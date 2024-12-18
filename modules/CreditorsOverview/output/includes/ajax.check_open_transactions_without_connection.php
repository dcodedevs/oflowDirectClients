<?php 

$s_sql = "SELECT ct.*, creditor.companyname  FROM creditor_transactions ct 
LEFT JOIN creditor_transactions ct2 ON ct2.link_id = ct.link_id AND ct2.collectingcase_id > 0
JOIN creditor ON creditor.id = ct.creditor_id
WHERE ct.open = 1 AND (ct.system_type='InvoiceCustomer') AND ct2.id IS NULL 
AND (ct.collectingcase_id is null OR ct.collectingcase_id = 0) AND (ct.comment LIKE '%reminderFee_%' OR ct.comment LIKE '%interest_%')
GROUP BY ct.creditor_id";
$o_query = $o_main->db->query($s_sql);
$creditors_with_open_fees = $o_query ? $o_query->result_array() : array();

?>

<table class="table">
    <tr><td><b><?php echo $formText_Creditor_output;?></b></td><td></td></tr>
<?php
foreach($creditors_with_open_fees as $creditor_with_open_fees){
    $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CreditorsOverview&folderfile=output&folder=output&inc_obj=show_fees_without_connection&cid=".$creditor_with_open_fees['creditor_id'];
    ?>
    <tr>
        <td><?php echo $creditor_with_open_fees['companyname']?></td>
        <td><a href="<?php echo $s_edit_link;?>" target="_blank"><?php echo $formText_ShowTransactions_output;?></a></td>
    </tr>
    <?php
}
?>
</table>