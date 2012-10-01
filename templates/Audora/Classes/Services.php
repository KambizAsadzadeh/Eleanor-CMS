<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ��������
*/
class TPLServices
{	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['ser'];
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
		�������� ����������� ���� ��������
		$items - ������ sitemap-��. ������: >array(array(),array()...), ����� ���������� ��������:
			file - ��� ����� �������
			login - ��� ������ �������
			name - �������� �������
			protected - ���� ������������ �������
			theme - ���� ����������, ������������ � ������� �� ���������
			_aedit - ������ �� �������������� �������
			_adel - ������ �� �������� �������
	*/
	public static function Services($items)
	{		static::Menu('list');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['ser'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin($lang['name'],$lang['file'],$lang['design'],$lang['login'],array($ltpl['functs'],80));

		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($items as &$v)
			$Lst->item(
				array($v['name'],'href'=>$v['_aedit']),
				array($v['file'],'style'=>$v['protected'] ? 'font-weight:bold' : ''),
				$v['theme'] ? array($v['theme'],'center','href'=>$GLOBALS['Eleanor']->Url->file.'?'.$GLOBALS['Eleanor']->Url->Construct(array('section'=>'management','module'=>'themes_editor','files'=>$v['theme']),false),'hrefaddon'=>array('title'=>$lang['etpl'])) : array('&mdash;','center'),
				array($v['login'],'center'),
				$Lst('func',
					$v['protected'] ? false : array($v['_adel'],$ltpl['delete'],$images.'delete.png'),
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png')
				)
			);
		$Lst->end();
		return Eleanor::$Template->Cover((string)$Lst);
	}

	/*
		������ ��������/�������������� �������
		$name - ������������� �������������� �������, ���� $name==false ������ ������ �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$error - ������, ���� ������ ������ - ������ �� ����
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
	*/
	public static function AddEdit($name,$controls,$values,$error,$back,$links)
	{
		static::Menu($name ? '' : 'add');
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();

		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$Lst->button(
			$back.Eleanor::Button()
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		return Eleanor::$Template->Cover($Lst,$error,'error');
	}


	/*
		�������� �������� �������
		$a - ������ ���������� �������, �����:
			name - �������� �������
			file - ���� �������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['ser']['deleting'],$a['name']),$back));
	}
}