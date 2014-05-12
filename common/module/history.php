<?php
/**
 * 存储用户登陆及操作记录
 */
 
/**
 * 向历史记录中添加事件
 */
function history_add($args) {
	$db = get_conn();global $_USER;
	
	$defaults = array(
		"action" => null,
		"objecttype" => null,
		"objectsubtype" => null,
		"objectid" => null,
		"objectname" => null,
		"userid" => null,
		"description" => null
	);

	$args = parse_args( $args, $defaults );
	
	if(isset($_USER["userid"])) $args['userid'] = $_USER["userid"];
	$localtime = current_time("mysql");
	$args['datetime'] = $localtime;
	
	if($args['userid']!=null)
		$db->insert('#@_history',$args);
	
	
/*	global $_USER;
	if (!is_array($data)) return false;
	if(isset($_USER["userid"])) $data['userid'] = $_USER["userid"];
	$localtime = current_time("mysql");
	$data['datetime'] = $localtime;
	$db = get_conn();
	$db->insert('#@_history',$data);*/
}
/**
 * 从数据表中删除旧的记录
 * @todo: 设置成用户是否可以删除,及删除多少天之前的
 */
function history_purge_db($days=60) {
	$db = get_conn();
	
	$do_purge_history = TRUE;

	$sql = "DELETE FROM `#@_history` WHERE DATE_ADD(`datetime`, INTERVAL $days DAY) < now()";

	if ($do_purge_history) {
		$db->query($sql);
	}
}
/**
 * Log failed login attempt to username that exists
 */
function log_authenticate_user($user, $password) {

	$description = "";
	$description .= "HTTP_USER_AGENT: " . isset($_SERVER["HTTP_USER_AGENT"])?$_SERVER["HTTP_USER_AGENT"]:null;
	$description .= "\nHTTP_REFERER: " . isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:null;
	$description .= "\nREMOTE_ADDR: " . isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:null;
	
	$description = esc_html($description);

	$data = array(
				"objecttype" => "user",
				"objectname" => $user['name'],
				"action" => "登陆失败,因为使用了错误的密码: ".$password,
				"objectid" => $user['userid'],
				"description" => $description
			);
	
	history_add($data);

	return $user;

}

// 更新用户时
function history_profile_update($userid) {
	$user = user_get_byid($userid);
	$nickname = isset($user['nickname'])?$user['nickname']:$user['name'];
	$user_nickname = urlencode($nickname);
	if(!empty($user_nickname))
		history_add("action=updated&objecttype=user&objectid=$userid&objectname=$user_nickname");
}

// 创建用户时
function history_user_created($userid) {
	$user = user_get_byid($userid);
	$nickname = isset($user['nickname'])?$user['nickname']:$user['name'];
	$user_nickname = urlencode($nickname);
	history_add("action=created&objecttype=user&objectid=$userid&objectname=$user_nickname");
}
// 用户被删除时
function history_delete_user($userid) {
	$user = user_get_byid($userid);
	$nickname = isset($user['nickname'])?$user['nickname']:$user['name'];
	$user_nickname = urlencode($nickname);
	history_add("action=deleted&objecttype=user&objectid=$userid&objectname=$user_nickname");
}


// 更新用组户时
function history_group_update($grpid) {
	$group = group_get_byid($grpid);
	$grp_name = urlencode($group['name']);
	if(!empty($grp_name))
		history_add("action=updated&objecttype=group&objectid=$grpid&objectname=$grp_name");
}

// 创建用户组时
function history_group_created($grpid) {
	$group = group_get_byid($grpid);
	$grp_name = urlencode($group['name']);
	history_add("action=created&objecttype=group&objectid=$grpid&objectname=$grp_name");
}
// 用户组被删除时
function history_delete_group($grpid) {
	$group = group_get_byid($grpid);
	$grp_name = urlencode($group['name']);
	history_add("action=deleted&objecttype=group&objectid=$grpid&objectname=$grp_name");
}

// 导出文件时
function history_export($count,$name) {
	$description = "";
	$description .= "INQUIRY_COUNT: ". $count;
	$description .= "\nHTTP_USER_AGENT: " . $_SERVER["HTTP_USER_AGENT"];
	$description .= "\nHTTP_REFERER: " . $_SERVER["HTTP_REFERER"];
	$description .= "\nREMOTE_ADDR: " . $_SERVER["REMOTE_ADDR"];
	//$description = esc_html($description);
	
	$args = array(
				"objecttype" => "excel",
				"objectname" => $name,
				"action" => "export",
				//"objectid" => $user->ID,
				"description" => $description
			);
			
	history_add($args);

}
function history_import($count,$name) {
	$count = intval($count);
	$description = "";
	$description .= "ROW_COUNT: ". $count;
	$description .= "\nHTTP_USER_AGENT: " . $_SERVER["HTTP_USER_AGENT"];
	$description .= "\nHTTP_REFERER: " . $_SERVER["HTTP_REFERER"];
	$description .= "\nREMOTE_ADDR: " . $_SERVER["REMOTE_ADDR"];
	
	$args = array(
				"objecttype" => "excel",
				"objectname" => $name,
				"action" => "import",
				//"objectid" => $user->ID,
				"description" => $description
			);
	history_add($args);
}


function history_clear_log() {
	$db = get_conn();
	$sql = "DELETE FROM `#@_history`";
	$db->query($sql);
}

function history_get($param,$type=0){
    $db = get_conn(); if ((int)$type>1) return null;
    $ckeys = array('history.id.','history.objecttype.');
    $ckey  = $ckeys[$type];
    $history  = fcache_get($ckey.$param);
    if (fcache_not_null($history)) return $history;

    switch($type){
        case 0:
            $where = sprintf("WHERE `id`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("WHERE `objecttype`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_history` {$where} LIMIT 1 OFFSET 0;");
    // 判断用户是否存在
    if ($history = $db->fetch($rs)) {
        // 保存到缓存
        fcache_set($ckey.$param,$history);

        return $history;
    }
    return null;
}