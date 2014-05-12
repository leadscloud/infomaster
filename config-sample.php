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
 * @example mysql:host=localhost;name=test;prefix=info_;
 *
 * @var string host
 * @var string name
 * @var string prefix
 */
define('DB_DSN','mysql:host=localhost;name=infomaster;prefix=wp_;');
// 数据库用户名
define('DB_USER','root');
// 数据库密码
define('DB_PWD','g2R9F6Qlci');