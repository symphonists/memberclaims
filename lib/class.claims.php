<?php

	/**
	 * Set of utilities for the Member Claims extension.
	 * 
	 * Allows field and event to share common functions like counting,
	 * and claim-checking.
	 */
	Class Claims {
	
	/*-------------------------------------------------------------------------
		Searching
	-------------------------------------------------------------------------*/
	
		/**
		 * Given an entry id, a field id, and a member id, check whether the
		 * member has a claim on the entry via the field
		 *
		 * Returns true if claim exists, false otherwise
		 */
		public function check($entry_id, $field_id, $member_id) {
			$result = Symphony::Database()->fetch("
				SELECT `member_id`
				FROM `tbl_member_claims`
				WHERE `entry_id` = '{$entry_id}'
				AND `field_id` = '{$field_id}'
				AND `member_id` = '{$member_id}'
			");
			
			if(!empty($result)) {
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
		 * Given an entry id and field id, fetch the ids of all members who
		 * have claims on the entry via the field
		 *
		 * Returns an array containing the member ids.
		 */
		public function fetchMembers($entry_id, $field_id) {
			$results = Symphony::Database()->fetch("
				SELECT `member_id`
				FROM `tbl_member_claims`
				WHERE `entry_id` = '{$entry_id}'
				AND `field_id` = '{$field_id}'
			");
			return $results;
		}
		
		/**
		 * Given a member id and field id, fetch the ids of all entries the member has
		 * claimed
		 *
		 * Returns an array containing the entry ids.
		 */
		public function fetchEntries($member_id, $field_id) {
			$results = Symphony::Database()->fetch("
				SELECT `entry_id`
				FROM `tbl_member_claims`
				WHERE `member_id` = '{$member_id}'
				AND `field_id` = '{$field_id}'
			");
			return $results;
		}
	
	/*-------------------------------------------------------------------------
		Managing
	-------------------------------------------------------------------------*/
	
		/**
		 * Given an entry id, field id, and a member id, create a claim
		 */
		public function create($entry_id, $field_id, $member_id) {
			
			$data = array(
				'entry_id'	=> $entry_id,
				'field_id'	=> $field_id,
				'member_id'	=> $member_id
			);
			
			$try = Symphony::Database()->insert($data, 'tbl_member_claims');

			return Claims::updateCount($entry_id, $field_id);
		}
		
		/**
		 * Given an entry id, field id, and member id, remove the associated claim
		 */
		public function remove($entry_id, $field_id, $member_id) {
			Symphony::Database()->query("
				DELETE FROM
					`tbl_member_claims`
				WHERE
					`entry_id` = '{$entry_id}'
				AND
					`field_id` = '{$field_id}'
				AND
					`member_id` = '{$member_id}'
			");
			
			return Claims::updateCount($entry_id, $field_id);
		}
	
	/*-------------------------------------------------------------------------
		Counting
	-------------------------------------------------------------------------*/
		
		/**
		 * Given an entry id and field id, count the number of claims
		 *
		 * Returns the count
		 */
		public function countMembers($entry_id, $field_id) {
			$results = Symphony::Database()->fetch("
				SELECT COUNT(*) AS `count`
				FROM `tbl_member_claims`
				WHERE `entry_id` = '{$entry_id}'
				AND `field_id` = '{$field_id}'
			");
			return $results[0]['count'];
		}
		
		/**
		 * Given an entry id and field id, update the field's claim count
		 */
		public function updateCount($entry_id, $field_id) {
			$count = Claims::countMembers($entry_id, $field_id);
			
			$fields = array(
				'count'	=> $count
			);
			
			return Symphony::Database()->query("
				REPLACE INTO `tbl_entries_data_{$field_id}`
				(entry_id, count)
				VALUES ({$entry_id}, {$count})
			");
		}
	}
