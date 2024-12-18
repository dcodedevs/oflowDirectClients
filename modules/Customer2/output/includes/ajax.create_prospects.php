<?php
if(!is_array($_POST['selected'])) $_POST['selected'] = explode(",", $_POST['selected']);

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$b_handle = TRUE;
		if(!isset($_POST['type']) || $_POST['type'] == '')
		{
			$b_handle = FALSE;
			$fw_error_msg[] = $formText_ProspectTypeNotSelected_Output;
		}
		if(!isset($_POST['employee']) || $_POST['employee'] == '')
		{
			$b_handle = FALSE;
			$fw_error_msg[] = $formText_EmployeeNotSelected_Output;
		}
		
		$l_count = 0;
		if($b_handle)
		foreach($_POST['selected'] as $l_customer_id)
		{
			$s_sql = "INSERT INTO prospect (moduleID, createdBy, created, customerId, prospecttypeId, closed, statusAfterClosed, employeeId, contactpersonId, batchId, content_status, onWorklist, statusId)
			VALUES ('".$o_main->db->escape_str($moduleID)."', '".$o_main->db->escape_str($variables->loggID)."', NOW(), '".$o_main->db->escape_str($l_customer_id)."', '".$o_main->db->escape_str($_POST['type'])."', 0, '', '".$o_main->db->escape_str($_POST['employee'])."', '', 0, 0, 0, 0)";
			$o_query = $o_main->db->query($s_sql);
			if($o_query) $l_count++;
		}
		
		echo '<div>'.$formText_ProspectsCreated_Output.': '.$l_count.' '.$formText_of_Output.' '.count($_POST['selected']).'.</div>';
		return;
	}
}

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_prospects";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<?php foreach($_POST['selected'] as $l_customer_id) { ?>
	<input type="hidden" name="selected[]" value="<?php print $l_customer_id;?>">
	<?php } ?>
	<div class="inner">
		<div class="line">
    		<div class="lineTitle"><?php echo $formText_ProspectType_Output; ?></div>
    		<div class="lineInput">
    			<select name="type" required>
				<option value=""><?php echo $formText_Choose_Output;?></option>
				<?php
				$o_query = $o_main->db->query("SELECT * FROM prospecttype WHERE content_status = 0 ORDER BY sortnr");
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $v_row)
				{
					?><option value="<?php echo $v_row['id'];?>"><?php echo $v_row['name'];?></option><?php
				}
				?></select>
    		</div>
    		<div class="clear"></div>
		</div>
		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Employee_Output; ?></div>
    		<div class="lineInput">
    			<select name="employee" required>
				<option value=""><?php echo $formText_Choose_Output;?></option>
				<?php
				$o_query = $o_main->db->query("SELECT * FROM employee WHERE content_status = 0 ORDER BY sortnr");
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $v_row)
				{
					?><option value="<?php echo $v_row['id'];?>"><?php echo $v_row['name'].($v_row['email']!=''?' ('.$v_row['email'].')':'');?></option><?php
				}
				?></select>
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
.popupform input.popupforminput.checkbox {
    width: auto;
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
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
    		fw_loading_start();
			$("#popup-validate-message").html('').hide();
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
						var _msg = '';
						$.each(data.error, function(index, value){
							/*var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);*/
							_msg = _msg + '<div>' + value + '</div>';
						});
						$("#popup-validate-message").html(_msg).show();
						fw_click_instance = fw_changes_made = false;
					} else {
						/*if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
							// fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;
						}*/
						$('#popupeditboxcontent').html('').html(data.html);
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true).show();
				fw_loading_end();
			});
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message").html(message).show();
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		}
	});
});
</script>
