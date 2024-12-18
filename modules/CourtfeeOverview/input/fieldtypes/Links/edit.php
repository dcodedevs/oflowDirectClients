<?php
if(!function_exists('include_local')) include(__DIR__.'/../../includes/fn_include_local.php');
$val = explode("#",$field[6][$langID]);
$urlPrefix = 'http://';
$field[11] = strtolower($field[11]);
$extra = $mode = '';
$options = explode(':',$field[11]);
if ( $options[0] != '' )
{
	$mode = explode(',', $options[0]);
	if ( $mode[0] != '' )
	{
		if ( in_array($mode[0], array('s','e')) )
		{
			if ( isset($options[1]) )
			{
				if ( ctype_digit(str_replace(',', '', $options[1])) )
				{
					$extra = ' AND id '.($mode[0]=='e'?'NOT':'').' IN ('.$options[1].')';
				}
			}
		}
	}
	if ( isset($mode[1]) )
	{
		if ( !in_array($mode[1], array('1','2','3')) )
		{
			$mode[1] = 3;
		}
	} else {
		$mode[1] = 3;
	}
}
$anchor = false;
if ( isset($options[2]) )
{
	if ( $options[2] == 'a' )
	{
		$anchor = true;
	}
}
$cnt = 0;

$v_content_modules = array();
$o_query = $o_main->db->query("SELECT DISTINCT contentTable FROM pageID");
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result() as $o_row)
{
	$o_find = $o_main->db->query("SELECT DISTINCT moduleID FROM ".$o_row->contentTable);
	if($o_find && $o_find->num_rows()>0)
	foreach($o_find->result() as $o_row)
	{
		$v_content_modules[] = $o_row->moduleID;
	}
}

$s_sql = 'SELECT id, name FROM moduledata WHERE modulemode = "C" AND id IN ? '.$extra.';';
$o_query = $o_main->db->query($s_sql, array($v_content_modules));
if($o_query) $cnt = $o_query->num_rows();
?><div class="leftBlock" style="width:40%; float:left;">
	<div class="fieldname"><?php echo $formText_chooseActionTitle_fieldtype;?></div>
	<select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>d1"<?php if( $mode[1] == 2 || $mode[1] == 3 ) { ?> onChange="getModuleData_<?php echo $field_ui_id;?>($(this).val());"<?php } echo ($field[10]==1||$access<10||$mode[1]==1?" disabled":"");?>>
		<option value="0"<?php echo ($val[0]==0?' selected="selected"':'');?>><?php
		echo ($mode[1]==2?$formText_chooseModuleTitle_fieldtype:'');
		echo ($mode[1]==1||$mode[1]==3?$formText_TypeExistingUrl_fieldtype:'');
		?></option><?php
		$existid = false;
		if ( $cnt > 0 )
		{
			foreach($o_query->result_array() as $dat)
			{
                 
                
                $dir = __DIR__.'/../../../../'.$dat['name'].'/input/settings/tables/';
                $langfile = __DIR__.'/../../../../'.$dat['name'].'/input/languagesInput/'.$variables->languageID.'.php';
                 
                $v_lang_variables = include_local($langfile);
                $filelist = glob($dir."*.php");
                foreach($filelist as $key => $value)
                {
                    $v_table_settings = include_local($value, $v_lang_variables);
                    if($v_table_settings['tableordernr'] == 1)
                    {
                        if($v_table_settings['preinputformName'] != '')
						{
                            $dat['name'] =   $v_table_settings['preinputformName'];
						}
                    }
                }
                 
	           unset($langfile);
                 
				if ( $val[0] == $dat['id'] ) { $existid = true; }
				?><option value="<?php echo $dat['id'];?>"<?php echo ($val[0]==$dat['id']?' selected="selected"':'');?>><?php echo $dat['name'];?></option><?php
			}

			if ( $val[0] == "list" ) { $existid = true; }
			?>
			<option value="list"<?php echo ($val[0]=="list"?' selected="selected"':'');?>><?php echo $formText_ListPages_Fieldtype;?></option>
			<?php
		}
	?></select>
</div>
<div class="rightBlock" style="width:60%; float:left;">
<div id="<?php echo $field_ui_id;?>extUrlBox" style="display:<?php echo ($mode[1]==2?'none':'block'); ?>;">
	<div class="fieldname"><?php echo $formText_typeExternalUrl_fieldtype;?></div>
	<input id="<?php echo $field_ui_id;?>Url" type="text" name="<?php echo $field[1].$ending;?>Url" value="<?php echo htmlspecialchars($field[6][$langID]); ?>" style="height: 34px; padding: 6px 12px;" onChange="$('#<?php echo $field_ui_id;?>orig').val($(this).val());" />
</div><?php
if ( $mode[1] == 2 || $mode[1] == 3 )
{
	?><div id="<?php echo $field_ui_id;?>pageBox" style="display:<?php echo ($mode[1]==3?'none':'block'); ?>;">
		<div class="fieldname"><?php echo $formText_choosePage_fieldtype;?></div>
		<select <?php echo $field_attributes;?> id="<?php echo $field_ui_id;?>d2" onChange="setModulePageID_<?php echo $field_ui_id;?>($(this).val())" disabled>
			<option value="0"<?php echo ($val[0]==0?' selected="selected"':'');?>><?php echo $formText_choosePage_fieldtype;?></option><?php
		?></select>
	</div>
	<script type="text/javascript">
	function setModulePageID_<?php echo $field_ui_id;?>(id){
		if ( id == 0 ){
			$('#<?php echo $field_ui_id;?>Anchor').attr('disabled', '');
		} else {
			$('#<?php echo $field_ui_id;?>orig').val(id+'#'+$('#<?php echo $field_ui_id;?>Anchor').val());
			$('#<?php echo $field_ui_id;?>Anchor').removeAttr('disabled');
		}
	}
	function getModuleData_<?php echo $field_ui_id;?>(id){
		if ( id == 0 ){
			$('#<?php echo $field_ui_id;?>d2').attr('disabled', '');
			$('#<?php echo $field_ui_id;?>orig').val('<?php echo $urlPrefix; ?>');
			$('#<?php echo $field_ui_id;?>Url').val('<?php echo $urlPrefix; ?>');
			$('#<?php echo $field_ui_id;?>extUrlBox').show();
			$('#<?php echo $field_ui_id;?>pageBox').hide();
			$('#<?php echo $field_ui_id;?>AnchorBox').hide();
		} else {
			$('#<?php echo $field_ui_id;?>extUrlBox').hide();
			$('#<?php echo $field_ui_id;?>pageBox').show();<?php
			if ( $anchor )
			{
				?>$('#<?php echo $field_ui_id;?>AnchorBox').show();<?php
			}
			?>$('#<?php echo $field_ui_id;?>Anchor').attr('disabled', '');
			$.ajax({
				url: "<?php echo $extradir;?>/input/fieldtypes/<?php echo $field[4];?>/ajax.getData.php?val="+id,
				dataType: 'text',
				cache: false,
				success: function(html) {
					$('#<?php echo $field_ui_id;?>d2').find('option').remove().end().append($("<option></option>").attr("value",'0').text('<?php echo $formText_choosePage_fieldtype;?>'));
					var splitedArr = html.split('#');
					splitedArr.forEach(function(item, i, arr) {
						itemArr = item.split(':');
						$('#<?php echo $field_ui_id;?>d2').append($("<option></option>").attr("value",id+'#'+itemArr[0]).text(itemArr[1]));
					});<?php
					if ( $val[0] !== 0 && $val[1] !== 0 )
					{
						?>
						$('select#<?php echo $field_ui_id;?>d2 option').each(function(){
							if ( $(this).val() == '<?php echo $val[0].'#'.$val[1]; ?>' ) {
								$(this).attr("selected","selected");    
							}
						});<?php
					}
					?>
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
			$('#<?php echo $field_ui_id;?>d2').removeAttr('disabled');
		}
	}<?php
	if ( $val[0] !== 0 && $val[1] !== 0 && $existid )
	{
		?>getModuleData_<?php echo $field_ui_id;?>("<?php echo $val[0]; ?>");
		$('#<?php echo $field_ui_id;?>Anchor').removeAttr('disabled');<?php
		if ( $anchor )
		{
			?>$('#<?php echo $field_ui_id;?>AnchorBox').hide();<?php
		}
	}
	?>
	</script>
	<div id="<?php echo $field_ui_id;?>AnchorBox" style="display:<?php
	if ( $anchor )
	{
		echo 'block';
	} else {
		echo 'none';
	}
	?>;">
		<div class="fieldname"><?php echo $formText_anchorTitle_fieldtype;?></div><?php
		if ( isset($val[2]) ) { $mpa = $val[2]; } else { $mpa = ''; }
		?><input id="<?php echo $field_ui_id;?>Anchor" type="text" name="<?php echo $field[1].$ending;?>Anchor" value="<?php echo $mpa; ?>" style="height: 34px; padding: 6px 12px; margin-top:5px;" onChange="$('#<?php echo $field_ui_id;?>orig').val($('#<?php echo $field_ui_id;?>d2').val()+'#'+$(this).val());" <?php
		if ( $val[0] != 0 && $val[1] != 0 ) {} else { echo 'disabled'; } ?> />
	</div><?php
}
?></div>
<div style="clear:both;"></div>
<input id="<?php echo $field_ui_id;?>orig" type="hidden" name="<?php echo $field[1].$ending;?>" value="<?php echo htmlspecialchars($field[6][$langID]); ?>" />