<?php

	Class extension_Memberclaims extends Extension {
		
		public function about() {
			return array(
				'name' 			=> 'Member Claims',
				'version' 		=> '0.1 beta',
				'release-date'	=> '18 May 2011',
				'author' => array(
					'name'		=> 'Craig Zheng',
					'website'	=> 'http://mongrl.com',
					'email'		=> 'craig@symphony-cms.com'
				),
				'description'	=> 'Unique Member-to-Entry actions'
			);
		}
	
		public function install() {
		
			Symphony::Database()->query(
				'CREATE TABLE `tbl_member_claims` (
					`id` int(11) unsigned NOT NULL auto_increment,
					`entry_id` int(11) unsigned NOT NULL,
					`field_id` int(11) unsigned NOT NULL,
					`member_id` int(11) unsigned NOT NULL,
					PRIMARY KEY (`id`),
					KEY `entry_id` (`entry_id`),
					KEY `field_id` (`field_id`),
					KEY `member_id` (`member_id`)
				);');
				
			Symphony::Database()->query("
				CREATE TABLE IF NOT EXISTS `tbl_fields_memberclaim` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `field_id` int(11) unsigned NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `field_id` (`field_id`)
				) ENGINE=MyISAM;
			");
			return;
		}
		
		public function uninstall() {
			Symphony::Database()->query(
				'DROP TABLE `tbl_member_claims`;'
			);
		}
	}
