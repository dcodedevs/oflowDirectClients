<?php
$s_sql = "SELECT * FROM collecting_cases_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$v_collectingcases_accountconfig = $o_query && $o_query->num_rows()>0 ? $o_query->row_array() : array();


if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$s_sql = "SELECT * FROM moduledata WHERE name = 'CollectingCompanyCases'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();
$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'CollectingCompanyCases', // module id in which this block is used
	  'id' => 'invoicefileupload',
	  'upload_type' => 'file',
	  'content_id' => $_POST['cid'],
	  'content_table' => 'collecting_company_cases_claim_lines',
	  'content_field' => 'invoiceFile',
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete',
	  'callbackPopupClose' => 'updatePreview'
	)
);
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		$claimType = $_POST['claim_type'];
		$originalDueDate = "";
		if($_POST['original_due_date'] != "" && $claimType == 1) {
			$originalDueDate = date("Y-m-d", strtotime($_POST['original_due_date']));
		}

		$date = "";
		if($_POST['date'] != "" && $claimType == 1) {
			$date = date("Y-m-d", strtotime($_POST['date']));
		}
		if($_POST['date'] != "" && $claimType == 15) {
			$date = date("Y-m-d", strtotime($_POST['date']));
		}
		$collect_warning_date = "";
		if($_POST['collect_warning_date'] != "" && $claimType == 1) {
			$collect_warning_date = date("Y-m-d", strtotime($_POST['collect_warning_date']));
		}
		$original_amount = str_replace(",", ".", $_POST['original_amount']);

		$court_fee_released_date = '0000-00-00';
		if($_POST['court_fee_released_date'] != ""){
			$court_fee_released_date = date("Y-m-d", strtotime($_POST['court_fee_released_date']));
		}
		$_POST['amount'] = str_replace(" ", "", str_replace(",", ".", $_POST['amount']));
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		    if($o_query && $o_query->num_rows() == 1) {
				$s_sql = "UPDATE collecting_company_cases_claim_lines SET
				updated = now(),
				updatedBy= ?,
				name= ?,
	            original_due_date='".$o_main->db->escape_str($originalDueDate)."',
	            claim_type='".$o_main->db->escape_str($claimType)."',
	            date='".$o_main->db->escape_str($date)."',
	            collect_warning_date='".$o_main->db->escape_str($collect_warning_date)."',
	            original_amount='".$o_main->db->escape_str($original_amount)."',
				invoice_nr ='".$o_main->db->escape_str($_POST['invoice_nr'])."',
				payment_after_closed ='".$o_main->db->escape_str($_POST['payment_after_closed'])."',
				amount= ?,
				court_fee_released_date = '".$o_main->db->escape_str($court_fee_released_date)."',
				note = '".$o_main->db->escape_str($_POST['note'])."'
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['amount'], $_POST['cid']));
			}
		} else {
			$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			collecting_company_case_id = ?,
			name= ?,
            original_due_date='".$o_main->db->escape_str($originalDueDate)."',
            claim_type='".$o_main->db->escape_str($claimType)."',
			date='".$o_main->db->escape_str($date)."',
			collect_warning_date='".$o_main->db->escape_str($collect_warning_date)."',
			original_amount='".$o_main->db->escape_str($original_amount)."',
			invoice_nr ='".$o_main->db->escape_str($_POST['invoice_nr'])."',
			payment_after_closed ='".$o_main->db->escape_str($_POST['payment_after_closed'])."',
			amount= ?,
			court_fee_released_date = '".$o_main->db->escape_str($court_fee_released_date)."',
			note = '".$o_main->db->escape_str($_POST['note'])."'";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['caseId'], $_POST['name'], $_POST['amount']));
			$_POST['cid'] = $o_main->db->insert_id();
		}
		foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
			$l_content_status = 0;
			$fieldName = $fwaFileuploadConfig['id'];
			$fwaFileuploadConfig['content_id'] = $_POST['cid'];
			include( __DIR__ . "/fileupload_popup/contentreg.php");
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_company_cases_claim_lines WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}
$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$claim_types = $o_query && $o_query->num_rows()>0 ? $o_query->result_array() : array();

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_claims";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">

	<div class="inner">
		<div class="line">
			<div class="lineTitle"><?php echo $formText_ClaimType_Output; ?></div>
			<div class="lineInput">
				<select name="claim_type" class="claimTypeChange" required>
					<option value=""><?php echo $formText_Select_output;?></option>
					<?php
					foreach($claim_types as $claim_type) {
						// if($claim_type['id'] != 8) {
						?>
						<option value="<?php echo $claim_type['id'];?>" <?php if($v_data['claim_type'] == $claim_type['id']) echo 'selected';?>><?php echo $claim_type['type_name'];?></option>
							<?php
						// }
					}
					?>

				</select>
			</div>
			<div class="clear"></div>
		</div>

		<div class="line">
			<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $v_data['name']; ?>" required>
			</div>
			<div class="clear"></div>
		</div>

		<div class="line dateWrapper originalClaimInputWrapper">
			<div class="lineTitle"><?php echo $formText_Date_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="date" value="<?php if($v_data['date'] != "0000-00-00" && $v_data['date'] != ""){ echo date("d.m.Y", strtotime($v_data['date'])); }?>" required>
			</div>
			<div class="clear"></div>
		</div>

		<div class="line payedAfterWrapper originalClaimInputWrapper">
			<div class="lineTitle"><?php echo $formText_PaymentAfterClosed_Output; ?></div>
			<div class="lineInput">
				<input type="checkbox" class="popupforminput botspace checkbox notRequired" autocomplete="off"  name="payment_after_closed" value="1" <?php if($v_data['payment_after_closed']) echo 'checked';?>>
			</div>
			<div class="clear"></div>
		</div>
		<?php if($v_data['claim_type'] != 5) { ?>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_OriginalDueDate_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="original_due_date" value="<?php if($v_data['original_due_date'] != "0000-00-00" && $v_data['original_due_date'] != ""){ echo date("d.m.Y", strtotime($v_data['original_due_date'])); }?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_CollectWarningDate_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datefield notRequired" autocomplete="off"  name="collect_warning_date" value="<?php if($v_data['collect_warning_date'] != "0000-00-00" && $v_data['collect_warning_date'] != ""){ echo date("d.m.Y", strtotime($v_data['collect_warning_date'])); }?>">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_InvoiceNr_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="invoice_nr" value="<?php echo $v_data['invoice_nr']; ?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_OriginalAmount_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace original_amount_input" autocomplete="off" name="original_amount" value="<?php echo number_format($v_data['original_amount'], 2, ",", ""); ?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace amount_input" autocomplete="off" name="amount" value="<?php echo number_format($v_data['amount'], 2, ",", ""); ?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_InvoiceFile_output; ?></div>
				<div class="lineInput">
					<?php
					$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
					require __DIR__ . '/fileupload_popup/output.php';
					?>
				</div>
				<div class="clear"></div>
			</div>
		<?php } else {
			?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_InterestPercent_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="interest_percent" value="<?php echo number_format($v_data['interest_percent'], 2, ",", ""); ?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<?php
			}
		?>
		
		<div class="line courtFeeReleasedDate">
			<div class="lineTitle"><?php echo $formText_CourtFeeReleasedDate_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace datefield notRequired" autocomplete="off"  name="court_fee_released_date" value="<?php if($v_data['court_fee_released_date'] != "0000-00-00" && $v_data['court_fee_released_date'] != ""){ echo date("d.m.Y", strtotime($v_data['court_fee_released_date'])); }?>">
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Note_Output; ?></div>
			<div class="lineInput">
				<textarea name="note" class="popupforminput botspace"><?php echo $v_data['note']; ?></textarea>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<style>

.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
.popupeditbox label.error {
    color: #c11;
    margin-left: 10px;
    border: 0;
    display: none !important;
}
.dateWrapper {
	display: none;
}
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }

</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">


$(function() {
	$(".claimTypeChange").change(function(){
		var value = $(this).val();
		$(".courtFeeReleasedDate").hide();
		if(value == 1){
			$(".originalClaimInputWrapper").show();
			$(".originalClaimInputWrapper input:not(.notRequired)").prop("required", true);
		} else {
			$(".originalClaimInputWrapper").hide();
			$(".originalClaimInputWrapper input").prop("required", false);
			if(value == 15){
				$(".dateWrapper").show();
				$(".payedAfterWrapper").show();
			}
			if(value == 9 || value == 10) {			
				$(".courtFeeReleasedDate").show();
			}
		}
	}).change();
	$(".datefield").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1
	})
	$(".original_amount_input").keyup(function() {
		$(".amount_input").val($(this).val());
	})
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			// data.imagesToProcess = imagesToProcess;
			// data.imagesHandle = imagesHandle;
			// data.images = images;

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					fw_loading_end();
					/*if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {*/
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					//}
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