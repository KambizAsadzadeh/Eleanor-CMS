<?php
/*
	������� �������. �������� ������� ���������� ���������� �������� ��� �� �������� ����� �������� ���������.

	@var array('values'=>...)
*/
if(!defined('CMS'))die;
$GLOBALS['jscripts'][]='js/jquery.drag.js';
$trs='';
$u=uniqid();
$bs=Eleanor::Button('+','button',array('class'=>'sb-plus')).' '
	.Eleanor::Button('&minus;','button',array('class'=>'sb-minus'),2);
if(!empty($values['options'][1]) and is_array($values['options'][1]))
	foreach($values['options'][1] as $k=>&$v)
		$trs.='<tr><td><img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" /></td><td>'.Eleanor::Edit($values['options'][0].'[name][]',$k,array('style'=>'width:100%')).'</td><td>'.Eleanor::Edit($values['options'][0].'[value][]',$v,array('style'=>'width:100%')).'</td><td style="function">'.$bs.'</td></tr>';
else
	$trs.='<tr><td><img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" /></td><td>'.Eleanor::Edit($values['options'][0].'[name][]','',array('style'=>'width:100%')).'</td><td>'.Eleanor::Edit($values['options'][0].'[value][]','',array('style'=>'width:100%')).'</td><td class="function">'.$bs.'</td></tr>';
$l=Eleanor::$Language['controls'];
return Eleanor::Select($n,Eleanor::Option($l['select_source_code'],'eval',$co['value']=='eval').Eleanor::Option($l['select_source_input'],'opts',$co['value']=='opts'),array('id'=>'s-opts-'.$u))
.'<br /><div id="s-eval-'.$u.'">'.Eleanor::Text(($ise=is_array($values['eval'])) ? $values['eval'][0] : $values['eval'],$ise ? $values['eval'][1] : '').'</div><table id="s-table-'.$u.'" class="tabstyle" style="width:420px"><tr class="first tablethhead"><th></th><th>'.$l['select_value1'].'</th><th>'.$l['select_value'].'</th><th style="width:60px">'.Eleanor::$Language['tpl']['functs'].'</th></tr>'.$trs
.'</table><script type="text/javascript">/*<![CDATA[*/EC.Select("'.$u.'")//]]></script>';