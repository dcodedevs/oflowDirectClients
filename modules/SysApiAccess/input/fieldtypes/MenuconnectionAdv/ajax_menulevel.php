<?php
include(__DIR__."/../../../../../dbConnect.php");
$selected = $v_menues = array();
if($_GET['menumodulemultiselect']==1)
{
	$o_result = mysql_query("SELECT * FROM moduledata WHERE type = '1';");
	while($v_row = mysql_fetch_array($o_result))
	{
		$v_menues[$v_row['id']] = $v_row['name'];
	}
}

if(strpos($_GET['parentID'],'_')!==false)
{
	$filter = 'IN (';
	$parents = explode(':',$_GET['parentID']);
	foreach($parents as $key => $parent)
	{
		list($parentLevel, $useID) = explode('_',$parent);
		if($key>0) $filter .= ',';
		$filter .= mysql_real_escape_string($useID);
	}
	$filter .= ')';
	$multi = true;
} else {
	if(strpos($_GET['parentID'],':')!==false)
	{
		$filter = " IN (".str_replace(":",",",$_GET['parentID']).")";
	} else {
		$filter = " = '".mysql_real_escape_string($_GET['parentID'])."'";
	}
}
$selecteds = explode(':',$_GET['selected']);
foreach($selecteds as $key => $item)
{
	list($cLevel, $id) = explode('_',$item);
	$selected[$id] = true;
}
$level = $_GET['level'];

$writeOut = "";
$outSQL = "SELECT child.id, child.name, child.parentlevelID, parent.name parentName FROM menulevel child, menulevel parent WHERE child.parentlevelID $filter AND child.level = '$level' AND parent.id = child.parentlevelID/* AND child.content_status < 2 AND parent.content_status < 2*/ ORDER BY child.moduleID, child.parentlevelID, child.sortnr;";
if($level == 0)
{
	$outSQL = "SELECT id, moduleID, name, parentlevelID FROM menulevel WHERE moduleID $filter AND level = '0'/* AND content_status < 2*/ ORDER BY moduleID, parentlevelID, sortnr;";
}
$o_result = mysql_query($outSQL);
while($v_row = mysql_fetch_array($o_result))
{
	if($writeOut != "")
	{
		$writeOut .= ";";
	}
	$writeOut .= (($multi or isset($_GET['multi'])) ? $v_row['parentlevelID'].'_' : '').$v_row['id'].":".($multi ? $v_row['parentName'].' - ' : '').(($v_row['parentlevelID']==0 && $_GET['menumodulemultiselect']==1) ? $v_menues[$v_row['moduleID']]." - " : "").$v_row['name'].":".($selected[$v_row['id']] ? 1 : 0);
}

if($writeOut == "")
{
	$writeOut = "NONE";
}

print $writeOut;
?>