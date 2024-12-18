<?php
if(!function_exists("remove_item_by_value")) include(__DIR__."/fn_remove_item_by_value.php");
$startsearch = 0;
$valueID = array();

$startname = $fields[$this->fieldNums[$f]][1]."level".$startsearch;

while(isset($_POST[$startname]))
{
	if(($_POST[$startname] != 0 && $_POST[$startname] != "") || (is_array($_POST['startname']))  )
	{
		$tempvalueID = $_POST[$startname];
	}
	for($x=0;$x<count($tempvalueID);$x++)
	{
		if(stristr($tempvalueID[$x],"_"))
			$parent = substr($tempvalueID[$x],0,strpos($tempvalueID[$x],"_"));
		else
			$parent = $tempvalue[$x];
		
		if(in_array($parent,$valueID))
		{
			$valueID = remove_item_by_value($valueID,$parent);
		}
		if(stristr($tempvalueID[$x],"_"))
			$tempvalueID[$x] = substr($tempvalueID[$x],strpos($tempvalueID[$x],"_")+1);
	}
	$startsearch++;
	$startname = $fields[$this->fieldNums[$f]][1]."level".$startsearch;
	$valueID = array_merge($valueID,$tempvalueID);
}

$fields[$fieldPos][6][$this->langfields[$a]] = "";
$fields[$fieldPos][30][$this->langfields[$a]] = $valueID;
?>