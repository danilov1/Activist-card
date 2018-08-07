<?php
access();

if(!$_GET['act']) { wrongusing(); }

// Получение списка возможных ответственных за мероприятие
if($_GET['act'] == "h") {
	accessto("s,k,t");
	if(!$_GET['term']) { wrongusing(); }
	if(strlen($_GET['term'])<2) { wrongusing(); }
	
	$sendlist = array();
	$getholders = mysql_query("SELECT `id`,`type`,`fullname` from `users` WHERE `type` !='a' AND `fullname` LIKE '%".$_GET['term']."%'  LIMIT 5;");
	while($holder = mysql_fetch_array($getholders)) {
		$newholder = array(
			"id" => $holder[0],
			"value" => $holder[2],
		);
		$sendlist[] = $newholder;
		unset($newholder);
	}
	exit(json_encode($sendlist));
}
elseif($_GET['act'] == "a") {
	accessto("s,k,t");
	if(!$_GET['term']) { wrongusing(); }
	if(strlen($_GET['term'])<2) { wrongusing(); }
	
	$sendlist = array();
	$getas = mysql_query("SELECT `id`,`type`,`fullname`,`dep` from `users` WHERE `type` ='a' AND `out` ='s' AND `fullname` LIKE '%".$_GET['term']."%'  LIMIT 5;");
	while($as = mysql_fetch_array($getas)) {
		$newas = array(
			"id" => $as[0],
			"value" => $as[2]
		);
		$sendlist[] = $newas;
		unset($newas);
	}
	exit(json_encode($sendlist));
}
elseif($_GET['act'] == "d") {
	accessto("s,k,t,a");
	if(!$_GET['term']) { wrongusing(); }
	if(strlen($_GET['term'])<2) { wrongusing(); }
	
	$sendlist = array();
	$getas = mysql_query("SELECT `id`,`type`,`name` from `deps` WHERE `type` ='d' AND `name` LIKE '%".$_GET['term']."%'  LIMIT 5;");
	while($as = mysql_fetch_array($getas)) {
		$newas = array(
			"id" => $as[0],
			"value" => $as[2]
		);
		$sendlist[] = $newas;
		unset($newas);
	}
	exit(json_encode($sendlist));
}
elseif($_GET['act'] == "e") {
	accessto("s,k");
	if(!$_GET['term']) { wrongusing(); }
	if(strlen($_GET['term'])<2) { wrongusing(); }
	
	$sendlist = array();
	$getas = mysql_query("SELECT `id`,`name` from `events` WHERE `name` LIKE '%".$_GET['term']."%'  LIMIT 5;");
	while($as = mysql_fetch_array($getas)) {
		$newas = array(
			"id" => $as[0],
			"value" => $as[1]
		);
		$sendlist[] = $newas;
		unset($newas);
	}
	exit(json_encode($sendlist));
}

else { wrongusing(); }
?>