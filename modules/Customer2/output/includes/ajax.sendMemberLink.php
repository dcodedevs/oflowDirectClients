<?php
use PHPMailer\PHPMailer\PHPMailer;
require_once("Exception.php");
require_once("PHPMailer.php");
require_once("SMTP.php");

$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// Save
$s_sql = "select * from customer where id = ?";
$o_query = $o_main->db->query($s_sql, array($customerId));
$customer = $o_query ? $o_query->row_array() : array();

$s_sql = "select * from accountinfo_emailsender_accountconfig";
$o_query = $o_main->db->query($s_sql);
$accountinfo_emailsender_accountconfig = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        $main_contacts = array();

        if($_POST['send_many']){
            foreach($_POST['customers'] as $customerIdInArray){
                $s_sql = "select * from customer where id = ?";
                $o_query = $o_main->db->query($s_sql, array($customerIdInArray));
                $customerInfo = $o_query ? $o_query->row_array() : array();
                $key = $customerInfo['member_profile_link_code'];
                if($key == "") {
                    do {
                        $key = generateRandomString("40");
                        $s_sql = "select * from customer where member_profile_link_code = ?";
                        $o_query = $o_main->db->query($s_sql, array($key));
                        $key_item = $o_query ? $o_query->row_array() : array();
                    } while(count($key_item) > 0);

                    $s_sql = "UPDATE customer SET
                    updated = now(),
                    updatedBy= ?,
                    member_profile_link_code = ?
                    WHERE id = ?";
                    $o_main->db->query($s_sql, array($variables->loggID, $key, $customerIdInArray));
                }
                $getComp = $o_main->db->query("SELECT contactperson.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, c.member_profile_link_code as keyItem  FROM contactperson JOIN customer c ON c.id = contactperson.customerId WHERE contactperson.customerId = ? AND contactperson.mainContact = 1", array($customerIdInArray));
                $main_contact = $getComp ? $getComp->row_array() : array();
                if($main_contact){
                    $main_contacts[] = $main_contact;
                }
            }
        }
        if($_POST['send_link'] && $customerId > 0){
            $key = $customer['member_profile_link_code'];
            if($key == "") {
                do {
                    $key = generateRandomString("40");
                    $s_sql = "select * from customer where member_profile_link_code = ?";
                    $o_query = $o_main->db->query($s_sql, array($key));
                    $key_item = $o_query ? $o_query->row_array() : array();
                } while(count($key_item) > 0);

                $s_sql = "UPDATE customer SET
                updated = now(),
                updatedBy= ?,
                member_profile_link_code = ?
                WHERE id = ?";
                $o_main->db->query($s_sql, array($variables->loggID, $key, $customerId));
            }
            $getComp = $o_main->db->query("SELECT contactperson.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, c.member_profile_link_code as keyItem FROM contactperson JOIN customer c ON c.id = contactperson.customerId WHERE contactperson.customerId = ? AND contactperson.mainContact = 1", array($customer['id']));
            $main_contact = $getComp ? $getComp->row_array() : array();
            $main_contacts[] = $main_contact;
        }

		$s_logo = '';
		$v_customer_member_link_settings = array();
		$s_sql = "SELECT * FROM customer_member_link_settings WHERE id = 1";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0) {
			$v_customer_member_link_settings = $o_query->row_array();
			$v_logo = json_decode($v_customer_member_link_settings['logo'], TRUE);
			if(isset($v_logo[0][1][0]) && is_file(BASEPATH.$v_logo[0][1][0]))
			{
				$s_logo = $v_logo[0][1][0];
			}
		}

        foreach($main_contacts as $main_contact) {
			$s_receiver_name = preg_replace('/\s+/', ' ', $main_contact['name'].' '.$main_contact['middlename'].' '.$main_contact['lastname']);
            $invoiceEmail = $main_contact['email'];
            if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
                $key = $main_contact['keyItem'];
                if($key != ""){
                    var_dump($key);
                    $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
                    $v_email_server_config = $v_email_server_config_sql ? $v_email_server_config_sql->row_array() : array();
                    $s_email_subject = $formText_ProfileEdit_output;
                    $s_email_body = $formText_Hello_output.('' != $s_receiver_name ? ' '.$s_receiver_name : '')."<br/><br/>"
    					.('' != $v_customer_member_link_settings['top_text'] ? nl2br($v_customer_member_link_settings['top_text'])."<br/><br/>" : '')
    					.$formText_YouCanUseFollowingLinkToEditYourProfile_output." ".$main_contact['customerName']
    					." <a href='".$extradomaindirroot."/modules/Customer2/output/memberProfileLink.php?key=".$key."&email=".$invoiceEmail."'>".$formText_EditCompanyProfile_output."</a><br/><br/>"
    					.$formText_IfYouAreNotTheRightPersonPleaseForwardThisEmailToThePersonInTheCompanyWhoCanEditTheirProfile_Output.".<br/><br/>"
    					.('' != $v_customer_member_link_settings['bottom_text'] ? nl2br($v_customer_member_link_settings['bottom_text'])."<br/><br/>" : '')
    					."<br/><b>".$accountinfo_emailsender_accountconfig['name']."</b><br/><br/>"
    					.('' != $s_logo ? '<img src="cid:logo">' : '');

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
                    $mail->SetFrom($accountinfo_emailsender_accountconfig['email'], ($accountinfo_emailsender_accountconfig['name']));
                    $mail->Subject  = $s_email_subject;
                    $mail->Body     = $s_email_body;
                    $mail->AddAddress($invoiceEmail, ($s_receiver_name));
    				if('' != $s_logo)
    				{
    					$mail->AddEmbeddedImage(BASEPATH.$s_logo, 'logo');
    				}

                    // $atached_files = json_decode($offer['files_attached_to_email'], true);
                    // foreach($atached_files as $attached_file){
                    //     $attachmentFile = __DIR__."/../../../../".$attached_file[1][0];
                    //     $mail->AddAttachment($attachmentFile);
                    // }


                    $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), '".$_COOKIE['username']."', 2, NOW(), '".addslashes($accountinfo_emailsender_accountconfig['name'])."', '".addslashes($accountinfo_emailsender_accountconfig['email'])."', 0, 0, '".$customer['id']."', 'customer_member_link', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                    $o_main->db->query($s_sql);
                    $l_emailsend_id = $o_main->db->insert_id();

                    $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '".addslashes($s_receiver_name)."', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                    $o_main->db->query($s_sql);
                    $l_emailsendto_id = $o_main->db->insert_id();

                    if($mail->Send())
                    {
                        $fw_redirect_url = $_POST['redirect_url'];

                    } else {
                        $fw_error_msg = $formText_ErrorSendingEmail_output;

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
                        $mail->AddAttachment($invoiceFile);
                        foreach($files_attached as $file_to_attach) {
                            $mail->AddAttachment(__DIR__."/../../../../".$file_to_attach[1][0]);
                        }

                    }
                } else {
                    $fw_error_msg = $formText_KeyNotGeneratedContactSupport_output;
                }
            } else {
                $fw_error_msg = $formText_InvalidEmail_output;
            }
        }
        return;
    }
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

$s_sql = "SELECT * FROM contactperson WHERE customerId = ?";
$o_query = $o_main->db->query($s_sql, array($customer['id']));
$contact_persons = $o_query ? $o_query->result_array() : array();
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendMemberLink";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="send_many" value="1">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=member_list&cid=".$customerId; ?>">
		<div class="inner">
            <div class="popupformTitle">
                <?php
                    echo $formText_SendLinkToMany_output;
                ?>
            </div>

            <table class="table">
                <tr>
                    <th width="40"></th>
                    <th><?php echo $formText_Member_output;?></th>
                    <th width="200"><?php echo $formText_MainContact_output;?></th>
                    <th width="150"><?php echo $formText_LinkLastOpen_output;?></th>
                    <th width="150"><?php echo $formText_LinkLastSent_output;?></th>
                </tr>
                <?php
                $getComp = $o_main->db->query("SELECT customer.* FROM customer
                LEFT JOIN
                    (SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
                        WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
                    ON subscriptionmulti.customerId = customer.id
                WHERE customer.content_status <> '2'
                AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
                AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00')
                AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00'))
                ORDER BY name");
                $members = $getComp ? $getComp->result_array() : array();
                $membersShown = 0;
                foreach($members as $member) {
                    $getComp = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ?", array($member['id']));
                    $contacts = $getComp ? $getComp->result_array() : array();
                    $main_contact = array();
                    $contactNumber = 0;
                    foreach($contacts as $contact) {
                        if($contact['mainContact']) {
                            $main_contact = $contact;
                        } else {
                            $contactNumber++;
                        }
                    }
                    if($main_contact){
                        $membersShown++;
                        $getComp = $o_main->db->query("SELECT * FROM sys_emailsend WHERE content_table = 'customer_member_link' AND content_id = ? ORDER BY send_on DESC", array($member['id']));
                        $emails = $getComp ? $getComp->result_array() : array();

                        $linkLastSent = "";
                        foreach($emails as $email) {
                            if($linkLastSent == ""){
                                $linkLastSent = $email['send_on'];
                            }
                        }

                    ?>
                        <tr>
                            <td><input class="customer_select" type="checkbox" name="customers[]" value="<?php echo $member['id'];?>"/></td>
                            <td><?php echo $member['name']." ".$member['middlename']." ".$member['lastname'];?></td>
                            <td>
                                <?php
                                echo $main_contact['name']." ".$main_contact['middle_name']." ".$main_contact['last_name'];
                                ?>
                            </td>
                            <td><?php if($linkLastOpen != "") echo date("d.m.Y", strtotime($linkLastOpen));?></td>
                            <td><?php if($linkLastSent != ""){
                                echo date("d.m.Y", strtotime($linkLastSent));
                            }?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </table>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input class="sendToBtn" type="submit" name="sbmbtn" value="<?php echo $formText_SendTo_Output; ?> ">
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

    $(".propertyOption").change(function(){
		var value = $(this).val();
		if(value == 1){
			$(".propertyOptionTextWrapper").show();
		} else if(value == 0){
			$(".propertyOptionTextWrapper").hide();
		} else {

		}
    })

	$(".datepicker").datepicker({
		firstDay: 1,
		beforeShow: function(dateText, inst) {
			$(inst.dpDiv).removeClass('monthcalendar');
		},
		dateFormat: "dd.mm.yy"
	})
    $(".customer_select").off("click").on("click", function(){
        var selectedCount = $(".customer_select:checked").length;
        var totalCount = "<?php echo $membersShown;?>";
        $(".sendToBtn").val("<?php echo $formText_SendTo_output?> "+selectedCount+" <?php echo $formText_Of_output;?> "+totalCount);
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
