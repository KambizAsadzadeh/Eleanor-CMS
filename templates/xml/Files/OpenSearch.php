<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
defined('CMS\STARTED')||die;

global$Eleanor;
/** @var array $var_0 Данные OpenSearch */
$var_0+=[
	'shortname'=>Eleanor::$vars['site_name'],
	'description'=>isset($Eleanor->module['description'])
		? $Eleanor->module['description'] : Eleanor::$vars['site_description'],
	'image'=>\Eleanor\PROTOCOL.\Eleanor\PUNYCODE.\Eleanor\SITEDIR.'favicon.ico',

	'search_url'=>false,
	'suggestions_url'=>false,
];?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
<ShortName><?=htmlspecialchars($var_0['shortname'],ENT,\Eleanor\CHARSET,false)?></ShortName>
<Description><?=htmlspecialchars($var_0['description'],ENT,\Eleanor\CHARSET,false)?></Description>
<Image height="16" width="16" type="image/vnd.microsoft.icon"><?=$var_0['image']?></Image>
<InputEncoding><?=\Eleanor\CHARSET?></InputEncoding>
<?=$var_0['search_url'] ? '<Url type="text/html" template="'.$var_0['search_url'].'" />' : '',
$var_0['suggestions_url'] ? '<Url type="application/x-suggestions+json" template="'.$var_0['suggestions_url'].'" />' : ''?>
</OpenSearchDescription>