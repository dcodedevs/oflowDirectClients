<?php

function rewriteCustomerBasisconfig(){
    global $v_customer_accountconfig;
    global $customer_basisconfig;
    global $_GET;

    foreach($v_customer_accountconfig as $key=>$value){
        if($key == 'activateProjectConnection' || $key == 'connectOrderToProject' || $key == 'activeAccountingProjectOnOrder'
        || $key == 'activateDropdownToChooseCompanyOrPrivatePerson' || $key == 'defaultWhenAddingNewCustomer' || $key == 'activate_incl_tax_in_offer'
        || $key == 'activate_member_profile_link'){
            if($value > 0){
                $customer_basisconfig[$key] = ($value - 1);
            }
        } else {
            if(array_key_exists($key, $customer_basisconfig)){
                if($value == 1){
                    $customer_basisconfig[$key] = 1;
                }
                if($value == 2){
                    $customer_basisconfig[$key] = 0;
                }
            }
        }
    }
    //expand content, 1-customer details, 2-selfdefined, 3-contact persons, 4-cleaning workers, 5-comments, 6-article matrix, 7-project with invoicing, 8- project without invoicing, 9-orders, 10-invoices, 11-invoicedOrders, 12-collapseSubscriptions, 13 - prospects
    if(isset($_GET['expandContent'])){
        $expandArray = explode(",", $_GET['expandContent']);
        foreach($expandArray as $expandItemNr){
            switch($expandItemNr){
                case 1:
                    $customer_basisconfig['collapseCustomerDetails'] = false;
                break;
                case 2:
                    $customer_basisconfig['expandSelfdefinedFields'] = true;
                break;
                case 3:
                    $customer_basisconfig['collapseContactpersons'] = false;
                break;
                case 4:
                    $customer_basisconfig['expandCleaningWorkers'] = true;
                break;
                case 5:
                    $customer_basisconfig['expandComments'] = true;
                break;
                case 6:
                    $customer_basisconfig['expandArticleMatrix'] = true;
                break;
                case 7:
                    $customer_basisconfig['expandProjectWithInvoicing'] = true;
                break;
                case 71:
                    $customer_basisconfig['expandProjectWithInvoicingCanceled'] = true;
                break;
                case 72:
                    $customer_basisconfig['expandProjectWithInvoicingFinished'] = true;
                break;
                case 73:
                    $customer_basisconfig['expandProjectWithInvoicingFinishedInvoiced'] = true;
                break;
                case 8:
                    $customer_basisconfig['expandProjectWithoutInvoicing'] = true;
                break;
                case 81:
                    $customer_basisconfig['expandProjectWithoutInvoicingCanceled'] = true;
                break;
                case 82:
                    $customer_basisconfig['expandProjectWithoutInvoicingFinished'] = true;
                break;
                case 9:
                    $customer_basisconfig['collapseOrders'] = false;
                break;
                case 10:
                    $customer_basisconfig['expandInvoices'] = true;
                break;
                case 11:
                    $customer_basisconfig['expandInvoicedOrders'] = true;
                break;
                case 12:
                    $customer_basisconfig['collapseSubscriptions'] = false;
                break;
                case 13:
                    $customer_basisconfig['expandProspects'] = true;
                break;
                case 14:
                    $customer_basisconfig['expandFiles'] = true;
                break;
            }
        }
    }
}

?>
