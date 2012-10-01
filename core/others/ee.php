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
class EE extends Exception
{	public
		$code,#��� ������
		$addon;#������ � ������������ ��� ������, ���� ���������� �� �������. �����: log -������� �����������, lfile - ���� ���� ����������, mess - ������, ������� ����� �������� ������������

	const
		FATAL=1,#�������� ������ ���������� � ��������. �������� � �������.
		CRITICAL=2,#�������� ������, ��� � � ���������� �������� ����������, �� ���������� ������ ��� ������. �������, ������� �� �� ��������
		INFO=3,#�������������� ������: ����� ����������� ��������� ����� ��� ����������� ����� ������ ���������� �� ������������. �� ��������, �� �������.
		ACCESS=4,#������ �������. ����� ������������ �� ����� �������� ������ ����, ���� �� ������� (403 ��� 404 ������)
		BAN=5,#������ ����, ����� ������������ ������������ �������� ban.html, ����������� ������������ � ���, ��� �� �������. �� ��������, �������.
		DEV=6,#������ ������������: ��������� � �������������������� ����������, ��������, ������. ��������, �� �� �������.
		ENV=7,#������ �����: ����� ��� ������� ��� ������/������ � ����, ��� ������ ����� � �.�. ��������. ���� �� ������� - �������.
		ALT=8;#�������������� ������: ���� ������� - ������ �� ������, ���� �� ������� - ��������, ���������� ������ � �������. �� ��� ��������� ����� ������ �����

	public static
		$vars;

	public function __construct($mess,$code=self::FATAL,$addon=array(),$PO=null)
	{		if(!isset(self::$vars))
		{			self::$vars=array();			self::$vars+=isset(Eleanor::$Db)
				? Eleanor::LoadOptions('errors',true)
				: array(
					'log_errors'=>'addons/logs/errors.log',
					'log_maxsize'=>1048576,#10 Mb
				);
		}

		if(!empty($addon['lang']))
		{			$le=Eleanor::$Language['exceptions'];
			if(isset($le[$mess]))
			{				$addon['code']=$mess;				$mess=is_callable($le[$mess]) ? $le[$mess]($addon) : $le[$mess];			}		}

		parent::__construct($mess,$code,$PO);
		if(isset($addon['file']))
			$this->file=$addon['file'];
		if(isset($addon['line']))
			$this->line=$addon['line'];
		switch($code)
		{
			case self::FATAL:
				self::LogIt(self::$vars['log_errors'],$mess);
			case self::CRITICAL:
				Error($mess);
			break;
			case self::BAN:
				$addon['ban']=true;
			break;
			case self::DEV:
			case self::ENV:
				self::LogIt(self::$vars['log_errors'],$mess);
			break;
			case self::ALT:
				$addon+=array('log'=>true,'logfile'=>self::$vars['log_errors']);
			case self::ACCESS:
			case self::INFO:
				#ToDo! ���� �� �������� ��� ���� ��������.
		}
		$this->addon=$addon+array('log'=>false,'logfile'=>false);
	}

	public function LogIt($fn,$message)
	{		if(!$fn or Eleanor::$nolog)
			return;
		$path=Eleanor::FormatPath($fn);
		if(!is_writeable(dirname($path)))
			die('File '.$fn.' is write-protected!');
		if(self::$vars['log_maxsize'] and is_file($path) and filesize($path)>(int)self::$vars['log_maxsize'])
		{
			if(self::CompressFile($path,substr($path,0,strrpos($path,'.')).'_'.date('Y-m-d_H-i-s')))
				unlink($path);
			clearstatcache();
		}
		if($fh=fopen($path,'a'))
		{			$f=$this->getFile();
			$l=$this->getLine();			$dt=date('Y-m-d H:i:s');
			$url=Url::Decode($_SERVER['REQUEST_URI']);
			flock($fh,LOCK_EX);
			fwrite($fh,$message.PHP_EOL.($f ? 'Line: '.$l.' in file '.$f.PHP_EOL : '').'URL: '.$url.($_POST ? PHP_EOL.'POST: '.str_replace("\n",'',var_export($_POST,true)) : '').PHP_EOL.'Date: '.$dt.PHP_EOL.'IP: '.Eleanor::$ip."\r\n\r\n");
			flock($fh,LOCK_UN);
			fclose($fh);
		}
	}

	/*
		����������� ������� ��� �������� ������.
	*/
	static function CompressFile($from,$to)
	{
		if(!is_file($from) or file_exists($to))
			return false;
		if(!is_writable(substr($to,0,strrpos($to,'/'))))
			return false;
		$hf=fopen($from,'r');
		$r=false;
		if(function_exists('bzopen') and $hbz=bzopen($to.'.bz2','w'))
		{
			while(!feof($hf))
				bzwrite($hbz,fread($hf,1024*16));
			bzclose($hbz);
			$r=true;
		}
		elseif(function_exists('gzopen') and $hgz=gzopen($to.'.gz','w9'))
		{
			while(!feof($hf))
				gzwrite($hgz,fread($hf,1024*64));
			gzclose($hgz);
			$r=true;
		}
		fclose($hf);
		return$r;
	}
}