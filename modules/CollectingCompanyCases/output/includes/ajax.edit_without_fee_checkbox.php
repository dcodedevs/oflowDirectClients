<?php
$collecting_case_id = $_POST['case_id'];

$s_sql = "SELECT * FROM collecting_company_cases WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($collecting_case_id));
$v_data = $o_query ? $o_query->row_array() : array();
if($moduleAccesslevel > 10)
{
	if($v_data){
		$sql_update = "";
		if(isset($_POST['without_fee_paid'])) {
			$sql_update = ", without_fee_paid = ".intval($_POST['without_fee_paid']);
		} else if(isset($_POST['without_fee_notpaid'])) {
			$sql_update = ", without_fee_notpaid = ".intval($_POST['without_fee_notpaid']);
		} else if(isset($_POST['company_fee_paid'])) {
			$sql_update = ", company_fee_paid = ".intval($_POST['company_fee_paid']);
		} else if(isset($_POST['company_fee_notpaid'])) {
			$sql_update = ", company_fee_notpaid = ".intval($_POST['company_fee_notpaid']);
		} else if(isset($_POST['checkbox1'])) {
			$sql_update = ", checkbox_1 = ".intval($_POST['checkbox1']);
		}
		$s_sql = "UPDATE collecting_company_cases SET
		updated = now(),
		updatedBy= ?".$sql_update."
		WHERE id = ?";
		$o_main->db->query($s_sql, array($variables->loggID, $v_data['id']));


		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}
