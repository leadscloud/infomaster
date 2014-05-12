<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 通过模型ID查询模型信息
 *
 * @param int $modelid
 * @param string $field
 * @return array|null
 */
function model_get_byid($modelid,$field='*') {
    $modelid = intval($modelid);
    $model = model_get($modelid,0);
    if ($field!='*') {
        return isset($model[$field])?$model[$field]:null;
    }
    return $model;
}
/**
 * 通过模型标识查询模型信息
 *
 * @param string $ename
 * @param string $field
 * @return array|null
 */
function model_get_bycode($code,$field='*') {
    $language = null;
    $model = model_get($code,1);
    if ($field!='*') {
        return isset($model[$field])?$model[$field]:null;
    }
    return $model;
}
/**
 * 取得模型信息
 *
 * @param string $param
 * @param int $type
 * @param string $language  语言
 * @return array|null
 */
function model_get($param,$type=0) {
    $db = get_conn(); if ((int)$type>2) return null;
    $ckeys = array('model.modelid.','model.code.');
	$ckey = $ckeys[$type];
    $model = fcache_get($ckey.$param);
    if (fcache_not_null($model)) return $model;

    switch($type){
        case 0:
            $where = sprintf("WHERE `modelid`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("WHERE `code`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_model` {$where} LIMIT 1 OFFSET 0;");
    // 判断是否存在
    if ($model = $db->fetch($rs)) {
        $fields = unserialize($model['fields']);
        $model['fields'] = array();
        if (is_array($fields)) {
            foreach ($fields as $i=>$field_str) {
                parse_str($field_str,$field);
                $field['_n'] = '_'.$field['n'];
                $model['fields'][$i+1] = $field;
            }
        }
        // 保存到缓存
        fcache_set($ckey.$param,$model);
        return $model;
    }
    return null;
}
/**
 * 查询模型
 *
 * @param string $type  post,sort
 * @param string $state enabled,disabled
 * @return array
 */
function model_gets($type=null, $state=null) {
    $db = get_conn(); $result = array();
    $where = is_null($state) ? null : sprintf(" AND `state`='%s'",esc_sql($state));
    $where.= is_null($type) ? null : sprintf(" AND `type`='%s'",esc_sql($type));
    $rs = $db->query("SELECT * FROM `#@_model` WHERE 1 {$where} ORDER BY `modelid` ASC;");
    while ($row = $db->fetch($rs)) {
        $result[] = model_get_byid($row['modelid']);
    }
    return $result;
}
/**
 * 创建一个模型
 *
 * @param array $data
 * @return array
 */
function model_add($data) {
    $db = get_conn();
    if (!is_array($data)) return false;
    $modelid = $db->insert('#@_model',$data);
    $model   = array_merge($data,array(
       'modelid' => $modelid,
    ));
    return $model;
}
/**
 * 更新模型信息
 *
 * @param int $modelid
 * @param array $data
 * @return array|null
 */
function model_edit($modelid,$data) {
    $db = get_conn(); $modelid = intval($modelid);
    $data = is_array($data) ? $data : array();
    if ($model = model_get_byid($modelid)) {
        // 更新数据
        if ($data) {
            $db->update('#@_model',$data,array('modelid'=>$modelid));
        }
        // 清理用户缓存
        model_clean_cache($modelid);
        return array_merge($model,$data);
    }
    return null;
}
/**
 * 清理缓存
 *
 * @param int $modelid
 * @return bool
 */
function model_clean_cache($modelid) {
    if ($model = model_get_byid($modelid)) {
        $ckey = 'model.';
        foreach (array('modelid','code') as $field) {
            if ($field=='modelid') {
                fcache_delete(sprintf('%s%s.%s',$ckey,$field,$model[$field]));
            } else {
                fcache_delete(sprintf('%s%s.%s.%s',$ckey,$field,$model['language'],$model[$field]));
            }
        }
    }
    return true;
}
/**
 * 删除
 *
 * @param int $userid
 * @return bool
 */
function model_delete($modelid) {
    $db = get_conn();
    $modelid = intval($modelid);
    if (!$modelid) return false;
    if (model_get_byid($modelid)) {
        model_clean_cache($modelid);
        $db->delete('#@_model',array('modelid'=>$modelid));
        return true;
    }
    return false;
}
/**
 * 取得控件的数据库字段类型
 *
 * @param string $type
 * @return string|array
 */
function model_get_types($type=null) {
    $types = array(
        'input'    => 'Input',                   // 输入框
        'textarea' => 'Textarea',                // 文本框
        'radio'    => 'Radio',                   // 单选框
        'checkbox' => 'Check box',               // 复选框
        'select'   => 'Drop-down menu',          // 下拉菜单
        'basic'    => 'Basic editor',            // 简易编辑器
        'editor'   => 'Adv editor',              // 内容编辑器
        'date'     => 'Date Selector',           // 日期选择器
        'upfile'   => 'File upload box',         // 文件上传框
    );
    return empty($type) ? $types : $types[$type];
}
/**
 * 模型字段转换为HTML
 *
 * @param array $field
 * @return string
 */
function model_field2html($field) {
    $hl = '';
    switch ($field['t']) {
        case 'input':
            $hl.= '<input class="text" id="'.$field['_n'].'" name="'.$field['_n'].'" type="text" style="width:'.$field['w'].'" maxlength="'.$field['c'].'" value="'.$field['d'].'" />';
            break;
        case 'textarea':
            $hl.= '<textarea class="text" name="'.$field['_n'].'" id="'.$field['_n'].'" style="width:'.$field['w'].'" rows="8">'.$field['d'].'</textarea>';
            break;
        case 'select':
            $values = explode("\n",$field['s']);
            $hl.= '<select name="'.$field['_n'].'" id="'.$field['_n'].'" edit="true" style="width:'.$field['w'].'">';
            foreach ($values as $k=>$v) {
                $v = trim($v);
                if ($v!='') {
                    $vs = explode(':',$v);
                    $vs = array_map('esc_html',$vs); $vs[1] = isset($vs[1])?$vs[1]:$vs[0];
                    $selected = !empty($field['d']) ? (strval($vs[0])==strval($field['d']) ? ' selected="selected"' : null) : null;
                    $hl.= '<option value="'.$vs[0].'"'.$selected.'>'.$vs[1].'</option>';
                }
            }
            $hl.= '</select>';
            break;
        case 'radio': case 'checkbox':
            $values = explode("\n",$field['s']);
            $hl.= '<div id="'.$field['_n'].'" style="width:'.$field['w'].'">';
            foreach ($values as $k=>$v) {
                $v = trim($v);
                if ($v!='') {
                    $vs = explode(':',$v);
                    $vs = array_map('esc_html',$vs); $vs[1] = isset($vs[1])?$vs[1]:$vs[0];
                    $checked = !empty($field['d']) ? (instr($vs[0],$field['d']) ? ' checked="checked"' : null) : null;
                    $hl.= '<label><input name="'.$field['_n'].($field['t']=='checkbox'?'[]':null).'" type="'.$field['t'].'" value="'.$vs[0].'"'.$checked.' />'.$vs[1].'</label>';
                }
            }
            $hl.= '</div>';
            break;
        case 'basic': case 'editor':
            $options = array();
            $options['width'] = $field['w'];
            $plugins = implode(',', $field['a']);
            if ($field['t']=='basic') {
                $options['tools']  = 'Blocktag,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,BackColor,|,Align,List,Outdent,Indent,|,Link,'.$plugins;
                $options['height'] = '120';
            } elseif ($field['t']=='editor') {
                $options['height'] = '280';
                $options['tools']  = 'Source,Preview,Pastetext,|,Blocktag,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,'.
                                     'BackColor,Removeformat,|,Align,List,Outdent,Indent,|,Link,Unlink,'.$plugins.',|,Fullscreen';

            }
            $hl.= editor($field['_n'],$field['d'],$options);
            break;
        case 'upfile':
            $hl.= '<input class="text" id="'.$field['_n'].'" name="'.$field['_n'].'" type="text" style="width:'.$field['w'].'" />&nbsp;<button type="button">浏览...</button>';
            break;
    }
    return $hl;
}