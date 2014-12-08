<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor,$title;
$table=P.'tasks';
$lang=Eleanor::$Language->Load(DIR.'admin/translation/tasks-*.php','tasks');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Tasks.php';

$tasks=[];
$R=Eleanor::$Db->Query("SELECT `task` FROM `{$table}`");
while($a=$R->fetch_assoc())
	$tasks[]=$a['task'];

$stock=glob(DIR.'tasks/*.php');

if($stock)
{
	foreach($stock as $k=>&$v)
	{
		$v=basename($v, '.php');
		if(strpos($v, 'special_')===0)
			unset($stock[ $k ]);
	}
	unset($v);

	$stock=array_diff($stock, $tasks);
}
else
	$stock=[];

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$id=0;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$stock ? $Url(['do'=>'create']) : null,
];

if(isset($_GET['do']) and $_GET['do']=='create' and $stock)
	goto CreateEdit;
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$errors=[];

	if($id)
	{
		$R=Eleanor::$Db->Query("SELECT * FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();
	}

	if($post)
	{
		include_once DIR.'crud.php';

		if(Eleanor::$vars['multilang'])
			$title_=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : [];
		else
			$title_=isset($_POST['title']) ? [''=>(string)Eleanor::$POST['title']] : [];

		$values=[];

		PostValues($values,[
			'task'=>'string',
			'status'=>'int',
			'run_month'=>'string',
			'run_day'=>'string',
			'run_hour'=>'string',
			'run_minute'=>'string',
			'run_second'=>'string',
		]);

		foreach($title_ as $k=>&$v)
		{
			$v=trim($v);

			if($v=='')
				if($id or $k==Language::$main or !isset($title_[ Language::$main ]))
					$errors['EMPTY_TITLE'][]=$k;
				else
					$v=$title_[ Language::$main ];
		}
		unset($v);

		if(!$id and !$title_)
			$errors['EMPTY_TITLE'][]=Language::$main;

		if(isset($errors['EMPTY_TITLE']))
			$errors['EMPTY_TITLE']=$lang['EMPTY_TITLE']( $errors['EMPTY_TITLE']==[''] ? [] : $errors['EMPTY_TITLE'] );

		if(isset($values['run_hour']) and $values['run_hour']==='')
			$errors[]='EMPTY_HOUR';

		if(isset($values['run_minute']) and $values['run_minute']==='')
			$errors[]='EMPTY_MINUTE';

		if(isset($values['run_second']) and $values['run_second']==='')
			$errors[]='EMPTY_SECOND';

		if(isset($values['run_month']) and $values['run_month']==='')
			$errors[]='EMPTY_MONTH';

		if(isset($values['run_day']) and $values['run_day']==='')
			$errors[]='EMPTY_DAY';

		if(!$id)
			$values+=[
				'task'=>reset($stock),
				'run_month'=>'*',
				'run_day'=>'*',
				'run_hour'=>'+1',
				'run_minute'=>'*',
				'run_second'=>'*',
			];

		$values['do']=date_offset_get(date_create());

		$nr=isset($_POST['now']) ? time() : Tasks::CalcNextRun([
				'month'=>isset($values['run_month']) ? $values['run_month'] : $orig['run_month'],
				'day'=>isset($values['run_day']) ? $values['run_day'] : $orig['run_day'],
				'hour'=>isset($values['run_hour']) ? $values['run_hour'] : $orig['run_hour'],
				'minute'=>isset($values['run_minute']) ? $values['run_minute'] : $orig['run_minute'],
				'second'=>isset($values['run_second']) ? $values['run_second'] : $orig['run_second'],
			],$values['do']);

		if($nr===false)
			$errors[]='NO_NEXT_RUN';

		if(!$errors)
		{
			if($title_)
				$values['title_l']=json_encode($title_,JSON);

			$values['!nextrun']='FROM_UNIXTIME('.$nr.')';

			if($id)
				Eleanor::$Db->Update($table,$values,"`id`={$id} LIMIT 1");
			else
				Eleanor::$Db->Insert($table,$values);

			Tasks::UpdateNextRun();

			if(isset($_GET['iframe']))
				return Response( Eleanor::$Template->IframeResponse((string)$Url) );

			return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
		}
	}

	if($id)
	{
		$values=$orig;
		$values['title']=$values['title_l'] ? json_decode($values['title_l'],true) : [''=>''];

		if(!Eleanor::$vars['multilang'])
			$values['title']=FilterLangValues($values['title']);

		$title[]=$lang['editing'];

		if(in_array($values['task'],$tasks))
			array_unshift($stock,$values['task']);
	}
	else
	{
		$title[]=$lang['creating'];
		$values=[
			'task'=>reset($stock),
			'title'=>Eleanor::$vars['multilang'] ? [] : '',
			'status'=>1,
			'run_month'=>'*',
			'run_day'=>'*',
			'run_hour'=>'+1',
			'run_minute'=>'*',
			'run_second'=>'*',
		];
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		$data=[
			'task'=>'string',
			'title'=>Eleanor::$vars['multilang'] ? 'array' : 'string',
			'run_month'=>'string',
			'run_day'=>'string',
			'run_hour'=>'string',
			'run_minute'=>'string',
			'run_second'=>'string',
		];

		PostValues($values,$data);
		IntValue($values,'status',[0,1]);
	}

	$values['now']=isset($_POST['now']);

	$links=[
		'delete'=>$id ? $Url(['delete'=>$id,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	if(Eleanor::$vars['multilang'])
		foreach(Eleanor::$langs as $lng=>$_)
			if(!isset($values['title'][$lng]))
				$values['title'][$lng]='';

	$c=Eleanor::$Template->CreateEdit($id,$values,$stock,$errors,$back,$links);
	Response($c);
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		Eleanor::$Db->Update($table,['!status'=>'NOT `status`'],'`id`='.$id.' LIMIT 1');
		Tasks::UpdateNextRun();
	}

	GoAway(false,301,'item'.$id);
}
elseif(isset($_GET['run']))
{
	$id=(int)$_GET['run'];
	$referrer=getenv('HTTP_REFERER');
	GoAway(Eleanor::$services['cron']['file'].'?'.DynUrl::Query([
		'id'=>$id,'return'=>$referrer ? $referrer.'#item'.$id : $Url.'#item'.$id
	]),301,'item'.$id);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title_l` `title` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
	if(!Eleanor::$ourquery or !$task=$R->fetch_assoc())
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($table,"`id`={$id} LIMIT 1");

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->Iframe((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];
	$task['title']=$task['title'] ? FilterLangValues(json_decode($task['title'],true)) : '';

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($task,$back) );
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

		if(isset($_REQUEST['fi']['task']) and $_REQUEST['fi']['task']!='')
		{
			$query['fi']['task']=$_REQUEST['fi']['task'];
			$where[]='`task` LIKE \'%'.Eleanor::$Db->Escape($query['fi']['task'],false).'%\'';
		}

		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$query['fi']['title']=(string)$_REQUEST['fi']['title'];
			$q=Eleanor::$Db->Escape(preg_quote(mb_strtolower($query['fi']['title'])),false);
			$mainlang=Language::$main;
			$where[]="LOWER(`title_l`) REGEXP '\"({$mainlang})?\": \"[^\"]*{$q}'";
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';

	$defsort='id';
	$deforder='desc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}`{$where}");
	list($cnt)=$R->fetch_row();

	if(isset($query['fi']))
	{
		$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}`");
		list($total)=$R->fetch_row();
	}
	else
		$total=$cnt;

	if($cnt>0)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','task','lastrun','nextrun','status'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `task`, `title_l` `title`, `free`, `nextrun`, `lastrun`, `run_month`, `run_day`, `run_hour`, `run_minute`, `run_second`, `status` FROM `{$table}`{$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
			$a['_atoggle']=$Url(['toggle'=>$a['id']]);
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$Url(['delete'=>$a['id']]);
			$a['_arun']=$a['status'] ? $Url(['run'=>$a['id']]) : null;

			$items[$a['id']]=array_slice($a,1);
		}

		$links=[
			'sort_nextrun'=>SortDynUrl('nextrun',$query,$defsort,$deforder),
			'sort_lastrun'=>SortDynUrl('lastrun',$query,$defsort,$deforder),
			'sort_status'=>SortDynUrl('status',$query,$defsort,$deforder),
			'sort_task'=>SortDynUrl('task',$query,$defsort,$deforder),
			'sort_id'=>SortDynUrl('id',$query,$defsort,$deforder),
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