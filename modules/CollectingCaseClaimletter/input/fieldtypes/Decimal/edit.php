<?php
//change decimal separator point->comma
$field[6][$langID] = str_replace(".",",",$field[6][$langID]);
?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" />