<form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
	<input name="username" type="text"/><br/>
	<input name="password" type="password"/><br/>
	<button type="submit">Submit</button>
</form>

<?
	if(!empty($_POST)){
		define('DRUPAL_ROOT','/home/khameleo/www/drupal');
		require DRUPAL_ROOT.'/includes/bootstrap.inc';
		
		drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
		require DRUPAL_ROOT.'/includes/password.inc';
		
		$uid = db_query("select uid from users where name = '{$_POST['username']}';")->fetch();
		print_r($uid);
	
		echo user_check_password($_POST['password'],$user);
	}
?>
