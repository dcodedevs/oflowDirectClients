<?php
if(!function_exists("remove_item_by_value")) include(__DIR__."/fn_remove_item_by_value.php");
$startsearch = 0;
$valueID = array();

$startname = $fields[$this->fieldNums[$f]][1]."level".$startsearch;
list($fieldtype,$rest) = explode(":",$fields[$this->fieldNums[$f]][11],2);

list($selectboxtype,$connectiontype) = split(",",str_replace(" ","",$fieldtype));
$selectboxheight = substr($selectboxtype,1);
$selecboxtype = substr($selectboxtype,0,1);
if($selectboxheight == '' && $selectboxtype == 'M')
	$selectboxheight = 5;
if($selectboxtype == '')
	$selectboxtype = 'S';

if($connectiontype == '')
	$connectiontype = 'L';

if($connectiontype == 'L')
{
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
} else {
	while(isset($_POST[$startname]))
	{
		if(($_POST[$startname] != 0 && $_POST[$startname] != "") || (is_array($_POST['startname']) && $_POST['startname'][0] != '')  )
		{
			$valueID = array_merge($valueID,$_POST[$startname]);
		}
		$startsearch++;
		$startname = $fields[$this->fieldNums[$f]][1]."level".$startsearch;
	}
	for($x=0;$x<count($valueID);$x++)
	{
		$valueID[$x] = substr($valueID[$x],strpos($valueID[$x],"_")+1);
	}
	//$valueID[] = $tempvalueID;
}
//print_r($valueID); exit;
$fields[$fieldPos][6][$this->langfields[$a]] = $valueID;
?>