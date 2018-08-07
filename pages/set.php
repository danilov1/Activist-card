<?php accesspage(); render_doctype(); ?>
<head>
	<?php render_meta("Настройки","set"); ?>
	
	<!-- js md5       --> <script src="js/md5.js"></script>
	
	<script type="text/javascript">
	$(document).ready(function () {
		$("#pw_old").keyup(function(e) { if(e.keyCode == 13) { $("#pw_new").focus(); } });
		$("#pw_new").keyup(function(e) { if(e.keyCode == 13) { $("#pw_newrepeat").focus(); } });
		$("#pw_newrepeat").keyup(function(e) { if(e.keyCode == 13) { changepw(); } });
		var setFocus = setInterval(function() { var elem = $("#pw_old"); if(elem.is(":focus")) { clearTimeout(setFocus); } else { elem.focus(); } }, 500);
	});
	</script>
</head>

<body class="setpage">
	<?php render_header(); ?>
	<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
				<div class="row-fluid">
					<div class="span3">
						<?php menu("staff"); ?>
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span6"><h1>Настройки</h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="clear"></div>
						<div class="row-fluid">
							<div class="alert alert-success">В данном разделе Вы можете сменить свой пароль на новый. Пароль может содержать <strong>латинские буквы и цифры (6-30 символов)</strong>.</div>
							<!-- СОДЕРЖАНИЕ ОРГАНИЗАЦИИ <div class="alert"></div>-->
							<div class="greybox"><div class="event_info_inner span12" style="margin-bottom:10px;">
								<div class="row-fluid form-horizontal">
									<div class="control-group">
										<label class="control-label">Старый пароль:</label>
										<div class="controls">
											<input id="pw_old" class="span5" type="password" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Новый пароль:</label>
										<div class="controls">
											<input id="pw_new" class="span5" type="password" />
										</div>
									</div>
									<div class="control-group">
										<label class="control-label">Повтор нового пароля:</label>
										<div class="controls">
											<input id="pw_newrepeat" class="span5" type="password" />
										</div>
									</div>
									<div class="control-group">
										<div class="controls">
											<a class="btn1" id="changepw" href="" onclick="changepw(); return false;">Сменить пароль</a>
										</div>
									</div>
								</div>
							</div></div>
							<?php
							$prevkauth = mysql_query("SELECT `id`,`vkauth`,`vktoken`,`type` from `users` WHERE `id`='".LOGGED_ID."' AND `type` !='d' LIMIT 1");
							$vkauth = mysql_fetch_row($prevkauth);
							
							if($vkauth[1] !== NULL and $GLOBALS['config']['vk_state'] == 1) { ?>
							<hr />
							<div class="row-fluid btnunlinksocial">
								<div class="span12">
									<script>
									function unlinksocial() {
										render_massage("Отвязать аккаунт ВКонтакте?","<div class='render_massage'>После отвязки аккаунта ВКонтакте Вы сможете войти в профиль системы Карта активиста только используя логин и пароль.</div><div class='render_massage_buttons'><a class='btn1' href='' onclick='unlinksocialYES(); $.fancybox.close(); return false;' style='background:#f36b69;'>Отвязать</a> <a class='btn1' href='' onclick='$.fancybox.close(); return false;'>Отмена</a></div>");
									}
									function unlinksocialYES() {
										$.ajax({
										  url:"operator2",
										  method:"POST",
										  data:{ act: "vkunlink" }
										})
										.done(function(answer) {
										  var data = JSON.parse(answer);
										  if(data.error == "ok") {
											$.fancybox({
													closeEffect : 'none',
													'afterClose':function () {
													window.location = "index";
												},
												'content' : m_ok('Аккаунт ВКонтакте успешно отвязан!')
											});
										  } else {
											  $.fancybox({ 'content' : data.error });
										  }
										  $('.fillpage').fadeOut(100);
										});
									}
									</script>
									<a style="background:#f36b69;" class="btn1 btnadd" href="" onclick="unlinksocial(); return false;"><i class="icon-remove icon-white"></i> Отвязать аккаунт ВКонтакте</a>
								</div>
							</div>
							<hr />
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>
</body>
</html>
	