<?php
if($field[6][$langID]!="") $value = explode(":",$field[6][$langID]);
else $value = array('00','00');

if($access>=10)
{
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo $value[0].":".$value[1];?>" maxlength="5" />
	<style>
	.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
	.ui-timepicker-div dl { text-align: left; }
	.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
	.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
	.ui-timepicker-div td { font-size: 90%; }
	.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
	.<?php echo $field_ui_id;?> { width:100px; }
	</style>
	<script type="text/javascript">
	$(function() {
		<?php if(isset($ob_javascript)) { ob_start(); } ?>
		$('#<?php echo $field_ui_id;?>').timepicker({
			timeFormat: "hh:mm",
			controlType: 'select',
			hour: <?php echo $value[0];?>,
			minute: <?php echo $value[1];?>
		});
		<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	});
	</script>
	<?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
	print $value[0].":".$value[1];
}
?>