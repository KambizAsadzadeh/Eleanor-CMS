<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ������� ������� "����������" � "������" � �������
*/
class TPLManagement
{	/*
		������ �������� ������� "����������"

		$gen - �������� �������� ���������, ���������� �������. ������ array(m1,m2...). mN - ������ � �������:
			title - �������� ������
			descr - �������� ������
			image - ��������-������� ������. �������� ����� �������� � ���� *, ������� ���������� �������� �� big ��� small � ����������� �� �������
			_a - ������ �� ������ ������
		$add - �������� �������������� ��������� �������. ������ � ����� ��������� $gen
		$e - ������, ���� ������ ������ - ������ �� ����
	*/	public static function ManagCover($gen,$add,$e)
	{		$g=$a='';
		$n=3;
		$di='images/modules/default-big.png';
		foreach($gen as &$v)
		{			$img=$di;
			if($v['image'])
			{				$v['image']='images/modules/'.str_replace('*','big',$v['image']);
				if(is_file(Eleanor::$root.$v['image']))
					$img=$v['image'];
			}
			if($n==0)
				$n=3;
			$g.=(--$n==2 ? '<tr>' : '')
				.'<td><a href="'.$v['_a'].'" title="'.$v['title'].'" class="mod_card"><span class="card_inn"><span><img src="'.$img.'" alt="" /><b>'.$v['title'].'</b><br /><i>'.$v['descr'].'</i></span></span></a></td>'
				.($n==0 ? '</tr>' : '');
		}
		if($n>0)
		{
			while($n-->0)
				$g.='<td></td>';
			$g.='</tr>';
		}

		$n=3;
		foreach($add as &$v)
		{
			$img=$di;
			if($v['image'])
			{
				$v['image']='images/modules/'.str_replace('*','big',$v['image']);
				if(is_file(Eleanor::$root.$v['image']))
					$img=$v['image'];
			}
			if($n==0)
				$n=3;
			$a.=(--$n==2 ? '<tr>' : '')
				.'<td><a href="'.$v['_a'].'" title="'.$v['title'].'" class="mod_card"><span class="card_inn"><span><img src="'.$img.'" alt="" /><b>'.$v['title'].'</b><br /><i>'.$v['descr'].'</i></span></span></a></td>'
				.($n==0 ? '</tr>' : '');
		}
		if($n>0)
		{
			while($n-->0)
				$a.='<td></td>';
			$a.='</tr>';
		}
		$l=Eleanor::$Language['tpl'];
		return ($e ? Eleanor::$Template->Message($e) : '')
			.Eleanor::$Template->Title($l['main_m']).'<table class="tablecard">'.$g.'</table>'
			.Eleanor::$Template->Title($l['addon_m']).'<table class="tablecard">'.$a.'</table>';
	}

	/*
		������ �������� ������� "������"

		$mo - ������ �������. ������ array(m1,m2...). mN - ������ � �������:
			title - �������� ������
			descr - �������� ������
			image - ��������-������� ������. �������� ����� �������� � ���� *, ������� ���������� �������� �� big ��� small � ����������� �� �������
			active - ���� ���������� ������
			_a - ������ �� ������ ������
		$e - ������, ���� ������ ������ - ������ �� ����
	*/
	public static function ModulesCover($mo,$e)
	{
		$g='';
		$n=3;
		$di='images/modules/default-big.png';
		foreach($mo as &$v)
		{
			$img=$di;
			if($v['image'])
			{
				$v['image']='images/modules/'.str_replace('*','big',$v['image']);
				if(is_file(Eleanor::$root.$v['image']))
					$img=$v['image'];
			}
			if($n==0)
				$n=3;
			$s='<span class="card_inn"><span><img src="'.$img.'" alt="" /><b>'.$v['title'].'</b><br /><i>'.$v['descr'].'</i></span></span>';
			$g.=(--$n==2 ? '<tr>' : '')
				.'<td>'.($v['active'] ? '<a href="'.$v['_a'].'" title="'.$v['title'].'" class="mod_card">'.$s.'</a>' : '<div title="'.$v['title'].'" class="mod_card">'.$s.'</div>').'</td>'
				.($n==0 ? '</tr>' : '');
		}
		if($n>0)
		{
			while($n-->0)
				$g.='<td></td>';
			$g.='</tr>';
		}
		return($e ? Eleanor::$Template->Message($e) : '')
			.'<table class="tablecard">'.$g.'</table>';
	}
}