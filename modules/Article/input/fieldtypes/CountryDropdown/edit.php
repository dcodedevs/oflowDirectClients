<?php
if(!function_exists("APIconnectOpen")) include(__DIR__."/../../includes/APIconnect.php");
$data = json_decode(APIconnectOpen("countrylistget", array()),true);
$options = $data['data'];
?><select <?=$field_attributes;?> id="<?=$field_ui_id;?>" onChange="$('#<?=$field_ui_id;?>orig').val($(this).val());"<?=($field[10]==1||$access<10?" disabled":"");?>><?php
foreach($options as $option)
{
	if($field[6][$langID]=="") $field[6][$langID] = $option['countryID'];
	?><option value="<?=$option['countryID'];?>"<?=($field[6][$langID]==$option['countryID']?' selected="selected"':'');?>><?=$option['name'];?></option><?php
}
?></select>
<input id="<?=$field_ui_id;?>orig" type="hidden" name="<?=$field[1].$ending;?>" value="<?=$field[6][$langID];?>" />