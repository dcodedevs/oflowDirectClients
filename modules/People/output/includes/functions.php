<?php
function get_customer_list_count2($o_main, $filter, $filters){
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list_count($o_main, $filter, $filters){
    $filters = array();
    return get_customer_list($o_main, $filter, $filters, 0, 0);
}
function get_customer_list($o_main, $filter, $filters, $page=1, $perPage=100, $customer_id = null) {
    global $variables;

	$people_contactperson_type = 2;
    $sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($sql);
    $accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
    if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
    	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
    }
	$o_query = $o_main->db->get('accountinfo');
	$accountinfo = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo['contactperson_type_to_use_in_people']) > 0)
	{
		$people_contactperson_type = $accountinfo['contactperson_type_to_use_in_people'];
	}

    $sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($sql);
    $accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();

    $s_sql = "select * from people_accountconfig";
    $o_query = $o_main->db->query($s_sql);
    $v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

    $sql = "SELECT * FROM people_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($sql);
    $v_employee_basisconfig = $o_query ? $o_query->row_array() : array();

    foreach($v_employee_accountconfig as $key=>$value){
        if($value > 0){
            $v_employee_basisconfig[$key] = ($value - 1);
        }
    }


    $offset = ($page-1)*$perPage;
    if($offset < 0){
        $offset = 0;
    }
    $pager = " LIMIT ".$perPage ." OFFSET ".$offset;
    $list = array();

    $sql_join = "";
    $sql_where = "";
    $sql_select = "";
    $sql_group = " GROUP BY p.id";
    if($page == 0 && $perPage == 0){
        $pager = "";
    }

    foreach($filters as $filterName=>$filterValue){
        switch($filterName){
            // case "department_filter":
            //     if($filterValue > 0){
            //         $sql_join .= "";
            //         $sql_where .= " AND p.projectLeader = ".$o_main->db->escape($filterValue);
            //     }
            // break;
            case "search_filter":
                if(is_array($filterValue)){
                     switch($filterValue[0]){
                        default:
                            $filterValue = $filterValue[1];

                            $sql_join .= "";
                            $sql_where .= " AND (CONCAT_WS(' ', IF(LENGTH(p.name),p.name,NULL), IF(LENGTH(p.middlename),p.middlename,NULL), IF(LENGTH(p.lastname),p.lastname,NULL)) LIKE '%".$filterValue."%'
							OR p.mobile LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.email LIKE '%".$o_main->db->escape_like_str($filterValue)."%'
							OR p.external_employee_id LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.title LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";

                        break;
                    }
                } else {
                    $sql_join .= "";
                    $sql_where .= " AND (CONCAT_WS(' ', IF(LENGTH(p.name),p.name,NULL), IF(LENGTH(p.middlename),p.middlename,NULL), IF(LENGTH(p.lastname),p.lastname,NULL)) LIKE '%".$filterValue."%'
					OR p.mobile LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.email LIKE '%".$o_main->db->escape_like_str($filterValue)."%'
					OR p.external_employee_id LIKE '%".$o_main->db->escape_like_str($filterValue)."%' OR p.title LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";
                }
            break;
        }
    }
    if($filter == "active"){
        $sql_where .= " AND p.content_status < 1";
    }  else if ($filter == "inactive") {
        $sql_where .= " AND p.content_status = 2 AND p.deactivated = 1";
    }  else if ($filter == "deleted") {
        $sql_where .= " AND p.content_status = 2 AND (p.deactivated IS NULL OR p.deactivated = 0)";
    } else {
        $sql_where .= " AND p.content_status < 1";
    }

    // if($v_employee_accountconfig['activateFilterByTags']) {
    //     $sql_select .= ", pc.crm_contactperson_id, pc.notVisibleInMemberOverview ";
    //     $sql_join .= " LEFT OUTER JOIN people_crm_contactperson_connection pc ON pc.people_id = p.id";
    //     $sql_group = " GROUP BY pc.id";
    // }
    if($v_employee_basisconfig['filter_by_subscription'] == 1) {
        $sql_join .= " LEFT OUTER JOIN customer c ON c.id = p.customerId
        LEFT OUTER JOIN subscriptionmulti s ON s.customerId = c.id";

        $sql_where .= " AND s.startDate <= NOW() AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null or s.stoppedDate > NOW())";
    }
    if($v_employee_basisconfig['show_only_persons_marked_to_show_in_intranet'] == 1){
        $sql_where .= " AND p.show_in_intranet = 1";
    }
    if($people_contactperson_type != 2){
        $sql_where .= " AND (p.notVisibleInMemberOverview = 0 OR p.notVisibleInMemberOverview is NULL)";
    }

    if((!isset($variables->useradmin) || 0 == $variables->useradmin) && $v_employee_accountconfig['activateFilterByTags'])
	{
		$v_property_ids = $v_property_group_ids = array();
		$s_sql = "SELECT cp.* FROM contactperson AS cp
		JOIN customer AS cus ON cus.id = cp.customerId AND cus.content_status < 2
		LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())

		LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
		LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
		AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())

		WHERE cp.email = '".$o_main->db->escape_str($variables->loggID)."' AND (
		(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR
		(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
		cp.intranet_membership_subscription_type = 2
		)";
		$o_query = $o_main->db->query($s_sql);
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_contactperson)
		{
			$v_properties = array();
			if(intval($v_contactperson['intranet_membership_type']) == 0)
			{
				$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
				JOIN intranet_membership_customer_connection AS im_cus ON im_cus.membership_id = im.id
				LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
				LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
				WHERE im_cus.customer_id = '".$o_main->db->escape_str($v_contactperson['customerId'])."'";
				$o_find = $o_main->db->query($s_sql);
				$v_properties = $o_find ? $o_find->result_array() : array();

			} else if($v_contactperson['intranet_membership_type'] == 1)
			{
				$s_sql = "SELECT imao.object_id, pgc.property_id FROM intranet_membership AS im
				JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.membership_id = im.id
				LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cp.membership_id
				LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0
				WHERE im_cp.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
				$o_find = $o_main->db->query($s_sql);
				$v_properties = $o_find ? $o_find->result_array() : array();

			}
			foreach($v_properties as $v_item)
			{
				if(0 < $v_item['object_id'] && !in_array($v_item['object_id'], $v_property_ids))
				{
					array_push($v_property_ids, $v_item['object_id']);
				}
				if(0 < $v_item['property_id'] && !in_array($v_item['property_id'], $v_property_group_ids))
				{
					array_push($v_property_group_ids, $v_item['property_id']);
				}
			}
		}
		//echo 'PROP: '.implode(', ', $v_property_ids).'<br>';
		//echo 'GROUP_PROP: '.implode(', ', $v_property_group_ids).'<br>';
		$s_sql_a = '';
		$s_sql_b = '';
		if(0<count($v_property_ids))
		{
			$s_sql_a = "imao.object_id IN (".implode(', ', $v_property_ids).")";
			$s_sql_b = "imao2.object_id IN (".implode(', ', $v_property_ids).")";
		}
		if(0<count($v_property_group_ids))
		{
			$s_sql_a .= (''!=$s_sql_a?" OR ":'')."pgc.property_id IN (".implode(', ', $v_property_group_ids).")";
			$s_sql_b .= (''!=$s_sql_b?" OR ":'')."pgc2.property_id IN (".implode(', ', $v_property_group_ids).")";
		}

		$sql_join .= " JOIN contactperson AS cp ON cp.id = p.id
		JOIN customer AS cus ON cus.id = cp.customerId AND cus.content_status < 2

		LEFT OUTER JOIN subscriptionmulti AS sm ON sm.customerId = cp.customerId AND sm.startDate <= CURDATE() AND (sm.stoppedDate = '0000-00-00' OR sm.stoppedDate is null OR sm.stoppedDate >= CURDATE())

		LEFT OUTER JOIN contactperson_subscription_connection AS csc ON csc.contactperson_id = cp.id
		LEFT OUTER JOIN subscriptionmulti AS sm2 ON csc.subscriptionmulti_id = sm2.id AND sm2.startDate <= CURDATE()
		AND (sm2.stoppedDate = '0000-00-00' OR sm2.stoppedDate is null OR sm2.stoppedDate >= CURDATE())

		LEFT OUTER JOIN intranet_membership_customer_connection AS im_cus ON im_cus.customer_id = cp.customerId
		LEFT OUTER JOIN intranet_membership_attached_object AS imao ON imao.membership_id = im_cus.membership_id
		LEFT OUTER JOIN property_group_connection AS pgc ON pgc.property_group_id = imao.objectgroup_id AND imao.object_id = 0

		LEFT OUTER JOIN intranet_membership_contactperson_connection AS im_cp ON im_cp.contactperson_id = cp.id
		LEFT OUTER JOIN intranet_membership_attached_object AS imao2 ON imao2.membership_id = im_cp.membership_id
		LEFT OUTER JOIN property_group_connection AS pgc2 ON pgc2.property_group_id = imao2.objectgroup_id AND imao2.object_id = 0";
				$sql_where .= " AND (
		(IFNULL(cp.intranet_membership_type, 0) = 0 AND (".$s_sql_a.") AND im_cus.id IS NOT NULL) OR
		(cp.intranet_membership_type = 1 AND (".$s_sql_b.") AND im_cp.id IS NOT NULL)
		)
		AND (
			(IFNULL(cp.intranet_membership_subscription_type, 0) = 0 AND sm.id IS NOT NULL) OR
			(cp.intranet_membership_subscription_type = 1 AND sm2.id IS NOT NULL AND csc.id IS NOT NULL) OR
			cp.intranet_membership_subscription_type = 2
		)";
	}

	$sql = "SELECT p.*".$sql_select."
             FROM contactperson p
            ".$sql_join."
            WHERE (p.id is not null) AND p.type = '".$people_contactperson_type."'".$sql_where;
            //var_dump($sql);echo '<br><br>';
	if($customer_id != null){
        $list = array();
        $sql .= $sql_group."  ORDER BY p.name ASC".$pager;

        $o_query = $o_main->db->query($sql);
        if($o_query && $o_query->num_rows()>0){
            $customerList = $o_query->result_array();
            foreach($customerList as $index=>$customer) {
                if($customer['id'] == $customer_id) {
                    $currentCustomerIndex = $index;
                    break;
                }
            }
            array_push($list, $customerList[$currentCustomerIndex-1]);
            array_push($list, $customerList[$currentCustomerIndex]);
            array_push($list, $customerList[$currentCustomerIndex+1]);
        }

        return $list;
    } else {
        $sql .= $sql_group;
        if($page == 0 && $perPage == 0){
            $rowCount = 0;
            $o_query = $o_main->db->query($sql);
            if($o_query){
                $rowCount = $o_query->num_rows();
            }
            return $rowCount;
        } else {
            $sql .= " ORDER BY p.name ASC".$pager;
            $f_check_sql = $sql;

            $o_query = $o_main->db->query($sql);
            if($o_query && $o_query->num_rows()>0){
                $list = $o_query->result_array();
            }
            return $list;
        }
    }
}

function get_salary_list($peopleData, $v_employee_accountconfig, $canEdit, $canEditAdmin, $noactionColumn = false){
    global $o_main;
    global $formText_StandardRate_Output;
    global $formText_StandartDescriptionText_output;
    global $formText_IndividualRate_Output;
    global $formText_IndividualDescriptionText_output;
    global $formText_SendingInvoice_output;
    global $formText_SendingInvoiceDescriptionText_output;
    global $formText_From_output;
    global $formText_HourlyRate_Output;
    global $formText_DeleteSalary_Output;
    global $formText_DefaultSalaryForRepeatingOrder_output;
    global $formText_DefaultSalaryForProject_output;
    global $formText_FixedSalary_output;
    global $formText_FixedSalaryDescriptionText_output;
    global $formText_Specified_output;
    global $module;
    $return = array();
    ob_start();
    ?>
    <div class="salaryList <?php if($noactionColumn) echo 'noactionColumn';?>">
        <?php
        $workLeaderSql = "SELECT *, peoplesalary.* FROM peoplesalary JOIN contactperson ON contactperson.id = peoplesalary.peopleId
            WHERE peoplesalary.peopleId = ?";
        $findWorkLeaders = $o_main->db->query($workLeaderSql, array($peopleData['id']));
        $workLeaders = array();
        if($findWorkLeaders && $findWorkLeaders->num_rows() > 0)
        foreach($findWorkLeaders->result() AS $workLeader) {
            array_push($workLeaders, $workLeader);
        }
        foreach($workLeaders as $workLeader) { ?>
            <div class="txt-row">
                <div class="txt-value">
                    <div class="worker-block">
                        <div class="worker-row">
                            <div class="worker-name">
                                <?php
                                if($workLeader->stdOrIndividualRate == 0) {
                                    if($workLeader->standardwagerate_group_id > 0){
                                        echo $formText_Specified_output;
                                    } else {
                                        echo $formText_StandardRate_Output;
                                    }
                                } else if ($workLeader->stdOrIndividualRate == 1){
                                    echo $formText_IndividualRate_Output;
                                } else if ($workLeader->stdOrIndividualRate == 2){
                                    echo $formText_SendingInvoice_output;
                                } else if ($workLeader->stdOrIndividualRate == 3){
                                    echo $formText_FixedSalary_output;
                                }
                                if($workLeader->stdOrIndividualRate != 0 || ($workLeader->stdOrIndividualRate == 0 && $workLeader->standardwagerate_group_id == 0)){
                                    ?>
                                    <span class="hoverInit"><i class="fas fa-info-circle"></i><span class="hoverSpan">
                                        <?php
                                        if($workLeader->stdOrIndividualRate == 0) {
                                            if($workLeader->standardwagerate_group_id == 0){
                                                echo $formText_StandartDescriptionText_output;
                                            }
                                        } else if ($workLeader->stdOrIndividualRate == 1){
                                            echo $formText_IndividualDescriptionText_output;
                                        } else if ($workLeader->stdOrIndividualRate == 2){
                                            echo $formText_SendingInvoiceDescriptionText_output;
                                        } else if ($workLeader->stdOrIndividualRate == 3){
                                            echo $formText_FixedSalaryDescriptionText_output;
                                        }
                                        ?>
                                    </span></span>
                                <?php } ?>

                            </div>
                            <div class="worker-rate">
                                <?php
                                if($workLeader->stdOrIndividualRate == 0) {
                                    if($workLeader->standardwagerate_group_id == 0){
                                        $s_sql = "SELECT * FROM standardwagerate_group WHERE default_group =  1 ORDER BY id DESC";
                                        $o_query = $o_main->db->query($s_sql);
                                        $defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
                                        if(!$defaultStandardWageRateGroup){
                                            $s_sql = "SELECT * FROM standardwagerate_group ORDER BY id ASC";
                                            $o_query = $o_main->db->query($s_sql);
                                            $defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
                                        }
                                    } else {
                                        $s_sql = "SELECT * FROM standardwagerate_group WHERE id = ? ORDER BY id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($workLeader->standardwagerate_group_id));
                                        $defaultStandardWageRateGroup = ($o_query ? $o_query->row_array() : array());
                                    }
                                    $s_sql = "SELECT * FROM standardwagerate WHERE default_salary_repeatingorder =  1 AND standartwagerate_group_id = ? ORDER BY id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($defaultStandardWageRateGroup['id']));
                                    $wageData = ($o_query ? $o_query->row() : array());
                                    if(!$wageData){
                                        $s_sql = "SELECT * FROM standardwagerate WHERE standartwagerate_group_id = ? ORDER BY id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($defaultStandardWageRateGroup['id']));
                                        $wageData = ($o_query ? $o_query->row() : array());
                                    }

                                    if($wageData) {
                                        $seniorityYears = 0;
                                        if($peopleData['seniority_salary'] == 1){
                                        	$seniorityStartDate = $peopleData['seniorityStartDate'];
                                        	if($seniorityStartDate != "" && $seniorityStartDate != "0000-00-00") {
                                        		$d1 = new DateTime($date);
                                        		$d2 = new DateTime($seniorityStartDate);
                                        		$diff = $d2->diff($d1);

                                        		$seniorityYears = $diff->y;
                                        	}
                                        } else if($peopleData['seniority_salary'] == 2){
                                            $seniorityYears = $peopleData['seniority_years'];
                                        }

                                        $s_sql = "SELECT * FROM standardwagerateinperiod_seniority
                                            WHERE standardwagerateinperiod_seniority.standardwagerate_id = ? AND standardwagerateinperiod_seniority.seniority_years <= ?
                                            ORDER BY standardwagerateinperiod_seniority.seniority_years DESC";
                                        $o_query = $o_main->db->query($s_sql, array($wageData->id, $seniorityYears));
                                        $wageDataPeriodSeniority = $o_query ? $o_query->row_array() : array();
                                        echo $wageData->name." ".number_format($wageDataPeriodSeniority['amount'], 2, ",", "");
                                    }
                                } else if ($workLeader->stdOrIndividualRate == 1){
                                    echo $formText_HourlyRate_Output." ".number_format($workLeader->rate, 2, ",", "");
                                } else  if($workLeader->stdOrIndividualRate == 2){
                                    echo $formText_HourlyRate_Output." ".number_format($workLeader->hourlyRate, 2, ",", "");
                                }
                                ?>
                            </div>
                            <div class="worker-action">
                                <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                    <?php if($canEdit || $canEditAdmin) { ?>
                                        <span class="output-btn small glyphicon glyphicon-pencil fw_delete_edit_icon_color output-edit-salary"  data-id="<?php echo $workLeader->id; ?>" data-employeeid="<?php echo $peopleData['id'];?>"></span>
                                        <?php /*
                                        <span class="output-btn small glyphicon glyphicon-trash fw_delete_edit_icon_color output-delete-item"  data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_salary&cid=".$workLeader->id;?>" data-delete-msg="<?php echo $formText_DeleteSalary_Output;?>?"></span>
                                        */?>
                                    <?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                        <?php /*
                        <div class="worker-default">
                            <?php if(!$v_employee_accountconfig['duplicate_module']) { ?>
                                <?php if($canEdit || $canEditAdmin) { ?>
                                    <input type="checkbox" autocomplete="off" <?php if($peopleData['default_salary_repeatingorder'] == $workLeader->id) { echo 'checked'; }?> class="default_salary_repeatingorder" id="repeatingordersalary<?php echo $workLeader->id?>" value="<?php echo $workLeader->id?>"/> <label for="repeatingordersalary<?php echo $workLeader->id?>"><?php echo $formText_DefaultSalaryForRepeatingOrder_output;?></label>

                                    <input type="checkbox" autocomplete="off" <?php if($peopleData['default_salary_project'] == $workLeader->id) { echo 'checked'; }?> class="default_salary_project" value="<?php echo $workLeader->id?>" id="projectsalary<?php echo $workLeader->id?>"  /> <label for="projectsalary<?php echo $workLeader->id?>"><?php echo $formText_DefaultSalaryForProject_output;?></label>

                                <?php } ?>
                            <?php } ?>
                        </div>*/ ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    $return['salary_count'] = count($workLeaders);
    $return['output'] = ob_get_contents();
    ob_end_clean();
    return $return;
}
