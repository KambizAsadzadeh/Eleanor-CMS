<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������� ������ ����������� ������
*/
class TPLAdminCL
{	/*
		���� ������
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
		);
	}
	/*
		�������� ����������� ���� ����������� ������
		$items - ������ ����������� ������. ������: ID=>array(), ����� ����������� �������:
			date_from - ���� ������ ���������
			date_till - ���� ���������� ���������
			status - ������ ���������� ����������� ������
			from - �������� ������ �������������� ����������� ������ (��)
			to - ��������� �������������� ����������� ������ (�)
			_aswap - ������ �� ��������� / ���������� ���������� ����������� ������
			_aedit - ������ �� �������������� ����������� ������
			_adel - ������ �� �������� ����������� ������
		$cnt - ����� ������� ���� �����
		$pp - ���������� ����������� ������ �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			sort_from - ������ �� ���������� ������ $items �� ������ ��� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_to - ������ �� ���������� ������ $items �� ������ ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_date_from - ������ �� ���������� ������ $items �� ���� ������ �������������� (�����������/�������� � ����������� �� ������� ����������)
			sort_date_till - ������ �� ���������� ������ $items �� ���� ���������� �������������� (�����������/�������� � ����������� �� ������� ����������)
			sort_status - ������ �� ���������� ������ $items �� ������� ���������� (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ����������� ������ ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'from'=>false,
		);

		$l=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list',7)
			->begin(
				array('ID',15,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array($l['from'],'sort'=>$qs['sort']=='from' ? $qs['so'] : false,'href'=>$links['sort_from']),
				array($l['to'],'sort'=>$qs['sort']=='to' ? $qs['so'] : false,'href'=>$links['sort_to']),
				array($l['date_from'],'sort'=>$qs['sort']=='date_from' ? $qs['so'] : false,'href'=>$links['sort_date_from']),
				array($l['date_till'],'sort'=>$qs['sort']=='date_till' ? $qs['so'] : false,'href'=>$links['sort_date_till']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					array($k,'right'),
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.$v['from'].'</a>',
					$v['to'],
					array((int)$v['date_from']>0 ? $v['date_from'] : '&infin;','center'),
					array((int)$v['date_till']>0 ? $v['date_till'] : '&infin;','center'),
					$Lst('func',
						array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png'),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png','extra'=>array('onclick'=>'return confirm(\''.$ltpl['are_you_sure'].'\')'))
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty($l['not_found']);
		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Edit('fi[from]',$qs['']['fi']['from']).' '.Eleanor::Button($ltpl['apply']).'</td>
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
		.'<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($l['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		�������� ����������/�������������� ����������� ������
		$id - ������������� ������������� ���������� ������, ���� $id==0 ������ ����������� ������ �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML-��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$hasdraft - ������� ����, ��� � ����������� ������ ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$hasdraft,$links)
	{		static::Menu($id ? '' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if(Eleanor::$vars['multilang'])
		{
			$mchecks=array();
			foreach(Eleanor::$langs as $k=>&$_)
				$mchecks[$k]=!$id || !empty($values['title']['value'][$k]) || !empty($values['text']['value'][$k]) || !empty($values['url']['value'][$k]);
		}
		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,9));

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Control('_draft','hidden',$id)
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		if($errors)
		{			$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}
}