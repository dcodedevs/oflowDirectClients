<?php
$o_query = $o_main->db->query("SELECT * FROM ownercompany_accountconfig");
$v_ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$departmentId = isset($_POST['departmentId']) ? $_POST['departmentId'] : 0;

if($departmentId) {
    $sql = "SELECT * FROM departmentforaccounting WHERE id = $departmentId";
    $result = $o_main->db->query($sql);
    $department = $result ? $result->row_array() : array();
}
$b_activate_email = (isset($v_ownercompany_accountconfig['activate_email_for_department']) && 1 == $v_ownercompany_accountconfig['activate_email_for_department']);

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($department) {
            $sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ? AND id <> ? ";
            $result = $o_main->db->query($sql, array($_POST['departmentnumber'], $department['id']));
            $departmentByNumber = $result ? $result->row_array() : array();
            if(count($departmentByNumber) == 0 ){
                $s_sql = "UPDATE departmentforaccounting SET
                updated = now(),
                updatedBy= '".$o_main->db->escape_str($variables->loggID)."',
                name= '".$o_main->db->escape_str($_POST['name'])."',
                departmentnumber= '".$o_main->db->escape_str($_POST['departmentnumber'])."'".
				($b_activate_email ? ", email = '".$o_main->db->escape_str($_POST['email'])."'" : '')."
                WHERE id = '".$o_main->db->escape_str($department['id'])."'";
                if($o_main->db->query($s_sql)){
                    $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
                } else {
                    $fw_error_msg = $formText_ErrorUpdatingDatabase_output;
                }
            } else {
                $fw_error_msg = $formText_DepartmentNumberAlreadyInUse_output;
            }
        } else {
            $sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
            $result = $o_main->db->query($sql, array($_POST['departmentnumber']));
            $departmentByNumber = $result ? $result->row_array() : array();
            if(count($departmentByNumber) == 0 ){
                $s_sql = "INSERT INTO departmentforaccounting SET
                created = now(),
                createdBy= '".$o_main->db->escape_str($variables->loggID)."',
                name= '".$o_main->db->escape_str($_POST['name'])."',
                departmentnumber= '".$o_main->db->escape_str($_POST['departmentnumber'])."'".
				($b_activate_email ? ", email = '".$o_main->db->escape_str($_POST['email'])."'" : '');
                if($o_main->db->query($s_sql)){
                    $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
                } else {
                    $fw_error_msg = $formText_ErrorUpdatingDatabase_output;
                }
            } else {
                $fw_error_msg = $formText_DepartmentNumberAlreadyInUse_output;
            }
        }
	}

	if(isset($_POST['deleteDepartment']) && $departmentId > 0) {
        $s_sql = "DELETE FROM departmentforaccounting WHERE id = ?";
        if($o_main->db->query($s_sql, array($departmentId))){
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
        } else {
            $fw_error_msg = $formText_ErrorUpdatingDatabase_output;
        }
    }
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editDepartment";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="departmentId" value="<?php echo $department['id'];?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_DepartmentNumber_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="departmentnumber" value="<?php echo $department['departmentnumber']; ?>" required>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Name_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="name" value="<?php echo $department['name']; ?>" required> 
                </div>
                <div class="clear"></div>
            </div>
			<?php if($b_activate_email) { ?>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Email_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="email" value="<?php echo $department['email']; ?>"> 
                </div>
                <div class="clear"></div>
            </div>
			<?php } ?>
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
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    if(data.redirect_url !== undefined)
                    {
                        var data = { fwajax: 1, fw_nocss: 1, search: $(".contactPersonSearchInput").val() };

                        ajaxCall('editAllDepartments', data, function(json) {
                            $("#popupeditboxcontent").html(json.html);
                        });
                        out_popup2.close();
                    } else {
                        if(data.error != undefined){
                            $("#popup-validate-message").html(data.error, true);
                            $("#popup-validate-message").show();
                            $('#popupeditbox2').height($('#popupeditboxcontent2').height());
                        }
                        fw_loading_end();
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox2').height($('#popupeditboxcontent2').height());
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
                $('#popupeditbox2').height($('#popupeditboxcontent2').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox2').height(''); }, 200);
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
