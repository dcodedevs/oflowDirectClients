<?php
$integration = 'IntegrationXledger';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }
}

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        $file = json_decode($exportData['file']);
        $fileName = $file[0][0];
        $filePath = realpath(__DIR__ . '/../../../../' . $file[0][1][0]);

        $api->upload_file('GL02b', false, $fileName, $filePath);

        $o_main->db->where('id', $exportId);
        $o_main->db->update('invoice_export_history', array('sentTime' => date('Y-m-d H:i:s')));

        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export_history&inc_obj=list";
	}
}
?>
<h1 class="popupformTitle"><?php echo $formText_SendExportToXledger_output; ?></h1>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export_history&inc_obj=ajax&inc_act=sendExport";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="exportId" value="<?php echo $exportId;?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_File_Output; ?></div>
                <div class="lineInput">
                    <?php
                    $file = json_decode($exportData['file']);
                    $fileUrl = $extradomaindirroot.'/../'.$file[0][1][0].'?caID='.$_GET['caID'].'&table=invoice_export_history&field=file&ID='.$file[0][4];
                    ?>

                    <a href="<?php echo $fileUrl; ?>"><?php echo $file[0][0]; ?></a>
                </div>
                <div class="clear"></div>
            </div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Upload_Output; ?>">
		</div>
	</form>
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Submit form
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
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
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
});
</script>
