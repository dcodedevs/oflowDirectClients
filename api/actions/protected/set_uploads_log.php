<?php
$o_main->db->query("UPDATE uploads SET filename = '".$o_main->db->escape_str($v_data['content_filename'])."', filepath = '".$o_main->db->escape_str($v_data['content_filepath'])."', size = '".$o_main->db->escape_str($v_data['content_size'])."' WHERE id = '".$o_main->db->escape_str($v_data['content_upload_id'])."'");

$v_return['status'] = 1;