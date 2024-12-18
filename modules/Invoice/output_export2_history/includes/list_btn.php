<div class="p_headerLine">
    <?php if($moduleAccesslevel > 10): ?>
        <div class="goToExportPage btnStyle">
            <div class="plusTextBox active">
                <div class="text"><?php echo $formText_GoToInvoiceList_Output; ?></div>
            </div>
            <div class="clear"></div>
        </div>
    	<div class="clear"></div>
    <?php endif; ?>
</div>


<script type="text/javascript">
    $(document).ready(function(e) {
        $(".goToExportPage").on('click', function(e){
            e.preventDefault();
            fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
        });
    });
</script>
