<?php

	Class extension_Memberclaims extends Extension {

		public function install() {
			Symphony::Database()->query(
				'CREATE TABLE IF NOT EXISTS `tbl_member_claims` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`field_id` int(11) unsigned NOT NULL,
					`member_id` int(11) unsigned NOT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `field_id` (`field_id`),
					KEY `member_id` (`member_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;'
			);

			Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_memberclaim` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `field_id` int(11) unsigned NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `field_id` (`field_id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
			");

			return true;
		}

		public function uninstall() {
			Symphony::Database()->query(
				'DROP TABLE IF EXISTS `tbl_member_claims`, `tbl_fields_memberclaim`;'
			);
		}
	}
