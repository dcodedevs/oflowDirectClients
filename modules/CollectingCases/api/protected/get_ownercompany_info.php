<?php
$getPage = $o_main->db->query("SELECT * FROM ownercompany ORDER BY id ASC");
$ownercompany = $getPage ? $getPage->row_array() : array();
$v_return['data'] = $ownercompany;
$v_return['status'] = 1; 
?>
