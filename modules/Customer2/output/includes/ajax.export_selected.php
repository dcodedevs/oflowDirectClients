<?php
// *********************************
// *** Ali - not in use anumore!?!
// *********************************

set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=ISO-8859-1");

if(!is_array($_POST['selected'])) $_POST['selected'] = explode(",", $_POST['selected']);

$s_csv_data = '';
$l_cp_max_count = 0;
$o_query = $o_main->db->query("SELECT *, id AS customerId, CONCAT(COALESCE(name, ''), ' ', COALESCE(middlename, ''), ' ', COALESCE(lastname, '')) AS customerName FROM customer WHERE id IN ? ORDER BY name", array($_POST['selected']));
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_customer)
{
	$s_line = '';
	$s_line = ($v_customer['customerType']==1?$formText_Person_Output:$formText_Company_Output).";".($v_customer['customerType']==1?$v_customer['personnumber']:$v_customer['publicRegisterId']).";".$v_customer['customerName'].";".$v_customer['phone'].";".$v_customer['mobile'].";".$v_customer['fax'].";".$v_customer['email']
	.";".$v_customer['paStreet'].";".$v_customer['paStreet2'].";".$v_customer['paPostalNumber'].";".$v_customer['paCity'].";".$v_customer['paCountry']
	.";".$v_customer['vaStreet'].";".$v_customer['vaStreet2'].";".$v_customer['vaPostalNumber'].";".$v_customer['vaCity'].";".$v_customer['vaCountry'].";".trim($v_customer['invoiceEmail']);

	$l_cp_count = 0;
	$o_find = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = '".$o_main->db->escape_str($v_customer['customerId'])."' AND content_status = 0 ORDER BY sortnr");
	if($o_find && $o_find->num_rows()>0)
	foreach($o_find->result_array() as $v_cp)
	{
		$l_cp_count++;
		$s_line .= ";".trim($v_cp['name']." ".$v_cp['middlename']." ".$v_cp['lastname']).";".$v_cp['mobile'].";".$v_cp['email'];
	}
	if($l_cp_count > $l_cp_max_count) $l_cp_max_count = $l_cp_count;

	$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
}

$s_csv_content = $formText_Type_Output.";".$formText_PublicRegisterIdOrPersonNumber_Output.";".$formText_Name_Output.";".$formText_Phone_Output.";".$formText_Mobile_Output.";".$formText_Fax_Output.";".$formText_Email_Output.";".$formText_PAStreet_Output.";".$formText_PAStreet2_Output.";".$formText_PAPostalNumber_Output.";".$formText_PACity_Output.";".$formText_PACountry_Output.";".$formText_VAStreet_Output.";".$formText_VAStreet2_Output.";".$formText_VAPostalNumber_Output.";".$formText_VACity_Output.";".$formText_VACountry_Output.";".$formText_InvoiceEmail;

for($i=1; $i<=$l_cp_max_count; $i++) $s_csv_content .= ";".$formText_ContactpersonName_Output." ".$i.";".$formText_ContactpersonMobile_Output." ".$i.";".$formText_ContactpersonEmail_Output." ".$i;

$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'ISO-8859-1', 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
