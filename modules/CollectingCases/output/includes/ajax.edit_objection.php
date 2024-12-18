<?php
$collecting_case_id = $_POST['collecting_case_id'];

include_once(__DIR__."/../../../CreditorsOverview/output/includes/fnc_process_open_cases_for_tabs.php");
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['message_from_debitor'] != "") {
			if($_POST['objection_type_id'] > 0) {
				if(isset($_POST['cid']) && $_POST['cid'] > 0)
				{
					$s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
				    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
				    if($o_query && $o_query->num_rows() == 1) {
						$s_sql = "select * from collecting_cases where id = ?";
						$o_query = $o_main->db->query($s_sql, array($collecting_case_id));
						$collecting_case = $o_query ? $o_query->row_array() : array();

						$s_sql = "SELECT creditor.*  FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
						$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
						$creditor = ($o_query ? $o_query->row_array() : array());

						$s_sql = "UPDATE collecting_cases_objection SET
						updated = now(),
						updatedBy= ?,
						collecting_case_id = ?,
						objection_type_id = ?,
						message_from_debitor = ?
						WHERE id = ?";
						$o_main->db->query($s_sql, array($variables->loggID, $collecting_case_id, $_POST['objection_type_id'], $_POST['message_from_debitor'], $_POST['cid']));
					}
				} else {
					$s_sql = "INSERT INTO collecting_cases_objection SET
					id=NULL,
					moduleID = ?,
					created = now(),
					createdBy= ?,
					collecting_case_id = ?,
					objection_type_id = ?,
					message_from_debitor = ?";
					$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $collecting_case_id, $_POST['objection_type_id'], $_POST['message_from_debitor']));
					$_POST['cid'] = $objectionId = $o_main->db->insert_id();

					$s_sql = "select * from collecting_cases where id = ?";
					$o_query = $o_main->db->query($s_sql, array($collecting_case_id));
					$collecting_case = $o_query ? $o_query->row_array() : array();

					$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
					$o_query = $o_main->db->query($s_sql, array($collecting_case['debitor_id']));
					$customer = ($o_query ? $o_query->row_array() : array());

					$s_sql = "SELECT creditor.*  FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
                    $o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
                    $creditor = ($o_query ? $o_query->row_array() : array());

					$s_sql = "SELECT * FROM accountinfo_emailsender_accountconfig";
                    $o_query = $o_main->db->query($s_sql);
                    $accountinfo_emailsender_accountconfig = ($o_query ? $o_query->row_array() : array());
                    if($accountinfo_emailsender_accountconfig['name'] != "" && $accountinfo_emailsender_accountconfig['email']){
                        $emailsToBeNotified = $creditor['emails_for_notification'];
                        $emailsToBeNotified = str_replace(";", ",", $emailsToBeNotified);
                        $invoiceEmails = explode(",", $emailsToBeNotified);

                        $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
                        $v_email_server_config = $v_email_server_config_sql ? $v_email_server_config_sql->row_array() : array();


                        foreach($invoiceEmails as $invoiceEmail){
                            $invoiceEmail = trim($invoiceEmail);
                            if($invoiceEmail != "") {
                                $mail = new PHPMailer;
                                $mail->CharSet  = 'UTF-8';
                                $mail->IsSMTP(true);
                                $mail->isHTML(true);
                                if($v_email_server_config['host'] != "")
                                {
                                    $mail->Host = $v_email_server_config['host'];
                                    if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

                                    if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
                                    {
                                        $mail->SMTPAuth = true;
                                        $mail->Username = $v_email_server_config['username'];
                                        $mail->Password = $v_email_server_config['password'];
                                    }
                                } else {
                                    $mail->Host = "mail.dcode.no";
                                }
                                $s_email_subject = $formText_Objection_output." ".$collecting_case['id'];

                                $s_email_body = $formText_ObjectionWasAddedForCase."<br/>";
                                $s_email_body .= $formText_Case_output.": ".$collecting_case['id']."<br/>";
                                $s_email_body .= $formText_Customer_output.": ".$customer['name']." ".$customer['middlename']." ".$customer['lastname']."<br/>";
                                $s_email_body .= $formText_MessageFromDebitor.": ".$_POST['message_from_debitor']."<br/>";

                                $mail->From     = $accountinfo_emailsender_accountconfig['email'];
                                $mail->FromName = $accountinfo_emailsender_accountconfig['name'];
                                $mail->Subject  = $s_email_subject;
                                $mail->Body     = $s_email_body;
                                $mail->AltBody  = strip_tags($s_email_body);
                                $mail->AddAddress($invoiceEmail, $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']);

                                // $atached_files = json_decode($offer['files_attached_to_email'], true);
                                // foreach($atached_files as $attached_file){
                                //     $attachmentFile = __DIR__."/../../../../".$attached_file[1][0];
                                //     $mail->AddAttachment($attachmentFile);
                                // }

                                $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), 'webpage', 2, NOW(), '', 'webpage', 0, 0, '".$objectionId."', 'collecting_cases_objection', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                                $o_main->db->query($s_sql);
                                $l_emailsend_id = $o_main->db->insert_id();

                                $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                                $o_main->db->query($s_sql);
                                $l_emailsendto_id = $o_main->db->insert_id();

                                if($mail->Send())
                                {

                                } else {
                                    $v_return['error'] = $formText_ErrorSendingEmail_output;

                                    $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = '".json_encode($mail)."' WHERE id = ?";
                                    $o_main->db->query($s_sql, array($l_emailsendto_id));

                                    $mail = new PHPMailer;
                                    $mail->CharSet  = 'UTF-8';
                                    $mail->IsSMTP(true);
                                    $mail->isHTML(true);
                                    if($v_email_server_config['host'] != "")
                                    {
                                        $mail->Host = $v_email_server_config['host'];
                                        if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

                                        if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
                                        {
                                            $mail->SMTPAuth = true;
                                            $mail->Username = $v_email_server_config['username'];
                                            $mail->Password = $v_email_server_config['password'];
                                        }
                                    } else {
                                        $mail->Host = "mail.dcode.no";
                                    }
                                    $mail->From     = "noreply@getynet.com";
                                    $mail->FromName = "Getynet.com";
                                    $mail->Subject  = $formText_NotDelivered_Output.": ".$s_email_subject;
                                    $mail->Body     = $s_email_body;
                                    $mail->AddAddress(trim($v_email_server_config['technical_email']));

                                }
                            }
                        }
                    }

				}
				
				$s_sql = "UPDATE creditor_transactions SET to_be_reordered = 1
				WHERE creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND collectingcase_id = '".$o_main->db->escape_str($collecting_case['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				process_open_cases_for_tabs($creditor['id'], 5);

				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
				return;
			} else {
				$fw_error_msg[] = $formText_ChooseType_output;
			}
		} else {
			$fw_error_msg[] = $formText_FillInMessage_output;
		}
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_cases_objection WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$type_messages = array("", $formText_WantsInvoiceCopy_output,$formText_WantsDefermentOfPayment_output,$formText_WantsInstallmentPayment_output,$formText_HasAnObjectionToTheAmount_output,$formText_HasAnObjectionToTheProductService_output);

$s_sql = "select * from collecting_cases where id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['collecting_case_id']));
$collecting_case = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT creditor.*  FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array())
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_objection";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<input type="hidden" name="collecting_case_id" value="<?php print $_POST['collecting_case_id'];?>">


		<div class="inner">

			<div class="line">
				<div class="lineTitle"><?php echo $formText_ObjectionType_Output; ?></div>
				<div class="lineInput">
					<select name="objection_type_id">
						<option value=""><?php echo $formText_Select_output;?></option>
						<option value="1" <?php if($v_data['objection_type_id'] == 1) echo 'selected';?>><?php echo $type_messages[1];?></option>
						<option value="2" <?php if($v_data['objection_type_id'] == 2) echo 'selected';?>><?php echo $type_messages[2];?></option>
						<option value="3" <?php if($v_data['objection_type_id'] == 3) echo 'selected';?>><?php echo $type_messages[3];?></option>
						<option value="4" <?php if($v_data['objection_type_id'] == 4) echo 'selected';?>><?php echo $type_messages[4];?></option>
						<option value="5" <?php if($v_data['objection_type_id'] == 5) echo 'selected';?>><?php echo $type_messages[5];?></option>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Comment_Output; ?></div>
				<div class="lineInput"><textarea class="popupforminput botspace" name="message_from_debitor" required><?php echo $v_data['message_from_debitor'];?></textarea></div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<?php
				$emailsToBeNotified = $creditor['emails_for_notification'];
				$emailsToBeNotified = str_replace(";", ",", $emailsToBeNotified);
				$invoiceEmails = explode(",", $emailsToBeNotified);
				if(count($invoiceEmails) > 0){
					echo $formText_EmailsWillBeSentTo_output.": ".$emailsToBeNotified;
				}
				?>
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
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(function() {
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
