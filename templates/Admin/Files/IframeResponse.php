<?php
/**
	Eleanor CMS © 2016
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

/** Результат отработки iframe
 * @var string $var_0 URL возврата */

if($var_0):
?><script>if(parent===window)
		location.href="<?=htmlspecialchars_decode($var_0,ENT)?>";
	else
		parent.location.reload();
</script>
<?php else:?><script>if(parent===window)
		window.close();
	else
		$("#iframe",parent.document).modal("close");
</script>
<?php endif?>