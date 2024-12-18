<?php
$field = $fields[$fieldsStructure[$fieldid]['index']];
//print_r($field);
$fieldWidth="";
if($field[16] != "" && is_numeric($field[16]))
{
	$fieldWidth = intval($field[16])."%";
}
?><div class="edit_field" <?php echo ($fieldWidth!="" ? 'style="width:'.$fieldWidth.';"':'');?>><?php
if($field[9] != 1)
{
	?>
	<div class="<?php echo ($textBeforeOrAbove==1?"oneinput":"twoinput");?>">
		<span class="fieldname">
			<strong class="input_title_wrapper"><?php
				echo $field[2];
			?></strong>
		</span>
		<?php if(strpos($submodule, "_accountconfig") > 0 || strpos($submodule, "_basisconfig") > 0) { ?>
			<span class="input_description_wrapper hoverElement" <?php if($field[17] != "") echo 'style="display: inline-block;"';?>>
				<span class="fas fa-info-circle "></span>
				<span class="hoveredInfo"><?php echo nl2br($field[17]);?></span>
			</span>
			<textarea class="input_description_hidden" name="input_description[<?php echo $field['index'];?>]"><?php echo br2nl($field[17]);?></textarea>
			<input type="hidden" class="input_title_hidden" name="input_title[<?php echo $field['index'];?>]" value="<?php echo $field[2]?>" />
			<?php if($variables->developeraccess > 5) { ?>
				<span class="glyphicon glyphicon-pencil edit_input_description" data-field-index="<?php echo $field['index']; ?>" data-field-description="<?php echo br2nl($field[17]); ?>" data-field-title="<?php echo $field[2]; ?>"></span>
			<?php } ?>
		<?php } ?>
	</div>
	<div class="<?php echo ($textBeforeOrAbove==1?"onefield":"twofield");?>">
	<?php
	if(isset($field[8]) && is_array($field[8]) && sizeof($field[8])>1)
	{
		?><ul class="nav nav-tabs"><?php
		foreach($field[8] as $x => $langID)
		{
			$ending = $langID;
			if($ending == "all") $ending = "";
			$field_ui_id = $field['ui_id'.$ending];
			?><li<?php echo ($x==0?' class="active"':'');?>><a href="#tab_<?php echo $field_ui_id;?>" class="script lang_<?php echo $langID;?>" data-toggle="tab"><?php echo $languageName[$langID];?></a></li><?php
		}
		?></ul><?php
	}
}
?><div class="tab-content"><?php
if(isset($field[8]) && is_array($field[8]))
foreach($field[8] as $x => $langID)
{
	$ending = $langID;
	if($ending == "all") $ending = "";

	$field_ui_id = $field['ui_id'.$ending];

	$field_attributes = ' class="'.$field_ui_id.' '.$field[1].' '.$langID.($field[14]==1?' mandatory':'').' form-control" data-lang="'.$langID.'" ';

	?><div id="tab_<?php echo $field_ui_id;?>" class="tab-pane<?php echo ($x==0?' active':'');?>"><?php
	if(isset($_GET['relationID']) && $_GET['submodule'].$_GET['relationfield'] == $field[1])
	{
		$field[6][$langID] = $_GET['relationID'];
	}
	if(isset($_GET['content_status']) && $field[0] == 'content_status')
	{
		$field[6][$langID] = $_GET['content_status'];
	}
	if($field[9] == 1) // HIDDEN field
	{
		if($field[0] != 'seotitle' and $field[0] != 'seodescription' and $field[0] != 'seourl')
		{
			if(is_file($extradir."/input/fieldtypes/".$field[4]."/editHidden.php"))
			{
				include($extradir."/input/fieldtypes/".$field[4]."/editHidden.php");
			} else {
				if($field[0] == 'moduleID' && $field[6][$langID] == '') $field[6][$langID] = $moduleID;
				?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>" /><?php
			}
		}
	}
	else if($field[10] == 1) // READONLY field
	{
		if(is_file($extradir."/input/fieldtypes/".$field[4]."/editReadonly.php"))
		{
			include($extradir."/input/fieldtypes/".$field[4]."/editReadonly.php");
		} else {
			?><input <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]);?>"/><?php
			print $field[6][$langID];
		}
	}
	else
	{
		include($extradir."/input/fieldtypes/".$field[4]."/edit.php");
	}
	?></div><?php
}
?></div><?php
if($field[9] != 1)
{
	?></div><div style="clear:both;"></div><?php
}
?></div>
