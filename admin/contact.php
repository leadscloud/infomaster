<?php
// 文件名
$php_file = isset($php_file) ? $php_file : 'contact.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '联系人管理');
//system_head('styles', array('css/user'));
system_head('scripts',array('js/contact'));
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
        // 重置标题
	    system_head('title', '添加联系人');
        // 权限检查
	    current_user_can('contact-new');
	    // 添加JS事件
	    system_head('loadevents','contact_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
        // 显示页面
	    contact_manage_page('add');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'edit':
	    // 所属
        $parent_file = 'contact.php';
        // 重置标题
	    system_head('title', '编辑联系人');
	    // 权限检查
	    current_user_can('contact-edit');
	    // 添加JS事件
	    system_head('loadevents','contact_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    contact_manage_page('edit');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 保存用户
	case 'save':
	    $contactid = isset($_POST['id'])?$_POST['id']:null;
	    current_user_can($contactid?'contact-edit':'contact-new');
	    
        if (validate_is_post()) {
            $name		= isset($_POST['name'])?$_POST['name']:null;
            $mobile		= isset($_POST['mobile'])?$_POST['mobile']:null;
            $email		= isset($_POST['email'])?$_POST['email']:null;
            $address	= isset($_POST['address'])?$_POST['address']:null;
            $birthday	= isset($_POST['birthday'])?$_POST['birthday']:null;
            $url		= isset($_POST['url'])?$_POST['url']:null;
            $note		= isset($_POST['note'])?$_POST['note']:null;
			$group		= isset($_POST['group'])?$_POST['group']:0;
			$category	= isset($_POST['category'])?$_POST['category']:array();
			
			
            if ($contactid) {
            	$contact = contact_get_byid($contactid); $is_exist = true;
            	if ($name != $contact['name']) {
            		$is_exist = contact_get_byname($name)?false:true;
            	}
            } else {
                $is_exist = contact_get_byname($name)?false:true;
            }
            // 验证用户名
            validate_check(array(
                // 用户名不能为空
                array('name',VALIDATE_EMPTY, '联系人姓名不能为空。'),
                // 姓名长度必须是2-30个字符
                array('name',VALIDATE_LENGTH,'姓名长度必须在 %d-%d 字符之间。',2,30),
                // 用户已存在
                array('name',$is_exist,'联系人姓名已经存在。'),	
            ));
            // 验证email
            validate_check(array(
                array('email',VALIDATE_IS_EMAIL,'请输入正确的邮箱地址。')
            ));
            // 验证通过
            if (validate_is_ok()) {
                $info = array(
                    'name'		=> esc_html($name),
					'mobile'	=> esc_html($mobile),
					'email'		=> esc_html($email),
                    'address'	=> $address,
                    'birthday'	=> esc_html($birthday),
                    'url' 		=> $url,
                    'note' 		=> esc_html($note),
                    'group' 	=> $group,
					'category'  => $category
                );
                // 编辑
                if ($contactid) {
					$info['edittime'] = date('Y-m-d H:i:s',time());
                    contact_edit($contactid,$info);
                    ajax_success('联系人更新成功。',"InfoSYS.redirect('".PHP_FILE."');");
                } 
                // 强力插入
                else {
					$info['datetime'] = date('Y-m-d H:i:s',time());
					$info['edittime'] = date('Y-m-d H:i:s',time());
                    contact_add($info);
                    ajax_success('联系人添加成功。',"InfoSYS.redirect('".PHP_FILE."');");
                }
            }
        }
	    break;
	//删除用户
	case 'delete':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目！');
	    }
		current_user_can('contact-delete');
		foreach ($listids as $contactid) {
			contact_delete($contactid);
		}
		ajax_success('用户已删除.',"InfoSYS.redirect('".referer()."');");
		break;
	default:
	    current_user_can('contact-list');
	    system_head('loadevents','contact_list_init');
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$query    = array('page' => '$');
		$conditions = array();
		if ($search) {
			$where = "WHERE 1";
			$query['query'] = $search;
			$fields = array('`name`','`mobile`','`email`','`address`','`birthday`','`url`','`note`');
			foreach($fields as $field) {
            	$conditions[] = sprintf("BINARY UCASE(%s) LIKE UCASE('%%%s%%')",$field,esc_sql($search));
            }
            $where.= ' AND ('.implode(' OR ', $conditions).')';
			$sql = "SELECT DISTINCT(`id`) FROM `#@_contact` {$where} ORDER BY `id` ASC";
		} else {
            $sql = "SELECT `id` FROM `#@_contact` ORDER BY `id` ASC";
		}
		$result = pages_query($sql);
		// 分页地址
        $page_url   = PHP_FILE.'?'.http_build_query($query);
		
        //$result = pages_query("SELECT `userid` FROM `#@_user_meta` WHERE `key`='Administrator' AND `VALUE`='Yes' ORDER BY `userid` ASC");
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-user"></i> 全部联系人</h3>';
		echo '</div>';
		
		
		
		echo '<div id="contactlist">';
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
            $contact = contact_get_byid($data['id']);
            $href = PHP_FILE.'?method=edit&id='.$contact['id'];

            echo           '<tr id="contact-'.$contact['id'].'">';
            echo               '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$contact['id'].'" /></td>';
            echo               '<td><i class="icon-user"></i> <a class="black" href="'.$href.'" data-toggle="tooltip" data-original-title="'.$contact['name'].'">'.$contact['name'].'</a></td>';
			echo               '<td><i class="icon-phone"></i> '.$contact['mobile'].'</td>';
            echo               '<td title="'.$contact['email'].'"><i class="icon-envelope"></i> '.$contact['email'].'</td>';
			echo               '<td title="'.$contact['address'].'"> '.$contact['address'].'</td>';
			echo               '<td> '.$contact['birthday'].'</td>';
			//echo               '<td title="'.$contact['url'].'"> '.$contact['url'].'</td>';
			echo               '<td title="'.$contact['note'].'"> '.$contact['note'].'</td>';
			echo               '<td> '.$contact['category'].'</td>';
            echo               '<td title="'.$contact['datetime'].'"><i class="icon-time"></i> '.$contact['datetime'].'</td>';
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
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加联系人</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-minus"></i> 删除</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	echo		'<button class="btn btn-small" name="refresh" onclick="javascript:;return false;"><i class="icon-refresh"></i> 刷新</button> ';
	
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
	echo     '<th>姓名</th>';
	echo     '<th>手机</th>';
	echo     '<th>邮箱</th>';
	echo     '<th>地址</th>';
	echo     '<th>生日</th>';
	//echo     '<th>URL</th>';
	echo     '<th>备注</th>';
	echo     '<th>部门</th>';
	echo     '<th>添加时间</th>';
	echo '</tr>';
}

/**
 * 管理页面
 *
 * @param string $action
 */
function contact_manage_page($action) {
	global $php_file;
    $referer = referer(PHP_FILE);
	
    $contactid  = isset($_GET['id'])?$_GET['id']:0;
    if ($action!='add') {
    	$_DATA  = contact_get_byid($contactid);
    }
    $name		= isset($_DATA['name'])?$_DATA['name']:null;
    $mobile		= isset($_DATA['mobile'])?$_DATA['mobile']:null;
    $email		= isset($_DATA['email'])?$_DATA['email']:null;
    $address	= isset($_DATA['address'])?$_DATA['address']:null;
    $birthday	= isset($_DATA['birthday'])?$_DATA['birthday']:null;
    $url		= isset($_DATA['url'])?$_DATA['url']:null;
    $note		= isset($_DATA['note'])?$_DATA['note']:null;
	$datetime  	= isset($_DATA['datetime'])?$_DATA['datetime']:null;
	$edittime  	= isset($_DATA['edittime'])?$_DATA['edittime']:null;
	
	$categories = isset($_DATA['category'])?$_DATA['category']:array();
	
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-user"></i> ').system_head('title').'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="contactmanage" id="contactmanage">';
    echo     '<fieldset>';
	
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	
	echo				'<div class="span5">';
	
	echo				'<div class="control-group"><label class="control-label" for="name">姓名</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="name" id="name" placeholder="联系人名字" value="'.$name.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="mobile">手机</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="mobile" id="mobile" placeholder="固定或移动电话" value="'.$mobile.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="email">邮箱</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="email" id="email" placeholder="电子邮箱 example@domain.com" value="'.$email.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="address">地址</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="address" id="address" placeholder="详细地址" value="'.$address.'">';
	echo					'</div>';
  	echo				'</div>';
	
	echo				'<div class="control-group"><label class="control-label" for="birthday">生日</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="birthday" id="birthday" placeholder="1986-01-01" value="'.$birthday.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="address">主页</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="url" id="url" placeholder="个人主页" value="'.$url.'">';
	echo					'</div>';
  	echo				'</div>';

	echo				'<div class="control-group"><label class="control-label" for="category">部门</label>';
    echo					'<div class="controls" style="height:200px; overflow:auto; width:230px;">';
	echo                   		display_ul_categories(0,$categories);
	echo					'</div>';
  	echo				'</div>';
	

	
	
	echo				'</div>';
	
	echo				'<div class="span5 note">';
	echo				  '<div class="control-group"><label class="control-label" for="note">备注</label>';
    echo					  '<div class="controls" style="margin-left:60px;">';
	echo						  '<textarea spellcheck="false" name="note" id="note" placeholder="添加备注" rows="15">'.$note.'</textarea>';
	echo					  '</div>';
  	echo				  '</div>';
	echo				'</div>';
	
	echo			'</div>';
	echo 		'</div>';

	
    echo   '</fieldset>';
    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">添加联系人</button>';
    } else {
        echo   '<button type="submit" class="btn btn-primary">更新联系人</button><input type="hidden" name="id" value="'.$contactid.'" />';
    }
    echo       '  <button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
    echo   '</p>';
    echo  '</form>';
    echo '</div>';
}

/**
 * 显示分类数
 *
 * @param int $sortid
 * @param array $categories
 * @param array $trees
 * @return string
 */
function display_ul_categories($sortid,$categories=array(),$trees=null) {
    static $func = null;
    $hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="unstyled categories"' : 'class="children unstyled"');
    if (!$func) $func = __FUNCTION__;
    if ($trees === null) $trees = taxonomy_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['taxonomyid'],$categories) && $sortid!=$tree['taxonomyid'] ? ' checked="checked"' : '';
        $main_checked = $tree['taxonomyid']==$sortid?' checked="checked"':'';
        $hl.= sprintf('<li><label class="checkbox" for="category-%d">',$tree['taxonomyid']);
        $hl.= sprintf(' <input type="checkbox" id="category-%1$d" name="category[]" value="%1$d"%3$s /> %2$s</label>',$tree['taxonomyid'],$tree['name'],$checked);
    	if (isset($tree['subs'])) {
    		$hl.= $func($sortid,$categories,$tree['subs']);
    	}
        $hl.= '</li>';
    }
    $hl.= '</ul>';
    return $hl;
}