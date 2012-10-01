<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� �����������
*/
class TPLMultisite
{	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['ms'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['main'],$lang['conf'],'act'=>$act=='main'),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);

	}
	/*
		������ �������������� �����������
		$sites - ������ id=>�������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$error - ������, ���� ������ ������ - ������ �� ����
	*/
	public static function Multisite($sites,$controls,$error)
	{		static::Menu('main');		$GLOBALS['jscripts'][]='js/multisite_manager.js';
		$lang=Eleanor::$Language['ms'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form(array('id'=>'multisite'));

		foreach($sites as $sn=>&$site)
		{
			$Lst->begin();
			foreach($controls as $k=>&$v)
			{
				if(is_array($v))
					$Lst->item(array($v['title'].$cl,Eleanor::$Template->LangEdit($site[$k],null),'tip'=>$v['descr'],'imp'=>$v['imp']));
				else
				{
					switch($v)
					{						case'site':
							$h=$lang['sgd'].' <a href="#" class="delsite">'.$lang['dels'].'</a>';
						break;
						default:
							$h=$lang['dbt'].' <a href="#" class="checkdb">'.$lang['chdb'].'</a>';					}
					$Lst->head($h);
				}
				$cl='';
			}
			$Lst->end();
		}
		$Lst->submitline(Eleanor::Button($lang['addsite'],'button',array('class'=>'addsite')).' '.Eleanor::Button($lang['saveconf']))->endform();
		return Eleanor::$Template->Cover((string)$Lst,$error,'error');
	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}