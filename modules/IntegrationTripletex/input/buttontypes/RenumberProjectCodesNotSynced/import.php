<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	// Ownercompany list
	$sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($sql);
	$ownercompany_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

	if(isset($_POST['migrateData'])) {
		$number = $_POST['next_number'];
		if($number > 0){
			$updatedNumber = 0;
			$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($number)."'";
			$o_query = $o_main->db->query($s_sql);
			$projectExisting = $o_query ? $o_query->row_array() : array();
			if(!$projectExisting){
				$s_sql = "SELECT * FROM projectforaccounting WHERE external_project_id is null OR external_project_id = ''";
				$o_query = $o_main->db->query($s_sql);
				$projects = $o_query ? $o_query->result_array() : array();
				if(count($projects) > 0) {
					foreach($projects as $project){
						$oldProjectNumber = $project['projectnumber'];
						if($oldProjectNumber != ""){
							$s_sql = "UPDATE projectforaccounting SET projectnumber = '".$o_main->db->escape_str($number)."', updated = now() WHERE id = '".$o_main->db->escape_str($project['id'])."'";
							$o_query = $o_main->db->query($s_sql);
							if($o_query){
								$nextCode = $number + 1;

								$s_sql = "UPDATE customer_accountconfig SET
								updated = now(),
								updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
								next_available_projectcode = '".$o_main->db->escape_str($nextCode)."'
								WHERE id = '".$o_main->db->escape_str($v_customer_accountconfig['id'])."'";
								$o_query = $o_main->db->query($s_sql);

								$s_sql = "UPDATE subscriptionmulti SET
								updated = now(),
								updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
								projectId = '".$o_main->db->escape_str($number)."'
								WHERE projectId = '".$o_main->db->escape_str($oldProjectNumber)."'";
								$o_query = $o_main->db->query($s_sql);

								$s_sql = "UPDATE project2 SET
								updated = now(),
								updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
								projectCode = '".$o_main->db->escape_str($number)."'
								WHERE projectCode = '".$o_main->db->escape_str($oldProjectNumber)."'";
								$o_query = $o_main->db->query($s_sql);

								$s_sql = "UPDATE customer_collectingorder SET
								updated = now(),
								updatedBy = '".$o_main->db->escape_str($variables->loggID)."',
								accountingProjectCode = '".$o_main->db->escape_str($number)."'
								WHERE accountingProjectCode = '".$o_main->db->escape_str($oldProjectNumber)."'";
								$o_query = $o_main->db->query($s_sql);

								$number = $nextCode;
								$updatedNumber++;
							} else {
								break;
							}
						}
					}
					echo $updatedNumber." ".$formText_ProjectsWereUpdated_output;
				} else {
					echo $formText_ThereAreNoProjectsThatAreNotSynced_output;
				}
			} else {
				echo $number." ".$formText_ProjectCodeIsNotAvailable_output;
			}
		} else {
			echo $formText_MissingNextNumber_output;
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		<div class="formRow submitRow">
			<div><label>Next number to use</label><input type="text" name="next_number" value=""/></div>
			<input type="submit" name="migrateData" value="Renumber Project Codes not synced">
		</div>
	</form>
</div>
