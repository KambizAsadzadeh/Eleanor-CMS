/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	//Для аплоадера
	"uploader-status":function(max_upload,max_files){
		return "Not more than "+(max_upload/1024/1024).toFixed(1)+" Mb"+(max_files>0 ? " or "
			+max_files+" file"+(max_files==1 ? "" : "s") : "");
	},
	"input-new-name":"Input new name",
	"too-big":function(toobig)
	{
		return "The size of these files is too large and thanks to server restrictions they can't be uploaded via web interface.\n" + toobig.join(", ");
	},
	"copy-and-paste":"Copy and paste",
	"input-folder-name":"Input folder name",
	SESSION_WAS_NOT_FOUND:"Session was lost. Reload page",
	RENAME_FAIL:"Rename failed",
	CREATE_FOLDER_FAIL:"Creation of folder failed",
	CREATE_FILE_FAIL:"Creation of file failed",
	EDIT_FAIL:"Files of this type are not allowed to edit.",
	SAVE_FAIL:"Failed to save the file. Perhaps limit of available space was exceeded.",
	DELETE_FAIL:"Delete failed"
});
