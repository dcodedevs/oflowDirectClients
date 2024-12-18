<?php
$ownercompanyId = isset($_POST['ownercompanyId']) ? $_POST['ownercompanyId'] : 0;

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($ownercompanyId) {
            $s_sql = "UPDATE ownercompany SET
            updated = now(),
            updatedBy= ?,
            export2ScriptFolder= ?,
			export2SendMethod = ?,
			export2FtpUsername = ?,
			export2FtpPassword = ?,
			export2FtpHost = ?,
			export2FtpPort = ?,
			export2FtpPath = ?,
			export2FtpUseSSL = ?
            WHERE id = ?";
            //var_dump($s_sql);
            $o_main->db->query($s_sql, array(
				$variables->loggID,
				$_POST['export2ScriptFolder'],
				$_POST['export2SendMethod'],
				$_POST['export2FtpUsername'],
				$_POST['export2FtpPassword'],
				$_POST['export2FtpHost'],
				$_POST['export2FtpPort'],
				$_POST['export2FtpPath'],
				$_POST['export2FtpUseSSL'],
				$ownercompanyId
			));
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
        }
	}
}

if($ownercompanyId) {
    $sql = "SELECT * FROM ownercompany WHERE id = $ownercompanyId";
    $result = $o_main->db->query($sql);
    if($result && $result->num_rows() > 0) $officeSpaceData = $result->row();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editExport2Details";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Export2ScriptFolder_output; ?></div>
                <div class="lineInput">
                    <select name="export2ScriptFolder">
                        <option value=""><?php echo $formText_SelectScriptFolder_output;?></option>
                        <?php
                        $folders = array();
                        $directories = glob(__DIR__ . '/exportScripts/*' , GLOB_ONLYDIR);
                        foreach($directories as $directory){
                            $folder = array();
                            $folder['folderName'] = basename($directory);
                            $folder['folderPath'] = $directory;
                            array_push($folders, $folder);
                        }

                        foreach($folders as $folder) {
                            ?>
                            <option value="<?php echo $folder['folderPath']?>" <?php if($officeSpaceData->export2ScriptFolder == $folder['folderPath']) echo ' selected';?>><?php echo $folder['folderName']?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2SendMethod_output; ?></div>
				<div class="lineInput">
                    <?php $sendMethod = $officeSpaceData->export2SendMethod ? $officeSpaceData->export2SendMethod : 0; ?>
                    <select name="export2SendMethod">
                        <option value="0" <?php echo $sendMethod == 0 ? 'selected="selected"' : ''; ?>><?php echo $formText_None_output; ?></option>
                        <option value="1" <?php echo $sendMethod == 1 ? 'selected="selected"' : ''; ?>><?php echo $formText_Email_output; ?></option>
                        <option value="2" <?php echo $sendMethod == 2 ? 'selected="selected"' : ''; ?>><?php echo $formText_Ftp_output; ?></option>
                    </select>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpUsername_output; ?></div>
				<div class="lineInput">
					<input type="text"  class="popupforminput botspace"  name="export2FtpUsername" value="<?php echo $officeSpaceData->export2FtpUsername;?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpPassword_output; ?></div>
				<div class="lineInput">
					<input type="password"  class="popupforminput botspace"  name="export2FtpPassword" value="<?php echo $officeSpaceData->export2FtpPassword;?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpHost_output; ?></div>
				<div class="lineInput">
					<input type="text"  class="popupforminput botspace"  name="export2FtpHost" value="<?php echo $officeSpaceData->export2FtpHost;?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpPort_output; ?></div>
				<div class="lineInput">
					<input type="text"  class="popupforminput botspace"  name="export2FtpPort" value="<?php echo $officeSpaceData->export2FtpPort;?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpPath_output; ?></div>
				<div class="lineInput">
					<input type="text"  class="popupforminput botspace"  name="export2FtpPath" value="<?php echo $officeSpaceData->export2FtpPath;?>"/>
				</div>
				<div class="clear"></div>
			</div>
			<div class="line">
				<div class="lineTitle"><?php echo $formText_Export2FtpUseSsl_output; ?></div>
				<div class="lineInput">
					<input type="checkbox" name="export2FtpUseSSL" value="1" <?php if($officeSpaceData->export2FtpUseSSL) echo 'checked';?>/>
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
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
     $("form.output-form").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    if(data.redirect_url !== undefined)
                    {
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
</style>
