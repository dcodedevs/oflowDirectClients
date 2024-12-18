<?php
include("fnc_get_sl_rows.php");

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
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
if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 1){
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 2) {
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 0;
}

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();
function duplicate_images($o_main, $s_images)
{
    global $moduleID;
	$s_acc_path = realpath(__DIR__."/../../../");
	$v_images = $v_images_new = json_decode($s_images, true);

	foreach($v_images as $l_key => $v_image)
	{
		$v_data = array();
        $v_data['filename'] = $v_image[0];
        $v_data['content_module_id'] = $moduleID;
		$o_main->db->insert('uploads', $v_data);
		$v_images_new[$l_key][4] = $o_main->db->insert_id();
		$v_tmp = array();
		foreach($v_image[1] as $l_i => $s_image)
		{
            $s_image_array = explode("/cpi_letters/", $s_image);
			$v_tmp[$l_i] = $s_image_array[0]."/cpi_letters/orders/".rawurlencode($s_image_array[1]);
			$s_file = $s_image_array[0]."/cpi_letters/orders/".$s_image_array[1];
			mkdir(dirname($s_acc_path."/".$s_file),octdec(777),true);
			copy($s_acc_path."/".$s_image, $s_acc_path."/".$s_file);
		}
		$v_images_new[$l_key][1] = $v_tmp;
	}

	return json_encode($v_images_new);
}
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}
// Process
$l_count = 0;
if(isset($_POST["subscribtion_id"], $_POST["selection"], $_POST['date']))
{

    $project_id = isset($_POST['project_filter']) ? $_POST['project_filter'] : 0;
    $department_id = isset($_POST['department_filter']) ? $_POST['department_filter'] : 0;

    $ownercompany_filter = $_POST['ownercompany'] ? explode(",", $_POST['ownercompany']) : array();
    $customerselfdefinedlist_filter = $_POST['customerselfdefinedlist_filter'] ? $_POST['customerselfdefinedlist_filter'] : "";
    $ownercompany_filter_sql = "";
    $selfdefined_join = "";
    $selfdefined_sql = "";
    $real_ownercompany_filter = array();
    if(count($ownercompany_filter) > 0){
    	foreach($ownercompany_filter as $singleItem){
    		if($singleItem > 0){
    			array_push($real_ownercompany_filter, $singleItem);
    		}
    	}
    	if(count($real_ownercompany_filter) > 0){
    		$ownercompany_filter_sql = " AND subscriptionmulti.ownercompany_id IN (".implode(',', $real_ownercompany_filter).")";
    	}
    }
    if($customerselfdefinedlist_filter != ""){
    	$selfdefined_join = "
    	LEFT OUTER JOIN customer_selfdefined_values ON  customer_selfdefined_values.customer_id = customer.id AND customer_selfdefined_values.selfdefined_fields_id = ".$o_main->db->escape($batch_renewal_accountconfig['customerSelfdefinedField'])."
    	LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_value_id = customer_selfdefined_values.id
    	";
    	$selfdefined_sql = " AND (customer_selfdefined_values_connection.selfdefined_list_line_id = ".$o_main->db->escape($customerselfdefinedlist_filter)." OR customer_selfdefined_values.value= ".$o_main->db->escape($customerselfdefinedlist_filter).")";
    }

    if($project_id > 0){
    	$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_id));
    	$projectData = $o_query ? $o_query->row_array() : array();

    	$ownercompany_filter_sql .= " AND (subscriptionmulti.projectId = ".$o_main->db->escape($projectData['projectnumber']).")";
    }
    if($department_id > 0){
    	$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE departmentnumber = ?", array($department_id));
    	$departmentData = $o_query ? $o_query->row_array() : array();
    	$ownercompany_filter_sql .= " AND (subscriptionmulti.departmentCode = ".$o_main->db->escape($departmentData['departmentnumber']).")";
    }

	$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
	    $customer_basisconfig = $o_query->row_array();
	}
	$dateMarker = $_POST['date'];

	if($customer_basisconfig['activateSubscriptionRenewalDateSetting']){
		$s_sql = "select subscriptionmulti.*, subscriptiontype.activate_own_tab_in_batchrenewal, subscriptiontype.name as subscriptionTypeName, subscriptiontype.periodUnit, subscriptiontype.activate_specified_invoicing, subscriptiontype.defaultPeriodising, subscriptiontype.default_subscriptionname_in_invoiceline, subscriptiontype.subscription_category, subscriptiontype.script_for_generating_order, subscriptiontype.useMainContactAsContactperson
            from subscriptionmulti
			left outer join subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
			left outer join subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
			left outer join customer ON customer.id = subscriptionmulti.customerId
			".$selfdefined_join."
			WHERE customer.content_status < 2 AND (subscriptiontype_subtype.is_free = 0 OR subscriptiontype_subtype.is_free is null) AND (subscriptiontype_subtype.type <> 4 OR subscriptiontype_subtype.type is null) AND str_to_date(startDate,'%Y-%m-%d') > str_to_date('0000-00-00','%Y-%m-%d')
			AND (
                (str_to_date(nextRenewalDate,'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') AND (subscriptionmulti.renewalappearance = 0 OR subscriptionmulti.renewalappearance is null))
				OR
				(subscriptionmulti.renewalappearance = 1 AND date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL subscriptionmulti.renewalappearance_daynumber DAY)  <= str_to_date('".$dateMarker."','%Y-%m-%d'))
				OR
				(subscriptionmulti.renewalappearance = 2 AND date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL subscriptionmulti.renewalappearance_daynumber DAY) <= str_to_date('".$dateMarker."','%Y-%m-%d'))
				OR
				(subscriptionmulti.renewalappearance = 3 AND str_to_date(CONCAT(YEAR(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', MONTH(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), subscriptionmulti.renewalappearance_daynumber)),'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') )
				OR
				(subscriptionmulti.renewalappearance = 5 AND str_to_date(CONCAT(YEAR(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', MONTH(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), DAY(LAST_DAY(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), subscriptionmulti.renewalappearance_daynumber)),'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') )
				OR
				(subscriptionmulti.renewalappearance = 4 AND str_to_date(CONCAT(YEAR(nextRenewalDate), '-', MONTH(nextRenewalDate), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(nextRenewalDate)), DAY(LAST_DAY(nextRenewalDate)), subscriptionmulti.renewalappearance_daynumber)), '%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d'))
			)
			AND ((stoppedDate = '0000-00-00' OR stoppedDate is null) OR (nextRenewalDate <> '0000-00-00' AND stoppedDate > nextRenewalDate))
			AND (freeNoBilling < 1 OR freeNoBilling IS NULL)
			AND (connectedCustomerId = 0 OR connectedCustomerId IS NULL)
			AND subscriptiontype.autorenewal = 1
			AND subscriptionmulti.content_status = 0 AND (subscriptionmulti.onhold is null OR subscriptionmulti.onhold = 0 OR subscriptionmulti.onhold = '')
			".$ownercompany_filter_sql."
			".$selfdefined_sql."
			order by nextRenewalDate";
	} else {
		$s_sql = "select subscriptionmulti.*, subscriptiontype.activate_own_tab_in_batchrenewal, subscriptiontype.name as subscriptionTypeName, subscriptiontype.periodUnit, subscriptiontype.activate_specified_invoicing, subscriptiontype.defaultPeriodising, subscriptiontype.default_subscriptionname_in_invoiceline, subscriptiontype.subscription_category, subscriptiontype.script_for_generating_order, subscriptiontype.useMainContactAsContactperson
        from subscriptionmulti
        left outer join subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
        left outer join subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
        left outer join customer ON customer.id = subscriptionmulti.customerId
        ".$selfdefined_join."
        where customer.content_status < 2 AND (subscriptiontype_subtype.is_free = 0 OR subscriptiontype_subtype.is_free is null) AND (subscriptiontype_subtype.type <> 4 OR subscriptiontype_subtype.type is null)
        AND (connectedCustomerId = 0 OR connectedCustomerId IS NULL) AND str_to_date(startDate,'%Y-%m-%d') > str_to_date('0000-00-00','%Y-%m-%d') AND str_to_date(nextRenewalDate,'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d')
        AND ((stoppedDate = '0000-00-00' OR stoppedDate is null) OR (nextRenewalDate <> '0000-00-00' AND stoppedDate > nextRenewalDate)) AND (freeNoBilling < 1 OR freeNoBilling IS NULL)
        AND subscriptiontype.autorenewal = 1  AND subscriptionmulti.content_status = 0 AND (subscriptionmulti.onhold is null OR subscriptionmulti.onhold = 0 OR subscriptionmulti.onhold = '')
        ".$ownercompany_filter_sql."
        ".$selfdefined_sql."
        order by nextRenewalDate";
	}
	$o_subscribtions = array();
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$o_subscribtions = $o_query->result_array();
	}
	foreach($o_subscribtions as $v_row)
	{
        if($v_row["invoice_to_other_customer_id"] > 0){
            $v_row["customerId"] = $v_row["invoice_to_other_customer_id"];
        }
        if(intval($v_row['placeSubscriptionNameInInvoiceLine']) == 0){
        	$v_row['placeSubscriptionNameInInvoiceLine'] = $v_row['default_subscriptionname_in_invoiceline'];
        } else {
        	$v_row['placeSubscriptionNameInInvoiceLine']--;
        }
        $skip = false;
        if($v_row['priceAdjustmentType'] == 1) {
            $dateToCheck = $v_row['annualAdjustmentDate'];
            if(!validateDate($v_row['annualAdjustmentDate'])){
                $dateToCheck = date("Y-m-d");
            }
            if(strtotime($dateToCheck) <= time()){
                $skip = true;
            }
        }
		if($v_row['priceAdjustmentType'] == 2) {
			$dateToCheck = $subscription['nextCpiAdjustmentDate'];
			if(!validateDate($subscription['nextCpiAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if(strtotime($dateToCheck) <= strtotime($subscription['nextRenewalDate'])){
				$skip = true;
			}
		}
		if($v_row['priceAdjustmentType'] == 3) {
			$dateToCheck = $subscription['nextManualAdjustmentDate'];
			if(!validateDate($subscription['nextManualAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if(strtotime($dateToCheck) <= strtotime($subscription['nextRenewalDate'])){
				$skip = true;
			}
		}

        if(!$skip) {
    		$invoiceDateSettingFromSubscription = 0;
    		if($customer_basisconfig['activateSubscriptionRenewalDateSetting']){
    			$invoiceDateSettingFromSubscription = $v_row['id'];
    		}
    		$seperateInvoiceFromSubscription = 0;
    		if($v_row['seperateInvoiceFromSubscription']){
    			$seperateInvoiceFromSubscription = $v_row['id'];
    		}
    		if(!in_array($v_row["id"], $_POST["selection"])) continue;
    		$l_id = array_search($v_row["id"], $_POST["subscribtion_id"]);
    		if($l_id === false) continue;
			$projectCode = "";
			if($v_row['projectId'] != ""){
				$projectCode = $v_row['projectId'];
			}
    		if($batch_renewal_basisconfig['activateGetProjectcodeFromSubscription']){
    			$connectTablename = $batch_renewal_basisconfig['tablenameOnConnecttable'];
    			$fieldInConnecttableForSubscriptionId = $batch_renewal_basisconfig['fieldInConnecttableForSubscriptionId'];
    			$fieldInConnecttableForConnectedRecordId = $batch_renewal_basisconfig['fieldInConnecttableForConnectedRecordId'];
    			$connectedRecordTableName = $batch_renewal_basisconfig['connectedRecordTableName'];
    			$connectedRecordConnectionFieldname = $batch_renewal_basisconfig['connectedRecordConnectionFieldname'];
    			$tablenameToGetProjectcodeFrom = $batch_renewal_basisconfig['tablenameToGetProjectcodeFrom'];
    			$fieldnameToGetProjectcodeFrom = $batch_renewal_basisconfig['fieldnameToGetProjectcodeFrom'];

    			$s_sql = "SELECT {$connectedRecordTableName}.* FROM {$connectTablename} LEFT OUTER JOIN {$connectedRecordTableName} ON {$connectedRecordTableName}.id = {$connectTablename}.{$fieldInConnecttableForConnectedRecordId}
    			WHERE {$connectTablename}.{$fieldInConnecttableForSubscriptionId} = ?";
    			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
    			if($o_query && $o_query->num_rows()>0){
    				$projectCodeFromId = "";
    				$connectedRecords = $o_query->result_array();

    				foreach($connectedRecords as $connectedRecord){
    					if($connectedRecord[$connectedRecordConnectionFieldname] != "") {
    						$projectCodeFromId = $connectedRecord[$connectedRecordConnectionFieldname];
    						break;
    					}
    				}
    				$s_sql = "SELECT * FROM {$tablenameToGetProjectcodeFrom} WHERE id = ?";
    				$o_query = $o_main->db->query($s_sql, array($projectCodeFromId));
    				$projectCodeItem = $o_query ? $o_query->row_array() : array();
    				if($projectCodeItem[$fieldnameToGetProjectcodeFrom] != "" && $projectCodeItem[$fieldnameToGetProjectcodeFrom] > 0){
    					$projectCode = $projectCodeItem[$fieldnameToGetProjectcodeFrom];
    				}
    			}
    		}
            $departmentCode = $v_row['departmentCode'];

    		// if(intval($_POST["price"][$l_id]) < 0) continue;
    		//($v_row["nextRenewalDate"] == "0000-00-00"
    		if($v_row['nextRenewalDate'] == '0000-00-00')
    			$nextrenewaldatevalue = $v_row['startDate'];
    		else
    			$nextrenewaldatevalue = $v_row['nextRenewalDate'];

    		$lastdate = $nextdate = $nextrenewaldatevalue;
    		$nextdate2 = strtotime($nextdate);


			$realStoppedDate = $v_row['stoppedDate'];
			$stoppedDate = strtotime("+". $v_row['periodNumberOfMonths']." months", strtotime($lastdate));

    		if(intval($v_row['periodUnit']) == 0){
    			$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
    			$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
    		} else {
    			$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths']));
    			$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths'])-24*60*60);
    		}

            $modifier = 1;
            $modifierActivated = false;
            if($realStoppedDate != "0000-00-00" && $realStoppedDate != null) {
                if(strtotime($realStoppedDate) < $stoppedDate-24*60*60) {
                    $earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
                    $later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)));
                    $laterTimestamp = $later->getTimestamp();
                    $lastDayOfStoppedDate = date("t.m.Y", $laterTimestamp);
                    $dayOfStoppedDate = date("d.m.Y", $laterTimestamp);

                    $wholeMonths = 0;
                    do {
                        $earlier->add(new DateInterval("P1M"));
                        $timestamp = $earlier->getTimestamp();
                        $wholeMonths++;
                    } while($timestamp < $laterTimestamp);

                    $modifier = $wholeMonths;
                    if($lastDayOfStoppedDate != $dayOfStoppedDate) {
                        $stoppedDateMonthTotalDays = date("t", strtotime($realStoppedDate));
                        $stoppedDateMonthFirstDay = date("01.m.Y", strtotime($realStoppedDate));

                        if(strtotime($nextrenewaldatevalue) > strtotime($stoppedDateMonthFirstDay)){
                            $earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
                        } else {
                            $earlier = new DateTime(date("Y-m-d", strtotime($stoppedDateMonthFirstDay)));
                        }
                        $later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)+24*60*60));
                        $diff = $later->diff($earlier);
                        $stoppedDateMonthDays = $diff->format("%a");

                        $modifier = $modifier + number_format($stoppedDateMonthDays/$stoppedDateMonthTotalDays, 4, ".", "");
                    }
                    $modifierActivated = true;

                    $nextrenewaldate = date('Y-m-d', strtotime($realStoppedDate));
                    $nextrenewaldate2 = date('d.m.Y', strtotime($realStoppedDate));
                    $decimalNumber = 4;
                }
            }

            $s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$v_row['id']." ORDER BY id ASC";
            $o_query = $o_main->db->query($s_sql);
            $datesForInvoicing = ($o_query ? $o_query->result_array() : array());
            $dateArray = array();
            foreach($datesForInvoicing as $dateForInvoicing) {
                $dateArray[] = date("d.m", strtotime($dateForInvoicing['date']));
            }
            $totalCollectNumber = count($dateArray);
            foreach($dateArray as $key => $dateToCompare) {
                if(date("d.m", strtotime($nextrenewaldatevalue)) == $dateToCompare){
                    $collectNumber = $key+1;
                }
            }
            if($v_row['subscription_category'] == 1){
                $addYear = 0;
                $collectIndex = $collectNumber;
                if($collectIndex >= count($dateArray)){
                    $collectIndex = 0;
                }
                $nextrenewaldate = date('Y-m-d', strtotime($dateArray[$collectIndex].".".(date("Y", strtotime($nextrenewaldatevalue)))));
                if(strtotime($nextrenewaldate) <= strtotime($nextrenewaldatevalue)) {
                    $nextrenewaldate = date('Y-m-d', strtotime("+1 year", strtotime($nextrenewaldate)));
                }
                $nextrenewaldate2 = date('d.m.Y', strtotime($nextrenewaldate)-24*60*60);
            }
    		$nextrenewaldate2raw = date('Y-m-d', strtotime($nextrenewaldate2));

    		$lastdate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('y',$nextdate2)));

    		if($nextrenewaldatevalue != '0000-00-00' && ($v_row['stoppedDate'] == '0000-00-00' || $v_row['stoppedDate'] == null || strtotime($v_row['stoppedDate']) > strtotime($nextrenewaldatevalue)))
    		{
    			$nextrenewaldatevalueraw = $nextrenewaldatevalue;
    			$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));

				$lines = get_sl_rows($v_row, $nextrenewaldatevalue, $nextrenewaldate2, $batch_renewal_accountconfig);

                $noError = true;
                if($v_row['useMainContactAsContactperson']){
    				$s_sql = "SELECT contactperson.* FROM contactperson
    				WHERE contactperson.customerId = ? AND contactperson.mainContact = 1";
    				$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
    				$contactPerson = $o_query ? $o_query->row_array() : array();
    			} else {
                    $s_sql = "SELECT contactperson.* FROM contactperson_role_conn
                    LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
                    WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
                    ORDER BY contactperson_role_conn.role DESC";
                    $o_query = $o_main->db->query($s_sql, array($v_row['id']));
                    $contactPerson = $o_query ? $o_query->row_array() : array();
                }

                $contactPersonId = $contactPerson['id'];

    			if(intval($v_row['ownercompany_id']) == 0){
    				$noError = false;
    				$errorTxt .= "<li>".$formText_OwnerCompanyIsNotAttachedToTheSubscription_output . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
    			}

                if($v_row['subscription_category'] == 2){
					require(__DIR__."/../../CrmPriceOverview/output/renewal_include.php");
				}
				if($v_row['script_for_generating_order'] != ""){
					require(__DIR__ . "/../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php");
				}
                if(!$v_row['activate_specified_invoicing']){
        			if(count($lines) == 0){
        				$noError = false;
                        $errorTxt .= "<li>".$formText_CannotRenewSubscriptionWithoutSubscriptionlines_output  . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") </li>";
        			} else {
        				$totalSubscriptionPrice = 0;
        				foreach($lines as $line) {
                            $pricePerPiece = $line['pricePerPiece'];
                            if($line['articleOrIndividualPrice']) {
                                $s_sql = "SELECT * FROM article WHERE id = ?";
                                $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
                                $article = $o_query ? $o_query->row_array() : array();
                                $pricePerPiece = $article['price'];
                            }
                            if(!$modifierActivated){
                                if($v_row['subscription_category'] == 1){
                                    $totalAmount = 1 * $line['amount'];
                                } else if($v_row['override_periods'] > 0) {
                                    $totalAmount = $v_row['override_periods']*$line['amount'];
                                } else {
                                    $totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
                                }
                            } else {
                                $totalAmount = $line['amount']*$modifier;
                            }
                            if($line['fromPriceList']){
                                if(intval($v_row['periodUnit']) == 0){
                                    $pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
                                    $totalAmount = 1;
                                }
                            }
        					$totalSubscriptionPrice += round($pricePerPiece * $totalAmount * (1 - $line['discountPercent']/100), 2);
        				}

        				if($totalSubscriptionPrice == 0){
        					$noError = false;
        	                $errorTxt .= "<li>".$formText_CannotRenewSubscriptionWith0Price_output  . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")</li>";
        	            }
        			}
                }
                //get subsription extras
                $sql = "SELECT * FROM subscriptionmulti_invoicing_extra WHERE content_status < 2 AND subscriptionmulti_id = '".$o_main->db->escape_str($v_row['id'])."' AND (collectingorderId = 0 OR collectingorderId is null) AND month <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($nextrenewaldate2)))."'";
                $o_query = $o_main->db->query($sql);
                $invoicing_extras = $o_query ? $o_query->result_array() : array();
                foreach($invoicing_extras as $invoicing_extra) {
                    $invoicing_extra['extra'] = 1;
                    $lines[] = $invoicing_extra;
                }


                $s_sql = "SELECT * FROM customer WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
                $customer = $o_query ? $o_query->row_array() : array();
    			foreach($lines as $line){
    				//update bookingaccount and vatcode
    	            $s_sql = "SELECT * FROM article WHERE id = ?";
    	            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
    	            $article = $o_query ? $o_query->row_array() : array();

                    $vatCode = $article['VatCodeWithVat'];
					$bookaccountNr = $article['SalesAccountWithVat'];

                    if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']){
                        $vatCode = $line['vatCode'];
                        $bookaccountNr = $line['bookaccountCode'];
                    }

					if($vatCode == ""){
						$vatCode = $article_accountconfig['defaultVatCodeForArticle'];
					}
					if($bookaccountNr == ""){
						$bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
					}
    	            $vatPercent = '';

    	            $vatCodeError = false;
    	            $bookAccountError = false;
    	            $articleError = false;
    	            $projectError = false;
    	            $departmentError = false;

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
                    $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
                    $bookaccountItem = $o_query ? $o_query->row_array() : array();
                    if(!$bookaccountItem){
                        $noError = false;
                        $articleError = true;
                    }

    	            if($batch_renewal_basisconfig['activateCheckForProjectNr']) {
    	                $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
    	                $o_query = $o_main->db->query($s_sql, array($projectCode));
    	                $bookaccountItem = $o_query ? $o_query->row_array() : array();
    	                if(!$bookaccountItem){
    	                    $noError = false;
    	                    $projectError = true;
    	                }
    	            }
					if($batch_renewal_basisconfig['activateCheckForDepartmentCode']) {
		                $s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
		                $o_query = $o_main->db->query($s_sql, array($departmentCode));
		                $departmentItem = $o_query ? $o_query->row_array() : array();
		                if(!$departmentItem){
		                    $noError = false;
		                    $departmentError = true;
		                }
		            }
    	            if($vatCodeError || $bookAccountError || $articleError || $projectError || $departmentError){
                        if($vatCodeError){
                            $errorTxt .= "<li>".$formText_VatCodeDoesntExist_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
                        }
                        if($bookAccountError){
                            $errorTxt .= "<li>".$formText_BookAccountDoesntExist_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
                        }
                        if($articleError){
                            $errorTxt .= "<li>".$formText_InvalidArticleNumber_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
                        }
                        if($projectError){
                            $errorTxt .= "<li>".$formText_InvalidProjectFAccNumber_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
                        }
                        if($departmentError){
                            $errorTxt .= "<li>".$formText_InvalidDepartmentFAccNumber_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
                        }
    				}
    			}
    			if($noError){
                    if(count($lines) > 0){
                        $s_order_reference = $delivery_address_line_1 = $delivery_address_city =  $delivery_address_postal_code = $delivery_address_country = '';

        				$s_sql = "SELECT * FROM customer_subunit WHERE id = ?";
        				$o_query = $o_main->db->query($s_sql, array($v_row['customer_subunit_id']));
        				$subunit = $o_query ? $o_query->row_array() : array();
        				if($v_row['address_source'] == 0 && $subunit){
                            $delivery_address_line_1 = $subunit['delivery_address_line_1'];
                            $delivery_address_city = $subunit['delivery_address_city'];
                            $delivery_address_postal_code = $subunit['delivery_address_postal_code'];
                            $delivery_address_country = $subunit['delivery_address_country'];
                        } else {
                            $delivery_address_line_1 = $v_row['delivery_address_line_1'];
                            $delivery_address_city = $v_row['delivery_address_city'];
                            $delivery_address_postal_code = $v_row['delivery_address_postal_code'];
                            $delivery_address_country = $v_row['delivery_address_country'];
    					}
                        $s_sql = "SELECT * FROM customer WHERE id = ?";
        				$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        				$customer = $o_query ? $o_query->row_array() : array();
                        if($v_row['reference'] == "") {
                            if($subunit) {
                                $s_order_reference = $subunit['reference'];
                            } else {
                                $s_order_reference = $customer['defaultInvoiceReference'];
                            }
                        } else {
                            if($v_row['reference'] != 'empty') {
                                $s_order_reference = $v_row['reference'];
                            }
                        }

                        if($v_customer_accountconfig['activate_subunits']) {
                            $s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? AND content_status < 1 ORDER BY customer_subunit.id ASC";
                            $o_query = $o_main->db->query($s_sql, array($customer['id']));
                            $subunits = $o_query ? $o_query->result_array() : array();
                            if(count($subunits) > 0){
                                if($subunit['putSubunitAddressInDeliveryAddress']) {
                                    $delivery_address_line_1 = $subunit['name'];
                                    $delivery_address_line_2 = $subunit['delivery_address_line_1']." ".$subunit['delivery_address_line_2'];
                                    $delivery_address_city = $subunit['delivery_address_city'];
                                    $delivery_address_postal_code = $subunit['delivery_address_postal_code'];
                                    $delivery_address_country = $subunit['delivery_address_country'];
                                }
                            }
                        }

                        $files = array();
                        $lettersAdded = array();
    					if($batch_renewal_accountconfig['activatePriceAdjustmentLetter']){
                            $s_sql = "SELECT * FROM subscriptionmulti_cpi_letter WHERE subscriptionmulti_id = '".$v_row['id'] ."' AND date >= '".date("Y-m-d", strtotime($nextrenewaldatevalue))."' AND date <= '".date("Y-m-d", strtotime($nextrenewaldate2))."' AND (added_to_order is null OR added_to_order = 0)";
                            $o_query = $o_main->db->query($s_sql);
                            $letters = ($o_query ? $o_query->result_array() : array());
                            foreach($letters as $letter){
                                if(file_exists(__DIR__."/../../../".$letter['cpi_adjustment_letter'])){
                                    $fileArray = array();
                                    $fileArray[0] = basename($letter['cpi_adjustment_letter']);
                                    $fileArray[1] = array($letter['cpi_adjustment_letter']);
                                    array_push($files, $fileArray);
                                }
                                $lettersAdded[] = $letter['id'];
                            }
                            $files = duplicate_images($o_main, json_encode($files));

                        }
                        $seperatedInvoice = 0;
                        // if($v_row['reference'] != "" || $v_row['delivery_address_line_1'] != "" || ($v_row['delivery_date'] != "0000-00-00" && $v_row['delivery_date'] != "")) {
                        //     $seperatedInvoice = 1;
                        // }
        				//create collecting order
        				$sql = "INSERT INTO customer_collectingorder SET
        	            created = now(),
        	            createdBy = '".$variables->loggID."',
                        moduleID = '".$moduleID."',
        	            date = now(),
        	            customerId = '".$v_row['customerId']."',
        	            accountingProjectCode = '".$projectCode."',
        	            contactpersonId = '".$contactPersonId."',
        	            ownercompanyId = ?,
        	            seperateInvoiceFromSubscription = ?,
                        reference = ?,
                        delivery_address_line_1 = ?,
                        delivery_address_city = ?,
                        delivery_address_postal_code = ?,
                        delivery_address_country = ?,
                        seperatedInvoice = ?,
                        department_for_accounting_code = ?,
        	            approvedForBatchinvoicing = 1,
                        files_attached_to_invoice = '".$o_main->db->escape_str($files)."'";
        	            $o_query = $o_main->db->query($sql, array($v_row['ownercompany_id'], $seperateInvoiceFromSubscription, $s_order_reference, $delivery_address_line_1,
                        $delivery_address_city, $delivery_address_postal_code, $delivery_address_country, $seperatedInvoice, $departmentCode));
        				$collectingorderId = $o_main->db->insert_id();
        				if(intval($collectingorderId) == 0){
        					$noError = false;
        					$errorTxt .= "<li>".$formText_CollectingOrderWasNotCreatedFor_output . " ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
        				}
        				if(!$noError){
        	                $errorTxt .= "<li>".$formText_SubscriptionWasNotRenewed_output . " ".$formText_For_output." ".$customer['name']." - ".$v_row['subscriptionName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
        					continue;
        				}
                    }
    				// Update renewal dates
    				$s_sql = "UPDATE subscriptionmulti SET nextRenewalDate = ?, lastRenewalDate = ? WHERE id = ?";
    				$o_main->db->query($s_sql, array($nextrenewaldate, $lastdate, $v_row["id"]));

                    if(count($lettersAdded) > 0){
                        foreach($lettersAdded as $letterId){
            				$s_sql = "UPDATE subscriptionmulti_cpi_letter SET added_to_order = ? WHERE id = ?";
            				$o_main->db->query($s_sql, array($collectingorderId, $letterId));
                        }
                    }

    				$subscribtionId = $v_row["id"];
    				foreach($lines as $line){
                        $not_full_order = 0;
                        $pricePerPiece = $line['pricePerPiece'];
                        if($line['articleOrIndividualPrice']) {
                            $s_sql = "SELECT * FROM article WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
                            $article = $o_query ? $o_query->row_array() : array();
                            $pricePerPiece = $article['price'];
                            if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
    							$line['articleName'] = $article['name'];
    						}
                        }
                        if($modifierActivated){
                            $not_full_order = 1;
                        }
                        if(!$modifierActivated){
                            if($v_row['subscription_category'] == 1){
                                $totalAmount = 1 * $line['amount'];
                            } else if($v_row['override_periods'] > 0) {
                                $totalAmount = $v_row['override_periods']*$line['amount'];
                            } else {
                                $totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
                            }
                        } else {
                            $totalAmount = $line['amount']*$modifier;
                        }

                        if($line['fromPriceList']){
                            if(intval($v_row['periodUnit']) == 0){
                                $pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
                                $totalAmount = 1;
                            }
                        }
                        $renewalMonth = date("n", strtotime($nextrenewaldatevalue));
    					$adjustmentPercent = 100;
    					switch($renewalMonth){
    						case 1:
    							$adjustmentPercent = 100-$v_row['january'];
    						break;
    						case 2:
    							$adjustmentPercent = 100-$v_row['february'];
    						break;
    						case 3:
    							$adjustmentPercent = 100-$v_row['march'];
    						break;
    						case 4:
    							$adjustmentPercent = 100-$v_row['april'];
    						break;
    						case 5:
    							$adjustmentPercent = 100-$v_row['may'];
    						break;
    						case 6:
    							$adjustmentPercent = 100-$v_row['june'];
    						break;
    						case 7:
    							$adjustmentPercent = 100-$v_row['july'];
    						break;
    						case 8:
    							$adjustmentPercent = 100-$v_row['august'];
    						break;
    						case 9:
    							$adjustmentPercent = 100-$v_row['september'];
    						break;
    						case 10:
    							$adjustmentPercent = 100-$v_row['october'];
    						break;
    						case 11:
    							$adjustmentPercent = 100-$v_row['november'];
    						break;
    						case 12:
    							$adjustmentPercent = 100-$v_row['december'];
    						break;
    					}
    					if($adjustmentPercent != 100) {
    						$pricePerPiece = $pricePerPiece*($adjustmentPercent)/100;
    					}
    					//update bookingaccount and vatcode
    		            $s_sql = "SELECT * FROM article WHERE id = ?";
    		            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
    		            $article = $o_query ? $o_query->row_array() : array();

    		            $s_sql = "SELECT * FROM customer WHERE id = ?";
    		            $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
    		            $customer = $o_query ? $o_query->row_array() : array();

                        $vatCode = $article['VatCodeWithVat'];
    					$bookaccountNr = $article['SalesAccountWithVat'];

                        if($v_customer_accountconfig['subscription_activate_edit_bookaccount_and_vatcode']){
                            $vatCode = $line['vatCode'];
                            $bookaccountNr = $line['bookaccountCode'];
                        }
    					if($vatCode == ""){
    						$vatCode = $article_accountconfig['defaultVatCodeForArticle'];
    					}
    					if($bookaccountNr == ""){
    						$bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
    					}
    		            $vatPercent = '';
                        $prepaidCommonCost = 0;
                        if($article['system_article_type'] == 1){
                            $prepaidCommonCost = 1;
                        }
    		            // $taxFreeSale = $customer['taxFreeSale'];
    	                //
    	                // // Building query
    	                // $q = "SELECT a.VatCodeWithVat vatcodeId, ba.accountNr bookAccountNr FROM article a";
    	                // if ($taxFreeSale) {
    	                //     $q .= " LEFT JOIN bookaccount ba ON a.SalesAccountWithoutVat = ba.accountNr";
    	                // } else {
    	                //     $q .= " LEFT JOIN bookaccount ba ON a.SalesAccountWithVat = ba.accountNr";
    	                // }
    	                // $q .= " WHERE a.id = ?";
    	                //
    	                // $o_query = $o_main->db->query($q, array($article['id']));
    	                // if($o_query && $o_query->num_rows()>0){
    	                //     $rowInfo = $o_query->row_array();
    	                //     $bookaccountNr = $rowInfo['bookAccountNr'];
    	                //     if($taxFreeSale) {
    	                //         $vatCode = $article_accountconfig['vatcode_default_when_without_vat'];
    	                //     } else {
    	                //         $vatCode = $rowInfo['vatcodeId'];
    	                //     }
    	                // }
    	                // if($article['forceVat']){
    	                //     $vatCode = $article['VatCodeWithVat'];
    	                // }
    	                $vatPercent = 0;

    	    			$periodization = 0;
    	    			$periodizationMonths = "";
    	    			if(intval($v_row['defaultPeriodising']) > 0){
    	    				$periodization = intval($v_row['defaultPeriodising']);
    	    				$start    = (new DateTime($nextrenewaldatevalueraw))->modify('first day of this month');
    						$end      = (new DateTime($nextrenewaldate2raw))->modify('first day of next month');
    						$interval = DateInterval::createFromDateString('1 month');
    						$period   = new DatePeriod($start, $interval, $end);

    						foreach ($period as $dt) {
    							$periodizationMonths .= $dt->format("mY") . ",";
    						}
    						if(strlen($periodizationMonths) > 0){
    							$periodizationMonths = substr($periodizationMonths, 0, -1);
    						}
    	    			}
                        if($line['specified_invoicing']) {
                            if($line['combined_specified']) {
                                $date_string = " (".$line['start_date']." - ".$line['end_date'].")";
                            } else {
                                $date_string = " ".$line['workDate'];
                            }
                        } else {
                            if($v_row['subscription_category'] == 1){
                                $collectString = "";
                                if($totalCollectNumber > 1){
                                    $collectString = "(".$formText_PartInvoice_output." ".$collectNumber. " ".$formText_Of_output ." ".$totalCollectNumber.")";
                                }
                                $date_string = " ".$collectString;
                            } else {
                                if($line['periodModifier'] > 0){
                                    if(intval($v_row['periodUnit']) == 0){
                                        $nextrenewaldatevalue_to = date("t.m.Y", strtotime("+".$line['periodModifier']." months", strtotime($nextrenewaldatevalue)));
                                        $date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldatevalue_to.")";
                                    } else {
                                        $nextrenewaldatevalue_to = date("t.m.Y", strtotime("+".((12*$line['periodModifier'])-1)." months", strtotime($nextrenewaldatevalue)));
                                        $date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldatevalue_to.")";
                                    }
                                    $nextrenewaldatevalueraw = date("Y-m-d", strtotime($nextrenewaldatevalue));
                                    $nextrenewaldate2raw = date("Y-m-d", strtotime($nextrenewaldatevalue_to));
                                } else {
                                    $date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")";
                                }
                            }
                        }

                        if($line['extra']){
                            $date_string = " (".date("m.Y", strtotime($line['month'])).")";
                        }
                        $subscriptionNameString = "";
                        if($v_row['placeSubscriptionNameInInvoiceLine']) {
                            $subscriptionNameString = $v_row['subscriptionName'] . " - ";
                        }
                        $articleName = $subscriptionNameString.$line['articleName'].$date_string;

    					$sql = $o_main->db->query("INSERT INTO orders SET moduleID = ?, createdBy = ?, created = NOW(),  articleNumber = ?, articleName = ?, amount = ?, pricePerPiece = ?, discountPercent = ?, priceTotal = ?,  status = 4, ownercompany_id = ?, invoiceDateSettingFromSubscription = ?, subscribtionId = ?, contactPerson = ?, projectCode = ?, bookaccountNr = ?, vatCode = ?, vatPercent = ?, prepaidCommonCost = ?, dateFrom = ?, dateTo = ?, periodization= ?, periodizationMonths = ?,  collectingorderId = ?, not_full_order = ?", array(0, $variables->loggID, $line['articleNumber'], $articleName, $totalAmount, $pricePerPiece, $line['discountPercent'], round($pricePerPiece * $totalAmount * (1 - $line['discountPercent']/100), 2), $v_row['ownercompany_id'], $invoiceDateSettingFromSubscription, $subscribtionId, $contactPersonId, $projectCode, $bookaccountNr, $vatCode, $vatPercent, $prepaidCommonCost, $nextrenewaldatevalueraw, $nextrenewaldate2raw, $periodization, $periodizationMonths, $collectingorderId, $not_full_order));

                        if($line['extra']) {
                            $sql = $o_main->db->query("UPDATE subscriptionmulti_invoicing_extra SET updated = NOW(), collectingorderId = ? WHERE id = ?", array($collectingorderId, $line['id']));
                        }
    				}
    				$l_count++;
    			}
    		}
        }
	}
}
if($errorTxt != ""){
    ?>
    <div class="item-error">
        <div class="alert alert-danger">
            <ul>
            <?php echo $errorTxt;?>
            </ul>
        </div>
    </div>
    <?php
}
?><h4><?php echo $formText_TotalSubscribtionsRenewed_Output.": ".$l_count;?></h4>
<a class="btn btn-default optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&ownercompany=".$_POST['ownercompany']."&customerselfdefinedlist_filter=".$_POST['customerselfdefinedlist_filter'];?>"><?php echo $formText_GoBack_Output;?></a>
