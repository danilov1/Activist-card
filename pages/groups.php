<?php accesspage(); render_doctype(); ?>
<head>
	<?php render_meta("Группы","groups"); ?>

	<script type="text/javascript">
	<?php
	$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	$predepname = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
	$depname = mysql_fetch_row($predepname);
	echo 'var depName = "'.addslashes($depname[1]).'";
';?>
	var goonit = "yes";
	var cursearch = "";
	var curLID = "";
	var ue = [];
	var uv = [];

	$(function() {
		init_groups();
	});
	</script>
</head>

<body>
<div class="fillblack"></div>
<?php if(LOGGED_ACCESS !== "a") { ?>
<div class="mw addwindow">
	<a class="closemw" href="javascript:closemw('addwindow');"><i class="icon-remove"></i></a>
	<h1>Добавление группы</h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">Название:</label>
			<div class="controls">
				<input id="list_name" class="span12" type="text" />
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Иконка <i>(необязательно)</i>:</label>
			<div class="controls">
				<span class="filepath"><input id="list_icon_path" class="uploadFile" disabled="disabled" placeholder="Формат .SVG или .PNG" style="width:180px;" /><a class="btn_delicon" href="" onclick="delicon(); return false;"><i class="icon-remove"></i></a></span>
				<div class="fileUpload btn">
					<span>Выбрать</span>
					<input id="list_icon" type="file" />
				</div>
				<div class="row-fluid"><button class="btn btn-small btn_noicon" onclick="noicon($(this));">не использовать иконку</button></div>
			</div>
		</div>

		<div class="greybox"><div class="event_info_inner span12" style="margin-bottom:10px;">
			<p><b>Просмотр списка участников группы студентами:</b></p>
			<div class="control-group">
				<div class="controls">
					<input type="radio" name="sv" value="r1" /> все студенты образовательной организации<br />
					<input type="radio" name="sv" value="r2" /> только участники этой группы<br />
					<input type="radio" name="sv" value="r3" /> никто<br />
				</div>
			</div>
		</div></div>

		<div class="greybox"><div class="event_info_inner span12" style="margin-bottom:10px;">
			<p><b>Доступ для сотрудников</b></p>
			<p>Внесение/изменение/удаление участников группы:</p>
			<div class="control-group">
				<div class="controls">
					<input type="radio" name="ue" value="r1" /> только я (создатель)<br />
					<input type="radio" name="ue" value="r3" /> только сотрудники <span></span><br />
					<input type="radio" name="ue" value="r4" /> только некоторые сотрудники<br />
					<input id="ue_search" class="span12" type="text" placeholder="" style="margin-top:10px;" />
					<div id="ue_add"></div>
				</div>
			</div>
			<p>Просмотр списка участников группы сотрудниками:</p>
			<div class="control-group">
				<div class="controls">
					<input type="radio" name="uv" value="r1" /> только я (создатель)<br />
					<input type="radio" name="uv" value="r2" /> все сотрудники образовательной организации<br />
					<input type="radio" name="uv" value="r3" /> только сотрудники <span></span><br />
					<input type="radio" name="uv" value="r4" /> только некоторые сотрудники<br />
					<input id="uv_search" class="span12" type="text" placeholder="" style="margin-top:10px;" />
					<div id="uv_add"></div>
				</div>
			</div>
		</div></div>
		<div class="control-group">
			<div class="controls">
				<a id="addbtn" class="btn1" href="" onclick="addlist_groups(); return false;">Добавить</a>
				<a id="savebtn" class="btn1" href="" onclick="addlist_groups(1); return false;">Сохранить</a>
				<a id="delbtn" class="btn1" href="" onclick="dellist_groups(); return false;" style="background:#f36b69;">Удалить</a>
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
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span6"><h1>Группы <a class="filter" href=''><i class="icon-search"></i></a></h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="row-fluid">
						<?php if(LOGGED_ACCESS !== "a") { ?>
								<div class="span10"><div class="filter"><div><input class="span12 searchinput" type="text" placeholder="Поиск по наименованию" /></div></div></div>
								<div class="span2"><a class="btn1 btnadd" href="" onclick="addwindow_groups(); return false;">Добавить</a></div>
						<?php } else { ?>
								<div class="span12"><div class="filter"><div><input class="span12 searchinput" type="text" placeholder="Поиск по наименованию" /></div></div></div>
						<?php } ?>
							</div>
							<table class="table_withhead table_normalrow liststable">
							</table>
							<div class="lowerbox">
								<div class="center textalert">По данному запросу группы не найдены</div>
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
