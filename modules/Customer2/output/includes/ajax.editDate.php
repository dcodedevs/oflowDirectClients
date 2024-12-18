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
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_result&inc_obj=ajax&inc_act=editDate";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">

	<div class="inner">
		<div class="line">
            <div class="lineTitle"><?php echo $formText_DateFrom_Output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput dateFromPopup botspace datepicker" name="date" value="<?php if($_POST['filter_date_from'] != "") echo date("d.m.Y", strtotime($_POST['filter_date_from'])); ?>" required autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>
		<div class="line">
            <div class="lineTitle"><?php echo $formText_DateTo_Output; ?></div>
            <div class="lineInput">
                <input type="text" class="popupforminput dateToPopup botspace datepicker" name="date" value="<?php if($_POST['filter_date_to'] != "") echo date("d.m.Y", strtotime($_POST['filter_date_to'])); ?>" required autocomplete="off">
            </div>
            <div class="clear"></div>
        </div>

		<div class="line">
            <div class="lineTitle"><?php echo $formText_ProjectType_output; ?></div>
            <div class="lineInput">
				<select class="project_type popupforminput botspace" name="project_type" autocomplete="off">
					<option value=""><?php echo $formText_All_output;?></option>
					<option value="1" <?php if($_POST['project_type'] == 1) echo 'selected';?>><?php echo $formText_RepeatingOrder_output;?></option>
					<option value="2" <?php if($_POST['project_type'] == 2) echo 'selected';?>><?php echo $formText_OneTimeProject_output;?></option>
					<option value="3" <?php if($_POST['project_type'] == 3) echo 'selected';?>><?php echo $formText_ContinuingProject_output;?></option>
				</select>
            </div>
            <div class="clear"></div>
        </div>

		<div class="line">
            <div class="lineTitle"><?php echo $formText_ProjectLeader_output; ?></div>
            <div class="lineInput">
				<?php
					$s_sql = "SELECT * FROM contactperson  WHERE content_status < 2 AND type = ? ORDER BY name ASC";
					$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
					$employees = ($o_query ? $o_query->result_array() : array());
				?>
				<select class="project_leader popupforminput botspace" name="project_leader" autocomplete="off">
					<option value=""><?php echo $formText_All_output;?></option>
					<?php foreach($employees as $employee) { ?>
						<option value="<?php echo $employee['id']?>" <?php if($employee['id'] == $_POST['project_leader']) echo 'selected';?>><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?></option>
					<?php } ?>
				</select>
			</div>
            <div class="clear"></div>
        </div>
	</div>
	<div id="popup-validate-message" style="display:none;"></div>
	<div class="popupformbtn">
        <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
        <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
	</div>
</form>
</div>
<style>
.popupeditbox label.error {
	display: none !important;
}
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
</style>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(function() {
	$(".datepicker").datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });
	$("form.output-form").validate({
		submitHandler: function(form) {
            var dateFromPopup = $(".dateFromPopup").val();
            var dateToPopup = $(".dateToPopup").val();
			var project_type = $(".project_type").val();
			var project_leader = $(".project_leader").val();
            $(".filter_date_from").html(dateFromPopup);
            $(".filter_date_to").html(dateToPopup);
            $(".projectTypeSelector").val(project_type);
            $(".projectLeaderSelector").val(project_leader);
            out_popup.addClass("close-reload");
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
