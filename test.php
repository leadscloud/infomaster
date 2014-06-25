<?php
/**
 * 测试页面
 */
// 文件名
$php_file = isset($php_file) ? $php_file : 'domain.php';
// 加载公共文件
//include dirname(__FILE__).'/admin/admin.php';
// 查询管理员信息
//$_USER = user_current();
function esc_js2($str) {
    if (function_exists('preg_replace_callback')) {
        $str = preg_replace_callback(
	        '/([^ :!#$%@()*+,-.\x30-\x5b\x5d-\x7e])/',
	        function ($matches) {
	        	return '\\x'.(ord($matches[1])<16? '0': '').dechex(ord($matches[1]));
	            //return strtolower($matches[0]);
	        },
	        $str
	    );
	    return $str;
    }
    return preg_replace('/([^ :!#$%@()*+,-.\x30-\x5b\x5d-\x7e])/e',
        "'\\x'.(ord('\\1')<16? '0': '').dechex(ord('\\1'))", $str);
}
function esc_js( $str ) {
	return preg_replace('/([^ :!#$%@()*+,-.\x30-\x5b\x5d-\x7e])/e',
        "'\\x'.(ord('\\1')<16? '0': '').dechex(ord('\\1'))", $str);
}

$str = "function(){中国的 [^ :!#$%@()*+,-. asdf}";
echo esc_js2($str);

echo esc_js($str);

//Start
//
//
/*
echo md5('admin'.'6A718A1B-7183-97C0-E3F0-83E12B0CF092');
echo '<br>';
$user =  user_login('admin','admin');
print_r($admin);

echo '<br>';
*/

//echo authcode(1);

/**
 * fb3f5ff1838e3c83f7f69653663dc27b
 * ee4ed9c2d70719ddb12442c7a91e26f5
 */
