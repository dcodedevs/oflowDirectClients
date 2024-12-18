<?php
require_once __DIR__ . '/ApiCallDriver.php';

class IntegrationTripletex {
    function __construct($config) {
        $this->o_main = $config['o_main'];
        $this->ownercompany_id = $config['ownercompany_id'];
        $this->creditorId = isset($config['creditorId']) ? $config['creditorId'] : 0;
        $this->api = new ApiCallDriver('https://tripletex.no/v2');
        $this->auth_token = $this->create_auth_token();
        $this->api->set_auth_token($this->auth_token);
    }

    function create_auth_token() {
        $session_data = $this->get_session_data();
        $id = $session_data['consumerToken']['id'];
        $token = $session_data['token'];
        return base64_encode($id.':'.$token);
    }

    function get_session_data() {
        $config = $this->get_local_config();
        $session_data = array();
        // If has valid session already saved in db
        if ($config['sessionData']['token'] && $config['sessionData'] != "null" && strtotime($config['sessionData']['expirationDate']) > time()) {
            $session_data = $config['sessionData'];
        } else {
            $data = array (
                'consumerToken' => $config['consumerToken'],
                'employeeToken' => $config['employeeToken'],
                'expirationDate' => date('Y-m-d', time() + (60 * 60 * 24 * 30)) // 30 days
            );

            $response = $this->api->put('/token/session/:create?' . http_build_query($data), array());
            $session_data = $response['value'];
            $this->save_session_data($session_data);
        }

        return $session_data;
    }

    function get_local_config() {

        if($this->creditorId > 0){
            $s_sql = "SELECT * FROM integrationtripletex";
            $o_query = $this->o_main->db->query($s_sql);
            $config = $o_query ? $o_query->row_array() : array();

            $sql = "SELECT * FROM creditor WHERE id = ?";
            $o_query = $this->o_main->db->query($sql, array($this->creditorId));
            $creditorData = $o_query ? $o_query->row_array() : array();

            $config['employeeToken'] = $creditorData['tripletex_employeetoken'];
            $config['sessionData'] = $creditorData['tripletex_sessiondata'];
            // Session data
            if ($creditorData['sessionData']) {
                $config['sessionData'] = json_decode($creditorData['sessionData'], true);
            }
        } else {
            $sql = "SELECT * FROM integrationtripletex WHERE content_status = 0";

            if ($this->ownercompany_id) {
                $sql = "SELECT * FROM integrationtripletex WHERE ownerCompanyId = ?";
            }

            $o_query = $this->o_main->db->query($sql, array($this->ownercompany_id));
            if($o_query && $o_query->num_rows()>0) $config = $o_query->row_array();

            // Session data
            if ($config['sessionData']) {
                $config['sessionData'] = json_decode($config['sessionData'], true);
            }
        }

        // Return
        return $config;
    }

    function save_session_data($session_data) {
        $data_json = json_encode($session_data);
        if($this->creditorId > 0){
            $sql = "UPDATE creditor SET tripletex_sessiondata = ? WHERE id = ?";
            $this->o_main->db->query($sql, array($data_json, $this->creditorId));
        } else {
            // NOTE: ignoring ownercompany as requested by David
            $sql = "UPDATE integrationtripletex SET sessionData = '$data_json'";
            if ($this->ownercompany_id) {
                $sql = "UPDATE integrationtripletex SET sessionData = '$data_json' WHERE ownerCompanyId = ?";
            }
            $this->o_main->db->query($sql, array($this->ownercompany_id));
        }

    }

    /**
     * API Functions
     */
    function get_customer_list($filter) {
        // FIXME: Some old fallcback
        if (!$filter) {
            return $this->api->get('/customer', array(
                'isInactive' => false,
                'count' => 1000
            ));
        }

        if ($filter['customerNumber']) {
            $filter['customerAccountNumber'] = $filter['customerNumber'];

        }

        $list = array();

        $result = $this->api->get('/customer', $filter);

        if ($result['values']) {
            foreach ($result['values'] as $item) {
                array_push($list, $item);
            }
        }

        return $list;
    }
    function get_address_list(){
        $result = $this->api->get('/address', array("count" => 10000));
        $list = array();
        if ($result['values']) {
            foreach ($result['values'] as $item) {
                $list[$item['id']] = $item;
            }
        }
        return $list;
    }
    function get_country_list(){
        $result = $this->api->get('/country', array("count" => 10000));
        $list = array();
        if ($result['values']) {
            foreach ($result['values'] as $item) {
                $list[$item['id']] = $item;
            }
        }
        return $list;
    }
    function add_customer($data) {
        $response = $this->api->post('/customer', $data);

        // If customerNumber is "taken", let's get external sys id
        // and do customer updated instead
        if ($response['status'] == 422 && $response['code'] == 18000) {
            foreach ($response['validationMessages'] as $validation_error) {
                if ($validation_error['field'] == 'customerNumber') {
                    // Get customer sys id
                    // This is a workaround, getting all customer list
                    // filtered by customerNumber. (currently only way
                    // how to do it)
                    $customer_list = $this->get_customer_list(array(
                        'customerNumber' => $data['customerNumber']
                    ));
                    $data['id'] = $customer_list[0]['id'];
                    $response2 =  $this->update_customer($data);
                    return $response2;
                }
            }
        }

        return $response;
    }

    function get_next_customer_number() {
        $response = $this->api->get('/customer', array(
          'isInactive' => 'false',
          'count'=> '1',
          'sorting' => '-customerNumber'
        ));

        if ($response['values'][0]['customerNumber']) {
            return $response['values'][0]['customerNumber'] + 1;
        }

        return 0;
    }

    function update_customer($data) {
        $id = $data['id'];
        return $this->api->put('/customer/' . $id, $data);
    }

    function add_invoice($data) {
        // Order
        $new_order = $this->add_order($data);

        $params = array(
            'invoiceDate' => $data['date'],
            'invoiceDueDate' => $data['invoiceDueDate'],
            'invoiceNumber' => $data['invoiceNr'],
            'kid' => $data['kid'],
            'orders' => array(
                0 => array(
                    'id' => $new_order['value']['id']
                )
            )
        );

        $new_invoice = $this->api->post('/invoice?sendToCustomer=false', $params);

        return array (
            'invoice' => $new_invoice,
            'order' => $new_order
        );
    }

    function add_order($data) {
        // Create order
        $new_order_data = array(
            'orderDate' => $data['date'],
            'deliveryDate' => $data['date'],
            'customer' => array('id' => $data['customerSysId'])
        );

        if ($data['projectSysId']) {
            $new_order_data['project'] = array('id' => $data['projectSysId']);
        }

        if ($data['departmentSysId']) {
            $new_order_data['department'] = array('id' => $data['departmentSysId']);
        }

        $new_order = $this->api->post('/order', $new_order_data);

        $order_id = $new_order['value']['id'];
        if($order_id > 0){
            // Create order lines
            $lines_data = array();
            foreach ($data['lines'] as $line) {
                $processed_line_data = array(
                    'order' => array('id' => $order_id),
                    'description' => $line['description'],
                    'unitPriceExcludingVatCurrency' => $line['unitPriceExcludingVatCurrency'],
                    'count' => $line['count'],
                    'discount' => $line['discount'] ? $line['discount'] : 0,
                    'vatType' => array(
                        'id' => $line['vatType']
                    ),
                );

                if ($line['product']) {
                    $processed_line_data['product'] = array('id' => $line['product']);
                }

                array_push($lines_data, $processed_line_data);
            }
            $this->api->post('/order/orderline/list', $lines_data);
        }
        $new_order['lines'] = $data['lines'];

        // Return
        return $new_order;
    }


    function get_account_list() {
        $accounts = $this->api->get('/ledger/account');

        $account_list_by_id = array();
        $account_list_by_number = array();
        $error = "";
        if ($accounts['values']) {
            foreach ($accounts['values'] as $account) {
                $account_list_by_id[$account['id']] = $account;
                $account_list_by_number[$account['number']] = $account;
            }
        } else {
            $error = $accounts['message']." ".$accounts['developerMessage'];
        }

        return array(
            'by_id' => $account_list_by_id,
            'by_number' => $account_list_by_number,
            'error' => $error
        );
    }


    function get_transactions($data) {

        $account_list = $this->get_account_list();
        $account_id = $account_list['by_number'][$data['account']]['id'];

        $transactions_processed = array();

        $transactions = $this->api->get('/ledger/posting', array(
            'dateFrom' => date('Y-m-d', time() - 60*60*24*180),
            'dateTo' => date('Y-m-d', time() + 60*60*24),
            'customerId' => $data['customerSysId'],
            'accountId' => $account_id,
            'from'=> isset($data['from']) ? $data['from'] : 0,
            'count'=> isset($data['count']) ? $data['count'] : 3000
        ));

        foreach($transactions['values'] as $transaction) {

            $account_number = $account_list['by_id'][$transaction['account']['id']]['number'];

            array_push($transactions_processed, array(
                'accountNr' => $account_number,
                'customerId' => $transaction['customer']['id'],
                'transactionNr' => $transaction['id'],
                'invoiceNr' => $transaction['invoiceNumber'],
                'amount' => $transaction['amount'],
                'date' => $transaction['date'],
                'voucher'=>$transaction['voucher'],
                'type'=>$transaction['type']

            ));
        }

        return $transactions_processed;
    }
    function get_posting_list($data) {

        $account_list = $this->get_account_list();
        $account_id = $account_list['by_number'][$data['account']]['id'];

        $transactions_processed = array();

        $transactions = $this->api->get('/ledger/posting', array(
            'dateFrom' => isset($data['dateFrom']) ? $data['dateFrom'] : date('Y-m-d', time() - 60*60*24*180),
            'dateTo' => isset($data['dateTo']) ? $data['dateTo'] : date('Y-m-d', time() + 60*60*24),
            'from'=> isset($data['from']) ? $data['from'] : 0,
            'count'=> isset($data['count']) ? $data['count'] : 3000,
            'fields'=> '*,supplier(*),project(*),department(*)'
        ));

        foreach($transactions['values'] as $transaction) {

            $account_number = $account_list['by_id'][$transaction['account']['id']]['number'];

            array_push($transactions_processed, array(
                'accountNr' => $account_number,
                'customerId' => $transaction['customer']['id'],
                'transactionNr' => $transaction['id'],
                'invoiceNr' => $transaction['invoiceNumber'],
                'amount' => $transaction['amount'],
                'date' => $transaction['date'],
                'voucher'=>$transaction['voucher'],
                'type'=>$transaction['type'],
                'supplier'=>$transaction['supplier'],
                'description'=>$transaction['description'],
                'project'=>$transaction['project'],
                'department'=>$transaction['department']

            ));
        }

        return $transactions_processed;
    }

    function get_open_posting_list() {
        $invoices = $this->api->get('/ledger/posting/openPost', array(
            'date' => date("Y-m-d"),
            'count'=>3000
        ));
        $list = array();

        if ($invoices['values']) {
            foreach ($invoices['values'] as $item) {
                // if($item['invoiceNumber'] != "") {
                    array_push($list, $item);
                // }
            }
        }

        return $list;
    }
    function get_invoice_list($data) {
        $invoices = $this->api->get('/invoice', array(
            'invoiceDateFrom' => date("Y-m-d", strtotime($data['invoiceDateFrom'])),
            'invoiceDateTo' => date("Y-m-d", strtotime($data['invoiceDateTo'])),
            'from'=> isset($data['from']) ? $data['from'] : 0,
            'count'=> isset($data['count']) ? $data['count'] : 2000
        ));

        return $invoices;
    }
    function get_projects_list($filter = array()) {
         $projects = $this->api->get('/project', $filter);

         // Build sys id and actual project number relation map
         // Used in next step to find parent project numbers
         $projectIdNumberMap = array();
         foreach ($projects['values'] as $project) {
             $projectIdNumberMap[$project['id']] = $project['number'];
         }

         // Find project parent numbers
         $project_list_processed = array();
         foreach ($projects['values'] as $project) {
             array_push($project_list_processed, array(
                'id' => $project['id'],
                'description' => $project['name'],
                'code' => $project['number'],
                'parentCode' => $project['mainProject'] ? $projectIdNumberMap[$project['mainProject']['id']] : ''
             ));
         }

        return $project_list_processed;
    }
    function get_departments_list() {
         $projects = $this->api->get('/department', array());

         // Build sys id and actual project number relation map
         // Used in next step to find parent project numbers
         $projectIdNumberMap = array();
         foreach ($projects['values'] as $project) {
             $projectIdNumberMap[$project['id']] = $project['departmentNumber'];
         }

         // Find project parent numbers
         $project_list_processed = array();
         foreach ($projects['values'] as $project) {
             array_push($project_list_processed, array(
                'id' => $project['id'],
                'description' => $project['name'],
                'code' => $project['departmentNumber']
             ));
         }

        return $project_list_processed;
    }
    function get_department($id){
        $department = $this->api->get('/department/' . $id);
        return $department['value'] ? $department['value'] : false;
    }
    function get_vat_list() {
        $vat_list = $this->api->get('/ledger/vatType');
        return $vat_list;
    }

    function get_api_route($route, $params) {
        return $this->api->get($route, $params);
    }

    function save_product($data) {
        // Get account id
        $account_list = $this->get_account_list();
        $account_data = $account_list['by_number'][$data['account']];


        if(!$account_data) {
            return array(
                'error' => true,
                'message' => 'Invalid account'
            );
        }

        if ($account_data['vatType']['id'] != $data['vat']) {
            return array(
                'error' => true,
                'message' => 'Account and VAT mismatch'
            );
        }

        if ($data['id']) {
            // update product
            $product = $this->api->put('/product/' . $data['id'], array(
                'name' => $data['name'],
                'account' => array('id' => $account_data['id']),
                'vatType' => array('id' => $data['vat']),
                'number' => $data['number'],
                'priceExcludingVatCurrency' => $data['priceWithoutVat'],
                'costExcludingVatCurrency' => $data['costWithoutVat'],
            ));
        } else {
            $product = $this->api->post('/product', array(
                'name' => $data['name'],
                'account' => array('id' => $account_data['id']),
                'vatType' => array('id' => $data['vat']),
                'number' => $data['number'],
                'priceExcludingVatCurrency' => $data['priceWithoutVat'],
                'costExcludingVatCurrency' => $data['costWithoutVat'],
            ));
        }

        if ($product['status'] == 422) {
            return array(
                'error' => true,
                'message' => json_encode($product['validationMessages'])
            );
        }

        return $product['value'];
    }

    function get_product($id) {
        $product = $this->api->get('/product/' . $id);
        return $product['value'] ? $product['value'] : false;
    }

    function get_product_by_number($number) {
        $result = $this->api->get('/product', array('productNumber' => $number));
        return $result['values'] ? $result['values'][0] : false;
    }
    function get_product_by_name($name) {
        $result = $this->api->get('/product', array('name' => $name));
        return $result['values'] ? $result['values'] : false;
    }

    function get_products() {
        $result = $this->api->get('/product/', array('isInactive'=> false));
        return $result['values'];
    }

    function sync_invoice($invoice_id) {
        global $moduleID;
        global $variables;

        $return = array();

        // Get invoice data
        $o_query = $this->o_main->db->query('SELECT * FROM invoice WHERE id = ?', array($invoice_id));
        $invoice_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

        // Get customer externalsystem id data
        $sql = "SELECT c.*,
        cei.external_sys_id external_sys_id,
        cei.external_id external_id
        FROM customer c
        LEFT JOIN customer_externalsystem_id cei ON cei.customer_id = c.id
        WHERE c.id = ?";
        $o_query = $this->o_main->db->query($sql, array($invoice_data['customerId']));
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
            'addressLine2' => $customer_data['paCity'] . ' ' . $customer_data['paCountry'],
            'postalCode' => $customer_data['paPostalNumber']
        );

        // Sync customer
        if ($customer_data['external_sys_id']) {
            $customer_data_processed['id'] = $customer_data['external_sys_id'];
            $customer_update = $this->update_customer($customer_data_processed);
            $return['customer_sync_result'] = $customer_update;
        }
        else {
            // Add on API
            $new_customer_data = $this->add_customer($customer_data_processed);

            // Save externalsystem id and number
            $this->o_main->db->where('customer_id', $customer_data['id']);
            $this->o_main->db->update('customer_externalsystem_id', array(
                'updated' => date('Y-m-d H:i:s'),
                'updatedBy' => $variables->loggID,
                'external_sys_id' => $new_customer_data['value']['id']
            ));

            $customer_data['external_sys_id'] = $new_customer_data['value']['id'];
            $return['customer_sync_result'] = $new_customer_data;
        }

        // Get orderlines
        $sql = "SELECT o.*
        FROM orders o
        LEFT JOIN customer_collectingorder cco ON cco.id = o.collectingorderId
        WHERE cco.invoiceNumber = ?";
        $o_query = $this->o_main->db->query($sql, array($invoice_id));
        $order_lines = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();

        $order_lines_processed = array();

        foreach ($order_lines as $order) {
            array_push($order_lines_processed, array(
                'description' => $order['articleName'],
                'unitPriceExcludingVatCurrency' => $order['pricePerPiece'],
                'count' => $order['amount'],
                'vatType' => $order['vatCode'],
                'discount' => $order['discountPercent'] ? $order['discountPercent'] : 0
            ));
        }

        // Sync invoice
        $invoice_result = $this->add_invoice(array(
            'date' => $invoice_data['invoiceDate'],
            'invoiceDueDate' => $invoice_data['dueDate'],
            'invoiceNr' => $invoice_data['id'],
            'kid' => $invoice_data['kidNumber'],
            'customerSysId' => $customer_data['external_sys_id'],
            'lines' => $order_lines_processed
        ));

        $return['sent_order_lines'] = $order_lines_processed;
        $return['invoice_sync_result'] = $invoice_result;

        return $return;
    }


    function add_project($data) {
        $response = $this->api->post('/project', $data);

        // If customerNumber is "taken", let's get external sys id
        // and do customer updated instead
        if ($response['status'] == 422 && $response['code'] == 18000) {
            foreach ($response['validationMessages'] as $validation_error) {
                if ($validation_error['field'] == 'number') {
                    // Get customer sys id
                    // This is a workaround, getting all customer list
                    // filtered by customerNumber. (currently only way
                    // how to do it)
                    $customer_list = $this->get_projects_list(array(
                        'number' => $data['number']
                    ));
                    $data['id'] = $customer_list[0]['id'];
                    $response2 =  $this->update_project($data);
                    return $response2;
                }
            }
        }

        return $response;
    }
    function update_project($data) {
        $id = $data['id'];
        return $this->api->put('/project/' . $id, $data);
    }
    function add_employee($data) {
        $response = $this->api->post('/employee', $data);
        return $response;
    }
    function get_employee($id) {
        $employee = $this->api->get('/employee/'.$id);
        return $employee['value'] ? $employee['value'] : false;
    }

    function get_posting($id){
        $employee = $this->api->get('/ledger/posting/'.$id);
        return $employee['value'] ? $employee['value'] : false;
    }
    function get_incoming_list($data) {
         $vouchers = $this->api->get('/ledger/voucher', $data);

         $voucherList = $vouchers['values'];
         $processed_vouchers = array();

         foreach($voucherList as $voucher) {
             $postings = $voucher['postings'];
             if(count($postings) > 0){
                 if($voucher['voucherType']['id'] == "2005783"){
                     $processed_vouchers[] = $voucher;
                 }
             }
         }


        return $processed_vouchers;
    }
    function get_supplier_invoice_list($data) {
         $vouchers = $this->api->get('/supplierInvoice', $data);

         $invoiceList = $vouchers['values'];
         $processed_invoices = array();
         foreach($invoiceList as $invoice) {
             $processed_invoices[] = $invoice;
         }


        return $processed_invoices;
    }
    function get_supplier_invoice_pdf($invoice_id) {
         $invoice_document = $this->api->get_file('/supplierInvoice/'.$invoice_id.'/pdf');
        return $invoice_document;
    }
    function get_document(){
        $invoice_document = $this->api->get('/document/197358205');
    }
}

?>
