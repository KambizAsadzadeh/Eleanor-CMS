<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Шаблон для админки модуля новостей
*/
include __DIR__.'/Select2.php';

class TPLAdminNews
{
	public static
		/** @static Языковой массив */
		$lang;

	/** Генератор меню на основе ссылок модуля
	 * @param string $act Идентификатор активного пунта */
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$categs=isset($GLOBALS['Eleanor']->module['navigation']['categories']) ? $GLOBALS['Eleanor']->module['navigation']['categories'] : false;
		$options=isset($GLOBALS['Eleanor']->module['navigation']['options']) ? $GLOBALS['Eleanor']->module['navigation']['options'] : false;
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list'),
			$links['newlist'] ? array($links['newlist']['link'],sprintf(static::$lang['news'],$links['newlist']['cnt']),'act'=>false) : false,
			array($links['add'],static::$lang['add'],'act'=>$act=='add'),

			array($links['tags'],$lang['tags_list'],'act'=>$act=='tags'),
			array($links['addt'],static::$lang['add_tag'],'act'=>$act=='addt'),

			$options ? $options : array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
			$categs ? $categs : array($links['categories'],static::$lang['cats_manage']),
			//array($links['addf'],static::$lang['addf'],'act'=>$act=='addf'),
		);
	}

	/** Страница отображения всех тегов
	 * @param array $items Перечень статических страниц. ID=>[], ключи внутреннего массива:
	 *  string language Язык тега
	 *  string name Название тега
	 *  int cnt Количество новостей с данным тегом
	 *  string _aedit Ссылка на редактирование тега
	 *  string _adel Ссылка на удаление тега
	 * @param int $cnt Количество тегов всего
	 * @param int $pp Количество тегов на страницу
	 * @param array $qs Параметры запроса
	 * @param int $page Номер текущей страницы
	 * @param array $links Перечень необходимых ссылок:
	 *  string sort_name Сортировка по имени тега
	 *  string sort_cnt Сортировка по количеству новостей
	 *  string sort_id Сортировку по ID
	 *  string form_items Ссылка для параметра action формы, внутри которой происходит отображение списка тегов
	 *  callback pp(int) Генератор ссылок на изменение количества тегов отображаемых на странице
	 *  string first_page Ссылка на первую страницу
	 *  callback pages(int) Генератор ссылок на остальные страницы
	 * @return string */
	public static function TagsList($items,$cnt,$pp,$qs,$page,$links)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Новости'),
			'Список тегов'
		);

		static::Menu('tags');

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=T::$lang;

/*<!-- Button trigger modal -->
<button class="btn btn-primary" data-toggle="modal" data-target="#myModal">
  Кнопка вызова окошка
</button>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Заголовок окна</h4>
      </div>
      <div class="modal-body">
        Тут код окна
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
        <button type="button" class="btn btn-primary">Сохранить</button>
      </div>
    </div>
  </div>
</div>*/

		if($items)
		{
			$listtop=<<<HTML
			<div class="list-top">
				<!-- Фильтры -->
				<div class="filters">
					<p class="filters-text grey">Применен фильтр: По заголовку, По категории, По статусу <a class="filters-reset" href="#">&times;</a></p>
					<div class="dropdown">
						<button class="btn btn-default" data-toggle="dropdown">Изменить фильтр <i class="caret"></i></button>
						<form class="dropdown-menu dropform pull-right" method="post">
							<div class="form-group">
								<label for="fi[title]">Заголовок</label>
								<select class="form-control" name="fi[titlet]" id="fi[titlet]">
									<option value="b">Начинается с</option>
									<option value="q">Совпадает</option>
									<option value="e">Заканчивается на</option>
									<option value="m">Содержит</option>
								</select>
							</div>
							<div class="form-group">
								<input placeholder="Укажите заголовок или его часть" class="form-control" type="text" name="fi[title]" id="fi[title]">
							</div>
							<hr>
							<div class="form-group">
								<select class="form-control" name="fi[category]" id="fi[category]">
									<option value="0">Все категории</option>
									<option value="no">-без категорий-</option>
									<option value="1">Наши новости</option>
								</select>
							</div>
							<div class="form-group">
								<select class="form-control" name="fi[status]" id="fi[status]">
									<option value="-">Все статусы</option>
									<option value="-1">Ожидание модерации</option>
									<option value="0">Заблокировано</option>
									<option value="1">Активировано</option>
								</select>
							</div>
							<button type="submit" class="btn btn-primary">Применить</button>
						</form>
					</div>
				</div>
				<a class="btn btn-default" href="{$GLOBALS['Eleanor']->module['links']['addt']}">Добавить тег</a>
			</div>
HTML;
		
			#Список
			$Items=Eleanor::LoadListTemplate('table-list',Eleanor::$vars['multilang'] ? 4 : 3)
				->begin(
					array($lang['tname'],$qs['sort']=='name' ? $qs['so'] : false,$cnt>0 ? $links['sort_name'] : false),
					Eleanor::$vars['multilang'] ? $lang['language'] : false,
					array(static::$lang['nums'],$qs['sort']=='cnt' ? $qs['so'] : false,$cnt>0 ? $links['sort_cnt'] : false),
					array(Eleanor::Check(false,false,array('id'=>'mass-check')),'class'=>'col_check')
				);

			foreach($items as $k=>$v)
				$Items->item(
					$Items('main',
						$v['name'],
						array( array( $v['_aedit'], $ltpl['edit'] ), array( $v['_adel'], $ltpl['delete'] ))
					)+array('tr-extra'=>'item'.$k),
					Eleanor::$vars['multilang'] ? array(isset(Eleanor::$langs[$v['language']]) ? Eleanor::$langs[$v['language']]['name'] : '<i>'.$ltpl['all'].'</i>','col_lang') : false,
					array($v['cnt'],'col_number'),
					array(Eleanor::Check('mass[]',false,array('value'=>$k)),'col_check')
				);

			$newpp=$cnt>30 ? Eleanor::$Template->perpage($pp,$links['pp']) : '';
			$pager=Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']));
			
			/*
			Кнопка будет Disable
[22:09:09] Sergey: После выбора она активируется
[22:09:36] Sergey: disabled="disabled"
			*/
			$selected='
						<div class="pull-right form-inline">
							<select class="form-control" id="op" name="op">
								<option value="0">- Для выбранных -</option>
								<option value="delete">Удалить</option>
							</select>
							<button class="btn btn-default" type="submit"><b>Ok</b></button>
						</div>';

			$content='
			<!-- Список тегов -->
			<form method="post" id="checks-form">'
				.$Items->end()
				.<<<HTML
				<div class="list-foot">
					{$selected}
					{$pager}
					{$newpp}
				</div>
			</form>
HTML;
			#/Список

		}
		else
		{
			$listtop='';
			$content='<div class="alert alert-info">Тегов пока еще нет. Но у вас есть возможность <a href="'.$GLOBALS['Eleanor']->module['links']['addt'].'" class="alert-link">добавить их</a>.</div>';
		}

		return<<<HTML
		<section id="content">{$listtop}{$content}</section>
HTML;

		$GLOBALS['scripts'][]='js/checkboxes.js';

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'language'=>array(),
			'name'=>false,
			'namet'=>false,
			'cntf'=>false,
			'cntt'=>false,
		);

		if($items)
		{
			$Lst=Eleanor::LoadListTemplate('table-list',5)
				->begin(
					array($lang['tname'],'sort'=>$qs['sort']=='name' ? $qs['so'] : false,'href'=>$links['sort_name']),
					Eleanor::$vars['multilang'] ? $lang['language'] : false,
					array(static::$lang['nums'],'sort'=>$qs['sort']=='cnt' ? $qs['so'] : false,'href'=>$links['sort_cnt']),
					array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
					array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
				);

			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['name'].'</a>',
					Eleanor::$vars['multilang'] ? array(isset(Eleanor::$langs[$v['language']]) ? Eleanor::$langs[$v['language']]['name'] : '<i>'.$ltpl['all'].'</i>','center') : false,
					array($v['cnt'],'right'),
					'',/*$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),*/
					''//Eleanor::Check('mass[]',false,array('value'=>$k))
				);

			$content=(string)$Lst->end();
		}
		else 
			$content='<div class="alert alert-info">Тегов пока еще нет. Но у вас есть возможность <a href="'.$GLOBALS['Eleanor']->module['links']['addt'].'" class="alert-link">добавить их</a>.</div>';

		$opslangs=$finamet='';
		$temp=array(
			'b'=>static::$lang['begins'],
			'q'=>static::$lang['match'],
			'e'=>static::$lang['endings'],
			'm'=>static::$lang['contains'],
		);
		foreach($temp as $k=>&$v)
			$finamet.=Eleanor::Option($v,$k,$qs['']['fi']['namet']==$k);
		foreach(Eleanor::$langs as $k=>&$v)
			$opslangs.=Eleanor::Option($v['name'],$k,in_array($k,$qs['']['fi']['language']));
		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$lang['tname'].'</b><br />'.Eleanor::Select('fi[namet]',$finamet,array('style'=>'width:30%')).Eleanor::Input('fi[name]',$qs['']['fi']['name'],array('style'=>'width:68%')).'</td>
		<td>'.(Eleanor::$vars['multilang'] ? '<b>'.$lang['language'].'</b><br />'.Eleanor::Items('fi[language]',$opslangs,array('style'=>'width:100%','size'=>4)) : '').'</td>
	</tr>
	<tr>
		<td><label>'.Eleanor::Check(false,$qs['']['fi']['cntf']!==false or $qs['']['fi']['cntt']!==false,array('id'=>'ft')).'<b>'.static::$lang['nums'].'</b> '.static::$lang['from-to'].'</label><br />'.Eleanor::Input('fi[cntf]',(int)$qs['']['fi']['cntf'],array('type'=>'number','min'=>0)).' - '.Eleanor::Input('fi[cntt]',(int)$qs['']['fi']['cntt'],array('type'=>'number','min'=>0)).'</td>
		<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
	</tr>
</table>
<script>
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);

	var cntf=$("[name=\"fi[cntf]\"]"),
		cntt=$("[name=\"fi[cntt]\"]");
	cntf.change(function(){
		var v=$(this).val();
		if(parseInt(cntt.val())<v)
			cntt.val(v);
	}).change();
	cntt.change(function(){
		var v=$(this).val();
		if(parseInt(cntf.val())>v && v>=0)
			cntf.val(v);
	}).change();
	$("#ft").change(function(){
		$("[name^=\"fi[cnt\"]").prop("disabled",!$(this).prop("checked"));
	}).change();
})</script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$content
			.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['nto_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		Страница создается/редактирования тега
		$id - идентификатор редактируемого тега, если $id==0 значит тег создается
		$controls - перечень контролов в соответствии с классом контролов. Если какой-то элемент массива не является массивом, значит это заголовок подгруппы контролов
		$values - результирующий HTML код контролов, который необходимо вывести на странице. Ключи данного массива совпадают с ключами $controls
		$errors - массив ошибок
		$back - URL возврата
		$hasdraft - признак наличия черновика
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление категории или false
			nodraft - ссылка на правку/добавление категории без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEditTag($id,$controls,$values,$errors,$back,$hasdraft,$links)
	{
		$ltpl=T::$lang;

		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['tags'],'Список тегов'),
			'Добавить тег'
		);

		#ToDo! Конфигурации вынести в генератор контролов
		#Скрипты - поскольку контролы передаются в чистом виде (без классов).
		$GLOBALS['head'][]='<script>$(function(){
	$("#content input, #content select").addClass("form-control");
})</script>';

		#Menu
		static::Menu($id ? 'editt' : 'addt');

		#Errors
		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		$errors=join('',$errors);
		#/Errors

		#Content
		$content='';
		foreach($controls as $k=>&$v)
			if($v and is_array($v) and !empty($values[$k]))
				$content.='
							<div class="form-group">
								<label'.($v['descr'] ? ' title="'.$v['descr'].'"' : '').'>'.$v['title'].'</label><br />
								'.Eleanor::$Template->LangEdit($values[$k],null).'
							</div>';
		#/Content

		#Buttons
		$draft=Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '');

		$success='<button type="submit" class="btn btn-success"><b>'
			.($id ? static::$lang['savet'] : static::$lang['add_tag'])
			.'</b></button>';

		$delete=$links['delete'] ? '<button type="button" onclick="window.location=\''.$links['delete'].'\'" class="ibtn ib-delete pull-right"><i class="ico-del"></i><span class="thd">'.$ltpl['delete'].'</span></button>' : '';

		if($back)
			$delete.=Eleanor::Input('back',$back,array('type'=>'hidden'));
		#/Buttons 

		return<<<HTML
			{$errors}
			<section id="content">
				<form method="post">
					<div id="mainbar">
						<div class="block">
{$content}
							{$delete}
							{$success}
							{$draft}
						</div>
					</div>
				</form>
			</section>
HTML;
	}

	/*
		Страница отображения всех новостей
		$items - массив новостей. Формат: ID=>array(), ключи внутреннего массива:
			cats - массив ID категорий, к которым принадлежит данная новость
			date - дата публикации новости
			enddate - дата завершения показа новости
			author - имя автора новости, безопасный HTML
			author_id - ID автора новости
			status - статус активности новости: 0 - не активна, 1 - активна, -1 - ожидает модерации, -2 - ожидает наступления даты активации, 2 - закреплена
			title - название новости
			_aedit - ссылка на редактирование новости
			_adel - ссылка на удаление новости
			_aswap - ссылка на обращение активности новости, если равна false - значит ссылка недоступна (частный случай)
		$categs - массив категорий новости. Форма: ID=>array(), ключи внутреннего массива:
			title - название категории
		$cnt - количество тегов всего
		$pp - количество тегов на страницу
		$qs - массив параметров адресной строки для каждого запроса
		$page - номер текущей страницы, на которой мы сейчас находимся
		$links - перечень необходимых ссылок, массив с ключами:
			sort_title - ссылка на сортировку списка $items по названию (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_date - ссылка на сортировку списка $items по дате (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_author - ссылка на сортировку списка $items по автору (возрастанию/убыванию в зависимости от текущей сортировки)
			sort_id - ссылка на сортировку списка $items по ID (возрастанию/убыванию в зависимости от текущей сортировки)
			form_items - ссылка для параметра action формы, внтури которой происходит отображение перечня $items
			pp - фукнция-генератор ссылок на изменение количества новостей отображаемых на странице
			first_page - ссылка на первую страницу пагинатора
			pages - функция-генератор ссылок на остальные страницы
	*/
	public static function ShowList($items,$categs,$cnt,$pp,$qs,$page,$links)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			'Новости',
		);
	
		static::Menu('list');
		$GLOBALS['scripts'][]='js/checkboxes.js';
		$ltpl=T::$lang;

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'title'=>false,
			'titlet'=>false,
			'status'=>false,
			'category'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',6)->begin(
			array($ltpl['title'],'sort'=>$qs['sort']=='title' ? $qs['so'] : false,'href'=>$links['sort_title']),
			static::$lang['category'],
			array(static::$lang['date'],'sort'=>$qs['sort']=='date' ? $qs['so'] : false,'href'=>$links['sort_date']),
			array(static::$lang['author'],'sort'=>$qs['sort']=='author' ? $qs['so'] : false,'href'=>$links['sort_author']),
			array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
			array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
		);

		if($items and false)
		{
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{
				$cats='';
				foreach($v['cats'] as &$cv)
					if(isset($categs[$cv]))
						$cats.=($cats ? '' : '<b>').$categs[$cv]['title'].($cats ? '' : '</b>').', ';
				$Lst->item(
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.$v['title'].'</a>',
					$cats ? rtrim($cats,', ') : array('--','center'),
					array(Eleanor::$Language->Date($v['date'],'fd'),'center'),
					$v['author_id'] ? '<a href="'.Eleanor::$Login->UserLink(htmlspecialchars_decode($v['author'],ELENT),$v['author_id']).'">'.$v['author'].'</a>' : $v['author'],
					$Lst('func',
						$v['_aswap'] ? array($v['_aswap'],$v['status']<=0 ? $ltpl['activate'] : $ltpl['deactivate'],$v['status']<0 ? $images.'waiting.png' : $images.($v['status']==0 ? 'inactive.png' : 'active.png')) : '<img src="'.$images.'inactive.png'.'" alt="" title="'.static::$lang['endeddate'].'" />',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty(static::$lang['not_found']);

		$fititlet=$statuses='';
		$temp=array(
			'b'=>static::$lang['begins'],
			'q'=>static::$lang['match'],
			'e'=>static::$lang['endings'],
			'm'=>static::$lang['contains'],
		);
		foreach($temp as $k=>&$v)
			$fititlet.=Eleanor::Option($v,$k,$qs['']['fi']['titlet']==$k);
		$temp=array(
			-1=>static::$lang['waitmod'],
			0=>static::$lang['blocked'],
			1=>static::$lang['active'],
		);
		foreach($temp as $k=>&$v)
			$statuses.=Eleanor::Option($v,$k,$qs['']['fi']['status']!==false and $qs['']['fi']['status']==$k);
		return Eleanor::$Template->Cover(
			'<form method="post">
<table class="tabstyle tabform" id="ftable">
	<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
	<tr>
		<td><b>'.$ltpl['title'].'</b><br />'.Eleanor::Select('fi[titlet]',$fititlet,array('style'=>'width:30%')).Eleanor::Input('fi[title]',$qs['']['fi']['title'],array('style'=>'width:68%')).'</td>
		<td><b>'.static::$lang['category'].'</b><br />'.Eleanor::Select('fi[category]',Eleanor::Option('&mdash;',0,false,array(),2).Eleanor::Option(static::$lang['nocat'],'no',$qs['']['fi']['category']=='no').$GLOBALS['Eleanor']->Categories->GetOptions($qs['']['fi']['category'])).'</td>
	</tr>
	<tr>
		<td><b>'.static::$lang['status'].'</b><br />'.Eleanor::Select('fi[status]',Eleanor::Option('&mdash;','-',false,array(),2).$statuses).'</td>
		<td style="text-align:center;vertical-align:middle">'.Eleanor::Button($ltpl['apply']).'</td>
	</tr>
</table>
<script>
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
})</script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['tto_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($ltpl['deactivate'],'d').Eleanor::Option($ltpl['delete'],'k').Eleanor::Option(static::$lang['waitmod'],'m')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);
	}

	/*
		Страница создается/редактирования новости
		$id идентификатор редактируемой новости, если $id==0 значит новость создается
		$values массив значений полей
			Общие ключи:
			cats - массив категорий
			date  - дата публикации новости
			pinned - дата до наступления которой, новость будет закреплена
			enddate - завершение показов новости
			author - имя автора новости
			author_id - ID автора новости
			show_detail - флаг включения показа ссылки "подробнее" при отсутствии подробностей новости
			show_sokr - флаг включения отображения показа сокращенной новости при просмотре подробной
			reads - количество просмотров новости
			status - статус активности новости: 0 - не активна, 1 - активна, -1 - ожидает модерации

			Языковые ключи:
			title - заголовок новости
			announcement - анонс новости
			text - текст новости
			uri - URI новости
			meta_title - заголовок окна браузера при просмотре новости
			meta_descr - мета описание новости

			Особые языковые ключи:
			tags - теги новости

			Специальные ключи:
			_onelang - флаг моноязычной новости при включенной мультиязычности
			_maincat - идентификатор основной категории новости
		$errors - массив ошибок
		$uploader - интерфейс загрузчика
		$voting - интерфейс опросника
		$bypost - признак того, что данные нужно брать из POST запроса
		$hasdraft - признак наличия черновика
		$back - URL возврата
		$links - перечень необходимых ссылок, массив с ключами:
			delete - ссылка на удаление новости или false
			nodraft - ссылка на правку/добавление новости без использования черновика или false
			draft - ссылка на сохранение черновиков (для фоновых запросов)
	*/
	public static function AddEdit($id,$values,$errors,$uploader,$voting,$bypost,$hasdraft,$back,$links)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Материалы'),
			is_array($GLOBALS['title']) ? end($GLOBALS['title']) : $GLOBALS['title']
		);

		#Скрипты
		$GLOBALS['head'][]='<script>
$(function(){
	$("select[name=status]").change(function(){
		var th=$(this).removeClass("published trash wait");
		switch(th.val())
		{
			case "1":
				th.addClass("published");
			break;
			case "0":
				th.addClass("trash");
			break;
			default:
				th.addClass("wait");
		}
	}).change();
})</script>';

		#Menu
		static::Menu($id ? 'edit' : 'add');

		$ltpl=T::$lang;
		$ti=0;

		#Multilang values
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>++$ti,'id'=>'title-'.$k));
				$ml['announcement'][$k]=$GLOBALS['Eleanor']->Editor->Area('announcement['.$k.']',Eleanor::FilterLangValues($values['announcement'],$k),array('post'=>$bypost,'no'=>array('tabindex'=>++$ti,'rows'=>10)));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('post'=>$bypost,'no'=>array('tabindex'=>++$ti,'rows'=>15)));

				$ml['uri'][$k]=Eleanor::Input('uri['.$k.']',Eleanor::FilterLangValues($values['uri'],$k),array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title-'.$k.'\').val())','tabindex'=>++$ti));
				$ml['meta_title'][$k]=Eleanor::Input('meta_title['.$k.']',Eleanor::FilterLangValues($values['meta_title'],$k),array('tabindex'=>++$ti));
				$ml['meta_descr'][$k]=Eleanor::Input('meta_descr['.$k.']',Eleanor::FilterLangValues($values['meta_descr'],$k),array('tabindex'=>++$ti));

				$ml['tags'][$k]=TPLSelect2::Tags('tags['.$k.']',Eleanor::FilterLangValues($values['tags'],$k),array('tabindex'=>++$ti,'id'=>'tags-'.$k));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Input('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('tabindex'=>++$ti,'id'=>'title')),
				'announcement'=>$GLOBALS['Eleanor']->Editor->Area('announcement',$values['announcement'],array('post'=>$bypost,'no'=>array('tabindex'=>++$ti,'rows'=>10))),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('post'=>$bypost,'no'=>array('tabindex'=>++$ti,'rows'=>15))),

				'uri'=>Eleanor::Input('uri',$values['uri'],array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title\').val())','tabindex'=>++$ti)),
				'meta_title'=>Eleanor::Input('meta_title',$values['meta_title'],array('tabindex'=>++$ti)),
				'meta_descr'=>Eleanor::Input('meta_descr',$values['meta_descr'],array('tabindex'=>++$ti)),

				'tags'=>TPLSelect2::Tags('tags',Eleanor::FilterLangValues($values['tags']),array('tabindex'=>++$ti,'id'=>'tags')),
			);
		#/Multilang values

		#Категории
		#ToDo!
		$category=
			'<br />'
			.Select2::Items('cats',$GLOBALS['Eleanor']->Categories->GetOptions($values['cats']),array('id'=>'categories','tabindex'=>++$ti))
			.'<br />'
			.Select2::Select('_maincat',$GLOBALS['Eleanor']->Categories->GetOptions($values['_maincat']),array('id'=>'category','tabindex'=>++$ti));
		#[E] Категории

		#Tags
		$tags=Eleanor::$Template->LangEdit($ml['tags'],'tags');
		if(Eleanor::$vars['multilang'])
			$tags.='<script>$(function(){
	var label=$("#tags-label");
	$("#a-tab-'.Language::$main.'-tags").closest("ul").find("a").on("shown.bs.tab",function(e){
		label.prop("for","tags-"+$(this).data("language"));
	});
})</script>';
		#/Tags

		$announce=Eleanor::$Template->LangEdit($ml['announcement'],null);
		$fulltext=Eleanor::$Template->LangEdit($ml['text'],null);

		#Buttons
		$draft=Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '');

		$success='<button type="submit" class="btn btn-success" tabindex="'.++$ti.'"><b>'
			.($id ? static::$lang['save'] : static::$lang['add'])
			.'</b></button>';

		$delete=$links['delete'] ? '<button type="button" onclick="window.location=\''.$links['delete'].'\'" class="ibtn ib-delete"><i class="ico-del"></i><span class="thd">'.$ltpl['delete'].'</span></button>' : '';

		if($back)
			$delete.=Eleanor::Input('back',$back,array('type'=>'hidden'));
		#/Buttons

		#Errors
		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		$errors=join($errors);
		#/Errors

		#Dates
		$date='<span title="'.static::$lang['pdate_'].'">'.static::$lang['pdate'].'</span><br />'.Eleanor::$Template->DatePicker('date',$values['date'],true,array('tabindex'=>$ti));

		return<<<HTML
			{$errors}
			<section id="content">
				<form method="post">
					<div id="mainbar">
						<div class="block">
							<input class="form-control input-lg" type="text" placeholder="Укажите заголовок">
							<br>
							<div class="form-group">
								<label>Анонс</label>
								{$announce}
							</div>
							<div class="form-group">
								<label>Полная новость</label>
								{$fulltext}
							</div>
						</div>
						<!-- Опрос -->
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b2">Опрос</p>
							<div id="b2" class="collapse in">
								<div class="bcont">
									Тест опроса
								</div>
							</div>
						</div>
						<!-- Опрос [E] -->
						<!-- Загрузчик -->
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#bupl">Uploader - временно тут пока не подключены окна</p>
							<div id="bupl" class="collapse in">
								<div class="bcont el-uploader">
									<div class="upl-loadframe">
										<button type="button" class="btn btn-primary"><b>Выберите файлы</b></button>
										<p class="upl-loadframe-text">
											<b>... или перетащите мышкой</b><br>
											<small>Не более 8mb или 20 файла(ов).</small>
										</p>
									</div>
									<div class="upl-top">
										<b>Файлы на сайте:</b>
										<button class="ubtn" title="Обновить"><i class="ico-reload"></i></button>
										<button class="ubtn" title="Создать папку"><i class="ico-addfolder"></i></button>
										<button class="ubtn ubtn-check" title="Создать Watermark"><i class="ico-watermark"></i></button>
									</div>
									<div class="upl-files-box">
										<div class="upl-breadcrumb">
											<a class="upl-back" title="Назад" href="#"><i class="ico-arrow-left"></i></a>
											<ul>
												<li><a href="#">temp</a></li>
												<li><a href="#">cat</a></li>
												<li>51e99d6dab6a1</li>
											</ul>
										</div>
										<div class="upl-files-list">
											<!-- File - folder -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-folder">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Название шаблона 3</span><span class="fl-type">папка</span>
												</div>
											</div>
											<!-- File - folder [E] -->
											<!-- File - folder -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-folder">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Алгоритм Работы</span><span class="fl-type">папка</span>
												</div>
											</div>
											<!-- File - folder [E] -->
											<!-- File - folder -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-folder">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Zcore.config Zcore.config Zcore.config Zcore.config</span><span class="fl-type">папка</span>
												</div>
											</div>
											<!-- File - folder [E] -->
											<!-- File - folder -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-folder">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Temp</span><span class="fl-type">папка</span>
												</div>
											</div>
											<!-- File - folder [E] -->
											<!-- File - Image -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-img" style="background-image: url(tmp/img-file1.jpg);">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Вставить в редактор</a></li>
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Картинка - 1</span><span class="fl-type">.png</span>
												</div>
											</div>
											<!-- File - Image [E] -->
											<!-- File - Image -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-img" style="background-image: url(tmp/img-file2.jpg);">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Вставить в редактор</a></li>
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Картинка - 2</span><span class="fl-type">.png</span>
												</div>
											</div>
											<!-- File - Image [E] -->
											<!-- File - PDF -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-pdf">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Вставить в редактор</a></li>
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Документ</span><span class="fl-type">.pdf</span>
												</div>
											</div>
											<!-- File - PDF [E] -->
											<!-- File - DOC -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-doc">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Вставить в редактор</a></li>
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Документ Word</span><span class="fl-type">.doc</span>
												</div>
											</div>
											<!-- File - DOC [E] -->
											<!-- File - XLS -->
											<div class="fl-item">
												<div class="fl-item-inn">
													<div class="fl-icon fl-xls">
														<!-- edit -->
														<div class="min-edit">
															<button class="dropdown-toggle" data-toggle="dropdown" title="Изменить"><i class="ico-setting"></i></button>
															<ul class="dropdown-menu">
																<li><a href="#">Вставить в редактор</a></li>
																<li><a href="#">Переименовать</a></li>
																<li><a href="#">Удалить</a></li>
															</ul>
														</div>
														<!-- edit [e] -->
													</div>
													<span class="fl-name">Документ Excel</span><span class="fl-type">.xls</span>
												</div>
											</div>
											<!-- File - XLS [E] -->
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- Загрузчик [E] -->
					</div>
					<div id="rightbar">
						<!-- Настройки -->
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b3">Настройки</p>
							<div id="b3" class="collapse in">
								<div class="bcont">
									<div class="form-group">
										<label for="categories">Категории</label>
										{$category}
									</div>
									<div class="form-group">
										<label id="tags-label">Теги</label>
										{$tags}
									</div>
									<fieldset>
										<div class="checkbox"><label><input type="checkbox"> Для всех языков</label></div>
										<div class="checkbox"><label><input type="checkbox"> Отображать анонс в полной новости?</label></div>
									</fieldset>
								</div>
							</div>
						</div>
						<!-- Настройки [E] -->
						<!-- Расширенное -->
						<div class="block-t expand">
							<p class="btl collapsed" data-toggle="collapse" data-target="#b4">Расширенное</p>
							<div id="b4" class="collapse">
								<div class="bcont">
									{$date}
								</div>
							</div>
						</div>
						<!-- Расширенное [E] -->
						<!-- Миниатюра -->
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b5">Миниатюра</p>
							<div id="b5" class="collapse in">
								<div class="bcont">
									Проверка блока Проверка блока Проверка блока Проверка блока
								</div>
							</div>
						</div>
						<!-- Миниатюра [E] -->
						<!-- Стиль оформления -->
						<div class="block-t expand">
							<p class="btl" data-toggle="collapse" data-target="#b6">Стиль оформления</p>
							<div id="b6" class="collapse in">
								<div class="bcont">
									Проверка блока Проверка блока Проверка блока Проверка блока
								</div>
							</div>
						</div>
						<!-- Стиль оформления [E] -->
						<div class="inv-block"></div>
					</div>
					<!-- FootLine -->
					<div class="submit-pane">
						<button type="button" class="btn btn-primary pull-right"><b>Работа с файлами</b></button>
						{$delete}
						{$success}
						<select name="status" class="form-control">
							<option value="1">Опубликовано</option>
							<option value="-1">На проверке</option>
							<option value="0">В корзине</option>
						</select>
						{$draft}
					</div>
					<!-- FootLine [E] -->
				</form>
			</section>
HTML;


		/*
		#$GLOBALS['scripts'][]='addons/autocomplete/jquery.autocomplete.js'; #Автор есть - это не нужно.
		#$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		$Lst=Eleanor::LoadListTemplate('table-form')
			->form()
			->begin()
			->item($ltpl['title'],Eleanor::$Template->LangEdit($ml['title'],null));
		if($GLOBALS['Eleanor']->Categories->dump)
			$Lst->item(static::$lang['categs'],Eleanor::Items('cats',$GLOBALS['Eleanor']->Categories->GetOptions($values['cats']),array('id'=>'cs','tabindex'=>2)))
				->item(static::$lang['maincat'],Eleanor::Select('_maincat',$GLOBALS['Eleanor']->Categories->GetOptions($values['_maincat']),array('id'=>'mc','tabindex'=>3)));
		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,4));
		$c=(string)$Lst->end();

		$text=(string)$Lst->begin()
			->item(array(static::$lang['tags'],Eleanor::$Template->LangEdit($ml['tags'],null),'descr'=>static::$lang['tags_']))
			->item(array(static::$lang['announcement'],Eleanor::$Template->LangEdit($ml['announcement'],null),'descr'=>static::$lang['announcement_']))
			->item(static::$lang['text'],Eleanor::$Template->LangEdit($ml['text'],null))
			->item(array(static::$lang['show_sokr'],Eleanor::Check('show_sokr',$values['show_sokr'],array('tabindex'=>8)),'descr'=>static::$lang['show_sokr_']))
			->item(array(static::$lang['show_detail'],Eleanor::Check('show_detail',$values['show_detail'],array('tabindex'=>9)),'descr'=>static::$lang['show_detail_']))
			->item(static::$lang['status'],Eleanor::Select('status',Eleanor::Option(static::$lang['waitmod'],-1,$values['status']==-1).Eleanor::Option(static::$lang['blocked'],0,$values['status']==0).Eleanor::Option(static::$lang['active'],1,$values['status']==1),array('tabindex'=>10)))
			->end();

		$Lst->begin()
			->item('URI',Eleanor::$Template->LangEdit($ml['uri'],null))
			->item(static::$lang['author'],Eleanor::$Template->Author($values['author'],$values['author_id'],12))
			->item(array(static::$lang['pdate'],Dates::Calendar('date',$values['date'],true,array('tabindex'=>13)),'tip'=>static::$lang['pdate_']))
			->item(static::$lang['pinned'],Dates::Calendar('pinned',$values['pinned'],true,array('tabindex'=>14)))
			->item(array(static::$lang['enddate'],Dates::Calendar('enddate',$values['enddate'],true,array('tabindex'=>15)),'tip'=>static::$lang['enddate_']))
			->item(static::$lang['reads'],Eleanor::Input('reads',$values['reads'],array('tabindex'=>16)))
			->item('Window title',Eleanor::$Template->LangEdit($ml['meta_title'],null))
			->item('Meta description',Eleanor::$Template->LangEdit($ml['meta_descr'],null));
		if($id)
			$Lst->item(array(static::$lang['ping'],Eleanor::Check('_ping',$values['_ping'],array('tabindex'=>19)),'descr'=>static::$lang['ping_']));
		$extra=(string)$Lst->end();

		$c.=$Lst->tabs(
			array($ltpl['general'],$text),
			array(Eleanor::$Language['main']['options'],$extra),
			array(static::$lang['voting'],$voting)
			//array('Дополнительные поля',Eleanor::$Template->Message('И это тоже в разработке...','info'))#ToDo!
		)
		->submitline((string)$uploader)
		->submitline(
			$back
			.Eleanor::Button($id ? static::$lang['save'] : static::$lang['add'],'submit',array('tabindex'=>20))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Input('_draft','n'.$id,array('type'=>'hidden'))
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)
		->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($c,$errors,'error').'<script>
$(function(){
	$("#cs").change(function(){
		var cs=this;
		$("#mc option").each(function(i){
			if($("option:eq("+i+")",cs).prop("selected"))
				$(this).prop("disabled",false);
			else
				$(this).prop({disabled:true,selected:false});
		});
	}).change();
	$("input[name^=\"tags\"]").each(function(){
		var m=$(this).prop("name").match(/tags\[([a-z]+)\]/),
			p={
				module:"'.$GLOBALS['Eleanor']->module['name'].'",
				event:"tags",
				lang:(m && !$("input[name=\"_onelang\"]").prop("checked")) ? m[1] : ""
			},
			a=$(this).autocomplete({
				serviceUrl:CORE.ajax_file,
				minChars:2,
				delimiter:/,\s* /,
				params:p
			});
		$("input[name=\"_onelang\"]").change(function(){
			p.lang=(m && !$(this).prop("checked")) ? m[1] : "";
			a.setOptions({params:p})
		});
	});
})</script>';*/
	}

	/*
		Страница удаления новости
		$a - массив параметров удаляемой новосоти
			title - новости
		$back - URL возврата
	*/
	public static function Delete($a,$back)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Новости'),
			'Удаление новости'
		);
	
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['submit_del'],$a['title']),$back));
	}

	/*
		Страница удаления тега
		$a - массив параметров удаляемого тега
			name - тег
		$back - URL возврата
	*/
	public static function DeleteTag($a,$back)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Новости'),
			array($GLOBALS['Eleanor']->module['links']['tags'],'Список тегов'),
			'Удаление тега'
		);

		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deletingt'],$a['name']),$back));
	}

	/*
		Обертка для категорий
		$c - интерфейс категорий
	*/
	public static function Categories($c)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Новости'),
			'categories'=>'Категории'
		);

		static::Menu();
		return$c;
	}

	/*
		Обертка для настроек
		$c - интерфейс настроек
	*/
	public static function Options($c)
	{
		#SpeedBar
		$GLOBALS['Eleanor']->module['speedbar']=array(
			array(Eleanor::$services['admin']['file'].'?section=modules','Модули'),
			array($GLOBALS['Eleanor']->module['links']['list'],'Новости'),
			'Настройки'
		);

		static::Menu('options');
		return$c;
	}
}
TplAdminNews::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/news-*.php',false);