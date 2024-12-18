<?php
$s_sql = "SELECT creditor_syncing.*, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as creditorName FROM creditor_syncing
LEFT OUTER JOIN creditor ON creditor.id = creditor_syncing.creditor_id
LEFT OUTER JOIN customer ON customer.id = creditor.customer_id
WHERE creditor_syncing.id = ? AND creditor_syncing.content_status < 2 GROUP BY creditor_syncing.id ORDER BY  started DESC ";
$o_query = $o_main->db->query($s_sql, array($_GET['cid']));
$creditor_syncing = ($o_query ? $o_query->row_array() : array());
$log_array = array($formText_Main_output, $formText_ReminderTransaction_output);
$transaction_log = $_GET['transaction_log'];

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&transaction_log=".$transaction_log;
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>
				<table class="table">
					<thead>
						<tr><td><?php echo $formText_Time_output?></td><td><?php echo $formText_Log_output?></td><td><?php echo $formText_Type_output?></td><td><?php echo $formText_NumberOfRetries_output?></td><td><?php echo $formText_NumberOfTransactions_output?></td><td><?php echo $formText_NumberOfRestclaims_output?></td></tr>
					</thead>
					<tbody>
						<?php
						$s_sql = "SELECT creditor_syncing_log.* FROM creditor_syncing_log
						WHERE creditor_syncing_log.creditor_syncing_id = ? ORDER BY created ASC, id ASC";
						$o_query = $o_main->db->query($s_sql, array($creditor_syncing['id']));
						$creditor_syncing_logs = ($o_query ? $o_query->result_array() : array());

						foreach($creditor_syncing_logs as $log) {
							?>
							<tr><td><?php echo $log['created']?></td><td><?php echo $log['log']?></td><td><?php echo $log_array[intval($log['type'])]?></td><td><?php echo $log['number_of_tries']?></td><td><?php echo $log['number_of_transactions']?></td><td><?php echo $log['number_of_restclaims']?></td></tr>
							<?php
						}?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
