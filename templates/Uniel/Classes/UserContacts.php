<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������������� ������ "�������� �����"
*/
class TplUserContacts
{	/*
		�������� �������� �������� �����

		$canupload - ���� ����������� �������� �����
		$info - ���������� �� �������� �����, ����������� � �������
		$whom - ������ ������ ���������� ������. ������ id=>��� ����������
		$values - ������ �������� �����, �����:
			subject - ���� ���������
			message - ����� ���������
			whom - ������������� ����������
			sess - ������������� ������
		$bypost - ���� �������� ����������� �� POST �������
		$errors - ������ ������
		$captcha - captcha ��� �������� ���������
	*/	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$captcha)
	{		$lang=Eleanor::$Language['contacts'];		$content=Eleanor::$Template->Menu(array(
			'title'=>$GLOBALS['Eleanor']->module['title'],
		));
		if($info)
		{
			$content->OpenTable();
			$content.=$info.Eleanor::$Template->CloseTable();
		}
		if($whom)
		{			if($errors)
			{
				foreach($errors as $k=>&$v)
					if(is_int($k) and isset($lang[$v]))
						$v=$lang[$v];
				$content.=Eleanor::$Template->Message($errors,'error');
			}

			$wh='';
			if(count($whom)>1)
				foreach($whom as $k=>&$v)
					$wh.=Eleanor::Option($v,$k,$k==$values['whom']);

			$Lst=Eleanor::LoadListTemplate('table-form')->form($canupload ? array('enctype'=>'multipart/form-data') : array())->begin();
			if($wh)
				$Lst->item($lang['whom'],Eleanor::Select('whom',$wh,array('tabindex'=>1)));
			$Lst
				->item($lang['subject'],Eleanor::Edit('subject',$values['subject'],array('tabindex'=>2)))
				->item($lang['message'],$GLOBALS['Eleanor']->Editor->Area('message',$values['message'],array('bypost'=>$bypost,'no'=>array('tabindex'=>3))));

			if($canupload)
				$Lst->item(array($lang['file'],Eleanor::Control('file','file'),'descr'=>$canupload===true ? '' : sprintf($lang['maxfs'],Files::BytesToSize($canupload))));

			if($captcha)
				$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Edit('check','',array('tabindex'=>4)),'descr'=>$lang['captcha_']));

			$content.=$Lst->end()->submitline(Eleanor::Control('sess','hidden',$values['sess']).Eleanor::Button('OK','submit',array('tabindex'=>5)))->endform();
		}
		return$content;
	}

	/*
		�������� � ����������� � ���, ��� ��������� ������� ����������
	*/
	public static function Sent()
	{		$lang=Eleanor::$Language['contacts'];
		return Eleanor::$Template->Menu(array(
			'title'=>$lang['st'],
		))->Message(sprintf($lang['sent'],$GLOBALS['Eleanor']->Url->Prefix()),'info');	}
}