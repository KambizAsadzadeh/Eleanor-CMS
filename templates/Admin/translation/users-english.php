<?php
defined('CMS\STARTED')||die;

$newpass='<span class="alert-link">{site}</span> - site name<br />
<span class="alert-link">{name}</span> - username<br />
<span class="alert-link">{fullname}</span> - full name<br />
<span class="alert-link">{userlink}</span> - link to user account<br />
<span class="alert-link">{pass}</span> - user password<br />
<span class="alert-link">{link}</span> - link to site';

return[
	#Для шаблона Classes/Users.php
	'username'=>'Username',
	'groups'=>'Groups',
	'last_visit'=>'Last visit date',
	'create'=>'Create user',
	'save'=>'Save user',
	'not_found'=>'Users were not found',
	'delete-text-span'=>'Are you sure to delete "<span id="delete-title"></span>"?',
	'delete-text%'=>'Are you sure to delete "%s"?',
	'form-errors'=>'Mistakes while filling out the form',
	'name-placeholder'=>'Specify username',
	'by-id'=>'by ID',
	'by-username'=>'by username',
	'by-full-name'=>'by full name',
	'by-group'=>'by group',
	'by-last-visit'=>'by last visit',
	'by-register'=>'by register date',
	'by-ip'=>'by ip',
	'by-offline'=>'by offline',
	'by-email'=>'by e-mail',
	'applied-by%'=>'Applied filter %s',
	'filter-by-id'=>'Input id',
	'filter-by-ip'=>'Input full ip',
	'filter-by-name'=>'Input username',
	'filter-by-email'=>'Input e-mail or part of it',
	'filter-by-user'=>'By user',
	'http-code'=>'HTTP http code',
	'save-success'=>'Successfully saved',
	'letters-save'=>'Save',
	'from'=>'from',
	'to'=>'to',
	'full-name'=>'Full name',
	'group'=>'Group',
	'register'=>'Register date',
	'vars-created'=>$newpass,
	'vars-newpass'=>$newpass,
	'vars-renamed'=>'<span class="alert-link">{site}</span> - site name<br />
<span class="alert-link">{oldname}</span> - old username<br />
<span class="alert-link">{name}</span> - new username<br />
<span class="alert-link">{fullname}</span> - full name<br />
<span class="alert-link">{userlink}</span> - link to user account<br />
<span class="alert-link">{link}</span> - link to site',
	'who'=>'Who',
	'enter'=>'Enter date',
	'location'=>'Page address',
	'guest'=>'Guest',
	'only'=>'Only',
	'include'=>'Include',
	'session_not_found'=>'Sessions were not found',
	'offline'=>'Offline',
	'name'=>'Name',
	'details'=>'Details',
	'activity'=>'Activity',
	'now_onp'=>'Now on page',
	'r'=>'Goes from',
	'browser'=>'Browser',
	'service'=>'Service',
	'c'=>'Encodings',
	'e'=>'Data types',
	'ips'=>'IP extra',
	'session_nf'=>'Session was not found',
	'go'=>'Go',
	'input_name'=>'Inout username (login)',
	'full_name'=>'Full username',
	'password'=>'Password',
	'password-leave'=>'You may not enter - system will generate it',
	'avatar'=>'Avatar',
	'main-group'=>'Main group',
	'other-groups'=>'Secondary groups',
	'letter-new-name'=>'Mail user about name changing',
	'letter-new-pass'=>'Mail user about new password',
	'letter-new-account'=>'Mail user about creating of account',
	'is-online'=>'User is online now',
	'auto'=>'Autoselect',
	'rights-on-site'=>'Permissions on site',
	'localization'=>'Localization',
	'language'=>'Language',
	'timezone'=>'Timezone',
	'server-time'=>'Server time',
	'blocking'=>'Ban',
	'banned-until'=>'Ban until',
	'ban-explain'=>'Ban reason',
	'input-ban-explain'=>'Specify reason of ban',
	'socials'=>'Social services',
	'failed-login'=>'Failed login attempts',
	'clean'=>'Clean',
	'active-sessions'=>'Active sessions',
	'replace'=>'Replace',
	'min_left'=>function($n){return $n.($n>1 ? ' minutes ago' : ' minute ago');},

	#Внешняя авторизация
	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'Yandex',
	'vk.com'=>'VK',
	'odnoklassniki.ru'=>'Odnoklassniki',
	'mail.ru'=>'Mail.ru',

	#Errors
	'SITE_ERROR'=>'Site address input incorrectly',
	'SHORT_ICQ'=>'ICQ number must contain at least 5 digits',
	'ERROR_BAN_DATE'=>'Date of ban was inputed incorrectly',
	'AVATAR_NOT_EXISTS'=>'Avatar does not exists',
	'EMPTY_NAME'=>'Username was not filled',
	'EMAIL_ERROR'=>'E-mail was inputed incorrectly',
	'NAME_EXISTS'=>'User with same name id already exists',
	'EMAIL_EXISTS'=>'User with same e-mail id already exists',
	'EMPTY_EMAIL'=>'E-mail was not inputed',
];