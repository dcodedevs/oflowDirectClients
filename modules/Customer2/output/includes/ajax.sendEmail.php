<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once('Exception.php');
require_once('PHPMailer.php');
require_once('SMTP.php');

$templateId = $_POST['cid'] ? ($_POST['cid']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

if($moduleAccesslevel > 10) {
    if(isset($_POST['output_form_submit'])) {
        $contentId = $customerId;
        $contentModuleId = $moduleID;
        if ($templateId) {
            $s_sql = "UPDATE emailtemplate_basic SET
            updated = now(),
            updatedBy= ?,
            subject= ?,
            topText= ?,
            receiverEmail= ?,
            contentModuleId= ?,
            contentId= ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['subject'], $_POST['text'], $_POST['receiver'], $contentModuleId, $contentId, $templateId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        } else {
            $s_sql = "INSERT INTO emailtemplate_basic SET
            created = now(),
            createdBy= ?,
            subject= ?,
            topText= ?,
            receiverEmail= ?,
            contentModuleId= ?,
            contentId= ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['subject'], $_POST['text'], $_POST['receiver'], $contentModuleId, $contentId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
        }
    }
}
if ($_POST['output_delete'] && $moduleAccesslevel > 110) {
    $sql = "DELETE FROM emailtemplate_basic WHERE id = ?";
    $o_main->db->query($sql, array($_GET['cid']));
}
if ($_POST['send_message'] && $moduleAccesslevel > 110) {
    $templateId = intval($_GET['cid']);

    $s_sql = "SELECT * FROM emailtemplate_basic WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($templateId));
    if($o_query && $o_query->num_rows()>0){
        $ordersData = $o_query->row_array();
    }


    $b_send_by_email = true;
    $s_email_subject = $ordersData['subject'];
    $s_email_body = nl2br($ordersData['topText']);

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
    $mail->From     = $ordersData['createdBy'];
    $mail->FromName = "";
    $mail->Subject  = $s_email_subject;
    $mail->Body     = $s_email_body;
    $mail->AddAddress($ordersData['receiverEmail']);

    $sql = "";
    $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'emailtemplate_basic', '', 0, ?, ?);";
    $o_main->db->query($s_sql, array($variables->loggID, $ordersData['createdBy'], $templateId, $s_email_subject, $s_email_body));
    $l_emailsend_id =  $o_main->db->insert_id();

    $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, '', ?, 1, '', NOW(), 1);";
    $o_main->db->query($s_sql, array($l_emailsend_id, $ordersData["receiverEmail"]));
    $l_emailsendto_id = $o_main->db->insert_id();

    if($mail->Send())
    {
        $b_successfully_sent_by_email = true;

    } else {
        $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = ? WHERE id = ?";
        $o_main->db->query($s_sql, array(json_encode($mail), $l_emailsendto_id));

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

}
if($templateId) {
    $s_sql = "SELECT * FROM emailtemplate_basic WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($templateId));
    if($o_query && $o_query->num_rows()>0){
        $ordersData = $o_query->row_array();
    }
}
?>

<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendEmail";?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="cid" value="<?php echo $templateId;?>">
        <input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
        <div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Template_Output; ?></div>
                <div class="lineInput">
                    <select name="template" class="templateChooser">
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        $templates = array();
                        $s_sql = "SELECT * FROM emailtemplate_basic WHERE contentTemplate = 1";
                        $o_query = $o_main->db->query($s_sql);
                        if($o_query && $o_query->num_rows()>0){
                            $templates = $o_query->result_array();
                        }
                        foreach($templates as $template){
                            ?>
                            <option value="<?php echo $template['id']?>" data-subject="<?php echo $template['subject'];?>" data-text="<?php echo ($template['topText']);?>"><?php echo $template['subject'];?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            <div class="clear"></div></div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Receiver_Output; ?></div>
                <div class="lineInput">
                    <select name="receiver" required="">
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php
                        $receivers = array();
                        $s_sql = "SELECT * FROM contactperson WHERE customerId = ?";
                        $o_query = $o_main->db->query($s_sql, array($customerId));
                        if($o_query && $o_query->num_rows()>0){
                            $receivers = $o_query->result_array();
                        }
                        foreach($receivers as $receiver){
                            if($receiver['email'] != ""){
                                ?>
                                <option value="<?php echo $receiver['email']?>" <?php if($receiver['email'] == $ordersData['receiverEmail']) echo 'selected';?>><?php echo $receiver['name']." (".$receiver['email'].")";?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Subject_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace subjectInput" name="subject" value="<?php echo $ordersData['subject']; ?>" required autocomplete="off">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Text_Output; ?></div>
                <div class="lineInput">
                    <textarea class="popupforminput botspace autoheight textInput"  name="text"><?php echo $ordersData['topText']; ?></textarea>
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

$(document).ready(function() {
    $("form.output-form").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                   if(data.error !== undefined){
                        $("#popup-validate-message").html(data.error);
                        $("#popup-validate-message").show();
                    } else {
                        if(data.redirect_url !== undefined)
                        {
                            out_popup.addClass("close-reload").data("redirect", data.redirect_url);
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

    $('.datefield').datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });
    $(".templateChooser").change(function(){
        var option = $(this).find("option:selected");
        var subject = option.data("subject");
        var topText = option.data("text");
        $(".popupform .subjectInput").val(subject);
        $(".popupform .textInput").val(topText);

    })
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
.popupform .line .lineInput select {
    max-width: 100%;
}
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
