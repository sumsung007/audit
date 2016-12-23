# ************************************************************
# Sequel Pro SQL dump
# Version 4499
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.9)
# Database: audit_system
# Generation Time: 2016-12-23 08:54:01 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table apps
# ------------------------------------------------------------

DROP TABLE IF EXISTS `apps`;

CREATE TABLE `apps` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(16) DEFAULT '',
  `name` varchar(128) DEFAULT '',
  `name_en` varchar(128) DEFAULT '',
  `create_time` datetime DEFAULT '0000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Applications';



# Dump of table expense201701GMID
# ------------------------------------------------------------

DROP TABLE IF EXISTS `expense201701GMID`;

CREATE TABLE `expense201701GMID` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(32) DEFAULT '',
  `coin` int(11) DEFAULT '0',
  `type` varchar(16) DEFAULT '',
  `time` datetime DEFAULT '0000-01-01 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Expense Records';



# Dump of table summary
# ------------------------------------------------------------

DROP TABLE IF EXISTS `summary`;

CREATE TABLE `summary` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(16) DEFAULT '',
  `month` varchar(6) DEFAULT '',
  `increase` int(11) DEFAULT '0',
  `reduce` int(11) DEFAULT '0',
  `sum` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `app_month` (`app_id`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Summary Of The Expense';



# Dump of table transaction
# ------------------------------------------------------------

DROP TABLE IF EXISTS `transaction`;

CREATE TABLE `transaction` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` varchar(16) DEFAULT '',
  `user_id` varchar(32) DEFAULT '',
  `amount` double(10,2) DEFAULT '0.00',
  `currency` varchar(3) DEFAULT '',
  `amount_usd` double(10,2) DEFAULT '0.00',
  `gateway` varchar(32) DEFAULT '',
  `uuid` varchar(36) DEFAULT '',
  `ip` varchar(15) DEFAULT '',
  `time` datetime DEFAULT '0000-01-01 00:00:00',
  PRIMARY KEY (`id`),
  KEY `app_user_time` (`app_id`,`user_id`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bills';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
