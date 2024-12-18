<?php
require_once(__DIR__."/../../../../fw/account_fw/includes/fn_fw_api_call.php");
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'payments';

?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "payments"; }
?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'payments' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="payments" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&list_filter=payments"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $payments_count; ?></span>
                    <?php echo $formText_UnsettledPayments_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'settlements' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="settlements" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid."&list_filter=settlements"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $settlements_count; ?></span>
                    <?php echo $formText_Settlements_output;?>
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
