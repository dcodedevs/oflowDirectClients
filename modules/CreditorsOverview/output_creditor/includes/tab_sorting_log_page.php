<?php
$show_closed = isset($_GET['show_closed']) ? $_GET['show_closed'] : 0;
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$v_creditor['id'];
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;
$creditor_id = isset($_GET['creditor_id']) ? $_GET['creditor_id'] : 0;

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){
    $s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=tab_sorting_log_page&list_filter=".$list_filter."&search_filter=".$search_filter;

}
?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_pagePreDetail">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>
                </div>
                <?php if($creditor){ 
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
                        <a href="#" class="reset_logs"><?php echo $formText_ResetLogs_output;?></a>
                    </div>
                    <?php
                    // $s_sql = "INSERT INTO creditor_transactions_status_log SET tab_status_from = ?, tab_status_to = ?, created = NOW(), creditor_transaction_id = ?, creditor_id = ?, source=?";
                
                    $s_sql = "SELECT ctsl.*, cred.companyname, ct.invoice_nr FROM creditor_transactions_status_log ctsl
                    JOIN creditor cred ON cred.id = ctsl.creditor_id
                    JOIN creditor_transactions ct ON ct.id = ctsl.creditor_transaction_id
                    WHERE ctsl.source = 1 AND ctsl.creditor_id = ? ORDER BY ctsl.created DESC LIMIT 1000";
                    $o_query = $o_main->db->query($s_sql, array($creditor['id']));
                    $logs = $o_query ? $o_query->result_array() : array();
                    ?>
                    <table class="table">
                        <tr>
                            <td><?php echo $formText_Date_output;?></td>
                            <td><?php echo $formText_Creditor_output;?></td>
                            <td><?php echo $formText_TransactionId_output;?></td>
                            <td><?php echo $formText_CustomerName_output;?></td>
                            <td><?php echo $formText_InvoiceNr_output;?></td>
                            <td><?php echo $formText_StatusFrom_output;?></td>
                            <td><?php echo $formText_StatusTo_output;?></td>
                        </tr>
                        <?php foreach($logs as $log) { ?>
                            <tr>
                                <td><?php echo $log['created']?></td>
                                <td><?php echo $log['companyname']?></td>
                                <td><?php echo $log['creditor_transaction_id'];?></td>
                                <td><?php echo $log['creditor_transaction_id'];?></td>
                                <td><?php echo $log['invoice_nr'];?></td>
                                <td><?php echo $log['tab_status_from']. " - ".$tab_statuses[$log['tab_status_from']]?></td>
                                <td><?php echo $log['tab_status_to']. " - ". $tab_statuses[$log['tab_status_to']]?></td>
                            </tr>
                        <?php } ?>
                    </table>
                    <script type="text/javascript">
                        $(function(){
                            $(".reset_logs").off("click").on("click", function(e){
                                e.preventDefault();
                                bootbox.confirm('<?php echo $formText_ResetLogs_output; ?>?', function(result) {
                                    if (result) {
                                        var data = {
                                            creditor_id: '<?php echo $creditor_id?>'
                                        }
                                        ajaxCall('reset_logs', data, function(json) {   
                                            var data2 = {

                                            }                                     
                                            loadView("tab_sorting_log_page", data2);
                                        });
                                    }
                                });
                            })
                        })
                    </script>
                <?php } else { ?>
                    <div class="">
                        <div style="padding: 5px 10px;">
                            <?php 
                            echo $formText_CreditorsWithLoggedChanges_output; 
                            ?>
                            <a href="#" class="reset_all_logs"><?php echo $formText_ResetAllLogs_outpup;?></a>
                
                        </div>
                        <?php
                        // $s_sql = "INSERT INTO creditor_transactions_status_log SET tab_status_from = ?, tab_status_to = ?, created = NOW(), creditor_transaction_id = ?, creditor_id = ?, source=?";
                    
                        $s_sql = "SELECT ctsl.*, cred.companyname, count(ctsl.id) as logAmount, MAX(ctsl.created) as created FROM creditor_transactions_status_log ctsl
                        JOIN creditor cred ON cred.id = ctsl.creditor_id
                        WHERE ctsl.source = 1 GROUP BY ctsl.creditor_id ORDER BY ctsl.created DESC LIMIT 1000";
                        $o_query = $o_main->db->query($s_sql);
                        $logs = $o_query ? $o_query->result_array() : array();
                        ?>
                        <table class="table">
                            <tr>
                                <td><?php echo $formText_Date_output;?></td>
                                <td><?php echo $formText_Creditor_output;?></td>
                                <td><?php echo $formText_Amount_output;?></td>
                            </tr>
                            <?php foreach($logs as $log) {
                                $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_creditor&inc_obj=tab_sorting_log_page&creditor_id=".$log['creditor_id'];
                                ?>
                                <tr>
                                    <td><a href="<?php echo $s_edit_link;?>"><?php echo $log['created']?></a></td>
                                    <td><a href="<?php echo $s_edit_link;?>"><?php echo $log['companyname']?></a></td>
                                    <td><a href="<?php echo $s_edit_link;?>"><?php echo $log['logAmount']?></a></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
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
</style>
<script>
    $(function(){
        $(".reset_all_logs").off("click").on("click", function(e){
            e.preventDefault();
            var self = $(this);
            var data = {
            };
            bootbox.confirm('<?php echo $formText_ConfirmLogReset_output; ?>', function(result) {
                if (result) {
                    ajaxCall('reset_all_logs', data, function(json) {
                        loadView("tab_sorting_log_page", {});
                    });
                }
            });
        })
    })
</script>