<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor, \Eleanor\Classes\Html, \CMS\OwnBB, \Eleanor\Classes\Strings;

defined('CMS\STARTED')||die;

/** Шаблон для админки управления комментариями */
class Comments
{
	/** @var array Языковые значения */
	public static $lang;

	/** Меню раздела
	 * @param string $act Идентификатор активного пункта */
	protected static function Menu($act='')
	{
		$links=&$GLOBALS['Eleanor']->module['links'];

		$ln=static::$lang['news'];
		$GLOBALS['Eleanor']->module['navigation']=[
			[$links['list'],Eleanor::$Language['lc']['list'],'act'=>$act=='list',
				'submenu'=>$links['news']
					? [[ $links['news']['link'],$ln($links['news']['cnt']) ]]
					: false,
			],
			[$links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'],
		];
	}

	/** Страница отображения списка комментариев
	 * @param array $items Перечень комментариев, ключи:
	 *  int status Статус комментария (-1 - ожидание модерации, 0 - заблокирован, 1 - активен)
	 *  string date Дата публикации комментария
	 *  string author Имя автора комментария
	 *  int|null author_id ID автора комментария
	 *  string ip IP адресс комментария
	 *  string text Текст комментария
	 *  string _aauthor Ссылка на автора комментария
	 *  string _atoggle Ссылка на активацию/дезактивацию комментария
	 *  string _aedit Ссылка на редактирование комментария
	 *  string _adel Ссылка на удаление комментария
	 * @param int $cnt Количество комментариев всего
	 * @param int $pp Количество комментариев на страницу
	 * @param string $sort Тип сортировки
	 * @param string $order Порядок сортировки (asc, desc)
	 * @param int $page Номер текущей страницы
	 * @param array $query Запрос
	 * @param array $links Перечень необходимые ссылок:
	 *  string sort_date Сортировка по дате
	 *  string sort_author Сортировка по автору
	 *  string sort_ip Сортировка ip
	 *  string sort_id Сортировка по ID
	 *  string form_items Ссылка для параметра action формы, внутри которой происходит отображение перечня $items
	 *  callback pp Генератор ссылок на изменение количества комментариев отображаемых на странице
	 *  string first_page Ссылка на первую страницу пагинатора
	 *  callback pages Генератор ссылок на остальные страницы
	 * @param bool $embed Флаг отображения интерфейса на главной странице админки */
	public static function CommentsList($items,$cnt,$pp,$sort,$order,$page,$query,$links,$embed)
	{
		if(!$embed)
		{
			static::Menu('list');
			$GLOBALS['scripts'][]='js/checkboxes.js';
		}

		if($items)
		{
			$Lst=TableList($embed ? 6 : 7)
				->begin(
					[static::$lang['date'],70,'sort'=>$sort=='date' ? $order : false,'href'=>$links['sort_date']],
					[static::$lang['author'],70,'sort'=>$sort=='author' ? $order : false,'href'=>$links['sort_author']],
					[static::$lang['published']],
					['IP',62,'sort'=>$sort=='ip' ? $order : false,'href'=>$links['sort_ip']],
					[static::$lang['text'],300],
					[T::$lang['functs'],60,'sort'=>$sort=='id' ? $order : false,'href'=>$links['sort_id']],
					$embed ? false : [Html::Check('mass',false,['id'=>'mass-check']),10]
				);

			$images=Eleanor::$Template->default['images'];
			foreach($items as $k=>$v)
			{
				$author=htmlspecialchars($v['author'],\CMS\ENT,\Eleanor\CHARSET);
				$Lst->item(
					Eleanor::$Language->Date($v['date'],'fdt'),
					$v['_aauthor'] ? '<a href="'.$v['_aauthor'].'">'.$author.'</a>' : $author,
					$v['_a'] ? '<a href="'.$v['_a'].'" target="_blank">'.$v['_title'].'</a>' : '',
					'<a href="http://eleanor-cms.ru/whois/'.$v['ip'].'" target="_blank">'.$v['ip'].'</a>',
					Strings::CutStr(strip_tags(OwnBB::Parse($v['text'])),160),
					$Lst('func',
						[$v['_atoggle'],$v['status']<=0 ? T::$lang['activate'] : T::$lang['deactivate'],
							$v['status']<0
								? $images.'waiting.png'
								: $images.($v['status']==0 ? 'inactive.png' : 'active.png')],
						[$v['_aedit'],T::$lang['edit'],$images.'edit.png'],
						[$v['_adel'],T::$lang['delete'],$images.'delete.png']
					),
					$embed ? false : Html::Check('mass[]',false,['value'=>$k])
				);
			}

			$Lst->end();
		}
		else
			$Lst=Eleanor::$Template->Message(empty($query['fi']) ? static::$lang['cnw'] : static::$lang['cnf'],'info');

		if($embed)
			return$Lst;

		if($cnt==0)
			return Eleanor::$Template->Cover($Lst);

		return Eleanor::$Template->Cover('<script>//<![CDATA[
$(function(){
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
})//]]></script>
<form id="checks-form" action="'.$links['form_items']
			.'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.T::$lang['are_you_sure'].'\'))">'.$Lst
			.'<div class="submitline" style="text-align:right">'
			.($cnt>30
				? '<div style="float:left">'.sprintf(static::$lang['cpp'],$Lst->perpage($pp,$links['pp'])).'</div>'
				: '')
			.T::$lang['with_selected']
			.Html::Select('op',Html::Option(T::$lang['delete'],'k').Html::Option(T::$lang['active'],'a')
				.Html::Option(T::$lang['inactive'],'d').Html::Option(static::$lang['blocked'],'b'))
			.Html::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,[$links['pages'],$links['first_page']]));
	}

	/** Страница редактирования комментария
	 * @param int $id Идентификатор редактируемого комментария
	 * @param array $values Значения полей формы, ключи:
	 *  string date Дата комментария (не подлежит изменению)
	 *  string author Имя автора комментария (не подлежит изменению)
	 *  string text Текст комментария
	 *  int status Статус комментария (-1 - ожидание модерации, 0 - заблокирован, 1 - активен)
	 * @param \CMS\Editor $Editor Объект редактора
	 * @param array $module Данные модуля, ключи:
	 *  object api Объект API модуля
	 *  string title Название модуля
	 * @param bool $bypost Флаг загрузки данных из POST запроса
	 * @param array $errors Ошибки
	 * @param string $back URL возврата
	 * @param array $links Перечень необходимых ссылок, ключи:
	 *  string delete Ссылка на удаление комментария
	 *  string author Ссылка на автора комментария
	 *  array comment [URL,название материала]
	 * @return string */
	public static function Edit($id,$values,$Editor,$module,$bypost,$errors,$back,$links)
	{
		static::Menu();

		if($back)
			$back=Html::Input('back',$back,['type'=>'hidden']);

		$author=htmlspecialchars($values['author'],\CMS\ENT,\Eleanor\CHARSET);
		$Lst=TableForm()
			->form()
			->begin()
			->item(static::$lang['module'],$module['title'])
			->item(static::$lang['published'],$links['comment']
				? '<a href="'.$links['comment'][0].'" target="_blank">'.$links['comment'][1].'</a>' : '')
			->item(static::$lang['date'],Eleanor::$Language->Date($values['date'],'fdt'))
			->item(static::$lang['author'],$links['author'] ? '<a href="'.$links['author'].'">'.$author.'</a>' : $author)
			->item(static::$lang['text'],$Editor->Area('text',$values['text'],['post'=>$bypost]))
			->item(static::$lang['status'],Html::Select('status',Html::Option(T::$lang['activate'],1,$values['status']==1)
				.Html::Option(T::$lang['deactivate'],0,$values['status']==0)
				.Html::Option(T::$lang['waiting_act'],-1,$values['status']==-1)))
			->button($back.Html::Button(T::$lang['save']).' '.Html::Button(T::$lang['delete'],'button',
				['onclick'=>'window.location=\''.$links['delete'].'\'']))
			->end()
			->endform();

		return Eleanor::$Template->Cover($Lst,$errors);
	}

	/** Страница удаления комментария
	 * @param array $comment Удаляемый комментарий, ключи:
	 *  string text - текст удаляемого комментария
	 * @param string $back URL возврата */
	public static function Delete($comment,$back)
	{
		static::Menu();

		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(
			sprintf(static::$lang['deleting'],Strings::CutStr(strip_tags($comment['text']),200))
		,$back));
	}

	/** Обертка для настроек
	 * @param string $c Интерфейс настроек
	 * @return string */
	public static function Options($c)
	{
		static::Menu('options');
		return$c;
	}
}
Comments::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/comments-*.php',false);

return Comments::class;