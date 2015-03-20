<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;
use CMS\Eleanor;

/** Набор основных шаблонов */
class Index
{
	/** Элемент шаблона: пагинатор, листалка страниц
	 * @param int $cnt Количество элементов
	 * @param int $pp Количество элементов на страницу
	 * @param int $page Номер текущей страницы, в случае отрицательного числа пагинатор станет обратным
	 * @param array|string|\Closure $href массив для генератора ссылок Url|строка с участком {page}
	 * @param string|null $ajax Название функции, куда будет передан запрос
	 * @param string $hash Окончание ссылок для страниц после знака #
	 * @return string */
	public static function Pagination($cnt,$pp,$page,$href,$ajax=null,$hash='')
	{
		$h=$hash ? '#'.$hash : '';

		if($reverse=$page<0)
			$page=-$page;

		$pages=$reverse ? (int)($cnt/$pp) : ceil($cnt/$pp);

		if($pages>1)
		{
			$prev=$next='';
			$blank=static::PageUrl($href,'{page}');
			$blank=str_replace('%7Bpage%7D','{page}',$blank);
			$result=[];

			if(strpos($blank,'{page}')!==false)
				if($reverse)
				{
					if($page<$pages)
					{
						$prev=static::PageUrl($href,$page+1);
						$GLOBALS['head']['prev']='<link rel="prev" href="'.$prev.'" />';
						$prev='<li><a href="'.$prev.'">&laquo;</a></li>';
					}

					if($page>1)
					{
						$next=static::PageUrl($href,$page-1);
						$GLOBALS['head']['next']='<link rel="next" href="'.$next.'" />';
						$next='<li><a href="'.$next.'">&raquo;</a></li>';
					}
				}
				else
				{
					if($page>1)
					{
						$prev=static::PageUrl($href,$page-1);
						$GLOBALS['head']['prev']='<link rel="prev" href="'.$prev.'" />';
						$prev='<li><a href="'.$prev.'">&laquo;</a></li>';
					}

					if($page<$pages)
					{
						$next=static::PageUrl($href,$page+1);
						$GLOBALS['head']['next']='<link rel="next" href="'.$next.'" />';
						$next='<li><a href="'.$next.'">&raquo;</a></li>';
					}
				}

			$i=$reverse ? $pages : 1;
			$first=true;
			$fill_r=$reverse ? ($pages-$page)==5 : ($pages-$page)==3;
			$fill_l=$reverse ? $page==4 : $page==5;

			for(;;)
			{
				if($i<1 or $i>$pages)
					break;

				if($i==$page)
					$result[]='<li class="active"><span>'.$i.'</span></li>';
				elseif($reverse and $i>$pages-3 or !$reverse and $i<3 or#Левая часть
					$i>($page-2) and $i<($page+2) or $fill_r and $i>$page or $fill_l and $i<$page or#Средня часть
					$reverse and $i==1 or !$reverse and $i==$pages#Правая часть
				)#or $all
					$result[]='<li><a href="'.static::PageUrl($href,$i).$h.'"'
						.($first ? ' rel="first"' : '').'>'.$i.'</a></li>';
				else
				{
					$result[]='<li><span>...</span></li>';

					if($reverse)
						$i=$i<$page-1 ? 1 : $page+1;
					else
						$i=$i>$page+1 ? $pages : $page-1;

					continue;
				}

				$first=false;

				if($reverse)
					$i--;
				else
					$i++;
			}
			$last=array_pop($result);
			$result[]=str_replace('<a','<a rel="first"',$last);
			$result=join('',$result);
			$goto=T::$lang['goto'];

			return<<<HTML
						<div class="list-pager" data-ajax="{$ajax}" data-blank="{$blank}">
							<ul class="pagination">{$prev}{$result}{$next}</ul>
							<div class="input-group">
								<input type="number" min="1" max="{$pages}" class="form-control">
								<span class="input-group-btn">
									<button type="button" class="btn btn-default">{$goto}</button>
								</span>
							</div>
						</div>
HTML;
		}

		return'';
	}

	#ToDo! Удалить следующий метод
	/** Элемент шаблона: пагинатор, листалка страниц
	 * @param int $cnt Количество элементов
	 * @param int $pp Количество элементов на страницу
	 * @param int $page Номер текущей страницы, в случае отрицательного числа пагинатор станет обратным
	 * @param array|string|\Closure $href массив для генератора ссылок Url|строка с участком {page}
	 * @param string|null $ajax Название функции, куда будет передан запрос
	 * @param string $hash Окончание ссылок для страниц после знака #
	 * @param bool $all Флаг отображения всех ссылок, без скрытых промежутков
	 * @param int $gap Максимальное число активных ссылок слева и справа от текущей страницы, а также с концов
	 * @return string */
	public static function Pages($cnt,$pp,$page,$href,$ajax=null,$hash='',$all=false,$gap=4)
	{
		$h=$hash ? '#'.$hash : '';

		if($reverse=$page<0)
			$page=-$page;

		$pages=$reverse ? (int)($cnt/$pp) : ceil($cnt/$pp);

		if($pages>1)
		{
			$js=static::PageUrl($href,'{page}');
			$js=str_replace('%7Bpage%7D','{page}',$js);
			$result[]='<a href="#"'.($ajax ? '' : ' onclick="CORE.JumpToPage(\''.$js.$h.'\','.$pages.');return !1;"')
				.' title="'.T::$lang['goto_page'].'"><img src="'.Eleanor::$Template->default['theme']
				.'gotopage.png" alt="" /></a>';

			if(strpos($js,'{page}')!==false)
				if($reverse)
				{
					if($page<$pages)
						$GLOBALS['head']['prev']='<link rel="prev" href="'.static::PageUrl($href,$page+1).'" />';

					if($page>1)
						$GLOBALS['head']['next']='<link rel="next" href="'.static::PageUrl($href,$page-1).'" />';
				}
				else
				{
					if($page>1)
						$GLOBALS['head']['prev']='<link rel="prev" href="'.static::PageUrl($href,$page-1).'" />';

					if($page<$pages)
						$GLOBALS['head']['next']='<link rel="next" href="'.static::PageUrl($href,$page+1).'" />';
				}

			$i=$reverse ? $pages : 1;
			$first=true;

			for(;;)
			{
				if($i<1 or $i>$pages)
					break;

				if($i==$page)
					$result[]='<span>'.$i.'</span>';
				elseif($all or ($i<=$gap or $i>($pages-$gap) or $i>=($page-$gap) and $i<=($page+$gap)))
					$result[]='<a href="'.static::PageUrl($href,$i).$h.'" data-page="'.$i.'"'
						.($first ? ' rel="first"' : '').'>'.$i.'</a>';
				else
				{
					$result[]='<span class="numbersmore">...</span>';

					if($i>($page+$gap))
						$i=$reverse ? $page+$gap : $pages-$gap+1;
					else
						$i=$reverse ? $gap : $page-$gap;

					continue;
				}

				$first=false;

				if($reverse)
					$i--;
				else
					$i++;
			}

			$u=uniqid('nu-');
			$last=array_pop($result);
			$result[]=str_replace('<a','<a rel="first"',$last);

			return'<div class="numbers"'
				.($ajax ? ' id="'.$u.'"' : '').'><b>'.T::$lang['pages'].' </b>'.implode(' ',$result).'</div>'
				.($ajax ? '<script>//<![CDATA[
$(function(){
	$("#'.$u.' a").click(function(e){
		e.preventDefault();
		var p=$(this).data("page");
		if(p)
			'.$ajax.'(p);
		else
			CORE.JumpToPage('.$ajax.','.$pages.');
	});
})//]]></script>' : '');
		}

		return'';
	}

	/** Генератор ссылок
	 * @param string|array|\Closure $h Набор ссылок
	 * @param int $p Номер страницы
	 * @return string */
	private static function PageUrl($h,$p)
	{
		if(is_array($h))
			$h=isset($h[$p]) ? $h[$p] : $h[0];

		return is_object($h) ? $h($p) : $h;
	}
}

return Index::class;