<?php
// ************
// Independent CronJob script which compare customer data with Brreg.no and stores difference in customer_sync_data
// Version 1.0
// ************
define("BASEPATH", realpath(__DIR__."/../../../").DIRECTORY_SEPARATOR);
require_once(BASEPATH."elementsGlobal/cMain.php");

$v_path = explode('/', __DIR__);
$_POST['folder'] = array_pop($v_path);
include(__DIR__."/../output/includes/readOutputLanguage.php");

$l_status = 0;
$v_parameters = $_SERVER['argv'];
$l_cronjob_id = intval($v_parameters[1]);
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

//
// brreg_compare_status:
// NULL - nothing
// 0 - without changes
// 1 - found differences
// 2 - changes approved
// 3 - changes declined

$o_query = $o_main->db->query("SELECT * FROM sys_cronjob WHERE id = '".$o_main->db->escape_str($l_cronjob_id)."'");
if($o_query && $o_query->num_rows()>0)
{
	$l_counter = 0;
	$v_cronjob = $o_query->row_array();
	$v_variables = json_decode($v_cronjob['parameters'], TRUE);
	foreach($v_variables as $s_key => $s_value) ${$s_key} = $s_value;

	$o_main->db->query("TRUNCATE customer_sync_data");

	if($l_type == 1)
	{
		$o_query = $o_main->db->query("SELECT c.* FROM customer c JOIN subscriptionmulti s ON s.customerId = c.id AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate >= DATE(NOW())) WHERE (c.brreg_compare_date IS NULL OR c.brreg_compare_date = '0000-00-00 00:00:00' OR DATE_ADD(c.brreg_compare_date, INTERVAL ".$l_delay." DAY) < NOW()) AND LENGTH(TRIM(c.publicRegisterId))>=9 AND (c.notOverwriteByImport IS NULL OR c.notOverwriteByImport = 0) AND (c.brreg_compare_status IS NULL OR c.brreg_compare_status < 4) GROUP BY c.id ORDER BY c.id ASC");
	} else {
		$o_query = $o_main->db->query("SELECT * FROM customer WHERE (brreg_compare_date IS NULL OR c.brreg_compare_date = '0000-00-00 00:00:00' OR DATE_ADD(brreg_compare_date, INTERVAL ".$l_delay." DAY) < NOW()) AND LENGTH(TRIM(c.publicRegisterId))>=9 AND (notOverwriteByImport IS NULL OR notOverwriteByImport = 0) AND (brreg_compare_status IS NULL OR brreg_compare_status < 4) ORDER BY id ASC");
	}
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_row)
	{
		$b_is_difference = FALSE;
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
		$v_post = array(
			'organisation_no' => $v_row['publicRegisterId'],
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

				if(mb_strtoupper($v_row[$s_field]) != mb_strtoupper($s_value))
				{
					$b_is_difference = TRUE;
					$o_main->db->query("INSERT INTO customer_sync_data SET customer_id = '".$o_main->db->escape_str($v_row['id'])."', field = '".$o_main->db->escape_str($s_field)."', brreg_value = '".$o_main->db->escape_str($s_value)."'");
				}
			}
		}
		$s_sql = "UPDATE customer SET brreg_compare_date = NOW(), brreg_compare_status = '".($b_is_difference?1:0)."' WHERE id = '".$o_main->db->escape_str($v_row['id'])."'";
		$o_main->db->query($s_sql);

		//sleep(3);
		if($b_is_difference) $l_counter++;
		if($l_counter >= 100) break;
	}

	echo "Script finished\n";
} else {
	echo "Error: sys_cronjob not found by ID: ".$l_cronjob_id."\n";
}
