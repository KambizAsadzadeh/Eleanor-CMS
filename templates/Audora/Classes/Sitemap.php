<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ��� ������� ���������� sitemap-��
*/
class TPLSitemap
{	/*
		���� ������
	*/
	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['sitemap'];
		$links=&$GLOBALS['Eleanor']->module['links'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			array($links['list'],$lang['list'],'act'=>$act=='list',
				'submenu'=>array(
					array($links['add'],$lang['add'],'act'=>$act=='add'),
				),
			),
			array($links['er'],$lang['editrobots'],'act'=>$act=='er'),
		);
	}
	/*
		�������� ����������� ���� sitemap-��
		$items - ������ sitemap-��. ������: ID=>array(), ����� ����������� �������:
			title - �������� sitemap-�
			modules - ������ ID �������, ������� ������������ � ������ sitemap
			taskid - ID ������ ������� sitemap-�
			total - ����� ������, ������� ����� ����� �������������
			already - ����� ��� ��������������� ������
			file - �������� ����� � ����������� ����������� (�������� ����� sitemap-�)
			compress - ���� ������ sitemap-�
			status - ������ ���������� sitemap-�
			lastrun - ����� ���������� ������� ������ sitemap-a
			nextrun - ����� ���������� ������� ������ sitemap-a
			free - ���� ������������� �������� �������� sitemap-�. ����� �������� ������� ����� ����� 1, ������ � ���� ������ ���������� ��������� sitemap-a
			_aswap - ������ �� �������������� ������� ���������� sitemap-a
			_aedit - ������ �� �������������� sitemap-�
			_adel - ������ �� �������� sitemap-�
		$cnt - ���������� ������� ������ �����
		$modules - ������ �������� �������. ������ id=>�������� ������
		$pp - ���������� ������� ������ �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_file - ������ �� ���������� ������ $items �� ����� (�����������/�������� � ����������� �� ������� ����������)
			sort_status - ������ �� ���������� ������ $items �� ������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
	*/	public static function ShowList($items,$cnt,$modules,$page,$pp,$qs,$links)
	{		static::Menu('list');		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['sitemap'];
		$ltpl=Eleanor::$Language['tpl'];

		$qs+=array(''=>array());
		$qs['']+=array('fi'=>array());
		$fs=(bool)$qs['']['fi'];
		$qs['']['fi']+=array(
			'file'=>false,
		);

		$Lst=Eleanor::LoadListTemplate('table-list',6)
			->begin(
				$ltpl['name'],
				array($lang['file'],'sort'=>$qs['sort']=='file' ? $qs['so'] : false,'href'=>$links['sort_file']),
				Eleanor::$Language['main']['modules'],
				array($lang['status'],'sort'=>$qs['sort']=='status' ? $qs['so'] : false,'href'=>$links['sort_status']),
				array($ltpl['functs'],80,'sort'=>$qs['sort']=='id' ? $qs['so'] : false,'href'=>$links['sort_id']),
				array(Eleanor::Check('mass',false,array('id'=>'mass-check')),20)
			);

		$images=Eleanor::$Template->default['theme'].'images/';
		if($items)
		{
			foreach($items as $k=>&$v)
			{				if($v['free'])
					$status=$v['lastrun']===null ? '<span style="color:red">Error</span>' : '<span style="color:green" title="'.$lang['pnrun'].'">'.((int)$v['lastrun']>0 ? Eleanor::$Language->Date($v['lastrun'],'fdt') : '&empty;').' - '.($v['nextrun'] ? Eleanor::$Language->Date($v['nextrun'],'fdt') : '&empty;').'</span>';
				else
					$status='<progress data-id="'.$k.'" style="width:100%" value="'.$v['already'].'" max="'.($v['total']>0 ? $v['total'] : 1).'" title="'.($pers=$v['total']>0 ? round($v['already']/$v['total']*100,2) : 0).'%"><span>'.$pers.'</span>%</progress>';				$ms='';
				foreach($v['modules'] as &$mv)
					if(isset($modules[$mv]))
						$ms.=$modules[$mv].', ';

				$Lst->item(
					'<a id="it'.$k.'" href="'.$v['_aedit'].'">'.($v['title'] ? $v['title'] : '&mdash;').'</a>',
					$v['file'],
					$ms ? rtrim($ms,', ') : '&mdash;',
					$status,
					$Lst('func',
						$ms ? array($v['_aswap'],$v['status'] ? $ltpl['deactivate'] : $ltpl['activate'],$v['status'] ? $images.'active.png' : $images.'inactive.png') : false,
						array($v['_aedit'],$ltpl['edit'],$images.'edit.png'),
						array($v['_adel'],$ltpl['delete'],$images.'delete.png')
					),
					Eleanor::Check('mass[]',false,array('value'=>$k))
				);
			}
		}
		else
			$Lst->empty($lang['nosm']);
		return Eleanor::$Template->Cover(
		'<form method="post">
			<table class="tabstyle tabform" id="ftable">
				<tr class="infolabel"><td colspan="2"><a href="#">'.$ltpl['filters'].'</a></td></tr>
				<tr>
					<td><b>'.$lang['file'].'</b><br />'.Eleanor::Edit('fi[file]',$qs['']['fi']['file']).'</td>
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
		.$Lst->end()
		.'<div class="submitline" style="text-align:right"><div style="float:left">'.sprintf($lang['smpp'],$Lst->perpage($pp,$qs)).'</div>'.$ltpl['with_selected'].Eleanor::Select('op',Eleanor::Option($ltpl['delete'],'k')).Eleanor::Button('Ok').'</div></form>'
		.Eleanor::$Template->Pages($cnt,$pp,$page,$qs));	}

	/*
		������ ��������/�������������� sitemap-�

		$id - ������������� �������������� sitemap-�, ���� $id==0 ������ ��������� �����������
		$values - ������ �������� �����
			����� �����:
			modules - ID (��.) �������, ������������ � ������ sitemap-�
			file - ��� ����� sitemap-a
			compress - ���� ������ sitemap-�
			fulllink - ���� ������ ������
			status - ������ ���������� sitemap-�
			per_time - ���������� ������, ������������ �� ���
			sendservice - ������ ��������, ���� ��������� sitemap ����� ��� ���������
			run_year - ��� ������� sitemap-�
			run_month - ����� ������� sitemap-�
			run_day - ���� ������� sitemap-�
			run_hour - ��� ������� sitemap-�
			run_minute - ������ ������� sitemap-�
			run_second - ������� ������� sitemap-�
			_recreate - ���� ����������� sitemap-� (������ ��� ��������������)
			_runnow - ���� ������� �������� sitemap-� ����� ����� ����������

			�������� �����:
			title_l - �������� sitemap-�

		$settings - ������ �������� ��� �������. ������ array(array(),array()...). ����� ���������� ��������:
			id - ID ������
			d - �������� ������
			t - �������� ������
			e - ������ �������� (�����)
			c - ������ �������� ������������ ������, �����
				title - �������� ���������
				descr - (��������� ����) �������� ���������
			s - ������ ��������������� HTML ��� ��������, ����� ������ � ������� ������� c ����.
		$errors - ������ ������
		$bypost - ������� ����, ��� ������ ����� ����� �� POST �������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
		$back - URL ��������
	*/
	public static function AddEdit($id,$values,$modules,$settings,$errors,$bypost,$links,$back)
	{		static::Menu($id ? '' : 'add');
		$lang=Eleanor::$Language['sitemap'];
		$ltpl=Eleanor::$Language['tpl'];

		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
				$ml['title_l'][$k]=Eleanor::Edit('title_l['.$k.']',Eleanor::FilterLangValues($values['title_l'],$k));
		}
		else
			$ml=array(
				'title_l'=>Eleanor::Edit('title_l',Eleanor::FilterLangValues($values['title_l'])),
			);

		if($back)
			$back=Eleanor::Control('back','hidden',$back);

		$ss=$mods=$opts='';
		foreach($modules as $k=>&$v)
			$mods.=Eleanor::Option($v,$k,in_array($k,$values['modules']));

		foreach(array('google'=>'Google','yahoo!'=>'Yahoo!','ask.com'=>'Ask.com','bing'=>'Bing') as $ks=>$vs)
			$ss.=Eleanor::Option($vs,$ks,!is_array($values['sendservice']) or in_array($ks,$values['sendservice']));

		foreach($settings as &$v)
			$opts=self::GetSettings($v);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item($ltpl['name'],Eleanor::$Template->LangEdit($ml['title_l'],null))
			->item(Eleanor::$Language['main']['modules'],Eleanor::Items('modules',$mods,10,array('id'=>'modules')))
			->item(array($lang['file'],Eleanor::Edit('file',$values['file']),'tip'=>$lang['file_']))
			->item($lang['egzip'],Eleanor::Check('compress',$values['compress']))
			->item($lang['fullurl'],Eleanor::Check('fulllink',$values['fulllink']))
			->item($lang['per_run'],Eleanor::Control('per_time','number',$values['per_time'],array('min'=>10,'max'=>65000)))
			->item($lang['status'],Eleanor::Check('status',$values['status']))
			->item($lang['sendservice'],Eleanor::Items('sendservice',$ss))
			->item($lang['runnow'],Eleanor::Check('_runnow',$values['_runnow']));
		if($id)
			$Lst->item($lang['recreate'],Eleanor::Check('_recreate',$values['_recreate']));
		$ge=(string)$Lst->end();

		$tt=(string)$Lst->begin()
			->item(array($lang['runyear'],Eleanor::Edit('run_year',$values['run_year']),'tip'=>$lang['runyear_']))
			->item(array($lang['runmonth'],Eleanor::Edit('run_month',$values['run_month']),'tip'=>$lang['runmonth_']))
			->item(array($lang['runday'],Eleanor::Edit('run_day',$values['run_day']),'tip'=>$lang['runday_']))
			->item(array($lang['runhour'],Eleanor::Edit('run_hour',$values['run_hour']),'tip'=>$lang['runhour_']))
			->item(array($lang['runminute'],Eleanor::Edit('run_minute',$values['run_minute']),'tip'=>$lang['runminute_']))
			->item(array($lang['runsecond'],Eleanor::Edit('run_second',$values['run_second']),'tip'=>$lang['runsecond_']))
			->end();

		$c=$Lst->form()
			->tabs(
				array($ltpl['general'],$ge),
				array($lang['timetorun'],$tt),
				array(
					$lang['mopts'],
					'<div id="mod-options"'.($opts ? ' style="display:none"' : '').'>'.Eleanor::$Template->Message($lang['nomops'],'info').'</div><div id="msetts"'.($opts ? '' : ' style="display:none"').'>'.$opts.'</div>'
				)
			)
			->submitline($back.Eleanor::Button().($id ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : ''))
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];

		return Eleanor::$Template->Cover($c,$errors,'error').'<script type="text/javascript">//<![CDATA[
$(function(){	var msetts={},
		noms=$("#mod-options");
	$("table[id^=\"ms-\"]","#msetts").each(function(){		msetts[$(this).prop("id").replace("ms-","")]=$(this).data("showed",true);	});	$("#modules").change(function(){		var th=$(this),
			mids=th.val()||[],
			ina,
			oldv=false,
			messsh=false;
		$.each(msetts,function(k,v){			ina=$.inArray(k,mids);
			if(ina>-1)
			{
				if(!v.data("showed"))
				{
					if(oldv)
						v.insertAfter(oldv);
					else
						v.prependTo("#msetts");
					v.data("showed",true);
				}
				messsh=true;
				mids.splice(ina,1);
				oldv=v;
			}
			else if(ina==-1 && v.data("showed"))
				v.detach().data("showed",false);		})

		if(messsh)
		{
			$("#mod-options").hide();
			$("#msetts").show();
		}
		else
		{
			$("#mod-options").show();
			$("#msetts").hide();
		}
		if(mids.length>0)			CORE.Ajax(
				{					direct:"admin",
					file:"sitemap",
					event:"loadmsetts",
					mids:mids
				},
				function(r)
				{					var op;
					$.each(r,function(k,v){						if($.inArray(k,mids)>-1)
						{
							op=th.find("option[value=\""+k+"\"]").prev();
							while(op.size()>0)
							{
								if(typeof msetts[op.val()]!="undefined" && msetts[op.val()].data("showed"))
								{
									msetts[op.val()].after(v);
									op=false;
									break;
								}
								op=op.prev();
							}
							if(op)
								$("#msetts").prepend(v);
							msetts[k]=$("#ms-"+k).data("showed",true);
							if(!messsh)
							{
								$("#mod-options").hide();
								$("#msetts").show();
							}
						}
					})
				}
			);
	});})//]]></script>';	}

	/*
		������� �������. ����������� �������� ������ ��� ������ sitemap-�. ������������ � ��� AJAX

		$ms ������ � ����������� �����. �����:
			id - ID ������
			d - �������� ������
			t - �������� ������
			e - ������ �������� (�����)
			c - ������ �������� ������������ ������, �����
				title - �������� ���������
				descr - (��������� ����) �������� ���������
			s - ������ ��������������� HTML ��� ��������, ����� ������ � ������� ������� c ����.
	*/
	public static function GetSettings($ms)
	{		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin(array('id'=>'ms-'.$ms['id']))
			->head('<span title="'.$ms['d'].'">'.$ms['t'].'</span>');

		if($ms['e'])
			$Lst->s.='<tr><td colspan="2">'.Eleanor::$Template->Message($ms['e'],'error').'</td></tr>';
		else
			foreach($ms['c'] as $ck=>&$cv)
				$Lst->item(array($cv['title'],$ms['s'][$ck],'descr'=>isset($cv['descr']) ? $cv['descr'] : false));
		return$Lst->end();
	}

	/*
		�������� ������ ����� robots.txt

		$v - ���������� �����
		$save - ���� �������������
	*/	public static function EditRobots($v,$saved)
	{		static::Menu('er');
		$lang=Eleanor::$Language['sitemap'];		$Lst=Eleanor::LoadListTemplate('table-form')			->form()
			->begin()
			->item($lang['robots'],Eleanor::Text('text',$v,array(),0))
			->button(Eleanor::Button())
			->end()
			->endform();
		return Eleanor::$Template->Cover(($saved ? Eleanor::$Template->Message($lang['rsaved'],'info') : '').$Lst);	}

	/*
		�������� �������� ����� �����
		$a - ������ ��������� ����� �����, �����:
			file - ���� ����� �����
			title - �������� ����� �����
		$back - URL ��������
	*/
	public static function Delete($t,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['sitemap']['deleting'],$a['title'],$a['file']),$back));
	}
}