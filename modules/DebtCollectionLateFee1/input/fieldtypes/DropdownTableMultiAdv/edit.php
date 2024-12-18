<?php
$l_id = 1;
$options = explode(":",$field[11]);

$dropdown_table		= $o_main->db_escape_name($options[0]);
$dropdown_id		= $o_main->db_escape_name($options[1]);
$dropdown_name		= $o_main->db_escape_name($options[2]);
$rel_table			= strtolower($o_main->db_escape_name($options[3]));
$rel_content_field	= $o_main->db_escape_name($options[4]);
$rel_dropdown_field	= $o_main->db_escape_name($options[5]);
$rel_input_field	= $o_main->db_escape_name($options[6]);
$rel_dropdown_label	= ucwords(strtolower($options[7]));
$rel_input_label	= ucwords(strtolower($options[8]));

$o_main->db->query("CREATE TABLE IF NOT EXISTS ".$rel_table." (".$rel_dropdown_field." INT NOT NULL, ".$rel_content_field." INT NOT NULL, ".$rel_input_field." TEXT, INDEX rel_idx (".$rel_dropdown_field.", ".$rel_content_field."))");

$o_dropdown = $o_main->db->query("SELECT * FROM ".$dropdown_table.($o_main->multi_acc?" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." ORDER BY sortnr");
$o_links = $o_main->db->query("SELECT * FROM ".$rel_table." WHERE ".$rel_content_field." = ?", array($ID));
$b_readonly = (($field[10]!=1 && $access >= 10) ? false : true);
?>
<div class="<?php echo $field_ui_id;?>-wrapper">
	<div class="<?php echo $field_ui_id;?>-fields">
		<?php
		if($o_links->num_rows() <= 0)
		{
			?><div class="<?php echo $field_ui_id;?>-field" data-id="<?php echo $l_id;?>">
				<div class="<?php echo $field_ui_id;?>-size">
					<div class="<?php echo $field_ui_id;?>-label"><?php echo $rel_dropdown_label; ?></div>
					<div class="<?php echo $field_ui_id;?>-size-select-wrapper">
						<select class="<?php echo $field_ui_id;?>-size-select" name="<?php echo $field[1].$ending; ?>[]"<?php echo ($b_readonly?' disabled':'');?>>
							<option>
							<?php $formText_Select_Fieldtype." ".$rel_dropdown_label; ?>
							</option>
							<?php foreach ($o_dropdown->result() as $o_row) { ?>
							<option value="<?php echo $o_row->$dropdown_id; ?>"><?php echo $o_row->$dropdown_name; ?></option>
							<?php } ?>
						</select>
						<?php if(!$b_readonly) { ?><a href="#" class="script <?php echo $field_ui_id;?>-remove" data-id="1">x</a><?php } ?>
					</div>
				</div>
				<div class="<?php echo $field_ui_id;?>-quantity">
					<div class="<?php echo $field_ui_id;?>-label"><?php echo $rel_input_label; ?></div>
					<div class="<?php echo $field_ui_id;?>-quantity-input-wrapper">
						<input type="text" name="<?php echo $field[1].$ending; ?>_input[]" class="<?php echo $field_ui_id;?>-quantity-input"<?php echo ($b_readonly?' disabled':'');?> />
					</div>
				</div>
			</div><?php
		}
		foreach($o_links->result() as $o_value)
		{
			?><div class="<?php echo $field_ui_id;?>-field" data-id="<?php echo $i; ?>">
				<div class="<?php echo $field_ui_id;?>-size">
					<div class="<?php echo $field_ui_id;?>-label"><?php echo $rel_dropdown_label; ?></div>
					<div class="<?php echo $field_ui_id;?>-size-select-wrapper">
						<select class="<?php echo $field_ui_id;?>-size-select" name="<?php echo $field[1].$ending; ?>[]"<?php echo ($b_readonly?' disabled':'');?>>
							<option>
							<?php $formText_Select_Fieldtype." ".$rel_dropdown_label;?>
							</option>
							<?php foreach ($o_dropdown->result() as $o_row) { ?>
							<option value="<?php echo $o_row->$dropdown_id; ?>" <?php if($o_row->$dropdown_id == $o_value->$rel_dropdown_field) { echo "selected='selected'"; } ?>><?php echo $o_row->$dropdown_name; ?></option>
							<?php } ?>
						</select>
						<?php if(!$b_readonly) { ?><a href="#" class="script <?php echo $field_ui_id;?>-remove" data-id="<?php echo $i; ?>">x</a><?php } ?>
					</div>
				</div>
				<div class="<?php echo $field_ui_id;?>-quantity">
					<div class="<?php echo $field_ui_id;?>-label"><?php echo $rel_input_label; ?></div>
					<div class="<?php echo $field_ui_id;?>-quantity-input-wrapper">
						<input type="text" name="<?php echo $field[1].$ending; ?>_input[]" class="<?php echo $field_ui_id;?>-quantity-input" value="<?php echo $o_value->$rel_input_field; ?>"<?php echo ($b_readonly?' disabled':'');?> />
					</div>
				</div>
			</div>
			<?php
			$i++;
		}
		if(!$b_readonly)
		{
			?><div class="<?php echo $field_ui_id;?>-add-wrapper">
				<div class="<?php echo $field_ui_id;?>-add">+ <span>
					<?php $formText_Add_Fieldtype;?>
					</span></div>
			</div><?php
		}
		?>
	</div>
</div>
<style type="text/css">
.<?php echo $field_ui_id;?>-wrapper{float:left;width:100%}
.<?php echo $field_ui_id;?>-fields{float:left}
.<?php echo $field_ui_id;?>-field{float:left;width:142px;background:#fff;box-sizing:border-box;border:1px solid #eee;border-radius:3px;margin:0 15px 10px 0}
.<?php echo $field_ui_id;?>-size{float:left;width:100%;border-bottom:1px solid #eee;position:relative}
.<?php echo $field_ui_id;?>-label{float:left;width:35%;padding:10px;box-sizing:border-box;line-height:1;position:absolute;font-weight:700;top:50%;-webkit-transform:translateY(-50%);-ms-transform:translateY(-50%);transform:translateY(-50%)}
.<?php echo $field_ui_id;?>-quantity-input,.<?php echo $field_ui_id;?>-size-select{float:right;width:100%!important}
.<?php echo $field_ui_id;?>-quantity{float:left;width:100%;position:relative}
.<?php echo $field_ui_id;?>-add-wrapper{float:left;border:1px solid #ccc;width:128px;padding:10px;box-sizing:border-box;height:86px;position:relative}
.<?php echo $field_ui_id;?>-add{color:#089ded;font-size:22px;cursor:pointer;float:left;position:absolute;width:100%;text-align:center;left:0;top:50%;-webkit-transform:translateY(-50%);-ms-transform:translateY(-50%);transform:translateY(-50%)}
.<?php echo $field_ui_id;?>-add span{font-size:15px;display:inline-block;position:relative;top:-2px}
.<?php echo $field_ui_id;?>-quantity-input-wrapper,.<?php echo $field_ui_id;?>-size-select-wrapper{float:right;box-sizing:border-box;width:60%;border-left:1px solid #eee}
.<?php echo $field_ui_id;?>-quantity-input-wrapper{padding:10px}
.<?php echo $field_ui_id;?>-size-select-wrapper{background:#eee;padding:15px 10px 5px}
.<?php echo $field_ui_id;?>-remove{position:absolute;top:0;right:3px;color:#313131}
.<?php echo $field_ui_id;?>-remove:hover{text-decoration:none}
</style>
<script>
function remove_input() {
	$(".<?php echo $field_ui_id;?>-remove").click(function(e){
		e.preventDefault();
		<?php if(!$b_readonly) { ?>
		var data_id = $(this).attr("data-id");
		var input_field = $(".<?php echo $field_ui_id;?>-field");
		if(input_field.length <= 1)
		{
			input_field.find(".<?php echo $field_ui_id;?>-size-select").prop('selectedIndex', 0);
			input_field.find(".<?php echo $field_ui_id;?>-quantity-input").val("");
		} else {
			$(".<?php echo $field_ui_id;?>-field[data-id='"+data_id+"']").remove();
		}
		<?php } ?>
	});
}
$(document).ready(function(){
	<?php if(!$b_readonly) { ?>
	$(".<?php echo $field_ui_id;?>-add").click(function(e){
		var input_field = $(".<?php echo $field_ui_id;?>-field").first(),
		next_id = parseInt($(".<?php echo $field_ui_id;?>-fields").find(".<?php echo $field_ui_id;?>-field").last().find(".<?php echo $field_ui_id;?>-remove").attr("data-id"))+1;
		input_field.clone(true).appendTo(".<?php echo $field_ui_id;?>-fields").find(".<?php echo $field_ui_id;?>-quantity-input").val("");
		$(".<?php echo $field_ui_id;?>-field").last().find(".<?php echo $field_ui_id;?>-size-select").prop('selectedIndex', 0);
		$(".<?php echo $field_ui_id;?>-fields").find(".<?php echo $field_ui_id;?>-field").last().find(".<?php echo $field_ui_id;?>-remove").attr("data-id", next_id);
		$(".<?php echo $field_ui_id;?>-fields").find(".<?php echo $field_ui_id;?>-field").last().attr("data-id", next_id);
		var input_fields    = $(".<?php echo $field_ui_id;?>-fields");
		var add_input_field = $(".<?php echo $field_ui_id;?>-add-wrapper");
		$(".<?php echo $field_ui_id;?>-add-wrapper").clone(true).appendTo(input_fields);
		add_input_field.first().remove();
	});
	<?php } ?>
	remove_input();
});
</script>