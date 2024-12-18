<?php
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$contactpersonId = intval($_POST['contactpersonId']);
		$customerId = intval($_POST['customerId']);
		if($contactpersonId > 0 && $customerId > 0)
		{
			if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
			$s_sql = "select * from accountinfo";
		    $o_query = $o_main->db->query($s_sql);
		    if($o_query && $o_query->num_rows()>0) {
		        $accountinfo = $o_query->row_array();
		    }
		    $s_sql = "select * from customer_stdmembersystem_basisconfig";
		    $o_query = $o_main->db->query($s_sql);
		    if($o_query && $o_query->num_rows()>0) {
		        $v_membersystem_config = $o_query->row_array();
		    }

			$s_sql = "select * from contactperson where id = ?";
		    $o_query = $o_main->db->query($s_sql, array($contactpersonId));
		    if($o_query && $o_query->num_rows()>0) {
		        $v_row = $o_query->row_array();
		    }

			$l_membersystem_id = $v_row[$v_membersystem_config['content_id_field']];
			$s_receiver_name = $v_row['name'];
			$s_receiver_email = $v_row['email'];

			$o_response = json_decode(APIconnectAccount("membersystemcompanyaccessdelete", $accountinfo['accountname'], $accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$s_receiver_email, "MEMBERSYSTEMID"=>$l_membersystem_id, "ACCESSLEVEL"=>$v_membersystem_config['access_level'])));

			if($o_response->data == "OK") {
				$s_sql = "DELETE FROM contactperson WHERE id = ?";
    			$o_main->db->query($s_sql, array($contactpersonId));
			}
			$fw_return_data = $customerId;
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-homes-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=delete_all";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php print $_POST['cid'];?>">
		<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">

		<div class="inner">
			<div class="line">
				<div class="errorMessage"><?php echo $_POST['message'];?></div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <?php if(!$_POST['hide_access']){ ?>
    			<input type="submit" name="sbmbtn" value="<?php echo $formText_DeleteWithAccess_Output; ?>">
            <?php } ?>
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	var datepickerChangeMade = false;
	$('.monthdatepicker').datepicker({
		changeMonth: true,
		changeYear: true,
		showButtonPanel: true,
		dateFormat: 'mm.yy',
		onClose: function(dateText, inst) {
			if (datepickerChangeMade) {
				$(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
				datepickerChangeMade = false;
			}
		},
		onChangeMonthYear: function(year, month) {
			datepickerChangeMade = true;
		},
		// add custom class so we can style it (hide calendar) without disturbing other datepickers on page
		beforeShow: function(input, inst) {
			$('#ui-datepicker-div').addClass('ui-datepicker-monthonly ');
			var value = $(this).val();
			if(value != ""){
				var tmp = $(this).val().split('.');
				$(this).datepicker('option','defaultDate',new Date(tmp[1],tmp[0]-1,1));
				$(this).val(value);
			} else {
				$(this).datepicker('option','defaultDate', "-1m");
			}
		}
	});


	$("form.output-homes-form").validate({
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
						if(data.redirect_url !== undefined)  {
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
<style>
.popupform .errorMessage {
	font-size: 14px;
}
.ui-datepicker-monthonly .ui-datepicker-calendar {
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
</style>
