<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �������� �� ���������� ������� "�����" BB �����
*/
class TplOwnBB
{	/*
		���� ������
	*/	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['ownbb'];
		$links=&$GLOBALS['Eleanor']->module['links'];
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>$links['add']
				? array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				)
				: false,
			),
			array($links['recache'],$lang['update']),
		);
	}
	/*
		������ �������� �� ������� "�����" BB �����
		$items - ������, ���������� �������� "�����" BB �����. �����: ID=>array. ������ ������� ������� ��� ������ � �������:
			pos - �� 1 �� N �������� ������� ��������� ������� BB ����. ��� �������� ������� $items ������������� �� ����� ��������� �� ����������
			active - ���� ���������� (������������) BB ����.
			handler - ��� �����-����������� BB ����
			tags - ����� �����, ������� �������������� ������ BB ����� [tagname]...[/tagname]
			special - ���� ����, ��� ������ BB ��� �������� �����������, ������ �������������� ������ ������ ������ "�����" BB �����
			sp_tags - ����� �����, ������� �������������� ������ ������� BB ����
			_aedit - ������ �� �������������� "������" BB ����
			_adel - ������ �� �������� "������" BB ����
			_aup - ������ �� �������� "������" BB ���� �����, ���� ����� false - ������ "����" BB ��� ��� � ��� ��������� � ����� �����
			_adown - ������ �� ��������� "������" BB ���� ����, ���� ����� false - ������ "����" BB ��� ��� � ��� ��������� � ����� ����
	*/	public static function ShowList($items)
	{		static::Menu('list');		$lang=Eleanor::$Language['ownbb'];
		$ltpl=Eleanor::$Language['tpl'];
		$images=Eleanor::$Template->default['theme'].'images/';		$Lst=Eleanor::LoadListTemplate('table-list',5)->begin(
			array($lang['tags'],'title'=>$lang['tags']),
			array($lang['handler'],150),
			array($lang['special'],'title'=>$lang['special_'],100),
			array($lang['sp_tags'],'title'=>$lang['sp_tags_'],150),
			array($ltpl['functs'],110)
		);

		$cnt=count($items);
		foreach($items as $k=>&$v)
		{
			if($v['_aact'])
			{
				$extra=array('style'=>'color:red');
				$active='';
			}
			else
			{
				$extra=array();
				$active=$v['active'] ? array($v['_aact'],$ltpl['deactivate'],$images.'active.png') : array($v['_aact'],$ltpl['activate'],$images.'inactive.png');
			}

			$Lst->item(
				array($v['tags'],'href'=>$v['_aedit'])+$extra,
				$v['handler'],
				array(Eleanor::$Template->YesNo($v['special']),'center'),
				$v['sp_tags'],
				$Lst('func',
					$v['_aup'] ? array($v['_aup'],$ltpl['moveup'],$images.'up.png') : false,
					$v['_adown'] ? array($v['_adown'],$ltpl['movedown'],$images.'down.png') : false,
					$active,
					array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
					array($v['_adel'],$ltpl['delete'],$images.'delete.png')
				)
			);
		}

		if($cnt==0)
			$Lst->empty($lang['no_tags']);

		return Eleanor::$Template->Cover($Lst->end());	}

	/*
		������ �������� ���������� / ������ "������" BB ����
		$id - �� "������" BB ����, ������� ��������. ���� $id==0, ������ "����" BB ��� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� "������" BB ���� ��� false
		$back - ����� ��������, � ������� �� ������ ������� / ��������� �������. �� ��� �������� ����� �������� ������� ����� ����������
	*/
	public static function AddEdit($id,$controls,$values,$errors,$links,$back)
	{		static::Menu($id ? '' : 'add');		$Lst=Eleanor::LoadListTemplate('table-form')->begin();
		$tabs=array();
		$head=false;
		foreach($controls as $k=>&$v)
		{
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
			{
				if($head)
				{
					$tabs[]=array($head,(string)$Lst->end());
					$Lst->begin();
				}
				$head=$v;
			}
		}
		$tabs[]=array($head,(string)$Lst->end());

		if($back)
			$back=Eleanor::Control('back','hidden',$back);
		$Lst->form()->tabs($tabs)->submitline($back.Eleanor::Button().($id ? ' '.Eleanor::Button(Eleanor::$Language['tpl']['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))->endform();

		if($errors)
		{			$lang=Eleanor::$Language['ownbb'];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover((string)$Lst,$errors,'error');
	}

	/*
		������ �������� �������� ��������

		$a - ������ ���������� ������ BB ����, �����:
			tags - ����
		$back - ����� ��������, � ������� �� ������ ����� ������� �������. �� ��� �������� ����� �������� ������� ����� ��������
	*/
	public static function Delete($a,$back)
	{		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['ownbb']['deleting'],$a['tags']),$back));
	}
}