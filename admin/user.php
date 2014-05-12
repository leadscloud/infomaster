<?php
// 文件名
$php_file = isset($php_file) ? $php_file : 'user.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '用户管理');
//system_head('styles', array('css/user'));
system_head('scripts',array('js/user'));
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
        // 重置标题
	    system_head('title', '添加新用户');
        // 权限检查
	    current_user_can('user-new');
	    // 添加JS事件
	    system_head('loadevents','user_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
        // 显示页面
	    user_manage_page('add');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'edit':
	    // 所属
        $parent_file = 'user.php';
        // 重置标题
	    system_head('title', '编辑用户');
	    // 权限检查
	    current_user_can('user-edit');
	    // 添加JS事件
	    system_head('loadevents','user_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    user_manage_page('edit');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'unapprove':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目.');
	    }
		current_user_can('user-status');
		foreach ($listids as $uid) {
			user_edit($uid,array('status'=>1));
		}
		redirect(referer());
		break;
	case 'approve':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目.');
	    }
		current_user_can('user-status');
		$approved = count($listids);
		foreach ($listids as $uid) {
			user_edit($uid,array('status'=>0));
		}
		ajax_success(sprintf('%s 个用户审核通过.', $approved),"InfoSYS.redirect('".referer()."');");
		break;
	// 保存用户
	case 'save':
	    $userid = isset($_POST['userid'])?$_POST['userid']:null;
	    current_user_can($userid?'user-edit':'user-new');
	    
        if (validate_is_post()) {
        	$referer  = referer(PHP_FILE,false);

            $username  = isset($_POST['username'])?$_POST['username']:null;
            $password  = isset($_POST['password1'])?$_POST['password1']:null;
            $password2 = isset($_POST['password2'])?$_POST['password2']:null;
            $nickname  = isset($_POST['nickname'])?$_POST['nickname']:null;
            $email     = isset($_POST['email'])?$_POST['email']:null;
            $symbol    = isset($_POST['symbol'])?$_POST['symbol']:null;
            $workplace = isset($_POST['workplace'])?$_POST['workplace']:null;
            $desc      = isset($_POST['description'])?$_POST['description']:null;
            $bcpwd     = isset($_POST['BanChangePassword'])?$_POST['BanChangePassword']:null;
            $mplogin   = isset($_POST['MultiPersonLogin'])?$_POST['MultiPersonLogin']:'Yes';
            $roldes    = isset($_POST['roles'])?$_POST['roles']:array();
			$groupid   = isset($_POST['usergroup'])?$_POST['usergroup']:0;
			$additional_groups   = isset($_POST['additional_groups'])?$_POST['additional_groups']:array();
			$status    = isset($_POST['activity'])?0:1;
			
			$grp_admin = isset($_POST['group_admin'])?0:1;
			
            if ($userid) {
            	$user = user_get_byid($userid); $is_exist = true;
            	if ($username != $user['name']) {
            		$is_exist = user_get_byname($username)?false:true;
            	}
                if ($user['roles']=='ALL') $roldes = 'ALL';
            	unset($user);
            } else {
                $is_exist = user_get_byname($username)?false:true;
            }
            // 验证用户名
            validate_check(array(
                // 用户名不能为空
                array('username',VALIDATE_EMPTY,'用户名不能为空。'),
				array('nickname',VALIDATE_EMPTY,'昵称不能为空。'),
				array('symbol',VALIDATE_EMPTY,'用户代号不能为空。'),
                // 用户名长度必须是2-30个字符
                array('username',VALIDATE_LENGTH,'用户名长度必须在 %d-%d 字符之间。',2,30),
				array('nickname',VALIDATE_LENGTH,'昵称长度必须在 %d-%d 字符之间。',2,30),
                // 用户已存在
                array('username',$is_exist,'用户名已经存在。'),	
            ));
            // 验证email
            validate_check(array(
                //array('email',VALIDATE_EMPTY,'Please enter an e-mail address.'),
                array('email',VALIDATE_IS_EMAIL,'你必须提供一个邮箱地址。')
            ));
            // 验证密码
            if ((!$userid) || $password) {
                validate_check(array(
                    array('password1',VALIDATE_EMPTY,'你输入你的密码.'),
                    array('password2',VALIDATE_EMPTY,'请再次输入你的密码.'),
                    array('password1',VALIDATE_EQUAL,'你两次输入的密码不匹配,请重试.','password2'),
                ));
            }
			validate_check('usergroup',VALIDATE_EMPTY,'请选择一个用户组');
            // 验证通过
            if (validate_is_ok()) {
                $username = esc_html($username);
                $email    = esc_html($email);
                $user_info = array(
                    'symbol'  => esc_html($symbol),
                    'workplace'  => esc_html($workplace),
					'mail' => esc_html($email),
					'status' => $status,
                    'roles' => $roldes,
                    'nickname' => esc_html($nickname),
                    'Administrator' => 'Yes',
                    'BanChangePassword' => $bcpwd,
                    'MultiPersonLogin'  => $mplogin,
					'usergroup'  => $groupid,
					'other_usergroups'  => $additional_groups,
					'GroupAdmin'	=> $grp_admin
                );
                // 编辑
                if ($userid) {
                    $user_info = array_merge($user_info,array(
                        'username'    => $username,
                        'description' => esc_html($desc)
                    ));
					
                    // 修改暗号
                    if ($password) {
                		$user_info = array_merge($user_info,array(
                	   		'pass' => md5($password), 'authcode' => '', //无论密码是否与以前一样，都要重新登陆
                		));	
                    }
                    user_edit($userid,$user_info);
                    ajax_success('已更新用户数据。',"InfoSYS.redirect('".$referer."');");
                } 
                // 强力插入
                else {
                    user_add($username,$password,$email,$user_info);
                    ajax_success('创建用户成功。',"InfoSYS.redirect('".$referer."');");
                }
            }
        }
	    break;
	//删除用户
	case 'delete':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('没有选择任何项目.');
	    }
		current_user_can('user-delete');
		foreach ($listids as $userid) {
			if ($_USER['userid']==$userid) continue;
			user_delete($userid);
		}
		ajax_success('用户已删除.',"InfoSYS.redirect('".referer()."');");
		break;
	default:
	    current_user_can('user-list');
	    system_head('loadevents','user_list_init');
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$query    = array('page' => '$');
		$conditions = array();
		if ($search) {
			$where = "WHERE `um`.`key`='Administrator' AND `um`.`VALUE`='Yes'";
			$query['query'] = $search;
			$fields = array('`u`.`name`','`u`.`mail`','`ug`.`name`');
			foreach($fields as $field) {
            	$conditions[] = sprintf("BINARY UCASE(%s) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
            }
			$conditions[] = sprintf("(`um2`.`key`='nickname' AND BINARY UCASE(`um2`.`value`) LIKE UCASE('%%%%%s%%%%'))",esc_sql($search));
            $where.= ' AND ('.implode(' OR ', $conditions).')';
			$sql = "SELECT DISTINCT(`u`.`userid`) FROM `#@_user` AS `u` LEFT JOIN `#@_user_meta` AS `um` ON `u`.`userid`=`um`.`userid` LEFT JOIN `#@_user_groups` AS `ug` ON `ug`.`id`=`u`.`usergroup` LEFT JOIN `#@_user_meta` AS `um2` ON `um`.`userid`=`um2`.`userid` {$where} ORDER BY `u`.`userid` ASC";
		} else {
            $sql = "SELECT `userid` FROM `#@_user_meta` WHERE `key`='Administrator' AND `VALUE`='Yes' ORDER BY `userid` ASC";
		}
		$result = pages_query($sql);
		// 分页地址
        $page_url   = PHP_FILE.'?'.http_build_query($query);
		
        //$result = pages_query("SELECT `userid` FROM `#@_user_meta` WHERE `key`='Administrator' AND `VALUE`='Yes' ORDER BY `userid` ASC");
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-user"></i> 用户列表</h3>';
		echo '</div>';

		
		
		echo '<div id="userlist">';
		table_nav('top',$page_url);
		echo 		'<table class="table table-striped table-hover table-bordered">';
		echo 			'<thead>';
		table_thead();
		echo			'</thead>';
		echo           	'<tfoot>';
        table_thead();
        echo           	'</tfoot>';
		echo			'<tbody>';
		while ($data = pages_fetch($result)) {
            $user = user_get_byid($data['userid']);
            if ($user['userid']==$_USER['userid']) {
            	$href = ADMIN.'profile.php?referer='.PHP_FILE;
            } else {
                $href = PHP_FILE.'?method=edit&userid='.$user['userid'];
            }
			$ghref = '';
			if(!empty($user['usergroup'])) {
				$ghref = '<a class="black" href="user-group.php?method=edit&groupid='.$user['primary_grp'].'">'.$user['usergroup'].'</a>';
			}else {
				$ghref = '未分配';
			}
            echo           '<tr id="user-'.$user['userid'].'">';
            echo               '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$user['userid'].'" /></td>';
            echo               '<td><i class="icon-user"></i> <a class="black" href="'.$href.'">'.$user['name'].'</a></td>';
			echo               '<td><i class="icon-user"></i> <a class="black" href="'.$href.'">'.$user['nickname'].'</a></td>';
            echo               '<td><i class="icon-envelope"></i> '.$user['mail'].'</td>';
            echo               '<td>'.($user['status']>0?'<span class="label label-important">不可用</span>':'<span class="label label-success">正常</span>').'</td>';
			echo               '<td><i class="icon-group"></i> '. $ghref .'</td>';
            echo               '<td><i class="icon-time"></i> '.$user['registered'].'</td>';
            echo           '</tr>';
        }
		echo			'</tbody>';
		echo 		'</table>';
		table_nav('bottom',$page_url);
		echo '</div>';
		
        include ADMIN_PATH.'/admin-footer.php';
        break;
}

/**
 * 批量操作
 *
 */
function table_nav($side='top',$url) {
	global $search;
	echo '<div class="table-nav clearfix">';
	echo 	'<div class="pull-left btn-group">';
	echo		'<button class="btn btn-small" onclick="javascript:;InfoSYS.redirect(\''.referer().'\');return false;"><i class="icon-arrow-up"></i> 返回</button> ';
	//echo		'<button class="btn btn-small" id="select" onclick="javascript:;return false;" data-toggle="button"><i class="icon-check"></i> 全选</button> ';
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加新用户</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-minus"></i> 删除</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	echo		'<button class="btn btn-small" name="refresh" onclick="javascript:;return false;"><i class="icon-refresh"></i> 刷新</button> ';
	
	echo	'</div>';



	echo 	'<div class="pull-left btn-group">';
	echo		'<button class="btn btn-small" name="approve" onclick="return false;"><i class="icon-eye-open"></i> 激活用户</button> ';
	echo		'<button class="btn btn-small" name="unapprove" onclick="return false;"><i class="icon-eye-close"></i> 禁用用户</button> ';
	
	echo	'</div>';




	if ($side == 'top') {
	echo 	'<div class="pull-right form-search btn-group"><form action="" method="get">';
	echo		'<div class="input-append"> <input class="span2 search-query" style="padding:2px 14px;height:21px;" name="query" type="text" value="'.esc_html($search).'"> <button class="btn  btn-small" type="submit" onclick="javascript:;">搜索</button></div></form>';
	
	echo 	'</div>';
	} else {
        echo pages_list($url);
    }
	echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
	echo '<tr>';
	echo     '<th style="width:20px" class="td-right"><input type="checkbox" name="select" value="all"></th>';
	echo     '<th>用户名</th>';
	echo     '<th>昵称（信息员）</th>';
	echo     '<th>邮箱</th>';
	echo     '<th>状态</th>';
	echo     '<th>用户组（信息组）</th>';
	echo     '<th>注册日期</th>';
	echo '</tr>';
}

/**
 * 用户管理页面
 *
 * @param string $action
 */
function user_manage_page($action) {
	global $php_file;
    $referer = referer(PHP_FILE);
	if ('user.php' == $php_file) {
		$trees = group_get_trees();
		if (empty($trees)) {
            echo '<div class="wrap">';
            echo   '<h2>'.system_head('title').'</h2>';
            echo   '<div class="well">';
            echo       '<div class="control-group">';
            echo               '<label class="control-label">请添加一个新的用户组</label>';
            echo               '<button type="button" class="btn btn-primary" onclick="InfoSYS.redirect(\''.ADMIN.'user-group.php?method=new\')">添加用户组</button> <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
            echo       '</div>';
            echo   '</div>';
            echo '</div>';
            return true;
        }
	}
	
    $userid  = isset($_GET['userid'])?$_GET['userid']:0;
    if ($action!='add') {
    	$_USER  = user_get_byid($userid);
    }
    $username = isset($_USER['name'])?$_USER['name']:null;
    $nickname = isset($_USER['nickname'])?$_USER['nickname']:null;
    $email    = isset($_USER['mail'])?$_USER['mail']:null;
    $symbol   = isset($_USER['symbol'])?$_USER['symbol']:null;
    $workplace= isset($_USER['workplace'])?$_USER['workplace']:null;
    $desc     = isset($_USER['description'])?$_USER['description']:null;
    $bcpwd    = isset($_USER['BanChangePassword'])?$_USER['BanChangePassword']:null;
    $mplogin  = isset($_USER['MultiPersonLogin'])?$_USER['MultiPersonLogin']:'No';
    $roles    = isset($_USER['roles'])?$_USER['roles']:null;
	$groupid  = isset($_USER['primary_grp'])?$_USER['primary_grp']:null;
	$additional_groups  = isset($_USER['other_grps'])?$_USER['other_grps']:array();
	//$additional_groups = explode(',',$additional_groups);
	$status   = isset($_USER['status'])?$_USER['status']==0:false;
	$grp_admin = isset($_USER['GroupAdmin'])?$_USER['GroupAdmin']==0:false;
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-user"></i> ').system_head('title').'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="usermanage" id="usermanage">';
    echo     '<fieldset>';
	
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	echo				'<div class="control-group"><label class="control-label" for="username">用户名</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="username" id="username" placeholder="注册名（必填）" value="'.$username.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="nickname">昵称</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="nickname" id="nickname" placeholder="昵称（必填 ，信息员的名称）" value="'.$nickname.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="symbol">用户代号</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="symbol" id="symbol" placeholder="用户昵称首写字母缩写" value="'.$symbol.'">';
	echo					'</div>';
  	echo				'</div>';
  	echo				'<div class="control-group"><label class="control-label" for="workplace">工作地点</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="workplace" id="workplace" placeholder="" value="'.$workplace.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="email">电子邮箱</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="email" id="email" placeholder="电子邮箱" value="'.$email.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="password1">密码</label>';
    echo					'<div class="controls">';
	echo						'<input type="password" name="password1" id="password1" placeholder="密码"> <br />  <br />';
	echo						'<input type="password" name="password2" id="password2" placeholder="密码（再输一次）">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group">';
    echo					'<div class="controls">';
	echo						'<span id="pass-strength-result" class="label">密码强度指示器</span>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="url">是否激活</label>';
    echo					'<div class="controls">';
	echo						'<input type="checkbox" name="activity" '.(($action=='add'||$status)?'checked="checked"':'').'>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="url">用户组</label>';
    echo					'<div class="controls">';
	echo						'<select name="usergroup">';
	echo							dropdown_groups($groupid);
	echo						'</select>';
	echo						' <span class="help-inline">信息员所在的信息组，必选</span>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="group_admin">组管理员</label>';
    echo					'<div class="controls">';
	echo						'<input type="checkbox" id="group_admin" name="group_admin" '.(($grp_admin)?'checked="checked"':'').'>';
	echo					'</div>';
  	echo				'</div>';
	if ($action!='add') {
	echo				'<div class="control-group"><label class="control-label" for="url">其它用户组</label>';
    echo					'<div class="controls">';
	echo						display_ul_groups($groupid,$additional_groups);
	echo					'</div>';
  	echo				'</div>';
	 }
	echo			'</div>';
	echo 		'</div>';
	
	echo		'<div style="height:30px">';
	echo			'<a class="btn btn-mini" data-toggle="collapse" data-target="#advance"><i class="icon-chevron-down"></i>权限设置</a>';
	echo		'</div>';

	echo		'<div class="widget collapse" id="advance">';
	echo			'<div class="widget-header">';
	echo				'<i class="icon-cog"></i><h3>权限设置</h3>';
	echo			'</div>';
	
	echo			'<div class="widget-content">';
	
	echo '<div class="alert alert-info fade in"><button type="button" class="close" data-dismiss="alert">×</button> <i class="icon-info-sign"></i> 用户所拥有的权限，为用户组权限 + 用户所在其它组权限 + 用户权限</div>';
	
	echo				'<div class="control-group">';
    echo					'<div class="controls">';
	echo						'<label for="BanChangePassword"  class="checkbox inline"><input type="checkbox" name="BanChangePassword" id="BanChangePassword" value="Yes"'.($bcpwd=='Yes'?' checked="checked"':null).' />禁止修改密码</label> ';
	echo                        '<label for="MultiPersonLogin" class="checkbox inline"><input type="checkbox" name="MultiPersonLogin" id="MultiPersonLogin" value="No"'.($mplogin=='No'?' checked="checked"':null).' />禁止多人同时登录</label>';
	echo					'</div>';
	echo				'</div>';

	echo                    system_purview($roles);
	echo				'<div class="control-group">';
	echo                    '<div class="controls"><button type="button" class="btn btn-small" rel="select">全选 / 反选</button></div>';
	echo				'</div>';
	echo			'</div>';
	echo		'</div>';
	
    echo   '</fieldset>';
    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">添加用户</button>';
    } else {
        echo   '<button type="submit" class="btn btn-primary">更新用户</button><input type="hidden" name="userid" value="'.$userid.'" /><input type="hidden" name="referer" value="'.referer().'" />';
    }
    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}
function dropdown_groups($selected=null){
	$hl = ''; $groupname ='';
	$trees	=	group_get_trees();
	//print_r($trees);
	foreach ($trees as $i=>$tree) {
		if($selected)
			$sel  = $selected==$tree['id']?' selected="selected"':null;
		else
			$sel  = 1==$tree['default_group']?' selected="selected"':null;
		$groupname = $tree['name'];
        $hl.= '<option value="'.$tree['id'].'"'.$sel.'>'.$groupname.'</option>';
		
	}
	return $hl;
}
function display_ul_groups($grpid,$groups=array(),$trees=null) {
    static $func = null;
	$hl = ' ';
    //$hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="categories"' : 'class="children"');
    if (!$func) $func = __FUNCTION__;
    if ($trees === null) $trees = group_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['id'],$groups) && $grpid!=$tree['id'] ? ' checked="checked"' : '';
        $main_checked = $tree['id']==$grpid?' checked="checked"':'';
        //$hl.= sprintf('<input type="radio" name="sortid" value="%d"%s />',$tree['taxonomyid'],$main_checked);
        $hl.= sprintf('<label class="checkbox " for="group-%d">',$tree['id']);
        $hl.= sprintf('<input type="checkbox" id="group-%1$d" name="additional_groups[]" value="%1$d"%3$s />%2$s</label>',$tree['id'],$tree['name'],$checked);
    	if (isset($tree['subs'])) {
    		$hl.= $func($grpid,$groups,$tree['subs']);
    	}
        $hl.= '';
    }
    $hl.= '';
    return $hl;
}