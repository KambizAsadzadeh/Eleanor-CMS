<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	���������� ������������ ������� ��� ����������.
*/
class Uploader extends BaseClass
{	const
		FILENAME='Filedata';
	public
		$prevsuff='_preview',#������� ���������
		$pp=20,#���������� ������ �� ��������
		$preview=array('','jpeg','jpg','png','bmp','gif'),#���� ������ ��� ������� ���������� ��������� ���������

		$allow_delete=true,#��������� �������� ������ � �����?
		$allow_walk=true,#��������� "������" �� ������

		$buttons_top=array(
			'create_file'=>false,
			'show_previews'=>true,
			'create_previews'=>true,
			'watermark'=>true,
			'create_folder'=>true,
			'update'=>true,
		),

		$buttons_item=array(
			'edit'=>false,
			'insert_attach'=>true,
			'insert_link'=>true,
			'file_rename'=>true,
			'file_delete'=>true,
			#��� ���������
			'folder_rename'=>true,
			'folder_open'=>false,
			'folder_delete'=>true,
		),
		$editable=array('php','css','txt','js','html','htm'),

		$max_size,#������������ ������ ���� ������� ������. ��������� � false, ���� �� ������, ����� ������������ ��� ��������� �����.
		$max_files=0,#������������ ����� ������, ������� ������������ ����� ���������

		$watermark,#true - ������ �������, false - ������ �� �������, null - �������� ������������
		$previews,#true - ������ ������, false - ������ �� ������, null - �������� ������������
		$types=array();#���� ������, ����������� ��� �������

	protected
		$pathlimit='',#������ �����������, �� ������� ������ ��������
		$vars,
		$path='';#������������. �� ������� ����� �������� �������� ������.

	/*
		$path - ����������� � / � �����!
	*/
	public function __construct($path=false,$tpl='Uploader')
	{		$this->vars=Eleanor::LoadOptions('files',true);
		$this->max_size=Eleanor::$Permissions->MaxUpload();
		if($this->vars['thumbs'])
			$this->preview=explode(',',Strings::CleanForExplode($this->vars['thumb_types']));
		if(!$this->vars['watermark'] or $this->max_size===false)
			$this->watermark=false;
		$this->pathlimit=($path ? preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,rtrim($path,'/\\')) : Eleanor::$root.Eleanor::$uploads).DIRECTORY_SEPARATOR;
		if($tpl)
			Eleanor::$Template->queue[]=$tpl;
		$this->uid=Eleanor::$Login->GetUserValue('id');	}

	public function Show($path=false,$uniq='',$title=false)
	{		$max_upload=Files::SizeToBytes(ini_get('upload_max_filesize'));
		if(is_int($this->max_size))
		{
			if($this->max_size<$max_upload)
				$max_upload=$this->max_size;
			#���� ����� ����������� ������ ���� ������ - �� ������� ����������� "������" ��� ����� �����, ������ ��� � ���� ������ ���������� ����� ����������� ������ - ����������.
			$this->allow_walk=false;
		}
		if($this->max_files>0)
		{			$this->allow_walk=false;
			if($path=='')
				$path=false;
		}

		if(!isset($_SESSION))
			Eleanor::StartSession();
		if($path===false)
		{			$newf=Eleanor::GetCookie(__class__.'-'.$uniq);
			if(!$newf)
			{				$newf=uniqid();
				Eleanor::SetCookie(__class__.'-'.$uniq,$newf);			}
			$this->path=$this->pathlimit.'temp'.DIRECTORY_SEPARATOR.$newf.DIRECTORY_SEPARATOR;
			$_SESSION[__class__][$uniq]=array(
				'prevsuff'=>$this->prevsuff,
				'pp'=>$this->pp,
				'preview'=>$this->preview,
				'allow_walk'=>$this->allow_walk,
				'allow_delete'=>$this->allow_delete,
				'buttons_top'=>$this->buttons_top,
				'buttons_item'=>$this->buttons_item,
				'max_size'=>$this->max_size,
				'max_files'=>$this->max_files,
				'watermark'=>$this->watermark,
				'previews'=>$this->previews,
				'pathlimit'=>$this->pathlimit,
				'types'=>$this->types,
				'path'=>$this->path,
				'tmp'=>true,
				'uid'=>$this->uid,
			);
		}
		else
		{			$path=preg_replace('#/|\\\\#',DIRECTORY_SEPARATOR,trim($path,'/\\'));
			$this->path=$this->pathlimit.($path ? Eleanor::WinFiles($path).DIRECTORY_SEPARATOR : '');
			$_SESSION[__class__][$uniq]=array(
				'prevsuff'=>$this->prevsuff,
				'pp'=>$this->pp,
				'preview'=>$this->preview,
				'allow_walk'=>$this->allow_walk,
				'allow_delete'=>$this->allow_delete,
				'buttons_top'=>$this->buttons_top,
				'buttons_item'=>$this->buttons_item,
				'max_size'=>$this->max_size,
				'max_files'=>$this->max_files,
				'watermark'=>$this->watermark,
				'previews'=>$this->previews,
				'pathlimit'=>$this->pathlimit,
				'types'=>$this->types,
				'path'=>$this->path,
				'tmp'=>false,
				'uid'=>$this->uid,
			);		}
		if(!is_dir($this->path))
			Files::MkDir($this->path);

		if(isset($this->watermark))
			$this->buttons_top['watermark']=false;
		if(isset($this->previews))
			$this->buttons_top['create_previews']=false;
		return Eleanor::$Template->UplUploader(
			$this->buttons_top,
			$title===false ? Eleanor::$Language['uploader']['file_manag'] : $title,
			$this->max_size===false ? false : $max_upload,
			$this->types,
			$uniq
		);
	}

	public function WorkingPath($uniq='',$sess=false)
	{		if($sess===false)
		{
			$f=Eleanor::GetCookie(__class__.'-'.$uniq);
			return$f ? Eleanor::$root.Eleanor::$uploads.'/temp/'.$f : false;
		}
		if(!isset($_SESSION))
			Eleanor::StartSession($sess);
		return isset($_SESSION[__class__][$uniq]) ? $_SESSION[__class__][$uniq]['path'] : false;
	}

	public function MoveFiles($path=false,$uniq='',$sess=false)
	{		if($sess===false)
		{
			$oldpath=Eleanor::GetCookie(__class__.'-'.$uniq);
			$oldpath=preg_replace('#[^a-z0-9]+#i','',$oldpath);
			if(!$oldpath)
				throw new EE('Upload error',EE::USER);
			$oldpath=Eleanor::$root.Eleanor::$uploads.'/temp/'.$oldpath;		}
		else
		{			if(!isset($_SESSION))				Eleanor::StartSession($sess);
			if(!isset($_SESSION[__class__][$uniq]))
				throw new EE('Upload error',EE::USER);
			if(!$_SESSION[__class__][$uniq]['tmp'])				return array('from'=>'','to'=>'');
			$oldpath=$_SESSION[__class__][$uniq]['path'];
		}
		if(!file_exists($oldpath) or !glob($oldpath.'/*'))
			return array('from'=>'','to'=>'');
		$newpath=Eleanor::FormatPath($path,Eleanor::$uploads);
		if(is_dir($newpath))
			Files::Delete($newpath);
		$bd=dirname($newpath);
		if(!is_dir($bd))
			Files::MkDir($bd);
		if(rename($oldpath,$newpath))
		{			$rl=strlen(Eleanor::$root);
			if(Eleanor::$os=='w')
			{
				$oldpath=str_replace(DIRECTORY_SEPARATOR,'/',$oldpath);
				$newpath=str_replace(DIRECTORY_SEPARATOR,'/',$newpath);
			}
			return array('from'=>substr($oldpath,$rl),'to'=>substr($newpath,$rl));		}
		throw new EE('Upload error',EE::ENV);
	}

	protected function GetPath($start,$to='')
	{		$start=Eleanor::WinFiles(trim($start,'/\\'));
		$p=realpath($this->path.($start ? $start.DIRECTORY_SEPARATOR : '').trim($to,'/\\'));
		if(is_dir($p))
			$p.=DIRECTORY_SEPARATOR;
		if($p and ($this->allow_walk or strncmp($p,$this->path,strlen($this->path))==0) and strncmp($p,$this->pathlimit,strlen($this->pathlimit))==0)
			return$p;		if(!is_dir($this->path))
			Files::MkDir($this->path);
		return$this->path;
	}

	protected function FilesSize($path)
	{		if(!is_dir($path))
			return array(0,0);
		$size=$cnt=0;
		$files=glob(rtrim($path,'/\\').'/*',GLOB_MARK);
		$oldk=-1;
		if($files)
			foreach($files as $k=>&$v)
			{
				if(substr($v,-1)==DIRECTORY_SEPARATOR)
				{
					list($t)=$this->FilesSize($v);
					$size+=$t[0];
					$cnt+=$t[1];
				}
				elseif($oldk>=0 and $v==substr_replace($files[$oldk],$this->prevsuff,strrpos($files[$oldk],'.'),0))
					continue;
				else
				{
					$oldk=$k;
					$size+=filesize($v);
					++$cnt;
				}
			}
		return array($size,$cnt);
	}

	protected function LoadOptions($u)
	{
		if(!isset($_SESSION[__class__][$u]))
			return false;
		foreach($_SESSION[__class__][$u] as $k=>&$v)
			if(property_exists($this,$k))
				$this->$k=$v;
		return true;
	}
}