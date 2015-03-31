﻿/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Russian={
	Plural:function(n,a)
	{
		return n%10==1&&n%100!=11?a[0]:(n%10>=2&&n%10<=4&&(n%100<10||n%100>=20)?a[1]:a[2]);
	},
	Translit:function(s)
	{
		var rus=['а','б','в','г','д','е','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ы','ё','ж','ч',
			'ш','щ','э','ю','я','ъ','ь','А','Б','В','Г','Д','Е','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У',
			'Ф','Х','Ц','Ы','Ё','Ж','Ч','Ш','Щ','Э','Ю','Я','Ъ','Ь'],
			eng=['a','b','v','g','d','e','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','y','yo','zh','ch',
			'sh','sch','je','yu','ya',"'","'",'A','B','V','G','D','E','Z','I','J','K','L','M','N','O','P','R','S','T','U',
			'F','H','C','Y','Yo','Zh','Ch','Sh','Sch','Je','Yu','Ya',"'","'"],
			i;

		for(i in rus)
			s=s.split(rus[i]).join(eng[i]);

		return s;
	}
};