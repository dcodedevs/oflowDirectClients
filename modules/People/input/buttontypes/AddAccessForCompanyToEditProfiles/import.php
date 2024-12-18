<?php

if(!function_exists("APIconnectorUser")) include(__DIR__."/../../../output/includes/APIconnector.php");
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['migrateData'])) {
		//customer
		$v_membersystem = array();

		$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
		$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
		foreach($v_cache_userlist_membership as $v_user_cached_info) {
			$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
		}
		$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
		$v_cache_userlist = $o_query ? $o_query->result_array() : array();
		foreach($v_cache_userlist as $v_user_cached_info) {
			$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
		}
		$accessesGiven = 0;
		foreach($v_membersystem as $member){
		    $sql = "SELECT * FROM contactperson WHERE email = '".$o_main->db->escape_str($member['username'])."'";
			$o_query = $o_main->db->query($sql);
		    $peopleData = $o_query ? $o_query->row_array() : array();
			if($peopleData){
				$v_param = array(
		        	'COMPANY_ID'=>$_GET['companyID'],
					'USERNAME'=>$member['username']
		        );
		        $s_response = APIconnectorUser("companyallowededituserdata_add", $variables->loggID, $variables->sessionID, $v_param);
		        $v_response = json_decode($s_response, TRUE);
		        if($v_response['data'] == 'OK'){
					$accessesGiven++;
		        }
			}
		}
		echo $accessesGiven ." ".$formText_AccessesGiven_output;

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="migrateData" value="Add Accesses">

		</div>
	</form>
</div>
