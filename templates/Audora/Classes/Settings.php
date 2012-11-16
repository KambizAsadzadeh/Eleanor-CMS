<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ������ ��������
*/
class TPLSettings
{	protected static function Menu($act='')
	{		$lang=Eleanor::$Language['settings'];
		$links=&$GLOBALS['Eleanor']->module['links_settings'];

		$GLOBALS['Eleanor']->module['navigation']=array(
			'opts'=>$links['opts']
				? array($links['opts'],$lang['olist'],'modules','act'=>$act=='options',
					'submenu'=>$links['addoption']
					? array(
						array($links['addoption'],$lang['addo'],'act'=>$act=='addo'),
					)
					: false,
				)
				: false,
			'grs'=>array($links['grs'],$lang['grlist'],'mgblocks','act'=>$act=='groups',
				'submenu'=>$links['addgroup']
				? array(
					array($links['addgroup'],$lang['addg'],'act'=>$act=='addg'),
				)
				: false,
			),
			'opt'=>$links['addoption'] && !$links['opts'] ? array($links['addoption'],$lang['addo'],'addoption','act'=>$act=='addo') : false,
			'im'=>$links['import'] ? array($links['import'],$lang['import'],'import','act'=>$act=='import') : false,
			'ex'=>$links['export'] ? array($links['export'],$lang['export'],'export','act'=>$act=='export') : false,
		);
	}
	/*
		������ �������� � �������� ��������
		$items - ������ ����� ��������. ������: ID=>array(), ����� ���������� ��������:
			title - �������� ������
			descr - �������� ������
			protected - ���� ������������ ������
			position - ������� ������. �� ����� ���� ������ �������������
			cnt - ���������� �������� � ������
			_buttons - ������ "������" ��� ������, ������ type=>link. ���������� �����:
				reset - ������ �� ����� �������� ������ (�������� �� ���������� ���������� �� ���������)
				show - ������ �� ��������� ������
				��������� �����:
				up - ������ �� �������� ������ ������
				down - ������ �� ��������� ������ ����
				default - ������ �� ������� �������� �������� �� ��������� �������� ����������� (������� ��������� ������ ����������� �� ���������)
				edit - ������ �� �������������� ������
				delete - ������ �� �������� ������
	*/
	public static function SettGroupsCover($items,$links)
	{		static::Menu('groups');		$trs='';
		$lang=Eleanor::$Language['settings'];
		$ltpl=Eleanor::$Language['tpl'];
		$h=Eleanor::$Template->default['theme'];
		foreach($items as $k=>&$v)
		{			$trs.='<tr><td style="width:80%" id="gr'.$k.'"><a href="'.$v['_buttons']['show'].'"><b>'.$v['title'].'</b></a><br /><span class="small"><b>'
				.$lang['options']($v['cnt'])
				.'</b>&nbsp;&nbsp;&nbsp;'.$v['descr'].'</span></td><td class="function">';
			if(isset($v['_buttons']['up']))
				$trs.='<a href="'.$v['_buttons']['up'].'" title="'.$lang['up'].'"><img src="'.$h.'images/up.png" alt="" /></a>';
			if(isset($v['_buttons']['down']))
				$trs.='<a href="'.$v['_buttons']['down'].'" title="'.$lang['down'].'"><img src="'.$h.'images/down.png" alt="" /></a>';
			$trs.='<a href="'.$v['_buttons']['reset'].'" title="'.$lang['reset_def_gr'].'"><img src="'.$h.'images/o_del.png" alt="" /></a>';
			if(isset($v['_buttons']['default']))
				$trs.='<a href="'.$v['_buttons']['default'].'" title="'.$lang['make_def_gr'].'"><img src="'.$h.'images/o_add.png" alt="" /></a>';
			if(isset($v['_buttons']['edit']))
				$trs.='<a href="'.$v['_buttons']['edit'].'" title="'.$ltpl['edit'].'"><img src="'.$h.'images/edit.png" alt="" /></a>';
			if(isset($v['_buttons']['delete']))
				$trs.='<a href="'.$v['_buttons']['delete'].'" title="'.$ltpl['delete'].'"><img src="'.$h.'images/delete.png" alt="" /></a>';
			$trs.='</td></tr>';
		}

		return Eleanor::$Template->Cover('<table class="tabstyle tabform">'.$trs
			.(isset($links['wg']) ? '<tr><td style="width:80%"><a href="'.$links['wg'].'"><b>'.$lang['ops_without_g'].'</b></a><br /><span class="small">'.$lang['ops_wo_g_d'].'</span></td><td></td></tr>' : '')
			.'</table>'
			.(isset($links['search']) ? '<form action="'.$links['search'].'" method="post"><div class="submitline" style="text-align: right;"><input style="width: 200px;" type="text" value="" name="search" /><input class="button" type="submit" value="'.$lang['find'].'" /></div></form>' : ''));
	}

	/*
		������ �������� � ������� ������
		$err - ����� ������
	*/
	public static function SettShowError($err)
	{		static::Menu();
		return Eleanor::$Template->Cover('',$err,'error');
	}

	/*
		������ ��������-������������ � ������� �������� �������� ������ �� ��������� �������� ���������� �������� ������
		$a - ������ ������ ��������, �����:
			title - �������� ������
		$back - URL ��������
	*/
	public static function SettGrDefault($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['settings']['make_o_def_c'],$a['title']),$back));
	}

	/*
		������ ��������-������������ � ������ �������� �������� ������ ����������� �� ���������
		$a - ������ ������ ��������, �����:
			title - �������� ������
		$back - URL ��������
	*/
	public static function SettGrReset($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['settings']['ays_to_rg'],$a['title']),$back));
	}

	/*
		������ �������� � ������������ ���������-��������

		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������. ������: name=>array(). ����� ����������� �������:
			id - ID ���������
			multilang - ������� �������������� ����
			_areset - ������ �� ����� ��������� (����������� �������� �� ���������) ��� false
			_adefault - ������ �� ������ �������� �� ��������� ������� ��������� ��������� ��� false
			_aedit - ������ �� �������������� ��������� ��� false
			_adelete - ������ �� �������� ��������� ��� false
			_aup - ������ �� �������� ��������� �����, ���� ����� false - ������ ��������� ��� � ��� ��������� � ����� �����
			_adown - ������ �� ��������� ��������� ����, ���� ����� false - ������ ��������� ��� � ��� ��������� � ����� ����
			_agroup - ������ �� ������ ��������� (��� ������������� ������), � ��������� ������ ����� false
			titles - ������ � �������:
				title - �������� ���������
				descr - �������� ���������
				group - ��������� ��������� � ���� ����������� ��������
				gtitle - �������� ������ ���������, ��� ������������� ������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$crerrors - ������ ����������� ������ ���������. ���� � ������� ���������� ���� �� ������� ���������, ������ ������ ������ ���� �������� ������ �������� - ��� �����������
		$errors - ������ ������ ���������� ���������. ���� � ������� ���������� ���� �� ������� ���������, ������ ������ ���� �������� ��� ���������. ������������� ����������.
		$links - �������� ����������� ������, ������ � �������:
			form - ������ �� ���������� ����� ��������
		$word - ������� �����, ���������� ���������
		$gshow - ���� ����������� �������� �����
		$error - ����� ������
	*/
	public static function SettOptionsList($controls,$values,$crerrors,$errors,$links,$word,$gshow,$error)
	{
		static::Menu('options');
		if(!$controls)
			return Eleanor::$Template->Message(Eleanor::$Language['settings']['nooptions'],'error');

		$c='';
		$n=0;
		$ids=array();
		$lang=Eleanor::$Language['settings'];
		$ltpl=Eleanor::$Language['tpl'];
		$tabs=$tip=false;
		foreach($controls as $k=>&$v)
		{
			$ids[]=$v['id'];

			if($word)
			{
				$v['titles']['title']=Strings::MarkWords($word,$v['titles']['title']);
				$v['titles']['descr']=Strings::MarkWords($word,$v['titles']['descr']);
			}

			$html='';
			if(isset($crerrors[$k]))
				$html=Eleanor::$Template->Message($crerrors[$k],'error');
			else
			{				$va=&$values[$k];				if($v['multilang'] and is_array($va))
				{
					$flags='';
					$u=uniqid('l');

					foreach($va as $vak=>&$vav)
					{
						$html.='<div id="'.$u.'-'.$vak.'" class="langtabcont">'.(isset($errors[$k][$vak]) ? Eleanor::$Template->Message($errors[$k][$vak],'error').'<br />' : '').$vav.'</div>';
						$flags.='<a href="#" data-rel="'.$u.'-'.$vak.'" class="'.$vak.($vak==Language::$main ? ' selected' : '').'" title="'.Eleanor::$langs[$vak]['name'].'"><img src="images/lang_flags/'.$vak.'.png" alt="'.Eleanor::$langs[$vak]['name'].'" /></a>';
					}
					$tabs=true;
					$html.='<div id="div-'.$u.'" class="langtabs">'.$flags.'</div><script type="text/javascript">/*<![CDATA[*/$("#div-'.$u.' a").Tabs();//]]></script>';
				}
				else
					$html.=(isset($errors[$k]) ? Eleanor::$Template->Message($errors[$k],'error').'<br />' : '').$va;			}

			if($v['titles']['descr'])
			{				$tip=true;
				$descr='<span class="labinfo" title="'.htmlspecialchars($v['titles']['descr'],ELENT,CHARSET).'">(?)</span>';			}
			else
				$descr='';
			$descr.=$v['titles']['title'];

			if($v['_agroup'])
				$descr.='<br /><br />'.$lang['group'].' <a href="'.$v['_agroup'].'">'.$v['titles']['gtitle'].'</a>';


			$n++;
			if($gshow and $v['titles']['group'] or $n==1)
			{
				if($n>1)
					$c.='</table>'.Eleanor::$Template->CloseTable();
				$c.=Eleanor::$Template->Title($v['titles']['group'] ? $v['titles']['group'] : end($GLOBALS['title']))
					.Eleanor::$Template->OpenTable().'<table class="tabstyle tabform">';
			}
			$c.='<tr><td class="label" id="opt'.$v['id'].'">'.$descr.'</td><td>'.$html
				.'</td><td class="function" style="width:130px">'
				.($v['_aup'] ? '<a href="'.$v['_aup'].'" title="'.$lang['up'].'"><img src="'.Eleanor::$Template->default['theme'].'images/up.png" alt="" /></a>' : '')
				.($v['_adown'] ? '<a href="'.$v['_adown'].'" title="'.$lang['down'].'"><img src="'.Eleanor::$Template->default['theme'].'images/down.png" alt="" /></a>' : '')
				.($v['_areset'] ? '<a href="'.$v['_areset'].'" title="'.$lang['reset_opt'].'"><img src="'.Eleanor::$Template->default['theme'].'images/o_del.png" alt="" /></a>' : '')
				.($v['_adefault'] ? '<a href="'.$v['_adefault'].'" title="'.$lang['default_opt'].'"><img src="'.Eleanor::$Template->default['theme'].'images/o_add.png" alt="" /></a>' : '')
				.($v['_aedit'] ? '<a href="'.$v['_aedit'].'" title="'.$ltpl['edit'].'"><img src="'.Eleanor::$Template->default['theme'].'images/edit.png" alt="" /></a>' : '')
				.($v['_adelete'] ? '<a href="'.$v['_adelete'].'" title="'.$ltpl['delete'].'"><img src="'.Eleanor::$Template->default['theme'].'images/delete.png" alt="" /></a>' : '')
				.'</td></tr>';
		}

		if($tabs)
			$GLOBALS['jscripts'][]='js/tabs.js';
		if($tip)
			$GLOBALS['jscripts'][]='js/jquery.poshytip.js';

		return($error ? Eleanor::$Template->Message($error,'error') : '')
			.'<form method="post" enctype="multipart/form-data" action="'.$links['form'].'">'
			.$c.'</table>'.Eleanor::Control('ids','hidden',join(',',$ids))
			.'<div class="submitline">'.Eleanor::Button().'</div>'
			.Eleanor::$Template->CloseTable().'</form>'
			.($tip ? '<script type="text/javascript">//<![CDATA[
$(function(){
	$("span.labinfo").poshytip({
		className: "tooltip",
		offsetX: -7,
		offsetY: 16,
		allowTipHover: false
	});
});//]]></script>' : '');
	}

	/*
		������ �������� �������� ��������

		$a - ������ �� ����� �������� � �����������. ������ ID=>array(), ����� �������
			title - �������� ������ ��������
			descr - �������� ������ ��������
			opts - ������ � �����������, ������ ID=>array(), ����� �������
				title - �������� ���������
				descr - �������� ���������
		$groups - ������ ���������� ����� (IDs)
		$options - ������ ���������� �������� (IDs)
	*/
	public static function SettExport($a,$groups,$options)
	{
		static::Menu('export');
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		$lang=Eleanor::$Language['settings'];
		$c='<form method="post"><table class="tabstyle" id="table-ch"><tr class="tablethhead"><th style="width:15px">'.Eleanor::Check('all',false,array('id'=>'all-ch')).'</th><th>'.$lang['olist'].'</th></tr>';
		$n=0;
		$script='';
		foreach($a as $k=>&$v)
		{
			$c.='<tr class="'.($n++ % 2 ? 'tabletrline2' : 'tabletrline1').'"><td style="text-align:center">'.Eleanor::Check('groups[]',in_array($k,$groups),array('value'=>$k,'id'=>'gr-'.$k)).'</td><td>';
			if(isset($v['opts']))
			{
				$c.='<a href="#" style="font-weight:bold" onclick="$(\'#opts-'.$k.'\').slideToggle(\'fast\');return false">'.$v['title'].'</a>'.($v['descr'] ? '<br />'.$v['descr'] : '').'<table class="tabstyle" id="opts-'.$k.'" style="display:none;margin:5px">';
				$no=0;
				foreach($v['opts'] as $ok=>&$ov)
					$c.='<tr class="'.($no++ % 2 ? 'tabletrline2' : 'tabletrline1').'"><td style="text-align:center;width:15px;vertical-align:top">'.Eleanor::Check('options[]',in_array($ok,$options),array('value'=>$ok)).'</td><td><b>'.$ov['title'].'</b>'.($ov['descr'] ? '<br />'.$ov['descr'] : '').'</td></tr>';
				$c.='</table>';
			}
			else
				$c.='<b>'.$v['title'].'</b>'.($v['descr'] ? '<br />'.$v['descr'] : '');
			$c.='</td></tr>';
			$script.='new One2AllCheckboxes("#opts-'.$k.'","#gr-'.$k.'","input[name=\"options[]\"]");';
		}
		$c.='</table><div class="submitline">'.$lang['ex_with_ex'].Eleanor::Select('update',Eleanor::Option($lang['ex_ignore'],'ignore').Eleanor::Option($lang['ex_update'],'update').Eleanor::Option($lang['ex_full'],'full').Eleanor::Option($lang['ex_delete'],'delete')).' '.Eleanor::Button($lang['do_export']).'</div></form><script type="text/javascript">/*<![CDATA[*/$(function(){'.$script.'new One2AllCheckboxes("#table-ch","#all-ch","input[name=\"groups[]\"]",true);})//]]></script>';
		return Eleanor::$Template->Cover($c);
	}


	/*
		������ �������� ������� ��������

		$mess - ��������� ��������� �� �������� ������� (��� <br />)
		$error - ��������� �� ������
	*/
	public static function SettImport($mess,$error)
	{		static::Menu('import');
		$lang=Eleanor::$Language['settings'];
		return Eleanor::$Template->Cover('<form method="post" enctype="multipart/form-data">'
			.($mess ? Eleanor::$Template->Message(nl2br($mess),'info') : '')
			.'<table class="tabstyle tabform"><tr class="tabletrline1"><td class="label">'.$lang['select_file_im'].'</td><td>'.Eleanor::Control('import','file','',array('tabindex'=>1)).'</td></tr></table><div class="submitline">'.Eleanor::Button($lang['do_import'],'submit',array('tabindex'=>2)).'</div></form>',$error);
	}

	/*
		�������� �������� ������
		$a - ������ ��������� ������, �����:
			title - �������� ������
		$back - URL ��������
	*/
	public static function SettGroupDelete($a,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['settings']['deleting_g'],$a['title']),$back));
	}

	/*
		�������� �������� ���������
		$a - ������ ��������� ���������, �����:
			title - �������� ���������
		$back - URL ��������
	*/
	public static function SettDelete($t,$back)
	{
		static::Menu();
		return Eleanor::$Template->Cover(Eleanor::$Template->Confirm(sprintf(Eleanor::$Language['settings']['deleting_o'],$a['title']),$back));
	}

	/*
		������ �������������� ������ ��������

		$id - ������������� ������������� ������, ���� $id==0 ������ ������ �����������
		$values - ������ �������� �����
			����� �����:
			pos - ������� ������
			keyword - �������� ����� ������ (�� �������� � ������ ������������)
			name - �������� ������ (�� �������� � ������ ������������)
			protected - ���� ������������ ������
			_onelang - ���� �������������

			�������� �����:
			title - �������� ������
			descr - �������� ������

		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ������ ��� false
		$errors - ������ ������
		$bypost - ������� ����, ��� ������ ����� ����� �� POST �������
		$back - URL ��������
	*/
	public static function SettAddEditGroup($id,$values,$links,$errors,$bypost,$back)
	{		static::Menu($id ? '' : 'addg');
		if(Eleanor::$vars['multilang'])
		{			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
				$ml['descr'][$k]=Eleanor::Text('descr['.$k.']',Eleanor::FilterLangValues($values['descr'],$k),array('tabindex'=>2));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title',$values['title'],array('tabindex'=>1)),
				'descr'=>Eleanor::Text('descr',$values['descr'],array('tabindex'=>2)),
			);
		$extra=$id && $values['protected'] ? array('disabled'=>true) : array();
		$lang=Eleanor::$Language['settings'];
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-form')->form()
			->begin()
			->item(array($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null),'imp'=>true))
			->item($ltpl['descr'],Eleanor::$Template->LangEdit($ml['descr'],null))
			->item(array($lang['pos'],Eleanor::Edit('pos',$values['pos'],array('tabindex'=>3)),'tip'=>$lang['pos_']))
			->item(array($lang['keyw_g'],Eleanor::Edit('keyword',$values['keyword'],array('tabindex'=>4)+$extra),'imp'=>true))
			->item(array($lang['priv_name'],Eleanor::Edit('name',$values['name'],array('tabindex'=>5)+$extra),'imp'=>true))
			->item($lang['prot_g'],Eleanor::Check('protected',$values['protected'],array('tabindex'=>6)+$extra));

		if(Eleanor::$vars['multilang'])
			$Lst->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],null,4));

		if($back)
			$back=Eleanor::Control('back','hidden',$back);
		$Lst->end()->submitline(
			$back
			.Eleanor::Button('Ok','submit',array('tabindex'=>7))
			.($links['delete'] ? ' '.Eleanor::Button($ltpl['delete'],'button',array('onclick'=>'window.location=\''.$links['delete'].'\'')) : '')
		)->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		return Eleanor::$Template->Cover((string)$Lst,$errors);
	}

	/*
		������ ��������/�������������� ���������

		$id - ������������� ������������� ���������, ���� $id==0 ������ ��������� �����������
		$values - ������ �������� �����
			����� �����:
			group - ������������� ������, � ������� ��������� ���������
			pos - ������� ���������
			name - ���������� �������� ���������
			protected - ���� ������������ ������
			eval_load - ��� �������� ��������
			eval_save - ��� ���������� ��������
			_onelang - ���� �������������

			�������� �����:
			title - �������� ������
			descr - �������� ������
			startgroup - �������� ��������� ��������

		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
		$errors - ������ ������
		$bypost - ������� ����, ��� ������ ����� ����� �� POST �������
		$back - URL ��������
	*/
	public static function SettAddEditOption($id,$values,$groups,$control,$links,$bypost,$back,$errors)
	{		static::Menu($id ? '' : 'addo');
		if(Eleanor::$vars['multilang'])
		{
			$ml=array();
			foreach(Eleanor::$langs as $k=>&$v)
			{
				$ml['title'][$k]=Eleanor::Edit('title['.$k.']',Eleanor::FilterLangValues($values['title'],$k),array('tabindex'=>1));
				$ml['descr'][$k]=Eleanor::Text('descr['.$k.']',Eleanor::FilterLangValues($values['descr'],$k),array('tabindex'=>2));
				$ml['startgroup'][$k]=Eleanor::Edit('startgroup['.$k.']',Eleanor::FilterLangValues($values['startgroup'],$k),array('tabindex'=>4));
			}
		}
		else
			$ml=array(
				'title'=>Eleanor::Edit('title',$values['title'],array('tabindex'=>1)),
				'descr'=>Eleanor::Text('descr',$values['descr'],array('tabindex'=>2)),
				'startgroup'=>Eleanor::Edit('startgroup',$values['startgroup'],array('tabindex'=>4)),
			);

		$ltpl=Eleanor::$Language['tpl'];
		$lang=Eleanor::$Language['settings'];
		$extra=$id && $values['protected'] ? array('disabled'=>true) : array();
		if($back)
			$back=Eleanor::Control('back','hidden',$back);
		$langs=array();
		foreach(Eleanor::$langs as $k=>&$v)
			$langs[]='"'.$k.'"';

		$grs='';
		foreach($groups as $k=>&$v)
			$grs.=Eleanor::Option($v,$k,$k==$values['group']);

		$Lst=Eleanor::LoadListTemplate('table-form')
			->begin()
			->item(array($ltpl['name'],Eleanor::$Template->LangEdit($ml['title'],null),'imp'=>true))
			->item($ltpl['descr'],Eleanor::$Template->LangEdit($ml['descr'],null))
			->item(array($lang['group'],Eleanor::Select('group',$grs,array('tabindex'=>3)+$extra),'imp'=>true))
			->item($lang['beg_subg'],Eleanor::$Template->LangEdit($ml['startgroup'],null))
			->item(array($lang['pos'],Eleanor::Edit('pos',$values['pos'],array('tabindex'=>5)),'tip'=>$lang['pos_']))
			->item($lang['priv_name'],Eleanor::Edit('name',$values['name'],array('tabindex'=>6)+$extra))
			->item($lang['prot_o'],Eleanor::Check('protected',$values['protected'],array('tabindex'=>7)+$extra));

		if(Eleanor::$vars['multilang'])
			$Lst->item(array($lang['multilang'],Eleanor::Check('multilang',$values['multilang'],array('onclick'=>'ChangeMultilang()','id'=>'multilang','tabindex'=>7)),'descr'=>$lang['multilang_']))
				->item($ltpl['set_for_langs'],Eleanor::$Template->LangChecks($values['_onelang'],$values['_langs'],'Multilangs',9));

		$general=(string)$Lst->end();

		$evals=(string)$Lst->begin()
			->item(array($lang['eval_load'],'descr'=>sprintf($lang['inc_vars'],'$co,$Obj'),Eleanor::Text('eval_load',$values['eval_load'],$extra+array('style'=>'width:100%')).'<br /><a href="#" onclick="$(this).next(\'div\').toggle();return false">'.$lang['op_example'].'</a><div style="display:none">'.Eleanor::Text('_','if($a[\'multilang\'])
	foreach($a[\'value\'] as &$v)
	{
		#Your code...
		#$v-=10;
	}
else
{
		#Your code...
		#$a[\'value\']-=10;
}
return $a;',array('style'=>'width:100%','readonly'=>'readonly')).'</div>'))
			->item(array($lang['eval_save'],'descr'=>sprintf($lang['inc_vars'],'$co,$Obj'),Eleanor::Text('eval_save',$values['eval_save'],$extra+array('style'=>'width:100%')).'<a href="#" onclick="$(this).next(\'div\').toggle();return false">'.$lang['op_example'].'</a><div style="display:none">'.Eleanor::Text('_','if($a[\'multilang\'])
	foreach($a[\'value\'] as &$v)
	{
		#Your code...
		#$v+=10;
	}
else
{
	#Your code...
	#$a[\'value\']+=10;
}
return $a[\'value\'];',array('style'=>'width:100%','readonly'=>'readonly')).'</div>'))
			->end();

		$c=(string)$Lst->form()
			->tabs(
				array($ltpl['general'],$general),
				array($lang['edit_control'],$control ? $control : null),
				array($lang['evals'],$evals)
			)
			->submitline(
				$back.Eleanor::Button('OK','submit',array('tabindex'=>10))
				.' '.($links['delete'] ? '' : Eleanor::Button($ltpl['delete'],'button',array('tabindex'=>11,'onclick'=>'window.location=\''.$links['delete'].'\'')))
			)
			->endform();

		if($errors)
			foreach($errors as $k=>&$v)
				if(is_int($k) and isset($lang[$v]))
					$v=$lang[$v];
		return Eleanor::$Template->Cover($c,$errors).'<script type="text/javascript">//<![CDATA[
function ChangeMultilang(onlyprev)
{
	if(typeof Multilangs=="undefined")
		return;
	var m=$("#multilang");
	if(onlyprev)
	{
		var old=Multilangs.opts.where;
		Multilangs.opts.where="#edit-control-preview";
		if(m.prop("checked"))
			Multilangs.Click();
		else
			Multilangs.opts.Switch(["'.Language::$main.'"],['.join(',',$langs).'],"#edit-control-preview");
		Multilangs.opts.where=old;
		return;
	}
	if(m.prop("checked"))
	{
		Multilangs.opts.where=document;
		Multilangs.Click();
	}
	else
	{
		Multilangs.opts.where=$("#tab1");//.add($("#tab2 tr.temp").slice(1));
		Multilangs.opts.Switch(["'.Language::$main.'"],['.join(',',$langs).'],$("#edit-control-preview").add($("#tab2 tr.temp").slice()));
	}
}
EC.OnChange=ChangeMultilang;
$(function(){
	$(".linetabs a").Tabs();
	ChangeMultilang();
});//]]></script>';
	}
}