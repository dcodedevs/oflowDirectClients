<?php
if(!function_exists("get_processed_customers")){
    function get_processed_customers($variables){
        global $o_main;
        global $_POST;
        global $_GET;
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

        $o_query = $o_main->db->get('ownercompany_accountconfig');
        $ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

        $o_query = $o_main->db->get('salaryreporting');
        $salaryreporting = $o_query ? $o_query->row_array() : array();
        $completedRepeatingOrderDate = date("t.m.Y", strtotime("-1 month", strtotime($salaryreporting['active_salary_period'])));

        if(isset($_GET['filter_date_from'])){ $filter_date_from = $_GET['filter_date_from']; } else { $filter_date_from = date("01.m.Y", strtotime("-1 month", time())); }
        if(isset($_GET['filter_date_to'])){ $filter_date_to = $_GET['filter_date_to']; } else { $filter_date_to = date("t.m.Y", strtotime("-1 month", time())); }
        $viewType = 0;
        if(isset($_GET['viewType'])) { $viewType = $_GET['viewType']; }
        if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = 1;}
        if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = 'customername';}

        $s_sql = "SELECT * FROM repeatingorder_accountconfig WHERE content_status < 2";
        $o_query = $o_main->db->query($s_sql);
        $repeatingorder_accountconfig = ($o_query ? $o_query->row_array() : array());

        if(strtotime($completedRepeatingOrderDate) >= strtotime($filter_date_from)){
        	if(strtotime($completedRepeatingOrderDate) >= strtotime($filter_date_to)){
        		$sub_filter_date_to = $filter_date_to;
        	} else {
        		$sub_filter_date_to = $completedRepeatingOrderDate;
        	}
        } else {
        	$sub_filter_date_to = "1990-00-00";
        }

        $sql_where = " AND (s.id is not null OR pp.id is not null)";
        if($_GET['project_type'] > 0){
        	if($_GET['project_type'] == 1) {
        		$sql_where = " AND s.id is not null";
        	} else if($_GET['project_type'] == 2) {
        		$sql_where = " AND pp.id is not null AND (p.type = 0 OR p.type is null)";
        	} else if($_GET['project_type'] == 3) {
        		$sql_where = " AND pp.id is not null AND p.type = 1";
        	}
        }
        if($_GET['project_leader'] > 0) {
        	$sql_where .= " AND (p.employeeId = '".$o_main->db->escape_str($_GET['project_leader'])."' OR wgl.employeeId = '".$o_main->db->escape_str($_GET['project_leader'])."')";
        }

        if($viewType == 1){
        	$sql = "SELECT s.id as repeatingOrderId,
        	CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, c.id, IFNULL(p.projectCode, s.projectId) as projectCode
        		 FROM customer c
        		LEFT OUTER JOIN subscriptionmulti s ON c.id = s.customerId AND s.startDate <= '".date("Y-m-d", strtotime($sub_filter_date_to))."' AND s.startDate <> '0000-00-00' AND s.content_status < 2
        		AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is not null AND s.stoppedDate < '".date("Y-m-d", strtotime($sub_filter_date_to))."'))
        		LEFT OUTER JOIN workgroupleader wgl ON wgl.workgroupId = s.workgroupId
        		LEFT OUTER JOIN project2 p ON p.customerId = c.id
        		LEFT OUTER JOIN project2_periods pp ON pp.projectId = p.id AND (((pp.status = 1 AND p.type = 1) OR ((p.type = 0 OR p.type is null) AND p.projectLeaderStatus = 1))  AND
        			(pp.completed_date is not null AND pp.completed_date <> '0000-00-00' AND pp.completed_date >= '".date("Y-m-d", strtotime($filter_date_from))."' AND pp.completed_date < '".date("Y-m-d", strtotime($filter_date_to))."')
        		)
        		WHERE c.content_status < 2".$sql_where."
        		GROUP BY projectCode, su.id
        		having projectCode >= 0";
        	$o_query = $o_main->db->query($sql);
        	$customers = $o_query ? $o_query->result_array() : array();
        } else {
        	$sql = "SELECT s.id as repeatingOrderId,
        	CONCAT_WS(' ', c.name, c.middlename, c.lastname) as customerName, c.id, s.startDate, s.stoppedDate, su.name as subunitName, su.id as subunitId
        		 FROM customer c
        		LEFT OUTER JOIN customer_subunit su ON c.id = su.customer_id
        		LEFT OUTER JOIN subscriptionmulti s ON ((c.id = s.customerId AND su.id is null) OR (su.id is not null AND s.customer_subunit_id = su.id))
        		AND s.startDate <= '".date("Y-m-d", strtotime($sub_filter_date_to))."' AND s.startDate <> '0000-00-00' AND s.content_status < 2
        		AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is not null AND s.stoppedDate < '".date("Y-m-d", strtotime($sub_filter_date_to))."'))
        		LEFT OUTER JOIN workgroupleader wgl ON wgl.workgroupId = s.workgroupId
        		LEFT OUTER JOIN project2 p ON ((c.id = p.customerId AND su.id is null) OR (su.id is not null AND p.customer_subunit_id = su.id))
        		LEFT OUTER JOIN project2_periods pp ON pp.projectId = p.id AND (((pp.status = 1 AND p.type = 1) OR ((p.type = 0 OR p.type is null) AND p.projectLeaderStatus = 1))  AND
        			(pp.completed_date is not null AND pp.completed_date <> '0000-00-00' AND pp.completed_date >= '".date("Y-m-d", strtotime($filter_date_from))."' AND pp.completed_date < '".date("Y-m-d", strtotime($filter_date_to))."')
        		)
        		WHERE c.content_status < 2".$sql_where."
        		GROUP BY c.id, su.id";
        	$o_query = $o_main->db->query($sql);
        	$customers = $o_query ? $o_query->result_array() : array();
        }
        $monthlyPeriods = array();
    	$firstPeriod['dateStart'] = $filter_date_from;
    	$firstPeriod['dateEnd'] = date("t.m.Y", strtotime($firstPeriod['dateStart']));

    	if(strtotime($filter_date_to) <= strtotime($firstPeriod['dateEnd'])){
    		$firstPeriod['dateEnd'] = $filter_date_to;
    		$monthlyPeriods[] = $firstPeriod;
    	} else {
    		$monthlyPeriods[] = $firstPeriod;
    		$prevPeriod = $firstPeriod;
    		do {
    			$nextPeriod = array();
    			$nextPeriod['dateStart'] = date("d.m.Y", strtotime("+1 day", strtotime($prevPeriod['dateEnd'])));
    			$nextPeriod['dateEnd'] = date("t.m.Y", strtotime($nextPeriod['dateStart']));
    			if(strtotime($filter_date_to) < strtotime($nextPeriod['dateEnd'])){
    				$nextPeriod['dateEnd'] = $filter_date_to;
    			}
    			$monthlyPeriods[] = $nextPeriod;
    			$prevPeriod = $nextPeriod;
    		} while(strtotime($nextPeriod['dateEnd']) < strtotime($filter_date_to));
    	}
    	$customers_ordered = array();
    	foreach($customers as $customer) {
    		$suborder_comments = array();
    		$total_invoicedServices = 0;
    		$total_invoicedItemSales = 0;
    		$total_salaryCost = 0;
    		$total_itemCost = 0;
    		$total_resultPercent = 0;
    		$total_resultAmount = 0;
    		$repeatingOrdersGlobal = array();
    		$repeatingOrdersUnique = array();
    		if($viewType == 1){

    			if($customer['subunitId'] > 0){
    				$sql_where_project = " AND p.projectCode = '".$o_main->db->escape_str($customer['projectCode'])."' AND p.customer_subunit_id = '".$o_main->db->escape_str($customer['subunitId'])."'";
    				$sql_where_order = " AND s.projectId = '".$o_main->db->escape_str($customer['projectCode'])."' AND s.customer_subunit_id = '".$o_main->db->escape_str($customer['subunitId'])."'";
    			} else {
    				$sql_where_project = " AND p.projectCode = '".$o_main->db->escape_str($customer['projectCode'])."'";
    				$sql_where_order = "  AND s.projectId = '".$o_main->db->escape_str($customer['projectCode'])."'";
    			}
    			foreach($monthlyPeriods as $monthlyPeriod) {
    				if(strtotime($completedRepeatingOrderDate) >= strtotime($monthlyPeriod['dateEnd'])){
    					$sql = "SELECT s.id as repeatingOrderId, s.projectId as projectCode, TRIM(s.subscriptionName) as subscriptionName, '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."' as completed_date
    					 FROM subscriptionmulti s
    					 WHERE  s.content_status < 2 AND s.startDate <= '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."'
    					AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is not null AND s.stoppedDate < '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."'))
    					".$sql_where_order;
    					$o_query = $o_main->db->query($sql);
    					$repeatingOrders = $o_query ? $o_query->result_array() : array();
    					$repeatingOrdersGlobal = array_merge($repeatingOrders, $repeatingOrdersGlobal);
    				}
    			}

    			$sql = "SELECT pp.id as projectPeriodId, p.projectCode as projectCode, TRIM(p.name) as subscriptionName, pp.completed_date as completed_date, p.type, p.id as projectId
    			 FROM  project2 p
    			LEFT OUTER JOIN project2_periods pp ON pp.projectId = p.id
    			WHERE  ((pp.status = 1 AND p.type = 1) OR ((p.type = 0 OR p.type is null) AND p.projectLeaderStatus = 1)) AND
    				(pp.completed_date is not null AND pp.completed_date <> '0000-00-00' AND pp.completed_date >= '".date("Y-m-d", strtotime($filter_date_from))."'
    				AND pp.completed_date < '".date("Y-m-d", strtotime($filter_date_to))."')
    			".$sql_where_project."
    			ORDER BY completed_date ASC";
    			$o_query = $o_main->db->query($sql);
    			$projects = $o_query ? $o_query->result_array() : array();
    			$repeatingOrdersUnified = array_merge($repeatingOrdersGlobal, $projects);

    		} else {
    			if($customer['subunitId'] > 0){
    				$sql_where_project = " AND p.customerId = '".$o_main->db->escape_str($customer['id'])."' AND p.customer_subunit_id = '".$o_main->db->escape_str($customer['subunitId'])."'";
    				$sql_where_order = " AND s.customerId = '".$o_main->db->escape_str($customer['id'])."' AND s.customer_subunit_id = '".$o_main->db->escape_str($customer['subunitId'])."'";
    			} else {
    				$sql_where_project = " AND p.customerId = '".$o_main->db->escape_str($customer['id'])."'";
    				$sql_where_order = "  AND s.customerId = '".$o_main->db->escape_str($customer['id'])."'";
    			}
    			foreach($monthlyPeriods as $monthlyPeriod) {
    				if(strtotime($completedRepeatingOrderDate) >= strtotime($monthlyPeriod['dateEnd'])){
    					$sql = "SELECT s.id as repeatingOrderId, s.projectId as projectCode, TRIM(s.subscriptionName) as subscriptionName, '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."' as completed_date
    					 FROM subscriptionmulti s
    					 WHERE  s.content_status < 2 AND s.startDate <= '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."'
    					AND (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null OR (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is not null AND s.stoppedDate < '".date("Y-m-d", strtotime($monthlyPeriod['dateEnd']))."'))
    					".$sql_where_order;
    					$o_query = $o_main->db->query($sql);
    					$repeatingOrders = $o_query ? $o_query->result_array() : array();
    					$repeatingOrdersGlobal = array_merge($repeatingOrders, $repeatingOrdersGlobal);
    				}
    			}

    			$sql = "SELECT pp.id as projectPeriodId, p.projectCode as projectCode, TRIM(p.name) as subscriptionName, pp.completed_date as completed_date, p.type, p.id as projectId
    			 FROM  project2 p
    		 	LEFT OUTER JOIN project2_periods pp ON pp.projectId = p.id
    		 	WHERE ((pp.status = 1 AND p.type = 1) OR ((p.type = 0 OR p.type is null) AND p.projectLeaderStatus = 1)) AND
    		 		(pp.completed_date is not null AND pp.completed_date <> '0000-00-00' AND pp.completed_date >= '".date("Y-m-d", strtotime($filter_date_from))."'
    				AND pp.completed_date < '".date("Y-m-d", strtotime($filter_date_to))."')
    			".$sql_where_project."
    			ORDER BY completed_date ASC";
    			$o_query = $o_main->db->query($sql);
    			$projects = $o_query ? $o_query->result_array() : array();
    			$repeatingOrdersUnified = array_merge($repeatingOrdersGlobal, $projects);
    		}

    		$oneTimeProjects = array();
    		$continuingProjects = array();
    		$continuingPeriods = array();
    		foreach($projects as $project){
    			if($project['type'] == 0) {
    				$oneTimeProjects[] = $project;
    			} else {
    				if(!isset($continuingProjects[$project['projectId']])){
    					$continuingProjects[$project['projectId']] = $project;
    				}
    				$continuingPeriods[] = $project;
    			}
    		}
    		foreach($repeatingOrdersGlobal as $repeatingOrder){
    			if(!isset($repeatingOrdersUnique[$repeatingOrder['repeatingOrderId']])){
    				$repeatingOrdersUnique[$repeatingOrder['repeatingOrderId']] = $repeatingOrder;
    			}
    		}

    		$repeatingOrdersUnified_updated = array();
    		foreach($repeatingOrdersUnified as $repeatingOrder) {
    			if($repeatingOrder['projectPeriodId'] > 0) {
    				$s_sql = "SELECT orders.*, article.system_article_type FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
    				LEFT OUTER JOIN article ON article.id = orders.articleNumber
    				WHERE customer_collectingorder.project2PeriodId = ? AND customer_collectingorder.invoiceNumber > 0";
    				$o_query = $o_main->db->query($s_sql, array($repeatingOrder['projectPeriodId']));
    				$lastApprovedMonthOrders = ($o_query ? $o_query->result_array() : array());
    			} else if($repeatingOrder['repeatingOrderId'] > 0) {
    				$s_sql = "SELECT orders.*, article.system_article_type FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
    				LEFT OUTER JOIN article ON article.id = orders.articleNumber
    				LEFT OUTER JOIN invoice i ON i.id = customer_collectingorder.invoiceNumber
    				WHERE orders.subscribtionId = ? AND i.invoiceDate >= ? AND i.invoiceDate <= ? AND customer_collectingorder.invoiceNumber > 0
    				GROUP BY orders.id";
    				$o_query = $o_main->db->query($s_sql, array($repeatingOrder['repeatingOrderId'], date("Y-m-01", strtotime($repeatingOrder['completed_date'])),date("Y-m-d", strtotime($repeatingOrder['completed_date']))));
    				$lastApprovedMonthOrders = ($o_query ? $o_query->result_array() : array());
    			}

    			$invoicedItemSales = 0;
    			$invoicedServices = 0;
    			$salaryCost = 0;
    			$itemCost = 0;

    			foreach($lastApprovedMonthOrders as $lastApprovedMonthOrder){
    				if($lastApprovedMonthOrder['system_article_type'] == 4){
    					$invoicedItemSales += $lastApprovedMonthOrder['priceTotal'];
    				} else {
    					$invoicedServices += $lastApprovedMonthOrder['priceTotal'];
    				}
    			}
    			if($repeatingOrder['projectCode'] > 0){
    				//accounting cost
    				$s_sql = "SELECT cost_from_accounting_system.* FROM cost_from_accounting_system WHERE cost_from_accounting_system.project_for_accounting_code = ?";
    				$o_query = $o_main->db->query($s_sql, array($repeatingOrder['projectCode']));
    				$accountingCosts = ($o_query ? $o_query->result_array() : array());
    				foreach($accountingCosts as $accountingCost) {
    					$itemCost += $accountingCost['amount'];
    				}
    			}

    			if($repeatingOrder['projectPeriodId'] > 0) {
    				$s_sql = "SELECT workplanlineworker.* FROM workplanlineworker WHERE workplanlineworker.projectPeriodId = ? AND (workplanlineworker.unpaidAbbsence is null OR workplanlineworker.unpaidAbbsence = 0)";
    				$o_query = $o_main->db->query($s_sql, array($repeatingOrder['projectPeriodId']));
    				$lastApprovedMonthOrders = ($o_query ? $o_query->result_array() : array());
    			} else if($repeatingOrder['repeatingOrderId'] > 0) {
    				$s_sql = "SELECT workplanlineworker.* FROM workplanlineworker WHERE workplanlineworker.repeatingOrderId = ? AND workplanlineworker.date >= ? AND workplanlineworker.date <= ? AND (workplanlineworker.unpaidAbbsence is null OR workplanlineworker.unpaidAbbsence = 0)";
    				$o_query = $o_main->db->query($s_sql, array($repeatingOrder['repeatingOrderId'], date("Y-m-01", strtotime($repeatingOrder['completed_date'])), date("Y-m-t", strtotime($repeatingOrder['completed_date']))));
    				$lastApprovedMonthOrders = ($o_query ? $o_query->result_array() : array());
    			}

    			foreach($lastApprovedMonthOrders as $lastApprovedMonthOrder){
    				$s_sql = "SELECT * FROM workplanlineworker_salary_rate WHERE workplanlineworker_id = ?";
    				$o_query = $o_main->db->query($s_sql, array($lastApprovedMonthOrder['id']));
    				$salary_rates_reported = ($o_query ? $o_query->result_array() : array());
    				foreach($salary_rates_reported as $salary_rate_reported) {
    					if($lastApprovedMonthOrder['timeSettlementMethod'] == 3){
    						$salaryCost += $salary_rate_reported['salary_rate'];
    					} else {
    						$salaryCost += $salary_rate_reported['salary_rate']*$salary_rate_reported['hours'];
    					}
    				}
    			}

    			if($repeatingorder_accountconfig['salaryPercentMultiplier'] > 0) {
    				$salaryCost = $salaryCost * (1+$repeatingorder_accountconfig['salaryPercentMultiplier']/100);
    			}

    			$invoicedAmount = $invoicedItemSales + $invoicedServices;
    			$costAmount = $salaryCost + $itemCost;
    			$resultAmount = $invoicedAmount - $costAmount;

    			$resultPercent = 0;
    			if($invoicedAmount <= 0) {
    				if($invoicedAmount == 0){
    					$resultPercent = number_format(($invoicedAmount - $costAmount)/($costAmount), 2, ".", "") * 100;
    				} else {
    					$resultPercent = number_format(($invoicedAmount - $costAmount)/($invoicedAmount-$costAmount), 2, ".", "") * 100;
    				}
    			} else {
    				$resultPercent = number_format(($invoicedAmount - $costAmount)/$invoicedAmount, 2, ".", "") * 100;
    			}
    			$repeatingOrder['resultPercent'] = $resultPercent;
    			$repeatingOrder['resultAmount'] = $resultAmount;
    			$repeatingOrder['itemCost'] = $itemCost;
    			$repeatingOrder['salaryCost'] = $salaryCost;
    			$repeatingOrder['invoicedItemSales'] = $invoicedItemSales;
    			$repeatingOrder['invoicedServices'] = $invoicedServices;
    			$repeatingOrder['comments'] = $invoicedServices;

    			if($repeatingOrder['projectPeriodId'] > 0) {

    				$sql = "SELECT * FROM total_result_comments WHERE project2_period_id = '".$o_main->db->escape_str($repeatingOrder['projectPeriodId'])."'";
    				$o_query = $o_main->db->query($sql);
    				$repeatingOrder['comments'] = $o_query ? $o_query->result_array() : array();
    				if(count($repeatingOrder['comments']) > 0) {
    					$suborder_comments[] = $repeatingOrder;
    				}
    			} else if($repeatingOrder['repeatingOrderId'] > 0) {
    				$sql = "SELECT * FROM total_result_comments WHERE subscriptionmulti_id = '".$o_main->db->escape_str($repeatingOrder['repeatingOrderId'])."'";
    				$o_query = $o_main->db->query($sql);
    				$repeatingOrder['comments'] = $o_query ? $o_query->result_array() : array();
    				if(count($repeatingOrder['comments']) > 0) {
    					$suborder_comments[] = $repeatingOrder;
    				}
    			}

    			$repeatingOrdersUnified_updated[] = $repeatingOrder;
    			$total_invoicedServices+= $invoicedServices;
    			$total_invoicedItemSales+= $invoicedItemSales;
    			$total_salaryCost+= $salaryCost;
    			$total_itemCost+= $itemCost;
    		}

    		$invoicedAmount = $total_invoicedItemSales + $total_invoicedServices;
    		$costAmount = $total_salaryCost + $total_itemCost;
    		$total_resultAmount = $invoicedAmount - $costAmount;

    		$total_resultPercent = 0;
    		if($invoicedAmount <= 0) {
    			if($invoicedAmount == 0){
    				$total_resultPercent = number_format(($invoicedAmount - $costAmount)/($costAmount), 2, ".", "") * 100;
    			} else {
    				$total_resultPercent = number_format(($invoicedAmount - $costAmount)/($invoicedAmount-$costAmount), 2, ".", "") * 100;
    			}
    		} else {
    			$total_resultPercent = number_format(($invoicedAmount - $costAmount)/$invoicedAmount, 2, ".", "") * 100;
    		}

    		$customer['total_resultAmount'] = $total_resultAmount;
    		$customer['total_resultPercent'] = $total_resultPercent;
    		$customer['total_itemCost'] = $total_itemCost;
    		$customer['total_salaryCost'] = $total_salaryCost;
    		$customer['total_invoicedItemSales'] = $total_invoicedItemSales;
    		$customer['total_invoicedServices'] = $total_invoicedServices;
    		$customer['total_invoiced'] = $total_invoicedServices+$total_invoicedItemSales;
    		$customer['repeatingOrdersUnified'] = $repeatingOrdersUnified_updated;
    		$customer['projects'] = $projects;
    		$customer['repeatingOrdersGlobal'] = $repeatingOrdersGlobal;
    		$customer['repeatingOrdersUnique'] = $repeatingOrdersUnique;

    		$customer['oneTimeProjects'] = $oneTimeProjects;
    		$customer['continuingProjects'] = $continuingProjects;
    		$customer['continuingPeriods'] = $continuingPeriods;
    		$customer['suborder_comments'] = $suborder_comments;
    		$customers_ordered[] = $customer;
    	}
    	if($order_field == "customername"){
    		if($order_direction == 1){
    			usort($customers_ordered, "cmp_customer");
    		} else {
    			usort($customers_ordered, "cmp_customer2");
    		}
    	} else if($order_field == "revenue"){
    		if($order_direction == 1){
    			usort($customers_ordered, "cmp_revenue");
    		} else {
    			usort($customers_ordered, "cmp_revenue2");
    		}
    	} else if($order_field == "result"){
    		if($order_direction == 1){
    			usort($customers_ordered, "cmp_result");
    		} else {
    			usort($customers_ordered, "cmp_result2");
    		}
    	} else if($order_field == "margin"){
    		if($order_direction == 1){
    			usort($customers_ordered, "cmp_margin");
    		} else {
    			usort($customers_ordered, "cmp_margin2");
    		}
    	}

        return $customers_ordered;
    }
}

function cmp_customer($a, $b)
{
    return strcmp(mb_strtolower(str_replace(" ","",$a["customerName"])), mb_strtolower(str_replace(" ","",$b["customerName"])));
}
function cmp_customer2($a, $b)
{
    return strcmp(mb_strtolower(str_replace(" ","",$b["customerName"])), mb_strtolower(str_replace(" ","",$a["customerName"])));
}
function cmp_revenue($a, $b)
{
    return $a["total_invoiced"] - $b["total_invoiced"];
}
function cmp_revenue2($a, $b)
{
    return $b["total_invoiced"] - $a["total_invoiced"];
}
function cmp_result($a, $b)
{
    return $a["total_resultAmount"] - $b["total_resultAmount"];
}
function cmp_result2($a, $b)
{
    return $b["total_resultAmount"] - $a["total_resultAmount"];
}
function cmp_margin($a, $b)
{
    return $a["total_resultPercent"] - $b["total_resultPercent"];
}
function cmp_margin2($a, $b)
{
    return $b["total_resultPercent"] - $a["total_resultPercent"];
}
?>
