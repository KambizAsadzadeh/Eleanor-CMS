<?php
/*
	Элемент шаблона. Вывод 2х кнопок для редактирования и удаления чего-либо

	@var ссылка на редактирование
	@var ссылка на удаление
*/
defined('CMS\STARTED')||die;
$ltpl=Eleanor::$Language['tpl'];
if(isset($var_0))
	echo'<a href="'.$var_0.'" title="'.$ltpl['edit'].'"><img src="templates/Audora/images/edit.png" /></a>';
if(isset($var_1))
	echo'<a href="'.$var_1.'" title="'.$ltpl['delete'].'"><img src="templates/Audora/images/delete.png" /></a>';