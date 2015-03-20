<?php
namespace CMS\Templates\Admin;
/** Элемент шаблона. Табличка с вопросом, ответ на который либо "да" либо "нет".
 * При нажатии на кнопку "да", происходит отправка формы с ключом ok
 * @var string $var_0 Вопрос
 * @var string $var_1 URL возврата
 * @var string $images Путь к каталогу images */

use Eleanor\Classes\Html;
defined('CMS\STARTED')||die;

$t=is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title'];
$back=isset($var_1) ? $var_1 : false;

if(isset(T::$data['speedbar'])):?>
<div role="alert" class="alert alert-danger">
	<h4><?=$var_0?></h4>
	<!-- <p>Дополнительный текст</p>-->
	<p>
		<form method="post"><button class="btn btn-danger" type="submit" name="ok">
			<?=T::$lang['delete']?></button><?=$back ? Html::Input('back',$back,['type'=>'hidden']) : ''?>
			<button class="btn btn-default" type="button" onclick="history.go(-1)"><?=T::$lang['cancel']?></button>
	</form>
	</p>
</div>
<?php else:?>
<div class="wbpad"><div class="warning">
	<img src="<?=$images?>confirm.png" class="info" alt="<?=$t?>" title="<?=$t?>" />
	<div>
		<h4><?=$t?></h4>
		<hr />
		<form method="post"><?=$var_0,$back ? Html::Input('back',$back,['type'=>'hidden']) : $back?><br />
			<input class="button" type="submit" value="<?=T::$lang['yes']?>" name="ok" />
			<input class="button" type="button" value="<?=T::$lang['no']?>" onclick="history.go(-1)" />
		</form>
	</div>
	<div class="clr"></div>
</div></div>
<?php endif?>