<?php
return array(
	#Для Classes/Comments.php
	'vc'=>'Комментарии посетителей',
	'nc'=>'Комментариев пока никто не писал.',
	'anc'=>'Ответов на этот комментарий пока никто не писал.',
	'lnp'=>'Загрузить новые комментарии',
	'addc'=>'Добавить комментарий',
	'yn'=>'Ваше имя',
	'yc'=>'Комментарий',
	'needch'=>'Ваш комментарий станет общедоступен только после проверки.',
	'captcha'=>'Защитный код',
	'captcha_'=>'введите символы с картинки',
	'cite'=>'Цитата %s',
	'stmodwait'=>'Этот комментарий ожидает проверки',
	'stblocked'=>'Этот комментарий заблокирован',
	'answer'=>'Ответить',
	'qquote'=>'Быстрая цитата',
	'withsel'=>'-С отмеченными-',
	'doact'=>'Активировать',
	'toblock'=>'Заблокировать',
	'tomod'=>'На модерацию',
	'save'=>'Сохранить',
	'cancel'=>'Отмена',
	'answers'=>function($n){return $n.Russian::Plural($n,array(' ответ',' ответа',' ответов'));},
	'added_after'=>function($y,$m,$d,$h,$i,$s)
	{
		return rtrim('Добавлено через '.($y>0 ? $y.' '.Russian::Plural($y,array(' год',' года',' лет')) : '')
			.($m>0 ? $m.Russian::Plural($m,array(' месяц ',' месяца ',' месцев ')) : '')
			.($d>0 ? $d.Russian::Plural($d,array(' день ',' дня ',' дней ')) : '')
			.($h>0 ? $h.Russian::Plural($h,array(' час ',' часа ',' часов ')) : '')
			.($i>0 ? $i.Russian::Plural($i,array(' минуту ',' минуты ',' минут ')) : '')
			.($s>0 ? $s.Russian::Plural($s,array(' секунду',' секунды',' секунд')) : ''));
	},
);