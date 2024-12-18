<?php
if($access>=10)
{
	?><textarea <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" name="<?php echo $field[1].$ending;?>"><?php echo $field[6][$langID];?></textarea>
	<script type="text/javascript">
	<?php if(isset($ob_javascript)) { ob_start(); } ?>
	$(function() {
		$('#<?php echo $field_ui_id;?>').keyup(function () {
			resize_<?php echo $field_ui_id;?>(this);
		});
		$('#<?php echo $field_ui_id;?>').each(function () {
			resize_<?php echo $field_ui_id;?>(this);
		});
	});
	function resize_<?php echo $field_ui_id;?>(textarea) {
		textarea.style.height = '0px';
		var _h = textarea.scrollHeight;
		if(_h<40) _h = 40;
		textarea.style.height = (_h+2) + 'px';
		$(window).resize();
	}
	<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
	</script><?php
} else {
	?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
	print $field[6][$langID];
}
?>