<?php
$case_id = $_POST['case_id'];
$creditor_id = $_POST['creditorId'];
$onhold_comment = $_POST['onhold_comment'];
$responsible_person = $_POST['responsible_person'];

$s_sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id));
$creditor = ($o_query ? $o_query->row_array() : array());
if($_POST['output_form_submit']) {
    if($onhold_comment != "" && $responsible_person != ""){
        if($creditor){
            $s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($case_id));
            $case = ($o_query ? $o_query->row_array() : array());
            if($case){
                $s_sql = "INSERT INTO collecting_cases_objection  SET created = NOW(), createdBy = ?, stopped_by_creditor = 1, collecting_case_id = ?, message_from_debitor = ?, responsible_person_id = ?";
                $o_query = $o_main->db->query($s_sql, array($variables->loggID, $case['id'], $onhold_comment, $responsible_person));
                if($o_query){
                    $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$case_id;

                } else {
                    $fw_error_msg[] = $formText_ErrorUpdatingDatabase_output;
                }
            } else {
                $fw_error_msg[] = $formText_MissingCase_output;
            }
        } else {
            $fw_error_msg[] = $formText_MissingCreditor_output;
        }
    } else {
        $fw_error_msg[] = $formText_MissingFields_output;
    }
    return;
}
$s_sql = "SELECT * FROM contactperson WHERE content_status < 2 AND customerId = ?";
$o_query = $o_main->db->query($s_sql, array($creditor['customer_id']));
$responsible_persons = ($o_query ? $o_query->result_array() : array());
?>
<div class="popupform">
    <div id="popup-validate-message" style="display:none;"></div>
    <form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=putonhold_case";?>" method="post">
        <input type="hidden" name="fwajax" value="1">
        <input type="hidden" name="fw_nocss" value="1">
        <input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="case_id" value="<?php print $_POST['case_id'];?>">
        <input type="hidden" name="creditorId" value="<?php print $_POST['creditorId'];?>">

        <div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ResponsiblePerson_Output; ?></div>
                <div class="lineInput">
                    <select name="responsible_person" required>
                        <option value=""><?php echo $formText_Select_output;?></option>
                        <?php foreach($responsible_persons as $responsible_person) {
                            ?>
                            <option value="<?php echo $responsible_person['id'];?>"><?php echo $responsible_person['name']." ".$responsible_person['middlename']." ".$responsible_person['lastname'];?></option>
                            <?php
                        }?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_StoppedComment_Output; ?></div>
                <div class="lineInput"><textarea class="popupforminput botspace" name="onhold_comment" required></textarea></div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="popupformbtn"><input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>"></div>
    </form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$("form.output-form").validate({
    submitHandler: function(form) {
        fw_loading_start();
        var formdata = $(form).serializeArray();
        var data = {};
        $(formdata).each(function(index, obj){
            data[obj.name] = obj.value;
        });
        $("#popup-validate-message").hide();

        $.ajax({
            url: $(form).attr("action"),
            cache: false,
            type: "POST",
            dataType: "json",
            data: data,
            success: function (data) {
                fw_loading_end();
                if(data.error !== undefined)
                {
                    $.each(data.error, function(index, value){
                        var _type = Array("error");
                        if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                        $("#popup-validate-message").append(value);
                    });
                    $("#popup-validate-message").show();
                    fw_loading_end();
                    fw_click_instance = fw_changes_made = false;
                } else  if(data.redirect_url !== undefined)
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
</script>
