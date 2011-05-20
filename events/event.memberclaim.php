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
			
			$result = new XMLElement(self::ROOTELEMENT);
			
			$fields = $_POST;

			// Add POST values to the Event XML
			$post_values = new XMLElement('post-values');

			// Create the post data cookie element
			if (is_array($fields) && !empty($fields)) {
				General::array_to_xml($post_values, $fields, true);
			}

			// Grab the member's id
			$member_id = Frontend::instance()->Page()->_param['member-id'];
			
			// Set the entry id and field id from POST
			$entry_id = $_POST['entry-id'];
			$field_id = $_POST['field-id'];
			
			$errors = array();
			
			// Validate the required data
			if(is_null($member_id)) {
				$errors[] = __('No member logged in');
			}
			elseif (is_null($entry_id)) {
				$errors[] = __('No entry ID provided');
			}
			elseif (is_null($field_id)) {
				$errors[] = __('No field ID provided');
			}
			
			if(!empty($errors)) {
				$result->setAttribute('result', 'error');
				
				foreach($errors as $error) {
					$result->appendChild(
						new XMLElement('error', null, array(
							'type' => 'invalid',
							'message' => $error)
						)
					);
				}
				
				$result->appendChild($post_values);
				return $result;
			}
			else {
				
				// If a claim already exists, then we're removing it
				if(Claims::check($entry_id, $field_id, $member_id)) {
					$result->setAttribute('type', 'removed');
					$success = Claims::remove($entry_id, $field_id, $member_id);
				}
				// Otherwise, we're creating one
				else {
					$result->setAttribute('type', 'created');
					$success = Claims::create($entry_id, $field_id, $member_id);
				}
				
				// If claim was successful, append claim data to XML
				if($success) {
					$data = array(
						'entry-id'	=> $entry_id,
						'field-id'	=> $field_id,
						'member-id'	=> $member_id
					);
					$result->setAttribute('result', 'success');
					General::array_to_xml($result, $data, true);
				}
				// Otherwise, append error
				else {
					$result->setAttribute('result', 'error');
					$result->appendChild(
						new XMLElement('error', null, array(
							'type' => 'invalid',
							'message' => __('Unable to complete claim'))
						)
					);
				}
	
				// If a redirect has been specified, do it
				if($success && isset($_REQUEST['redirect'])) redirect($_REQUEST['redirect']);
				
				return $result;
			}
			
			
		}
		
	/*-------------------------------------------------------------------------
		Documentation
	-------------------------------------------------------------------------*/
		
		public static function documentation() {
			return '
				<p>This event can be used to create member claims on an entry.</p>
				<h3>Usage</h3>
				<p>Attach this event to a single, standalone page to be used for all member claims sitewide. Then, simply <code>POST</code> the following fields to that page to create/remove claims:</p>
						<ul>
							<li><code>entry-id</code>: ID of the entry being claimed</li>
							<li><code>field-id</code>: ID of the claim field*</li>
							<li><code>action[member-claim]</code>: used to trigger the event</li>
						</ul>
				<p class="help">* The Member Claim field\'s XML output always includes its field id</p>
				<h3>Example Front-end Form Markup</h3>
				<p>You\'ll likely want to handle these requests asynchronously, but in the event you need to use traditional forms, you could use the following markup to display a claim button:</p>
				<pre class="XML"><code>
	&lt;form method="post" action="{path/to/your/claim/page}"&gt;
		&lt;input type="hidden" name="entry-id" value="{entry-id}"/&gt;
		&lt;input type="hidden" name="field-id" value="{field-id}"/&gt;
		&lt;input type="submit" name="action['.self::ROOTELEMENT.']" value="{Your Button Text}"/&gt;
	&lt;/form&gt;
				</code></pre>
				<p>Optional redirect is also accepted:</p>
				<pre class="XML"><code>
	&lt;input type="hidden" name="redirect" value="{$root}/"/&gt;
				</code></pre>
				<h3>Sample Success Response</h3>
				<pre class="XML"><code>
	&lt;member-claim type="created" result="success"&gt;
		&lt;entry-id&gt;22&lt;/entry-id&gt;
		&lt;field-id&gt;43&lt;/field-id&gt;
		&lt;member-id&gt;1&lt;/member-id&gt;
	&lt;/member-claim&gt;
				</code></pre>
				<h3>Sample Failure Response</h3>
				<pre class="XML"><code>
	&lt;member-claim result="error"&gt;
	&lt;error type="invalid" message="No entry ID provided" /&gt;
	&lt;post-values&gt;
		&lt;field-id>43&lt;/field-id&gt;
		&lt;action&gt;
			&lt;member-claim&gt;Follow&lt;/member-claim&gt;
		&lt;/action&gt;
	&lt;/post-values&gt;
	&lt;/member-claim&gt;
				</code></pre>
			';
		}
	
	}
