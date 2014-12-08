<?php
use \Eleanor\Classes\Language\Russian;
defined('CMS\STARTED')||die;

return[
	#Для Classes/Comments.php
	'list'=>'Список комментариев',
	'news'=>function($n){
		return$n.Russian::Plural($n,[' новый комментарий',' новых комментария',' новых комментариев']);
	},
	'deleting'=>'Вы действительно хотите удалить комментарий &quot;%s&quot;?',
	'filter'=>'Фильтр',
	'module'=>'Модуль',
	'date'=>'Дата',
	'author'=>'Автор',
	'published'=>'Опубликовано в',
	'text'=>'Текст',
	'cnf'=>'Комментарии не найдены',
	'cnw'=>'Комментариев пока никто не написал',
	'cpp'=>'Комментариев на страницу: %s',
	'blocked'=>'Заблокирован',
	'status'=>'Статус',
];