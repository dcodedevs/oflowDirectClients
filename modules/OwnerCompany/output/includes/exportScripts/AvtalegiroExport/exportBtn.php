<?php
$exportFolderName = basename(__DIR__);
?>
<div class="exportBtn exportBtnAvtalegiro btnStyle">
    <div class="plusTextBox active output-btn">
        <div class="text"><?php echo $formText_AvtalegiroExport_Output; ?></div>
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(".exportBtnAvtalegiro").on('click', function(event) {
        event.preventDefault();
        /* Act on the event */
        var data = {
            company_filter: '<?php echo $company_filter;?>'
        };

        if (typeof(showLoader) !== 'boolean') var showLoader = true;

        // Default data
        var __data = {
            fwajax: 1,
            fw_nocss: 1
        }

        // Concat default and user data
        var ajaxData = $.extend({}, __data, data);

        // Show loader
        if (showLoader) fw_loading_start();

        // Run AJAX
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=OwnerCompany&folderfile=output&folder=output&inc_obj=ajax&inc_act=export&exportBtn=".$exportFolderName; ?>',
            data: ajaxData,
            success: function(json){
                if (showLoader) fw_loading_end();
               
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            }
        });
    })
</script>