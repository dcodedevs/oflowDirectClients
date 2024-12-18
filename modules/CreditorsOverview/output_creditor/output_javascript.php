<script type="text/javascript">
    /**
     * ajaxCall - for easier ajax calls across module
     */
    function ajaxCall(includeFile, data, callback, showLoader, failover, customModuleData) {
        var moduleName = '<?php echo $module?>';
        var moduleFolder = "output_creditor";
        var moduleFile = "";
        // includeFile check
        if (typeof(includeFile) !== 'string') {
            if (typeof(includeFile) !== 'object') {
                 return;
            } else {
                if(typeof(includeFile.module_file) === "string"){
                    moduleFile = includeFile.module_file;
                }
                if(typeof(includeFile.module_name) === "string"){
                    moduleName = includeFile.module_name;
                }
                if(typeof(includeFile.module_folder) === "string"){
                    moduleFolder = includeFile.module_folder;
                }
            }
        } else {
            moduleFile = includeFile;
        }
        if(moduleFile == "") return;
        // data object check
        if (typeof(data) !== 'object') var data = {};
        // callback check
        if (typeof(callback) !== 'function') var callback = function() { };
        // showLoader check
        if (typeof(showLoader) !== 'boolean') var showLoader = true;
		// failover check
        if (typeof(failover) !== 'function') var failover = function() { };



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
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module="?>'+moduleName+'<?php echo "&folderfile=output&folder="?>'+moduleFolder+'<?php echo "&inc_obj=ajax&inc_act="; ?>' + moduleFile,
            data: ajaxData,
            success: function(json){
                if(showLoader) fw_loading_end();
                callback(json);
            }
        }).fail(function(){
			if(showLoader) fw_loading_end();
			failover();
		});
    }

    /**
     * loadView - for easier view loading across module
     */
     function loadView(includeFile, data) {
         var moduleName = '<?php echo $module?>';
         var moduleFolder = "output_creditor";
         var moduleFile = "";
         if (typeof(includeFile) !== 'string') {
             if (typeof(includeFile) !== 'object') {
                  return;
             } else {
                 if(typeof(includeFile.module_file) === "string"){
                     moduleFile = includeFile.module_file;
                 }
                 if(typeof(includeFile.module_name) === "string"){
                     moduleName = includeFile.module_name;
                 }
                 if(typeof(includeFile.module_folder) === "string"){
                     moduleFolder = includeFile.module_folder;
                 }
             }
         } else {
             moduleFile = includeFile;
         }
         // includeFile check
         if(moduleFile == "") return;
         // data object check
         if (typeof(data) !== 'object') var data = {};
         // Url params
         var urlParams = $.param(data);

         // Load view
         fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module="?>"+moduleName+"<?php echo "&folder="?>"+moduleFolder+"<?php echo "&folderfile=output";?>&inc_obj=" + moduleFile + '&' + urlParams, '', true);

     }
</script>
