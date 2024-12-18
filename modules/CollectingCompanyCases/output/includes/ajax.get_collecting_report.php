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
		include(__DIR__."/fnc_get_collecting_case_report_data.php");
		$v_count = get_collecting_case_report_data($o_main, $_POST['report_year'], $_POST['report_period']);
		include_once(__DIR__.'/tcpdf/tcpdf.php');
		include("collecting_report_entries.php");

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
		/*
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
		</tr>*/
		// Set some content to print
		$s_html = '<h1>'.$formText_CollectingStatisticReport_Output.'</h1>
		<p>'.$formText_Year_Output.': '.$_POST['report_year'].'</p>
		<p>'.$formText_Period_Output.': '.$v_period[$_POST['report_period']].'</p>
		<hr>
		<table cellpadding="5" cellspacing="0" border="1">
			<tr>
				<td width="527">'.$formText_TotalCollectedMainclaim_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_20'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedInterests_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_21'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedNonLegalCosts_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_22'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalCollectedVat_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_23'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfNewCompanyCasesInBothLevelsSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_25'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfNewCompanyCasesInWarningLevelsSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_26'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfNewPersonCasesInBothLevelsSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_27'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfNewPersonCasesInWarningLevelsSoFarThisYear_Output.'</td>
				<td width="100" align="right">'.$v_count['value_28'].'</td>
			</tr>

			<tr>
				<td width="527">'.$formText_NumberOfActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.($v_count['value_6']).'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_7'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInBothLevels_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_8'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActiveCompanyCases_Output.'</td>
				<td width="100" align="right">'.$v_count['value_29'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfActivePersonCases_Output.'</td>
				<td width="100" align="right">'.$v_count['value_32'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCompanyCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_30'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActivePersonCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_33'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_InterestInActiveCompanyCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_31_1'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_InterestInActivePersonCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_34_1'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NonLegalCostInActiveCompanyCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_31_2'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NonLegalCostInActivePersonCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_34_2'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCompanyCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_31'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActivePersonCases_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_34'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor0To500_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_35'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor500To1000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_36'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor1000To2500_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_37'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor2500To10000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_38'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor10000To50000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_39'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor50000To250000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_40'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor250000To500000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_41'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor500000To1000000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_42'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor1000000To3000000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_43'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimFor3000000To5000000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_44'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesMainClaimOver5000000_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_45'], 2, "," ," ").'</td>
			</tr>


			<tr>
				<td width="527">'.$formText_PersonCasesWithYear0To1_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_46'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesWithYear1To2_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_47'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesWithYear2To3_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_48'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesWithYear3To5_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_49'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesWithYear5To10_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_50'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_PersonCasesWithYearOver10_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_51'], 2, "," ," ").'</td>
			</tr>


			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor0To500_Output.'</td>
				<td width="100" align="right">'.$v_count['value_52'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor500To1000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_53'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor1000To2500_Output.'</td>
				<td width="100" align="right">'.$v_count['value_54'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor2500To10000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_55'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor10000To50000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_56'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor50000To250000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_57'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor250000To500000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_58'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor500000To1000000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_59'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor1000000To3000000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_60'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimFor3000000To5000000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_61'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesMainClaimOver5000000_Output.'</td>
				<td width="100" align="right">'.$v_count['value_62'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYear0To1_Output.'</td>
				<td width="100" align="right">'.$v_count['value_63'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYear1To2_Output.'</td>
				<td width="100" align="right">'.$v_count['value_64'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYear2To3_Output.'</td>
				<td width="100" align="right">'.$v_count['value_65'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYear3To5_Output.'</td>
				<td width="100" align="right">'.$v_count['value_66'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYear5To10_Output.'</td>
				<td width="100" align="right">'.$v_count['value_67'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_NumberOfPersonCasesWithYearOver10_Output.'</td>
				<td width="100" align="right">'.$v_count['value_68'].'</td>
			</tr>


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
				<td width="527">'.$formText_NumberOfActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.$v_count['value_9'].'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_MainClaimInActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_10'], 2, "," ," ").'</td>
			</tr>
			<tr>
				<td width="527">'.$formText_TotalClaimInActiveCasesInReminderlevel_Output.'</td>
				<td width="100" align="right">'.number_format($v_count['value_11'], 2, "," ," ").'</td>
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
SELECT MIN(c1.warning_case_created_date) AS first_date FROM collecting_company_cases AS c1 WHERE IFNULL(c1.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND c1.warning_case_created_date <= CURDATE()
UNION
SELECT MIN(c2.collecting_case_created_date) AS first_date FROM collecting_company_cases AS c2 WHERE IFNULL(c2.collecting_case_created_date, '0000-00-00') <> '0000-00-00' AND c2.collecting_case_created_date <= CURDATE()
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
	<div class="popupformTitle"><?php echo $formText_CreateCollectingCasesStatisticsReport_Output;?></div>
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
