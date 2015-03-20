<?php
return[
	#For /admin.php
	'TEMPORARILY_BLOCKED'=>function($name,$minutes){
		return'<b>'.$name.'</b> has been blocked due to frequent incorrect password entering. Try again after '
			.$minutes.($minutes==1 ? ' minute.' : ' minutes');
	},
	'enter_to'=>'Login to admin panel',
];