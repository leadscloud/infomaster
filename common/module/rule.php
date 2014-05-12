<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 通过规则ID查询规则信息
 *
 * @param int $ruleid
 * @param string $field
 * @return array|null
 */
function rule_get_byid($ruleid) {
    $ruleid = intval($ruleid);
    $rule = rule_get($ruleid,0);
    return $rule;
}
function rule_get_bypattern($pattern,$type) {
	$db = get_conn();
    $rs = $db->query("SELECT * FROM `#@_rule` WHERE `type`='{$type}';");
	// 判断是否存在
    while ($rule = $db->fetch($rs)) {
        $patterns = unserialize($rule['pattern']);
        if (is_array($patterns)) {
            foreach ($patterns as $value) {
				foreach($pattern as $p) {
					if( $p==$value ) return $rule;
				}
            }
        }
    }
    return null;
}

/**
 * 通过模型标识查询模型信息
 *
 * @param string $ename
 * @param string $field
 * @return array|null
 */
function rule_get_bycode($code,$field='*') {
    $language = null;
    if (($pos=strpos($code,':'))!==false) {
        $language = mb_substr($code,0,$pos);
        $code     = mb_substr($code,$pos+1);
    }
    $rule = rule_get($code,1,$language);
    if ($field!='*') {
        return isset($rule[$field])?$rule[$field]:null;
    }
    return $rule;
}
/**
 * 取得规则信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function rule_get($param,$type=0) {
    $db = get_conn(); if ((int)$type>2) return null;
    $ckeys = array('rule.ruleid.','rule.code.');
    if ($type==1) {
        $ckey = sprintf('%s.',$ckeys[$type]);
    } else {
        $ckey = $ckeys[$type];
    }
    $rule = fcache_get($ckey.$param);
    if (fcache_not_null($rule)) return $rule;

    switch($type){
        case 0:
            $where = sprintf("WHERE `ruleid`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("WHERE `pattern`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_rule` {$where} LIMIT 1 OFFSET 0;");
    // 判断是否存在
    if ($rule = $db->fetch($rs)) {
        $patterns = unserialize($rule['pattern']);
        $rule['pattern'] = array();
        if (is_array($patterns)) {
            foreach ($patterns as $i=>$pattern_str) {
                $rule['pattern'][$i] = $pattern_str;
            }
        }
        // 保存到缓存
        fcache_set($ckey.$param,$rule);
        return $rule;
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
function rule_gets($type=null, $state=null) {
    $db = get_conn(); $result = array();
    $where = is_null($state) ? null : sprintf(" AND `state`='%s'",esc_sql($state));
    $where.= is_null($type) ? null : sprintf(" AND `type`='%s'",esc_sql($type));
    $rs = $db->query("SELECT * FROM `#@_rule` WHERE 1 {$where} ORDER BY `ruleid` ASC;");
    while ($row = $db->fetch($rs)) {
        $result[] = rule_get_byid($row['ruleid']);
    }
    return $result;
}
/**
 * 创建一个模型
 *
 * @param array $data
 * @return array
 */
function rule_add($data) {
    $db = get_conn();
    if (!is_array($data)) return false;
    $ruleid = $db->insert('#@_rule',$data);
    $rule   = array_merge($data,array(
       'ruleid' => $ruleid,
    ));
    return $rule;
}
/**
 * 更新模型信息
 *
 * @param int $ruleid
 * @param array $data
 * @return array|null
 */
function rule_edit($ruleid,$data) {
    $db = get_conn(); $ruleid = intval($ruleid);
    $data = is_array($data) ? $data : array();
    if ($rule = rule_get_byid($ruleid)) {
        // 更新数据
        if ($data) {
            $db->update('#@_rule',$data,array('ruleid'=>$ruleid));
        }
        // 清理用户缓存
        rule_clean_cache($ruleid);
        return array_merge($rule,$data);
    }
    return null;
}
/**
 * 清理缓存
 *
 * @param int $ruleid
 * @return bool
 */
function rule_clean_cache($ruleid) {
    if ($rule = rule_get_byid($ruleid)) {
        $ckey = 'rule.';
        foreach (array('ruleid','pattern') as $field) {
			fcache_delete($ckey.$field.'.'.$rule[$field]);
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
function rule_delete($ruleid) {
    $db = get_conn();
    $ruleid = intval($ruleid);
    if (!$ruleid) return false;
    if (rule_get_byid($ruleid)) {
        rule_clean_cache($ruleid);
        $db->delete('#@_rule',array('ruleid'=>$ruleid));
        return true;
    }
    return false;
}
