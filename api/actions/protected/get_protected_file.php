<?php

$params = $v_data['params'];

$s_table = $o_main->db_escape_name($params['table']);

if($o_main->db->table_exists($s_table.'content'))
{
    $s_sql = "SELECT ".$s_table.".id cid, ".$s_table.".*, ".$s_table."content.* FROM ".$s_table." JOIN ".$s_table."content ON ".$s_table."content.".$s_table."ID = ".$s_table.".id AND ".$s_table."content.languageID = ".$o_main->db->escape($params['languageID'])." WHERE ".$s_table.".id = ".$o_main->db->escape($params['ID']);
} else {
    $s_sql = "SELECT ".$s_table.".id cid, ".$s_table.".* FROM ".$s_table." WHERE ".$s_table.".id = ".$o_main->db->escape($params['ID']);
}
$content = array();
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $content = $o_query->row_array();

$module = array();
$o_query = $o_main->db->query('select name from moduledata where id = ?', array($content['moduleID']));
if($o_query && $o_query->num_rows()>0) $module = $o_query->row_array();

$params['file'] = stripslashes($params['file']);
$v_file = explode("/", $params['file']);
$s_file = array_pop($v_file);
$s_file_encoded = implode("/", $v_file)."/".rawurlencode($s_file);

$b_found_file = false;
if(strpos($content[$params['field']], $params['file']) !== false || strpos($content[$params['field']], $s_file_encoded) !== false)
{
    $b_found_file = true;
} else {
    $v_data = json_decode($content[$params['field']],true);
    foreach($v_data as $v_item)
    {
        foreach($v_item[1] as $s_file)
        {
            if(strpos($s_file, $params['file']) !== false || strpos($s_file, $s_file_encoded) !== false)
            {
                $b_found_file = true;
                break 2;
            }
        }
    }
}

if ($b_found_file) {
    $file_src = realpath(__DIR__ . '/../../../uploads/protected/' . $params['file']);
	$s_mime_type = mime_content_type($file_src);
	if(pathinfo($file_src, PATHINFO_EXTENSION) === 'csv') $s_mime_type = 'text/csv';
	if($s_mime_type == "") $s_mime_type = "application/octet-stream";
	
	$v_return['data'] = array(
        'file_module' => $module['name'],
        'file_path' => $file_src,
		'mime_type' => $s_mime_type,
		'file_content' => base64_encode(file_get_contents($file_src)),
    );
} else {
    $v_return['data']['err'] = 1;
}

?>
