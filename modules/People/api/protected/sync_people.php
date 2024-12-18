<?php
$v_return['status'] = 0;

$peopleSync = $v_data['params']['dump_sql'];
if($peopleSync != ""){
    $array = explode("\n", $peopleSync);
    $v_return['status'] = 1;
    foreach ($array as $line)
    {
        if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 1) == '#')
            continue;
        $temp_line .= $line;
        if (substr(trim($line), -1, 1) == ';')
        {
            $o_query = $o_main->db->simple_query($temp_line);
            if(!$o_query){
                $v_return['status'] = 0;
            }
            $temp_line = '';
        }
    }
    if($v_return['status']){        
        $o_main->db->query("UPDATE accountinfo SET force_cache_refresh = NOW()");
    }
}
?>
