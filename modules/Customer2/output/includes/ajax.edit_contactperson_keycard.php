<?php
$contactpersonId = $_POST['contactpersonId'] ? ($_POST['contactpersonId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;
$action = $_POST['action'] ? ($_POST['action']) : '';
//$keycardNumber = $_POST['keycardNumber'] ? ($_POST['keycardNumber']) : 0;

// Keycards
$integration = 'IntegrationArx';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if(isset($api)) unset($api);
        $api = new $integration(array(
            'o_main' => $o_main
        ));
    }
}
$v_amadeus_systems = array();
$integration = 'IntegrationAmadeus';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
		$o_query = $o_main->db->query("SELECT * FROM integration_amadeus ORDER BY id");
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			$v_amadeus_systems[$v_row['id']] = array(
				'name' => $v_row['name'],
				'api' => new $integration(array(
					'o_main' => $o_main,
					'config_id' => $v_row['id'],
				))
			);
		}
    }
}

// Helper function
function checkKeyCardOnArx($keycardNumber, $person_id) {
    global $o_main;
    global $api;

    $keycards = $api->get_keycards();
    $foundKeycards = array();

    if (!empty(trim($keycardNumber))) {
        foreach ($keycards as $card) {
            if ($card->number == $keycardNumber && $card->person_id != $person_id) {
                if ($card->person_id) {
                    $card = (array)$card;
                    $card['person_data'] = $api->get_person_by_id($card['person_id']);
                    array_push($foundKeycards, $card);
                }
            }
        }
    }

    return array(
        'count' => count($foundKeycards),
        'result' => $foundKeycards
    );
}

function checkKeyCardOnAmadeus($api, $keycardNumber, $person_id) {
    global $o_main;

    $foundKeycards = array();

    if (!empty(trim($keycardNumber)))
	{
		$keycardNumber = trim($keycardNumber);
		$keycards = $api->get_keycard($keycardNumber);
        foreach ($keycards as $card) {
            if ($card['person_id'] != $person_id) {
                if ($card['person_id']) {
                    $card['person_data'] = $api->get_person_by_id($card['person_id']);
                    array_push($foundKeycards, $card);
                }
            }
        }
    }

    return array(
        'count' => count($foundKeycards),
        'result' => $foundKeycards
    );
}

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

function isKeyCardUserOnAmadeus($api, $keycardNumber, $person_id) {
    global $o_main;
	$b_return = FALSE;
	
    if(!empty(trim($keycardNumber)))
	{
		$keycardNumber = trim($keycardNumber);
		$keycards = $api->get_keycard($keycardNumber);
        foreach ($keycards as $card) {
            if ($card['person_id'] == $person_id) {
                $b_return = TRUE;
            }
        }
    }
	
    return $b_return;
}

// Get contactperson data
$o_query = $o_main->db->get_where('contactperson', array('id' => $contactpersonId));
$contactperson_data = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get_where('customer', array('id' => $contactperson_data['customerId']));
$customer_data = $o_query ? $o_query->row_array() : array();

$access_card_number = trim($contactperson_data['access_card_number']);
$access_card_number_on_card = trim($contactperson_data['access_card_number_on_card']);

// Create person if ID does not exist on ARX, update if exists already
$external_locksystem_person_id = ($contactperson_data['external_locksystem_person_id'] != '' ? $contactperson_data['external_locksystem_person_id'] : 'DCODE_ID_' . $contactperson_data['id']);
$external_locksystem2_person_id = ($contactperson_data['external_locksystem2_person_id'] != '' ? $contactperson_data['external_locksystem2_person_id'] : 'D' . $contactperson_data['id']);

$b_active_arx = !empty($access_card_number) && isKeyCardUserOnArx($access_card_number, $external_locksystem_person_id);
$v_active_amadeus = array();
foreach($v_amadeus_systems as $l_key => $v_amadeus)
{
	$v_active_amadeus[$l_key] = !empty($access_card_number_on_card) && isKeyCardUserOnAmadeus($v_amadeus['api'], $access_card_number_on_card, $external_locksystem2_person_id);
}

// On form submit
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
        $b_connected = FALSE;
		// Divide name in parts
        $name_parts = explode(" ", trim(preg_replace('/\s+/', ' ', $contactperson_data['name'].' '.$contactperson_data['middlename'].' '.$contactperson_data['lastname'])));
        $first_name = $name_parts[0];
        $last_name = '';
        for($i = 1; $i < count($name_parts); $i++) {
            $last_name .= $name_parts[$i] . ' ';
        }

        $last_name = trim($last_name);
		
		// Validation
		foreach($v_amadeus_systems as $l_key => $v_amadeus)
		{
			if(isset($_POST['external_locksystem_amadeus_'.$l_key]))
			{
				if(empty($last_name))
				{
					$fw_error_msg[] = $formText_LastNameCannotBeEmpty_Output;
					return;
				}
			}
		}

        if(isset($_POST['external_locksystem_arx']))
		{
			$b_connected = TRUE;
			$api->add_person($external_locksystem_person_id, $first_name, $last_name, $contactperson_data['external_locksystem_pin']);
		}
		foreach($v_amadeus_systems as $l_key => $v_amadeus)
		{
			if(isset($_POST['external_locksystem_amadeus_'.$l_key]))
			{
				$b_connected = TRUE;
				$b_status = $v_amadeus['api']->add_update_person($external_locksystem2_person_id, $first_name, $last_name, $contactperson_data['external_locksystem_pin'], $customer_data['name']);
				if(!$b_status)
				{
					$fw_error_msg[] = $formText_ErrorOccurredHandlingRequest_Output;
					return;
				}
			}
		}
        
		// Add or update keycard ARX
        if($b_connected && !empty($access_card_number))
		{
            if(isset($_POST['external_locksystem_arx']))
			{
				$api->add_keycard('Offline Mifare 1K', $access_card_number, $external_locksystem_person_id);
				
				// If this card belongs to somebody else in local database - remove
				$o_query = $o_main->db->query("UPDATE contactperson SET access_card_number = '', external_locksystem_person_id = '' WHERE access_card_number = ? AND id <> ?", array($access_card_number, $contactpersonId));
				
				//deactivate in logs for somebody else
				$afftectedRows = $o_main->db->affected_rows();
				if($afftectedRows > 0){
					$o_main->db->where('keycardNumber = '.$access_card_number.' AND deactivatedTime IS NULL AND contactpersonId <> '.$contactpersonId);
					$o_query = $o_main->db->update('contactperson_keycard_log', array(
						'deactivatedTime' => date('Y-m-d H:i:s')
					));
				}
				$o_main->db->where('id', $contactpersonId);
				$o_query = $o_main->db->update('contactperson', array(
					'external_locksystem_person_id' => $external_locksystem_person_id
				));
			}
        }

        // Empty access card number? Remove relation from API
        if($b_active_arx && !isset($_POST['external_locksystem_arx']))
		{
			$api->delete_keycard('Offline Mifare 1K', $access_card_number);
			$o_main->db->where('id', $contactpersonId);
			$o_query = $o_main->db->update('contactperson', array(
				'external_locksystem_person_id' => ''
			));
        }
		
		
		// Add or update keycard AMADEUS
        if($b_connected && !empty($access_card_number_on_card))
		{
			$b_update = FALSE;
			foreach($v_amadeus_systems as $l_key => $v_amadeus)
			{
				if(isset($_POST['external_locksystem_amadeus_'.$l_key]))
				{
					$b_update = TRUE;
					$v_check = checkKeyCardOnAmadeus($v_amadeus['api'], $access_card_number_on_card, $external_locksystem2_person_id);
					if(0 < $v_check['count'])
					{
						$v_amadeus['api']->delete_keycard($access_card_number_on_card);
					}
					$b_status = $v_amadeus['api']->add_keycard($access_card_number_on_card, $external_locksystem2_person_id);
					if(!$b_status)
					{
						$fw_error_msg[] = $formText_ErrorOccurredHandlingRequest_Output;
						return;
					}
				}
			}
			if($b_update)
			{
				// If this card belongs to somebody else in local database - remove
				$o_query = $o_main->db->query("UPDATE contactperson SET access_card_number_on_card = '', external_locksystem2_person_id = '' WHERE access_card_number_on_card = ? AND id <> ?", array($access_card_number_on_card, $contactpersonId));
				
				//deactivate in logs for somebody else
				$afftectedRows = $o_main->db->affected_rows();
				if($afftectedRows > 0){
					$o_main->db->where('keycard_number_hex = '.$access_card_number_on_card.' AND deactivatedTime IS NULL AND contactpersonId <> '.$contactpersonId);
					$o_query = $o_main->db->update('contactperson_keycard_log', array(
						'deactivatedTime' => date('Y-m-d H:i:s')
					));
				}
				$o_main->db->where('id', $contactpersonId);
				$o_query = $o_main->db->update('contactperson', array(
					'external_locksystem2_person_id' => $external_locksystem2_person_id
				));
			}
        }

        // Empty access card number? Remove relation from API
		foreach($v_amadeus_systems as $l_key => $v_amadeus)
		{
			if($v_active_amadeus[$l_key] && !isset($_POST['external_locksystem_amadeus_'.$l_key]))
			{
				$v_amadeus['api']->delete_keycard($access_card_number_on_card);
				$o_main->db->where('id', $contactpersonId);
				$o_query = $o_main->db->update('contactperson', array(
					'external_locksystem2_person_id' => ''
				));
			}
		}
        if(!$b_connected)
		{
            $o_main->db->where('contactpersonId = '.$contactpersonId.' AND deactivatedTime IS NULL');
            $o_query = $o_main->db->update('contactperson_keycard_log', array(
                'deactivatedTime' => date('Y-m-d H:i:s')
            ));
			$o_main->db->where('id', $contactpersonId);
			$o_query = $o_main->db->update('contactperson', array(
				'external_locksystem_person_id' => '',
				'external_locksystem2_person_id' => ''
			));
        }

        // Redirect
        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
		return;
	}
}

// On check
if ($action == 'checkKeyCardArx') {
    $return['checkKeyCardReturn'] = checkKeyCardOnArx($access_card_number, $external_locksystem_person_id);
	return;
}
if ($action == 'checkKeyCardAmadeus') {
	$return['checkKeyCardReturn'] = checkKeyCardOnAmadeus($v_amadeus_systems[$_POST['config_id']]['api'], $access_card_number_on_card, $external_locksystem2_person_id);
	return;
}
?>
<div class="popupform">
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_keycard";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
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
                   	<span class="access_card_number"><?php echo $access_card_number; ?></span>
					<span class="editEntryBtn glyphicon glyphicon-pencil edit-access-card-number"></span>
                    <br/><span class="checking-keycard-progress">
						<?php echo $formText_Checking_output; ?>...
					</span>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_KeycardNumberOnCard_Output; ?></div>
                <div class="lineInput">
                    <span class="access_card_number_on_card"><?php echo $access_card_number_on_card; ?></span>
					<span class="editEntryBtn glyphicon glyphicon-pencil edit-access-card-number"></span>
                    <br /><span class="checking-keycard-progress">
						<?php echo $formText_Checking_output; ?>...
					</span>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_PinNumber_Output; ?></div>
                <div class="lineInput">
                    <span class="external_locksystem_pin"><?php echo $contactperson_data['external_locksystem_pin']; ?></span>
					<span class="editEntryBtn glyphicon glyphicon-pencil edit-access-card-number"></span>
                </div>
                <div class="clear"></div>
            </div>
			<div class="line">
                <div class="lineTitle"><?php echo $formText_Arx_Output; ?></div>
                <div class="lineInput">
                    <input type="checkbox" class="botspace" name="external_locksystem_arx" value="1"<?php echo ($b_active_arx?' checked':'');?>>
                </div>
                <div class="clear"></div>
            </div>
			<?php
			foreach($v_amadeus_systems as $l_key => $v_amadeus)
			{
				?>
				<div class="line">
					<div class="lineTitle"><?php echo $v_amadeus['name']; ?></div>
					<div class="lineInput">
						<input type="checkbox" class="botspace external_locksystem_amadeus" name="external_locksystem_amadeus_<?php echo $l_key;?>" value="<?php echo $l_key;?>"<?php echo ($v_active_amadeus[$l_key]?' checked':'');?>>
					</div>
					<div class="clear"></div>
				</div>
				<?php
			}
			?>
		</div>
		<div id="popup-validate-message" style="display:none;"></div>
		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
            <input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>

<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    function checkKeycardArx(callback) {
        var data = {
            action: 'checkKeyCardArx',
			contactpersonId: '<?php echo $contactpersonId;?>'
        };

        $('.checking-keycard-progress').show();

        ajaxCall('edit_contactperson_keycard', data, function(json) {
            if (json.checkKeyCardReturn && json.checkKeyCardReturn.count) {
                var person_data = json.checkKeyCardReturn.result[0].person_data;
                var person_name = person_data.first_name + ' ' + person_data.last_name;
                $("#popup-validate-message").html("<?php echo $formText_CardIsAlreadyAssignedToPersonInArx_Output;?>: " + person_name + "<br><?php echo $formText_SavingWillRemoveKeycardFromPreviousPersonAndAssignToNewOne_output; ?>", true);
                $("#popup-validate-message").show();
                $('.checking-keycard-progress').hide();
            } else {
                $("#popup-validate-message").html("", true);
                $("#popup-validate-message").hide();
                $('.checking-keycard-progress').hide();
                if (typeof(callback) === 'function') callback();
            }
        }, false);
    }
	function checkKeycardAmadeus(config_id, callback) {
        var data = {
            action: 'checkKeyCardAmadeus',
            config_id: config_id,
			contactpersonId: '<?php echo $contactpersonId;?>'
        };

        $('.checking-keycard-progress').show();

        ajaxCall('edit_contactperson_keycard', data, function(json) {
            if(json.checkKeyCardReturn && json.checkKeyCardReturn.count) {
                var person_data = json.checkKeyCardReturn.result[0].person_data;
                var person_name = person_data.first_name + ' ' + person_data.last_name;
                $("#popup-validate-message").html("<?php echo $formText_CardIsAlreadyAssignedToPersonInAmadeus_Output;?>: " + person_name + "<br><?php echo $formText_SavingWillRemoveKeycardFromPreviousPersonAndAssignToNewOne_output; ?>", true);
                $("#popup-validate-message").show();
                $('.checking-keycard-progress').hide();
            }
            else {
                $("#popup-validate-message").html("", true);
                $("#popup-validate-message").hide();
                $('.checking-keycard-progress').hide();
                if (typeof(callback) === 'function') callback();
            }
        }, false);
    }

    // Check number on API
    $('[name="external_locksystem_arx"]').off('change').on('change', function() {
        $("#popup-validate-message").html("", true);
        $("#popup-validate-message").hide();
		if($(this).is(':checked')) checkKeycardArx();
    });
	$('input.external_locksystem_amadeus').off('change').on('change', function() {
        $("#popup-validate-message").html("", true);
        $("#popup-validate-message").hide();
		if($(this).is(':checked')) checkKeycardAmadeus($(this).val());
    });
    $('.edit-access-card-number').off('click').on('click', function(e) {
        e.preventDefault();
        fw_loading_start();
        var _data = { fwajax: 1, fw_nocss: 1, contactpersonId: '<?php echo $contactpersonId;?>' };
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_keycard_numbers";?>',
            data: _data,
            success: function(obj){
                fw_loading_end();
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(obj.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
            }
        });
    });



    // Submit form
    $("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
            $("#popup-validate-message").html('');
			$.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (json) {
					if(json.error !== undefined)
					{
						var _msg = '';
						$.each(json.error, function(index, value){
							var _type = Array("error");
							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
							_msg = _msg + '<div class="type_' + _type[0] + '">' + value + '</div>';
						});
						
						$("#popup-validate-message").html(_msg, true);
						$("#popup-validate-message").show();
					} else {
						if(json.redirect_url !== undefined)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						}
					}
					fw_loading_end();
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
