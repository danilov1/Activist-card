<?php
ini_set('display_errors', 0); // 1
error_reporting(0); // E_ALL
ini_set('upload_max_filesize', '1M');
ini_set('post_max_size', '2M');
date_default_timezone_set('Europe/Moscow');

if(!file_exists('.htaccess')) { 
	header("Location: index.php");
	exit();
}

$config_file = file_get_contents("../settings/config_global.json");
$GLOBALS['config'] = json_decode($config_file, true);
if($GLOBALS['config']['mysql_user'] !== "") {
	header("Location: /");
	exit();
}

foreach ($_POST as $key => $value) { $_POST[$key] = addslashes($value); if($_POST[$key] == "") { unset($_POST[$key]); } }
if($_POST['act'] == 'setup') {
	if(!$_POST['setup_onlydb'] or !$_POST['setup_dblogin'] or !$_POST['setup_dbpw'] or !$_POST['setup_dbname']) { exit("Нестандартное использование сервиса"); }
	if(!mysql_connect('localhost', $_POST['setup_dblogin'], $_POST['setup_dbpw'])) {
		exit("Ошибка авторизации в MySQL. Проверьте работу MySQL, правильность введенных логина, пароля и названия БД.");
	} else {
		$GLOBALS['config']['mysql_user'] = $_POST['setup_dblogin'];
		$GLOBALS['config']['mysql_pw'] = $_POST['setup_dbpw'];
		$GLOBALS['config']['mysql_db'] = $_POST['setup_dbname'];
	}
	
	// Использование уже установленной БД
	if($_POST['setup_onlydb'] == "true") {
		mysql_set_charset('utf8');
		mysql_select_db($_POST['setup_dbname']);
		$precheckuid = mysql_query("SELECT `id`,`access`,`type` from `users` WHERE `access`='y' AND `type`='s' LIMIT 1");
		$checkuid = mysql_fetch_row($precheckuid);
		if(!$checkuid[0]) { exit("Данная БД не может быть использована для работы системы. Используйте другую БД."); }
		
		$fp = fopen('../settings/config_global.json', 'w');
		fwrite($fp, json_encode($GLOBALS['config'], JSON_PRETTY_PRINT)); // php 5.6
		fclose($fp);
		exit("ok");
	}
	
	if(!$_POST['setup_sname'] or !$_POST['setup_fname'] or !$_POST['setup_phone'] or !$_POST['setup_pw']) { exit("Нестандартное использование сервиса"); }
	if(!preg_match('/^[0-9]{10}$/', $_POST['setup_phone'])) { exit("Неверный формат телефонного номера"); }
	//if(mb_strlen($_POST['setup_pw'], "UTF-8") !== 32) { exit("Неверный формат пароля"); }
	
	// Создание БД
	mysql_set_charset('utf8');
	$dbsetup_file = file_get_contents("../settings/setdb.txt", FILE_USE_INCLUDE_PATH);
	$firstSQLQuery = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `'.$_POST['setup_dbname'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `'.$_POST['setup_dbname'].'`;'.$dbsetup_file;
	$file_content = preg_split("/\r\n|\n|\r/", $firstSQLQuery);
	foreach ($file_content as $line) {
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;
		$templine .= $line;
		if (substr(trim($line), -1, 1) == ';') {
			if(!mysql_query($templine)) {
				 $dberrortext = mysql_error();
				 mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;");
				 exit('Ошибка запроса к БД: ' . $dberrortext . '');
			}
			$templine = '';
		}
	}

	// Регистрация студенческого подразделения
	if(!mysql_query("INSERT INTO `deps` (`id`, `type`, `area`, `name`, `full`) VALUES (1, 'd', NULL, 'СО', 'Совет обучающихся');")) { mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Ошибка БД: ".mysql_error()); }
	
	// Создание основного шаблона служебных записк
	if(!mysql_query("INSERT INTO `temp_sz` (`id`, `name`, `header`, `title`, `post`, `sign`, `content`, `holder`, `area`) VALUES (NULL, 'Основной шаблон', '&lt;p&gt;Руководителю образовательной организации&lt;/p&gt;&lt;p&gt;&lt;br data-mce-bogus=&quot;1&quot;&gt;&lt;/p&gt;&lt;p&gt;от ...&lt;/p&gt;', '&lt;p&gt;&lt;strong&gt;служебная записка&lt;/strong&gt;&lt;br&gt;&lt;/p&gt;', '-', '&lt;p&gt;И.О. Фамилия&lt;/p&gt;', '&lt;p&gt;Это основной шаблон. Отредактируйте его и сохраните для дальнейшего использования.&lt;/p&gt;', '1', '1');")) { mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Ошибка БД: ".mysql_error()); }
	
	// Регистрация администратора
	if(!mysql_query("INSERT INTO `users` (`id`, `access`, `sin`, `phone`, `password`, `vkauth`, `vktoken`, `type`, `out`, `code`, `fullname`, `sname`, `fname`, `pname`, `sex`, `birthday`, `post`, `fac`, `dep`, `gen`, `form`, `curcourse`, `groupnum`, `budget`, `created`, `addedby`, `count`, `groups`) VALUES (NULL, 'y', '".$_POST['setup_phone']."', '".$_POST['setup_phone']."', '".$_POST['setup_pw']."', NULL, NULL, 's', NULL, NULL, '".$_POST['setup_sname']." ".$_POST['setup_fname']."', '".$_POST['setup_sname']."', '".$_POST['setup_fname']."', '-', 'm', '1990-01-01', 'Администратор', '', '1', NULL, NULL, NULL, NULL, NULL, '".date("Y-m-d H:i:s")."', '1', '0', '[]');")) { mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Не удалось зарегистрировать администратора. Ошибка БД: ".mysql_error()); }
	
	$fp = fopen('../settings/config_global.json', 'w');
	fwrite($fp, json_encode($GLOBALS['config'], JSON_PRETTY_PRINT)); // php 5.6
	fclose($fp);
	exit("ok");
}
?>
<!DOCTYPE html>
<!--[if IE 7]>                  <html class="ie7 no-js" lang="en">        <![endif]-->
<!--[if lte IE 8]>              <html class="ie8 no-js" lang="en">        <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="not-ie no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<title>КАРТА АКТИВИСТА</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
	
	<!-- google fonts --> <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700|Roboto+Slab:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<!-- google fonts --> <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700&subset=latin,cyrillic,cyrillic-ext' rel='stylesheet' type='text/css'>
	<!-- bootstrap    --> <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
	<!-- jquery       --> <script src="js/jquery.js"></script>
	<!-- common style --> <link href="css/style.css" rel="stylesheet" type="text/css" />
	<!-- input mask   --><script src="js/md5.js"></script>
	<!-- input mask   --><script src="js/jquery.maskedinput.min.js"></script>

	<script type="text/javascript">

	$(function() {
		$(".page").delay(600).fadeTo(500,1);
		$("input[name=setup_phone]").mask("(999)999-99-99");
	});
	
	function changeonlydb() {
		if($("input[name=setup_onlydb]").prop("checked") == true) {
			$(".setup_onlydb_box").slideUp();
		} else {
			$(".setup_onlydb_box").slideDown();
		}
	}
	
	function setup() {
		if($("input[name=setup_dblogin]").val().trim() == "" || $("input[name=setup_dbpw]").val() == "" || $("input[name=setup_dbname]").val().trim() == "") { return false; }
		
		phoneformat = "";
		if($("input[name=setup_onlydb]").prop("checked") == false) {
			if($("input[name=setup_sname]").val().trim() == "" || $("input[name=setup_fname]").val().trim() == "" || $("input[name=setup_phone]").val().trim() == "" || $("input[name=loginauth_pw1]").val() == "" || $("input[name=loginauth_pw2]").val() == "") { return false; }
			if($("input[name=loginauth_pw1]").val() !== $("input[name=loginauth_pw2]").val()) { alert("Повтор пароля введен неверно"); return false; }
			phoneformat = $("input[name=setup_phone]").val().replace(/[-()]/g,"");
			
			/*var re = /^(?=.*\d)(?=.*[a-z])[0-9a-zA-Z]{6,30}$/;
			if(!re.test($("input[name=loginauth_pw1]").val())) {
			  alert("Неверный формат пароля. Длина пароля от 6 до 30 символов. Используйте буквы латинского алфавита и цифры.");
			  return false;
			}*/
		}
		
		$.ajax({
		  timeout: 300000,
		  type: "POST",
		  url: "setup.php",
		  data: {
			act: "setup",
			setup_onlydb: $("input[name=setup_onlydb]").prop("checked"),
			setup_dblogin: $("input[name=setup_dblogin]").val().trim(),
			setup_dbpw: $("input[name=setup_dbpw]").val(),
			setup_dbname: $("input[name=setup_dbname]").val().trim(),
			setup_sname: $("input[name=setup_sname]").val().trim(),
			setup_fname: $("input[name=setup_fname]").val().trim(),
			setup_phone: phoneformat,
			setup_pw: $("input[name=loginauth_pw1]").val() // не md5
		  },
		  beforeSend: function() { $('.fillpage').fadeIn(300); },
		})
		.done(function(data) {
		  $('.fillpage').fadeOut();
		  if(data == 'ok') {
			alert("Система готова к работе!");
			window.location = "/";
		  }
		  else {
			alert(data);
		  }
		});
	}

	</script>

	</head>



<body class="page_index">
	<div class="fillpage">
		<div class="loading">Пожалуйста, подождите...</div>
	</div>
		<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
				<div class="row-fluid preloginbox">
					<div class="span4"></div>
					<div class="span4">
						<form class="centerform" method="post" onsubmit="setup()" autocomplete="off" style="position:relative; top:10px; margin-top:0 !important;">
							<div>
								<div style="position:relative; margin:0 0 10px 0; text-align:center;">
									<div class="head_title">НАЧАЛО РАБОТЫ</div>
								</div>
							</div>
							<b>Доступ к БД MySQL:</b>
							<div class="row-fluid"><div class="span12"><input name="setup_dblogin" class="span12" type="text" placeholder="Логин БД" /></div></div>
							<div class="row-fluid"><div class="span12"><input name="setup_dbpw" class="span12" type="password" placeholder="Пароль БД" /></div></div>
							<div class="row-fluid"><div class="span12"><input name="setup_dbname" class="span12" type="text" placeholder="Название БД" /></div></div>
							<div class="controls">
								<label class="checkbox">
									<input name="setup_onlydb" type="checkbox" onChange="changeonlydb()"> Данная БД уже существует и она хранит данные АИС "Карта активиста". Не перезаписывать ее.
								</label>
							</div>
							<hr>
							<div class="setup_onlydb_box">
								<b>Администратор:</b>
								<div class="row-fluid"><div class="span12"><input name="setup_sname" class="span12" type="text" placeholder="Фамилия" /></div></div>
								<div class="row-fluid"><div class="span12"><input name="setup_fname" class="span12" type="text" placeholder="Имя" /></div></div>
								<div class="row-fluid"><div class="span12"><input name="setup_phone" class="span12" type="text" placeholder="Номер телефона" /></div></div>
								<input name="loginauth_pw1" class="span12" type="password" placeholder="Пароль" />
								<input name="loginauth_pw2" class="span12" type="password" placeholder="Повтор пароля" />
							</div>
							<div class="center">
								<div><a class="btn1 loginauth_submit" href="" onclick="setup(); return false;">Установить систему</a></div>
							</div>
						</form>
					</div>
					<div class="span4"></div>
				</div>
			</div>
		</section>
	</section>
	<footer>АИС "КАРТА АКТИВИСТА"</footer>
</body>

</html>