<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Html;
/** Оформление стандартной системной капчи
 * @var string $src Путь src. Используя параметры w (ширина) и h (высота), можно получить картинку нужного размера
 * @var string $name Базовое имя контролов капчи
 * @var string $session Идентификатор сессии
 * @var int $length Длина текста в капче */
defined('CMS\STARTED')||die;
$lang=Eleanor::$Language->Load(__DIR__.'/../translation/captcha-*.php',false);?>
<div class="captcha">
	<label for="captcha-input"><?=$lang['enter-code']?></label>
	<img class="captcha-img" onclick="this.a;if(!this.a)this.a=this.src;this.src=this.a+'&amp;new='+Math.random()" src="<?=$src?>&amp;w=115&amp;h=57" alt="" style="cursor:pointer;" id="<?=$name?>" title="<?=$lang['click']?>">
	<?=Html::Input($name.'[t]','',['class'=>'form-control', 'id'=>'captcha-input','required'=>true,'maxlength'=>$length]),
		Html::Input($name.'[s]',$session,['type'=>'hidden'])?>
</div>