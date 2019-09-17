# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.40)
# Database: kamazon
# Generation Time: 2019-09-09 04:41:23 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;



# Dump of table permissions
# ------------------------------------------------------------

CREATE TABLE `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;

INSERT INTO `permissions` (`id`, `name`, `group`, `display_name`, `description`, `created_at`, `updated_at`)
VALUES
	(1,'customer.browse','Customer','Browse',NULL,'2018-01-14 06:05:40','2018-01-14 06:05:40'),
	(2,'customer.edit','Customer','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(3,'customer.add','Customer','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(4,'customer.delete','Customer','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(6,'branch.browse','Branch','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(7,'branch.add','Branch','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(8,'branch.edit','Branch','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(9,'branch.delete','Branch','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(11,'staff.browse','Staff','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(12,'staff.add','Staff','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(13,'staff.edit','Staff','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(14,'staff.delete','Staff','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(16,'position.browse','Position','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(17,'position.add','Position','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(18,'position.edit','Position','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(19,'position.delete','Position','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(21,'product.browse','Product','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(22,'product.add','Product','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(23,'product.edit','Product','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(24,'product.delete','Product','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(26,'product-type.browse','Product Type','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(27,'product-type.add','Product Type','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(28,'product-type.edit','Product Type','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(29,'product-type.delete','Product Type','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(31,'brand.browse','Brand','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(32,'brand.add','Brand','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(33,'brand.edit','Brand','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(34,'brand.delete','Brand','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(36,'stock.transfer','Stock','Transfer',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(37,'stock.adjust','Stock','Adjust',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(38,'po.edit','Purchase Order','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(39,'po.delete','Purchase Order','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(40,'po.add','Purchase Order','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(41,'po.browse','Purchase Order','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(42,'sale.add','Sale','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(43,'sale.edit','Sale','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(44,'sale.delete','Sale','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(45,'sale.browse','Sale','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(46,'loan.browse','Loan','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(47,'loan.add','Loan','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(48,'loan.commission','Loan','Pay Commissions',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(49,'loan.delete','Loan','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(50,'loan.print','Loan','Print Contract',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(51,'loan.pay','Loan','Repay',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(52,'app.setting','App','Setting',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(53,'report.loan-approval','Report','Loan approval',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(54,'report.loan-expired','Report','Expired loans',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(55,'report.loan','Report','Loan',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(56,'report.financial','Report','Financials',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(57,'report.payment','Report','Loan Repayment',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(58,'report.customer','Report','Customer',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(59,'report.agent','Report','Agent',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(60,'report.commission-pay','Report','Commission payment',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(61,'staff.commission','Staff','Commission ',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(71,'user.browse','User','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(72,'user.add','User','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(73,'user.edit','User','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(74,'user.delete','User','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(75,'role.browse','User Role','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(76,'role.add','User Role','Add',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(77,'role.edit','User Role','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(78,'role.delete','User Role','Delete',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(125,'dashboard','Misc.','View Dashboard',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(126,'stock.transfer.browse','Stock','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(127,'stock.adjust.browse','Stock','Browse',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00'),
	(128,'loan.edit','Loan','Edit',NULL,'2018-06-09 17:00:00','2018-06-09 17:00:00');

/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table roles
# ------------------------------------------------------------

CREATE TABLE `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `display_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;

INSERT INTO `roles` (`id`, `user_id`, `name`, `display_name`, `description`, `created_at`, `updated_at`)
VALUES
	(1,2,'admin','admin',NULL,'2019-09-07 00:40:35','2019-09-08 12:03:26'),
	(3,2,'staff','staff',NULL,'2019-09-07 11:14:40','2019-09-08 13:53:57');

/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table role_user
# ------------------------------------------------------------

CREATE TABLE `role_user` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_user_role_id_foreign` (`role_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;

-- INSERT INTO `role_user` (`user_id`, `role_id`)
-- VALUES
-- 	(7,1),
-- 	(2,3),
-- 	(3,3),
-- 	(4,3),
-- 	(5,3);

/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

# Dump of table permission_role
# ------------------------------------------------------------

CREATE TABLE `permission_role` (
  `permission_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;

INSERT INTO `permission_role` (`permission_id`, `role_id`)
VALUES
	(1,1),
	(2,1),
	(3,1),
	(4,1),
	(6,1),
	(7,1),
	(8,1),
	(9,1),
	(11,1),
	(12,1),
	(13,1),
	(14,1),
	(16,1),
	(17,1),
	(18,1),
	(19,1),
	(21,1),
	(22,1),
	(23,1),
	(24,1),
	(26,1),
	(27,1),
	(28,1),
	(29,1),
	(31,1),
	(32,1),
	(33,1),
	(34,1),
	(36,1),
	(37,1),
	(38,1),
	(39,1),
	(40,1),
	(41,1),
	(42,1),
	(43,1),
	(44,1),
	(45,1),
	(46,1),
	(47,1),
	(48,1),
	(49,1),
	(50,1),
	(51,1),
	(52,1),
	(53,1),
	(54,1),
	(55,1),
	(56,1),
	(57,1),
	(58,1),
	(59,1),
	(60,1),
	(61,1),
	(71,1),
	(72,1),
	(73,1),
	(74,1),
	(75,1),
	(76,1),
	(77,1),
	(78,1),
	(125,1),
	(126,1),
	(127,1),
	(128,1),
	(21,3),
	(22,3),
	(23,3),
	(26,3),
	(27,3),
	(28,3),
	(31,3),
	(32,3),
	(33,3),
	(42,3),
	(43,3),
	(45,3),
	(47,3),
	(128,3);

/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
