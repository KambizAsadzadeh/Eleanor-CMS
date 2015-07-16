<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Templates\Easy;
use CMS\Eleanor, Eleanor\Classes\Html;

/*
	#ToDo! Нужно где-то разместить ссылки на:
	$GLOBALS['Eleanor']->module['links']['all'] - Все новости
	$GLOBALS['Eleanor']->module['links']['categories'] - Категории
	$GLOBALS['Eleanor']->module['links']['tags'] - Теги
	$GLOBALS['Eleanor']->module['links']['search'] - Поиск по новостям
	$GLOBALS['Eleanor']->module['links']['add'] - Добавить новость
	$GLOBALS['Eleanor']->module['links']['my'] - Мои новости

	Я помню, что ты хотел исключить такие страницы, как "Категории". Окей, без проблем. Но остальные ссылки-то нужно куда-то вывести. Нет?
*/

/** Шаблон для пользователей модуля новости */
class UserNews
{
	/** @var array Языковые значения */
	public static $lang=[];

	/** Внутренний метод. Важный момент Cron */
	protected static function TopMenu($tit=false)
	{
		$GLOBALS['scripts'][]=Eleanor::$Template->default['theme'].'js/publications.js';
		#Cron
		$cron=$GLOBALS['Eleanor']->module['cron'] ? '<img src="'.$GLOBALS['Eleanor']->module['cron'].'" style="width:1px;height1px;" />' : '';
		#[E] Cron

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return Eleanor::$Template->Title(($tit ? $tit : $lang['n']).$cron);
	}

	protected static function List_($data,$shst=false)
	{
		$GLOBALS['head'][__CLASS__]=Html::JSON(['module'=>$GLOBALS['Eleanor']->module['name']],true,false,'');

		$lcomm=static::$lang['comments'];
		$lviews=static::$lang['views'];
		$r='';
		$marks=range(Eleanor::$vars['publ_lowmark'],Eleanor::$vars['publ_highmark']);
		if(false!==$z=array_search(0,$marks))
			unset($marks[$z]);

		foreach($data['items'] as $k=>$news)
		{
			$tags='';
			foreach($news['tags'] as &$tv)
				if(isset($data['tags'][$tv]))
					$tags.='<a href="'.$data['tags'][$tv]['_url'].'">'.$data['tags'][$tv]['name'].'</a> ';

			$status=false;

			if(isset($news['status']) and $shst)
				switch($news['status'])
				{
					case-1:
						$status='<span class="ptag pt-status" style="color:darkyellow">'.static::$lang['waitmod'].'</span>';
					break;
					case 1:
						$status='<span class="ptag pt-status" style="color:green">'.static::$lang['activated'].'</span>';
					break;
					default:
						$status='<span class="ptag pt-status">'.static::$lang['deactivated'].'</span>';
				}

			$status=$status ? '<span class="ptag pt-stats"><b class="thd">*</b>'.$status.'</span>' : '';

			$title=$news['_readmore'] ? '<a href="'.$news['_url'].'">'.$news['title'].'</a>' : $news['title'];
			$rating=Eleanor::$vars['publ_rating'] ? '<div class="rate-bt rate-bt-up">'.static::Rating($k,$news['_canrate'],$news['r_total'],$news['r_average'],$news['r_sum'],$marks,false).'</div>' : '';
			$humandate=Eleanor::$Language->Date($news['date'],'fdt');
			$category=isset($data['cats'][$news['_cat']]) ? '<span class="ptag pt-cat"><b class="thd">*</b><a href="'.$data['cats'][$news['_cat']]['_a'].'">'.$data['cats'][$news['_cat']]['t'].'</a></span>' : '';
			$author=$news['author_id'] ? '<a href="'.Eleanor::$Login->UserLink($news['author'],$news['author_id']).'" rel="author">'.$news['author'].'</a>' : $news['author'];
			$text=$news['announcement'].($news['_hastext'] ? '<div id="more-'.$k.'" style="display:none"></div>' : '');
			$tags=$tags ? '<p class="post-tags">'.rtrim($tags).'</p>' : '';
			$readmore=$news['_readmore'] ? '<span class="btnmin postmore"><a class="pt-more" href="'.$news['_url'].'#more"><b>'.static::$lang['indetail'].'</b></a><a href="#" data-id="'.$k.'" data-more="#more-'.$k.'" class="thd getmore" title="'.static::$lang['expandn'].'" data-lang="'.static::$lang['collapse'].'">'.static::$lang['expand'].'</a></span>' : '';
			$edit=$news['_aedit'] ? '<span class="btnmin pt-moder"><a class="iequick indev" href="#" title="'.static::$lang['quicke'].'">'.static::$lang['quicke'].'</a><a class="iedit" href="'.$news['_aedit'].'" title="'.static::$lang['fulle'].'">'.static::$lang['fulle'].'</a><a class="idel" href="'.$news['_adel'].'" title="'.static::$lang['delete'].'">'.static::$lang['delete'].'</a></span>' : '';
			$views=$lviews($news['reads']);
			$comments=$lcomm($news['comments'],$news['comments']);

			$r.=<<<HTML
<article class="post">
	<h2>{$title}</h2>
	<div class="post-inf">
		{$rating}
		<span class="ptag pt-date"><b class="thd">*</b>{$humandate}</span>
		{$category}
		<span class="ptag pt-user"><b class="thd">*</b>{$author}</span>
	</div>
	<div class="pcont">{$text}</div>
	{$tags}
	<div class="post-foot">
		{$readmore}
		{$edit}
		<span class="ptag pt-view"><b class="thd">*</b>{$views}</span>
		<span class="ptag pt-coms"><b class="thd">*</b><a href="{$news['_url']}#comments">{$comments}</a></span>
		{$status}
	</div>
</article>
HTML;
		}
		return$r;
	}

	/*
		Список новостей на главной сайта и главной модуля
		$data - массив данных. Ключи:
			items - массив новостей. Формат: id=>array()
				date - дата публикации новости
				author - имя автора новости
				author_id - идентификатор автора новости
				status - статус новости (1 - активна, 0 - заблокирована, -1 - ожидает модерации)
				reads - число просмотров
				comments - число комментариев
				tags - массив идентификаторов тегов новости
				title - заголовок новости
				announcement - анонс новости
				voting - флаг наличия опроса в новости
				r_sum - сумма всех оценок
				r_total - число оценок
				r_average - средняя оценка

				_aedit - ссылка на редактирование новости, либо false
				_adel - ссылка на удаление новости, либо false
				_cat - идентификатор категории новости
				_readmore - флаг наличия подробной новости
				_hastext - флаг наличия подробного текста новости
				_url - ссылка на новость
				_canrate - флаг возможности оценивать новость
			cats - массив категорий. Формат: id=>array()
				_a - ссылка на категорию
				t - название категории
			tags - массив тегов. Формат: id=>array(), ключи внутреннего массива:
				_url - ссылка на новости с тегом
				name - имя тега
				cnt - количество новостей с данным тегом
		$cnt - количество новостей всего
		$page - номер страницы, на которой мы сейчас находимся
		$pp - число новостей на страницу
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($data,$cnt,$page,$pages,$pp,$links)
	{
		return static::TopMenu().static::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$pages=>$links['first_page']));
	}

	/*
		Страница вывода новостей за определенную дату
		$data - дата
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function DateList($date,$data,$cnt,$page,$pages,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$pages=>$links['first_page']));
	}

	/*
		Страница вывода новостей пользователя (своих)
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function MyList($data,$cnt,$page,$pages,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data,false).Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']));
	}

	/*
		Страница вывода новостей определенной категории
		$category - данные категории, массив с ключами:
			id - идентификатор категории
			title - название категории
			description - описание категории
		Описание остальных переменных доступно в методе List
	*/
	public static function CategoryList($category,$data,$cnt,$page,$pages,$pp,$links)
	{
		return self::ShowCategories($category['id']).self::List_($data)
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$pages=>$links['first_page']));
	}

	/*
		Страница вывода всех категорий
	*/
	public static function ShowCategories($cat=0)
	{
		#ToDo!
	}

	/*
		Страница вывода всех тегов
	*/
	public static function ShowAllTags()
	{
		$tags=clone Eleanor::$Template;
		foreach($GLOBALS['Eleanor']->module['tags'] as &$v)
			$tags->Tag($v);
		return static::TopMenu().'<p class="post-tags">'.$tags.'</p>';
	}

	/*
		Страница вывода новостей за определенную дату
		$data - дата
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function TagsList($tag,$data,$cnt,$page,$pages,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title']))
			.($data['items'] ? self::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$pages=>$links['first_page'])) : Eleanor::$Template->Message(sprintf(static::$lang['notag'],$tag['name']),'info'));
	}

	/*
		Страница поиска новостей
		$values - значение полей поиска формы, массив с ключами:
			text - поисковый запрос
			where - где искать: в заголовке, в заголовке и анонсе, в заголовке, анонсе и тексте (t,ta,tat)
			tags - массив тегов
			categs - массив категорий
			sort - порядок сортировки (date,relevance)
			c - поиск в массиве категорий И или ИЛИ (and,or)
			t - поиск в массиве тегов И или ИЛИ (and,or)
		$error - ошибка, если пустая, значит ошибки нет
		$tags - массив тегов, формат id=>имя тега
		$links - массив ссылок, ключи:
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
		Описание остальных переменных доступно в методе List
	*/
	public static function Search($values,$error,$tags,$data,$cnt,$page,$pages,$pp,$links)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$tagopts='';
		foreach($tags as $k=>&$v)
			$tagopts.=Eleanor::Option($v,$k,in_array($k,$values['tags']));
		$Lst=Eleanor::LoadListTemplate('table-form');

		if($data and $data['items'])
		{
			if($values['text'])
			{
				$mw=preg_split('/\s+/',$values['text']);
				foreach($data['items'] as &$v)
				{
					$v['title']=Strings::MarkWords($mw,$v['title']);
					$v['announcement']=Strings::MarkWords($mw,$v['announcement']);
				}
			}
			$results='<br /><br />'.self::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']));
		}
		else
			$results='';
		return static::TopMenu(static::$lang['lookfor'])
			.($error ? Eleanor::$Template->Message($error,'error') : '')
			.($cnt===0 ? Eleanor::$Template->Message(static::$lang['notfound'],'info') : '')
			.'<form method="post">'
			.$Lst->begin()
				->item(static::$lang['what'],Eleanor::Input('text',$values['text']))
				->item(static::$lang['swhere'],Eleanor::Select('where',Eleanor::Option(static::$lang['title'],'title',$values['where']=='t').Eleanor::Option(static::$lang['ta'],'ta',$values['where']=='ta').Eleanor::Option(static::$lang['tat'],'tat',$values['where']=='tat')))
				->item($lang['categs'],Eleanor::Items('categs',$GLOBALS['Eleanor']->Categories->GetOptions($values['categs'])).'<br /><label>'.Eleanor::Radio('c','and',$values['c']=='and').static::$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('c','or',$values['c']=='or').static::$lang['or'].'</label>')
				->item(static::$lang['tags'],Eleanor::Items('tags',$tagopts).'<br /><label>'.Eleanor::Radio('t','and',$values['t']=='and').static::$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('t','or',$values['t']=='or').static::$lang['or'].'</label>')
				->item(static::$lang['sortby'],Eleanor::Select('sort',Eleanor::Option(static::$lang['sdate'],'date',$values['sort']=='date').Eleanor::Option(static::$lang['srel'],'relevance',$values['sort']=='relevance')).'</label>')
				->button(Eleanor::Button(static::$lang['find']))
				->end()
			.'</form>'
			.$results;
	}

	/*
		Страница подробного просмотра новости
		$news - массив новости, ключи:
			id - идентификатор новости в БД
			date - дата новости
			author - имя автора новости
			author_id - идентификатор автора новости
			status - статус новости (1 - активна, 0 - заблокирована, -1 - ожидает модерации)
			reads - число просмотров
			comments - число комментариев
			title - заголовок новости
			announcement - анонс новости
			text - подробный текст новости
			_aedit - ссылка на редактирование новости, либо false
			_adel - ссылка на удаление новости, либо false
			_cat - идентификатор основной категории новости
			_sokr - анонс новости
			_tags - массив всех тегов новости. Формат: id=>array(), ключи внутреннего массива:
				_a - ссылка на новости данного тега
				tag'=>$temp['name']),true,''),'name'=>$temp['name']);
		$category
			id - идентификатор категории
			title - название категории
			description - описание категории
			_a - ссылка на новости из данной категории
		$voting - HTML опроса новости, либо false
		$comments - HTML комментариев
		$hl - массив слов, которые необходимо подсветить в новости
	*/
	public static function Show($news,$category,$voting,$comments,$hl)
	{
		if($hl)
		{
			$news['title']=Strings::MarkWords($hl,$news['title']);
			$news['text']=Strings::MarkWords($hl,$news['text']);
			if($news['announcement'])
				$news['announcement']=Strings::MarkWords($hl,$news['announcement']);
		}
		$tags='';
		foreach($news['_tags'] as &$v)
			$tags.='<a href="'.$v['_a'].'">'.$v['name'].'</a> ';

		switch($news['status'])
		{
			case-1:
				$status='<span style="font-weight:bold;color:darkyellow">'.static::$lang['waitmod'].'</span>';
			break;
			case 0:
				$status='<span style="font-weight:bold;">'.static::$lang['deactivated'].'</span>';
			break;
			default:
				$status=false;
		}
		$status=$status ? '<span class="ptag pt-stats"><b class="thd">*</b>'.$status.'</span>' : '';

		if($voting)
			$voting->type='main';

		$marks=range(Eleanor::$vars['publ_lowmark'],Eleanor::$vars['publ_highmark']);
		if(false!==$z=array_search(0,$marks))
			unset($marks[$z]);

		$lviews=static::$lang['views'];
		$views=$lviews($news['reads']);

		$rating=Eleanor::$vars['publ_rating'] ? static::Rating($news['id'],$news['_canrate'],$news['r_total'],$news['r_average'],$news['r_sum'],$marks,true) : '';
		$humandate=Eleanor::$Language->Date($news['date'],'fdt');
		$category=$category ? '<span class="ptag pt-cat"><i class="thd">*</i><a href="'.$category['_a'].'">'.$category['title'].'</a></span>' : '';
		$author=$news['author_id'] ? '<a href="'.$news['_author'].'" rel="author">'.$news['author'].'</a>' : $news['author'];
		$text=($news['announcement'] ? $news['announcement'].'<a id="more"></a>' : '').$news['text'];
		$voting=$voting ? '<a id="voting"></a>'.$voting : '';
		$tags=$tags ? '<p class="post-tags">'.$tags.'</div>' : '';
		$edit=$news['_aedit'] ? '<span class="btnmin pt-moder"><a class="iequick indev" href="#" title="'.static::$lang['quicke'].'">'.static::$lang['quicke'].'</a><a class="iedit" href="'.$news['_aedit'].'" title="'.static::$lang['fulle'].'">'.static::$lang['fulle'].'</a><a class="idel" href="'.$news['_adel'].'" title="'.static::$lang['delete'].'">'.static::$lang['delete'].'</a></span>' : '';

		return static::TopMenu().<<<HTML
<article class="post">
	<h1 class="midtitle">{$news['title']}</h1>
	<div class="post-inf">{$rating}
		<span class="ptag pt-date"><i class="thd">*</i>{$humandate}</span>
		{$category}
		<span class="ptag pt-user"><i class="thd">*</i>{$author}</span>
	</div>
	<div class="pcont">{$text}
	{$voting}
	{$tags}
	<div class="post-foot">
		<div class="shareline">
			<!-- AddThis Button BEGIN -->
			<div class="addthis_toolbox addthis_default_style" addthis:title="Обновление CMS Eleanor 1.0a" addthis:description="Основная фишка обновления - свой форум. Много сил было потрачено, поэтому спасибо всем, кто дождался, кто помагал и кто не мешал. Основная трудность была в том, что при разработке форума НЕЛЬЗЯ выходить за пределы модуля. Это, оказывается, не так просто...">
			<a class="addthis_button_preferred_1"></a>
			<a class="addthis_button_preferred_2"></a>
			<a class="addthis_button_preferred_3"></a>
			<a class="addthis_button_preferred_4"></a>
			<a class="addthis_button_compact"></a>
			<a class="addthis_counter addthis_bubble_style"></a>
			</div>
			<script src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4fbcea485ff1a03d"></script>
			<script>
			var addthis_share = {
				templates: { twitter: "{{title}} {{url}} (@Eleanor_CMS)"}
			};
			</script>
			<!-- AddThis Button END -->
		</div>
		{$edit}
		<span class="ptag pt-view"><i class="thd">*</i>{$views}</span>
		{$status}
	</div>
</article>
{$comments}
HTML;
	}

	/*
		Вывод рейтинга новости
		$id - ID новости
		$can - возможность выставить оценку
		$sum - сумма всех оценок
		$total - число оценок
		$average - средняя оценка
		$marks - массив возможных оценок
	*/
	public static function Rating($id,$can,$total,$average,$sum,$marks,$full=false)
	{
		$min=min($marks);
		$max=max($marks);
		if($full)
		{
			$fmin=$min;
			$fmax=$max;
		}
		foreach($marks as &$v)
			if($v>0 and $v<$max)
				$max=$v;
			elseif($v<0 and $v>$min)
				$min=$v;

		if($average>$max)
			$average=$max;
		$average/=$max;
		$width=$total>0 ? round($average*100,1) : 0;

		if($can)
		{
			if(!isset($GLOBALS['head']['rating']))
			{
				$GLOBALS['scripts'][]=Eleanor::$Template->default['theme'].'js/rating.js';
				$GLOBALS['head']['rating']='<script>$(function(){ Rating("'.$GLOBALS['Eleanor']->module['name'].'",'.$min.','.$max.'); });</script>';
			}
			$r='<div class="rate-bt rate-bt-'
				.($sum>0 ? 'up' : 'down')
				.'" data-id="'.$id.'"><span class="rt-num">'.$sum.'</span><i class="rt-line"><b style="width:'.$width.'%">'.$width
				.'%</b></i><a class="rt-bt-min" href="#"><b>&minus;</b></a><a class="rt-bt-pls" href="#"><b>+</b></a></div>';
		}
		else
			$r='<div class="rate-bt rate-bt-'
				.($sum>0 ? 'up' : 'down')
				.'"><span class="rt-num">'.$sum.'</span><i class="rt-line"><b style="width:'.$width.'%">'.$width
				.'%</b></i><span class="rt-bt-min"><b>&minus;</b></span><span class="rt-bt-pls"><b>+</b></span></div>';

		if($full)
		{
			$cnt=count($marks);
			$r.='<div itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="hidden"><span itemprop="ratingValue">'.round($cnt*$average,1).'</span><span itemprop="ratingCount">'.$total.'</span><span itemprop="bestRating">'.$cnt.'</span><span itemprop="worstRating">1</span></div>';
		}
		return$r;
	}
}
TplUserNews::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/news-*.php',false);