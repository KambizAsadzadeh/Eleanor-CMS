<?php
return array(
	'callback'=>function($co)
	{
		while($a=$R->fetch_assoc())
			$opts[$a['id']]=$a['title_l'] ? Eleanor::FilterLangValues((array)unserialize($a['title_l'])) : '';
	},
);