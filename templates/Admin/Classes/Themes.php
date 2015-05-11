<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html;

/** Шаблоны менеджера шаблонов */
class Themes
{
	/** @var array Языковые параметры */
	public static $lang;

	/** Меню модуля
	 * @param string $act Идентификатор активного пункта меню
	 * @return string */
	protected static function Menu($act='')
	{
		$links=$GLOBALS['Eleanor']->module['links'];
		T::$data['navigation']=[
			[$links['list'],Eleanor::$Language['themes']['list'],'act'=>$act=='list'],
		];

		if($links['info'] and $links['files'] and $links['config'])
			array_push(T::$data['navigation'],
				[$links['info'],static::$lang['info'],'act'=>$act=='info'],
				[$links['config'],static::$lang['config'],'act'=>$act=='config'],
				[$links['files'],static::$lang['file-working'],'act'=>$act=='files']
			);
	}

	/** Основная страница менеджера шаблонов. Вывод всех шаблонов, которые существуют в системе */
	public static function TemplatesList()
	{
		static::Menu('list');

		return<<<HTML
Серёж, именно здесь ты можешь оформлять шаблон менеджера шаблонов :)
HTML;
	}

	/*
		Страница принятия лицензионного соглашения шаблона. Выводится в случае установки темы.
		$t - название шаблона
		$back - URI возврата
		$lic - лицензионное соглашение
	*/
	public static function License($t,$back,$lic)
	{
		static::Menu('info');
		return'<div class="wbpad"><div class="warning">
<img src="'.Eleanor::$Template->default['theme'].'images/warning.png" class="info" alt="" title="'.$t.'" />
<div>
	<h4>'.$t.'</h4><hr /><div class="wbpad" style="max-height:300px;margin:10px 0 10px 0;">'.$lic.'</div><hr />
	<form method="post">'.($back ? '' : Eleanor::Input('back',$back,array('type'=>'hidden'))).'<div style="text-align:center;margin-top:10px">
	<input class="button" name="submit" type="submit" value="'.static::$lang['submitlic'].'" />
	<input class="button" name="refuse" type="submit" value="'.static::$lang['refuselic'].'" />
	<input class="button" type="button" value="'.static::$lang['cancel'].'" onclick="history.go(-1); return false;" />
	</div>
	</form>
</div>
<div class="clr"></div>
</div></div>';
	}

	/*
		Страница с информацией о шаблоне оформления
		$name - имя шаблона
		$info - информация о шаблоне
		$license - лицензия шаблона
	*/
	public static function Info($name,$info,$license)
	{
		static::Menu('info');
		return ($info
			? Eleanor::$Template->Title($name)
				->OpenTable().'<div class="wbpad" style="max-height:300px">'.$info.'</div>'.Eleanor::$Template->CloseTable()
			: '')
			.($license
			? Eleanor::$Template->Title(Eleanor::$Language['te']['agreement'])
				->OpenTable().'<div class="wbpad" style="max-height:300px">'.$license.'</div>'.Eleanor::$Template->CloseTable()
			: '');
	}

	/*
		Страница управления файлами шаблона
		$files - интерфейс аплоадера файлов
		$name - название шаблона
	*/
	public static function Files($files,$name)
	{
		static::Menu('files');
		return Eleanor::$Template->Cover($files).'<script>//<![CDATA[
$(function(){
	$("#showb-tpl").hide().click();
	FItpl.Open=function(url)
	{
		url=encodeURIComponent(FItpl.Get("realpath").replace("templates/","")+"/"+url).replace(/!/g,"%21").replace(/\'/g,"%27").replace(/\(/g,"%28").replace(/\)/g,"%29").replace(/\*/g,"%2A").replace(/%20/g,"+")
		window.open(window.location.protocol+"//"+window.location.hostname+CORE.site_path+"'.Eleanor::$services['download']['file'].'?direct='.Eleanor::$service.'&file='.$GLOBALS['Eleanor']->module['name'].'&f="+url);
		return false;
	}
})//]]></script>';
	}

	/*
		Шаблон страницы с редактированием конфигураций шаблона
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML-код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
	*/
	public static function Config($controls,$values,$errors)
	{
		static::Menu('config');
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if($v)
				if(is_array($v) and !empty($values[$k]))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
				elseif(is_string($v))
					$Lst->head($v);

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst->button(Eleanor::Button(T::$lang['save']))->end()->endform(),$errors,'error');
	}

	/*
		Страница удаления шаблона
		$t - текст-подтверждение удаления
		$back - URL возврата
	*/
	public static function Delete($t,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm($t,$back));
	}
}
Themes::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/themes-*.php',false);

return Themes::class;