<?php
access();

if(!$_POST['act']) { wrongusing(); }

// Получение списка групп
if($_POST['act'] == "lists") {
	accessto("a,t,k,s");

	if(($_POST['page'] == "") or (!is_numeric($_POST['page']))) { wrongusing(); }

	$maxrows = 10;
	$pagenum = ($_POST['page'])*$maxrows;

	$sendlist = array();

	$presearch = preg_replace("|[\s]+|", " ", $_POST['query']);
	$presearch = trim($presearch);

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	if(LOGGED_ACCESS == "a") { $addcond = "`rights` LIKE '%_sva_%' OR `content` LIKE '%[\"".LOGGED_ID."\",\"%'"; }
	else { $addcond = "`rights` LIKE '%_uva_%' OR `rights` LIKE '%_uvd(".$getdep[0].")_%' OR `rights` LIKE '%_uvs(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'"; }

	$addsearch = "`name` LIKE '%".$presearch."%'";
	$pregetlist = mysql_query("SELECT `id`,`name`,`rights`,`public`,`icon`,`content`,`holder` from `lists` WHERE ".$addsearch." AND (".$addcond.") ORDER BY LENGTH(`icon`) DESC,`id` DESC LIMIT ".$pagenum.", ".$maxrows.";");
	$fornum = mysql_query("SELECT `id`,`name`,`rights`,`public`,`icon`,`content`,`holder` from `lists` WHERE ".$addsearch." AND (".$addcond.") ORDER BY LENGTH(`icon`) DESC,`id` DESC;");

	$getnum = mysql_num_rows($fornum);
	if($getnum == 0) { errorjson("notfound"); }

	while($lists = mysql_fetch_array($pregetlist)) {
		$involved = json_decode($lists[5]);
		$isedit = "n";
		if($lists[6] == LOGGED_ID) { $isedit = "y"; }
		$newlist = array(
			"l_id" => $lists[0],
			"l_name" => $lists[1],
			"l_icon" => $lists[4],
			"l_involved" => count($involved),
			"l_edit" => $isedit
		);
		$sendlist["lists"][] = $newlist;
		unset($newlist);
	}

	if(LOGGED_ACCESS == "a") {
		$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
		$resscores = mysql_fetch_row($prescores);

		$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
		$getrank = mysql_fetch_row($pregetrank);

		$sendlist["scores"] = $resscores[1];
		$sendlist["current"] = $getrank[0];
		if($sendlist["scores"] == "0") { $sendlist["current"] = "&#8734;"; }

		$sendlist["addme_alerts"] = addme_alerts();
	}

	$sendlist["allrows"] = $getnum;
	$sendlist["maxrows"] = $maxrows;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}
else if($_POST['act'] == "addlist" or $_POST['act'] == "editlist") {
	accessto("t,k,s");
	if(!is([$_POST['name'],$_POST['ue'],$_POST['uv'],$_POST['sv']])) { errorjson("Заполните все поля"); };
	if(!same($_POST['ue'],["r1","r2","r3","r4"]) or !same($_POST['uv'],["r1","r2","r3","r4"]) or !same($_POST['sv'],["r1","r2","r3"])) { wrongusing(); };

	if($_POST['act'] == "editlist") {
		if(!is([$_POST['lid']],$_POST['newicon'])) { wrongusing(); }
		if(!same($_POST['newicon'],["n","y"])) { wrongusing(); }
		$prechecklist = mysql_query("SELECT `id`,`holder`,`icon` from `lists` WHERE `id`='".$_POST['lid']."' AND `holder`='".LOGGED_ID."' LIMIT 1;");
		$checklist = mysql_fetch_row($prechecklist);
		if(!$checklist[0]) { errorjson("Группа не найдена или доступ к ней ограничен настройки."); }
	}

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$list_rights = "";

	if($_POST['uv'] == "r2") { $list_rights .= "_uva_"; }
	if($_POST['uv'] == "r3") { $list_rights .= "_uvd(".$getdep[0].")_"; }

	if($_POST['ue'] == "r2") { $list_rights .= "_uea_"; }
	if($_POST['ue'] == "r3") { $list_rights .= "_ued(".$getdep[0].")_"; }

	if(($_POST['uv'] == "r3") or ($_POST['uv'] == "r4")) {
		if(!is([$_POST['auv']])) { wrongusing(); }
		$auv = json_decode(stripslashes($_POST['auv']));
		if((gettype == "array") and !$auv[0]) {}
		else {
			for($i=0; $i<count($auv); $i++) {
				if(!is_numeric($auv[$i])) { wrongusing(); }
				$precheck = mysql_query("SELECT `id`,`type`,`fullname` from `users` WHERE `id`='".$auv[$i]."' AND `type` !='d' LIMIT 1");
				$check = mysql_fetch_row($precheck);
				if(!$check[0]) { wrongusing(); }
				$list_rights .= "_uvs(".$auv[$i].")_";
			}
		}
	}

	if(($_POST['ue'] == "r3") or ($_POST['ue'] == "r4")) {
		if(!is([$_POST['aue']])) { wrongusing(); }
		$aue = json_decode(stripslashes($_POST['aue']));
		if((gettype == "array") and !$aue[0]) {}
		else {
			for($i=0; $i<count($aue); $i++) {
				if(!is_numeric($aue[$i])) { wrongusing(); }
				$precheck = mysql_query("SELECT `id`,`type`,`fullname` from `users` WHERE `id`='".$aue[$i]."' AND `type` !='d' LIMIT 1");
				$check = mysql_fetch_row($precheck);
				if(!$check[0]) { wrongusing(); }
				$list_rights .= "_ues(".$aue[$i].")_";
			}
		}
	}

	if($_POST['sv'] == "r1") { $list_rights .= "_sva_"; }
	if($_POST['sv'] == "r2") { $list_rights .= "_svl_"; }

	$list_icon = "n";
	$icon_type = "";
	if(isset($_FILES['icon'])) {
		uploaderror($_FILES['icon']);
		if($_FILES['icon']['type'] == 'image/svg+xml') {
			$icon_type = "svg";
		} elseif($_FILES['icon']['type'] == 'image/png') {
			$icon_type = "png";
			list($icon_width, $icon_height) = getimagesize($_FILES['icon']['tmp_name']);
			$icon_size = $icon_width - $icon_height;
			if($icon_size < 0) { $icon_size = $icon_size * (-1); }
			if($icon_size > 2 or $icon_width < 127) { errorjson("Значок группы в формате .PNG должен быть одинакового размера по высоте и ширине и равен 127 пикс. или больше."); }
		} else {
			wrongusing();
		}

		$list_icon = md5(uniqid('icon',true).$_POST['lid']).".".$icon_type;
		chmod("content/svg/",0777);
		chmod("content/img/",0777);
	}

	if($_POST['act'] == "addlist") {
		$addlistreq = "INSERT INTO `".$GLOBALS['config_db']['mysql_db']."`.`lists` (`id`, `name`, `rights`, `public`, `icon`, `content`, `holder`) VALUES (NULL, '".htmlentities($_POST['name'], ENT_QUOTES, "UTF-8")."', '".$list_rights."', 'n', '".$list_icon."', '[]', '".LOGGED_ID."');";
	} else {
		if($_POST['newicon'] == "n") { $list_icon = $checklist[2]; }
		$addlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `name` = '".htmlentities($_POST['name'], ENT_QUOTES, "UTF-8")."', `rights` = '".$list_rights."', `icon` = '".$list_icon."' WHERE `lists`.`id` = '".$_POST['lid']."';";
		if($icon_type == "svg") { $uploadfile = "content/svg/".$list_icon; }
		if($icon_type == "png") { $uploadfile = "content/img/".$list_icon; }
	}

	if(!mysql_query($addlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	if($_POST['act'] == "addlist") {
		if($icon_type == "svg") { $uploadfile = "content/svg/".$list_icon; }
		if($icon_type == "png") { $uploadfile = "content/img/".$list_icon; }
	}
	if(isset($_FILES['icon'])) {
		if(!move_uploaded_file($_FILES['icon']['tmp_name'], $uploadfile)) { errorjson("Ошибка записи файла."); };
		chmod($uploadfile,0777);
	}
	if($_POST['act'] == "editlist" and $checklist[2] !== "n" and $_POST['newicon'] == "y") {
		$type_unlink = explode('.',$checklist[2]);
		if($type_unlink[1] == "svg") { unlink("content/svg/".$checklist[2]); }
		else { unlink("content/img/".$checklist[2]); }
	}
	errorjson("ok");
}
else if($_POST['act'] == "listpublicicon") {
	accessto("s");
	if(!is([$_POST['lid'],$_POST['public']]) or ($_POST['public'] !== "y" and $_POST['public'] !== "n")) { wrongusing(); }
	$prechecklist = mysql_query("SELECT `id`,`public`,`icon` from `lists` WHERE `id`='".$_POST['lid']."' AND `icon`!='n' LIMIT 1;");
	$checklist = mysql_fetch_row($prechecklist);
	if(!$checklist[0]) { errorjson("Группа не найдена или доступ к ней ограничен настройки."); }
	if($_POST['public'] == $checklist[1]) {
		$sendlist = array(
			"error" => "ok",
			"public" => $checklist[1]
		);
		exit(json_encode($sendlist));
	}
	$listpubliciconreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `public` = '".$_POST['public']."' WHERE `lists`.`id` = '".$_POST['lid']."';";
	if(!mysql_query($listpubliciconreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	$sendlist = array(
		"error" => "ok",
		"public" => $_POST['public']
	);
	exit(json_encode($sendlist));
}
else if($_POST['act'] == "getlistdata") {
	accessto("t,k,s");
	if(!is([$_POST['lid']])) { wrongusing(); }
	$pregetlist = mysql_query("SELECT `id`,`name`,`rights`,`public`,`icon`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND `holder`='".LOGGED_ID."' LIMIT 1;");
	$list = mysql_fetch_row($pregetlist);
	if(!$list[0]) { errorjson("Группа не найдена или доступ к ней ограничен настройки."); }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	/* минимальная установка */
	$list_ue ="r1";
	$list_uv ="r1";
	$list_sv ="r3";
	$list_aue = array();
	$list_auv = array();

	/* ue - r2 */
	$pos = mb_strpos($list[2], "_uea_", 0, "UTF-8");
	if ($pos !== false) { $list_ue = "r2"; }
	/* ue - r3 */
	$pos = mb_strpos($list[2], "_ued(".$getdep[0].")_", 0, "UTF-8");
	if ($pos !== false) { $list_ue = "r3"; }
	/* uv - r2 */
	$pos = mb_strpos($list[2], "_uva_", 0, "UTF-8");
	if ($pos !== false) { $list_uv = "r2"; }
	/* uv - r3 */
	$pos = mb_strpos($list[2], "_uvd(".$getdep[0].")_", 0, "UTF-8");
	if ($pos !== false) { $list_uv = "r3"; }
	/* sv - r1 */
	$pos = mb_strpos($list[2], "_sva_", 0, "UTF-8");
	if ($pos !== false) { $list_sv = "r1"; }
	/* sv - r2 */
	$pos = mb_strpos($list[2], "_svl_", 0, "UTF-8");
	if ($pos !== false) { $list_sv = "r2"; }

	/* доступ для отдельных лиц (ue) */
	preg_match_all('/_ues[(](.*?)[)]_/',$list[2], $people);
	if(count($people[1]) !== 0) {
		if($list_ue == "r1") { $list_ue = "r4"; }
		for($i=0; $i<count($people[1]); $i++) {
			$preperson = mysql_query("SELECT `id`,`fullname` from `users` WHERE `id`='".$people[1][$i]."' LIMIT 1;");
			$person = mysql_fetch_row($preperson);
			$list_aue[] = array($person[0],$person[1]);
		}
	}

	/* доступ для отдельных лиц (uv) */
	preg_match_all('/_uvs[(](.*?)[)]_/',$list[2], $people);
	if(count($people[1]) !== 0) {
		if($list_uv == "r1") { $list_uv = "r4"; }
		for($i=0; $i<count($people[1]); $i++) {
			$preperson = mysql_query("SELECT `id`,`fullname` from `users` WHERE `id`='".$people[1][$i]."' LIMIT 1;");
			$person = mysql_fetch_row($preperson);
			$list_auv[] = array($person[0],$person[1]);
		}
	}

	$newlist = array(
		"l_id" => $list[0],
		"l_name" => $list[1],
		"l_icon" => $list[4],
		"l_ue" => $list_ue,
		"l_uv" => $list_uv,
		"l_sv" => $list_sv,
		"l_aue" => $list_aue,
		"l_auv" => $list_auv,
	);
	$sendlist["listdata"][] = $newlist;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

else if($_POST['act'] == "dellist") {
	accessto("t,k,s");
	if(!is([$_POST['lid']])) { wrongusing(); }
	$pregetlist = mysql_query("SELECT `id`,`rights`,`content`,`holder`,`icon` from `lists` WHERE `id`='".$_POST['lid']."' AND `holder`='".LOGGED_ID."' LIMIT 1;");
	$list = mysql_fetch_row($pregetlist);
	if(!$list[0]) { errorjson("Группа не найдена или доступ к ней ограничен настройки."); }

	$list_members = json_decode($list[2]);
	for($i=0; $i<count($list_members); $i++) {
		$pregetown = mysql_query("SELECT `id`,`groups` from `users` WHERE `id`='".$list_members[$i][0]."' LIMIT 1;");
		$getown = mysql_fetch_row($pregetown);
		if(!$getown[0]) { continue; }
		$ownlist = json_decode($getown[1]);
		$pos = array_search($_POST['lid'], $ownlist);
		if($pos === false) { continue; }
		unset($ownlist[$pos]);
		$newownlist = array_values($ownlist);
		$newownlist = json_encode($newownlist);
		$newownlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `groups` = '".$newownlist."' WHERE `users`.`id` = '".$list_members[$i][0]."';";
		if(!mysql_query($newownlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	}

	$dellistreq = "DELETE FROM `".$GLOBALS['config_db']['mysql_db']."`.`lists` WHERE `lists`.`id` = ".$_POST['lid'].";";
	if(!mysql_query($dellistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$uploadfile = "content/svg/".$list[4];
	unlink($uploadfile);

	errorjson("ok");
}


// Получение списка участников группы
else if($_POST['act'] == "getlist") {
	accessto("a,t,k,s");
	if(!is([$_POST['lid']])) { wrongusing(); }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	if(LOGGED_ACCESS == "a") { $addcond = "`rights` LIKE '%_sva_%' OR `content` LIKE '%[\"".LOGGED_ID."\"%'"; }
	else { $addcond = "`rights` LIKE '%_uva_%' OR `rights` LIKE '%_uvd(".$getdep[0].")_%' OR `rights` LIKE '%_uvs(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'"; }

	$prelcheck = mysql_query("SELECT `id`,`name`,`rights`,`public`,`icon`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
	$list = mysql_fetch_row($prelcheck);
	if(!$list[0]) { exit("Данная группа недоступна"); }

	$sendrights = array();
	if(LOGGED_ACCESS !== "a") {
		$addcond = "`rights` LIKE '%_uea_%' OR `rights` LIKE '%_ued(".$getdep[0].")_%' OR `rights` LIKE '%_ues(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'";
		$prelcheck = mysql_query("SELECT `id`,`name`,`rights`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
		$rights = mysql_fetch_row($prelcheck);
		if(!$rights[0]) { $isedit = "n"; }
		else {
			$isedit = "y";
			/* ОТРЫВОК ИЗ ФАЙЛА ОПЕРАТОРА */
			/* минимальная установка */
			$list_ue ="r1";
			$list_uv ="r1";
			$list_sv ="r3";
			$list_aue = array();
			$list_auv = array();

			/* ue - r2 */
			$pos = mb_strpos($list[2], "_uea_", 0, "UTF-8");
			if ($pos !== false) { $list_ue = "r2"; }
			/* ue - r3 */
			$pos = mb_strpos($list[2], "_ued(".$getdep[0].")_", 0, "UTF-8");
			if ($pos !== false) { $list_ue = "r3"; }
			/* uv - r2 */
			$pos = mb_strpos($list[2], "_uva_", 0, "UTF-8");
			if ($pos !== false) { $list_uv = "r2"; }
			/* uv - r3 */
			$pos = mb_strpos($list[2], "_uvd(".$getdep[0].")_", 0, "UTF-8");
			if ($pos !== false) { $list_uv = "r3"; }
			/* sv - r1 */
			$pos = mb_strpos($list[2], "_sva_", 0, "UTF-8");
			if ($pos !== false) { $list_sv = "r1"; }
			/* sv - r2 */
			$pos = mb_strpos($list[2], "_svl_", 0, "UTF-8");
			if ($pos !== false) { $list_sv = "r2"; }

			/* доступ для отдельных лиц (ue) */
			preg_match_all('/_ues[(](.*?)[)]_/',$list[2], $people);
			if(count($people[1]) !== 0) {
				if($list_ue == "r1") { $list_ue = "r4"; }
				for($i=0; $i<count($people[1]); $i++) {
					$preperson = mysql_query("SELECT `id`,`fullname` from `users` WHERE `id`='".$people[1][$i]."' LIMIT 1;");
					$person = mysql_fetch_row($preperson);
					$list_aue[] = array($person[0],$person[1]);
				}
			}

			/* доступ для отдельных лиц (uv) */
			preg_match_all('/_uvs[(](.*?)[)]_/',$list[2], $people);
			if(count($people[1]) !== 0) {
				if($list_uv == "r1") { $list_uv = "r4"; }
				for($i=0; $i<count($people[1]); $i++) {
					$preperson = mysql_query("SELECT `id`,`fullname` from `users` WHERE `id`='".$people[1][$i]."' LIMIT 1;");
					$person = mysql_fetch_row($preperson);
					$list_auv[] = array($person[0],$person[1]);
				}
			}
			/* ЗАВЕРШЕН ОТРЫВОК ФАЙЛА ОПЕРАТОРА */
			$sendrights = array($list_ue,$list_uv,$list_sv,$list_aue,$list_auv);
		}
	}

	/* Расшифровка списка участников */
	$memberlist = json_decode($list[5]);
	$members = array();
	for($i=0; $i<count($memberlist); $i++) {
		$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum` from `users` WHERE `id` ='".$memberlist[$i][0]."' LIMIT 1;");
		$getuser = mysql_fetch_row($pregetuser);

		$newpname = "";
		if(mb_strlen($getuser[3], "UTF-8")<2) { $newpname = ""; } else { $newpname = " ".$getuser[3]; }
		$name = $getuser[1]." ".$getuser[2]."".$newpname;

		$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
		$getdep = mysql_fetch_row($pregetdep);
		$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
		$getfac = mysql_fetch_row($pregetfac);

		list($edul, $educ) = split('[-]', $getuser[5]);
		if($edul == "m") { $educ .= "м"; }

		if(LOGGED_ACCESS == "a") { $afrom = "".$getfac[1]." (".$educ." курс)"; }
		else { $afrom = $getfac[1]."(".$getdep[2].")-".$getuser[6]; }

		$members[] = array($memberlist[$i][0], $name, $afrom, $memberlist[$i][1]);
	}

	if(LOGGED_ACCESS == "a") {
		$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
		$resscores = mysql_fetch_row($prescores);

		$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
		$getrank = mysql_fetch_row($pregetrank);

		$sendlist["scores"] = $resscores[1];
		$sendlist["current"] = $getrank[0];
		if($sendlist["scores"] == "0") { $sendlist["current"] = "&#8734;"; }

		$sendlist["addme_alerts"] = addme_alerts();
	}

	if($list[4] !== "n" and LOGGED_ACCESS == "s") { $sendlist["isediticon"] = 1; }

	$sendlist["name"] = $list[1];
	$sendlist["icon"] = $list[4];
	$sendlist["public"] = $list[3];
	$sendlist["rights"] = $sendrights;
	$sendlist["isedit"] = $isedit;
	$sendlist["members"] = $members;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
} else if($_POST['act'] == "addinlist") {
	accessto("t,k,s");
	if(!is([$_POST['lid'],$_POST['hid']])) { wrongusing(); }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$addcond = "`rights` LIKE '%_uea_%' OR `rights` LIKE '%_ued(".$getdep[0].")_%' OR `rights` LIKE '%_ues(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'";
	$prelcheck = mysql_query("SELECT `id`,`rights`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
	$list = mysql_fetch_row($prelcheck);
	if(!$list[0]) { errorjson("Студент не добавлен. Ошибка доступа."); }

	$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum`,`groups` from `users` WHERE `id`='".$_POST['hid']."' LIMIT 1");
	$getuser = mysql_fetch_row($pregetuser);
	if(!$getuser[0]) { errorjson("Выбранный студент не найден."); }

	$membersjson = json_decode($list[2]);

	for($i=0; $i<count($membersjson); $i++) {
		if($membersjson[$i][1] == $_POST['hid']) { errorjson("Выбранный студент уже добавлен"); }
	}

	array_unshift($membersjson, array(''.$_POST['hid'].'',''));
	$injson = addslashes(json_encode($membersjson));

	$addinlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `content` = '".$injson."' WHERE `lists`.`id` = '".$_POST['lid']."';";
	if(!mysql_query($addinlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$ownlist = json_decode($getuser[7]);
	$ownlist[] = $_POST['lid'];
	$newownlist = json_encode($ownlist);
	$newownlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `groups` = '".$newownlist."' WHERE `users`.`id` = '".$_POST['hid']."';";
	if(!mysql_query($newownlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$newpname = "";
	if(mb_strlen($getuser[3], "UTF-8")<2) { $newpname = ""; } else { $newpname = " ".$getuser[3]; }
	$name = $getuser[1]." ".$getuser[2]."".$newpname;

	$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
	$getfac = mysql_fetch_row($pregetfac);

	list($edul, $educ) = split('[-]', $getuser[5]);
	if($edul == "m") { $educ .= "м"; }

	$afrom = $getfac[1]."(".$getdep[2].")-".$getuser[6];

	$sendlist["id"] = $getuser[0];
	$sendlist["name"] = $name;
	$sendlist["afrom"] = $afrom;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
} else if($_POST['act'] == "sortlist") {
	accessto("t,k,s");
	if(!is([$_POST['lid'],$_POST['order']])) { wrongusing(); }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$addcond = "`rights` LIKE '%_uea_%' OR `rights` LIKE '%_ued(".$getdep[0].")_%' OR `rights` LIKE '%_ues(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'";
	$prelcheck = mysql_query("SELECT `id`,`rights`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
	$list = mysql_fetch_row($prelcheck);
	if(!$list[0]) { errorjson("Новый порядок не применен. Ошибка доступа."); }

	$getoldorder = json_decode($list[2]);
	$oldorder = array();
	for($i=0; $i<count($getoldorder); $i++) {
		$oldorder[] = $getoldorder[$i][0];
	}
	$getneworder = json_decode(stripslashes($_POST['order']));
	if(!$getneworder[0]) { wrongusing(); }
	$neworder = array();
	for($i=0; $i<count($getneworder); $i++) {
		if(!is_numeric($getneworder[$i])) { wrongusing(); }
		$pregetuser = mysql_query("SELECT `id`,`type`,`curcourse`,`groupnum` from `users` WHERE `id`='".$getneworder[$i]."' LIMIT 1;");
		$getuser = mysql_fetch_row($pregetuser);
		if((!$getuser[0]) or (array_search($getneworder[$i], $oldorder) === false)) { errorjson("Новый порядок не применен. Один из студентов не найден в списке группы."); }
		$neworder[] = array(''.$getneworder[$i].'',$getoldorder[(array_search($getneworder[$i], $oldorder))][1]);
	}
	$injson = addslashes(json_encode($neworder));
	$sortlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `content` = '".$injson."' WHERE `lists`.`id` = '".$_POST['lid']."';";
	if(!mysql_query($sortlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
} else if($_POST['act'] == "delfromlist") {
	if(!is([$_POST['lid'],$_POST['hid']])) { wrongusing(); }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$addcond = "`rights` LIKE '%_uea_%' OR `rights` LIKE '%_ued(".$getdep[0].")_%' OR `rights` LIKE '%_ues(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'";
	$prelcheck = mysql_query("SELECT `id`,`rights`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
	$list = mysql_fetch_row($prelcheck);
	if(!$list[0]) { errorjson("Студент не удален. Ошибка доступа."); }

	$list_members = json_decode($list[2]);
	$members = array();
	for($i=0; $i<count($list_members); $i++) {
		$members[] = $list_members[$i][0];
	}

	$pos = array_search($_POST['hid'], $members);
	if($pos === false) { errorjson("Студент не удален. Ошибка доступа."); }
	unset($list_members[$pos]);
	$neworder = array_values($list_members);
	$injson = addslashes(json_encode($neworder));
	$sortlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `content` = '".$injson."' WHERE `lists`.`id` = '".$_POST['lid']."';";
	if(!mysql_query($sortlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$pregetown = mysql_query("SELECT `id`,`groups` from `users` WHERE `id`='".$_POST['hid']."' LIMIT 1;");
	$getown = mysql_fetch_row($pregetown);

	$ownlist = json_decode($getown[1]);
	$pos = array_search($_POST['lid'], $ownlist);
	unset($ownlist[$pos]);
	$newownlist = array_values($ownlist);
	$newownlist = json_encode($newownlist);
	$newownlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `groups` = '".$newownlist."' WHERE `users`.`id` = '".$_POST['hid']."';";
	if(!mysql_query($newownlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	errorjson("ok");
} else if($_POST['act'] == "editinlist") {
	if(!is([$_POST['lid'],$_POST['hid']])) { wrongusing(); }

	$setcontent = "";
	if(is([$_POST['content']])) { $setcontent = $_POST['content']; }

	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$addcond = "`rights` LIKE '%_uea_%' OR `rights` LIKE '%_ued(".$getdep[0].")_%' OR `rights` LIKE '%_ues(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'";
	$prelcheck = mysql_query("SELECT `id`,`rights`,`content`,`holder` from `lists` WHERE `id`='".$_POST['lid']."' AND (".$addcond.") LIMIT 1;");
	$list = mysql_fetch_row($prelcheck);
	if(!$list[0]) { errorjson("Изменения не внесены. Ошибка доступа."); }

	$list_members = json_decode($list[2]);
	$members = array();
	for($i=0; $i<count($list_members); $i++) {
		$members[] = $list_members[$i][0];
	}

	$pos = array_search($_POST['hid'], $members);
	if($pos === false) { errorjson("Изменения не внесены. Студент в списке не найден."); }
	$list_members[$pos][1] = htmlentities(stripslashes($setcontent), ENT_QUOTES, 'UTF-8');
	$injson = addslashes(json_encode($list_members));
	$sortlistreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`lists` SET `content` = '".$injson."' WHERE `lists`.`id` = '".$_POST['lid']."';";
	if(!mysql_query($sortlistreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	errorjson("ok");
}
// Отвязка аккаунта ВКонтакте
else if($_POST['act']  == "vkunlink") {
	accessto("a,t,k,s");
	$vkauthreq = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `vkauth` = NULL,
	`vktoken` = NULL WHERE `users`.`id` =".LOGGED_ID.";"; // удаление ключа
	if(!mysql_query($vkauthreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
}

// Сохранение основной информации об организации
elseif($_POST['act'] == "setorginfo") {
	accessto("s");
	if(!is([$_POST['organization_form'],$_POST['organization_fullname'],$_POST['organization_shortname'],$_POST['organization_department']])) { wrongusing(); }
	$GLOBALS['config']['organization_form'] = $_POST['organization_form'];
	$GLOBALS['config']['organization_fullname'] = $_POST['organization_fullname'];
	$GLOBALS['config']['organization_shortname'] = $_POST['organization_shortname'];
	$GLOBALS['config']['organization_department'] = $_POST['organization_department'];

	if(isset($_FILES['organization_logo'])) {
		uploaderror($_FILES['organization_logo']);
		if($_FILES['organization_logo']['type'] == 'image/svg+xml') {
			$organization_logo_way = "img/org_logo.svg";
		} elseif($_FILES['organization_logo']['type'] == 'image/png') {
			$organization_logo_way = "img/org_logo.png";
			list($logo_width, $logo_height) = getimagesize($_FILES['organization_logo']['tmp_name']);
			$logo_size = $logo_width - $logo_height;
			if($logo_size < 0) { $logo_size = $logo_size * (-1); }
			if($logo_size > 2 or $logo_width < 50) { errorjson("Логотип в формате .PNG должен быть одинакового размера по высоте и ширине и равен 50 пикс. или больше."); }

		} else {
			errorjson("Неверный формат файла логотипа.");
		}
		if(file_exists("img/org_logo.svg")) { unlink("img/org_logo.svg"); }
		if(file_exists("img/org_logo.png")) { unlink("img/org_logo.png"); }
		if(!move_uploaded_file($_FILES['organization_logo']['tmp_name'], $organization_logo_way)) { errorjson("Ошибка записи файла логотипа."); };
		$GLOBALS['config']['organization_logo'] = $organization_logo_way."?".uniqid()."";
	}

	if(isset($_FILES['organization_favicon'])) {
		uploaderror($_FILES['organization_favicon']);
		if($_FILES['organization_favicon']['type'] == 'image/x-icon' or $_FILES['organization_favicon']['type'] == 'image/vnd.microsoft.icon') {
			$organization_favicon_way = "img/favicon.ico";
		} elseif($_FILES['organization_favicon']['type'] == 'image/png' ) {
			$organization_favicon_way = "img/favicon.png";
		} else {
			errorjson("Неверный формат файла favicon.");
		}
		list($icon_width, $icon_height) = getimagesize($_FILES['organization_favicon']['tmp_name']);
		$icon_size = $icon_width - $icon_height;
		if($icon_size < 0) { $icon_size = $icon_size * (-1); }
		if($icon_size > 2 or $icon_width < 32) { errorjson("Favicon должна быть одинакового размера по высоте и ширине и равна 32 пикс. или больше."); }
		if(file_exists("img/favicon.ico")) { unlink("img/favicon.ico"); }
		if(file_exists("img/favicon.png")) { unlink("img/favicon.png"); }
		if(!move_uploaded_file($_FILES['organization_favicon']['tmp_name'], $organization_favicon_way)) { errorjson("Ошибка записи файла favicon."); };
		$GLOBALS['config']['organization_favicon'] = $organization_favicon_way."?".uniqid()."";
	}

	// Запись конфигурации
	config_save();
	errorjson("ok");
}

// Загрузка списка студента
elseif($_POST['act'] == "studentsupload") {
	if(!isset($_FILES['file'])) { wrongusing(); }
	if($_FILES['file']['size'] > 3145728) { errorjson("Размер файла слишком большой. Загрузите файл не более 3мб"); }

	$csvFile = file($_FILES['file']['tmp_name']);
    $data = [];
    foreach ($csvFile as $line) {
		if(mb_strlen($line, "UTF-8") > 1000) { errorjson("Строки в загружаемом файле слишком длинные"); }
        $data[] = str_getcsv($line,";");
    }
	$num = count($data);
	if($num < 2) { errorjson("Количество строк в таблице должно быть не менее 2"); }
	if($num > 5) { $num = 5; }
	$sendPreCSV = array();
	for($i=0; $i < $num; $i++) {
		if(count($data[$i]) > 18) { errorjson("Количество столбцов в таблице слишком большое"); }
		$sendPreCSV[$i] = array();
		for($c=0; $c < count($data[$i]); $c++) {
			$sendPreCSV[$i][$c] = iconv('windows-1251', 'UTF-8', $data[$i][$c]);
		}
	}

	$fileid = md5(uniqid("work",true).rand(0,9999999999));
	$filename = $fileid.".csv";
	$newFilePath = "content/".$filename;
	if(!move_uploaded_file($_FILES['file']['tmp_name'], $newFilePath)) { errorjson("Ошибка записи файла"); }

	if(isset($GLOBALS['config']['csvfile']) and file_exists("content/".$GLOBALS['config']['csvfile'])) {
		unlink("content/".$GLOBALS['config']['csvfile']);
	}
	$GLOBALS['config']['csvfile'] = $filename;
	config_save();

	$sendlist["CSVid"] = $fileid;
	$sendlist["preCSV"] = $sendPreCSV;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

else { wrongusing(); }













?>
