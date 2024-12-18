<?php
$customer_id = $v_data['params']['customer_id'];


$s_sql = "SELECT subscriptionmulti.* FROM draw_case
LEFT OUTER JOIN creditor_getynet_pay ON creditor_getynet_pay.id = draw_case.creditor_id
LEFT OUTER JOIN customer c1 ON c1.id = creditor_getynet_pay.customer_id
LEFT OUTER JOIN customer c2 ON c2.id = draw_case.customer_id
LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.customerId = c2.id
WHERE c1.id = ?
ORDER BY subscriptionmulti.id DESC";
$o_query = $o_main->db->query($s_sql, array($customer_id));

$subscriptions = array();

if ($o_query && $o_query->num_rows()) {
    $v_return['status'] = 1;
	foreach ($o_query->result_array() as $row) {

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
        if(!$contact_person){
            $s_sql = "SELECT contactperson.* FROM contactperson_role_conn
            LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
            WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
            ORDER BY contactperson_role_conn.role DESC";
            $o_query = $o_main->db->query($s_sql, array($row['id']));
            $contact_person = $o_query ? $o_query->row_array() : array();
        }
        $row['contact_person'] = $contact_person;

        $s_sql = "SELECT * FROM customer WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['customerId']));
        $debitor = ($o_query ? $o_query->row_array() : array());
        $row['debitor'] = $debitor;

        array_push($subscriptions, $row);
    }
}

$v_return['data'] = $subscriptions;
