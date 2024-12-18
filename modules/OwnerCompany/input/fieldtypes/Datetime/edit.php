<?php
if($field[6][$langID] != "")
{
	$field[6][$langID] = date("d.m.Y H:i:s", strtotime($field[6][$langID]));
}
?>
<div class="input-group">
	<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"<?php echo ($field[6][$langID]==""?' title="00.00.0000 00:00:00"':'');?> placeholder="dd.mm.yyyy hh:ii:ss"/>
	<div class="input-group-addon"><span id="<?php echo $field_ui_id;?>reset" class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span></div>
</div>
<style>
/* css for timepicker */
.ui-datepicker { z-index:300 !important; }
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
#<?php echo $field_ui_id;?>reset { cursor:pointer; }
</style>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
$(function() {
	/*$('#<?php echo $field_ui_id;?>').datepicker({
		dateFormat: "dd.mm.yy hh:ii:ss",
		changeMonth: true,
		changeYear: true,
		yearRange: '-20:+20',
		firstDay: 1
	});
	.timepicker({
			timeFormat: "hh:mm",
			controlType: 'select',
			hour: <?php echo $value[0];?>,
			minute: <?php echo $value[1];?>
		});*/
	$('#<?php echo $field_ui_id;?>reset').on('click',function(){
		$('#<?php echo $field_ui_id;?>').val('00.00.0000 00:00:00');
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>