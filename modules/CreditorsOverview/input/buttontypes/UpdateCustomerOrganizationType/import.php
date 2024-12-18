<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['checkCreditorNames'])) {
		$sql = "SELECT * FROM customer WHERE IFNULL(organization_type, '') = '' AND IFNULL(organization_type_checked, 0) = 0 AND IFNULL(publicRegisterId, '') <> '' LIMIT 300";
		$o_query = $o_main->db->query($sql);
		$customers = $o_query ? $o_query->result_array() : array();
		$organization_numbers = array();
		foreach($customers as $customer) {
			$organization_numbers[$customer['publicRegisterId']] = $customer['publicRegisterId'];
		}
		if(count($organization_numbers) > 0) {
			$only_organization_numbers = array();
			foreach($organization_numbers as $organization_number){
				$only_organization_numbers[] = $organization_number;
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
			$v_post = array(
				'organisation_no' => $only_organization_numbers,
				'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
				'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
			);

			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
			$s_response = curl_exec($ch);
			
			$v_items = array();
			$v_response = json_decode($s_response, TRUE);
			if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
			{
				foreach($v_response['items'] as $v_item) {
					$s_person_sql = "";
					if(mb_strtolower($v_item['organisasjonsform']) == mb_strtolower("ENK")){
						// $s_person_sql = ", customer_type_for_collecting_cases = 2";
					}
					$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type = ?".$s_person_sql." WHERE publicRegisterId = ?";
					$o_query = $o_main->db->query($sql, array($v_item['organisasjonsform'], $v_item['orgnr']));					
				}
				foreach($organization_numbers as $organization_number) {
					$sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type_checked = 1 WHERE publicRegisterId = ?";
					$o_query = $o_main->db->query($sql, array($organization_number));		
				}
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="checkCreditorNames" value="Update organization types">
		</div>
	</form>
</div>
