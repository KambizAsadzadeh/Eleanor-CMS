<?php
namespace Eleanor\Classes\Language;
return[
	#Для /admin.php
	'TEMPORARILY_BLOCKED'=>function($name,$minutes){
		return'У зв\'язку з частим вводом неправильного пароля, обліковий запис <b>'.$name
			.'</b> тимчасово заблоковано! Спробуйте через '.$minutes
			.Ukrainian::Plural($minutes,[' хвилину.',' хвилини.',' хвилин.']);
	},
	'enter_to'=>'Вхід в панель адміністратора',
];