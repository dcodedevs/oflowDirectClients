<?php
$orderId = $_POST['orderId'] ? ($_POST['orderId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
$ordersModuleData = ($o_query->num_rows() > 0 ? $o_query->row_array() : array());
$ordersModuleId = $ordersModuleData['id'];

if($moduleAccesslevel > 10) {


?><div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editOrder";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="orderId" value="<?php echo $orderId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
		<input type="hidden" name="action" value="deleteOrder"/>
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
		<div class="inner">
            <?php echo $formText_DeletingWillDeleteAllOrderlinesAttachedToThisOrder_output;?><br/>
            <?php echo $formText_DoYouWantToProceedWithDeleting_output;?>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Delete_Output; ?>">
		</div>
	</form>
</div>
<style>
.popupform .inner {
	font-size: 16px;
}
</style>
<script type="text/javascript" src="../modules/Customer2/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
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
                        if(data.redirect_url !== undefined)
                        {
                            out_popup.addClass("close-reload");
                            out_popup.close();
                            // out_popup.close();
                            // fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;

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
})
</script>
<?php } ?>