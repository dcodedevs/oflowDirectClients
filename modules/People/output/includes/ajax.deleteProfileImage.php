<?php
if(isset($_POST['cid'])){ $cid = $_POST['cid']; } else { $cid = NULL; }

$s_sql = "SELECT contactperson.* FROM contactperson
WHERE contactperson.id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$v_data = $o_query ? $o_query->row_array() : array();

if(isset($_POST['output_form_submit']))
{
	$o_query = $o_main->db->query("SELECT * FROM accountinfo");
	$v_accountinfo = $o_query ? $o_query->row_array() : array();

	$b_registered_user = FALSE;

	$userdetail_response = array();
	if(!function_exists("APIconnectorAccount")) include(__DIR__."/APIconnector.php");
	$v_response = json_decode(APIconnectorAccount("userinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], array('SEARCH_USERNAME'=>$v_data['email'], 'COMPANY_ID'=>$companyID)), TRUE);
	// $v_response = json_decode(APIconnectorUser("userdetailsget", $_COOKIE['username'], $_COOKIE['sessionID'], array('USERNAME'=>$v_data['email'])), TRUE);

	if(!array_key_exists("error", $v_response))
	{
		$b_registered_user = TRUE;
		$userdetail_response = $v_response['data'];
		//if($variables->loggID == $v_data['email'])
	} else {
		$b_registered_user = FALSE;
	}

	if($b_registered_user)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_URL, 'http://pics.getynet.com/serverapi/commands/profileimagedelete.php');
		$post_array = array("delete_images"=>$userdetail_response['image']);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
		$s_response = curl_exec($ch);
		$v_response = json_decode($s_response, TRUE);
		if($v_response === null || isset($v_response['error']))
		{} else {
			APIconnectorAccount("userinfoset", $v_accountinfo['accountname'], $v_accountinfo['password'], array('IMAGE'=>'', 'ID'=>$userdetail_response['userID']));
		}
	} else {
		$v_response = json_decode(APIconnectorAccount("user_image_upload_delete", $v_accountinfo['accountname'], $v_accountinfo['password'], array('username'=>$v_data['email'])), TRUE);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_URL, 'http://pics.getynet.com/serverapi/commands/profileimagedelete.php');
			$post_array = array("delete_images"=>$v_response['image']);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_array);
			$s_response = curl_exec($ch);
			$v_response = json_decode($s_response, TRUE);
			if($v_response === null || isset($v_response['error']))
			{
				//$v_return['error'] = $formText_ImageUploadFailed_Output;
			}
		}
	}
} else {
	?>
	<div class="profileEditForm popupform">
		<div id="popup-validate-message"></div>
		<form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=deleteProfileImage";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="languageID" value="<?php echo $languageID?>">
		<input type="hidden" name="cid" value="<?php echo $cid?>">
		<div class="confirm-text"><?php echo $formText_AreYouSureYouWantToDeleteProfilePicture_Output;?></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
			<input type="submit" class="fw_button_color" name="sbmbtn" value="<?php echo $formText_Delete_Output; ?>">
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
					if(data.error !== undefined)
					{
						fw_loading_end();
						var errorMessage = "";
						$.each(data.error, function(index, value){
							errorMessage += value+"<br/>";
						});
						$("#popup-validate-message").html(errorMessage, true);
						$("#popup-validate-message").show();
						$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
					} else {
						window.location.reload();
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
