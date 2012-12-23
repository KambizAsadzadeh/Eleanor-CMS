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
	'creation'=>'2009-03-07',#���� ��������
	'author'=>'Eleanor CMS team',#����� �������
	'name'=>'����������� ������',#�������� �������
	'info'=><<<INFO
	����������
INFO
,
	'license'=><<<'LICENSE'
����������� ���� ���������� ���������������� �����. ������������ �� � ������� Eleanor CMS ����� ���������. ������������ ���� ������ ��� ��� ����� � ��������� ����������� - ������ ���������!
LICENSE
,#��������

	#����� ������
	'places'=>array(
		'left'=>array(
			'title'=>array(
				'russian'=>'����� �����',
				'english'=>'Left blocks',
				'ukrainian'=>'˳� �����',
			),
			'extra'=>'50,108,184,270,1',
		),
		'right'=>array(
			'title'=>array(
				'russian'=>'������ �����',
				'english'=>'Right blocks',
				'ukrainian'=>'���� �����',
			),
			'extra'=>'416,107,182,270,2',
		),
		'center_up'=>array(
			'title'=>array(
				'russian'=>'������� �����������',
				'english'=>'Up central',
				'ukrainian'=>'����� ���������',
			),
			'extra'=>'50,0,548,101,3',
		),
		'center_down'=>array(
			'title'=>array(
				'russian'=>'������ �����������',
				'english'=>'Down central',
				'ukrainian'=>'���� ���������',
			),
			'extra'=>'50,393,548,101,4',
		),
	),

	'options'=>array(#����� � ������������� ����
		'������ 1: ��������� ����',
		'param1'=>array(
			'title'=>'������',
			'descr'=>'�������� ������',
			'default'=>'�������� ��-���������',
			'type'=>'input',
			'options'=>array(
				'extra'=>array('tabindex'=>1)
			),
		),
		'param2'=>array(
			'title'=>'��������� ����',
			'descr'=>'�������� ���������� ����',
			'default'=>'�������� ��-���������',
			'type'=>'text',
			'options'=>array(
				'extra'=>array('tabindex'=>2),
			),
		),
		'param3'=>array(
			'title'=>'��������� ��������',
			'descr'=>'�������� ���������� ���������',
			'default'=>'�������� ��-���������',
			'type'=>'editor',
			'extra'=>array(
				'no'=>array('tabindex'=>3),
			)
		),
		'������ 2: select-�',
		'param4'=>array(
			'title'=>'�����1',
			'descr'=>'�������� ������ 1',
			'default'=>1,
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'extra'=>array('tabindex'=>4),
			),
			'type'=>'select',
		),
		'param5'=>array(
			'title'=>'�����2',
			'descr'=>'�������� ������ 2',
			'default'=>2,
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'extra'=>array('tabindex'=>5),
			),
			'type'=>'item',
		),
		'param6'=>array(
			'title'=>'������������� �����',
			'descr'=>'�������� �������������� ������',
			'default'=>array(0,1,2),
			'options'=>array(
				'options'=>array(1,2,3,4,5),
				'extra'=>array('tabindex'=>6),
			),
			'type'=>'items',
		),
		'������ 3: Chechbox',
		'param8'=>array(
			'title'=>'Checkbox',
			'descr'=>'�������� checkbox-a',
			'default'=>true,
			'type'=>'check',
			'options'=>array(
				'extra'=>array('tabindex'=>7),
			),
		),
		'param9'=>array(
			'title'=>'Checkboxes',
			'descr'=>'�������� �������������� ������',
			'default'=>array(0,1,2),
			'options'=>array(
				'options'=>array(1,2,3,4,5),
			),
			'type'=>'checks',
		),
		'������ 4: ��� ������',
		'param10'=>array(
			'title'=>'��� ������',
			'descr'=>'�������� ���� ������',
			'options'=>array(
				'content'=>'��� ������ ����� ����� ���������',
			),
			'type'=>'',
		),
	)
);