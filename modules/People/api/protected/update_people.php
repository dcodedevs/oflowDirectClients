<?php
$peopleSyncData = $v_data['params']['people'];
$people_comments = $v_data['params']['people_comments'];
$people_selfdefined_fields = $v_data['params']['people_selfdefined_fields'];
$people_selfdefined_values = $v_data['params']['people_selfdefined_values'];
$peoplesalary_group = $v_data['params']['peoplesalary_group'];
$peoplesalary = $v_data['params']['peoplesalary'];
$people_employerconnection = $v_data['params']['people_employerconnection'];
$people_files = $v_data['params']['people_files'];

$peopleId = $peopleSyncData['id'];

if ($peopleId) {
    $sql = "UPDATE people SET
    updated = now(),
    updatedBy='".$o_main->db->escape_str($peopleSyncData['updatedBy'])."',
    sortnr='".$o_main->db->escape_str($peopleSyncData['sortnr'])."',
    content_status='".$o_main->db->escape_str($peopleSyncData['content_status'])."',
    name='".$o_main->db->escape_str($peopleSyncData['name'])."',
    middle_name='".$o_main->db->escape_str($peopleSyncData['middle_name'])."',
    last_name='".$o_main->db->escape_str($peopleSyncData['last_name'])."',
    email='".$o_main->db->escape_str($peopleSyncData['email'])."',
    phone='".$o_main->db->escape_str($peopleSyncData['phone'])."',
    phone_prefix='".$o_main->db->escape_str($peopleSyncData['phone_prefix'])."',
    job_title = '".$o_main->db->escape_str($peopleSyncData['job_title'])."',
    external_employee_id = '".$o_main->db->escape_str($peopleSyncData['external_employee_id'])."',
    streetadress = '".$o_main->db->escape_str($peopleSyncData['streetadress'])."',
    postalnumber = '".$o_main->db->escape_str($peopleSyncData['postalnumber'])."',
    city = '".$o_main->db->escape_str($peopleSyncData['city'])."',
    personNumber = '".$o_main->db->escape_str($peopleSyncData['personNumber'])."',
    bankAccountNr = '".$o_main->db->escape_str($peopleSyncData['bankAccountNr'])."',
    emailSignature = '".$o_main->db->escape_str($peopleSyncData['emailSignature'])."',
    emailAddress = '".$o_main->db->escape_str($peopleSyncData['emailAddress'])."',
    emailPassword = '".$o_main->db->escape_str($peopleSyncData['emailPassword'])."',
    emailCalDavUrl = '".$o_main->db->escape_str($peopleSyncData['emailCalDavUrl'])."',
    emailCalendarActivateSharing = '".$o_main->db->escape_str($peopleSyncData['emailCalendarActivateSharing'])."',
    hourlyBudgetCost = '".$o_main->db->escape_str($peopleSyncData['hourlyBudgetCost'])."',
    comment = '".$o_main->db->escape_str($peopleSyncData['comment'])."',
    workIdCardExpireDate = '".$o_main->db->escape_str($peopleSyncData['workIdCardExpireDate'])."',
    seniorityStartDate = '".$o_main->db->escape_str($peopleSyncData['seniorityStartDate'])."'
    WHERE id = $peopleId";
    $o_query = $o_main->db->query($sql);
} else {
    $sql = "INSERT INTO people SET
    created = now(),
    createdBy='".$o_main->db->escape_str($peopleSyncData['createdBy'])."',
    sortnr='".$o_main->db->escape_str($peopleSyncData['sortnr'])."',
    content_status='".$o_main->db->escape_str($peopleSyncData['content_status'])."',
    name='".$o_main->db->escape_str($peopleSyncData['name'])."',
    middle_name='".$o_main->db->escape_str($peopleSyncData['middle_name'])."',
    last_name='".$o_main->db->escape_str($peopleSyncData['last_name'])."',
    email='".$o_main->db->escape_str($peopleSyncData['email'])."',
    phone='".$o_main->db->escape_str($peopleSyncData['phone'])."',
    phone_prefix='".$o_main->db->escape_str($peopleSyncData['phone_prefix'])."',
    job_title = '".$o_main->db->escape_str($peopleSyncData['job_title'])."',
    external_employee_id = '".$o_main->db->escape_str($peopleSyncData['external_employee_id'])."',
    streetadress = '".$o_main->db->escape_str($peopleSyncData['streetadress'])."',
    postalnumber = '".$o_main->db->escape_str($peopleSyncData['postalnumber'])."',
    city = '".$o_main->db->escape_str($peopleSyncData['city'])."',
    personNumber = '".$o_main->db->escape_str($peopleSyncData['personNumber'])."',
    bankAccountNr = '".$o_main->db->escape_str($peopleSyncData['bankAccountNr'])."',
    emailSignature = '".$o_main->db->escape_str($peopleSyncData['emailSignature'])."',
    emailAddress = '".$o_main->db->escape_str($peopleSyncData['emailAddress'])."',
    emailPassword = '".$o_main->db->escape_str($peopleSyncData['emailPassword'])."',
    emailCalDavUrl = '".$o_main->db->escape_str($peopleSyncData['emailCalDavUrl'])."',
    emailCalendarActivateSharing = '".$o_main->db->escape_str($peopleSyncData['emailCalendarActivateSharing'])."',
    hourlyBudgetCost = '".$o_main->db->escape_str($peopleSyncData['hourlyBudgetCost'])."',
    comment = '".$o_main->db->escape_str($peopleSyncData['comment'])."',
    workIdCardExpireDate = '".$o_main->db->escape_str($peopleSyncData['workIdCardExpireDate'])."',
    seniorityStartDate = '".$o_main->db->escape_str($peopleSyncData['seniorityStartDate'])."',
    id = '".$o_main->db->escape_str($peopleSyncData['id'])."'";
    $o_query = $o_main->db->query($sql);
}

if($o_query){
    $error = false;
    foreach($people_comments as $people_comment) {
        $s_sql = $o_main->db->query("SELECT * FROM people_comments WHERE id = ? AND createdBy = ?", array($people_comment['id'], $variables->loggID));
        if($s_sql->num_rows() == 1)
        {
            $s_sql = "UPDATE people_comments SET
            updated = now(),
            updatedBy=?,
            employeeId = ?
            comment=?
            WHERE id = ? AND createdBy=?";
            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_comment['employeeId'], $people_comment['comment'], $people_comment['id'], $variables->loggID));
        } else {
            $s_sql = "INSERT INTO people_comments SET
            id=NULL,
            moduleID = ?,
            created = now(),
            createdBy=?,
            peopleId = ?,
            comment=?";
            $o_query = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $people_comment['employeeId'], $people_comment['comment']));
        }
        if(!$o_query) {
            $error = true;
        }
    }

    foreach($people_comments as $people_comment) {
        $s_sql = $o_main->db->query("SELECT * FROM people_comments WHERE id = ? AND createdBy = ?", array($people_comment['id'], $variables->loggID));
        if($s_sql->num_rows() == 1)
        {
            $s_sql = "UPDATE people_comments SET
            updated = now(),
            updatedBy=?,
            employeeId = ?
            comment=?
            WHERE id = ? AND createdBy=?";
            $o_query = $o_main->db->query($s_sql, array($people_comment['updatedBy'], $people_comment['employeeId'], $people_comment['comment'], $people_comment['id'], $people_comment['createdBy']));
        } else {
            $s_sql = "INSERT INTO people_comments SET
            id=NULL,
            moduleID = ?,
            created = now(),
            createdBy=?,
            peopleId = ?,
            comment=?,
            id = ?";
            $o_query = $o_main->db->query($s_sql, array($people_comment['moduleID'], $people_comment['createdBy'], $people_comment['employeeId'], $people_comment['comment'], $people_comment['id']));
        }
        if(!$o_query) {
            $error = true;
        }
    }

    foreach($people_selfdefined_fields as $people_selfdefined_field) {
        $s_sql = $o_main->db->query("SELECT * FROM people_selfdefined_fields WHERE id = ?", array($people_selfdefined_field['id']));
        if($s_sql->num_rows() == 1)
        {
			$s_sql = "UPDATE people_selfdefined_fields SET
			updated = now(),
			updatedBy= ?,
			name= ?,
			type= ?
			WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($people_selfdefined_field['updatedBy'], $people_selfdefined_field['name'], $people_selfdefined_field['type'], $people_selfdefined_field['id']));
		}
		else if(intval($_POST['deleteResource']) == 0) {
			$s_sql = "INSERT INTO people_selfdefined_fields SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			name= ?,
			type= ?,
            id = ?";
			$o_query = $o_main->db->query($s_sql, array($people_selfdefined_field['moduleID'], $people_selfdefined_field['updatedBy'], $people_selfdefined_field['name'], $people_selfdefined_field['type'], $people_selfdefined_field['id']));
        }
        if(!$o_query) {
            $error = true;
        }
    }
    foreach($people_selfdefined_values as $people_selfdefined_value) {
        $s_sql = "SELECT * FROM people_selfdefined_values WHERE  id = ?";

        $o_query = $o_main->db->query($s_sql, array($people_selfdefined_value['id']));
        $selfdefinedFieldValue = $o_query ? $o_query->row_array() : array();
        if($selfdefinedFieldValue){
            $sql = "UPDATE people_selfdefined_values SET
            updated = now(),
            updatedBy='".$people_selfdefined_value['updatedBy']."',
            value='".$o_main->db->escape_str($people_selfdefined_value['value'])."',
            people_id='".$o_main->db->escape_str($people_selfdefined_value['people_id'])."',
            selfdefined_fields_id='".$o_main->db->escape_str($people_selfdefined_value['selfdefined_fields_id'])."'
            WHERE id = '".$o_main->db->escape_str($people_selfdefined_value['id'])."'";

            $o_query = $o_main->db->query($sql);

        } else {
            $sql = "INSERT INTO people_selfdefined_values SET
            created = now(),
            createdBy='".$people_selfdefined_value['createdBy']."',
            people_id='".$o_main->db->escape_str($people_selfdefined_value['people_id'])."',
            selfdefined_fields_id='".$o_main->db->escape_str($people_selfdefined_value['selfdefined_fields_id'])."',
            value='".$o_main->db->escape_str($people_selfdefined_value['value'])."',
            id='".$o_main->db->escape_str($people_selfdefined_value['id'])."'";

            $o_query = $o_main->db->query($sql);
        }
        if(!$o_query) {
            $error = true;
        }
    }

    foreach($peoplesalary_group as $peoplesalary_group_single) {

        $s_fields = ",
		name = '".$o_main->db->escape_str($peoplesalary_group_single['name'])."',
		type = '".$o_main->db->escape_str($peoplesalary_group_single['type'])."',
		peopleId = '".$o_main->db->escape_str($peoplesalary_group_single['peopleId'])."'";

        $s_sql = $o_main->db->query("SELECT * FROM peoplesalary_group WHERE id = ?", array($peoplesalary_group_single['id']));
        if($s_sql->num_rows() == 1)
        {
			$s_sql = "UPDATE peoplesalary_group SET
			updated = now(),
			updatedBy = ?".$s_fields."
			WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($peoplesalary_group_single['updatedBy'], $peoplesalary_group_single['id']));
		} else {
			$s_sql = "INSERT INTO peoplesalary_group SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy = ?".$s_fields;
			$o_query = $o_main->db->query($s_sql, array($peoplesalary_group_single['moduleID'], $peoplesalary_group_single['createdBy']));
		}
        if(!$o_query) {
            $error = true;
        }
    }
    foreach($peoplesalary as $peoplesalary_single) {

        $s_fields = ",
        dateFrom ='".$o_main->db->escape_str($peoplesalary_single['dateFrom'])."',
        stdOrIndividualRate = '".$o_main->db->escape_str($peoplesalary_single['stdOrIndividualRate'])."',
        standardWageRateId = '".$o_main->db->escape_str($peoplesalary_single['standardWageRateId'])."',
        rate = '".$o_main->db->escape_str($peoplesalary_single['rate'])."',
        individualRateSalaryCode = '".$o_main->db->escape_str($peoplesalary_single['individualRateSalaryCode'])."',
        peopleId = '".$o_main->db->escape_str($peoplesalary_single['peopleId'])."',
        hourlyRate = '".$o_main->db->escape_str($peoplesalary_single['hourlyRate'])."',
        peoplesalary_group_id = '".$o_main->db->escape_str($peoplesalary_single['peoplesalary_group_id'])."'";

        $s_sql = $o_main->db->query("SELECT * FROM peoplesalary WHERE id = ?", array($peoplesalary_single['id']));
        if($s_sql->num_rows() == 1)
        {
            $s_sql = "UPDATE peoplesalary SET
            updated = now(),
            updatedBy = ?".$s_fields."
            WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($peoplesalary_single['updatedBy'], $peoplesalary_single['id']));
        } else {
            $s_sql = "INSERT INTO peoplesalary SET
            id=NULL,
            moduleID = ?,
            created = now(),
            createdBy = ?".$s_fields;
            $o_query = $o_main->db->query($s_sql, array($peoplesalary_single['moduleID'], $peoplesalary_single['createdBy']));
        }
        if(!$o_query) {
            $error = true;
        }
    }

    foreach($people_employerconnection as $people_employerconnection_single) {
        $s_sql = $o_main->db->query("SELECT * FROM people_employerconnection WHERE id = ?", array($people_employerconnection_single['id']));
        if($s_sql->num_rows() == 1) {
			$s_sql = "UPDATE people_employerconnection SET
			updated = now(),
			updatedBy = ?,
			accountingEmployeeId = ?
			WHERE employerId = ? AND peopleId = ?";
			$o_query = $o_main->db->query($s_sql, array($people_employerconnection_single['updatedBy'], $people_employerconnection_single['accountingEmployeeId'],
            $people_employerconnection_single['employerId'], $people_employerconnection_single['peopleId']));
		} else {
			$s_sql = "INSERT INTO people_employerconnection SET
			created = now(),
			createdBy = ?,
			accountingEmployeeId = ?,
			employerId = ?,
			peopleId = ?";
			$o_query = $o_main->db->query($s_sql, array($people_employerconnection_single['createdBy'], $people_employerconnection_single['accountingEmployeeId'],
            $people_employerconnection_single['employerId'], $people_employerconnection_single['peopleId']));

		}
        if(!$o_query) {
            $error = true;
        }
    }

    foreach($people_files as $people_file) {
        $s_sql = $o_main->db->query("SELECT * FROM people_files WHERE id = ?", array($people_file['id']));
        if($s_sql->num_rows() == 1) {
            $s_sql = "UPDATE people_files SET
            moduleID = ?,
            updated = now(),
            updatedBy = ?,
            filename = ?,
            peopleId = ?
            WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($people_file['moduleID'], $people_file['updatedBy'], $people_file['filename'], $people_file['peopleId'], $people_file['id']));

		} else {
            $s_sql = "INSERT INTO people_files SET
            id=?,
            moduleID = ?,
            created = now(),
            createdBy = ?,
            filename = ?,
            peopleId = ?";
            $o_query = $o_main->db->query($s_sql, array($people_file['id'], $people_file['moduleID'], $people_file['createdBy'], $people_file['filename'], $people_file['peopleId']));

		}
        if(!$o_query) {
            $error = true;
        }
    }
    if(!$error) {
        $v_return['status'] = 1;
    } else {
       $v_return['status'] = 0;
   }
} else {
    $v_return['status'] = 0;
}
?>
