<?php
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

include(__DIR__.'/../../../elementsGlobal/cMain.php');
$key = $_GET['key'];
if($key != ""){
    $username = $_GET['email'];
    $s_sql = "select * from customer_accountconfig ORDER BY id ASC";
    $o_query = $o_main->db->query($s_sql);
    $customer_accountconfig = $o_query ? $o_query->row_array() : array();

    $s_sql = "select * from customer where member_profile_link_code = ?";
    $o_query = $o_main->db->query($s_sql, array($key));
    $customer = $o_query ? $o_query->row_array() : array();
    if($customer){

        $s_sql = "INSERT INTO customer_member_link_tracking SET created = NOW(), code = ?, createdBy = ?";
        $o_query = $o_main->db->query($s_sql, array($key, $username));

        header("Location: ".$customer_accountconfig['member_profile_link_url'].$customer['id']."&code=".$key);
    }
}
?>
