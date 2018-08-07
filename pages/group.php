<?php accesspage(); accessto("a,s,k,t");
$geturl = explode("-",$_SERVER[REQUEST_URI]);

if((!$geturl[1]) or (!is_numeric($geturl[1]))) { exit; }
define("LISTID",$geturl[1]);

$pregetdep = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
$getdep = mysql_fetch_row($pregetdep);
$predepname = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$getdep[1]."' LIMIT 1");
$depname = mysql_fetch_row($predepname);
define("DEPNAME",$depname[1]);

if(LOGGED_ACCESS == "a") { $addcond = "`rights` LIKE '%_sva_%' OR `content` LIKE '%[\"".LOGGED_ID."\",\"%'"; }
else { $addcond = "`rights` LIKE '%_uva_%' OR `rights` LIKE '%_uvd(".$getdep[0].")_%' OR `rights` LIKE '%_uvs(".LOGGED_ID.")_%' OR `holder` = '".LOGGED_ID."'"; }

$prelcheck = mysql_query("SELECT `id`,`name`,`rights`,`holder`,`icon`,`public` from `lists` WHERE `id`='".LISTID."' AND (".$addcond.") LIMIT 1;");
$list = mysql_fetch_row($prelcheck);
if(!$list[0]) { exit("Данная группа недоступна"); }
define("LIST_NAME",$list[1]);

render_doctype();
?>
<head>
	<?php render_meta("".LIST_NAME."","groups"); ?>
	<!-- inline editor --> <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
	
	<style>
	@media screen and (max-width: 768px) {
		.greybox , .table_head { display:none; }
	}
	</style>
	
	<script type="text/javascript">
	var curmemberid;
	var listid = "<?php echo LISTID; ?>";
	var depname = "<?php echo addslashes(DEPNAME); ?>";
	var ue = [];
	var uv = [];
	$(function() {
		$(".searchbox").hide();
		page_group();
	});
	</script>
</head>

<body>
<div class="fillblack"></div>
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
								<div class="span6"><h1>Группы</h1></div>
								<div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>
						<div class="row-fluid">
							<div class="greybox"><div class="event_info_inner span12" style="margin-bottom:10px;">
								<div class="row-fluid list_info">
								</div>
								<?php if(LOGGED_ACCESS == "s" and $list[4] !== "n") {
									echo '<div class="row-fluid"><button href="" class="span12 btn btn-success listpublicicon" publicset="y" onclick="listpublicicon(); return false;"></button></div>';
								} ?>
							</div></div>
							<div class="clear"></div>
							
							<div class="row-fluid searchbox">
								<div class="span12"><input class="span12 searchinput" type="text" placeholder="Добавить студента..." /></div>
							</div>
							<div class="table_head">
								<div>
									<div style="margin:0 10px;">
										<div class="row-fluid" style="height:30px;">
											<div class="span5 editSortFIO" style="padding-top:5px;"><b>ФИО студента</b></div>
											<div class="span6" style="padding-top:5px;"><b>Комментарий</b></div>
											<div class="span1"></div>
										</div>
									</div>
								</div>
							</div>
							<div class="personslist">
							</div>
							<div class="lowerbox">
								<div class="center textalert">Студенты еще не добавлены</div>
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