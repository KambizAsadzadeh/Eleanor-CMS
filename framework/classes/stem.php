<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Набор функций для получение основы слова */
class Stem extends Eleanor\BaseClass
{
	#Русский язык основано на http://santafox.googlecode.com/svn/modules/search/include/lingua_stem_ru.class.php
	public static $cache=[];

	const
		VOWEL='/аеиоуыэюя/u',
		PERFECTIVEGROUND='/((ив|ивши|ившись|ыв|ывши|ывшись)|((?<=[ая])(в|вши|вшись)))$/u',
		REFLEXIVE='/(с[яь])$/u',
		ADJECTIVE='/(ее|ие|ые|ое|ими|ыми|ей|ий|ый|ой|ем|им|ым|ом|его|ого|ему|ому|их|ых|ую|юю|ая|яя|ою|ею)$/u',
		PARTICIPLE='/((ивш|ывш|ующ)|((?<=[ая])(ем|нн|вш|ющ|щ)))$/u',
		VERB='/((ила|ыла|ена|ейте|уйте|ите|или|ыли|ей|уй|ил|ыл|им|ым|ен|ило|ыло|ено|ят|ует|уют|ит|ыт|ены|ить|ыть|ишь|ую|ю)|((?<=[ая])(ла|на|ете|йте|ли|й|л|ем|н|ло|но|ет|ют|ны|ть|ешь|нно)))$/u',
		NOUN='/(а|ев|ов|ие|ье|е|иями|ями|ами|еи|ии|и|ией|ей|ой|ий|й|иям|ям|ием|ем|ам|ом|о|у|ах|иях|ях|ы|ь|ию|ью|ю|ия|ья|я)$/u',
		RVRE='/^(.*?[аеиоуыэюя])(.*)$/u',
		DERIVATIONAL='/[^аеиоуыэюя][аеиоуыэюя]+[^аеиоуыэюя]+[аеиоуыэюя].*(?<=о)сть?$/u';

	/** Получение основы слова для русского языка
	 * @param string $word Русское слово
	 * @return mixed|string */
	public static function RussianWord($word)
	{
		$word=mb_strtolower($word);
		$word=str_replace('ё','е',$word);

		#Check against cache of stemmed words
		if(isset(static::$cache[$word]))
			return static::$cache[$word];

		$stem=$word;

		if(preg_match(static::RVRE,$word,$p)>0 and $p[2])
		{
			$start=$p[1];
			$rv=$p[2];

			#Step 1
			if(!static::Test($rv,static::PERFECTIVEGROUND,''))
			{
				static::Test($rv,static::REFLEXIVE,'');

				if(static::Test($rv,static::ADJECTIVE,''))
					static::Test($rv,static::PARTICIPLE,'');
				elseif(!static::Test($rv,static::VERB,''))
					static::Test($rv,static::NOUN,'');
			}

			#Step 2
			static::Test($rv,'/и$/u','');

			#Step 3
			if(preg_match(static::DERIVATIONAL,$rv)>0)
				static::Test($rv,'/ость?$/u','');

			#Step 4
			if(!static::Test($rv,'/ь$/u',''))
			{
				static::Test($rv,'/ейше?/u','');
				static::Test($rv,'/нн$/u','н');
			}

			$stem=$start.$rv;
		}

		static::$cache[$word]=$stem;

		return$stem;
	}

	/** Внутренний метод тестирования */
	protected static function Test(&$s,$re,$to)
	{
		$orig=$s;
		$s=preg_replace($re,$to,$s);
		return$orig!==$s;
	}
} 