<?php 
$page =  $v_data['params']['page'] ? $v_data['params']['page']: 1;
$perPage =  $v_data['params']['perPage'] ? $v_data['params']['perPage']: 100;

$offset = ($page-1)*$perPage;
if($offset < 0){
    $offset = 0;
}
$pager = " LIMIT ".$perPage ." OFFSET ".$offset;

$sql_join = "";
$sql_where = "";

if($page == 0 && $perPage == 0){
    $pager = "";
}

foreach($filters as $filterName=>$filterValue){
    switch($filterName){
        case "adtype":        
            if($filterValue > 0){
                $sql_join .= "";    
                $sql_where .= " AND m.adType = ".$o_main->db->escape($filterValue);  
            } 
        break;
        case "search_filter":
            if(is_array($filterValue)){
                 switch($filterValue[0]){
                    case 1:
                    $sql_join .= "";
                    $sql_where .= " AND (m.title LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                    break;
                    default:
                    $sql_join .= "";
                    $sql_where .= " AND (m.title LIKE '%".$o_main->db->escape_like_str($filterValue[1])."%')";
                }
            } else {
                $sql_join .= "";            
                $sql_where .= " AND (m.title LIKE '%".$o_main->db->escape_like_str($filterValue)."%')";                   
            }
        break;
    }
}

$s_sql = "SELECT customer.*, subscriptiontype.name as subscriptionTypeName FROM customer JOIN subscriptionmulti ON subscriptionmulti.customerId = customer.id 
JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
WHERE (subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate > DATE(NOW())) AND (subscriptionmulti.startDate <= DATE(NOW())) 
GROUP BY customer.id ORDER BY customer.name";

$o_query = $o_main->db->query($s_sql);
$rowCount = $o_query ? $o_query->num_rows() : 0;


$s_sqlLimit = $s_sql.$pager;
$o_query = $o_main->db->query($s_sqlLimit);
$customers = $o_query ? $o_query->result_array() : array();
$returnCustomers = array();
foreach($customers as $customer) {
	$s_sql = "SELECT * FROM contactperson WHERE contactperson.customerId = ? ORDER BY contactperson.name";
	$o_query = $o_main->db->query($s_sql, array($customer['id']));
	$contactPersons = $o_query ? $o_query->result_array() : array();

	$customer['contactPersons'] = $contactPersons;
	array_push($returnCustomers, $customer);
}
$v_return['data'] = $returnCustomers;
$v_return['rowCount'] = $rowCount;
?>