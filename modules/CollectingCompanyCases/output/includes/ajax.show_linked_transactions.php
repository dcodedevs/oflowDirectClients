<?php
$link_id = isset($_POST['link_id']) ? $_POST['link_id'] : '';
$creditor_id = isset($_POST['creditor_id']) ? $_POST['creditor_id'] : '';

if($link_id != ""){
	$s_sql = "SELECT creditor.* FROM creditor WHERE creditor.id = ?";
	$o_query = $o_main->db->query($s_sql, array($creditor_id));
	$creditor = ($o_query ? $o_query->row_array() : array());
	if($creditor){
		require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
		$api = new Integration24SevenOffice(array(
			'ownercompany_id' => 1,
			'identityId' => $creditor['entity_id'],
			'creditorId' => $creditor['id'],
			'o_main' => $o_main
		));

		?>
		<table class="claimsTable table table-borderless">
			<tr>
				<th width="20%"><?php echo $formText_SystemType_Output; ?></th>
				<th width="10%"><?php echo $formText_Date_Output; ?></th>
				<th width="10%"><?php echo $formText_LastChangedDate_Output; ?></th>
				<th width="10%">
					<?php echo $formText_Account_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_TransactionNo_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_InvoiceNr_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_Amount_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_DueDate_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_LinkId_Output;?>
				</th>
				<th width="10%">
					<?php echo $formText_Status_Output;?>
				</th>
			</tr>
			<?php
            $s_sql = "SELECT * FROM creditor_transactions WHERE link_id = '".$o_main->db->escape_str($link_id)."' AND creditor_id = '".$o_main->db->escape_str($creditor_id)."' ORDER BY created DESC";
            $o_query = $o_main->db->query($s_sql);
            $linked_transactions = ($o_query ? $o_query->result_array() : array());
            foreach($linked_transactions as $transaction){
                ?>
                <tr>
                    <td><?php echo $transaction['system_type'];?></td>
                    <td><?php echo date("d.m.Y", strtotime($transaction['date'])) ;?></td>
                    <td><?php echo date("d.m.Y H:i:s", strtotime($transaction['date_changed']));?></td>
                    <td><?php echo $transaction['accountNo'];?></td>
                    <td><?php echo $transaction['transaction_nr'];?></td>
                    <td><?php echo $transaction['invoice_no'];?></td>
                    <td><?php echo $transaction['amount'];?></td>
                    <td><?php if($transaction['due_date'] != "") echo date("d.m.Y", strtotime($transaction['due_date']));?></td>
                    <td><?php echo $transaction['link_id'];?></td>
                    <td><?php if($transaction['open']){ echo $formText_Open_output; } else { echo $formText_Closed_output; } ?></td>
                </tr>
                <?php
            }
			?>
		</table>
		<?php
	}
}

?>
