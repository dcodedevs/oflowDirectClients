<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
require_once("Exception.php");
require_once("PHPMailer.php");
require_once("SMTP.php");

$collectingOrderPdfId = $_POST['collectingOrderPdfId'] ? $o_main->db->escape_str($_POST['collectingOrderPdfId']) : 0;

$s_sql = "SELECT * FROM customer_collectingorder_confirmations WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($collectingOrderPdfId));
$collectingOrderPdf = ($o_query ? $o_query->row_array() : array());
if($_POST['output_form_submit2']){
    $sql = "UPDATE customer_collectingorder SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    contactpersonId = '".$_POST['contact_person']."'
    WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($collectingOrderPdf['customer_collectingorder_id']));
}
$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($collectingOrderPdf['customer_collectingorder_id']));
$collectingOrder = ($o_query ? $o_query->row_array() : array());
$invoiceEmail = "";

$s_sql = "select * from contactperson where id = ?";
$o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
$contactPerson = $o_query ? $o_query->row_array() : array();
if($contactPerson) {
    $invoiceEmail = $contactPerson['email'];
}
if($invoiceEmail != ""){

    $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
    $v_email_server_config = $v_email_server_config_sql ? $v_email_server_config_sql->row_array() : array();

    $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($collectingOrder['ownercompanyId']));
    $v_settings = $o_query ? $o_query->row_array() : array();

    $currentUser = json_decode(APIconnectorUser("userdetailsget", $variables->loggID, $variables->sessionID, array('USER_ID'=>$variables->userID)),true);

    if($moduleAccesslevel > 10) {

        if(isset($_POST['output_form_submit'])) {
            function generateRandomString($length = 10) {
                return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
            }

            if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {

                $s_sql = "select * from file_links where content_table = 'customer_collectingorder_confirmations' AND content_id = ?";
                $o_query = $o_main->db->query($s_sql, array($collectingOrderPdf['id']));
                $file_link = $o_query ? $o_query->row_array() : array();

                $key = "";
                if($file_link){
                    $key = $file_link['link_key'];
                } else {
                    do {
                        $key = generateRandomString("40");
                        $s_sql = "select * from file_links where content_table = 'customer_collectingorder_confirmations' AND link_key = ?";
                        $o_query = $o_main->db->query($s_sql, array($key));
                        $key_item = $o_query ? $o_query->row_array() : array();
                    } while(count($key_item) > 0);


                    $s_sql = "INSERT INTO file_links SET content_table = 'customer_collectingorder_confirmations', content_id = ?, link_key = ?, filepath = ?";
                    $o_query = $o_main->db->query($s_sql, array($collectingOrderPdf['id'], $key, $collectingOrderPdf['file']));

                }
                if($_POST['subject'] == ""){
                    $_POST['subject'] = $formText_OrderConfirmation_output;
                }
                $s_email_subject = $_POST['subject'];

                $s_email_body = $formText_Hi_output."<br/><br/>";
                $s_email_body .= nl2br($_POST['text'])."<br/>";
                $s_email_body .= "<a href='".$extradomaindirroot."/modules/Accountinfo/view_file.php?key=".$key."'>".$formText_ClickHereToOpen_output."</a>";
                $s_email_body .= "<br/><br/>";
                $s_email_body .= $formText_BestRegards_output."<br/>";
                $s_email_body .= $currentUser['name']." ".$currentUser['middle_name']." ".$currentUser['last_name']."<br/>";
                if($currentUser['mobile'] != "") {
                    $s_email_body .= $formText_Phone_output.": ".$currentUser['mobile_prefix']." ".$currentUser['mobile']."<br/>";
                }
                $s_email_body .= $v_settings['name'];

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
                $mail->From     = $variables->loggID;
                $mail->FromName = $variables->fullname;
                $mail->Subject  = $s_email_subject;
                $mail->Body     = $s_email_body;
                $mail->AltBody  = strip_tags($s_email_body);
                $mail->AddAddress($invoiceEmail, $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']);

                $atached_files = json_decode($collectingOrder['files_attached_to_invoice'], true);
                foreach($atached_files as $attached_file){
                    $attachmentFile = __DIR__."/../../../../".$attached_file[1][0];
                    $mail->AddAttachment($attachmentFile);
                }
                $s_sql = "SELECT order_confirmation_attached_files.* FROM order_confirmation_attached_files ORDER BY id";
                $o_query = $o_main->db->query($s_sql);
                $offerFiles = ($o_query ? $o_query->row_array() : array());
                $atached_files = json_decode($offerFiles['file'], true);
                $not_attach_uids = array();
                if(count($_POST['do_not_attach']) > 0){
                    foreach($_POST['do_not_attach'] as $uid) {
                        $not_attach_uids[] = $uid;
                    }
                }
                foreach($atached_files as $attached_file){
                    if(!in_array($attached_file[4], $not_attach_uids)){
                        $attachmentFile = __DIR__."/../../../../".$attached_file[1][0];
                        $mail->AddAttachment($attachmentFile);
                    }
                }

                $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), '".$_COOKIE['username']."', 2, NOW(), '', '".addslashes($variables->loggID)."', 0, 0, '".$collectingOrderPdf['id']."', 'customer_collectingorder_confirmations', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                $o_main->db->query($s_sql);
                $l_emailsend_id = $o_main->db->insert_id();

                $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                $o_main->db->query($s_sql);
                $l_emailsendto_id = $o_main->db->insert_id();

                if($mail->Send())
                {
                    $fw_redirect_url = $_POST['redirect_url'];

                } else {
                    $fw_error_msg[-1] = $formText_ErrorSendingEmail_output;

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
                $fw_error_msg[-1] = $formText_InvalidEmail_output;
            }
            return;
        }
    }
    ?>
    <div class="popupform">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendOrderConfirmationEmail";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="collectingOrderPdfId" value="<?php echo $collectingOrderPdfId;?>" id="offerpdf">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
            <div class="defaultForm">
                <div class="inner">
                    <div class="popupformTitle"><?php echo $formText_SendOrderConfirmationEmail_output;?></div>
                    <div class="sendEmail_info_msg"></div>
                    <div class="line ">
                        <div class="lineTitle"><?php echo $formText_Sender_Output; ?></div>
                        <div class="lineInput">
                            <?php
                            echo $variables->loggID;
                            ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line ">
                        <div class="lineTitle"><?php echo $formText_Receiver_Output; ?></div>
                        <div class="lineInput">
                            <?php echo $invoiceEmail;?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line ">
                        <div class="lineTitle"><?php echo $formText_Subject_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="subject" value="<?php echo $formText_OrderConfirmation_output; ?>" required autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div class="line ">
                        <div class="lineTitle"><?php echo $formText_Text_Output; ?></div>
                        <div class="lineInput">
                            <textarea class="popupforminput botspace textChanger" name="text" required autocomplete="off"><?php echo $formText_HereComesOrderConfirmationFromUs_output; ?></textarea>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                    $attachedFiles = json_decode($collectingOrder['files_attached_to_invoice'], true);
                    if(count($attachedFiles) > 0){
                        ?>
                        <div class="line ">
                            <div class="lineTitle"><?php echo $formText_AttachedFiles_Output; ?></div>
                            <div class="lineInput">
                                <?php
                                foreach($attachedFiles as $file){
                                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=customer_collectingorder&field=files_attached_to_email&ID='.$collectingOrder['id'];

                                    ?>
                                        <div>
                                            <a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a>
                                        </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <?php
                    }
                    $s_sql = "SELECT order_confirmation_attached_files.* FROM order_confirmation_attached_files ORDER BY id";
                    $o_query = $o_main->db->query($s_sql, array($collectingOrder['customerId']));
                    $offerFiles = ($o_query ? $o_query->row_array() : array());
                    $attachedFiles = json_decode($offerFiles['file'], true);
                    if(count($attachedFiles) > 0){
                        ?>
                        <div class="line ">
                            <div class="lineTitle"><?php echo $formText_AttachedFilesToAllOrderConfirmations_Output; ?></div>
                            <div class="lineInput">
                                <?php
                                foreach($attachedFiles as $file){
                                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=order_confirmation_attached_files&field=file&ID='.$offerFiles['id'];

                                    ?>
                                        <div>
                                            <input type="checkbox" name="do_not_attach[]" value="<?php echo $file[4]?>" id="label<?php echo $file[4]?>"/><label for="label<?php echo $file[4]?>"><?php echo $formText_DoNotAttachToEmail_output;?></label>
                                            &nbsp;&nbsp;
                                            <a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a>
                                        </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <div class="article_preview">
                    <div class="lineTitle"><?php echo $formText_Preview_output; ?></div>
    				<div class="article_preview_block">
    					<?php
                        echo $formText_Hi_output."<br/><br/>";
                        ?>
                        <div class="preview_text">
                            <?php
                            echo $formText_HereComesOrderConfirmationFromUs_output."<br/>";
                            ?>
                        </div>
                        <a href="#">
                            <?php
                                echo $formText_ClickHereToOpen_output;
                            ?>
                        </a>
                        <br/>
                        <br/>
                        <?php echo $formText_BestRegards_output."<br/>";?>
                        <?php echo $currentUser['name']." ".$currentUser['middle_name']." ".$currentUser['last_name']."<br/>";?>
                        <?php if($currentUser['mobile'] != "") echo $formText_Phone_output.": ".$currentUser['mobile_prefix']." ".$currentUser['mobile']." <br/>";?>
                        <?php echo $v_settings['name']."<br/>";?>
    				</div>
        		</div>
            </div>
            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" class="saveOnly" value="<?php echo $formText_Send_Output; ?>">
            </div>
            <div>
                <?php
                $pdf_link = '../'.$collectingOrderPdf['file'].'?caID='.$_GET['caID'].'&table=customer_collectingorder_confirmations&field=file&ID='.$collectingOrderPdf['id'].'&time='.time();
                ?>
                <a target="_blank" href="<?php echo $pdf_link;?>"><?php echo $formText_PreviewPdf_output;?> <?php echo basename($collectingOrderPdf['file']);?></a>
                <!-- <object data="<?php echo $pdf_link;?>" type="application/pdf" width="100%" height="700">
                    <?php echo $formText_BrowserDoesNotSupportPdfYouCanDownloadItHere_output?>
                </object> -->
            </div>
        </form>
    </div>
    <style>
    .article_preview {
        padding: 10px;
    }
    .article_preview_block {
    	background: #fff;
    	border: 1px solid #cecece;
    	margin-top: 5px;
        padding: 20px 20px;
    }
    </style>
    <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
    function nl2br (str, is_xhtml) {
        if (typeof str === 'undefined' || str === null) {
            return '';
        }
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
    $(".textChanger").on("keyup", function(){
        $(".preview_text").html(nl2br($(this).val()));
    })
    var data = {
        sender: '<?php echo $variables->loggID?>',
        host: '<?php echo $v_email_server_config["host"];?>'
    }
    ajaxCall("check_spf", data, function(obj){
        if(obj.data !== undefined)
        {
            if(obj.data.status == 'FAIL')
            {
                sendEmail_alert(obj.data.message, "warning");
            }
        }
    });
    function sendEmail_alert(msg, type)
    {
        $('.popupform .sendEmail_info_msg').html('<div class="alert alert-' + type + ' alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' + msg + '</div>');
    }

    $("form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            if(!fw_click_instance)
            {
                $('textarea.ckeditor').each(function () {
                    var $textarea = $(this);
                    $textarea.val(CKEDITOR.instances[$textarea.attr('id')].getData());
                });
                var formdata = $(form).serializeArray();
                var data = {};
                $(formdata ).each(function(index, obj){
                    if(data[obj.name] != undefined) {
                        if(Array.isArray(data[obj.name])){
                            data[obj.name].push(obj.value);
                        } else {
                            data[obj.name] = [data[obj.name], obj.value];
                        }
                    } else {
                        data[obj.name] = obj.value;
                    }
                });
                fw_click_instance = true;
                fw_loading_start();
                $(".errorText").hide().html("");
                $.ajax({
                    url: $(form).attr("action"),
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: data,
                    success: function (data) {
                        fw_click_instance = false;
                        fw_loading_end();
                        if(data.data == "confirmation") {
                            $(".popupform .output-form").append("<input type='hidden' name='forceUpdate' value='1'/>");
                            $(".popupform .defaultForm").hide();
                            $(".popupform .confirmationForm").show();
                        } else if(data.data == "deletedOrderline") {
                            out_popup.addClass("close-reload");
                            out_popup2.addClass("deleted").data("order-id", <?php echo $_POST['orderlineid'];?>);
                            out_popup2.close();
                        } else if(data.data == "deletedOrder") {
                            out_popup.addClass("close-reload");
                            out_popup.close();
                        } else {
                            if(data.error !== undefined)
                            {
                                if(data.data !== undefined) {
                                    $("#offerId").val(data.data);
                                }
                                $.each(data.error, function(index, value){
                                    var _type = Array("error");
                                    if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                    if(index == -1){
                                        $("#popup-validate-message").html(value);
                                    } else {
                                        $(".articleTableWrapper .articleRow").eq(index).find(".accountingInfoTable .errorText").html(value).show();
                                    }
                                });
                                $("#popup-validate-message").show();
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
                    }
                }).fail(function() {
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    fw_loading_end();
                    fw_click_instance = false;
                });
            }

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
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform .selectEmployee");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
        }
    });
    </script>
<?php } else {
    $customerId = $collectingOrder['customerId'];
    $s_sql = "SELECT * FROM contactperson WHERE customerId = ? ORDER BY name";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $contactpersons = $o_query ? $o_query->result_array() : array();
    ?>
    <div class="popupform">
        <form class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendOrderConfirmationEmail";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit2" value="1">
            <input type="hidden" name="collectingOrderPdfId" value="<?php echo $collectingOrderPdfId;?>" id="collectingOrderPdfId">
            <input type="hidden" name="customerId" id="customerId" value="<?php print $customerId;?>" required>
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
            <div class="defaultForm">
                <div class="inner">
                    <div class="line ">
                        <div class="lineTitle"><?php echo $formText_ContactPerson_Output; ?></div>
                        <div class="lineInput">
                            <select name="contact_person" class="contactPerson contactPersonSelect">
                                <option value=""><?php echo $formText_Select_output;?></option>
                                <?php foreach ($contactpersons as $contactperson): ?>
                                    <option value="<?php echo $contactperson['id']; ?>" <?php if($contactperson['id'] == $projectData['contactPerson']) echo 'selected';?> data-name="<?php echo $contactperson['name']." ".$contactperson['middlename']." ".$contactperson['lastname'];?>">
                                        <?php echo $contactperson['name']." ".$contactperson['middlename']." ".$contactperson['lastname']." - ".$contactperson['email']; ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="-1" class="createNewOption"><?php echo $formText_CreateNew_output;?></option>
                            </select>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div id="popup-validate-message" style="display:none;"></div>
                    <div class="popupformbtn">
                        <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                        <input type="submit" name="sbmbtn" class="save" value="<?php echo $formText_Save_Output; ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script type="text/javascript">
    $(".contactPersonSelect").change(function(){
        if($(this).val() == "-1") {
            var data = {
                customerId: '<?php echo $customerId;?>',
                from_popup: 1
            };
            ajaxCall('edit_contactperson', data, function(json) {
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(json.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
                $(window).resize();
            });
        } else if($(this).val() != ""){
            $(".cp_block").show();
            $(".cp_block .cp_wrapper").html($(this).find("option:selected").data("name"));
        } else {
            $(".cp_block").hide();
        }
    }).change();
    $("form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            if(!fw_click_instance)
            {
                $('textarea.ckeditor').each(function () {
                    var $textarea = $(this);
                    $textarea.val(CKEDITOR.instances[$textarea.attr('id')].getData());
                });
                var formdata = $(form).serializeArray();
                var data = {};
                $(formdata ).each(function(index, obj){
                    if(data[obj.name] != undefined) {
                        if(Array.isArray(data[obj.name])){
                            data[obj.name].push(obj.value);
                        } else {
                            data[obj.name] = [data[obj.name], obj.value];
                        }
                    } else {
                        data[obj.name] = obj.value;
                    }
                });
                fw_click_instance = true;
                fw_loading_start();
                $(".errorText").hide().html("");
                $.ajax({
                    url: $(form).attr("action"),
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: data,
                    success: function (data) {
                        fw_click_instance = false;
                        fw_loading_end();
                        if(data.error !== undefined)
                        {
                            if(data.data !== undefined) {
                                $("#offerId").val(data.data);
                            }
                            $.each(data.error, function(index, value){
                                var _type = Array("error");
                                if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                if(index == -1){
                                    $("#popup-validate-message").html(value);
                                } else {
                                    $(".articleTableWrapper .articleRow").eq(index).find(".accountingInfoTable .errorText").html(value).show();
                                    $(".offerLinesTable .offerlines").eq(index).find(".errorText").html(value).show();
                                }
                            });
                            $("#popup-validate-message").show();
                            fw_loading_end();
                            fw_click_instance = fw_changes_made = false;
                        } else {
                            $('#popupeditboxcontent').html('');
                            $('#popupeditboxcontent').html(data.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                            $(window).resize();
                        }
                    }
                }).fail(function() {
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    fw_loading_end();
                    fw_click_instance = false;
                });
            }

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
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "seller_people_id") {
                error.insertAfter(".selectWorker");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform .selectEmployee");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
        }
    });
    </script>
    <?php
}?>
