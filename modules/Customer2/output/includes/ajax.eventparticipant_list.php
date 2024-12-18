<?php

$customerId = $_POST['customerId'] ? $_POST['customerId'] : '';
$s_sql = "SELECT * FROM getynet_event_client";
$o_query = $o_main->db->query($s_sql);
$getynet_event_client = ($o_query ? $o_query->row_array():array());

if($getynet_event_client['ge_account_url'] != "" && $getynet_event_client['ge_account_token'] != ""){
    $params = array(
        'api_url' => $getynet_event_client['ge_account_url'],
        'access_token'=> $getynet_event_client['ge_account_token'],
        'module' => 'GetynetEventProvider',
        'action' => 'customer_get_participants',
        'params' => array(
            'languageID' => $languageID,
            'customer_id'=> $customerId,
            'ge_provider_id' => $getynet_event_client['ge_provider_id']
        )
    );
    $response = fw_api_call($params, false);
    if($response['status']){
        $attendees = $response['attendees'];
        ?>
        <table class="table table-bordered table-striped">
            <tr>
                <th><?php echo $formText_EventDate_output; ?></th>
                <th><?php echo $formText_Event_output; ?></th>
                <th><?php echo $formText_Attendee_output; ?></th>
                <!-- <th>&nbsp;</th> -->
            </tr>
                <?php
                foreach($attendees as $attendee) {
                    ?>
                    <tr>
                        <td><?php echo date("d.m.Y", strtotime($attendee['eventDate'])); ?></td>
                        <td><?php echo $attendee['eventName']; ?></td>
                        <td><?php echo $attendee['name']." ".$attendee['middle_name']." ".$attendee['last_name']; ?></td>
                    </tr>
                    <?php
                }
                ?>
        </table>
        <?php
    }
}
?>
