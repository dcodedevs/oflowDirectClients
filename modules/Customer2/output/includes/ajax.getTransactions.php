<?php

$ownercompany_id = ($_POST['ownercompany_id']);
$customer_id = ($_POST['customer_id']);

// Ownercompany data
$s_sql = "SELECT * FROM ownercompany WHERE id = $ownercompany_id";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $ownercompany_data = $o_query->row_array(); 

// External customer id
$s_sql = "SELECT * FROM customer_externalsystem_id WHERE ownercompany_id = ? AND customer_id = ?";
$o_query = $o_main->db->query($s_sql, array($ownercompany_id, $customer_id));
if($o_query && $o_query->num_rows()>0) $external_customer = $o_query->row_array(); 

$external_customer_id = $external_customer['external_id'];
$external_customer_sys_id = $external_customer['external_sys_id'];

// This will be move to ajax file
$integration_name = $ownercompany_data['use_integration'];
$integration_file =  __DIR__ . '/../../../'. $integration_name .'/api/load.php';

if (file_exists($integration_file)) {
    require_once $integration_file;

    $api = new $integration_name(array(
        'ownercompany_id' => $ownercompany_id,
        'o_main' => $o_main
    ));

    $transaction_list = $api->get_transactions(array(
        'customerId' => $external_customer_id,
        'customerSysId' => $external_customer_sys_id,
        'account' => 1500
    ));

    if (count($transaction_list)): ?>
        <table class="table table-bordered table-striped">
            <tr>
                <th><?php echo $formText_Date_output; ?></th>
                <th><?php echo $formText_AccountNo_output; ?></th>
                <th><?php echo $formText_TransactionNr_output; ?></th>
                <th><?php echo $formText_InvoiceNr_output; ?></th>
                <th><?php echo $formText_Amount_output; ?></th>
            </tr>
            <?php foreach ($transaction_list as $transaction): ?>
                <tr>
                    <td><?php echo date('d.m.Y', strtotime($transaction['date'])); ?></td>
                    <td><?php echo $transaction['accountNr']; ?></td>
                    <td><?php echo $transaction['transactionNr']; ?></td>
                    <td><?php echo $transaction['invoiceNr']; ?></td>
                    <td><?php echo $transaction['amount']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

    <?php else: ?>
        <?php echo $formText_NoTransactions_output; ?>
    <?php endif; ?>

    <?php

}
else {
    if ($ownercompany_id) {
        echo 'Loading integration failed';
    }
}

?>
