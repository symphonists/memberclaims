<?php

	if(!defined('__IN_SYMPHONY__')) die('<h2>Error</h2><p>You cannot directly access this file</p>');
	
	require_once(EXTENSIONS . '/memberclaims/lib/class.claims.php');

	Class eventMemberClaim extends Event {
	
	/*-------------------------------------------------------------------------
		Setup
	-------------------------------------------------------------------------*/
	
		const ROOTELEMENT = 'member-claim';
	
		public static function about() {
			return array(
				'name' => 'Member Claim',
				'author' => array(
					'name' => 'Craig Zheng	',
					'email' => 'craig@symphony-cms.com'),
				'version' => '1.0',
				'release-date' => '2011-05-19'
			);
		}
		
		public static function allowEditorToParse() {
			return false;
		}
		
	/*-------------------------------------------------------------------------
		Processing
	-------------------------------------------------------------------------*/
		
		public function load() {
			if(isset($_POST['action'][self::ROOTELEMENT])) return $this->__trigger();
		}
		
		protected function __trigger() {

			// Grab the member's id
			$member_id = Frontend::instance()->Page()->_param['member-id'];
			
			// Set the entry id and field id from POST
			$entry_id = $_POST['entry-id'];
			$field_id = $_POST['field-id'];
			
			$errors = array();
			
			// validation step
			if (is_null($member_id)) {
				$errors[] = 'Member not logged in';
			} elseif (is_null($entry_id)) {
				$errors[] = 'No entry ID provided';
			}
			} elseif (is_null($field_id)) {
				$errors[] = 'No field ID provided';
			}
			
			if(!empty($errors)) {
				// attach error element
				// return;
			}
			else {
				// If a claim already exists, then we're removing it
				if(Claims::check($entry_id, $field_id, $member_id)) {
					$success = Claims::remove($entry_id, $field_id, $member_id);
					// Build output element
				}
				// Otherwise, we're creating one
				else {
					$success = Claims::create($entry_id, $field_id, $member_id);
					// Build output element
				}
	
				// If a redirect has been specified, do it
				if($success && isset($_REQUEST['redirect'])) redirect($_REQUEST['redirect']);
			}
			
			
		}
		
	/*-------------------------------------------------------------------------
		Documentation
	-------------------------------------------------------------------------*/
		
		public static function documentation() {
			// Attach event to a page that you want to be able to submit member claims to, normally an AJAX page
			// <input name="action[member-claim]" type="submit" value="Follow" />
			// just need to pass an entry id and a mode (create/remove)
			// optionally pass a redirect
		}
	
	}
