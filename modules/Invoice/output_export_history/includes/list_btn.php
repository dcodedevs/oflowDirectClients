<div class="p_headerLine">
    <?php if($moduleAccesslevel > 10): ?>
        <div class="goToExportPage btnStyle">
            <div class="plusTextBox active">
                <div class="text"><?php echo $formText_GoToInvoiceList_Output; ?></div>
            </div>
            <div class="clear"></div>
        </div>
        <span class="editInvoiceConfig fas fa-cog"></span>
    	<div class="clear"></div>
    <?php endif; ?>
</div>

<style>
.editInvoiceConfig {
    float: right;
    cursor: pointer;
    color: #46b2e2;
}

</style>

<script type="text/javascript">
    $(document).ready(function(e) {
        $(".goToExportPage").on('click', function(e){
            e.preventDefault();
            fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
        });
    });
    $(".editInvoiceConfig").on('click', function(e){
        e.preventDefault();
        var data = { };
        ajaxCall('edit_invoice_accountconfig', data, function(obj) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
</script>
