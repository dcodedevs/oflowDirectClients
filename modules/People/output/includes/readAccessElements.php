<?php
$v_current_module=explode('/modules/', __DIR__);
$s_current_module=$v_current_module[1];

$v_current_module=explode('/', $s_current_module);
$s_current_module=$v_current_module[0];
$s_current_module_folder=$v_current_module[1];
if(!$fw_session){
    $o_query = $o_main->db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(), cache_timestamp)) refreshtime FROM session_framework WHERE companyaccessID = '".$o_main->db->escape_str($_GET['caID'])."' AND session = '".$o_main->db->escape_str($_COOKIE['sessionID'])."' AND username = '".$o_main->db->escape_str($_COOKIE['username'])."' LIMIT 1");
    $fw_session = $o_query ? $o_query->row_array() : array();
}
$menuAccess = json_decode($fw_session['cache_menu'], true);
$accessElements = $menuAccess[$s_current_module][4]['data'][0]['access_element'];
foreach($accessElements as $key=>$type) {
    if($type == 0){
        ${"accessElementAllow_".$key} = true;
    } else if($type==1) {
        ${"accessElementRestrict_".$key} = true;
    }
}
if($variables->useradmin){
    if(!function_exists("getAccessElements")) include(__DIR__."/../../../../fw/getynet_fw/modules/users/output/getAccessElementList.php");
    $allAccessElements = getAccessElements($s_current_module, "allow");
    foreach($allAccessElements as $singleAccessElement) {
        ${"accessElementAllow_".$singleAccessElement['name']} = true;
    }

    $allAccessElements = getAccessElements($s_current_module, "restrict");
    foreach($allAccessElements as $singleAccessElement) {
        ${"accessElementRestrict_".$singleAccessElement['name']} = true;
    }
}
?>
