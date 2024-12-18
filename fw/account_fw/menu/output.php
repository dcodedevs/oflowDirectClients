<?php
$include_file = __DIR__."/../languages/default.php";
if(is_file($include_file)) include($include_file);
$include_file = __DIR__."/../languages/".$variables->languageID.".php";
if(is_file($include_file)) include($include_file);

if($variables->loggID != '')
{
	if(isset($_GET['updatepath']))
	{
		$variables->fw_session['urlpath'] = urlencode(str_replace('fwajax=1&','',$_SERVER['QUERY_STRING']));
		$variables->fw_session['returl'] = urlencode(updateUrlQuery(fwCurrentPageUrl(),array(),array('fwajax','_')));
		$v_param = array($variables->fw_session['urlpath'], $variables->fw_session['returl'], (!$variables->fw_url_share?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']), $_COOKIE['sessionID'], $_COOKIE['username']);
		$o_main->db->query('UPDATE session_framework SET urlpath = ?, returl = ? WHERE companyaccessID = ? AND session = ? AND username = ?', $v_param);
	}

	$getcode = explode("&",urldecode($variables->fw_session['urlpath']));
	for($x=0;$x<count($getcode);$x++)
	{
		$v_tmp = explode("=",$getcode[$x]);
		if(!empty($v_tmp[0]))
		{
			$name = $v_tmp[0];
			$value = (isset($v_tmp[1]) ? $v_tmp[1] : '');
			${$name} = str_replace("%2F","/",$value);
		}
	}

	foreach($_GET as $name=>$value)
	{
		${$name} = str_replace("%2F","/",$value);
	}
	$pageID = 35;

	if(isset($variables->fw_settings_accountconfig['activate_module_hiding_script']) && is_file(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']))
	{
		include(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']);
	}

	$l_x = 0;
	$b_expand_admin_module = false;
	$v_modules = $v_admin_modules = $v_modules_print = $v_module_show_after = $v_fw_menu_extension = array();
	$o_query = $o_main->db->query("SELECT main.name main_name, next.name next_name FROM moduledata_accountconfig mc JOIN moduledata main ON main.uniqueID = mc.show_after_module JOIN moduledata next ON next.uniqueID = mc.id WHERE mc.show_after_module > 0");
	if($o_query)
	{
		foreach($o_query->result_array() as $v_row)
		{
			$v_module_show_after[$v_row['main_name']] = $v_row['next_name'];
		}
	}
	$v_module_skip = $v_module_show_after;

	$o_query = $o_main->db->query("SELECT * FROM moduledata ORDER BY modulemode, ordernr");
	if($o_query)
	{
		foreach($o_query->result_array() as $v_row)
		{

			$o_query2 = $o_main->db->query("SELECT * FROM moduledata_accountconfig WHERE id = ?", array($v_row['uniqueID']));
			if($o_query2)
			{
				$o_config = $o_query2->row();
				if(isset($o_config->deactivated)) $v_row['deactivated'] = $o_config->deactivated;
			}
			$s_module = $v_row['name'];
			$l_module_id = $v_row['uniqueID'];
			$v_data = array
			(
				'module'=> $s_module,
				'name'=> ($variables->developeraccess < 10 ? $variables->menu_access[$s_module][0] : $s_module),
				'url'=> $variables->menu_access[$s_module][1],
				'url_input'=> "module=".$s_module."&folder=input&folderfile=input&updatepath=1".($v_row['externalurl'] != "" ? "&external=".$v_row['externalurl'] : ""),
				'class'=> ((isset($_GET['module']) && $_GET['module'] == $s_module) ? ' active' : ''),
				'deactivated'=>$v_row['deactivated'],
			);
			if($v_row['modulemode']=='A')
			{
				if($v_row['deactivated'] == 1) continue;
				$v_admin_modules[$s_module] = $v_data;
				if($_GET['module']==$s_module) $b_expand_admin_module = true;
			} else {
				$v_modules[$s_module] = $v_data;
			}

			$s_virtual_module = '';
			if(isset($v_row['virtual_module_source']) && '' != trim($v_row['virtual_module_source']))
			{
				$s_virtual_module = trim($v_row['virtual_module_source']);
			}
			$s_file = BASEPATH.'modules/'.(''!=$s_virtual_module?$s_virtual_module:$s_module).'/output_fw_menu_extension/load_config.php';
			if(is_file($s_file)) include($s_file);
		}
	}

	$v_modules_free = array_keys($v_modules);
	$o_query = $o_main->db->query('SELECT s.* FROM sys_modulemenuset s JOIN sys_modulemenuusers u ON u.set_id = s.id WHERE u.username = ?', array($username));
	if(!$o_query || ($o_query && $o_query->num_rows() == 0))
	{
		$o_query = $o_main->db->query('SELECT * FROM sys_modulemenuset WHERE default_set = 1');
	}
	$v_set = array();
	if($o_query && 0 < $o_query->num_rows())
	{
		$v_set = $o_query->row_array();
		$o_query = $o_main->db->query('SELECT mmg.*, mmgc.name AS name_use FROM sys_modulemenugroup mmg LEFT OUTER JOIN sys_modulemenugroupcontent mmgc ON mmgc.sys_modulemenugroupID = mmg.id AND mmgc.languageID = ? WHERE mmg.set_id = ? ORDER BY mmg.id', array($variables->languageID, $v_set['id']));
		if($o_query && $o_query->num_rows() == 0)
		{
			$o_query = $o_main->db->query('SELECT mmg.*, mmgc.name AS name_use FROM sys_modulemenugroup mmg LEFT OUTER JOIN sys_modulemenugroupcontent mmgc ON mmgc.sys_modulemenugroupID = mmg.id AND mmgc.languageID = (SELECT l.languageID FROM language l ORDER BY l.defaultInputlanguage DESC LIMIT 1) WHERE mmg.set_id = ? ORDER BY mmg.id', array($v_set['id']));
		}
		if($o_query && $o_query->num_rows()>0)
		foreach($o_query->result_array() as $v_row)
		{
			$l_i = 0;
			$s_class = "";
			$v_childs = array();
			$o_query2 = $o_main->db->query('SELECT * FROM sys_modulemenulink WHERE group_id = ? ORDER BY id', array($v_row['id']));
			if($o_query2)
			foreach($o_query2->result_array() as $v_row2)
			{
				$s_module = $v_row2['module_name'];
				if(!array_key_exists($s_module, $variables->menu_access) || in_array($s_module, $v_module_skip)) continue;
				$b_show_module = false;
				$b_show_module_deactivated = true;
				$s_module_lc = strtolower($s_module);
				if(
					($variables->menu_access[$s_module][3] == 'D' &&
						(
							(isset($developeraccessmodules[$s_module_lc]) && $developeraccessmodules[$s_module_lc]<=$variables->developeraccess) ||
							(!isset($developeraccessmodules[$s_module_lc]) && $variables->developeraccess >= 20)
						)
					) // Developer
					||
					($variables->menu_access[$s_module][3] == 'I' && $variables->developeraccess >= 10) // Designer
					||
					($variables->menu_access[$s_module][3] == 'E' && $variables->developeraccess >= 5) // Extra
					||
					($variables->menu_access[$s_module][3] == 'S' && ($variables->system_admin == 1 || $variables->developeraccess >= 20)) // Systemadmin
					||
					($variables->menu_access[$s_module][3] == 'A' && $variables->useradmin == 1) // Admin
				)
				{
					$b_show_module = true;
				}
				if($v_modules[$s_module]['deactivated'] == 1 && $variables->developeraccess < 20) $b_show_module_deactivated = false;

				if(($variables->menu_access[$s_module][2] > 0 && $variables->menu_access[$s_module][3] == 'C' && $b_show_module_deactivated) || $b_show_module)
				{
					$l_id = array_search($s_module, $v_modules_free);
					if($l_id !== false) unset($v_modules_free[$l_id]);
					$v_childs[$l_i] = $s_module;
					if(strpos($v_modules[$s_module]["class"], 'active') !== FALSE) $s_class = " in";
					if($v_row['name_use']=="")
					{
						$v_modules_print[$l_x] = $s_module;
						unset($v_childs[$l_i]);
						$l_x++;
						if(isset($v_module_show_after[$s_module]) && strpos($v_module_show_after[$s_module], ':') === false)
						{
							$l_id = array_search($v_module_show_after[$s_module], $v_modules_free);
							if($l_id !== false) unset($v_modules_free[$l_id]);
							$v_modules_print[$l_x] = $v_module_show_after[$s_module];
							$v_module_show_after[$s_module] .= ':'.$l_x;
							$l_x++;
						}
					} else {
						$l_i++;
						if(isset($v_module_show_after[$s_module]) && strpos($v_module_show_after[$s_module], ':') === false)
						{
							$l_id = array_search($v_module_show_after[$s_module], $v_modules_free);
							if($l_id !== false) unset($v_modules_free[$l_id]);
							$v_childs[$l_i] = $v_module_show_after[$s_module];
							$v_module_show_after[$s_module] .= ':'.$l_x.':'.$l_i;
							$l_i++;
						}
					}
				}
			}
			if($l_i>0)
			{
				$v_modules_print[$l_x] = array();
				$v_modules_print[$l_x]["class"] = $s_class;
				if($v_row['collapse'] != 1) $v_modules_print[$l_x]["class"] = " in";
				$v_modules_print[$l_x]['name'] = $v_row['name_use'];
				$v_modules_print[$l_x]["childs"] = $v_childs;
				$l_x++;
			}
		}
	}

	if(!isset($v_set['id']) || $v_set['hide_modules_not_in_set'] != 1 || $variables->developeraccess >= 20)
	{
		foreach($v_modules_free as $s_module)
		{
			if(!array_key_exists($s_module, $variables->menu_access) || in_array($s_module, $v_module_skip)) continue;
			$b_show_module = false;
			$b_show_module_deactivated = true;
			$s_module_lc = strtolower($s_module);
			if(
				($variables->menu_access[$s_module][3] == 'D' &&
					(
						(isset($developeraccessmodules[$s_module_lc]) && $developeraccessmodules[$s_module_lc]<=$variables->developeraccess) ||
						(!isset($developeraccessmodules[$s_module_lc]) && $variables->developeraccess >= 20)
					)
				)
				||
				($variables->menu_access[$s_module][3] == 'S' && ($variables->system_admin == 1 || $variables->developeraccess >= 20))
			)
			{
				$b_show_module = true;
			}
			if($v_modules[$s_module]['deactivated'] == 1 && $variables->developeraccess < 20) $b_show_module_deactivated = false;

			if(($variables->menu_access[$s_module][2] > 0 && $variables->menu_access[$s_module][3] == 'C' && $b_show_module_deactivated) || $b_show_module)
			{
				$v_modules_print[$l_x] = $s_module;
				$l_x++;
				if(isset($v_module_show_after[$s_module]) && strpos($v_module_show_after[$s_module], ':') === false)
				{
					$l_id = array_search($v_module_show_after[$s_module], $v_modules_free);
					if($l_id !== false) unset($v_modules_free[$l_id]);
					$v_modules_print[$l_x] = $v_module_show_after[$s_module];
					$v_module_show_after[$s_module] .= ':'.$l_x;
					$l_x++;
				}
			}
		}
	}
	foreach($v_module_show_after as $s_item)
	{
		list($s_module, $l_xx, $l_i) = explode(':', $s_item);
		if(!array_key_exists($s_module, $variables->menu_access)) continue;
		$b_show_module = false;
		$b_show_module_deactivated = true;
		$s_module_lc = strtolower($s_module);
		if(
			($variables->menu_access[$s_module][3] == 'D' &&
				(
					(isset($developeraccessmodules[$s_module_lc]) && $developeraccessmodules[$s_module_lc]<=$variables->developeraccess) ||
					(!isset($developeraccessmodules[$s_module_lc]) && $variables->developeraccess >= 20)
				)
			)
			||
			($variables->menu_access[$s_module][3] == 'S' && ($variables->system_admin == 1 || $variables->developeraccess >= 20))
		)
		{
			$b_show_module = true;
		}
		if($v_modules[$s_module]['deactivated'] == 1 && $variables->developeraccess < 20) $b_show_module_deactivated = false;

		if(($variables->menu_access[$s_module][2] > 0 && $variables->menu_access[$s_module][3] == 'C' && $b_show_module_deactivated) || $b_show_module)
		{
			if(empty($l_xx))
			{
				if(isset($v_set['id']) && $v_set['hide_modules_not_in_set'] == 1 && $variables->developeraccess < 20) continue;
				$l_xx = $l_x;
				$l_x++;
			}
			if(!empty($l_i))
			{
				$v_modules_print[$l_xx][$l_ii] = $s_module;
			} else {
				$v_modules_print[$l_xx] = $s_module;
			}
		}
	}
	?>
	<div class="fw_menu_info">
		<?php if($variables->fw_settings_basisconfig && $variables->fw_settings_basisconfig['user_alternative_design']){ ?>
			<?php if($fwShowBrandSecondLogo){
				//check if People module exists and user is registered
				$sql = "SELECT m.* FROM moduledata m WHERE m.name = 'Frontpage' AND m.uniqueID = 57";
				$o_query = $o_main->db->query($sql);
				$peopleModule = $o_query ? $o_query->row_array() : array();
				$frontpageModuleLink = "";
				if($peopleModule){
					$frontpageModuleLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=Frontpage&folderfile=output&folder=output";
				}
				?>
				<!--  Logo -->
				<div class="fw_brand_second_logo">
					<?php if($frontpageModuleLink != "") {?>
						<a href="<?php echo $frontpageModuleLink?>">
					<?php } ?>
						<img src="<?php echo $fwBrandSecondLogoImage;?>" class="desktop" alt="" />
					<?php if($frontpageModuleLink != "") {?>
						</a>
					<?php } ?>
					<?php /*?><img src="<?php echo $fwBrandSecondLogoImage;?>" class="mobile" alt="" /><?php */?>
				</div>
				<!-- // Logo -->
			<?php } else { ?>
				<div class="fw_account_info">
					<div class="fw_logo"></div>
					<div class="fw_text">
						<div class="fw_company_name"><?php echo $variables->companyname;?></div>
						<div class="fw_account_name"><?php echo $variables->accountnamefriendly; ?></div>
					</div>
				</div>
			<?php } ?>
		<?php } else { ?>
			<div class="fw_account_info">
				<div class="fw_logo"></div>
				<div class="fw_text">
					<div class="fw_company_name"><?php echo $variables->companyname;?></div>
					<div class="fw_account_name"><?php echo $variables->accountnamefriendly; ?></div>
				</div>
			</div>
		<?php } ?>
		<!--  Group switch (button) -->
		<?php if ($fw_activate_groups): ?>
			<div class="fw_change_group">
				<div class="fw_change_group_selected_name">
					<?php echo $fw_selected_group_name; ?>
				</div>
				<div class="fw_change_group_btn_container">
					<a id="fw_change_group_btn" class="fw_change_group_btn">
						<?php echo $changeGroupButtonText ? $changeGroupButtonText : 'Change group'; ?>
					</a>
				</div>
			</div>
		<?php endif; ?>

		<?php if(!$variables->fw_settings_basisconfig || !$variables->fw_settings_basisconfig['user_alternative_design']){ ?>
			<?php
			if($variables->fw_session['developeraccessoriginal']>0)
			{
				$link_parameters = "pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&";
				foreach($variables->menu_access as $item)
				{
					if($item[3]!='A')
					{
						$link_parameters .= $item[1];
						break;
					}
				}
				?>
				<div class="fw_mode_text"><?php echo $formText_DeveloperAccess_users;?></div>
				<div class="fw_mode_change btn-group btn-group-justified">
					<?php
					foreach($developeraccesslevels as $key => $item)
					{
						if($key > $variables->fw_session['developeraccessoriginal']) break;
						?><div class="btn-group" role="group">
							<button type="button" role="group" class="btn btn-xs<?php echo ($variables->developeraccess==$key?' active':'');?>" data-value="<?php echo $key;?>" title="<?php echo $item;?>" onClick="javascript: fw_loading_start(); $('#fw_change_mode_form input').val($(this).data('value')); $('#fw_change_mode_form').submit();"><?php echo $item;?></button>
						</div><?php
					}
					?>
				</div>
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
					$s_choosen = '';
					$v_items = $o_find->result_array();
					foreach($v_items as $v_item)
					{
						if($variables->fw_session['accountlanguageID'] == $v_item['languageID']) $s_choosen = $v_item['name'];
					}
					?>
					<div class="fw_language_text">Language<?php echo $formText_Language_Framework;?></div>
					<div class="btn-group btn-group-justified" role="group">
						<div class="btn-group fw_language_change">
							<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<?php echo $s_choosen;?> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<?php
								foreach($v_items as $v_item)
								{
									?><li><a href="#" class="script" data-value="<?php echo $v_item['languageID'];?>" onClick="javascript: fw_loading_start(); $('#fw_change_language_form input').val($(this).data('value')); $('#fw_change_language_form').submit();"><?php echo $v_item['name'];?></a></li><?php
								}
								?>
							</ul>
						</div>
					</div>
					<form id="fw_change_language_form" method="post" action="<?php echo $_SERVER['PHP_SELF'].'?'.$link_parameters;?>">
						<input type="hidden" name="fw_set_account_language" value="">
					</form>
					<?php
				}
			}
			?>
		<?php } ?>
	</div>
	<?php
	if($variables->useradmin == 1)
	{
		if(isset($_GET['module']) && $_GET['module'] == 37) $b_expand_admin_module = true;
		ob_start();

		if(isset($variables->accountinfo['deactivate_useradministration']) && 0 < $variables->accountinfo['deactivate_useradministration']) {
			$variables->accountinfo_basisconfig['deactivate_useradministration'] = $variables->accountinfo['deactivate_useradministration'] - 1;
		}
		if(!isset($variables->accountinfo_basisconfig['deactivate_useradministration']) || !$variables->accountinfo_basisconfig['deactivate_useradministration'] || $variables->developeraccess >= 10) {
			?>
			<div class="fw_menu_item fw_menu_color adm activate mod_UserManager<?php if(isset($_GET['module']) && $_GET['module'] == 37) echo " active";?>" data-group="fw_menu_item">
				<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&getynetaccount=1&module=37&folderfile=output&folder=output&modulename=users&usermodule=1&updatepath=1";?>" role="button"><span class="fw-icon-secure"></span> <?php echo $formText_adminUserinfo_getynetmenu;?></a>
			</div>
			<?php
		}
		if(isset($variables->fw_settings_accountconfig['activate_account_settings']) && $variables->fw_settings_accountconfig['activate_account_settings']) {
			$variables->fw_settings_basisconfig['activate_account_settings'] = $variables->fw_settings_accountconfig['activate_account_settings'] - 1;
			$variables->fw_settings_basisconfig['account_settings_modulename'] = $variables->fw_settings_accountconfig['account_settings_modulename'];
		}

		if(isset($variables->fw_settings_basisconfig['activate_account_settings']) && $variables->fw_settings_basisconfig['activate_account_settings'] && $variables->fw_settings_basisconfig['account_settings_modulename'] != "" && $variables->useradmin) {
			$s_sql = "SELECT * FROM moduledata WHERE name = ?";
			$o_query = $o_main->db->query($s_sql, array($variables->fw_settings_basisconfig['account_settings_modulename']));
			$account_settings_module = $o_query ? $o_query->row_array() : array();
			if($account_settings_module){
			?>
				<div class="fw_menu_item fw_menu_color <?php if($_GET['module']==37) echo " active";?>" data-group="fw_menu_item">
					<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$account_settings_module['name']."&moduleID=".$account_settings_module['id']."&modulename=".$account_settings_module['name']."&folder=output&folderfile=output";?>" role="button"> <?php echo $formText_AccountSettings_getynetmenu;?></a>
				</div>
				<?php
			}
		}
		foreach($v_admin_modules as $v_module)
		{
			?><div class="fw_menu_item fw_menu_color adm activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module["module"]).$v_module["class"];?>" data-group="fw_menu_item">
				<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"]; ?>"><span class="fw-icon-secure"></span> <?php echo $v_module['name'];?></a>
				<?php if($variables->developeraccess >= 10 && strpos($v_module["url"],'folderfile=input') === false) { ?>
				<div class="pull-right">
					<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];?>">
						<span class="glyphicon glyphicon-edit"></span>
					</a>
				</div>
				<?php } ?>
			</div><?php
		}
		$fw_ob_menu = ob_get_clean();
		if(count($v_admin_modules)>0)
		{
			?><div class="fw_menu_item fw_menu_color adm" data-group="fw_menu_item">
				<a id="fw_menu_trigger_adm" class="script" href="#fw_menu_collapse_adm" data-toggle="collapse" onClick="$(this).find('span').toggleClass('glyphicon-chevron-right').toggleClass('glyphicon-chevron-down');"><?php echo $formText_AdminModules_FwMenu." (".(sizeof($v_admin_modules)+1).")";?> <span class="glyphicon glyphicon-chevron-<?php echo ($b_expand_admin_module ? "down" : "right");?>"></span></a>
			</div>
			<div id="fw_menu_collapse_adm" class="sub-items collapse<?php if($b_expand_admin_module) echo " in";?>" data-trigger="#fw_menu_trigger_adm"><?php echo $fw_ob_menu;?></div><?php
		} else {
			echo $fw_ob_menu;
		}
	}
	?>
	<?php if(isset($variables->fw_settings_basisconfig['user_alternative_design']) && $variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		<div class="fw_account_profile">
			<span class="fw_profile_image_crop">
				<?php if(is_array($profileimage)){ ?>
					<img src="https://pics.getynet.com/profileimages/<?php echo $profileimage[1];?>" alt="" />
				<?php } ?>
			</span>
			<span class="fw_profile_name">
				<?php echo $variables->fw_session['fullname']; ?><br/>
				<a href="<?php if($fw_people_added) { echo $fw_people_module_link; } else { echo $link;}?>" class="fw_profile fw_link_box"><?php echo $formText_ShowProfile_AccountFrameworkMenu;?></a>
			</span>
			<?php
			if($v_accountinfo['activate_crm_user_content_filtering_tags'])
			{
				?>
				<div class="sharingToTags">
					<span class="fas fa-info-circle"></span>
					<div class="sharingToTagsHover">
						<?php
						if($variables->useradmin){
							echo $formText_YouAreAdminAndCanReadAndWriteToAllTagsAndGroups_framework;
						} else {
							$tagsToRead = $tagsToWrite = $groupsToRead = $groupsToWrite = array();
							if($variables->fw_session['membership_tags'])
							{
								$v_membership_tags = json_decode($variables->fw_session['membership_tags'], TRUE);
								$tagsToRead = $v_membership_tags['tags_read'];
								$groupsToRead = $v_membership_tags['groups_read'];
								$tagsToWrite = $v_membership_tags['tags_write'];
								$groupsToWrite = $v_membership_tags['groups_write'];
							}
							?>
							<div style="font-weight: bold;">
								<?php echo $formText_GroupsWrite_framework;?>
							</div>
							<?php if(count($groupsToWrite) > 0) { ?>
								<?php
								foreach($groupsToWrite as $groupToShowTo){
									?>
									<div><?php echo $groupToShowTo['name'];?></div>
									<?php
								} ?>
							<?php } ?>
							<div style="font-weight: bold;">
								<?php echo $formText_TagsWrite_framework;?>
							</div>
							<?php if(count($tagsToWrite) > 0) { ?>
								<?php
								foreach($tagsToWrite as $tagToShowTo){
									?>
									<div><?php echo $tagToShowTo['name'];?></div>
									<?php
								} ?>
							<?php } ?>

							<div style="font-weight: bold;">
								<?php echo $formText_GroupsRead_framework;?>
							</div>
							<?php if(count($groupsToRead) > 0) { ?>
								<?php
								foreach($groupsToRead as $groupToShowTo){
									?>
									<div><?php echo $groupToShowTo['name'];?></div>
									<?php
								} ?>
							<?php } ?>
							<div style="font-weight: bold;">
								<?php echo $formText_TagsRead_framework;?>
							</div>
							<?php if(count($tagsToRead) > 0) { ?>
								<?php
								foreach($tagsToRead as $tagToShowTo){
									?>
									<div><?php echo $tagToShowTo['name'];?></div>
									<?php
								} ?>
							<?php } ?>
						<?php } ?>
					</div>
					<?php ?>
				</div>
				<?php
			}
			?>
		</div>
	<?php } ?>
	<div class="fw_account_menu">
	<?php
	$v_icons = array(""=>"triangle-right", " in"=>"triangle-bottom");
	foreach($v_modules_print as $l_key => $v_item)
	{
		if(isset($v_item["childs"]))
		{
			?><div class="fw_menu_item activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_item['module']);?>" data-group="fw_menu_item">
				<a id="fw_menu_trigger_<?php echo $l_key;?>" class="script" href="#fw_menu_collapse_<?php echo $l_key;?>" data-toggle="collapse" onClick="$(this).find('span').toggleClass('glyphicon-triangle-right').toggleClass('glyphicon-triangle-bottom');"><span class="glyphicon glyphicon-<?php echo $v_icons[$v_item["class"]];?>"></span> <?php echo $v_item['name'];?></a>
			</div>
			<div id="fw_menu_collapse_<?php echo $l_key;?>" class="sub-items collapse<?php echo $v_item['class'];?>" data-trigger="#fw_menu_trigger_<?php echo $l_key;?>"><?php
			foreach($v_item["childs"] as $s_module)
			{
				$v_module = $v_modules[$s_module];
				$s_attributes = '';
				$s_optimize = ' optimize';
				if(isset($v_module['target']) && '' != $v_module['target'])
				{
					$s_optimize = '';
					$s_attributes = ' target="'.$v_module['target'].'"';
				}
				$s_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"];
				if(isset($v_module["url_replace"]) && '' != $v_module["url_replace"])
				{
					$s_url = $v_module["url_replace"];
				}
				?><div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module["module"]).$v_module["class"];?>" data-group="fw_menu_item">
					<a class="abort_bg<?php echo $s_optimize.($variables->menu_access[$v_module["module"]][3] == 'D' ? ' dev':'');?>" href="<?php echo $s_url; ?>"<?php echo $s_attributes; ?>><?php echo $v_module['name'];?></a>
					<?php if($variables->developeraccess >= 10 && strpos($v_module["url"],'folderfile=input') === false) { ?>
					<div class="pull-right">
						<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];?>">
							<span class="glyphicon glyphicon-edit"></span>
						</a>
					</div>
					<?php } ?>
				</div><?php
			}
			?></div><?php
		} else if(isset($v_fw_menu_extension[$v_item]))
		{
			$v_module = $v_modules[$v_item];
			$s_module = $v_item;
			$v_item = $v_fw_menu_extension[$s_module];
			if(/*1==1 || */0 < count($v_item['childs']))
			{
				?><div class="fw_menu_item activate <?php echo 'mod_'/*.preg_replace('/\s+/', '', $v_item['module'])*/;?>" data-group="fw_menu_item">
					<a id="fw_menu_trigger_<?php echo $l_key;?>" class="script" href="#fw_menu_collapse_<?php echo $l_key;?>" data-toggle="collapse" onClick="$(this).find('span').toggleClass('glyphicon-triangle-right').toggleClass('glyphicon-triangle-bottom');"><span class="glyphicon glyphicon-<?php echo $v_icons[$v_item['class']];?>"></span> <?php echo $v_module['name'];?></a>
				</div>
				<div id="fw_menu_collapse_<?php echo $l_key;?>" class="sub-items collapse<?php echo $v_item['class'];?>" data-trigger="#fw_menu_trigger_<?php echo $l_key;?>"><?php
				foreach($v_item['childs'] as $l_key_ext => $v_ext)
				{
					?><div class="fw_menu_item activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module['module']).(!empty($v_ext['id_ext'])?'_ext'.$v_ext['id_ext']:'').((isset($v_ext['childs']) && 0 < count($v_ext['childs']))?' colex':' fw_menu_color'.($v_ext['class'] == ' in' ? ' active' : ''));?>"
						 data-group="fw_menu_item">
						<?php if(isset($v_ext['childs']) && 0 < count($v_ext['childs'])) { ?>
							<a id="fw_menu_trigger_<?php echo $l_key.'_'.$l_key_ext;?>" class="script" href="#fw_menu_collapse_<?php echo $l_key.'_'.$l_key_ext;?>" data-toggle="collapse" onClick="$(this).find('span').toggleClass('glyphicon-triangle-right').toggleClass('glyphicon-triangle-bottom');"><span class="glyphicon glyphicon-<?php echo $v_icons[$v_ext['class']];?>"></span> <?php echo $v_ext['name'];?></a>
						<?php } else { ?>
							<a class="optimize abort_bg<?php echo ($variables->menu_access[$v_module['module']][3] == 'D' ? ' dev':'');?>" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url'].$v_ext['url_ext']; ?>"><?php echo $v_ext['name'];?></a>
							<?php if($variables->developeraccess >= 10 && strpos($v_module['url'],'folderfile=input') === false) { ?>
							<div class="pull-right">
								<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url_input'];?>">
									<span class="glyphicon glyphicon-edit"></span>
								</a>
							</div>
							<?php } ?>
						<?php } ?>
					</div><?php
					if(isset($v_ext['childs']) && 0 < count($v_ext['childs']))
					{
						?><div id="fw_menu_collapse_<?php echo $l_key.'_'.$l_key_ext;?>" class="sub-sub-items collapse<?php echo $v_ext['class'];?>" data-trigger="#fw_menu_trigger_<?php echo $l_key.'_'.$l_key_ext;?>"><?php
						foreach($v_ext['childs'] as $l_key_ext2 => $v_ext2)
						{
							?><div class="fw_menu_item activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module['module']).(!empty($v_ext2['id_ext'])?'_ext'.$v_ext2['id_ext']:'').((isset($v_ext2['childs']) && 0 < count($v_ext2['childs']))?' colex':' fw_menu_color'.($v_ext2['class'] == ' in' ? ' active' : '')).$v_ext2['class'];?>" data-group="fw_menu_item">
								<?php if(isset($v_ext2['childs']) && 0 < count($v_ext2['childs'])) { ?>
									<a id="fw_menu_trigger_<?php echo $l_key.'_'.$l_key_ext.'_'.$l_key_ext2;?>" class="script" href="#fw_menu_collapse_<?php echo $l_key.'_'.$l_key_ext.'_'.$l_key_ext2;?>" data-toggle="collapse" onClick="$(this).find('span').toggleClass('glyphicon-triangle-right').toggleClass('glyphicon-triangle-bottom');"><span class="glyphicon glyphicon-<?php echo $v_icons[$v_ext['class']];?>"></span> <?php echo $v_ext2['name'];?></a>
								<?php } else { ?>
									<a class="optimize abort_bg<?php echo ($variables->menu_access[$v_module['module']][3] == 'D' ? ' dev':'');?>" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url'].$v_ext2['url_ext']; ?>"><?php echo $v_ext2['name'];?></a>
									<?php if($variables->developeraccess >= 10 && strpos($v_module['url'],'folderfile=input') === false) { ?>
									<div class="pull-right">
										<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url_input'];?>">
											<span class="glyphicon glyphicon-edit"></span>
										</a>
									</div>
									<?php } ?>
								<?php } ?>
							</div><?php
							if(isset($v_ext2['childs']) && 0 < count($v_ext2['childs']))
							{
								?><div id="fw_menu_collapse_<?php echo $l_key.'_'.$l_key_ext.'_'.$l_key_ext2;?>" class="sub-sub-sub-items collapse<?php echo $v_ext2['class'];?>" data-trigger="#fw_menu_trigger_<?php echo $l_key.'_'.$l_key_ext.'_'.$l_key_ext2;?>"><?php
								foreach($v_ext2['childs'] as $v_ext3)
								{
									?><div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module['module']).(!empty($v_ext3['id_ext'])?'_ext'.$v_ext3['id_ext']:'').($v_ext3['class'] == ' in' ? ' active' : '');?>" data-group="fw_menu_item">
										<a class="optimize abort_bg<?php echo ($variables->menu_access[$v_module['module']][3] == 'D' ? ' dev':'');?>" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url'].$v_ext3['url_ext']; ?>"><?php echo $v_ext3['name'];?></a>
										<?php if($variables->developeraccess >= 10 && strpos($v_module['url'],'folderfile=input') === false) { ?>
										<div class="pull-right">
											<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF'].'?pageID='.$_GET['pageID'].'&accountname='.$_GET['accountname'].'&companyID='.$_GET['companyID'].(!$variables->fw_url_share?'&caID='.$_GET['caID']:'').'&'.$v_module['url_input'];?>">
												<span class="glyphicon glyphicon-edit"></span>
											</a>
										</div>
										<?php } ?>
									</div><?php
								}
								?></div><?php
							}
						}
						?></div><?php
					}
				}
				?></div><?php
			} else {
				$s_attributes = '';
				$s_optimize = ' optimize';
				if(isset($v_module['target']) && '' != $v_module['target'])
				{
					$s_optimize = '';
					$s_attributes = ' target="'.$v_module['target'].'"';
				}
				$s_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"].$v_item['url_ext'];
				if(isset($v_module["url_replace"]) && '' != $v_module["url_replace"])
				{
					$s_url = $v_module["url_replace"];
				}
				?><div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module["module"]).$v_module["class"];?>" data-group="fw_menu_item">
					<a class="abort_bg<?php echo $s_optimize.($variables->menu_access[$v_module["module"]][3] == 'D' ? ' dev':'');?>" href="<?php echo $s_url; ?>"<?php echo $s_attributes;?>><?php echo $v_module['name'];?></a>
					<?php if($variables->developeraccess >= 10 && strpos($v_module["url"],'folderfile=input') === false) { ?>
					<div class="pull-right">
						<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];?>">
							<span class="glyphicon glyphicon-edit"></span>
						</a>
					</div>
					<?php } ?>
				</div><?php
			}
		} else {
			$v_module = $v_modules[$v_item];
			$s_attributes = '';
			$s_optimize = ' optimize';
			if(isset($v_module['target']) && '' != $v_module['target'])
			{
				$s_optimize = '';
				$s_attributes = ' target="'.$v_module['target'].'"';
			}
			$s_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"];
			if(isset($v_module["url_replace"]) && '' != $v_module["url_replace"])
			{
				$s_url = $v_module["url_replace"];
			}
			?><div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_module["module"]).$v_module["class"];?>" data-group="fw_menu_item">
				<a class="abort_bg<?php echo $s_optimize.($variables->menu_access[$v_module["module"]][3] == 'D' ? ' dev':'');?>" href="<?php echo $s_url; ?>"<?php echo $s_attributes;?>><?php echo $v_module['name'];?></a>
				<?php if($variables->developeraccess >= 10 && strpos($v_module["url"],'folderfile=input') === false) { ?>
				<div class="pull-right">
					<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];?>">
						<span class="glyphicon glyphicon-edit"></span>
					</a>
				</div>
				<?php } ?>
			</div><?php
		}
	}

	if(isset($variables->accountinfo['activate_statistic']) && $variables->accountinfo['activate_statistic'] == 1)
	{
		?><div class="fw_menu_item"><a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/awstats/awstats.pl?config=<?php echo $variables->accountinfo['domain'];?>" target="_blank"><?php echo $formText_Statistic_AccountFrameworkMenu;?></a></div><?php
	}

	ob_start();

	$people_contactperson_type = 2;
	if(isset($variables->accountinfo_basisconfig['contactperson_type_to_use_in_people']) && 0 < $variables->accountinfo_basisconfig['contactperson_type_to_use_in_people']){
		$people_contactperson_type = intval($variables->accountinfo_basisconfig['contactperson_type_to_use_in_people']);
	}
	$v_user_groups = array();
	$hideGroupsFromMenu = isset($variables->fw_settings_basisconfig['hideGroupsFromMenu']) ? $variables->fw_settings_basisconfig['hideGroupsFromMenu'] : FALSE;
	if(isset($variables->fw_settings_accountconfig['hideGroupsFromMenu']) && $variables->fw_settings_accountconfig['hideGroupsFromMenu'] > 0){
		$hideGroupsFromMenu = $variables->fw_settings_accountconfig['hideGroupsFromMenu'] - 1;
	}
	if(!$hideGroupsFromMenu){
		$v_user_groups = array();
		if($o_main->db->table_exists('contactperson_group'))
		{
			$o_query = $o_main->db->query("SELECT * FROM contactperson_group WHERE enable_page = 1 ORDER BY name");
			$v_user_groups = $o_query ? $o_query->result_array() : array();
		}
	}
	if($v_user_groups !== FALSE && count($v_user_groups)>0)
	{
		$s_user_departments = array();
		$s_user_departments_nopage = array();
		$s_user_groups = array();
		$s_user_adminall_departments = array();
		$s_user_adminall_groups = array();
		function getUnseenPostCount($groupId, $username){
			global $o_main;
			$o_query = $o_main->db->query("SELECT postfeed.id FROM postfeed
				LEFT OUTER JOIN content_activity_log ON content_activity_log.content_table='postfeed' AND content_activity_log.content_id = postfeed.id
				AND content_activity_log.action = 4 AND content_activity_log.username = ?
				WHERE postfeed.groupId = ? AND content_activity_log.created is null", array($username, $groupId));
			$unseenPostCount = $o_query ? $o_query->num_rows() : 0;
			return $unseenPostCount;
		}
		$readAccessElementsFromFw = true;
		include_once(__DIR__."/../../../modules/GroupPage/output/includes/readAccessElementsGroup.php");
		foreach($v_user_groups as $v_group)
		{
			if($v_group['enable_page'] != 1 && !$v_group['department']) continue;
			// if($v_group['account_id'] != $variables->account_id) continue;
			$is_group_member = false;
			$is_group_member_admin = false;
			$o_query = $o_main->db->query("SELECT c.email, gu.* FROM contactperson_group_user gu LEFT OUTER JOIN contactperson c ON c.id = gu.contactperson_id
				WHERE gu.contactperson_group_id = ?", array($v_group['id']));
			$members = $o_query ? $o_query->result_array() : array();
			foreach($members as $member) {
				if(mb_strtolower($variables->loggID) == mb_strtolower($member['email'])){
					$is_group_member = true;
					if($member['type'] == 2){
						$is_group_member_admin = true;
					}
				}
			}
			$v_group['is_group_member'] = $is_group_member;
			$v_group['is_admin'] = $is_group_member_admin;
			if(!$is_group_member){
				if(!$v_group['show_group_to_all_in_group_page']) {
					if(!$variables->useradmin){
						continue;
					} else {
						if($v_group['department']){
							array_push($s_user_adminall_departments, $v_group);
						} else {
							array_push($s_user_adminall_groups, $v_group);
						}
						continue;
					}
				} else {
					if($accessElementRestrict_NotAllowAccessToOpenGroups){
						continue;
					}
				}
			}

			$v_group['page_module'] = "GroupPage";
			// if($v_group['group_type'] == 1){
			// 	$v_group['page_module'] = "Customer2";
			// }
			if($v_group['department']){
				if($v_group['enable_page']){
					array_push($s_user_departments, $v_group);
				} else {
					array_push($s_user_departments_nopage, $v_group);
				}
			} else {
				array_push($s_user_groups, $v_group);
			}
		}

		$totalDepartmentsCount = (count($s_user_departments) + count($s_user_adminall_departments));
		$totalGroupsCount = (count($s_user_groups) + count($s_user_adminall_groups));
		foreach($s_user_departments as $v_group) {
			$s_class = (($_GET['module'] == $v_group['page_module'] && $_GET['inc_obj'] == 'details' && $_GET['cid'] == $v_group["id"]) ? ' active' : '');
			$departmentLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output&inc_obj=details&cid=".$v_group["id"];
			?>
			<div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_group['page_module'].$v_group["id"]).$s_class;?>">
				<a class="optimize abort_bg" href="<?php if($v_group['enable_page']){ echo $departmentLink; } else { echo '#';}?>">
					<span class="fw_menu_group_icon fas fa-users fw_menu_icon_color"></span><?php echo $v_group['name'];?>
					<?php
					if($v_group['display_posts_to_members'] == 1){
						$unseenPostCount = getUnseenPostCount($v_group['id'], $variables->loggID);
						if($unseenPostCount > 0){
							echo '<span class="badge unseenBadge">'.$unseenPostCount.'</span>';
						}
					}
					?>
				</a>
				<?php /*if($variables->useradmin && !$v_group['is_group_member']){ ?>
					<span class="fas fa-ellipsis-h giveYourselfAccess" data-group-id="<?php echo $v_group['id']?>"></span>
				<?php } */?>
			</div><?php
		}
		/*
		//removed, departments will be shown in People module
		if(count($s_user_adminall_departments) > 0){
		?>
		<div class="fw_menu_item showAllDepartments"><?php echo $formText_ShowAllDepartments_AccountFrameworkMenu;?> (<?php echo count($s_user_adminall_departments);?>)</div>
		<?php
		}
		foreach($s_user_adminall_departments as $v_group) {
			$s_class = (($_GET['module'] == $v_group['page_module'] && $_GET['inc_obj'] == 'details' && $_GET['cid'] == $v_group["id"]) ? ' active' : '');
			?><div class="fw_menu_item extradepartments fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_group['page_module'].$v_group["id"]).$s_class;?>">
				<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output&inc_obj=details&cid=".$v_group["id"]; ?>">
					<span class="fw_menu_group_icon fas fa-users fw_menu_icon_color"></span><?php echo $v_group['name'];?>
					<?php
					if($v_group['display_posts_to_members'] == 1){
						$unseenPostCount = getUnseenPostCount($v_group['id'], $variables->loggID);
						if($unseenPostCount > 0){
							echo '<span class="badge unseenBadge">'.$unseenPostCount.'</span>';
						}
					}
					?>
				</a>
				<?php if($variables->useradmin && !$v_group['is_group_member']){ ?>
					<span class="fas fa-ellipsis-h giveYourselfAccess" data-group-id="<?php echo $v_group['id']?>"></span>
				<?php } ?>
			</div><?php
		}
		if(count($s_user_departments_nopage) > 0){
		?>
		<div class="fw_menu_item showDepartmentsNopage"><?php echo $formText_DepartmentsWithoutPage_AccountFrameworkMenu;?> (<?php echo count($s_user_departments_nopage);?>)</div>
		<?php
		}
		foreach($s_user_departments_nopage as $v_group) {
			$s_class = (($_GET['module'] == $v_group['page_module'] && $_GET['inc_obj'] == 'details' && $_GET['cid'] == $v_group["id"]) ? ' active' : '');
			?><div class="fw_menu_item nopagedepartments fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_group['page_module'].$v_group["id"]).$s_class;?>">
				<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output&inc_obj=details&cid=".$v_group["id"]; ?>">
					<span class="fw_menu_group_icon fas fa-users fw_menu_icon_color"></span><?php echo $v_group['name'];?>
				</a>
				<?php if($variables->useradmin){ ?>
					<span class="glyphicon glyphicon-pencil edit_department" data-group-id="<?php echo $v_group['id']?>"></span>
					<span class="fas fa-users edit_department_members" data-group-id="<?php echo $v_group['id']?>"></span>
				<?php } ?>
			</div><?php
		}
		if($variables->useradmin && is_dir(__DIR__."/../../../modules/GroupPage/") && $totalDepartmentsCount > 0){
			?>
			<div class="fw_menu_item addNewDepartment">+ <?php echo $formText_AddNewDepartment_AccountFrameworkMenu; ?></div>
			<?php
		}
		*/
		$s_buffer = ob_get_clean();
		if($s_buffer!='')
		{
			?><div class="fw_menu_separator"><span><?php echo $formText_Departments_AccountFrameworkMenu;?></span></div><?php
			echo $s_buffer;
		}

		ob_start();
		foreach($s_user_groups as $v_group) {
			$s_sql = "SELECT group_page_additional_subpage.* FROM group_page_additional_subpage
			WHERE group_page_additional_subpage.group_id = ? ORDER BY group_page_additional_subpage.sortnr";
			$o_query = $o_main->db->query($s_sql, array($v_group['id']));
			$grouppage_additional_subpages = $o_query ? $o_query->result_array() : array();
			$additional_mainpage = "";
			foreach($grouppage_additional_subpages as $grouppage_additional_subpage) {
				if($grouppage_additional_subpage['make_group_mainpage']) {
					$additional_mainpage = $grouppage_additional_subpage['id'];
				}
			}
			$prefix = "&inc_obj=details&cid=".$v_group["id"];
			if($additional_mainpage != "") {
				$prefix = "&inc_obj=additional&additional_id=".$additional_mainpage."&cid=".$v_group["id"];
			}

			$s_class = (($_GET['module'] == $v_group['page_module'] && $_GET['inc_obj'] == 'details' && $_GET['cid'] == $v_group["id"]) ? ' active' : '');
			?><div class="fw_menu_item fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_group['page_module'].$v_group["id"]).$s_class;?>">
				<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output".$prefix; ?>">
					<span class="fw_menu_group_icon fas fa-users fw_menu_icon_color"></span><?php echo $v_group['name'];?>
					<?php
					if($v_group['display_posts_to_members'] == 1){
						$unseenPostCount = getUnseenPostCount($v_group['id'], $variables->loggID);
						if($unseenPostCount > 0){
							echo '<span class="badge unseenBadge">'.$unseenPostCount.'</span>';
						}
					}
					?>
				</a>
				<?php if($variables->useradmin && !$v_group['is_admin']){ ?>
					<span class="fas fa-ellipsis-h giveYourselfAccess" data-group-id="<?php echo $v_group['id']?>" data-is_member="<?php echo $v_group['is_group_member']?>"></span>
				<?php } ?>
			</div><?php
		}
		if(count($s_user_adminall_groups) > 0){
		?>
		<div class="fw_menu_item showAllGroups "><?php echo $formText_ShowAllGroups_AccountFrameworkMenu; ?> (<?php echo count($s_user_adminall_groups);?>)</div>
		<?php
		}
		foreach($s_user_adminall_groups as $v_group) {
			$s_sql = "SELECT group_page_additional_subpage.* FROM group_page_additional_subpage
			WHERE group_page_additional_subpage.group_id = ? ORDER BY group_page_additional_subpage.sortnr";
			$o_query = $o_main->db->query($s_sql, array($v_group['id']));
			$grouppage_additional_subpages = $o_query ? $o_query->result_array() : array();
			$additional_mainpage = "";
			foreach($grouppage_additional_subpages as $grouppage_additional_subpage) {
				if($grouppage_additional_subpage['make_group_mainpage']) {
					$additional_mainpage = $grouppage_additional_subpage['id'];
				}
			}
			$prefix = "&inc_obj=details&cid=".$v_group["id"];
			if($additional_mainpage != "") {
				$prefix = "&inc_obj=additional&additional_id=".$additional_mainpage."&cid=".$v_group["id"];
			}

			$s_class = (($_GET['module'] == $v_group['page_module'] && $_GET['inc_obj'] == 'details' && $_GET['cid'] == $v_group["id"]) ? ' active' : '');
			?><div class="fw_menu_item extragroups fw_menu_color activate <?php echo 'mod_'.preg_replace('/\s+/', '', $v_group['page_module'].$v_group["id"]).$s_class;?>">
				<a class="optimize abort_bg" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output".$prefix; ?>">
					<span class="fw_menu_group_icon fas fa-users fw_menu_icon_color"></span><?php echo $v_group['name'];?>
					<?php
					if($v_group['display_posts_to_members'] == 1){
						$unseenPostCount = getUnseenPostCount($v_group['id'], $variables->loggID);
						if($unseenPostCount > 0){
							echo '<span class="badge unseenBadge">'.$unseenPostCount.'</span>';
						}
					}
					?>
				</a>
				<?php if($variables->useradmin && !$v_group['is_admin']){ ?>
					<span class="fas fa-ellipsis-h giveYourselfAccess" data-group-id="<?php echo $v_group['id']?>" data-is_member="<?php echo $v_group['is_group_member']?>"></span>
				<?php } ?>
			</div><?php
		}
	}

	if(isset($variables->accountinfo['allow_add_group']) && intval($variables->accountinfo['allow_add_group']) > 0) {
		$variables->accountinfo_basisconfig['allow_add_group'] = intval($variables->accountinfo['allow_add_group']) - 1;
	}
	$variables->accountinfo_basisconfig['allow_add_group'] = isset($variables->accountinfo_basisconfig['allow_add_group']) ? intval($variables->accountinfo_basisconfig['allow_add_group']) : FALSE;

	$variables->accountinfo['allow_create_grouppage_only_for_admin'] = isset($variables->accountinfo['allow_create_grouppage_only_for_admin']) ? intval($variables->accountinfo['allow_create_grouppage_only_for_admin']) : FALSE;
	if((0 == $variables->accountinfo_basisconfig['allow_add_group'] || (1 == $variables->accountinfo_basisconfig['allow_add_group'] && $variables->useradmin)) && is_dir(__DIR__."/../../../modules/GroupPage/")){
		?>
		<div class="fw_menu_item addNewGroup ">+  <?php echo $formText_AddNewGroup_AccountFrameworkMenu; ?></div>
		<?php
		/*if($totalDepartmentsCount == 0){ ?>
			<div class="fw_menu_item addNewDepartment">+ <?php echo $formText_AddNewDepartment_AccountFrameworkMenu; ?></div>
			<?php
		}*/
	}
	$s_buffer = ob_get_clean();
	if($s_buffer!='')
	{
		if(isset($totalDepartmentsCount, $totalGroupsCount) && $totalDepartmentsCount+$totalGroupsCount > 0) {
			?><div class="fw_menu_separator"><span><?php echo $formText_Groups_AccountFrameworkMenu;?></span></div><?php
		}
		echo $s_buffer;
	}
	?>

	<?php if(isset($variables->fw_settings_basisconfig['user_alternative_design']) && $variables->fw_settings_basisconfig['user_alternative_design']){ ?>
		<div class="mobile" style="display: none;">
			<div class="fw_menu_separator"><span>&nbsp;</span></div>
			<div class="fw_menu_item fw_menu_color">
				<!--  Log out button -->
				<a href="<?php echo $_SERVER['PHP_SELF'];?>?logout=1" class="fw_logout_button fw_link_box" id="fw_logut_btn" title="<?php echo $formText_Logout_blueline;?>">
					<span ><?php echo $formText_Logout_blueline?></span>
				</a>
			</div>
		</div>
	<?php } ?>

	</div>
	<?php
	$include_file = __DIR__."/output_javascript.php";
	if(is_file($include_file)) include($include_file);

} else {
	echo "Ikke logget inn";
}
