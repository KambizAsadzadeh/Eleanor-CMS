<?php
/**
	Eleanor CMS © 2014
	http://eleanor-cms.ru
	info@eleanor-cms.ru
*/
namespace CMS;
use Eleanor\Classes\Output;

defined('CMS\STARTED')||die;

if(Eleanor::$service=='xml')
{
	$out=Eleanor::$Template->OpenSearch(['shortname'=>Eleanor::$vars[ 'site_name' ],
		'search_url'=>'http://google.com/search?q=site:'.\Eleanor\PUNYCODE.'%20{searchTerms}'],null);
	//$out=XML($out);

	Output::SendHeaders('xml');
	Output::Gzip($out);
}