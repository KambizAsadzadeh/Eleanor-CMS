<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

/** Предпросмотр содержимого BB редактора
 * @var string $var_0 Текст
 * @var string $css Пусть к каталогу css
 * @var string $images Путь к каталогу images */
defined('CMS\STARTED')||die;
$var_0=OwnBB::Parse($var_0);
include_once __DIR__.'/../../html.php';
$head=Templates\GetHead(false,false);
return[
	'modal'=>'<!-- Modal -->
<div class="modal fade" id="bb-preview" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body">
				<iframe style="width:100%;border:0"></iframe>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
			</div>
		</div>
	</div>
</div>',
	'html'=><<<HTML
<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap/3/css/bootstrap.min.css" type="text/css">
	<script src="//cdn.jsdelivr.net/g/jquery,bootstrap@3"></script>
	{$head}
</head>
<body>{$var_0}</body>
</html>
HTML
,
];