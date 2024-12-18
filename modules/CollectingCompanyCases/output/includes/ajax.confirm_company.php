<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['customerId']) && $_POST['customerId'] > 0)
	{
		$s_sql = "SELECT * FROM customer WHERE id = ?";
	    $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
	    if($o_query && $o_query->num_rows() == 1) {
			$s_sql = "UPDATE customer SET
			confirmed_as_company= NOW(),
			confirmed_by = ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID,  $_POST['customerId']));
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	} else {
		$fw_error_msg[] = $formText_MissingObjection_output;
	}
}
