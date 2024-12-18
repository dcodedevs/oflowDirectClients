<?php
$o_query = $o_main->db->get_where('auto_task', array('id' => $_POST['cid']));
if($o_query && $o_query->num_rows()>0)
{
	$v_auto_task = $o_query->row_array();
	$v_path = explode('/', $v_auto_task['script_path']);
	$s_script = array_pop($v_path);
	$s_task = array_pop($v_path);
	$s_module = array_pop($v_path);
	$v_auto_task_config = json_decode($v_auto_task['config'], TRUE);

	$l_runtime = strtotime($v_auto_task['next_run']);
} else {
	$s_task = $_POST['task'];
	$s_module = $_POST['module'];
	include(BASEPATH.'modules/'.$s_module.'/'.$s_task.'/config.php');

	$v_auto_task_config['repeat_minutes'] = intval($v_auto_task_config['repeat_minutes']);
	if(0 == $v_auto_task_config['repeat_minutes']) $v_auto_task_config['repeat_minutes'] = 1;

	$s_format = (!empty($v_auto_task_config['runtime_y'])?$v_auto_task_config['runtime_y']:'Y').'-'.
				(!empty($v_auto_task_config['runtime_m'])?$v_auto_task_config['runtime_m']:'m').'-'.
				(!empty($v_auto_task_config['runtime_d'])?$v_auto_task_config['runtime_d']:'d').' '.
				(!empty($v_auto_task_config['runtime_h'])?$v_auto_task_config['runtime_h']:'H').':'.
				(!empty($v_auto_task_config['runtime_i'])?$v_auto_task_config['runtime_i']:'i').':00';
	$s_date = date($s_format);
	$l_runtime = strtotime($s_date);
	while($l_runtime < time())
	{
		$l_runtime = $l_runtime + (1*60);
	}
}

// On form submit
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['delete_item']) && 1 == $_POST['delete_item'])
		{
			$s_sql = "UPDATE auto_task SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."', content_status = 2 WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'";
			$o_query = $o_main->db->query($s_sql);
			if(!$o_query)
			{
				$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredHandlingRequest_Output;
			}
		} else {
			foreach($v_auto_task_config['parameters'] as $s_key => $v_parameter)
			{
				if(1 != $v_parameter['input']) continue;
				$v_auto_task_config['parameters'][$s_key]['value'] = $_POST['input_param_'.$s_key];
			}

			if(isset($v_auto_task) && 0 < $v_auto_task['id'])
			{
				$s_sql = "UPDATE auto_task SET updated = NOW(), updatedBy = '".$o_main->db->escape_str($variables->loggID)."', script_path = '".$o_main->db->escape_str('modules/'.$s_module.'/'.$s_task.'/run.php')."', config = '".$o_main->db->escape_str(json_encode($v_auto_task_config))."' WHERE id = '".$o_main->db->escape_str($v_auto_task['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if(!$o_query)
				{
					$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredHandlingRequest_Output;
				}
			} else {
				$s_sql = "INSERT INTO auto_task SET id = NULL, created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', moduleID = '".$o_main->db->escape_str($moduleID)."', script_path = '".$o_main->db->escape_str('modules/'.$s_module.'/'.$s_task.'/run.php')."', config = '".$o_main->db->escape_str(json_encode($v_auto_task_config))."', next_run = '".$o_main->db->escape_str(date("Y.m.d H:i:s", $l_runtime))."'";
				$o_query = $o_main->db->query($s_sql);
				if(!$o_query)
				{
					$fw_error_msg['error_'.count($fw_error_msg)] = $formText_ErrorOccurredHandlingRequest_Output;
				}
			}
		}

		// Redirect
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output";
		return;
	}
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form second" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_task";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="cid" value="<?php echo $_POST['cid'];?>">
		<input type="hidden" name="task" value="<?php echo $s_task;?>">
		<input type="hidden" name="module" value="<?php echo $s_module;?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_NextRun_Output; ?></div>
                <div class="lineInput">
                    <?php echo date("Y.m.d H:i", $l_runtime);?>
                </div>
                <div class="clear"></div>
            </div>
			<?php
			foreach($v_auto_task_config['parameters'] as $s_key => $v_parameter)
			{
				if(1 != $v_parameter['input']) continue;
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $s_key; ?></div>
					<div class="lineInput">
						<input type="text" class="popupforminput botspace" name="input_param_<?php echo $s_key;?>" value="<?php echo $v_parameter['value']; ?>" autocomplete="off">
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
			?>
		</div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	// Submit form
    $("form.output-form.second").validate({
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
						$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                		$("#popup-validate-message").show();
					} else {
						out_popup.addClass("close-reload");
						out_popup.close();
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
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
});

</script>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    border:1px solid #e8e8e8;
    position:relative;
}
.invoiceEmail {
    display: none;
}
.selectDivModified {
    display:block;
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

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}

.checking-keycard-progress {
    display:none;
}

.remove-card-button-confirm {
    color:red;
    border-color:red;
    display:none;
}

.remove-card-button-confirm:hover {
    color:red;
    border-color:red;
}
</style>
