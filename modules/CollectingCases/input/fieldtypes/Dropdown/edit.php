<?php
$initial_set = false;
$options = explode("::",$field[11]);
?><select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" onChange="$('#<?php echo $field_ui_id;?>orig').val($(this).val());"<?php echo ($field[10]==1||$access<10?" disabled":"");?>><?php
foreach($options as $option)
{
	$val = explode(":",$option);
	if(!$initial_set && $field[6][$langID]=="")
	{
		$initial_set = true;
		$field[6][$langID] = $val[0];
	}
	?><option value="<?php echo $val[0];?>"<?php echo ($field[6][$langID]==$val[0]?' selected="selected"':'');?>><?php echo $val[1];?></option><?php
}
?></select>
<input id="<?php echo $field_ui_id;?>orig" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />