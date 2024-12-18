<?php
if(isset($_POST['cid'])){ $cid = $_POST['cid']; } else { $cid = NULL; }
$focus_w_limit = 560;
$focus_h_limit = 460;

$s_sql = "SELECT contactperson.* FROM contactperson
WHERE contactperson.id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
$v_data = $o_query ? $o_query->row_array() : array();


// if(!function_exists("APIconnectorUser")) include(__DIR__."/includes/APIconnector.php");
// $v_response = json_decode(APIconnectorUser("userprofileget", $_COOKIE['username'], $_COOKIE['sessionID'], array()), TRUE);
// if(!array_key_exists("error", $v_response))
// {
// 	$v_user_external = $v_response['data'];
// 	$v_user_external['fullname'] = preg_replace('/\s+/', ' ', $v_user_external['name'].' '.$v_user_external['middle_name'].' '.$v_user_external['last_name']);
// 	$v_profile_image = json_decode(urldecode($v_user_external['image']),TRUE);
// 	$v_user_external['profile_image'] = ($v_profile_image[0] != "" ? "https://pics.getynet.com/profileimages/".$v_profile_image[0] : "");
// }
if(isset($_POST['output_form_submit'])) {
} else {
?>
<div class="profileEditForm popupform">
  <div id="popup-validate-message"></div>
  <form class="output-form" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editPageCover";?>" method="post">
	  <input type="hidden" name="fwajax" value="1">
  	<input type="hidden" name="fw_nocss" value="1">
  	<input type="hidden" name="output_form_submit" value="1">
    <input type="hidden" name="languageID" value="<?php echo $languageID?>">
    <input type="hidden" name="cid" value="<?php echo $cid?>">
    <div class="line">
      <div class="lineTitle"><?php echo $formText_BannerImage_Output; ?></div>
      <div class="lineInput">
        <?php foreach (json_decode($v_data['profileBannerImage']) as $image):
            $officeSpaceImage = $image[1][0];
            if($officeSpaceImage == "") {
                $officeSpaceImage = $image[1][0];
            }
			$field_ui_id = "upload_".$image[4];
        ?>
            <div class="office-image">
                <div class="office-image-img">
                    <img style="width:200px;" src="../<?php echo $officeSpaceImage; ?>" />
                </div>
                <div class="office-image-button item">
					<a href="#" class="deleteBannerImage editBtnIcon" data-image-upload-id="<?php echo $image[4]; ?>" data-content-id="<?php echo $cid;?>"><?php echo $formText_DeleteImage_output;?></a>
				</div>
            </div>
        <?php endforeach; ?>
        <a href="#" class="addImagesBtn" <?php if(count(json_decode($v_data['profileBannerImage'])) >0){ ?> style="display: none;" <?php } ?>>
          <?php echo $formText_AddImage_output; ?></a>
      </div>
      <div class="clear"></div>
      <br/>
    </div>

      <div class="popupformbtn">
          <button type="button" class="output-btn b-large b-close fw_button_not_filled_color"><?php echo $formText_Close_Output;?></button>
      </div>
      <div class="loader-overlay">
          <div class="output-loader"></div>
      </div>
  </form>
  </div>
  <div id="popupeditbox2" class="popupeditbox">
      <span class="button b-close fw_popup_x_color"><span>X</span></span>
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

  <!-- <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script> -->
	<script type="text/javascript">
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
		            if(data.result){
		                if(data.redirect){
		                    window.location = data.redirect;
		                } else {
		                    window.location.reload();
		                }
		            }
		          // if(data.redirect_url !== undefined)
		          // {
		          //   out_popup.addClass("close-reload");
		          //   out_popup.close();
		          //   // fw_load_ajax(data.redirect_url, '', false);//window.location = data.redirect_url;
		          // }
		        }
		      }
		    }).fail(function() {
			  fw_loading_end();
		      $("#popup-validate-message").html("<?php echo $formText_ErrorOccurredSavingContent_Output;?>", true);
		      $("#popup-validate-message").show();
		      $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
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

		function reloadPopup(){
			var data = {
				cid: '<?php echo $cid;?>'
			};
			ajaxCall('editPageCover', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		}
		  $('.addImagesBtn').on('click', function(e) {
			  e.preventDefault();
			  var data = {
				  cid: '<?php echo $cid; ?>'
			  };
			  ajaxCall('addImages', data, function(json) {
				  $('#popupeditboxcontent2').html('');
				  $('#popupeditboxcontent2').html(json.html);
				  out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
				  $("#popupeditbox2:not(.opened)").remove();
				  out_popup.addClass("close-reload");
				  reloadPopup();
			  });
		  });
		  $(".deleteBannerImage").on('click', function(e){
		      e.preventDefault();
		      var self = $(this);

		      if (confirm('<?php echo $formText_ConfirmDelete_output; ?>')){
				  var data = {
		              imageUploadId: self.data('image-upload-id'),
					  cid: '<?php echo $cid; ?>'
				  };
				  ajaxCall('deleteImage', data, function(json) {
					  out_popup.addClass("close-reload");
					  reloadPopup();
				  });
			  }
		  });
	</script>
<?php
}
?>
