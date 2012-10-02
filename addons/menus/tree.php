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
$parent=isset($parent) ? (int)$parent : false;
$exclude=isset($exclude) ? (int)$exclude : 0;
$menu=Eleanor::$Cache->Get('menu_tree_'.Language::$main.$parent);
if($menu===false)
{
	$p='';
	if($parent)
	{
		$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.P.'menu` WHERE `id`='.$parent.' AND `status`=1 LIMIT 1');
		if(!list($p)=$R->fetch_row())
			return'';
		$p.=$parent.',';
	}

	$maxlen=0;
	$menu=$to1sort=$to2sort=$db=$excl=array();
	$R=Eleanor::$Db->Query('SELECT `id`,`title`,`url`,`eval_url`,`params`,`parents`,`pos`,`status` FROM `'.P.'menu` LEFT JOIN `'.P.'menu_l` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\')'.($p ? ' AND `parents` LIKE \''.$p.'%\'' : '').' ORDER BY `parents` ASC, `pos` ASC');
	while($a=$R->fetch_assoc())
	{
		foreach($excl as $v)
			if(strpos($a['parents'],$v)===0)
				continue;
		if($a['id']==$exclude or !$a['status'])
		{
			$excl[]=$a['parents'].$a['id'].',';
			continue;
		}

		if($a['eval_url'])
		{
			ob_start();
			$f=create_function('$Eleanor',$a['eval_url']);
			if($f===false)
			{
				ob_end_clean();
				continue;
			}
			ob_end_clean();
			$a['url']=$f($GLOBALS['Eleanor']);
		}
		$a['parents']=str_replace($p,'',$a['parents']);
		if($a['parents'])
		{
			$cnt=substr_count($a['parents'],',');
			$to1sort[$a['id']]=$cnt;
			$maxlen=max($cnt,$maxlen);
		}
		$db[$a['id']]=$a;
		$to2sort[$a['id']]=$a['pos'];
	}
	asort($to1sort,SORT_NUMERIC);

	foreach($to1sort as $k=>&$v)
		if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0 and $p[1]!=$parent)
			if(isset($to2sort[$p[1]]))
				$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
			else
				unset($to1sort[$k],$db[$k],$to2sort[$k]);

	foreach($to2sort as $k=>&$v)
		$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

	natsort($to2sort);
	foreach($to2sort as $k=>&$v)
		$menu[(int)$db[$k]['id']]=$db[$k];

	if(!class_exists('ApiMenu',false))
		include Eleanor::$root.'modules/menu/api.php';

	$u=uniqid();
	$menu=ApiMenu::BuildMultilineMenu($menu,'<nav><ul class="blockcategories" id="q'.$u.'">').'</nav><script type="text/javascript">//<![CDATA[
$(function(){
	$("#q'.$u.' li:has(ul)").addClass("subcat").each(function(i){
		var img=$("<img>").css({cursor:"pointer","margin-right":"3px"}).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"+"}).prependTo(this).click(function(){
			if(localStorage.getItem("bm"+i))
			{
				localStorage.removeItem("bm"+i);
			}
			else
			{
				$(this).prop({src:"'.Eleanor::$Template->default['theme'].'images/minus.gif",title:"-"}).next().next().show();
				try
				{
					localStorage.setItem("bm"+i,"1");
				}catch(e){}
			}
		});
		if(!localStorage.getItem("bm"+i))
			img.prop({src:"'.Eleanor::$Template->default['theme'].'images/plus.gif",title:"+"}).next().next().hide();
	}).find("ul").css("margin-left","4px");
});//]]></script>';

	Eleanor::$Cache->Put('menu_tree_'.Language::$main.$parent,$menu);
}
return$menu;