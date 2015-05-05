<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Uniel;
use CMS\Eleanor;

include_once __DIR__.'/../../html.php';
/** Шаблон для публичной части модуля статических страниц */
class StaticPage
{
	/** Страница отображения статической страницы
	 * @param int|string $id Идентификатор: числовой для страницы из базы данных, строка - для файловых страниц.
	 * @param array $data Данные статической страницы, ключи:
	 *  string title Название
	 *  string text Тест (содержимое)
	 *  array navi Хлебные крошки, двумерный массив, ключи:
	 *   int 0 Текст крошки
	 *   int|null 1 Ссылка крошки
	 *  array seealso Ссылки блока "смотри еще", двумерный массив, ключи:
	 *   int 0 Текст ссылки
	 *   int 1 Ссылка
	 * @return string */
	public static function StaticShow($id,$data)
	{
		static::RssLink();
		$see=$navi='';

		if($data['navi'])
		{
			foreach($data['navi'] as &$v)
				$v=$v[1] ? '<a href="'.$v[1].'">'.$v[0].'</a>' : $v[0];

			$navi.=join(' &raquo; ',$data['navi']).'<hr />';
		}

		if($data['seealso'])
		{
			foreach($data['seealso'] as &$v)
				$v='<a href="'.$v[1].'">'.$v[0].'</a>';

			$see='<hr /><b>'.Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['seealso']
				.'</b><br /><ul><li>'.join('</li><li>',$data['seealso']).'</li></ul>';
		}

		\CMS\Templates\OG([
			'title'=>$data['title'],
			'uri'=>$GLOBALS['Eleanor']->module['origurl'],
			'locale'=>Eleanor::$langs[\CMS\Language::$main]['d'],
			'site_name'=>Eleanor::$vars['site_name'],
			'description'=>$GLOBALS['Eleanor']->module['description'],
			'image'=>preg_match('#<img.+?src="([^"]+)"[^>]*>#',$data['text'],$m)>0
				? (strpos($m[1],'://')===false ? \Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.$m[1] : $m[1])
				: false
		]);

		return T::$T->OpenTable()
			.$navi
			.\CMS\Templates\Content($data['text'])
			.$see
			.T::$T->CloseTable();
	}

	/** Вывод статических страниц на главной (когда модуль установлен на главную)
	 * @param array $statics Перечень страниц для вывода, ключи:
	 *  string title Название
	 *  string text Текст
	 * @return string */
	public static function StaticGeneral($statics)
	{
		static::RssLink();
		$c='';

		foreach($statics as &$v)
			$c.='<h1 style="text-align:center">'.$v['title'].'</h1><br />'.\CMS\Templates\Content($v['text']).'<br /><br />';

		return$c;
	}

	/** Вывод содержания (перечень всех страниц)
	 * @param array $statics Перечень страниц для вывода, ключи:
	 *  string _a Ссылка на страницу
	 *  string uri Идентификатор
	 *  string title Название
	 *  string parents Идентификаторы родителей (parents), разделенных запятыми (если они, конечно, есть)
	 *  int pos Позиция страницы
	 * @return string */
	public static function StaticSubstance($statics)
	{
		static::RssLink();
		return T::$T->Title(end($GLOBALS['title']))
		.($statics ? T::$T->OpenTable().self::SubstanceItems($statics).T::$T->CloseTable() : '');
	}

	/** Внутренний шаблон отображения содержания */
	protected static function SubstanceItems($statics)
	{
		$parents=reset($statics);
		$l=strlen($parents['parents']);
		$c='<ul>';#Content
		$n=-1;
		$nonp=true;#No new page

		foreach($statics as &$v)
		{
			++$n;
			$nl=strlen($v['parents']);

			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif($nonp)
				{
					$c.=self::SubstanceItems(array_slice($statics,$n,count($statics)-$n,true));
					$nonp=false;
				}
				continue;
			}

			if($n>0)
				$c.='</li>';

			$c.='<li><a href="'.$v['_a'].'">'.$v['title'].'</a>';
			$nonp=true;
		}

		return$c.'</li></ul>';
	}

	/** Внутренний шаблон добавления RSS ссылки в заголовки сайта */
	protected static function RssLink()
	{
		\CMS\Templates\RssLink($GLOBALS['Eleanor']->module['links']['rss'],
			is_array($GLOBALS['title']) ? reset($GLOBALS['title']) : $GLOBALS['title']);
	}
}

return StaticPage::class;