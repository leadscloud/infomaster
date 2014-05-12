<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 验证用户是否登录成功
 *
 * @return bool
 */
function user_current($is_redirect=true){
    global $_USER; $user = null;
    // 取得 authcode
    $authcode = cookie_get('authcode');
    $is_login = $authcode?true:false;
    // 执行用户验证
    if ($is_login) {
        if ($user = user_get_byauth($authcode)) {
            $is_login = true;
        } else {
            $is_login = false;
        }
    }
    // 未登录，且跳转
    if (!$is_login && $is_redirect) {
        if (is_ajax()) {
            // 显示未登录的提示警告
            ajax_echo('Alert','您现在已经登出，请重新登录！',"InfoSYS.redirect('".ADMIN."login.php');");
        } else {
            redirect(ADMIN.'login.php');
        }
    }
    $_USER = $user;
    return $user;
}
/**
 * 用户登录
 *
 * @param string $username
 * @param string $password
 * @return array $user  用户信息
 *         int   null1   没有此用户
 *         int   0      用户密码不正确
 *         int   负数   用户的其它状态，可能是被锁定
 */
function user_login($username,$password){
    if ($user = user_get_byname($username)) {
        if ((int)$user['status']!==0) {
            return $user['status'];
        }
        $md5_pass = md5($password.$user['authcode']);
        if ($md5_pass == $user['pass']) {//var_dump($user,isset($user['MultiPersonLogin']) === false,(isset($user['MultiPersonLogin']) && $user['MultiPersonLogin']=='No'));
            // 不允许多用户同时登录
            if ( isset($user['MultiPersonLogin']) === false || (isset($user['MultiPersonLogin']) && $user['MultiPersonLogin']=='No') || $user['authcode']==null) {
                $authcode = authcode($user['userid']);
                if ($authcode != $user['authcode']) {
                    // 生成需要更新的数据
                    $userinfo = array(
                        'pass'     => md5($password.$authcode),
                        'authcode' => $authcode,
                    );
                    // 更新数据
                    user_edit($user['userid'],$userinfo);
                    // 合并新密码和key
                    $user = array_merge($user,$userinfo);
                }
            }
            /**
             * 记录用户登陆
             * 2013-10-8添加功能： 记录 HTTP_USER_AGENT
             */    
            $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:'';
            $description = "";
            $description .= "HTTP_USER_AGENT: " . $_SERVER["HTTP_USER_AGENT"];
            $description .= "\nHTTP_REFERER: " . $referer;
            $description .= "\nREMOTE_ADDR: " . $_SERVER["REMOTE_ADDR"];
            $description = esc_html($description);
            $args['description'] = $description;
			history_add(array(
				'action'        =>  'logged in',
				'objecttype'    =>  'user',
				'objectid'      =>  $user['userid'],
				'userid'        =>  $user['userid'],
				'objectname'    =>  $user['nickname'],
                'description'   =>  $description
			));

            return $user;
        } else {
			//记录失败的登陆
			log_authenticate_user($user,$password);
            // 密码不正确
            return 0;
        }
    } else {
        // 没有此用户
        return null;
    }
}

function user_logout($redirect=null){
	//记录注销
	$authcode = cookie_get('authcode');
	if($authcode){
		if($user = user_get_byauth($authcode)){
			history_add(array(
				'action'=>'logged out',
				'objecttype'=>'user',
				'objectid'=>$user['userid'],
				'userid'=>$user['userid'],
				'objectname'=>$user['nickname']
			));
		}
	}
	//删除cookie
	cookie_delete('authcode');
	if($redirect)  redirect($redirect);
}
/**
 * 通过用户ID查询用户信息
 *
 * @param int $userid
 * @return array|null
 */
function user_get_byid($userid) {
    $userid = intval($userid);
    return user_get($userid,0);
}
/**
 * 通过用户名查询用户信息
 *
 * @param string $name
 * @return array|null
 */
function user_get_byname($name) {
    return user_get($name,1);
}
/**
 * 通过authcode查询用户信息
 *
 * @param string $authcode
 * @return array|null
 */
function user_get_byauth($authcode) {
    return user_get($authcode,2);
}
/**
 * 取得用户信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function user_get($param,$type=0){
    $db = get_conn(); if ((int)$type>2) return null;
    $ckeys = array('user.userid.','user.name.','user.authcode.');
    $ckey  = $ckeys[$type];
    $user  = fcache_get($ckey.$param);
    if (fcache_not_null($user)) return $user;

    switch($type){
        case 0:
            $where = sprintf("`u`.`userid`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("`u`.`name`='%s'",esc_sql($param));
            break;
        case 2:
            $where = sprintf("`u`.`authcode`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT u.userid,mg.name as usergroup,mg.code as grpcode, u.name,u.pass,u.authcode,u.mail,u.status,u.registered,u.usergroup as primary_grp,u.other_usergroups as other_grps,mg.permissions FROM `#@_user` as u LEFT JOIN `#@_user_groups` mg ON mg.id = u.usergroup WHERE {$where} LIMIT 1 OFFSET 0;");
    // 判断用户是否存在
    if ($user = $db->fetch($rs)) {
        if ($meta = user_get_meta($user['userid'])) {
            $user = array_merge($user,$meta);
        }
        // 保存到缓存
        fcache_set($ckey.$param,$user);

        return $user;
    }
    return null;
}
/**
 * 获取用户的详细信息
 *
 * @param int $userid
 * @return array
 */
function user_get_meta($userid) {
    $db = get_conn(); $result = array(); $userid = intval($userid);
    $rs = $db->query("SELECT * FROM `#@_user_meta` WHERE `userid`=%d;",$userid);
    while ($row = $db->fetch($rs)) {
        $result[$row['key']] = is_serialized($row['value']) ? unserialize($row['value']) : $row['value'];
    }
    return $result;
}
/**
 * 取得用户组列表
 *
 * @param string $type
 * @return array
 */
function group_get_list() {
    $db = get_conn(); $result = array();
    $rs = $db->query("SELECT * FROM `#@_user_groups`");
    while ($row = $db->fetch($rs)) {
        $result[] = $row['id'];
    }
    return $result;
}
/**
 * 取得用户组树
 *
 * @param int $parentid
 * @param string $type
 * @return array
 */
function group_get_trees($grpid=0) {
    $result = array(); $grpid = intval($grpid);
    $group_list = group_get_list();
    foreach ($group_list as $grpmyid) {
        $result[$grpmyid] = group_get($grpmyid,0);
    }
    if ($grpid) {
        $result = isset($result[$grpid])?$result[$grpid]:array();
    }
    return $result;
}
/**
 * 取得用户组信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function group_get($param,$type=0){
    $db = get_conn(); if ((int)$type>2) return null;

    switch($type){
        case 0:
            $where = sprintf("`id`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("`name`='%s'",esc_sql($param));
            break;
		case 2:
            $where = sprintf("`code`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_user_groups` WHERE {$where} LIMIT 1 OFFSET 0;");
    // 判断用户组是否存在
    if ($group = $db->fetch($rs)) {
		$count = $db->result(sprintf("SELECT COUNT(`usergroup`) FROM `#@_user` WHERE `usergroup`=%d;",esc_sql($group['id'])));
		$group['count'] = $count;
        return $group;
    }
    return null;
}

/**
 * 通过用户ID查询用户信息
 *
 * @param int $userid
 * @return array|null
 */
function group_get_byid($grpid) {
    $grpid = intval($grpid);
    return group_get($grpid,0);
}
/**
 * 通过用户组名查询用户组信息
 *
 * @param string $name
 * @return array|null
 */
function group_get_byname($name) {
    return group_get($name,1);
}
/**
 * 创建一个用户组
 *
 * @param array $data
 * @return array
 */
function group_add($data) {
    $db = get_conn();
    if (!is_array($data)) return false;
	$category = isset($data['category']) ? $data['category'] : null;
	$data['category'] = serialize($category);
    $groupid = $db->insert('#@_user_groups',$data);
    $group   = array_merge($data,array(
       'id' => $groupid,
    ));
	//log to history
	history_group_created($groupid);
    return $group;
}
function group_edit($groupid,$data) {
    $db = get_conn();
    $groupid = intval($groupid);
    $group_rows = array();
    if ($group = group_get($groupid)) {
        $data = is_array($data) ? $data : array();
		$category = isset($data['category']) ? $data['category'] : null;
		$data['category'] = serialize($category);
        foreach ($data as $field=>$value) {
            if ($db->is_field('#@_user_groups',$field)) {
                $group_rows[$field] = $value;
            }
        }
        // 更新数据
        if ($group_rows) {
			$db->update('#@_user_groups',array('default_group'=>0));
            $db->update('#@_user_groups',$group_rows,array('id' => $groupid));
        }
		//清理所有用户缓存
		$users = user_get_list(); 
		foreach($users as $userid){
			user_clean_cache($userid);
		}
		//log to history
		history_group_update($groupid);
        return array_merge($group,$data);
    }
    return null;
}


/**
 * 创建用户
 *
 * @param string $name
 * @param string $pass
 * @param string $email
 * @param array $data
 * @return array
 */
function user_add($name,$pass,$email,$data=null) {
    $db = get_conn();
    // 插入用户
    $userid = $db->insert('#@_user',array(
       'name' => $name,
       'pass' => $pass,
       'mail' => $email,
       'status' => 0,
       'registered' => date('Y-m-d H:i:s',time()),
    ));
    // 生成authcode
    $authcode = authcode($userid);
    $user_info = array(
       'pass' => md5($pass.$authcode),
       'authcode' => $authcode,
    );
    if ($data && is_array($data)) {
        $user_info = array_merge($user_info,$data);
    }
    // 更新用户资料
	$user = user_edit($userid,$user_info);
	//记住操作
	history_user_created($userid);
    return $user;
}
/**
 * 填写用户信息
 *
 * @param int $userid
 * @param array $data
 * @return array|null
 */
function user_edit($userid,$data) {
    $db = get_conn();
    $userid = intval($userid);
    $user_rows = $meta_rows = array();
    if ($user = user_get_byid($userid)) {
        $data = is_array($data) ? $data : array();
        foreach ($data as $field=>$value) {
            if ($db->is_field('#@_user',$field)) {
                $user_rows[$field] = $value;
            } else {
                $meta_rows[$field] = $value;
            }
        }
        // 更新数据
        if ($user_rows) {
            $db->update('#@_user',$user_rows,array('userid' => $userid));
        }
        if ($meta_rows) {
            user_edit_meta($userid,$meta_rows);
        }
		//记住操作
		if($user_rows || $meta_rows) {
			if(!(isset($user_rows['registered']) || isset($meta_rows['registered'])))
				history_profile_update($userid);
		}
        // 清理用户缓存
        user_clean_cache($userid);
        return array_merge($user,$data);
    }
    return null;
}
/**
 * 填写用户扩展信息
 *
 * @param int $userid
 * @param array $data
 * @return bool
 */
function user_edit_meta($userid,$data) {
    $db = get_conn(); $userid = intval($userid);
    if (!is_array($data)) return false;
    foreach ($data as $key=>$value) {
        // 查询数据库里是否已经存在
        $length = (int) $db->result(vsprintf("SELECT COUNT(*) FROM `#@_user_meta` WHERE `userid`=%d AND `key`='%s';",array($userid,esc_sql($key))));
        // update
        if ($length > 0) {
            $db->update('#@_user_meta',array(
                'value' => $value,
            ),array(
                'userid' => $userid,
                'key'    => $key,
            ));
        }
        // insert
        else {
            // 保存到数据库里
            $db->insert('#@_user_meta',array(
                'userid' => $userid,
                'key'    => $key,
                'value'  => $value,
            ));
        }
    }
    return true;
}
/**
 * 清理用户缓存
 *
 * @param int $userid
 * @return bool
 */
function user_clean_cache($userid) {
    if ($user = user_get_byid($userid)) {
        $ckey = 'user.';
        foreach (array('userid','name','authcode') as $field) {
            fcache_delete($ckey.$field.'.'.$user[$field]);
        }
    }
    return true;
}
/**
 * 删除用户
 *
 * @param int $userid
 * @return bool
 */
function user_delete($userid) {
    $db = get_conn();
    $userid = intval($userid);
    if (!$userid) return false;
    if ($user = user_get_byid($userid)) {
        // 超级管理员不能删除
        if ($user['Administrator']=='Yes' && $user['roles']=='ALL')
            return false;

        user_clean_cache($userid);
		//记住操作
		history_delete_user($userid);
		
        $db->delete('#@_user',array('userid' => $userid));
        $db->delete('#@_user_meta',array('userid' => $userid));
		
        return true;
    }
    return false;
}
/**
 * 删除用户组
 *
 * @param int $userid
 * @return bool
 */
function group_delete($groupid) {
    $db = get_conn();
    $groupid = intval($groupid);
    if (!$groupid) return false;
    if ($group = group_get($groupid)) {
		//log to history
		history_delete_group($groupid);
        $db->delete('#@_user_groups',array('id' => $groupid));
        return true;
    }
    return false;
}
/**
 * 获得用户列表
 *
 */
function user_get_trees(){
	$result = array();
    $user_list = user_get_list();
    foreach ($user_list as $userid) {
        $result[$userid] = user_get_byid($userid);
    }
	return $result;
}
function user_get_list(){
	$db = get_conn(); $result = array();
	$rs = $db->query("SELECT * FROM `#@_user` WHERE `status` = 0;");
	while ($row = $db->fetch($rs)) {
		$result[] = $row['userid'];
    }
	return $result;
}
/*
 * 统计用户数量
*/
function user_count($status=0) {
    $db = get_conn(); return $db->result(sprintf("SELECT COUNT(`userid`) FROM `#@_user` WHERE `status`=%d", $status));
}