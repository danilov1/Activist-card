<?php
if(LOGGEDIN == "YES") {
	$precpwl = mysql_query("SELECT `id`,`password` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$cpwl = mysql_fetch_row($precpwl);
	if(mb_strlen($cpwl[1],"UTF-8") !== 5) {
		if(LOGGED_ACCESS == "a") { header("Location: /my"); exit; }
		else { header("Location: /rating"); exit; }
	}
}
render_doctype();
?>
<head>
	<?php render_meta("КАРТА АКТИВИСТА"); ?>
	<?php
	if(LOGGEDIN == "NO") {
	?>

	<script type="text/javascript">
	image2 = new Image();
	image2.src = 'img/loading.gif';
	$(function() {
		$(".loginauth_info, .header_mini, .header_slide").hide();
		$(".loadgif").fadeOut(500);
		$("header").delay(500).fadeTo(700,1);
		$(".page").delay(600).fadeTo(500,1);
		$(".loginauth_submit").click(function(e) { /* grecaptcha.execute();  e.preventDefault(); */ login(null); return false; });
		$("input[name=loginauth_login]").keyup(function(e) { if(e.keyCode == 13) { $( "input[name=loginauth_pw]" ).focus(); } });
		$("input[name=loginauth_pw]").keyup(function(e) { if(e.keyCode == 13) { /* grecaptcha.execute(); */ login(null); return false; } });
		var setFocus = setInterval(function() {
			var elem = $("input[name=loginauth_login]");
			if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); }
		}, 500);
	});
	</script>
	<?php
	} else {
	?>

	<script type="text/javascript">
	$(function() {
		$(".setpw_info, .header_mini, .header_slide").hide();
		$(".page, header, .indexnav").delay(500).fadeTo(600,1);
		$(".setpw_submit").click(function(e) { setpw(); e.preventDefault(); });
		$("input[name=setpw_pw1]").keyup(function(e) { if(e.keyCode == 13) { $( "input[name=setpw_pw2]" ).focus(); } });
		$("input[name=setpw_pw2]").keyup(function(e) { if(e.keyCode == 13) { setpw(); } });
		$("input[name=setpw_pw1]").focus();
		var setFocus = setInterval(function() {
			var elem = $("input[name=setpw_pw1]");
			if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); }
		}, 500);
	});
	</script>
	<?php
	}
?>
</head>

<body class="page_index">
	<?php if(LOGGEDIN == "NO") { echo '<div class="loadgif"></div>'; } ?>
	<div class="slicknav_menu indexnav"><div style="margin-top:5px; font-weight:bold; text-align:center; color:#fff;"><?php if((LOGGEDIN == "NO") or (pwshort == "yes")) { echo 'КАРТА АКТИВИСТА'; } ?></div></div>
	<?php render_header(); ?>
	<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
			<?php
		if(LOGGEDIN == "NO") {
		?>
				<!-- ИНФОРМАЦИЯ ОРГАНИЗАЦИИ <div class="alert alert-success alertwidth"><span></span></div> -->
				<div class="row-fluid preloginbox">
					<div class="span4"></div>
					<div class="span4">
						<form name="loginauth_form" class="centerform" method="post" autocomplete="off">
							<div>
								<div style="position:relative; margin:0 0 10px 0; text-align:center;">
									<div><img src="<?php echo $GLOBALS['config']['organization_logo']; ?>" alt="" width="40px" style="" /></div>
									<div class="head_title">Карта активиста</div>
									<div class="loadover"><div></div></div>
								</div>
							</div>
							<div class="alert loginauth_info">
								<button type="button" class="close">&times;</button>
								<span></span>
							</div>
							<input style="display:none">
							<input type="password" style="display:none">
							<div class="row-fluid"><div class="span12"><input name="loginauth_login" class="span12" type="text" placeholder="Логин" /></div></div>
							<input name="loginauth_pw" class="span12" type="password" placeholder="Пароль" />
							<div class="center">
								<?php if($GLOBALS['config']['vk_id'] == "") { ?>
								<div style="float:left; width:100%;"><a class="btn1 loginauth_submit g-recaptcha" href="" data-sitekey="6Le0pmIUAAAAAAbD01zfKyxEfisr9UBOEzuI-8kp" data-callback="login" data-size="invisible">Вход</a></div>
								<?php
								} else {
								?>
								<div style="float:left; width:84%;"><a class="btn1 loginauth_submit g-recaptcha" href="" data-sitekey="6Le0pmIUAAAAAAbD01zfKyxEfisr9UBOEzuI-8kp" data-callback="login" data-size="invisible">Вход</a></div>
								<div style="float:right; width:15%;"><a class="btn1 loginvk" href="#" onclick="javascript:void window.open('<?php vk_auth_link('vkauth'); ?>','vkauthwindow','width=656,height=377,toolbar=0,menubar=0,location=0,status=1,scrollbars=0,resizable=1'); return false;">В</a></div>
								<?php
								}
								?>
						</form>
					</div>
					<div class="span4"></div>
				</div>
			<?php
		}
		else {
		?><div class="slicknav_menu"></div>
				<div class="row-fluid">
				<div class="alert alert-success">Вы успешно вошли в систему. Для продолжения придумайте себе новый пароль.<br />Пароль может содержать <strong>латинские буквы и цифры (6-30 символов)</strong>.</strong></div>
					<div class="span4"></div>
					<div class="span4">
						<form name="setpw_form" class="centerform centerform_add greybg" method="post" onsubmit="setpw()" autocomplete="off">
							<div class="alert setpw_info">
								<button type="button" class="close">&times;</button>
								<span></span>
							</div>
							<?php
								if(LOGGED_ACCESS !== "a") {
									$preuseragree = mysql_query("SELECT `id`,`fullname`,`phone` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
									$useragree = mysql_fetch_row($preuseragree);
									echo '<div class="inforow" style="margin-bottom:15px;"><div><b>ФИО:</b> <span>'.$useragree[1].'</span></div><div><b>Тел.:</b> <span>'.$useragree[2].'</span></div></div>';
								}
							?>
							<input name="setpw_pw1" class="span12" type="password" placeholder="Новый пароль" />
							<input name="setpw_pw2" class="span12" type="password" placeholder="Повтор нового пароля" />
							<?php
								if(LOGGED_ACCESS !== "a") { ?>
							<div class='alert-small'>Нажимая на кнопку "Продолжить", Вы становитесь пользователем АИС "Карта активиста", подтверждаете идентификацию Вас по указанным данным и даете согласие на их обработку в рамках <b><a href="https://studmol.ru/%D0%BF%D0%BE%D0%BB%D0%B8%D1%82%D0%B8%D0%BA%D0%B0-%D0%BA%D0%BE%D0%BD%D1%84%D0%B8%D0%B4%D0%B5%D0%BD%D1%86%D0%B8%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D1%81%D1%82%D0%B8/" target="_blank">политики конфиденциальности</a></b> координатора системы.</div>
								<?php }
							?>
							<div class="center"><a class="btn1 setpw_submit" href="#">Продолжить &gt;</a></div>
						</form>
					</div>
					<div class="span4"></div>
				</div>
			<?php
		}
		?>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>

</body>
</html>
