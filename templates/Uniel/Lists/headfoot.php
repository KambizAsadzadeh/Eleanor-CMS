<?php
$base=include Eleanor::$root.'templates/Audora/Lists/headfoot.php';

#���������� Open Graph ��� ��������� ������ ������ �������� http://ogp.me/
return $base+array(
	'og'=>'<meta property="og:{0}" content="{1}" />',
);