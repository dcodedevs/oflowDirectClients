<?php
$initial_set = false;
//testdata - $field[11] = "customerID:id:customers:contactPerson:contactPName,contactPEmail";
list($relationField,$relationDataField,$dataTable,$dataField,$dataShowFields) = explode(":",$field[11]);
$relationField = $o_main->db_escape_name($relationField);
$relationDataField = $o_main->db_escape_name($relationDataField);
$dataTable = $o_main->db_escape_name($dataTable);
$dataField = $o_main->db_escape_name($dataField);
$v_items = explode(',', $dataShowFields);
foreach($v_items as $l_key => $s_item) $v_items[$l_key] = $o_main->db_escape_name($s_item);
$dataShowFields = implode(',', $v_items);

$relationSql="";
if($relationField!="" && $relationDataField!="")
{
	foreach($fields as $tmp)
	{
		if($tmp[0]==$relationField)
		{
			$relationSql = "AND $relationDataField = ".$o_main->db->escape($tmp[6][$langID]);
			break;
		}
	}
}
$sql = "SELECT $dataField, $dataShowFields, $dataTable.content_status FROM $dataTable ".($o_main->multi_acc?"WHERE $dataTable.account_id = '".$o_main->db->escape_str($o_main->account_id)."' AND": " WHERE")." $dataTable.content_status < 2 ".$relationSql.";";

$o_query = $o_main->db->query($sql);
?><select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" onChange="$('#<?php echo $field_ui_id;?>orig').val($(this).val());"<?php echo ($field[10]==1||$access<10?" disabled":"");?>><?php
if($o_query && $o_query->num_rows()>0)
{
	foreach($o_query->result_array() as $v_row)
	{
		$items = array();
		$keys = explode(",",$dataField.",".$dataShowFields);
		foreach($keys as $key)
		{
			$item2 = explode("¤",$v_row[$key]);
			for($i=0;$i<sizeof($item2);$i++)
			{
				$items[$i][] = $item2[$i];
			}
		}
		foreach($items as $item)
		{
			list($dropID, $rest) = explode(",",implode(",",$item),2);
			$dropArray = explode(",",$rest);
			if(!$initial_set && $field[6][$langID]=="")
			{
				$initial_set = true;
				$field[6][$langID] = $dropID;
			}
			?><option value="<?php echo $dropID;?>"<?php echo ($dropID == $field[6][$langID]?" selected":"");?>><?php echo implode(", ",$dropArray).($v_row['content_status']==1 ? ' - [inactive]':'');?></option><?php
		}
	}
}
?>
</select>
<input id="<?php echo $field_ui_id;?>orig" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />
<?php if($relationField!="" && array_key_exists($relationField,$fieldsStructure)) { ?>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function(){
	$("#<?php echo $fieldsStructure[$field[0]]['ui_id'.$ending];?>").on("change", function() {
		$.ajax({
			type: "GET",
			cache: false,
			url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_get_option_data.php",
			data: { filter : $(this).val(), param : "<?php echo implode(":",array($relationField,$relationDataField,$dataTable,$dataField,$dataShowFields));?>" },
			success: function(data) {
				$('#<?php echo $field_ui_id?>').html(data).trigger('change');
			}
		});
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>