<?php
$initial_set = false;
if(!function_exists("dropdowntable_get_options")) include(__DIR__."/fn_dropdowntable_get_options.php");
$options = explode(":",$field[11]);
$dataTable = $o_main->db_escape_name($options[0]);
$dataID = $o_main->db_escape_name($options[1]);
$dataNames = explode(",",$options[2]);
foreach($dataNames as $l_key => $s_item) $dataNames[$l_key] = $o_main->db_escape_name($s_item);
$dataFilter = $options[3];
list($dataParentField,$dataParentLimit) = explode(",",$options[4]);
if(!$o_main->db->table_exists($dataTable))
{
	echo $formText_ConfigurationError_Fieldtype.': '.$formText_TableDoesNotExists_Fieldtype;
	return;
}
if(!isset($options[4]) or $options[4]=="" or ($dataParentField!="" and ($dataParentLimit != 1 or ($dataParentLimit==1 and $_GET['level']>0))))
{
	?><select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" onChange="$('#<?php echo $field_ui_id;?>orig').val($(this).val());"<?php echo ($field[10]==1||$access<10?" disabled":"");?>><?php
		if($options[6]==1)
		{
			$initial_set = true;
			?><option value=""><?php echo $formText_none_input;?></option><?php
		}
		$select_fields = array();
		$extra_join = "";
		$v_fields = $o_main->db->list_fields($dataTable);
		foreach($v_fields as $s_field)
		{
			if($s_field == $dataID) $select_fields[] = $dataTable.".".$s_field;
			if(in_array($s_field,$dataNames)) $select_fields[] = $dataTable.".".$s_field;
		}
		if(sizeof($dataNames)+1 > sizeof($select_fields))
		{
			$extra_join = "JOIN {$dataTable}content ON {$dataTable}content.{$dataTable}ID = $dataTable.id AND {$dataTable}content.languageID = ".$o_main->db->escape($s_default_output_language);
			$v_fields = $o_main->db->list_fields($dataTable.'content');
			foreach($v_fields as $s_field)
			{
				if($s_field == $dataID && strtolower($dataID) != "id") $select_fields[] = $dataTable."content.".$s_field;
				if(in_array($s_field,$dataNames)) $select_fields[] = $dataTable."content.".$s_field;
			}
		}
		$select_fields[] = $dataTable.".content_status";
		$contentSQL = "SELECT ".implode(",",$select_fields)." FROM $dataTable $extra_join WHERE 1=1";
		$sqlWhere = "";
		//TODO: ALI security_check - filter
		if($dataFilter!="") $sqlWhere .= " AND {$dataFilter}";
		if($dataParentField!="") $sqlWhere .= " AND $dataParentField = 0";
		$sqlWhere .= " AND $dataTable.content_status < 2";
		$sqlOrder = " ORDER BY ".$dataNames[0];
		$o_query = $o_main->db->query($contentSQL.$sqlWhere.$sqlOrder);
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			$outName = "";
			foreach($dataNames as $dataName) $outName .= $v_row[$dataName]." ";
			if(!$initial_set && $field[6][$langID]=="")
			{
				$initial_set = true;
				$field[6][$langID] = $v_row[$dataID];
			}
			?><option value="<?php echo $v_row[$dataID];?>"<?php echo ($v_row[$dataID] == $field[6][$langID] ? ' selected' : '');?>><?php echo $outName.($v_row['content_status']==1 ? ' - [inactive]':'');?></option><?php
			if($dataParentField!="" and ($dataParentLimit != 1 or ($dataParentLimit==1 and $_GET['level']>1)))
			{
				dropdowntable_get_options(1, $_GET['level'], $contentSQL, $dataParentField, $dataParentLimit, $v_row[$dataID], $dataID, $dataNames, $access, $field[6][$langID]);
			}
		}
	?></select>
	<input id="<?php echo $field_ui_id;?>orig" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
}
?>