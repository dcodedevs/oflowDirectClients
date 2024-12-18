<?php
$splitted = explode(":::",$field[11]);
$parameters = explode("::",strtoupper($splitted[1]));
$items = explode("::",$splitted[0]);
$structure = array();
foreach($items as $item)
{
	$structure[] = explode(":",$item);
}
?>
<div class="group_<?php echo $field_ui_id.($field[9]==1?" hide":"");?>">
	<span id="selections_<?php echo $field_ui_id;?>">
	<?php
	$groupSize = sizeof(explode("¤",$field[6][$langID]));
	for($i=0;$i<$groupSize;$i++)
	{
		?><a class="selection script<?php echo ($i==0?" select_active":"");?>" id="selection_<?php echo $field_ui_id."_".$i;?>" href="javascript:;" onclick="change_<?php echo $field_ui_id;?>(<?php echo $i;?>);"><?php echo ($i+1);?></a><?php
	}
	?>
	</span>
	<?php if($access >= 10 and $field[10] != 1 and !in_array("NOTOOLBOX",$parameters)){ ?>
	<input type="button" class="group_btn_<?php echo $field_ui_id;?>" onClick="add_<?php echo $field_ui_id;?>(); return false;" value="<?php echo $formText_Add_Types;?>" />
	<input type="button" class="group_btn_<?php echo $field_ui_id;?> remove_item" onClick="remove_<?php echo $field_ui_id;?>();" value="<?php echo $formText_delete_fieldtype;?>" />
	<input type="button" class="group_btn_<?php echo $field_ui_id;?> set_default" onClick="setDefault_<?php echo $field_ui_id;?>();" value="<?php echo $formText_SetAsDefault_fieldtype;?>" />
	<?php } ?>
	
	<div class="group_items_<?php echo $field_ui_id;?>">
		<div id="items_<?php echo $field_ui_id;?>">
		<?php
		$i = $num = $dispNum = 0;
		$numArray = array();
		if($field[6][$langID] != "")
		{
			$numArray = explode("¤",$field[6][$langID]);
			$data = array();
			foreach($structure as $key=>$obj)
			{
				$item = explode("¤",$fields[$fieldsStructure[$obj[1]]['index']][6][$langID]);
				for($z=0;$z<(sizeof($item));$z++)
				{
					$data[$z][$key]=$item[$z];
				}
			}
	
			foreach($data as $obj)
			{
				if($numArray[$i]!="")
				{
					$dispNum = $numArray[$i];
				} else {
					$dispNum = $i+1;
				}
				if($num < $dispNum) $num = $dispNum;
				?>
				<div class="items_div <?php if($i==0) print "items_active";?>" id="items_div_<?php echo $field_ui_id;?>_<?php echo $i;?>">
				<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>[]" value="<?php echo $dispNum;?>" />
				<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td>
				<?php
				$x = 0;
				foreach($structure as $item)
				{
					$item_ui = $fieldsStructure[$item[1]]['ui_id'.$ending];
					if($item[2]>0) $style = 'style="width:'.$item[2].'px;"';
					else $style = '';
					switch($fieldTypes[$item[1]])
					{
						case "GroupField":
							?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui.$i;?>" name="<?php echo $field[3].$item[1].$ending;?>[]" value="<?php echo htmlspecialchars($obj[$x]);?>" <?php echo $style.($access<10||$field[10]==1?' readonly="readonly"':'');?> /><div class="clear_both"></div></div><?php
							break;
						case "GroupCheckboxField":
							?><div><label><?php echo $item[0];?>: </label><input class="checkbox" type="checkbox" <?php echo $style.($obj[$x]==1?' checked':'').($access<10||$field[10]==1?' readonly="readonly"':'');?> /><input id="item_<?php echo $item_ui.$i;?>" class="real" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="<?php echo $obj[$x];?>" /><div class="clear_both"></div></div><?php
							break;
						case "GroupDropdownField":
							$options = explode('::',$fieldsStructure[$item[1]][11]);
							?><div><label><?php echo $item[0];?>: </label><select class="group" onChange="$('#item_<?php echo $item_ui.$i;?>').val($(this).val());" <?php echo $style.($access<10||$field[10]==1?' disabled':'');?>><?php
							foreach($options as $option)
							{
								$val = explode(":",$option);
								?><option value="<?php echo $val[0];?>"<?php echo ($obj[$x]==$val[0]?' selected="selected"':'');?>><?php echo $val[1];?></option><?php
							}
							?>
							</select>
							<input id="item_<?php echo $item_ui.$i;?>" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="<?php echo $obj[$x];?>" /><div class="clear_both"></div></div><?php
							break;
						default:
							?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui.$i;?>" name="<?php echo $field[3].$item[1].$ending;?>[]" value="<?php echo $obj[$x];?>" <?php echo $style.($access<10||$field[10]==1?' readonly="readonly"':'');?> /><div class="clear_both"></div></div><?php
					}
					$x++;
				}
				?>
				</td>
				</tr>
				</table>
				</div>
				<?php
				$i++;
			}
		}
		if($access>=10 and $field[10]!=1 and $i==0)
		{
			if($numArray[$i] > 0)
			{
				$dispNum = $numArray[$i];
			} else {
				$dispNum = $i+1;
			}
			if($num < $dispNum) $num = $dispNum;
			?>
			<div class="items_div items_active" id="items_div_<?php echo $field_ui_id;?>_<?php echo $i;?>" >
			<input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>[]" value="<?php echo $dispNum;?>" />
			<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td>
			<?php
			foreach($structure as $item)
			{
				$item_ui = $fieldsStructure[$item[1]]['ui_id'.$ending];
				if($item[2]>0) $style = 'style="width:'.$item[2].'px;"';
				else $style = '';
				switch($fieldTypes[$item[1]])
				{
					case "GroupField":
						?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui.$i;?>" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" <?php echo $style;?> /><div class="clear_both"></div></div><?php
						break;
					case "GroupCheckboxField":
						?><div><label><?php echo $item[0];?>: </label><input class="checkbox" type="checkbox" <?php echo $style;?> /><input id="item_<?php echo $item_ui.$i;?>" class="real" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="0" /><div class="clear_both"></div></div><?php
						break;
					case "GroupDropdownField":
						$options = explode('::',$fieldsStructure[$item[1]][11]);
						?><div><label><?php echo $item[0];?>: </label><select class="group" onChange="$('#item_<?php echo $item_ui.$i;?>').val($(this).val());" <?php echo $style;?>><?php
						foreach($options as $option)
						{
							$val = explode(":",$option);
							?><option value="<?php echo $val[0];?>"><?php echo $val[1];?></option><?php
						}
						?>
						</select>
						<input id="item_<?php echo $item_ui.$i;?>" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" /><div class="clear_both"></div></div><?php
						break;
					default:
						?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui.$i;?>" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" <?php echo $style;?> /><div class="clear_both"></div></div><?php
				}
			}
			?>
			</td>
			</tr>
			</table>
			</div>
			<?php
			$i++;
		}
		?>
		</div>
	</div>
	<input type="hidden" id="items_ids_<?php echo $field_ui_id;?>" value="<?php echo $i;?>" />
	<input type="hidden" id="items_num_<?php echo $field_ui_id;?>" value="<?php echo $num;?>" />
</div>
<style>
.group_<?php echo $field_ui_id;?> { border:1px solid #666666; padding:5px; margin-bottom:5px;}
.group_<?php echo $field_ui_id;?> input.button { width:auto; }
.group_btn_<?php echo $field_ui_id;?> { width:auto; }
.group_items_<?php echo $field_ui_id;?> { border:1px solid #999999; margin:5px 0 0 0;}
.group_items_<?php echo $field_ui_id;?> label { float:left; width:100px; display:block; }
#items_<?php echo $field_ui_id;?> input.text, #items_<?php echo $field_ui_id;?> input.group, #items_<?php echo $field_ui_id;?> select.group { width:70%; }
#items_<?php echo $field_ui_id;?> input.checkbox { width:auto; }
#items_<?php echo $field_ui_id;?> .items_div { _border: 1px solid #999999; margin: 3px; padding: 3px; background-color:#FFFFFF; display:none;}
#items_<?php echo $field_ui_id;?> .items_active { display:block; }
#items_<?php echo $field_ui_id;?> .items_div input { margin-right: 8px; }
#items_<?php echo $field_ui_id;?> .image_item img { margin-right:10px; vertical-align:middle;}
#items_<?php echo $field_ui_id;?> .image_name {  }
#items_<?php echo $field_ui_id;?> .label_group { margin-top:5px; padding:4px; background-color:#c8d6f6; border:1px solid #006; width:360px; }
#items_<?php echo $field_ui_id;?> .image_labels input { width:300px; }
#items_<?php echo $field_ui_id;?> .image_labels div { padding-top:3px; }
#items_<?php echo $field_ui_id;?> .remove_item { padding-right:5px; }
#selections_<?php echo $field_ui_id;?> .selection { padding:0 5px 0 5px; text-decoration:none; color:#000000;}
#selections_<?php echo $field_ui_id;?> .select_active { border:1px solid #999999; }
</style>
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
<?php if($access >= 10 and $field[10] != 1) { ?>
function add_<?php echo $field_ui_id;?>()
{
	var id = parseInt($("#items_ids_<?php echo $field_ui_id;?>").val());
	id = id + 1;
	var num = parseInt($("#items_num_<?php echo $field_ui_id;?>").val());
	num = num + 1;
	$("#items_<?php echo $field_ui_id;?>").append('<div class="items_div" id="items_div_<?php echo $field_ui_id;?>_'+id+'" ><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>[]" value="'+num+'" /><table width="100%" border="0" cellpadding="0" cellspacing="0"><tr><td><?php
			foreach($structure as $item)
			{
				$item_ui = $fieldsStructure[$item[1]]['ui_id'.$ending];
				if($item[2]>0) $style = 'style="width:'.$item[2].'px;"';
				else $style = '';
				switch($fieldTypes[$item[1]])
				{
					case "GroupField":
						?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui;?>'+id+'" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" <?php echo $style;?> /><div class="clear_both"></div></div><?php
						break;
					case "GroupCheckboxField":
						?><div><label><?php echo $item[0];?>: </label><input class="checkbox" type="checkbox" <?php echo $style;?> /><input id="item_<?php echo $item_ui;?>'+id+'" class="real" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="0" /><div class="clear_both"></div></div><?php
						break;
					case "GroupDropdownField":
						$options = explode('::',$fieldsStructure[$item[1]][11]);
						?><div><label><?php echo $item[0];?>: </label><select class="group" onChange="$(\'#item_<?php echo $item_ui;?>'+id+'\').val($(this).val());" <?php echo $style;?>><?php
						foreach($options as $option)
						{
							$val = explode(":",$option);
							?><option value="<?php echo $val[0];?>"><?php echo $val[1];?></option><?php
						}
						?></select><input id="item_<?php echo $item_ui;?>'+id+'" type="hidden" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" /><div class="clear_both"></div></div><?php
						break;
					default :
						?><div><label><?php echo $item[0];?>: </label><input class="group" type="text" id="item_<?php echo $item_ui;?>'+id+'" name="<?php echo $field[3].$item[1].$ending;?>[]" value="" <?php echo $style;?> /><div class="clear_both"></div></div><?php
				}
			}
			?></td></tr></table></div>');
			
	$("#selections_<?php echo $field_ui_id;?>").append('<a class="selection script" id="selection_<?php echo $field_ui_id;?>_'+id+'" href="javascript:;" onclick="change_<?php echo $field_ui_id;?>('+id+');"></a>');
	$("#selections_<?php echo $field_ui_id;?> .selection").each(function(index) {
		if(index==$("#selections_<?php echo $field_ui_id;?> .selection").size()-1) $(this).text($("#selections_<?php echo $field_ui_id;?> .selection").size());
	});
	change_<?php echo $field_ui_id;?>(id);
	
	$("#items_ids_<?php echo $field_ui_id;?>").val(id);
	$("#items_num_<?php echo $field_ui_id;?>").val(num);
	
	load_<?php echo $field_ui_id;?>();
	$(window).resize();
}
function strrpos_<?php echo $field_ui_id;?>(haystack, needle, offset)
{
	var i = -1;
	if (offset) {
		i = (haystack + '').slice(offset).lastIndexOf(needle);
		if (i !== -1) {
			i += offset;
		}
	} else {
		i = (haystack + '').lastIndexOf(needle);
	}
	return i >= 0 ? i : false;
}

function remove_<?php echo $field_ui_id;?>() {
	$("#items_<?php echo $field_ui_id;?> .items_active").remove();
	$("#selections_<?php echo $field_ui_id;?> .select_active").remove();
	var i = 1;
	$("#selections_<?php echo $field_ui_id;?> .selection").each(function(index) {
    if(i==1)
		{
			var id = $(this).attr("id").substring(strrpos_<?php echo $field_ui_id;?>($(this).attr("id"),'_')+1,$(this).attr("id").length);
			change_<?php echo $field_ui_id;?>(id);
		}
		$(this).text(i);
		i++;
	});
}
function setDefault_<?php echo $field_ui_id;?>()
{
	var cur = $("#selections_<?php echo $field_ui_id;?> .select_active").text();
	var grpsel = $("#selections_<?php echo $field_ui_id;?>").children();
	var newsel = $("#selections_<?php echo $field_ui_id;?>").children();
	
	var grpitem = $("#items_<?php echo $field_ui_id;?>").children();
	var newitem = $("#items_<?php echo $field_ui_id;?>").children();
	
	var cnt = grpsel.length;
	cur = cur - 1;
	
	var x = 1;
	newsel[0] = grpsel[cur];
	newitem[0] = grpitem[cur];
	for (var i = 0; i < cnt; i++) {
		if(i==cur) continue;
		newsel[x] = grpsel[i];
		newitem[x] = grpitem[i];
		x++;
	}
	$(grpsel).remove();
	$(grpitem).remove();
	$("#selections_<?php echo $field_ui_id;?>").append($(newsel));
	$("#items_<?php echo $field_ui_id;?>").append($(newitem));
	
	var i = 1;
	$("#selections_<?php echo $field_ui_id;?> .selection").each(function(index) {
    $(this).text(i);
		i++;
	});
}
function load_<?php echo $field_ui_id;?>() {
	$('#items_<?php echo $field_ui_id;?> input.checkbox').on('change', function() {
		if($(this).is(':checked'))
			$(this).next('input.real').val('1');
		else
			$(this).next('input.real').val('0');
	});
}
$(function() {
	load_<?php echo $field_ui_id;?>();
});
<?php } ?>
function change_<?php echo $field_ui_id;?>(id)
{
	$("#items_<?php echo $field_ui_id;?> .items_active").hide();
	$("#items_<?php echo $field_ui_id;?> .items_active").attr("class","items_div");
	$("#items_div_<?php echo $field_ui_id;?>_"+id).show();
	$("#items_div_<?php echo $field_ui_id;?>_"+id).attr("class","items_div items_active");
	$("#selections_<?php echo $field_ui_id;?> .select_active").attr("class","selection");
	$("#selection_<?php echo $field_ui_id;?>_"+id).attr("class","selection select_active");
}
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>