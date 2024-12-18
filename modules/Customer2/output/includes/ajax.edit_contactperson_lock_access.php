<?php
$contactpersonId = $_POST['contactpersonId'] ? ($_POST['contactpersonId']) : 0;
$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;

// Keycards
$integration = 'IntegrationArx';
$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
if (file_exists($integration_file)) {
    require_once $integration_file;
    if (class_exists($integration)) {
        if (isset($api)) unset($api);
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

// Get contactperson data
$o_query = $o_main->db->get_where('contactperson', array('id' => $_POST['contactpersonId']));
$contactperson_data = $o_query ? $o_query->row_array() : array();

// Check keycard
// $checkKeyCardOnArx = checkKeyCardOnArx($_POST['access_card_number']);

// Create person if ID does not exist on ARX, update if exists already
$external_locksystem_person_id = ($contactperson_data['external_locksystem_person_id'] != '' ? $contactperson_data['external_locksystem_person_id'] : 'DCODE_ID_' . $contactperson_data['id']);
$external_locksystem2_person_id = ($contactperson_data['external_locksystem2_person_id'] != '' ? $contactperson_data['external_locksystem2_person_id'] : 'D' . $contactperson_data['id']);

// On form submit
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        $o_query = $o_main->db->get_where('contactperson', array('id' => $_POST['contactpersonId']));
        $contactperson_data = $o_query ? $o_query->row_array() : array();
		
        // Divide name in parts
        $name_parts = explode(" ", preg_replace('/\s+/', ' ', $contactperson_data['name'].' '.$contactperson_data['middlename'].' '.$contactperson_data['lastname']));
        $first_name = $name_parts[0];
        $last_name = '';
        for($i = 1; $i < count($name_parts); $i++) {
            $last_name .= $name_parts[$i] . ' ';
        }

        $last_name = trim($last_name);
		
		// Validation
		foreach($v_amadeus_systems as $l_key => $v_amadeus)
		{
			if(isset($_POST['access_categories_amadeus_'.$l_key]) && 0 < count($_POST['access_categories_amadeus_'.$l_key]))
			{
				if(empty($last_name))
				{
					$fw_error_msg[] = $formText_LastNameCannotBeEmpty_Output;
					return;
				}
			}
		}

        $api->add_person($external_locksystem_person_id, $first_name, $last_name, $contactperson_data['external_locksystem_pin']);
        $api->update_person_access_categories($external_locksystem_person_id, $_POST['access_categories']);

		foreach($v_amadeus_systems as $l_key => $v_amadeus)
		{
			$v_amadeus['api']->add_update_person($external_locksystem2_person_id, $first_name, $last_name, $contactperson_data['external_locksystem_pin']);
			$v_amadeus['api']->update_person_access_categories($external_locksystem2_person_id, $_POST['access_categories_amadeus_'.$l_key]);
		}

        $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId;
	}
}

$access_card_number = $contactperson_data['access_card_number'];

// Get person data from arx
$assigned_categories = array();
if ($external_locksystem_person_id) {
    $person_arx_data = $api->get_person_by_id($external_locksystem_person_id);

    if ($person_arx_data['access_categories']) {
        foreach ($person_arx_data['access_categories']->access_category as $category) {
            array_push($assigned_categories, (string)$category->name);
        }
    }
}
?>
<div class="popupform">
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson_lock_access";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="contactpersonId" value="<?php echo $contactpersonId;?>">
		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
		<div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Contactperson_Output; ?></div>
                <div class="lineInput">
                    <?php echo preg_replace('/\s+/', ' ', $contactperson_data['name'].' '.$contactperson_data['middlename'].' '.$contactperson_data['lastname']); ?>
                </div>
                <div class="clear"></div>
            </div>
			
			<?php
			$s_buffer = '';
			$access_categories = $api->get_all_access_categories();
			$grouped_access_categories = array();

			foreach($access_categories as $category) {
				$group = (string)$category->domain->name;
				if (!isset($grouped_access_categories[$group])) $grouped_access_categories[$group] = array();

				array_push($grouped_access_categories[$group], $category);
			}
			?>
			<div class="lineTitle"><?php echo $formText_AccessLevels_Output; ?></div>
			<div>
				<ul id="output-keycard-tab" class="nav nav-tabs" role="tablist">
					<?php
					$l_count = 1;
					foreach($grouped_access_categories as $group => $categories)
					{
						?><li role="presentation"<?php echo (1 == $l_count ? ' class="active"' : '');?>><a href="#keycards-sys-<?php echo $l_count;?>" aria-controls="keycards-sys-<?php echo $l_count;?>" role="tab" data-toggle="tab"><?php echo $group;?></a></li><?php
						ob_start();
						?><div role="tabpanel" class="tab-pane<?php echo (1 == $l_count ? ' active' : '');?>" id="keycards-sys-<?php echo $l_count;?>"><table class="table output-keycard-grid"><tr><td><?php
						$l_grid_count = 0;
						$l_total_count = sizeof($categories);
						$l_break_count = ceil($l_total_count / 3);
						foreach($categories as $category)
						{
							if($l_grid_count > 0 && 0 == $l_grid_count % $l_break_count)
							{
								echo '</td><td>';
							}
							$l_grid_count++;
							?>
							<div>
							<input type="checkbox" name="access_categories[]" value="<?php echo (string)$category->name; ?>" <?php echo in_array( (string)$category->name, $assigned_categories) ? 'checked="checked"' : ''; ?>>  <?php echo (string)$category->name; ?>
							</div><?php
						}
						?></td></tr></table></div><?php
						$s_buffer .= ob_get_contents();
						ob_end_clean();
						$l_count++;
					}
					foreach($v_amadeus_systems as $l_key => $v_amadeus)
					{
						// Get person data from Amadeus
						$assigned_categories_amadeus = array();
						if($external_locksystem2_person_id)
						{
							$v_response = $v_amadeus['api']->get_person_by_id($external_locksystem2_person_id);
						
							if($v_response['access_categories'])
							{
								foreach($v_response['access_categories'] as $category)
								{
									array_push($assigned_categories_amadeus, $category);
								}
							}
						}
						$access_categories_amadeus = $v_amadeus['api']->get_all_access_categories();
						if(sizeof($access_categories_amadeus))
						{
							?><li role="presentation"<?php echo (1 == $l_count ? ' class="active"' : '');?>><a href="#keycards-sys-<?php echo $l_count;?>" aria-controls="keycards-sys-<?php echo $l_count;?>" role="tab" data-toggle="tab"><?php echo $v_amadeus['name'];?></a></li><?php
							ob_start();
							?><div role="tabpanel" class="tab-pane<?php echo (1 == $l_count ? ' active' : '');?>" id="keycards-sys-<?php echo $l_count;?>"><table class="table output-keycard-grid"><tr><td><?php
							$l_grid_count = 0;
							$l_total_count = sizeof($access_categories_amadeus);
							$l_break_count = ceil($l_total_count / 3);
							foreach($access_categories_amadeus as $category)
							{
								if($l_grid_count > 0 && 0 == $l_grid_count % $l_break_count)
								{
									echo '</td><td>';
								}
								$l_grid_count++;
								?>
								<div>
									<input type="checkbox" name="access_categories_amadeus_<?php echo $l_key;?>[]" value="<?php echo $category['Name']; ?>" <?php echo in_array($category['Name'], $assigned_categories_amadeus) ? 'checked="checked"' : ''; ?>> <?php echo $category['Name']; ?>
								</div>
								<?php
							}
							?></td></tr></table></div><?php
							$s_buffer .= ob_get_contents();
							ob_end_clean();
							$l_count++;
						}
					}
					?>
				</ul>
				<div class="tab-content"><?php echo $s_buffer;?></div>
			</div>
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
$(document).ready(function(){
	$('#output-keycard-tab li a').click(function(e){e.preventDefault();$(this).tab('show');$(window).trigger('resize');});
	
    // Submit form
    $("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
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
.output-keycard-grid td {
	border:none !important;
}
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
