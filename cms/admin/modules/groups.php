<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor,$title;
$table=P.'groups';
$lang=Eleanor::$Language->Load(DIR.'admin/translation/groups-*.php','groups');
Eleanor::$Template->queue[]=Eleanor::$Template->classes.'Groups.php';

/** @var DynUrl $Url */
$Url=$Eleanor->DynUrl;
$post=$_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$ourquery;
$id=0;
$Eleanor->module['links']=[
	'list'=>(string)$Url,
	'create'=>$Url(['do'=>'create']),
	'parent_create'=>false,
];

if(isset($_GET['do']) and $_GET['do']=='create')
	goto CreateEdit;
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];

	CreateEdit:

	$errors=[];
	$controls=\Eleanor\AwareInclude(__DIR__.'/users/groups.php');

	if($id)
	{
		$R=Eleanor::$Db->Query("SELECT * FROM `{$table}` WHERE `id`={$id} LIMIT 1");
		if(!$orig=$R->fetch_assoc())
			return GoAway();

		if($orig['protected'])
			unset($controls['is_admin'],$controls['banned'],$controls['closed_site_access']);
	}

	if($post)
	{
		$C=new Controls;
		$values=$C->SaveControls($controls);
		$errors=$C->errors;

		include_once DIR.'crud.php';

		PostValues($values,[
			'_inherit'=>'array',
			'parent'=>'int',
			'style'=>'string',
		]);

		if(Eleanor::$vars['multilang'])
		{
			$title_=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : [];
			$descr=isset($_POST['descr']) ? (array)Eleanor::$POST['descr'] : [];
		}
		else
		{
			$title_=isset($_POST['title']) ? [''=>(string)Eleanor::$POST['title']] : [];
			$descr=isset($_POST['descr']) ? [''=>(string)Eleanor::$POST['descr']] : [];
		}

		foreach($title_ as $k=>&$v)
		{
			$v=trim($v);

			if($v=='')
				if($id or $k==Language::$main or !isset($title_[ Language::$main ]))
					$errors['EMPTY_TITLE'][]=$k;
				else
					$v=$title_[ Language::$main ];
		}

		if(!$id and !$title_)
			$errors['EMPTY_TITLE'][]=Language::$main;

		foreach($descr as $k=>&$v)
		{
			$v=trim($v);

			if($v=='' and !$id and $k!=Language::$main and isset($descr[ Language::$main ]))
				$v=$descr[ Language::$main ];
		}
		unset($v);

		if(isset($errors['EMPTY_TITLE']))
			$errors['EMPTY_TITLE']=$lang['EMPTY_TITLE']( $errors['EMPTY_TITLE']==[''] ? [] : $errors['EMPTY_TITLE'] );

		if(isset($values['_inherit']))
			foreach($values['_inherit'] as $v)
				unset($values[$v]);

		if($errors)
			goto EditForm;

		unset($values['_inherit']);

		#Установка поля parents
		if(isset($_POST['parent']))
		{
			$parent=(int)$_POST['parent'];
			$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$table}` WHERE `id`={$parent}");
			if($R->num_rows>0)
			{
				$values['parents']=$R->fetch_row()[0].$parent.',';

				#Проверка, не поместили ли мы себя внутри себя
				if(strpos(','.$id.',',','.$values['parents'])===false)
					$values['parent']=$parent;
				else
					$values['parents']='';
			}
		}
		#/Установка поля parents

		if($title_)
			$values['title_l']=json_encode($title_,JSON);

		if($descr)
			$values['descr_l']=json_encode($descr,JSON);

		if($id)
			Eleanor::$Db->Update($table,$values,"`id`={$id} LIMIT 1");
		else
			Eleanor::$Db->Insert($table,$values);

		Eleanor::$Cache->Engine->DeleteByTag('groups');

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->IframeResponse((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	EditForm:

	if($id)
	{
		$values=$orig;
		$values['title']=$values['title_l'] ? json_decode($values['title_l'],true) : [''=>''];
		$values['descr']=$values['descr_l'] ? json_decode($values['descr_l'],true) : [''=>''];
		$values['_inherit']=$orig['parent'] ? [] : ['style'];

		foreach($orig as $k=>$v)
			if($v===null or !$orig['parent'] and isset($controls[$k]))
				$values['_inherit'][]=$k;

		if(!Eleanor::$vars['multilang'])
		{
			$values['title']=FilterLangValues($values['title']);
			$values['descr']=FilterLangValues($values['descr']);
		}

		$title[]=$lang['editing'];
	}
	else
	{
		$def=Eleanor::$vars['multilang'] ? [] : '';
		$values=[
			'parent'=>isset($_GET['parent']) ? (int)$_GET['parent'] : 0,
			'title'=>$def,
			'descr'=>$def,
			'style'=>'',
			'_inherit'=>['style'],
		];

		foreach($controls as $k=>$v)
			$values['_inherit'][]=$k;

		$title[]=$lang['creating'];
	}

	if($errors)
	{
		if($errors===true)
			$errors=[];

		$data=[
			'_inherit'=>'array',
			'parent'=>'int',
			'style'=>'string',
		];

		if(Eleanor::$vars['multilang'])
			$data+=[
				'title'=>'array',
				'descr'=>'array',
			];
		else
			$data+=[
				'title'=>'string',
				'descr'=>'string',
			];

		PostValues($values,$data);
	}

	$links=[
		'delete'=>$id && !$orig['protected'] ? $Url(['delete'=>$id,'noback'=>1,'iframe'=>isset($_GET['iframe']) ? 1 : null]) : false,
	];

	if(isset($_GET['noback']) or isset($_GET['iframe']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	if(Eleanor::$vars['multilang'])
		foreach(Eleanor::$langs as $lng=>$_)
		{
			if(!isset($values['title'][$lng]))
				$values['title'][$lng]='';

			if(!isset($values['descr'][$lng]))
				$values['descr'][$lng]='';
		}

	$parents=!$id || !$orig['protected'] ? UserManager::GroupsOpts($values['parent'],$id) : '';
	$Controls2Html=function($controls)use($values){
		foreach($values as $k=>$v)
			if(isset($controls[$k]))
				$controls[$k]['value']=$v;

		$C=new Controls;
		return$C->DisplayControls($controls);
	};
	$Editor=function()use($Eleanor){
		return call_user_func_array([$Eleanor->Editor,'Area'],func_get_args());
	};

	$c=Eleanor::$Template->CreateEdit($id,$values,$Editor,$controls,$parents,$Controls2Html,$errors,$back,$links);
	Response($c);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query("SELECT `title_l` `title`, `style`, `parents` FROM `{$table}` WHERE `id`={$id} AND `protected`=0");
	if(!Eleanor::$ourquery or !$group=$R->fetch_assoc())
		return GoAway(true);

	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete($table,"`id`={$id} LIMIT 1");
		Eleanor::$Db->Delete($table,"`parents` LIKE '{$group['parents']}{$id},%' LIMIT 1");
		Eleanor::$Cache->Engine->DeleteByTag('groups');

		if(isset($_GET['iframe']))
			return Response( Eleanor::$Template->Iframe((string)$Url) );

		return GoAway(empty($_POST['back']) ? true : (string)$_POST['back']);
	}

	$title[]=$lang['deleting'];
	$group['title']=$group['title'] ? FilterLangValues(json_decode($group['title'],true)) : '';

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	Response( Eleanor::$Template->Delete($group,$back) );
}
else
{
	$title[]=$lang['list'];
	$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
	$navi=$where=$query=$items=[];

	if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']) and !AJAX)
	{
		if($post)
			$page=1;

		if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
		{
			$query['fi']['title']=(string)$_REQUEST['fi']['title'];
			$q=Eleanor::$Db->Escape(preg_quote(mb_strtolower($query['fi']['title'])),false);
			$mainlang=Language::$main;
			$where[]="LOWER(`title_l`) REGEXP '\"({$mainlang})?\": \"[^\"]*{$q}'";
		}
	}

	if(isset($_REQUEST['parent']) and 0<$query['parent']=(int)$_REQUEST['parent'])
	{
		$R=Eleanor::$Db->Query("SELECT `parents` FROM `{$table}` WHERE `id`={$query['parent']} LIMIT 1");
		list($parents)=$R->fetch_row();

		$parents.=$query['parent'];
		$where[]='`g`.`parent`='.$query['parent'];

		if(!AJAX)
		{
			$R=Eleanor::$Db->Query("SELECT `id`, `title_l` `title` FROM `{$table}` WHERE `id` IN ({$parents})");
			while($a=$R->fetch_assoc())
				$items[$a['id']]=$a['title'] ? FilterLangValues(json_decode($a['title'], true)) : '';

			foreach(explode(',', $parents) as $v)
				if(isset($items[$v]))
					$navi[$v]=$v==$query['parent'] ? $items[$v] : ['title'=>$items[$v], '_a'=>$Url(['parent'=>$v])];

			$items=[];
			$Eleanor->module['links']['parent_create']=$Url(['do'=>'create', 'parent'=>$query['parent']]);
		}
	}
	else
		$where[]='`g`.`parent` IS NULL';

	$where=' WHERE '.join(' AND ',$where);
	$defsort='id';
	$deforder='asc';
	include DIR.'sort-helper.php';

	$R=Eleanor::$Db->Query("SELECT COUNT(`id`) FROM `{$table}` `g`{$where}");
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
		list($sort,$order,$limit,$pp)=SortOrderLimit($cnt,$page,$query,['id','parent'],$defsort,$deforder);

		$parents=[];
		$inherit=['style','is_admin','max_upload','captcha','moderate','banned'];
		$inherit_=', `g`.`'.join('`,`g`.`',$inherit).'`';
		$R=Eleanor::$Db->Query("SELECT `g`.`id`, `g`.`parents`, `g`.`protected`, `g`.`title_l` `title`, `g`.`descr_l` `descr`{$inherit_},COUNT(`sg`.`parent`) `children`
FROM `{$table}` `g` LEFT JOIN `{$table}` `sg` ON `sg`.`parent`=`g`.`id`{$where}
GROUP BY `g`.`id` ORDER BY `g`.`{$sort}` {$order}{$limit}");
		while($a=$R->fetch_assoc())
		{
			$a['title']=$a['title'] ? FilterLangValues(json_decode($a['title'],true)) : '';
			$a['descr']=$a['descr'] ? FilterLangValues(json_decode($a['descr'],true)) : '';
			$a['parents']=$a['parents'] ? explode(',',rtrim($a['parents'],',')) : [];
			$a['_aedit']=$Url(['edit'=>$a['id']]);
			$a['_adel']=$a['protected'] ? null : $Url(['delete'=>$a['id']]);
			$a['_achildren']=$a['children']>0 ? $Url(['parent'=>$a['id']]) : false;
			$a['_acreate']=$Url(['do'=>'create','parent'=>$a['id']]);

			$items[$a['id']]=array_slice($a,1);

			if($a['parents'])
				$parents=array_merge($parents,$a['parents']);
		}

		if($parents)
		{
			$in=Eleanor::$Db->In($parents);
			$parents=[];
			$R=Eleanor::$Db->Query("SELECT `id`{$inherit_} FROM `{$table}` `g` WHERE `id`{$in}");
			while($a=$R->fetch_assoc())
				$parents[ $a['id'] ]=array_slice($a,1);

			foreach($items as &$item)
				foreach($inherit as $inh)
					if(!isset($item[$inh]))
						foreach($item['parents'] as $parent)
							if(isset($parents[$parent][$inh]))
								$item[$inh]=$parents[$parent][$inh];

			unset($item);
		}

		$links=[
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
	$c=AJAX
		? Eleanor::$Template->LoadSubGroups($items)
		: Eleanor::$Template->ShowList($items,$navi,$total>0,$cnt,$pp,$query,$page,$links);
	Response($c);
}