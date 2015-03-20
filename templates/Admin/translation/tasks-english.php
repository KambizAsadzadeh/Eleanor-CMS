<?php
namespace Eleanor\Classes\Language;
return[
	#Для Classes/Tasks.php
	'NO_NEXT_RUN'=>'At set start time, the task will never run',
	'EMPTY_HOUR'=>'Please enter hours',
	'EMPTY_MINUTE'=>'Please enter minutes',
	'EMPTY_SECOND'=>'Please enter seconds',
	'EMPTY_MONTH'=>'Please enter months',
	'EMPTY_DAY'=>'Please enter days',

	'nextrun'=>'Next run',
	'run_time'=>'Run time',
	'lastrun'=>'Last run',
	'months%'=>'months: %s; ',
	'run_month'=>function($n)
	{
		if($n==1)
			return'every month ';

		return'every '.$n.' months ';
	},

	'wdays%'=>'week days: %s; ',
	'days%'=>'days: %s; ',
	'run_day'=>function($n)
	{
		if($n==1)
			return'every day ';

		return'every '.$n.' days ';
	},

	'hours%'=>'hours: %s; ',
	'run_hour'=>function($n)
	{
		if($n==1)
			return'every hour ';

		return'every '.$n.' hours ';
	},

	'minutes%'=>'minutes: %s; ',
	'run_minute'=>function($n)
	{
		if($n==1)
			return'every minute ';

		return'every '.$n.' minutes ';
	},

	'seconds%'=>'seconds: %s; ',
	'run_second'=>function($n)
	{
		if($n==1)
			return'every second ';

		return'every '.$n.' seconds ';
	},

	'run'=>'Run',
	'title-plh'=>'Введите название задачи',
	'hour-*'=>'Any hour',
	'def-hour'=>'In specified hours',
	'minute-*'=>'Any minute',
	'def-minute'=>'In specified minutes',
	'second-*'=>'Any second',
	'def-second'=>'In specified seconds',
	'month-*'=>'Any month',
	'def-month'=>'In specified months',
	'day-*'=>'Any day',
	'def-day'=>'In specified days',
	'handler'=>'Handler',
	'run-hours'=>'Run hours',
	'run-minutes'=>'Run minutes',
	'run-seconds'=>'Run seconds',
	'run-months'=>'Run months',
	'run-days'=>'Run days',
	'options'=>'Options',
	'now'=>'Run now',
	'example-plh'=>'Example: 1,3,5-10',
	'create'=>'Create task',
	'save'=>'Save task',
	'delete-text-span'=>'Are you sure to delete "<span id="delete-title"></span>"?',
	'delete-text%'=>'Are you sure to delete "%s"?',
	'form-errors'=>'Mistakes while filling out the form',
	'applied-by%'=>'Applied filter %s',
	'task'=>'Task',
	'by-task'=>'by task',
	'by-title'=>'by title',
	'filter-by-task'=>'Input task name or part of it',
	'not_found'=>'Tasks were not found',
];