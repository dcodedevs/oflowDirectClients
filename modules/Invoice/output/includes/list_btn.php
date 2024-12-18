<div class="p_headerLine">
    <?php if($moduleAccesslevel > 10):
        $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE exportScriptFolder <> ''");
        $exportScriptOC = $o_query ? $o_query->row_array() : array();
        $exportScriptName = basename($exportScriptOC['exportScriptFolder']);

        $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE export2ScriptFolder <> ''");
        $exportScriptOC = $o_query ? $o_query->row_array() : array();
        $export2ScriptName = basename($exportScriptOC['export2ScriptFolder']);

        $sql = "SELECT * FROM invoice_accountconfig";
        $o_query = $o_main->db->query($sql);
        $invoice_accountconfig = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        ?>

        <?php if($exportScriptName != ""): ?>
            <div class="goToExportPage btnStyle">
                <div class="plusTextBox active">
                    <div class="text"><?php echo $formText_GoToExportPage_Output; ?></div>
                </div>
                <div class="clear"></div>
            </div>
        <?php endif; ?>

        <?php if($export2ScriptName != ""): ?>
            <div class="goToExport2Page btnStyle">
                <div class="plusTextBox active">
                    <div class="text"><?php echo $formText_GoToExport2Page_Output; ?></div>
                </div>
                <div class="clear"></div>
            </div>
        <?php endif; ?>

        <?php if ($invoice_accountconfig['activate_check_sync_status']): ?>
            <div class="checkSyncStatus btnStyle">
                <div class="plusTextBox active">
                    <div class="text"><?php echo $formText_CheckSyncStatus_Output; ?></div>
                </div>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
    	<div class="clear"></div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    $(document).ready(function(e) {
        $(".goToExportPage").on('click', function(e){
            e.preventDefault();
            fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export_history&inc_obj=list"; ?>', false, true);
        });

        $(".goToExport2Page").on('click', function(e){
            e.preventDefault();
            fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export2_history&inc_obj=list"; ?>', false, true);
        });

        $(".checkSyncStatus").on('click', function(e){
            e.preventDefault();
            var data = { };
            ajaxCall('check_sync_status', data, function(obj) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        });

    });
</script>
