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
        var counter = parseInt($("#items_ids_<?php echo $field_ui_id;?>").val());
		var id = parseInt($("#items_ids_<?php echo $field_ui_id;?>").val())+1;
        
        var html ='<div class="list-group-item" id="items_div_<?php echo $field_ui_id;?>_'+id+'" ><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td><?php
		$x = 0;
		foreach($structure as $item)
		{
			switch($s_output_type)
			{
				case "columns":
					if($x>0) echo '</td><td>';
					echo '<b>'.($item[0]!=""?$item[0].": ":"").'</b></td><td>'; if($item[2] != '0'){ echo '<input type="text" name="'.$field[1].$ending."_".$item[1].'[]" value="" size="'.$item[2].'" />';} else{echo '<input type="checkbox" name="'.$field[1].$ending."_".$item[1].'#idreplace" value="1" style="width: 20px !important;"/> ';}
					break;
				default :
					echo '<div><b>'.($item[0]!=""?$item[0].": ":"").'</b></div>'; if($item[2] != '0'){ echo '<input type="text" name="'.$field[1].$ending."_".$item[1].'[]" value="" />';} else{echo '<input type="checkbox" style="width: 20px !important;" name="'.$field[1].$ending."_".$item[1].'#idreplace" value="1"  /> ';}
			}
			$x++;
		}
		?></td><td width="11%" class="text-right"><button type="button" class="btn btn-danger btn-xs" onClick="deleteFormElement_<?php echo $field_ui_id;?>(id);$(\'#items_div_<?php echo $field_ui_id;?>_'+id+'\').remove();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> <?php echo $formText_delete_fieldtype;?></button></td></tr></table></div>';
		$("#items_<?php echo $field_ui_id;?>").append(html.replace('#idreplace','_'+counter));
		
		$("#items_ids_<?php echo $field_ui_id;?>").val(id);
	}
    function deleteFormElement_<?php echo $field_ui_id;?>(deleteID)
	{
       
        $('#items_div_<?php echo $field_ui_id."_";?>'+deleteID).remove();
		var id = parseInt($("#items_ids_<?php echo $field_ui_id;?>").val())-1;
         
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
							if($item[2] != 0){?><input type="text" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" size="<?php echo $item[2];?>" /><?php }else{?><input type="checkbox" style="width: 20px !important;" name="<?php echo $field[1].$ending."_".$item[1].'_'.$i;?>" value="1" <?php if($obj[$x] == 1){ ?> checked <?php } ?>/><?php }
						} else {
							print $obj[$x];
							?><input type="hidden" name="<?php echo $field[1].$ending."#".$item[1];?>[]" value="<?php echo $obj[$x];?>" /><?php
						}
						break;
					default :
						?><div><b><?php echo ($item[0]!=""?$item[0].": ":"");?></b></div><?php
						if($access>=10 && $field[10]!=1)
						{
							if($item[2] != 0){?>?><input type="text" name="<?php echo $field[1].$ending."_".$item[1];?>[]" value="<?php echo $obj[$x];?>" size="<?php echo $item[2];?>" /><?php }else{?><input type="checkbox" name="<?php echo $field[1].$ending."_".$item[1].'_'.$i;?>" style="width: 20px !important;" value="1" <?php if($obj[$x] == 1){ ?> checked <?php } ?>/><?php }
						} else {
							print $obj[$x];
							?><input type="hidden" name="<?php echo $field[1].$ending."#".$item[1];?>[]" value="<?php echo $obj[$x];?>" /><?php
						}
				}
				$x++;
			}
			?></td><?php
			if($field[10] != 1 and $access >= 10)
			{
				?><td width="11%" class="text-right">
					<button type="button" class="btn btn-danger btn-xs" onClick="deleteFormElement_<?php echo $field_ui_id;?>('<?php print $i; ?>');">
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
 
<input type="hidden" id="items_ids_<?php echo $field_ui_id;?>" name="<?php echo $field[1].$ending.'_counter';?>" value="<?php echo $i;?>" />
<style type="text/css">
#items_<?php echo $field_ui_id;?> {}
</style>