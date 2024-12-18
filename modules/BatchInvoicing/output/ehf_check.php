<?php
$v_status = array(
	$formText_EhfIsAlreadyActivated_Output,
	$formText_EhfWillBeActivated_Output,
	$formText_EhfCanBeActivated_Output,
	$formText_EhfWillBeDisabled_Output,
	$formText_EhfIsAlreadyDisabled_Output
);
if($basisConfigData['activate_ehf_check'] > 1)
{
	$s_sql = "SELECT c.*, co.ownercompanyId ownercompany_id, co.seperateInvoiceFromSubscription as seperatedInvoiceSubscriptionId, co.id collectingorderId, co.seperatedInvoice
	FROM customer_collectingorder co
	JOIN customer c ON c.id = co.customerId
	WHERE co.approvedForBatchinvoicing = 1 AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0
	GROUP BY c.id ORDER BY c.name";
} else {
	$basisConfigData['activate_ehf_check'] = 3;
	$s_sql = "SELECT c.*, co.ownercompanyId ownercompany_id, co.seperateInvoiceFromSubscription as seperatedInvoiceSubscriptionId, co.id collectingorderId, co.seperatedInvoice
	FROM customer_collectingorder co
	JOIN customer c ON c.id = co.customerId
	WHERE c.invoiceBy = 2 AND co.approvedForBatchinvoicing = 1 AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0
	GROUP BY c.id ORDER BY c.name";
}
$o_query = $o_main->db->query($s_sql);
$l_total = 0;
if(isset($_POST['step']) && $_POST['step'] == 1)
{
	foreach($_POST['customer'] as $l_customer_id => $l_value)
	{
		$o_main->db->query("UPDATE customer SET invoiceBy = '".((int)$l_value == 1 ? 2 : 0)."' WHERE id = '".(int)$l_customer_id."'");
	}
	return;
}
ob_start();
?>
<div class="p_pageDetails">
	<div class="p_pageDetailsTitle"><?php echo $formText_EhfCheck_output;?></div>
	<div class="p_contentBlock">
		<form class="output-form">
		<table class="table table-bordered" style="margin-bottom: 0px;">
		<thead>
		<tr>
			<?php if($basisConfigData['activate_ehf_check'] == 3) { ?>
			<th width="10%"><span id="output-select-all-none" class="checked" style="color:#068ad1; cursor:pointer;"><?php echo $formText_SelectAllNone_output;?></span></th>
			<?php } ?>
			<th><?php echo $formText_OrganisationNumber_output;?></th>
			<th width="50%"><?php echo $formText_CompanyName_output;?></th>
			<th><?php echo $formText_Result_output;?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_customer)
		{
			if(!$v_customer['do_not_check_for_ehf']){
				$b_found_receiver = FALSE;
				$s_customer_org_nr = preg_replace('#[^0-9]+#', '', $v_customer['publicRegisterId']);

				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_VERBOSE, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
				curl_setopt($ch, CURLOPT_URL, 'https://hotell.difi.no/api/jsonp/difi/elma/capabilities?ehf_invoice=true&query='.$s_customer_org_nr.'*&callback=callback123');
				$s_response = curl_exec($ch);
				if($s_response === FALSE) continue;
				$b_update = $b_found_receiver = FALSE;
				$v_response = json_decode(substr($s_response, 12, -2), TRUE);
				if(isset($v_response['entries']))
				foreach($v_response['entries'] as $v_entry)
				{
					if($v_entry['identifier'] == $s_customer_org_nr)
					{
						$b_update = TRUE;
					}
				}
				//If empty result (not exists in ELMA at all)
				if(!$b_update && isset($v_response['entries']) && count($v_response['entries']) == 0) $b_update = TRUE;
				// BIS 3.0
				//curl_setopt($ch, CURLOPT_URL, 'https://hotell.difi.no/api/jsonp/difi/elma/participants?PEPPOLBIS_3_0_BILLING_01_UBL=Ja&query='.$s_customer_org_nr.'*&callback=callback123');
				// BIS 3.0 identification
				$s_url = 'https://hotell.difi.no/api/jsonp/difi/elma/participants?identifier='.$s_customer_org_nr.'&callback=callback123';
				curl_setopt($ch, CURLOPT_URL, $s_url);
				$s_response = curl_exec($ch);
				$b_found_receiver = FALSE;
				if(FALSE !== $s_response)
				{
					$v_response = json_decode(substr($s_response, 12, -2), TRUE);
					if(!isset($v_response['entries'])) continue;
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
				}
				if(!$b_found_receiver) file_put_contents(BASEPATH.'/../../../tmp/_EHF_elma_check.log', date('Y-m-d H:i:s')."\n".$s_url."\n".$s_response."\n\n", FILE_APPEND);
				// Do not update customer which cannot be found in company register
				if(!$b_update) continue;

				$l_status = 0;
				if($b_found_receiver)
				{
					// not ehf
					if($v_customer['invoiceBy'] != 2)
					{
						$l_status = ($basisConfigData['activate_ehf_check'] == 2 ? 1 : 2);
					}
				} else {
					$l_status = 4;
					if($v_customer['invoiceBy'] == 2) $l_status = 3;
				}
				if($l_status == 0 || $l_status == 4) continue;
				?>
				<tr>
					<?php
					if($basisConfigData['activate_ehf_check'] == 3)
					{
						?><td><?php
						if($l_status == 2)
						{
							?><input type="checkbox" name="value[]" value="1" data-id="<?php echo $v_customer['id'];?>"<?php echo ($b_found_receiver?' checked':'');?>><?php
						} else if($l_status == 3) {
							?><input type="hidden" name="value[]" value="<?php echo ($b_found_receiver?'1':'0');?>" data-id="<?php echo $v_customer['id'];?>"><?php
						}
						?></td><?php
					} else if($l_status == 1 || $l_status == 3) {
						?><input type="hidden" name="value[]" value="<?php echo ($b_found_receiver?'1':'0');?>" data-id="<?php echo $v_customer['id'];?>"><?php
					}
					?>
					<td><?php echo $s_customer_org_nr;?></td>
					<td><?php echo $v_customer['name'];?></td>
					<td><?php echo $v_status[$l_status];?></td>
				</tr>
				<?php
				$l_total++;
			}
		}
		?>
		</tbody>
		</table>
		</form>
		<div style="padding-top:10px;">
			<button id="output-save-ehf-selection" class="btn btn-default" type="button"><?php echo $formText_Continue_Output;?></button>
		</div>
	</div>
</div>
<script type="text/javascript">
$(function(){
	$('#output-select-all-none').off('click').on('click', function(){
		if($(this).is('.checked'))
		{
			$('.p_pageDetails .output-form tbody input[type="checkbox"]').removeProp('checked');
		} else {
			$('.p_pageDetails .output-form tbody input[type="checkbox"]').prop('checked', true);
		}
		$(this).toggleClass('checked');
	});
	$("#output-save-ehf-selection").off('click').on("click", function(){
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var customer = {};
			$('.p_pageDetails .output-form input').each(function(){
				customer[$(this).data('id')] = <?php if($basisConfigData['activate_ehf_check'] == 3) { ?>($(this).is(':checked') ? 1 : 0);<?php } else { ?>$(this).val();<?php } ?>
			});
			var data = {
				step: 1,
				fwajax: 1,
				fw_nocss: 1,
				customer: customer
			};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output";?>',
				data: data,
				success: function(json){
					$('#output-content-container').html(json.html);
					fw_click_instance = false;
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccurredHandlingRquest_Output;?>", true, true);
				fw_click_instance = false;
			});
		}
	});
});
</script>
<?php
$s_buffer = ob_get_clean();

if($l_total == 0)
{
	$_POST['step'] = 1;
	return;
}
echo $s_buffer;
