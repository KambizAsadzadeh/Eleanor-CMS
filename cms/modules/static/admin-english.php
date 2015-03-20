<?php
namespace CMS;
return[
	#For admin/index.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Title not given'.($l ? ' (for '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Content not given'.($l ? ' (for '.join(', ',$l).')' : '');
	},

	'list'=>'List of static pages',
	'fp'=>'File pages',
	'creating'=>'Static page creation',
	'editing'=>'Static page editing',
	'deleting'=>'Confirm the deletion',
];