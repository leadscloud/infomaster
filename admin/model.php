<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
// 标题
system_head('title',  '字段管理');
system_head('styles', array('css/model'));
system_head('scripts',array('js/model'));
// 动作
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;

switch ($method) {
    // 强力插入
	case 'new':
        // 重置标题
	    system_head('title','添加新字段');
        // 权限检查
	    current_user_can('model-new');
	    // 添加JS事件
	    system_head('loadevents','model_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
        // 显示页面
	    model_manage_page('add');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 活塞式运动，你懂得。。。
	case 'edit':
	    // 所属
        $parent_file = 'model.php';
	    // 重置标题
	    system_head('title', '编辑字段');
        // 权限检查
	    current_user_can('model-edit');
	    // 添加JS事件
	    system_head('loadevents','model_manage_init');
	    include ADMIN_PATH.'/admin-header.php';
	    model_manage_page('edit');
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 保存
	case 'save':
        $modelid = isset($_POST['modelid'])?$_POST['modelid']:null;
	    $purview = $modelid?'model-edit':'model-new';
	    current_user_can($purview);

        if (validate_is_post()) {
            $type     = isset($_POST['type'])?$_POST['type']:null;
            $name     = isset($_POST['name'])?$_POST['name']:null;
            $code     = isset($_POST['code'])?$_POST['code']:null;
            $path     = isset($_POST['path'])?$_POST['path']:null;
            $list     = isset($_POST['list'])?$_POST['list']:null;
            $page     = isset($_POST['page'])?$_POST['page']:null;
            $fields   = isset($_POST['field'])?$_POST['field']:null;
            $language = isset($_POST['language'])?$_POST['language']:language();
            $langcode = sprintf('%s:%s',$language,$code);

            validate_check(array(
                // 模型名不能为空
                array('name',VALIDATE_EMPTY,'字段名字为空。'),
                // 模型名长度必须是2-30个字符
                array('name',VALIDATE_LENGTH,'模型名称长度必须%d至%d个字符。',1,30),
            ));


            if ($modelid) {
                // 模型存在
                if ($model = model_get_bycode($langcode)) {
                    if ($model['modelid']==$modelid) {
                        $is_exist = false;
                    } else {
                        $is_exist = true;
                    }
                } else {
                    $is_exist = false;
                }
            } else {
                $is_exist = model_get_bycode($langcode) ? true : false;
            }

            validate_check(array(
                // 模型标识不能为空
                array('code',VALIDATE_EMPTY,'模型标识不能为空。'),
                // 模型标识长度必须是2-30个字符
                array('code',VALIDATE_LENGTH,'模型标识长度必须%d至%d个字符。',1,30),
                // 模型标识已存在
                array('code',!$is_exist,'模型标识已存在，请重新输入。'),
            ));
           

            // 安全有保证，做爱做的事吧！
            if (validate_is_ok()) {
                switch ($type) {
                    case 'Category': $page = $path = null; break;
                    case 'Post': default: $list = null; break;
                }
                $info = array(
                    'type'     => esc_html($type),
                    'code'     => esc_html($code),
                    'name'     => esc_html($name),
                    'path'     => esc_html($path),
                    'list'     => esc_html($list),
                    'page'     => esc_html($page),
                    'language' => esc_html($language),
                );
                if (current_user_can('model-fields',false)) {
                    $info['fields'] = serialize($fields);
                }
                // 编辑
                if ($modelid) {
                    model_edit($modelid,$info);
                    ajax_success('模型更新成功。',"InfoSYS.redirect('".PHP_FILE."');");
                } 
                // 强力插入了
                else {
                    model_add($info);
                    ajax_success('模型创建成功。',"InfoSYS.redirect('".PHP_FILE."');");
                }
            }
        }
	    break;
	// 批量动作
	case 'bulk':
	    $action  = isset($_POST['action'])?$_POST['action']:null;
	    $listids = isset($_POST['listids'])?$_POST['listids']:null;
	    if (empty($listids)) {
	    	ajax_error('没有选择任何项目。');
	    }
	    switch ($action) {
	        // 删除
	        case 'delete':
	            current_user_can('model-delete');
	            foreach ($listids as $modelid) {
	            	model_delete($modelid);
	            }
	            ajax_success('模型删除成功。',"InfoSYS.redirect('".PHP_FILE."');");
	            break;
	        // 启用
	        case 'enabled':
	            foreach ($listids as $modelid) {
	            	model_edit($modelid,array(
	            	  'state' => 'enabled'
	            	));
	            }
	            ajax_success('模型启用成功。',"InfoSYS.redirect('".PHP_FILE."');");
	            break;
	        // 禁用
	        case 'disabled':
	            foreach ($listids as $modelid) {
	            	model_edit($modelid,array(
	            	  'state' => 'disabled'
	            	));
	            }
	            ajax_success('模型禁用成功。',"InfoSYS.redirect('".PHP_FILE."');");
	            break;
	        // 导出
	        case 'export':
	            // 批量导出，打包成zip
	            break;
            default:
                ajax_alert('参数错误。');
                break;
	    }
	    break;
	// 导出
	case 'export':
	    current_user_can('model-export');
	    break;
	// 导入
	case 'import':
	    current_user_can('model-import');
	    include ADMIN_PATH.'/admin-header.php';
        
        include ADMIN_PATH.'/admin-footer.php';
	    break;
	// 字段管理
	case 'field':
	    current_user_can('model-fields');
	    $id = isset($_POST['id'])?$_POST['id']:null;
	    $l  = isset($_POST['l'])?$_POST['l']:null;
	    $h  = isset($_POST['h'])?$_POST['h']:null;
	    $n  = isset($_POST['n'])?$_POST['n']:null;
	    $so = isset($_POST['so'])?$_POST['so']:null;
	    $t  = isset($_POST['t'])?$_POST['t']:null;
	    $w  = isset($_POST['w'])?$_POST['w']:'auto';
	    $v  = isset($_POST['v'])?$_POST['v']:null;
	    $s  = isset($_POST['s'])?$_POST['s']:null;
	    $a  = isset($_POST['a'])?$_POST['a']:null;
	    $c  = isset($_POST['c'])?$_POST['c']:255;
	    $d  = isset($_POST['d'])?$_POST['d']:null;
	    $verify = array(
	       "不能为空"             => 'IS_EMPTY|'.__('The field value is empty.'),
	       '固定长度'               => 'LENGTH_LIMIT|'.__('The field value length must be %d-%d characters.').'|1-100',
	       '相同的两个值'            => 'IS_EQUAL|'.__('Same the two fields.').'|[field]',
	       'E-mail'                     => 'IS_EMAIL|'.__('The e-mail address isn\'t correct.'),
	       '英文字母'   => 'IS_LETTERS|'.__('This field value is not a letter.'),
	       '匹配数字'              => 'IS_NUMERIC|'.__('This field value is not a number.'),
	       '匹配网址'               => 'IS_URL|'.__('This field value is not a URL.'),
	       '自定义验证'          => 'CUSTOM|'.__('Error Message'),
	    );
	    $hl = '<div class="wrapper">';
	    $hl.= '<a href="javascript:;" class="help">'.get_icon('f1').'</a>';
	    $hl.= '<form id="model-field-table">';
	    $hl.= '<table class="model-field-table">';
	    $hl.=    '<tr><th><label for="field_l">'._x('Label','field').'</label></th><td><input class="text" id="field_l" name="l" type="text" size="35" value="'.$l.'" />';
	    $hl.=    '<label for="field_is_help"><input type="checkbox" id="field_is_help"'.($h?' checked="checked"':null).' />'.__('Need help').'</label></td></tr>';
	    $hl.=    '<tr id="field_help" class="hide"><th class="vt"><label for="field_h">'._x('Help','field').'</label></th><td><textarea class="text" name="h" id="field_h" rows="2" cols="40">'.$h.'</textarea></td></tr>';
	    $hl.=    '<tr><th><label for="field_n">'._x('Field','field').'</label></th><td><input class="text" id="field_n" name="n" type="text" size="30" value="'.$n.'" />';
	    $hl.=    '<label for="can_search"><input type="checkbox" id="can_search" name="so" value="1"'.($so?' checked="checked"':null).' />'.__('Can search').'</label></td></tr>';
	    $hl.=    '<tr><th><label for="field_t">'._x('Type','field').'</label></th><td>';
	    $hl.=        '<select id="field_t" name="t">'; $types = model_get_types();
	    foreach ($types as $type=>$text) {
	        $selected = $type==$t?' selected="selected"':null;
	    	$hl.=      '<option value="'.$type.'"'.$selected.'>'.$text.'</option>';
	    }
	    $hl.=        '</select>';
	    $hl.=        '<label for="field_w">'.__('Width').'</label><select name="w" id="field_w" edit="true" default="'.$w.'">';
        $hl.=          '<option value="auto">'.__('Auto').'</option>';
	    for($i=1;$i<=16;$i++){
            $hl.=      '<option value="'.($i*50).'px">'.($i*50).'px</option>';
        }
	    $hl.=        '</select>';
	    $hl.=        '<label for="field_is_verify"><input type="checkbox" id="field_is_verify"'.($v?' checked="checked"':null).' />'.__('Need to verify').'</label>';
	    $hl.=    '</td></tr>';
	    $hl.=    '<tr id="field_verify" class="hide">';
	    $hl.=        '<th class="vt"><label for="field_sv">'.__('Verify rule').'</label></th>';
	    $hl.=        '<td><select name="sv" id="field_sv">';
	    foreach ($verify as $text=>$val) {
            $hl.=       '<option value="'.$val.'">'.$text.'</option>';
        }
	    $hl.=        '</select>&nbsp;<a href="javascript:;" rule="+">'.get_icon('b3').'</a><a href="javascript:;" rule="-">'.get_icon('b4').'</a>';
	    $hl.=        '<br/><textarea class="text" name="v" id="field_v" rows="3" cols="40">'.$v.'</textarea></td>';
	    $hl.=    '</tr>';
	    $hl.=    '<tr id="field_serialize" class="hide"><th class="vt"><label for="field_s">'._x('Serialize','field').'</label></th><td><textarea class="text" name="s" id="field_s" rows="3" cols="40">'.$s.'</textarea></td></tr>';
	    $hl.=    '<tr id="field_toolbar" class="hide"><th class="vt"><label>'.__('Toolbar').'</label></th><td class="toolbar">';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Img', $a)?' checked="checked"':null).' value="Img" /> '.__('Insert Image').'</label>';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Flash', $a)?' checked="checked"':null).' value="Flash" /> '.__('Insert Flash').'</label>';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Flv', $a)?' checked="checked"':null).' value="Flv" /> '.__('Insert Flv').'</label>';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Emot', $a)?' checked="checked"':null).' value="Emot" /> '.__('Insert Emote').'</label>';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Table', $a)?' checked="checked"':null).' value="Table" /> '.__('Insert Table').'</label>';
        $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('GoogleMap', $a)?' checked="checked"':null).' value="GoogleMap" /> '.__('Insert Google Map').'</label>';
	    $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Pagebreak', $a)?' checked="checked"':null).' value="Pagebreak" /> '.__('Insert Pagebreak').'</label>';
        $hl.=        '<label><input type="checkbox" name="a[]"'.(instr('Removelink', $a)?' checked="checked"':null).' value="Removelink" /> '.__('Remove external links').'</label>';
        $hl.=    '</td></tr>';
	    $hl.=    '<tr id="field_length" class="hide">';
	    $hl.=        '<th><label for="field_c">'._x('Length','field').'</label></th>';
	    $hl.=        '<td><select name="c" id="field_c" edit="true" default="'.$c.'">';
	    foreach (array(10,20,30,50,100,255) as $v) {
            $hl.=       '<option value="'.$v.'">'.$v.'</option>';
        }
	    $hl.=        '</select></td>';
	    $hl.=    '</tr>';
	    $hl.=    '<tr id="field_default" class="hide"><th><label for="field_d">'._x('Default','field').'</label></th><td><input class="text" id="field_d" name="d" type="text" size="40" value="'.$d.'" /></td></tr>';
	    $hl.= '</table>';
	    $hl.= '<div class="buttons"><button type="button" rel="save">'.__('Save').'</button><button type="button" rel="close">'.__('Cancel').'</button></div>';
	    $hl.= '<input type="hidden" name="id" value="'.$id.'" />';
	    $hl.= '</form></div>';
	    ajax_return($hl);
	    break;
	default:
	    current_user_can('model-list');
	    system_head('loadevents','model_list_init');
	    $models = model_gets();
        include ADMIN_PATH.'/admin-header.php';
		echo '<div class="module-header">';
		echo	'<h3><i class="icon-tags"></i> 字段管理 </h3>';
		echo '</div>';
		
        echo '<div id="modellist">';
        echo   '<form action="'.PHP_FILE.'?method=bulk" method="post" name="modellist" id="modellist">';
        table_nav();
        echo       '<table class="data-table table table-striped table-hover table-bordered">';
        echo           '<thead>';
        table_thead();
        echo           '</thead>';
        echo           '<tfoot>';
        table_thead();
        echo           '</tfoot>';
        echo           '<tbody>';
        if ($models) {
            foreach ($models as $model) {
				$_MODEL = model_get_byid($model['modelid']);
				$fields   = isset($_MODEL['fields'])?$_MODEL['fields']:null;
				
                $href = PHP_FILE.'?method=edit&modelid='.$model['modelid'];
                $actions = '<span class="edit"><a href="'.$href.'">'.__('Edit').'</a> | </span>';
                //$actions.= '<span class="export"><a href="'.PHP_FILE.'?method=export&modelid='.$model['modelid'].'">'.__('Export').'</a> | </span>';
                $actions.= '<span class="enabled"><a href="javascript:;" onclick="model_state(\'enabled\','.$model['modelid'].')">'.__('Enabled').'</a> | </span>';
                $actions.= '<span class="disabled"><a href="javascript:;" onclick="model_state(\'disabled\','.$model['modelid'].')">'.__('Disabled').'</a> | </span>';
                $actions.= '<span class="delete"><a href="javascript:;" onclick="model_delete('.$model['modelid'].')">'.__('Delete').'</a></span>';
				if ($fields) {
					foreach ($fields as $i=>$field) {
						$actions  = '<span class="edit"><a href="#'.$i.'">'.__('Edit').'</a> | </span>';
						$actions .= '<span class="delete"><a href="#'.$i.'">'.__('Delete').'</a></span>';
						$textarea = '<textarea class="hide" name="field[]">'.http_build_query($field).'</textarea>';
						echo                    '<tr id="field-index-'.$i.'" index="'.$i.'">';
						echo                       '<td class="check-column"><input type="checkbox" name="listids[]" value="'.$i.'" /></td>';
						echo                       '<td><strong class="edit"><a href="#'.$i.'">'.$field['l'].'</a></strong><br/><div class="row-actions">'.$actions.'</div>'.$textarea.'</td>';
						echo                       '<td>'.$field['n'].'</td>';
						echo                       '<td>'.model_get_types($field['t']).'</td>';
						echo                       '<td>'.(empty($field['d'])?'NULL':$field['d']).'</td>';
						echo                    '</tr>';
					}
				} else {
					echo                        '<tr class="empty"><td colspan="5" class="tc">'.__('No record!').'</td></tr>';
				}
            }
        } else {
            echo           '<tr><td colspan="5" class="tc">No record!</td></tr>';
        }
        echo           '</tbody>';
        echo       '</table>';
        table_nav();
        echo   '</form>';
        echo '</div>';
        include ADMIN_PATH.'/admin-footer.php';
        break;
}

/**
 * 批量操作
 *
 */
function table_nav() {
    echo '<div class="table-nav clearfix">';
    echo     '<div class="pull-left btn-group">';
    echo		'<button class="btn btn-small" onclick="javascript:;InfoSYS.redirect(\''.referer().'\');return false;"><i class="icon-arrow-up"></i> 返回</button> ';
	echo		'<a class="btn btn-small" href="'.PHP_FILE.'?method=new"><i class="icon-plus"></i> 添加字段</a> ';
	echo		'<button class="btn btn-small" name="delete" onclick="return false;"><i class="icon-minus"></i> 删除</button> ';
	echo		'<input type="hidden" name="referer" value="'.referer().'"> ';
	echo		'<button class="btn btn-small" name="refresh" onclick="javascript:;return false;"><i class="icon-refresh"></i> 刷新</button> ';
    echo     '</div>';
    echo '</div>';
}
/**
 * 表头
 *
 */
function table_thead() {
    echo '<tr>';
    echo     '<th class="check-column"><input type="checkbox" name="select" value="all" /></th>';
    echo     '<th>表单文字</th>';
    echo     '<th>字段名</th>';
    echo     '<th>输入类型</th>';
    echo     '<th>默认</th>';
    echo '</tr>';
}

/**
 * 管理页面
 *
 * @param string $action
 */
function model_manage_page($action) {
    global $method;
    $referer = referer(PHP_FILE);
    $modelid  = isset($_GET['modelid'])?$_GET['modelid']:0;
    if ($action!='add') {
    	$_MODEL = model_get_byid($modelid);
    }
    $name     = isset($_MODEL['name'])?$_MODEL['name']:null;
    $type     = isset($_MODEL['type'])?$_MODEL['type']:'Post';
    $code     = isset($_MODEL['code'])?$_MODEL['code']:null;
    $fields   = isset($_MODEL['fields'])?$_MODEL['fields']:null;
	
	
	$id = isset($_POST['id'])?$_POST['id']:null;
	$l  = isset($_POST['l'])?$_POST['l']:null;
	$h  = isset($_POST['h'])?$_POST['h']:null;
	$n  = isset($_POST['n'])?$_POST['n']:null;
	$so = isset($_POST['so'])?$_POST['so']:null;
	$t  = isset($_POST['t'])?$_POST['t']:null;
	$w  = isset($_POST['w'])?$_POST['w']:'auto';
	$v  = isset($_POST['v'])?$_POST['v']:null;
	$s  = isset($_POST['s'])?$_POST['s']:null;
	$a  = isset($_POST['a'])?$_POST['a']:null;
	$c  = isset($_POST['c'])?$_POST['c']:255;
	$d  = isset($_POST['d'])?$_POST['d']:null;
	
	echo	'<div class="module-header">';
	echo    	'<h3>'.($action=='add'?'<i class="icon-plus"></i> ':'<i class="icon-tag"></i> ').system_head('title').'</h3>';
	echo	'</div>';
    echo '<div class="wrap form-horizontal">';
    echo   '<form action="'.PHP_FILE.'?method=save" method="post" name="modelmanage" id="modelmanage">';
    echo     '<fieldset>';
	echo 		'<div class="widget">';
	echo			'<div class="widget-header"><i class="icon-cog"></i><h3>基本设置</h3></div>';
	echo			'<div class="widget-content">';
	echo				'<div class="control-group"><label class="control-label" for="type">模型类型</label>';
    echo					'<div class="controls">';
	echo                      '<label class="radio inline" for="type_post"><input type="radio" name="type" id="type_post" value="Post"'.($type=='Post'?' checked="checked"':'').' />'._x('Post','model').'</label>';
    echo                      '<label class="radio inline" for="type_sort"><input type="radio" name="type" id="type_sort" value="Category"'.($type=='Category'?' checked="checked"':'').' />'._x('Category','model').'</label>';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="name">模型名称</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="name" id="name" placeholder="模型名称(必填)" value="'.$name.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="code">模型标识</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="code" id="code" placeholder="模型标识(必填，唯一)" value="'.$code.'">';
	echo					'</div>';
  	echo				'</div>';
	
	echo				'<div class="control-group"><label class="control-label" for="field_l">表单文字</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="l" id="field_l" value="'.$l.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="field_n">字段名</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="n" id="field_n" value="'.$n.'">';
	echo					'</div>';
  	echo				'</div>';
	echo				'<div class="control-group"><label class="control-label" for="field_t">输入类型</label>';
    echo					'<div class="controls">';
	echo			          '<select id="field_t" name="t">'; $types = model_get_types();
	foreach ($types as $type=>$text) {
		$selected = $type==$t?' selected="selected"':null;
		echo			        '<option value="'.$type.'"'.$selected.'>'.$text.'</option>';
	}
	echo        			  '</select>';
	echo					'</div>';
  	echo				'</div>';
	
	echo				'<div class="control-group hide"><label class="control-label" for="field_s">序列值</label>';
    echo					'<div class="controls">';
	echo						'<textarea class="text" name="s" id="field_s" rows="3" cols="40">'.$s.'</textarea>';
	echo					'</div>';
  	echo				'</div>';
	
	echo				'<div class="control-group"><label class="control-label" for="field_d">默认</label>';
    echo					'<div class="controls">';
	echo						'<input type="text" name="d" id="field_d" value="'.$code.'">';
	echo					'</div>';
  	echo				'</div>';

	echo				'<div class="control-group"><label class="control-label" for="status">状态</label>';
    echo					'<div class="controls">';
	echo						'<input type="checkbox" name="status" '.(($action=='add'||$status)?'checked="checked"':'').'>';
	echo					'</div>';
  	echo				'</div>';
	echo			'</div>';
	echo 		'</div>';

    echo     '</fieldset>';
    echo     '<p class="submit">';
    if ($action=='add') {
        echo   '<button type="submit" class="btn btn-primary">添加字段</button> ';
    } else {
        echo   '<button type="submit" class="btn btn-primary">更新字段</button><input type="hidden" name="modelid" value="'.$modelid.'" /> ';
    }
    echo       '<button type="button" class="btn" onclick="InfoSYS.redirect(\''.$referer.'\')">返回</button>';
    echo     '</p>';
    echo   '</form>';
    echo '</div>';
}
/**
 * 表头
 *
 */
function fields_table_thead() {
    echo '<tr class="nodrop">';
    echo     '<th class="check-column"><input type="checkbox" name="select" value="all" /></th>';
    echo     '<th>'._x('Label','field').'</th>';
    echo     '<th>'._x('Field','field').'</th>';
    echo     '<th>'._x('Type','field').'</th>';
    echo     '<th>'._x('Default','field').'</th>';
    echo '</tr>';
}
/**
 * 批量操作
 *
 */
function fields_actions() {
    echo '<div class="actions">';
    echo     '<button type="button" class="delete">'._x('Delete','field').'</button>';
    echo     '<button type="button" class="addnew">'._x('Add New','field').'</button>';
    echo '</div>';
}