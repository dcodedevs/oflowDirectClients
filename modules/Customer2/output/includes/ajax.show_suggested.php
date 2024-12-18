<?php
$customerId = $_POST['cid'];


$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($customerId)."'";
$o_query = $o_main->db->query($s_sql);
$v_row = $o_query ? $o_query->row_array() : array();

$sql_where = " name LIKE '".$o_main->db->escape_str(mb_substr($v_row['name'], 0, 3))."%'";

$s_sql = "SELECT * FROM customer WHERE customer.content_status < 2 AND (".$sql_where.") AND id <> '".$o_main->db->escape_str($v_row['id'])."'";
$o_query = $o_main->db->query($s_sql);
$suggestedCustomers = $o_query ? $o_query->result_array() : array();
?>
<table class="table">
    <tr><th><?php echo $formText_CustomerName_output;?></th></tr>
<?php
foreach($suggestedCustomers as $suggestedCustomer) {
    ?>
    <tr>
        <td><?php echo $suggestedCustomer['name']." ".$suggestedCustomer['middlename']." ".$suggestedCustomer['lastname']; ?></td>
    </tr>
    <?php
}
?>
</table>
