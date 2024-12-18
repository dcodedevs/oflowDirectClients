<?php

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

if($article_accountconfig['path_import_from_integration'] != "") {
    if($_POST['output_form_submit']) {

		$hook_file = __DIR__ . '/../../../../' . $article_accountconfig['path_import_from_integration'];
		if (file_exists($hook_file)) {
			require_once $hook_file;
            $s_sql = "SELECT * FROM integrationtripletex ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $integrationtripletexs = $o_query ? $o_query->result_array() : array();
            foreach($integrationtripletexs as $integrationtripletex) {
                if($integrationtripletex['ownerCompanyId'] > 0){
                    $hook_result = array();
                    $hook_params = array(
                        'ownercompany_id' => $integrationtripletex['ownerCompanyId']
                    );
        			if (is_callable($run_hook)) {
        				$hook_result = $run_hook($hook_params);
        			}
                    echo count($hook_result['successully_imported'])." ".$formText_ArticlesImportedSuccessfully_output."<br/>";

                    echo count($hook_result['failed_to_import'])." ".$formText_ArticlesFailedToImport_output."<br/>";
                }
            }
            unset($run_hook);
		}

        return;
    }
    ?>
    <div class="popupform">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=import_from_integration";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <input type="hidden" name="articleId" value="<?php echo $articleId;?>">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
            <div class="inner">
                <?php echo $formText_LaunchImportFromIntegration_output?>
            </div>
            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_Import_Output; ?>">
            </div>
        </form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
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
                    if(data.error !== undefined){
                        $("#popup-validate-message").html(data.error);
                        $("#popup-validate-message").show();
                    } else {

    					$('#popupeditboxcontent').html('');
    					$('#popupeditboxcontent').html(data.html);
    					out_popup = $('#popupeditbox').bPopup(out_popup_options);
    					$("#popupeditbox:not(.opened)").remove();
                        out_popup.addClass("close-reload");

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
    </script>
    <?php
} else {
    echo $formText_MissingPathForImport_output;
}
?>
