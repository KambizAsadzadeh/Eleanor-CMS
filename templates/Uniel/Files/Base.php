<?php
namespace CMS;
/** Базовый контейнер для размещения контента. Содержит заголовок, верхние элементы, основную часть и нижние элементы
 * @var string $title Название материала, можно ссылкой
 * @var string $text Основная часть материала (текст новости)
 * @var array $top Верхние элементы. Зачастую это ссылки, пример: ['<a href="...">...</a>','<a href="...">...</a>',...)
 * @var array $bottom Нижние элементы, аналогично $top. Но ключом можно уточнить содержимое:
 *  rating для рейтинга (будет отображен справа)
 *  readmore для ссылки "читать далее" (будет отображена слева и выделена полужирным начертанием) */
defined('CMS\STARTED')||die;?>
<div class="base">
	<div class="heading"><div class="binner">
		<h1><?=$title?></h1>
<?php if(isset($top))
{
	echo'<div class="moreinfo">';

	foreach($top as $v)
		if($v!==false)
			echo'<span class="arg">',$v,'</span>';

	echo'<div class="clr"></div>
	</div>';
}?>
		<div class="clr"></div>
	</div></div>
	<div class="maincont"><div class="binner"><?=$text?>
		<div class="clr"></div>
	</div></div>
<?php if(isset($bottom))
{
	echo'<div class="morelink"><div class="binner">';
	foreach($bottom as $k=>$v)
		if($v!==false)
			switch($k)
			{
				case'rating':
					echo'<div class="ratebase">',$v,'</div>';
				break;
				case'readmore':
					echo'<span class="argmore">',$v,'</span>';
				break;
				default:
					echo'<span class="arg">',$v,'</span>';
			}
	echo'<div class="clr"></div>
	</div></div>';
} ?>
</div>