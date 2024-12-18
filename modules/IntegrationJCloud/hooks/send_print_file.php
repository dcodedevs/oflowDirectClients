<?php
$run_hook = function($data) {
    global $o_main;
    global $variables;

    // Params
    $letter_id = $data['letter_id'];
    $ownercompany_id = $data['ownercompany_id'];

    require_once __DIR__ . '/../internal_api/load.php';
    $api = new IntegrationJCloud(array(
        'o_main' => $o_main,
        'ownercompany_id' => $ownercompany_id
    ));

    $return = array();

    // Process customer data
    $customer_data_processed = array();


    $s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($letter_id));
    $collecting_cases_claim_letter = $o_query ? $o_query->row_array() : array();

    $file_path = $collecting_cases_claim_letter['pdf'];
    if(file_exists(__DIR__."/../../../".$file_path)) {
		include(__DIR__."/../../CollectingCases/output/languagesOutput/no.php");
        $letter_pdf_data = file_get_contents(__DIR__."/../../../".$file_path);

		$s_sql = "SELECT * FROM collecting_cases WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($collecting_cases_claim_letter['case_id']));
		$case = $o_query ? $o_query->row_array() : array();

        $s_sql = "SELECT creditor.*, customer.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
        $o_query = $o_main->db->query($s_sql, array($case['creditor_id']));
        $creditor = ($o_query ? $o_query->row_array() : array());

        $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
        $o_query = $o_main->db->query($s_sql, array($case['debitor_id']));
        $debitor = ($o_query ? $o_query->row_array() : array());

		$s_sql = "SELECT * FROM creditor_transactions WHERE collectingcase_id = ?";
		$o_query = $o_main->db->query($s_sql, array($case['id']));
		$creditor_invoice = ($o_query ? $o_query->row_array() : array());

        $invoice = array();
        $invoice['id'] = $collecting_cases_claim_letter['id'];
        $invoice['type'] = "reminder";
        $invoice['bank_account'] = $creditor['bank_account'];
        $invoice['bank_iban'] = "";
        $invoice['bank_bic'] = "";
        $invoice['payment_id'] = $creditor_invoice['kid_number'];
        $invoice['currency'] = "NOK";
        $invoice['date_issue'] = date("c", strtotime($collecting_cases_claim_letter['created']));
        $invoice['date_due'] = date("c", strtotime($collecting_cases_claim_letter['due_date']));
        $invoice['amount'] = $collecting_cases_claim_letter['total_amount'];
        $invoice['amount_base'] = $collecting_cases_claim_letter['total_amount'];
        $invoice['amount_vat'] = 0;

        $contacts = array();
        $sender = array();
        $receiver = array();
        $senderType = "company";
        $senderId = str_replace(" ", "",$creditor["publicRegisterId"]);
        if($creditor['customerType'] == 1){
            $senderType = "person";
            $senderId = str_replace(" ", "",$creditor["personnumber"]);
        }
        $sender['id'] = $senderId;
        $sender['type'] = $senderType;
        $sender['address_street'] = $creditor["paStreet"];
        $sender['address_place'] = $creditor["paCity"];
        $sender['address_zip'] = $creditor["paPostalNumber"];
        $sender['address_country'] = "Norway";
        $sender['reference'] = "";
        $sender['phone'] = $creditor["phone"];
        $sender['fax'] = "";
        $sender['email'] = $creditor["invoiceEmail"];
		// $sender['email'] = "david@dcode.no";
        $sender['name'] = $creditor["name"]." ".$creditor['middlename']." ".$creditor['lastname'];

        $receiverType = "company";
        $receiverId = str_replace(" ", "",$debitor["publicRegisterId"]);
        if($debitor['customerType'] == 1){
            $receiverType = "person";
            $receiverId = str_replace(" ", "",$debitor["personnumber"]);
        }

        $receiver['id'] = $receiverId;
        $receiver['type'] = $receiverType;
        $receiver['address_street'] = $debitor["paStreet"];
        $receiver['address_place'] = $debitor["paCity"];
        $receiver['address_zip'] = $debitor["paPostalNumber"];
        $receiver['address_country'] = "Norway";
        $receiver['reference'] = "";
        $receiver['phone'] = $debitor["phone"];
        $receiver['fax'] = "";
        $receiver['email'] = $debitor["invoiceEmail"];
		// $receiver['email'] = "byamba@dcode.no";
        $receiver['name'] = $debitor["name"]." ".$debitor['middlename']." ".$debitor['lastname'];

        $contacts['sender'] = $sender;
        $contacts['receiver'] = $receiver;
        $invoice['contacts'] = $contacts;

        $invoice_lines = array();

		$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='InvoiceCustomer' AND link_id = ? AND creditor_id = ? AND (collectingcase_id is null OR collectingcase_id = 0) AND comment LIKE '%_%' ORDER BY created DESC";
		$o_query = $o_main->db->query($s_sql, array($creditor_invoice['link_id'], $creditor_invoice['creditor_id']));
		$claim_transactions = ($o_query ? $o_query->result_array() : array());

		if($creditor_invoice){
			$topBordered = true;
			$claimAmount = number_format($creditor_invoice['collecting_case_original_claim'], 2, ".", "");
			$invoiceDueText = "";
			if($creditor_invoice['due_date'] != "0000-00-00" && $creditor_invoice['due_date'] != ""){
				$dueDate = date("d.m.Y", strtotime($creditor_invoice['due_date']));
				$invoiceDueText = " - ".$formText_DueDate_output." ".$dueDate;
			}
			$invoice_line = array();
			$invoice_line['name'] = $formText_InvoiceNumber_output." ".$invoice['invoice_nr'].$invoiceDueText;
			$invoice_line['quantity'] = 1.0;
			$invoice_line['amount'] = $claimAmount;
			$invoice_line['amount_base'] = $claimAmount;
			$invoice_line['amount_vat'] = 0.0;
			$invoice_line['vat_percent'] = 0.0;
			$invoice_lines[] = $invoice_line;

			$s_sql = "SELECT * FROM creditor_transactions WHERE open = 1 AND system_type='Payment' AND link_id = ? AND creditor_id = ? ORDER BY created DESC";
			$o_query = $o_main->db->query($s_sql, array($creditor_invoice['link_id'], $creditor_invoice['creditor_id']));
			$payments = ($o_query ? $o_query->result_array() : array());

			foreach($payments as $payment){
				$invoice_line = array();
				$invoice_line['name'] = $formText_Payment_output." ".date("d.m.Y", strtotime($payment['date']));
				$invoice_line['quantity'] = 1.0;
				$invoice_line['amount'] = $payment['amount'];
				$invoice_line['amount_base'] = $payment['amount'];
				$invoice_line['amount_vat'] = 0.0;
				$invoice_line['vat_percent'] = 0.0;
				$invoice_lines[] = $invoice_line;
			}
		}
		foreach($claim_transactions as $claim_transaction) {
			$claim_text_array = explode("_", $claim_transaction['comment']);
			$claimAmount = number_format($claim_transaction['amount'], 2, ".", "");
			$invoice_line['name'] = $claim_text_array[0];
			$invoice_line['quantity'] = 1.0;
			$invoice_line['amount'] = $claimAmount;
			$invoice_line['amount_base'] = $claimAmount;
			$invoice_line['amount_vat'] = 0.0;
			$invoice_line['vat_percent'] = 0.0;
			$invoice_lines[] = $invoice_line;
		}
        $invoice['invoice_lines'] = $invoice_lines;

        $attachments = array();
        $attachment = array();
        $attachment['type'] = "";
        $attachment['contenttype'] = "application/pdf";
        $attachment['data'] = base64_encode($letter_pdf_data);

        $attachments[] = $attachment;
        $invoice['attachments'] = $attachments;

        // $customer_data_processed['methods']=array("");
        $customer_data_processed['invoice'] = $invoice;
		$customer_data_processed['uid'] = $collecting_cases_claim_letter['id'];
		$customer_data_processed['callbackurl'] = "https://s30.getynet.com/accounts/oflowDirectClients/modules/IntegrationJCloud/output/handler.php";
		// $return['sent_data'] = $customer_data_processed;
        if(!$data['data_prep_only']) {
            $customer_result = $api->send_reminder($customer_data_processed);
            $return['result'] = $customer_result;
        }
    } else {
        $return['error'] = 'missing file';
    }
    return $return;
}
?>
