<?php
$show_closed = isset($_GET['show_closed']) ? $_GET['show_closed'] : 0;
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$v_creditor['id'];
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_pagePreDetail">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>

                    <select class="showClosedDuplicateFees" autocomplete="off">
                        <option value=""><?php echo $formText_ShowOpen_output;?></option>
                        <option value="1" <?php if($show_closed) echo 'selected';?>><?php echo $formText_ShowClosed_output;?></option>
                    </select>
                </div>
                <div class="">
                    <?php
                    $o_query = $o_main->db->query("SELECT id, COUNT(id), comment, creditor_id
                    FROM creditor_transactions WHERE creditor_transactions.comment <> '' AND LENGTH(creditor_transactions.comment) = 36 AND creditor_transactions.comment LIKE '%-%'
                    GROUP BY creditor_transactions.comment HAVING COUNT(id) > 1 LIMIT 4000");
                    $v_duplicated_fees = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
                    $grouped_by_creditor_fees = array();
                    foreach($v_duplicated_fees as $v_duplicated_fee) {
                        $grouped_by_creditor_fees[$v_duplicated_fee['creditor_id']][] = $v_duplicated_fee;
                    }
                    foreach($grouped_by_creditor_fees as $creditor_id=>$v_duplicated_fees) {
                        $correct_duplicated_fees = array();
                        foreach($v_duplicated_fees as $v_duplicated_fee) {
                            $o_query = $o_main->db->query("SELECT *
                            FROM creditor_transactions WHERE creditor_transactions.transaction_id = ? AND creditor_transactions.creditor_id = ?", array($v_duplicated_fee['comment'], $v_duplicated_fee['creditor_id']));
                            $parent_transactions = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
                            if($parent_transactions) {
                                if($show_closed){
                                    $o_query = $o_main->db->query("SELECT ct.* FROM creditor_transactions ct
                                    LEFT OUTER JOIN creditor_transactions ct2 ON ct2.transaction_id = ct.comment AND ct2.link_id = ct.link_id
                                    WHERE ct.comment = ? AND ct.creditor_id = ? AND ct2.id is null AND IFNULL(ct.open, 0) = 0",
                                    array($v_duplicated_fee['comment'], $v_duplicated_fee['creditor_id']));
                                    $v_open_duplicates = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
                                    if(count($v_open_duplicates) > 0) {
                                        $correct_duplicated_fees = array_merge($correct_duplicated_fees, $v_open_duplicates);
                                    }

                                } else {
                                    $o_query = $o_main->db->query("SELECT *
                                    FROM creditor_transactions WHERE creditor_transactions.comment = ? AND creditor_transactions.creditor_id = ? AND open = 1", array($v_duplicated_fee['comment'], $v_duplicated_fee['creditor_id']));
                                    $v_open_duplicates = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
                                    if(count($v_open_duplicates) > 0) {
                                        $correct_duplicated_fees = array_merge($correct_duplicated_fees, $v_open_duplicates);
                                    }
                                }
                            }
                        }
                        if(count($correct_duplicated_fees) > 0) {
                            $o_query = $o_main->db->query("SELECT * FROM creditor WHERE id = ?", array($creditor_id));
                            $v_creditor_data = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();
                            ?>
                            <div class='creditor_wrapper'>
                                <span class="creditor_label">
                                    <?php
                                    echo $v_creditor_data['companyname']. " (".count($correct_duplicated_fees)." ".$formText_Transactions_output.")";
                                    ?>
                                </span>
                                <span class="close_all_duplicate_fees" data-creditor-id="<?php echo $v_creditor_data['id'];?>">
                                    <?php echo $formText_ResetAllDuplicateFees_output;?>
                                    <?php
                                    $show_trans = 0;
                                    foreach($correct_duplicated_fees as $correct_duplicated_fee) {
                                        $show_trans++;
                                        if($show_trans <= 50){
                                        ?>
                                        <input type="hidden" class="transaction_ids" name="transactions_ids" value="<?php echo $correct_duplicated_fee['id']?>"/>
                                    <?php }
                                    } ?>
                                </span>
                                <table class="table table-fixed">
                                    <tr>
                                        <td width="70px"><?php echo $formText_collecting_output; ?></td>
                                        <td><?php echo $formText_transactionId_output;?></td>
                                        <td><?php echo $formText_Comment_output;?></td>
                                        <td><?php echo $formText_Link_output;?></td>
                                        <td width="90px"><?php echo $formText_Date_output;?></td>
                                        <td width="70px"><?php echo $formText_InvoiceNr_output;?></td>
                                        <td><?php echo $formText_Type_output;?></td>
                                        <td width="70px"><?php echo $formText_Amount_output;?></td>
                                        <td width="70px"><?php echo $formText_Status_output;?></td>
                                    </tr>
                                    <?php
                                        foreach($correct_duplicated_fees as $correct_duplicated_fee) {
                                            $o_query = $o_main->db->query("SELECT cc.* FROM creditor_transactions ct
                                            JOIN collecting_cases cc ON cc.id = ct.collectingcase_id WHERE ct.invoice_nr = ? AND ct.creditor_id = ?", array($correct_duplicated_fee['invoice_nr'], $correct_duplicated_fee['creditor_id']));
                                            $v_case_data = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();

                                            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_case_data['id'];

                                            ?>
                                            <tr>
                                                <td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $v_case_data['id'];?></a></td>
                                                <td><?php echo $correct_duplicated_fee['transaction_id']?></td>
                                                <td><?php echo $correct_duplicated_fee['comment']?></td>
                                                <td><?php echo $correct_duplicated_fee['link_id']?></td>
                                                <td><?php echo $correct_duplicated_fee['date']?></td>
                                                <td><?php echo $correct_duplicated_fee['invoice_nr']?></td>
                                                <td><?php echo $correct_duplicated_fee['system_type']?></td>
                                                <td><?php echo $correct_duplicated_fee['amount']?></td>
                                                <td>
                                                    <?php if($correct_duplicated_fee['open']){ echo $formText_Open_output; } else { echo $formText_Close_output;}?>
                                                    <br/>
                                                    <div class="reset_transaction" data-transaction-id="<?php echo $correct_duplicated_fee['id']?>"><?php echo $formText_ResetTransaction_output?></div>
                                                    
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    ?>
                                </table>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
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
<script type="text/javascript">
    $(function(){
        $(".close_transaction").off("click").on("click", function(){

        })
        $(".showClosedDuplicateFees").off("change").on("change", function(){
            var data = {
                show_closed: $(this).val()
            }
            loadView("duplicated_fee_reset_list", data);
        })
        $(".reset_transaction").off("click").on("click", function(e){
            e.preventDefault();
            var data = {
                transaction_id: $(this).data("transaction-id")
            };
            ajaxCall('reset_transaction', data, function(json) {
                var data2 = {
                    show_closed: $(".showClosedDuplicateFees").val()
                };
                loadView("duplicated_fee_reset_list", data2);
            });
        })
        $(".close_all_duplicate_fees").off("click").on("click", function(e){
            e.preventDefault();
            var data = {
                transaction_ids: $(this).find(".transaction_ids").serializeArray(),
                creditor_id: $(this).data("creditor-id")
            };
            ajaxCall('reset_transaction_all', data, function(json) {
                var data2 = {
                    show_closed: $(".showClosedDuplicateFees").val()
                };
                loadView("duplicated_fee_reset_list", data2);
            });
        })
    })
</script>
