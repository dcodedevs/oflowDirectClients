<?php
$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();
if(count($company_product_sets) == 0){
	$_POST['set_id'] = 0;
}
$vatcodeId = $_POST['vatcodeId'] ? $o_main->db->escape_str($_POST['vatcodeId']) : 0;
$set_id = $_POST['set_id'] ? $o_main->db->escape_str($_POST['set_id']) : 0;
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

if($set_id == -1 && count($company_product_sets) > 0){
	echo $formText_PleaseSelectSet_output;
	return;
}
$sql = "SELECT * FROM vatcode WHERE id = '".$o_main->db->escape_str($vatcodeId)."'";
$o_query = $o_main->db->query($sql);
$vatData = $o_query ? $o_query->row_array() : array();

$s_sql = "select * from vatcode_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_vat_accountconfig= ($o_query ? $o_query->row_array() : array());

$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
		if($_POST['vatCode'] == "" || $_POST['name'] == '' || $_POST['percentRate'] == '' || $_POST['ehf'] == '') {
			$fw_error_msg[] = $formText_MissingFieldsPleseFillInAllFields_output;
			return;
		}
		if($v_vat_accountconfig['activateRevenueClass']) {
			if($_POST['revenue_class'] == "") {
				$fw_error_msg[] = $formText_MissingRevenueClass_output;
				return;
			}
		}
        if ($vatData) {
            $sql = "UPDATE vatcode SET
            updated = now(),
            updatedBy = '".$variables->loggID."',
            vatCode = '".$o_main->db->escape_str($_POST['vatCode'])."',
			name = '".$o_main->db->escape_str($_POST['name'])."',
			percentRate = '".$o_main->db->escape_str($_POST['percentRate'])."',
			ehf = '".$o_main->db->escape_str($_POST['ehf'])."',
			revenue_class = '".$o_main->db->escape_str($_POST['revenue_class'])."',
			bookaccountNr = '".$o_main->db->escape_str($_POST['bookaccountNr'])."',
			company_product_set_id = '".$o_main->db->escape_str($_POST['set_id'])."'
            WHERE id = ".$vatData['id'];

			$insert_id = $vatData['id'];
			$o_query = $o_main->db->query($sql);
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $sql = "INSERT INTO vatcode SET
            created = now(),
            createdBy='".$variables->loggID."',
            vatCode = '".$o_main->db->escape_str($_POST['vatCode'])."',
			name  = '".$o_main->db->escape_str($_POST['name'])."',
			percentRate  = '".$o_main->db->escape_str($_POST['percentRate'])."',
			ehf = '".$o_main->db->escape_str($_POST['ehf'])."',
			revenue_class = '".$o_main->db->escape_str($_POST['revenue_class'])."',
			bookaccountNr = '".$o_main->db->escape_str($_POST['bookaccountNr'])."',
			company_product_set_id = '".$o_main->db->escape_str($_POST['set_id'])."'";
			$o_query = $o_main->db->query($sql);
            $insert_id = $o_main->db->insert_id();
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;

     	}
		return;
	}
	if($action == "deleteVat" && $vatcodeId > 0) {
		$sql = "DELETE FROM vatcode WHERE id = '".$o_main->db->escape_str($vatcodeId)."'";
		$o_query = $o_main->db->query($sql);
		return;
	}
}
$s_sql = "select * from bookaccount ORDER BY accountNr";
$o_query = $o_main->db->query($s_sql);
$bookaccounts= ($o_query ? $o_query->result_array() : array());
?>

<div class="popupform popupform-<?php echo $vatcodeId;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editVat";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="vatcodeId" value="<?php echo $vatcodeId;?>">
		<?php if($vatData) { ?>
			<input type="hidden" name="set_id" value="<?php echo $vatData['company_product_set_id'];?>">
		<?php } else { ?>
        	<input type="hidden" name="set_id" value="<?php echo $set_id;?>">
		<?php } ?>
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list" ?>">
		<div class="popupformTitle"><?php
		if($caseId> 0){
			echo $formText_EditVatCode_output;
		} else {
			echo $formText_AddVatCode_output;
		}
		?></div>
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_VatCode_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="vatCode" value="<?php echo $vatData['vatCode']; ?>" autocomplete="off" required>
				</div>
				<div class="clear"></div>
			</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $vatData['name']; ?>" autocomplete="off" required>
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_PercentRate_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="percentRate" value="<?php echo $vatData['percentRate']; ?>" required autocomplete="off" >
                </div>
        		<div class="clear"></div>
    		</div>
			<div class="line">
        		<div class="lineTitle"><?php echo $formText_Ehf_Output; ?></div>
        		<div class="lineInput">
					<select name="ehf" class="popupforminput botspace" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<option <?php if($vatData['ehf'] == "S") echo 'selected';?> value="S">Vanlig sats (25%) - Redusert sats, mellom (15%) - Redusert sats, rå fisk (11,11%) - Redusert sats, lav (12%)</option>
						<option <?php if($vatData['ehf'] == "E") echo 'selected';?>  value="E">Momsfritak (0%)</option>
						<option <?php if($vatData['ehf'] == "Z") echo 'selected';?>  value="Z">Momsfritak (Varer og tjenester som ikke er inkludert i merverdiavgiftsforskriften) (0%)</option>
						<option <?php if($vatData['ehf'] == "K") echo 'selected';?>  value="K">Utslippskostnader for private eller offentlige virksomheter - kjøper beregner merverdiavgift (0%)</option>
						<option <?php if($vatData['ehf'] == "AE") echo 'selected';?>  value="AE">Omvendt merverdiavgift (0%)</option>
						<option <?php if($vatData['ehf'] == "G") echo 'selected';?>  value="G">Eksport av varer og tjenester (0%)</option>
					</select>
                </div>
        		<div class="clear"></div>
    		</div>
			<?php if($v_vat_accountconfig['activateRevenueClass']) { ?>
				<div class="line">
	        		<div class="lineTitle"><?php echo $formText_RevenueClass_output; ?></div>
	        		<div class="lineInput">
	                    <input type="text" class="popupforminput botspace" name="revenue_class" value="<?php echo $vatData['revenue_class']; ?>" required autocomplete="off" >
	                </div>
	        		<div class="clear"></div>
	    		</div>
			<?php } ?>
			<?php if($v_vat_accountconfig['activateVatBookaccountNr']) { ?>
				<div class="line">
	        		<div class="lineTitle"><?php echo $formText_BookaccountNr_output; ?></div>
	        		<div class="lineInput">
						<select class="popupforminput botspace debitorSelect" autocomplete="off"  name="bookaccountNr">
							<option value=""><?php echo $formText_SelectBookAccount_output;?></option>
							<?php foreach($bookaccounts as $bookaccount) { ?>
								<option value="<?php echo $bookaccount['accountNr'];?>" <?php if($vatData['bookaccountNr'] == $bookaccount['accountNr']) echo 'selected';?>><?php echo $bookaccount['accountNr']." ".$bookaccount['name'];?></option>
							<?php } ?>
						</select>
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $(".popupform-<?php echo $vatcodeId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            fw_loading_start();
			$("#popup-validate-message").html("");
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
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {
	                    if(data.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload");
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $vatcodeId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $vatcodeId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $vatcodeId;?> #popupeditbox').css('height', $('.popupform-<?php echo $vatcodeId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $vatcodeId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $vatcodeId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $vatcodeId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $vatcodeId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $vatcodeId;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $vatcodeId;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $vatcodeId;?> .selectOwner");
            }
            if(element.attr("name") == "invoiceResponsible") {
                error.insertAfter(".popupform-<?php echo $vatcodeId;?> .invoiceResponsible");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectResponsiblePerson_output;?>",
            invoiceResponsible: "<?php echo $formText_SelectInvoiceResponsible_output;?>"
        }
    });

});

</script>
<style>
.emailSendingWrapper {
	display: none;
}
.reset_customer {
	cursor: pointer;
}
.reset_responsible_person {
	cursor: pointer;
}
.rightWrapper {
	position: absolute;
	top: 0;
	right: 0px;
	width: 30%;
}
.popupeditbox .popupform .rightWrapper .line .lineTitle {
	width: calc(100% - 40px);
}
.popupeditbox .popupform .rightWrapper .line .lineInput {
	width: 40px;
}
.projectOwnerData {
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
.popupform .inlineInput select {
    display: inline-block;
    width: auto;
    vertical-align: middle;
	margin-bottom: 10px;
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
