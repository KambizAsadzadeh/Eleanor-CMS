<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблоны отображения опроса, готового опрашивать людей
*/
class TplVoting
{
	public static
		$lang;

	public
		$type='',
		$jparams='';

	protected
		$voting,
		$qs,
		$status,
		$request;

	public function __construct($voting,$qs,$status,$request)
	{
		$this->voting=$voting;
		$this->qs=$qs;
		$this->status=$status;
		$this->request=$request;
	}

	public function __toString()
	{
		$q=static::Voting($this->voting,$this->qs,$this->status,$this->type);
		if($this->status=='voted')
			return$q;
		$GLOBALS['scripts'][]='js/voting.js';
		$u=uniqid('v');
		$this->request['votingtpl']=$this->type;
		return'<form id="'.$u.'">'.$q.'</form><script>
$(function(){
	new Voting({
		form:"#'.$u.'",
		similar:".voting-'.$this->voting['id'].'",
		type:"'.$this->status.'",
		request:'.Eleanor::JsVars($this->request,false,true).',
		qcnt:'.count($this->qs).$this->jparams.'
	});
})</script>';
	}

	/*
		Вывод опроса
		$voting - массив параметров опроса, ключи:
			id - идентификатор опроса в БД
			begin - дата начала опроса, либо 0000-00-00, если опрос работал с момента добавления
			end - дата окончания опроса
			onlyusers - флаг опроса только для пользователей (не гостей)
			againdays - количество дней, по истечению которых можно снова голосовать
			votes - число опрошеных
		$qs - массив вопросов опроса. Формат: id=>array(), ключи внутреннего массива:
			title - название вопроса
			variants - миссив вариантов ответа, формат: id=>текст варианта
			answers - массив количества голосов за каждый вариант, формат: id=>число голосов
			multiple - флаг возможности множественного ответа на вопрос
			maxans - в случае возможности множественного ответа, этот ключ содержит максимальное число одновременно выбранных ответов
		$status - статус опроса. Возможны следующие значения:
			false (bool) - можно голосовать
			voted - уже проголосовали
			refused - голос не защитан
			confirmed - голос защитан
			guest - голосовать нельзя, потому что голосование только для пользователей
			wait - ожидает открытия
			finished - голосование завершено
	*/
	public static function Voting($voting,$qs,$status,$type=false)
	{
		if(!$type)
			$type=isset($_POST['votingtpl']) ? (string)$_POST['votingtpl'] : '';

		#Switch
		$r='<p class="vtitle">'.($type=='block' ? '<a href="#"></a><br />' : '').'<span>'.sprintf(static::$lang['nums'],$voting['votes']).'</span>'.(!$status && (int)$voting['end']>0 ? '<br /><span>'.sprintf(static::$lang['tlimit'],Eleanor::$Language->Date($voting['end'],'fdt')).'</span>' : '').'</p>';
		foreach($qs as $k=>&$v)
		{
			$qid=$v['multiple'] && !$status ? uniqid() : false;

			$sum=$v['multiple'] ? max($v['answers']) : array_sum($v['answers']);
			$div=$sum==0 ? 1 : $sum;
			$r.='<h4 class="vs-title">'.$v['title'].'</h4><ul class="vs-cont"'.($qid ? ' id="'.$qid.'"' : '').'>';
			foreach($v['variants'] as $vk=>&$vv)
			{
				$percent=round($v['answers'][$vk]/$div*100,1);
				$r.='<li><label>';
				if(!$status)
					$r.=($qid ? Eleanor::Check($k.'[]',false,array('value'=>$vk)) : Eleanor::Radio($k,$vk,false)).' ';
				$r.=$vv.' - '.$percent.'% ('.$v['answers'][$vk].')</label>';
				$r.=($percent ? '<p class="vline"><i style="width:'.$percent.'%">'.$percent.'%</i></p>' : '').'</li>';
			}
			$r.='</ul>'
				.($qid ? '<script>new Voting.ChecksLimit("#'.$qid.'",'.$v['maxans'].')</script>' : '');
		}
		$r.='<div class="vs-buttons">';
		switch($status)
		{
			case'guest':
			case'voted':
				$r.='<span class="vs-result">'.static::$lang[$status].'</span>';
			break;
			case'finished':
				$r.='<span class="vs-result">'.sprintf(static::$lang['finished'],Eleanor::$Language->Date($voting['end'],'fdt')).'</span>';
			break;
			case'wait':
				$r.='<span class="vs-result">'.sprintf(static::$lang['wait'],Eleanor::$Language->Date($voting['begin'],'fdt')).'</span>';
			break;
			case'confirmed':
				$r.='<span class="vs-result" style="color:green;">'.static::$lang['vc'].'</span>';
			break;
			case'rejected':
				$r.='<span class="vs-result" style="color:red;">'.static::$lang['vr'].'</span>';
			break;
			default:
				$r.='<button class="wh-btn" type="submit">'.static::$lang['vote'].'</button>';
		}
		$r.='</div>';
		#[E]Switch
		return$r;
	}

	/*
		Вывод формы опроса, включающей и сам опрос
		Описание переменных $voting,$qs,$status смотрите в методе Voting
		$request - параметры AJAX запроса
	*/
	public static function VotingCover($voting,$qs,$status,$request)
	{
		return new self($voting,$qs,$status,$request);
	}
}
TplVoting::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/voting-*.php',false);