<?php accesspage(); render_doctype(); ?>
<head>
	<?php render_meta("Рейтинг","rating"); ?>

	<style>
		@media screen and (max-width: 768px) { .owncourse, .hideit, .curdep { display:none; } }
	</style>

	<script type="text/javascript">
	var curdate = "<?php echo date("d.m.Y"); ?>";
	var global_access, globalsid, global_sinid, global_surname, global_firstname, global_patronumic, global_sex, global_birthday, global_phone, global_facid, global_groupid, global_course, global_groupnum, global_level, global_budget, global_created, curpage;
	var cursearch = "";
	var cursearchdep = "";
	var cursearchcourse = "";
	var cursearchtagA = "";
	var getstudby = "i";
	var isoncheck = "n";
	var goonit = "yes";
	var icons_array = [];
	var period_since = "";
	var period_for = "";

	$(function () {
		init_rating();
	});
	</script>
</head>

<body>
<div class="fillblack"></div>
<?php if(LOGGED_ACCESS == "s" or LOGGED_ACCESS == "k") { ?>
<div class="mw addwindow">
	<a class="closemw" href="javascript:closemw('addwindow'); if($('#savebtn').is(':visible')) { student(globalsid); }"><i class="icon-remove"></i></a>
	<h1>Регистрация студента</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">ID:</label>
			<div class="controls">
				<input id="add_id" class="span12" type="text" />
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
				<select id="add_sex" class="span12" onChange="$('#add_birthday').focus();"><option value=""></option><option value="m">мужской</option><option value="f">женский</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Дата рождения:</label>
			<div class="controls">
				<input id="add_birthday" class="span12" type="text" placeholder="00.00.0000" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Номер телефона:</label>
			<div class="controls">
				<input id="add_phone" class="span12" type="text" placeholder="(9xx)xxx-xx-xx" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Факультет:</label>
			<div class="controls">
				<select id="add_faculty" class="span12" onChange="loadgroups()">
					<option value="" selected></option>
					<?php
			$loadfac = mysql_query("SELECT `id`,`type`,`name` from `deps` WHERE `type` = 'i'");
			while($faculties = mysql_fetch_array($loadfac)) {
				echo '<option value="'.$faculties[0].'">'.$faculties[2].'</option>';
			}
			?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Группа:</label>
			<div class="controls">
				<select id="add_group" class="span12" onChange="$('#add_course').focus();"><option value="" selected></option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Курс:</label>
			<div class="controls">
				<select id="add_course" class="span12" onChange="$('#add_gen').focus();"><option value=""></option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Программа:</label>
			<div class="controls">
				<select id="add_level" class="span12"><option value=""></option><option value="c">СПО</option><option value="b">бакалавриат</option><option value="m">магистратура</option><option value="s">специалитет</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Бюджетная основа:</label>
			<div class="controls">
				<select id="add_budget" class="span12"><option value=""></option><option value="y">да</option><option value="n">нет</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Номер группы:</label>
			<div class="controls">
				<input id="add_groupnum" class="span12" type="text" value="" />
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<a id="regbtn" class="btn1" href="javascript:oncheck()">Регистрация</a>
				<a id="savebtn" class="btn1" href="javascript:onsave()">Сохранить</a>
			</div>
		</div>
	</div>
</div>
<div class="mw changebook">
	<a class="closemw" href="javascript:closemw('changebook'); student(globalsid);"><i class="icon-remove"></i></a>
	<h1>Смена карты</h1>
	<div class="alert alert-error changebook_info"><span></span></div>
	<div class="row-fluid">
		<div class="span12">Владелец: <b class="changebook_holder"></b></div>
	</div>
	<div class="row-fluid">
		<div class="span12"><input id="newbookcode" class="span12 center" type="text" placeholder="Сканируйте код новой карты" /></div>
	</div>
</div>
<?php
}
?>

<?php if( LOGGED_ACCESS !== "a" ) { ?>
<div class="mw window_ratingperiod" style="max-width:500px;">
	<a class="closemw" href="javascript:closemw('window_ratingperiod')"><i class="icon-remove"></i></a>
	<h1>Выгрузка за период</h1>
	<div class="row-fluid">
		<div class="span12">
			<div class="alert alert-success"><strong>Внимание!</strong> После применения периода будет сформирован рейтинг исходя из всех начисленных баллов за указанный период, включая мероприятия, не входящие в текущий учебный год. Без указания периода в таблице рейтинга отображаются баллы, начисленные только за мероприятия текущего учебного года (с 1 сентября по 31 августа).</div>
			<div class="alert alert-warning">Загрузка списка при указании периода происходит дольше обычного.</div>
			Период:
			<div class="control-group">
				<div class="controls">
					<input class="span6 search_since" type="text" placeholder="00.00.0000 начало" />
				</div>
				<div class="controls">
					<input class="span6 search_for" type="text" placeholder="00.00.0000 конец" />
				</div>
			</div>
			<hr />
			<div style="margin-bottom:10px;"><a class="btn1" href="" onclick="rating_period(); return false;">Выгрузить</a></div>
		</div>
	</div>
</div>
<?php } ?>

<div class="hiddenbox" style="display:none;">
	<div class="box_student" style="position:relative; width:600px;">

		<h1 style="width:665px;">Карта активиста</h1>
		<?php if(LOGGED_ACCESS == "s") { ?>
		<div class="editblock">
			<div class="btn-group">
				<a class="btn1 dropdown-toggle" data-toggle="dropdown" href="#">
					Выберите действие
					<span class="caret" style="margin-top:8px;"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="" onclick="editwindow_rating(); return false;">Редактировать</a></li>
					<li><a href="" onclick="changebook(); return false;">Сменить карту</a></li>
					<li><a href="" onclick="returnaccess(); return false;">Восстановить доступ</a></li>
				</ul>
			</div>
		</div>
		<?php
		}
		elseif(LOGGED_ACCESS == "k") { ?>
		<div class="editblock">
			<div class="btn-group">
				<a class="btn1 dropdown-toggle" data-toggle="dropdown" href="#">
					Выберите действие
					<span class="caret" style="margin-top:8px;"></span>
				</a>
				<ul class="dropdown-menu">
					<li><a href="" onclick="editwindow_rating(); return false;">Редактировать</a></li>
					<li><a href="" onclick="returnaccess(); return false;">Восстановить доступ</a></li>
					<!--<li><a href="" onclick="reportwindow(); return false;">Выгрузить</a></li>-->
				</ul>
			</div>
		</div>
		<?php
		}
	?>
		<div class="greybox"><div class="event_info_inner" style="margin-bottom:10px;">
			<p class="box_student_info"></p>
		</div></div>
		<div class="box_student_lists" style="margin-bottom:10px;"></div>
		<table class="table_withhead table_forshort owntable">
		</table>
		<div class="box_student_add"></div>
	</div>
</div>
	<?php render_header(); ?>
	<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
				<div class="row-fluid">
					<div class="span3">
						<?php menu();
			if(LOGGED_ACCESS == "a") { ?>
						<div class="undermenu">
							<div class="points">Баллов: <b></b></div>
							<div class="rank">Рейтинг: <b></b></div>
						</div>
						<?php
			}
			 ?>
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span6"><h1>Рейтинг студентов <a class="filter" href=''><i class="icon-search"></i></a></h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>

						<div class="row-fluid">
							<div class="row-fluid filter">
								<div class="span3">
									<select class="span12 searchtagA" onchange="findstudents();">
										<option value="">Деятельность</option>
										<?php
										 $_pregetByATags = mysql_query("SELECT `id`,`type`,`name` from `tags` WHERE `type` = 'a';");
										 while($_getByATags = mysql_fetch_array($_pregetByATags)) {
											 echo '<option value="'.$_getByATags[0].'">'.$_getByATags[2].'</option>
											 ';
										 }
					?>
									</select>
								</div>
								<div class="span2">
									<select class="span12 searchdep" onchange="findstudents();">
										<option value="">Факультет</option>
										<?php
					 $pregetdeps = mysql_query("SELECT `id`,`type`,`name` FROM `deps` WHERE `type` = 'i'");
					 while($getdeps = mysql_fetch_array($pregetdeps)) {
						 echo '<option value="'.$getdeps[0].'">'.$getdeps[2].'</option>
						 ';
					 }
					?>
									</select>
								</div>
								<div class="span2">
									<select class="span12 searchcourse" onchange="findstudents();">
										<option value="">Курс</option>
										<option value="c1">1 СПО</option>
										<option value="c2">2 СПО</option>
										<option value="c3">3 СПО</option>
										<option value="1">1</option>
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="m1">1 маг.</option>
										<option value="m2">2 маг.</option>
									</select>
								</div>
								<?php
									if( LOGGED_ACCESS !== "a" )
									{ ?>
										<div class="span1"><a class="btn1 btnadd btn-period" href="" onclick="rating_period_window(); return false;"></a></div>
										<div class="span4"><input class="span12 searchinput" type="text" placeholder="Поиск по ФИО..." /></div>
									<?php } else { ?>
											<div class="span5"><input class="span12 searchinput" type="text" placeholder="Поиск по ФИО..." /></div>
									<?php } ?>
							</div>
							<div class="rating_info"></div>
							<table class="table_withhead table_normalrow ratingtable">
							</table>
							<div class="lowerbox">
								<div class="center textalert">По данному запросу студенты не найдены</div>
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
