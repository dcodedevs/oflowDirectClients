<?php
$customer_id = $v_data['params']['customer_id'];
$o_main->db->order_by('id', 'DESC');
$o_query = $o_main->db->get_where('subscriptionmulti', array('customerId' => $customer_id));

$subscriptions = array();

if ($o_query && $o_query->num_rows()) {
    $subscriptionsResult = $o_query->result_array();

    $s_sql = "select * from repeatingorder_accountconfig";
    $o_query = $o_main->db->query($s_sql);
    $v_repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());
    $endMonthDate = date("Y-m-d");
    $startMonthDate = date("Y-m-01", strtotime("-1 month", strtotime($endMonthDate)));

    $v_return['status'] = 1;

    $s_sql = "SELECT * FROM employeeworkplan_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($s_sql);
    $employeeworkplan_basisconfig = ($o_query ? $o_query->row_array() : array());

    $s_sql = "SELECT * FROM employeeworkplan_accountconfig ORDER BY id";
    $o_query = $o_main->db->query($s_sql);
    $employeeworkplan_accountconfig = ($o_query ? $o_query->row_array() : array());

    $default_breaktime = "0.50";
    $default_breaktime_start = 0;
    $add_breaktime_after_hours = 0;
    if($employeeworkplan_basisconfig['suggest_breaktime_after_exceeding_hours'] > 0){
        $default_breaktime_start = number_format($employeeworkplan_basisconfig['suggest_breaktime_after_exceeding_hours'], 2, ".", "");
    }
    if($employeeworkplan_accountconfig['breaktime_config'] == 2){
        if($employeeworkplan_accountconfig['suggest_breaktime_after_exceeding_hours'] > 0){
            $default_breaktime_start = number_format($employeeworkplan_accountconfig['suggest_breaktime_after_exceeding_hours'], 2, ".", "");
        }
    }
    if($employeeworkplan_basisconfig['suggested_breaktime'] > 0){
        $default_breaktime = number_format($employeeworkplan_basisconfig['suggested_breaktime'], 2, ".", "");
    }
    if($employeeworkplan_accountconfig['breaktime_config'] == 2){
        if($employeeworkplan_accountconfig['suggested_breaktime'] > 0){
            $default_breaktime = number_format($employeeworkplan_accountconfig['suggested_breaktime'], 2, ".", "");
        }
    }
    if($employeeworkplan_basisconfig['break_starts_after_hours'] > 0){
        $add_breaktime_after_hours = number_format($employeeworkplan_basisconfig['break_starts_after_hours'], 2, ".", "");
    }
    if($employeeworkplan_accountconfig['breaktime_config'] == 2){
        if($employeeworkplan_accountconfig['break_starts_after_hours'] > 0){
            $add_breaktime_after_hours = number_format($employeeworkplan_accountconfig['break_starts_after_hours'], 2, ".", "");
        }
    }
    if($employeeworkplan_accountconfig['breaktime_config'] == 1){
        $default_breaktime_start = 0;
    }

	foreach ($subscriptionsResult as $row) {

        // Get connected file folder
        $s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
        $o_query = $o_main->db->query($s_sql, array('subscriptionmulti', $row['id']));
        $folder_data = $o_query ? $o_query->row_array() : array();

        // Get files in that folder
        $s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
        $o_query = $o_main->db->query($s_sql, array($folder_data['id']));
        $files = $o_query ? $o_query->result_array() : array();

        // Get public tag
        $o_query = $o_main->db->query("SELECT * FROM sys_filearchive_other_tags WHERE id_name = 'make_subscription_file_public'");
        $public_tag = $o_query ? $o_query->row_array() : array();
        $public_tag_id = $public_tag['id'];

        // Other tags group
        $o_query = $o_main->db->query("SELECT * FROM sys_filearchive_tag_group WHERE content_table = 'sys_filearchive_other_tags'");
        $other_tags_group = $o_query ? $o_query->row_array() : array();
        $other_tags_group_id = $other_tags_group['id'];

        // Public files
        $files_public = array();

        foreach($files as $file) {
            // Check if this file is tagged with "public tag"
            $sql = "SELECT * FROM sys_filearchive_tag_connection WHERE file_id = ? AND group_id = ? AND content_id = ?";
            $o_query = $o_main->db->query($sql, array($file['id'], $other_tags_group_id, $public_tag_id));
            if ($o_query && $o_query->num_rows()) {
                $sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? ORDER BY id DESC";
                $o_query = $o_main->db->query($sql, array($file['id']));
                $version_data = $o_query ? $o_query->row_array() : array();
                $file['version_id'] = $version_data['id'];
                $version_file = json_decode($version_data['file']);
                $file['name'] = $version_file[0][0];
                $file['file_url'] = $version_file[0][1][0];
                array_push($files_public, $file);
            }
        }



        // Add to subscription
        $row['files'] = $files_public;

        //new file structure
        $s_sql = "SELECT * FROM subscriptionmulti_files WHERE content_status < 2 AND subscriptionmulti_id = ? ORDER BY created DESC";
        $o_query = $o_main->db->query($s_sql, array($row['id']));
        $fileItems = ($o_query ? $o_query->result_array() : array());
        $row['new_files'] = $fileItems;

        $s_sql = "SELECT * FROM contactperson WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['contactPerson']));
        $contact_person = ($o_query ? $o_query->row_array() : array());
        $row['contact_person'] = $contact_person;
        if($v_data['params']['get_posts'] == 1){
            $s_sql = "SELECT * FROM messages_to_customer WHERE content_id = ? AND content_table='subscriptionmulti' ORDER BY date DESC";
            $o_query = $o_main->db->query($s_sql, array($row['id']));
            $posts = $o_query ? $o_query->result_array() : array();
            $row['posts'] = $posts;
        }
        $workplanlines = array();
        if($row['showWorkplanlinesToCustomer'] && $v_repeatingorder_accountconfig['activatePossibilityToShowWorklinesToCustomer']) {
            $s_sql = "SELECT workplanlineworker.*, CONCAT(COALESCE(contactperson.name, ''), ' ', COALESCE(contactperson.middlename, ''), ' ', COALESCE(contactperson.lastname, '')) as employeeName FROM workplanlineworker
            LEFT OUTER JOIN contactperson ON contactperson.id = workplanlineworker.employeeId
            WHERE workplanlineworker.repeatingOrderId = ?
            AND workplanlineworker.date >= ? AND workplanlineworker.date <= ? ORDER BY date DESC";
            $o_query = $o_main->db->query($s_sql, array($row['id'], $startMonthDate, $endMonthDate));
            $workplanlines = ($o_query ? $o_query->result_array() : array());

        }
        $row['workplanlines'] = $workplanlines;
        $row['default_breaktime_start'] = $default_breaktime_start;
        $row['default_breaktime'] = $default_breaktime;
        array_push($subscriptions, $row);
    }
}

$v_return['data'] = $subscriptions;
