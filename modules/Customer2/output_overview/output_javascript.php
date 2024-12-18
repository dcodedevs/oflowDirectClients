<script type="text/javascript">
	$(document).ajaxStart(function() {
		$('.loader').show();
	}).ajaxStop(function() {
		$('.loader').hide();
	});
    /**
     * ajaxCall - for easier ajax calls across module
     */
    function ajaxCall(includeFile, data, callback, showLoader, failover, customModuleData) {
        var moduleName = '<?php echo $module?>';
        var moduleFolder = "output";
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
        if (showLoader) document.body.style.cursor = 'wait';

        // Run AJAX
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo ACCOUNT_FW_URL."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module="?>'+moduleName+'<?php echo "&folderfile=output&folder="?>'+moduleFolder+'<?php echo "&inc_obj=ajax&inc_act="; ?>' + moduleFile,
            data: ajaxData,
            success: function(json){
                if(showLoader) document.body.style.cursor = 'default';
                callback(json);
            }
        }).fail(function(){
			if(showLoader) document.body.style.cursor = 'default';
			failover();
		});
    }

    /**
     * loadView - for easier view loading across module
     */
     function loadView(includeFile, data) {
         var moduleName = '<?php echo $module?>';
         var moduleFolder = "output";
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
         fw_load_ajax("<?php echo ACCOUNT_FW_URL."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module="?>"+moduleName+"<?php echo "&folder="?>"+moduleFolder+"<?php echo "&folderfile=output";?>&inc_obj=" + moduleFile + '&' + urlParams, '', true);

     }
</script>
