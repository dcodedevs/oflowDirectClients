<?php

if ($_POST['showFinish']) {
	// Get Batchinvoicing data
	$s_sql = "SELECT * FROM batch_invoicing_accountconfig";
	$o_query = $o_main->db->query($s_sql);
	$batchinvoicing_accountconfig = $o_query ? $o_query->row_array() : array();

	$s_sql = "SELECT id, log FROM batch_invoicing ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$v_batch_id = $o_query->row_array();
	}
	$log = $v_batch_id['log'];
	$v_batch_id = $v_batch_id['id'];
	$o_main->db->query("INSERT INTO batch_invoicing_log SET created = NOW(), log = ?, batch_invoicing_id = ?", array(" \nBatch ended - ".date("d.m.Y H:i:s"), $v_batch_id));

	// $o_main->db->query("UPDATE batch_invoicing SET status = 0, log = ? WHERE id = ?", array($log." \nBatch ended - ".date("d.m.Y H:i:s"), $v_batch_id));


	$invoicesCreated = array();
	$total_count = 0;
	$s_sql = "SELECT * FROM invoice WHERE batch_id = ?";
	$o_query = $o_main->db->query($s_sql, array($v_batch_id));
	if($o_query && $o_query->num_rows()>0){
		$total_count = $o_query->num_rows();
		$invoicesCreated = $o_query->result_array();
	}

	$sent_count = 0;
	$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND sentByEmail IS NOT NULL AND sentByEmail != ''";
	$o_query = $o_main->db->query($s_sql, array($v_batch_id));
	if($o_query && $o_query->num_rows()>0){
		$sent_count = $o_query->num_rows();
	}
	$sentandehf_count = 0;
	$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND for_sending = 1";
	$o_query = $o_main->db->query($s_sql, array($v_batch_id));
	if($o_query && $o_query->num_rows()>0){
		$sentandehf_count = $o_query->num_rows();
	}
	$print_count = $total_count-$sentandehf_count;

	$ehf_count = 0;
	$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND ehf_reference IS NOT NULL AND ehf_reference LIKE '%[REFERENCE]%' ESCAPE '!'";
	$o_query = $o_main->db->query($s_sql, array($v_batch_id));
	if($o_query && $o_query->num_rows()>0){
		$ehf_count = $o_query->num_rows();
	}


	/*
	$s_sql = "SELECT * FROM invoice WHERE batch_id = ? AND ehf_reference IS NOT NULL AND ehf_reference <> '' AND ehf_reference NOT LIKE '%[REFERENCE]%' ESCAPE '!'";
	$o_query = $o_main->db->query($s_sql, array($v_batch_id));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		?><div class="item-error">
			<div class="alert alert-danger"><?php echo $formText_FollowingInvoiceWasNotHandledByEhfService_output.': '.$v_row['external_invoice_nr']; ?></div>
		</div><?php
	}*/

	if(count($_SESSION['invoicing_errors'])): ?>
	<div class="item-error">
		<div class="alert alert-danger"><?php echo $formText_CustomerHasOrderErrors_output; ?>
			<ul style="margin:0; padding:0 15px;">
				<?php foreach($_SESSION['invoicing_errors'] as $orderErrorList): ?>
					<?php foreach($orderErrorList as $error):
						if($error['orderId'] == 0){
							?>
							<li><?php echo $error['errorMsg']; ?></li>
							<?php
						} else {
							$s_sql = "SELECT customer.* FROM orders
							LEFT OUTER JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
							LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
							WHERE orders.id = ?";
			                $o_query = $o_main->db->query($s_sql, array($error['orderId']));
			                $customerInfo = $o_query ? $o_query->row_array() : array();
						 ?>
							<li><?php echo $formText_Order_output; ?> #<?php echo $error['orderId']; ?>
								<?php echo $formText_ForCustomer_output;?>
								<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Customer2&folderfile=output&folder=output&inc_obj=details&cid=".$customerInfo['id']; ?>">
									<?php echo $customerInfo['name']." ".$customerInfo['middlename']." ".$customerInfo['lastname']?>
								</a> - <?php echo $error['errorMsg']; ?>
							</li>
						<?php } ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	<?php endif; ?>
	<?php

    /*if ($batchinvoicing_accountconfig['activate_syncing_of_customer_and_invoice']) {
		$notSyncedInvoices = array();
		foreach($invoicesCreated as $invoice){
			if($invoice['sync_status'] == 1 || $invoice['sync_status'] == 0){
				array_push($notSyncedInvoices, $invoice);
			}
		}
		if(count($notSyncedInvoices) > 0){
			?>
			<div class="item-error">
				<div class="alert alert-danger"><?php echo $formText_TheseInvoicesWereNotSyncedPleaseContactSystemDeveloper_output; ?>
					<ul style="margin:0; padding:0 15px;">
						<?php foreach($notSyncedInvoices as $notSyncedInvoice): ?>
							<li><?php echo $formText_Invoice_output; ?> #<?php echo $notSyncedInvoice['external_invoice_nr']; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
			<?php
		}
    }*/
	?>
	<div><?php echo $formText_TotalInvoicesWasCreated_Output.": ".$total_count;?></div>

	<?php
	if($print_count > 0)
	{
		echo $formText_PrintableFileWithInvoicesWasGenerated_Output; ?>. <a target="_blank" href="<?php echo $extradir."/output/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_batch_id ;?>"><?php echo $formText_DownloadInvoicesForPrint_Output;?></a><?php

		// echo $formText_PrintableInvoicesWillBeSentInEmail_Output.": ".$print_count; ?><?php
	}

	/*
	if($sent_count > 0)
	{
		?><div><?php echo $formText_TotalInvoicesWasSent_Output.": ".$sent_count;?></div><?php
	}
	if($ehf_count > 0)
	{
		?><div><?php echo $formText_TotalInvoicesWasHandledByEhfService_Output.": ".$ehf_count;?></div><?php
	}
	if($total_count > ($sent_count + $ehf_count))
	{
		echo $formText_PrintableFileWithInvoicesWasGenerated_Output; ?>. <a target="_blank" href="<?php echo $extradir."/output/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_batch_id ;?>"><?php echo $formText_DownloadInvoicesForPrint_Output;?></a><?php
	}*/
	$fw_return_data = $total_count;
	unset($_SESSION['invoicing_errors']);
}
else {
	$v_proc_variables['dontLaunch'] = false;
	$v_proc_variables['emergencyLock'] = false;

	// Check if invoicing is locked
	$locked_sql = "SELECT * FROM batch_invoicing WHERE emergency_lock = 1";
	$locked_query = $o_main->db->query($locked_sql);
	if($locked_query && $locked_query->num_rows()) {
		$v_proc_variables['emergencyLock'] = true;
	}

	if(isset($_POST["order_number"]))
	{
		// Module ID
		// $s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
		// $o_query = $o_main->db->query($s_sql);
		// if($o_query && $o_query->num_rows()>0){
		// 	$v_row = $o_query->row_array();
		// 	$l_orders_module_id = $v_row["uniqueID"];
		// }

		// Proc variables
		$v_proc_variables["order_number"] = $_POST["order_number"];
		// $v_proc_variables["module_id"] = $l_orders_module_id;
		$v_proc_variables["lines_total"] = 0;
		$v_proc_variables["lines_sent"] = 0;

		// Run proc line
		if($_POST['procrunlineID']) {
			$s_sql = "SELECT id FROM batch_invoicing ORDER BY id DESC";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0){
				$v_batch_id = $o_query->row_array();
			}

			$v_proc_variables['createProcLines'] = false;
			$v_proc_variables['procrunlineID'] = $_POST['procrunlineID'];
			$v_proc_variables['procrunID'] = $_POST['procrunID'];
			$v_proc_variables["batch_invoice_id"] = $v_batch_id['id'];
		}

		// Create lines and batch invoice id
		else {
			$s_sql = "SELECT id FROM batch_invoicing WHERE status = 1 ORDER BY id DESC";
			$o_query = $o_main->db->query($s_sql);
			$v_active_batch = $o_query ? $o_query->row_array() : array();

			if($v_active_batch){
				$v_proc_variables['dontLaunch'] = true;
			} else {
				unset($_SESSION['invoicing_errors']);
				// Batch ID
				$s_sql = "SELECT max(sortnr) sortnr, id FROM batch_invoicing";
				$o_query = $o_main->db->query($s_sql);
				if($o_query && $o_query->num_rows()>0){
					$v_sort_nr = $o_query->row_array();
				}
				$l_sort_nr = $v_sort_nr["sortnr"] + 1;
				$s_sql = "INSERT INTO batch_invoicing SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy= ?,
				sortnr= ?,
				status = 0,
				log = ?,
				server_url = ?";
				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $l_sort_nr, "Batch created - ".date("d.m.Y H:i:s"), $extradomaindirroot));
				$v_proc_variables["batch_invoice_id"] = $o_main->db->insert_id();

				$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
				$o_result = $o_main->db->query($s_sql);
				$module_data = $o_result ? $o_result->row_array() : array();

				$fwaFileuploadConfigs = array(
					array (
					  'module_folder' => 'BatchInvoicing', // module id in which this block is used
					  'id' => 'articleinsfileupload',
					  'upload_type' => 'file',
					  'content_table' => 'batch_invoicing',
					  'content_field' => 'attached_files',
					  'content_module_id' => $module_data['uniqueID'], // id of module
					  'dropZone' => 'block',
				      'file_type'=>'pdf',
					  'callbackAll' => 'callBackOnUploadAll',
					  'callbackStart' => 'callbackOnStart',
					  'callbackDelete' => 'callbackOnDelete'
					)
				);

				foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
					$fieldName = $fwaFileuploadConfig['id'];
					$fwaFileuploadConfig['content_id'] = $v_proc_variables["batch_invoice_id"];
					include( __DIR__ . "/includes/fileupload_popup/contentreg.php");
				}

				// Create lines flag
				$v_proc_variables['createProcLines'] = true;
			}
		}

		include(__DIR__."/../procedure_create_invoices/run.php");

	}
}
