<?php accesspage(); render_doctype(); ?><head>
	<?php render_meta("Мероприятия","events"); ?>
	
	<style>
		.ui-datepicker { font-size:90%; }
	</style>
	<script type="text/javascript">
	var newholder =  "";
	var fixers = [];
	var global_eid, global_name, global_place, global_date_since, global_date_for, global_time_since, global_time_for, global_level, global_holderid, global_fixers, global_holdername, global_dep, global_comment, global_outside, global_complex, global_tags;
	var cursearch = "";
	var goonit = "yes";
	
	$(document).ready(function () {
		init_events();
	});
	</script>
</head>

<body>
<div class="fillblack"></div>
<?php if(LOGGED_ACCESS !== "a") { ?>
<div class="mw addwindow">
	<a class="closemw" href="javascript:closemw('addwindow');"><i class="icon-remove"></i></a>
	<h1>Добавление мероприятия</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">Название:</label>
			<div class="controls">
				<input id="add_name" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Уровень:</label>
			<div class="controls">
				<select id="add_level" class="span12" onChange="$('#add_place').focus();">
					<option value=""></option>
					<option value="f">факультетский</option>
					<option value="u">университетский</option>
					<option value="c">городской</option>
					<option value="r">региональный</option>
					<option value="v">всероссийский</option>
					<option value="i">международный</option>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Место:</label>
			<div class="controls">
				<input id="add_place" class="span12" type="text" placeholder="необязательно" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Дата начала:</label>
			<div class="controls">
				<input id="add_date_since" class="span12" type="text" placeholder="00.00.0000" />
			</div>
			<div class="controls">
				<label class="checkbox">
					<input id="add_date_if" type="checkbox" onChange="changedate()">Дата окончания совпадает 
					<input id="add_date_for" class="span12" type="text" placeholder="00.00.0000" />
				</label>
			</div>
			<div class="controls">
				Время <i>(необязательно)</i>:<br>с: <input id="add_time_since" class="span3" type="text" placeholder="00:00" /> по: <input id="add_time_for" class="span3" type="text" placeholder="00:00" />
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Координатор:</label>
			<div class="controls">
				<label class="checkbox">
					<input id="add_dep_if" type="checkbox" onChange="changedep()"> Координирует подразделение вуза
				</label>
				<select id="add_dep" class="span12" onChange="">
					<option value="" selected></option>
					<?php
					for($i=0; $i<count($GLOBALS['config']['organizations_order']); $i++) {
						$loaddep = mysql_query("SELECT `id`,`type`,`name` from `deps` WHERE `id` = '".$GLOBALS['config']['organizations_order'][$i]."'");
						$department = mysql_fetch_row($loaddep);
						echo '<option value="'.$department[0].'">'.$department[2].'</option>';
					}
					?>
				</select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Ответственный:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span><b>Ответственный</b> - пользователь системы (не из рейтинга), ответственный за все вносимые данные о мероприятии.</span>
				</div>
				<input id="add_holder" class="span12" type="text" placeholder="Поиск по ФИО (среди пользователей)..." />
				<div id="add_holderend"></div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Внешний организатор:</label>
			<div class="controls">
				<label class="checkbox">
					<input id="add_outside" type="checkbox" onChange=""> Если организатор НЕ относится к Вашей образовательной организации
				</label>
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Деятельность:</label>
			<div class="controls">
				<div class="row-fluid">
					<div class="span6">
					<?php
					$getAtags = mysql_query("SELECT `id`,`type`,`name` from `tags` WHERE `type`='a'");
					$numAtags = mysql_num_rows($getAtags);
					$whenNewDiv = round(($numAtags/2), 0, PHP_ROUND_HALF_UP);
					?>
					<?php
					$countTags = 0;
					while($Atag = mysql_fetch_array($getAtags)) {
						if($countTags == $whenNewDiv) {
							echo '</div>
							<div class="span6">
							';
						}
						echo '<label class="checkbox">
								<input class="add_tagA" tagID="'.$Atag[0].'" type="checkbox" onChange=""> '.$Atag[2].'
							</label>';
						$countTags++;
					}
					?>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Тип мероприятия:</label>
			<div class="controls">
				<div class="row-fluid">
					<div class="span6">
					<?php
					$getEtags = mysql_query("SELECT `id`,`type`,`name`,`style` from `tags` WHERE `type`='e'");
					$numEtags = mysql_num_rows($getEtags);
					$whenNewDiv = round(($numEtags/2), 0, PHP_ROUND_HALF_UP);
					?>
					<?php
					$countTags = 0;
					while($Etag = mysql_fetch_array($getEtags)) {
						if($countTags == $whenNewDiv) {
							echo '</div>
							<div class="span6">
							';
						}
						echo '<label class="checkbox" style="color:'.$Etag[3].';">
								<input class="add_tagE" tagID="'.$Etag[0].'" type="checkbox" onChange=""> '.$Etag[2].'
							</label>';
						$countTags++;
					}
					?>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Сложность мероприятия:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span><b>"Тяжелое" мероприятие</b> - нечастое, требующее длительной подготовки мероприятие или требующее большой ответственности на протяжении длительного времени</span>
				</div>
				<label class="checkbox">
					<input id="add_complex" type="checkbox" onChange=""> "Тяжелое" мероприятие
				</label>
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Доп. фиксаторы <i>(необязательно)</i>:</label>
			<div class="controls">
				<div class="alert" style="font-size:12px; line-height:12px;">
					<span><b>Доп.фиксаторы</b> - пользователи системы Карты активиста (не студенты), которые также смогут добавлять вовлеченных в создаваемое мероприятие. По умолчанию фиксаторами являетесь Вы, а также указанный ответственный и все сотрудники указанного подразделения вуза</span>
				</div>
				<input id="add_fixers" class="span12" type="text" placeholder="Поиск по ФИО (среди пользователей)..." />
				<div id="add_fixersend"></div>
			</div>
		</div>
		<hr>
		<div class="control-group">
			<label class="control-label">Комментарий:</label>
			<div class="controls">
				<textarea id="add_comment" class="span12" placeholder="необязательно" style="resize:none;"></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<a id="regbtn" class="btn1" href="" onclick="addevent(); return false;">Добавить</a>
				<a id="savebtn" class="btn1" href="" onclick="saveevent(); return false;">Сохранить</a>
				<a id="delbtn" class="btn1" href="" onclick="delevent(); return false;" style="background:#f36b69;">Удалить</a>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
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
			 		<div class="event_tags">
							<div class="event_tagsA">
						<?php
						$getAtags = mysql_query("SELECT `id`,`type`,`name`,`style` from `tags` WHERE `type`='a'");
						while($Atag = mysql_fetch_array($getAtags)) {
							echo '<a class="tag" tagID="'.$Atag[0].'" tagsearch="n" href="#">
									<div>
										<i><img src="content/tags/'.$Atag[3].'" /></i>
										<span>'.$Atag[2].'</span>
										<b></b>
									</div>
								</a>';
						}
						?>
							</div>
							<div class="event_tagsE">
								<div class="show_tagsE">
						<?php
						$getEtags = mysql_query("SELECT `id`,`type`,`name`,`style` from `tags` WHERE `type`='e'");
						while($Etag = mysql_fetch_array($getEtags)) {
							echo '<a class="tag tagPoint" tagID="'.$Etag[0].'" tagsearch="n" href="#" style="color:'.$Etag[3].';">
									<div>
										<i><article style="background:'.$Etag[3].';"></article></i>
										<span>'.$Etag[2].'</span>
										<b></b>
									</div>
								</a>';
						}
						?>
								</div>
								<div><a href="" class="span12 btn btn_show_tagsE" onclick="printdata(); return false;">Просмотреть все тэги...</a></div>
							</div>
						</div>
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span6"><h1>Мероприятия <a class="filter" href=''><i class="icon-search"></i></a> <a class="showtags_mobile" href=''><i class="icon-align-left"></i></a></h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="row-fluid">
								<?php if(LOGGED_ACCESS == "s" or LOGGED_ACCESS == "k") { ?>
								<div class="span10">
									<div class="filter">
										<div class="row-fluid">
											<input class="span12 searchinput" type="text" placeholder="Поиск по наименованию..." />
										</div>
										<div class="row-fluid">
											<input class="span6 searchholder" type="text" placeholder="Организация..." holderid="" />
											<input class="span2 search_since" type="text" placeholder="с..." />
											<input class="span2 search_for" type="text" placeholder="по..." />
											<button class="btn span2" style="float:right; margin-bottom:10px;" onClick="findevents(); return false;"><i class="icon-search"></i></button>
										</div>
									</div>
									
								</div>
									<div class="span2"><a class="btn1 btnadd" href="" onclick="addwindow_events(); return false;">Добавить</a></div>
								<?php
				} else { ?>
									<div class="span12">
										<div class="filter">
											<div class="row-fluid">
											<input class="span12 searchinput" type="text" placeholder="Поиск по наименованию..." />
										</div>
											<div class="row-fluid">
												<input class="span6 searchholder" type="text" placeholder="Организация..." holderid="" />
												<input class="span2 search_since" type="text" placeholder="с..." />
												<input class="span2 search_for" type="text" placeholder="по..." />
												<button class="btn span2" style="float:right; margin-bottom:10px;" onClick="findevents(); return false;"><i class="icon-search"></i></button>
											</div>
										</div>
									</div>
								<?php
				}
				?>
							</div>
							<table class="table_withhead table_normalrow eventstable">
							</table>
							<div class="lowerbox">
								<div class="center textalert">По данному запросу мероприятия не найдены</div>
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