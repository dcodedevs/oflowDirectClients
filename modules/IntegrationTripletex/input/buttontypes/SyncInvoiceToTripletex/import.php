<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		if($_POST['invoice_id'] > 0){
		    $o_query = $o_main->db->query('SELECT * FROM invoice WHERE id = ?', array($_POST['invoice_id']));
		    $invoice_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
			if($invoice_data){
		        $hook_file = __DIR__ . '/../../../hooks/sync_customer_and_invoice.php';
		        if (file_exists($hook_file)) {
		            require_once $hook_file;
		            if (is_callable($run_hook)) {
						$hook_params = array(
							'invoice_id'=>$_POST['invoice_id']
						);
						$hook_result = $run_hook($hook_params);
						var_dump($hook_result);
		            }
					unset($run_hook);
		        }
			} else {
				echo $formText_CanNotFindInvoiceWithId_output." ".$_POST['invoice_id'];
			}
		} else {
			echo $formText_MissingFields_output;
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<label>Invoice Id Not Invoice Number</label>
			<input type="text" name="invoice_id" value="<?php echo $_POST['invoice_id']?>" required/>
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="migrateData" value="Sync invoice">
		</div>
	</form>
</div>
