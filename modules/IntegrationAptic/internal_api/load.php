<?php
require_once __DIR__ . '/ApiCallDriver.php';

class IntegrationAptic {
    function __construct($config) {
        $this->o_main = $config['o_main'];
        $this->api = new ApiCallDriver('https://c005013.test.hosting.aptic.cloud/C005013-15530-T01/APIGW');
        $this->auth_token = $this->create_auth_token();
        $this->api->set_auth_token($this->auth_token);
    }

    function create_auth_token() {        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://c005013.test.hosting.aptic.cloud/C005013-15530-T01/APIGW/login/oauth/access_token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT=> 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_USERPWD => "david@dcode.no:Skarsnuten194!",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=david@dcode.no&client_secret=Skarsnuten194!",
            CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded",
            ),
        ));
        $response = curl_exec($curl);
        $response_decoded = json_decode(trim($response), true);
        return $response_decoded['access_token'];
    }

    function get_current_user(){
        $result = $this->api->get('/v1/CurrentUser');
        return $result;
    }

    
    function get_debtors(){
        $result = $this->api->get('/v1/Debtors');
        return $result;
    }
    function get_debtor($guid){
        $result = $this->api->get('/v1/Debtors/'.$guid);
        return $result;
    }
    function update_debtor($debtor_data) {
        $data = array();
        // $data['debtorReference'] = '';      
        $data['idNumber'] = $debtor_data['publicRegisterId'];    
        // $data['juridicalType'] = '';        
        // $data['placeOfBirth'] = '';   
        // $data['dateOfBirth'] = '';  
        // $data['gender'] = '';   
        // $data['cultureCode'] = '';   
        // $data['fullName'] = $debtor_data['name']; 
        $data['firstname'] = $debtor_data['name'];  
        $data['middlename'] = $debtor_data['middlename'];  
        $data['lastname'] = $debtor_data['lastname'];  
        // $data['nameSuffix'] = '';  
        // $data['street'] = '';   
        // $data['houseNo'] = '';   
        // $data['houseNoExtension'] = '';   
        // $data['city'] = '';    
        // $data['zipCode'] = '';
        // $data['stateCode'] = '';
        // $data['countryCode'] = '';
        // $data['fullAddress'] = '';          
        // $data['underAged'] = '';
        // $data['domicile'] = '';
        // $data['municipalCode'] = '';
        // $data['vatNumber'] = '';
        // $data['naceCode'] = '';
        // $data['homePhone'] = '';
        $data['workPhone'] = $debtor_data['phone'];
        $data['cellularPhone'] = $debtor_data['mobile'];
        // $data['telefax'] = '';

        $data['email'] =  $debtor_data['invoiceEmail'];

        // $data['languageCode'] = '';
        // $data['currencyCode'] = '';
        // $data['conversationNotificationChannel'] = '';
        // $data['conversationNotificationEmail'] = '';
        // $data['conversationNotificationCellularPhone'] = '';
        


        if($debtor_data['aptic_debtor_id'] != "") {
            $result = $this->api->patch('/v1/Debtors/'.$debtor_data['aptic_debtor_id'], $data);
            if($result) {
                $result = array();
                $result['debtorGuid'] = $debtor_data['aptic_debtor_id'];
            }
        } else {
            $result = $this->api->put('/v1/Debtors', $data);
        }
        return $result;
    }

    function get_clients(){
        $result = $this->api->get('/v1/Clients');
        return $result;
    }
    function get_client($guid){
        $result = $this->api->get('/v1/Clients/'.$guid);
        return $result;
    }
    function update_client($creditor_data) {
        $data = array();
        // $data['referenceCode'] = '';        
        // $data['parentPartyUniqueId'] = '';        
        // $data['ledgerType'] = '';   
        // $data['informalName'] = '';  
        // $data['contactPerson'] = ''; 
        $data['idNumber'] = $creditor_data['id'];  
        $data['fullName'] = $creditor_data['companyname'];   
        // $data['street'] = '';   
        // $data['houseNo'] = '';   
        // $data['houseNoExtension'] = '';   
        // $data['city'] = '';    
        // $data['zipCode'] = '';
        // $data['stateCode'] = '';
        // $data['countryCode'] = '';
        $data['companyPhone'] = $creditor_data['companyphone'];
        // $data['cellularPhone'] = '';

        $data['email'] =  $creditor_data['companyEmail'];
        $data['active'] = true;
        // $data['webPage'] = '';
        // $data['vatNumber'] = '';
        // $data['vatRate'] = '';
        // $data['municipalCode'] = '';
        // $data['naceCode'] = '';
        // $data['leiNumber'] = '';
        // $data['glnNumber'] = '';

        // $data['registeredForTAX'] = '';
        // $data['languageCode'] = '';
        // $data['currencyCode'] = '';
        // $data['blocked'] = '';
        // $data['companyType'] = '';
        // $data['defaultCultureCode'] = '';
        // $data['countryCodeOfOperation'] = '';
        // $data['logotype'] = '';
        // $data['webLogotype'] = '';
        // $data['pricelistTemplate'] = '';
        // $data['parameterTemplate'] = '';
        // $data['chartOfAccountTemplate'] = '';


        if($creditor_data['aptic_client_id'] != ""){
            $result = $this->api->patch('/v1/Clients/'.$creditor_data['aptic_client_id'], $data);
            if($result) {
                $result = array();
                $result['uniqueId'] = $creditor_data['aptic_client_id'];
            }
        } else {
            $result = $this->api->put('/v1/Clients', $data);
        }
        return $result;
    }
    function get_cases(){
        $result = $this->api->get('/v1/Cases');
        return $result;
    }
    function get_case($guid){
        $result = $this->api->get('/v1/Cases/'.$guid);
        return $result;
    }
    function update_case($case_datas) {
        $data_array = array();
        $data = array();
                
        // $data['clientReferenceNumber'] = '';  
        // $data['currencyCode'] = '';   
        // $data['receivedFrom'] = '';   
        // $data['referenceNumber'] = '';   
        // $data['deliverySystemCode'] = '';    
        // $data['description'] = '';
        // $data['paymentReferenceNumber'] = '';
        // $data['originalClaimantName'] = '';
        // $data['service'] = '';
        // $data['claimType'] = '';

        // $data['legalClaimType'] = '';
        // $data['protected'] = '';
        // $data['stopPayments'] = '';
        // $data['prohibitTrustAccounting'] = '';

        // $data['reportPaidInterest'] = '';
        // $data['reportPaidInterestFrom'] = '';
        // $data['feeBasePrincipalAmount'] = '';
        // $data['highCostCredit'] = '';

        // $data['highCostCreditAmount'] = '';
        // $data['highCostCreditUsedAmount'] = '';
        // $data['creditRegistryStatus'] = '';

        // $data['creditRegistrySubmissionDate'] = '';
        // $data['debts'] = '';
        // $data['fees'] = '';
        // $data['claimant'] = '';
        // $data['energyDebt'] = '';
        // $data['relations'] = '';
        // $data['actionDates'] = '';
        // $data['extraFields'] = '';

        if($case_data['aptic_sys_id'] != ""){
            $result = $this->api->patch('/v1/Cases/'.$case_data['aptic_sys_id'], $data);
            if($result) {
                $result = array();
                $result['caseGuids'][] = $case_data['aptic_sys_id'];
            }
        } else {
            foreach($case_datas as $case_data){
                $data['caseNumber'] = $case_data['id'];
                $data['clientGuid'] = $case_data['aptic_client_id'];  
                $data['debtors'] = $case_data['aptic_debtors'];
                $data_array[] = $data;
            }
            $result = $this->api->put('/v1/Cases', $data_array);
        }
        return $result;
    }
    function update_case_debt($debt_data) {
        $data = array();
        // $data['description'] = '';        
        // $data['referenceNumber'] = '';        
        // $data['ledgerRef'] = '';   
        // $data['debtGroup'] = '';   
        // $data['ourReference'] = '';   
        // $data['yourReference'] = '';   
        // $data['currencyCode'] = '';    
        // $data['originalPrincipal'] = '';
        // $data['remainingPrincipal'] = '';
        // $data['originalInterest'] = '';
        // $data['remainingInterest'] = '';
        // $data['originalVAT'] = '';

        // $data['remainingVAT'] = '';
        // $data['exchRate'] = '';
        // $data['exchDate'] = '';
        // $data['billDate'] = '';

        // $data['dueDate'] = '';
        // $data['paymentCondition'] = '';
        // $data['interestCondition'] = '';
        // $data['interestRate'] = '';

        // $data['interestCode'] = '';
        // $data['interestSpecificCalculationType'] = '';
        // $data['interestSpecificCalculationCutoffDate'] = '';

        // $data['fixedInterestTermStart'] = '';
        // $data['interestFrom'] = '';
        // $data['interestTo'] = '';
        // $data['accountRefType'] = '';
        // $data['oneTimeServiceFees'] = '';
        // $data['loanPurpose'] = '';
        // $data['loanTypeCode'] = '';
        // $data['agreementDate'] = '';
        // $data['agreementDueDate'] = '';
        // $data['agreementEndDate'] = '';
        // $data['enforceableDebtType'] = '';
        // $data['goodsRelatedCredit'] = '';
        // $data['effectiveYear'] = '';
        // $data['debtPrincipalType'] = '';
        // $data['grantedCreditAmount'] = '';
        // $data['usedCreditAmount'] = '';
        // $data['originalEir'] = '';
        // $data['lenderName'] = '';
        // $data['periodOfLimitation'] = '';
        // $data['limitationDate'] = '';
        // $data['extraFields'] = '';

        if($debt_data['aptic_debt_id'] != "") {
            $result = $this->api->patch('/v1/Cases/'.$debt_data['aptic_case_id']."/Debts/".$debt_data['aptic_debt_id'], $data);
        } else {
            $result = $this->api->put('/v1/Cases'.$debt_data['aptic_case_id']."/Debts", $data);
        }
        return $result;
    }
    

    function get_customers(){
        $result = $this->api->get('/v1/Customers');
        return $result;
    }
    function get_customer($guid){
        $result = $this->api->get('/v1/Customers/'.$guid);
        return $result;
    }
    function update_customer($creditor_data) {
        $data = array();
        // $data['clientReference'] = '';        
        // $data['customerReference'] = '';        
        // $data['parentCustomer'] = '';   
        $data['idNumber'] = $creditor_data['id'];   
        $data['juridicalType'] = 1;   
        // $data['placeOfBirth'] = '';   
        // $data['dateOfBirth'] = '';   
        // $data['gender'] = '';   
        // $data['cultureCode'] = '';    
        // $data['firstname'] = '';
        // $data['middlename'] = '';
        // $data['lastname'] = '';
        // $data['nameSuffix'] = '';
        // $data['street'] = '';
        // $data['houseNo'] = '';
        // $data['houseNoExtension'] = '';
        // $data['city'] = '';
        // $data['zipCode'] = '';
        // $data['stateCode'] = '';
        // $data['countryCode'] = '';
        // $data['fullAddress'] = '';
        // $data['domicile'] = '';
        // $data['municipalCode'] = '';
        // $data['vatNumber'] = '';
        // $data['companyType'] = '';
        // $data['glnNumber'] = '';
        // $data['naceCode'] = '';
        // $data['homePhone'] = '';
        $data['workPhone'] = $creditor_data['companyphone'];
        // $data['cellularPhone'] = '';
        // $data['telefax'] = '';
        $data['email'] = $creditor_data['companyEmail'];
        // $data['languageCode'] = '';
        // $data['currencyCode'] = '';
        // $data['preferredChannel'] = '';
        // $data['underAged'] = '';
        // $data['invoiceChannel'] = '';
        if($creditor_data['aptic_customer_id'] != ""){
            $result = $this->api->patch('/v1/Customers/'.$creditor_data['aptic_customer_id'], $data);
        } else {
            $data['fullName'] = $creditor_data['companyname'];  
            $result = $this->api->put('/v1/Customers', $data);
        }
        return $result;
    }

   
}

?>
