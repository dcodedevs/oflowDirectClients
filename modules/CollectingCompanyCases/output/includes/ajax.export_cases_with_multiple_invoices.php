<?php
if(isset($_POST['output_form_submit'])) {
    set_time_limit(300);
    ini_set('memory_limit', '256M');

    // no cache
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // content type
    header("Content-Disposition: attachment; filename=\"eksport.csv\"");
    header("Content-type: text/csv; charset=ISO-8859-1");
    
    $creditor_id = $_POST['creditor_id'];
    
    $dateFrom = date("Y-m-d", strtotime($_POST['dateFrom']));
    $dateTo = date("Y-m-d", strtotime($_POST['dateTo']));

    $s_csv_content = '';
    $s_line_array = array();
    $s_line_array[] = $formText_CreditorName_output;
    $s_line_array[] = $formText_DebitorName_output;
    $s_line_array[] = $formText_CaseId_output;
    $s_line_array[] = $formText_NumberOfInvoicesOnTheCase_output;

    $s_line = implode(";", $s_line_array);
    $s_csv_content .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');

    $s_sql = "SELECT ccc.*, COUNT(cccl.id) as invoiceCount, cred.companyname as creditorName, concat_ws(' ', c.name, c.middlename, c.lastname) as debitorName FROM collecting_company_cases ccc
    JOIN collecting_company_cases_claim_lines cccl ON cccl.collecting_company_case_id = ccc.id AND claim_type = 1
    JOIN creditor cred ON  cred.id  = ccc.creditor_id
    JOIN customer c ON c.id = ccc.debitor_id
    WHERE 
    (
    (IFNULL(ccc.warning_case_created_date, '0000-00-00') >= '".$o_main->db->escape_str($dateFrom)."' AND IFNULL(ccc.warning_case_created_date, '0000-00-00') <= '".$o_main->db->escape_str($dateTo)."')
    OR (IFNULL(ccc.collecting_case_created_date, '0000-00-00') >= '".$o_main->db->escape_str($dateFrom)."' AND IFNULL(ccc.collecting_case_created_date, '0000-00-00') <= '".$o_main->db->escape_str($dateTo)."')
    )
    GROUP BY ccc.id HAVING invoiceCount >1";
    $o_query = $o_main->db->query($s_sql);
	$v_collecting_company_cases = $o_query ? $o_query->result_array() : array();
    foreach($v_collecting_company_cases  as $v_collecting_company_case){
        $s_line_array = array();
        $s_line_array[] = $v_collecting_company_case['creditorName'];
        $s_line_array[] = $v_collecting_company_case['debitorName'];
        $s_line_array[] = $v_collecting_company_case['id'];
        $s_line_array[] = $v_collecting_company_case['invoiceCount'];

        $s_line = implode(";", $s_line_array);
        $s_csv_content .= mb_convert_encoding($s_line."\r\n", 'ISO-8859-1', 'UTF-8');
    }
    
    

    echo $s_csv_content;
    exit;
}

?>
<div class="popupform">
<div class="popupformTitle"><?php echo $formText_ChooseDateToExport_LID6214;?></div>
<div id="popup-validate-message" style="display:none;"></div>
<form class="output-form-contactperson" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$inc_obj."&inc_act=export_cases_with_multiple_invoices";?>" method="post" target="_blank">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="creditor_id" value="<?php echo $_POST['creditor_id'];?>">

	<div class="inner">
        <div class="line">
            <div class="lineTitle"><?php echo $formText_DateFrom_output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput botspace datepicker" name="dateFrom" value="<?php echo date("01.m.Y", strtotime("-1 month"));?>" autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>
        <div class="line">
            <div class="lineTitle"><?php echo $formText_DateTo_output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput botspace datepicker" name="dateTo" value="<?php echo date("t.m.Y", strtotime("-1 month"));?>" autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_LID4382;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Export_LID6219; ?>">
	</div>
</form>
</div>
<style>
input[type="checkbox"][readonly] {
  pointer-events: none;
}
h3 select {
    font-size: 16px;
    margin-left: 15px;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$("form.output-form-contactperson").validate({
		submitHandler: function(form) {
			form.submit();
			out_popup.close();
		}
	});
    $(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: 'dd.mm.yy'
	})
});
</script>
