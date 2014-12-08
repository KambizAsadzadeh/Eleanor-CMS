<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\Output, \Eleanor\Classes\Files;

defined('CMS\STARTED')||die;
global$Eleanor,$title;

$lang=Eleanor::$Language->Load(DIR.'admin/translation/general-*.php','general');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'General.php';
$Url=$Eleanor->DynUrl;
/** @var DynUrl $Url */
$Eleanor->module['links']=[
	'main'=>(string)$Url,
	'server'=>$Url(['do'=>'server']),
	'logs'=>$Url(['do'=>'logs']),
	'license'=>$Url(['do'=>'license']),
];

switch(isset($_GET['do']) ? (string)$_GET['do'] : '')
{
	case'server':
		$title[]=$lang['server_info'];
		Response(Eleanor::$Template->Server([
			'gd_info'=>function_exists('gd_info') ? gd_info() : null,
			'ini_get_v'=>empty($_POST['ini_get']) ? false : ini_get((string)$_POST['ini_get']),
			'ini_get'=>isset($_POST['ini_get']) ? (string)$_POST['ini_get'] : '',
			'os'=>php_uname('s'),
			'pms'=>ini_get('post_max_size'),
			'ums'=>ini_get('upload_max_filesize'),
			'ml'=>ini_get('memory_limit'),
			'met'=>ini_get('max_execution_time'),
			'mysql'=>str_replace('-nt-max','',Eleanor::$Db->Driver->server_info),
		]));
	break;
	case'logs':
		$Url=$Eleanor->DynUrl;
		$Url->prefix.='do=logs&amp;';

		if(isset($_GET['view']))
		{
			$f=str_replace(['..','/','\\'],'',(string)$_GET['view']);
			$f=Eleanor::$logspath.$f;
			$help=substr($f,0,-4).'.inc';

			if(is_file($f))
			{
				$bn=basename($f);

				if(in_array($bn,['errors.log','database.log','requests.log']) and is_file($help))
				{
					$help_arr=(array)unserialize(file_get_contents($help));

					if(AJAX)
					{
						$id=isset($_POST['id']) ? (string)$_POST['id'] : '';

						if(isset($help_arr[$id]))
						{
							if(count($help_arr)==1)
							{
								Files::Delete($f);
								Files::Delete($help);
							}
							else
							{
								$fh=fopen($f,'rb+');

								if(flock($fh,LOCK_EX))
								{
									$diff=Files::FReplace($fh,'',$help_arr[$id]['o'],$help_arr[$id]['l']+strlen(PHP_EOL)*2);

									foreach($help_arr as &$v)
										if($v['o']>$help_arr[$id]['o'])
											$v['o']+=$diff;

									flock($fh,LOCK_UN);
									fclose($fh);
									unset($help_arr[$id]);
									file_put_contents($help,serialize($help_arr));
								}
								else
								{
									fclose($fh);
									Error();
									break;
								}
							}
						}

						Response(true);
						break;
					}
				}
				else
					$help_arr=false;

				if(AJAX)
				{
					Error();
					break;
				}

				$title[]=isset($lang[$bn]) ? $lang[$bn] : $bn;

				Response(Eleanor::$Template->ShowLog(
					$help_arr ? $help_arr: file_get_contents($f),
					substr($bn,0,-4),
					[
						'download'=>$Url(['download'=>$bn]),
						'delete'=>$Url(['delete'=>$bn]),
						'back'=>(string)$Url,
					]
				));

				break;
			}
		}
		elseif(isset($_GET['download']))
		{
			$f=str_replace(['..','/','\\'],'',(string)$_GET['download']);
			$f=Eleanor::$logspath.$f;

			if(is_file($f))
			{
				Output::Stream(['file'=>$f]);
				break;
			}
		}
		elseif(isset($_GET['delete']))
		{
			$f=str_replace(['..','/','\\'],'',$_GET['delete']);
			$f=Eleanor::$logspath.$f;

			if(is_file($f))
			{
				Files::Delete($f);
				Files::Delete($f.'.inc');

				GoAway(true);
				break;
			}
		}

		$logs=glob(DIR.'../trash/logs/*.log');
		$title[]=$lang['logs'];

		if($logs)
		{
			LoadOptions('errors');

			foreach($logs as &$v)
			{
				$bn=basename($v);
				$v=[
					'path'=>'trash/logs/'.basename($v),
					'size'=>filesize($v),
					'view'=>$Url(['view'=>$bn]),
					'download'=>$Url(['download'=>$bn]),
					'delete'=>$Url(['delete'=>$bn]),
					'descr'=>isset($lang[$bn]) ? $lang[$bn] : $bn,
				];
			}
		}

		$size=Files::BytesToSize(Files::GetSize(
			Eleanor::$logspath,
			function($f){
				return basename($f)!='.htaccess';
			}
		));

		Response( Eleanor::$Template->Logs($logs,$size) );
	break;
	case'license':
		$title[]=$lang['license_'];

		$license=DIR.'license/license-'.Language::$main.'.html';
		$license=is_file($license) ? file_get_contents($license) : file_get_contents(DIR.'license/license-russian.html');
		$license=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$license);

		$sanctions=DIR.'license/sanctions-'.Language::$main.'.html';
		$sanctions=is_file($sanctions) ? file_get_contents($sanctions) : file_get_contents(DIR.'license/sanctions-russian.html');
		$sanctions=preg_replace('#^.*?<body[^>]*>|</body>.*$#s','',$sanctions);

		Response( Eleanor::$Template->License($license,$sanctions) );
	break;
	default:
		if(AJAX)
		{
			$event=isset($_POST['event']) ? (string)$_POST['event'] : '';

			switch($event)
			{
				case'mynotesload':
					$text=Eleanor::$Cache->Get('notes_'.Eleanor::$Login->Get('id'),true);
					Response(Eleanor::$Template->Notes($Eleanor->Editor->Area('emynotes',$text),true));
				break;
				case'conotesload':
					$text=Eleanor::$Cache->Get('notes',true);
					Response(Eleanor::$Template->Notes($Eleanor->Editor->Area('econotes',$text),true));
				break;
				case'mynotes':
					$text=isset($_POST['text']) ? $Eleanor->Saver->Save((string)$_POST['text']) : '';
					Eleanor::$Cache->Put('notes_'.Eleanor::$Login->Get('id'),$text,0,true);
					Response(Eleanor::$Template->Notes($text));
				break;
				case'conotes':
					$text=isset($_POST['text']) ? $Eleanor->Saver->Save((string)$_POST['text']) : '';
					Eleanor::$Cache->Put('notes',$text,0,true);
					Response(Eleanor::$Template->Notes($text));
				break;
				case'remove-install':
					Files::Delete(\CMS\DIR.'../install');
					Response(true);
				break;
				default:
					Error(Eleanor::$Language['main']['unknown_event']);
			}

			break;
		}

		$wd=date('w');
		if($wd==0)
			$wd=7;
		--$wd;

		$nums=$groups=$users=$grs=[];
		$table=P.'comments';
		$R=Eleanor::$Db->Query("(SELECT COUNT(`id`) FROM `{$table}`)UNION ALL (SELECT COUNT(`id`) FROM `{$table}` WHERE `date`>DATE_SUB(CURDATE(), INTERVAL {$wd} DAY))");
		list($nums['comments'])=$R->fetch_row();#Комментариев всего
		list($nums['comments-week'])=$R->fetch_row();#Комментариев на этой неделе

		$table=USERS_TABLE;
		$R=Eleanor::$UsersDb->Query("(SELECT COUNT(`id`) FROM `{$table}` WHERE `id`>0)UNION ALL (SELECT COUNT(`id`) FROM `{$table}` WHERE `register`>DATE_SUB(CURDATE(), INTERVAL {$wd} DAY))");
		list($nums['users'])=$R->fetch_row();#Пользователей всего
		list($nums['users-week'])=$R->fetch_row();#Пользователей на этой неделе

		$table=P.'upgrade_hist';
		$R=Eleanor::$Db->Query("SELECT TO_DAYS(NOW())-TO_DAYS(`date`) FROM `{$table}` ORDER BY `id` ASC LIMIT 1");
		list($nums['life'])=$R->fetch_row();#Количество дней, которые живет сайт

		$old=$Url->prefix;
		$Url->prefix=DynUrl::$base.'section=management&amp;module=comments&amp;';
		$comments=require DIR.'admin/modules/comments.php';

		$Url->prefix=DynUrl::$base.'section=management&amp;module=users&amp;';
		$uid=Eleanor::$Login->Get('id');
		$table=P.'users_site';
		$R=Eleanor::$Db->Query("SELECT `id`,`full_name`,`name`,`email`,`groups`,`ip`,`register`,`last_visit` FROM `{$table}` WHERE `id`>0 ORDER BY `id` DESC LIMIT 5");
		while($a=$R->fetch_assoc())
		{
			$a['groups']=$a['groups'] ? explode(',,',trim($a['groups'],',')) : [];
			$grs=array_merge($grs,$a['groups']);

			$a['ip']=inet_ntop($a['ip']);
			$a['_adel']=$uid==$a['id'] ? false : $Url(['delete'=>$a['id']]);
			$a['_aedit']=$Url(['edit'=>$a['id']]);

			$users[$a['id']]=array_slice($a,1);
		}

		if($grs)
		{
			$Url->prefix=DynUrl::$base.'section=management&amp;module=groups&amp;';
			$table=P.'groups';
			$in=Eleanor::$Db->In($grs);
			$R=Eleanor::$Db->Query("SELECT `id`, `title_l` `title`, `style` FROM `{$table}` WHERE `id`{$in}");
			$grs=[];
			while($a=$R->fetch_assoc())
			{
				$groups[$a['id']]=$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
				$a['_aedit']=$Url(['edit'=>$a['id']]);
				$grs[$a['id']]=array_slice($a,1);
			}
			asort($groups,SORT_STRING);
			foreach($groups as $k=>&$v)
				$v=$grs[$k];
		}
		$Url->prefix=$old;

		$mynotes=Eleanor::$Cache->Get('notes_'.Eleanor::$Login->Get('id'),true);
		$conotes=Eleanor::$Cache->Get('notes',true);
		$cleaned=false;

		if(isset($_POST['kill_cache']))
		{
			Eleanor::$Cache->Engine->DeleteByTag('');
			$cleaned=true;
		}

		$s=Eleanor::$Template->General($nums,$comments,$users,$groups,$mynotes,$conotes,$cleaned);

		Response($s);
}