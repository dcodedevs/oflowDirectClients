<script type="text/javascript">
    $(document).click(function (e) {
        // e.stopPropagation();
        // var container = $(".requestTrigger");
        //
        // //check if the clicked area is dropDown or not
        // if (container.has(e.target).length === 0) {
        //     $('.requestsDropdown').hide();
        // }
    })
    $("#fw_notificationcenter_content .fw_notificationcenter_header_button").unbind("click").bind("click", function(e){
        e.preventDefault();
        if($("#fw_notificationcenter_content .fw_notificationcenter_dropdown").is(":visible")){
            $("#fw_notificationcenter_content .fw_notificationcenter_dropdown").hide();
        } else {
            $("#fw_notificationcenter_content .fw_notificationcenter_dropdown").show();
            if($("#fw_notificationcenter_content .fw_notificationcenter_dropdown .fw_notification_item").length == 0 || $("#fw_notificationcenter_content .countWrapper").is(":visible")){
                load_notifications();
            }
        }
    })
    $("#fw_notificationcenter_content .fw_notificationcenter_dropdown").hover(function(){
    },function(){
        $('#fw_notificationcenter_content .fw_notificationcenter_dropdown').hide();
    })
    function load_notifications(){
        var data = {
            method: "get_notifications",
            accountname: '<?php echo $_GET['accountname'];?>',
            caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
			url_share: '<?php echo $variables->fw_url_share ? 1 : 0; ?>',
            pageID: '<?php echo $_GET['pageID'];?>',
            companyID: '<?php echo $_GET['companyID'];?>',
            dlang: '<?php echo $variables->defaultLanguageID; ?>',
            lang: '<?php echo $variables->languageID;?>',
        }
        notification_ajax_call(data, function(returnData){
            $("#fw_notificationcenter_content .fw_notificationcenter_dropdown .fw_notification_list").html(returnData.html);
            notification_update_seen_status();
            bind_notification_clicks();
        });
    }
    function bind_notification_clicks(){
        $("#fw_notificationcenter_content .fw_notificationcenter_dropdown .fw_notification_item").off('click').on("click", function(){
            var notificationId = $(this).data("notification-id");
            if(notificationId != undefined) {
                if($(this).hasClass("not_pressed")) {
                    notification_update_pressed_status(notificationId);
                }
                var notificationUrl = $(this).data("href");
                if(notificationUrl != undefined && notificationUrl != ""){
                    fw_load_ajax(notificationUrl, false, true);
                    $('#fw_notificationcenter_content .fw_notificationcenter_dropdown').hide();

                }
            }
        })
        $("#fw_notificationcenter_content .fw_notificationcenter_dropdown .fw_load_more_notifications").off('click').on("click", function(){
            var element = $(this);
            var page = parseInt(element.data("page")) + 1;
            var data = {
                method: "get_notifications",
                accountname: '<?php echo $_GET['accountname'];?>',
                caID: '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
				url_share: '<?php echo $variables->fw_url_share ? 1 : 0; ?>',
                pageID: '<?php echo $_GET['pageID'];?>',
                companyID: '<?php echo $_GET['companyID'];?>',
                dlang: '<?php echo $variables->defaultLanguageID; ?>',
                lang: '<?php echo $variables->languageID;?>',
                page: page,
                entriesOnly: 1
            }
            element.html('<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>');
            notification_ajax_call(data, function(returnData){
                element.remove();
                $("#fw_notificationcenter_content .fw_notificationcenter_dropdown .fw_notification_list").append(returnData.html);
                bind_notification_clicks();
            });
        })
    }
    function notification_update_seen_status(){
        // var notificationIds = [];
        // var notificationItems = $(".fw_notificationcenter_dropdown .fw_notification_item");
        // notificationItems.each(function(index, el){
        //     var notificationId = $(el).data("notification-id");
        //     if(notificationId != undefined) {
        //         notificationIds.push(notificationId);
        //     }
        // })
        var data = {
            "method": "update_seen",
            "caID": '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
			url_share: '<?php echo $variables->fw_url_share ? 1 : 0; ?>',
            dlang: '<?php echo $variables->defaultLanguageID; ?>',
            lang: '<?php echo $variables->languageID;?>',
        }
        notification_ajax_call(data, function(returnData){
            if(returnData.result){
                $("#fw_notificationcenter_content .countWrapper").html(0).hide();
            }
        });
    }
    function refresh_notification_unseen_count(){
        var data = {
            "method": "get_unseen_notification_count",
            "caID": '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
			url_share: '<?php echo $variables->fw_url_share ? 1 : 0; ?>',
            dlang: '<?php echo $variables->defaultLanguageID; ?>',
            lang: '<?php echo $variables->languageID;?>',
        }
        notification_ajax_call(data, function(returnData){
            $("#fw_notificationcenter_content .countWrapper").html(parseInt(returnData.result));
			if(parseInt(returnData.result) > 0){
                $("#fw_notificationcenter_content .countWrapper").show();
            } else {
				$("#fw_notificationcenter_content .countWrapper").hide();
			}
			if(returnData.upgrade_lock == 1){
				$('#fw_account').html(returnData.upgrade_text);
				$('#fw_account_upgrade_line').hide();
			} else if(returnData.upgrade == 1){
				$('#fw_account_upgrade_line').slideDown().find('h3').text(returnData.upgrade_text);
			}
        });
    }
    function notification_update_pressed_status(notificationId){
        // var notificationIds = [];
        // var notificationItems = $(".fw_notificationcenter_dropdown .fw_notification_item");
        // notificationItems.each(function(index, el){
        //     var notificationId = $(el).data("notification-id");
        //     if(notificationId != undefined) {
        //         notificationIds.push(notificationId);
        //     }
        // })
        var data = {
            "method": "update_pressed",
            "caID": '<?php echo (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']);?>',
			url_share: '<?php echo $variables->fw_url_share ? 1 : 0; ?>',
            dlang: '<?php echo $variables->defaultLanguageID; ?>',
            lang: '<?php echo $variables->languageID;?>',
            notification_id: notificationId
        }
        notification_ajax_call(data, function(returnData){
            if(returnData.result){
                $('.fw_notification_item[data-notification-id="'+notificationId+'"]').removeClass("not_pressed");
            }
        });
    }

    function notification_ajax_call(data, callback){
        if(data == undefined) data = {}
        var ajaxUrl = '<?php echo $variables->account_framework_url; ?>getynet_fw/modules/NotificationCenter/output/output_ajax.php';
        $.ajax({
            type: "POST",
            url: ajaxUrl,
            data: data,
            cache: false,
            dataType: "json",
            success: function(data)
            {
                callback(data);
            }
        });
    }
    //refresh on page load
    var fw_notification_interval;
    if(fw_notification_interval){
        clearInterval(fw_notification_interval);
        fw_notification_interval = null;
    }
    //check for new noticitions
	if (!fw_notification_interval) {
		fw_notification_interval = setInterval(function(){
            refresh_notification_unseen_count();
	   }, 30000);
   }
</script>
