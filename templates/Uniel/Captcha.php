<?php
/*
	������� �������: ����������� �����.

	@var array(
		name - ��� �����
		w - ������ �����
		h - ������ �����
		src - ������ �� ����������� �����
		s - ���������� �������� �����
	)
*/
if(!defined('CMS'))die;
echo'<img id="'.$name.'" src="'.$src.'" style="cursor:pointer;" width="'.$w.'" height="'.$h.'" alt="" title="'.Eleanor::$Language['tpl']['captcha'].'" onclick="this.a;if(!this.a)this.a=this.src;this.src=this.a+\'&amp;new=\'+Math.random()" />'.Eleanor::Control($name,'hidden',$s);