<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
	ini_set('max_execution_time', 120);


	if(isset($_POST['fixNewContactpersonStructure'])) {
		$increase_by = $_POST['increase_by'];
		if($increase_by > 0) {
			$people_contactperson_type = 2;
			$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
			$o_query = $o_main->db->query($sql);
			$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
			$o_query = $o_main->db->get('accountinfo');
			$accountinfo = $o_query ? $o_query->row_array() : array();
			if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
				$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
			}
			if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
			{
				$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
			}

			$s_sql = "SELECT * FROM people";
			$o_query = $o_main->db->query($s_sql);
			$people = ($o_query ? $o_query->result_array() : array());

			foreach($people as $person) {
				$person['id'] = $person['id'] + $increase_by;
				$person['type'] = $people_contactperson_type;
				$person['middlename'] = $person['middle_name'];
				$person['lastname'] = $person['last_name'];
				$person['title'] = $person['job_title'];
				$person['mobile'] = $person['phone'];
				$person['mobile_prefix'] = $person['phone_prefix'];

				unset($person['middle_name']);
				unset($person['last_name']);
				unset($person['job_title']);
				unset($person['phone']);
				unset($person['phone_prefix']);
				unset($person['workIdCard']);

				$query = $o_main->db->insert('contactperson', $person);
			}

			$s_sql = "UPDATE checklist_report SET responsible_person_id = responsible_person_id + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'checklist_report table was updated<br/>';
			} else {
				echo '<span style="color:red;">checklist_report table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE customer_tasks SET employeeId = employeeId + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'customer_tasks table was updated<br/>';
			} else {
				echo '<span style="color:red;">customer_tasks table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE inspectionreport SET emplyeeId = emplyeeId + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'inspectionreport table was updated<br/>';
			} else {
				echo '<span style="color:red;">inspectionreport table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE offer SET seller_people_id = seller_people_id  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'offer table was updated<br/>';
			} else {
				echo '<span style="color:red;">offer table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE peoplesalary SET peopleId = peopleId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'peoplesalary table was updated<br/>';
			} else {
				echo '<span style="color:red;">peoplesalary table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE peoplesalary_group SET peopleId = peopleId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'peoplesalary_group table was updated<br/>';
			} else {
				echo '<span style="color:red;">peoplesalary_group table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE people_comments SET peopleId = peopleId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'people_comments table was updated<br/>';
			} else {
				echo '<span style="color:red;">people_comments table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE people_employerconnection SET peopleId = peopleId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'people_employerconnection table was updated<br/>';
			} else {
				echo '<span style="color:red;">people_employerconnection table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE people_files SET peopleId = peopleId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'people_files table was updated<br/>';
			} else {
				echo '<span style="color:red;">people_files table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE people_selfdefined_values SET people_id = people_id  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'people_selfdefined_values table was updated<br/>';
			} else {
				echo '<span style="color:red;">people_selfdefined_values table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2 SET seller_people_id = seller_people_id  + $increase_by,
			employeeId = employeeId + $increase_by, invoiceresponsibleId = invoiceresponsibleId + $increase_by,
			workplaceleaderId = workplaceleaderId + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2 table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2 table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2_admin_access_clean_app_users SET employeeId = employeeId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2_admin_access_clean_app_users table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2_admin_access_clean_app_users table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2_default_role SET employeeId = employeeId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2_default_role table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2_default_role table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2_employee_allow_add_workline SET employeeId = employeeId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2_employee_allow_add_workline table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2_employee_allow_add_workline table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2_repeating_work SET employeeId = employeeId  + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2_repeating_work table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2_repeating_work table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE project2_periods SET workplaceleaderId  = workplaceleaderId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'project2_periods table was updated<br/>';
			} else {
				echo '<span style="color:red;">project2_periods table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE prospect SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'prospect table was updated<br/>';
			} else {
				echo '<span style="color:red;">prospect table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE prospectcontactpoint SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'prospectcontactpoint table was updated<br/>';
			} else {
				echo '<span style="color:red;">prospectcontactpoint table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE prospect_reporting SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'prospect_reporting table was updated<br/>';
			} else {
				echo '<span style="color:red;">prospect_reporting table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE prospect_status_type_item_sorting SET employee_id  = employee_id   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'prospect_status_type_item_sorting table was updated<br/>';
			} else {
				echo '<span style="color:red;">prospect_status_type_item_sorting table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE repeatingorderinspection SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'repeatingorderinspection table was updated<br/>';
			} else {
				echo '<span style="color:red;">repeatingorderinspection table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE repeatingorder_admin_access_clean_app_users SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'repeatingorder_admin_access_clean_app_users table was updated<br/>';
			} else {
				echo '<span style="color:red;">repeatingorder_admin_access_clean_app_users table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE repeatingorder_employee_allow_add_workline SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'repeatingorder_employee_allow_add_workline table was updated<br/>';
			} else {
				echo '<span style="color:red;">repeatingorder_employee_allow_add_workline table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE repeatingorder_fixedsalary_worker SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'repeatingorder_fixedsalary_worker table was updated<br/>';
			} else {
				echo '<span style="color:red;">repeatingorder_fixedsalary_worker table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE reporderworklineworker SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'reporderworklineworker table was updated<br/>';
			} else {
				echo '<span style="color:red;">reporderworklineworker table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE salaryreporting_employee_aprovement SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'salaryreporting_employee_aprovement table was updated<br/>';
			} else {
				echo '<span style="color:red;">salaryreporting_employee_aprovement table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE salaryreportline SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'salaryreportline table was updated<br/>';
			} else {
				echo '<span style="color:red;">salaryreportline table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE timeregisteringline SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'timeregisteringline table was updated<br/>';
			} else {
				echo '<span style="color:red;">timeregisteringline table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE timeregisteringreport SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'timeregisteringreport table was updated<br/>';
			} else {
				echo '<span style="color:red;">timeregisteringreport table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE worker SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'worker table was updated<br/>';
			} else {
				echo '<span style="color:red;">worker table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE workgroupleader SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'workgroupleader table was updated<br/>';
			} else {
				echo '<span style="color:red;">workgroupleader table was NOT updated<br/></span>';
			}

			$s_sql = "UPDATE workleader SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'workleader table was updated<br/>';
			} else {
				echo '<span style="color:red;">workleader table was NOT updated<br/></span>';
			}
			$s_sql = "UPDATE workplanlineworker SET employeeId  = employeeId   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'workplanlineworker table was updated<br/>';
			} else {
				echo '<span style="color:red;">workplanlineworker table was NOT updated<br/></span></span>';
			}
			
			$s_sql = "UPDATE postfeed_tagged_people SET people_id  = people_id   + $increase_by";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				echo 'postfeed_tagged_people table was updated<br/>';
			} else {
				echo '<span style="color:red;">postfeed_tagged_people table was NOT updated<br/></span></span>';
			}


		} else {
			echo $formText_PleaseInputValueMoreThan0_button;
		}
	}
	$increase_by = "";

	$s_sql = "SHOW TABLE STATUS LIKE 'contactperson'";
	$o_query = $o_main->db->query($s_sql);
	$data = ($o_query ? $o_query->row_array() : array());

	$next_increment = $data['Auto_increment'];
	$increase_by = 100;
	if($next_increment > 100){
		$increase_by = 1000;
	} else if($next_increment > 1000) {
		$increase_by = 10000;
	} else if($next_increment > 10000) {
		$increase_by = 100000;
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="">
		<div class="formRow submitRow">
			<div>
				<label><?php echo $formText_IncreaseBy_button;?></label>
				<input type="text" name="increase_by" value="<?php echo $increase_by;?>">
			</div>
			<input type="submit" name="fixNewContactpersonStructure" value="Fix new contacperson structure">
		</div>
	</form>
</div>
