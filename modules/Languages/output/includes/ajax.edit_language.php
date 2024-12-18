<?php
$b_webapp = FALSE;
$o_query = $o_main->db->get('accountinfo_basisconfig');
$v_accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(isset($v_accountinfo_basisconfig['account_type']) && 'webapp' == $v_accountinfo_basisconfig['account_type'])
{
	$b_webapp = TRUE;
}

$s_sql = "SELECT * FROM language WHERE languageID = '".$o_main->db->escape_str($_POST['languageID'])."'";
if(isset($_POST['content_id']) && 0 < $_POST['content_id'])
{
	$s_sql = "SELECT * FROM language WHERE id = '".$o_main->db->escape_str($_POST['content_id'])."'";
}
$o_query = $o_main->db->query($s_sql);
$v_data = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['action']) && 'delete_language' == $_POST['action'])
		{
			$o_main->db->query("DELETE FROM language WHERE languageID = '".$o_main->db->escape_str($_POST['languageID'])."'");
			return;
		}
		
		if($b_webapp)
		{
			$webapp_language = (isset($_POST['webapp_language']) && 1 == $_POST['webapp_language']) ? 1 : 0;
			$default_webapp_language = (isset($_POST['default_webapp_language']) && 1 == $_POST['default_webapp_language']) ? 1 : 0;
			$published_webapp_language = (isset($_POST['published_webapp_language']) && 1 == $_POST['published_webapp_language']) ? 1 : 0;
			$v_update = array(
				'languageID' => $_POST['languageID'],
				'name' => $_POST['name'],
				'inputlanguage' => $webapp_language,
				'defaultInputlanguage' => $default_webapp_language,
				'outputlanguage' => $webapp_language,
				'defaultOutputlanguage' => $default_webapp_language,
				'list_url_prefix' => '',
				'webapp_language' => $webapp_language,
				'default_webapp_language' => $default_webapp_language,
				'published_webapp_language' => $published_webapp_language,
			);
		} else {
			$inputlanguage = (isset($_POST['inputlanguage']) && 1 == $_POST['inputlanguage']) ? 1 : 0;
			$defaultInputlanguage = (isset($_POST['defaultInputlanguage']) && 1 == $_POST['defaultInputlanguage']) ? 1 : 0;
			$outputlanguage = (isset($_POST['outputlanguage']) && 1 == $_POST['outputlanguage']) ? 1 : 0;
			$defaultOutputlanguage = (isset($_POST['defaultOutputlanguage']) && 1 == $_POST['defaultOutputlanguage']) ? 1 : 0;
			$hideOutputlanguage = (isset($_POST['hideOutputlanguage']) && 1 == $_POST['hideOutputlanguage']) ? 1 : 0;
			$v_update = array(
				'languageID' => $_POST['languageID'],
				'name' => $_POST['name'],
				'inputlanguage' => $inputlanguage,
				'defaultInputlanguage' => $defaultInputlanguage,
				'outputlanguage' => $outputlanguage,
				'defaultOutputlanguage' => $defaultOutputlanguage,
				'hideOutputlanguage' => $hideOutputlanguage,
				'list_url_prefix' => $_POST['list_url_prefix'],
				'webapp_language' => 0,
				'default_webapp_language' => 0,
				'published_webapp_language' => 0,
			);
		}
		
		if(isset($v_data['id']) && 0 < $v_data['id'])
		{
			$v_update['updated'] = date('Y-m-d H:i:s');
			$v_update['updatedBy'] = $variables->loggID;
			$v_update_where = array(
				'id' => $v_data['id'],
			);
			$b_update = $o_main->db->update('language', $v_update, $v_update_where);
		} else {
			$v_update['created'] = date('Y-m-d H:i:s');
			$v_update['createdBy'] = $variables->loggID;
			$b_update = $o_main->db->insert('language', $v_update);
		}
        
		return;
	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_language";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="content_id" value="<?php echo $v_data['id'];?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_LanguageId_Output;?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="languageID" value="<?php echo $v_data['languageID'];?>" autocomplete="off" required>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Name_Output;?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $v_data['name'];?>" autocomplete="off" required>
                </div>
                <div class="clear"></div>
            </div>
            <?php if($b_webapp) { ?>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_WebApplicationLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="webapp_language" value="1" <?php echo (1==$v_data['webapp_language']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_DefaultWebApplicationLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="default_webapp_language" value="1" <?php echo (1==$v_data['default_webapp_language']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_PublishedWebApplicationLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="published_webapp_language" value="1" <?php echo (1==$v_data['published_webapp_language']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<?php } else { ?>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_InputLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="inputlanguage" value="1" <?php echo (1==$v_data['inputlanguage']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_DefaultInputLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="defaultInputlanguage" value="1" <?php echo (1==$v_data['defaultInputlanguage']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_OutputLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="outputlanguage" value="1" <?php echo (1==$v_data['outputlanguage']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_DefaultOutputLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="defaultOutputlanguage" value="1" <?php echo (1==$v_data['defaultOutputlanguage']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_HideOutputLanguage_Output;?></div>
                <div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace" name="hideOutputlanguage" value="1" <?php echo (1==$v_data['hideOutputlanguage']?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_ListUrlPrefix_Output;?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="list_url_prefix" value="<?php echo $v_data['list_url_prefix'];?>">
                </div>
                <div class="clear"></div>
            </div>
			<?php } ?>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	// Submit form
    $("form.output-form").validate({
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
						$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
                		$("#popup-validate-message").show();
					} else {
						$("#popupeditbox .b-close").click();
						output_reload_page();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
});

</script>
<style>
.popupform input[type="checkbox"] {
    width: auto !important;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    border:1px solid #e8e8e8;
    position:relative;
}
.invoiceEmail {
    display: none;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
