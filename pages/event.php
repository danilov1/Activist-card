<?php accesspage(); accessto("s,k,t,a");
$geturl = explode("-",$_SERVER[REQUEST_URI]);

if((!$geturl[1]) or (!is_numeric($geturl[1]))) { exit; }
define("EVENTID",$geturl[1]);
$forecheck = mysql_query("SELECT `id`,`holder`,`author`,`fixers` from `events` WHERE `id`=".EVENTID." LIMIT 1;");
$echeck = mysql_fetch_row($forecheck);
if(!$echeck[1]) { exit; }

// Проверка департамента
$_preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".$echeck[1]." LIMIT 1;");
$_ifindep = mysql_fetch_row($_preifindep);

$preifindep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`=".LOGGED_ID." LIMIT 1;");
$ifindep = mysql_fetch_row($preifindep);

$checkfixers = json_decode($echeck[3]);
$pos = array_search(LOGGED_ID, $checkfixers);

if((LOGGED_ID == $echeck[1]) or (LOGGED_ID == $echeck[2]) or (LOGGED_ACCESS == "s") or ($pos !== false) or ($_ifindep[1] == $ifindep[1])) { define("ISHOLDER", "YES"); }
else { define("ISHOLDER", "NO"); }

render_doctype();
?>
<head>
	<?php render_meta("Мероприятия","events"); ?>

	<style>
	.content input, .content select { margin-bottom:0; }
	<?php if(ISHOLDER !== "YES") { ?>.activitytable td { padding:10px; }<?php } ?>
	</style>

	<script type="text/javascript">
	$(window).bind('load', function() {
		$("#add_activity").focus();
	});
	$(document).ready(function () {
		$(".loadlogo, .fillblack, .textalert").hide();
		<?php if(ISHOLDER == "YES") { ?>
		init_event_control();
		<?php } else { ?>
		init_event_user();
		<?php } ?>
	});
	var curevent = <?php echo EVENTID; ?>;
	var si_already = "";
	var curaid;
	</script>
</head>

<body>
<div class="fillblack"></div>
<?php if(LOGGED_ACCESS !== "a") { ?>
<div class="mw commentwindow">
	<a class="closemw" href="javascript:closemw('commentwindow');"><i class="icon-remove"></i></a>
	<h1></h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">Пользователь:</label>
			<div class="controls">
				<input id="addme_holder" class="span12" type="text" placeholder="Поиск по ФИО..." />
				<div id="addme_holderend"></div>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Комментарий:</label>
			<div class="controls">
				<textarea id="addme_comment" class="span12" placeholder="" style="resize:none;"></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<a id="addme_savebtn" class="btn1" href=""></a>
			</div>
		</div>
	</div>
</div>
<?php
} else { ?>
<div class="mw addmewindow">
	<a class="closemw" href="javascript:closemw('addmewindow');"><i class="icon-remove"></i></a>
	<h1></h1>
	<div class="row-fluid form-horizontal" style="width:500px;">
		<div class="control-group">
			<label class="control-label">Роль:</label>
			<div class="controls">
				<select id="addme_role" class="span12"><!--<option value="n">без роли</option>--><option value="b">без роли</option><option value="u" selected="selected">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select>
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">
				<img src="img/muscle_black.svg" width="15px" alt="" style="margin-top:-5px;" />
			</label>
			<div class="controls">
				<input id="addme_complex" type="checkbox" onChange="">
			</div>
		</div>
		<div class="control-group">
			<label class="control-label">Комментарий:</label>
			<div class="controls">
				<textarea id="addme_comment" class="span12" placeholder="" style="resize:none;"></textarea>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<a id="addme_savebtn" class="btn1" href="" onclick="addme(); return false;">Отправить заявку</a>
				<a id="removemebtn" class="btn1" href="" onclick="removeme(); return false;" style="background:#f36b69;">Удалить</a>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
<div class="hiddenbox" style="display:none;">
	<div class="box_student" style="position:relative; width:600px;">
		<h1 style="width:665px;">Карта активиста</h1>
		<script>var getstudby = "i";</script>
		<p class="box_student_info"></p>
		<div class="box_student_lists" style="margin-bottom:10px;"></div>
		<table class="table_withhead table_forshort owntable">
		</table>
		<div class="box_student_add"></div>
	</div>
</div>
<div class="fastmassage">
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
								<div class="span8"><h1></h1></div>
								<div class="span4 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="greybox event_info"><div class="event_info_inner span12" style="margin-bottom:10px;">
							<div class="row-fluid">
								<div class="span6">
									<p class="inforow event_i1"></p>
								</div>
								<div class="span6">
									<p class="inforow event_i2"></p>
								</div>
							</div>
							<div class="event_comment_box"></div>
							<div style="padding-bottom:0 !important;">
							<?php
						$forecheck = mysql_query("SELECT `id`,`tags` from `events` WHERE `id`=".EVENTID." LIMIT 1;");
						$echeck = mysql_fetch_row($forecheck);
						$tags = json_decode(stripslashes($echeck[1]));
						for($i = 0; $i < count($tags); $i++) {
							$gettags = mysql_query("SELECT `id`,`type`,`name`,`style` from `tags` WHERE `id`='".$tags[$i]."' LIMIT 1");
							$tag = mysql_fetch_row($gettags);
							if($tag[1] == "a") {
								echo '<span class="tag" tagID="'.$tag[0].'">
									<div>
										<span>'.$tag[2].'</span>
									</div>
								</span>';
							} elseif($tag[1] == "e") {
								echo '<span class="tag tagPoint" tagID="'.$tag[0].'" style="color:'.$tag[3].';">
									<div>
										<i><article style="background:'.$tag[3].';"></article></i>
										<span>'.$tag[2].'</span>
										<b></b>
									</div>
								</span>';
							}
						}
						?>
							</div>
						</div></div>
						<?php if(ISHOLDER == "YES") { ?>
						<div class="greybox"><div class="span12" style="margin-bottom:10px;">
							<div class="row-fluid regnew">
								<div class="span9">
									<input id="add_activity" class="span12 f_inner" type="text" placeholder="НАЖМИТЕ ДЛЯ ДОБАВЛЕНИЯ СТУДЕНТА" />
								</div>
								<div class="span3">
									<select id="add_role" class="span12" onchange="if(!ismobile()) { $('#add_activity').focus(); }"><!--<option value="n">без роли</option>--><option value="b">без роли</option><option value="u" selected="selected">участник</option><option value="p">призер</option><option value="w">победитель</option><option value="l">помощник организатора</option><option value="m">организатор</option><option value="h">главный организатор</option></select>
								</div>
							</div>
						</div></div>
						<?php } ?>
						<table class="table_withhead table_normalrow activitytable">
						</table>
						<div class="lowerbox">
							<div class="center textalert">Задействованных нет</div>
							<div class="pager"></div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>
</body>
</html>
