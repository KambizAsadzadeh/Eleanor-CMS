<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ���������� ������ ����������� �������
*/
class TPLAdminStatic
{	/*
		���� ������
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$options=isset($GLOBALS['Eleanor']->module['navigation']['options']) ? $GLOBALS['Eleanor']->module['navigation']['options'] : false;		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['parent_add'] ? $links['parent_add'] : $links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['files'],$lang['fp'],'act'=>$act=='files'),
			$options ? $options : array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);	}
	/*
		�������� ����������� ���� ����������� �������
		$items - ������ ����������� �������. ������: ID=>array(), ����� ����������� �������:
			title - ��������� ����������� ��������
			pos - ����� �����, ��������������� ������� ����������� ��������
			status - ������ ���������� ����������� ��������
			_aswap - ������ �� ��������� / ���������� ���������� ����������� ��������
			_aedit - ������ �� �������������� ����������� ��������
			_adel - ������ �� �������� ����������� ��������
			_aparent - ������ �� �������� ���������� ������� ����������� ��������
			_aup - ������ �� �������� ����������� �������� �����, ���� ����� false - ������ ����������� �������� ��� � ��� ��������� � ����� �����
			_adown - ������ �� ��������� ����������� �������� ����, ���� ����� false - ������ ����������� �������� ��� � ��� ��������� � ����� ����
			_aaddp - ������ �� ���������� ���������� � ������ ��������
		$subitems - ������ ����������� ���������� ��� ������� �� ������� $items. ������: ID=>array(id=>array(), ...), ��� ID - ������������� ����������� ��������, id - ������������� ����������� �����������. ����� ������� ����������� �����������:
			title - ��������� ����������� ��������
			_aedit - ������ �� �������������� ����������� ��������
		$navi - ������, ������� ������ ���������. ������ ID=>array(), �����:
			title - ��������� ������
			_a - ������ ��������� ������ ������. ����� ���� ����� false
		$cnt - ���������� ����������� ������� �����
		$pp - ���������� ����������� ������� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_status - ������ �� ���������� ������ $items �� ������� ���������� (�����������/�������� � ����������� �� ������� ����������)
			sort_title - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_pos - ������ �� ���������� ������ $items �� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ����������� ������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

		$nav=array();
		foreach($navi as &$v)
			$nav[]=$v['_a'] ? '<a href="'.$v['_a'].'">'.$v['title'].'</a>' : $v['title'];

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'title'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin(
				array('ID',15,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array($ltpl['title'],'sort'=>$qs['sort']=='title' ? $qs['so'] : false,'href'=>$links['sort_title']),
				array($lang['pos'],80,'sort'=>$qs['sort']=='pos' ? $qs['so'] : false,'href'=>$links['sort_pos']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';

			$posasc=!$qs['sort'] || $qs['sort']=='pos' && $qs['so']=='asc';
			foreach($items as $k=>&$v)
			{				$subs='';
				if(isset($subitems[$k]))
					foreach($subitems[$k] as $kk=>&$vv)
						$subs.='<a href="'.$vv['_aedit'].'">'.$vv['title'].'</a>, ';

				$Lst->item(
					array($k,'right'),
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.$v['title'].'</a><br /><span class="small"><a href="'.$v['_aparent'].'" style="font-weight:bold">'.$lang['subpages'].'</a> '.rtrim($subs,', ').' <a href="'.$v['_aaddp'].'" title="'.$lang['addsubpage'].'"><img src="'.$images.'plus.gif" alt="" /></a></span>',
					$posasc
						? $Lst('func',
							$v['_aup'] ? array($v['_aup'],$ltpl['moveup'],$images.'up.png') : false,
							$v['_adown'] ? array($v['_adown'],$ltpl['movedown'],$images.'down.png') : false
							)
						: array('&empty;','center'),
					$Lst('func',
						array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png'),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty($lang['not_found']);

		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Edit('fi[title]',$qs['']['fi']['title']).' '.Eleanor::Button($ltpl['apply']).'</td>
	</tr>
</table>
<script type="text/javascript">//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
});//]]></script>
		</form>'
			.($nav ? '<table class="filtertable"><tr><td style="font-weight:bold">'.join(' &raquo; ',$nav).'</td></tr></table>' : '')
			.'<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end()
			.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		�������� ����������/�������������� ����������� ��������
		$id - ������������� ������������� ��������, ���� $id==0 ������ �������� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$uploader - ��������� ���������� ������
		$hasdraft - ������� ������� ���������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$uploader,$hasdraft,$links)
	{		static::Menu($id ? '' : 'add');		$ltpl=Eleanor::$Language['tpl'];		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,9));

		$Lst->s.='<tr><td colspan="2">'.$uploader.'</td></tr>';

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Control('_draft','hidden',$id)
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		�������� �������������� ������.
		$files - Uploader
	*/
	public static function Files($files)
	{
		static::Menu('files');
		return Eleanor::$Template->Cover($files).'<script type="text/javascript">//<![CDATA[
$(function(){
	$("#showb-").hide().click().parent().remove();
	FI.Open=function(url)
	{		url=encodeURIComponent(FI.Get("realpath").replace(/^.+?DIRECT\//,"")+url).replace(/!/g,"%21").replace(/\'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A").replace(/%20/g,"+")
		window.open(window.location.protocol+"//"+window.location.hostname+CORE.site_path+"'.Eleanor::$services['download']['file'].'?'.Url::Query(array('module'=>$GLOBALS['Eleanor']->module['name']),array('delim'=>'&')).'&f="+url);
		return false;
	}
})//]]></script>';
	}

	/*
		�������� �������� ����������� ��������
		$a - ������ � ����������� � ����������� ��������, �����
			title - �������� ����������� ��������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['deleting'],$a['title']),$back));	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}