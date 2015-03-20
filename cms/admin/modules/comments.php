<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\EE;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
$table=P.'comments';
$lang=Eleanor::$Language->Load(DIR.'admin/translation/comments-*.php','lc');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Comments.php';

if(!isset($_GET['section']) or $_GET['section']!='management')
	return CommentsList(true);

$R=Eleanor::$Db->Query("SELECT COUNT(`status`) FROM `{$table}` WHERE `status`=-1");
list($cnt)=$R->fetch_row();

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'news'=>$cnt>0
		? [ 'link'=>$Url(['fi'=>['status'=>-1]]),
			'cnt'=>$cnt ]
		: null,
	'options'=>$Url(['do'=>'options']),
];

if(isset($_GET['do']))switch($_GET['do'])
{
	case'options':
		$Url->prefix.='do=options&amp;';
		$c=$Eleanor->Settings->Group('comments');

		if($c)
			Response( Eleanor::$Template->Options($c) );
	break;
	default:
		GoAway(true);
}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	$errors=[];

	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery)
	{
		$R=Eleanor::$Db->Query("SELECT `status` FROM `{$table}` WHERE id={$id} LIMIT 1");
		if(!$comment=$R->fetch_assoc())
			return GoAway(true);

		$status=isset($_POST['status']) ? (int)$_POST['status'] : 1;
		$values=[
			'text'=>isset($_POST['text']) ? $Eleanor->Saver->Save((string)$_POST['text']) : '',
		];

		if($status<-1 or $status>1)
			$status=1;

		if($comment['status']!=$status)
			ChangeStatus($id,$status);

		Eleanor::$Db->Update($table,$values,'`id`='.$id.' LIMIT 1');
		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['editing'];
	$R=Eleanor::$Db->Query('SELECT `id`,`module`,`content_id`,`status`,`date`,`author`,`author_id`,`text` FROM `'.P
		.'comments` WHERE id='.$id.' LIMIT 1');
	if(!$values=$R->fetch_assoc())
		return GoAway(true);

	if($errors)
	{
		if($errors===true)
			$errors=[];

		$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
		$values['status']=isset($_POST['status']) ? (int)$_POST['status'] : 0;
		$bypost=true;
	}
	else
		$bypost=false;

	$module=CommentsModules($values['module']);

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$comment=isset($module['api']) ? $module['api']->Link2Comment([$values['content_id']=>[$values['id']]]) : [];

	$links=[
		'delete'=>$Url(['delete'=>$id,'noback'=>1]),
		'author'=>$values['author_id'] ? UserLink($values['author_id'],$values['author'],'admin') : false,
		'comment'=>isset($comment[ $values['id'] ]) ? $comment[ $values['id'] ] : [],
	];

	Response(Eleanor::$Template->Edit($id,$values,$Eleanor->Editor,$module,$bypost,$errors,$back,$links));
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `parents`,`text` FROM `{$table}` WHERE `id`={$id} LIMIT 1");

	if(!$comment=$R->fetch_assoc() or !Eleanor::$ourquery)
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		ChangeStatus($id,0);

		$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if($comment=$R->fetch_assoc())
		{
			Eleanor::$Db->Delete($table,'`parents` LIKE \''.$comment['parents'].$id.',%\'');
			Eleanor::$Db->Delete($table,'`id`='.$id.' LIMIT 1');
		}

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($comment,$back) );
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		$R=Eleanor::$Db->Query("SELECT `status` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if($comment=$R->fetch_assoc())
			ChangeStatus($id,$comment['status']<1 ? 1 : 0);
	}

	$back=getenv('HTTP_REFERER');
	GoAway($back ? $back.'#comment'.$id : true);
}
else
	CommentsList();

/** Список комментариев
 * @param bool $embed Флаг включенности комментариев в другую страницу
 * @return string */
function CommentsList($embed=false)
{global$Eleanor,$title;
	if(!$embed)
		$title=Eleanor::$Language['lc']['list'];

	$table=P.'comments';
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$modcomm=$items=$where=$query=[];
	$modules=CommentsModules();

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($_SERVER['REQUEST_METHOD']=='POST')
			$page=1;

		$query['fi']=[];

		if(isset($_REQUEST['fi']['module']))
		{
			$m=(int)$_REQUEST['fi']['module'];

			if(isset($modules[$m]))
			{
				$query['fi']['module']=$m;
				$where[]='`module`='.$m;
			}
		}
	}

	$where=$where ? ' WHERE '.join(' AND ',$where) : '';
	if(Eleanor::$ourquery and isset($_POST['op'],$_POST['mass']) and is_array($_POST['mass']))
		switch($_POST['op'])
		{
			case'a':
				ChangeStatus($_POST['mass'],1);
			break;
			case'd':
				ChangeStatus($_POST['mass'],-1);
			break;
			case'b':
				ChangeStatus($_POST['mass'],0);
			break;
			case'k':
				ChangeStatus($_POST['mass'],0);

				$R=Eleanor::$Db->Query("SELECT `id`,`parents` FROM `{$table}` WHERE `id`"
					.Eleanor::$Db->In($_POST['mass']));
				while($a=$R->fetch_assoc())
				{
					Eleanor::$Db->Delete($table,'`parents` LIKE \''.$a['parents'].$a['id'].',%\'');
					Eleanor::$Db->Delete($table,'`id`='.$a['id'].' LIMIT 1');
				}
		}

	/** @var DynUrl $Url */
	$Url=$Eleanor->DynUrl;
	$sort='id';
	$order='desc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}`{$where}");
	list($cnt)=$R->fetch_row();

	if($cnt>0)
	{
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','date','author','ip'],$sort,$order);

		$R=Eleanor::$Db->Query("SELECT `id`, `module`, `content_id`, `status`, `date`, `author`, `author_id`, `ip`, `text` FROM `{$table}`{$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$modcomm[ $a['module'] ][ $a['content_id'] ][]=$a['id'];

			$a['ip']=inet_ntop($a['ip']);
			$a['_aauthor']=$a['author_id'] ? UserLink($a['author_id'],$a['author'],'admin') : false;
			$a['_atoggle']=$Url(['toggle'=>$a['id']]);
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$Url(['delete'=>$a['id']]);
			$a['_a']=$a['_title']=false;

			$items[ $a['id'] ]=array_slice($a,1);
		}

		$links=[
			'sort_date'=>SortDynUrl('date',$query,'id','desc'),
			'sort_author'=>SortDynUrl('author',$query,'id','desc'),
			'sort_ip'=>SortDynUrl('ip',$query,'id','desc'),
			'sort_id'=>SortDynUrl('id',$query,'id','desc'),
			'form_items'=>$Url(array_merge($query,['page'=>$page>1 ? $page : false])),
			'pp'=>function($n)use($Url,$query){ return$Url(array_merge($query+['per-page'=>$n])); },
			'first_page'=>$Url($query),
			'pages'=>function($n)use($Url,$query){ return$Url(array_merge($query,['page'=>$n])); },
		];

		foreach($modcomm as $k=>$v)
			if(isset($modules[$k]))
			{
				/** @var Interfaces\Comments $Api */
				$Api=$modules[$k]['api'];

				foreach($Api->Link2Comment($v) as $kk=>$vv)
				{
					$items[$kk]['_a']=$vv[0];
					$items[$kk]['_title']=$vv[1];
				}
			}
	}
	else
	{
		$pp=0;
		$links=[];
	}

	$c=Eleanor::$Template->CommentsList($items,$cnt,$pp,$sort,$order,$page,$query,$links,$embed);

	if($embed)
		return$c;

	Response($c);
}

/** Изменение статуса комментария
 * @param array|int $ids Идентификатор комментария
 * @param int $status Идентификатор статуса
 * @param array|null $modules Апи модулей*/
function ChangeStatus($ids,$status,$modules=null)
{
	$table=P.'comments';
	$activate=$status==1;
	$parents='';

	$R=Eleanor::$Db->Query("SELECT `id`,`module`,`content_id`,`status`,`parent`,`parents` FROM `{$table}` WHERE `id`"
		.Eleanor::$Db->In($ids));

	$ids=$statuses=$answers=$children=$change=$changeid=[];
	while($comment=$R->fetch_assoc())
	{
		$parents.=$comment['parents'];

		if(strpos($parents,','.$comment['id'].',')===false)
			$children[]=$comment['parents'].$comment['id'].',';

		$ids[]=$comment['id'];
		$statuses[ $comment['status'] ][]=$comment['id'];

		if($comment['parent'] and ($activate and $comment['status']!=1 or !$activate and $comment['status']==1))
			$answers[ $comment['parent'] ]=isset($answers[ $comment['parent'] ]) ? $answers[ $comment['parent'] ]+1 : 1;

		$change[ $comment['module'] ][ $comment['content_id'] ]=0;
		$changeid[ $comment['id'] ]=&$change[ $comment['module'] ][ $comment['content_id'] ];
	}

	$table=P.'comments';
	$nin=Eleanor::$Db->In($ids,true);

	foreach($children as $v)
	{
		$R=Eleanor::$Db->Query("SELECT `id`, `module`, `content_id`, `status`, `parent` FROM `{$table}` WHERE `parents` LIKE '{$v}%' AND `id`{$nin}");
		while($comment=$R->fetch_assoc())
		{
			$ids[]=$comment['id'];
			$statuses[ $comment['status'] ][]=$comment['id'];

			if($comment['parent'] and ($activate and $comment['status']!=1 or !$activate and $comment['status']==1))
				$answers[ $comment['parent'] ]=isset($answers[ $comment['parent'] ])
					? $answers[ $comment['parent'] ]+1
					: 1;

			$change[ $comment['module'] ][ $comment['content_id'] ]=0;
			$changeid[ $comment['id'] ]=&$change[ $comment['module'] ][ $comment['content_id'] ];
		}
	}

	Eleanor::$Db->Transaction();
	foreach($statuses as $k=>$v)
		if($k==1)
		{
			if(!$activate)
				foreach($v as $vv)
					$changeid[$vv]-=Eleanor::$Db->Update($table,['!approved'=>'NOW()','status'=>$status],
						'`id`='.$vv.' LIMIT 1');
			elseif($status!=$k)
				Eleanor::$Db->Update($table,['status'=>$status],'`id`'.Eleanor::$Db->In($v));
		}
		else
		{
			if($activate)
				foreach($v as $vv)
					$changeid[$vv]+=Eleanor::$Db->Update($table,['!approved'=>'NOW()','status'=>$status],
						'`id`='.$vv.' LIMIT 1');
			elseif($status!=$k)
				Eleanor::$Db->Update($table,['status'=>$status],'`id`'.Eleanor::$Db->In($v));
		}

	foreach($answers as $k=>$v)
		Eleanor::$Db->Update($table,['!answers'=>$activate ? '`answers`+'.$v : 'GREATEST(0,`answers`-'.$v.')'],
			'`id`='.$k.' LIMIT 1');

	Eleanor::$Db->Commit();

	if($modules===null)
		$modules=CommentsModules(array_keys($change));

	foreach($change as $module=>$id2change)
		if(isset($modules[$module]))
		{
			/** @var Interfaces\Comments $Api */
			$Api=$modules[$module]['api'];

			try
			{
				$Api->UpdateCommentsCounter($id2change);
			}
			catch(EE$E)
			{
				$E->Log();
			}
		}
}

/** Получение API извлечение ссылки
 * @param int|array $ids
 * @return array of ['api'=>Interfaces\Link2Comment,'title'=>''] */
function CommentsModules($ids=[])
{
	$isa=is_array($ids);
	$modules=[];
	$table=P.'modules';
	$in=$ids ? ' AND `id`'.Eleanor::$Db->In($ids) : '';

	$R=Eleanor::$Db->Query("SELECT `id`, `title_l`, `uris`, `path`, `api` FROM `{$table}` WHERE `api`!=''{$in}");
	while($module=$R->fetch_assoc())
	{
		$api=DIR.$module['path'].$module['api'];
		$class='Api'.basename($module['path']);

		if(!class_exists($class,false))
			if(is_file($api))
			{
				$retapi=\Eleanor\AwareInclude($api);

				if(is_string($retapi))
					$class=$retapi;

				if(!class_exists($class,false))
					continue;
			}
			else
				continue;

		$module['uris']=json_decode($module['uris'],true);

		foreach($module['uris'] as $k=>&$v)
			if(Eleanor::$vars['multilang'] and isset($v[ Language::$main ]))
				$v=reset($v[ Language::$main ]);
			elseif(isset($v['']))
				$v=reset($v['']);
			else
				unset($module['uris'][$k]);

		if(is_a($class,'CMS\Interfaces\Comments',true))
		{
			$Api=new$class([
				'uris'=>$module['uris'],
				'id'=>$module['id'],
			]);

			$modules[ $module['id'] ]=[
				'api'=>$Api,
				'title'=>$module['title_l'] ? FilterLangValues(json_decode($module['title_l'],true)) : '',
			];
		}
	}

	if(!$isa and $modules)
		return reset($modules);

	uasort($modules,function($a,$b){
		return strcasecmp($a['title'],$b['title']);
	});

	return$modules;
}