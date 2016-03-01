<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Вывод содержимого в браузер, сборник методов для выдачи информации через http */
class Output extends Eleanor\BaseClass
{
	/** Отдача содержимого клиенту в виде файла
	 * @param array $a Опции передачи, может содержать следующие ключи:
	 *  [string data] Данные, которые необходимо передать
	 *  [string file] Путь к фалу, который будет передан пользователю. Имеет приоритет над data
	 *  [string filename] Имя файла, которое будет отображено пользователю
	 *  [int last-modified] Timestamp последнего изменения файла, по умолчанию Сегодня 00:00. Важно для мультипоточности.
	 *  [bool multithread] Флаг разрешения мультипоточности при скачивании
	 *  [string mimetype] Mimetype для передачи файла
	 *  [string etag Etag]
	 *  [bool save] Флаг отображения пользователю диалога сохранения файла (Для картинок рекомендуется false)
	 * @throws EE */
	public static function Stream(array$a)
	{
		$a+=[
			'data'=>'',
			'file'=>'',
			'filename'=>false,
			'last-modified'=>mktime(0,0,0),
			'multithread'=>true,
			'mimetype'=>false,
			'etag'=>false,
			'save'=>true,
		];

		if(headers_sent())
			throw new EE('Headers already sent',EE::DEV);

		if(!$a['filename'])
			if($a['file'])
				$a['filename']=basename($a['file']);
			elseif($a['save'])
				throw new EE('Filename is missing',EE::DEV);

		if(!$a['data'] and !is_file($a['file']))
			throw new EE('No file '.$a['filename'],EE::DEV);

		$type=strpos($a['filename'],'.')===false ? false : strrchr($a['filename'],'.');

		if(!$a['mimetype'])
			$a['mimetype']=$type ? Types::MimeTypeByExt($type,'auto-detect') : 'auto-detect';

		ignore_user_abort(false);

		$size=$a['data'] ? strlen($a['data']) : filesize($a['file']);
		$zsize=$size-1;#Размер, включая 0 байт
		$etag=$a['etag'] ? $a['etag'] : md5($a['filename'].$a['mimetype']);
		$lm=$a['file'] ? filemtime($a['file']) : $a['last-modified'];

		$fn=preg_match('#^[a-z0-9\-_\.\(\)]+$#i',$a['filename'])>0
			? $a['filename']
			: '=?'.Eleanor\CHARSET.'?B?'.base64_encode(Eleanor\W && Eleanor\UTF8
				? mb_convert_encoding($a['filename'],'utf-8','cp1251') : $a['filename']).'?=';

		header('Accept-Ranges: '.($a['multithread'] ? 'bytes' : 'none'));
		header('Connection: '.($a['multithread'] ? 'keep-alive' : 'close'));
		header('Content-Type: '.$a['mimetype']);
		header('Content-encoding: none');
		header('Etag: '.$etag);
		header('Date: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s',$lm).' GMT');

		if($a['save'])
			header('Content-Disposition: attachment; filename="'.$fn.'"');
		else
			header('Content-Disposition: inline; filename="'.$fn.'"');

		$ifr=isset($_SERVER['HTTP_IF_RANGE']) ? trim($_SERVER['HTTP_IF_RANGE']) : false;

		if($ifr and $ifr==$etag || strtotime($ifr)==$lm)
			$ifr=false;

		if($a['multithread'] and isset($_SERVER['HTTP_RANGE']) and !$ifr)
		{
			#Поддержка мультипромежуточного запроса
			$range=[];
			$m=preg_match('/^bytes=((?:\d*-\d*,?\s?)+)/',$_SERVER['HTTP_RANGE'],$m)>0 ? explode(',',$m[1]) : [];

			foreach($m as $v)
			{
				$v=explode('-',trim($v),2);

				$v[0]=(int)$v[0];
				$v[1]=(int)$v[1];

				if($v[0]>$zsize or $v[1]>$zsize or $v[0]>$v[1])
					continue;

				if($v[1]===0)
					$v[1]=$zsize;

				$range[]=$v;
			}

			#Отсеим пересекающиеся промежутки т.е. 500-799,600-1023,800-849 => 500-1023
			foreach($range as $k1=>&$v1)
				foreach($range as $k2=>&$v2)
					if($k1!==$k2 and $v2[0]>=$v1[0] and $v2[0]<=$v1[1])
					{
						if($v1[1]<$v2[1])
							$v1[1]=$v2[1];
						unset($range[$k2]);
					}

			#Сбойные учатки? Пошлем такой запрос нах.й
			if(!$range)
			{
				header('HTTP/1.1 416 Requested range not satisfiable');
				die;
			}

			$s='';
			$total=0;

			foreach($range as &$v)
			{
				$s.=$v[0].'-'.$v[1].',';
				$total+=$v[1]-$v[0]+1;

				#Преобразуем в offset и length
				$v[1]-=$v[0]-1;
			}

			header('Content-Length: '.$total,true,206);
			header('Content-Range: bytes '.rtrim($s,',').'/'.$size);
		}
		else
		{
			header('Content-Length: '.$size,true,200);
			$range=[[0,$size]];
		}

		if($a['data'])
			foreach($range as &$r)
			{
				echo substr($a['data'],$r[0],$r[1]);
				flush();
			}
		else
		{
			$f=fopen($a['file'],'rb');
			foreach($range as &$r)
			{
				fseek($f,$r[0],SEEK_SET);
				$d=0;
				while(!feof($f) and connection_status()==0 and $d<$r[1])
				{
					$b=min(1024*16,$r[1]-$d);
					$d+=$b;
					echo fread($f,$b);
					flush();
				}
			}
			fclose($f);
		}
	}

	/** Проверка возможности вернуть браузеру его кэш
	 * @param string|false $etag
	 * @param string|int $modified Дата последнего изменения страницы
	 * @param bool $includes Флаг проверки даты у всех проинклуженых файлов
	 * @return bool */
	public static function TryReturnCache($etag,&$modified=0,$includes=true)
	{
		if(!$includes and !isset($_SERVER['HTTP_IF_NONE_MATCH'],$_SERVER['HTTP_IF_MODIFIED_SINCE']))
			return false;

		$ifmod=isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : 0;
		$iftag=isset($_SERVER['HTTP_IF_NONE_MATCH']) ? (string)$_SERVER['HTTP_IF_NONE_MATCH'] : '';

		if(!$modified)
			$modified=$ifmod;
		elseif(is_string($modified))
			$modified=strtotime($modified);

		if(!$etag)
			$etag=md5($_SERVER['REQUEST_URI']);

		if($includes)
			foreach(get_included_files() as $file)
				$modified=max($modified,filemtime($file));

		if($ifmod && $modified && $modified<=$ifmod && $iftag && $etag==$iftag)
		{
			header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru',true,304);
			return true;
		}

		return false;
	}

	/** Подготовка хедеров (действия перед echo)
	 * @param string|array $type Тип контента (text,html,js,css,json,xml), второй параметр массива - charset
	 * @param int $code Код ответа
	 * @param array $cache Данные для кэширования отвата, ключи:
	 *  int max-age Возраст кэша в секундах, можно ставить 0 Required!
	 *  string|int modified Время обновления контента
	 *  bool revalidate Необходимость проверки кэша браузера сервером (возврат 304 кода), нужен ключ modified
	 *  string etag
	 *  bool public Кэш является публичным?
	 * @return bool Флаг успешной отправки заголовков */
	public static function SendHeaders($type='html',$code=200,array$cache=[])
	{
		if(headers_sent())
			return false;

		if(isset($cache['max-age']))
		{#http://xmlhack.ru/texts/06/doing-http-caching-right/doing-http-caching-right.html

			$cache+=['revalidate'=>$cache['max-age']==0 ? true : false,'public'=>false];

			header('Cache-Control: '.($cache['public'] ? 'public' : 'private')
					.', max-age='.$cache['max-age']
					.($cache['revalidate'] ? ', must-revalidate' : '')
				,false,$code);

			if($cache['revalidate'])
			{
				if(!isset($cache['etag']))
					$cache['etag']=md5($_SERVER['REQUEST_URI']);

				if(!isset($cache['modified']))
					$cache['modified']=time();
				elseif(is_string($cache['modified']))
					$cache['modified']=strtotime($cache['modified']);

				header('ETag: '.$cache['etag']);#Кавычки не ставить, ибо они передаются потом в HTTP_IF_NONE_MATCH
				header('Last-Modified: '.gmdate('D, d M Y H:i:s ',$cache['modified']).'GMT',false);
			}
		}
		else
			header('Cache-Control: no-store');

		$type=(array)$type+[1=>Eleanor\CHARSET];
		switch($type[0])
		{
			case'text':
				$type[0]='text/plain';
			break;
			case'html':
			case'css':
				$type[0]='text/'.$type[0];
			break;
			case'js':
				$type[0]='application/javascript';
			break;
			case'json':
			case'xml':
				$type[0]='application/'.$type[0];
		}

		header("Content-Type: {$type[0]}; charset={$type[1]}");
		header("Content-Encoding: {$type[1]}");
		header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru',false,$code);

		return true;
	}

	/** Вывод контента
	 * @param string $content Строка для вывода
	 * @param int $level Степень GZIP сжатия от 1 до 9 */
	public static function Gzip($content,$level=1)
	{
		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')!==false && extension_loaded('zlib'))
		{
			$gsize=strlen($content);
			$gcrc=crc32($content);

			$content=gzcompress($content,$level);
			$content=substr($content,0,-4);
			$content="\x1f\x8b\x08\x00\x00\x00\x00\x00".$content.pack('V',$gcrc).pack('V',$gsize);

			header('Content-Encoding: gzip',true);
		}

		echo$content;
		flush();
	}
}