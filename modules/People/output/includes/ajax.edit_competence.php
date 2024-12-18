<?php
$s_sql = "SELECT * FROM moduledata WHERE name = '".$o_main->db->escape_str($module)."'";
$o_result = $o_main->db->query($s_sql);
$module_data = $o_result ? $o_result->row_array() : array();

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'People', // module id in which this block is used
	  'id' => 'articleinsfileupload',
	  'upload_type' => 'file',
	  'content_table' => 'people_competence',
	  'content_field' => 'files',
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
			$s_sql = "DELETE FROM people_competence
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
		if(intval($_POST['competence_id']) == 0){
			$fw_error_msg[] = $formText_MissingCompetence_output;
			return;
		}

		$start_date = "";


		if($_POST['date'] != "") {
			$start_date = date("Y-m-d", strtotime($_POST['date']));
		}

		if(isset($_POST['cid']) && $_POST['cid'] > 0) {
			$s_sql = "UPDATE people_competence SET
			updated = now(),
			updatedBy = ?,
			moduleID = ?,
			date = ?,
			competence_id = ?,
			peopleId = ?
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $moduleID, $start_date, $_POST['competence_id'], $_POST['peopleId'], $_POST['cid']));
			$fw_return_data = $_POST['cid'];
		} else {
            $s_sql = "INSERT INTO people_competence SET
			updated = now(),
			updatedBy = ?,
			moduleID = ?,
			date = ?,
			competence_id = ?,
			peopleId = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $moduleID,  $start_date, $_POST['competence_id'], $_POST['peopleId']));
			$fw_return_data = $o_main->db->insert_id();
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
	$s_sql = "SELECT * FROM people_competence WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
	$employementData = ($o_query ? $o_query->row_array() : array());
}

$s_sql = "SELECT * FROM people_competence_register ORDER BY name ASC";
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
        <div class="popupformTitle"><?php  echo $formText_AddEditCompetence_output;   ?></div>

        <div class="inner">
            <div class="line">
				<div class="lineTitle"><?php echo $formText_Date_Output;?></div>
				<div class="lineInput">
                    <input type="text" name="date" class="popupforminput botspace datepicker" autocomplete="off" value="<?php if($employementData['date'] != "" && $employementData['date'] != "0000-00-00") { echo date("d.m.Y", strtotime($employementData['date'])); }?>"/>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_Competence_Output;?></div>
				<div class="lineInput">
					<select name="competence_id" class="popupforminput botspace" autocomplete="off" required>
						<option value=""><?php echo $formText_Select_output;?></option>
						<?php
						foreach($contract_types as $contract_type) { ?>
							<option value="<?php echo $contract_type['id'];?>" <?php if($contract_type["id"] == $employementData['competence_id']) echo 'selected';?>><?php echo $contract_type['name'];?></option>
							<?php
						}
						?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
            <div class="line">
				<div class="lineTitle"><?php echo $formText_CompetenceFile_Output;?></div>
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
