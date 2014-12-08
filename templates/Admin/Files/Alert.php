<?php
namespace CMS\Templates\Admin;
defined('CMS\STARTED')||die;

/** Элемент шаблона. Отображает информацию для привлечения внимания в характерной рамке.
 * @var array|string $var_0 Текст без HTML разметки. Допускается теги: <strong>, <a> должен быть с классом .alert-link
 * @var string success|info|warning|danger $var_1 Тип информации, по умолчанию danger
 * @var bool $var_2 Возможность "закрыть" сообщение */

$type=isset($var_1) ? $var_1 : 'danger';
$isa=is_array($var_0);
$dismiss=isset($var_2) ? (int)$var_2 : false;?>
<div class="alert alert-<?=$type,$dismiss ? ' alert-dismissible' : ''?>" role="alert">
<?=$dismiss
	? '	<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>'
	: '',
$var_0?>
</div>