<pre>
<?
	$test = array('key' => array(
		'stuff1' => 'stuff 1',
		'stuff2' => 'stuff 2',
		'stuff3' => 'stuff 3',
		'stuff4' => 'stuff 4',
		'stuff5' => 'stuff 5',
		'stuff6' => 'stuff 6',
		'stuff7' => 'stuff 7',
		'stuff8' => 'stuff 8',
		'stuff9' => 'stuff 9',
		'stuff10' => 'stuff 10',
		'stuff11' => 'stuff 11',
		'stuff12' => 'stuff 12',
	));
	
	function doStuff(&$var){
		$var += $var -= $var .= $var;
	}
	
	$iters = 1000000;
	
	$i = $iters;
	$start = microtime(true);
	while($i>-1){
		foreach($test['key'] as $component){
			doStuff($component);
		}
		--$i;
	}
	$end = microtime(true);
	$time1 = $end - $start;
	
	$i = $iters;
	$start = microtime(true);
	$key = 'key';
	while($i>-1){
		foreach($test[$key] as $component){
			doStuff($component);
		}
		--$i;
	}
	$end = microtime(true);
	$time2 = $end - $start;
	
	$i = $iters;
	$start = microtime(true);
	define('KEY','key');
	while($i>-1){
		foreach($test[KEY] as $component){
			doStuff($component);
		}
		--$i;
	}
	$end = microtime(true);
	$time3 = $end - $start;
	
	echo'Anonymous String Index: ',$time1,"\n",
		'Stored String Index: ',$time2,"\n",
		'Defined Global Index: ',$time3;
?>
</pre>