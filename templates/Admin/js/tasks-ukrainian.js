/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	"run-hour-num":function(n){
		if(n==1)
			return"Щогодини";

		return CORE.Ukrainian.Plural(n,["Кожну "+n+" годину","Кожні "+n+" годнини","Кожні "+n+" годин"]);
	},
	"run-minute-num":function(n){
		if(n==1)
			return"Щохвилини";

		return CORE.Ukrainian.Plural(n,["Кожну "+n+" хвилину","Кожні "+n+" хвилини","Кожні "+n+" хвилин"]);
	},
	"run-second-num":function(n){
		if(n==1)
			return"Щосекунды";

		return CORE.Ukrainian.Plural(n,["Кожну "+n+" секунду","Кожні "+n+" секунди","Кожні "+n+" секунд"]);
	},
	"run-month-num":function(n){
		if(n==1)
			return"Щомісяця";

		return CORE.Ukrainian.Plural(n,["Кожен "+n+" місяць","Кожні "+n+" місяця","Кожні "+n+" месяців"]);
	},
	"run-day-num":function(n){
		if(n==1)
			return"Щодня";

		return CORE.Ukrainian.Plural(n,["Кожен "+n+" день","Кожні "+n+" дня","Кожні "+n+" днів"]);
	}
});
