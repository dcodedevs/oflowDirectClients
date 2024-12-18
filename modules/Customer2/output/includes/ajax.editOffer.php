<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}
function proc_rem_style($str)
{
    $str = trim($str);
    $str = strip_tags($str,'<p><ol><ul><li><b><i><strong>');
    $str = str_replace('<p>',"",$str);
    $str = str_replace('</p>',"<br />",$str);
    $str = str_replace('&rdquo;',"\"",$str);
    return $str;
}

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

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

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();
$customerId = $_POST['customerId'] ? $o_main->db->escape_str($_POST['customerId']) : 0;
$offerId = $_POST['offerId'] ? $o_main->db->escape_str($_POST['offerId']) : 0;
if(isset($_POST['action'])){ $action = $_POST['action']; } else { $action = ''; }
$forceUpdate = $_POST['forceUpdate'] ? $_POST['forceUpdate'] : false;

$s_sql = "SELECT * FROM offer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($offerId));
$project = ($o_query ? $o_query->row_array() : array());
if($customerId == 0){
    $customerId = $project['customerId'];
}
$sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($sql, array($customerId));
$customer = $o_query ? $o_query->row_array() : array();

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
$v_membersystem = array();

$o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
$v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist_membership as $v_user_cached_info) {
    $v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}
$o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
$v_cache_userlist = $o_query ? $o_query->result_array() : array();
foreach($v_cache_userlist as $v_user_cached_info) {
    $v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
}

$s_sql = "SELECT * FROM contactperson WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($project['seller_people_id']));
$seller = ($o_query ? $o_query->row_array() : array());
$loggedInPerson = array();
if($seller['email'] == ""){
    $seller['email'] = $variables->loggID;
}
foreach($v_membersystem as $member){
    if(mb_strtolower(trim($seller['email'])) == mb_strtolower($member['username'])) {
        $loggedInPerson = $member;
    }
}

$emailStringRow = "";
if($loggedInPerson['username'] != ""){
    $emailStringRow = $formText_Email_header.": <a href='mailto:".$loggedInPerson['username']."'>".$loggedInPerson['username'].'</a><br/>';
}
$phoneStringRow = "";
if($loggedInPerson['mobile'] != ""){
    $phoneStringRow = $formText_Mobile_header.": ".$loggedInPerson['mobile'].'<br/>';
}

if(isset($_POST['action']) && $_POST['action'] == "updateApprovedForBatchInvoicing"){
    $sql = "UPDATE offer SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    approvedForBatchinvoicing = '".$_POST['checked']."'
    WHERE id = ?";
    $o_query = $o_main->db->query($sql, $offerId);
} else if(isset($_POST['action']) && $_POST['action'] == "updateSeperatedInvoice"){
    $checked = 0;
    if($_POST['checked']){
        $checked = $offerId;
    }
    $sql = "UPDATE offer SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    seperatedInvoice = '".$checked."'
    WHERE id = ?";
    $o_query = $o_main->db->query($sql, $offerId);
} else {
    if(!$project['approvedForInvoicing']){
         if($moduleAccesslevel > 10) {
            if(isset($_POST['output_form_submit'])) {

                $s_sql = "SELECT * FROM offer WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($offerId));
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
                // $_POST['frontpage_text'] = str_replace(array("\n","\r\n","\r"), '', $_POST['frontpage_text']);
                // $_POST['frontpage_headline'] = str_replace(array("\n","\r\n","\r"), '', $_POST['frontpage_headline']);
                // $_POST['offer_headline'] = str_replace(array("\n","\r\n","\r"), '', $_POST['offer_headline']);
                // $_POST['offer_intro_text'] = str_replace(array("\n","\r\n","\r"), '', $_POST['offer_intro_text']);
                // $_POST['offer_end_text'] = str_replace(array("\n","\r\n","\r"), '', $_POST['offer_end_text']);

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
                    if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] == 3) {
                        $s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
                        $o_query = $o_main->db->query($s_sql, array($_POST['departmentCode']));
                        $departmentItem = $o_query ? $o_query->row_array() : array();
                        if(!$departmentItem){
                            $departmentError = true;
                        }
                    }

                    if(!$departmentError) {
                        $sql = "UPDATE offer SET
                        updated = now(),
                        updatedBy='".$variables->loggID."',
                        date = '".date("Y-m-d", strtotime($_POST['date']))."',
                        customerId = '".$_POST['customerId']."',
                        accountingProjectCode = '".$_POST['projectCode']."',
                        department_for_accounting_code = '".$_POST['departmentCode']."',
                        frontpage_text = '".$_POST['frontpage_text']."',
                        frontpage_headline = '".$_POST['frontpage_headline']."',
                        offer_headline = '".$_POST['offer_headline']."',
                        offer_intro_text = '".$_POST['offer_intro_text']."',
                        offer_end_text = '".$_POST['offer_end_text']."',
                        contactPerson = '".$_POST['contact_person']."',
                        seller_people_id = '".$_POST['seller_people_id']."',
                        ownercompanyId = ?,
                        include_tax = '".$_POST['include_tax']."'
                        WHERE id = ?";
                        $o_query = $o_main->db->query($sql, array($ownercompany_id, $offerId));

                        $newOfferId = $offerId;
                        foreach($_POST['articleId'] as $key=>$articleId){
                            $s_sql = "SELECT * FROM article WHERE article.id = ?";
                            $o_query = $o_main->db->query($s_sql, array($articleId));
                            $article = ($o_query ? $o_query->row_array() : array());
                            if($article){
                                $orderItem = array();
                                if(isset($_POST['ordersId'][$key])){
                                    $s_sql = "SELECT * FROM offerline WHERE offerline.id = ?";
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

                                if($v_customer_accountconfig['activeAccountingProjectOnOffer'] > 1) {
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

                                        $s_sql = "UPDATE offerline SET
                                        updated = now(),
                                        updatedBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        offer_id = ?
                                        WHERE id = ?";
                                        $o_main->db->query($s_sql, array($variables->loggID, $article['id'], $articleName, str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'], $bookaccountNr, $vatCode, $vatPercent, $offerId, $orderItem['id']));
                                    } else {
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

                                        $s_sql = "INSERT INTO offerline SET
                                        moduleID = ?,
                                        created = now(),
                                        createdBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        offer_id = ?";
                                        $o_main->db->query($s_sql, array(0, $variables->loggID, $article['id'], $articleName, str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'], $bookaccountNr, $vatCode, $vatPercent, $offerId));

                                    }
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
                        $fw_error_msg[-1] = $formText_InvalidDepartmentFAccNumber_output;
                    }
                    if($fw_error_msg == "" || empty($fw_error_msg)){
                        $fw_return_data = $o_main->db->insert_id();
                        $fw_redirect_url = $_POST['redirect_url'];
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
                    if(!$departmentError) {
                        $connectedProjectId = $_POST['projectId'];
                        $connectedProject2Id = $_POST['project2Id'];
                        $connectedProject2PeriodId = $_POST['project2PeriodId'];

                        $sql = "INSERT INTO offer SET
                        created = now(),
                        createdBy='".$variables->loggID."',
                        date = '".date("Y-m-d", strtotime($_POST['date']))."',
                        customerId = '".$_POST['customerId']."',
                        accountingProjectCode = '".$_POST['projectCode']."',
                        department_for_accounting_code = '".$_POST['departmentCode']."',
                        frontpage_text = '".$_POST['frontpage_text']."',
                        frontpage_headline = '".$_POST['frontpage_headline']."',
                        offer_headline = '".$_POST['offer_headline']."',
                        offer_intro_text = '".$_POST['offer_intro_text']."',
                        offer_end_text = '".$_POST['offer_end_text']."',
                        contactPerson = '".$_POST['contact_person']."',
                        seller_people_id = '".$_POST['seller_people_id']."',
                        ownercompanyId = ?,
                        include_tax = '".$_POST['include_tax']."'";

                        $o_query = $o_main->db->query($sql, array($ownercompany_id));
                        $insert_id = $o_main->db->insert_id();
                        $newOfferId = $insert_id;

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

                                if($v_customer_accountconfig['activeAccountingProjectOnOffer'] > 1) {
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

                                    $s_sql = "INSERT INTO offerline SET
                                        moduleID = ?,
                                        created = now(),
                                        createdBy= ?,
                                        articleNumber= ?,
                                        articleName= ?,
                                        amount= ?,
                                        pricePerPiece= ?,
                                        discountPercent= ?,
                                        priceTotal= ?,
                                        projectCode= ?,
                                        bookaccountNr = ?,
                                        vatCode = ?,
                                        vatPercent = ?,
                                        offer_id = ?";
                                        $o_main->db->query($s_sql, array(0, $variables->loggID, $article['id'], $articleName, str_replace(",", ".", $amount), str_replace(",", ".", $pricePerPiece), str_replace(",", ".", $discountPercent), str_replace(",", ".", $priceTotal), $_POST['projectCode'],$bookaccountNr, $vatCode, $vatPercent, $insert_id));
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
                    } else {
                        return;
                    }
                }

                if($_POST['create_pdf'] || $_POST['create_pdf_send_link']) {
                    $createPdf = true;

                    if($_POST['create_pdf_send_link']) {
                        $fw_redirect_url_back = $fw_redirect_url;
                        $createPdf = false;
                        $fw_redirect_url = "";
                        if($_POST['contact_person']) {
                            $fw_redirect_url = $fw_redirect_url_back;
                            $createPdf = true;
                        } else {
                            $fw_error_msg[-1] = $formText_MissingContactPerson_output;
                        }
                    }
                    if($createPdf){
                        //create pdf
                        if(!class_exists("TCPDF"))
                    	{
                    		require_once(__DIR__."/tcpdf/config/lang/eng.php");
                    		require_once(__DIR__."/tcpdf/tcpdf.php");
                    	}

                        $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
        				$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
    					$v_settings = $o_query ? $o_query->row_array() : array();
                        if($v_settings){
        					$invoicelogo = json_decode($v_settings['invoicelogo'],true);
                            class MYPDF extends TCPDF {
                                protected $custom_filename;
                                protected $pageLabelCustom;
                                protected $invoicelogo;
                                protected $additional_logos;
                                protected $v_settings;

                                public function setFileNameCustom($custom_filename) {
                                    $this->custom_filename = $custom_filename;
                                }
                                public function setPageLabelCustom($pageLabelCustom) {
                                    $this->pageLabelCustom = $pageLabelCustom;
                                }
                                public function setCustomInfo($invoicelogo, $v_settings) {
                                    $this->invoicelogo = $invoicelogo;
                                    $this->v_settings = $v_settings;
                                }
                                public function Header() {
                                    $invoicelogo = $this->invoicelogo;
                                    $v_settings = $this->v_settings;
                                    $additional_logos = $v_settings['additional_logos'];
                                    if(count($additional_logos) > 0){
                                        foreach($additional_logos as $additional_logo){
                                            if(intval($additional_logo['logo_width']) > 0){
                                                $additional_logo_image = json_decode($additional_logo['logo'], true);
                                                $logoWidth = intval($additional_logo['logo_width']);
                                                $yPos = $additional_logo['logo_pos_y'];
                                                $xPos = $additional_logo['logo_pos_x']+105-$logoWidth/2;
                                                list($width, $height, $type, $attr) = getimagesize(__DIR__."/../../../../".$additional_logo_image[0][1][0]);
                                                $ratio = $width/$height;
                                                $logoHeight = $logoWidth/$ratio;
                                                $this->Image(__DIR__."/../../../../".$additional_logo_image[0][1][0], $xPos, $yPos, $logoWidth, $logoHeight, '', '', '', true, 300);

                                            }
                                        }
                                    }
                                	if ($invoicelogo[0][1][0]) {
                                		$divider = 3;
                                		$logoWidth = (is_numeric($v_settings['invoicelogoWidth']) ? $v_settings['invoicelogoWidth'] : 100) / $divider;
                                		$logoPosX = (is_numeric($v_settings['invoicelogoPositionX']) ? $v_settings['invoicelogoPositionX'] : 0) / $divider + 17;
                                		$logoPosY = (is_numeric($v_settings['invoicelogoPositionY']) ? $v_settings['invoicelogoPositionY'] : 0) / $divider + 6;
                                        $logoPosX = 200/2 - $logoWidth/2;
                                		$this->Image(__DIR__."/../../../../".$invoicelogo[0][1][0], $logoPosX, $logoPosY, $logoWidth, 0, '', '', '', true, 300);
                                    	$this->SetMargins(20, 35, 20);
                                	}
                                }
                                // Page footer
                                public function Footer() {
                                    // Position at 15 mm from bottom
                                    $this->SetY(-10);
                                    // Set font
                                    $this->SetFont('verdana', '', 8);
                                    // Page number
                                    $this->Cell(180, 10, $this->pageLabelCustom.' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages()." ".$this->custom_filename, 0, false, 'R', 0, '', 0, false, 'T', 'M');
                                }
                            }
                        	// create new PDF document
                        	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'iso-8859-1', false);

                            $s_sql = "SELECT * FROM ownercompany_logos WHERE ownercompanyId = ? ORDER BY sortnr";
            				$o_query = $o_main->db->query($s_sql, array($ownercompany_id));
        					$additionalLogos = $o_query ? $o_query->result_array() : array();
                            $v_settings['additional_logos'] = $additionalLogos;
                            $pdf->setCustomInfo($invoicelogo, $v_settings);

                        	$pdf->SetCreator(PDF_CREATOR);
                        	$pdf->SetAuthor('ERP');
                        	$pdf->SetTitle('Offer: '.$newOfferId);
                        	$pdf->SetSubject('');
                        	$pdf->SetKeywords('');
                        	$pdf->setPrintHeader(true);
                        	$pdf->setPrintFooter(true);
                            $pdf->SetFooterMargin(20);
                        	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
                        	$pdf->SetMargins(20, 15, 20);
                        	$pdf->SetAutoPageBreak(TRUE, 20);
                        	$pdf->setLanguageArray($l);
                        	$pdf->SetFont('verdana', '', 9);

                        	$pdf->AddPage();
                        	//$pdf->Image($extraimagedir.''.$v_invoice_log[0][1][0] , 20, 5, 40, 15, '', '', '', true, 300);
                        	//

                            $s_sql = "SELECT * FROM offer WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($newOfferId));
                            $offer = ($o_query ? $o_query->row_array() : array());

                            $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($offer['seller_people_id']));
                            $seller = ($o_query ? $o_query->row_array() : array());

                            $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($offer['contactPerson']));
                            $contactPerson = ($o_query ? $o_query->row_array() : array());

                            $s_sql = "SELECT * FROM offerline WHERE offerline.offer_id = ? AND offerline.content_status = 0  ORDER BY offerline.id ASC";
                            $o_query = $o_main->db->query($s_sql, array($newOfferId));
                            $orders = ($o_query ? $o_query->result_array() : array());

                            $s_sql = "SELECT * FROM customer WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($offer['customerId']));
                            $v_customer = ($o_query ? $o_query->row_array() : array());

                            if($v_customer['useOwnInvoiceAdress']) {
                        		$s_cust_addr_prefix = 'ia';
                        		$customerAddress = 'own address';
                        		$customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
                        	} else {
                        		$s_cust_addr_prefix = 'pa';
                        		$customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
                        	}
                        	$s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress." <br /><br />";

                            if($contactPerson) {
                                $s_customer .= $formText_YourContactPerson_Output.': '.$contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']." <br />";
                            }

                            $s_invoice_text = $v_settings['companyname']." <br />".$v_settings['companypostalbox']." <br />".$v_settings['companyzipcode']." ".$v_settings['companypostalplace'];
                            if($v_settings['companyCountry'] != ""){
                                $s_invoice_text .= ", ".$v_settings['companyCountry'];
                            }
                            $s_invoice_text .=" <br />".$formText_Phone.": ".$v_settings['companyphone'];
                            $s_invoice_text .= " <br /><br />".$formText_date.': '.date("d.m.Y", strtotime($_POST['date']));
                            /*.
                        	" <br />".$formText_iban.": ".$bankAccountData['companyiban']." <br />".$formText_swiftCode.": ".$bankAccountData['companyswift']."<br />".$formText_account.": ".$bankAccountData['companyaccount'];*/
                            $hasAnyDiscount = false;
                            foreach($orders as $order){
                                if($order['discountPercent'] > 0) $hasAnyDiscount = true;
                            }

                            $v_membersystem = array();

                            $o_query = $o_main->db->query("SELECT * FROM cache_userlist_membershipaccess");
                            $v_cache_userlist_membership = $o_query ? $o_query->result_array() : array();
                            foreach($v_cache_userlist_membership as $v_user_cached_info) {
                            	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
                            }
                            $o_query = $o_main->db->query("SELECT * FROM cache_userlist_access");
                            $v_cache_userlist = $o_query ? $o_query->result_array() : array();
                            foreach($v_cache_userlist as $v_user_cached_info) {
                            	$v_membersystem[$v_user_cached_info['username']] = $v_user_cached_info;
                            }
                            $loggedInPerson = array();
                            if($seller['email'] == ""){
                                $seller['email'] = $variables->loggID;
                            }
                            foreach($v_membersystem as $member){
                                if(mb_strtolower(trim($seller['email'])) == mb_strtolower($member['username'])) {
                                    $loggedInPerson = $member;
                                }
                            }


                            $html1 = '<table style="margin: 0" border="0" cellpadding="0" cellspacing="0" width="100%">';
                            $html1_1 ='
                        		<tr>
                        			<td width="340">'.($s_customer).'</td>
                        			<td width="150"><span style="font-size:44px;"><b>'.($formText_Offer_header).'</b></span><br />'.($s_invoice_text).'</td>
                        		</tr>
                        		<tr><td colspan="2"></td></tr>';

                            $html2_before='
                                <tr>
                        			<td colspan="2">';

                                    if(!$v_customer_accountconfig['activateOfferFrontpage']){
                                        	$html2_before.='<div style="margin-bottom: 10px; font-size: 40px;"><b>'.($offer['offer_headline']).'</b></div>';
                                    }
                        	$html2_before.='<div style="margin-bottom: 10px;">'.$offer['offer_intro_text'].'</div>
                                    </td>
                                </tr>';
                            $html_orderlines = '<tr>
                                <td colspan="2"><table style="border-top:1px solid #cecece; border-bottom:1px solid #cecece;" cellspacing="0" cellpadding="4" border="0" width="100%">
                        				<tr><td width="'.($hasAnyDiscount ? '215' : '245').'" style="font-weight:bold;"></td><td width="10"></td><td width="65" style="font-weight:bold;">'.$formText_amount.'</td><td width="95" style="font-weight:bold;">'.$formText_price.'</td>'.($hasAnyDiscount ? '<td width="55" style="font-weight:bold;">'.$formText_discount.'</td>' : '').'<td width="70" style="font-weight:bold; text-align:right;">'.$formText_totalprice.'</td></tr>';

                        				$totalSum = 0;
                        				foreach($orders as $order){
                                            $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                            $o_query = $o_main->db->query($s_sql, array($order['vatCode']));
                                            $vatItem = $o_query ? $o_query->row_array() : array();
                                            $vatPercent = $vatItem['percentRate'];
                                            $vat = $order['priceTotal']*$vatPercent/100;
                                            $totalSum+=$order['priceTotal'];
                                            $totalTax += $vat;
                    						$html_orderlines.= '
                    						<tr><td>'.proc_rem_style($order['articleName']).'</td><td></td><td>'.proc_rem_style(number_format(floatval($order['amount']),2,',',' ')).'</td><td>'.proc_rem_style(number_format(floatval($order['pricePerPiece']),2,',',' ')).'</td>'.($hasAnyDiscount ? '<td>'.proc_rem_style(round($order['discountPercent'])).'%</td>' : '').'<td style="text-align:right;">'.proc_rem_style(number_format(floatval($order['priceTotal']),2,',',' ')).'</td></tr>';
                    					}
                                $emailStringRow = "";
                                if($loggedInPerson['username'] != ""){
                                    $emailStringRow = $formText_Email_header.": <a href='mailto:".$loggedInPerson['username']."'>".$loggedInPerson['username'].'</a><br/>';
                                }
                                $phoneStringRow = "";
                                if($loggedInPerson['mobile'] != ""){
                                    $phoneStringRow = $formText_Mobile_header.": ".$loggedInPerson['mobile'].'<br/>';
                                }

    	                       $html_orderlines.= '
                                           <tr>
                                               <td width="'.($hasAnyDiscount ? '215' : '245').'"></td>
                                               <td width="10"></td>
                                               <td  width="65" style="border-top:1px solid #cecece;"><b>'.$formText_total.'</b></td>
                                               <td  width="95" style="border-top:1px solid #cecece;"></td>
                                               '.($hasAnyDiscount ? '<td width="55" style="border-top:1px solid #cecece;"></td>' : '').'
                                               <td width="70" style="border-top:1px solid #cecece; text-align:right;">'.number_format(floatval($totalSum),2,',',' ').'</td>
                                           </tr>';
                                if($offer['include_tax']){
                                    $html_orderlines.= '
                                                <tr>
                                                    <td width="'.($hasAnyDiscount ? '215' : '245').'"></td>
                                                    <td width="10"></td>
                                                    <td  width="65"><b>'.$formText_totalTax.'</b></td>
                                                    <td  width="95"></td>
                                                    '.($hasAnyDiscount ? '<td width="55"></td>' : '').'
                                                    <td width="70" style="text-align:right;">'.number_format(floatval($totalTax),2,',',' ').'</td>
                                                </tr>
                                                <tr>
                                                    <td width="'.($hasAnyDiscount ? '215' : '245').'"></td>
                                                    <td width="10"></td>
                                                    <td  width="65"><b>'.$formText_totalWithTax.'</b></td>
                                                    <td  width="95"></td>
                                                    '.($hasAnyDiscount ? '<td width="55"></td>' : '').'
                                                    <td width="70" style="text-align:right;">'.number_format(floatval($totalSum+$totalTax),2,',',' ').'</td>
                                                </tr>';
                                }
                                $html_orderlines.= '</table></td>
                                   </tr>';
                            $html2_after ='
                                <tr>
                                    <td colspan="2">
                        				<div style="margin-bottom: 20px;">'.$offer['offer_end_text'].'</div>
                                    </td>
                                </tr>';

                                    $html3.='
                                    <tr>
                                        <td colspan="2">
                                            <div>'.$formText_BestRegards_header.'<br/>
                                                '.$v_settings['companyname'].'<br/><br/>
                                                <b>'.$loggedInPerson['first_name']." ".$loggedInPerson['middle_name']." ".$loggedInPerson['last_name'].'</b><br/>
                                                '.$emailStringRow.'
                                                '.$phoneStringRow.'</div>
                            			</td>
            		                </tr>';
                                    $html3_1.='
                        		</table>';
                            if($v_customer_accountconfig['activateOfferFrontpage']){
                                $html4 = '
                                <tr>
                                    <td colspan="2">
                                        <div style="margin-bottom: 10px; font-size: 40px;"><b>'.($offer['offer_headline']).'</b></div>
                                        <div style="margin-bottom: 10px;">'.$offer['frontpage_text'].'</div>
                                    </td>
                                </tr>
                                ';
                            }

                            // tcpd not outputting if there is space at the end of the span tag
                            $checkTags = array("</strong>", "</em>", "</ul>", "</li>", "</ol>");

                            foreach($checkTags as $checkTag) {
                                $html2_before = str_replace(" ".$checkTag, $checkTag, $html2_before);
                                $html2_after = str_replace(" ".$checkTag, $checkTag, $html2_after);
                                $html_orderlines = str_replace(" ".$checkTag, $checkTag, $html_orderlines);
                                $html4 = str_replace(" ".$checkTag, $checkTag, $html4);
                            }
                            $html2_before = str_replace(": ", ":", $html2_before);
                            $html2_after = str_replace(": ", ":", $html2_after);
                            $html_orderlines = str_replace(": ", ":", $html_orderlines);
                            $html4 = str_replace(": ", ":", $html4);

                            $html2_before = str_replace("&nbsp;", " ", $html2_before);
                            $html2_after = str_replace("&nbsp;", " ", $html2_after);
                            $html_orderlines = str_replace("&nbsp;", " ", $html_orderlines);
                            $html4 = str_replace("&nbsp;", " ", $html4);

                            $html2_before = html_entity_decode(preg_replace('/\t+/', '', $html2_before));
                            $html2_after = html_entity_decode(preg_replace('/\t+/', '', $html2_after));
                            $html_orderlines = html_entity_decode(preg_replace('/\t+/', '', $html_orderlines));
                            $html4 = html_entity_decode(preg_replace('/\t+/', '', $html4));

                            $htmlFrontpage_prefix = $html1.$html1_1;
                            $htmlFrontpage = $html4.$html3.$html3_1;

                            $html_before = $html2_before;
                            $html_after = $html2_after.$html3.$html3_1;
                            $html_prefix = $html1.$html1_1;

                            if($v_customer_accountconfig['activateOfferFrontpage']) {
                                $html_prefix = $html1;
                                $html_before = $html2_before;
                                $html_after = $html2_after.$html3_1;
                            }


                            $s_sql = "INSERT INTO offer_pdf SET
                            moduleID = ?,
                            created = now(),
                            createdBy= ?,
                            offer_id = ?";
                            $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $newOfferId));
                            $file_insert_id = $o_main->db->insert_id();

                            $file = $formText_OfferNr_output." ".$newOfferId."-".$file_insert_id." ".$formText_From_output." ".$v_settings['name'];

        					$file .= ".pdf";
                            $pdf->setFileNameCustom($file);
                            $pdf->setPageLabelCustom($formText_Page_output);
        					$filepath = __DIR__."/../../../../uploads/protected/offers/";
        					if(!file_exists($filepath))
        					{
        						mkdir($filepath, 0777,true);
        					}
        					chmod($filepath, 0777);

                            if($v_customer_accountconfig['activateOfferFrontpage']) {
                                $pdf->SetFont('verdana', '', 9);
                                $pdf->writeHTML($htmlFrontpage_prefix, true, false, true, false, '');
                            	$pdf->SetFont('verdana', '', 10);
                                $pdf->writeHTML($htmlFrontpage, true, false, true, false, '');
                                $pdf->AddPage();
                            }

                            $pdf->SetFont('verdana', '', 9);
                            $pdf->writeHTML($html_prefix, true, false, true, false, '');

                            $pdf->SetFont('verdana', '', 10);
                        	$pdf->writeHTML($html_before, true, false, true, false, '');

                            $cp =  $pdf->getPage();
                            $pdf->startTransaction();

                            $pdf->SetFont('verdana', '', 10);
                        	$pdf->writeHTML($html_orderlines, true, false, true, false, '');
                            if ($pdf->getPage() > $cp) {
                                $pdf->rollbackTransaction(true);//true is very important
                                $pdf->AddPage();
                                $pdf->writeHTML($html_orderlines, true, false, true, false, '');
                            } else {
                                $pdf->commitTransaction();
                            }
                            $pdf->SetFont('verdana', '', 10);
                        	$pdf->writeHTML($html_after, true, false, true, false, '');

                        	$pdf->lastPage();

                        	$pdf->Output($filepath.$file, 'F');//'FD');

                            if(file_exists($filepath.$file)){
                                $s_sql = "UPDATE offer_pdf SET
                                file = ?
                                WHERE id = ?";
                                $o_main->db->query($s_sql, array("uploads/protected/offers/".$file, $file_insert_id));
                            }
                            return;
                        }

                        // if($_POST['create_pdf_send']) {
                        //     $fw_redirect_url_back = $fw_redirect_url;
                        //     $createPdf = false;
                        //     $fw_redirect_url = "";
                        //     if($_POST['contact_person']) {
                        //         $invoiceEmail = "";
                        //
                        //         $v_email_server_config_sql = $o_main->db->query("select * from sys_emailserverconfig order by default_server desc");
                        //         if($v_email_server_config_sql && $v_email_server_config_sql->num_rows()>0)
                        //         $v_email_server_config = $v_email_server_config_sql->result();
                        //
                        //         $s_sql = "select * from contactperson where id = ?";
                        //         $o_query = $o_main->db->query($s_sql, array($_POST['contact_person']));
                        //         $contactPerson = $o_query ? $o_query->row_array() : array();
                        //         if($contactPerson) {
                        //             $invoiceEmail = $contactPerson['email'];
                        //         }
                        //         if(filter_var($invoiceEmail, FILTER_VALIDATE_EMAIL)) {
                        //             $s_email_subject = $formText_Offer_output;
                        //             $s_email_body = " ";
                        //             $mail = new PHPMailer;
                        //             $mail->CharSet  = 'UTF-8';
                        //             $mail->IsSMTP(true);
                        //             $mail->isHTML(true);
                        //             $invoiceFile = __DIR__."/../../../../uploads/protected/offers/".$file;
                        //             if($v_email_server_config[0]->host != "")
                        //             {
                        //                 $mail->Host = $v_email_server_config[0]->host;
                        //                 if($v_email_server_config[0]->port != "") $mail->Port = $v_email_server_config[0]->port;
                        //
                        //                 if($v_email_server_config[0]->username != "" and $v_email_server_config[0]->password != "")
                        //                 {
                        //                     $mail->SMTPAuth = true;
                        //                     $mail->Username = $v_email_server_config[0]->username;
                        //                     $mail->Password = $v_email_server_config[0]->password;
                        //
                        //                 }
                        //             } else {
                        //                 $mail->Host = "mail.dcode.no";
                        //             }
                        //             $mail->From     = $v_settings[0]->invoiceFromEmail;
                        //             $mail->FromName = "";
                        //             $mail->Subject  = $s_email_subject;
                        //             $mail->Body     = $s_email_body;
                        //             $mail->AddAddress($invoiceEmail);
                        //             $mail->AddAttachment($invoiceFile);
                        //
                        //             $s_sql = "INSERT INTO sys_emailsend (id, created, createdBy, `type`, send_on, sender, sender_email, subscriberlist_id, unsubscriberlist_id, content_id, content_table, content_module_id, sending_limit, subject, text) VALUES (NULL, NOW(), '".$_COOKIE['username']."', 2, NOW(), '', '".addslashes($v_settings['invoiceFromEmail'])."', 0, 0, '".$newInvoiceNrInDb."', 'invoice', '', 0, '".addslashes($s_email_subject)."', '".addslashes($s_email_body)."');";
                        //             $o_main->db->query($s_sql);
                        //             $l_emailsend_id = $o_main->db->insert_id();
                        //
                        //             $s_sql = "INSERT INTO sys_emailsendto (id, emailsend_id, receiver, receiver_email, `status`, status_message, perform_time, perform_count) VALUES (NULL, '".$l_emailsend_id."', '', '".addslashes($invoiceEmail)."', 1, '', NOW(), 1);";
                        //             $o_main->db->query($s_sql);
                        //             $l_emailsendto_id = $o_main->db->insert_id();
                        //
                        //             if($mail->Send())
                        //             {
                        //                 $fw_redirect_url = $fw_redirect_url_back;
                        //
                        //             } else {
                        //                 $fw_error_msg[-1] = $formText_ErrorSendingEmail_output;
                        //
                        //                 $s_sql = "UPDATE sys_emailsendto SET status = 2, status_message = '".json_encode($mail)."' WHERE id = ?";
                        //                 $o_main->db->query($s_sql, array($l_emailsendto_id));
                        //
                        //                 $mail = new PHPMailer;
                        //                 $mail->CharSet  = 'UTF-8';
                        //                 $mail->IsSMTP(true);
                        //                 $mail->isHTML(true);
                        //                 if($v_email_server_config[0]->host != "")
                        //                 {
                        //                     $mail->Host = $v_email_server_config[0]->host;
                        //                     if($v_email_server_config[0]->port != "") $mail->Port = $v_email_server_config[0]->port;
                        //
                        //                     if($v_email_server_config[0]->username != "" and $v_email_server_config[0]->password != "")
                        //                     {
                        //                         $mail->SMTPAuth = true;
                        //                         $mail->Username = $v_email_server_config[0]->username;
                        //                         $mail->Password = $v_email_server_config[0]->password;
                        //
                        //                     }
                        //                 } else {
                        //                     $mail->Host = "mail.dcode.no";
                        //                 }
                        //                 $mail->From     = "noreply@getynet.com";
                        //                 $mail->FromName = "Getynet.com";
                        //                 $mail->Subject  = $formText_NotDelivered_Output.": ".$s_email_subject;
                        //                 $mail->Body     = $s_email_body;
                        //                 $mail->AddAddress(trim($v_email_server_config[0]->technical_email));
                        //                 $mail->AddAttachment($invoiceFile);
                        //                 foreach($files_attached as $file_to_attach) {
                		// 					$mail->AddAttachment(__DIR__."/../../../../".$file_to_attach[1][0]);
                		// 				}
                        //
                        //             }
                        //         } else {
                        //             $fw_error_msg[-1] = $formText_InvalidEmail_output;
                        //         }
                        //     } else {
                        //         $fw_error_msg[-1] = $formText_MissingContactPerson_output;
                        //     }
                        // }

                    }
                }
            }
        }
        if($offerId) {
            $sql = "SELECT * FROM offer WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($offerId));
            $projectData = $o_query ? $o_query->row_array() : array();

            $sql = "SELECT * FROM customer WHERE id = ?";
            $o_query = $o_main->db->query($sql, array($projectData['customerId']));
            $customer = $o_query ? $o_query->row_array() : array();
        }

        $showDeleteConfirmation = false;
        if($action == 'deleteOrderByStatus' && $moduleAccesslevel > 110)
        {
            $sql = "UPDATE offer SET content_status = 2 WHERE offer.id = ?";
            $o_main->db->query($sql, array($offerId));
            $fw_return_data = "deletedOrder";
        }

        if($action == 'deleteOrder' && $moduleAccesslevel > 110)
        {
             $sql = "DELETE offer, offerline FROM offer LEFT JOIN offerline ON offerline.offer_id = offer.id WHERE offer.id = ?";
            $o_main->db->query($sql, array($offerId));
            $fw_return_data = "deletedOrder";
        }
        if($action == 'deleteOfferPdf' && $moduleAccesslevel > 110)
        {
            $sql = "DELETE offer_pdf FROM offer_pdf WHERE offer_pdf.id = ?";
            $o_main->db->query($sql, array($_POST['offerPdfId']));
            $fw_return_data = "deletedOfferPdf";
        }
        if($action == "deleteOrderline" && $moduleAccesslevel > 110){
            $sql = "DELETE offerline FROM offerline WHERE offerline.id = ?";
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


        $s_sql = "SELECT * FROM offer_template ORDER BY offer_headline";
        $o_query = $o_main->db->query($s_sql);
        $templates = $o_query ? $o_query->result_array() : array();

        $s_sql = "SELECT * FROM contactperson WHERE customerId = ? ORDER BY name";
        $o_query = $o_main->db->query($s_sql, array($customerId));
        $contactpersons = $o_query ? $o_query->result_array() : array();
        ?>
        <div class="popupform">
            <form accept-charset="UTF-8" class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editOffer";?>" method="post">
                <input type="hidden" name="fwajax" value="1">
                <input type="hidden" name="fw_nocss" value="1">
                <input type="hidden" name="output_form_submit" value="1">
                <input type="hidden" name="offerId" value="<?php echo $offerId;?>" id="offerId">
                <input type="hidden" name="customerId" id="customerId" value="<?php print $customerId;?>" required>
                <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
                <div class="defaultForm">
                    <div class="inner">
                        <div class="popupformTitle"><?php echo $formText_Offer_output;?></div>
                        <div class="fieldWrapper">

                            <div class="selectTemplateWrapper">
                                <select name="select_template" class="selectTemplate">
                                    <option value="" data-projectcode="0"><?php echo $formText_Select_output;?></option>

                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo $template['id']; ?>"><?php echo $template['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="edit_templates"><?php echo $formText_EditTemplates_output;?></div>
                            </div>
                            <div class="clear"></div>
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
                            <?php if($v_customer_accountconfig['activeAccountingProjectOnOffer']) { ?>
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
                            <div class="line ">
                                <div class="lineTitle"><?php echo $formText_ContactPerson_Output; ?></div>
                                <div class="lineInput">
                                    <select name="contact_person" class="contactPerson contactPersonSelect">
                                        <option value=""><?php echo $formText_Select_output;?></option>
                                        <?php foreach ($contactpersons as $contactperson): ?>
                                            <option value="<?php echo $contactperson['id']; ?>" <?php if($contactperson['id'] == $projectData['contactPerson']) echo 'selected';?> data-name="<?php echo $contactperson['name']." ".$contactperson['middlename']." ".$contactperson['lastname'];?>">
                                                <?php echo $contactperson['name']." ".$contactperson['middlename']." ".$contactperson['lastname']." - ".$contactperson['email']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="-1" class="createNewOption"><?php echo $formText_CreateNew_output;?></option>
                                    </select>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="line ">
                                <div class="lineTitle"><?php echo $formText_Seller_Output; ?></div>
                                <div class="lineInput">
                                    <?php
                                    $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($projectData['seller_people_id']));
                                    $seller = ($o_query ? $o_query->row_array() : array());
                                    if(!$seller) {
                                        $s_sql = "SELECT * FROM contactperson WHERE contactperson.email = ? AND type = ?";
                                        $o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_contactperson_type));
                                        $seller = ($o_query ? $o_query->row_array() : array());
                                    }

                                    if($seller) { ?>
                                    <a href="#" class="selectWorker"><?php echo $seller['name']." ".$seller['middlename']." ".$seller['lastname'];?></a>
                                    <?php } else { ?>
                                    <a href="#" class="selectWorker"><?php echo $formText_SelectSeller_Output;?></a>
                                    <?php } ?>
                                    <input type="hidden" name="seller_people_id" id="employeeId" value="<?php print $seller['id'];?>" required>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="pdf_input_text_wrapper">
                                <div class="row_wrapper offer_headline_wrapper">
                                    <?php if($v_customer_accountconfig['activateOfferFrontpage']) { ?>
                                        <div class="page_divider"><?php echo $formText_Page1_output;?></div>
                                    <?php } ?>
                                    <div class="line projectLeaderWrapper">
                                        <div class="lineTitle"><?php echo $formText_OfferHeadline_Output; ?></div>
                                        <div class="lineInput">
                            				<input type="text" class="popupforminput botspace offer_headline_input" name="offer_headline" value="<?php echo $projectData['offer_headline']; ?>" autocomplete="off">
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <?php if($v_customer_accountconfig['activateOfferFrontpage']) { ?>
                                    <div class="row_wrapper offer_frontpage_wrapper">
                                        <div class="line projectLeaderWrapper">
                                            <div class="lineTitle"><?php echo $formText_FrontpageText_Output; ?></div>
                                            <div class="lineInput">
                            					<textarea class="popupforminput botspace ckeditor frontpage_text_input" id="frontpage_text" name="frontpage_text"  autocomplete="off"><?php echo $projectData['frontpage_text']; ?></textarea>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row_wrapper offer_intro_text_wrapper">
                                    <?php if($v_customer_accountconfig['activateOfferFrontpage']) { ?>
                                        <div class="page_divider"><?php echo $formText_Page2_output;?></div>
                                    <?php } ?>
                                    <div class="line projectLeaderWrapper">
                                        <div class="lineTitle"><?php echo $formText_OfferIntroText_Output; ?></div>
                                        <div class="lineInput">
                        					<textarea class="popupforminput botspace ckeditor offer_intro_input" id="intro_text" name="offer_intro_text"  autocomplete="off"><?php echo $projectData['offer_intro_text']; ?></textarea>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="row_wrapper offer_lines_wrapper">
                                    <div class="line">
                                        <div class="lineTitle"><?php echo $formText_OfferLines_Output; ?></div>
                                        <div class="lineInput">
                                            <div class="articleTable">
                                                <table class="table table-bordered articleTableWrapper">
                                                    <tr>
                                                        <th width="10%"><?php if($article_accountconfig['activateArticleCode']) { echo $formText_ArticleCode_output; } else { echo $formText_ArticleNr_output; } ?></th>
                                                        <th width="30%"><?php echo $formText_ProductName_output;?></th>
                                                        <th width="10%"><?php echo $formText_Accounting_output;?></th>
                                                        <th width="10%"><?php echo $formText_Quantity_output;?></th>
                                                        <th width="10%"><?php echo $formText_PricePerPiece_output;?></th>
                                                        <th width="10%"><?php echo $formText_Discount_output;?> %</th>
                                                        <th width="10%"><?php echo $formText_PriceTotal_output;?></th>
                                                    </tr>
                                                    <?php




                                                    $s_sql = "SELECT * FROM offerline WHERE offerline.offer_id = ? AND offerline.content_status = 0  ORDER BY offerline.id ASC";
                                                    $o_query = $o_main->db->query($s_sql, array($projectData['id']));
                                                    $orders = ($o_query ? $o_query->result_array() : array());

                                                    foreach($orders as $order){
                                                        $vatPercent = 0;
                                                        $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                                        $o_query = $o_main->db->query($s_sql, array($order['vatCode']));
                                                        $vatItem = $o_query ? $o_query->row_array() : array();
                                                        if($vatItem){
                                                            $vatPercent = $vatItem['percentRate'];
                                                        }
                                                        $periodising = false;
                                                        $s_sql = "SELECT * FROM article WHERE article.id = ?";
                                                        $o_query = $o_main->db->query($s_sql, array($order['articleNumber']));
                                                        $article = ($o_query ? $o_query->row_array() : array());
								                        $o_query = $o_main->db->query("SELECT * FROM article_supplier WHERE id = '".$o_main->db->escape_str($article['article_supplier_id'])."'");
								                        $articleSupplier = $o_query ? $o_query->row_array() : array();
                                                        if($order['periodization'] > 0){
                                                            $periodising = true;
                                                        }
                                                    ?>
                                                    <tr class='articleRow' data-tax="<?php echo $vatPercent;?>">
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
                                                        <td width="10%" class="accountingInfoTable">
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
                                                                            <option data-percent="<?php echo $row['percentRate'];?>" value="<?php echo $row['vatCode']; ?>" <?php echo $row['vatCode'] == $order['vatCode'] ? 'selected="selected"' : ''; ?>>
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
                                                        <td width='10%' class='quantity' data-value="<?php echo number_format($order['amount'], 2, ".", ""); ?>">
                                                            <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                                                <input type='text' name='quantity[]' class='numberInput quantityInput' value='<?php echo number_format($order['amount'], 2, ",", ""); ?>' autocomplete="off"/>
                                                            <?php } else {
                                                                echo $order['amount'];
                                                            } ?>
                                                        </td>
                                                        <td width='10%' class='pricePerPiece' data-value="<?php echo number_format($order['pricePerPiece'], 2, ".", ""); ?>">
                                                            <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                                                <input type='text' name='pricePerPiece[]' class='numberInput pricePerPieceInput' value='<?php echo number_format($order['pricePerPiece'], 2, ",", ""); ?>' autocomplete="off"/>
                                                            <?php } else {
                                                                echo $order['pricePerPiece'];
                                                            } ?>
                                                        </td>
                                                        <td width='10%' class='discount' data-value="<?php echo number_format($order['discountPercent'], 2, ".", "");?>">
                                                            <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                                            <input type='text' name='discount[]' class='numberInput discountInput' value='<?php echo number_format($order['discountPercent'], 2, ",", ""); ?>' autocomplete="off"/>
                                                            <?php } else {
                                                                echo $order['discountPercent'];
                                                            } ?>
                                                        </td>
                                                        <td class="rightAligned" width='10%'>
                                                            <span class="priceTotal">
                                                                <?php echo number_format($order['priceTotal'], 2, ".", ""); ?>
                                                            </span>

                                                            <?php if(intval($order['invoiceNumber']) == 0) { ?>
                                                                <span class="output-delete-orderline output-btn small editBtnIcon" data-order-id="<?php echo $order['id'];?>" data-project-id="<?php echo $offerId;?>"><span class="glyphicon glyphicon-trash"></span></span>
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
                                                    <div class="grandTotalTax" style="display: none;"></div>
                                                    <div class="grandTotalPriceTax" style="display: none;"></div>
                                                    <div class="clear"></div>
                                                </div>
                                                <div class="taxBlock">
                                                    <span>
                                                        <?php echo $formText_IncludeTax_output;?>
                                                    </span>
                                                    <input type="checkbox" class="include_tax" name="include_tax" value="1" autocomplete="off" <?php if($projectData['include_tax']) echo 'checked';?>/>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>

                                <div class="row_wrapper offer_end_text_wrapper">
                                    <div class="line end_offer">
                                        <div class="lineTitle"><?php echo $formText_OfferEndText_Output; ?></div>
                                        <div class="lineInput">
                        					<textarea class="popupforminput botspace ckeditor offer_end_input" id="end_text" name="offer_end_text"  autocomplete="off"><?php echo $projectData['offer_end_text']; ?></textarea>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                                <div class="popupformbtn">
                                    <button type="button" class="output-btn b-large close_edit_wrapper close_edit_cancel"><?php echo $formText_Close_Output;?></button>
                                    <!-- <input type="button" class="close_edit_wrapper" value="<?php echo $formText_Save_Output; ?>"> -->
                                </div>
                            </div>
                            <?php
                            /*
                            if($customer_basisconfig['connectOrderToProject'] > 0) { ?>
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
                            <?php } */?>
                        </div>
                    </div>
                </div>
                <div class="preview">
                    <?php if($v_customer_accountconfig['activateOfferFrontpage']) { ?>
                        <div class="pageWrapper">
                            <?php
                            foreach($ownercompanies as $ownercompany){
            					$invoicelogo = json_decode($ownercompany['invoicelogo'],true);

                                $s_sql = "SELECT * FROM ownercompany_logos WHERE ownercompanyId = ?";
                                $o_query = $o_main->db->query($s_sql, array($ownercompany['id']));
                                $additional_logos = $o_query ? $o_query->result_array() : array();
                                ?>
                                <div class="ownercompany_top ownercompany_info_hidden ownercompany_logo_wrapper ownercompany<?php echo $ownercompany['id']?>">
                                    <?php
                                    if(count($invoicelogo) > 0){
                                        ?>
                                            <div class="imagewrapper">
                                                <img src="../<?php echo $invoicelogo[0][1][0]?>"/>
                                            </div>
                                        <?php
                                    }
                                    if(count($additional_logos) > 0){
                                        ?>
                                        <?php foreach($additional_logos as $additional_logo) {
                                            $logoImage = json_decode($additional_logo['logo'], true);
                                            $logoWidth = $additional_logo['logo_width'] * 4.7;
                                            $logoPosX = $additional_logo['logo_pos_x']* 4.7+495-$logoWidth/2;
                                            $logoPosY = $additional_logo['logo_pos_y']* 4.7;
                                            ?>
                                            <div class="additional_logo" style="position: absolute; width: <?php echo $logoWidth;?>px; top: <?php echo $logoPosY;?>px; left: <?php echo $logoPosX;?>px;">
                                                <img src="../<?php echo $logoImage[0][1][0]?>"/>
                                            </div>
                                        <?php } ?>
                                        <?php
                                    }
                                    ?>&nbsp;
                                </div>
                                <?php
                            }
                            ?>

                            <div class="ownercompany_top ">
                                <div class="ownercompany_top_left">
                                    <?php
                                    $s_sql = "SELECT * FROM customer WHERE id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
                                    $v_customer = ($o_query ? $o_query->row_array() : array());

                                    if($v_customer['useOwnInvoiceAdress']) {
                                        $s_cust_addr_prefix = 'ia';
                                        $customerAddress = 'own address';
                                        $customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
                                    } else {
                                        $s_cust_addr_prefix = 'pa';
                                        $customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
                                    }
                                    $s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress."<br/><br/>";

                                    $s_customer .= "<span class='cp_block'>".$formText_YourContactPerson_Output.': <span class="cp_wrapper">'.$contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']."</span> <br /></span>";

                                    echo $s_customer;
                                    ?>
                                </div>
                                <div class="ownercompany_top_right">
                                    <?php foreach($ownercompanies as $ownercompany){ ?>
                                        <div class="ownercompany_info_hidden ownercompany<?php echo $ownercompany['id']?>">
                                            <span style="font-size:26px;"><b><?php echo ($formText_Offer_header); ?></b></span><br />
                                            <?php
                                            $s_invoice_text = $ownercompany['companyname']." <br />".$ownercompany['companypostalbox']." <br />".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
                                            if($ownercompany['companyCountry'] != ""){
                                                $s_invoice_text .= ", ".$ownercompany['companyCountry'];
                                            }
                                            $dateString = date("d.m.Y");
                                            if($projectData){
                                                $dateString = date("d.m.Y", strtotime($projectData['date']));
                                            }
                                            $s_invoice_text .=" <br />".$formText_Phone.": ".$ownercompany['companyphone'];
                                            $s_invoice_text .= " <br /><br />".$formText_date.': <span class="dateWrapper">'.$dateString.'</span>';
                                            echo $s_invoice_text;
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                            <div class="frontpageHeadInfo"><span class="textwrapper"><?php if($projectData) { echo $projectData['offer_headline']; } else { echo $formText_OfferHeadlineHere_output;}?>&nbsp;</span><span class="edit_headline edit_pencil glyphicon glyphicon-pencil"></span></div>

                            <div class="textwrapper frontpageTextWrapper">
                                <?php
                                if($projectData) {
                                    echo $projectData['frontpage_text'];
                                } else {
                                     echo $formText_OfferFrontpageTextHere_output;
                                }
                                ?>&nbsp;
                            </div>
                            <span class="edit_frontpage_text edit_pencil glyphicon glyphicon-pencil"></span>
                            <?php
                            foreach($ownercompanies as $ownercompany){
                                echo "<div class='ownercompany_regards ownercompany_info_hidden ownercompany".$ownercompany['id']."'>".$formText_BestRegards_header.'<br/>
                                '.$ownercompany['companyname'].'<br/><br/>
                                <b>'.$loggedInPerson['first_name']." ".$loggedInPerson['middle_name']." ".$loggedInPerson['last_name'].'</b><br/>
                                '.$emailStringRow.'
                                '.$phoneStringRow."</div>";
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                    <div class="pageWrapper">
                        <?php
                        foreach($ownercompanies as $ownercompany){
                            $invoicelogo = json_decode($ownercompany['invoicelogo'],true);

                            $s_sql = "SELECT * FROM ownercompany_logos WHERE ownercompanyId = ?";
                            $o_query = $o_main->db->query($s_sql, array($ownercompany['id']));
                            $additional_logos = $o_query ? $o_query->result_array() : array();
                            ?>
                            <div class="ownercompany_top ownercompany_info_hidden ownercompany_logo_wrapper ownercompany<?php echo $ownercompany['id']?>">
                                <?php
                                if(count($invoicelogo) > 0){
                                    ?>
                                        <div class="imagewrapper">
                                            <img src="../<?php echo $invoicelogo[0][1][0]?>"/>
                                        </div>
                                    <?php
                                }

                                if(count($additional_logos) > 0){
                                    ?>
                                    <?php foreach($additional_logos as $additional_logo) {
                                        $logoImage = json_decode($additional_logo['logo'], true);
                                        $logoWidth = $additional_logo['logo_width'] *  4.7;
                                        $logoPosX = $additional_logo['logo_pos_x']* 4.7+495-$logoWidth/2;
                                        $logoPosY = $additional_logo['logo_pos_y']* 4.7;
                                        ?>
                                        <div class="additional_logo" style="position: absolute; width: <?php echo $logoWidth;?>px; top: <?php echo $logoPosY;?>px; left: <?php echo $logoPosX;?>px;">
                                            <img src="../<?php echo $logoImage[0][1][0]?>"/>
                                        </div>
                                    <?php } ?>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                        }
                        ?>
                        <?php if(!$v_customer_accountconfig['activateOfferFrontpage']) { ?>
                            <div class="ownercompany_top ">
                                <div class="ownercompany_top_left">
                                    <?php
                                    $s_sql = "SELECT * FROM customer WHERE id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($offer['customerId']));
                                    $v_customer = ($o_query ? $o_query->row_array() : array());

                                    if($v_customer['useOwnInvoiceAdress']) {
                                        $s_cust_addr_prefix = 'ia';
                                        $customerAddress = 'own address';
                                        $customerAddress = $v_customer['iaStreet1']."<br />".(!empty($v_customer['iaStreet2']) ? $v_customer['iaStreet2'] . '<br />' : '').$v_customer['iaPostalNumber']." ".$v_customer['iaCity'] . "<br>" . $v_customer['iaCountry'];
                                    } else {
                                        $s_cust_addr_prefix = 'pa';
                                        $customerAddress = $v_customer['paStreet']."<br />".(!empty($v_customer['paStreet2']) ? $v_customer['paStreet2'] . '<br />' : '').$v_customer['paPostalNumber']." ".$v_customer['paCity'] . "<br>" . $v_customer['paCountry'];
                                    }
                                    $s_customer = trim($v_customer['name']." ".$v_customer['middlename']." ".$v_customer['lastname'])."<br />".$customerAddress."<br/><br/>";
                                    $s_customer .= "<span class='cp_block'>".$formText_YourContactPerson_Output.': <span class="cp_wrapper">'.$contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']."</span> <br /></span>";

                                    echo $s_customer;
                                    ?>
                                </div>
                                <div class="ownercompany_top_right">
                                    <?php foreach($ownercompanies as $ownercompany){ ?>
                                        <div class="ownercompany_info_hidden ownercompany<?php echo $ownercompany['id']?>">
                                            <span style="font-size:26px;"><b><?php echo ($formText_Offer_header); ?></b></span><br />
                                            <?php
                                            $s_invoice_text = $ownercompany['companyname']." <br />".$ownercompany['companypostalbox']." <br />".$ownercompany['companyzipcode']." ".$ownercompany['companypostalplace'];
                                            if($ownercompany['companyCountry'] != ""){
                                                $s_invoice_text .= ", ".$ownercompany['companyCountry'];
                                            }
                                            $dateString = date("d.m.Y");
                                            if($projectData){
                                                $dateString = date("d.m.Y", strtotime($projectData['date']));
                                            }
                                            $s_invoice_text .=" <br />".$formText_Phone.": ".$ownercompany['companyphone'];
                                            $s_invoice_text .= " <br /><br />".$formText_date.': <span class="dateWrapper">'.$dateString.'</span>';
                                            echo $s_invoice_text;
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="clear"></div>
                            </div>
                        <?php } ?>
                        <?php if(!$v_customer_accountconfig['activateOfferFrontpage']) { ?>
                            <div class="frontpageHeadInfo"><span class="textwrapper"><?php if($projectData) { echo $projectData['offer_headline']; } else { echo $formText_OfferHeadlineHere_output;}?> &nbsp;</span><span class="edit_headline edit_pencil glyphicon glyphicon-pencil"></span></div>
                        <?php } ?>

                        <div class="textwrapper offerTextWrapper">
                            <?php
                            if($projectData) {
                                echo $projectData['offer_intro_text'];
                            } else {
                                 echo $formText_OfferIntroTextHere_output;
                            }
                            ?>&nbsp;
                        </div>
                        <span class="edit_offer_text edit_pencil glyphicon glyphicon-pencil"></span>
                            <?php
                        $html2='<table class="offerLinesTable" style="border-top:1px solid #cecece; border-bottom:1px solid #cecece;" cellspacing="0" cellpadding="4" border="0" width="100%">
                            <tr><td class="offerLinesTableFirst" style="font-weight:bold;"></td><td width="10"></td><td width="50" style="font-weight:bold;">'.$formText_amount.'</td><td width="90" style="font-weight:bold;">'.$formText_price.'</td><td class="offerLinesTableDiscount" width="55" style="font-weight:bold;">'.$formText_discount.'</td><td width="70" style="font-weight:bold; text-align:right;">'.$formText_totalprice.'</td></tr>';

                            $totalSum = 0;
                            $totalTax = 0;
                            foreach($orders as $order){
                                $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
                                $o_query = $o_main->db->query($s_sql, array($order['vatCode']));
                                $vatItem = $o_query ? $o_query->row_array() : array();
                                $vatPercent = $vatItem['percentRate'];
                                $vat = $order['priceTotal']*$vatPercent/100;
                                $totalSum+=$order['priceTotal'];
                                $totalTax += $vat;
                                $html2.= '
                                <tr class="offerlines"><td class="offerLinesTableFirst">'.proc_rem_style($order['articleName']).'</td><td></td><td>'.proc_rem_style(number_format(floatval($order['amount']),2,',',' ')).'</td><td>'.proc_rem_style(number_format(floatval($order['pricePerPiece']),2,',',' ')).'</td><td class="offerLinesTableDiscount">'.proc_rem_style(round($order['discountPercent'])).'%</td><td style="text-align:right;">'.proc_rem_style(number_format(floatval($order['priceTotal']),2,',',' ')).'</td></tr>';
                            }

                            $html2.= '
                               <tr class="offerlineTotal">
                                   <td class="offerLinesTableFirst"></td>
                                   <td width="10"></td>
                                   <td  width="50" style="border-top:1px solid #cecece;"><b>'.$formText_total.'</b></td>
                                   <td  width="90" style="border-top:1px solid #cecece;"></td>
                                   <td class="offerLinesTableDiscount"  width="55" style="border-top:1px solid #cecece;"></td>
                                   <td class="offerlineTotalWrapper" width="70" style="border-top:1px solid #cecece; text-align:right;">'.number_format(floatval($totalSum),2,',',' ').'</td>
                               </tr>
                               <tr class="offerlineTax">
                                   <td class="offerLinesTableFirst"></td>
                                   <td width="10"></td>
                                   <td  width="50"><b>'.$formText_totalTax.'</b></td>
                                   <td  width="90"></td>
                                   <td class="offerLinesTableDiscount"  width="55" ></td>
                                   <td class="offerlineTaxWrapper" width="70" style="text-align:right;">'.number_format(floatval($totalTax),2,',',' ').'</td>
                               </tr>
                               <tr class="offerlineTax">
                                   <td class="offerLinesTableFirst"></td>
                                   <td width="10"></td>
                                   <td  width="50"><b>'.$formText_totalWithTax.'</b></td>
                                   <td  width="90"></td>
                                   <td class="offerLinesTableDiscount"  width="55" ></td>
                                   <td class="offerlineTotalTaxWrapper" width="70" style="text-align:right;">'.number_format(floatval($totalSum+$totalTax),2,',',' ').'</td>
                               </tr>
                           </table>';
                           echo $html2;
                       ?>
                       <span class="edit_offer_lines edit_pencil glyphicon glyphicon-pencil"></span>
                       <br/>
                        <div class="textwrapper offerEndTextWrapper">
                            <?php
                            if($projectData) {
                                echo $projectData['offer_end_text'];
                            } else {
                                 echo $formText_OfferEndTextHere_output;
                            }
                            ?>&nbsp;
                        </div>
                        <span class="edit_offer_end_text edit_pencil glyphicon glyphicon-pencil"></span>

                        <?php if(!$v_customer_accountconfig['activateOfferFrontpage']) { ?>
                            <?php
                            foreach($ownercompanies as $ownercompany){
                                echo "<div class='ownercompany_regards ownercompany_info_hidden ownercompany".$ownercompany['id']."'>".$formText_BestRegards_header.'<br/>
                                '.$ownercompany['companyname'].'<br/><br/>
                                <b>'.$loggedInPerson['first_name']." ".$loggedInPerson['middle_name']." ".$loggedInPerson['last_name'].'</b><br/>
                                '.$emailStringRow.'
                                '.$phoneStringRow."</div>";
                            }
                            ?>
                        <?php } ?>
                    </div>
                </div>

                <div id="popup-validate-message" style="display:none;"></div>
                <div class="popupformbtn">
                    <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                    <input type="submit" name="sbmbtn" class="saveOnly" value="<?php echo $formText_Save_Output; ?>">
                    <input type="submit" name="sbmbtn2" class="saveAndCreatePdf" value="<?php echo $formText_SaveAndCreatePdf_Output; ?>">
                    <input type="hidden" class="createPdf" name="create_pdf" value="0" autocomplete="off"/>
                    <?php /*

                    <input type="submit" name="sbmbtn3" class="saveAndCreatePdfAndSendEmail" value="<?php echo $formText_SaveAndCreatePdfAndSendEmail_Output; ?>"> ?>
                    <!-- <input type="hidden" class="createPdfAndSend" name="create_pdf_send" value="0" autocomplete="off"/> -->
                    <input type="hidden" class="createPdfAndSend" name="create_pdf_send_link" value="0" autocomplete="off"/>
                    <?php */ ?>
                </div>
            </form>
            <div class="overlayBg"></div>
        </div>
        <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
        <script type="text/javascript">
        <?php
        $js_array = json_encode($templates);

        echo "var javascript_array = ". $js_array . ";\n";
        ?>
        $('#popupeditbox').addClass("fixedWidth");
        $('#popupeditbox textarea.ckeditor').each(function () {
            var $textarea = $(this);

            var ckeditorId = $textarea.attr('id');
        	CKEDITOR.replace( ckeditorId, {
        		toolbar: [
        			{ name: 'basicstyles', items: ['Bold','Italic','Underline'] },
        			{ name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
        		],
        		height: 300,
        		contentsCss: ["body {font-size: 16px; font-family: 'Verdana', sans-serif;}"],
                enterMode: CKEDITOR.ENTER_BR
        	});

            var ckeditor_ins = CKEDITOR.instances[$textarea.attr('id')];
            ckeditor_ins.on('resize',function(reEvent){
        		$(window).resize();;
            });
            ckeditor_ins.on('instanceReady', function() {
            	setTimeout(function(){
            		$(window).resize();
            	}, 100);
            });
            ckeditor_ins.on('change', function() {
                var name = $(ckeditor_ins).prop("name");
                if(name == "frontpage_text") {
                    $(".frontpageTextWrapper").html(ckeditor_ins.getData());
                } else if(name == "intro_text") {
                    $(".offerTextWrapper").html(ckeditor_ins.getData());
                } else if(name == "end_text") {
                    $(".offerEndTextWrapper").html(ckeditor_ins.getData());
                }
            	$(window).resize();
            });
        });
		$(document).on("change", ".articleTable input", function(){
			if($(this).hasClass("numberInput")){
				$(this).val(parseFloat($(this).val().replaceAll(",",".")).toFixed(2).replaceAll(".",","));
			}
		})
        <?php if($customer_basisconfig['activate_incl_tax_in_offer'] && $projectData['include_tax'] == null) { ?>
            $(".include_tax").prop("checked", true);
        <?php } ?>
        function numberWithSpaces(x) {
            var parts = x.toString().split(".");
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
            return parts.join(".");
        }
        function calculateTotal(){
            var globalTotal = 0;
            var globalTax = 0;
            $(".articleTable .articleRow").each(function(){
	            var pricePerPiece = parseFloat($(this).find(".pricePerPiece").data("value").toString().replace(",", ".")).toFixed(2);
	            var quantity = parseFloat($(this).find(".quantity").data("value").toString().replace(",", ".")).toFixed(4);
	            var discount = parseFloat($(this).find(".discount").data("value").toString().replace(",", ".")).toFixed(2);
                var totalPerRow = pricePerPiece * quantity * (100-discount)/100;
                var taxPercent = 0;
                if($(this).data("tax") != undefined){
                    taxPercent = $(this).data("tax").toString().replace(",", ".");
                }
                totalPerRow = parseFloat(totalPerRow).toFixed(2);
                globalTax += parseFloat(totalPerRow * taxPercent/100);
                globalTotal += parseFloat(totalPerRow);
                totalPerRow = numberWithSpaces(totalPerRow);
                totalPerRow = totalPerRow.toString().replace(".", ",");
                $(this).find(".priceTotal").html(totalPerRow);
            })
            globalTotal = parseFloat(globalTotal).toFixed(2);
            globalTax = parseFloat(globalTax).toFixed(2);
            globalTotalWithTax = parseFloat(globalTotal)+parseFloat(globalTax);
            globalTotalWithTax = parseFloat(globalTotalWithTax).toFixed(2);

            globalTotal = numberWithSpaces(globalTotal);
            globalTotal = globalTotal.toString().replace(".", ",");

            globalTax = numberWithSpaces(globalTax);
            globalTax = globalTax.toString().replace(".", ",");

            globalTotalWithTax = numberWithSpaces(globalTotalWithTax);
            globalTotalWithTax = globalTotalWithTax.toString().replace(".", ",");

            $(".grandTotalPrice").html(globalTotal);
            $(".grandTotalTax").html(globalTax);
            $(".grandTotalPriceTax").html(globalTotalWithTax);
            $(window).resize();
        }
        function rebindTable(){
            $(".accountInfoSelect").change(function(){
                var parent = $(this).parents("td");
                var label = parent.find(".accountingInfo");
                var bookaccountNr = parent.find(".bookaccountNrWrapper select").val();
                var vatcode = parent.find(".vatCodeWrapper select").val();
                var vatPercent = parent.find(".vatCodeWrapper select option:selected").data("percent");
                if(vatPercent != undefined){
                    $(this).parents(".articleRow").data("tax", vatPercent);
                }
                var periodising = parent.find(".periodisingWrapper select").val();
                var finalText =bookaccountNr;
                if(vatcode != ""){
                    finalText += " - "+vatcode;
                }
                if(periodising > 0){
                    finalText += " - P";
                }
                label.html(finalText);
                calculateTotal();
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
                        var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', supplier_id: $(".supplierChoose").val()};
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

        <?php if(v_customer_accountconfig['activeAccountingProjectOnOffer']) { ?>

            $(".popupform .buildingOwner").unbind("change").change(function(){
                var buildingOwnerProjectCode = -1;
                <?php if($customer_basisconfig['activateFilterProjectByOwnercompany']) { ?>
                    buildingOwnerProjectCode = $(".buildingOwner").data("projectcode");
                    if($(".buildingOwner option").length > 0) {
                        buildingOwnerProjectCode = $(".buildingOwner option:selected").data("projectcode");
                    }
                <?php } ?>
                var data = {
                    buildingOwnerProjectCode: buildingOwnerProjectCode,
                    projectCode: '<?php echo $projectData['accountingProjectCode']?>',
                    <?php if(v_customer_accountconfig['activeAccountingProjectOnOffer'] > 1) { ?>
                        projectMandatory: 1
                    <?php } ?>
                };
                ajaxCall('getProjects', data, function(json) {
                    $('.popupform .projectWrapper').html(json.html);
                });
            })
            $(".popupform .buildingOwner").change();
        <?php } ?>
        <?php if($v_customer_accountconfig['activateAccountingDepartmentOnOrder'] > 1) { ?>
            $(".popupform .buildingOwner").unbind("change").change(function(){
                var buildingOwnerProjectCode = -1;
                <?php if($customer_basisconfig['activateFilterProjectByOwnercompany']) { ?>
                    buildingOwnerProjectCode = $(".buildingOwner").data("projectcode");
                    if($(".buildingOwner option").length > 0) {
                        buildingOwnerProjectCode = $(".buildingOwner option:selected").data("projectcode");
                    }
                <?php } ?>
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
            })
            $(".popupform .buildingOwner").change();
        <?php } ?>

        var out_popup3;
        var out_popup_options3={
        	follow: [true, true],
        	followSpeed: 300,
        	fadeSpeed: 0,
        	modalClose: false,
        	escClose: false,
        	closeClass:'b-close',
        	onOpen: function(){
        		$(this).addClass('opened');
        		//$(this).find('.b-close').on('click', function(){out_popup.close();});
        	},
        	onClose: function(){
        		$(this).removeClass('opened');
                if($(this).hasClass("refresh")){
                    var data = {
                        customerId: '<?php echo $_POST['customerId'];?>',
                        offerId: '<?php echo $_POST['offerId'];?>',
                    };
                    ajaxCall('editOffer', data, function(json) {
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                        $(window).resize();
                    });
                }
        	}
        };
        function updateOrderlinesPreview(){
            showDiscountColumn = false;
            $(".offerLinesTable .offerlines").remove();
            $(".offer_lines_wrapper .articleTableWrapper tr.articleRow").each(function(){
                var name = $(this).find(".articleNameInput").val();
                var quantity = $(this).find(".quantityInput").val();
                var pricePerPiece = $(this).find(".pricePerPieceInput").val();
                var priceTotal = $(this).find(".priceTotal").html();
                var discountNumberString =  $(this).find(".discountInput").val();
                var discountNumber =  parseFloat(discountNumberString.replace(",",".")).toFixed(2);
                if(discountNumber > 0){
                    showDiscountColumn = true;
                }
                $('<tr class="offerlines"><td class="offerLinesTableFirst">'+name+' <span class="errorText"></span></td><td></td><td>'+quantity+'</td><td>'+pricePerPiece+'</td><td class="offerLinesTableDiscount">'+discountNumberString+'</td><td style="text-align:right;">'+priceTotal+'</td></tr>').insertBefore(".offerLinesTable .offerlineTotal");
            })
            if(showDiscountColumn){
                $(".offerLinesTable .offerLinesTableDiscount").show();
            } else {
                $(".offerLinesTable .offerLinesTableDiscount").hide();
            }
            var priceGlobalTotal = $(".articleTable .grandTotalPrice").html();
            var priceGlobalTax = $(".articleTable .grandTotalTax").html();
            var priceGlobalTotalTax = $(".articleTable .grandTotalPriceTax").html();
            $(".offerlineTotalWrapper").html(priceGlobalTotal);
            $(".offerlineTaxWrapper").html(priceGlobalTax);
            $(".offerlineTotalTaxWrapper").html(priceGlobalTotalTax);
            if($(".articleTable .include_tax").is(":checked")){
                $(".offerLinesTable .offerlineTax").show();
            } else {
                $(".offerLinesTable .offerlineTax").hide();
            }

            $(".pageWrapper .pageWrapperSeperator").remove();
            $(".pageWrapper .ownercompany_logo_wrapper.toBeRemoved").remove();
            $(".pageWrapper").each(function(){
                var newLines = $(this).find("*");
                var heightValue = 1253;
                var previousTag;
                $(newLines).each(function(){
                    if($(this).is(":visible") && !$(this).hasClass("edit_pencil")){
                        var heightStart = 0;
                        if($(".pageWrapper .pageWrapperSeperator").length > 0){
                            var lastSeperator = $(".pageWrapper .pageWrapperSeperator").eq($(".pageWrapper .pageWrapperSeperator").length - 1);
                            heightStart = lastSeperator.position().top + 170;
                        }
                        var heightToReach = heightStart+heightValue;
                        if($(this).position().top > heightToReach){
                            if($(this).parents(".offerLinesTable").length > 0) {
                                var html = '';
                                var imageObject = $(".ownercompany_logo_wrapper:visible").clone();
                                if(imageObject.length > 0){
                                    imageObject = imageObject.eq(0);
                                    html = $("<div />").append(imageObject.clone().addClass("toBeRemoved")).html();
                                }
                                $("<div class='pageWrapperSeperator'></div>"+html).insertBefore($(this).parents(".offerLinesTable"));
                                var pageAmountBefore = $(this).parents(".offerLinesTable").prevAll(".pageWrapperSeperator").length;
                                heightToReach += (1180+120) * pageAmountBefore;
                            } else {
                                var html = '';
                                var imageObject = $(".ownercompany_logo_wrapper:visible").clone();
                                if(imageObject.length > 0){
                                    imageObject = imageObject.eq(0);
                                    html = $("<div />").append(imageObject.clone().addClass("toBeRemoved")).html();
                                }
                                $("<div class='pageWrapperSeperator'></div>"+html).insertAfter(previousTag);
                                if($(this).prop("tagName") == 'BR'){
                                    // $(this).remove();
                                }
                                var pageAmountBefore = $(this).prevAll(".pageWrapperSeperator").length;
                                heightToReach += (1180+120) * pageAmountBefore;
                            }
                        }
                        if($(this).parents(".offerLinesTable").length > 0) {
                            previousTag = $(this).parents(".offerLinesTable");
                        } else {
                            previousTag = $(this);
                        }
                    }
                })
            })
        }
        $(document).ready(function() {
            setTimeout(function() {
                updateOrderlinesPreview();
            }, 100);
            $(".offer_headline_input").off("keyup").on("keyup", function(){
                $(".frontpageHeadInfo .textwrapper").html($(this).val());
            })
            // $(".close_edit_cancel").off("click").on("click", function(){
            //     $(".pdf_input_text_wrapper").hide();
            //     $(".popupeditbox").removeClass("pdfOpened");
            //     $(".row_wrapper").hide();
            // })
            $(".close_edit_wrapper").off("click").on("click", function(){
                $(".pdf_input_text_wrapper").hide();
                $(".popupeditbox").removeClass("pdfOpened");
                $(".row_wrapper").hide();
                updateOrderlinesPreview();
            })
            $(".edit_headline").off("click").on("click", function(){
                var prevWrappers = $(this).parents(".pageWrapper").prevAll(".pageWrapper");
                var totalHeight = 0;
                $(prevWrappers).each(function(){
                    totalHeight += $(this).height();
                })
                $(".pdf_input_text_wrapper").css("top", $(this).position().top+totalHeight);
                $(".pdf_input_text_wrapper").show();
                $(".popupeditbox").addClass("pdfOpened");
                $(".row_wrapper").hide();
                $(".offer_headline_wrapper").show();
            })
            $(".edit_frontpage_text").off("click").on("click", function(){
                var prevWrappers = $(this).parents(".pageWrapper").prevAll(".pageWrapper");
                var totalHeight = 0;
                $(prevWrappers).each(function(){
                    totalHeight += $(this).height();
                })
                $(".pdf_input_text_wrapper").css("top", $(this).position().top+totalHeight);
                $(".pdf_input_text_wrapper").show();
                $(".popupeditbox").addClass("pdfOpened");
                $(".row_wrapper").hide();
                $(".offer_frontpage_wrapper").show();
            })
            $(".edit_offer_text").off("click").on("click", function(){
                var prevWrappers = $(this).parents(".pageWrapper").prevAll(".pageWrapper");
                var totalHeight = 0;
                $(prevWrappers).each(function(){
                    totalHeight += $(this).height();
                })

                $(".pdf_input_text_wrapper").css("top", $(this).position().top+totalHeight);
                $(".pdf_input_text_wrapper").show();
                $(".popupeditbox").addClass("pdfOpened");
                $(".row_wrapper").hide();
                $(".offer_intro_text_wrapper").show();
            })
            $(".edit_offer_lines").off("click").on("click", function(){
                var prevWrappers = $(this).parents(".pageWrapper").prevAll(".pageWrapper");
                var totalHeight = 0;
                $(prevWrappers).each(function(){
                    totalHeight += $(this).height();
                })

                $(".pdf_input_text_wrapper").css("top", $(this).position().top+totalHeight-$(".offerLinesTable").height()/2);
                $(".pdf_input_text_wrapper").show();
                $(".popupeditbox").addClass("pdfOpened");
                $(".row_wrapper").hide();
                $(".offer_lines_wrapper").show();
            })
            $(".edit_offer_end_text").off("click").on("click", function(){
                var prevWrappers = $(this).parents(".pageWrapper").prevAll(".pageWrapper");
                var totalHeight = 0;
                $(prevWrappers).each(function(){
                    totalHeight += $(this).height();
                })
                $(".pdf_input_text_wrapper").css("top", $(this).position().top+totalHeight);
                $(".pdf_input_text_wrapper").show();
                $(".popupeditbox").addClass("pdfOpened");
                $(".row_wrapper").hide();
                $(".offer_end_text_wrapper").show();
            })
            $(".contactPersonSelect").change(function(){
                if($(this).val() == "-1") {
                    var data = {
                        customerId: '<?php echo $_POST['customerId'];?>',
                        from_popup: 1
                    };
                    ajaxCall('edit_contactperson', data, function(json) {
                        $('#popupeditboxcontent2').html('');
                        $('#popupeditboxcontent2').html(json.html);
                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                        $("#popupeditbox2:not(.opened)").remove();
                        $(window).resize();
                    });
                } else if($(this).val() != ""){
                    $(".cp_block").show();
                    $(".cp_block .cp_wrapper").html($(this).find("option:selected").data("name"));
                } else {
                    $(".cp_block").hide();
                }
            }).change();
            $(".selectTemplate").change(function(){
                var value = $(this).val();

                var headline = $(this).find("option:selected").data("headline");
                var introtext = $(this).find("option:selected").data("introtext");
                var endtext = $(this).find("option:selected").data("endtext");

                $(javascript_array).each(function(index, element){
                    var elementId = element.id;
                    if(value == elementId) {
                        var offer_end_text = element.offer_end_text;
                        var offer_headline = element.offer_headline;
                        var offer_intro_text = element.offer_intro_text;
                        $(".offer_headline_input").val(offer_headline).keyup();

                        <?php if($v_customer_accountconfig['activateOfferFrontpage']) { ?>
                            var frontpage_headline = element.frontpage_headline;
                            var frontpage_text = element.frontpage_text;
                            $(".frontpage_headline_input").val(frontpage_headline).keyup();
                            CKEDITOR.instances.frontpage_text.setData(frontpage_text);
                        <?php } ?>
                        CKEDITOR.instances.end_text.setData(offer_end_text);
                        CKEDITOR.instances.intro_text.setData(offer_intro_text);
                    }
                })

            })
            $(".edit_templates").on('click', function(e){
            	e.preventDefault();
            	var data = { };
                ajaxCall('edit_offer_templates', data, function(obj) {
                    $('#popupeditboxcontent2').html('');
                    $('#popupeditboxcontent2').html(obj.html);
                    out_popup3 = $('#popupeditbox2').bPopup(out_popup_options3);
                    $("#popupeditbox2:not(.opened)").remove();
                });
            });
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
            $('.saveAndCreatePdf').off("click").click(function(e) {
                e.preventDefault();
                $('.createPdf').val(1);
                $('.createPdfAndSend').val(0);
                $("form.output-form").submit();
            });
            $('.saveAndCreatePdfAndSendEmail').off("click").click(function(e) {
                e.preventDefault();
                $('.createPdf').val(0);
                $('.createPdfAndSend').val(1);
                $("form.output-form").submit();
            });
            $('.saveOnly').off("click").click(function(e) {
                e.preventDefault();
                $('.createPdf').val(0);
                $('.createPdfAndSend').val(0);
                $("form.output-form").submit();
            });
            $(".buildingOwner").change(function(){
                $(".ownercompany_info_hidden").hide();
                $(".ownercompany"+$(this).val()).show();
            }).change()
            $("form.output-form").validate({
                ignore: [],
                submitHandler: function(form) {
                    if(!fw_click_instance)
            		{
                        $('textarea.ckeditor').each(function () {
        					var $textarea = $(this);
        					$textarea.val(CKEDITOR.instances[$textarea.attr('id')].getData());
        				});
        				var formdata = $(form).serializeArray();
        				var data = {};
        				$(formdata ).each(function(index, obj){
        					if(data[obj.name] != undefined) {
        						if(Array.isArray(data[obj.name])){
        							data[obj.name].push(obj.value);
        						} else {
        							data[obj.name] = [data[obj.name], obj.value];
        						}
        					} else {
        						data[obj.name] = obj.value;
        					}
        				});
            			fw_click_instance = true;
                        fw_loading_start();
                        $(".errorText").hide().html("");
                        $.ajax({
                            url: $(form).attr("action"),
                            cache: false,
                            type: "POST",
                            dataType: "json",
                            data: data,
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
                                            $("#offerId").val(data.data);
                                        }
                                        $.each(data.error, function(index, value){
                                            var _type = Array("error");
                                            if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                            if(index == -1){
                                                $("#popup-validate-message").html(value);
                                            } else {
                                                $(".articleTableWrapper .articleRow").eq(index).find(".accountingInfoTable .errorText").html(value).show();
                                                $(".offerLinesTable .offerlines").eq(index).find(".errorText").html(value).show();
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
                    if(element.attr("name") == "seller_people_id") {
                        error.insertAfter(".selectWorker");
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

            $(".selectWorker").unbind("click").bind("click", function(e){
                e.preventDefault();
                fw_loading_start();
                var _data = { fwajax: 1, fw_nocss: 1};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
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
                        ajaxCall('editOffer', data, function(json) {
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
                        var _data = { fwajax: 1, fw_nocss: 1, search: parent.find(".articleName").val(), customerId: '<?php echo $customer['id'];?>', addNewRow: 1, supplier_id: $(".supplierChoose").val()};
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
            <?php if (!$offerId) { ?>
                $(".popupform .addEntryBtn").click();
            <?php } ?>
        });

        </script>
        <style>
        .offerLinesTable {
            table-layout: fixed;
        }
        .offerLinesTableFirst {
            width: 230px;
        }
        .offerLinesTableFirst.hasDiscount {
            width: 260px;
        }
        .offerLinesTableDiscount {
            width: 50px;
        }
        .cp_block {
            display: none;
        }
        .connectToProjectLink {}
        .popupform .fieldWrapper {
            position: relative;
        }
        .selectTemplateWrapper {
            float: right;
            text-align: right;
        }
        .edit_templates {
            text-decoration: none;
            color: #46b2e2;
            cursor: pointer;
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
        #popupeditbox.popupeditbox.fixedWidth {
            width: 1100px;
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
        .taxBlock {
            float: right;
            font-weight: bold;
            margin-right: 15px;
        }
        .taxBlock span {
            vertical-align: middle;
        }
        .taxBlock input {
            vertical-align: middle;
            margin: 0;
            margin-left: 10px;s
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
        .articleTable .article_supplier_dropdown {
            float: left;
            margin-left: 20px;
            margin-top: 2px;
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
        .end_offer {
            margin-top: 15px;
        }
        .popupeditbox .popupform .line .lineTitle {
            width: 15%;
        }
        .popupeditbox .popupform .line .lineInput {
            width: 85%;
        }
        .page_divider {
            font-weight: bold;
            border-bottom: 1px solid #ededed;
            padding: 5px 0px;
            margin-bottom: 10px;
            font-size: 15px;
        }
        .preview {
            margin: 0 auto;
            display: block;
            width: 990px;
        }
        .preview .pageWrapper {
            position: relative;
            padding: 40px 93px 100px 93px;
            border: 1px solid #cecece;
            font-family: 'Verdana', sans-serif;
            margin-bottom: 15px;
            min-height: 1440px;
        }
        .preview .pageWrapper .pageWrapperSeperator {
            width: calc(100% + 190px);
            position: relative;
            left: -90px;
            margin-top: 112px;
            margin-bottom: 40px;
            border-top: 1px solid #cecece;
            border-bottom: 1px solid #cecece;
            height: 18px;
        }
        .preview .frontpageHeadInfo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
			margin-top: 60px;
        }
        .preview .pageWrapper {
            font-size: 17px;
            line-height: 21px;
            letter-spacing: -0.1px;
        }
        .preview .ownercompany_top {
            font-size: 15px;
            margin-bottom: 15px;
        }
        .preview .pageWrapper table {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .preview .pageWrapper ul {
            margin: 20px 0px 20px 0px;
            padding: 0;
        }
        .preview .pageWrapper ol {
            margin: 20px 0px 20px 0px;
            padding: 0;
        }
        .preview .pageWrapper li {
            list-style: initial;
            margin: 0px 0px 0px 35px;
        }
        .preview .pageWrapper ol,
        .preview .pageWrapper ol li {
            list-style-type: decimal;
        }
        .preview .pageWrapper table td {
            font-family: 'Verdana', sans-serif;
            font-size: 17px !important;
            padding: 5px 5px;
        }
        .preview .ownercompany_info_hidden {
            display: none;
        }
        .preview .ownercompany_regards {
            margin-top: 20px;
        }
        .preview .ownercompany_top .imagewrapper {
            text-align: center;
        }
        .preview .ownercompany_top .imagewrapper img {
            width: 280px;
            margin-left: -48px;
        }
        .preview .ownercompany_top .additional_logos_wrapper {
            position: absolute;
            top: 40px;
            right: 93px;
        }
        .preview .ownercompany_top .additional_logo {
        }
        .preview .ownercompany_top .additional_logo img {
            vertical-align: top;
            width: 100%;
        }
        .preview .ownercompany_top .ownercompany_top_left {
            float: left;
        }
        .preview .ownercompany_top .ownercompany_top_right {
            float: right;
            width: 240px;
        }
        .edit_pencil {
            font-size: 13px !important;
            color: #46b2e2;
            cursor: pointer;
            float: right;
        }
        .edit_headline {
            margin-left: 20px;
        }
        .pdf_input_text_wrapper {
            position: absolute;
            display: none;
            width: 100%;
            background: #fff;
            z-index: 11;
            border: 1px solid #cecece;
            border-radius: 4px;
            padding: 10px 15px;
        }
        .pdf_input_text_wrapper .row_wrapper {
            display: none;
        }
        .popupeditbox.pdfOpened::after {
            content: ' ';
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            top: 0;
            left: 0;
        }
        .popupeditbox .popupformbtn button.close_edit_cancel {
            background-color: #fff;
            color: #194273;
            border: 1px solid #194273;
        }
        .offerlineTax {
            display: none;
        }
        </style>
    <?php } else {
        echo $formText_ProjectAlreadyApprovedForInvoicing_output;
    }
}
?>