<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['deleteWorker']))
	{
		$workLeaderId = intval($_POST['workplanlineworkerId']);
		if($workLeaderId > 0)
		{
			$s_sql = "DELETE FROM workplanlineworker WHERE id = ?";
			$o_main->db->query($s_sql, array($workLeaderId));
		}
		$fw_return_data = $_POST['customerId'];
		// print $s_sql;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	}
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['estimatedTimeuse'] != "" && $_POST['employeeId'] != ""){
			$date = date("Y-m-d", strtotime($_POST['dateday']));

			$s_fields = ", employeeId='".$o_main->db->escape_str($_POST['employeeId'])."',
			estimatedTimeuse = '".$o_main->db->escape_str(str_replace(",", ".", $_POST['estimatedTimeuse']))."',
			absenceDueToIllness = '".$o_main->db->escape_str($_POST['absenceDueToIllness'])."',
			startTimeFrom = '".$o_main->db->escape_str($_POST['startTimeFrom'])."',
			startTimeTo = '".$o_main->db->escape_str($_POST['startTimeTo'])."',
			parentId = '".intval($_POST['parentId'])."',
			salaryAddition = '".$o_main->db->escape_str($_POST['addition'])."',
			orderId = '".($_POST['orderId'])."',
			date='".$date."'";

			if(isset($_POST['workplanlineworkerId']) && $_POST['workplanlineworkerId'] > 0)
			{
				$s_sql = "UPDATE workplanlineworker SET
				updated = now(),
				updatedBy='".$variables->loggID."'".$s_fields."
				WHERE id = '".$o_main->db->escape_str($_POST['workplanlineworkerId'])."'";
				$o_main->db->query($s_sql);
			} else {
				$s_sql = "INSERT INTO workplanlineworker SET
				id=NULL,
				moduleID = '".$moduleID."',
				created = now(),
				createdBy='".$variables->loggID."'".$s_fields;
				$o_main->db->query($s_sql);
				$fw_return_data = $o_main->db->insert_id();
			}
			$fw_return_data = $_POST['repeatingOrderId'];
			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
			return;
		} else {
			$fw_error_msg[] = $formText_PleaseFillInTheFields_output;
		}
	}
}

if(isset($_POST['workplanlineworkerId']) && $_POST['workplanlineworkerId'] > 0) {
	$s_sql = "SELECT *, workplanlineworker.id FROM workplanlineworker
									LEFT OUTER JOIN employee ON employee.id = workplanlineworker.employeeId
									WHERE workplanlineworker.id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['workplanlineworkerId']));
	$repeatingOrderWorklineWorker = ($o_query->num_rows() > 0 ? $o_query->row_array() : array());
}

$copy = false;

if(isset($_POST['copy']) && $_POST['copy']){
	$copy = true;
}

$s_sql = "SELECT * FROM salaryadditionrate ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$additions = ($o_query->num_rows()>0 ? $o_query->result_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_orderlineworker";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
		<input type="hidden" name="orderId" value="<?php print $_POST['orderId'];?>">
		<?php if($copy) { ?>
			<input type="hidden" name="parentId" value="<?php print $_POST['workplanlineworkerId'];?>">
			<input type="hidden" name="workplanlineworkerId" value="">
		<?php } else { ?>
			<input type="hidden" name="parentId" value="<?php echo $repeatingOrderWorklineWorker['parentId']?>">
			<input type="hidden" name="workplanlineworkerId" value="<?php print $_POST['workplanlineworkerId'];?>">
		<?php } ?>
		<div class="inner">

			<div class="line">
				<div class="lineTitle"><?php echo $formText_Worker_Output; ?></div>
				<div class="lineInput">
					<?php if($repeatingOrderWorklineWorker && !$copy) { ?>
					<a href="#" class="selectWorker"><?php echo $repeatingOrderWorklineWorker['name']?></a>
					<?php } else { ?>
					<a href="#" class="selectWorker"><?php echo $formText_SelectWorker_Output;?></a>
					<?php } ?>
					<input type="hidden" name="employeeId" id="employeeId" value="<?php print $repeatingOrderWorklineWorker['employeeId'];?>">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Date_Output; ?></div>
				<div class="lineInput">
					<input type="text" name="dateday" value="<?php if($repeatingOrderWorklineWorker['date'] != "") echo date("d.m.Y", strtotime($repeatingOrderWorklineWorker['date']));?>" class="dateday" required  autocomplete="off"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_StartTime_Output; ?></div>
				<div class="lineInput">
					<input type="text" name="startTimeFrom" value="<?php echo $repeatingOrderWorklineWorker['startTimeFrom'];?>" class="starttimeinput" required autocomplete="off"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_StartTimeUntil_Output; ?></div>
				<div class="lineInput">
					<input type="text" name="startTimeTo" value="<?php echo $repeatingOrderWorklineWorker['startTimeTo'];?>" class="starttimeinput" autocomplete="off"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_EstimatedTimeuse_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace" name="estimatedTimeuse" type="text" value="<?php echo number_format($repeatingOrderWorklineWorker['estimatedTimeuse'], 2, ",", "")?>" required autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Addition_Output; ?></div>
				<div class="lineInput">
					<select name="addition">
						<option value="0"><?php echo $formText_None_output;?></option>
						<?php foreach($additions as $addition) { ?>
							<option value="<?php echo $addition['id']?>" <?php if($repeatingOrderWorklineWorker['salaryAddition'] == $addition['id']) echo 'selected';?>><?php echo $addition['name'];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<?php if(!$copy){?>
				<div class="line">
					<div class="lineTitle"><label for="absence"><?php echo $formText_AbbsenceDueToIllness_Output; ?></label></div>
					<div class="lineInput">
						<input class="botspace" name="absenceDueToIllness" id="absence" type="checkbox" value="1" <?php if($repeatingOrderWorklineWorker['absenceDueToIllness']) echo 'checked';?>>
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
<div id="popupeditbox2" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent2"></div>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$('.popupform .dateday').datepicker({
	dateFormat: "dd.mm.yy",
	firstDay: 1,
	<?php if($week_start!=""){?>
	minDate: '<?php echo $week_start;?>',
	<?php } ?>
	<?php if($week_end!=""){?>
	maxDate: '<?php echo $week_end;?>',
	<?php } ?>
	onSelect: function(dateText, inst) {

    }
});
$(".starttimeinput").timepicker({
	timeFormat: 'H:i',
	minTime: "7:00"
});
$(".selectWorker").unbind("click").bind("click", function(){
	var _data = { fwajax: 1, fw_nocss: 1};
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
		data: _data,
		success: function(obj){
			$('#popupeditboxcontent2').html('');
			$('#popupeditboxcontent2').html(obj.html);
			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
			$("#popupeditbox2:not(.opened)").remove();
		}
	});
})
$("form.output-worker-form").validate({
	submitHandler: function(form) {
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
				} else {

					if(data.redirect_url !== undefined)  {
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
</script>
<style>
.dayLine {
	padding: 5px 0px;
}
.dayLine input {
	vertical-align: middle;
}
.dayLine label {
	vertical-align: middle;
	margin-left: 10px;
	margin-right: 15px;
}
.starttime {
	display: none;
	vertical-align: middle;
}
.step2 {
	display: none;
}
.step3 {
	display: none;
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
.worktimePeriod {
	line-height: 29px;
	margin-bottom: 10px;
}
</style>
