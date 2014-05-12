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
  `inforate` char(1) NOT NULL,
  `saleunit` varchar(20) NOT NULL,
  `salesubunit` varchar(20) NOT NULL,
  `operational` varchar(20) NOT NULL,
  `language` varchar(10) NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `landingurl` varchar(255) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `country` varchar(30) NOT NULL,
  `province` varchar(30) NOT NULL,
  `producttype` varchar(30) NOT NULL,
  `auction` enum('Yes','No') NOT NULL DEFAULT 'No',
  `sesource` char(10) NOT NULL,
  `description` varchar(255) NOT NULL,
  `remarks` longtext NOT NULL,
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
  `default_group` tinyint(1) NOT NULL DEFAULT '0',
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
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