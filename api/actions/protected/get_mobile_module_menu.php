<?php
//refresh tags
include(__DIR__."/refresh_filtering_tags.php");

// TODO: check module access
$variables->menu_access = json_decode($v_fw_session['cache_menu'], TRUE);

// Get framework settings
$o_query = $o_main->db->get('frameworksettings_basisconfig');
$variables->fw_settings_basisconfig = $o_query ? $o_query->row_array() : array();
$o_query = $o_main->db->query("SELECT *, IF(id = '".$o_main->db->escape_str($v_fw_session['style_set'])."', 0, 1) AS priority FROM frameworksettings WHERE id = '".$o_main->db->escape_str($v_fw_session['style_set'])."' OR id > 0 ORDER BY priority, id LIMIT 1");
$variables->fw_settings_accountconfig = $o_query ? $o_query->row_array() : array();


if(isset($variables->fw_settings_accountconfig['activate_module_hiding_script']) && is_file(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']))
{
	$_COOKIE['username'] = $v_data['username'];
	include(BASEPATH.$variables->fw_settings_accountconfig['activate_module_hiding_script']);
}

$result = array();
// Testing, list all modules
$sql = "SELECT id,
name,
local_name,
mobile_type,
mobile_show_in_custom_tab,
virtual_module_source
FROM moduledata
WHERE mobile_type > 0
AND content_status = 0
AND (deactivated IS NULL OR deactivated = 0)
ORDER BY ordernr ASC";
$o_query = $o_main->db->query($sql);
if($o_query && $o_query->num_rows()>0)
foreach($o_query->result_array() as $v_row)
{
	if($variables->menu_access[$v_row['name']][3] == 'D' && $v_row['name'] != "GroupPage") continue;
	if ($v_row['virtual_module_source']) $v_row['name'] = $v_row['virtual_module_source'];
	$result[] = $v_row;
}

$v_return['data'] = $result;
