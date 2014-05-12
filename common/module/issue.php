<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 添加反馈
 *
 * @param int $postid
 * @param string $content
 * @param int $parent
 * @param array $user
 * @return int
 */
function issue_add($title,$content,$parent=0,$user=null) {
    $db = get_conn();
    $userid = isset($user['userid']) ? $user['userid'] : 0;
    $author = isset($user['name']) ? esc_html($user['name']) : '';
    $email  = isset($user['mail']) ? esc_html($user['mail']) : '';
    return $db->insert('#@_issue', array(
		'title'	   => $title,
        'author'   => $author,
        'mail'     => $email,
        'ip'       => sprintf('%u',ip2long($_SERVER['REMOTE_ADDR'])),
        'agent'    => esc_html($_SERVER['HTTP_USER_AGENT']),
        'date'     => time(),
        'content'  => $content,
        'parent'   => $parent,
        'userid'   => $userid
    ));
}
/**
 * 编辑问题
 *
 * @param int $cmtid
 * @param string $content
 * @param string $status
 * @param array $user
 * @return int
 */
function issue_edit($issueid, $title, $content, $status=null, $user=null) {
    $db = get_conn(); $sets = array();
	if ($title !== null) $sets['title'] = $title;
    if ($content !== null) $sets['content'] = $content;
    if ($status !== null) $sets['status'] = $status;
    if (isset($user['name'])) $sets['author'] = esc_html($user['name']);
    if (isset($user['mail'])) $sets['mail'] = esc_html($user['mail']);
    if (isset($user['userid'])) $sets['userid'] = esc_html($user['userid']);
    $result = $db->update('#@_issue', $sets, array('id' => $issueid));
    return $result;
}

function issue_close($issueid){
    global $_USER;
    $userid = $_USER['userid'];
    $issue = issue_get($issueid);
    $status = array();
    if( $userid == $issue['userid'] || $_USER['roles']=='ALL'){
        if(issue_edit($issueid,null,null,'closed',null))
            $status['message'] ='关闭问题成功.';
        else
            $status['message'] ='关闭问题失败.';
    } else {
        $status['message'] ='仅限本人关闭该问题.';
    }
	return $status;
}
function issue_open($issueid){
	return issue_edit($issueid,null,null,'open',null);
}

/**
 * 取得一条反馈信息
 *
 * @param int $cmtid
 * @return array
 */
function issue_get($issueid) {
    static $issues = array();
    if (isset($issues[$issueid]))
        return $issues[$issueid];
    
    $db = get_conn();
    $rs = $db->query("SELECT * FROM `#@_issue` WHERE `id`=%d;", $issueid);
    if ($data = $db->fetch($rs)) {
        $issues[$issueid] = $data;
    }
    return $issues[$issueid];
}
/**
 * 取得评论树
 *
 * @param int $postid
 * @param int $parentid
 * @return array
 */
function issue_get_trees($parentid=0) {
    static $trees;
    if (!$trees) {
        $db = get_conn();
        $rs = $db->query("SELECT * FROM `#@_issue` WHERE 1;");
        while ($data = $db->fetch($rs)){
            $data['ip']      = long2ip($data['ip']);
            $data['ip'] = substr_replace($data['ip'], '*', strrpos($data['ip'], '.')+1);
            $trees[$data['id']] = $data;
        }
    }
	
    // 将数组转变成树，因为使用了引用，所以不会占用太多的内存
    foreach ($trees as $id => $item) {
        if ($item['parent']) {
            $trees[$id]['parents'] = &$trees[$item['parent']];
        }
    }
	print_r($trees);
    if ($parentid) {
        $result = isset($trees[$parentid]['parents']) ? $trees[$parentid]['parents'] : null;
    } else {
        $result = $trees;
    }
    return $result;
}
/**
 * 删除问题
 *
 * @param int $cmtid
 * @return int
 */
function issue_delete($issueid) {
    $db = get_conn();
    $result = $db->delete('#@_issue', array('id' => $issueid));
    return $result;
}


/**
 * 评论数
 *
 * @param int $postid
 * @param string $status
 * @return int
 */
function issue_count($issueid,$status='all') {
    $where = 'WHERE `parent`=0';
    if ($issueid) {
        $where.= sprintf(" AND `parent`='%d'", $issueid);
    }
	if ($status != 'all') {
        $where.= sprintf(" AND `status`='%s'", strval($status));
    }
    return get_conn()->result("SELECT COUNT(`id`) FROM `#@_issue` {$where};");
}

function issue_comment_count($issueid,$status='all') {
    $where = 'WHERE 1';
    if ($issueid) {
        $where.= sprintf(" AND `parent`='%d'", $issueid);
    }
	if ($status != 'all') {
        $where.= sprintf(" AND `status`='%s'", strval($status));
    }
    return get_conn()->result("SELECT COUNT(`id`) FROM `#@_issue` {$where};");
}
/**
 * 反馈人数
 *
 * @return int
 */
function issue_people() {
    $db = get_conn(); return $db->result("SELECT COUNT(DISTINCT(`author`)) FROM `#@_issue` WHERE 1;");
}
/**
 * 回复盖楼
 *
 * @param array $comment
 * @param array $sblock
 * @return mixed
 */
function issue_parse_reply($comment, $sblock) {
    static $func; if (!$func) $func = __FUNCTION__;
    $tpl = new Template();
    $sblock['inner'] = $tpl->get_block_inner($sblock);
    $tpl->clean();
    $tpl->set_var(array(
        'cmtid'   => $comment['cmtid'],
        'avatar'  => get_avatar($comment['mail'], 16, 'mystery'),
        'author'  => $comment['author'] ? $comment['author'] : __('Anonymous'),
        'email'   => $comment['mail'],
        'url'     => !strncmp($comment['url'], 'http://', 7) ? $comment['url'] : 'http://' . $comment['url'],
        'ip'      => $comment['ip'],
        'address' => $comment['ipaddr'],
        'content' => nl2br($comment['content']),
        'agent'   => $comment['agent'],
        'date'    => $comment['date'],
    ));
    if (isset($comment['parents'])) {
        $tpl->set_var('contents_deep', $func($comment['parents'], $sblock));
    }
    return $tpl->parse($sblock['inner']);
}