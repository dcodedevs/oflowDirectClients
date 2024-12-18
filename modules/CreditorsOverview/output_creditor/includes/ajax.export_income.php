<?php
if($_POST['output_form_submit']) {

    set_time_limit(300);
    ini_set('memory_limit', '256M');

    // no cache
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    // content type
    header("Content-Disposition: attachment; filename=\"eksport.csv\"");
    header("Content-type: text/csv; charset=UTF-8");

    $s_csv_data = '';
    $l_cp_max_count = 0;

    if(isset($_POST['dateFrom'])){ $filter_date_from = date("Y-m-d", strtotime($_POST['dateFrom'])); } else { $filter_date_from = ''; }
    if(isset($_POST['dateTo'])){ $filter_date_to = date("Y-m-d", strtotime($_POST['dateTo'])); } else { $filter_date_to = ''; }
    if($filter_date_from != "" && $filter_date_to != "") {
        $sql = "SELECT cr.*, cr.companyname as creditorName, IFNULL(cr.last_report_date, '0000-00-00') as last_report_date FROM creditor cr
        WHERE cr.integration_module <> '' AND cr.sync_from_accounting = 1 
        AND DATE(IFNULL(cr.last_report_date, '0000-00-00')) <>'0000-00-00'";
        $o_query = $o_main->db->query($sql);
        $creditors = $o_query ? $o_query->result_array() : array();
        foreach($creditors as $creditor){
            $sql = "SELECT SUM(crc.summary) as collecting_sum, SUM(crc.saerskilt) as sum_saerskilt, 
            SUM(crc.fee_cwo) as sum_fee_cwo, SUM(crc.fee_cw) as sum_fee_cw, SUM(crc.fee_pwo) as sum_fee_pwo, 
            SUM(crc.fee_pw) as sum_fee_pw, SUM(crc.forsinkelsesrente) as sum_forsinkelsesrente, 
            SUM(crc.purregebyr) as sum_purregebyr, SUM(crc.overbetalt) as sum_overbetalt, 
            SUM(crc.hovedstol) as sum_hovedstol, SUM(crc.avdragsgebyr) as sum_avdragsgebyr, 
            SUM(crc.omkostningesrente) as sum_omkostningesrente, 
            SUM(crc.mva) as sum_mva
            FROM creditor_report_collecting crc WHERE crc.creditor_id = ? AND crc.date >= ? AND crc.date <= ? GROUP BY crc.creditor_id";
            $o_query = $o_main->db->query($sql, array($creditor['id'], $filter_date_from, $filter_date_to));
            $creditor_report_collecting = $o_query ? $o_query->row_array() : array();
            $sql = "SELECT SUM(crc.total_income) as reminder_sum FROM creditor_report_reminder crc WHERE crc.creditor_id = ? AND crc.date >= ? AND crc.date <= ? GROUP BY crc.creditor_id";
            $o_query = $o_main->db->query($sql, array($creditor['id'], $filter_date_from, $filter_date_to));
            $creditor_report_reminder = $o_query ? $o_query->row_array() : array();
        
            $total_income = $creditor_report_collecting['collecting_sum'] + $creditor_report_reminder['reminder_sum'];
            
            $s_line = '';
            $s_line = $creditor['companyname'].";".$creditor['id'].";".date("d.m.Y", strtotime($filter_date_from)).";".date("d.m.Y", strtotime($filter_date_to)).";".$creditor_report_collecting['collecting_sum'].";".$creditor_report_reminder['reminder_sum'].";".$total_income;

            $s_csv_data .= mb_convert_encoding($s_line."\r\n", 'UTF-8');
        }

    }
    $s_csv_content = $formText_CreditorName_output.";".$formText_CreditorId_output.";".$formText_DateFrom_output.";".$formText_DateTo_output.";".$formText_IncomeFromCollecting_output.";".$formText_IncomeFromReminder_output.";".$formText_TotalIncome_output;

    $s_csv_content = mb_convert_encoding($s_csv_content."\r\n", 'UTF-8');

    $s_csv_content .= $s_csv_data;
    echo $s_csv_content;
    exit;
} else {
    ?>
    <div class="popupform">
    <div class="popupformTitle"><?php echo $formText_ChooseWhichColumnsToExport_LID6214;?></div>
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form-contactperson" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$inc_obj."&inc_act=export_income";?>" method="post" target="_blank">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">

        <div class="inner">
            <div class="line">
				<div class="lineTitle"><?php echo $formText_DateFrom_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker dateFrom" autocomplete="off" name="dateFrom" value="<?php echo $bookaccount['bookaccount']; ?>" required/>
				</div>
				<div class="clear"></div>
			</div>
            
            <div class="line">
				<div class="lineTitle"><?php echo $formText_DateTo_output; ?></div>
				<div class="lineInput">
					<input type="text" class="popupforminput botspace datepicker dateTo" autocomplete="off" name="dateTo" value="<?php echo $bookaccount['bookaccount']; ?>" required/>
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
        $(".datepicker").datepicker({
            "dateFormat": "dd.mm.yy"
        });
        $("form.output-form-contactperson").validate({
            submitHandler: function(form) {
                form.submit();
                out_popup.close();
            }
        });
    });
    </script>

    <?php
}