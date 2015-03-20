<?php
return[
	#For Classes/Comments.php
	'list'=>'Comments list',
	'news'=>function($n){
		return$n.($n==1 ? ' new comment' : ' new comments');
	},
	'deleting'=>'Are you sure you want to to delete comment &quot;%s&quot;?',
	'filter'=>'Filter',
	'module'=>'Module',
	'date'=>'Date',
	'author'=>'Author',
	'published'=>'Published in',
	'text'=>'Text',
	'cnf'=>'Comments not found',
	'cnw'=>'Comments were not written yet',
	'cpp'=>'Comments per page: %s',
	'blocked'=>'Blocked',
	'status'=>'Status',
];