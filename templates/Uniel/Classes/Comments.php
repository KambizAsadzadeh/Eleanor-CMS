<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class TplComments
{	/*
		������� �������: ����������� ������������
		$rights - ������ ���� ������������ � ������������, �����:
			edit - ����� ������������� ���� �����������. ���� ����� - ��� ���������� ������, �� ��������� ������� ����� ��������� ����������� ����� ��������.
			delete - ����� ������� ���� �����������. ���� ����� - ��� ���������� �������� ��������� ������� ����� ��������� ����������� ����� ��������.
			post - ����� ��������� ����� �����������, �������� ���������� ������ ����� ������������: -1 - ��� �������������, 0 - ��� ����������, 1 - ��� ������������, false - ��� ������� ����������
			medit - ����� ������������� ����� �����������
			mdelete - ����� ������� ����� �����������
			ip - ����� ������������� IP � ������� ���� ���������� �����������
			status - ����� ������ ������� ������

		$pagpq  - �� posts+authors+groups+parent+quotes ������ ��������:
			posts - ������ ������������. ������: id=>array(), ����� ����������� �������:
				status - ������ �����������
				parents - ������ ID ���� ��������� �����������
				date - ���� ������������
				answers - ����� ������� �� ������ �����������
				author_id - ������������� ������ �����������
				author - ��� ������ �����������
				ip (��� ������� ����) - IP �����, � �������� ��� �������� �����������
				_n - ���������� ����� �����������
				_afind - ������ �� �����������
				_achilden - ������ �� ����� ������� �����������
				_edit - ���� ����������� �������������� �����������
				_delete - ���� ����������� �������� �����������
			authors - ������ ������� ���� ������������. ������ id=>array(), ����� ����������� �������:
				_group - ������������� ������ ������
				name - ��� ������ (�� ���������� HTML)
				signature - ������� ������
				avatar_location - �������������� �������
				avatar_type - ��� ������� (uploaded,url,local)
				_online - ���� ������� ������������ ������
			groups - ������ ����� ������� ���� ������������. ������ id=>array(), ����� ����������� �������:
				title - �������� ������
				html_pref - HTML ������� ������
				html_end - HTML ��������� ������
			parent - ������ ������������� �����������, �����:
				id - ������������� �����������
				�������� ��������� ������ (status, parents, date, answers, author_id, author, ip, text, _edit, _delete, _afind, _n) �������� ����.
			quotes - ������ ����� �� ������������ ������������ (������������, ������� �� �������, �������� ������� �����������). ������ id=>text.
				������ ������������� � ������������ ������� (����������� 1 � 2):
					����������� 1
						����������� 2: ����� �� ����������� 1:
								����������� 3 (������� �����������, �� ������ � ������ ������): ����� �� ����������� 2
				������ ������ �������� � ���� ������ <!-- SUBQUOTE --> ��� ������� ���������.
		$postquery - ���� ������ ���������� ������ ���� ������� � $_POST ������� ��� ajax �������
		$dataquery - ���������� ajax-������� ������ ���� �������� ������� POST � ���� ������
		$cnt - ���������� ������������ �����
		$pp - ���������� ������������ �� ��������
		$page - ����� ������� �������� �� ������� �� ���������
		$reverse - ���� ��������� ������ ������������ "����� ������"
		$statuses - ������ ���������� ������������ ������� �������. ����� ������� - �������� ��������� ������� ������������
		$gname - ��� �����, ���� ����� ��� �������������, ��� ���������� ����� false
		$captcha - ����� ��� ��������� �����������
		$links
			first_page - ������ �� ������ �������� ������������
			pages - ������ �� ������ ����������� �������� ������������
	*/	public static function ShowComments($rights,$pagpq,$postquery,$dataquery,$cnt,$pp,$page,$pages,$reverse,$statuses,$gname,$captcha,$links)
	{		array_push($GLOBALS['jscripts'],'js/eleanor_comments.js','js/eleanor_comments-'.Language::$main.'.js');		$lang=Eleanor::$Language['comments'];

		$editor='';
		if($rights['post']!==false)
		{			$Lst=Eleanor::LoadListTemplate('table-form');			$editor.=
				($rights['post']==-1 ? Eleanor::$Template->Message($lang['needch'],'info') : '')
				.$Lst->form(array('id'=>'newcomment'))->begin()
				.'<tr class="infolabel first"><td colspan="2" class="answerto">'.$lang['addc'].'</td></tr>'
				.($gname===false ? '' : $Lst->item($lang['yn'],Eleanor::Edit('name',$gname,array('tabindex'=>1))))
				.$Lst->item($lang['yc'],$GLOBALS['Eleanor']->Editor->Area('text','',array('bb'=>array('tabindex'=>2))))
				.($captcha ? $Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>3)),'descr'=>$lang['captcha_'])) : '')
				.$Lst->button(Eleanor::Control('parent','hidden',$pagpq[3] ? $pagpq[3]['id'] : 0).Eleanor::Button($lang['addc']))
				->end()->endform();
		}
		$pager=$reverse ? Eleanor::$Template->Pages(array($cnt,$pages=>$links['first_page'],'hash'=>'comments'),$pp,-$page,$links['pages'],'C.GoToPage') : Eleanor::$Template->Pages(array($cnt,$links['firt_page'],'hash'=>'comments'),$pp,$page,$links['pages'],'C.GoToPage');
		if($pagpq[3])
			Eleanor::LoadOptions('user-profile');		return Eleanor::$Template->Title($lang['vc']).'<div id="comments">'
			.($rights['status'] ? '<div class="moderate"'.($pagpq[0] ? '' : ' style="display:none"').'>'.static::CommentsModerate($rights,$lang).'</div>' : '')
			.($pagpq[3] ? '<div class="parent">'.static::CommentsPost($rights,$pagpq[3]['id'],$pagpq[3],true,$pagpq[1],$pagpq[2],$pagpq[4],$lang).'</div>' : '')
			.'<div class="nocomments"'.($pagpq[0] ? ' style="display:none">' : '>'.Eleanor::$Template->Message($pagpq[3] ? $lang['anc'] : $lang['nc'],'info')).'</div>'
			.'<div class="comments'.($pagpq[3] ? ' children' : '').'"'.($pagpq[0] ? '>'.static::CommentsPosts($rights,$pagpq,$lang) : ' style="display:none">').'</div>'
			.'<div class="paginator"'.($pager ? '>'.$pager : ' style="display:none">').'</div>
			<div class="status" id="commentsinfo"></div><div style="text-align:center;margin-bottom:15px"><a href="#" class="link-button cb-lnc" style="width:250px"><b>'.$lang['lnp'].'</b></a></div>'
			.$editor.'</div><script type="text/javascript">/*<![CDATA[*/var C;$(function(){C=new CORE.Comments('.Eleanor::JsVars(array(
				'lastpost'=>time(),
				'postquery'=>$postquery,
				'!dataquery'=>'["'.join('","',$dataquery).'"]',
				'nextn'=>$statuses[1]+$statuses[0],
				'reverse'=>$reverse,
				'page'=>$page,
				'pages'=>$pages,
				'baseurl'=>$links['first_page'],
				'parent'=>$pagpq[3] ? (int)$pagpq[3]['id'] : 0,
			),false,true,'').')})//]]></script>';	}

	/*
		������� �������: "���������" ��� ���������� ������������
		$diff - ������ ������� �������� ������� � ����� ��������������� �����������, �����:
			0 - ����
			1 - ������
			2 - ���
			3 - ����
			4 - ������
			5 - �������
	*/
	public static function CommentsAddedAfter($diff)
	{		return'<br /><br /><span class="small">'.call_user_func_array(Eleanor::$Language['comments']['added_after'],$diff).':</span><br />';	}

	/*
		������� �������. ���������� ������
		$q - ������ ������, �����:
			name - ��� ������������
			date - ���� ������
			find - ������ �� ������������ �����������
			id - ������������� �����, ������� ����������
			text - ����� ������
	*/
	public static function CommentsQuote($q)
	{		return'<blockquote class="extend"><div class="top">'
		.sprintf(
			Eleanor::$Language['comments']['cite'],
			($q['name'] || $q['date'] ? ' ('.$q['name'].' @ '.$q['date'].')' : '')
			.($q['id'] ? ' <a href="'.$q['find'].'" data-id="'.$q['id'].'" class="cb-gocomment" target="_blank"><img src="'.Eleanor::$Template->default['theme'].'images/findpost.gif" /></a>' : '')
		)
		.'</div><div class="text">'.$q['text'].'</div></blockquote>';
	}

	/*
		������� �������: �������� ����� ������������
		�������� �������� ���������� �������� � ������ ShowComments (����).
	*/
	public static function CommentsLNC($rights,$pagpq)
	{
		if($pagpq[3])
			Eleanor::LoadOptions('user-profile');
		return array(
			'moderate'=>$rights['status'] && $pagpq[0] ? static::CommentsModerate($rights) : '',
			'comments'=>$pagpq[0] ? static::CommentsPosts($rights,$pagpq) : '',
		);
	}

	/*
		������� �������: �������� �������� �� AJAX.
		�������� �������� ���������� �������� � ������ ShowComments (����).
	*/
	public static function CommentsLoadPage($rights,$pagpq,$cnt,$pp,$page,$pages,$reverse,$parent,$links)
	{
		$r=array('paginator'=>$reverse ? Eleanor::$Template->Pages(array($cnt,$pages=>$links['first_page'],'hash'=>'comments'),$pp,-$page,$links['pages'],'C.GoToPage') : Eleanor::$Template->Pages(array($cnt,$links['first_page'],'hash'=>'comments'),$pp,$page,$links['pages'],'C.GoToPage'));
		if($pagpq)
		{
			if($pagpq[0])
				$r['comments']=static::CommentsPosts($rights,$pagpq);
			else
			{
				$lang=Eleanor::$Language['comments'];
				$r['moderate']=$r['comments']='';
				$r['nocomments']=Eleanor::$Template->Message($parent ? $lang['anc'] : $lang['nc'],'info');
			}
		}
		return$r;
	}

	/*
		������� �������: ����� �������������� �����������
		$a - ������ �������������� �����������, �����:
			id - ������������� �����������
			status - ������ �����������
			date - ���� �����������
			author_id - ������������� ������ �����������
			author - ��� ������ �����������
			text - ����� �����������
	*/
	public static function CommentsEdit($a)
	{
		$lang=Eleanor::$Language['comments'];
		return'<form>'.$GLOBALS['Eleanor']->Editor->Area('text'.$a['id'],$a['text']).'<div style="text-align:center">'.Eleanor::Button($lang['save']).' '.Eleanor::Button(Eleanor::$Language['tpl']['cancel'],'button',array('class'=>'cb-cancel')).'</div></form>';
	}

	/*
		������� �������: ����� �����������, ����� ��� ���������� (��������������)
			$text - ����� �������� �����������
			$quotes - ������ ����� �� ������������ ������������ (������������, ������� �� ������� �������� ������� �����������). ������ id=>text.
				������ ������������� � ������������ ������� (����������� 1 � 2):
					����������� 1
						����������� 2: ����� �� ����������� 1:
							����������� 3 (������� �����������, �� ������ � ������ ������): ����� �� ����������� 2
			������ ������ �������� � ���� ������ <!-- SUBQUOTE --> ��� ������� ���������.
	*/
	public static function CommentsAfterEdit($text,$quotes)
	{
		$pq='';
		if(count($quotes)>2)
			array_splice($quotes,-2);
		foreach($quotes as &$v)
			$pq=str_replace('<!-- SUBQUOTE -->',$pq,$v);
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);
		return$pq.$text;
	}

	protected static function CommentsModerate($rights)
	{
		$lang=Eleanor::$Language['comments'];
		$GLOBALS['jscripts'][]='js/checkboxes.js';
		return Eleanor::Select('',Eleanor::Option($lang['withsel'],'').Eleanor::Option($lang['doact'],1).Eleanor::Option($lang['toblock'],0).Eleanor::Option($lang['tomod'],-1).($rights['mdelete'] ? Eleanor::Option(Eleanor::$Language['tpl']['delete'],'delete') : ''),array('class'=>'modevent')).' '.Eleanor::Check('',false,array('id'=>'masscheck'));
	}

	protected static function CommentsPosts($rights,$pagpq)
	{		$c='';
		if($pagpq[0] and !$pagpq[3])
			Eleanor::LoadOptions('user-profile');
		$mass=$rights['status'] || $rights['mdelete'];
		foreach($pagpq[0] as $k=>&$v)
			$c.=static::CommentsPost($rights,$k,$v,$mass,$pagpq[1],$pagpq[2],$pagpq[4]);		return$c;
	}

	protected static function CommentsPost($rights,$id,$c,$mass,$authors,$groups,$quotes)
	{		$lang=Eleanor::$Language['comments'];
		$ltpl=Eleanor::$Language['tpl'];		$author=isset($authors[$c['author_id']]) ? $authors[$c['author_id']] : false;
		$group=$author && isset($groups[$author['_group']]) ? $groups[$author['_group']] : false;
		switch($author && $author['avatar_location'] ? $author['avatar_type'] : '')
		{
			case'local':
				$avatar='images/avatars/'.$author['avatar_location'];
			break;
			case'upload':
				$avatar=Eleanor::$uploads.'/avatars/'.$author['avatar_location'];
			break;
			case'url':
				$avatar=$author['avatar_location'];
			break;
			default:
				$avatar=Eleanor::$vars['noavatar'];
		}
		static$maw,$mah;
		if(!isset($maw,$mah))
			list($maw,$mah)=explode(' ',Eleanor::$vars['avatar_size']);

		switch($c['status'])
		{
			case -1:
				$status='<span style="color:orange;font-weight:bold">'.$lang['stmodwait'].'</span>';
				$data['postn']='?';
			break;
			case 0:
				$status='<span style="color:red;font-weight:bold">'.$lang['stblocked'].'</span>';
			break;
			default:
				$status='';
		}
		$ip=$rights['ip'] ? '<a href="http://eleanor-cms.ru/whois/'.$c['ip'].'" target="_blank">'.$c['ip'].'</a> ' : '';

		$pq='';
		if(count($c['parents'])>2)
			array_splice($c['parents'],0,-2);
		foreach($c['parents'] as &$pv)
			$pq=isset($quotes[$pv]) ? str_replace('<!-- SUBQUOTE -->',$pq,$quotes[$pv]) : '';
		$pq=str_replace('<!-- SUBQUOTE -->','',$pq);
		return'<div class="comment" id="comment'.$id.'">
<div class="binner">
	<div class="avatarcol">
		<div class="lcolomn">
			<div class="avatarbox"><img style="max-width:'.$maw.';max-height:'.$mah.';" src="'.$avatar.'" title="'.$c['author'].'" />'.($author && $author['_online'] ? '<br /><span style="color:green">Online</span>' : '').'</div>
		</div>
		<div class="rcolomn">
			<div class="heading">
				<span class="argr">'
				.($c['_n'] ? '#<a href="'.$c['_afind'].'" class="cb-findcomment">'.($c['status'] ? $c['_n'] : '?').'</a>' : '')
				.($mass && in_array($c['status'],array(-1,0,1)) ? ' '.Eleanor::Check('mass[]',false,array('value'=>$id)) : '')
				.'</span><h2>'
				.Eleanor::$Language->Date($c['date'],'fdt').', '.($group ? '<a href="'.Eleanor::$Login->UserLink($author['name'],$c['author_id']).'" title="'.$group['title'].'" class="cb-insertnick">'.$group['html_pref'].$c['author'].$group['html_end'].'</a>' : '<span class="cb-insertnick">'.$c['author'].'</span>').' </h2>'
				.($status || $ip ? '<div class="moreinfo">'.$ip.$status.'<div class="clr"></div></div>' : '')
				.'</div>
			<div class="maincont"><div class="text">'.$pq.$c['text'].'</div>'
			.($author && $author['signature'] ? '<div class="clr"></div><p class="signature">-----<br />'.$author['signature'].'</p>' : '')
			.'</div>
		</div>
		<div class="clr"></div>
	</div>
	<div class="commentinfo buttons">'
		.($c['_achilden'] ? '<a href="'.$c['_achilden'].'#comments" class="answers">'.$lang['answers']($c['answers']).'</a>' : '')
		.($c['status']==1 && $rights['post']
			? '<span class="argr"><a href="#" class="cb-qquote" data-id="'.$id.'" data-date="'.$c['date'].'" data-name="'.$c['author'].'">'.$lang['qquote'].'</a></span>'
				.(isset($c['_n']) ? '<span class="argr"><a href="#" class="cb-answer" data-id="'.$id.'">'.$lang['answer'].'</a></span>' : '')
			: '')
		.($c['_edit'] ? '<span class="argr"><a href="#" class="cb-edit" data-id="'.$id.'">'.$ltpl['edit'].'</a></span>' : '')
		.($c['_delete'] ? '<span class="argr"><a href="#" class="cb-delete" data-id="'.$id.'"'.(isset($c['_n']) ? '' : ' data-recount="1"').'>'.$ltpl['delete'].'</a></span>' : '')
		.'<div class="clr"></div>
	</div>
</div></div>';	}
}