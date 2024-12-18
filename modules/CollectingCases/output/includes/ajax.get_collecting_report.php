<?php
$v_period = array(
	$formText_FirstHalfYear_Output,
	$formText_AllYear_Output,
);
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		ini_set('max_execution_time', 600);
		
		$s_period_start = $_POST['report_year'].'-01-01';
		$s_period_end = $_POST['report_year'].(1==$_POST['report_period']?'-12-31':'-06-30');
		
		//$s_sql = "SELECT COUNT(id) AS cnt FROM collecting_cases AS cc WHERE (cc.first_reminder_letter_date IS NOT NULL AND cc.first_reminder_letter_date < '".$o_main->db->escape_str($s_period_start)."') AND (cc.first_collecting_letter_date IS NULL OR cc.first_collecting_letter_date >= '".$o_main->db->escape_str($s_period_start)."') AND (cc.stopped_date IS NULL OR cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."')";
		$s_sql = "SELECT COUNT(id) AS cnt FROM collecting_cases AS cc WHERE (cc.first_reminder_letter_date IS NOT NULL AND cc.first_reminder_letter_date < '".$o_main->db->escape_str($s_period_start)."') AND (cc.first_collecting_letter_date IS NULL OR (cc.first_collecting_letter_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.first_collecting_letter_date <= '".$o_main->db->escape_str($s_period_end)."')) AND (cc.stopped_date IS NULL OR (cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."'))";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInReminderlevelAtBeginningOfYear_Output
		$v_count['value_1'] = $v_row['cnt'];
		
		$s_sql = "SELECT COUNT(*) AS cnt FROM collecting_cases AS cc WHERE (cc.first_collecting_letter_date IS NOT NULL AND cc.first_collecting_letter_date < '".$o_main->db->escape_str($s_period_start)."') AND (cc.stopped_date IS NULL OR (cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."'))";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInCollectinglevelAtBeginningOfYear_Output
		$v_count['value_2'] = $v_row['cnt'];
		
		$s_sql = "SELECT COUNT(*) AS cnt FROM collecting_cases AS cc WHERE (cc.first_reminder_letter_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.first_reminder_letter_date <= '".$o_main->db->escape_str($s_period_end)."') OR (cc.first_reminder_letter_date IS NULL AND cc.first_collecting_letter_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.first_collecting_letter_date <= '".$o_main->db->escape_str($s_period_end)."')";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfNewCasesInBothLevelsSoFarThisYear_Output
		$v_count['value_3'] = $v_row['cnt'];
		
		$s_sql = "SELECT COUNT(*) AS cnt FROM collecting_cases AS cc WHERE cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."' AND cc.status = 2";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfClosedCasesInReminderlevelSoFarThisYear_Output
		$v_count['value_4'] = $v_row['cnt'];
		
		$s_sql = "SELECT COUNT(*) AS cnt FROM collecting_cases AS cc WHERE cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."' AND cc.status = 4";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfClosedCasesInCollectinglevelSoFarThisYear_Output
		$v_count['value_5'] = $v_row['cnt'];
		
		$s_sql = "SELECT COUNT(*) AS cnt FROM collecting_cases AS cc WHERE cc.status = 1 OR cc.status = 3";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInBothLevels_Output
		$v_count['value_6'] = $v_row['cnt'];
		
		$s_sql = "SELECT SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim FROM collecting_cases AS cc WHERE cc.status = 1 OR cc.status = 3";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_MainClaimInActiveCasesInBothLevels_Output
		$v_count['value_7'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
		//formText_MainClaimInActiveCasesInBothLevels_Output
		$v_count['value_8'] = $v_row['current_total_claim'];
		
		$s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim FROM collecting_cases AS cc WHERE cc.status = 1";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInReminderlevel_Output
		$v_count['value_9'] = $v_row['cnt'];
		//formText_MainClaimInActiveCasesInReminderlevel_Output
		$v_count['value_10'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
		//formText_TotalClaimInActiveCasesInReminderlevel_Output
		$v_count['value_11'] = $v_row['current_total_claim'];
		
		$s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim FROM collecting_cases AS cc WHERE cc.status = 3 AND cc.first_collecting_letter_date >= DATE_SUB(NOW(), INTERVAL 18 MONTH)";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInReminderlevel_Output
		$v_count['value_12'] = $v_row['cnt'];
		//formText_MainClaimInActiveCasesInReminderlevel_Output
		$v_count['value_13'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
		//formText_TotalClaimInActiveCasesInReminderlevel_Output
		$v_count['value_14'] = $v_row['current_total_claim'];
		
		$s_sql = "SELECT COUNT(cc.id) AS cnt, SUM(cc.original_main_claim) AS original_main_claim, SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.current_total_claim) AS current_total_claim FROM collecting_cases AS cc WHERE cc.status = 3 AND (cc.first_collecting_letter_date IS NULL OR cc.first_collecting_letter_date < DATE_SUB(NOW(), INTERVAL 18 MONTH))";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInReminderlevel_Output
		$v_count['value_15'] = $v_row['cnt'];
		//formText_MainClaimInActiveCasesInReminderlevel_Output
		$v_count['value_16'] = $v_row['original_main_claim'] - $v_row['collected_main_claim'];
		//formText_TotalClaimInActiveCasesInReminderlevel_Output
		$v_count['value_17'] = $v_row['current_total_claim'];
		
		$s_sql = "SELECT COUNT(cc.id) AS cnt FROM collecting_cases AS cc JOIN collecting_cases_payment_plan AS ccpp ON ccpp.collecting_case_id = cc.id WHERE ccpp.status IS NULL OR ccpp.status = 0";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_NumberOfActiveCasesInReminderlevel_Output
		$v_count['value_18'] = $v_row['cnt'];
		
		
		$s_sql = "SELECT SUM(cc.collected_main_claim) AS collected_main_claim FROM collecting_cases AS cc WHERE cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."' AND (cc.first_reminder_letter_date > DATE_SUB(cc.stopped_date, INTERVAL 6 MONTH)";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_CollectedMainclaimWithCasetimeLessThan6Months_Output
		$v_count['value_19'] = $v_row['collected_main_claim'];
		
		$s_sql = "SELECT SUM(cc.collected_main_claim) AS collected_main_claim, SUM(cc.collected_interest) AS collected_interest, SUM(cc.collected_legal_cost) AS collected_legal_cost, SUM(cc.collected_vat) AS collected_vat FROM collecting_cases AS cc WHERE cc.stopped_date >= '".$o_main->db->escape_str($s_period_start)."' AND cc.stopped_date <= '".$o_main->db->escape_str($s_period_end)."'";
		$o_query = $o_main->db->query($s_sql);
		$v_row = $o_query ? $o_query->row_array() : array();
		//formText_TotalCollectedMainclaim_Output
		$v_count['value_20'] = $v_row['collected_main_claim'];
		//formText_TotalCollectedInterests_Output
		$v_count['value_21'] = $v_row['collected_interest'];
		//formText_TotalCollectedLegalCosts_Output
		$v_count['value_22'] = $v_row['collected_legal_cost'];
		//formText_TotalCollectedVat_Output
		$v_count['value_23'] = $v_row['collected_vat'];
		//formText_SumAllCollected_Output
		$v_count['value_24'] = $v_row['collected_main_claim'] + $v_row['collected_interest'] + $v_row['collected_legal_cost'] + $v_row['collected_vat'];
		
		

		include_once(__DIR__.'/tcpdf/tcpdf.php');
	
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('');
		$pdf->SetTitle($formText_CollectingReport_Output);
		$pdf->SetSubject('');
		$pdf->SetKeywords('');
	
		// remove default header/footer
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	
		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	
		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		// add a page
		$pdf->AddPage();
		
		setlocale(LC_TIME, 'no_NO');
		$pdf->SetFont('times', '', 12);
		
		// Set some content to print
		$s_html = '<h1>'.$formText_CollectingStatisticReport_Output.'</h1>
		<p>'.$formText_Year_Output.': '.$_POST['report_year'].'</p>
		<p>'.$formText_Period_Output.': '.$v_period[$_POST['report_period']].'</p>
		<hr>
		<table cellpadding="5" cellspacing="0" border="1">
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInReminderlevelAtBeginningOfYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_1'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInCollectinglevelAtBeginningOfYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_2'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfNewCasesInBothLevelsSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_3'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfClosedCasesInReminderlevelSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_4'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfClosedCasesInCollectinglevelSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_5'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.($v_count['value_6']).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.$v_count['value_7'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.$v_count['value_8'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.$v_count['value_9'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.$v_count['value_10'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.$v_count['value_11'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInCollectinglevelNotOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.$v_count['value_12'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInCollectinglevelNotOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.$v_count['value_13'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInCollectinglevelNotOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.$v_count['value_14'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInCollectinglevelOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.$v_count['value_15'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInCollectinglevelOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_16'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInCollectinglevelOlderThan18Months_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_17'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfDownpaymentAgreements_Output.'</td>
				<td width="100" align="right">'.$v_count['value_18'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_CollectedMainclaimWithCasetimeLessThan6Months_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_19'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedMainclaim_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_20'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedInterests_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_21'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedLegalCosts_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_22'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedVat_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_23'], 2).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_SumAllCollected_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_24'], 2).'</td>
			</tr>
		</table>';
		$pdf->writeHTMLCell(0, 0, '', '', $s_html, 0, 1, 0, true, '', true);
		
		while (ob_get_level() !== 0) {
		  ob_end_clean();
		}
		$pdf->Output('collecting_report.pdf', 'I');
		exit;
	}
}

$s_sql = "SELECT MIN(t_main.first_date) AS start_date FROM 
(
SELECT MIN(c1.first_reminder_letter_date) AS first_date FROM collecting_cases AS c1 WHERE c1.first_reminder_letter_date IS NOT NULL AND c1.first_reminder_letter_date <= CURDATE()
UNION
SELECT MIN(c2.first_collecting_letter_date) AS first_date FROM collecting_cases AS c2 WHERE c2.first_collecting_letter_date IS NOT NULL AND c2.first_collecting_letter_date <= CURDATE()
) AS t_main";
$o_query = $o_main->db->query($s_sql);
$v_start_date = $o_query ? $o_query->row_array() : array();
$l_start_year = $l_stop_year = date("Y");
if('' != $v_start_date['start_date'] && '0000-00-00' != $v_start_date['start_date'])
{
	$l_start_year = date("Y", strtotime($v_start_date['start_date']));
}
?>
<div class="popupform">
	<div class="popupformTitle"><?php echo $formText_CreateCollectingCasesWtatisticsReport_Output;?></div>
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=".$s_inc_obj."&inc_act=".$s_inc_act;?>" method="post" target="_blank">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ChooseYear_Output; ?></div>
				<div class="lineInput">
					<select name="report_year">
					<?php
					for($l_stop_year; $l_stop_year >= $l_start_year; $l_stop_year--)
					{
						?><option value="<?php echo $l_stop_year;?>"><?php echo $l_stop_year;?></option><?php
					}
					?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_ChoosePeriod_Output; ?></div>
				<div class="lineInput">
					<select name="report_period">
					<?php
					foreach($v_period as $l_key => $s_item)
					{
						?><option value="<?php echo $l_key;?>"><?php echo $s_item;?></option><?php
					}
					?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_CreateReport_Output; ?>">
		</div>
	</form>
</div>
<style>
.popupform .lineTitle {
	font-weight:700;
	margin-bottom: 10px;
}
.popupform textarea.popupforminput {
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
}
.project-file {
	margin-bottom: 4px;
}
.project-file .deleteImage {
	float: right;
}
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

// New uploaded images to process
var imagesToProcess = [];
var imagesHandle = [];
var images = [];

$(function() {
	$("form.output-form").validate({
		submitHandler: function(form){
			form.submit();
			out_popup.close();
		},
		invalidHandler: function(event, validator) {
			var errors = validator.numberOfInvalids();
			if (errors) {
				var message = errors == 1
				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

				$("#popup-validate-message").html(message);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			} else {
				$("#popup-validate-message").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		}
	});
});
</script>
