<?php
return[
	#For admin/index.php
	'list'=>'Menus',
	'parent'=>'Parent',
	'text'=>'Text links',
	'text_'=>'HTML enabled!',
	'url'=>'Link address',
	'params'=>'Extra options links',
	'params_'=>'For example: onclick="alert()"',
	'pos'=>'Position',
	'pos_'=>'Leave blank to append',
	'in_map'=>'Show in main site map',
	'activate'=>'Activate',
	'adding'=>'Adding menu item',
	'editing'=>'Editing menu item',
	'EMPTY_LINK'=>function($l){return'Neither the text of a link or address not specified'.($l ? ' (for '.$l.')' : '');},
];