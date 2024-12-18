<?php
if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM contactperson WHERE id = ?";
	$o_result = $o_main->db->query($s_sql, array($_POST['cid']));
	if($o_result && $o_result->num_rows() > 0) $v_data = $o_result->row();
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0) {
			$seniorityStartDate = "";
			$seniorityReminderDate = "";

			if($_POST['seniority_salary'] == 1){
				if($_POST['seniorityStartDate'] != ""){
					$seniorityStartDate = date("Y-m-d", strtotime($_POST['seniorityStartDate']));
				}
			}
			if($_POST['seniority_salary'] == 2){
				if($_POST['seniority_reminder_consider_new_adjustment_from_date'] != ""){
					$seniorityReminderDate = date("Y-m-d", strtotime("01.".$_POST['seniority_reminder_consider_new_adjustment_from_date']));
				}
			}

			$s_sql = "UPDATE contactperson SET
			updated = now(),
			updatedBy = ?,
			seniorityStartDate = ?,
			seniority_salary = ?,
			seniority_years = ?,
			seniority_reminder_consider_new_adjustment_from_date = ?,
			seniority_note = ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, str_replace(",", ".", $seniorityStartDate),
			$_POST['seniority_salary'], $_POST['seniority_years'], $seniorityReminderDate, $_POST['seniority_note'], $_POST['cid']));
			$fw_return_data = $_POST['cid'];

			// $peopleId - needed for sync script
			$peopleId = $_POST['cid'];
			include("sync_people.php");
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_employee_seniority";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_SenioritySalary_Output; ?></div>
				<div class="lineInput">
					<select class="senioritySalaryChange" name="seniority_salary" autocomplete="off">
						<option value="0" <?php if($v_data->seniority_salary == 0) echo 'selected';?>><?php echo $formText_NotActivated_output;?></option>
						<option value="1" <?php if($v_data->seniority_salary == 1) echo 'selected';?>><?php echo $formText_AdjustAutomaticallyFromSeniorityDate_output;?></option>
						<option value="2" <?php if($v_data->seniority_salary == 2) echo 'selected';?>><?php echo $formText_AdjustManually_output;?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="automaticWrapper">
				<div class="line">
					<div class="lineTitle"><?php echo $formText_SeniorityStartDate_Output; ?></div>
					<div class="lineInput"><input class="popupforminput botspace datepicker" id="seniorityStartDate" readonly name="seniorityStartDate" type="text" value="<?php if($v_data->seniorityStartDate != "0000-00-00" && $v_data->seniorityStartDate != null) { echo date("d.m.Y", strtotime($v_data->seniorityStartDate)); } ?>" autocomplete="off"></div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="manualWrapper">

				<div class="line">
					<div class="lineTitle"><?php echo $formText_SeniorityYears_Output; ?></div>
					<div class="lineInput"><input class="popupforminput botspace" id="seniorityYears" name="seniority_years" type="text" value="<?php echo $v_data->seniority_years; ?>" autocomplete="off"></div>
					<div class="clear"></div>
				</div>
				<div class="line">
					<div class="lineTitle"><?php echo $formText_NextAdjustmentMonth_output; ?></div>
					<div class="lineInput">
						<input class="popupforminput botspace datemonthPicker month_year_datepicker" readonly id="seniority_reminder_consider_new_adjustment_from_date" name="seniority_reminder_consider_new_adjustment_from_date" type="text" value="<?php if($v_data->seniority_reminder_consider_new_adjustment_from_date != "0000-00-00" && $v_data->seniority_reminder_consider_new_adjustment_from_date != null) { echo date("m.Y", strtotime($v_data->seniority_reminder_consider_new_adjustment_from_date)); } ?>" autocomplete="off">
					</div>
					<div class="clear"></div>
				</div>
				<div class="line">
				<div class="lineTitle"><?php echo $formText_Note_Output; ?></div>
				<div class="lineInput"><textarea class="popupforminput" name="seniority_note" ><?php echo $v_data->seniority_note;?></textarea></div>
				<div class="clear"></div>
				</div>
			</div>



		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
	</form>
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
	$(".senioritySalaryChange").change(function(){
		var value = $(this).val();
		if(value == 1) {
			$(".automaticWrapper").show();
			$(".manualWrapper").hide();

			$("#seniorityStartDate").prop("required", true);
			$("#seniority_reminder_consider_new_adjustment_from_date").prop("required", false);
			$("#seniorityYears").prop("required", false);
		} else if(value == 2){
			$(".automaticWrapper").hide();
			$(".manualWrapper").show();

			$("#seniorityStartDate").prop("required", false);
			$("#seniority_reminder_consider_new_adjustment_from_date").prop("required", true);
			$("#seniorityYears").prop("required", true);
		} else if(value == 0) {
			$(".automaticWrapper").hide();
			$(".manualWrapper").hide();
			$("#seniorityStartDate").prop("required", false);
			$("#seniority_reminder_consider_new_adjustment_from_date").prop("required", false);
			$("#seniorityYears").prop("required", false);
		}
	}).change();

	$(".datepicker").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1,
		changeMonth: true,
    	changeYear: true
	});
	$(".datemonthPicker").datepicker({
	 	dateFormat: "mm.yy",
	    changeMonth: true,
	    changeYear: true,
	    showButtonPanel: true,
	    onClose: function(dateText, inst) {
	        inst.dpDiv.removeClass('month_year_datepicker');
	        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
	        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
	        $(this).datepicker('setDate', new Date(year, month, 1));
	    },
	    beforeShow : function(input, inst) {
	        inst.dpDiv.addClass('month_year_datepicker');

	        if ((datestr = $(this).val()).length > 0) {
	            year = datestr.substring(datestr.length-4, datestr.length);
	            month = datestr.substring(0, 2);
	            $(this).datepicker('option', 'defaultDate', new Date(year, month-1, 1));
	    	}
	    }
	})

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
					if(data.redirect_url !== undefined) window.location = data.redirect_url;
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

.month_year_datepicker .ui-datepicker-calendar {
	display: none;
}
.manualWrapper {
	display: none;
}
.automaticWrapper {
	display: none;
}
.popupform input.popupforminput.checkbox {
	width: auto;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.popupeditbox label.error { display: none !important; }
input.error { border-color:#c11; }
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
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
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
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
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
