<form action="drupal-password-hasher.php" method="post">
	<input type="text" id="password" name="password"/><br/>
	<input type="submit"/>
</form>
<?
	if($_POST['password']){
		define('DRUPAL_ROOT','/home/senorwoo/public_html/test/drupal');
		require('../drupal/includes/bootstrap.inc');
		
		drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
		require('../drupal/includes/password.inc');
		
		$user = user_load(1);
		echo user_check_password($_POST['password'],$user);
	}
?>