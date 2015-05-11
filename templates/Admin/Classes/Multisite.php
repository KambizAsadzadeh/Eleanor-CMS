<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/

namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

/** Шаблоны мультисайта */
class Multisite
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language['multisite'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		T::$data['navigation']=[
			[$links['main'],$lang['config'],'act'=>$act=='main'],
			[$links['options'],T::$lang['options'],'act'=>$act=='options'],
		];
	}

	/** Страница заполнения конфига мультисайта
	 * @param callback $Controls2Html Генератор html из $controls: id=>массив html контролов
	 * @param array $controls Перечень элементов формы
	 * @param array $errors Ошибки формы
	 * @param bool $saved Флаг успешного сохранения
	 * @return string */
	public static function Multisite($Controls2Html,$controls,$errors,$saved)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title']
		];

		static::Menu('main');
		$GLOBALS['scripts'][]=T::$T->default['js'].'multisite_manager.js';

		$c_lang=static::$lang;
		$t_lang=T::$lang;

		#Errors
		$er_def='';

		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error) and isset(static::$lang[$error]))
					$error=static::$lang[$error];

			$er_def.=T::$T->Alert($error,'danger',true);
		}
		#/Errors

		if(!$er_def and $saved)
			$er_def.=T::$T->Alert(T::$lang['successfully-saved'],'success',true);

		$sites=$Controls2Html();
		$parts='';
		$next_add=0;

		foreach($sites as $key=>$site)
		{
			$next_add=$key;
			$site['title']=T::$T->LangEdit($site['title'], 'title-'.$key);

			$parts.=<<<HTML
<div class="block-t expand">
	<p class="btl" data-toggle="collapse" data-target="#opts-{$key}"><span class="site-title">&nbsp;</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="#" class="delete-site small">{$t_lang['delete']}</a></p>
	<div id="opts-{$key}" class="collapse in">
		<div class="bcont">
			<div class="row">
				<div class="col-xs-6 form-group">
					<label id="label-title-{$key}" for="title-{$key}">{$controls['title']['title']}</label>
					{$site['title']}
				</div>
				<div class="col-xs-6 form-group no-bottom-padding">
					<label id="label-url-{$key}" for="url-{$key}">{$controls['url']['title']}</label>
					{$site['url']}
					<p class="text-muted">{$controls['url']['descr']}</p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 form-group">
					<label id="label-secret-{$key}" for="secret-{$key}">{$controls['secret']['title']}</label>
					{$site['secret']}
				</div>
				<div class="col-xs-6 form-group">
					<label class="control-label" id="label-prefix-{$key}" for="prefix-{$key}">{$controls['prefix']['title']}</label>
					{$site['prefix']}
				</div>
			</div>
			<hr />
			<div class="row">
				<div class="col-xs-6 form-group">
					<label class="control-label" id="label-host-{$key}" for="host-{$key}">{$controls['host']['title']}</label>
					{$site['host']}
				</div>
				<div class="col-xs-6 form-group">
					<label class="control-label" id="label-db-{$key}" for="db-{$key}">{$controls['db']['title']}</label>
					{$site['db']}
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 form-group">
					<label class="control-label" id="label-user-{$key}" for="user-{$key}">{$controls['user']['title']}</label>
					{$site['user']}
				</div>
				<div class="col-xs-6 form-group">
					<label class="control-label" id="label-pass-{$key}" for="pass-{$key}">{$controls['pass']['title']}</label>
					{$site['pass']}
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6 checkbox">
					<label title="{$controls['sync']['descr']}">{$site['sync']} {$controls['sync']['title']}</label>
				</div>
				<div class="col-xs-6 text-right">
					<button type="button" class="btn btn-default need-tabindex button-check"><b>{$c_lang['check']}</b></button>
				</div>
			</div>
		</div>
	</div>
</div>
HTML;
		}

		$next_add++;

		return<<<HTML
		{$er_def}
			<form method="post" id="create-edit">
				<div id="mainbar">
					{$parts}
				</div>
				<div id="rightbar"><div class="alert alert-info" style="margin-left: 15px">{$c_lang['info']}</div></div>
				<!-- FootLine -->
				<div class="submit-pane">
					<button type="submit" class="btn btn-success need-tabindex"><b>{$c_lang['save-config']}</b></button>
					<button type="button" class="btn btn-primary need-tabindex" id="add-site"><b>{$c_lang['add-site']}</b></button>
				</div>
				<!-- FootLine [E] -->
			</form>
			<script>$(function(){ CreateEdit({$next_add}); })</script>
HTML;
	}

	/** Обертка для интерфейса настроек
	 * @param string $options Интерфейс настроек
	 * @return string */
	public static function Options($options)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title'],
			end($GLOBALS['title'])
		];

		static::Menu('options');
		return(string)$options;
	}
}
Multisite::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/multisite-*.php',false);

return Multisite::class;