<?php

$s_sql = "SELECT p.*, c.name as creditorName, c2.name as debitorName, concat_ws(' ', cp.name, cp.middlename, cp.lastname) as responsibleName, cp.email as responsibleEmail,
        ci.invoice_number
         FROM collecting_cases p
         LEFT JOIN creditor cred ON cred.id = p.creditor_id
         LEFT JOIN customer c ON cred.customer_id = c.id
         LEFT JOIN customer c2 ON c2.id = p.debitor_id
         LEFT JOIN creditor_invoice ci ON ci.collecting_case_id = p.id
         LEFT OUTER JOIN collecting_cases_objection obj ON obj.collecting_case_id = p.id
         AND (obj.objection_closed_date = '0000-00-00' or obj.objection_closed_date is null)
         LEFT OUTER JOIN contactperson cp ON cp.id = obj.responsible_person_id
        WHERE p.content_status < 2 AND (p.status = 0 or p.status is null )
        AND obj.id is not null AND (obj.objection_closed_date = '0000-00-00' OR obj.objection_closed_date is null)";

$o_query = $o_main->db->query($s_sql);
$allCases = ($o_query ? $o_query->result_array() : array());
$casesWithObjections = array();
foreach($allCases as $case){
    $casesWithObjections[$case['creditor_id']][] = $case;
}

$s_sql = "SELECT * FROM creditor";
$o_query = $o_main->db->query($s_sql, array($customer_id));
$creditors = ($o_query ? $o_query->result_array() : array());

$v_return['status'] = 1;
$v_return['casesWithObjections'] = $casesWithObjections;
$v_return['creditors'] = $creditors;

?>
