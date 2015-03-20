<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Адаптер для ReCaptcha. Для того, чтобы скрипт работал, необходимо перейти на http://recaptcha.net, зарегистрировать
 * свой сайт и получить Public Key и Private Key, которые необхимдо вставить в переменные $public_key и $private_key
 * класса ReCaotcha */
class ReCaptcha extends Eleanor\BaseClass implements Eleanor\Interfaces\Captcha
{
	public static
		/** @var string Use this in the JavaScript code that is served to your users */
		$public_key='',

		/** @var int Use this when communicating between your server and our server. Be sure to keep it a secret. */
		$private_key='';

	/** Получение общего $head
	 * @param array $params Параметры загрузки
	 * @return \Eleanor\Classes\Head */
	public static function GetGeneralHead($params=[])
	{
		$params=$params ? '?'.Url::Query($params) : '';
		return new Head(['script'=>'//www.google.com/recaptcha/api.js'.$params]);
	}

	/** Получение HTML кода капчи, для вывода его на странице
	 * @return CaptchaCallback */
	public static function GetCode()
	{
		$Str=new CaptchaCallback(function(){
			$key=static::$public_key;

			return<<<HTML
<div class="g-recaptcha" data-sitekey="{$key}"></div>
HTML;
		},function($params=[]){
			$params=$params ? '?'.Url::Query($params) : '';
			return new Head(['script'=>'//www.google.com/recaptcha/api.js'.$params]);
		});
		$Str->creator=__CLASS__;

		return$Str;
	}

	/** Проверка капчи
	 * @param array|null $post Подмена $_POST массива. Полезно, в случае использования AJAX
	 * @return bool */
	public static function Check($post=null)
	{
		if(!is_array($post))
			$post=&$_POST;

		if(!isset($post['g-recaptcha-response']))
			return false;

		$recaptcha=file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='
			.static::$private_key.'&response='.$post['g-recaptcha-response'].'&remoteip='.$_SERVER['REMOTE_ADDR']);
		$recaptcha=json_decode($recaptcha,true);

		return $recaptcha && $recaptcha['success'];
	}
}