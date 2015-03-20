<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use \CMS\Eleanor, Eleanor\Classes\Html, CMS\Language;

defined('CMS\STARTED')||die;

/** Шаблоны менеджера задач */
class Tasks
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
			[$links['list'],Eleanor::$Language['tasks']['list'],'act'=>$act=='list'],
			$links['create'] ? [$links['create'],static::$lang['create'],'extra'=>['class'=>'iframe'.($act=='create' ? ' active' : '')]] : null,
		];
	}

	/** Список всех задач
	 * @param array $items Перечень задач. Формат: ID=>[], ключи:
	 *  [string task] Файл-обработчик
	 *  [string title] Название
	 *  [bool free] Флаг завершенности выполнения. При true - в данный момент происходит выполнение
	 *  [string lastrun] Время последнего запуска
	 *  [string nextrun] Время следующего запуска
	 *  [string run_month] Месяц запуска: * - любой месяц; +N - каждые N месяцев; 1,3,5-7 - конкретные месяцы
	 *  [string run_day] День запуска: * - любой день; +N - каждые N дней; 1,3,5-7 - конкретные дни месяца; w1,3,5-7 - конкретные дни месяца
	 *  [string run_hour] Часы запуска: * - любой час; +N - каждые N часов; 1,3,5-7 - конкретные часы
	 *  [string run_minute] Минуты запуска: * - любая минута; +N - каждые N минут; 1,3,5-7 - конкретные минуты
	 *  [string run_second] Секунды запуска: * - любая секунда; +N - каждые N секунд; 1,3,5-7 - конкретные секунды
	 *  [int status] Статус: 1 - активировано, 0 - деактивировано
	 *  [string _atoggle] Ссылка-тумблер на переключение активности
	 *  [string _aedit] Ссылка на редактирование
	 *  [string _adel] Ссылка на удаление
	 *  [string _arun] Ссылка на запуск
	 * @param bool $notempty Флаг того, что задачи существуют, несмотря на настройки фильтра
	 * @param int $cnt Количество задач всего
	 * @param int $pp Количество пунктов на страницу
	 * @param array $query Параметры запроса
	 * @param int $page Номер текущей страницы списка
	 * @param array $links Перечень ссылок:
	 *  [string nofilter] Ссылка на очистку фильтров
	 *  [string sort_task] Ссылка на сортировку списка названию файла обработчика
	 *  [string sort_nextrun] Ссылка на сортировку списка по дате следующего запуска
	 *  [string sort_lastrun] Ссылка на сортировку списка по дате предыдущего запуска
	 *  [string sort_status] Ссылка на сортировку списка по фдагу активности
	 *  [string sort_id] Ссылка на сортировку списка по ID
	 *  [callback pp] Генератор ссылок на изменение количества пунктов отображаемых на странице
	 *  [string first_page] Ссылка на первую страницу
	 *  [callback pagination] Генератор ссылок на остальные страницы
	 * @return string */
	public static function ShowList($items,$notempty,$cnt,$pp,$query,$page,$links)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			$GLOBALS['Eleanor']->module['title']
		];

		static::Menu('list');

		$t_lang=T::$lang;
		$c_lang=static::$lang;

		if($items)
		{
			$Items=TableList(5)
				->head(
					[T::$lang['status'],$query['sort']=='status' ? $query['order'] : false,$links['sort_status'],'col_status'],
					[T::$lang['name'],$query['sort']=='task' ? $query['task'] : false,$links['sort_task'],'col_item'],
					static::$lang['run_time'],
					[static::$lang['lastrun'],$query['sort']=='lastrun' ? $query['task'] : false,$links['sort_lastrun']],
					[static::$lang['nextrun'],$query['sort']=='nextrun' ? $query['task'] : false,$links['sort_nextrun']]
				);

			foreach($items as $k=>$v)
			{
				$run='';

				if($v['run_month']!=='' and $v['run_month'][0]!='*')
					if($v['run_month'][0]=='+')
					{
						$v['run_month']=(int)ltrim($v['run_month'],'+');

						$lang=static::$lang['run_month'];
						$run=$lang($v['run_month']);
					}
					else
						$run.=sprintf(static::$lang['months%'],$v['run_month']);

				if($v['run_day']!=='' and $v['run_day'][0]!='*')
					if($v['run_day'][0]=='+')
					{
						$v['run_day']=(int)ltrim($v['run_day'],'+');

						$lang=static::$lang['run_day'];
						$run=$lang($v['run_day']);
					}
					elseif($v['run_day'][0]=='w')
						$run.=sprintf(static::$lang['wdays%'],ltrim($v['run_day'],'w'));
					else
						$run.=sprintf(static::$lang['days%'],$v['run_day']);

				if($v['run_hour']!=='' and $v['run_hour'][0]!='*')
					if($v['run_hour'][0]=='+')
					{
						$v['run_hour']=(int)ltrim($v['run_hour'],'+');

						$lang=static::$lang['run_hour'];
						$run=$lang($v['run_hour']);
					}
					else
						$run.=sprintf(static::$lang['hours%'],$v['run_hour']);

				if($v['run_minute']!=='' and $v['run_minute'][0]!='*')
					if($v['run_minute'][0]=='+')
					{
						$v['run_minute']=(int)ltrim($v['run_minute'],'+');

						$lang=static::$lang['run_minute'];
						$run=$lang($v['run_minute']);
					}
					else
						$run.=sprintf(static::$lang['minutes%'],$v['run_minute']);

				if($v['run_second']!=='' and $v['run_second'][0]!='*')
					if($v['run_second'][0]=='+')
					{
						$v['run_second']=(int)ltrim($v['run_second'],'+');

						$lang=static::$lang['run_second'];
						$run=$lang($v['run_second']);
					}
					else
						$run.=sprintf(static::$lang['seconds%'],$v['run_second']);

				$Items->item(
					$Items('status',$v['status'],$v['status'] ? T::$lang['deactivate'] : T::$lang['activate'],$v['_atoggle']),
					$Items('main',
						($v['title'] ? $v['title'].' &mdash; ' : '').ucfirst($v['task']),
						[ [$v['_aedit'], T::$lang['edit'], 'extra'=>['class'=>'iframe']], [ $v['_adel'], T::$lang['delete'], 'extra'=>['class'=>'delete']],
							$v['_arun'] ? [$v['_arun'], static::$lang['run']] : null]
					)+['tr-extra'=>['id'=>'item'.$k]],
					rtrim($run,'; '),
					(int)$v['lastrun']>0 ? Eleanor::$Language->Date($v['lastrun'],'fdt') : '&mdash;',
					(int)$v['nextrun']>0 ? Eleanor::$Language->Date($v['nextrun'],'fdt') : '&mdash;'
				);
			}

			$Items->end()->foot('',$cnt,$pp,$page,$links);

			$back=Html::Input('back',\Eleanor\SITEDIR.\CMS\Url::$current,['type'=>'hidden']);
			$Items.=<<<HTML
<!-- Окно подтверждение удаления -->
<div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title">{$t_lang['delete-confirm']}</h4>
			</div>
			<div class="modal-body">{$c_lang['delete-text-span']}</div>
			<div class="modal-footer"><form method="post" id="delete-form">{$back}
				<button type="button" class="btn btn-default" data-dismiss="modal">{$t_lang['cancel']}</button>
				<button type="submit" class="btn btn-danger once" name="ok">{$t_lang['delete']}</button>
			</form></div>
		</div>
	</div>
</div>
<script>$(ItemsDelete)</script>
HTML;
		}
		else
			$Items=T::$T->Alert(static::$lang['not_found'],'info');

		if($notempty)
		{
			$filters=[
				'task'=>'',
				'title'=>'',
			];

			if($links['nofilter'] and isset($query['fi']))
			{
				$caption=T::$lang['change-filter'];
				$applied=[];

				foreach($query['fi'] as $k=>$v)
					switch($k)
					{
						case'title':
							$applied[]=static::$lang['by-title'];
							$filters['title']=$v;
						break;
						case'task':
							$applied[]=static::$lang['by-task'];
							$filters['task']=$v;
					}

				$applied=sprintf(static::$lang['applied-by%'],join(', ',$applied));
				$nofilter=<<<HTML
<p class="filters-text grey">{$applied}<a class="filters-reset" href="{$links['nofilter']}">&times;</a></p>
HTML;
			}
			else
			{
				$caption=T::$lang['apply-filter'];
				$nofilter='';
			}

			$filters['task']=Html::Input('fi[task]',$filters['task'],['placeholder'=>static::$lang['filter-by-task'],'class'=>'form-control','id'=>'fi-task']);
			$filters['title']=Html::Input('fi[title]',$filters['title'],['placeholder'=>T::$lang['filter-by-name'],'class'=>'form-control','id'=>'fi-title']);
			$filters=<<<HTML
					<!-- Фильтры -->
					<div class="filters">
						{$nofilter}
						<div class="dropdown">
							<button class="btn btn-default" data-toggle="dropdown">{$caption} <i class="caret"></i></button>
							<form class="dropdown-menu dropform pull-right" method="post">
								<div class="form-group">
									<label for="fi-title">{$t_lang['name']}</label>
									{$filters['title']}
								</div>
								<div class="form-group">
									<label for="fi-task">{$c_lang['task']}</label>
									{$filters['task']}
								</div>
								<button type="submit" class="btn btn-primary">{$t_lang['apply']}</button>
							</form>
						</div>
					</div>
HTML;
		}
		else
			$filters='';

		$links=$GLOBALS['Eleanor']->module['links'];
		$create=$links['create'] ? '<a href="'.$links['create'].'" class="btn btn-default iframe">'.static::$lang['create'].'</a>' : '';

		if($create or $items)
			$create.=T::$T->IframeLink();

		return<<<HTML
	<div class="list-top">
		{$filters}
		{$create}
	</div>
	{$Items}
HTML;
	}

	/** Страница создания/редактирования задачи
	 * @param int $id ID редактируемой задачи, если равно 0, значит страница добавляется
	 * @param array $values Значения полей формы:
	 *  [array title] Название
	 *  [string task] Обработчик
	 *  [bool now] Флаг запуска задачи сразу после сохранения формы
	 *  [string run_month] Месяц запуска: * - любой месяц; +N - каждые N месяцев; 1,3,5-7 - конкретные месяцы
	 *  [string run_day] День запуска: * - любой день; +N - каждые N дней; 1,3,5-7 - конкретные дни месяца; w1,3,5-7 - конкретные дни месяца
	 *  [string run_hour] Часы запуска: * - любой час; +N - каждые N часов; 1,3,5-7 - конкретные часы
	 *  [string run_minute] Минуты запуска: * - любая минута; +N - каждые N минут; 1,3,5-7 - конкретные минуты
	 *  [string run_second] Секунды запуска: * - любая секунда; +N - каждые N секунд; 1,3,5-7 - конкретные секунды
	 *  [int status] Статус: 1 - акивировано, 0 - деактивировано
	 * @param array $handlers Перечень возможных обработчиков задачи
	 * @param array $errors Ошибки формы
	 * @param string $back URL возврата
	 * @param array $links Перечень ссылок:
	 *  [string|null delete] Ссылка на удаление
	 * @return string */
	public static function CreateEdit($id,$values,$handlers,$errors,$back,$links)
	{
		array_push($GLOBALS['scripts'],
			T::$http['static'].'js/'.Language::$main.'.js',
			T::$T->default['js'].'tasks-'.Language::$main.'.js');

		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		$c_lang=static::$lang;
		$t_lang=T::$lang;

		static::Menu($id ? 'edit' : 'create');

		if(Eleanor::$vars['multilang'])
		{
			$input=[];

			foreach(Eleanor::$langs as $lng=>$v)
				$input['title'][$lng]=Html::Input("title[{$lng}]",$values['title'][$lng],
					['class'=>'form-control need-tabindex input-lg','id'=>'title-'.$lng,'placeholder'=>static::$lang['title-plh']]);

			$input['title']=T::$T->LangEdit($input['title'],'title');
		}
		else
			$input=[
				'title'=>Html::Input('title',$values['title'],['id'=>'title','class'=>'form-control need-tabindex input-lg','placeholder'=>static::$lang['title-plh']]),
			];

		#Обработчики
		$handler='';
		foreach($handlers as $v)
			$handler.=Html::Option($v,false,$v==$values['task']);
		#Обработчики

		$input+=[
			'now'=>Html::Check('now',$values['now'],['class'=>'need-tabindex']),
			'handler'=>Html::Select('handler',$handler,['class'=>'form-control need-tabindex pim','id'=>'handler']),
			'run_hour'=>['select'=>Html::Input('run_hour',$values['run_hour'],['type'=>'hidden','id'=>'run-hour','class'=>'pim'])
				.Html::Select('',
						Html::Option(static::$lang['hour-*'],'*').
						Html::Option('','+').
						Html::Option(static::$lang['def-hour'],''),
					['class'=>'form-control need-tabindex','id'=>'run-hour-select']
				),
				'input'=>Html::Input('',1,['type'=>'number','class'=>'form-control need-tabindex task','id'=>'run-hour-num','min'=>1,'max'=>100,'required'=>true])
					.Html::Input('','',['class'=>'form-control need-tabindex','id'=>'run-hour-text','placeholder'=>static::$lang['example-plh']])],
			'run_minute'=>['select'=>Html::Input('run_minute',$values['run_minute'],['type'=>'hidden','id'=>'run-minute','class'=>'pim'])
				.Html::Select('',
						Html::Option(static::$lang['minute-*'],'*').
						Html::Option('','+').
						Html::Option(static::$lang['def-minute'],''),
					['class'=>'form-control need-tabindex','id'=>'run-minute-select']
				),
				'input'=>Html::Input('',1,['type'=>'number','class'=>'form-control need-tabindex task','id'=>'run-minute-num','min'=>1,'max'=>100,'required'=>true])
					.Html::Input('','',['class'=>'form-control need-tabindex','id'=>'run-minute-text','placeholder'=>static::$lang['example-plh']])],
			'run_second'=>['select'=>Html::Input('run_second',$values['run_second'],['type'=>'hidden','id'=>'run-second','class'=>'pim'])
				.Html::Select('',
						Html::Option(static::$lang['second-*'],'*').
						Html::Option('','+').
						Html::Option(static::$lang['def-second'],''),
					['class'=>'form-control need-tabindex','id'=>'run-second-select']
				),
				'input'=>Html::Input('',1,['type'=>'number','class'=>'form-control need-tabindex task','id'=>'run-second-num','min'=>1,'max'=>100,'required'=>true])
					.Html::Input('','',['class'=>'form-control need-tabindex','id'=>'run-second-text','placeholder'=>static::$lang['example-plh']])],
			'run_month'=>['select'=>Html::Input('run_month',$values['run_month'],['type'=>'hidden','id'=>'run-month','class'=>'pim'])
				.Html::Select('',
						Html::Option(static::$lang['month-*'],'*').
						Html::Option('','+').
						Html::Option(static::$lang['def-month'],''),
					['class'=>'form-control need-tabindex','id'=>'run-month-select']
				),
				'input'=>Html::Input('',1,['type'=>'number','class'=>'form-control need-tabindex task','id'=>'run-month-num','min'=>1,'max'=>100,'required'=>true])
					.Html::Input('','',['class'=>'form-control need-tabindex','id'=>'run-month-text','placeholder'=>static::$lang['example-plh']])],
			'run_day'=>['select'=>Html::Input('run_day',$values['run_day'],['type'=>'hidden','id'=>'run-day','class'=>'pim'])
				.Html::Select('',
						Html::Option(static::$lang['day-*'],'*').
						Html::Option('','+').
						Html::Option(static::$lang['def-day'],''),
					['class'=>'form-control need-tabindex','id'=>'run-day-select']
				),
				'input'=>Html::Input('',1,['type'=>'number','class'=>'form-control need-tabindex task','id'=>'run-day-num','min'=>1,'max'=>100,'required'=>true])
					.Html::Input('','',['class'=>'form-control need-tabindex','id'=>'run-day-text','placeholder'=>static::$lang['example-plh']])],
		];

		#Pim поля, которые сабмитятся только если изменились
		$pim=$errors || $_SERVER['REQUEST_METHOD']=='POST' ? '' : 'Pim();';

		#Url возврата
		$back=$back ? Html::Input('back',$back,['type'=>'hidden']) : '';

		#Errors
		$er_title=$er_run=$er_def=$er_hour=$er_min=$er_sec=$er_mon=$er_day='';

		foreach($errors as $type=>$error)
		{
			if(is_int($type) and is_string($error))
			{
				$type=$error;
				if(isset(static::$lang[$error]))
					$error=static::$lang[$error];
			}

			$error=T::$T->Alert($error,'danger',true);;

			switch($type)
			{
				case'EMPTY_TITLE':
					$er_title=$error;
				break;
				case'NO_NEXT_RUN':
					$er_run=$error;
				break;
				case'EMPTY_HOUR':
					$er_hour=$error;
				break;
				case'EMPTY_MINUTE':
					$er_min=$error;
				break;
				case'EMPTY_SECOND':
					$er_sec=$error;
				break;
				case'EMPTY_MONTH':
					$er_mon=$error;
				break;
				case'EMPTY_DAY':
					$er_day=$error;
				break;
				default:
					$er_def=$error;
			}
		}

		if($errors and !$er_def)
			$er_def=T::$T->Alert(static::$lang['form-errors'],'warning',true);
		#/Errors

		#Кнопки
		$success=$id ? static::$lang['save'] : static::$lang['create'];

		$delete=$links['delete'] ? '<button type="button" onclick="window.location=\''.$links['delete']
			.'\'" class="ibtn ib-delete need-tabindex"><i class="ico-del"></i><span class="thd">'
			.T::$lang['delete'].'</span></button>' : '';

		$stopts=Html::Option(T::$lang['active'],1,$values['status']==1)
			.Html::Option(T::$lang['inactive'],0,$values['status']==0);
		#/Кнопки

		return<<<HTML
		{$er_def}
			<form method="post">
				<div id="mainbar">
					<div class="block">
						{$er_title}
						{$input['title']}
						<br />
						<div class="form-group">
							<label for="handler">{$c_lang['handler']}</label>
							<div>{$input['handler']}</div>
						</div>
						{$er_run}{$er_hour}
						<div class="form-group">
							<label for="run-hour-select">{$c_lang['run-hours']}</label>
							<div class="row">
								<div class="col-xs-6">
									{$input['run_hour']['select']}
								</div>
								<div class="col-xs-6">
									{$input['run_hour']['input']}
								</div>
							</div>
						</div>
						{$er_min}
						<div class="form-group">
							<label for="run-minute-select">{$c_lang['run-minutes']}</label>
							<div class="row">
								<div class="col-xs-6">
									{$input['run_minute']['select']}
								</div>
								<div class="col-xs-6">
									{$input['run_minute']['input']}
								</div>
							</div>
						</div>
						{$er_sec}
						<div class="form-group">
							<label for="run-second-select">{$c_lang['run-seconds']}</label>
							<div class="row">
								<div class="col-xs-6">
									{$input['run_second']['select']}
								</div>
								<div class="col-xs-6">
									{$input['run_second']['input']}
								</div>
							</div>
						</div>
						{$er_mon}
						<div class="form-group">
							<label for="run-month-select">{$c_lang['run-months']}</label>
							<div class="row">
								<div class="col-xs-6">
									{$input['run_month']['select']}
								</div>
								<div class="col-xs-6">
									{$input['run_month']['input']}
								</div>
							</div>
						</div>
						{$er_day}
						<div class="form-group">
							<label for="run-day-select">{$c_lang['run-days']}</label>
							<div class="row">
								<div class="col-xs-6">
									{$input['run_day']['select']}
								</div>
								<div class="col-xs-6">
									{$input['run_day']['input']}
								</div>
							</div>
						</div>
					</div>
				</div>
				<div id="rightbar">
					<div class="block-t expand">
						<p class="btl" data-toggle="collapse" data-target="#opts">{$c_lang['options']}</p>
						<div id="opts" class="collapse in">
							<div class="bcont">
								<div class="checkbox"><label>{$input['now']} {$c_lang['now']}</label></div>
							</div>
						</div>
					</div>
				</div>
				<!-- FootLine -->
				<div class="submit-pane">
					{$back}<button type="submit" class="btn btn-success need-tabindex"><b>{$success}</b></button>
					<select name="status" class="form-control pim">{$stopts}</select>{$delete}
				</div>
				<!-- FootLine [E] -->
			</form>
		<script>$(function(){
$("#run-hour,#run-minute,#run-second,#run-month,#run-day").each(function(){
	var th=$(this),
		num=1,
		val=th.val(),
		select=th.next();

	if(val=="*")
		select.val("*").parent().next().children().hide();
	else if(val.indexOf("+")==0)
	{
		num=parseInt(val.substr(1));

		if(isNaN(num))
			num=1;

		select.val("+").parent().next().find(":first").val(num).next().hide();
	}
	else
		select.val("").parent().next().find(":first").hide().next().val(val);

	select.find("[value='+']").text( CORE.Lang(th.prop("id")+"-num",[num]) ).end();
})

//Select
.next().change(function(){
	var select=$(this),
		hidden=select.prev(),
		num=select.parent().next().find(":first"),
		input=num.next();

	switch(select.val())
	{
		case"*":
			select.parent().next().children().hide();
			hidden.val("*");
		break;
		case"+":
			num.show();
			input.hide();
			hidden.val("+"+num.val());
		break;
		default:
			num.hide();
			input.show();
			hidden.val(input.val());
	}
})

//Num
.parent().next().find(":first").change(function(){
	var num=$(this),
		select=num.parent().prev().find("select"),
		hidden=select.prev();

	 select.find(":selected").text( CORE.Lang(num.prop("id"),[num.val()]) );

	hidden.val(select.val()+num.val());
})

//Input
.next().change(function(){
	var input=$(this),
		select=input.parent().prev().find("select"),
		hidden=select.prev();

	hidden.val(select.val()+input.val());
});
{$pim} })</script>
HTML;
	}

	/** Страница удаления задачи
	 * @param array $task Данные удаляемой задачи
	 *  [string title] Название
	 * @param string $back URL возврата
	 * @return string */
	public static function Delete($task,$back)
	{
		#SpeedBar
		T::$data['speedbar']=[
			[Eleanor::$services['admin']['file'].'?section=management',Eleanor::$Language['main']['management']],
			[$GLOBALS['Eleanor']->module['links']['list'], $GLOBALS['Eleanor']->module['title']],
			end($GLOBALS['title'])
		];

		static::Menu();
		return Eleanor::$Template->Confirm(sprintf(static::$lang['delete-text%'],$task['title']),$back);
	}
}
Tasks::$lang=Eleanor::$Language->Load(__DIR__.'/../translation/tasks-*.php',false);

return Tasks::class;