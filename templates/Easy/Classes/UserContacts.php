<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Шаблон для пользователей модуля "обратная связь"
*/
class TplUserContacts
{
	/*
		Основная страница обратной связи

		$canupload - флаг возможности загрузки файла
		$info - информация по обратной связи, заполняемая в админке
		$whom - массив выбора получателя письма. Формат id=>имя получателя
		$values - массив значений формы, ключи:
			subject - тема сообщения
			message - текст сообщения
			whom - идентификатор получателя
			sess - идентификатор сессии
		$bypost - флаг загрузки содержимого из POST запроса
		$errors - массив ошибок
		$captcha - captcha при отправке сообщения
	*/
	public static function Contacts($canupload,$info,$whom,$values,$bypost,$errors,$captcha)
	{
		return'';#ToDo!
		$lang=Eleanor::$Language['contacts'];
		$content=Eleanor::$Template->Menu(array(
			'title'=>$GLOBALS['Eleanor']->module['title'],
		));
		if($info)
		{
			$content->OpenTable();
			$content.=$info.Eleanor::$Template->CloseTable();
		}
		if($whom)
		{
			if($errors)
			{
				foreach($errors as $k=>&$v)
					if(is_int($k) and is_string($v) and isset($lang[$v]))
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
				->item($lang['subject'],Eleanor::Input('subject',$values['subject'],array('tabindex'=>2)))
				->item($lang['message'],$GLOBALS['Eleanor']->Editor->Area('message',$values['message'],array('post'=>$bypost,'no'=>array('tabindex'=>3))));

			if($canupload)
				$Lst->item(array($lang['file'],Eleanor::Input('file',false,array('type'=>'file')),'descr'=>$canupload===true ? '' : sprintf($lang['maxfs'],Files::BytesToSize($canupload))));

			if($captcha)
				$Lst->item(array($lang['captcha'],$captcha.'<br />'.Eleanor::Input('check','',array('tabindex'=>4,'autocomplete'=>'off')),'descr'=>$lang['captcha_']));

			$content.=$Lst->end()->submitline(Eleanor::Input('sess',$values['sess'],array('type'=>'hidden')).Eleanor::Button('OK','submit',array('tabindex'=>5)))->endform();
		}
		return$content;
	}

	/*
		Страница с информацией о том, что сообщение успешно отправлено
	*/
	public static function Sent()
	{
		return'';#ToDo!
		$lang=Eleanor::$Language['contacts'];
		return Eleanor::$Template->Menu(array(
			'title'=>$lang['st'],
		))->Message(sprintf($lang['sent'],$GLOBALS['Eleanor']->Url->Prefix()),'info');
	}
}