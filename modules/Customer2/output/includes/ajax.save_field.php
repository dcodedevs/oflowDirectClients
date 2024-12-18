<?php
$editableFields = array(
    "publicRegisterId"=> array("label"=>$formText_PublicRegisterNumer_output, "width"=>"200"),
    "phone"=>array("label"=>$formText_Phone_output, "width"=>"200"),
    "email"=>array("label"=>$formText_Email_output, "width"=>"200"),
    "credittimeDays"=>array("label"=>$formText_CreditTimeDays_output, "width"=>"200"),
    "invoiceEmail"=>array("label"=>$formText_InvoiceEmail_output, "width"=>"200"),
    "homepage"=>array("label"=>$formText_Homepage_output, "width"=>"200"),
    "paStreet"=>array("label"=>$formText_Street_output, "width"=>"200"),
    "paStreet2"=>array("label"=>$formText_Street2_output, "width"=>"200"),
    "paPostalNumber"=>array("label"=>$formText_PostalNumber_output, "width"=>"200"),
    "paCity"=>array("label"=>$formText_City_output, "width"=>"200"),
    "paCountry"=>array("label"=>$formText_Country_output, "width"=>"200"),
    "vaStreet"=>array("label"=>$formText_VisitingStreet_output, "width"=>"200"),
    "vaStreet2"=>array("label"=>$formText_VisitingStreet2_output, "width"=>"200"),
    "vaPostalNumber"=>array("label"=>$formText_VisitingPostalNumber_output, "width"=>"200"),
    "vaCity"=>array("label"=>$formText_VisitingCity_output, "width"=>"200"),
    "vaCountry"=>array("label"=>$formText_VisitingCountry_output, "width"=>"200")
);

$field_to_edit = $_POST['field'];
$field_value = $_POST['value'];
$customer_id = $_POST['customer_id'];
if($field_to_edit != "" && $customer_id > 0){
    if(array_key_exists($field_to_edit, $editableFields)) {

        $s_sql = "UPDATE customer SET updated=NOW(), updatedBy = ?, ".$field_to_edit." = ? WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($variables->loggID, $field_value, $customer_id));
        if($o_query){
			$o_query = $o_main->db->query("SELECT customer.* FROM customer WHERE id = ?", array($customer_id));
			$foundItem = $o_query ? $o_query->row_array() : array();
            $fw_return_data = $foundItem[$field_to_edit];
        } else {
            $fw_error_msg = $formText_ErrorUpdatingDatabase_output;
        }
    } else {
        $fw_error_msg = $formText_WrongField_output;
    }
} else {
    $fw_error_msg = $formText_MissingFields_output;
}
?>
