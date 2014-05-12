<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 通过联系人ID查询联系人信息
 *
 * @param int $id
 * @return array|null
 */
function contact_get_byid($id) {
    $id = intval($id);
    return contact_get($id,0);
}
/**
 * 通过联系人姓名查询联系人信息
 *
 * @param string $name
 * @return array|null
 */
function contact_get_byname($name) {
    return contact_get($name,1);
}
/**
 * 取得联系人信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function contact_get($param,$type=0){
    $db = get_conn(); if ((int)$type>2) return null;
    $ckeys = array('contact.id.','contact.name.');
    $ckey  = $ckeys[$type];
    $contact  = fcache_get($ckey.$param);
    if (fcache_not_null($contact)) return $contact;

    switch($type){
        case 0:
            $where = sprintf("WHERE `id`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("WHERE `name`='%s'",esc_sql($param));
            break;
    }
	
    $rs = $db->query("SELECT * FROM `#@_contact` {$where} LIMIT 1 OFFSET 0;");
    // 判断联系人是否存在
    if ($contact = $db->fetch($rs)) {
        // 保存到缓存
        fcache_set($ckey.$param,$contact);
        return $contact;
    }
    return null;
}

/**
 * 创建联系人
 *
 * @param array $data
 * @return array
 */
function contact_add($data=null) {
    $db = get_conn();
    if (!is_array($data)) return false;
    $contactid = $db->insert('#@_contact',$data);
    $contact   = array_merge($data,array(
       'id' => $contactid,
    ));
    return $contact;
}
/**
 * 编辑联系人信息
 *
 * @param int $contactid
 * @param array $data
 * @return array|null
 */
function contact_edit($contactid,$data) {
    $db = get_conn();
    $contactid = intval($contactid);
    $data = is_array($data) ? $data : array();
    if ($contact = contact_get_byid($contactid)) {
        // 更新数据
        if ($data) {
            $db->update('#@_contact',$data,array('id'=>$contactid));
        }
        // 清理用户缓存
        contact_clean_cache($contactid);
        return array_merge($contact,$data);
    }
    return null;
}
/**
 * 清理联系人缓存
 *
 * @param int $contactid
 * @return bool
 */
function contact_clean_cache($contactid) {
    if ($contact = contact_get_byid($contactid)) {
        $ckey = 'contact.';
        foreach (array('id','name') as $field) {
            fcache_delete($ckey.$field.'.'.$contact[$field]);
        }
    }
    return true;
}
/**
 * 删除联系人
 *
 * @param int $contactid
 * @return bool
 */
function contact_delete($contactid) {
    $db = get_conn();
    $contactid = intval($contactid);
    if (!$contactid) return false;
    if ($contact = contact_get_byid($contactid)) {
        contact_clean_cache($contactid);
        $db->delete('#@_contact',array('id' => $contactid));
        return true;
    }
    return false;
}

/**
 * 获得用户列表
 *
 */
function contact_get_trees(){
	$result = array();
    $contact_list = contact_get_list();
    foreach ($contact_list as $contactid) {
        $result[$contactid] = contact_get_byid($contactid);
    }
	return $result;
}
function contact_get_list(){
	$db = get_conn(); $result = array();
	$rs = $db->query("SELECT `id` FROM `#@_contact`;");
	while ($row = $db->fetch($rs)) {
		$result[] = $row['id'];
    }
	return $result;
}