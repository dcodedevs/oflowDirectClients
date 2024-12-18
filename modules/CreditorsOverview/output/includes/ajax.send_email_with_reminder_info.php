<?php 
$creditor_id = $_POST['creditor_id'];
require("fnc_send_email_with_reminder_info.php");
if($_POST['output_form_submit']) {
    if($_POST['email'] != "" && $_POST['creditor_id'] > 0){
        $s_sql = "SELECT * FROM creditor WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['creditor_id']));
        $creditor = $o_query ? $o_query->row_array() : array();

        send_email_with_reminder_info($creditor, array($_POST['email']), true);
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id;
        
    } else {        
        $fw_error_msg = array($formText_MissingEmail_output);
    }
}
?>
<div class="popupform popupform-<?php echo $creditor_id;?>">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_email_with_reminder_info";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="creditor_id" value="<?php echo $creditor_id;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$creditor_id; ?>">
		<div class="inner">
            <div class="line">
				<div class="lineTitle"><?php echo $formText_EmailToSendTo_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace" autocomplete="off" name="email" value="" required/>
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
<script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $(".popupform-<?php echo $creditor_id;?> form.output-form").validate({
            ignore: [],
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
                            out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                            out_popup.close();
                        } else {
                            $.each(data.error, function(index, value){
                                $("#popup-validate-message").append("<div>"+value+"</div>").show();
    						});
                        }
                    }
                }).fail(function() {
                    $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('.popupform-<?php echo $creditor_id;?> #popupeditboxcontent').height());
                    fw_loading_end();
                });
            },
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                    ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                    : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                    $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").html(message);
                    $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $creditor_id;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
                } else {
                    $(".popupform-<?php echo $creditor_id;?> #popup-validate-message").hide();
                }
                setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "creditor_id") {
                    error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCreditor");
                }
                if(element.attr("name") == "customer_id") {
                    error.insertAfter(".popupform-<?php echo $creditor_id;?> .selectCustomer");
                }
            },
            messages: {
                creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
                customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
            }
        });
    })
</script>