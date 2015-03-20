<?php
namespace CMS;
defined('CMS\STARTED')||die;

return[
	#Для /cms/admin/modules/users/index.php
	'list'=>'Userlist',
	'deleting'=>'Confirm the deletion',
	'creating'=>'User creation',
	'editing'=>'User editing',
	'letters'=>'Formats of emails',
	'letter4created'=>'Email about creating new account',
	'letter4renamed'=>'Email about changing user name',
	'letter4newpass'=>'Email about changing user password',
	'letter-title'=>'Subject',
	'letter-descr'=>'Text',
	'online-list'=>'Who online',

	#Для /cms/admin/modules/users/extra.php
	'personal'=>'Personal',
	'gender'=>'Gender',
	'no-gender'=>'Unknown',
	'female'=>'Female',
	'male'=>'Male',
	'bio'=>'Bio',
	'site'=>'Site',
	'site_'=>'Input address with beginnin of с http://',
	'interests'=>'Interests',
	'location'=>'Location',
	'signature'=>'Signature',
	'connect'=>'Connect',
	'vk'=>'VK',
	'vk_'=>'Please, input only your ID or nickname',
	'twitter_'=>'Please, input only nickname',
	'theme'=>'Theme',
	'by_default'=>'Default',
	'editor'=>'Editor',

	#Errors
	'NAME_TOO_LONG'=>function($l,$e){
		return"Length of username must be longer than {$l} symbols. You have entered {$e} symbols";
	},
	'PASS_TOO_SHORT'=>function($l,$e){
		return"Minimum length of password is {$l} symbols. You have entered only {$e} symbols";
	},
];