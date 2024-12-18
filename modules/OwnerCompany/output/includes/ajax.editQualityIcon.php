<?php
$iconId = $_POST['iconId'] ? ($_POST['iconId']) : 0;

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => $module, // module id in which this block is used
	  'id' => 'iconuploadpopup',
	  'upload_type'=>'image',
	  'content_table' => 'ownercompany_qualityicons',
	  'content_field' => 'icon',
	  'content_id' => $iconId,
	  'content_module_id' => $moduleID, // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart'
	)
);
if($iconId) {
    $s_sql = "SELECT * FROM ownercompany_qualityicons WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($iconId));
    if($o_query && $o_query->num_rows()>0) {
        $customerData = $o_query->row_array();
    }
}
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_delete']))
	{
		if($iconId > 0)
		{
			$s_sql = "DELETE ownercompany_qualityicons FROM ownercompany_qualityicons WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($iconId));
			if($o_query){
		    	$files = json_decode($customerData['icon'], true);
		    	foreach($files as $file) {
	    			// delete file from ftp
	    			foreach($file[1] as $single_file){
	    				unlink(__DIR__."/../../../../".$single_file);
	                    $parent_dir_name = dirname($single_file);
	    				rmdir(__DIR__."/../../../../".$parent_dir_name);
	                    $parent_dir_name2 = dirname($parent_dir_name);
	    				rmdir(__DIR__."/../../../../".$parent_dir_name2);
	    			}
		    	}
			}
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['ownercompany_id'];
		return;
	}

	if(isset($_POST['output_form_submit'])) {

        if ($iconId) {
        }
        else {
            $s_sql = "INSERT INTO ownercompany_qualityicons SET
            created = now(),
            createdBy= ?,
			moduleID = ?,
			ownercompany_id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $moduleID, $_POST['ownercompany_id']));

            $insert_id = $o_main->db->insert_id();

			foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
				$fieldName = $fwaFileuploadConfig['id'];
				$fwaFileuploadConfig['content_id'] = $insert_id;
				include( __DIR__ . "/fileupload10/contentreg.php");
			}
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['ownercompany_id'];
        }

	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editQualityIcon";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="iconId" value="<?php echo $iconId;?>">
		<input type="hidden" name="ownercompany_id" value="<?php echo $_POST['ownercompany_id'];?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['ownercompany_id']; ?>">
		<div class="inner">
            <div class="line lineFull">
                <div class="lineTitle"><?php echo $formText_Icon_Output; ?></div>
                <div class="lineInput">
					<?php
					$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
					include __DIR__ . '/fileupload10/output.php';
					?>
                </div>
                <div class="clear"></div>
            </div>

		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" class="saveFiles" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);
};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
$(document).ready(function() {
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
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
.popupform input.popupforminput.checkbox {
    width: auto;
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
    position:relative;
}
.invoiceEmail {
    display: none;
}
.selectDivModified {
    display:block;
}
.popupeditbox label.error{ display: none !important; }
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
.popupeditbox .popupform .line.lineFull .lineTitle {
	width: 100%;
	float: none;
}
.popupeditbox .popupform .line.lineFull .lineInput {
	width: 100%;
	float: none;
}
</style>
