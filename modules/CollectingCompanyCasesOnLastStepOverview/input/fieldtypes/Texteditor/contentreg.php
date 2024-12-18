<?php
$doc = new DOMDocument();
@$doc->loadHTML($_POST[$fieldName].' ');
foreach($doc->getElementsByTagName('img') as $tag)
{
	$s_src = $tag->getAttribute('src');
	$_POST[$fieldName] = str_replace('src="'.$s_src.'"', 'src="'.str_replace('/accounts/'.$_GET['accountname'].'/','',$s_src).'"', $_POST[$fieldName]);
}
foreach($doc->getElementsByTagName('a') as $tag)
{
	$s_href = $tag->getAttribute('href');
	if(strpos($s_href, '/accounts/'.$_GET['accountname'].'/') !== false)
	{
		$_POST[$fieldName] = str_replace('href="'.$s_href.'"', 'href="'.str_replace('/accounts/'.$_GET['accountname'].'/','',$s_href).'"', $_POST[$fieldName]);
	}
}
$fields[$fieldPos][6][$this->langfields[$a]] = $_POST[$fieldName];
?>