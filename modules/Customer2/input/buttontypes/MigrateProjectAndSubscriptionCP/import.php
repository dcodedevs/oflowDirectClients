<?php

	// error_reporting(E_ALL);
	// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);
	if(isset($_POST['fixFiles'])) {
		$s_sql = "select * from project2 where contactPerson > 0";
		$o_query = $o_main->db->query($s_sql);
		$project2 = $o_query ? $o_query->result_array() : array();

		foreach($project2 as $project){
			$projectId = $project['id'];
			$contactPersonId = $project['contactPerson'];
			$s_sql = "SELECT * FROM contactperson_role_conn WHERE project2_id = ? and contactperson_id = ?";
			$o_query = $o_main->db->query($s_sql, array($projectId, $contactPersonId));
			$contactperson_conn = $o_query ? $o_query->row_array() : array();
			if($contactperson_conn){
				// $s_sql = "UPDATE contactperson_role_conn SET
				// updated = now(),
				// updatedBy= ?,
				// project2_id = ?,
				// contactperson_id = ?,
				// role = 0
				// WHERE id = ?";
				// $o_main->db->query($s_sql, array($variables->loggID, $projectId, $contactPersonId, $contactperson_conn['id']));
				// $contactperson_conn_id = $contactperson_conn['id'];
			} else {
				$s_sql = "INSERT INTO contactperson_role_conn SET
				created = now(),
				createdBy= ?,
				project2_id = ?,
				contactperson_id = ?,
				role = 0";
				$o_main->db->query($s_sql, array($variables->loggID, $projectId, $contactPersonId));
				$contactperson_conn_id = $o_main->db->insert_id();
			}
		}


		$s_sql = "select * from subscriptionmulti where contactPerson > 0";
		$o_query = $o_main->db->query($s_sql);
		$subscriptionmulti = $o_query ? $o_query->result_array() : array();

		foreach($subscriptionmulti as $subscription){
			$projectId = $subscription['id'];
			$contactPersonId = $subscription['contactPerson'];
			$s_sql = "SELECT * FROM contactperson_role_conn WHERE subscriptionmulti_id = ? and contactperson_id = ?";
			$o_query = $o_main->db->query($s_sql, array($projectId, $contactPersonId));
			$contactperson_conn = $o_query ? $o_query->row_array() : array();
			if($contactperson_conn){
				// $s_sql = "UPDATE contactperson_role_conn SET
				// updated = now(),
				// updatedBy= ?,
				// subscriptionmulti_id = ?,
				// contactperson_id = ?,
				// role = 0
				// WHERE id = ?";
				// $o_main->db->query($s_sql, array($variables->loggID, $projectId, $contactPersonId, $contactperson_conn['id']));
				// $contactperson_conn_id = $contactperson_conn['id'];
			} else {
				$s_sql = "INSERT INTO contactperson_role_conn SET
				created = now(),
				createdBy= ?,
				subscriptionmulti_id = ?,
				contactperson_id = ?,
				role = 0";
				$o_main->db->query($s_sql, array($variables->loggID, $projectId, $contactPersonId));
				$contactperson_conn_id = $o_main->db->insert_id();
			}
		}

	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data" action="">
		<div class="formRow submitRow">
			<input type="submit" name="fixFiles" value="Migrate projet2 and subscription contact persons into new structure">

		</div>
	</form>
</div>
