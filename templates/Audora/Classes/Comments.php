<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������� ���������� �������������
*/
class TPLComments
{	public static
		$lang;
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],Eleanor::$Language['lc']['list'],'act'=>$act=='list',
				'submenu'=>$links['news']
					? array(
						array($links['news']['link'],static::$lang['news']($links['news']['cnt'])),
					)
					: false,
			),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);	}
	/*
		�������� ����������� ���� ������������
		$items - ������ ������������. ������: ID=>array(), ����� ����������� �������:
			module - id ������
			contid - ������-������������� ���������� ������
			status - ������ ����������� (-1 - �������� ���������, 0 - ������������, 1 - �������)
			date - ���� ���������� �����������
			author - ��� ������ �����������
			author_id - ID ������ �����������
			ip - ip ������ �����������
			text - ����� �����������
			_aswap - ������ �� �������������� ���������� �����������
			_aedit - ������ �� �������������� �����������
			_adel - ������ �� �������� �����������
		$modules - ������ �������. ������ id=>��������
		$titles - ������ ���������� � ������ �� �����������. ������: id=>array(), ����� ����������� �������:
			0 - ��������� ����������
			1 - ������ �� �����������
		$cnt - ���������� ������������ �����
		$pp - ���������� ������������ �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_date - ������ �� ���������� ������ $items �� ���� (�����������/�������� � ����������� �� ������� ����������)
			sort_author - ������ �� ���������� ������ $items �� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_module - ������ �� ���������� ������ $items �� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_ip - ������ �� ���������� ������ $items �� ip (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ������������ ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
		$ong - ���� ����������� ���������� �� ������� �������� �������
	*/
	public static function CommentsList($items,$modules,$titles,$cnt,$pp,$qs,$page,$links,$ong)
	{		if(!$ong)
			static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',$ong ? 6 : 7)
			->begin(
				array(static::$lang['date'],70,'sort'=>$qs['sort']=='date' ? $qs['so'] : false,'href'=>$links['sort_date']),
				array(static::$lang['author'],70,'sort'=>$qs['sort']=='author' ? $qs['so'] : false,'href'=>$links['sort_author']),
				array(static::$lang['published'],'sort'=>$qs['sort']=='module' ? $qs['so'] : false,'href'=>$links['sort_module']),
				array('IP',62,'sort'=>$qs['sort']=='ip' ? $qs['so'] : false,'href'=>$links['sort_ip']),
				array(static::$lang['text'],300),
				array($ltpl['functs'],60,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				$ong ? false : array(Eleanor::Check('mass',false,array('id'=>'mass-check')),10)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					Eleanor::$Language->Date($v['date'],'fdt'),
					$v['author_id'] ? '<a href="'.Eleanor::$Login->UserLink(htmlspecialchars_decode($v['author'],ELENT),$v['author_id']).'">'.$v['author'].'</a>' : $v['author'],
					isset($titles[$k]) ? '<a href="'.$titles[$k][1].'" target="_blank">'.$titles[$k][0].'</a>' : '',
					'<a href="http://eleanor-cms.ru/whois/'.$v['ip'].'">'.$v['ip'].'</a>',
					Strings::CutStr(strip_tags($v['text']),160),
					$Lst('func',
						array($v['_aswap'],$v['status']<=0 ? $ltpl['activate'] : $ltpl['deactivate'],$v['status']<0 ? $images.'waiting.png' : $images.($v['status']==0 ? 'inactive.png' : 'active.png')),
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					$ong ? false : Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty(static::$lang['cnf']);
		$Lst->end();
		if($ong)
			return$Lst;

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'module'=>false,
		);
		$omods=Eleanor::Option('&mdash;',0,false,array(),2);
		foreach($modules as $k=>&$v)
			$omods.=Eleanor::Option($v,$k,$k==$qs['']['fi']['module']);

		return Eleanor::$Template->Cover('<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td colspan="2"><a href="#">'.static::$lang['filter'].'</a></td></tr>
	<tr>
		<td><b>'.static::$lang['module'].'</b><br />'.Eleanor::Select('fi[module]',$omods).'</td>
		<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
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
</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
		.$Lst
		.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['cpp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k').Eleanor::Option($ltpl['active'],'a').Eleanor::Option($ltpl['inactive'],'d').Eleanor::Option(static::$lang['blocked'],'b')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page'])));
	}

	/*
		�������� �������������� �����������
		$id ������������� �������������� �����������
		$module �������� ������
		$info - ������ � ������� � ���������� �����������, �����:
			0 - �������� ����������
			1 - ������ �� �����������
		$values ������ �������� �����, �����:
			date - ���� ����������� (���������)
			author - ��� ������ ����������� (���������)
			author_id - ID ������ ����������� (���������)
			text - ����� �����������
			status - ������ ����������� (-1 - �������� ���������, 0 - �����������, 1 - �������)
		$bypost ���� �������� ������ �� POST �������
		$error ������, ���� ������ ������ - ������ �� ���
		$back URL ��������
		$links �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
	*/
	public static function Edit($id,$module,$info,$values,$bypost,$error,$back,$links)
	{		static::Menu();
		if($back)
			$back=Eleanor::Control('back','hidden',$back);
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form');
		return Eleanor::$Template->Cover($Lst->form()
			->begin()
			->item(static::$lang['module'],$module)
			->item(static::$lang['published'],'<a href="'.$info[1].'" target="_blank">'.$info[0].'</a>')
			->item(static::$lang['date'],Eleanor::$Language->Date($values['date'],'fdt'))
			->item(static::$lang['author'],$values['author_id'] ? '<a href="'.Eleanor::$Login->UserLink(htmlspecialchars_decode($values['author'],ELENT),$values['author_id']).'">'.$values['author'].'</a>' : $values['author'])
			->item(static::$lang['text'],$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost)))
			->item(static::$lang['status'],Eleanor::Select('status',Eleanor::Option($ltpl['activate'],1,$values['status']==1).Eleanor::Option($ltpl['deactivate'],0,$values['status']==0).Eleanor::Option($ltpl['waiting_act'],-1,$values['status']==-1)))
			->button($back.Eleanor::Button().' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')))
			->end()
			->endform(),$error);
	}

	/*
		�������� �������� �����������
		$t - ������ ���������� �����������, �����:
			text - ����� ���������� �����������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],Strings::CutStr(strip_tags($a['text']),200)),$back));
	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}
TplComments::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/comments-*.php',false);