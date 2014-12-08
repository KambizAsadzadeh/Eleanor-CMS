<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files, Eleanor\Classes\StringCallback;

/** Управление загрузкой файлов */
class Uploader extends \Eleanor\BaseClass
{
	public
		/** @var Template|callable Шаблон аплоадера по умолчанию */
		$Template,

		/** @var int Количество элементов (файлов и каталогов) на страницу */
		$pp=70,

		/** @var bool Флаг возможности "гулять" по папкам */
		$walking=true,

		/** @var array Разрешенные пользователю команды */
		$commands=[
			#Кнопки управления интерфейса
			'create_file'=>true,#Создание файла
			'watermark'=>true,#Включение / выключение создание ватермарка
			'create_folder'=>true,#Создание каталога
			'update'=>true,#Обновление содержимого

			#Для файлов
			'edit'=>true,#Правка файла
			'insert_attach'=>true,#Вставить файл используя ownbb код [attach]
			'file_rename'=>true,#Переименование файла
			'file_delete'=>true,#Удаление файла

			#Для каталогов
			'folder_rename'=>true,#Переименование каталога
			'folder_delete'=>true,#Удаление каталога
		],

		/** @var array Перечень файлов, для которых доступно редактирование */
		$editable=['php','css','txt','js','html','htm'],

		/** @var int Максимальный размер всех залитых файлов. Установка в false отключит загрузчик */
		$max_size,

		/** @var int Максимальный размер файлов, загружаемых за один запрос */
		$max_upload,

		/** @var int Максимальное число файлов, которые пользователь может загрузить */
		$max_files=0,

		/** @var bool Флаг ватермарка: true - всегда ставить, false - всегда не ставить, null - выбирает пользователь */
		$watermark,

		/** @var array Типы файлов, разрешенные для загрузки */
		$types=[];

	protected
		/** @var array Внутренние переменные */
		$vars,

		/** @var string Путь AJAX запросов */
		$query,

		/** @var string Корневой каталог. За пределы этого каталога выходить нельзя. */
		$path,

		/** @var string Путь к корневому каталогу */
		$http;

	/** Конструктор загрузчика файлов
	 * @param string|null $path Полный абсолютный путь к коневому каталогу загрузчика. Обязательно с / в конце.
	 * @param null|string $http Полный путь к корневому каталогу загрузчика с браузера, с / в конце.
	 * @param string $query Адрес, куда отправлять AJAX запросы
	 * @param Template|null $Template Шаблон загрузчика по умолчанию */
	public function __construct($path=null,$http=null,$query='',$Template=null)
	{
		$this->vars=LoadOptions('files',true);
		$this->path=$path ? $path : Template::$path['uploads'];
		$this->http=$http===null ? Template::$http['uploads'] : $http;
		$this->max_size=Eleanor::$Permissions->MaxUpload(true);
		$this->max_upload=Eleanor::$Permissions->MaxUpload();
		$this->query=$query ? $query : \Eleanor\SITEDIR.basename($_SERVER['PHP_SELF']).'?direct=ajax&special=uploader';

		if(!$this->vars['watermark'] or $this->max_size===false)
			$this->watermark=false;

		if($Template)
			$this->Template=$Template;
		else
			$this->Template=new Template(Eleanor::$Template->classes.'Uploader.php');

		#Микрооптимизация
		unset($this->vars['download_antileech'],$this->vars['download_no_session']);
	}

	/** Получение HTML кода загрузчика. Метод можно вызывать несколько раз, передавая каждый раз уникальный параметр
	 * $uniq для создания нескольких независимых загрузчиков на странице
	 * @param string $folder Относительный путь каталога для загрузки файла.
	 * @param string $uniq Уникальная строка-идентификатор каждого отдельного загрузчика на странице
	 * @return StringCallback */
	public function Show($folder='',$uniq='')
	{
		$max_upload=$this->max_upload;
		$max_size=$this->max_size;
		$walking=$this->walking;
		$path=$this->path;
		$http=$this->http;

		if(is_int($max_size))
		{
			if($max_size<$max_upload)
				$max_upload=$max_size;

			#Если задан макимальный размер всех файлов - уберём возможность "гулять", поскольку в этом случае подсчитать объем загруженных файлов невозможно
			$walking=false;
		}

		if($this->max_files>0)
			$walking=false;

		if(!isset($_SESSION))
			\Eleanor\StartSession();

		if($folder)
		{
			$folder=preg_replace('#[/\\\\]+#', '/', trim($folder, '/\\'));
			$wpath=Files::Windows($path.$folder);

			if(!$walking)
			{
				$path.=$folder;

				if($http)
					$http.=$folder.'/';
			}
		}
		else
			$wpath=Files::Windows($path);

		if(!is_dir($wpath))
			Files::MkDir($wpath);

		if(isset($this->watermark))
			$this->commands['watermark']=false;

		$_SESSION['Uploader'][$uniq]=[
			'pp'=>$this->pp,
			'commands'=>$this->commands,
			'editable'=>$this->editable,
			'max_size'=>$this->max_size,
			'max_upload'=>$this->max_upload,
			'max_files'=>$this->max_files,
			'watermark'=>$this->watermark,
			'types'=>$this->types,
			'path'=>realpath($path).DIRECTORY_SEPARATOR,
			'http'=>$http,
			'translit'=>Eleanor::$vars['trans_uri'] ? Language::$main : false,
		];

		if(!isset($this->watermark) or $this->watermark)
		{
			$csa=explode(',',$this->vars['watermark_csa'])+[1,1,1,15,0];

			$_SESSION['Uploader'][$uniq]['watermark-settings']=[
				'colour'=>['r'=>$csa[0],'g'=>$csa[1],'b'=>$csa[2],'a'=>$this->vars['watermark_alpha']],
				'size'=>$csa[3],
				'angle'=>$csa[4],
				'top'=>$this->vars['watermark_top'],
				'left'=>$this->vars['watermark_left'],
				'image'=>$this->vars['watermark_image'],
				'string'=>$this->vars['watermark_string'],
			];
		}

		$params=[
			$this->query,
			$this->commands,#Доступные команды
			$this->max_size===false ? false : $max_upload,#Максимальный размер загружаемых за раз файлов
			ini_get('max_file_uploads'),#Максимальное число файлов, загружаемых за раз
			$this->types,#Поддерживаемые типы файлов для загрузки
			session_id(),#ID сессии
			$folder,
			$uniq#Уникальная строка
		];
		$Str=new StringCallback(function($Template=null)use($params){
			if(!$Template)
				$Template=$this->Template;

			return (string)call_user_func_array(($Template instanceof Template) ? [$Template,'Uploader'] : $Template,$params);
		});
		$Str->creator=__CLASS__;

		return$Str;
	}
}