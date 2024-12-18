<?php
require_once(__DIR__."/../../../../fw/account_fw/includes/fn_fw_api_call.php");
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'not_processed';
$actiontype_filter = $_GET['actiontype_filter'] ? $_GET['actiontype_filter'] : '';
?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "not_printed"; }


?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'not_processed' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="not_processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_processed"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $not_processed_count; ?></span>
                    <?php echo $formText_NotProcessed_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'processed' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=processed"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $processed_count; ?></span>
                    <?php echo $formText_Processed_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'failed' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="failed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=failed"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $failed_count; ?></span>
                    <?php echo $formText_Failed_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'canceled' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="canceled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=canceled"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $canceled_count; ?></span>
                    <?php echo $formText_Canceled_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'under_process' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="under_process" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=under_process"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $under_process_count; ?></span>
                    <?php echo $formText_UnderProcess_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($list_filter == 'for_download' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="for_download" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=for_download"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $for_download_count; ?></span>
                    <?php echo $formText_ForDownload_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($list_filter == 'demo' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="demo" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=demo"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $demo_count; ?></span>
                    <?php echo $formText_DemoNotForSending_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'all' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=all"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $all_count; ?></span>
                    <?php echo $formText_All_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>
<div class="" style="float:left">
    <?php 
    $sending_action_text = array(1=>$formText_SendLetter_output, 2=>$formText_SendEmail_output, 3=>$formText_SelfPrint_output, 4=>$formText_SendSms_output, 5=>$formText_SendEhf_output);
    $status_array = array(-2=>$formText_MarkedToBeProcessed_output, -1=>$formText_Processing_output, 0 => $formText_NotProcessed_output, 1=> $formText_Completed_output, 2=>$formText_Failed_output, 3=>$formText_Canceled_output, 4=>$formText_ForDownload_output, 5=>$formText_DemoNotForSending_output);

    ?>
    <?php echo $formText_SendingAction_output;?>:
    <select class="changeSendingType">
        <option value=""><?php echo $formText_All_output;?></option>
        <?php foreach($sending_action_text as $key=> $sending_action) { ?>
            <option value="<?php echo $key;?>" <?php if($actiontype_filter == $key){ echo 'selected';}?>><?php echo $sending_action;?></option>
        <?php } ?>
    </select>
</div>
<?php if($list_filter == "failed") { ?>    
    
    <div class="change_sending_wrapper">
        <select class="changeSendingStatusAll">
            <option value=""><?php echo $formText_Select_output;?></option>
            <?php foreach($status_array as $key=>$status) { ?>
                <option value="<?php echo $key;?>"><?php echo $status;?></option>
            <?php } ?>
        </select>
        <span class="change_all_sending_status"><?php echo $formText_ChangeSendingStatus_output;?> <span class="selected_letters">0</span></span>
    </div>
    <div class="change_sending_wrapper">
        <select class="changeSendingTypeAll">
            <option value=""><?php echo $formText_Select_output;?></option>
            <?php foreach($sending_action_text as $key=> $sending_action) { ?>
                <option value="<?php echo $key;?>"><?php echo $sending_action;?></option>
            <?php } ?>
        </select>
        <span class="change_all_sending_action"><?php echo $formText_ChangeSendingAction_output;?> <span class="selected_letters">0</span></span>
    </div>

<?php } else { ?>
<?php if($list_filter == "for_download") { ?>
	<div class="markAsSent"><?php echo $formText_MarkAsSent_output; ?> <span class="selected_letters">0</span></div>
	<div class="downloadPdfs"><?php echo $formText_DownloadSelectedPdf_output; ?> <span class="selected_letters">0</span></div>
<?php } else { ?>
	<div class="launchPdfGenerate"><?php echo $formText_LaunchLetterProcess_output; ?> <span class="selected_letters">0</span></div>
	<div class="markForDownload"><?php echo $formText_MarkForDownload_output; ?> <span class="selected_letters">0</span></div>
<?php } ?>
<?php } ?>
<div class="clear"></div>
<style>
	.downloadPdfs {
		float: right;
		cursor: pointer;
		color: #46b2e2;
		margin-right: 20px;
		margin-top: 30px;
	}
	.markForDownload {
		float: right;
		cursor: pointer;
		color: #46b2e2;
		margin-right: 20px;
		margin-top: 30px;
	}
	.markAsSent {
		color: #fff;
		background: #0497e5;
		border: 1px solid #0497e5;
		cursor: pointer;
		padding: 10px 20px;
		float: right;
		font-size: 14px;
		font-weight: bold;
		border-radius: 3px;
		margin-left: 10px;
		margin-top: 20px;
	}
    .change_sending_wrapper {
        float: right;
        margin-left: 10px;
    }
    .change_all_sending_status {
        color: #fff;
		background: #0497e5;
		border: 1px solid #0497e5;
		cursor: pointer;
		padding: 5px 10px;
		border-radius: 3px;
    }
    .change_all_sending_action {
        color: #fff;
		background: #0497e5;
		border: 1px solid #0497e5;
		cursor: pointer;
		padding: 5px 10px;
		border-radius: 3px;
    }
</style>
<script type="text/javascript">
$(document).ready(function(){
    $(".customerIdSelector").on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            customer_filter:$(".customerId").val(),
            search_filter: $('.searchFilter').val()
        };
        loadView('list', data);
    });
    // Filter by building
    $('.filterDepartment').on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            customer_filter:$(".customerId").val(),
            search_filter: $('.searchFilter').val()
        };
        loadView('list', data);
    });
    $(".changeSendingType").on('change', function(e) {
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            actiontype_filter:$(".changeSendingType").val(),
        };
        loadView('list', data);
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            customer_filter:$(".customerId").val(),
            search_filter: $('.searchFilter').val(),
        };
        loadView('list', data);
    });
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            customer_filter:$(".customerId").val()
        };
        loadView('list', data);
    });
	$(".downloadPdfs").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			fwajax: 1,
			fw_nocss: 1
		}
		submit_post_via_hidden_form_download('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=download_selected_pdfs"?>', data);

	})
	$(".markForDownload").off("click").on("click", function(e){
		var casesToGenerate = [];
		$(".checkboxesGenerate").each(function(index, el){
			if($(el).is(":checked")){
				casesToGenerate.push($(el).val());
			}
		})

		var data = {
			casesToGenerate: casesToGenerate
		}
		ajaxCall('mark_for_download', data, function(json) {
			var data = {
				list_filter: '<?php echo $list_filter;?>'
			};
			loadView('list', data);
		});
	})
	$(".markAsSent").off("click").on("click", function(e){
		var casesToGenerate = [];
		$(".checkboxesGenerate").each(function(index, el){
			if($(el).is(":checked")){
				casesToGenerate.push($(el).val());
			}
		})

		var data = {
			casesToGenerate: casesToGenerate
		}
		ajaxCall('mark_as_sent', data, function(json) {
			var data = {
				list_filter: '<?php echo $list_filter;?>'
			};
			loadView('list', data);
		});
	})

	function submit_post_via_hidden_form_download(url, params) {
	    var f = $("<form method='POST' target='_blank' style='display:none;'></form>").attr({
	        action: url
	    }).appendTo(document.body);
	    for (var i in params) {
	        if (params.hasOwnProperty(i)) {
	            $('<input type="hidden" />').attr({
	                name: i,
	                value: params[i]
	            }).appendTo(f);
	        }
	    }
		$(".checkboxesGenerate").each(function(index, el){
			if($(el).is(":checked")){
				$('<input type="hidden" />').attr({
				   name: "casesToGenerate[]",
				   value: $(el).val()
			   }).appendTo(f);
			}
		})
	    f.submit();
	    f.remove();
	}

})
</script>
