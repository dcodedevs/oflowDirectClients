<?php
// get single-language table
$s_single_table = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);

$options = explode(":",$fields[$nums][11]);
$options = array_map('trim',$options);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);

$counter =0;
$extrainputfield = '';
for($x=7;$x<=50;$x++)
{
	if($options[$x]!= '')
	{
		$extrainputfield .=", ".$options[$x];
		$counter = $x;
		 
	}
	else
		break;
}

if($options[6] != 1)
{
	$o_main->db->query("DELETE FROM {$options[3]} WHERE {$options[4]} = ? and contentTable = ?", array($basetable->ID, $s_single_table));
	for($x=0;$x<=count($_POST[$fields[$nums][1]]);$x++)
	{
		$extravalue = '';
		for($y=7;$y<=$counter;$y++)
		{
			$extravalue .=", ".$o_main->db->escape($_POST[$fields[$nums][1]."_".$options[$counter]][$x]);
		}
		$sql = "INSERT INTO {$options[3]}(sortnr, {$options[4]},{$options[5]}{$extrainputfield},contentTable) VALUES(".$o_main->db->escape($x).", ".$o_main->db->escape($basetable->ID).",".$o_main->db->escape($_POST[$fields[$nums][1]][$x]).$extravalue.",".$o_main->db->escape($s_single_table).");";
		$o_main->db->query($sql);
	}
}
