<?php
defined('COM_PATH') or die('Restricted access!');
/** 
 * 基础配置文件。
 *
 * 这个文件用在于安装程序自动生成 config.php 配置文件，
 * 您可以手动复制这个文件，并重命名为 config.php，然后输入相关信息。
 *
 */

/*** *** 数据库设置 - 具体信息来自您正在使用的主机 *** ***/

/**
 * 数据库 DSN
 *
 * 如果您有在同一数据库内安装多个 系统 的需求，请为每个 系统 设置不同的数据表前缀。
 * 前缀名只能为数字、字母加下划线。
 *
 * @example mysql:host=localhost;name=test;prefix=im_;
 *
 * @var string host
 * @var string name
 * @var string prefix
 */
define('DB_DSN','database_dsn_here');
// 数据库用户名
define('DB_USER','username_here');
// 数据库密码
define('DB_PWD','password_here');
//HASHING_KEY
define('HASHING_KEY','hashing_key');