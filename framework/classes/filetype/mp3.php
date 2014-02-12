<?php
/*
	Copyright © Eleanor CMS
	http://eleanor-cms.ru
	info@eleanor-cms.ru

	Извлечение информации из mp3 файла
	Code based on http://habrahabr.ru/post/103635/
*/
namespace Eleanor\Classes\FileType;
use Eleanor\Classes;
use Eleanor;

class Mp3 extends Eleanor\BaseClass
{
	/**
	 * Получение информации о mp3-файле
	 * @param string $mp3file Путь к mp3 файлу
	 * @throws Classes\EE
	 * @return array
	 */
	public static function GetData($mp3file)
	{
		if(!is_file($mp3file) or !$fh=fopen($mp3file,'rb'))
			throw new Classes\EE('File not found',Classes\EE::ENV);

		#Определение ID3 тегов
		$ret=[
			'id3v1'=>static::GetID3v1($fh),
			'id3v2'=>static::GetID3v2($fh),
		];

		#Если есть id3v2 тег, то перед поиском mp3 фрейма сдвигаем указатель файла за id3v2 тег
		if($ret['id3v2'] and $ret['id3v2']['size']>0)
			fseek($fh,$ret['id3v2']['size']+10);

		#Ищем mp3 фрейм. - 11111111-11111111-1111111? (0xFFF(E))
		do
		{
			$chr255=chr(255);
			while(fread($fh,1)!=$chr255)
				if(feof($fh))
					return'MP3 frame not found.';

			$s=fread($fh, 3);
			$header=sprintf('%08b%08b%08b%08b',255,ord($s[0]),ord($s[1]),ord($s[2]));
		}while($header[0]!=1 and $header[1]!=1 and $header[2]!=1);

		#Нашли первый mp3 фрейм. Читаем информацию
		if($header[11]==0)
			$ret['id']='MPEG-2.5';
		else
			$ret['id']=$header[12]==1 ? 'MPEG-1' : 'MPEG-2';

		$layers=[
			[0, 3],
			[2, 1]
		];
		$ret['layer']=$layers[ $header[13] ][ $header[14] ];

		$ret['protect_CRC']=$header[15]==0;

		$bitrates['MPEG-1']=[1=>
			[0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448],#MPEG-1 Layer I
			[0, 32, 48, 56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 384],#MPEG-1 Layer II
			[0, 32, 40, 48,  56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320],#MPEG-1 Layer III
		];
		$bitrates['MPEG-2']=[1=>
			[0, 32, 64, 96, 128, 160, 192, 224, 256, 288, 320, 352, 384, 416, 448],#MPEG-2 Layer I
			[0, 32, 48, 56,  64,  80,  96, 112, 128, 160, 192, 224, 256, 320, 384],#MPEG-2 Layer II
			[0, 8,  16, 24,  32,  64,  80,  56,  64, 128, 160, 112, 128, 256, 320],#MPEG-2 Layer III
		];
		$bitrates['MPEG-2.5']=[1=>
			[0, 32, 48, 56, 64, 80, 96, 112, 128, 144, 160, 176, 192, 224, 256, 0],#MPEG-2.5 Layer I
			[0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0],#MPEG-2.5 Layer II
			[0,  8, 16, 24, 32, 40, 48,  56,  64,  80,  96, 112, 128, 144, 160, 0],#MPEG-2.5 Layer III
		];

		$last=bindec($header[16].$header[17].$header[18].$header[19]);
		$ret['bitrate']=isset($bitrates[ $ret['id'] ][ $ret['layer'] ][ $last ])
			? $bitrates[ $ret['id'] ][ $ret['layer'] ][ $last ]
			: 0;

		$frequency=[
			'MPEG-1'=>[
				[44100, 48000],
				[32000, 0],
			],
			'MPEG-2'=>[
				[22050, 24000],
				[16000, 0],
			],
			'MPEG-2.5'=>[
				[11025, 12000],
				[8000, 0],
			],
		];
		$ret['frequency']=$frequency[ $ret['id'] ][ $header[20] ][ $header[21] ];
		$ret['padding']=$header[22];

		$samples_per_frame=[
			'MPEG-1'	=>[1=>384, 1152, 1152],
			'MPEG-2'	=>[1=>384, 1152, 576],
			'MPEG-2.5'	=>[1=>384, 1152, 576],
		];

		$modes=[
			['Stereo','Joint stereo'],
			['Dual channel','Mono']
		];
		$ret['mode']=$modes[ $header[24] ][ $header[25] ];

		#Если режим = Joint Stereo
		if($header[24]==0 and $header[25]==1)
		{
			$ret['Intensity stereo']=$header[26];
			$ret['MS stereo']=$header[27];
		}

		$ret['Copyrighted']=$header[28];
		$ret['Original']=$header[29];

		$emphasises=[
			['None','50/15ms'],
			['','CCITT j.17']
		];

		$ret['Emphasis']=$emphasises[ $header[30] ][ $header[31] ];

		if($ret['mode']!='Mono' and $ret['id']=='MPEG-1')
			$offset=32;
		elseif($ret['mode']=='Mono' and $ret['id']=='MPEG-1')
			$offset=17;
		elseif($ret['mode']=='Mono' and $ret['id']=='MPEG-2' || $ret['id']=='MPEG-2.5')
			$offset=9;
		else
			$offset=17;

		fseek($fh,$offset,SEEK_CUR);
		$s=fread($fh,32);

		$ret['size']=$datasize=filesize($mp3file);

		if(substr($s,0,4)=='VBRI')
		{
			$ret['bitrate_mode']='VBR';
			$ret['VBR_header']='VBRI';
			$ret['nof']=bindec(sprintf('%08b%08b%08b%08b',ord($s[14]),ord($s[15]),ord($s[16]),ord($s[17])));
			$duration=floor($ret['nof'] * $samples_per_frame[ $ret['id'] ][ $ret['layer'] ] / $ret['frequency']);
		}
		elseif(substr($s,0,4)=='Xing')
		{
			$ret['bitrate_mode']='VBR';
			$ret['VBR_header']='Xing';
			$frames=bindec(sprintf('%08b%08b%08b%08b', ord($s[8]),ord($s[9]),ord($s[10]),ord($s[11])));
			$duration=floor($frames * $samples_per_frame[ $ret['id'] ][ $ret['layer'] ] / $ret['frequency']);
		}
		else
		{
			$ret['bitrate_mode']='CBR';

			if($ret['id3v1'])
				$datasize-=128;

			if($ret['id3v2'])
				$datasize-=$ret['id3v2']['size'] - 10;

			$duration=$ret['bitrate']>0 ? floor($datasize / ($ret['bitrate'] * 1000) * 8) : 0;
		}

		$ret['duration_str']=sprintf('%02d:%02d',floor($duration/60),floor($duration-(floor($duration/60)*60)));
		$ret['duration_str_hour']=sprintf('%02d:%02d:%02d',floor($duration/3600),floor($duration/60),floor($duration -(floor($duration/60)*60)));
		$ret['duration']=(int)$duration;

		fclose($fh);

		return$ret;
	}

	/**
	 * Получение информации из ID3v2 mp3-файла
	 * @param resource $fh Результат вызова fopen('file.mp3','rb')
	 * @return array|bool
	 */
	public static function GetID3v2($fh)
	{
		fseek($fh,0,SEEK_SET);

		$header=fread($fh,10);
		$header=unpack('a3signature/c1version_major/c1version_minor/c1flags/Nsize',$header);

		if($header['signature']!='ID3')
			return false;

		$bsize=sprintf('%032b',$header['size']);
		$result=array(
			'version'=>$header['version_major'].'.'.$header['version_minor'],
			'size'=>bindec(substr($bsize, 1, 7).substr($bsize, 9, 7).substr($bsize, 17, 7).substr($bsize, 25, 7)),
		);
		$tags=[
			'TALB'=>'Album',
			'TCON'=>'Genre',
			'TENC'=>'Encoder',
			'TIT2'=>'Title',
			'TPE1'=>'Artist',
			'TPE2'=>'Ensemble',
			'TYER'=>'Year',
			'TCOM'=>'Composer',
			'TCOP'=>'Copyright',
			'TRCK'=>'Track',
			#'WXXX'=>'URL',
			'COMM'=>'Comment',
		];

		for($i=0;$i<22;$i++)
		{
			$tag=rtrim(fread($fh,6));
			if(!isset($tags[$tag]))
				break;

			$size=fread($fh,2);
			$size=@unpack('n',$size);
			$size=$size[1]+2;

			$value=fread($fh,$size);
			$value=static::decTag($value,$tag);

			$result[ $tags[$tag] ]=$value;
		}

		return$result;
	}

	/**
	 * Получение информации из ID3v1 mp3-файла
	 * @param resource $fh Результат вызова fopen('file.mp3','rb')
	 * @return string|bool
	 */
	public static function GetID3v1($fh)
	{
		fseek($fh,-128,SEEK_END);

		$s=fread($fh,128);
		$ret=unpack($s[125]==chr(0) && $s[126]!=chr(0) ? 'a3tag/a30name/a30artists/a30album/a4year/a28comment/x1/c1track/c1genreno' : 'a3tag/a30name/a30artists/a30album/a4year/a30comment/c1genreno',$s);

		return$ret['tag']=='TAG' ? $ret : false;
	}

	protected static function decTag($tag,$type)
	{
		if($type=='COMM')
			$tag=substr($tag,0,3).substr($tag,10);

		switch(ord($tag[2]))
		{
			case 0:#ISO-8859-1
				return substr($tag,3);
			case 1:#UTF-16 BOM
				return mb_convert_encoding(substr($tag,5),Eleanor\CHARSET,'UTF-16LE');
			case 2:#UTF-16BE
				return mb_convert_encoding(substr($tag,5),Eleanor\CHARSET,'UTF-16BE');
			case 3:#UTF-8
				return substr($tag,3);
		}

		return false;
	}
} 