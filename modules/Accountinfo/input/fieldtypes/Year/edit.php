<?php
if($access>=10)
{
	?><select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>"<?php echo ($field[10]==1?' readonly="readonly"':'');?> /><?php
	for($x=date('Y',mktime(0, 0, 0, date("m"), date("d"), date("Y")+1));$x>2005;$x--)
	{ 
		?><option value="<?php echo $x;?>" <?php echo ($field[6][$langID]==$x ? 'selected="selected"':'');?>><?php echo $x;?></option><?php
	}
	?></select>
	<style>.<?php echo $field_ui_id;?>{width:100px;}</style><?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
	print $field[6][$langID];
}
?>