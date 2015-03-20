<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace Eleanor\Classes;
use Eleanor;

/** Системная капча. This code based on KCAPTCHA PROJECT VERSION 2.0 http://captcha.ru Copyright by Kruglov Sergei */
class Captcha extends Eleanor\BaseClass implements Eleanor\Interfaces\Captcha_Image
{
	/** Алфавит. Не меняйте без изменения файлов шрифтов */
	const ALPHABET='0123456789abcdefghijklmnopqrstuvwxyz';

	public
		/** @var Template|callable Шаблон капчи по умолчанию */
		$Template,

		/** @var string Ссылка на картинку для капчи. Справа добавятся параметры разделенныее &amp; */
		$src,

		/** @var string Алфавит, используемый на картинке без подобных символов (o=0, 1=l, i=j, t=f) */
		$symbols='23456789abcdeghkmnpqsuvxyz',

		/** @var int Количество символов в капче */
		$length=3,

		/** @var int Отклонение в пикселях символов по вертикали */
		$fluct=8,

		/** @var float Проценты густота "белого" шума (фон) */
		$wh_noise=0.14,

		/** @var float Проценты густота "черного" шума (текст) */
		$bl_noise=0.14;

	/** Конструктор, самый обыкновенный, ничем не приметный конструктор
	 * @param null|string $src Ссылка для загрузки картинки, к ссылке будут добавлены параметры k1=v2&amp;k2=v2
	 * @param null|Template|callable Шаблон капчи по умолчанию */
	public function __construct($src=null,$Template=null)
	{
		$this->src=$src ? $src : $_SERVER['PHP_SELF'].'?captcha&amp;';
		$this->Template=$Template ? $Template : new Template(__DIR__.'/../template.php');
	}

	/** Получение HTML кода капчи, для вывода его на странице. Код определяется шаблонизатором.
	 * @param string $name Имя капчи, используется в случае, когда на странице выводится более одной капчи
	 * @param array|null $post Подмена $_POST массива. Полезно, в случае использования AJAX
	 * @return CaptchaCallback */
	public function GetCode($name='captcha',$post=null)
	{
		if(!isset($_SESSION))
		{
			if(!is_array($post))
				$post=&$_POST;

			Eleanor\StartSession(isset($post[$name]) ? $post[$name] : '',$name);
		}

		$_SESSION[$name]=[
			'symbols'=>$this->symbols,
			'length'=>$this->length,
			'fluct'=>$this->fluct,
			'wh_noise'=>$this->wh_noise,
			'bl_noise'=>$this->bl_noise,
		];

		$params=[
			'name'=>$name,
			'session'=>session_id(),
			'src'=>$this->src.'session='.session_id().'&amp;name='.$name,
			'length'=>$this->length,
		];

		$Str=new CaptchaCallback(function($Template=null)use($params){
			if(!$Template)
				$Template=$this->Template;

			return($Template instanceof Template)
				? (string)$Template->Captcha($params)
				: (string)call_user_func($Template,$params);
		});
		$Str->creator=__CLASS__;

		return$Str;
	}

	/** Проверка капчи
	 * @param string $name Имя капчи, используется в случае, когда на странице выводится более одной капчи
	 * @param array|null $post Подмена $_POST массива. Полезно, в случае использования AJAX
	 * @return bool */
	public static function Check($name='captcha',$post=null)
	{
		if(!is_array($post))
			$post=&$_POST;

		if(!isset($_SESSION))
			Eleanor\StartSession(isset($post[$name]['s']) ? $post[$name]['s'] : '',$name);

		$check=isset($_SESSION[$name][''],$post[$name]['t'])
			? strcasecmp($_SESSION[$name][''],(string)$post[$name]['t'])==0
			: false;

		unset($_SESSION[$name]);

		return$check;
	}

	/** Вывод картинки
	 * @throws EE */
	public static function GetImage()
	{
		$width=isset($_GET['w']) ? (int)$_GET['w'] : 0;
		$height=isset($_GET['h']) ? (int)$_GET['h'] : 0;
		$name=isset($_GET['name']) ? (string)$_GET['name'] : 'captcha';

		if($width<80 or $width>300)
			$width=120;

		if($height<50 or $height>200 or $height>$width)
			$height=60;

		if(!isset($_SESSION))
			Eleanor\StartSession(isset($_GET['session']) ? (string)$_GET['session'] : '',$name);

		if(!isset($_SESSION[$name]))
		{
			header('Content-Type: image/png');
			#1px alpha png
			echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQImWP4//8/AwAI/AL+hc2rNAAAAABJRU5ErkJggg==');
			return;
		}

		$sess=$_SESSION[$name];
		$s='';
		$l=mb_strlen($sess['symbols'])-1;

		for($i=0;$i<$sess['length'];$i++)
			$s.=mb_substr($sess['symbols'],mt_rand(0,$l),1);

		$_SESSION[$name]['']=$s;
		$f_color=[mt_rand(0,100),mt_rand(0,100),mt_rand(0,100)];
		$b_color=[mt_rand(150,255),mt_rand(150,255),mt_rand(150,255)];
		$alen=strlen(self::ALPHABET);
		$fonts=glob(__DIR__.'/captcha/*.png');

		if(!$fonts)
			throw new EE('Captcha fonts was not found.');

		$font=imagecreatefrompng($fonts[array_rand($fonts)]);

		unset($fonts);
		imagealphablending($font,true);

		$ffw=imagesx($font);
		$ffh=imagesy($font)-1;
		$fm=[];
		$symbol=0;
		$rs=false;

		#Подготовим данные для нашего алфавита
		for($i=0;$i<$ffw and $symbol<$alen;$i++)
		{
			$trans=(imagecolorat($font,$i,0) >> 24)==127;

			if(!$rs and !$trans)
			{
				$fm[substr(self::ALPHABET,$symbol,1)]=['start'=>$i];
				$rs=true;
			}
			elseif($rs and $trans)
			{
				$fm[substr(self::ALPHABET,$symbol,1)]['end']=$i;
				$rs=false;
				$symbol++;
			}
		}

		$img=imagecreatetruecolor($width,$height);

		imagealphablending($img,true);

		$white=imagecolorallocate($img,255,255,255);
		$black=imagecolorallocate($img,0,0,0);

		imagefilledrectangle($img,0,0,$width-1,$height-1,$white);

		$x=1;

		for($i=0;$i<$sess['length'];$i++)
		{
			$odd=mt_rand(0,1);

			if($odd==0)
				$odd=-1;

			$m=$fm[substr($s,$i,1)];
			$y=(($i%2)*$sess['fluct']-$sess['fluct']/2)*$odd+mt_rand(-round($sess['fluct']/3),round($sess['fluct']/3))
				+($height-$ffh)/2;

			if($y<0)
				$y=0;

			$shift=0;

			if($i>0)
			{
				$shift=10000;

				for($sy=3;$sy<$ffh-10;$sy++)
					for($sx=$m['start']-1;$sx<$m['end'];$sx++)
					{
						$rgb=imagecolorat($font,$sx,$sy);
						$opacity=$rgb>>24;

						if($opacity<127)
						{
							$py=$sy+$y;

							if($py>=$height)
								break;

							$left=$sx-$m['start']+$x;

							for($px=min($left,$width-1);$px>$left-200 and $px>=0;$px--)
							{
								$color=imagecolorat($img,$px,$py) & 0xff;

								if($color+$opacity<170)
								{
									if($shift>$left-$px)
										$shift=$left-$px;

									break;
								}
							}
							break;
						}
					}

				if($shift==10000)
					$shift=mt_rand(4,6);
			}

			imagecopy($img,$font,$x-$shift,$y,$m['start'],1,$m['end']-$m['start'],$ffh);

			$x+=$m['end']-$m['start']-$shift;
		}

		for($i=0;$i<($height-30)*$x*$sess['wh_noise'];$i++)
			imagesetpixel($img,mt_rand(0,$x-1),mt_rand(10,$height-15),$white);

		for($i=0;$i<($height-30)*$x*$sess['bl_noise'];$i++)
			imagesetpixel($img,mt_rand(0,$x-1),mt_rand(10,$height-15),$black);

		$center=$x/2;
		$out_img=imagecreatetruecolor($width,$height);
		$background=imagecolorallocate($out_img,$b_color[0],$b_color[1],$b_color[2]);

		imagefilledrectangle($out_img,0,0,$width-1,$height-1,$background);

		$rand1=mt_rand(750000,1200000)/10000000;
		$rand2=mt_rand(750000,1200000)/10000000;
		$rand3=mt_rand(750000,1200000)/10000000;
		$rand4=mt_rand(750000,1200000)/10000000;
		$rand5=mt_rand(0,31415926)/10000000;
		$rand6=mt_rand(0,31415926)/10000000;
		$rand7=mt_rand(0,31415926)/10000000;
		$rand8=mt_rand(0,31415926)/10000000;
		$rand9=mt_rand(330,420)/110;
		$rand10=mt_rand(330,450)/110;

		#Искривление изображения
		for($x=0;$x<$width;$x++)
			for($y=0;$y<$height;$y++)
			{
				$sx=$x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy=$y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if($sx<0 or $sy<0 or $sx>=$width-1 or $sy>=$height-1)
					continue;
				else
				{
					$color=imagecolorat($img,$sx,$sy) & 0xFF;
					$color_x=imagecolorat($img,$sx+1,$sy) & 0xFF;
					$color_y=imagecolorat($img,$sx,$sy+1) & 0xFF;
					$color_xy=imagecolorat($img,$sx+1,$sy+1) & 0xFF;
				}

				if($color==255 and $color_x==255 and $color_y==255 and $color_xy==255)
					continue;
				elseif($color==0 and $color_x==0 and $color_y==0 and $color_xy==0)
				{

						$nr=$f_color[0];
						$ng=$f_color[1];
						$nb=$f_color[2];

					#continue;
				}
				else
				{
					$frsx=$sx-floor($sx);
					$frsy=$sy-floor($sy);
					$frsx1=1-$frsx;
					$frsy1=1-$frsy;
					$newcolor=$color*$frsx1*$frsy1+$color_x*$frsx*$frsy1+$color_y*$frsx1*$frsy+$color_xy*$frsx*$frsy;

					if($newcolor>255)
						$newcolor=255;

					$newcolor=$newcolor/255;
					$newcolor0=1-$newcolor;
					$nr=$newcolor0*$f_color[0]+$newcolor*$b_color[0];
					$ng=$newcolor0*$f_color[1]+$newcolor*$b_color[1];
					$nb=$newcolor0*$f_color[2]+$newcolor*$b_color[2];
				}

				imagesetpixel($out_img,$x,$y,imagecolorallocate($out_img,$nr,$ng,$nb));
			}

		header('Cache-Control: no-store');

		if(function_exists('imagejpeg'))
		{
			header('Content-Type: image/jpeg');
			imagejpeg($out_img,null,80);
		}
		elseif(function_exists('imagegif'))
		{
			header('Content-Type: image/gif');
			imagegif($out_img);
		}
		elseif(function_exists('imagepng'))
		{
			header('Content-Type: image/png');
			imagepng($out_img,null,80);
		}
	}
}