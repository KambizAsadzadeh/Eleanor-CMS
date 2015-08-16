<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE, Eleanor\Classes\Files, Eleanor\Classes\Html, Eleanor\Classes\Image, Eleanor\Classes\Output;

/** BackEnd загрузчика файлов */
class Uploader_BackEnd
{
	/** @var array Данные сессии */
	protected static $data;

	/** Реализация AJAX запроса
	 * @param string $uniq Уникальная строка-идентификатор каждого отдельного загрузчика на странице */
	public static function Process($uniq)
	{
		if(!isset($_SESSION['Uploader'][$uniq]))
			\Eleanor\StartSession(isset($_REQUEST['sid']) ? (string)$_REQUEST['sid'] : '');

		if(!AJAX and isset($_GET['download'],$_SESSION['Uploader'][$uniq]))
		{
			static::$data=$_SESSION['Uploader'][$uniq];

			$path=static::GetPath((string)$_GET['download']);

			if(is_file($path))
				Output::Stream(['file'=>$path]);
			else
			{
				OutPut::SendHeaders('text',403);
				Output::Gzip('Access denied');
			}

			return;
		}

		if(!isset($_SESSION['Uploader'][$uniq],$_REQUEST['event'],$_REQUEST['current']))
			return Error(['SESSION_WAS_NOT_FOUND']);

		static::$data=$_SESSION['Uploader'][$uniq];
		$path=static::GetPath((string)$_REQUEST['current'],isset($_REQUEST['where']) ? (string)$_REQUEST['where'] : '');

		switch($_REQUEST['event'])
		{
			case'create-folder':
				$name=isset($_POST['name']) ? (string)$_POST['name'] : '';

				if(!static::$data['commands']['create_folder'] or $name==='' or static::EF($name) or file_exists($path.$name) or !Files::MkDir($path.$name))
					return Error(['CREATE_FOLDER_FAIL']);
			case'go':
				$page=isset($_REQUEST['page']) ? (int)$_REQUEST['page'] : 1;
				$pp=isset($_REQUEST['pp']) ? (int)$_REQUEST['pp'] : 1;
				$preview=isset($_REQUEST['preview']) ? (string)$_REQUEST['preview'] : 1;

				if(strpos($preview,',')!==false)
				{
					$preview=explode(',',$preview);

					UploaderPreview::$w=(int)$preview[0];
					UploaderPreview::$h=(int)$preview[1];
				}

				if($page<1)
					$page=1;

				if($pp<2 or $pp>500)
					$pp=100;

				$dirs=$pathway=[];
				$files=glob($path.'/*',GLOB_MARK | GLOB_NOSORT);

				if(!$files)
					$files=[];

				$total=count($files);

				foreach($files as $k=>&$v)
				{
					$v=Files::Windows($v,true);

					if(substr($v,-1)==DIRECTORY_SEPARATOR)
					{
						$dirs[]=basename($v);
						unset($files[$k]);
					}
					else
						$v=basename($v);
				}

				unset($v);
				natsort($dirs);
				natsort($files);

				$dirs=array_values($dirs);
				$files=array_values($files);

				if($page>ceil($total/$pp))
					$page=floor($total/$pp);

				if($pp<$total)
				{
					$cnt_dir=count($dirs);
					$offset=($page-1)*$pp;

					if(($offset+$pp)<=$cnt_dir)
					{
						$dirs=array_slice($dirs,$offset,$pp);
						$files=[];
					}
					elseif($offset<$cnt_dir)
					{
						$dirs=array_slice($dirs,$offset,$pp);
						$files=array_slice($files,0,$pp-count($dirs));
					}
					else
					{
						$dirs=[];
						$files=array_slice($files,$offset-$cnt_dir,$pp);
					}
				}

				$current=(string)substr($path,strlen(static::$data['path']));
				$current=rtrim($current, '/\\');

				if($current)
				{
					$current=Files::Windows($current, true);
					$pathway=explode(DIRECTORY_SEPARATOR, $current);
					$current=join('/',$pathway).'/';
					$cnt=count($pathway);

					foreach($pathway as $k=>&$entry)
					{
						$entry=['name'=>$entry];

						if($cnt-$k>1)
							$entry['jump']=trim(str_repeat('../',$cnt-$k-1),'/');
					}
				}

				foreach($dirs as &$entry)
				{
					$commands=[];

					if(static::$data['commands']['folder_delete'])
						$commands[]='delete';

					if(static::$data['commands']['folder_rename'])
						$commands[]='rename';

					$entry=[
						'name'=>$entry,
						'commands'=>$commands,
					];
				}

				$download=basename($_SERVER['PHP_SELF']).'?direct=download&amp;sid='.session_id().'&amp;uniq='
					.urlencode($uniq).'&amp;download=';

				foreach($files as &$entry)
				{
					$wpath=Files::Windows($path.$entry);
					$type=strpos($entry,'.')===false ? '' : strtolower(pathinfo($wpath,PATHINFO_EXTENSION));

					try{
						$image=in_array($type, ['jpeg', 'jpg', 'png', 'bmp', 'gif', 'ico', 'webp']) ? ['image'=>UploaderPreview::Preview($wpath)] : [];
					}catch(EE$E){
						$image=[];
					}
					$commands=[];

					$http=static::$data['http'] ? static::$data['http'].$current.$entry : false;

					if(static::$data['commands']['edit'] and in_array($type,static::$data['editable']))
						$commands[]='edit';

					if(static::$data['commands']['file_delete'])
						$commands[]='delete';

					if(static::$data['commands']['file_rename'])
						$commands[]='rename';

					if(static::$data['commands']['insert_attach'] and $http)
						$commands[]='attach';

					$entry=[
						'name'=>$entry,
						'size'=>filesize($wpath),
						'date'=>filemtime($wpath),
						'commands'=>$commands,
					]+$image;

					if($http)
						$entry['http']=$http;
					else
						$entry['download']=$download.urlencode($current.$entry['name']);
				}

				$canupload=true;
				$sizelim=static::$data['max_size']!==true;
				$fileslim=static::$data['max_files']>0;

				if($sizelim or $fileslim)
					list($cursize,$curcnt)=static::FilesSize(static::$data['path']);

				if($sizelim)
				{
					$freesize=static::$data['max_size']-$cursize;
					$canupload&=$freesize>0;

					if($freesize<static::$data['max_upload'])
						static::$data['max_upload']=$freesize;
				}

				if($fileslim)
				{
					$maxfiles=static::$data['max_files']-$curcnt;
					$canupload&=$maxfiles>0;

					if($maxfiles<static::$data['max_files'])
						static::$data['max_files']=$maxfiles;
				}

				Response([
					'dirs'=>$dirs,
					'files'=>$files,
					'pathway'=>$pathway,

					'max_upload'=>static::$data['max_upload'],
					'max_files'=>static::$data['max_files'],
					'can_upload'=>$canupload,
					'total'=>$total,
					'page'=>$page,
					'current'=>$current
				]);
			break;
			case'rename':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';
				$to=isset($_POST['to']) ? trim((string)$_POST['to'],'. ') : '';

				if($what=='' or $to=='' or $to==$what or static::EF($to) or static::EF($what))
					goto RenameFail;

				$type=strpos($to,'.')===false ? '' : strtolower(pathinfo($to,PATHINFO_EXTENSION));

				if(static::$data['types'] and !in_array($type,static::$data['types']))
					goto RenameFail;

				$dest=$path.$to;
				$path.=$what;

				if(!file_exists($path) or file_exists($dest))
					goto RenameFail;

				$isdir=is_dir($path);

				if($isdir and !static::$data['commands']['folder_rename'] or !$isdir and !static::$data['commands']['file_rename'])
					goto RenameFail;

				if(rename($path,$dest))
					return Response(Files::Windows($to,true));

				RenameFail:
				Error(['RENAME_FAIL']);
			break;
			case'delete':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';

				if($what=='' or static::EF($what))
					goto DeleteFail;

				$path.=$what;

				if(!file_exists($path))
					return Response(true);

				$isdir=is_dir($path);

				if($isdir and !static::$data['commands']['folder_delete'] or !$isdir and !static::$data['commands']['file_delete'])
					goto DeleteFail;

				if(Files::Delete($path))
					return Response(true);

				DeleteFail:
				Error(['DELETE_FAIL']);
			break;
			case'upload':
				if(!isset($_FILES['file']) or !is_array($_FILES['file']['tmp_name']))
				{
					UploadFail:
					return Error('Uploading fail');
				}

				$watermark=isset($_REQUEST['watermark']) ? (bool)$_REQUEST['watermark'] : false;
				$sizelim=static::$data['max_size']!==true;
				$fileslim=static::$data['max_files']>0;
				$uploaded=0;
				$errors=[];

				if($sizelim or $fileslim)
				{
					list($free,$remain)=static::FilesSize(static::$data[ 'path' ]);

					$free=max(0,static::$data['max_size']-$free);#Остаток свободного места
					$remain=max(0,static::$data['max_files']-$remain);#Остаток количества файлов
				}

				foreach($_FILES['file']['name'] as $k=>$name)
				{
					if(static::$data['translit'] and method_exists('\\Eleanor\\Classes\\Language\\'.static::$data['translit'],'Translit'))
						$name=call_user_func(['\\Eleanor\\Classes\\Language\\'.static::$data['translit'],'Translit'],$name);

					#Заменяем все [ и ] потому что они криво вставляются в тег [attach]
					$name=preg_replace('#[\s\'"%\]\[/\\\-]+#','-',$name);

					#Проверка на сбойность имени
					if(static::EF($name) or !is_uploaded_file($_FILES['file']['tmp_name'][$k]))
						continue;

					#Проверка на лимиты
					if($sizelim and $_FILES['file']['size'][$k]>$free or $fileslim and $remain<1)
						continue;

					$type=strpos($name,'.')===false ? '' : pathinfo($name,PATHINFO_EXTENSION);
					$dest=$path.$name;

					if(static::$data['types'] and !in_array($type,static::$data['types']) or !move_uploaded_file($_FILES['file']['tmp_name'][$k],$dest))
						continue;

					#Обновление лимитов
					$uploaded++;

					if($sizelim)
						$free-=$_FILES['file']['size'][$k];

					if($fileslim)
						$remain--;
					#/Обновление лимитов

					if(in_array($type,['jpeg','jpg','png','gif','webp']) and (static::$data['watermark'] or static::$data['watermark']===null and $watermark))
						try
						{
							$sets=static::$data['watermark-settings'];
							Image::WaterMark(
								$dest,
								[
									'alpha'=>(int)$sets['colour']['a'],
									'top'=>(int)$sets['top'],
									'left'=>(int)$sets['left'],

									#Если задана картинка - нарисуем картинку
									'image'=>$sets['image'],

									#Если картинка - false, наприсуем текст
									'text'=>$sets['string'],
									'font'=>Template::$path['static'].'fonts/arial.ttf',
									'size'=>$sets['size'],
									'angle'=>$sets['angle'],
									'r'=>(int)$sets['colour']['r'],
									'g'=>(int)$sets['colour']['g'],
									'b'=>(int)$sets['colour']['b'],
								]
							);
						}
						catch(EE$E)
						{
							$errors[]=$E->getMessage();
						}
				}

				if($uploaded==0)
					$errors[]='NOTHING_WAS_UPLOAD';

				OutPut::SendHeaders('application/json');
				Output::Gzip(Html::JSON($errors ? ['errors'=>$errors] : ['status'=>'ok']));
			break;
			case'create-file':
				$name=isset($_POST['name']) ? (string)$_POST['name'] : '';

				$fileslim=static::$data['max_files']>0;
				$can=true;

				if($fileslim)
				{
					list(,$remain)=static::FilesSize(static::$data[ 'path' ]);
					$can=$remain<static::$data['max_files'];
				}

				if(!$can or !static::$data['commands']['create_file'] or $name==='' or static::EF($name) or file_exists($path.$name))
					return Error(['CREATE_FILE_FAIL']);

				$type=strpos($name,'.')===false ? '' : strtolower(pathinfo($name,PATHINFO_EXTENSION));

				if(!in_array($type,static::$data['editable']))
					return Error(['EDIT_FAIL']);

				$path.=$name;

				if(false===file_put_contents($path,''))
					return Error(['CREATE_FILE_FAIL']);

				$Editor=function(...$args)use($type){
					$Ed=new Editor('codemirror',false,false);
					return$Ed->Area(...$args+[3=>['syntax'=>$type]]);
				};

				$out=(array)Eleanor::$Template->CreateEditFile($Editor,Files::Windows($name,true),'');

				OutPut::SendHeaders('application/json');
				Output::Gzip(Html::JSON($out));
			break;
			case'edit':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';

				if($what=='' or static::EF($what))
					return Error(['EDIT_FAIL']);

				$type=strpos($what,'.')===false ? '' : strtolower(pathinfo($what,PATHINFO_EXTENSION));

				if(!in_array($type,static::$data['editable']))
					return Error(['EDIT_FAIL']);

				$path.=$what;

				$Editor=function(...$args)use($type){
					$Ed=new Editor('codemirror',false,false);

					return $Ed->Area(...$args+[3=>['syntax'=>$type]]);
				};

				$out=(array)Eleanor::$Template->CreateEditFile($Editor,Files::Windows($what,true),file_get_contents($path));

				OutPut::SendHeaders('application/json');
				Output::Gzip(Html::JSON($out));
			break;
			case'save':
				$what=isset($_POST['what']) ? (string)$_POST['what'] : '';

				if($what=='' or static::EF($what))
					return Error(['EDIT_FAIL']);

				$type=strpos($what,'.')===false ? '' : strtolower(pathinfo($what,PATHINFO_EXTENSION));

				if(!in_array($type,static::$data['editable']) or !isset($_POST['content']))
					return Error(['EDIT_FAIL']);

				$path.=$what;
				$S=new Saver('codemirror',false,false);
				$content=$S->Save($_POST['content']);

				if(static::$data['max_size']!==true)
				{
					list($size)=static::FilesSize(static::$data[ 'path' ]);

					if($size-filesize($path)+strlen($content)>static::$data['max_size'])
						return Error(['SAVE_FAIL']);
				}

				if(file_put_contents($path,$content))
					return Response(true);

				Error(['SAVE_FAIL']);
			break;
			default:
				Error('Unknown event');
		}
	}

	/** Получение абсолютного серверного пути к каталогу с учётом перехода пользователя
	 * @param string $current Текущий путь пользователя
	 * @param string $go Путь, куда нужно перейти
	 * @return string */
	protected static function GetPath($current,$go='')
	{
		if($current)
			$current=Files::Windows(trim($current,'/\\'));

		if($go)
			$go=DIRECTORY_SEPARATOR.Files::Windows(trim($go,'/\\'));

		$path=realpath(static::$data['path'].$current.$go);

		if($path)
		{
			if(is_dir($path))
				$path.=DIRECTORY_SEPARATOR;

			if(strncmp($path, static::$data[ 'path' ], strlen(static::$data[ 'path' ]))==0)
				return $path;
		}

		if(!is_dir(static::$data['path']))
			Files::MkDir(static::$data['path']);

		return static::$data['path'];
	}

	/** Проверка корректности имени файла или каталога
	 * @param string $f Имя файла или каталога для проверки
	 * @return bool true если строка содержит недопустимые символы*/
	protected static function EF(&$f)
	{
		$f=preg_replace('#[\s/\\\\]+|\.\./|\.\.\\\\#','-',$f);

		if(strpbrk($f,"#\"'\\\\/:*~?<>&|%")!==false)
			return true;

		$f=Files::Windows($f);

		return false;
	}

	/** Получение количества загруженных файлов и их общего размера
	 * @param string $path Путь к каталогу
	 * @return array */
	protected static function FilesSize($path)
	{
		if(!is_dir($path))
			return[0,0];

		$size=$cnt=0;
		$files=glob(rtrim($path,'/\\').'/*',GLOB_MARK);

		if($files)
			foreach($files as $k=>$v)
				if(substr($v,-1)==DIRECTORY_SEPARATOR)
				{
					$t=static::FilesSize($v);
					$size+=$t[0];
					$cnt+=$t[1];
				}
				else
				{
					$size+=filesize($v);
					++$cnt;
				}
		return[$size,$cnt];
	}
}

/** Класс-генератор превьюшек для аплоадера */
class UploaderPreview extends Minify
{
	public static
		/** @var string Путь к каталогу на сервере */
		$path,

		/** @var string Путь к каталогу по HTTP */
		$http,

		/* @var int Ширина превьюшки */
		$w=64,

		/* @var int Высота превьюшки */
		$h=64;

	/** Превьюшка для изображения
	 * @param string|array $source Путь до картинки-исходника
	 * @return string Путь к сжатом файлу для доступа по HTTP */
	public static function Preview($source)
	{
		$bn=basename($source);

		#Фикс .webp формата: превьюшки создаем png формата
		if(preg_match('#\.webp$#i',$bn))
			$bn.='.png';

		if(Files::Windows($bn,true)!=$bn)
			$bn=md5($bn).strrchr($bn,'.');

		return static::GetFile(md5(dirname($source)).'/'.$bn,$source,[get_called_class(),'PreviewGenerator']);
	}

	/** Обработчик превьюх
	 * @param array $source Файл, которые необходимо сжать
	 * @param string $dest Файл, куда необходимо положить результат */
	protected static function PreviewGenerator($source,$dest)
	{
		if(!Image::Preview($source,['newname'=>$dest,'width'=>static::$w,'height'=>static::$h,'returnbool'=>true]))
			Files::SymLink($source, $dest);
	}
}

UploaderPreview::$path=Template::$path['uploads'].'temp/';
UploaderPreview::$http=Template::$http['uploads'].'temp/';