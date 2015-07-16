<?php
/*
	Элемент шаблона: блок логина пользователя. Вынесено в отдельный файл, дабы во-первых не засорять блоки, а во-вторых предоставить дизайнеру
	возможность разместить этот блок в самом логичном по его мнению месте
*/
defined('CMS\STARTED')||die;
$ma=array_keys($Eleanor->modules['sections'],'account');
$ma=reset($ma);
if(Eleanor::$Login->IsUser()):
	$user=Eleanor::$Login->Get(array('name','avatar_type','avatar'));
	switch($user['avatar'] ? $user['avatar_type'] : '')
	{
		case'gallery':
			$avatar='images/avatars/'.$user['avatar'];
		break;
		case'upload':
			$avatar=Eleanor::$uploads.'/avatars/'.$user['avatar'];
		break;
		case'link':
			$avatar=$user['avatar'];
		break;
		default:
			$avatar=$theme.'images/avatar-no.png';
	}
?>
			<div class="loginbox lg-enter">
				<div id="lg-menu">
					<a class="lg-user" href="#">
						<b class="ava-box"><img title="<?=$user['name']?>" alt="<?=$user['name']?>" src="<?=$avatar?>" /></b>
						<span class="lg-uname"><?=$user['name']?></span>
					</a>
					<ul class="lg-menu">
						<li><a href="<?php echo Eleanor::$vars['link_options']?>"><?=$ltpl['myacc']?></a></li>
						<li><a href="<?=$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'avatar'),false,'')?>"><?=$ltpl['chav']?></a></li>
						<?php if(Eleanor::$Permissions->IsAdmin()):?><li><a href="<?=Eleanor::$services['admin']['file']?>"><?=$ltpl['adminka']?></a></li><?php endif; ?>
<?php if($GLOBALS['Eleanor']->multisite):
echo '<li>',Eleanor::Select(false,Eleanor::Option($ltpl['msjump'],'',true),array('id'=>'msjump','onchange'=>'CORE.Jump($(this).val())')),'</li>'?>
<script>
$(function(){
	$.each(CORE.sites,function(k,v){
		$("<option>").text(v.title).val(k).appendTo("#msjump");
	})
})</script><?php endif?>

					</ul>
				</div>
				<a class="thd lg-exit" href="<?=$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'logout'),false,'')?>" title="<?=$ltpl['exit']?>"><?=$ltpl['exit']?></a>
			</div>
<?php else:?>
			<div class="loginbox">
				<a id="loginbtn" class="bk-btn" href="#"><?=$ltpl['enter']?></a>
				<p><?=$ltpl['yllg']?></p>
				<div id="logindialog" title="<?=$ltpl['auth']?>" style="display:none;">
					<form method="post" action="<?=$Eleanor->Url->special.$Eleanor->Url->Construct(array('module'=>$ma,'do'=>'login'),false,'')?>">
						<div class="logform">
							<div class="lfield"><input placeholder="<?=$ltpl['el']?>" type="text" name="login[name]" /></div>
							<div class="lfield"><input placeholder="<?=$ltpl['ep']?>" type="password" name="login[password]" /></div>
							<div class="lsubm clrfix">
								<label><input type="checkbox" name="login[rememberme]" value="0" /> <?=$ltpl['remember']?></label>
							</div>
							<div class="lsubm clrfix">
								<button class="wh-btn" type="submit"><?=$ltpl['enter']?></button>
								<p class="regline"><a href="<?=Eleanor::$vars['link_register']?>"><?=$ltpl['reg']?></a> | <a href="<?=Eleanor::$vars['link_lost_pass']?>"><?=$ltpl['lostp']?></a></p>
							</div>
							<div class="soc-login"><?php include Eleanor::$root.$theme.'Static/external_auth.php';
							if($GLOBALS['Eleanor']->multisite):?>
								<script>
								CORE.MultiSite.done(function(qw){
									var al=$(".soc-login").append("<br />");
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
											$(this).append(" ").append(a);
											a=a.clone(true);
										});
									})
								})</script>
							<?php endif?>
							</div>
						</div>
					</form>
				</div>
			</div>
<?php endif?>