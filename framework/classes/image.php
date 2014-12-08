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
			default:
				imagepng($image,$path,0,PNG_ALL_FILTERS);
		}
	}

	/** Создание превьюшки (preview, thumbnail) картинки
	 * @param string $path Путь к файлу
	 * @param array $o Опции, описание доступно внутри самого метода
	 * @throws EE
	 * @return bool|string */
	public static function Preview($path,array$o=[])
	{
		if(!is_file($path))
			throw new EE('Image not found',EE::ENV,[ 'input'=>$path ]);

		if(!list($w,$h)=getimagesize($path))
			throw new EE('Incorrect image',EE::ENV);

		#Размер превьюшки по умолчанию 100 на 100 будет установлен если в $o отсутствуют указатели размера
		$setsize=!isset($o['width']) && !isset($o['height']);

		$o+=[
			'cut_first'=>false,#Если true - превьюшка будет не ужиматься, а тупо обрезаться
			'cut_last'=>false,#Если true - превьюшка будет уменьшена по одной стороне, а по другой - обрезана
			'first'=>'b',#Что будет уменьшаться первое: высота или ширина. w,h . Автоматически: b - по наибольшей стороне, s - по наименьшей стороне

			#Параметры нового имени
			'newname'=>false,
			'suffix'=>'_preview',#Суффикс для имени файла

			'returnbool'=>false,
		];

		$newpath=$o['newname']
			? (preg_match('#[/\\\]#',$o['newname'])>0 ? '' : dirname($path).'/').$o['newname']
			: substr_replace($path,$o['suffix'],strrpos($path,'.'),0);

		if(!is_writable($dn=dirname($newpath)))#Нам нужно проверить, сможем ли записать не только в каталог файла, но и в сам файл.
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
			return$o['returnbool'] ? false : $path;

		$source=static::CreateImage($path);

		switch($o['first'])
		{
			case'w':
				$height=$o['cut_first'] ? $h : round($h*$o['width']/$w);
				$dest=imagecreatetruecolor($o['width'],$height);

				#Сохраняем прозрачность
				imagealphablending($dest,false);
				imagesavealpha($dest,true);
				imagecopyresampled($dest,$source,0,0,0,0,$o['width'],$height,$o['cut_first'] ? $o['width'] : $w,$h);

				if($height>$o['height'] and $o['height'])
				{
					$width=$o['cut_last'] ? $o['width'] : round($o['width']*$o['height']/$height);
					$temp=$dest;
					$dest=imagecreatetruecolor($width,$o['height']);

					imagealphablending($dest,false);
					imagesavealpha($dest,true);
					imagecopyresampled($dest,$temp,0,0,0,0,$width,$o['height'],$o['width'],$o['cut_last'] ? $o['height'] : $height);
					imagedestroy($temp);
				}
			break;
			#case'h':
			default:
				$width=$o['cut_first'] ? $w : round($w*$o['height']/$h);
				$dest=imagecreatetruecolor($width,$o['height']);

				#Сохраняем прозрачность
				imagealphablending($dest,false);
				imagesavealpha($dest,true);
				imagecopyresampled($dest,$source,0,0,0,0,$width,$o['height'],$w,$o['cut_first'] ? $o['height'] : $h);

				if($width>$o['width'] and $o['width'])
				{
					$height=$o['cut_last'] ? $o['height'] : round($o['height']*$o['width']/$width);
					$temp=$dest;
					$dest=imagecreatetruecolor($o['width'],$height);

					imagealphablending($dest,false);
					imagesavealpha($dest,true);
					imagecopyresampled($dest,$temp,0,0,0,0,$o['width'],$height,$o['cut_last'] ? $o['width'] : $width,$o['height']);
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
		static::SaveImage($dest,$newpath);
		imagedestroy($dest);

		return$o['returnbool'] ? true : $newpath;
	}

	/** Установка водяного знака (watermark) на картинку
	 * @param string $path Путь к файлу
	 * @param array $o Опции, описание доступно внутри самого метода
	 * @throws EE
	 * @return bool */
	public static function WaterMark($path,$o=[])
	{
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

				$wiw=imagesx($wimg);
				$wih=imagesy($wimg);

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