<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ������� ��������
*/
class TPLSpam
{	public static
		$lang;	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],Eleanor::$Language['spam']['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
		);
	}
	/*
		�������� ����������� ���� ��������
		$items - ������ ��������. ������: ID=>array(), ����� ����������� �������:
			title - �������� ��������
			sent - ���������� ������������ �����
			total - ����� ����� ��������� �����
			status - ������ �������� (���������, �� �����, �����������, ��������)
			statusdate - ���� ��������� �������
			_aedit - ������ �� �������������� ��������
			_adel - ������ �� �������� ��������

			� ����������� �� ��������, �������� �������������� �����.
			��� ������� runned (��������), �������� �����
			_astop - ������ �� ���������
			_apause - ������ �� �����

			��� ������� "�� �����" �������� ����
			_astop - ������ �� ���������
			+
			��� ��������� ��������, �������� ����:
			_arun - ������ �� ������

		$cnt - ���������� �������� �����
		$pp - ���������� �������� �� ��������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$links - �������� ����������� ������, ������ � �������:
			sort_innertitle - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_status - ������ �� ���������� ������ $items �� ������� ��� ������� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� �������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/	public static function ShowList($items,$cnt,$pp,$page,$qs,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(
				array($ltpl['name'],'sort'=>$qs['sort']=='innertitle' ? $qs['so'] : false,'href'=>$links['sort_innertitle']),
				array(static::$lang['condition'],250,'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{				switch($v['status'])
				{					case'runned':
						$status='<progress data-id="'.$v['id'].'" style="width:100%" value="'.$v['sent'].'" max="'.$v['total'].'" title="'.($pers=$v['total']>0 ? round($v['sent']/$v['total']*100,2) : 0).'%"><span>'.$pers.'</span>%</progress><br /><a href="'.$v['_astop'].'">'.static::$lang['stop'].'</a> <a href="'.$v['_apause'].'">'.static::$lang['pause'].'</a>';
					break;					case'paused':
						$status=static::$lang['paused'].' '.$v['statusdate'].'<br /><a href="'.$v['_astop'].'">'.static::$lang['stop'].'</a> <a href="'.$v['_arun'].'">'.static::$lang['run'].'</a>';
					break;
					case'finished':
						$status='<span style="color:green">'.static::$lang['finished'].' '.$v['statusdate'].'</span><br /><a href="'.$v['_arun'].'">'.static::$lang['+run'].'</a>';
					break;
					case'stopped':
					default:
						$status='<span style="color:red">'.static::$lang['stopped'].' '.$v['statusdate'].'</span><br /><a href="'.$v['_arun'].'">'.static::$lang['run'].'</a>';
				}
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['title'].'</a>',
					array($status,'center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty(static::$lang['nospam']);

		return Eleanor::$Template->Cover(
			'<form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end()
			.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['spp'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k'))
			.Eleanor::Button('Ok').'</div></form><script type="text/javascript">/*<![CDATA[*/$(function(){One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);new ProgressList("'.$GLOBALS['Eleanor']->module['name'].'","'.Eleanor::$services['cron']['file'].'");})//]]></script>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		������ ��������/�������������� ��������

		$id - ������������� ������������� ���������, ���� $id==0 ������ ��������� �����������
		$values - ������ �������� �����
			����� �����:
			per_run - ���������� ����� ������������ �� ���
			finame - ������ �� ����� ������������
			finamet - ������� ��� ������� �� ����� ������������
			figroup - ������ �� �������
			figroupt - ������� ��� ������� �� �������
			fiip - ������ �� IP �������
			firegisterb - ������ �� ����������� ��
			firegistera - ������ �� ����������� ��
			filastvisitb - ������ �� ���������� ������ ��
			filastvisita - ������ �� ���������� ������ ��
			figender - ������ �� ����
			fiemail - ������ �� e-mail
			fiids - ������ �� ID �������������
			deleteondone - ���� ������������ �������� ����� ����������
			status - ������ �������� (stopped, runned, finished, paused)
			_onelang - ���� �������������

			�������� �����:
			innertitle - ���������� �������� ��������
			title - ��������� ������
			text - ����� ������

		$runned - ������� ����, ��� �������� ������������� (������������ ��������)
		$uploader - ��������� ���������� ������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
		$errors - ������ ������
		$bypost - ������� ����, ��� ������ ����� ����� �� POST �������
		$back - URL ��������
	*/
	public static function AddEdit($id,$values,$runned,$uploader,$links,$errors,$bypost,$back)
	{		static::Menu($id ? '' : 'add');		$ltpl=Eleanor::$Language['tpl'];
		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['innertitle'][$k]=Eleanor::Input('innertitle['.$k.']',Eleanor::FilterLangValues($values['innertitle'],$k),array('tabindex'=>17));
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>18));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>19)));
			}
		}
		else
			$ml=array(
				'innertitle'=>Eleanor::Input('innertitle',$values['innertitle'],array('tabindex'=>17)),
				'title'=>Eleanor::Input('title',$values['title'],array('tabindex'=>18)),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>19))),
			);

		$Lst=Eleanor::LoadListTemplate('table-form');
		$extra=$runned ? array('disabled'=>true) : array();

		$uf=$Lst->begin()
			->item(static::$lang['groups'],Eleanor::Items('figroup',UserManager::GroupsOpts($values['figroup']),$extra+array('tabindex'=>1))
				.'<br /><label>'.Eleanor::Radio('figroupt','and',$values['figroupt']=='and',$extra+array('tabindex'=>2)).static::$lang['and'].'</label> <label>'.Eleanor::Radio('figroupt','or',$values['figroupt']=='or',$extra+array('tabindex'=>3)).static::$lang['or'].'</label>'
			)
			->item(static::$lang['username'],Eleanor::Select('finamet',Eleanor::Option(static::$lang['b'],'b','b'==$values['finamet']).Eleanor::Option(static::$lang['e'],'e','e'==$values['finamet']).Eleanor::Option(static::$lang['c'],'c','c'==$values['finamet']).Eleanor::Option(static::$lang['m'],'m','m'==$values['finamet']),$extra+array('tabindex'=>3,'style'=>'width:200px')).Eleanor::Input('finame',$values['finame'],$extra+array('tabindex'=>5,'style'=>'width:50%')))
			->item(static::$lang['register'],Dates::Calendar('firegisterb',$values['firegisterb'],true,$extra+array('style'=>'width:40%','tabindex'=>4)).' &mdash; '.Dates::Calendar('firegistera',$values['firegistera'],true,$extra+array('style'=>'width:40%','tabindex'=>5)))
			->item(static::$lang['last_visit'],Dates::Calendar('filastvisitb',$values['filastvisitb'],true,$extra+array('style'=>'width:40%','tabindex'=>6)).' &mdash; '.Dates::Calendar('filastvisita',$values['filastvisita'],true,$extra+array('style'=>'width:40%','tabindex'=>7)))
			->item('IP',Eleanor::Input('fiip',$values['fiip'],$extra+array('tabindex'=>8)))
			->item(static::$lang['gender'],Eleanor::Select('figender',Eleanor::Option(static::$lang['ni'],-2,$values['figender']==-2).Eleanor::Option(static::$lang['ns'],-1,$values['figender']==-1).Eleanor::Option(static::$lang['female'],0,$values['figender']==0).Eleanor::Option(static::$lang['male'],1,$values['figender']==1),$extra+array('tabindex'=>9)))
			->item('E-mail',Eleanor::Input('fiemail',$values['fiemail'],$extra+array('tabindex'=>10)))
			->item('IDs',Eleanor::Input('fiids',$values['fiids'],$extra+array('tabindex'=>11)))
			->button(Eleanor::Button(static::$lang['ts'],'button',array('onclick'=>'TryUsers()','tabindex'=>12)).' '.Eleanor::Button(static::$lang['hideres'],'button',array('id'=>'hide','style'=>'display:none','tabindex'=>13,'id'=>'hideres')))
			->end()
			.'<div id="tryusers" style="display:none"></div><script type="text/javascript">//<![CDATA[
function TryUsers(page)
{
	var request={direct:"admin",file:"spam",event:"search",page:page||0,pp:$("input[name=\"per_run\"]").val()};
	$("#newspam").find("[name^=\"fi\"]").each(function(){
		var obj=$(this),v=obj.val();
		if(v)
			request[obj.attr("name")]=v;
	})
	CORE.Ajax(
		request,
		function(result)
		{
			$("#tryusers").html(result).add("#hideres").show();
		}
	);
}
$("#hide").click(function(){	$("#tryusers").empty().add("#hideres").hide();});//]]></script>';

		$Lst->begin()
			->item(array(static::$lang['per_run'],'descr'=>static::$lang['per_run_'],Eleanor::Input('per_run',$values['per_run'],array('tabindex'=>14))))
			->item(array(static::$lang['delspam'],'descr'=>static::$lang['delspam_'],Eleanor::Check('deleteondone',$values['deleteondone'],array('tabindex'=>15))))
			->item(static::$lang['condition'],Eleanor::Select('status',Eleanor::Option(static::$lang['stopped'],'stopped',$values['status']=='stopped').Eleanor::Option(static::$lang['run'],'runned',$values['status']=='runned').Eleanor::Option(static::$lang['paused'],'paused',$values['status']=='paused').Eleanor::Option(static::$lang['finished'],'finished',$values['status']=='finished'),array('tabindex'=>16)))
			->item(static::$lang['innertitle'],Eleanor::$Template->LangEdit($ml['innertitle'],null))
			->item(static::$lang['topic'],Eleanor::$Template->LangEdit($ml['title'],null))
			->item(static::$lang['text'],Eleanor::$Template->LangEdit($ml['text'],null));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,20));

		$ss=(string)$Lst->button((string)$uploader)->end();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
					$v=static::$lang[$v];

		return Eleanor::$Template->Cover(
			($runned ? Eleanor::$Template->Message(static::$lang['runned'],'info') : '')
			.$Lst->form(array('id'=>'newspam'))
			->tabs(
				array(static::$lang['userfilter'],$uf,),
				array(static::$lang['ssetting'],$ss,)
			)
			->submitline($back.Eleanor::Button('OK','submit',array('tabindex'=>21)).($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>22,'onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform(),
			$errors,'error'
		);
	}

	/*
		������� �������. ����� ������� ������������� �� ��������� ������.

		$items - ������ �������������, ������: ID=>array(), ����� ����������� �������:
			full_name - ������ ��� ������������
			name - ��� ���������� �� ���������� HTML!
			email - e-mail ������������
			groups - ������ ����� ������������
			ip - IP ����� ������������
			last_visi - ���� ���������� ������ ������������ �� ���� � ������� YYYY-MM-DD HH:II:SS
			_aedit - ������ �� �������������� ������������
			_adel - ������ �� �������� ������������, ����� ���� false, ���� �������� ������������ ����������
		$groups - ������ ����� �������������. ������ ID=>array(), ����� ����������� �������:
			title - �������� ������
			html_pref - HTML ������� ������
			html_end - HTML ������� ������
		$pp - ���������� ������������� �� ��������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$cnt - ���������� ������������� �����
	*/
	public static function UsersList($items,$groups,$pp,$page,$cnt)
	{		$ltpl=Eleanor::$Language['tpl'];

		$Lst=Eleanor::LoadListTemplate('table-list',4)
			->begin(static::$lang['username'],static::$lang['groups'],static::$lang['last_visit'],$ltpl['functs']);

		$c='';
		if($items)
		{
			$editimg=Eleanor::$Template->default['theme'].'images/edit.png';
			$delimg=Eleanor::$Template->default['theme'].'images/delete.png';
			foreach($items as &$v)
			{
				$grs='';
				foreach($v['groups'] as &$gv)
					if(isset($groups[$gv]))
						$grs.='<a href="'.$groups[$gv]['_aedit'].'">'.$groups[$gv]['html_pref'].$groups[$gv]['title'].$groups[$gv]['html_end'].'</a>, ';
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.htmlspecialchars($v['name'],ELENT,CHARSET).'</a>'.($v['name']==$v['full_name'] ? '' : '<br /><i>'.$v['full_name'].'</i>'),
					rtrim($grs,' ,'),
					array(substr($v['last_visit'],0,-3),'center'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$editimg),
						$v['_adel'] ? array($v['_adel'],$ltpl['delete'],$delimg) : false
					)
				);
			}
		}
		else
			$Lst->empty(static::$lang['nousers']);
		return$Lst->end().Eleanor::$Template->Pages($cnt,$pp,$page,'#','TryUsers');
	}

	/*
		�������� �������� ��������
		$a - ������ ��������� ��������, �����:
			innertitle - ���������� �������� ��������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{
		static::Menu('');
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deleting'],$a['innertitle']),$back));
	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{		static::Menu('options');
		return$c;	}
}
TplSpam::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/spam-*.php',false);