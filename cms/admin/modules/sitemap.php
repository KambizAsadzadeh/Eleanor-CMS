<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Files;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
$lang=Eleanor::$Language->Load(DIR.'admin/translation/sitemap-*.php','sitemap');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Sitemap.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' && Eleanor::$ourquery;
$table=P.'sitemaps';
$tasks=P.'tasks';
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$Url(['do'=>'create']),
	'robots.txt'=>$Url(['do'=>'robots.txt']),
];

if(isset($_GET['do'])) switch($_GET['do'])
{
	case'create':
		goto CreateEdit;
	break;
	case'robots.txt':
		$title[]=$lang['robots.txt'];
		$f=DIR.'../robots.txt';
		$saved=false;

		if($post and isset($_POST['text']))
		{
			file_put_contents($f,(string)$_POST['text']);
			$saved=true;

			if(isset($_GET['iframe']))
				return Response( Eleanor::$Template->IframeResponse(false) );
		}

		$text=is_file($f) ? file_get_contents($f) : '';
		$s=Eleanor::$Template->EditRobots($text,$saved);
		Response($s);
	break;
	default:
		ShowList();
}
elseif(isset($_GET['edit']))
{
	CreateEdit:

	/* 	$lang=Eleanor::$Language['sitemap'];
	$values=array(
		'modules'=>isset($_POST['modules']) ? (array)$_POST['modules'] : false,
		'file'=>isset($_POST['file']) ? (string)$_POST['file'] : '',
		'compress'=>isset($_POST['compress']),
		'fulllink'=>isset($_POST['fulllink']),
		'sendservice'=>isset($_POST['sendservice']) ? ','.join(',,',(array)$_POST['sendservice']).',' : '',
		'status'=>isset($_POST['status']),
		'per_time'=>isset($_POST['per_time']) ? (int)$_POST['per_time'] : 1000,
	);
	if($values['per_time']<10)
		$values['per_time']=10;

	$errors=array();
	if($values['file']=='')
		$errors[]='NOFILE';
	if(!is_writeable(Eleanor::$root))
		$errors['UNABLE_CREATE_FILE']=sprintf($lang['unacr'],$values['file'].($values['compress'] ? '.xml.gz' : '.xml'));

	if(Eleanor::$vars['multilang'])
		$values['title_l']=isset($_POST['title_l']) ? (array)Eleanor::$POST['title_l'] : array();
	else
		$values['title_l']=isset($_POST['title_l']) ? array(''=>(string)Eleanor::$POST['title_l']) : array();
	$values['title_l']=serialize($values['title_l']);

	$do=date_offset_get(date_create());
	$tvalues=array(
		'task'=>'special_sitemap.php',
		'title_l'=>$values['title_l'],
		'name'=>'sitemap',
		'ondone'=>'deactivate',
		'status'=>$values['status'],
		'run_month'=>isset($_POST['run_month']) ? (string)$_POST['run_month'] : '',
		'run_day'=>isset($_POST['run_day']) ? (string)$_POST['run_day'] : '',
		'run_hour'=>isset($_POST['run_hour']) ? (string)$_POST['run_hour'] : '',
		'run_minute'=>isset($_POST['run_minute']) ? (string)$_POST['run_minute'] : '',
		'run_second'=>isset($_POST['run_second']) ? (string)$_POST['run_second'] : '',
		'do'=>$do,
	);
	$nr=isset($_POST['_runnow']) ? time() : Tasks::CalcNextRun(array(
		'month'=>$tvalues['run_month'],
		'day'=>$tvalues['run_day'],
		'hour'=>$tvalues['run_hour'],
		'minute'=>$tvalues['run_minute'],
		'second'=>$tvalues['run_second'],
	),$do);

	if($nr===false)
		$errors[]='NO_NEXT_RUN';
	$tvalues['!nextrun']='FROM_UNIXTIME('.(int)$nr.')';
	if(!$values['modules'])
		$errors[]='NOMODULES';

	if(isset($_POST['_recreate']))
	{
		$tvalues['data']='';
		$values['total']=$values['already']=0;
		$f=Eleanor::$root.$values['file'].'.xml';
		Files::Delete($f);
		if($values['compress'])
			Files::Delete($f.'.gz');
	}

	$R=Eleanor::$Db->Query('SELECT `id`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\' AND `id`'.Eleanor::$Db->In($values['modules']));
	$options=$values['modules']=array();
	while($a=$R->fetch_assoc())
	{
		$api=Eleanor::FormatPath($a['api'],$a['path']);
		$class='Api'.basename(dirname($api));
		do
		{
			if(class_exists($class,false))
				break;
			if(is_file($api))
			{
				include$api;
				if(class_exists($class,false))
					break;
			}
			continue 2;
		}while(false);
		if(!method_exists($class,'SitemapGenerate'))
			continue;
		$values['modules'][]=$a['id'];
		if(method_exists($class,'SitemapConfigure'))
		{
			$Api=new$class;
			$conf=$Api->SitemapConfigure($p=false);
			$Eleanor->Controls->arrname=array('module'.$a['id']);
			try
			{
				$options['m'][$a['id']]=$Eleanor->Controls->SaveControls($conf);
			}
			catch(EE$E)
			{
				return AddEdit($id,array('ERROR'=>$E->getMessage()));
			}
		}
	}
	if($values['modules'])
		$values['modules']=','.join(',,',$values['modules']).',';
	else
		$errors[]='NOMODULES';

	if($errors)
		return AddEdit($id,array_unique($errors));

	if($id)
	{
		$R=Eleanor::$Db->Query('SELECT `task_id` FROM `'.P.'sitemaps` WHERE id='.$id.' LIMIT 1');
		if(!$a=$R->fetch_assoc())
			return GoAway();
		$options['id']=$id;
		$tvalues['options']=serialize($options);
		$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'tasks` WHERE `id`='.$a['task_id'].' LIMIT 1');
		if($R->num_rows>0)
			Eleanor::$Db->Update(P.'tasks',$tvalues,'`id`='.$a['task_id'].' LIMIT 1');
		else
			$values['task_id']=Eleanor::$Db->Insert(P.'tasks',$tvalues+array('free'=>1,'locked'=>0,));
		Eleanor::$Db->Update(P.'sitemaps',$values,'`id`='.$id.' LIMIT 1');
	}
	else
	{
		$values['task_id']=Eleanor::$Db->Insert(P.'tasks',$tvalues+array('free'=>1,'locked'=>0,));
		$options['id']=Eleanor::$Db->Insert(P.'sitemaps',$values);
		$options=serialize($options);
		Eleanor::$Db->Update(P.'tasks',array('options'=>$options),'`id`='.$values['task_id'].' LIMIT 1');
	}
	Tasks::UpdateNextRun();
	GoAway(empty($_POST['back']) ? true : $_POST['back']); */







	/*	$lang=Eleanor::$Language['sitemap'];
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT `title_l`,`modules`,`task_id`,`file`,`compress`,`fulllink`,`sendservice`,`per_time`,`status` FROM `'.P.'sitemaps` WHERE id='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway(true);
			$values['title_l']=$values['title_l'] ? (array)unserialize($values['title_l']) : array();
			$values['modules']=$values['modules'] ? explode(',,',trim($values['modules'],',')) : array();
			$values['sendservice']=$values['sendservice'] ? explode(',,',trim($values['sendservice'],',')) : array();
			$values['_recreate']=$values['_runnow']=false;
			$R=Eleanor::$Db->Query('SELECT `options`,`run_month`,`run_day`,`run_hour`,`run_minute`,`run_second` FROM `'.P.'tasks` WHERE id='.(int)$values['task_id'].' LIMIT 1');
			if($R->num_rows>0)
			{
				$values+=$R->fetch_assoc();
				$values['options']=$values['options'] ? (array)unserialize($values['options']) : array();
			}
			else
				$values+=array(
					'options'=>array(),
					'run_month'=>'*',
					'run_day'=>'*',
					'run_hour'=>0,
					'run_minute'=>0,
					'run_second'=>0,
				);
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$values=array(
			'title_l'=>array(''=>''),
			'modules'=>array(),
			'file'=>'',
			'compress'=>true,
			'fulllink'=>true,
			'status'=>true,
			'per_time'=>1000,
			'sendservice'=>true,

			'options'=>array(),
			'run_month'=>'*',
			'run_day'=>'*',
			'run_hour'=>0,
			'run_minute'=>0,
			'run_second'=>0,
			'_runnow'=>true,
		);
		$title[]=$lang['adding'];
	}
	$bypost=false;
	if($errors)
	{
		$bypost=true;
		if($errors===true)
			$errors=array();
		$values['modules']=isset($_POST['modules']) ? (array)$_POST['modules'] : array();
		$values['file']=isset($_POST['file']) ? (string)$_POST['file'] : '';
		$values['compress']=isset($_POST['compress']);
		$values['fulllink']=isset($_POST['fulllink']);
		$values['status']=isset($_POST['status']);
		$values['per_time']=isset($_POST['per_time']) ? (int)$_POST['per_time'] : '';
		$values['sendservice']=isset($_POST['sendservice']) ? (array)$_POST['sendservice'] : array();

		$values['run_month']=isset($_POST['run_month']) ? (string)$_POST['run_month'] : '';
		$values['run_day']=isset($_POST['run_day']) ? (string)$_POST['run_day'] : '';
		$values['run_hour']=isset($_POST['run_hour']) ? (string)$_POST['run_hour'] : '';
		$values['run_minute']=isset($_POST['run_minute']) ? (string)$_POST['run_minute'] : '';
		$values['run_second']=isset($_POST['run_second']) ? (string)$_POST['run_second'] : '';
		$values['_recreate']=isset($_POST['_recreate']);
		$values['_runnow']=isset($_POST['_runnow']);

		if(Eleanor::$vars['multilang'])
			foreach(Eleanor::$langs as $k=>&$v)
				$values['title_l'][$k]=isset($_POST['title_l'][$k]) ? $_POST['title_l'][$k] : '';
		else
			$values['title_l']=array(''=>isset($_POST['title_l']) ? $_POST['title_l'] : '');
	}
	$modules=$settings=array();
	$C=new Controls;
	$R=Eleanor::$Db->Query('SELECT `id`,`title_l`,`descr_l`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\'');
	while($a=$R->fetch_assoc())
	{
		$api=Eleanor::FormatPath($a['api'],$a['path']);
		$class='Api'.basename(dirname($api));
		do
		{
			if(class_exists($class,false))
				break;
			if(is_file($api))
			{
				include$api;
				if(class_exists($class,false))
					break;
			}
			continue 2;
		}while(false);
		if(!method_exists($class,'SitemapGenerate'))
			continue;
		$a['title_l']=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
		$modules[$a['id']]=$a['title_l'];
		if(method_exists($class,'SitemapConfigure') and in_array($a['id'],$values['modules']))
		{
			$a['descr_l']=$a['descr_l'] ? Eleanor::FilterLangValues((array)unserialize($a['descr_l'])) : '';

			$Api=new$class;
			$conf=$Api->SitemapConfigure($bypost);
			$C->arrname=array('module'.$a['id']);

			$sett=$ovalues=array();
			if(isset($values['options']['m'][$a['id']]))
				foreach($values['options']['m'][$a['id']] as $k=>&$v)
					$ovalues[$k]=array('value'=>$v);

			$error=false;
			try
			{
				$sett=$C->DisplayControls($conf,$ovalues);
			}
			catch(EE$E)
			{
				$error=$E->getMessage();
			}

			$settings[]=array(
				'id'=>$a['id'],
				't'=>$a['title_l'],
				'd'=>$a['descr_l'],
				'c'=>$conf,
				's'=>$sett,
				'e'=>$error,
			);
		}
	}
	unset($Api);
	asort($modules,SORT_STRING);
	$values['_recreate']=isset($_POST['_recreate']);
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
	);
	$c=Eleanor::$Template->AddEdit($id,$values,$modules,$settings,$errors,$bypost,$links,$back);
	Start();
	echo$c;*/
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		$R=Eleanor::$Db->Query("SELECT `task_id`, `status` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if($sitemap=$R->fetch_assoc())
		{
			$status=$sitemap['status']==0 ? 1 : 0;

			Eleanor::$Db->Update($table,['status'=>$status],"`id`={$id} LIMIT 1");

			if($status)
				Eleanor::$Db->Update($tasks,['status'=>$status],"`id`={$sitemap['task_id']} AND `name`='sitemap' LIMIT 1");
			else
				Eleanor::$Db->Update($tasks,['status'=>$status,'free'=>1,'locked'=>0],"`id`={$sitemap['task_id']} AND `name`='sitemap' LIMIT 1");
		}
	}

	GoAway(false,301,'item'.$id);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title_l` `title`, `task_id`, `file`, `compress` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
	if(!Eleanor::$ourquery or !$sitemap=$R->fetch_assoc())
		return GoAway(true);

	$sitemap['file'].=$sitemap['compress'] ? '.gz' : '.xml';

	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($table,"`id`={$id} LIMIT 1");
		Eleanor::$Db->Delete($tasks,"`id`={$sitemap['task_id']} AND `name`='sitemap'");

		Files::Delete(DIR.'../'.$sitemap['file']);
		Tasks::UpdateNextRun();

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];
	$sitemap['title']=$sitemap['title'] ? FilterLangValues(json_decode($sitemap['title'],true)) : '';

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($sitemap,$back) );
}
else
{
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$items=$where=$query=$modules=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['file']) and $_REQUEST['fi']['file']!=='')
		{
			$query['fi']['file']=(string)$_REQUEST['fi']['file'];
			$q=Eleanor::$Db->Escape($query['fi']['file'],false);
			$where[]="`s`.`file` LIKE '%{$q}%'";
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';

	if($post and isset($_POST['event'],$_POST['items']))
	{
		$in=Eleanor::$Db->In($_POST['items']);

		switch($_POST['event'])
		{
			case'delete':
				$task_ids=[];
				$R=Eleanor::$Db->Query("SELECT `task_id`, `file`, `compress` FROM `{$table}` WHERE `id`{$in}");
				while($a=$R->fetch_assoc())
				{
					$task_ids[]=$a['task_id'];
					$a['file'].=$a['compress'] ? '.gz' : '.xml';

					Files::Delete(DIR.'../'.$a['file']);
				}

				Eleanor::$Db->Delete($table,'`id`'.$in);

				$in=Eleanor::$Db->In($task_ids);
				Eleanor::$Db->Delete($tasks,"`id`{$in} AND `name`='sitemap'");
		}
	}

	$defsort='id';
	$deforder='desc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}` `s`{$where}");
	list($cnt)=$R->fetch_row();

	if(isset($query['fi']))
	{
		$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}` `s`");
		list($total)=$R->fetch_row();
	}
	else
		$total=$cnt;

	if($cnt>0)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','file','status','title'],$defsort,$deforder);

		#Флаг сортировки по названию
		if($sort=='title')
		{
			$so_title=true;
			$sort='id';
		}
		else
			$so_title=false;

		$to_sort=[];
		$time=time();

		$R=Eleanor::$Db->Query("SELECT `s`.`id`, `s`.`title_l` `title`, `s`.`modules`, `s`.`task_id`, `s`.`total`, `s`.`already`, `s`.`file`, `s`.`compress`, `s`.`status`, `t`.`lastrun`, `t`.`nextrun`, `t`.`free` FROM `{$table}` `s` LEFT JOIN `{$tasks}` `t` ON `t`.`id`=`s`.`task_id`{$where} ORDER BY `s`.``{$sort}` {$order}{$limit}");
		while($sitemap=$R->fetch_assoc())
		{
			$sitemap['modules']=$sitemap['modules'] ? explode(',,',trim($sitemap['modules'],',')) : [];
			$sitemap['title']=$sitemap['title'] ? FilterLangValues(json_decode($sitemap['title'],true)) : '';
			$sitemap['file'].=$sitemap['compress'] ? '.gz' : '.xml';

			if($sitemap['modules'])
				$modules=array_merge($modules,$sitemap['modules']);

			if($so_title)
				$to_sort[]=$sitemap['title'];

			if($sitemap['free'] and $time>=strtotime($sitemap['nextrun']))
				$sitemap['free']=false;
			elseif($sitemap['free']===null)
				$sitemap['free']=true;

			$sitemap['_aedit']=$Url(['edit'=>$sitemap['id']]);
			$sitemap['_adel']=$Url(['delete'=>$sitemap['id']]);
			$sitemap['_atoggle']=$Url(['toggle'=>$sitemap['id']]);

			$items[ $sitemap['id'] ]=array_slice($sitemap,1);
		}

		if($to_sort)
		{
			asort($to_sort,SORT_STRING);

			$sorted=[];

			foreach($to_sort as $k=>$v)
				$sorted[$k]=$items[$k];

			$items=$sorted;
		}

		if($modules)
		{
			$modules=array_unique($modules);
			$R=Eleanor::$Db->Query('SELECT `id`,`title_l` FROM `'.P.'modules` WHERE `id`'.Eleanor::$Db->In($modules));
			$modules=array();
			while($a=$R->fetch_assoc())
				$modules[$a['id']]=$a['title_l'] ? FilterLangValues((array)unserialize($a['title_l'])) : '';
		}



		$links=[
			'sort_id'=>SortDynUrl('id',$query,$defsort,$deforder),
			'sort_file'=>SortDynUrl('file',$query,$defsort,$deforder),
			'sort_title'=>SortDynUrl('title',$query,$defsort,$deforder),
			'sort_pos'=>SortDynUrl('pos',$query,$defsort,$deforder),
			'sort_status'=>SortDynUrl('status',$query,$defsort,$deforder),
			'form_items'=>$Url($query+['page'=>$page>1 ? $page : null]),
			'pp'=>function($n)use($Url,$query){ $query['per-page']=$n; return$Url($query); },
			'pagination'=>function($n)use($Url,$query){ return$Url($query+['page'=>$n===1 ? null : $n]); },
		];
		$query['sort']=$sort;
		$query['order']=$order;
	}
	else
	{
		$pp=0;
		$links=[];
	}

	$c=Eleanor::$Template->ShowList($items,$cnt,$modules,$page,$pp,$query,$links);
	Start();
	echo$c;
}