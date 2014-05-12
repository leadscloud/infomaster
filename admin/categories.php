<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '分类（部门）');
system_head('scripts',array('js/categories'));
// 动作
$method  = isset($_REQUEST['method'])?$_REQUEST['method']:null;
// 所属
$parent_file = 'categories.php';
// 权限检查
current_user_can('categories');

switch ($method) {
    // 强力插入
    case 'new':
        // 重置标题
        system_head('title', '添加新分类（部门）');
        system_head('styles', array('css/xheditor.plugins'));
        system_head('scripts', array('js/xheditor'));
        // 添加JS事件
        system_head('loadevents', 'sort_manage_init');
        include ADMIN_PATH . '/admin-header.php';
        // 显示页面
        category_manage_page('add');
        include ADMIN_PATH . '/admin-footer.php';
        break;
    // 活塞式运动，你懂得。。。
    case 'edit':
        // 重置标题
        system_head('title', '编辑分类（部门）');
        //system_head('styles', array('css/xheditor.plugins'));
        //system_head('scripts', array('js/xheditor'));
        //system_head('jslang', system_editor_lang());
        // 添加JS事件
        system_head('loadevents', 'sort_manage_init');
        include ADMIN_PATH . '/admin-header.php';
        category_manage_page('edit');
        include ADMIN_PATH . '/admin-footer.php';
        break;
    // 保存
	case 'save':
        $taxonomyid  = isset($_POST['taxonomyid'])?$_POST['taxonomyid']:0;
        if (validate_is_post()) {
            $parent   = isset($_POST['parent']) ? $_POST['parent'] : '0';
            $name     = isset($_POST['name']) ? $_POST['name'] : null;
			
            $description = isset($_POST['description']) ? $_POST['description'] : null;

            validate_check(array(
                array('name', VALIDATE_EMPTY, '分类名称不能为空。'),
                array('name', VALIDATE_LENGTH, '分类名称长度必须%d至%d个字符。', 1, 30),
            ));

            if ($description) {
                validate_check(array(
                    array('description', VALIDATE_LENGTH, '描述信息不能超过255个字符.', 0, 255),
                ));
            }

            // 安全有保证，做爱做的事吧！
            if (validate_is_ok()) {
                $data = array(
                    'description' => esc_html($description),
                );
                // 编辑
                if ($taxonomyid) {
                    $data['parent'] = esc_html($parent);
                    $data['name']   = esc_html($name);
                    taxonomy_edit($taxonomyid, $data);
                    $result = '分类已更新';
                }
                // 强力插入了
                else {
                    $parent   = esc_html($parent);
                    $name     = esc_html($name);
                    $taxonomy = taxonomy_add('category', $name, $parent, $data);
                    $taxonomyid = $taxonomy['taxonomyid'];
                    $result = '分类创建成功.';
                }
				ajax_success($result,"InfoSYS.redirect('".PHP_FILE."');");
				//ajax_confirm($result, "InfoSYS.redirect('" . PHP_FILE . "?method=new');", "InfoSYS.redirect('" . PHP_FILE . "');");

            }
        }
	    break;
	// 批量动作
	case 'delete':
		//$action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
		if (empty($listids)) {
	    	ajax_error('没有选择任何项目.');
	    }
		foreach ($listids as $taxonomyid) {
	    	taxonomy_delete($taxonomyid);
	    }
	    ajax_success('删除分类成功.',"InfoSYS.redirect('".PHP_FILE."');");
	    break;
    // 获取扩展字段
	case 'extend-attr':
        $model  = null; $hl = '';
	    $mcode  = isset($_REQUEST['model'])?$_REQUEST['model']:null;
	    $sortid = isset($_REQUEST['sortid'])?$_REQUEST['sortid']:0;
        $suffix = C('HTMLFileSuffix');
        if ($sortid) {
            $taxonomy = taxonomy_get($sortid);
        }
        if ($mcode) {
            $model = model_get_bycode($mcode);
            $path  = isset($taxonomy['list'])?$taxonomy['list']:$model['list'];
        } else {
            $path  = isset($taxonomy['list'])?$taxonomy['list']:'list'.$suffix;
        }
        header('X-InfoSYS-List: '.$path);
	    if ($model) {
	    	foreach ($model['fields'] as $field) {
                if (isset($taxonomy['meta'][$field['n']])) {
                    $field['d'] = $taxonomy['meta'][$field['n']];
                }
	    		$hl.= '<tr>';
                $hl.=    '<th><label for="'.$field['_n'].'">'.$field['l'];
                if (!empty($field['h'])) {
                    $hl.=    '<span class="resume">'.$field['h'].'</span>';
                }
                $hl.=        '</label>';
                $hl.=    '</th>';
                $hl.=    '<td>'.model_field2html($field).'</td>';
                $hl.= '</tr>';
	    	}
	    }
        ajax_return($hl);
	    break;
    default:
	    system_head('loadevents','sort_list_init');
		$query = isset($_GET['s']) ? $_GET['s'] : '';
		if($query!=null){
	    	$sorts = taxonomy_get_trees(0,'category',$query);
		}else{
			$sorts = taxonomy_get_trees();
		}
        include ADMIN_PATH.'/admin-header.php';
		
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-flag"></i> 分类管理</h3>';
		echo '</div>';
		
		
		
		echo '<div id="categories">';
		table_nav('top');
		//echo	'<form action="'.PHP_FILE.'?method=bulk" method="post" name="sortlist" id="sortlist">';
		echo 		'<table class="table table-striped table-hover table-bordered">';
		echo 			'<thead>';
		table_thead();
		echo			'</thead>';
		echo           	'<tfoot>';
        table_thead();
        echo           	'</tfoot>';
		echo			'<tbody>';
		if ($sorts) {
            echo            display_tr_categories($sorts);
        } else {
            echo           '<tr><td colspan="4">尚未添加分类</td></tr>';
        }
		echo			'</tbody>';
		echo 		'</table>';
		//echo   '</form>';
		table_nav('bottom');
		echo '</div>';
		
		
		

        include ADMIN_PATH.'/admin-footer.php';
        break;
}

/**
 * 批量操作
 *
 */
function table_nav($side) {
	echo '<div class="table-nav clearfix">';
	echo 	'<div class="pull-left btn-group">';
	echo		'<button class="btn btn-small" onclick="javascript:;InfoSYS.redirect(\''.referer().'\')"><i class="icon-arrow-up"></i> 返回上级</button> ';
	//echo		'<button class="btn btn-small" id="select" onclick="javascript:;" data-toggle="button"><i class="icon-check"></i> 全选</button> ';
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加分类</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-minus"></i> 删除分类</button> ';
	echo		'<button class="btn btn-small" name="refresh"><i class="icon-refresh"></i>刷新</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	echo	'</div>';
	if ($side == 'top') {
	echo 	'<div class="pull-right form-search btn-group"><form action="" method="get">';
	echo		'<div class="input-append"> <input class="span2 search-query" style="padding:2px 14px;height:21px;" name="s" type="text"> <button class="btn  btn-small" type="submit" onclick="javascript:;">搜索</button></div></form>';
	
	echo 	'</div>';
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
	echo     '<th>分类名称</th>';
	echo     '<th>分类模型</th>';
	echo     '<th>分类简介</th>';
	echo     '<th>记录数</th>';
	echo '</tr>';
}
/**
 * 显示分类表格树
 *
 * @param array $sorts
 * @param int $n
 * @return string
 */
function display_tr_categories($sorts,$n=0) {
    static $func = null; if (!$func) $func = __FUNCTION__;
    $hl = ''; $space = str_repeat('&mdash; ',$n);
	
    foreach ($sorts as $sort) {		
        $href    = PHP_FILE.'?method=edit&taxonomyid='.$sort['taxonomyid'];
        $hl.= '<tr id="category-'.$sort['taxonomyid'].'">';
        $hl.=   '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$sort['taxonomyid'].'" /></td>';
        $hl.=   '<td><span class="space">'.$space.'</span><a class="black" href="'.$href.'">'.$sort['name'].'</a></td>';
        $hl.=   '<td>'.(isset($sort['model'])?$sort['model']:'').'</td>';
		$hl.=   '<td>'.$sort['description'].'</td>';
		$hl.=   '<td>'.$sort['count'].'</td>';
        $hl.= '</tr>';
        if (isset($sort['subs'])) {
    		$hl.= $func($sort['subs'],$n+1);
    	}
    }
    return $hl;
}

/**
 * 管理页面
 *
 * @param string $action
 */
function category_manage_page($action) {
    $taxonomyid = isset($_GET['taxonomyid']) ? $_GET['taxonomyid'] : 0;
    if ($action != 'add') {
        $_SORT = taxonomy_get($taxonomyid);
    }
	//print_r($_SORT);
    $parent = isset($_SORT['parent']) ? $_SORT['parent'] : null;
    $name   = isset($_SORT['name']) ? $_SORT['name'] : null;
    $mcode  = isset($_SORT['model']) ? $_SORT['model'] : null;
    //$model  = $mcode ? model_get_bycode($mcode) : array('langcode'=>'');
    $path   = isset($_SORT['path']) ? $_SORT['path'] : null;
    $list   = isset($_SORT['list']) ? $_SORT['list'] : null;
    //$page   = isset($_SORT['page']) ? $_SORT['page'] : null;
    //$models = model_gets('Category', 'enabled');
    //$keywords = isset($_SORT['keywords']) ? taxonomy_get_keywords($_SORT['keywords']) : null;
    $description = isset($_SORT['description']) ? $_SORT['description'] : null;
	
	echo	'<div class="module-header">';
	echo		'<h3>';
	if ($action=='add') {
		echo		'<i class="icon-plus"></i> 添加分类';
	}else{
		echo		'<i class="icon-flag"></i> 编辑分类';
	}
	echo		'</h3>';
	echo	'</div>';
	
    echo '<div class="wrap form-horizontal">';
	echo    '<form action="' . PHP_FILE . '?method=save" method="post" name="sortmanage" id="sortmanage">';
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	echo				'<div class="control-group"><label class="control-label" for="inputEmail">上级目录</label>';
    echo					'<div class="controls"><select name="parent" id="parent">';
	echo						'<option value="0">&mdash; 无父级 &mdash;</option>';
	echo						dropdown_categories($taxonomyid, $parent);
	echo					'</select></div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="inputEmail">分类名称</label>';
    echo					'<div class="controls"><input type="text" name="name" id="name" placeholder="分类名称" value="'.$name.'"></div>';
  	echo				'</div>';
	
	echo                '<div class="control-group">';
    echo                	'<label class="control-label" for="description">简介<br /><span class="resume">(最大250个字)</span></label>';
    echo                	'<div class="controls"><textarea class="text" cols="70" rows="5" id="description" name="description">' . $description . '</textarea></div>';
    echo            	'</div>';
	echo			'</div>';
	echo 		'</div>';

    echo    '<p class="submit">';
    if ($action == 'add') {
        echo    '<button type="submit" class="btn btn-primary">添加分类</button> ';
    } else {
        echo    '<button type="submit" class="btn btn-primary">更新分类</button><input type="hidden" name="taxonomyid" value="' . $taxonomyid . '" /> ';
    }
    echo        '<button type="button" class="btn" onclick="InfoSYS.redirect(\'' . PHP_FILE . '\')">返回</button>';
    echo    '</p>';
    echo    '</form>';
    echo '</div>';
}
