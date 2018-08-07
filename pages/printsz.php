<?php accesspage(); accessto("s,k,t");
if(!$_GET["id"]) { exit; }
if(isset($_GET["preview"]) and $_GET["preview"] == "yes") { define("PREVIEW","YES"); } else { define("PREVIEW","NO"); }

session_id($_GET["id"]);
session_start();
//if(!$_SESSION["temp1"]) { exit; }

define("SZ_header",$_SESSION["temp1"]);
define("SZ_title",$_SESSION["temp2"]);
define("SZ_post",$_SESSION["temp3"]);
define("SZ_sign",$_SESSION["temp4"]);
define("SZ_content",$_SESSION["temp5"]);

unset($_SESSION);
session_destroy();

require_once("../plugins/mpdf/mpdf.php");

$html = '<div class="page">
		<div class="padded">
			<div class="header">'.html_entity_decode(stripcslashes(SZ_header)).'</div>
			<div class="clear"></div>
			<div class="title">'.html_entity_decode(stripcslashes(SZ_title)).'</div>
			<div class="text">'.html_entity_decode(stripcslashes(SZ_content)).'</div>
			<div class="footer">
				<div class="post">'.html_entity_decode(stripcslashes(SZ_post)).'</div>
				<div class="sign">'.html_entity_decode(stripcslashes(SZ_sign)).'</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>';
	
$mpdf = new mPDF('utf-8', 'A4', '14', 'Times New Roman', 20, 15, 15, 15, 10, 10);
$mpdf->charset_in = 'utf-8';
$mpdf->useOnlyCoreFonts = true;
$stylesheet = file_get_contents('css/printsz.css');
$mpdf->WriteHTML($stylesheet, 1);
if(PREVIEW == "NO") { $mpdf->SetJS('this.print();'); } 
$mpdf->WriteHTML($html, 2);
$mpdf->Output('Документ Карта активиста.pdf', 'I');
exit;


?>
<!DOCTYPE html>
<!--[if IE 7]>                  <html class="ie7 no-js" lang="en">      <![endif]-->
<!--[if lte IE 8]>              <html class="ie8 no-js" lang="en">      <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="not-ie no-js" lang="en">	<!--<![endif]-->
<head>
	<meta charset="utf-8">
	<title>Печать служебной записки | КАРТА АКТИВИСТА</title>
	<link rel="shortcut icon" href="img/favicon.ico">
	<meta name="author"                content="Совет Студентов и Аспирантов ФГБОУ ВПО ПГЛУ, Владимир Данилов">
	<meta name="description"           content="">
	<meta name="keywords"              content="">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="">
	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	
	<!-- jquery       --> <script src="js/fancybox/lib/jquery-1.9.0.min.js"></script>
	<!-- js interface --> <script src="js/interface.js"></script>
	
	<style>
		@media screen {
			body {
				background:url(../img/bg.png) repeat fixed;
				margin:0;
				padding:0;
				font-family:"Times New Roman", Times, serif;
				font-size:16px;
				line-height:normal;
				direction:ltr;
				color:#3d5065;
			}
			.page {
				zoom:1;
			    background:#fff;
				background:rgba(255,255,255,0.85);
				width:210mm;
				margin:10px auto;
				box-shadow:0 0 4px 2px rgba(0,0,0,0.2);
			}
			.padded {
				position:relative;
				margin:0 1cm 0 1.5cm;
				padding:1cm 0;
			}
			.cardlogo {
				top:35px;
				left:-20px;
				opacity:0.7;
			}
			.cardlogo img { width:200px; }
			.header, .footer { color:#333; }
		}
		
		@media print {
			body {
				padding:0;
				margin:0;
				font-family:"Times New Roman", Times, serif;
				font-size:16px;
				color:#000;
			}
			.cardlogo img {
				width:150px;
			}
			@page {
				size:auto;
				margin:1cm 1.5cm 2cm 2cm;
			}
		}
		
		.cardlogo {
			display:none;
			position:absolute;
			border:1px solid #000;
		}
		p { margin:0; }
		.clear { clear:both; }
		.header { float:right; width:40%; }
		.title {
			margin:1cm 0 0.5cm 0;
			font-weight:bold;
			text-align:center;
		}
		.text p {
			margin-bottom:5px;
			line-height:18px;
			text-indent:1cm;
			text-align:justify;
		}
		/*ol { margin:5px 0 0 0; }*/
		.footer { margin-top:1cm; }
		.footer div:first-child { float:left; width:40%; }
		.footer div:last-child { float:right; }
	</style>
	<script>
		$(function() {
			var header = "<?php echo stripcslashes(SZ_header); ?>";
			var title = "<?php echo stripcslashes(SZ_title); ?>";
			var post = "<?php echo stripcslashes(SZ_post); ?>";
			var sign = "<?php echo stripcslashes(SZ_sign); ?>";
			var content = "<?php echo stripcslashes(SZ_content); ?>";
			
			$(".header").html(HTML.decode(header));
			$(".title").html(HTML.decode(title));
			$(".post").html(HTML.decode(post));
			$(".sign").html(HTML.decode(sign));
			$(".text").html(HTML.decode(content));
			
			<?php if(PREVIEW == "NO") { echo 'window.print(); window.close();'; } ?>
		});
	</script>
</head>

<body>
	<div class="page">
		<div class="padded">
			<div class="cardlogo"><img src="img/aiscardlogo.png" alt="" /></div>
			<div class="header"></div>
			<div class="clear"></div>
			<div class="title"></div>
			<div class="text"></div>
			<div class="footer">
				<div class="post"></div>
				<div class="sign"></div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<script src="js/bootstrap.min.js"></script>
</body>
</html>