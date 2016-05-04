<?php
// 加载公共文件
include dirname(__FILE__).'/../admin/admin.php';

// 退出登录
$type = isset($_GET['type'])?$_GET['type']:null;
$hostname = isset($_GET['hostname'])?$_GET['hostname']:null;
$version = isset($_GET['version'])?$_GET['version']:null;
$source = isset($_GET['source'])?$_GET['source']:null;
$extversion = isset($_GET['extversion'])?$_GET['extversion']:null;

$message = array(
	'status'=>'error',
	'data'=>'Bad Request!'
);
switch($type){
	case 'siteinfo':
		if($hostname=="www.hxjq.cn"){
			$message['status'] = 'succeed';
			$message['name'] = "红星";
			$message['domain'] = 'www.hxjq.cn';
			$message['type'] = 'competitors';
		}else{
			$name = determine_url($hostname,'网站所属人');
			$message['status'] = 'succeed';
			$message['name'] = $name;
			$message['hostname'] = $hostname;
			$message['type'] = 'normal';
			// $message['subdomain'] = null;
		}
		
		unset($message['data']);
		break;
}
echo json_encode($message);
?>