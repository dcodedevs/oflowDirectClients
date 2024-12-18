<?php
$fieldname = $_POST['field_name'];
$comparing_select = $_POST['comparing_select'];

$spliter = $_POST['spliter'];
if($spliter == "t"){
    $spliter = "\t";
}
$rows = explode("\n", str_replace("\"", "", $_POST['csv']));
$header = $rows[0];
$headers = explode($spliter, $header);
unset($rows[0]);

for ( $i = 1; $i <= sizeof($rows); $i++ ) {
    if(strlen($rows[$i]) > 0){
        $rowValues = explode($spliter,$rows[$i]);
        for ( $j = 0; $j < sizeof($headers); $j++ ) {
            $csv[$i][trim($headers[$j])] = $rowValues[$j];
        }
    }
    //break;
}
$foundItems = 0;
$notFoundItems = 0;
foreach($csv as $row) {
    $comparing_field_value = trim($row[$fieldname]);

    if($comparing_select == 0) {
        $o_query = $o_main->db->query("SELECT customer.* FROM customer WHERE id = ?", array($comparing_field_value));
        $foundItem = $o_query ? $o_query->row_array() : array();
    } else if($comparing_select == 1){
        $ownercompanyIdPost = $_POST['ownercompany'];
        $o_query = $o_main->db->query("SELECT customer.* FROM customer JOIN customer_externalsystem_id ON customer_externalsystem_id.customer_id
            WHERE customer_externalsystem_id.external_id= ? AND customer_externalsystem_id.ownercompany_id = ?", array($comparing_field_value, $ownercompanyIdPost));
        $foundItem = $o_query ? $o_query->row_array() : array();
    } else if($comparing_select == 2){
        $o_query = $o_main->db->query("SELECT customer.* FROM customer WHERE publicRegisterId= ?", array($comparing_field_value));
        $foundItem = $o_query ? $o_query->row_array() : array();
    }

    if($foundItem) {
        $foundItems++;
    } else {
        $notFoundItems++;
    }
}
$fw_return_data = array($foundItems, $notFoundItems);

?>
