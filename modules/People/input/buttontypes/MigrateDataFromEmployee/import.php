<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);

	if(isset($_POST['migrateData'])) {
		//customer
		$s_sql = "SELECT * FROM employee ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$employees = $o_query ? $o_query->result_array() : array();
		foreach($employees as $employee){
			$s_sql = "SELECT * FROM people WHERE id = ? ORDER BY id ASC";
			$o_query = $o_main->db->query($s_sql, $employee['id']);
			$people = $o_query ? $o_query->row_array() : array();

			$fields_to_update = "name = '".($employee['name'])."',
			email = '".($employee['email'])."',
			external_employee_id = '".($employee['accountingEmployeeId'])."',
			bankAccountNr = '".($employee['bankAccountNr'])."',
			streetadress = '".($employee['streetadress'])."',
			city = '".($employee['city'])."',
			postalnumber = '".($employee['postalnumber'])."',
			personNumber = '".($employee['personNumber'])."',
			emailSignature = '".($employee['emailSignature'])."',
			emailAddress = '".($employee['emailAddress'])."',
			emailPassword = '".($employee['emailPassword'])."',
			emailCalDavUrl = '".($employee['emailCalDavUrl'])."',
			emailCalendarActivateSharing = '".($employee['emailCalendarActivateSharing'])."',
			hourlyBudgetCost = '".($employee['hourlyBudgetCost'])."',
			comment = '".($employee['comment'])."'";

			if(count($people) == 0) {
				$o_main->db->query("INSERT INTO people SET id = '".($employee['id'])."',
					createdBy = '".($employee['createdBy'])."',
					created = '".($employee['created'])."',
					updatedBy = '".($employee['updatedBy'])."',
					updated = '".($employee['updated'])."',
					sortnr = '".($employee['sortnr'])."',
					content_status = '".($employee['content_status'])."',".$fields_to_update);
			} else {
				$o_main->db->query("UPDATE people SET
					".$fields_to_update."
					WHERE id = ?", array($people['id']));
			}
		}

		$s_sql = "SELECT * FROM employeesalary ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$employeesalaries = $o_query ? $o_query->result_array() : array();
		foreach($employeesalaries as $employee){
			$s_sql = "SELECT * FROM peoplesalary WHERE id = ? ORDER BY id ASC";
			$o_query = $o_main->db->query($s_sql, $employee['id']);
			$people = $o_query ? $o_query->row_array() : array();

			$fields_to_update = "peopleId = '".($employee['employeeId'])."',
			dateFrom = '".($employee['dateFrom'])."',
			stdOrIndividualRate = '".($employee['stdOrIndividualRate'])."',
			standardWageRateId = '".($employee['standardWageRateId'])."',
			rate = '".($employee['rate'])."',
			individualRateSalaryCode = '".($employee['individualRateSalaryCode'])."'";

			if(count($people) == 0) {
				$o_main->db->query("INSERT INTO peoplesalary SET id = '".($employee['id'])."',
					createdBy = '".($employee['createdBy'])."',
					created = '".($employee['created'])."',
					updatedBy = '".($employee['updatedBy'])."',
					updated = '".($employee['updated'])."',
					sortnr = '".($employee['sortnr'])."',
					content_status = '".($employee['content_status'])."',".$fields_to_update);
			} else {
				$o_main->db->query("UPDATE peoplesalary SET
					".$fields_to_update."
					WHERE id = ?", array($people['id']));
			}
		}
		$s_sql = "SELECT * FROM employee_comments ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$employeesalaries = $o_query ? $o_query->result_array() : array();
		foreach($employeesalaries as $employee){
			$s_sql = "SELECT * FROM people_comments WHERE id = ? ORDER BY id ASC";
			$o_query = $o_main->db->query($s_sql, $employee['id']);
			$people = $o_query ? $o_query->row_array() : array();

			$fields_to_update = "comment = '".($employee['comment'])."',
			peopleId = '".($employee['employeeId'])."'";

			if(count($people) == 0) {
				$o_main->db->query("INSERT INTO people_comments SET id = '".($employee['id'])."',
					createdBy = '".($employee['createdBy'])."',
					created = '".($employee['created'])."',
					updatedBy = '".($employee['updatedBy'])."',
					updated = '".($employee['updated'])."',
					sortnr = '".($employee['sortnr'])."',
					content_status = '".($employee['content_status'])."',".$fields_to_update);
			} else {
				$o_main->db->query("UPDATE people_comments SET
					".$fields_to_update."
					WHERE id = ?", array($people['id']));
			}
		}
		$s_sql = "SELECT * FROM employee_employerconnection ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$employeesalaries = $o_query ? $o_query->result_array() : array();
		foreach($employeesalaries as $employee){
			$s_sql = "SELECT * FROM people_employerconnection WHERE id = ? ORDER BY id ASC";
			$o_query = $o_main->db->query($s_sql, $employee['id']);
			$people = $o_query ? $o_query->row_array() : array();

			$fields_to_update = "accountingEmployeeId = '".($employee['accountingEmployeeId'])."',
			peopleId = '".($employee['employeeId'])."',
			employerId = '".($employee['employerId'])."'";

			if(count($people) == 0) {
				$o_main->db->query("INSERT INTO people_employerconnection SET id = '".($employee['id'])."',
					createdBy = '".($employee['createdBy'])."',
					created = '".($employee['created'])."',
					updatedBy = '".($employee['updatedBy'])."',
					updated = '".($employee['updated'])."',
					sortnr = '".($employee['sortnr'])."',
					content_status = '".($employee['content_status'])."',".$fields_to_update);
			} else {
				$o_main->db->query("UPDATE people_employerconnection SET
					".$fields_to_update."
					WHERE id = ?", array($people['id']));
			}
		}
		$peopleModuleId = 0;

		$s_sql = "SELECT * FROM moduledata WHERE name='People' ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$peopleModule = $o_query ? $o_query->row_array() : array();
		if($peopleModule) $peopleModuleId = $peopleModule['uniqueID'];

		$s_sql = "SELECT sys_filearchive_folder.*, sys_filearchive_file_version.id as file_version_id, sys_filearchive_file_version.file, sys_filearchive_file_version.name FROM sys_filearchive_folder
		LEFT OUTER JOIN sys_filearchive_file ON sys_filearchive_file.folder_id = sys_filearchive_folder.id
		LEFT OUTER JOIN sys_filearchive_file_version ON sys_filearchive_file_version.file_id = sys_filearchive_file.id AND status = 1
		WHERE connected_content_table = 'employee' ORDER BY id ASC";
		$o_query = $o_main->db->query($s_sql);
		$employeesalaries = $o_query ? $o_query->result_array() : array();
		foreach($employeesalaries as $employee){
			$s_sql = "SELECT * FROM people_files WHERE id = ? ORDER BY id ASC";
			$o_query = $o_main->db->query($s_sql, $employee['file_version_id']);
			$people = $o_query ? $o_query->row_array() : array();

			$fields_to_update = "
			moduleID = '".$peopleModuleId."',
			file = '".($employee['file'])."',
			filename = '".($employee['name'])."',
			peopleId = '".($employee['connected_content_id'])."'";

			if(count($people) == 0) {
				$o_main->db->query("INSERT INTO people_files SET id = '".($employee['file_version_id'])."',
					createdBy = '".($employee['createdBy'])."',
					created = '".($employee['created'])."',
					updatedBy = '".($employee['updatedBy'])."',
					updated = '".($employee['updated'])."',
					sortnr = '".($employee['sortnr'])."',
					content_status = '".($employee['content_status'])."',".$fields_to_update);
			} else {
				$o_main->db->query("UPDATE people_files SET
					".$fields_to_update."
					WHERE id = ?", array($people['id']));
			}
		}


	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >

		<div class="formRow submitRow">
			<input type="submit" name="migrateData" value="Migrate Data">

		</div>
	</form>
</div>
