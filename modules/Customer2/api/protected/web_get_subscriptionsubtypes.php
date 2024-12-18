<?php

$o_query = $o_main->db->query("SELECT * FROM subscriptiontype_subtype
WHERE content_status < 2 AND subscriptiontype_id = '".$o_main->db->escape_str($v_data['params']['subscriptiontype_id'])."' ORDER BY name ASC");
$subscriptionSubtypes = $o_query ? $o_query->result_array() : array();
$returnSubscriptionSubtypes = array();
foreach($subscriptionSubtypes as $subscriptionSubtype){
    $o_query = $o_main->db->query("SELECT * FROM subscriptionmulti s
    LEFT OUTER JOIN customer c ON c.id = s.customerId
    WHERE c.content_status < 2 AND s.content_status < 2 AND s.subscriptionsubtypeId = '".$o_main->db->escape_str($subscriptionSubtype['id'])."'
    AND s.startDate <> '0000-00-00' AND s.startDate is NOT null AND s.startDate <= CURDATE()
    AND
    (
        (s.stoppedDate <> '0000-00-00' AND s.stoppedDate is NOT null AND s.stoppedDate > CURDATE())
        OR (s.stoppedDate = '0000-00-00' OR s.stoppedDate is null)
    )
    GROUP BY s.id");
    $activeSubscriptionCount = $o_query ? $o_query->num_rows() : 0;
    $subscriptionSubtype['activeSubscriptionCount'] = $activeSubscriptionCount;
    $returnSubscriptionSubtypes[] = $subscriptionSubtype;
}

$v_return['data'] = $returnSubscriptionSubtypes;

?>
