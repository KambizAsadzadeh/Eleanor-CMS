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
return array(
	'n'=>'news',#������
	't'=>P.'news',#��� ������� � ���������
	'tl'=>P.'news_l',#��� ������� � �������� ���������
	'tt'=>P.'news_tags',#��� ������� � ������
	'rt'=>P.'news_rt',#��� ������� � ������ => ��������� (Related tags)
	'c'=>P.'news_categories',#��� ������� � �����������
	'admintpl'=>'AdminNews',#����� ������������������ ����������
	'usertpl'=>'UserNews',#����� ����������������� ����������
	'usercorrecttpl'=>'UserNewsCorrect',#����� ����������������� ����������
	'opts'=>'module_news',#�������� ������ �����
	'pv'=>'m_news_',#������� ��������
	'api'=>'ApiNews',#�������� ������
	'secret'=>crc32(__file__),#��� ������� ��������, ����������� �������
);