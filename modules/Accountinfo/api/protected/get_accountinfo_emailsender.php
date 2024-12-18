<?php

$sql = "SELECT * FROM accountinfo_emailsender_accountconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_emailsender_accountconfig = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM sys_emailserverconfig order by default_server desc");
$v_email_server_config = $o_query ? $o_query->row_array() : array();

$accountinfo_emailsender_accountconfig['host'] = $v_email_server_config['host'];

$v_return['data'] = $accountinfo_emailsender_accountconfig;
?>
