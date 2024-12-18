<?php
$fw_return_data = 0;

$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."'");
$v_customer = $o_query ? $o_query->row_array() : array();
if(($v_customer) || $_POST['publicRegisterId'] != "")
{
	if($_POST['publicRegisterId'] != "") {
		$v_customer['publicRegisterId'] = $_POST['publicRegisterId'];
		$v_customer['invoiceEmail'] = $_POST['invoiceEmail'];
	}
	$s_customer_org_nr = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);

	$b_update = FALSE;
	$b_found_receiver = FALSE;
	if(strlen($s_customer_org_nr)>=9)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		/*curl_setopt($ch, CURLOPT_URL, 'https://hotell.difi.no/api/jsonp/difi/elma/capabilities?ehf_invoice=true&query='.$s_customer_org_nr.'*&callback=callback123');
		$s_response = curl_exec($ch);
		if($s_response !== FALSE)
		{
			$v_response = json_decode(substr($s_response, 12, -2), TRUE);
			if(isset($v_response['entries']))
			foreach($v_response['entries'] as $v_entry)
			{
				if($v_entry['identifier'] == $s_customer_org_nr)
				{
					$b_update = TRUE;
					break;
				}
			}
		}*/
		// BIS 3.0 identification
		$s_url = 'https://hotell.difi.no/api/jsonp/difi/elma/participants?identifier='.$s_customer_org_nr.'&callback=callback123';
		curl_setopt($ch, CURLOPT_URL, $s_url);
		$s_response = curl_exec($ch);
		if(FALSE !== $s_response)
		{
			$v_response = json_decode(substr($s_response, 12, -2), TRUE);
			if(!isset($v_response['entries'])) return;
			foreach($v_response['entries'] as $v_entry)
			{
				$b_is_icd = $b_is_bis3 = FALSE;
				foreach($v_entry as $s_key => $s_value)
				{
					if($s_key == 'Icd' && $s_value == '0192') $b_is_icd = TRUE;
					if($s_key == 'PEPPOLBIS_3_0_BILLING_01_UBL' && $s_value == 'Ja') $b_is_bis3 = TRUE;
				}
				if($b_is_icd && $b_is_bis3) $b_found_receiver = TRUE;
			}
		} else return;
		if(!$b_found_receiver) file_put_contents(BASEPATH.'/../../../tmp/_EHF_elma_check.log', date('Y-m-d H:i:s')."\n".$s_url."\n".$s_response."\n\n", FILE_APPEND);
	}
	$b_update = FALSE;
	$l_invoice_by = 0;
	if($b_found_receiver)
	{
		$l_invoice_by = 2;
	} else {
		if('' != trim($v_customer['invoiceEmail']))
		{
			$l_invoice_by = 1;
		}
	}
	if($v_customer['invoiceBy'] != $l_invoice_by)
	{
		$b_update = TRUE;
		$fw_return_data = $l_invoice_by+1;
	} else {
		$fw_return_data = $l_invoice_by+1;
	}

	if($moduleAccesslevel > 10)
	{
		if(isset($_POST['output_form_submit']) && $v_customer['id'] > 0 && $fw_return_data >= 1)
		{
			$s_sql_update = "invoiceBy = '".$o_main->db->escape_str($l_invoice_by)."'";

			$o_query = $o_main->db->query("UPDATE customer SET ".$s_sql_update." WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
			$fw_return_data = ($o_query ? 1 : 0);

			echo ($o_query ? $formText_CustomerUpdatedSuccessfully_Output : $formText_ErrorOccurredUpdatingCustomer_Output);
			return;
		}
	}

	if(isset($_POST['confirm_update']) && $_POST['confirm_update'] == 1)
	{
		?>
		<div class="popupform">
			<div class="popupformTitle"><?php echo $formText_UpdateCustomer_Output;?></div>
			<div><?php
			if($fw_return_data == 2){
				echo $formText_AreYouSureYouWantToUpdateCustomerToReceiveInvoiceByEmail_Output;
			} else if($fw_return_data == 3){
				echo $formText_AreYouSureYouWantToUpdateCustomerToReceiveInvoiceByEhf_Output;
			} else if($fw_return_data == 1){
				echo $formText_AreYouSureYouWantToUpdateCustomerToReceiveInvoiceByPaper_Output;
			}
			?>
			?</div>
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
				<input type="button" class="output-update-customer-btn" value="<?php echo $formText_Update_Output; ?>">
			</div>
		</div>
		<script type="text/javascript">
		$(function(){
			$('input.output-update-customer-btn').off('click').on('click', function(e){
				e.preventDefault();
				var data = {
					output_form_submit: 1,
					customer_id: '<?php echo $v_customer['id'];?>'
				};
				ajaxCall('check_ehf', data, function(json) {
					if(json.data == 1) $('#popupeditbox').addClass('close-reload');
					$('#popupeditboxcontent').html(json.html);
				});
			});
		});
		</script>
		<?php
	}
}
