<?php
/*
    $parameters = array('userID', "countOnly" "unseen");
*/

function fw_get_notifications($o_main, $parameters) {
    $userID = $parameters['userID'];
    $countOnly = $parameters['countOnly'];
    $unseen = $parameters['unseen'];
    $seen = $parameters['seen'];
    $page = $parameters['page'];
    $per_page = $parameters['per_page'];
    $before_id = $parameters['before_id']; // when looking for messages older than given id
    $after_id = $parameters['after_id']; // when looking for messages new than given id
    
    $sql_where = "";
    
    if($unseen) {
        $sql_where .= " AND (nc.is_seen = 0 OR nc.is_seen is null)";
    }
    
    if($seen) {
        $sql_where .= " AND (nc.is_seen = 1)";
    }
    
    $sql_limit = "";

    if ($per_page > 0) {
        $sql_limit = " LIMIT ".$per_page;    

        if ($page > 0) {
            $offset = ($page - 1) * $per_page;
            $sql_limit = " LIMIT ".$per_page." OFFSET ".$offset;    
        }

        if ($before_id) {
            $sql_where .= " AND nc.id < " . $before_id;
        }
    }
    
    if ($after_id > 0) {
        $sql_where .= " AND nc.id > " . $after_id;
    }

    $notifications = array();
    if($o_main != "" && $userID != "") {
        $s_sql = "SELECT * FROM notificationcenter nc WHERE nc.receiver_user_id = ? ".$sql_where." ORDER BY nc.created DESC".$sql_limit;
        $o_query = $o_main->db->query($s_sql, array($userID));
        if($countOnly){
            $notifications = $o_query ? $o_query->num_rows(): 0;
        } else {
            $notifications = $o_query ? $o_query->result_array(): array();

            // Get post ids for comments
            foreach ($notifications as &$notification) {
                if ($notification['content_table'] == 'feedback_comment') {
                    $sql = "SELECT content_id FROM feedback_comment WHERE id = ?";
                    $o_query = $o_main->db->query($sql, array($notification['content_id']));
                    $row_data = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
                    $notification['extra_content_id'] = $row_data['content_id'];
                }
            }
        }

    }
    return $notifications;
}
?>
