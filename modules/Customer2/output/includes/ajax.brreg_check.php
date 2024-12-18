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

$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."' AND LENGTH(TRIM(publicRegisterId))>=9 AND (notOverwriteByImport IS NULL OR notOverwriteByImport = 0)");
if($o_query && $o_query->num_rows()>0)
{
	$v_customer = $o_query->row_array();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
	$v_post = array(
		'organisation_no' => $v_customer['publicRegisterId'],
		'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
		'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
	);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
	$s_response = curl_exec($ch);
	
	$v_items = array();
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
	{
		$v_row_difi = $v_response['items'][0];
		foreach($v_fields as $s_field => $v_difi_fields)
		{
			$s_value = '';
			foreach($v_difi_fields as $s_difi_field => $s_difi_check)
			{
				if($s_difi_check != '' && $v_row_difi[$s_difi_check] == '') continue;
				$s_value = $v_row_difi[$s_difi_field];
				break;
			}
			
			$b_change = (mb_strtoupper($v_customer[$s_field]) != mb_strtoupper($s_value));
			$v_items[$s_field] = array($v_customer[$s_field], $s_value, $b_change);
			if($b_change) $fw_return_data = 1;
		}
	}
	
	if($moduleAccesslevel > 10)
	{
		if(isset($_POST['output_form_submit']) && $v_customer['id'] > 0 && $fw_return_data == 1)
		{
			//
			// brreg_compare_status:
			// NULL - nothing
			// 0 - without changes
			// 1 - found differences
			// 2 - changes approved
			// 3 - changes declined
			$s_sql_update = '';
			if(isset($_POST['never_sync']) && $_POST['never_sync'] == 1)
			{
				$s_sql_update = 'notOverwriteByImport = 1, brreg_compare_date = NOW(), brreg_compare_status = 3';
			} else {
				foreach($v_items as $s_field => $v_item)
				{
					if($v_item[2]) $s_sql_update .= ($s_sql_update!=''?', ':'').$s_field." = '".$o_main->db->escape_str($v_item[1])."'";
				}
				$s_sql_update .= ", brreg_compare_date = NOW(), brreg_compare_status = 2";
			}
			
			$o_query = $o_main->db->query("UPDATE customer SET ".$s_sql_update." WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
			$fw_return_data = ($o_query ? 1 : 0);
			
			echo ($o_query ? $formText_CustomerUpdatedSuccessfully_Output : $formText_ErrorOccurredUpdatingCustomer_Output);
			
			return;
		}
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
}