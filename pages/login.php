<?php
if(!$_POST['act']) { header("HTTP/1.0 404 Not Found"); die(); }
if($_POST['act'] == 'login') { gologin(); }
if($_POST['act'] == 'logout') {
	if($_COOKIE['a']) {
		$_logoutsql = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`tokens` WHERE `tokens`.`token`='".$_COOKIE['a']."';";
		if(!mysql_query($_logoutsql)) { errorjson("Не удалось завершить сессию на сервере."); }
		setcookie('a', null, -1, '/');
	}
	errorjson("ok");
}

if($_POST['act'] == 'access_token') {
	$curip = get_client_ip();
	if($curip == "UNKNOWN") { errorjson("Невозможно распознать IP-адрес клиента"); }
	if(!$_POST['l'] or !$_POST['p']) { wrongusing(); }
	if(!is_numeric($_POST['l']) or mb_strlen($_POST['p'],"UTF-8") !== 32) { wrongusing(); }
	$pregetaccess = mysql_query("SELECT `id`,`sin`,`password`,`type`,`access` from `users` WHERE `sin`='".$_POST['l']."' AND `access` ='y' and `type`!='a' LIMIT 1");
	$getaccess = mysql_fetch_row($pregetaccess);
	if(!$getaccess[0]) { errorjson("access_wrong"); }
	if($_POST['p'] !== $getaccess[2]) { errorjson("access_wrong"); }
	$pretoken1 = uniqid('auth1',true).$getaccess[0];
	$pretoken1 = md5(md5($pretoken1));
	$pretoken2 = uniqid('auth2',true).$getaccess[2];
	$pretoken2 = md5(md5($pretoken2));
	$newtoken = substr($pretoken2,16).substr($pretoken1,16).substr($pretoken2,0,16).substr($pretoken1,0,16);
	$newtime = time()+3600*24*20;
	$fromunix = date("Y-m-d H:i:s", $newtime);
	$currenttime = date("Y-m-d H:i:s", time());
	$findtokens = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`tokens` WHERE `tokens`.`user`='".$getaccess[0]."';";
	if(!mysql_query($findtokens)) { errorjson("Ошибка базы данных. Повторите попытку позже. #1"); }
	$authreq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`tokens` (`token`, `lastip`, `deadline`, `user`) VALUES ('".$newtoken."', '".$curip."', '".$fromunix."', '".$getaccess[0]."')";
	if(!mysql_query($authreq)) { errorjson("Ошибка базы данных. Повторите попытку позже. #2"); }
	exit(json_encode(array("access_token" => $newtoken,"expire" => $newtime)));
}
?>