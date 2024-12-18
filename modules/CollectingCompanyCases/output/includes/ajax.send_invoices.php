<?php
$cid = $_POST['case_id'];

$sql = "SELECT * FROM collecting_company_cases WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM customer WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($caseData['debitor_id']));
$debitor = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT * FROM collecting_system_settings";
$o_query = $o_main->db->query($s_sql, array($case_id));
$collecting_system_settings = $o_query ? $o_query->row_array() : array();

if($_POST['output_form_submit']) {
	if($_POST['email'] != "" && $_POST['body']) {
		$companyPhone = $creditor['companyphone'];
		$companyEmail = $creditor['companyEmail'];
		if($creditor['use_local_email_phone_for_reminder']) {
			$companyPhone = $creditor['local_phone'];
			$companyEmail = $creditor['local_email'];
		}

		$s_email_subject = $formText_Invoices_output." ".$creditor['companyname'];
		$s_email_body = $_POST['body'];
		$s_sql = "select * from sys_emailserverconfig order by default_server desc";
		$o_query = $o_main->db->query($s_sql);
		$v_email_server_config = $o_query ? $o_query->row_array() : array();

		$invoiceEmail_string = str_replace(",",";",preg_replace('/\xc2\xa0/', '', trim($_POST['email'])));
		$invoiceEmails = explode(";", $invoiceEmail_string);
			// Trim U+00A0 (0xc2 0xa0) NO-BREAK SPACE
			//$invoiceEmail = trim($invoiceEmail,chr(0xC2).chr(0xA0));
			// Trim rest spaces and new lines
			//$invoiceEmail = trim($invoiceEmail);
		if(count($invoiceEmails) > 0) {
			$s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text, batch_id) VALUES (NULL, NOW(), ?, 2, NOW(), '', ?, 0, 0, ?, 'collecting_company_cases', '', 0, ?, ?, ?);";
			$o_main->db->query($s_sql, array($creditor['sender_name'], $creditor['sender_email'], $caseData['id'], $s_email_subject, $s_email_body.json_encode($invoiceEmails), $batch_id));
			$l_emailsend_id = $o_main->db->insert_id();

			$mail = new PHPMailer;
			$mail->CharSet	= 'UTF-8';
			$mail->IsSMTP(true);
			$mail->isHTML(true);
			if($v_email_server_config['host'] != "")
			{
				$mail->Host	= $v_email_server_config['host'];
				if($v_email_server_config['port'] != "") $mail->Port = $v_email_server_config['port'];

				if($v_email_server_config['username'] != "" and $v_email_server_config['password'] != "")
				{
					$mail->SMTPAuth	= true;
					$mail->Username	= $v_email_server_config['username'];
					$mail->Password	= $v_email_server_config['password'];

				}
			} else {
				$mail->Host = "mail.dcode.no";
			}
			if($companyEmail != "") {
				$mail->addReplyTo($companyEmail, $creditor['companyname']);
			}
			$mail->From		= $collecting_system_settings['reminder_sender_email'];
			$mail->FromName	= $creditor['companyname'];
			$mail->Subject	= $s_email_subject;
			$mail->Body		= $s_email_body;
			$emailAdded = false;
			foreach($invoiceEmails as $invoiceEmail){
				if(filter_var(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)), FILTER_VALIDATE_EMAIL))
				{
					$emailAdded = true;
					$mail->AddAddress(preg_replace('/\xc2\xa0/', '', trim($invoiceEmail)));
				}
			}
			require_once __DIR__ . '/../../../Integration24SevenOffice/internal_api/load.php';
			$v_config = array(
				'ownercompany_id' => 1,
				'identityId' => $creditor['entity_id'],
				'creditorId' => $creditor['id'],
				'o_main' => $o_main
			);
			$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && 0 < $o_query->num_rows())
			{
				$v_int_session = $o_query->row_array();
				$v_config['session_id'] = $v_int_session['session_id'];
			}

			$api = new Integration24SevenOffice($v_config);
			if($api->error == "") {
				$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql);
				$connected_transactions = ($o_query ? $o_query->result_array() : array());
				foreach($connected_transactions as $connected_transaction){
					$data = array("invoice_id"=>$connected_transaction['invoice_nr']);
					$fileText = $api->get_invoice_pdf($data);
					$mail->AddStringAttachment($fileText, "invoice_".$connected_transaction['invoice_nr'].".pdf");
				}
			} else {
				$fw_error_msg[] = ' Error connecting to integration';
			}

			if($emailAdded){
				$s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, ?, ?, ?, 1, '', NOW(), 1);";
				$o_main->db->query($s_sql, array($l_emailsend_id, $creditor['companyname'], preg_replace('/\xc2\xa0/', '', trim($invoiceEmail))));
				$l_emailsendto_id = $o_main->db->insert_id();

				if($mail->Send())
				{
					$emails_sent++;
					$fw_redirect_url = $_POST['redirect_url'];
				} else {
					$s_sql = "UPDATE sys_emailsendto SET status = 2, status_message ='".$o_main->db->escape_str($mail->ErrorInfo)."' WHERE id = '".$o_main->db->escape_str($l_emailsendto_id)."'";
					$o_query = $o_main->db->query($s_sql);
					$fw_error_msg[] = "error sending email";
				}
			} else {
				$fw_error_msg[] = "invalid email";
			}
		}
	}
	return;
}
?>
<div class="popupform popupform-<?php echo $eventId;?>">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php if(isset($formActionUrl)) { echo $formActionUrl; } else { print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invoices"; }?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="languageID" value="<?php echo $variables->languageID?>">
        <input type="hidden" name="case_id" value="<?php echo $_POST['case_id'];?>">
        <input type="hidden" name="redirect_url" value="<?php if(isset($formRedirectUrl)) { echo $formRedirectUrl; } else { echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$eventId; } ?>">
        <div class="inner">
            <div class="popupformTitle"><?php echo $formText_SendInvoices_output; ?></div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
				<div class="lineInput">
					<input type="text" name="email" class="popupforminput botspace" autocomplete="off" required/>
				</div>
				<div class="clear"></div>
			</div>
			<?php
			?>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Body_Output; ?></div>
				<div class="lineInput">
					<textarea name="body" id="texteditor" class="ckeditor popupforminput botspace" autocomplete="off" required></textarea>
				</div>
				<div class="clear"></div>
			</div>
			<br/>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Attachment_Output; ?></div>
				<div class="lineInput">
					<?php
					$s_sql = "SELECT * FROM creditor_transactions WHERE collecting_company_case_id = '".$o_main->db->escape_str($caseData['id'])."' AND creditor_id = '".$o_main->db->escape_str($caseData['creditor_id'])."' ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql);
					$connected_transactions = ($o_query ? $o_query->result_array() : array());
					foreach($connected_transactions as $connected_transaction) {
						?>
						<div>
						<?php echo $formText_Invoice_output." <span class='download_invoice' data-id='".$connected_transaction['id']."'>".$connected_transaction['invoice_nr']."</span>"; ?>

						</div><?php
					}
					?>
				</div>
				<div class="clear"></div>
			</div>
			<div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_SendEmail_Output; ?>">
            </div>
        </div>
    </form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<style>
.add_comment {
	cursor: pointer;
	color: #2266ff;
}
</style>
<script type="text/javascript">
function redrawTexteditor(){

    $('textarea.ckeditor').each(function () {

        var $textarea = $(this);
        var ckeditorId = $textarea.attr('id');
        CKEDITOR.replace( ckeditorId, {

            toolbar: [

				//{ name: 'document', items: [ 'Source', '-', 'Save', 'NewPage', 'ExportPdf', 'Preview', 'Print', '-', 'Templates' ] },

				{ name: 'basicstyles', items: ['Bold','Italic','Underline','RemoveFormat'] },

        		{ name: 'paragraph', items: ['NumberedList', 'BulletedList'] },

				{ name: 'clipboard', items: [ 'PasteText' ] },

        		{ name: 'insert', items: ['Image', 'Youtube'] },

				{ name: 'links', items: ['Link', 'Unlink'] },

        		{ name: 'styles', items: ['FontSize'] },

            ],

			enterMode : CKEDITOR.ENTER_BR,

            height: 300,

            contentsCss: ["body {font-size: 16px; font-family: 'Roboto', sans-serif;}"]

        });



        var ckeditor_ins = CKEDITOR.instances[$textarea.attr('id')];

        ckeditor_ins.on('resize',function(reEvent){

            $(window).resize();

        });

        ckeditor_ins.on('instanceReady', function() {

            setTimeout(function(){

                $(window).resize();

            }, 100);

        });

        ckeditor_ins.on('change', function() {

            $(window).resize();

        });

    });

}
redrawTexteditor();
$("form.output-form").validate({
    submitHandler: function(form) {
        fw_loading_start();
		$('textarea.ckeditor').each(function () {
			var $textarea = $(this);
			$textarea.val(CKEDITOR.instances[$textarea.attr('id')].getData());
		});

        var formdata = $(form).serializeArray();
        var data = {};
        $(formdata).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        $("#popup-validate-message").hide();

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
                    $.each(data.error, function(index, value){
                        var _type = Array("error");
                        if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                        $("#popup-validate-message").append(value);
                    });
                    $("#popup-validate-message").show();
                    fw_loading_end();
                    fw_click_instance = fw_changes_made = false;
                } else  if(data.redirect_url !== undefined)
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

$(".download_invoice").off("click").on("click", function(e){
	e.preventDefault();
	var invoice_nr = $(this).data("id");
	var data = {
		transaction_id: $(this).data("id"),
		creditor_id: '<?php echo $creditor['id']?>'
	};
	ajaxCall('download_invoice', data, function(json) {
		if(json.error == undefined){
			var link = document.createElement("a");
			  document.body.appendChild(link);
			  link.setAttribute("type", "hidden");
			  link.href = "data:text/plain;base64," + json.data;
			  link.download = "invoice_"+invoice_nr+".pdf";
			  link.click();
			  document.body.removeChild(link);
		} else {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.error);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		}
	});
})
</script>
