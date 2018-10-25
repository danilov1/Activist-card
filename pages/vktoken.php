<?php
	access();
	//if($GLOBALS['config']['vk_state'] == 0) { exit; }

	if(!$_GET['code']) { renderSocialHandle("vktoken_error"); }

	$params = array(
        'client_id' => $GLOBALS['config']['vk_id'],
        'client_secret' => $GLOBALS['config']['vk_secret'],
        'code' => $_GET['code'],
        'redirect_uri' => urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/vktoken')
    );

	$pretoken = file_get_contents('https://oauth.vk.com/access_token?' . urldecode(http_build_query($params)));
	$token = json_decode($pretoken, true);

	if (!$token['access_token']) { renderSocialHandle("vktoken_error"); }

	$prevkauth = mysql_query("SELECT `id`,`vkauth`,`vktoken`,`type` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$vkauth = mysql_fetch_row($prevkauth);
	if($vkauth[1] !== NULL) { renderSocialHandle("vktoken_already"); } // профиль уже привязана к какому-то аккаунту ВКонтакте

	$_prevkauth = mysql_query("SELECT `id`,`vkauth` from `users` WHERE `vkauth`='".$token['user_id']."' LIMIT 1");
	$_vkauth = mysql_fetch_row($_prevkauth);
	if($_vkauth[0]) { renderSocialHandle("vktoken_anotheruser"); } // аккаунт уже привязан к другому профилю

	$vkauthreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `vkauth` = '".$token['user_id']."',
	`vktoken` = '".$token['access_token']."' WHERE `users`.`id` =".LOGGED_ID.";"; // запись ключа
	if(!mysql_query($vkauthreq)) { renderSocialHandle("vktoken_error"); }

	renderSocialHandle("ok");
?>
