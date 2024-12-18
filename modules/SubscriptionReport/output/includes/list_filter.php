<?php
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'active';

$sql = "SELECT * FROM subscriptiontype ORDER BY subscriptiontype.name";
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0) $firstSubscriptionType = $o_query->row_array();


if(isset($_POST['subscription_type'])) $_GET['subscription_type'] = $_POST['subscription_type'];
if(isset($_POST['workgroup_filter'])) $_GET['workgroup_filter'] = $_POST['workgroup_filter'];
if(isset($_POST['status_filter'])) $_GET['status_filter'] = $_POST['status_filter'];
if(isset($_POST['search_filter'])) $_GET['search_filter'] = $_POST['search_filter'];
if(isset($_POST['customerselfdefinedlist_filter'])) $_GET['customerselfdefinedlist_filter'] = $_POST['customerselfdefinedlist_filter'];
if(isset($_POST['ownercompany_filter'])) $_GET['ownercompany_filter'] = $_POST['ownercompany_filter'];

$subscription_type_filter = isset($_GET['subscription_type']) ? $_GET['subscription_type'] : $firstSubscriptionType['id'];
$workgroup_filter = isset($_GET['workgroup_filter']) ? $_GET['workgroup_filter'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 2;
$search_filter = isset($_GET['search_filter']) ? $_GET['search_filter'] : '';
$customerselfdefinedlist_filter = isset($_GET['customerselfdefinedlist_filter']) ? $_GET['customerselfdefinedlist_filter'] : '';
$ownercompany_filter = isset($_GET['ownercompany_filter']) ? $_GET['ownercompany_filter'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : date("d.m.Y");
require_once __DIR__ . '/functions.php';

$s_sql = "SELECT * FROM subscriptionreport_accountconfig ORDER BY id ASC";
$o_query = $o_main->db->query($s_sql);
$subscriptionreport_accountconfig = ($o_query ? $o_query->row_array():array());

$s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($subscription_type_filter));
$subscriptionType = ($o_query ? $o_query->row_array():array());
if($subscriptionType['periodUnit'] == 0 ){
    $totalSummaryPerMonth = get_total_subscription_summary_per_month($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, false,  $date_filter, $workgroup_filter);
    $totalSummaryPerYear = $totalSummaryPerMonth * 12;
} else {
    $totalSummaryPerYear = get_total_subscription_summary_per_month($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, false,  $date_filter, $workgroup_filter);
    $totalSummaryPerMonth = $totalSummaryPerYear / 12;
}
// $totalSummaryPerYear = get_total_subscription_summary_per_month($o_main, $list_filter, $search_filter, $subscription_type_filter, $status_filter, true);
$active_count = get_support_list_count($o_main, 'active', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, false, $workgroup_filter);
$not_started_count = get_support_list_count($o_main, 'not_started', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
$stopped_count = get_support_list_count($o_main, 'stopped', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
$future_stop_count = get_support_list_count($o_main, 'future_stop', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
$deleted_count = get_support_list_count($o_main, 'deleted', $search_filter, $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);
$freeNoBillingAmount = get_support_list_count($o_main, $list_filter, 'freeNoBilling', $subscription_type_filter, $status_filter, $customerselfdefinedlist_filter, $ownercompany_filter, $date_filter, $workgroup_filter);

$s_sql = "SELECT * FROM ownercompany ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$ownercompanies = ($o_query ? $o_query->result_array():array());

?>
<?php
echo $formText_Date_output." ";
?>
<input type="text" class="date_filter" autocomplete="off" value="<?php echo date("d.m.Y", strtotime($date_filter))?>"/>

<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="active" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active&date_filter=".$date_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $active_count; ?></span>
                    <?php echo $formText_ActiveSubscriptions_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'not_started' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="not_started" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_started&date_filter=".$date_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $not_started_count; ?></span>
                    <?php echo $formText_NotStartedSubscriptions_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'stopped' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="stopped" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=stopped&date_filter=".$date_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $stopped_count; ?></span>
                    <?php echo $formText_StoppedSubscriptions_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'future_stop' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="future_stop" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=future_stop&date_filter=".$date_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $future_stop_count; ?></span>
                    <?php echo $formText_FutureStopSubscriptions_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'deleted' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="deleted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=deleted&date_filter=".$date_filter; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $deleted_count; ?></span>
                    <?php echo $formText_DeletedSubscriptions_output;?>
                </span>
            </a>
        </li>
    </ul>
</div>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <?php echo $formText_SubscriptionType_output; ?>
        <span class="selectDiv selected">
            <span class="selectDivWrapper">
                <select name="defaultSelect" class="subscriptionTypeFilter" autocomplete="off">
                    <?php
                    $sql = "SELECT * FROM subscriptiontype ORDER BY subscriptiontype.name";
                    $o_query = $o_main->db->query($sql);
                    if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result_array() as $row)
					{
						?><option value="<?php echo $row['id']; ?>" <?php if($subscription_type_filter == $row['id']) { echo 'selected';}?>><?php echo $row['name']; ?></option><?php
					}
					?>
                </select>
            </span>
            <span class="arrowDown"></span>
        </span>
        <?php if($subscriptionreport_accountconfig['activate_filter_by_workgroup']) {?>
			<?php echo $formText_Workgroup_Output; ?>
			<span class="selectDiv selected">
				<span class="selectDivWrapper">
					<select name="workgroupFilterSelect" class="workgroupFilter" autocomplete="off">
						<option value=""><?php echo $formText_All_output;?></option>
						<?php
						$sql = "SELECT * FROM workgroup ORDER BY name";
						$o_query = $o_main->db->query($sql);
						if($o_query && $o_query->num_rows()>0)
						foreach($o_query->result_array() as $row)
						{
							?><option value="<?php echo $row['id']; ?>" <?php if($workgroup_filter == $row['id']) { echo 'selected';}?>><?php echo $row['name']; ?></option><?php
						}
						?>
					</select>
				</span>
				<span class="arrowDown"></span>
			</span>
            <script type="text/javascript">
                $('.workgroupFilter').on('change', function(e) {
                    var data = {
                        subscription_type: $(".subscriptionTypeFilter").val(),
                        workgroup_filter: $('.workgroupFilter').val(),
                        status_filter: $('.statusFilter').val(),
                        list_filter: '<?php echo $list_filter; ?>',
                        search_filter: $('.searchFilter').val(),
                        customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
                        ownercompany_filter: $('.ownercompanyFilter').val()
                    };
                    ajaxCall('list', data, function(json) {
                        $('.p_pageContent').html(json.html);
                    });
                });
            </script>
		<?php } ?>
        <?php if($subscriptionreport_accountconfig['activateCustomerSelfdefnedListFilter']) {?>
            <?php echo $formText_CustomerSelfdefinedListFilter_output; ?>
            <span class="selectDiv selected">
                <span class="selectDivWrapper">
                    <select name="defaultSelect" class="customerSelfdefinedListFilter" autocomplete="off">
                        <option value=""><?php echo $formText_All_output;?></option>
                        <?php
                        $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($subscriptionreport_accountconfig['customerSelfdefinedField']));
                        $customer_selfdefined_field = ($o_query ? $o_query->row_array():array());

                        $sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name";
                        $o_query = $o_main->db->query($sql, array($customer_selfdefined_field['list_id']));
                        if($o_query && $o_query->num_rows()>0)
    					foreach($o_query->result_array() as $row)
    					{
    						?><option value="<?php echo $row['id']; ?>" <?php if($customerselfdefinedlist_filter == $row['id']) { echo 'selected';}?>><?php echo $row['name']; ?></option><?php
    					}
    					?>
                    </select>
                </span>
                <span class="arrowDown"></span>
            </span>
            <script type="text/javascript">
                $('.customerSelfdefinedListFilter').on('change', function(e) {
                    var data = {
                        subscription_type: $(".subscriptionTypeFilter").val(),
                        workgroup_filter: $('.workgroupFilter').val(),
                        status_filter: $('.statusFilter').val(),
                        list_filter: '<?php echo $list_filter; ?>',
                        search_filter: $('.searchFilter').val(),
                        customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
                        ownercompany_filter: $('.ownercompanyFilter').val()
                    };
                    ajaxCall('list', data, function(json) {
                        $('.p_pageContent').html(json.html);
                    });
                });
            </script>
        <?php } ?>

        <?php
        if(count($ownercompanies) > 1) {
            ?>
            <?php echo $formText_Ownercompany_output; ?>
            <span class="selectDiv selected">
                <span class="selectDivWrapper">
                    <select name="defaultSelect" class="ownercompanyFilter" autocomplete="off">
                        <option value=""><?php echo $formText_All_output;?></option>
                        <?php
                        foreach($ownercompanies as $ownercompany) {
    						?><option value="<?php echo $ownercompany['id']; ?>" <?php if($ownercompany_filter == $ownercompany['id']) { echo 'selected';}?>><?php echo $ownercompany['name']; ?></option><?php
    					}
    					?>
                    </select>
                </span>
                <span class="arrowDown"></span>
            </span>
            <?php
        }
        ?>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>

    <div class="clear"></div>
    <div class="summary">
        <?php echo $formText_TotalSummaryPerMonth_output;?>: <?php echo number_format($totalSummaryPerMonth, 2, ',', '');  ?> &nbsp;&nbsp;
        <?php echo $formText_TotalSummaryPerYear_output;?>: <?php echo number_format($totalSummaryPerYear, 2, ',', '');  ?> &nbsp;&nbsp;
        <?php echo $formText_FreeNoBillingAmount_output;?>: <?php echo number_format($freeNoBillingAmount, 0, ',', '');  ?>
    </div>
    <?php require("export_btn.php");?>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('.subscriptionTypeFilter').on('change', function(e) {
        var data = {
            subscription_type: $(this).val(),
			workgroup_filter: $('.workgroupFilter').val(),
            status_filter: $('.statusFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $(".date_filter").val()
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $('.ownercompanyFilter').on('change', function(e) {
        var data = {
            subscription_type: $(".subscriptionTypeFilter").val(),
			workgroup_filter: $('.workgroupFilter').val(),
            status_filter: $('.statusFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $(".date_filter").val()
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $('.statusFilter').on('change', function(e) {
        var data = {
            subscription_type: $('.subscriptionTypeFilter').val(),
			workgroup_filter: $('.workgroupFilter').val(),
            status_filter: $('.statusFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $(".date_filter").val()
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            subscription_type: $('.subscriptionTypeFilter').val(),
			workgroup_filter: $('.workgroupFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            status_filter: $('.statusFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $(".date_filter").val()
        };
        loadView("list", data);
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
    });
    $(".topFilterlink").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            subscription_type: $('.subscriptionTypeFilter').val(),
			workgroup_filter: $('.workgroupFilter').val(),
            list_filter: $(this).data("listfilter"),
            status_filter: $('.statusFilter').val(),
            customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
            ownercompany_filter: $('.ownercompanyFilter').val(),
            date_filter: $(".date_filter").val()
        };
        loadView("list", data);
    })
    $('.date_filter').datepicker({
        firstDay: 1,
        dateFormat: 'dd.mm.yy',
        onClose: function(dateText, inst) {
            function isDonePressed() {
                return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
            }
            if (isDonePressed()){
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
            var data = {
                subscription_type: $('.subscriptionTypeFilter').val(),
				workgroup_filter: $('.workgroupFilter').val(),
                status_filter: $('.statusFilter').val(),
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                customerselfdefinedlist_filter: $('.customerSelfdefinedListFilter').val(),
                ownercompany_filter: $('.ownercompanyFilter').val(),
                date_filter: $(".date_filter").val()
            };
            loadView("list", data);

        }
    });
})
</script>
<style>
</style>
