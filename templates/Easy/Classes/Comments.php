<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Templates\Easy;
use CMS\Eleanor, Eleanor\Classes\Html;

/** Оформление комментариев */
class Comments
{
	/** @var array Языковые значения */
	public static $lang=[];

	/*
		Элемент шаблона: отображение комментариев
		$rights - массив прав пользователя в комментариях, ключи:
			edit - право редактировать свои комментарии. Если число - это количество секунд, по истечению которых после написания комментария право теряется.
			delete - право удалять свои комментарии. Если число - это количество секундпо истечению которых после написания комментария право теряется.
			post - право создавать новые комментарии, свойство определяет статус новых комментариев: -1 - для перемодерации, 0 - для блокировки, 1 - без премодерации, false - для запрета публикации
			medit - Право редактировать чужие комментарии
			mdelete - Право удалять чужие комментарии
			ip - Право просматривать IP с которых были отправлены комментарии
			status - право менять статусы постов

		$pagpq  - от posts+authors+groups+parent+quotes массив значений:
			posts - массив комментариев. Формат: id=>array(), ключи внутреннего массива:
				status - статус комментария
				parents - массив ID всех родителей комментария
				date - дата комментариея
				answers - число ответов на данный комментарий
				author_id - идентификатор автора комментария
				author - имя автора комментария
				ip (при наличии прав) - IP адрес, с которого был оставлен комментарий
				_n - порядковый номер комментария
				_afind - ссылка на комментарий
				_achilden - ссылка на ветку данного комментария
				_edit - флаг возможности редактирования комментария
				_delete - флаг возможности удаления комментария
			authors - массив авторов всех комментариев. Формат id=>array(), ключи внутреннего массива:
				_group - идентификатор группы автора
				name - имя автора (не безопасный HTML)
				signature - подпись автора
				avatar - местоположения аватара
				avatar_type - тип аватара (uploaded,url,gallery)
				_online - флаг наличия пользователя онлайн
			groups - массив групп авторов всех комментариев. Формат id=>array(), ключи внутреннего массива:
				title - название группы
				style - стиль группы
			parent - массив родительского комментария, ключи:
				id - идентификатор комментария
				описание остальных ключей (status, parents, date, answers, author_id, author, ip, text, _edit, _delete, _afind, _n) смотрите выше.
			quotes - массив цитат из родительских комментариев (комментариев, ответом на которые, является текущий комментарий). Формат id=>text.
				Цитаты отсортированы в родительском порядке (комментарии 1 и 2):
					Комментарий 1
						Комментарий 2: Ответ на комментарий 1:
								Комментарий 3 (текущий комментарий, не входит в список цитаты): Ответ на комментарий 2
				Каждая цитата содержит в себе строку <!-- SUBQUOTE --> для вставки подцитаты.
		$postquery - этот массив параметров должен быть передан в $_POST запросе при ajax запросе
		$dataquery - содержимое ajax-запроса должно быть передано методом POST в этих ключах
		$cnt - количество комментариев всего
		$pp - количество комментариев на страницу
		$page - номер текущей страницы на которой мы находимся
		$statuses - массив количества комментариев каждого статуса. Ключи массива - числовые выражения статуса комментариев
		$gname - имя гостя, если зашли под пользователем, эта переменная равна false
		$captcha - капча при написании комментария
		$links
			first_page - ссылка на первую страницу комментариев
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowComments($rights,$pagpq,$postquery,$dataquery,$cnt,$pp,$page,$pages,$statuses,$gname,$captcha,$links)
	{
		array_push($GLOBALS['scripts'],'js/eleanor_comments.js','js/eleanor_comments-'.Language::$main.'.js');

		$editor='';
		if($rights['post']!==false)
		{
			$editor.=($rights['post']==-1 ? Eleanor::$Template->Message(static::$lang['needch'],'info') : '')
				.'<fieldset>
					<form id="newcomment">
						<h3 class="midtitle answerto">'.static::$lang['addc'].'</h3>'
						.($gname===false ? '' : '<div class="field imp">'.Html::Input('name',$gname,['tabindex'=>1,'placeholder'=>static::$lang['yn*'],'title'=>static::$lang['yn'],'size'=>45]).'</div>')
						.'<div class="field">'.$GLOBALS['Eleanor']->Editor->Area('text','',['bb'=>['tabindex'=>2,'placeholder'=>static::$lang['yc']]]).'</div>'
						.($captcha ? '<div class="field imp">'.$captcha.'<br />'.Html::Input('check','',['tabindex'=>3,'autocomplete'=>'off','placeholder'=>static::$lang['captcha'],'title'=>static::$lang['captcha_']]).'</div>' : '')
						.Html::Input('parent',$pagpq[3] ? $pagpq[3]['id'] : 0,['type'=>'hidden'])
						.'<button class="wh-btn" title="'.static::$lang['addc'].'" type="submit">'.static::$lang['addc'].'</button>
					</form>
				</fieldset>';
		}
		$reverse=$page<1;
		$pager=Eleanor::$Template->Pages($cnt,$pp,$page,[$links['pages'],$reverse ? $pages : 1=>$links['first_page']],'C.GoToPage','comments');
		if($pagpq[3])
			Eleanor::LoadOptions('user-profile');

		$r='<div id="comments">
			<h3 class="midtitle"><span class="com-nums cnt">'.$cnt.'</span>'.static::$lang['comments'].'</h3>';

		if($pagpq[3])
			$r.='<ol><li class="commentitem">'.static::CommentsPost($rights,$pagpq[3]['id'],$pagpq[3],true,$pagpq[1],$pagpq[2],$pagpq[4],static::$lang)
				.'<ul class="children comments"'.($pagpq[0] ? '><li class="commentitem">'.static::CommentsPosts($rights,$pagpq,static::$lang).'</li>' : ' style="display:none">').'</ul></li></ol>';
		else
			$r.='<ol class="comments"'.($pagpq[0] ? '><li class="commentitem">'.static::CommentsPosts($rights,$pagpq,static::$lang).'</li>' : ' style="display:none">').'</ol>';

		return$r
			.'<div class="nocomments"'.($pagpq[0] ? ' style="display:none">' : '>'.Eleanor::$Template->Message($pagpq[3] ? static::$lang['anc'] : static::$lang['nc'],'info')).'</div>
			<div class="paginator"'.($pager ? '>'.$pager : ' style="display:none">').'</div>'
			.($rights['status'] ? '<fieldset class="moderate select-comments"'.($pagpq[0] ? '>'.static::CommentsModerate($rights) : ' style="display:none">').'</fieldset>' : '')
			.'<div class="status" id="commentsinfo"></div><div style="text-align:center;margin-bottom:15px"><a href="#" class="link-button cb-lnc" style="width:250px"><b>'.static::$lang['lnp'].'</b></a></div>'
			.$editor.'</div><script>/*<![CDATA[*/var C;$(function(){C=new CORE.Comments('.Html::JSON([
				'lastpost'=>time(),
				'postquery'=>$postquery,
				'!dataquery'=>'["'.join('","',$dataquery).'"]',
				'nextn'=>$statuses[1]+$statuses[0],
				'reverse'=>$reverse,
				'page'=>$page,
				'pages'=>$pages,
				'baseurl'=>$links['first_page'],
				'parent'=>$pagpq[3] ? (int)$pagpq[3]['id'] : 0,
			],false,true,'').')})//]]></script>';
	}

	/*
		Элемент шаблона: "прослойка" при склеивании комментариев
		$diff - массив разницы текущего времени и ранее опубликованного комментария, ключи:
			0 - годы
			1 - месяцы
			2 - дни
			3 - часы
			4 - минуты
			5 - секунды
	*/
	public static function CommentsAddedAfter($diff)
	{
		return'<br /><br /><span class="small">'.call_user_func_array(static::$lang['added_after'],$diff).':</span><br />';
	}

	/*
		Элемент массива. Оформления цитаты
		$q - массив цитаты, ключи:
			name - имя пользователя
			date - дата цитаты
			find - ссылка на оригинальный комментарий
			id - идентификатор поста, который цитируется
			text - текст цитаты
	*/
	public static function CommentsQuote($q)
	{
		return'<blockquote class="extend"><div class="top">'
		.sprintf(
			static::$lang['cite'],
			($q['name'] || $q['date'] ? ' ('.$q['name'].' @ '.$q['date'].')' : '')
			.($q['id'] ? ' <a href="'.$q['find'].'" data-id="'.$q['id'].'" class="cb-gocomment" target="_blank"><img src="'.Eleanor::$Template->default['theme'].'images/findpost.gif" /></a>' : '')
		)
		.'</div><div class="text">'.$q['text'].'</div></blockquote>';
	}

	/*
		Элемент шаблона: загрузка новых комментариев
		Описание входящих параметров смотрите в методе ShowComments (выше).
	*/
	public static function CommentsLNC($rights,$pagpq)
	{
		if($pagpq[3])
			\CMS\LoadOptions('user-profile');

		return array(
			'moderate'=>$rights['status'] && $pagpq[0] ? static::CommentsModerate($rights) : '',
			'comments'=>$pagpq[0] ? '<li class="commentitem">'.static::CommentsPosts($rights,$pagpq).'</li>' : '',
		);
	}

	/*
		Элемент шаблона: Загрузка страницы на AJAX.
		Описание входящих параметров смотрите в методе ShowComments (выше).
	*/
	public static function CommentsLoadPage($rights,$pagpq,$cnt,$pp,$page,$pages,$parent,$links)
	{
		$r=array('paginator'=>Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$page<1 ? $pages : 1=>$links['first_page']),'C.GoToPage','comments'));
		if($pagpq)
		{
			if($pagpq[0])
				$r['comments']='<li class="commentitem">'.static::CommentsPosts($rights,$pagpq).'</li>';
			else
			{
				$r['moderate']=$r['comments']='';
				$r['nocomments']=Eleanor::$Template->Message($parent ? static::$lang['anc'] : static::$lang['nc'],'info');
			}
		}
		return$r;
	}

	/*
		Элемент шаблона: форма редактирования комментария
		$a - массив редактируемого комментария, ключи:
			id - идентификатор комментария
			status - статус комментария
			date - дата комментария
			author_id - идентификатор автора комментария
			author - имя автора комментария
			text - текст комментария
	*/
	public static function CommentsEdit($a)
	{
		return'<form>'.$GLOBALS['Eleanor']->Editor->Area('text'.$a['id'],$a['text']).'<div style="text-align:center">'.Eleanor::Button(static::$lang['save']).' '.Eleanor::Button(static::$lang['cancel'],'button',array('class'=>'cb-cancel')).'</div></form>';
	}

	/*
		Элемент шаблона: текст комментария, после его сохранения (редактирования)
			$text - текст текущего комментария
			$quotes - массив цитат из родительских комментариев (комментариев, ответом на которые является текущий комментарий). Формат id=>text.
				Цитаты отсортированы в родительском порядке (комментарии 1 и 2):
					Комментарий 1
						Комментарий 2: Ответ на комментарий 1:
							Комментарий 3 (текущий комментарий, не входит в список цитаты): Ответ на комментарий 2
			Каждая цитата содержит в себе строку <!-- SUBQUOTE --> для вставки подцитаты.
	*/
	public static function CommentsAfterEdit($text,$quotes)
	{
		$pq='';
		if(count($quotes)>2)
			array_splice($quotes,-2);
		foreach($quotes as &$v)
			$pq=str_replace('<!-- SUBQUOTE -->',$pq,$v);
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);
		return$pq.$text;
	}

	protected static function CommentsModerate($rights)
	{
		$GLOBALS['scripts'][]='js/checkboxes.js';
		return'<span>'.static::$lang['moderate'].'</span>'.Eleanor::Select('',Eleanor::Option(static::$lang['withsel'],'').Eleanor::Option(static::$lang['doact'],1).Eleanor::Option(static::$lang['toblock'],0).Eleanor::Option(static::$lang['tomod'],-1).($rights['mdelete'] ? Eleanor::Option(static::$lang['delete'],'delete') : ''),array('class'=>'modevent')).' '.Eleanor::Check('',false,array('id'=>'masscheck'));
	}

	protected static function CommentsPosts($rights,$pagpq)
	{
		$c='';
		if($pagpq[0] and !$pagpq[3])
			Eleanor::LoadOptions('user-profile');
		$mass=$rights['status'] || $rights['mdelete'];

		$app='</li><li class="commentitem">';
		foreach($pagpq[0] as $k=>&$v)
			$c.=static::CommentsPost($rights,$k,$v,$mass,$pagpq[1],$pagpq[2],$pagpq[4]).$app;
		return$c ? substr($c,0,-strlen($app)) : '';
	}

	protected static function CommentsPost($rights,$id,$comment,$mass,$authors,$groups,$quotes)
	{
		$la=static::$lang['answers'];
		$author=isset($authors[$comment['author_id']]) ? $authors[$comment['author_id']] : false;
		$group=$author && isset($groups[$author['_group']]) ? $groups[$author['_group']] : false;

		if(!$author)
			$avatar='images/avatars/guest.png';
		else
			switch($author['avatar'] ? $author['avatar_type'] : '')
			{
				case'gallery':
					$avatar='images/avatars/'.$author['avatar'];
				break;
				case'upload':
					$avatar=Eleanor::$uploads.'/avatars/'.$author['avatar'];
				break;
				case'url':
					$avatar=$author['avatar'];
				break;
				default:
					$avatar='images/avatars/user.png';
			}

		switch($comment['status'])
		{
			case -1:
				$status='<li><span style="color:orange;font-weight:bold">'.static::$lang['stmodwait'].'</span></li>';
				$data['postn']='?';
			break;
			case 0:
				$status='<li><span style="color:red;font-weight:bold">'.static::$lang['stblocked'].'</span></li>';
			break;
			default:
				$status='';
		}

		$pq='';
		if(count($comment['parents'])>2)
			array_splice($comment['parents'],0,-2);

		foreach($comment['parents'] as &$pv)
			$pq=isset($quotes[$pv]) ? str_replace('<!-- SUBQUOTE -->',$pq,$quotes[$pv]) : '';
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);

		$signature=$author && $author['signature'] ? '<p class="signature">'.$author['signature'].'</p>' : '';
		$humandate=Eleanor::$Language->Date($comment['date'],'fdt');
		$itemnum=$comment['_n'] ? '<a class="com-id cb-findcomment" href="'.$comment['_afind'].'"><b>#'.($comment['status'] ? $comment['_n'] : '?').'</b></a>' : '';
		$rank=$group ? $group['style'].$group['title'] : false;
		$online=$author && $author['_online'] ? '<li><span class="online">Сейчас на сайте</span></li>' : '';
		$ip=$rights['ip'] ? '<li><a href="http://eleanor-cms.ru/whois/'.$comment['ip'].'" target="_blank">'.$comment['ip'].'</a></li>' : '';
		$selection=$mass && in_array($comment['status'],[-1,0,1]) ? '<li class="com-select">'.Html::Check('mass[]',false,['value'=>$id]).'</li>' : '';

		$name=$author
			? '<a href="'.$author['_a'].'" class="cb-insertnick">'.$comment['author'].'</a> <span class="com-group">('.$rank.')</span>'
			: '<span class="cb-insertnick">'.$comment['author'].'</span>';

		$quote=$comment['status']==1 && $rights['post']
			? '<li><a href="#" class="cb-quote" data-id="'.$id.'" data-date="'.$comment['date'].'" data-name="'.$comment['author'].'">'.static::$lang['qquote'].'</a></li>'
			.(isset($comment['_n']) ? '<li><a href="#" class="cb-answer" data-id="'.$id.'">'.static::$lang['answer'].'</a></li>' : '')
			: '';

		$avainfo=$author ? <<<HTML
			<ul class="ava-info">
				<li><a href="#"><img src="{$avatar}" alt="{$author['name']}" title="{$author['name']}" /></a></li>
				<li><b>{$rank}</b></li>{$online}{$ip}
			</ul>
HTML
				: '';


		$edit=$comment['_edit'] ? '<li><a href="#" class="cb-edit" data-id="'.$id.'">'.static::$lang['edit'].'</a></li>' : '';
		$delete=$comment['_delete'] ? '<li><a href="#" class="cb-delete" data-id="'.$id.'"'.(isset($comment['_n']) ? '' : ' data-recount="1"').'>'.static::$lang['delete'].'</a></li>' : '';

		return<<<HTML
<div class="comment" id="comment{$id}">
	<div class="com-top clrfix">
		<span class="ptag pt-date">{$itemnum}<i class="thd">*</i>{$humandate}</span>
		<div class="ava-min">
			<b class="ava-box"><img src="{$avatar}" alt="{$author['name']}" title="{$author['name']}" /></b>
			{$avainfo}
		</div>
		<p class="com-name">{$name}</p>
	</div>
	<div class="box">
		<div class="scont clrfix text">{$pq}{$comment['text']}</div>
		{$signature}
		<ul class="com-foot clrfix">{$selection}{$quote}{$edit}{$delete}{$status}</ul>
	</div>
</div>
HTML;
	}
}

Comments::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/comments-*.php',false);

return Comments::class;