<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{

		$monthlyPayment = str_replace(" ", "", str_replace(",", ".", $_POST['monthly_payment']));
		$firstPaymentDate = date("Y-m-d", strtotime($_POST['first_payment_date']));
		$nextPaymentDate = "";
		if($_POST['next_payment_date'] != "0000-00-00" && $_POST['next_payment_date'] != ""){
			$nextPaymentDate = date("Y-m-d", strtotime($_POST['next_payment_date']));
		}

		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		    if($o_query && $o_query->num_rows() == 1) {
				$s_sql = "UPDATE collecting_cases_payment_plan SET
				updated = now(),
				updatedBy= ?,
				monthly_payment_day_nr = ?,
				first_payment_date = ?,
				next_payment_date = ?,
				monthly_payment = ?
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['monthly_payment_day_nr'], $firstPaymentDate,$nextPaymentDate, $monthlyPayment, $_POST['cid']));
			}
		} else {
			$s_sql = "INSERT INTO collecting_cases_payment_plan SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			collecting_case_id = ?,
			monthly_payment_day_nr= ?,
			first_payment_date= ?,
			next_payment_date = ?,
			monthly_payment = ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['caseId'], $_POST['monthly_payment_day_nr'], $firstPaymentDate,$nextPaymentDate, $monthlyPayment));
			$_POST['cid'] = $o_main->db->insert_id();
		}
		if($_POST['cid'] > 0) {
			// $s_sql = "SELECT * FROM collecting_cases_payment_plan_lines WHERE collecting_cases_payment_plan_id = ?";
		    // $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
			// $collecting_cases_payment_plan_lines = $o_query ? $o_query->result_array() : array();
			// if(count($collecting_cases_payment_plan_lines) == 0) {
			// 	$dayNumber = date("d", strtotime($firstPaymentDate));
			// 	$nextPaymentDate = date("Y-m-".$dayNumber, strtotime($firstPaymentDate . " +1 month"));
			//
			// 	$s_sql = "INSERT INTO collecting_cases_payment_plan_lines SET
			// 	id=NULL,
			// 	moduleID = '".$o_main->db->escape_str($moduleID)."',
			// 	created = now(),
			// 	createdBy= '".$o_main->db->escape_str($variables->loggID)."',
			// 	status = 0,
			// 	due_date = '".$o_main->db->escape_str($firstPaymentDate)."',
			// 	amount_to_pay = '".$o_main->db->escape_str($monthlyPayment)."',
			// 	collecting_cases_payment_plan_id = '".$o_main->db->escape_str($_POST['cid'])."',
			// 	payed = 0";
			// 	$o_main->db->query($s_sql);
			// }
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_cases_payment_plan WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_payment_plan WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_payment_plan";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">

	<div class="inner">
		<div class="line ">
			<div class="lineTitle"><?php echo $formText_FirstPaymentDate_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="first_payment_date" value="<?php if($v_data['first_payment_date'] != "0000-00-00" && $v_data['first_payment_date'] != ""){ echo date("d.m.Y", strtotime($v_data['first_payment_date'])); }?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<?php if($v_data) { ?>
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_NextPaymentDate_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datefield" autocomplete="off"  name="next_payment_date" value="<?php if($v_data['next_payment_date'] != "0000-00-00" && $v_data['next_payment_date'] != ""){ echo date("d.m.Y", strtotime($v_data['next_payment_date'])); }?>">
				</div>
				<div class="clear"></div>
			</div>
		<?php } ?>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_MonthlyPayment_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="monthly_payment" value="<?php echo number_format($v_data['monthly_payment'], 2, ",", ""); ?>" required>
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
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {

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
