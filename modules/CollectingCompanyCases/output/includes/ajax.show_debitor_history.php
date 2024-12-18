<?php
$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."'";
$o_query = $o_main->db->query($s_sql);
$debitor = ($o_query ? $o_query->row_array() : array());

$v_types = array(
	1 => $formText_Company_output,
	2 => $formText_PrivatePerson_output,
);

$v_fields = array(
	'extraName' => $formText_CollectingName_Output,
	'extraPublicRegisterId' => $formText_CollectingPublictRegisterId_Output,
	'extra_social_security_number' => $formText_CollectingSocialSecurityNumber_Output,
	'extra_phone' => $formText_CollectingPhone_Output,
	'extra_invoice_email' => $formText_CollectingInvoiceEmail_Output,
	'extraStreet' => $formText_Street_Output,
	'extraPostalNumber' => $formText_PostalNumber_Output,
	'extraCity' => $formText_City_Output,
	'extraCountry' => $formText_Country_Output,
	'customer_type_for_collecting_cases' => $formText_CollectingCustomerType_Output,
);

$v_current = $debitor;
$v_debitor_history = array();
$s_sql = "SELECT created, content_value FROM sys_content_history WHERE content_id = '".$o_main->db->escape_str($debitor['id'])."' AND content_table = 'customer' ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	$v_json = json_decode($v_row['content_value'], TRUE);
	
	$b_found = FALSE;
	foreach($v_fields as $s_field => $s_label) if(array_key_exists($s_field, $v_json)) $b_found = TRUE;

	if($b_found)
	{
		if($v_json['extraName'] == $v_current['extraName']
		&& $v_json['extraPublicRegisterId'] == $v_current['extraPublicRegisterId']
		&& $v_json['extra_social_security_number'] == $v_current['extra_social_security_number']
		&& $v_json['extra_phone'] == $v_current['extra_phone']
		&& $v_json['extra_invoice_email'] == $v_current['extra_invoice_email']
		&& $v_json['extraStreet'] == $v_current['extraStreet']
		&& $v_json['extraPostalNumber'] == $v_current['extraPostalNumber']
		&& $v_json['extraCity'] == $v_current['extraCity']
		&& $v_json['extraCountry'] == $v_current['extraCountry']
		&& $v_json['customer_type_for_collecting_cases'] == $v_current['customer_type_for_collecting_cases'])
		{
		} else {
			$v_debitor_history[$v_row['created']] = $v_json;
			$v_current = $v_json;
		}
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<div class="inner">
		<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
			<?php
			$l_count = 1;
			$v_current = $debitor;
			if(0 < count($v_debitor_history))
			{
				foreach($v_debitor_history as $s_date => $v_json)
				{
					?><div class="panel panel-default">
						<div class="panel-heading" role="tab" id="headingOne">
							<h4 class="panel-title">
								<a role="button" data-toggle="collapse" data-parent="#accordion" href="#history<?php echo $l_count;?>" aria-expanded="true">
									<?php echo date('d.m.Y H:i', strtotime($s_date));?>
								</a>
							</h4>
						</div>
						<div id="history<?php echo $l_count;?>" class="panel-collapse collapse <?php echo (1==$l_count?' in':'');?>" role="tabpanel" aria-labelledby="headingOne">
							<div class="panel-body">
								<div class="container-fluid">
									<div class="row">
										<div class="col-xs-4"><b><?php echo $formText_Fields_Output;?></b></div>
										<div class="col-xs-4"><b><?php echo $formText_PreviousValue_Output;?></b></div>
										<div class="col-xs-4"><b><?php echo $formText_ChangedTo_Output;?></b></div>
									</div>
									<?php
									foreach($v_fields as $s_field => $s_label)
									{
										$s_old = (array_key_exists($s_field, $v_json) ? $v_json[$s_field] : $v_current[$s_field]);
										$s_new = (array_key_exists($s_field, $v_json) ? $v_current[$s_field] : '');
										$v_current[$s_field] = $s_old;
										if('customer_type_for_collecting_cases' == $s_field)
										{
											if(0 < (int)$s_old) $s_old = $v_types[$s_old];
											if(0 < (int)$s_new) $s_new = $v_types[$s_new];
										}
										?>
										<div class="row">
											<div class="col-xs-4"><?php echo $s_label;?></div>
											<div class="col-xs-4"><?php echo $s_old;?></div>
											<div class="col-xs-4"><?php echo $s_new;?></div>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
					<?php
					$l_count++;
				}
			} else {
				echo $formText_NoHistoryFound_Output;
			}
			?>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
		</div>
	</div>
</div>