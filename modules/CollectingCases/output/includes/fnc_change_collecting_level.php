<?php
function change_collecting_level($collecting_case_id, $fromLevel, $toLevel, $updateBy) {
    global $o_main;
    global $formText_CollectingLevelWasChangedFrom;
    global $formText_To_output;

    $sql = "UPDATE collecting_cases SET
    updated = now(),
    updatedBy='".$variables->loggID."',
    collectinglevel = '".$o_main->db->escape_str($toLevel)."',
    last_collecting_change_date = now()
    WHERE id = '".$o_main->db->escape_str($collecting_case_id)."'";
    $o_query = $o_main->db->query($sql);

    if($o_query){
        $sql = "INSERT INTO collecting_cases_handling SET
        created = now(),
        createdBy='".$updateBy."',
        collecting_case_id ='".$o_main->db->escape_str($collecting_case_id)."',
        text = '".$o_main->db->escape_str($formText_CollectingLevelWasChangedFrom." ".$fromLevel." ".$formText_To_output." ".$toLevel)."',
        from_status = '".$o_main->db->escape_str($fromLevel)."',
        to_status = '".$o_main->db->escape_str($toLevel)."',
        type = '0'";
        $o_query = $o_main->db->query($sql);
    }

}
?>
