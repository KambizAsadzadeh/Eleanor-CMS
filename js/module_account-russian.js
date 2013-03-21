﻿CORE.AddScript("js/russian.js",function(){
	CORE.Lang({
		NICK_TOO_LONG:function(n,l)
		{
			return"Длина имени пользователя не должна превышать "+n+CORE.Russian.Plural(n,[" символ"," символа"," символов"])+". Ви ввели "+l+CORE.Russian.Plural(l,[" символ."," символа."," символов."]);
		},
		PASS_TOO_SHORT:function(n,l)
		{
			return"Минимальная длина пароля "+n+CORE.Russian.Plural(n,[" символ"," символа"," символов"])+". Вы ввели только "+l+CORE.Russian.Plural(l,[" символ."," символа."," символов."]);
		}
	})
});