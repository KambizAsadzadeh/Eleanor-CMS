<?php
/*
	Шаблон блока "Архив публикаций". Преимущественно для модуля новостей

	@var массив дней, ключи
		m - месяц
		y - год
		pm - предыдущий месяц или false, если публикаций в предыдущем месяце нет
		nm - следующий месяц или false, если публикаций в следующим месяце нет
		py - предыдущий год или false, если публикаций в предыдущем году нет
		ny - следующий год или false, если публикаций в следующим месяце нет
		a - ссылка на просмотр новостей месяца
		calendar - массив неделя=>день недели=>число (если нет публикаций) или массив с ключами:
			day - число
			cnt - количество публикаций
			a - ссылка на публикации
	@var имя модуля
	@var флаг AJAX-запроса. При AJAX-запросе возвращается только содержимое дней, поскольку переключается только календарь с днями
	@var массив месяцев, формат YYYYMM=>array(), ключи внутреннего массива:
		cnt - количество публикаций в месяце
		a - ссылка на просмотр публикаций
*/
$ltpl=Eleanor::$Language['tpl'];
$tday=date('j');
$tmon=$var_0['y']==date('Y') && $var_0['m']==date('n');

$yb='';
if($var_0['py'])
	$yb.='<a class="m-prev" title="'.$ltpl['year-'].'" href="#">'.$ltpl['year-'].'/a>';
if($var_0['ny'])
	$yb.='<a class="y-next" title="'.$ltpl['year+'].'" href="#">'.$ltpl['year+'].'</a>';

$cnt=0;
$calendar='<div class="bcalendar"><p class="calhead"><b>'.Eleanor::$Language->Date($var_0['y'].'-'.$var_0['m'],'my').'</b>'
	.($yb ? '<span class="year-sel">'.$yb.'</span>' : '')
	.'</p><table><tr class="c_days"><td>'.$ltpl['mon'].'</td><td>'.$ltpl['tue'].'</td><td>'.$ltpl['wed'].'</td><td>'.$ltpl['thu'].'</td><td>'.$ltpl['fri'].'</td><td class="vday">'.$ltpl['sat'].'</td><td class="vday">'.$ltpl['sun'].'</td></tr>';
foreach($var_0['calendar'] as &$week)
{
	$calendar.='<tr>';
	foreach($week as $k=>&$day)
	{
		if(!$day)
			$td='&nbsp;';
		elseif(is_array($day))
		{
			$cnt+=$day['cnt'];
			$td='<a href="'.$day['a'].'" title="'.$ltpl['_cnt']($day['cnt']).'">'.$day['day'].'</a>';
			$day=$day['day'];
		}
		else
			$td=$day;

		$cl=$tmon && $day==$tday ? 'today' : false;
		if($k>4)
			$cl=$cl ? 'tovday' : 'vday';
		$calendar.='<td'.($cl ? ' class="'.$cl.'"' : '').'>'.$td.'</td>';
	}
	$calendar.='</tr>';
}
$arrows='';
if($var_0['pm'])
{
	$p=Eleanor::$Language->Date(array('n'=>$var_0['pm'],'d'=>1),'my');
	$p=substr($p,0,strpos($p,' '));
	$arrows.='<a class="m-prev" href="#">'.$p.'</a>';
}

if($var_0['nm'])
{
	$n=Eleanor::$Language->Date(array('n'=>$var_0['nm'],'d'=>1),'my');
	$n=substr($n,0,strpos($n,' '));
	$arrows.='<a class="m-next" href="#">'.$n.'</a>';
}

$calendar.='</table>'.($arrows ? '<div class="month-sel">'.$arrows.'</div>' : '')
.'<p class="calnews-all">'.($cnt>0 ? '<a href="'.$var_0['a'].'">'.$ltpl['total']($cnt).'</a>' : $ltpl['no_per']).'</p></div>';

if($v_2)#Ajax
	return$calendar;

foreach($v_3 as $k=>&$v)
	$v='<a href="'.$v['a'].'">'.Strings::UcFirst(Eleanor::$Language->Date($k,'my')).' ('.$v['cnt'].')</a>';

$GLOBALS['scripts'][]='js/block_archive.js';
$u=uniqid('cal-');
return array(
	'				<div class="box">
					<span class="barch-tabs"><a class="arh-cal" href="#" title="В виде календаря">В виде календаря</a><a class="arh-list" href="#" title="В виде списка">В виде списка</a></span>
					<h3 class="btl">',
	'title'=>'',
	'</h3>
					<div class="bcont">
						<div class="tabcont" id="'.$u.'">
					'.$calendar.'
						</div>
						<div class="tabcont">
							<ul class="archives"><li>'.join('</li><li>',$v_3).'</li></ul>
						</div>
					</div>
				</div><script>$(function(){ new CORE.Archive({module:"'.$var_1.'",year:'.$var_0['y'].',month:'.$var_0['m'].',container:"#'.$u.'"}); });</script>',
);