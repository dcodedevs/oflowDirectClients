<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		if($_POST['orderId'] != ""){
			// Load integration
	        require_once __DIR__ . '/../../../internal_api/load.php';
	        $api = new Integration24SevenOffice(array(
	            'o_main' => $o_main,
	            'ownercompany_id' => 1
	        ));

			$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ? ";
			$o_query = $o_main->db->query($s_sql, array($_POST['orderId']));
			$collectingOrder = $o_query ? $o_query->row_array() : array();
			if($collectingOrder){
				$sql = "SELECT c.*,
		        cei.external_sys_id external_sys_id,
		        cei.external_id external_id
		        FROM customer c
		        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = ?
		        WHERE c.id = ?";
		        $o_query = $o_main->db->query($sql, array(1, $collectingOrder['customerId']));
		        $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
				if($customer_data){
					$customer_result['id'] = $customer_data['external_id'];
					$external_order_id = $collectingOrder['external_sys_id'];
					// Get orderlines
	                $sql = "SELECT o.*
	                FROM orders o
	                LEFT JOIN customer_collectingorder cco ON cco.id = o.collectingorderId
	                WHERE cco.id = ?";
	                $o_query = $o_main->db->query($sql, array($collectingOrder['id']));
	                $order_lines = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

					$order_lines_processed = array();

					foreach ($order_lines as $order) {

						$sql = "SELECT * FROM article WHERE id = ?";
						$o_query = $o_main->db->query($sql, array($order['articleNumber']));
						$article = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

						array_push($order_lines_processed, array(
							'description' => $order['articleName'],
							'articleNumber' => $order['articleNumber'],
							'external_product_id' => $article['external_sys_id'],
							'accountNumber' => $order['bookaccountNr'],
							'amount' => $order['pricePerPiece'],
							'count' => $order['amount'],
							'vatCode' => $order['vatCode'],
							'external_sys_id' => $order['external_sys_id'],
							'discount' => $order['discountPercent'] ? $order['discountPercent'] : 0
						));
					}

					$update_result = $api->update_order(array(
						'orderId' => $external_order_id,
						'customerCode' => $customer_result['id'],
						'lines' => $order_lines_processed,
						'departmentCode' =>$collectingOrder['department_for_accounting_code'],
						'projectCode' =>$collectingOrder['accountingProjectCode']
					));
					var_dump(array(
						'orderId' => $external_order_id,
						'customerCode' => $customer_result['id'],
						'lines' => $order_lines_processed,
						'departmentCode' =>$collectingOrder['department_for_accounting_code'],
						'projectCode' =>$collectingOrder['accountingProjectCode']
					), $update_result);
				}
			}

		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<?php echo $formText_OrderId_output;?>
			<input type="text" name="orderId" value=""/><br/>

			<input type="submit" name="migrateData" value="Update Order">

		</div>
	</form>
</div>
