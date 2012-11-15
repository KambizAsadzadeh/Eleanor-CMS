<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ��������� �����
*/
class TPLTasks
{	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['tasks'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>$links['add']
					? array(
						array($links['add'],$lang['add'],'act'=>$act=='add'),
					)
					: false,
			),
		);
	}
	/*
		�������� ����������� ���� �����
		$items - ������ ����� ������: ID=>array(), ����� ����������� �������:
			task - ����-���������� ������
			title - �������� ������
			free - ���� ������������� �������� �������� ������. ����� �������� ������� ����� ����� 1, ������ � ���� ������ ���������� ���������� ������
			lastrun - ����� ���������� ������� ������
			nextrun - ����� ���������� ������� ������
			run_year - ��� ������� ������
			run_month - ����� ������� ������
			run_day - ���� ������� ������
			run_hour - ��� ������� ������
			run_minute - ������ ������� ������
			run_second - ������� ������� ������
			status - ������ ���������� ������
			_aedit - ������ �� �������������� ������ ��� false
			_adel - ������ �� �������� ������

		$cnt - ���������� ����� �����
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$pp - ���������� ����� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$links - �������� ����������� ������, ������ � �������:
			sort_nextrun - ������ �� ���������� ������ $items �� ���� ���������� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_task - ������ �� ���������� ������ $items �� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_free - ������ �� ���������� ������ $items �� ����� ������������� (�����������/�������� � ����������� �� ������� ����������)
			sort_status - ������ �� ���������� ������ $items �� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			pp - �������-��������� ������ �� ��������� ���������� ����� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$cnt,$page,$pp,$qs,$links)
	{		static::Menu('list');
		$lang=Eleanor::$Language['tasks'];
		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',9)
			->begin(
				array($ltpl['name'],'sort'=>$qs['sort']=='task' ? $qs['so'] : false,'href'=>$links['sort_task']),
				array($lang['nextrun'],'sort'=>$qs['sort']=='nextrun' ? $qs['so'] : false,'href'=>$links['sort_nextrun']),
				$lang['runyear'],
				$lang['runmonth'],
				$lang['runday'],
				$lang['runhour'],
				$lang['runminute'],
				$lang['runsecond'],
				array($ltpl['functs'],'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id'])
			);

		$images=Eleanor::$Template->default['theme'].'images/';
		if($items)
			foreach($items as $k=>&$v)
				$Lst->item(
					array($v['title'],'href'=>$v['_aedit']),
					array($v['free'] ? Eleanor::$Language->Date($v['nextrun']) : $lang['now'],'center'),
					array($v['run_year'],'center'),
					array($v['run_month'],'center'),
					array($v['run_day'],'center'),
					array($v['run_hour'],'center'),
					array($v['run_minute'],'center'),
					array($v['run_second'],'center'),
					$Lst('func',
						array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png'),
						$v['_aedit'] ? array($v['_aedit'],$ltpl['edit'],$images.'edit.png') : false,
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					)
				);
		else
			$Lst->empty($lang['notasks']);

		return Eleanor::$Template->Cover(
			$Lst->end()
			.'<div class="submitline" style="text-align:left">'.sprintf($lang['tpp'],$Lst->perpage($pp,$links['pp'])).'</div>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		�������� ����������/�������������� ������
		$id - ������������� ������������� ������, ���� $id==0 ������ ������ �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML-��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
	*/
	public static function AddEdit($id,$controls,$values,$errors,$back,$links)
	{		static::Menu($id ? '' : 'add');
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
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->end()->endform();

		if($errors)
		{			$lang=Eleanor::$Language['tasks'];
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		}
		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		�������� �������� ������
		$a - ������ ���������� �������, �����:
			title - �������� �������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['tasks']['deleting'],$a['title']),$back));
	}
}