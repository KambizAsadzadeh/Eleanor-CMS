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
{	public static
		$lang;
	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],Eleanor::$Language['ser']['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
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
			->begin($lang['name'],$lang['file'],static::$lang['design'],$lang['login'],array($ltpl['functs'],80));

		$images=Eleanor::$Template->default['theme'].'images/';
		foreach($items as &$v)
			$Lst->item(
				array($v['name'],'href'=>$v['_aedit']),
				array($v['file'],'style'=>$v['protected'] ? 'font-weight:bold' : ''),
				$v['theme'] ? array($v['theme'],'center','href'=>$GLOBALS['Eleanor']->Url->file.'?'.$GLOBALS['Eleanor']->Url->Construct(array('section'=>'management','module'=>'themes_editor','files'=>$v['theme']),false),'hrefextra'=>array('title'=>static::$lang['etpl'])) : array('&mdash;','center'),
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
		$error - ������, ���� ������ ������ - ������ �� ���
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
	*/
	public static function AddEdit($name,$controls,$values,$errors,$back,$links)
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
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->button(
			$back.Eleanor::Button()
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error');
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
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['name']),$back));
	}
}
TplServices::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/services-*.php',false);