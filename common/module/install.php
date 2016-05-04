<?php
defined('COM_PATH') or die('Restricted access!');

/**
 * 检测系统是否正确安装
 *
 * @return bool
 */
function installed() {
    $result = false;
    // 能取到安装日期
    if (C('Installed')) {
        $db = get_conn();
        // 数据库链接不正确
        if (!$db) return $result;
        $tables = array(
            'option','rule','user','user_meta','user_groups',
            'post','post_meta',
            'term','term_relation','term_taxonomy','term_taxonomy_meta',
        );
        $table_ok = true;
        // 检查数据表是否正确
        foreach($tables as $table) {
            if (false === $db->is_table('#@_'.$table)) {
                $table_ok = false;
            }
        }
        $result = $table_ok;
    }
    return $result;
}

/**
 * 安装默认设置
 *
 * @return bool
 */
function install_defaults($initial) {
  $guessurl = guess_url();
    // 默认设置
    $options = array(
        // 2.0
        'home'        => $guessurl,
        'Installed'   => W3cDate(),
        'Language'    => 'zh-CN',
        'Timezone'    => 'Asia/Shanghai',
        'gmt_offset'  => '8'
    );
    // 覆盖或升级设置
    foreach($options as $k=>$v) {
        if (C($k)===null) {
            C($k,$v);
        }
    }
    // 安装初始化数据
    if ($initial) {
        $db = get_conn();
    $date = time();
    //创建分类
    }
    return true;
}

/**
 * 表结构
 *
 * @return string
 * inforate 从char(1) 修改为char(2)，因为增加了C+等级
 */
function install_schema() {
    return <<<SQL
CREATE TABLE IF NOT EXISTS `#@_option` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `module` char(20) NOT NULL,
  `code` char(50) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opt_idx` (`code`,`module`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_post` (
  `postid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sortid` int(10) unsigned NOT NULL DEFAULT '0',
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `grpid` int(10) unsigned NOT NULL DEFAULT '0',
  `otheruser` char(255) NOT NULL,
  `type` char(20) NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `date_gmt` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified_gmt` datetime NOT NULL default '0000-00-00 00:00:00',
  `addtime` int(10) unsigned NOT NULL DEFAULT '0',
  `datetime` int(10) unsigned NOT NULL DEFAULT '0',
  `edittime` int(10) unsigned NOT NULL DEFAULT '0',
  `model` char(75) NOT NULL,
  `author` varchar(255) NOT NULL,
  `serial` int(10) NOT NULL DEFAULT '0',
  `identifier` varchar(60) NOT NULL,
  `source` char(255) NOT NULL,
  `department` varchar(20) NOT NULL,
  `infoclass` varchar(20) NOT NULL,
  `infomember` varchar(20) NOT NULL,
  `inforate` char(2) NOT NULL,  
  `saleunit` varchar(20) NOT NULL,
  `salesubunit` varchar(20) NOT NULL,
  `operational` varchar(255) NOT NULL,
  `language` varchar(10) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `landingurl` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `country` varchar(30) NOT NULL,
  `continent` varchar(20) NOT NULL,
  `province` varchar(30) NOT NULL,
  `producttype` varchar(30) NOT NULL,
  `auction` varchar(20) NOT NULL,
  `sesource` char(10) NOT NULL,
  `description` longtext NOT NULL,
  `remarks` longtext NOT NULL,
  `chatlog` longtext NOT NULL,
  `belong`  varchar(50) NOT NULL,
  `xp_status`  varchar(10) NOT NULL,
  `comments` enum('Yes','No') NOT NULL DEFAULT 'No',
  PRIMARY KEY (`postid`),
  KEY `grpid` (`grpid`),
  KEY `author` (`author`),
  KEY `userid` (`userid`,`otheruser`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#@_post_meta` (
  `metaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `postid` bigint(20) unsigned NOT NULL,
  `key` char(50) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`metaid`),
  KEY `postid` (`postid`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_user` (
  `userid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usergroup` tinyint(4) NOT NULL DEFAULT '1',
  `other_usergroups` char(255) NOT NULL,
  `name` char(20) NOT NULL,
  `pass` char(32) NOT NULL,
  `mail` char(150) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `registered` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `authcode` char(36) NOT NULL,
  `reset_key` char(40) NOT NULL,
  `reset_timer` int(11) NOT NULL,
  PRIMARY KEY (`userid`),
  UNIQUE KEY `name` (`name`),
  KEY `authcode` (`authcode`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#@_user_meta` (
  `metaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL DEFAULT '0',
  `key` char(50) NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`metaid`),
  KEY `userid` (`userid`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS `#@_user_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL,
  `colour` char(6) NOT NULL,
  `code` char(6) NOT NULL,
  `default_group` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text NOT NULL,
  `category` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `#@_user_groups` (`id`, `name`, `colour`, `code`, `permissions`) VALUES
(1, 'SEO技术人员', '284fe2', 'SEO', 'rule-list,rule-new,rule-edit');

CREATE TABLE IF NOT EXISTS `#@_model` (
  `modelid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` char(20) NOT NULL,
  `code` char(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `fields` longtext NOT NULL,
  `state` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  PRIMARY KEY (`modelid`),
  UNIQUE KEY `code` (`code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_term` (
  `termid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(35) NOT NULL,
  PRIMARY KEY (`termid`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_term_relation` (
  `objectid` bigint(20) unsigned NOT NULL,
  `taxonomyid` int(10) unsigned NOT NULL,
  `order` smallint(5) unsigned NOT NULL DEFAULT '0',
  KEY `taxonomyid` (`taxonomyid`),
  KEY `objectid` (`objectid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_term_taxonomy` (
  `taxonomyid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `termid` bigint(20) unsigned NOT NULL,
  `type` char(20) NOT NULL DEFAULT 'category',
  `parent` int(10) unsigned NOT NULL DEFAULT '0',
  `count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`taxonomyid`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_term_taxonomy_meta` (
  `metaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `taxonomyid` int(10) unsigned NOT NULL,
  `key` char(50) NOT NULL,
  `value` longtext,
  PRIMARY KEY (`metaid`),
  KEY `taxonomyid` (`taxonomyid`),
  KEY `key` (`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_rule` (
  `ruleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `pattern` longtext NOT NULL,
  `type` char(20) NOT NULL,
  `result` varchar(50) NOT NULL,
  `domain` mediumtext,
  `subdomain` tinyint(1) NOT NULL DEFAULT '0',
  `content` longtext,
  `state` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edittime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ruleid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group` tinyint(4) NOT NULL DEFAULT '1',
  `category` varchar(255) NOT NULL,
  `name` char(20) NOT NULL,
  `mobile` char(32) NOT NULL,
  `email` char(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `birthday` char(10) NOT NULL,
  `url` varchar(50) NOT NULL,
  `note` text NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edittime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `datetime` datetime NOT NULL,
  `action` VARCHAR(256) NOT NULL,
  `objecttype` VARCHAR(256) NOT NULL,
  `objectsubtype` VARCHAR(256) NOT NULL,
  `userid` int(10) NOT NULL,
  `objectid` int(10) NOT NULL,
  `objectname` VARCHAR(256) NOT NULL,
  `description` mediumtext,
  PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARACTER SET=utf8;

CREATE TABLE IF NOT EXISTS `#@_issue` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author` varchar(255) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `ip` bigint(20) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `status` enum('open', 'closed') NOT NULL DEFAULT 'open',
  `agent` varchar(255) NOT NULL,
  `parent` bigint(20) unsigned NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_messages` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `to_user` int(12) NOT NULL,
  `from_user` int(12) NOT NULL,
  `subject` char(200) NOT NULL,
  `message` text NOT NULL,
  `date` int(30) NOT NULL,
  `status` enum('unread','read') NOT NULL DEFAULT 'unread',
  PRIMARY KEY (`id`),
  KEY `to_user` (`to_user`),
  KEY `from_user` (`from_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `#@_domain` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` tinyint(4) NOT NULL DEFAULT '0',
  `author` varchar(255) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `exusername` varchar(255) NOT NULL,
  `changetime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domain` varchar(255) NOT NULL UNIQUE,
  `registrationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `renewaldate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `expirationdate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `registrar` varchar(255) NOT NULL,
  `renewalurl` varchar(255) NOT NULL,
  `status` char(10) NOT NULL,
  `marker` varchar(100) NOT NULL,
  `charge` decimal(3,2) NOT NULL,
  `language` varchar(10) NOT NULL,
  `domaintype` varchar(10) NOT NULL,
  `whois` TEXT NOT NULL,
  `extloginurl` varchar(255) NOT NULL,
  `extloginname` varchar(100) NOT NULL,
  `extloginpass` varchar(100) NOT NULL,
  `addtime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `edittime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `description` TEXT NOT NULL,
  `userid` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#@_domain_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` char(30) NOT NULL,
  `default_group` tinyint(1) NOT NULL DEFAULT '0',
  `admin` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#@_domain_meta` (
  `metaid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `domainid` bigint(20) unsigned NOT NULL,
  `type` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `host` varchar(255) NOT NULL,
  `database` varchar(255) NOT NULL,
  `notes` TEXT NOT NULL,
   PRIMARY KEY (`metaid`),
  KEY `domainid` (`domainid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

SQL;
}
?>