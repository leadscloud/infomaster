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

//Start

echo authcode(31);

echo '<br>';

echo authcode(1);
