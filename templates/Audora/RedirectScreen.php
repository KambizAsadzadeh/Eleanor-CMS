<?php
/*
	��������� ������� �������, ������� ���������� �������

	@var ������ ��� ��������
	@var ����� ������, �� ��������� ������� ���������� ����������� �������
*/
if(!defined('CMS'))die;
$our=PROTOCOL.Eleanor::$domain.Eleanor::$site_path;
$href=strpos($v_0,$our)===0 ? $v_0 : $our.$v_0;
$GLOBALS['head']['redirect']='<meta http-equiv="refresh" content="'.$v_1.'; url='.$href.'" />';
echo'<script type="text/javascript">//<![CDATA[
	$(function(){
		if($("meta[http-equiv=\"refresh\"]").size()==0)
			setTimeout(function(){window.location.href="'.$href.'"},'.$v_1.'*1000);
	});
//]]></script>';