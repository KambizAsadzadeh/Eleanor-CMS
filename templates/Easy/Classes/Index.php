<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	Файл частоиспользуемых элементов шаблона
*/
class TplIndex
{
	/*
		Элемент шаблона: листалка страниц

		$cnt - количество элементов
		$pp - количество элементов на страницу
		$page - номер текущей страницы, где мы сейчас находимся
		$href - массив для генератора ссылок Url, либо строка с участком {page} (этот участок может быть и в массиве)
		$ajax - название функции, куда будет передан запрос
		$hash - окончание ссылок для страниц после знака #
	*/
	public static function Pages($cnt,$pp,$page,$href,$ajax=false,$hash='',$all=false,$gap=4)
	{
		#ToDo! rel="first", rel="last" - добавить к тегам a
		$ltpl=Eleanor::$Language['tpl'];
		$h=$hash ? '#'.$hash : '';

		if($reverse=$page<0)
			$page=-$page;

		$pages=$reverse ? (int)($cnt/$pp) : ceil($cnt/$pp);
		if($pages>1)
		{
			$js=static::PageUrl($href,'{page}');
			$js=str_replace('%7Bpage%7D','{page}',$js);
			$bv='<span class="prev" title="'.$ltpl['<<'].'">'.$ltpl['<<'].'</span>';
			$fv='<span class="next" title="'.$ltpl['>>'].'">'.$ltpl['>>'].'</span>';
			if(strpos($js,'{page}')!==false)
				if($reverse)
				{
					if($page<$pages)
					{
						$u=static::PageUrl($href,$page+1);
						$GLOBALS['head']['prev']='<link rel="prev" href="'.$u.'" />';
						$bv='<a href="'.$u.'" rel="prev" class="prev" title="'.$ltpl['<<'].'">'.$ltpl['<<'].'</a>';
					}
					if($page>1)
					{
						$u=static::PageUrl($href,$page-1);
						$GLOBALS['head']['next']='<link rel="next" href="'.$u.'" />';
						$fv='<a href="'.$u.'" rel="next" class="next" title="'.$ltpl['>>'].'">'.$ltpl['>>'].'</a>';
					}
				}
				else
				{
					if($page>1)
					{
						$u=static::PageUrl($href,$page-1);
						$GLOBALS['head']['prev']='<link rel="prev" href="'.$u.'" />';
						$bv='<a href="'.$u.'" rel="prev" class="prev" title="'.$ltpl['<<'].'">'.$ltpl['<<'].'</a>';
					}
					if($page<$pages)
					{
						$u=static::PageUrl($href,$page+1);
						$GLOBALS['head']['next']='<link rel="next" href="'.$u.'" />';
						$fv='<a href="'.$u.'" rel="next" class="next" title="'.$ltpl['>>'].'">'.$ltpl['>>'].'</a>';
					}
				}
			$i=$reverse ? $pages : 1;
			for(;;)
			{
				if($i<1 or $i>$pages)
					break;
				if($i==$page)
					$result[]='<span>'.$i.'</span>';
				elseif($all or ($i<=$gap or $i>($pages-$gap) or $i>=($page-$gap) and $i<=($page+$gap)))
					$result[]='<a href="'.static::PageUrl($href,$i).$h.'" data-page="'.$i.'">'.$i.'</a>';
				else
				{
					$result[]='<span class="nav_ext">...</span>';
					if($i>($page+$gap))
						$i=$reverse ? $page+$gap : $pages-$gap+1;
					else
						$i=$reverse ? $gap : $page-$gap;
					continue;
				}
				if($reverse)
					$i--;
				else
					$i++;
			}

			return'<div class="clrfix"'.($ajax ? ' id="pager"' : '').'><a href="#"'.($ajax ? '' : ' onclick="CORE.JumpToPage(\''.$js.$h.'\','.$pages.');return !1;"').' class="pg-sel thd" title="'.$ltpl['selpage'].'">'.$ltpl['selpage'].'</a><div class="pager">'
			.implode(' ',$result).'</div>'.$bv.$fv.'</div>'.($ajax ? '<script>//<![CDATA[
$(function(){
	$("#pager a").click(function(){
		var p=$(this).data("page");
		if(p)
			'.$ajax.'(p);
		else
			CORE.JumpToPage('.$ajax.','.$pages.');
		return false;
	});
})//]]></script>' : '');
		}
	}

	private static function PageUrl($h,$p)
	{
		if(is_array($h))
			$h=isset($h[$p]) ? $h[$p] : $h[0];
		return is_object($h) ? $h($p) : $h;
	}
}