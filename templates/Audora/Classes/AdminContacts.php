<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������� ������ �������� �����
*/
class TplAdminContacts
{
		�������� �������������� ���������� �������� �����
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$error - ������, ���� ������ ������ - ������ �� ����
	*/
	{
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
			else
				$Lst->head($v);
		return Eleanor::$Template->Cover((string)$Lst->button(Eleanor::Button())->end()->endform(),$error)
			.'<script type="text/javascript">//<![CDATA[
$(function(){
	$("table.whoms").each(function(){
		var t=$(this),
			AppDaD=function()
			{
				t.DragAndDrop({
					items:"tr:has(td)",
					move:".updown",
					replace:"<tr style=\"height:35px\"><td colspan=\"4\">&nbsp;</td></tr>"
				}).find("tr").width(t.innerWidth());
			}

		t.on("click",".sb-plus",function(){
			var tr=$(this).closest("tr");
			tr.clone(false).find("input[type=text]").val("").end().insertAfter(tr);
			AppDaD();
		}).on("click",".sb-minus",function(){
			var tr=$(this).closest("tr");
			if(t.find("tr").size()>2)
				tr.remove();
			else
				tr.find("input[type=text]").val("");
		})

		AppDaD();
	});
});//]]></script>';
	}

	/*
		������� �������. ������� ����� ����������� ������� ����������� �������� �����
		$n - ���-������� ���� ���������
		$emails - ������ ����������� ������ email=>���
	*/
	public static function LoadWhom($n,$emails)
	{
		$GLOBALS['jscripts'][]='js/jquery.drag.js';
		$trs='';
		$bs=Eleanor::Button('+','button',array('class'=>'sb-plus')).' '
			.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2);
		foreach($emails as $k=>&$v)
			$trs.='<tr><td><img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" /></td><td>'.Eleanor::Edit($n.'[email][]',$k,array('style'=>'width:100%')).'</td><td>'.Eleanor::Edit($n.'[whom][]',$v,array('style'=>'width:100%')).'</td><td style="function">'.$bs.'</td></tr>';
		return'<table class="tabstyle whoms" style="width:420px"><tr class="first tablethhead"><th></th><th>E-mail</th><th>'.Eleanor::$Language['contacts']['who'].'</th><th style="width:60px">'.Eleanor::$Language['tpl']['functs'].'</th></tr>'
			.($trs ? $trs : '<tr><td><img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" /></td><td>'.Eleanor::Edit($n.'[email][]','',array('style'=>'width:100%')).'</td><td>'.Eleanor::Edit($n.'[whom][]','',array('style'=>'width:100%')).'</td><td class="function">'.$bs.'</td></tr>')
			.'</table>';
	}
}