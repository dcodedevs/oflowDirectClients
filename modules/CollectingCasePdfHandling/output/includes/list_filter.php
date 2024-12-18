<?php
require_once(__DIR__."/../../../../fw/account_fw/includes/fn_fw_api_call.php");
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'not_printed';

?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "not_printed"; }


?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'not_printed' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="not_printed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_printed"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $not_printed_count; ?></span>
                    <?php echo $formText_NotPrinted_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'printed_today' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="printed_today" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=printed_today"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $printed_today_count; ?></span>
                    <?php echo $formText_PrintedToday_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($list_filter == 'printed_earlier' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="printed_earlier" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=printed_earlier"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $printed_earlier_count; ?></span>
                    <?php echo $formText_PrintedEarlier_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>

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
