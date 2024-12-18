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

if($accessElementAllow_AddEditDeletePeople){

	if(isset($_POST['cid'])){ $cid = $_POST['cid']; }

	$s_sql = "SELECT contactperson.* FROM contactperson
	WHERE contactperson.id = ?";
	$o_query = $o_main->db->query($s_sql, array($cid));
	$v_data = $o_query ? $o_query->row_array() : array();

	if(isset($_POST['output_form_submit']) && $_POST['output_form_submit'])
	{
		$sql = "UPDATE contactperson SET
		updated = now(),
		updatedBy='".$variables->loggID."',
		content_status = 0,
		deactivated = 0
		WHERE id = $cid";
		$o_query = $o_main->db->query($sql);

		// $peopleId - needed for sync script
		$peopleId = $cid;
		include("sync_people.php");
		if($o_query){

		} else {
			$fw_error_msg = array($formText_ErrorUpdatingDatabase_output);
		}

	} else {
		?>
		<div class="profileEditForm popupform">
			<div id="popup-validate-message"></div>
			<form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=activatePeople";?>" method="post">
			<input type="hidden" name="fwajax" value="1">
			<input type="hidden" name="fw_nocss" value="1">
			<input type="hidden" name="output_form_submit" value="1">
			<input type="hidden" name="languageID" value="<?php echo $languageID?>">
			<input type="hidden" name="cid" value="<?php echo $cid?>">
			<div class="confirm-text">
				<?php echo $formText_AreYouSureYouWantToActivateThisPerson_Output;?>
				<br/>
				<b><?php echo $v_data['email']; ?></b>
			</div>
			<div class="popupformbtn">
				<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
				<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Activate_Output; ?>">
			</div>
		  </form>
		</div>
		<style>
		</style>

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

		<!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
		<script type="text/javascript">
		$("form.output-form").validate({
			submitHandler: function(form){
				fw_loading_start();
				$.ajax({
					url: $(form).attr("action"),
					cache: false,
					type: "POST",
					dataType: "json",
					data: $(form).serialize(),
					success: function (data){
						fw_loading_end();
						if(data.error !== undefined)
						{
							var errorMessage = "";
							$.each(data.error, function(index, value){
								errorMessage += value+"<br/>";
							});
							$("#popup-validate-message").html(errorMessage, true);
							$("#popup-validate-message").show();
							$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
						} else {
							out_popup.close();
							var data = {
								cid: "<?php echo $v_data['id']?>"
							};
							loadView('details', data);
						}
					}
				}).fail(function(){
					fw_loading_end();
					$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
					$("#popup-validate-message").show();
					$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				});
			},
			invalidHandler: function(event, validator){}
		});
		</script>
		<?php
	}
} else {
	echo $formText_YouHaveNoAccess_Output;
} ?>
