<?php
// 检查环境是否适合做爱做的事
!version_compare(PHP_VERSION, '4.3.3', '<') or die('PHP version lower than 4.3.3, upgrade PHP!<br/>&lt;<a href="http://php.net/downloads.php" target="_blank">http://php.net/downloads.php</a>&gt;');
// 设置错误等级
error_reporting() === E_ALL & ~E_NOTICE or error_reporting(E_ALL & ~E_NOTICE);
// 定义项目物理跟路径
define('ABS_PATH',dirname(__FILE__));
// 定义项目物理公共目录
define('COM_PATH',ABS_PATH.'/common');
// 加载项目配置
if (is_file(ABS_PATH.'/config.php'))
    include ABS_PATH.'/config.php';
// 定义系统常量
include COM_PATH.'/defines.php';
// 加载公共函数库
include COM_PATH.'/functions.php';
// 加载验证类
include COM_PATH.'/system/validate.php';
// 加载cookie库
include COM_PATH.'/system/cookie.php';
// 加载文件缓存类
include COM_PATH.'/system/fcache.php';
// 加载本地化语言类库
//include COM_PATH.'/system/l10n.php';
//判断配置文件是否存在
if ( !file_exists( ABS_PATH . '/config.php') ) {
	$home_url = get_home_url();
	echo error_page('找不到配置文件',"配置文件<code>config.php</code>不存在,你可以通过查看根目录下的 <a href='$home_url/readme.html'>readme.html</a> 帮助文件来重新配置<code>config.php</code>",true);
	exit();
}
// 设置系统时区
//time_zone_set(C('System.Timezone')==null?'Asia/Shanghai':C('System.Timezone'));
// Set default timezone in PHP 5.
if ( function_exists( 'date_default_timezone_set' ) )
	date_default_timezone_set( 'UTC' );
// 开始时间
define('__BEGIN__', micro_time(true));
// 处理错误
set_error_handler('handler_error');
// 处理系统变量
if (get_magic_quotes_gpc()) {
    $args = array(& $_GET, & $_POST, & $_COOKIE, & $_FILES, & $_REQUEST);
    while (list($k,$v) = each($args)) {
        $args[$k] = stripslashes_deep($args[$k]);
    }
    unset($args,$k,$v);
}
// 加载默认语言包
//load_textdomain();
if (!IS_CLI) C('Compress') ? ob_start('ob_compress') : null;
// 删除没用的系统变量
unset($_ENV,$HTTP_ENV_VARS,$HTTP_SERVER_VARS,$HTTP_SESSION_VARS,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS);
// 禁止直接访问此文件
strtolower(substr($_SERVER['SCRIPT_FILENAME'],-strlen(__FILE__))) != strtolower(__FILE__) or die('Restricted access!');