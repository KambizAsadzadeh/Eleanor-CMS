<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	���� ���� �������� �� ���������� ������ "�������" � �������
*/
class TPLGeneral
{	/*
		���� �������
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['sg'];
		$links=&$GLOBALS['Eleanor']->module['links'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],Eleanor::$Language['main']['main page'],'act'=>$act=='main'),
			array($links['server'],$lang['server_info'],'act'=>$act=='server'),
			array($links['logs'],$lang['logs'],'act'=>$act=='logs'),
			array($links['license'],$lang['license_'],'act'=>$act=='license'),
		);
	}
	/*
		������� �������� �������

		$nums - ������, �������� �����:
			c - ���������� ������������ �����
			cw - ���������� ������������ �� ������� ������
			u - ���������� ������������� �����
			uw - ���������� ������������� �� ������� ������
			sl - ���� ����� ����� � ����
		$comments �������� ������� ������ (!) ��������� ������������
		$users - ������, �������� �������� ��������� ������������������ ������������� �� �����. ���������: id=>array(), ����� ������� ������� ��������:
			full_name - ������ ��� ������������ (���������� HTML)
			name - ����� ������������ (������������ HTML)
			email - ����������� ����� ������������
			groups - array - �������� ����� ������������
			ip - IP ����� ������������
			register - ���� ����������� ������������ � ������� Y-m-d H:i:s
			last_visit - ���� ���������� ����� ������������ � ������� Y-m-d H:i:s
		$groups - ������, �������� �������� ����� �������������, ��� �������� $users[ID]['groups']. ���������: id=>array(), ����� ������� ������� ��������:
			title - �������� ������
			html_pref - HTML ������� ������
			html_end - HTML ������� ������
		$mynotes - ������ � ���������� "����" �������
		$conotes - ������ � ���������� ����� �������
		$ck - ����������, ������������� ������ �� ���
	*/
	public static function General($nums,$comments,$users,$groups,$mynotes,$conotes,$ck)
	{		static::Menu('main');		$lang=Eleanor::$Language['sg'];
		$ltpl=Eleanor::$Language['tpl'];

		$ULst=Eleanor::LoadListTemplate('table-list',7)->begin($lang['name'],'E-mail',$lang['group'],$lang['reg'],$lang['lastw'],'IP',$ltpl['functs']);
		$myuid=Eleanor::$Login->GetUserValue('id');
		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($users as $k=>&$v)
		{
			$grs='';
			foreach($v['groups'] as &$gv)
				if(isset($groups[$gv]))
					$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title'].$groups[$gv]['html_end'].'</a>, ';
			$ULst->item(
				'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
				array($v['email'],'center'),
				rtrim($grs,' ,'),
				array(substr($v['register'],0,-3),'center'),
				array(substr($v['last_visit'],0,-3),'center'),
				array($v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'hrefaddon'=>array('target'=>'_blank')),
				$ULst('func',
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
					$myuid==$k ? false : array($v['_adel'],$ltpl['delete'],$images.'delete.png')
				)
			);
		}
		$ULst->end();

		$lang=Eleanor::$Language['sg'];
		$modules=Modules::GetCache();

		$newsurl=array_keys($modules['ids'],1);#�������
		$newsurl=urlencode(reset($newsurl));

		$pageurl=array_keys($modules['ids'],2);#����������� ��������		$pageurl=urlencode(reset($pageurl));

		$menuurl=array_keys($modules['ids'],7);#����
		$menuurl=urlencode(reset($menuurl));

		$c=Eleanor::$Template->OpenTable()
	.'<div class="wbpad twocol"><div class="colomn">
<ul class="reset blockbtns">
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$newsurl.'&amp;do=add"><img src="images/modules/news-big.png" alt="" /><span>'.$lang['crnews'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$pageurl.'&amp;do=add"><img src="images/modules/static-big.png" alt="" /><span>'.$lang['crpage'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=modules&amp;module='.$menuurl.'&amp;do=add"><img src="images/modules/menu-big.png" alt="" /><span>'.$lang['crmenu'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=blocks&amp;do=add"><img src="images/modules/blocks-big.png" alt="" /><span>'.$lang['crbl'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=users&amp;do=add"><img src="images/modules/users-big.png" alt="" /><span>'.$lang['cruser'].'</span></a></li>
<li><a href="'.Eleanor::$services['admin']['file'].'?section=management&amp;module=spam&amp;do=add"><img src="images/modules/spam-big.png" alt="" /><span>'.$lang['crspam'].'</span></a></li>
</ul></div>
<div class="colomn">
<div class="blockwel"><div class="pad"><h3 class="dtitle">'.$lang['thanks'].'</h3>'.$lang['thanks_'].'</div></div>
</div>
<div class="clr"></div>
</div>'.Eleanor::$Template->CloseTable();

		if(file_exists(Eleanor::$root.'install'))
			$c.=Eleanor::$Template->Message($lang['install_nd'],'warning');
		$GLOBALS['jscripts'][]='js/tabs.js';
		$c.=Eleanor::$Template->Title($ltpl['info'])->OpenTable()
	.'<ul id="stabs" class="reset linetabs">
	<li><a class="selected" data-rel="stab1" href="#"><b>'.$lang['stat'].'</b></a></li>
	<li><a data-rel="stab2" href="#"><b>'.$lang['comments'].'</b></a></li>
	<li><a data-rel="stab3" href="#"><b>'.$lang['users'].'</b></a></li>
	<li><a data-rel="stab4" href="#"><b>'.$lang['newselc'].'</b></a></li>
	<li><a data-rel="mynotes" href="#"><b>'.$lang['ownnote'].'</b></a></li>
	<li><a data-rel="conotes" href="#"><b>'.$lang['gennote'].'</b></a></li>
</ul>
<div id="stab1" class="tabcontent">
<table class="tabstyle">
<tr class="first tabletrline1"><td>'.$lang['stcomm'].'</td><td style="text-align:center"><b>'.$nums['cw'].'</b> ('.$nums['c'].')</td></tr>
<tr class="tabletrline2"><td>'.$lang['stuser'].'</td><td style="text-align:center"><b>'.$nums['uw'].'</b> ('.$nums['u'].')</td></tr>
<tr class="tabletrline1"><td>'.$lang['stsite'].'</td><td style="text-align:center"><b>'.$nums['sl'].'</b></td></tr>
<tr class="last tabletrline2"><td>'.$lang['time_on_server'].'</td><td style="text-align:center">'.Eleanor::$Language->Date().'</td></tr>
</table>
</div>
<div id="stab2" class="tabcontent">'.$comments.'</div>
<div id="stab3" class="tabcontent">'.$ULst.'</div>
<div id="stab4" class="tabcontent"></div>
<div id="mynotes" class="tabcontent">'.static::Notes($mynotes).'</div>
<div id="conotes" class="tabcontent">'.static::Notes($conotes).'</div>
<script type="text/javascript">//<![CDATA[
$(function(){
	$("#stabs a").Tabs({		OnBeforeSwitch:function(a){			if(a.data("rel")=="stab4" && !$("#stab4").html())
			{				CORE.ShowLoading();				$.getJSON("http://eleanor-cms.ru/updates.php?ver=1&c=?",function(d){					$("#stab4").html(d.data);					CORE.HideLoading();
				});
			}
			return true;		}
	});
	$("#mynotes,#conotes").on("click",".submitline [type=button]",function(){		var p=$(this).closest(".tabcontent").attr("id"),
			s=$(this).data("save");		CORE.Ajax(
			{
				direct:"admin",
				file:"notes",
				event:p+(s ? "" : "load"),
				text:s ? EDITOR.Get("e"+p) : "",
			},
			function(r)
			{
				$("#"+p).html(r);
			}
		);	})
});//]]></script>'.Eleanor::$Template->CloseTable()->Title($lang['cachem']);

		if($ck)
			$c.=Eleanor::$Template->Message($lang['cache_deleted'],'info');

		return$c.Eleanor::$Template->OpenTable().'<div class="blockcache">
		<div class="colomn"><div class="pad">'.$lang['cache_'].'<div class="submitline"><form method="post">'.Eleanor::Control('kill_cache','hidden','1').Eleanor::Button($lang['cachedel'],'submit',array('style'=>'button')).'</form></div></div></div>
			<div class="clr"></div></div>'.Eleanor::$Template->CloseTable();
	}

	/*
		������ �������� � ����������� � �������

		array $values �����:
			gd_info - ������ ���������� ���������� GD ���� false
			ini_get_v - �������� ������������ ���������
			ini_get - ������������� ���������
			os - ������������ �������, �� ������� �������� Eleanor CMS
			pms - Post max size
			ums - Upload max size
			ml - Memory limit
			met - Max execution time
			db - ������ MySQL
	*/
	public static function Server($values)
	{		static::Menu('server');
		$lang=Eleanor::$Language['sg'];		$gdver='';
		if($values['gd_info'])
			foreach($values['gd_info'] as $k=>&$v)				$gdver.=is_bool($v) ? '<li><b>'.$k.'</b>: '.($v ? '<span style="color:green">Yes</span>' : '<span style="color:green">No</span>').'</li>' : '<li><b>'.$k.'</b>: '.$v.'</li>';
		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item('OS',$values['os'])
			->item('PHP',PHP_VERSION)
			->item('GD',$gdver ? '<ul style="list-style-type:none">'.$gdver.'</ul>' : '&mdash;')
			->item('DB',$values['db'])
			->item('Post max size',$values['pms'])
			->item('Upload max size',$values['ums'])
			->item('Memory limit',$values['ml'])
			->item('Max execution time',$values['met'])
			->item('Max int',PHP_INT_MAX)
			->item($lang['get_value'],'<form method="post">'.Eleanor::Edit('ini_get',$values['ini_get']).Eleanor::Button('?').'</form>');
		if($values['ini_get_v'] or $values['ini_get'])
			$Lst->item(htmlspecialchars($values['ini_get'],ELENT,CHARSET),$values['ini_get_v'] ? htmlspecialchars($values['ini_get_v'],ELENT,CHARSET) : '&mdash;');
		return Eleanor::$Template->Cover(
			$Lst->button('<a href="'.$GLOBALS['Eleanor']->Url->Construct().'">'.Eleanor::$Language['tpl']['goback'].'</a>')->end()
		);
	}

	/*
		�������� ��������� ������ ���-������

		$logs ������, �������� ������ ���-������. ������ ������� ������� - ������ � �������:
			path - ������ ���� � ����� ������������ ����� �����
			descr - �������� �����
			size - ������ ����� � ������
			aview - ������ �� �������� �����
			adown - ������ �� ���������� �����
			adel - ������ �� �������� �����
		$size ������ � ������ �������� � ������
	*/
	public static function Logs($logs,$size)
	{		static::Menu('logs');
		$lang=Eleanor::$Language['sg'];
		$images=Eleanor::$Template->default['theme'].'images/';
		if($logs)
		{
			$Lst=Eleanor::LoadListTemplate('table-list',4)
				->begin($lang['file'],$lang['path'],$lang['size'],array(Eleanor::$Language['tpl']['functs'],70));

			foreach($logs as &$v)
				$Lst->item(
					$v['descr'],
					$v['path'],
					Files::BytesToSize('size'),
					$Lst('func',
						array($v['aview'],$lang['view_log'],$images.'viewfile.png'),
						array($v['adown'],$lang['download_log'],$images.'downloadfile.png'),
						array($v['adel'],$lang['delete_log'],$images.'delete.png')
					)
				);
			$Lst->end()->s.='<br />';
		}
		else
			$Lst=Eleanor::$Template->Message($lang['nologs'],'info');
		return Eleanor::$Template->Cover($Lst.($size>0 ? Eleanor::$Template->Message($lang['logs_size'].$size,'info') : ''));	}

	/*
		�������� ��������� ���-�����

		$text ����� ���-�����
		$links �������� ����������� ������, ������ � �������:
			adown - ������ �� ���������� �����
			adel - ������ �� �������� �����
	*/
	public static function ShowLog($text,$links)
	{		static::Menu('logs');
		$lang=Eleanor::$Language['sg'];
		$ltpl=Eleanor::$Language['tpl'];		return Eleanor::$Template->Cover('<p class="function"><a href="'.$links['adown'].'" title="'.$lang['download_log'].'"><img src="'.Eleanor::$Template->default['theme'].'images/downloadfile.png" alt="" /></a><a href="'.$links['adel'].'" title="'.$lang['delete_log'].'" onclick="return confirm(\''.$ltpl['are_you_sure'].'\')"><img src="'.Eleanor::$Template->default['theme'].'images/delete.png" alt="" /></a></p><div style="margin:15px;">'.Eleanor::Text('text',$text,array('style'=>'width:100%;','readonly'=>'readonly','rows'=>30)).'</div><div class="submitline">'.Eleanor::Button($ltpl['goback'],'button',array('onclick'=>'window.location=\''.$GLOBALS['Eleanor']->Url->Prefix().'\'')).'</div>');	}

	/*
		������� �������: �������. ���������� � �� AJAX

		$edt �������� ��������� �������� ��� ����������������� ������
		$edit ������� ����, ������������� ������� ��� ������������
	*/
	public static function Notes($edt,$edit=false)
	{		return'<div class="wbpad"><div class="brdbox">'.($edt ? $edt : '<div style="text-align:center;color:lightgray;font-size:1.5em">'.Eleanor::$Language['sg']['empty'].'</div>').'</div></div><div class="submitline">'.Eleanor::Button($edit ? 'OK' : Eleanor::$Language['tpl']['edit'],'button',$edit ? array('data-save'=>1) : array()).'</div>';
	}

	/*
		�������� �������� � �������

		$l �������� ����� ��������
		$s �������� ����� �������
	*/
	public static function License($l,$s)
	{		static::Menu('license');
		$lang=Eleanor::$Language['sg'];		return Eleanor::$Template->Title($lang['license'])
			->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$l.'</div><a href="addons/license/license-'.Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'.Eleanor::$Template->default['theme'].'images/print.png" alt="" /> '.$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable()
			.'<br />'
			.Eleanor::$Template->Title($lang['sanctions'])
			->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$s.'</div><a href="addons/license/sanctions-'.Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'.Eleanor::$Template->default['theme'].'images/print.png" alt="" /> '.$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable();	}}