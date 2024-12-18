<?php

$ownercompanyId = isset($_POST['ownercompanyId']) ? $_POST['ownercompanyId'] : 0;
$projectId = isset($_POST['projectId']) ? $_POST['projectId'] : 0;
$parentId = isset($_POST['parentId']) ? $_POST['parentId'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$redirectBackTo = isset($_POST['redirectBackTo']) ? $_POST['redirectBackTo'] : '';

$return['redirectBackTo'] = $redirectBackTo;

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($projectId) {
            $s_sql = "UPDATE projectforaccounting SET
            updated = now(),
            updatedBy= ?,
            projectnumber= ?,
            name= ?,
            ownercompany_id = ?,
            parentId = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['projectnumber'], $_POST['name'], $_POST['ownercompanyId'], $_POST['parentId'], $projectId));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];

        } else {
			$s_sql = "INSERT INTO projectforaccounting SET
			moduleID = ?,
            created = now(),
            createdBy= ?,
            projectnumber= ?,
            name= ?,
            ownercompany_id = ?,
            parentId = ?";
            $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['projectnumber'], $_POST['name'], $_POST['ownercompanyId'], $_POST['parentId']));
            $fw_return_data = $s_sql;
            $fw_redirect_url = $_POST['redirect_url'];
		}
	}
}

if ($action == 'deleteProject' && $moduleAccesslevel > 110) {
    $sql = "DELETE FROM projectforaccounting WHERE id = ?";
    $o_main->db->query($sql, array($projectId));
}

if ($action == 'checkProjectNumber') {
	if ($_POST['editProjectId']) {
		$sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ? AND id <> ?";
		$o_query = $o_main->db->query($sql, array($_POST['projectnumber'], $_POST['editProjectId']));
	} else {
		$o_query = $o_main->db->get_where('projectforaccounting', array('projectnumber' => $_POST['projectnumber']));
	}

	$return['projectnumberexists'] = $o_query && $o_query->num_rows();
}

if($projectId) {
    $sql = "SELECT * FROM projectforaccounting WHERE id = ?";
    $result = $o_main->db->query($sql, array($projectId));
    if($result && $result->num_rows() > 0) $projectData = $result->row();
    $parentId = $projectData->parentId;
}
if($parentId) {
    $sql = "SELECT * FROM projectforaccounting WHERE id = ?";
    $result = $o_main->db->query($sql, array($parentId));
    if($result && $result->num_rows() > 0) $parentProjectData = $result->row_array();
}

function formatDate($date) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return '';
    return date('d.m.Y', strtotime($date));
}

function unformatDate($date) {
    $d = explode('.', $date);
    return $d[2].'-'.$d[1].'-'.$d[0];
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editProject";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="projectId" value="<?php echo $projectId;?>">
        <input type="hidden" name="parentId" value="<?php echo $parentId;?>">
        <input type="hidden" name="redirectBackTo" value="<?php echo $redirectBackTo;?>">
		<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId; ?>">
		<div class="inner">
            <?php if($parentProjectData) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_ParentProjectNumber_Output; ?></div>
                    <div class="lineInput">
                        <?php echo $parentProjectData['projectnumber']; ?>
                    </div>
                    <div class="clear"></div>
                </div>
            <?php } ?>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_ProjectNumber_Output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="projectnumber" value="<?php echo $projectData->projectnumber; ?>" required>
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $projectData->name; ?>" required>
                </div>
                <div class="clear"></div>
            </div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large cancel-button"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$('.cancel-button').on('click', function(e) {
		var redirectBackTo = $('[name="redirectBackTo"]').val();

		if (redirectBackTo == 'editAllProjectsPopup') {
			var data = {};
			ajaxCall('editAllProjects', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
			});
		} else {
			$('.b-close').click();
		}
	});

    $("form.output-form").validate({
        submitHandler: function(form) {
			var data = {
				action: 'checkProjectNumber',
				projectnumber: $('[name="projectnumber"]').val(),
				editProjectId: '<?php echo $projectId; ?>'
			};

			ajaxCall('editProject', data, function(json) {
				if (json.projectnumberexists) {
					$("#popup-validate-message").html("<?php echo $formText_ProjectNumberAlreadyExists_Output;?>", true);
					$("#popup-validate-message").show();
					// $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
				}
				else {
					$.ajax({
					    url: $(form).attr("action"),
					    cache: false,
					    type: "POST",
					    dataType: "json",
					    data: $(form).serialize(),
					    success: function (data) {
					        if (data.redirectBackTo == 'editAllProjectsPopup') {
					            var data = {};
					            ajaxCall('editAllProjects', data, function(json) {
					                $('#popupeditboxcontent').html('');
					                $('#popupeditboxcontent').html(json.html);
					            });
					        }
					        else {
					            if(data.redirect_url !== undefined)
					            {
					                out_popup.addClass("close-reload").data("redirect", data.redirect_url);
					                out_popup.close();
					            }
					        }

					    }
					}).fail(function() {
					    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
					    $("#popup-validate-message").show();
					    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
					    fw_loading_end();
					});
				}
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


    // $('.output-form').on('submit', function(e) {
    //     e.preventDefault();
    //     var data = {};
    //     $(this).serializeArray().forEach(function(item, index) {
    //         data[item.name] = item.value;
    //     });
    //     ajaxCall('editOrder', data, function (json) {
    //         if (json.redirect_url) document.location.href = json.redirect_url;
    //         else out_popup.close();
    //     });
    // });
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
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
