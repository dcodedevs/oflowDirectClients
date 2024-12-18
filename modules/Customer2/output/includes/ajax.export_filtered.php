<?php
set_time_limit(300);
ini_set('memory_limit', '256M');

// no cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// content type
header("Content-Disposition: attachment; filename=\"eksport.csv\"");
header("Content-type: text/csv; charset=ISO-8859-1");

// Include column config
include(__DIR__.'/ajax.export_config_columns.php');

$v_enabled_columns = array();
foreach($v_export_columns as $l_key => $v_export_column)
{
	if(isset($_POST[$v_export_column['name']]) && 1 == $_POST[$v_export_column['name']])
	{
		$v_enabled_columns[] = $v_export_column;
	}
}
$b_export_main_contactperson = isset($_POST['export_main_contactperson']) && 1 == $_POST['export_main_contactperson'];

$s_sql = "SELECT * FROM customer_listtabs_basisconfig ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->result_array() : array());
$default_list = "all";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }

if($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
if($_POST['city_filter']) $_GET['city_filter'] = $_POST['city_filter'];
if($_POST['search_filter']) $_GET['search_filter'] = $_POST['search_filter'];
if($_POST['search_by']) $_GET['search_by'] = $_POST['search_by'];
if($_POST['selfdefinedfield_filter']) $_GET['selfdefinedfield_filter'] = $_POST['selfdefinedfield_filter'];
if($_POST['activecontract_filter']) $_GET['activecontract_filter'] = $_POST['activecontract_filter'];

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
$city_filter = $_GET['city_filter'] ? ($_GET['city_filter']) : '';
$search_filter = $_GET['search_filter'] ? ($_GET['search_filter']) : '';
$search_by = $_GET['search_by'] ? ($_GET['search_by']) : 1;
$activecontract_filter = $_GET['activecontract_filter'] ? ($_GET['activecontract_filter']) : '';

$selfdefinedfield_filter = $_GET['selfdefinedfield_filter'] ? $_GET['selfdefinedfield_filter'] : '';
if(!is_array($selfdefinedfield_filter)) {
	$selfdefinedfield_filter = json_decode(base64_decode($selfdefinedfield_filter), true);
}

$list_filter = $_SESSION['list_filter'];
$city_filter = $_SESSION['city_filter'];
$search_filter = $_SESSION['search_filter'];
$search_by = $_SESSION['search_by'];
$selfdefinedfield_filter = $_SESSION['selfdefinedfield_filter'];
$activecontract_filter = $_SESSION['activecontract_filter'];

require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM customer_listtabs_basisconfig WHERE id = '".$list_filter."' ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->row_array() : array());
$customer_currentList_basisconfig = array();
if(count($customer_listtabs_basisconfig) > 0){
	$s_sql = "SELECT * FROM customer_list_basisconfig WHERE id = '".$customer_listtabs_basisconfig['choose_list']."' ORDER BY sortnr";
	$o_query = $o_main->db->query($s_sql);
	$customer_currentList_basisconfig = ($o_query ? $o_query->row_array() : array());
}
$s_sql = "SELECT * FROM customer_list_basisconfig WHERE default_list = 1 ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_defaultList_basisconfig = ($o_query ? $o_query->row_array() : array());

$showDefaultList = false;
if($list_filter == "all" && count($customer_defaultList_basisconfig) > 0) {
	$showDefaultList = true;
}

$page = 0;
$perPage = 100000;

if($search_filter != "")
{
	$contactPage = $_POST['contactPage'] ? $_POST['contactPage'] : 1;
	$customerPage = $_POST['customerPage'] ? $_POST['customerPage'] : 1;

	if(!$customer_basisconfig['deactivateCompanySearch']){
		$customerList = get_customer_list($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1, $page, $perPage);
	}
	if(!$customer_basisconfig['deactivateContactPersonSearch']){
		$customerContactList = get_customer_list($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 2, $page, $perPage);
	}
} else {
	$customerList = get_customer_list($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1, $page, $perPage);
}


$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();
$subscriptionttypeId = 0;
if(strpos($list_filter, "subscriptiontype_") !== false) {
	if(strpos($list_filter, "group_by_") !== false) {
		$subscriptionttypeId = str_replace("group_by_subscriptiontype_", "", $list_filter);
	} else {
		$subscriptionttypeId = str_replace("subscriptiontype_", "", $list_filter);
	}
}
$s_csv_data = '';
$l_cp_max_count = 0;
foreach($customerList as $v_row)
{

	$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_row['customerId'])."'");
	$v_customer = $o_query ? $o_query->row_array() : array();

	$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE customer_id = '".$o_main->db->escape_str($v_row['customerId'])."'");
	$customerExternal = $o_query ? $o_query->row_array() : array();

	$v_customer['customerTypeName'] = ($v_customer['customerType']==1?$formText_Person_Output:$formText_Company_Output);
	$v_customer['id_number'] = ($v_customer['customerType']==1?$v_customer['personnumber']:$v_customer['publicRegisterId']);
	$v_customer['external_id'] = $customerExternal['external_id'];
	$v_customer['customerName'] = $v_row['customerName'];

	$getSubscriptions = false;
	foreach($v_enabled_columns as $l_key => $v_enabled_column)
	{
		if($v_enabled_column['type'] == 5)
		{
			$getSubscriptions = true;
		}
	}
	$activeSubs = array(array("id"=>0));
	if($getSubscriptions) {
		$sql_sub_where = "";
		if($subscriptionttypeId > 0){
			$sql_sub_where = " AND s.subscriptiontype_id = '".$o_main->db->escape_str($subscriptionttypeId)."'";
		}
		$sql = "SELECT s.*, st.name as subscriptionTypeName, sts.name as subscriptionSubTypeName FROM subscriptionmulti s
		LEFT OUTER JOIN subscriptiontype st ON st.id = s.subscriptiontype_id
		LEFT OUTER JOIN subscriptiontype_subtype sts ON sts.id = s.subscriptionsubtypeId
		WHERE
		(
			s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= CURDATE()
			AND
			(
				(s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null AND s.stoppedDate > CURDATE())
				OR (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null)
			)
		) AND s.content_status < 2 AND s.customerId = ? ".$sql_sub_where." GROUP BY s.id";

		$o_query = $o_main->db->query($sql, array($v_customer['id']));
		$activeSubsChecking = $o_query ? $o_query->result_array() : array();
		if($activeSubsChecking){
			$activeSubs = $activeSubsChecking;
		}
	}
	foreach($activeSubs as $activeSub) {

	/*$o_query = $o_main->db->query("SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($v_row['customerId'])."'");
	$v_customer = $o_query ? $o_query->row_array() : array();

	$o_query = $o_main->db->query("SELECT * FROM customer_externalsystem_id WHERE customer_id = '".$o_main->db->escape_str($v_row['customerId'])."'");
	$customerExternal = $o_query ? $o_query->row_array() : array();

	$s_line = '';
	$s_line = ($v_customer['customerType']==1?$formText_Person_Output:$formText_Company_Output).";".$customerExternal['external_id'].";".($v_customer['customerType']==1?$v_customer['personnumber']:$v_customer['publicRegisterId']).";".$v_row['customerName'].";".$v_customer['phone'].";".$v_customer['mobile'].";".$v_customer['fax'].";".$v_customer['email']
	.";".$v_customer['paStreet'].";".$v_customer['paStreet2'].";".$v_customer['paPostalNumber'].";".$v_customer['paCity'].";".$v_customer['paCountry']
	.";".$v_customer['vaStreet'].";".$v_customer['vaStreet2'].";".$v_customer['vaPostalNumber'].";".$v_customer['vaCity'].";".$v_customer['vaCountry'].";".trim($v_customer['invoiceEmail']);

	$l_cp_count = 0;
	$o_find = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = '".$o_main->db->escape_str($v_row['customerId'])."' AND content_status = 0 ORDER BY sortnr");
	if($o_find && $o_find->num_rows()>0)
	foreach($o_find->result_array() as $v_cp)
	{
		$l_cp_count++;
		$s_line .= ";".trim($v_cp['name']." ".$v_cp['middlename']." ".$v_cp['lastname']).";".$v_cp['mobile'].";".$v_cp['email'];
	}
	if($l_cp_count > $l_cp_max_count) $l_cp_max_count = $l_cp_count;

	$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');*/



		$s_line = '';
		$s_line_array = array();
		foreach($v_enabled_columns as $l_key => $v_enabled_column)
		{
			if($v_enabled_column['type'] == 1)
			{
				$s_line_array[] = trim($v_customer[$v_enabled_column['field']]);
			}
		}

		foreach($v_enabled_columns as $l_key => $v_enabled_column)
		{
			if($v_enabled_column['type'] == 3)
			{
				$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = '".$o_main->db->escape_str($v_customer['id'])."' AND selfdefined_fields_id = '".$o_main->db->escape_str($v_enabled_column['id'])."'";
				$o_find = $o_main->db->query($s_sql);
				$v_customer_selfdefined_field_value = $o_find ? $o_find->row_array() : array();

				$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = '".$o_main->db->escape_str($v_enabled_column['id'])."'";
				$o_find = $o_main->db->query($s_sql);
				$v_customer_selfdefined_field = $o_find ? $o_find->row_array() : array();

				if(0 == $v_customer_selfdefined_field['type']) //Checkbox
				{
					$s_line_array[] = (1==$v_customer_selfdefined_field_value['active']?1:0);
					$s_line_array[] = $v_customer_selfdefined_field_value['value'];
				} else if(1 == $v_customer_selfdefined_field['type']) //Dropdown
				{
					$s_value = '';
					$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE id = '".$o_main->db->escape_str($v_customer_selfdefined_field_value['value'])."'";
					$o_find = $o_main->db->query($s_sql);
					if($o_find && $o_find->num_rows()>0)
					foreach($o_find->result_array() as $v_value)
					{
						$s_value .= (''!=$s_value?',':'').$v_value['name'];
					}
					$s_line_array[] = $s_value;
				} else if(2 == $v_customer_selfdefined_field['type']) //Multiple checkboxes
				{
					$s_value = '';
					$s_sql = "SELECT i.* FROM customer_selfdefined_values_connection AS c JOIN customer_selfdefined_list_lines AS i ON i.id = c.selfdefined_list_line_id WHERE selfdefined_value_id = '".$o_main->db->escape_str($v_customer_selfdefined_field_value['id'])."'";
					$o_find = $o_main->db->query($s_sql);
					if($o_find && $o_find->num_rows()>0)
					foreach($o_find->result_array() as $v_value)
					{
						$s_value .= (''!=$s_value?',':'').$v_value['name'];
					}
					$s_line_array[] = $s_value;
				}
			}
		}


		foreach($v_enabled_columns as $l_key => $v_enabled_column)
		{
			if($v_enabled_column['type'] == 5)
			{
				$s_line_array[] = trim($activeSub[$v_enabled_column['field']]);
			}
		}
		$l_cp_count = 0;
		$s_sql_where = ($b_export_main_contactperson ? " AND mainContact = 1" : "");
		$o_find = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = '".$o_main->db->escape_str($v_row['customerId'])."' AND content_status = 0 AND (inactive IS NULL OR inactive = 0)".$s_sql_where." ORDER BY sortnr");
		if($o_find && $o_find->num_rows()>0)
		foreach($o_find->result_array() as $v_cp)
		{
			$l_cp_count++;
			$v_cp['fullname'] = trim($v_cp['name']." ".$v_cp['middlename']." ".$v_cp['lastname']);
			foreach($v_enabled_columns as $l_key => $v_enabled_column)
			{
				if($v_enabled_column['type'] == 2)
				{
					$s_line_array[] = trim($v_cp[$v_enabled_column['field']]);
				}
			}
		}
		if($l_cp_count > $l_cp_max_count) $l_cp_max_count = $l_cp_count;

		$s_line = implode(";", $s_line_array);
		$s_csv_data .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
	}
}

$s_csv_content = '';
$s_csv_content_array = array();
foreach($v_enabled_columns as $l_key => $v_enabled_column)
{
	if($v_enabled_column['type'] == 1)
	{
		$s_csv_content_array[] = $v_enabled_column['label'];
	}
}
foreach($v_enabled_columns as $l_key => $v_enabled_column)
{
	if($v_enabled_column['type'] == 3)
	{
		$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = '".$o_main->db->escape_str($v_enabled_column['id'])."'";
		$o_find = $o_main->db->query($s_sql);
		$v_customer_selfdefined_field = $o_find ? $o_find->row_array() : array();

		if(0 == $v_customer_selfdefined_field['type']) //Checkbox
		{
			$s_csv_content_array[] = $v_enabled_column['label'].' '.$formText_isChecked_Export;
			$s_csv_content_array[] = $v_enabled_column['label'].' '.$formText_value_Export;
		} else {
			$s_csv_content_array[] = $v_enabled_column['label'];
		}
	}
}
foreach($v_enabled_columns as $l_key => $v_enabled_column)
{
	if($v_enabled_column['type'] == 5)
	{
		$s_csv_content_array[] = $v_enabled_column['label'];
	}
}
for($i=1; $i<=$l_cp_max_count; $i++)
{
	foreach($v_enabled_columns as $l_key => $v_enabled_column)
	{
		if($v_enabled_column['type'] == 2)
		{
			$s_csv_content_array[] = $v_enabled_column['label'].' '.$i;
		}
	}
}
$s_csv_content = implode(";", $s_csv_content_array);
$s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'ISO-8859-1', 'UTF-8');

$s_csv_content .= $s_csv_data;
echo $s_csv_content;
exit;
