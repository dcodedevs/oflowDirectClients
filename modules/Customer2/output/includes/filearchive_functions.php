<?php
function create_customer_folders($o_main, $customer_id) {
    global $moduleID;
    global $variables;

    /****************************************
     * Language
     ****************************************
     */

    // Hardcoded language variables
    // NOTE Be EXTRA careful changing those hardcoded folder name,
    // because it migh result in duplicate folders ar messed up folder tree
    //
    // NOTE THERE MUST BE DEFAULT LANGUAGE FOR ACCOUNT, OTHERWISE FOLDERS WILL
    // NOT BE CREATED

    $lang_all = array(
        'en' => array(
            'customer' => 'Customer',
            'subscriptions' => 'Repeating order',
            'visible_for_customer' => 'Visible for customer',
            'visible_only_in_here' => 'Visible only in here',

        ),
        'no' => array(
            'customer' => 'Kunder',
            'subscriptions' => 'Repeterende ordre',
            'visible_for_customer' => 'Vises i kundeportal',
            'visible_only_in_here' => 'Vises kun her',
        )
    );

    // Get account default language
    $o_query = $o_main->db->get_where('language', array('defaultOutputlanguage' => 1));
    $default_output_language_data = $o_query ? $o_query->row_array() : array();
    $default_output_language_id = $default_output_language_data['languageID'];

    // If there is no default language or no language variables defined quit execution
    if (!$default_output_language_id || !array_key_exists($default_output_language_id, $lang_all)) return;

    // Default language variables
    $lang = $lang_all[$default_output_language_id];

    /****************************************
     * All customers container folder
     ****************************************
     */

    // Check if there is main folder that CONTAINS LIST OF ALL CUSTOMER folders
    $o_query = $o_main->db->get_where('sys_filearchive_folder', array(
        'name' => $lang['customer'],
        'parent_id' => 0,
        'content_status' => 0
    ));

    $all_customer_container_folder_data = $o_query ? $o_query->row_array() : array();

    if (!$all_customer_container_folder_data) {
        $o_query = $o_main->db->query("INSERT INTO sys_filearchive_folder SET
            moduleID = ?,
            created = now(),
            createdBy= ?,
            parent_id = '0',
            name = ?,
            connected_content_table = '',
            connected_content_id = '',
            disallow_rename = '1',
            disallow_move = '1',
            disallow_delete = '1',
            disallow_store_items = '1',
            device_disallow_rename = '1',
            device_disallow_move = '1',
            device_disallow_delete = '1',
            device_disallow_store_items = '1'", array($moduleID, $variables->loggID, $lang['customer']));
        $all_customer_container_folder_id = $o_main->db->insert_id();
        $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
    } else {
        $all_customer_container_folder_id = $all_customer_container_folder_data['id'];
    }

    /****************************************
     * Current customer folder
     * (provided by $customer_id function param)
     ****************************************
     */

    // Get customer data, create name variable
    $o_query = $o_main->db->get_where('customer', array('id' => $customer_id));
    $customer_data = $o_query ? $o_query->row_array() : array();
    $customer_folder_name = $customer_data['name'];

    // Check if customer has folder
    $o_query = $o_main->db->get_where('sys_filearchive_folder', array(
        'connected_content_table' => 'customer',
        'connected_content_id' => $customer_id,
        'parent_id' => $all_customer_container_folder_id,
        'content_status' => 0
    ));

    // Exist
    if($o_query && $o_query->num_rows()>0) {
        $customer_folder_data = $o_query->row_array();
        $customer_folder_id = $customer_folder_data['id'];

        // If renamed
        if ($customer_folder_data['name'] != $customer_folder_name) {
            $o_main->db->query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy= ?, name = ? WHERE id = ?", array(
                $variables->loggID,
                $customer_folder_name,
                $customer_folder_id
            ));
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
            device_disallow_store_items = '0'",
            array(
                $moduleID,
                $variables->loggID,
                $all_customer_container_folder_id,
                $customer_folder_name,
                'customer',
                $customer_data['id']
            )
        );

        $customer_folder_id = $o_main->db->insert_id();

        $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
    }

    /****************************************
     * Subscription container folder
     ****************************************
     */

    // Check if subscription container folder is created for customer
    $o_query = $o_main->db->get_where('sys_filearchive_folder', array(
        'name' => $lang['subscriptions'],
        'parent_id' => $customer_folder_id,
        'content_status' => 0
    ));

    $subscription_container_folder_data = $o_query ? $o_query->row_array() : array();

    if (!$subscription_container_folder_data) {
        $o_main->db->query("INSERT INTO sys_filearchive_folder SET
            moduleID = ?,
            created = now(),
            createdBy= ?,
            parent_id = ?,
            name = ?,
            connected_content_table = '',
            connected_content_id = '',
            disallow_rename = '1',
            disallow_move = '1',
            disallow_delete = '1',
            disallow_store_items = '1',
            device_disallow_rename = '1',
            device_disallow_move = '1',
            device_disallow_delete = '1',
            device_disallow_store_items = '1'",
            array(
                $moduleID,
                $variables->loggID,
                $customer_folder_id,
                $lang['subscriptions']
            )
        );

        $subscription_container_folder_id = $o_main->db->insert_id();
        $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
    } else {
        $subscription_container_folder_id = $subscription_container_folder_data['id'];
    }

    /****************************************
     * Subscription folders
     ****************************************
     */
    $o_query = $o_main->db->get_where('subscriptionmulti', array('customerId' => $customer_id));
    $subscriptions = $o_query ? $o_query->result_array() : array();

    // $content_query = "SELECT * FROM subscriptionmulti WHERE customerId = " .$row['customerId'];
    // check_filearchive_folder_customized($o_main, intval($subscribtion_main_subfolder_id), $content_query, 'subscriptionmulti', 'subscriptionName');

    foreach ($subscriptions as $subscription) {
        $subscription_folder_name = $subscription['subscriptionName']. ' (#'.$subscription['id'].')';
        $subscription_id = $subscription['id'];

        // Check if subscription has folder
        $o_query = $o_main->db->get_where('sys_filearchive_folder', array(
            'connected_content_table' => 'subscriptionmulti',
            'connected_content_id' => $subscription_id,
            'parent_id' => $subscription_container_folder_id,
            'content_status' => 0
        ));

        // Exist
        if($o_query && $o_query->num_rows()>0) {
            $subscription_folder_data = $o_query->row_array();
            $subscription_folder_id = $subscription_folder_data['id'];

            // If renamed
            if ($subscription_folder_data['name'] != $subscription_folder_name) {
                $o_main->db->query("UPDATE sys_filearchive_folder SET updated = now(), updatedBy= ?, name = ? WHERE id = ?", array(
                    $variables->loggID,
                    $subscription_folder_name,
                    $subscription_folder_id
                ));
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
                disallow_store_items = '1',
                device_disallow_rename = '1',
                device_disallow_move = '1',
                device_disallow_delete = '1',
                device_disallow_store_items = '1'",
                array(
                    $moduleID,
                    $variables->loggID,
                    $subscription_container_folder_id,
                    $subscription_folder_name,
                    'subscriptionmulti',
                    $subscription_id
                )
            );

            $subscription_folder_id = $o_main->db->insert_id();

            $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        }

        /****************************************
         * Visible for customer folders
         ****************************************
         */
        $sub_query = $o_main->db->get_where('sys_filearchive_folder', array(
            'name' => $lang['visible_for_customer'],
            'parent_id' => $subscription_folder_id,
            'content_status' => 0
        ));

        if (!$sub_query->num_rows()) {
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
                device_disallow_store_items = '0'",
                array(
                    $moduleID,
                    $variables->loggID,
                    $subscription_folder_id,
                    $lang['visible_for_customer'],
                    'subscriptionmulti#visible_for_customer',
                    $subscription_id
                )
            );

            $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        }

        /****************************************
         * Visible only in here folder
         ****************************************
         */
        $sub_query = $o_main->db->get_where('sys_filearchive_folder', array(
            'name' => $lang['visible_only_in_here'],
            'parent_id' => $subscription_folder_id,
            'content_status' => 0
        ));

        if (!$sub_query->num_rows()) {
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
                device_disallow_store_items = '0'",
                array(
                    $moduleID,
                    $variables->loggID,
                    $subscription_folder_id,
                    $lang['visible_only_in_here'],
                    'subscriptionmulti#visible_only_in_here',
                    $subscription_id
                )
            );

            $o_main->db->query("UPDATE sys_filearchive_sync_index SET sync = 1 ORDER BY id DESC LIMIT 1");
        }
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
