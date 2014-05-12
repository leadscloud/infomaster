<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 系统定义文件
 */
// System version
define('SYS_VERSION','1.1.Alpha1');
// 严重错误，停止程序
define('E_SYS_ERROR',10);
// 警告错误，不停止程序
define('E_SYS_WARNING',20);
// 提示错误，不停止程序
define('E_SYS_NOTICE',40);
// 系统信息
if(version_compare(PHP_VERSION,'6.0.0','<') ) {
    @set_magic_quotes_runtime(0);
}
defined('E_STRICT') or define('E_STRICT',2048);
define('IS_CGI',!strncasecmp(PHP_SAPI,'cgi',3) ? 1 : 0 );
define('IS_WIN',DIRECTORY_SEPARATOR == '\\' );
define('IS_CLI',PHP_SAPI=='cli' ?  1 : 0);
// 当前文件名
if(!defined('PHP_FILE')) {
    if (IS_CLI) {
        define('PHP_FILE',$argv[0]);
    } elseif(IS_CGI) {
        //CGI/FASTCGI模式下
        $_temp  = explode('.php',$_SERVER["PHP_SELF"]);
        define('PHP_FILE', rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
    } else {
        define('PHP_FILE', rtrim($_SERVER["SCRIPT_NAME"],'/'));
    }
}
// Web root
define('ROOT',str_replace('\\','/',substr(dirname(PHP_FILE),0,-strlen(substr(realpath('.'),strlen(ABS_PATH)))+1)));
// Http scheme
define('HTTP_SCHEME',(($scheme=isset($_SERVER['HTTPS'])?$_SERVER['HTTPS']:null)=='off' || empty($scheme))?'http':'https');
// 非命令行模式
if (!IS_CLI) {
    // Delete or modify this line may cause the system does not work
    header(sprintf('X-Powered-By: InfoMaster/%s (Ray)',SYS_VERSION));
    // Http host
    define('HTTP_HOST',HTTP_SCHEME.'://'.$_SERVER['HTTP_HOST']);
} else {
    define('HTTP_HOST','');
}
// Server detection

/**
 * Whether the server software is Apache or something else
 * @global bool $is_apache
 */
$is_apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);

/**
 * Whether the server software is IIS or something else
 * @global bool $is_IIS
 */
$is_IIS = !$is_apache && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);

/**
 * Whether the server software is IIS 7.X
 * @global bool $is_iis7
 */
$is_iis7 = $is_IIS && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/7.') !== false);
