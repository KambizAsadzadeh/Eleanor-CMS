<?php
namespace CMS;
return[
	#For /cms/admin/modules/modules.php
	'list'=>'Modules list',
	'deleting'=>'Confirm the deletion',
	'creating'=>'Module creation',
	'editing'=>'Editing module',

	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Module name must not be empty'.($l ? ' (for '.join(', ',$l).')' : '');
	},
	'URI_EXISTS'=>function($s){
		return'Module with URI &quot;'.join('&quot;, &quot;',$s).'&quot; already exists.';
	},
];