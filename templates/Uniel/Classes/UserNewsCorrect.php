<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �� ��������� ��� ������������� ������ "�������". �������������� ��������.
*/
class TplUserNewsCorrect
{	/*
		��������� �����. ������ ������ Cron
	*/	protected static function TopMenu($tit=false)
	{		$GLOBALS['jscripts'][]='js/module_publications.js';
		#Cron
		if(isset(Eleanor::$services['cron']))
		{
			$task=Eleanor::$Cache->Get($GLOBALS['Eleanor']->module['config']['n'].'_nextrun');
			$t=time();
			$task=$task===false && $task<=$t ? '<img src="'.Eleanor::$services['cron']['file'].'?'.Url::Query(array('module'=>$GLOBALS['Eleanor']->module['name'],'language'=>Language::$main==LANGUAGE ? false : Language::$main,'rand'=>$t)).'" style="width:1px;height1px;" />' : '';
		}
		else
			$task='';
		#[E] Cron

		if(!empty($GLOBALS['Eleanor']->module['general']))
			return$task;		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];		return Eleanor::$Template->Menu(array(
			'menu'=>array(
				array($GLOBALS['Eleanor']->Url->Prefix(),$lang['all']),
				$GLOBALS['Eleanor']->Categories->dump ? array($GLOBALS['Eleanor']->Url->Construct(array('do'=>'categories'),true,''),$lang['categs']) : false,
				$GLOBALS['Eleanor']->module['tags'] ? array($GLOBALS['Eleanor']->Url->Construct(array('do'=>'tags'),true,''),$lang['tags']) : false,
				array($GLOBALS['Eleanor']->Url->Construct(array('do'=>'search'),true,''),$lang['search'],'addon'=>array('rel'=>'search')),
				Eleanor::$vars['publ_add'] ? array($GLOBALS['Eleanor']->Url->Construct(array('do'=>'add'),true,''),$lang['add']) : false,
				Eleanor::$vars['publ_add'] ? array($GLOBALS['Eleanor']->Url->Construct(array('do'=>'my'),true,''),$lang['my']) : false,
			),
			'title'=>($tit ? $tit : $lang['n']).$task,
		));
	}

	/*
		�������� ����������/�������������� �������
		$id ������������� ������������� �������, ���� $id==0 ������ ������� �����������
		$values ������ �������� �����
			����� �����:
			author - ��� ������ ������� (������ ��� �����, ���� �� ����������)
			status - ������ ���������� ������� (������ ��� ������������, ���� �� ����������): 0 - �� �������, 1 - �������
			cats - ������ ���������
			enddate - ���������� ������� �������
			show_detail - ���� ��������� ������ ������ "���������" ��� ���������� ������������ �������
			show_sokr - ���� ��������� ����������� ������ ����������� ������� ��� ��������� ���������

			�������� �����:
			title - ��������� �������
			announcement - ����� �������
			text - ����� �������
			uri - URI �������

			������ �������� �����:
			tags - ���� �������

			����������� �����:
			_onelang - ���� ����������� ������� ��� ���������� ���������������
			_maincat - ������������� �������� ��������� �������
		$errors - ������ ������
		$uploader - ��������� ����������
		$voting - ��������� ���������
		$bypost - ������� ����, ��� ������ ����� ����� �� POST �������
		$hasdraft - ������� ������� ���������
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
		$captcha - captcha ��� �������� �������
	*/
	public static function AddEdit($id,$values,$errors,$uploader,$voting,$bypost,$hasdraft,$back,$links,$captcha)
	{		$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js';
		$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		if(Eleanor::$vars['multilang'])
		{			$mchecks=$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{				$mchecks[$k]=(!$id or !empty($values['title'][$k]) or !empty($values['announcement'][$k]) or !empty($values['text'][$k]) or !empty($values['uri'][$k]) or !empty($values['meta_title'][$k]) or !empty($values['meta_descr'][$k]));
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1,'id'=>'title-'.$k));
				$ml['announcement'][$k]=$GLOBALS['Eleanor']->Editor->Area('announcement['.$k.']',Eleanor::FilterLangValues($values['announcement'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10)));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15)));
				$ml['uri'][$k]=Eleanor::Edit('uri['.$k.']',Eleanor::FilterLangValues($values['uri'],$k),array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title-'.$k.'\').val())','tabindex'=>2));

				$ml['tags'][$k]=Eleanor::Edit('tags['.$k.']',Eleanor::FilterLangValues($values['tags'],$k),array('tabindex'=>5));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('id'=>'title','tabindex'=>1)),
				'announcement'=>$GLOBALS['Eleanor']->Editor->Area('announcement',$values['announcement'],array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10))),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15))),
				'uri'=>Eleanor::Edit('uri',$values['uri'],array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title\').val())','tabindex'=>2)),

				'tags'=>Eleanor::Edit('tags',Eleanor::FilterLangValues($values['tags']),array('tabindex'=>5)),
			);

		$Lst=Eleanor::LoadListTemplate('table-form')->begin();

		if(isset($values['author']))
			$Lst->item($lang['author'],Eleanor::Edit('author',$values['author']));
		$Lst->item($lang['title'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item('URI',Eleanor::$Template->LangEdit($ml['uri'],null));
		if($GLOBALS['Eleanor']->Categories->dump)
			$Lst->item($lang['categs'],Eleanor::Items('cats',$GLOBALS['Eleanor']->Categories->GetOptions($values['cats']),10,array('id'=>'cs','tabindex'=>3)))
				->item($lang['maincat'],Eleanor::Select('_maincat',$GLOBALS['Eleanor']->Categories->GetOptions($values['_maincat']),array('id'=>'mc','tabindex'=>4)));
		$Lst->item(array($lang['tags'],Eleanor::$Template->LangEdit($ml['tags'],null),'descr'=>$lang['ftags_']))
			->item(array($lang['announcement'],Eleanor::$Template->LangEdit($ml['announcement'],null),'descr'=>$lang['announcement_']))
			->item($lang['text'],Eleanor::$Template->LangEdit($ml['text'],null))
			->item(array($lang['show_sokr'],Eleanor::Check('show_sokr',$values['show_sokr'],array('tabindex'=>8)),'descr'=>$lang['show_sokr_']))
			->item(array($lang['show_detail'],Eleanor::Check('show_detail',$values['show_detail'],array('tabindex'=>9)),'descr'=>$lang['show_detail_']))
			->item(array($lang['enddate'],Dates::Calendar('enddate',$values['enddate'],true,array('tabindex'=>10)),'descr'=>$lang['enddate_']));
		if(Eleanor::$vars['multilang'])
			$Lst->item($lang['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$mchecks,null,11));
		if(isset($values['status']))
			$Lst->item($lang['status'],Eleanor::Select('status',Eleanor::Option($lang['blocked'],0,$values['status']==0).Eleanor::Option($lang['active'],1,$values['status']==1),array('tabindex'=>12)));
		if($captcha)
			$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>13)),'descr'=>$lang['captcha_']));
		$general=(string)$Lst->end();

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		return static::TopMenu(reset($GLOBALS['title']))
			.($errors ? Eleanor::$Template->Message($errors,'error') : '')
			.$Lst->form()
			->tabs(
				array($lang['general'],$general),
				array($lang['voting'],$voting)
			)
			->submitline((string)$uploader)
			->submitline(
				$back.Eleanor::Button('OK','submit',array('tabindex'=>14))
				.($id ? ' '.Eleanor::Button(Eleanor::$Language['tpl']['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
				.($links['draft'] ? Eleanor::Control('_draft','hidden',$id).Eleanor::$Template->DraftButton($links['draft'],1).($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$lang['nodraft'].'</a>' : '') : '')
			)
			->endform()
			.'<script type="text/javascript">//<![CDATA[
$(function(){	$("#cs").change(function(){		var cs=this;		$("#mc option").each(function(i){			if($("option:eq("+i+")",cs).prop("selected"))
				$(this).prop("disabled",false);
			else
				$(this).prop({disabled:true,selected:false});		});	}).change();
	$("input[name^=\"tags\"]").each(function(){		var m=$(this).prop("name").match(/tags\[([a-z]+)\]/),
			p={
				module:"'.$GLOBALS['Eleanor']->module['name'].'",
				event:"tags",
				lang:(m && !$("input[name=\"_onelang\"]").prop("checked")) ? m[1] : ""
			},
			a=$(this).autocomplete({
				serviceUrl:"'.Eleanor::$services['ajax']['file'].'",
				minChars:2,
				delimiter:/,\s*/,
				params:p
			});
		$("input[name=\"_onelang\"]").change(function(){			p.lang=(m && !$(this).prop("checked")) ? m[1] : "";
			a.setOptions({params:p})		});
	});})//]]></script>';	}

	/*
		�������� � ����������� �� �������� ��������/���������� �������
		$back - URL ��������
		$url - ������ �� ����������������� ��� ����������� �������
		$edited - ���� ��������������, ���� false - ������� �����������
		$status - ���� ���������������� �������
		$title - �������� �������
		$mod - ���� ����, ��� ������������/��������� ������� �������� ���������
	*/
	public static function AddEditComplete($back,$url,$edited,$status,$title,$mod)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];		if($status)
		{			if($mod)
				$text=$edited ? $lang['modedit'] : $lang['modadd'];
			else
				$text=$edited ? $lang['nomodedit'] : $lang['nomodadd'];		}
		else
			$text=$edited ? $lang['statusedit'] : $lang['statusadd'];
		return static::TopMenu(reset($GLOBALS['title']))->Message(sprintf($text,$title).'<br /><br />'.sprintf($lang['goto'],'<a href="'.$url.'">'.$title.'</a>'.($back ? ' | <a href="'.$back.'">'.$lang['pagewg'].'</a>' : '')),'info');	}

	/*
		�������� �������� �������
		$t - �����-������������� ��������
		$back - URL ��������
	*/
	public static function Delete($t,$back)
	{		return static::TopMenu()->Confirm($t,$back);	}

	/*
		�������� ���������� �������� �������
		$a - ������ � ������� ��������� �������. �����:
			title - �������� �������
		$back - URL ��������
	*/
	public static function DelSuccess($a,$back)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];		$u=$back ? $back : $GLOBALS['Eleanor']->Url->Prefix();		return static::TopMenu(reset($GLOBALS['title']))
			->RedirectScreen($u,30)
			->Message(sprintf($lang['deleting'],$a['title']).($back ? '<br />'.sprintf($lang['goto'],'<a href="'.$back.'">'.$lang['pagewg'].'</a>') : ''),'info');	}
}