<?php


if(isset($_POST['detectDuplicate'])) {
	$possibleRepeatingOrders = array();

	$s_sql = "SELECT *, COUNT(id) as duplicatedCount FROM creditor_transactions WHERE system_type = 'InvoiceCustomer' AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%\_%'
	AND comment <>'Rente_8050' AND comment <>'Purregebyr_3900' AND comment <>'EU gebyr_3900' AND comment <>'Purregebyr inkassovarsel_3900' AND comment <>'Purregebyr_8050'
	AND comment <>'Rente_3900' AND comment <>'EU gebyr_8050' AND comment <>'Interest_8050' AND comment <>'Rente_8055' AND comment <>'Purregebyr_8070'
	AND comment <>'inkassovarsel_3900' AND comment <>'Purring_3900' AND comment <> 'Purregebyr_3090'
	AND comment <> 'Rente_8050_interest' AND comment <> 'Rente_8055_interest' AND comment <> 'Rente_8070_interest' AND comment <> 'Interest_8050_interest'  GROUP BY creditor_id, invoice_nr, comment having duplicatedCount > 1";
	$o_query = $o_main->db->query($s_sql);
	$duplicated_transactions = $o_query ? $o_query->result_array() : array();

	foreach($duplicated_transactions as $duplicated_transaction) {
		$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND link_id = ? AND creditor_id = ? AND collectingcase_id > 0 ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($duplicated_transaction['link_id'], $duplicated_transaction['creditor_id']));
		$invoice = ($o_query ? $o_query->row_array() : array());
		if($duplicated_transaction['open']){
			?>
			<div>
				<?php
				if($invoice){

					$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$invoice['collectingcase_id'];

					?>
					<a href="<?php echo $s_list_link;?>" target="_blank"><?php echo $invoice['collectingcase_id'];?> - <?php echo 'Creditor:'. $invoice['creditor_id']?> <?php echo $duplicated_transaction['open']?> <?php echo $duplicated_transaction['id']?></a>
					<?php
				} else {
					/*$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND invoice_nr = ? AND creditor_id = ? AND collectingcase_id > 0 ORDER BY created DESC";
					$o_query = $o_main->db->query($s_sql, array($duplicated_transaction['invoice_nr'], $duplicated_transaction['creditor_id']));
					$invoice = ($o_query ? $o_query->row_array() : array());
					if($invoice){
						$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$invoice['collectingcase_id'];

						?>
						by invoice nr <a href="<?php echo $s_list_link;?>" target="_blank"><?php echo $invoice['collectingcase_id'];?> - <?php echo 'Creditor:'. $invoice['creditor_id']?> <?php echo $duplicated_transaction['open']?> <?php echo $duplicated_transaction['id']?></a>
						<?php
					} else {
						echo 'invoice Not found '.$duplicated_transaction['id']. " ".$duplicated_transaction['open'];
					}*/
				}
				?>
			</div>
			<?php
		}
	}
}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">

			<input type="submit" name="detectDuplicate" value="Detect duplicate fees">

			<!-- <input type="submit" name="changeAssociation" value="Change Associations"> -->
		</div>
	</form>
</div>
