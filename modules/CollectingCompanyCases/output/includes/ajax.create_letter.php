<?php
$collecting_case_id = $_POST['caseId'];

$s_sql = "select * from collecting_company_cases where id = ?";
$o_query = $o_main->db->query($s_sql, array($collecting_case_id));
$collecting_case = $o_query ? $o_query->row_array() : array();

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_calculate_interest.php");
		include_once(__DIR__."/../../../CollectingCompanyCases/output/includes/fnc_generate_pdf.php");
        $collecting_cases_pdftext_id = $_POST['collecting_cases_pdftext_id'];
        $add_due_days = $_POST['add_due_days'];
        $update_interest = $_POST['update_interest'];

        $s_sql = "SELECT collecting_cases_pdftext.*  
        FROM collecting_cases_pdftext 
        WHERE id = ? ORDER BY sortnr";
        $o_query = $o_main->db->query($s_sql, array($collecting_cases_pdftext_id));
        $collecting_case_pdftext = ($o_query ? $o_query->row_array() : array());

        if($collecting_case_pdftext && $add_due_days != "" && $add_due_days > 0) {

			if($update_interest){
				$noInterestError = false;
				$s_sql = "DELETE FROM collecting_cases_interest_calculation WHERE collecting_company_case_id = ? ";
				$o_query = $o_main->db->query($s_sql, array($collecting_case['id']));

				$currentClaimInterest = 0;
				$interestArray = calculate_interest(array(), $collecting_case);
				$totalInterest = 0;
				foreach($interestArray as $interest_index => $interest) {
					$interest_index_array = explode("_", $interest_index);
					$claimline_id = intval($interest_index_array[2]);

					$interestRate = $interest['rate'];
					$interestAmount = $interest['amount'];
					$interestFrom = date("Y-m-d", strtotime($interest['dateFrom']));
					$interestTo = date("Y-m-d", strtotime($interest['dateTo']));

					$s_sql = "INSERT INTO collecting_cases_interest_calculation SET created = NOW(), date_from = '".$o_main->db->escape_str($interestFrom)."',
					date_to = '".$o_main->db->escape_str($interestTo)."', amount = '".$o_main->db->escape_str($interestAmount)."', rate = '".$o_main->db->escape_str($interestRate)."', collecting_company_case_id = '".$o_main->db->escape_str($collecting_case['id'])."',
					collecting_company_cases_claim_line_id = '".$o_main->db->escape_str($claimline_id)."'";
					$o_query = $o_main->db->query($s_sql, array());
					$totalInterest += $interestAmount;
				}

				$s_sql = "SELECT * FROM collecting_company_cases_claim_lines WHERE collecting_company_case_id = '".$o_main->db->escape_str($collecting_case['id'])."' AND claim_type = 8 ORDER BY created DESC";
				$o_query = $o_main->db->query($s_sql, array($collecting_case['id']));
				$interest_claim_line = ($o_query ? $o_query->row_array() : array());
				if($interest_claim_line) {
					$s_sql = "UPDATE collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
					collecting_company_case_id = '".$o_main->db->escape_str($collecting_case['id'])."'
					WHERE id = '".$o_main->db->escape_str($interest_claim_line['id'])."'";
					$o_query = $o_main->db->query($s_sql);
				} else {
					$s_sql = "INSERT INTO collecting_company_cases_claim_lines SET updated = NOW(), amount = '".$o_main->db->escape_str($totalInterest)."',
					collecting_company_case_id = '".$o_main->db->escape_str($collecting_case['id'])."', claim_type = 8, name= '".$o_main->db->escape_str($formText_Interest_output)."'";
					$o_query = $o_main->db->query($s_sql);
				}
			}
			$dueDate = date("Y-m-d", strtotime("+".$add_due_days." days", time()));

			$s_sql = "UPDATE collecting_company_cases SET due_date = '".$o_main->db->escape_str($dueDate)."' WHERE id = '".$o_main->db->escape_str($collecting_case['id'])."'";
			$o_query = $o_main->db->query($s_sql);

            $single_task_array = array();
            $single_task_array['collecting_case_pdftext'] = $collecting_case_pdftext;

            $result = generate_pdf($collecting_case['id'], 0, 0, $single_task_array);
			// var_dump($result);
        }
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;

	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_cases_objection WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_objection WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$type_messages = array("", $formText_WantsInvoiceCopy_output,$formText_WantsDefermentOfPayment_output,$formText_WantsInstallmentPayment_output,$formText_HasAnObjectionToTheAmount_output,$formText_HasAnObjectionToTheProductService_output);


$s_sql = "SELECT creditor.*  FROM creditor WHERE creditor.id = ?";
$o_query = $o_main->db->query($s_sql, array($collecting_case['creditor_id']));
$creditor = ($o_query ? $o_query->row_array() : array());


$s_sql = "SELECT collecting_cases_pdftext.*  FROM collecting_cases_pdftext WHERE use_for_single_letter_creation = 1 ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$collecting_cases_pdftext = ($o_query ? $o_query->result_array() : array());
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=create_letter";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
		<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">


		<div class="inner">

			<div class="line">
				<div class="lineTitle"><?php echo $formText_Letter_Output; ?></div>
				<div class="lineInput">
					<select name="collecting_cases_pdftext_id" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php 
                        foreach($collecting_cases_pdftext as $collecting_case_pdftext) {
                            ?>
                            <option value="<?php echo $collecting_case_pdftext['id']?>"><?php echo $collecting_case_pdftext['name'];?></option>
                            <?php
                        }
                        ?>
                    </select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_AddDueDays_Output; ?></div>
				<div class="lineInput">
                <input type="text" value="14" class="popupforminput botspace" name="add_due_days" autocomplete="off"/>
                </div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_UpdateInterest_Output; ?></div>
				<div class="lineInput">
                    <select name="update_interest" required>
                        <option value="1"><?php echo $formText_Yes_output;?></option>
                        <option value="2"><?php echo $formText_No_output;?></option>
                    </select>
                </div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
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

$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			// data.imagesToProcess = imagesToProcess;
			// data.imagesHandle = imagesHandle;
			// data.images = images;

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
				success: function (data) {
					fw_loading_end();
					if(data.error !== undefined)
					{
						$("#popup-validate-message").html("");
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							$("#popup-validate-message").append(value);
						});
						$("#popup-validate-message").show()
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					}
				}
			}).fail(function() {
				$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
				$("#popup-validate-message").show();
				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				fw_loading_end();
			});
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
