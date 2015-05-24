/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	//Для аплоадера
	"uploader-status":function(max_upload,max_files){
		return "Не более "+(max_upload/1024/1024).toFixed(1)+" Mb"+(max_files>0 ? " или "
			+max_files+CORE.Russian.Plural(max_files,[" файла"," файлов"," файлов"]) : "");
	},
	"input-new-name":"Введите новое имя",
	"too-big":function(toobig)
	{
		return "Размер следующих файлов слишком велик и, благодаря ограничениям веб сервера, они не могут быть загружены через веб интерфейс.\n" + toobig.join(", ");
	},
	"copy-and-paste":"Скопируйте и вставьте",
	"input-folder-name":"Введите название папки",

	SESSION_WAS_NOT_FOUND:"Потеряна сессия! Обновите страницу.",
	RENAME_FAIL:"Переименование не удалось",
	CREATE_FOLDER_FAIL:"Не удалось создать папку",
	CREATE_FILE_FAIL:"Не удалось создать файл",
	EDIT_FAIL:"Файлы этого типа запрещено редактировать",
	SAVE_FAIL:"Не удалось сохранить файл. Возможно, превышен лимит доступного пространства",
	DELETE_FAIL:"Удаление не удалось"
});
