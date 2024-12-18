<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		if($_POST['customerId'] != ""){
	        $hook_file = __DIR__ . '/../../../hooks/sync_customer.php';
	        if (file_exists($hook_file)) {
	            require_once $hook_file;
	            if (is_callable($run_hook)) {
					$hook_params = array(
						'customer_id' => $_POST['customerId']
					);
					$hook_result = $run_hook($hook_params);
					var_dump($hook_result);
	            }
				unset($run_hook);
	        }
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<?php echo $formText_CustomerId_output;?>
			<input type="text" name="customerId" required value=""/>

			<input type="submit" name="migrateData" value="Sync customer">

		</div>
	</form>
</div>
