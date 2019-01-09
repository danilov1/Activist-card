<?php
ini_set('display_errors', 0);
error_reporting(0); // E_ALL
ini_set('upload_max_filesize', '1M');
ini_set('post_max_size', '2M');
date_default_timezone_set('Europe/Moscow');
if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') { define("PROTOCOL", "https://"); } else { define("PROTOCOL", "http://"); }

define("filesversion", "2.5.20190109");

// Загрузка конфигурации БД
$GLOBALS['config_db'] = include '../settings/config_db.php';
foreach ($GLOBALS['config_db'] as $key => $value) { if($GLOBALS['config_db'][$key] == "") { unset($GLOBALS['config_db'][$key]); } }

// Проверка .htaccess и БД
if(!file_exists('.htaccess') or !$GLOBALS['config_db']['mysql_user']) {
	header("Location: setup.php");
	exit();
}

function errorjson($ejs_text) { $senderror["error"] = $ejs_text; exit(json_encode($senderror)); }
function wrongusing($wu_text) { errorjson("Нестандартное использование сервиса ".$wu_text.""); }

// Входное экранирование
foreach ($_GET as $key => $value) { $_GET[$key] = addslashes($value); if($_GET[$key] == "") { unset($_GET[$key]); } }
foreach ($_POST as $key => $value) { $_POST[$key] = addslashes($value); if($_POST[$key] == "") { unset($_POST[$key]); } }
foreach ($_COOKIE as $key => $value) { $_COOKIE[$key] = addslashes($value); if($_COOKIE[$key] == "") { unset($_COOKIE[$key]); } }

// Подключение к БД
if(!mysql_connect('localhost', $GLOBALS['config_db']['mysql_user'], $GLOBALS['config_db']['mysql_pw'])) {
	exit("Ошибка подключения к БД.");
}
mysql_set_charset('utf8');
mysql_select_db($GLOBALS['config_db']['mysql_db']);

// Загрузка конфигурации системы
$config_load = mysql_query("SELECT `key`,`value` from `config` WHERE 1;");
$GLOBALS['config'] = array();
while($config_set = mysql_fetch_array($config_load)) { $GLOBALS['config'][$config_set[0]] = $config_set[1]; }

// Проверка прав доступа
function access_check() {
	if(!$_COOKIE['a']) {
		if(!$_GET['access_token']) { define("LOGGEDIN", "NO"); }
		else {
			$_prebytoken = "SELECT `token`,`lastip`,`deadline`,`user` from `tokens` WHERE `token`='".$_GET['access_token']."' AND `lastip`='".get_client_ip()."' LIMIT 1";
			$prebytoken = mysql_query($_prebytoken);
			$bytoken = mysql_fetch_row($prebytoken);
			if(!$bytoken[0]) { destroylogin(); define("LOGGEDIN", "NO"); }
			else {
				$setdate = strtotime($bytoken[2]);
				if($setdate<time()) { destroylogin(); define("LOGGEDIN", "NO"); }
				else {
					$prelogin = mysql_query("SELECT `id`,`type`,`sname`,`fname` from `users` WHERE `id`='".$bytoken[3]."' LIMIT 1");
					$login = mysql_fetch_row($prelogin);
					define("LOGGEDIN", "YES");
					define("LOGGED_ID", $login[0]);
					define("LOGGED_ACCESS", $login[1]);
					define("LOGGED_SURNAME", $login[2]);
					define("LOGGED_FIRSTNAME", $login[3]);
				}
			}
		}
	}
	else {
		$_prebytoken = "SELECT `token`,`lastip`,`deadline`,`user` from `tokens` WHERE `token`='".$_COOKIE['a']."' AND `lastip`='".get_client_ip()."' LIMIT 1";
		$prebytoken = mysql_query($_prebytoken);
		$bytoken = mysql_fetch_row($prebytoken);
		if(!$bytoken[0]) { destroylogin(); define("LOGGEDIN", "NO"); }
		else {
			$setdate = strtotime($bytoken[2]);
			if($setdate<time()) { destroylogin(); define("LOGGEDIN", "NO"); }
			else {
				$prelogin = mysql_query("SELECT `id`,`type`,`sname`,`fname` from `users` WHERE `id`='".$bytoken[3]."' LIMIT 1");
				$login = mysql_fetch_row($prelogin);
				define("LOGGEDIN", "YES");
				define("LOGGED_ID", $login[0]);
				define("LOGGED_ACCESS", $login[1]);
				define("LOGGED_SURNAME", $login[2]);
				define("LOGGED_FIRSTNAME", $login[3]);
			}
		}
	}
}
access_check();

function destroylogin() { setcookie('a', null, -1, '/'); }
function access() { if(LOGGEDIN == "NO") { errorjson("authError"); } }
function accesspage() { if(LOGGEDIN == "NO") { header("Location: /"); die(); } }

function accessto($access_list) {
	$access_types = explode(",", $access_list);
	if(array_search(LOGGED_ACCESS, $access_types) === false) { errorjson("Ошибка доступа"); }
}

// Авторизация через систему организации
include '../settings/anotherAuth.php';

// Стандартная авторизация
function gologin() {
	if(LOGGEDIN == "YES") { errorjson("ok"); }
	else {
		if((!$_POST['l']) or (!$_POST['p'])) { errorjson("access_wrong"); }
		//if((!$_POST['l']) or (!$_POST['p']) or (!$_POST['token'])) { errorjson("access_wrong"); }
		$curip = get_client_ip();
		if($curip == "UNKNOWN") { errorjson("Невозможно распознать IP-адрес клиента"); }
		//if(!$_POST['token']) { errorjson("Ошибка сессии. Обновите страницу. #1"); }

		// RECAPTURE
		/*
		require_once('../plugins/request/Requests.php');
		Requests::register_autoloader();
		try {
			$Request_RECAPTURE = Requests::post('https://www.google.com/recaptcha/api/siteverify', array(), array(
				'secret' => '6LcIVmwUAAAAACLOb9IYsDygqWlaDCdVEH7oB7Nb',
				'response' => $_POST['token'],
				'remoteip' => $curip
			));
			$isRequest_RECAPTURE = $Request_RECAPTURE->body;
		} catch (Exception $e) { errorjson("Ошибка сессии. Обновите страницу. #2"); }
		if(isset($isRequest_RECAPTURE)) {
			$isRequest_RECAPTURE = json_decode($isRequest_RECAPTURE, true);
			if($isRequest_RECAPTURE["success"] !== true or $isRequest_RECAPTURE["hostname"] !== $_SERVER['SERVER_NAME']) { errorjson("Ошибка сессии. Обновите страницу. #3"); }
		}
		else { errorjson("Ошибка сессии. Обновите страницу. #4"); }
		*/

		$pregetaccess = mysql_query("SELECT `id`,`sin`,`password`,`type` from `users` WHERE `sin`='".$_POST['l']."' AND `access` ='y' LIMIT 1");
		$getaccess = mysql_fetch_row($pregetaccess);
		if(!$getaccess[0]) { errorjson("access_wrong"); }
		else {
			// Если пароль сгенерирован в 5 символов
			if(mb_strlen($getaccess[2],"UTF-8") == 5) { $comparewith = md5($getaccess[2]); }
			else { $comparewith = $getaccess[2]; }
			if(md5($_POST['p']) == $comparewith or anotherAuth($_POST['l'],$_POST['p']) == "success") {
				// Формирование токена
				$pretoken1 = uniqid('auth1',true).$getaccess[0];
				$pretoken1 = md5(md5($pretoken1));
				$pretoken2 = uniqid('auth2',true).$getaccess[2];
				$pretoken2 = md5(md5($pretoken2));
				$newtoken = substr($pretoken2,16).substr($pretoken1,16).substr($pretoken2,0,16).substr($pretoken1,0,16);
				$newtime = time()+3600*24*20;
				$fromunix = date("Y-m-d H:i:s", $newtime);
				$currenttime = date("Y-m-d H:i:s", time());
				$findtokens = "DELETE FROM `".$GLOBALS['config_db']['mysql_db']."`.`tokens` WHERE `tokens`.`deadline`<='".$currenttime."';";
				if(!mysql_query($findtokens)) { errorjson("Ошибка базы данных. Повторите попытку позже. #1"); }
				$authreq = "INSERT INTO `".$GLOBALS['config_db']['mysql_db']."`.`tokens` (`token`, `lastip`, `deadline`, `user`) VALUES ('".$newtoken."', '".$curip."', '".$fromunix."', '".$getaccess[0]."')";
				if(!mysql_query($authreq)) { errorjson("Ошибка базы данных. Повторите попытку позже. #2"); }
				setcookie("a",$newtoken,$newtime,"/","",false,true);
				errorjson("ok");
			}
			else {
				errorjson("access_wrong");
			}
		}
	}
}

function is($vars) {
	if(gettype($vars) !== "array") { errorjson("Внутренняя ошибка: переменные не помещены в массив"); }
	foreach ($vars as $EVERYVAR) {
		if(!$EVERYVAR or !isset($EVERYVAR) or $EVERYVAR == "") {  unset($EVERYVAR); return false; }
	}
	unset($EVERYVAR); return true;
}

function same($var, $values) {
	$checkvalue = "";
	$nums = count($values);
	$counter = 0;
	if(gettype($values) !== "array") { errorjson("Внутренняя ошибка: значения не помещены в массив"); }
	foreach ($values as $EVERYVALUE) {
		$checkvalue .= '($var !== "'.$EVERYVALUE.'")';
		$counter++;
		if($counter !== $nums) { $checkvalue .= ' and '; }
	}
	eval('if('.$checkvalue.') { unset($EVERYVALUE); return false; }');
	unset($EVERYVALUE); return true;
}

function get_client_ip() {
     $ipaddress = '';
     if ($_SERVER['HTTP_CLIENT_IP'])
         $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
     else if($_SERVER['HTTP_X_FORWARDED_FOR'])
         $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
     else if($_SERVER['HTTP_X_FORWARDED'])
         $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
     else if($_SERVER['HTTP_FORWARDED_FOR'])
         $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
     else if($_SERVER['HTTP_FORWARDED'])
         $ipaddress = $_SERVER['HTTP_FORWARDED'];
     else if($_SERVER['REMOTE_ADDR'])
         $ipaddress = $_SERVER['REMOTE_ADDR'];
     else
         $ipaddress = 'UNKNOWN';

     return $ipaddress;
}

function uploaderror($filevar) {
	if($filevar['error'] > 0)
	{
	 switch ($filevar['error'])
	 {
	 case 1: errorjson("Размер файла превышает допустимое значение. UMFS."); break;
	 case 2: errorjson("Размер файла превышает допустимое значение. MFS."); break;
	 case 3: errorjson("Не удалось загрузить часть файла."); break;
	 case 4: errorjson("Файл не был загружен."); break;
	 case 6: errorjson("Отсутствует временная папка."); break;
	 case 7: errorjson("Не удалось записать файл."); break;
	 case 8: errorjson("Расширение остановило загрузку файла."); break;
	 }
	 return true;
	}
}

function pwgenerator() {
	$chars = 'abcdefghijklmnopqrstuvwxyz';
	$numchars = strlen($chars);
	$string = '';
	for ($i = 0; $i < 5; $i++) {
		$string .= substr($chars, rand(1, $numchars) - 1, 1);
	}
	return $string;
}

function pwcheck($pw_password) {
	if((!preg_match('/^(?=.*\d)(?=.*[a-z])[0-9a-zA-Z]{6,30}$/', $pw_password)) or (!ctype_alnum($pw_password))) { return false; }
	else { return true; }
}

function datecheck($dc_date, $dc_error) {
	if(!preg_match('/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/', $dc_date)) { errorjson($dc_error); }
	list($dc_day, $dc_month, $dc_year) = split('[.]', $dc_date);
	if(!checkdate($dc_month, $dc_day, $dc_year)) { errorjson($dc_error); }
	$dc_return = $dc_year."-".$dc_month."-".$dc_day;
	return $dc_return;
}

function timecheck($tc_time) {
	if(!preg_match("/(2[0-3]|[01][0-9]):[0-5][0-9]/", $tc_time)) { return false; }
}

function activityPoints($up_event, $up_role, $up_complex) {
	// Роли
	$roles = json_decode($GLOBALS['config']['rating_roles'], true);
	// Уровни
	$levels = json_decode($GLOBALS['config']['rating_levels'], true);
	// Коеффициент за "тяжелое" мероприятие
	$complex = $GLOBALS['config']['rating_complex']; // от всего
	// Коеффициент за "мускул"
	$muscle = $GLOBALS['config']['rating_muscle'];
	// Коеффициент за каждый дополнительный день
	$oneday = $GLOBALS['config']['rating_oneday'];
	// Верификация параметров
	$precheckeid = mysql_query("SELECT `id`,`level`,`date`,`date_for`,`complex` from `events` WHERE `id`='".$up_event."' LIMIT 1");
	$checkeid = mysql_fetch_row($precheckeid);
	if(!$checkeid[1]) { errorjson("Мероприятие не найдено"); }
	if(!array_key_exists($up_role, $roles)) { errorjson("Неверно задана роль"); }
	if(!array_key_exists($checkeid[1], $levels)) { errorjson("Неверно задан уровень"); }
	// Количество дней
	if($checkeid[3] !== NULL) {
		$d1 = strtotime($checkeid[2]);
		$d2 = strtotime($checkeid[3]);
		$diff = $d2-$d1;
		$diff = $diff/86400;
		$dayadd = floor($diff);
	} else { $dayadd = 0; }
	// Мускул
	if($up_complex == "n") { $muscle = 0; }
	// Уровень сложности
	if($checkeid[4] == "n") { $complex = 1; }

	// Баллы за мероприятие (если задана роль):
	// ПервичныйБалл = (Роль + Уровень) * КоеффициентСложности
	// Округлить до запятой (ПервичныйБалл + ПервичныйБалл*КолвоДней*КоеффициентДополнительныхДней + ПервичныйБалл*КоеффициентМускул)
	if($up_role == "b") { $RenderPoints = 0; return $RenderPoints; }
	$PrimaryPoints = ($roles[$up_role] + $levels[$checkeid[1]]) * $complex;
	$RenderPoints = round($PrimaryPoints + $PrimaryPoints*$dayadd*$oneday + $PrimaryPoints*$muscle);
	return $RenderPoints;
}

function checkbc($cc_code) {
	if(!preg_match('/^[0-9]{10}$/', $cc_code)) { return false; }
	else { return true; }
}

function sendsms($pn, $msg) {
	if($GLOBALS['config']['sms_login'] == "") { return "SMS-уведомления отключены. Настроить SMS-уведомления можно в разделе Конфигурация."; }
	$sterrtext = "SMS-уведомление не отправлено: ";
	$renderans = "";
	$sendaddress = 'http://gate.smsaero.ru/send?answer=json&user='.$GLOBALS['config']['sms_login'].'&password='.$GLOBALS['config']['sms_pw'].'&to=7'.$pn.'&text='.urlencode($msg).'&from='.$GLOBALS['config']['sms_name'].'&type='.$GLOBALS['config']['sms_channel'].'';
	$senddata = file_get_contents($sendaddress);
	if($senddata === false) { $renderans = "".$sterrtext."нет соединения с SMS-центром"; }
	else {
		$gotdata = json_decode($senddata, true);
		if($gotdata["result"] == "reject") {
			if($gotdata["reason"] == "incorrect user or password") { $renderans = "".$sterrtext."ошибка авторизации в SMS-шлюзе."; }
			elseif($gotdata["reason"] == "no credits") { $renderans = "".$sterrtext."недостаточно SMS на балансе. Пополните баланс и повторите отправку."; }
			elseif($gotdata["reason"] == "incorrect destination adress") { $renderans = "".$sterrtext."номер телефона задан неверно."; }
			else { $renderans = "".$sterrtext."ошибка неизвестна."; }
		}
		elseif($gotdata["result"] == "accepted") { $renderans = "sent"; }
		else { $renderans = "".$sterrtext."ошибка неизвестна."; }
	}
	return $renderans;
}

function render_doctype() {
?><!DOCTYPE html>
<!--[if IE 7]>                  <html class="ie7 no-js" lang="en">        <![endif]-->
<!--[if lte IE 8]>              <html class="ie8 no-js" lang="en">        <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="not-ie no-js" lang="en"> <!--<![endif]-->
<?php
}

function render_meta($pagename, $pagefile) {
	if(isset($pagefile)) { define("PAGEFILE", $pagefile); }
	echo'<meta charset="utf-8">
	<title>'.$pagename.'</title>
'; ?>	<link rel="shortcut icon" href="<?php echo $GLOBALS['config']['organization_favicon']; ?>">
	<meta name="author"			 content="Центр молодежных и студенческих программ (АНО ЦМСП), Владимир Данилов, Анна Ревазова, 2015г.">
	<meta name="description"	content="Карта активиста <?php echo htmlentities($GLOBALS['config']['organization_shortname'], ENT_QUOTES, "UTF-8"); ?>, Студенческая карта">
	<meta name="keywords"		 content="Карта активиста, Активисты, Центр молодежных и студенческих программ, <?php echo htmlentities($GLOBALS['config']['organization_shortname'], ENT_QUOTES, "UTF-8").", ".htmlentities($GLOBALS['config']['organization_form'], ENT_QUOTES, "UTF-8")." ".htmlentities($GLOBALS['config']['organization_fullname'], ENT_QUOTES, "UTF-8").", ".htmlentities($GLOBALS['config']['organization_department'], ENT_QUOTES, "UTF-8"); ?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0">
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->

	<!-- google fonts --> <link href='https://fonts.googleapis.com/css?family=PT+Sans:400,700|Roboto+Slab:400,700&subset=latin,cyrillic' rel='stylesheet' type='text/css'>
	<!-- google fonts --> <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,700&subset=latin,cyrillic,cyrillic-ext' rel='stylesheet' type='text/css'>
	<!-- bootstrap    --> <link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
	<!-- NAV          --> <link rel="stylesheet" href="css/slicknav.css?<?php echo filesversion; ?>" type="text/css"/>
	<!-- jquery       --> <script src="js/jquery.js"></script>
	<!-- NAV          --> <script type="text/javascript" src="js/jquery.slicknav.js"></script>
	<?php
	if(addslashes(basename($_SERVER['REQUEST_URI'])) == '') {
		if(LOGGEDIN == "NO") { ?>
	<!-- fancybox		 -->
		<script type="text/javascript" src="js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
		<link rel="stylesheet" href="js/fancybox/source/jquery.fancybox.css?v=2.1.4" type="text/css" media="screen" />
		<script type="text/javascript" src="js/fancybox/source/jquery.fancybox.pack.js?v=2.1.4"></script>
	<!-- /fancybox		-->
	<!-- common style --> <link href="css/style.css?<?php echo filesversion; ?>" rel="stylesheet" type="text/css" />
	<!-- js md5       --> <script src="js/md5.js"></script>
	<!-- recapture        <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->
	<!-- js index     --> <script src="js/index.js?<?php echo filesversion; ?>"></script>
		<?php } else { ?><!-- common style --> <link href="css/style.css?<?php echo filesversion; ?>" rel="stylesheet" type="text/css" />
	<!-- js md5       --> <script src="js/md5.js"></script>
	<!-- js interface --> <script src="js/interface.js?<?php echo filesversion; ?>"></script>
		<?php }
	} else { ?><!-- input mask	 --><script src="js/jquery.maskedinput.min.js"></script>
	<!-- fancybox		 -->
		<script type="text/javascript" src="js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
		<link rel="stylesheet" href="js/fancybox/source/jquery.fancybox.css?v=2.1.4" type="text/css" media="screen" />
		<script type="text/javascript" src="js/fancybox/source/jquery.fancybox.pack.js?v=2.1.4"></script>
	<!-- /fancybox		-->
	<!-- jquery ui    --> <link href="js/ui/jquery-ui.min.css" rel="stylesheet">
	<!-- jquery ui    --> <script src="js/ui/jquery-ui.min.js"></script>
	<!-- common style --> <link href="css/style.css?<?php echo filesversion; ?>" rel="stylesheet" type="text/css" />
	<!-- js interface --> <script src="js/interface.js?<?php echo filesversion; ?>"></script>
	<?php if(LOGGED_ACCESS == "a") { ?><!-- js interface --> <script src="js/func/pages_a.js?<?php echo filesversion; ?>"></script>
	<?php } else { ?><!-- js interface --> <script src="js/func/pages_k.js?<?php echo filesversion; ?>"></script>
	<?php } ?>
	<?php }
	?>

	<!--[if IE 8]>
		<style>
			header, .page { width:960px !important; }
			.row-fluid [class*="span"] { min-height:20px !important; }
		</style>
	<![endif]--><?php
}

function render_header() {
?>	<script>
		$(document).ready(function(){
			$('.menu').slicknav();
		});
	</script><div class="fillpage">
		<div class="loading">Пожалуйста, подождите...</div>
	</div>
	<div class="mw pd_window">
		<a class="closemw" href="javascript:closemw('pd_window');"><i class="icon-remove"></i></a>
		<h1>Политика конфиденциальности</h1>
		<p>Данный веб-ресурс предоставляет АНО "Центр молодежных и студенческих программ", который выступает как Оператор веб-ресурса.</p>
		<p>Посещая и используя данный веб-ресурс Вы соглашаетесь с <a href="https://studmol.ru/%D0%BF%D0%BE%D0%BB%D0%B8%D1%82%D0%B8%D0%BA%D0%B0-%D0%BA%D0%BE%D0%BD%D1%84%D0%B8%D0%B4%D0%B5%D0%BD%D1%86%D0%B8%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D1%81%D1%82%D0%B8/" target="_blank" style="text-decoration:underline;">Положением о персональных данных Оператора</a>, в частности с обработкой Ваших персональных данных, полученных на данном веб-ресурсе, в том числе указанных Вами, с целью Вашей идентификации, предоставления всех доступных возможностей веб-ресурса.</p>
		<p>Оператор предоставляет данный веб-ресурс в рамках своей Программы развития студенческого самоуправления России на основании договора между Оператором и образовательной организацией.</p>
	</div>
	<?php
	if( preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']) ) {
		echo '<div class="browserinfo">Ваш браузер устарел. Для стабильной работы системы обновите браузер до последней версии.<br />Рекомендуемые браузеры: Google Chrome, Safari.</div>';
	}
}

function render_footer() {
	?>	<footer>&copy; <?php echo $GLOBALS['config']['organization_form']." \"".$GLOBALS['config']['organization_shortname']."\""; ?> | Вы соглашаетесь с <a href="#" onclick='$("html, body").animate({ scrollTop: 0 }); $(".fillblack, .pd_window").fadeIn(); return false;'>Политикой конфиденциальности</a></footer>
	<script src="js/bootstrap.min.js"></script><?php
}

function menu() {
	?>
	<div style="margin:7px 0 8px 0">
		<div class="row-fluid head_logo">
			<div class="span2"><img src="<?php echo $GLOBALS['config']['organization_logo']; ?>" alt="" width="30px" /></div>
			<div class="span10"><div class="head_title">Карта активиста</div></div>
		</div>
	</div>
	<?php
	$mydep = mysql_query("SELECT `id`, `dep` FROM `users` WHERE `id`=".LOGGED_ID.";");
	$mydep = mysql_fetch_row($mydep);
	$addme_sql = "";
	if(LOGGED_ACCESS == "s") {
		$addme_sql = "SELECT `id`, `type`, `event`, `sid`, `role`, `complex`, `comment`, `executer`, `answer`, `status`, `story`,`see` from `addme` WHERE `status` = 'n';";
	} elseif(LOGGED_ACCESS == "t") {
		$addme_sql = "SELECT `id`, `type`, `event`, `sid`, `role`, `complex`, `comment`, `executer`, `answer`, `status`, `story`,`see` from `addme` WHERE `status` = 'n' AND `executer` = '".LOGGED_ID."';";
	} else {
		$addme_sql = "SELECT `id`, `type`, `event`, `sid`, `role`, `complex`, `comment`, `executer`, `answer`, `status`, `story`,`see` from `addme` WHERE `status` = 'n' AND (`executer` = '".LOGGED_ID."' OR `see` LIKE '%\"d".$mydep[1]."\"%' OR `see` LIKE '%\"u".LOGGED_ID."\"%');";
	}
	$addme_sql = mysql_query($addme_sql);
	$addme_sql = mysql_num_rows($addme_sql);
	$addme_render = '<li><a href="events">Мероприятия</a></li>';
	if($addme_sql > 0) {
		$addme_render = '<li><a href="events" class="box-badge"><span>Мероприятия<div>'.$addme_sql.'</div></span></a></li>';
	}
	if(LOGGED_ACCESS == "s") {
		?><ul class="menu">
			<li><a href="rating">Рейтинг</a></li>
			<li><a href="staff">Пользователи</a></li>
			<?php echo $addme_render; ?>
			<li><a href="groups">Группы</a></li>
			<li><a href="sz">Печать с/з</a></li>
			<li><a href="set">Настройки</a></li>
			<hr>
			<li><a href="config">Конфигурация</a></li>
		</ul><?php
	}
	elseif(LOGGED_ACCESS == "k") {
		?><ul class="menu">
			<li><a href="rating">Рейтинг</a></li>
			<?php echo $addme_render; ?>
			<li><a href="groups">Группы</a></li>
			<li><a href="sz">Печать с/з</a></li>
			<li><a href="set">Настройки</a></li>
		</ul><?php
	}
	elseif(LOGGED_ACCESS == "t") {
		?><ul class="menu">
			<li><a href="rating">Рейтинг</a></li>
			<?php echo $addme_render; ?>
			<li><a href="groups">Группы</a></li>
			<li><a href="set">Настройки</a></li>
		</ul><?php
	}
	elseif(LOGGED_ACCESS == "a") {
		?><ul class="menu">
			<li><a href="my">Моя активность</a></li>
			<li><a href="rating">Рейтинг</a></li>
			<li><a href="events">Мероприятия</a></li>
			<li><a href="groups">Группы</a></li>
			<li><a href="set">Настройки</a></li>
		</ul><?php
	}
	echo '<script>$(".menu a[href=\''.PAGEFILE.'\']").addClass("active");</script>';

	$prevkauth = mysql_query("SELECT `id`,`vkauth`,`vktoken`,`type` from `users` WHERE `id`='".LOGGED_ID."' AND `type` !='d' LIMIT 1");
	$vkauth = mysql_fetch_row($prevkauth);

	if($vkauth[1] == NULL and $GLOBALS['config']['vk_state'] == 1) {
	?>
	<a class="btnadd sociallink" href="#" onclick="javascript:void window.open('<?php vk_auth_link('vktoken'); ?>','vkauthwindow','width=656,height=377,toolbar=0,menubar=0,location=0,status=1,scrollbars=0,resizable=1'); return false;" style="margin:15px 0;">Привязать аккаунт</a>
	<?php
	}
}

function renderSocialHandle($socialResult) {
	echo '<script>function closeWithResult() {
		try {
			window.opener.authSocial("'.$socialResult.'");
		}
		catch (err) {}
		window.close();
		return false;
	}
	closeWithResult();</script>';
	exit;
}

// Подсчет только за текущий учебный год
function isThisAcademicYear($datestart) {
	$setyear = date("Y");
	if(date("m") == "01" or date("m") == "02" or date("m") == "03" or date("m") == "04" or date("m") == "05" or date("m") == "06" or date("m") == "07" or date("m") == "08") { $setyear = date("Y")-1; }
	if(strtotime($datestart)>=strtotime("".$setyear."-09-01") and strtotime($datestart)<strtotime("".($setyear+1)."-09-01")) {
		return true;
	} else {
		return false;
	}
}

function countStudentsRating($studentID) {
	$editcount = 0;
	$sqlcountByTags = array();
	$pregetByATags = mysql_query("SELECT `id`,`type` from `tags` WHERE `type` = 'a';");
	while($getByATags = mysql_fetch_array($pregetByATags)) {
		$sqlcountByTags["".$getByATags[0].""] = 0;
	}
	$pregetactive = mysql_query("SELECT `id`,`user`,`event`,`role`,`complex` from `activity` WHERE `user`='".$studentID."'");
	if(mysql_num_rows($pregetactive) > 0) {
		while($getactive = mysql_fetch_array($pregetactive)) {
			$precheckeid = mysql_query("SELECT `id`,`date`,`tags` from `events` WHERE `id`='".$getactive[2]."' LIMIT 1");
			$checkeid = mysql_fetch_row($precheckeid);
			if(isThisAcademicYear($checkeid[1]) == true) {
				$currentActivityPoints = activityPoints($getactive[2],$getactive[3],$getactive[4]);
				$editcount = $editcount + $currentActivityPoints;

				$sqlcountByTagsText = "";
				$countByTags = json_decode($checkeid[2]);
				foreach($countByTags as $countByTags_id => $countByTags_value) {
					if(array_key_exists($countByTags_value, $sqlcountByTags)) {
						$sqlcountByTags["".$countByTags_value.""] += $currentActivityPoints;
					}
				}
			}
		}
	}

	$sqlcountByTagsText = "";
	foreach($sqlcountByTags as $sqlcountByTags_id => $sqlcountByTags_value) {
		$sqlcountByTagsText .= ", `ic_".$sqlcountByTags_id."` = ".$sqlcountByTags_value."";
	}

	$recounts = "UPDATE  `".$GLOBALS['config_db']['mysql_db']."`.`users` SET `count` = ".$editcount."".$sqlcountByTagsText." WHERE  `users`.`id` =".$studentID.";";
	if(!mysql_query($recounts)) { errorjson("Ошибка базы данных. Повторите попытку позже.".mysql_error()); }
}

function config_empty() {
	$GLOBALS['config'] = array();
	config_save();
}

function config_save() {
	if(!mysql_query("TRUNCATE TABLE `config`;")) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	foreach($GLOBALS['config'] as $key => $value) {
		$config_check = mysql_query("SELECT `key`,`value` from `config` WHERE `key`='{$key}' LIMIT 1;");
		$config_check = mysql_fetch_row($config_check);
		if($config_check[0]) {
			$sql = "UPDATE `".$GLOBALS['config_db']['mysql_db']."`.`config` SET `value` = '{$value}' WHERE `config`.`key` ='{$key}';";
		} else {
			$sql = "INSERT INTO `".$GLOBALS['config_db']['mysql_db']."`.`config` (`key`, `value`) VALUES ('{$key}', '{$value}');";
		}
		if(!mysql_query($sql)) { errorjson("Ошибка базы данных. Повторите попытку позже."); }
	}
}

function vk_auth_link($callack_page) {
	echo 'https://oauth.vk.com/authorize?client_id='.$GLOBALS['config']['vk_id'].'&scope=offline&redirect_uri='.urlencode(PROTOCOL.$_SERVER['SERVER_NAME'].'/'.$callack_page).'&response_type=code&v=5.32&state=1';
}

function addme_alerts() {
	$addme_sql = "SELECT `id`, `event`, `sid`, `executer`, `answer`, `status`, `story` from `addme` WHERE `status` != 'n' AND `sid` = '".LOGGED_ID."' ";
	$addme_sql = mysql_query($addme_sql);
	$addme_array = Array();
	while($addme = mysql_fetch_array($addme_sql)) {
		$event = "SELECT `id`, `name` from `events` WHERE `id` = '".$addme[1]."' LIMIT 1;";
		$event = mysql_query($event);
		$event = mysql_fetch_row($event);
		$newpname = "";
		$user = mysql_query("SELECT `id`,`sname`,`fname`,`pname` from `users` WHERE `id` ='".$addme[3]."' LIMIT 1;");
		$user = mysql_fetch_row($user);
		if(mb_strlen($user[3], "UTF-8")<2) { $name = mb_substr($user[2], 0, 1, "UTF-8").".".$user[1]; }
		else { $name = mb_substr($user[2], 0, 1, "UTF-8").".".mb_substr($user[3], 0, 1, "UTF-8").".".$user[1]; }
		$addme_array_new = array(
			"event_id" => $addme[1],
			"event_name" => $event[1],
			"status" => $addme[5],
			"holder" => $name
		);
		if($addme[5] == "c" or $addme[5] == "h") {
			//$addme_array_new["answer"] = $addme[4];
			$story = json_decode($addme[6], true);
			$addme_array_new["time"] = $story[(count($story)-1)]["time"];
		} else {
			$activity = mysql_query("SELECT `id`,`user`,`event`,`created` from `activity` WHERE `user` ='".LOGGED_ID."' AND `event` ='".$addme[1]."' LIMIT 1;");
			$activity = mysql_fetch_row($activity);
			$time = explode(' ', $activity[3]);
			$date = explode('-', $time[0]);
			$addme_array_new["time"] = $date[2].".".$date[1].".".$date[0]." ".$time[1];
		}
		if($addme[5] == "c") {
			$addme_array_new["answer"] = $addme[4];
		}
		$addme_array[] = $addme_array_new;
	}
	return $addme_array;
}

// Маршрутизатор
$__page = $_SERVER['REQUEST_URI'];
$__page = parse_url($__page);
$__page = substr($__page["path"], 1);
if($__page == "") { $__page = "index"; }
if(0 === strpos($__page, 'events-')) { $__page = "event"; }
if(0 === strpos($__page, 'groups-')) { $__page = "group"; }

if( !file_exists("../pages/".$__page.".php") )
{
	header("Location: 404");
	exit();
}
require_once("../pages/".$__page.".php");
?>
