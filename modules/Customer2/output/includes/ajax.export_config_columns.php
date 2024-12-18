<?php
$v_export_columns = array(
	array(
		'label' => $formText_CheckOrUncheckAll_Output,
		'toggle_class' => 'toggle_regular_customer_fields',
		'field' => 'customerTypeName',
		'checked' => TRUE,
		'type' => 6,
	),
	array(
		'label' => $formText_Type_Output,
		'name' => 'export_config_type',
		'field' => 'customerTypeName',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_CustomerNumber_Output,
		'name' => 'export_config_customer_number',
		'field' => 'external_id',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PublicRegisterIdOrPersonNumber_Output,
		'name' => 'export_config_reg_number',
		'field' => 'id_number',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_Name_Output,
		'name' => 'export_config_name',
		'field' => 'customerName',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_ShopName_Output,
		'name' => 'export_config_shop_name',
		'field' => 'shop_name',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_Phone_Output,
		'name' => 'export_config_phone',
		'field' => 'phone',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_Mobile_Output,
		'name' => 'export_config_mobile',
		'field' => 'mobile',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_Fax_Output,
		'name' => 'export_config_fax',
		'field' => 'fax',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_Email_Output,
		'name' => 'export_config_email',
		'field' => 'email',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PAStreet_Output,
		'name' => 'export_config_PAStreet',
		'field' => 'paStreet',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PAStreet2_Output,
		'name' => 'export_config_PAStreet2',
		'field' => 'paStreet2',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PAPostalNumber_Output,
		'name' => 'export_config_PAPostalNumber',
		'field' => 'paPostalNumber',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PACity_Output,
		'name' => 'export_config_PACity',
		'field' => 'paCity',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_PACountry_Output,
		'name' => 'export_config_PACountry',
		'field' => 'paCountry',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_VAStreet_Output,
		'name' => 'export_config_VAStreet',
		'field' => 'vaStreet',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_VAStreet2_Output,
		'name' => 'export_config_VAStreet2',
		'field' => 'vaStreet2',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_VAPostalNumber_Output,
		'name' => 'export_config_VAPostalNumber',
		'field' => 'vaPostalNumber',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_VACity_Output,
		'name' => 'export_config_VACity',
		'field' => 'vaCity',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_VACountry_Output,
		'name' => 'export_config_VACountry',
		'field' => 'vaCountry',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
	array(
		'label' => $formText_InvoiceEmail,
		'name' => 'export_config_InvoiceEmail',
		'field' => 'invoiceEmail',
		'checked' => TRUE,
		'type' => 1,
		'class' => 'toggle_regular_customer_fields',
	),
);
$v_export_columns[] = array(
	'label' => $formText_Contactpersons_Export,
	'type' => 4,
);
$v_export_columns[] = array(
	'label' => $formText_ContactpersonName_Output,
	'name' => 'export_config_ContactpersonName',
	'field' => 'fullname',
	'checked' => TRUE,
	'type' => 2,
);
$v_export_columns[] = array(
	'label' => $formText_ContactpersonMobile_Output,
	'name' => 'export_config_ContactpersonMobile',
	'field' => 'mobile',
	'checked' => TRUE,
	'type' => 2,
);
$v_export_columns[] = array(
	'label' => $formText_ContactpersonEmail_Output,
	'name' => 'export_config_ContactpersonEmail',
	'field' => 'email',
	'checked' => TRUE,
	'type' => 2,
);

$s_sql = "SELECT * FROM customer_selfdefined_fields ORDER BY customer_selfdefined_fields.sortnr";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0)
{
	$v_export_columns[] = array(
		'label' => $formText_SelfDefinedFields_Export,
		'type' => 0,
	);
	foreach($o_query->result_array() as $v_row)
	{
		$v_export_columns[] = array(
			'label' => $v_row['name'],
			'name' => 'export_config_selfdefined_field_'.$v_row['id'],
			'id' => $v_row['id'],
			'checked' => FALSE,
			'type' => 3,
		);
	}
}
$v_export_columns[] = array(
	'label' => $formText_Subscriptions_Export,
	'type' => 0,
);

$v_export_columns[] = array(
	'label' => $formText_SubscriptionType_Output,
	'name' => 'export_config_SubscriptionType',
	'field' => 'subscriptionTypeName',
	'checked' => FALSE,
	'type' => 5,
);
$v_export_columns[] = array(
	'label' => $formText_SubscriptionSubtype_Output,
	'name' => 'export_config_SubscriptionSubType',
	'field' => 'subscriptionSubTypeName',
	'checked' => FALSE,
	'type' => 5,
);
