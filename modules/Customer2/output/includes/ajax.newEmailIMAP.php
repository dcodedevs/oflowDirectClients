<?php
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();
$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'Customer2', // module id in which this block is used
	  'id' => 'articleimageeuploadpopup',
	  'upload_type'=>'file',
	  'content_table' => 'customer2',
	  'content_field' => 'email_attachments',
	  'content_id' => $cid,
      'content_limit'=>100,
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete',
	)
);
// Save
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

        class Mailer extends PHPMailer {
            /**
             * Save email to a folder (via IMAP)
             *
             * This function will open an IMAP stream using the email
             * credentials previously specified, and will save the email
             * to a specified folder. Parameter is the folder name (ie, Sent)
             * if nothing was specified it will be saved in the inbox.
             *
             * @author David Tkachuk <http://davidrockin.com/>
             */
            public function copyToFolder($folderPath = null) {
                $message = $this->getSentMIMEMessage();
                $path = "INBOX";
                if($folderPath != null){
                    $path = $folderPath;
                }
                // $path = "INBOX" . (isset($folderPath) && !is_null($folderPath) ? ".".$folderPath : ""); // Location to save the email
                $imapStream = imap_open("{" . $this->Host . "/imap/ssl}" . $path , $this->Username, $this->Password);

                imap_append($imapStream, "{" . $this->Host . "/imap/ssl}" . $path, $message);
                imap_close($imapStream);
            }

        }

        $from_email = $_POST['from_email'];
        $to_email = $_POST['to_email'];
        $s_email_subject = $_POST['subject'];
        $s_email_body = $_POST['message'];
        if($from_email != "" && $to_email != "" && $s_email_subject != "" && $s_email_body != ""){
            if (filter_var($to_email, FILTER_VALIDATE_EMAIL)) {
                if($from_email){
                    $s_sql = "SELECT * FROM sys_emailintegration WHERE emailAddress = ? ORDER BY emailName ASC";
                    $o_query = $o_main->db->query($s_sql, array($from_email));
                    $emailInfo = $o_query ? $o_query->row_array() : array();
                    if($emailInfo){
                        $mail = new Mailer;
                        $mail->CharSet  = 'UTF-8';
                        $mail->Encoding = "base64";
                        $mail->IsSMTP(true);
                        $mail->isHTML(true);

                        if($emailInfo['emailServerOut'] != "")
                        {
                            $mail->Host = $emailInfo['emailServerOut'];
                            if($emailInfo['port'] != "") $mail->Port = $emailInfo['port'];

                            if($emailInfo['emailAddress'] != "" and $emailInfo['emailPassword'] != "")
                            {
                                $mail->SMTPAuth = true;
                                $mail->Username = $emailInfo['emailAddress'];
                                $mail->Password = $emailInfo['emailPassword'];
                            }
                        } else {
                            $mail->Host = "mail.dcode.no";
                        }
                        $mail->From     = $emailInfo['emailAddress'];
                        $mail->FromName = $emailInfo['emailName'];
                        $mail->Subject  = $s_email_subject;
                        $mail->Body     = $s_email_body;
                        $mail->AddAddress($to_email);
                        foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
        					$fieldName = $fwaFileuploadConfig['id'];
        					$fwaFileuploadConfig['content_id'] = $_POST['cid'];

                            if(array_key_exists($fieldName."_name",$_POST))
                            {
                                $account_path = ACCOUNT_PATH;
                                foreach($_POST[$fieldName."_name"] as $key => $item)
                                {
                                    $image_name = explode("|",$item);

                					$s_sql = "SELECT * FROM uploads WHERE id = ? AND handle_status = 0";
                                    $o_query = $o_main->db->query($s_sql, array($image_name[1]));
                                    $uploaded_image_data = $o_query ? $o_query->row_array() : array();

                                    $s_original_file = rawurldecode($uploaded_image_data['filepath']);
                                    $mail->addAttachment($account_path . '/'.$s_original_file, '=?UTF-8?B?' . base64_encode(basename($s_original_file)) . '?=');
                                }
                            }
        				}
                        $sql = "";
                        $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, 0, '', '', 0, ?, ?);";
                        $o_main->db->query($s_sql, array($variables->loggID, $emailInfo['emailAddress'], $s_email_subject, $s_email_body));
                        $l_emailsend_id =  $o_main->db->insert_id();

                        $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, '', ?, 1, '', NOW(), 1);";
                        $o_main->db->query($s_sql, array($l_emailsend_id, $to_email));
                        $l_emailsendto_id = $o_main->db->insert_id();

                        if($mail->Send())
                        {
                            $b_successfully_sent_by_email = true;
                            $mail->copyToFolder("SENT ITEMS");
                        } else {

                            $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = ? WHERE id = ?";
                            $o_main->db->query($s_sql, array(json_encode($mail), $l_emailsendto_id));

                            $s_sql = "select * from sys_emailserverconfig order by default_server desc";
                            $o_query = $o_main->db->query($s_sql);
                            if($o_query && $o_query->num_rows()>0){
                                $v_email_server_config = $o_query->row_array();
                            }

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
                            $mail->AddAddress($v_email_server_config['technical_email']);

                        }
                        if($b_successfully_sent_by_email){
                            $fw_return_data = $s_sql;
                            $fw_redirect_url = $_POST['redirect_url'];
                        } else {
                            $fw_error_msg = $formText_ErrorSendinEmail_output;
                            return;
                        }
                    }
                }
            } else {
                $fw_error_msg = $formText_WrongToEmailAddress_output;
                return;
            }
        } else {
            $fw_error_msg = $formText_MissingFields_output;
            return;
        }
        return;
	}
}

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
if($o_query && $o_query->num_rows()>0) {
    $customer = $o_query->row_array();
}

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
$ownercompanies = array();

$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $ownercompanies = $o_query->result_array();
}

$subscriptiontypes = array();

$s_sql = "SELECT * FROM subscriptiontype";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $subscriptiontypes = $o_query->result_array();
}
$emailInfos = array();
$s_sql = "SELECT * FROM sys_emailintegration ORDER BY emailName ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$emailInfos = $o_query->result_array();
}
$s_sql = "SELECT * FROM contactperson WHERE email is not null AND email <> '' AND customerId = ? ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql, array($customer['id']));
$contactPersons = $o_query ? $o_query->result_array() : array();
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=newEmailIMAP";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_FromMail_Output; ?></div>
				<div class="lineInput">
					<select name="from_email" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
                        foreach($emailInfos as $emailInfo) {
            				?>
            				<option value="<?php echo $emailInfo['emailAddress']?>"><?php echo $emailInfo['emailAddress'];?></option>
            				<?php
            			}
                        ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>

            <div class="line ">
				<div class="lineTitle"><?php echo $formText_To_Output; ?></div>
				<div class="lineInput">
					<select name="to_email" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
                        foreach($contactPersons as $contactPerson) {
            				?>
            				<option value="<?php echo $contactPerson['email']?>"><?php echo $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname'];?></option>
            				<?php
            			}
                        ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_Subject_Output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" name="subject" value="" autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_Message_Output; ?></div>
				<div class="lineInput">
                    <textarea name="message" class="popupforminput botspace"></textarea>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_Attachment_Output; ?></div>
				<div class="lineInput">
                    <?php
            		$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
            		include __DIR__ . '/fileupload_popup/output.php';
            		?>
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
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
}
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
    $(".priceAdjustment").change(function(){
		var value = $(this).val();
		if(value == 1){
			$(".priceAdjustmentTypeWrapper").show();
			$(".turnoverBasedRentWrapper").hide();
		} else if(value == 2){
			$(".turnoverBasedRentWrapper").show();
			$(".priceAdjustmentTypeWrapper").show();
		} else {
			$(".turnoverBasedRentWrapper").hide();
			$(".priceAdjustmentTypeWrapper").hide();
		}
		$(".priceAdjustmentType").change();
    })
    $(".priceAdjustmentType").change(function(){
		var priceAdjustmentValue = $(".priceAdjustment").val();
		var value = $(this).val();
		if(value == 1){
			$(".annualPercentageAdjustmentWrapper").show();
			$(".cpiWrapper").hide();
		} else if(value == 2){
			$(".cpiWrapper").show();
			$(".annualPercentageAdjustmentWrapper").hide();
		} else {
			$(".cpiWrapper").hide();
			$(".annualPercentageAdjustmentWrapper").hide();
		}
    })
	$(".priceAdjustment").change();
	$(".priceAdjustmentType").change();

	$(".datepicker").datepicker({
		firstDay: 1,
		beforeShow: function(dateText, inst) {
			$(inst.dpDiv).removeClass('monthcalendar');
		},
		dateFormat: "dd.mm.yy"
	})
})

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
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
