<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Html, Eleanor\Classes\Output;

define('CMS\STARTED',microtime(true));
require __DIR__.'/core/core.php';

$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;
$errors=[];

\Eleanor\StartSession(isset($_REQUEST['s']) ? (string)$_REQUEST['s'] : '','INSTALLSESSION');

if(!isset($_SESSION['agreed'],$_SESSION['agreed2'],$_SESSION['language']))
	return GoAway('?s='.session_id());

$config=Init();
SetLanguage($_SESSION['language']);

$lang=Eleanor::$Language['main'];
$errors=CheckEnv();

$percent=0;
$content=$navi='';

if($errors)
{
	$navi=$title=$lang['error'];

	foreach($errors as $v)
		$content.=Eleanor::$Template->Message($v);

	goto Output;
}

$step=isset($_GET['step']) ? (int)$_GET['step'] : 1;

switch($step)
{
	case 5:
		$navi=$lang['finish'];
		$percent=100;

		try
		{
			InitDb($_SESSION);
		}
		catch(EE$E)
		{
			$content=Eleanor::$Template->Message($E->getMessage());
			break;
		}

		LoadOptions('mailer',false);

		$sitedir=str_replace('install/','',\Eleanor\SITEDIR);
		$conf=file_get_contents(__DIR__.'/core/config.php');
		$langs=$_SESSION['languages']+['main'=>Language::$main];

		#Удаление лишних языков из конфига
		foreach(Eleanor::$langs as $k=>$v)
			if(!in_array($k,$langs))
				$conf=preg_replace("#\t*'{$k}'[^\n]*\n#",'',$conf);

		$from=[
			'{db}',
			'{db-host}',
			'{db-user}',
			'{db-pass}',
			'{prefix}',
			'{language}',
		];
		$to=[
			$_SESSION['db'],
			$_SESSION['db-host'],
			$_SESSION['db-user'],
			$_SESSION['db-pass'],
			$_SESSION['prefix'],
			Language::$main
		];

		file_put_contents(__DIR__.'/install.lock',1);
		file_put_contents(DIR.'config.php',str_replace($from,$to,$conf));
		file_put_contents(DIR.'../robots.txt',
			str_replace(['{protocol}','{domain}','{sitedir}'],
				[\Eleanor\DOMAIN,\Eleanor\PROTOCOL,$sitedir],
				file_get_contents(__DIR__.'/core/robots.txt'))
		);
		file_put_contents(DIR.'../sitemap.xml',
			str_replace(['{protocol}','{domain}','{sitedir}','{static}'],
				[\Eleanor\DOMAIN,\Eleanor\PROTOCOL,$sitedir,$config['static']],
				file_get_contents(__DIR__.'/core/robots.txt')
			)
		);
		file_put_contents(DIR.'../.htaccess',
			str_replace(
				['{full}','{full_}','{shost}'],
				[\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.($sitedir=='/' ? '' : $sitedir),\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.$sitedir,preg_quote(\Eleanor\PUNYCODE)],
				file_get_contents(__DIR__.'/core/.htaccess')
			)
		);

		$url1=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.$sitedir;
		$url2=\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.$sitedir.'admin.php';
		$links=sprintf($lang['links%'],$url1,$url2);
		$title=$lang['install_finished'];
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<div class="information" style="text-align:center">
				<h4 style="color: green;">{$title}</h4>
			</div>
			<div class="information">{$lang['inst_fin_text']}</div>
			<div class="submitline">{$links}</div>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;

		try
		{
			#Внимание! Отправка e-mail-а осуществляется в информативных целях и НЕ содержит никакой конфиденциальной информации
			\Eleanor\Classes\Email::Simple(
				'newsite@eleanor-cms.ru',
				'Новый сайт: '.$_SESSION['site'],
				'URL: http://'.\Eleanor\DOMAIN.$sitedir
			);
		}
		catch(EE$E){}
	break;
	case 4:
		$navi=$title=$lang['create_admin'];
		$percent=90;

		try
		{
			InitDb($_SESSION);
		}
		catch(EE$E)
		{
			$content=Eleanor::$Template->Message($E->getMessage());
			break;
		}

		$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
		$email=isset($_POST['email']) ? (string)$_POST['email'] : $_SESSION['email'];
		$pass=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
		$pass2=isset($_POST['pass2']) ? (string)$_POST['pass2'] : '';

		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			if($pass!==$pass2)
				$errors[]=$lang['PASS_MISMATCH'];

			if(!filter_var($email,FILTER_VALIDATE_EMAIL))
				$errors[]=$lang['err_email'];

			if(!$errors)
			{

				LoadOptions('blocker');
				try
				{
					UserManager::Add([
						'name'=>$name,
						'_password'=>$pass,
						'email'=>$email,
						'groups'=>1,
						'avatar_location'=>'av-1.png',
						'avatar_type'=>'upload',
					]);
				}
				catch(EE$E)
				{
					$mess=$E->getMessage();

					switch($mess)
					{
						case'PASS_TOO_SHORT':
							$errors[]=$lang['PASS_TOO_SHORT']($E->extra['min'],$E->extra['you']);
						break;
						default:
							$errors[]=isset($lang[$mess]) ? $lang[$mess] : $mess;
					}
				}
			}

			if(!$errors)
			{
				header('Location: install.php?step=5&s='.session_id());
				die;
			}
		}

		#Template:
		$errors=$errors ? Eleanor::$Template->Message($errors) : '';
		$ti=0;
		$form=[
			'sess'=>Html::Input('s',session_id(),['type'=>'hidden']),
			'name'=>Html::Input('name',$name,['class'=>'f_text','id'=>'name','tabindex'=>++$ti]),
			'pass'=>Html::Input('pass',$pass,['type'=>'password','class'=>'f_text','id'=>'pass','tabindex'=>++$ti]),
			'pass2'=>Html::Input('pass2',$pass2,['type'=>'password','class'=>'f_text','id'=>'pass2','tabindex'=>++$ti]),
			'email'=>Html::Input('email',$email,['class'=>'f_text','tabindex'=>++$ti]),
			'submit'=>Html::Button($lang['do_create_admin'],'submit',['class'=>'button','tabindex'=>++$ti]),
		];
		$script=[
			'name'=>addcslashes($lang['ENTER_NAME'],"\n\r\t\"\\"),
			'pass'=>addcslashes($lang['PASS_MISMATCH'],"\n\r\t\"\\"),
		];
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			{$errors}
			<form method="post" action="install.php?step=4">
				<ul class="reset formfield">
					<li class="ffield">
						<span class="label"><b>{$lang['a_name']}</b></span>
						<div class="ffdd">{$form['name']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['db_pass']}</b></span>
						<div class="ffdd">{$form['pass']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['a_rpass']}</b></span>
						<div class="ffdd">{$form['pass2']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['a_email']}</b></span>
						<div class="ffdd">{$form['email']}</div>
					</li>
				</ul>
				<div class="submitline">{$form['submit']}{$form['sess']}</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
<script>//<![CDATA[
$(function(){
	$("form").submit(function(e){
		if($('#name').val()=="")
		{
			alert('{$script['name']}');
			e.preventDefault();
		}
		else if($('#pass').val()!=$('#pass2').val())
		{
			alert('{$script['pass']}');
			e.preventDefault();
		}
	});
})//]]></script>
HTML;
	break;
	case 3:
		if($_SERVER['REQUEST_METHOD']=='POST')
			unset($_SESSION['tables']);

		$title=$lang['install'];
		$navi=$lang['installing'];
		$percent=70;
		$db_list=[];

		try
		{
			InitDb($_SESSION);
		}
		catch(EE$E)
		{
			#Template:
			$content=Eleanor::$Template->Message($E->getMessage());
			break;
		}

		if(isset($_SESSION['tables']))
		{#Создание записей в таблицы
			$percent=80;

			if(!isset($_SESSION['values']))
			{
				$insert=require __DIR__.'/data_install/insert.php';

				foreach($insert as $k=>$v)
				{
					$dbe=false;
					try
					{
						Eleanor::$Db->Query($v);
					}
					catch(EE$E)
					{
						$dbe=$E->getMessage();
					}

					if(!is_int($k))
						$db_list[$k]=$dbe;
				}

				$_SESSION['values']=true;
			}

			$url='install.php?step=4&amp;s='.session_id();
		}
		else
		{#Создание таблиц
			$tables=require __DIR__.'/data_install/tables.php';

			foreach($tables as $k=>$v)
			{
				$dbe=false;

				try
				{
					Eleanor::$Db->Query($v);
				}
				catch(EE$E)
				{
					$dbe=$E->getMessage();
				}

				if(!is_int($k))
					$db_list[$k]=$dbe;
			}

			$_SESSION['tables']=true;
			unset($_SESSION['values']);

			$url='install.php?step=3&amp;s='.session_id().'&amp;rand='.time();
		}

		#Template:
		foreach($db_list as $k=>&$v)
		{
			$color=$v ? 'red' : 'green';
			$v=$v ? strip_tags($v) : 'OK';
			$v=<<<HTML
<span style="color:{$color}" title="{$v}">{$k}</span>
HTML;
		}
		unset($v);

		$db_list=$db_list ? join(', ',$db_list) : $lang['skip'];
		$h4=isset($_SESSION['values']) ? $lang['inserting_v'] : $lang['creating_tables'];
		$content=Eleanor::$Template->RedirectScreen($url,5).<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<div class="information"><h4>{$h4}</h4>{$db_list}</div>
			<div class="submitline"><a href="{$url}">{$lang['press_here']}</a></div>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;
	break;
	case 2:
		$title=$navi=$lang['already_to_install'];
		$percent=60;

		if(!isset($_POST['host'],$_POST['name'],$_POST['user'],$_POST['pass'],$_POST['prefix'],$_POST['site'],
			$_POST['email'],$_POST['timezone']))
			goto Start;

		$data=[
			'db-host'=>(string)$_POST['host'],
			'db-user'=>(string)$_POST['user'],
			'db-pass'=>(string)$_POST['pass'],
			'prefix'=>(string)$_POST['prefix'],
			'db'=>(string)$_POST['name'],
			'site'=>(string)$_POST['site'],
			'email'=>(string)$_POST['email'],
			'timezone'=>(string)$_POST['timezone'],
			'languages'=>isset($_POST['languages']) ? (array)$_POST['languages'] : [],

			#Этап установки
			'step'=>1,

			#Дополнительные переменные конфига
			'db-charset'=>$config['db-charset'],
		];

		if(!$data['site'])
			$errors[]=$lang['empty_site'];

		if(!filter_var($data['email'],FILTER_VALIDATE_EMAIL))
			$errors[]=$lang['err_email'];

		try
		{
			InitDb($data);

			if(!CheckMySQLVersion())
				$errors[]=$lang['low_mysql'];
		}
		catch(EE$E)
		{
			$errors[]=$E->getMessage();
		}

		if($errors)
			goto Start;

		$_SESSION=$data+$_SESSION;

		#Template:
		$langs=[];
		foreach($data['languages'] as $k=>$v)
			if(isset(Eleanor::$langs[$v]))
				$langs[]=Eleanor::$langs[$v]['name'];

		$data['languages']=$langs ? implode(', ',$langs) : $lang['no'];

		foreach($data as &$v)
			if(is_string($v))
				$v=htmlspecialchars($v,ENT,\Eleanor\CHARSET);
		unset($v);

		$form=[
			's'=>Html::Input('s',session_id(),['type'=>'hidden']),
			'back'=>Html::Button($lang['back'],'button',['class'=>'button','onclick'=>'history.go(-1)','tabindex'=>2],2),
			'submit'=>Html::Button($lang['install_me'],'submit',['class'=>'button','tabindex'=>1],2),
		];
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			<form method="post" action="install.php?step=3">
				<h3 class="subhead">{$lang['db']}</h3>
				<ul class="reset formfield">
					<li class="ffield">
						<span class="label">{$lang['db_host']}</span>
						<div class="ffdd"><h4>{$data['db-host']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['db_name']}</span>
						<div class="ffdd"><h4>{$data['db']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['db_user']}</span>
						<div class="ffdd"><h4>{$data['db-user']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['db_pass']}</span>
						<div class="ffdd"><h4>{$data['db-pass']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['db_pref']}</span>
						<div class="ffdd"><h4>{$data['prefix']}</h4></div>
					</li>
				</ul>
				<br />
				<h3 class="subhead">{$lang['gen_data']}</h3>
				<ul class="reset formfield">
					<li class="ffield">
						<span class="label">{$lang['site-name']}</span>
						<div class="ffdd"><h4>{$data['site']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['email']}</span>
						<div class="ffdd"><h4>{$data['email']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['addl']}</span>
						<div class="ffdd"><h4>{$data['languages']}</h4></div>
					</li>
					<li class="ffield">
						<span class="label">{$lang['timezone']}</span>
						<div class="ffdd"><h4>{$data['timezone']}</h4></div>
					</li>
				</ul>
				<div class="submitline">{$form['s']}{$form['back']}{$form['submit']}</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;
	break;
	default:
		Start:
		$navi=$title=$lang['get_data'];
		$percent=50;

		if($_SERVER['REQUEST_METHOD']=='POST')
		{
			$host=isset($_POST['host']) ? (string)$_POST['host'] : 'localhost';
			$name=isset($_POST['name']) ? (string)$_POST['name'] : '';
			$user=isset($_POST['user']) ? (string)$_POST['user'] : '';
			$pass=isset($_POST['pass']) ? (string)$_POST['pass'] : '';
			$prefix=isset($_POST['prefix']) ? (string)$_POST['prefix'] : 'el_';
			$site=isset($_POST['site']) ? (string)$_POST['site'] : '';
			$langs=isset($_POST['languages']) ? (array)$_POST['languages'] : [];
			$email=isset($_POST['email']) ? (string)$_POST['email'] : '';
		}
		else
		{
			$host=isset($_SESSION['db-host']) ? (string)$_SESSION['host'] : 'localhost';
			$name=isset($_SESSION['db-name']) ? (string)$_SESSION['db-name'] : '';
			$user=isset($_SESSION['db-user']) ? (string)$_SESSION['db-user'] : '';
			$pass=isset($_SESSION['db-pass']) ? (string)$_SESSION['db-pass'] : '';
			$prefix=isset($_SESSION['prefix']) ? (string)$_SESSION['prefix'] : 'el_';
			$site=isset($_SESSION['site']) ? (string)$_SESSION['site'] : '';
			$langs=isset($_SESSION['languages']) ? (array)$_SESSION['languages'] : [];
			$email=isset($_SESSION['email']) ? (string)$_SESSION['email'] : '';
		}

		if(isset($_SESSION['tzo'],$_SESSION['dst']))
		{
			$tzo=[];
			$tal=timezone_abbreviations_list();

			foreach($tal as $tv)
				foreach($tv as $v)
					if($v['offset']/60==-$_SESSION['tzo'] and $v['dst']==$_SESSION['dst'])
						$tzo[]=$v['timezone_id'];

			#Сюда можно добавить пояса по-умолчанию
			if(in_array('Europe/Moscow',$tzo))
				$tzo='Europe/Moscow';
			else
				$tzo=reset($tzo);
		}
		else
			$tzo=date_default_timezone_get();

		$timezone=isset($_POST['timezone']) ? (string)$_POST['timezone'] : $tzo;

		#Template:
		$languages='';

		foreach(Eleanor::$langs as $k=>$v)
			if($k!=Language::$main)
				$languages.=Html::Option($v['name'],$k,in_array($k,$langs));

		$errors=$errors ? Eleanor::$Template->Message($errors,null) : '';
		$ti=0;
		$form=[
			'host'=>Html::Input('host',$host,['class'=>'f_text','tabindex'=>++$ti]),
			'name'=>Html::Input('name',$name,['class'=>'f_text','tabindex'=>++$ti]),
			'user'=>Html::Input('user',$user,['class'=>'f_text','tabindex'=>++$ti]),
			'pass'=>Html::Input('pass',$pass,['class'=>'f_text','tabindex'=>++$ti]),
			'prefix'=>Html::Input('prefix',$prefix,['class'=>'f_text','tabindex'=>++$ti]),
			'site'=>Html::Input('site',$site,['class'=>'f_text','tabindex'=>++$ti]),
			'email'=>Html::Input('email',$email,['class'=>'f_text','tabindex'=>++$ti,'type'=>'email']),
			'timezone'=>Html::Select('timezone',\Eleanor\Classes\Types::TimeZonesOptions($timezone),['class'=>'f_text','tabindex'=>++$ti]),
			'languages'=>Html::Items('languages',$languages,['class'=>'f_text','tabindex'=>++$ti,'size'=>2]),
			's'=>Html::Input('s',session_id(),['type'=>'hidden']),
			'next'=>Html::Button($lang['next'],'submit',['class'=>'button','tabindex'=>++$ti],2),
		];
		$content=<<<HTML
<div class="wpbox wpbwhite">
	<div class="wptop"><b>&nbsp;</b></div>
	<div class="wpmid">
		<div class="wpcont">
			{$errors}
			<form method="post" action="install.php?step=2">
				<h3 class="subhead">{$lang['db']}</h3>
				<ul class="reset formfield">
					<li class="ffield">
						<span class="label"><b>{$lang['db_host']}</b></span>
						<div class="ffdd">{$form['host']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['db_name']}</b></span>
						<div class="ffdd">{$form['name']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['db_user']}</b></span>
						<div class="ffdd">{$form['user']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['db_pass']}</b></span>
						<div class="ffdd">{$form['pass']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['db_pref']}</b></span>
						<div class="ffdd">{$form['prefix']}<span class="small" style="color:red">{$lang['db_prefinfo']}</span></div>
					</li>
				</ul>
				<br />
				<h3 class="subhead">{$lang['gen_data']}</h3>
				<ul class="reset formfield">
					<li class="ffield">
						<span class="label"><b>{$lang['site-name']}</b></span>
						<div class="ffdd">{$form['site']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['email']}</b></span>
						<div class="ffdd">{$form['email']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['timezone']}</b></span>
						<div class="ffdd">{$form['timezone']}</div>
					</li>
					<li class="ffield">
						<span class="label"><b>{$lang['addl']}</b><br /><span class="small">{$lang['addl_']}</span></span>
						<div class="ffdd">{$form['languages']}</div>
					</li>
				</ul>
				<div class="submitline">{$form['s']}{$form['next']}</div>
			</form>
		</div>
	</div>
	<div class="wpbtm"><b>&nbsp;</b></div>
</div>
HTML;
}

Output:

$out=(string)Eleanor::$Template->index(compact('content','navi','percent','errors','percent'));

Output::SendHeaders('html');
Output::Gzip($out);