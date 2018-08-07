<?php
	access_check();
	if($GLOBALS['config']['vk_state'] == 0) { exit; }
	if(LOGGEDIN == "YES") { renderSocialHandle("ok"); }
	
	if(!$_GET['code']) { header("HTTP/1.0 404 Not Found"); die(); }

	$params = array(
        'client_id' => $GLOBALS['config']['vk_id'],
        'client_secret' => $GLOBALS['config']['vk_secret'],
        'code' => $_GET['code'],
        'redirect_uri' => urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/vkauth')
    );
    
	$pretoken = file_get_contents('https://oauth.vk.com/access_token?' . urldecode(http_build_query($params)));
	$token = json_decode($pretoken, true);
	
	if(!$token['access_token']) { renderSocialHandle("vktoken_error"); }
	
	$prevkauth = mysql_query("SELECT `id`,`vkauth`,`vktoken`,`access` from `users` WHERE `vkauth`='".$token['user_id']."' AND `access` ='y' LIMIT 1"); // AND `type`='a'
	$vkauth = mysql_fetch_row($prevkauth);
	if(!$vkauth[0]) { renderSocialHandle("vktoken_noid"); } // не привязан
	
	$vkcurip = get_client_ip();
	if($vkcurip == "UNKNOWN") { renderSocialHandle("vktoken_error"); }
	
	$vkauthreq = "UPDATE `".$GLOBALS['config']['mysql_db']."`.`users` SET `vktoken` = '".$token['access_token']."' WHERE `users`.`id` =".$vkauth[0].";"; // обновление ключа
	if(!mysql_query($vkauthreq)) { renderSocialHandle("vktoken_error"); }
	
	// Формирование токена
	$pretoken1 = uniqid('auth1',true).$vkauth[0];
	$pretoken1 = md5(md5($pretoken1));
	$pretoken2 = uniqid('auth2',true).$vkauth[2];
	$pretoken2 = md5(md5($pretoken2));
	$newtoken = substr($pretoken2,16).substr($pretoken1,16).substr($pretoken2,0,16).substr($pretoken1,0,16);
	$newtime = time()+3600*24*30;
	$fromunix = date("Y-m-d H:i:s", $newtime);
	$currenttime = date("Y-m-d H:i:s", time());
	// Запись токена
	$findtokens = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`tokens` WHERE `tokens`.`deadline`<='".$currenttime."';";
	if(!mysql_query($findtokens)) { errorjson("Ошибка базы данных. Повторите попытку позже. #1"); }
	$authreq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`tokens` (`token`, `lastip`, `deadline`, `user`) VALUES ('".$newtoken."', '".$vkcurip."', '".$fromunix."', '".$vkauth[0]."')";
	if(!mysql_query($authreq)) { renderSocialHandle("vktoken_error"); }
	setcookie("a",$newtoken,$newtime,"/","",false,true);
	
	renderSocialHandle("ok");
	
	
	
	/*
	$params = array(
		'user_ids'         => $token['user_id'],
		'fields'       => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
		'access_token' => $token['access_token']
	);
	
	$preuserInfo = file_get_contents('https://api.vk.com/method/users.get' . '?' . urldecode(http_build_query($params)));
	$userInfo = json_decode($preuserInfo, true);

	if (isset($userInfo['response'][0]['uid'])) {
		$userInfo = $userInfo['response'][0];
		$result = true;
	}

    if ($result) {
        echo "Социальный ID пользователя: " . $userInfo['uid'] . '<br />';
        echo "Имя пользователя: " . $userInfo['first_name'] . '<br />';
        echo "Ссылка на профиль пользователя: " . $userInfo['screen_name'] . '<br />';
        echo "Пол пользователя: " . $userInfo['sex'] . '<br />';
        echo "День Рождения: " . $userInfo['bdate'] . '<br />';
        echo '<img src="' . $userInfo['photo_big'] . '" />'; echo "<br />";
	}
	*/
?>