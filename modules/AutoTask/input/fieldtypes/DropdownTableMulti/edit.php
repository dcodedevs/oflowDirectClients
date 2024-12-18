<?php
$options = explode(":",$field[11]);
$options[0] = $o_main->db_escape_name($options[0]);
$options[1] = $o_main->db_escape_name($options[1]);
$options[3] = $o_main->db_escape_name($options[3]);
$options[4] = $o_main->db_escape_name($options[4]);
$options[5] = $o_main->db_escape_name($options[5]);
$dataTable = $options[0];
$dataID = $options[1];
$dataNames = explode(",",$options[2]);
foreach($dataNames as $l_key => $s_item) $dataNames[$l_key] = $o_main->db_escape_name($s_item);
if(!$o_main->db->table_exists($options[3]))
{
	$b_table_created = $o_main->db->simple_query("CREATE TABLE `{$options[3]}` (
		`{$options[4]}` INT(11) NOT NULL,
		`{$options[5]}` INT(11) NOT NULL,
		`contentTable` CHAR(100) NOT NULL,
		INDEX `Relation` (`{$options[4]}`, `{$options[5]}`, `contentTable`)
	)");
	if(!$b_table_created)
	{
		echo $formText_RelationTableIsNotCreated_Fieldtype;
		return;
	}
}

// get single-language table
$s_single_table = ($basetable->multilanguage == 1 ? substr($basetable->name,0,-7) : $basetable->name);
$s_print_data = "";

$dataFields = array();
$extraJoin = "";
$v_fields = $o_main->db->list_fields($dataTable);
foreach($v_fields as $s_field)
{
	if($s_field == $dataID) $dataFields[] = $dataTable.".".$s_field;
	if(in_array($s_field,$dataNames)) $dataFields[] = $dataTable.".".$s_field;
}
if(sizeof($dataNames)+1 > sizeof($dataFields))
{
	$extraJoin = "JOIN {$dataTable}content ON {$dataTable}content.{$dataTable}ID = $dataTable.id AND {$dataTable}content.languageID = ".$o_main->db->escape($s_default_output_language);
	$v_fields = $o_main->db->list_fields($dataTable.'content');
	foreach($v_fields as $s_field)
	{
		if($s_field == $dataID && strtolower($dataID) != "id") $dataFields[] = $dataTable."content.".$s_field;
		if(in_array($s_field,$dataNames)) $dataFields[] = $dataTable."content.".$s_field;
	}
}
$dataFields[] = $dataTable.".content_status";
$contentSQL = "SELECT ".implode(",",$dataFields).", {$options[3]}.{$options[4]} FROM $dataTable LEFT OUTER JOIN {$options[3]} on $dataTable.{$options[1]} = {$options[3]}.{$options[5]} AND {$options[3]}.contentTable = '{$s_single_table}' AND {$options[3]}.{$options[4]} = ".$o_main->db->escape($ID)." $extraJoin WHERE 1=1";
$sqlWhere = " AND $dataTable.content_status < 2";
$sqlOrder = " ORDER BY ".$dataNames[0];

$value = array();
$o_query = $o_main->db->query($contentSQL.$sqlWhere.$sqlOrder);
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result_array() as $v_row)
	{
		reset($v_row);
		$col0 = key($v_row);
		next($v_row);
		$col1 = key($v_row);
		next($v_row);
		$col2 = key($v_row);
		end($v_row);         // move the internal pointer to the end of the array
		$lastKey = key($v_row);
		if($field[9]==1)
		{
			$s_print_data .= '<input type="hidden" name="'.$field[1].$ending.'[]" value="'.htmlspecialchars($v_row[$col0]).'"/>';
		} else if($access>=10 && $field[10]!=1)
		{
			$s_print_data .= '<option value="'.$v_row[$col0].'"'.($v_row[$lastKey]>0 ? ' selected="selected"':'').'>'.$v_row[$col1].($v_row['content_status']==1?' - [inactive]':'').'</option>';
		} else if($v_row[$col2]>0)
		{
			$s_print_data .= '<div>'.$v_row[$col1].($v_row['content_status']==1?' - [inactive]':'').'<input type="hidden" name="'.$field[1].$ending.'[]" value="'.htmlspecialchars($v_row[$col0]).'"/></div>';
		}
	}
}

if($field[9]==1)
{
	print $s_print_data;
} else if($access>=10 && $field[10]!=1)
{
	?><div><i><?php echo $formText_HoldCtrlKeyToSelectMultiple_fieldtype;?></i></div>
	<select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" size="10" name="<?php echo $field[1].$ending;?>[]" multiple="multiple"><?php echo $s_print_data;?></select><?php
} else {
	print $s_print_data;
}
?>