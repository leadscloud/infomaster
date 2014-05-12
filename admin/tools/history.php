<?php
// 加载公共文件
include dirname(__FILE__).'/../admin.php';
// 查询管理员信息
$_USER = user_current();
// 方法
$method = isset($_REQUEST['method'])?$_REQUEST['method']:null;
switch ($method) {
	case 'delete':
		$days  = isset($_POST['days'])?$_POST['days']:60;
		history_purge_db($days);
		ajax_success('清除成功!');
		break;
	default:
		// 权限验证
		current_user_can('history');
		$referer = referer(PHP_FILE,false);
		// 标题
		system_head('title',  '历史记录');
		system_head('scripts',array('js/history'));
		
		$query    = array('page' => '$');
		$search   = isset($_REQUEST['query'])?$_REQUEST['query']:'';
		$userid   = isset($_REQUEST['userid'])?$_REQUEST['userid']:'';
		$objtype   = isset($_REQUEST['type'])?$_REQUEST['type']:'';
		$search	  = esc_html(trim($search));
		$conditions = array();
		$where = "WHERE 1";
		
		if ($search) {
			$query['query'] = $search;
			$fields = array('action','objectname','description','datetime','objectsubtype');
			foreach($fields as $field) {
				$conditions[] = sprintf("BINARY UCASE(`%s`) LIKE UCASE('%%%%%s%%%%')",$field,esc_sql($search));
			}
			$where.= ' AND ('.implode(' OR ', $conditions).')';
		}
		if($userid) {
			$query['userid'] = $userid;
			$where.= sprintf(" AND (`userid`=%d)",esc_sql($userid));
		}
		if($objtype) {
			$query['type'] = $objtype;
			$where.= sprintf(" AND (`objecttype`='%s')",esc_sql($objtype));
		}
		
		$sql = "SELECT DISTINCT(`id`) FROM `#@_history` {$where} ORDER BY `id` DESC";

		//设置每页显示数
		pages_init(50);
		$result = pages_query($sql);
		// 分页地址
		$page_url   = PHP_FILE.'?'.http_build_query($query);
			
		include ADMIN_PATH.'/admin-header.php';
		echo '<div class="module-header"><h3><i class="icon-info-sign"></i> 历史记录</h3></div>';
		echo   '<div class="row">';
		echo     '<div class="span12">';
		echo       '<p>以下显示了60天内系统发生的各种操作事件. </p>';
		echo       '<p><i class="icon-umbrella"></i> <small>- 目前仅能显示用户的登陆与注销记录,未来完善记录询盘及用户操作的信息.</small></p>';
		echo       '<form  class="form-inline">';
		echo         '<div class="controls controls-row">';
		echo           '<select name="type" class="span2">';
        echo             '<option value="">请选择类型</option>';
        echo             drop_history_type_select($objtype);
        echo           '</select>';
        echo           '<select name="userid" class="span2">';
        echo             '<option value="">请选择用户</option>';
        echo             drop_history_user_select($userid);
        echo           '</select>';
        echo           '<input class="span2" type="text" name="query" placeholder="输入任意关键词">';
        echo           '<button type="submit" class="btn btn-success span1" onclick="javascript:;"><i class="icon-search"></i> 搜索</button>';
        echo           '<button class="btn btn-danger span" onclick="deleteHistory(60);return false;"><i class="icon-trash"></i> 删除60天前数据</button>';
		echo         '</div>';
		echo       '</form>';
		echo       '<table class="table table-striped table-hover table-history">';
		echo         '<thead>';
		echo           '<tr>';
		echo             '<th width="20%">日期</th>';
		echo             '<th width="10%">用户</th>';
		echo             '<th width="10%">目标类型</th>';
		echo             '<th width="10%">目标对象</th>';
		echo             '<th width="50%">行为</th>';
		echo           '</tr>';
		echo         '</thead>';
		echo         '<tbody>';
		if ($result) {
			while ($data = pages_fetch($result)) {
				$history	= history_get($data['id']);
				$details	= $history['description'];
				if(!empty($details)){
					$details = ' <a href="#" class="view-detail">详情 <i class="icon-double-angle-down"></i></a><div class="detail hide">'.nl2br($details).'</div>';
				}
				$user = user_get_byid($history['userid']);
				echo '<tr>';
				echo   '<td><i class="icon-time"></i> '.$history['datetime'].'</td>';
				echo   '<td> '.(empty($user['nickname'])?$user['name']:$user['nickname']).'</td>';
				echo   '<td> '.$history['objecttype'].'</td>';
				echo   '<td> '.$history['objectname'].'</td>';
				echo   '<td> '.$history['action'].$details.'</td>';
				echo '</tr>';
			}
		}
		echo         '</tbody>';
		echo       '</table>';
		echo pages_list($page_url);
		echo     '</div>';
		echo   '</div>';
		echo '</div>';
		include ADMIN_PATH.'/admin-footer.php';
		break;
}

function drop_history_user_select($selected=0){
	$db = get_conn();$hl = '';
	$rs = $db->query("SELECT DISTINCT(`userid`) FROM `#@_history` WHERE `userid`<>'';");
	while($data = $db->fetch($rs)){
		$user = user_get_byid($data['userid']);
		$sel  = trim($selected)===$data['userid']?' selected="selected"':null;
		$hl.= '<option value="'.$data['userid'].'"'.$sel.'>'.$user['nickname'].'</option>';
	}
	
	return $hl;
}
function drop_history_type_select($selected=null){
	$db = get_conn();$hl = '';
	$rs = $db->query("SELECT DISTINCT(`objecttype`) FROM `#@_history` WHERE `objecttype`<>'';");
	while($data = $db->fetch($rs)){
		$sel  = trim($selected)===$data['objecttype']?' selected="selected"':null;
		$hl.= '<option value="'.$data['objecttype'].'"'.$sel.'>'.$data['objecttype'].'</option>';
	}
	
	return $hl;
}
?>