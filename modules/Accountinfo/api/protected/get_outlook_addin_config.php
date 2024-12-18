<?php
$o_query = $o_main->db->query("SELECT * FROM project_accountconfig ORDER BY id DESC");
$v_project_accountconfig = $o_query ? $o_query->row_array() : array();



$v_return['activate_outlook_addin_create_project'] = (int)$v_project_accountconfig['activate_outlook_addin_create_project'];
$v_return['status'] = 1;