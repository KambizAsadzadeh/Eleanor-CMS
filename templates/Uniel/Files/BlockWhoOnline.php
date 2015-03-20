<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
/** Оформление содержимого блока пользователей онлайн
 * @var array $var_0 пользователи онлайн. Формат id=>[], ключи:
 *  string p HTML-префикс группы
 *  string e HTML-окончание группы
 *  string n Имя пользователя
 *  string t Время входа
 * @var array $var_1 Поисковые боты онлайн. Формат имя бота=>[], ключи:
 *  int cnt Количество "щупалец" (сессий) у этого бота
 *  string t Время входа
 * @var int $var_2 Количество пользователей онлайн
 * @var int $var_3 Количество ботов онлайн
 * @var int $var_4 Количество гостей онлайн */
defined('CMS\STARTED')||die;
global$Eleanor;

$ltpl=Eleanor::$Language['tpl'];
$users=$bots='';
$t=time();

foreach($var_0 as $k=>&$v)
{
	$et=floor(($t-strtotime($v['t']))/60);
	$users.='<a href="'.UserLink($k,$v['n']).'" title="'.$ltpl['minutes_ago']($et).'">'.$v['p']
		.htmlspecialchars($v['n'],ENT,\Eleanor\CHARSET).$v['e'].'</a>, ';
}

foreach($var_1 as $k=>&$v)
{
	$et=floor(($t-strtotime($v['t']))/60);
	$bots.='<span title="'.$ltpl['minutes_ago']($et).'">'.$k.($v['cnt']>1 ? ' ('.$v['cnt'].')' : '').'</span>, ';
}

$mo=array_keys($Eleanor->modules['uris'],'online');
$mo=reset($mo);

echo$users ? '<h5>'.$ltpl['users']($var_2).'</h5><p>'.rtrim($users,', ').'</p>' : '',
	$bots ? '<h5>'.$ltpl['bots']($var_3).'</h5><p>'.rtrim($bots,', ').'</p>' : '',
	$var_4>0 ? '<h5>'.$ltpl['guests']($var_4).'</h5><br />' : '',
	'<a href="',Url::Encode($mo),'">',$ltpl['alls'],'</a>';
