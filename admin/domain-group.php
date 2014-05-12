<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '域名分组管理');
//system_head('styles', array('css/user'));

// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
        // 重置标题
	    system_head('title', '添加分组');
        // 权限检查
	    current_user_can('domain-group-new');
	    // 添加JS事件
		system_head('scripts',array('js/domain'));
	    system_head('loadevents','group_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
        // 显示页面
	    group_manage_page('add');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	case 'edit':
	    // 所属
        $parent_file = 'user.php';
        // 重置标题
	    system_head('title', '编辑分组');
	    // 权限检查
	    current_user_can('domain-group-edit');
	    // 添加JS事件
		system_head('scripts',array('js/domain'));
	    system_head('loadevents','group_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    group_manage_page('edit');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 保存用户
	case 'save':
		system_head('scripts',array('js/domain'));
	    $groupid = isset($_POST['groupid'])?$_POST['groupid']:null;
	    current_user_can($groupid?'domain-group-edit':'domain-group-new');
	    
        if (validate_is_post()) {
            $groupid  = isset($_POST['groupid'])?$_POST['groupid']:null;
            $groupname  = isset($_POST['groupname'])?$_POST['groupname']:null;
			
            // 验证
            validate_check(array(
                // 分组名不能为空
                array('groupname',VALIDATE_EMPTY,'分组名还没有填写。'),
                // 用户名长度必须是2-30个字符
                array('groupname',VALIDATE_LENGTH,'分组名的长度必须在 %d-%d 个字符串。',2,30),
            ));
			
            // 验证通过
            if (validate_is_ok()) {
                $groupname = esc_html($groupname);
				$group_info	= array(
					'name'			=> $groupname,
                 );
                // 编辑
                if ($groupid) {
                    domain_group_edit($groupid,$group_info);
                    ajax_success('分组已更新。',"InfoSYS.redirect('".PHP_FILE."');");
                } 
                // 强力插入
                else {
                    domain_group_add($group_info);
                    ajax_success('分组创建成功。',"InfoSYS.redirect('".PHP_FILE."');");
                }
            }
        }
	    break;
	//删除用户组
	case 'delete':
		$listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('你没有选择任何项目！');
	    }
		current_user_can('group-delete');
		foreach ($listids as $groupid) {
			domain_group_delete($groupid);
		}
		ajax_success('分组已删除.',"InfoSYS.redirect('".referer()."');");
		break;
	default:
	    current_user_can('domain-group-list');
		system_head('scripts',array('js/domain'));
	    system_head('loadevents','group_list_init');
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$query    = array('page' => '$');
		$conditions = array();
		if ($search) {
			$where = "WHERE 1";
			$query['query'] = $search;
			$fields = array('name');
			foreach($fields as $field) {
				$conditions[] = sprintf("BINARY UCASE(`g`.`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
			}
            $where.= ' AND ('.implode(' OR ', $conditions).')';
			$sql = "SELECT DISTINCT(`g`.`id`) FROM `#@_domain_groups` as `g` {$where} ORDER BY `g`.`id` ASC";
		} else {
			$sql = "SELECT `id` FROM `#@_domain_groups` ORDER BY `id` ASC";
		}
		$result = pages_query($sql);
		// 分页地址
        $page_url   = PHP_FILE.'?'.http_build_query($query);
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-tags"></i> 域名分组</h3>';
		echo '</div>';
		
		
		
		echo '<div id="grouplist">';
		table_nav('top',$page_url);
		//echo	'<form action="'.PHP_FILE.'?method=bulk" method="post" name="sortlist" id="sortlist">';
		echo 		'<table class="table table-striped table-hover table-bordered">';
		echo 			'<thead>';
		table_thead();
		echo			'</thead>';
		echo           	'<tfoot>';
        table_thead();
        echo           	'</tfoot>';
		echo			'<tbody>';
		if ($result) {
			while ($data = pages_fetch($result)) {
	            $group = domain_group_get($data['id']);
				$href = PHP_FILE.'?method=edit&groupid='.$group['id'];
	            echo           '<tr id="group-'.$group['id'].'">';
	            echo               '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$group['id'].'" /></td>';
	            echo               '<td> <a class="black" href="'.$href.'">'.$group['id'].'</a></td>';
	            echo               '<td>'.$group['name'].'</td>';
	            echo               '<td>'.$group['default_group'].'</td>';
	            echo           '</tr>';
	        }
    	}else{
    		echo  '<tr><td colspan="4" class="tc">没有任何记录!</td></tr>';
    	}
		echo			'</tbody>';
		echo 		'</table>';
		//echo   '</form>';
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
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加分组</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-minus"></i> 删除</button> ';
	echo		'<button class="btn btn-small" name="refresh" onclick="javascript:;return false;"><i class="icon-refresh"></i> 刷新</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
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
	echo     '<th>ID</th>';
	echo     '<th>分组名</th>';
	echo     '<th>默认用户组</th>';
	echo '</tr>';
}

/**
 * 用户管理页面
 *
 * @param string $action
 */
function group_manage_page($action) {
    $referer = referer(PHP_FILE);
    $groupid  = isset($_GET['groupid'])?$_GET['groupid']:0;
    if ($action!='add') {
    	$group  = domain_group_get($groupid);
    }

    $groupname = isset($group['name'])?$group['name']:null;
	$accdefault = isset($group['accdefault'])?$group['accdefault']:null;
	$admin	= isset($group['admin'])?$group['admin']:null;

	
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-group"></i> ').system_head('title').'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="groupmanage" id="groupmanage">';
    echo     '<fieldset>';
	
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	echo				'<div class="control-group"><label class="control-label" for="username">分组名</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="groupname" id="groupname" value="'.$groupname.'">';
	echo					'</div>';
  	echo				'</div>';

	echo			'</div>';
	echo 		'</div>';
	
    echo   '</fieldset>';
    echo   '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">添加分组</button>';
    } else {
        echo   '<button type="submit" class="btn btn-primary">更新分组</button><input type="hidden" name="groupid" value="'.$groupid.'" />';
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
function display_ul_top_categories($sortid,$categories=array(),$trees=null) {
    static $func = null;
    $hl = sprintf('<ul %s>',is_null($func) ? 'id="sortid" class="unstyled categories"' : 'class="children unstyled"');
    if (!$func) $func = __FUNCTION__;
    if ($trees === null) $trees = taxonomy_get_trees();
    foreach ($trees as $i=>$tree) {
        $checked = instr($tree['taxonomyid'],$categories) && $sortid!=$tree['taxonomyid'] ? ' checked="checked"' : '';
        $main_checked = $tree['taxonomyid']==$sortid?' checked="checked"':'';
        $hl.= sprintf('<li><label class="checkbox" for="category-%d">',$tree['taxonomyid']);
        $hl.= sprintf(' <input type="checkbox" id="category-%1$d" name="category[]" value="%1$d"%3$s /> %2$s</label>',$tree['taxonomyid'],$tree['name'],$checked);
    	
        $hl.= '</li>';
    }
    $hl.= '</ul>';
    return $hl;
}