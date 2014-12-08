<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files;

/** Минификатор-обфускатор JS и CSS */
class Minify
{
	public static
		/** @var string Путь к каталогу на сервере */
		$path,

		/** @var string Путь к каталогу по HTTP */
		$http;

	/** Обфускатор css. Добавляет BOM в начало файла результата.
	 * @param string|array $source Файлы, которые необходимо сжать
	 * @return string Путь к сжатом файлу для доступа по HTTP */
	public static function Css($source)
	{
		$source=(array)$source;
		return static::GetFile(md5(join(',',$source)).'.css',$source,[get_called_class(),'CssGenerator']);
	}

	/** Обработчик css обфускации
	 * @param array $source Файлы, которые необходимо сжать
	 * @param string $dest Файл, куда необходимо положить результат */
	protected static function CssGenerator(array$source,$dest)
	{
		if(!class_exists('CssMinifier',false))
			include Template::$path['3rd'].'cssmin.php';

		$h=fopen($dest,'wb');
		fwrite($h,"\xEF\xBB\xBF");#Запись BOM

		$destdir=dirname($dest);
		$quot=$waydiff='';
		$F=function($m)use(&$waydiff,&$quot)
		{
			return'url('.$quot.(stripos($m[1],'data:')===0 ? '' : $waydiff).$m[1].$quot.')';
		};

		foreach($source as $v)
		{
			$M=new \CssMinifier(
				preg_replace("#^\xEF\xBB\xBF#",'',file_get_contents($v)),
				[
					'ImportImports'=>['BasePath'=>dirname($v)],
					'RemoveComments'=>true,
					'RemoveEmptyRulesets'=>true,
					'RemoveEmptyAtBlocks'=>true,
					'ConvertLevel3AtKeyframes'=>['RemoveSource'=>false],#Анимация
					'ConvertLevel3Properties'=>true,#Автоматические приставки типа -webkit
					'Variables'=>true,
					'RemoveLastDelarationSemiColon'=>true,
				],
				[
					'Variables'=>true,
					'ConvertFontWeight'=>true,
					'ConvertHslColors'=>true,
					'ConvertRgbColors'=>true,
					'ConvertNamedColors'=>false,
					'CompressColorValues'=>true,
					'CompressUnitValues'=>false,#0 0 преобразовывается в 0, что не совсем корректно
					'CompressExpressionValues'=>false#Expression все-равно уже deprecated
				]
			);

			$mini=$M->getMinified();
			$waydiff=static::ShortPathTo($destdir,dirname($v));

			$quot=false;
			$mini=preg_replace_callback('#url\(([^\"\'][^\)]+)\)#i',$F,$mini);
			$quot='"';
			$mini=preg_replace_callback('#url\("(.+?)"\)#i',$F,$mini);
			$quot="'";
			$mini=preg_replace_callback('#url\(\'(.+?)\'\)#i',$F,$mini);

			fwrite($h,$mini);
		}

		fclose($h);
	}

	/** Обфускатор Javascript
	 * @param string|array $source Файлы, которые необходимо сжать
	 * @return string Путь к сжатому файлу для доступа по HTTP */
	public static function Script($source)
	{
		$source=(array)$source;
		return static::GetFile(md5(join(',',$source)).'.js',$source,[get_called_class(),'ScriptGenerator']);
	}

	/** Обработчик js обфускации
	 * @param array $source Файлы, которые необходимо сжать
	 * @param string $dest Файл, куда необходимо положить результат */
	protected static function ScriptGenerator($source,$dest)
	{
		if(!class_exists('JavaScriptPacker',false))
			include Template::$path['3rd'].'JavaScriptPacker.php';

		$s='';
		foreach($source as $v)
			$s.=preg_replace("#^\xEF\xBB\xBF#",'',file_get_contents($v))."\n";

		$M=new \JavaScriptPacker($s,0);

		$h=fopen($dest,'wb');
		fwrite($h,"\xEF\xBB\xBF".$M->pack());
		fclose($h);
	}

	/** Создание кэша для произвольных файлов (сжатые css, javascript, картинки и т.п.)
	 * @param string $file Имя файла, в котором будет хранится результат
	 * @param string|array Перечень файлов-источников, у которых будет проверяться fileMtime (время последнего изменения файла)
	 * @param callback $generator Генератор кэша, метод получит на вход 2 переменные: $source и полный путь к файлу, куда нужно сохранить результат.
	 * @return string */
	public static function GetFile($file,$source,$generator)
	{
		$f=static::$path.$file;

		if(is_file($f))
		{
			$create=false;
			$mt=filemtime($f);
			$size=filesize($f);

			foreach((array)$source as $v)
				if(is_file($v) and (filesize($v)!=$size or filemtime($v)>$mt))
				{
					$create=true;
					unlink($f);
					break;
				}
		}
		else
			$create=true;

		if($create)
		{
			$dir=dirname($f);

			if(!is_dir($dir))
				Files::MkDir($dir);

			call_user_func($generator, $source, $f);
		}

		return static::$http.$file;
	}
	
	/** Генерация относительного путь для перехода из одного каталога в другой
	 * @param string $a Путь к первому каталогу
	 * @param string $b Путь ко второму каталогу
	 * @return string Например: ../../aa/bb/cc */
	public static function ShortPathTo($a,$b)
	{
		$a=preg_split('#[/\\\\]+#',rtrim($a,'/\\').'/');
		$b=preg_split('#[/\\\\]+#',rtrim($b,'/\\').'/');
		$m=min($acnt=count($a),count($b));
		for($i=0;$i<$m;++$i)
		{
			if($i==0 and $a[0]!=$b[0])
				return false;
			if($a[$i]!=$b[$i])
				break;
		}
		$acnt-=$i+1;
		$ret=$acnt>0 ? array_merge(array_fill(0,$acnt,'..'),array_slice($b,$i)) : array_slice($b,$i);
		return join('/',$ret);
	}
}

Minify::$path=DIR.'/../cache/';
Minify::$http=\Eleanor\SITEDIR.'cache/';
