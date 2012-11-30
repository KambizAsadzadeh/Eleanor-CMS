<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ���������� �����������
*/
class TplCategoriesManager
{	public static
		$lang;
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links_categories'];		$GLOBALS['Eleanor']->module['navigation']=array(
			'categories'=>array($links['list'],static::$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				),
			),
		);
	}
	/*
		�������� ����������� ���� ���������
		$items - ������ ���������. ������: ID=>array(), ����� ����������� �������:
			title - �������� ���������
			image - ���� � ��������-�������� ���������, ���� ������ - ������ �������� ���
			pos - ����� �����, ��������������� ������� ���������
			_aedit - ������ �� �������������� ���������
			_adel - ������ �� �������� ���������
			_aparent - ������ �� �������� ������������ ������� ���������
			_aup - ������ �� �������� ��������� �����, ���� ����� false - ������ ��������� ��� � ��� ��������� � ����� �����
			_adown - ������ �� ��������� ��������� ����, ���� ����� false - ������ ��������� ��� � ��� ��������� � ����� ����
			_aaddp - ������ �� ���������� ������������ � ������ ���������
		$subitems - ������ ������������ ��� ������� �� ������� $items. ������: ID=>array(id=>array(), ...), ��� ID - ������������� ����������� ��������, id - ������������� ���������. ����� ������� ������������:
			title - ��������� ���������
			_aedit - ������ �� �������������� ������������
		$navi - ������, ������� ������ ���������. ������ ID=>array(), �����:
			title - ��������� ������
			_a - ������ �� ��������� ������ ������. ����� ���� ����� false
		$cnt - ���������� ����������� ������� �����
		$pp - ���������� ����������� ������� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_title - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_pos - ������ �� ���������� ������ $items �� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ��������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function CMList($items,$subitems,$navi,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$ltpl=Eleanor::$Language['tpl'];
		$nav=array();
		foreach($navi as &$v)
			$nav[]=$v['_a'] ? '<a href="'.$v['_a'].'">'.$v['title'].'</a>' : $v['title'];

		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(
				array($ltpl['title'],'href'=>$links['sort_title'],'colspan'=>2),
				array(static::$lang['pos'],80,'href'=>$links['sort_pos']),
				array($ltpl['functs'],80,'href'=>$links['sort_id'])
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';

			$posasc=!$qs['sort'] || $qs['sort']=='pos' && $qs['so']=='asc';
			foreach($items as $k=>&$v)
			{
				$subs='';
				if(isset($subitems[$k]))
					foreach($subitems[$k] as $kk=>&$vv)
						$subs.='<a href="'.$vv['_aedit'].'">'.$vv['title'].'</a>, ';

				$Lst->item(
					$v['image'] ? array('<a href="'.$v['_aedit'].'"><img src="'.$v['image'].'" /></a>','style'=>'width:1px') : false,
					array('<a id="cat'.$k.'" href="'.$v['_aedit'].'">'.$v['title'].'</a><br /><span class="small"><a href="'.$v['_aparent'].'" style="font-weight:bold">'.static::$lang['subitems'].'</a> '.rtrim($subs,', ').' <a href="'.$v['_aaddp'].'" title="'.static::$lang['addsubitem'].'"><img src="'.$images.'plus.gif'.'" /></a></span>','colspan'=>$v['image'] ? false : 2),
					$posasc
						? $Lst('func',
							$v['_aup'] ? array($v['_aup'],static::$lang['up'],$images.'up.png') : false,
							$v['_adown'] ? array($v['_adown'],static::$lang['down'],$images.'down.png') : false
						)
						: array('&empty;','center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					)
				);
			}
		}
		else
			$Lst->empty(static::$lang['no']);
		return Eleanor::$Template->Cover(
			($nav ? '<table class="filtertable"><tr><td style="font-weight:bold">'.join(' &raquo; ',$nav).'</td></tr></table>' : '')
			.'<form action="'.$links['form_items'].'" method="post">'
			.$Lst->end().'<div class="submitline" style="text-align:left">'.sprintf(static::$lang['to_pages'],$Lst->perpage($pp,$links['pp'])).'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		�������� ����������/�������������� ���������
		$id - ������������� ������������� ���������, ���� $id==0 ������ ��������� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ��������� (��� ������� ��������)
	*/
	public static function CMAddEdit($id,$controls,$values,$errors,$back,$links)
	{		static::Menu($id ? 'edit' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if($values[$k])
				if(is_array($v))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
				else
					$Lst->head($v);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,9));

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Control('_draft','hidden',$id)
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($links['nodraft'] ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		�������� �������� ����������� ��������
		$a - ������ ��������� ���������, �����:
			title - �������� ���������
		$back - URL ��������
		$error - ������, ���� ������ ������ - ������ �� ���
	*/
	public static function CMDelete($a,$back,$error)
	{		static::Menu();		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['title']),$back),$error);	}
}
TplCategoriesManager::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/categories_manager-*.php',false);