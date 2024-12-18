<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['type']) && $_POST['type'] == 0){
			$s_sql = "UPDATE peoplesalary_group SET
			type = 1";
			$o_main->db->query($s_sql);
		}
		$s_fields = ",
		name = '".$o_main->db->escape_str($_POST['name'])."',
		type = '".$o_main->db->escape_str($_POST['type'])."',
		peopleId = '".$o_main->db->escape_str($_POST['employeeid'])."'";
		if(isset($_POST['salarygroupid']) && $_POST['salarygroupid'] > 0)
		{
			$s_sql = "UPDATE peoplesalary_group SET
			updated = now(),
			updatedBy = ?".$s_fields."
			WHERE id = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['salarygroupid']));
		} else {
			$s_sql = "INSERT INTO peoplesalary_group SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy = ?".$s_fields;
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID));
			$fw_return_data = $o_main->db->insert_id();
		}
		// $peopleId - needed for sync script
		if(isset($_POST['employeeid'])){ $peopleId = $_POST['employeeid']; }
		include("sync_people.php");

		$fw_return_data = $_POST['employeeid'];
		// print $s_sql;
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	} else if(isset($_POST['output_delete'])) {
		if(isset($_GET['cid']) && $_GET['cid'] > 0)
		{
			$s_sql = "DELETE peoplesalary_group, peoplesalary FROM peoplesalary_group LEFT OUTER JOIN peoplesalary ON peoplesalary.peoplesalary_group_id = peoplesalary_group.id WHERE peoplesalary_group.id = ?";
			$o_main->db->query($s_sql, array($_GET['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['employeeId'];
		return;
	}
}
if(isset($_POST['salarygroupid']) && $_POST['salarygroupid'] > 0)
{
	$s_sql = "SELECT * FROM peoplesalary_group
	WHERE peoplesalary_group.peopleId = ? AND peoplesalary_group.id = ?";
	$o_result = $o_main->db->query($s_sql, array($_POST['employeeid'], $_POST['salarygroupid']));
	$v_data = $o_result ? $o_result->row() : array();

	$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate WHERE id = ?", array($v_data->standardWageRateId));
	$wageData = $wageData_sql ? $wageData_sql->row_array() : array();
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_salary_group";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="employeeid" value="<?php print $_POST['employeeid'];?>">
		<input type="hidden" name="salarygroupid" value="<?php print $_POST['salarygroupid'];?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Type_Output; ?></div>
				<div class="lineInput">
					<input type="radio" class="type" name="type" value="0" autocomplete="off" <?php if($v_data->type == 0 || $v_data == null){ ?> checked<?php } ?> id="standardWage"><label for="standardWage"><?php echo $formText_Default_output?></label>
					<input type="radio" class="type" name="type" value="1" autocomplete="off" <?php if($v_data->type == 1){ ?> checked<?php } ?> id="individualWage"><label for="individualWage"><?php echo $formText_Additional_output?></label>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line ">
				<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
				<div class="lineInput">
					<input class="popupforminput botspace nameInput" name="name" type="text" value="<?php if($v_data->name != "") { echo $v_data->name ; } ?>" autocomplete="off">
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<style>
.individualWage {
	display: none;
}
</style>
<div id="popupeditbox2" class="popupeditbox">
	<span class="button b-close"><span>X</span></span>
	<div id="popupeditboxcontent2"></div>
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

<!-- <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">
$(function() {
	<?php if(!$v_data) { ?>
		$(".type").change(function(){
			var value = $(this).val();
			if(value == 0){
				$(".nameInput").val('<?php echo $formText_DefaultSalary_output;?>');
			} else if (value == 1){
				$(".nameInput").val('<?php echo $formText_AdditionalSalary_output;?>');
			}
		})
		$(".type:checked").change();
	<?php } ?>
	$("form.output-worker-form").validate({
		ignore: [],
		submitHandler: function(form) {
			fw_loading_start();
			$("#popup-validate-message").html("");
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					if(data.error !== undefined)
					{
						$.each(data.error, function(index, value){
							$("#popup-validate-message").append("<div>"+value+"</div>").show();
						});
						fw_loading_end();
					} else {
						if(data.redirect_url !== undefined){
							window.location = data.redirect_url;
						} else {
							fw_loading_end();
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
				$(".error-span").hide();
			}
			setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
		},
		errorPlacement: function(error, element) {
		    if (element.attr("name") == "wageId" )
		        $("<span class='error-span'><?php echo $formText_SelectTheWage_output;?></span>").insertAfter(".selectWage");
		}
	});
});
</script>
<style>
.popupform .descriptionText {
	padding: 10px 0px;
}
.error-span {
	color: #c11;
	margin-left: 10px;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}
</style>
