<?php
if(!function_exists("fw_checkIfContactPersonHasValidActiveSubscription")){
    function fw_checkIfContactPersonHasValidActiveSubscription($contactPerson, $dateMarker){
        global $o_main;

        $hasActiveValidSubscription = false;
        if($contactPerson['intranet_membership_subscription_type'] == 0) {
            $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti
            WHERE subscriptionmulti.customerId = ?
            AND subscriptionmulti.startDate <= str_to_date(?, '%Y-%m-%d')
           AND ( subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate >= str_to_date(?, '%Y-%m-%d'))";
            $o_query = $o_main->db->query($s_sql, array($contactPerson['customerId'], $dateMarker, $dateMarker));
            $activeSubscriptions = $o_query ? $o_query->result_array() : array();
            if(count($activeSubscriptions) > 0){
                $hasActiveValidSubscription = true;
            }

        } else if($contactPerson['intranet_membership_subscription_type'] == 1) {
            $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti
            JOIN contactperson_subscription_connection ON contactperson_subscription_connection.subscriptionmulti_id = subscriptionmulti.id WHERE contactperson_subscription_connection.contactperson_id = ?
            AND subscriptionmulti.startDate <= str_to_date(?, '%Y-%m-%d')
            AND ( subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate >= str_to_date(?, '%Y-%m-%d'))";
            $o_query = $o_main->db->query($s_sql, array($contactPerson['id'], $dateMarker, $dateMarker));
            $activeSubscriptions = $o_query ? $o_query->result_array() : array();
            if(count($activeSubscriptions) > 0){
                $hasActiveValidSubscription = true;
            }
        } else if($contactPerson['intranet_membership_subscription_type'] == 2) {
            $hasActiveValidSubscription = true;
        }
        return $hasActiveValidSubscription;
    }
}
if(!function_exists("fw_getConnectedMemberships")){
    function fw_getConnectedMemberships($username){
    global $o_main;
    $return = array();

    $dateMarker = date("Y-m-d");

    $s_sql = "SELECT contactperson.* FROM contactperson WHERE email = ?";
    $o_query = $o_main->db->query($s_sql, array($username));
    $contactPersons = $o_query ? $o_query->result_array() : array();

    foreach($contactPersons as $contactPerson) {
        //$hasActiveValidSubscription = fw_checkIfContactPersonHasValidActiveSubscription($contactPerson, $dateMarker);

        //if($hasActiveValidSubscription) {
            if(intval($contactPerson['intranet_membership_type']) == 0) {
                $s_sql = "SELECT intranet_membership.* FROM intranet_membership
                JOIN intranet_membership_customer_connection ON intranet_membership_customer_connection.membership_id = intranet_membership.id WHERE intranet_membership_customer_connection.customer_id = ?";
                $o_query = $o_main->db->query($s_sql, array($contactPerson['customerId']));
                $memberships = $o_query ? $o_query->result_array() : array();

            } else if($contactPerson['intranet_membership_type'] == 1) {
                $s_sql = "SELECT intranet_membership.* FROM intranet_membership
                JOIN intranet_membership_contactperson_connection ON intranet_membership_contactperson_connection.membership_id = intranet_membership.id WHERE intranet_membership_contactperson_connection.contactperson_id = ?";
                $o_query = $o_main->db->query($s_sql, array($contactPerson['id']));
                $memberships = $o_query ? $o_query->result_array() : array();

            }
            foreach($memberships as $membership) {
                if(!in_array($membership, $return)){
                    array_push($return, $membership);
                }
            }
        //}
    }
    return $return;
}
}
if(!function_exists("fw_getReadTags")){
    function fw_getReadTags ($username) {
        global $o_main;
        $return = array();
        $memberships = fw_getConnectedMemberships($username);

        foreach($memberships as $membership) {
            $s_sql = "SELECT property.id, property.name FROM property
            JOIN intranet_membership_attached_object ON intranet_membership_attached_object.object_id = property.id
            WHERE intranet_membership_attached_object.membership_id = ?
            GROUP BY property.id";
            $o_query = $o_main->db->query($s_sql, array($membership['id']));
            $list = $o_query ? $o_query->result_array() : array();

            foreach($list as $item) {
                if(!in_array($item, $return)){
                    array_push($return, $item);
                }
            }
        }
        return $return;
    }
}
if(!function_exists("fw_getReadGroups")){
    function fw_getReadGroups ($username) {
        global $o_main;
        $return = array();
        $memberships = fw_getConnectedMemberships($username);

        foreach($memberships as $membership) {
            $s_sql = "SELECT property_group.id, property_group.name FROM property_group
            JOIN intranet_membership_attached_object ON intranet_membership_attached_object.objectgroup_id = property_group.id
            WHERE intranet_membership_attached_object.membership_id = ?
            GROUP BY property_group.id";
            $o_query = $o_main->db->query($s_sql, array($membership['id']));
            $list = $o_query ? $o_query->result_array() : array();

            foreach($list as $item) {
                if(!in_array($item, $return)){
                    array_push($return, $item);
                }
            }
        }
        return $return;
    }
}
if(!function_exists("fw_getWriteTags")){
    function fw_getWriteTags ($username) {
        global $o_main;
        $return = array();

        $memberships = fw_getConnectedMemberships($username);

        foreach($memberships as $membership) {
            $s_sql = "SELECT property.id, property.name FROM property
            JOIN intranet_membership_attached_object ON intranet_membership_attached_object.object_id = property.id
            WHERE intranet_membership_attached_object.membership_id = ?
            GROUP BY property.id";
            $o_query = $o_main->db->query($s_sql, array($membership['id']));
            $list = $o_query ? $o_query->result_array() : array();

            foreach($list as $item) {
                if(!in_array($item, $return)){
                    array_push($return, $item);
                }
            }
        }
        return $return;
    }
}
if(!function_exists("fw_getWriteGroups")){
    function fw_getWriteGroups ($username) {
        global $o_main;
        $return = array();
        $memberships = fw_getConnectedMemberships($username);

        foreach($memberships as $membership) {
            $s_sql = "SELECT property_group.id, property_group.name FROM property_group
            JOIN intranet_membership_attached_object ON intranet_membership_attached_object.objectgroup_id = property_group.id
            WHERE intranet_membership_attached_object.membership_id = ?
            GROUP BY property_group.id";
            $o_query = $o_main->db->query($s_sql, array($membership['id']));
            $list = $o_query ? $o_query->result_array() : array();

            foreach($list as $item) {
                if(!in_array($item, $return)){
                    array_push($return, $item);
                }
            }
        }
        return $return;
    }
}
if(!function_exists("fw_getMembershipSettings")){
    function fw_getMembershipSettings($username) {
        global $o_main;
        $return = array();

        $dateMarker = date("Y-m-d");

        $s_sql = "SELECT contactperson.* FROM contactperson WHERE email = ?";
        $o_query = $o_main->db->query($s_sql, array($username));
        $contactPersons = $o_query ? $o_query->result_array() : array();

        foreach($contactPersons as $contactPerson) {
            //$hasActiveValidSubscription = fw_checkIfContactPersonHasValidActiveSubscription($contactPerson, $dateMarker);

            //if($hasActiveValidSubscription) {
                if(intval($contactPerson['intranet_membership_type']) == 0) {
                    $s_sql = "SELECT intranet_membership.id, intranet_membership.name  FROM intranet_membership
                    JOIN intranet_membership_customer_connection ON intranet_membership_customer_connection.membership_id = intranet_membership.id WHERE intranet_membership_customer_connection.customer_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($contactPerson['customerId']));
                    $memberships = $o_query ? $o_query->result_array() : array();
                    foreach($memberships as $membership) {
                        $s_sql = "SELECT id, object_id, objectgroup_id, membership_id  FROM intranet_membership_attached_object
                        WHERE intranet_membership_attached_object.membership_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($membership['id']));
                        $membership_customer_objects = $o_query ? $o_query->result_array() : array();
                        $membership_customer_connections = array();
                        foreach($membership_customer_objects as $membership_customer_object){
                            $s_sql = "SELECT module_name, access_level, intranet_membership_attached_object_id, id FROM intranet_membership_attached_object_setting
                            WHERE intranet_membership_attached_object_setting.intranet_membership_attached_object_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($membership_customer_object['id']));
                            $membership_settings = $o_query ? $o_query->result_array() : array();
                            $membership_customer_object['settings'] = $membership_settings;

                            array_push($membership_customer_connections, $membership_customer_object);
                        }
                        $membership['customer_attached_objects'] = $membership_customer_connections;
                        array_push($return, $membership);
                    }
                } else if($contactPerson['intranet_membership_type'] == 1) {
                    $s_sql = "SELECT intranet_membership.id, intranet_membership.name FROM intranet_membership
                    JOIN intranet_membership_contactperson_connection ON intranet_membership_contactperson_connection.membership_id = intranet_membership.id WHERE intranet_membership_contactperson_connection.contactperson_id = ?";
                    $o_query = $o_main->db->query($s_sql, array($contactPerson['id']));
                    $memberships = $o_query ? $o_query->result_array() : array();
                    foreach($memberships as $membership) {
                        $s_sql = "SELECT id, object_id, objectgroup_id, membership_id FROM intranet_membership_attached_object
                        WHERE intranet_membership_attached_object.membership_id = ?";
                        $o_query = $o_main->db->query($s_sql, array($membership['id']));
                        $membership_customer_objects = $o_query ? $o_query->result_array() : array();
                        $membership_customer_connections = array();
                        foreach($membership_customer_objects as $membership_customer_object){
                            $s_sql = "SELECT module_name, access_level, intranet_membership_attached_object_id, id FROM intranet_membership_attached_object_setting
                            WHERE intranet_membership_attached_object_setting.intranet_membership_attached_object_id = ?";
                            $o_query = $o_main->db->query($s_sql, array($membership_customer_object['id']));
                            $membership_settings = $o_query ? $o_query->result_array() : array();
                            $membership_customer_object['settings'] = $membership_settings;
                            array_push($membership_customer_connections, $membership_customer_object);
                        }
                        $membership['customer_attached_objects'] = $membership_customer_connections;
                        array_push($return, $membership);
                    }
                }

            //}
        }
        return $return;
    }
}
if(!function_exists("fw_getDoorCodeSettings")){
    function fw_getDoorCodeSettings($username) {
        global $o_main;
        $return = array();

        $dateMarker = date("Y-m-d");

        $s_sql = "SELECT contactperson.* FROM contactperson WHERE email = ?";
        $o_query = $o_main->db->query($s_sql, array($username));
        $contactPersons = $o_query ? $o_query->result_array() : array();

        foreach($contactPersons as $contactPerson) {
            $hasActiveValidSubscription = false;
            if($contactPerson['door_access_code_type'] == 1) {
                $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti
                WHERE subscriptionmulti.customerId = ?
                AND subscriptionmulti.startDate <= str_to_date(?, '%Y-%m-%d')
               AND ( subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate >= str_to_date(?, '%Y-%m-%d'))";
                $o_query = $o_main->db->query($s_sql, array($contactPerson['customerId'], $dateMarker, $dateMarker));
                $activeSubscriptions = $o_query ? $o_query->result_array() : array();
                if(count($activeSubscriptions) > 0){
                    $hasActiveValidSubscription = true;
                }

            } else if($contactPerson['door_access_code_type'] == 2) {
                $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti
                JOIN contactperson_doorcode_connection ON contactperson_doorcode_connection.subscriptionmulti_id = subscriptionmulti.id WHERE contactperson_doorcode_connection.contactperson_id = ?
                AND subscriptionmulti.startDate <= str_to_date(?, '%Y-%m-%d')
                AND ( subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate >= str_to_date(?, '%Y-%m-%d'))";
                $o_query = $o_main->db->query($s_sql, array($contactPerson['id'], $dateMarker, $dateMarker));
                $activeSubscriptions = $o_query ? $o_query->result_array() : array();
                if(count($activeSubscriptions) > 0){
                    $hasActiveValidSubscription = true;
                }
            } else if($contactPerson['door_access_code_type'] == 3) {
                $hasActiveValidSubscription = true;
            }

    		$return['allowed'] = ($hasActiveValidSubscription ? 1 : 0);
    		if($hasActiveValidSubscription) break;
        }
        return $return;
    }
}
if(!function_exists("fw_getConnectedContactPersons")){
    function fw_getConnectedContactPersons($membership){
        global $o_main;
        $finalContactpersons = array();
        $s_sql = "SELECT contactperson.* FROM contactperson LEFT OUTER JOIN customer ON customer.id = contactperson.customerId
        LEFT OUTER JOIN intranet_membership_customer_connection ON intranet_membership_customer_connection.customer_id = customer.id
        WHERE (contactperson.intranet_membership_type = 0 OR contactperson.intranet_membership_type is null) AND intranet_membership_customer_connection.membership_id = ?";
        $o_query = $o_main->db->query($s_sql, array($membership['id']));
        $contactPersons = $o_query ? $o_query->result_array() : array();
        foreach($contactPersons as $contactPerson) {
            if(!in_array($contactPerson, $finalContactpersons)){
                array_push($finalContactpersons, $contactPerson);
            }
        }

        $s_sql = "SELECT contactperson.* FROM contactperson LEFT OUTER JOIN customer ON customer.id = contactperson.customerId
        LEFT OUTER JOIN intranet_membership_contactperson_connection ON intranet_membership_contactperson_connection.contactperson_id = contactperson.id
        WHERE contactperson.intranet_membership_type = 1 AND intranet_membership_contactperson_connection.membership_id = ?";
        $o_query = $o_main->db->query($s_sql, array($membership['id']));
        $contactPersons = $o_query ? $o_query->result_array() : array();
        foreach($contactPersons as $contactPerson) {
            if(!in_array($contactPerson, $finalContactpersons)){
                array_push($finalContactpersons, $contactPerson);
            }
        }

        return $finalContactpersons;
    }
}
if(!function_exists("fw_getUsernamesConnectedToTags")){
    function fw_getUsernamesConnectedToTags($tagIds){
        global $o_main;
        $usernames = array();
        if(count($tagIds) > 0){
            $s_sql = "SELECT intranet_membership.id FROM intranet_membership
            JOIN intranet_membership_attached_object ON intranet_membership_attached_object.membership_id = intranet_membership.id
            WHERE intranet_membership_attached_object.object_id IN (".implode(',', $tagIds).")
            GROUP BY intranet_membership.id";
            $o_query = $o_main->db->query($s_sql);
            $list = $o_query ? $o_query->result_array() : array();

            $dateMarker = date("Y-m-d");
            foreach($list as $membership) {
                $contactPersons = fw_getConnectedContactPersons($membership);
                foreach($contactPersons as $contactPerson){
                    if(trim($contactPerson['email']) != ""){
                        $hasActiveValidSubscription = fw_checkIfContactPersonHasValidActiveSubscription($contactPerson, $dateMarker);
                        if($hasActiveValidSubscription){
                            array_push($usernames, ($contactPerson['email']));
                        }
                    }
                }
            }
        }

        return $usernames;
    }
}
if(!function_exists("fw_getUsernamesConnectedToGroups")){
    function fw_getUsernamesConnectedToGroups($groupIds){
    global $o_main;
    $usernames = array();

    if(count($groupIds) > 0){
        $s_sql = "SELECT intranet_membership.id FROM intranet_membership
        JOIN intranet_membership_attached_object ON intranet_membership_attached_object.membership_id = intranet_membership.id
        WHERE intranet_membership_attached_object.objectgroup_id IN (".implode(',', $groupIds).")
        GROUP BY intranet_membership.id";

        $o_query = $o_main->db->query($s_sql);
        $list = $o_query ? $o_query->result_array() : array();
        $dateMarker = date("Y-m-d");
        foreach($list as $membership) {
            $contactPersons = fw_getConnectedContactPersons($membership);
            foreach($contactPersons as $contactPerson){
                if(trim($contactPerson['email']) != ""){
                    $hasActiveValidSubscription = fw_checkIfContactPersonHasValidActiveSubscription($contactPerson, $dateMarker);
                    if($hasActiveValidSubscription){
                        array_push($usernames, ($contactPerson['email']));
                    }
                }
            }
        }
    }
    return $usernames;
}
}
?>
