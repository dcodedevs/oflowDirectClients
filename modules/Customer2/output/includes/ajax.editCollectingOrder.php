<?php

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

$customerId = $_POST['customerId'] ? $o_main->db->escape_str($_POST['customerId']) : 0;
$collectingorderId = $_POST['collectingorderId'] ? $o_main->db->escape_str($_POST['collectingorderId']) : 0;
if(isset($_POST['action'])){ $action = $_POST['action']; } else { $action = ''; }
$forceUpdate = $_POST['forceUpdate'] ? $_POST['forceUpdate'] : false;

$s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($collectingorderId));
$project = ($o_query ? $o_query->row_array() : array());
if($customerId == 0){
    $customerId = $project['customerId'];
}

$s_sql = "SELECT * FROM batch_renewal_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $batch_renewal_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_renewal_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batch_renewal_accountconfig = $o_query ? $o_query->row_array() : array();

if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 1){
    $batch_renewal_basisconfig['activateCheckForProjectNr'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 2) {
    $batch_renewal_basisconfig['activateCheckForProjectNr'] = 0;
}

$ownercompanies = array();
$s_sql = "SELECT * FROM ownercompany";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $ownercompanies = $o_query->result_array();
}

if(isset($_POST['action']) && $_POST['action'] == "updateApprovedForBatchInvoicing"){
    $sql = "UPDATE customer_collectingorder SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    approvedForBatchinvoicing = '".$_POST['checked']."'
    WHERE id = ?";
    $o_query = $o_main->db->query($sql, $collectingorderId);
} else if(isset($_POST['action']) && $_POST['action'] == "updateSeperatedInvoice"){
    $checked = 0;
    if($_POST['checked']){
        $checked = $collectingorderId;
    }
    $sql = "UPDATE customer_collectingorder SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    seperatedInvoice = '".$checked."'
    WHERE id = ?";
    $o_query = $o_main->db->query($sql, $collectingorderId);
} else {
    if(!$project['approvedForInvoicing']){
         if($moduleAccesslevel > 10) {
            if(isset($_POST['output_form_submit'])) {

                $s_sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($collectingorderId));
                $project = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
                $o_query = $o_main->db->query($s_sql, array($project['customerId']));
                $customer = ($o_query ? $o_query->row_array() : array());

                $s_sql = "SELECT * FROM article_accountconfig";
                $o_query = $o_main->db->query($s_sql);
                $article_accountconfig = $o_query ? $o_query->row_array() : array();

                $ownercompany_id = 1;
                if(isset($_POST['ownercompany_id'])) {
                    $ownercompany_id = $_POST['ownercompany_id'];
                }
                $addedOrderlines = array();

                if ($project) {

                    $connectedProjectId = $_POST['projectId'];
                    $connectedProject2Id = $_POST['project2Id'];
                    $connectedProject2PeriodId = $_POST['project2PeriodId'];

                    if($project['customerId'] != $_POST['customerId']){
                        $connectedProjectId = 0;
                        $connectedProject2Id = 0;
                        $connectedProject2PeriodId = 0;
                    }
                    $departmentError = false;
                    $globalError = false;
                    if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] == 3) {
                        $s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
                        $o_query = $o_main->db->query($s_sql, array($_POST['departmentCode']));
                        $departmentItem = $o_query ? $o_query->row_array() : array();
                        if(!$departmentItem){
                            $departmentError = true;
                        }
                    }

                    foreach($_POST['articleId'] as $key=>$articleId){
                        $pricePerPieceError = false;
                        $pricePerPiece = str_replace(",", ".", $_POST['pricePerPiece'][$key]);
                        if($pricePerPiece < 0){
                            $globalError = true;
                            $fw_error_msg[$key] = $formText_PricePerPieceCanNotBeNegativeUseNegativeAmount_output;
                        }
                        if($_POST['articleName'][$key] == ""){
                            $globalError = true;
                            $fw_error_msg[$key] = $formText_ProductNameCanNotBeEmpty_output;
                        }
                    }

                    if(!$departmentError && !$globalError) {
                        $sql = "UPDATE customer_collectingorder SET
                        updated = now(),
                        updatedBy='".$variables->loggID."',
                        date = '".date("Y-m-d", strtotime($_POST['date']))."',
                        contactpersonId = '".$_POST['contactPerson']."',
                        customerId = '".$_POST['customerId']."',
                        accountingProjectCode = '".$_POST['projectCode']."',
                        projectId = '".$connectedProjectId."',
                        project2Id = '".$connectedProject2Id."',
                        project2PeriodId = '".$connectedProject2PeriodId."',
                        department_for_accounting_code = '".$_POST['departmentCode']."',
                        ownercompanyId = ?
                        WHERE id = ?";
                        $o_query = $o_main->db->query($sql, array($ownercompany_id, $collectingorderId));


                        foreach($_POST['articleId'] as $key=>$articleId) {
                            $s_sql = "SELECT * FROM article WHERE article.id = ?";
                            $o_query = $o_main->db->query($s_sql, array($articleId));
                            $article = ($o_query ? $o_query->row_array() : array());
                            if($article){
                                $orderItem = array();
                                if(isset($_POST['ordersId'][$key])){
                                    $s_sql = "SELECT * FROM orders WHERE orders.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($_POST['ordersId'][$key]));
                                    $orderItem = ($o_query ? $o_query->row_array() : array());
                                }
                                $customerId = $customer['id'];

                                $vatCode = $_POST['vatCode'][$key];
                                $bookaccountNr = $_POST['bookaccountNr'][$key];
                                $vatPercent = 0;

                                $noError = true;

                                $vatCodeError = false;
                                $bookAccountError = false;
                                $articleError = false;
                                $projectError = false;

                                $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                $o_query = $o_main->db->query($s_sql, array($vatCode));
                                $vatcodeItem = $o_query ? $o_query->row_array() : array();
                                if(!$vatcodeItem){
                                    $noError = false;
                                    $vatCodeError = true;
                                }

                                $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
                                $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
                                $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                if(!$bookaccountItem){
                                    $noError = false;
                                    $bookAccountError = true;
                                }

                                $s_sql = "SELECT * FROM article WHERE id = ?";
                                $o_query = $o_main->db->query($s_sql, array($articleId));
                                $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                if(!$bookaccountItem){
                                    $noError = false;
                                    $articleError = true;
                                }

                                if($customer_basisconfig['activeAccountingProjectOnOrder'] > 1) {
                                    $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
                                    $o_query = $o_main->db->query($s_sql, array($_POST['projectCode']));
                                    $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                    if(!$bookaccountItem){
                                        $noError = false;
                                        $projectError = true;
                                    }
                                }
                                if($noError){
                                    if($orderItem){
                                        $articleName = $_POST['articleName'][$key];
                                        $pricePerPiece = round(str_replace(",", ".", $_POST['pricePerPiece'][$key]), 2);
                                        $amount = round(str_replace(",", ".", $_POST['quantity'][$key]), 4);
                                        $discountPercent = round(str_replace(",", ".", $_POST['discount'][$key]), 2);

                                        $periodization = intval($_POST['periodization'][$key]);
                                        $dateFrom = null;
                                        $dateTo = null;

                                        if($periodization == 1){
                                            $dateFrom = date("Y-m-d", strtotime("01.".$_POST['dateFromMonth'][$key]));
                                            $dateTo = date("Y-m-t", strtotime("01.".$_POST['dateToMonth'][$key]));
                                        }
                                        if($periodization == 2){
                                            $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom'][$key]));
                                            $dateTo =  date("Y-m-d", strtotime($_POST['dateTo'][$key]));
                                        }

                                        $periodizationMonths = "";

                                        if($periodization == 0){
                                            $dateFrom = $orderItem['dateFrom'];
                                            $dateTo = $orderItem['dateTo'];
                                        } else {
                                            $start    = (new DateTime($dateFrom))->modify('first day of this month');
                                            $end      = (new DateTime($dateTo))->modify('first day of next month');
                                            $interval = DateInterval::createFromDateString('1 month');
                                            $period   = new DatePeriod($start, $interval, $end);
                                            foreach ($period as $dt) {
                                                $periodizationMonths .= $dt->format("mY") . ",";
                                            }
                                            if(strlen($periodizationMonths) > 0){
                                                $periodizationMonths = substr($periodizationMonths, 0, -1);
                                            }
                                        }

                                        $priceTotal = round($pricePerPiece * $amount * (100-$discountPercent)/100, 2);

                                        $s_sql = "UPDATE orders SET
                                        updated = now(),
                                        updatedBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        describtion= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        Status= ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        collectingorderId = ?,
                                        periodization = ?,
                                        dateFrom = ?,
                                        dateTo = ?,
                                        periodizationMonths = ?
                                        WHERE id = ?";
                                        $o_main->db->query($s_sql, array($variables->loggID, $article['id'], $articleName, '', str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'], 1, $bookaccountNr, $vatCode, $vatPercent, $collectingorderId, $periodization, $dateFrom, $dateTo, $periodizationMonths, $orderItem['id']));
                                        $orderId = $orderItem['id'];
                                    } else {
                                        $articleName = $_POST['articleName'][$key];
                                        $pricePerPiece = str_replace(",", ".", $_POST['pricePerPiece'][$key]);
                                        $amount = str_replace(",", ".", $_POST['quantity'][$key]);
                                        $discountPercent = str_replace(",", ".", $_POST['discount'][$key]);

                                        $periodization = intval($_POST['periodization'][$key]);
                                        $dateFrom = null;
                                        $dateTo = null;

                                        if($periodization == 1){
                                            $dateFrom = date("Y-m-d", strtotime("01.".$_POST['dateFromMonth'][$key]));
                                            $dateTo = date("Y-m-t", strtotime("01.".$_POST['dateToMonth'][$key]));
                                        }
                                        if($periodization == 2){
                                            $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom'][$key]));
                                            $dateTo =  date("Y-m-d", strtotime($_POST['dateTo'][$key]));
                                        }

                                        $periodizationMonths = "";

                                        if($periodization == 0){
                                            $dateFrom = $orderItem['dateFrom'];
                                            $dateTo = $orderItem['dateTo'];
                                        } else {
                                            $start    = (new DateTime($dateFrom))->modify('first day of this month');
                                            $end      = (new DateTime($dateTo))->modify('first day of next month');
                                            $interval = DateInterval::createFromDateString('1 month');
                                            $period   = new DatePeriod($start, $interval, $end);
                                            foreach ($period as $dt) {
                                                $periodizationMonths .= $dt->format("mY") . ",";
                                            }
                                            if(strlen($periodizationMonths) > 0){
                                                $periodizationMonths = substr($periodizationMonths, 0, -1);
                                            }
                                        }

                                        $priceTotal = round($pricePerPiece * $amount * (100-$discountPercent)/100, 2);

                                        $s_sql = "INSERT INTO orders SET
                                        moduleID = ?,
                                        created = now(),
                                        createdBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        describtion= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        Status = ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        collectingorderId = ?,
                                        periodization = ?,
                                        dateFrom = ?,
                                        dateTo = ?,
                                        periodizationMonths = ?";
                                        $o_main->db->query($s_sql, array(0, $variables->loggID, $article['id'], $articleName, '', str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'], 1, $bookaccountNr, $vatCode, $vatPercent, $collectingorderId, $periodization, $dateFrom, $dateTo, $periodizationMonths));
                                        $orderId = $o_main->db->insert_id();
                                    }
                                    $addedOrderlines[] = $orderId;
                                } else {
                                    if($vatCodeError){
                                        $fw_error_msg[$key] = $formText_VatCodeDoesntExist_output;
                                    }
                                    if($bookAccountError){
                                        $fw_error_msg[$key] = $formText_BookAccountDoesntExist_output;
                                    }
                                    if($articleError){
                                        $fw_error_msg[$key] = $formText_InvalidArticleNumber_output;
                                    }
                                    if($projectError){
                                        $fw_error_msg[$key] = $formText_InvalidProjectFAccNumber_output;
                                    }
                                    if($departmentError){
                                    }
                                }
                            }
                        }
                    } else {
                        if($departmentError){
                            $fw_error_msg[-1] = $formText_InvalidDepartmentFAccNumber_output;
                        }
                    }
                    if($fw_error_msg == "" || empty($fw_error_msg)){
                        $fw_return_data = $o_main->db->insert_id();
                        $fw_redirect_url = $_POST['redirect_url'];
                    }

                    //delete orderlines that were removed
                    $s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ?";
                    $o_query = $o_main->db->query($s_sql, array($project['id']));
                    $orders_aftersave = ($o_query ? $o_query->result_array() : array());
                    $to_be_deleted = array();
                    foreach($orders_aftersave as $order_aftersave) {
                        $intheArray = false;
                        foreach($addedOrderlines as $addedOrderlineId) {
                            if($addedOrderlineId == $order_aftersave['id']) {
                                $intheArray = true;
                            }
                        }
                        if(!$intheArray) {
                            $to_be_deleted[] = $order_aftersave['id'];
                        }
                    }
                    foreach($to_be_deleted as $to_be_deleted_id) {
                        $sql = "DELETE orders FROM orders WHERE orders.id = ?";
                        $o_main->db->query($sql, array($to_be_deleted_id));
                    }


                } else {
                    $departmentError = false;
                    if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] == 3) {
                        $s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
                        $o_query = $o_main->db->query($s_sql, array($_POST['departmentCode']));
                        $departmentItem = $o_query ? $o_query->row_array() : array();
                        if(!$departmentItem){
                            $departmentError = true;
                        }
                    }
                    $globalError = false;
                    foreach($_POST['articleId'] as $key=>$articleId){
                        $pricePerPieceError = false;
                        $pricePerPiece = str_replace(",", ".", $_POST['pricePerPiece'][$key]);
                        if($pricePerPiece < 0){
                            $globalError = true;
                            $fw_error_msg[$key] = $formText_PricePerPieceCanNotBeNegativeUseNegativeAmount_output;
                        }
                    }
                    if(!$departmentError && !$globalError) {
                        $connectedProjectId = $_POST['projectId'];
                        $connectedProject2Id = $_POST['project2Id'];
                        $connectedProject2PeriodId = $_POST['project2PeriodId'];
                        $batch_invoicing_sql = "";
                        if($customer_basisconfig['activate_mark_neworders_for_batchinvoicing']){
                            $batch_invoicing_sql = ", approvedForBatchinvoicing = 1";
                        }

                        $s_sql = "SELECT * FROM customer WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
                        $customerInfo = $o_query ? $o_query->row_array() : array();

                        $sql = "INSERT INTO customer_collectingorder SET
                        created = now(),
                        createdBy='".$variables->loggID."',
                        date = '".date("Y-m-d", strtotime($_POST['date']))."',
                        customerId = '".$_POST['customerId']."',
                        contactpersonId = '".$_POST['contactPerson']."',
                        projectId = '".$connectedProjectId."',
                        project2Id = '".$connectedProject2Id."',
                        project2PeriodId = '".$connectedProject2PeriodId."',
                        accountingProjectCode = '".$_POST['projectCode']."',
                        department_for_accounting_code = '".$_POST['departmentCode']."',
                        reference = '".$customerInfo['defaultInvoiceReference']."',
                        ownercompanyId = ?".$batch_invoicing_sql;

                        $o_query = $o_main->db->query($sql, array($ownercompany_id));
                        $insert_id = $o_main->db->insert_id();

                        foreach($_POST['articleId'] as $key=>$articleId){
                            $s_sql = "SELECT * FROM article WHERE article.id = ?";
                            $o_query = $o_main->db->query($s_sql, array($articleId));
                            $article = ($o_query ? $o_query->row_array() : array());
                            if($article){
                                $articleName = $_POST['articleName'][$key];
                                $pricePerPiece = round(str_replace(",", ".", $_POST['pricePerPiece'][$key]), 2);
                                $amount = round(str_replace(",", ".", $_POST['quantity'][$key]), 4);
                                $discountPercent = round(str_replace(",", ".", $_POST['discount'][$key]), 2);
                                $periodization = intval($_POST['periodization'][$key]);


                                $vatCode = $_POST['vatCode'][$key];
                                $bookaccountNr = $_POST['bookaccountNr'][$key];
                                $vatPercent = 0;

                                $noError = true;

                                $vatCodeError = false;
                                $bookAccountError = false;
                                $articleError = false;
                                $projectError = false;

                                $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                $o_query = $o_main->db->query($s_sql, array($vatCode));
                                $vatcodeItem = $o_query ? $o_query->row_array() : array();
                                if(!$vatcodeItem){
                                    $noError = false;
                                    $vatCodeError = true;
                                }

                                $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
                                $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
                                $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                if(!$bookaccountItem){
                                    $noError = false;
                                    $bookAccountError = true;
                                }

                                $s_sql = "SELECT * FROM article WHERE id = ?";
                                $o_query = $o_main->db->query($s_sql, array($articleId));
                                $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                if(!$bookaccountItem){
                                    $noError = false;
                                    $articleError = true;
                                }

                                if($customer_basisconfig['activeAccountingProjectOnOrder'] > 1) {
                                    $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
                                    $o_query = $o_main->db->query($s_sql, array($_POST['projectCode']));
                                    $bookaccountItem = $o_query ? $o_query->row_array() : array();
                                    if(!$bookaccountItem){
                                        $noError = false;
                                        $projectError = true;
                                    }
                                }
                                if($noError){

                                    $customerId = $_POST['customerId'];

                                    $priceTotal = round($pricePerPiece * $amount * (100-$discountPercent)/100, 2);

                                    $s_sql = "INSERT INTO orders SET
                                        moduleID = ?,
                                        created = now(),
                                        createdBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        describtion= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        Status = ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        collectingorderId = ?,
                                        periodization = ?,
                                        dateFrom = ?,
                                        dateTo = ?,
                                        periodizationMonths = ?";
                                        $o_main->db->query($s_sql, array(0, $variables->loggID, $article['id'], $articleName, '', str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'], 1, $bookaccountNr, $vatCode, $vatPercent, $insert_id, $periodization, $dateFrom, $dateTo, $periodizationMonths));

                                    $orderId = $o_main->db->insert_id();
                                    $addedOrderlines[] = $orderId;
                                } else {
                                    if($vatCodeError){
                                        $fw_error_msg[$key] = $formText_VatCodeDoesntExist_output;
                                    }
                                    if($bookAccountError){
                                        $fw_error_msg[$key] = $formText_BookAccountDoesntExist_output;
                                    }
                                    if($articleError){
                                        $fw_error_msg[$key] = $formText_InvalidArticleNumber_output;
                                    }
                                    if($projectError){
                                        $fw_error_msg[$key] = $formText_InvalidProjectFAccNumber_output;
                                    }
                                }
                            }
                        }
                    } else {
                        if($departmentError){
                            $fw_error_msg[-1] = $formText_InvalidDepartmentFAccNumber_output;
                        }
                    }
                    if($fw_error_msg == "" || empty($fw_error_msg)){
                        $fw_return_data = $insert_id;
                        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
                    }
                }
            }
        }
        if($collectingorderId) {
            $sql = "SELECT * FROM customer_collectingorder WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($collectingorderId));
            $projectData = $o_query ? $o_query->row_array() : array();

            $sql = "SELECT * FROM customer WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($projectData['customerId']));
            $customer = $o_query ? $o_query->row_array() : array();
        }

        $showDeleteConfirmation = false;
        if($action == 'deleteOrderByStatus' && $moduleAccesslevel > 110)
        {
            $sql = "UPDATE customer_collectingorder SET content_status = 2 WHERE customer_collectingorder.id = ?";
            $o_main->db->query($sql, array($collectingorderId));
            $fw_return_data = "deletedOrder";
        }
        if($action == 'deleteOrder' && $moduleAccesslevel > 110)
        {
             $sql = "DELETE customer_collectingorder, orders FROM customer_collectingorder LEFT JOIN orders ON orders.collectingorderId = customer_collectingorder.id WHERE customer_collectingorder.id = ?";
            $o_main->db->query($sql, array($collectingorderId));
            $fw_return_data = "deletedOrder";
        }
        if($action == "deleteOrderline" && $moduleAccesslevel > 110){
            $sql = "DELETE orders FROM orders WHERE orders.id = ?";
            $o_main->db->query($sql, array($_POST['orderlineid']));
            $fw_return_data = "deletedOrderline";
        }
        $s_sql = "select * from contactperson where customerId = ? AND content_status = 0 order by sortnr";;
        $o_query = $o_main->db->query($s_sql, array($customerId));
        $contactPersons = $o_query ? $o_query->result_array() : array();

        $s_sql = "select * from project where id = ?";
        $o_query = $o_main->db->query($s_sql, array($project['projectId']));
        $connectedProject = $o_query ? $o_query->row_array() : array();

        $s_sql = "select * from project2 where id = ?";
        $o_query = $o_main->db->query($s_sql, array($project['project2Id']));
        $connectedProject2 = $o_query ? $o_query->row_array() : array();

        $s_sql = "select * from project2_periods where id = ?";
        $o_query = $o_main->db->query($s_sql, array($project['project2PeriodId']));
        $connectedProject2Period = $o_query ? $o_query->row_array() : array();

        $s_sql = "SELECT * FROM article_accountconfig";
        $o_query = $o_main->db->query($s_sql);
        $article_accountconfig = $o_query ? $o_query->row_array() : array();
        ?>
        <div class="popupform">
            <div id="popup-validate-message" style="display:none;"></div>
            <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCollectingOrder";?>" method="post">
                <input type="hidden" name="fwajax" value="1">
                <input type="hidden" name="fw_nocss" value="1">
                <input type="hidden" name="output_form_submit" value="1">
                <input type="hidden" name="collectingorderId" value="<?php echo $collectingorderId;?>" id="collectingorderId">
                <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
                <div class="defaultForm">
                    <div class="inner">
                        <div class="popupformTitle"><?php echo $formText_Order_output;?></div>
                        <div class="fieldWrapper">

                            <?php if(count($ownercompanies) > 1){ ?>
                                <div class="line">
                                    <div class="lineTitle"><?php echo $formText_ChooseOwnerCompany_Output; ?></div>
                                    <div class="lineInput">
                                    <select name="ownercompany_id" class="buildingOwner" required>
                                        <option value="" data-projectcode="0"><?php echo $formText_Select_output;?></option>
                                        <?php foreach ($ownercompanies as $ownercompany): ?>
                                            <option value="<?php echo $ownercompany['id']; ?>" <?php echo $projectData['ownercompanyId'] == $ownercompany['id'] ? 'selected="selected"' : ''; ?> data-projectcode="<?php echo $ownercompany['accountingproject_code']?>"><?php echo $ownercompany['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            <?php } else if(count($ownercompanies) == 1) {  ?>
                                <input type="hidden" value="<?php echo $ownercompanies[0]['id']?>" name="ownercompany_id"  class="buildingOwner" data-projectcode="-1"/>
                            <?php } ?>
                            <?php if($customer_basisconfig['activeAccountingProjectOnOrder']) { ?>
                                <div class="line">
                                    <div class="lineTitle"><?php echo $formText_Project_Output; ?></div>
                                    <div class="lineInput projectWrapper">

                                    </div>
                                    <div class="clear"></div>
                                </div>
                            <?php } ?>
                            <?php if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] > 1) {
                                if(!$projectData['project2Id']) { ?>
                                    <div class="line">
                                        <div class="lineTitle"><?php echo $formText_Department_Output; ?></div>
                                        <div class="lineInput departmentWrapper">

                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                <?php } else { ?>
                                    <div class=""><input type="hidden" name="departmentCode" value="<?php echo $connectedProject2['departmentCode']?>"/></div>
                                <?php } ?>
                            <?php } ?>
                            <div class="line">
                                <div class="lineTitle"><?php echo $formText_OrderDate_Output; ?></div>
                                <div class="lineInput">
                                    <span class="dateWrapper"><?php if($projectData['date'] != "0000-00-00" && $projectData['date'] != null){ echo date("d.m.Y", strtotime($projectData['date'])); } else { echo date("d.m.Y"); }?></span>
                                    <input type="text" style="display:none;" class="popupforminput botspace datefield shortinput collectingOrderDate" name="date" value="<?php if($projectData['date'] != "0000-00-00" && $projectData['date'] != null){ echo date("d.m.Y", strtotime($projectData['date'])); } else { echo date("d.m.Y"); }?>" required autocomplete="off">
                                    <span class="glyphicon glyphicon-pencil editCollectingOrderDate"></span>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="line projectLeaderWrapper">
                                <div class="lineTitle"><?php echo $formText_Customer_Output; ?></div>
                                <div class="lineInput">
                                    <?php
                                    $s_sql = "SELECT * FROM customer  WHERE customer.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($customerId));
                                    $customer = ($o_query ? $o_query->row_array() : array());

                                    if($customer) { ?>
                                    <a href="#" class="selectCustomer"><?php echo $customer['name']." ".$customer['middlename']." ".$customer['lastname'];?></a>
                                    <?php } else { ?>
                                    <a href="#" class="selectCustomer"><?php echo $formText_SelectCustomer_Output;?></a>
                                    <?php } ?>
                                    <input type="hidden" name="customerId" id="customerId" value="<?php print $customer['id'];?>" required>
                                    <?php if($connectedProject){?>
                                        <input type="hidden" class="customerChanged" value="0"/>
                                    <?php } ?>

                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="line contactPersonWrapper">
                                <div class="lineTitle"><?php echo $formText_ContactPerson_Output; ?></div>
                                <div class="lineInput contactPersonSelectWrapper">
                                    <select name="contactPerson">
                                        <option value=""><?php echo $formText_Select_output;?></option>
                                        <?php
                                        foreach($contactPersons as $contactPerson) {
                                            ?>
                                            <option value="<?php echo $contactPerson['id'];?>" <?php if($projectData['contactpersonId'] == $contactPerson['id']) echo 'selected';?>><?php echo $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname'];?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php if($customer_basisconfig['connectOrderToProject'] > 0) { ?>
                                <div class="line projectConnectWrapper">
                                    <?php if($customer_basisconfig['connectOrderToProject'] == 1) { ?>
                                        <a href=""# class="connectToProjectLink">
                                            <?php
                                            if(intval($project['projectId']) == 0){
                                                echo $formText_ConnectToProject_Output.": <span></span>";
                                            } else {
                                                echo $formText_ConnectToProject_Output.": <span>".$connectedProject['name']."</span>";
                                            }
                                            ?>
                                            <input type="hidden" name="projectId" id="projectId" value="<?php print $project['projectId'];?>">
                                        </a>
                                    <?php } ?>
                                    <?php if($customer_basisconfig['connectOrderToProject'] == 2) { ?>
                                        <a href=""# class="connectToProject2Link">
                                            <?php
                                            if(intval($project['project2Id']) == 0){
                                                echo $formText_ConnectToProject_Output.": <span></span>";
                                            } else {
                                                echo $formText_ConnectToProject_Output.": <span>".$connectedProject2['name'];
                                                if($connectedProject2Period){
                                                    echo " - ".$formText_Period_output." ".$connectedProject2Period['id'];
                                                }
                                                echo "</span>";
                                            }
                                            ?>
                                            <input type="hidden" name="project2Id" id="project2Id" value="<?php print $project['project2Id'];?>">
                                            <input type="hidden" name="project2PeriodId" id="project2PeriodId" value="<?php print $project['project2PeriodId'];?>">
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="articleTable">
                        <table class="table table-bordered articleTableWrapper">
                            <tr>
                                <th width="10%"><?php if($article_accountconfig['activateArticleCode']) { echo $formText_ArticleCode_output; } else { echo $formText_ArticleNr_output; } ?></th>
                                <th width="30%"><?php echo $formText_ProductName_output;?></th>
                                <th width="20%"><?php echo $formText_Accounting_output;?></th>
                                <th width="10%"><?php echo $formText_Quantity_output;?></th>
                                <th width="10%"><?php echo $formText_PricePerPiece_output;?></th>
                                <th width="10%"><?php echo $formText_Discount_output;?> %</th>
                                <th width="10%"><?php echo $formText_PriceTotal_output;?></th>
                            </tr>
                            <?php




                            $s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ? AND orders.content_status = 0  ORDER BY orders.id ASC";
                            $o_query = $o_main->db->query($s_sql, array($projectData['id']));
                            $orders = ($o_query ? $o_query->result_array() : array());

                            foreach($orders as $order){
                                $periodising = false;
                                $s_sql = "SELECT * FROM article WHERE article.id = ?";
                                $o_query = $o_main->db->query($s_sql, array($order['articleNumber']));
                                $article = ($o_query ? $o_query->row_array() : array());
                                $o_query = $o_main->db->query("SELECT * FROM article_supplier WHERE id = '".$o_main->db->escape_str($article['article_supplier_id'])."'");
                        		$articleSupplier = $o_query ? $o_query->row_array() : array();
                                if($order['periodization'] > 0){
                                    $periodising = true;
                                }
                                $decimalNumber = 4;
                                if($order['not_full_order']){
                                    $decimalNumber = 4;
                                }
                            ?>
                            <tr class='articleRow'>
                                <td width="10%">
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                        <input type='hidden' name='ordersId[]' value='<?php echo $order['id']; ?>'/>
                                    <?php } ?>
                                    <div class="articleIDwrapper">
                                        <span class="articleID"> <?php if($article_accountconfig['activateArticleCode']) { if($articleSupplier['supplier_prefix'] != "") echo $articleSupplier['supplier_prefix'].'_'; echo $article['articleCode']; } else { echo $article['id']; } ?> </span>
                                        <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                        <input type='hidden' name='articleId[]' class="articleIdInput" value='<?php echo $order['articleNumber']; ?>'/>
                                        <span class="glyphicon glyphicon-pencil edit-articleid"></span>
                                        <?php } ?>
                                    </div>
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                    <div class='employeeSearch' style="display: none;">
                                        <span class='glyphicon glyphicon-search'></span>
                                        <input type='text' placeholder='<?php echo $formText_Search_output;?>' class='articleName' style='width:100%;' autocomplete="off"/>
                                        <span class='glyphicon glyphicon-triangle-right'></span>
                                        <div class='employeeSearchSuggestions allowScroll'></div>
                                    </div>
                                    <?php } ?>
                                </td>
                                <td width="30%">
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                    <input type='text' name='articleName[]' class='articleNameInput' value='<?php echo $order['articleName']; ?>' autocomplete="off"/>
                                    <?php } else {
                                        echo $order['articleName'];
                                    }?>
                                </td>
                                <td width="20%" class="accountingInfoTable">
                                    <div>
                                        <div class="errorText" style="display: none;"></div>
                                        <span class="accountingInfo"><?php echo $order['bookaccountNr']; if($order['vatCode'] != "") echo " - ".$order['vatCode']; if($periodising) echo " - P";?></span>
                                        <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                            <span class="glyphicon glyphicon-pencil edit-accountingInfo"></span>
                                        <?php } ?>
                                    </div>
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                    <div class="accountinfoWrapper" style="display: none;">
                                        <label><?php echo $formText_BookAccountNr_Output; ?></label>
                                        <span class="bookaccountNrWrapper accountInfoSelect">
                                            <select name="bookaccountNr[]" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?>>
                                                <option value=""><?php echo $formText_SelectBookAccountNr_output; ?></option>
                                                <?php
                                                $rows = array();
                                                $s_sql = "SELECT * FROM bookaccount GROUP BY accountNr ORDER BY accountNr ASC";
                                                $o_query = $o_main->db->query($s_sql);
                                                if($o_query && $o_query->num_rows()>0) {
                                                    $rows = $o_query->result_array();
                                                }
                                                foreach($rows as $row){ ?>
                                                    <option value="<?php echo $row['accountNr']; ?>" <?php echo $row['accountNr'] == $order['bookaccountNr'] ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $row['accountNr']." - ".$row['name']; ?>
                                                    </option>
                                                <?php
                                                } ?>
                                            </select>
                                        </span><br/>
                                        <label><?php echo $formText_VatCode_Output; ?></label>
                                        <span class="vatCodeWrapper accountInfoSelect">
                                            <select name="vatCode[]" <?php if($article_accountconfig['activateVatcodeMandatory']){ ?> required <?php } ?>>
                                                <option value=""><?php echo $formText_SelectVatCode_output; ?></option>
                                                <?php
                                                $rows = array();
                                                $s_sql = "SELECT * FROM vatcode GROUP BY vatCode ORDER BY vatCode ASC";
                                                $o_query = $o_main->db->query($s_sql);
                                                if($o_query && $o_query->num_rows()>0) {
                                                    $rows = $o_query->result_array();
                                                }
                                                foreach($rows as $row){ ?>
                                                    <option value="<?php echo $row['vatCode']; ?>" <?php echo $row['vatCode'] == $order['vatCode'] ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $row['vatCode']." - ".$row['name']; ?>
                                                    </option>
                                                <?php
                                                } ?>
                                            </select>
                                        </span><br/>
                                        <label><?php echo $formText_Periodising_Output; ?></label>
                                        <span class="periodisingWrapper accountInfoSelect">
                                            <select name="periodization[]" class="periodization">
                                                <option value="0" <?php if($order['periodization'] == 0) { echo 'selected';}?>><?php echo $formText_None_output; ?></option>
                                                <option value="1" <?php if($order['periodization'] == 1) { echo 'selected';}?>><?php echo $formText_DivideOnMonths_output; ?></option>
                                                <option value="2" <?php if($order['periodization'] == 2) { echo 'selected';}?>><?php echo $formText_DivideOnDays_output; ?></option>
                                            </select>
                                        </span>
                                        <div class="periodisingDates">
                                            <label><?php echo $formText_FromDate_Output; ?></label>
                                            <input type="text" class="datefield botspace" name="dateFrom[]" value="<?php if ($order['dateFrom'] != '0000-00-00' && $order['dateFrom'] && !empty($order['dateFrom'])) echo date('d.m.Y', strtotime($order['dateFrom'])); ?>" autocomplete="off">
                                            <br/>
                                            <label><?php echo $formText_TillDate_Output; ?></label>
                                            <input type="text" class="datefield botspace" name="dateTo[]" value="<?php if ($order['dateTo'] != '0000-00-00' && $order['dateTo'] && !empty($order['dateTo'])) echo date('d.m.Y', strtotime($order['dateTo'])); ?>" autocomplete="off">
                                        </div>
                                        <div class="periodisingMonths">
                                            <label><?php echo $formText_FromMonth_Output; ?></label>
                                            <input type="text" class="monthfield botspace" name="dateFromMonth[]" value="<?php if ($order['dateFrom'] != '0000-00-00' && $order['dateFrom'] && !empty($order['dateFrom'])) echo date('m.Y', strtotime($order['dateFrom'])); ?>" autocomplete="off">
                                            <br/>
                                            <label><?php echo $formText_TillMonth_Output; ?></label>
                                            <input type="text" class="monthfield botspace" name="dateToMonth[]" value="<?php if ($order['dateTo'] != '0000-00-00' && $order['dateTo'] && !empty($order['dateTo'])) echo date('m.Y', strtotime($order['dateTo'])); ?>" autocomplete="off">
                                        </div>
                                        <div class="clear"></div>
                                        <div class="output-btn close-accountinginfo"><?php echo $formText_Close_output;?></div>
                                    </div>
                                    <?php } ?>
                                </td>
                                <td width='10%' class='quantity' data-value="<?php echo number_format($order['amount'], $decimalNumber, ".", ""); ?>">
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                        <input type='text' name='quantity[]' class='quantityInput' value='<?php echo number_format($order['amount'], $decimalNumber, ",", ""); ?>' autocomplete="off"/>
                                    <?php } else {
                                        echo $order['amount'];
                                    } ?>
                                </td>
                                <td width='10%' class='pricePerPiece' data-value="<?php echo number_format($order['pricePerPiece'], 2, ".", ""); ?>">
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                        <input type='text' name='pricePerPiece[]' class='pricePerPieceInput' value='<?php echo number_format($order['pricePerPiece'], 2, ",", ""); ?>' autocomplete="off"/>
                                    <?php } else {
                                        echo $order['pricePerPiece'];
                                    } ?>
                                </td>
                                <td width='10%' class='discount' data-value="<?php echo number_format($order['discountPercent'], 2, ".", "");?>">
                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                    <input type='text' name='discount[]' class='discountInput' value='<?php echo number_format($order['discountPercent'], 2, ",", ""); ?>' autocomplete="off"/>
                                    <?php } else {
                                        echo $order['discountPercent'];
                                    } ?>
                                </td>
                                <td width='10%'>
                                    <span class="priceTotal">
                                        <?php echo number_format($order['priceTotal'], 2, ".", ""); ?>
                                    </span>

                                    <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                        <span class="output-delete-orderline output-btn small editBtnIcon" data-order-id="<?php echo $order['id'];?>" data-project-id="<?php echo $collectingorderId;?>"><span class="glyphicon glyphicon-trash"></span></span>
                                    <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>

                        </table>
                        <div class='employeeSearch addNewOrderlineSearch'>
                            <span class='glyphicon glyphicon-search'></span>
                            <input type='text' placeholder='<?php echo $formText_SearchAndAdd_output;?>' class='articleName' style='width:100%;' autocomplete="off"/>
                            <span class='glyphicon glyphicon-triangle-right'></span>
                            <div class='employeeSearchSuggestions allowScroll'></div>
                        </div>
                        <?php if($article_accountconfig['activate_supplier_products']) { ?>
                            <div class="article_supplier_dropdown">
                                <select class="supplierChoose">
                                    <option value="0"><?php echo $formText_RegularArticle_output;?></option>
                                    <?php
                                    $s_sql = "SELECT * FROM article_supplier WHERE article_supplier.content_status < 2 ORDER BY article_supplier.name ASC";
                                    $o_query = $o_main->db->query($s_sql);
                                    $suppliers = ($o_query ? $o_query->result_array() : array());
                                    foreach($suppliers as $supplier) {
                                    ?>
                                        <option value="<?php echo $supplier['id'];?>"><?php echo $formText_FromSupplier_output." ".$supplier['name'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>

                        <div class="totalPriceBlock">
                            <?php echo $formText_Total_output;?>:
                            <div class="grandTotalPrice"></div>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="popupformbtn">
                    <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                    <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
                </div>
            </form>
        </div>
        <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
        <script type="text/javascript">
        function numberWithSpaces(x) {
            var parts = x.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            return parts.join(".");
        }
        function calculateTotal(){
            var globalTotal = 0;
            $(".articleTable .articleRow").each(function(){
                var pricePerPiece = parseFloat($(this).find(".pricePerPiece").data("value").toString().replace(",", ".")).toFixed(2);
                var quantity = parseFloat($(this).find(".quantity").data("value").toString().replace(",", ".")).toFixed(4);
                var discount = parseFloat($(this).find(".discount").data("value").toString().replace(",", ".")).toFixed(2);
                var totalPerRow = pricePerPiece * quantity * (100-discount)/100;
                totalPerRow = parseFloat(totalPerRow).toFixed(2);
                globalTotal += parseFloat(totalPerRow);
                totalPerRow = numberWithSpaces(totalPerRow);
                totalPerRow = totalPerRow.toString().replace(".", ",");
                $(this).find(".priceTotal").html(totalPerRow);
            })
            globalTotal = parseFloat(globalTotal).toFixed(2);
            globalTotal = numberWithSpaces(globalTotal);
            globalTotal = globalTotal.toString().replace(".", ",");
            $(".grandTotalPrice").html(globalTotal);
            $(window).resize();
        }
        function rebindTable(){
            $(".accountInfoSelect").change(function(){
                var parent = $(this).parents("td");
                var label = parent.find(".accountingInfo");
                var bookaccountNr = parent.find(".bookaccountNrWrapper select").val();
                var vatcode = parent.find(".vatCodeWrapper select").val();
                var periodising = parent.find(".periodisingWrapper select").val();
                var finalText =bookaccountNr;
                if(vatcode != ""){
                    finalText += " - "+vatcode;
                }
                if(periodising > 0){
                    finalText += " - P";
                }
                label.html(finalText);
            })
            $(".close-accountinginfo").unbind("click").bind("click", function(){
                var parent = $(this).parents("td");
                var input = parent.find(".accountinfoWrapper");
                if(input.is(":visible")){
                    input.hide();
                } else {
                    input.show();
                }
            })
            $(".edit-articleid").unbind("click").bind("click", function(){
                var parent = $(this).parents("td");
                var label = parent.find(".articleNameText");
                var input = parent.find(".employeeSearch");
                if(input.is(":visible")){
                    input.hide();
                } else {
                    input.show();
                }
            })
            $(".edit-accountingInfo").unbind("click").bind("click", function(){
                var parent = $(this).parents("td");
                var input = parent.find(".accountinfoWrapper");
                if(input.is(":visible")){
                    input.hide();
                } else {
                    input.show();
                }
            })

            $(".quantityInput").bind('keyup change', function(){
                $(this).parents(".quantity").data("value", $(this).val());
                calculateTotal();
            })
            $(".pricePerPieceInput").bind('keyup change', function(){
                $(this).parents(".pricePerPiece").data("value", $(this).val());
                calculateTotal();
            })
            $(".discountInput").bind('keyup change', function(){
                if($(this).val() < 0){
                    $(this).val(0);
                }
                if($(this).val() > 100){
                    $(this).val(100);
                }
                $(this).parents(".discount").data("value", $(this).val());
                calculateTotal();
            })

            $(".periodization").change(function(){
                var value = $(this).val();
                if(value == 0){
                    $(this).parents(".accountinfoWrapper").find(".periodisingMonths").hide();
                    $(this).parents(".accountinfoWrapper").find(".periodisingDates").hide();
                } else if(value == 2){
                    $(this).parents(".accountinfoWrapper").find(".periodisingMonths").hide();
                    $(this).parents(".accountinfoWrapper").find(".periodisingDates").show();
                } else if(value == 1){
                    $(this).parents(".accountinfoWrapper").find(".periodisingMonths").show();
                    $(this).parents(".accountinfoWrapper").find(".periodisingDates").hide();
                }
            })
            $(".periodization").change();

            $(".output-delete-neworderline").off("click").on("click", function(){
                $(this).parents(".articleRow").remove();
                calculateTotal();
            })

            var loadingArticle2 = false;
            var $input2 = $(".articleTableWrapper .articleName");
            var article_search_value2;
            $input2.each(function(index, el){
               var parent = $(el).parents("tr");
               $(el).unbind("focusin").on('focusin', function () {
                   searchArticleSuggestions2(parent, true);
                   $(".output-form").unbind("click").bind("click", function (ev) {
                       if($(ev.target).parents(".employeeSearch").length == 0){
                           $(".employeeSearchSuggestions").hide();
                       }
                   });
               })
               //on keyup, start the countdown
               $(el).unbind("keyup").on('keyup', function () {
                   searchArticleSuggestions2(parent, true);
               });

               //on keydown, clear the countdown
               $(el).unbind("keydown").on('keydown', function () {
                   searchArticleSuggestions2(parent, true);
               });
            })
            function searchArticleSuggestions2 (parent, addLoading){
                if(!loadingArticle2) {
                    if(article_search_value2 != parent.find(".articleName").val() || article_search_value2 == "") {
                        loadingArticle2 = true;
                        article_search_value2 = parent.find(".articleName").val();
                        if(addLoading){
                            parent.find('.employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                        }
                        var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', supplier_id: $(".supplierChoose").val(), ownercompany_id: $(".buildingOwner").val()};
                        $.ajax({
                            cache: false,
                            type: 'POST',
                            dataType: 'json',
                            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles_suggestions";?>',
                            data: _data,
                            success: function(obj){
                                loadingArticle2 = false;
                                if(parent.find(".articleName").val() != "" && article_search_value2 != parent.find(".articleName").val()){
                                    searchArticleSuggestions2(parent, false);
                                } else {
                                    $('.employeeSearchSuggestions').html('').hide();
                                    parent.find('.employeeSearchSuggestions').html(obj.html).show();
                                }
                            }
                        }).fail(function(){
                            loadingArticle2 = false;
                        })
                    }
                }
            }
        }
        var initownerValue = $(".popupform .buildingOwner").val();
		$(".popupform .buildingOwner").unbind("change").change(function(){

			var buildingOwnerProjectCode = -1;
			<?php if($customer_basisconfig['activateFilterProjectByOwnercompany']) { ?>
				buildingOwnerProjectCode = $(".buildingOwner").data("projectcode");
				if($(".buildingOwner option").length > 0) {
					buildingOwnerProjectCode = $(".buildingOwner option:selected").data("projectcode");
				}
			<?php } ?>
			<?php if($customer_basisconfig['activeAccountingProjectOnOrder']) { ?>
			/*var data = {
				get_item: 1,
				ownercompany_id: $('.buildingOwner').val(),
				projectCode: '<?php echo $projectData['accountingProjectCode']?>',
				<?php if(empty($projectData['id']) && $v_customer_accountconfig['activate_add_accounting_project_number_on_new_order'] > 0) { ?>
					customer_id: $('#customerId').val(),
				<?php } ?>
			};
			ajaxCall('get_accounting_projects', data, function(json) {
				if(json.data !== undefined)
				{
					$(".output-form #projectCode").val(json.data.projectnumber);
					$(".output-form .selectProject").html(json.data.name);
				}
			});*/
			var data = {
				ownercompany_id: $('.buildingOwner').val(),
				buildingOwnerProjectCode: buildingOwnerProjectCode,
				projectCode: '<?php echo $projectData['accountingProjectCode']?>',
				<?php if($customer_basisconfig['activeAccountingProjectOnOrder'] > 1) { ?>
					projectMandatory: 1,
				<?php } ?>
				<?php if(empty($projectData['id']) && $v_customer_accountconfig['activate_add_accounting_project_number_on_new_order'] > 0) { ?>
					customer_id: $('#customerId').val(),
				<?php } ?>
				<?php if($customer_basisconfig['activeAccountingProjectOnOrder'] == 3) { ?>
                getProjectFromCustomerContent: $('#customerId').val(),
                <?php } ?>
			};
			ajaxCall('getProjects', data, function(json) {
				$('.popupform .projectWrapper').html(json.html);
			});

			<?php } ?>
			<?php if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] > 1) { ?>
			var data = {
				buildingOwnerProjectCode: buildingOwnerProjectCode,
				departmentCode: '<?php echo $projectData['department_for_accounting_code']?>',
				<?php if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] == 3) { ?>
					projectMandatory: 1
				<?php } ?>
			};
			ajaxCall('getAccountingDepartments', data, function(json) {
				$('.popupform .departmentWrapper').html(json.html);
			});
			<?php } ?>
            <?php  if($ownercompany_accountconfig['activate_company_product_sets']  && count($company_product_sets) > 0) { ?>
                if(initownerValue != $(".popupform .buildingOwner").val()){
                    bootbox.confirm('<?php echo $formText_SubscriptionlinesWillBeDeleted_output; ?>', function(result) {
                        if (result) {
                            $(".articleTable .articleRow").remove();
                            initownerValue = $(".popupform .buildingOwner").val();
                        } else {
                            $(".popupform .buildingOwner").val(initownerValue).change();
                        }
                    }).css({"z-index": 99999});
                }
            <?php }?>
		})
		$(".popupform .buildingOwner").change();


        $(document).ready(function() {
			<?php if(empty($order['id']) && $v_customer_accountconfig['activate_add_accounting_project_number_on_new_order']) { ?>
			$('#customerId').bind('change',function(e){ $(".popupform .buildingOwner").trigger('change'); });
            <?php } ?>
			calculateTotal();
            $(".popupform .notProjectCheckbox").unbind("change").change(function(){
                if($(this).is(":checked")) {
                    $(".projectNameWrapper").find("input").prop('required', false);
                    $(".projectLeaderWrapper").find("input").prop('required', false);
                    $(".projectNameWrapper").hide();
                    $(".projectLeaderWrapper").hide();

                    $(".projectStatusWrapper").hide();
                } else {
                    $(".projectNameWrapper").show();
                    $(".projectLeaderWrapper").show();
                    $(".projectNameWrapper").find("input").prop('required', true);
                    $(".projectLeaderWrapper").find("input").prop('required', true);

                    $(".projectStatusWrapper").show();
                }
            })
            $(".popupform .notProjectCheckbox").change();
            $(".popupform .editCollectingOrderDate").unbind("click").bind("click", function(){
                var collectingDate = $(this).parents(".lineInput");
                if(collectingDate.find("input").is(":visible")){
                    collectingDate.find(".dateWrapper").show();
                    collectingDate.find("input").hide();
                } else {
                    collectingDate.find(".dateWrapper").hide();
                    collectingDate.find("input").show();
                }
            })
            $(".popupform .collectingOrderDate").unbind("change").change(function(){
                $(".dateWrapper").html($(this).val());
            })
            $("form.output-form input[type='submit']").click(function(ev){
                ev.preventDefault();
                var needsConfirmation = false;
                if($(".customerChanged").val() == 1){
                    needsConfirmation = true;
                }
                if(needsConfirmation){
                    if(confirm("<?php echo $formText_ProjectWasResetContinue_output?>?")){
                        $("form.output-form").submit();
                    }
                }else {
                    $("form.output-form").submit();
                }
            })
            $("form.output-form").validate({
                ignore: [],
                submitHandler: function(form) {
                    if(!fw_click_instance)
            		{
            			fw_click_instance = true;
                        fw_loading_start();
                        $(".errorText").hide().html("");
                        $.ajax({
                            url: $(form).attr("action"),
                            cache: false,
                            type: "POST",
                            dataType: "json",
                            data: $(form).serialize(),
                            success: function (data) {
                                fw_click_instance = false;
                                fw_loading_end();
                                if(data.data == "confirmation") {
                                    $(".popupform .output-form").append("<input type='hidden' name='forceUpdate' value='1'/>");
                                    $(".popupform .defaultForm").hide();
                                    $(".popupform .confirmationForm").show();
                                } else if(data.data == "deletedOrderline") {
                                    out_popup.addClass("close-reload");
                                    out_popup2.addClass("deleted").data("order-id", <?php echo $_POST['orderlineid'];?>);
                                    out_popup2.close();
                                } else if(data.data == "deletedOrder") {
                                    out_popup.addClass("close-reload");
                                    out_popup.close();
                                } else {
                                    if(data.error !== undefined)
                                    {
                                        if(data.data !== undefined) {
                                            $("#collectingorderId").val(data.data);
                                        }
                                        $.each(data.error, function(index, value){
                                            var _type = Array("error");
                                            if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                            if(index == -1){
                                                $("#popup-validate-message").html(value);
                                            } else {
                                                $(".articleTableWrapper .articleRow").eq(index).find(".accountingInfoTable .errorText").html(value).show();
                                            }
                                        });
                                        $("#popup-validate-message").show();
                                        fw_loading_end();
                                        fw_click_instance = fw_changes_made = false;
                                    } else {
                                        if(data.redirect_url !== undefined)
                                        {
                                            out_popup.addClass("close-reload");
                                            out_popup.close();
                                        }
                                    }
                                }
                            }
                        }).fail(function() {
                            $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                            $("#popup-validate-message").show();
                            $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                            fw_loading_end();
                            fw_click_instance = false;
                        });
                    }

                },
                invalidHandler: function(event, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var message = errors == 1
                        ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                        : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                        $("#popup-validate-message").html(message);
                        $("#popup-validate-message").show();
                        $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    } else {
                        $("#popup-validate-message").hide();
                    }
                    setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
                },
                errorPlacement: function(error, element) {
                    if(element.attr("name") == "customerId") {
                        error.insertAfter(".selectCustomer");
                    }
                    if(element.attr("name") == "projectLeader") {
                        error.insertAfter(".popupform .selectEmployee");
                    }
                },
                messages: {
                    customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
                    projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
                }
            });
            $(".datefield").datepicker({
                firstDay: 1,
                beforeShow: function(dateText, inst) {
                    $(inst.dpDiv).removeClass('monthcalendar');
                },
                dateFormat: "dd.mm.yy"
            })

            $('.monthfield').datepicker({
                changeMonth: true,
                changeYear: true,
                showButtonPanel: true,
                dateFormat: 'mm.yy',
                beforeShow: function(dateText, inst) {
                    $(inst.dpDiv).addClass('monthcalendar');
                },
                onClose: function(dateText, inst) {
                    function isDonePressed() {
                        return ($('#ui-datepicker-div').html().indexOf('ui-datepicker-close ui-state-default ui-priority-primary ui-corner-all ui-state-hover') > -1);
                    }
                    if (isDonePressed()){
                        $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
                    }
                },
                firstDay: 1
            });

            $(".selectCustomer").unbind("click").bind("click", function(e){
                e.preventDefault();
                fw_loading_start();
                var _data = { fwajax: 1, fw_nocss: 1};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
                    data: _data,
                    success: function(obj){
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(obj.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                        fw_loading_end();
                    }
                });
            })

            $(".connectToProjectLink").unbind("click").bind("click", function(ev){
                ev.preventDefault();
                fw_loading_start();
                var _data = { fwajax: 1, fw_nocss: 1, customerId :$("#customerId").val()};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customer_projects";?>',
                    data: _data,
                    success: function(obj){
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(obj.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                        fw_loading_end();
                    }
                });
            })

            $(".connectToProject2Link").unbind("click").bind("click", function(ev){
                ev.preventDefault();
                fw_loading_start();
                var _data = { fwajax: 1, fw_nocss: 1, customerId :$("#customerId").val(), project2:true};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customer_projects";?>',
                    data: _data,
                    success: function(obj){
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(obj.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                        fw_loading_end();
                    }
                });
            })

            $(".quantityInput").bind('keyup change', function(){
                $(this).parents(".quantity").data("value", $(this).val());
                calculateTotal();
            })
            $(".pricePerPieceInput").bind('keyup change', function(){
                $(this).parents(".pricePerPiece").data("value", $(this).val());
                calculateTotal();
            })
            $(".discountInput").bind('keyup change', function(){
                if($(this).val() < 0){
                    $(this).val(0);
                }
                if($(this).val() > 100){
                    $(this).val(100);
                }
                $(this).parents(".discount").data("value", $(this).val());
                calculateTotal();
            })

            $(".output-delete-orderline").unbind("click").on('click', function(e){
                e.preventDefault();
                var self = $(this);
                var data = {
                    orderlineid: self.data('order-id'),
                    collectionOrderId: self.data('collectingorder-id'),
                    action: 'deleteOrderline'
                };
                bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
                    if (result) {
                        ajaxCall('editCollectingOrder', data, function(json) {
                            if(json.data == "confirmation"){
                                $('#popupeditboxcontent2').html('');
                                $('#popupeditboxcontent2').html(json.html);
                                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                                $("#popupeditbox2:not(.opened)").remove();
                            } else {
                                self.closest('tr').remove();
                                out_popup.addClass("close-reload");
                                calculateTotal();
                            }
                        });
                    }
                }).css({"z-index": 99999});
            });

            var loadingArticle = false;
            var $input = $('.addNewOrderlineSearch .articleName');
            var article_search_value;
            var parent = $input.parents(".addNewOrderlineSearch");
            $input.on('focusin', function () {
                searchArticleSuggestions(parent, true);
                $(".output-form").unbind("click").bind("click", function (ev) {
                    if($(ev.target).parents(".employeeSearch").length == 0){
                        $(".employeeSearchSuggestions").hide();
                    }
                });
            })
            //on keyup, start the countdown
            $input.on('keyup', function () {
                searchArticleSuggestions(parent, true);
            });
            //on keydown, clear the countdown
            $input.on('keydown', function () {
                searchArticleSuggestions(parent, true);
            });
            function searchArticleSuggestions (parent, addLoading){
                if(!loadingArticle) {
                    if(article_search_value != parent.find(".articleName").val() || article_search_value == "") {
                        loadingArticle = true;
                        article_search_value = parent.find(".articleName").val();
                        if(addLoading){
                            parent.find('.employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                        }
                        var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', addNewRow: 1, supplier_id: $(".supplierChoose").val(), ownercompany_id: $(".buildingOwner").val()};
                        $.ajax({
                            cache: false,
                            type: 'POST',
                            dataType: 'json',
                            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_articles_suggestions";?>',
                            data: _data,
                            success: function(obj){
                                loadingArticle = false;
                                if(parent.find(".articleName").val() != "" && article_search_value != parent.find(".articleName").val()){
                                    searchArticleSuggestions(parent, true);
                                } else {
                                    $('.employeeSearchSuggestions').html('').hide();
                                    parent.find('.employeeSearchSuggestions').html(obj.html).show();
                                }
                            }
                        }).fail(function(){
                            loadingArticle = false;
                        })
                    }
                }
            }

            rebindTable();
            calculateTotal();
            $(".popupform .selectEmployee").unbind("click").bind("click", function(){
                fw_loading_start();
                var _data = { fwajax: 1, fw_nocss: 1, leader: 1};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
                    data: _data,
                    success: function(obj){
                        fw_loading_end();
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(obj.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                    }
                });
            })
            <?php if (!$collectingorderId) { ?>
                $(".popupform .addEntryBtn").click();
            <?php } ?>
        });

        </script>
        <style>
        .connectToProjectLink {}
        .popupform .fieldWrapper {
            position: relative;
        }
        .projectConnectWrapper {
            position: absolute;
            right: 0px;
            top: 0px;
            min-width: 250px;
        }
        .monthcalendar .ui-datepicker-calendar {
            display: none;
        }
        .editCollectingOrderDate {
            color: #46b2e2;
            cursor: pointer;
            margin-left: 10px;
        }
        .popupform input.popupforminput.shortinput {
            width: auto;
        }
        .articleIDwrapper {
            margin-bottom: 5px;
            line-height: 17px;
        }
        .edit-articleid {
            color: #46b2e2;
            float: right;
            cursor: pointer;
            margin-top: 4px;
        }
        .edit-accountingInfo {
            color: #46b2e2;
            float: right;
            cursor: pointer;
            margin-top: 4px;
        }
        #popupeditbox.popupeditbox {
            max-width: 1024px;
            width: 90%;
        }
        .popupform .confirmationForm {
            display: none;
        }
        .output-delete-orderline {
            cursor: pointer;
            margin:0;
            float: right;
            margin-top: 2px;
        }
        .output-delete-neworderline {
            cursor: pointer;
            margin:0;
            float: right;
            margin-top: 2px;
        }
        .totalPriceBlock {
            font-weight: bold;
            float: right;
            margin-right: 15px;
        }
        .totalPriceBlock .grandTotalPrice {
            font-weight: normal;
            float: right;
            padding-left: 15px;
        }
        .articleTable .articleName {
            border: 1px solid #cecece;
        }
        .articleTableWrapper .accountinfoWrapper {
            background: #fff;
            padding: 5px;
            float: right;
            position: relative;
            width: 200%;
            margin-right: -100%;
            border: 1px solid #cecece;
            margin-top: 5px;
        }
        .articleTableWrapper .accountinfoWrapper span {
            display: inline-block;
        }
        .articleTableWrapper .accountinfoWrapper label {
            display: inline-block !important;
            width: 100px;
        }
        .articleTable .article_supplier_dropdown {
            float: left;
            margin-left: 20px;
            margin-top: 2px;
        }
        .articleTable .employeeSearch {
            float: right;
            width: 300%;
            position: relative;
            margin-bottom: 0;
            margin-right: -200%;
        }
        .articleTable .addNewOrderlineSearch {
            width: 300px;
            margin-right: 0px;
            float: left;
        }
        .articleTable .employeeSearch .employeeSearchSuggestions {
            display: none;
            background: #fff;
            position: absolute;
            width: 100%;
            max-height: 200px;
            overflow: auto;
            z-index: 2;
            border: 1px solid #dedede;
            border-top: 0;
        }
        .articleTable .employeeSearch .employeeSearchSuggestions table {
            margin-bottom: 0;
        }
        #p_container .p_contentBlock .articleTable .employeeSearch .employeeSearchSuggestions td {
            padding: 5px 10px;
        }
        .articleTable .employeeSearch .glyphicon-triangle-right {
            position: absolute;
            top: 7px;
            right: 4px;
            color: #048fcf;
        }
        .articleTable .employeeSearch .glyphicon-search {
            position: absolute;
            top: 7px;
            left: 6px;
            color: #048fcf;
        }
        .articleTable .articleName {
            width: 100%;
            border: 1px solid #dedede;
            padding: 3px 15px 3px 25px;
        }
        .articleTable .employeeSearchInputBefore {
            width: 150px;
            border: 1px solid #dedede;
            padding: 3px 10px 3px 10px;
        }
        .articleTable .employeeSearchBtn {
            background: #0093e7;
            border-radius: 5px;
            margin-left: 3px;
            color: #fff;
            padding: 5px 15px;
            cursor: pointer;
            border: 0;
        }
        .articleRow .articleNameInput {
            width: 100%;
            padding: 3px 5px;
            border: 1px solid #dedede;
        }
        .articleRow .quantityInput {
            width: 100%;
            padding: 3px 5px;
            border: 1px solid #dedede;
        }
        .articleRow .pricePerPieceInput {
            width: 100%;
            padding: 3px 5px;
            border: 1px solid #dedede;
        }
        .articleRow .discountInput {
            width: 80%;
            padding: 3px 5px;
            border: 1px solid #dedede;
        }
        .output-add-article {
            cursor: pointer;
        }
        .lineInput .otherInput {
            margin-top: 10px;
        }
        .lineInput input[type="radio"]{
            margin-right: 10px;
            vertical-align: middle;
        }
        .lineInput input[type="radio"] + label {
            margin-right: 10px;
            vertical-align: middle;
        }
        .popupform .inlineInput input.popupforminput {
            display: inline-block;
            width: auto;
            vertical-align: middle;
            margin-right: 20px;
        }
        .popupform .inlineInput label {
            display: inline-block !important;
            vertical-align: middle;
        }
        .popupform .lineInput.lineWhole {
            font-size: 14px;
        }
        .popupform .lineInput.lineWhole label {
            font-weight: normal !important;
        }
        .selectDivModified {
            display:block;
        }
        .popupform, .popupeditform {
            width:100%;
            margin:0 auto;
            border:1px solid #e8e8e8;
            position:relative;
        }
        .invoiceEmail {
            display: none;
        }
        label.error {
            color: #c11;
            margin-left: 10px;
            border: 0;
            display: inline !important;
        }
        .errorText {
            color: #c11;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .popupform .popupforminput.error { border-color:#c11 !important;}
        #popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
        /* css for timepicker */
        .ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
        .ui-timepicker-div dl { text-align: left; }
        .ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
        .ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
        .ui-timepicker-div td { font-size: 90%; }
        .ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
        .clear {
            clear:both;
        }
        .inner {
            padding:10px;
        }
        .pplineV {
            position:absolute;
            top:0;bottom:0;left:70%;
            border-left:1px solid #e8e8e8;
        }
        .popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
            width:100%;
            border-radius: 4px;
            padding:5px 10px;
            font-size:12px;
            line-height:17px;
            color:#3c3c3f;
            background-color:transparent;
            -webkit-box-sizing: border-box;
               -moz-box-sizing: border-box;
                 -o-box-sizing: border-box;
                    box-sizing: border-box;
            font-weight:400;
            border: 1px solid #cccccc;
        }
        .popupformname {
            font-size:12px;
            font-weight:bold;
            padding:5px 0px;
        }
        .popupforminput.botspace {
            margin-bottom:10px;
        }
        textarea {
            min-height:50px;
            max-width:100%;
            min-width:100%;
            width:100%;
        }
        .popupformname {
            font-weight: 700;
            font-size: 13px;
        }
        .popupformbtn {
            text-align:right;
            margin:10px;
        }
        .popupformbtn input {
            border-radius:4px;
            border:1px solid #0393ff;
            background-color:#0393ff;
            font-size:13px;
            line-height:0px;
            padding: 20px 35px;
            font-weight:700;
            color:#FFF;
            margin-left:10px;
        }
        .error {
            border: 1px solid #c11;
        }
        .popupform .lineTitle {
            font-weight:700;
        }
        .popupform .line .lineTitle {
            width:30%;
            float:left;
            font-weight:700;
            padding:5px 0;
        }

        .popupform .line .lineTitleWithSeperator {
            width:100%;
            margin: 20px 0;
            padding:0 0 10px;
            border-bottom:1px solid #EEE;
        }

        .popupform .line .lineInput {
            width:70%;
            float:left;
            padding: 5px 0;
        }
        .close-accountinginfo {
            margin-left: 0;
            cursor: pointer;
        }
        .popupeditbox {
            margin-bottom: 100px;
        }
        </style>
    <?php } else {
        echo $formText_ProjectAlreadyApprovedForInvoicing_output;
    }
}
?>
