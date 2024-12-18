<?php

if(isset($_POST['employeeId']) && $_POST['employeeId'] > 0 && isset($_POST['employerId']) && $_POST['employerId'] > 0)
{
	$s_sql = "SELECT * FROM people_employerconnection WHERE employerId = ? AND peopleId = ?";
	$o_result = $o_main->db->query($s_sql, array($_POST['employerId'], $_POST['employeeId']));
	if($o_result && $o_result->num_rows() > 0) $v_data = $o_result->row_array();
}

if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if($v_data) {
			$s_sql = "UPDATE people_employerconnection SET
			updated = now(),
			updatedBy = ?,
			accountingEmployeeId = ?
			WHERE employerId = ? AND peopleId = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['accountingEmployeeId'], $_POST['employerId'], $_POST['employeeId']));
			$fw_return_data = $_POST['employeeId'];
		} else {
			$s_sql = "INSERT INTO people_employerconnection SET
			created = now(),
			createdBy = ?,
			accountingEmployeeId = ?,
			employerId = ?,
			peopleId = ?";
			$o_main->db->query($s_sql, array($variables->loggID, $_POST['accountingEmployeeId'], $_POST['employerId'], $_POST['employeeId']));
			$fw_return_data = $_POST['employeeId'];
		}

		// $peopleId - needed for sync script
		$peopleId = $_POST['employeeId'];
		include("sync_people.php");
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$fw_return_data;
		return;
	} else if(isset($_POST['output_delete'])) {
		if(isset($_GET['cid']) && $_GET['cid'] > 0)
		{
			$s_sql = "DELETE FROM people_employerconnection WHERE id = ?";
			$o_main->db->query($s_sql, array($_GET['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['employeeId'];
		return;
	}
}

$s_sql = "select * from people_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_employee_accountconfig = ($o_query ? $o_query->row_array() : array());

?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_employee_employers";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="employeeId" value="<?php print $_POST['employeeId'];?>">
		<div class="inner">
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Employer_Output; ?></div>
				<div class="lineInput">
					<?php
					$resources = array();

					$s_sql = "SELECT * FROM repeatingorder_employers WHERE content_status < 2 ORDER BY name ASC";
					$o_query = $o_main->db->query($s_sql);
					$resources = ($o_query ? $o_query->result_array() : array());
					?>
					<select name="employerId" required>
						<option value=""><?php echo $formText_None_output;?></option>
						<?php foreach($resources as $resource) { ?>
						<option value="<?php echo $resource['id']?>" <?php if($_POST['employerId'] == $resource['id']) echo 'selected';?>><?php echo $resource['name']?></option>
						<?php } ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
			<div class="lineTitle"><?php echo $formText_AccountingEmployeeId_Output; ?></div>
			<div class="lineInput"><input class="popupforminput botspace" name="accountingEmployeeId" type="text" required value="<?php echo $v_data['accountingEmployeeId'];?>" autocomplete="off"></div>
			<div class="clear"></div>
			</div>
		</div>
		<div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
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

<!-- <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
<script type="text/javascript">
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: $(form).serialize(),
				success: function (data) {
					if(data.redirect_url !== undefined) window.location = data.redirect_url;
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
<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
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
.popupform input.popupforminput, .popupform textarea.popupforminput, .col-md-8z input {
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
	border-radius: 4px;
	border:0px none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
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
