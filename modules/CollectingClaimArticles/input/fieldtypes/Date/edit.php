<?php
if($field[6][$langID] != "")
{
	$rearrange = explode("-",$field[6][$langID]);
	$field[6][$langID] = $rearrange[2].".".$rearrange[1].".".$rearrange[0];
} else {
	//$field[6][$langID] = "00.00.0000";
}
?>
<div class="input-group">
	<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="text" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"<?php echo ($field[6][$langID]==""?' title="00.00.0000"':'');?> maxlength="10" />
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
	$('#<?php echo $field_ui_id;?>').datepicker({
		dateFormat: "dd.mm.yy",
		changeMonth: true,
		changeYear: true,
		yearRange: '-100:+20',
		firstDay: 1
	});
	$('#<?php echo $field_ui_id;?>reset').on('click',function(){
		$('#<?php echo $field_ui_id;?>').val('00.00.0000');
	});
});
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>