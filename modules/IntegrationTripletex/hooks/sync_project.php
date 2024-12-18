<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    $ownercompany_id = $data['ownercompany_id'];
    if(intval($ownercompany_id) == 0){
        $ownercompany_id = 1;
    }
    // Load integration
    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationTripletex(array(
        'ownercompany_id' => $ownercompany_id,
        'o_main' => $o_main
    ));

    // Return object
    $return = array();

    // Params
    $project_id = $data['project_id'];
    $subscription_id = $data['subscription_id'];

    if($ownercompany_id > 0 && ($project_id > 0 || $subscription_id > 0)) {
        // Check if external id does exist
        // $sql = "SELECT * FROM project2 WHERE id = ? AND external_sys_id > 0";
        // $o_query = $o_main->db->query($sql, array($project_id));
        // $has_external_id = $o_query && $o_query->num_rows();
        //
        // // Generate external id if needed
        // if (!$has_external_id) {
        //     $sql = "SELECT * FROM ownercompany WHERE id = ?";
        //     $o_query = $o_main->db->query($sql, array($ownercompany_id));
        //     $ownercompany_settings = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        //
        //     $nextCustomerId = $ownercompany_settings['nextExternalProjectId'];
        //     $o_main->db->query("UPDATE project2
        //     SET updated = NOW(),
        //     updatedBy = ?,
        //     external_sys_id = ?
        //     WHERE id = ?", array($variables->loggID, $nextCustomerId, $project_id));
        //     $nextCustomerId++;
        //     $o_main->db->query("UPDATE ownercompany SET nextExternalProjectId = $nextCustomerId WHERE id = ?", array($ownercompany_id));
        //
        // }

        // Get customer data + externalsystem id data
        if($project_id){
            $sql = "SELECT p.*
            FROM project2 p
            WHERE p.id = ?";
            $o_query = $o_main->db->query($sql, array($project_id));
            $project_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

            $sql = "SELECT p.*
            FROM people p
            WHERE p.id = ?";
            $o_query = $o_main->db->query($sql, array($project_data['employeeId']));
            $projectLeader = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

        }
        if($subscription_id > 0){
            $sql = "SELECT p.*
            FROM subscriptionmulti p
            WHERE p.id = ?";
            $o_query = $o_main->db->query($sql, array($subscription_id));
            $project_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

            $sql = "SELECT p.*
            FROM workgroupleader p
            WHERE p.workgroupId = ?";
            $o_query = $o_main->db->query($sql, array($project_data['workgroupId']));
            $workgroupleader = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

            $sql = "SELECT p.*
            FROM people p
            WHERE p.id = ?";
            $o_query = $o_main->db->query($sql, array($workgroupleader['employeeId']));
            $projectLeader = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        }


        if(intval($projectLeader['external_employee_id']) == 0){
            $project_leader_processed = array();
            $project_leader_processed['firstName'] = $projectLeader['name'];
            $project_leader_processed['lastName'] = $projectLeader['last_name'];
            $project_leader_processed['employeeNumber'] = $projectLeader['id'];
            $project_leader_processed['userType'] = "NO_ACCESS";
            $new_employee_data = $api->add_employee($project_leader_processed);

            // var_dump($new_employee_data);
            $o_main->db->where('id', $projectLeader['id']);
            $o_main->db->update('people', array(
                'updated' => date('Y-m-d H:i:s'),
                'updatedBy' => $variables->loggID,
                'external_employee_id' => $new_employee_data['value']['id']
            ));
            $sql = "SELECT p.*
            FROM people p
            WHERE p.id = ?";
            $o_query = $o_main->db->query($sql, array($projectLeader['id']));
            $projectLeader = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
        }
        $employee_data = $api->get_employee($projectLeader['external_employee_id']);
        if(empty($employee_data['holidayAllowanceEarned'])) {
            $employee_data['holidayAllowanceEarned'] = null;
        }
        // External sys id
        $customer_data_processed = array();
        $customer_data_processed['number'] = trim($project_data['projectCode']);
        $customer_data_processed['projectManager'] = $employee_data;
        if($project_id){
            $customer_data_processed['name'] = trim($project_data['name']);
            $customer_data_processed['startDate'] = date("Y-m-d", strtotime($project_data['date']));
        } else {
            $customer_data_processed['name'] = trim($project_data['subscriptionName']);
            $customer_data_processed['startDate'] = date("Y-m-d", strtotime($project_data['startDate']));
        }
        $customer_data_processed['isInternal'] = false;

        // Sync customer
        if ($project_data['external_sys_id']) {
            $customer_data_processed['id'] = $project_data['external_sys_id'];
            $customer_update = $api->update_project($customer_data_processed);
            $return['project_sync_result'] = $customer_update;
        }
        else {
            // Add on API
            $new_customer_data = $api->add_project($customer_data_processed);
            // Save externalsystem id and number
            $o_main->db->where('id', $project_data['id']);
            if($project_id > 0){
                $o_main->db->update('project2', array(
                    'updated' => date('Y-m-d H:i:s'),
                    'updatedBy' => $variables->loggID,
                    'external_sys_id' => $new_customer_data['value']['id']
                ));
            } else if($subscription_id > 0){
                $o_main->db->update('subscriptionmulti', array(
                    'updated' => date('Y-m-d H:i:s'),
                    'updatedBy' => $variables->loggID,
                    'external_sys_id' => $new_customer_data['value']['id']
                ));
            }

            $return['project_sync_result'] = $new_customer_data;
        }
    }

    return $return;
    }
?>
