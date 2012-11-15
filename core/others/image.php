<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class Image extends BaseClass
{
	/*
		������� ������������� ��� ����������� "��������" �����.
	*/
	public static function GetAverage($a,$b,$alpha)
	{
		return round($a*(1-$alpha)+$b*$alpha);
	}

	public static function CreateImage($p,$t=false)
	{
		if(function_exists('exif_imagetype') and (!$t or is_int($t)))
			switch($t ? $t : exif_imagetype($p))
			{
				case IMAGETYPE_JPEG:
					return@imagecreatefromjpeg($p);
				case IMAGETYPE_PNG:
					return@imagecreatefrompng($p);
				case IMAGETYPE_GIF:
					return@imagecreatefromgif($p);
				case IMAGETYPE_BMP:
					return@imagecreatefromwbmp($p);
			}
		else
			switch($t ? $t : strtolower(pathinfo($p,PATHINFO_EXTENSION)))
			{
				case'jpg':
				case'jpeg':
					return@imagecreatefromjpeg($p);
				case'png':
					return@imagecreatefrompng($p);
				case'gif':
					return@imagecreatefromgif($p);
				case'bmp':
					return@imagecreatefromwbmp($p);
			}
		return false;
	}

	public static function SaveImage($im,$p,$t=false)
	{
		switch($t ? $t : strtolower(pathinfo($p,PATHINFO_EXTENSION)))
		{
			case'jpg':
			case'jpeg':
				imagejpeg($im,$p,100);
			break;
			case'gif':
				imagegif($im,$p);
			break;
			case'bmp':
				imagewbmp($im,$p);
			break;
			case'png':
				imagepng($im,$p,0,PNG_ALL_FILTERS);
		}
	}
	/*
		�����, ��������� ��������� �� ��������.
		$path - ���� �� ��������.
		$o - ������ ����
	*/
	public static function Preview($path,array$o=array())
	{
		if(!is_file($path))
			throw new EE('File not found! ('.$path.')',EE::DEV);
		if(!list($w,$h)=getimagesize($path))
			throw new EE('Image failed!',EE::ENV);
		$o+=array(
			'width'=>$w>$h ? 100 : 0,#������ ������� ���������; ����� �����: 0 - ��� ���������.
			'height'=>$h>$w ? 100 : 0,#������ ������� ���������; ����� �����: 0 - ��� ���������.
			'cut_first'=>false,#���� true - ��������� ����� �� ���������, � ���� ����������
			'cut_last'=>false,#���� true - ���������� ����� ��������� �� ����� �������, � �� ������ - ���� ��������
			'first'=>'b',#��� ����� ����������� ������: ������ ��� ������. w,h . �������������: b - �� ��������� �������, s - �� ��������� �������.

			#��������� ������ �����
			'newname'=>false,
			'suffix'=>'_preview',#������ ��� �����
		);
		$newpath=$o['newname'] ? (preg_match('#[/\\\]#',$o['newname'])>0 ? '' : dirname($path).'/').$o['newname'] : substr_replace($path,$o['suffix'],strrpos($path,'.'),0);
		if(!is_writable($dn=dirname($newpath)))#��� ����� ���������, ������ �� �������� �� ������ � ������� �����, �� � � ��� ����.
			throw new EE('Folder '.$dn.' is write-protected!',EE::ENV);
		if($o['first']=='b')
			$o['first']=$w>$h ? 'w' : 'h';
		elseif($o['first']=='s')
			$o['first']=$w>$h ? 'h' : 'w';

		if($o['first']=='w' and ($o['width']>=$w or $o['width']==0) or $o['first']=='h' and ($o['height']>=$h or $o['height']==0))
			return$path;

		#����� ����� �������� ����������� �������� � ���������� �������. ��� ��� �� ���� ����������.
		Eleanor::$nolog=true;
		if(false===$img=self::CreateImage($path))
		{
			Eleanor::$nolog=false;
			throw new EE('Unable to create preview!',EE::ENV);
		}
		Eleanor::$nolog=false;
		switch($o['first'])
		{
			case'w':
				$height=$o['cut_first'] ? $h : round($h*$o['width']/$w);
				$r=imagecreatetruecolor($o['width'],$height);
				#��������� ������������
				imagealphablending($r,false);
				imagesavealpha($r,true);
				imagecopyresampled($r,$img,0,0,0,0,$o['width'],$height,$o['cut_first'] ? $o['width'] : $w,$h);
				if($height>$o['height'] and $o['height'])
				{
					$width=$o['cut_last'] ? $o['width'] : round($o['width']*$o['height']/$height);
					$temp=$r;
					$r=imagecreatetruecolor($width,$o['height']);
					imagecopyresampled($r,$temp,0,0,0,0,$width,$o['height'],$o['width'],$o['cut_last'] ? $o['height'] : $height);
				}
			break;
			#case'h':
			default:
				$width=$o['cut_first'] ? $w : round($w*$o['height']/$h);
				$r=imagecreatetruecolor($width,$o['height']);
				#��������� ������������
				imagealphablending($r,false);
				imagesavealpha($r,true);
				imagecopyresampled($r,$img,0,0,0,0,$width,$o['height'],$w,$o['cut_first'] ? $o['height'] : $h);
				if($width>$o['width'] and $o['width'])
				{
					$height=$o['cut_last'] ? $o['height'] : round($o['height']*$o['width']/$width);
					$temp=$r;
					$r=imagecreatetruecolor($o['width'],$height);
					imagecopyresampled($r,$temp,0,0,0,0,$o['width'],$height,$o['cut_last'] ? $o['width'] : $width,$o['height']);
				}
		}
		imagedestroy($img);
		self::SaveImage($r,$newpath);
		imagedestroy($r);
		return$newpath;
	}

	/*
		�������, ������� ������ ���������
		$path - ���� � ��������
		$o - ������ ��������. ��. ����
	*/
	public static function WaterMark($path,$o=array())
	{
		if(!is_file($path))
			throw new EE('File not found!',EE::DEV);
		$o+=array(
			'types'=>array('bmp','png','jpg'),#���� ������ ��� ������� ������� ���������
			'alpha'=>0,#������������ ���������� � ��������� �� 0 �� 100%
			'top'=>50,#��������� � ��������� �� 0 �� 100% �� ������ (����� ����)
			'left'=>50,#��������� � ��������� �� 0 �� 100% �� ������ (����� �������)

			#������ ���������. ��� ���������
			'ptop'=>0,#��������� � �������� �� ������ (����� ����)
			'pleft'=>0,#��������� � �������� �� ������ (����� �������)

			#���� ������ �������� - �������� ��������
			'image'=>'',

			#���� �������� - false, ��������� �����
			'text'=>'Eleanor CMS',
			'font'=>Eleanor::$root.'addons/fonts/arial.ttf',
			'size'=>15,
			'angle'=>0,
			'r'=>1,
			'g'=>1,
			'b'=>1,
		);
		$o['types']=$o['types'] ? array_intersect(array('jpeg','jpg','png','gif','bmp'),$o['types']) : array();
		$o['alpha']=(100-$o['alpha'])/100;
		$o['top']/=100;
		$o['left']/=100;
		if(!in_array(strtolower(pathinfo($path,PATHINFO_EXTENSION)),$o['types']))
			return false;
		if(false===$img=self::CreateImage($path))
			throw new EE('Unable to create image!',EE::ENV);
		$iw=imagesx($img);
		$ih=imagesy($img);

		#��������� ������������
		imagealphablending($img,false);
		imagesavealpha($img,true);

		if($o['image'] and is_file($o['image']))
		{
			if(false===$wimg=self::CreateImage($o['image']))
			{
				imagedestroy($img);
				throw new EE('Unable to load watermark image!',EE::ENV);
			}
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
					#�������� ���������� � %
					$a=round((127-$wrgb['alpha'])/127,2)*$o['alpha'];
					#������ ����� � ����� ��������� ��������
					$r=self::GetAverage($rgb['red'],$wrgb['red'],$a);
					$g=self::GetAverage($rgb['green'],$wrgb['green'],$a);
					$b=self::GetAverage($rgb['blue'],$wrgb['blue'],$a);
					$color=imagecolorexact($img,$r,$g,$b);
					imagesetpixel($img,$dx+$x,$dy+$y,$color);
				}
		}
		elseif($o['text'])
		{
			$rect=imageftbbox($o['size'],$o['angle'],$o['font'],$o['text']);
			$width=abs($rect[4]-$rect[0]);
			$height=abs($rect[1]-$rect[5]);
			if($width<$iw and $height<$ih)
			{
				$dx=$o['pleft']>0 ? $o['pleft'] : round(($iw-$width)*$o['left']);
				$dy=$o['ptop']>0 ? $o['ptop'] : round(($ih-$height)*$o['top']);
				#���� ����������
				$color=imagecolorexactalpha($img,$o['r'],$o['g'],$o['b'],$o['alpha']*127);
				if(CHARSET!='utf-8')
					$o['text']=mb_convert_encoding($o['text'],'utf-8');
				#���� ������������� ������ ������ ����. �� ���� �������� ���: http://phpforum.ru/txt/index.php/t23846.html
				imagettftext($img,$o['size'],$o['angle'],$dx,$dy,-$color,$o['font'],$o['text']);
			}
			else
			{
				imagedestroy($img);
				return false;
			}
		}
		else
		{
			imagedestroy($img);
			return false;
		}
		self::SaveImage($img,$path);
		imagedestroy($img);
		return true;
	}
}