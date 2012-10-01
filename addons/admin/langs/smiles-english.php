<?php
return array(
	#For /addons/admin/modules/smiles.php
	'emotion'=>'Emotion',
	'emotion_'=>'Emotion in the text. For example: :-) :-* ;-). You can specify multiple comma-separated.',
	'path'=>'Path to smile',
	'preview'=>'Preview',
	'pos'=>'Position',
	'pos_'=>'Leave blank to append',
	'status'=>'Active',
	'show'=>'Display it the list',
	'gadd'=>'Batch addition',
	'fdne'=>'Entered directory does not exist',
	'emoexists'=>function($em){return (count($em)>1 ? 'Emotions '.join(', ',$em) : 'Emotion '.join($em)).' already exist!';},
	'smnots'=>'You have not selected any smilie to add',
	'smnf'=>'In this directory new emoticons are not detected.',
	'delc'=>'Delete confirmation',
	'list'=>'Smiles list',
	'adding'=>'Adding smile',
	'editing'=>'Editing smile',

	#For template
	'NOFILE'=>'File of smile does not exists',
	'NOEMO'=>'Not given emotion',
	'add'=>'Add smile',
	'smile'=>'Smile',
	'no_smiles'=>'Smiles was not found',
	'deleting'=>'Do you really want to remove smile %s?',
	'selcat'=>'Choose a directory',
	'smadded'=>'Smilies successfully added',
	'smpp'=>'Smiles per page: %s',
	'addsels'=>'Add selected smilies',
);