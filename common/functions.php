<?php
defined('COM_PATH') or die('Restricted access!');
/**
 * 系统公用函数库
 */
 
 
/**
 * 解析 PHP info
 *
 * @return array
 */
function parse_phpinfo() {
    ob_start(); phpinfo(INFO_MODULES); $s = ob_get_contents(); ob_end_clean();
    $s = strip_tags($s, '<h2><th><td>');
    $s = preg_replace('/<th[^>]*>([^<]+)<\/th>/', '<info>\1</info>', $s);
    $s = preg_replace('/<td[^>]*>([^<]+)<\/td>/', '<info>\1</info>', $s);
    $t = preg_split('/(<h2[^>]*>[^<]+<\/h2>)/', $s, -1, PREG_SPLIT_DELIM_CAPTURE);
    $r = array(); $count = count($t);
    $p1 = '<info>([^<]+)<\/info>';
    $p2 = '/'.$p1.'\s*'.$p1.'\s*'.$p1.'/';
    $p3 = '/'.$p1.'\s*'.$p1.'/';
    for ($i = 1; $i < $count; $i++) {
        if (preg_match('/<h2[^>]*>([^<]+)<\/h2>/', $t[$i], $matchs)) {
            $name = trim($matchs[1]);
            $vals = explode("\n", $t[$i + 1]);
            foreach ($vals AS $val) {
                if (preg_match($p2, $val, $matchs)) { // 3cols
                    $r[$name][trim($matchs[1])] = array(trim($matchs[2]), trim($matchs[3]));
                } elseif (preg_match($p3, $val, $matchs)) { // 2cols
                    $r[$name][trim($matchs[1])] = trim($matchs[2]);
                }
            }
        }
    }
    return $r;
}

/**
 * W3c Datetime
 *
 * @param int $timestamp
 * @return string
 */
function W3cDate($timestamp=0) {
    if (!$timestamp) $timestamp = time();
    if (version_compare(PHP_VERSION,'5.1.0','>='))
        return date('c', $timestamp);
    
    $date    = date('Y-m-d\TH:i:s', $timestamp);
    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
        $date .= $matches[1] . $matches[2] . ':' . $matches[3];
    } else {
        $date .= 'Z';
    }
    return $date;

}
 
 /**
 * 取得数据库连接对象
 */
function &get_conn(){
    global $db;
    if (is_null($db) || get_class($db)=='DBQuery_NOOP') {
        if (!class_exists('DBQuery'))
            include COM_PATH.'/system/dbquery.php';

        if (defined('DB_DSN') && defined('DB_USER') && defined('DB_PWD')) {
            $db = DBQuery::factory(DB_DSN, DB_USER, DB_PWD);
        } else {
            $db = new DBQuery_NOOP();
        }
    }
    return $db;
}
/**
 * 返回当前 Unix 时间戳和微秒数
 *
 * @return float
 */
function micro_time($get_as_float=false){
    if ($get_as_float && version_compare(PHP_VERSION, '5.0.0', '<')) {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
    return microtime($get_as_float);
}
/**
 * 检查值是否已经序列化
 *
 * @param mixed $data Value to check to see if was serialized.
 * @return bool
 */
function is_serialized($data) {
    // if it isn't a string, it isn't serialized
    if (!is_string($data))
        return false;
    $data = trim($data);
    if ('N;' == $data)
        return true;
    if (!preg_match('/^([adObis]):/', $data, $badions))
        return false;
    switch ($badions[1]) {
        case 'a' :
        case 'O' :
        case 's' :
            if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
                return true;
            break;
        case 'b' :
        case 'i' :
        case 'd' :
            if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
                return true;
            break;
    }
    return false;
}
/**
 * 判断是否请求JSON格式
 * 
 * @return bool
 */
function is_accept_json() {
    return strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/json')!==false;
}
/**
 * 验证是否json
 *
 * @param string $string
 * @return bool
 */
function is_json($string){
    return preg_match('/^("(\\.|[^"\n\r])*?"|[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t])+?$/',$string);
}
/**
 * 判断是否为ajax提交
 *
 * @return bool
 */
function is_ajax(){
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH'])?$_SERVER['HTTP_X_REQUESTED_WITH']:null)=='XMLHttpRequest';
}
/**
 * 检查数组类型
 *
 * @param array $array
 * @return bool
 */
function is_assoc($array) {
    return (is_array($array) && (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array)==0));
}
/**
 * stripslashes 扩展
 *
 * @param   array     $value     要处理的数组
 * @return  mixed
 */
function stripslashes_deep($value) {
    if (is_array($value)) {
		$value = array_map('stripslashes_deep', $value);
	} elseif (is_object($value)) {
		$vars = get_object_vars($value);
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep($data);
		}
	} else {
		$value = stripslashes($value);
	}
	return $value;
}
/**
 * 取得使用的语言
 *
 * @return string
 */
function language() {
    $ck_lang = cookie_get('language');
    $ck_lang = preg_replace( '/[^a-z0-9,_-]+/i', '', $ck_lang ); 

    if (empty($ck_lang) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $ck_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if (($pos=strpos($ck_lang,',')) !== false) {
            $ck_lang = substr($ck_lang,0,$pos);
        }
		if(strpos($ck_lang,'zh-CN')) {
			$ck_lang = 'zh-CN';
		}elseif (($pos=strpos($ck_lang,',')) !== false) {
            $ck_lang = substr($ck_lang,0,$pos);
        }
        // 需要转换大小写
        if (strtolower($ck_lang) == 'zh-cn') {
            $ck_lang = 'zh-CN';
        } elseif (strtolower($ck_lang) == 'zh-tw') {
            $ck_lang = 'zh-TW';
        }
    } elseif(empty($ck_lang)) {
        $ck_lang = C('Language');
    }
    return $ck_lang;
}
/**
 * 格式化下拉框选项
 *
 * @param string $path      路径
 * @param string $ext       类型
 * @param string $html      html字符串 可以使用变量：#value#,#name#,#selected#
 * @param string $selected  selected
 */
function options($path,$ext,$html,$selected=null){
    $type = $ext=='lang' ? 'mo' : $ext;
    $dirs = get_dir_array($path,$type); $result = null;
    if (strpos($html,'%23')!==false) { $html = str_replace('%23','#',$html); }
    foreach ($dirs as $v) {
        if ($ext=='lang') {
            $v   = basename($v,'.mo');
            $val = code2lang($v);
        } else{
            $val = $v;
        }
        $opt = $html;
        if (strpos($opt,'#value#')!==false) { $opt = str_replace('#value#',$v,$opt); }
        if (strpos($opt,'#name#')!==false)  { $opt = str_replace('#name#',$val,$opt); }
        if ($selected==$v) {
            $opt = str_replace('#selected#',' selected="selected"',$opt);
        } else{
            $opt = str_replace('#selected#','',$opt);
        }
        $result.= $opt;
    }
    return $result;
}
/**
 * 查询配置
 * 安装后会用到这个上，暂时先不用。
 * @param string|array $key
 * @param mixed $value
 * @return mixed
 */
function C($key,$value=null){
	global $_USER;
    $ckey = 'cfg.'; $args = null;
    // 批量赋值
    if(is_array($key)) {
        foreach ($key as $k=>$v) {
        	C($k,$v);
        }
        return true;
    }
    // 分析key
    if (strpos($key,'.')!==false) {
    	$args   = explode('.',$key);
    	$module = array_shift($args);
		
		if($module==null) $module ='System'; //自己加的
    	$code   = array_shift($args);
    } else {
        $module = $_USER['name'];//'System';
    	$code   = $key;
    }
    $db  = @get_conn();
    $key = $module.'.'.$code;
    // 取值
    if($key && func_num_args()==1) {
        // 数据库链接有问题
        if ($db && !$db->ready) return null;
        // 先从缓存里取值
        $value = fcache_get($ckey.$key);
        if (fcache_is_null($value)) {
            if ($db->is_table('#@_option')) {
                $result = $db->query("SELECT `value` FROM `#@_option` WHERE `module`='%s' AND `code`='%s' LIMIT 1 OFFSET 0;",array($module,$code));
                if ($data = $db->fetch($result)) {
                    $value = is_serialized($data['value']) ? unserialize($data['value']) : $data['value'];
                    // 保存到缓存
                    fcache_set($ckey.$key,$value);
                }
            }
        }
        // 支持多维数组取值
        if (!empty($args) && is_array($value)) {
        	foreach ($args as $arg) {
        		$value = $value[$arg];
        	}
        }
        return $value;
    }
    // 参数赋值
    else {
        // 删除属性
        if (is_null($value)) {
            fcache_delete($key);
            $db->delete('#@_option',array(
                'module' => $module,
                'code'   => $code,
            ));
        } else {
            // 保存到缓存
            fcache_set($ckey.$key,$value);
            // 查询数据库里是否已经存在
            $length = (int) $db->result(vsprintf("SELECT COUNT(`id`) FROM `#@_option` WHERE `module`='%s' AND `code`='%s'",array(esc_sql($module),esc_sql($code))));
            // update
            if ($length > 0) {
                $db->update('#@_option',array(
                   'value' => $value,
                ),array(
                    'module' => $module,
                    'code'   => $code,
                ));
            }
            // insert
            else {
                // 保存到数据库里
                $db->insert('#@_option',array(
                    'module' => $module,
                    'code'   => $code,
                    'value'  => $value,
                ));
            }
        }
        return true;
    }
    return null;
}
/**
 * 转义sql语句
 *
 * @param  $str
 * @return string
 */
function esc_sql($str) {
    $db = get_conn(); return $db->escape($str);
}
/**
 * 清除空白
 *
 * @param  $content
 * @return mixed
 */
function clear_space($content){
    if (strlen($content)==0) return $content; $r = $content;
    $r = str_replace(array(chr(9),chr(10),chr(13)),'',$r);
    while (strpos($r,chr(32).chr(32))!==false || strpos($r,'&nbsp;')!==false) {
        $r = str_replace(chr(32).chr(32),chr(32),str_replace('&nbsp;',chr(32),$r));
    }
    return $r;
}
/**
 * 在数组或字符串中查找
 *
 * @param mixed  $needle   需要搜索的字符串
 * @param string|array $haystack 被搜索的数据，字符串用英文"逗号"分割或数组
 * @return bool
 */
function instr($needle,$haystack){
    if (empty($haystack)) { return false; }
    if (!is_array($haystack)) $haystack = explode(',',$haystack);
    return in_array($needle,$haystack);
}
/**
 * jsmin
 *
 * @param string $js
 * @return string
 */
function jsmin($js) {
    if (!class_exists('JSMin')) {
        include_file(COM_PATH.'/system/jsmin.php');
    }
    return JSMin::minify($js);
}
/**
 * 页面跳转
 *
 * @param string $url
 * @param int $time
 * @param string $msg
 * @return void
 */
function redirect($url,$time=0,$msg='') {
	// 多行URL地址支持
    $url = str_replace(array("\n", "\r"), '', $url);
    if (empty($msg)) $msg = sprintf('<a href="%1$s">%2$d 秒后到 %1$s.</a>',$url,$time);
    if (!headers_sent()) header("Content-Type:text/html; charset=utf-8");
    if (is_ajax()) {
        $data = array('Location' => $url);
        if ($time) $data = array_merge($data,array('Time' => $time));
        if ($time && $msg)  $data = array_merge($data,array('Message' => $msg));
        ajax_echo('Redirect',$data);
    } else {
    	if (!headers_sent()) {
    		if(0===intval($time)) {
    			header("Location: {$url}");
    		}
    	}
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$html.= '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$html.= '<meta http-equiv="refresh" content="'.$time.';url='.$url.'" />';
		$html.= '<title>重定向中...</title>';
		$html.= '<script type="text/javascript" charset="utf-8">';
        $html.= 'window.setTimeout(function(){location.replace("'.esc_js($url).'");}, '.($time*1000).');';
        $html.= '</script>';
		$html.= '</head><body>';
		$html.= 0===$time ? null : $msg;
		$html.= '</body></html>';
        exit($html);
    }
}
/**
 * 取得返回地址
 *
 * @param string $default
 * @param bool   $back_server_referer 是否返回来路
 * @return string
 */
function referer($default='',$back_server_referer=true){
    $default = $default?$default:ROOT;
    $referer = isset($_REQUEST['referer'])?$_REQUEST['referer']:null;
    if ($back_server_referer) {
        if(empty($referer) && isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        } else {
            $referer = esc_html($referer);
        }
    } else {
        if(empty($referer)) {
            $referer = $default;
        } else {
            $referer = esc_html($referer);
        }

    }

    if(strpos($referer, 'login.php')!==false) $referer = $default;
    return $referer;
}
/**
 * 替换文件路径以网站根目录开始，防止暴露文件的真实地址
 *
 * @param   string  $path
 * @return  string  返回一个相对当前站点的文件路径
 */
function rel_root($path){
    $abs_path = str_replace(DIRECTORY_SEPARATOR,'/',ABS_PATH.DIRECTORY_SEPARATOR);
    $src_path = str_replace(DIRECTORY_SEPARATOR,'/',$path);
    return str_replace($abs_path, (IS_CLI ? '/' : ROOT), $src_path);
}
/**
 * 转换特殊字符为HTML实体
 *
 * @param   string $str
 * @return  string
 */
function esc_html($str){
    if(empty($str)) {
        return $str;
    } elseif (is_array($str)) {
		$str = array_map('esc_html', $str);
	} elseif (is_object($str)) {
		$vars = get_object_vars($str);
		foreach ($vars as $key=>$data) {
			$str->{$key} = esc_html($data);
		}
	} else {
        $str = htmlspecialchars($str);
    }
    return $str;
}
/**
 * Escapes strings to be included in javascript
 *
 * @param string $str
 * @return mixed
 */
function esc_js($str) {
    if (function_exists('preg_replace_callback')) {
        $str = preg_replace_callback(
            '/([^ :!#$%@()*+,-.\x30-\x5b\x5d-\x7e])/',
            function ($matches) {
                return '\\x'.(ord($matches[1])<16? '0': '').dechex(ord($matches[1]));
            },
            $str
        );
        return $str;
    }
    return preg_replace('/([^ :!#$%@()*+,-.\x30-\x5b\x5d-\x7e])/e',
        "'\\x'.(ord('\\1')<16? '0': '').dechex(ord('\\1'))", $str);
}
if (!function_exists('error_page')) :
/**
 * 错误页面
 *
 * @param string $title
 * @param string $content
 * @param bool $is_full     是否输出完整页面
 * @return string
 */
function error_page($title,$content,$is_full=false) {
    // CSS
    $css = '<style type="text/css">';
    $css.= '.alert-error{color:#b94a48;background-color:#f2dede;border-color:#eed3d7}.alert-error h4{color:#b94a48}';
    $css.= '.alert{padding:8px 35px 8px 14px;margin-bottom:20px;text-shadow:0 1px 0 rgba(255,255,255,0.5);background-color:#fcf8e3;border:1px solid #fbeed5;-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}';
    $css.= '#error-title { width:100%; border-bottom:solid 1px #B5B5B5; }';
    $css.= '#error-title h1{ font-size: 50px; font-weight: bold;letter-spacing: -1px;margin:0 0 5px 0; }';
    $css.= '#error-content,#error-buttons { }';
    if ($is_full) {
        $css.= 'body { margin:10px 20px;  font-family: "Helvetica Neue",Helvetica,Arial,"Microsoft Yahei UI","Microsoft YaHei",SimHei,simsun,sans-serif; color: #333333; background:#FAFAFA; font-size: 13px; line-height: 1.5; }';
        $css.= '#error-page { width:900px; margin:6% auto; background-color: #EEEEEE;border-radius: 6px 6px 6px 6px;border: 1px solid #E3E3E3;box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05) inset; padding: 48px;}';
		$css.= '.btn {position: relative;padding: 4px 12px;margin: 0;background-image: linear-gradient(to bottom, #f8f8f8, #f1f1f1);background-color: #f5f5f5;box-sizing: border-box;border: 1px solid #c6c6c6;color: #333;border-radius: 2px;}';
		$css.= '.btn:hover {box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);}';
    }
    $css.= '</style>';
    // Page
    $page = '<div id="error-page" class="alert alert-error well">';
    $page.= '<div id="error-title"><h1>'.$title.'</h1></div>';
    $page.= '<div id="error-content"><p>'.$content.'</p></div>';
    $page.= '<div id="error-buttons"><button type="button" onclick="window.history.back();" class="btn btn-primary">返回</button></div>';
    $page.= '</div>';

    if ($is_full) {
        $hl = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $hl.= '<html xmlns="http://www.w3.org/1999/xhtml"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        $hl.= '<title>'.$title.' &#8212; InofMaster</title>';
        $hl.= $css.'</head><body>'.$page;
        $hl.= '</body></html>';
    } else {
    	$hl = $page;
    }
    return $hl;
}
endif;
/**
 * 系统异常处理
 *
 * @param  $errno
 * @param  $errstr
 * @param  $errfile
 * @param  $errline
 * @return bool
 */
function handler_error($errno,$errstr,$errfile,$errline) {
    if (E_STRICT===$errno) return true;
    return throw_error($errstr,$errno,$errfile,$errline);
}
/**
 * 取得错误信息
 *
 * @return array
 */
function last_error($error=true) {
    global $LC_ERRNO, $LC_ERROR,$LC_ERRFILE,$LC_ERRLINE;
    // 清理错误
    if ($error === null)
        $LC_ERRNO = $LC_ERROR = $LC_ERRFILE = $LC_ERRLINE = null;
    // 没有错误
    if (!$LC_ERRNO) return null;
    // 有错误
    return array(
        'errno' => $LC_ERRNO,
        'error' => $LC_ERROR,
        'file'  => $LC_ERRFILE,
        'line'  => $LC_ERRLINE,
    );
}
/**
 * 判断IP是否在可以debug的范围内
 * 目前未添加此功能，以后上传到服务器，需要启用，否则会泄露重要系统信息
 *
 * @return bool
 */
function isAllowed() {
    $allowIPs = array(
                '127.0.0.0/127.255.255.255',
                '192.168.0.0/192.168.255.255',
                '10.0.0.0.0/10.255.255.255',
                '172.16.0.0/172.31.255.255',
            );
    $strIP = get_ip_address();
    if ($strIP == 'Unknown') return false;
    $intIP = sprintf('%u', ip2long($strIP));
    foreach($allowIPs as $IPs) {
        if (strpos($IPs, '/') !== false) {
            $IPs = explode('/', $IPs);
        } else {
            $IPs = array($IPs, $IPs);
        }
        $IPs[0] = sprintf('%u', ip2long($IPs[0]));
        $IPs[1] = sprintf('%u', ip2long($IPs[1]));
        if ($IPs[0] <= $intIP && $intIP <= $IPs[1]) {
            return true;
        }
    }
    return false;
}
/**
 * 异常处里函数
 *
 * @param  $errstr          错误消息
 * @param int $errno        异常类型
 * @return bool
 */
function throw_error($errstr,$errno=E_SYS_NOTICE,$errfile=null,$errline=0){
    global $LC_ERRNO, $LC_ERROR,$LC_ERRFILE,$LC_ERRLINE;
    $string  = $file = null;
    $traces  = debug_backtrace();
    $error   = $traces[0]; unset($traces[0]);
    $errstr  = rel_root($errstr);
    $errfile = rel_root($errfile ? $errfile : $error['file']);
    $errline = rel_root($errline ? $errline : $error['line']);
    $LC_ERRNO = $errno; $LC_ERROR = $errstr; $LC_ERRFILE = $errfile; $LC_ERRLINE = $errline;
    if (error_reporting() === 0) return false;
    foreach($traces as $i=>$trace) {
        $file  = isset($trace['file']) ? rel_root($trace['file']) : $file;
        $line  = isset($trace['line']) ? $trace['line'] : null;
        $class = isset($trace['class']) ? $trace['class'] : null;
        $type  = isset($trace['type']) ? $trace['type'] : null;
        $args  = isset($trace['args']) ? $trace['args'] : null;
        $function  = isset($trace['function']) ? $trace['function'] : null;
        $string   .= "\t#".$i.' ['.date("y-m-d H:i:s").'] '.$file.($line?'('.$line.') ':' ');
        $string   .= $class.$type.$function.'(';
        if (is_array($args)) {
            $arrs = array();
            foreach ($args as $v) {
                if (is_object($v)) {
                    $arrs[] = implode(' ',get_object_vars($v));
                } else {
                    $error_level = error_reporting(0);
                    $vars = print_r($v,true);
                    error_reporting($error_level);
                    while (strpos($vars,chr(32).chr(32))!==false) {
                        $vars = str_replace(chr(32).chr(32),chr(32),$vars);
                    }
                    $arrs[] = $vars;
                }
            }
            $string.= str_replace("\n",'',implode(', ',$arrs));
        }
        $string.=")\r\n";
    }
	
		
    $log = "[Message]:\r\n\t{$errstr}\r\n";
    $log.= "[File]:\r\n\t{$errfile} ({$errline})\r\n";
    $log.= $string?"[Trace]:\r\n{$string}\r\n":'';
    // 记录日志
    error_log($log, 3, ABS_PATH.'/error.log');
    // 处里错误
    switch ($errno) {
        case E_SYS_ERROR:
            // 命令行模式
            if (IS_CLI) $html = $log;
            else {
                // 格式化为HTML
                $html = str_replace("\t",str_repeat('&nbsp; ',2),nl2br(esc_html($log)));
                // 不是ajax请求，格式化成HTML完成页面
                $html = is_ajax() ? $html : error_page('系统错误',$html,true);
            }
            // 输出错误信息，并停止程序
            echo $html; exit();
            break;
        case E_SYS_WARNING: case E_SYS_NOTICE:
            // 命令行模式
            if (IS_CLI) $html = $log;
            else {
                // 格式化为HTML
                $html = str_replace("\t",str_repeat('&nbsp; ',2),nl2br(esc_html($log)));
                // 不是ajax请求，格式化成HTML完成页面
                $html = is_ajax() ? $html : error_page('系统警告',$html,true);
            }
            echo $html;
            break;
        default: break;
    }
    return false;
}
/**
 * 输出ajax规范的json字符串
 *
 * @param  $code
 * @param  $data
 * @param  $eval
 * @return void
 */
function ajax_echo($code,$data,$eval=null){
    if ($code) header('X-InfoMaster-Code: '.$code);
    if ($eval) header('X-InfoMaster-Eval: '.$eval);
    if (is_accept_json()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    } else {
        echo $data;
    }
    exit();
}
/**
 * ajax confirm
 *
 * @param string $message   提示文字
 * @param string $submit    确定之后执行的代码
 * @param string $cancel    取消之后执行的代码
 * @return void
 */
function ajax_confirm($message,$submit,$cancel=null) {
    if ($submit) header('X-InfoMaster-Submit: '.$submit);
    if ($cancel) header('X-InfoMaster-Cancel: '.$cancel);
    return ajax_echo('Confirm',$message);
}
function ajax_alert($message,$eval=null){
    return ajax_echo('Alert',$message,$eval);
}
function ajax_success($message,$eval=null){
    return ajax_echo('Success',$message,$eval);
}
function ajax_error($message,$eval=null){
    return ajax_echo('Error',$message,$eval);
}
function ajax_return($data) {
    return ajax_echo('Return', $data);
}
/**
 * 批量创建目录
 *
 * @param string $path   文件夹路径
 *
 * @param int    $mode   权限
 * @return bool
 */
function mkdirs($path, $mode = 0775){
    if (!is_dir($path)) {
        mkdirs(dirname($path), $mode);
        $error_level = error_reporting(0);
        $result      = mkdir($path, $mode);
        error_reporting($error_level);
        return $result;
    }
    return true;
}
/**
 * 删除文件夹
 *
 * @param string $path		要删除的文件夹路径
 * @return bool
 */
function rmdirs($path){
    $error_level = error_reporting(0);
    if ($dh = opendir($path)) {
        while (false !== ($file=readdir($dh))) {
            if ($file != '.' && $file != '..') {
                $file_path = $path.'/'.$file;
                is_dir($file_path) ? rmdirs($file_path) : unlink($file_path);
            }
        }
        closedir($dh);
    }
    $result = rmdir($path);
    error_reporting($error_level);
    return $result;
}
/**
 * 代替 require_once
 *
 * @param  $path
 * @return bool
 */
function include_file($path){
    static $paths = array();
    if (is_file($path)) {
        if (!isset($paths[$path])) {
            include $path;
            $paths[$path] = true;
            return true;
        }
        return false;
    }
    return false;
}
if (!function_exists('authcode')) :
/**
 * 给用户生成唯一CODE
 *
 * @param string $data
 * @return string
 */
function authcode($data=null){
    return guid(HTTP_HOST.$data.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
}
endif;
/**
 * 生成guid
 *
 * @param  $randid  字符串
 * @return string   guid
 */
function guid($mix=null){
    if (is_null($mix)) {
        $randid = uniqid(mt_rand(),true);
    } else {
        if (is_object($mix) && function_exists('spl_object_hash')) {
            $randid = spl_object_hash($mix);
        } elseif (is_resource($mix)) {
            $randid = get_resource_type($mix).strval($mix);
        } else {
            $randid = serialize($mix);
        }
    }
    $randid = strtoupper(md5($randid));
    $hyphen = chr(45);
    $result = array();
    $result[] = substr($randid, 0, 8);
    $result[] = substr($randid, 8, 4);
    $result[] = substr($randid, 12, 4);
    $result[] = substr($randid, 16, 4);
    $result[] = substr($randid, 20, 12);
    return implode($hyphen,$result);
}
/**
 * 加载所有模块
 *
 * @return bool
 */
function include_modules() {
    static $loaded; if ($loaded) return true;
    // 加载分页类
    include_file(COM_PATH.'/system/pages.php');
    // 查询模块列表
    $modules = get_dir_array('@/module','php');
    foreach ($modules as $file) {
        include_file(COM_PATH.'/module/'.$file);
    }
    // 执行函数回调
    global $LC_func_callback;
    if ($LC_func_callback) {
        foreach ((array)$LC_func_callback as $call) {
            if (function_exists($call['func'])) call_user_func_array($call['func'], $call['args']);
        }
    }
    $loaded = true; return true;
}
/**
 * 设置时区
 *
 * @param string $timezone
 * @return bool
 */
function time_zone_set($timezone) {
    if (!function_exists('_timezone_get_object')) {
        include_file(COM_PATH.'/system/timezone.php');
    }
    $zone = _timezone_get_object();
    return $zone->set_zone($timezone);
}
/**
 * 添加回调函数
 *
 * @param string $func
 * @param mixed $args
 * @return bool
 */
function func_add_callback() {
    global $LC_func_callback;

    if (!is_array($LC_func_callback))
        $LC_func_callback = array();
    
    $args = func_get_args();
    $func = array_shift($args);
    
    if (function_exists($func)) {
        $LC_func_callback[] = array(
            'func' => $func,
            'args' => $args,
        );
        return true;
    }
    return false;
}
/**
 * 将目录下的文件或文件夹读取成为数组
 *
 * @param string $path    路径
 * @param string $ext     读取类型
 * @return array
 * 性能有待提升
 */
function get_dir_array($path,$ext='*'){
    $path = str_replace(array('.','[',']'),array(DIRECTORY_SEPARATOR,'*','*'),$path);
    if (!strncasecmp($path,'@',1)) {
        $path = str_replace('@',COM_PATH,$path);
    } else {
        $path = ABS_PATH.DIRECTORY_SEPARATOR.$path;
    }
    $process_func = create_function('&$path,$ext','$path=substr($path,strrpos($path,"/")+1);');
    if (!substr_compare($path,'/',strlen($path)-1,1)===false) $path .= '/';
    $result = ($ext=='dir') ? glob("{$path}*",GLOB_ONLYDIR) : glob("{$path}*.{{$ext}}",GLOB_BRACE);
    array_walk($result,$process_func);
    return $result;
}
/**
 * 获得用户的头像, 如果没有上传返回false
 *
 * @param int $userid
 * @return string 返回头像html代码
 */
function get_user_avatar($userid, $size=96, $default=''){
	$user = user_get_byid($userid);
	$email = isset($user['mail'])?$user['mail']:null;
	$avatar = isset($user['avatar'])?HTTP_HOST.rel_root($user['avatar']):null;
	if(@file_get_contents($avatar))
		return $avatar;
	if(!$avatar)
		return get_avatar($email, $size=96, $default='');
}
/**
 * 头像
 *
 * @param string $email
 * @param int $size
 * @param string $default
 * @return string
 */
function get_avatar($email, $size=96, $default='') {
	
    if ( !empty($email) )
		$email_hash = md5( strtolower( $email ) );

    if ( !empty($email) )
        $host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash{0} ) % 2 ) );
    else
        $host = 'http://0.gravatar.com';

    if ( 'mystery' == $default )
        $default = "{$host}/avatar/ad516503a11cd5ca435acc9bb6523536.gif?s={$size}"; // ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
    elseif ( 'blank' == $default )
		$default = HTTP_HOST.ROOT.'lib/img/blank.gif';
    elseif ( empty($email) )
		$default = "{$host}/avatar/00000000000000000000000000000000.gif?d={$default}&amp;s={$size}";

    if ( !empty($email) ) {
        $result = "{$host}/avatar/{$email_hash}.gif?s={$size}&amp;d=".urlencode( $default )."&amp;r=g";
    } else {
        $result = $default;
    }
    return $result;
}
/**
 * 显示分类树
 *
 * @param int $taxonomyid   当前分类IDrm
 * @param int $selected 被选择的分类ID
 * @param int $n
 * @param array $trees
 * @return string
 */
function dropdown_categories($taxonomyid,$selected=0,$trees=null,$level=0) {
    static $func = null,$n = 0; if (!$func) $func = __FUNCTION__;
    if ($trees===null) $trees = taxonomy_get_trees();
    $hl = ''; $space = str_repeat('&nbsp; &nbsp; ',$level); $level++;
    foreach ($trees as $tree) {
        $sel  = $selected==$tree['taxonomyid']?' selected="selected"':null;
        if ($taxonomyid==$tree['taxonomyid']&&$taxonomyid!=null) {
            $hl.= '<optgroup label="'.$space.'├ '.$tree['name'].'"></optgroup>';
        } else {
            $hl.= '<option value="'.$tree['taxonomyid'].'"'.$sel.'>'.$space.'├ '.$tree['name'].'</option>';
        }
    	if (isset($tree['subs'])) {
    		$hl.= $func($taxonomyid,$selected,$tree['subs'],$level);
    	}
    }
    return $hl;
}
/**
 * 显示目录的下拉列表
 * @param  [type]  $sortid     分类id
 * @param  array   $categories 分类
 * @param  [type]  $trees      分类树
 * @param  integer $level      显示层深
 * @return [type]              
 */
function dropdown_other_categories($sortid,$categories=array(),$trees=null,$level=0) {
    static $func = null,$n = 0; if (!$func) $func = __FUNCTION__;
    if ($trees===null) $trees = taxonomy_get_trees();
    $hl = ''; $space = str_repeat('&nbsp; &nbsp; ',$level); $level++;
    foreach ($trees as $tree) {
		$sel = instr($tree['taxonomyid'],$categories) && $sortid!=$tree['taxonomyid'] ? ' selected="selected"' : '';
        if ($sortid==$tree['taxonomyid']&&$sortid!=null) {
            $hl.= '<optgroup label="'.$space.'├ '.$tree['name'].'"></optgroup>';
        } else {
            $hl.= '<option value="'.$tree['taxonomyid'].'"'.$sel.'>'.$space.'├ '.$tree['name'].'</option>';
        }
    	if (isset($tree['subs'])) {
    		$hl.= $func($sortid,$categories,$tree['subs'],$level);
    	}
    }
    return $hl;
}

/**
 * 得到列信息
 * @return [type] [description]
 */
function get_column_info() {
	$columns = array(
			'cb'			=> '<input type="checkbox" name="select" value="all">',
			'id'			=> 'ID',
			'serial'		=> '序号',
			'identifier'	=> '询盘ID',
			'source'		=> '来源',
			'infoclass'		=> '信息组',
			'infomember'	=> '信息员',
			'inforate'		=> '信息评级',
			'saleunit'		=> '销售部门',
			'salesubunit'	=> '销售组别',
			'operational'	=> '业务员',
			'language'		=> '语种',
			'keywords'		=> '关键词',
			'referer'		=> '来源网址',
			'landingurl'	=> '到访网址',
			'belong'		=> '所属人',
			'email'			=> '邮箱',
			'phone'			=> '电话',
			'continent'		=> '洲',
			'country'		=> '国家',
			'province'		=> '省份',
			'producttype'	=> '产品类型',
			'auction'		=> '是否竞价',
			'sesource'		=> '搜索引擎来源',
			'remarks'		=> '备注信息',
			'addtime'		=> '询盘日期',
			'edittime'		=> '编辑时间',
			'datetime'		=> '添加时间',
			'xp_status'		=> '询盘状态'
			);
	$hidden = get_hidden_columns();
	$sortable = array('id'	=> array('postid','asc'),
			'identifier'	=> array('identifier','asc'),
			'serial' => array('serial','asc'),
			'source' => array('source','asc'),
			'infoclass' => array('infoclass','asc'),
			'infomember' => array('infomember','asc'),
			'inforate' => array('inforate','asc'),
			'saleunit'	=> array('saleunit','asc'),
			'salesubunit' => array('salesubunit','asc'),
			'operational' => array('operational','asc'),
			'language' => array('language','asc'),
			'keywords' => array('keywords','asc'),
			'referer' => array('referer','asc'),
			'landingurl' => array('landingurl','asc'),
			'belong' => array('belong','asc'),
			'email' => array('email','asc'),
			'phone' => array('phone','asc'),
			'country' => array('country','asc'),
			'continent' => array('continent','asc'),
			'province' => array('province','asc'),
			'producttype' => array('producttype','asc'),
			'auction' => array('auction','asc'),
			'sesource' => array('sesource','asc'),
			'addtime' => array('inquirydate','asc'),
			'edittime' => array('editdate','asc'),
			'datetime' => array('adddate','asc'),
			'xp_status' => array('status','asc')
			);

	return array( $columns, $hidden, $sortable );
}
/**
 * 显示询盘信息需要显示的列信息
 * @return [type] [description]
 */
function get_hidden_columns() {
	global $_USER;
	$user = $_USER;
	//if ( is_string( $screen ) )
		//$screen = convert_to_screen( $screen );
	if(C( $user['name'] . '.columnshidden' )==null) return array('cb','identifier','infoclass','infomember','inforate','belong','referer','landingurl','producttype','datetime');
	$value = C( $user['name'] . '.columnshidden' );
	if(is_string($value)) $value = explode(",", $value);
	return $value;
}
/**
 * 重建URL并为URL查询语句添加新的查询变量
 * @return  string 新的URL查询字符串
 */
function add_query_arg() {
        $ret = '';
        $args = func_get_args();
        if ( is_array( $args[0] ) ) {
                if ( count( $args ) < 2 || false === $args[1] )
                        $uri = $_SERVER['REQUEST_URI'];
                else
                        $uri = $args[1];
        } else {
                if ( count( $args ) < 3 || false === $args[2] )
                        $uri = $_SERVER['REQUEST_URI'];
                else
                        $uri = $args[2];
        }
        if ( $frag = strstr( $uri, '#' ) )
                $uri = substr( $uri, 0, -strlen( $frag ) );
        else
                $frag = '';
        if ( 0 === stripos( 'http://', $uri ) ) {
                $protocol = 'http://';
                $uri = substr( $uri, 7 );
        } elseif ( 0 === stripos( 'https://', $uri ) ) {
                $protocol = 'https://';
                $uri = substr( $uri, 8 );
        } else {
                $protocol = '';
        }
        if ( strpos( $uri, '?' ) !== false ) {
                $parts = explode( '?', $uri, 2 );
                if ( 1 == count( $parts ) ) {
                        $base = '?';
                        $query = $parts[0];
                } else {
                        $base = $parts[0] . '?';
                        $query = $parts[1];
                }
        } elseif ( $protocol || strpos( $uri, '=' ) === false ) {
                $base = $uri . '?';
                $query = '';
        } else {
                $base = '';
                $query = $uri;
        }
        wp_parse_str( $query, $qs );
        $qs = urlencode_deep( $qs ); // this re-URL-encodes things that were already in the query string
        if ( is_array( $args[0] ) ) {
                $kayvees = $args[0];
                $qs = array_merge( $qs, $kayvees );
        } else {
                $qs[ $args[0] ] = $args[1];
        }
        foreach ( $qs as $k => $v ) {
                if ( $v === false )
                        unset( $qs[$k] );
        }
        $ret = build_query( $qs );
        $ret = trim( $ret, '?' );
        $ret = preg_replace( '#=(&|$)#', '$1', $ret );
        $ret = $protocol . $base . $ret . $frag;
        $ret = rtrim( $ret, '?' );
        return $ret;
}
/**
 * 从查询字符串中删除某个条目或列表项
 * @param  [string|array]  $key   需要被删除的查询关键字
 * @param  boolean $query [description]
 * @return [type]         新的URL查询字符串
 */
function remove_query_arg( $key, $query=false ) {
	if ( is_array( $key ) ) { // removing multiple keys
		foreach ( $key as $k )
			$query = add_query_arg( $k, false, $query );
		return $query;
	}
	return add_query_arg( $key, false, $query );
}
/**
 * Parses a string into variables to be stored in an array. 
 * @param  string $string The string to be parsed.
 * @param  array $array  Variables will be stored in this array.
 * @return array         [description]
 */
function wp_parse_str( $string, &$array ) {
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array );
	parse_str( $string, $array );
}
/**
 * Navigates through an array and encodes the values to be used in a URL.
 * @param  array|string $value The array or string to be encoded.
 * @return array|string        $value The encoded array (or string from the callback).
 */
function urlencode_deep($value) {
	$value = is_array($value) ? array_map('urlencode_deep', $value) : urlencode($value);
	return $value;
}
function build_query( $data ) {
	return _http_build_query( $data, null, '&', '', false );
}
function _http_build_query($data, $prefix=null, $sep=null, $key='', $urlencode=true) {
	$ret = array();

	foreach ( (array) $data as $k => $v ) {
		if ( $urlencode)
			$k = urlencode($k);
		if ( is_int($k) && $prefix != null )
			$k = $prefix.$k;
		if ( !empty($key) )
			$k = $key . '%5B' . $k . '%5D';
		if ( $v === NULL )
			continue;
		elseif ( $v === FALSE )
			$v = '0';

		if ( is_array($v) || is_object($v) )
			array_push($ret,_http_build_query($v, '', $sep, $k, $urlencode));
		elseif ( $urlencode )
			array_push($ret, $k.'='.urlencode($v));
		else
			array_push($ret, $k.'='.$v);
	}

	if ( NULL === $sep )
		$sep = ini_get('arg_separator.output');

	return implode($sep, $ret);
}

function get_group_permissions($primary, $other_ids = ''){
	$permissions_list = array();
	$db = get_conn();
	if(!empty($other_ids)){
		$other_ids = @unserialize($other_ids)===false?$other_ids:unserialize($other_ids);
		$query = '';
		if(is_array($other_ids)){
			foreach($other_ids as $gid){
				$query .= (empty($query) ? $gid : ','.$gid);
			}
		}else {
			$query = $other_ids;
		}
		
		if($query==null) return $primary;
		$rs = $db->query('SELECT permissions FROM `#@_user_groups` WHERE id IN ('.$query.')');
		
		if($rs){
			while($row = $db->fetch($rs)){
				$permissions_list = explode(',', $row['permissions']);
			}
		}
	}
	if(is_array($primary))
		$primary = array_merge($primary,$permissions_list);
	return $primary;
}
/*
 * 
 * (string|array) $args Value to merge with $defaults
 * (array) $defaults Array that serves as the defaults.
 * @return (array) Merged user defined values with defaults.
*/
function parse_args( $args, $defaults = '' ) {
	if ( is_object( $args ) )
		$r = get_object_vars( $args );
	elseif ( is_array( $args ) )
		$r =& $args;
	else
		wp_parse_str( $args, $r );

	if ( is_array( $defaults ) )
		return array_merge( $defaults, $r );
	return $r;
}

function badge_rate($level){
	$arr = array("A" => "badge-success", "B" => "badge-info", "C" => "badge-warning", "D" => "badge-important", "E" => "badge-inverse");
	$class = strtr($level,$arr);
	return '<span class="badge '.$class.'">'.$level.'</span>';
}

function generate_identifier($postid){
	//$department,$source,$user,$serial,$rate,$language
	$post = post_get($postid);
	$user = user_get_byid($post['userid']);
	$infoclass = $post['infoclass'];
	$source = $post['source'];
	$serial = $post['serial'];
	$rate = $post['inforate'];
	$language = $post['language'];
	$symbol	= isset($user['symbol'])?$user['symbol']:'unknown';
	//$date	= date('Ymd', $post['datetime']);
	$date	= date_gmt('Ymd', $post['datetime']);
	//额外添加的两个字段
	$sale_name = $post['operational'];
	$country = $post['country'];
	$xp_status = $post['xp_status'];
	
	// 替换掉全角逗号和全角空格
	$sale_name = str_replace(array('，','　'),array(',',' '),$sale_name);
	// 先用,分隔名字
	$sale_name = explode(',',$sale_name);
	// 分隔失败，使用空格分隔名字
	if (count($sale_name)==1) $sale_name = explode(' ',$sale_name[0]);
	// 移除重复的名字
	$sale_name = array_unique($sale_name);
	// 去除名字两边的空格，转义HTML
	array_walk($sale_name,create_function('&$s','$s=esc_html(trim($s));'));
	$sale_name = implode('/',$sale_name);
	


	$identifier ='';
	switch($infoclass){
		case '世邦国贸信息组':
			$infoclass = 'SBXX';
			break;
		case '西芝国贸信息组':
			$infoclass = 'XZXX';
			break;
		case '世邦国内信息组':
			$infoclass = 'GNXX';
			break;
		default:
			$infoclass = isset($user['grpcode'])?$user['grpcode']:'';
			break;
	}
	switch($source){
		case '商务通':
			$source = 'SWT';
			break;
		case '办事处':
			$source = 'BSC';
			break;
		case '报纸杂志':
			$source = 'BZ';
			break;
		case '电话':
			$source = 'TEL';
			break;
		case '技术员':
			$source = 'JSY';
			break;
		case '网站留言':
			$source = 'WEB';
			break;
		case '展会':
			$source = 'ZH';
			break;
		case '直接来信':
			$source = 'EM';
			break;
		case 'B2B':
			$source = 'B2B';
			break;
		case 'SNS':
			$source = 'SNS';
			break;
		default:
			$source = 'QT';
			break;
	}
	switch($language){
		case '阿语':
			$language = 'AE';
			break;
		case '俄语':
			$language = 'RU';
			break;
		case '法语':
			$language = 'FR';
			break;
		case '葡语':
			$language = 'PT';
			break;
		case '西语':
			$language = 'ES';
			break;
		case '印尼语':
			$language = 'ID';
			break;
		case '英语':
			$language = 'EN';
			break;
		case '中文':
			$language = 'ZH';
			break;
		case '泰语':
			$language = 'TH';
			break;
		case '越南语':
			$language = 'VN';
			break;
		case '波斯语':
			$language = 'IR';
			break;
		default:
			$language = 'OL';
			break;
	}
	if($infoclass=='') return false;
	$identifier	= array(
		$infoclass,
		$source.$date,
		$symbol.str_pad($serial, 2, '0', STR_PAD_LEFT).$rate,
		$sale_name,
		$xp_status,
		$language,
		$country==null?'未知国':$country
	);
	// 过滤掉数组中为false的值
	$identifier	 = array_filter($identifier);
	$identifier	= implode('-',$identifier);
	//$identifier = $infoclass .'-'.$source.$date.'-'.$symbol.str_pad($serial, 2, '0', STR_PAD_LEFT).$rate.'-'.$sale_name.'-'.$language.'-'.$country;
	return post_edit($postid,array( 'identifier' => strtoupper($identifier) ));
}

/**
 * Excel 数据导出
 * @param array $data  要导出数据的二维数组
 * @param string $name  保存的文件名
 * 如果想自定导出的数据与格式，请参考以下网址：
 * http://www.cnblogs.com/tsunlight/archive/2012/03/08/2385843.html
 */
function excel_export($rows,$name='export_data') {
	set_time_limit(0);
	ini_set("memory_limit","-1");
	$db = get_conn();
	$excel_name = 'DS基础数据表'.date('Ymd',time());
	//log history
	//history_export(count($rows),$excel_name);
	include_file(COM_PATH.'/system/Excel/PHPExcel.php');
	//设置缓存
	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory; 
	$cacheSettings = array( );
	PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);	  

	$objPHPExcel = new PHPExcel();
	$fields = $db->list_fields('#@_post');
	//输出表头
	$sheet_head = array('identifier'=>'询盘ID','source'=>'来源','infoclass'=>'信息组部门','infomember'=>'信息员','addtime'=>'询盘日期','datetime'=>'时间','xp_status'=>'询盘状态','inforate'=>'信息评级','belong'=>'所属人','operational'=>'业务人员','saleunit'=>'销售部门','salesubunit'=>'销售组别','language'=>'语种','keywords'=>'关键词','referer'=>'来源网址','landingurl'=>'到访网址','country'=>'所属国家','continent'=>'大洲','producttype'=>'产品类型','auction'=>'是否竞价','sesource'=>'搜索引擎来源','remarks'=>'备注');
	$num = 0;
	
	foreach($sheet_head as $f=>$v) {
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($num,1, $v);
		$num++;
	}
	
	$objSheet = $objPHPExcel->getActiveSheet();
	foreach($rows as $row => $columns){
		$count =0;
		foreach($sheet_head as $index=>$value) {
			//$column_value = is_string($columns[$index])?$columns[$index]:'';
			$objSheet->setCellValueByColumnAndRow($count, $row + 2, $columns[$index]);
			$count++;
		}
	}
	$objSheet->setTitle('基础数据');
	
	$objPHPExcel->setActiveSheetIndex(0);
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$excel_name.'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->setPreCalculateFormulas(false);
	$objWriter->save('php://output');
    exit;
}

function export_domain($type){
	set_time_limit(0);
	ini_set("memory_limit","-1");
	$db = get_conn();
	$sql = "SELECT * FROM `#@_rule` WHERE `type`='{$type}' AND `domain`<>'' AND `state`='enabled' ORDER BY `result` DESC;";
	$result = $db->query($sql);
	include_file(COM_PATH.'/system/Excel/PHPExcel.php');
	$objPHPExcel = new PHPExcel();
	$objSheet = $objPHPExcel->getActiveSheet();
	$rowCount = 2;
	if ($result) {
		//表头
		$objSheet->SetCellValue('A1', '域名');
		$objSheet->SetCellValue('B1', '所属人');
		//表内容
		while ($row = $db->fetch($result)) {
			$objSheet->SetCellValue('A'.$rowCount, $row['domain']);
			$objSheet->SetCellValue('B'.$rowCount, $row['result']);
			$rowCount++; 
		}
		//列宽
		$objSheet->getColumnDimension('A')->setWidth(50);
		$objSheet->getColumnDimension('B')->setWidth(10);
	}
	$objSheet->setTitle($type);
	$excel_name = '域名数据'.date('Ymd',time());
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="'.$excel_name.'.xls"');
	header('Cache-Control: max-age=0');
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
}

function convert_bytes_to_hr( $bytes ) {
	$units = array( 0 => 'B', 1 => 'kB', 2 => 'MB', 3 => 'GB' );
	$log = log( $bytes, 1024 );
	$power = (int) $log;
	$size = pow(1024, $log - $power);
	return $size . $units[$power];
}
function convert_hr_to_bytes( $size ) {
	$size = strtolower($size);
	$bytes = (int) $size;
	if ( strpos($size, 'k') !== false )
		$bytes = intval($size) * 1024;
	elseif ( strpos($size, 'm') !== false )
		$bytes = intval($size) * 1024 * 1024;
	elseif ( strpos($size, 'g') !== false )
		$bytes = intval($size) * 1024 * 1024 * 1024;
	return $bytes;
}

function max_upload_size() {
	$u_bytes = convert_hr_to_bytes( ini_get( 'upload_max_filesize' ) );
	$p_bytes = convert_hr_to_bytes( ini_get( 'post_max_size' ) );
	$bytes	=	min($u_bytes, $p_bytes);
	return $bytes;
}
/**
 * [导入Excel数据]
 */
function Import_Excel(){
	if (validate_is_post()) {
		set_time_limit(0);
		if ( !isset($_FILES['importFile']) ) {
			$file['error'] = '文件是空的。';
		}else if($_FILES["importFile"]["error"] > 0){
			$status['status'] = false;
			$status['message'] = "文件上传发生错误：".$_FILES["importFile"]["error"];
			return $status;
		}
		
		$tmp_file = $_FILES["importFile"]["tmp_name"];
		$file_types = explode ( ".", $_FILES ['importFile'] ['name'] );
		$file_type = $file_types [count ( $file_types ) - 1];
		
		/*判别是不是.xls文件，判别是不是excel文件*/
		if (strtolower ( $file_type ) != "xls")              
		{
			$status['status'] = false;
			$status['message'] = "文件格式不对，请上传.xls的Excel文件。";
			return $status;
		
		}
		
		//开始识别excel文件
		include_file(COM_PATH.'/system/Excel/PHPExcel.php');
		$objReader = PHPExcel_IOFactory::createReader('Excel5'); 
		$objReader->setReadDataOnly(true); 
		$objPHPExcel = $objReader->load($tmp_file); 
		$objWorksheet = $objPHPExcel->getActiveSheet(); 
		
		$highestRow = $objWorksheet->getHighestRow(); 
		$highestColumn = $objWorksheet->getHighestColumn(); 
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 
		$excelData = array(); 
		
		for ($row = 2; $row <= $highestRow; $row++) { 
			for ($col = 0; $col < $highestColumnIndex; $col++) {
				$field = (string)$objWorksheet->getCellByColumnAndRow($col, 1)->getValue();
				if($field!=null)
					$excelData[$row][$field] =(string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
			}
			//每获取一行，添加到数据库
			$identifier	= isset($excelData[$row]['询盘ID'])?$excelData[$row]['询盘ID']:null;
			$source		= isset($excelData[$row]['来源'])?$excelData[$row]['来源']:null;
			$infoclass	= isset($excelData[$row]['信息组部门'])?$excelData[$row]['信息组部门']:null;
			$infomember	= isset($excelData[$row]['信息员'])?$excelData[$row]['信息员']:null;
			$addtime	= isset($excelData[$row]['询盘日期'])?$excelData[$row]['询盘日期']:null;
			$datetime	= isset($excelData[$row]['时间'])?$excelData[$row]['时间']:null;
			$inforate	= isset($excelData[$row]['信息评级'])?$excelData[$row]['信息评级']:null;
			$operational= isset($excelData[$row]['业务人员'])?$excelData[$row]['业务人员']:null;
			$saleunit	= isset($excelData[$row]['销售部门'])?$excelData[$row]['销售部门']:null;
			$salesubunit= isset($excelData[$row]['销售组别'])?$excelData[$row]['销售组别']:null;
			$language	= isset($excelData[$row]['语种'])?$excelData[$row]['语种']:null;
			$keywords	= isset($excelData[$row]['关键词'])?$excelData[$row]['关键词']:null;
			$refererurl	= isset($excelData[$row]['来源网址'])?$excelData[$row]['来源网址']:null;
			$landingurl	= isset($excelData[$row]['到访网址'])?$excelData[$row]['到访网址']:null;
			$country	= isset($excelData[$row]['所属国家'])?$excelData[$row]['所属国家']:null;
			$producttype= isset($excelData[$row]['产品类型'])?$excelData[$row]['产品类型']:null;
			$auction	= isset($excelData[$row]['是否竞价'])?$excelData[$row]['是否竞价']:null;
			$sesource	= isset($excelData[$row]['搜索引擎来源'])?$excelData[$row]['搜索引擎来源']:null;
			$remarks	= isset($excelData[$row]['备注'])?$excelData[$row]['备注']:null;
			$continent	= isset($excelData[$row]['大洲'])?$excelData[$row]['大洲']:null;
			$category	= array();
            $agency     = isset($excelData[$row]['媒介'])?$excelData[$row]['媒介']:null;
			
			unset($excelData[$row]);
			
			$terms = term_get_byname($saleunit);
			if(!empty($terms )) {
				$category[] = $terms['termid'];
				$sortid		= $terms['termid'];
			}
			$terms_sub = term_get_byname($salesubunit);
			if(!empty($terms_sub))
				$category[] = $terms_sub['termid'];
			
			 $data = array(
			 	//'sortid'   		=> $sortid,
				'identifier'    => $identifier,
				'category' 		=> $category,
				'keywords' 		=> esc_html(trim($keywords)),
				'operational' 	=> $operational,
				'remarks' 		=> esc_html($remarks),
				'datetime' 		=> date_to_stamp($datetime),
				'addtime'		=> date_to_stamp($addtime),
				'source'		=> $source,
				'language'		=> $language,
				'referer'		=> $refererurl,
				'landingurl'	=> $landingurl,
				'sesource'		=> $sesource,
				'auction'		=> $auction,
				'producttype'	=> $producttype,
				'country'		=> $country,
				'continent'		=> $continent,
				//'email'			=> $email,
				//'phone'			=> $phone,
				'inforate'		=> $inforate,
				//'serial'		=> $serial,
				'infoclass'		=> $infoclass,
				'infomember'	=> $infomember,
				'saleunit'		=> $saleunit,
				'salesubunit'	=> $salesubunit,
				
			);
            // import meta data
            if($agency){
                $data['meta']['agency'] = $agency;
            }
			if($sortid)
				$data['sortid'] = $sortid;
			$user = user_get_byname($infomember);
			if($user) {
				$data['userid']	= $user['userid'];
				$data['grpid']	= $user['usergroup'];
			}
			$serial = explode("-", $identifier);
			$serial = isset($serial[2])?$serial[2]:1;
			preg_match('/\d+/', $serial, $matches);
			post_add($matches[0],$remarks,$data);
		} 
		history_import($highestRow,$tmp_file);
		$status['status'] = true;
		$status['message'] = "文件上传完成。";
		return $status;
	}
}
function date_to_stamp( $date) {
	$date = date_parse_from_format('Y-m-d H:i:s',$date);
	$timestamp = mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']);
	return $timestamp;
}
if( !function_exists('date_parse_from_format') ){
    function date_parse_from_format($format, $date) {
        // reverse engineer date formats
        $keys = array(
            'Y' => array('year', '\d{4}'),              //Année sur 4 chiffres
            'y' => array('year', '\d{2}'),              //Année sur 2 chiffres
            'm' => array('month', '\d{2}'),             //Mois au format numérique, avec zéros initiaux
            'n' => array('month', '\d{1,2}'),           //Mois sans les zéros initiaux
            'M' => array('month', '[A-Z][a-z]{3}'),     //Mois, en trois lettres, en anglais
            'F' => array('month', '[A-Z][a-z]{2,8}'),   //Mois, textuel, version longue; en anglais, comme January ou December
            'd' => array('day', '\d{2}'),               //Jour du mois, sur deux chiffres (avec un zéro initial)
            'j' => array('day', '\d{1,2}'),             //Jour du mois sans les zéros initiaux
            'D' => array('day', '[A-Z][a-z]{2}'),       //Jour de la semaine, en trois lettres (et en anglais)
            'l' => array('day', '[A-Z][a-z]{6,9}'),     //Jour de la semaine, textuel, version longue, en anglais
            'u' => array('hour', '\d{1,6}'),            //Microsecondes
            'h' => array('hour', '\d{2}'),              //Heure, au format 12h, avec les zéros initiaux
            'H' => array('hour', '\d{2}'),              //Heure, au format 24h, avec les zéros initiaux
            'g' => array('hour', '\d{1,2}'),            //Heure, au format 12h, sans les zéros initiaux
            'G' => array('hour', '\d{1,2}'),            //Heure, au format 24h, sans les zéros initiaux
            'i' => array('minute', '\d{2}'),            //Minutes avec les zéros initiaux
            's' => array('second', '\d{2}')             //Secondes, avec zéros initiaux
        );

        // convert format string to regex
        $regex = '';
        $chars = str_split($format);
        foreach ( $chars AS $n => $char ) {
            $lastChar = isset($chars[$n-1]) ? $chars[$n-1] : '';
            $skipCurrent = '\\' == $lastChar;
            if ( !$skipCurrent && isset($keys[$char]) ) {
                $regex .= '(?P<'.$keys[$char][0].'>'.$keys[$char][1].')';
            }
            else if ( '\\' == $char ) {
                $regex .= $char;
            }
            else {
                $regex .= preg_quote($char);
            }
        }

        $dt = array();
        $dt['error_count'] = 0;
        // now try to match it
        if( preg_match('#^'.$regex.'$#', $date, $dt) ){
            foreach ( $dt AS $k => $v ){
                if ( is_int($k) ){
                    unset($dt[$k]);
                }
            }
            if( !checkdate($dt['month'], $dt['day'], $dt['year']) ){
                $dt['error_count'] = 1;
            }
        }
        else {
            $dt['error_count'] = 1;
        }
        $dt['errors'] = array();
        $dt['fraction'] = '';
        $dt['warning_count'] = 0;
        $dt['warnings'] = array();
        $dt['is_localtime'] = 0;
        $dt['zone_type'] = 0;
        $dt['zone'] = 0;
        $dt['is_dst'] = '';
        return $dt;
    }
}

//搜索引擎来源判定，检测来源网址，竞价，优化，EDM或SNS
function determine_url($url, $type, $result=null) {
	if($url==null) return null;
	$url = trim(strtolower($url)); //转换成小写,并去除空格
	if (substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://'){
		$url = 'http://' . $url;
	}
	if(strrpos($url, "@")!==false){
        $url = str_replace('@','.',$url);
    }
	if(strrpos($url,"translate.googleusercontent.com")!==false){
		parse_str($url);
		$url = isset($u)?$u:$url;
	}
    
    //如果是google重定向地址
    $url_parse = parse_url($url);
    if(strrpos($url_parse['host'],"google")!==false){
        if(isset($url_parse['query'])){
            parse_str($url_parse['query'],$path_array);
            if(isset($path_array['sa']) && $path_array['sa']=='t' && isset($path_array['url'])){
                $url = $path_array['url'];
            }
        } 
    }

	//if(!validate_is($url,VALIDATE_IS_URL)) return false;
	//确保url是正确的格式： 以http://开头的
	$info = parse_url($url);
	if(!isset($info['host'])) return false;
	
	$host = $info['host'];
	
	//替换掉开关的www.字符
	$host = preg_replace('/^www./i','',$host);
	
	//开始判断
	$rules = rule_gets($type,'enabled');
	if(!empty($rules)){
		foreach($rules as $rule){
			$is_true = true;
			foreach($rule['pattern'] as $pattern) {
				$pattern = strtolower($pattern);
				if (@preg_match($pattern, $host)==false) {
					$is_true = false;
					break;
				}
			}
			$result = $is_true?$rule['result']:$result;
		}
	}
    //如果没有，则查询域名列表domain.php
    if(!$result){ 
        $domain_list = domain_get_list();
        foreach ($domain_list as $author => $domainArray) {
            $uri = new parseURL($host);
            $registerableDomain = $uri->getRegisterableDomain();
            foreach($domainArray as $domain){
                if(trim($registerableDomain)==trim($domain)) {
                    return  $author;
                }
            }
        }
    }
	return $result;
}

function re_determine_url($days=90) {
	$db = get_conn();
	$where = " WHERE `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY)";
	$where.= " AND `type`='inquiry' AND `landingurl`<>''";
	$sql = "SELECT `postid`, `landingurl`, `belong` FROM `#@_post` {$where};";
	$result = $db->query($sql);
	set_time_limit(0);
	if($result){
		while ($data = $db->fetch($result)) {
			set_time_limit(0);
			$new = determine_url($data['landingurl'],'网站所属人');
			if($data['landingurl']!=$new && $new!==false)
				post_edit($data['postid'],array('belong'=>$new));
		}
	}
	return array('status'=>true,'message'=>'重新判定所属人完成.');
}

/**
 * 检测网址搜索引擎来源
 * 2013-12-14 添加此功用
 * @param  [string] $url [要检测的url]
 * @return [string]      [返回搜索引擎判断结果]
 */
function detect_se($url){

    $engines = array(
        'google' => '谷歌搜索',
        'bing' => 'Bing',
        'yahoo' => 'yahoo',
        'sougou' => '搜狗',
        'soso' => '搜搜',
        'live' => 'live',
        'yandex' => 'yandex',
        'so.com' => '360搜索',
        'baidu' => '百度',
        'doubleclick' => '谷歌内容',
        'ask' => 'ask',
        'youtube' => 'youtube',
        'mail.ru' => 'mail.ru',
    );

    $result = null;
    if($url==null) return null;
    $url = trim(strtolower($url)); //转换成小写,并去除空格
    if (substr($url, 0, 7) !== 'http://' && substr($url, 0, 8) !== 'https://'){
        $url = 'http://' . $url;
    }

    $info = parse_url($url);
    if(!isset($info['host'])) return false;
    
    $host = $info['host'];
    
    //开始判断
    $rules = rule_gets('搜索引擎来源','enabled');
    if(!empty($rules)){
        foreach($rules as $rule){
            $is_true = true;
            foreach($rule['pattern'] as $pattern) {
                $pattern = strtolower($pattern);
                if (@preg_match($pattern, $host)==false) {
                    $is_true = false;
                    break;
                }
            }
            $result = $is_true?$rule['result']:$result;
        }
    }
    if(!$result){
        foreach($engines as $k=>$v){
            if(strpos($host,$k)!==false){
                $result = $v;
            }
        }
    }
    return $result;
}
/**
 * 从搜索引擎来源URL中得到关键词
 * @param  [string] $referrer [来源搜索引擎的referer]
 * @return [string]           [返回关键词]
 */
function get_sekeywords($referrer){
    $parsed = parse_url( $referrer, PHP_URL_QUERY );
    parse_str( $parsed, $query );
    return $query['q'];
}
/**
 * 检测国家所属大洲
 *
 * @param int $country   国家名字
 * @return string
 */
function check_continent($country){
	if($country==null) return null;
    $list = array('亚洲'=>array('中国','香港','台湾','澳门',"阿富汗","阿拉伯联合酋长国","阿曼","阿塞拜疆","巴基斯坦","巴勒斯坦","巴林","不丹","朝鲜","东帝汶","菲律宾","格鲁吉亚","哈萨克斯坦","韩国","吉尔吉斯斯坦","柬埔寨","卡塔尔","科威特","老挝","黎巴嫩","马尔代夫","马来西亚","蒙古国","孟加拉国","缅甸","尼泊尔","日本","塞普勒斯","沙特阿拉伯","斯里兰卡","塔吉克斯坦","泰国","土耳其","土库曼斯坦","文莱","乌兹别克斯坦","新加坡","叙利亚","亚美尼亚","也门","伊拉克","伊朗","以色列","印度","印度尼西亚","约旦","越南"),
        '非洲'=>array('阿尔及利亚','埃及','埃塞俄比亚','安哥拉','贝宁共和国','波札那','博茨瓦纳','布基纳法索','蒲隆地','赤道几内亚','多哥','厄立特里亚','佛得角','冈比亚','刚果','刚果共和国','刚果民主共和国','吉布提','几内亚','几内亚比绍','加那利群岛','加纳','加蓬','辛巴威','喀麦隆','科摩罗','科特迪瓦','肯尼亚','莱索托','利比里亚','利比亚','留尼旺（法）','卢旺达','马达加斯加','马德拉群岛（葡 ）','马拉维','马里共和国','毛里求斯','毛里塔尼亚','摩洛哥','莫桑比克','纳米比亚','南非','尼日尔','尼日利亚','塞拉利昂','塞内加尔','塞舌尔','圣多美及普林西比','圣赫勒拿（英）','斯威士兰','苏丹','索马里','坦桑尼亚','突尼斯','乌干达','西撒哈拉','亚速尔群岛（葡）','赞比亚','乍得','中非'),
        '南美洲'=>array('阿根廷','巴拉圭','巴西','玻利维亚','厄瓜多尔','法属圭亚那','哥伦比亚','圭亚那','秘鲁','苏里南','委内瑞拉','乌拉圭','智利'),
        '北美洲'=>array('阿鲁巴','安圭拉','安提瓜和巴布达','巴巴多斯','巴哈马','巴拿马','百慕大','波多黎各','伯利兹','多米尼加','多米尼克','哥斯达黎加','格林纳达','格陵兰','古巴','瓜德罗普岛','海地','荷属安的列斯','洪都拉斯','加拿大','开曼群岛','马提尼克','美国','美属维尔京群岛','蒙特塞拉特','墨西哥','尼加拉瓜','萨尔瓦多','圣基茨和尼维斯','圣卢西亚','圣文森特和格林纳丁斯','特克斯和凯科斯群岛','特立尼达和多巴哥','危地马拉','牙买加','英属维尔京群岛'),
        '大洋洲'=>array('澳大利亚','巴布亚新几内亚','北马里亚纳','玻利尼西亚','法属波利尼西亚','斐济','关岛','基里巴斯','库克群岛','马绍尔群岛','美属萨摩亚','密克罗尼西亚','瑙鲁','纽埃','帕劳','皮特凯恩岛','萨摩亚','所罗门群岛','汤加','图瓦卢','托克劳','瓦利斯与富图纳','瓦努阿图','新喀里多尼亚','新西兰'),
        '欧洲'=>array('阿尔巴尼亚','爱尔兰共和国','爱沙尼亚','安道尔','奥地利','白俄罗斯共和国','保加利亚','比利时','冰岛','波斯尼亚和黑塞哥维那','波兰','丹麦','德国','俄罗斯','法国','法罗群岛','梵蒂冈','芬兰','荷兰','黑山','捷克','克罗地亚','拉脱维亚','立陶宛','列支敦士登','卢森堡','罗马尼亚','马耳他','马其顿共和国','摩尔多瓦','摩纳哥','挪威','南斯拉夫','葡萄牙','瑞典','瑞士','塞尔维亚','圣马力诺','斯洛伐克','斯洛文尼亚','乌克兰','西班牙','希腊','匈牙利','意大利','英国','科索沃')
        
    );
/*
	$list = array('亚洲'=>array('中国','中国香港','中国台湾','中国澳门',"阿富汗","阿联酋","阿曼","阿塞拜疆","巴基斯坦","巴勒斯坦","巴林","不丹","朝鲜","东帝汶","菲律宾","格鲁吉亚","哈萨克斯坦","韩国","吉尔吉斯斯坦","柬埔寨","卡塔尔","科威特","老挝","黎巴嫩","马尔代夫","马来西亚","蒙古","孟加拉国","缅甸","尼泊尔","日本","塞普勒斯","沙特","斯里兰卡","塔吉克斯坦","泰国","土耳其","土库曼斯坦","文莱","乌兹别克斯坦","新加坡","叙利亚","亚美尼亚","也门","伊拉克","伊朗","以色列","印度","印度尼西亚","约旦","越南"),
		'非洲'=>array('阿尔及利亚','埃及','埃塞俄比亚','安哥拉','贝宁','波札那','博茨瓦纳','布基纳法索','布隆迪','赤道几内亚','多哥','厄立特里亚','佛得角','冈比亚','刚果','刚果（布）','刚果（金）','吉布提','几内亚','几内亚比绍','加那利群岛','加纳','加蓬','津巴布韦','喀麦隆','科摩罗','科特迪瓦','肯尼亚','莱索托','利比里亚','利比亚','留尼旺（法）','卢旺达','马达加斯加','马德拉群岛（葡 ）','马拉维','马里','毛里求斯','毛里塔尼亚','摩洛哥','莫桑比克','纳米比亚','南非','尼日尔','尼日利亚','塞拉利昂','塞内加尔','塞舌尔','圣多美及普林西比','圣赫勒拿（英）','斯威士兰','苏丹','索马里','坦桑尼亚','突尼斯','乌干达','西撒哈拉','亚速尔群岛（葡）','赞比亚','乍得','中非'),
		'南美洲'=>array('阿根廷','巴拉圭','巴西','玻利维亚','厄瓜多尔','法属圭亚那','哥伦比亚','圭亚那','秘鲁','苏里南','委内瑞拉','乌拉圭','智利'),
		'北美洲'=>array('阿鲁巴','安圭拉','安提瓜和巴布达','巴巴多斯','巴哈马','巴拿马','百慕大','波多黎各','伯利兹','多米尼加','多米尼克','哥斯达黎加','格林纳达','格陵兰','古巴','瓜德罗普岛','海地','荷属安的列斯','洪都拉斯','加拿大','开曼群岛','马提尼克','美国','美属维尔京群岛','蒙特塞拉特','墨西哥','尼加拉瓜','萨尔瓦多','圣基茨和尼维斯','圣卢西亚','圣文森特和格林纳丁斯','特克斯和凯科斯群岛','特立尼达和多巴哥','危地马拉','牙买加','英属维尔京群岛'),
		'大洋洲'=>array('澳大利亚','巴布亚新几内亚','北马里亚纳','玻利尼西亚','法属波利尼西亚','斐济','关岛','基里巴斯','库克群岛','马绍尔群岛','美属萨摩亚','密克罗尼西亚','瑙鲁','纽埃','帕劳','皮特凯恩岛','萨摩亚','所罗门群岛','汤加','图瓦卢','托克劳','瓦利斯与富图纳','瓦努阿图','新喀里多尼亚','新西兰'),
		'欧洲'=>array('阿尔巴尼亚','爱尔兰','爱沙尼亚','安道尔','奥地利','白俄罗斯','保加利亚','比利时','冰岛','波黑','波兰','丹麦','德国','俄罗斯','法国','法罗群岛','梵蒂冈','芬兰','荷兰','黑山','捷克','克罗地亚','拉脱维亚','立陶宛','列支敦士登','卢森堡','罗马尼亚','马耳他','马其顿','摩尔多瓦','摩纳哥','挪威','葡萄牙','瑞典','瑞士','塞尔维亚','圣马力诺','斯洛伐克','斯洛文尼亚','乌克兰','西班牙','希腊','匈牙利','意大利','英国')
		
	);
*/
	foreach ($list as $continents=>$countries) {
		if(in_array($country, $countries)) {
			return $continents;
		}
	}
	return null;
}
/**
 * 清除错误日志
 */
function clearLog($admin = false){
	if(!$admin){
		return false;
	}
	
	if(@file_exists(ABS_PATH.'/error.log')){
		@unlink(ABS_PATH.'/error.log');
	}
	
	return true;
}

/**
 * 删除缓存
 */
function clearCache($admin = false){
	if(!$admin){
		return false;
	}
	return rmdirs(CACHE_PATH);
}

/**
 * http://ca2.php.net/manual/en/function.readfile.php#48683
 * @param  [string]  $filename [description]
 * @param  boolean $retbytes [description]
 * @return [string or boolean]            [description]
 */
function readfile_chunked($filename,$retbytes=true) { 
   $chunksize = 1*(1024*1024); // how many bytes per chunk 
   $buffer = ''; 
   $cnt =0; 
   // $handle = fopen($filename, 'rb'); 
   $handle = fopen($filename, 'rb'); 
   if ($handle === false) { 
       return false; 
   } 
   while (!feof($handle)) { 
       $buffer = fread($handle, $chunksize); 
       return $buffer; 
       ob_flush(); 
       flush(); 
       if ($retbytes) { 
           $cnt += strlen($buffer); 
       } 
   } 
       $status = fclose($handle); 
   if ($retbytes && $status) { 
       return $cnt; // return num. bytes delivered like readfile() does. 
   } 
   return $status; 

} 
/**
 * 显示错误日志
 */
function showErrLog(){
	$log = '没有错误信息。';
	$rows = 2;
	$path = ABS_PATH.'/error.log';
	if(@file_exists(ABS_PATH.'/error.log')){
        $log = readfile_chunked(ABS_PATH.'/error.log');
		// $log = @file_get_contents(ABS_PATH.'/error.log');
		$rows = ceil(@filesize(ABS_PATH.'/error.log')/30);
		if($rows > 30){
            $rows = 30;
		}
	}
	
	return '<textarea rows="'.$rows.'" readonly="readonly" class="span8">'.$log.'</textarea>';
}

//http://broqun.blog.163.com/blog/static/7065565120101028114050585/
function tail($fp,$n,$base=5)
{
    //assert($n>0);
    $pos = $n+1;
    $lines = array();
    while(count($lines)<=$n){
        try{
            fseek($fp,-$pos,SEEK_END);
        } catch (Exception $e){
            fseek(0);
            break;
        }
        $pos *= $base;
        while(!feof($fp)){
            array_unshift($lines,fgets($fp));
        }
    }
    return array_slice($lines,0,$n);
}
/**
 * 随机字符串
 *
 * @param int $length
 * @param string $charlist
 * @return string
 */
function str_rand($length=6,$charlist='0123456789abcdefghijklmnopqrstopwxyz'){
    $charcount = strlen($charlist); $str = null;
    for ($i=0;$i<$length;$i++) {
        $str.= $charlist[mt_rand(0,$charcount-1)];
    }
    return $str;
}
/**
 * 检查状态
 *
 * @param bool $state
 * @return string
 */
function test_result($state) {
    return $state ? '<i class="icon-ok" style="color:#009900;"></i>' : '<i class="icon-ok" style="color:#ff0000;"></i>';
}
/**
 * 获取所有联系人名字
 *
 */
function get_all_contact(){
	$db = get_conn(); $result = array();
	$rs = $db->query("SELECT `name` FROM `#@_contact`;");
	while ($row = $db->fetch($rs)) {
		$result[] = $row['name'];
    }
	return $result;
}

/**
 * Guess the URL for the site.
 *
 * Will remove admin links to retrieve only return URLs not in the admin
 * directory.
 *
 * @since 1.0.0
 *
 * @return string
 */
function guess_url() {
	//in config.php file, you can define siteurl
	if ( defined('SITEURL') && '' != SITEURL ) {
		$url = SITEURL;
	} else {
		$schema = is_ssl() ? 'https://' : 'http://';
		$url = preg_replace('|/admin/.*|i', '', $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	}
	return rtrim($url, '/');
}
/**
 * Determine if SSL is used.
 *
 *
 * @return bool True if SSL, false if not used.
 */
function is_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}
/**
 * 获取系统的主页地址
 *
 *
 * @return string.
 */
function get_home_url() {
	$url = C('System.home');
	if($url==false){
		$url = guess_url();
	}
	return $url;
}
/**
 * Removes trailing slash if it exists.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 *
 * @param string $string What to remove the trailing slash from.
 * @return string String without the trailing slash.
 */
function untrailingslashit($string) {
	return rtrim($string, '/');
}
/**
 * Appends a trailing slash.
 *
 * Will remove trailing slash if it exists already before adding a trailing
 * slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @uses untrailingslashit() Unslashes string if it was slashed already.
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function trailingslashit($string) {
	return untrailingslashit($string) . '/';
}

/**
 * Retrieve mod_rewrite formatted rewrite rules to write to .htaccess.
 *
 * Does not actually write to the .htaccess file, but creates the rules for
 * the process that will.
 *
 *
 * @return string
 */
function mod_rewrite_rules() {
	$home_url = C('System.home')?C('System.home'):guess_url();
	$home_root = parse_url($home_url);
	if ( isset( $home_root['path'] ) )
		$home_root = trailingslashit($home_root['path']);
	else
		$home_root = '/';

	$rules = "<IfModule mod_rewrite.c>\n";
	$rules .= "RewriteEngine On\n";
	$rules .= "RewriteBase $home_root\n";
	$rules .= "RewriteRule ^index\.php$ - [L]\n"; // Prevent -f checks on index.php.
	//使用简单规则
	$rules .= "RewriteCond %{REQUEST_FILENAME} !-f\n" .
		"RewriteCond %{REQUEST_FILENAME} !-d\n" .
		"RewriteRule . {$home_root}{$this->index} [L]\n";

	$rules .= "</IfModule>\n";

	return $rules;
}
function save_mod_rewrite_rules() {
	$home_path = ABS_PATH.'/';
	$htaccess_file = $home_path.'.htaccess';

	// If the file doesn't already exist check for write access to the directory and whether we have some rules.
	// else check for write access to the file.
	if ((!file_exists($htaccess_file) && is_writable($home_path)) || is_writable($htaccess_file)) {
		if ( got_mod_rewrite() ) {
			$rules = explode( "\n", mod_rewrite_rules() );
			return insert_with_markers( $htaccess_file, 'InfoMaster', $rules );
		}
	}

	return false;
}
/**
 * Does the specified module exist in the apache config?
 *
 *
 * @param string $mod e.g. mod_rewrite
 * @param bool $default The default return value if the module is not found
 * @return bool
 */
function apache_mod_loaded($mod, $default = false) {
	global $is_apache;

	if ( !$is_apache )
		return false;

	if ( function_exists('apache_get_modules') ) {
		$mods = apache_get_modules();
		if ( in_array($mod, $mods) )
			return true;
	} elseif ( function_exists('phpinfo') ) {
			ob_start();
			phpinfo(8);
			$phpinfo = ob_get_clean();
			if ( false !== strpos($phpinfo, $mod) )
				return true;
	}
	return $default;
}
/**
 * {@internal Missing Short Description}}
 *
 * Inserts an array of strings into a file (.htaccess ), placing it between
 * BEGIN and END markers. Replaces existing marked info. Retains surrounding
 * data. Creates file if none exists.
 *
 *
 * @param unknown_type $filename
 * @param unknown_type $marker
 * @param unknown_type $insertion
 * @return bool True on write success, false on failure.
 */
function insert_with_markers( $filename, $marker, $insertion ) {
	if (!file_exists( $filename ) || is_writeable( $filename ) ) {
		if (!file_exists( $filename ) ) {
			$markerdata = '';
		} else {
			$markerdata = explode( "\n", implode( '', file( $filename ) ) );
		}

		if ( !$f = @fopen( $filename, 'w' ) )
			return false;

		$foundit = false;
		if ( $markerdata ) {
			$state = true;
			foreach ( $markerdata as $n => $markerline ) {
				if (strpos($markerline, '# BEGIN ' . $marker) !== false)
					$state = false;
				if ( $state ) {
					if ( $n + 1 < count( $markerdata ) )
						fwrite( $f, "{$markerline}\n" );
					else
						fwrite( $f, "{$markerline}" );
				}
				if (strpos($markerline, '# END ' . $marker) !== false) {
					fwrite( $f, "# BEGIN {$marker}\n" );
					if ( is_array( $insertion ))
						foreach ( $insertion as $insertline )
							fwrite( $f, "{$insertline}\n" );
					fwrite( $f, "# END {$marker}\n" );
					$state = true;
					$foundit = true;
				}
			}
		}
		if (!$foundit) {
			fwrite( $f, "\n# BEGIN {$marker}\n" );
			foreach ( $insertion as $insertline )
				fwrite( $f, "{$insertline}\n" );
			fwrite( $f, "# END {$marker}\n" );
		}
		fclose( $f );
		return true;
	} else {
		return false;
	}
}

/**
 * Retrieve the description for the HTTP status.
 *
 * @since 2.3.0
 *
 * @param int $code HTTP status code.
 * @return string Empty string if not found, or description if found.
 */
function get_status_header_desc( $code ) {
	global $wp_header_to_desc;

	$code = abs( intval( $code ) );

	if ( !isset( $wp_header_to_desc ) ) {
		$wp_header_to_desc = array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			102 => 'Processing',

			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			207 => 'Multi-Status',
			226 => 'IM Used',

			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => 'Reserved',
			307 => 'Temporary Redirect',

			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			422 => 'Unprocessable Entity',
			423 => 'Locked',
			424 => 'Failed Dependency',
			426 => 'Upgrade Required',

			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported',
			506 => 'Variant Also Negotiates',
			507 => 'Insufficient Storage',
			510 => 'Not Extended'
		);
	}

	if ( isset( $wp_header_to_desc[$code] ) )
		return $wp_header_to_desc[$code];
	else
		return '';
}
/**
 * Set HTTP status header.
 *
 * @since 2.0.0
 * @uses apply_filters() Calls 'status_header' on status header string, HTTP
 *		HTTP code, HTTP code description, and protocol string as separate
 *		parameters.
 *
 * @param int $header HTTP status code
 * @return unknown
 */
function status_header( $header ) {
	$text = get_status_header_desc( $header );

	if ( empty( $text ) )
		return false;

	$protocol = $_SERVER["SERVER_PROTOCOL"];
	if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
		$protocol = 'HTTP/1.0';
	$status_header = "$protocol $header $text";
	return @header( $status_header, true, $header );
}
/**
 * Sets the headers to prevent caching for the different browsers.
 *
 * Different browsers support different nocache headers, so several headers must
 * be sent so that all of them get the point that no caching should occur.
 *
 */
function nocache_headers() {
	$headers = array(
		'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
		'Last-Modified' => gmdate( 'D, d M Y H:i:s' ) . ' GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
		'Pragma' => 'no-cache',
	);
	foreach( $headers as $name => $field_value )
		@header("{$name}: {$field_value}");
}
/**
 * 得到所有技术人员的网站详细信息
 *
 */
function get_domain_data($type=null){
	$db = get_conn();
	if($type==null)
		$type = '网站所属人';
	$domain_total = $db->result(sprintf("SELECT COUNT(`ruleid`) FROM `#@_rule` WHERE `type`='%s' AND `domain`<>''", $type));
	$data = array('total'=>$domain_total,'details'=>array());
	$rs= $db->query("SELECT `result` as `name`,COUNT(`ruleid`) as `count` FROM `#@_rule`  WHERE `type`='网站所属人' AND `domain`<>''  GROUP BY `result`");
	while ($result = $db->fetch($rs)) {
		$name = $result['name'];
		$data['details'][$name]['sum'] =$result['count'];
		//获得每个人的域名信息
		$res = $db->query("SELECT `domain` FROM `#@_rule`  WHERE `type`='网站所属人' AND `domain`<>'' AND `result`='{$name}'");
		while($urls = $db->fetch($res)){
			$data['details'][$name]['domain'][] = $urls['domain'];
		}
	}
    //临时增加域名表中的数据
    $rs= $db->query("SELECT `domain`, `author` FROM `#@_domain` WHERE `status`='approved' ORDER BY `author`;");
    while ($result = $db->fetch($rs)) {
        //$whois = $Parser->lookup($result['domain']);
        $data['details'][$result['author']]['domain'][] = 'http://'.$result['domain'];
        $data['details'][$result['author']]['sum'] = 0;
    }
	return $data;
	//return json_encode($data);
}
/*
 * 显示时区选择列表.
 *
*/
function timezone_choice( $selected_zone ) {
	$continents = array( 'Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
	$zonen = array();
	foreach ( timezone_identifiers_list() as $zone ) {
		$zone = explode( '/', $zone );
		if ( !in_array( $zone[0], $continents ) ) {
			continue;
		}

		// This determines what gets set and translated - we don't translate Etc/* strings here, they are done later
		$exists = array(
			0 => ( isset( $zone[0] ) && $zone[0] ),
			1 => ( isset( $zone[1] ) && $zone[1] ),
			2 => ( isset( $zone[2] ) && $zone[2] ),
		);
		$exists[3] = ( $exists[0] && 'Etc' !== $zone[0] );
		$exists[4] = ( $exists[1] && $exists[3] );
		$exists[5] = ( $exists[2] && $exists[3] );

		$zonen[] = array(
			'continent'   => ( $exists[0] ? $zone[0] : '' ),
			'city'        => ( $exists[1] ? $zone[1] : '' ),
			'subcity'     => ( $exists[2] ? $zone[2] : '' ),
			't_continent' => ( $exists[3] ? translate( str_replace( '_', ' ', $zone[0] ), 'continents-cities' ) : '' ),
			't_city'      => ( $exists[4] ? translate( str_replace( '_', ' ', $zone[1] ), 'continents-cities' ) : '' ),
			't_subcity'   => ( $exists[5] ? translate( str_replace( '_', ' ', $zone[2] ), 'continents-cities' ) : '' )
		);
	}
	$structure = array();

	if ( empty( $selected_zone ) ) {
		$structure[] = '<option selected="selected" value="">选择一个城市</option>';
	}
	foreach ( $zonen as $key => $zone ) {
		// Build value in an array to join later
		$value = array( $zone['continent'] );

		if ( empty( $zone['city'] ) ) {
			// It's at the continent level (generally won't happen)
			$display = $zone['t_continent'];
		} else {
			// It's inside a continent group

			// Continent optgroup
			if ( !isset( $zonen[$key - 1] ) || $zonen[$key - 1]['continent'] !== $zone['continent'] ) {
				$label = $zone['t_continent'];
				$structure[] = '<optgroup label="'. esc_html( $label ) .'">';
			}

			// Add the city to the value
			$value[] = $zone['city'];

			$display = $zone['t_city'];
			if ( !empty( $zone['subcity'] ) ) {
				// Add the subcity to the value
				$value[] = $zone['subcity'];
				$display .= ' - ' . $zone['t_subcity'];
			}
		}

		// Build the value
		$value = join( '/', $value );
		$selected = '';
		if ( $value === $selected_zone ) {
			$selected = 'selected="selected" ';
		}
		$structure[] = '<option ' . $selected . 'value="' . esc_html( $value ) . '">' . esc_html( $display ) . "</option>";

		// Close continent optgroup
		if ( !empty( $zone['city'] ) && ( !isset($zonen[$key + 1]) || (isset( $zonen[$key + 1] ) && $zonen[$key + 1]['continent'] !== $zone['continent']) ) ) {
			$structure[] = '</optgroup>';
		}
	}
	$structure[] = '</optgroup>';

	return join( "\n", $structure );
}
function translate( $text, $domain = 'default' ) {
	return $text;
}
function current_time( $type, $gmt = 0 ) {
	switch ( $type ) {
		case 'mysql':
			return ( $gmt ) ? gmdate( 'Y-m-d H:i:s' ) : gmdate( 'Y-m-d H:i:s', ( time() + ( C( '.gmt_offset' ) * 60 * 60 ) ) );
			break;
		case 'timestamp':
			return ( $gmt ) ? time() : time() + ( C( '.gmt_offset' ) * 60 * 60 );
			break;
	}
}

function get_gmt_from_date( $string, $format = 'Y-m-d H:i:s' ) {
    $tz = C( '.timezone_string' );
    if ( $tz ) {
        $datetime = date_create( $string, new DateTimeZone( $tz ) );
        if ( ! $datetime )
            return gmdate( $format, 0 );
        $datetime->setTimezone( new DateTimeZone( 'UTC' ) );
        $string_gmt = $datetime->format( $format );
    } else {
        if ( ! preg_match( '#([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})#', $string, $matches ) )
            return gmdate( $format, 0 );
        $string_time = gmmktime( $matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1] );
        $string_gmt = gmdate( $format, $string_time - C( '.gmt_offset' ) * 60 * 60 );
    }
    return $string_gmt;
}

//显示gmt时间, 根据当前时区显示时间.
function date_gmt($dateformatstring,$unixtimestamp = false){
	if( $unixtimestamp === false ) {
		$unixtimestamp = time();
	}
	return gmdate($dateformatstring,$unixtimestamp + ( C( '.gmt_offset' ) * 60 * 60 ) );
}

function date_i18n( $dateformatstring, $unixtimestamp = false, $gmt = false ) {
	$i = $unixtimestamp;

	if ( false === $i ) {
		if ( ! $gmt )
			$i = current_time( 'timestamp' );
		else
			$i = time();
		// we should not let date() interfere with our
		// specially computed timestamp
		$gmt = true;
	}

	$datefunc = $gmt? 'gmdate' : 'date';
	
	//$dateformatstring = 'Y-m-d G:i:s';

	$j = @$datefunc( $dateformatstring, $i );
	return $j;
}
function mysql2date( $format, $date, $translate = true ) {
	if ( empty( $date ) )
		return false;

	if ( 'G' == $format )
		return strtotime( $date . ' +0000' );

	$i = strtotime( $date );

	if ( 'U' == $format )
		return $i;

	if ( $translate )
		return date_i18n( $format, $i );
	else
		return date( $format, $i );
}

function get_post_count_per_day($inforate=null,$days=30,$type='inquiry'){
	$db = get_conn();
	$where = " WHERE `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY)";
	$where .= sprintf(" AND `type`='%s'",$type);
	if($inforate){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$inforate);
	}
	$rs = $db->query("SELECT FROM_UNIXTIME(`datetime`) as post_time,YEAR(FROM_UNIXTIME(`datetime`)) as post_year, MONTH(FROM_UNIXTIME(`datetime`)) as post_month, DAY(FROM_UNIXTIME(`datetime`)) as post_day, COUNT(`postid`) as post_count FROM `#@_post`  $where GROUP BY post_day ORDER BY `datetime` DESC");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[] = array((strtotime($result['post_time']) * 1000), $result['post_count']);
	}
	return $data;
}
/**
 * 获取询盘信息
 *
 */
function get_inqiury_info($type='inquiry',$inforate=null,$days=30){
	$info_per_day = array(
		array(
			"type"	=>'ALL',
			"label" => '每日询盘总数',
			"color" => "#BA1E20",
			"data"  => get_post_count_per_day()
		),
		array(
			"type"	=>'A',
			"label" => 'A类询盘',
			"color" => "#0062E3",
			"data"  => get_post_count_per_day('A')
		),
		array(
			"type"	=>'B',
			"label" => 'B类询盘',
			"color" => "#FFFF33",
			"data"  => get_post_count_per_day('B')
		),
		array(
			"type"	=>'C',
			"label" => 'C类询盘',
			"color" => "#319400",
			"data"  => get_post_count_per_day('C')
		)
	);
	return $info_per_day;
}

function get_recent_inqiury($inforate=null,$days=30,$type='inquiry'){
	$db = get_conn();global $_USER;
	$name = $_USER['nickname'];
	$where = " WHERE FROM_UNIXTIME(`addtime`) BETWEEN DATE_SUB(NOW(), INTERVAL $days DAY)";
    $where = " WHERE date_format(FROM_UNIXTIME(`addtime`),'%Y-%m')=date_format(now(),'%Y-%m')"; //当前月数据
	$where .= sprintf(" AND `type`='%s' AND `belong`<>'' AND `belong`='%s'",$type,$name);
	if($inforate!=null){
		$where .= sprintf(" AND `inforate` = '%s'",$inforate);
	}else{
        $where .= " AND `inforate` <> ''";
    }
	$rs = $db->query("SELECT FROM_UNIXTIME(`addtime`) as post_time,YEAR(FROM_UNIXTIME(`addtime`)) as post_year, MONTH(FROM_UNIXTIME(`addtime`)) as post_month, DAY(FROM_UNIXTIME(`addtime`)) as post_day, COUNT(`postid`) as post_count, `belong` FROM `#@_post` $where GROUP BY post_year,post_month,post_day ORDER BY `addtime` DESC");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[] = array((strtotime($result['post_time']) * 1000), $result['post_count']);
	}
	return $data;
}

function get_recent_inqiury_info($type='inquiry',$inforate=null,$days=30){
	global $_USER;
	$name = $_USER['nickname'];
	$info_per_day = array(
		array(
			"type"	=>'ALL',
			"label" => $name.'的每日询盘总数',
			"color" => "#BA1E20",
			"data"  => get_recent_inqiury()
		),
		array(
			"type"	=>'A',
			"label" => $name.'的A类询盘',
			"color" => "#0062E3",
			"data"  => get_recent_inqiury('A')
		),
		array(
			"type"	=>'B',
			"label" => $name.'的B类询盘',
			"color" => "#FFFF33",
			"data"  => get_recent_inqiury('B')
		),
		array(
			"type"	=>'C',
			"label" => $name.'的C类询盘',
			"color" => "#319400",
			"data"  => get_recent_inqiury('C')
		)
	);
	return $info_per_day;
}


/**
 * 获得每个信息员这个月的信息总数
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function get_operator_info($inforate=null,$days=30,$type='inquiry') {
	$db = get_conn();
	$where = " WHERE `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY)";
	$where .= sprintf(" AND `type`='%s'",$type);
	if($inforate){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$inforate);
	}
	$rs = $db->query("SELECT FROM_UNIXTIME(`datetime`) as post_time,YEAR(FROM_UNIXTIME(`datetime`)) as post_year, MONTH(FROM_UNIXTIME(`datetime`)) as post_month, DAY(FROM_UNIXTIME(`datetime`)) as post_day, COUNT(`postid`) as post_count, infomember FROM `#@_post` $where GROUP BY infomember ORDER BY `datetime` DESC");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[] = array($result['infomember'], $result['post_count']);
	}
	return  array(array(
				"label"=>'最近30天信息数',
				"data"=>$data
			));
    
} // end func

function get_belong_info($inforate=null,$days=30,$type='inquiry'){
	$db = get_conn();
	//$where = " WHERE `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY)";
	$where = sprintf(" WHERE FROM_UNIXTIME(`datetime`) >= DATE_ADD(CURDATE(), INTERVAL -%d DAY)",$days);
	$where .= sprintf(" AND `type`='%s'",$type);
	$where .= " AND `belong`<>'' AND `xp_status`=''";
	if($inforate){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$inforate);
	}
	$rs = $db->query("SELECT * FROM (SELECT FROM_UNIXTIME(`datetime`) as post_time,YEAR(FROM_UNIXTIME(`datetime`)) as post_year, MONTH(FROM_UNIXTIME(`datetime`)) as post_month, DAY(FROM_UNIXTIME(`datetime`)) as post_day, COUNT(`postid`) as post_count, `belong` FROM `#@_post` $where GROUP BY `belong` ORDER BY `post_count` DESC) AS t2 WHERE post_count > 29");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[] = array($result['belong'], $result['post_count']);
	}
	
	return  array(array(
				"label"=>'最近30天询盘数',
				"data"=>$data
			));
}

/**
 * 获取询盘在各大洲的布情况
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function get_continents_info($inforate=null,$days=30,$type='inquiry'){
	$db = get_conn();
	$where = " WHERE `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY)";
	$where .= sprintf(" AND `type`='%s'",$type);
	if($inforate){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$inforate);
	}
	$rs = $db->query("SELECT `continent`, COUNT(`postid`) as post_count FROM `#@_post` {$where} GROUP BY `continent` ORDER BY post_count DESC");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[] = array("label"=>$result['continent'], "data"=>$result['post_count']);
	}

	return  $data;//array("label"=>"询盘信息各大洲分布", "data"=>$data);
    
} // end func


/**
 * 得到询盘在所有国家的信息
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function get_world_info($inforate=null,$days=30,$type='inquiry')
{
    $db = get_conn();
	$where = " WHERE 1";
	//$where .= " AND `datetime` BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY)";
	$where .= sprintf(" AND `type`='%s'",$type);
	if($inforate){
		$where .= sprintf(" AND `inforate` LIKE '%s'",$inforate);
	}
	$rs = $db->query("SELECT `country`, COUNT(`postid`) as post_count FROM `#@_post` {$where} GROUP BY `country` ORDER BY post_count DESC");
	$data = array();
	while($result = $db->fetch($rs)){
		$data[$result['country']] = $result['post_count'];
	}

	return  $data;//array("label"=>"询盘信息各大洲分布", "data"=>$data);
} // end func

/**
 * 获得用户的IP地址
 *
 * @access private
 * @param string $iri
 * @return array
 */
function get_ip_address() {
    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                // trim for safety measures
                $ip = trim($ip);
				return $ip;
            }
        }
    }
 
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
}

function get_ip_place(){
	$ip = get_ip_address();
	$url = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.$ip;
	$result = @file_get_contents($url);
	$result = json_decode($result);
	$result = array(
		$country	= isset($result->country)?$result->country:'',
		$proveince	= isset($result->province)?$result->province:'',
		$city		= isset($result->city)?$result->city:'',
		$district	= isset($result->district)?$result->district:'',
		$isp		= isset($result->isp)?$result->isp:''
	);
	
	return implode(", ",array_filter($result));;
}

function get_user_groups($userid) {
	$user = user_get_byid($userid);
	if(@unserialize($user['other_usergroups'])===false)
		$other_group = $user['other_usergroups'];
	else
		$other_group = unserialize($user['other_usergroups']);
		
	$usergroup = array_merge($user['usergroup'],$other_group);
	
	return $usergroup;
}

function get_group_users($group){
	$db = get_conn(); $result = array();
	$rs = $db->query("SELECT * FROM `#@_user` WHERE `status` = 0;");
	while ($row = $db->fetch($rs)) {
		$usergroup = array();
		if(@unserialize($row['other_usergroups'])===false)
			$other_group = $row['other_usergroups'];
		else
			$other_group = unserialize($row['other_usergroups']);
		array_push($usergroup,$row['usergroup']);
		if(is_array($other_group))
			$usergroup = array_merge($usergroup,$other_group);
		else
			array_push($usergroup,$other_group);
		if(instr($group,$usergroup))
			$result[] = $row['userid'];
    }
	return $result;
}
/**
 * 保存裁剪的图片
*/
function save_image($x1,$y1,$w,$h) {
	global $_USER;
	$DestinationDirectory   = $_USER['avatar'];
	$DestinationImageName	= $_USER['avatar'];
	$Quality                = 100;
	$ImageType				= $_USER['avatar_type'];

	
	$status = array('error'=>'','msg'=>'');
	$image_path = ADMIN_PATH.'/20007.jpg';
	
	list($CurWidth,$CurHeight)=getimagesize($DestinationImageName);
	
	switch(strtolower($ImageType))
	{
		case 'image/png':
			$CreatedImage =  imagecreatefrompng($DestinationImageName);
			break;
		case 'image/gif':
			$CreatedImage =  imagecreatefromgif($DestinationImageName);
			break;
		case 'image/jpeg':
		case 'image/pjpeg':
			$CreatedImage = imagecreatefromjpeg($DestinationImageName);
			break;
		default:
			$status['error'] ='不支持的图片类型.';
			return $status;
	}

	cropImage($CurWidth,$CurHeight,$x1,$y1,$w,$h,$DestinationImageName,$CreatedImage,$Quality,$ImageType);
	$status['msg'] = rel_root($DestinationImageName);
	
	return $status;
}
/**
 * 保存图片到服务器
 *
 * @param string $x1
 * @param string $y1
 * @param string $w
 * @param string $h
 */
function upload_image() {
	global $_USER;
	$status = array('error'=>'','msg');
	if(isset($_POST)){
		if(!isset($_FILES['uploadimage']) || !is_uploaded_file($_FILES['uploadimage']['tmp_name'])){
				$status['error'] ='上传过程中发生错误, 请重试!';
				return $status;
		}
		$RandomNumber   = rand(0, 9999999999);
		
		$ImageName      = str_replace(' ','-',strtolower($_FILES['uploadimage']['name']));
    	$ImageSize      = $_FILES['uploadimage']['size']; // Obtain original image size
    	$TempSrc        = $_FILES['uploadimage']['tmp_name']; // Tmp name of image file stored in PHP tmp folder
    	$ImageType      = $_FILES['uploadimage']['type']; //Obtain file type, returns "image/png", image/jpeg, text/plain etc.
		
		switch(strtolower($ImageType))
		{
			case 'image/png':
				$CreatedImage =  imagecreatefrompng($_FILES['uploadimage']['tmp_name']);
				break;
			case 'image/gif':
				$CreatedImage =  imagecreatefromgif($_FILES['uploadimage']['tmp_name']);
				break;
			case 'image/jpeg':
			case 'image/pjpeg':
				$CreatedImage = imagecreatefromjpeg($_FILES['uploadimage']['tmp_name']);
				break;
			default:
				$status['error'] ='不支持的图片类型.';
				return $status;
		}
		if($ImageSize>10000) {
			$status['error'] = '图片大小限制为10KB.';
			return $status;
		}
		//Get file extension from Image name, this will be re-added after random name
    	$ImageExt = substr($ImageName, strrpos($ImageName, '.'));
    	$ImageExt = str_replace('.','',$ImageExt);

    	//remove extension from filename
    	$ImageName      = preg_replace("/\.[^.\s]{3,4}$/", "", $ImageName);
		$upload_path = COM_PATH.'/upload/avatar/';
		if(!file_exists($upload_path)) {
			if (!@mkdir($upload_path, 0777)) {
				$status['error'] = '目录不可写.';
				return $status;
			}
			chmod($upload_path, 0777);
		}
		if (!@is_dir($upload_path) || !is_writable($upload_path)) {
			$status['error'] = '目录不可写.';
			return $status;
		}
		$NewImageName = $ImageName.'-'.$RandomNumber.'.'.$ImageExt;
		// save
		if (!@move_uploaded_file($TempSrc, $upload_path.$NewImageName)) {
			continue;
		}else {
			$status['msg'] = rel_root($upload_path.$NewImageName);
		}
		
		//删除原文件
		if(isset($_USER['avatar']) && file_exists($_USER['avatar']))
				unlink($_USER['avatar']);
		//保存图片路径到个人资料里
		user_edit($_USER['userid'],array('avatar'	=> $upload_path.$NewImageName,'avatar_type'=>$ImageType));

	}
	return $status;
}


//This function corps image to create exact square images, no matter what its original size!
function cropImage($CurWidth,$CurHeight,$x1, $y1, $iSize_W,$iSize_H,$DestFolder,$SrcImage,$Quality,$ImageType)
{
    //Check Image size is not 0
    if($CurWidth <= 0 || $CurHeight <= 0)
    {
        return false;
    }
	
	 if($CurWidth>$CurHeight)
    {
        $y_offset = 0;
        $x_offset = ($CurWidth - $CurHeight) / 2;
        $square_size    = $CurWidth - ($x_offset * 2);
    }else{
        $x_offset = 0;
        $y_offset = ($CurHeight - $CurWidth) / 2;
        $square_size = $CurHeight - ($y_offset * 2);
    }

    $NewCanves  = imagecreatetruecolor($iSize_W,$iSize_H);
    if(imagecopyresampled($NewCanves, $SrcImage, 0,0, $x1, $y1,  $iSize_W, $iSize_H, $iSize_W, $iSize_H))
    {
        switch(strtolower($ImageType))
        {
            case 'image/png':
                imagepng($NewCanves,$DestFolder);
                break;
            case 'image/gif':
                imagegif($NewCanves,$DestFolder);
                break;
            case 'image/jpeg':
            case 'image/pjpeg':
                imagejpeg($NewCanves,$DestFolder,$Quality);
                break;
            default:
                return false;
        }
    //Destroy image, frees up memory
    if(is_resource($NewCanves)) {imagedestroy($NewCanves);}
    return true;

    }

}

function get_domain2($url){
	if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
	$pieces = parse_url($url);
	$domain = isset($pieces['host']) ? $pieces['host'] : '';
	if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
	  return $regs['domain'];
	}
	return false;
}

function get_domain_ip($domain){
	$data = gethostbynamel($domain);

	if($data==false){
		return false;
	}else {
		return implode(', ',$data);
	}
}

/**
 * Finds the Top Level Domain, this could be "com", "dk" etc.
 * For more information about TLD's see: http://en.wikipedia.org/wiki/Tld
 *
 * @param string $url the url to find the top level domain for.
 * @return string the top level domain found, else an empty string
 */
function get_tld($url) {
  $tld = "";
  
  $domain = get_domain($url);
  if($domain != "") {
    preg_match('/[^.]+$/', $domain, $matches);
    
    /*echo "tld: \n";
    print_r($matches);*/
    
    $tld = $matches[0];
  }
  
  return $tld;
}

/**
 * Finds the domain name of an URL.
 * For more information about domains see: http://en.wikipedia.org/wiki/Domain_name
 *
 * @param string $url the url to find the domain for.
 * @return string the domain found, else an empty string
 */
function get_domain($url) {
  $domain = "";
  
  $services = "(http:\/\/|https:\/\/|ftp:\/\/){0,1}";
  // pattern for IP-addresses
  $ipPattern = '/^'.$services.'(([0-9]{1,3}.){3}[0-9]{1,3}){1}/';
  // localhost
  $localhostPattern = '/^'.$services.'localhost{1}/';
  
  // tests that it is not localhost or an ip
  if(preg_match($localhostPattern, $url) == 0 && preg_match($ipPattern, $url) == 0) {
    //echo $url."\n";
    
    preg_match('@^(?:(http|https|ftp|sftp)://)?([^/]+)@i',$url, $matches);
    //print_r($matches);
    preg_match('/[^.]+\.[^.]+$/', $matches[2], $matches);
    $domain = $matches[0];
    //echo "domain: ".$domain."\n";
  }
  
  return $domain;
}


/**
 * Short description.
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function sendResetPassword($mail) {
	$db = get_conn();
	$sql = sprintf("SELECT `userid`, `name`, `mail`, `reset_timer` FROM `#@_user` WHERE `mail` = '%s' LIMIT 1",$mail);
	$rs = $db->query($sql);
	if($row = $db->fetch($rs)) {
		$acc_userid = $row['userid'];
		$acc_username = $row['name'];
		$acc_email = $row['mail'];

		$reset_timer = $row['reset_timer'];
		/*
		if((time() - $reset_timer) < 7200){
			$status['status'] = true;
			$status['message'] = '重置密码的时间间隔为1小时.';
			return $status;
			exit;
		}
		*/
		$resetcode = hash_hmac('sha1', $acc_username.uniqid(), HASHING_KEY);
		//更新用户
		user_edit($acc_userid, array('reset_key'=>$resetcode,'reset_timer'=>time()));

		//如果有邮箱则发送
		if($acc_email){
			require_once(COM_PATH.'/system/templates/emails/tmpl_confirm_pw_reset.php');
			$variables = array('website_name' => '信息管理系统',
							   'site_url' => C('System.home'),
							   'username' => $acc_username,
							   'email' => $acc_email,
							   'resetcode' => $resetcode,
							   'visitor_ip' => $_SERVER['REMOTE_ADDR']
							   );
			$subject	= render_email($variables, $email['title']);
			$body		= render_email($variables, $email['body']);
			//error_log($subject, 3, ABS_PATH.'/app.log');
			send_mail($acc_email, $subject, $body);

			ajax_success('邮件发送成功,请检查你的邮箱.');
		} 
	} else {
		ajax_error('此邮箱没有被注册使用!');
	}
    
} // end func


/**
 * Short description.
 * @param   type    $varname    description
 * @return  type    description
 * @access  public or private
 * @static  makes the class property accessible without needing an instantiation of the class
 */
function resetNewPassword()
{
    
} // end funcresetNewPassword

/**
 * 准备邮件内容格式
 */
function render_email(array $values = array(), $text){
	//run through the body and replace any keys with the data from the array
	foreach($values as $key => $txt){
		$text = preg_replace('#\{{'.$key.'}}#s', $txt, $text);
	}
		
	return $text;
}

/**
 * 发送邮件
 */
function send_mail($to, $subject, $body){
	$headers = 'MIME-Version: 1.0'."\r\n".
			  'Content-type: text/html; charset=utf8'."\r\n".
			  'From: info@shibangsfot.com'."\r\n".
			  'To: '.$to."\r\n".
			  'Subject: '.$subject;

	$mailtype	= C('.mailtype');
	$smtphost	= C('.smtphost');
	$smtpport	= C('.smtpport')?C('.smtpport'):25;
	$smtpuser	= C('.smtpuser');
	$smtppass	= C('.smtppass');

	if(strtolower($mailtype) == 'smtp' && !empty($smtphost) && !empty($smtpport) && !empty($smtpuser) && !empty($smtppass)) {
		require_once(COM_PATH.'/system/PHPMailer/class.phpmailer.php');
		include(COM_PATH.'/system/PHPMailer/class.smtp.php'); 
		$mail             = new PHPMailer(); //new一个PHPMailer对象出来
		$body             = preg_replace('/\\\\/','', $body); //对邮件内容进行必要的过滤
		$mail->CharSet ="UTF-8";//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
		$mail->IsSMTP(); // 设定使用SMTP服务
		$mail->SMTPDebug  = 1;                     // 启用SMTP调试功能
											   // 1 = errors and messages
											   // 2 = messages only
		$mail->SMTPAuth   = true;                  // 启用 SMTP 验证功能
		//$mail->SMTPSecure = "ssl";                 // 安全协议
		$mail->Host       = $smtphost;			   // SMTP 服务器 smtp.exmail.qq.com
		$mail->Port       = $smtpport;             // SMTP服务器的端口号
		$mail->Username   = $smtpuser;			   // SMTP服务器用户名 noreply@shibangcrusher.com
		$mail->Password   = $smtppass;             // SMTP服务器密码 info123456
		$mail->SetFrom($smtpuser, 'SBM信息管理系统');
		//$mail->AddReplyTo("noreply@shibangsoft.com","邮件回复人的名称");
		$mail->Subject    = $subject;
		//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer! - From www.shibangsfot.com"; // optional, comment out and test
		$mail->WordWrap   = 80;
		$mail->IsHTML(true); // send as HTML
		$mail->MsgHTML($body);
		$mail->AddAddress($to);
		//$mail->AddAttachment("images/phpmailer.gif");      // attachment 
		//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
		if(!$mail->Send()) {
			return false;
			//echo "Mailer Error: " . $mail->ErrorInfo;
		} else {
			return true;
			//echo "Message sent!恭喜，邮件发送成功！";
		}
	} else {
	    if(!@mail($to, $subject, $body, $headers)){
			return false;
		}else{
			return true;
		}
	}
}

/**
 * Parse an IRI into scheme/authority/path/query/fragment segments
 *
 * @access private
 * @param string $iri
 * @return array
 */
function parse_iri($iri)
{
	$iri = (string) $iri;
	preg_match('/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?$/', $iri, $match);
	for ($i = count($match); $i <= 9; $i++)
	{
		$match[$i] = '';
	}
	return array('scheme' => $match[2], 'authority' => $match[4], 'path' => $match[5], 'query' => $match[7], 'fragment' => $match[9]);
}

function whois_date_format ($format)
{
    $keys = explode('-', $format);
    $new_keys = array();
    $i = 0;
    while ($i < 3)
    {
        if ($keys[$i] == 1)
        {
            $new_keys['d'] = $keys[$i];
        } elseif ($keys[$i] == 2)
        {
            $new_keys['m'] = $keys[$i];
        } else
        {
            $new_keys['y'] = $keys[$i];
        }
        ++ $i;
    }
    return $new_keys;
}

/**
 * [询盘等级比较,A>B>C>D>E]
 * 
 * @return [string] [返回等级最高的那个]
 */
function rating_review($source='E',$target){
    if(strcmp($source,$target)>=0)
        return $target;
    else
        return $source;
}
/**
 * 姓名是否在联系人中
 * @param  [string]  $name [联系人姓名]
 * @return boolean
 */
function is_operational($name){
    if (empty($name)) { return true; }

    if(strpos($name,'，')!==false){
        $namestack = explode('，',$name);
    }else{
        $namestack = explode(',',$name);
    }

    $name_list = get_all_contact();
    foreach ($namestack as $key => $value) {
        if(!instr($value,$name_list))
            return false;
    }
    return true;
}

/**
 * 输出编辑器
 *
 * @param  $id
 * @param  $content
 * @param  $options see http://www.tinymce.com/
 * @return string
 */
function editor($id,$content,$options=null) {
	$path = ROOT.'common/tinymce/tinymce.min.js';

    $defaults = array(
        'width' => '700',
        'height'=> '280',
        'toobar' => 'full',
        'emotPath' => ROOT.'common/images/emots/',
        'editorRoot' => ROOT.'common/editor/',
        'loadCSS'    => ROOT.'common/css/xheditor.plugins.css',
    );
    
    $options = $options ? array_merge($defaults, $options) : $defaults;

    if (isset($options['tools'])) unset($options['toobar']);

    if (isset($options['toobar'])) {
        switch ($options['toobar']) {
            case 'full':
                $options['tools'] = 'Source,Preview,Pastetext,|,Blocktag,FontSize,Bold,Italic,Underline,Strikethrough,FontColor,'.
                                    'BackColor,Removeformat,|,Align,List,Outdent,Indent,|,Link,Unlink,Img,Flash,Flv,Emot,Table,GoogleMap,Pagebreak,Removelink,|,'.
                                    'Fullscreen';
                break;
            case 'simple':
                $options['tools'] = 'simple';
                break;
            case 'mini':
                $options['tools'] = 'mini';
                break;
        }
    }

    $ht = '<script type="text/javascript" src="'.ROOT.'common/tinymce/tinymce.min.js'.'"></script>';

    $ht.= '<script type="text/javascript">
tinymce.init({
    selector: "textarea",
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image"
});
</script>';
    $ht.= '<textarea class="text" id="'.$id.'" name="'.$id.'">'.esc_html($content).'</textarea>';
    //$ht.= '<script type="text/javascript">$(\'textarea[name='.$id.']\').xheditor($.extend('.json_encode($options).',{"plugins":xhePlugins,"beforeSetSource":xheFilter.SetSource,"beforeGetSource":xheFilter.GetSource}));</script>';
    return $ht;
}

?>