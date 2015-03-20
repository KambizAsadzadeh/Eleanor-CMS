<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor, \Eleanor\Classes\BBCode, \Eleanor\Classes\Email;

/** Рассылка уведомлений ответственному за исправление ошибок на сайте */
class Informer extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	/** @var array Сохраемые между запусками данные */
	protected $data=[];

	/** Запуск задачи */
	public function Run($data)
	{
		if(\Eleanor\Framework::$debug)
			return;

		if(!is_array($data))
			$data=[];

		if(!isset($data['t']))
			$data['t']=time()-86400;

		$sitepref=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;
		$vars=CMS\LoadOptions(['errors','site'],true);
		$f=\Eleanor\Framework::$logspath.'errors.inc';

		if($vars['errors_code_users'] and is_file($f))
		{
			$vars['errors_code_users']=explode(',,',trim($vars['errors_code_users'],','));
			$users=[];

			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.CMS\P.'users_site` WHERE `id`'
				.Eleanor::$Db->In($vars['errors_code_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=['cnt'=>0,'errors'=>[]];
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : [];

			foreach($f as $v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$repl['errors'][]=($v['d']['n']>1
							? substr_replace($v['d']['e'],'('.$v['d']['n'].')',strpos($v['d']['e'],':'),0)
							: $v['d']['e']).PHP_EOL
						.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL
						.'URL: '.$sitepref.($v['d']['p'] ? $v['d']['p'] : '').PHP_EOL
						.'Date: '.$v['d']['d'];
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=[
					'site'=>$vars['site_name'],
					'link'=>$sitepref,
				];
				$vars['errors_code_text']=CMS\OwnBB::Parse($vars['errors_code_text']);

				foreach($users as $v)
				{
					if($v['language'] and CMS\Language::$main!=$v['language'])
						Eleanor::$Language->Change($v['language']);

					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						BBCode::ExecLogic($vars['errors_code_title'],$repl),
						BBCode::ExecLogic($vars['errors_code_text'],$repl)
					);
				}
			}
		}

		$f=\Eleanor\Framework::$logspath.'database.inc';

		if($vars['errors_db_users'] and is_file($f))
		{
			$vars['errors_db_users']=explode(',,',trim($vars['errors_db_users'],','));
			$users=[];

			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.CMS\P.'users_site` WHERE `id`'
				.Eleanor::$Db->In($vars['errors_db_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=['cnt'=>0,'errors'=>[]];
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : [];

			foreach($f as $v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$log=$v['d']['e'].PHP_EOL;
					switch($v['d']['d'])
					{
						case'connect':
							$log.='DB: '.$v['d']['db'].PHP_EOL
								.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL
								.'Date: '.$v['d']['d'].PHP_EOL
								.'Happened: '.$v['d']['n'];
						break;
						case'query':
							$log.='Query: '.$v['d']['q'].PHP_EOL
								.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL
								.'Date: '.$v['d']['d'].PHP_EOL
								.'Happened: '.$v['d']['n'];
						break;
						default:
							$log.='File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL
								.'Date: '.$v['d']['d'].PHP_EOL
								.'Happened: '.$v['d']['n'];
					}
					$repl['errors'][]=$log;
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=[
					'site'=>$vars['site_name'],
					'link'=>$sitepref,
				];
				$vars['errors_db_text']=CMS\OwnBB::Parse($vars['errors_db_text']);

				foreach($users as $v)
				{
					if($v['language'] and CMS\Language::$main!=$v['language'])
						Eleanor::$Language->Change($v['language']);

					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						BBCode::ExecLogic($vars['errors_db_title'],$repl),
						BBCode::ExecLogic($vars['errors_db_text'],$repl)
					);
				}
			}
		}

		$f=\Eleanor\Framework::$logspath.'requests.inc';

		if($vars['errors_requests_users'] and is_file($f))
		{
			$vars['errors_requests_users']=explode(',,',trim($vars['errors_requests_users'],','));
			$users=[];
			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.CMS\P.'users_site` WHERE `id`'
				.Eleanor::$Db->In($vars['errors_requests_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=['cnt'=>0,'errors'=>[]];
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : [];

			foreach($f as &$v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$repl['errors'][]=$v['d']['e'].'('.$v['d']['n'].'): '.($v['d']['p'] ? $v['d']['p'] : '/').PHP_EOL
						.'Date: '.$v['d']['d'].PHP_EOL
						.'IP: '.$v['d']['ip'].PHP_EOL
						.(isset($v['d']['u']) ? 'User: '.$v['d']['u'].PHP_EOL : '')
						.'Browser: '.$v['d']['b'].PHP_EOL
						.'Referrers: '.join(', ',$v['d']['r']);
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=[
					'site'=>$vars['site_name'],
					'link'=>$sitepref,
				];
				$vars['errors_requests_text']=CMS\OwnBB::Parse($vars['errors_requests_text']);

				foreach($users as $v)
				{
					if($v['language'] and CMS\Language::$main!=$v['language'])
						Eleanor::$Language->Change($v['language']);

					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						BBCode::ExecLogic($vars['errors_requests_title'],$repl),
						BBCode::ExecLogic($vars['errors_requests_text'],$repl)
					);
				}
			}
		}

		$data['t']=time();
		$this->data=$data;
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return$this->data;
	}
}