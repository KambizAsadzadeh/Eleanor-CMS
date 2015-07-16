<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use Eleanor\Classes\Html;

defined('CMS\STARTED')||die;

return[
	'form'=>function($args=[]){
		if(!is_array($args))
			$args=['action'=>$args];

		$args+=['method'=>'post','id'=>'items-form'];

		return'<form'.Html::TagParams($args).'>';
	},

	'empty_head'=>function($n){
		$ths=str_repeat('<th></th>',$n);
		return<<<HTML
<table class="table table-list"><thead class="empty"><tr>{$ths}</tr></thead>
HTML;
	},
	'head'=>function(){
		$GLOBALS['head']['table-list']='<script>$(TableList)</script>';
		$args=func_get_args();

		if(isset($args[0]) and is_array($args[0]))
		{
			if(!isset($args[0]['table-extra']) or !is_array($args[0]['table-extra']))
				$args[0]['table-extra']=[];

			if(!isset($args[0]['tr-extra']) or !is_array($args[0]['tr-extra']))
				$args[0]['tr-extra']=[];

			$args[0]['table-extra']+=['class'=>'table table-list'];

			$result='<table'.Html::TagParams($args[0]['table-extra']).'>';

			if($args)
				$result.='<thead><tr'.Html::TagParams($args[0]['tr-extra']).'>';
		}
		else
		{
			$result='<table class="table table-list">';

			if($args)
				$result.='<thead><tr>';
		}

		foreach($args as $arg)
		{
			if($arg===false)
				continue;

			$attr=[];
			$content='';

			if(is_array($arg))
			{
				foreach($arg as $name=>$param) if($param!==false) switch((string)$name)
				{#Числовые значения не ставить! Иначе не обработается default, при $arg=['title','colspan'=>'2'],
					case'0':#Содержимое
						$content=trim($param);
						break;
					case'1':#Текущая сортировка asc или desc
					case'sort':
						$content.=' <span class="caret"></span>';
						if(strcasecmp($param, 'asc')==0)
							if(isset($arg['href-extra']['class']))
								$arg['href-extra']['class'].=' dropup';
							else
								$arg['href-extra']['class']='dropup';
						break;
					case'2':#Ссылка на сортировку
					case'href':
						if(!isset($arg['href-extra']) or !is_array($arg['href-extra']))
							$arg['href-extra']=[];
						$arg['href-extra']+=['href'=>$param];
						$content='<a'.Html::TagParams($arg['href-extra']).'>'.$content.'</a>';
						break;
					case'href-extra':
					case'tr-extra':
					case'table-extra':
						break;
					case'3':#Класс
						$name='class';
					default:
						$attr[$name]=$param;
				}
			}
			else
				$content=$arg;

			$result.='<th'.Html::TagParams($attr).'>'.$content.'</th>';
		}

		return$result.'</tr></thead>';
	},

	'item'=>function()
	{
		$args=func_get_args();

		if(isset($args[0]) and is_array($args[0]))
		{
			if(!isset($args[0]['tr-extra']) or !is_array($args[0]['tr-extra']))
				$args[0]['tr-extra']=[];

			$result='<tr'.Html::TagParams($args[0]['tr-extra']).'>';
		}
		else
			$result='<tr>';

		foreach($args as $arg)
		{
			if($arg===false)
				continue;

			$attribs=[];
			$content='';

			if(is_array($arg))
			{
				foreach($arg as $name=>$param) if($param!==false) switch((string)$name)
				{#Числовые значения не ставить!
					case'0':#Содержимое
						$content=$param;
						break;
					case'href':
					case'2':#Ссылка
						if(!isset($arg['href-extra']) or !is_array($arg['href-extra']))
							$arg['href-extra']=[];

						$arg['href-extra']+=['href'=>$param];
						$content='<a'.Html::TagParams($arg['href-extra']).'>'.$content.'</a>';

						break;
					case'href-extra':
					case'tr-extra':
					break;
					case'1':#Класс
						$name='class';
					default:
						$attribs[$name]=$param;
				}
			}
			else
				$content=$arg;

			$result.='<td'.Html::TagParams($attribs).'>'.$content.'</td>';
		}

		return$result.'</tr>';
	},

	'status'=>function($status='ok',$title='',$a=''){
		$extra='';
		switch($status)
		{
			case'trash':
			case'0':
				$extra.=' data-status="trash"';

				if(!$title)
					$title='Снято с публикации';
			break;
			case'wait':
			case'-1':
				$extra.=' data-status="wait"';

				if(!$title)
					$title='На проверке';
			break;
			default:
				$extra.=' data-status="published"';

				if(!$title)
					$title='Опубликовано';
		}

		return[($a ? '<a href="'.$a.'"' : '<i').' title="'.$title.'" class="istatus"'.$extra.'>'.$title.($a ? '</a>' : '</i>'),'class'=>'col_status'];
	},

	/** Основной элемент таблицы
	 * @param string $title
	 * @param array $links Формат ссылок [href,title,is_main (является ли главной)]
	 * @param string|null $thumb */
	'main'=>function($title,array$links,$thumb=false)
	{
		$menu=$href=$extra='';

		foreach($links as $v)
		{
			if(!$v)
				continue;

			if(!is_array($v))
			{
				$href=$v;
				continue;
			}

			if(!$menu and !$href)
				$href=$v[0];
			elseif(!empty($v[2]))
			{
				$href=$v[0];
				$extra=$v[2];
			}

			if(!isset($v[1]))
				continue;

			$extra_=isset($v['extra']) ? ' '.Html::TagParams($v['extra']) : '';
			$menu.=<<<HTML
<li><a href="{$v[0]}"{$extra_}>{$v[1]}</a></li>
HTML;
		}

		return[($thumb
					? <<<HTML
<a class="zoom-thumb" href="{$href}"{$extra}><span class="thumb" style="background-image: url({$thumb});"></span></a>
HTML
					: ItemAvatar($title)
				).<<<HTML
<div class="col_item-text"><h4><a href="{$href}"{$extra}>{$title}</a></h4><ul class="inline-menu">{$menu}</ul></div>
HTML
				,'class'=>'col_item'];
	},

	'end'=>'</tbody></table>',
	'subitems'=>'<script>$(function(){ InitTableSubitems({0}) })</script>',
	'foot'=>function($itemsopt,$cnt,$pp,$page,$links,$ajaxapge=null){
		$event=$itemsopt ? <<<HTML
					<div class="pull-right form-inline">
						<select class="form-control" id="items-event" name="event">
							{$itemsopt}
						</select>
						<button class="btn btn-default" type="submit" id="items-submit"><b>Ok</b></button>
					</div>
HTML
: '';
		$pages=T::$T->Pagination($cnt,$pp,$page,$links['pagination'],$ajaxapge);

		#PerPage
		$perpage='';
		$next=false;

		foreach([10,30,50,100,500] as $v)
			if($v<=$cnt or $next)
			{
				$next=$v<=$cnt;
				$perpage.=$v==$pp ? ' '.$v.' |' : ' <a href="'.$links[ 'pp' ]($v).'">'.$v.'</a> |';
			}

		$perpage=$perpage ? '<div class="list-num-view">'.sprintf(T::$lang['pp%'],rtrim($perpage,'|')).'</div>' : '';
		#/PerPage

		return<<<HTML
				<div class="list-foot">{$event}{$pages}{$perpage}</div>
HTML;

	},
	'endform'=>'</form>',
	'checks'=>function(){
		$GLOBALS['scripts'][]=T::$http['static'].'js/checkboxes.js';
		return'<script>$(ItemsForm)</script>';
	}
];