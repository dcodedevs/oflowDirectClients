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

// Get contactperson data
$o_query = $o_main->db->get_where('contactperson', array('id' => $_POST['contactpersonId']));
$contactperson_data = $o_query ? $o_query->row_array() : array();

// Create person if ID does not exist on ARX, update if exists already
$external_locksystem_person_id = ($contactperson_data['external_locksystem_person_id'] != '' ? $contactperson_data['external_locksystem_person_id'] : 'DCODE_ID_' . $contactperson_data['id']);
$external_locksystem2_person_id = ($contactperson_data['external_locksystem2_person_id'] != '' ? $contactperson_data['external_locksystem2_person_id'] : 'D' . $contactperson_data['id']);

// Check connection
$b_exists_in_security_system = $b_in_arx = $b_in_amadeus = FALSE;

if(!$b_exists_in_security_system)
{
	$b_exists_in_security_system = !empty($contactperson_data['access_card_number']) && isKeyCardUserOnArx($contactperson_data['access_card_number'], $external_locksystem_person_id);
	if($b_exists_in_security_system) $b_in_arx = TRUE;
}
if(!$b_exists_in_security_system)
{
	$b_exists_in_security_system = !empty($contactperson_data['access_card_number_on_card']) && isKeyCardUserOnAmadeus($contactperson_data['access_card_number_on_card'], $external_locksystem2_person_id);
	if($b_exists_in_security_system) $b_in_amadeus = TRUE;
}

// On form submit
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		if(isset($_POST['convert']) && 1 == $_POST['convert'])
		{
			if(!empty($_POST['dec']))
			{
				$s_hex = str_pad(strtoupper(dechex($_POST['dec'])), 8, '0', STR_PAD_LEFT);
				$s_hex_converted = '';
				for($i=-1; $i>=-4; $i--)
				{
					$s_tmp = substr($s_hex, 2*$i, 2);
					$s_hex_converted .= $s_tmp;
				}
				$fw_return_data = array(
					'hex_number' => $s_hex_converted,
					'dec_number' => $_POST['dec'],
				);
			}
			if(!empty($_POST['hex']))
			{
				$s_hex_converted = '';
				$s_hex = strtoupper(str_pad($_POST['hex'], 8, '0', STR_PAD_LEFT));
				for($i=-1; $i>=-4; $i--)
				{
					$s_tmp = substr($s_hex, 2*$i, 2);
					$s_hex_converted .= $s_tmp;
				}
				$s_dec = hexdec($s_hex_converted);
				$fw_return_data = array(
					'hex_number' => $s_hex,
					'dec_number' => $s_dec,
				);
			}
			return;
		}
		
        $v_update = array(
            'external_locksystem_pin' => $_POST['external_locksystem_pin']
        );
		if($b_exists_in_security_system)
		{
			if('' != trim($_POST['external_locksystem_pin']) && $_POST['external_locksystem_pin'] != $contactperson_data['external_locksystem_pin'])
			{
				// Divide name in parts
				$name_parts = explode(" ", trim(preg_replace('/\s+/', ' ', $contactperson_data['name'].' '.$contactperson_data['middlename'].' '.$contactperson_data['lastname'])));
				$first_name = $name_parts[0];
				$last_name = '';
				for($i = 1; $i < count($name_parts); $i++) {
					$last_name .= $name_parts[$i] . ' ';
				}
		
				$last_name = trim($last_name);
				
				if($b_in_arx)
				{
					$api->add_person($external_locksystem_person_id, $first_name, $last_name, trim($_POST['external_locksystem_pin']));
				}
				if($b_in_amadeus)
				{
					$api_amadeus->add_update_person($external_locksystem2_person_id, $first_name, $last_name, trim($_POST['external_locksystem_pin']));
				}
			}
		} else {
			$v_update['access_card_number'] = $_POST['access_card_number'];
			$v_update['access_card_number_on_card'] = $_POST['access_card_number_on_card'];
			
			$o_query = $o_main->db->insert('contactperson_keycard_log', array(
				'created' => date('Y-m-d H:i:s'),
				'createdBy' => $variables->loggID,
				'contactpersonId' => $contactperson_data['id'],
				'keycardNumber' => trim($_POST['access_card_number']),
				'keycard_number_hex' => trim($_POST['access_card_number_on_card'])
			));
		}
		$o_main->db->where('id', $contactperson_data['id']);
        $o_query = $o_main->db->update('contactperson', $v_update);
        
		return;
	}
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form second" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_keycard_numbers";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactperson_data['id'];?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Contactperson_Output; ?></div>
                <div class="lineInput">
                    <?php echo trim(preg_replace('/\s+/', ' ', $contactperson_data['name'].' '.$contactperson_data['middlename'].' '.$contactperson_data['lastname'])); ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_KeycardNumber_Output; ?></div>
                <div class="lineInput">
                    <?php if ($b_exists_in_security_system): ?>
                        <?php echo $contactperson_data['access_card_number']; ?>
                        <input type="hidden" name="access_card_number" value="<?php echo $contactperson_data['access_card_number']; ?>">
                    <?php else: ?>
                        <input type="text" class="popupforminput botspace" name="access_card_number" value="<?php echo $contactperson_data['access_card_number']; ?>" autocomplete="off">
                    <?php endif; ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_KeycardNumberOnCard_Output; ?></div>
                <div class="lineInput">
                    <?php if ($b_exists_in_security_system): ?>
                        <?php echo $contactperson_data['access_card_number_on_card']; ?>
                        <input type="hidden" name="access_card_number_on_card" value="<?php echo $contactperson_data['access_card_number_on_card']; ?>">
                    <?php else: ?>
                        <input type="text" class="popupforminput botspace" name="access_card_number_on_card" value="<?php echo $contactperson_data['access_card_number_on_card']; ?>" autocomplete="off">
                    <?php endif; ?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_PinNumber_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="external_locksystem_pin" value="<?php echo $contactperson_data['external_locksystem_pin']; ?>" autocomplete="off">
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

<script type="text/javascript">
$(document).ready(function() {
	var output_timer;
    $('input[name=access_card_number]').off('keyup').on('keyup', function(){
		var code = $(this).val();
		window.clearTimeout(output_timer);
		output_timer = window.setTimeout(function(){ output_convert_card(code, ''); }, 300);
	});
	$('input[name=access_card_number_on_card]').off('keyup').on('keyup', function(){
		var code = $(this).val();
		window.clearTimeout(output_timer);
		output_timer = window.setTimeout(function(){ output_convert_card('', code); }, 300);
	});
	function output_convert_card(dec, hex)
	{
		fw_loading_start();
		$.ajax({
			url: '<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_keycard_numbers";?>',
			cache: false,
			type: "POST",
			dataType: "json",
			data: { fwajax: 1, fw_nocss: 1, output_form_submit: 1, convert: 1, dec: dec, hex: hex },
			success: function (json) {
				fw_loading_end();
				if(json.error !== undefined)
				{
					$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
					$("#popup-validate-message").show();
				} else {
					if(hex == '') $('input[name=access_card_number_on_card]').val(json.data.hex_number);
					if(dec == '') $('input[name=access_card_number]').val(json.data.dec_number);
				}
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccurredHandlingRequest_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});
	}
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
						$('span.access_card_number').text($('input[name=access_card_number]').val());
						$('span.external_locksystem_pin').text($('input[name=external_locksystem_pin]').val());
						$('span.access_card_number_on_card').text($('input[name=access_card_number_on_card]').val());
						$("#popupeditbox2 .b-close").click();
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
