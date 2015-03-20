/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	"run-hour-num":function(n){
		if(n==1)
			return"Ежечасно";

		return CORE.Russian.Plural(n,["Каждый "+n+" час","Каждые "+n+" часа","Каждые "+n+" часов"]);
	},
	"run-minute-num":function(n){
		if(n==1)
			return"Ежеминутно";

		return CORE.Russian.Plural(n,["Каждую "+n+" минуту","Каждые "+n+" минуты","Каждые "+n+" минут"]);
	},
	"run-second-num":function(n){
		if(n==1)
			return"Ежесекундно";

		return CORE.Russian.Plural(n,["Каждую "+n+" секунду","Каждые "+n+" секунды","Каждые "+n+" секунд"]);
	},
	"run-month-num":function(n){
		if(n==1)
			return"Ежемесячно";

		return CORE.Russian.Plural(n,["Каждый "+n+" месяц","Каждые "+n+" месяца","Каждые "+n+" месяцев"]);
	},
	"run-day-num":function(n){
		if(n==1)
			return"Ежедневно";

		return CORE.Russian.Plural(n,["Каждый "+n+" день","Каждые "+n+" дня","Каждые "+n+" дней"]);
	}
});
