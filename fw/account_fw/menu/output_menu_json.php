<?php
$include_file = __DIR__."/../languages/default.php";
if(is_file($include_file)) include($include_file);
$include_file = __DIR__."/../languages/".$variables->languageID.".php";
if(is_file($include_file)) include($include_file);

if($variables->loggID != '')
{
	$getcode = explode("&",urldecode($variables->fw_session['urlpath']));
	for($x=0;$x<count($getcode);$x++)
	{
		list($name,$value) = explode("=",$getcode[$x]);
		${$name} = str_replace("%2F","/",$value);
	}
	while(list($name,$value) = each($_GET))
	{
		${$name} = str_replace("%2F","/",$value);
	}
	
	if(isset($variables->fw_settings_accountconfig['activate_module_hiding_script']) && is_file(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']))
	{
		include(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']);
	}
	
	$l_x = 0;
	$b_expand_admin_module = false;
	$v_modules = $v_admin_modules = $v_modules_print = $v_module_show_after = array();
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
			$v_data = array
			(
				'module'=> $s_module,
				'name'=> ($variables->developeraccess == 0 ? $variables->menu_access[$s_module][0] : $s_module),
				'url'=> $variables->menu_access[$s_module][1],
				'url_input'=> "module=".$s_module."&folder=input&folderfile=input&updatepath=1".($v_row['externalurl'] != "" ? "&external=".$v_row['externalurl'] : ""),
				'class'=> ($_GET['module']==$s_module ? ' active' : ''),
				'deactivated'=>$v_row['deactivated']
			);
			if($v_row['modulemode']=='A')
			{
				if($v_row['deactivated'] == 1) continue;
				$v_admin_modules[$s_module] = $v_data;
				if($_GET['module']==$s_module) $b_expand_admin_module = true;
			} else {
				$v_modules[$s_module] = $v_data;
			}
		}
	}
	
	$v_modules_free = array_keys($v_modules);
	$o_query = $o_main->db->query('SELECT s.* FROM sys_modulemenuset s JOIN sys_modulemenuusers u ON u.set_id = s.id WHERE u.username = ?', array($username));
	if(!$o_query || ($o_query && $o_query->num_rows() == 0))
	{
		$o_query = $o_main->db->query('SELECT * FROM sys_modulemenuset WHERE default_set = 1');
	}
	$v_set = array();
	if($o_query)
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
	
	$l_count = 0;
	if($variables->useradmin == 1)
	{
		$v_obj = array("name" => $formText_adminUserinfo_getynetmenu, "url" => $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&getynetaccount=1&module=37&folderfile=output&folder=output&modulename=users&usermodule=1&updatepath=1");
		if(count($v_admin_modules)>0)
		{
			$v_menu_list[$l_count] = array("name" => $formText_AdminModules_FwMenu, "childs" => array());
			$v_menu_list[$l_count]["childs"][] = $v_obj;
			foreach($v_admin_modules as $v_module)
			{
				$v_obj = array("name" => $v_module['name'], "url" => $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"]);
				/*if($variables->developeraccess > 0 && strpos($v_module["url"],'folderfile=input') === false)
				{
					$v_obj["url_input"] = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];
				}*/
				$v_menu_list[$l_count]["childs"][] = $v_obj;
			}
		} else {
			$v_menu_list[$l_count] = $v_obj;
		}
		$l_count++;
	}
	
	foreach($v_modules_print as $l_key => $v_item)
	{
		if(isset($v_item["childs"])) 
		{
			$v_menu_list[$l_count] = array("name" => $v_item['name'], "childs" => array());
			foreach($v_item["childs"] as $s_module)
			{
				$v_module = $v_modules[$s_module];
				$v_obj = array("name" => $v_module['name'], "url" => $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"]);
				/*if($variables->developeraccess > 0 && strpos($v_module["url"],'folderfile=input') === false)
				{
					$v_obj["url_input"] = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];
				}*/
				$v_menu_list[$l_count]["childs"][] = $v_obj;
			}
		} else {
			$v_module = $v_modules[$v_item];
			$v_obj = array("name" => $v_module['name'], "url" => $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url"]);
			/*if($variables->developeraccess > 0 && strpos($v_module["url"],'folderfile=input') === false)
			{
				$v_obj["url_input"] = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&".$v_module["url_input"];
			}*/
			$v_menu_list[$l_count] = $v_obj;
		}
		$l_count++;
	}
	
	if(isset($variables->accountinfo['activate_statistic']) && $variables->accountinfo['activate_statistic'] == 1)
	{
		$v_menu_list["usr_".$l_count] = array("name" => $formText_Statistic_AccountFrameworkMenu, "url" => "http://".$_SERVER['HTTP_HOST']."/awstats/awstats.pl?config=".$variables->accountinfo['domain']);
		$l_count++;
	}
	
	$v_user_groups = json_decode($variables->fw_session['user_groups'], TRUE);
	if($v_user_groups !== FALSE && count($v_user_groups)>0)
	{
		$v_menu_list[$l_count] = array("name" => $formText_Groups_AccountFrameworkMenu, "childs" => array());
		foreach($v_user_groups as $v_group)
		{
			if($v_group['enable_page'] != 1) continue;
			$v_obj = array("name" => $v_group['name'], "url" => $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID'].(!$variables->fw_url_share?"&caID=".$_GET['caID']:'')."&module=".$v_group['page_module']."&folder=output&folderfile=output&inc_obj=details&cid=".$v_group["id"]);
			$v_menu_list[$l_count]["childs"][] = $v_obj;
		}
		$l_count++;
	}
}