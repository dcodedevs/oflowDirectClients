<?php
//$fieldPos is same as $this->fieldNums[$f]
$items = explode("::",$fields[$fieldPos][11]);
$structure = array();
foreach($items as $item)
{
	$structure[] = explode(":",$item);
}
// load items
$data = array();
foreach($structure as $obj)
{
	$key = $fieldName."_".$obj[1];
	if(array_key_exists($key,$_POST))
	{
		$i=0;
		foreach($_POST[$key] as $item)
		{
			$data[$i][] = htmlentities($item);
			$i++;
		}
	}
}

$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($data);
?> 