<?php
list($related_table, $related_field_to_copy, $link_field) = explode(":",$field[11]);
$related_table = $o_main->db_escape_name($related_table);

if($ID > 0 || isset($_GET['relationID']))
{
	$tmpID = ($ID > 0 ? $fields[$fieldsStructure[$link_field]['index']][6][$langID] : $_GET['relationID']);
	if($o_main->db->table_exists($related_table.'content'))
	{
		$sql = "SELECT $related_table.id cid, $related_table.*, {$related_table}content.* FROM $related_table JOIN {$related_table}content ON {$related_table}content.{$related_table}ID = $related_table.id AND {$related_table}content.languageID = ".$o_main->db->escape($langID)." WHERE $related_table.id = ".$o_main->db->escape($tmpID);
	} else {
		$sql = "SELECT $related_table.id cid, $related_table.* FROM $related_table WHERE $related_table.id = ".$o_main->db->escape($tmpID);
	}
	$row = array();
	$o_query = $o_main->db->query($sql);
	if($o_query && $o_query->num_rows()>0) $row = $o_query->row_array();
	$field[6][$langID] = $row[$related_field_to_copy];
}
if($field[9] == 1)
{
    ?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"<?php echo ($field[10]==1||$access<10?" readonly":"");?> /><?php
}
