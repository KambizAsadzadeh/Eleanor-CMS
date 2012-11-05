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
/*
	������� ������:
		-3 - ���� ������� ����������� ���� ������ ������
		-2 -  ���� �� ������������, ��������� ��������� ���� ���������� �������
		-1 - ���������������
		0 - ���� ������������
		1 - ���� �����������
*/
if(!defined('CMS'))die;
global$Eleanor,$title;
$lang=Eleanor::$Language->Load('addons/admin/langs/blocks-*.php','blocks');
Eleanor::$Template->queue[]='Blocks';

$Eleanor->module['links']=array(
	'main'=>$Eleanor->Url->Prefix(),
	'ids'=>$Eleanor->Url->Construct(array('do'=>'identification')),
	'addi'=>$Eleanor->Url->Construct(array('do'=>'addi')),
	'list'=>$Eleanor->Url->Construct(array('do'=>'list')),
	'add'=>$Eleanor->Url->Construct(array('do'=>'add')),
);

if(isset($_GET['do']))
	switch($_GET['do'])
	{
		case'identification':
			$title[]=$lang['ipages'];
			$tosort=$items=$tmp=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`service`,`title_l` `title`,`code` FROM `'.P.'blocks_ids` ORDER BY `service` ASC');
			while($a=$R->fetch_assoc())
			{
				$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';

				$a['_aedit']=$Eleanor->Url->Construct(array('editi'=>$a['id']));
				$a['_adel']=$Eleanor->Url->Construct(array('deletei'=>$a['id']));

				$tosort[$a['service']][$a['id']]=$a['title'];
				$tmp[$a['id']]=array_slice($a,2);
			}
			foreach($tosort as &$v)
				natsort($v);
			ksort($tosort,SORT_STRING);
			foreach($tosort as $k=>&$v)
				foreach($v as $kk=>&$vv)
					$items[$k][$kk]=$tmp[$kk];

			$c=Eleanor::$Template->BlocksIdsList($items);
			Start();
			echo$c;
		break;
		case'list':
			$title[]=$lang['lab'];
			$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$qs=array('do'=>'list');
			$groups=$items=array();
			$where='';
			if(isset($_REQUEST['fi']) and is_array($_REQUEST['fi']))
			{
				if($_SERVER['REQUEST_METHOD']=='POST')
					$page=1;
				$qs['']['fi']=array();
				if(isset($_REQUEST['fi']['title']) and $_REQUEST['fi']['title']!='')
				{
					$t=Eleanor::$Db->Escape((string)$_REQUEST['fi']['title'],false);
					$qs['']['fi']['title']=$_REQUEST['fi']['title'];
					$where.=' AND `title` LIKE \'%'.$t.'%\'';
				}
				if(isset($_REQUEST['fi']['status']) and $_REQUEST['fi']['status']!='-')
				{
					$qs['']['fi']['status']=(int)$_REQUEST['fi']['status'];
					$where.=' AND `status`='.$qs['']['fi']['status'];
				}
			}

			if(Eleanor::$our_query and isset($_POST['op'],$_POST['mass']))
			{
				$in=Eleanor::$Db->In($_POST['mass']);
				switch($_POST['op'])
				{
					case'd':
						Eleanor::$Db->Update(P.'blocks',array('status'=>0),'`id`'.$in);
					break;
					case'a':
						$t=time();
						$R=Eleanor::$Db->Query('SELECT `id`,`showfrom`,`showto` FROM `'.P.'blocks` WHERE `id`'.$in.' AND `status`=0');
						while($a=$R->fetch_assoc())
						{
							$sf=(int)$a['showfrom'] ? strtotime($a['showfrom']) : false;
							$st=(int)$a['showto'] ? strtotime($a['showto']) : false;
							$upd=array();
							if($sf and $sf>$t)
								$upd['status']=-3;
							elseif($st and $st<$t)
								$upd['status']=-2;
							else
								$upd['status']=1;
							Eleanor::$Db->Update(P.'blocks',$upd,'`id`='.$a['id'].' LIMIT 1');
						}
					break;
					case'm':
						Eleanor::$Db->Update($Eleanor->module['config']['t'],array('status'=>0),'`id`'.$in);
					break;
					case'k':
						Eleanor::$Db->Delete(P.'blocks','`id`'.$in);
						Eleanor::$Db->Delete(P.'blocks_l','`id`'.$in);
				}
			}
			$R=Eleanor::$Db->Query('SELECT COUNT(`id`) FROM `'.P.'blocks` INNER JOIN `'.P.'blocks_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'.$where);
			list($cnt)=$R->fetch_row();
			if($page<=0)
				$page=1;
			if(isset($_GET['new-pp']) and 4<$pp=(int)$_GET['new-pp'])
				Eleanor::SetCookie('per-page',$pp);
			else
				$pp=abs((int)Eleanor::GetCookie('per-page'));
			if($pp<5 or $pp>500)
				$pp=50;
			$offset=abs(($page-1)*$pp);
			if($cnt and $offset>=$cnt)
				$offset=max(0,$cnt-$pp);
			$sort=isset($_GET['sort']) ? $_GET['sort'] : '';
			if(!in_array($sort,array('id','title','showfrom','showto','status')))
				$sort='';
			$so=$_SERVER['REQUEST_METHOD']!='POST' && $sort && isset($_GET['so']) ? $_GET['so'] : 'desc';
			if($so!='asc')
				$so='desc';
			if($sort and ($sort!='id' or $so!='asc'))
				$qs+=array('sort'=>$sort,'so'=>$so);
			else
				$sort='id';
			$qs+=array('sort'=>false,'so'=>false);

			if($cnt>0)
			{
				$R=Eleanor::$Db->Query('SELECT `id`,`ctype`,`file`,`user_groups`,`showfrom`,`showto`,`textfile`,`template`,`notemplate`,`status`,`title`,`text` FROM `'.P.'blocks` INNER JOIN `'.P.'blocks_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'.$where.' ORDER BY `'.$sort.'` '.$so.' LIMIT '.$offset.', '.$pp);
				while($a=$R->fetch_assoc())
				{
					$a['user_groups']=$a['user_groups'] ? explode(',,',trim($a['user_groups'],',')) : array();
					if($a['user_groups'])
						$groups=array_merge($groups,$a['user_groups']);

					$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
					$a['_aswap']=$a['status']==-2 ? false : $Eleanor->Url->Construct(array('swap'=>$a['id']));
					$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

					$items[$a['id']]=array_slice($a,1);
				}
			}

			if($groups)
			{
				$groups=array();
				$pref=$Eleanor->Url->file.'?&amp;module=groups&amp;';
				$R=Eleanor::$Db->Query('SELECT `id`,`title_l` `title`,`html_pref`,`html_end` FROM `'.P.'groups` WHERE `id`'.Eleanor::$Db->In($groups));
				while($a=$R->fetch_assoc())
				{
					$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
					$a['_aedit']=$pref.$Eleanor->Url->Construct(array('edit'=>$a['id']),false);
					$groups[$a['id']]=array_slice($a,1);
				}
			}

			$links=array(
				'sort_id'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'id','so'=>$qs['sort']=='id' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_title'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'title','so'=>$qs['sort']=='title' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_showto'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'showto','so'=>$qs['sort']=='showto' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'sort_showfrom'=>$Eleanor->Url->Construct(array_merge($qs,array('sort'=>'showfrom','so'=>$qs['sort']=='showfrom' && $qs['so']=='asc' ? 'desc' : 'asc'))),
				'form_items'=>$Eleanor->Url->Construct($qs+array('page'=>$page>1 ? $page : false)),
				'pp'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('new-pp'=>$n)); },
				'first_page'=>$Eleanor->Url->Construct($qs),
				'pages'=>function($n)use($qs){ return$GLOBALS['Eleanor']->Url->Construct($qs+array('page'=>$n)); },
			);

			$c=Eleanor::$Template->ShowList($items,$groups,$cnt,$pp,$qs,$page,$links);
			Start();
			echo$c;
		break;
		case'add':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				Save(0);
			else
				AddEdit(0);
		break;
		case'addi':
			if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
				SaveId(0);
			else
				AddEditId(0);
		break;
		case'draft':
			$t=isset($_POST['_draft']) ? (string)$_POST['_draft'] : '';
			if(preg_match('#^([big])(\d+|'.join('|',array_keys(Eleanor::$services)).')$#',$t,$m)>0)
			{
				unset($_POST['_draft'],$_POST['back']);
				Eleanor::$Db->Replace(P.'drafts',array('key'=>'_blocks-'.Eleanor::$Login->GetUserValue('id').'-'.$t,'value'=>serialize($_POST)));
			}
			Eleanor::$content_type='text/plain';
			Start('');
			echo'ok';
		break;
		default:
			ShowGroup();
	}
elseif(isset($_GET['edit']))
{
	$id=(int)$_GET['edit'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		Save($id);
	else
		AddEdit($id);
}
elseif(isset($_GET['editi']))
{
	$id=(int)$_GET['editi'];
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		SaveId($id);
	else
		AddEditId($id);
}
elseif(isset($_GET['delete']))
{
	$id=(int)$_GET['delete'];
	$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.P.'blocks` LEFT JOIN `'.P.'blocks_l` USING(`id`) WHERE `id`='.$id.' AND `language` IN (\'\',\''.Language::$main.'\') LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'blocks','`id`='.$id);
		Eleanor::$Db->Delete(P.'blocks_l','`id`='.$id);
		Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-b'.$id.'\'');
		Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-g'.$id.'\'');
		Eleanor::$Cache->Obsolete('blocks');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$c=Eleanor::$Template->Delete($a,$back);
	Start();
	echo$c;
}
elseif(isset($_GET['deletei']))
{
	$id=(int)$_GET['deletei'];
	$R=Eleanor::$Db->Query('SELECT `title_l` `title` FROM `'.P.'blocks_ids` WHERE `id`='.$id.' LIMIT 1');
	if(!$a=$R->fetch_assoc() or !Eleanor::$our_query)
		return GoAway(true);
	if(isset($_POST['ok']))
	{
		Eleanor::$Db->Delete(P.'blocks_ids','`id`='.$id);
		Eleanor::$Db->Delete(P.'blocks_groups','`id`='.$id);
		Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-i'.$id.'\' LIMIT 1');
		Eleanor::$Cache->Obsolete('blocks');
		return GoAway(empty($_POST['back']) ? true : $_POST['back']);
	}
	$title[]=$lang['delc'];
	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');
	$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
	$c=Eleanor::$Template->DeleteI($a,$back);
	Start();
	echo$c;
}
elseif(isset($_GET['swap']))
{
	$R=Eleanor::$Db->Query('SELECT `id`,`showfrom`,`showto`,`status` FROM `'.P.'blocks` WHERE `id`='.(int)$_GET['swap'].' LIMIT 1');
	if($a=$R->fetch_assoc())
	{
		$upd=array();
		if($a['status']==0)
		{
			$sf=(int)$a['showfrom'] ? strtotime($a['showfrom']) : false;
			$st=(int)$a['showto'] ? strtotime($a['showto']) : false;
			$t=time();
			if($sf and $sf>$t)
				$upd['status']=-3;
			elseif($st and $st<$t)
				$upd['status']=-2;
			else
				$upd['status']=1;
		}
		else
			$upd['status']=0;
		Eleanor::$Db->Update(P.'blocks',$upd,'`id`='.$a['id'].' LIMIT 1');
		Eleanor::$Cache->Obsolete('blocks');
	}
	GoAway();
}
elseif(isset($_GET['deleteg']))
{
	$id=(int)$_GET['deleteg'];
	Eleanor::$Db->Delete(P.'blocks_groups','`id`='.$id);
	Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-g'.$id.'\'');
 	Eleanor::$Cache->Obsolete('blocks');
	GoAway();
}
else
{
	$gid=isset($_GET['group']) ? (string)$_GET['group'] : 'user';
	if(!isset(Eleanor::$services[$gid]))
		$gid=(int)$gid;
	$saved=false;
	if($_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
	{
		$values=SaveGroupValues();
		if(is_int($gid))
		{
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'blocks_ids` WHERE `id`='.$gid.' LIMIT 1');
			if($R->num_rows==0)
				return ShowGroup($gid,array('UNCREATABLE'));
			$values['id']=$gid;
			$values['blocks']=$values['blocks'] ? serialize($values['blocks']) : '';
			$values['places']=$values['places'] ? serialize($values['places']) : '';
			$values['addon']=$values['addon'] ? serialize($values['addon']) : '';
			Eleanor::$Db->Replace(P.'blocks_groups',$values);
		}
		elseif(isset(Eleanor::$services[$gid]))
			Eleanor::$Cache->Put('blocks_defgr-'.$gid,$values,0,true);
		else
			return ShowGroup($gid,array('UNCREATABLE'));
		Eleanor::$Cache->Obsolete('blocks');
		if(isset($_POST['group']) and $_POST['group']!=$gid)
			return GoAway(array('group'=>$_POST['group'])+(empty($_POST['similar']) ? array() : array('similar'=>$_POST['similar'])));
		$saved=true;
	}
	ShowGroup($gid,array(),$saved);
}

function ShowGroup($gid,$errors=array(),$saved=false)
{global$Eleanor,$title;
	$lang=Eleanor::$Language['blocks'];
	$title[]=$lang['bpos'];

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-g'.$gid.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	$isi=is_int($gid);
	if($errors)
	{
		if($errors===true)
			$errors=array();
		$group=SaveGroupValues();
	}
	else
	{
		if($isi)
		{
			$R=Eleanor::$Db->Query('SELECT `blocks`,`places`,`addon` FROM `'.P.'blocks_groups` WHERE `id`='.$gid.' LIMIT 1');
			if($group=$R->fetch_assoc())
			{
				$group['blocks']=$group['blocks'] ? (array)unserialize($group['blocks']) : array();
				$group['places']=$group['places'] ? (array)unserialize($group['places']) : array();
				$group['addon']=$group['addon'] ? (array)unserialize($group['addon']) : array();
			}
		}
		else
			$group=Eleanor::$Cache->Get('blocks_defgr-'.$gid,true);

		do
		{
			if($group)
				break;

			$similar=isset($_GET['similar']) ? (string)$_GET['similar'] : 'user';
			if(!isset(Eleanor::$services[$similar]))
				$similar=(int)$similar;
			if($similar)
				if(is_int($similar))
				{
					$R=Eleanor::$Db->Query('SELECT `places`,`addon` FROM `'.P.'blocks_groups` WHERE `id`='.$similar.' LIMIT 1');
					if($group=$R->fetch_assoc())
					{
						$group['blocks']=$group['blocks'] ? (array)unserialize($group['blocks']) : array();
						$group['places']=$group['places'] ? (array)unserialize($group['places']) : array();
						$group['addon']=$group['addon'] ? (array)unserialize($group['addon']) : array();
						break;
					}
				}
				elseif($group=Eleanor::$Cache->Get('blocks_defgr-'.$similar,true))
					break;

			if($isi)
			{
				$R=Eleanor::$Db->Query('SELECT `id` FROM `'.P.'blocks_ids` WHERE `id`='.$gid.' LIMIT 1');
				if($R->num_rows>0)
					$group=true;
			}
			elseif(isset(Eleanor::$services[$gid]))
				$group=true;

			if($group)
				$group=array();
			else
				return FatalError($lang['UNCREATABLE']);
		}while(false);
	}

	$group+=array('blocks'=>array(),'places'=>array(),'addon'=>array(),);

	$blocks=$tosort=$preids=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.P.'blocks` INNER JOIN `'.P.'blocks_l` USING(`id`) WHERE `language`IN(\'\',\''.Language::$main.'\') ORDER BY `title` ASC');
	while($a=$R->fetch_assoc())
	{		$a['_aedit']=$Eleanor->Url->Construct(array('edit'=>$a['id']));
		$a['_adel']=$Eleanor->Url->Construct(array('delete'=>$a['id']));

		$blocks[$a['id']]=array_slice($a,1);
	}

	$ids=array(
		'user'=>array('user'=>array('t'=>$lang['bydef'],'g'=>true)),#t - �������� ��������������, g - ������� ������� ������ � ��������������
		'admin'=>array('admin'=>array('t'=>$lang['bydef'],'g'=>true)),
	);
	$R=Eleanor::$Db->Query('SELECT `i`.`id`,`i`.`service`,`i`.`title_l` `title`,`g`.`id` `gid` FROM `'.P.'blocks_ids` `i` LEFT JOIN `'.P.'blocks_groups` `g` USING(`id`)');
	while($a=$R->fetch_assoc())
	{
		$a['title']=$a['title'] ? Eleanor::FilterLangValues((array)unserialize($a['title'])) : '';
		$preids[]=$a;
		$tosort[]=$a['title'];
	}
	asort($tosort,SORT_STRING);
	foreach($tosort as $k=>&$v)
		$ids[$preids[$k]['service']][$preids[$k]['id']]=array('t'=>$preids[$k]['title'],'g'=>(bool)$preids[$k]['gid']);
	unset($tosort,$preids);

	$links=array(
		'del_group'=>$isi ? $Eleanor->Url->Construct(array('deleteg'=>$gid)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('group'=>$gid=='user' ? false : $gid,'nodraft'=>1)) : false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);
	$c=Eleanor::$Template->BlocksGroup($gid,$blocks,$ids,$group,$errors,$hasdraft,$saved,$links);
	Start();
	echo$c;
}

function FatalError($e)
{
	$s=Eleanor::$Template->FatalError('',$e);
	Start();
	echo$s;
}

function SaveGroupValues()
{
	$group=array();
	if(isset($_POST['place']) and is_array($_POST['place']))
		if(Eleanor::$vars['multilang'])
			foreach($_POST['place'] as $k=>&$v)
			{
				$group['blocks'][$k]=$group['places'][$k]['title']=array();
				foreach(Eleanor::$langs as $l=>&$_)
					$group['places'][$k]['title'][$l]=Eleanor::FilterLangValues((array)$v,$l);
			}
		else
			foreach($_POST['place'] as $k=>&$v)
			{
				$group['blocks'][$k]=array();
				$group['places'][$k]['title']=array(''=>Eleanor::FilterLangValues((array)$v));
			}

	if(isset($_POST['placeinfo']) and is_array($_POST['placeinfo']))
		foreach($_POST['placeinfo'] as $k=>&$v)
			if(isset($group['places'][$k]))
				$group['places'][$k]['info']=(string)$v;

	if(isset($_POST['block']) and is_array($_POST['block']))
		foreach($_POST['block'] as $k=>&$v)
			if(isset($group['blocks'][$k]))
				$group['blocks'][$k]=(array)$v;

	$group['addon']=isset($_POST['addon']) && is_array($_POST['addon']) ? $_POST['addon'] : array();
	return$group;
}

function AddEdit($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['blocks'];
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'blocks` WHERE `id`='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway();
			$values['user_groups']=$values['user_groups'] ? explode(',,',trim($values['user_groups'],',')) : array();
			$values['vars']=$values['vars'] ? (array)unserialize($values['vars']) : array();
			if((int)$values['showfrom']==0)
				$values['showfrom']='';
			if((int)$values['showto']==0)
				$values['showto']='';

			$R2=Eleanor::$Db->Query('SELECT `language`,`title`,`text`,`config` FROM `'.P.'blocks_l` WHERE `id`='.$id);
			while($temp=$R2->fetch_assoc())
			{
				$temp['config']=$temp['config'] ? (array)unserialize($temp['config']) : array();
				if(!Eleanor::$vars['multilang'] and (!$temp['language'] or $temp['language']==Language::$main))
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk]=$tv;
					if(!$temp['language'])
						break;
				}
				elseif(!$temp['language'] and Eleanor::$vars['multilang'])
				{
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][Language::$main]=$tv;
					break;
				}
				elseif(Eleanor::$vars['multilang'] and isset(Eleanor::$langs[$temp['language']]))
					foreach(array_slice($temp,1) as $tk=>$tv)
						$values[$tk][$temp['language']]=$tv;
			}

			if(Eleanor::$vars['multilang'])
			{
				$values['_onelang']=!is_array($values['title']) || count($values['title'])==1 && isset($values['title'][LANGUAGE]);
				foreach(Eleanor::$langs as $k=>&$v)
					if(!isset($values['title'][$k]))
					{
						$values['title'][$k]=$values['text'][$k]='';
						$values['config'][$k]=array();
					}
			}
		}
		$title[]=$lang['editing'];
	}
	else
	{
		$title[]=$lang['adding'];
		$dv=Eleanor::$vars['multilang'] ? array(''=>'') : '';
		$values=array(
			'title'=>$dv,
			'text'=>$dv,
			'ctype'=>'text',
			'file'=>'',
			'user_groups'=>array(),
			'showfrom'=>'',
			'showto'=>'',
			'textfile'=>false,
			'template'=>'',
			'notemplate'=>false,
			'config'=>array(),
			'vars'=>array(),
			'status'=>1,
			'_onelang'=>true,
		);
	}

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-b'.$id.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	if($errors)
	{
		if($errors===true)
			$errors=array();
		$bypost=true;
		if(Eleanor::$vars['multilang'])
		{
			$values['title']=isset($_POST['title']) ? (array)$_POST['title'] : array();
			$values['text']=isset($_POST['text']) ? (array)$_POST['text'] : array();
		}
		else
		{
			$values['title']=isset($_POST['title']) ? (string)$_POST['title'] : '';
			$values['text']=isset($_POST['text']) ? (string)$_POST['text'] : '';
		}
		$values['ctype']=isset($_POST['ctype']) && in_array($_POST['ctype'],array('file','text')) ? (string)$_POST['ctype'] : 'text';
		$values['file']=isset($_POST['file']) ? (string)$_POST['file'] : '';
		$values['user_groups']=isset($_POST['user_groups']) ? (array)$_POST['user_groups'] : array();
		$values['showfrom']=isset($_POST['showfrom']) ? (string)$_POST['showfrom'] : '';
		$values['showto']=isset($_POST['showto']) ? (string)$_POST['showto'] : '';
		$values['template']=isset($_POST['template']) ? (string)$_POST['template'] : '';
		$values['notemplate']=isset($_POST['notemplate']);
		$values['textfile']=isset($_POST['textfile']);
		$values['status']=isset($_POST['status']);
		$values['_onelang']=isset($_POST['_onelang']);

		$values['vars']=array();
		if(isset($_POST['vn'],$_POST['vv']) and is_array($_POST['vn']) and is_array($_POST['vv']))
			foreach($_POST['vn'] as $k=>&$v)
				if(isset($_POST['vv'][$k]))
					$values['vars'][(string)$v]=(string)$_POST['vv'][$k];
	}
	else
		$bypost=false;

	$values['_config']=false;
	if($values['file'] and false!==$p=strrpos($values['file'],'.'))
	{
		$conf=substr_replace($values['file'],'.config',$p,0);
		$conf=Eleanor::FormatPath($values['_config']);
		if(is_file($conf))
		{
			$values['_config']=include$conf;
			if(!is_array($values['_config']))
				$values['_config']=false;
			elseif($bypost)
			{
				foreach($values['_config'] as &$v)
					if(is_array($v))
						$v['bypost']=$bypost;

				$cvals=array();
				foreach($values['config'] as $l=>&$kv)
				{
					foreach($kv as $k=>&$v)
						if(isset($values['_config'][$k]))
						{
							if(!empty($values['_config'][$k]['multilang']))
								$cvals[$k][$l]=array('value'=>$v);
							elseif(!isset($cvals[$k]) or $l==Language::$main)
								$cvals[$k]=array('value'=>$v);
						}
				}
				$Eleanor->Controls->arrname=array('config');
				$values['config']=$Eleanor->Controls->DisplayControls($values['_config'],$cvals);
			}
		}
	}

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('delete'=>$id,'noback'=>1)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('do'=>$id ? false : 'add','edit'=>$id ? $id : false,'nodraft'=>1)) : false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);

	$c=Eleanor::$Template->AddEdit($id,$values,$errors,$bypost,$hasdraft,$Eleanor->Uploader->Show('blocks'),$back,$links);
	Start();
	echo$c;
}

function Save($id)
{global$Eleanor;
	$errors=array();
	$lang=Eleanor::$Language['blocks'];
	if(Eleanor::$vars['multilang'] and !isset($_POST['_onelang']))
	{
		$langs=empty($_POST['lang']) || !is_array($_POST['lang']) ? array() : $_POST['lang'];
		$langs=array_intersect(array_keys(Eleanor::$langs),$langs);
		if(!$langs)
			$langs=array(Language::$main);
	}
	else
		$langs=array('');

	$values=array(
		'ctype'=>isset($_POST['ctype']) && in_array($_POST['ctype'],array('file','text')) ? (string)$_POST['ctype'] : 'text',
		'file'=>isset($_POST['file']) ? (string)$_POST['file'] : '',
		'user_groups'=>isset($_POST['user_groups']) ? (array)$_POST['user_groups'] : array(),
		'showfrom'=>isset($_POST['showfrom']) ? (string)$_POST['showfrom'] : '',
		'showto'=>isset($_POST['showto']) ? (string)$_POST['showto'] : '',
		'template'=>isset($_POST['template']) ? (string)$_POST['template'] : '',
		'notemplate'=>isset($_POST['notemplate']),
		'textfile'=>isset($_POST['textfile']),
		'status'=>isset($_POST['status']),
		'vars'=>array(),
	);

	if($values['file'] and false!==$p=strrpos($values['file'],'.'))
	{
		$conf=substr_replace($values['file'],'.config',$p,0);
		$conf=Eleanor::FormatPath($conf);
		if(is_file($conf))
		{
			$conf=include$conf;
			if(is_array($conf))
			{
				$Eleanor->Controls->arrname=array('config');
				$sc=$Eleanor->Controls->SaveControls($conf);
			}
			else
				$conf=array();
		}
		else
			$conf=array();
	}
	else
		$conf=array();

	if(Eleanor::$vars['multilang'])
	{
		$lvalues=array(
			'title'=>array(),
			'text'=>array(),
			'config'=>array(),
		);
		foreach($langs as $l)
		{
			$lng=$l ? $l : Language::$main;
			$Eleanor->Editor_result->imgalt=$lvalues['title'][$l]=(isset($_POST['title'],$_POST['title'][$lng]) and is_array($_POST['title'])) ? (string)Eleanor::$POST['title'][$lng] : '';
			$lvalues['text'][$l]=isset($_POST['text'],$_POST['text'][$lng]) && is_array($_POST['text']) ? $Eleanor->Editor_result->GetHtml((string)$_POST['text'][$lng],true) : '';
			$lvalues['config'][$l]=array();
			foreach($conf as $k=>&$v)
				if(isset($sc[$k]))
					$lvalues['config'][$l][$k]=empty($v['multilang']) || !isset($sc[$k][$lng]) ? $sc[$k] : $sc[$k][$lng];
		}
	}
	else
	{
		$Eleanor->Editor_result->imgalt=isset($_POST['title']) ? (string)Eleanor::$POST['title'] : '';
		$lvalues=array(
			'title'=>array(''=>$Eleanor->Editor_result->imgalt),
			'text'=>array(''=>$Eleanor->Editor_result->GetHtml('text')),
			'config'=>array(''=>array()),
		);
		foreach($conf as $k=>&$v)
			if(isset($sc[$k]))
				$lvalues['config'][''][$k]=$sc[$k];
	}

	$ml=in_array('',$langs) ? Language::$main : '';
	foreach(array('title') as $field)
		foreach($lvalues[$field] as $k=>&$v)
			if($v=='')
			{
				$er=strtoupper('empty_'.$field.($k ? '_'.$k : ''));
				$errors[$er]=$lang['empty_'.$field]($k);
			}

	if($errors)
		return AddEdit($id,$errors);

	if(isset($_POST['vn'],$_POST['vv']) and is_array($_POST['vn']) and is_array($_POST['vv']))
		foreach($_POST['vn'] as $k=>&$v)
			if($v and isset($_POST['vv'][$k]))
				$values['vars'][$v]=(string)$_POST['vv'][$k];
	$values['vars']=$values['vars'] ? serialize($values['vars']) : '';
	$values['user_groups']=$values['user_groups'] ? ','.join(',,',$values['user_groups']).',' : '';
	Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-b'.$id.'\' LIMIT 1');
	if($id)
	{
		Eleanor::$Db->Update(P.'blocks',$values,'`id`='.$id.' LIMIT 1');
		Eleanor::$Db->Delete(P.'blocks_l','`id`='.$id.' AND `language`'.Eleanor::$Db->In($langs,true));
		$replace=array();
		foreach($langs as &$v)
			$replace[]=array(
				'id'=>$id,
				'language'=>$v,
				'title'=>$lvalues['title'][$v],
				'text'=>$lvalues['text'][$v],
				'config'=>$lvalues['config'][$v] ? serialize($lvalues['config'][$v]) : '',
			);
		Eleanor::$Db->Replace(P.'blocks_l',$replace);
	}
	else
	{
		$id=Eleanor::$Db->Insert(P.'blocks',$values);
		$values=array('id'=>array(),'language'=>array(),'title'=>array(),'text'=>array());
		foreach($langs as &$v)
		{
			$values['id'][]=$id;
			$values['language'][]=$v;
			$values['title'][]=$lvalues['title'][$v];
			$values['text'][]=$lvalues['text'][$v];
			$values['config'][]=$lvalues['config'][$v] ? serialize($lvalues['config'][$v]) : '';
		}
		Eleanor::$Db->Insert(P.'blocks_l',$values);
	}
	Eleanor::$Cache->Obsolete('blocks');
	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}

function AddEditId($id,$errors=array())
{global$Eleanor,$title;
	$lang=Eleanor::$Language['blocks'];
	if($id)
	{
		if(!$errors)
		{
			$R=Eleanor::$Db->Query('SELECT * FROM `'.P.'blocks_ids` WHERE `id`='.$id.' LIMIT 1');
			if(!$values=$R->fetch_assoc())
				return GoAway();
			$values['title']=$values['title_l'] ? (array)unserialize($values['title_l']) : array();
		}
		$title[]=$lang['editingi'];
	}
	else
	{
		$title[]=$lang['addingi'];
		$values=array(
			'service'=>'user',
			'title'=>array(''=>''),
			'code'=>'',
		);
	}

	$hasdraft=false;
	if(!$errors and !isset($_GET['nodraft']))
	{
		$R=Eleanor::$Db->Query('SELECT `value` FROM `'.P.'drafts` WHERE `key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-i'.$id.'\' LIMIT 1');
		if($draft=$R->fetch_row() and $draft[0])
		{
			$hasdraft=true;
			$_POST+=(array)unserialize($draft[0]);
			$errors=true;
		}
	}

	if($errors)
	{
		if($errors===true)
			$errors=array();
		if(Eleanor::$vars['multilang'])
			$values['title']=isset($_POST['title']) ? (array)$_POST['title'] : array();
		else
			$values['title']=isset($_POST['title']) ? array(''=>(string)$_POST['title']) : array(''=>'');
		$values['service']=isset($_POST['service']) ? (string)$_POST['service'] : '';
		$values['code']=isset($_POST['code']) ? (string)$_POST['code'] : '';
		$bypost=true;
	}
	else
		$bypost=false;

	if(isset($_GET['noback']))
		$back='';
	else
		$back=isset($_POST['back']) ? (string)$_POST['back'] : getenv('HTTP_REFERER');

	$links=array(
		'delete'=>$id ? $Eleanor->Url->Construct(array('deletei'=>$id,'noback'=>1)) : false,
		'nodraft'=>$hasdraft ? $Eleanor->Url->Construct(array('do'=>$id ? false : 'addi','editi'=>$id ? $id : false,'nodraft'=>1)) : false,
		'draft'=>$Eleanor->Url->Construct(array('do'=>'draft')),
	);

	$Eleanor->Editor->type='codemirror';
	$Eleanor->Editor->ownbb=$Eleanor->Editor->smiles=false;
	$c=Eleanor::$Template->AddEditId($id,$values,$errors,$bypost,$hasdraft,$back,$links);
	Start();
	echo$c;
}

function SaveId($id)
{global$Eleanor;
	$Eleanor->Editor_result->type='codemirror';
	$Eleanor->Editor_result->ownbb=$Eleanor->Editor_result->smiles=false;
	$values=array(
		'service'=>isset($_POST['service']) ? (string)$_POST['service'] : '',
		'code'=>isset($_POST['code']) ? (string)$_POST['code'] : '',
	);
	if(!isset(Eleanor::$services[$values['service']]))
		return AddEditId($id,true);

	if(Eleanor::$vars['multilang'])
		$values['title_l']=isset($_POST['title']) ? (array)Eleanor::$POST['title'] : array();
	else
		$values['title_l']=isset($_POST['title']) ? array(''=>(string)Eleanor::$POST['title']) : array();

	$lang=Eleanor::$Language['blocks'];
	$errors=array();
	foreach($values['title_l'] as $k=>&$v)
		if($v=='')
		{			$er=$k ? strtoupper('_'.$k) : '';
			$errors['NOTITLE'.$er]=$lang['notitle']($k);
		}

	ob_start();
	if(create_function('',$values['code'])===false)
	{
		$err=ob_get_contents();
		ob_end_clean();
		$Eleanor->e_g_l=error_get_last();
		$errors['ERROR_CODE']=sprintf($lang['errcode'],$err);
	}
	ob_end_clean();

	if($errors)
		return AddEditId($id,$errors);

	Eleanor::$Db->Delete(P.'drafts','`key`=\'_blocks-'.Eleanor::$Login->GetUserValue('id').'-i'.$id.'\' LIMIT 1');
	$values['title_l']=serialize($values['title_l']);
	if($id)
		Eleanor::$Db->Update(P.'blocks_ids',$values,'`id`='.$id.' LIMIT 1');
	else
		Eleanor::$Db->Insert(P.'blocks_ids',$values);
	Eleanor::$Cache->Obsolete('blocks');

	GoAway(empty($_POST['back']) ? true : $_POST['back']);
}