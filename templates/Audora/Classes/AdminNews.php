<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������� ������ ��������
*/
class TPLAdminNews
{	public static
		$lang;	/*
		���� ������
	*/
	protected static function Menu($act='')
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$categs=isset($GLOBALS['Eleanor']->module['navigation']['categories']) ? $GLOBALS['Eleanor']->module['navigation']['categories'] : false;
		$options=isset($GLOBALS['Eleanor']->module['navigation']['options']) ? $GLOBALS['Eleanor']->module['navigation']['options'] : false;
		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					$links['newlist'] ? array($links['newlist']['link'],sprintf(static::$lang['news'],$links['links']['cnt']),'act'=>false) : false,
					array($links['add'],static::$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['tags'],$lang['tags_list'],'act'=>$act=='tags',
				'submenu'=>array(
					array($links['addt'],static::$lang['add_tag'],'act'=>$act=='addt'),
				),
			),
			$options ? $options : array($links['options'],Eleanor::$Language['main']['options'],'act'=>$act=='options'),
			$categs ? $categs : array($links['categories'],static::$lang['cats_manage']),
			//array($links['addf'],static::$lang['addf'],'act'=>$act=='addf'),
		);
	}
	/*
		�������� ����������� ���� �����
		$items - ������ ����������� �������. ������: ID=>array(), ����� ����������� �������:
			language - ���� ����
			name - �������� ����
			cnt - ���������� �������� � ������� ����
			_aedit - ������ �� �������������� ����
			_adel - ������ �� �������� ����
		$cnt - ���������� ����� �����
		$pp - ���������� ����� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_name - ������ �� ���������� ������ $items ����� ���� (�����������/�������� � ����������� �� ������� ����������)
			sort_cnt - ������ �� ���������� ������ $items ���������� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ����� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function TagsList($items,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('tags');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$ltpl=Eleanor::$Language['tpl'];

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

		$Lst=Eleanor::LoadListTemplate('table-list',5)
			->begin(
				array($lang['tname'],'sort'=>$qs['sort']=='name' ? $qs['so'] : false,'href'=>$links['sort_name']),
				Eleanor::$vars['multilang'] ? $lang['language'] : false,
				array(static::$lang['nums'],'sort'=>$qs['sort']=='cnt' ? $qs['so'] : false,'href'=>$links['sort_cnt']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
				$Lst->item(
					'<a href="'.$v['_aedit'].'">'.$v['name'].'</a>',
					Eleanor::$vars['multilang'] ? array(isset(Eleanor::$langs[$v['language']]) ? Eleanor::$langs[$v['language']]['name'] : '<i>'.$ltpl['all'].'</i>','center') : false,
					array($v['cnt'],'right'),
					$Lst('func',
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
		}
		else
			$Lst->empty(static::$lang['tnfn']);

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
<script type="text/javascript">//<![CDATA[
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
});//]]></script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['nto_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		�������� ����������/�������������� ����
		$id - ������������� �������������� ����, ���� $id==0 ������ ��� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$errors - ������ ������
		$back - URL ��������
		$hasdraft - ������� ������� ���������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEditTag($id,$controls,$values,$errors,$back,$hasdraft,$links)
	{		static::Menu($id ? 'editt' : 'addt');		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()->begin();
		foreach($controls as $k=>&$v)
			if(is_array($v))
				$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'tip'=>$v['descr']));
			else
				$Lst->head($v);

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));

		$Lst->button(
			$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Input('_draft','t'.$id,array('type'=>'hidden'))
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)->end()->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($Lst,$errors,'error');
	}

	/*
		�������� ����������� ���� ��������
		$items - ������ ��������. ������: ID=>array(), ����� ����������� �������:
			cats - ������ ID ���������, � ������� ����������� ������ �������
			date - ���� ���������� �������
			enddate - ���� ���������� ������ �������
			author - ��� ������ �������, ���������� HTML
			author_id - ID ������ �������
			status - ������ ���������� �������: 0 - �� �������, 1 - �������, -1 - ������� ���������, -2 - ������� ����������� ���� ���������, 2 - ����������
			title - �������� �������
			_aedit - ������ �� �������������� �������
			_adel - ������ �� �������� �������
			_aswap - ������ �� ��������� ���������� �������, ���� ����� false - ������ ������ ���������� (������� ������)
		$categs - ������ ��������� �������. �����: ID=>array(), ����� ����������� �������:
			title - �������� ���������
		$cnt - ���������� ����� �����
		$pp - ���������� ����� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_title - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_date - ������ �� ���������� ������ $items �� ���� (�����������/�������� � ����������� �� ������� ����������)
			sort_author - ������ �� ���������� ������ $items �� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� �������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$categs,$cnt,$pp,$qs,$page,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$ltpl=Eleanor::$Language['tpl'];

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
		if($items)
		{			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($items as $k=>&$v)
			{				$cats='';
				foreach($v['cats'] as &$cv)
					if(isset($categs[$cv]))
						$cats.=($cats ? '' : '<b>').$categs[$cv]['title'].($cats ? '' : '</b>').', ';
				$Lst->item(
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.$v['title'].'</a>',
					$cats ? rtrim($cats,', ') : array('--','center'),
					array($v['date'],'center'),
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
<script type="text/javascript">//<![CDATA[
$(function(){
	var fitrs=$("#ftable tr:not(.infolabel)");
	$("#ftable .infolabel a").click(function(){
		fitrs.toggle();
		$("#ftable .infolabel a").toggleClass("selected");
		return false;
	})'.($fs ? '' : '.click()').';
	One2AllCheckboxes("#checks-form","#mass-check","[name=\"mass[]\"]",true);
});//]]></script>
		</form><form id="checks-form" action="'.$links['form_items'].'" method="post" onsubmit="return (CheckGroup(this) && confirm(\''.$ltpl['are_you_sure'].'\'))">'
			.$Lst->end().'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf(static::$lang['tto_pages'],$Lst->perpage($pp,$links['pp'])).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['activate'],'a').Eleanor::Option($ltpl['deactivate'],'d').Eleanor::Option($ltpl['delete'],'k').Eleanor::Option(static::$lang['waitmod'],'m')).Eleanor::Button('Ok').'</div></form>'
			.Eleanor::$Template->Pages($cnt,$pp,$page,array($links['pages'],$links['first_page']))
		);	}

	/*
		�������� ����������/�������������� �������
		$id ������������� ������������� �������, ���� $id==0 ������ ������� �����������
		$values ������ �������� �����
			����� �����:
			cats - ������ ���������
			date  - ���� ���������� �������
			pinned - ���� �� ����������� �������, ������� ����� ����������
			enddate - ���������� ������� �������
			author - ��� ������ �������
			author_id - ID ������ �������
			show_detail - ���� ��������� ������ ������ "���������" ��� ���������� ������������ �������
			show_sokr - ���� ��������� ����������� ������ ����������� ������� ��� ��������� ���������
			reads - ���������� ���������� �������
			status - ������ ���������� �������: 0 - �� �������, 1 - �������, -1 - ������� ���������

			�������� �����:
			title - ��������� �������
			announcement - ����� �������
			text - ����� �������
			uri - URI �������
			meta_title - ��������� ���� �������� ��� ��������� �������
			meta_descr - ���� �������� �������

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
	*/
	public static function AddEdit($id,$values,$errors,$uploader,$voting,$bypost,$hasdraft,$back,$links)
	{		static::Menu($id ? 'edit' : 'add');		#$GLOBALS['jscripts'][]='addons/autocomplete/jquery.autocomplete.js'; #����� ���� - ��� �� �����.
		#$GLOBALS['head']['autocomplete']='<link rel="stylesheet" type="text/css" href="addons/autocomplete/style.css" />';

		$ltpl=Eleanor::$Language['tpl'];
		if(Eleanor::$vars['multilang'])
		{			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{				$ml['meta_title'][$k]=Eleanor::Input('meta_title['.$k.']',Eleanor::FilterLangValues($values['meta_title'],$k),array('tabindex'=>17));
				$ml['meta_descr'][$k]=Eleanor::Input('meta_descr['.$k.']',Eleanor::FilterLangValues($values['meta_descr'],$k),array('tabindex'=>18));
				$ml['title'][$k]=Eleanor::Input('title['.$k.']',$GLOBALS['Eleanor']->Editor->imgalt=Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1,'id'=>'title-'.$k));
				$ml['announcement'][$k]=$GLOBALS['Eleanor']->Editor->Area('announcement['.$k.']',Eleanor::FilterLangValues($values['announcement'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10)));
				$ml['text'][$k]=$GLOBALS['Eleanor']->Editor->Area('text['.$k.']',Eleanor::FilterLangValues($values['text'],$k),array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15)));
				$ml['uri'][$k]=Eleanor::Input('uri['.$k.']',Eleanor::FilterLangValues($values['uri'],$k),array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title-'.$k.'\').val())','tabindex'=>11));

				$ml['tags'][$k]=Eleanor::Input('tags['.$k.']',Eleanor::FilterLangValues($values['tags'],$k),array('tabindex'=>5));
			}
		}
		else
			$ml=array(
				'meta_title'=>Eleanor::Input('meta_title',$values['meta_title'],array('tabindex'=>17)),
				'meta_descr'=>Eleanor::Input('meta_descr',$values['meta_descr'],array('tabindex'=>18)),
				'title'=>Eleanor::Input('title',$GLOBALS['Eleanor']->Editor->imgalt=$values['title'],array('tabindex'=>1,'id'=>'title')),
				'announcement'=>$GLOBALS['Eleanor']->Editor->Area('announcement',$values['announcement'],array('bypost'=>$bypost,'no'=>array('tabindex'=>6,'rows'=>10))),
				'text'=>$GLOBALS['Eleanor']->Editor->Area('text',$values['text'],array('bypost'=>$bypost,'no'=>array('tabindex'=>7,'rows'=>15))),
				'uri'=>Eleanor::Input('uri',$values['uri'],array('onfocus'=>'if(!$(this).val())$(this).val($(\'#title\').val())','tabindex'=>1)),

				'tags'=>Eleanor::Input('tags',Eleanor::FilterLangValues($values['tags']),array('tabindex'=>5)),
			);
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

		if($back)
			$back=Eleanor::Input('back',$back,array('type'=>'hidden'));
		$c.=$Lst->tabs(
			array($ltpl['general'],$text),
			array(Eleanor::$Language['main']['options'],$extra),
			array(static::$lang['voting'],$voting)
			//array('�������������� ����',Eleanor::$Template->Message('� ��� ���� � ����������...','info'))#ToDo!
		)
		->submitline((string)$uploader)
		->submitline(
			$back
			.Eleanor::Button('Ok','submit',array('tabindex'=>20))
			.($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
			.Eleanor::Input('_draft','n'.$id,array('type'=>'hidden'))
			.Eleanor::$Template->DraftButton($links['draft'],1)
			.($hasdraft ? ' <a href="'.$links['nodraft'].'">'.$ltpl['nodraft'].'</a>' : '')
		)
		->endform();

		foreach($errors as $k=>&$v)
			if(is_int($k) and is_string($v) and isset(static::$lang[$v]))
				$v=static::$lang[$v];

		return Eleanor::$Template->Cover($c,$errors,'error').'<script type="text/javascript">//<![CDATA[
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
				serviceUrl:CORE.ajax_file,
				minChars:2,
				delimiter:/,\s*/,
				params:p
			});
		$("input[name=\"_onelang\"]").change(function(){			p.lang=(m && !$(this).prop("checked")) ? m[1] : "";
			a.setOptions({params:p})		});
	});})//]]></script>';	}

	/*
		�������� �������� �������
		$a - ������ ���������� ��������� ��������
			title - �������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['submit_del'],$a['title']),$back));
	}

	/*
		�������� �������� ����
		$a - ������ ���������� ���������� ����
			name - ���
		$back - URL ��������
	*/
	public static function DeleteTag($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(static::$lang['deletingt'],$a['name']),$back));
	}

	/*
		������� ��� ���������
		$c - ��������� ���������
	*/
	public static function Categories($c)
	{		static::Menu();		return$c;	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{
		static::Menu('options');
		return$c;
	}
}
TplAdminNews::$lang=Eleanor::$Language->Load(Eleanor::$Template->default['theme'].'langs/news-*.php',false);