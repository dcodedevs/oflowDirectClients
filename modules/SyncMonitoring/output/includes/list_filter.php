<?php
require_once(__DIR__."/../../../../fw/account_fw/includes/fn_fw_api_call.php");
$transaction_log = $_GET['transaction_log'] ? ($_GET['transaction_log']) : '0';

?>
<div class="output-filter">
    <ul>
		<li class="item<?php echo ($transaction_log == '0' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="0" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&transaction_log=0"; ?>">
				<span class="link_wrapper">
					<?php echo $formText_MainLogs_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($transaction_log == '1' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="1" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&transaction_log=1"; ?>">
				<span class="link_wrapper">
					<?php echo $formText_LogsWithTransactionFees_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($transaction_log == '2' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="2" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&transaction_log=2"; ?>">
				<span class="link_wrapper">
					<?php echo $formText_LogsWithResetFees_output;?>
				</span>
			</a>
		</li>
		<li class="item<?php echo ($transaction_log == '3' ? ' active':'');?>">
			<a class="topFilterlink" data-listfilter="3" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&transaction_log=3"; ?>">
				<span class="link_wrapper">
					<?php echo $formText_CompanyInfoLogs_output;?>
				</span>
			</a>
		</li>
    </ul>
</div>
<?php
/*
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" autocomplete="off" value="<?php echo $search_filter;?>" placeholder="<?php echo $formText_SearchForPayments_output;?>">
            <button id="p_tableFilterSearchBtn" class="fw_button_color "><?php echo $formText_Search_output; ?></button>
        </form>
        <div class="clear"></div>
        <div class="filteredCountRow">
            <span class="selectionCount">0</span> <?php echo $formText_InSelection_output;?>
            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
        </div>
    </div>
    <div class="p_tableFilter_left">
        <?php if(count($customersWithAccessTo) > 1){?>
            <select name="customerId" class="customerId customerIdSelector" autocomplete="off">
                <option value=""><?php echo $formText_Select_output;?></option>
                <?php foreach($customersWithAccessTo as $customerWithAccessTo){ ?>
                <option value="<?php echo $customerWithAccessTo['id'];?>" <?php if($customerWithAccessTo['id'] == $customer_filter) echo 'selected';?>><?php echo $customerWithAccessTo['name'];?></option>
                <?php } ?>
            </select>
        <?php } else {?>
            <input type="hidden" class="customerId" name="customerId" value="<?php echo $customersWithAccessTo[0]['id'];?>"/>
            <?php echo $customersWithAccessTo[0]['name'];?>
        <?php } ?>
        <div class="clear"></div>
    </div>
</div>
*/?>
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
})
</script>
