<?php
/*
	������� �������. ����� 2� ������ ��� �������������� � �������� ����-����

	@var ������ �� ��������������
	@var ������ �� ��������
*/
if(!defined('CMS'))die;
$ltpl=Eleanor::$Language['tpl'];
if(isset($v_0))
	echo'<a href="'.$v_0.'" title="'.$ltpl['edit'].'"><img src="templates/Audora/images/edit.png" /></a>';
if(isset($v_1))
	echo'<a href="'.$v_1.'" title="'.$ltpl['delete'].'"><img src="templates/Audora/images/delete.png" /></a>';