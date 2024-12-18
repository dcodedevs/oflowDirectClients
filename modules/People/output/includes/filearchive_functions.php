<?php

function check_filearchive_folder($o_main, $parent_folder_id_or_name, $content_query, $content_table, $name_field) {

    global $moduleID;
    global $variables;

    // Parent folder check
    if (is_string($parent_folder_id_or_name)) {
        $parent_folder_name = $parent_folder_id_or_name;
        $s_sql = "SELECT * FROM sys_filearchive_folder WHERE name = ? AND parent_id = '0'";
        $o_query = $o_main->db->query($s_sql, array($parent_folder_name));
        if($o_query && $o_query->num_rows()>0) {
            $parent_folder_data = $o_query->row_array();
        }
        if (!$parent_folder_data['id']) {
            $o_query = $o_main->db->query("INSERT INTO sys_filearchive_folder SET
                moduleID = ?,
                created = now(),
                createdBy= ?,
                parent_id = '0',
                name = ?,
                disallow_rename = '1',
                disallow_move = '1',
                disallow_delete = '1',
                disallow_store_items = '1',
                device_disallow_rename = '1',
                device_disallow_move = '1',
                device_disallow_delete = '1',
                device_disallow_store_items = '1'", array($moduleID, $variables->loggID, $parent_folder_name));
            $parent_folder_id = $o_main->db->insert_id();
            $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        } else {
            $parent_folder_id = $parent_folder_data['id'];
        }
    } else {
        $parent_folder_id = $parent_folder_id_or_name;
    }

    $return = array('parent_folder_id' => $parent_folder_id, 'folder_ids' => array());

    // Loop over content and create / rename / delete folder for each item
    if (is_string($content_query) && is_string($content_table) && is_string($name_field) ) {
        $content_ids = array();
        $content_rows = array();
        $o_query = $o_main->db->query($content_query);
        if($o_query && $o_query->num_rows()>0) {
            $content_rows = $o_query->result_array();
        }
        $content_result = mysql_query($content_query);
        foreach($content_rows as $content_row) {
            $content_ids[] = $content_row['id'];

            $folder_query = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ? AND parent_id = ?";
            // Exist
            $o_query = $o_main->db->query($folder_query, array($content_table, $content_row['id'], $parent_folder_id));
            if($o_query && $o_query->num_rows()>0) {
                $folder_data = $o_query->row_array();
                // Check if renamed?
                $return['folder_ids'][] = $folder_data['id'];
                if ($folder_data['name'] != $content_row[$name_field]) {

                    $o_main->db->query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy= ?, name = ? WHERE id = ?", array($variables->loggID, $content_row[$name_field], $folder_data['id']));
                    $return['renamed_folder_ids'][] = $folder_data['id'];
                    $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
                }
            }
            // Doesn't exist, create!
            else {
                $o_main->db->query("INSERT INTO sys_filearchive_folder SET
                    moduleID = ?,
                    created = now(),
                    createdBy= ?,
                    parent_id = ?,
                    name = ?,
                    connected_content_table = ?,
                    connected_content_id = ?,
                    disallow_rename = '1',
                    disallow_move = '1',
                    disallow_delete = '1',
                    disallow_store_items = '0',
                    device_disallow_rename = '1',
                    device_disallow_move = '1',
                    device_disallow_delete = '1',
                    device_disallow_store_items = '0'", array($moduleID, $variables->loggID, $parent_folder_id, $content_row[$name_field], $content_table, $content_row['id']));
                $return['folder_ids'][] = $o_main->db->insert_id();
                $return['created_folder_ids'][] = $o_main->db->insert_id();
                $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
            }
        }

        // Check for deleted content
        // $deleted_check_query = mysql_query("SELECT * FROM sys_filearchive_folder WHERE parent_id = '".$parent_folder_id."' AND connected_content_table = '".$content_table."'");
        // while ($folder = mysql_fetch_assoc($deleted_check_query)) {
        //     // if connected content doesn't exist
        //     if (!in_array($folder['connected_content_id'], $content_ids)) {
        //         mysql_query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy='".$variables->loggID."', name = '".$folder['name']." (deleted)', connected_content_table = '', connected_content_id = 0 WHERE id = '".$folder['id']."'");
        //         $return['deleted_folder_ids'][] = $folder['id'];
        //         mysql_query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        //     }
        // }
    }

    return $return;
}

// customized for subscriptions, hacky way
function check_filearchive_folder_customized ($o_main, $parent_folder_id_or_name, $content_query, $content_table, $name_field) {

    global $moduleID;
    global $variables;

    // Parent folder check
    if (is_string($parent_folder_id_or_name)) {
        $parent_folder_name = $parent_folder_id_or_name;

         $s_sql = "SELECT * FROM sys_filearchive_folder WHERE name = ? AND parent_id = '0'";
        $o_query = $o_main->db->query($s_sql, array($parent_folder_name));
        if($o_query && $o_query->num_rows()>0) {
            $parent_folder_data = $o_query->row_array();
        }
        if (!$parent_folder_data['id']) {
            $o_query = $o_main->db->query("INSERT INTO sys_filearchive_folder SET
                moduleID = ?,
                created = now(),
                createdBy= ?,
                parent_id = '0',
                name = ?,
                disallow_rename = '1',
                disallow_move = '1',
                disallow_delete = '1',
                disallow_store_items = '1',
                device_disallow_rename = '1',
                device_disallow_move = '1',
                device_disallow_delete = '1',
                device_disallow_store_items = '1'", array($moduleID, $variables->loggID, $parent_folder_name));
            $parent_folder_id = $o_main->db->insert_id();
        } else {
            $parent_folder_id = $parent_folder_data['id'];
        }
    } else {
        $parent_folder_id = $parent_folder_id_or_name;
    }

    $return = array('parent_folder_id' => $parent_folder_id, 'folder_ids' => array());

    // Loop over content and create / rename / delete folder for each item
    if (is_string($content_query) && is_string($content_table) && is_string($name_field) ) {
        $content_ids = array();
        $content_rows = array();
        $o_query = $o_main->db->query($content_query);
        if($o_query && $o_query->num_rows()>0) {
            $content_rows = $o_query->result_array();
        }
        $content_result = mysql_query($content_query);
        foreach($content_rows as $content_row) {
            $content_ids[] = $content_row['id'];
            $folder_query = mysql_query("SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ? AND parent_id = ?");
            $custom_folder_name = $content_row[$name_field]. ' (L'.$content_row['id'].')';
            // Exist
            $o_query = $o_main->db->query($folder_query, array($content_table, $content_row['id'], $parent_folder_id));
            if($o_query && $o_query->num_rows()>0) {
                $folder_data = $o_query->row_array();
                // Check if renamed?
                $return['folder_ids'][] = $folder_data['id'];

                if ($folder_data['name'] != $custom_folder_name) {
                    $o_main->db->query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy= ?, name = ? WHERE id = ?", array($variables->loggID, $custom_folder_name, $folder_data['id']));
                    $return['renamed_folder_ids'][] = $folder_data['id'];
                    $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
                }
            }
            // Doesn't exist, create!
            else {
                 $o_main->db->query("INSERT INTO sys_filearchive_folder SET
                    moduleID = ?,
                    created = now(),
                    createdBy= ?,
                    parent_id = ?,
                    name = ?,
                    connected_content_table = ?,
                    connected_content_id = ?,
                    disallow_rename = '1',
                    disallow_move = '1',
                    disallow_delete = '1',
                    disallow_store_items = '0',
                    device_disallow_rename = '1',
                    device_disallow_move = '1',
                    device_disallow_delete = '1',
                    device_disallow_store_items = '0'", array($moduleID, $variables->loggID, $parent_folder_id, $custom_folder_name, $content_table, $content_row['id']));
                $return['folder_ids'][] = $o_main->db->insert_id();
                $return['created_folder_ids'][] = $o_main->db->insert_id();
                 $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
            }
        }

        // Check for deleted content
        // $deleted_check_query = mysql_query("SELECT * FROM sys_filearchive_folder WHERE parent_id = '".$parent_folder_id."' AND connected_content_table = '".$content_table."'");
        // while ($folder = mysql_fetch_assoc($deleted_check_query)) {
        //     // if connected content doesn't exist
        //     if (!in_array($folder['connected_content_id'], $content_ids)) {
        //         mysql_query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy='".$variables->loggID."', name = '".$folder['name']." (deleted)', connected_content_table = '', connected_content_id = 0 WHERE id = '".$folder['id']."'");
        //         $return['deleted_folder_ids'][] = $folder['id'];
        //         mysql_query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        //     }
        // }
    }

    return $return;
}

function create_subscription_folders($o_main, $customerId) {

    global $moduleID;
    global $variables;

    // Get parent folder ids (customer folders)
    $rows = array();
    $s_sql = "SELECT f.id folderId, c.id customerId FROM customer c LEFT JOIN sys_filearchive_folder f ON c.id = f.connected_content_id WHERE f.connected_content_table = 'customer' AND c.id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0) {
        $rows = $o_query->result_array();
    }
    foreach($rows as $row){
        // Create subfolder "subscriotions" or get it's id
        $s_sql = "SELECT * FROM sys_filearchive_folder WHERE name = 'Repeterende ordre' AND parent_id = ?";
        $o_query = $o_main->db->query($s_sql, array($row['folderId']));
        if($o_query && $o_query->num_rows()>0) {
            $subscribtion_main_subfolder_data = $o_query->row_array();
        }
        if ($subscribtion_main_subfolder_data['id']) {
            $subscribtion_main_subfolder_id = $subscribtion_main_subfolder_data['id'];
        } else {
            $o_main->db->query("INSERT INTO sys_filearchive_folder SET
                moduleID = ?,
                created = now(),
                createdBy= ?,
                parent_id = ?,
                name = 'Repeterende ordre',
                connected_content_table = '',
                connected_content_id = '',
                disallow_rename = '1',
                disallow_move = '1',
                disallow_delete = '1',
                disallow_store_items = '0',
                device_disallow_rename = '1',
                device_disallow_move = '1',
                device_disallow_delete = '1',
                device_disallow_store_items = '0'", array($moduleID, $variables->loggID, $row['folderId']));

            $subscribtion_main_subfolder_id = $o_main->db->insert_id();
            $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        }

        $content_query = "SELECT * FROM subscriptionmulti WHERE customerId = " .$row['customerId'];
        check_filearchive_folder_customized($o_main, intval($subscribtion_main_subfolder_id), $content_query, 'subscriptionmulti', 'subscriptionName');
    }
}

function check_if_customer_folder_created($o_main, $customerId) {

    $s_sql = "SELECT f.id folderId, c.id customerId FROM customer c LEFT JOIN sys_filearchive_folder f ON c.id = f.connected_content_id WHERE f.connected_content_table = 'customer' AND c.id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $rowsCount = 0;
    if($o_query && $o_query->num_rows()>0) {
        $rowsCount = $o_query->num_rows();
    }
    return $rowsCount;
}

?>
