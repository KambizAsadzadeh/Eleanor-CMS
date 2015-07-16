<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor, CMS\OwnBB, CMS\DynUrl, CMS\Template, \Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

/** Шаблон главной секции в админке (страницы, на которую мы попадаем сразу после входа) */
class General
{
	/** @var array Языковые значения */
	public static $lang=[];

	/** Меню раздела
	 * @param string $act Идентификатор активного пункта */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language['general'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=[
			[$links['main'],Eleanor::$Language['main']['main page'],'act'=>$act=='main'],
			[$links['server'],$lang['server_info'],'act'=>$act=='server'],
			[$links['logs'],$lang['logs'],'act'=>$act=='logs'],
			[$links['license'],$lang['license_'],'act'=>$act=='license'],
		];
	}

	/** Главная страница админки
	 * @param array $nums Количества разнообразных пунктов на сайте:
	 *  int comments Количество комментариев всего
	 *  int comments-week Количество комментариев за текущую неделю
	 *  int users Количество пользователей всего
	 *  int users-week Количество пользователей за текущую неделю
	 *  int life Cрок жизни сайта в днях
	 * @param string $comments Готовый шаблон последних комментариев
	 * @param array $users Перечень последних зарегистрированных пользователей на сайте, ключи:
	 *  string full_name Полное имя пользователя (безопасный HTML)
	 *  string name Логин пользователя (НЕбезопасный HTML)
	 *  string email Электронная почта пользователя
	 *  array groups Перечень групп пользователя
	 *  string ip IP адрес пользователя
	 *  string register Дата регистрации пользователя в формате Y-m-d H:i:s
	 *  string last_visit Дата последнего входа пользователя в формате Y-m-d H:i:s
	 * @param array $groups Перечень групп пользователей, ключи:
	 *  string title Название группы
	 *  string style стиль группы
	 * @param string $mynotes Содержимое заметок пользователя
	 * @param string $conotes Содержимое общих заметок
	 * @param bool $cleaned Флаг очищенности кэша
	 * @return string */
	public static function General($nums,$comments,$users,$groups,$mynotes,$conotes,$cleaned)
	{
		static::Menu('main');

		$Lst=TableList(7)
			->head(static::$lang['name'],'E-mail',static::$lang['group'],static::$lang['reg'],static::$lang['lastw'],
					'IP');

		foreach($users as$v)
		{
			$grs='';

			foreach($v['groups'] as $gv)
				if(isset($groups[$gv]))
				{
					$style=$groups[ $gv ]['style'] ? ' style="'.$groups[ $gv ]['style'].'"' : '';
					$grs.=<<<HTML
<a href="{$groups[$gv]['_aedit']}"{$style}>{$groups[$gv]['title']}</a>,
HTML;
				}

			$Lst->item(
				'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],\CMS\ENT,\Eleanor\CHARSET).'</a>'
				.($v['name']==$v['full_name'] ? '' : "<br /><i>{$v['full_name']}</i>"),
				[$v['email'],'center'],
				rtrim($grs,' ,'),
				[Eleanor::$Language->Date($v['register'],'fdt'),'center'],
				[substr($v['last_visit'],0,-3),'center'],
				[$v['ip'],'center','href'=>'http://eleanor-cms.ru/whois/'.$v['ip'],'href-extra'=>['target'=>'_blank']]
			);
		}

		$Lst->end();

		$modules=\CMS\GetModules();

		$newsurl=array_keys($modules['uri2id'],1);#Новости
		$newsurl=urlencode(reset($newsurl));

		$pageurl=array_keys($modules['uri2id'],2);#Статические страницы
		$pageurl=urlencode(reset($pageurl));

		$menuurl=array_keys($modules['uri2id'],5);#Меню
		$menuurl=urlencode(reset($menuurl));

		$c=Eleanor::$Template->OpenTable()
	.'<div class="wbpad twocol"><div class="colomn">
<ul class="reset blockbtns">
<li><a href="'.DynUrl::$base.'section=modules&amp;module='.$newsurl.'&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/news-48x48.png" alt="" /><span>'.static::$lang['crnews'].'</span></a></li>
<li><a href="'.DynUrl::$base.'section=modules&amp;module='.$pageurl.'&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/static-48x48.png" alt="" /><span>'.static::$lang['crpage'].'</span></a></li>
<li><a href="'.DynUrl::$base.'section=modules&amp;module='.$menuurl.'&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/menu-48x48.png" alt="" /><span>'.static::$lang['crmenu'].'</span></a></li>
<li><a href="'.DynUrl::$base.'section=management&amp;module=blocks&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/blocks-48x48.png" alt="" /><span>'.static::$lang['crbl'].'</span></a></li>
<li><a href="'.DynUrl::$base.'section=management&amp;module=users&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/users-48x48.png" alt="" /><span>'.static::$lang['cruser'].'</span></a></li>
<li><a href="'.DynUrl::$base.'section=management&amp;module=spam&amp;do=add"><img src="'.Template::$http['static']
			.'images/modules/spam-48x48.png" alt="" /><span>'.static::$lang['crspam'].'</span></a></li>
</ul></div>
<div class="colomn">
<div class="blockwel"><div class="pad"><h3 class="dtitle">'.static::$lang['thanks'].'</h3>'.static::$lang['thanks_']
			.'</div></div>
</div>
<div class="clr"></div>
</div>'.Eleanor::$Template->CloseTable();

		if($install=file_exists(\CMS\DIR.'../install'))
			$c.=Eleanor::$Template->Message(static::$lang['install_nd'],'warning');

		$GLOBALS['scripts'][]=Template::$http['static'].'js/tabs.js';
		$c.=Eleanor::$Template->Title(T::$lang['info'])->OpenTable()
	.'<ul id="stabs" class="reset linetabs">
	<li><a class="selected" data-rel="stab1" href="#"><b>'.static::$lang['stat'].'</b></a></li>
	<li><a data-rel="stab2" href="#"><b>'.static::$lang['comments'].'</b></a></li>
	<li><a data-rel="stab3" href="#"><b>'.static::$lang['users'].'</b></a></li>
	<li><a data-rel="stab4" href="#"><b>'.static::$lang['newselc'].'</b></a></li>
	<li><a data-rel="mynotes" href="#"><b>'.static::$lang['ownnote'].'</b></a></li>
	<li><a data-rel="conotes" href="#"><b>'.static::$lang['gennote'].'</b></a></li>
</ul>
<div id="stab1" class="tabcontent">
<table class="tabstyle">
<tr class="first tabletrline1"><td>'.static::$lang['stcomm'].'</td><td style="text-align:center"><b>'
			.$nums['comments-week'].'</b> ('.$nums['comments'].')</td></tr>
<tr class="tabletrline2"><td>'.static::$lang['stuser'].'</td><td style="text-align:center"><b>'
			.$nums['users-week'].'</b> ('.$nums['users'].')</td></tr>
<tr class="tabletrline1"><td>'.static::$lang['stsite'].'</td><td style="text-align:center"><b>'
			.$nums['life'].'</b></td></tr>
<tr class="last tabletrline2"><td>'.static::$lang['time_on_server'].'</td><td style="text-align:center">'
			.Eleanor::$Language->Date().'</td></tr>
</table>
</div>
<div id="stab2" class="tabcontent">'.$comments.'</div>
<div id="stab3" class="tabcontent">'.$Lst.'</div>
<div id="stab4" class="tabcontent"></div>
<div id="mynotes" class="tabcontent">'.static::Notes($mynotes).'</div>
<div id="conotes" class="tabcontent">'.static::Notes($conotes).'</div>
<script>//<![CDATA[
$(function(){
	$("#stabs a").Tabs({
		OnBeforeSwitch:function(a){
			if(a.data("rel")=="stab4" && !$("#stab4").html())
			{
				CORE.ShowLoading();
				$.getJSON("http://eleanor-cms.ru/updates.php?ver=1&c=?",function(d){
					$("#stab4").html(d.data);
					CORE.HideLoading();
				});
			}
			return true;
		}
	});

	$("#mynotes,#conotes").on("click",".submitline [type=button]",function(){
		var p=$(this).closest(".tabcontent").attr("id"),
			s=$(this).data("save");

		CORE.Ajax(
			{
				event:p+(s ? "" : "load"),
				text:s ? EDITOR.Get("e"+p)||"" : "",
			},
			function(r)
			{
				$("#"+p).html(r);
			}
		);
	});
	'.($install ? '
	$("#delete-install").click(function(e){
		var th=$(this);
		e.preventDefault();
		CORE.Ajax({event:"remove-install"},function(){
			th.closest(".wbpad").remove();
		});
	});' : '').'
});//]]></script>'.Eleanor::$Template->CloseTable()->Title(static::$lang['cachem']);

		if($cleaned)
			$c.=Eleanor::$Template->Message(static::$lang['cache_deleted'],'info');

		return$c.Eleanor::$Template->OpenTable().'<div class="blockcache"><div class="colomn"><div class="pad">'
			.static::$lang['cache_'].'<div class="submitline"><form method="post">'
			.Html::Input('kill_cache','1',['type'=>'hidden'])
			.Html::Button(static::$lang['cachedel'],'submit',['style'=>'button'])
			.'</form></div></div></div>	<div class="clr"></div></div>'.Eleanor::$Template->CloseTable();
	}

	/** Шаблон страницы с информацией о сервере
	 * @param array $values Значения серверных настроек:
	 *  array|null gd_info Параметры библиотеки GD
	 *  string ini_get_v Значение запрашивамой константы
	 *  string ini_get Запрашиваемая константа
	 *  string os Операционная система
	 *  string pms Post max size
	 *  string ums Upload max size
	 *  string ml Memory limit
	 *  string met Max execution time
	 *  string mysql версия MySQL */
	public static function Server($values)
	{
		static::Menu('server');

		$gdver='';

		if($values['gd_info'])
			foreach($values['gd_info'] as $k=>&$v)
				$gdver.=is_bool($v)
					? '<li><b>'.$k.'</b>: '
						.($v ? '<span style="color:green">Yes</span>' : '<span style="color:green">No</span>').'</li>'
					: '<li><b>'.$k.'</b>: '.$v.'</li>';

		$Lst=TableForm()
			->begin()
			->item('OS',$values['os'])
			->item('PHP',PHP_VERSION)
			->item('GD',$gdver ? '<ul style="list-style-type:none">'.$gdver.'</ul>' : '&mdash;')
			->item('DB',$values['mysql'])
			->item('Post max size',$values['pms'])
			->item('Upload max size',$values['ums'])
			->item('Memory limit',$values['ml'])
			->item('Max execution time',$values['met'])
			->item('Max int',PHP_INT_MAX)
			->item(static::$lang['get_value'],'<form method="post">'.Html::Input('ini_get',$values['ini_get']).Html::Button('?').'</form>');

		if($values['ini_get_v'] or $values['ini_get'])
			$Lst->item(htmlspecialchars($values['ini_get'],\CMS\ENT,\Eleanor\CHARSET),$values['ini_get_v']
				? htmlspecialchars($values['ini_get_v'],\CMS\ENT,\Eleanor\CHARSET) : '&mdash;');

		return Eleanor::$Template->Cover(
			$Lst->button('<a href="'.$GLOBALS['Eleanor']->Url.'">'.T::$lang['go-back'].'</a>')->end()
		);
	}

	/** Страница просмотра списка лог-файлов
	 * @param array $logs Перечень лог-файлов, ключи:
	 *  string path Полный путь к файлу относительно корня сайта
	 *  string descr Описание файла
	 *  int size Размер файла в байтах
	 *  string view Ссылка на просмотр файла
	 *  string download Ссылка на скачивание файла
	 *  string adelete Ссылка на удаление файла */
	public static function Logs($logs)
	{
		static::Menu('logs');

		if($logs)
		{
			$Lst=TableList(4)
				->begin(static::$lang['file'],static::$lang['path'],static::$lang['size'],[T::$lang['functs'],70]);

			foreach($logs as $v)
				$Lst->item(
					'<a href="'.$v['view'].'">'.$v['descr'].'</a>',
					'<a href="'.$v['download'].'">'.$v['path'].'</a>',
					\Eleanor\Classes\Files::BytesToSize($v['size']),
					$Lst('func',
						[$v['view'],static::$lang['view_log'],Eleanor::$Template->default['images'].'viewfile.png'],
						[$v['download'],static::$lang['download_log'],Eleanor::$Template->default['images'].'downloadfile.png'],
						[$v['delete'],static::$lang['delete_log'],Eleanor::$Template->default['images'].'delete.png',
							'extra'=>['onclick'=>'return confirm(\''.T::$lang['are_you_sure'].'\')']]
					)
				);

			$Lst->end()->s.='<br />';
		}
		else
			$Lst=Eleanor::$Template->Message(static::$lang['nologs'],'info');

		return Eleanor::$Template->Cover($Lst);
	}

	/** Просмотр лог-файла
	 * @param string|array В зависимости от типа, либо массив логов, либо текст лог-файла
	 * @param string $file Название лог-файла
	 * @param array $links перечень необходимых ссылок:
	 *  string download Ссылка на скачивание файла
	 *  string delete Ссылка на удаление файла
	 * @return string */
	public static function ShowLog($data,$file,$links)
	{
		static::Menu('logs');

		if(is_array($data))
		{
			$log='<div class="logs">';
			switch($file)
			{
				case'errors':
					foreach($data as $k=>$v)
					{
						$page=htmlspecialchars($v['d']['p'],\CMS\ENT,\Eleanor\CHARSET,false);
						$p=strpos($v['d']['e'],':');
						$v['d']['e']=substr_replace($v['d']['e'],'<span style="color:red">',$p+2,0);
						$v['d']['e']=substr_replace($v['d']['e'],'</b>('.$v['d']['n'].')',$p,0);
						$log.='<div class="warning" data-id="'.$k.'"><pre><code><b>'
							.$v['d']['e'].'</span><br />'.$v['d']['f'].'['.$v['d']['l'].']<br />'
							.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br /><a href="'.$page.'" target="_blank">'.($page ? $page : '/')
							.'</a></code></pre><div class="repair"><a href="#">'.static::$lang['fixed']
							.'</a></div></div>';
					}
				break;
				case'database':
					foreach($data as $k=>$v)
						$log.='<div class="warning" data-id="'.$k.'"><pre><code>'
							.(isset($v['d']['e']) ? '<b>'.$v['d']['e'].'</b><br />' : '')
							.(isset($v['d']['q']) ? 'Query: <span style="color:red">'.$v['d']['q'].'</span><br />' : '')
							.(isset($v['d']['h']) ? 'Host: '.$v['d']['h'].'<br />' : '')
							.(isset($v['d']['u']) ? 'User: '.$v['d']['u'].'<br />' : '')
							.(isset($v['d']['p']) ? 'Password: '.$v['d']['p'].'<br />' : '')
							.(isset($v['d']['db']) ? 'DB: '.$v['d']['db'].'<br />' : '')
							.(isset($v['d']['f'],$v['d']['l']) ? $v['d']['f'].'['.$v['d']['l'].']<br />' : '')
							.''.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br />Happend: <b>'.$v['d']['n'].'</b></code></pre><div class="repair"><a href="#">'
							.static::$lang['fixed'].'</a></div></div>';
				break;
				case'requests':
					foreach($data as $k=>$v)
					{
						$refs='';
						if(isset($v['d']['r']))
							foreach($v['d']['r'] as &$rv)
							{
								$rv=htmlspecialchars($rv,\CMS\ENT,\Eleanor\CHARSET,false);
								$refs.='<a href="'.$rv.'" target="_blank">'.($rv ? $rv : '/').'</a>, ';
							}

						$page=htmlspecialchars($v['d']['p'],\CMS\ENT,\Eleanor\CHARSET,false);
						$log.='<div class="warning" data-id="'.$k.'"><code><pre><b>'.$v['d']['e'].'</b>('.$v['d']['n'].')<br />'
							.(isset($v['d']['u']) ? '<a href="'.\CMS\UserLink($v['d']['ui'],$v['d']['u']).'">'
							.htmlspecialchars($v['d']['u'],\CMS\ENT,\Eleanor\CHARSET).'</a>' : 'Guest')
							.' &mdash; <a href="http://eleanor-cms.ru/whois/'.$v['d']['ip'].'">'.$v['d']['ip'].'</a> &mdash; '
							.$v['d']['b'].'<br />'
							.Eleanor::$Language->Date($v['d']['d'],'fdt')
							.'<br /><a href="'.$page.'" target="_blank">'.($page ? $page : '/').'</a>'
							.($refs ? ' &lt;&lt;&lt; '.rtrim($refs,', ') : '')
							.'</pre></code><div class="repair"><a href="#">'.static::$lang['fixed'].'</a></div></div>';
					}
			}

			$log.='</div><script>//<![CDATA[
$(function(){
	$(".logs a[href=#]").click(function(e){
		e.preventDefault();

		var div=$(this).closest(".warning");

		CORE.Ajax({
				id:div.data("id")
			},function(){
				if($(".logs .warning").length>1)
					div.remove();
				else
					$(".submitline :button").click();
			}
		);
	});
})//]]></script>';
		}
		else
			$log=Html::Text('text',$data,['style'=>'width:100%;','readonly'=>'readonly','rows'=>30]);

		return Eleanor::$Template->Cover('<p class="function"><a href="'.$links['download'].'" title="'
			.static::$lang['download_log'].'"><img src="'.Eleanor::$Template->default['images']
			.'downloadfile.png" alt="" /></a><a href="'.$links['delete'].'" title="'.static::$lang['delete_log']
			.'" onclick="return confirm(\''.T::$lang['are_you_sure'].'\')"><img src="'.Eleanor::$Template->default['images']
			.'delete.png" alt="" /></a></p><div style="margin:15px;max-width:953px;">'.$log
			.'</div><div class="submitline">'.Html::Button(T::$lang['go-back'],'button',[
				'onclick'=>'window.location=\''.$links['back'].'\'']).'</div>');
	}

	/** Элемент шаблона: блокнот. Вызывается и из AJAX
	 * @param string $edt содержит текстовый редактор или отредактированный шаблон
	 * @param bool $edit Флаг редактирования блокнота, в случае false - отображение
	 * @return string */
	public static function Notes($edt,$edit=false)
	{
		if(!$edit)
			$edt=OwnBB::Parse($edt);

		return'<div class="wbpad"><div class="brdbox">'
			.($edt ? $edt : '<div style="text-align:center;color:lightgray;font-size:1.5em">'.static::$lang['empty']
				.'</div>')
			.'</div></div><div class="submitline">'
			.Html::Button($edit ? T::$lang['save'] : T::$lang['edit'],'button',
				$edit ? ['data-save'=>1] : [])
			.'</div>';
	}

	/** Страница с лицензией и санкциями
	 * @param string $license содержит текст лицензии
	 * @param string $sanctions содержит текст санкций
	 * @return string */
	public static function License($license,$sanctions)
	{
		static::Menu('license');
		return Eleanor::$Template->Title(static::$lang['license'])->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$license.'</div><a href="cms/license/license-'
			.\CMS\Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'
			.Eleanor::$Template->default['images'].'print.png" alt="" /> '.static::$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable().'<br />'

			.Eleanor::$Template->Title(static::$lang['sanctions'])->OpenTable()
			.'<div class="textarea license" style="margin-left:5px">'.$sanctions.'</div><a href="cms/license/sanctions-'
			.\CMS\Language::$main.'.html" target="_blank" style="margin-left:5px"><img src="'
			.Eleanor::$Template->default['images'].'print.png" alt="" /> '.static::$lang['print'].'</a>'
			.Eleanor::$Template->CloseTable();
	}
}
General::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/general-*.php',false);

return General::class;