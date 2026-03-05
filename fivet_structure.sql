-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: fivet
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.24.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `procedure_config_fields`
--

DROP TABLE IF EXISTS `procedure_config_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procedure_config_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `procedure_worksheet_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_type` enum('input','datetime','date','number','checkbox','textarea') COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` int NOT NULL DEFAULT '0',
  `help_text` text COLLATE utf8mb4_unicode_ci,
  `model_tied_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `field_value_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procedure_config_fields_procedure_worksheet_id_foreign` (`procedure_worksheet_id`),
  CONSTRAINT `procedure_config_fields_procedure_worksheet_id_foreign` FOREIGN KEY (`procedure_worksheet_id`) REFERENCES `procedure_worksheets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `captured_procedure_config_values`
--

DROP TABLE IF EXISTS `captured_procedure_config_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `captured_procedure_config_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `captured_result_id` bigint unsigned NOT NULL,
  `procedure_worksheet_id` bigint unsigned NOT NULL,
  `procedure_config_field_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_test_kit_columns`
--

DROP TABLE IF EXISTS `procedure_test_kit_columns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procedure_test_kit_columns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `procedure_worksheet_id` bigint unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('string','number','date','boolean') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `order` int NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `help_text` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procedure_test_kit_columns_procedure_worksheet_id_foreign` (`procedure_worksheet_id`),
  CONSTRAINT `procedure_test_kit_columns_procedure_worksheet_id_foreign` FOREIGN KEY (`procedure_worksheet_id`) REFERENCES `procedure_worksheets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_test_kit_rows`
--

DROP TABLE IF EXISTS `procedure_test_kit_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procedure_test_kit_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `procedure_worksheet_id` bigint unsigned NOT NULL,
  `row_index` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procedure_test_kit_rows_procedure_worksheet_id_foreign` (`procedure_worksheet_id`),
  CONSTRAINT `procedure_test_kit_rows_procedure_worksheet_id_foreign` FOREIGN KEY (`procedure_worksheet_id`) REFERENCES `procedure_worksheets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `procedure_test_kit_values`
--

DROP TABLE IF EXISTS `procedure_test_kit_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `procedure_test_kit_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `procedure_test_kit_row_id` bigint unsigned NOT NULL,
  `procedure_test_kit_column_id` bigint unsigned NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `procedure_test_kit_values_procedure_test_kit_row_id_foreign` (`procedure_test_kit_row_id`),
  KEY `procedure_test_kit_values_procedure_test_kit_column_id_foreign` (`procedure_test_kit_column_id`),
  CONSTRAINT `procedure_test_kit_values_procedure_test_kit_column_id_foreign` FOREIGN KEY (`procedure_test_kit_column_id`) REFERENCES `procedure_test_kit_columns` (`id`) ON DELETE CASCADE,
  CONSTRAINT `procedure_test_kit_values_procedure_test_kit_row_id_foreign` FOREIGN KEY (`procedure_test_kit_row_id`) REFERENCES `procedure_test_kit_rows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-05 13:38:54
