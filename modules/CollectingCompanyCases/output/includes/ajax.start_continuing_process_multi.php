<?php
$process_case = $_POST['process_case'] ? $_POST['process_case'] : array();
$action = $_POST['action'] ? $_POST['action'] : '';
$status = $_POST['status'] ? $_POST['status'] : 0;

if(count($process_case) > 0) {
    require("fnc_process_continuing_step.php");

    if($moduleAccesslevel > 10) {
        if(isset($_POST['output_form_submit'])) {  
            $s_sql = "SELECT * FROM collecting_company_cases_continuing_process_steps WHERE collecting_company_cases_continuing_process_id = '".$o_main->db->escape_str($_POST['continuing_process_id'])."'  ORDER BY sortnr ASC";
            $o_query = $o_main->db->query($s_sql);
            $first_step = $o_query ? $o_query->row_array() : array();
            if($first_step){
                $successful_count = 0;
                $failed_count = 0;
                foreach($process_case as $process_single_case_id){     
                    //process 
                    $result = process_continuing_step($process_single_case_id, $first_step['id']);
                    if($result['success']){
                        $successful_count++;
                    } else {
                        $failed_count++;
                        if($result['due_date_not_reached']){
                            $fw_error_msg[] = $process_single_case_id." ".$formText_DueDateNotReached_output;
                        } else {
                            $fw_error_msg[] = $process_single_case_id." ".$formText_ErrorProcessingCase_output;
                        }
                    }
                }   
                if($successful_count > 0 && $successful_count == count($process_case)){
                    $fw_redirect_url = $_POST['redirect_url'];
                } else {
                    if(count($fw_error_msg) > 0){
                        $fw_error_msg[] = $successful_count." ".$formText_CasesWereProcessed_output;
                        $fw_error_msg[] = $failed_count." ".$formText_CasesFailedToProcessed_output;
                    }
                }
            } else {
                $fw_error_msg[] = $formText_MissingProcess_output;
            }
            return;
        }
    }

    ?>

    <div class="popupform popupform-<?php echo $caseId;?>">
        <div id="popup-validate-message" style="display:none;"></div>
        <form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=start_continuing_process_multi";?>" method="post">
            <input type="hidden" name="fwajax" value="1">
            <input type="hidden" name="fw_nocss" value="1">
            <input type="hidden" name="output_form_submit" value="1">
            <?php foreach($process_case as $process_single_case_id) { ?>
            <input type="hidden" name="process_case[]" value="<?php echo $process_single_case_id;?>">
            <?php } ?>
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseId; ?>">
            <div class="inner">
                <div class="popupformTitle"><?php echo $formText_SelectContinuingProcess_output;?></div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_ContinuingProcess_Output; ?></div>
                    <div class="lineInput">
                        <?php
                        $s_sql = "SELECT * FROM collecting_company_cases_continuing_process  ORDER BY sortnr ASC";
                        $o_query = $o_main->db->query($s_sql);
                        $processes = ($o_query ? $o_query->result_array() : array());
                        ?>
                        <select name="continuing_process_id" class="processSelect">
                            <option value=""><?php echo $formText_Select_output;?></option>
                            <?php foreach($processes as $process) { ?>
                                <option value="<?php echo $process['id'];?>" <?php if($process['id'] == $continuing_process_step['collecting_company_cases_continuing_process_id']) echo 'selected';?>>
                                    <?php echo $process['name'];?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_FirstStep_Output; ?></div>
                    <div class="lineInput step_wrapper">

                    </div>
                    <div class="clear"></div>
                </div>
                <br/><br/>
                <div class=""><span class="fas fa-exclamation-triangle"></span> <?php echo $formText_FirstStepWillBeProcessedRightAway_output?></div>
            </div>

            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
                <input type="submit" name="sbmbtn" value="<?php echo $formText_Process_Output; ?>">
            </div>
        </form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">


    function refresh_steps(){
        var data = {
            continuing_process_id: $(".processSelect").val()
        };
        ajaxCall('get_continuing_process_first_step', data, function(json) {
            $(".step_wrapper").html(json.html);
        });
    };
    $(document).ready(function() {
        $(".popupform-<?php echo $caseId;?> form.output-form").validate({
            ignore: [],
            submitHandler: function(form) {
                $("#popup-validate-message").hide();
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
                                var _type = Array("error");
                                if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                $("#popup-validate-message").html(value);
                            });
                            $("#popup-validate-message").show();
                            fw_click_instance = fw_changes_made = false;
                        } else {                            
                            out_popup.addClass("close-reload");
                            out_popup.close();
                        }
                    }
                }).fail(function() {
                    $(".popupform-<?php echo $caseId;?> #popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('.popupform-<?php echo $caseId;?> #popupeditboxcontent').height());
                    fw_loading_end();
                });
            },
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                    ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                    : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                    $(".popupform-<?php echo $caseId;?> #popup-validate-message").html(message);
                    $(".popupform-<?php echo $caseId;?> #popup-validate-message").show();
                    $('.popupform-<?php echo $caseId;?> #popupeditbox').css('height', $('#popupeditboxcontent').height());
                } else {
                    $(".popupform-<?php echo $caseId;?> #popup-validate-message").hide();
                }
                setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "creditor_id") {
                    error.insertAfter(".popupform-<?php echo $caseId;?> .selectCreditor");
                }
                if(element.attr("name") == "debitor_id") {
                    error.insertAfter(".popupform-<?php echo $caseId;?> .selectDebitor");
                }
            },
            messages: {
                creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
                debitor_id: "<?php echo $formText_SelectTheDebitor_output;?>",
            }
        });
        $(".datefield").datepicker({
            dateFormat: "d.m.yy",
            firstDay: 1
        })
        
        $(".processSelect").change(function(){
            refresh_steps();
        })
        
    });

    </script>
    <style>
    .categoryWrapper {
        display: none;
    }
    .resetInvoiceResponsible {
        margin-left: 20px;
    }
    .lineInput .otherInput {
        margin-top: 10px;
    }
    .lineInput input[type="radio"]{
        margin-right: 10px;
        vertical-align: middle;
    }
    .lineInput input[type="radio"] + label {
        margin-right: 10px;
        vertical-align: middle;
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
    .popupform .lineInput.lineWhole {
        font-size: 14px;
    }
    .popupform .lineInput.lineWhole label {
        font-weight: normal !important;
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
    label.error {
        color: #c11;
        margin-left: 10px;
        border: 0;
        display: inline !important;
    }
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
    .addSubProject {
        margin-bottom: 10px;
    }
    </style>
<?php 
} else {
    echo $formText_PleaseSelectCases_output;
}
?>