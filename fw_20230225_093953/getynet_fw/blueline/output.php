<?php
$includeFile = __DIR__."/../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../languages/".$variables->languageID.".php";
if(is_file($includeFile)) include($includeFile);
$include_file = __DIR__."/../includes/include.developeraccess.php";
if(is_file($include_file)) include($include_file);

$profileimage = json_decode($variables->fw_session['profileimage'],true);
$link = "https://www.getynet.com/no/settings";

//check if People module exists and user is registered
$fw_people_added = false;
$sql = "SELECT m.* FROM moduledata m WHERE m.name = 'People'";
$o_query = $o_main->db->query($sql);
$peopleModule = $o_query ? $o_query->row_array() : array();
if($peopleModule){
	$people_contactperson_type = 2;
	$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
	$o_query = $o_main->db->query($sql);
	$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
	if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
		$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
	}

	$sql = "SELECT p.* FROM contactperson p WHERE p.email = ? AND p.type = ? AND p.content_status = 0";
	$o_query = $o_main->db->query($sql, array($variables->loggID, $people_contactperson_type));
	$peopleData = $o_query ? $o_query->row_array() : array();
	if($peopleData){
		$fw_people_added = true;
		$fw_people_module_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=People&folderfile=output&folder=output&inc_obj=details&cid=".$peopleData['id'];
	}
}

// Framework settings
$sql = "SELECT *, IF(id = '".$o_main->db->escape_str($variables->global_style_set)."', 0, 1) AS priority FROM frameworksettings_globalstyles WHERE id = '".$o_main->db->escape_str($variables->global_style_set)."' OR id > 0 ORDER BY priority, id LIMIT 1";
$o_query = $o_main->db->query($sql);
$framework_settings_globalstyles = $o_query ? $o_query->row_array() : array();

if($variables->fw_settings_accountconfig){
	if($variables->fw_settings_accountconfig['activateHelpButton'] >= 0){
		$variables->fw_settings_basisconfig['activateHelpButton'] = $variables->fw_settings_accountconfig['activateHelpButton'];
	}
	if($variables->fw_settings_accountconfig['activateReadIntroNewUsers'] >= 0){
		$variables->fw_settings_basisconfig['activateReadIntroNewUsers'] = $variables->fw_settings_accountconfig['activateReadIntroNewUsers'];
	}
}

// Helpcenter
$is_helpcenter_active = $variables->fw_settings_basisconfig['activateHelpButton'];
$b_help_page_active = 1 == $variables->fw_settings_basisconfig['activate_help_page'];

// Onboarding popup status check
$introArticle = false;
$fw_intro_force_read = false;
if($variables->fw_settings_basisconfig['activateReadIntroNewUsers']){
	$sql = "SELECT t.*
	FROM onboarding_read_by_user t
	WHERE t.username_read = ?
	AND t.article_name = ?";
	$o_query = $o_main->db->query($sql, array($variables->loggID, $variables->fw_settings_basisconfig['introArticle']));
	$onboardingContent = $o_query ? $o_query->row_array() : array();
	if(!$onboardingContent){
		$introArticle = true;
		$sql = "SELECT t.id id,
		t.moduleName moduleName,
		t.uniqueName uniqueName,
		c.languageID languageID,
		c.name name,
		c.text text,
		t.video,
		t.slideView,
		t.forceReadAll
		FROM onboarding_basisconfig t
		LEFT JOIN onboarding_basisconfigcontent c
		ON t.id = c.onboarding_basisconfigID
		WHERE t.moduleName = 'fw_blueline'
		AND t.uniqueName = ?";
		$o_query = $o_main->db->query($sql, array($variables->fw_settings_basisconfig['introArticle']));
		$onboardingContent = $o_query ? $o_query->row_array() : array();
		if($onboardingContent['forceReadAll'] > 0){
			$fw_intro_force_read = true;
		}
	}
}
//check if People module exists and user is registered
$sql = "SELECT m.* FROM moduledata m WHERE m.name = 'Frontpage' AND m.uniqueID = 57";
$o_query = $o_main->db->query($sql);
$peopleModule = $o_query ? $o_query->row_array() : array();
$frontpageModuleLink = "";
if($peopleModule){
	$frontpageModuleLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=Frontpage&folderfile=output&folder=output";
}
$poweredByLogo = $variables->account_framework_url."getynet_fw/blueline/elementsOutput/poweredBy.png";
if($variables->fw_settings_basisconfig['framework_logo'] != ""){
	$poweredByLogo = $variables->account_framework_url."getynet_fw/blueline/elementsOutput/".$variables->fw_settings_basisconfig['framework_logo'];
}
// $frameworkLogo = json_decode($variables->fw_settings_accountconfig['framework_logo']);
// if(count($frameworkLogo) > 0){
// 	$poweredByLogo = $variables->account_root_url.$frameworkLogo[0][1][0];
// }


if($b_help_page_active)
{
	$v_description = array(
		$formText_GeneralInfoAboutArticles_Helppage,
		$formText_GeneralInfoAboutModules_Helppage,
	);
	$v_subtitle = array(
		$formText_AllArticles_Helppage,
		$formText_AllModules_Helppage,
	);
	$v_help_article_tabs = array(
		$formText_Articles_Helppage,
		$formText_Modules_Helppage,
		/*$formText_Contact_Helppage,
		$formText_Search_Helppage,*/
	);

	$fw_helppage_content = '';
	$l_help_modulde = ((isset($_SESSION['help_page_module']) && '' != $_SESSION['help_page_module']) ? $_SESSION['help_page_module'] : '');
	$l_help_article_id = ((isset($_SESSION['help_page_article_id']) && 0 < $_SESSION['help_page_article_id']) ? $_SESSION['help_page_article_id'] : 0);
	$l_help_article_tab = ((isset($_SESSION['help_page_article_tab']) && 0 < $_SESSION['help_page_article_tab']) ? $_SESSION['help_page_article_tab'] : 0);
	if(1 >= $l_help_article_tab)
	{
		if($l_help_article_id == 0)
		{
			$v_params = array(
				'api_url' => 'https://help.getynet.com/api/',
				'module' => 'HelpArticle',
				'action' => 'get_help_articles',
				'params' => array(
					'app_id' => $variables->accountinfo['getynet_app_id'],
					'language_id' => $variables->accountinfo['customerlanguageID'],
					'article_id' => $l_help_article_id,
					'article_type' => $l_help_article_tab == 0 ? 1 : 2,
				)
			);
			$v_response = fw_api_call($v_params, FALSE);
			$v_help_articles = array();
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				$fw_helppage_content .= '<div class="fw-hp-description">'.$v_description[$l_help_article_tab].'</div>';
				$fw_helppage_content .= '<div class="fw-hp-subtitle">'.$v_subtitle[$l_help_article_tab].'</div>';
				foreach($v_response['items'] as $v_help_article)
				{
					$fw_helppage_content .= '<div class="fw-hp-article-btn fw-hp-related" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_help_article['id'].'" data-module="'.$v_help_article['module_folder'].'">'.$v_help_article['name'].'</div>';
				}
			}
		} else {
			$s_version = '';
			if(isset($l_help_modulde) && '' != $l_help_modulde)
			{
				$s_path = BASEPATH.'modules/'.$l_help_modulde;
				if($o_dir = opendir($s_path))
				{
					while(($s_file = readdir($o_dir)) !== FALSE)
					{
						if(strpos($s_file,".ver") > 0 && !stristr($s_file,"LCK"))
						{
							$s_version = str_replace("_",".",substr($s_file,0,strpos($s_file,".ver")));
						}
					}
				}
			}
			$v_params = array(
				'api_url' => 'https://help.getynet.com/api/',
				'module' => 'HelpArticle',
				'action' => 'get_help_content',
				'params' => array(
					'article_id' => $l_help_article_id,
					'version_from' => $s_version,
				)
			);
			$v_response = fw_api_call($v_params, FALSE);
			if(isset($v_response['status']) && 1 == $v_response['status'])
			{
				$fw_helppage_content .= '<div class="fw-hp-breadcrumb">';
				$fw_helppage_content .= '<span class="fw-hp-tab fw-hp-article-btn" data-tab-id="'.$l_help_article_tab.'">'.$v_help_article_tabs[$l_help_article_tab].'</span>';
				foreach($v_response['parents'] as $v_parent)
				{
					$fw_helppage_content .= ' > <span class="fw-hp-article-btn fw-hp-related'.($v_parent['level'] == 0 ? '-active' : '').'" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_parent['id'].'" data-module="'.$v_parent['module_folder'].'">'.$v_parent['name'].'</span>';
				}
				$fw_helppage_content .= '</div>';

				$fw_helppage_content .= '<div class="fw-hp-title">'.$v_response['title'].'</div>';
				$fw_helppage_content .= '<div class="fw-hp-description">'.$v_response['text'].'</div>';
				if(sizeof($v_response['items'])>0)
				{
					$fw_helppage_content .= '<div class="fw-hp-subtitle">'.$formText_RelatedArticles_Helppage.'</div>';
					foreach($v_response['items'] as $v_help_article)
					{
						$fw_helppage_content .= '<div class="fw-hp-article-btn fw-hp-related" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_help_article['id'].'" data-module="'.$v_help_article['module_folder'].'">'.$v_help_article['name'].'</div>';
					}
				}
			} else {
				$fw_helppage_content .= '<center>'.$formText_ContentNotFound_Helppage.'</center>';
			}
		}
	}
}
?>
<!-- Wraper  -->
<div class="wraper<?php if($variables->fw_settings_basisconfig['user_alternative_design']) echo ' top_line_wrapper';?> fw_header_color">
	<?php if($variables->fw_settings_basisconfig['user_alternative_design']) { ?>
		<div class="fw_logo powered_by">
			<?php if($frontpageModuleLink != "") {?>
				<a href="<?php echo $frontpageModuleLink?>">
			<?php } ?>
				<img src="<?php echo $poweredByLogo;?>" class="logo2" alt="" />
			<?php if($frontpageModuleLink != "") {?>
				</a>
			<?php } ?>
		</div>
		<?php
		if($variables->fw_session['developeraccessoriginal']>0)	{
			$link_parameters = "pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&";
			foreach($variables->menu_access as $item){
				if($item[3]!='A'){
					$link_parameters .= $item[1];
					break;
				}
			}
			?>
			<!--  Mode (button) -->
			<div class="fw_account_list_button fw_link_box" id="fw_change_mode_wrapper">
			<img class="user" src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/user-shield.png" alt="key">
				<span class="text"><?php
				foreach($developeraccesslevels as $key => $item){
					if($variables->developeraccess==$key) echo $item[0];
				} ?></span>
				<div class="fw_mode_change">
					<?php
					foreach($developeraccesslevels as $key => $item){
						if($key > $variables->fw_session['developeraccessoriginal']) break;
						?><div class="" role="group">
							<button type="button" role="group" class="btn btn-xs<?php echo ($variables->developeraccess==$key?' active':'');?>" data-value="<?php echo $key;?>" title="<?php echo $item;?>" onClick="javascript: fw_loading_start(); $('#fw_change_mode_form input').val($(this).data('value')); $('#fw_change_mode_form').submit();"><?php echo $item;?></button>
						</div><?php
					}
					?>
				</div>
			</div><!-- // Mode (button) -->
			<form id="fw_change_mode_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?'.$link_parameters;?>">
				<input type="hidden" name="fw_setdeveloperaccess" value="0">
			</form>
			<?php
		}

		if(isset($variables->accountinfo_basisconfig['account_type']) && 'webapp' == $variables->accountinfo_basisconfig['account_type'])
		{
			$o_find = $o_main->db->query("SELECT * FROM language WHERE webapp_language = 1 AND published_webapp_language = 1 ORDER BY default_webapp_language DESC, name ASC");
			if($o_find && $o_find->num_rows()>1)
			{
				$v_items = $o_find->result_array();
				?>
				<!--  Language choise (button) -->
				<div class="fw_account_list_button fw_link_box" id="fw_change_language_wrapper">
				<?php /*?><img class="user" src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/user-shield.png" alt="key"><?php */?>
					<span class="text"><?php
					foreach($v_items as $v_item)
					{
						if($variables->fw_session['accountlanguageID'] == $v_item['languageID']) echo $v_item['name'];
					}
					?></span>
					<div class="fw_language_change">
						<?php
						foreach($v_items as $v_item)
						{
							?><div class="" role="group">
								<button type="button" role="group" class="btn btn-xs<?php echo ($variables->fw_session['accountlanguageID'] == $v_item['languageID']?' active':'');?>" data-value="<?php echo $v_item['languageID'];?>" title="<?php echo $v_item['name'];?>" onClick="javascript: fw_loading_start(); $('#fw_change_language_form input').val($(this).data('value')); $('#fw_change_language_form').submit();"><?php echo $v_item['name'];?></button>
							</div><?php
						}
						?>
					</div>
				</div><!-- // Language choise (button) -->
				<form id="fw_change_language_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?'.$link_parameters;?>">
					<input type="hidden" name="fw_set_account_language" value="">
				</form>
				<?php
			}
		}
		?>
	<?php } else {
		if($fwShowBrandSecondLogo){ ?>
		<!--  Logo -->
		<div class="fw_brand_second_logo">
			<img src="<?php echo $fwBrandSecondLogoImage;?>" class="desktop" alt="" />
			<?php /*?><img src="<?php echo $fwBrandSecondLogoImage;?>" class="mobile" alt="" /><?php */?>
		</div>
		<div class="fw_logo powered_by">
			<img src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/powered_by_getynet_logo.svg" class="desktop logo2" alt="" />
			<img src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/getynet_logo_small_black.svg" class="mobile" alt="" />
		</div>
		<!-- // Logo -->
		<?php } else { ?>
		<!--  Logo -->
		<div class="fw_logo">
			<img src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/getynet_logo.svg" class="desktop" alt="" />
			<img src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/getynet_logo_small_black.svg" class="mobile" alt="" />
		</div>
		<!-- // Logo -->
	<?php }
	} ?>

	<!-- Buttons -->
	<div class="fw_settings">
		<?php if($variables->fw_settings_basisconfig['user_alternative_design']) { ?>

			<?php require(__DIR__ . '/../modules/NotificationCenter/output/output.php'); ?>

			<!--  Chat -->
			<a href="#" class="fw_chat_header_button fw_link_box" id="fw_chat_header_button">
				<div class="mobile" style="display: none;"><span class="fas fa-comments"></span></div>
				<div class="desktop">
					<div class="topChatIconWrapper">
						<span class="icon icon-chat"></span>
						<span class="message-count">0</span>
					</div>
					<?php /*?><div class="topChatIconWrapper hide" style="display:none !important;">
						<span class="glyphicon glyphicon-bullhorn" style=" font-size:16px;"></span>
						<span class="channel-count">0</span>
					</div><?php */?>
				</div>
			</a>
			<!-- // Chat -->
		<?php } ?>
		<!-- Profile -->
		<a href="<?php if($fw_people_added) { echo $fw_people_module_link; } else { echo $link;}?>" class="fw_profile fw_link_box">
			<span class="fw_profile_image_crop">
				<?php if(is_array($profileimage)){ ?>
				<img src="https://pics.getynet.com/profileimages/<?php echo $profileimage[1];?>" alt="" />
				<?php } ?>
			</span>

			<span class="fw_profile_name"><?php echo $variables->fw_session['fullname']; ?></span>

		</a><!-- // Profile -->

		<?php if($variables->fw_settings_basisconfig['user_alternative_design']) { ?>
			<span class="fw_menu_wrapper fw_link_box"  id="fw_accounts_list_btn">
				<img class="key" src="<?php echo $variables->account_framework_url;?>getynet_fw/blueline/elementsOutput/key-new.png" alt="key">
				<span class="text"><?php echo $formText_MyAccounts_blueline;?></span>
			</span>

			<a class="fw_menu_wrapper fw_link_box fw_module_list_button mobile" style="display: none;" id="fw_module_list_btn2">
				<span class="icon icon-menu"></span>
			</a>

			<span class="fw_menu_wrapper fw_link_box desktop"  id="fw_menu_options">
				<span class="glyphicon glyphicon-triangle-bottom"></span>
			</span>
			<div class="fw_logout_menu_dropdown">
				<div class=""></div>
				<!--  Log out button -->
				<a href="<?php echo $_SERVER['PHP_SELF'];?>?logout=1" class="fw_logout_button fw_link_box" id="fw_logut_btn" title="<?php echo $formText_Logout_blueline;?>">
					<span class="icon icon-logout"></span>
					<span ><?php echo $formText_Logout_blueline?></span>
				</a>			<!-- // Log out button -->
			</div>
		<?php } else { ?>
			<!--  Chat -->
			<a href="#" class="fw_chat_header_button fw_link_box" id="fw_chat_header_button">
				<span class="icon icon-chat"></span>
				<span class="message-count">0</span>
				<?php /*?><span class="glyphicon glyphicon-bullhorn" style="padding:0 5px 0 10px; font-size:16px;"></span>
				<span class="channel-count">0</span><?php */?>
			</a><!-- // Chat -->

			<?php if($is_helpcenter_active) { ?>
				<a href="#" class="fw_help_center_button fw_link_box helpcenter_link" data-module-name="fw_blueline" data-help-entry="<?php if($introArticle) { echo $variables->fw_settings_basisconfig['introArticle']; } else { echo $variables->fw_settings_basisconfig['helpArticle']; }?>">
					<span class="glyphicon glyphicon-question-sign"></span>
					<span class="title"><?php echo $formText_Help_blueline;?></span>
				</a>
			<?php } ?>

			<!--  Account list (button) -->
			<a class="fw_account_list_button fw_link_box" id="fw_accounts_list_btn">
				<span class="icon glyphicon glyphicon-user"></span>
				<span class="title"><?php echo $formText_MyAccounts_blueline; ?></span>
			</a><!-- // Account list (button) -->

			<!--  Log out button -->
			<a href="<?php echo $_SERVER['PHP_SELF'];?>?logout=1" class="fw_logout_button fw_link_box" id="fw_logut_btn" title="<?php echo $formText_Logout_blueline;?>">
				<span class="icon icon-logout"></span>
			</a><!-- // Log out button -->
		<?php } ?>
	</div><!-- // Buttons -->

	<div class="fw_clear_both"></div>

</div><!-- // Wrapper -->
<?php
//global styles
if($framework_settings_globalstyles) {?>
	<style>
		#fw_getynet.alternative .fw_header_color {
			background: <?php echo $framework_settings_globalstyles['header'];?>;
		}
		#fw_container.alternative .fw_module_head_color {
			background: <?php echo $framework_settings_globalstyles['module_header'];?>;
		}
		#fw_account.alternative .fw_module_head.fw_module_head_color li.active {
			background: <?php echo $framework_settings_globalstyles['module_header'];?>;
		}
		#fw_container.alternative .fw_button_color,
		#fw_container.alternative .fw_button_color:hover {
			background: <?php echo $framework_settings_globalstyles['buttons'];?>;
			color: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['buttons'];?>;
		}
		#fw_container.alternative .fw_button_not_filled_color,
		#fw_container.alternative .fw_button_not_filled_color:hover {
			color: <?php echo $framework_settings_globalstyles['buttons'];?>;
			background: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['buttons'];?>;
		}
		#fw_container.alternative .fw_filter_color {
			color: <?php echo $framework_settings_globalstyles['filter'];?>;
		}
		#fw_container.alternative .fw_filter_color .arrowDown {
			border-top-color: <?php echo $framework_settings_globalstyles['filter'];?>;
			color: <?php echo $framework_settings_globalstyles['filter'];?>;
		}
		#fw_container.alternative .fw_text_link_color,
		#fw_container.alternative .fw_text_link_color:hover {
			color: <?php echo $framework_settings_globalstyles['text_links'];?>;
		}
		#fw_container.alternative .fw_text_link_color:hover {
			font-weight: bold;
		}
		#fw_container.alternative .fw_icon_title_color {
			color: <?php echo $framework_settings_globalstyles['icons_title'];?>;
		}
		#fw_container.alternative .fw_icon_color {
			color: <?php echo $framework_settings_globalstyles['icons'];?>;
		}
		#fw_container.alternative .fw_delete_edit_icon_color {
			color: <?php echo $framework_settings_globalstyles['delete_edit_icons'];?>;
		}
		#fw_container.alternative #fw_menu .fw_menu_color.active a,
		#fw_container.alternative #fw_menu .fw_menu_color:hover a,
		#fw_container.alternative .fw_menu_color.active,
		#fw_container.alternative .fw_menu_color:hover {
			color: <?php echo $framework_settings_globalstyles['menu_active'];?>;
			font-weight: bold;
		}
		#fw_container.alternative #fw_menu .fw_menu_icon_color,
		#fw_container.alternative .fw_menu_icon_color {
			color: <?php echo $framework_settings_globalstyles['menu_icons'];?>
		}
		#fw_container.alternative .fw_tab_color.active,
		#fw_container.alternative .active .fw_tab_color,
		#fw_container.alternative .fw_tab_color:hover {
			border-bottom: 3px solid <?php echo $framework_settings_globalstyles['filter_tabs_active'];?>;
		}
		#fw_container.alternative .fw_alert_icon {
			background: <?php echo $framework_settings_globalstyles['alert_icon'];?>;
			color: #fff;
		}
		#fw_container.alternative .fw_alert_icon_color {
			color: <?php echo $framework_settings_globalstyles['alert_icon'];?>;
		}
		#fw_container.alternative .fw_load_button_color,
		#fw_container.alternative .fw_load_button_color:hover {
			background: <?php echo $framework_settings_globalstyles['load_button'];?>;
			color: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['load_button'];?>;
		}
		body.alternative .popupeditbox .fw_popup_x_color {
			background-color: <?php echo $framework_settings_globalstyles['popup_x'];?>;
		}
		body.alternative .popupeditbox .fw_filter_color .arrowDown {
			border-top-color: <?php echo $framework_settings_globalstyles['filter'];?>;
			color: <?php echo $framework_settings_globalstyles['filter'];?>;
		}
		body.alternative .popupeditbox .fw_text_link_color,
		body.alternative .popupeditbox .fw_text_link_color:hover {
			color: <?php echo $framework_settings_globalstyles['text_links'];?>;
		}
		body.alternative .popupeditbox .fw_text_link_color:hover {
			font-weight: bold;
		}
		body.alternative .popupeditbox .fw_delete_edit_icon_color {
			color: <?php echo $framework_settings_globalstyles['delete_edit_icons'];?>;
		}
		body.alternative .popupeditbox .fw_button_color,
		body.alternative .popupeditbox.fw_button_color:hover {
			background: <?php echo $framework_settings_globalstyles['buttons'];?>;
			color: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['buttons'];?>;
		}
		body.alternative .popupeditbox .fw_button_not_filled_color,
		body.alternative .popupeditbox .fw_button_not_filled_color:hover {
			color: <?php echo $framework_settings_globalstyles['buttons'];?>;
			background: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['buttons'];?>;
		}
		body.alternative .popupeditbox .fw_icon_color {
			color: <?php echo $framework_settings_globalstyles['icons'];?>;
		}
		body.alternative .popupeditbox .fw_load_button_color,
		body.alternative .popupeditbox .fw_load_button_color:hover {
			background: <?php echo $framework_settings_globalstyles['load_button'];?>;
			color: #fff;
			border: 1px solid <?php echo $framework_settings_globalstyles['load_button'];?>;
		}


		body.alternative .fw_popup_x_color {
			background-color: <?php echo $framework_settings_globalstyles['popup_x'];?>;
		}
	</style>
<?php } ?>
<!-- Account list wrapper  -->
<div id="fw_account_list"></div>
<!-- // Account list wrapper  -->

<?php if($b_help_page_active) { ?>
<div id="fw-help-page-overlay"></div>
<a href="#" class="fw_getynet_help_button fw_link_box">
	<span class="title"><?php echo $formText_Help_blueline;?></span>
	<span class="glyphicon glyphicon-question-sign"></span>
</a>
<!-- Help page wrapper  -->
<div id="fw-help-page-popup">
	<div class="fw-hp-header">
		<span class="fw-hp-back-btn"></span>
		<span class="pull-right">
			<?php
			foreach($v_help_article_tabs as $l_key => $s_tab_tilte)
			{
				?><span class="fw-hp-article-btn fw-hp-tab idx<?php echo $l_key;?><?php echo $l_key == $l_help_article_tab ? ' active':'';?>" data-tab-id="<?php echo $l_key;?>"><?php echo $s_tab_tilte;?></span><?php
			}
			?>
			<span class="fw-hp-close-btn glyphicon glyphicon-remove"></span>
		</span>
		<span class="clearfix"></span>
	</div>
	<div class="fw-hp-content"><?php echo $fw_helppage_content;?></div>
</div>
<!-- // Help page wrapper  -->
<?php } ?>

<!--  JS -->
<script type="text/javascript" language="javascript">
<?php if($b_help_page_active) { ?>
$('.fw_getynet_help_button, .fw-hp-close-btn').off('click').on('click', function(e){
	e.preventDefault();

	$('#fw-help-page-popup').toggle();
	$('#fw-help-page-overlay').toggle();
});
$('#fw-help-page-overlay').off('click').on('click', function(e){
	e.preventDefault();

	$('#fw-help-page-popup').toggle();
	$('#fw-help-page-overlay').toggle();
});
function fw_help_page_binding()
{
	$('#fw-help-page-popup .fw-hp-article-btn').off('click').on('click', function(e){
		e.preventDefault();
		var param = {
			language_id: '<?php echo $variables->languageID;?>',
			tab_id: $(this).data('tab-id'),
		};
		if($(this).data('module')) param.module = $(this).data('module');
		if($(this).data('article-id')) param.article_id = $(this).data('article-id');
		$('#fw-help-page-popup .fw-hp-tab.active').removeClass('active');
		$('#fw-help-page-popup .fw-hp-tab.idx'+$(this).data('tab-id')).addClass('active');

		fw_loading_start();
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $variables->account_framework_url.'getynet_fw/blueline/ajax.get_help_content.php';?>',
			data: param,
			success: function(json){
				fw_loading_end();
				if(json.error !== undefined)
				{
					$('#fw-help-page-popup .fw-hp-content').html(json.error);
				} else {
					$('#fw-help-page-popup .fw-hp-content').html(json.html);
					fw_help_page_binding();
				}
			}
		}).fail(function(){
			fw_loading_end();
		});
	});
	$('#fw-help-page-popup .fw-hp-content img').off('click').on('click', function(e){
		$.fancybox({
			'transitionIn'	: 'none',
			'transitionOut'	: 'none',
			'href'			: $(this).prop('src'),
		});
	});
}
fw_help_page_binding();
<?php } ?>

<?php if($variables->fw_settings_basisconfig['user_alternative_design']) { ?>
//toogle logout dropdown
$("#fw_menu_options").unbind("click").click(function(){
	$(".fw_logout_menu_dropdown").slideToggle();
})
$("#fw_change_mode_wrapper").unbind("click").bind("click", function(){
	$(".fw_language_change").hide();
	$(".fw_mode_change").toggle();
})
$("#fw_change_language_wrapper").unbind("click").bind("click", function(){
	$(".fw_mode_change").hide();
	$(".fw_language_change").toggle();
})
<?php } ?>
// On account list button click
$('#fw_accounts_list_btn').on('click', function() {

	// Toggle class for Button
	$(this).toggleClass('opened');

	// Change hamburger menu icon to close icon (X)
	if($(this).find('.icon').hasClass('glyphicon-user')) {
		$(this).find('.icon').removeClass('glyphicon-user').addClass('icon-close');
	}
	else {
		$(this).find('.icon').removeClass('icon-close').addClass('glyphicon-user');
	}

	// Toggle account list block
	$('#fw_account_list').toggle(function() {

		// Reset all contents
		var self = $(this);
		self.html('');

		// Load content via AJAX (if visible after toggle)
		if(self.is(':visible')) {
			$.ajax({
				url: '<?php echo $variables->account_framework_url;?>getynet_fw/blueline/ajax_accountlist.php?languageID=<?php echo $variables->languageID;?>&random=' + Math.random()
			}).done(function(data) {
				self.html(data);

				var accountlist = new List('accountlist', {
					valueNames: ['name'],
					plugins: [ ListFuzzySearch() ]
				});
			})
		}
	});
});

// Arrows up and down
$(document).off('keydown').on('keydown', function(e) {

	if($('#fw_account_list').is(':visible')) {

		// Down arrow key
		if (e.keyCode == 40) {
			e.preventDefault();
			if($('#fw_account_list li a:focus').length==0) {
				$('#fw_account_list li').first().find('a').focus();
			}
			else {
				$("#fw_account_list li a:focus").parent().next().find('a').focus();
			}
		}
		// Up arrow key
		else if (e.keyCode == 38) {
			e.preventDefault();
			$("#fw_account_list li a:focus").parent().prev().find('a').focus();
		}

		// Enter
		else if (e.keyCode == 13) {
			// do nothing, just default
		}

		// Esc key
		else if (e.keyCode == 27) {
			$('#fw_account_list').hide();
			$('#fw_accounts_list_btn').find('.icon').removeClass('icon-close').addClass('glyphicon-user');
			$('#fw_accounts_list_btn').toggleClass('opened');

		}
		// Focus back to input field
		else {
			$("#fw_account_list input").focus();
		}
	}
});
var fw_toggle_account_list_click = false;
function fw_toggle_account_list()
{
	if(!fw_toggle_account_list_click)
	{
		fw_toggle_account_list_click = true;
		$('#fw_accounts_list_btn').trigger('click');
		setTimeout(function(){fw_toggle_account_list_click=false;},500);
	}
}
// Close account list when clicked outside container
$(document).off('mouseup').on('mouseup', function(e) {
	var container = $('#fw_account_list, #fw_accounts_list_btn');
	if($('#fw_account_list').is(':visible') && !container.is(e.target) && container.has(e.target).length === 0){
		$('#fw_accounts_list_btn').trigger('click');
	}
});
</script>

<!-- OnboardingPopup popup and actions -->
<?php if($fw_intro_force_read): ?>
	<script type="text/javascript">
		var onboardingPopup, onboardingPopupOptions={
			follow: [true, false],
			modalClose : false,
			onOpen: function(){
				$(this).addClass('opened');
				$(this).find('.b-close').unbind("click").on('click', function(){
					$("#onboarding_popup .readAllMessage").hide();
					if(!$(this).hasClass("forceReadAll")){
						onboardingPopup.close();
					} else {
						$("#onboarding_popup .readAllMessage").show();
					}
				});
			},
			onClose: function(){
				$(this).removeClass('opened');
			}
		};

		var showOnboardingPopup = function () {
			var ajaxData = {
				fwajax: 1,
				fw_nocss: 1,
				moduleName: 'fw_blueline',
				helpEntry: '<?php echo $variables->fw_settings_basisconfig['introArticle']; ?>'
			}
			// Show loader
			$('#fw_loading').show();
			// Ajax
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=OnboardingPopup&folderfile=output&folder=output&inc_obj=list&inc_act="; ?>',
				data: ajaxData,
				success: function(json){
					$('#fw_loading').hide();
					$('#onboarding_popupcontent').html('');
					helpcenterPopup = $('#onboarding_popup').bPopup(helpcenterPopupOptions);
					$('#onboarding_popupcontent').html(json.html);
					$("#onboarding_popup:not(.opened)").remove();
					$(window).resize();
				}
			});
		}

		showOnboardingPopup();
	</script>
    <div id="onboarding_popup">
        <span class="button b-close"><span>+</span></span>
        <div id="onboarding_popupcontent"></div>
    </div>
<?php endif; ?>

<!-- HelpCenter popup and actions -->
<?php if ($is_helpcenter_active): ?>
    <script type="text/javascript">
    /**
     * Help center
     */
    // Popup
    var helpcenterPopup, helpcenterPopupOptions={
    	follow: [true, false],
    	modalClose : false,
    	onOpen: function(){
    		$(this).addClass('opened');
    		$(this).find('.b-close').unbind("click").on('click', function(){
    			$("#helpcenter_popup .readAllMessage").hide();
    			if(!$(this).hasClass("forceReadAll")){
	    			helpcenterPopup.close();
	    		} else {
	    			$("#helpcenter_popup .readAllMessage").show();
	    		}
    		});
    	},
    	onClose: function(){
    		$(this).removeClass('opened');
    	}
    };
    // Helper function to get URL parameter
    var helpcenterGetUrlParameter = function (sParam) {
        var sPageURL = decodeURIComponent(window.location.search.substring(1)),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;
        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : sParameterName[1];
            }
        }
    };
    // Open helpcenter
    var helpcenterOpen = function (data) {
        // Default data
        var __data = {
            fwajax: 1,
            fw_nocss: 1
        }
        // data object check
        if (typeof(data) !== 'object') var data = {};
        // Concat default and user data
        var ajaxData = $.extend({}, __data, data);
        // Show loader
        $('#fw_loading').show();
        // Ajax
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'')."&module=OnboardingPopup&folderfile=output&folder=output&inc_obj="; ?>' + data.inc_obj + '&inc_act=' + data.inc_act,
            data: ajaxData,
            success: function(json){
                $('#fw_loading').hide();
                $('#helpcenter_popupcontent').html('');
                helpcenterPopup = $('#helpcenter_popup').bPopup(helpcenterPopupOptions);
                $('#helpcenter_popupcontent').html(json.html);
                $("#helpcenter_popup:not(.opened)").remove();
				$(window).resize();
            }
        });
    }
    // Event listener
    $(document).ready(function() {
        $('body').on('click', '.helpcenter_link', function(e) {
            e.preventDefault();
            // Check if link has any data params
            var moduleName = $(this).data('module-name') ? $(this).data('module-name') : helpcenterGetUrlParameter('module');
            var tab = $(this).data('tab') ? $(this).data('tab') : '';
            var helpEntry = $(this).data('help-entry') ? $(this).data('help-entry') : '';
            // Open helpcenter
            helpcenterOpen({
                moduleName: moduleName,
                tab: tab,
                helpEntry: helpEntry,
                inc_obj: 'list',
                inc_act: ''
            });
        });

    });
    </script>

    <div id="helpcenter_popup">
        <span class="button b-close"><span>+</span></span>
        <div id="helpcenter_popupcontent"></div>
    </div>
<?php endif; ?>
