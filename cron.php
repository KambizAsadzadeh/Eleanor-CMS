<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
define('CMS\STARTED',microtime(true));

/** Вывод однопиксельной прозрачной png картинки
 * @return mixed Только для совместимости: зачастую этой функцией производится возврат из файла */
function Image()
{
	header('Cache-Control: no-store');
	header('Content-Type: image/png');
	header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru',false,200);
	echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWP4//8/AwAI/AL+hc2rNAAAAABJRU5ErkJggg==');
	return 1;
}

/** Завершение работы скрипта
 * @param bool $again Флаг повторного запуска */
function Finish($again=false)
{
	if($again)
	{#http://php.net/manual/en/features.commandline.options.php

		$php='php';#путь к php
		$script=__FILE__;//путь к скрипту

		if(\Eleanor\W)
		{
			#PHP необходимо добавить в переменную PATH системы, если это OpenServer или его аналог, то ненужно
			$path=explode(';', $_SERVER['PATH']);
			foreach($path as $v)
				if(is_file($v.'\php.exe'))
				{
					$php=$v.'\php.exe';
					break;
				}

			#http://www.somacon.com/p395.php
			pclose(popen('start /b "" "'.$php.'" -q '.$script,'r'));
		}
		else
			`$php -q $script > /dev/null &`;
	}

	if(isset($_GET['return']) and Eleanor::$ourquery)
	{
		header('Cache-Control: no-store');
		header('Location: '.(string)$_GET['return'],true,301);
	}
	else
		Image();
}

require __DIR__.'/cms/core.php';

$Eleanor=new Eleanor(true);

SetService('cron');

if(Eleanor::$vars['multilang'])
{
	if(isset($_REQUEST['language']) and is_string($_REQUEST['language']) and $_REQUEST['language']!=Language::$main)
		Eleanor::$Language->Change($_REQUEST['language']);

	foreach(Eleanor::$lvars as $lk=>$lv)
		Eleanor::$vars[$lk]=FilterLangValues($lv);
}
else
	Eleanor::$lvars=[];

if(isset($_GET['direct']) and key($_GET)=='direct'
	and is_file($f=DIR.'direct/'.preg_replace('#[^a-z0-9\-_]+#i','',(string)$_GET['direct']).'.php'))
	include$f;
elseif(isset($_REQUEST['module']) and is_string($_REQUEST['module']))
{
	$uri=$_REQUEST['module'];
	$Eleanor->modules=GetModules();

	if(!isset($Eleanor->modules['uri2id'][$uri]))
		return Finish();

	$id=$Eleanor->modules['uri2id'][$uri];
	$module=$Eleanor->modules['id2module'][$id];
	$path=DIR.$module['path'];
	$Eleanor->module=[
		'uri'=>$uri,
		'section'=>isset($Eleanor->modules['uris'][$uri]) ? $Eleanor->modules['uris'][$uri] : '',
		'path'=>$path,
		'id'=>$id,
		'uris'=>$module['uris'],
	];

	$path.=$module['file'] ? $module['file'] : 'index.php';

	if(is_file($path))
		\Eleanor\AwareInclude($path);
	else
		return Finish();
}
else
{
	$t=time();
	$table=P.'tasks';
	$again=false;

	if(isset($_GET['id']))
	{
		$id=(int)$_GET['id'];
		$R=Eleanor::$Db->Query("SELECT `id`, `task`, `free`, `options`, `data`, `run_month`, `run_day`, `run_hour`, `run_minute`, `run_second`, `do` FROM `{$table}` WHERE `id`={$id} AND `status`=1 AND `locked`=0");
	}
	else
	{
		#В случае, если скрипт завис... Через 2 часа запустим его снова.
		Eleanor::$Db->Update($table,['free'=>1,'locked'=>0],"`status`=1 AND `locked`=1 AND `free`=0 AND `nextrun`<FROM_UNIXTIME({$t}-7200)");

		$date=date('Y-m-d H:i:s');
		$R=Eleanor::$Db->Query("SELECT `id`, `task`, `free`, `options`, `data`, `run_month`, `run_day`, `run_hour`, `run_minute`, `run_second`, `do` FROM `{$table}` WHERE `status`=1 AND `locked`=0 AND `nextrun`<'{$date}' ORDER BY `free` ASC, `nextrun` ASC");
	}

	if($task=$R->fetch_assoc())do
	{
		$class='CMS\Tasks\\'.$task['task'];

		if(!class_exists($class) or !is_subclass_of($class,Interfaces\Task::class))
		{
			Eleanor::$Db->Update($table,['status'=>0],'`id`='.$task['id'].' LIMIT 1');
			break;
		}

		/** @var Interfaces\Task $T */
		$T=new $class($task['options'] ? json_decode($task['options']) : []);

		Eleanor::$Db->Update($table,$T::BLOKING ? ['free'=>0,'locked'=>1,'!lastrun'=>'NOW()'] : ['!lastrun'=>'NOW()'],"`id`={$task['id']} LIMIT 1");
		$res=$T->Run($task['data'] ? json_decode($task['data']) : []);

		if($res!==false)
			$res=true;

		$update=[
			'free'=>$res ? 1 : 0,
			'locked'=>0,
			'!lastrun'=>'NOW()',
		];

		if($res)
		{
			$nr=Tasks::CalcNextRun([
				'month'=>$task['run_month'],
				'day'=>$task['run_day'],
				'hour'=>$task['run_hour'],
				'minute'=>$task['run_minute'],
				'second'=>$task['run_second'],
			],$task['do']);

			if(!$nr)
				$update['status']=0;

			$update['!nextrun']='FROM_UNIXTIME('.(int)$nr.')';
		}
		else
			$again=true;

		$update['data']=$T->GetNextRunInfo();
		$update['data']=$update['data'] ? json_encode($update['data'],JSON) : '';

		Eleanor::$Db->Update($table,$update,"`id`={$task['id']} LIMIT 1");
	}while(false);

	Tasks::UpdateNextRun();
	Finish($again);
}