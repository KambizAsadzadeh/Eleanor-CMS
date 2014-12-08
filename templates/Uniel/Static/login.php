<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use \Eleanor\Classes\Html, CMS\Templates\Uniel\T;

/* Элемент шаблона: блок логина пользователя. Вынесено в отдельный файл, дабы во-первых не засорять блоки, а во-вторых
предоставить дизайнеру возможность разместить этот блок в самом логичном по его мнению месте */

defined('CMS\STARTED')||die;

$ma=array_keys($GLOBALS['Eleanor']->modules['uri2section'],'account');
$Url=new Url(false);
$Url->prefix.=Url::$base.Url::Encode(reset($ma)).'/';

if(Eleanor::$Login->IsUser()):
	$user=Eleanor::$Login->Get(['name','avatar_type','avatar_location']);
	switch($user['avatar_location'] ? $user['avatar_type'] : '')
	{
		case'local':
			$avatar=Template::$http['static'].'images/avatars/'.$user['avatar_location'];
		break;
		case'upload':
			$avatar=Template::$http['uploads'].'avatars/'.$user['avatar_location'];
		break;
		case'url':
			$avatar=$user['avatar_location'];
		break;
		default:
			$avatar=Template::$http['static'].'images/avatars/user.png';
	}
?>
<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont"><?php if($avatar):?>
	<a href="<?=Eleanor::$vars['link_options']?>">
		<img style="float:left;margin-right:10px;width:40px;" src="<?=$avatar?>" alt="<?=$user['name']?>" />
	</a><?php endif ?>
	<h5 style="padding-top: 4px;">
		<?=sprintf(T::$lang['hello'],'<a href="',Eleanor::$vars['link_options'],'">',$user['name'],'</a>')?>
	</h5>
	<div><?php if(Eleanor::$Permissions->IsAdmin()):?>
		<a href="<?=Eleanor::$services['admin']['file']?>"><?=T::$lang['adminka']?></a> | <?php endif
		?><a href="<?=$Url('logout')?>"><?=T::$lang['exit']?></a>

<?php if(Eleanor::$Template->multisite):
	echo Html::Select(false,Html::Option(T::$lang['msjump'],'',true),
		['id'=>'msjump','style'=>'width:100%','onchange'=>'CORE.Jump($(this).val())'])?>
<script>//<![CDATA[
$(function(){
	$.each(CORE.sites,function(k,v){
		$("<option>").text(v.title).val(k).appendTo("#msjump");
	})
})//]]></script><?php endif?>
	</div>
	<div class="clr"></div>
	</div>
</div></div></div>
<?php else:?>
<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont">
		<form action="<?=$Url('login')?>" method="post">
			<div class="logintext">
				<label for="block-name"><?=T::$lang['login']?></label>
				<div><div><input type="text" name="login[name]" tabindex="1" id="block-name" /></div></div>
			</div>
			<div class="logintext">
				<label for="block-password"><?=T::$lang['pass']?></label>
				<div><div><input type="password" name="login[password]" tabindex="2" id="block-password" /></div></div>
			</div>
			<div style="text-align:center">
				<div style="padding-bottom: 6px;">
					<input value="<?=T::$lang['enter']?>" class="enterbtn" type="submit" tabindex="3" />
				</div>
				<a href="<?=Eleanor::$vars['link_register']?>"><?=T::$lang['register']?></a> |
				<a href="<?=Eleanor::$vars['link_lost_pass']?>"><?=T::$lang['lostpass']?></a>
				<hr /><?php include __DIR__.'/external_auth.php'?>
			</div>
		</form>
	</div>
</div></div></div>
<?php if(Eleanor::$Template->multisite):?>
<script>//<![CDATA[
CORE.MultiSite.done(function(qw){
	var al=$(".externals");
	$.each(qw,function(k,v){
		var a=$("<a>").prop({
			href:"#",
			title:v.name,
			style:"font-weight:bold"
		}).text(v.title).click(function(){
			CORE.Login(k);
			return false;
		});
		al.each(function(){
			$(this).append("<br />").append(a);
			a=a.clone(true);
		});
	})
});
//]]></script>
<?php endif;endif;?>