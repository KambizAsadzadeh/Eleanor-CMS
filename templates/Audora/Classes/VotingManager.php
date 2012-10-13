<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ���������� �������. ������ ������� ������ �������� ������ �� ������ ������ Voting_Manager, �� �������� ���������� ����� ��������,
	���������� ����� � ������. �������� ������� ����� ������ ����� ������, ������ ���� core/others/voting_manager.php
*/
class TplVotingManager
{	/*
		������� �������: ����������/�������������� ������
		$id - ������������� �������������� ������, ���� $id==0 ������ ����� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$lang - �������� ������
	*/
	public static function VmAddEdit($id,$controls,$values,$lang)
	{
		array_push($GLOBALS['jscripts'],'js/voting_manager.js','js/jquery.drag.js');

		$Lst=Eleanor::LoadListTemplate('table-form')->begin()->head($lang['questions']);
		foreach($controls as $k=>&$v)
			if($k!='_questions' and $values[$k])
				if(is_array($v))
					$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
				else
					$Lst->head($v);

		$u=uniqid('vo-');
		return'<div id="'.$u.'">'.$Lst->end().$values['_questions'].'</div><script type="text/javascript">/*<![CDATA[*/$(function(){VotingManager("'.$u.'");})//]]></script>';
	}

	/*
		���������� �������: ���� ���� �������� ������
		$questions - ������ ���� �������� ������. ������: ����� ������=>�������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$lang - �������� ������r
	*/
	public static function VmQuestions($questions,$controls,$lang)
	{
		$Lst=Eleanor::LoadListTemplate('table-form');
		foreach($questions as $kq=>&$values)
		{
			$Lst->begin(array('class'=>'tabstyle tabform question','data-qn'=>$kq));
			foreach($controls as $k=>&$v)
				if(!empty($values[$k]))
					if(is_array($v))
						$Lst->item(array($v['title'],Eleanor::$Template->LangEdit($values[$k],null),'descr'=>$v['descr']));
					else
						$Lst->head($v);

			$Lst->button(Eleanor::Button($lang['addq'],'button',array('class'=>'addquestion')).' '.Eleanor::Button($lang['delq'],'button',array('class'=>'deletequestion')))->end();
		}
		return$Lst;
	}

	/*
		������� ����������� �������: ������������ ��������� ������ ��� ������� �������
		$variants - �������� �������. ������: ����� ��������=>������� ������
		$vn - ������� ���� ��������� ��������� ������
		$answers - ���������� ������� ������� ��������. ������: ����� ��������=>���������� �������. ����� �� �������������, � ������, ���� ��� ��������� ��������� $noans
		$an - ������� ���� ��������� ��������� ������
		$ti - tabindex
		$real - ����� �������������, ������� ������� ������������� �� ���� �����. ������� ������������� ������, ��� �� � ������� ������� ���� ������
			������ ��� ������� ��������, � �������� ������������ �������� ���� �������. ������: ����� ��������=>����� ���������������
		$noans - ���� ������� �������������� ���������� ������� ��� ������� ��������
		$lang - �������� ������
	*/
	public static function VmVariants($variants,$vn,$answers,$an,$ti,$real,$noans,$lang)
	{
		$ltpl=Eleanor::$Language['tpl'];
		$Lst=Eleanor::LoadListTemplate('table-list');
		$n1=$a1=$a2=$k=0;
		$v2=$v1='';
		$n2=$n=1;

		foreach($variants as $k=>&$va)
		{
			$ans=isset($answers[$k]) ? $answers[$k] : '';
			switch($n++)
			{
				case 1:
					$a1=$ans;
					$v1=$va;
					$n1=$k;
					$n2=$k+1;
				break;
				case 2:
					$a2=$ans;
					$v2=$va;
					$n2=$k;
				break;
				default:
					$Lst->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Edit($vn.'['.$k.']',$va,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$k)),$noans ? false : Eleanor::Control($an.'['.$k.']','number',$ans,array('min'=>0,'tabindex'=>$ti,'style'=>'width:50px','class'=>'number'.$k,'data-class'=>'number'.$k,'title'=>isset($real[$k]) ? $lang['rvoters']($real[$k]) : $lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2));
			}
		}
		$c=(string)$Lst->end();
		return$Lst->begin(
				array($lang['va'],'colspan'=>2,'style'=>'min-width:170px','tableaddon'=>array('class'=>'tabstyle variants','data-max'=>max($k,$n1,$n2))),
				$noans ? false : $lang['votes'],
				'&nbsp;'
			)
			->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Edit($vn.'['.$n1.']',$v1,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$n1)),$noans ? false : Eleanor::Control($an.'['.$n1.']','number',$a1,array('min'=>0,'style'=>'width:50px','tabindex'=>$ti,'class'=>'number'.$n1,'title'=>isset($real[$n1]) ? sprintf($lang['rvoters'],$real[$n1]) : $lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2))
			->item(array('<img src="'.Eleanor::$Template->default['theme'].'images/updown.png" class="updown" />','style'=>'width:1px'),Eleanor::Edit($vn.'['.$n2.']',$v2,array('style'=>'width:100%','tabindex'=>$ti,'class'=>'variant'.$n2)),$noans ? false : Eleanor::Control($an.'['.$n2.']','number',$a2,array('min'=>0,'style'=>'width:50px','tabindex'=>$ti,'class'=>'number'.$n2,'title'=>isset($real[$n2]) ? sprintf($lang['rvoters'],$real[$n2]) : $lang['norv'])),Eleanor::Button('+','button',array('class'=>'sb-plus')).' '.Eleanor::Button('&minus;','button',array('class'=>'sb-minus','title'=>$ltpl['delete']),2))
			.$c;
	}
}