<?php
if($level > 0)
{
	$lastParent = $field[6][$langID];
	$parents = array($lastParent);
	$parentMenu = $moduleID;
	
	$s_sql = 'SELECT parentlevelID FROM menulevel WHERE id = ? AND content_status < 2 LIMIT 1';
	$o_query = $o_main->db->query($s_sql, array($lastParent));
	while($o_query && $o_query->num_rows()>0 && $row = $o_query->row_array())
	{
		if($row['parentlevelID'] > 0)
		{
			array_unshift($parents,$row['parentlevelID']);
			$lastParent = $row['parentlevelID'];
			$o_query = $o_main->db->query($s_sql, array($lastParent));
		} else {
			break;
		}
	}
	?><table border="0" cellspacing="0" cellpadding="0">
		<tr><?php
		for($t = 0; $t < $level; $t++)
		{
			$table = "menulevel";
			$extra_where = "";
			if($t > 0 && ($t - 1) < sizeof($parents))
			{
				$extra_where .= " AND $table.parentlevelID = ".$o_main->db->escape($parents[$t - 1]);
			}
			else if(($t - 1) >= sizeof($parents))
			{
				$extra_where .= " AND 1 = 2";
			}
			$extra_where .= " AND $table.content_status < 2";
			$sql = "SELECT $table.id cid, $table.*, {$table}content.* FROM $table JOIN {$table}content ON {$table}content.{$table}ID = $table.id AND {$table}content.languageID = '$s_default_output_language' WHERE $table.moduleID = ".$o_main->db->escape($parentMenu)." AND $table.level = ".$o_main->db->escape($t).$extra_where;
			
			$menu_settings = include_local(__DIR__."/../../settings/tables/menulevel.php");
			if($menu_settings['orderByField'] != "") $sql .= " ORDER BY $table.".$o_main->db_escape_name($menu_settings['orderByField']);
			
			$l_rows = 0;
			$o_query = $o_main->db->query($sql);
			if($o_query) $l_rows = $o_query->num_rows();
			?>            
			<td class="menudropdown"><?php
				?><select id="<?php echo $field_ui_id."level".$t;?>" <?php echo ($t < ($level - 1) ? "onChange=\"javascript:fill_menulevel_".$field_ui_id."('".$field_ui_id."level"."','".$t."');\"":"onchange=\"javascript:$('#".$field_ui_id."').val($(this).val());\"").($field[10]==1||$access<10?" disabled":"");?>>
				<option value="0"><?php echo ($l_rows==0 ? $formText_none_fieldtype : $formText_choose_fieldtype);?></option><?php
				if($l_rows>0)
				foreach($o_query->result_array() as $row)
				{
					?><option value="<?php echo $row['cid'];?>" <?php echo ($parents[$t]==$row['cid'] ? "selected":"");?>><?php echo $row[$menu_settings['prefieldInList']];?></option><?php
				}
				?></select><?php
			?></td><?php
		}	
		?>
		</tr>
	</table>
	<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	function fill_menulevel_<?php echo $field_ui_id;?>(field_ui_id, level)
	{
		level = Number(level);
		var selected_id = $("#"+field_ui_id+level).val();
		$("#<?php echo $field_ui_id;?>").val(selected_id);
		
		var obj_counter = level + 1;
		var obj = document.getElementById(field_ui_id + obj_counter);
		while(obj != null)
		{
			obj.options.length = 0;
			obj.options[0] = new Option("<?php echo $formText_none_fieldtype;?>",0);
			obj_counter++;
			obj = document.getElementById(field_ui_id + obj_counter);
		}
		$.ajax({
			type: "GET",
			cache: false,
			url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax_menulevel.php",
			data: { level: (level + 1), parentID: selected_id, s_default_output_language: '<?php echo $s_default_output_language;?>' },
			success: function(data) {
				if(data != "NONE")
				{
					var obj = document.getElementById(field_ui_id + (level + 1)),
					splitstring = data.explode(";"), subsplit;
					
					obj.options.length = 0;
					obj.options[obj.length] = new Option("<?php echo $formText_choose_fieldtype;?>",0);
							
					for(e = 0; e < splitstring.length; e++)
					{
						subsplit = splitstring[e].explode(":");
						obj.options[obj.length] = new Option(subsplit[1],subsplit[0]);
					}
				}
			}
		});
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script>
	<?php
} else {
	print $formText_NotAvailable_fieldtype;
}
?>