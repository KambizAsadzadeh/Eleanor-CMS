<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Набор функций для работы с изображениями */
class Image extends Eleanor\BaseClass
{
	/** Вычисление среднего цвета
	 * @param int $a Значение 1 либо R либо G либо B
	 * @param int $b Значение 2 либо R либо G либо B
	 * @param float $alpha Прозрачность от 0 до 1 (от $a до $b)
	 * @return float */
	public static function GetAverage($a,$b,$alpha)
	{
		return round($a*(1-$alpha)+$b*$alpha);
	}

	/** Создание ресурса изображения из файла исходя из его внутренней структуры или типа
	 * @param string $path Путь к файлу
	 * @throws EE
	 * @return resource */
	public static function CreateImage($path)
	{
		if(!is_file($path))
			throw new EE('Image not found',EE::ENV,[ 'input'=>$path ]);

		if(preg_match('#\.webp$#i',$path)>0)
			return imagecreatefromwebp($path);

		switch(exif_imagetype($path))
		{
			case IMAGETYPE_JPEG:
				return imagecreatefromjpeg($path);
			case IMAGETYPE_PNG:
				return imagecreatefrompng($path);
			case IMAGETYPE_GIF:
				return imagecreatefromgif($path);
			case IMAGETYPE_WBMP:
				return imagecreatefromwbmp($path);
		}

		throw new EE('Incorrect image',EE::ENV,[ 'input'=>$path ]);
	}

	/** Сохранение картинки в виде файла определенного формата
	 * @param resource $image Ресурс картинки
	 * @param string $path Путь для сохранения картинки
	 * @param string|null $type Тип картинки */
	public static function SaveImage($image,$path,$type=null)
	{
		switch($type ? $type : strtolower(pathinfo($path,PATHINFO_EXTENSION)))
		{
			case'jpg':
			case'jpeg':
				imagejpeg($image,$path,100);
			break;
			case'gif':
				imagegif($image,$path);
			break;
			case'webp':
				imagewebp($image,$path);
			break;
			default:
				imagepng($image,$path,0,PNG_ALL_FILTERS);
		}
	}

	/** Создание превьюшки (preview, thumbnail) картинки
	 * @param string $source Путь к файлу
	 * @param array $o Опции, описание доступно внутри самого метода
	 * @throws EE
	 * @return bool|string */
	public static function Preview($source,array$o=[])
	{
		if(!is_file($source))
			throw new EE('Image not found',EE::ENV,[ 'input'=>$source ]);

		if(!list($w,$h)=getimagesize($source))
			throw new EE('Incorrect image',EE::ENV);

		#Размер превьюшки по умолчанию 100 на 100 будет установлен если в $o отсутствуют указатели размера
		$setsize=!isset($o['width']) && !isset($o['height']);

		$o+=[
			'cut_first'=>false,#Если true - превьюшка будет не ужиматься, а тупо обрезаться
			'cut_last'=>false,#Если true - превьюшка будет уменьшена по одной стороне, а по другой - обрезана
			'first'=>'b',#Что будет уменьшаться первое: высота или ширина. w,h . Автоматически: b - по наибольшей стороне, s - по наименьшей стороне
			'imagic'=>[],#Параметры ImageMagic

			#Параметры нового имени
			'newname'=>false,
			'suffix'=>'_preview',#Суффикс для имени файла

			'returnbool'=>false,
		];

		$dest=$o['newname']
			? (preg_match('#[/\\\]#',$o['newname'])>0 ? '' : dirname($source).'/').$o['newname']
			: substr_replace($source,$o['suffix'],strrpos($source,'.'),0);

		if(!is_writable($dn=dirname($dest)))#Нам нужно проверить, сможем ли записать не только в каталог файла, но и в сам файл.
			throw new EE('Folder is write-protected',EE::ENV,[ 'input'=>$dn ]);

		if($o['first']=='b')
			$o['first']=$w>$h ? 'w' : 'h';
		elseif($o['first']=='s')
			$o['first']=$w>$h ? 'h' : 'w';

		if($setsize)
			if($w>=$h or $o['first']=='w')
				$o['width']=100;
			elseif($w>=$h or $o['first']=='h')
				$o['height']=100;

		$o+=[
			'width'=>0,#Ширина будущей превьюшки; целое число: 0 - без изменений
			'height'=>0,#Высота будущем превьюшки; целое число: 0 - без изменений
		];

		if($o['first']=='w' and ($o['width']>=$w or $o['width']==0) or $o['first']=='h' and ($o['height']>=$h or $o['height']==0))
			return$o['returnbool'] ? false : $source;

		if(class_exists('\imagick'))
			static::PreviewMagic($source,$dest,$w,$h,$o['width'],$o['height'],$o['first'],$o['cut_first'],$o['cut_last'],$o['imagic']);
		else
			static::PreviewGD($source,$dest,$w,$h,$o['width'],$o['height'],$o['first'],$o['cut_first'],$o['cut_last']);

		return$o['returnbool'] ? true : $dest;
	}

	/** Создание превьюшки при помощи библиотеки GD */
	protected static function PreviewGD($source,$dest,$sx,$sy,$dx,$dy,$first,$cut_first,$cut_last)
	{
		$source=static::CreateImage($source);

		if($first=='w')
		{
			$height=$cut_first ? $sy : round($sy*$dx/$sx);
			$img_dest=imagecreatetruecolor($dx, $height);
			#Сохраняем прозрачность
			imagealphablending($img_dest, false);
			imagesavealpha($img_dest, true);
			imagecopyresampled($img_dest, $source, 0, 0, 0, 0, $dx, $height, $cut_first ? $dx : $sx, $sy);

			if($height>$dy and $dy)
			{
				$width=$cut_last ? $dx : round($dx*$dy/$height);
				$temp=$img_dest;
				$img_dest=imagecreatetruecolor($width, $dy);

				imagealphablending($img_dest, false);
				imagesavealpha($img_dest, true);
				imagecopyresampled($img_dest, $temp, 0, 0, 0, 0, $width, $dy, $dx, $cut_last ? $dy : $height);
				imagedestroy($temp);
			}
		}
		else
		{
			$width=$cut_first ? $sx : round($sx*$dy/$sy);
			$img_dest=imagecreatetruecolor($width,$dy);

			#Сохраняем прозрачность
			imagealphablending($img_dest,false);
			imagesavealpha($img_dest,true);
			imagecopyresampled($img_dest,$source,0,0,0,0,$width,$dy,$sx,$cut_first ? $dy : $sy);

			if($width>$dx and $dx)
			{
				$height=$cut_last ? $dy : round($dy*$dx/$width);
				$temp=$img_dest;
				$img_dest=imagecreatetruecolor($dx,$height);

				imagealphablending($img_dest,false);
				imagesavealpha($img_dest,true);
				imagecopyresampled($img_dest,$temp,0,0,0,0,$dx,$height,$cut_last ? $dx : $width,$dy);
				imagedestroy($temp);
			}
		}

		/*Квадратная превьюха с центрированием внутри виртуального квадрата
		$sx=imagesx($dest);
		$sy=imagesy($dest);

		if($sx!=$sy)
		{
			$max=max($sx,$sy);

			$dest2=imagecreatetruecolor($max,$max);
			imagealphablending($dest2,false);
			imagesavealpha($dest2,true);
			imagefill($dest2,0,0,imagecolorexactalpha($dest2,1,1,1,255));

			imagecopy($dest2,$dest,$sx>$sy ? 0 : round(($sy-$sx)/2),$sy>$sx ? 0 : round(($sx-$sy)/2),0,0,$sx,$sy);
			imagedestroy($dest);
			$dest=$dest2;

			$newpath=preg_replace('#\.(jpe?g|png|gif)$#','.png',$newpath);
		}*/

		imagedestroy($source);
		static::SaveImage($img_dest,$dest);
		imagedestroy($img_dest);
	}

	/** Создание превьюшки при помощи библиотеки ImageMagic. Описание параметров смотрите в PreviewGD */
	protected static function PreviewMagic($source,$dest,$sx,$sy,$dx,$dy,$first,$cut_first,$cut_last,$params)
	{
		$params+=[
			'filter'=>\imagick::FILTER_BOX,
			'blur'=>0.9,
			'bestfit'=>true,
		];

		$Magic=new \imagick($source);

		#Удаление exif http://stackoverflow.com/questions/13646028/how-to-remove-exif-from-a-jpg-without-losing-image-quality
		$profiles=$Magic->getImageProfiles('icc',true);
		$Magic->stripImage();

		if($profiles)
			$Magic->profileImage('icc',$profiles['icc']);
		#/Удаление exif

		if($first=='w')
		{
			$height=$cut_first ? $sy : round($sy*$dx/$sx);

			if($height>$dy and $dy)
			{
				$width=$cut_last ? $dx : round($dx*$dy/$height);
				$height=$dy;
			}
			else
				$width=$dx;
		}
		else
		{
			$width=$cut_first ? $sx : round($sx*$dy/$sy);

			if($width>$dx and $dx)
			{
				$height=$cut_last ? $dy : round($dy*$dx/$width);
				$width=$dx;
			}
			else
				$height=$dy;
		}

		try
		{
			$Magic->resizeImage($width, $height, $params['filter'], $params['blur'], $params['bestfit']);
			$Magic->writeImage($dest);
		}
		catch (\Exception$E){}
		finally
		{
			$Magic->destroy();
		}
	}

	/** Установка водяного знака (watermark) на картинку
	 * @param string $path Путь к файлу
	 * @param array $o Опции, описание доступно внутри самого метода
	 * @throws EE
	 * @return bool */
	public static function WaterMark($path,$o=[])
	{#ToDo! Image magic
		if(!is_file($path))
			throw new EE('File not found',EE::ENV,[ 'input'=>$path ]);

		$o+=[
			'alpha'=>0,#Прозрачность ватермарка в процентах от 0 до 100
			'top'=>50,#Положение в процентах от 0 до 100 по высоте (сверху вниз)
			'left'=>50,#Положение в процентах от 0 до 100 по ширине (слева вправо)

			#Низкий приоритет. Для жесткости
			'ptop'=>0,#Положение в пикселях по высоте (сверх вниз)
			'pleft'=>0,#Положение в пикселях по ширине (слева вправо)

			#Если в качестве ватермарка задана картинка - нарисуем картинку
			'image'=>'',#Путь к файлу картинки-ватермарка

			#Если картинка в качестве ватермарка картинка не задана, наприсуем текст
			'text'=>'Eleanor CMS',#Текст ватермарка
			'font'=>'',#Путь к файлу-шрифту ватермарка
			'size'=>15,#Размер кегля шрифта
			'angle'=>0,#Угол наклон шрифта
			'r'=>1,#Цевет шрифта, R
			'g'=>1,#Цевет шрифта, G
			'b'=>1,#Цевет шрифта, B
		];
		$o['alpha']=(100-$o['alpha'])/100;
		$o['top']/=100;
		$o['left']/=100;

		$img=static::CreateImage($path);

		$iw=imagesx($img);
		$ih=imagesy($img);

		#Сохраняем прозрачность
		imagealphablending($img,false);
		imagesavealpha($img,true);

		try
		{
			if($o['image'] and is_file($o['image']))
			{
				$wimg=static::CreateImage($o['image']);

				#ToDo! Что делать, если превью больше картинки?
				$wiw=min(imagesx($wimg),$iw);
				$wih=min(imagesy($wimg),$ih);

				$dx=$o['pleft']>0 ? $o['pleft'] : round(($iw-$wiw)*$o['left']);
				$dy=$o['ptop']>0 ? $o['ptop'] : round(($ih-$wih)*$o['top']);

				if($dx+$wiw>$iw)
					$dx=$iw-$wiw;

				if($dy+$wih>$ih)
					$dy=$ih-$wih;

				for($y=0;$y<$wih;$y++)
					for($x=0;$x<$wiw;$x++)
					{
						$rgb=imagecolorsforindex($img,imagecolorat($img,$dx+$x,$dy+$y));
						$wrgb=imagecolorsforindex($wimg,imagecolorat($wimg,$x,$y));

						#Вычислим альфаканал в %
						$a=round((127-$wrgb['alpha'])/127,2)*$o['alpha'];

						#расчет цвета в месте наложения картинок
						$r=static::GetAverage($rgb['red'],$wrgb['red'],$a);
						$g=static::GetAverage($rgb['green'],$wrgb['green'],$a);
						$b=static::GetAverage($rgb['blue'],$wrgb['blue'],$a);
						$color=imagecolorexact($img,$r,$g,$b);
						imagesetpixel($img,$dx+$x,$dy+$y,$color);
					}
			}
			elseif($o['text'] and is_file($o['font']))
			{
				$rect=imageftbbox($o['size'],$o['angle'],$o['font'],$o['text']);
				$width=abs($rect[4]-$rect[0]);
				$height=abs($rect[1]-$rect[5]);

				if($width<$iw and $height<$ih)
				{
					$dx=$o['pleft']>0 ? $o['pleft'] : round(($iw-$width)*$o['left']);
					$dy=$o['ptop']>0 ? $o['ptop'] : round(($ih-$height)*$o['top']);
					#Цвет ватермарка
					$color=imagecolorexactalpha($img,$o['r'],$o['g'],$o['b'],$o['alpha']*127);
					if(!Eleanor\UTF8)
						$o['text']=mb_convert_encoding($o['text'],'utf-8');

					#Цвет должен быть отрицательным числом. http://phpforum.ru/txt/index.php/t23846.html
					imagettftext($img,$o['size'],$o['angle'],$dx,$dy,-$color,$o['font'],$o['text']);
				}
				else
				{
					imagedestroy($img);
					return false;
				}
			}
			else
				throw new EE('Watermark was not specified',EE::DEV);
		}
		catch(EE$E)
		{
			imagedestroy($img);
			throw$E;
		}

		static::SaveImage($img,$path);
		imagedestroy($img);

		return true;
	}
}