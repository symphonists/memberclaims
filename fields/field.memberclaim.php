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
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"
			);
		}

	/*-------------------------------------------------------------------------
		Settings:
	-------------------------------------------------------------------------*/

		public function displaySettingsPanel(XMLElement &$wrapper, $errors = null)
		{
			parent::displaySettingsPanel($wrapper, $errors);
			$this->appendShowColumnCheckbox($wrapper);
		}

		public function commit()
		{
			if (!parent::commit()) return false;

			$id = $this->get('id');
			$handle = $this->handle();

			if ($id === false) return false;

			$fields = array(
				'field_id' => $id
			);

			return FieldManager::saveSettings($id, $fields);
		}

	/*-------------------------------------------------------------------------
		Publish:
	-------------------------------------------------------------------------*/

	public function displayPublishPanel(XMLElement &$wrapper, $data = null, $flagWithError = null, $fieldnamePrefix = null, $fieldnamePostfix = null, $entry_id = null)
		{

			if(!is_null($data['count'])) {
				$value = $data['count'];

				$label = Widget::Label($this->get('label'));

				$p = new XMLElement('p', $value);
				$label->appendChild($p);

				$wrapper->appendChild($label);
			}
		}

		public function processRawFieldData($data, &$status, &$message=null, $simulate = false, $entry_id = null)
		{
			$status = self::__OK__;

			$count = Claims::countMembers($entry_id, $this->get('id'));

			return array(
				'count' => $count,
			);
		}

	/*-------------------------------------------------------------------------
		Output:
	-------------------------------------------------------------------------*/

		public function fetchIncludableElements()
		{
			return array(
				$this->get('element_name'),
				$this->get('element_name') . ': list',
			);
		}

		public function appendFormattedElement(XMLElement &$wrapper, $data, $encode = false, $mode = null, $entry_id = null)
		{

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

		public function prepareTableValue($data, XMLElement $link = null, $entry_id = null) {
			return $data['count'];
		}

	/*-------------------------------------------------------------------------
		Filtering:
	-------------------------------------------------------------------------*/

		public function displayDatasourceFilterPanel(XMLElement &$wrapper, $data = null, $errors = null, $fieldnamePrefix = null, $fieldnamePostfix = null) {
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

		public function buildDSRetrievalSQL($data, &$joins, &$where, $andOperation = false) {
			$joins .= " LEFT JOIN
							`tbl_member_claims` AS `claim`
							ON (`e`.`id` = `claim`.entry_id)";
			$where .= " AND `claim`.field_id = ". $this->get('id'). " AND `claim`.member_id = ('".implode("', '", $data)."')";
			return true;
		}

	/*-------------------------------------------------------------------------
		Sorting:
	-------------------------------------------------------------------------*/

		public function buildSortingSQL(&$joins, &$where, &$sort, $order='ASC') {
			if(in_array(strtolower($order), array('random', 'rand'))) {
				$sort = 'ORDER BY RAND()';
			}
			else {
				$joins .= "LEFT OUTER JOIN `tbl_entries_data_".$this->get('id')."` AS `ed` ON (`e`.`id` = `ed`.`entry_id`) ";
				$sort = "ORDER BY `ed`.`count` $order";
			}
		}

	}
