<?php
if(!function_exists("include_local")) include(__DIR__."/../../../input/includes/fn_include_local.php");

include_once(__DIR__."/readOutputLanguage.php");

$currentYear = date("Y");
$monthArray = array(
    1=>$formText_January_output,
    2=>$formText_February_output,
    3=>$formText_March_output,
    4=>$formText_April_output,
    5=>$formText_May_output,
    6=>$formText_June_output,
    7=>$formText_July_output,
    8=>$formText_August_output,
    9=>$formText_September_output,
    10=>$formText_October_output,
    11=>$formText_November_output,
    12=>$formText_December_output
);
$currentMonth = date("n");

$departmentActive = false;
$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE content_status < 2");
$departmentCount = $o_query ? $o_query->num_rows() : 0;
if($departmentCount > 0){
    $departmentActive = true;
}
?>
<?php

$o_query = $o_main->db->query("SELECT * FROM invoice ORDER BY id DESC LIMIT 10", array($monthStart,$monthEnd));
$invoices = $o_query ? $o_query->result_array() : array();

?>
<table class="table project-table-fixed">
    <tr>
        <th><?php echo $formText_SentDate_output;?></th>
        <th><?php echo $formText_InvoiceDate_output;?></th>
        <th width="110px"><?php echo $formText_Customer_output;?></th>
        <th><?php echo $formText_SentBy_output;?></th>
        <th class="numberedTd"><?php echo $formText_Sum_output;?></th>
    </tr>
    <?php

    foreach($invoices as $invoice) {
        $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
        $o_query = $o_main->db->query($s_sql, array($invoice['customerId']));
        $customer = ($o_query ? $o_query->row_array() : array());
        ?>
        <tr>
            <td><?php if($invoice['created'] != "" && $invoice['created'] != "0000-00-00") echo date("d.m.Y", strtotime($invoice['created']));?></td>
            <td><?php echo date("d.m.Y", strtotime($invoice['invoiceDate']));?></td>
            <td width="110px" class="project_info_name">
                <span class="short_name"><?php echo mb_substr($customer['name'], 0, 16);?></span>
                <span class="full_name"><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></span>
            </td>
            <td class="project_info_name forcebreak">
                <span class="short_name"><?php echo mb_substr($invoice['createdBy'], 0, 8);?></span>
                <span class="full_name"><?php echo $invoice['createdBy'];?></span>
            </td>
            <td class="numberedTd"><?php echo number_format($invoice['totalExTax'], 2, ",", "");?></td>
        </tr>
    <?php } ?>
</table>
