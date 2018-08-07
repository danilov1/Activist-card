<?php render_doctype(); ?>
<head>
	<?php render_meta("Страница не найдена","404"); ?>
	
	<style>
	.preloginbox {
		position:absolute;
		top:50%;
		left:50%;
		margin-top:-80px;
		margin-left:-115.4px;
	}
	</style>

<body class="page_index">
	<section class="page" style="opacity:1.0;">
		<div class="preloginbox">
			<div style="position:relative; margin:0 0 10px 0; text-align:center;">
				<div><img src="<?php echo $GLOBALS['config']['organization_logo']; ?>" alt="" width="40px" style="" /></div>
				<div class="head_title">ОШИБКА 404<br>Страница не найдена</div>
			</div>
		</div>
	</section>
	<?php render_footer(); ?>
</body>
</html>