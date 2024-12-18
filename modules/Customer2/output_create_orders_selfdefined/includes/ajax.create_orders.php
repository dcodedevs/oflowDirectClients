<?php
$updatedSuccessfully = 0;
$customers = $_POST['customer'];
$display_option = intval($_POST['display_option']);
$selfdefined_field_id= $_POST['selfdefined_field_id'];
$ownercompany_id = $_POST['ownercompany'];
$article_id = $_POST['article_id'];
$article_text = $_POST['article_name'];
$selfdefined_value_type= $_POST['selfdefined_value_type'];
$selfdefined_price_per_piece = str_replace(" ", "", str_replace(",", ".", $_POST['selfdefined_price_per_piece']));

$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ? ORDER BY customer_selfdefined_fields.sortnr";
$o_query = $o_main->db->query($s_sql, array($selfdefined_field_id));
$selected_selfdefinedField =$o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM article WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($article_id));
$article =$o_query ? $o_query->row_array() : array();
if($article_text != ""){
    $article['name'] = $article_text;
}
if($article && $selected_selfdefinedField && $ownercompany_id && (($selfdefined_value_type == 1 && $selfdefined_price_per_piece > 0) || $selfdefined_value_type == 0) ) {
    foreach($customers as $customerId) {
        $s_sql = "SELECT c.*, csv.value as pricePerPiece
             FROM customer c
             LEFT OUTER JOIN customer_selfdefined_values csv ON csv.customer_id = c.id
             WHERE csv.selfdefined_fields_id = ? AND (csv.value is not null AND csv.value <> 0) AND csv.active = 1
             AND c.id = ?
             GROUP BY c.id
             ORDER BY c.name ASC";
        $o_query = $o_main->db->query($s_sql, array($selected_selfdefinedField['id'], $customerId));
        $customer_input = $o_query ? $o_query->row_array() : array();

        $subscriptionlines = array();
        $subscriptionline = array();

		if($selfdefined_value_type == 0){
			$subscriptionline['pricePerPiece'] = $customer_input['pricePerPiece'];
			$subscriptionline['amount'] = 1;
		} else if($selfdefined_value_type == 1){
			$subscriptionline['pricePerPiece'] = $selfdefined_price_per_piece;
			$subscriptionline['amount'] = $customer_input['pricePerPiece'];
		}

        $subscriptionline['articleNumber'] = $article['id'];
        $subscriptionline['discountPercent'] = $article['discountPercent'];
        $subscriptionline['articleName'] = $article['name'];
        $subscriptionlines[] = $subscriptionline;
        $lineUpdated = 0;

        $seperatedInvoice = 0;
        if($v_row['reference'] != "" || $v_row['delivery_address_line_1'] != "" || ($v_row['delivery_date'] != "0000-00-00" && $v_row['delivery_date'] != "")) {
            $seperatedInvoice = 1;
        }
        $sql = "INSERT INTO customer_collectingorder SET
        created = now(),
        createdBy='".$variables->loggID."',
        date = NOW(),
        customerId = '".$customerId."',
        accountingProjectCode = '".$projectId."',
        contactpersonId = '".$contactPersonId."',
        ownercompanyId = ?,
        seperateInvoiceFromSubscription = 0,
        reference = '',
        delivery_address_line_1 = '',
        delivery_address_line_2 = '',
        delivery_address_city = '',
        delivery_address_postal_code ='',
        delivery_address_country = '',
        seperatedInvoice = '',
        department_for_accounting_code = '".$departmentCode."',
        approvedForBatchinvoicing = 1";
        $o_query = $o_main->db->query($sql, array($ownercompany_id));
        if($o_query){
            $updatedSuccessfully++;
            $collectingorderId = $o_main->db->insert_id();

            foreach($subscriptionlines as $line){
                $subscribtionId = 0;
                $not_full_order = 0;
                $pricePerPiece = $line['pricePerPiece'];
                $totalAmount = $line['amount'];
                //update bookingaccount and vatcode
                $s_sql = "SELECT * FROM article WHERE id = ?";
                $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
                $articleSingle = $o_query ? $o_query->row_array() : array();

                $vatCode = $articleSingle['VatCodeWithVat'];
                $bookaccountNr = $articleSingle['SalesAccountWithVat'];

                if($vatCode == ""){
                    $vatCode = $article_accountconfig['defaultVatCodeForArticle'];
                }
                if($bookaccountNr == ""){
                    $bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
                }
                $vatPercent = '';

                $vatPercent = 0;
                $periodization = 0;
                $periodizationMonths = "";

                // $date_string = "".date("m.Y");

                $subscriptionNameString = "";//$selected_selfdefinedField['name']." - ";
                $articleName = $subscriptionNameString.$line['articleName'].$date_string;

                $sql = $o_main->db->query("INSERT INTO orders SET moduleID = ?, createdBy = ?, created = NOW(), customerID = ?,  articleNumber = ?, articleName = ?, amount = ?, pricePerPiece = ?, discountPercent = ?, priceTotal = ?,  status = 4,  invoiceDateSettingFromSubscription = ?, subscribtionId = ?,  contactPerson = ?, projectCode = ?, bookaccountNr = ?, vatCode = ?, vatPercent = ?, collectingorderId = ?", array(0, $variables->loggID, $customerId, $line['articleNumber'], $articleName, $totalAmount, $pricePerPiece, $line['discountPercent'], round($pricePerPiece * $totalAmount * (1 - $line['discountPercent']/100), 2), $invoiceDateSettingFromSubscription, $subscribtionId, $contactPersonId, $projectId, $bookaccountNr, $vatCode, $vatPercent, $collectingorderId));
            }
        } else {
            $fw_error_msg[] = $formText_ErrorUpdatingSubscription_output." ".$subscription;
        }

    }
}
echo $updatedSuccessfully;
?>
