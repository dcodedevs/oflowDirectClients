<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		$sql = "SELECT cei.*, COUNT(cei.external_sys_id) as duplicates, c.name FROM `customer_externalsystem_id` cei LEFT OUTER JOIN customer c ON c.id = cei.customer_id WHERE cei.external_sys_id is not null AND cei.external_sys_id > 0 GROUP BY cei.external_sys_id, cei.customer_id HAVING duplicates > 1";
		$o_query = $o_main->db->query($sql);
		$duplicatesExternalIds = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
		foreach($ownercompany_list as $ownercompany){
			$ownercompany_id = $ownercompany['id'];
		    // Load integration
		    require_once __DIR__ . '/../../../internal_api/load.php';
		    $api = new IntegrationTripletex(array(
		        'o_main' => $o_main,
		        'ownercompany_id' => $ownercompany_id
		    ));
			foreach($duplicatesExternalIds as $duplicatesExternalId){
				$sql = "SELECT cei.* FROM `customer_externalsystem_id` cei WHERE cei.external_sys_id = ? AND cei.customer_id = ? AND cei.ownercompany_id = ?";
				$o_query = $o_main->db->query($sql, array($duplicatesExternalId['external_sys_id'], $duplicatesExternalId['customer_id'], $ownercompany_id));
				$customer_externalsystem = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
				if($customer_externalsystem){
					$customerNumber = $customer_externalsystem['external_id'];
				    // Return object
				    $return = array();
				    $customers = $api->get_customer_list(array("count" => 1, 'isInactive' => false, 'fields'=> '*,postalAddress(*)', 'customerNumber'=>$customerNumber));
					$customer = $customers[0];
					if($customer['customerNumber'] == $customerNumber){
				        // Save externalsystem id and number
				        $o_query = $o_main->db->query('UPDATE customer_externalsystem_id SET updated = ?, updatedBy = ?, external_sys_id = ? WHERE id = ?',
				         array(date('Y-m-d H:i:s'), $variables->loggID, $customer['id'], $customer_externalsystem['id']));
						 if($o_query){
							 $updatedElement++;
						 }
					}
				}
			}
		}
	}
	$sql = "SELECT cei.*, COUNT(cei.external_sys_id) as duplicates, c.name FROM `customer_externalsystem_id` cei LEFT OUTER JOIN customer c ON c.id = cei.customer_id WHERE cei.external_sys_id is not null AND cei.external_sys_id > 0 GROUP BY cei.external_sys_id, cei.customer_id HAVING duplicates > 1";
	$o_query = $o_main->db->query($sql);
	$duplicatesExternalIds = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<?php
			if(count($duplicatesExternalIds) > 0){
				echo $formText_DulicatedExternalSysIdsFound_input." ".count($duplicatesExternalIds)." entries<br/>";
				foreach($duplicatesExternalIds as $duplicatesExternalId) {
					echo $duplicatesExternalId['customer_id']." ".$duplicatesExternalId['name']."<br/>";
				}
			}
			?>
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="migrateData" value="Fix customer external sys id">
		</div>
	</form>
</div>
