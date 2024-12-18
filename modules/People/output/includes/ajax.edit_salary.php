<?php
$action = isset($_POST['action']) ? $_POST['action'] : "";
if($moduleAccesslevel > 10)
{
	if($action != ""){
		$s_sql = "SELECT * FROM contactperson
		WHERE contactperson.id = ?";
		$o_result = $o_main->db->query($s_sql, array($_POST['employeeid']));
		$peopleData = $o_result ? $o_result->row_array() : array();
		$value = intval($_POST['value']);
		if($action == "defaultSalaryRepeatingorder" && $peopleData && $value > 0)
		{
			$s_sql = "UPDATE contactperson SET default_salary_repeatingorder = ? WHERE id = ?";
			$o_main->db->query($s_sql, array($value, $peopleData['id']));
			return;
		}
		if($action == "defaultSalaryProject" && $peopleData && $value > 0)
		{
			$s_sql = "UPDATE contactperson SET default_salary_project = ? WHERE id = ?";
			$o_main->db->query($s_sql, array($value, $peopleData['id']));
			return;
		}
		if($action == "updateSalarySource" && $peopleData)
		{
			$salary_source = $_POST['salary_source'];
			if($salary_source == ""){
				$s_sql = "DELETE FROM peoplesalary WHERE peopleId = ?";
				$o_main->db->query($s_sql, array($peopleData['id']));
				return;
			}
		}
	}

	if(isset($_POST['output_form_submit']))
	{

		$dateFromPost = $_POST['dateFrom'];
		$dateToPost = $_POST['dateTo'];
		$stdOrIndividualRate = $_POST['wagetype'];
		$standartWageRateId = $_POST['wageId'];
		$rate = str_replace(",", ".",$_POST['rate']);
		$individualRateSalaryCode = $_POST['individualRateSalaryCode'];
		$standartWageRateGroupId = $_POST['standartWageRateGroupId'];
		$hourlyRate = str_replace(",", ".", $_POST['hourlyRate']);
		$dateFrom = "0000-00-00";
		// $dateTo = "0000-00-00";
		$dateFromTime = 0;
		// $dateToTime = 0;
		if($stdOrIndividualRate == 0){
			$rate = "";
			$individualRateSalaryCode = "";
			$hourlyRate = "";
		} else if($stdOrIndividualRate == 1){
			$standartWageRateId = 0;
			$hourlyRate = "";
		} else if ($stdOrIndividualRate == 2) {
			$rate = "";
			$individualRateSalaryCode = "";
			$standartWageRateId = 0;
		} else if ($stdOrIndividualRate == 3) {
			$rate = "";
			$individualRateSalaryCode = "";
			$standartWageRateId = 0;
		} else if ($stdOrIndividualRate == 4) {
			$rate = "";
			$individualRateSalaryCode = "";
			$standartWageRateId = 0;
		}
		if($dateFromPost != ""){
			$dateFromTime = strtotime($dateFromPost);
			// $dateToTime = $dateFromTime + 1;
			$dateFrom = date("Y-m-d", $dateFromTime);
		}
		// if($dateToPost != ""){
		// 	$dateToTime = strtotime($dateToPost);
		// 	$dateTo = date("Y-m-d", $dateToTime);
		// }
		// if($dateFromTime > 0 ) {
			$workleaderIdSql = "";
			if(isset($_POST['salaryid']) && $_POST['salaryid'] > 0)
			{
				$workleaderIdSql = " AND peoplesalary.id <> '".$_POST['salaryid']."'";
			}

			$s_fields = ",
			stdOrIndividualRate = '".$o_main->db->escape_str($stdOrIndividualRate)."',
			standardwagerate_group_id = '".$o_main->db->escape_str($standartWageRateGroupId)."',
			standardWageRateId = '".$o_main->db->escape_str($standartWageRateId)."',
			rate = '".$o_main->db->escape_str($rate)."',
			individualRateSalaryCode = '".$o_main->db->escape_str($individualRateSalaryCode)."',
			peopleId = '".$o_main->db->escape_str($_POST['employeeid'])."',
			hourlyRate = '".$o_main->db->escape_str($hourlyRate)."',
			fixed_salary_choice = '".$o_main->db->escape_str($_POST['fixed_salary_choice'])."'";
			if(isset($_POST['salaryid']) && $_POST['salaryid'] > 0)
			{
				$s_sql = "UPDATE peoplesalary SET
				updated = now(),
				updatedBy = ?".$s_fields."
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['salaryid']));
			} else {
				$s_sql = "INSERT INTO peoplesalary SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy = ?".$s_fields;
				$o_main->db->query($s_sql, array($moduleID, $variables->loggID));
				$fw_return_data = $o_main->db->insert_id();

				$s_sql = "SELECT * FROM peoplesalary
				WHERE peoplesalary.peopleId = ?";
				$o_result = $o_main->db->query($s_sql, array($_POST['employeeid']));
				if($o_result){
					$rowsCount = $o_result->num_rows();
					if($rowsCount == 1){
						$s_sql = "UPDATE contactperson SET default_salary_repeatingorder = ?, default_salary_project = ? WHERE id = ?";

						$o_main->db->query($s_sql, array($fw_return_data, $fw_return_data, $_POST['employeeid']));
					}
				}

			}
			if($stdOrIndividualRate == 0){
				if($standartWageRateGroupId == 0) {
					$s_sql = "DELETE FROM peoplesalary WHERE peopleId = ?";
					$o_main->db->query($s_sql, array( $_POST['employeeid']));
				}
			}

			// $peopleId - needed for sync script
			$peopleId = $_POST['employeeid'];
			include("sync_people.php");

			$fw_return_data = $_POST['employeeid'];
			// print $s_sql;
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
			return;
		// } else {
		// 	$fw_error_msg[] = $formText_TheDatesAreIncorrect_output;
		// 	return;
		// }

	} else if(isset($_POST['output_delete'])) {
		if(isset($_GET['cid']) && $_GET['cid'] > 0)
		{
			$s_sql = "DELETE FROM peoplesalary WHERE id = ?";
			$o_main->db->query($s_sql, array($_GET['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['employeeId'];
		return;
	}
}
if(isset($_POST['salaryid']) && $_POST['salaryid'] > 0)
{
	$s_sql = "SELECT * FROM peoplesalary
	WHERE peoplesalary.id = ?";
	$o_result = $o_main->db->query($s_sql, array($_POST['salaryid']));
	$v_data = $o_result ? $o_result->row() : array();

	$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate WHERE id = ?", array($v_data->standardWageRateId));
	$wageData = $wageData_sql ? $wageData_sql->row_array() : array();
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_salary";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="salaryid" value="<?php print $_POST['salaryid'];?>">
		<input type="hidden" name="employeeid" value="<?php print $_POST['employeeid'];?>">
		<div class="inner">
			<?php /*?><div class="line ">
				<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace nameInput" name="name" type="text" value="<?php if($v_data->name != "") { echo $v_data->name ; } ?>" autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_DateFrom_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace" name="dateFrom" type="text" value="<?php if($v_data->dateFrom != "0000-00-00" && $v_data->dateFrom != null) { echo date("d.m.Y", strtotime($v_data->dateFrom)); } ?>" id="dateFromField" required autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>*/?>

			<div class="line" style="display: none;">
				<div class="lineTitle"><?php echo $formText_WageType_Output; ?></div>
				<div class="lineInput">
					<input type="radio" class="wagetype" name="wagetype" value="0" <?php if($salary_source != "") {if($salary_source == 0) echo 'checked';} else {if($v_data->stdOrIndividualRate == 0 || $v_data == null){ ?> checked<?php }} ?> id="standardWage"><label for="standardWage"><?php echo $formText_Specified_output?></label>
					<input type="radio" class="wagetype" name="wagetype" value="1" <?php if($salary_source != "") {if($salary_source == 1) echo 'checked';} else {if($v_data->stdOrIndividualRate == 1){ ?> checked<?php }} ?> id="individualWage"><label for="individualWage"><?php echo $formText_Individual_output?></label>
					<input type="radio" class="wagetype" name="wagetype" value="2" <?php if($salary_source != "") {if($salary_source == 2) echo 'checked';} else {if($v_data->stdOrIndividualRate == 2){ ?> checked<?php }} ?> id="sendingInvoice"><label for="sendingInvoice"><?php echo $formText_SendingInvoice_output?></label>
					<input type="radio" class="wagetype" name="wagetype" value="3" <?php if($salary_source != "") {if($salary_source == 3) echo 'checked';} else {if($v_data->stdOrIndividualRate == 3){ ?> checked<?php }} ?> id="fixedSalary"><label for="fixedSalary"><?php echo $formText_FixedSalary_output?></label>

				</div>
				<div class="clear"></div>
			</div>

			<div class="line fixedSalary">
				<div class="lineTitle"><?php echo $formText_Choose_Output; ?></div>
				<div class="lineInput">
					<select class="chooseDropdown" name="fixed_salary_choice" autocomplete="off">
						<option value="0" <?php if($v_data->fixed_salary_choice == 0) echo 'selected';?>><?php echo $formText_SalaryGroup_output;?></option>
						<option value="1" <?php if($v_data->fixed_salary_choice == 1) echo 'selected';?>><?php echo $formText_HourlyRate_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line standartWage fixedSalary individualWage standartWageGroupWrapper">
				<div class="lineTitle">
					<span class="standartWage fixedSalary">
						<?php echo $formText_StandartWageGroup_Output; ?>
					</span>
					<span class="individualWage sendingInvoice">
						<?php echo $formText_UseAdditionSettingFrom_Output; ?>
					</span>
				</div>
				<div class="lineInput">
					<select class="standartWageGroup" name="standartWageRateGroupId" autocomplete="off">
						<option value="0"><?php echo $formText_DefaultGroup_output;?></option>
						<?php

						$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate_group ORDER BY name");
						$rateGroups = $wageData_sql ? $wageData_sql->result_array() : array();
						foreach($rateGroups as $rateGroup) {
							?>
							<option value="<?php echo $rateGroup['id'];?>" <?php if($rateGroup['id'] == $v_data->standardwagerate_group_id) echo 'selected';?>><?php echo $rateGroup['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line individualWage">
				<div class="lineTitle"><?php echo $formText_IndividualWage_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace" name="rate" type="text" value="<?php if($v_data->rate != "") { echo number_format($v_data->rate, 2, ",", ""); } ?>" autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line individualWage">
				<div class="lineTitle"><?php echo $formText_IndividualWageSalaryCode_Output; ?></div>
				<div class="lineInput">
					<select class="individualRateSalaryCode" name="individualRateSalaryCode" autocomplete="off">
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php

						$wageData_sql = $o_main->db->query("SELECT * FROM salarycode ORDER BY name");
						$salarycodes = $wageData_sql ? $wageData_sql->result_array() : array();
						foreach($salarycodes as $salarycode) {
							?>
							<option value="<?php echo $salarycode['salarycode'];?>" <?php if($salarycode['salarycode'] == $v_data->individualRateSalaryCode) echo 'selected';?>><?php echo $salarycode['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line sendingInvoice hourlyRateWrapper">
				<div class="lineTitle"><?php echo $formText_HourlyRate_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace" name="hourlyRate" type="text" value="<?php if($v_data->hourlyRate != "") { echo number_format($v_data->hourlyRate,2,",",""); } ?>" autocomplete="off">
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
<style>
.individualWage {
	display: none;
}
</style>
<div id="popupeditbox2" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent2"></div>
</div>

<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>

<!-- <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">
$(function() {
	var wetPopupAC, wetPopupOptionsAC={
    	follow: [true, true],
		followSpeed: 200,
		fadeSpeed: 0,
		closeClass:'b-close',
    	onOpen: function(){
    		$(this).addClass('opened');
    	},
    	onClose: function(){
    		$(this).removeClass('opened');
    		if($(this).is('.close-reload')) output_reload_page()
    	}
    };
	<?php if($v_data->dateFrom != "0000-00-00" && $v_data->dateFrom != null) { ?>
		$("#dateFromField").datepicker({
			dateFormat: "dd.mm.yy",
			defaultDate: '<?php echo date("d.m.Y",strtotime($v_data->dateFrom))?>'
		});
	<?php } else { ?>
		$("#dateFromField").datepicker({
			dateFormat: "dd.mm.yy"
		});
	<?php } ?>
	<?php if($v_data->dateTo != "0000-00-00" && $v_data->dateTo != null) { ?>
		$("#dateToField").datepicker({
			dateFormat: "dd.mm.yy",
			defaultDate: '<?php echo date("d.m.Y",strtotime($v_data->dateTo))?>'
		});
	<?php } else { ?>
		$("#dateToField").datepicker({
			dateFormat: "dd.mm.yy"
		});
	<?php } ?>
	$(".wagetype").change(function(){
		if($('input[name=wagetype]:checked').val() == 0){
			$(".individualWage").hide();
			$(".individualWage").find("input").prop("required", false);
			$(".individualRateSalaryCode").prop("required", false);
			$(".sendingInvoice").hide();
			$(".fixedSalary").hide();
			$(".standartWage").show();
			$(".standartWage").find("input").prop('required',true);
		} else if($('input[name=wagetype]:checked').val() == 1){
			$(".standartWage").hide();
			$(".sendingInvoice").hide();
			$(".standartWage").find("input").prop('required',false);
			$(".fixedSalary").hide();
			$(".individualWage").show();
			$(".individualWage").find("input").prop("required", true);
			$(".individualRateSalaryCode").prop("required", true);
		} else if($('input[name=wagetype]:checked').val() == 2){
			$(".standartWage").hide();
			$(".individualWage").hide();
			$(".individualWage").find("input").prop("required", false);
			$(".individualRateSalaryCode").prop("required", false);
			$(".standartWage").find("input").prop('required',false);
			$(".fixedSalary").hide();
			$(".sendingInvoice").show();
		} else if($('input[name=wagetype]:checked').val() == 3) {
			$(".standartWage").hide();
			$(".individualWage").hide();
			$(".individualWage").find("input").prop("required", false);
			$(".individualRateSalaryCode").prop("required", false);
			$(".sendingInvoice").hide();
			$(".standartWage").find("input").prop('required',false);
			$(".fixedSalary").show();
		}
	})
	$(".wagetype").change();
	$(".standartWageGroup").change(function(){
		$("#wageId").val(0);
		$(".selectWage").html("<?php echo $formText_SelectTheWage_output;?>");
	})

	$(".chooseDropdown").change(function(){
		if($('input[name=wagetype]:checked').val() == 3){
			if($(this).val() == 0){
				$(".hourlyRateWrapper").hide();
				$(".standartWageGroupWrapper").show();
			} else if($(this).val() == 1){
				$(".standartWageGroupWrapper").hide();
				$(".hourlyRateWrapper").show();
			}
		}
	}).change();

	$(".selectWage").unbind("click").bind("click", function(e){
		e.preventDefault();
		var _data = { fwajax: 1, fw_nocss: 1, wage_group_id: $(".standartWageGroup").val()};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_wages";?>',
			data: _data,
			success: function(obj){
				$('#popupeditboxcontent2').html('');
				$('#popupeditboxcontent2').html(obj.html);
				out_popup = $('#popupeditbox2').bPopup(wetPopupOptionsAC);
				$("#popupeditbox2:not(.opened)").remove();
			}
		});
	})

	$("form.output-worker-form").validate({
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
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_loading_end();
					} else {
						if(data.redirect_url !== undefined){
							window.location = data.redirect_url;
						} else {
							fw_loading_end();
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
				$(".error-span").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		},
		errorPlacement: function(error, element) {
		    if (element.attr("name") == "wageId" )
		        $("<span class='error-span'><?php echo $formText_SelectTheWage_output;?></span>").insertAfter(".selectWage");
		}
	});
});
</script>
<style>
.popupform .descriptionText {
	padding: 10px 0px;
}
.error-span {
	color: #c11;
	margin-left: 10px;
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
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
