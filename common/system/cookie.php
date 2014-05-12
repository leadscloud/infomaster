<?php
defined('COM_PATH') or die('Restricted access!');
// 定义域名
defined('COOKIE_DOMAIN') or define('COOKIE_DOMAIN','');
/**
 * Cookie 管理类
 *
 */
class Cookie {
    /**
     * 判断cookie是否存在
     *
     * @param string $name
     * @return bool
     */
    function is_set($name) {
        return isset($_COOKIE[$name]);
    }
    /**
     * 获取某个cookie值
     *
     * @param string $name
     * @return mixed
     */
    function get($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }
    /**
     * 设置某个cookie值
     *
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     */
    function set($name,$value,$expire=0,$path='/',$domain='') {
        if (empty($domain)) $domain = COOKIE_DOMAIN;
        if ($expire) $expire = time() + $expire;
        setcookie($name,$value,$expire,$path,$domain); 
    }
    /**
     * 删除某个cookie值
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     */
    function delete($name,$path='/',$domain='') {
        if(empty($domain)) { $domain = COOKIE_DOMAIN; }
        $this->set($name,'',time()-3600,$path,$domain);
    }
}
/**
 * 实例化对象
 *
 * @return FCache
 */
function &_cookie_get_object() {
    static $cookie;
	if ( is_null($cookie) )
		$cookie = new Cookie();
	return $cookie;
}

function cookie_isset($name) {
    $cookie = _cookie_get_object();
    return $cookie->is_set($name);
}
function cookie_get($name) {
    $cookie = _cookie_get_object();
    return $cookie->get($name);
}
function cookie_set($name,$value,$expire=0,$path='/',$domain='') {
    $cookie = _cookie_get_object();
    return $cookie->set($name,$value,$expire,$path,$domain);
}
function cookie_delete($name,$path='/',$domain='') {
    $cookie = _cookie_get_object();
    return $cookie->delete($name,$path,$domain);
}