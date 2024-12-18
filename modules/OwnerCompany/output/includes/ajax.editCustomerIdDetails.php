<?php
$ownercompanyId = isset($_POST['ownercompanyId']) ? $_POST['ownercompanyId'] : 0;

$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($ownercompanyId) {
            $s_sql = "UPDATE ownercompany SET
            updated = now(),
            updatedBy= ?,
            customerid_autoormanually= ?,
            nextCustomerId= ?,
            use_integration = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['customerid_autoormanually'], $_POST['nextCustomerId'], $_POST['use_integration'], $ownercompanyId));
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
        }
	}
}

if($ownercompanyId) {
    $sql = "SELECT * FROM ownercompany WHERE id = $ownercompanyId";
    $result = $o_main->db->query($sql);
    if($result && $result->num_rows() > 0) $officeSpaceData = $result->row();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCustomerIdDetails";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>">
		<div class="inner">

            <div class="line">
                <div class="lineTitle"><?php echo $formText_UseExternalCustomerId_output; ?></div>
                <div class="lineInput">
                    <select name="customerid_autoormanually" required class="customerid_autoormanually">
                        <option value=""><?php echo $formText_Select_output?></option>
                        <option value="1" <?php if($officeSpaceData->customerid_autoormanually == 1) echo 'selected';?>><?php echo $formText_Automaticly_output?></option>
                        <option value="2" <?php if($officeSpaceData->customerid_autoormanually == 2) echo 'selected';?>><?php echo $formText_Manually_output?></option>
                        <option value="3" <?php if($officeSpaceData->customerid_autoormanually == 3) echo 'selected';?>><?php echo $formText_AutomaticAndEditable_output?></option>
                        <option value="-1" <?php if($officeSpaceData->customerid_autoormanually == -1) echo 'selected';?>><?php echo $formText_FromExternalSystem_output?></option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line nextCustomerId">
        		<div class="lineTitle"><?php echo $formText_NextCustomerId_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="nextCustomerId" value="<?php echo $officeSpaceData->nextCustomerId; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line integration">
                <div class="lineTitle"><?php echo $formText_UseIntegration_output; ?></div>
                <div class="lineInput">
                    <select name="use_integration">
                        <option value="0"><?php echo $formText_NoIntegration_output?></option>
                        <?php
                        $getIntegrations = "SELECT * FROM moduledata WHERE LOWER(name) LIKE LOWER('Integration%')";
                        $findIntegrations = $o_main->db->query($getIntegrations);
                        if($findIntegrations &&  $findIntegrations->num_rows() > 0)
                        foreach($findIntegrations->result() AS $integration){
                            ?>
                            <option value="<?php echo $integration->name; ?>" <?php if($officeSpaceData->use_integration == $integration->name) echo 'selected';?>><?php echo $integration->name; ?></option>
                            <?php
                        }
                        ?>

                    </select>
                </div>
                <div class="clear"></div>
            </div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".customerid_autoormanually").change(function(){
        if($(this).val() == 1 || $(this).val() == 3){
            $(".integration").show();
        } else {
            $(".integration").hide();
        }
		if($(this).val() == -1){
			$(".nextCustomerId").hide();
		} else {
			$(".nextCustomerId").show();
		}
    })
    $(".customerid_autoormanually").change();
     $("form.output-form").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
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
.integration {
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
