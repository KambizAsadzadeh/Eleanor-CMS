<?php
namespace CMS;
defined('CMS\STARTED')||die;

/** Элемент шаблона: вывод "шапки модуля" с названием и меню
 * @var string $title Название в шапке
 * @var array $menu Элементы меню:
 *  [string 0] Ссылка
 *  [string 1] Анкор
 *  [array extra] Дополнительные параметры тега a
 *  [array submenu] Подменю
 */

$main='';

if(isset($menu))
{
	$Menu=function(array$menu)use(&$Menu)
	{
		$c='';

		foreach($menu as $item)
			if(is_array($item) and $item)
			{
				if(!empty($item['act']) and !isset($item['extra']['class']))
					$item['extra']['class']='active';

				$submenu=empty($item['submenu']) ? '' : '<ul>'.$Menu($item['submenu']).'</ul>';
				$a=isset($item['extra']) ? \Eleanor\Classes\Html::TagParams($item['extra']) : '';

				if($item[0]===false)
					$link=<<<HTML
<span{$a}>{$item[1]}</span>
HTML;
				else
					$link=<<<HTML
<a href="{$item[0]}"{$a}>{$item[1]}</a>
HTML;

				$c.="<li>{$link}{$submenu}</li>";
			}

		return$c;
	};

	$menu=$Menu($menu);

	if($menu)
	{
		$GLOBALS['scripts'][]=Template::$http['js'].'menu_multilevel.js';
		$u=uniqid();
		$main=<<<HTML
<ul id="menu-{$u}" class="modulemenu">{$menu}</ul><script>$(function(){ $("#menu-{$u}").MultiLevelMenu(); });</script>
HTML;
	}
}
?>
<div class="base">
	<div class="heading2"><div class="binner">
		<h6><?=$title?></h6>
		<div class="clr"></div>
	</div></div>
	<nav><?=$main?></nav>
</div>