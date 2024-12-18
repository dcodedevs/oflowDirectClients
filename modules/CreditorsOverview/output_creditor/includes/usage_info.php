<?php
$show_closed = isset($_GET['show_closed']) ? $_GET['show_closed'] : 0;
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$v_creditor['id'];
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;
$creditor_id = isset($_GET['creditor_id']) ? $_GET['creditor_id'] : 0;

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_pagePreDetail">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>
                </div>
                <?php 
                    //0:not decided::1:Can send reminder manual::2:Can send reminder automatic::3:missing address::4:due date not expired::5:do not send::6:stopped with objection::7:not payed consider collecting manual::8:not payed consider collecting automatic::9:not payed consider collecting missing address::10:1st tab not enough amount::11:last tab not enough amount 
                    $tab_statuses = array("", 
                    $formText_CanSendReminderNowManual_output,
                    $formText_CanSendReminderNowAutomatic_output,
                    $formText_CanSendReminderNowMissingAddress_output,
                    $formText_DueDateNotExpired_output,
                    $formText_DoNotSend_output,
                    $formText_stoppedWithObjection_output,
                    $formText_notPayedConsiderCollectingProcessManual_output,
                    $formText_notPayedConsiderCollectingProcessAutomatic_output,
                    $formText_notPayedConsiderCollectingProcessMissingAddress_output,
                    $formText_CanSendReminderNowNotEnoughAmount_output,
                    $formText_notPayedConsiderCollectingProcessNotEnoughAmount_output,
                    $formText_OnlyFeesLeft_output);
                    ?>
                    <div style="padding: 5px 10px;">
                        <?php 
                        echo $creditor['companyname']; 
                        ?>
                    </div>
                    <?php
                    // $s_sql = "INSERT INTO creditor_transactions_status_log SET tab_status_from = ?, tab_status_to = ?, created = NOW(), creditor_transaction_id = ?, creditor_id = ?, source=?";
                    $perPage = 200;
                    $page = $_GET['page'] ?? 1;
                    $offset = ($page - 1) * $perPage;
                    $pager = " LIMIT ".$perPage." OFFSET ".$offset;

                    $s_sql = "SELECT ccic.*, cred.companyname, concat_ws(' ', cus.name, cus.middlename, cus.lastname) as debitorName FROM creditor_credit_info_call ccic
                    JOIN creditor cred ON cred.id = ccic.creditor_id
                    JOIN customer cus ON cus.id = ccic.customer_id
                    ORDER BY ccic.created DESC";
                    $o_query = $o_main->db->query($s_sql.$pager);
                    $logs = $o_query ? $o_query->result_array() : array();
                    $o_query = $o_main->db->query($s_sql);
                    $currentCount = $o_query ? $o_query->num_rows() : 0;
                    $totalPages = ceil($currentCount/$perPage);
                    ?>
                    <table class="table">
                        <tr>
                            <td><?php echo $formText_Date_output;?></td>
                            <td><?php echo $formText_Creditor_output;?></td>
                            <td><?php echo $formText_Debitor_output;?></td>
                            <td><?php echo $formText_Type_output;?></td>
                        </tr>
                        <?php foreach($logs as $log) { ?>
                            <tr>
                                <td><?php echo date("d.m.Y H:i:s", strtotime($log['created']))?></td>
                                <td><?php echo $log['companyname']?></td>
                                <td><?php echo $log['debitorName'];?></td>
                                <td><?php if($log['call_type'] == 1) { echo $formText_Extended_output;}else {echo $formText_Limited_output;}?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <?php if($totalPages > 1) {
                        $currentPage = $page;
                        $pages = array();
                        array_push($pages, 1);
                        if(!in_array($currentPage, $pages)){
                            array_push($pages, $currentPage);
                        }
                        if(!in_array($totalPages, $pages)){
                            array_push($pages, $totalPages);
                        }
                        for ($y = 10; $y <= $totalPages; $y+=10){
                            if(!in_array($y, $pages)){
                                array_push($pages, $y);
                            }
                        }
                        for($x = 1; $x <= 3;$x++){
                            $prevPage = $page - $x;
                            $nextPage = $page + $x;
                            if($prevPage > 0){
                                if(!in_array($prevPage, $pages)){
                                    array_push($pages, $prevPage);
                                }
                            }
                            if($nextPage <= $totalPages){
                                if(!in_array($nextPage, $pages)){
                                    array_push($pages, $nextPage);
                                }
                            }
                        }echo '<!--section-->';
                        asort($pages);
                        ?>
                        <?php foreach($pages as $page_single) {?>
                            <a href="#" data-page="<?php echo $page_single?>" class="page-link<?php if($page_single == $page) { echo ' active';} ?>"><?php echo $page_single;?></a>
                        <?php } ?>
                        <?php /*
                        <div class="showMoreCustomers"><?php echo $formText_Showing_LID16634;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_LID16635." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_LID16636;?></a> </div>*/?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<style>
.table-fixed {
    table-layout: fixed;
}
.table-fixed td {
    word-break: break-all;
}
.back-to-list {
    margin-left: 10px;
}
.creditor_label {
    display: inline-block;
    padding: 10px;
    font-weight: bold;
    margin-right: 10px;
}
.close_transaction {
    cursor: pointer;
    color: #46b2e2;
}
.creditor_wrapper {
    border: 1px solid #cecece;

    margin: 10px 10px 20px 10px;
}
.reset_transaction {
    cursor: pointer;
    color: #46b2e2;
    margin-bottom: 10px;
}
.close_all_duplicate_fees {
    cursor: pointer;
    color: #46b2e2;
}
.page-link.active {
    text-decoration: underline;
}
</style>
<script>
    $(function(){
            
        $(".page-link").on('click', function(e) {
            e.preventDefault();
            page = $(this).data("page");
            var data = {
                page: page
            };
            loadView('usage_info', data);
        });
    })
</script>