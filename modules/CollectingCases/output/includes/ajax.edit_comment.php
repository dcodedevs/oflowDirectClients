<?php
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE id = ?";
		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		    if($o_query && $o_query->num_rows() == 1) {
				$s_sql = "UPDATE collecting_cases_notesandfiles SET
				updated = now(),
				updatedBy= ?,
				text= ?
				WHERE id = ?";
				$o_main->db->query($s_sql, array($variables->loggID, $_POST['text'], $_POST['cid']));
			}
		} else {
			$s_sql = "INSERT INTO collecting_cases_notesandfiles SET
			id=NULL,
			moduleID = ?,
			created = now(),
			createdBy= ?,
			collecting_case_id = ?,
			text= ?";
			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['caseId'], $_POST['text']));
			$_POST['cid'] = $o_main->db->insert_id();
		}
		$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($_POST['cid']));
		$notesAndFiles = $o_query && $o_query->num_rows()>0 ? $o_query->row_array() : array();

		$imageList = json_decode($notesAndFiles['files']);
		$newImageList = array();
		foreach ($imageList as $image) {
			if(in_array($image['4'], $_POST['images'])){
				array_push($newImageList, $image);
			} else {
				foreach($image[1] as $imageVersion) {
					$file = __DIR__."/../../../../".$imageVersion;
					if (is_file($file)) {
						unlink($file);
						rmdir(dirname($file));
					}
				}
				rmdir(dirname($file."/../"));
			}
		}
		$newImageList = json_encode($newImageList);

		$o_main->db->query("UPDATE collecting_cases_notesandfiles SET files = ? WHERE id = ?", array($newImageList, $notesAndFiles['id']));

		require_once __DIR__ . '/process_uploaded_image_to_entry.php';

		foreach ($_POST['imagesToProcess'] as $key=>$imageToProcess) {
			process_uploaded_image_to_entry($o_main, $imageToProcess, $_POST['imagesHandle'][$key], $notesAndFiles['id']);
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	} else if(isset($_POST['output_delete']))
	{
		if(isset($_POST['cid']) && $_POST['cid'] > 0)
		{
			$s_sql = "DELETE FROM collecting_cases_notesandfiles WHERE id = ?";
			$o_main->db->query($s_sql, array($_POST['cid']));
		}

		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
		return;
	}
}

if(isset($_POST['cid']) && $_POST['cid'] > 0)
{
	$s_sql = "SELECT * FROM collecting_cases_notesandfiles WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    if($o_query && $o_query->num_rows()>0) {
        $v_data = $o_query->row_array();
    }
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_comment";?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
	<input type="hidden" name="caseId" value="<?php print $_POST['caseId'];?>">

	<div class="inner">

		<div class="line">
		<div class="lineTitle"><?php echo $formText_Comment_Output; ?></div>
		<div class="lineInput"><textarea class="popupforminput botspace" name="text" required><?php echo $v_data['text'];?></textarea></div>
		<div class="clear"></div>
		</div>
		<div class="line">
			<div class="lineTitle"><?php echo $formText_Files_Output; ?></div>
			<div class="lineInput">

				<?php
				$files = json_decode($v_data['files']);
				foreach($files as $file) {
					$fileParts = explode('/',$file[1][0]);
					$fileName = array_pop($fileParts);
					$fileParts[] = rawurlencode($fileName);
					$filePath = implode('/',$fileParts);
					$fileUrl = $extradomaindirroot."/../".$file[1][0];
					$fileName = $file[0];
					if(strpos($file[1][0],'uploads/protected/')!==false)
					{
						$fileUrl = $extradomaindirroot."/../".$filePath.'?caID='.$_GET['caID'].'&table=collecting_cases_notesandfiles&field=files&ID='.$v_data['id'];
					}
				?>
					<div class="project-file">
						<div class="project-file-file">
							<a href="<?php echo $fileUrl;?>" download><?php echo $fileName;?></a>
							<span class="output-btn small deleteImage editBtnIcon " data-upload-id="<?php echo $file[4]?>"><span class="glyphicon glyphicon-trash"></span></span>
						</div>
					</div>
					<?php
				}
				?>
			  <?php
			  $s_sql = "SELECT moduledata.* FROM moduledata
			  WHERE moduledata.name = ?";
			  $o_query = $o_main->db->query($s_sql, array("CollectingCases"));
			  $presentationModuleData = $o_query ? $o_query->row_array() : array();
			  $moduleID = $presentationModuleData['id'];

			  $fwaFileuploadConfig = array (
				  'module_folder' => 'CollectingCases', // module id in which this block is used
				  'id' => 'collectingcasefileUpload',
				  'content_table' => 'collecting_cases_notesandfiles',
				  'content_field' => 'files',
				  'content_module_id' => $moduleID, // id of module
				  'dropZone' => 'block',
				  'callback' => 'callbackOnImageUpload',
				  'callbackDelete'=> 'callbackOnImageDelete',
				  'callbackAll' => 'callBackOnUploadAll',
				  'callbackStart' => 'callbackOnStart'
			  );
			  require __DIR__ . '/fileupload9/output.php';
			  ?>
			  <br/>
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

	// New uploaded images to process
	var imagesToProcess = [];
	var imagesHandle = [];
	var images = [];

	<?php foreach($files as $presentationImage){?>
	    images.push(<?php echo $presentationImage['4']?>);
	<?php } ?>

	function callbackOnImageUpload(data) {
	    for (first in data.result) break;
	    var uploaded_image = data.result[first][0];
	    imagesToProcess.push(uploaded_image.upload_id);

	}
	function callbackOnImageDelete(data) {
	    if(data != undefined && data.upload_id != undefined){
	        var index = imagesToProcess.indexOf(data.upload_id);
	        if (index > -1) {
	            imagesToProcess.splice(index, 1);
	            imagesHandle.splice(index, 1);

	            $("#<?php echo $field_ui_id;?>_files .handleInput.imageUpload"+data.upload_id).remove();

	            // $(".office-image-img img").attr("src", $('.office-image-img img').data("defaultimage"));
	        }
	    }
	}

	function callBackOnUploadAll(data) {
	    $('.popupformbtn .saveForm').val('<?php echo $formText_Save_output; ?>').prop('disabled',false);
	};
	function callbackOnStart(data) {
	    $('.popupformbtn .saveForm').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
	};
	$(".deleteImage").off("click").on("click", function(e){
		e.preventDefault();
		var index = images.indexOf($(this).data("upload-id"));
		if (index > -1) {
			images.splice(index, 1);
			$(this).parents(".project-file").remove();
		}
	})
$(function() {
	$("form.output-form").validate({
		submitHandler: function(form) {
			fw_loading_start();
			var formdata = $(form).serializeArray();
			var data = {};
			$(formdata).each(function(index, obj){
				data[obj.name] = obj.value;
			});
			data.imagesToProcess = imagesToProcess;
			data.imagesHandle = imagesHandle;
			data.images = images;

			$.ajax({
				url: $(form).attr("action"),
				cache: false,
				type: "POST",
				dataType: "json",
				data: data,
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
