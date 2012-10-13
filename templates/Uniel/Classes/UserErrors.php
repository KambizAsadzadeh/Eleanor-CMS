<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������������� ���������� ������ ������� ������
*/
class TPLUserErrors
{	/*
		����� �������� ������
		$a - ��������� ������, ������ � �������:
			id - ������������� ������ � ��
			http_code - HTTP ��� ������
			image - ������� ������
			mail - e-mail, ���� ���������� ��������� ��������� �� �������������
			log - ���� ����������� ������
			title - �������� �������� ������
			text - ����� � ���������� ������
			meta_title - ��������� ����
			meta_descr - meta description
		$info - ���������� �� �������� ���������, ������ � �������:
			sent - ���� �������������� ���������
			error - ������ ��������, ���� ������ ������ - ������ �� ���
			text - ����� ���������
			back - URI ��������
			name - ��� �����
		$captcha - captcha ��� �������� ������
	*/
	public static function ShowError($a,$info,$captcha)
	{		$lang=Eleanor::$Language['merror'];		if($info['sent'])
			$tosend='<hr /><br />'.Eleanor::$Template->Message($lang['sent'],'info');
		elseif($a['mail'])
			$tosend='<hr />'.($info['error'] ? Eleanor::$Template->Message($info['error'],'error') : '')
				.'<form method="post">'
				.(Eleanor::$Login->IsUser() ? '' : '<div class="errorinput"><span>'.$lang['yourname'].'</span><br />'.Eleanor::Edit('name',$info['name']).'</div><br />')
				.'<div class="errorinput"><span>'
				.$lang['tell_us'].'</span><br />'.$GLOBALS['Eleanor']->Editor->Area('text',$info['text']).'</div>'
				.($info['back'] ? Eleanor::Control('back','hidden',$info['back']) : '')
				.($captcha ? '<br /><div class="errorinput"><span>'.$lang['captcha'].'</span><br /><span class="small">'.$lang['captcha_'].'</span><br />'.Eleanor::Edit('check','').'<br />'.$captcha.'</div>' : '')
				.'<div style="text-align:center;"><a href="#" onclick="$(this).closest(\'form\').submit();return false;" class="button">'.$lang['send'].'</a></div></form>';
		else
			$tosend='';
		return '<div class="base"><div class="heading2"><div class="binner"><h6>'.$a['title']
			.'</h6><div class="clr"></div></div></div><div class="maincont"><div class="binner">'
			.($a['image'] ? '<img style="float:left;margin-right:10px;" src="images/errors/'.$a['image'].'" alt="'.$a['title'].'" title="'.$a['title'].'" />' : '')
			.$a['text'].'<div class="clr"></div>'.$tosend
			.'<div class="clr"></div></div></div><div class="morelink"><div class="binner">'
			.($info['back'] ? '<a href="'.$info['back'].'"><b>'.$lang['back'].'</b></a><br />' : '')
			.'<div class="clr"></div></div></div></div>';	}
}