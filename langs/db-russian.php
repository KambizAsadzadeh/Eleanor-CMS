<?php
return array(
	#��� ������ Db #ToDo! ��� ������?
	'connect'=>function($p){return'���������� ������������ � ���� ������ '.$p['db'].($p['no'] ? ': <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #<b>'.$p['no'].'</b>)' : '.');},
	'query'=>function($p){return'SQL ������ ���������� ��������: <b>'.htmlspecialchars($p['error'],ELENT,CHARSET,false).'</b> (error #'.$p['no'].')';},
);