<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 取得关键词
 *
 * @param int $termid
 * @return array|null
 */
function term_get_byid($termid) {
    $db = get_conn(); $termid = intval($termid);
    $rs = $db->query("SELECT * FROM `#@_term` WHERE `termid`=%d LIMIT 1 OFFSET 0;",$termid);
    if ($term = $db->fetch($rs)) {
        return $term;
    }
    return null;
}
/**
 * 根据名称查找
 *
 * @param  $name
 * @return array|null
 */
function term_get_byname($name) {
    $db = get_conn();
    $rs = $db->query("SELECT * FROM `#@_term` WHERE `name`='%s' LIMIT 1 OFFSET 0;",$name);
    if ($term = $db->fetch($rs)) {
        return $term;
    }
    return null;
}
/**
 * 添加术语
 *
 * @param  $name
 * @return $termid
 */
function term_add($name) {
    $db = get_conn();
    $termid = $db->result(sprintf("SELECT `termid` FROM `#@_term` WHERE `name`='%s' LIMIT 1 OFFSET 0;",esc_sql($name)));
    if (!$termid) {
        $termid = $db->insert('#@_term',array(
            'name' => $name,
        ));
        // 清理缓存
        fcache_delete('terms.dicts');
    }
    return $termid;
}

/**
 * 取得分类名称列表
 *
 * @param  $category
 * @param int $num
 * @return string
 */
/*function taxonomy_get_names($category,$num=3) {
    $names = array();
    foreach($category as $i=>$taxonomyid) {
        $taxonomy = taxonomy_get($taxonomyid);
        if ($i >= $num) {
            $names[] = $taxonomy['name'].'...';
            break;
        } else {
            $names[] = $taxonomy['name'];
        }
    }
    return implode(',', $names);
}*/
function taxonomy_get_names($category){
	$names = array();
	foreach($category as $i=>$taxonomyid) {
        $taxonomy = taxonomy_get($taxonomyid);
        $names[] = $taxonomy['name'];
    }
	return $names;
}
/**
 * 统计数量
 *
 * @param string $type
 * @return int
 */
function taxonomy_count($type='category') {
    $db = get_conn(); return $db->result(sprintf("SELECT COUNT(`taxonomyid`) FROM `#@_term_taxonomy` WHERE `type`='%s';", $type));
}
/**
 * 取得分类列表
 *
 * @param string $type
 * @return array
 */
function taxonomy_get_list($type='category',$query=null) {
    $db = get_conn(); $result = array();
	if($query!=null)
		$sql = sprintf("SELECT * FROM `#@_term_taxonomy` AS tt LEFT JOIN `#@_term` AS t ON tt.`taxonomyid`=t.`termid` WHERE tt.`type`='%s' AND t.`name` LIKE '%%%%%s%%%%';",$type,$query);
	else
		$sql = sprintf("SELECT * FROM `#@_term_taxonomy` WHERE `type`='%s';",$type);
    $rs = $db->query($sql);
    while ($row = $db->fetch($rs)) {
        $result[] = $row['taxonomyid'];
    }
    return $result;
}

/**
 * 取得分类树
 *
 * @param int $parentid
 * @param string $type
 * @return array
 */
function taxonomy_get_trees($parentid=0,$type='category',$query=null) {
    $result = array(); $un = array(); $parentid = intval($parentid);
    $taxonomy_list = taxonomy_get_list($type,$query);
    foreach ($taxonomy_list as $taxonomyid) {
        $result[$taxonomyid] = taxonomy_get($taxonomyid);
    }
    // 将数组转变成树，因为使用了引用，所以不会占用太多的内存
    foreach ($result as $id => $item) {
        if ($item['parent']&&$query==null) {
            $result[$item['parent']]['subs'][$id] = &$result[$id];
            $un[] = $id;
        }
    }
    if ($parentid) {
		$result = isset($result[$parentid])?$result[$parentid]:array();
    }
    foreach($un as $v) unset($result[$v]);
    return $result;
}
/**
 * 检查分类目录是否存在
 *
 * @param  $taxonomyid
 * @param  $path        必须是format_path()格式化过的路径
 * @return bool
 */
function taxonomy_path_exists($taxonomyid,$path) {
    if (strpos($path,'%ID')!==false && strpos($path,'%MD5')!==false) return false;
    $db = get_conn();
    if ($taxonomyid) {
        $sql = sprintf("SELECT COUNT(`taxonomyid`) FROM `#@_term_taxonomy_meta` WHERE `key`='path' AND `value`='%s' AND `taxonomyid`<>'%d';", esc_sql($path), esc_sql($taxonomyid));
    } else {
        $sql = sprintf("SELECT COUNT(`taxonomyid`) FROM `#@_term_taxonomy_meta` WHERE `key`='path' AND `value`='%s';",esc_sql($path));
    }
    return !($db->result($sql) == 0);
}
/**
 * 取得分类信息
 *
 * @param int $taxonomyid
 * @return array|null
 */
function taxonomy_get($taxonomyid) {
    $db = get_conn(); $prefix = 'taxonomy.';
    $taxonomyid = intval($taxonomyid);
    $taxonomy   = fcache_get($prefix.$taxonomyid);
    if (fcache_not_null($taxonomy)) return $taxonomy;

    $rs = $db->query("SELECT * FROM `#@_term_taxonomy` WHERE `taxonomyid`=%d LIMIT 1 OFFSET 0;",$taxonomyid);
    if ($taxonomy = $db->fetch($rs)) {
        if ($term = term_get_byid($taxonomy['termid'])) {
            $taxonomy = array_merge($taxonomy,$term);
        }
        if ($meta = taxonomy_get_meta($taxonomy['taxonomyid'])) {
            foreach (array('description') as $field) {
                $taxonomy[$field] = $meta[$field]; unset($meta[$field]);
            }
            foreach (array('codename') as $field) {
                if(isset($meta[$field]))
                    $taxonomy[$field] = $meta[$field]; 
                unset($meta[$field]);
            }
            $taxonomy['meta'] = $meta;
        }
        $taxonomy['keywords'] = taxonomy_get_relation('sort_tag',$taxonomy['taxonomyid']);
        // 保存到缓存
        fcache_set($prefix.$taxonomyid,$taxonomy);
        
        return $taxonomy;
    }
    return null;
}
/**
 * 获取分类扩展信息
 *
 * @param int $taxonomyid
 * @return array
 */
function taxonomy_get_meta($taxonomyid) {
    $db = get_conn(); $result = array(); $taxonomyid = intval($taxonomyid);
    $rs = $db->query("SELECT * FROM `#@_term_taxonomy_meta` WHERE `taxonomyid`=%d;",$taxonomyid);
    while ($row = $db->fetch($rs)) {
        $result[$row['key']] = is_serialized($row['value']) ? unserialize($row['value']) : $row['value'];
    }
    return $result;
}
/**
 * 取得一个对象的分类
 *
 * @param string $type
 * @param int $objectid
 * @return array
 */
function taxonomy_get_relation($type, $objectid) {
    $db = get_conn(); $result = array(); $tt_ids = array();
    $rs = $db->query("SELECT `taxonomyid` FROM `#@_term_taxonomy` WHERE `type`='%s';",$type);
    while ($tt = $db->fetch($rs)) {
        $tt_ids[] = $tt['taxonomyid'];
    }
    $in_tt_ids = "'" . implode("', '", $tt_ids) . "'";
    $rs = $db->query("SELECT DISTINCT(`tr`.`taxonomyid`) AS `taxonomyid`,`tr`.`order` AS `order` FROM `#@_term_relation` AS `tr` LEFT JOIN `#@_term_taxonomy` AS `tt` ON `tt`.`taxonomyid`=`tr`.`taxonomyid` WHERE `tr`.`objectid`=%d AND `tt`.`taxonomyid` IN({$in_tt_ids});",$objectid);
    while ($taxonomy = $db->fetch($rs)) {
        $result[$taxonomy['order']] = $taxonomy['taxonomyid'];
    }
    ksort($result);
    return $result;
}
/**
 * 获取关键词
 *
 * @param  $keywords
 * @return string
 */
function taxonomy_get_keywords($keywords) {
    $result = array();
    foreach((array)$keywords as $taxonomyid) {
        $taxonomy = taxonomy_get($taxonomyid);
        $result[] = str_replace(chr(44), '&#44;', $taxonomy['name']);
    }
    return implode(',', $result);
}
/**
 * 建立分类关系
 *
 * @param  $type
 * @param  $objectid
 * @param  $taxonomies
 * @return bool
 */
function taxonomy_make_relation($type,$objectid,$taxonomies) {
    $db = get_conn(); $tt_ids = array(); $taxonomies = (array) $taxonomies;
    $rs = $db->query("SELECT `taxonomyid` FROM `#@_term_taxonomy` WHERE `type`='%s';",$type);
    while ($tt = $db->fetch($rs)) {
        $tt_ids[] = $tt['taxonomyid'];
    }
    // 取得分类差集,删除差集
    $tt_ids = array_diff($tt_ids,$taxonomies);
    $in_tt_ids = "'" . implode("', '", $tt_ids) . "'";
    // 先删除关系
    $rs = $db->query("SELECT DISTINCT(`tr`.`taxonomyid`) AS `taxonomyid` FROM `#@_term_relation` AS `tr` LEFT JOIN `#@_term_taxonomy` AS `tt` ON `tt`.`taxonomyid`=`tr`.`taxonomyid` WHERE `tr`.`objectid`=%d AND `tt`.`taxonomyid` IN({$in_tt_ids});",$objectid);
    while ($taxonomy = $db->fetch($rs)) {
        taxonomy_delete_relation($objectid,$taxonomy['taxonomyid']);
    }
    // 然后添加分类关系
    foreach($taxonomies as $order=>$taxonomyid) {
        $is_exist = $db->result(sprintf("SELECT COUNT(*) FROM `#@_term_relation` WHERE `taxonomyid`=%d AND `objectid`=%d;",esc_sql($taxonomyid),esc_sql($objectid)));
        if (0 < $is_exist) {
            $db->update('#@_term_relation',array(
                'order' => $order,
            ),array(
                'taxonomyid' => $taxonomyid,
                'objectid'   => $objectid,
            ));
        } else {
            $db->insert('#@_term_relation',array(
                'taxonomyid' => $taxonomyid,
                'objectid'   => $objectid,
                'order'      => $order,
            ));
        }
        // 更新文章数
        $count = $db->result(sprintf("SELECT COUNT(`objectid`) FROM `#@_term_relation` WHERE `taxonomyid`=%d;",esc_sql($taxonomyid)));
        $db->update('#@_term_taxonomy',array('count'=>$count),array('taxonomyid'=>$taxonomyid));
        taxonomy_clean_cache($taxonomyid);
    }
    return true;
}
/**
 * 删除关系
 *
 * @param  $objectid
 * @param  $taxonomyid
 * @return bool
 */
function taxonomy_delete_relation($objectid,$taxonomyid) {
    $db = get_conn();
    return $db->delete('#@_term_relation',array(
        'taxonomyid' => $taxonomyid,
        'objectid'   => $objectid,
    ));
}
/**
 * 创建分类
 *
 * @param  $type
 * @param  $name
 * @param int $parentid
 * @param  $data
 * @return array|null
 */
function taxonomy_add($type,$name,$parentid=0,$data=null) {
    $db = get_conn(); $parentid = intval($parentid);
    $data = is_array($data) ? $data : array();
    $taxonomyid = $db->insert('#@_term_taxonomy',array(
       'type'   => $type,
       'parent' => $parentid,
    ));
    $data['name'] = $name;
    return taxonomy_edit($taxonomyid,$data);
}
/**
 * 添加Tag
 *
 * @param string $name
 * @param string $type
 * @return array|null
 */
function taxonomy_add_tag($name, $type='post_tag') {
    $db = get_conn();
    $taxonomyid = $db->result(sprintf("SELECT `taxonomyid` FROM `#@_term` AS `t` LEFT JOIN `#@_term_taxonomy` AS `tt` ON `tt`.`termid`=`t`.`termid` WHERE `tt`.`type`='%s' AND `t`.`name`='%s' LIMIT 1 OFFSET 0;",esc_sql($type),esc_sql($name)));
    if (!$taxonomyid) {
        $taxonomyid = $db->insert('#@_term_taxonomy',array(
           'type'   => $type,
        ));
        taxonomy_edit($taxonomyid,array(
            'name' => $name,
        ));
    }
    return $taxonomyid;
}
/**
 * 填写分类信息
 *
 * @param int $taxonomyid
 * @param array $data
 * @return array|null
 */
function taxonomy_edit($taxonomyid,$data) {
    $db = get_conn(); $taxonomy_rows = $term_rows = $meta_rows = array();
    $data = is_array($data) ? $data : array();
    if ($taxonomy = taxonomy_get($taxonomyid)) {
        // 分析关键词
        if (isset($data['keywords']) && !empty($data['keywords'])) {
            if (is_array($data['keywords'])) {
                $keywords = $data['keywords'];
            } else {
                // 替换掉全角逗号和全角空格
                $data['keywords'] = str_replace(array('，','　'),array(',',' '),$data['keywords']);
                // 先用,分隔关键词
                $keywords = explode(',',$data['keywords']);
                // 分隔失败，使用空格分隔关键词
                if (count($keywords)==1) $keywords = explode(' ',$data['keywords']);
            }
            $taxonomies = array();
            // 移除重复的关键词
            $keywords = array_unique($keywords);
            // 去除关键词两边的空格，转义HTML
            array_walk($keywords,create_function('&$s','$s=esc_html(trim($s));'));
            // 强力插入关键词
            foreach($keywords as $key) {
                $taxonomies[] = taxonomy_add_tag($key, 'sort_tag');
            }
            // 组合关键词
            $data['keywords'] = implode(',', $keywords);
            // 创建关系
            taxonomy_make_relation('sort_tag',$taxonomyid,$taxonomies);
        }
        // 判断数据应该放在哪里
        foreach ($data as $field=>$value) {
            if ($db->is_field('#@_term_taxonomy',$field)) {
                $taxonomy_rows[$field] = $value;
            } elseif ($field=='name') {
                $term_rows[$field] = $value;
            } else {
                $meta_rows[$field] = $value;
            }
        }
        // 清理字段
        unset($meta_rows['keywords']);
        // 更新数据
        if (!empty($term_rows['name'])) $taxonomy_rows['termid'] = term_add($term_rows['name']);
        if ($taxonomy_rows) $db->update('#@_term_taxonomy',$taxonomy_rows,array('taxonomyid'=>$taxonomyid));
        if ($meta_rows) taxonomy_edit_meta($taxonomyid,$meta_rows);
        // 清理缓存
        taxonomy_clean_cache($taxonomyid);
        return array_merge($taxonomy,$data);
    }
    return null;
}
/**
 * 填写扩展信息
 *
 * @param int $taxonomyid
 * @param array $data
 * @return bool
 */
function taxonomy_edit_meta($taxonomyid,$data) {
    $db = get_conn(); $taxonomyid = intval($taxonomyid);
    $data = is_array($data) ? $data : array();
    foreach ($data as $key=>$value) {
        // 查询数据库里是否已经存在
        $length = (int) $db->result(vsprintf("SELECT COUNT(*) FROM `#@_term_taxonomy_meta` WHERE `taxonomyid`=%d AND `key`='%s';",array($taxonomyid,esc_sql($key))));
        // update
        if ($length > 0) {
            $db->update('#@_term_taxonomy_meta',array(
                'value' => $value,
            ),array(
                'taxonomyid' => $taxonomyid,
                'key'    => $key,
            ));
        }
        // insert
        else {
            // 保存到数据库里
            $db->insert('#@_term_taxonomy_meta',array(
                'taxonomyid' => $taxonomyid,
                'key'    => $key,
                'value'  => $value,
            ));
        }
    }
    return true;
}
/**
 * 清理缓存
 *
 * @param int $taxonomyid
 * @return bool
 */
function taxonomy_clean_cache($taxonomyid) {
    $taxonomyid = intval($taxonomyid);
    return fcache_delete('taxonomy.'.$taxonomyid);
}
/**
 * 删除分类
 *
 * @param int $taxonomyid
 * @return bool
 */
function taxonomy_delete($taxonomyid) {
    $db = get_conn();
    $taxonomyid = intval($taxonomyid);
    if (!$taxonomyid) return false;
    if ($taxonomy = taxonomy_get($taxonomyid)) {
        // 删除分类关系
        $db->delete('#@_term_relation',array('taxonomyid' => $taxonomyid));
        // 删除分类扩展信息
        $db->delete('#@_term_taxonomy_meta',array('taxonomyid' => $taxonomyid));
        // 删除分类信息
        $db->delete('#@_term_taxonomy',array('taxonomyid' => $taxonomyid));
        // 清理缓存
        taxonomy_clean_cache($taxonomyid);
    }
    return false;
}