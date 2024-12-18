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
    $s_sql = "SELECT * FROM integrationtripletex";
    if($ownercompany_id > 0){
        $s_sql = "SELECT * FROM integrationtripletex WHERE ownerCompanyId = ?";
    }
    $o_query = $o_main->db->query($s_sql, array($ownercompany_id));
    $config = $o_query ? $o_query->row_array() : array();
    $project_data_processed = array();


    $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE id = ?", array($data['projectforaccountingId']));
    $projectForAccounting = $o_query ? $o_query->row_array() : array();

    $employee_data = array();
    if($config['syncProjectCodesWhenSyncingInvoices'] && $config['projectManagerId'] != "" && $projectForAccounting){
        $employee_data = $api->get_employee($config['projectManagerId']);

        if($employee_data) {
            // $employee_data_to_pass = $employee_data;
            $employee_data_to_pass =array('id'=>$employee_data['id']);
            // if(empty($employee_data['holidayAllowanceEarned'])) {
            //     $employee_data['holidayAllowanceEarned'] = null;
            // }
            if($projectForAccounting['external_project_id'] > 0){
                $project_data_processed['id'] = $projectForAccounting['external_project_id'];
            }
            $project_data_processed['number'] = trim($projectForAccounting['projectnumber']);
            $project_data_processed['projectManager'] = $employee_data_to_pass;
            $project_data_processed['name'] = trim($projectForAccounting['name']);
            $project_data_processed['startDate'] = date("Y-m-d", time());
            $project_data_processed['isInternal'] = true;

            if($project_data_processed['id'] > 0){
                $new_customer_data = $api->update_project($project_data_processed);
            } else {
                $new_customer_data = $api->add_project($project_data_processed);
                $project_sys_id = $new_customer_data['value']['id'];
                // var_dump($project_data_processed, $new_customer_data);
                if($project_sys_id > 0) {
                    if($projectForAccounting['external_project_id'] == 0){
                        $o_query = $o_main->db->query("UPDATE projectforaccounting SET external_project_id = ? WHERE id = ?", array($project_sys_id, $projectForAccounting['id']));
                    }
                }
            }

            $return['project_sync_result'][] = $new_customer_data;
        } else {
            $return['error'] = 'MissingEmployee';
        }
    } else {
        $return['error'] = 'MissingConfig';
    }
    return $return;
}
?>
