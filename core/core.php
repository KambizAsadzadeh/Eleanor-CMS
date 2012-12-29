<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

#ToDo! array() => []
#�������� �������� ���� �����
ignore_user_abort(true);
error_reporting(E_ALL^E_NOTICE);
set_error_handler(array('Eleanor','ErrorHandle'));
set_exception_handler(array('Eleanor','ExceptionHandle'));
define('ELENT',defined('ENT_HTML5') ? ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE | ENT_DISALLOWED : ENT_QUOTES);#ToDo! PHP 5.4 ������� defined
spl_autoload_register(array('Eleanor','Autoload'));

abstract class BaseClass
{	/**
	 * ��������� �������������� ������ � ����: ���� + ������
	 *
	 * @param array $d ���� ����� ������ ��� ������ ������� debug_backtrace
	 */
	private static function _BT($d)
	{
		foreach($d as &$v)
			if(isset($v['file'],$v['line']))
				return$v;
		return array('file'=>'-','line'=>'-');
	}

	/**
	 * ��������� ��������� ������� �������������� ����������� �������
	 *
	 * ������� ����� ������ ����� ���������� ��������: ���� ���� ������� �������������� ����������� �����, ����� ������������ Fatal error,
	 * ������� ����� �������� � ������������. �� �������� ������ ����������� � ������ ���������� � ������� __callStatic ������� �� �����
	 * ��������� ��� ���������� ������.
	 *
	 * @param string $n �������� ��������������� ������
	 * @param array $p ������ �������� ���������� ����������� ������
	 */
	public static function __callStatic($n,$p)
	{
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_called_class().' :: '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}

	/**
	 * ��������� ��������� ������� �������������� �������
	 *
	 * ������� ����� ������ ����� ���������� ��������: ���� ���� ������� �������������� ����� �������, ����� ������������ Fatal error,
	 * ������� ����� �������� � ������������. �� �������� ������ ����������� � ������ ���������� � ������� __call ������� �� �����
	 * ��������� ��� ���������� ������.
	 *
	 * @param string $n �������� ��������������� ������
	 * @param array $p ������ �������� ���������� ����������� ������
	 */
	public function __call($n,$p)
	{		if(property_exists($this,$n) and is_object($this->$n) and method_exists($this->$n,'__invoke'))
			return call_user_func_array(array($this->$n,'__invoke'),$p);
		$d=self::_BT(debug_backtrace());
		$E=new EE('Called undefined method '.get_class().' -� '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}

	/**
	 * ��������� ��������� �������������� �������
	 *
	 * ������� ����� ������ ����� ���������� ��������: ���������, ��� ������� �������� �������������� �������� ������������ Notice,
	 * ������� ����� �������� � ������������. �� �������� ������ ����������� � ������ ���������� � ������� __get, ������� �����
	 * ������� �� ��� ������������� ��������.
	 *
	 * @param string $n ��� �������������� ��������
	 */
	public function __get($n)
	{
		if(is_array($n))
		{
			$d=$n;
			$n=func_get_arg(1);
		}
		else
			$d=debug_backtrace();
		$d=self::_BT($d);
		$E=new EE('Trying to get value from the unknown variable '.get_class($this).' -� '.$n,EE::DEV,array('file'=>$d['file'],'line'=>$d['line']));
		if(DEBUG)
			throw$E;
		$E->Log();
	}
}

final class GlobalsWrapper implements ArrayAccess
{
	private
		$vn;

	/**
	 * �������� �������� ���������� ����������. ����������: ���������� ���������� ������ ���� ��������
	 *
	 * @param string $vn ��� ���������� ����������, ��� ������� ��������� ��������
	 */
	public function __construct($vn)
	{
		$this->vn=$vn;
	}

	/**
	 * ��������� �������� �������� ���������� ����������
	 *
	 * @param string $k ��� ��������, ���� �������
	 * @param mixed $v ��������
	 */
	public function offsetSet($k,$v)
	{
		$GLOBALS[$this->vn][$k]=$v;
	}

	/**
	 * �������� ������������� ������������� ��������
	 *
	 * @param string $k ��� ��������, ���� �������
	 */
	public function offsetExists($k)
	{
		return isset($GLOBALS[$this->vn][$k]);
	}

	/**
	 * �������� ������������� ��������
	 *
	 * @param string $k ��� ��������, ���� �������
	 */
	public function offsetUnset($k)
	{
		unset($GLOBALS[$this->vn][$k]);
	}

	/**
	 * ��������� ������������� ��������
	 *
	 * @param string $k ��� ��������, ���� �������
	 */
	public function offsetGet($k)
	{
		return isset($GLOBALS[$this->vn][$k]) ? self::Filter($GLOBALS[$this->vn][$k]) : null;
	}

	/**
	 * �������������� �������� HTML � ���������� (������� ��� �������� htmlspecialchars)
	 *
	 * @param string|array $s ������ � ������� HTML
	 */
	public static function Filter($s)
	{
		if(is_array($s))
		{
			foreach($s as &$v)
				$v=self::Filter($v);
			unset($v);
			return$s;
		}
		return htmlspecialchars($s,ELENT,CHARSET,false);
	}
}

/**
 * @property Db $Db �������� ������ ���� ������
 * @property Db $UsersDb ������ ���� ������, ��� ������� � ������� ������������� (��� ���������� �������������), ��� ����������� - ������ �� $Db
 * @property Cache $Cache �������� ������ ���� �������
 * @property TemplateMixed $Template ������������ �������
 * @property Language $Language �������� ������, ��� ����������� ��� � ������ - ������ ��� �����
 * @property LoginClass $Login ������ �������� ������. ������ ������, � �� ������ (�������� ������), ������ ���� �������� ������� � �������
 * @property Permissions $Permissions ������ ���������� �������� ������
 * @property GlobalWrapper $POST,#��������������� POST ������
 */
final class Eleanor extends BaseClass
{
	public static
		$uploads='uploads',#������� �������� ����������� ������

		#���������� ����������
		$debug=array(),#������, ���� ���������� ������ �������, ��� ����������� �� ������

		#�������� ������������ ��������
		$gzip=true,#��������� GZIP ������
		$charset,#��������� � ���������� charset
		$caching,#���������� �� �������� � �� �������
		$last_mod,#��������� ��������� TIMESTAMP �������� �� �������
		$modified,#��������� ��������� TIMESTAMP �������� �� ��������
		$maxage=0,#���� ����� ���� �� ������� ��������, ��� �������������� ��������� �� ������� �������. � ���� ��������� ����� ������ �������������� ��������� ����� �������, ��������: 0, public
		$etag,#Etag ��������
		$content_type='text/html',#��������� � ���������� content-type

		#�������� �����
		$domain,#Readonly. ����� ������� �������
		$punycode,#Readonly. Punycode ������. ���� ����� ���������� - ��� ������ �� domain
		$site_path,#Readonly. ������� ����� ������������ ������
		$filename,#Readonly. ��� �����-�������, ������������ ���� ������. ������������ ���� ����� ��� ������������� ������

		#�������� ������������
		$ip,#�����, ������ ������ ������
		$ips=array(),#������ �� ����� �����������, ����������� ��� �� ������������
		$our_query=true,#�������, ��� ������������ ������ �� ��� �������� �� ����� �������� (� �� � ����� �������� ����� ��������). ��������� ����� ��������� �������� �� �������.
		$sessextra='',#�������������� ������, ������� ����� �������� � ������� ������. ������� ��� �������� ���� �� ���������� �������: ��� ���� ������ N �������������
		$is_bot,#�������� ���? ���? - �����. ��� ���������� ����

		#������ ������
		$langs=array(),#������ ������
		$lvars=array(),#������ ���������� ���������������� ��������, ��� ������� �������� ���������������
		$vars=array(#����������, ������ �� �������.
			'page_caching'=>false,
			'gzip'=>true,
			'cookie_domain'=>'',
			'site_domain'=>'',
			'cookie_save_time'=>86400,
			'cookie_prefix'=>'',
			'multilang'=>false,
			'bot_group'=>0,
			'guest_group'=>0,
			'parked_domains'=>'',

			'bots_enable'=>false,
			'bots_list'=>array(),
			'time_online'=>array(),
		),
		$services,#������ ���� ��������
		$perms=array(),#������ ����������. [�������] => [ID] => [�����] => ��������

		#�������
		$Db,#������ ���� ������
		$UsersDb,#������ ���� ������, ��� ������� � ������� ������������� (��� ���������� �������������), ��� ����������� - ������ �� $Db
		$Cache,#���
		$Template,#������ ����������
		$Language,#�������� ������, ��� ����������� ��� � ������ - ������ ��� �����
		$Login,#������ �������� ������. ������ ������, � �� ������ (�������� ������), ������ ���� �������� ������� � �������
		$Permissions,#���������� �������� ������
		$POST,#��������������� POST ������

		#��������� ��������
		$os,#��� �������, �� ������� ����� ���� u - *nix, w - windows
		$root,#������ �����
		$rootf,#������ �����, � �������� �� �����������
		$service,#�������
		$nolog=false;#���� ���������� ����������� ������

	private static
		$Instance;#������������ ������ ����� ������. Singleton

	/**
	 * ��������� ������������� ������� ����� ������. ��� ������ ������� - ����������� � ������������ ����������:
	 *
	 * @param string $conf ���� ����� � ��������������
	 */
	public static function getInstance($conf='config_general.php')
	{
		if(!isset(self::$Instance))
		{
			self::$Instance=new self;
			self::$root=dirname(dirname(__file__)).DIRECTORY_SEPARATOR;
			self::$rootf=dirname($_SERVER['SCRIPT_FILENAME']).DIRECTORY_SEPARATOR;
			self::$filename=basename($_SERVER['SCRIPT_FILENAME']);
			chdir(self::$root);
			#Detect IP
			self::$ip=$_SERVER['REMOTE_ADDR'];
			foreach(array('HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED_FOR','HTTP_FORWARDED','HTTP_X_COMING_FROM','HTTP_COMING_FROM','HTTP_CLIENT_IP','HTTP_X_CLUSTER_CLIENT_IP','HTTP_PROXY_USER','HTTP_XROXY_CONNECTION','HTTP_PROXY_CONNECTION','HTTP_USERAGENT_VIA') as $v)
			{
				$ip=getenv($v);
				if($ip!=self::$ip and $ip)
					self::$ips[$v]=$ip;
			}
			self::$ips=array_unique(self::$ips);
			#Detect IP [E]
			self::$domain=isset($_SERVER['HTTP_HOST']) && preg_match('#^[a-z0-9\-\.]+$#i',$_SERVER['HTTP_HOST'])>0 ? $_SERVER['HTTP_HOST'] : false;
			self::$site_path=rtrim(dirname($_SERVER['PHP_SELF']),'/\\').'/';
			if(self::$filename and false!==$t=strpos(self::$site_path,self::$filename))
				self::$site_path=substr(self::$site_path,0,$t);
			self::$POST=$_SERVER['REQUEST_METHOD']=='POST' ? new GlobalsWrapper('_POST') : array();
			self::$os=stripos(PHP_OS,'win')===0 ? 'w' : 'u';

			$c=false;
			if($conf)
			{
				$conf=self::FormatPath('',$conf);
				if(is_file($conf))
				{
					$c=include $conf;
					self::$langs=$c['langs'];
				}
			}

			#� ��-�� ����������, ����������� ��� ���?
			if(!defined('CHARSET'))
			{
				if(is_file(self::$root.'install/index.php') and !headers_sent())
					header('Location: http://'.self::$domain.self::$site_path.'install/');
				die('CMS Eleanor not installed!');
			}

			if(!DEBUG)
			{
				if(isset($_SERVER['HTTP_IF_NONE_MATCH']))
					self::$etag=$_SERVER['HTTP_IF_NONE_MATCH'];
				if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
					self::$modified=strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}

			self::$charset=DISPLAY_CHARSET;
			mb_internal_encoding(CHARSET);
			self::$Language=new Language(true);
			self::$Language->Change();
			self::$Cache=new Cache;
			if($c)
			{
				self::$Db=new Db(array(
					'host'=>$c['db_host'],
					'user'=>$c['db_user'],
					'pass'=>$c['db_pass'],
					'db'=>$c['db'],
				));
				if(isset($c['users']))
					self::$UsersDb=new Db(array(
						'host'=>$c['users']['db_host'],
						'user'=>$c['users']['db_user'],
						'pass'=>$c['users']['db_pass'],
						'db'=>$c['users']['db'],
					));
				else
					self::$UsersDb=&self::$Db;
				if(!isset(self::$services) and false===self::$services=self::$Cache->Get('system-services'))
				{
					self::$services=array();
					self::$Db->Query('SELECT `name`,`file`,`theme`,`login` FROM `'.P.'services`');
					while($a=self::$Db->fetch_assoc())
						self::$services[$a['name']]=array_slice($a,1);
					self::$Cache->Put('system-services',self::$services);
				}

				self::LoadOptions('system');
				if(self::$vars['time_zone'])
				{
					date_default_timezone_set(self::$vars['time_zone']);
					self::$Db->SyncTimeZone();
					if(self::$UsersDb!==self::$Db)
						self::$UsersDb->SyncTimeZone();
				}

				$task='';
				if(isset(self::$services['cron']))
				{
					$task=self::$Cache->Get('nextrun',true);
					$t=time();
					$task=$task===false || $task<=$t ? '<img src="'.self::$services['cron']['file'].'?rand='.$t.'" style="width:1px;height1px;" />' : '';
				}
				if(defined('ELEANOR_COPYRIGHT'))
					die('Copyright defined!');
				else
					#��������! ����������� �������� ���������� ������� ����������� �� ����������� ����� ������� � ������������ �� ������!
					#��������� ������/������� ������! ������!! ��� ������ ���������� ����������� �� ����!
					define('ELEANOR_COPYRIGHT','<!-- ]]></script> --><a href="http://eleanor-cms.ru/" target="_blank">CMS Eleanor</a> � <!-- Eleanor CMS Team http://eleanor-cms.ru/copyright.php -->'.idate('Y').$task);

				$r=getenv('HTTP_REFERER');
				if($r and preg_match('#^'.PROTOCOL.'('.self::$vars['site_domain'].'|'.self::$domain.')'.self::$site_path.'#',$r)==0)
					self::$our_query=false;

				self::$caching=self::$vars['page_caching'];
				self::$gzip=self::$vars['gzip'];
				if(self::$vars['cookie_domain'])
					self::$vars['cookie_domain']=str_replace('*',preg_replace('#^www\.#i','',self::$domain),self::$vars['cookie_domain']);
				if(self::$vars['parked_domains']=='redirect' and self::$vars['site_domain'] or !self::$domain)
					self::$domain=self::$vars['site_domain'];
				#�������� ��� ��������� FF & IE, ����� ��� �� ����� ������������ ���� � ������� ������� ������ ��� localhost
				if(strpos(self::$domain,'.')===false)
					self::$vars['cookie_domain']='';
				#�������� ��� ����� � ��, ������� �� ����� ������������ ���� � IP �������
				elseif(strpos(self::$domain,':')!==false or preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#',self::$domain)!=0)
					self::$vars['cookie_domain']='';
				#��������� IDN
				if(strpos(self::$domain,'xn--')!==false)
				{
					self::$punycode=self::$domain;
					self::$domain=Punycode::Domain(self::$domain,false);
				}
				elseif(preg_match('#^[a-z0-9\-\.]+$#i',self::$domain)==0)
					self::$punycode=Punycode::Domain(self::$domain);
				else
					self::$punycode=&self::$domain;
				$ips=explode(',',self::$vars['blocked_ips']);
				foreach($ips as &$bip)
				{
					if(strpos($bip,'=')!==false)
					{
						$m=substr($bip,strpos($bip,'=')+1);
						$bip=substr($bip,0,strpos($bip,'='));
					}
					else
						$m=self::$vars['blocked_message'];
					if(self::IPMatchMask(self::$ip,$bip))
						throw new EE($m,EE::USER,array('ban'=>'ip'));
					foreach(self::$ips as &$ip)
						if(self::IPMatchMask($ip,$bip))
							throw new EE($m,EE::USER,array('ban'=>'ip'));
				}
				unset(self::$vars['blocked_ips']);
			}
		}
		return self::$Instance;
	}

	/**
	 * ������ ��������� Singleton
	 */
	private function __construct(){}

	/**
	 * ����� �������� �������� �������� �������
	 *
	 * @param string $n ��� ������
	 */
	public function __get($n)
	{
		if(class_exists($n))
			return$this->$n=new$n;
		return parent::__get(debug_backtrace(),$n);
	}

	/**
	 * ���������� ���� ����������� ������
	 *
	 * @param int $num ����� ������
	 * @param string $str �������� ������
	 * @param string $f ����, � ������� �������� ������
	 * @param string $l ������ � �����, �� ������� �������� ������
	 */
	public static function ErrorHandle($num,$str,$f,$l)
	{		if(self::$nolog or $num&E_STRICT)
			return;
		$ae=array(
			E_ERROR=>'Error',
			E_WARNING=>'Warning',
			E_NOTICE=>'Notice',
			E_PARSE=>'Parse error',
		);
		if(class_exists('EE'))#�������� �� ������ ������������ �����������
		{
			$E=new EE((isset($ae[$num]) ? $ae[$num].': ' : '').$str,EE::DEV,array('file'=>$f,'line'=>$l));
			if(DEBUG and !E_PARSE&$num)
				throw$E;
			$E->Log();
		}
	}

	/**
	 * ���������� ��������������� ����������
	 *
	 * @param exception $E ������ ���������������� ����������
	 */
	public static function ExceptionHandle($E)
	{		$m=$E->getMessage();
		if($E instanceof EE)
			$E->Log();
		else
		{
			$E2=new EE($m,EE::UNIT,array(),$E);
			$E2->Log();
		}
		Error($m,isset($E->extra) ? $E->extra : array());
	}

	/**
	 * ������������� ����������� �������
	 *
	 * @param string $cl ��� ������, ������� ����� ���������
	 */
	public static function Autoload($cl)
	{
		if(is_file($f=self::$root.'core/others/'.strtolower($cl).'.php'))
			require$f;
		else
		{
			if(class_exists('EE',false) or include(self::$root.'core/others/ee.php'))
			{
				$d=debug_backtrace();
				$a=array();
				foreach($d as &$v)
					if(isset($v['file'],$v['line']) and $v['file']!=__file__)
					{
						$a['file']=$v['file'];
						$a['line']=$v['line'];
						break;
					}
				throw new EE('Class not found: '.$cl,EE::DEV,$a);
			}
			trigger_error('Class not found: '.$cl,E_USER_ERROR);
		}
	}

	/**
	 * ������������� �������, � �������� �� �����������. ������ - ��� ����, � �������� ���������� ������ �������: index.php, admin.php, ajax.php � �.�.
	 */
	public static function InitService()
	{
		if(self::$service and isset(self::$services[self::$service]))
			$a=self::$services[self::$service];
		else
			throw new EE('Unknown service!');
		if($a['file']!=self::$filename)
		{
			self::$Db->Update(P.'services',array('file'=>self::$filename),'`name`=\''.self::$service.'\' LIMIT 1');
			self::$Cache->Obsolete('system-services');
		}
		self::ApplyLogin($a['login']);
	}

	/**
	 * �������� ��������
	 *
	 * @param string|array $need �������� ����� ����� ��������, ������� ������ ���� ���������
	 * @param bool $r ���� �������� ���������� ��������. � ������ �������� FALSE, ���������� ��������� ����� �������� � ������ Eleanor::$vars
	 * @param bool $cache ���� ��������� ����������� ��������
	 */
	public static function LoadOptions($need,$r=false,$cache=true)
	{
		$need=(array)$need;
		$lgetted=$getted=array();
		if($cache)
			foreach($need as $k=>&$v)
				if($value=self::$Cache->Lib->Get('config-'.$v))
				{
					unset($need[$k]);
					foreach($value['v'] as $ok=>&$ov)
					{
						$getted[$ok]=$ov;
						$lgetted[$ok]=in_array($ok,$value['m']);
					}
				}
		if($need)
		{
			$kw=array();
			foreach($need as &$v)
				$kw[]=preg_quote(self::$Db->Escape($v,false));
			$ml=$config=$cache=array();
			$oid=0;
			$ogname='';
			self::$Db->Query('SELECT `o`.`id`,`o`.`name`,`l`.`value`,`l`.`serialized`,`l`.`language`,`o`.`multilang`,`g`.`name` `gname`,`g`.`keyword` FROM `'.P.'config` `o` INNER JOIN `'.P.'config_l` `l` USING(`id`) INNER JOIN `'.P.'config_groups` `g` ON `g`.`id`=`o`.`group` WHERE `g`.`keyword` REGEXP \''.join('|',$kw).'\' ORDER BY `o`.`id` ASC');
			while($a=self::$Db->fetch_assoc())
			{
				if($a['serialized'])
					$a['value']=unserialize($a['value']);
				if($oid==$a['id'])
				{
					if($a['multilang'])
						$cache[$a['gname']][$a['name']][$a['language']]=$a['value'];
					if($a['multilang'] or $a['language']!=Language::$main)
						continue;
				}
				$oid=$a['id'];
				if($ogname!=$a['gname'])
				{
					$ogname=$a['gname'];
					$temp=$a['keyword'] ? explode(',',trim($a['keyword'],',')) : array();
					foreach($temp as &$v)
						$config['config-'.$v][]=$a['gname'];
				}

				$cache[$a['gname']][$a['name']]=$a['multilang'] ? array($a['language']=>$a['value']) : $a['value'];
				if($a['multilang'])
					$ml[$a['gname']][$a['name']]=true;
			}
			foreach($config as $kw=>&$v)
			{
				$tocache=array('v'=>array(),'m'=>array());
				foreach($v as &$grname)
					foreach($cache[$grname] as $ok=>&$ov)
					{
						if(!isset($tocache['v'][$ok]))
						{
							$tocache['v'][$ok]=$ov;
							if(isset($ml[$grname][$ok]))
								$tocache['m'][]=$ok;
						}
						$getted[$ok]=$ov;
						$lgetted[$ok]=isset($ml[$grname][$ok]);
					}
				self::$Cache->Lib->Put($kw,$tocache);
			}
		}
		if($r)
		{
			foreach($getted as $k=>&$v)
				if($lgetted[$k])
					$v=self::FilterLangValues($v);
			return$getted;
		}
		foreach($getted as $k=>&$v)
			if($lgetted[$k])
			{
				self::$lvars[$k]=$v;
				self::$vars[$k]=self::FilterLangValues($v);
			}
			else
				self::$vars[$k]=$v;
	}

	/**
	 * ������� ������. ��������� �������� ���������� ��������� �������
	 *
	 * @param callback|FALSE $cb ������� ���������� �������� ��������������� ����� ������� ��� ������������. � ������ �������� FALSE, ���������� $data ����� ���������� ������ ������������.
	 * @param int $code HTTP ��� ����������
	 * @param mixed $data, ������ ������� ����� �������� ������ ���������� ������� $cb (������ ����� ������� ���������� �������)
	 */
	public static function HookOutPut($cb='',$code=200,$data='')
	{static $d=false;
		if($d)
			return;
		$d=true;
		#������ ����� ���� ������� ����������. ����� �� � ������ ���� ����� ���� ��������. ����� ����� ������ - ������� Error � index.php
		if(!headers_sent())
		{
			if(self::$gzip)
				self::$gzip=isset($_SERVER['HTTP_ACCEPT_ENCODING']) && strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip')!==false && extension_loaded('zlib');

			if(self::$caching and self::$last_mod)
			{
				$etag=self::$etag ? self::$etag : md5($_SERVER['REQUEST_URI']);
				header('Cache-Control: max-age='.self::$maxage.', must-revalidate');
				header('ETag: '.$etag);
				if(self::$modified and self::$modified>=self::$last_mod)
					return header('Last-Modified: '.gmdate('D, d M Y H:i:s ',self::$last_mod).'GMT',true,304);
				header('Last-Modified: '.gmdate('D, d M Y H:i:s ',self::$last_mod).'GMT',false,$code);
			}
			else
				header('Cache-Control: no-store');
			header('Content-Type: '.self::$content_type.'; charset='.self::$charset);
			header('Content-Language: '.self::$langs[Language::$main]['d']);
			header('Content-Encoding: '.(self::$gzip ? 'gzip' : 'none'),false,$code);
			header('X-Powered-CMS: Eleanor CMS http://eleanor-cms.ru');
		}

		if($cb===false)
			self::FinishOutPut(false,$data);
		else
		{
			ob_start();
			register_shutdown_function(array(__class__,'FinishOutPut'),true,$cb,$data);
		}
	}

	/**
	 * ����� ����������������� ������ �������� �������
	 *
	 * @access protected �� ��-�� ����, ��� register_shutdown_function ����� ������� protected �����, � ���� ���� ����� ������ ��� public
	 * @param bool $docb ���� ���������� callback ������� $cb
	 * @param callback|string $cb � ������ $docb==true, ���������� �������� callback �������, ��������� ����������� ����������� ��������������� ����� �������, � ���� ������ ���� ���������� �� �����
	 * @param mixed $data ������ ��� �������� ������ ���������� � ������� $cb
	 */
	public static function FinishOutPut($docb,$cb,$data=null)
	{
		if($docb)
		{
			$s=ob_get_contents();
			if($s!==false)
				ob_end_clean();
			if(is_callable($cb))
				$s=call_user_func($cb,$s,$data);
		}
		else
			$s=$cb;
		if($s===false)
			return;
		if(self::$gzip)
		{
			$gsize=strlen($s);
			$gcrc=crc32($s);
			$s=gzcompress($s,1);
			$s=substr($s,0,-4);
			$s="\x1f\x8b\x08\x00\x00\x00\x00\x00".$s.pack('V',$gcrc).pack('V',$gsize);
		}
		echo$s;
	}

	/**
	 * �������������� ����: ��������� ������� ���� � ������ �� �������� ����������
	 *
	 * @param string $p ����, ������������ ����� �����. ���� �� ���������� � / , � ����� ����� ��������� Eleanor::$root.
	 * @param string $cp �������, ������������ �������� ��������� ���� $p. ���� ��� ���������� ����, ������������ ����� �������� �������, ���� ������������� ���� ������������ ����� �����.
	 */
	public static function FormatPath($p,$cp='')
	{
		$p=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,trim($p,'/\\'));
		if(strpos($p,'/')===0 or $cp=='')
			return self::$root.$p;
		$cp=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,$cp);
		return(self::$os=='u' && strpos($cp,'/')===0 || strpos($cp,':')==1 ? rtrim($cp,'/\\') : self::$root.trim($cp,'/\\')).($p ? DIRECTORY_SEPARATOR.$p : '');
	}

	/**
	 * ��������� ���� � ������ ������ � ��������� ����, ������ �� ��������
	 *
	 * @param string $n ��� ����
	 * @param string|FALSE $v �������� ����
	 * @param int|FALSE ����� ����� ���� � ������� \d+[tsmMhd]?, ��� t - ������ TIMESTAMP �������� ����, s - �������, m - ������, h - ����, d (�� ���������) - ���, M - ������
	 * @param bool $safe ���� ����������� ���� ������ ����� HTTP �������, �� �� ����� Javascript (�� ������������, �������� �������)
	 */
	public static function SetCookie($n,$v='',$t=false,$safe=false)
	{
		if($t===false)
			$t=$v ? self::$vars['cookie_save_time']+time() : 0;
		elseif($t)
			do
			{
				switch(substr($t,-1))
				{
					case't':
						$t=(int)$t;
					break 2;
					case'M':
						$t=strtotime('+ '.(int)$t.' MONTH');
					break 2;
					case's':
						$t=(int)$t;
					break;
					case'm':
						$t=(int)$t*60;
					break;
					case'h':
						$t=(int)$t*3600;
					break;
					default:#Days...
						$t=(int)$t*86400;
				}
				$t+=time();
			}while(false);
		return setcookie(self::$vars['cookie_prefix'].$n,$v,$t,self::$site_path,self::$vars['cookie_domain'],false,$safe);
	}

	/**
	 * ��������� ���� � ������ �������� ����, ����������� �� ��������
	 *
	 * @param string $n ��� ����
	 */
	public static function GetCookie($n)
	{
		$n=self::$vars['cookie_prefix'].$n;
		return isset($_COOKIE[$n]) ? $_COOKIE[$n] : false;
	}

	/**
	 * ������� ��� �������� ������
	 *
	 * @param string $id ������������� ������, ��������, ������ ����� ������� ������
	 * @param string $n ��� ������
	 */
	public static function StartSession($id='',$n='')
	{
		if(isset($_SESSION))
		{
			if(session_id()==$id and (!$n or session_name()==$n))
				return;
			session_write_close();
		}
		ini_set('session.use_cookies',0);
		ini_set('session.use_trans_sid',0);
		if($n and preg_match('#^[a-z0-9]+$#i',$n)>0)
			session_name($n);
		if($id and preg_match('#^[a-z0-9,\-]+$#i',$id)>0)
			session_id($id);
		session_start();
	}

	/**
	 * ��������� ��������� �������� �� ������� �� ���������� ��� ������ ������
	 *
	 * @param array $a ������ �������� �������� ������ ��������� � �������� ������ �������� ������ � ���� ������ ������ ��� �������������� �������� ��� ���� ������
	 * @param string|FALSE $l �������� �����, ���� �������� FALSE, ����� �������������� ��������� ����
	 * @param mixed $d �������� �� ���������, ������� ����� ���������� ������� � ������, ���� �������� ����� ����� �����������
	 */
	public static function FilterLangValues(array$a,$l=false,$d=null)
	{
		if(!$l)
			$l=Language::$main;
		if(isset($a[$l]))
			return$a[$l];
		if(isset($a['']))
			return$a[''];
		return isset($a[0]) ? $a[0] : $d;
	}

	/**
	 * ������������� �������
	 *
	 * @param string $tpl �������� �������
	 * @param string $path ���� � �������� � ���������
	 */
	public static function InitTemplate($tpl,$path='templates/')
	{
		$f=self::$rootf.$path.$tpl;
		if(!is_dir($f))
			throw new EE('Template '.$tpl.' not found!',EE::ENV);

		self::$Template=new Template_Mixed;
		self::$Template->paths[$tpl]=$f.'/';
		self::$Template->default['theme']=$path.$tpl.'/';
		$init=$f.'.init.php';
		if(is_file($init))
			include$init;
		$config=$f.'.config.php';
		self::$Template->default['CONFIG']=is_file($config) && ($cfg=include$config) ? (array)$cfg : array();
		self::$Template->queue[]='Index';
	}

	/**
	 * �������� PHP ����� � �������� ����������. ���������� ��� ���������� � ����������� �����������.
	 *
	 * @param string $f ���������� ���� � �����
	 * @param array $vars ������ ����������, ������������ �����
	 */
	public static function LoadFileTemplate($f,array$vars=array())
	{
		extract($vars,EXTR_PREFIX_INVALID,'v');
		ob_start();
		$r=include$f;
		if($r===1)
			$r=ob_get_contents();
		ob_end_clean();
		return$r;
	}

	/**
	 * ������� ����� ������� ������
	 *
	 * @param string $n �������� ������� ������
	 */
	public static function LoadListTemplate($n)
	{
		$path=self::$rootf.self::$Template->default['theme'].'Lists/'.$n.'.php';
		if(!is_file($path))
			do
			{
				foreach(self::$Template->paths as &$v)
					if(is_file($path=$v.'Lists/'.$n.'.php'))
						break 2;
				throw new EE('Unable to load list template '.$n,EE::DEV);
			}while(false);
		$p=array_slice(func_get_args(),1);
		extract(count($p)==1 && is_array($p[0]) ? $p[0] : $p,EXTR_PREFIX_INVALID,'v');
		$l=include$path;
		if(!is_array($l))
			throw new EE('Incorrect list template '.$n,EE::DEV);
		$L=new Template_List($l);
		$L->default=self::$Template->default;
		return$L;
	}

	/**
	 * ������������� bb ������ � ������
	 *
	 * ������ ������ ����������: ���������� var ����� {var}
	 * ������ �������: [var]���������� var ����� {var}[/var]
	 * ������ ������� � else: [var]���������� var ����� {var}[-var] ���������� var �����[/var]
	 * ������ ������� ���������� ����� �����, � ����������� �� ����� �������� �����: ������� ������������ {var} [var=plural]���|����|���[var]
	 */
	public static function ExecBBLogic($s,array$r)
	{
		foreach($r as $k=>&$v)
		{
			$fp=0;
			while(false!==$fp=strpos($s,'['.$k,$fp))
			{
				$kl=strlen($k);

				if(trim($s{$fp+$kl+1},'=] ')!='')
				{
					++$fp;
					continue;
				}

				$fpcl=false;#First Post Close
				do
				{
					$fpcl=strpos($s,']',$fpcl ? $fpcl+1 : $fp);
					if($fpcl===false)
					{
						++$fp;
						continue 2;
					}
				}while($s{$fpcl-1}=='\\');

				$ps=substr($s,$fp+$kl+1,$fpcl-$fp-$kl-1);
				$ps=str_replace('\\]',']',trim($ps));

				$fpcl++;#1 - ��� ]
				$lp=strpos($s,'[/'.$k.']',$fp);
				if($lp===false)
				{
					$len=$fpcl-$fp;
					$cont=false;
				}
				else
				{
					$len=$lp-$fp+$kl+3;#3 - ��� [/] ������������ ����
					$cont=substr($s,$fpcl,$lp-$fpcl);
				}

				switch($ps)
				{
					case'=plural':
						$cont=call_user_func(array(Language::$main,'Plural'),$v,explode('|',$cont));
					break;
					default:
						$cont=explode('[-'.$k.']',$cont,2)+array(1=>'');
						$cont=$v ? $cont[0] : $cont[1];
				}
				$s=substr_replace($s,$cont,$fp,$len);
			}
			if(is_scalar($v))
				$s=str_replace('{'.$k.'}',$v,$s);
		}
		return$s;
	}

	/**
	 * ����� ������� � JSON � JavaScript ����������.
	 *
	 * @param array $a ��� ������������� ��� � ���� javascript ����������, ���� JSON �������������
	 * @param bool $t ��������� ���������� ���������� � <script...>...</script>
	 * @param bool|string $n ������������� ������� ������: false - ����� ����������, true - JSON, string - � ����������� ���������� ���������� Object.
	 * @param string $p ������� ����������
	 */
	public static function JsVars($a,$t=true,$n=false,$p='var ')
	{
		if($n)
		{
			$r=$n===true ? '{' : $p.$n.'={';
			$p='"';
			$s='":';
			$e=',';
		}
		else
		{
			$r='';
			$s='=';
			$e=';';
		}
		foreach($a as $k=>&$v)
		{
			if(is_array($v))
				$rv=self::JsVars($v,false,true);
			elseif(is_bool($v))
				$rv=$v ? 'true' : 'false';
			elseif($v===null)
				$rv='null';
			elseif(substr($k,0,1)=='!')
			{
				$rv=$v;
				$k=substr($k,1);
			}
			else
				$rv=is_int($v) || is_float($v) ? $v : '"'.addcslashes($v,"\n\r\t\"\\").'"';
			$r.=$p.$k.$s.$rv.$e;
		}
		if($n)
		{
			$r=rtrim($r,',').'}';
			if($n===true)
				return$r;
			$r.=';';
		}
		return $t ? '<script type="text/javascript">/*<![CDATA[*/'.$r.'//]]></script>' : $r;
	}

	/**
	 * ����� ��������� �������� ������ ��� ���������� � �������� �������� �������� �����
	 *
	 * @param string $s ������-��������
	 * @param int $m ����� ������:
	 * 0 ����� ����������� ����� htmlspecialchars, ����� ������� �� ������ ������ � ����� ����, � ����� �� �� ��������.
	 * 1 ����� ���������� ������� ����� htmlspecialchars_decode, � ����� ����� htmlspecialchars. ����� ������� �� ������ HTML � ����� ����, � ������� ��� ����� ������������. �������� ������� �������� ��� &#93; ������������ ������, � �� �����.
	 * 2 � ������ ���������� ������ < � > �� &lt; � &gt; ��������������.
	 * 3 ������� ���� � ����� ����, � ������� ��� ����� ������������.
	 * @param string $ch ���������
	 */
	public static function ControlValue($s,$m=1,$ch=CHARSET)
	{
		if($m==1)
			$s=htmlspecialchars_decode($s,ELENT);

		if($m==2)
			return str_replace(array('<','>'),array('&lt;','&gt;'),$s);
		elseif($s2=htmlspecialchars($s,ELENT,$ch,$m<3) or !$ch)
			return$s2;
		#�������� �����, ����� �� UTF ������ �� �������� ������� 1251 ����.
		return self::ControlValue($s,$m,null);
	}

	/**
	 * �������������� �������������� ������� � ��������� ����
	 *
	 * @param array $a ������������� ������ � ����������� �������� ���������=>�������� ���������
	 */
	public static function TagParams(array$a)
	{
		$ad='';
		foreach($a as $k=>&$v)
			if($v!==false)
				if(is_int($k))
					$ad.=' '.$v;
				else
				{					$ad.=' '.$k;
					if($v!==true)
						$ad.='="'.str_replace('"','&quot;',$v).'"';
				}
		return$ad;
	}

	/**
	 * ��������� <input type="checkbox" />
	 *
	 * ��-�� ������������ ������ ������� �������� �����, ����� �� �������� ���������� ��������� ��� �������� ��������, ��������� 99% ���������
	 * �� �����, ����� � ��� ��������, �����, ��� ��� ���������� �� ������. �� �������� �������� �� ������ ���������� ����� ������ $a.
	 *
	 * @param string $n ���
	 * @param bool $c ������������
	 * @param array $a ������������� ������ �������������� ����������
	 */
	public static function Check($n,$c=false,array$a=array())
	{
		return'<input'.self::TagParams($a+array('type'=>'checkbox','value'=>1,'name'=>$n,'checked'=>(bool)$c)).' />';
	}

	/**
	 * ��������� <input type="radio" />
	 *
	 * @param string $n ���
	 * @param string $v ��������
	 * @param bool $c ������������
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Radio($n,$v=1,$c=false,array$a=array(),$m=1)
	{
		return'<input'.self::TagParams($a+array('type'=>'radio','value'=>$v ? self::ControlValue($v,(int)$m) : $v,'name'=>$n,'checked'=>(bool)$c)).' />';
	}

	/**
	 * ��������� <textarea>
	 *
	 * @param string $n ���
	 * @param string $v ��������
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Text($n,$v='',array$a=array(),$m=1)
	{
		return'<textarea'.self::TagParams($a+array('rows'=>5,'cols'=>20,'name'=>$n)).'>'.self::ControlValue($v,(int)$m).'</textarea>';
	}

	/**
	 * ��������� <input> type �� ��������� ����� text
	 *
	 * @param string $n ���
	 * @param string $v ��������
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Input($n,$v=false,array$a=array(),$m=1)
	{
		return'<input'.self::TagParams($a+array('value'=>$v ? self::ControlValue($v,(int)$m) : $v,'type'=>'text','name'=>$n)).' />';
	}

	/**
	 * ��������� <input> ��������������� ��� ������
	 *
	 * @param string $v ������� �� ������
	 * @param string $t ��� ������: submit, button, reset
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Button($v='OK',$t='submit',array$a=array(),$m=1)
	{
		return self::Input(false,$v,$a+array('type'=>$t),$m);
	}

	/**
	 * ��������� <option> ��� Select
	 *
	 * @param string $t ��������� ��������
	 * @param string $v ��������
	 * @param bool $s ������������
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Option($t,$v=false,$s=false,array$a=array(),$m=1)
	{
		return'<option'.self::TagParams($a+array('value'=>$v ? self::ControlValue($v,(int)$m) : $v,'selected'=>(bool)$s)).'>'.self::ControlValue($t,(int)$m).'</option>';
	}

	/**
	 * ��������� <optgroup> ��� Select
	 *
	 * @param string $l �������� ������
	 * @param string $o �������� option-��
	 * @param array $a ������������� ������ �������������� ����������
	 * @param int $m ����� ������ ��������, ��������� �������� ����� ControlValue
	 */
	public static function Optgroup($l,$o,array$a=array(),$m=2)
	{
		return'<optgroup'.self::TagParams($a+array('label'=>$l ? self::ControlValue($l,$m) : $l)).'>'.$o.'</optgroup>';
	}

	/**
	 * ��������� <select> � ��������� �������
	 *
	 * @param string $n �������� select-�
	 * @param string $o �������� option-��
	 * @param array $a ������������� ������ �������������� ����������
	 */
	public static function Select($n,$o='',array$a=array())
	{
		if(!$o)
		{
			$o=self::Option('');
			$a['disabled']=true;
		}
		return'<select'.self::TagParams($a+array('name'=>$n,'size'=>1,'class'=>'select')).'>'.$o.'</select>';
	}

	/**
	 * ��������� <select> � ������������� �������
	 *
	 * @param string $n �������� select-�
	 * @param string $o �������� option-��
	 * @param array $a ������������� ������ �������������� ����������
	 */
	public static function Items($n,$o='',array$a=array())
	{
		return self::Select(substr($n,-2)=='[]' ? $n : $n.'[]',$o,$a+array('size'=>5,'multiple'=>true));
	}
#����� ������� ����������.

#������ ����������������� ����������
	/**
	 * ������� ������ �����������
	 *
	 * @param string $l �������� ������. �� ��������� ������ ����������� �� �������� core/login/*.php (�������� * ����� ��������� � $l)
	 */
	public static function LoadLogin($l)
	{
		$c='Login'.$l;
		if(!class_exists($c,false))
		{
			if(!is_file(self::$root.'core/login/'.$l.'.php'))
				throw new EE('Login '.$l.' not found!');
			require self::$root.'core/login/'.$l.'.php';
		}
		return new$c;
	}

	/**
	 * ���������� ������, ��� �������� � �������: ��������� ������� ���������������� �������� (����, ������� ����)
	 *
	 * @param string|LoginClass $Login �����
	 */
	public static function ApplyLogin($Login)
	{
		self::$Login=is_object($Login) ? $Login : self::LoadLogin($Login);
		self::$Permissions=new Permissions(self::$Login);
		if(self::$Login->IsUser())
		{
			if(self::$vars['multilang'] and $l=self::$Login->GetUserValue('language') and Language::$main!=$l and isset(self::$langs[$l]))
			{
				Language::$main=$l;
				self::$Language->Change($l);
			}
			if($t=self::$Login->GetUserValue('timezone') and in_array($t,timezone_identifiers_list()))
			{
				date_default_timezone_set($t);
				self::$Db->SyncTimeZone();
				if(self::$UsersDb!==self::$Db)
					self::$UsersDb->SyncTimeZone();
			}
			self::$Login->ApplyCheck();
		}
	}

	/**
	 * ���������������� ��������� ���������� �����
	 *
	 * @param array $g ID �����
	 * @param string $p �������� ��������� (������� ������� �����)
	 * @param string|FALSE $t �������� ������� � ������������ �����
	 */
	public static function Permissions(array$ids,$p,$t=false)
	{
		if(!$t)
			$t=P.'groups';
		if(isset(self::$perms[$t]))
			$g=self::$perms[$t];
		else
		{
			if(false===$g=self::$Cache->Get($t))
			{
				$g=array();
				self::$Db->Query('SELECT * FROM `'.$t.'`');
				while($a=self::$Db->fetch_assoc())
				{
					$r=array();
					$id=0;
					foreach($a as $k=>&$v)
						if($k=='id')
							$id=$v;
						elseif($k=='parents')
							$r[$k]=$v ? array_reverse(explode(',',rtrim($v,','))) : array();
						elseif('_l'==substr($k,-2))
							$r[$k]=$v ? (array)unserialize($v) : array();
						elseif($v!==null)
							$r[$k]=$v;
					if($id!=0)
						$g[$id]=$r;
				}
				self::$Cache->Put($t,$g,3600);
			}
			self::$perms[$t]=$g;
		}
		$r=array();
		foreach($ids as &$v)
			if(isset($g[$v][$p]))
				$r[$v]=$g[$v][$p];
			else
			{
				$r[$v]=null;
				if(isset($g[$v]))#��� ������������ �����
					foreach($g[$v]['parents'] as &$pv)
						if(isset($g[$pv][$p]))
							$r[$v]=$g[$pv][$p];
			}
		return$r;
	}

	/**
	 * ��������� ���������� ������������ � ������ ��������� �������� � ���������� ������� � ������������ �������� ��������������� �����������
	 *
	 * @param string $p �������� ������, �� �������� ���������� �������� ����������. ����� ������� ��� ������� ���������� �� ����������� ��������������.
	 * @param string|Login_class $l �����
	 * @param string $t �������� ������� � ������������ �����
	 * @param string $go �������� ����������������� ��������� � �������� ���������� ���������� �����
	 * @return array
	 */
	public static function GetPermission($p,$L=false,$t=false,$go='groups_overload')
	{		if(!$L)
			$L=self::$Login;
		if(!$over=$L::GetUserValue($go) or !isset($over['method'][$p],$over['value'][$p]) or $over['method'][$p]=='inherit')
			return self::Permissions(self::GetUserGroups($L),$p,$t);
		$res=($add=$over['method'][$p]=='replace') ? array($over['value'][$p]) : self::Permissions(self::GetUserGroups($L),$p,$t);
		if(!$add)
			$res[]=$over['value'][$p];
		return$res;
	}

	/**
	 * ��������� ������� ���� �����, � ������� ������� ������������
	 *
	 * @param string|Login_class $l �����
	 * @return array
	 */
	public static function GetUserGroups($L=false)
	{
		if(!$L)
			$L=self::$Login;
		if($L ? $L::GetUserValue('id') : false)#�� ������� IsUser() - ���������� �������� � �������!
			return$L::GetUserValue('groups');
		else
			return self::$is_bot ? (array)self::$vars['bot_group'] : (array)self::$vars['guest_group'];
	}

	/**
	 * ������ ���������������� ���������� � ������� ������. ������������ ��� ������ "��� ������".
	 */
	public static function AddSession()
	{
		$uid=self::$Login->GetUserValue('id');
		$ua=getenv('HTTP_USER_AGENT');

		$n='';
		if(!$uid and self::$vars['bots_enable'] and $ua)
			foreach(self::$vars['bots_list'] as $k=>&$v)
				if(stripos($_SERVER['HTTP_USER_AGENT'],$k)!==false)
				{
					$n=self::$is_bot=$v;
					break;
				}
		unset(self::$vars['bots_list']);

		$info=array(
			'r'=>getenv('HTTP_REFERER'),
			'c'=>getenv('HTTP_ACCEPT_CHARSET'),
			'e'=>getenv('HTTP_ACCEPT_ENCODING'),
		);
		if(self::$ips)
			$info['ips']=self::$ips;

		$to=get_class(self::$Login);
		self::$Db->Replace(P.
			'sessions',
			array(
				'type'=>self::$is_bot ? 'bot' : $uid ? 'user' : 'guest',
				'user_id'=>$uid,
				'!enter'=>'NOW()',
				'!expire'=>'\''.date('Y-m-d H:i:s').'\' + INTERVAL '.(isset(self::$vars['time_online'][$to]) ? (int)self::$vars['time_online'][$to] : 900).' SECOND',
				($uid>0 ? 'ip_user' : 'ip_guest')=>self::$ip,
				'info'=>serialize($info),
				'service'=>self::$service,
				'browser'=>$ua,
				'location'=>Url::Decode(preg_replace('#^'.preg_quote(self::$site_path,'#').'#','',$_SERVER['REQUEST_URI'])),
				'name'=>$n,
				'extra'=>self::$sessextra,
			)
		);
	}

	/**
	 * �������� ������������ IP ������ �������� �����.
	 *
	 * �������� ��������� IPv4 � IPv6, �������� ��������� ���������� IP ������� � �������� IP.
	 * ��������: IPMatchMask('192.168.100.100','192.168.100.x'), IPMatchMask('192.168.100.100','192.168.100.50-192.168.100.150'), IPMatchMask('192.168.100.100','192.168.100.0/16')
	 *
	 * @param string $ip IP �����, ������� �����������
	 * @param string $m �����, ��������, �������� � ������, �������
	 */
	public static function IPMatchMask($ip,$m)
	{
		$m=trim($m);
		if($ipv6=strpos($ip,':')!==false)
		{
			if(strpos($m,':')===false)
				return false;
			$n=substr_count($ip,':')-2;
			$r=str_repeat(':0000',8-$n-2);
			$ip=str_replace('::',$r.':',$ip);
		}
		if(strpos($m,'-')===false)
		{
			if($ipv6 and false!==$p=strpos($m,'::'))
			{
				$n=substr_count($m,':')-2-($p==0);
				$r=str_repeat(':0000',8-$n-2);
				$m=trim(str_replace('::',$r.':',$m),':');
			}
			if(strpos($m,'/')!==false)
			{
				$m=explode('/',$m,2);
				$bm=$bip='';
				if($ipv6)
				{
					$m[0]=explode(':',$m[0]);
					$ip=explode(':',$ip);
					foreach($m[0] as &$v)
						$bm.=str_pad(decbin(hexdec($v)),8,'0',STR_PAD_LEFT);
					foreach($ip as &$v)
						$bip.=str_pad(decbin(hexdec($v)),8,'0',STR_PAD_LEFT);
				}
				else
				{
					$m[0]=explode('.',$m[0]);
					$ip=explode('.',$ip);
					foreach($m[0] as &$v)
						$bm.=str_pad(decbin($v),8,'0',STR_PAD_LEFT);
					foreach($ip as &$v)
						$bip.=str_pad(decbin($v),8,'0',STR_PAD_LEFT);
				}
				return strncmp($bm,$bip,(int)$m[1])==0;
			}
			$m=str_replace('\*','[a-f0-9]{1,4}',preg_quote($m,'#'));
			return preg_match('#^'.$m.'$#',$ip)>0;
		}
		else
		{
			$m=explode('-',$m,2);
			if($ipv6)
			{
				if(false!==$p=strpos($m[1],'::'))
				{
					$n=substr_count($m[1],':')-2-($p==0);
					$r=str_repeat(':0000',8-$n-2);
					$m[1]=trim(str_replace('::',$r.':',$m[1]),':');
				}
				if(false!==$p=strpos($m[0],'::'))
				{
					$n=substr_count($m[0],':')-2-($p==0);
					$r=str_repeat(':0000',8-$n-2);
					$m[0]=trim(str_replace('::',$r.':',$m[0]),':');
				}
				$mto=explode(':',$m[1],8);
				$m=explode(':',$m[0],8);
				$ip=explode(':',$ip,8);
				foreach($m as &$v)
					$v=hexdec($v);
				$m=join($m);
				foreach($mto as &$v)
					$v=hexdec($v);
				$mto=join($mto);
				foreach($ip as &$v)
					$v=hexdec($v);
				$ip=implode($ip);
			}
			else
			{
				$mto=explode('.',str_replace('*',255,$m[1]),4);
				$m=explode('.',str_replace('*',0,$m[0]),4);
				$ip=explode('.',$ip,4);
				foreach($m as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$m=ltrim(join($m),0);
				foreach($mto as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$mto=ltrim(join($mto),0);
				foreach($ip as &$v)
					$v=str_pad($v,3,0,STR_PAD_LEFT);
				$ip=ltrim(join($ip),0);
			}
			return(bccomp($ip,$m)>=0 and bccomp($mto,$ip)>=0);
		}
	}
}

# ����������������� ������ ��������� � ���� ����� ������ �� ������� ���������������� �� ������������� ��� ��������� 99% ��������.
# ��������� ���� ������� � ��������� ����� ���� �������� �������� ��������� �������, ���� ����� �������� ��������� - �� ����� ������� ��������

abstract class Template
{
	public
		$s='',#����������� �����������
		$cloned=false;#���� ����������� ��������������. ����� ������� � ���, ��� ������ fluent interface - ��������� ����������� ������.

	/**
	 * ���������� Fluent Interface, ������ ����������
	 */
	public function __toString()
	{
		$s=$this->s;
		$this->s='';
		return$s;
	}

	/**
	 * ��������� ���������� ������-������ �������, ��� ��������� �������� ������
	 *
	 * @param string �������� �������
	 * @params ���������� �������
	 */
	public function __invoke()
	{
		$n=func_num_args();
		if($n>0)
		{
			$a=func_get_args();
			return$this->_($a[0],array_slice($a,1));
		}
	}

	public function __clone()
	{
		$this->cloned=true;
	}

	/**
	 * ���������� fluent interface �������
	 *
	 * @param string $n �������� �������
	 * @param array $p ��������� �������
	 */
	public function __call($n,$p)
	{		if(!$this->cloned)
		{
			$O=clone$this;
			return$O->__call($n,$p);
		}

		$r=$this->_($n,$p);
		if($r===null or is_scalar($r) or is_object($r) and $r instanceof self)
		{
			$this->s.=$r;
			return$this;
		}
		return$r;
	}

	/**
	 * �������� ��������
	 *
	 * @param string $n �������� �������
	 * @param array $p ��������� �������
	 */
	abstract public function _($n,array$p);
}

class Template_Mixed extends Template
{
	public
		$default=array(),#���������� ��-���������, ������� ����� �������������� �� ���� �����. ���� theme ������ �� ������������� �������!
		$queue=array(),#������� ������� �� ��������

		$classes=array(),#������ ��� ����������
		$paths=array(),#�������������� ����
		$files=array();#����� ������

	/**
	 * �������� ��������
	 *
	 * @param string $n �������� �������
	 * @param array $p ��������� �������
	 */
	public function _($n,array$p)
	{
		$c=end($this->classes);
		while($c)
		{
			if(method_exists($c,$n))
				return call_user_func_array(array($c,$n),$p);
			$c=array($c,$n);
			if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
				return$s;
			$c=prev($this->classes);
		}

		foreach($this->paths as $k=>&$v)
		{
			if(!isset($this->files[$k]))
			{
				$this->files[$k]=array();
				if(is_dir($v) and $fs=glob($v.'*.php',GLOB_MARK))
					foreach($fs as &$fv)
						if($fv=substr(strrchr($fv,'/'),1))#��������� ������ ����� ������
							$this->files[$k][]=substr($fv,0,strrpos($fv,'.'));
			}
			if(in_array($n,$this->files[$k]))
				return Eleanor::LoadFileTemplate($v.$n.'.php',(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
		}

		while($cl=array_pop($this->queue))
		{			$c='Tpl'.$cl;
			if(!class_exists($c,false))
				do
				{
					foreach($this->paths as &$v)
						if(is_file($path=$v.'Classes/'.$cl.'.php'))
						{
							include$path;
							if(class_exists($c,false))
								break 2;
						}
					continue 2;
				}while(false);
			$this->classes[]=$c;
			if(method_exists($c,$n))
				return call_user_func_array(array($c,$n),$p);
			$c=array($c,$n);
			if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
				return$s;
		}
		$d=debug_backtrace();
		$a=array();
		foreach($d as &$v)
			if(isset($v['file'],$v['line']) and $v['file']!=__file__)
			{
				$a['file']=$v['file'];
				$a['line']=$v['line'];
				break;
			}
		throw new EE('Template '.$n.' was not found!',EE::DEV,$a);
	}
}

class Template_List extends Template
{
	public
		$cloned=true,
		$default=array();

	protected
		$tpl;

	/**
	 * ����������� ������������� ������
	 *
	 * @param array $a ������ ��������
	 */
	public function __construct(array$a)
	{
		$this->tpl=$a;
	}

	/**
	 * �������� ��������
	 *
	 * @param string $n �������� �������
	 * @param array $p ��������� �������
	 */
	public function _($n,array$p)
	{
		if(!isset($this->tpl[$n]))
			throw new EE('Unknown list template: '.$n,EE::DEV);
		if(is_callable($this->tpl[$n]))
			return call_user_func_array($this->tpl[$n],$p);
		return Eleanor::ExecBBLogic($this->tpl[$n],(count($p)==1 && is_array($p[0]) ? $p[0] : $p)+$this->default);
	}
}
/*
class Template_Class extends Template
{
	public
		$cloned=true,
		$class;

	public function __construct(string$cl)
	{
		$this->class=$cl;
	}

	public function _($n,array$p)
	{
		if(is_callable(array($this->class,$n)))
			return call_user_func_array(array($this->class,$n),$p);
		throw new EE('Template: '.$this->class.'::'.$n,EE::DEV);
	}
}
*/
### Cache

interface CacheMachineInterface #��������� ��� �������� �����
{
	/**
	 * ������ ��������
	 *
	 * @param string $k ����. �������� ��������, ��� ����� ������������� �������� � ���� ���1_���2 ...
	 * @param mixed $value ��������
	 * @param int $t ����� ����� ���� ������ ���� � ��������
	 */
	public function Put($k,$v,$ttl=0);

	/**
	 * ��������� ������ �� ����
	 *
	 * @param string $k ����
	 */
	public function Get($k);

	/**
	 * �������� ������ �� ����
	 *
	 * @param string $k ����
	 */
	public function Delete($k);

	/**
	 * �������� ������� �� ����. ���� ��� ���� ������ - ��������� ���� ���.
	 *
	 * @param string $t ���
	 */
	public function DeleteByTag($tag);
}

class Cache
{
	public
		$table,#������� "�������" ����
		$Lib;#���-������

	/**
	 * ����������� ����������� ������
	 *
	 * @param string|FALSE $u ������������ ��� ���������
	 * @param string|FALSE $table �������� ������� ��� �������� "�������" ����
	 * @param array $cm ������ ��������� ��� �����. ������: ��� ������=>���� � �����
	 */
	public function __construct($u=false,$table=false,array$cm=array())
	{
		if($u===false)
			$u=crc32(__file__);
		$this->table=$table===false && defined('P') ? P.'cache' : $table;

		$a=array();
		if(function_exists('apc_store'))
			$a['CacheMachineApc']=Eleanor::$root.'core/cache_machines/apc.php';
		if(function_exists('memcache_connect'))
			$a['CacheMachineMemCache']=Eleanor::$root.'core/cache_machines/memcache.php';
		if(class_exists('Memcached',false))
			$a['CacheMachineMemCached']=Eleanor::$root.'core/cache_machines/memcached.php';
		if(function_exists('output_cache_put'))
			$a['CacheMachineZend']=Eleanor::$root.'core/cache_machines/zend.php';
		$cm+=$a;

		if(!isset($this->Lib))
			foreach($cm as $k=>&$v)
				if(class_exists($k,false) or is_file($v) and include$v)
				{
					try
					{
						$this->Lib=new $k($u);
					}catch(Exception$E){}
				}

		if(!isset($this->Lib))
		{
			#������ Serialize ����� ������������ HardDisk
			if(!class_exists('CacheMachineSerialize',false))
				include Eleanor::$root.'core/cache_machines/serialize.php';
			$this->Lib=new CacheMachineSerialize;
		}
	}

	/**
	 * ������ ������ � ���
	 *
	 * @param string $n ��� ������ �������� ����
	 * @param mixed $v �������� ������
	 * @param int $ttl ����� �������� � ��������
	 * @param bool $tdb ���� ������ � ������� � ����� "�������" �����������
	 * @param int|FALSE ����� ������������ ����������� ����. �� ��������� � ��� ���� ������ $ttl. ������������ ��� �������������� dog-pile effect
	 */
	public function Put($n,$v=false,$ttl=0,$tdb=false,$insur=false)
	{
		if(!is_array($n))
			$n=array($n=>$v);
		$r=true;
		if(!DEBUG)
		{
			if(!$insur)
				$insur=$ttl*2;
			$del=array();
			foreach($n as $k=>&$v)
				if($v===false)
					$del[]=$k;
				else
				{
					$r&=$this->Lib->Put($k,array($v,$ttl,time()+$ttl),$insur);
					if($tdb)
						$v=serialize($v);
				}
			if($del)
				$this->Delete($del,true);
		}
		if($tdb and $this->table)
			Eleanor::$Db->Replace($this->table,array('key'=>array_keys($n),'value'=>array_values($n)));
		return$r;
	}

	/**
	 * ��������� ������ �� ����
	 *
	 * @param string $n ��� ������ �������� ����
	 * @param bool $fdb ���� ��� ������������� ������� ������ ��� �� ������� "�������" ��������, � ������ ������� ��� ������ ���� �� ��������� ���������
	 */
	public function Get($n,$fdb=false)
	{
		if($a=is_array($n))
		{
			$r=array();
			foreach($n as $k=>&$v)
				if(false!==$r[$v]=$this->Lib->Get($v))
					unset($n[$k]);
		}
		elseif(DEBUG)
			$r=false;
		elseif(false!==$r=$this->Lib->Get($n))
		{
			if($r[1]>0 and $r[2]<time())
			{
				$this->Put($n,$r[0],$r[1],false,$r[1]);
				return false;
			}
			return$r[0];
		}
		if(!$fdb or !$n or !$this->table)
			return$r;

		$db=array();
		$R=Eleanor::$Db->Query('SELECT `key`,`value` FROM `'.$this->table.'` WHERE `key`'.Eleanor::$Db->In($n));
		while($a=$R->fetch_assoc())
			$db[$a['key']]=unserialize($a['value']);
		if($db and !DEBUG)
			$this->Put($db);
		return$a ? $db+$r : reset($db);
	}

	/**
	 * �������� ������ �� ����
	 *
	 * @param string $n ��� ������ �������� ����
	 * @param bool $fdb ���� �������� ���� �� ������� "�������" ��������
	 */
	public function Delete($n,$fdb=false)
	{
		if(is_array($n))
			foreach($n as &$v)
				$this->Lib->Delete($v);
		else
			$this->Lib->Delete($n);
		if($fdb and $this->table)
			Eleanor::$Db->Delete($this->table,'`key`'.Eleanor::$Db->In($n));
	}

	/**
	 * ������� ���� ���������� ��� ��� �������������. � ������� �� ������ Delete, ������������� ����� ������ �� ������ �� ����� ����������� ��������� dog-pile effect
	 *
	 * @param string $n ��� ������ �������� ����
	 * @param bool $fdb ���� �������� ���� �� ������� "�������" ��������
	 */
	public function Obsolete($n,$fdb=false)
	{
		if(false!==$r=$this->Lib->Get($n))
		{
			if($r[1]==0)
				return$this->Delete($n,$fdb);
			$ttl=max(time()-$r[2]+$r[1],1);
			$r[2]=0;
			$this->Lib->Put($n,$r,$ttl);
		}
	}
}

### DB
class Db extends BaseClass
{
	public
		$Driver,#������ MySQLi
		$Result,#������ ���������� MySQLi
		$db,#��� ���� ������
		$queries=0;#������� ��������

	/**
	 * ���������� � ��
	 *
	 * @param array $p ��������� ���������� � ��. ����� �������:
	 * host ������ ��.
	 * user ������������ ��
	 * pass ������ ������������
	 * db �������� ���� ������
	 * @throws EE_SQL
	 */
	public function __construct(array$p)
	{
		if(!isset($p['host'],$p['user'],$p['pass'],$p['db']))
			throw new EE_SQL('connect',$p);
		Eleanor::$nolog=true;#���������� warining
		$M=new MySQLi($p['host'],$p['user'],$p['pass'],$p['db']);
		Eleanor::$nolog=false;
		if($M->connect_errno or !$M->server_version)
			throw new EE_SQL('connect',$p+array('error'=>$M->connect_error,'errno'=>$M->connect_errno));
		$M->autocommit(true);
		$M->set_charset(DB_CHARSET);

		$this->Driver=$M;
		$this->db=$p['db'];
	}

	/**
	 * ������� ��� ����������� ������� � ������� �������� MySQLi � ���������� MySQLi
	 *
	 * @param string $n ��� ����������� ������
	 * @param array $p ��������� ������
	 */
	public function __call($n,$p)
	{
		if(method_exists($this->Driver,$n))
			return call_user_func_array(array($this->Driver,$n),$p);
		elseif(is_object($this->Result) and method_exists($this->Result,$n))
			return call_user_func_array(array($this->Result,$n),$p);
		return parent::__call($n,$p);
	}

	/**
	 * ������������� ������� �� �� �������� PHP (���������� �������� �����). ���������������� ������ ���� ���� TIMESTAMP.
	 */
	public function SyncTimeZone()
	{
		$t=date_offset_get(date_create());
		$s=$t>0 ? '+' : '-';
		$t=abs($t);
		$s.=floor($t/3600).':'.($t%3600);
		$this->Driver->query('SET TIME_ZONE=\''.$s.'\'');
	}

	/**
	 * ����� ����������
	 */
	public function Transaction()
	{
		$this->Driver->autocommit(false);
	}

	/**
	 * ������������� ����������
	 */
	public function Commit()
	{
		$this->Driver->commit();
		$this->Driver->autocommit(true);
	}

	/**
	 * ����� ����������
	 */
	public function RollBack()
	{
		$this->Driver->rollback();
		$this->Driver->autocommit(true);
	}

	/**
	 * ���������� SQL ������� � ����
	 *
	 * @param string $q SQL ������
	 */
	public function Query($q)
	{
		++$this->queries;
		if(DEBUG)
		{
			$d=debug_backtrace();
			$f=$l='';
			foreach($d as &$v)
			{
				if(!isset($v['class']) or $v['class']!='Db')
					break;
				$f=$v['file'];
				$l=$v['line'];
			}
			$debug=array(
				'e'=>$q,
				'f'=>$f,
				'l'=>$l,
			);
			$timer=microtime();
		}
		$this->Result=$this->Driver->query($q);
		if($this->Result===false)
		{
			if($q)
			{
				$e=$this->Driver->error;
				$en=$this->Driver->errno;
			}
			else
			{
				$e='Empty query';
				$en=0;
			}
			throw new EE_SQL('query',array('error'=>$e,'no'=>$en,'query'=>$q));
		}
		if(DEBUG)
		{
			$debug['t']=round(array_sum(explode(' ',microtime()))-array_sum(explode(' ',$timer)),4);
			Eleanor::$debug[]=$debug;
		}
		return$this->Result;
	}

	/**
	 * ������� ��� �������� ������������� INSERT ��������
	 *
	 * @param string $t ��� �������, ���� ���������� �������� ������
	 * @param array $a ������ ������. ��� ������ ������������� ������������. ���� ������������� �� �����, ����� ������ ���� ��������� !.
	 * �������������� 3 ������� �������:
	 * 1. ������� ����� ������: array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field4'=>'NOW()')
	 * 2. ������� ������ ����� ������� 1: array('field1'=>array('value1','value11'),'field2'=>(2,3),'field3'=>array(null,null),'!field4'=>array('NOW()','NOW() + INTERVAL 1 DAY'))
	 * 3. ������� ������ ����� ������� 2: array( array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field4'=>'NOW()'), array('field1'=>'value11','field2'=>3,'field3'=>NULL,'!field4'=>'NOW() + INTERVAL 1 DAY') )
	 * ������ �� ������������ INSERT �������� � MySQL, ��� ������������� 3�� �������, ����� ���������� �������� ������ ���� �����������.
	 * @param string $add ��� INSERT �������
	 * @return int Insert ID
	 */
	public function Insert($t,array$a,$add='IGNORE')
	{
		$this->Query('INSERT '.$add.' INTO `'.$t.'`'.$this->GenerateInsert($a));
		return$this->Driver->insert_id;
	}

	/**
	 * ������� ��� �������� ������������� REPLACE ��������
	 *
	 * @param string $t ��� �������, ���� ���������� �������� ������
	 * @param array $a ������ ������. ��������� ������ Insert
	 * @param string $add ��� REPLACE �������
	 * @return int Affected rows
	 */
	public function Replace($t,array$a,$add='')
	{
		$this->Query('REPLACE '.$add.' INTO `'.$t.'` '.$this->GenerateInsert($a));
		return$this->Driver->affected_rows;
	}

	/**
	 * ��������� INSERT ������� �� ������ � �������
	 *
	 * @param array $a ������ ����� �� ������ Insert ��� Replace
	 */
	public function GenerateInsert(array$a)
	{
		$rk=$rv='';#result key & result value
		$k=key($a);
		if(is_int($k))
		{
			foreach($a as &$v)
			{
				if(!$rk)
				{
					$ks=array();
					foreach($v as $vk=>&$vv)
					{
						$ks[]=$vk=ltrim($vk,'!');
						$rk.='`'.$vk.'`,';
					}
					$rk='('.rtrim($rk,',').')VALUES';
				}
				$rv='(';
				foreach($ks as $ksk=>&$ksv)
					if(isset($v[$ksv]))
						$rv.=$this->Escape($v[$ksv]).',';
					elseif(isset($v['!'.$ksv]))
						$rv.=$v['!'.$ksv].',';
					elseif(isset($v[$ksk]))
						$rv.=$this->Escape($v[$ksk]).',';
					else
						$rv.='NULL,';
				$rk.=rtrim($rv,',').'),';
			}
			return rtrim($rk,',');
		}
		$va=array();#values array
		$isa=true;
		foreach($a as $k=>&$v)
		{
			if($k[0]=='!')
				$k=substr($k,1);
			else
				$v=$this->Escape($v);

			if(is_array($v))
			{
				foreach($v as $vk=>&$vv)
					$va[$vk][]=$vv;
				$v='Array';#� ���� ������ ��������� ������. ���� ����� �������, ��� ���� �� ���.
			}
			else
				$isa=false;
			$rv.=$v.',';
			$rk.='`'.$k.'`,';
		}
		if($va and $isa)
		{
			foreach($va as &$v)
				$v=join(',',$v);
			$rv='('.join('),(',$va).')';
		}
		else
			$rv='('.rtrim($rv,',').')';
		return'('.rtrim($rk,',').') VALUES '.$rv;
	}

	/**
	 * ������� ��� �������� ������������� UPDATE ��������
	 *
	 * @param string $t ��� �������, ��� ���������� �������� ������
	 * @param array $a ������ ��������� ������. ��� ������ ������������� ������������. ���� ������������� �� �����, ����� ������ ���� ��������� !.
	 * ��������: array('field1'=>'value1','field2'=>2,'field3'=>NULL,'!field5'=>'NOW()')
	 * @param string ������� ����������. ������ WHERE, ��� ��������� ����� WHERE
	 * @return int Affected rows
	 */
	public function Update($t,array$a,$w='',$add='IGNORE')
	{
		$q='UPDATE '.$add.' `'.$t.'` SET ';
		foreach($a as $k=>&$v)
			$q.=$k[0]=='!' ? '`'.substr($k,1).'`='.$v.',' : '`'.$k.'`='.$this->Escape(is_array($v) ? serialize($v) : $v).',';
		$q=rtrim($q,',');
		if($w)
			$q.=' WHERE '.$w;
		$this->Query($q);
		return$this->Driver->affected_rows;
	}

	/**
	 * ������� ��� �������� ������������� DELETE ��������
	 *
	 * @param string $t ��� �������, ������ ���������� ������� ������
	 * @param string ������� ��������. ������ WHERE, ��� ��������� ����� WHERE. ���� ���� �������� �� ���������, ���������� �� DELETE, � TRUNCATE ������.
	 * @return int Affected rows
	 */
	public function Delete($t,$w='')
	{
		$this->Query($w ? 'DELETE FROM `'.$t.'` WHERE '.$w : 'TRUNCATE TABLE `'.$t.'`');
		return$this->Driver->affected_rows;
	}

	/**
	 * �������������� ������� � ������������������ ��� ����������� IN(). ������ ������������� ������������
	 *
	 * @param mixed $a ������ ��� ����������� IN
	 * @param bool $not ��������� ����������� NOT IN. ��� ����������� ��������, ����������� IN �� ������ ����������, ������ ������������ ������ =
	 */
	public function In($a,$not=false)
	{
		if(is_array($a) and count($a)==1)
			$a=reset($a);
		if(is_array($a))
			return($not ? ' NOT' : '').' IN ('.join(',',$this->Escape($a)).')';
		return($not ? '!' : '').'='.$this->Escape($a);
	}

	/**
	 * ������������� ������� �������� � �������
	 *
	 * @param string $s ������ ��� �������������
	 * @param bool $qs ���� ��������� ��������� ������� � ������ � � ����� ����������
	 */
	public function Escape($s,$qs=true)
	{
		if($s===null)
			return'NULL';
		if(is_array($s))
		{
			foreach($s as &$v)
				$v=$this->Escape(is_array($v) ? serialize($v) : $v,$qs);
			return$s;
		}
		if(is_int($s) or is_float($s))
			return$s;
		if(is_bool($s))
			return(int)$s;
		$s=$this->Driver->real_escape_string($s);
		return$qs ? '\''.$s.'\'' : $s;
	}
}
#������� ��� ��������� ����������� ��������

interface LoginClass#��������� ��� �������� ����� �����������
{	/**
	 * �������������� �� ������������ �������� ����������, ��������, �� ������ � ������
	 *
	 * @param array $data ������ � �������
	 * @throws EE
	 */
	public static function Login(array$data);

	/**
	 * �������������� ������ �� ID ������������
	 *
	 * @param int $id ID ������������
	 * @throws EE
	 */
	public static function Auth($id);

	/**
	 * ����������� ������������: �������� �������� �� ������������ �������������
	 *
	 * @param bool $hard ����� �������� ���������, ��� ������ ���� ��������� true
	 * @return bool
	 */
	public static function IsUser($hard=false);

	/**
	 * ���������� ������, ��� �������� � �������: ���������� ������� ��� ������������, ��������� �������� �����, �������� ������������ � �.�.
	 *
	 * @throws EE
	 */
	public static function ApplyCheck();

	/**
	 * ����� ������������ �� ������� ������
	 */
	public static function Logout();

	/**
	 * ������������ ������ �� ������� ������ ������������
	 *
	 * @param string $name ��� ������������
	 * @param string $id ID ������������
	 * @return string|FALSE
	 */
	public static function UserLink($name,$id=0);

	/**
	 * ��������� �������� ����������������� ���������
	 *
	 * @param array|string $key ���� ��� ��������� ����������, �������� ������� ����� ��������
	 * @return array|string � ����������� �� ���� ���������� ����������
	 */
	public static function GetUserValue($value);

	/**
	 * ��������� �������� ����������������� ���������. ����� �� ������ ��������� ����� ������������ � ��! ������ �� ����� ������ �������
	 *
	 * @param array|string $key ��� ���������, ���� ������ � ���� $key=>$value
	 * @param mixed $value ��������
	 */
	public static function SetUserValue($key,$value=null);
}

class Language extends BaseClass implements ArrayAccess
{
	public static
		$main=LANGUAGE;#���������� ������������ ��������� ����

	public
		$loadfrom='langs',#������� �� ���������, ������ ����� ����������� �������������������� �����
		$queue=array();#������� ������ ��� �������� ��� ������ => ����

	protected
		$l,#��� �����
		$gr,#������� ����������� �������� �� �������
		$db,#������ ���� ������
		$files=array();#���������� �����

	/**
	 * ����������� ��������� �������
	 *
	 * @param bool|string $f ���� �� ����� � ��������� ����������. � ������ ��������� true, ���������� ����������� �������� �� �������, false - ��������� "������" ������
	 * @param string $s � ������ �������� � $f ����� � ��������� �����������, ��� ���������� ��������� �� ������, � ������� ���������� ��������� ���������
	 */
	public function __construct($f=false,$s='')
	{
		if($f===true)
			$this->gr=true;
		elseif($f)
			$this->Load($f,$s);
		$this->l=self::$main;
	}

	/**
	 * ����������� �������� ����� �������
	 */
	public function __toString()
	{
		return$this->l;
	}

	/**
	 * ���������� ������������� ������� �������� �������. �������� ��������� ������ ��������� � ��������� �����: Russian, English...
	 *
	 * @param string $n �������� ������
	 * @param array $p ��������� ������
	 */
	public function __call($n,$p)
	{
		if(method_exists($this->l,$n))
			return call_user_func_array(array($this->l,$n),$p);
		$c=array($this->l,$n);
		if(is_callable($c) and false!==$s=call_user_func_array($c,$p))
			return$s;
		return parent::__call($n,$p);
	}

	/**
	 * �������� ��������� ����� � ������
	 *
	 * ��������� ��������� ����� ������ ���� �����:
	 * <?php
	 * return array(
	 *     'param1'=>'value1',
	 *     ...
	 *     );
	 *
	 * @param string $f ��� �����, � ������� ������ * ����� ����������� �������� �������� �����
	 * @param string $s �������� ������, � ������� ����� ������� �������� ������ �� �����
	 */
	public function Load($f,$s='')
	{
		if(is_array($f))
		{
			$l=array();
			foreach($f as &$v)
				$l+=$this->Load($v,$s);
			return$l;
		}
		$rf=Eleanor::FormatPath('',$f);
		$f=str_replace('*',$this->l,$rf);
		do
		{
			if(is_file($f))
				break;
			$f=str_replace('*',LANGUAGE,$rf);
			if(is_file($f))
				break;
			return false;
		}while(false);
		$this->files[$s][]=$rf;
		$l=include$f;
		if($s)
			$this->gr=true;
		elseif($s===false)
			return$l;
		return$this->db[$s]=isset($this->db[$s]) ? $l+$this->db[$s] : $l;
	}

	/**
	 * ��������� ����� �������. � ���� ������ ��� �������� ����� ����� �������������
	 *
	 * @param string|FALSe $l �������� ������ �����. � ������ �������� FALSE, ����� ���������� ��������� ����
	 */
	public function Change($l=false)
	{
		if(!$l)
			$l=self::$main;
		$loc=Eleanor::$langs[$l]['l'];
		setlocale(LC_TIME,$loc);
		setlocale(LC_COLLATE,$loc);
		setlocale(LC_CTYPE,$loc);
		if($l==$this->l)
			return;
		foreach($this->files as $s=>&$fs)
			foreach($fs as $f)
			{
				$f=str_replace('*',$l,$f);
				if(is_file($f))
				{
					$li=include$f;
					$this->db[$s]=$li+$this->db[$s];
				}
			}
		$this->l=$l;
	}

	/**
	 * ��������� �������� ����������
	 *
	 * @param string $k ��� ����������
	 * @param mixed $v �������� ��������
	 */
	public function offsetSet($k,$v)
	{
		if($this->gr)
			$this->db[$k]=$v;
		else
			$this->db[''][$k]=$v;
	}

	/**
	 * �������� ������������� �������� ����������
	 *
	 * @param string $k ��� ����������
	 */
	public function offsetExists($k)
	{
		return$this->gr ? isset($this->db[$k]) : isset($this->db[''][$k]);
	}

	/**
	 * �������� �������� ����������
	 *
	 * @param string $k ��� ����������
	 */
	public function offsetUnset($k)
	{
		if($this->gr)
			unset($this->db[$k]);
		else
			unset($this->db[''][$k]);
	}

	/**
	 * ��������� �������� ����������
	 *
	 * @param string $k ��� ����������
	 */
	public function offsetGet($k)
	{
		if($this->gr)
		{
			if(!isset($this->db[$k]) and !$this->Load(isset($this->queue[$k]) ? $this->queue[$k] : $this->loadfrom.DIRECTORY_SEPARATOR.$k.'-*.php',$k))
				return parent::__get(debug_backtrace(),$k);
			return$this->db[$k];
		}

		if(!isset($this->db[''][$k]))
		{
			while($l=array_pop($this->queue))
				if($this->Load($this->loadfrom.DIRECTORY_SEPARATOR.$l) and isset($this->db[''][$k]))
					return$this->db[''][$k];
			return parent::__get($k);
		}

		return$this->db[''][$k];
	}
}