<?php
$s_sql = "SELECT * FROM collecting_cases_accountconfig ORDER BY id";
$o_query = $o_main->db->query($s_sql);
$v_collectingcases_accountconfig = $o_query && $o_query->num_rows()>0 ? $o_query->row_array() : array();


if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		$claimType = $_POST['claim_type'];
		if($claimType != 8) {
			$originalDueDate = "";
			if($_POST['original_due_date'] != "" && $claimType == 1) {
				$originalDueDate = date("Y-m-d", strtotime($_POST['original_due_date']));
			}
			$_POST['amount'] = str_replace(" ", "", str_replace(",", ".", $_POST['amount']));
			$interestPercent = str_replace(" ", "", str_replace(",", ".", $_POST['interest_percent']));
			if(isset($_POST['cid']) && $_POST['cid'] > 0)
			{
				$s_sql = "SELECT * FROM collecting_cases_claim_lines WHERE id = ?";
			    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
			    if($o_query && $o_query->num_rows() == 1) {
					$s_sql = "UPDATE collecting_cases_claim_lines SET
					updated = now(),
					updatedBy= ?,
					name= ?,
		            original_due_date='".$o_main->db->escape_str($originalDueDate)."',
		            claim_type='".$o_main->db->escape_str($claimType)."',
					amount= ?,
					interest_percent= ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['amount'], $interestPercent, $_POST['cid']));
				}
			} else {
				$s_sql = "INSERT INTO collecting_cases_claim_lines SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy= ?,
				collecting_case_id = ?,
				name= ?,
	            original_due_date='".$o_main->db->escape_str($originalDueDate)."',
	            claim_type='".$o_main->db->escape_str($claimType)."',
				amount= ?,
				interest_percent= ?";
				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['caseId'], $_POST['name'], $_POST['amount'], $interestPercent));
				$_POST['cid'] = $o_main->db->insert_id();
			}
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
			return;
		}
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_cases_claim_lines WHERE id = ?";
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
						if($claim_type['id'] != 8) {
						?>
						<option value="<?php echo $claim_type['id'];?>" <?php if($v_data['claim_type'] == $claim_type['id']) echo 'selected';?>><?php echo $claim_type['type_name'];?></option>
							<?php
						}
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
		<?php if($v_data['claim_type'] != 5) { ?>
			<div class="line originalClaimInputWrapper">
				<div class="lineTitle"><?php echo $formText_OriginalDueDate_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="original_due_date" value="<?php if($v_data['original_due_date'] != "0000-00-00" && $v_data['original_due_date'] != ""){ echo date("d.m.Y", strtotime($v_data['original_due_date'])); }?>" required>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="amount" value="<?php echo number_format($v_data['amount'], 2, ",", ""); ?>" required>
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
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">


$(function() {
	$(".claimTypeChange").change(function(){
		var value = $(this).val();
		if(value == 1){
			$(".originalClaimInputWrapper").show();
			$(".originalClaimInputWrapper input").prop("required", true);
		} else {
			$(".originalClaimInputWrapper").hide();
			$(".originalClaimInputWrapper input").prop("required", false);
		}
	}).change();
	$(".datefield").datepicker({
		dateFormat: "d.m.yy",
		firstDay: 1
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
