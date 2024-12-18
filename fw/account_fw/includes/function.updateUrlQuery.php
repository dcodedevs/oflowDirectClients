<?php
function updateUrlQuery($url, $replaceItem, $removeItem)
{
 	$vUrl = parse_url($url);
	parse_str($vUrl['query'], $vQuery);
	foreach($removeItem as $value)
	{
		unset($vQuery[$value]);
	}
	foreach($replaceItem as $key => $value)
	{
		$vQuery[$key] = $value;
	}
	$vUrl['query'] = $vQuery;
	
	return ((isset($vUrl['scheme']) && $vUrl['scheme']!="")?$vUrl['scheme'].'://':'').$vUrl['host'].((isset($vUrl['port']) && $vUrl['port']!="")?':'.$vUrl['port']:'').$vUrl['path'].'?'.urldecode(http_build_query($vUrl['query']));
}
?>