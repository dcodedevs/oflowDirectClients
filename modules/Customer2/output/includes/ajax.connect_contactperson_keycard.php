<?php
// Keycards
$integration = 'IntegrationArx';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }
}
$integration = 'IntegrationAmadeus';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if ($api_amadeus) unset($api_amadeus);
        $api_amadeus = new $integration(array(
            'o_main' => $o_main
        ));
    }
}

// Helper function
function isKeyCardUserOnArx($keycardNumber, $person_id) {
	global $o_main;
    global $api;
	$b_return = FALSE;
	
    if(!empty(trim($keycardNumber)))
	{
		$keycardNumber = trim($keycardNumber);
		$keycards = $api->get_keycards();
        foreach ($keycards as $card) {
            if ($card->number == $keycardNumber && $card->person_id == $person_id) {
                $b_return = TRUE;
            }
        }
    }
	
    return $b_return;
}

function isKeyCardUserOnAmadeus($keycardNumber, $person_id) {
    global $o_main;
    global $api_amadeus;
	$b_return = FALSE;
	
    if(!empty(trim($keycardNumber)))
	{
		$keycardNumber = trim($keycardNumber);
		$keycards = $api_amadeus->get_keycard($keycardNumber);
        foreach ($keycards as $card) {
            if ($card['person_id'] == $person_id) {
                $b_return = TRUE;
            }
        }
    }
	
    return $b_return;
}

// On form submit
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
        $o_query = $o_main->db->query("SELECT * FROM contactperson WHERE id = '".$o_main->db->escape_str($_POST['contactperson_id'])."'");
		if($o_query && $o_query->num_rows()>0)
		{
			$v_contactperson = $o_query ? $o_query->row_array() : array();
			$external_locksystem2_person_id = ($v_contactperson['external_locksystem2_person_id'] != '' ? $v_contactperson['external_locksystem2_person_id'] : 'D' . $v_contactperson['id']);
			$_POST['keycard'] = trim($_POST['keycard']);
			
			$s_hex_converted = '';
			$s_hex = strtoupper(str_pad(trim($_POST['keycard']), 8, '0', STR_PAD_LEFT));
			for($i=-1; $i>=-4; $i--)
			{
				$s_tmp = substr($s_hex, 2*$i, 2);
				$s_hex_converted .= $s_tmp;
			}
			$s_dec = hexdec($s_hex_converted);
			$v_update = array(
				'external_locksystem2_person_id' => $external_locksystem2_person_id,
				'access_card_number_on_card' => $_POST['keycard'],
				'access_card_number' => $s_dec,
			);
			// Divide name in parts
			$name_parts = explode(" ", trim(preg_replace('/\s+/', ' ', $v_contactperson['name'].' '.$v_contactperson['middlename'].' '.$v_contactperson['lastname'])));
			$first_name = $name_parts[0];
			$last_name = '';
			for($i = 1; $i < count($name_parts); $i++) {
				$last_name .= $name_parts[$i] . ' ';
			}
	
			$last_name = trim($last_name);
			$b_status = $api_amadeus->change_person_num($external_locksystem2_person_id, $_POST['cardholder_id']);
			if(!$b_status)
			{
				$fw_error_msg[] = $formText_ErrorOccurredHandlingRequest_Output;
				return;
			}
			$b_status = $api_amadeus->add_keycard($_POST['keycard'], $external_locksystem2_person_id);
			if(!$b_status)
			{
				$fw_error_msg[] = $formText_ErrorOccurredHandlingRequest_Output;
				return;
			}
			
			// If this card belongs to somebody else in local database - remove
			$o_query = $o_main->db->query("UPDATE contactperson SET access_card_number_on_card = '', external_locksystem2_person_id = '' WHERE access_card_number_on_card = ? AND id <> ?", array($_POST['keycard'], $v_contactperson['id']));
			
			$o_main->db->where('id', $v_contactperson['id']);
			$o_query = $o_main->db->update('contactperson', $v_update);
			
			$o_query = $o_main->db->insert('contactperson_keycard_log', array(
				'created' => date('Y-m-d H:i:s'),
				'createdBy' => $variables->loggID,
				'contactpersonId' => $v_contactperson['id'],
				'keycardNumber' => $s_dec,
				'keycard_number_hex' => $_POST['keycard']
			));
		}
		return;
	}
}
$keycards = $api_amadeus->get_keycards();
?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form second" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=connect_contactperson_keycard";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="keycard" value="<?php echo $_POST['keycard'];?>">
		<input type="hidden" name="cardholder_id" value="<?php echo $_POST['cardholder_id'];?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ChooseContactperson_Output; ?></div>
                <div class="lineInput">
                    <select class="popupforminput botspace" name="contactperson_id">
					<?php
					$o_query = $o_main->db->query("SELECT c.name AS customer_name, cp.* FROM contactperson cp LEFT OUTER JOIN customer c ON c.id = cp.customerId WHERE cp.content_status = 0 ORDER BY cp.name");
					if($o_query && $o_query->num_rows()>0)
					foreach($o_query->result_array() as $v_contactperson)
					{
						$b_disabled = FALSE;
						foreach($keycards as $v_keycard)
						{
							if('' != $v_contactperson['external_locksystem2_person_id'] && $v_contactperson['external_locksystem2_person_id'] == $v_keycard['person_id']) $b_disabled = TRUE;
						}
						?><option value="<?php echo (!$b_disabled ? $v_contactperson['id'] : '');?>"<?php echo ($b_disabled?' disabled':'');?>><?php echo preg_replace('/\s+/', ' ', $v_contactperson['name'].' '.$v_contactperson['middlename'].' '.$v_contactperson['lastname']).' '.(!empty($v_contactperson['customer_name']) ? '('.$v_contactperson['customer_name'].')' : '');?></option><?php
					}
					?>
					</select>
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
<script type="text/javascript" src="<?php echo $variables->account_root_url.'/modules/'.$module;?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	// Submit form
    $("form.output-form.second").validate({
        submitHandler: function(form) {
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    if(data.error !== undefined)
                    {
						$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                		$("#popup-validate-message").show();
					} else {
						window.location.reload(true);
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
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
