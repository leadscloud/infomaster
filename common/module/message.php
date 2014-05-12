<?php
/**
 * +---------------------------------------------------------------------------+
 * | 私信管理  private messages
 * +---------------------------------------------------------------------------+
 * | 2013-6-26  |
 * +---------------------------------------------------------------------------+
 */
defined('COM_PATH') or die('Restricted access!');

/**
 * 发送私信
 *
 * @param int $postid
 * @param string $content
 * @param int $parent
 * @param array $user
 * @return int
 */
function sendpm($data){
	global $_USER;
	if(is_array($data)){
		$to = user_get_byname($data['to_user']);
		if(empty($to)) {
			$reply['status'] = false;
			$reply['message'] = 'Your Message was not delivered. Please make sure the username is correct.';
			return $reply;
		}
		
		$data['to_user'] = $to['userid'];
		$data['from_user'] = $_USER['userid'];
		
		if(empty($data['subject']) || empty($data['message'])){
			$reply['status'] = false;
			$reply['message'] = 'Your PM must have both a title and a message.';
			return  $reply;
		}
		
		if(message_add($data)){
			$reply['status'] = true;
			$reply['message'] = 'Your PM was delivered.';
		}else {
			$reply['status'] = false;
			$reply['message'] = 'An error occured. Your Message was not delivered.';
		}
		
		$_SESSION['last_pm'] = time();
		return $reply;
	}
	
}
/**
 * 取得私信信息
 *
 * @param int $pmid
 * @return array|null
 */
function message_get($pmid) {
	static $messages = array();
    if (isset($messages[$pmid]))
        return $messages[$pmid];
    
    $db = get_conn();
    $rs = $db->query("SELECT * FROM `#@_messages` WHERE `id`=%d;", $pmid);
    if ($data = $db->fetch($rs)) {
        $messages[$pmid] = $data;
    }
    return $messages[$pmid];
}
/**
 * 添加一个私信
 *
 * @param array $data
 * @return array
 */
function message_add($data) {
	$db = get_conn();
    if (!is_array($data)) return false;
    $pmid = $db->insert('#@_messages',$data);
	if(!$pmid) return false;
    $message   = array_merge($data,array(
       'id' => $pmid,
    ));
    return $message;
}
/**
 * 更新私信内容
 *
 * @param int $pmid
 * @param array $data
 * @return array|null
 */
function message_edit($pmid,$data) {
	global $_USER;
    $db = get_conn(); $pmid = intval($pmid);
    $data = is_array($data) ? $data : array();
    if ($message = message_get($pmid)) {
        // 更新数据
        if ($data) {
            $db->update('#@_messages',$data,array('id'=>$pmid, 'to_user'=>$_USER['userid']));
        }
        return array_merge($message,$data);
    }
    return null;
}
/**
 * 删除私信
 *
 * @param int $pmid
 * @return int
 */
function message_delete($pmid) {
	global $_USER;
    $db = get_conn();
    $result = $db->delete('#@_messages', array('id' => $pmid, 'to_user'=>$_USER['userid']));
    return $result;
}
/**
 * 私信数
 *
 * @param int $userid
 * @param string $status
 * @return int
 */
function message_count($status='all') {
	global $_USER; $userid = $_USER['userid'];
    $db = get_conn(); $where = 'WHERE 1';
	
    if ($userid && $status == 'inbox') {
        $where.= sprintf(" AND `to_user`='%d'", $userid);
    }
	if ($userid && $status == 'sent') {
        $where.= sprintf(" AND `from_user`='%d'", $userid);
    }
    if ($status == 'read' || $status == 'unread') {
        $where.= sprintf(" AND `status`='%s'", strval($status));
		$where.= sprintf(" AND `to_user`='%d'", $userid);
    }
	if ($status == 'all') {
        $where.= sprintf(" AND `to_user`='%d' OR `from_user`='%d'", intval($userid), intval($userid));
    }
    return $db->result("SELECT COUNT(`id`) FROM `#@_messages` {$where};");
}

/**
 * 获取自己的私信
 *
 * @param int $pmid
 * @return array
 */
function pm_get($pmid){
	global $_USER;
	$db   = get_conn();
	
	$pmid = intval($pmid);
	$message['status'] = false;
	$result = $db->query(sprintf("SELECT p.message, p.subject, p.status, u.name as sender FROM #@_messages as p INNER JOIN #@_user as u ON userid = from_user WHERE id = %d AND to_user = %d LIMIT 1",$pmid,$_USER['userid']));
	if($result){
		while($row = $db->fetch($result)){
			$message['status']	= true;
			$message['message'] = $row['message'];
			$message['subject'] = $row['subject'];
			$message['sender'] 	= $row['sender'];
			$message['pmid'] 	= $pmid;
			$message['unread']	= ($row['status'] == 'unread' ? true : false);
		}
	}
	
	if(!empty($message['unread']) && $message['unread']){
		$markpm = $db->query('UPDATE #@_messages SET status="read" WHERE `id` = '.$pmid);
	}
	
	return $message;
}
/**
 * 删除自己的私信
 *
 * @param int $pmid
 * @return int
 */
function pm_delete($pmid){
	global $_USER;
	$db   = get_conn();
	$pmid = intval($pmid);
	$result = $db->query(sprintf("DELETE FROM `#@_messages` WHERE `id` = %d AND `to_user` = %d",$pmid,$_USER['userid']));
	if($result){
		$reply['status'] = true;
	}else{
		$reply['status'] = false;
	}
	//$reply['pm_max'] = 1000;
	return $reply;
}

function check_pms(){
	global $_USER;
	$db   = get_conn();
	$result = $db->query(sprintf("SELECT (SELECT COUNT(id) FROM #@_messages WHERE to_user = u.userid AND status = 'unread') as pm_count FROM `#@_user` as u WHERE u.userid = %d",$_USER['userid']));
	if($result){
		while($row = $db->fetch($result)){
			$reply['unread_pms'] = $row['pm_count'];
			$reply['status'] = true;
		}
	}else {
		$reply['status'] = false;
	}
	return $reply;
}