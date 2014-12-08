<?php
namespace CMS;
return[
	#For /cms/admin/modules/tasks.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Title was not entered'.($l ? ' (for '.join(', ',$l).')' : '');
	},

	'list'=>'Task list',
	'deleting'=>'Confirm deleting',
	'creating'=>'Creating task',
	'editing'=>'Editing task',
];