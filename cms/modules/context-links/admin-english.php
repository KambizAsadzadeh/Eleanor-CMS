<?php
return[
	#For admin/index.php
	'from'=>'Texts to replace',
	'from_'=>'You can specify multiple texts separated by commas',
	'to'=>'Link text',
	'to_'=>'HTML is allowed! If you do not fill in the link text will be the original text.',
	'reg'=>'Regular expression?',
	'reg_'=>'Regular expression must return 2 groups to replate. Replacement will occur on the pattern \1&lt;a&gt;\2&lt;/a&gt;',
	'rege'=>'Regular expression was entered with error!',
	'url'=>'Link address',
	'url_'=>'&lt;a href=',
	'params'=>'Extra options links',
	'params_'=>'For example: onclick="alert()"',
	'date_from'=>'Starting',
	'date_till'=>'Ending',
	'activate'=>'Activate',
	'list'=>'Links list',
	'adding'=>'Adding link',
	'editing'=>'Editing link',
	'EMPTY_FROM'=>function($l){
		return'Replacement text is not defined'.($l ? ' (for '.$l.')' : '');
	},
	'EMPTY_LINK'=>function($l){
		return'Neither the text of a link or address not specified'.($l ? ' (for '.$l.')' : '');
	},
];