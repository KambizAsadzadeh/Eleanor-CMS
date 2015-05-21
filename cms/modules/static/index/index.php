<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use\Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

/** @var array $config */

global$Eleanor,$title;

$lang=Eleanor::$Language->Load(__DIR__.'/index-*.php',$config['n']);;
$uid=Eleanor::$Login->Get('id');
$Eleanor->module['links']=[
	'rss'=>Eleanor::$services['rss']['file'].'?'.Url::Query([
		'lang'=>Eleanor::$vars['multilang'] ? Language::$main : null, 'module'=>$Eleanor->module['uri']
	]),
];

if(!isset($Eleanor->module['etag']))
	$Eleanor->module['etag']='';

include_once __DIR__.'/../api.php';
$Api=new ApiStatic($config);

if(isset($Eleanor->module['general']))
	if(Eleanor::$vars[$config['pv'].'general'])
	{
		$ids=explode(',',Eleanor::$vars[$config['pv'].'general']);
		$res=$temp=[];
		$modified=0;

		$R=Eleanor::$Db->Query('SELECT `id`, `title`, `text`, UNIX_TIMESTAMP(`last_mod`) `modified` FROM `'.$config['t']
			.'` INNER JOIN `'.$config['tl'].'` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids)
			.' AND `status`=1 AND `language`IN(\'\',\''.Language::$main.'\')');
		while($item=$R->fetch_assoc())
		{
			if($item['modified']>$modified)
				$modified=$item['modified'];

			$temp[$item['id']]=$item;
		}

		foreach($ids as &$v)
			if(isset($temp[$v]))
				$res[]=$temp[$v];

		unset($temp,$ids);

		$etag=md5($uid.join(',',array_keys($item)));

		if(!Output::TryReturnCache($etag,$modified))
			Response(Eleanor::$Template->StaticGeneral($res),[
				'max-age'=>0,
				'etag'=>$etag,
				'modified'=>$modified,
			]);
	}
	else
	{
		Substance:

		$title[]=$lang['substance'];
		$substance=$Api->GetSubstance();

		foreach($substance as $k=>&$v)
			$v['_a']=$Api->GetUrl($k,$Eleanor->Url);

		if(isset($Eleanor->module['general']))
			$Eleanor->module['origurl']=rtrim($Eleanor->Url->prefix,'/');

		$etag=md5($uid.join(',',array_keys($substance)));

		if(!Output::TryReturnCache($etag))
			Response(Eleanor::$Template->StaticSubstance($substance),[
				'max-age'=>0,
				'etag'=>$etag,
			]);
	}
else
{
	$id=isset($_GET['id']) ? (int)$_GET['id'] : 0;

	if($Eleanor->Url->parts or $id)
	{
		$parents='';
		$data=[];

		if($id>0)
		{
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$config['t'].'` WHERE `status`=1 AND `id`='.$id.' LIMIT 1');
			if(!list($parents)=$R->fetch_row())
				return ExitPage();

			$parents.=$id.',';
		}

		if($Eleanor->Url->parts)
		{
			$item=false;

			if(!$id)
			{
				$local=preg_replace('#([^a-z0-9\.\-_/]|\.\.)+#i','',
					join('/',$Eleanor->Url->parts));#Обезопасим от возможного выхода из каталога и проверку других файлов.
				$item=glob($Eleanor->module['path'].'DIRECT/'.$local.'.php',GLOB_BRACE);

				if($item)
				{
					ob_start();
					$data=\Eleanor\AwareInclude($item[0]);
					$text=ob_get_contents();
					ob_end_clean();

					if(!is_array($data))
						$data=[];

					$data+=[
						'text'=>$text,
						'title'=>'',
						'navi'=>[],
						'seealso'=>[],
						'last_mod'=>filemtime($item[0]),
						'document_title'=>false,
						'meta_descr'=>false,
					];

					if(Output::TryReturnCache(md5($item),$data['last_mod']))
						return 1;

					$Eleanor->module['origurl']=$Eleanor->Url($local);
					$id=$local;
				}
			}

			if(!$item)
			{
				$R=Eleanor::$Db->Query('SELECT `id`, `parents`, `uri` FROM `'.$config['t'].'` INNER JOIN `'
					.$config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `uri`'
					.Eleanor::$Db->In($Eleanor->Url->parts).' AND `status`=1 ORDER BY `parents` ASC');
				if($R->num_rows>0)
				{
					$parents='';
					$uri=reset($Eleanor->Url->parts);

					while($a=$R->fetch_assoc())
						if($parents==$a['parents'] and mb_strtolower($uri)==mb_strtolower($a['uri']))
						{
							$id=$a['id'];
							$parents.=$a['id'].',';

							$uri=next($Eleanor->Url->parts);
						}
						else
							return ExitPage();
				}
			}
		}

		if(!$id and !$data)
			return ExitPage();

		if($id and !$data)
		{
			$R=Eleanor::$Db->Query('SELECT `title`, `text`, `parents`, `document_title`, `meta_descr`, `last_mod` FROM `'
				.$config['t'].'` INNER JOIN `'.$config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''
				.Language::$main.'\') AND `status`=1 AND `id`='.$id.' LIMIT 1');
			if(!$data=$R->fetch_assoc())
				return ExitPage();

			if(Output::TryReturnCache('',$data['last_mod']))
				return 1;

			$data['navi'][]=[$lang['substance'],Eleanor::$vars['prefix_free_module']!=$Eleanor->module['id'] ||
				Eleanor::$vars[$config['pv'].'general'] ? $Eleanor->Url([ $Eleanor->module['uri'] ]) : false];

			$Eleanor->module['origurl']=$Api->GetUrl($id,$Eleanor->Url);

			OwnBB::$opts['alt']=$data['title'];

			if($data['parents'])
			{
				$pids=explode(',',$data['parents']);
				$items=[];

				$R=Eleanor::$Db->Query('SELECT `id`,`title` FROM `'.$config['t'].'` INNER JOIN `'.$config['tl']
					.'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `id`'
					.Eleanor::$Db->In($pids));
				while($item=$R->fetch_assoc())
					$items[ $item['id'] ]=$item['title'];

				foreach($pids as $v)
					if(isset($items[$v]))
						$data['navi'][]=[$items[$v],$Api->GetUrl($v,$Eleanor->Url)];
			}

			$data['navi'][]=[$data['title'],false];
			$data['seealso']=[];

			$R=Eleanor::$Db->Query('SELECT `id`, `title` FROM `'.$config['t'].'` INNER JOIN `'.$config['tl']
				.'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `status`=1 AND `parents`=\''
				.$parents.'\' ORDER BY `pos` ASC');
			while($item=$R->fetch_assoc())
				$data['seealso'][]=[$item['title'],$Api->GetUrl($item['id'],$Eleanor->Url)];
		}

		if($data['document_title'])
			$title=$data['document_title'];
		else
			$title[]=$data['title'];

		$Eleanor->module['description']=$data['meta_descr']
			? $data['meta_descr']
			: \Eleanor\Classes\Strings::CutStr(strip_tags(str_replace("\n",' ',$data['text'])),250);

		$etag=md5($Eleanor->module['etag'].$uid);

		if(!Output::TryReturnCache($etag))
			Response(Eleanor::$Template->StaticShow($id,$data),[
				'max-age'=>0,
				'etag'=>$etag,
				'modified'=>$data['last_mod'],
			]);
	}
	else
		goto Substance;
}