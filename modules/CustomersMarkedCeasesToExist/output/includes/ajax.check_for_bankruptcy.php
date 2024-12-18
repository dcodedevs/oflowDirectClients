<?php
$fw_return_data = 0;
$v_fields = array(
	'name' => array('navn'=>''),
	'paStreet' => array('postadresse'=>'postadresse', 'forretningsadr'=>''),
	'paPostalNumber' => array('ppostnr'=>'postadresse', 'forradrpostnr'=>''),
	'paCity' => array('ppoststed'=>'postadresse', 'forradrpoststed'=>''),
	'paCountry' => array('ppostland'=>'postadresse', 'forradrland'=>''),
	'vaStreet' => array('forretningsadr'=>''),
	'vaPostalNumber' => array('forradrpostnr'=>''),
	'vaCity' => array('forradrpoststed'=>''),
	'vaCountry' => array('forradrland'=>'')
);
$v_labels = array(
	'name' => $formText_Name_Output,
	'paStreet' => $formText_PaStreet_Output,
	'paPostalNumber' => $formText_PaPostalNumber_Output,
	'paCity' => $formText_PaCity_Output,
	'paCountry' => $formText_PaCountry_Output,
	'vaStreet' => $formText_VaStreet_Output,
	'vaPostalNumber' => $formText_VaPostalNumber_Output,
	'vaCity' => $formText_VaCity_Output,
	'vaCountry' => $formText_VaCountry_Output,
);

// $ch = curl_init();
// curl_setopt($ch, CURLOPT_HEADER, 0);
// curl_setopt($ch, CURLOPT_VERBOSE, 0);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
// curl_setopt($ch, CURLOPT_POST, TRUE);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
// curl_setopt($ch, CURLOPT_URL, 'https://brreg.getynet.com/brreg.php');
// $v_post = array(
// 	'organisation_no' => array("987141662", "989868179"),
// 	'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
// 	'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
// );
// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
// $s_response = curl_exec($ch);

// $v_items = array();
// $v_response = json_decode($s_response, TRUE);
// var_dump($v_response);

// $s_sql = "SELECT c.publicRegisterId, c.id FROM customer c WHERE c.publicRegisterId <> '' AND IFNULL(c.updatedBy, '') <> 'fix script 2' AND c.publicRegisterId <> 0 LIMIT 2000";
// $o_query = $o_main->db->query($s_sql);
// $customers = ($o_query ? $o_query->result_array() : array());
// $updated_count = 0;
// $total_sql = "";
// foreach($customers as $customer){
// 	$regNr = preg_replace('/[^0-9]+/', '', $customer['publicRegisterId']);
// 	$s_sql_update = ",extra1='".$o_main->db->escape_str($customer['publicRegisterId'])."', publicRegisterId = '".$o_main->db->escape_str($regNr)."'";

// 	if($s_sql_update != "") {
// 		$total_sql .= "UPDATE customer SET
// 		updated = now(),
// 		updatedBy='fix script 2'".$s_sql_update."
// 		WHERE id = '".$o_main->db->escape_str($customer['id'])."';";
// 	}
// }
// // $o_query = $o_main->db->query($total_sql);
// // if($o_query){
// // 	$updated_count++;
// // }
// echo $total_sql;

$o_query = $o_main->db->query("SELECT creditor_id, debitor_id 
FROM collecting_company_cases 
WHERE IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'");
$v_active_collecting_company_cases = $o_query ? $o_query->result_array() : array();
$debitor_ids = array();
foreach($v_active_collecting_company_cases as $v_active_collecting_company_case){
	$debitor_ids[] = $v_active_collecting_company_case['debitor_id'];
}
if(count($debitor_ids) > 0){
	// $o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE IFNULL(publicRegisterId, '') <> '' AND IFNULL(updatedBy, '') <> 'cease check' GROUP BY publicRegisterId");
	// $v_customers = $o_query ? $o_query->result_array() : array();
	$o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE IFNULL(updatedBy, '') <> 'cease check' AND id IN (".implode(",", $debitor_ids).")");
	$v_customers = $o_query ? $o_query->result_array() : array();

	// $o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date FROM customer WHERE IFNULL(publicRegisterId, '') <> '' AND IFNULL(updatedBy, '') <> 'cease check' GROUP BY publicRegisterId");
	// $v_customers = $o_query ? $o_query->result_array() : array();
	$org_nrs = array();
	foreach($v_customers as $v_customer){
		if($v_customer['customer_marked_ceases_to_exist_date'] == '0000-00-00' || $v_customer['customer_marked_ceases_to_exist_date'] == ""){
			$org_nrs[] = trim($v_customer['publicRegisterId']);		
		}
	}
	$org_nrs = array_slice($org_nrs, 0, 5000);
	// $markedAsBankrupt = 0;
	$org_nrs_chunk = array_chunk($org_nrs, 500);
	// $customers_checked = 0;
	foreach($org_nrs_chunk as $org_nrs) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'https://brreg.getynet.com/brreg.php');
		$v_post = array(
			'organisation_no' => $org_nrs,
			'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
			'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
		);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
		$s_response = curl_exec($ch);

		$v_items = array();
		$v_response = json_decode($s_response, TRUE);
		$org_nr_update_array = array();
		if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
		{
			$v_items = $v_response['items'];
			foreach($v_items as $v_item) {
				if($v_item['konkurs'] == "J") {
					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Konkurs'";
					$markedAsBankrupt++;
				} else if($v_item['tvangsavvikling'] == "J") {
					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Tvangsavvikling'";
					$markedAsBankrupt++;
				} else if($v_item['avvikling'] == "J") {
					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = NOW(), customer_marked_ceases_to_exist_reason='Avvikling'";
					$markedAsBankrupt++;
				}
			}			
		}
		foreach($org_nrs as $org_nr) {
			$sql_update = $org_nr_update_array[$org_nr];			
			$o_query = $o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy='cease check'".$sql_update." WHERE publicRegisterId = '".$o_main->db->escape_like_str($org_nr)."'");
			if($o_query){
				$customers_checked++;
			}
		}
	}

	echo $markedAsBankrupt." customers marked<br/>";
	echo $customers_checked." customers checked";
}
if(isset($_POST['show_difference']) && $_POST['show_difference'] == 1)
{
	?>
	<div class="popupform">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-3"><?php echo $formText_Field_Output;?></div>
				<div class="col-xs-4"><?php echo $formText_CurrentValue_Output;?></div>
				<div class="col-xs-5"><?php echo $formText_BrregValue_Output;?></div>
			</div>
			
			<?php
			foreach($v_items as $s_field => $v_item)
			{
				?>
				<div class="row"<?php echo ($v_item[2]?' style="background-color: #f0faff;"':'');?>>
					<div class="col-xs-3"><?php echo $v_labels[$s_field];?></div>
					<div class="col-xs-4"><?php echo $v_item[0];?></div>
					<div class="col-xs-5"><?php echo $v_item[1];?></div>
				</div>
				<?php
			}
			?>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="button" class="output-never-sync-btn" value="<?php echo $formText_NeverSync_Output; ?>">
			<input type="button" class="output-sync-now-btn" value="<?php echo $formText_SyncNow_Output; ?>">
		</div>
	</div>
	<script type="text/javascript">
	$(function(){
		$('input.output-sync-now-btn').off('click').on('click', function(e){
			e.preventDefault();
			var data = {
				output_form_submit: 1,
				customer_id: '<?php echo $v_customer['id'];?>'
			};
			ajaxCall('brreg_check', data, function(json) {
				if(json.data == 1) $('#popupeditbox').addClass('close-reload');
				$('#popupeditboxcontent').html(json.html);
			});
		});
		$('input.output-never-sync-btn').off('click').on('click', function(e){
			e.preventDefault();
			var data = {
				never_sync: 1,
				output_form_submit: 1,
				customer_id: '<?php echo $v_customer['id'];?>'
			};
			ajaxCall('brreg_check', data, function(json) {
				if(json.data == 1) $('#popupeditbox').addClass('close-reload');
				$('#popupeditboxcontent').html(json.html);
			});
		});
	});
	</script>
	<?php
}