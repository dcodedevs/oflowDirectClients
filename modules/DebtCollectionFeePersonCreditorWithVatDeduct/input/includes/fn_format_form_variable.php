<?php
function format_form_variable($text, $group = '', $long = false)
{
	$return = "";
	$text = strtolower(preg_replace('/[^A-za-z0-9 ]+/', '',trim($text)));
	if($text!="")
	{
		$items = explode(" ", $text);
		foreach($items as $item)
		{
			$return .= ucfirst($item);
		}
		return '$'."form".($long?"Long":"")."Text_".$return.($group!=""?"_".$group:"");
	} else {
		return "";
	}
}
?>