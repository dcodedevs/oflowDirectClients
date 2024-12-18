<?php
$cover_id = $_POST['cover_id'] ? $o_main->db->escape_str($_POST['cover_id']) : 0;
$coverline_id = $_POST['coverline_id'] ? $o_main->db->escape_str($_POST['coverline_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

$sql = "SELECT * FROM covering_order_and_split WHERE id = $cover_id";
$o_query = $o_main->db->query($sql);
$processData = $o_query ? $o_query->row_array() : array();


if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		$sortnr = 0;
		if($_POST['collectioncompany_share'] + $_POST['creditor_share'] == 100) {
			$sql = "SELECT * FROM covering_order_and_split_lines WHERE covering_order_and_split_id = ? ORDER BY sortnr DESC";
			$o_query = $o_main->db->query($sql, array($processData['id']));
			$maxData = $o_query ? $o_query->row_array() : array();
			$sortnr = intval($maxData['sortnr']) + 1;

	        if ($coverline_id) {
	            $sql = "UPDATE covering_order_and_split_lines SET
	            updated = now(),
	            updatedBy='".$variables->loggID."',
	            covering_order_and_split_id='".$o_main->db->escape_str($cover_id)."',
	            collecting_claim_line_type='".$o_main->db->escape_str($_POST['collecting_claim_line_type'])."',
	            covering_order='".$o_main->db->escape_str($_POST['covering_order'])."',
				collectioncompany_share = '".$o_main->db->escape_str($_POST['collectioncompany_share'])."',
				creditor_share = '".$o_main->db->escape_str($_POST['creditor_share'])."',
				collectioncompany_share_warning = '".$o_main->db->escape_str($_POST['collectioncompany_share_warning'])."',
				creditor_share_warning = '".$o_main->db->escape_str($_POST['creditor_share_warning'])."',
				warning_level_checkbox = '".$o_main->db->escape_str($_POST['warning_level_checkbox'])."'
	            WHERE id = $coverline_id";

				$o_query = $o_main->db->query($sql);
				$insert_id = $cover_id;
	            $fw_redirect_url = $_POST['redirect_url'];
	        } else {
	            $sql = "INSERT INTO covering_order_and_split_lines SET
	            created = now(),
	            createdBy='".$variables->loggID."',
				sortnr = '".$o_main->db->escape_str($sortnr)."',
	            covering_order_and_split_id='".$o_main->db->escape_str($cover_id)."',
	            collecting_claim_line_type='".$o_main->db->escape_str($_POST['collecting_claim_line_type'])."',
	            covering_order='".$o_main->db->escape_str($_POST['covering_order'])."',
				collectioncompany_share = '".$o_main->db->escape_str($_POST['collectioncompany_share'])."',
				creditor_share = '".$o_main->db->escape_str($_POST['creditor_share'])."',
				collectioncompany_share_warning = '".$o_main->db->escape_str($_POST['collectioncompany_share_warning'])."',
				creditor_share_warning = '".$o_main->db->escape_str($_POST['creditor_share_warning'])."',
				warning_level_checkbox = '".$o_main->db->escape_str($_POST['warning_level_checkbox'])."'";

				$o_query = $o_main->db->query($sql);
	            $insert_id = $o_main->db->insert_id();
	            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
	        }
		} else {
			$fw_error_msg[] = $formText_TotalSharesNeedToBe100Percent_output;
		}
	}
}
if($action == "deleteCoverlines" && $coverline_id) {
    $sql = "DELETE FROM covering_order_and_split_lines
    WHERE id = $coverline_id";
    $o_query = $o_main->db->query($sql);
}

$sql = "SELECT * FROM covering_order_and_split_lines WHERE id = $coverline_id";
$o_query = $o_main->db->query($sql);
$processStepData = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig WHERE content_status < 2 ORDER BY sortnr ASC";
$o_query = $o_main->db->query($sql);
$claim_types = $o_query ? $o_query->result_array() : array();
?>

<div class="popupform popupform-<?php echo $cover_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_cover_lines";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cover_id" value="<?php echo $cover_id;?>">
		<input type="hidden" name="coverline_id" value="<?php echo $coverline_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$cover_id; ?>">
		<div class="inner">
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectingClaimLineType_output; ?></div>
        		<div class="lineInput">
					<select name="collecting_claim_line_type"  autocomplete="off" class="popupforminput botspace" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php foreach($claim_types as $claim_type) { ?>
							<option value="<?php echo $claim_type['id'];?>" <?php if($processStepData['collecting_claim_line_type'] == $claim_type['id']) echo 'selected';?>><?php echo $claim_type['type_name'];?></option>
						<?php } ?>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_CoveringOrder_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="covering_order" value="<?php echo $processStepData['covering_order']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CollectionCompanyShare_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="collectioncompany_share" value="<?php echo $processStepData['collectioncompany_share']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_CreditorShare_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="creditor_share" value="<?php echo $processStepData['creditor_share']; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
			
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_SpecificSplitForWarningLevel_output; ?></div>
        		<div class="lineInput">
                    <input type="checkbox" class="popupforminput botspace checkbox warninglevel_checkbox" autocomplete="off" name="warning_level_checkbox" value="1" <?php if($processStepData['warning_level_checkbox'] == 1){ echo 'checked';} ?>>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="warning_wrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_WarningLevelCollectionCompanyShare_Output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="collectioncompany_share_warning" value="<?php echo $processStepData['collectioncompany_share_warning']; ?>">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_WarningLevelCreditorShare_output; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" autocomplete="off" name="creditor_share_warning" value="<?php echo $processStepData['creditor_share_warning']; ?>">
					</div>
					<div class="clear"></div>
				</div>
			</div>

		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $cover_id;?> form.output-form").validate({
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
					if(data.error !== undefined){
                        var _msg = '';
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
                    } else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $cover_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $cover_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $cover_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $cover_id;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $cover_id;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $cover_id;?> #popup-validate-message").show();
                $('.popupform-<?php echo $cover_id;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $cover_id;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "creditor_id") {
                error.insertAfter(".popupform-<?php echo $cover_id;?> .selectCreditor");
            }
            if(element.attr("name") == "debitor_id") {
                error.insertAfter(".popupform-<?php echo $cover_id;?> .selectDebitor");
            }
        },
        messages: {
            creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
            debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
        }
    });
	$(".warninglevel_checkbox").off("change").on("change", function(){
		if($(this).is(":checked")){
			$(".warning_wrapper").show();
		} else {
			$(".warning_wrapper").hide();			
		}
	}).change();
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
	})
    $(".popupform-<?php echo $cover_id;?> .selectCreditor").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, creditor: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_creditors";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })
    $(".popupform-<?php echo $cover_id;?> .selectDebitor").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, debitor: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })


    $(".popupform-<?php echo $cover_id;?> .selectOwner").unbind("click").bind("click", function(){
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, owner: 1};
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    })

	$(".resetInvoiceResponsible").on("click", function(){
		$("#invoiceResponsible").val("");
		$(".selectInvoiceResponsible").html("<?php echo $formText_SelectInvoiceResponsible_Output;?>");
	})
});

</script>
<style>
.warning_wrapper {
	display: none;
}
.categoryWrapper {
	display: none;
}
.resetInvoiceResponsible {
	margin-left: 20px;
}
.lineInput .otherInput {
    margin-top: 10px;
}
.lineInput input[type="radio"]{
    margin-right: 10px;
    vertical-align: middle;
}
.lineInput input[type="radio"] + label {
    margin-right: 10px;
    vertical-align: middle;
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
.popupform .lineInput.lineWhole {
	font-size: 14px;
}
.popupform .lineInput.lineWhole label {
	font-weight: normal !important;
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
label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: inline !important;
}
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
.addSubProject {
    margin-bottom: 10px;
}
</style>
