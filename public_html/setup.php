<?php
ini_set('display_errors', 0); // 1
error_reporting(0); // E_ALL
ini_set('upload_max_filesize', '1M');
ini_set('post_max_size', '2M');
date_default_timezone_set('Europe/Moscow');

// Проверка .htaccess
if(!file_exists(__DIR__.'/.htaccess')) {
	$htaccess = fopen(".htaccess", "a+");
	fwrite($htaccess, "RewriteEngine On
RewriteCond %{ENV:HTTPS} !on [NC]
RewriteRule ^(.*)$ https://{HTTP_HOST}/$1 [R,L]
RewriteRule ^([^/.]+)$ index.php [L]
DirectoryIndex index.php
AddDefaultCharset utf-8
Options -Indexes");
	fclose($htaccess);
	if(!file_exists('.htaccess')) { exit("Ошибка записи .htaccess файла. Установите права на запись."); }
}

$db_create = "
CREATE TABLE `activity` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `event` bigint(20) NOT NULL,
  `user` bigint(20) NOT NULL,
  `role` varchar(10) NOT NULL,
  `created` datetime NOT NULL,
  `addedby` bigint(20) NOT NULL,
  `complex` varchar(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event` (`event`),
  KEY `user` (`user`),
  KEY `event_2` (`event`),
  KEY `user_2` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `config` (
  `key` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `config` VALUES ('organization_department',''),('organization_favicon','img/favicon.ico?5bd07058de180'),('organization_form',''),('organization_fullname',''),('organization_logo','img/org_logo.svg?5bd07058de063'),('organization_shortname',''),('organizations_order','[\"1\"]'),('rating_complex','1.5'),('rating_levels','{\"f\":1,\"u\":2,\"c\":3,\"r\":4,\"v\":5,\"i\":6}'),('rating_muscle','0.2'),('rating_oneday','0.1'),('rating_roles','{\"b\":0,\"u\":1,\"p\":2,\"w\":3,\"l\":1,\"m\":3,\"h\":4}'),('sms_channel',''),('sms_login',''),('sms_name',''),('sms_pw',''),('vk_id',''),('vk_secret',''),('vk_state','0');

CREATE TABLE `deps` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) NOT NULL,
  `area` bigint(20) DEFAULT NULL,
  `name` varchar(500) NOT NULL,
  `full` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `place` varchar(500) DEFAULT NULL,
  `date` date NOT NULL,
  `date_for` date DEFAULT NULL,
  `time_since` time NOT NULL,
  `time_for` time NOT NULL,
  `comment` longtext,
  `level` varchar(10) NOT NULL,
  `dep` bigint(11) DEFAULT NULL,
  `holder` bigint(20) NOT NULL,
  `created` datetime NOT NULL,
  `author` bigint(20) NOT NULL,
  `fixers` text,
  `outside` varchar(1) NOT NULL,
  `complex` varchar(1) NOT NULL,
  `tags` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `rights` text NOT NULL,
  `public` varchar(2) NOT NULL,
  `icon` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `holder` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tags` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(1) NOT NULL,
  `name` varchar(150) NOT NULL,
  `comment` text,
  `style` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

INSERT INTO `tags` VALUES (1,'a','Общественная',NULL,'0000000001.png'),(2,'a','Научно-исследовательская',NULL,'0000000002.png'),(3,'a','Творческая',NULL,'0000000003.png'),(4,'a','Спортивная',NULL,'0000000004.png'),(5,'e','Организационное (сопроводительное) мероприятие',NULL,'#45926b'),(6,'e','Воспитательное/патриотическое',NULL,'#be3f3f'),(7,'e','Благотворительное',NULL,'#3f8dbe'),(8,'e','Конкурс/Соревнование',NULL,'#e5882d'),(9,'e','Концертная программа',NULL,'#b26bb3'),(10,'e','Приуроченная акция (не благотворительная)',NULL,'#bcb842'),(11,'e','Выпуск периодического продукта',NULL,'#5cb77c'),(12,'e','Форум/Конференция',NULL,'#59aaa9'),(13,'e','Прием/Почетная встреча',NULL,'#795a5a');

CREATE TABLE `temp_sz` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `header` text NOT NULL,
  `title` varchar(500) NOT NULL,
  `post` varchar(300) NOT NULL,
  `sign` varchar(300) NOT NULL,
  `content` longtext NOT NULL,
  `holder` bigint(20) NOT NULL,
  `area` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tokens` (
  `token` varchar(70) NOT NULL,
  `lastip` varchar(50) NOT NULL,
  `deadline` datetime NOT NULL,
  `user` bigint(20) NOT NULL,
  PRIMARY KEY (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Токены доступа';

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `access` varchar(1) NOT NULL,
  `sin` varchar(15) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `password` varchar(150) NOT NULL,
  `vkauth` int(50) DEFAULT NULL,
  `vktoken` varchar(300) DEFAULT NULL,
  `type` varchar(10) NOT NULL,
  `out` varchar(6) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `fullname` varchar(150) NOT NULL,
  `sname` varchar(150) NOT NULL,
  `fname` varchar(150) NOT NULL,
  `pname` varchar(150) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `birthday` date DEFAULT NULL,
  `post` varchar(300) DEFAULT NULL,
  `fac` varchar(2) DEFAULT NULL,
  `dep` bigint(20) NOT NULL,
  `form` varchar(3) DEFAULT NULL,
  `curcourse` varchar(10) DEFAULT NULL,
  `groupnum` varchar(10) DEFAULT NULL,
  `budget` varchar(1) DEFAULT NULL,
  `created` datetime NOT NULL,
  `addedby` bigint(20) NOT NULL,
  `count` bigint(20) NOT NULL,
  `groups` text,
  `ic_1` bigint(20) DEFAULT NULL,
  `ic_2` bigint(20) DEFAULT NULL,
  `ic_3` bigint(20) DEFAULT NULL,
  `ic_4` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `type` (`type`),
  KEY `out` (`out`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `addme` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(1) NOT NULL,
  `event` bigint(20) NOT NULL,
  `sid` bigint(20) NOT NULL,
  `role` varchar(1) NOT NULL,
  `complex` varchar(1) NOT NULL,
  `comment` longtext NOT NULL,
  `executer` bigint(20) NOT NULL,
  `answer` longtext,
  `status` varchar(1) NOT NULL,
  `story` text NOT NULL,
  `see` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

// Загрузка конфигурации БД
$GLOBALS['config_db'] = include '../settings/config_db.php';
foreach ($GLOBALS['config_db'] as $key => $value) { if($GLOBALS['config_db'][$key] == "") { unset($GLOBALS['config_db'][$key]); } }

if($GLOBALS['config_db']['mysql_user']) {
	header("Location: /");
	exit();
}

foreach ($_POST as $key => $value) { $_POST[$key] = addslashes($value); if($_POST[$key] == "") { unset($_POST[$key]); } }
if($_POST['act'] == 'setup') {
	if(!$_POST['setup_onlydb'] or !$_POST['setup_dblogin'] or !$_POST['setup_dbpw'] or !$_POST['setup_dbname']) { exit("Нестандартное использование сервиса"); }
	if(!mysql_connect('localhost', $_POST['setup_dblogin'], $_POST['setup_dbpw'])) {
		exit("Ошибка авторизации в MySQL. Проверьте работу MySQL, правильность введенных логина, пароля и названия БД.");
	} else {
		$GLOBALS['config_db']['mysql_user'] = $_POST['setup_dblogin'];
		$GLOBALS['config_db']['mysql_pw'] = $_POST['setup_dbpw'];
		$GLOBALS['config_db']['mysql_db'] = $_POST['setup_dbname'];
	}

	// Использование уже установленной БД
	if($_POST['setup_onlydb'] == "true") {
		mysql_set_charset('utf8');
		mysql_select_db($_POST['setup_dbname']);
		$precheckuid = mysql_query("SELECT `id`,`access`,`type` from `users` WHERE `access`='y' AND `type`='s' LIMIT 1");
		$checkuid = mysql_fetch_row($precheckuid);
		if(!$checkuid[0]) { exit("Данная БД не может быть использована для работы системы. Используйте другую БД."); }

		file_put_contents('../settings/config_db.php', '<?php return ' . var_export($GLOBALS['config_db'], true) . ';');
		exit("ok");
	}

	if(!$_POST['setup_sname'] or !$_POST['setup_fname'] or !$_POST['setup_phone'] or !$_POST['setup_pw']) { exit("Нестандартное использование сервиса"); }
	if(!preg_match('/^[0-9]{10}$/', $_POST['setup_phone'])) { exit("Неверный формат телефонного номера"); }
	//if(mb_strlen($_POST['setup_pw'], "UTF-8") !== 32) { exit("Неверный формат пароля"); }


	// Создание БД
	mysql_set_charset('utf8');
	//$dbsetup_file = file_get_contents("../settings/setdb.txt", FILE_USE_INCLUDE_PATH);
	$firstSQLQuery = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
CREATE DATABASE IF NOT EXISTS `'.$_POST['setup_dbname'].'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
USE `'.$_POST['setup_dbname'].'`;'.$db_create;
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
	if(!mysql_query("INSERT INTO `deps` (`id`, `type`, `area`, `name`, `full`) VALUES (1, 'd', NULL, 'СО', 'Совет обучающихся');")) { $error = mysql_error(); mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Ошибка БД: ".$error); }

	// Создание основного шаблона служебных записк
	if(!mysql_query("INSERT INTO `temp_sz` (`id`, `name`, `header`, `title`, `post`, `sign`, `content`, `holder`, `area`) VALUES (NULL, 'Основной шаблон', '&lt;p&gt;Руководителю образовательной организации&lt;/p&gt;&lt;p&gt;&lt;br data-mce-bogus=&quot;1&quot;&gt;&lt;/p&gt;&lt;p&gt;от ...&lt;/p&gt;', '&lt;p&gt;&lt;strong&gt;служебная записка&lt;/strong&gt;&lt;br&gt;&lt;/p&gt;', '-', '&lt;p&gt;И.О. Фамилия&lt;/p&gt;', '&lt;p&gt;Это основной шаблон. Отредактируйте его и сохраните для дальнейшего использования.&lt;/p&gt;', '1', '1');")) { $error = mysql_error(); mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Ошибка БД: ".$error); }

	// Регистрация администратора
	if(!mysql_query("INSERT INTO `users` (`id`, `access`, `sin`, `phone`, `password`, `vkauth`, `vktoken`, `type`, `out`, `code`, `fullname`, `sname`, `fname`, `pname`, `sex`, `birthday`, `post`, `fac`, `dep`, `form`, `curcourse`, `groupnum`, `budget`, `created`, `addedby`, `count`, `groups`) VALUES (NULL, 'y', '".$_POST['setup_phone']."', '".$_POST['setup_phone']."', '".md5($_POST['setup_pw'])."', NULL, NULL, 's', NULL, NULL, '".$_POST['setup_sname']." ".$_POST['setup_fname']."', '".$_POST['setup_sname']."', '".$_POST['setup_fname']."', '-', 'm', '1990-01-01', 'Администратор', '', '1', NULL, NULL, NULL, NULL, '".date("Y-m-d H:i:s")."', '1', '0', '[]');")) { $error = mysql_error(); mysql_query("DROP DATABASE `".$_POST['setup_dbname']."`;"); exit("Не удалось зарегистрировать администратора. Ошибка БД: ".$error); }

	file_put_contents('../settings/config_db.php', '<?php return ' . var_export($GLOBALS['config_db'], true) . ';');
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

			var re = /^(?=.*\d)(?=.*[a-z])[0-9a-zA-Z]{6,30}$/;
			if(!re.test($("input[name=loginauth_pw1]").val())) {
			  alert("Неверный формат пароля. Длина пароля от 6 до 30 символов. Используйте буквы латинского алфавита и цифры.");
			  return false;
			}
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
