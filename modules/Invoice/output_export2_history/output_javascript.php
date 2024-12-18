<script type="text/javascript">
    /**
     * ajaxCall - for easier ajax calls across module
     */
    function ajaxCall(includeFile, data, callback, showLoader) {

        // includeFile check
        if (typeof(includeFile) !== 'string') return;
        // data object check
        if (typeof(data) !== 'object') var data = {};
        // callback check
        if (typeof(callback) !== 'function') var callback = function() { };
        // showLoader check
        if (typeof(showLoader) !== 'boolean') var showLoader = true;

        // Default data
        var __data = {
            fwajax: 1,
            fw_nocss: 1
        }

        // Concat default and user data
        var ajaxData = $.extend({}, __data, data);

        // Show loader
        if (showLoader) $('#fw_loading').show();

        // Run AJAX
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_export2_history&inc_obj=ajax&inc_act="; ?>' + includeFile,
            data: ajaxData,
            success: function(json){
                if (showLoader) $('#fw_loading').hide();
                callback(json);
            }
        });
    }

    /**
     * loadView - for easier view loading across module
     */
    function loadView(includeFile, data) {

        // includeFile check
        if (typeof(includeFile) !== 'string') return;
        // data object check
        if (typeof(data) !== 'object') var data = {};
        // Url params
        var urlParams = $.param(data);

        // Load view
        fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output_export2_history";?>&inc_obj=" + includeFile + '&' + urlParams, '', true);

    }
</script>
