<?php

$collecting_case_id = $o_main->db->escape_str($_POST['collecting_case_id']);
$process_step_id = $o_main->db->escape_str($_POST['process_step_id']);
$cid = $o_main->db->escape_str($_POST['cid']);

$sql = "SELECT * FROM collecting_cases WHERE id = $collecting_case_id";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($process_step_id));
$process_step = $o_query ? $o_query->row_array() : array();
if($caseData && $process_step) {
    if($moduleAccesslevel > 10)
    {
    	if(isset($_POST['output_form_submit']))
    	{
    		if(isset($_POST['cid']) && $_POST['cid'] > 0)
    		{
    			$s_sql = "SELECT * FROM collecting_cases_handling_additionaldays_log WHERE id = ?";
    		    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    		    if($o_query && $o_query->num_rows() == 1) {
    				$s_sql = "UPDATE collecting_cases_handling_additionaldays_log SET
    				updated = now(),
    				updatedBy= ?,
                    process_step_id = ?,
                    collecting_case_id = ?,
        			days = ?
    				WHERE id = ?";
    				$o_main->db->query($s_sql, array($variables->loggID, $process_step['id'], $caseData['id'], $_POST['days'], $_POST['cid']));
    			}
    		} else {
    			$s_sql = "INSERT INTO collecting_cases_handling_additionaldays_log SET
    			id=NULL,
    			moduleID = ?,
    			created = now(),
    			createdBy= ?,
                process_step_id = ?,
                collecting_case_id = ?,
    			days= ?";
    			$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $process_step['id'], $caseData['id'], $_POST['days']));
    			$_POST['cid'] = $o_main->db->insert_id();
    		}

    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
    		return;
    	} else if(isset($_POST['output_delete']))
    	{
    		if(isset($_POST['cid']) && $_POST['cid'] > 0)
    		{
    			$s_sql = "DELETE FROM collecting_cases_handling_additionaldays_log WHERE id = ?";
    			$o_main->db->query($s_sql, array($_POST['cid']));
    		}

    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['caseId'];
    		return;
    	}
    }

    $s_sql = "SELECT * FROM collecting_cases_handling_additionaldays_log WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
    $collecting_cases_handling_additionaldays_log = $o_query ? $o_query->row_array() : array();
    ?>
    <div class="popupform">
    <form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=add_additional_days";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
    	<input type="hidden" name="process_step_id" value="<?php print $_POST['process_step_id'];?>">
    	<input type="hidden" name="collecting_case_id" value="<?php print $_POST['collecting_case_id'];?>">

    	<div class="inner">

    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Days_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" autocomplete="off" name="days" value="<?php echo $collecting_cases_handling_additionaldays_log['days']; ?>">
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
<?php } ?>
