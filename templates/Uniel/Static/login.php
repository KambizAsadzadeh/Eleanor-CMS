<?php
/*
	������� �������: ���� ������ ������������. �������� � ��������� ����, ���� ��-������ �� �������� �����, � ��-������ ������������ ���������
	����������� ���������� ���� ���� � ����� �������� �� ��� ������ �����
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
global$Eleanor;
$ma=array_keys($Eleanor->modules['sections'],'account');
$ma=reset($ma);
if(Eleanor::$Login->IsUser()):
	switch(Eleanor::$Login->user['avatar_type'] && Eleanor::$Login->user['avatar_location'] ? Eleanor::$Login->user['avatar_type'] : '')
	{
		case'local':
			$avatar='images/avatars/'.Eleanor::$Login->user['avatar_location'];
		break;
		case'upload':
			$avatar=Eleanor::$uploads.'/avatars/'.Eleanor::$Login->user['avatar_location'];
		break;
		case'url':
			$avatar=Eleanor::$Login->user['avatar_location'];
		break;
		default:
			$avatar=Eleanor::$vars['noavatar'];
	}
?>
<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont">
	<?php if($avatar):?><a href="<?php echo Eleanor::$vars['link_options']?>"><img style="float:left;margin-right:10px;width:40px;" src="<?php echo$avatar?>" alt="<?php echo Eleanor::$Login->GetUserValue('name')?>" /></a><?php endif?>
	<h5 style="padding-top: 4px;"><?php echo$ltpl['hello']?><a href="<?php echo Eleanor::$vars['link_options']?>"><?php echo Eleanor::$Login->GetUserValue('name')?></a>!</h5>
	<div><?php if(Eleanor::$Permissions->IsAdmin()):?><a href="<?php echo Eleanor::$services['admin']['file']?>"><?php echo$ltpl['adminka']?></a> | <?php endif; ?><a href="<?php echo$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'logout'),false,'')?>"><?php echo$ltpl['exit']?></a>
<?php if($GLOBALS['Eleanor']->multisite):
echo Eleanor::Select(false,Eleanor::Option($ltpl['msjump'],'',true),array('id'=>'msjump','style'=>'width:100%','onchange'=>'CORE.MSJump($(this).val())'))?>
<script type="text/javascript">//<![CDATA[
$(function(){
	$.each(CORE.mssites,function(k,v){
		$("<option>").text(v.title).val(k).appendTo("#msjump");
	})
})//]]></script><?php endif?>
	</div>
	<div class="clr"></div>
	</div>
</div></div></div>
<?php else: ?>

<div class="blocklogin"><div class="dbottom"><div class="dtop">
	<div class="dcont">
		<form action="<?php echo$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'login'),false,'')?>" method="post">
			<div class="logintext">
				<span><?php echo$ltpl['login']?></span>
				<div><div><input type="text" name="login[name]" tabindex="1" /></div></div>
			</div>
			<div class="logintext">
				<span><?php echo$ltpl['pass']?></span>
				<div><div><input type="password" name="login[password]" tabindex="2" /></div></div>
			</div>
			<div style="text-align:center">
				<div style="padding-bottom: 6px;"><input value="<?php echo$ltpl['enter']?>" class="enterbtn" type="submit" tabindex="3" /></div>
				<a href="<?php echo Eleanor::$vars['link_register']?>"><?php echo$ltpl['register']?></a> | <a href="<?php echo Eleanor::$vars['link_passlost']?>"><?php echo$ltpl['lostpass']?></a>
<hr /><?php include Eleanor::$root.$theme.'Static/external_auth.php'?>
			</div>
		</form>
	</div>
</div></div></div>
<?php if($GLOBALS['Eleanor']->multisite):?>
<script type="text/javascript">//<![CDATA[
CORE.MSQueue.done(function(qw){
			title:v.name,
			style:"font-weight:bold"
			return false;
			a=a.clone(true);
//]]></script>
<?php endif;endif;?>