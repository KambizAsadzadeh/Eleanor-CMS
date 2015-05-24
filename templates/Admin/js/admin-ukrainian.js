/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	//Для аплоадера
	"uploader-status":function(max_upload,max_files){
		return "Не більше "+(max_upload/1024/1024).toPrecision(2)+" Mb або "
			+CORE.Ukrainian.Plural(max_files,["файлу","файлів","файлів"]);
	},
	"input-new-name":"Введіть нове им'я",
	"too-big":function(toobig)
	{
		return"Размір наступних файлів надто великий, та дякуючи обмеженням веб сервера, вони не можуть бути завантажені через веб інтерфейс.\n"+toobig.join(", ");
	},
	"copy-and-paste":"Скопіюйте і вставте",
	"input-folder-name":"Введіть назву папки",

	SESSION_WAS_NOT_FOUND:"Втрачено сесія! Оновіть сторінку.",
	RENAME_FAIL:"Переіменування не вдалося",
	CREATE_FOLDER_FAIL:"Не вдалося створити папку",
	CREATE_FILE_FAIL:"Не вдалося створити файл",
	EDIT_FAIL:"Файли цього типу заборонено редагувати",
	SAVE_FAIL:"Не вдалося зберегти файл. Можливо, вичерпано ліміт доступного простору",
	DELETE_FAIL:"Видалення не вдалося"
});
