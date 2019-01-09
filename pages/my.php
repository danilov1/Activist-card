<?php accesspage(); accessto("a"); render_doctype(); ?>
<head>
  <meta charset="utf-8">
  <?php render_meta("Моя активность","my"); ?>

  <style>
  	@media screen and (max-width: 768px) {
		.titleline .row-fluid { border:none; }
		.titleline h1 { padding-top:6px; text-align:center; }
		.curevent_by { display:none; }
		.undermenu { display:inline-block; }
	}
  </style>

  <script type="text/javascript">
  $(document).ready(function () {
	  activity();
  });
  </script>
</head>

<body>
  <?php render_header(); ?>
  <section class="page">
    <section class="content">
      <div class="row-fluid content_sides">
        <div class="row-fluid">
          <div class="span3">
            <?php menu("my"); ?>
			<div class="undermenu_box">
				<div class="undermenu">
				  <div class="points">Баллов: <b></b></div>
				  <div class="rank">Рейтинг: <b></b></div>
				</div>
            </div>
          </div>
          <div class="span9">
            <div class="span12 titleline">
              <div class="row-fluid">
                <div class="span6"><h1>Моя активность</h1></div>
                <div class="span6 userline"><div><span><?php echo LOGGED_FIRSTNAME.' '.LOGGED_SURNAME; ?></span> | <a class="logout" href="#">Выход</a></div></div>
              </div>
            </div>
            <div class="row-fluid">
              <table class="table_withhead table_normalrow activities">
                <tr class="table_head">
                  <td width="12%"><b>Дата</b></td>
                  <td><b>Наименование мероприятия</b></td>
                  <td width="20%"><b>Статус</b></td>
                  <td class="curevent_by" width="10%"><b>Координатор</b></td>
                  <td width="7%"><b>Балл</b></td>
                </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </section>
  <?php render_footer(); ?>
</body>
</html>
