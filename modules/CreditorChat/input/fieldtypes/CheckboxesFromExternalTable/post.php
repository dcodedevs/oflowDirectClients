<?php
if(!function_exists("save_checkboxes_from_external_table")) include(__DIR__."/fn_save_checkboxes_from_external_table.php");
$tablename = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);
$options = explode(":",$fields[$nums][11]);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[2] = $o_main->db_escape_name($options[2]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$options[6] = $o_main->db_escape_name($options[6]);

$values = array();
$o_main->db->query("DELETE FROM ".$options[3]." WHERE ".$options[4]." = ? AND contentTable = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."", array($basetable->ID, $tablename));
foreach($_POST[$fields[$nums][1]] as $item)
{
	if(!in_array($item,$values))
	{
		$values[] = $item;
		if($options[8]==1)
		{
			$sql = "SELECT c.".$options[1]." col0, c.".$options[6]." col1 FROM ".$options[0]." c WHERE c.".$options[1]." = ?".($o_main->multi_acc?" AND c.account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")."";
			$o_query = $o_main->db->query($sql, array($item));
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result_array() as $row)
				{
					if($row['col1'] > 0)
						$values = save_checkboxes_from_external_table($options, $row['col1'], $values);
				}
			}
		}
	}
}
foreach($values as $item)
{
	$o_main->db->query("INSERT INTO ".$options[3]."(".$options[4].",".$options[5].",contentTable".($o_main->multi_acc?", account_id":"").") VALUES(?,?,?".($o_main->multi_acc?", '".$o_main->db->escape_str($o_main->account_id)."'":"").")", array($basetable->ID, $item, $tablename));
}
