<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������� ���������� ������
*/
class TplUploader
{	/*
		����� ������ ���������� ������, �������.
		$buttons - ������ ����������� ������. ������ �������� ������=>���� �����������. ��������� �������� ������:
			create_file - ������ �������� �����
			create_folder - ������ �������� ��������
			update - ������ ���������� ���������� ����������
			watermark - ������ ���������/���������� ��������� ����������� �� ����������� �����������
			show_previews - ������ ���������/���������� ����������� ��������� ����������� � ������� ������
			create_previews - ������ ���������/���������� �������� ��������� ��� ����������� �����������
		$title - �������� ����������
		$maxu - ������������ ������ ������������ �����
		$types - ���� ������ ��� ��������
		$u - ������ ��� ������������ ����������
	*/
	public static function UplUploader($buttons,$title,$maxu,$types,$u)
	{		array_push($GLOBALS['jscripts'],'js/eleanor_uploader.js','js/jquery.poshytip.js');
		$GLOBALS['head'][__class__.__function__]='<link rel="stylesheet" type="text/css" href="templates/Audora/style/uploader.css" media="screen" /><link type="text/css" rel="stylesheet" href="addons/swfupload/css.css" />';
		$lang=Eleanor::$Language['uploader'];
		if($maxu)
		{			$types=$types ? '*.'.join(';*.',$types) : '*.*';			array_push(
				$GLOBALS['jscripts'],
				'addons/swfupload/swfupload.js',
				'addons/swfupload/swfupload.queue.js',
				'addons/swfupload/fileprogress.js'
			);
			$uploader='<div style="float:left;"><span id="fplace-'.$u.'"></span></div><a class="btn" href="#" onclick="FI'.$u.'.UP.cancelQueue(); return false;" id="cancel-'.$u.'" style="display:none;text-decoration:none">'.$lang['cancel_upload'].'</a><div class="info" style="float:left;font-size:10px;line-height:125%;margin-left:7px;max-width:270px"></div>';
			$floading='<div class="divloading" id="loading-'.$u.'"></div>';
			$upscript='FI'.$u.'.UP=new SWFUpload({
	flash_url:"'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'addons/swfupload/swfupload.swf",
	upload_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'upload.php",
	post_params:{session:"'.session_id().'",type:"uploader",uniq:"'.$u.'"},
	file_size_limit:"'.$maxu.'",
	file_types:"'.$types.'",
	file_types_description:"'.$types.'",
	file_upload_limit:100,
	file_queue_limit:0,
	custom_settings:{
		progressTarget:"loading-'.$u.'",
		cancel_button:"#cancel-'.$u.'",
		Update:function(){FI'.$u.'.Update()}
	},
	button_placeholder_id:"fplace-'.$u.'",
	button_image_url:"'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'images/uploader/uploadbtn.png",
	button_text:\'<span class="upbtntext">'.$lang['upload_file'].'</span>\',
	button_text_style:".upbtntext { font-size: 11px; color: #6D6A65; font-family: Tahoma, Arial, sans-serif; font-weight: bold; }",
	button_text_left_padding:16,
	button_text_top_padding:4,
	button_width:129,
	button_height:27,
	button_cursor:SWFUpload.CURSOR.HAND,
	debug:false,
	file_queued_handler:CORE.UPLOADER.FileQueued,
	file_queue_error_handler:CORE.UPLOADER.FileQueueError,
	file_dialog_complete_handler:CORE.UPLOADER.FileDialogComplete,
	upload_start_handler:CORE.UPLOADER.UploadStart,
	upload_progress_handler:CORE.UPLOADER.UploadProgress,
	upload_error_handler:CORE.UPLOADER.UploadError,
	upload_success_handler:CORE.UPLOADER.UploadSuccess,
	upload_complete_handler:CORE.UPLOADER.UploadComplete,
	button_window_mode:SWFUpload.WINDOW_MODE.OPAQUE,
	swfupload_loaded_handler:function(){FI'.$u.'.Update()}
});';		}
		else
		{
			$upscript='FI'.$u.'.Update();';
			$uploader=$floading='';
		}
		$icons='';
		foreach($buttons as $k=>&$v)
			if($v)
				switch($k)
				{					case'create_file':
						$icons.='<a href="#" class="up-create_file" title="'.$lang['create_file'].'"><img style="background-image:url(images/uploader/add_file.png)" src="images/spacer.png" alt="" /></a>';
					break;
					case'create_folder':
						$icons.='<a href="#" class="up-create_folder" title="'.$lang['add_folder'].'"><img src="images/uploader/add_folder.png" alt="" /></a>';
					break;
					case'update':
						$icons.='<a href="#" class="up-update" title="'.$lang['update'].'"><img src="images/uploader/refresh.png" alt="" /></a>';
					break;
					case'watermark':
						$icons.='<a href="#" class="up-watermark" title="'.$lang['watermark'].'"><img style="background-image:url(images/uploader/watermark.png)" src="images/spacer.png" alt="" /></a>';
					break;
					case'show_previews':
						$icons.='<a href="#" class="up-show_previews" title="'.$lang['showprevs'].'"><img style="background-image:url(images/uploader/showpreviews.png)" src="images/spacer.png" alt="" /></a>';
					break;					case'create_previews':
						$icons.='<a href="#" class="up-dopreviews" title="'.$lang['doprevs'].'"><img style="background-image:url(images/uploader/dopreviews.png)" src="images/spacer.png" alt="" /></a>';
				}
		return'<div class="uploadbox"><div class="uploadhead"><h2>'.$title.'</h2><a href="#" id="showb-'.$u.'" onclick="CORE.UPLOADER.Toggle(\'#upl-'.$u.'\',\''.$lang['show'].'\',\''.$lang['hide'].'\',this);return false" style="text-decoration:none"><b>'.$lang['show'].'</b></a></div><div class="uploader" style="display:none" id="upl-'.$u.'"><div class="uppanel">'.$uploader.'<div class="uppanel_ricons">'.$icons.'</div><div class="clr"></div></div><ul class="uppanel_files files"><li>'.$lang['loading'].'</li></ul><div class="pages" style="display:none"></div>'.$floading.'</div></div><script type="text/javascript">/*<![CDATA[*/var FI'.$u.';$(function(){FI'.$u.'=new CORE.UPLOADER({container:"#upl-'.$u.'",uniq:"'.$u.'",sess:"'.session_id().'",service:"'.Eleanor::$service.'"});'.$upscript.'})//]]></script>';	}

	/*
		����������� ������ ��������� � ������ - ������� ���������� ������. ����������� �� ajax
		$buttons - ������, ������� ���������� ���������� ����� ������� �������� � �����. ������ ������� ��� ������=>���� �����������.
			������ ��� ���������:
			folder_open - ������ �������� �������� (����������� � ���� ��������)
			folder_delete - ������ �������� ��������
			folder_rename - ������ �������������� ��������

			������ ��� ������:
			insert_attach - ������ ������� ����� � ���� ������ � ��������� ���������
			insert_link - ������ ������� ������ �� ���� � ��������� ���������
			edit - ������ �������������� �����
			file_rename - ������ �������������� �����
			file_delete - ������ �������� �����
		$short - �������� ������ �� ������� ������� (������ ������������ ��������� ��������)
		$path - ������ ���� � �������� ��������
		$dirs - ������ ���������. ������ �������� ������� - �������� ��������
		$files - ������ ������. ������ ����������� �������:
			file - ��� �����
			edit - ����������� �������������� ����� (�������� ������ �������������)
			date - ���� �������� �����
			size - ������ ����� � ������
			image - ������� ����, ��� ���� �������� ���������
			type - ��� ����� (����������)
		$previews - ������ ���� ������, ��� ������� ���������� ���������
		$prev - ������� ���� ������, ������� �������� �����������
	*/
	public static function UplContent($buttons,$short,$path,$dirs,$files,$previews,$prev)
	{		$lang=Eleanor::$Language['uploader'];
		$r='';
		if($short)
		{			$a=explode('/',ltrim($short,'/'));			$r.='<li><a href="#" class="up-go" data-goal=".." title="'.$lang['go_up'].'"><img class="typeicon" src="images/uploader/folder_up.gif" alt="" style="width:25px;height:16px;" /></a><b>';
			$cnt=count($a)-1;
			for($i=0;$i<$cnt;++$i)
				$r.='<a href="#" class="up-go" data-goal="'.rtrim(str_repeat('../',$cnt-$i),'/').'">'.$a[$i].'</a>/';
			$r.=end($a).'</b></li>';
		}

		$icons='';
		foreach($buttons as $k=>&$v)
			if($v)
				switch($k)
				{
					case'folder_open':
						$icons.='<a href="#" class="up-go" data-goal="{goal}" title="'.$lang['open_folder'].'"><img src="images/uploader/open_folder.png" style="width:16px;height:16px;" /></a>';
					break;
					case'folder_delete':
						$icons.='<a href="#" class="up-delete" data-goal="{goal}" title="'.$lang['delete'].'"><img src="images/uploader/delete.png" style="width:16px;height:16px;" /></a>';
					break;
					case'folder_rename':
						$icons.='<a href="#" class="up-rename" data-goal="{goal}" title="'.$lang['rename'].'"><img src="images/uploader/rename.png" style="width:16px;height:16px;" /></a>';
				}
		foreach($dirs as &$v)
			$r.='<li>'.($icons ? '<span>'.str_replace('{goal}',$v,$icons).'</span>' : '').'<a href="#"  class="up-go" data-goal="'.$v.'"><img class="typeicon" src="images/uploader/type_folder.png" style="width:16px;height:16px;" /><b>'.$v.'</b></a></li>';

		foreach($files as &$v)
		{			$icons='';
			$images=Eleanor::$Template->default['theme'].'images/';
			foreach($buttons as $bk=>&$bv)
				if($bv)
					switch($bk)
					{
						case'insert_attach':
							$preview=in_array($v['file'],$previews) ? substr_replace($v['file'],$prev,strrpos($v['file'],'.'),0) : '';
							$icons.='<a href="#" class="up-attach" data-goal="'.$v['file'].'" data-preview="'.$preview.'" title="'.$lang['insert_attach'].'"><img src="images/uploader/paste_object.png" style="width:16px;height:16px;" /></a>';
						break;
						case'insert_link':
							$icons.='<a href="#" class="up-link" data-goal="'.$v['file'].'" title="'.$lang['insert_file'].'"><img src="images/uploader/paste_link.png" style="width:16px;height:16px;" /></a>';
						break;
						case'edit':
							if($v['edit'])
								$icons.='<a href="#" class="up-edit" data-goal="'.$v['file'].'" title="'.$lang['edit'].'"><img src="'.$images.'edit.png" style="width:16px;height:16px;" /></a>';
						break;
						case'file_rename':
							$icons.='<a href="#" class="up-rename" data-goal="'.$v['file'].'" title="'.$lang['rename'].'"><img src="images/uploader/rename.png" style="width:16px;height:16px;" /></a>';
						break;
						case'file_delete':
							$icons.='<a href="#" class="up-delete" data-goal="'.$v['file'].'" title="'.$lang['delete'].'"><img src="images/uploader/delete.png" style="width:16px;height:16px;" /></a>';
					}
			$t=Eleanor::$Language->Date($v['date']);
			$si=Files::BytesToSize($v['size']);
			$tip=$v['image'] ? ' title="&lt;div class=&quot;frdatethm&quot;&gt;'.$t.'&nbsp;&nbsp;&nbsp;&nbsp;'.$si.'&lt;br /&gt;&lt;img src=&quot;'.$path.$v['file'].'&quot; alt=&quot;&quot; style=&quot;max-width:100%&quot; /&gt;&lt;/div&gt;"' : ' title="&lt;div class=&quot;frdatethm&quot;&gt;'.$t.'&nbsp;&nbsp;&nbsp;&nbsp;'.$si.'&lt;/div&gt;"';
			$r.='<li>'.($icons ? '<span>'.$icons.'</span>' : '').'<img'.$tip.' class="typeicon type up-open" data-goal="'.$v['file'].'" src="images/uploader/file_types/'.$v['type'].'.png" alt="" title="'.$lang['open_file'].'" style="width:16px;height:16px;cursor:pointer" />'.$v['file'].'</li>';
		}
		return$r;	}

	/*
		������� ���������: ��������� (�������� �������). ��� ��������� ������� ����������, ������� ������� � ��������� ������
		$cnt - ���������� ��������� (������+���������) �����
		$pp - ���������� ��������� �� ��������
		$page - ����� ������� ��������, �� ������� �� ���������
		$u - ������ ��� ������������ ���������
	*/
	public static function UplPages($cnt,$pp,$page,$u)
	{
		return Eleanor::$Template->Pages($cnt,$pp,$page,'#','FI'.$u.'.GoPage');
	}

	/*
		��������, ���������� ������������ ���� ��� �������������� �����
		$t - ��� �������������� �����
		$editor - HTML ��� ���������
		$path - ���� � �������������� �����
		$add - ���� �������� �����. ���� true, ������ ���� ���������
	*/
	public static function UplEditFile($t,$editor,$path,$add)
	{		$lang=Eleanor::$Language['uploader'];

		#���� ��������		$js='';
		foreach($GLOBALS['jscripts'] as &$v)
			$js.='<script type="text/javascript" src="'.$v.'"></script>';

		$c='<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html; charset='.DISPLAY_CHARSET.'" /><title>'.$t
		.'</title><base href="'.PROTOCOL.Eleanor::$domain.Eleanor::$site_path.'" /><script src="js/jquery.min.js" type="text/javascript"></script><script src="js/core.js" type="text/javascript"></script>'
		.Eleanor::JsVars(array(
			'c_domain'=>Eleanor::$vars['cookie_domain'],
			'c_prefix'=>Eleanor::$vars['cookie_prefix'],
			'c_time'=>Eleanor::$vars['cookie_save_time'],
			'ajax_file'=>Eleanor::$services['ajax']['file'],
			'site_path'=>Eleanor::$site_path,
			'language'=>Language::$main,
			'!head'=>$GLOBALS['head'] ? '["'.join('","',array_keys($GLOBALS['head'])).'"]' : '[]',
		),true,false,'CORE.').join($GLOBALS['head']).$js
		.'<style type="text/css">
body { text-align: left; margin: 20px; }
#down { text-align:center; height:30px; margin-top:20px; }
</style></head>
<body>'.$editor.'<div id="down">'.Eleanor::Button($lang['save'],'button',array('id'=>'save')).' '.Eleanor::Button($lang['cancel'],'button',array('id'=>'cancel')).'</div>
		<script type="text/javascript">//<![CDATA[
		$(function(){			var fr=function(){$(".CodeMirror").height(window.innerHeight-100)};			setTimeout(function(){				fr();
			},50);
			$(window).resize(fr);

			$("#cancel").click(function(){
				'.($add ? 'document.UPLOADER.DeleteFile("'.$path.'",function(){window.close()})' : 'window.close()').'
			});
			$("#save").click(function(){
				document.UPLOADER.SaveFile("'.$path.'",EDITOR.Get(\'text\'),function(){window.close()});
			});
		});//]]></script>
</body>
</html>';
		$GLOBALS['jscripts']=$GLOBALS['head']=array();
		return$c;
	}
}