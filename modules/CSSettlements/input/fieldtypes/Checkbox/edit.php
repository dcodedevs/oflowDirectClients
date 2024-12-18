<input <?php echo str_replace(" form-control","",$field_attributes);?> id="<?php echo $field_ui_id;?>" type="checkbox" name="<?php echo $field[1].$ending;?>" value="1"<?php echo ($field[6][$langID] == 1 ? " checked":"").($access < 10 || $field[10] == 1 ? " readonly":"");?> />
<style>
#<?php echo $field_ui_id;?> { width:20px !important; }
</style>