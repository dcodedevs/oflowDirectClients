<?php
function filter_email_by_domain($email){
    global $o_main;

    $sql = "SELECT * FROM people_accountconfig";
    $o_query = $o_main->db->query($sql);
    $people_accountconfig = $o_query ? $o_query->row_array() : array();
    if(trim($people_accountconfig['show_only_emails_with_domain']) != ""){
        $domains = explode(",", str_replace(" ", "", $people_accountconfig['show_only_emails_with_domain']));

        if(count($domains) > 0) {
            $domain = trim(substr($email, strrpos($email, '@') + 1));
            if(!in_array($domain, $domains)){
                $email = "";
            }
        }
    }

    return $email;
}
?>
