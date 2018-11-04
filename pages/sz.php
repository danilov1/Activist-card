<?php accesspage(); accessto("s,k,t");
if($_GET['id'] !== "") {
	$forecheck = mysql_query("SELECT `id`,`name`,`holder`,`author` from `events` WHERE `id`=".$_GET['id']." LIMIT 1;");
	$echeck = mysql_fetch_row($forecheck);
	if(!$echeck[1]) { define("BYEVENT", "NO"); }
	else { define("BYEVENT", "YES"); define("EVENT_NAME", $echeck[1]); }
}

render_doctype();
?>
<head>
	<?php render_meta("Печать с/з","sz"); ?>
	<!-- jquery color  --> <script src="js/jquery-color.js"></script>
	<!-- inline editor --> <script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>

	<style>
	.greybox div.span12 { overflow:hidden; position:relative; margin-top:10px; }
	.greybox .row-fluid { margin-top:10px; }
	.greybox .row-fluid:first-child { margin-top:0px; }
	.form_ready .event_info_inner { background:#fff; }
	.form_addbtns button { margin-top:5px; }
	.greybox .closemw { top:10px; right:10px; }
	.greybox .btn_downloadlist { position:absolute; top:10px; right:45px; }
	table tbody tr { cursor:pointer; }
	table .icon-remove { opacity:0.5; }
	table .icon-remove:hover { opacity:0.9; }
	.alert {
		padding:5px;
		line-height:13px;
	}
	.form_afterprint { display:none; }
	hr { margin:20px 0 !important; }
	@media screen and (max-width: 768px) {
		.form_aftermenu { display:none; }
		.form_afterprint { display:block; }
		.form_savebtns a { display:block;}
		.form_savebtns span { display:none !important; }
		.btn, .btn1 { display:block; text-align:center; margin-top:5px; }
		.greybox .closemw { top:0; right:5px; }
		.greybox .btn_downloadlist { top:0; right:35px; }
	}
	@media (max-width: 480px) {
		.c3,.c5 { display:none; }
	}
	@media print { @page { size:auto; } body { display:none; } }
	</style>
	<script type="text/javascript">
	$(document).ready(function () {
		$(".loadlogo, .fillblack, .blocksample, .form_savebtns span, .btn_savecur, .btn_deltemp").hide();
		$(".form_tempselector select [value='']").prop("selected", true);
		$("#newtemp_name").keypress(function(e) { if(e.keyCode == 13) { if($("#newtemp_name").val() !== "") { createTemp(); } } });

		$(".temp2").html("<p><strong>служебная записка</strong><br></p>");
		$(".temp3").html("<p>"+dd+"."+mm+"."+yy+"<br></p>");
		tinymce.init({
			selector: ".temp1,.temp2,.temp3,.temp4,.temp5",
			inline: true,
			toolbar: "bold italic underline bullist numlist outdent indent removeformat",
			menubar: false,
			paste_word_valid_elements: "b,strong,i"
		});
<?php
function renderSPECIALSZ($SpecHEADER, $SpecSIGN) {
	$forecheck = mysql_query("SELECT `id`,`name`,`date`,`date_for` from `events` WHERE `id`=".$_GET['id']." LIMIT 1;");
	$echeck = mysql_fetch_row($forecheck);
	$SpecNAME = $echeck[1];
	if($echeck[3] == NULL) { $SpecSD = explode("-", $echeck[2]); $SpecDATE = " ".$SpecSD[2].".".$SpecSD[1].".".$SpecSD[0]; }
	else { $SpecSD = explode("-", $echeck[2]); $SpecSF = explode("-", $echeck[3]); $SpecDATE = " c ".$SpecSD[2].".".$SpecSD[1].".".$SpecSD[0]." по ".$SpecSF[2].".".$SpecSF[1].".".$SpecSF[0]; }
	?>
	$(".temp1").html(HTML.decode("<?php echo $SpecHEADER; ?>"));
	$(".temp2").html(HTML.decode("<p><b>служебная записка.</b></p>"));
	$(".temp3").html("<p>"+dd+"."+mm+"."+yy+"<br></p>");
	$(".temp4").html(HTML.decode("<?php echo $SpecSIGN; ?>"));
	$(".temp5").html(HTML.decode("<p>Прошу вас освободить от учебных занятий следующих студентов, принимающих участие в мероприятии &quot;<?php echo $SpecNAME; ?>&quot;<?php echo $SpecDATE; ?>:</p>"));
	list_addfromevent(1);
	$(".form_savebtns span, .btn_savecur, .btn_deltemp").show();
	$(".form_ready").slideDown(300);
	$(".form_savebtns, .form_addbtns, .form_line, .form_print").slideDown();
	<?php
}

if(isset($_GET['l']) and BYEVENT == "YES") {
	$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
	$tmpholder = mysql_fetch_row($pretmpholder);
	$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$tmpholder[1]."' LIMIT 1");
	$getdep = mysql_fetch_row($pregetdep);
	$gettemps = mysql_query("SELECT `id`,`header`,`title`,`post`,`sign`,`content`,`area` from `temp_sz` WHERE `area`=".$tmpholder[1]." OR `holder`='".LOGGED_ID."' LIMIT 1;");
	$gettempdata = mysql_fetch_row($gettemps);
	if($gettempdata[0]) { renderSPECIALSZ($gettempdata[1], $gettempdata[4]); }
}
?>
	});

	var today = new Date();
	var dd = today.getDate();
	var mm = ("0" + (today.getMonth() + 1)).slice(-2);
	var yy = today.getFullYear().toString().substr(2,2);
	var curevent = "no";
	<?php if(BYEVENT == "YES") { echo "curevent = ".$_GET['id'].";"; } ?>
	var listblock = 0;
	</script>
</head>

<body>
<div class="fillblack"></div>
<div class="mw window_newtemp">
	<a class="closemw" href="javascript:closemw('window_newtemp')"><i class="icon-remove"></i></a>
	<h1>Сохранение нового шаблона С/З</h1>
	<div class="row-fluid">
		<label class="checkbox">
			<input id="newtemp_share" type="checkbox"> Для общего пользования сотрудниками вашего подразделения
		</label>
	</div>
	<div class="row-fluid">
		<div class="span12"><input id="newtemp_name" class="span12" type="text" placeholder="Название нового шаблона" /></div>
	</div>
</div>
<div class="mw window_addfromevent" style="max-width:500px;">
	<a class="closemw" href="javascript:closemw('window_addfromevent')"><i class="icon-remove"></i></a>
	<h1>Добавить список студентов из мероприятия</h1>
	<div class="row-fluid">
		<div class="span12">
			Выберите роли:
			<label class="checkbox"><input id="addrole_1" type="checkbox"> без роли</label>
			<label class="checkbox"><input id="addrole_2" type="checkbox"> участник</label>
			<label class="checkbox"><input id="addrole_3" type="checkbox"> призер</label>
			<label class="checkbox"><input id="addrole_4" type="checkbox"> победитель</label>
			<label class="checkbox"><input id="addrole_5" type="checkbox"> помощник организатора</label>
			<label class="checkbox"><input id="addrole_6" type="checkbox"> организатор</label>
			<label class="checkbox"><input id="addrole_7" type="checkbox"> главный организатор</label>
			<hr />
			<div style="margin-bottom:10px;"><a class="btn1" href="" onclick="list_addfromevent(); return false;">Добавить</a></div>
		</div>
	</div>
</div>
	<?php render_header(); ?>
	<section class="page">
		<section class="content">
			<div class="row-fluid content_sides">
				<div class="row-fluid">
					<div class="span3">
						<?php menu(); ?>
					</div>
					<div class="span9">
						<div class="span12 titleline">
							<div class="row-fluid">
								<div class="span8"><h1>Печать С/З<?php if(BYEVENT == "YES") { echo " - ".EVENT_NAME; } ?></h1></div>
								<div class="span4 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
							</div>
						</div>

						<div class="greybox form_tempselector"><div class="event_info_inner span12" style="margin-top:0 !important;">
							<div class="row-fluid">
								<select onchange="fillByTemp();" style="width:100%;">
									<option value="">Выберите шаблон</option>
									<?php
									$pretmpholder = mysql_query("SELECT `id`,`dep` from `users` WHERE `id`='".LOGGED_ID."' LIMIT 1");
									$tmpholder = mysql_fetch_row($pretmpholder);
									$pregetdep = mysql_query("SELECT `id`,`name` from `deps` WHERE `id`='".$tmpholder[1]."' LIMIT 1");
									$getdep = mysql_fetch_row($pregetdep);
									$gettemps = mysql_query("SELECT `id`,`name`,`holder`,`area` from `temp_sz` WHERE `area`=".$tmpholder[1]." OR `holder`='".LOGGED_ID."';");
									while($temps = mysql_fetch_array($gettemps)) {
										if($temps[3] == $tmpholder[1]) { echo '<option value="'.$temps[0].'">'.$getdep[1].' - '.$temps[1].'</option>'; }
										else { echo '<option value="'.$temps[0].'">'.$temps[1].'</option>'; }
									}
									?>
								</select>
								<!--<div class="span1"><a href="#" class="btn1 btnadd" onclick="newTemp(); return false;" style="padding:5px 1px; text-align:center;"><i class="icon-plus icon-white"></i></a></div>-->
							</div>
							<div class="form_savebtns"><a class="btn_savecur" href="" onclick="saveInTemp(); return false;"><i class="icon-download"></i> Сохранить в текущем шаблоне</a><span> | </span><a class="btn_savenew" href="" onclick="createTempWindow(); return false;"><i class="icon-plus"></i> Сохранить в новом шаблоне</a><span> | </span><a class="btn_deltemp" href="" onclick="delTemp(); return false;"><i class="icon-remove"></i> Удалить шаблон</a></div>
						</div></div>
						<div class="greybox form_ready"><div class="event_info_inner span12" style="position:relative; line-height:14px;">
							<div class="row-fluid"><div class="span2"><b>Шапка:</b></div><div class="span10"><div class="temp_content temp1" contenteditable="true"><p><br></p></div></div></div>
							<div class="row-fluid"><div class="span2"><b>Заголовок:</b></div><div class="span10"><div class="temp_content temp2" contenteditable="true"><p><br></p></div></div></div>
							<div class="row-fluid"><div class="span2"><b>Дата:</b></div><div class="span10"><div class="temp_content temp3" contenteditable="true"><p><br></p></div></div></div>
							<div class="row-fluid"><div class="span2"><b>Инициалы:</b></div><div class="span10"><div class="temp_content temp4" contenteditable="true"><p><br></p></div></div></div>
							<div class="row-fluid"><div class="span2"><b>Содержание:</b></div><div class="span10"><div class="temp_content temp5" contenteditable="true"><p><br></p></div></div></div>
							<div class="row-fluid form_addbtns">
								<div class="span2"></div><div class="span10"><button href="" class="btn" onclick="list_addnew(); return false;"><i class="icon-plus"></i> Создать список студентов</button><?php if(BYEVENT == "YES") { ?> <button href="" class="btn btn-success" onclick="addfromevent_window(); return false;"><i class="icon-plus icon-white"></i> Загрузить список из мероприятия</button> <?php } ?> <!--<button href="" class="btn"><i class="icon-download"></i> Сохранить списки</button>--></div>
							</div>
							<!--<div class="editicon"><a href="#" onclick="$('.form_ready').slideUp(); $('.form_edit').slideDown(); return false;"><i class="icon-edit icon-white"></i> изменить</a></div>-->
						</div></div>

						<div class="blocksample">
							<div class="greybox block_students" style="display:none;"><div class="event_info_inner span12">
								<!--<a class="btn btn-mini closemw" href="" style="right:45px;"><i class="icon-list"></i></a>--><a class="btn btn-mini closemw btn_dellist" href=""><i class="icon-remove"></i></a>
								<a class="btn btn-mini btn_downloadlist" href=""><i class=" icon-download-alt"></i></a>
								<h2></h2>
								<div class="row-fluid regnew labelline">
									<input class="span12 f_searcher" type="text" placeholder="Добавить студента по фамилии..." style="padding:0 10px;" />
								</div>
								<table class="table_withhead table_forshort">
									<thead>
										<tr class="table_head">
											<td width="5%" class="c1 center"><b>№</b></td>
											<td class='c2'><a class="sort2" href=""><b>ФИО</b></a></td>
											<td width="5%" class='c3'><a href="" class="sort3"><b>Курс</b></a></td>
											<td width="15%" class='c4'><a href="" class="sort4"><b>Факультет</b></a></td>
											<td width="20%" class='c5'><a href="" class="sort5"><b>Группа</b></a></td>
											<td width="3%" class='c6'><b></b></td>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div></div>
						</div>

						<div class="list_blocks">
						</div>

						<div class="row-fluid form_line"><hr style="border:1px solid #eee;" /></div>

						<div class="row-fluid form_print">
							<div align="right">
								<a href="" class="btn" onclick="printSZ('y'); return false;"><i class="icon-search"></i> Предварительный просмотр</a>
								<a href="" class="btn1 btn" onclick="printSZ('n'); return false;"><i class="icon-print icon-white"></i> Печать С/З</a>
							</div>
						</div>
						<!--
						<div class="form_afterprint">
							<hr>
							<div class="row-fluid" style="margin-top:20px;">
								<button href="" class="span12 btn">Реестр напечатанных с/з</button>
							</div>
							<div class="row-fluid" style="margin-top:5px;">
								<button href="" class="span12 btn">Сохраненные списки</button>
							</div>
						</div>
						-->
					</div>
				</div>
			</div>
		</section>
	</section>
	<?php render_footer(); ?>
</body>
</html>
