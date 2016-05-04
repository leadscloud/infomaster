<?php
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();
defined('COM_PATH') or die('Restricted access!');

if(!empty($_SERVER['HTTP_REFERE']) && strpos($_SERVER['HTTP_REFERER'], 'admin') !== false ){
	define('ADMIN_PANEL', true);
}

$_REQUEST = array_merge($_GET, $_POST);
$status = array();
if(!empty($_REQUEST['call'])){
	switch($_REQUEST['call']){
		case 'importfile':
			$status = Import_Excel();
			break;
		case 'getcontinent':
			$status = check_continent($_POST['country']);
			break;
		case 'checkurl':
			$default = isset($_POST['default'])?$_POST['default']:null;
			$status = determine_url($_POST['url'],$_POST['type'],$default);
			break;
		case 'detectse':
			$url = isset($_POST['url'])?$_POST['url']:null;
			$status = detect_se($url);
			break;
		case 'clearlog':
			$status['status'] = clearLog( current_user_can('clear-log',false) );
			break;
		case 'clearcache':
			$status['status'] = clearCache( current_user_can('clear-cache',false) );
			break;
		case 'getallcontact':
			$sortid = isset($_REQUEST['sortid'])?$_REQUEST['sortid']:null;
			$status = get_all_contact($sortid);
			break;
		case 'getinqiuryinfo':
			$rate = isset($_REQUEST['rate'])?$_REQUEST['rate']:'all';
			$status = get_inqiury_info('inquiry',$rate,30);
			break;
		case 'getrecentinqiuryinfo':
			$rate = isset($_REQUEST['rate'])?$_REQUEST['rate']:'all';
			$status = get_recent_inqiury_info('inquiry',$rate,30);
			break;
		case 'getoperatorinfo':
			$status = get_operator_info();
			break;
		case 'getbelonginfo':
			$days = isset($_REQUEST['days'])?$_REQUEST['days']:30;
			$status = get_belong_info();
			break;
		case 'getcontinentsinfo':
			$status = get_continents_info();
			break;
		case 'getworldinfo':
			$status = get_world_info();
			break;
		case 'closeissue':
			$issueid = isset($_REQUEST['issueid'])?$_REQUEST['issueid']:0;
			$status = issue_close($issueid);
			break;
		case 'save_image':
			$x1 = isset($_REQUEST['x1'])?$_REQUEST['x1']:null;
			$y1 = isset($_REQUEST['y1'])?$_REQUEST['y1']:null;
			$w	= isset($_REQUEST['w'])?$_REQUEST['w']:null;
			$h	= isset($_REQUEST['h'])?$_REQUEST['h']:null;
			$status = save_image($x1,$y1,$w,$h);
			break;
		case 'uploadimg':
			$x1 = isset($_REQUEST['x1'])?$_REQUEST['x1']:null;
			$y1 = isset($_REQUEST['y1'])?$_REQUEST['y1']:null;
			$w	= isset($_REQUEST['w'])?$_REQUEST['w']:null;
			$h	= isset($_REQUEST['h'])?$_REQUEST['h']:null;
			$status = upload_image($x1,$y1,$w,$h);
			break;
		case 'checkpms':
			$status = check_pms();
			break;
		case 'redetermineurl':
			$status = re_determine_url();
			break;
	}
	echo json_encode($status);
	exit;
}
?>