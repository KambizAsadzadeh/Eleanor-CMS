/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
CORE.Lang({
	"run-hour-num":function(n){
		if(n==1)
			return"Hourly";

		return"Every "+n+" hours";
	},
	"run-minute-num":function(n){
		if(n==1)
			return"Minutely";

		return"Every "+n+" minutes";
	},
	"run-second-num":function(n){
		if(n==1)
			return"Every second";

		return"Every "+n+" seconds";
	},
	"run-month-num":function(n){
		if(n==1)
			return"Monthly";

		return"Every "+n+" months";
	},
	"run-day-num":function(n){
		if(n==1)
			return"Daily";

		return"Every "+n+" days";
	}
});
