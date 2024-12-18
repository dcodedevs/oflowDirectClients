<?php
$o_query = $o_main->db->get('customer_accountconfig');
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

$contactpersonId = $_POST['contactpersonId'] ? ($_POST['contactpersonId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

// Get contactperson data
$o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
$contactperson_data = $o_query ? $o_query->row_array() : array();

//Check if contact person has gate access
$s_sql = "select * from contactperson_gate_access where contactpersonId = ? AND customerId = ? AND (deleted is null OR deleted = '0000-00-00 00:00:00') order by sortnr";
$o_query = $o_main->db->query($s_sql, array($contactpersonId, $customerId));
$contactperson_gate_access = $o_query ? $o_query->row_array() : array();

function sendSms($contactpersonId, $action){
    // global $o_main;

    // $s_sql = "select * from accountinfo";
    // $o_result = $o_main->db->query($s_sql);
    // $v_accountinfo = $o_result ? $o_result->row_array(): array();

    // $s_sql = "select * from sys_emailserverconfig order by default_server desc";
    // $o_result = $o_main->db->query($s_sql);
    // $v_email_server_config = $o_result ? $o_result->row_array(): array();

    // $s_sql = "select * from sys_smsserviceconfig order by default_config desc";
    // $o_result = $o_main->db->query($s_sql);
    // $v_sms_service_config = $o_result ? $o_result->row_array(): array();

    // $o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
    // $contactperson_data = $o_query ? $o_query->row_array() : array();

    // $s_send_on = date("d-m-Y H:i");
    // //TODO change sms password and add gatePhone
    // $smsPassword = "";

    // if($action == "add") {
    //     $s_sms_message = $smsPassword."_N:".$contactperson_data['mobile'];
    // } else if($action == "delete") {
    //     $s_sms_message = $smsPassword."_D:".$contactperson_data['mobile'];
    // }
    // $s_sql = "INSERT INTO sys_smssend (id, created, createdBy, `type`, send_on, sender, sender_email, content_module_id, content_id, content_table, message) VALUES (NULL, NOW(), ".$variables->loggID.", 1, STR_TO_DATE('".$s_send_on."','%d-%m-%Y %H:%i'), '".addslashes($v_accountinfo['default_email_sender_name'])."', '".addslashes($v_accountinfo['default_email_sender_email_address'])."', '".$contactperson_data['moduleID']."', '".$contactperson_data['id']."', 'contactperson', '".$s_sms_message."');";

    // $o_main->db->query($s_sql);
    // $l_emailsend_id = $o_main->db->insert_id();

    // $v_sms_service_config['gatePhone'] = preg_replace("/[^0-9+]/", "", $v_sms_service_config['gatePhone']);
    // if(substr($v_sms_service_config['gatePhone'], 0, 1) != "+") $v_sms_service_config['gatePhone'] = $v_sms_service_config['prefix'].$v_sms_service_config['gatePhone'];

    // if($action == "add") {
    //     $s_sql = "INSERT INTO sys_smssendto (id, smssend_id, receiver, receiver_mobile, `status`, status_message, response, perform_time, perform_count)
    //         VALUES (NULL, '".$l_smssend_id."', 'Add phone ".addslashes($contactperson_data['mobile'])."', '".addslashes($v_sms_service_config['gatePhone'])."', 0, '', '', '', 0)";
    // } else if($action == "delete") {
    //     $s_sql = "INSERT INTO sys_smssendto (id, smssend_id, receiver, receiver_mobile, `status`, status_message, response, perform_time, perform_count)
    //         VALUES (NULL, '".$l_smssend_id."', 'Delete phone ".addslashes($contactperson_data['mobile'])."', '".addslashes($v_sms_service_config['gatePhone'])."', 0, '', '', '', 0)";
    // }
    // $o_main->db->query($s_sql);
    // $l_smssendto_id = $o_main->db->insert_id();

    // $data = array('User' => $v_sms_service_config['username'], 'Password' => $v_sms_service_config['password'],
    //             'LookupOption' => $v_sms_service_config['lookup_option'], 'MessageType' => $v_sms_service_config['type'],
    //             'Originator' => $v_accountinfo['default_sms_sender_name'], 'RequireAck' => 1, 'AckUrl' => rtrim($s_account_root_url,"/")."/elementsGlobal/smsack.php",
    //             'BatchID' => $l_smssendto_id, 'ChannelID' => 0, 'Msisdn' => $v_sms_service_config['gatePhone'], 'Data' => $s_sms_message);

    // //call api
    // $url = 'http://msgw.linkmobility.com/MessageService.aspx';

    // $ch = curl_init();
    // curl_setopt($ch, CURLOPT_URL, $url.'?'.http_build_query($data));
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // $response = curl_exec($ch);
    // curl_close($ch);

    // if(strpos($response,'NOK')===false)
    // {
    //     $o_main->db->query("update sys_smssendto set status = 1, response = '".$o_main->db->escape_str($response)."', perform_time = NOW(), perform_count = 1 where id = ".$l_smssendto_id." and status = 0");
    //     // return "SMS has been added in sending queue for: ".$contactperson_data['mobile']." (".$contactperson_data['name'].")\n";
    //     return 1;
    // } else {
    //     $o_main->db->query("update sys_smssendto set status = 3, status_message = 'Error occured on sms registration', response = '".$o_main->db->escape_str($response)."', perform_time = NOW(), perform_count = 1 where id = ".$l_smssendto_id." and status = 0");
    //     // return "SMS has not been added in sending queue for: ".$contactperson_data['mobile']." (".$contactperson_data['name'].") (".$response.")\n";
    //     return 0;
    // }
    return 1;
}

// On form submit
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if(isset($_POST['saveMobilePhone'])) {
            $mobile = $_POST['mobile'];
            if($mobile != ""){
                $s_sql = "UPDATE contactperson SET
                updated = now(),
                updatedBy= ?,
                mobile = ?
                WHERE id = ?";
                $queryResult = $o_main->db->query($s_sql, array($variables->loggID, $mobile, $contactpersonId));
                if($queryResult){
                    $return['mobileSaved'] = 1;
                }
            } else {
                $fw_error_msg[] = $formText_MobilePhoneIsMandatory_output;
            }
        } else {
            if(isset($_POST['giveAccess'])) {
                if($contactperson_gate_access) {
                    $fw_error_msg[] = $formText_UserAlreadyHaveAccess_output;
                } else {
                    $sendSms = sendSms($contactpersonId, "add");
                    if($sendSms === 1) {
                        $s_sql = "INSERT INTO contactperson_gate_access SET
                        created = now(),
                        createdBy= ?,
                        contactpersonId = ?,
                        customerId = ?,
                        phone = ?";
                        $queryResult = $o_main->db->query($s_sql, array($variables->loggID, $contactpersonId, $customerId, $contactperson_data['mobile']));
                        if($queryResult){
                            // Redirect
                            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
                        } else {
                            $fw_error_msg[] = $formText_ErrorSavingDataPleaseTryLaterOrContactSupport_output;
                        }
                    } else {
                        $fw_error_msg[] = $formText_ErrorSendingSmsPleaseContactSupport_output;
                    }
                }
            }
            if(isset($_POST['deleteAccess'])) {
                if($contactperson_gate_access) {
                    $sendSms = sendSms($contactpersonId, "delete");
                    if($sendSms === 1) {
                        $s_sql = "UPDATE contactperson_gate_access SET deleted = NOW() WHERE id = ?";
                        $queryResult = $o_main->db->query($s_sql, array($contactperson_gate_access['id']));
                        if($queryResult){
                            // Redirect
                            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
                        } else {
                            $fw_error_msg[] = $formText_ErrorSavingDataPleaseTryLaterOrContactSupport_output;
                        }
                    } else {
                        $fw_error_msg[] = $formText_ErrorSendingSmsPleaseContactSupport_output;
                    }
                } else {
                    $fw_error_msg[] = $formText_UserDoesntHaveAccess_output;
                }
            }
        }

	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_gate";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <div class="inner">
            <?php if($contactperson_gate_access) { ?>
                <div class="line">
                    <?php echo $formText_ContactPersonHaveAccessToGate_output;?>
                </div>
            <?php } else { ?>
        		<?php if($contactperson_data['mobile'] != "") { ?>
        			<div class="line">
        				<div class="lineTitle"><?php echo $formText_MobilePhone_Output; ?></div>
        				<div class="lineInput">
        					<?php echo $contactperson_data['mobile']; ?>
        				</div>
        				<div class="clear"></div>
        			</div>
                <?php } else { ?>
                    <div class="popupformTitle"><?php echo $formText_ContactPersonShouldHavePhoneNumber_output;?></div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_MobilePhone_Output; ?></div>
                        <div class="lineInput">
                            <input class="popupforminput" type="text" value="" name="mobile" required autocomplete="off"/>
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <?php if($contactperson_gate_access) { ?>
                     <input type="submit" name="deleteAccess" id="deleteAccess" value="<?php echo $formText_DeleteAccess_Output; ?>">
            <?php } else { ?>
                <?php if($contactperson_data['mobile'] != "") { ?>
    			     <input type="submit" name="giveAccess" value="<?php echo $formText_GiveAccess_Output; ?>">
                <?php } else { ?>
                     <input type="submit" name="saveMobilePhone" value="<?php echo $formText_Save_Output; ?>">
                <?php } ?>
            <?php } ?>
		</div>
	</form>
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
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
					fw_loading_end();
                    if(data.error !== undefined){
                        $.each(data.error, function(index, value){
                            $("#popup-validate-message").append("<div>"+value+"</div>").show();
                        });
                    } else if (data.mobileSaved) {
                        fw_click_instance = true;
                        fw_loading_start();
                        var data2 = {
                            contactpersonId: '<?php echo $contactpersonId; ?>',
                            customerId: '<?php echo $customerId; ?>'
                        };
                        ajaxCall('edit_contactperson_gate', data2, function(json) {
                             $('#popupeditboxcontent').html('').html(json.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                            fw_click_instance = false;
                            fw_loading_end();
                        });
		                // $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
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

    $("#deleteAccess").unbind("click").bind("click", function(e){
        e.preventDefault();

        fw_click_instance = true;
        var $_this = $(this);
        bootbox.confirm({
            message:"<?php echo $formText_DeleteGateAccess_Output;?>",
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result) {
                    $_this.unbind("click").click();
                }
                fw_click_instance = false;
            }
        }).css({"z-index": "10000"})
    })

});

</script>
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
