<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ ��� ������������� ������ �������.

	���������� ����������:
		$GLOBALS['Eleanor']->Categories - ������ ��������� ������, ������ ������ � ��������
			dump - ������ ���� ���������. ������: id=>array(), ����� ����������� �������:
				title - �������� ���������
				description - �������� ���������
				image - ��� ��������-�������� ���������, ���� �����
				parent - ������������� ���������-��������
				parents - �������������� ���������-���������, ����������� ��������
			imgforlder - ���� � �������� � ����������-���������� ���������
			GetOptions() - ����� ���������� <option>-� (����) ��� <select> ����������������� ���������
			GetUri() - ��������� ���������� ������� ���������� ��� Url->Construct()
		$GLOBALS['Eleanor']->module - ������ ���������� ������
			tags - ������ ����� ������, ������ ������� �������� - ������ � �������:
				_a - ������ �� ��������� � ���� �����
				cnt - ���������� ���������� � ���� �����
				name - �������� ����
			corn - ������ �� ������ ����� ��������
			links - ������ ������ (���� ������) � �������:
				base - ������ �� ������� ������
				categories - ������ �� ���������, ���� false
				tags - ������ �� ���� ������, ���� false
				search - ������ �� ����� ����������
				add - ������ �� ���������� ����������, ���� false
				my - ������ �� ��������� ������������ (���� ���������), ���� false
*/
class TplUserNews
{	/*
		��������� �����. ������ ������ Cron
	*/	protected static function TopMenu($tit=false)
	{		$GLOBALS['jscripts'][]='js/module_publications.js';
		#Cron
		$cron=$GLOBALS['Eleanor']->module['cron'] ? '<img src="'.$GLOBALS['Eleanor']->module['cron'].'" style="width:1px;height1px;" />' : '';
		#[E] Cron

		if($_SERVER['QUERY_STRING']=='')
			return$cron;		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$links=&$GLOBALS['Eleanor']->module['links'];		return Eleanor::$Template->Menu(array(
			'menu'=>array(
				array($links['base'],$lang['all']),
				$links['categories'] ? array($links['categories'],$lang['categs']) : false,
				$links['tags'] ? array($links['tags'],$lang['tags']) : false,
				array($links['search'],$lang['search'],'addon'=>array('rel'=>'search')),
				$links['add'] ? array($links['add'],$lang['add']) : false,
				$links['my'] ? array($links['my'],$lang['my']) : false,
			),
			'title'=>($tit ? $tit : $lang['n']).$cron,
		));
	}

	protected static function List_($data,$shst=false)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		$T=clone Eleanor::$Template;
		foreach($data['items'] as $k=>&$v)
		{
			$ntags='';
			foreach($v['tags'] as &$tv)
				if(isset($data['tags'][$tv]))
					$ntags.='<a href="'.$data['tags'][$tv]['_url'].'">'.$data['tags'][$tv]['name'].'</a>, ';

			$status=false;
			if(isset($v['status']) and $shst)
				switch($v['status'])
				{					case-1:
						$status='<span style="font-weight:bold;color:darkyellow">'.$lang['waitmod'].'</span>';
					break;					case 1:
						$status='<span style="font-weight:bold;color:green">'.$lang['activated'].'</span>';
					break;
					default:
						$status='<span style="font-weight:bold;">'.$lang['deactivated'].'</span>';
				}

			$T->Base(array(
				'top'=>array(
					'published'=>sprintf($lang['published_'],Eleanor::$Language->Date($v['date'],'fdt')),
					'category'=>isset($data['cats'][$v['_cat']]) ? sprintf($lang['category_'],'<a href="'.$data['cats'][$v['_cat']]['_a'].'">'.$data['cats'][$v['_cat']]['t'].'</a>') : false,
					'comments'=>$lang['comments_']($v['comments'],'<a href="'.$v['_url'].'#comments">'.$v['comments'].'</a>'),
					'author'=>sprintf($lang['publisher_'],$v['author_id'] ? '<a href="'.Eleanor::$Login->UserLink($v['author'],$v['author_id']).'" rel="author">'.$v['author'].'</a>' : $v['author']),
				),
				'bottom'=>array(
					'readmore'=>$v['_readmore'] ? '<a href="'.$v['_url'].'#more">'.$lang['readmore'].'</a>' : false,
					'voting'=>$v['voting'] ? ' <a href="'.$v['_url'].'#voting">'.$lang['voting'].'</a>' : false,
					'status'=>$status,
					'rating'=>Eleanor::$vars['publ_rating'] && isset($data['rating'][$k]) ? $data['rating'][$k] : false,
					'edit'=>$v['_aedit'] ? Eleanor::$Template->EditDelete($v['_aedit'],$v['_adel']) : false,
				),
				'title'=>$v['_readmore'] ? '<a href="'.$v['_url'].'">'.$v['title'].'</a>'.($v['_hastext'] ? ' <a href="#" data-id="'.$k.'" data-more="#more-'.$k.'" class="getmore"></a>' : '') : $v['title'],
				'text'=>$v['announcement'].($v['_hastext'] ? '<div id="more-'.$k.'" style="display:none"></div>' : '').($ntags ? '<div class="tags">'.sprintf($lang['tags_'],rtrim($ntags,', ')).'</div>' : ''),
			));
		}
		return$T.'<script type="text/javascript">//<![CDATA[
$(function(){
	CORE.Publications.Init("div.base","'.$GLOBALS['Eleanor']->module['name'].'");
})//]]></script>';	}

	/*
		������ �������� �� ������� ����� � ������� ������
		$data - ������ ������. �����:
			items - ������ ��������. ������: id=>array()
				date - ���� ���������� �������
				author - ��� ������ �������
				author_id - ������������� ������ �������
				status - ������ ������� (1 - �������, 0 - �������������, -1 - ������� ���������)
				reads - ����� ����������
				comments - ����� ������������
				tags - ������ ��������������� ����� �������
				title - ��������� �������
				announcement - ����� �������
				voting`- ���� ������� ������ � �������

				_aedit - ������ �� �������������� �������, ���� false
				_adel - ������ �� �������� �������, ���� false
				_cat - ������������� ��������� �������
				_readmore - ���� ������� ��������� �������
				_hastext - ���� ������� ���������� ������ �������
				_url - ������ �� �������
			cats - ������ ���������. ������: id=>array()
				_a - ������ �� ���������
				t - �������� ���������
			tags - ������ �����. ������: id=>array(), ����� ����������� �������:
				_url - ������ �� ������� � �����
				name - ��� ����
				cnt - ���������� �������� � ������ �����
			rating - ������ ��������������� ���� �������� ��������. ������: id=>��� ��������
		$cnt - ���������� �������� �����
		$page - ����� ��������, �� ������� �� ������ ���������
		$pp - ����� �������� �� ��������
		$links - ������ ������, �����:
			first_page - ������ �� ������ �������� ����������
	*/
	public static function ShowList($data,$cnt,$page,$pp,$links)
	{		return static::TopMenu().static::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page);
	}

	/*
		�������� ������ �������� �� ������������ ����
		$data - ����
		$links - ������ ������, �����:
			first_page - ������ �� ������ �������� ����������
			pages - ������ ������ �� ��������� ��������
		�������� ��������� ���������� �������� � ������ List
	*/
	public static function DateList($date,$data,$cnt,$page,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']);
	}

	/*
		�������� ������ �������� ������������ (�����)
		$links - ������ ������, �����:
			first_page - ������ �� ������ �������� ����������
			pages - ������ ������ �� ��������� ��������
		�������� ��������� ���������� �������� � ������ List
	*/
	public static function MyList($data,$cnt,$page,$pp,$links)
	{
		return static::TopMenu(reset($GLOBALS['title'])).self::List_($data,false).Eleanor::$Template->Pages(array($cnt,1=>$links['first_page']),$pp,$page,$links['pages']);
	}

	/*
		�������� ������ �������� ������������ ���������
		$category - ������ ���������, ������ � �������:
			id - ������������� ���������
			title - �������� ���������
			description - �������� ���������
		�������� ��������� ���������� �������� � ������ List
	*/
	public static function CategoryList($category,$data,$cnt,$page,$pp,$links)
	{		return self::ShowCategories($category['id']).self::List_($data)
			.Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']);
	}

	/*
		�������� ������ ���� ���������
	*/
	public static function ShowCategories($cat=0)
	{		$dump=&$GLOBALS['Eleanor']->Categories->dump;		if(isset($dump[$cat]))
		{			$way=$dump[$cat]['parents'] ? explode(',',rtrim($dump[$cat]['parents'],',')) : array();
			foreach($way as $k=>&$v)
				if(isset($dump[$v]))
					$v=array(
						$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($v),true,false),
						$dump[$v]['title'],
					);				else
					unset($way[$k]);
			if($way)
			{				$w='<span class="cat1">';				foreach($way as $v)
					$w='<a href="'.$v[0].'">'.$v[1].'</a> &raquo; ';
				$w.='</span><hr />';			}
			else
				$w='';			$c=$w.'<table style="width:100%"><tr>'
				.($dump[$cat]['image'] ? '<td><img src="'.$GLOBALS['Eleanor']->Categories->imgfolder.$dump[$cat]['image'].'" alt="'.$dump[$cat]['title'].'" title="'.$dump[$cat]['title'].'" /></td>' : '')
				.'<td><td><h2 class="title">'.$dump[$cat]['title'].'</h2>'.$dump[$cat]['description'].'</td></tr></table>';		}
		else
			$c='';
		$cols=3;#���������� ������� ���������

		$w=round(100/$cols);
		$num=$cols;
		$iscats=true;
		$subcat=-1;
		$subcatsb=true;
		foreach($dump as $k=>&$v)
			switch($v['parent'])
			{				case$cat:
					if($iscats)
					{
						$c.=($cat ? '<hr />' : '').'<table class="categories">';
						$iscats=false;
					}

					if($subcat>0)
					{						if(!$subcatsb)
							$c.='</ul>';
						$c.='</td>';					}
					if($num==0)
					{
						$c.='</tr>';
						$num=$cols;
					}
					if($num==$cols)
						$c.='<tr>';

					$c.='<td style="width:'.$w.'%"><table><tr>'
						.($v['image'] ? '<td><img src="'.$GLOBALS['Eleanor']->Categories->imgfolder.$v['image'].'" alt="'.$v['title'].'" title="'.$v['title'].'" /></td>' : '')
						.'<td><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($k),true,false).'"><strong>'.$v['title'].'</strong></a></td></tr></table>';

					$subcat=$k;
					$subcatsb=true;
					$num--;
				break;
				case$subcat:
					if($subcatsb)
					{						$c.='<ul>';
						$subcatsb=false;					}
					$c.='<li><a href="'.$GLOBALS['Eleanor']->Url->Construct($GLOBALS['Eleanor']->Categories->GetUri($k),true,false).'">'.$v['title'].'</a></li>';
				break;
			}
		if(!$iscats)
		{			if($subcat>0)
			{
				if(!$subcatsb)
					$c.='</ul>';
				$c.='</td>';
			}
			for(;$num>0;$num--)
				$c.='<td></td>';
			if($num==0)
				$c.='</tr>';
			$c.='</table>';		}
		return static::TopMenu().Eleanor::$Template->OpenTable().$c.Eleanor::$Template->CloseTable();	}

	/*
		�������� ������ ���� �����
	*/
	public static function ShowAllTags()
	{		$tags=clone Eleanor::$Template;
		foreach($GLOBALS['Eleanor']->module['tags'] as &$v)
			$tags->Tag($v);
		return static::TopMenu(Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']]['tags_list']).Eleanor::$Template->OpenTable().'<span class="alltags">'.$tags.'</span>'.Eleanor::$Template->CloseTable();
	}

	/*
		�������� ������ �������� �� ������������ ����
		$data - ����
		$links - ������ ������, �����:
			first_page - ������ �� ������ �������� ����������
			pages - ������ ������ �� ��������� ��������
		�������� ��������� ���������� �������� � ������ List
	*/
	public static function TagsList($tag,$data,$cnt,$page,$pp,$links)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];
		return static::TopMenu(sprintf($lang['wt'],$tag['name']))
			.($data['items'] ? self::List_($data).Eleanor::$Template->Pages(array($cnt,ceil($cnt/$pp)=>$links['first_page']),$pp,-$page,$links['pages']) : Eleanor::$Template->Message(sprintf($lang['notag'],$tag['name']),'info'));	}

	/*
		�������� ������ ��������
		$values - �������� ����� ������ �����, ������ � �������:
			text - ��������� ������
			where - ��� ������: � ���������, � ��������� � ������, � ���������, ������ � ������ (t,ta,tat)
			tags - ������ �����
			categs - ������ ���������
			sort - ������� ���������� (date,relevance)
			c - ����� � ������� ��������� � ��� ��� (and,or)
			t - ����� � ������� ����� � ��� ��� (and,or)
		$error - ������, ���� ������, ������ ������ ���
		$md - ������������� ������
		$tags - ������ �����, ������ id=>��� ����
		�������� ��������� ���������� �������� � ������ List
	*/
	public static function Search($values,$error,$md,$tags,$data,$cnt,$page,$pp)
	{		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];		$tagopts='';
		foreach($tags as $k=>&$v)
			$tagopts.=Eleanor::Option($v,$k,in_array($k,$values['tags']));
		$Lst=Eleanor::LoadListTemplate('table-form');

		if($data and $data['items'])
		{			if($values['text'])
			{				$mw=preg_split('/\s+/',$values['text']);
				foreach($data['items'] as &$v)
				{
					$v['title']=Strings::MarkWords($mw,$v['title']);
					$v['text']=Strings::MarkWords($mw,$v['text']);
				}
			}
			$results='<br /><br />'.self::List_($data).Eleanor::$Template->Pages($cnt,$pp,$page,$GLOBALS['Eleanor']->Url->Construct(array('do'=>'search','md'=>$md,array('page'=>'{page}')),true,''));
		}
		else
			$results='';
		return static::TopMenu($lang['lookfor'])
			.($error ? Eleanor::$Template->Message($error,'error') : '')
			.($cnt===0 ? Eleanor::$Template->Message($lang['notfound'],'info') : '')
			.'<form method="post">'
			.$Lst->begin()
				->item($lang['text'],Eleanor::Edit('text',$values['text']))
				->item($lang['swhere'],Eleanor::Select('where',Eleanor::Option($lang['title'],'title',$values['where']=='t').Eleanor::Option($lang['ta'],'ta',$values['where']=='ta').Eleanor::Option($lang['tat'],'tat',$values['where']=='tat')))
				->item($lang['categs'],Eleanor::Items('categs',$GLOBALS['Eleanor']->Categories->GetOptions($values['categs'])).'<br /><label>'.Eleanor::Radio('c','and',$values['c']=='and').$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('c','or',$values['c']=='or').$lang['or'].'</label>')
				->item($lang['tags'],Eleanor::Items('tags',$tagopts).'<br /><label>'.Eleanor::Radio('t','and',$values['t']=='and').$lang['and'].'</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>'.Eleanor::Radio('t','or',$values['t']=='or').$lang['or'].'</label>')
				->item($lang['sortby'],Eleanor::Select('sort',Eleanor::Option($lang['sdate'],'date',$values['sort']=='date').Eleanor::Option($lang['srel'],'relevance',$values['sort']=='relevance')).'</label>')
				->button(Eleanor::Button($lang['find']))
				->end()
			.'</form>'
			.$results;
	}

	/*
		�������� ���������� ��������� �������
		$a - ������ �������, �����:
			id - ������������� ������� � ��
			date - ���� �������
			author - ��� ������ �������
			author_id - ������������� ������ �������
			status - ������ ������� (1 - �������, 0 - �������������, -1 - ������� ���������)
			reads - ����� ����������
			comments - ����� ������������
			title - ��������� �������
			announcement - ����� �������
			text - ��������� ����� �������
			_aedit - ������ �� �������������� �������, ���� false
			_adel - ������ �� �������� �������, ���� false
			_cat - ������������� �������� ��������� �������
			_sokr - ����� �������
			_tags - ������ ���� ����� ��������. ������: id=>array(), ����� ����������� �������:
				_a - ������ �� ������� ������� ����
				tag'=>$temp['name']),true,''),'name'=>$temp['name']);
		$category
			id - ������������� ���������
			title - �������� ���������
			description - �������� ���������
			_a - ������ �� ������� �� ������ ���������
		$rating - HTML �������� �������
		$voting - HTML ������ �������, ���� false
		$comments - HTML ������������
		$hl - ������ ����, ������� ���������� ���������� � �������
	*/
	public static function Show($a,$category,$rating,$voting,$comments,$hl)
	{		if($hl)
		{			$a['title']=Strings::MarkWords($mw,$a['title']);
			$a['text']=Strings::MarkWords($mw,$a['text']);
			if($a['announcement'])
				$a['announcement']=Strings::MarkWords($mw,$a['announcement']);
		}
		$tags='';
		foreach($a['_tags'] as &$v)
			$tags.='<a href="'.$v['_a'].'">'.$v['name'].'</a>, ';

		$lang=Eleanor::$Language[$GLOBALS['Eleanor']->module['config']['n']];

		switch($a['status'])
		{
			case-1:
				$status='<span style="font-weight:bold;color:darkyellow">'.$lang['waitmod'].'</span>';
			break;
			case 0:
				$status='<span style="font-weight:bold;">'.$lang['deactivated'].'</span>';
			break;
			default:
				$status=false;
		}

		return static::TopMenu()
			.Eleanor::$Template->Base(array(
				'top'=>array(
					'published'=>sprintf($lang['published_'],Eleanor::$Language->Date($a['date'],'fdt')),
					'category'=>$category ? sprintf($lang['category_'],'<a href="'.$category['_a'].'">'.$category['title'].'</a>') : false,
					'author'=>sprintf($lang['publisher_'],$a['author_id'] ? '<a href="'.Eleanor::$Login->UserLink($a['author'],$a['author_id']).'">'.$a['author'].'</a>' : $a['author']),
					'reads'=>sprintf($lang['reads_'],$a['reads']),
				),
				'bottom'=>array(
					'status'=>$status,
					'rating'=>$rating ? $rating : false,
					'edit'=>$a['_aedit'] ? Eleanor::$Template->EditDelete($a['_aedit'],$a['_adel']) : false,
				),
				'title'=>$a['title'],
				'text'=>($a['announcement'] ? $a['announcement'].'<a id="more"></a>' : '').$a['text'].($tags ? '<div class="tags">'.sprintf($lang['tags_'],rtrim($tags,', ')).'</div>' : '').($voting ? '<a id="voting"></a>'.$voting : ''),
			))
			.$comments;	}
}