<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(!defined('CMS'))die;
return array(
	'service'=>array('user'),#������ ��� �������������
	'creation'=>'2012-27-06',#���� ��������
	'author'=>'Eleanor CMS team',#����� �������
	'name'=>'����������� ������ ������ ������',#�������� �������
	'info'=><<<INFO
	����������
INFO
,
	'license'=><<<LICENSE
����������� ���� ���������� ���������������� �����. ������������ �� � ������� Eleanor CMS ����� ���������. ������������ ���� ������ ��� ��� ����� � ��������� ����������� - ������ ���������!
LICENSE
,#��������
	'options'=>array(#����� � ������������� ����
		'eleanor'=>array(
			'title'=>'���������� ������� Eleanor CMS',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'extra'=>array('tabindex'=>1),
			),
		),
		'downtags'=>array(
			'title'=>'���������� ������ ���� �����',
			'descr'=>'',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'extra'=>array('tabindex'=>2),
			),
		),
	)
);