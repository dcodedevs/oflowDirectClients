<?php
$customerCode = $_POST['customerCode'];
$externalOwnercompanyCode = $_POST['externalOwnercompanyCode'];

if ($customerCode) {
    $integration = 'IntegrationXledger';
    $integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
    if (file_exists($integration_file)) {
        require_once $integration_file;
        if (class_exists($integration)) {
            if ($xledger_api) unset($xledger_api);
            $xledger_api = new $integration(array(
                'o_main' => $o_main
            ));
        }
    }

    $transactions = $xledger_api->get_customer_transactions(array(
        'subledgerCode' => $customerCode,
        'entityCode' => $externalOwnercompanyCode
    ));

    $transactions = array_slice(array_reverse($transactions), 0, 20);

}
?>
<div class="popupform" style="border:none;">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th><?php echo $formText_InvoiceNo_output; ?></th>
                <th><?php echo $formText_InvoiceDate_output; ?></th>
                <th><?php echo $formText_DueDate_output; ?></th>
                <th><?php echo $formText_InvoiceAmount_output; ?></th>
                <th><?php echo $formText_Amount_output; ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($transactions as $transaction): ?>
                <tr>
                    <td><?php echo $transaction['invoiceNo']; ?></td>
                    <td><?php echo date('d.m.Y', strtotime($transaction['invoiceDate'])); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($transaction['dueDate'])); ?></td>
                    <td><?php echo $transaction['invoiceAmount']; ?></td>
                    <td><?php echo $transaction['amount']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
