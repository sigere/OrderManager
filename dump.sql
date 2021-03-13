-- MySQL dump 10.18  Distrib 10.3.27-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: 127.0.0.1    Database: order_manager_dev
-- ------------------------------------------------------
-- Server version	10.4.14-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

USE `order_manager_dev`;
--
-- Table structure for table `client`
--

DROP TABLE IF EXISTS `client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alias` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `nip` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `post_code` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client`
--

LOCK TABLES `client` WRITE;
/*!40000 ALTER TABLE `client` DISABLE KEYS */;
INSERT INTO `client` VALUES (1,'W11 wydział kryminalny','W11','123-456-78-90','37-500','Jarosław','ul. Poniatwoskiego 997','PL','2021-02-05 12:34:56',NULL),(2,'K&P','k&p','321-654-87-09','21-377','Yarolsav','Peukinska 2137','PL','2021-02-05 12:44:46',NULL),(3,'Firma przewozowa szybki-strzał','SS','321-654-87-09','99-377','Dąbrowice','Wietnamska 3998','RU','2021-02-05 12:47:31',NULL),(4,'Herbaciarnia Miętka','HM','321-654-87-09','99-223','Wrocław','Północna 3','PL','2021-02-05 12:47:31',NULL);
/*!40000 ALTER TABLE `client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20210119125756','2021-02-04 13:44:13',287),('DoctrineMigrations\\Version20210119174922','2021-02-04 13:44:14',20),('DoctrineMigrations\\Version20210312123347','2021-03-12 13:34:45',786);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lang`
--

DROP TABLE IF EXISTS `lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `short` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lang`
--

LOCK TABLES `lang` WRITE;
/*!40000 ALTER TABLE `lang` DISABLE KEYS */;
INSERT INTO `lang` VALUES (1,'Polski','PL'),(2,'Rosyjski','RU'),(3,'Ukraiński','UA'),(4,'Angielski','EN'),(5,'Białoruski','BY');
/*!40000 ALTER TABLE `lang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `action` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8F3F68C5A76ED395` (`user_id`),
  KEY `IDX_8F3F68C58D9F6D38` (`order_id`),
  CONSTRAINT `FK_8F3F68C58D9F6D38` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`),
  CONSTRAINT `FK_8F3F68C5A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `log`
--

LOCK TABLES `log` WRITE;
/*!40000 ALTER TABLE `log` DISABLE KEYS */;
INSERT INTO `log` VALUES (1,1,1,'2021-02-06 14:35:57','Dodano zlecenie'),(2,1,1,'2021-02-06 14:36:24','Custom log'),(3,1,1,'2021-02-06 14:36:35','Another random stuff'),(4,1,2,'2021-02-06 14:36:50','Dodano zlecenie'),(5,1,2,'2021-02-06 14:36:57','Zmieniono zlecenie'),(6,1,3,'2021-02-06 14:37:17','Ptaki latają kluczem'),(7,1,3,'2021-02-06 14:37:28','Klucze latają ptakiem'),(8,1,4,'2021-02-06 14:37:35','Klucze latają ptakiem'),(9,1,5,'2021-02-06 14:38:14','crazy shit just happend'),(10,1,2,'2021-02-06 14:39:41','crazy shit just happend'),(11,1,3,'2021-02-06 14:39:45','crazy shit just happend'),(12,1,1,'2021-02-06 14:39:49','crazy shit just happend'),(13,1,4,'2021-02-06 20:15:10','Zmiana statusu: wykonane -> wyslane.'),(14,1,4,'2021-02-06 20:15:18','Zmiana statusu: wyslane -> wykonane.'),(15,1,9,'2021-02-06 20:16:48','Zmiana statusu: wyslane -> wykonane.'),(16,1,4,'2021-02-06 20:17:14','Zmiana statusu: wykonane -> przyjete.'),(17,1,4,'2021-02-06 20:23:15','Zmiana statusu: przyjete -> przyjete.'),(18,1,4,'2021-02-06 20:23:36','Zmiana statusu: przyjete -> przyjete.'),(19,1,4,'2021-02-06 20:24:50','Zmiana statusu: przyjete -> wykonane.'),(20,1,9,'2021-02-06 20:31:31','Zmiana statusu: wykonane -> wyslane.'),(21,1,4,'2021-02-06 20:34:57','Zmiana statusu: wykonane -> przyjete.'),(22,1,4,'2021-02-06 20:59:28','Zmiana statusu: przyjete -> wykonane.'),(23,1,2,'2021-02-06 20:59:35','Zmiana statusu: wykonane -> przyjete.'),(24,1,4,'2021-02-06 21:01:48','Zmiana statusu: wykonane -> wyslane.'),(25,1,4,'2021-02-06 21:02:23','Zmiana statusu: wyslane -> wykonane.'),(26,1,4,'2021-02-06 21:02:58','Zmiana statusu: wykonane -> przyjete.'),(27,1,4,'2021-02-06 21:05:11','Zmiana statusu: przyjete -> wykonane.'),(28,1,3,'2021-02-07 11:30:46','Usunięto zlecenie'),(29,1,3,'2021-02-07 11:31:46','Usunięto zlecenie'),(30,1,3,'2021-02-07 11:34:04','Usunięto zlecenie'),(31,1,2,'2021-02-07 11:34:14','Usunięto zlecenie'),(32,1,5,'2021-02-07 11:34:26','Usunięto zlecenie'),(33,1,6,'2021-02-07 15:53:18','Usunięto zlecenie'),(34,1,7,'2021-02-07 15:55:11','Zmiana statusu: wykonane -> przyjete.'),(35,1,7,'2021-02-07 15:55:22','Zmiana statusu: przyjete -> wykonane.'),(36,1,7,'2021-02-07 15:56:38','Zmiana statusu: wykonane -> przyjete.'),(37,1,7,'2021-02-07 15:57:57','Zmiana statusu: przyjete -> wykonane.'),(38,1,9,'2021-02-07 16:03:51','Zmiana statusu: wyslane -> przyjete.'),(39,1,7,'2021-02-07 16:05:35','Zmiana statusu: wykonane -> przyjete.'),(40,1,1,'2021-02-07 16:06:12','Zmiana statusu: wyslane -> przyjete.'),(41,1,9,'2021-02-07 16:06:26','Zmiana statusu: przyjete -> wykonane.'),(42,1,9,'2021-02-07 16:07:36','Zmiana statusu: wykonane -> przyjete.'),(43,1,9,'2021-02-07 16:08:02','Zmiana statusu: przyjete -> wykonane.'),(44,1,9,'2021-02-07 16:12:22','Zmiana statusu: wykonane -> przyjete.'),(45,1,9,'2021-02-07 16:13:26','Zmiana statusu: przyjete -> wykonane.'),(46,1,9,'2021-02-07 16:13:54','Zmiana statusu: wykonane -> przyjete.'),(47,1,4,'2021-02-07 16:14:50','Zmiana statusu: wykonane -> przyjete.'),(48,1,9,'2021-02-07 16:15:03','Zmiana statusu: przyjete -> wykonane.'),(49,1,1,'2021-02-07 16:15:05','Zmiana statusu: przyjete -> wykonane.'),(50,1,9,'2021-02-07 16:15:12','Zmiana statusu: wykonane -> przyjete.'),(51,1,1,'2021-02-07 16:15:43','Zmiana statusu: wykonane -> przyjete.'),(52,1,1,'2021-02-07 16:15:45','Zmiana statusu: przyjete -> wykonane.'),(53,1,4,'2021-02-07 16:17:02','Zmiana statusu: przyjete -> wykonane.'),(54,1,4,'2021-02-07 16:20:09','Zmiana statusu: wykonane -> przyjete.'),(55,1,4,'2021-02-07 16:28:08','Zmiana statusu: przyjete -> wykonane.'),(56,1,4,'2021-02-07 16:30:33','Zmiana statusu: wykonane -> przyjete.'),(57,1,1,'2021-02-07 16:33:14','Zmiana statusu: wykonane -> przyjete.'),(58,1,4,'2021-02-07 16:33:36','Zmiana statusu: przyjete -> wykonane.'),(59,1,4,'2021-02-07 16:35:18','Zmiana statusu: wykonane -> przyjete.'),(60,1,4,'2021-02-07 16:46:16','Zmiana statusu: przyjete -> wykonane.'),(61,1,4,'2021-02-07 16:48:53','Zmiana statusu: wykonane -> przyjete.'),(62,1,1,'2021-02-07 16:48:57','Zmiana statusu: przyjete -> wyslane.'),(63,1,7,'2021-02-07 16:48:59','Zmiana statusu: przyjete -> wykonane.'),(64,1,4,'2021-02-07 16:49:07','Zmiana statusu: przyjete -> wykonane.'),(65,1,9,'2021-02-07 16:49:09','Zmiana statusu: przyjete -> wykonane.'),(66,1,4,'2021-02-07 16:50:09','Zmiana statusu: wykonane -> przyjete.'),(67,1,4,'2021-02-07 16:50:13','Zmiana statusu: przyjete -> wykonane.'),(68,1,4,'2021-02-07 16:50:17','Zmiana statusu: wykonane -> wyslane.'),(69,1,4,'2021-02-07 16:55:01','Zmiana statusu: wyslane -> przyjete.'),(70,1,1,'2021-02-07 16:57:17','Zmiana statusu: wyslane -> przyjete.'),(71,1,1,'2021-02-07 16:57:45','Zmiana statusu: przyjete -> wykonane.'),(72,1,4,'2021-02-07 16:57:47','Zmiana statusu: przyjete -> wyslane.'),(73,1,10,'2021-02-07 18:04:05','Dodano zlecenie'),(74,1,11,'2021-02-07 18:06:55','Dodano zlecenie'),(75,1,12,'2021-02-07 18:08:05','Dodano zlecenie'),(76,1,12,'2021-02-07 18:08:23','Zmiana statusu: przyjete -> wykonane.'),(77,1,12,'2021-02-07 18:09:46','Usunięto zlecenie'),(78,1,4,'2021-02-13 23:15:08','Zmiana statusu: wyslane -> przyjete.'),(79,1,4,'2021-02-13 23:16:15','Zmiana statusu: przyjete -> wyslane.'),(80,1,13,'2021-03-02 20:29:44','Dodano zlecenie'),(81,1,13,'2021-03-11 16:59:28','Zmiana statusu: przyjete -> wykonane.'),(82,1,12,'2021-03-12 15:58:13','Usunięto zlecenie');
/*!40000 ALTER TABLE `log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order`
--

DROP TABLE IF EXISTS `order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `base_lang_id` int(11) NOT NULL,
  `target_lang_id` int(11) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `certified` tinyint(1) NOT NULL,
  `pages` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `topic` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `info` longtext COLLATE utf8_unicode_ci NOT NULL,
  `adoption` datetime NOT NULL,
  `deadline` datetime NOT NULL,
  `settled_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_F529939819EB6921` (`client_id`),
  KEY `IDX_F5299398F675F31B` (`author_id`),
  KEY `IDX_F5299398D4D57CD` (`staff_id`),
  KEY `IDX_F52993983F2786C0` (`base_lang_id`),
  KEY `IDX_F5299398C04986CF` (`target_lang_id`),
  CONSTRAINT `FK_F529939819EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `FK_F52993983F2786C0` FOREIGN KEY (`base_lang_id`) REFERENCES `lang` (`id`),
  CONSTRAINT `FK_F5299398C04986CF` FOREIGN KEY (`target_lang_id`) REFERENCES `lang` (`id`),
  CONSTRAINT `FK_F5299398D4D57CD` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`),
  CONSTRAINT `FK_F5299398F675F31B` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order`
--

LOCK TABLES `order` WRITE;
/*!40000 ALTER TABLE `order` DISABLE KEYS */;
INSERT INTO `order` VALUES (1,1,1,1,1,2,NULL,1,2.00,30.00,'Ważne zlecenie','wykonane','','2021-02-05 12:59:38','2021-02-14 12:12:00',NULL),(2,2,1,1,2,3,'2021-02-07 11:34:14',0,1.00,50.00,'Dowodzik','przyjete','','2021-02-05 12:59:38','2021-02-15 15:15:00',NULL),(3,3,1,1,4,3,'2021-02-07 11:34:04',1,2.00,60.00,'Mało ważne zlecenie','przyjete','','2021-02-05 15:15:28','2021-02-28 10:22:00',NULL),(4,4,1,1,5,1,NULL,0,2.00,20.00,'Paszport polsatu','wyslane','','2021-02-05 15:15:28','2021-01-12 12:12:00','2021-03-12 20:50:05'),(5,2,1,1,1,2,'2021-02-07 11:34:26',1,2.00,50.00,'Lorem worem ipsum śripsum','przyjete','uwaga na ortografię','2021-02-05 15:15:28','2021-03-01 12:12:00',NULL),(6,2,1,1,2,3,'2021-02-07 15:53:18',1,10.00,10.00,'Jak wytresować Piotrka','wyslane','','2021-02-05 15:44:20','2021-02-28 10:22:00',NULL),(7,2,1,1,2,1,NULL,0,5.00,24.00,'Na górze róże na dole wacki','wykonane','Juliusz Słowacki','2021-02-05 15:44:20','2021-02-28 10:22:00',NULL),(8,2,1,1,4,5,NULL,0,3.00,35.50,'Mało ważne zlecenie','wyslane','','2021-02-05 15:44:20','2021-02-28 10:22:00',NULL),(9,2,1,1,1,5,NULL,1,0.00,0.00,'Przeterminowane','wykonane','qwerty','2021-02-06 17:56:09','2021-02-01 17:00:00',NULL),(10,1,1,1,1,1,NULL,1,0.00,0.00,'Jazada','przyjete','','2021-02-07 00:00:00','2021-02-07 23:59:00',NULL),(11,1,1,2,2,4,NULL,0,10.00,0.00,'Emigrowałem','przyjete','Z RAMION TWYCH NAD RAAAANEM!','2021-03-07 00:00:00','2021-05-07 22:59:00',NULL),(12,1,1,1,2,3,'2021-03-12 15:58:13',0,0.00,35.00,'EMIGROWAŁEM','wykonane','Z RAMION TWYCH NAD RANEEEEEM!!!','2021-04-07 00:00:00','2021-01-07 23:52:00',NULL),(13,2,1,4,1,3,NULL,1,2.00,0.00,'$$$$','wykonane','','2021-03-02 00:00:00','2021-03-08 21:59:00',NULL);
/*!40000 ALTER TABLE `order` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Jan','Borówa','2021-02-04 13:57:10',NULL),(2,'Grzegorz','Parówa','2021-02-05 12:54:09',NULL),(3,'Dominika','Pączek','2021-02-05 12:54:09',NULL),(4,'Wacław','Frydrych','2021-02-05 12:54:09',NULL);
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) DEFAULT NULL,
  `username` varchar(180) COLLATE utf8_unicode_ci NOT NULL,
  `roles` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `preferences` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `created_at` datetime NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`),
  KEY `IDX_8D93D649D4D57CD` (`staff_id`),
  CONSTRAINT `FK_8D93D649D4D57CD` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,1,'siger','[\"ROLE_USER\",\"ROLE_ADMIN\"]','$argon2id$v=19$m=65536,t=4,p=1$DEBMyabL8aZm0VFW0FW4FQ$Q1r40OA972tM/KhcANFMI5FY2UtBzWsHVJayDDyzq7I','siger','siger','{\"index\":{\"przyjete\":true,\"wykonane\":true,\"wyslane\":true,\"adoption\":false,\"client\":true,\"topic\":true,\"lang\":false,\"deadline\":true,\"staff\":1,\"select-client\":null,\"date-type\":\"deadline\",\"date-from\":null,\"date-to\":{\"date\":\"2021-03-20 00:00:00.000000\",\"timezone_type\":3,\"timezone\":\"Europe\\/Berlin\"}},\"archives\":{\"usuniete\":true,\"adoption\":false,\"client\":true,\"topic\":true,\"lang\":false,\"deadline\":true,\"staff\":null,\"select-client\":null,\"date-type\":\"adoption\",\"date-from\":{\"date\":\"2021-03-13 00:00:00.000000\",\"timezone_type\":3,\"timezone\":\"Europe\\/Berlin\"},\"date-to\":null}}','2021-03-13 14:04:07',NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-03-13 16:15:00
