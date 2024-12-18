<?php
function remove_item_by_value($array, $val = '', $preserve_keys = true) 
{
	if (empty($array) || !is_array($array)) return false;

	if (!in_array($val, $array)) return $array;
	foreach($array as $key => $value) 
	{
		if ($value == $val) unset($array[$key]);
	}
	return ($preserve_keys === true) ? $array : array_values($array);

}
?>