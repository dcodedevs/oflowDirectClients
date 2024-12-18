<?php
$variables->account_root_url = account_root_url();
$variables->account_framework_url = account_root_url()."fw/";
$s_fw_account_upgrade_check = ACCOUNT_PATH."/upgrade";
$s_fw_account_upgrade_lock_check = ACCOUNT_PATH."/upgrade_lock";
$b_fw_account_upgrade = $b_fw_account_upgrade_lock = false;

/**
 * Group switcher
 */
// Account config variables
$fw_activate_groups = $variables->accountinfo['activateGroups'];
$fw_read_group_data_from_external_database = $variables->accountinfo['readGroupDataFromExternalDatabase'];
$fw_groups_list = array();

// Account config lang "variables"
$selectGroupText = $variables->accountinfo['selectGroupText'];
$noAccessToAnyGroupText = $variables->accountinfo['noAccessToAnyGroupText'];
$changeGroupButtonText = $variables->accountinfo['changeGroupButtonText'];

$fw_getynet_class = "original";

// If groups activated
if ($fw_activate_groups) {

    // Read data from external database
    if ($fw_read_group_data_from_external_database) {
        // change databse to external
        $o_main->database('external', false, true);
		$o_main->db2 = $o_main->database('external', true, true);
    }

    // Load group access check addon script
    $group_check_script = __DIR__ . '/../fw_addon_check_group_access.php';
    if (file_exists($group_check_script)) {
        require $group_check_script;
    } else {
        // If script deleted by accident shim version that always returns false and show error
        function fw_check_group_access() {
            return false;
        }
        echo 'fw_addon_check_group_access.php missing!';
    }

    // Read group list
    $group_table = $variables->accountinfo['groupTable'];
    $group_field_to_list = $variables->accountinfo['groupFieldToList'];
    $o_query = $o_main->db->query("SELECT id, $group_field_to_list name FROM $group_table ORDER BY $group_field_to_list ASC");
	if ($o_query && $o_query->num_rows()) {
		foreach ($o_query->result_array() as $group_row) {
	        if (fw_check_group_access($group_row['id'])) {
				$fw_groups_list[$group_row['id']] = $group_row;
	        }
	    }
	}


    // Get selected group
    $fw_selected_group = $_COOKIE['selected_group'] ? $_COOKIE['selected_group'] : 0;
	$fw_selected_group_name = $fw_groups_list[$fw_selected_group]['name'];
    if (isset($_GET['select_group'])) $fw_selected_group = $_GET['select_group'] ? $_GET['select_group'] : 0;
    if (!fw_check_group_access($fw_selected_group)) $fw_selected_group = 0;
    setcookie('selected_group', $fw_selected_group);

    // Close external db connection
    if ($fw_read_group_data_from_external_database) {
        $o_main->database('default', false, true);
    }
}
else {
    function fw_check_group_access() {
        return false;
    }
}
/* End of Group switcher*/

/* Membersystem access check */
$b_is_membership = TRUE;
if(1==0 && $variables->useradmin == 0 && $variables->accountinfo['activate_crm_user_content_filtering_tags'])
{
	if($variables->accountinfo['crm_account_url'] != "" && $variables->accountinfo['crm_access_token'] != ""  && $variables->accountinfo['crm_account_module'] != "")
	{
		$b_is_membership = FALSE;
		$s_sql = "SELECT * FROM crm_user_content_filtering_tags WHERE username = ?";
		$o_query = $o_main->db->query($s_sql, array($_COOKIE['username']));
		if($o_query && $o_query->num_rows()>0)
		{
			$tags_info = $o_query->row_array();
			$membership_settings = json_decode($tags_info['membership_settings'], TRUE);
			if(sizeof($membership_settings) > 0) $b_is_membership = TRUE;
		}
	}
}
/* End Membersystem access check */

if(is_file($s_fw_account_upgrade_check))
{
	$b_fw_account_upgrade_lock = is_file($s_fw_account_upgrade_lock_check);

	$v_response = json_decode(APIconnectorAccount("account_upgrade_check", $variables->accountinfo['accountname'], $variables->accountinfo['password']),true);
	if(isset($v_response['error'], $v_response['status']) && $v_response['status'] == 0)
	{
		// remove upgrade file
		if(!$b_fw_account_upgrade_lock)
		{
			unlink($s_fw_account_upgrade_check);
			$b_fw_account_upgrade = FALSE;
		}
	} else {
		$s_fw_account_upgrade_time = $v_response['data'];
		if($v_response['data'] != file_get_contents($s_fw_account_upgrade_check))
		{
			include(ACCOUNT_PATH."/modules/Languages/input/includes/ftp_commands.php");
			ftp_file_put_content("/upgrade", $v_response['data']);
		}
		$b_fw_account_upgrade = TRUE;
	}
}

$variables->validate_fw_mobile_code = FALSE;
if(!$b_fw_account_upgrade_lock && $variables->accountinfo['access_only_by_mobile_code'] == 1)
{
	$o_query = $o_main->db->query("SELECT * FROM accountinfo_mobile_auth WHERE user_id = '".$o_main->db->escape_str($variables->userID)."' AND expire >= NOW() AND object = 'fw' LIMIT 1");
	$l_valid = $o_query ? $o_query->num_rows() : 0;

	if(0 === $l_valid)
	{
		$variables->validate_fw_mobile_code = TRUE;
		$v_user_profile = json_decode($variables->fw_session['user_profile'], TRUE);

		if(!isset($_POST['fw_verify_mobile_code'], $_POST['fw_verify_mobile_id']))
		{
			if($v_user_profile['mobile_verified'] == 1)
			{
				$s_commands = 'verify_mobile_send_code';
				$v_param = array(
					'USER_ID' => $v_user_profile['id'],
					'MOBILE' => $v_user_profile['mobile'],
					'MOBILE_PREFIX' => $v_user_profile['mobile_prefix'],
					'COMPANY_ID' => $_GET['companyID'],
				);
				$s_response = APIconnectorAccount($s_commands, $variables->accountinfo['accountname'], $variables->accountinfo['password'], $v_param);
				$fw_verify_mobile_code = json_decode($s_response, true);
			} else {
				$fw_verify_mobile_code['status'] = 20;
			}
		} else {
			$s_commands = 'verify_mobile_check_code';
			$v_param = array(
				'ID' => $_POST['fw_verify_mobile_id'],
				'CODE' => $_POST['fw_verify_mobile_code'],
				'MOBILE' => $_POST['fw_verify_mobile'],
			);
			$s_response = APIconnectorAccount($s_commands, $variables->accountinfo['accountname'], $variables->accountinfo['password'], $v_param);
			$v_response = json_decode($s_response, true);
			if(isset($v_response['status']) && $v_response['status'] == 1)
			{
				$l_expire_interval = intval($variables->accountinfo['mobile_code_valid_for_minutes']);
				if(0 === $l_expire_interval) $l_expire_interval = 60*24;
				$o_main->db->query("INSERT INTO accountinfo_mobile_auth SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', user_id = '".$o_main->db->escape_str($variables->userID)."', object = 'fw', expire = DATE_ADD(NOW(), INTERVAL ".$l_expire_interval." MINUTE)");
				header('Location: '.fwCurrentPageUrl());
				exit;
			} else {
				$fw_verify_mobile_code['id'] = $_POST['fw_verify_mobile_id'];
				$fw_verify_mobile_code['mobile'] = $_POST['fw_verify_mobile'];
				$fw_verify_mobile_code['status'] = 10;
			}
		}
	}
}

if(isset($_POST['fwajax']) && $_POST['fwajax'] == 1)
{
	$include_file = __DIR__."/getynet_fw/languages/default.php";
	if(is_file($include_file)) include($include_file);
	$include_file = __DIR__."/getynet_fw/languages/".$variables->languageID.".php";
	if(is_file($include_file)) include($include_file);
	$include_file = __DIR__."/getynet_fw/includes/include.developeraccess.php";
	if(is_file($include_file)) include($include_file);

	$return = $fw_error_msg = $fw_return_data = array();
	$fw_column = 1;
	$ob_javascript = $fw_redirect_url = $fw_module_head = "";

	header('Content-Type: text/html; charset=utf-8');
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	if($b_fw_account_upgrade_lock)
	{
		$return['error'] = array("Account upgrade in progress");
	} else if(!$b_is_membership)
	{
		$return['error'] = array($formText_YouCannotAccessThisAccountBecauseYouDoNotHaveAnActiveMembership_Framework);
	} else {
		if($variables->validate_fw_mobile_code)
		{
			require(__DIR__.'/getynet_fw/verify_mobile/output.php');
			$return['html'] = ob_get_clean();
			$return['column'] = 1;
			$return['columns'] = 1;
		} else if(isset($_POST['fwmenu']))
		{
			$v_menu_list = array();
			include(__DIR__.'/account_fw/menu/output_menu_json.php');
			ob_clean();
			$return['menu'] = $v_menu_list;
		} else {
			ob_clean();
			$fw_custom_width_column_1 = $fw_custom_width_column_2 = '';
			$moduleCol1MaxWidth = $moduleCol1MinWidth = $moduleCol2MaxWidth = $moduleCol2MinWidth = '';
			include(__DIR__.'/account_fw/content/output.php');
			$return['html'] = ob_get_clean();
			$return['column'] = $fw_column;
			$return['columns'] = $fw_columns;
			$return['width'] = $fw_custom_width_column_1.':'.$fw_custom_width_column_2;

			$return['col1MaxWidth'] = $moduleCol1MaxWidth;
			$return['col1MinWidth'] = $moduleCol1MinWidth;
			$return['col2MaxWidth'] = $moduleCol2MaxWidth;
			$return['col2MinWidth'] = $moduleCol2MinWidth;
			$return['module'] = ((isset($fw_module_menu_item) && $fw_module_menu_item != '') ? $fw_module_menu_item : 'mod_'.preg_replace('/\s+/', '', ($variables->is_virtual_module?$variables->virtual_module:$modulename)));
			$ob_javascript = trim($ob_javascript);
			if($ob_javascript != "") $return['javascript'] = $ob_javascript;
			if($fw_redirect_url != "") $return['redirect_url'] = $fw_redirect_url;
			if(isset($fw_error_msg) && count($fw_error_msg)>0) $return['error'] = $fw_error_msg;
			if(isset($fw_return_data)) $return['data'] = $fw_return_data;
			if($fw_module_head != "") $return['module_head'] = $fw_module_head;
		}
	}

	header('Content-type:application/json;charset=utf-8');
	echo json_encode($return);
	return;

} else {
?><!DOCTYPE html>
<html>
<head>
<title><?php echo $accountname;?></title>
<meta http-equiv="expires" content="0">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0<?php echo ($b_fw_getynet_app?', maximum-scale=1.0':'');?>">
<?php if(is_file(__DIR__.'/../lib/loadInput.php')) include(__DIR__.'/../lib/loadInput.php'); ?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
<?php
$v_css = array(
	'fw/account_fw/layout/layout.css',
	'fw/getynet_fw/blueline/output.css',
	'fw/account_fw/content/output.css',
);
if(!$b_fw_getynet_app)
{
	$v_css[] = 'fw/account_fw/menu/output.css';
	$v_css[] = 'fw/getynet_fw/modules/OnlineList/output/output.css';
	$v_css[] = 'fw/getynet_fw/modules/Chat/output/output.css';
	$v_css[] = 'fw/getynet_fw/modules/NotificationCenter/output/output.css';
}
foreach($v_css as $s_item)
{
	$l_time = filemtime(BASEPATH.$s_item);
	?><link href="<?php echo $variables->account_root_url.$s_item.'?v='.$l_time;?>" rel="stylesheet" type="text/css" /><?php
}
$v_script = array(
	'ckeditor/ckeditor.js',
	'fw/account_fw/js/jquery.tinyscrollbar.min.js',
	// Fuzzy search libs
	'fw/getynet_fw/blueline/js/list.js',
	'fw/getynet_fw/blueline/js/list.fuzzysearch.min.js',
);
foreach($v_script as $s_item)
{
	$l_time = filemtime(BASEPATH.$s_item);
	?><script type="text/javascript" src="<?php echo $variables->account_root_url.$s_item.'?v='.$l_time;?>"></script><?php
}
?>
<script type="text/javascript">
//removed not needed any more
// window.onerror = function (msg, url, lineNo, columnNo, error) {
// 	var string = msg.toLowerCase();
// 	var substring = "script error";
// 	var log_errors = [
// 		'resizeobserver loop limit exceeded'
// 	];
//
// 	if (string.indexOf(substring) > -1){
// 		alert('Script Error: See Browser Console for Detail');
// 	} else {
// 		var message = [
// 			'Error message: ' + msg,
// 			'URL: ' + url,
// 			'Line: ' + lineNo,
// 			'Column: ' + columnNo,
// 			'Error object: ' + JSON.stringify(error)
// 		];
//
// 		if(log_errors.indexOf(string) > -1){
// 			console.log(message);
// 		} else {
// 			alert(message.join(' - '));
// 		}
// 	}
//
// 	return true;
// };
</script>
</head>
<?php
	$acceptedTerms = true;
	// GetynetApp html exclude
	$fwBrandImage = $fwLogoImage = $fwShowBrandLine = $fwShowContactList = $fwBrandSecondLogoImage = $fwShowBrandSecondLogo = $b_activate_channel_admin = false;

	if($variables->fw_settings_basisconfig)
	{
		$fwShowContactList = $variables->fw_settings_basisconfig['showContactList'];
		$b_activate_channel_admin = $variables->fw_settings_basisconfig['activate_channel_admin'];
		if(is_file(BASEPATH.'elementsGlobal/'.$variables->fw_settings_basisconfig['brand_logo_in_elements_global']))
		{
			$fwShowBrandSecondLogo = $variables->fw_settings_basisconfig['show_brand_logo'];
			$fwBrandSecondLogoImage = $variables->account_root_url.'elementsGlobal/'.$variables->fw_settings_basisconfig['brand_logo_in_elements_global'];
		}
		if($variables->fw_settings_basisconfig['user_alternative_design']) {
			$fw_getynet_class = "alternative";
		}
	}
	// If there is any data
	if($variables->fw_settings_accountconfig)
	{
		$fwSettingsData = $variables->fw_settings_accountconfig; // default settings

		// load specific framework settings
		$selectedGroupId = fw_check_group_access($_COOKIE['selected_group']) ? $_COOKIE['selected_group'] : 0;
		if ($selectedGroupId) {
			$o_query = $o_main->db->query("SELECT * FROM frameworksettings WHERE groupId = ?", array($selectedGroupId));
			if ($o_query && $o_query->num_rows()) {
				$fwSettingsData = $o_query->row_array();
			}
		}
		// Brand image url
		$v_tmp = json_decode($fwSettingsData['headerBackground'],true);
		$fwBrandImage = $variables->account_root_url.$v_tmp[0][1][0];

		// Get logo
		$v_tmp = json_decode($fwSettingsData['logo'],true);
		$fwLogoImage = $variables->account_root_url.$v_tmp[0][1][0];

		if($fwSettingsData['show_brand_logo'] != '-1')
		{
			// Should we show brand second logo?
			$fwShowBrandSecondLogo = $fwSettingsData['show_brand_logo'];

			// Get Brand second logo
			$v_tmp = json_decode($fwSettingsData['brand_logo'],true);
			if(is_file(urldecode(BASEPATH.$v_tmp[0][1][0])))
			{
				$fwBrandSecondLogoImage = $variables->account_root_url.$v_tmp[0][1][0];
			} else {
				$fwShowBrandSecondLogo = 0;
			}
		}

		// Should we show brand line?
		$fwShowBrandLine = $fwSettingsData['showBrandLine'];

		// Should we show contact list
		if($fwSettingsData['showContactList'] != '-1')
		{
			$fwShowContactList = $fwSettingsData['showContactList'];
		}

		// Activate channel admin
		if($fwSettingsData['activate_channel_admin'] != '-1')
		{
			$b_activate_channel_admin = $fwSettingsData['activate_channel_admin'];
		}

		if($fwSettingsData['activate_account_settings'] && $fwSettingsData['account_settings_modulename'] !=""){

			$o_query = $o_main->db->query("SELECT * FROM account_terms WHERE content_status < 2 AND status = 1 ORDER BY version DESC");
			$term_info = $o_query ? $o_query->row_array() : array();
			if($term_info) {
				$acceptedTerms = false;
				$lastUpdatedDate = "0000-00-00";
				if($term_info['updated'] != "0000-00-00"){
					$lastUpdatedDate = date("Y-m-d H:i:s", strtotime($term_info['updated']));
				}
				$o_query = $o_main->db->query("SELECT * FROM account_terms_accepted WHERE account_terms_id = ? AND username = ? AND created > ?", array($term_info['id'], $variables->loggID, $lastUpdatedDate));
				$accepted_terms_info = $o_query ? $o_query->row_array() : array();
				if($accepted_terms_info) {
					$acceptedTerms = true;
				}
			}
		}
	}
?>
<body class="<?php echo $fw_getynet_class; if($variables->fw_settings_basisconfig['alternative_design_seperate_menu_scroll']) echo ' seperateMenuScroll';?>">
	<div id="fw_container" class="<?php echo $fw_getynet_class;?>">
		<?php if($acceptedTerms){ ?>
			<?php
			if($b_fw_getynet_app) ob_start(); ?>
			<div id="fw_getynet" class="<?php echo $fw_getynet_class;?>">
				<!-- Brand line  -->
				<?php if($fwShowBrandLine): ?>
				<div class="fw_brand_line" id="fw_brand_line" style="background-image:url('<?php echo $fwBrandImage; ?>');">
					<div class="fw_brand_logo">
						<img src="<?php echo $fwLogoImage; ?>" title="" alt="">
					</div>
				</div>
				<?php endif; ?>

				<!-- Blueline -->
				<?php include(__DIR__.'/getynet_fw/blueline/output.php'); ?>

				<!-- Upgrade line  -->
				<?php include(__DIR__.'/getynet_fw/upgradeline/output.php'); ?>

			</div>
			<?php if($b_fw_getynet_app) ob_clean(); ?>
			<?php if($b_is_membership && !$b_fw_account_upgrade_lock && !$variables->validate_fw_mobile_code) { ?>
			<div id="fw_account" class="<?php echo $fw_getynet_class;?>">
	            <?php if (!$fw_selected_group && $fw_activate_groups): ?>
	                <div class="fw_group_list">
	                    <div class="fw_group_list_title"><?php echo $selectGroupText ? $selectGroupText : 'Select group'; ?>:</div>
	                    <ul>
	                        <?php foreach ($fw_groups_list as $group): ?>
	                            <li>
	                                <a href="#" class="fw_group_list_item" data-group-id="<?php echo $group['id']; ?>">
										<?php echo $group['name']; ?>
										<?php if ($variables->fw_session['accesslevel'] == 1 || $variables->fw_session['accesslevel'] == 2): ?>
											[ID: <?php echo $group['id']; ?>]
										<?php endif; ?>
									</a>
	                            </li>
	                        <?php endforeach; ?>
	                    </ul>

	                    <?php if (!count($fw_groups_list)): ?>
	                        <?php echo $noAccessToAnyGroupText ? $noAccessToAnyGroupText : 'No access to any group'; ?>
	                    <?php endif; ?>

	                    <script>
	                    $(document).ready(function() {
	                        $('.fw_group_list_item').on('click', function(e) {
	                            e.preventDefault();
	                            document.cookie = "selected_group = " + $(this).data('group-id');
	                            document.location.reload();
	                        });
	                    });
	                    </script>
	                </div>
	            <?php else: ?>
	    			<?php
	    			ob_start();
	    			include(__DIR__.'/account_fw/menu/output.php');
	    			$ob_menu_column = trim(ob_get_clean());

	    			$fw_columns = 1;
	    			$ob_column1 = $ob_column2 = $fw_module_head = "";
	    			ob_start();
	    			$fw_column = 1;
	    			include(__DIR__.'/account_fw/content/output.php');
	    			$ob_column1 = trim(ob_get_clean());
	    			if($fw_columns == 2)
	    			{
	    				ob_start();
	    				$fw_column = 2;
	    				include(__DIR__.'/account_fw/content/output.php');
	    				$ob_column2 = trim(ob_get_clean());
	    			}
	    			?>
	    			<div class="fw_info_message_wraper"><div class="fw_info_messages"></div></div>

					<div class="fw_col <?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>tinyScrollbar<?php } ?> col0 <?php echo ($b_fw_getynet_app?' hide':'');?>">
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
							<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
		                	<div class="viewport">
		                     	<div class="overview">
						<?php } ?>
									<div id="fw_menu">
                                        <?php echo $ob_menu_column; ?>
                                    </div>
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
								</div>
							</div>
						<?php } ?>

	    			</div>
	    			<div class="fw_module_head_wrapper">
                        <div class="fw_menu_hamburger">
                            <span class="fw_menu_hamburger_label fw_module_head_color">
                                <?php echo $formText_Menu_Framework;?>
                                <span class="fas fa-bars"></span>
                            </span>
                        </div>
	    				<div class="fw_module_head fw_module_head_color">
	    					<?php echo $fw_module_head;?>
	    				</div>
	    				<!--  Module list button (on mobile) -->
	    				<a class="fw_module_list_button fw_link_box" id="fw_module_list_btn">
	    					<span class="icon icon-menu"></span>
	    				</a>
	    				<!-- // Module list button (on mobile)-->
	    			</div>

	    			<div class="fw_col <?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?> tinyScrollbar<?php } ?> col1<?php echo ($fw_columns==1?' end':'').($b_fw_getynet_app && $fw_columns==2 && $ob_column2!=''?' hide':'');?>"
	    				<?php echo ((isset($fw_custom_width_column_1) && $fw_custom_width_column_1!='') ? ' data-width="'.$fw_custom_width_column_1.'"' : '');?>
	    				<?php echo ((isset($moduleCol1MaxWidth) && $moduleCol1MaxWidth!=null) ? ' data-maxwidth="'.$moduleCol1MaxWidth.'"' : '');?>
	    				<?php echo ((isset($moduleCol1MinWidth) && $moduleCol1MinWidth!=null) ? ' data-minwidth="'.$moduleCol1MinWidth.'"' : '');?>
	    				>
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
							<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
		                	<div class="viewport">
		                     	<div class="overview">
						<?php } ?>
								<div class="data"><?php echo $ob_column1;?></div>
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
								</div>
							</div>
						<?php } ?>
	    			</div>
	    			<div class="fw_col <?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?> tinyScrollbar<?php } ?> col2<?php echo ($fw_columns==1 || $ob_column2==''?' hide':' end');?>"
	    				<?php echo ((isset($fw_custom_width_column_2) && $fw_custom_width_column_2!='') ? ' data-width="'.$fw_custom_width_column_2.'"' : '');?>
	    				<?php echo ((isset($moduleCol2MaxWidth) && $moduleCol2MaxWidth!=null) ? ' data-maxwidth="'.$moduleCol2MaxWidth.'"' : '');?>
	    				<?php echo ((isset($moduleCol2MinWidth) && $moduleCol2MinWidth!=null) ? ' data-minwidth="'.$moduleCol2MinWidth.'"' : '');?>
	    				>
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
							<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
		                	<div class="viewport">
		                     	<div class="overview">
						<?php } ?>
								<div class="data forceFullWidthModuleContent"><?php echo $ob_column2;?></div>
						<?php if(!$variables->fw_settings_basisconfig['user_alternative_design']) {?>
								</div>
							</div>
						<?php } ?>
	    			</div>
	    			<?php
					// ALI - not in use anymore (2019-01-10)
					/*if(!$b_fw_getynet_app) { ?>
	    			<!-- CONTACT LIST -->
	    			<div class="fw_contact_list ">
	    				<?php require(__DIR__ . '/getynet_fw/modules/OnlineList/output/output.php'); ?>
	    			</div>
	    			<!-- // CONTACT LIST -->
	    			<?php }*/ ?>
	    			<div class="fw_clear_both"></div>
	            <?php endif; ?>
			</div>
			<?php if(!$b_fw_getynet_app) { ?>
				<!-- CHAT  -->
				<?php $chatActive = true; ?>
				<?php if ($chatActive): ?>
				<?php require(__DIR__ . '/getynet_fw/modules/Chat/output/output.php'); ?>
				<?php endif; ?>
				<!-- // CHAT -->
			<?php } ?>
			<?php } else {
				if($b_fw_account_upgrade_lock)
				{
					?><div id="fw_account" class="<?php echo $fw_getynet_class;?>" style="margin-top:50px;"><center><h3>Account upgrade in progress</h3></center></div><?php
				} else if(!$b_is_membership)
				{
					?><div id="fw_account" class="<?php echo $fw_getynet_class;?>" style="margin-top:50px;"><center><h3><?php echo $formText_YouCannotAccessThisAccountBecauseYouDoNotHaveAnActiveMembership_Framework;?></h3></center></div><?php
				} else {
					require(__DIR__.'/getynet_fw/verify_mobile/output.php');
				}
				?><style type="text/css">body{visibility:visible !important;}</style><?php
			}
			?>
		<?php } else {
			?>
			<div class="fw_info_message_wraper"><div class="fw_info_messages"></div></div>
			<?php
			include(__DIR__."/../modules/".$fwSettingsData['account_settings_modulename']."/output/include_into_fw.php");

			$include_file = __DIR__."/account_fw/menu/output_javascript.php";
			if(is_file($include_file)) include($include_file);
			?>
			<script type="text/javascript">
				$("body").css("visibility","visible");
			</script>
			<?php
		} ?>
	</div>

	<div id="show-on-ipad" class="show-on-ipad"></div>
	<div id="show-on-mobile" class="show-on-mobile"></div>
	<div id="fw_loading"><div><img border="0" src="<?php echo $variables->account_framework_url; ?>account_fw/menu/elementsOutput/ajax-loader.gif" /></div></div>

</body>
</html>
<?php
}
