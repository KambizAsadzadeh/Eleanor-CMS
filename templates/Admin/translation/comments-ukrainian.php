<?php
use \Eleanor\Classes\Language\Ukrainian;
defined('CMS\STARTED')||die;

return[
	#Для Classes/Comments.php
	'list'=>'Список коментарів',
	'news'=>function($n){
		return$n.Ukrainian::Plural($n,[' новий коментар',' нових коментаря',' нових коментарів']);
	},
	'deleting'=>'Ви дійсно хочете видалити коментар &quot;%s&quot;?',
	'filter'=>'Фільтр',
	'module'=>'Модуль',
	'date'=>'Дата',
	'author'=>'Автор',
	'published'=>'Опубліковано в',
	'text'=>'Текст',
	'cnf'=>'Коментарі не знайдені',
	'cnw'=>'Коментарів поки ніхто не написав',
	'cpp'=>'Коментарів на сторінку: %s',
	'blocked'=>'Заблокувано',
	'status'=>'Статус',
];