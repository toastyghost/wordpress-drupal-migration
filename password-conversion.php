<?
	if(!empty($_POST)){
		/*
		 * test values to be replaced w/ db fetch & compare
		 * 
		 * $pw_text = '63l8dCiQ4Zuz';
		 * $pw_hash = '$P$BBn7NY9c0r/UlpSdCCuOv69dSrvKw40';
		 * 
		 * */
		
		if(!$_POST['username'] || !$_POST['password']){
			$error_text = 'Username and password are required.';
		}else{
			# TODO: need to add some sort of postdata validation to prevent injection attacks in place of this dummy conditional
			if(true){
				require('database.class.php');
				require('database.config.php');
				$db = db::getInstance($config);
				$db->set_charset('utf-8');
				
				$pw_hash = $db->query('select user_pass from wp_users where user_login = "'.$_POST['username'].'";');
				
				require('../../blog/wp-includes/class-phpass.php');
				$wp_hasher = new PasswordHash(8,true);
						
				if($wp_hasher->CheckPassword($_POST['password'],$pw_hash)){
					if($db->select_database('senorwoo_drupal')){
						$user_data = unserialize($db->query('select data from users where name = "'.$_POST['username'].'";'));
						if($user_data['wp_pass'] == $pw_hash){
							if($user_data['wp_pass'] != 'ACTIVATED'){
								$user_data['wp_pass'] = 'ACTIVATED';
								
								define('DRUPAL_ROOT','/home/senorwoo/public_html/test/drupal');
								require('../drupal/includes/bootstrap.inc');
								
								drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
								require('../drupal/includes/password.inc');
								
								$new_pw_hash = user_hash_password($_POST['password']);
								
								$db->query('update users set pass = "'.$new_pw_hash.'", data = "'.mysql_real_escape_string(serialize($user_data)).'" where name = "'.$_POST['username'].'" limit 1;');
								$new_user = $db->query('select * from users where name = "'.$_POST['username'].'";');
								
								if($new_user['pass'] == $new_pw_hash){
									echo 'success';
									exit;
								}else{
									$error_text = 'There was a problem activating your account.  Please contact support at <a href="mailto:senorwooly@yahoo.com">senorwooly@yahoo.com</a>.';
								}
							}else{
								$error_text = 'User account has already been activated on the new site.';
							}
						}else{
							$error_text = 'Passwords don\'t match.  If you\'ve recently changed your password, try using the previous one.  If you can\'t remember it, please contact support at <a href="mailto:senorwooly@yahoo.com">senorwooly@yahoo.com</a>.';
						}
					}else{
						$error_text = 'Your username and password were validated, but your account could not be copied over to the new user database.  Please contact support at <a href="mailto:senorwooly@yahoo.com">senorwooly@yahoo.com</a>.';
					}
				}else{
					$error_text = 'Your username and password did not match any existing account.';
				}
			}
		}
	}
	
	if(empty($_POST) || $error_text != ''){
		echo
		'<h2>Log in to convert your password</h2>',
		'<form method="post" action="password-conversion.php">',
			'<label for="username">Username:</label><input type="text" id="username" name="username"/><br/>',
			'<label for="password">Password:</label><input type="password" id="password" name="password"/><br/>',
			'<input type="submit"/>',
		'</form>',
		'<span class="error">',$error_text,'</span>';
	}
?>
<style>
	*{
		font-family:'Courier New';
		font-size:12px;
	}
	.error{
		color:red;
	}
</style>