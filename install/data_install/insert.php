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
$insert[]='SET FOREIGN_KEY_CHECKS=0;';
$domain=Eleanor::$domain;
$rus=in_array('russian',$languages);
$eng=in_array('english',$languages);
$ukr=in_array('ukrainian',$languages);

$insert['blocks']=<<<QUERY
INSERT INTO `{$prefix}blocks` (`id`, `ctype`, `file`, `user_groups`, `showfrom`, `showto`, `textfile`, `template`, `notemplate`, `vars`, `status`) VALUES
(1, 'file', 'addons/blocks/block_who_online.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(2, 'file', 'addons/blocks/block_tags_cloud.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(3, 'file', 'addons/blocks/block_archive.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(4, 'file', 'modules/news/block_lastvoting.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(5, 'file', 'addons/blocks/block_menu_single.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, 'a:1:{s:6:"parent";i:7;}', 1),
(6, 'file', 'modules/news/block_similar.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1),
(7, 'file', 'addons/blocks/block_themesel.php', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 0, '', 0, '', 1)
QUERY;

#Russian
if($rus)
	$insert['blocks_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`) VALUES
(1, 'russian', '��� ������'),
(2, 'russian', '������ �����'),
(3, 'russian', '�����'),
(4, 'russian', '�����'),
(5, 'russian', '������������ ����'),
(6, 'russian', '�� ����'),
(7, 'russian', '����� �������')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['blocks_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`) VALUES
(1, 'english', 'Who online'),
(2, 'english', 'Tags cloud'),
(3, 'english', 'Archive'),
(4, 'english', 'Voting'),
(5, 'english', 'Vertical menu'),
(6, 'english', 'By topic'),
(7, 'english', 'Select template')
QUERY;
#[E]English

#Ukrainian
if($ukr)
	$insert['blocks_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}blocks_l` (`id`, `language`, `title`) VALUES
(1, 'ukrainian', '��� ������'),
(2, 'ukrainian', '������ ����'),
(3, 'ukrainian', '�����'),
(4, 'ukrainian', '����������'),
(5, 'ukrainian', '����������� ����'),
(6, 'ukrainian', '�� ���'),
(7, 'ukrainian', '���� �������')
QUERY;
#[E]Ukrainian

$ser=array(
	1=>serialize(array(
		'russian'=>'�������',
		'english'=>'News',
		'ukrainian'=>'������',
	)),
	serialize(array(
		'russian'=>'������� ��������',
		'english'=>'Mainpage',
		'ukrainian'=>'������� �������',
	)),
);

$insert['blocks_ids']=<<<QUERY
INSERT INTO `{$prefix}blocks_ids` (`id`,`service`,`title_l`,`code`) VALUES
(1, 'user', '{$ser[1]}', 'return isset(\$GLOBALS[''Eleanor'']->module[''section'']) && !isset(\$GLOBALS[''Eleanor'']->module[''general'']) && \$GLOBALS[''Eleanor'']->module[''section'']==''news'';'),
(2, 'user', '{$ser[2]}', 'return isset(\$GLOBALS[''Eleanor'']->module[''general'']);')
QUERY;

$ser=array(
	'admin'=>serialize(array(
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
		'blocks'=>array(
			'right'=>array(1),
		),
		'addon'=>array('verhor'=>''),
	)),
	'user'=>serialize(array(
		'places'=>array(
			'left'=>array(
				'title'=>array(
					'russian'=>'����� �����',
					'english'=>'Left blocks',
					'ukrainian'=>'˳� �����',
				),
				'info'=>'50,30,184,242,1',
			),
			'right'=>array(
				'title'=>array(
					'russian'=>'������ �����',
					'english'=>'Right blocks',
					'ukrainian'=>'���� �����',
				),
				'info'=>'415,19,182,260,2',
			),
		),
		'blocks'=>array(
			'left'=>array(5,7,1),
			'right'=>array(6,3,2,4),
		),
		'addon'=>array('verhor'=>''),
	)),
);

$insert['cache']=<<<QUERY
INSERT INTO `{$prefix}cache` (`key`,`value`) VALUES
('blocks_defgr-admin', '{$ser['admin']}'),
('blocks_defgr-user', '{$ser['user']}')
QUERY;

$ser=array(
	1=>serialize(array('russian'=>'��������������','english'=>'Administrators','ukrainian'=>'������������')),
	serialize(array('russian'=>'������������','english'=>'Users','ukrainian'=>'�����������')),
	serialize(array('russian'=>'�����','english'=>'Guests','ukrainian'=>'����')),
	serialize(array('russian'=>'��������� ����','english'=>'Search engine bots','ukrainian'=>'������� ����')),
	serialize(array('russian'=>'�� ��������������','english'=>'Not activated','ukrainian'=>'�� ���������')),
	serialize(array('russian'=>'���������������','english'=>'Banned','ukrainian'=>'����������')),
);
$insert['groups']=<<<QUERY
INSERT INTO `{$prefix}groups` (`id`,`title_l`,`html_pref`,`html_end`,`protected`,`access_cp`,`max_upload`,`captcha`,`moderate`,`banned`) VALUES
(1, '{$ser[1]}', '<span style="color:red"><b>', '</b></span>', 1, 1, 1, 0, 0, 0),
(2, '{$ser[2]}', '', '', 1, 0, 2048, 0, 0, 0),
(3, '{$ser[3]}', '', '', 1, 0, 0, 1, 1, 0),
(4, '{$ser[4]}', '', '', 0, 0, 0, 1, 1, 0),
(5, '{$ser[5]}', '<span style="color:gray">', '</span>', 0, 0, 0, 1, 1, 0),
(6, '{$ser[6]}', '', '', 0, 0, 0, 1, 1, 1)
QUERY;

$insert['config_groups']=<<<QUERY
INSERT INTO `{$prefix}config_groups` (`id`,`name`,`protected`,`keyword`,`pos`) VALUES
(1, 'system', 1, 'system', 1),
(2, 'site', 1, 'site', 2),
(3, 'users-on-site', 1, 'users-on-site', 3),
(4, 'user-profile', 1, 'user-profile', 4),
(5, 'captcha', 1, 'captcha', 5),
(6, 'errors', 1, 'errors', 6),
(7, 'mailer', 1, 'mailer', 7),
(8, 'editor', 1, 'editor', 8),
(9, 'rss', 1, 'rss', 9),
(10, 'comments', 1, 'comments', 10),
(11, 'files', 1, 'files', 11),
(12, 'multisite', 1, 'multisite', 13),
(13, 'drafts', 1, 'drafts', 14),
(14, 'module_static', 1, 'module_static', 15),
(15, 'module_news', 0, 'module_news', 16);
QUERY;

#Russian
if($rus)
	$insert['config_groups_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'russian', '��������� ���������', '��������� ��������� �������'),
(2, 'russian', '��������� �����', '��������, �������� � ������'),
(3, 'russian', '������������ �� �����', '���������� ��������� ������������� �� �����'),
(4, 'russian', '������� ������������', '������������ ��������� ������������� �� �����'),
(5, 'russian', '�����', '��������� �����'),
(6, 'russian', '�����������', '��������� ����� �����'),
(7, 'russian', '��������� ����������� �����', ''),
(8, 'russian', '��������', '��������� ���������'),
(9, 'russian', 'RSS �����', '����� ��������� RSS ����'),
(10, 'russian', '�����������', ''),
(11, 'russian', '��������� ������', '��������� �������� � ���������� ������'),
(12, 'russian', '����������', '��������� ������� ��� ������� ������ �� ���������� ������.'),
(13, 'russian', '���������', ''),
(14, 'russian', '������ "����������� ��������"', ''),
(15, 'russian', '��������� ������ "�������"', '');
QUERY;
#[E] Russian

#Ukrainian
if($ukr)
	$insert['config_groups_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'ukrainian', '������� ������������', '������� ������������ �������'),
(2, 'ukrainian', '������������ �����', '�����, ����, �� ��.'),
(3, 'ukrainian', '����������� �� ����', '�������� ������������ ������������ �� ����'),
(4, 'ukrainian', '������� �����������', '���������� ������������ ������������ �� ����'),
(5, 'ukrainian', '�����', '������������ �����'),
(6, 'ukrainian', '���������', '������������ ���� �����'),
(7, 'ukrainian', '������������ ���������� �����', ''),
(8, 'ukrainian', '��������', '��������� ���������'),
(9, 'ukrainian', 'RSS ������', '������� ��������� RSS ������'),
(10, 'ukrainian', '���������', ''),
(11, 'ukrainian', '������� �����', '������������ ������������ � ���������� �����'),
(12, 'ukrainian', '����������', '������������ ������� ��� ������ ������ �� ������ ������.'),
(13, 'ukrainian', '��������', ''),
(14, 'ukrainian', '������ "������� �������"', ''),
(15, 'ukrainian', '��������� ������ "������"', '');
QUERY;
#[E] Ukrainian

#English
if($eng)
	$insert['config_groups_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}config_groups_l` (`id`,`language`,`title`,`descr`) VALUES
(1, 'english', 'System settings', 'System configuration tools'),
(2, 'english', 'Site settings', 'Title, description and others'),
(3, 'english', 'Users on site', 'Global settings users on site'),
(4, 'english', 'Users profile', 'Personal settings users on site'),
(5, 'english', 'Captcha', 'Captcha options'),
(6, 'english', 'Logging', 'Log options'),
(7, 'english', 'E-mail settings', ''),
(8, 'english', 'Editor', 'Editor settings'),
(9, 'english', 'RSS feeds', 'General settings of RSS feeds'),
(10, 'english', 'Comments', ''),
(11, 'english', 'Proccessing files', 'Settings of uploading and downloading files'),
(12, 'english', 'Multisite', 'Configuring the system for easy operation at several sites.'),
(13, 'english', 'Drafts', ''),
(14, 'english', 'Static "Pages module"', ''),
(15, 'english', 'Module configuration "News"', '');
QUERY;
#English

$insert['config']=<<<QUERY
INSERT INTO `{$prefix}config` (`id`,`group`,`type`,`name`,`protected`,`pos`,`multilang`,`eval_load`,`eval_save`) VALUES
(1, 1, 'edit', 'site_domain', 1, 1, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=preg_replace(''#^(?:[a-z]{2,}://)?([a-z0-9\\\\-\\\\.]+).*\$#i'',''\\\\1'',\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nelse\\r\\n	return preg_replace(''#^(?:[a-z]{2,}://)?([a-z0-9\\\\-\\\\.]+).*\$#i'',''\\\\1'',\$co[''value'']);'),
(2, 1, 'select', 'parked_domains', 1, 2, 0, '', ''),
(3, 1, 'edit', 'page_caching', 1, 3, 0, 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs(round((int)\$v/60));\\r\\n}\\r\\nelse\\r\\n	\$co[''value'']=abs(round((int)\$co[''value'']/60));\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v)*60;\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*60);'),
(4, 1, 'check', 'gzip', 1, 4, 0, '', ''),
(5, 1, 'edit', 'cookie_save_time', 1, 5, 0, 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=round(\$v/86400);\\r\\nelse\\r\\n	\$co[''value'']=round(\$co[''value'']/86400);\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v*86400);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*86400);'),
(6, 1, 'edit', 'cookie_domain', 1, 6, 0, '', ''),
(7, 1, 'edit', 'cookie_prefix', 1, 7, 0, '', ''),
(8, 1, 'items', 'guest_group', 1, 8, 0, '', ''),
(9, 1, 'check', 'bots_enable', 1, 9, 0, '', ''),
(10, 1, 'items', 'bot_group', 1, 10, 0, '', ''),
(11, 1, 'text', 'bots_list', 0, 11, 0, 'if(\$co[''multilang''])\r\n	foreach(\$co[''value''] as &\$v)\r\n	{\r\n		foreach(\$v as \$k=>&\$bot)\r\n			\$bot=\$k.''=''.\$bot;\r\n		\$v=join("\\n",\$v);\r\n	}\r\nelse\r\n{\r\n	foreach(\$co[''value''] as \$k=>&\$bot)\r\n		\$bot=\$k.''=''.\$bot;\r\n	\$co[''value'']=join("\\n",\$co[''value'']);\r\n}\r\nreturn\$co;', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n	{\r\n		\$res=array();\r\n		\$v=str_replace("\\r",'''',\$v);\r\n		foreach(explode("\\n",\$v) as \$bot)\r\n			if(strpos(\$bot,''='')!==false)\r\n			{\r\n				list(\$uagent,\$name)=explode(''='',\$bot,2);\r\n				\$res[\$uagent]=\$name;\r\n			}\r\n		\$v=\$res;\r\n	}\r\n	return\$co[''value''];\r\n}\r\nelse\r\n{\r\n	\$v=str_replace("\\r",'''',\$co[''value'']);\r\n	\$res=array();\r\n	foreach(explode("\\n",\$v) as \$bot)\r\n		if(strpos(\$bot,''='')!==false)\r\n		{\r\n			list(\$uagent,\$name)=explode(''='',\$bot,2);\r\n			\$res[\$uagent]=\$name;\r\n		}\r\n	return \$res;\r\n}'),
(12, 1, 'check', 'multilang', 1, 12, 0, 'if(count(Eleanor::\$langs)==1)\\r\\n	if(\$co[''multilang''])\\r\\n	{\\r\\n		\$co[''options''][''addon''][''disabled'']=''disabled'';\\r\\n		foreach(\$co[''value''] as &\$v)\\r\\n			\$v=0;\\r\\n	}\\r\\n	else\\r\\n	{\\r\\n		\$co[''value'']=0;\\r\\n		\$co[''options''][''addon''][''disabled'']=''disabled'';\\r\\n	}\\r\\nreturn\$co;', ''),
(13, 1, 'select', 'time_zone', 1, 13, 0, '', ''),
(14, 1, 'text', 'blocked_ips', 1, 14, 0, '', ''),
(15, 1, 'edit', 'blocked_message', 1, 15, 1, '', ''),

(16, 2, 'edit', 'site_name', 1, 1, 1, '', ''),
(17, 2, 'edit', 'site_defis', 1, 2, 1, '', ''),
(18, 2, 'text', 'site_description', 1, 3, 1, '', ''),
(19, 2, 'check', 'furl', 1, 4, 0, '', ''),
(20, 2, 'check', 'trans_uri', 1, 5, 0, '', ''),
(21, 2, 'edit', 'url_static_delimiter', 1, 6, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\r\\n		if(!\$v)\\r\\n			\$v=''/'';\\r\\n	}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\r\\n	if(!\$co[''value''])\\r\\n		\$co[''value'']=''/'';\\r\\n}\\r\\n	return\$co[''value''];'),
(22, 2, 'edit', 'url_static_defis', 1, 7, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\r\\n		if(!\$v)\\r\\n			\$v=''_'';\\r\\n	}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\r\\n	if(!\$co[''value''])\\r\\n		\$co[''value'']=''_'';\\r\\n}\\r\\nreturn\$co[''value''];'),
(23, 2, 'edit', 'url_static_ending', 1, 8, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		if(preg_match(''/^[^a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']/i'',\$v)==0)\\r\\n			\$v=''.''.\$v;\\r\\n}\\r\\nelse\\r\\n{\\r\\n	if(preg_match(''/^[^a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']/i'',\$co[''value''])==0)\\r\\n		\$co[''value'']=''.''.\$co[''value''];\\r\\n}\\r\\nreturn\$co[''value''];'),
(24, 2, 'edit', 'url_rep_space', 1, 9, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$v);\\r\\n		if(!\$v)\\r\\n			\$v=''-'';\\r\\n	}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=preg_replace(''/[a-z0-9''.constant(Language::\$main.''::ALPHABET'').'']+/i'','''',\$co[''value'']);\\r\\n	if(!\$co[''value''])\\r\\n		\$co[''value'']=''-'';\\r\\n}\\r\\nreturn\$co[''value''];'),
(25, 2, 'select', 'prefix_free_module', 1, 10, 0, '', ''),
(26, 2, 'check', 'site_closed', 1, 11, 0, '', ''),
(27, 2, 'editor', 'site_close_mes', 1, 12, 1, '', ''),
(28, 2, 'select', 'show_status', 1, 13, 0, '', ''),
(29, 2, 'items', 'templates', 1, 14, 0, '', ''),

(30, 3, 'edit', 'link_options', 1, 1, 1, '', ''),
(31, 3, 'edit', 'link_register', 1, 2, 1, '', ''),
(32, 3, 'edit', 'link_passlost', 1, 3, 1, '', ''),
(33, 3, 'user', 'time_online', 1, 4, 0, '', ''),
(34, 3, 'select', 'antibrute', 1, 5, 0, '', ''),
(35, 3, 'edit', 'antibrute_cnt', 1, 6, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(36, 3, 'edit', 'antibrute_time', 1, 7, 0, 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=round(\$v/60);\\r\\nelse\\r\\n	\$co[''value'']=round(\$co[''value'']/60);\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v*60);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*60);'),

(37, 4, 'text', 'blocked_names', 1, 1, 0, '', ''),
(38, 4, 'text', 'blocked_emails', 1, 2, 0, '', ''),
(39, 4, 'select', 'reg_type', 1, 3, 0, '', ''),
(40, 4, 'input', 'reg_act_time', 1, 4, 0, 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs(round((int)\$v/3600));\\r\\n}\\r\\nelse\\r\\n	\$co[''value'']=abs(round((int)\$co[''value'']/3600));\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v)*3600;\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*3600);'),
(41, 4, 'select', 'reg_unactivated', 1, 5, 0, '', ''),
(42, 4, 'check', 'reg_off', 1, 6, 0, '', ''),
(43, 4, 'edit', 'max_name_length', 1, 7, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=abs((int)\$v);\\r\\n		if(\$v<5)\\r\\n			\$v=5;\\r\\n	}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\n	if(\$co[''value'']<5)\\r\\n		\$co[''value'']=5;\\r\\n}\\r\\nreturn\$co[''value''];'),
(44, 4, 'edit', 'min_pass_length', 1, 8, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(45, 4, 'edit', 'avatar_bytes', 1, 9, 0, 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs(round((int)\$v/1024));\\r\\n}\\r\\nelse\\r\\n	\$co[''value'']=abs(round((int)\$co[''value'']/1024));\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v)*1024;\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*1024);'),
(46, 4, 'edit', 'avatar_size', 1, 10, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		if(preg_match(''#^\\\\d+ \\\\d+\$#'',\$v)==0)\\r\\n			throw new EE(''incorrect_format'',EE::INFO,array(''lang''=>true));\\r\\n}\\r\\nelseif(preg_match(''#^\\\\d+ \\\\d+\$#'',\$co[''value''])==0)\\r\\n	throw new EE(''incorrect_format'',EE::INFO,array(''lang''=>true));\\r\\nreturn\$co[''value''];'),
(47, 4, 'select', 'account_pass_rec_t', 1, 11, 0, '', ''),

(48, 5, 'edit', 'captcha_length', 1, 1, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(49, 5, 'edit', 'captcha_symbols', 1, 2, 0, '', ''),
(50, 5, 'edit', 'captcha_width', 1, 3, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(51, 5, 'edit', 'captcha_height', 1, 4, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(52, 5, 'edit', 'captcha_fluctuation', 1, 5, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),

(53, 6, 'edit', 'log_errors', 1, 1, 0, '', ''),
(54, 6, 'edit', 'log_exceptions', 1, 2, 0, '', ''),
(55, 6, 'edit', 'log_site_errors', 1, 3, 0, '', ''),
(56, 6, 'edit', 'log_db_errors', 1, 4, 0, '', ''),
(57, 6, 'edit', 'log_maxsize', 1, 5, 0, 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs(round((int)\$v/1024));\\r\\nelse\\r\\n	\$co[''value'']=abs(round((int)\$co[''value'']/1024));\\r\\nreturn\$co;', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v)*1024;\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']*1024);'),

(58, 7, 'select', 'mail_method', 1, 1, 0, '', ''),
(59, 7, 'edit', 'mail_from', 1, 2, 0, '', 'if(!Strings::CheckEmail(\$co[''value'']))\\r\\n	throw new EE(''incorrect_email'',EE::INFO,array(''lang''=>true));\\r\\nreturn\$co[''value''];'),
(60, 7, 'select', 'mail_priority', 1, 3, 0, '', ''),
(61, 7, 'edit', 'mail_reply', 1, 4, 0, '', 'if(!Strings::CheckEmail(\$co[''value''],false))\\r\\n	throw new EE(''incorrect_email'',EE::INFO,array(''lang''=>true));\\r\\nreturn\$co[''value''];'),
(62, 7, 'edit', 'mail_notice', 1, 5, 0, '', 'if(!Strings::CheckEmail(\$co[''value''],false))\\r\\n	throw new EE(''incorrect_email'',EE::INFO,array(''lang''=>true));\\r\\nreturn\$co[''value''];'),
(63, 7, 'edit', 'mail_smtp_user', 1, 6, 0, '', ''),
(64, 7, 'edit', 'mail_smtp_pass', 1, 7, 0, '', ''),
(65, 7, 'edit', 'mail_smtp_host', 1, 8, 0, '', ''),
(66, 7, 'edit', 'mail_smtp_port', 1, 9, 0, '', ''),

(67, 8, 'select', 'editor_type', 1, 1, 0, '', ''),
(68, 8, 'text', 'bad_words', 1, 2, 0, '', ''),
(69, 8, 'edit', 'bad_words_replace', 1, 3, 1, '', ''),
(70, 8, 'select', 'antidirectlink', 1, 4, 0, '', ''),
(71, 8, 'check', 'autoparse_urls', 1, 5, 0, '', ''),

(72, 9, 'uploadimage', 'rss_image', 1, 1, 0, '', ''),

(73, 10, 'select', 'comments_sort', 1, 1, 0, '', ''),
(74, 10, 'edit', 'comments_pp', 1, 2, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=abs((int)\$v);\\r\\n		if(\$v==0)\\r\\n			\$v=10;\\r\\n	}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\n	if(\$co[''value'']==0)\\r\\n		\$co[''value'']=10;\\r\\n}\\r\\nreturn\$co[''value''];'),
(75, 10, 'edit', 'comments_timelimit', 1, 3, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(76, 10, 'items', 'comments_display_for', 1, 5, 0, '', ''),
(77, 10, 'items', 'comments_post_for', 1, 6, 0, '', ''),

(78, 11, 'check', 'thumbs', 1, 1, 0, '', ''),
(79, 11, 'edit', 'thumb_types', 1, 2, 0, '', ''),
(80, 11, 'edit', 'thumb_width', 1, 3, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n}\\r\\nelse\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\nreturn\$co[''value''];'),
(81, 11, 'edit', 'thumb_height', 1, 4, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n}\\r\\nelse\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\nreturn\$co[''value''];'),
(82, 11, 'select', 'thumb_reducing', 1, 5, 0, '', ''),
(83, 11, 'select', 'thumb_first', 1, 6, 0, '', ''),
(84, 11, 'check', 'watermark', 1, 7, 0, '', ''),
(85, 11, 'edit', 'watermark_types', 1, 8, 0, '', ''),
(86, 11, 'edit', 'watermark_alpha', 1, 9, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=abs((int)\$v);\\r\\n		if(\$v>100)\\r\\n			\$v=100;\\r\\n	}\\r\\n}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\n	if(\$co[''value'']>100)\\r\\n		\$co[''value'']=100;\\r\\n}\\r\\nreturn\$co[''value''];'),
(87, 11, 'edit', 'watermark_top', 1, 10, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=abs((int)\$v);\\r\\n		if(\$v>100)\\r\\n			\$v=100;\\r\\n	}\\r\\n}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\n	if(\$co[''value'']>100)\\r\\n		\$co[''value'']=100;\\r\\n}\\r\\nreturn\$co[''value''];'),
(88, 11, 'edit', 'watermark_left', 1, 11, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n	{\\r\\n		\$v=abs((int)\$v);\\r\\n		if(\$v>100)\\r\\n			\$v=100;\\r\\n	}\\r\\n}\\r\\nelse\\r\\n{\\r\\n	\$co[''value'']=abs((int)\$co[''value'']);\\r\\n	if(\$co[''value'']>100)\\r\\n		\$co[''value'']=100;\\r\\n}\\r\\nreturn\$co[''value''];'),
(89, 11, 'edit', 'watermark_image', 1, 12, 0, '', ''),
(90, 11, 'edit', 'watermark_string', 1, 13, 1, '', ''),
(91, 11, 'edit', 'watermark_csa', 1, 14, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		if(preg_match(''#^\\\\d+,\\\\d+,\\\\d+,\\\\d+,\\\\d+\$#'',\$v)==0)\\r\\n			throw new EE(''incorrect_format'',EE::INFO,array(''lang''=>true));\\r\\n}\\r\\nelse\\r\\n{\\r\\n	if(preg_match(''#^\\\\d+,\\\\d+,\\\\d+,\\\\d+,\\\\d+\$#'',\$co[''value''])==0)\\r\\n		throw new EE(''incorrect_format'',EE::INFO,array(''lang''=>true));\\r\\n}\\r\\nreturn\$co[''value''];'),
(92, 11, 'check', 'download_antileech', 1, 15, 0, '', ''),
(93, 11, 'check', 'download_no_session', 1, 16, 0, '', ''),

(94, 12, 'edit', 'multisite_secret', 0, 1, 0, '', ''),
(95, 12, 'edit', 'multisite_ttl', 0, 2, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),

(96, 13, 'edit', 'drafts_days', 0, 1, 0, '', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n		\$v=(int)\$v;\r\n	return\$co[''value''];\r\n}\r\nreturn(int)\$co[''value''];'),
(97, 13, 'edit', 'drafts_autosave', 0, 2, 0, '', 'if(\$co[''multilang''])\r\n{\r\n	foreach(\$co[''value''] as &\$v)\r\n		\$v=(int)\$v;\r\n	return\$co[''value''];\r\n}\r\nreturn(int)\$co[''value''];'),

(98, 14, 'user', 'm_static_general', 1, 1, 0, '', ''),

(99, 15, 'edit', 'publ_per_page', 0, 1, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(100, 15, 'edit', 'publ_rss_per_page', 0, 3, 0, '', 'if(\$co[''multilang''])\\r\\n{\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=abs((int)\$v);\\r\\n	return\$co[''value''];\\r\\n}\\r\\nreturn abs((int)\$co[''value'']);'),
(101, 15, 'check', 'publ_add', 0, 4, 0, '', ''),
(102, 15, 'check', 'publ_catsubcat', 0, 5, 0, '', ''),
(103, 15, 'check', 'publ_ping', 0, 6, 0, '', ''),
(104, 15, 'check', 'publ_rating', 0, 7, 0, '', ''),
(105, 15, 'check', 'publ_mark_details', 0, 8, 0, '', ''),
(106, 15, 'check', 'publ_mark_users', 0, 9, 0, '', ''),
(107, 15, 'edit', 'publ_remark', 0, 10, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=max((int)\$v,1);\\r\\nelse\\r\\n	\$co[''value'']=max((int)\$co[''value''],1);\\r\\nreturn\$co[''value''];'),
(108, 15, 'edit', 'publ_lowmark', 0, 11, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=min((int)\$v,0);\\r\\nelse\\r\\n	\$co[''value'']=min((int)\$co[''value''],0);\\r\\nreturn\$co[''value''];'),
(109, 15, 'edit', 'publ_highmark', 0, 12, 0, '', 'if(\$co[''multilang''])\\r\\n	foreach(\$co[''value''] as &\$v)\\r\\n		\$v=max((int)\$v,0);\\r\\nelse\\r\\n	\$co[''value'']=max((int)\$co[''value''],0);\\r\\nreturn\$co[''value''];')
QUERY;

$multilang=count($languages)>1;
if($furl)
{	$p=Language::$main=='russian' ? '' : '%D1%80%D1%83%D1%81/';
	$ac_a_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82.html';
	$ac_r_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/register';
	$ac_p_r=$p.'%D0%B0%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/lostpass';

	$p=Language::$main=='ukrainian' ? '' : '%D1%83%D0%BA%D1%80/';
	$ac_a_u=$p.'%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82.html';
	$ac_r_u=$p.'%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/register';
	$ac_p_u=$p.'%D0%BA%D0%BA%D0%B0%D1%83%D0%BD%D1%82/lostpass';

	$p=Language::$main=='english' ? '' : 'eng/';
	$ac_a_e=$p.'account.html';
	$ac_r_e=$p.'account/register';
	$ac_p_e=$p.'account/lostpass';

	$e_403='index.php?module=errors&code=403';
}
else
{	$ac_a='index.php?module=account';
	$ac_r='index.php?module=account&amp;do=register';
	$ac_p='index.php?module=account&amp;do=lostpass';

	$p=Language::$main=='russian' ? '' : 'lang=%F0%F3%F1&amp;';
	$ac_a_r='index.php?'.$p.'module=%E0%EA%EA%E0%F3%ED%F2';
	$ac_r_r='index.php?'.$p.'module=%E0%EA%EA%E0%F3%ED%F2&amp;do=register';
	$ac_p_r='index.php?'.$p.'module=%E0%EA%EA%E0%F3%ED%F2&amp;do=lostpass';

	$p=Language::$main=='russian' ? '' : 'lang=%F3%EA%F0&amp;';
	$ac_a_u='index.php?'.$p.'module=account';
	$ac_r_u='index.php?'.$p.'module=account&amp;do=register';
	$ac_p_u='index.php?'.$p.'module=account&amp;do=lostpass';

	$p=Language::$main=='russian' ? '' : 'lang=eng&amp;';
	$ac_a_e='index.php?'.$p.'module=account';
	$ac_r_e='index.php?'.$p.'module=account&amp;do=register';
	$ac_p_e='index.php?'.$p.'module=account&amp;do=lostpass';
	$e_403='errors/403.html';}

$ser=array(
	'groups'=>'include Eleanor::$root.\'\'addons/admin/options/groups.php\'\';',
	'bots'=>serialize(array(
		'googlebot'=>'Google Bot',
		'slurp@inktomi'=>'Hot Bot',
		'archive_org'=>'Archive.org Bot',
		'Ask Jeeves'=>'Ask Jeeves Bot',
		'Lycos'=>'Lycos Bot',
		'WhatUSeek'=>'What You Seek Bot',
		'ia_archiver'=>'IA.Archiver Bot',
		'GigaBlast'=>'Gigablast Bot',
		'Gigabot'=>'Gigablast Bot',
		'Yandex'=>'Yandex Bot',
		'Yahoo!'=>'Yahoo Bot',
		'Yahoo-MMCrawler'=>'Yahoo-MM Crawler Bot',
		'TurtleScanner'=>'Turtle Scanner Bot',
		'TurnitinBot'=>'Turnitin Bot',
		'ZipppBot'=>'Zippp Bot',
		'StackRambler'=>'Stack Rambler Bot',
		'oBot'=>'oBot',
		'rambler'=>'Rambler Bot',
		'Jetbot'=>'Jet Bot',
		'NaverBot'=>'Naver Bot',
		'libwww'=>'Punto Bot',
		'aport'=>'Aport Bot',
		'msnbot'=>'MSN Bot',
		'MnoGoSearch'=>'Mno Go Search Bot',
		'booch'=>'Booch Bot',
		'Openbot'=>'Openfind Bot',
		'scooter'=>'Altavista Bot',
		'WebCrawler'=>'Fast Bot',
		'WebZIP'=>'WebZIP Bot',
		'GetSmart'=>'Get Smart Bot',
		'grub-client'=>'Grub Client Bot',
		'Vampire'=>'Net Vampire Bot',
	)),
	'templates'=>'include Eleanor::$root.\'\'addons/admin/options/templates.php\'\'',
	'tz'=>'include Eleanor::$root.\'\'addons/admin/options/tz.php\'\'',
	'lfm'=>'include Eleanor::$root.\'\'addons/admin/options/lfm.php\'\'',
	'time_online'=>'include Eleanor::$root.\'\'addons/admin/options/time_online.php\'\'',
	'sg'=>'include Eleanor::$root.\'\'modules/static/optionsg.php\'\'',
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'������������\'\',
			\'\'redirect\'\'=>\'\'������������� ��� ������ �� �������� �����\'\',
			\'\'rel\'\'=>\'\'�������� rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'������\'\',\'\'���������� ������ ���������������\'\',\'\'���������� ����\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'���������\'\',\'\'����� ������� �� ������������ �����\'\',\'\'���������� ����� ����� ���������� ������ ������� �����\'\'),
	)',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'����������\'\',\'\'�������\'\',\'\'�������\'\',\'\'������\'\',\'\'������\'\'),
	)',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'�� ���������\'\',\'\'����� e-mail\'\',\'\'������� ���������������\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'�������\'\',\'\'������ �� ������\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'���������\'\',\'\'��������� ������ ����� ������\'\',\'\'������������� � ������� ������ �� e-mail\'\'),
	);',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'��������\'\',\'\'������\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'��������\'\',
			\'\'small\'\'=>\'\'���������\'\',
			\'\'cutsmall\'\'=>\'\'�������� � ���������\'\',
			\'\'smallcut\'\'=>\'\'��������� � ��������\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'������\'\',
			\'\'h\'\'=>\'\'������\'\',
			\'\'b\'\'=>\'\'���������� �������\'\',
			\'\'s\'\'=>\'\'���������� �������\'\',
		),
	)',
);
$secret=uniqid();

if($rus)
	$insert['config_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`,`title`,`descr`,`value`,`serialized`,`default`,`extra`,`startgroup`) VALUES
(1, 'russian', '�������� �����', '�������� ��� ��������� http://', '{$domain}', 0, '{$domain}', '', '�����'),
(2, 'russian', '��������� ����������� �������', '��� ������� ����� �� ����������� ������', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'russian', '����������� ������� ���������', '������� ����������� ���� ����������� �������� � �������. 0 - ��������� �����������.', '600', 0, '600', '', '����������� ��������'),
(4, 'russian', '�������� GZIP ������?', '��������� ���� ����� �������� ���������� ������', '1', 0, '1', '', ''),
(5, 'russian', '���� �������� cookie (� ����)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'russian', '����� cookie', '����������� .example.com ��� ���������� cookie. �������� �������� �� ����� ����� ������ ������. ������ "example.com" ������������ ���� �������� ���. ���� �������� ��������� ����������� ������, ������ ����� ������ ����������� *.', '.*', 0, '.*', '', ''),
(7, 'russian', '������� �ookie', '������ ����� ��������� �������� ����������, ���� �� ������ ����� ������� ����������� � ������ �������, ������������ cookies', 'el', 0, 'el', '', ''),
(8, 'russian', '������ ������', '�������� ������ ������� ������...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', '����� �� ���������'),
(9, 'russian', '����������� ��������� �����?', '', '1', 0, '1', '', ''),
(10, 'russian', '������ ��������� �����', '�������� ��������� ����� ������� ������...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'russian', '������ ��������� �����', '����� �������� ������ � ��������� �����. ������ �����: �� ������ � ������ ������ � ���� <b>user agent=��� ����</b>.', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'russian', '�������� ������������� ���������?', '', '{$multilang}', 0, '{$multilang}', '', '�����������'),
(13, 'russian', '������� ���� ��-���������', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'russian', '��������������� IP ������', '������ ����� � � ����� ������. ����������� ����� ���� 87.183.*.*, � ��� �� ��������� ���� 79.224.60.1-79.224.60.255 � ���������� �����. ����� ������� ���������� ������� ���� ����� ��������� IP ������, ��������� = � �������� �������. ��������: 87.183.*.*=�������� ����� �� �����!', '', 0, '', 'array(''addon''=>array(''style''=>''word-wrap:normal''));', '��� �� IP'),
(15, 'russian', '��������� ��� ���������������', '��� ��������� ������ ������������, ��� ������� �� ������� �������.', ':-p', 0, ':-p', '', ''),

(16, 'russian', '�������� �����', '', '{$sitename}', 0, '{$sitename}', '', '��������� �����'),
(17, 'russian', '����������� ����������', '', ' - ', 0, ' - ', '', ''),
(18, 'russian', '�������� �����', '', '���� �������� �� ������� ���������� ������ Eleanor', 0, '���� �������� �� ������� ���������� ������ Eleanor', '', ''),
(19, 'russian', '�������� ����������� ������?', '', '{$furl}', 0, '{$furl}', '', '��������� ������'),
(20, 'russian', '����������������� ����������� ������?', '����������� ������ ��������, � ����� ����� ����������� ������ ����� ������������� �����������������.', '0', 0, '0', '', ''),
(21, 'russian', '����������� ����������', '� ������ <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> ������������ ���������� �������� ����� ����� / (����).\r\n��� ����� ����������� ����� �� ��������� �������.', '/', 0, '/', '', ''),
(22, 'russian', '����������� ��������', '� ������ <q>news/category/news/page<b>_</b>1.html</q> ������������ �������� �������� ������ ������� _.\r\n��� ����� ����������� ����� �� ��������� �������.', '_', 0, '_', '', ''),
(23, 'russian', '��������� ����������� ������', '� ������ <q>news/category/news/page_1<b>.html</b></q> ���������� �������� <q>.html</q>.\r\n�������� ��������, ��� ���������� ��� ���� ������ � �� ���������� �������!', '.html', 0, '.html', '', ''),
(24, 'russian', '���������� ������������ �������� � ��������� �������', '�������� �� ������ ��������� �� � ������������ ����������, �� � ������������ ��������, �� � ���������� ����������� ������ � ��� �� ������ ���������� � �� ���������� �������.', '-', 0, '-', '', ''),
(25, 'russian', '������ ��� ��������', '������ ����� ������ ����� �������� ��� �������� ��������-�������������� ������', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'russian', '��������� ����?', '���� ����� �������� ������ �������, ��� ������� �������� ����� ��������� ��������� �����', '0', 0, '0', '', '���������� �����'),
(27, 'russian', '������� ���������� �����', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'russian', '���������� � ��������� ��������', '���������� ����� ��������, ���������� �������� ��������� ��������, ���������� �������������� �������� � ���� ������, ������ GZIP ������ � ���������� ����������� ������.', '2', 0, '2', '{$ser['pg']}', '�������������� ����������'),
(29, 'russian', '������� �� �����', '������� �������, ������� ������������ ������ �������� � �������� ���������� �����.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', '������'),

(30, 'russian', '������ ������� ��������', '�������� ��������, ��� ������ <q>param1=value1<b>&amp;</b>param2=value2</q> �����������. ���������� ������: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_a_r}', 0, '{$ac_a_r}', '', '������'),
(31, 'russian', '������ �� �����������', '�������� ��������, ��� ������ <q>param1=value1<b>&amp;</b>param2=value2</q> �����������. ���������� ������: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_r_r}', 0, '{$ac_r_r}', '', ''),
(32, 'russian', '������ �� �������������� ������', '�������� ��������, ��� ������ <q>param1=value1<b>&amp;</b>param2=value2</q> �����������. ���������� ������: <q>param1=value1<b>&amp;amp;</b>param2=value2</q>', '{$ac_p_r}', 0, '{$ac_p_r}', '', ''),
(33, 'russian', '������������ ������', '���������� ������ �� ����� ������� ������������ ��������� ������ ����� ���������� ����������.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', '��������� �������������� � �����������'),
(34, 'russian', '��� ������', '', '1', 0, '1', '{$ser['ab']}', '������ �� ������� ������'),
(35, 'russian', '������������ ����� ��������� ������� ��������������', '', '5', 0, '5', '', ''),
(36, 'russian', '������������ ���������� ������� � �������, �� ����� �������� ����������� ��������� ������� ��������������', '������: ���� ������������ ����� ��������� ������� �������������� ����� 5, � �������� �������� ����� 15, �� ������������ ����� ������������ � ������ ���� �� ��������� 15 ����� ���� 5 ��������� ������� �����.', '600', 0, '600', '', ''),

(37, 'russian', '��������������� ����', '����� �������. ���������� �����������: * - ����� ������������������ ��������, ? - ����� ���� ������.', '', 0, '', '', '����������'),
(38, 'russian', '��������������� e-mail', '����� �������. ���������� �����������: * - ����� ������������������ ��������, ? - ����� ���� ������.', '', 0, '', '', ''),
(39, 'russian', '��������� �������������� ������������', '', '1', 0, '1', '{$ser['aa']}', '�����������'),
(40, 'russian', '���� ���������', '���������� ����� ���������� ��� ��������� ������� ������', '86400', 0, '86400', 'array(''type''=>''number'',''addon''=>array(''min''=>1));', ''),
(41, 'russian', '��� ��������� � ����������������� �������� ��������', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'russian', '��������� �����������?', '', '0', 0, '0', '', ''),
(43, 'russian', '������������ ����� ����', '', '15', 0, '15', '', ''),
(44, 'russian', '����������� ����� ������', '', '7', 0, '7', '', ''),
(45, 'russian', '������������ ������ ������������ ������� � KB', '', '307200', 0, '307200', '', '�������'),
(46, 'russian', '������������ ������ �������', '������� ����� � �������: ������[������]������.', '100 100', 0, '100 100', '', ''),
(47, 'russian', '�������������� ������', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'russian', '���������� �������� � captcha', '', '5', 0, '5', '', '��������� captcha'),
(49, 'russian', '������� captcha', '�������, ������������ � captcha (���������� ��������� ������� �������, ��� 0� ����� � o � �����)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'russian', '������ captcha', '', '120', 0, '120', '', ''),
(51, 'russian', '������ captcha', '', '60', 0, '60', '', ''),
(52, 'russian', '������� �������� �� ���������', '������������ ���������� �������� �� ��������� �� ������.', '5', 0, '5', '', ''),

(53, 'russian', '���-���� ������ ����', '', 'addons/logs/errors.log', 0, 'addons/logs/errors.log', '', '����������� ������'),
(54, 'russian', '���-���� ��������������� ����������', '', 'addons/logs/exceptions.log', 0, 'addons/logs/exceptions.log', '', ''),
(55, 'russian', '���-���� ������ �����', '', 'addons/logs/site_errors.log', 0, 'addons/logs/site_errors.log', '', ''),
(56, 'russian', '���-���� ���� ������ (������� �������)', '', 'addons/logs/db_errors.log', 0, 'addons/logs/db_errors.log', '', ''),
(57, 'russian', '���������� ������ �����', '����������� � ����������. ����� ���������� ����� �������, ���� ������������� ������������� Gzip ��� BZip2 �����. ������� 0 ��� ���������� ��������������� ������.', '2097152', 0, '2097152', '', ''),

(58, 'russian', '������ �������� e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', '����� ���������'),
(59, 'russian', 'E-mail �����������', '', '{$email}', 0, '{$email}', '', ''),
(60, 'russian', '��������', '', '3', 0, '3', '{$ser['pr']}', ''),
(61, 'russian', 'E-mail ��� ������', '� ������, ���� ������ �� ������������� ���������� ��������� �� ������ e-mail, ��������� ��� ����.', '', 0, '', '', ''),
(62, 'russian', 'E-mail ��� ������������� � ���������', '', '', 0, '', '', ''),
(63, 'russian', '�����', '������������', '', 0, '', '', '��������� SMTP'),
(64, 'russian', '������', '', '', 0, '', '', ''),
(65, 'russian', '����', '������', '', 0, '', '', ''),
(66, 'russian', '����', '', '25', 0, '25', '', ''),

(67, 'russian', '�������� �� ���������', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(68, 'russian', '����������� �����', '���� � ������������. ����� �������.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(69, 'russian', '���������� ����������� ����', '', '*�������*', 0, '*�������*', '', ''),
(70, 'russian', '�������� ������ �� ������ ������?', '', 'go', 0, 'go', 'array(''options''=>array(''���'',''go''=>''�������� ����� go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(71, 'russian', '�������� ��������������� ������ � ������?', '��� ��������� ���� �����, ��� ������ �������������� ��� ����� - ����� ���������� ��� ������.', '1', 0, '1', '', ''),

(72, 'russian', '������� RSS', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>'''',)', ''),

(73, 'russian', '������� ���������� ������������', '', '1', 0, '1', '{$ser['or']}', ''),
(74, 'russian', '������������ �� ��������', '', '10', 0, '10', '', ''),
(75, 'russian', '����������� ��������� �� �������', '������� ���������� ������, �� ��������� ������� ������������ �� ������ ������� / ������� ���� �����������. ������ ������� �������������� � ������� ���������� �����������.', '86400', 0, '86400', '', ''),
(76, 'russian', '���������� ����������� ���', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', '�����'),
(77, 'russian', '���������� ������������ �������� ���', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(78, 'russian', '�������� �������� ������ ��� ����������� �����������?', '', '1', 0, '1', '', '������ �����������'),
(79, 'russian', '���� ������ ��� ������� ��������� ������', '', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(80, 'russian', '������ ������', '������� 0 ��� ���������� �������� ������ �����������', '200', 0, '200', '', ''),
(81, 'russian', '������ ������', '������� 0 ��� ���������� �������� ������ �����������', '0', 0, '0', '', ''),
(82, 'russian', '������ ���������� �����������', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(83, 'russian', '�������� ������ ������ �', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(84, 'russian', '�������� ������� ����?', '�������� ������� ���� �� ����������� ��������?', '1', 0, '1', '', '��������� �������� �����'),
(85, 'russian', '���� ������ ��� �������� �����', '�� ��� ���� ������ ����� ��������� ������� ����. ����������, �������� ��������.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(86, 'russian', '������������ �������� ����� (� ��������� �� 0 �� 100)', '100 - �� ����� �������� �����', '50', 0, '50', '', ''),
(87, 'russian', '��������� �� ��������� (� ��������� �� 0 �� 100)', '', '50', 0, '50', '', ''),
(88, 'russian', '��������� �� ����������� (� ��������� �� 0 �� 100)', '', '50', 0, '50', '', ''),
(89, 'russian', '���� �������� �����', '������� ���� � ������� �� �������, ������� ����� ����������� � �������� �������� ����� (��������: images/watermrak.jpg). �������� ��������, ��� ������� ���� �� ����� ������� �� ����������� ���� �� �������� ��� ������, ��� ������� ����. ����� ��������� ��� ������� �������� �����.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(90, 'russian', '����� �������� �����', '���� ����� ����� ������� �� ����������� � �������� �������� �����, ���� ����������� �������� ����� ����������.', '� {$sitename}', 0, '� {$sitename}', '', ''),
(91, 'russian', '����, ������ � ���� ������ �������� �����', '�������� � ������� red,green,blue,size,angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(92, 'russian', '��������� ���������� � ������ ������?', '��� ��������� ���� �����, ��� ������� ������� ���� ����� ����������� ����� ��������, � ������� ������ ������������. ���� ��� ����� ����� ��������, ������������ �� ������ ������� ����.', '1', 0, '1', '', '���������� ������'),
(93, 'russian', '��������� ���������� ��� ������?', '��� ��������� ���� �����, ������������, IP ����� �������� �� ������������ � ������ ������, �� ������ ������� ����.', '1', 0, '1', '', ''),

(94, 'russian', '������ �����', '��������� ��������� ������ ��� ������ ������� ����� ������������� ������ ��� �����-�������� ����������.', '{$secret}', 0, '{$secret}', '', ''),
(95, 'russian', '���� ����� ������ �����-�������� ����������', '� ��������.', '100', 0, '100', '', ''),

(96, 'russian', '������� ���� ������� ���������?', '', '10', 0, '10', '', ''),
(97, 'russian', '�������� �������������� � ��������', '', '20', 0, '20', '', ''),

(98, 'russian', '��������, ��������� �� �������', '�������� ������ ��� ������ ����������', '', 0, '', '{$ser['sg']}', ''),

(99, 'russian', '���������� �� ��������', '', '10', 0, '10', '', '��������'),
(100, 'russian', '���������� �� �������� � RSS', '', '30', 0, '30', '', ''),
(101, 'russian', '���������� �������� ��������������', '', '1', 0, '1', '', ''),
(102, 'russian', '�������� ���������� ������������ ��� ��������� ���������', '', '1', 0, '1', '', ''),
(103, 'russian', '�������� ping', '����������� ��������� ������ �� ���������� �� �����', '1', 0, '1', '', ''),
(104, 'russian', '�������� �������', '', '1', 0, '1', '', '��������� ��������'),
(105, 'russian', '������ ������ ��� ��������� ���������?', '��������� ��������� ���������� ������ ��� �� ��������� ���������?', '0', 0, '0', '', ''),
(106, 'russian', '������� ������ ��� �������������', '��� ��������� ���� �����, ���������� ������� ������ ������ ������ �������������� ������������ � ������ 1 ���.', '0', 0, '0', '', ''),
(107, 'russian', '������ ����� ������������ � ����', '���� ���������� ������ ����� �� ������ ������������, �� � �����, ��� ����� ���������� �����, �� ��������� �������� ����� ������ ����� ��������� ������.', '3', 0, '3', '', ''),
(108, 'russian', '����������� ���������� ������', '�������� �� ����� ���� ���� ����. ��� ���������� ���������� ������, ������� 0.', '-3', 0, '-3', '', ''),
(109, 'russian', '������������ ���������� ������', '�������� �� ����� ���� ���� ����. ��� ���������� ���������� ������, ������� 0.', '3', 0, '3', '', '')
QUERY;
#[E] Russian

$ser=array(
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'����������\'\',
			\'\'redirect\'\'=>\'\'������������� �� ��������� �� �������� �����\'\',
			\'\'rel\'\'=>\'\'������ rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'���������\'\',\'\'³��������� ����� �������������\'\',\'\'³��������� ���\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'³��������\'\',\'\'˳�� ����� �� ������ ���\'\',\'\'³��������� ����� ���� ���������� ���� ����� �����.\'\'),
	)',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'�������\'\',\'\'������\'\',\'\'���������\'\',\'\'������\'\',\'\'��������\'\'),
	)',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'�� �������\'\',\'\'����� e-mail\'\',\'\'������ �������������\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'��������\'\',\'\'ͳ���� �� ������\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'����������\'\',\'\'��������� ������ ����� ������\'\',\'\'����������� � ������� ������ �� e-mail\'\'),
	)',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'���������\'\',\'\'������\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'�������\'\',
			\'\'small\'\'=>\'\'��������\'\',
			\'\'cutsmall\'\'=>\'\'������� �� ��������\'\',
			\'\'smallcut\'\'=>\'\'�������� �� �������\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'������\'\',
			\'\'h\'\'=>\'\'������\'\',
			\'\'b\'\'=>\'\'�������� �������\'\',
			\'\'s\'\'=>\'\'�������� �������\'\',
		),
	)',
)+$ser;
#Ukrainian
if($ukr)
	$insert['config_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`, `title`, `descr`, `value`, `serialized`, `default`, `extra`, `startgroup`) VALUES
(1, 'ukrainian', '�������� �����', '��������� ��� ��������� http://', '{$domain}', 0, '{$domain}', '', '�����'),
(2, 'ukrainian', 'ϳ������� ���������� ������', '��� ������� ����� �� ����������� �����', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'ukrainian', '��������� ������� ���������', '������ ����������� ����� ��������� ������� � ��������. 0 - ��������� ���������.', '600', 0, '600', '', '���������� ������������'),
(4, 'ukrainian', '�������� GZIP ���������?', '��������� ���� ����� ��������� ��������� ������', '1', 0, '', '', ''),
(5, 'ukrainian', '����� ��������� cookie (� ����)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'ukrainian', '����� cookie', '�������������� .example.com ��� ���������� cookie. ������� ����� �� ������ ����� ��''�� ������. ������ "example.com" �������������� ���� ������� ��''�. ���� �������� �������� ���������� ������, ������ ���� ������ �������������� *.', '.*', 0, '.*', '', ''),
(7, 'ukrainian', '������� �ookie', '���� ����� �������� �������� ��������, ���� �� ����� ��� ������� ���������� ����� ���� �������, �� �������������� cookies.', 'el', 0, 'el', '', ''),
(8, 'ukrainian', '����� ������', '������� ������ ������� �����...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', '����� �� ������������'),
(9, 'ukrainian', '³������������ ��������� ������?', '', '1', 0, '1', '', ''),
(10, 'ukrainian', '����� ��������� ����', '������� ��������� ������ ������� �����...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'ukrainian', '������ ��������� ����', '��� ����������� ��� ��� ��������� �����. ������ �����: �� ������ � ������� ����� � ������ <b>user agent=��''� ����</ b>', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'ukrainian', '�������� ����������� ��������?', '', '{$multilang}', 0, '{$multilang}', '', '����������'),
(13, 'ukrainian', '������� ���� �� �������������', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'ukrainian', '���������� IP ������', '����� ������ - � ������ �����. ������������ ����� ���� 87.183 .*.*, � ��� ���� �������� ���� 79.224.60.1-79.224.60.255 � ��������� �����. ��� ������� �������� ������� ���� ���� ��������� IP ������, �������� = � �������� �������. ���������: 87.183 .*.*=��������� ��� �� ����!', '', 0, '', 'array(''addon''=>array(''style''=>''word-wrap:normal''));', '��� �� IP'),
(15, 'ukrainian', '����������� ��� ������������', '�� ����������� �������� �����������, ��� ���� �� ������� �������.', ':-p', 0, ':-p', '', ''),

(16, 'ukrainian', '����� �����', '', '{$sitename}', 0, '{$sitename}', '', '��������� �����'),
(17, 'ukrainian', '��������� ���������', '', ' - ', 0, ' - ', '', ''),
(18, 'ukrainian', '���� �����', '', '���� ����������� �� ������ ��������� ������ Eleanor', 0, '���� ����������� �� ������ ��������� ������ Eleanor', '', ''),
(19, 'ukrainian', '�������� ������� ���������?', '', '{$furl}', 0, '{$furl}', '', '������������ ��������'),
(20, 'ukrainian', '�������������� ������� ���������?', '������� ��������� ��������, � ����� ����� �����, �� �������������� ������ ����������� �������������.', '0', 0, '', '', ''),
(21, 'ukrainian', '��������� ���������', '� �������� <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> ����������� ��������� � ���� ����� / (����).\r\n��� �������� ������������ ����-�� �� ����� �������.', '/', 0, '/', '', ''),
(22, 'ukrainian', '��������� �������', '� �������� <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q> ����������� ������� � ����� ������� _.\r\n��� �������� ������������ ����-�� �� ����� �������.', '_', 0, '_', '', ''),
(23, 'ukrainian', '��������� ��������� ��������', '� �������� <q>news/category/news/page_1<b>.html</b></q> ���������� � <q>.html</ q>.\r\n������� �����, �� ���������� �� ���� ������� � �� �������� �������!', '.html', 0, '.html', '', ''),
(24, 'ukrainian', '��������� ������������� ������� � ��������� ����������', '�������� �� ������� �������� � � ����������� ���������, � � ����������� �������, � � ���������� ��������� �������� � ��� ���� ������� ���������� � �� �������� �������.', '-', 0, '-', '', ''),
(25, 'ukrainian', '������ ��� ��������', '��������� ����� ������ ������ ��������� ��� ������� ��������-�������������� ������', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'ukrainian', '�������� ����?', '���� ���� ��������� ����� ������, ��� ���� �������� ����� ��������� ��������� �����', '0', 0, '0', '', '��������� �����'),
(27, 'ukrainian', '������� ��������� �����', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'ukrainian', '���������� ��� ��������� �������', '���������� ����� �������, �� ������ �������� ��������� �������, ������� ������������ ������ �� ���� �����, ������ GZIP ��������� � ������� ��������� ���''��.', '2', 0, '2', '{$ser['pg']}', '��������� ����������'),
(29, 'ukrainian', '������� �� ����', '������ �������, �� ����������� ������� �������� � ����� ���������� �����.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', 'г���'),

(30, 'ukrainian', '��������� ���������� �������', '������� �����, �� ����� <q>param1=value1<b>&</b>param2=value2</q> �����������. ��������� �����: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_a_u}', 0, '{$ac_a_u}', '', '���������'),
(31, 'ukrainian', '��������� �� ���������', '������� �����, �� ����� <q>param1=value1<b>&</b>param2=value2</q> �����������. ��������� �����: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_r_u}', 0, '{$ac_r_u}', '', ''),
(32, 'ukrainian', '��������� �� ���������� ������', '������� �����, �� ����� <q>param1=value1<b>&</b>param2=value2</q> �����������. ��������� �����: <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_p_u}', 0, '{$ac_p_u}', '', ''),
(33, 'ukrainian', '��������� ����', 'ʳ������ ������ �� ��� ���� ���������� ��������� ������ ���� ������ ���������.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', '��������� �������������� �� �����������'),
(34, 'ukrainian', '��� �������', '', '1', 0, '', '{$ser['ab']}', '������ �� ������ ������'),
(35, 'ukrainian', '����������� ������� �������� ����� ��������������', '', '5', 0, '5', '', ''),
(36, 'ukrainian', '������������ ������� ���� � ��������, �� ��� ����� ������������ ������ ������ ��������������', '�������: ���� ����������� ����� �������� ����� �������������� ������� 5, � ������ �������� ������� 15, �� ���������� ���� ������������ � ��� ���� �� ������ 15 ������ ���� 5 �������� ����� �����.', '600', 0, '600', '', ''),

(37, 'ukrainian', '���������� ���������', '����� ����. �������� �����������: * - ����-��� ����������� �������,? - ����-���� ���� ������.', '', 0, '', '', '����������'),
(38, 'ukrainian', '���������� e-mail', '����� ����. �������� �����������: * - ����-��� ����������� �������,? - ����-���� ���� ������.', '', 0, '', '', ''),
(39, 'ukrainian', '��������� �������������� �����������', '', '1', 0, '1', '{$ser['aa']}', '���������'),
(40, 'ukrainian', '����� ���������', 'ʳ������ ����� ��������� ��� ��������� ��������� ������.', '86400', 0, '86400', 'array(''type''=>''number'',''addon''=>array(''min''=>1));', ''),
(41, 'ukrainian', '�� ��������� � �������������� ��������� ��������', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'ukrainian', '�������� ���������?', '', '0', 0, '0', '', ''),
(43, 'ukrainian', '����������� ������� ����', '', '15', 0, '15', '', ''),
(44, 'ukrainian', '̳������� ������� ������', '', '7', 0, '7', '', ''),
(45, 'ukrainian', '������������ ����� ��������������� ������� � KB', '', '307200', 0, '307200', '', '�������'),
(46, 'ukrainian', '������������ ����� �������', '������� ������� � ������: ������[�����]������.', '100 100', 0, '100 100', '', ''),
(47, 'ukrainian', '³��������� ������', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'ukrainian', 'ʳ������ ������� � captcha', '', '5', 0, '5', '', '������������ captcha'),
(49, 'ukrainian', '������ captcha', '������� �� ���������������� � captcha (������ �������� ����� �������, ��� �� 0 - ����� � o - �����)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'ukrainian', '������ captcha', '', '120', 0, '120', '', ''),
(51, 'ukrainian', '������ captcha', '', '60', 0, '60', '', ''),
(52, 'ukrainian', '������ ������� �� ��������', '����������� ��������� ������� �� �������� �� ������.', '5', 0, '5', '', ''),

(53, 'ukrainian', '���-���� ������� ����', '', 'addons/logs/errors.log', 0, '', '', '��������� �������'),
(54, 'ukrainian', '���-���� �� ������������ �������', '', 'addons/logs/exceptions.log', 0, '', '', ''),
(55, 'ukrainian', '���-���� ������� �����', '', 'addons/logs/site_errors.log', 0, '', '', ''),
(56, 'ukrainian', '���-���� ���� ����� (��������� ������)', '', 'addons/logs/db_errors.log', 0, '', '', ''),
(57, 'ukrainian', '��������� ����� �����', '����������� � ���������. ϳ��� ���������� ����� ������, ���� ����������� ������������ Gzip ��� BZip2 �����. ������ 0 ��� ���������� ������������� ���������.', '2097152', 0, '2097152', '', ''),

(58, 'ukrainian', '����� �������� e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', '������� ������������'),
(59, 'ukrainian', 'E-mail ����������', '� ������ ����� ����� ��������� ������?', '{$email}', 0, '{$email}', '', ''),
(60, 'ukrainian', '���������', '', '3', 0, '3', '{$ser['pr']}', ''),
(61, 'ukrainian', 'E-mail ��� ������', '� ���, ���� ������ �� ������������ ��������� �������� �� ����� e-mail, �������� �� ����.', '', 0, '', '', ''),
(62, 'ukrainian', 'E-mail ��� ������������ ��� ����������', '', '', 0, '', '', ''),
(63, 'ukrainian', '����', '����������', '', 0, '', '', '��������� SMTP'),
(64, 'ukrainian', '������', '', '', 0, '', '', ''),
(65, 'ukrainian', '����', '������', '', 0, '', '', ''),
(66, 'ukrainian', '����', '', '25', 0, '25', '', ''),

(67, 'ukrainian', '�������� �� ������������', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(68, 'ukrainian', '��������� �����', '���� � �����. ����� ����.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(69, 'ukrainian', '��������� ����������� ���', '', '*�������*', 0, '*�������*', '', ''),
(70, 'ukrainian', '���������� ������ �� ������ ��������?', '', 'go', 0, 'go', 'array(''options''=>array(''�'',''go''=>''������� ����� go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(71, 'ukrainian', '�������� �������������� �������� � �����?', '��� �������� ���� �����, �� ��������� ���������� �� ����� - ������ �������� �� ���������.', '1', 0, '1', '', ''),

(72, 'ukrainian', '������� RSS', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>'''',)', ''),

(73, 'ukrainian', '������� ���������� ���������', '', '1', 0, '1', '{$ser['or']}', ''),
(74, 'ukrainian', '��������� �� �������', '', '10', 0, '10', '', ''),
(75, 'ukrainian', '��������� ���� �� �����', '������ ������� ������, ���� ���������� ���� ����������� �� ������� �������� / ���������� ��� ��������. ³��� ���� ����������� � ������� ��������� ���������.', '86400', 0, '86400', '', ''),
(76, 'ukrainian', '³��������� �������� ���', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', '�����'),
(77, 'ukrainian', '��������� ��������� �������� ���', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(78, 'ukrainian', '�������� ��������� ����''� ��� �������������� ���������?', '', '1', 0, '1', '', '������ ���������'),
(79, 'ukrainian', '���� ����� ��� ���� ���������� ������', '������� ���� ������ ��� ������� ����� ����������� ������ (����� �������).', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(80, 'ukrainian', '������ ������', '������ 0 ��� ���������� ������� ������ ����������', '200', 0, '200', '', ''),
(81, 'ukrainian', '������ ����''�', '������ 0 ��� ���������� ������� ������ ����������', '0', 0, '0', '', ''),
(82, 'ukrainian', '����� ��������� ����������', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(83, 'ukrainian', '��������� ������ ������ �', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(84, 'ukrainian', '�������� ������� ����?', '�������� ������� ���� �� ������������ ��������?', '1', 0, '1', '', '��������� ����������'),
(85, 'ukrainian', '���� ����� ��� �������� �����', '�� �� ���� ����� ���� ��������� ������� ����. ��������, ��������� ������.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(86, 'ukrainian', '��������� �������� ����� (� �������� �� 0 �� 100)', '100 - �� ����� �������� �����', '50', 0, '50', '', ''),
(87, 'ukrainian', '��������� �� �������� (� �������� �� 0 �� 100)', '', '50', 0, '50', '', ''),
(88, 'ukrainian', '��������� �� ���������� (� �������� �� 0 �� 100)', '', '50', 0, '50', '', ''),
(89, 'ukrainian', '���� �������� �����', '������ ���� �� ������� �� ������, ���� ���� ������������ � ����� �������� ����� (���������: images / watermrak.jpg). ������� �����, �� ������� ���� �� ���� ������������ �� ���������� ���� �� �������� ���� �����, �� ������� ����. �� �������� ��� ������� �������� �����.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(90, 'ukrainian', '����� �������� �����', '��� ����� ���� ��������� �� ���������� � ����� �������� �����, ���� ���������� �������� ����� ����������.', '� {$sitename}', 0, '� {$sitename}', '', ''),
(91, 'ukrainian', '����, ����� � ��� ������ �������� �����', '�������� � ������ red, green, blue, size, angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(92, 'ukrainian', '���������� ���������� � ����� �����?', '��� �������� ���� �����, ��� ����� ����������� ���� ���� ����������� ������ �������, � ���� ������� ����������. ���� �� ���� ���� �������, ���������� �� ����� ����������� ����.', '1', 0, '1', '', '������������ �����'),
(93, 'ukrainian', '���������� ���������� ��� ���?', '��� �������� ���� �����, ����������, IP-������ ����� �� �������� � ������ ����, �� ����� ����������� ����.', '1', 0, '1', '', ''),

(94, 'ukrainian', '������ �����', '���������� ��������� ����� �� ��������� ����� ������ ������������ ��� ��� ��������� ���������.', '{$secret}', 0, '{$secret}', '', ''),
(95, 'ukrainian', '����� ����� ����� ����-������� ���������', '� ��������.', '100', 0, '100', '', ''),

(96, 'ukrainian', '������ ��� �������� ��������?', '', '10', 0, '10', '', ''),
(97, 'ukrainian', '�������� �������������� � ��������', '', '20', 0, '20', '', ''),

(98, 'ukrainian', '�������, �� ���������� �� �������', '������� ������ ��� ��������� �����', '', 0, '', '{$ser['sg']}', ''),

(99, 'ukrainian', '��������� �� �������', '', '10', 0, '10', '', '�������'),
(100, 'ukrainian', '��������� �� ������� � RSS', '', '30', 0, '30', '', ''),
(101, 'ukrainian', '��������� ����� �������������', '', '1', 0, '1', '', ''),
(102, 'ukrainian', '�������� ���� ���������� ��� �������� �������', '', '1', 0, '1', '', ''),
(103, 'ukrainian', '�������� ping', '����������� ��������� ������ ��� ��������� �� ����', '1', 0, '1', '', ''),
(104, 'ukrainian', '�������� �������', '', '1', 0, '1', '', '��������� ��������'),
(105, 'ukrainian', '������ ����� ��� ���������� ��������?', '��������� ��������� ��������� ����� ��� �� ���������� ��������?', '0', 0, '0', '', ''),
(106, 'ukrainian', '������� ����� ��� ������������', '��� �������� ���� �����, ���������� ������ ������ ������� ���� ����������� ����������� � ���� 1 ���.', '0', 0, '0', '', ''),
(107, 'ukrainian', '����� �� ���������� � ����', '���� ���������� ������ ������ �� ���� �����������, ��� � ����, �� ����� ������� ���, �� ��������� ����� ���� ����� ����� ��������� ������.', '3', 0, '3', '', ''),
(108, 'ukrainian', '̳������� ��������� ������', '�������� �� ���� ���� ����� ����. ��� ���������� ���������� ������, ������ 0.', '-3', 0, '-3', '', ''),
(109, 'ukrainian', '����������� ��������� ������', '�������� �� ���� ���� ����� ����. ��� ���������� ���������� ������, ������ 0', '3', 0, '3', '', '')
QUERY;
#[E] Ukrainian
$ser=array(
	'pd'=>'array(
		\'\'options\'\'=>array(
			\'\'ignore\'\'=>\'\'Ignore\'\',
			\'\'redirect\'\'=>\'\'Redirect all links to the primary domain\'\',
			\'\'rel\'\'=>\'\'Add rel canonical\'\',
		),
	)',
	'pg'=>'array(
		\'\'options\'\'=>array(\'\'Hide\'\',\'\'Display only for administrators\'\',\'\'Display for all\'\'),
	)',
	'ab'=>'array(
		\'\'options\'\'=>array(\'\'Disabled\'\',\'\'Limit of attempts within a certain time\'\',\'\'Display the captcha, after exhausting the limit of login attempts\'\'),
	);',
	'pr'=>'array(
		\'\'options\'\'=>array(1=>\'\'Highest\'\',\'\'High\'\',\'\'Normal\'\',\'\'Low\'\',\'\'Lowest\'\'),
	);',
	'aa'=>'array(
		\'\'options\'\'=>array(1=>\'\'Not required\'\',\'\'By e-mail\'\',\'\'Manually by an administrator\'\'),
	)',
	'ua'=>'array(
		\'\'options\'\'=>array(1=>\'\'Delete\'\',\'\'Nothing\'\'),
	)',
	'rp'=>'array(
		\'\'options\'\'=>array(\'\'Disabled\'\',\'\'Allow enter a new password\'\',\'\'Generate and send the password to the e-mail\'\'),
	)',
	'or'=>'array(
		\'\'options\'\'=>array(1=>\'\'Reverse\'\',\'\'Direct\'\'),
	)',
	'cu'=>'array(
		\'\'options\'\'=>array(
			\'\'cut\'\'=>\'\'Crop\'\',
			\'\'small\'\'=>\'\'Reduce\'\',
			\'\'cutsmall\'\'=>\'\'Crop and reduce\'\',
			\'\'smallcut\'\'=>\'\'Reduce and crop\'\',
		),
	)',
	'cf'=>'array(
		\'\'options\'\'=>array(
			\'\'w\'\'=>\'\'Width\'\',
			\'\'h\'\'=>\'\'Height\'\',
			\'\'b\'\'=>\'\'Biggest side\'\',
			\'\'s\'\'=>\'\'Smallest side\'\',
		),
	)',
)+$ser;
#English
if($eng)
	$insert['config_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}config_l` (`id`,`language`,`title`,`descr`,`value`,`serialized`,`default`,`extra`,`startgroup`) VALUES
(1, 'english', 'Primary domain', 'Enter without prefix http://', '{$domain}', 0, '{$domain}', '', 'Domain'),
(2, 'english', 'Support for parked domains', 'When site running on a parked domain', 'redirect', 0, 'redirect', '{$ser['pd']}', ''),
(3, 'english', 'Browser page caching', 'Enter the standard term caching pages in minutes. 0 - disable caching.', '600', 0, '600', '', 'Optimizing workload'),
(4, 'english', 'Enamble GZIP compression?', 'Enabling this option will save bandwidth', '1', 0, '', '', ''),
(5, 'english', 'Cookie lifetime (in days)', '', '31536000', 0, '31536000', '', 'Cookies'),
(6, 'english', 'Cookie domain', 'Use .example.com for the global cookie. Note the dot before the domain name. Instead of "example.com" use your domain name. If enabled parked domain names, instead of the domain name use *.', '.*', 0, '.*', '', ''),
(7, 'english', 'Cookie prefix', 'This option allows you to avoid conflicts if a domain other than the system are located and other scripts that use cookies.', 'el', 0, 'el', '', ''),
(8, 'english', 'Guests group', 'Give guest the rights of group...', 'a:1:{i:0;s:1:"3";}', 1, 'a:1:{i:0;s:1:"3";}', '{$ser['groups']}', 'Permissions by default'),
(9, 'english', 'Track search engine bots?', '', '1', 0, '1', '', ''),
(10, 'english', 'Search engine bots group', 'Give the search bots rights of group...', 'a:1:{i:0;s:1:"4";}', 1, 'a:1:{i:0;s:1:"4";}', '{$ser['groups']}', ''),
(11, 'english', 'List of search engine bots', 'Here, the data is stored on the search bots. Input format: one on each line in the form <b>user agent=bot</ b>.', '{$ser['bots']}', 1, '{$ser['bots']}', '', ''),
(12, 'english', 'Enable multilingual support?', '', '{$multilang}', 0, '{$multilang}', '', 'Localization'),
(13, 'english', 'Timezone by default', '', '{$timezone}', 0, '{$timezone}', '{$ser['tz']}', ''),
(14, 'english', 'Blocked IP addresses', 'Each address - with a new line. Wildcards like 87.183.*.* are enabled, where * is any value. That would indicate a unique reason for the ban imposed after the IP address, put "=" and write the reason. For example:<br />87.183.*.*=Fuck you!', '', 0, '', 'array(''addon''=>array(''style''=>''word-wrap:normal''));', 'Blocking by IP'),
(15, 'english', 'Message to the blocked', 'This message will appear to users that do not enter a reason.', ':-p', 0, ':-p', '', ''),

(16, 'english', 'Site name', '', '{$sitename}', 0, '{$sitename}', '', 'Site headers'),
(17, 'english', 'Separator titles', '', ' - ', 0, ' - ', '', ''),
(18, 'english', 'Site description', '', 'The site is built on a content management system Eleanor', 0, 'The site is built on a content management system Eleanor', '', ''),
(19, 'english', 'Enable static links?', '', '{$furl}', 0, '{$furl}', '', 'Links options'),
(20, 'english', 'Transliterate static links?', 'Static links content and the names of uploaded files will be automatically transliterated.', '0', 0, '', '', ''),
(21, 'english', 'Delimiter parameters', 'In link <q>news<b>/</b>category<b>/</b>news<b>/</b>page_1.html</q>  delimiter parameters is / (slash).\r\nAllowed to enter any non-alphabetic characters.', '/', 0, '/', '', ''),
(22, 'english', 'Delimiter values', 'In exile, <q>news/category/news/page<b>_</b>1.html</q> separator value is lower dash _.\r\nAllowed to enter any non-alphabetic characters.', '_', 0, '_', '', ''),
(23, 'english', 'End of static links', 'In link <q>news/category/news/page_1<b>.html</b></q> ending is <q>.html</ q>.\r\nNote that this field should begin with an alphabetic character is not!', '.html', 0, '.html', '', ''),
(24, 'english', 'Autocorrect invalid characters in static links', 'Value should not coincide with delimiter parameters or delimited values, or with the end of static links and the same should not begin with an alphabetic character.', '-', 0, '-', '', ''),
(25, 'english', 'Module without prefix', 'Links of this module will work without module prefix-identifier', '2', 0, '2', '{$ser['lfm']}', ''),
(26, 'english', 'Turn off the site?', 'Site will be available to groups for which the option view site closing', '0', 0, '0', '', 'Turning site off'),
(27, 'english', 'The reason for the turning off the site', '', '', 0, '', 'array(''type''=>-1)', ''),
(28, 'english', 'Information about the generation of page', 'Information at the bottom of the page containing the speed of page generation, the number of used database queries, GZIP compression status and number of memory consumed.', '2', 0, '2', '{$ser['pg']}', 'Addon information'),
(29, 'english', 'Templates to choose', 'Specify a templates that users can choose as a site design.', 'a:1:{i:0;s:5:"Uniel";}', 1, 'a:1:{i:0;s:5:"Uniel";}', '{$ser['templates']}', 'Others'),

(30, 'english', 'Link to personal cabinet', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_a_e}', 0, '{$ac_a_e}', '', 'Links'),
(31, 'english', 'Link to registration', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_r_e}', 0, '{$ac_r_e}', '', ''),
(32, 'english', 'Link to password recovery', 'Please note that the record <q>param1=value1<b>&</b>param2=value2</q> is incorrect. Correct record is <q>param1=value1<b>&amp;</b>param2=value2</q>', '{$ac_p_e}', 0, '{$ac_p_e}', '', ''),
(33, 'english', 'Duration of sessions', 'Number of seconds during which user considered as online after his last activity.', 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', 1, 'a:4:{s:10:"LoginAdmin";i:900;s:9:"LoginBase";i:900;s:10:"LoginModer";i:300;s:7:"LoginNo";i:10;}', '{$ser['time_online']}', 'The authentication and authorization'),
(34, 'english', 'Protection type', '', '1', 0, '', '{$ser['ab']}', 'Protection against guessing password'),
(35, 'english', 'The maximum number of unsuccessful authentication attempts', '', '5', 0, '5', '', ''),
(36, 'english', 'The maximum amount of time in minutes, during which allowed failed authentication attempts', 'Example: If the maximum number of failed authentication attempts is 5, and the set value is 15, the user will be blocked if the last 15 minutes were 5 failed login attempts.', '600', 0, '600', '', ''),

(37, 'english', 'Blocked nicknames', 'Separated by commas. Allowable special characters: * - any sequence of characters,? - any one character.', '', 0, '', '', 'Bans'),
(38, 'english', 'Blocked e-mail', 'Separated by commas. Allowable special characters: * - any sequence of characters,? - any one character.', '', 0, '', '', ''),
(39, 'english', 'Activation of newly created user', '', '1', 0, '1', '{$ser['aa']}', 'Register'),
(40, 'english', 'Activation term', 'The number of hours allotted to activate account.', '86400', 0, '86400', 'array(''type''=>''number'',''addon''=>array(''min''=>1));', ''),
(41, 'english', 'How to deal with nonactivated accounts', '', '1', 0, '1', '{$ser['ua']}', ''),
(42, 'english', 'Disable registration?', '', '0', 0, '0', '', ''),
(43, 'english', 'The maximum length of nickname', '', '15', 0, '15', '', ''),
(44, 'english', 'The minimum length of password', '', '7', 0, '7', '', ''),
(45, 'english', 'Maximum size of uploaded avatars in KB', '', '307200', 0, '307200', '', 'Profile'),
(46, 'english', 'Maximum size of avatar', 'Need to enter in the format: width[space]height.', '100 100', 0, '100 100', '', ''),
(47, 'english', 'Password recovery', '', '1', 0, '1', '{$ser['rp']}', ''),

(48, 'english', 'Number of characters in captcha', '', '5', 0, '5', '', 'Captcha options'),
(49, 'english', 'Captcha alphabet', 'Symbols used in the captcha (preferably exclude similar characters like 0 - number and o - letter)', '23456789abcdeghkmnpqsuvxyz', 0, '23456789abcdeghkmnpqsuvxyz', '', ''),
(50, 'english', '�aptcha width', '', '120', 0, '120', '', ''),
(51, 'english', 'Captcha height', '', '60', 0, '60', '', ''),
(52, 'english', 'The scatter symbols on the vertical', 'The maximum deviation of the symbols on the vertical line from the center.', '5', 0, '5', '', ''),

(53, 'english', 'Log file code errors', '', 'addons/logs/errors.log', 0, '', '', 'Errors log'),
(54, 'english', 'Log file uncaught exceptions', '', 'addons/logs/exceptions.log', 0, '', '', ''),
(55, 'english', 'Log-file site errors', '', 'addons/logs/site_errors.log', 0, '', '', ''),
(56, 'english', 'Log-file database (including queries)', '', 'addons/logs/db_errors.log', 0, '', '', ''),
(57, 'english', 'Limit file size', 'Specified in kilobytes. After reaching this size, the file is automatically packaged Gzip or BZip2 archive. Specify 0 to disable automatic compression.', '2097152', 0, '2097152', '', ''),

(58, 'english', 'Way to send e-mail', '', 'mail', 0, 'mail', 'array(''options''=>array(''php''=>''PHP mail'',''smtp''=>''SMTP'',))', 'General settiongs'),
(59, 'english', 'Sender e-mail', 'From what email letters will send?', '{$email}', 0, '{$email}', '', '����� ���������'),
(60, 'english', 'Importance', '', '3', 0, '3', '{$ser['pr']}', ''),
(61, 'english', 'E-mail for response', 'If the response from the user should be taken to another e-mail, fill out this field.', '', 0, '', '', ''),
(62, 'english', 'E-mail to confirm a read', '', '', 0, '', '', ''),
(63, 'english', 'Login', 'User', '', 0, '', '', 'SMTP settings'),
(64, 'english', 'Password', '', '', 0, '', '', ''),
(65, 'english', 'Host', 'Server', '', 0, '', '', ''),
(66, 'english', 'Port', '', '25', 0, '25', '', ''),

(67, 'english', 'Editor by default', '', 'bb', 0, 'bb', 'array(''eval''=>''return Eleanor::getInstance()->Editor->editors;'')', ''),
(68, 'english', 'Swear words', 'Mats and abuse. Separated by commas.', 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', 0, 'slaed, slaed cms, Edmann, DiFor, zigmat, peter911', '', ''),
(69, 'english', 'Autocorrect banned words', '', '*Censorship*', 0, '*Censorship*', '', ''),
(70, 'english', 'Enable protection from direct links?', '', 'bb', 0, 'bb', 'array(''options''=>array(''no'',''go''=>''Redirect via go.php'',''nofollow''=>''rel="nofollow"'',))', ''),
(71, 'english', 'Enable autoparse links in the text?', 'If you enable this option, all links published as text - will be treated as links.', '1', 0, '1', '', ''),

(72, 'english', 'RSS logo', '', 'images/rss.png', 0, 'images/rss.png', 'array(''path''=>''uploads/'',''types''=>array(0=>''jpeg'',1=>''jpg'',2=>''png'',3=>''bmp'',4=>''gif'',),''max_size''=>''307200'',''filename_eval''=>'''',)', ''),

(73, 'english', 'Order displaying comments', '', '1', 0, '1', '{$ser['or']}', ''),
(74, 'english', 'Comments per page', '', '10', 0, '10', '', ''),
(75, 'english', 'Limitation of time changes', 'Enter the number of seconds after which users can not delete / edit your comments. The countdown is carried out since the publication of comments.', '86400', 0, '86400', '', ''),
(76, 'english', 'Display comments for', '', 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', 1, 'a:6:{i:0;s:1:"4";i:1;s:1:"1";i:2;s:1:"3";i:3;s:1:"6";i:4;s:1:"5";i:5;s:1:"2";}', '{$ser['groups']}', 'Rights'),
(77, 'english', 'Post comments available for', '', 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', 1, 'a:2:{i:0;s:1:"1";i:1;s:1:"2";}', '{$ser['groups']}', ''),

(78, 'english', 'Enable the creation of thumbs for uploaded images?', '', '1', 0, '1', '', 'Preview images'),
(79, 'english', 'File types for which to create thumbs', 'Specify the file types for which to create a preview (separated by comma).', 'png,jpg,bmp', 0, 'png,jpg,bmp', '', ''),
(80, 'english', 'Thumb width', 'Enter 0 to keep the original width of the image', '200', 0, '200', '', ''),
(81, 'english', 'Height preview', 'Enter 0 to keep the original height of the image', '0', 0, '0', '', ''),
(82, 'english', 'Method of reducing the image', '', 'small', 0, 'small', '{$ser['cu']}', ''),
(83, 'english', 'Creating a thumb begin with', '', 'b', 0, 'b', '{$ser['cf']}', ''),
(84, 'english', 'Enable a watermark?', 'Apply a watermark to your uploaded images?', '1', 0, '1', '', '��������� ����������'),
(85, 'english', 'Filetypes for watermark', 'These types of files will be put watermark. Specify, separated by commas.', 'jpg,jpeg,png,bmp', 0, 'jpg,jpeg,png,bmp', '', ''),
(86, 'english', 'Transparency of the watermark (as a percentage from 0 to 100)', '100 - not visible watermark', '50', 0, '50', '', ''),
(87, 'english', 'Vertical position (as a percentage from 0 to 100)', '', '50', 0, '50', '', ''),
(88, 'english', 'Horizontal position (as a percentage from 0 to 100)', '', '50', 0, '50', '', ''),
(89, 'english', 'File of the watermark', 'Enter the path to the picture on the server, which will be used as a watermark (eg: images / watermrak.jpg). Please note that the watermark will not be applied to the image if the size is less than the watermark. Takes precedence over the text watermark.', 'images/watermark.png', 0, 'images/watermark.png', '', ''),
(90, 'english', 'Text watermark', 'This text will be superimposed on the image as a watermark, if the watermark image is not available.', '� {$sitename}', 0, '� {$sitename}', '', ''),
(91, 'english', 'Color, size and angle of text watermark', 'Given in the format of red, green, blue, size, angle', '1,1,1,15,0', 0, '1,1,1,15,0', '', ''),
(92, 'english', 'Prohibit downloads from other sites?', 'When this option when you try to download the file will be checked to indicate the address to which the user came from. If it is someone else''s page, the user can not download the file.', '1', 0, '1', '', 'Downloading files'),
(93, 'english', 'Prohibit downloading without session?', 'When this option is enabled, the user, IP address is not on the list of sessions will not be able to download the file.', '1', 0, '1', '', ''),

(94, 'english', 'Site secret', 'A random secret string with which to sign the data for cross-domain switching.', '{$secret}', 0, '{$secret}', '', ''),
(95, 'english', 'The life of these cross-domain switching', 'In seconds.', '100', 0, '100', '', ''),

(96, 'english', 'Days to keep drafts?', '', '10', 0, '10', '', ''),
(97, 'english', 'Autosave interval in seconds', '', '20', 0, '20', '', ''),

(98, 'english', 'The pages displayed on the main', 'Leave empty to display contents', '', 0, '', '{$ser['sg']}', ''),

(99, 'english', 'Publications per page', '', '10', 0, '10', '', 'General'),
(100, 'english', 'Publications per page in RSS', '', '30', 0, '30', '', ''),
(101, 'english', 'Adding news by users', '', '1', 0, '1', '', ''),
(102, 'english', 'Display the contents of subcategories when viewing category', '', '1', 0, '1', '', ''),
(103, 'english', 'Enable ping', 'Notification search engines about updating on the site', '1', 0, '1', '', ''),
(104, 'english', 'Enable rating', '', '1', 0, '1', '', 'Rating options'),
(105, 'english', 'Score only in the detailed view?', 'Allow to assess publication only when they are detailed viewing?', '0', 0, '0', '', ''),
(106, 'english', 'Rating users only', 'When this option is on, rate news can only authorized users and only 1 time.', '0', 0, '0', '', ''),
(107, 'english', 'Period between marks in days', 'If rate can not only users but also the guests, this option specifies the time after which guests will be able to remark.', '3', 0, '3', '', ''),
(108, 'english', 'Low negative mark', 'Value can not be greater than zero. To turn off the negative marks, enter 0.', '-3', 0, '-3', '', ''),
(109, 'english', 'The maximum positive mark', 'Value can not be below zero. To disable the positive ratings, please enter 0.', '3', 0, '3', '', '')
QUERY;
#[E] English

$insert['errors']=<<<QUERY
INSERT INTO `{$prefix}errors` VALUES
(1, 404, 'warning.png', '', 1),
(2, 403, 'hand.png', '', 1)
QUERY;

#Russian
if($rus)
	$insert['errors_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'russian', '404', '�������� �� �������!', '��������, ������� �� ���������, �� ���������� ���� ��� �������� �� ��������.<br /><br />��������, �� ������� �� ���������� ������ � ������ �������� ��� �������� ��������, ������� ����� �������.','','',NOW()),
(2, 'russian', '403', '������ ��������!', '��� �������� ������ � ���� ��������!','','',NOW())
QUERY;
#[E] Russian

#English
if($eng)
	$insert['errors_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'english', '404', 'Page not found!', 'The page you have requested does not exist or is temporarily unavailable.','','',NOW()),
(2, 'english', '403', 'Access denied!', 'You haven''t permisson to visit this page!','','',NOW())
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['errors_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}errors_l` VALUES
(1, 'ukrainian', '404', '������� �� ��������!', '�������, ��� �� ���������, �� ���� ��� ���� ��������� �� ��������.<br /><br />�������, �� �������� �� ���������� ��������� � ���� ������� ��� ��������� ����������, ��������� ������ ������.','','',NOW()),
(2, 'ukrainian', '403', '������ ����������!', '��� ����������� ������ �� ���� �������!','','',NOW())
QUERY;
#[E] Ukrainian

$insert['mainpage']=<<<QUERY
INSERT INTO `{$prefix}mainpage` VALUES (1,1)
QUERY;

$insert['menu']=<<<QUERY
INSERT INTO `{$prefix}menu` (`id`,`pos`,`parents`,`in_map`,`status`) VALUES
(1, 1, '', 1, 1),
(2, 2, '', 1, 1),
(3, 3, '', 1, 1),
(4, 4, '', 1, 1),
(5, 5, '', 1, 1),
(6, 6, '', 1, 1),
(7, 7, '', 1, 1),
(8, 1, '7,', 1, 1),
(9, 2, '7,', 1, 1),
(10, 3, '7,', 1, 1)
QUERY;

#Russian
if($rus)
	$insert['menu_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'russian', '������ �������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������''),false,false);', ''),
(2, 'russian', '�������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������''),false,false);', ''),
(3, 'russian', '�����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�����''),false);', ' rel="search"'),
(4, 'russian', '����� �����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''����� �����''),false);', ''),
(5, 'russian', '����������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''��������''),false,false);', ''),
(6, 'russian', '�������� �����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������� �����''),false);', ' rel="contact"'),
(7, 'russian', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'russian', '����������� ���� Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(9, 'russian', '����� ���������', 'http://eleanor-cms.ru/%D1%84%D0%BE%D1%80%D1%83%D0%BC/', '', ''),
(10, 'russian', 'Eleanor Server', 'http://eleanor-cms.ru/%D1%81%D0%B5%D1%80%D0%B2%D0%B5%D1%80/', '', '')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['menu_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'english', 'Personal cabinet', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''account''),false,false);', ''),
(2, 'english', 'News', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''news''),false,false);', ''),
(3, 'english', 'Search', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''search''),false);', ' rel="search"'),
(4, 'english', 'Sitemap', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''sitemap''),false);', ''),
(5, 'english', 'Information', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''pages''),false,false);', ''),
(6, 'english', 'Contacts', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''contacts''),false);', ' rel="contact"'),
(7, 'english', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'english', 'Official site Eleanor CMS', 'http://eleanor-cms.ru/eng/', '', ''),
(9, 'english', 'Supporting forum', 'http://eleanor-cms.ru/eng/forum/', '', ''),
(10, 'english', 'Eleanor Server', 'http://eleanor-cms.ru/eng/server/', '', '')
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['menu_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}menu_l` (`id`, `language`, `title`, `url`, `eval_url`, `params`) VALUES
(1, 'ukrainian', '��������� ������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������''),false,false);', ''),
(2, 'ukrainian', '������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''������''),false,false);', ''),
(3, 'ukrainian', '�����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�����''),false);', ' rel="search"'),
(4, 'ukrainian', '���� �����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''���� �����''),false);', ''),
(5, 'ukrainian', '����������', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������''),false,false);', ''),
(6, 'ukrainian', '�������� ��''����', '', 'return\$Eleanor->Url->special.\$Eleanor->Url->Construct(array(''module''=>''�������� ��\\\\''����''),false);', ' rel="contact"'),
(7, 'ukrainian', 'Eleanor CMS', 'http://eleanor-cms.ru', '', ''),
(8, 'ukrainian', '��������� ���� Eleanor CMS', 'http://eleanor-cms.ru/%D1%83%D0%BA%D1%80/', '', ''),
(9, 'ukrainian', '����� ��������', 'http://eleanor-cms.ru/%D1%83%D0%BA%D1%80/%D1%84%D0%BE%D1%80%D1%83%D0%BC/', '', ''),
(10, 'ukrainian', 'Eleanor Server', 'http://eleanor-cms.ru/%D1%83%D0%BA%D1%80/%D1%81%D0%B5%D1%80%D0%B2%D0%B5%D1%80/', '', '')
QUERY;
#[E]Ukrainian

$ser=array(
	1=>array(
		serialize(array(
			'news'=>array(
				'russian'=>array('�������','news'),
				'english'=>array('news'),
				'ukrainian'=>array('������','news'),
			),
		)),
		serialize(array('russian'=>'�������','english'=>'News','ukrainian'=>'������')),
		serialize(array('russian'=>'���������� ��������� ������ �����','english'=>'Management news your site','ukrainian'=>'��������� �������� ������ �����')),
	),
	array(
		serialize(array(
			'static'=>array(
				'russian'=>array('��������','pages'),
				'english'=>array('pages'),
				'ukrainian'=>array('�������','pages'),
			),
		)),
		serialize(array('russian'=>'����������� ��������','english'=>'Static pages','ukrainian'=>'������� �������')),
		serialize(array('russian'=>'������ ��� �������� ����������� �������','english'=>'Module for configurating static pages','ukrainian'=>'������ ��� ��������� ��������� �������')),
	),
	array(
		serialize(array(
			'section'=>array(
				''=>array('_mainpage'),
			),
		)),
		serialize(array('russian'=>'������� ��������','english'=>'Main page','ukrainian'=>'������� �������')),
		serialize(array('russian'=>'����������� ������� �������� �����','english'=>'Constructor homepage site','ukrainian'=>'����������� ������� ������� �����')),
	),
	array(
		serialize(array(
			'errors'=>array(
				'russian'=>array('������','errors'),
				'english'=>array('errors'),
				'ukrainian'=>array('�������','errors'),
			),
		)),
		serialize(array('russian'=>'�������� ������','english'=>'Error pages','ukrainian'=>'������� �������')),
		serialize(array('russian'=>'��������� ������� ������ ������ ����� (404,403,...)','english'=>'Configuring error pages your site (404,403, etc...)','ukrainian'=>'������������ ������� ������� ������ ����� (404,403,...)')),
	),
	array(
		str_replace('\'','\'\'',serialize(array(
			'contacts'=>array(
				'russian'=>array('�������� �����','contacts'),
				'english'=>array('contacts'),
				'ukrainian'=>array('�������� ��\'����','contacts'),
			),
		))),
		serialize(array('russian'=>'�������� �����','english'=>'Feedback','ukrainian'=>'�������� ��&#039;����')),
		serialize(array('russian'=>'��������� �������� �����','english'=>'Settings of feedback','ukrainian'=>'������������ ����������� ��&#039;����')),
	),
	array(
		serialize(array(
			'search'=>array(
				'russian'=>array('�����','search'),
				'english'=>array('search'),
				'ukrainian'=>array('�����','search'),
			),
		)),
		serialize(array('russian'=>'Google �����','english'=>'Google search','ukrainian'=>'Google �����')),
	),
	array(
		serialize(array(
			'menu'=>array(
				'russian'=>array('����� �����','����','menu','sitemap'),
				'english'=>array('sitemap','menu'),
				'ukrainian'=>array('���� �����','����','menu','sitemap'),
			),
		)),
		serialize(array('russian'=>'���� �����','english'=>'Menu','ukrainian'=>'���� �����')),
	),
	array(
		serialize(array(
			'account'=>array(
				'russian'=>array('�������','account'),
				'english'=>array('account'),
				'ukrainian'=>array('�������','account'),
			),
			'groups'=>array(
				'russian'=>array('������','groups'),
				'english'=>array('groups'),
				'ukrainian'=>array('�����','groups'),
			),
			'user'=>array(
				'russian'=>array('������������','user'),
				'english'=>array('user'),
				'ukrainian'=>array('user','����������'),
			),
			'online'=>array(
				'russian'=>array('���-������','online'),
				'english'=>array('online'),
				'ukrainian'=>array('���-������','online'),
			),
		)),
		serialize(array('russian'=>'������� ������������','english'=>'User account','ukrainian'=>'������� �����������')),
	),
	array(
		serialize(array(
			'context'=>array(
				'russian'=>array('����������� ������','context links'),
				'english'=>array('context links'),
				'ukrainian'=>array('��������� ���������','context links'),
			),
		)),
		serialize(array('russian'=>'����������� ������','english'=>'�ontext links','ukrainian'=>'��������� ���������')),
	),
);
$insert['modules']=<<<QUERY
INSERT INTO `{$prefix}modules` (`services`,`sections`,`title_l`,`descr_l`,`protected`,`path`,`multiservice`,`file`,`files`,`image`,`active`,`api`) VALUES
(',ajax,,admin,,cron,,user,,rss,,xml,', '{$ser[1][0]}', '{$ser[1][1]}', '{$ser[1][2]}', 0, 'modules/news', 1, 'index.php', 'a:4:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:4:"ajax";s:8:"ajax.php";s:3:"rss";s:7:"rss.php";}', 'news-*.png', 1, 'api.php'),
(',admin,,user,,rss,,download,', '{$ser[2][0]}', '{$ser[2][1]}', '{$ser[2][2]}', 1, 'modules/static', 1, 'index.php', '', 'static-*.png', 1, 'api.php'),
(',admin,,user,', '{$ser[3][0]}', '{$ser[3][1]}', '{$ser[3][2]}', 1, 'modules/mainpage', 1, 'index.php', '', 'mainpage-*.png', 1, ''),
(',admin,,user,', '{$ser[4][0]}', '{$ser[4][1]}', '{$ser[4][2]}', 1, 'modules/errors', 1, 'index.php', '', 'errors-*.png', 1, 'api.php'),
(',admin,,user,', '{$ser[5][0]}', '{$ser[5][1]}', '{$ser[5][2]}', 0, 'modules/contacts', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'contacts-*.png', 1, ''),
(',admin,,user,,xml,', '{$ser[6][0]}', '{$ser[6][1]}', 'a:0:{}', 0, 'modules/search', 0, 'index.php', 'a:3:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:3:"xml";s:7:"xml.php";}', '', 1, ''),
(',admin,,user,', '{$ser[7][0]}', '{$ser[7][1]}', 'a:0:{}', 0, 'modules/menu', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'menu-*.png', 1, ''),
(',admin,,user,,ajax,', '{$ser[8][0]}', '{$ser[8][1]}', 'a:0:{}', 0, 'modules/account', 1, 'index.php', 'a:3:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";s:4:"ajax";s:8:"ajax.php";}', 'account-*.png', 1, 'api.php'),
(',admin,', '{$ser[9][0]}', '{$ser[9][1]}', 'a:0:{}', 0, 'modules/context-links', 1, 'index.php', 'a:2:{s:5:"admin";s:9:"admin.php";s:4:"user";s:9:"index.php";}', 'links-*.png', 1, '')
QUERY;

$ndate=date('Y-m-d H:i:s');
$insert['news']=<<<QUERY
INSERT INTO `{$prefix}news` (`id`,`cats`,`date`,`pinned`,`author`,`author_id`,`show_detail`,`show_sokr`,`status`,`voting`) VALUES
(1, ',1,', '{$ndate}' + INTERVAL 1 MONTH, '{$ndate}' + INTERVAL 2 SECOND, 'Eleanor CMS', 0, 1, 1, 1, 1),
(2, ',1,', '{$ndate}' + INTERVAL 1 SECOND, 0, 'Eleanor CMS', 0, 0, 1, 1, 0),
(3, ',1,', '{$ndate}', 0, 'Eleanor CMS', 0, 0, 1, 1, 0)
QUERY;

$insert['news_categories']=<<<QUERY
INSERT INTO `{$prefix}news_categories` (`id`,`image`,`pos`) VALUES (1, 'bomb.png', 1)
QUERY;

#Russian
if($rus)
	$insert['news_categories_l(rus)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`) VALUES
(1, 'russian', '����-�������', '���� �������', '�������� ��������� ��������')
QUERY;
#[E] Russian

#English
if($eng)
	$insert['news_categories_l(eng)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`) VALUES
(1, 'english', 'our-news', 'Our news', 'News test category')
QUERY;
#[E] English

#Ukrainian
if($ukr)
	$insert['news_categories_l(ukr)']=<<<QUERY
INSERT INTO `{$prefix}news_categories_l` (`id`,`language`,`uri`,`title`,`description`) VALUES
(1, 'ukrainian', '����-������', '���� ������', '������� �������� �����')
QUERY;
#[E] Ukrainian

$version=ELEANOR_VERSION;
$insert['news_l']=<<<QUERY
INSERT INTO `{$prefix}news_l` (`id`,`uri`,`lstatus`,`ldate`,`lcats`,`title`,`announcement`,`text`,`last_mod`) VALUES
(1, 'eleanor-cms', 1, '{$ndate}' + INTERVAL 2 SECOND, ',1,', 'Eleanor CMS {$version}', '���������� ��� �� ����������� Eleanor CMS {$version}. �� ��������, ��� ������ � Eleanor CMS ������� � ��� ������ ������������� ������. ���� �� � ��� ��������� �����-���� �������, ���������, ��� �� �� ������ ������ � �������, �� �� ��� � �������� ��������� �� ����������� ������ ������� <a href="http://forum.eleanor-cms.ru" target="_blank">forum.eleanor-cms.ru</a>', '<br /><br />������������ ������:', '{$ndate}'),
(2, 'netlevel-�������-�������-���-eleanor-cms', 1, '{$ndate}' + INTERVAL 1 SECOND, ',1,', 'NetLevel - ������� ������� ��� Eleanor CMS', '<div style="text-align:center"><img src="uploads/news/2/netlevel_logo.png" alt="NetLevel" title="NetLevel" /></div><br />NetLevel.ru �������� ����������� �������� ������� ���������� ������� Eleanor CMS. ��� �������� �����, ����� �� ����� ������ �������� �������� ����������� ��� ����������, ������� � ���������� ������ � ���� ��������. ��������� �������������� ������� NetLevel ��������:<br /><br /><ul>\r\n<li>������ ������������� � Eleanor CMS � ���������� ���������;</li><li>������ � ����������� �����, ��������� � Eleanor CMS;</li><li>������� ������������, �������� � ������������;</li><li>����������� ��������� 24/7/365;</li><li>������� ������ �����.</li></ul><br /><br />� ����������� ��������� ���������� � ������� � ������.', '[html]\r\n������:<br /><br />\r\n<b>1. ����������� ������� � ������</b><br />\r\n������ ��������������� ���������� �����, ����� �������� ����������� ������ � ����� �� �������������� ���� ���. �� ������������� ������ ������������ �������� �� ������ �������� � ������ ����������� ���� � �������������� �������� ���������� nginx, ������ ���������� CPanel � ���������� ���� ����������� ����������, ������������ � CMS-��������.<br />\r\n<a href="http://www.netlevel.ru/hosting" target="_blank">��������� � ����������� ��������</a><br />\r\n<a href="http://www.netlevel.ru/domains" target="_blank">��������� � ����������� �������</a><br /><br />\r\n<b>2. ���������� � ����������� �������</b><br />\r\n����������� (VPS) � ���������� ������� - ��������� ������� ��� ���������� �����, ������� ��������������� ��������� ��������������� �������� � ������� �����������������. ����� ������� �� ������ ���������� ������� ���������� ������, ��������� �������� ��� ����� �������� ��� ������ � ����� ������ root-������ � ������ ������� ��� ��������� ������ �� � ��������� ����� ���������� ��.<br />\r\n<a href="http://www.netlevel.ru/vps-servers" target="_blank">��������� � ����������� �������� (VPS/VDS)</a><br />\r\n<a href="http://www.netlevel.ru/dedicated-servers" target="_blank">��������� � ���������� ��������</a><br /><br />\r\n<b>3. ����������������� � ����������</b><br />\r\n����������� ����� ��������, ��������� � ������������, ���������� ��������������� ��, �������� �������. �������� ���������� � ������� �����������������. ������� ����������������� �������� ����������� ���������� ����������� ����� � �������� � �������������� ������. �������� - ��������� � ���������������� ����������� ������� ������ �� DDoS ����, ��������� �������� ������ �������, ������ � ���������� ������ ������������ � �.�. ���������� (�������������) ����������������� ��������������� ���������� ����� �� �������, � ����� ���������� ��������� ������� � ������� ������� � ������ �������������. �������� - ������������� ���������� ����������� �� � ��������� ������ ���������� � ������ ������������, ���������� ��������� ����� � �.�. ������������ ��������� � ������ ��������� ����������������� � ������, ����� ��������������� ������ ����.<br />\r\n<a href="http://www.netlevel.ru/administration/permanent" target="_blank">���������� ����������������� � ����������</a><br />\r\n<a href="http://www.netlevel.ru/administration/one-time" target="_blank">������� �����������������</a><br /><br />\r\n\r\n����� �������� ������������ � ���������������� ���� �������� ����� �� ����� �����, ��� �� ����� ��������� � ���� � ������ ��� ������������ ��� �������.<br />\r\n<a href="http://www.netlevel.ru/" target="_blank">������� �� ����</a>\r\n[/html]', '{$ndate}'),
(3, 'centroarts', 1, '{$ndate}', ',1,', 'Centroarts', '<div style="text-align:center"><img src="uploads/news/3/centroarts.png" alt="Centroarts" title="Centroarts" /></div><br />[html]<p>��������� �� �������� ����� ��� ������� Eleanor CMS �������� ������ <a href="http://centroarts.com">CENTROARTS.com</a>. �������������, ��� ��� ������� �� ������ �������� ������, �� �������� �� �������� ���������� ������ �� ����� centroarts.com.</p>\r\n<p>&nbsp;</p>[/html]', '[html]\r\n<h3>������ ����������.<br /></h3>\r\n<p><img class="left" title="�������" src="uploads/news/3/ca_template.png" alt="�������" width="90" height="92" />���������� ����������� ������� ��� ���������� Eleanor CMS. ������ �������� �� ��, ��� ����� ��������� ��� ����. � ������� ����������� ������������ � ���������� ������, ���������, ������, � ��. � ������� ����� ������ ����������� ����� ����������� ����������, ��������, ������� �������� ����� ���������� �� ������� � ���������. ������ �� �������� � ���� ���������� ��������� �����, �������, � �.�. ������ ������������ ����� �������� ��������������� ������ ��� ������ ����� �� ���� Eleanor CMS, �������� ������� HTML+CSS.</p>\r\n<p><a href="http://centroarts.com/service/template.html" target="_blank"><strong>�������� ������</strong></a></p>\r\n<br />\r\n<h3>�������� ����� �� ���� Eleanor CMS.<br /></h3>\r\n<img class="left" title="Web-���� �� ���� Eleanor CMS" src="uploads/news/3/ca_site.png" alt="Web-���� �� ���� Eleanor CMS" width="90" height="92" />�������� ����� �� ���� Eleanor CMS. ����� ���������� ������� ��� �����, ��������������� ����� ��������� �����, ����������� ��������� ����������� �������, �� ��������� �������� ������� ������ �� �������� ���������� ����������� ������� ��� �������������� ������������ ������� � ���������������� �������. Eleanor CMS ��������� ������������� ��� ���������� ������, ������������ ����������.<br /><br /><strong><a href="http://centroarts.com/service/website.html" target="_blank">�������� ������</a></strong><br /><br /><br />\r\n<h3>���������� �������� ��� Eleanor CMS.</h3>\r\n<img class="left" title="���������� �������� ��� Eleanor CMS" src="uploads/news/3/ca_scripts.png" alt="���������� �������� ��� Eleanor CMS" width="90" height="92" />���������� php-�������� ��� Eleanor CMS. PHP-������� - ��� ����������� ����� �����, ������� ��������� ��������� ���������� ������ �����. �������� ���������������� ���� ��������: �����, ������. ���� - ��� ����� ���������� (��������, �������������� ����). ������ - ��� ����������� ������� ��� ���������� ������������� ������� �� ����� (��������, �����������). ���������� PHP �������� ������� �� �������������� ������� � ������ ���� ����������� ������� � ������������ � ����� ������������.<br /><br /><a href="http://centroarts.com/service/script.html" target="_blank"><strong>�������� ������</strong></a><br /><br /><br />\r\n<h3>���������� ������ ��� ������ �����.</h3>\r\n<img class="left" title="���������� ������ ��� �����" src="uploads/news/3/ca_icons.png" alt="���������� ������ ��� �����" width="90" height="92" />�� ����������� ���� ������ �������� ������������ ������ ��������, ���-������, �����������. ������ - ��� ��������� � ������� �������� ��� ���������� ����������, ������� �� ����� ���������� � �������� ��������� ���������� ��� �������� ������� � �������� ���������. ���������� ������ ������� � �������������� �������. �������� �������� ������ ����� ��������� �� ������� �������-��������� ������ - �� ��������, ��������-������������� ������. ���������������� ������� ������: 16x16px, 32x32px, 48x48px, 64x64px � 128x128px.<br /><br /><strong><a href="http://centroarts.com/service/icons.html" target="_blank">�������� ������</a><br /></strong>\r\n<p>&nbsp;</p>\r\n<ul>\r\n<li>��������� ������ �� ������ ���������� �� ������: <a href="http://centroarts.com/portfolio/">http://centroarts.com/portfolio.html</a></li>\r\n<li>��������� ���������� �� ������� ����� ���������� �� ������: <a href="http://centroarts.com/info/">http://centroarts.com/info.html</a></li>\r\n</ul>\r\n[/html]', '{$ndate}')
QUERY;

$insert['ownbb']=<<<QUERY
INSERT INTO `{$prefix}ownbb` (`pos`,`active`,`handler`,`tags`,`no_parse`,`special`,`sp_tags`,`gr_use`,`gr_see`,`sb`) VALUES
(1, 1, 'url.php', 'url', 0, 0, '', '', '', 0),
(2, 1, 'nobb.php', 'nobb', 1, 0, '', '', '', 0),
(3, 1, 'code.php', 'code', 1, 0, 'csel', '', '', 1),
(4, 1, 'hide.php', 'hide', 0, 0, '', '', '1,4,2,3', 1),
(5, 1, 'quote.php', 'quote', 0, 0, '', '', '', 1),
(6, 1, 'script.php', 'script', 1, 0, '', '1', '', 1),
(7, 1, 'php.php', 'php', 1, 0, '', '1', '', 1),
(8, 1, 'html.php', 'html', 1, 0, '', '1', '', 1),
(9, 1, 'attach.php', 'attach', 1, 0, '', '', '', 0),
(10, 1, 'csel.php', 'csel', 0, 1, '', '', '', 1),
(11, 1, 'onlinevideo.php', 'onlinevideo', 1, 0, '', '', '', 1),
(12, 1, 'spoiler.php', 'spoiler', 0, 0, '', '', '', 1)
QUERY;

$insert['services']=<<<QUERY
INSERT INTO `{$prefix}services` VALUES
('admin', 'admin.php', 1, 'Audora', 'admin'),
('user', 'index.php', 2, 'Uniel', 'base'),
('ajax', 'ajax.php', 1, '', 'no'),
('upload', 'upload.php', 2, '', 'no'),
('download', 'download.php', 2, '', 'no'),
('rss', 'rss.php', 0, '', 'no'),
('cron', 'cron.php', 0, '', 'no'),
('xml', 'xml.php', 0, 'xml', 'no'),
('moder', 'moder.php', 0, '', 'moder');
QUERY;

$insert['smiles']=<<<QUERY
INSERT INTO `{$prefix}smiles` (`path`,`emotion`,`status`,`show`,`pos`) VALUES
('images/smiles/alien.png', ',:alien:,', 1, 1, 0),
('images/smiles/andy.png', ',:andy:,', 1, 1, 1),
('images/smiles/angel.png', ',:angel:,', 1, 1, 2),
('images/smiles/angry.png', ',:angry:,', 1, 1, 3),
('images/smiles/bandit.png', ',:bandit:,', 1, 1, 4),
('images/smiles/blushing.png', ',:blushing:,', 1, 1, 5),
('images/smiles/cool.png', ',:cool:,', 1, 1, 6),
('images/smiles/crying.png', ',:crying:,', 1, 1, 7),
('images/smiles/devil.png', ',:devil:,', 1, 1, 8),
('images/smiles/grin.png', ',:D,', 1, 1, 9),
('images/smiles/happy.png', ',:happy:,', 1, 1, 10),
('images/smiles/heart.png', ',:heart:,', 1, 1, 11),
('images/smiles/joyful.png', ',:joyful:,', 1, 1, 12),
('images/smiles/kissing.png', ',:kissing:,', 1, 1, 13),
('images/smiles/lol.png', ',:lol:,', 1, 1, 14),
('images/smiles/love.png', ',:love:,', 1, 1, 15),
('images/smiles/ninja.png', ',:ninja:,', 1, 1, 16),
('images/smiles/pinched.png', ',:pinched:,', 1, 1, 17),
('images/smiles/policeman.png', ',:policeman:,', 1, 1, 18),
('images/smiles/pouty.png', ',:pouty:,', 1, 1, 19),
('images/smiles/sad.png', ',:sad:,', 1, 1, 20),
('images/smiles/sick.png', ',:sick:,', 1, 1, 21),
('images/smiles/sideways.png', ',:sideways:,', 1, 1, 22),
('images/smiles/sleeping.png', ',:sleeping:,', 1, 1, 23),
('images/smiles/smile.png', ',:),', 1, 1, 24),
('images/smiles/surprised.png', ',:surprised:,', 1, 1, 25),
('images/smiles/tongue.png', ',:tongue:,', 1, 1, 26),
('images/smiles/uncertain.png', ',:uncertain:,', 1, 1, 27),
('images/smiles/unsure.png', ',:unsure:,', 1, 1, 28),
('images/smiles/w00t.png', ',:w00t:,', 1, 1, 29),
('images/smiles/whistling.png', ',:whistling:,', 1, 1, 30),
('images/smiles/wink.png', ',:wink:,', 1, 1, 31),
('images/smiles/wizard.png', ',:wizard:,', 1, 1, 32),
('images/smiles/wondering.png', ',:wondering:,', 1, 1, 33)
QUERY;

$insert['upgrade_hist']="INSERT INTO `{$prefix}upgrade_hist` VALUES (1, '".ELEANOR_VERSION."', NOW(), '".ELEANOR_BUILD."', 1, 'Install')";
$insert['users_site']="INSERT INTO `{$prefix}users_site` (`id`) VALUES (0)";

$ser=array(
	1=>serialize(array('russian'=>'������� �������','english'=>'Daytime cleaning','ukrainian'=>'������� �������')),
	serialize(array('russian'=>'������� ping','english'=>'Daytime ping','ukrainian'=>'�������� ping')),
);

$dateo=date_offset_get(date_create());
$insert['tasks']=<<<QUERY
INSERT INTO `{$prefix}tasks` (`task`, `title_l`, `name`, `free`, `ondone`, `status`, `run_year`, `run_month`, `run_day`, `run_hour`, `run_minute`, `run_second`, `do`) VALUES
('mainclean.php', '{$ser[1]}', 'mainclean', 1, 'deactivate', 1, '*', '*', '*', '0', '0', '0', {$dateo}),
('ping.php', '{$ser[2]}', 'ping', 1, 'deactivate', 1, '*', '*', '*', '0', '0', '0', {$dateo});
QUERY;

$insert['voting']="INSERT INTO `{$prefix}voting` (`id`,`begin`,`end`,`onlyusers`,`againdays`,`votes`) VALUES (1,'0000-00-00 00:00:00','0000-00-00 00:00:00',0,10,0)";

$insert['voting_q']="INSERT INTO `{$prefix}voting_q` (`id`,`qid`,`multiple`,`maxans`,`answers`) VALUES
(1,0,0,2,'a:3:{i:0;i:0;i:1;i:0;i:2;i:0;}'),
(1,1,1,2,'a:3:{i:0;i:0;i:1;i:0;i:2;i:0;}')";

#Russian
if($rus)
{
	$ser=array(
		serialize(array('������� 1','������� 2','������� 3')),
		serialize(array('������� - 1','������� - 2','������� - 3')),
	);
	$insert['voting_q_l(rus)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'russian', '������ � ��������� �������', '{$ser[0]}'),
(1, 1, 'russian', '������ � �������������� ��������', '{$ser[1]}')";
};
#[E] Russian

#English
if($eng)
{
	$ser=array(
		serialize(array('Variant 1','Variant 2','Variant 3')),
		serialize(array('Variant - 1','Variant - 2','Variant - 3')),
	);
	$insert['voting_q_l(eng)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'english', 'Question with single answer', '{$ser[0]}'),
(1, 1, 'english', 'Question with multiple answers', '{$ser[1]}')";
};
#[E] English

#Ukrainian
if($ukr)
{
	$ser=array(
		serialize(array('������ 1','������ 2','������ 3')),
		serialize(array('������ - 1','������ - 2','������ - 3')),
	);
	$insert['voting_q_l(ukr)']="INSERT INTO `{$prefix}voting_q_l` (`id`, `qid`, `language`, `title`, `variants`) VALUES
(1, 0, 'ukrainian', '������� � ��������� ��������', '{$ser[0]}'),
(1, 1, 'ukrainian', '������� � ���������� ���������', '{$ser[1]}')";
};
#[E] Ukrainian