<?php
/**
 * 测试页面
 */
// 文件名
$php_file = isset($php_file) ? $php_file : 'domain.php';
// 加载公共文件
include dirname(__FILE__).'/admin.php';
// 查询管理员信息
$_USER = user_current();

$domain = '192.168.1.1';
$url = '192.168.1.1';
echo preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\z/',$url);
echo '------<br>';

$url    = new parseURL($domain);

print_r($url);

// echo $url->host;
$domain = $url->getRegisterableDomain();

echo $domain;
