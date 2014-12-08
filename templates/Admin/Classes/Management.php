<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor;

/** Шаблоны раздела "Управление"  */
class Management
{
	/** Шаблон раздела "Управление"
	 * @param array $modules Перечень системных модулей, ключи:
	 *  string title Название
	 *  string descr Описание
	 *  string image Логотип. Может содежать *, для замены на big или small (в зависимости от шаблона)
	 *  string _a Ссылка на запуск модуля
	 * @return string */
	public static function ManageCover($modules)
	{
		#SpeedBar
		T::$data['speedbar']=[ Eleanor::$Language['main']['management'] ];

		$content='		<!-- Список модулей -->
		<div class="card-list">';

		foreach($modules as $module)
		{
			$img=false;

			if($module['image'])
			{
				$module['image']='images/modules/'.str_replace('*','big',$module['image']);

				if(is_file(T::$path['static'].$module['image']))
					$img=T::$http['static'].$module['image'];
			}

			$img=$img ? '<div class="card-img"><img src="'.$img.'" alt=""></div>' : ItemAvatar($module['title']);

			$content.=<<<HTML
<!-- Карточка модуля -->
<div class="card-item card-min">
	<div class="cover">
		{$img}
		<span class="card-overlay"></span>
	</div>
	<div class="card-info">
		<h4 class="title">{$module['title']}</h4>
		<div class="text">{$module['descr']}</div>
	</div>
	<a class="card-link" href="{$module['_a']}"></a>
</div>
<!-- / Карточка модуля -->
HTML;
		}

		$content.='		</div>
		<!-- / Список модулей -->';

		return$content;
	}
}

return Management::class;