<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Strings;

/** Сохранение текста, логическое продолжение класса editor */
class Saver extends \Eleanor\Classes\SafeHtml
{use Traits\Editor;

	/** @var bool Автоматическое прописывание rel="nofollow" ко всем внешним ссылкам */
	public static $nofollow=false;

	/** @var mixed Внутреннее подтверждение парсинга смайлов для проброса не static параметров в static */
	protected static $smile;

	/** Получение результата работы редактора - HTML разметки
	 * @param string $text Текст из POST запроса. Пример: $t=isset($_POST['t'] ? $Saver->Get($_POST['t']) :'';
	 * @return string */
	public function Save($text)
	{
		$text=str_replace("\r",'',$text);

		#Цензура
		if(Eleanor::$vars['bad_words'])
		{
			$repl=[];

			foreach(explode(',',trim(Eleanor::$vars['bad_words'],',')) as $v)
			{
				$v=trim($v);

				if(!$v)
					continue;

				$repl[]='#(?<![\w<])'.preg_quote($v,'#').'(?!\w)#i';
			}

			$text=preg_replace($repl,Eleanor::$vars['bad_words_replace'],$text);
		}
		#/Цензура

		if($this->ownbb)
		{
			OwnBB::$opts['visual']=$this->visual;
			$text=OwnBB::StoreNotParsed($text,OwnBB::SAVE);
		}

		switch($this->type)
		{
			case'bb':#ББ редактор
				$text=htmlspecialchars($text,ENT_NOQUOTES | ENT_HTML5 | ENT_SUBSTITUTE|ENT_DISALLOWED,\Eleanor\CHARSET);
				$text=preg_replace('#&lt;!\-\- NP (\d+) \-\-&gt;#','<!-- NP \1 -->',$text);#Fix для noparse OwnBB
				$text=\Eleanor\Classes\BBCode::BB2HTML($text);
			break;
			case'tinymce':
			case'ckeditor':
			break;
			case'codemirror':
			default:#Без ББ редактора
				$text=htmlspecialchars($text,ENT_NOQUOTES | ENT_HTML5 | ENT_SUBSTITUTE|ENT_DISALLOWED,\Eleanor\CHARSET);
				$text=preg_replace('#&lt;!\-\- NP (\d+) \-\-&gt;#','<!-- NP \1 -->',$text);#Fix для noparse OwnBB
		}

		if($this->smiles and !static::$smile)
			static::$smile=null;
		elseif(!$this->smiles and static::$smile)
			static::$smile=[];

		$text=static::Make($text);

		if($this->ownbb)
		{
			$text=OwnBB::ParseBBCodes($text,OwnBB::SAVE);
			$text=OwnBB::ParseNotParsed($text,OwnBB::SAVE);
		}

		return$text;
	}

	/** Обработчик автоматической обработки ссылок */
	protected static function AutoParseURL($m)
	{
		$m[2]=trim($m[2]);

		if(!$m[2] or !filter_var($m[2],FILTER_VALIDATE_URL))
			return$m[0];

		$text=$m[2];
		if(strlen($text)>55)
			$text=substr($text,0,35).'...'.substr($text,-15);

		return$m[1].'<a href="'.$m[2].'">'.htmlspecialchars($text,ENT,\Eleanor\CHARSET,false).'</a>';
	}

	/** Обработчик текста вне ссылок, скриптов и textarea.
	 * @param string $text Текст
	 * @return string */
	protected static function PlainTextHandler($text)
	{
		if(Eleanor::$vars['autoparse_urls'])
			$text=preg_replace_callback('#(^|\s|>|\](?<!\[url\]))([a-z]{3,10}://[\wa-z0-9/\._\-&=\?:_;\#]+)#i',
				'static::AutoParseURL',$text);

		if(!isset(static::$smile))
			static::$smile=static::GetSmiles();

		if(static::$smile)
			foreach(static::$smile as $v)
			{
				$sp=0;

				foreach($v['emotion'] as &$emo)
					while(false!==$p=mb_strpos($text,$emo,$sp))
					{
						$emlen=mb_strlen($emo);
						$sp=$p;

						if(($p===0 or preg_match('#\w#',mb_substr($text,$p-1,1))==0)
							and (mb_substr($text,$p+$emlen,1)=='' or preg_match('#\w#',mb_substr($text,$p+$emlen,1))==0))
						{
							$em='<img class="smile" alt="'.($tmp=htmlspecialchars($emo,ENT,\Eleanor\CHARSET))
								.'" title="'.$tmp.'" src="'.Template::$http['static'].$v['path'].'" />';
							$text=substr_replace($text,$em,strlen(mb_substr($text,0,$p)),strlen($emo));
							$sp+=mb_strlen($em)-$emlen;
						}
						else
							$sp+=$emlen;
					}
			}

		return$text;
	}
}