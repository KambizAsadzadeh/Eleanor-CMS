<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/

class AccountSettings
{
	public static function Menu()
	{
		return array(
			'main'=>$GLOBALS['Eleanor']->Url->Construct(array('do'=>'settings'),true,''),
		);
	}

	public static function Content($master=true)
	{
		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		Eleanor::LoadOptions('user-profile',false);

		#Themes
		$themes=array(''=>$lang['by_default']);
		if(Eleanor::$vars['templates'] and is_array(Eleanor::$vars['templates']))
			foreach(Eleanor::$vars['templates'] as &$v)
			{
				$f=Eleanor::$root.'templates/'.$v.'.settings.php';
				if(!file_exists($f))
					continue;
				$a=include($f);
				$name=is_array($a) && isset($a['name']) ? $a['name'] : $v;
				$themes[$v]=$name;
			}
		#[E] Themes

		$post=false;
		$avatar=array(
			'id'=>Eleanor::$Login->Get('id'),
			'type'=>'uploadimage',
			'name'=>'a',
			'default'=>'',
			'post'=>&$post,
			'options'=>array(
				'types'=>array('png','jpeg','jpg','bmp','gif'),
				'path'=>Eleanor::$uploads.'/avatars/',
				'max_size'=>Eleanor::$vars['avatar_bytes'],
				'max_image_size'=>Eleanor::$vars['avatar_size'],
				'filename'=>function($a)
				{
					return isset($a['id']) ? 'av-'.$a['id'].strrchr($a['filename'],'.') : $a['filename'];
				},
				'deleted'=>false,
			),
		);
		$controls=array(
			$lang['siteopts'],
			'full_name'=>array(
				'title'=>$lang['full_name'],
				'descr'=>'',
				'type'=>'input',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'language'=>Eleanor::$vars['multilang'] ? array(
				'title'=>$lang['lang'],
				'descr'=>'',
				'type'=>'select',
				'post'=>&$post,
				'options'=>array(
					'callback'=>function() use ($lang)
					{
						$a=array(''=>$lang['by_default']);
						foreach(Eleanor::$langs as $k=>&$v)
							$a[$k]=$v['name'];
						return$a;
					},
				),
			) : false,
			'theme'=>count($themes)>2 ? array(
				'title'=>$lang['theme'],
				'descr'=>'',
				'type'=>'select',
				'post'=>&$post,
				'options'=>array(
					'options'=>$themes,
				),
			) : false,
			'editor'=>array(
				'title'=>$lang['editor'],
				'descr'=>'',
				'type'=>'select',
				'post'=>&$post,
				'options'=>array(
					'callback'=>function() use ($lang)
					{
						return array(''=>$lang['by_default'])+$GLOBALS['Eleanor']->Editor->editors;
					},
				),
			),
			'timezone'=>array(
				'title'=>$lang['timezone'],
				'descr'=>'',
				'type'=>'select',
				'post'=>&$post,
				'options'=>array(
					'callback'=>function($a) use ($lang)
					{
						return Eleanor::Option($lang['by_default'],'',in_array('',$a['value'],'')).Types::TimeZonesOptions($a['value']);
					},
				),
			),
			$lang['personal'],
			'gender'=>array(
				'title'=>$lang['gender'],
				'descr'=>'',
				'type'=>'select',
				'post'=>&$post,
				'options'=>array(
					'options'=>array(-1=>$lang['nogender'],$lang['female'],$lang['male']),
				),
			),
			'bio'=>array(
				'title'=>$lang['bio'],
				'descr'=>'',
				'type'=>'text',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'interests'=>array(
				'title'=>$lang['interests'],
				'descr'=>'',
				'type'=>'text',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'location'=>array(
				'title'=>$lang['location'],
				'descr'=>$lang['location_'],
				'type'=>'input',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'site'=>array(
				'title'=>$lang['site'],
				'descr'=>$lang['site_'],
				'type'=>'input',
				'save'=>function($a,$Obj)
				{
					if($a['value'] and !filter_var($a['value'],FILTER_VALIDATE_URL))
						$Obj->errors[]='SITE_ERROR';
					else
						return$a['value'];
				},
				'post'=>&$post,
				'options'=>array(
					'type'=>'url',
					'safe'=>false,
				),
			),
			'signature'=>array(
				'title'=>$lang['signature'],
				'descr'=>'',
				'type'=>'editor',
				'post'=>&$post,
			),
			$lang['connect'],
			'jabber'=>array(
				'title'=>'Jabber',
				'descr'=>'',
				'type'=>'input',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'skype'=>array(
				'title'=>'Skype',
				'descr'=>'',
				'type'=>'input',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'icq'=>array(
				'title'=>'ICQ',
				'descr'=>'',
				'type'=>'input',
				'save'=>function($a,$Obj)
				{
					$v=preg_replace('#[^0-9]+#','',$a['value']);
					if($v and !isset($v[4]))
						$Obj->errors[]='SHORT_ICQ';
					return$v;
				},
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'vk'=>array(
				'title'=>$lang['vk'],
				'descr'=>$lang['vk_'],
				'type'=>'input',
				'save'=>array(__class__,'SaveVK'),
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'facebook'=>array(
				'title'=>'Facebook',
				'descr'=>'',
				'type'=>'input',
				'save'=>array(__class__,'SaveVK'),
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
			'twitter'=>array(
				'title'=>'Twitter',
				'descr'=>$lang['twitter_'],
				'type'=>'input',
				'post'=>&$post,
				'options'=>array(
					'safe'=>true,
				),
			),
		);
		$saved=false;
		if($master and $_SERVER['REQUEST_METHOD']=='POST' and Eleanor::$our_query)
		{
			$C=new Controls;
			$C->arrname=array();
			$C->throw=false;
			$values=$C->SaveControls($controls);

			$C->arrname=array('avatar');
			$oldavatar=Eleanor::$Login->Get(array('avatar','avatar_type'),false);
			$atype=isset($_POST['_atype']) ? $_POST['_atype'] : false;

			if($atype=='upload')
				$av=$C->SaveControl($avatar+array('value'=>$oldavatar['avatar_type']=='upload' && $oldavatar['avatar'] ? Eleanor::$uploads.'/avatars/'.$oldavatar['avatar'] : ''));
			else
				$av=isset($_POST['avatar']) ? (string)$_POST['avatar'] : '';

			if($atype=='upload' and $av)
				$atype=strpos($av,'://')===false ? 'upload' : 'url';
			else
				$atype=$av ? 'gallery' : '';

			if(($atype=='upload' or $atype=='gallery') and $av and !is_file(Eleanor::$root.$av))
				$C->errors[]='AVATAR_NOT_EXISTS';

			if($atype=='gallery' and $av)
				$av=preg_replace('#^images/avatars/#','',$av);

			if($C->errors)
			{
				$post=true;
				return static::Edit($controls,$avatar,$C->errors);
			}

			UserManager::Update($values);

			if($atype=='upload')
				$av=basename($av);
			if($oldavatar['avatar']!=$av or $oldavatar['avatar_type']!=$atype)
			{
				if($oldavatar['avatar_type']=='upload' and $oldavatar['avatar'] and $oldavatar['avatar']!=$av)
					Files::Delete(Eleanor::$root.Eleanor::$uploads.'/avatars/'.$oldavatar['avatar']);
				UserManager::Update(array('avatar'=>$av,'avatar_type'=>$atype));
				Eleanor::$Login->SetUserValue(array(
					'avatar'=>$av,
					'avatar_type'=>$atype,
				));
			}
			$saved=true;
		}
		return static::Edit($controls,$avatar,array(),$saved);
	}

	public static function SaveVK($a)
	{
		return preg_replace('#[^a-z0-9_\.-]+/#','',$a['value']);
	}

	protected static function Edit($controls,$avatar,$errors=array(),$saved=false)
	{
		$names=array('avatar_type','avatar');
		foreach($controls as $k=>&$control)
			if(is_array($control))
				$names[]=$k;

		$values=Eleanor::$Login->Get($names,false);
		if($errors)
		{
			$values['_aupload']=isset($_POST['_atype']) && $_POST['_atype']=='upload';
			$values['avatar']=isset($_POST['avatar']) ? (string)$_POST['avatar'] : '';
		}
		else
		{
			if($values['avatar_type']=='gallery' and $values['avatar'])
				$values['avatar']='images/avatars/'.$values['avatar'];
			$values['_aupload']=$values['avatar_type']!='gallery';

			$al=$values['avatar'] ? ($values['_aupload'] && strpos($values['avatar'],'://')===false ? Eleanor::$uploads.'/avatars/' : '').$values['avatar'] : '';
			if($values['_aupload'])
			{
				$avatar['value']=$al;
				$values['avatar']='';
			}
			else
				$values['avatar']=$al;
		}

		foreach($values as $k=>&$v)
			if(isset($controls[$k]))
				$controls[$k]['value']=$v;
		$C=new Controls;
		$C->arrname=array();
		$values=$C->DisplayControls($controls)+$values;

		$C->arrname=array('avatar');
		$avatar=$C->DisplayControl($avatar);

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$GLOBALS['title'][]=$lang['settings'];
		return Eleanor::$Template->AcOptions($controls,$values,$avatar,$errors,$saved);
	}
}