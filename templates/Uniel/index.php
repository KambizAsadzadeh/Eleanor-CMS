<?php
/*
	������� ��������� �������.
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];?><!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#">
<head>
{head}
<link media="screen" href="<?php echo$theme?>style/main.css" type="text/css" rel="stylesheet" />
<link rel="shortcut icon" href="favicon.ico" />
</head>

<body class="page_bg">
<div id="loading">
	<span><?php echo$ltpl['loading']?></span>
</div><script type="text/javascript">//<![CDATA[
$(function(){
	$("#loading").on("show",function(){
			top:Math.round(($(window).height()-$(this).height())/2)
		});
	}).triggerHandler("show");
	$(window).resize(function(){
});//]]></script>
<?php
if(Eleanor::$Permissions->IsAdmin())
	include Eleanor::$root.'addons/blocks/block_adminheader.php';
?>
<div class="wrapper">
<div id="headerboxic"><div class="dleft"><div class="dright">
	<a class="logotype" href="http://<?php echo Eleanor::$domain.Eleanor::$site_path?>"><img src="<?php echo$theme?>images/eleanorcms.png" alt="Eleanor CMS" title="Eleanor CMS" /></a>
	<span class="headbanner">
		<!-- ������ 468x60-->
		<!-- <a href="link.html" title="��� ������"><img src="<?php echo$theme?>images/spacer.png" alt="��� ������" /></a> -->
	</span>
</div></div></div>

<div id="menuhead"><div class="dleft"><div class="dright">
<div class="language">
<?php
if(Eleanor::$vars['multilang'])
{
	$langs=Eleanor::$langs;
	unset($langs[Language::$main]);
	foreach($langs as $k=>$v)
		echo'<a href="'.Eleanor::$filename.'?language='.$k.'" title="'.$v['name'].'"><b>'.substr($k,0,3).'</b></a>';
}
?>
</div>
<?php echo include Eleanor::$root.'addons/menus/multiline.php'; ?>
</div></div></div>

<div class="container">
	<div class="mainbox">
<?php
$br=Blocks::Get('right');
$bl=Blocks::Get('left');
echo'<div id="maincol'.($br ? 'R' : '').'">
			<div class="baseblock"><div class="dtop"><div class="dbottom">
				<div class="dcont">'
				.Blocks::Get('center_up')
				.'<!-- CONTEXT LINKS -->{module}<!-- /CONTEXT LINKS -->'
				.Blocks::Get('center_down')
				.'</div>
			</div></div></div>
		</div>'.($br ? '<div id="rightcol">'.$br.'</div>' : '');
?>
	</div>
	<div id="leftcol">
<?php
	include Eleanor::$root.$theme.'Static/login.php';
	echo$bl;
?>
	</div>

	<div class="clr"></div>
</div>

<div id="footmenu"><div class="dleft"><div class="dright">
	<a title="<?php echo$ltpl['to_top']?>" onclick="scroll(0,0); return false" href="#" class="top-top"><img src="<?php echo$theme?>images/top-top.png" alt="" /></a>
	<span class="menu"><?php echo include Eleanor::$root.'addons/menus/line.php'; ?></span>
</div></div></div>

<div id="footer"><div class="dleft"><div class="dright">
	<div class="count">
		<span style="width: 88px;"><!-- ������, ������� --></span>
		<span style="width: 88px;"><!-- ������, ������� --></span>
		<span style="width: 88px;"><!-- ������, ������� --></span>
		<span style="width: 88px;"><!-- ������, ������� --></span>
		<span style="width: 60px;">  <a href="http://validator.w3.org/check?uri=referer"><img src="<?php echo$theme?>images/xhtml_valid.png" alt="Valid HTML 5" title="Valid HTML 5" width="60" height="31" /></a></span>

	</div>
	<!-- ��������� -->
	<span class="copyright">&copy; Copyright</span>
	<div class="clr"></div>
</div></div></div>
<div id="syscopyright">
	<span class="centroarts"><a href="http://centroarts.com" title="������ ���������� ������� CENTROARTS.com">Designed by CENTROARTS.com</a></span>
	<div><?php
	#��������! ����������� �������� ���������� ������� ����������� �� ����������� ����� ������� � ������������ �� ������!
	#��������� ������/������� ������! ������!! ��� ������ ���������� ����������� �� ����! ����� ����������� � �� �������!
	echo'Powered by '.ELEANOR_COPYRIGHT?></div>
	<div>{page status}</div>
	<div>{debug}</div>
</div>
</div>

</body>
</html>