<?php
/*
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
$full=__DIR__.'/settings/full.php';
include extension_loaded('ionCube Loader') && is_file($full) ? $full : __DIR__.'/settings/simple.php';