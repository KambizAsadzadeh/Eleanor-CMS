<?php
namespace CMS;
return[
	#For /cms/admin/modules/ownbb.php
	'list'=>'List of BB tags',
	'deleting'=>'Confirm deleting',
	'creating'=>'Creating BB tag',
	'editing'=>'Editing BB tag',

	#For /cms/admin/modules/tasks.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Description was not entered'.($l ? ' (for '.join(', ',$l).')' : '');
	},
];