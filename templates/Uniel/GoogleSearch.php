<?php
/*
	������ ��� ������������� ������ "�����" � �������������� ��������� ��������� Google.

	@var array(
		id - ������������� google custom search
		ads - ������������� ��������, � ������ �������� � ����������� ������ ����� ������������ �������
	)
*/
if(!defined('CMS'))die;
global$Eleanor;
echo Eleanor::$Template->Title($Eleanor->module['title'])?><div id="cse" style="padding:10px"><?php echo Eleanor::$Language['tpl']['loading']?></div>
<script src="http://www.google.com/jsapi" type="text/javascript"></script>
<script type="text/javascript">//<![CDATA[
google.load('search','1');
google.setOnLoadCallback(function(){
	var cse=new google.search.CustomSearchControl('<?php echo$id?>');
<?php
if($ads)
	echo'cse.enableAds(\''.$ads.'\');'?>
cse.draw('cse');
},true);
//]]></script>