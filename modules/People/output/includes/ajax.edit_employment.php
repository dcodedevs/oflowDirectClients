<?php
$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'People', // module id in which this block is used
	  'id' => 'articleinsfileupload',
	  'upload_type' => 'file',
	  'content_table' => 'people_employment',
	  'content_field' => 'contract_file',
	  'content_id' => $_POST['cid'],
	  'content_module_id' => $module_data['uniqueID'], // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	)
);
if($moduleAccesslevel > 10)
{
	if($_POST['output_delete'] == 1){
		if($_GET['cid'] > 0){
			$s_sql = "DELETE FROM people_employment
			WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($_GET['cid']));

		}
	}
	if(isset($_POST['output_form_submit']))
	{
		if(intval($_POST['peopleId']) == 0){
			$fw_error_msg[] = $formText_MissingPeopleId_output;
			return;
		}
		$stopped_date = "";
		$start_date = "";

		$sql_where = "";
		if(isset($_POST['cid']) && $_POST['cid'] > 0) {
			$sql_where = " AND id <> '".$o_main->db->escape_str($_POST['cid'])."'";
		}

		$s_sql = "SELECT * FROM people_employment WHERE content_status < 2 AND peopleId = '".$o_main->db->escape_str($_POST['peopleId'])."' ".$sql_where." ORDER BY stopped_date DESC";
		$o_query = $o_main->db->query($s_sql);
		$last_employment = ($o_query ? $o_query->row_array() : array());

		if($_POST['start_date'] != "") {
			$start_date = date("Y-m-d", strtotime($_POST['start_date']));
		} else {
			$fw_error_msg[] = $formText_StartDateMissing_output;
			return;
		}

		if($_POST['stopped_date'] != ""){
			$stopped_date = date("Y-m-d", strtotime($_POST['stopped_date']));
			if(strtotime($stopped_date) <= strtotime($start_date)) {
				$fw_error_msg[] = $formText_StoppedDateCanNotBeLessThanStartDate_output;
				return;
			}
		}
		$lastStoppedTime = 0;
		if($last_employment['stopped_date'] != "0000-00-00" && $last_employment['stopped_date'] != "") {
			$lastStoppedTime = strtotime($last_employment['stopped_date']);
		}
		if(isset($_POST['cid']) && $_POST['cid'] > 0) {
			$s_sql = "UPDATE people_employment SET
			updated = now(),
			updatedBy = ?,
			moduleID = ?,
			start_date = ?,
			stopped_date = ?,
			contract_type_id = ?,
			job_time_percent = ?,
			peopleId = ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $moduleID, $start_date, $stopped_date, $_POST['contract_type_id'], $_POST['job_time_percent'], $_POST['peopleId'], $_POST['cid']));
			$fw_return_data = $_POST['cid'];
		} else {
			if(!$last_employment || $lastStoppedTime > 0) {
				if(strtotime($start_date) > $lastStoppedTime) {
		            $s_sql = "INSERT INTO people_employment SET
					updated = now(),
					updatedBy = ?,
					moduleID = ?,
					start_date = ?,
					stopped_date = ?,
					contract_type_id = ?,
					job_time_percent = ?,
					peopleId = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $moduleID,  $start_date, $stopped_date, $_POST['contract_type_id'], $_POST['job_time_percent'], $_POST['peopleId']));
					$fw_return_data = $o_main->db->insert_id();
				} else {
					$fw_error_msg[] = $formText_CanNotPutStartTimeBeforePreviousEmploymentStoppedDate_output;
					return;
				}
			} else {
				$fw_error_msg[] = $formText_CanNotAddNewEmploymentAsPreviousOneStillActive_output;
				return;
			}
        }
		if($fw_return_data > 0){
			foreach($fwaFileuploadConfigs as $fwaFileuploadConfig) {
				$fieldName = $fwaFileuploadConfig['id'];
				$fwaFileuploadConfig['content_id'] = $fw_return_data;
			// foreach($_POST['$fieldNames'] as $fieldName){
				include( __DIR__ . "/fileupload_popup/contentreg.php");
			}
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	}
}
if(isset($_POST['cid']) && $_POST['cid'] > 0) {
	$s_sql = "SELECT * FROM people_employment WHERE id = ? ORDER BY start_date ASC";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$employementData = ($o_query ? $o_query->row_array() : array());
}

$s_sql = "SELECT * FROM people_contract_types ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$contract_types = ($o_query ? $o_query->result_array() : array());
?>
<div class="popupform popupform-<?php echo $groupId;?>">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile="
    .$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=ajax&inc_act=".$_GET['inc_act'];?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="peopleId" value="<?php echo $_POST['peopleId']?>">
        <input type="hidden" name="cid" value="<?php echo $_POST['cid']?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
        <div class="popupformTitle"><?php  echo $formText_AddEditEmployment_output;   ?></div>

        <div class="inner">
            <div class="line">
				<div class="lineTitle"><?php echo $formText_StartDate_Output;?></div>
				<div class="lineInput">
                    <input type="text" name="start_date" autocomplete="off" class="popupforminput botspace datepicker" required value="<?php if($employementData['start_date'] != "" && $employementData['start_date'] != "0000-00-00") { echo $employementData['start_date']; }?>"/>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_StoppedDate_Output;?></div>
				<div class="lineInput">
                    <input type="text" name="stopped_date" autocomplete="off" class="popupforminput botspace datepicker" value="<?php if($employementData['stopped_date'] != "" && $employementData['stopped_date'] != "0000-00-00") { echo $employementData['stopped_date']; }?>"/>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_JobTimePercent_Output;?></div>
				<div class="lineInput">
                    <input type="text" name="job_time_percent" autocomplete="off" class="popupforminput botspace" value="<?php echo $employementData['job_time_percent'];?>"/>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_ContractType_Output;?></div>
				<div class="lineInput">
					<select name="contract_type_id" class="popupforminput botspace" >
						<option value=""><?php echo $formText_None_output;?></option>
						<?php
						foreach($contract_types as $contract_type) { ?>
							<option value="<?php echo $contract_type['id'];?>" <?php if($contract_type["id"] == $employementData['contract_type_id']) echo 'selected';?>><?php echo $contract_type['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_ContractFile_Output;?></div>
				<div class="lineInput">
					<?php
					$fwaFileuploadConfig = $fwaFileuploadConfigs[0];
					 require __DIR__ . '/fileupload_popup/output.php';
					?>
				</div>
				<div class="clear"></div>
			</div>

    		<div class="popupformbtn">
    			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
				<input type="submit" name="sbmbtn" class="saveFiles" value="<?php echo $formText_Save_Output; ?>">
    		</div>
        </div>
    </form>
</div>
<?php

$s_path = $variables->account_root_url;

$v_script = array(
  'modules/'.$module.'/output/elementsOutput/jquery.validate/jquery.validate.min.js',
);

foreach($v_script as $s_item)
{
  $l_time = filemtime(BASEPATH.$s_item);
  ?><script type="text/javascript" src="<?php echo $s_path.$s_item.'?v='.$l_time;?>"></script><?php
}

?>
<script type="text/javascript">
function callBackOnUploadAll(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_Save; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.popupformbtn .saveFiles').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){

}
$(function() {
	$(".datepicker").datepicker({
		dateFormat: "dd.mm.yy",
		firstDay: 1,
		changeMonth: true,
    	changeYear: true
	});
    $(".popupform-<?php echo $groupId;?> form.output-form").validate({
        ignore: [],
        submitHandler: function(form) {
            $("#popup-validate-message").html("");
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (json) {
                    fw_loading_end();
					if(json.error !== undefined)
					{
						$.each(json.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_click_instance = fw_changes_made = false;
						fw_loading_end();
						$("#popup-validate-message").show();
					} else {
	                    if(json.redirect_url !== undefined)
	                    {
	                        out_popup.addClass("close-reload");
	                        out_popup.close();
	                    }
					}
                }
            }).fail(function() {
                $(".popupform-<?php echo $groupId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $(".popupform-<?php echo $groupId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $groupId;?> #popupeditbox').css('height', $('.popupform-<?php echo $groupId;?> #popupeditboxcontent').height());
                fw_loading_end();
            });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $(".popupform-<?php echo $groupId;?> #popup-validate-message").html(message);
                $(".popupform-<?php echo $groupId;?> #popup-validate-message").show();
                $('.popupform-<?php echo $groupId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $(".popupform-<?php echo $groupId;?> #popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        },
        errorPlacement: function(error, element) {
            if(element.attr("name") == "customerId") {
                error.insertAfter(".popupform-<?php echo $groupId;?> .selectCustomer");
            }
            if(element.attr("name") == "projectLeader") {
                error.insertAfter(".popupform-<?php echo $groupId;?> .selectEmployee");
            }
            if(element.attr("name") == "projectOwner") {
                error.insertAfter(".popupform-<?php echo $groupId;?> .selectOwner");
            }
        },
        messages: {
            customerId: "<?php echo $formText_SelectTheCustomer_output;?>",
            projectLeader: "<?php echo $formText_SelectProjectLeader_output;?>",
            projectOwner: "<?php echo $formText_SelectProjectOwner_output;?>"
        }
    });
});
</script>
