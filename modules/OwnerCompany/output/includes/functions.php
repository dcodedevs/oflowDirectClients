<?php
function get_ownercompany_list($o_main, $filter, $search_filter) {
    $list = array();
    if ($search_filter) {
        $sql = "SELECT oc.*
        FROM ownercompany oc WHERE (oc.name LIKE '%$search_filter%') ORDER BY name ASC";
    } else {
        $sql = "SELECT oc.*
        FROM ownercompany oc ORDER BY name ASC";
    }

    $result = $o_main->db->query($sql);
    if($result && $result->num_rows() > 0)

    foreach($result->result() AS $row) {
        if ($filter == 'active') {
            if (!$row->content_status) {
                array_push($list, $row);
            }
        }

        if ($filter == 'inactive') {
            if ($row->content_status == 2) {
                array_push($list, $row);
            }
        }
    }

    return $list;
}
