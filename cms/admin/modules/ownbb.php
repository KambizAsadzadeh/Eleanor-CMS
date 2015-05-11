<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

global$Eleanor,$title;
$table=P.'ownbb';
$lang=Eleanor::$Language->Load(DIR.'admin/translation/ownbb-*.php','ownbb');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Ownbb.php';

$ownbbs=[];
$R=Eleanor::$Db->Query("SELECT `handler` FROM `{$table}`");

while($a=$R->fetch_assoc())
	$ownbbs[]=$a['handler'];

$stock=glob(DIR.'ownbb/*.php');

if($stock)
{
	foreach($stock as $k=>&$v)
	{
		$v=basename($v, '.php');
		if(strpos($v, 'special_')===0)
			unset($stock[ $k ]);
	}
	unset($v);

	$stock=array_diff($stock, $ownbbs);
}

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$id=0;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$stock ? $Url(['do'=>'create']) : null,
];

if(isset($_GET['do']))switch($_GET['do'])
{
	case'resort':
		Resort();
		GoAway();
	break;
	case'create':
		if($stock)
			goto CreateEdit;
	default:
		GoAway($Eleanor->module['links']['list']);
}
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

		$orig['title']=$orig['title_l'] ? json_decode($orig['title_l'],true) : [''=>''];
		$orig['tags']=$orig['tags'] ? explode(',',trim($orig['tags'],',')) : [];
		$orig['sp_tags']=$orig['sp_tags'] ? explode(',',trim($orig['sp_tags'],',')) : [];
		$orig['gr_use']=$orig['gr_use'] ? explode(',',trim($orig['gr_use'],',')) : [];
		$orig['gr_see']=$orig['gr_see'] ? explode(',',trim($orig['gr_see'],',')) : [];

		unset($orig['title_l']);
	}

	if($post)
	{
		include_once DIR.'crud.php';

		if(Eleanor::$vars['multilang'])
			$title_=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : [];
		else
			$title_=isset($_POST['title']) ? [''=>(string)Eleanor::$POST['title']] : [];

		$values=[
			'no_parse'=>isset($_POST['no_parse']),
			'special'=>isset($_POST['special']),
			'sb'=>isset($_POST['sb']),
		];

		PostValues($values,[
			'handler'=>'string',
			'pos'=>'int',
			'tags'=>'array',
			'sp_tags'=>'array',
			'gr_use'=>'array',
			'gr_see'=>'array',
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

		if(isset($errors['EMPTY_TITLE']))
			$errors['EMPTY_TITLE']=$lang['EMPTY_TITLE']( $errors['EMPTY_TITLE']==[''] ? [] : $errors['EMPTY_TITLE'] );

		if(empty($values['tags']))
			$errors[]='EMPTY_TAGS';

		if($errors)
			goto EditForm;

		$values['title_l']=$title_ ? json_encode($title_,JSON) : '';

		if(isset($values['tags']))
			$values['tags']=$values['tags'] ? join(',',$values['tags']) : '';

		if(isset($values['sp_tags']))
			$values['sp_tags']=$values['sp_tags'] ? join(',',$values['sp_tags']) : '';

		if(isset($values['gr_see']))
			$values['gr_see']=$values['gr_see'] ? join(',',$values['gr_see']) : '';

		if(isset($values['gr_use']))
			$values['gr_use']=$values['gr_use'] ? join(',',$values['gr_use']) : '';

		if($id)
		{
			if(!isset($values['pos']))
				$values['pos']=$orig['pos'];

			if($values['pos']>0)
			{
				$R=Eleanor::$Db->Query("SELECT MAX(`pos`) FROM `{$table}`");
				list($maxpos)=$R->fetch_row();
				$values['pos']=min($values['pos'],(int)$maxpos+1);
			}

			if($orig['pos']!=$values['pos'])
			{
				Eleanor::$Db->Update($table,['!pos'=>'`pos`-1'],'`pos`>'.$orig['pos']);
				Eleanor::$Db->Update($table,['!pos'=>'`pos`+1'],'`pos`>='.$values['pos']);
			}

			Eleanor::$Db->Update($table, $values, '`id`='.$id.' LIMIT 1');
		}
		else
		{
			$values+=['pos'=>0];

			if($values['pos']>0)
			{
				$R=Eleanor::$Db->Query("SELECT MAX(`pos`) FROM `{$table}`");
				list($maxpos)=$R->fetch_row();
				$values['pos']=min($values['pos'],(int)$maxpos+1);
			}

			Eleanor::$Db->Insert($table, $values);
		}

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->IframeResponse((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	if($id)
	{
		$origpos=$orig['pos'];
		$values=$orig;
		$title[]=$lang['editing'];

		if(in_array($values['handler'],$ownbbs))
			array_unshift($stock,$values['handler']);

		if(!Eleanor::$vars['multilang'])
			$values['title']=FilterLangValues($values['title']);
	}
	else
	{
		$title[]=$lang['creating'];
		$values=[
			'handler'=>reset($stock),
			'pos'=>0,
			'status'=>1,
			'title'=>Eleanor::$vars['multilang'] ? array_fill_keys(array_keys(Eleanor::$langs),'') : '',
			'tags'=>[],
			'no_parse'=>false,
			'special'=>false,
			'sp_tags'=>[],
			'gr_use'=>[],
			'gr_see'=>[],
			'sb'=>false,
		];

		$origpos=0;
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		PostValues($values,[
			'handler'=>'string',
			'pos'=>'int',
			'title'=>Eleanor::$vars['multilang'] ? 'array' : 'string',
			'tags'=>'array',
			'sp_tags'=>'array',
			'gr_use'=>'array',
			'gr_see'=>'array',
		]);
		IntValue($values,'status',[0,1]);

		$values['no_parse']=isset($_POST['no_parse']);
		$values['special']=isset($_POST['special']);
		$values['sb']=isset($_POST['sb']);
	}

	$data=[
		'handlers'=>$stock,
		'poses'=>[],
		'ownbb'=>[],
	];

	$R=Eleanor::$Db->Query("SELECT `id`, `pos`, `handler`, `title_l` `title` FROM `{$table}` ORDER BY `pos` ASC");
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
		$data['ownbb'][ $a['handler'] ]=array_slice($a,3);

		if($a['id']!=$id)
		{
			if($a['pos']<$origpos or $id==0)
				$a['pos']++;

			$data['poses'][ $a['pos'] ]=array_slice($a,2);
		}
	}

	if(Eleanor::$vars['multilang'])
		foreach(Eleanor::$langs as $lng=>$_)
			if(!isset($values['title'][$lng]))
				$values['title'][$lng]='';

	$links=[
		'delete'=>$id ? $Url(['delete'=>$id,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$c=Eleanor::$Template->CreateEdit($id,$values,$data,$errors,$back,$links);
	Response($c);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title_l` `title`, `handler`, `tags`, `pos` FROM `{$table}` WHERE `id`={$id} LIMIT 1");
	if(!Eleanor::$ourquery or !$ownbb=$R->fetch_assoc())
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($table,'`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Update($table,['!pos'=>'`pos`-1'],'`pos`>'.$ownbb['pos']);
		Eleanor::$Cache->Obsolete('ownbb');

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->Iframe((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];
	$ownbb['title']=$ownbb['title'] ? FilterLangValues(json_decode($ownbb['title'],true)) : '';

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($ownbb,$back) );
}
elseif(isset($_GET['toggle']))
{
	$id=(int)$_GET['toggle'];

	if(Eleanor::$ourquery)
	{
		Eleanor::$Db->Update($table,['!status'=>'NOT `status`'],'`id`='.$id.' LIMIT 1');
		Eleanor::$Cache->Obsolete('ownbb');
	}

	GoAway(false,301,'item'.$id);
}
else
{
	if(AJAX and isset($_POST['order']))
	{
		$order=explode(',',(string)$_POST['order']);
		$in=Eleanor::$Db->In($order);
		$R=Eleanor::$Db->Query("SELECT `pos` FROM `{$table}` WHERE `id`{$in} ORDER BY `pos` ASC");

		if(count($order)==$R->num_rows)
		{
			foreach($order as $v)
				Eleanor::$Db->Update($table,$R->fetch_assoc(),'`id`='.$v.' LIMIT 1');

			$status='ok';
		}
		else
			$status='error';

		Output::SendHeaders('text');
		Output::Gzip($status);
		return 1;
	}

	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$where=$query=$items=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['handler']) and $_REQUEST['fi']['handler']!='')
		{
			$query['fi']['handler']=(string)$_REQUEST['fi']['handler'];
			$q=Eleanor::$Db->Escape($query['fi']['handler'],false);
			$where[]="`handler` LIKE '%{$q}%'";
		}

		if(isset($_REQUEST['fi']['tags']) and $_REQUEST['fi']['tags']!='')
		{
			$query['fi']['tags']=(string)$_REQUEST['fi']['tags'];
			$q=Eleanor::$Db->Escape($query['fi']['tags'],false);
			$where[]="`tags` LIKE '%{$q}%'";
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

	$defsort='pos';
	$deforder='asc';
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
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','handler','pos','status'],$defsort,$deforder);

		$R=Eleanor::$Db->Query("SELECT `id`, `status`, `title_l` `title`, `handler`, `no_parse`, `tags`, `special`, `sp_tags`, `sb` FROM `{$table}`{$where} ORDER BY `{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
			$a['_atoggle']=$Url(['toggle'=>$a['id']]);
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$Url(['delete'=>$a['id']]);
			$a['sp_tags']=$a['sp_tags'] ? explode(',',trim($a['sp_tags'],',')) : [];
			$a['tags']=$a['tags'] ? explode(',',trim($a['tags'],',')) : [];

			$items[$a['id']]=array_slice($a,1);
		}

		$links=[
			'sort_id'=>SortDynUrl('id',$query,$defsort,$deforder),
			'sort_handler'=>SortDynUrl('handler',$query,$defsort,$deforder),
			'sort_pos'=>SortDynUrl('pos',$query,$defsort,$deforder),
			'sort_status'=>SortDynUrl('status',$query,$defsort,$deforder),
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

/** Восстановление правильного порядка OwnBB кодов */
function Resort()
{
	$table=P.'ownbb';
	$n=0;
	$R=Eleanor::$Db->Query("SELECT `id`, `pos` FROM `{$table}` ORDER BY `pos` ASC");
	while($a=$R->fetch_assoc())
	{
		++$n;
		if($a['pos']!=$n)
			Eleanor::$Db->Update(P.'ownbb',['pos'=>$n],'`id`='.$a['id'].' LIMIT 1');
	}
}