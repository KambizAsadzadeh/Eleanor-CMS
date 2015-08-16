<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\OutPut, \Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

#4 глобальные переменные
global$title,$head,$scripts;
$title=$head=$scripts=[];

/** Перенаправление на другую страницу
 * @param bool|string $where true - на префикс модуля, false - на предыдущую страницу, string - на адрес
 * @param int $code Код редиректа 301 или 302
 * @param string $hash Хеш URL
 * @return mixed */
function GoAway($where=false,$code=301,$hash='')
{
	$referrer=getenv('HTTP_REFERER');

	if(!$referrer or $referrer==Url::$current or $where)
	{
		if(is_bool($where))
		{global$Eleanor;
			if($where and isset($Eleanor->DynUrl) and Eleanor::$service=='admin')
				$where=\Eleanor\SITEDIR.(string)$Eleanor->DynUrl;
			elseif($where and isset($Eleanor->Url))
				$where=\Eleanor\SITEDIR.$Eleanor->Url->prefix;
			else
				$where=\Eleanor\SITEDIR;
		}
		else
		{
			$d=parse_url($where);

			if(isset($d['host'],$d['scheme']))
			{
				if(preg_match('#^[a-z0-9\-\.]+$#',$d['host'])==0)
					$where=preg_replace('#^'.$d['scheme'].'://'.preg_quote($d['host']).'#',
						$d['scheme'].'://'.\Eleanor\Classes\Punycode::Domain($d['host']),$where);
			}
			elseif(strpos($where,'/')!==0)
				$where=\Eleanor\SITEDIR.$where;
		}

		if($where==Url::$current and $_SERVER['REQUEST_METHOD']=='GET')
			return ExitPage(404);

		$referrer=$where;
	}

	if($hash)
		$referrer=preg_replace('%#.*$%','',$referrer).'#'.ltrim($hash,'#');

	header('Cache-Control: no-store');
	header('Location: '.rtrim(html_entity_decode($referrer),'&?'),true,$code);
	die;
}

/** Формирование ссылки на учётную запись пользователя
 * @param int|null $id ID пользователя
 * @param string $name Имя пользователя
 * @param string $service Название сервиса
 * @return string */
function UserLink($id=0,$name='',$service='index')
{
	$id=(int)$id;
	$admin=$service=='admin';

	if($id<1 and !$name || $admin)
		return'';

	if($admin)
		return Eleanor::$services['admin']['file'].'?section=management&amp;module=users&amp;edit='.$id;

	if(!$admin and $id>0 and !$name)
	{
		$table=USERS_TABLE;
		$R=Eleanor::$UsersDb->Query("SELECT `name` FROM `{$table}` WHERE `id`={$id} LIMIT 1");

		if($R->num_rows==0)
			return'';

		list($name)=$R->fetch_row();
	}

	static $ma;

	if(!isset($ma))
	{
		$ma=array_keys(GetModules()['uri2section'],'user');

		if(!$ma)
			return'';

		$ma=reset($ma);
	}

	$Url=new Url(false);
	$Url->prefix=Url::$base;

	return $name && !filter_var($name,FILTER_VALIDATE_EMAIL) ? $Url([$ma,$name]) : $Url([$ma],'',['id'=>$id]);
}

/** В зависимости от настроек системы, возвращается HTML код капчи
 * @param bool $forced Обязательное отображение капчи вне настроек пользователя
 * @param string|bool $type Тип капчи (ReCaptcha, KeyCaptcha, Eleanor)
 * @return \Eleanor\Interfaces\Captcha | \Eleanor\Interfaces\Captcha_Image | null */
function Captcha($forced=false,$type='Eleanor')
{
	if(!$forced and Eleanor::$Permissions->HideCaptcha())
		return null;

	switch($type)
	{
		case'ReCaptcha':
			#ToDo! Вставить переменные ReCaptcha
			$C=new\Eleanor\Classes\ReCaptcha;
		break;
		case'KeyCaptcha':
			#ToDo! Вставить переменные KeyCaptcha
			$C=new\Eleanor\Classes\KeyCaptcha;
		break;
		default:
			$C=new \Eleanor\Classes\Captcha(Eleanor::$services['download']['file'].'?captcha&amp;',Eleanor::$Template);
	}

	return$C;
}

/** Формирование значения аватара (миниатюры) для шаблона
 * @param array $user Входящие данные
 * @return array*/
function Avatar(array$user)
{
	$image=false;

	if($user['avatar'] and $user['avatar_type']) switch($user['avatar_type'])
	{
		case'gallery':
			if(is_file($f=Template::$path['static'].'images/avatars/'.$user['avatar']))
				$image=[
					'type'=>'gallery',
					'path'=>$f,
					'http'=>Template::$http['static'].'images/avatars/'.$user['avatar'],
					'src'=>$user['avatar'],
				];
		break;
		case'upload':
			if(is_file($f=Template::$path['uploads'].'avatars/'.$user['avatar']))
				$image=[
					'type'=>'upload',
					'path'=>$f,
					'http'=>Template::$http['uploads'].'avatars/'.$user['avatar'],
					'src'=>$user['avatar'],
				];
		break;
		case'link':
			$image=[
				'type'=>'link',
				'http'=>$user['avatar'],
			];
	}

	return$image;
}

if(AJAX or ANGULAR)
{
	Eleanor::$bsodtype='json';

	/** Вывод результата для AJAX
	 * @param mixed $data Данные для вывода
	 * @param bool $withhead Включить в ответ содержимое переменных $scripts и $head */
	function Response($data,$withhead=true)
	{global$scripts,$head;
		if($withhead)
		{
			$scripts=array_unique($scripts);

			foreach($scripts as &$v)
				$v=addcslashes($v,"\n\r\t\"\\");

			$out=[
				'data'=>$data,
				'!scripts'=>$scripts ? '["'.join('","',$scripts).'"]' : '[]',
				'head'=>$head,
			];
		}
		else
			$out=$data;

		OutPut::SendHeaders('application/json');

		if(ANGULAR)
			echo json_encode($out,JSON^JSON_PRETTY_PRINT);
		else
			Output::Gzip(Html::JSON($out));
	}

	/** Вывод ошибка AJAX
	 * @param string $e Ошибка
	 * @param int $code Код ответа*/
	function Error($e='',$code=200)
	{
		$out=Html::JSON([ 'error'=>$e ]);

		OutPut::SendHeaders('application/json',$code);
		Output::Gzip($out);
	}
}
else
{
	/** Простой вывод содержимого в стандартном оформлении
	 * @param string $content HTML результат работы модуля
	 * @param array $cache параметры кэша
	 * @param int $code HTTP код */
	function Response($content,array$cache=[],$code=200)
	{
		$out=isset($_GET['iframe'])
			? (string)Eleanor::$Template->iframe([ 'content'=>$content ])
			: (string)Eleanor::$Template->index([ 'content'=>$content ]);

		#Мегафикс #1: поисковики не понимают тег <base href...>, и всегда лишний раз переходят по ссылке без его учета
		$out=preg_replace_callback('%(href|src)=(["\'])([^\'"#/][^\'"]+)%i',function($match){
			#Фильтр на полноценные ссылки и всякие mailto
			if(preg_match('#^[a-z]+:#',$match[3])==0)
				return$match[1].'='.$match[2].\Eleanor\SITEDIR.$match[3];

			return$match[0];
		},$out);

		#Мегафикс #2: перенос JavaScript из верхних частей страницы в нижние по материалам https://developers.google.com/speed/pagespeed/insights/
		if(strpos($out,'</body>')!==false and preg_match_all('#\t*<script([^>]*)>(.*?)</script>\n*#is',$out,$m)>0)
		{
			$joined=false;
			$scripts='';

			foreach($m[1] as $k=>$tag)
			{
				if(strpos($tag, 'type=')!==false)
					continue;

				if(strpos($tag, 'src=')!==false)
				{
					if(preg_match('#html5shiv\.|respond\.#',$tag)>0)
						continue;

					if($joined)
						$scripts.='</script>';

					$joined=false;
					$scripts.=trim($m[0][$k]);
				}
				elseif($m[2][$k])
				{
					$scripts.=$joined ? ";\n" : '<script>';
					$scripts.=trim($m[2][$k],"; \n\r");
					$joined=true;
				}

				$out=str_replace($m[0][$k],'',$out);
			}

			if($joined)
				$scripts.='</script>';

			$out=str_replace('</body>',$scripts.'</body>',$out);
		}

		OutPut::SendHeaders('html',$code,$cache);
		Output::Gzip($out);
	}
}