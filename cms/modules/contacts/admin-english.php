<?php
namespace CMS;
return[
	#For admin/index.php
	'EMPTY_SUBJECT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Subject not given'.($l ? ' (for '.join(', ',$l).')' : '');
	},
	'EMPTY_TEXT'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Template of letter not given'.($l ? ' (for '.join(', ',$l).')' : '');
	},
];