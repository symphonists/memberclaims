<?php

	require_once(EXTENSIONS . '/memberclaims/lib/class.claims.php');

	Class fieldMemberClaim extends Field {
	
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function __construct(&$parent) {
			parent::__construct($parent);
			$this->_name = __('Member Claim');
		}
		
		public function isSortable(){
			return true;
		}
		
	/*-------------------------------------------------------------------------
		Setup:
	-------------------------------------------------------------------------*/

		public function createTable() {
			$field_id = $this->get('id');
			
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_{$field_id}` (
				  `id` int(11) unsigned NOT NULL auto_increment,
				  `entry_id` int(11) unsigned NOT NULL,
 				  `count` int(11) unsigned NOT NULL DEFAULT 0,
				  PRIMARY KEY  (`id`),
				  UNIQUE KEY `entry_id` (`entry_id`)
				)"
			);
		}
	
	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		public function displaySettingsPanel(
							&$wrapper,
							$errors=NULL
		) {
			parent::displaySettingsPanel($wrapper, $errors);
			$this->appendShowColumnCheckbox($wrapper);
		}

		public function commit() {
			if (!parent::commit()) return false;
			
			$id = $this->get('id');
			$handle = $this->handle();

			if ($id === false) return false;

			$fields = array(
				'field_id' => $id
			);

			Symphony::Database()->query("
				DELETE FROM
					`tbl_fields_{$handle}`
				WHERE
					`field_id` = '{$id}'
				LIMIT 1
			");

			return $this->Database->insert(
				$fields,
				"tbl_fields_{$handle}"
			);
		}
		
	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

		public function displayPublishPanel(
							XMLElement &$wrapper,
							$data = null,
							$error = null,
							$prefix = null,
							$postfix = null,
							$entry_id = null
		) {
			// TODO: Label and paragraph displaying count
		}
		
		public function processRawFieldData(
							$data,
							&$status,
							$simulate=false,
							$entry_id=NULL
		) {
			$status = self::__OK__;
		
			$count = Claims::countMembers($entry_id, $this->get('id'));
			
			return array(
				'count' => $count,
			);
		}
		
	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/

		public function fetchIncludableElements() { 
			return array(
				$this->get('element_name') . ': count',
				$this->get('element_name') . ': ids',
				$this->get('element_name') . ': current-member',
			);
		}
		
		public function appendFormattedElement(
							XMLElement &$wrapper,
							$data,
							$encode = false,
							$mode = null,
							$entry_id = null
		) {
		
			$field_id = $this->get('id');
		
			// Mode is count so just return the number
			if($mode == 'count') {
				$value = $data['count'];
			}
			
			// Mode is ids, so fetch those
			elseif($mode == 'ids') {
				$results = Claims::fetchMembers($entry_id, $field_id);
				
				// TODO: Turn this into XML elements
				foreach($results as $index => $result) {
					$results[$index] = $result['member_id'];
				}
				$value = implode($results);
			}
			
			elseif ($mode == 'current-member') {
				// Checking if there's a currently-logged-in member, and if so,
				// whether they're a claimant	
				if($current_member = Frontend::instance()->Page()->_param['member-id']) {
										
					if(Claims::check($entry_id, $field_id, $member_id)) {
						$value = 'Yes';
					}
					else {
						$value = 'No';
					}
				}
			}

			// If there's a value, build an output element appending $mode
			// to the name. E.g. <claims-count> or <claims-ids>
			if($value != '') {
				$wrapper->appendChild(new XMLElement(
					$this->get('element_name') . '-' . $mode,
					$value,
					array(
						'field-id'	=> $field_id
					)
				));
			}
		}
		
		public function prepareTableValue($data, XMLElement $link=NULL) {
			return $data['count'];
		}
		
	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/

		public function buildSortingSQL(
							&$joins,
							&$where,
							&$sort,
							$order='ASC'
		) {
			// TODO: Sorting by count
		}
	
	}
