<?php
//$str = "Article Nr:articlenumber:7::Name:name:20::NOK:price1:5::EUR:price2:5[:]columns";
$v_items = explode("[:]", $field[11]);
$s_output_type = (isset($v_items[1]) ? strtolower($v_items[1]) : '');
$v_items = explode("::", $v_items[0]);
$structure = array();
foreach($v_items as $s_item)
{
	$structure[] = explode(":",$s_item);
}
if($field[9] != 1 && $field[10] != 1 and $access >= 10)
{
	?><script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	$(function(){
		$("#items_<?php echo $field_ui_id;?>").sortable();
	});
	function addFormElement_<?php echo $field_ui_id;?>()
	{
		var id = parseInt($("#items_ids_<?php echo $field_ui_id;?>").val())+1;
		$("#items_<?php echo $field_ui_id;?>").append('<div class="list-group-item" id="items_div_<?php echo $field_ui_id;?>_'+id+'" ><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td><?php
		$x = 0;
		foreach($structure as $item)
		{
			switch($s_output_type)
			{
				case "columns":
					if($x>0) echo '</td><td>';
					echo '<b>'.($item[0]!=""?$item[0].": ":"").'</b></td><td><input type="text" name="'.$field[1].$ending."_".$item[1].'[]" value="" size="'.$item[2].'" />';
					break;
				default :
					echo '<div><b>'.($item[0]!=""?$item[0].": ":"").'</b></div><input type="text" name="'.$field[1].$ending."_".$item[1].'[]" value="" />';
			}
			$x++;
		}
		?></td><td width="11%" class="text-right"><button type="button" class="btn btn-danger btn-xs" onClick="$(\'#items_div_<?php echo $field_ui_id;?>_'+id+'\').remove();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> <?php echo $formText_delete_fieldtype;?></button></td></tr></table></div>');
		
		$("#items_ids_<?php echo $field_ui_id;?>").val(id);
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php
}
 
if($field[9] != 1 && $field[10] != 1 and $access >= 10)
{
	?><div style="margin-bottom:3px;">
	<button type="button" class="btn btn-success btn-xs" onClick="addFormElement_<?php echo $field_ui_id;?>();">
		<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo $formText_Add_Types;?>
	</button>
	<span style="padding-left:20px;"><?php echo $formText_DragAndDropItemsToChangeOrder_fieldtype;?></span>
	</div><?php
}
?>
<div id="items_<?php echo $field_ui_id;?>" class="list-group" <?php if($field[9]==1){?>style="display:none;"<?php }?>>
	<?php
	$i=0;
	if($field[6][$langID] != "")
	{
		$data = json_decode($field[6][$langID]);
		//print_r($data);
		foreach($data as $obj)
		{
			?>
			<div class="list-group-item" id="items_div_<?php echo $field_ui_id."_".$i;?>">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr><td>
			<?php
			$x = 0;
			foreach($structure as $item)
			{
				$obj[$x]= htmlspecialchars(html_entity_decode($obj[$x]));
				switch($s_output_type)
				{
					case "columns":
						if($x>0) echo '</td><td>';
						?><b><?php echo ($item[0]!=""?$item[0].": ":"");?></b><?php
						echo '</td><td>';
						if($access>=10 && $field[10]!=1)
						{
							?><input type="text" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" size="<?php echo $item[2];?>" /><?php
						} else {
							print $obj[$x];
							?><input type="hidden" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" /><?php
						}
						break;
					default :
						?><div><b><?php echo ($item[0]!=""?$item[0].": ":"");?></b></div><?php
						if($access>=10 && $field[10]!=1)
						{
							?><input type="text" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" size="<?php echo $item[2];?>" /><?php
						} else {
							print $obj[$x];
							?><input type="hidden" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" /><?php
						}
				}
				$x++;
			}
			?></td><?php
			if($field[10] != 1 and $access >= 10)
			{
				?><td width="11%" class="text-right">
					<button type="button" class="btn btn-danger btn-xs" onClick="$('#items_div_<?php echo $field_ui_id."_".$i;?>').remove();">
						<span class="glyphicon glyphicon-remove" aria-hidden="true"></span> <?php echo $formText_delete_fieldtype;?>
					</button>
				</td><?php
			}
			?></tr>
			</table>
			</div>
			<?php
			$i++;
		}
	}
	?>
</div>
<input type="hidden" id="items_ids_<?php echo $field_ui_id;?>" value="<?php echo $i;?>" />
<style type="text/css">
#items_<?php echo $field_ui_id;?> {}
</style>