<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($_POST['performDate'] != "" && $_POST['name'] != ""){
			$performDate = date("Y-m-d", strtotime($_POST['performDate']));
			if(isset($_POST['cid']) && $_POST['cid'] > 0)
			{
				$s_sql = "SELECT * FROM customer_tasks WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
				if($o_query && $o_query->num_rows() == 1) {
					$s_sql = "UPDATE customer_tasks SET
					updated = now(),
					updatedBy= ?,
					employeeId= ?,
					name = ?,
					description = ?,
					performDate = ?,
					performTime = ?,
					customer_id = ?
					WHERE id = ?";
					$o_main->db->query($s_sql, array($variables->loggID, $_POST['employeeId'], $_POST['name'], $_POST['description'], $performDate, $_POST['performTime'], $_POST['customer_id'], $_POST['cid']));
				}
			} else {
				$s_sql = "INSERT INTO customer_tasks SET
				id=NULL,
				moduleID = ?,
				created = now(),
				createdBy= ?,
				employeeId= ?,
				name = ?,
				description = ?,
				performDate = ?,
				performTime = ?,
				customer_id = ?";
				$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['employeeId'], $_POST['name'], $_POST['description'], $_POST['performDate'], $_POST['performTime'], $_POST['customer_id']));
			}

			$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
			return;
		} else {
			$fw_error_msg = array($formText_MissingFields_output);
			return;
		}
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM customer_tasks WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM customer_tasks WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
$s_sql = "SELECT * FROM contactperson WHERE email =  ? AND type = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_contactperson_type));
if($o_query && $o_query->num_rows()>0) {
    $currentUser = $o_query->row_array();
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_tasks";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="customer_id" value="<?php print $_POST['customer_id'];?>">

	<div class="inner">
		<div class="popupformTitle"><?php echo $formText_AddActivity_output;?></div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace" autocomplete="off" name="name" value="<?php echo $v_data['name'];?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Description_Output; ?></div>
			<div class="lineInput">
				<textarea class="popupforminput botspace" autocomplete="off" name="description"><?php echo $v_data['description'];?></textarea>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_PerformDate_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace datepicker" autocomplete="off" name="performDate" value="<?php if($v_data['performDate'] != "0000-00-00" && $v_data['performDate'] != null) echo date("d.m.Y", strtotime($v_data['performDate']));?>" required>
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_PerformTime_Output; ?></div>
			<div class="lineInput">
				<input type="text" class="popupforminput botspace timepicker" autocomplete="off" name="performTime" value="<?php echo $v_data['performTime'];?>" >
			</div>
			<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_PerformPerson_Output; ?></div>
			<div class="lineInput">
				<select name="employeeId" required class="popupforminput botspace" autocomplete="off">
                    <option value=""><?php echo $formText_Select_output;?></option>
                    <?php
                    $employees = array();
					$s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND type = ? ORDER BY name";
					$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
					if($o_query && $o_query->num_rows()>0) {
					    $employees = $o_query->result_array();
					}
	                foreach($employees as $employee) {
                        ?>
                        <option value="<?php echo $employee['id']?>" <?php if($v_data) { if($v_data['employeeId'] == $employee['id']) echo 'selected';} else if($employee['id'] == $currentUser['id']){ echo 'selected';}?>><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname']?></option>
                        <?php
                    }
                    ?>
                </select>
			</div>
			<div class="clear"></div>
		</div>

	</div>
	<div id="popup-validate-message" style="display:none;"></div>
	<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
</form>
</div>
<style>
	.popupeditbox label.error {
		display: none !important;
	}
</style>
<style>
<?php include(__DIR__."/../elementsOutput/jquery.timepicker.css");?>
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".datepicker").datepicker({
		firstDay: 1,
		beforeShow: function(dateText, inst) {
			$(inst.dpDiv).removeClass('monthcalendar');
		},
		dateFormat: "dd.mm.yy"
	})
	$(".timepicker").timepicker({
		timeFormat: 'H:i',
		minTime: "7:00"
	});
	$("form.output-form").validate({
		submitHandler: function(form) {
            fw_loading_start();
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					fw_loading_end();
					/*if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							fw_info_message_add(_type[0], value);
						});
						fw_info_message_show();
						fw_loading_end();
						fw_click_instance = fw_changes_made = false;
					} else {*/
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					//}
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
