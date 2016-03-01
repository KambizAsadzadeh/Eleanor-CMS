<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS\Templates\Admin;

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
						$GLOBALS['head']['prev']=<<<HTML
<link rel="prev" href="{$prev}" />
HTML;
						$prev=<<<HTML
<li><a rel="prev" href="{$prev}">&laquo;</a></li>
HTML;
					}

					if($page>1)
					{
						$next=static::PageUrl($href,$page-1);
						$GLOBALS['head']['next']=<<<HTML
<link rel="next" href="{$next}" />
HTML;
						$next=<<<HTML
<li><a rel="next" href="{$next}">&raquo;</a></li>
HTML;
					}
				}
				else
				{
					if($page>1)
					{
						$prev=static::PageUrl($href,$page-1);
						$GLOBALS['head']['prev']=<<<HTML
<link rel="prev" href="{$prev}" />
HTML;
						$prev=<<<HTML
<li><a rel="prev" href="{$prev}">&laquo;</a></li>
HTML;
					}

					if($page<$pages)
					{
						$next=static::PageUrl($href,$page+1);
						$GLOBALS['head']['next']=<<<HTML
<link rel="next" href="{$next}" />
HTML;
						$next=<<<HTML
<li><a rel="next" href="{$next}">&raquo;</a></li>
HTML;
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
					$result[]=<<<HTML
<li class="active"><span>{$i}</span></li>
HTML;
				elseif($reverse and $i>$pages-3 or !$reverse and $i<3 or#Левая часть
					$i>($page-2) and $i<($page+2) or $fill_r and $i>$page or $fill_l and $i<$page or#Средня часть
					$reverse and $i==1 or !$reverse and $i==$pages#Правая часть
				)#or $all
				{
					if($first)
						$rel=' rel="first"';
					elseif($reverse and $i+1==$page or !$reverse and $i-1==$page)
						$rel=' rel="next"';
					elseif($reverse and $i-1==$page or !$reverse and $i+1==$page)
						$rel=' rel="prev"';
					else
						$rel='';

					$result[]='<li><a href="'.static::PageUrl($href, $i).$h.'"'.$rel.">{$i}</a></li>";
				}
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
			$result[]=str_replace('<a','<a rel="last"',$last);
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