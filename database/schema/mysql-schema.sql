/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bank_soal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bank_soal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_bank` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mapel_id` bigint unsigned NOT NULL,
  `tingkat` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_soal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pilihan_ganda',
  `total_soal` int NOT NULL DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `pengaturan` json DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bank_soal_kode_bank_unique` (`kode_bank`),
  KEY `bank_soal_created_by_foreign` (`created_by`),
  KEY `bank_soal_mapel_id_status_index` (`mapel_id`,`status`),
  KEY `bank_soal_status_tingkat_index` (`status`,`tingkat`),
  CONSTRAINT `bank_soal_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `bank_soal_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `berita_acara_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `berita_acara_ujian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sesi_ruangan_id` bigint unsigned DEFAULT NULL,
  `pengawas_id` bigint unsigned NOT NULL,
  `catatan_pembukaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_pelaksanaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `catatan_penutupan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `jumlah_peserta_terdaftar` int NOT NULL DEFAULT '0',
  `jumlah_peserta_hadir` int NOT NULL DEFAULT '0',
  `jumlah_peserta_tidak_hadir` int NOT NULL DEFAULT '0',
  `status_pelaksanaan` enum('selesai_normal','selesai_terganggu','dibatalkan') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'selesai_normal',
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `waktu_finalisasi` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `berita_acara_ujian_pengawas_id_foreign` (`pengawas_id`),
  KEY `berita_acara_ujian_sesi_ruangan_id_foreign` (`sesi_ruangan_id`),
  CONSTRAINT `berita_acara_ujian_pengawas_id_foreign` FOREIGN KEY (`pengawas_id`) REFERENCES `guru` (`id`),
  CONSTRAINT `berita_acara_ujian_sesi_ruangan_id_foreign` FOREIGN KEY (`sesi_ruangan_id`) REFERENCES `sesi_ruangan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `enrollment_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enrollment_ujian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sesi_ruangan_id` bigint unsigned DEFAULT NULL,
  `jadwal_ujian_id` bigint unsigned DEFAULT NULL,
  `siswa_id` bigint unsigned NOT NULL,
  `status_enrollment` enum('enrolled','active','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'enrolled',
  `waktu_mulai_ujian` timestamp NULL DEFAULT NULL,
  `waktu_selesai_ujian` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_logout_at` timestamp NULL DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enrollment_ujian_siswa_id_foreign` (`siswa_id`),
  KEY `enrollment_ujian_sesi_ruangan_id_foreign` (`sesi_ruangan_id`),
  KEY `enrollment_ujian_jadwal_ujian_id_foreign` (`jadwal_ujian_id`),
  CONSTRAINT `enrollment_ujian_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujian` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollment_ujian_sesi_ruangan_id_foreign` FOREIGN KEY (`sesi_ruangan_id`) REFERENCES `sesi_ruangan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollment_ujian_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `guru` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `nama` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guru_nip_unique` (`nip`),
  UNIQUE KEY `guru_email_unique` (`email`),
  KEY `guru_user_id_foreign` (`user_id`),
  CONSTRAINT `guru_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hasil_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hasil_ujian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enrollment_ujian_id` bigint unsigned NOT NULL,
  `siswa_id` bigint unsigned NOT NULL,
  `jadwal_ujian_id` bigint unsigned NOT NULL,
  `sesi_ruangan_id` bigint unsigned DEFAULT NULL,
  `waktu_mulai` timestamp NULL DEFAULT NULL,
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  `durasi_menit` int DEFAULT NULL,
  `jumlah_soal` int NOT NULL DEFAULT '0',
  `jumlah_dijawab` int NOT NULL DEFAULT '0',
  `jumlah_benar` int NOT NULL DEFAULT '0',
  `jumlah_salah` int NOT NULL DEFAULT '0',
  `jumlah_tidak_dijawab` int NOT NULL DEFAULT '0',
  `skor` int NOT NULL DEFAULT '0',
  `nilai` decimal(5,2) NOT NULL DEFAULT '0.00',
  `lulus` tinyint(1) NOT NULL DEFAULT '0',
  `is_final` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_mulai',
  `violations_count` int NOT NULL DEFAULT '0' COMMENT 'Count of detected violations during the exam',
  `jawaban` json DEFAULT NULL,
  `hasil_detail` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hasil_ujian_jadwal_ujian_id_siswa_id_unique` (`jadwal_ujian_id`,`siswa_id`),
  KEY `hasil_ujian_enrollment_ujian_id_foreign` (`enrollment_ujian_id`),
  KEY `hasil_ujian_jadwal_ujian_id_status_index` (`jadwal_ujian_id`,`status`),
  KEY `hasil_ujian_siswa_id_status_index` (`siswa_id`,`status`),
  KEY `hasil_ujian_sesi_ruangan_id_status_index` (`sesi_ruangan_id`,`status`),
  CONSTRAINT `hasil_ujian_enrollment_ujian_id_foreign` FOREIGN KEY (`enrollment_ujian_id`) REFERENCES `enrollment_ujian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_ujian_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hasil_ujian_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_ujian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mapel_id` bigint unsigned NOT NULL,
  `tanggal` datetime NOT NULL,
  `durasi_menit` int NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auto_assign_sesi` tinyint(1) NOT NULL DEFAULT '1',
  `scheduling_mode` enum('fixed','flexible') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'flexible',
  `timezone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Asia/Jakarta',
  `tampilkan_hasil` tinyint(1) NOT NULL DEFAULT '0',
  `jumlah_soal` int NOT NULL DEFAULT '0',
  `kelas_target` json DEFAULT NULL,
  `bank_soal_id` bigint unsigned DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `kode_ujian` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_ujian` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acak_soal` tinyint(1) NOT NULL DEFAULT '0',
  `acak_jawaban` tinyint(1) NOT NULL DEFAULT '0',
  `aktifkan_auto_logout` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `jadwal_ujian_kode_ujian_unique` (`kode_ujian`),
  KEY `jadwal_ujian_created_by_foreign` (`created_by`),
  KEY `jadwal_ujian_bank_soal_id_foreign` (`bank_soal_id`),
  KEY `jadwal_ujian_mapel_id_foreign` (`mapel_id`),
  CONSTRAINT `jadwal_ujian_bank_soal_id_foreign` FOREIGN KEY (`bank_soal_id`) REFERENCES `bank_soal` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jadwal_ujian_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `jadwal_ujian_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jadwal_ujian_sesi_ruangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jadwal_ujian_sesi_ruangan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `jadwal_ujian_id` bigint unsigned NOT NULL,
  `sesi_ruangan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `pengawas_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jadwal_sesi_unique` (`jadwal_ujian_id`,`sesi_ruangan_id`),
  KEY `jadwal_ujian_sesi_ruangan_jadwal_ujian_id_index` (`jadwal_ujian_id`),
  KEY `jadwal_ujian_sesi_ruangan_sesi_ruangan_id_index` (`sesi_ruangan_id`),
  KEY `jadwal_ujian_sesi_ruangan_pengawas_id_foreign` (`pengawas_id`),
  CONSTRAINT `jadwal_ujian_sesi_ruangan_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujian` (`id`) ON DELETE CASCADE,
  CONSTRAINT `jadwal_ujian_sesi_ruangan_pengawas_id_foreign` FOREIGN KEY (`pengawas_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL,
  CONSTRAINT `jadwal_ujian_sesi_ruangan_sesi_ruangan_id_foreign` FOREIGN KEY (`sesi_ruangan_id`) REFERENCES `sesi_ruangan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jawaban_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jawaban_siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `hasil_ujian_id` bigint unsigned NOT NULL,
  `soal_ujian_id` bigint unsigned NOT NULL,
  `jawaban` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_flagged` tinyint(1) NOT NULL DEFAULT '0',
  `waktu_jawab` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kelas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mapel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapel` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_mapel` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mapel` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tingkat` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jurusan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mapel_kode_mapel_unique` (`kode_mapel`),
  KEY `mapel_tingkat_jurusan_index` (`tingkat`,`jurusan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pelanggaran_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pelanggaran_ujian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `siswa_id` bigint unsigned NOT NULL,
  `hasil_ujian_id` bigint unsigned NOT NULL,
  `jadwal_ujian_id` bigint unsigned NOT NULL,
  `sesi_ruangan_id` bigint unsigned NOT NULL,
  `jenis_pelanggaran` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `waktu_pelanggaran` timestamp NOT NULL,
  `is_dismissed` tinyint(1) NOT NULL DEFAULT '0',
  `is_finalized` tinyint(1) NOT NULL DEFAULT '0',
  `tindakan` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_pengawas` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pelanggaran_ujian_siswa_id_foreign` (`siswa_id`),
  KEY `pelanggaran_ujian_hasil_ujian_id_foreign` (`hasil_ujian_id`),
  KEY `pelanggaran_ujian_jadwal_ujian_id_foreign` (`jadwal_ujian_id`),
  KEY `pelanggaran_ujian_sesi_ruangan_id_foreign` (`sesi_ruangan_id`),
  CONSTRAINT `pelanggaran_ujian_hasil_ujian_id_foreign` FOREIGN KEY (`hasil_ujian_id`) REFERENCES `hasil_ujian` (`id`),
  CONSTRAINT `pelanggaran_ujian_jadwal_ujian_id_foreign` FOREIGN KEY (`jadwal_ujian_id`) REFERENCES `jadwal_ujian` (`id`),
  CONSTRAINT `pelanggaran_ujian_sesi_ruangan_id_foreign` FOREIGN KEY (`sesi_ruangan_id`) REFERENCES `sesi_ruangan` (`id`),
  CONSTRAINT `pelanggaran_ujian_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ruangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ruangan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_ruangan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_ruangan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasi` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kapasitas` int NOT NULL,
  `fasilitas` json DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruangan_kode_ruangan_unique` (`kode_ruangan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sesi_ruangan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesi_ruangan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_sesi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_sesi` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `waktu_mulai` time NOT NULL,
  `waktu_selesai` time NOT NULL,
  `token_ujian` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expired_at` datetime DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_mulai',
  `pengaturan` json DEFAULT NULL,
  `ruangan_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sesi_ruangan_ruangan_id_foreign` (`ruangan_id`),
  CONSTRAINT `sesi_ruangan_ruangan_id_foreign` FOREIGN KEY (`ruangan_id`) REFERENCES `ruangan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sesi_ruangan_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesi_ruangan_siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sesi_ruangan_id` bigint unsigned NOT NULL,
  `siswa_id` bigint unsigned NOT NULL,
  `status_kehadiran` enum('hadir','tidak_hadir','sakit','izin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sesi_ruangan_siswa_sesi_ruangan_id_siswa_id_unique` (`sesi_ruangan_id`,`siswa_id`),
  KEY `sesi_ruangan_siswa_siswa_id_foreign` (`siswa_id`),
  KEY `idx_sesi_ruangan_siswa_status` (`sesi_ruangan_id`),
  CONSTRAINT `sesi_ruangan_siswa_sesi_ruangan_id_foreign` FOREIGN KEY (`sesi_ruangan_id`) REFERENCES `sesi_ruangan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sesi_ruangan_siswa_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `siswa` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nis` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idyayasan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` bigint unsigned DEFAULT NULL,
  `status_pembayaran` enum('Lunas','Belum Lunas') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Belum Lunas',
  `rekomendasi` enum('ya','tidak') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tidak',
  `catatan_rekomendasi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `siswa_idyayasan_unique` (`idyayasan`),
  UNIQUE KEY `siswa_email_unique` (`email`),
  UNIQUE KEY `siswa_nis_unique` (`nis`),
  KEY `siswa_kelas_id_foreign` (`kelas_id`),
  CONSTRAINT `siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `soal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `soal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint unsigned DEFAULT NULL,
  `bank_soal_id` bigint unsigned NOT NULL,
  `is_parent` tinyint(1) NOT NULL DEFAULT '0',
  `tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pg',
  `kategori` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sedang',
  `display_settings` json DEFAULT NULL,
  `nomor_soal` int NOT NULL DEFAULT '1',
  `tipe_soal` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pilihan_ganda',
  `pertanyaan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_pertanyaan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `pilihan_a_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pilihan_a_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan_a_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `pilihan_b_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pilihan_b_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan_b_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `pilihan_c_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pilihan_c_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan_c_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `pilihan_d_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pilihan_d_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan_d_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `pilihan_e_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pilihan_e_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan_e_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `gambar_pertanyaan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pilihan` json DEFAULT NULL,
  `kunci_jawaban` char(1) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pembahasan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pembahasan_teks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `pembahasan_gambar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pembahasan_tipe` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'teks',
  `bobot` decimal(5,2) NOT NULL DEFAULT '1.00',
  `status` enum('aktif','draft','arsip') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `gambar_pembahasan` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `soal_bank_soal_id_nomor_index` (`bank_soal_id`),
  KEY `soal_parent_id_foreign` (`parent_id`),
  CONSTRAINT `soal_bank_soal_id_foreign` FOREIGN KEY (`bank_soal_id`) REFERENCES `bank_soal` (`id`) ON DELETE CASCADE,
  CONSTRAINT `soal_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `soal` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2021_06_09_050000_create_kelas_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2021_06_09_100000_create_siswa_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2022_06_10_000100_create_mapel_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2023_01_01_000000_create_guru_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2023_01_01_000002_create_jadwal_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2023_01_01_000003_create_ruangan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2023_01_01_000003_create_sesi_ruangan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2023_01_01_000005_create_enrollment_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2023_01_01_000006_create_berita_acara_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2023_06_01_000007_create_hasil_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2023_06_09_110000_add_siswa_foreign_key_to_enrollment_ujian',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2023_06_10_001000_create_bank_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2023_06_10_001500_add_bank_soal_foreign_key_to_jadwal_ujian',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2023_06_10_002000_create_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2023_06_10_003000_update_jadwal_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2024_09_01_000000_make_kunci_jawaban_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2024_09_01_100000_modify_kunci_jawaban_nullable',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_24_112621_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_28_030145_create_storage_directories',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_28_224330_add_deleted_at_to_bank_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_29_000535_replace_tingkat_kesulitan_from_bank_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_29_144924_add_deleted_at_to_mapel_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_30_044041_add_foreign_key_to_siswa_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_30_124108_update_jadwal_ujian_foreign_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_08_31_034635_update_enrollment_and_sesi_ruangan_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2025_08_31_035942_create_sesi_ruangan_siswa_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2025_08_31_044524_add_indexes_to_sesi_ruangan_siswa',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2025_09_02_000000_add_sesi_ruangan_id_to_berita_acara_ujian',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2025_09_02_045237_add_template_id_to_sesi_ruangan_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2025_09_02_045314_create_sesi_templates_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2025_09_02_045444_update_sesi_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2025_09_02_064307_remove_sesi_ujian_id_from_enrollment_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2025_09_02_064405_drop_sesi_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2025_09_02_120000_update_soal_table_complete_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2025_09_03_000000_add_jadwal_ujian_id_to_enrollment_ujian_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2025_09_03_100000_update_sesi_template_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2025_09_03_120000_correct_soal_table_structure',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2025_09_03_130000_add_missing_columns_to_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2025_09_03_140000_rename_columns_in_soal_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2025_09_03_160000_add_missing_columns_to_soal_table_corrected',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2025_09_04_140615_add_jadwal_ujian_id_to_sesi_ruangan_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2025_09_03_150000_add_missing_columns_to_soal_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2025_09_05_000000_create_jadwal_ujian_sesi_ruangan_pivot_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2025_09_05_062133_modify_jadwal_ujian_status_column',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2025_09_05_000001_remove_jadwal_ujian_id_from_sesi_ruangan',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2025_09_05_062044_update_jadwal_ujian_status_values',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2025_09_05_064024_modify_jadwal_ujian_for_sesi_based_scheduling',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2025_09_06_000001_remove_tanggal_from_sesi_ruangan_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2025_09_10_000000_move_pengawas_id_to_pivot_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2025_09_10_123145_add_user_id_to_guru_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2025_09_10_130743_make_password_nullable_in_guru_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2025_09_10_153213_migrate_legacy_pengawas_assignments_to_pivot',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2025_09_10_225352_remove_pengawas_id_from_sesi_ruangan_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2025_09_10_225412_remove_pengawas_id_from_sesi_ruangan_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2025_09_11_033109_add_missing_columns_to_sesi_ruangan_siswa_table',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2025_09_11_133841_remove_status_column_from_sesi_ruangan_siswa_table',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2025_09_11_183905_update_status_pelaksanaan_enum_values_in_berita_acara_ujian_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2025_01_21_000001_remove_token_columns_from_sesi_ruangan_siswa',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2025_09_12_142724_remove_token_columns_from_enrollment_ujian_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2025_09_12_145442_add_active_status_to_enrollment_ujian_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2025_09_12_221158_create_jawaban_siswas_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2023_10_30_000001_fix_jawaban_siswa_foreign_key',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2023_11_05_add_aktifkan_auto_logout_to_jadwal_ujian_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2023_12_13_000000_create_pelanggaran_ujian_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2023_12_13_000001_add_violations_count_to_hasil_ujian_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2025_09_16_100000_create_sesi_template_assignments_tables',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2025_09_16_131552_remove_template_id_from_sesi_ruangan_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2025_09_16_131614_drop_template_tables',22);
