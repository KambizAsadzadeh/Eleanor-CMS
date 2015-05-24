<?php
namespace Eleanor\Classes\Language;
defined('CMS\STARTED')||die;

return[
	'error'=>'Error',
	'low_php'=>'For the system correct system you must have at least PHP version 5.5, you have %s.',
	'low_mysql'=>'For the system correct work you must have at least MySQL version 5.1.',
	'install.lock'=>'Set locked file install/install.lock. Delete this file and reload page.',
	'GD'=>'GD2 is required for correct working!',
	'MB'=>'Multibyte String is required for correct working!',
	'no_db_driver'=>'Database management systems are not detected!',
	'must_writeable'=>'The following folders and files should be available to record:<br />',
	'must_ex'=>'The following folders and files are not available:<br />',
	'err_email'=>'E-mail entered with an error',
	'empty_site'=>'Please, input site name.',
	'welcome'=>'Welcome! Preparing to install Eleanor CMS',
	'lang_select'=>'Selecting language',
	'install'=>'Install',
	'update'=>'Update',
	'license'=>'License agreement',
	'read_careful'=>'Read carefully',
	'get_data'=>'Data collection',
	'already_to_install'=>'Everything is prepared for installation',
	'installing'=>'Installing...',
	'create_admin'=>'Administrator account creation',
	'do_create_admin'=>'Create',
	'finish'=>'Finishing of the installation',
	'i_am_agree_lic'=>'I accept the license agreement',
	'next'=>'Next &raquo;',
	'you_must_lagree'=>'You must accept the license agreement',
	'sanctions'=>'Sanctions',
	'i_am_agree_sanc'=>'I accept the terms of the sanctions',
	'you_must_sagree'=>'You must accept the terms of the sanctions',
	'print'=>'Printable version',
	'db_host'=>'Database server',
	'db_name'=>'Database name',
	'db_user'=>'User',
	'db_pass'=>'Password',
	'db_pref'=>'Table prefix',
	'db_prefinfo'=>'Attention! Use the unique table prefix, since all the tables will be overwritten during a new installation!',
	'site-name'=>'Site name',
	'email'=>'Basic e-mail',
	'install_me'=>'Install',
	'back'=>'&laquo; Back',
	'select-lang'=>'&laquo; Select language',
	'press_here'=>'Click here if you haven\'t been moved automatically',
	'creating_tables'=>'Creating of tables...',
	'inserting_v'=>'Record of values...',
	'a_name'=>'User name',
	'a_rpass'=>'Retype password',
	'a_email'=>'E-mail',
	'PASS_MISMATCH'=>'Passwords do not match',
	'ENTER_NAME'=>'Please, input your name',
	'install_finished'=>'Setup completed successfully',
	'inst_fin_text'=>'Your copy of Eleanor CMS has been successfully installed and prepared to work! The installation script is blocked by file install/install.lock, so if you want to install the system again - you must manually delete the file. We strongly recommend you to remove the install folder and all its contents for security reasons.',
	'links%'=>'<a href="%s">Back to the main page of your site</a><br />
<a href="%s">Go to admin panel</a>',
	'requirements'=>'System requirements',
	'skip'=>'Skipping...',
	'parameter'=>'Parameter',
	'value'=>'Value',
	'status'=>'Status',
	'unknown'=>'Unknown',
	'mysqlver'=>'For system to work correctly, MySQL is required and it should be no lower than 5.1.<br />
Unfortunately, without connection, it is impossible to check MySQL version.<br />Please address to your hoster for additional information regarding this.',
	'php_version'=>'<b>PHP version:</b><br />
<span class="small">PHP version must be not lower than 5.5</span>',
	'php_gd'=>'<b>Availability of the library GD</b><br />
<span class="small">Image Processing is required for correct working</span>',
	'db_drivers'=>'<b>Database drivers</b><br />
<span class="small">Database driver is required for correct working</span>',
	'php_dom'=>'<b>DOM Functions</b><br />
<span class="small">To import and export settings Eleanor CMS, requires DOM Functions. Without them, these steps are <b>impossible</b>.</span>',
	'mod_rewrite'=>'<b>Apache mod_rewrite</b><br />
<span class="small">For working <abbr title="Friendly URL">FURL</abbr> mod_rewrite is needed.',
	'not-found'=>'Not detected',
	'sysver'=>'System version: ',
	'yes'=>'Yes',
	'no'=>'No',
	'addl'=>'Additional languages',
	'addl_'=>'Choose languages, which will be used at your site',
	'db'=>'Datebase',
	'gen_data'=>'Main site data',
	'timezone'=>'Time zone',
	'mbstring.func_overload'=>'Overloading functions mbstring is on. In php.ini set option value mbstring.func_overload to 0',

	'EMPTY_NAME'=>'Login field blank',
	'EMPTY_PASSWORD'=>'Password field blank',
	'EMAIL_ERROR'=>'E-mail address entered incorrectly',
	'NAME_EXISTS'=>'This user name already exists',
	'NAME_TOO_LONG'=>function($l,$e){
		return'User name length should not exceed '.$l.' character'.($l>1 ? 's' : '')
			.'. Your - '.$e.' character'.($e>1 ? 's' : '').'.';
	},
	'PASS_TOO_SHORT'=>function($l,$e){
		return'The password should be at least '.$l.' character'.($l>1 ? 's' : '')
			.'. Your - '.$e.' character'.($e>1 ? 's' : '').'.';
	},
];