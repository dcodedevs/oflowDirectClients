<?php
require_once(__DIR__."/../../../../fw/account_fw/includes/fn_fw_api_call.php");
$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'not_sent';

?>
<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "not_sent"; }


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
*/
$_SESSION['list_filter'] = $list_filter;

$s_sql = "SELECT es.batch_id, es.send_on, COUNT(est.id) AS cnt, es.id FROM sys_emailsend es LEFT OUTER JOIN sys_emailsendto est ON est.emailsend_id = es.id
WHERE es.content_table = 'collecting_cases' AND DATE(es.send_on) = CURDATE() GROUP BY es.batch_id ORDER BY es.batch_id";
$o_query = $o_main->db->query($s_sql);
$sent_today_count = $o_query ? $o_query->num_rows() : array();

$s_sql = "SELECT es.batch_id, es.send_on, COUNT(est.id) AS cnt, es.id FROM sys_emailsend es LEFT OUTER JOIN sys_emailsendto est ON est.emailsend_id = es.id
WHERE es.content_table = 'collecting_cases' AND DATE(es.send_on) <> CURDATE() GROUP BY es.batch_id ORDER BY es.batch_id";
$o_query = $o_main->db->query($s_sql);
$sent_earlier_count = $o_query ? $o_query->num_rows() : array();

$s_sql = "SELECT c.*, a.id AS action_id, cust.invoiceEmail FROM collecting_cases_handling_action a JOIN collecting_cases_handling h ON h.id = a.handling_id
JOIN collecting_cases c ON c.id = h.collecting_case_id
LEFT OUTER JOIN customer cust ON cust.id = c.debitor_id
WHERE (a.performed_date IS NULL OR a.performed_date = '0000-00-00')
AND (a.action_type = 2 OR (a.action_type = 4 AND (cust.invoiceEmail <> '' AND cust.invoiceEmail is not null))) AND a.collecting_cases_process_steps_action_id is not null
ORDER BY c.id";
$o_query = $o_main->db->query($s_sql);
$not_sent_count = $o_query ? $o_query->num_rows() : array();
?>

<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'not_sent' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="not_printed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_sent"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $not_sent_count; ?></span>
                    <?php echo $formText_NotSent_output;?>
                </span>
            </a>
        </li>
        <li class="item<?php echo ($list_filter == 'sent_today' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="sent_today" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=sent_today"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $sent_today_count; ?></span>
                    <?php echo $formText_SentToday_output;?>
                </span>
            </a>
        </li>

        <li class="item<?php echo ($list_filter == 'sent_earlier' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="sent_earlier" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=sent_earlier"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $sent_earlier_count; ?></span>
                    <?php echo $formText_SentEarlier_output;?>
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
