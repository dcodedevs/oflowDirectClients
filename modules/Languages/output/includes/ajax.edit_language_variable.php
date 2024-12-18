<?php
if(!function_exists('include_local')) include_once(__DIR__.'/../input/includes/fn_include_local.php');

$_POST['module'] = preg_replace('[^A-Za-z0-9_]', '', $_POST['module']);
$_POST['folder'] = preg_replace('[^A-Za-z0-9_]', '', $_POST['folder']);

$s_default_output_language = '';
$s_default_output_language_name = '';
$o_lang_query = $o_main->db->query("SELECT name, languageID FROM language WHERE languageID = '".$o_main->db->escape_str($variables->languageID)."' AND outputlanguage = 1");
if($o_lang_query && $o_lang_query->num_rows()>0)
{
	$o_row = $o_lang_query->row();
	$s_default_output_language = $o_row->languageID;
	$s_default_output_language_name = $o_row->name;
} else {
	$o_lang_query = $o_main->db->query("SELECT name, languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
	if($o_lang_query && $o_row = $o_lang_query->row())
	$s_default_output_language = $o_row->languageID;
	$s_default_output_language_name = $o_row->name;
}

$s_path = BASEPATH.'modules/'.$_POST['module'];

$s_saved_value = '';
$s_file = $s_path.'/'.$_POST['folder'].'/languagesOutput/'.$s_default_output_language.'.php';
if(is_file($s_file))
{
	$v_variables = include_local($s_file);
	if(isset($v_variables[$_POST['variable_id']]))
	{
		$s_saved_value = $v_variables[$_POST['variable_id']];
	}
}


if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(is_file($s_file)) ftp_copy($s_file, str_replace(BASEPATH, '/', $s_path).'/'.$_POST['folder'].'/languagesOutput/'.$s_default_output_language.'.bckp');
		$s_data = "<"."?php".PHP_EOL;
		foreach($v_variables as $var_id => $s_value)
		{
			if($_POST['variable_id'] == $var_id)
			{
				$s_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$_POST['variable_value']).'";'.PHP_EOL;
			} else {
				$s_data .= '$'.$var_id.'="'.str_replace('"','&quot;',$s_value).'";'.PHP_EOL;
			}
		}
		ftp_file_put_content(str_replace(BASEPATH, '/', $s_file), $s_data);
		
	}
}
?>

<div class="popupform popupform-<?php echo $cid;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$inc_obj."&inc_act=".$s_inc_act;?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="variable_id" value="<?php echo $_POST['variable_id'];?>">
		<input type="hidden" name="module" value="<?php echo $_POST['module'];?>">
		<input type="hidden" name="folder" value="<?php echo $_POST['folder'];?>">
		<input type="hidden" name="skip_translate" value="<?php echo $_POST['skip_translate'];?>">
        <div class="popupformTitle"><?php echo $formText_UpdateLanguageVariableIn_Output.': '.$s_default_output_language_name;?></div>
        <div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_TranslateVariable_Output.': '.$_POST['variable_id']; ?></div>
				<div class="lineInput">
                    <input type="text" class="popupforminput" name="variable_value" value="<?php echo $s_saved_value;?>">
				</div>
				<div class="clear"></div>
			</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>

<?php
$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);
foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $variables->account_root_url.$s_item.'?v='.$l_time;?>"></script><?php
}
?>
<script type="text/javascript">
$(document).ready(function() {
    $(".popupform-<?php echo $cid;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
					} else {
                        out_popup.addClass("close-reload");
                        out_popup.close();
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $cid;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $cid;?> #popup-validate-message").show();
                $('.popupform-<?php echo $cid;?> #popupeditbox').css('height', $('.popupform-<?php echo $cid;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $cid;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $cid;?> #popup-validate-message").show();
                $('.popupform-<?php echo $cid;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $cid;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
    });
});

</script>