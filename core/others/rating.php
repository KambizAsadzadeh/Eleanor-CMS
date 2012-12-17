<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class Rating extends BaseClass
{	/**
	 * ���������� ������ �������� �������� ��� ���������� ������
	 *
	 * @param int $total ���������� ���������������
	 * @param float $average ������� ������
	 * @param int $mark ����������� ������
	 */	public static function AddMark($total,$average,$mark)
	{		return round((ceil($average*$total)+$mark)/++$total,2);	}

	/**
	 * ���������� ������ �������� �������� ��� �������� ������
	 *
	 * @param int $total ���������� ���������������
	 * @param float $average ������� ������
	 * @param int $mark ��������� ������
	 */
	public static function SubMark($total,$average,$mark)
	{
		return round((ceil($average*$total)-$mark)/--$total,2);
	}

	/**
	 * ���������� ������ �������� �������� ��� ��������� ������
	 *
	 * @param int $total ���������� ���������������
	 * @param float $average ������� ������
	 * @param int $oldmark ������ ������
	 * @param int $newmark ����� ������
	 */
	public static function ChangeMark($total,$average,$oldmark,$newmark)
	{
		return round((ceil($average*$total)-$oldmark+$newmark)/$total,2);
	}
}