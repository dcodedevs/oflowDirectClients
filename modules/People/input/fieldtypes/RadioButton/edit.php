<?php
$items = explode(":",$field[11]);

foreach($items as $key=>$value)
{
	print $value; ?><input name="<?php echo $field[1].$ending;?>_radio" onClick="$('#<?php echo $field_ui_id;?>').val($(this).val())" type="radio" value="<?php echo $key;?>" <?php echo ($field[6][$langID]==$key?" checked":"").($access<10||$field[10]==1?" disabled":"");?> style="width:20px;" />&nbsp;&nbsp;&nbsp;<?php

}
?>
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/>