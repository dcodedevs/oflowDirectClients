<?php
$collecting_case_id = $_POST['collecting_case_id'];

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['close']){
			$s_sql = "UPDATE collecting_company_case_paused SET
			updated = now(),
			updatedBy= ?,
			closed_date = NOW(),
			closed_comment = ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['closed_comment'], $_POST['cid']));

			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
			return;
		} else if($_POST['delete']) {
			$s_sql = "DELETE FROM collecting_company_case_paused  WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
			return;
		} else {
			if($_POST['created_date'] != "") {
				if(isset($_POST['pause_reason'])) {

					if(isset($_POST['cid']) && $_POST['cid'] > 0)
					{
						$s_sql = "SELECT * FROM collecting_company_case_paused WHERE id = ?";
					    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
					    if($o_query && $o_query->num_rows() == 1) {
							$s_sql = "UPDATE collecting_company_case_paused SET
							updated = now(),
							updatedBy= ?,
							collecting_company_case_id = ?,
							created_date = ?,
							pause_reason_comment = ?,
							pause_reason = ?
							WHERE id = ?";
							$o_main->db->query($s_sql, array($variables->loggID, $collecting_case_id, date("Y-m-d", strtotime($_POST['created_date'])), $_POST['pause_reason_comment'], $_POST['pause_reason'], $_POST['cid']));
						}
					} else {
						$s_sql = "INSERT INTO collecting_company_case_paused SET
						id=NULL,
						moduleID = ?,
						created = now(),
						createdBy= ?,
						collecting_company_case_id = ?,
						created_date = ?,
						pause_reason_comment = ?,
						pause_reason = ?";
						$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $collecting_case_id, date("Y-m-d", strtotime($_POST['created_date'])), $_POST['pause_reason_comment'], $_POST['pause_reason']));
						$_POST['cid'] = $objectionId = $o_main->db->insert_id();

					}

					$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
					return;
				} else {
					$fw_error_msg[] = $formText_WrongReason_output;
				}
			} else {
				$fw_error_msg[] = $formText_FillInDate_output;
			}
		}
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_company_case_paused WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_company_case_paused WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$s_sql = "select * from collecting_company_cases where id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['collecting_case_id']));
$collecting_case = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT creditor.*  FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array())
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_paused";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<input type="hidden" name="collecting_case_id" value="<?php print $_POST['collecting_case_id'];?>">
		<input type="hidden" name="reason" value="<?php print $_POST['reason'];?>">


			<?php if($_POST['close']) { ?>
				<input type="hidden" name="close" value="<?php print $_POST['close'];?>">
				<div class="inner">
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ClosedComment_Output; ?></div>
						<div class="lineInput"><textarea class="popupforminput botspace" name="closed_comment" required><?php echo $v_data['closed_comment'];?></textarea></div>
						<div class="clear"></div>
					</div>
				</div>
				<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_CloseObjection_Output; ?>"></div>
			<?php } else { ?>
				<div class="inner">
					<?php
					$type_messages = array(
						$formText_ReturnedLetters_Output, 
						$formText_PausedByCollectingCompany_output, 
						$formText_PausedByCreditor_output, 
						$formText_StoppedWithMessageFromDebitor_output,
						$formText_WantsInvoiceCopy_output,
						$formText_WantsInstallmentPayment_output,
						$formText_WantsDefermentOfPayment_output, 
						$formText_HasAnObjection_output,
						$formText_StoppedWithOtherReason_output);
					?>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_ObjectionType_Output; ?></div>
						<div class="lineInput">
							<select name="pause_reason">
								<option value=""><?php echo $formText_Select_output;?></option>
								<?php foreach($type_messages as $key => $type_message) { ?>
									<option value="<?php echo $key;?>" <?php if($v_data['pause_reason'] == $key) echo 'selected';?>><?php echo $type_messages[$key];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_Date_Output; ?></div>
						<div class="lineInput">
							<input type="text" value="<?php if($v_data['created_date'] != "" && $v_data['created_date'] != "0000-00-00") echo date("d.m.Y", strtotime($v_data['created_date']));?>" class="popupforminput botspace datepicker" name="created_date" autocomplete="off"/>
						</div>
						<div class="clear"></div>
					</div>
					<div class="line">
						<div class="lineTitle"><?php echo $formText_Comment_Output; ?></div>
						<div class="lineInput"><textarea class="popupforminput botspace" name="pause_reason_comment" ><?php echo $v_data['pause_reason_comment'];?></textarea></div>
						<div class="clear"></div>
					</div>
					<?php if($_POST['reason'] == "returned_letter") { ?>
						<div class="line">
							<div class="lineTitle"><?php echo $formText_Solved_Output; ?></div>
							<div class="lineInput">
								<input type="checkbox" value="1" class="popupforminput botspace checkbox" name="solved" autocomplete="off" <?php if($v_data['closed_date'] != "" && $v_data['closed_date'] != "0000-00-00 00:00:00") echo 'checked';?>/>
							</div>
							<div class="clear"></div>
						</div>
					<?php } else { ?>

					<?php } ?>
				</div>
				<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
			<?php } ?>
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
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {
	$(".datepicker").datepicker({
		dateFormat: "dd.mm.yy",
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
					if(data.error !== undefined)
					{
						$("#popup-validate-message").html("");
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show()
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
