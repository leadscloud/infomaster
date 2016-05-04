<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 添加文章
 *
 * @param  $serial
 * @param  $remarks
 * @param  $data
 * @return array
 */
function post_add($serial,$remarks,$data=null) {
    $db = get_conn(); 

    $post_date = current_time('mysql');
    $post_date_gmt = get_gmt_from_date($post_date);

    $postid = $db->insert('#@_post',array(
        'serial'    => $serial,
        'remarks'  	=> $remarks,
        'type'     	=> 'inquiry',
        'datetime'  => time(),
        'edittime'  => time(),
        'date'      => $post_date,
        'date_gmt'  => $post_date_gmt 
    ));
    return post_edit($postid,$data);
}
/**
 * 更新文章信息
 *
 * @param int $postid
 * @param array $data
 * @return array
 */
function post_edit($postid,$data) {
    $db = get_conn();
    $postid = intval($postid);
    $post_rows = $meta_rows = array();
    if ($post = post_get($postid)) {
        $data = is_array($data) ? $data : array();      

        $category = isset($data['category']) ? $data['category'] : null;
        //$keywords = isset($data['keywords']) ? $data['keywords'] : null;
        unset($data['category']);
        $meta_rows = empty($data['meta']) ? array() : $data['meta']; unset($data['meta']);
        $post_rows = $data; $data['meta'] = $meta_rows; $data['category'] = $category;

        // 更新数据
        if (!empty($post_rows)) {
            $db->update('#@_post',$post_rows,array('postid' => $postid));
        }
        if (!empty($meta_rows)) {
            post_edit_meta($postid,$meta_rows);
        }
        // 更新分类关系
        if ($data['category']) {
            taxonomy_make_relation('category',$postid,$data['category']);
        }
        // 清理缓存
        post_clean_cache($postid);
        return array_merge($post,$data);
    }
    return null;
}
/**
 * 判断路径是否存在
 *
 * @param  $postid
 * @param  $path    必须是format_path()格式化过的路径
 * @return bool
 */
function post_path_exists($postid,$path) {
    if (strpos($path,'%ID')!==false && strpos($path,'%MD5')!==false) return false;
    $db = get_conn();
    if ($postid) {
        $sql = sprintf("SELECT COUNT(`postid`) FROM `#@_post` WHERE `path`='%s' AND `postid`<>'%d';", esc_sql($path), esc_sql($postid));
    } else {
        $sql = sprintf("SELECT COUNT(`postid`) FROM `#@_post` WHERE `path`='%s';",esc_sql($path));
    }
    return !($db->result($sql) == 0);
}
/**
 * 统计文章数量
 *
 * @param string $type
 * @return int
 */
function post_count($type,$rate=null) {
	$where = sprintf("WHERE `type`='%s'",$type);
	if($rate!=null){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$rate);
	}
    $db = get_conn(); return $db->result("SELECT COUNT(`postid`) FROM `#@_post` {$where}");
}
/**
 * 查找指定的文章
 *
 * @param int $postid
 * @return array
 */
function post_get($postid) {
    $db   = get_conn();
    $ckey = sprintf('post.%d',$postid);
    $post = fcache_get($ckey);
    if (fcache_not_null($post)) return $post;

    $rs = $db->query("SELECT * FROM `#@_post` WHERE `postid`=%d LIMIT 1 OFFSET 0;",$postid);
    // 判断文章是否存在
    if ($post = $db->fetch($rs)) {
        // 取得分类关系
        $post['category'] = taxonomy_get_relation('category',$postid);
        //$post['keywords'] = taxonomy_get_relation('post_tag',$postid);
        if ($meta = post_get_meta($post['postid'])) {
            $post['meta'] = $meta;
        }
        // 保存到缓存
        fcache_set($ckey,$post);
        return $post;
    }
    return null;
}
/**
 * 文章分类
 *
 * @param  $categories
 * @return array
 */
function post_get_taxonomy($categories) {
    $result = array();
    foreach((array)$categories as $taxonomyid) {
        $result[$taxonomyid] = taxonomy_get($taxonomyid);
    }
    return $result;
}

/**
 * 获取文章的详细信息
 *
 * @param  $postid
 * @return array
 */
function post_get_meta($postid) {
    $db = get_conn(); $result = array(); $postid = intval($postid);
    $rs = $db->query("SELECT * FROM `#@_post_meta` WHERE `postid`=%d;",$postid);
    while ($row = $db->fetch($rs)) {
        $result[$row['key']] = is_serialized($row['value']) ? unserialize($row['value']) : $row['value'];
    }
    return $result;
}
/**
 * 填写文章的详细信息
 *
 * @param  $postid
 * @param  $data
 * @return bool
 */
function post_edit_meta($postid,$data) {
    $db = get_conn(); $postid = intval($postid);
    if (!is_array($data)) return false;
    foreach ($data as $key=>$value) {
        // 查询数据库里是否已经存在
        $length = (int) $db->result(vsprintf("SELECT COUNT(*) FROM `#@_post_meta` WHERE `postid`=%d AND `key`='%s';",array($postid,esc_sql($key))));
        // update
        if ($length > 0) {
            $db->update('#@_post_meta',array(
                'value' => $value,
            ),array(
                'postid' => $postid,
                'key'    => $key,
            ));
        }
        // insert
        else {
            // 保存到数据库里
            $db->insert('#@_post_meta',array(
                'postid' => $postid,
                'key'    => $key,
                'value'  => $value,
            ));
        }
    }
    return true;
}
/**
 * 清理文章缓存
 *
 * @param  $postid
 * @return bool
 */
function post_clean_cache($postid) {
    return fcache_delete('post.'.$postid);
}
/**
 * 删除一片文章
 *
 * @param  $postid
 * @return bool
 */
function post_delete($postid) {
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;
    if ($post = post_get($postid)) {
        // 删除分类关系
        foreach($post['category'] as $taxonomyid) {
            taxonomy_delete_relation($postid,$taxonomyid);
        }
        $db->delete('#@_post_meta',array('postid' => $postid));
        $db->delete('#@_post',array('postid' => $postid));
        // 清理缓存
        post_clean_cache($postid);
        return true;
    }
    return false;
}
function post_parse_belong($postid){
    $db = get_conn();
    $postid = intval($postid);
    if (!$postid) return false;
    if ($post = post_get($postid)) {
        $landingurl = $post['landingurl'];
        $refererurl = $post['referer'];
        $belong = determine_url($landingurl,'网站所属人');
        if(!$belong)
            $belong = determine_url($refererurl,'网站所属人');
        if($belong){
            post_edit($postid, array(
                'belong' => $belong,
            ));
        }
        return true;
    }
    return false;
}

/**
 * 上一页
 *
 * @param int $sortid
 * @param int $postid
 * @param int &$preid
 * @return string
 */
function post_prepage($sortid,$postid,&$preid=0) {
    $db    = get_conn();
    $preid = $db->result(sprintf("SELECT `objectid` FROM `#@_term_relation` WHERE `taxonomyid`=%d AND `objectid`<%d ORDER BY `objectid` DESC LIMIT 1 OFFSET 0;", esc_sql($sortid), esc_sql($postid)));
    if ($preid) {
        $post = post_get($preid);
        $post['path'] = post_get_path($post['sortid'],$post['path']);
        $result = '<a href="'.ROOT.$post['path'].'">'.$post['title'].'</a>';
    } elseif($sortid) {
        $post = post_get($postid);
        $post['sort'] = taxonomy_get($post['sortid']);
        $result = '<a href="'.ROOT.$post['sort']['path'].'/">['.$post['sort']['name'].']</a>';
    } else {
        $result = '[不支持]';
    }
    return $result;
}
/**
 * 上一篇文章
 *
 * @param int $userid
 * @param int $postid
 * @return array
 */
function user_prepost($userid,$postid){
	$db    = get_conn();$post = array();
	$gmt_offset = '+8:00';
	$offset = C('System.gmt_offset');
	
	$tzstring = C('System.Timezone');
	$date_time_zone_selected = new DateTimeZone($tzstring);
	$offset = timezone_offset_get($date_time_zone_selected, date_create());
			
	$offsetHours = round(abs($offset)/3600); 
	$offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60); 
	
	$offsetString = ($offset < 0 ? '-' : '+') 
                . ($offsetHours < 10 ? '0' : '') . $offsetHours 
                . ':' 
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes; 
		
	if($postid) 
		$where = "WHERE `userid`=%d AND `postid`<%d AND DATE( CONVERT_TZ( FROM_UNIXTIME( `datetime` ), '+00:00','%s' ) ) = DATE( CONVERT_TZ( UTC_TIMESTAMP(), '+00:00','%s' ) ) AND `type`='inquiry'";
	else
		$where = "WHERE `userid`=%d AND `postid`>%d AND DATE( CONVERT_TZ( FROM_UNIXTIME( `datetime` ), '+00:00','%s' ) ) = DATE( CONVERT_TZ( UTC_TIMESTAMP(), '+00:00','%s' ) ) AND `type`='inquiry'";
	$preid = $db->result(sprintf("SELECT * FROM `#@_post` {$where} ORDER BY `postid` DESC LIMIT 1 OFFSET 0;", esc_sql($userid), esc_sql($postid),$offsetString,$offsetString));
	if ($preid) {
		 $post = post_get($preid);
	}
	return $post;
}

function post_pre_count($userid,$postid){
	$db    = get_conn();$post = array();
    $post = post_get($postid);
    $current_date = $post['datetime'];
	$gmt_offset = '+8:00';
	$offset = C('System.gmt_offset');
	
	$tzstring = C('System.Timezone');
    if(!$tzstring) $tzstring = 'Asia/Shanghai';
	$date_time_zone_selected = new DateTimeZone($tzstring);
	$offset = timezone_offset_get($date_time_zone_selected, date_create());
			
	$offsetHours = round(abs($offset)/3600); 
	$offsetMinutes = round((abs($offset) - $offsetHours * 3600) / 60); 
	
	$offsetString = ($offset < 0 ? '-' : '+') 
                . ($offsetHours < 10 ? '0' : '') . $offsetHours 
                . ':' 
                . ($offsetMinutes < 10 ? '0' : '') . $offsetMinutes; 

	if($postid) 
		$where = "WHERE `userid`=%d AND `postid`<%d AND TO_DAYS( CONVERT_TZ( FROM_UNIXTIME( `datetime` ), '+00:00','+00:00' ) ) = TO_DAYS( CONVERT_TZ( FROM_UNIXTIME( '%s' ) , '+00:00','+00:00' )  ) AND `type`='inquiry'";
	else
		$where = "WHERE `userid`=%d AND `postid`>%d AND TO_DAYS( CONVERT_TZ( FROM_UNIXTIME( `datetime` ), '+00:00','+00:00' ) ) = TO_DAYS( CONVERT_TZ( FROM_UNIXTIME( '%s' ) , '+00:00','+00:00' )) AND `type`='inquiry'";

	$count = $db->result(sprintf("SELECT COUNT(*) FROM `#@_post` {$where} ;", esc_sql($userid), esc_sql($postid),$current_date,$offsetString));
//echo sprintf("SELECT COUNT(*) FROM `#@_post` {$where} ;", esc_sql($userid), esc_sql($postid),$current_date,$offsetString);
	return $count;
}
/**
 * 下一页
 *
 * @param int $sortid
 * @param int $postid
 * @param int &$nextid
 * @return string
 */
function post_nextpage($sortid,$postid,&$nextid=0) {
    $db     = get_conn();
    $nextid = $db->result(sprintf("SELECT `objectid` FROM `#@_term_relation` WHERE `taxonomyid`=%d AND `objectid`>%d ORDER BY `objectid` ASC LIMIT 1 OFFSET 0;", esc_sql($sortid), esc_sql($postid)));
    if ($nextid) {
        $post = post_get($nextid);
        $post['path'] = post_get_path($post['sortid'],$post['path']);
        $result = '<a href="'.ROOT.$post['path'].'">'.$post['title'].'</a>';
    } elseif($sortid) {
        $post = post_get($postid);
        $post['sort'] = taxonomy_get($post['sortid']);
        $result = '<a href="'.ROOT.$post['sort']['path'].'/">['.$post['sort']['name'].']</a>';
    } else {
        $result = '[不支持]';
    }
    return $result;
}

/**
 * 同一天的信息数
 * @return [int] [post count of current day]
 */
function post_count_curdate($userid,$postid=0){
    $db = get_conn();
    $post = array();

    $curdate = current_time('mysql');

    if($postid) {
        $post = post_get($postid);
        $post_date = $post['date'];
        if($post_date=='0000-00-00 00:00:00')
            return $post['serial'];
        $where = "WHERE `userid`=%d AND `postid`<%d AND DATE( `date` ) = DATE( '{$post_date}' ) AND `type`='inquiry'";
    }
    else
        $where = "WHERE `userid`=%d AND `postid`>%d AND DATE( `date` ) = DATE( '{$curdate}' ) AND `type`='inquiry'";

    $post_count = $db->result(sprintf("SELECT COUNT(*) FROM `#@_post` {$where} ;", esc_sql($userid), esc_sql($postid)));
    
    return $post_count + 1;
}
/**
 * 显示浏览量
 *
 * @return int|string
 */
function post_gateway_views() {
    $postid  = isset($_REQUEST['postid'])  ? $_REQUEST['postid']  : 0;
    $updated = isset($_REQUEST['updated']) ? $_REQUEST['updated'] : null;
    if (post_get($postid)) {
        $db = get_conn();
        $views = $db->result(sprintf("SELECT `views` FROM `#@_post` WHERE `postid`=%d",esc_sql($postid)));
        if ($updated=='true' || $updated=='1') {
            $views++; no_cache();
            $db->update('#@_post',array('views' => $views),array( 'postid' => $postid));
        }
    } else {
        $views = 0;
    }
    return 'document.write('.esc_js($views).');';
}
/**
 * 显示评论信息
 *
 * @return
 */
function post_gateway_ajax_comment() {
    $postid  = isset($_REQUEST['postid'])  ? $_REQUEST['postid']  : 0;
    $comment_count  = comment_count($postid);
    $comment_people = comment_people($postid);
    return array($comment_count,$comment_people);
}
/**
 * 评论数量
 *
 * @return string
 */
function post_gateway_comment_count() {
    $postid = isset($_REQUEST['postid'])  ? $_REQUEST['postid']  : 0;
    $comment_count  = comment_count($postid);
    return 'document.write('.esc_js($comment_count).');';
}