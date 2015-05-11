<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;

defined('CMS\STARTED')||die;

global$Eleanor,$title;

$lang=Eleanor::$Language->Load(DIR.'admin/translation/services-*.php','services');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Services.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$table=P.'services';
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$service=false;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$Url(['do'=>'create']),
];

if(isset($_GET['do']) and $_GET['do']=='create')
	goto CreateEdit;
elseif(isset($_GET['edit']))
{
	$service=(string)$_GET['edit'];

	CreateEdit:

	$errors=[];

	if($service)
	{
		$db_name=Eleanor::$Db->Escape($service);
		$R=Eleanor::$Db->Query("SELECT * FROM `{$table}` WHERE `name`={$db_name} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();
	}

	if($post)
	{
		include_once DIR.'crud.php';

		$protect=!$service || !$orig['protected'] ? 'string' : false;
		$values=$service ? [] : ['protected'=>isset($_POST['protected'])];

		PostValues($values,[
			'name'=>$protect,
			'file'=>'string',
			'login'=>$protect,
		]);

		#Фильтр имени сервиса
		if(isset($values['name']))
		{
			$values['name']=trim($values['name']);

			if($values['name']!==$service and isset(Eleanor::$services[ $values['name'] ]))
				$errors[]='NAME_EXISTS';
		}
		elseif(!$service)
			$errors[]='EMPTY_NAME';
		#/Фильтр имени сервиса

		if(isset($values['login']))
		{
			$values['login']=preg_replace('#[^a-z0-9\-_]+#i','',$values['login']);

			if($values['login'] and !is_file(DIR.'logins/'.$values['login'].'.php'))
				$errors[]='LOGIN_MISSED';
		}

		if($errors)
			goto EditForm;

		if($service)
			Eleanor::$Db->Update($table,$values,'`name`='.$db_name.' LIMIT 1');
		else
			Eleanor::$Db->Insert($table,$values);

		Eleanor::$Cache->Obsolete('system-services');

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->IframeResponse((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	if($service)
	{
		$title[]=$lang['editing'];
		$values=$orig;
	}
	else
	{
		$title[]=$lang['creating'];
		$values=[
			'name'=>'',
			'file'=>'',
			'login'=>'',
			'protected'=>false,
		];
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		PostValues($values,[
			'name'=>'string',
			'file'=>'string',
			'login'=>'string',
		]);

		if(!$service or !$orig['protected'])
			$values['protected']=isset($_POST['protected']);
	}

	$data=[
		'logins'=>[],
		'files'=>[],
	];

	#Список логинов
	$logins=glob(DIR.'logins/*.php');
	if($logins)
		foreach($logins as $login)
			if(is_file($login))
				$data['logins'][]=basename($login,'.php');
	#/Список логинов

	#Список файлов
	$files=glob(DIR.'../*.php');
	if($files)
	{
		$other=[];

		foreach(Eleanor::$services as $name=>$v)
			if($name!=$service)
				$other[]=$v['file'];

		foreach($files as $file)
		{
			$bn=basename($file);

			if(is_file($file) and !in_array($bn,$other))
				$data[ 'files' ][]=$bn;
		}
	}
	#/Список файлов

	$links=[
		'delete'=>$service && !$orig['protected'] ? $Url(['delete'=>$service,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$c=Eleanor::$Template->CreateEdit($service,$values,$data,$errors,$back,$links);
	Response($c);
}
elseif(isset($_GET['delete']))
{
	$service=(string)$_GET['delete'];
	$qservice=Eleanor::$Db->Escape($service);

	$R=Eleanor::$Db->Query("SELECT `name`, `file` FROM `{$table}` WHERE `name`={$qservice} AND `protected`=0 LIMIT 1");
	if(!$service=$R->fetch_assoc() or !Eleanor::$ourquery)
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($table,"`name`={$qservice} LIMIT 1");
		Eleanor::$Cache->Obsolete('system-services');

		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}

	$title[]=$lang['deleting'];

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($service,$back) );
}
else
{
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$where=$query=$items=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['name']) and $_REQUEST['fi']['name']!='')
		{
			$query['fi']['name']=$_REQUEST['fi']['name'];
			$where[]='`name` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['name'],false).'%\'';
		}

		if(isset($_REQUEST['fi']['file']) and $_REQUEST['fi']['file']!='')
		{
			$query['fi']['file']=$_REQUEST['fi']['file'];
			$where[]='`file` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['file'],false).'%\'';
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';

	$defsort='name';
	$deforder='asc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`name`) FROM `{$table}`{$where}");
	list($cnt)=$R->fetch_row();

	if(isset($query['fi']))
	{
		$R=Eleanor::$Db->Query("SELECT COUNT(`name`) FROM `{$table}`");
		list($total)=$R->fetch_row();
	}
	else
		$total=$cnt;

	if($cnt)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['name','file'],$defsort,$deforder);

		$tpl=DynUrl::$base.'section=management&amp;module=theme&amp;info=';
		$R=Eleanor::$Db->Query("SELECT `name`, `file`, `protected`, `theme`, `login` FROM `{$table}`{$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['_atheme']=$a['theme'] ? $tpl.DynUrl::Encode($a['theme']) : null;
			$a['_aedit']=$Url(['edit'=>$a['name']]);
			$a['_adel']=$a['protected'] ? null : $Url(['delete'=>$a['name']]);

			$items[ $a['name'] ]=array_slice($a,1);
		}

		$links=[
			'sort_name'=>SortDynUrl('name',$query,$defsort,$deforder),
			'sort_file'=>SortDynUrl('file',$query,$defsort,$deforder),
			'form_items'=>$Url($query+['page'=>$page>1 ? $page : false]),
			'pp'=>function($n)use($Url,$query){ $query['per-page']=$n; return$Url($query); },
			'first_page'=>$Url($query),
			'pagination'=>function($n)use($Url,$query){ return$Url($query+['page'=>$n]); },
		];
		$query['sort']=$sort;
		$query['order']=$order;
	}
	else
	{
		$pp=0;
		$links=[];
	}

	$links['nofilter']=isset($query['fi']) ? $Url(['fi'=>[]]+$query) : false;
	$c=Eleanor::$Template->ShowList($items,$total>0,$cnt,$pp,$query,$page,$links);
	Response($c);
}