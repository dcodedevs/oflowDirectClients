<?php
if(empty($field[6][$langID])) $field[6][$langID] = $o_main->app_id;
?>
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />