<?php
/*
	������� �������: ����� "����� ������" � ��������� � ����.

	@var array(
		title - �������� � �����
		menu - ������ ��������� ���� �� ���������. ������ ������� - ������ � �������:
			0 - ������ ������ ����, ���� false
			1 - ����� �������� ����
			addon - ������ �������������� ���������� ���� a, ����
			submenu - ����������� ������ ����������� ���� ��������
	)
*/
if(!defined('CMS'))die;
$mainmenu='';
if(isset($menu))
{
	if(!function_exists('TPLFMenu'))
	{
		function TPLFMenu(array$menu)
		{
			$c='';
			foreach($menu as &$v)
				if(is_array($v) and $v)
				{					if(!empty($v['act']) and !isset($v['addon']['class']))
						$v['addon']['class']='active';
					$a=isset($v['addon']) ? Eleanor::TagParams($v['addon']) : '';
					$c.='<li>'.($v[0]===false ? '<span'.$a.'>'.$v[1].'</span>' : '<a href="'.$v[0].'"'.$a.'>'.$v[1].'</a>')
						.(empty($v['submenu']) ? '' : '<ul>'.TPLFMenu($v['submenu']).'</ul>')
						.'</li>';
				}
			return$c;
		}
	}
	$menu=TPLFMenu($menu);
	if($menu)
	{
		$GLOBALS['jscripts'][]='js/menu_multilevel.js';
		$u=uniqid();
		$mainmenu='<ul id="menu-'.$u.'" class="modulemenu">'.$menu.'</ul><script type="text/javascript">/*<![CDATA[*/$(function(){$("#menu-'.$u.'").MultiLevelMenu();});//]]></script>';
	}
}
?>
<div class="base">
	<div class="heading2"><div class="binner">
		<h6><?php echo$title?></h6>
		<div class="clr"></div>
	</div></div>
	<nav><?php echo$mainmenu?></nav>
</div>