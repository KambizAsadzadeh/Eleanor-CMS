<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Результат отработки iframe
 * @var string $var_0 URL возврата */
?><script>if(parent===window)
		location.href="<?=htmlspecialchars_decode($var_0,ENT)?>";
	else
		parent.location.reload();
</script>