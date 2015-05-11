<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Реализация специальных BB кодов, которые обрабатываются каждый раз при показе страницы */
class OwnBB extends \Eleanor\BaseClass
{
	const
		/** Обработка сохраненных данных перед показом */
		DISPLAY=1,

		/** Обработка сохраненных данных перед правкой */
		EDIT=2,

		/** Обработка несохранных (полученных от пользователя) данных перед показом */
		SAVE=4;

	public static
		/** @var array Замены классов обработчиков BB кодов. Формат: имя класса => имя класса замены */
		$replace=[],

		/** @var array Данные обрабатываемых ownbb кодов. Заполняется в конце этого файла */
		$bbs=[],

		/** @var array Тонкие настройки для каждого класса. Например ключ alt отвечает за присвоение всем
		 * картинкам параметра alt по умолчанию, visual - флаг визуального редактора с которого получены данные */
		$opts=[],

		/** @var array Используется в методах StoreNotParsed и ParseNotParsed */
		$np=[];

	/** Грамотная обработка ownbb кодов
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 * @param array $bbs Исключительный массив только эти ownbb коды будут обработаны
	 * @return string */
	public static function Parse($s,$type=self::DISPLAY,array$bbs=[])
	{
		$s=static::StoreNotParsed($s,$type);
		$s=static::ParseBBCodes($s,$type,$bbs);
		return static::ParseNotParsed($s,$type);
	}

	/** Непосредственная обработка ownbb кодов. Отличие от Parse в том, именно этот метод обрабатывает ownbb коды, в то
	 * время как Parse всего лишь надстройка
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 * @param array $handlers Исключительный массив: только эти обработчики нужно парсить
	 * @return string */
	public static function ParseBBCodes($s,$type,array$handlers=[])
	{
		switch($type)
		{
			case static::EDIT:
				$mth='PreEdit';
			break;
			case static::SAVE:
				$mth='PreSave';
			break;
			default:
				$mth='PreDisplay';
		}

		$groups=GetGroups();

		foreach(static::$bbs as $bb)
		{
			if($handlers and $bb['special'] and !in_array($bb['handler'],$handlers))
				continue;

			$cu=true;

			if($type&static::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&static::DISPLAY)
				$grs=$bb['gr_see'];
			else
				$grs=false;

			if($grs)
				$cu=(bool)array_intersect($grs,$groups);

			$c=isset(static::$replace[ $bb['handler'] ])
				? static::$replace[ $bb['handler'] ]
				: '\CMS\OwnBB\\'.$bb['handler'];

			foreach($bb['tags'] as &$t)
			{
				$ocp=-1;
				$cp=0;

				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}

					$tl=strlen($t);

					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )

					if(trim($s{$cp+$tl+1},'=] ')!='')
					{
						++$cp;
						continue;
					}

					$l=false;

					do
					{
						$l=strpos($s,']',$l ? $l+1 : $cp);

						if($l===false)
						{
							++$cp;
							continue 2;
						}
					}while($s{$l-1}=='\\');

					if(!class_exists($c) or !is_subclass_of($c,\CMS\Abstracts\OwnBbCode::class))
						continue 3;

					if(method_exists($c,'Total'.$mth))
					{
						$s=call_user_func([$c,'Total'.$mth],$s,$bb['tags'],$cu);
						continue 3;
					}

					$ps=substr($s,$cp+$tl+1,$l-$cp-$tl-1);
					$ps=str_replace('\\]',']',trim($ps));
					$e=constant($c.'::SINGLE');

					if($e or false===$clpos=stripos($s,'[/'.$t.']',$l+1))
					{
						$l-=$cp-1;#]
						$ct='';
					}
					else
					{
						$ct=substr($s,$l+1,$clpos-$l-1);
						$l=$clpos-$cp+$tl+3;#[/]
					}

					$r=call_user_func([$c,$mth],$t,$ps,$ct,$cu,$e);
					$s=substr_replace($s,$r,$cp,$l);
					$ocp=$cp;
				}
			}
		}

		#Удаление лишних <br>. Например, после цитаты, которая заканчивается блочным элементом </blockquot>, <br> не нужен.
		return$type&static::DISPLAY ? preg_replace('#<!-- NOBR --><br\s?/?>#i','',$s)
			: str_replace('<!-- NOBR -->','',$s);
	}

	/** Сохранение содержимого специальных ownbb кодов, внутри которых нельзя производить обработку содержимого
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 * @return string */
	public static function StoreNotParsed($s,$type)
	{
		$s=str_replace('<!-- NP ','<!-- ',$s);
		$n=0;
		static::$np=[];
		$groups=GetGroups();

		foreach(static::$bbs as $bb)
		{
			if(!$bb['no_parse'])
				continue;

			if($type&static::SAVE)
				$grs=$bb['gr_use'];
			elseif($type&static::DISPLAY)
				$grs=$bb['gr_see'];
			else
				$grs=false;

			if($grs and !(bool)array_intersect($grs,$groups))
				continue;

			$c=isset(static::$replace[ $bb['handler'] ])
				? static::$replace[ $bb['handler'] ]
				: '\CMS\OwnBB\\'.$bb['handler'];

			foreach($bb['tags'] as $t)
			{
				$ocp=-1;
				$cp=0;

				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}

					$tl=strlen($t);

					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )
					if(trim(substr($s,$cp+$tl+1,1),'=] ')!='')
					{
						++$cp;
						continue;
					}

					if(false!==$nop=strpos($s,'noparse]',$cp) and $nop<strpos($s,']',$cp))
					{
						++$cp;
						continue;
					}

					if(!class_exists($c) or !is_subclass_of($c,\CMS\Abstracts\OwnBbCode::class))
						continue 3;

					$e=constant($c.'::SINGLE');

					if($e or false===$l=strpos($s,'[/'.$t.']',$cp))
					{
						$l=strpos($s,']',$cp);

						if($l===false)
						{
							++$cp;
							continue;
						}

						$l-=$cp-1;#]
					}
					else
						$l-=$cp-$tl-3;#[/]

					$r='<!-- NP '.$n++.' -->';
					$ct=substr($s,$cp,$l);
					$s=substr_replace($s,$r,$cp,$l);
					$ocp=$cp;

					static::$np[]=[
						'r'=>$r,
						't'=>$ct,
						's'=>$bb['sp_tags'] ? $bb['sp_tags']+[''=>$t] : [$t],
					];
				}
			}
		}

		return$s;
	}

	/** Обработка содержимого специальных ownbb кодов. Вызывается после их сохранения методом StoreNotParsed и обработки
	 * основных кодов методом ParseBBCodes
	 * @param string $s Текст для обработки, должен содержать ownbb коды
	 * @param int $type Тип обработки (см. константы выше)
	 * @return string */
	public static function ParseNotParsed($s,$type)
	{
		if(static::$np)
			if($type)
				foreach(static::$np as $v)
					$s=str_replace($v['r'],static::ParseBBCodes($v['t'],$type,$v['s']),$s);
			else
				foreach(static::$np as $v)
					$s=str_replace($v['r'],$v['t'],$s);

		static::$np=[];

		return$s;
	}

	/** Создание кэша ownbb кодов */
	public static function Recache()
	{
		static::$bbs=[];
		$table=P.'ownbb';

		$R=Eleanor::$Db->Query("SELECT `title_l` `title`, `handler`, `tags`, `no_parse`, `special`, `sp_tags`, `gr_use`, `gr_see`, `sb` FROM `{$table}` WHERE `status`=1 ORDER BY `pos` ASC");
		while($a=$R->fetch_assoc())
		{
			$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
			$a['sp_tags']=$a['sp_tags'] ? explode(',',$a['sp_tags']) : [];
			$a['gr_use']=$a['gr_use'] ? explode(',',$a['gr_use']) : [];
			$a['gr_see']=$a['gr_see'] ? explode(',',$a['gr_see']) : [];
			$a['tags']=$a['tags'] ? explode(',',$a['tags']) : [];

			static::$bbs[]=$a;
		}

		Eleanor::$Cache->Put('ownbb_'.Language::$main,static::$bbs);
	}
}

OwnBB::$bbs=Eleanor::$Cache->Get('ownbb_'.Language::$main);
if(OwnBB::$bbs===false)
	OwnBB::Recache();