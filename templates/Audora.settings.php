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
	'service'=>array('admin'),#������ ��� �������
	'creation'=>'2009-12-29',#���� ��������
	'author'=>'Eleanor CMS team',#����� �������
	'name'=>'����������� ������ ������ ��������������',#�������� �������
	'info'=><<<INFO
	����������
INFO
,
	'license'=><<<'LICENSE'
����������� ���� ���������� ������ ��������������. ������������ �� � ������� Eleanor CMS ����� ���������. ������������ ���� ������ ��� ��� ����� � ��������� ����������� - ������ ���������!
LICENSE
,#��������

	#����� ������
	'places'=>array(
		'right'=>array(
			'title'=>array(
				'russian'=>'������ �����',
				'english'=>'Right blocks',
				'ukrainian'=>'���� �����',
			),
			'info'=>'276,10,160,229,0',
		),
	),

	'options'=>array(
		'sizethm'=>array(
			'title'=>'��� ������� ����������',
			'descr'=>'�� ���������: ���������',
			'default'=>1,
			'options'=>array(
				'options'=>array('r'=>'���������','f'=>'�������������'),
				'tabindex'=>1,
			),
			'type'=>'select',
		),
		'colorbg'=>array(
			'title'=>'���� ����',
			'descr'=>'�� ���������: #2d2f30',
			'default'=>'#2d2f30',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>2,
			),
		),
		'imagebg'=>array(
			'title'=>'������� �����������',
			'descr'=>'�� ���������: templates/Audora/images/pagebg.png',
			'default'=>'templates/Audora/images/pagebg.png',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>3,
			),
		),
		'positionimg'=>array(
			'title'=>'������� �������� �����������',
			'descr'=>'� ������� X Y, ��� X - ������� �� ��� x, Y - ������� �� ��� y. ��������: 50% 5px. �� ���������: 0 0.',
			'default'=>'0 0',
			'type'=>'input',
			'options'=>array(
				'tabindex'=>4,
			),
		),
		'bgattachment'=>array(
			'title'=>'��������� �������� �����������',
			'descr'=>'�� ���������: ���������',
			'default'=>false,
			'type'=>'check',
			'options'=>array(
				'tabindex'=>5,
			),
		),
		'bgrepeat'=>array(
			'title'=>'������ �������� �����������',
			'descr'=>'�� ���������: �� �����������',
			'default'=>'repeat-x',
			'options'=>array(
				'options'=>array('repeat'=>'�� ����������� � ���������','repeat-x'=>'�� �����������','repeat-y'=>'�� ���������','no-repeat'=>'�� ���������'),
				'tabindex'=>6,
			),
			'type'=>'select',
		),
	),
);