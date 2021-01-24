CREATE DATABASE  IF NOT EXISTS `church_svc_assist`;
USE `church_svc_assist`;

CREATE TABLE `church` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tstamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `passwd` tinytext NOT NULL,
  `email` tinytext,
  `tel` tinytext,
  `invitedemail` tinytext NOT NULL,
  `maxseats` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `proposal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `church` bigint unsigned NOT NULL,
  `tstamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `socialsec` tinytext,
  `email` tinytext,
  `tel` tinytext,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `church_ibfk_id` (`church`),
  CONSTRAINT `usr_ibfk_id` FOREIGN KEY (`church`) REFERENCES `church` (`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `member` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `church` bigint unsigned NOT NULL,
  `tstamp` datetime DEFAULT CURRENT_TIMESTAMP,
  `socialsec` tinytext,
  `email` tinytext,
  `tel` tinytext,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `passwd` tinytext,
  `attrib` bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `church_ibfk_id` (`church`),
  CONSTRAINT `church_ibfk_id` FOREIGN KEY (`church`) REFERENCES `church` (`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `recover_passwd` (
  `member` bigint unsigned NOT NULL,
  `tstamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`member`),
  KEY `member_ibfk_id` (`member`),
  CONSTRAINT `member_ibfk_id` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

CREATE TABLE `assist_days` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `service` int NOT NULL,
  `servicedate` datetime NOT NULL,
  `member` bigint unsigned NOT NULL,
  `tstamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `member_ibfk_id` (`member`),
  CONSTRAINT `member_ibfk_id` FOREIGN KEY (`member`) REFERENCES `member` (`id`) ON DELETE CASCADE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
