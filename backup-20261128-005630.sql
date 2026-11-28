-- backup 2026-11-28 00:56:31
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `app_notifications`;
CREATE TABLE `app_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `ticket_id` bigint(20) unsigned DEFAULT NULL,
  `message` varchar(255) NOT NULL,
  `notified_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `app_notifications_ticket_id_foreign` (`ticket_id`),
  KEY `app_notifications_user_id_read_at_index` (`user_id`,`read_at`),
  CONSTRAINT `app_notifications_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `subject_type` varchar(50) DEFAULT NULL,
  `subject_id` bigint(20) unsigned DEFAULT NULL,
  `subject_label` varchar(255) DEFAULT NULL,
  `changes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changes`)),
  `ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `audit_logs_subject_type_subject_id_index` (`subject_type`,`subject_id`),
  KEY `audit_logs_action_index` (`action`),
  KEY `audit_logs_created_at_index` (`created_at`),
  KEY `audit_user_created_idx` (`user_id`,`created_at`),
  KEY `audit_action_created_idx` (`action`,`created_at`),
  KEY `audit_subject_created_idx` (`subject_type`,`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` VALUES (1,NULL,'created','Department',1,'النظم والمعلومات','{\"name\":\"\\u0627\\u0644\\u0646\\u0638\\u0645 \\u0648\\u0627\\u0644\\u0645\\u0639\\u0644\\u0648\\u0645\\u0627\\u062a\",\"building\":\"A\",\"floor\":\"\\u0627\\u0644\\u0623\\u0648\\u0644\",\"room_number\":\"101\"}','127.0.0.1','2026-11-28 00:38:58'),
(2,NULL,'created','Department',2,'الموارد البشرية','{\"name\":\"\\u0627\\u0644\\u0645\\u0648\\u0627\\u0631\\u062f \\u0627\\u0644\\u0628\\u0634\\u0631\\u064a\\u0629\",\"building\":\"B\",\"floor\":\"\\u0627\\u0644\\u062b\\u0627\\u0646\\u064a\",\"room_number\":\"204\"}','127.0.0.1','2026-11-28 00:38:58'),
(3,NULL,'created','Department',3,'المالية','{\"name\":\"\\u0627\\u0644\\u0645\\u0627\\u0644\\u064a\\u0629\",\"building\":\"C\",\"floor\":\"\\u0627\\u0644\\u062b\\u0627\\u0644\\u062b\",\"room_number\":\"305\"}','127.0.0.1','2026-11-28 00:38:58'),
(4,NULL,'created','Department',4,'الشئون الإدارية','{\"name\":\"\\u0627\\u0644\\u0634\\u0626\\u0648\\u0646 \\u0627\\u0644\\u0625\\u062f\\u0627\\u0631\\u064a\\u0629\",\"building\":\"A\",\"floor\":\"\\u0627\\u0644\\u062b\\u0627\\u0646\\u064a\",\"room_number\":\"210\"}','127.0.0.1','2026-11-28 00:38:58'),
(5,NULL,'created','User',1,'مدير النظام','{\"email\":\"admin@admin.com\",\"name\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0646\\u0638\\u0627\\u0645\",\"role_id\":1,\"is_active\":true}','127.0.0.1','2026-11-28 00:38:58'),
(6,1,'login',NULL,NULL,NULL,NULL,'127.0.0.1','2026-11-28 00:41:16'),
(7,1,'created','User',2,'baher talal','{\"name\":\"baher talal\",\"email\":\"baher@admin.com\",\"role_id\":\"2\",\"department_id\":\"1\",\"is_active\":true}','127.0.0.1','2026-11-28 00:46:45'),
(8,1,'created','User',3,'ali','{\"name\":\"ali\",\"email\":\"ali@admin.com\",\"role_id\":\"6\",\"department_id\":\"1\",\"is_active\":true}','127.0.0.1','2026-11-28 00:47:32'),
(9,1,'created','User',4,'hamdy','{\"name\":\"hamdy\",\"email\":\"hamdy@admin.com\",\"role_id\":\"6\",\"department_id\":\"3\",\"is_active\":true}','127.0.0.1','2026-11-28 00:48:17'),
(10,1,'created','User',5,'rafaat','{\"name\":\"rafaat\",\"email\":\"rafaat@admin.com\",\"role_id\":\"5\",\"department_id\":\"3\",\"is_active\":true}','127.0.0.1','2026-11-28 00:48:50'),
(11,1,'created','Department',5,'إدارة حسابات المخازن','{\"name\":\"\\u0625\\u062f\\u0627\\u0631\\u0629 \\u062d\\u0633\\u0627\\u0628\\u0627\\u062a \\u0627\\u0644\\u0645\\u062e\\u0627\\u0632\\u0646\",\"building\":\"C\",\"floor\":\"1\",\"room_number\":\"102\"}','127.0.0.1','2026-11-28 00:49:49'),
(12,1,'created','Device',1,'dell 3080','{\"department_id\":\"3\",\"name\":\"dell 3080\",\"user_id\":\"4\",\"employee_name\":\"hamdy\",\"type\":\"company\"}','127.0.0.1','2026-11-28 00:51:38'),
(13,1,'created','Device',2,'hp','{\"department_id\":\"3\",\"name\":\"hp\",\"user_id\":\"5\",\"employee_name\":\"rafaat\",\"type\":\"company\"}','127.0.0.1','2026-11-28 00:51:58'),
(14,1,'created','Device',3,'--','{\"department_id\":\"5\",\"name\":\"--\",\"employee_name\":\"\\u0633\\u0644\\u0648\\u064a \\u062d\\u0627\\u0645\\u062f\",\"ip_address\":\"172.16.112.12\",\"notes\":\"Username: 35\\r\\nPassword: 1234\",\"type\":\"system\"}','127.0.0.1','2026-11-28 00:54:57');

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `building` varchar(1) DEFAULT NULL,
  `floor` varchar(30) DEFAULT NULL,
  `room_number` varchar(30) DEFAULT NULL,
  `sector_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_name_unique` (`name`),
  KEY `departments_building_index` (`building`),
  KEY `departments_sector_id_foreign` (`sector_id`),
  CONSTRAINT `departments_sector_id_foreign` FOREIGN KEY (`sector_id`) REFERENCES `sectors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `departments` VALUES (1,'النظم والمعلومات','2026-11-28 00:38:58','2026-11-28 00:38:58','A','الأول','101',NULL),
(2,'الموارد البشرية','2026-11-28 00:38:58','2026-11-28 00:38:58','B','الثاني','204',NULL),
(3,'المالية','2026-11-28 00:38:58','2026-11-28 00:38:58','C','الثالث','305',NULL),
(4,'الشئون الإدارية','2026-11-28 00:38:58','2026-11-28 00:38:58','A','الثاني','210',NULL),
(5,'إدارة حسابات المخازن','2026-11-28 00:49:49','2026-11-28 00:49:49','C','1','102',NULL);

DROP TABLE IF EXISTS `devices`;
CREATE TABLE `devices` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `cpu` varchar(255) DEFAULT NULL,
  `ram` varchar(255) DEFAULT NULL,
  `storage` varchar(255) DEFAULT NULL,
  `os` varchar(255) DEFAULT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `employee_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `devices_department_id_foreign` (`department_id`),
  KEY `devices_type_index` (`type`),
  KEY `devices_ip_address_index` (`ip_address`),
  KEY `devices_mac_address_index` (`mac_address`),
  KEY `devices_user_id_foreign` (`user_id`),
  KEY `devices_type_name_idx` (`type`,`name`),
  KEY `devices_type_dept_idx` (`type`,`department_id`),
  KEY `devices_type_serial_idx` (`type`,`serial`),
  CONSTRAINT `devices_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `devices` VALUES (1,'company',3,4,'dell 3080',NULL,NULL,NULL,NULL,NULL,'hamdy',NULL,NULL,NULL,'2026-11-28 00:51:38','2026-11-28 00:51:38'),
(2,'company',3,5,'hp',NULL,NULL,NULL,NULL,NULL,'rafaat',NULL,NULL,NULL,'2026-11-28 00:51:58','2026-11-28 00:51:58'),
(3,'system',5,NULL,'--',NULL,NULL,NULL,NULL,NULL,'سلوي حامد','172.16.112.12',NULL,'Username: 35\r\nPassword: 1234','2026-11-28 00:54:57','2026-11-28 00:54:57');

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),
(2,'2030_01_01_000001_create_helpdesk_tables',1),
(3,'2030_01_02_000001_add_locations_notifications',1),
(4,'2030_01_03_000001_add_created_by_to_tickets',1),
(5,'2030_01_05_000001_create_permission_user_table',1),
(6,'2030_01_06_000001_add_ticket_extras',1),
(7,'2030_01_07_000001_create_sectors',1),
(8,'2030_01_09_000001_create_kb_audit_tables',1),
(9,'2030_01_11_000001_add_performance_indexes',1);

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `permission_role`;
CREATE TABLE `permission_role` (
  `role_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_role_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permission_role` VALUES (1,1),
(1,2),
(1,3),
(1,4),
(1,5),
(1,6),
(1,7),
(1,8),
(1,9),
(1,10),
(1,11),
(1,12),
(1,13),
(1,14),
(1,15),
(1,16),
(1,17),
(1,18),
(1,19),
(1,20),
(2,3),
(2,4),
(2,6),
(2,7),
(2,8),
(2,9),
(2,10),
(2,11),
(2,12),
(2,13),
(2,14),
(2,15),
(2,16),
(2,17),
(2,20),
(3,3),
(3,5),
(3,9),
(3,17),
(4,3),
(4,5),
(4,9),
(4,17),
(5,3),
(5,5),
(5,9),
(5,17),
(6,3),
(7,8),
(7,9),
(7,13),
(7,17),
(8,3),
(8,9),
(8,13);

DROP TABLE IF EXISTS `permission_user`;
CREATE TABLE `permission_user` (
  `user_id` bigint(20) unsigned NOT NULL,
  `permission_id` bigint(20) unsigned NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `permission_user_permission_id_foreign` (`permission_id`),
  CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` VALUES (1,'users.manage','إدارة المستخدمين والإدارات'),
(2,'roles.manage','إدارة الصلاحيات'),
(3,'tickets.create','إنشاء تيكيت'),
(4,'tickets.approve','قبول/رفض التيكيت وتعيين التكنيكال (مدير عام النظم والمعلومات)'),
(5,'tickets.view_department','عرض تيكيتات إدارته'),
(6,'tickets.delete','حذف التيكيتات'),
(7,'tickets.create_for_others','إنشاء تيكيت لأي مستخدم في أي إدارة'),
(8,'tickets.view_all','عرض جميع التيكيتات'),
(9,'devices.view','عرض أجهزة الشركة'),
(10,'devices.manage','إدارة أجهزة الشركة'),
(11,'devices.export','تصدير أجهزة الشركة (Excel)'),
(12,'devices.import','استيراد أجهزة الشركة (Excel)'),
(13,'system_devices.view','عرض Computer ERP System'),
(14,'system_devices.manage','إدارة Computer ERP System'),
(15,'system_devices.export','تصدير Computer ERP System (Excel)'),
(16,'system_devices.import','استيراد Computer ERP System (Excel)'),
(17,'reports.view','عرض التقارير'),
(18,'audit.view','عرض سجل النشاط'),
(19,'backup.manage','إدارة النسخ الاحتياطي'),
(20,'dashboard.view','عرض لوحة التحكم');

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` VALUES (1,'admin','مدير النظام','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(2,'it_gm','مدير عام النظم والمعلومات','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(3,'head_of_sector','رئيس قطاع','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(4,'general_manager','مدير عام','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(5,'dept_manager','مدير إدارة','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(6,'employee','موظف','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(7,'follower','متابع','2026-11-28 00:38:58','2026-11-28 00:38:58'),
(8,'technician','تكنيكال','2026-11-28 00:38:58','2026-11-28 00:38:58');

DROP TABLE IF EXISTS `sectors`;
CREATE TABLE `sectors` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `head_id` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sectors_name_unique` (`name`),
  KEY `sectors_head_id_foreign` (`head_id`),
  CONSTRAINT `sectors_head_id_foreign` FOREIGN KEY (`head_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ticket_logs`;
CREATE TABLE `ticket_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_comment` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ticket_logs_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `ticket_logs_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ticket_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ticket_supplies`;
CREATE TABLE `ticket_supplies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) unsigned NOT NULL,
  `item` varchar(255) NOT NULL,
  `qty` int(10) unsigned NOT NULL DEFAULT 1,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ticket_supplies_ticket_id_foreign` (`ticket_id`),
  KEY `ticket_supplies_issued_by_foreign` (`issued_by`),
  CONSTRAINT `ticket_supplies_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ticket_supplies_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `device_id` bigint(20) unsigned DEFAULT NULL,
  `category` varchar(20) NOT NULL,
  `item` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` varchar(10) NOT NULL DEFAULT 'normal',
  `status` varchar(20) NOT NULL DEFAULT 'pending_approval',
  `approver_id` bigint(20) unsigned DEFAULT NULL,
  `approval_note` text DEFAULT NULL,
  `technician_id` bigint(20) unsigned DEFAULT NULL,
  `resolution_note` text DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rating` tinyint(3) unsigned DEFAULT NULL,
  `rating_note` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_code_unique` (`code`),
  KEY `tickets_user_id_foreign` (`user_id`),
  KEY `tickets_approver_id_foreign` (`approver_id`),
  KEY `tickets_technician_id_foreign` (`technician_id`),
  KEY `tickets_department_id_status_index` (`department_id`,`status`),
  KEY `tickets_status_index` (`status`),
  KEY `tickets_created_by_foreign` (`created_by`),
  KEY `tickets_status_priority_created_at_index` (`status`,`priority`,`created_at`),
  KEY `tickets_device_created_idx` (`device_id`,`created_at`),
  KEY `tickets_created_at_idx` (`created_at`),
  CONSTRAINT `tickets_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_device_id_foreign` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_technician_id_foreign` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_department_id_foreign` (`department_id`),
  KEY `users_is_active_index` (`is_active`),
  CONSTRAINT `users_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` VALUES (1,1,NULL,'مدير النظام','admin@admin.com',NULL,'$2y$12$QdXYu0n.x7UQ/wNUWyRWhODHMVjP36FY0c0.kGqNX/u4crJCfYRGO',NULL,'2026-11-28 00:38:58','2026-11-28 00:38:58',NULL,1),
(2,2,1,'baher talal','baher@admin.com',NULL,'$2y$12$VHBry2vynlYBxUOKWQbzPuZtNIjScGmLKfxWvwD7GR/hZA48K7wyO',NULL,'2026-11-28 00:46:45','2026-11-28 00:46:45',NULL,1),
(3,6,1,'ali','ali@admin.com',NULL,'$2y$12$/tHPUyuIubUrXitogElQR.k01TBh6eGfA6dw9FPg3fXBmJq1Gj/ba',NULL,'2026-11-28 00:47:32','2026-11-28 00:47:32',NULL,1),
(4,6,3,'hamdy','hamdy@admin.com',NULL,'$2y$12$Vt0uU/r0bo4KHaKLEDNfmOSwvgtmr2Za64MNbQ76idcXDACqZIY5C',NULL,'2026-11-28 00:48:17','2026-11-28 00:48:17',NULL,1),
(5,5,3,'rafaat','rafaat@admin.com',NULL,'$2y$12$ny.u9ORbNZttP0l3w/bEVeqlLkNTDeBJgfxNroc8mGNNNB05G8o5u',NULL,'2026-11-28 00:48:50','2026-11-28 00:48:50',NULL,1);

SET FOREIGN_KEY_CHECKS=1;
