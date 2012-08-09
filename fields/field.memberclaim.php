<?php

	require_once(EXTENSIONS . '/memberclaims/lib/class.claims.php');

	Class fieldMemberClaim extends Field {
	
	/*-------------------------------------------------------------------------
		Definition:
	-------------------------------------------------------------------------*/

		public function __construct() {
			parent::__construct();
			$this->_name = __('Member Claim');
		}
		
		public function isSortable(){
			return true;
		}
		
		public function canFilter(){
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

			return Symphony::Database()->insert(
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
		
			if(!is_null($data['count'])) {
				$value = $data['count'];

				$label = Widget::Label($this->get('label'));
			
				$p = new XMLElement('p', $value);
				$label->appendChild($p);
			
				$wrapper->appendChild($label);
			}
		}
		
		public function processRawFieldData(
							$data,
							&$status,
							&$message=NULL,
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
				$this->get('element_name'),
				$this->get('element_name') . ': list',
			);
		}
		
		public function appendFormattedElement(
							XMLElement &$wrapper,
							$data,
							$encode = false,
							$mode = null,
							$entry_id = null
		) {
		
			// Grab the field's ID
			$field_id = $this->get('id');
			
			// Grab the claim count
			$count = $data['count'];
			
			$current = 'No';
			// Determine if the current member is a claimant
			if($member_id = Frontend::instance()->Page()->_param['member-id']) {

			    if(Claims::check($entry_id, $field_id, $member_id)) {
			        $current = 'Yes';
			    }
			}
			
			$output = new XMLElement(
				$this->get('element_name'),
				NULL,
				array(
					'count'				=> $count,
					'field-id'			=> $field_id,
					'current-member'	=> $current
				)
			);
			
			// Mode is ids, so fetch those
			if($mode == 'list') {
				$results = Claims::fetchMembers($entry_id, $field_id);
				
				foreach($results as $index => $result) {
					$item = new XMLElement(
						'item',
						$result['member_id']
					);
					$output->appendChild($item);
				}
			}

			$wrapper->appendChild($output);
		}
		
		public function prepareTableValue(
							$data,
							XMLElement $link=NULL
		) {
			return $data['count'];
		}
		
	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/
	
		public function displayDatasourceFilterPanel(
							&$wrapper,
							$data=NULL,
							$errors=NULL,
							$fieldnamePrefix=NULL,
							$fieldnamePostfix=NULL
		) {
			$header = new XMLElement('header');
			$header->appendChild(new XMLElement('h4', $this->get('label')));
			$header->appendChild(new XMLElement('span', $this->name()));
			$wrapper->appendChild($header);
			
			$label = Widget::Label('Member IDs');
			
			$label->appendChild(
				Widget::Input(
					'fields[filter]' . 
						($fieldnamePrefix ? '['.$fieldnamePrefix.']' : '') . 
						'['.$this->get('id').']' . 
						($fieldnamePostfix ? '['.$fieldnamePostfix.']' : ''),
					($data ? General::sanitize($data) : NULL)
				)
			);
			$wrapper->appendChild($label);
			
			// {$member-id} hint
			$optionlist = new XMLElement('ul');
			$optionlist->setAttribute('class', 'tags singular');
			$optionlist->appendChild(new XMLElement('li', '{$member-id}'));
			$wrapper->appendChild($optionlist);
		}
		
		public function buildDSRetrievalSQL(
							$data,
							&$joins,
							&$where,
							$andOperation=false
		) {
			$joins .= " LEFT JOIN
							`tbl_member_claims` AS `claim`
							ON (`e`.`id` = `claim`.entry_id)";
			$where .= " AND `claim`.field_id = ". $this->get('id'). " AND `claim`.member_id = ('".implode("', '", $data)."')";
			return true;
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
			$joins .= "LEFT OUTER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
			$sort = 'ORDER BY ' . (in_array(strtolower($order), array('random', 'rand')) ? 'RAND()' : "`ed`.`count` $order");
		}
	
	}
