<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 添加域名
 *
 * @param  $$userid
 * @param  $domain
 * @param  $data
 * @return array
 */
function domain_add($domain,$data=null) {
    $db = get_conn(); 
    $is_exist = $db->result("SELECT * FROM `#@_domain` WHERE `domain`='{$domain}' AND `status`='approved' LIMIT 1 OFFSET 0;");

    if(!$is_exist){
        $domainid = $db->insert('#@_domain',array(
            'domain'        => $domain,
            'status'        => 'approved',
            'addtime'       => date('Y-m-d H:i:s',time()),
            'edittime'      => date('Y-m-d H:i:s',time()),
        ));
        return domain_edit($domainid,$data);
    }

    return null; 
}

function domain_exist($domainid) {
    $domain = domain_get($domainid)['domain'];
    if(empty($domain)) return false;
    $db = get_conn(); 
    $is_exist = $db->result("SELECT * FROM `#@_domain` WHERE `domain`='{$domain}' AND `status`='approved' LIMIT 1 OFFSET 0;");
    return $is_exist;
}

/**
 * 更新域名信息
 *
 * @param int $domainid
 * @param array $data
 * @return array
 */
function domain_edit($domainid,$data) {
    $db = get_conn();
    $domainid = intval($domainid);
    $domain_rows = $meta_rows = array();
    if ($domain = domain_get($domainid)) {
        $data = is_array($data) ? $data : array();
        $meta_rows = empty($data['meta']) ? array() : $data['meta']; unset($data['meta']);
        $domain_rows = $data; $data['meta'] = $meta_rows;

        // 更新数据
        if (!empty($domain_rows)) {
           $db->update('#@_domain',$domain_rows,array('id' => $domainid));
        }
        if (!empty($meta_rows)) {
            domain_edit_meta($domainid,$meta_rows);
        }
        // 清理缓存
        domain_clean_cache($domainid);
        return array_merge($domain,$data);
    }
    return null;
}
function trash_domain($domainid=0){
    $domain = domain_get($domainid);
    if ( $domain['status'] == 'trash' )
        return false;
    $domain['status'] = 'trash';
    domain_edit($domainid, $domain);
    return true;
}
function status_domain($domainid=0, $status){
    $domain = domain_get($domainid);
    if($status){
        $domain['status'] = $status;
        domain_edit($domainid, $domain);
        return true;
    }
    return true;
}
/**
 * 查找指定的域名信息
 *
 * @param int $domain
 * @return array
 */
function domain_get($domainid) {
    $db   = get_conn();
    $ckey = sprintf('domain.%d',$domainid);
    $domain = fcache_get($ckey);
    if (fcache_not_null($domain)) return $domain;

    $rs = $db->query("SELECT * FROM `#@_domain` WHERE `id`=%d LIMIT 1 OFFSET 0;",$domainid);
    // 判断文章是否存在
    if ($domain = $db->fetch($rs)) {
        if ($meta = domain_get_meta($domain['id'])) {
            $domain['meta'] = $meta;
        }
        // 保存到缓存
        fcache_set($ckey,$domain);
        return $domain;
    }
    return null;
}
/**
 * 获取所有域名
 * @param  string $status 类型
 * @return array        所有域名的数组形式
 */
function domain_gets($status='approved') {
    $db = get_conn(); $result = array();
    $where = is_null($status) ? 'approved' : sprintf(" AND `status`='%s'",esc_sql($status));
    $rs = $db->query("SELECT `author`,`domain` FROM `#@_domain` WHERE 1 {$where} ORDER BY `id` ASC;");
    while ($row = $db->fetch($rs)) {
        $result[] = domain_get($row['id']);
    }
    return $result;
}
/**
 * 获取域名的访问信息
 *
 * @param  $domainid
 * @return array
 */
function domain_get_meta($metaid) {
    $db = get_conn(); $result = array(); $metaid = intval($metaid);
    $rs = $db->query("SELECT * FROM `#@_domain_meta` WHERE `metaid`=%d LIMIT 1 OFFSET 0;",$metaid);
    if ($result = $db->fetch($rs)) {
        return $result;
    }
    return null;
}

function domain_add_meta($domainid,$data=null) {
    $db = get_conn(); 
    $metaid = $db->insert('#@_domain_meta',array(
        'domainid'    		=> $domainid
    ));
    return domain_edit_meta($metaid,$data);
}
/**
 * 填写域名访问信息
 *
 * @param  $domainid
 * @param  $data
 * @return bool
 */
function domain_edit_meta($metaid,$data) {
    $db = get_conn(); $metaid = intval($metaid);
    $data = is_array($data) ? $data : array();
	if ($meta = domain_get_meta($metaid)) {
		if (!empty($data)) {
           $db->update('#@_domain_meta',$data,array('metaid' => $metaid));
        }
		return array_merge($meta,$data);
	}
	return null;
}
/**
 * 清理域名缓存
 *
 * @param  $domainid
 * @return bool
 */
function domain_clean_cache($domainid) {
    return fcache_delete('domain.'.$domainid);
}
/**
 * 删除一个域名信息
 *
 * @param  $domainid
 * @return bool
 */
function domain_delete($domainid) {
    $db = get_conn();
    $domainid = intval($domainid);
    if (!$domainid) return false;
    if ($domain = domain_get($domainid)) {
        $db->delete('#@_domain_meta',array('domainid' => $domainid));
        $db->delete('#@_domain',array('id' => $domainid));
        // 清理缓存
        domain_clean_cache($domainid);
        return true;
    }
    return false;
}

function domain_meta_delete($metaid) {
    $db = get_conn();
    $metaid = intval($metaid);
    if (!$metaid) return false;
    if (domain_get_meta($metaid)) {
        $db->delete('#@_domain_meta',array('metaid' => $metaid));
        return true;
    }
    return false;
}

function domain_count($status=null) {
	$where = 'WHERE　1';
	if($status!=null){
		$where = sprintf(" AND `status`='%s'",$status);
	}
    $db = get_conn(); return $db->result("SELECT COUNT(DISTINCT(`id`)) FROM `#@_domain` {$where}");
}

/**
 * [contact_get_list description]
 * @return [type] [description]
 */
function domain_get_list(){
    $db = get_conn(); $result = array();
    $rs = $db->query("SELECT DISTINCT `domain`,`author`,`userid` FROM `#@_domain` WHERE `status` = 'approved';");
    while ($row = $db->fetch($rs)) {
        $result[$row['author']][] = $row['domain'];
    }
    return $result;
}



function domain_group_get_trees(){
    $result = array();
    $domain_list = domain_group_get_list();
    foreach ($domain_list as $domainid) {
        $result[$domainid] = domain_group_get($domainid,0);
    }
    return $result;
}
function domain_group_get_list(){
    $db = get_conn(); $result = array();
    $rs = $db->query("SELECT * FROM `#@_domain_groups` WHERE 1;");
    while ($row = $db->fetch($rs)) {
        $result[] = $row['id'];
    }
    return $result;
}


/**
 * 取得分组信息
 *
 * @param string $param
 * @param int $type
 * @return array|null
 */
function domain_group_get($param,$type=0){
    $db = get_conn(); if ((int)$type>2) return null;

    switch($type){
        case 0:
            $where = sprintf("`id`=%d",esc_sql($param));
            break;
        case 1:
            $where = sprintf("`name`='%s'",esc_sql($param));
            break;
    }
    $rs = $db->query("SELECT * FROM `#@_domain_groups` WHERE {$where} LIMIT 1 OFFSET 0;");
    // 判断分组是否存在
    if ($group = $db->fetch($rs)) {
        return $group;
    }
    return null;
}

/**
 * 创建一个分组
 *
 * @param array $data
 * @return array
 */
function domain_group_add($data) {
    $db = get_conn();
    if (!is_array($data)) return false;
    $groupid = $db->insert('#@_domain_groups',$data);
    $group   = array_merge($data,array(
       'id' => $groupid,
    ));
    return $group;
}

/**
 * 删除分组
 *
 * @param int $userid
 * @return bool
 */
function domain_group_delete($groupid) {
    $db = get_conn();
    $groupid = intval($groupid);
    if (!$groupid) return false;
    if ($group = domain_group_get($groupid,0)) {
        $db->delete('#@_domain_groups',array('id' => $groupid));
        return true;
    }
    return false;
}

/**
 * 编辑分组
 * @param  [type] $groupid [description]
 * @param  [type] $data    [description]
 * @return [type]          [description]
 */
function domain_group_edit($groupid,$data) {
    $db = get_conn(); $groupid = intval($groupid);
    $data = is_array($data) ? $data : array();
    if ($group = domain_group_get($groupid, 0)) {
        // 更新数据
        if ($data) {
            $db->update('#@_domain_groups',$data,array('id'=>$groupid));
        }
        return array_merge($group,$data);
    }
    return null;
}