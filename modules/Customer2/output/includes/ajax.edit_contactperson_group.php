<?php
$contactpersonId = $_POST['contactpersonId'] ? ($_POST['contactpersonId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

$sql = "SELECT c.* FROM contactperson c
WHERE c.id = ?";
$o_query = $o_main->db->query($sql, array($contactpersonId));
$contactPerson = $o_query ? $o_query->row_array(): array();

$sql = "SELECT g.* FROM contactperson_group_user p
JOIN contactperson_group g ON g.id = p.contactperson_group_id
WHERE p.type = 1 AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null) AND p.contactperson_id = ?";
$o_query = $o_main->db->query($sql, array($contactpersonId));
$added_groups = $o_query ? $o_query->result_array(): array();
$groupIds = array();
foreach($added_groups as $added_group) {
    $groupIds[] = $added_group['id'];
}
// On form submit
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        foreach($_POST['groupIds'] as $group_id) {
            $sql = "SELECT g.* FROM contactperson_group_user p
            JOIN contactperson_group g ON g.id = p.contactperson_group_id
            WHERE p.type = 1 AND p.contactperson_group_id = ? AND p.contactperson_id = ?";
            $o_query = $o_main->db->query($sql, array($group_id, $contactpersonId));
            $connection = $o_query ? $o_query->row_array(): array();
            if($connection){
                $o_query = $o_main->db->query("UPDATE contactperson_group_user SET
                    updated = NOW(),
                    updatedBy = ?,
                    type = 1,
                    status = 0
                    WHERE id = ?", array($variables->loggID, $connection['id']));
            } else {
                $o_query = $o_main->db->query("INSERT INTO contactperson_group_user SET
        			created = NOW(),
        			createdBy = ?,
        			contactperson_group_id = ?,
        			contactperson_id = ?,
        			type = 1,
        			status = 0", array($variables->loggID, $group_id, $contactpersonId));
            }
        }

        foreach($groupIds as $old_group){
            if(!in_array($old_group, $_POST['groupIds'])){
                $o_query = $o_main->db->query("DELETE FROM contactperson_group_user WHERE contactperson_group_id = ? AND contactperson_id = ? AND type = 1 ", array($old_group, $contactpersonId));
            }
        }
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
    }
}
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_group";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
        <div class="inner">
            <div class="popupformTitle"><?php echo $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname']?></div>
            <?php

            $sql = "SELECT g.* FROM contactperson_group g WHERE g.group_type = 1 AND g.content_status < 2";
            $o_query = $o_main->db->query($sql);
            $groups = $o_query ? $o_query->result_array(): array();
            foreach($groups as $group) {
                ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $group['name']; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" name="groupIds[]" <?php if(in_array($group['id'], $groupIds)) echo 'checked';?> value="<?php echo $group['id']?>"/>
                    </div>
                    <div class="clear"></div>
                </div>
                <?php
            }
            ?>

        </div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <input type="submit" name="saveMobilePhone" value="<?php echo $formText_Save_Output; ?>">
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
					fw_loading_end();
                    if(data.error !== undefined){
                        $.each(data.error, function(index, value){
                            $("#popup-validate-message").append("<div>"+value+"</div>").show();
                        });
                    } else {
						if(data.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
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

    $("#deleteAccess").unbind("click").bind("click", function(e){
        e.preventDefault();

        fw_click_instance = true;
        var $_this = $(this);
        bootbox.confirm({
            message:"<?php echo $formText_DeleteGateAccess_Output;?>",
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result) {
                    $_this.unbind("click").click();
                }
                fw_click_instance = false;
            }
        }).css({"z-index": "10000"})
    })

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
</style>
