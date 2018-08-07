<?php accesspage(); accessto("s"); render_doctype(); ?>
<head>
	<?php render_meta("Пользователи","staff"); ?>
	<!-- js interface --> <script src="js/func/staff.js?1_1"></script>
</head>

<body>
<div class="fillblack"></div>
<div class="mw addwindow">
	<a class="closemw" href="javascript:closemw('addwindow'); if($('#savebtn').is(':visible')) { user(globaluid); }"><i class="icon-remove"></i></a>
	<h1>Регистрация пользователя</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">Тип доступа:</label>
			<div class="controls">
				<select id="add_type" class="span12" onChange="$('#add_surname').focus();"><option value=""></option><option value="s">Администратор (полный доступ)</option><option value="k">Специалист (вносит данные)</option><option value="t">Преподаватель (просмотр данных)</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Фамилия:</label>
			<div class="controls">
				<input id="add_surname" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Имя:</label>
			<div class="controls">
				<input id="add_firstname" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Отчество:</label>
			<div class="controls">
				<input id="add_patronymic" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Пол:</label>
			<div class="controls">
				<select id="add_sex" class="span12" onChange="$('#add_phone').focus();"><option value=""></option><option value="m">мужской</option><option value="f">женский</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Номер телефона:</label>
			<div class="controls">
				<input id="add_phone" class="span12" type="text" placeholder="(9xx)xxx-xx-xx" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Подразделение:</label>
			<div class="controls">
				<select id="add_dep" class="span12" onChange="$('#add_post').focus();">
					<option value="" selected></option>
					<?php
			$loaddep = mysql_query("SELECT `id`,`type`,`name` from `deps` WHERE `type` = 'd'");
			while($department = mysql_fetch_array($loaddep)) {
				echo '<option value="'.$department[0].'">'.$department[2].'</option>';
			}
			?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Должность:</label>
			<div class="controls">
				<input id="add_post" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<a id="regbtn" class="btn1" href="" onclick="reguser(); return false;">Регистрация</a>
				<a id="savebtn" class="btn1" href="" onclick="onsave(); return false;">Сохранить</a>
			</div>
		</div>
	</div>
</div>
<div class="hiddenbox" style="display:none;">
	<div class="box_user" style="position:relative; width:600px;">
		<h1 style="width:665px;">Справка пользователя</h1>
		<div class="editblock">
			<div class="btn-group">
				<a class="btn1 dropdown-toggle" data-toggle="dropdown" href="#">
					Выберите действие
					<span class="caret" style="margin-top:8px;"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="" onclick="editwindow(); return false;">Редактировать</a></li>
					<li><a href="" onclick="returnaccess(); return false;">Восстановить доступ</a></li>
					<li><a href="" onclick="deluser(); return false;">Удалить</a></li>
				</ul>
			</div>
		</div>
		<p class="box_user_info"></p>
		<table class="table_withhead table_forshort owntable">
		</table>
		<div class="box_user_add"></div>
	</div>
</div>
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
								<div class="span6"><h1>Пользователи системы</h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>

						<div class="row-fluid">
							<div class="row-fluid">
								<div class="span10"><input class="span12 searchinput" type="text" placeholder="Фамилия, имя, отчество" /></div>
								<div class="span2"><a class="btn1 btnadd" href="" onclick="addwindow(); return false;">Добавить</a></div>
							</div>
							<table class="table_withhead table_normalrow userstable">
							</table>
							<div class="lowerbox">
								<div class="center textalert">По данному запросу пользователи не найдены</div>
								<div class="pager"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>
</body>
</html>
	