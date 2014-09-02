<pre>
<?
	require('database.class.php');
	require('wordpress-database.config.php');
	$db = db::getInstance($config);
	
	define('DRUPAL_ROOT','/home/khameleo/public_html/drupal');
	require('../drupal/includes/bootstrap.inc');
	drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
	
	define('DRUPAL_ADMIN_UID',1);
	define('DRUPAL_BOOL_FALSE',0);
	define('DRUPAL_COMMENTS_OFF',1);
	
	define('DEFAULT_SUBSCRIPTION_LENGTH',365);
	define('EXTENDED_SUBSCRIPTION_LENGTH',456);
	define('DEFAULT_TIMEZONE','America/Chicago');
	define('ISO_DATE_FORMAT','Y-m-d\TH:i:s');
	
	$access_codes = $db->query('select * from access_codes;');
	
	$once = false;
	
	foreach($access_codes as $access_code){
		$node = new stdClass();
		$node->title = 'Access Code '.str_pad($access_code['ID'],5,'0',STR_PAD_LEFT);
		$node->type = 'access_code';
		$node->language = LANGUAGE_NONE;
		$node->uid = DRUPAL_ADMIN_UID;
		$node->status = DRUPAL_BOOL_FALSE;
		$node->promote = DRUPAL_BOOL_FALSE;
		$node->comment = DRUPAL_COMMENTS_OFF;
		
		$node->field_email[$node->language][]['value'] = $access_code['email'];
		$node->field_teacher_code[$node->language][]['value'] = $access_code['codes1'];
		$node->field_student_code[$node->language][]['value'] = $access_code['codes2'];
		$node->field_teacher_code_used[$node->language][]['value'] = $access_code['status1'];
		$node->field_student_code_used[$node->language][]['value'] = $access_code['status2'];
		
		if(substr($access_code['codes1'],0,4) === 'CCCC'){
			$node->field_subscription_length_days[$node->language][]['value'] = EXTENDED_SUBSCRIPTION_LENGTH;
		}else{
			$node->field_subscription_length_days[$node->language][]['value'] = DEFAULT_SUBSCRIPTION_LENGTH;
		}
		
		$node->field_valid_dates[$node->language][] = array(
			'value' => date(ISO_DATE_FORMAT),
			'value2' => str_replace(' ','T',$access_code['time']),
			'timezone' => DEFAULT_TIMEZONE,
			'data_type' => 'date',
			'db' => array(
				'value' => new DateObject(date(ISO_DATE_FORMAT),DEFAULT_TIMEZONE),
				'value2' => new DateObject($access_code['time'],DEFAULT_TIMEZONE),
			),
		);
		
		$uid = array_keys(db_query("select uid from users where mail = :mail;", array(':mail' => $access_code['email']))->fetchAllAssoc('uid'));
		if(!empty($uid)){
			$uid = $uid[0];
			$user = user_load($uid);
			$node->field_user[$node->language][] = array('uid' => $uid, 'access' => 1, 'user' => $user);
		}
		
		$existing = array_keys(db_query("select nid from node where type = 'access_code' and title = '".$node->title."';")->fetchAllAssoc('nid'));
		if(empty($existing)){
			$node->is_new = true;
		}else{
			$node->nid = $existing[0];
		}
		
		node_save($node);
		if(!is_numeric($node->nid)){
			echo 'Could not migrate ',$node->title,'. Check source data integrity.',"\n";
		}
		unset($node);
	}
?>
</pre>