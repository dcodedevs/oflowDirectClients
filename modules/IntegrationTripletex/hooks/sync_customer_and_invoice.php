<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $invoice_id = $data['invoice_id'];

    // Get invoice data
    $o_query = $o_main->db->query('SELECT * FROM invoice WHERE id = ?', array($invoice_id));
    $invoice_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
    if($invoice_data){
        $ownercompany_id = $invoice_data['ownercompany_id'];
        if(intval($ownercompany_id) == 0){
            $ownercompany_id = 1;
        }
        // Mark invoice sync_status as started
        $o_main->db->where('id', $invoice_id);
        $o_main->db->update('invoice', array('sync_status' => 1));

        // Load integration
        require_once __DIR__ . '/../internal_api/load.php';
        $api = new IntegrationTripletex(array(
            'ownercompany_id' => $ownercompany_id,
            'o_main' => $o_main
        ));

        // Return object
        $return = array();

        // Get customer externalsystem id data
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id,
        cei.external_id external_id
        FROM customer c
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id AND cei.ownercompany_id = '".$o_main->db->escape_str($ownercompany_id)."'
        WHERE c.id = ?";
        $o_query = $o_main->db->query($sql, array($invoice_data['customerId']));
        $customer_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

        // Process customer data
        $customer_data_processed = array();
        $customer_data_processed['name'] = trim($customer_data['name']." ". $customer_data['middlename']." ". $customer_data['lastname']);
        if (!$customer_data['external_sys_id']) {
            $customer_data_processed['customerNumber'] = $customer_data['external_id'];
        }
        if (strlen($customer_data['publicRegisterId']) === 9 && is_numeric($customer_data['publicRegisterId'])) {
            $customer_data_processed['organizationNumber'] = $customer_data['publicRegisterId'];
        }
        $customer_data_processed['postalAddress'] = array(
            'addressLine1' => $customer_data['paStreet'] . ' ' . $customer_data['paStreet2'],
            // 'addressLine2' => $customer_data['paCity'] . ' ' . $customer_data['paCountry'],
            'postalCode' => $customer_data['paPostalNumber'],
            'city' => $customer_data['paCity']
        );
        if($customer_data['invoiceEmail'] != "") {
            $customer_data_processed['invoiceEmail'] = $customer_data['invoiceEmail'];
        }
        $customer_data_processed['invoiceSendMethod'] = "EMAIL";

        // workaround to clear invoicEmail, to stop sending them from Tripletex
        // Approved by David (05.04.2019)
        // was used before /invoice?sendToCustomer=false

        // Sync customer
        if ($customer_data['external_sys_id']) {
            $customer_data_processed['id'] = $customer_data['external_sys_id'];
            $customer_update = $api->update_customer($customer_data_processed);
            $return['customer_sync_result'] = $customer_update;
        }
        else {
            // Add on API
            $new_customer_data = $api->add_customer($customer_data_processed);

            $o_query = $o_main->db->query('UPDATE customer_externalsystem_id SET updated = ?, updatedBy = ?, external_sys_id = ? WHERE customer_id = ? AND ownercompany_id = ?',
             array(date('Y-m-d H:i:s'), $variables->loggID, $new_customer_data['value']['id'], $customer_data['id'], $ownercompany_id));

            $customer_data['external_sys_id'] = $new_customer_data['value']['id'];
            $return['customer_sync_result'] = $new_customer_data;
        }

        // Get orderlines
        $sql = "SELECT o.*, a.external_sys_id externalArticleId, cco.department_for_accounting_code, cco.accountingProjectCode
        FROM orders o
        LEFT JOIN customer_collectingorder cco ON cco.id = o.collectingorderId
        LEFT JOIN article a ON o.articleNumber = a.id
        WHERE cco.invoiceNumber = ?";
        $o_query = $o_main->db->query($sql, array($invoice_id));
        $order_lines = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

        $order_lines_processed = array();

        // Project code / sys_id vars
        $project_code = 0;
        $project_sys_id = 0;
        $department_sys_id = 0;
        $department_code = 0;

        $config = $api->get_local_config();
        $employee_data = array();
        if($config['syncProjectCodesWhenSyncingInvoices'] && $config['projectManagerId'] != ""){
            $employee_data = $api->get_employee($config['projectManagerId']);
            if(empty($employee_data['holidayAllowanceEarned'])) {
                $employee_data['holidayAllowanceEarned'] = null;
            }
        }

        foreach ($order_lines as $order) {
            $line = array(
                'description' => $order['articleName'],
                'unitPriceExcludingVatCurrency' => $order['pricePerPiece'],
                'count' => $order['amount'],
                'vatType' => $order['vatCode'],
                'discount' => $order['discountPercent'] ? $order['discountPercent'] : 0
            );

            if ($order['externalArticleId']) {
                $line['product'] = $order['externalArticleId'];
            }

            if ($order['accountingProjectCode'] && !$project_code) {
                $project_code = $order['accountingProjectCode'];
            }

            if ($order['department_for_accounting_code'] && !$department_code) {
                $department_code = $order['department_for_accounting_code'];
            }

            array_push($order_lines_processed, $line);
        }


        // Get projects from API
        if ($project_code) {
            $projects_from_api = $api->get_projects_list();
            foreach ($projects_from_api as $project_from_api) {
                if ($project_from_api['code'] == $project_code) {
                    $project_sys_id = $project_from_api['id'];
                }
            }
        }

        if($config['syncProjectCodesWhenSyncingInvoices']){
            if($employee_data){
                $project_data_processed = array();
            	$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_code));
            	$projectForAccounting = $o_query ? $o_query->row_array() : array();
                $project_data_processed['number'] = trim($project_code);
                $project_data_processed['projectManager'] = $employee_data;
                $project_data_processed['name'] = trim($projectForAccounting['name']);
                $project_data_processed['startDate'] = date("Y-m-d", strtotime($invoice_data['invoiceDate']));
                $project_data_processed['isInternal'] = true;

                if($project_sys_id == 0) {
                    $new_customer_data = $api->add_project($project_data_processed);
                    $project_sys_id = $new_customer_data['value']['id'];

                    if($project_sys_id > 0) {
                        $o_query = $o_main->db->query("UPDATE projectforaccounting SET external_project_id = ? WHERE id = ?", array($project_sys_id, $projectForAccounting['id']));
                    }
                } else {
                    $project_data_processed['id'] = $project_sys_id;
                    $new_customer_data = $api->update_project($project_data_processed);
                }
            }
        }
        // Get projects from API

        $projects_from_api = $api->get_departments_list();

        foreach ($projects_from_api as $project_from_api) {
            if ($project_from_api['code'] == $department_code) {
                $department_sys_id = $project_from_api['id'];
            }
        }

        // Sync invoice
        $invoice_result = $api->add_invoice(array(
            'date' => $invoice_data['invoiceDate'],
            'invoiceDueDate' => $invoice_data['dueDate'],
            'invoiceNr' => $invoice_data['external_invoice_nr'],
            'kid' => $invoice_data['kidNumber'],
            'customerSysId' => $customer_data['external_sys_id'],
            'projectSysId' => $project_sys_id,
            'departmentSysId' => $department_sys_id,
            'lines' => $order_lines_processed
        ));
        $return['dataToPass'] = array(
            'date' => $invoice_data['invoiceDate'],
            'invoiceDueDate' => $invoice_data['dueDate'],
            'invoiceNr' => $invoice_data['external_invoice_nr'],
            'kid' => $invoice_data['kidNumber'],
            'customerSysId' => $customer_data['external_sys_id'],
            'projectSysId' => $project_sys_id,
            'departmentSysId' => $department_sys_id,
            'lines' => $order_lines_processed
        );
        $return['sent_order_lines'] = $order_lines_processed;
        $return['invoice_sync_result'] = $invoice_result;

        if ($invoice_result['invoice']['value']['id']) {
            // Mark invoice sync_status as success
            // Save total values from tripletex
            $o_main->db->where('id', $invoice_id);
            $o_main->db->update('invoice', array(
                'sync_status' => 2,
                'sync_confirmed_total_value_without_vat' => $invoice_result['invoice']['value']['amountExcludingVat'],
                'sync_confirmed_total_value_with_vat' => $invoice_result['invoice']['value']['amount']
            ));
        } else {
            $o_main->db->where('id', $invoice_id);
            $o_main->db->update('invoice', array(
                'sync_error_response' => json_encode($return),
            ));
        }
    }

    return $return;
}
?>
