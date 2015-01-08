<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Template, \CMS\Eleanor;

/** Несущий класс шаблона */
class T extends Template
{
	public static
		/** @var Template Основной объект шаблона */
		$T,

		/** @var \Eleanor\Classes\Language Основные языковые параметры */
		$lang,

		/** @var array Место хранение служебных данных */
		$data;
}

/** Генератор шаблонизатора таблиц
 * @param int $columns Количество столбцов таблицы
 * @return Template */
function TableList($columns)
{
	$a=\Eleanor\AwareInclude(__DIR__.'/Lists/table-list.php',['columns'=>$columns]);
	return new Template([$a]);
}

/** Генератор шаблонизатора форм #Old....
 * @return Template */
function TableForm()
{
	$a=\Eleanor\AwareInclude(__DIR__.'/Lists/table-form.php');
	return new Template([$a]);
}

/** Аватарка для любой строки
 * @param string $name Строка
 * @return string */
function ItemAvatar($name)
{
	$colours=[
		'8B008B', '9932CC', '8FBC8F', '00CED1', '00BFFF', 'B22222', 'FF00FF', 'FFD700', '008000', 'FF69B4', 'ADD8E6',
		'FFB6C1', '87CEFA', '66CDAA', '00FA9A', '191970', 'FFA500', '5F9EA0', 'FF7F50', 'DC143C', '008B8B', '006400',
		'556B2F', '483D8B', '696969', 'F08080', '90EE90', 'FFA07A', '778899', '48D1CC', 'FF4500', '4169E1', 'A0522D',
		'6A5ACD', '008080', '40E0D0', '9ACD32', 'A52A2A', '6495ED', 'FF8C00', 'E9967A', '2F4F4F', 'FF1493', '4B0082',
		'BA55D3', '4682B4', '708090', ];

	$code=abs(crc32($name)) % count($colours);

	return'<div class="icon-letter" style="background-color:#'.$colours[$code].'">'.mb_substr(strip_tags($name),0,1)
		.'</div>';
}

$name=basename(__DIR__);
T::$lang=Eleanor::$Language->Load(__DIR__.'/translation/*.php',false);

T::$T=new Template(__DIR__.'/Files','Admin');
T::$T->classes=__DIR__.'/Classes/';
T::$T->default=[
	'config'=>include __DIR__.'/config.php',
	'css'=>Template::$http['templates'].$name.'/css/',#HTTP путь к css шаблона
	'ico'=>Template::$http['templates'].$name.'/ico/',#HTTP путь к ico шаблона
	'js'=>Template::$http['templates'].$name.'/js/',#HTTP путь к js шаблона
	'images'=>Template::$http['templates'].$name.'/images/',#HTTP путь к картинкам шаблона
];
T::$T->queue[]=T::$T->classes.'Index.php';

return T::$T;
