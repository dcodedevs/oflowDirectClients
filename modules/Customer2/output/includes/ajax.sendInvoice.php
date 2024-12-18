<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once("Exception.php");
require_once("PHPMailer.php");
require_once("SMTP.php");
$invoiceId = $_POST['invoiceId'] ? $_POST['invoiceId'] : 0;

if($invoiceId) {
    $sql = "SELECT * FROM invoice WHERE id = $invoiceId";
    $result = $o_main->db->query($sql, array($invoiceId));
    if($result && $result->num_rows()>0)
    $invoiceData = $result->result();
}
$v_customer_sql = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($invoiceData[0]->customerId));
if($v_customer_sql && $v_customer_sql->num_rows()>0)
$v_customer = $v_customer_sql->result();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($invoiceId) {

            $b_successfully_sent_by_email = false;

            $o_result = $o_main->db->query("SELECT * FROM ownercompany WHERE id = ?", array($invoiceData[0]->ownercompany_id));
            if($o_result && $o_result->num_rows()>0)
            $v_settings = $o_result->result();

            $b_send_by_email = false;
            $emails = $_POST['email'];
            $emails = str_replace(";", ",", $emails);
            $emailsArray = explode(",", $emails);
            $emailsArray = array_map('trim', $emailsArray);

            $newInvoiceNrInDb = $invoiceData[0]->id;
            $b_send_by_email = true;
            $s_email_subject = $v_settings[0]->invoiceSubjectEmail;
            $s_email_body = nl2br($v_settings[0]->invoiceTextEmail);

            $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
            if($v_email_server_config_sql && $v_email_server_config_sql->num_rows()>0)
            $v_email_server_config = $v_email_server_config_sql->result();

            $files_attached = json_decode($invoiceData[0]->files_attached);
            foreach($emailsArray as $invoiceEmail){
                if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
                    $mail = new PHPMailer;
                    $mail->CharSet  = 'UTF-8';
                    $mail->IsSMTP(true);
                    $mail->isHTML(true);
                    $invoiceFile = __DIR__."/../../../../".$invoiceData[0]->invoiceFile;
                    if($v_email_server_config[0]->host != "")
                    {
                        $mail->Host = $v_email_server_config[0]->host;
                        if($v_email_server_config[0]->port != "") $mail->Port = $v_email_server_config[0]->port;

                        if($v_email_server_config[0]->username != "" and $v_email_server_config[0]->password != "")
                        {
                            $mail->SMTPAuth = true;
                            $mail->Username = $v_email_server_config[0]->username;
                            $mail->Password = $v_email_server_config[0]->password;

                        }
                    } else {
                        $mail->Host = "mail.dcode.no";
                    }
                    $mail->From     = $v_settings[0]->invoiceFromEmail;
                    $mail->FromName = "";
                    $mail->Subject  = $s_email_subject;
                    $mail->Body     = $s_email_body;
                    $mail->AddAddress($invoiceEmail);
                    foreach($files_attached as $file_to_attach) {
                        $mail->AddAttachment(__DIR__."/../../../../".$file_to_attach[1][0]);
                    }
                    $mail->AddAttachment($invoiceFile);

                    $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), '".$_COOKIE['username']."', 2, NOW(), '', '".addslashes($v_settings['invoiceFromEmail'])."', 0, 0, '".$newInvoiceNrInDb."', 'invoice', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                    $o_main->db->query($s_sql);
                    $l_emailsend_id = $o_main->db->insert_id();

                    $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                    $o_main->db->query($s_sql);
                    $l_emailsendto_id = $o_main->db->insert_id();

                    if($mail->Send())
                    {
                        $b_successfully_sent_by_email = true;
						$l_invoice_sent++;

                    } else {
                        $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = '".json_encode($mail)."' WHERE id = ?";
                        $o_main->db->query($s_sql, array($l_emailsendto_id));

                        $mail = new PHPMailer;
                        $mail->CharSet  = 'UTF-8';
                        $mail->IsSMTP(true);
                        $mail->isHTML(true);
                        if($v_email_server_config[0]->host != "")
                        {
                            $mail->Host = $v_email_server_config[0]->host;
                            if($v_email_server_config[0]->port != "") $mail->Port = $v_email_server_config[0]->port;

                            if($v_email_server_config[0]->username != "" and $v_email_server_config[0]->password != "")
                            {
                                $mail->SMTPAuth = true;
                                $mail->Username = $v_email_server_config[0]->username;
                                $mail->Password = $v_email_server_config[0]->password;

                            }
                        } else {
                            $mail->Host = "mail.dcode.no";
                        }
                        $mail->From     = "noreply@getynet.com";
                        $mail->FromName = "Getynet.com";
                        $mail->Subject  = $formText_NotDelivered_Output.": ".$s_email_subject;
                        $mail->Body     = $s_email_body;
                        $mail->AddAddress(trim($v_email_server_config[0]->technical_email));
                        $mail->AddAttachment($invoiceFile);
                        foreach($files_attached as $file_to_attach) {
							$mail->AddAttachment(__DIR__."/../../../../".$file_to_attach[1][0]);
						}

                    }
                }
            }
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&company_filter=".$v_settings[0]->id;
            $o_main->db->query("INSERT INTO invoice_send_log SET created = NOW(), invoice_id = '".$o_main->db->escape_str($invoiceId)."', send_type = 2, send_status = '".$o_main->db->escape_str(count($emailsArray) != $l_invoice_sent ? 2 : 1)."', send_emails='".implode(",", $emailsArray)."'");

        }
	}
}

?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendInvoice";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="invoiceId" value="<?php echo $invoiceId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"?>">
		<div class="inner">
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="email" value="<?php echo $v_customer[0]->invoiceEmail; ?>" required>
                </div>
        		<div class="clear"></div>
    		</div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Send_Output; ?>">
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
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload");
                        out_popup.close();
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
    // $('.output-form').on('submit', function(e) {
    //     e.preventDefault();
    //     var data = {};
    //     $(this).serializeArray().forEach(function(item, index) {
    //         data[item.name] = item.value;
    //     });
    //     ajaxCall('editOrder', data, function (json) {
    //         if (json.redirect_url) document.location.href = json.redirect_url;
    //         else out_popup.close();
    //     });
    // });

    function h(e) {
        $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
    }
    $('.autoheight').each(function () {
        h(this);
    }).on('input', function () {
        h(this);
    });
});

</script>
<style>

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
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
