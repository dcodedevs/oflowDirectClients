<?php
if($ID==0)
{
	$field[6][$langID] = 0;
} else if($field[6][$langID] == "") {
	$field[6][$langID] = 0;
}
?>
<div class="btn-toolbar">
	<div class="btn-group" data-toggle="buttons">
		<?php
		if($field[6][$langID] <= 1)
		{
			?><label class="btn btn-default<?php echo ($field[6][$langID]==0 ? " active":"");?>"><input type="radio" value="0" onChange="$('#<?php echo $field_ui_id;?>').val(this.value);"><?php echo $formText_Active_fieldtype;?></label>
			<label class="btn btn-default<?php echo ($field[6][$langID]==1 ? " active":"");?>"><input type="radio" value="1" onChange="$('#<?php echo $field_ui_id;?>').val(this.value);"><?php echo $formText_Inactive_fieldtype;?></label><?php
		}
		if($field[6][$langID] == 2)
		{
			?><label class="btn btn-default<?php echo ($field[6][$langID]==2 ? " active":"");?>"><input type="radio" value="2" onChange="$('#<?php echo $field_ui_id;?>').val(this.value);"><?php echo $formText_Deleted_fieldtype;?></label><?php
		}
		if($field[6][$langID] == 3)
		{
			?><label class="btn btn-default<?php echo ($field[6][$langID]==3 ? " active":"");?>"><input type="radio" value="3" onChange="$('#<?php echo $field_ui_id;?>').val(this.value);"><?php echo $formText_History_fieldtype;?></label><?php
		}
		?>
	</div>
</div>
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo $field[6][$langID];?>" />