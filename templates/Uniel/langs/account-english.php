<?php
return array(
	#For Classes/UserAccount.php
	'wait_new_act'=>function($h){return'You were re-sent an email with instructions on how to activate your account. Reminder: You must activate your account, either at the end of '.$h.' hour'.($h==1 ? '' : 's').', it will be removed. If you have any difficulties - please contact the administration.';},
	'please_activate'=>function($h,$l){return 'You still do not activate your account! Do it, because in '.$h.($h==1 ? ' hour' : ' hours').' hours your account will be automatically removed. <a href="'.$l.'">Resend activation e-mail.</a>';},
	'group'=>'Group',
	'descr'=>'Description',
	'who'=>'Who',
	'activity'=>'Activity',
	'pl'=>'Location',
	'snf'=>'Sessions not found',
	'guest'=>'Guest',
	'main'=>'Main',
	'settings'=>'Profile setup',
	'captcha'=>'Security code',
	'captcha_'=>'type the characters you see',
	'ENTER_CAPTCHA'=>'Please enter security code',
	'WRONG_CAPTCHA'=>'Security code is entered with error.',
	'WRONG_PASSWORD'=>'Incorrect password',
	'NOT_FOUND'=>'User not found',
	'PASSWORD_MISMATCH'=>'Password mismatch',
	'EMAIL_EXISTS'=>'Introduced e-mail already in use by another',
	'NAME_BLOCKED'=>'This nickname is locked',
	'EMAIL_ERROR'=>'Entered an invalid e-mail!',
	'EMAIL_BLOCKED'=>'Introduced e-mail blocked',
	'NAME_EXISTS'=>'This nickname is already used by another user!',
	'EMPTY_NAME'=>'Enter the user name',
	'EMPTY_EMAIL'=>'Enter e-mail',
	'reg_off'=>'Sorry, user registration is disabled.',
	'external_reg'=>'%s, ��� ����� ������ ����������� �� �����. � ���������� �� ������� ������� �� ���� �����.',
	'name'=>'Login',
	'enter_g_name'=>'Enter your desired username',
	'name_'=>'Should consist of characters A-z, 0-9 begin with a letter, end with a letter or a digit, with at least 4 characters',
	'enter_g_email'=>'Enter your e-mail',
	'email_'=>'Registration can be done only with this e-mail address.',
	'check'=>'Check',
	'pass'=>'Password',
	'pass_'=>'The password can not enter - then auto-generate. But if you decide to enter - do not recommend the use of simple passwords',
	'rpass'=>'Re-enter password',
	'do_reg'=>'Register',
	'success_reg'=>'You have successfully registered!',
	'wait_act_text'=>function($h){return'You have successfully registered. However, to complete registration, you must activate your account. Link to activate the account was sent to the entered your e-mail - you just go for it. Link is valid for '.$h.' hour'.($h==1 ? '' : 's').'.';},
	'wait_act_admin'=>'You have successfully registered. All newly created accounts are manually activated by the administrator.',
	'wait_pass1_text'=>'Information for laying sent to you by e-mail.',
	'EMPTY_FIELDS'=>'You fill nothing!',
	'ACCOUNT_NOT_FOUND'=>'Account with such data was not found',
	'notnoem'=>'If you do not remember any login name or e-mail, with what you have, what are you registered? - Sign up again.',
	'enterna'=>'Enter your nickname',
	'enterem'=>'Enter your e-mail',
	'fogotname'=>'Forgot your username?',
	'fogotemail'=>'Remembered login?',
	'ent_newp'=>'Enter new password',
	'rep_newp'=>'Repeat new password',
	'new_pass_sent'=>'A new password sent to your e-mail.',
	'pass_changed'=>'Your password was successfully changed!',
	'ractletter'=>'Resend activation e-mail',
	'activation_ok'=>'Your account has been successfully activated!',
	'activation_err'=>'The account is not activated. Maybe you\'ve broken link.',
	'EMAIL_BROKEN_LINK'=>'E-mail can not be changed. Maybe you\'ve broken link.',
	'EMAIL_YOURS'=>'You enter your current e-mail',
	'change_email'=>'Change e-mail',
	'curr_email'=>'Your current e-mail',
	'new_email'=>'The new e-mail',
	'continue'=>'Continue',
	'email_changed'=>'Your e-mail was successfully changed!',
	'wait_change1'=>'To activate the new e-mail, the old e-mail you sent confirmation. Please check your email.',
	'wait_change2'=>'For the final activation of the new e-mail, you are sent a confirmation on it. Please check your email.',
	'email_success'=>'Your e-mail was successfully changed!',
	'change_pass'=>'Change password',
	'WRONG_OLD_PASSWORD'=>'Old password is incorrect!',
	'your_curr_pass'=>'Your current password',
	'en_ycp'=>'Enter your current password',
	'new_pass_me'=>'New password',
	'optssaved'=>'Options successfully saved',
	'SITE_ERROR'=>'Site address is entered incorrectly!',
	'AVATAR_NOT_EXISTS'=>'Chosen avatar does not exist',
	'SHORT_ICQ'=>'ICQ number must contain at least 5 digits',
	'avatar'=>'Avatar',
	'alocation'=>'Placing',
	'apersonal'=>'Upload',
	'agallery'=>'From the gallery',
	'amanage'=>'Management',
	'gallery_select'=>'Choose',
	'noavatar'=>'No avatar',
	'nickname'=>'Nick',
	'registered'=>'Registered',
	'last_visit'=>'Last visit',
	'maingroup'=>'Primary group',
	'othgroups'=>'Secondary groups',
	'no_avatars'=>'No available avatars',
	'cancel_avatar'=>'Cancel',
	'togals'=>'Galleries',
	'aexternal'=>'Successfully added a bunch of %s.',
	'add'=>'Add',
	'datee'=>'Expiration date',
	'csnd'=>'You can not delete current session',
	'sessions'=>'Opened sessions',

	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'Yandex',
	'vkontakte.ru'=>'VK',

	#Ajax
	'noavatars'=>'No avatars available',
	'cancel'=>'Cancel',
	'togals'=>'Galleries',
);