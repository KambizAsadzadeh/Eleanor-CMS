<?php
/**
	Eleanor CMS © 2015
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

$t=time();
$groups=GetGroups();
$table=P.'blocks';
$table_l=P.'blocks_l';
$language=Language::$main;
$id=isset($_GET['block_id']) ? (int)$_GET['block_id'] : 0;

$R=Eleanor::$Db->Query("SELECT `id`, `file`, `user_groups`, `showfrom`, `showto`,
`vars`, `config` FROM `{$table}` INNER JOIN `{$table_l}` USING(`id`) WHERE `id`={$id} AND `language`IN('','{$language}') AND `status`IN(1,-3) AND `type`='file' AND `textfile`=0");
if($block=$R->fetch_assoc())
{
	if($block['user_groups'] and !array_intersect(explode(',,',trim($block['user_groups'],',')),$groups))
		goto Error;

	if((int)$block['showfrom'] and $t<strtotime($block['showfrom']))
		goto Error;

	if((int)$block['showto'] and $t>=strtotime($block['showto']))
	{
		Eleanor::$Db->Update(P.'blocks',['status'=>-2],'`id`='.$block['id'].' LIMIT 1');
		goto Error;
	}

	$f=DIR.$block['file'];

	if(!$block['file'] or !is_file($f))
		goto Error;

	$vars=$block['vars'] ? (array)json_decode($block['vars'],true) : [];

	if($block['config'])
		$vars['CONFIG']=$block['config'] ? (array)json_decode($block['config'],true) : [];

	$vars['REQUEST']=\Eleanor\SITEDIR.basename($_SERVER['SCRIPT_FILENAME']).'?'.Url::Query([
			'direct'=>'blocks',
			'block_id'=>$block['id']
		]);

	\Eleanor\AwareInclude(DIR.$block['file'],$vars);

	return;
}

Error:
Output::SendHeaders('application/json');
Output::Gzip(json_encode(['status'=>'error'],JSON^JSON_PRETTY_PRINT));
