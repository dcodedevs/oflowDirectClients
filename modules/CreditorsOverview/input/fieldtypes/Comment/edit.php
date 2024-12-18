<div class="field_<?php echo $field_ui_id;?>">
	<?php if($access >= 10 and $field[10] != 1) { ?>
	<a class="add script" href="javascript:;" onClick="add_<?php echo $field_ui_id;?>();"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo $formText_Add_Types;?></a>
	<?php } ?>
	<div id="items_<?php echo $field_ui_id;?>">
	<?php
	$i=0;
	if($field[6][$langID] != "")
	{
		$data = json_decode($field[6][$langID]);
		asort($data);
		rsort($data);
		foreach($data as $obj)
		{
			$obj[1] = html_entity_decode($obj[1]);
			?>
			<div class="comment" id="comment_<?php echo $field_ui_id."_".$i;?>">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td class="first">
					<span class="comment_date"><?php echo $obj[0].($obj[2]!=''?' - '.$obj[2]:'');?></span>
					<span class="comment_text"><?php echo $obj[1];?></span>
					<input type="hidden" name="<?php echo $field[1].$ending;?>_date[]" value="<?php echo htmlspecialchars($obj[0]);?>" />
					<input type="hidden" name="<?php echo $field[1].$ending;?>_comment[]" value="<?php echo htmlspecialchars($obj[1]);?>" />
					<input type="hidden" name="<?php echo $field[1].$ending;?>_user[]" value="<?php echo htmlspecialchars($obj[2]);?>" />
				</td>
				<?php if($access >= 10 and $field[10] != 1) { ?>
				<td class="second"><a class="remove script" href="javascript:;" onClick="remove_<?php echo $field_ui_id;?>('<?php echo $i;?>');" title="<?php echo $formText_delete_fieldtype;?>"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a>
				</td>
				<?php } ?>
			</tr>
			</table>
			</div>
			<?php
			$i++;
		}
	}
	?>
	</div>
</div>
<input id="ids_<?php echo $field_ui_id;?>" type="hidden" value="<?php echo $i;?>" />
<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" value="<?php echo $i;?>" />
<?php if($access >= 10 and $field[10] != 1) { ?>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
function add_<?php echo $field_ui_id;?>()
{
	var id = parseInt($("#ids_<?php echo $field_ui_id;?>").val()) + 1;
	$("#items_<?php echo $field_ui_id;?>").prepend('<div class="comment" id="comment_<?php echo $field_ui_id;?>_'+id+'"><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td class="first"><input type="hidden" name="<?php echo $field[1].$ending;?>_date[]" value="" /><textarea class="autosize_<?php echo $field_ui_id;?>" name="<?php echo $field[1].$ending;?>_comment[]"></textarea><input type="hidden" name="<?php echo $field[1].$ending;?>_user[]" value="<?php echo $variables->loggID;?>" /></td><td class="second"><a class="remove" href="javascript:;" onClick="remove_<?php echo $field_ui_id;?>(\''+id+'\');" title="<?php echo $formText_delete_fieldtype;?>"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></a></td></tr></table></div>');
	
	$('.autosize_<?php echo $field_ui_id;?>').keyup(function() {
    	autoresize_<?php echo $field_ui_id;?>(this);
	});
	$("#ids_<?php echo $field_ui_id;?>").val(id);
	$("#<?php echo $field_ui_id;?>").val($("#items_<?php echo $field_ui_id;?> .comment").length);
	fw_changes_made = true;
}
function remove_<?php echo $field_ui_id;?>(id)
{
	$("#comment_<?php echo $field_ui_id;?>_" + id).remove();
	$("#<?php echo $field_ui_id;?>").val($("#items_<?php echo $field_ui_id;?> .comment").length);
	fw_changes_made = true;
}
function autoresize_<?php echo $field_ui_id;?>(textarea) {
	textarea.style.height = '0px';
	var _h = textarea.scrollHeight;
	if(_h<40) _h = 40;
    textarea.style.height = _h + 'px';
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>
<?php } ?>
<style>
.field_<?php echo $field_ui_id;?> { border: 1px solid #666666; margin-bottom: 5px; padding:0 5px 5px 5px; }
.field_<?php echo $field_ui_id;?> .comment { border: 1px solid #999999; margin:5px 0 0 0; padding:3px; }
.field_<?php echo $field_ui_id;?> .comment .comment_date { padding-right:3%; font-size:10px; font-style:italic; line-height:16px; display:block; color:#999999; }
.field_<?php echo $field_ui_id;?> .comment .comment_text { line-height:16px; display:block; }
.field_<?php echo $field_ui_id;?> .comment textarea { width:98%; font-size:12px; }
.field_<?php echo $field_ui_id;?> .comment .first { width:90%; }
.field_<?php echo $field_ui_id;?> .comment .second { width:10%; }
.field_<?php echo $field_ui_id;?> .remove { float:right; padding-top:3px; padding-right:5px; }
.field_<?php echo $field_ui_id;?> .add { font-weight:bold; text-decoration:none; }
.field_<?php echo $field_ui_id;?> .add span { vertical-align: middle;}
</style>