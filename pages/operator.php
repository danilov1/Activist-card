<?php
access();

if(!$_GET['act']) { wrongusing(); }

// Получение списка мероприятий, в которых участвовал активист
if($_GET['act'] == "mylist") {
	accessto("a");

	$sendlist = array();

	$pregetactive = mysql_query("SELECT `id`,`user`,`event`,`role`,`complex` from `activity` WHERE `user`='".LOGGED_ID."'");
	while($active = mysql_fetch_array($pregetactive)) {
		$preevent = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`holder`,`dep` from `events` WHERE `id`='".$active[2]."' LIMIT 1");
		$event = mysql_fetch_row($preevent);

		if($event[5] !== NULL) {
			$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$event[5]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			if(strlen($getdep[1]) <= 50) { $holder = $getdep[1]; }
			else { $holder = mb_substr($getdep[1], 0, 50, "UTF-8")."..."; }
		} else {
			$pregetholder = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$event[4]."' LIMIT 1;");
			$getholder = mysql_fetch_row($pregetholder);
			$holder = mb_substr($getholder[2], 0, 1, "UTF-8").".".mb_substr($getholder[3], 0, 1, "UTF-8").".".$getholder[1];
		}

		$countit = activityPoints($active[2],$active[3],$active[4]);

		list($d1, $d2, $d3) = split('[-]', $event[2]);
		$d_since = $d3.".".$d2.".".substr($d1, 2, 4);
		if($event[3] !== NULL) {
			list($d1, $d2, $d3) = split('[-]', $event[3]);
			$event[3] = $d3.".".$d2.".".substr($d1, 2, 4);
		}

		$newactive = array(
			"eid" => $event[0],
			"date_since" => $d_since,
			"date_for" => $event[3],
			"name" => $event[1],
			"holder" => $holder,
			"role" => $active[3],
			"complex" => $active[4],
			"points" => $countit
		);
		$sendlist["events"][] = $newactive;
		unset($newactive);
	}

	$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
	$resscores = mysql_fetch_row($prescores);

	$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
	$getrank = mysql_fetch_row($pregetrank);

	$sendlist["scores"] = $resscores[1];
	$sendlist["current"] = $getrank[0];
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Вывод рейтинговой таблицы
elseif($_GET['act'] == "getrating") {
	accessto("s,k,t,a");

	if(($_GET['page'] == "") or (!is_numeric($_GET['page']))) { wrongusing(); }

	$maxrows = 10;
	$pagenum = ($_GET['page'])*$maxrows;

	$sendlist = array();

	$cursearchdep = "";
	if(isset($_GET['dep'])) { $cursearchdep = $_GET['dep']; }
	$cursearchcourse = "";
	if(isset($_GET['course'])) {
		$cursearchcourse .= "AND (";
		if(mb_strlen($_GET['course'], "UTF-8") == 2) {
			if(mb_substr($_GET['course'], 0, 1, "UTF-8") == "c") {
				$cursearchcourse .= "`curcourse` = 'c-".mb_substr($_GET['course'], 1, 1, "UTF-8")."'";
			}
			if(mb_substr($_GET['course'], 0, 1, "UTF-8") == "m") {
				$cursearchcourse .= "`curcourse` = 'm-".mb_substr($_GET['course'], 1, 1, "UTF-8")."'";
			}
		}
		else { $cursearchcourse .= "`curcourse` = 'b-".$_GET['course']."' OR `curcourse` = 's-".$_GET['course']."'"; }
		$cursearchcourse .= ")";
	}
	$pregetfacs = mysql_query("SELECT `id`,`type`,`area` from `deps` WHERE `type`='g' AND `area`='".$_GET['dep']."'");
	$facsnum = mysql_num_rows($pregetfacs);
	$searchfaclist = "";
	$counter = 0;
	while($getfacs = mysql_fetch_array($pregetfacs)) {
		if($counter == 0) { $searchfaclist .= " AND ("; }
		$searchfaclist .= "`dep` = '".$getfacs[0]."'";
		if(++$counter !== $facsnum) { $searchfaclist .= " OR "; }
		else { $searchfaclist .= ")"; }
	}

	$cursearchtag = "count";
	if(isset($_GET['tag'])) {
		$_pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a' AND `id` ='".$_GET['tag']."';");
		$pregetByATags = mysql_fetch_row($_pregetByATags);
		if(!$pregetByATags[0]) { wrongusing(); }
		$cursearchtag = "ic_".$pregetByATags[0]."";
	}

	$presearch = trim($_GET['query']);
	if($presearch == "") {
		$pregetlist = mysql_query("SELECT `id`,`type`,`fullname`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`count`,`groupnum`,`groups` from `users` WHERE `type`='a' AND `out`='s' ".$cursearchcourse." ".$searchfaclist." ORDER BY `".$cursearchtag."` DESC LIMIT ".$pagenum.", ".$maxrows.";");
		$fornum = mysql_query("SELECT `id`,`type`,`fullname`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`count`,`groupnum`,`groups` from `users` WHERE `type`='a' AND `out`='s' ".$cursearchcourse." ".$searchfaclist." ORDER BY `".$cursearchtag."` DESC;");
	}
	else {
		$pregetlist = mysql_query("SELECT `id`,`type`,`fullname`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`count`,`groupnum`,`groups` from `users` WHERE `type`='a' AND `out`='s' AND `fullname` LIKE '%".$presearch."%' ".$cursearchcourse." ".$searchfaclist." ORDER BY `".$cursearchtag."` DESC LIMIT ".$pagenum.", ".$maxrows.";");
		$fornum = mysql_query("SELECT `id`,`type`,`fullname`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`count`,`groupnum`,`groups` from `users` WHERE `type`='a' AND `out`='s' AND `fullname` LIKE '%".$presearch."%' ".$cursearchcourse." ".$searchfaclist." ORDER BY `".$cursearchtag."` DESC;");
	}
	$getnum = mysql_num_rows($fornum);
	if($getnum == 0) { errorjson("notfound"); }
	$n = 1;

	if(LOGGED_ACCESS == "a") {
		while($getlist = mysql_fetch_array($pregetlist)) {
			//$name = mb_substr($getlist[4], 0, 1, "UTF-8").".".mb_substr($getlist[5], 0, 1, "UTF-8").". ".$getlist[3];
			$newpname = "";
			if(mb_strlen($getlist[5], "UTF-8")<2) { $newpname = ""; } else { $newpname = " ".$getlist[5]; }
			$name = $getlist[3]." ".$getlist[4]."".$newpname;

			$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getlist[6]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
			$getfac = mysql_fetch_row($pregetfac);
			$dep = $getfac[1];

			// бакалавриат: b-1, специалитет: s-1, магистратура: m-1
			list($edul, $educ) = split('[-]', $getlist[7]);
			//if($edul == "b") { $edul = "бак."; }
			//elseif($edul == "m") { $edul = "маг."; }
			//elseif($edul == "s") { $edul = "спец."; }
			if($edul == "m") { $educ .= " маг"; }
			if($edul == "c") { $educ .= " спо"; }

			$mylists = array();
			$ownlist = json_decode($getlist[10]);
			if(count($ownlist) !== 0) {
				for($i=0; $i<count($ownlist); $i++) {
					$pregetlists = mysql_query("SELECT `id`,`name`,`icon`,`public`,`content` from `lists` WHERE `id`='".$ownlist[$i]."' AND `public`='y' AND `icon`!='n' LIMIT 1;");
					$lists = mysql_fetch_row($pregetlists);
					if(!$lists[0]) { continue; }
					$list_members = json_decode($lists[4]);
					$members = array();
					for($c=0; $c<count($list_members); $c++) {
						$members[] = $list_members[$c][0];
					}
					$pos = array_search($getlist[0], $members);
					$mylists[] = array($lists[0],$lists[1],$list_members[$pos][1],$lists[2]);
				}
			}

			$_pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".$getlist[0]." AND `type`='a';");
			$_getrank = mysql_fetch_row($_pregetrank);

			$newactive = array(
				"id" => $getlist[0],
				"rate" => ($n + ($_GET['page']*$maxrows)),
				"rate_filter" => $_getrank[0],
				"name" => $name,
				"course" => $educ,
				"dep" => $dep,
				"lists" => $mylists,
				"points" => $getlist[8]
			);
			$sendlist["students"][] = $newactive;
			unset($newactive);
			$n++;
		}

		$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
		$resscores = mysql_fetch_row($prescores);

		$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
		$getrank = mysql_fetch_row($pregetrank);

		$sendlist["scores"] = $resscores[1];
		$sendlist["current"] = $getrank[0];
	}
	elseif((LOGGED_ACCESS == "t") or (LOGGED_ACCESS == "k") or (LOGGED_ACCESS == "s")) {
		while($getlist = mysql_fetch_array($pregetlist)) {
			$newpname = "";
			if(mb_strlen($getlist[5], "UTF-8")<2) { $newpname = ""; } else { $newpname = " ".$getlist[5]; }
			$name = $getlist[3]." ".$getlist[4]."".$newpname;

			$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getlist[6]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
			$getfac = mysql_fetch_row($pregetfac);
			$dep = $getfac[1]." (".$getdep[2].")-".$getlist[9];

			// бакалавриат: b-1, специалитет: s-1, магистратура: m-1
			list($edul, $educ) = split('[-]', $getlist[7]);
			if($edul == "m") { $educ .= " маг"; }
			if($edul == "c") { $educ .= " спо"; }

			$mylists = array();
			$ownlist = json_decode($getlist[10]);
			if(count($ownlist) !== 0) {
				for($i=0; $i<count($ownlist); $i++) {
					$pregetlists = mysql_query("SELECT `id`,`name`,`icon`,`public`,`content` from `lists` WHERE `id`='".$ownlist[$i]."' AND `public`='y' AND `icon`!='n' LIMIT 1;");
					$lists = mysql_fetch_row($pregetlists);
					if(!$lists[0]) { continue; }
					$list_members = json_decode($lists[4]);
					$members = array();
					for($c=0; $c<count($list_members); $c++) {
						$members[] = $list_members[$c][0];
					}
					$pos = array_search($getlist[0], $members);
					$mylists[] = array($lists[0],$lists[1],$list_members[$pos][1],$lists[2]);
				}
			}

			$_pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".$getlist[0]." AND `type`='a';");
			$_getrank = mysql_fetch_row($_pregetrank);

			$newactive = array(
				"id" => $getlist[0],
				"rate" => ($n + ($_GET['page']*$maxrows)),
				"rate_filter" => $_getrank[0],
				"name" => $name,
				"course" => $educ,
				"dep" => $dep,
				"lists" => $mylists,
				"points" => $getlist[8]
			);
			$sendlist["students"][] = $newactive;
			unset($newactive);
			$n++;
		}
	}
	$sendlist["allrows"] = $getnum;
	$sendlist["maxrows"] = $maxrows;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Список пользователей
elseif($_GET['act'] == "getusers") {
	accessto("s");

	if(($_GET['page'] == "") or (!is_numeric($_GET['page']))) { wrongusing(); }

	$maxrows = 10;
	$pagenum = ($_GET['page'])*$maxrows;

	$sendlist = array();

	$presearch = $_GET['query'];
	$presearch = trim($presearch);
	if($presearch == "") {
		$pregetlist = mysql_query("SELECT `id`,`type`,`fullname`,`dep` from `users` WHERE `type` !='a' ORDER BY `id` DESC LIMIT ".$pagenum.", ".$maxrows.";");
		$fornum = mysql_query("SELECT `id`,`type`,`fullname`,`dep` from `users` WHERE `type` !='a' ORDER BY `id` DESC;");
	}
	else {
		$pregetlist = mysql_query("SELECT `id`,`type`,`fullname`,`dep` from `users` WHERE `type` !='a' AND `fullname` LIKE '%".$presearch."%' ORDER BY `id` DESC LIMIT ".$pagenum.", ".$maxrows.";");
		$fornum = mysql_query("SELECT `id`,`type`,`fullname`,`dep` from `users` WHERE `type` !='a' AND `fullname` LIKE '%".$presearch."%' ORDER BY `id` DESC;");
	}
	$getnum = mysql_num_rows($fornum);
	if($getnum == 0) { errorjson("notfound"); }
	$i = 1;
	while($getlist = mysql_fetch_array($pregetlist)) {

		$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getlist[3]."' LIMIT 1");
		$getdep = mysql_fetch_row($pregetdep);

		if($getlist[1] == "s") { $usertype = "А"; }
		elseif($getlist[1] == "k") { $usertype = "С"; }
		elseif($getlist[1] == "t") { $usertype = "П"; }

		$newactive = array(
			"id" => $getlist[0],
			"type" => $usertype,
			"name" => $getlist[2],
			"dep" => $getdep[1],
		);
		$sendlist["users"][] = $newactive;
		unset($newactive);
		$i++;
	}

	$sendlist["allrows"] = $getnum;
	$sendlist["maxrows"] = $maxrows;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Список вовлеченных в мероприятие
elseif($_GET['act'] == "getinvolved") {
	accessto("s,k,t,a");

	if(!$_GET['eid']) { wrongusing(); }

	$forecheck = mysql_query("SELECT `id`,`name`,`place`,`date`,`date_for`,`time_since`,`time_for`,`level`,`dep`,`holder`,`author`,`comment`,`fixers`,`outside`,`complex`,`tags` from `events` WHERE `id`=".$_GET['eid']." LIMIT 1;");
	$echeck = mysql_fetch_row($forecheck);
	if(!$echeck[1]) { errorjson("Мероприятие не найдено"); }

	$sendlist = array();

	list($d1, $d2, $d3) = split('[-]', $echeck[3]);
	$echeck[3] = $d3.".".$d2.".".$d1;
	if($echeck[4] !== NULL) {
		list($d1, $d2, $d3) = split('[-]', $echeck[4]);
		$echeck[4] = $d3.".".$d2.".".$d1;
	}

	$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$echeck[8]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$pregeteholder = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$echeck[9]."' LIMIT 1;");
	$geteholder = mysql_fetch_row($pregeteholder);
	$eholder = mb_substr($geteholder[2], 0, 1, "UTF-8").".".mb_substr($geteholder[3], 0, 1, "UTF-8").".".$geteholder[1];

	$pregetcreator = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$echeck[10]."' LIMIT 1;");
	$getcreator = mysql_fetch_row($pregetcreator);
	$creator = mb_substr($getcreator[2], 0, 1, "UTF-8").".".mb_substr($getcreator[3], 0, 1, "UTF-8").".".$getcreator[1];

	if($echeck[5] == "00:00:00" and $echeck[6] == "00:00:00") {
		$echeck[5] = NULL;
		$echeck[6] = NULL;
	} else {
		$echeck[5] = substr($echeck[5], 0, 5);
		$echeck[6] = substr($echeck[6], 0, 5);
	}

	$sendlist["einfo"] = array(
		"eid" => $echeck[0],
		"name" => $echeck[1],
		"place" => $echeck[2],
		"ds" => $echeck[3],
		"df" => $echeck[4],
		"ts" => $echeck[5],
		"tf" => $echeck[6],
		"comment" => $echeck[11],
		"level" => $echeck[7],
		"dep" => $getdep[1],
		"hid" => $echeck[9],
		"hname" => $eholder,
		"creator" => $creator,
		"outside" => $echeck[13],
		"complex" => $echeck[14],
		"tags" => json_decode($echeck[15])
	);

	$checkfixers = json_decode($echeck[12]);
	$pos = array_search(LOGGED_ID, $checkfixers);

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$echeck[9]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ID == $echeck[9]) or (LOGGED_ID == $echeck[10]) or (LOGGED_ACCESS == "s") or ($pos !== false) or ($_ifindep[1] == $ifindep[1])) {
		$pregetlist = mysql_query("SELECT `id`,`event`,`user`,`role`,`created`,`addedby`,`complex` from `activity` WHERE `event` ='".$echeck[0]."' ORDER BY `id` DESC;");
		$getnum = mysql_num_rows($pregetlist);

		while($erend = mysql_fetch_array($pregetlist)) {
			$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum` from `users` WHERE `id` ='".$erend[2]."' LIMIT 1;");
			$getuser = mysql_fetch_row($pregetuser);

			$newpname = "";
			if(mb_strlen($getuser[3], "UTF-8")<2) { $name = mb_substr($getuser[2], 0, 1, "UTF-8").".".$getuser[1]; }
			else { $name = mb_substr($getuser[2], 0, 1, "UTF-8").".".mb_substr($getuser[3], 0, 1, "UTF-8").".".$getuser[1]; }

			$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
			$getfac = mysql_fetch_row($pregetfac);

			list($edul, $educ) = split('[-]', $getuser[5]);
			//if($edul == "b") { $edul = "бак."; }
			//elseif($edul == "m") { $edul = "маг."; }
			//elseif($edul == "s") { $edul = "спец."; }
			if($edul == "m") { $educ .= "м"; }

			$afrom = $getfac[1]."(".$getdep[2].")-".$getuser[6];

			list($ii1, $ii2) = split(' ', $erend[4]);
			list($addyear, $addmonth, $addday) = split('[-]', $ii1);
			$added_dt = $addday.'.'.$addmonth.'.'.substr($addyear, 2, 4).' '.substr($ii2, 0, 5);

			$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id` ='".$erend[5]."' LIMIT 1;");
			$getholder = mysql_fetch_row($pregetuser);
			$holder = mb_substr($getholder[2], 0, 1, "UTF-8").".".mb_substr($getholder[3], 0, 1, "UTF-8").".".$getholder[1];

			$isedit = "y";
			if((LOGGED_ACCESS !== "s") and ($echeck[9] !== LOGGED_ID) and ($echeck[10] !== LOGGED_ID) and ($erend[5] !== LOGGED_ID) and ($_ifindep[1] !== $ifindep[1])) {
				if($checkfixers[$pos] !== $erend[5]) { $isedit = "n"; }
			}

			$newinlist = array(
				"a_id" => $erend[0],
				"a_uid" => $erend[2],
				"a_name" => $name,
				"a_from" => $afrom,
				"a_role" => $erend[3],
				"a_complex" => $erend[6],
				"a_time" => $added_dt,
				"a_by" => $holder,
				"a_edit" => $isedit
			);
			$sendlist["alist"][] = $newinlist;
			unset($newinlist);
		}
	}
	else {
		$pregetlist = mysql_query("SELECT `id`,`event`,`user`,`role`,`complex` from `activity` WHERE `event` ='".$echeck[0]."' ORDER BY `id` DESC;");
		$getnum = mysql_num_rows($pregetlist);

		while($erend = mysql_fetch_array($pregetlist)) {
			$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum` from `users` WHERE `id` ='".$erend[2]."' LIMIT 1;");
			$getuser = mysql_fetch_row($pregetuser);
			$newpname = "";
			if(mb_strlen($getuser[3], "UTF-8")<2) { $name = mb_substr($getuser[2], 0, 1, "UTF-8").".".$getuser[1]; }
			else { $name = mb_substr($getuser[2], 0, 1, "UTF-8").".".mb_substr($getuser[3], 0, 1, "UTF-8").".".$getuser[1]; }

			$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
			$getfac = mysql_fetch_row($pregetfac);

			list($edul, $educ) = split('[-]', $getuser[5]);
			if($edul == "m") { $educ .= "м"; }

			if(LOGGED_ACCESS == "a") { $afrom = $getfac[1]." ".$educ."к."; }
			else { $afrom = $getfac[1]."(".$getdep[2].")-".$getuser[6]; }

			$newinlist = array(
				"a_id" => $erend[0],
				"a_uid" => $erend[2],
				"a_name" => $name,
				"a_from" => $afrom,
				"a_role" => $erend[3],
				"a_complex" => $erend[4]
			);
			$sendlist["alist"][] = $newinlist;
			unset($newinlist);
		}
	}

	if(LOGGED_ACCESS == "a") {
		$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
		$resscores = mysql_fetch_row($prescores);

		$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
		$getrank = mysql_fetch_row($pregetrank);

		$sendlist["scores"] = $resscores[1];
		$sendlist["current"] = $getrank[0];
	}

	$sendlist["allrows"] = $getnum;
	$sendlist["maxrows"] = 10;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Список мероприятий
elseif($_GET['act'] == "getevents") {
	accessto("s,k,t,a");

	if(($_GET['page'] == "") or (!is_numeric($_GET['page']))) { wrongusing(); }

	$page_holderid = "";
	if(isset($_GET['holder'])) { $page_holderid = $_GET['holder']; }

	$page_info = "";
	if(isset($_GET['date_since'])) {
		$date_since = datecheck($_GET['date_since'], "Дата введена неверно.");
		$page_info .= " AND `date` >= '".$date_since."'";
	}
	if(isset($_GET['date_for'])) {
		$date_for = datecheck($_GET['date_for'], "Дата введена неверно.");
		$page_info .= " AND `date` <= '".$date_for."'";
	}
	if(isset($_GET['tag'])) {
		$page_info .= " AND `tags` LIKE '%\"".$_GET['tag']."\"%'";
	}

	$holderid = "";
	if(isset($_GET['holder'])) { $page_info .= " AND `dep`='".$page_holderid."'"; }

	$maxrows = 10;
	$pagenum = ($_GET['page'])*$maxrows;

	$sendlist = array();

	$presearch = preg_replace("|[\s]+|", " ", $_GET['query']);
	$presearch = trim($presearch);

	$pregetlist = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`time_since`,`time_for`,`level`,`dep`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `name` LIKE '%".$presearch."%' ".$page_info." ORDER BY `date` DESC LIMIT ".$pagenum.", ".$maxrows.";");

	$fornum = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`time_since`,`time_for`,`level`,`dep`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `name` LIKE '%".$presearch."%' ".$page_info." ORDER BY `date` DESC;");

	$getnum = mysql_num_rows($fornum);
	if($getnum == 0) { errorjson("notfound"); }

	$getAEtags = mysql_query("SELECT `id`,`type`,`name` from `tags` WHERE `type`='a' or `type`='e'");
	while($AEtag = mysql_fetch_array($getAEtags)) {
		$_fornumAEtags = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`time_since`,`time_for`,`level`,`dep`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `tags` LIKE '%\"".$AEtag[0]."\"%' AND `name` LIKE '%".$presearch."%' ".$page_info." ORDER BY `date` DESC;");
		$fornumAEtags = mysql_num_rows($_fornumAEtags);
		$sendlist["tags"][] = array($AEtag[0],$fornumAEtags);
	}

	while($event = mysql_fetch_array($pregetlist)) {
		$preenum = mysql_query("SELECT `id`,`event` from `activity` WHERE `event`='".$event[0]."'");
		$enum = mysql_num_rows($preenum);

		list($d1, $d2, $d3) = split('[-]', $event[2]);
		$d_since = $d3.".".$d2.".".substr($d1, 2, 4);
		if($event[3] !== NULL) {
			list($d1, $d2, $d3) = split('[-]', $event[3]);
			$event[3] = $d3.".".$d2.".".substr($d1, 2, 4);
		}

		if($event[7] !== NULL) {
			$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$event[7]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			if(strlen($getdep[1]) <= 50) { $holder = $getdep[1]; }
			else { $holder = mb_substr($getdep[1], 0, 50, "UTF-8")."..."; }
		} else {
			$pregetholder = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$event[8]."' LIMIT 1;");
			$getholder = mysql_fetch_row($pregetholder);
			$holder = mb_substr($getholder[2], 0, 1, "UTF-8").".".mb_substr($getholder[3], 0, 1, "UTF-8").".".$getholder[1];
		}

		// Проверка департамента
		$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$event[8]." LIMIT 1;");
		$_ifindep = mysql_fetch_row($_preifindep);

		$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
		$ifindep = mysql_fetch_row($preifindep);

		if((LOGGED_ID == $event[8]) or (LOGGED_ID == $event[9]) or (LOGGED_ACCESS == "s") or ($_ifindep[1] == $ifindep[1])) { $isedit = "yes"; $isaccess = "yes"; } else {
			$isedit = "no";
			$isaccess = "no";

			$checkfixers = json_decode($event[10]);
			$pos = array_search(LOGGED_ID, $checkfixers);

			if($pos !== false) {
				$isaccess = "yes";
			}
		}

		if(LOGGED_ACCESS == "a") {
			$preifmy = mysql_query("SELECT `id`,`event`,`user` from `activity` WHERE `event`='".$event[0]."' AND `user`='".LOGGED_ID."' LIMIT 1;");
			$ifmy = mysql_fetch_row($preifmy);
			if($ifmy[0]) { $isaccess = "yes"; }
		}

		$newevent = array(
			"e_id" => $event[0],
			"e_date_since" => $d_since,
			"e_date_for" => $event[3],
			"e_time_since" => substr($event[4], 0, 5),
			"e_time_for" => substr($event[5], 0, 5),
			"e_name" => $event[1],
			"e_level" => $event[6],
			"e_holder" => $holder,
			"e_involved" => $enum,
			"e_edit" => $isedit,
			"e_access" => $isaccess
		);
		$sendlist["events"][] = $newevent;
		unset($newevent);
	}



	if(LOGGED_ACCESS == "a") {
		$prescores = mysql_query("SELECT `id`,`count` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1;");
		$resscores = mysql_fetch_row($prescores);

		$pregetrank = mysql_query("SELECT * FROM (SELECT @rownum:=@rownum+1 AS pos, `id`, `count`,`type` FROM `users` u, (SELECT @rownum:=0) r WHERE `type`='a' ORDER BY `count` DESC) as a WHERE `id`=".LOGGED_ID." AND `type`='a';");
		$getrank = mysql_fetch_row($pregetrank);

		$sendlist["scores"] = $resscores[1];
		$sendlist["current"] = $getrank[0];
	}

	$sendlist["allrows"] = $getnum;
	$sendlist["maxrows"] = $maxrows;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Вывод информации о мероприятии для редактирования
elseif($_GET['act'] == "getevent") {
	accessto("s,k");
	if(!$_GET['eid']) { wrongusing(); }
	$precheckeid = mysql_query("SELECT `id`,`name`,`place`,`date`,`date_for`,`time_since`,`time_for`,`comment`,`level`,`dep`,`holder`,`author`,`fixers`,`outside`,`complex`,`tags` from `events` WHERE `id`='".$_GET['eid']."' LIMIT 1");
	$checkeid = mysql_fetch_row($precheckeid);
	if(!$checkeid[1]) { errorjson("Мероприятие не найдено"); }

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$checkeid[10]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ACCESS !== "s") and ($checkeid[10] !== LOGGED_ID) and ($checkeid[11] !== LOGGED_ID) and ($_ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для редактирования данного мероприятия"); }

	list($d1, $d2, $d3) = split('[-]', $checkeid[3]);
	$checkeid[3] = $d3.".".$d2.".".$d1;
	if($checkeid[4] !== NULL) {
		list($d1, $d2, $d3) = split('[-]', $checkeid[4]);
		$checkeid[4] = $d3.".".$d2.".".$d1;
	}

	$pregetholder = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$checkeid[10]."' LIMIT 1;");
	$getholder = mysql_fetch_row($pregetholder);
	$holder = $getholder[1]." ".$getholder[2]." ".$getholder[3];

	$fixers = array();
	$getfixers = json_decode(stripslashes($checkeid[12]));
	if((gettype($getfixers) !== "array") and !$getfixers[0]) {}
	else {
		for($i=0; $i<count($getfixers); $i++) {
			if(!is_numeric($getfixers[$i])) { wrongusing(); }
			$precheckuser = mysql_query("SELECT `id`,`type`,`fullname` from `users` WHERE `id`='".$getfixers[$i]."' AND `type` !='d' LIMIT 1");
			$checkuser = mysql_fetch_row($precheckuser);
			if(!$checkuser[0]) { wrongusing(); }
			$fixers[] = array("".$getfixers[$i]."",$checkuser[2]);
		}
	}

	if($checkeid[13] == "y")  { $outside = true; } else { $outside = false; }
	if($checkeid[14] == "y")  { $complex = true; } else { $complex = false; }

	$gettags = json_decode(stripslashes($checkeid[15]));

	if($checkeid[5] == "00:00:00" and $checkeid[6] == "00:00:00") {
		$checkeid[5] = NULL;
		$checkeid[6] = NULL;
	} else {
		$checkeid[5] = substr($checkeid[5], 0, 5);
		$checkeid[6] = substr($checkeid[6], 0, 5);
	}

	$sendlist["einfo"] = array(
		"eid" => $checkeid[0],
		"name" => $checkeid[1],
		"place" => $checkeid[2],
		"ds" => $checkeid[3],
		"df" => $checkeid[4],
		"ts" => $checkeid[5],
		"tf" => $checkeid[6],
		"comment" => $checkeid[7],
		"level" => $checkeid[8],
		"dep" => $checkeid[9],
		"hid" => $checkeid[10],
		"hname" => $holder,
		"fixers" => $fixers,
		"outside" => $outside,
		"complex" => $complex,
		"tags" => $gettags
	);
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Регистрация и редактирование пользователя
elseif(($_GET['act'] == "adduser") or ($_GET['act'] == "edituser")) {
	accessto("s,k"); // доступ только для ССиА и КМ
	// При попытке редактирования проверить id пользователя
	if($_GET['act'] == "edituser") {
		if(!$_GET['id']) { wrongusing(1); }
		$precheckuid = mysql_query("SELECT `id`,`phone`,`code` from `users` WHERE `id`='".$_GET['id']."' LIMIT 1");
		$checkuid = mysql_fetch_row($precheckuid);
		if(!$checkuid[1]) { errorjson("Пользователь не найден"); }
		// Последний администратор
		if(LOGGED_ID == $_GET['id'] and LOGGED_ACCESS == "s" and $_GET['type'] !== "s") { errorjson("Администратор не может изменить себе доступ."); }
	}
	else {
		if(!$_GET['as']) { wrongusing(2); }
		if(($_GET['as'] !== "y") and ($_GET['as'] !== "n")) { wrongusing(3); }
	}
	// Проверка на заполнение полей
	if((!$_GET['type']) or (!$_GET['surname']) or (!$_GET['firstname']) or (!$_GET['patronymic']) or (!$_GET['sex']) or (!$_GET['depid'])) { wrongusing(10); }
	if(($_GET['type'] !== "a") and ($_GET['type'] !== "k") and ($_GET['type'] !== "t") and ($_GET['type'] !== "s")) { wrongusing(5); }
	// Проверка пола
	if(($_GET['sex'] !== "m") and ($_GET['sex'] !== "f")) { wrongusing(6);  }
	// Проверка формата телефонного номера
	if(isset($_GET['phone'])) {
		if(!preg_match('/^[0-9]{10}$/', $_GET['phone'])) { errorjson("Неверный формат телефонного номера"); }

		// Проверка на повторяемость номера телефона
		if(($_GET['act'] == "adduser") or (($_GET['act'] == "edituser") and ($_GET['phone'] !== $checkuid[1]))) {
			$precheckphone = mysql_query("SELECT `id`,`phone` from `users` WHERE `phone`='".$_GET['phone']."' LIMIT 1");
			$checkphone = mysql_fetch_row($precheckphone);
			if($checkphone[1]) { errorjson("Пользователь с таким номером телефона уже существует"); }
		}
	}
	else {
		if($_GET['type'] == "a") { $_GET['phone'] = "unknown"; } else { wrongusing(); }
	}

	$depname = "";
	// Если студент
	if($_GET['type'] == "a") {
		if((!$_GET['course']) or (!$_GET['gen']) or (!$_GET['level']) or (!$_GET['budget']) or (!$_GET['groupnum'])) { wrongusing(); }
		// Проверка даты рождения
		if(isset($_GET['birthday']) and $_GET['birthday'] !== "00.00.0000") {
			$userbd = "'".datecheck($_GET['birthday'], "Дата рождения введена неверно")."'";
		} else {
			$userbd = "NULL";
		}
		$precheckdep = mysql_query("SELECT `id`,`type`,`area` from `deps` WHERE `type` ='g' AND `id`='".$_GET['depid']."' LIMIT 1");
		$checkdep = mysql_fetch_row($precheckdep);
		if(!$checkdep[1]) { wrongusing(); }
		$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$checkdep[2]."' LIMIT 1");
		$getfac = mysql_fetch_row($pregetfac);
		$depname = " (".$getfac[1].")";
		$setgen = "0";
		// Образовательный стандарт (3 и 3+)
		if($_GET['gen'] !== "2" and $_GET['gen'] !== "3" and $_GET['gen'] !== "3+") { wrongusing(); }
		else { $setgen = "'".$_GET['gen']."'"; }

		if(($_GET['budget'] !== "y") and ($_GET['budget'] !== "n")) { wrongusing(); }
		// Проверка номера группы
		if(!is_numeric($_GET['groupnum'])) { errorjson("Номер группы может состоять только из цифр"); }
		if($_GET['act'] == "adduser") {
			if(!$_GET['barcode']) { wrongusing(); }
			else {
				// Проверка формата кода
				if(!checkbc($_GET['barcode'])) { errorjson("Неверный формат кода"); }
				// Проверка на повторяемость кода
				$precheckcode = mysql_query("SELECT `id`,`code` from `users` WHERE `code`='".$_GET['barcode']."' LIMIT 1");
				$checkcode = mysql_fetch_row($precheckcode);
				if($checkcode[1]) { errorjson("Студент с таким кодом уже существует"); }
			}
		} else { $curcode = "'".$checkuid[1]."'";; }
		// Добавление недостающих данных
		$inputpost = "NULL";
		$groupnum = "'".$_GET['groupnum']."'";
		$curcode = "'".$_GET['barcode']."'";
		$setform = "'1'";
		$setcourse = "'".$_GET['level']."-".$_GET['course']."'";
		$budget = "'".$_GET['budget']."'";
		$setfac = "'".$checkdep[2]."'";
	}
	// Если сотрудник университета или системы
	elseif(($_GET['type'] == "t") or ($_GET['type'] == "k") or ($_GET['type'] == "s")) {
		if(!$_GET['post']) { wrongusing(7); }
		$precheckdep = mysql_query("SELECT `id`,`type` from `deps` WHERE `id`='".$_GET['depid']."' AND `type`='d' LIMIT 1");
		$checkdep = mysql_fetch_row($precheckdep);
		if(!$checkdep[1]) { wrongusing(8); }
		// Добавление недостающих данных
		$userbd = "NULL";
		$inputpost = "'".htmlentities($_GET['post'], ENT_QUOTES, "UTF-8")."'";
		$groupnum = "NULL";
		$curcode = "NULL";
		$setgen = "NULL";
		$setform = "NULL";
		$setcourse = "NULL";
		$budget = "NULL";
		$setfac = "NULL";
	}

	// Формирование запроса и попытка добавления/редактирования строки в БД
	$u_name1 = htmlentities($_GET['surname'], ENT_QUOTES, "UTF-8");
	$u_name2 = htmlentities($_GET['firstname'], ENT_QUOTES, "UTF-8");
	$u_name3 = htmlentities($_GET['patronymic'], ENT_QUOTES, "UTF-8");
	if($_GET['act'] == "adduser") {
		$newpwgen = pwgenerator();
		$regtoken = md5(uniqid('auth', true).$newpwgen);
		$addusereq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`users` (`id`, `access`, `phone`, `password`, `type`, `out`, `sin`, `code`, `fullname`, `sname`, `fname`, `pname`, `sex`, `birthday`, `post`, `fac`, `dep`, `gen`, `form`, `curcourse`, `groupnum`, `budget`, `created`, `addedby`, `count`, `groups`) VALUES (NULL, 'y', '".$_GET['phone']."', '".$newpwgen."', '".$_GET['type']."', 's', '".$_GET['phone']."', ".$curcode.", '".$u_name1." ".$u_name2." ".$u_name3."".$depname."', '".$u_name1."', '".$u_name2."', '".$u_name3."', '".$_GET['sex']."', ".$userbd.", ".$inputpost.", ".$setfac.", '".$checkdep[0]."', ".$setgen.", ".$setform.", ".$setcourse.", ".$groupnum.", ".$budget.", '".date("Y-m-d")." ".date("H:i:s")."', '".LOGGED_ID."', '0','[]');";
	} elseif($_GET['act'] == "edituser") {
		if($_GET['type'] == "a") {
			$addusereq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `phone` =  '".$_GET['phone']."',
`fullname` = '".$u_name1." ".$u_name2." ".$u_name3."".$depname."',
`sname` =  '".$u_name1."',
`fname` =  '".$u_name2."',
`pname` =  '".$u_name3."',
`sex` =  '".$_GET['sex']."',
`birthday` =  ".$userbd.",
`fac` = '".$getfac[0]."',
`dep` =  '".$checkdep[0]."',
`gen` =  ".$setgen.",
`form` =  ".$setform.",
`curcourse` =  ".$setcourse.",
`groupnum` =  ".$groupnum.",
`budget` =  ".$budget." WHERE `users`.`id` =".$_GET['id'].";";
		} else {
			$addusereq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `phone` =  '".$_GET['phone']."',
`type` =  '".$_GET['type']."',
`sin` = '".$_GET['phone']."',
`fullname` = '".$u_name1." ".$u_name2." ".$u_name3."".$depname."',
`sname` =  '".$u_name1."',
`fname` =  '".$u_name2."',
`pname` =  '".$u_name3."',
`sex` =  '".$_GET['sex']."',
`birthday` =  ".$userbd.",
`post` =  ".$inputpost.",
`fac` = '',
`dep` =  '".$checkdep[0]."',
`gen` =  ".$setgen.",
`form` =  ".$setform.",
`curcourse` =  ".$setcourse.",
`groupnum` =  ".$groupnum.",
`budget` =  ".$budget." WHERE `users`.`id` =".$_GET['id'].";";
		}
	}
	if(!mysql_query($addusereq)) { errorjson("Ошибка базы данных. Повторите попытку позже.".mysql_error()); }
	if(($_GET['act'] == "adduser") and ($_GET['as'] == "y")) {
		$notifyres = sendsms($_GET['phone'], "Сайт: ".PROTOCOL.$_SERVER['SERVER_NAME']." Логин: ".$_GET['phone']." Пароль: ".$newpwgen."");
		if($notifyres !== "sent") { exit('{"error":"ok_notify","notifyres":"'.$notifyres.'"}'); }
		else { errorjson("ok"); }
	} else { errorjson("ok"); }
}

// Сменить код карты
elseif($_GET['act'] == "changebook") {
	accessto("s");
	if((!$_GET['sid']) or (!$_GET['newcode'])) { wrongusing(); }
	$precheckscode = mysql_query("SELECT `id`,`code`,`type` from `users` WHERE `id`='".$_GET['sid']."' AND type='a' LIMIT 1");
	$checkscode = mysql_fetch_row($precheckscode);
	if(!$checkscode[0]) { errorjson("Пользователь не найден"); }
	if($checkscode[1] == $_GET['newcode']) { errorjson("Сканирован код текущей карты. Используйте другую карту."); }

	if(!checkbc($_GET['newcode'])) { errorjson("Неверный формат кода"); }
	$pretrycode = mysql_query("SELECT `id`,`code` from `users` WHERE `code`='".$_GET['newcode']."' LIMIT 1");
	$trycode = mysql_fetch_row($pretrycode);
	if($trycode[1]) { errorjson("Студент с такой картой уже существует"); }
	$ccreq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `code` =  '".$_GET['newcode']."' WHERE  `users`.`id` =".$_GET['sid'].";";
	if(!mysql_query($ccreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	else { errorjson("ok"); }
}

// Удаление пользователя
elseif($_GET['act'] == "deluser") {
	accessto("s");
	if(!$_GET['id']) { wrongusing(); }
	$precheckuid = mysql_query("SELECT `id`,`phone` from `users` WHERE `id`='".$_GET['id']."' LIMIT 1");
	$checkuid = mysql_fetch_row($precheckuid);
	if(!$checkuid[1]) { errorjson("Пользователь не найден"); }
	$precheckheid = mysql_query("SELECT `id`,`name`,`holder` from `events` WHERE `holder`='".$_GET['id']."' LIMIT 1");
	$checkheid = mysql_fetch_row($precheckheid);
	if($checkheid[1]) { errorjson("Пользователя невозможно удалить, т.к. он является ответственным за одно или более уже внесенных мероприятий"); }

	$deluserreq1 = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`users` WHERE `users`.`id` = ".$_GET['id']."";
	if(!mysql_query($deluserreq1)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$deluserreq2 = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`activity` WHERE `activity`.`user` = ".$_GET['id']."";
	if(!mysql_query($deluserreq2)) { errorjson("Ошибка базы данных. Запрос выполнен неполностью. Немедленно обратитесь к администратору."); }
	else { errorjson("ok"); }
}

// Загрузка групп департаментов
elseif($_GET['act'] == "getgroups") {
	accessto("s,k");
	if(!$_GET['f']) { wrongusing(); }
	$pregroups = mysql_query("SELECT `id`,`type`,`area`,`name` from `deps` WHERE `type` = 'g' AND `area`='".$_GET['f']."'");
	$grouplist = array();
	while($groups = mysql_fetch_array($pregroups)) {
		$newgroup = array(
			"id" => $groups[0],
			"name" => $groups[3]
		);
		$grouplist[] = $newgroup;
	}
	exit(json_encode($grouplist));
}

// Добавление и редактирование мероприятия
elseif(($_GET['act'] == "addevent") or ($_GET['act'] == "editevent")) {
	accessto("s,k,t");
	if($_GET['act'] == "editevent") {
		if(!$_GET['id']) { wrongusing(); }
		$precheckeid = mysql_query("SELECT `id`,`name`,`holder`,`author`,`date`,`date_for`,`level` from `events` WHERE `id`='".$_GET['id']."' LIMIT 1");
		$checkeid = mysql_fetch_row($precheckeid);
		if(!$checkeid[1]) { errorjson("Мероприятие не найдено"); }

		// Проверка департамента
		$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$checkeid[2]." LIMIT 1;");
		$_ifindep = mysql_fetch_row($_preifindep);

		$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
		$ifindep = mysql_fetch_row($preifindep);

		if((LOGGED_ACCESS !== "s") and ($checkeid[2] !== LOGGED_ID) and ($checkeid[3] !== LOGGED_ID) and ($_ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для редактирования данного мероприятия"); }
	}

	if((!$_GET['name']) or (!$_GET['date_start']) or (!$_GET['level']) or (!$_GET['holder']) or (!$_GET['outside']) or (!$_GET['complex'])) { wrongusing(); }

	$datestart = datecheck($_GET['date_start'], "Дата введена неверно");
	if(!$_GET['date_end']) { $dateend = "NULL"; }
	else {
		$checkdateend = datecheck($_GET['date_end'], "Дата введена неверно");
		if($checkdateend == $datestart) { $dateend = "NULL"; }
		else { $dateend = "'".$checkdateend."'"; }
	}

	//// ПРОВЕРКА ДЕПАРТАМЕНТА и РАЗНИЦЫ МЕЖДУ ДАТОЙ И ВРЕМЕНЕМ
	if(!$_GET['dep']) { $setdep = "NULL"; }
	else { $setdep = "'".$_GET['dep']."'"; }

	if(isset($_GET['time_start']) and isset($_GET['time_finish'])) {
		if((timecheck($_GET['time_start']) === false) or (timecheck($_GET['time_finish']) === false)) { errorjson("Время введено неверно"); }
		$_GET['time_start'] = $_GET['time_start'].":00";
		$_GET['time_finish'] = $_GET['time_finish'].":00";
	} else {
		$_GET['time_start'] = "00:00:00";
		$_GET['time_finish'] = "00:00:00";
	}

	// Уровень мероприятия
	if(($_GET['level'] !== "f") and ($_GET['level'] !== "u") and ($_GET['level'] !== "c") and ($_GET['level'] !== "r") and ($_GET['level'] !== "v") and ($_GET['level'] !== "i")) { wrongusing(); }
	// Ответственный
	$precheckholder = mysql_query("SELECT `id`,`type` from `users` WHERE `type` !='a' AND `id`='".$_GET['holder']."' LIMIT 1");
	$checkholder = mysql_fetch_row($precheckholder);
	if(!$checkholder[1]) { errorjson("Выбранный ответственный не найден"); }
	//if(($checkholder[1] !== "s") and ($checkholder[1] !== "k")) { errorjson("Выбранный ответственный не может быть закреплен за мероприятием."); }

	$fixers = array();
	if(isset($_GET['fixers'])) {
		$getfixers = json_decode(stripslashes($_GET['fixers']));
		if((gettype($getfixers) !== "array") and !$getfixers[0]) { wrongusing(); }
		for($i=0; $i<count($getfixers); $i++) {
			if(!is_numeric($getfixers[$i])) { wrongusing(); }
			$precheckuser = mysql_query("SELECT `id`,`type`,`fullname` from `users` WHERE `id`='".$getfixers[$i]."' AND `type` !='d' LIMIT 1");
			$checkuser = mysql_fetch_row($precheckuser);
			if(!$checkuser[0]) { wrongusing(); }
			$fixers[] = $getfixers[$i];
		}
	}
	$fixers = json_encode($fixers);

	$tags = array();
	if(isset($_GET['tags'])) {
		$gettags = json_decode(stripslashes($_GET['tags']));
		if((gettype($gettags) !== "array") and !$gettags[0]) { wrongusing(); }
		for($i=0; $i<count($gettags); $i++) {
			if(!is_numeric($gettags[$i])) { wrongusing(); }
			$prechecktags = mysql_query("SELECT `id`,`type` from `tags` WHERE `id`='".$gettags[$i]."' AND (`type` ='a' OR `type` ='e') LIMIT 1");
			$tag = mysql_fetch_row($prechecktags);
			if(!$tag[0]) { wrongusing(); }
			$tags[] = $tag[0];
		}
	}
	$tags = json_encode($tags);

	if(!$_GET['comment']) { $comment = "NULL"; }
	else {
		$_GET['comment'] = preg_replace('/#(\w+)/', ' <a target="_blank" href="https://www.instagram.com/explore/tags/$1">$1</a>', $_GET['comment']);
		$comment = "'".htmlentities($_GET['comment'], ENT_QUOTES, "UTF-8")."'"; }
	if(!$_GET['place']) { $setplace = "NULL"; }
	else { $setplace = "'".htmlentities($_GET['place'], ENT_QUOTES, "UTF-8")."'"; }
	$outside = "n";
	if($_GET['outside'] == "true") { $outside = "y"; }
	$complex = "n";
	if($_GET['complex'] == "true") { $complex = "y"; }

	if($_GET['act'] == "addevent") {
		$addeventreq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`events` (`id`, `name`, `place`, `date`, `date_for`, `time_since`, `time_for`, `comment`, `level`, `dep`, `holder`, `created`, `author`,`fixers`,`outside`,`complex`,`tags`) VALUES (NULL, '".htmlentities($_GET['name'], ENT_QUOTES, 'UTF-8')."', ".$setplace.", '".$datestart."', ".$dateend.", '".$_GET['time_start']."', '".$_GET['time_finish']."', ".$comment.", '".$_GET['level']."', ".$setdep.", '".$_GET['holder']."', '".date("Y-m-d")." ".date("H:i:s")."', '".LOGGED_ID."', '".$fixers."','".$outside."','".$complex."','".$tags."');";
		if(!mysql_query($addeventreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
		else { errorjson("ok"); }
	}
	elseif($_GET['act'] == "editevent") {
		$addeventreq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`events` SET  `name` =  '".htmlentities($_GET['name'], ENT_QUOTES, 'UTF-8')."',
`place` =  ".$setplace.",
`date` =  '".$datestart."',
`date_for` =  ".$dateend.",
`time_since` =  '".$_GET['time_start']."',
`time_for` =  '".$_GET['time_finish']."',
`comment` =  ".$comment.",
`dep` = ".$setdep.",
`level` =  '".$_GET['level']."',
`holder` =  '".$_GET['holder']."',
`fixers` = '".$fixers."',
`outside` = '".$outside."',
`complex` = '".$complex."',
`tags` = '".$tags."' WHERE  `events`.`id` =".$_GET['id'].";";
		if(!mysql_query($addeventreq)) { errorjson("Ошибка базы данных. Повторите попытку позже. #2 "); }
		// Пересчет баллов у вовлеченных
		$pregetactive = mysql_query("SELECT `id`,`user`,`event`,`role` from `activity` WHERE `event`='".$_GET['id']."'");
		if(mysql_num_rows($pregetactive) > 0) {
			while($getactive = mysql_fetch_array($pregetactive)) {
				countStudentsRating($getactive[1]);
			}
		}
		errorjson("ok");
	}
}

// Удаление мероприятия
elseif($_GET['act'] == "delevent") {
	accessto("s,k");
	if(!$_GET['eid']) { wrongusing(); }
	$precheckeid = mysql_query("SELECT `id`,`name`,`holder`,`author`,`tags` from `events` WHERE `id`='".$_GET['eid']."' LIMIT 1");
	$checkeid = mysql_fetch_row($precheckeid);
	if(!$checkeid[1]) { errorjson("Мероприятие не найдено"); }

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$checkeid[2]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ACCESS !== "s") and ($checkeid[2] !== LOGGED_ID) and ($checkeid[3] !== LOGGED_ID) and ($ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для удаления данного мероприятия"); }

	$pregetactive = mysql_query("SELECT `id`,`user`,`event`,`role`,`complex` from `activity` WHERE `event`='".$_GET['eid']."'");
	if(mysql_num_rows($pregetactive) > 0) {
		while($getactive = mysql_fetch_array($pregetactive)) {
			$decount = activityPoints($getactive[2],$getactive[3],$getactive[4]);
			$_precheckeid = mysql_query("SELECT `id`,`date` from `events` WHERE `id`='".$_GET['eid']."' LIMIT 1");
			$_checkeid = mysql_fetch_row($_precheckeid);
			if(isThisAcademicYear($_checkeid[1]) == true) {

				$sqlcountByTags = "";
				$countByTags = json_decode($checkeid[4]);
				$pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a';");
				while($getByATags = mysql_fetch_array($pregetByATags)) {
					if(in_array($getByATags[0], $countByTags)) {
						$sqlcountByTags .= ", `ic_".$getByATags[0]."` = `ic_".$getByATags[0]."` -".$decount."";
					}
				}

				if(!mysql_query("UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `count` = `count` -".$decount."".$sqlcountByTags." WHERE  `users`.`id` =".$getactive[1].";")) {
					errorjson("Ошибка базы данных. Повторите попытку позже. #1");
				}
			}
		}
	}

	if(!mysql_query("DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`events` WHERE `events`.`id` = ".$_GET['eid'].";")) { errorjson("Ошибка базы данных. Повторите попытку позже. #2"); }
	if(!mysql_query("DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`activity` WHERE `activity`.`event` = ".$_GET['eid'].";")) { errorjson("Ошибка базы данных. Повторите попытку позже. #3"); }
	else { errorjson("ok"); }
}

// Добавление студента в мероприятие
elseif($_GET['act'] == "addactive") {
	//sleep(16);
	accessto("s,k,t");
	if((!$_GET['eid']) or (!$_GET['uid']) or (!$_GET['rid'])) { wrongusing(); }
	$precheckeid = mysql_query("SELECT `id`,`name`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `id`='".$_GET['eid']."' LIMIT 1");
	$checkeid = mysql_fetch_row($precheckeid);
	if(!$checkeid[1]) { errorjson("Мероприятие не найдено"); }

	$checkfixers = json_decode($checkeid[4]);
	$pos = array_search(LOGGED_ID, $checkfixers);

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$checkeid[2]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ACCESS !== "s") and ($checkeid[2] !== LOGGED_ID) and ($checkeid[3] !== LOGGED_ID) and ($pos === false) and ($_ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для регистрации студента на данное мероприятие"); }

	if(preg_match('/^[0-9]{10}$/', $_GET['uid'])) {
		if(!checkbc($_GET['uid'])) { errorjson("a_wrongcode"); }
		$precheckuid = mysql_query("SELECT `id`,`phone` from `users` WHERE `code`='".$_GET['uid']."' AND `type`='a' LIMIT 1");
	} else {
		$precheckuid = mysql_query("SELECT `id`,`phone` from `users` WHERE `id`='".$_GET['uid']."' AND `type`='a' LIMIT 1");
	}
	$checkuid = mysql_fetch_row($precheckuid);

	if(!$checkuid[1]) { errorjson("a_notexist"); } // не найден или не студент

	$precheckaid = mysql_query("SELECT `id`,`event`,`user`,`role`,`created`,`addedby` from `activity` WHERE `event`='".$_GET['eid']."' AND `user`='".$checkuid[0]."' LIMIT 1;");
	$checkaid = mysql_fetch_row($precheckaid);

	if(!$checkaid[1]) {
		$countup = activityPoints($_GET['eid'],$_GET['rid'],'n');
		$curtime = date("Y-m-d")." ".date("H:i:s");

		$addactivereq1 = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`activity` (`id`, `event`, `user`, `role`, `created`, `addedby`, `complex`) VALUES (NULL, '".$_GET['eid']."', '".$checkuid[0]."', '".$_GET['rid']."', '".$curtime."', '".LOGGED_ID."', 'n');";
		if(!mysql_query($addactivereq1)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

		$_precheckeid = mysql_query("SELECT `id`,`date` from `events` WHERE `id`='".$_GET['eid']."' LIMIT 1");
		$_checkeid = mysql_fetch_row($_precheckeid);
		if(isThisAcademicYear($_checkeid[1]) == true) {

			$sqlcountByTags = "";
			$countByTags = json_decode($checkeid[5]);
			$pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a';");
			while($getByATags = mysql_fetch_array($pregetByATags)) {
				if(in_array($getByATags[0], $countByTags)) {
					$sqlcountByTags .= ", `ic_".$getByATags[0]."` = `ic_".$getByATags[0]."` +".$countup."";
				}
			}

			$addactivereq2 = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `count` = `count` +".$countup."".$sqlcountByTags." WHERE  `users`.`id` =".$checkuid[0].";";
			if(!mysql_query($addactivereq2)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
		}

		$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`sex`,`groupnum` from `users` WHERE `id` ='".$checkuid[0]."' LIMIT 1;");
		$getuser = mysql_fetch_row($pregetuser);
		if(mb_strlen($getuser[3], "UTF-8")<2) {
			$name = mb_substr($getuser[2], 0, 1, "UTF-8").".".$getuser[1];
		} else {
			$name = mb_substr($getuser[2], 0, 1, "UTF-8").".".mb_substr($getuser[3], 0, 1, "UTF-8").".".$getuser[1];
		}

		$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
		$getdep = mysql_fetch_row($pregetdep);
		$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
		$getfac = mysql_fetch_row($pregetfac);

		list($edul, $educ) = split('[-]', $getuser[5]);
		if($edul == "m") { $educ .= "м"; }

		$afrom = $getfac[1]."(".$getdep[2].")-".$getuser[7];

		list($ii1, $ii2) = split(' ', $curtime);
		list($addyear, $addmonth, $addday) = split('[-]', $ii1);
		$added_dt = $addday.'.'.$addmonth.'.'.substr($addyear, 2, 4).' '.substr($ii2, 0, 5);

		$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id` ='".LOGGED_ID."' LIMIT 1;");
		$getholder = mysql_fetch_row($pregetuser);
		$holder = mb_substr($getholder[2], 0, 1, "UTF-8").".".mb_substr($getholder[3], 0, 1, "UTF-8").".".$getholder[1];

		$prechecka = mysql_query("SELECT `id`,`event`,`user` from `activity` WHERE `event`='".$_GET['eid']."' AND `user`='".$checkuid[0]."' LIMIT 1");
		$checka = mysql_fetch_row($prechecka);

		$newinlist = array(
			"a_id" => $checka[0],
			"a_uid" => $checka[2],
			"a_name" => $name,
			"a_sex" => $getuser[6],
			"a_from" => $afrom,
			"a_role" => $_GET['rid'],
			"a_time" => $added_dt,
			"a_by" => $holder
		);
		$sendlist["error"] = "ok";
	} else {
		$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`sex`,`groupnum` from `users` WHERE `id` ='".$checkaid[2]."' LIMIT 1;");
		$getuser = mysql_fetch_row($pregetuser);
		$name = mb_substr($getuser[2], 0, 1, "UTF-8").".".mb_substr($getuser[3], 0, 1, "UTF-8").".".$getuser[1];

		$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
		$getdep = mysql_fetch_row($pregetdep);
		$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
		$getfac = mysql_fetch_row($pregetfac);

		list($edul, $educ) = split('[-]', $getuser[5]);
		if($edul == "m") { $educ .= "м"; }

		$afrom = $getfac[1]."(".$getdep[2].")-".$getuser[7];

		$newinlist = array(
			"a_id" => $checkaid[0],
			"a_uid" => $checkaid[2],
			"a_name" => $name,
			"a_sex" => $getuser[6],
			"a_from" => $afrom,
			"a_role" => $checkaid[3]
		);
		$sendlist["error"] = "a_already";
	}

	$sendlist["gr"] = $newinlist;
	exit(json_encode($sendlist));
}

// Редактирование роли
elseif($_GET['act'] == "editrole") {
	accessto("s,k,t");
	if((!$_GET['aid']) or (!$_GET['rid']) or (!$_GET['cv'])) { wrongusing(); }
	$pregetactive = mysql_query("SELECT `id`,`event`,`role`,`addedby`,`user`,`complex` from `activity` WHERE `id`='".$_GET['aid']."' LIMIT 1");
	$getactive = mysql_fetch_row($pregetactive);
	if(!$getactive[0]) { errorjson("Студент не зарегистрирован на это мероприятие. Обновите страницу."); }

	$pregetifh = mysql_query("SELECT `id`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `id`='".$getactive[1]."' LIMIT 1");
	$getifh = mysql_fetch_row($pregetifh);

	$checkfixers = json_decode($getifh[3]);
	$pos = array_search(LOGGED_ID, $checkfixers);

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$getifh[1]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ACCESS !== "s") and ($getifh[1] !== LOGGED_ID) and ($getifh[2] !== LOGGED_ID) and ($pos === false) and ($_ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для внесения изменений"); }
	//if($_GET['rid'] == $getactive[2]) { errorjson("ok"); }

	if((LOGGED_ACCESS !== "s") and ($getifh[1] !== LOGGED_ID) and ($getifh[2] !== LOGGED_ID) and ($_ifindep[1] !== $ifindep[1])) {
		if($checkfixers[$pos] !== $getactive[3]) { errorjson("Роль данного студента может изменить только сотрудник организационного подразделения, фиксатор, который внес его в это мероприятие, или ответственный за мероприятие."); }
	}

	if($_GET['cv'] == "true") { $_GET['cv'] = "y"; } else { $_GET['cv'] = "n"; }

	$decount = activityPoints($getactive[1],$getactive[2],$getactive[5]);
	$recount = activityPoints($getactive[1],$_GET['rid'],$_GET['cv']);
	$jhgjh = $getactive[1].", ".$getactive[2].", ".$getactive[5]." - ".$getactive[1].", ".$_GET['rid'].", ".$_GET['cv'];

	$recountreq1 = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`activity` SET  `role` =  '".$_GET['rid']."', `complex` =  '".$_GET['cv']."' WHERE  `activity`.`id` =".$_GET['aid'].";";
	if(!mysql_query($recountreq1)) { errorjson("Ошибка базы данных. Повторите попытку позже. #1"); }

	$_precheckeid = mysql_query("SELECT `id`,`date` from `events` WHERE `id`='".$getactive[1]."' LIMIT 1");
	$_checkeid = mysql_fetch_row($_precheckeid);
	if(isThisAcademicYear($_checkeid[1]) == true) {

		$sqlcountByTags = "";
		$countByTags = json_decode($getifh[4]);
		$pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a';");
		while($getByATags = mysql_fetch_array($pregetByATags)) {
			if(in_array($getByATags[0], $countByTags)) {
				$sqlcountByTags .= ", `ic_".$getByATags[0]."` = `ic_".$getByATags[0]."` -".$decount." +".$recount."";
			}
		}

		$recountreq2 = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `count` = `count` -".$decount." +".$recount."".$sqlcountByTags." WHERE  `users`.`id` =".$getactive[4].";";
		if(!mysql_query($recountreq2)) { errorjson("Ошибка базы данных. Повторите попытку позже. #2"); }
	}
	errorjson("ok");
}

// Удаление студента из мероприятия
elseif($_GET['act'] == "delactive") {
	accessto("s,k,t");
	if(!$_GET['aid']) { wrongusing(); }
	$pregetactive = mysql_query("SELECT `id`,`event`,`user`,`role`,`addedby`,`complex` from `activity` WHERE `id`='".$_GET['aid']."' LIMIT 1");
	$getactive = mysql_fetch_row($pregetactive);
	if(!$getactive[3]) { errorjson("Студент не зарегистрирован на это мероприятие. Обновите страницу."); }

	$pregetifh = mysql_query("SELECT `id`,`holder`,`author`,`fixers`,`tags` from `events` WHERE `id`='".$getactive[1]."' LIMIT 1");
	$getifh = mysql_fetch_row($pregetifh);

	$checkfixers = json_decode($getifh[3]);
	$pos = array_search(LOGGED_ID, $checkfixers);

	// Проверка департамента
	$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$getifh[1]." LIMIT 1;");
	$_ifindep = mysql_fetch_row($_preifindep);

	$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
	$ifindep = mysql_fetch_row($preifindep);

	if((LOGGED_ACCESS !== "s") and ($getifh[1] !== LOGGED_ID) and ($getifh[2] !== LOGGED_ID) and ($pos === false) and ($_ifindep[1] !== $ifindep[1])) { errorjson("У Вас недостаточно прав для удаления студента из данного мероприятия"); }

	if((LOGGED_ACCESS !== "s") and ($getifh[1] !== LOGGED_ID) and ($getifh[2] !== LOGGED_ID) and ($_ifindep[1] !== $ifindep[1])) {
		if($checkfixers[$pos] !== $getactive[4]) { errorjson("Данного студента может удалить только сотрудник организационного подразделения, фиксатор, который внес его в это мероприятие, или ответственный за мероприятие."); }
	}

	$decount = activityPoints($getactive[1],$getactive[3],$getactive[5]);
	$curcount = mysql_fetch_row(mysql_query("SELECT `id`,`count` from `users` WHERE `id`=".$getactive[2]." LIMIT 1"));
	$setcount = $curcount[1] - $decount;

	$delactivereq = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`activity` WHERE `activity`.`id` =".$_GET['aid'].";";
	if(!mysql_query($delactivereq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	$_precheckeid = mysql_query("SELECT `id`,`date` from `events` WHERE `id`='".$getactive[1]."' LIMIT 1");
	$_checkeid = mysql_fetch_row($_precheckeid);
	if(isThisAcademicYear($_checkeid[1]) == true) {

		$sqlcountByTags = "";
		$countByTags = json_decode($getifh[4]);
		$pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a';");
		while($getByATags = mysql_fetch_array($pregetByATags)) {
			if(in_array($getByATags[0], $countByTags)) {
				$sqlcountByTags .= ", `ic_".$getByATags[0]."` = `ic_".$getByATags[0]."` -".$decount."";
			}
		}

		$updatereq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET  `count` =  '".$setcount."'".$sqlcountByTags." WHERE  `users`.`id` =".$getactive[2].";";
		if(!mysql_query($updatereq)) { errorjson("Ошибка базы данных. Повторите попытку позже. Запрос выполнен неполностью. Немедленно обратитесь к администратору."); }
	}
	errorjson("ok");
}

// Вывод активности студента
elseif($_GET['act'] == "getactive") {
	accessto("s,k,t,a");
	if((!$_GET['by']) or (!$_GET['uid'])) { wrongusing(); }

	// По карте, идентификатору, SIN'у
	if($_GET['by'] == "b") { $getby = 'code'; }
	elseif($_GET['by'] == "i") { $getby = 'id'; }
	elseif($_GET['by'] == "sin") { $getby = 'sin'; }
	else { wrongusing(); }

	$pregetstudent = mysql_query("SELECT `id`,`phone`,`code`,`sname`,`fname`,`pname`,`sex`,`birthday`,`dep`,`curcourse`,`created`,`addedby`,`groupnum`,`budget`,`count`,`sin`,`gen`,`out`,`groups` from `users` WHERE `".$getby."`='".$_GET['uid']."' AND `type`='a' LIMIT 1");
	$getstudent = mysql_fetch_row($pregetstudent);
	if(!$getstudent[1]) { errorjson("s_notexist"); } // не найден или не студент
	if(LOGGED_ACCESS == "s") {
		$pregetcreator = mysql_query("SELECT `id`,`sname`,`fname` from `users` WHERE `id`='".$getstudent[11]."' LIMIT 1");
		$getcreator = mysql_fetch_row($pregetcreator);
		if(!$getcreator[1]) { $addedinfo = "".$getstudent[10]." (регистратор неизветен)"; }
		else { $addedinfo = "".$getstudent[10]." (".$getcreator[2]." ".$getcreator[1].")"; }
	}
	else { $getstudent[2] = "not"; $addedinfo = "not"; }

	$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getstudent[8]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
	$getfac = mysql_fetch_row($pregetfac);

	$birthday = "unknown";
	if($getstudent[7] !== NULL) {
		list($rbyear, $rbmonth, $rbday) = split('[-]', $getstudent[7]);
		$birthday = $rbday.'.'.$rbmonth.'.'.$rbyear;
	}

	list($ii1, $ii2) = split(' ', $getstudent[10]);
	list($addyear, $addmonth, $addday) = split('[-]', $ii1);
	$added_date = $addday.'.'.$addmonth.'.'.$addyear;

	// бакалавриат: b-1, специалитет: s-1, магистратура: m-1
	list($edul, $educ) = split('[-]', $getstudent[9]);

	$mylists = array();
	$ownlist = json_decode($getstudent[18]);
	if(count($ownlist) !== 0) {
		for($i=0; $i<count($ownlist); $i++) {
			$pregetlists = mysql_query("SELECT `id`,`name`,`icon`,`public`,`content` from `lists` WHERE `id`='".$ownlist[$i]."' AND `public`='y' AND `icon`!='n' LIMIT 1;");
			$lists = mysql_fetch_row($pregetlists);
			if(!$lists[0]) { continue; }
			$list_members = json_decode($lists[4]);
			$members = array();
			for($c=0; $c<count($list_members); $c++) {
				$members[] = $list_members[$c][0];
			}
			$pos = array_search($getstudent[0], $members);
			$mylists[] = array($lists[0],$lists[1],$list_members[$pos][1],$lists[2]);
		}
	}

	$readyinfo = array(
		"id" => $getstudent[0],
		"out" => $getstudent[17],
		"sinid" => $getstudent[15],
		"phone" => $getstudent[1],
		"code" => $getstudent[2],
		"surname" => $getstudent[3],
		"firstname" => $getstudent[4],
		"patronymic" => $getstudent[5],
		"sex" => $getstudent[6],
		"birthday" => $birthday,
		"department" => $getfac[1],
		"department_id" => $getfac[0],
		"group" => $getdep[2],
		"group_id" => $getdep[0],
		"edulevel" => $edul,
		"educourse" => $educ,
		"gen" => $getstudent[16],
		"groupnum" => $getstudent[12],
		"budget" => $getstudent[13],
		"points" => $getstudent[14],
		"lists" => $mylists,
		"added" => $addedinfo,
		"added_date" => $added_date,
		"error" => "ok"
	);
	if(LOGGED_ACCESS == "t" or LOGGED_ACCESS == "a") { unset($readyinfo["phone"]); unset($readyinfo["birthday"]); unset($readyinfo["code"]); }
	if(LOGGED_ACCESS == "a") { unset($readyinfo["sinid"]); unset($readyinfo["group"]); unset($readyinfo["group_id"]); unset($readyinfo["groupnum"]); unset($readyinfo["budget"]); }
	if(LOGGED_ACCESS !== "s") {
		unset($readyinfo["added"]);
		unset($readyinfo["added_date"]);
	}

	$pregetactive = mysql_query("SELECT `id`,`user`,`event`,`role`,`complex` from `activity` WHERE `user`='".$getstudent[0]."'  ORDER BY `id` DESC");
	while($active = mysql_fetch_array($pregetactive)) {
		$preevent = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`holder`,`author`,`complex` from `events` WHERE `id`='".$active[2]."' LIMIT 1");
		$event = mysql_fetch_row($preevent);

		$pregetholder = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id`='".$event[4]."' LIMIT 1;");
		$getholder = mysql_fetch_row($pregetholder);
		$holder = mb_substr($getholder[2], 0, 1, "UTF-8").".".mb_substr($getholder[3], 0, 1, "UTF-8").".".$getholder[1];

		$countit = activityPoints($active[2],$active[3],$active[4]);

		list($d1, $d2, $d3) = split('[-]', $event[2]);
		$d_since = $d3.".".$d2.".".substr($d1, 2, 4);
		if($event[3] !== NULL) {
			list($d1, $d2, $d3) = split('[-]', $event[3]);
			$event[3] = $d3.".".$d2.".".substr($d1, 2, 4);
		}

		if((LOGGED_ID == $event[4]) or (LOGGED_ID == $event[5]) or (LOGGED_ACCESS == "s")) { $isedit = "yes"; } else { $isedit = "no"; }
		$newactive = array(
			"eid" => $event[0],
			"aid" => $active[0],
			"date_since" => $d_since,
			"date_for" => $event[3],
			"name" => $event[1],
			"holder" => $holder,
			"role" => $active[3],
			"complex" => $active[4],
			"points" => $countit,
			"isedit" => $isedit
		);
		$readyinfo["events"][] = $newactive;
		unset($newactive);
	}

	exit(json_encode($readyinfo));
}

// Вывод информации о пользователе
elseif($_GET['act'] == "byuser") {
	accessto("s");
	if(!$_GET['uid']) { wrongusing(); }

	$pregetuser = mysql_query("SELECT `id`,`type`,`phone`,`sname`,`fname`,`pname`,`sex`,`birthday`,`dep`,`post`,`created`,`addedby` from `users` WHERE `id`='".$_GET['uid']."' AND `type` !='a' LIMIT 1");
	$getuser = mysql_fetch_row($pregetuser);
	if(!$getuser[1]) { errorjson("u_notexist"); } // не найден или не пользоваель

	$pregetcreator = mysql_query("SELECT `id`,`sname`,`fname` from `users` WHERE `id`='".$getuser[11]."' LIMIT 1");
	$getcreator = mysql_fetch_row($pregetcreator);
	if(!$getcreator[1]) { $addedinfo = "".$getuser[10]." (регистратор неизветен)"; }
	else { $addedinfo = "".$getuser[10]." (".$getcreator[2]." ".$getcreator[1].")"; }

	$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getuser[8]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);

	$birthday = "unknown";
	if($getstudent[7] !== NULL) {
		list($rbyear, $rbmonth, $rbday) = split('[-]', $getuser[7]);
		$birthday = $rbday.'.'.$rbmonth.'.'.$rbyear;
	}

	$readyinfo = array(
		"id" => $getuser[0],
		"type" => $getuser[1],
		"phone" => $getuser[2],
		"surname" => $getuser[3],
		"firstname" => $getuser[4],
		"patronymic" => $getuser[5],
		"sex" => $getuser[6],
		"birthday" => $birthday,
		"department_name" => $getdep[1],
		"department_id" => $getdep[0],
		"post" => $getuser[9],
		"added" => $addedinfo,
		"error" => "ok"
	);

	$preevent = mysql_query("SELECT `id`,`name`,`date`,`date_for`,`holder`,`level` from `events` WHERE `holder`='".$getuser[0]."' ORDER BY `date` DESC");
	while($event = mysql_fetch_array($preevent)) {
		$preenum = mysql_query("SELECT `id`,`event` from `activity` WHERE `event`='".$event[0]."'");
		$enum = mysql_num_rows($preenum);

		list($d1, $d2, $d3) = split('[-]', $event[2]);
		$d_since = $d3.".".$d2.".".substr($d1, 2, 4);
		if($event[3] !== NULL) {
			list($d1, $d2, $d3) = split('[-]', $event[3]);
			$event[3] = $d3.".".$d2.".".substr($d1, 2, 4);
		}

		if((LOGGED_ID == $event[4]) or (LOGGED_ACCESS == "s")) { $isedit = "yes"; } else { $isedit = "no"; }
		$newevent = array(
			"e_id" => $event[0],
			"e_date_since" => $d_since,
			"e_date_for" => $event[3],
			"e_name" => $event[1],
			"e_level" => $event[5],
			"e_involved" => $enum
		);
		$readyinfo["events"][] = $newevent;
		unset($newevent);
	}

	exit(json_encode($readyinfo));
}

// Пересчет
elseif($_GET['act'] == "recount") {
	accessto("s");
	$prestudpoints = mysql_query("SELECT `id` from `users` WHERE `type`='a'");
	while($studpoints = mysql_fetch_array($prestudpoints)) {
		countStudentsRating($studpoints[0]);
	}
	errorjson("ok");
}

// Добавление и редактирование департаментов
elseif(($_GET['act'] == "depadd") or ($_GET['act'] == "depedit")) {
	accessto("s");
	if((!$_GET['type']) or (!$_GET['name'])) { wrongusing(); }
	if(($_GET['type'] !== "i") and ($_GET['type'] !== "d")) { wrongusing(); }

	if($_GET['act'] == "depedit") {
		if(!$_GET['id']) { wrongusing(); }
		$precheckdep = mysql_query("SELECT `id`,`type` from `deps` WHERE `id`='".$_GET['id']."' LIMIT 1");
		$checkdep = mysql_fetch_row($precheckdep);
		if(!$checkdep[1]) { errorjson("Подразделение не найдено"); }
		$depaddreq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`deps` SET  `type` = '".$_GET['type']."', `name` = '".htmlentities($_GET['name'], ENT_QUOTES, "UTF-8")."' WHERE  `deps`.`id` =".$_GET['id'].";";
	}
	elseif($_GET['act'] == "depedit") {
		$depaddreq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`deps` (`id`, `type`, `name`) VALUES (NULL, '".$_GET['type']."', '".$_GET['name']."');";
	}

	if(!mysql_query($depaddreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	else { errorjson("ok"); }
}

// Удаление департаментов
elseif($_GET['act'] == "depdel") {
	accessto("s");
	if(!$_GET['id']) { wrongusing(); }
	$precheckdep = mysql_query("SELECT `id`,`type` from `deps` WHERE `id`='".$_GET['id']."' LIMIT 1");
	$checkdep = mysql_fetch_row($precheckdep);
	if(!$checkdep[1]) { errorjson("Подразделение не найдено"); }

	$precheckuser = mysql_query("SELECT `id`,`phone` from `users` WHERE `dep`='".$_GET['id']."' LIMIT 1");
	$checkuser = mysql_fetch_row($precheckuser);
	if($checkuser[1]) { errorjson("Удаление невозможно, т.к. в системе зарегистрированы пользователи, принадлежащие к данному подразделению."); }

	$depdelreq = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`deps` WHERE `deps`.`id` = ".$_GET['id'].";";
	if(!mysql_query($depdelreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	else { errorjson("ok"); }
}

// Изменение пароля со стороны пользователя
elseif($_GET['act'] == "cpw") {
	accessto("s,k,t,a");
	if(!$_GET['new']) { wrongusing(); }
	if(mb_strlen($_GET['new'],"UTF-8") !== 32) { wrongusing(); }

	$precheckuid = mysql_query("SELECT `id`,`password` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$checkuid = mysql_fetch_row($precheckuid);
	// Пароль изменяется не в первый раз
	if(strlen($checkuid[1]) >= 6) {
		if(!$_GET['old']) { wrongusing(); }
		if($_GET['old'] !== $checkuid[1]) { errorjson("Текущий пароль введен неверно"); }
	}
	if(strlen($_GET['new']) !== 32) { wrongusing(); }
	//if(!pwcheck($_GET['new'])) { errorjson("Неверный формат пароля"); }
	$authreq = "UPDATE `".$GLOBALS['config']['mysql_db']."`.`users` SET `password` = '".$_GET['new']."' WHERE `users`.`id` =".LOGGED_ID.";";
	if(!mysql_query($authreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
}

// Генерирование нового пароля / Восстановление доступа
elseif($_GET['act'] == "genpw") {
	accessto("s,k");
	if((!$_GET['uid']) or (!$_GET['as'])) { wrongusing(); }
	if(($_GET['as'] !== "y") and ($_GET['as'] !== "n")) { wrongusing(); }
	$precheckuid = mysql_query("SELECT `id`,`phone`,`password`,`sin` from `users` WHERE `id`='".$_GET['uid']."' LIMIT 1");
	$checkuid = mysql_fetch_row($precheckuid);
	if(!$checkuid[1]) { errorjson("Пользователь не найден"); }

	$gennewpw = pwgenerator();
	$newpwreq = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`users` SET `password` = '".$gennewpw."' WHERE  `users`.`id` =".$_GET['uid'].";";
	if($_GET['as'] == "y") {
		$notifyres = sendsms($checkuid[1], "Сайт: ".PROTOCOL.$_SERVER['SERVER_NAME']." Логин: ".$checkuid[3]." Пароль: ".$gennewpw."");
		if($notifyres !== "sent") { exit('{"error":"ok_notify","notifyres":"'.$notifyres.'"}'); }
	} else {
		$ok_display = array(
			"error" => "ok_display",
			"l" => $checkuid[3],
			"p" => $gennewpw
		);
		if(!mysql_query($newpwreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
		exit(json_encode($ok_display));
	}
	if(!mysql_query($newpwreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
}

// Получение шаблона служебной записки
elseif($_GET['act'] == "gettempsz") {
	accessto("s,k,t");
	if(!$_GET['id']) { wrongusing(); }

	$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$tmpholder = mysql_fetch_row($pretmpholder);
	$gettemps = mysql_query("SELECT `id`,`header`,`title`,`post`,`sign`,`content`,`area` from `temp_sz` WHERE `id`='".$_GET['id']."' AND (`area`='".$tmpholder[1]."' OR `holder`='".LOGGED_ID."') LIMIT 1;");
	$temp = mysql_fetch_row($gettemps);
	if(!$temp[1]) { errorjson("Шаблон не найден"); }
	$readyinfo = array(
		"id" => $temp[0],
		"temp1" => $temp[1],
		"temp2" => $temp[2],
		"temp3" => $temp[3],
		"temp4" => $temp[4],
		"temp5" => $temp[5],
		"error" => "ok"
	);
	exit(json_encode($readyinfo));
}

// Сохранение в текущем шаблоне
elseif($_GET['act'] == "savetempsz") {
	accessto("s,k,t");
	if((!$_GET['id']) or (!$_GET['temp1']) or (!$_GET['temp2']) or (!$_GET['temp3']) or (!$_GET['temp4']) or (!$_GET['temp5'])) { wrongusing(); }

	$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$tmpholder = mysql_fetch_row($pretmpholder);
	$gettemps = mysql_query("SELECT `id`,`area` from `temp_sz` WHERE `id`='".$_GET['id']."' AND `area`='".$tmpholder[1]."' LIMIT 1;");
	$temp = mysql_fetch_row($gettemps);
	if(!$temp[1]) { errorjson("Шаблон не найден"); }

	$edittemp = "UPDATE  `".$GLOBALS['config']['mysql_db']."`.`temp_sz` SET
	`header` = '".htmlentities($_GET['temp1'], ENT_QUOTES, "UTF-8")."',
	`title` =  '".htmlentities($_GET['temp2'], ENT_QUOTES, "UTF-8")."',
	`post` =  '".htmlentities($_GET['temp3'], ENT_QUOTES, "UTF-8")."',
	`sign` =  '".htmlentities($_GET['temp4'], ENT_QUOTES, "UTF-8")."',
	`content` =  '".htmlentities($_GET['temp5'], ENT_QUOTES, "UTF-8")."'
	 WHERE  `temp_sz`.`id` =".$_GET['id'].";";
	if(!mysql_query($edittemp)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
}

// Создание нового шаблона
elseif($_GET['act'] == "newtempsz") {
	accessto("s,k,t");
	if((!$_GET['name']) or (!$_GET['share']) or (!$_GET['temp1']) or (!$_GET['temp2']) or (!$_GET['temp3']) or (!$_GET['temp4']) or (!$_GET['temp5'])) { wrongusing(); }

	$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$tmpholder = mysql_fetch_row($pretmpholder);

	if($_GET['share'] == "false") { $setholder = "n"; }
	else { $setholder = $tmpholder[1]; }

	$prefindsame = mysql_query("SELECT `id`,`name` from `temp_sz` WHERE `name`='".$_GET['name']."' AND `area`='".$tmpholder[1]."' LIMIT 1;");
	$findsame = mysql_fetch_row($prefindsame);
	if($findsame[1]) { errorjson("Шаблон с таким названием уже существует"); }

	$newtemp = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`temp_sz` (`id`, `name`, `header`, `title`, `post`, `sign`, `content`, `holder`, `area`)
	 VALUES (NULL, '".$_GET['name']."', '".htmlentities($_GET['temp1'], ENT_QUOTES, "UTF-8")."', '".htmlentities($_GET['temp2'], ENT_QUOTES, "UTF-8")."', '".htmlentities($_GET['temp3'], ENT_QUOTES, "UTF-8")."', '".htmlentities($_GET['temp4'], ENT_QUOTES, "UTF-8")."', '".htmlentities($_GET['temp5'], ENT_QUOTES, "UTF-8")."', '".LOGGED_ID."', '".$setholder."');";
	if(!mysql_query($newtemp)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	$sendlist["newid"] = mysql_insert_id();
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Удаление шаблона
elseif($_GET['act'] == "deltempsz") {
	accessto("s,k,t");
	if(!$_GET['id']) { wrongusing(); }

	$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$tmpholder = mysql_fetch_row($pretmpholder);
	$gettemps = mysql_query("SELECT `id`,`area` from `temp_sz` WHERE `id`='".$_GET['id']."' AND (`area`='".$tmpholder[1]."' OR `holder`='".LOGGED_ID."') LIMIT 1;");
	$temp = mysql_fetch_row($gettemps);
	if(!$temp[1]) { errorjson("Шаблон не найден"); }

	$deltemp = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`temp_sz` WHERE `temp_sz`.`id` = ".$_GET['id']."";
	if(!mysql_query($deltemp)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	errorjson("ok");
}

// Получение списка вовлеченных по роли
elseif($_GET['act'] == "getinvolvedsz") {
	accessto("s,k,t");

	if((!$_GET['eid']) or (!$_GET['roles'])) { wrongusing(); }

	$forecheck = mysql_query("SELECT `id`,`name` from `events` WHERE `id`=".$_GET['eid']." LIMIT 1;");
	$echeck = mysql_fetch_row($forecheck);
	if(!$echeck[1]) { errorjson("Мероприятие не найдено"); }

	$rolelist = str_split(htmlentities($_GET['roles'], ENT_QUOTES, "UTF-8"));

	$roles = array(
		"b" => 0,	/* Без роли */
		"u" => 0,	/* Участник */
		"p" => 0,	/* Призер */
		"w" => 0,	/* Победитель */
		"l" => 0,	/* Помощник организатора */
		"m" => 0,	/* Организатор */
		"h" => 0,	/* Главный организатор */
	);

	$rolesql = "";
	for($i = 0; $i < count($rolelist); $i++) {
		if(!array_key_exists($rolelist[$i], $roles)) { errorjson("Неверно задана роль"); }
		$rolesql .= "`role` = '".$rolelist[$i]."'";
		if($i !== (count($rolelist)-1)) { $rolesql .= " OR "; }
	}

	$pregetlist = mysql_query("SELECT `id`,`event`,`user`,`role` from `activity` WHERE `event` ='".$echeck[0]."' AND (".$rolesql.") ORDER BY `id` DESC;");
	$getnum = mysql_num_rows($pregetlist);

	$sendlist = Array();

	while($erend = mysql_fetch_array($pregetlist)) {
		$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum`,`gen` from `users` WHERE `id` ='".$erend[2]."' LIMIT 1;");
		$getuser = mysql_fetch_row($pregetuser);

		$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
		$getdep = mysql_fetch_row($pregetdep);
		$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
		$getfac = mysql_fetch_row($pregetfac);

		list($edul, $educ) = split('[-]', $getuser[5]);
		if($edul == "c") { $edul = "1"; }
		if($edul == "b") { $edul = "2"; }
		if($edul == "m") { $edul = "3"; }
		if($edul == "s") { $edul = "4"; }
		$educode = $getuser[7].".".$edul.".1"; // .1 - очная форма

		$newinlist = array(
			"a_id" => $getuser[0],
			"a_sname" => $getuser[1],
			"a_fname" => $getuser[2],
			"a_pname" => $getuser[3],
			"a_educode" => $educode,
			"a_course" => $educ,
			"a_dep" => $getfac[1],
			"a_group" => $getdep[2],
			"a_gnum" => $getuser[6],
			"a_role" => $erend[3]
		);
		$sendlist["alist"][] = $newinlist;
		unset($newinlist);
	}

	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

elseif($_GET['act'] == "getstudentsz") {
	accessto("s,k,t");

	if(!$_GET['sid']) { wrongusing(); }

	$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum`,`sex`,`gen` from `users` WHERE `id` ='".$_GET['sid']."' LIMIT 1;");
	$getuser = mysql_fetch_row($pregetuser);
	if(!$getuser[1]) { errorjson("Пользователь не найден"); }

	$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
	$getfac = mysql_fetch_row($pregetfac);

	list($edul, $educ) = split('[-]', $getuser[5]);
	if($edul == "c") { $edul = "1"; }
	if($edul == "b") { $edul = "2"; }
	if($edul == "m") { $edul = "3"; }
	if($edul == "s") { $edul = "4"; }
	$educode = $getuser[8].".".$edul.".1"; // .1 - очная форма

	$newinlist = array(
		"a_id" => $getuser[0],
		"a_sname" => $getuser[1],
		"a_fname" => $getuser[2],
		"a_pname" => $getuser[3],
		"a_sex" => $getuser[7],
		"a_educode" => $educode,
		"a_course" => $educ,
		"a_dep" => $getfac[1],
		"a_group" => $getdep[2],
		"a_gnum" => $getuser[6],
	);
	$sendlist["alist"] = $newinlist;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

//Печать СЗ
elseif($_GET['act'] == "printsz") {
	accessto("s,k,t");
	if((!$_GET['temp1']) or (!$_GET['temp1']) or (!$_GET['temp2']) or (!$_GET['temp3']) or (!$_GET['temp4']) or (!$_GET['temp5']) or (!$_GET['lists'])) { wrongusing(); }

	session_start();
	session_regenerate_id();

	$_SESSION['temp1'] = htmlentities($_GET['temp1'], ENT_QUOTES, "UTF-8");
	$_SESSION['temp2'] = htmlentities($_GET['temp2'], ENT_QUOTES, "UTF-8");
	$_SESSION['temp3'] = htmlentities($_GET['temp3'], ENT_QUOTES, "UTF-8");
	$_SESSION['temp4'] = htmlentities($_GET['temp4'], ENT_QUOTES, "UTF-8");


	//Преобразование списков
	$prelists = stripcslashes($_GET['lists']);
	if(($lists = json_decode($prelists)) === false) { errorjson("Ошибка составления списков"); }

	$replavedtext = $_GET['temp5'];
	$replavedtext = str_replace('<ol>', '<div class=\"ollist\"><ol>', $replavedtext);
	$replavedtext = str_replace('<ul>', '<div class=\"ollist\"><ul>', $replavedtext);
	$replavedtext = str_replace('</ol>', '</ol></div>', $replavedtext);
	$replavedtext = str_replace('</ul>', '</ul></div>', $replavedtext);

	for($i = 0; $i<count($lists); $i++) {
		$searchlist = 'СПИСОК('.$lists[$i][0].')';
		$newlist = "<div class=\"ollist\"><ol>";
		//$newlist = "<div class=\"ollist\">";
		if(strpos($replavedtext,$searchlist) == false) { continue; }
		$studlist = $lists[$i][1];

		for($c = 0; $c<count($studlist); $c++) {

			$pregetuser = mysql_query("SELECT `id`,`sname`,`fname`,`pname`,`dep`,`curcourse`,`groupnum`,`gen` from `users` WHERE `id` ='".$studlist[$c]."' LIMIT 1;");
			$getuser = mysql_fetch_row($pregetuser);
			if(!$getuser[1]) { errorjson("Некоторые студенты не найдены. Составьте список заново."); }
			$pregetdep = mysql_query("SELECT `id`,`area`,`name` from `deps` WHERE `id`='".$getuser[4]."' LIMIT 1");
			$getdep = mysql_fetch_row($pregetdep);
			$pregetfac = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
			$getfac = mysql_fetch_row($pregetfac);
			list($edul, $educ) = split('[-]', $getuser[5]);

			$newpname = "";
			if(mb_strlen($getuser[3], "UTF-8")<2) { $newpname = ""; } else { $newpname = " ".$getuser[3]; }
			$name = $getuser[1]." ".$getuser[2]."".$newpname;

			list($edul, $educ) = split('[-]', $getuser[5]);
			if($edul == "c") { $edul = "1"; }
			if($edul == "b") { $edul = "2"; }
			if($edul == "m") { $edul = "3"; }
			if($edul == "s") { $edul = "4"; }
			$educode = $getuser[7].".".$edul.".1"; // .1 - очная форма

			//$studinfo = $name." ".$getfac[1]."-".$educode."-(".$getdep[2].")-".$getuser[6].";";
			$studinfo = $name." ".$getfac[1]."-".$getdep[2]."-".$getuser[6].";";
			//if($c==(count($studlist)-1)) { $studinfo = mb_substr($studinfo, 0, -1, "UTF-8"); $studinfo = $studinfo."."; }
			$newlist .= "<li>".$studinfo."</li>";
			//$newlist .= $studinfo;
		}
		$newlist .= "</ol></div>";
		//$newlist .= "</div>";
		$replavedtext = str_replace($searchlist, $newlist, $replavedtext);
	}

	$_SESSION['temp5'] = htmlentities($replavedtext, ENT_QUOTES, "UTF-8");

	$sesid = session_id();
	$sendlist["view"] = $sesid;
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

// Вывод основной информации об организации
elseif($_GET['act'] == "getorginfo") {
	accessto("s");
	$sendlist = array(
		"organization_form" => $GLOBALS['config']['organization_form'],
		"organization_fullname" => $GLOBALS['config']['organization_fullname'],
		"organization_shortname" => $GLOBALS['config']['organization_shortname'],
		"organization_department" => $GLOBALS['config']['organization_department'],
		"organization_logo" => $GLOBALS['config']['organization_logo'],
		"organization_favicon" => $GLOBALS['config']['organization_favicon'],
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

// SMS-уведомления
elseif($_GET['act'] == "getsmsparams") {
	accessto("s");

	if($GLOBALS['config']['sms_login'] == "") { errorjson("sms_auth_new"); }

	$senddata1 = file_get_contents('https://gate.smsaero.ru/balance/?answer=json&user='.$GLOBALS['config']['sms_login'].'&password='.$GLOBALS['config']['sms_pw'].'');
	if($senddata1 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotbalance = json_decode($senddata1, true);
	if(!$gotbalance["balance"]) { errorjson("sms_auth_error"); }

	$senddata2 = file_get_contents('https://gate.smsaero.ru/senders/?answer=json&user='.$GLOBALS['config']['sms_login'].'&password='.$GLOBALS['config']['sms_pw'].'');
	if($senddata2 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotsenders = json_decode($senddata2, true);

	$sendlist = array(
		"sms_login" => $GLOBALS['config']['sms_login'],
		"sms_name" => $GLOBALS['config']['sms_name'],
		"sms_balance" => $gotbalance["balance"],
		"sms_senders" => $gotsenders,
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

// SMS-уведомления
elseif($_GET['act'] == "smsauth") {
	accessto("s");
	if(!is([$_GET['sms_login'],$_GET['sms_pw']])) { wrongusing(); }

	$senddata1 = file_get_contents('https://gate.smsaero.ru/balance/?answer=json&user='.$_GET['sms_login'].'&password='.$_GET['sms_pw'].'');
	if($senddata1 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotbalance = json_decode($senddata1, true);
	if(!$gotbalance["balance"]) { errorjson("Неверный логин или пароль."); }

	$senddata2 = file_get_contents('https://gate.smsaero.ru/senders/?answer=json&user='.$_GET['sms_login'].'&password='.$_GET['sms_pw'].'');
	if($senddata2 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotsenders = json_decode($senddata2, true);

	$sendlist = array(
		"sms_login" => $_GET['sms_login'],
		"sms_balance" => $gotbalance["balance"],
		"sms_senders" => $gotsenders,
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

elseif($_GET['act'] == "setsmsparams") {
	accessto("s");

	if(!is([$_GET['sms_login'],$_GET['sms_pw']])) {
		if(is([$_GET['sms_name']])) {
			$senddata2 = file_get_contents('https://gate.smsaero.ru/senders/?answer=json&user='.$GLOBALS['config']['sms_login'].'&password='.$GLOBALS['config']['sms_pw'].'');
			if($senddata2 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
			$gotsenders = json_decode($senddata2, true);
			if(array_search($_GET['sms_name'], $gotsenders) === false) { errorjson("Указано недоступное имя отправителя."); }
			$GLOBALS['config']['sms_name'] = $_GET['sms_name'];
			config_save();
			errorjson("ok");
		} else {
			$GLOBALS['config']['sms_login'] = "";
			$GLOBALS['config']['sms_pw'] = "";
			$GLOBALS['config']['sms_name'] = "";
			config_save();
			errorjson("ok");
		}
	}

	if(mb_strlen($_GET['sms_pw'], "UTF-8") !== 32) { wrongusing(); }

	if(!is([$_GET['sms_name']])) { wrongusing(); }

	$senddata1 = file_get_contents('https://gate.smsaero.ru/balance/?answer=json&user='.$_GET['sms_login'].'&password='.$_GET['sms_pw'].'');
	if($senddata1 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotbalance = json_decode($senddata1, true);
	if(!$gotbalance["balance"]) { errorjson("Неверный логин или пароль."); }

	$senddata2 = file_get_contents('https://gate.smsaero.ru/senders/?answer=json&user='.$_GET['sms_login'].'&password='.$_GET['sms_pw'].'');
	if($senddata2 === false) { errorjson("Нет соединения с SMS-центром. Повторите попытку позже."); }
	$gotsenders = json_decode($senddata2, true);
	if(array_search($_GET['sms_name'], $gotsenders) === false) { errorjson("Указано недоступное имя отправителя."); }

	$GLOBALS['config']['sms_login'] = $_GET['sms_login'];
	$GLOBALS['config']['sms_pw'] = $_GET['sms_pw'];
	$GLOBALS['config']['sms_name'] = $_GET['sms_name'];
	config_save();
	errorjson("ok");
}

// Вывод настроик рейтинговой системы
elseif($_GET['act'] == "getratingparams") {
	accessto("s");
	$sendlist = array(
		"rating_roles" => $GLOBALS['config']['rating_roles'],
		"rating_levels" => $GLOBALS['config']['rating_levels'],
		"rating_complex" => $GLOBALS['config']['rating_complex'],
		"rating_muscle" => $GLOBALS['config']['rating_muscle'],
		"rating_oneday" => $GLOBALS['config']['rating_oneday'],
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

// Сохранение настроек рейтинговой системы
elseif($_GET['act'] == "setratingparams") {
	accessto("s");
	if(!is([
		$_GET['rating_roles_u'],
		$_GET['rating_roles_p'],
		$_GET['rating_roles_w'],
		$_GET['rating_roles_l'],
		$_GET['rating_roles_m'],
		$_GET['rating_roles_h'],
		$_GET['rating_levels_f'],
		$_GET['rating_levels_u'],
		$_GET['rating_levels_c'],
		$_GET['rating_levels_r'],
		$_GET['rating_levels_v'],
		$_GET['rating_levels_i'],
		$_GET['rating_complex'],
		$_GET['rating_muscle'],
		$_GET['rating_oneday']
	])) { wrongusing(); }

	if(
		!is_numeric($_GET['rating_roles_u'])
		or !is_numeric($_GET['rating_roles_p'])
		or !is_numeric($_GET['rating_roles_w'])
		or !is_numeric($_GET['rating_roles_l'])
		or !is_numeric($_GET['rating_roles_m'])
		or !is_numeric($_GET['rating_roles_h'])
		or !is_numeric($_GET['rating_levels_f'])
		or !is_numeric($_GET['rating_levels_u'])
		or !is_numeric($_GET['rating_levels_c'])
		or !is_numeric($_GET['rating_levels_r'])
		or !is_numeric($_GET['rating_levels_v'])
		or !is_numeric($_GET['rating_levels_i'])
		or !is_numeric($_GET['rating_complex'])
		or !is_numeric($_GET['rating_muscle'])
		or !is_numeric($_GET['rating_oneday'])
	) { errorjson("Могут быть указаны только целые и дробные числа. Дробная часть разделяется точкой (.), НЕ ЗАПЯТОЙ. Например, \"1.5\"."); }

	settype($_GET['rating_roles_u'], "float");
	settype($_GET['rating_roles_p'], "float");
	settype($_GET['rating_roles_w'], "float");
	settype($_GET['rating_roles_l'], "float");
	settype($_GET['rating_roles_m'], "float");
	settype($_GET['rating_roles_h'], "float");
	settype($_GET['rating_levels_f'], "float");
	settype($_GET['rating_levels_u'], "float");
	settype($_GET['rating_levels_c'], "float");
	settype($_GET['rating_levels_r'], "float");
	settype($_GET['rating_levels_v'], "float");
	settype($_GET['rating_levels_i'], "float");
	settype($_GET['rating_complex'], "float");
	settype($_GET['rating_muscle'], "float");
	settype($_GET['rating_oneday'], "float");

	$GLOBALS['config']['rating_roles']['u'] = $_GET['rating_roles_u'];
	$GLOBALS['config']['rating_roles']['p'] = $_GET['rating_roles_p'];
	$GLOBALS['config']['rating_roles']['w'] = $_GET['rating_roles_w'];
	$GLOBALS['config']['rating_roles']['l'] = $_GET['rating_roles_l'];
	$GLOBALS['config']['rating_roles']['m'] = $_GET['rating_roles_m'];
	$GLOBALS['config']['rating_roles']['h'] = $_GET['rating_roles_h'];
	$GLOBALS['config']['rating_levels']['f'] = $_GET['rating_levels_f'];
	$GLOBALS['config']['rating_levels']['u'] = $_GET['rating_levels_u'];
	$GLOBALS['config']['rating_levels']['c'] = $_GET['rating_levels_c'];
	$GLOBALS['config']['rating_levels']['r'] = $_GET['rating_levels_r'];
	$GLOBALS['config']['rating_levels']['v'] = $_GET['rating_levels_v'];
	$GLOBALS['config']['rating_levels']['i'] = $_GET['rating_levels_i'];
	$GLOBALS['config']['rating_complex'] = $_GET['rating_complex'];
	$GLOBALS['config']['rating_muscle'] = $_GET['rating_muscle'];
	$GLOBALS['config']['rating_oneday'] = $_GET['rating_oneday'];
	config_save();
	errorjson("ok");
}

// Вывод настроик по авторизации через соцсети
elseif($_GET['act'] == "getsnparams") {
	accessto("s");

	if($GLOBALS['config']['vk_state'] == 0 and $GLOBALS['config']['vk_id'] !== "") {
		$GLOBALS['config']['vk_id'] = "";
		$GLOBALS['config']['vk_secret'] = "";
		config_save();
	}

	$sendlist = array(
		"vk_id" => $GLOBALS['config']['vk_id'],
		"vk_secret" => $GLOBALS['config']['vk_secret'],
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

// "vk_auth_link" => 'https://oauth.vk.com/authorize?client_id='.$GLOBALS['config']['vk_id'].'&scope=offline&redirect_uri='.urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/vkauth').'&response_type=code&v=5.32&state=1',

// Предварительное сохранение настроик по авторизации через соцсети
elseif($_GET['act'] == "sn_vk_prepare") {
	accessto("s");

	if(!is([$_GET['vk_id'],$_GET['vk_secret']])) { wrongusing(); }

	$url = 'https://oauth.vk.com/authorize?client_id='.$_GET['vk_id'].'&scope=offline&redirect_uri='.urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/vktoken').'&response_type=code&v=5.32&state=1';
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HEADER, false);
	$prevkcheck = curl_exec($curl);
	curl_close($curl);

	$vkcheck = json_decode($prevkcheck, true);

	if($vkcheck['error']) { errorjson("ВКонтакте вернул ошибку. Исправте введенные данные или настройки приложения и повторите попытку."); }

	$GLOBALS['config']['vk_id'] = $_GET['vk_id'];
	$GLOBALS['config']['vk_secret'] = $_GET['vk_secret'];
	config_save();
	$sendlist = array(
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}

// Активация настроик по авторизации через соцсети
elseif($_GET['act'] == "sn_vk_activate") {
	accessto("s");

	if($GLOBALS['config']['vk_id'] == "" or $GLOBALS['config']['vk_secret'] == "") { errorjson("Перед активацией предварительно сохраните введенные данные."); }

	$prevkauth = mysql_query("SELECT `id`,`vkauth`,`vktoken` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$vkauth = mysql_fetch_row($prevkauth);
	if(!$vkauth[0]) { errorjson("Ваш аккаунт в системе Карта активиста не привязан к приложению ВКонтакте. Для активации привяжите Ваш аккаунт ВКонтакте к приложению. #1"); }

	$params = array(
		'v' => "5.32",
        'access_token' => $vkauth[2],
        'user_id' => $vkauth[1]
    );

	$prevkcheck = file_get_contents('https://api.vk.com/method/users.isAppUser?' . urldecode(http_build_query($params)));
	$vkcheck = json_decode($prevkcheck, true);

	if($vkcheck['error'] or !$vkcheck['response']) { errorjson("Ошибка запроса к ВКонтакте API"); }

	if($vkcheck['response'] == "0") { errorjson("Ваш аккаунт в системе Карта активиста не привязан к приложению ВКонтакте. Для активации привяжите Ваш аккаунт ВКонтакте к приложению. #2"); }

	$GLOBALS['config']['vk_state'] = 1;
	config_save();
	$sendlist = array(
		"error" => "ok"
	);
	exit(json_encode($sendlist));
}


// Удаление настроик по авторизации через соцсети
elseif($_GET['act'] == "unlinksnparams") {
	accessto("s");

	$GLOBALS['config']['vk_state'] = 0;
	$GLOBALS['config']['vk_id'] = "";
	$GLOBALS['config']['vk_secret'] = "";
	config_save();
	errorjson("ok");
}

// Вывод списка департаментов и групп
elseif($_GET['act'] == "getdeps_fac" or $_GET['act'] == "getdeps_groups" or $_GET['act'] == "getdeps_org") {
	accessto("s");
	if($_GET['act'] == "getdeps_fac") {
		$pregetdeps = mysql_query("SELECT `id`,`type`,`name`,`full` from `deps` WHERE `type`='i';");
	} elseif($_GET['act'] == "getdeps_org") {
		$pregetdeps = mysql_query("SELECT `id`,`type`,`name`,`full` from `deps` WHERE `type`='d';");
	} else {
		if((!$_GET['id']) or !is_numeric($_GET['id'])) { wrongusing(); }
		$pregetdeps = mysql_query("SELECT `id`,`type`,`name`,`full`,`area` from `deps` WHERE `area` = '".$_GET['id']."' AND `type`='g';");
		if(mysql_num_rows($pregetdeps) == 0) { errorjson("Для данного подразделения группы не зарегистрированы."); }
	}
	$faclist = array();
	while($getdeps = mysql_fetch_array($pregetdeps)) {
		$newfac = array(
			"id" => $getdeps[0],
			"short" => $getdeps[2],
			"full" => $getdeps[3]
		);
		$faclist[] = $newfac;
		unset($newfac);
	}
	if($_GET['act'] == "getdeps_fac") { $sendlist["fac"] = $faclist; } elseif($_GET['act'] == "getdeps_org") { $sendlist["org"] = $faclist; $sendlist["order"] = $GLOBALS['config']['organizations_order']; } else { $sendlist["groups"] = $faclist; }
	$sendlist["error"] = "ok";
	exit(json_encode($sendlist));
}

elseif($_GET['act'] == "getdeps_dep_save") {
	accessto("s");
	if((!$_GET['id']) or !is_numeric($_GET['id']) or !$_GET['short'] or !$_GET['full']) { wrongusing(); }
	$checkdep = mysql_query("SELECT `id`,`type` from `deps` WHERE `id`='".$_GET['id']."' AND `type`='i' OR `type`='g'  OR `type`='d' LIMIT 1;");
	$checkdep = mysql_fetch_row($checkdep);
	if(!$checkdep[0]) { errorjson("Редактируемые подразделения/группы не зарегистрированы."); }
	$authreq = "UPDATE `".$GLOBALS['config']['mysql_db']."`.`deps` SET `name` = '".htmlentities($_GET['short'], ENT_QUOTES, "UTF-8")."', `full` = '".htmlentities($_GET['full'], ENT_QUOTES, "UTF-8")."' WHERE `deps`.`id` =".$_GET['id'].";";
	if(!mysql_query($authreq)) { errorjson("Ошибка базы данных. Повторите попытку позже.".mysql_error()); }
	errorjson("ok");
}

elseif($_GET['act'] == "config_orglist_sort") {
	accessto("s");
	if(!$_GET['order']) { wrongusing(); }

	$getneworder = json_decode(stripslashes($_GET['order']));
	$neworder = array();
	for($i=0; $i<count($getneworder); $i++) {
		if(!is_numeric($getneworder[$i])) { wrongusing(); }
		$getEl = mysql_query("SELECT `id`,`type` from `deps` WHERE `id`='".$getneworder[$i]."' AND `type` = 'd' LIMIT 1;");
		$getEl = mysql_fetch_row($getEl);
		if(!$getEl[0]) { errorjson("Новый порядок не применен. Одино из подразделений не найдено."); }
		$neworder[] = $getneworder[$i];
	}
	$GLOBALS['config']['organizations_order'] = $neworder;
	config_save();
	errorjson("ok");
}

elseif($_GET['act'] == "deps_org_add") {
	accessto("s");
	if(!$_GET['short'] or !$_GET['full']) { wrongusing(); }
	$checkdep = mysql_query("SELECT `id`,`name`,`type` from `deps` WHERE `type`='d' AND `name` = '".htmlentities($_GET['short'], ENT_QUOTES, "UTF-8")."'; LIMIT 1;");
	$checkdep = mysql_fetch_row($checkdep);
	if($checkdep[0]) { errorjson("Указанное сокращение уже добавлено."); }
	$addreq = "INSERT INTO `".$GLOBALS['config']['mysql_db']."`.`deps` (`id`, `type`, `area`, `name`, `full`)
	 VALUES (NULL, 'd', NULL, '".htmlentities($_GET['short'], ENT_QUOTES, "UTF-8")."', '".htmlentities($_GET['full'], ENT_QUOTES, "UTF-8")."');";
	if(!mysql_query($addreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	array_unshift($GLOBALS['config']['organizations_order'], "".mysql_insert_id()."");
	config_save();
	errorjson("ok");
}

// Удаление подразделения, организующего мероприятия
elseif($_GET['act'] == "deldep_org") {
	accessto("s");
	if(!$_GET['id'] or !is_numeric($_GET['id'])) { wrongusing(); }

	$checkdep = mysql_query("SELECT `id` from `deps` WHERE `id`='".$_GET['id']."' AND `type` = 'd' LIMIT 1");
	$checkdep = mysql_fetch_row($checkdep);
	if(!$checkdep[0]) { errorjson("Подразделение не найдено"); }

	$checkeevent = mysql_query("SELECT `id`,`dep` from `events` WHERE `dep`='".$_GET['id']."' LIMIT 1");
	$checkeevent = mysql_fetch_row($checkeevent);
	if($checkeevent[0]) { errorjson("Подразделение невозможно удалить, т.к. оно является организатором одного или более уже внесенных мероприятий"); }

	$deldepreq = "DELETE FROM `".$GLOBALS['config']['mysql_db']."`.`deps` WHERE `deps`.`id` = ".$_GET['id']."";
	if(!mysql_query($deldepreq)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }

	unset($GLOBALS['config']['organizations_order'][array_search(''.$_GET['id'].'',$GLOBALS['config']['organizations_order'])]);
	$GLOBALS['config']['organizations_order'] = array_values($GLOBALS['config']['organizations_order']);
	config_save();

	errorjson("ok");
}

// Обработка списка студентов
elseif($_GET['act'] == "studentsupload_connect") {
	accessto("s");
	if(!$_GET['csvid'] or !$_GET['connect'] or !$_GET['type']) { wrongusing(); }
	if($_GET['type'] !== "reload" and $_GET['type'] !== "add") { wrongusing(); }
	if(!isset($GLOBALS['config']['csvfile']) or ($_GET['csvid'].'.csv') !== $GLOBALS['config']['csvfile'] or !file_exists("content/".$GLOBALS['config']['csvfile'])) { errorjson("Файл для обработки отсутствует на сервере. Повторите загрузку файла."); }

	$JSONConnect = json_decode(stripslashes($_GET['connect']), true);
	if($JSONConnect == NULL) { wrongusing(); }

	$csvFile = file("content/".$GLOBALS['config']['csvfile']);
    $data = str_getcsv($csvFile[0],";");

	// Проверка выборки
	$allRows = array("nodata", "id", "sname", "fname", "pname", "sex", "birthday", "phone", "edu_type", "edu_level", "edu_standard", "department", "course", "group_name", "group_num", "pay");
	$necessoryRows = array("id", "sname", "fname", "pname", "edu_level", "department", "course", "group_name", "group_num");

	$samerows = array();
	$samecolumns = array();
	$CONNECTIONS = array();
	for($i=0; $i<count($JSONConnect); $i++) {
		if(!isset($JSONConnect[$i]["column"]) or !isset($JSONConnect[$i]["row"])) { wrongusing(); }
		if(array_search($JSONConnect[$i]["column"], $allRows) === false) { wrongusing(); }
		settype($JSONConnect[$i]["row"], "integer");
		if($JSONConnect[$i]["row"] < 0 or $JSONConnect[$i]["row"] > (count($data)-1)) { wrongusing(); }
		if(array_search($JSONConnect[$i]["row"], $samerows) or array_search($JSONConnect[$i]["column"], $samecolumns)) { wrongusing(); }
		$samerows[] = $JSONConnect[$i]["row"];
		$samecolumns[] = $JSONConnect[$i]["column"];
		$CONNECTIONS[($JSONConnect[$i]["column"])] = $JSONConnect[$i]["row"];
	}
	for($i=0; $i<count($necessoryRows); $i++) {
		if(array_search($necessoryRows[$i], $samecolumns) === false) { wrongusing(); }
	}

	// Подготовка списка
	$dataCSV = array();
    foreach ($csvFile as $line) {
		$dataCSV[] = str_getcsv($line,";");
    }
	array_shift($dataCSV);
	for($i=0; $i < count($dataCSV); $i++) {
		for($c=0; $c < count($dataCSV[$i]); $c++) {
			$dataCSV[$i][$c] = iconv('windows-1251', 'UTF-8', $dataCSV[$i][$c]);
			$dataCSV[$i][$c] = addslashes($dataCSV[$i][$c]); //хз
		}
	}

	$dataCSVnum = count($dataCSV);

	// Обработка id
	$checkIDs = array();
	$repeatedIDs = array();
	for($i=0; $i < $dataCSVnum; $i++) {
		if(mb_strlen($dataCSV[$i][($CONNECTIONS["id"])], "UTF-8") > 50) { errorjson("Длина идентификатора не может превышать 50 символов"); }
		if(array_search($dataCSV[$i][($CONNECTIONS["id"])], $checkIDs)) {
			$repeatedIDs[] = $dataCSV[$i][($CONNECTIONS["id"])];
		} else {
			$checkIDs[] = $dataCSV[$i][($CONNECTIONS["id"])];
		}
	}
	if(count($repeatedIDs) !== 0) {
		$textRepeatedIDs = "";
		for($c=0; $c < count($repeatedIDs); $c++) {
			$textRepeatedIDs = $textRepeatedIDs.$repeatedIDs[$c]." ";
		}
		errorjson("Найдены одинаковые идентификаторы студентов: '".$textRepeatedIDs."'. Идентификаторы студентов не могут повторятся.");
	}

	// Обработка ФИО
	for($i=0; $i < $dataCSVnum; $i++) {
		if(
			   mb_strlen($dataCSV[$i][($CONNECTIONS["fname"])], "UTF-8") < 1
			or mb_strlen($dataCSV[$i][($CONNECTIONS["sname"])], "UTF-8") < 1
			or mb_strlen($dataCSV[$i][($CONNECTIONS["fname"])], "UTF-8") > 150
			or mb_strlen($dataCSV[$i][($CONNECTIONS["sname"])], "UTF-8") > 150
		) { errorjson("Фамилия и имя не могут быть короче 1 и длинее 150 символов"); }
		if(mb_strlen($dataCSV[$i][($CONNECTIONS["pname"])], "UTF-8") <= 1) { $dataCSV[$i][($CONNECTIONS["pname"])] = ""; }
	}

	// Обработка даты рождения
	if(!isset($CONNECTIONS["birthday"])) {
		$CONNECTIONS["birthday"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["birthday"])])) {
			$dataCSV[$i][($CONNECTIONS["birthday"])] = "NULL"; continue;
		}
		$dc_date = $dataCSV[$i][($CONNECTIONS["birthday"])];
		$dc_error = "Дата рождения '".$dc_date."' указана в нестандартном формате.";
		if(!preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $dc_date) and !preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}$/', $dc_date)) { errorjson($dc_error); }
		list($dc_day, $dc_month, $dc_year) = split('[.]', $dc_date);
		if(strlen($dc_year) == 2) {
			if($dc_year > 40) { $dc_year = "19".$dc_year; }
			else { $dc_year = "20".$dc_year; }
		}
		if(!checkdate($dc_month, $dc_day, $dc_year)) { errorjson($dc_error); }
		$dataCSV[$i][($CONNECTIONS["birthday"])] = "'".$dc_year."-".$dc_month."-".$dc_day."'";
	}

	// Обработка ступени образования
	for($i=0; $i < $dataCSVnum; $i++) {
		$curEduLevel = $dataCSV[$i][($CONNECTIONS["edu_level"])];
		settype($dataCSV[$i][($CONNECTIONS["edu_level"])], "integer");
		if($dataCSV[$i][($CONNECTIONS["edu_level"])] < 1 or $dataCSV[$i][($CONNECTIONS["edu_level"])] > 5) {
			errorjson("Ступень образования '".$curEduLevel."' указана в нестандартном формате. Ступень образования может быть: для СПО = '1', для бакалавриата = '2', для магистратуры = '3', для специалитета = '4', для аспирантуры = '5'.");
		}
		if($dataCSV[$i][($CONNECTIONS["edu_level"])] == 1) { $dataCSV[$i][($CONNECTIONS["edu_level"])] = "c"; }
		elseif($dataCSV[$i][($CONNECTIONS["edu_level"])] == 2) { $dataCSV[$i][($CONNECTIONS["edu_level"])] = "b"; }
		elseif($dataCSV[$i][($CONNECTIONS["edu_level"])] == 3) { $dataCSV[$i][($CONNECTIONS["edu_level"])] = "m"; }
		elseif($dataCSV[$i][($CONNECTIONS["edu_level"])] == 4) { $dataCSV[$i][($CONNECTIONS["edu_level"])] = "s"; }
		elseif($dataCSV[$i][($CONNECTIONS["edu_level"])] == 5) { $dataCSV[$i][($CONNECTIONS["edu_level"])] = "a"; }
	}

	// Обработка факультета и группы
	$newdeps = array();
	$newgroups = array();
	for($i=0; $i < $dataCSVnum; $i++) {
		if(
			   mb_strlen($dataCSV[$i][($CONNECTIONS["department"])], "UTF-8") < 1
			or mb_strlen($dataCSV[$i][($CONNECTIONS["department"])], "UTF-8") > 50
			or mb_strlen($dataCSV[$i][($CONNECTIONS["group_name"])], "UTF-8") < 1
			or mb_strlen($dataCSV[$i][($CONNECTIONS["group_name"])], "UTF-8") > 50
		) {
			errorjson("Аббревиатура факультета/группы не может быть меньше 1 и больше 50 символов");
		}

		$prefindfaceid = mysql_query("SELECT `id`,`type`,`area`,`name` from `deps` WHERE `type`='i' AND `name`='".$dataCSV[$i][($CONNECTIONS["department"])]."';");
		$findfaceid = mysql_fetch_row($prefindfaceid);
		if(!$findfaceid[0]) {
			$newdeps[] = $dataCSV[$i][($CONNECTIONS["department"])];
			$newgroups[] = $dataCSV[$i][($CONNECTIONS["group_name"])].' ('.$dataCSV[$i][($CONNECTIONS["department"])].')';
		} else {
			$prefinddep = mysql_query("SELECT `id`,`type`,`area`,`name` from `deps` WHERE `type`='g' AND `name`='".$dataCSV[$i][($CONNECTIONS["group_name"])]."' AND `area`='".$findfaceid[0]."';");
			$finddep = mysql_fetch_row($prefinddep);
			if(!$finddep[0]) {
				$newgroups[] = $dataCSV[$i][($CONNECTIONS["group_name"])].' ('.$dataCSV[$i][($CONNECTIONS["department"])].')';
			}
		}
	}

	// Обработка курса
	for($i=0; $i < $dataCSVnum; $i++) {
		if(!is_numeric($dataCSV[$i][($CONNECTIONS["course"])])) {
			errorjson("Номер курса указан в нестандартном формате: `".$dataCSV[$i][($CONNECTIONS["course"])]."`.");
		}
		if(
			   $dataCSV[$i][($CONNECTIONS["course"])] < 1
			or $dataCSV[$i][($CONNECTIONS["course"])] > 6
		) {
			errorjson("Номер курса не может быть меньше 1 и больше 6: `".$dataCSV[$i][($CONNECTIONS["course"])]."`.");
		}
	}

	// Обработка номера группы
	for($i=0; $i < $dataCSVnum; $i++) {
		if(
			   mb_strlen($dataCSV[$i][($CONNECTIONS["group_num"])], "UTF-8") < 1
			or mb_strlen($dataCSV[$i][($CONNECTIONS["group_num"])], "UTF-8") > 10
		) {
			errorjson("Номер группы не может быть меньше 1 и больше 10 символов");
		}
		if(!is_numeric(mb_substr($dataCSV[$i][($CONNECTIONS["group_num"])], 0, 1, "UTF-8"))) {
			errorjson("Номер группы указан в нестандартном формате. Первый символ номера группы должен быть цифрой, которая в дальнейшем будет использоваться как номер курса.");
		}
	}

	// Обработка гендера
	require '../plugins/ncl/NCL.NameCase.ru.php';
	$RPnc = new NCLNameCaseRu();

	if(!isset($CONNECTIONS["sex"])) {
		$CONNECTIONS["sex"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["sex"])])) { $dataCSV[$i][($CONNECTIONS["sex"])] = ""; }
		$dataCSV[$i][($CONNECTIONS["sex"])] = mb_strtolower($dataCSV[$i][($CONNECTIONS["sex"])], "UTF-8");
		if($dataCSV[$i][($CONNECTIONS["sex"])] == "м" or $dataCSV[$i][($CONNECTIONS["sex"])] == "m") {
			$dataCSV[$i][($CONNECTIONS["sex"])] = "m";
		} elseif($dataCSV[$i][($CONNECTIONS["sex"])] == "ж" or $dataCSV[$i][($CONNECTIONS["sex"])] == "f") {
			$dataCSV[$i][($CONNECTIONS["sex"])] = "f";
		} else {
			$gender = $RPnc->genderDetect(trim("".$dataCSV[$i][($CONNECTIONS["sname"])]." ".$dataCSV[$i][($CONNECTIONS["fname"])]." ".$dataCSV[$i][($CONNECTIONS["pname"])].""));
			if($gender == NCL::$MAN) { $dataCSV[$i][($CONNECTIONS["sex"])] = "m"; }
			else { $dataCSV[$i][($CONNECTIONS["sex"])] = "f"; }
		}
	}

	// Обработка номера телефона
	if(!isset($CONNECTIONS["phone"])) {
		$CONNECTIONS["phone"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["phone"])])) {
			$dataCSV[$i][($CONNECTIONS["phone"])] = "unknown"; continue;
		}
		$clearPhone = preg_replace("/[^0-9]/","",$dataCSV[$i][($CONNECTIONS["phone"])]);
		if(mb_strlen($clearPhone, "UTF-8") < 10) {
			$dataCSV[$i][($CONNECTIONS["phone"])] = "unknown"; continue;
		}
		$clearPhone = mb_substr($clearPhone, 0, 11, "UTF-8");
		if(mb_substr($clearPhone, 0, 2, "UTF-8") == "89" or mb_substr($clearPhone, 0, 2, "UTF-8") == "79") {
			$dataCSV[$i][($CONNECTIONS["phone"])] = mb_substr($clearPhone, 1, 10, "UTF-8"); continue;
		}
		if(mb_substr($clearPhone, 0, 1, "UTF-8") == "9") {
			$dataCSV[$i][($CONNECTIONS["phone"])] = mb_substr($clearPhone, 0, 10, "UTF-8"); continue;
		}
		$dataCSV[$i][($CONNECTIONS["phone"])] = "unknown"; continue;
	}

	// Обработка формы обучения
	if(!isset($CONNECTIONS["edu_type"])) {
		$CONNECTIONS["edu_type"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["edu_type"])])) {
			$dataCSV[$i][($CONNECTIONS["edu_type"])] = 1; continue;
		}
		if(trim($dataCSV[$i][($CONNECTIONS["edu_type"])]) == "" or trim($dataCSV[$i][($CONNECTIONS["edu_type"])]) == "-") {
			$dataCSV[$i][($CONNECTIONS["edu_type"])] = 1; continue;
		}
		$curEduType = $dataCSV[$i][($CONNECTIONS["edu_type"])];
		settype($dataCSV[$i][($CONNECTIONS["edu_type"])], "integer");
		if($dataCSV[$i][($CONNECTIONS["edu_type"])] < 1 or $dataCSV[$i][($CONNECTIONS["edu_type"])] > 3) {
			errorjson("Форма обучения '".$curEduType."' указана в нестандартном формате. Форма обучения может быть: очная = '1', очно-заочная (вечерняя) = '2', заочная = '3'.");
		}
	}

	// Обработка образовательного стандарта
	if(!isset($CONNECTIONS["edu_standard"])) {
		$CONNECTIONS["edu_standard"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["edu_standard"])])) {
			$dataCSV[$i][($CONNECTIONS["edu_standard"])] = ""; continue;
		}
		if(trim($dataCSV[$i][($CONNECTIONS["edu_standard"])]) == "" or trim($dataCSV[$i][($CONNECTIONS["edu_standard"])]) == "-") {
			$dataCSV[$i][($CONNECTIONS["edu_standard"])] = ""; continue;
		}
		if(mb_strlen($dataCSV[$i][($CONNECTIONS["edu_standard"])], "UTF-8") > 15) {
			errorjson("Поле 'Образовательный стандарт' не может превышать 15 символов. Рекомендуется использовать номер поколения образовательного стандарта, например, '3+' или '2'.");
		}
	}

	// Обработка формы оплаты
	if(!isset($CONNECTIONS["pay"])) {
		$CONNECTIONS["pay"] = count($dataCSV[0]);
	}

	for($i=0; $i < $dataCSVnum; $i++) {
		if(!isset($dataCSV[$i][($CONNECTIONS["pay"])])) {
			$dataCSV[$i][($CONNECTIONS["pay"])] = "y"; continue;
		}
		$dataCSV[$i][($CONNECTIONS["pay"])] = mb_strtolower($dataCSV[$i][($CONNECTIONS["pay"])], "UTF-8");
		if(trim($dataCSV[$i][($CONNECTIONS["pay"])]) == "" or trim($dataCSV[$i][($CONNECTIONS["pay"])]) == "-") {
			$dataCSV[$i][($CONNECTIONS["pay"])] = "y"; continue;
		}
		if($dataCSV[$i][($CONNECTIONS["pay"])] == "б" or $dataCSV[$i][($CONNECTIONS["pay"])] == "b") {
			$dataCSV[$i][($CONNECTIONS["pay"])] = "y"; continue;
		}
		if($dataCSV[$i][($CONNECTIONS["pay"])] == "к" or $dataCSV[$i][($CONNECTIONS["pay"])] == "k") {
			$dataCSV[$i][($CONNECTIONS["pay"])] = "n"; continue;
		}
		errorjson("Форма оплаты может быть: бюджет = 'б', коммерция = 'к'. Если поле пустое, то по умолчанию форма оплаты = 'б'.");
	}

	$preOutStudents = mysql_query("SELECT `id`,`sin`,`fullname` from `users` WHERE `type`='a';");

	// В архив студентов
	$dbIDs = array();
	$sendlist["out"] = array();
	while($outStudent = mysql_fetch_array($preOutStudents)) {
		$dbIDs[] = $outStudent[1];
		if($_GET['type'] == "reload") {
			if(array_search($outStudent[1], $checkIDs) === false) {
				$sendlist["out"][] = array(0 => $outStudent[1], 1 => $outStudent[2]);
			}
		}
	}

	// Id, ФИО, факультет новых студентов
	$IDsAndNames = array();
	for($i=0; $i < count($checkIDs); $i++) {
		if(array_search($checkIDs[$i], $dbIDs) === false) {
			$IDsAndNames[] = array($dataCSV[$i][($CONNECTIONS["id"])], $dataCSV[$i][($CONNECTIONS["sname"])].' '.$dataCSV[$i][($CONNECTIONS["fname"])].' '.$dataCSV[$i][($CONNECTIONS["pname"])].' ('.$dataCSV[$i][($CONNECTIONS["department"])].')');
		}
	}

	$sendlist["CSVid"] = $_GET['csvid'];
	$sendlist["type"] = $_GET['type'];
	$sendlist["datarows"] = count($dataCSV);
	$sendlist["newdeps"] = $newdeps;
	$sendlist["newgroups"] = $newgroups;
	$sendlist["newdata"] = $IDsAndNames;
	$sendlist["error"] = "ok";

	// Запись в файл
	$savedata = array();
	$savedata["type"] = $_GET['type'];
	$savedata["data"] = $dataCSV;
	$savedata["connections"] = $CONNECTIONS;
	$writedata = fopen('content/'.$GLOBALS['config']['csvfile'], 'w');
	fwrite($writedata, json_encode($savedata, JSON_PRETTY_PRINT)); // php 5.6
	fclose($writedata);

	exit(json_encode($sendlist));
}

// Обновление базы студентов
elseif($_GET['act'] == "studentsupload_confirm") {
	if(!$_GET['csvid']) { wrongusing(1); }
	if(!isset($GLOBALS['config']['csvfile']) or ($_GET['csvid'].'.csv') !== $GLOBALS['config']['csvfile'] or !file_exists("content/".$GLOBALS['config']['csvfile'])) { errorjson("Файл для обработки отсутствует на сервере. Повторите загрузку файла."); }

	$csvjson_file = file_get_contents("content/".$GLOBALS['config']['csvfile']);
	$JSONStudents = json_decode($csvjson_file, true);
	if($JSONStudents == NULL) { wrongusing(); }

	$CONNECTIONS = $JSONStudents["connections"];
	$JSONStudentsData = $JSONStudents["data"];

	$IDList = array();
	for($i=0; $i < count($JSONStudentsData); $i++) {
		$IDList[] = $JSONStudentsData[$i][($CONNECTIONS["id"])];
		$checkSIN = mysql_query("SELECT `id`,`type`,`sin` from `users` WHERE `type`='a' AND `sin`='".$JSONStudentsData[$i][($CONNECTIONS["id"])]."';");
		$checkSIN = mysql_fetch_row($checkSIN);

		// Регистрация департаментов
		$prefindfaceid = mysql_query("SELECT `id`,`type`,`area`,`name` from `deps` WHERE `type`='i' AND `name`='".$JSONStudentsData[$i][($CONNECTIONS["department"])]."';");
		$findfaceid = mysql_fetch_row($prefindfaceid);
		if(!$findfaceid[0]) {
			$newDepSQL = "INSERT INTO `deps` (`id`, `type`, `area`, `name`, `full`) VALUES (NULL, 'i', NULL, '".$JSONStudentsData[$i][($CONNECTIONS["department"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["department"])]."');";
			if(!mysql_query($newDepSQL)) { errorjson("Не удалось зарегистрировать факультет. Ошибка базы данных."); }
			$depID = mysql_insert_id();
			$depName = $JSONStudentsData[$i][($CONNECTIONS["department"])];
			$newGroupSQL = "INSERT INTO `deps` (`id`, `type`, `area`, `name`, `full`) VALUES (NULL, 'g', '".$depID."', '".$JSONStudentsData[$i][($CONNECTIONS["group_name"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["group_name"])]."');";
			if(!mysql_query($newGroupSQL)) { errorjson("Не удалось зарегистрировать группу. Ошибка базы данных."); }
			$groupID = mysql_insert_id();
		} else {
			$depID = $findfaceid[0];
			$depName = $findfaceid[3];
			$prefinddep = mysql_query("SELECT `id`,`type`,`area`,`name` from `deps` WHERE `type`='g' AND `name`='".$JSONStudentsData[$i][($CONNECTIONS["group_name"])]."' AND `area`='".$findfaceid[0]."';");
			$finddep = mysql_fetch_row($prefinddep);
			if(!$finddep[0]) {
				$newGroupSQL = "INSERT INTO `deps` (`id`, `type`, `area`, `name`, `full`) VALUES (NULL, 'g', '".$depID."', '".$JSONStudentsData[$i][($CONNECTIONS["group_name"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["group_name"])]."');";
				if(!mysql_query($newGroupSQL)) { errorjson("Не удалось зарегистрировать группу. Ошибка базы данных."); }
				$groupID = mysql_insert_id();
			} else {
				$groupID = $finddep[0];
			}
		}

		if($JSONStudentsData[$i][($CONNECTIONS["pname"])] == "") {
			$fullName = $JSONStudentsData[$i][($CONNECTIONS["sname"])].' '.$JSONStudentsData[$i][($CONNECTIONS["fname"])];
		} else {
			$fullName = $JSONStudentsData[$i][($CONNECTIONS["sname"])].' '.$JSONStudentsData[$i][($CONNECTIONS["fname"])].' '.$JSONStudentsData[$i][($CONNECTIONS["pname"])];
		}

		// Регистрация студента
		if(!$checkSIN[0]) {
			$newpwgen = pwgenerator().pwgenerator();
			$regtoken = md5(uniqid('auth', true).$newpwgen);
			$RegStudentSQL = "INSERT INTO `users` (`id`, `access`, `sin`, `phone`, `password`, `vkauth`, `vktoken`, `type`, `out`, `code`, `fullname`, `sname`, `fname`, `pname`, `sex`, `birthday`, `post`, `fac`, `dep`, `gen`, `form`, `curcourse`, `groupnum`, `budget`, `created`, `addedby`, `count`, `groups`) VALUES (NULL, 'y', '".$JSONStudentsData[$i][($CONNECTIONS["id"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["phone"])]."', '".$regtoken."', NULL, NULL, 'a', 's', '0123456789', '".$fullName." (".$depName.")', '".$JSONStudentsData[$i][($CONNECTIONS["sname"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["fname"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["pname"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["sex"])]."', ".$JSONStudentsData[$i][($CONNECTIONS["birthday"])].", NULL, '".$depID."', '".$groupID."', '".$JSONStudentsData[$i][($CONNECTIONS["edu_standard"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["edu_type"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["edu_level"])]."-".$JSONStudentsData[$i][($CONNECTIONS["course"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["group_num"])]."', '".$JSONStudentsData[$i][($CONNECTIONS["pay"])]."', '".date("Y-m-d H:i:s")."', '".LOGGED_ID."', '0', '[]');";
			if(!mysql_query($RegStudentSQL)) { errorjson("Не удалось зарегистрировать студента. Ошибка базы данных."); }
		} else {
		// Обновление информации о студенте
			$UpdateStudentSQL = "UPDATE `".$GLOBALS['config']['mysql_db']."`.`users` SET `out` = 's',
`sname` = '".$JSONStudentsData[$i][($CONNECTIONS["sname"])]."',
`fname` = '".$JSONStudentsData[$i][($CONNECTIONS["fname"])]."',
`pname` = '".$JSONStudentsData[$i][($CONNECTIONS["pname"])]."',
`fullname` = '".$fullName." (".$depName.")',
`sex` = '".$JSONStudentsData[$i][($CONNECTIONS["sex"])]."',
`birthday` = ".$JSONStudentsData[$i][($CONNECTIONS["birthday"])].",
`gen` = '".$JSONStudentsData[$i][($CONNECTIONS["edu_standard"])]."',
`form` = '".$JSONStudentsData[$i][($CONNECTIONS["edu_type"])]."',
`fac` = '".$depID."',
`dep` =  '".$groupID."',
`curcourse` =  '".$JSONStudentsData[$i][($CONNECTIONS["edu_level"])]."-".$JSONStudentsData[$i][($CONNECTIONS["course"])]."',
`groupnum` =  '".$JSONStudentsData[$i][($CONNECTIONS["group_num"])]."',
`budget` =  '".$JSONStudentsData[$i][($CONNECTIONS["pay"])]."' WHERE `users`.`sin` ='".$JSONStudentsData[$i][($CONNECTIONS["id"])]."';";
			if(!mysql_query($UpdateStudentSQL)) { errorjson("Не удалось обновить информацию о студенте. Ошибка базы данных.".$UpdateStudentSQL); }
		}
	}

	// Перевод в Архив студентов
	if($JSONStudents["type"] == "reload") {
		$allStudents = mysql_query("SELECT `id`,`sin`,`fullname` from `users` WHERE `type`='a' AND `out`='s';");
		while($OutStudent = mysql_fetch_array($allStudents)) {
			if(array_search($OutStudent[1], $IDList) === false) {
				$DestroyStudentSQL = "UPDATE `".$GLOBALS['config']['mysql_db']."`.`users` SET `out`='o' WHERE `users`.`sin` ='".$OutStudent[1]."';";
				if(!mysql_query($DestroyStudentSQL)) { errorjson("Не удалось перевести студента в Архив студентов. Ошибка базы данных."); }
			}
		}
	}

	unlink("content/".$GLOBALS['config']['csvfile']);
	unset($GLOBALS['config']['csvfile']);
	config_save();
	errorjson("ok");
}








else { wrongusing(); }
?>
