<?php
namespace CMS;
return[
	#For /cms/admin/modules/groups.php
	'EMPTY_TITLE'=>function($l){
		foreach($l as &$v)
			if(isset(Eleanor::$langs[$v]))
				$v=Eleanor::$langs[$v]['name'];

		return'Group name was not entered'.($l ? ' (for '.join(', ',$l).')' : '');
	},

	'list'=>'Groups list',
	'deleting'=>'Confirm deleting',
	'creating'=>'Creating group',
	'editing'=>'Editing group',
];