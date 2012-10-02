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

class Ping extends BaseClass
{
		SAVEDAYS=1,#���������� ����, ������� �������
		PROCCESS_LIMIT=50;#����� ������ �� ���

	protected static
		$services;

	/*
		id - ID ��� ������������ ������. ��� ������ � ���������� ��, �������� ������ �� ������, ������� ���� �������� ���������
		main - ������ �� ����, ��� ���������� ����������
		[site] - �������� �����.
		[services] - ��������� �����, ������� ����� ��������� (�����). ���� �� ������� - ����� ����������� ��� �����.
		[exclude] - �������� �����, ������� ����� ��������� �� �����. ���� �� ������� - ���������� �� �����.
		[changes] - �������� �� ������� ��������� ���������
		[rss] - RSS �����
		[categories] - ���������. ������ ���������, �������� |. ���� ��������.
	*/
	{
			P.'ping',
			array(
				'id'=>isset($a['id']) ? (string)$a['id'] : '',
				'pinged'=>0,
				'!date'=>'NOW()',
				'site'=>empty($a['site']) ? '' : join(',',(array)$a['site']),
				'services'=>empty($a['services']) ? '' : join(',',(array)$a['services']),
				'exclude'=>empty($a['exclude']) ? '' : join(',',(array)$a['exclude']),
				'main'=>isset($a['main']) ? (string)$a['main'] : '',
				'changes'=>isset($a['changes']) ? (string)$a['changes'] : '',
				'rss'=>isset($a['rss']) ? (string)$a['rss'] : '',
				'categories'=>empty($a['categories']) ? '' : join('|',(array)$a['categories']),
			)
		);
		Eleanor::$Db->Update(P.'tasks',array('!nextrun'=>'NOW()'),'`name`=\'ping\'');
		Tasks::UpdateNextRun();

	/*
		������� ���������� �����. ���������� ���������.
	*/
	public static function Once(array$a)
	{
			self::$services=include Eleanor::$root.'addons/config_ping.php';

		if(isset($a['exclude']))
		{
				$a['exclude']=$a['exclude'] ? explode(',',$a['exclude']) : array();
		}
		else
			$a['exclude']=array();
		if(isset($a['services']))
		{
			if(!is_array($a['services']))
				$a['services']=$a['services'] ? explode(',',$a['services']) : false;
		}
		else
			$a['services']=false;

		$r=array();
		$nech=!empty($a['changes']);
		$nerss=!empty($a['rss']);
		if(empty($a['site']))
			$a['site']=Eleanor::$vars['site_name'];
		if(empty($a['main']))
			$a['main']=PROTOCOL.Eleanor::$domain.Eleanor::$site_path;
		foreach(self::$services as $k=>&$v)
		{
				continue;

			if(!is_array($v['methods']))
				$v['methods']=(array)$v['methods'];

			$f='<?xml version="1.0" encoding="'.CHARSET.'"?><methodCall><methodName>';
			if(in_array('weblogUpdates.extendedPing',$v['methods']) and $nech and $nerss)
					.$a['site'].'</value></param><param><value>'
					.$a['main'].'</value></param><param><value>'
					.$a['changes'].'</value></param><param><value>'
					.$a['rss'].'</value></param>'
					.(isset($a['categories']) ? '<param><value>'.$a['categories'].'</value></param>' : '')
					.'</params></methodCall>';
				$f.='weblogUpdates.ping</methodName><params><param><value>'
					.$a['site'].'</value></param><param><value>'
					.$a['main'].'</value></param>'
					.(isset($a['changes']) ? '<param><value>'.$a['changes'].'</value></param>' : '')
					.(isset($a['rss']) ? '<param><value>'.$a['rss'].'</value></param>' : '')
					.(isset($a['categories']) ? '<param><value>'.$a['categories'].'</value></param>' : '')
					.'</params></methodCall>';
			else
				continue;
			$cu=curl_init($v['url']);
			curl_setopt_array($cu,array(
				CURLOPT_RETURNTRANSFER=>1,
				CURLOPT_TIMEOUT=>10,
				CURLOPT_HEADER=>false,
				CURLOPT_POST=>true,
				CURLOPT_POSTFIELDS=>$f,
				CURLOPT_HTTPHEADER=>array('Content-type: text/xml'),
			));
			$r[$k]=curl_exec($cu);
			curl_close($cu);
		}
		return$r;

	public static function Proccess()
	{
		$n=$R->num_rows;
		while($a=$R->fetch_assoc())
		{
			foreach($res as &$v)
				$v=strpos($v,'Thanks for the ping.')===false ? 'error' : 'ok';
			Eleanor::$Db->Update(P.'ping',array('pinged'=>1,'result'=>serialize($res)),'`id`='.Eleanor::$Db->Escape($a['id']).' LIMIT 1');
		Eleanor::$Db->Delete(P.'ping','`pinged`=1 AND `date`<NOW()-INTERVAL '.self::SAVEDAYS.' DAY');
}