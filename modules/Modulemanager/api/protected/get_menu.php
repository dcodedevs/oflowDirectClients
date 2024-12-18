<?php
// TODO: check module access

// Testing, list all modules
$sql = "SELECT id, 
name,
local_name,
mobile_type,
mobile_show_in_custom_tab
FROM moduledata 
WHERE mobile_type > 0
AND content_status = 0
AND (deactivated IS NULL OR deactivated = 0)
ORDER BY ordernr ASC";
$o_query = $o_main->db->query($sql);
$result = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
$v_return['data'] = $result;

