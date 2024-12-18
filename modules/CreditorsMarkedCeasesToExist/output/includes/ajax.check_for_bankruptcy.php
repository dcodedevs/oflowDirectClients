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

$o_query = $o_main->db->query("SELECT * FROM creditor WHERE companyorgnr <> ''");
if($o_query && $o_query->num_rows()>0)
{
	$v_creditors = $o_query->result_array();
	$org_nrs = array();
	$markedAsBankrupt = 0;
    foreach($v_creditors as $v_creditor) {	   
		if($v_creditor['creditor_marked_ceases_to_exist_date'] == "0000-00-00" || $v_creditor['creditor_marked_ceases_to_exist_date'] == ""){
			$org_nrs[] = $v_creditor['companyorgnr'];
		}
		// $ch = curl_init();
		// curl_setopt($ch, CURLOPT_HEADER, 0);
		// curl_setopt($ch, CURLOPT_VERBOSE, 0);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		// curl_setopt($ch, CURLOPT_POST, TRUE);
		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// curl_setopt($ch, CURLOPT_URL, 'https://brreg.getynet.com/brreg.php');
		// $v_post = array(
		// 	'organisation_no' => $v_creditor['companyorgnr'],
		// 	'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
		// 	'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
		// );
		// curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
		// $s_response = curl_exec($ch);
		
		// $v_items = array();
		// $v_response = json_decode($s_response, TRUE);
		// if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
		// {
		// 	$v_row_difi = $v_response['items'][0];
		// 	if($v_row_difi['konkurs'] == "J"){
		// 		$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Bankrupt' WHERE id = ?", array($v_creditor['id']));
		// 		$markedAsBankrupt++;
		// 	}
		// }
    }
	$org_nrs_chunk = array_chunk($org_nrs, 500);
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
		if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
		{
			$v_items = $v_response['items'];
			foreach($v_items as $v_item) {			
				
				$s_sql = "SELECT collecting_company_cases.id,collecting_company_cases.creditor_id
				FROM collecting_company_cases
				WHERE collecting_company_cases.creditor_id = ? AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'";
				$o_query = $o_main->db->query($s_sql, array($v_creditor['id']));
				$v_count_active_cases = $o_query ? $o_query->num_rows() : 0;

				if($v_item['konkurs'] == "J"){
					$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Konkurs', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
					$markedAsBankrupt++;
				} else if($v_item['tvangsavvikling'] == "J"){
					$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Tvangsavvikling', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
					$markedAsBankrupt++;
				} else if($v_item['avvikling'] == "J"){
					$o_main->db->query("UPDATE creditor SET creditor_marked_ceases_to_exist_date = NOW(), creditor_marked_ceases_to_exist_reason='Avvikling', active_company_case_count = '".$o_main->db->escape_str($v_count_active_cases)."' WHERE companyorgnr LIKE '%".$o_main->db->escape_like_str($v_item['orgnr'])."%'");
					$markedAsBankrupt++;
				}
			}
			
		}
	}

	echo $markedAsBankrupt." creditors marked";
	
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
}