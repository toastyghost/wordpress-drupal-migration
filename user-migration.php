<pre>
<?
	require('database.class.php');
	require('wordpress-database.config.php');
	$db = db::getInstance($config);
	
	require('role-values.config.php');
	$role_map = array(
		WP_ACCESS_LEVEL_INACTIVE => array(DRUPAL_ROLE_AUTHENTICATED,),
		WP_ACCESS_LEVEL_STUDENT => array(DRUPAL_ROLE_INACTIVE_STUDENT,),
		WP_ACCESS_LEVEL_TEACHER => array(DRUPAL_ROLE_AUTHENTICATED,DRUPAL_ROLE_TEACHER,DRUPAL_ROLE_SUBSCRIBER),
	);
	
	define('DATA','data');
	define('REVISION','revision');
	
	$users = $db->query('select u.*, m1.meta_value as first_name, m2.meta_value as last_name from wp_users u
						 left join wp_usermeta m1 on m1.user_id = u.ID
						 left join wp_usermeta m2 on m2.user_id = u.ID
						 where m1.meta_key = "first_name"
						 and m2.meta_key = "last_name";');
	unset($db);
	
	$i = 0;
	$len = count($users);
	if($len>0){
		$value_split_sql = '","';
		$record_split_sql = '),(';
		$end_sql = ');';
		
		$users_sql = 'insert into users(uid,name,pass,mail,created,status,init,data) values(';
		$roles_sql = 'insert into users_roles(uid,rid) values(';
		
		$custom_field_names = array('expiration','first_name','last_name','mature_content');
		
		foreach($custom_field_names as $key){
			$field_sql[DATA][$key] = 'insert into field_data_field_'.$key.'(entity_type,bundle,deleted,entity_id,revision_id,language,delta,field_'.$key.'_value) values(';
			$field_sql[REVISION][$key] = 'insert into field_revision_field_'.$key.'(entity_type,bundle,deleted,entity_id,revision_id,language,delta,field_'.$key.'_value) values(';
		}
		
		while($i<$len){
			extract($users[$i]);
			
			foreach($role_map as $wp_access_level => $roles){
				$role_counter = 1;
				$roles_length = count($roles);
				if($access_level == $wp_access_level){
					foreach($roles as $role){
						$roles_sql .= $ID.','.$role.$record_split_sql;
						++$role_counter;
					}
				}
			}
			
			$user_registered = strtotime($user_registered);
			$user_data = mysql_real_escape_string(serialize(array(
				'wp_pass' => $user_pass,
				'nicename' => $user_nicename,
				'url' => $user_url,
				'display_name' => $display_name,
				'activation_key' => $user_activation_key,
				'status' => $user_status
			)));
			
			$users_sql .=
				$ID.',"'.
				$user_login.$value_split_sql.
				$user_pass.$value_split_sql.
				$user_email.$value_split_sql.
				$user_registered.'",1,"'.
				$user_email.$value_split_sql.
				$user_data.'"';
			
			$custom_field_values['first_name'] = $first_name;
			$custom_field_values['last_name'] = $last_name;
			$custom_field_values['expiration'] = strtotime($access_expiration);
			$custom_field_values['mature_content'] = $dentista;
			
			foreach($custom_field_names as $key){
				if($custom_field_values[$key] !== NULL){
					if(is_numeric($custom_field_values[$key])){
						$value_encaps = NULL;
					}else{
						$value_encaps = '"';
					}
					
					$value = '"user","user",0,'.$ID.',1,"und",0,'.$value_encaps.$custom_field_values[$key].$value_encaps;
					$field_sql[DATA][$key] .= $value;
					$field_sql[REVISION][$key] .= $value;
				}
			}
			
			++$i;
			
			if($i<$len){
				$users_sql .= $record_split_sql;
				foreach($custom_field_names as $key){
					$field_sql[DATA][$key] .= $record_split_sql;
					$field_sql[REVISION][$key] .= $record_split_sql;
				}
			}
			unset($ID,$user_login,$user_pass,$user_nicename,$user_email,$user_url,$user_registered,$access_level,$access_expiration,$remind_num,$remind_date,$dentista,$user_activation_key,$user_status,$display_name,$first_name,$last_name);
		}
		$users_sql .= $end_sql;
		$roles_sql = substr_replace($roles_sql,';',strrpos($roles_sql,',('),2);
		foreach($custom_field_names as $key){
			$field_sql[DATA][$key] .= $end_sql;
			$field_sql[REVISION][$key] .= $end_sql;
		}
	}
	
	require('drupal-database.config.php');
	$db = db::getInstance($config);
	
	if($db->select_database('khameleo_drupal')){
		if(is_array($db->query($users_sql))){
			echo 'Successfully migrated users.',"\n";
			if(is_array($db->query($roles_sql))){
				echo 'Successfully migrated roles.',"\n";
			}
			foreach($field_sql as $table_type){
				foreach($table_type as $key => $sql){
					if(is_array($db->query($sql))){
						echo 'Successfully migrated ',$key,' data!',"\n";
					}
				}
			}
		}
	}
?>
</pre>