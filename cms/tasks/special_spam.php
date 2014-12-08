<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor, \Eleanor\Classes\BBCode, CMS\Template;

/** Рассылка сообщений */
class Spam extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	private
		/** @var array Конфигурация (создается в менеджере управления задачами) */
		$opts=[],

		/** @var array Данные запуска */
		$data=[];

	/** Конструктор
	 * @param array $opts Конфигурация */
	public function __construct($opts)
	{
		$this->opts=$opts;
	}

	/** Запуск задачи */
	public function Run($data)
	{
		$this->data=$data;

		if(!isset($this->opts['id']))
			return true;

		$R=Eleanor::$Db->Query('SELECT `id`, `sent`, `total`, `per_run`, `taskid`, `finame`, `finamet`, `figroup`,
`figroupt`, `fiip`, `firegisterb`, `firegistera`, `filastvisitb`, `filastvisita`, `figender`, `fiemail`,`fiids`,
`deleteondone` FROM `'.CMS\P.'spam` WHERE `id`='.(int)$this->opts['id'].' LIMIT 1');
		if(!$spam=$R->fetch_assoc())
			return true;

		$lid=isset($data['lastid']) ? (int)$data['lastid'] : 0;
		$users=$update=$where=[];
		$langs=[''];

		if($spam['finame'] and $spam['finamet'])
		{
			$name=Eleanor::$Db->Escape($spam['finamet'],false);
			switch($spam['finamet'])
			{
				case'b':
					$name=' LIKE \''.$name.'%\'';
				break;
				case'm':
					$name=' LIKE \'%'.$name.'%\'';
				break;
				case'e':
					$name=' LIKE \'%'.$name.'\'';
				break;
				default:
					$name='=\''.$name.'\'';
			}
			$where[]='`u`.`name`'.$name;
		}

		if($spam['fiids'])
			$where[]='`id`'.Eleanor::$Db->In(explode(',',CMS\Tasks::FillInt($spam['fiids'])));

		if($spam['firegisterb'] and 0<$t=strtotime($spam['firegisterb']))
			$where[]='`u`.`register`>=\''.date('Y-m-d H:i:s',$t).'\'';

		if($spam['firegistera'] and 0<$t=strtotime($spam['firegistera']))
			$where[]='`u`.`register`<=\''.date('Y-m-d H:i:s',$t).'\'';

		if($spam['fiip'])
			$where[]='`ip` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($spam['fiip'],false)).'\'';

		if($spam['fiemail'])
			$where[]='`email` LIKE \''.str_replace('*','%',Eleanor::$Db->Escape($spam['fiemail'],false)).'\'';

		if($spam['figender'] and $spam['figender']>-2)
			$where[]='`gender`='.(int)$spam['figender'];

		if($spam['figroup'])
		{
			$gr=explode(',',trim($spam['figroup'],','));

			if($spam['figroupt']=='and')
			{
				$g='%,';

				foreach($gr as &$v)
					$g.=(int)$v.',%';

				$where[]='`groups` LIKE \''.str_replace('*','%',$g).'\'';
			}
			else
			{
				foreach($gr as &$v)
					$v=(int)$v;

				$where[]='`groups` REGEXP \',('.join('|',$gr).'),\'';
			}
		}

		$where=$where ? join(' AND ',$where) : false;

		if($spam['sent']+$spam['per_run']>$spam['total'])
		{
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM '.(Eleanor::$Db===Eleanor::$UsersDb
					? '`'.CMS\USERS_TABLE.'` `u` INNER JOIN `'.CMS\P.'users_site` USING(`id`)'
					: '`'.CMS\P.'users_site`')
				.' INNER JOIN `'.CMS\P.'users_extra` USING(`id`)'.($where ? ' WHERE '.$where : ''));
			list($spam['total'])=$R->fetch_row();
			$update['total']=$spam['total'];
		}

		if(Eleanor::$Db===Eleanor::$UsersDb)
		{
			$R=Eleanor::$Db->Query('SELECT `id`, `u`.`name`, `u`.`full_name`, `email`, `u`.`language` FROM `'
				.CMS\USERS_TABLE.'` `u` INNER JOIN `'.CMS\P.'users_extra` USING(`id`) INNER JOIN `'
				.CMS\P.'users_site` USING(`id`) WHERE `id`>'.(int)$lid.($where ? ' AND '.$where : '')
				.' LIMIT '.$spam['per_run']);
			while($temp=$R->fetch_assoc())
			{
				$users[ $temp['id'] ]=array_slice($temp,1);
				$langs[]=$temp['language'] ? $temp['language'] : CMS\Language::$main;
			}
		}
		else
		{
			$R=Eleanor::$Db->Query('SELECT `id`,`name`,`full_name`,`email` FROM `'.CMS\P.'users_site` `u` INNER JOIN `'
				.CMS\P.'users_extra` `e` USING(`id`) WHERE `id`>'.(int)$lid.($where ? ' AND '.$where : '')
				.' LIMIT '.$spam['per_run']);
			while($temp=$R->fetch_assoc())
				$users[$temp['id']]=array_slice($temp,1);

			if($users)
			{
				$R=Eleanor::$UsersDb->Query('SELECT `id`,`language` FROM `'.CMS\USERS_TABLE.'` WHERE `id`'
					.Eleanor::$UsersDb->In(array_keys($users)));
				while($temp=$R->fetch_assoc())
				{
					$users[ $temp['id'] ]['language']=$temp['language'];
					$langs[]=$temp['language'] ? $temp['language'] : CMS\Language::$main;
				}
			}
		}

		$ret=true;

		do
		{
			if(!$users)
			{
				$update['sent']=$spam['total'];
				$update['status']='finished';
				$update['!statusdate']='NOW()';
				break;
			}

			$frepk=$frepv=[];
			$files=glob(Template::$path['uploads'].'spam/'.$spam['id'].'/*');
			$sitepref=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR;
			$prefrep=strpos(Template::$http['uploads'],'://')===false
				? Template::$http['uploads']
				: $sitepref;

			foreach($files as $k=>$v)
			{
				$frepk[$k]='src="'.str_replace(Template::$path['uploads'],$prefrep,$v).'"';
				$frepv[$k]='src="cid:f'.$k.'"';
			}

			$langs=array_unique($langs);
			$R=Eleanor::$Db->Query('SELECT `language`,`title`,`text` FROM `'.CMS\P.'spam_l` WHERE `id`='.$spam['id']
				.' AND `language`'.Eleanor::$Db->In($langs));
			$langs=[];

			while($temp=$R->fetch_assoc())
			{
				$temp['text']=CMS\OwnBB::Parse($temp['text']);
				$temp['text']=str_replace('href="go.php','href="',$temp['text']);
				$temp['text']=preg_replace('#(src|href)=(["\'])(?![a-z]{1,5}://)#','\1=\2'.$sitepref,$temp['text']);
				$temp['text']=preg_replace('#url\((["\']?)(?![a-z]{1,5}://)#','url(\1'.$sitepref,$temp['text']);
				$temp['text']=str_replace($frepk,$frepv,$temp['text']);

				$langs[ $temp['language'] ]=array_slice($temp,1);
			}

			$c='';
			$Email=new \Eleanor\Classes\Email;
			$Email->parts=[
				'multipart'=>'mixed',
				[
					'content-type'=>'text/html',
					'charset'=>\Eleanor\CHARSET,
					'content'=>&$c,
				],
			];

			foreach($files as $k=>&$v)
				$Email->parts[]=[
					'content-type'=>\Eleanor\Classes\Types::MimeTypeByExt($v),
					'filename'=>basename($v),
					'content'=>file_get_contents($v),
					'id'=>'f'.$k,
				];

			foreach($users as $k=>&$v)
			{
				if($v['email'])
				{
					$lang=CMS\FilterLangValues($langs,$v['language']);

					if($lang)
					{
						$Email->subject=BBCode::ExecLogic($lang['title'],$v);
						$c=BBCode::ExecLogic($lang['text'],$v);

						$Email->Send(['to'=>$v['email']]);
					}
				}

				++$spam['sent'];
				$this->data['lastid']=$k;
			}

			$update['sent']=$spam['sent'];

			$ret=false;
		}while(false);

		if($ret and $spam['deleteondone'])
		{
			Eleanor::$Db->Delete(CMS\P.'spam','`id`='.$spam['id'].' LIMIT 1');
			Eleanor::$Db->Delete(CMS\P.'spam_l','`id`='.$spam['id']);
			\Eleanor\Classes\Files::Delete(Template::$http['uploads'].'spam/'.$spam['id']);
		}
		else
			Eleanor::$Db->Update(CMS\P.'spam',$update,'`id`='.$spam['id'].' LIMIT 1');

		return$ret;
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return$this->data;
	}
}