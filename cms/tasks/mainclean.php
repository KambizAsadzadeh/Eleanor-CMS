<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Tasks;
defined('CMS\STARTED')||die;
use \CMS, \CMS\Eleanor;

/** Основная очистка системы от повседневного мусора */
class MainClean extends \Eleanor\BaseClass implements CMS\Interfaces\Task
{
	/** Запуск задачи */
	public function Run($data)
	{
		$S=new Statistic;
		$S->Run([]);

		$d=date('Y-m-d H:i:s');
		Eleanor::$Db->Delete(CMS\P.'timecheck','`timegone`=1 AND `date`<\''.$d.'\'');
		Eleanor::$Db->Delete(CMS\P.'confirmation','`expire`<\''.$d.'\'');
		Eleanor::$Db->Delete(CMS\P.'multisite_jump','`expire`<\''.$d.'\'');

		CMS\LoadOptions(['user-profile','drafts']);

		if(Eleanor::$vars['reg_unactivated']=='1')
		{
			$ids=[];
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.CMS\P.'users_site` WHERE `groups`=\','
				.CMS\UserManager::GROUP_WAIT.',\' AND `register`<\''.date('Y-m-d H:i:s').'\' - INTERVAL '
				.Eleanor::$vars['reg_act_time'].' SECOND');
			while($a=$R->fetch_assoc())
				$ids[]=$a['id'];

			if($ids)
				CMS\UserManager::Delete($ids);
		}

		#Удаляем черновики
		Eleanor::$Db->Delete(CMS\P.'drafts','`date`<\''.date('Y-m-d H:i:s').'\' - INTERVAL '
			.(int)Eleanor::$vars['drafts_days'].' DAY');

		/* Удаляем все файлы из каталога temp, которые добавлен больше дня назад. Естественно, о них все забыли и они не
		   будут обработаны уже никогда :) Почему-то появляется ошибка Warning: rmdir([path]\uploads\temp)
		   [<a href='function.rmdir'>function.rmdir</a>]: No such file or directory. Хер знает почему */
		\Eleanor\Framework::$logs=false;
		self::RemoveTempFiles(CMS\Template::$path['uploads'].'temp',time()-86400);
		\Eleanor\Framework::$logs=true;

		/* Синхронизация обновленных и удаленных пользователей. Добавление здесь не делается, оно происходит в момент
		   входа пользователя */
		if(Eleanor::$UsersDb!==Eleanor::$Db or CMS\USERS_TABLE!=CMS\P.'users')
		{
			$lastdate=Eleanor::$Cache->Get('date-users-sync',true);

			if(!$lastdate)
			{
				$R=Eleanor::$UsersDb->Query('SELECT MIN(`date`) FROM `'.CMS\USERS_TABLE.'_updated`');
				list($lastdate)=$R->fetch_row();
			}

			if($lastdate)
			{
				$del=$ids=[];
				$n=1;
				$R=Eleanor::$UsersDb->Query('(SELECT `id`, `date` FROM `'.CMS\USERS_TABLE.'_updated` WHERE `date`=\''
					.$lastdate.'\')UNION ALL(SELECT `id` FROM `'.CMS\USERS_TABLE.'_updated` WHERE `date`>\''.$lastdate
					.'\' ORDER BY `date` ASC LIMIT 50)');
				while($a=$R->fetch_assoc())
				{
					if($n++!=$R->num_rows or $lastdate==$a['date'])
						$ids[]=$a['id'];

					$lastdate=$a['date'];
				}

				$R=Eleanor::$UsersDb->Query('SELECT `id`, `full_name`, `name`, `register`, `last_visit`, `language`,
`timezone` FROM `'.CMS\USERS_TABLE.'` WHERE `id`'.Eleanor::$Db->In($ids).' AND `temp`=0');
				while($a=$R->fetch_assoc())
				{
					$del[]=$a['id'];
					Eleanor::$Db->Update(CMS\P.'users_site',array_slice($a,1),'`id`='.$a['id'].' LIMIT 1');
				}

				$del=array_diff($ids,$del);
				if($del)
					CMS\UserManager::Delete($del);

				Eleanor::$Cache->Put('date-users-sync',$lastdate,true);
			}
		}
	}

	/** Полученне данных для следующего запуска, которые будут переданы в метод Run первым параметром */
	public function GetNextRunInfo()
	{
		return'';
	}

	/** Удаление неиспользуемых временных файлов
	 * @param string $path Путь к каталогу-родителю, в котором нужно искать неиспользуемые файлы
	 * @param int $t Время ДО которого файл считается устаревшим
	 * @return bool */
	private static function RemoveTempFiles($path,$t)
	{
		if(is_link($path))
			return unlink($path);

		elseif(is_file($path))
			return $t>=filectime($path) ? unlink($path) : false;

		elseif(is_dir($path))
		{
			$emp=true;

			if($files=glob($path.'/*'))
				foreach($files as &$file)
					$emp&=self::RemoveTempFiles($file,$t);

			return $emp ? rmdir($path) : false;
		}

		return true;
	}
}