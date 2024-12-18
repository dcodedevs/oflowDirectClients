<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
        $hook_file = __DIR__ . '/../../../hooks/sync_customer.php';
        if (file_exists($hook_file)) {
            require_once $hook_file;
            if (is_callable($run_hook)) {
				$s_sql = "SELECT customer_externalsystem_id.* FROM customer
				JOIN customer_externalsystem_id ON customer_externalsystem_id.customer_id = customer.id
				WHERE (customer.content_status = 0 OR customer.content_status is null)";
				$o_query = $o_main->db->query($s_sql);
				$customers = $o_query ? $o_query->result_array() : array();

				foreach($customers as $customer){
					if($customer['customer_id'] > 0 && $customer['ownercompany_id'] > 0) {
						$hook_params = array(
				            'customer_id' => $customer['customer_id'],
				            'ownercompany_id' => $customer['ownercompany_id']
				        );
		                $hook_result = $run_hook($hook_params);
					}
				}
            }
			unset($run_hook);
        }
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">

			<input type="submit" name="migrateData" value="Sync all customers to Tripletex">

		</div>
	</form>
</div>
