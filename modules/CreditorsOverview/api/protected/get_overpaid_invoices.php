<?php
$_POST = $v_data['params']['post'];
$customer_id = $_POST['customer_id'];

$sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($customer_id)."'";
$o_query = $o_main->db->query($sql);
$customer = $o_query ? $o_query->row_array() : array();
$creditor_transactions = array();
if($customer){
    $customerName= $customer['name'];
    if($customer['middlename']){            
        $customerName.= " ".$customer['middlename'];
    }
    if($customer['lastname']){            
        $customerName.= " ".$customer['lastname'];
    }
    $sql = "SELECT * FROM creditor_transactions WHERE external_customer_id = '".$o_main->db->escape_str($customer['creditor_customer_id'])."' AND creditor_id = '".$o_main->db->escape_str($customer['creditor_id'])."' AND open = 1";
    $o_query = $o_main->db->query($sql);
    $creditor_transactions = $o_query ? $o_query->result_array() : array();

}
$v_return['status'] = 1;
$v_return['creditor_transactions'] = $creditor_transactions;
$v_return['customerName'] = $customerName;
?>
