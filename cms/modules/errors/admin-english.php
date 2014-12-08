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

	'list'=>'List of pages of errors',
	'letters'=>'Letters format',
	'creating'=>'Creation page of error',
	'editing'=>'Editing page of error',
	'deleting'=>'Confirm the deletion',
];