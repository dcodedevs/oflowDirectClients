<div id="<?php echo $field_ui_id;?>_container">
<?php
$v_options = explode("::",$field[11]);
if($access>=10)
{
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
	print $field[6][$langID];
}
?>
</div>
<script type="text/javascript">
$(function(){
	$('#<?php echo $field_ui_id;?>').colorpicker({<?php
		$v_exceptions = array("true", "false", "null");
		if(count($v_options)>0)
		{
			foreach($v_options as $s_otion)
			{
				$v_item = explode(":",$s_otion);
				array_map('trim', $v_item);
				if($v_item[0] == '') continue;
				echo $v_item[0].": ".(in_array($v_item[1], $v_exceptions) ? $v_item[1] : "'".$v_item[1]."'").", ";
			}
		}
		?>inline: true,
		container: "#<?php echo $field_ui_id;?>_container"
	});
});
</script>