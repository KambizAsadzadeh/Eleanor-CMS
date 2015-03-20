<?php
namespace CMS;
/** Шаблон блока "Архив публикаций". Преимущественно для модуля новостей.  При AJAX-запросе нужно вернуть только
 * содержимое дней, поскольку переключается только календарь с днями
 * @var array $var_0 Данные, ключи
 *  int m Месяц
 *  int y Год
 *  int pm Предыдущий месяц или false, если публикаций в предыдущем месяце нет
 *  int nm Следующий месяц или false, если публикаций в следующим месяце нет
 *  int py Предыдущий год или false, если публикаций в предыдущем году нет
 *  int ny Следующий год или false, если публикаций в следующим месяце нет
 *  string a Ссылка на просмотр новостей месяца
 *  array calendar Неделя=>день недели=>число (если нет публикаций) или массив с ключами:
 *   int day Число
 *   int cnt Количество публикаций
 *   string a Ссылка на публикации
 * @var string $var_1 URI модуля
 * @var array $var_2 Месяцы, формат YYYYMM=>[], ключи:
 *  int cnt Количество публикаций в месяце
 *  string a Ссылка на просмотр публикаций
 * @var string $images HTTP путь к картинкам шаблона */
use \Eleanor\Classes\Strings;

$ltpl=Eleanor::$Language['tpl'];
$tday=date('j');
$tmon=$var_0['y']==date('Y') && $var_0['m']==date('n');

$yb='';
if($var_0['py'])
	$yb.='<a href="#" class="y-prev" title="'.$ltpl['year-'].'"><img src="'.$images.'year_minus.png" alt="" /></a>';
if($var_0['ny'])
	$yb.='<a href="#" class="y-next" title="'.$ltpl['year+'].'"><img src="'.$images.'year_plus.png" alt="" /></a>';

$cnt=0;
$calendar='<div class="month"><h4>'.Eleanor::$Language->Date($var_0['y'].'-'.$var_0['m'],'my').'</h4>'
.($yb ? '<span class="selyears">'.$yb.'</span>' : '')
.'<div class="clr"></div></div>
<table><tr class="c_days"><td>'.$ltpl['mon'].'</td><td>'.$ltpl['tue'].'</td><td>'.$ltpl['wed'].'</td><td>'.$ltpl['thu']
	.'</td><td>'.$ltpl['fri'].'</td><td class="vday">'.$ltpl['sat'].'</td><td class="vday">'.$ltpl['sun'].'</td></tr>';

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
	$p=Eleanor::$Language->Date(['n'=>$var_0['pm'],'d'=>1],'my');
	$p=substr($p,0,strpos($p,' '));
	$arrows.='<a class="arrowleft m-prev" href="#">'.$p.'</a>';
}

if($var_0['nm'])
{
	$n=Eleanor::$Language->Date(['n'=>$var_0['nm'],'d'=>1],'my');
	$n=substr($n,0,strpos($n,' '));
	$arrows.='<a class="arrowright m-next" href="#">'.$n.'</a>';
}

$calendar.='</table>'.($arrows ? '<div class="arrows">'.$arrows.'<hr /></div>' : '').'<div style="text-align:center">'
	.($cnt>0 ? '<a href="'.$var_0['a'].'">'.$ltpl['total']($cnt).'</a>' : $ltpl['no_per']).'</div>';

if(AJAX)
	return$calendar;

foreach($var_2 as $k=>&$v)
	$v='<a href="'.$v['a'].'">'.Strings::UcFirst(Eleanor::$Language->Date($k,'my')).' ('.$v['cnt'].')</a>';

$GLOBALS['scripts'][]='js/block_archive.js';
$u=uniqid('cal-');?>
<div class="blockcalendar" id="<?=$u?>"><?=$calendar?></div>
<?=$var_2 ? '<div id="'.$u.'d" style="display:none">'.join('<br />',$var_2).'</div>' : ''?>
<script>//<![CDATA[
$(function(){
	new CORE.Archive({module:"<?=$var_1?>",year:<?=$var_0['y']?>,month:<?=$var_0['m']?>,container:"#<?=$u?>"});
	var cl=localStorage.getItem("clndr","1"),
		ar=$("#<?=$u?>,#<?=$u?>d");

	if(cl)
		ar.toggle();

	$("#<?=$u?>").closest(".dcont").prev().css("cursor","pointer").click(function(){
		ar.toggle();
		cl=!cl;
		if(cl)
			localStorage.setItem("clndr","1");
		else
			localStorage.removeItem("clndr");

		return false;
	});
});//]]></script>