<?php
if(isset($_POST['customerId'])){ $customerId = $_POST['customerId']; } else { $customerId = 0; }
//$customerId = $_POST['customerId'] ? ($_POST['customerId']) : 0;


$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = array();
if($o_query && $o_query->num_rows()>0) {
    $v_customer_accountconfig = $o_query->row_array();
}

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();


if($customerId) {
    $s_sql = "SELECT * FROM customer WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    if($o_query && $o_query->num_rows()>0) {
        $customerData = $o_query->row_array();
    }
}
if($moduleAccesslevel > 10 && isset($_POST['action']) && $_POST['action'] == "deleteCustomer" && $customerId) {
    $canDelete = true;

    $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
    LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
    WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND (customer_collectingorder.projectId = 0 OR customer_collectingorder.projectId is null)  GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $activeOrders = ($o_query ? $o_query->result_array() : array());
    if(count($activeOrders) > 0) {
        $canDelete = false;
    }
    $currentDate = date("Y-m-d", time());
    $s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = ? AND content_status = 0 AND (stoppedDate = '0000-00-00' OR stoppedDate IS NULL OR stoppedDate > ".$o_main->db->escape($currentDate).")
    ORDER BY subscriptionName ASC";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $activeSubscriptions = ($o_query ? $o_query->result_array() : array());
    if(count($activeSubscriptions) > 0){
        $canDelete = false;
    }

    $s_sql = "SELECT p.*, c.name  as customerName
    FROM project2 p
    LEFT JOIN customer c ON c.id = p.customerId
    WHERE c.id is not null AND c.id = ? AND (p.projectLeaderStatus = 0 OR p.projectLeaderStatus is null)  GROUP BY p.id ORDER BY p.id DESC";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $activeProjects2 = ($o_query ? $o_query->result_array() : array());
    if(count($activeProjects2) > 0){
        $canDelete = false;
    }
    $s_sql = "SELECT p.*, c.name  as customerName
         FROM project p
     LEFT JOIN customer c ON c.id = p.customerId
    WHERE c.id is not null AND c.id = ? AND (p.parentId = 0 OR p.parentId is null) AND (p.status = 0 OR p.status is null)
    GROUP BY p.id ORDER BY p.id DESC";
    $o_query = $o_main->db->query($s_sql, array($customerId));
    $activeProjects = ($o_query ? $o_query->result_array() : array());
    if(count($activeProjects) > 0){
        $canDelete = false;
    }

    if($canDelete){
        $s_sql = "UPDATE customer SET
        updated = now(),
        updatedBy=?,
        content_status = 2
        WHERE id = ?";
        $o_query = $o_main->db->query($s_sql, array($variables->loggID, $customerId));
        return;
    } else {
        ?>
        <div class="popupform">
            <div class="popupformTitle"><?php echo $formText_CanNotDeleteCustomer_output;?></div>
            <div class="inner">
                <?php

                if(count($activeOrders) > 0){
                    echo $formText_CustomerHasActiveOrders_output."</br>";
                }
                if(count($activeSubscriptions) > 0){
                    echo $formText_CustomerHasActiveSubscriptions_output."</br>";
                }
                if(count($activeProjects) > 0){
                    echo $formText_CustomerHasActiveProjects_output."</br>";
                }
                if(count($activeProjects2) > 0){
                    echo $formText_CustomerHasActiveProjects_output."</br>";
                }
                ?>
            </div>
        </div>
        <?php

        $fw_return_data = "warning";
        return;
    }

}
if($moduleAccesslevel > 10 && $_POST['action'] == "activateCustomer" && $customerId) {
    $s_sql = "UPDATE customer SET
    updated = now(),
    updatedBy=?,
    content_status = 0
    WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $customerId));
    return;
}

if($moduleAccesslevel > 10 && $_POST['action'] == "handleSelfregistered" && $customerId) {
    $s_sql = "UPDATE customer SET
    updated = now(),
    updatedBy=?,
    selfregistered = ?
    WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['selfregistered'], $customerId));
    return;
}
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$$v_invalid_emails = array();
		$_POST['invoiceEmail'] = str_replace(array(';', '/', '', chr(0xC2).chr(0xA0)), array(',', ',', '', ''), $_POST['invoiceEmail']);
		if(!empty($_POST['invoiceEmail']))
		{
			$v_emails = explode(",", $_POST['invoiceEmail']);
			$v_emails = array_map('trim', $v_emails);
			foreach($v_emails as $s_email)
			{
				if(FALSE === filter_var($s_email, FILTER_VALIDATE_EMAIL))
				{
					$v_invalid_emails[] = $s_email;
				}
			}
		}
		if(0 < count($v_invalid_emails))
		{
			$fw_error_msg['error_'.count($fw_error_msg)] = $formText_YouHaveSpecifiedInvalidEmailAddresses_Output.': '.implode(', ', $v_invalid_emails);
		}
		if(0 < count($fw_error_msg))
		{
			return;
		}

        $birthdate = "0000-00-00";
        if($_POST['birthdate'] != ""){
            $birthdate = date("Y-m-d", strtotime($_POST['birthdate']));
        }
        if($_POST['customerType'] == 0) {
            $_POST['middlename'] = "";
            $_POST['lastname'] = "";
        }
		if(!$customer_basisconfig['activate_shop_name'])
		{
			$_POST['shop_name'] = "";
		}
        $_POST['publicRegisterId'] = str_replace(" ", "", $_POST['publicRegisterId']);
        $sql_update = "";
        if($_POST['creditorId'] > 0){
            $s_sql = "SELECT * FROM customer WHERE creditor_id = ? AND creditor_customer_id = ?";
            $o_query = $o_main->db->query($s_sql, array($_POST['creditorId'], $_POST['creditor_customer_id']));
            $customerIdInUse = $o_query ? $o_query->row_array() : array();
            if($customerIdInUse){
                $fw_error_msg[] = $formText_CustomerIdIsAlreadyInUse_output;
                return;
            }
            $sql_update .= ", creditor_id = '".$o_main->db->escape_str($_POST['creditorId'])."',
            creditor_customer_id = '".$o_main->db->escape_str($_POST['creditor_customer_id'])."'";
        } else {
            if($v_customer_accountconfig['activateFieldCreditorId']) {
                $sql_update .= ", creditor_id = '".$o_main->db->escape_str($_POST['creditor_id'])."',
                creditor_customer_id = '".$o_main->db->escape_str($_POST['creditor_customer_id'])."'";
            }
        }
		$sql_update .= ", customer_type_collect = '".$o_main->db->escape_str($_POST['customer_type_collect'])."'";
        if ($customerId) {
            $cantChange = false;
            if($customerId) {
                $cantChange = true;
            }
            if($customer_basisconfig['activateChangeCustomerTypeAfterCreated']) {
                $cantChange = false;
            }
            if($cantChange){
               $_POST['customerType'] = $customerData['customerType'];
            }

            $s_sql = "UPDATE customer SET
            updated = now(),
            updatedBy=?,
            publicRegisterId= ?,
            name= ?,
            middlename= ?,
            lastname= ?,
            phone= ?,
            mobile = ?,
            paStreet= ?,
            paStreet2= ?,
            paPostalNumber=?,
            paCity=?,
            paCountry=?,
            vaStreet=?,
            vaStreet2=?,
            vaPostalNumber=?,
            vaCity=?,
            vaCountry=?,
            invoiceBy=?,
            invoiceEmail=?,
            credittimeDays=?,
            textVisibleInMyProfile=?,
            overrideAdminFeeDefault=?,
            customerType = ?,
            birthdate = ?,
            personnumber = ?,
            notOverwriteByImport = ?,
            email = ?,
            homepage = ?,
            useOwnInvoiceAdress = ?,
            iaStreet1= ?,
            iaStreet2= ?,
            iaPostalNumber=?,
            iaCity=?,
            iaCountry=?,
			accounting_project_number = ?,
			shop_name= ?,
            responsible_person_id= ?,
            do_not_check_for_ehf = ?,
            contact_type = ?,
            defaultInvoiceReference = ?".$sql_update."
            WHERE id = ?";

            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['publicRegisterId'], $_POST['name'],$_POST['middlename'],$_POST['lastname'], $_POST['phone'], $_POST['mobile'], $_POST['paStreet'], $_POST['paStreet2'],
             $_POST['paPostalNumber'], $_POST['paCity'], $_POST['paCountry'], $_POST['vaStreet'], $_POST['vaStreet2'], $_POST['vaPostalNumber'], $_POST['vaCity'], $_POST['vaCountry'], $_POST['invoiceBy'], $_POST['invoiceEmail'],
              $_POST['credittimeDays'], $_POST['textVisibleInMyProfile'], $_POST['overrideAdminFeeDefault'], $_POST['customerType'], $birthdate, $_POST['personnumber'],$_POST['notOverwriteByImport'], $_POST['email'],
               $_POST['homepage'], $_POST['useOwnInvoiceAdress'], $_POST['iaStreet1'], $_POST['iaStreet2'], $_POST['iaPostalNumber'], $_POST['iaCity'], $_POST['iaCountry'], $_POST['accounting_project_number'], $_POST['shop_name'], $_POST['employeeId'], $_POST['do_not_check_for_ehf'], $_POST['contact_type'],$_POST['defaultInvoiceReference'], $customerId));

            if($o_query)
			{
                $fw_return_data = $_POST['name'];
				$fw_redirect_url = $_POST['redirect_url'];
				$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE customer_id = '".$o_main->db->escape_str($customerId)."'");
				if($o_query && $o_query->num_rows() > 0)
				{
					$s_name = trim(preg_replace('/\s+/', ' ', $_POST['name'].' '.$_POST['middlename'].' '.$_POST['lastname']));
					$o_query = $o_main->db->query("UPDATE projectforaccounting SET name = '".$o_main->db->escape_str($s_name)."' WHERE customer_id = '".$o_main->db->escape_str($customerId)."'");
				}
			} else {
				die('sql_error');
			}
        }
        else {
			$l_projectforaccounting_id = '';
			if($v_customer_accountconfig['activate_add_accounting_project_number_when_new_customer'] && 1 == $v_customer_accountconfig['activate_add_accounting_project_number_when_new_customer'])
			{
				$s_name = trim(preg_replace('/\s+/', ' ', $_POST['name'].' '.$_POST['middlename'].' '.$_POST['lastname']));
				$o_query = $o_main->db->query("INSERT INTO projectforaccounting SET id = NULL, moduleID = '".$o_main->db->escape_str($moduleID)."', createdBy = '".$o_main->db->escape_str($variables->loggID)."', created = NOW(), projectnumber = id, name = '".$o_main->db->escape_str($s_name)."', ownercompany_id = '".$o_main->db->escape_str($v_customer_accountconfig['accounting_project_number_ownercompany_id'])."', parentId = '', parentNumber = ''");
				$l_projectforaccounting_id = $o_main->db->insert_id();
				$o_query = $o_main->db->query("UPDATE projectforaccounting SET projectnumber = '".$o_main->db->escape_str($l_projectforaccounting_id)."' WHERE id = '".$o_main->db->escape_str($l_projectforaccounting_id)."'");
			}

	        if($_POST['creditorId'] > 0){
				$sql_update .= ", extraName = '".$o_main->db->escape_str($_POST['name'])."', extraPublicRegisterId = '".$o_main->db->escape_str($_POST['publicRegisterId'])."',
				extraStreet = '".$o_main->db->escape_str($_POST['paStreet'])."',
				extraStreet2 = '".$o_main->db->escape_str($_POST['paStreet2'])."',
				extraPostalNumber = '".$o_main->db->escape_str($_POST['paPostalNumber'])."',
				extraCity = '".$o_main->db->escape_str($_POST['paCity'])."',
				extraCountry = '".$o_main->db->escape_str($_POST['paCountry'])."',
				customer_type_for_collecting_cases =  '".$o_main->db->escape_str($_POST['customer_type_collect'] + 1)."'";
			}
            $s_sql = "INSERT INTO customer SET
            created = now(),
            createdBy= ?,
			publicRegisterId= ?,
            name= ?,
            middlename= ?,
            lastname= ?,
            phone= ?,
            mobile = ?,
            paStreet= ?,
            paStreet2= ?,
            paPostalNumber=?,
            paCity=?,
            paCountry=?,
            vaStreet=?,
            vaStreet2=?,
            vaPostalNumber=?,
            vaCity=?,
            vaCountry=?,
            invoiceBy=?,
            invoiceEmail=?,
            credittimeDays=?,
            textVisibleInMyProfile=?,
            overrideAdminFeeDefault=?,
            customerType = ?,
            birthdate = ?,
            personnumber = ?,
            notOverwriteByImport = ?,
            email = ?,
            homepage = ?,
            useOwnInvoiceAdress = ?,
            iaStreet1= ?,
            iaStreet2= ?,
            iaPostalNumber=?,
            iaCity=?,
            iaCountry=?,
			accounting_project_number = ?,
			shop_name= ?,
            responsible_person_id = ?,
            do_not_check_for_ehf = ?,
            contact_type = ?,
            defaultInvoiceReference = ?".$sql_update;

            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $_POST['publicRegisterId'], $_POST['name'],$_POST['middlename'],$_POST['lastname'], $_POST['phone'], $_POST['mobile'],$_POST['paStreet'], $_POST['paStreet2'],
            $_POST['paPostalNumber'], $_POST['paCity'], $_POST['paCountry'], $_POST['vaStreet'], $_POST['vaStreet2'], $_POST['vaPostalNumber'], $_POST['vaCity'], $_POST['vaCountry'], $_POST['invoiceBy'], $_POST['invoiceEmail'],
            $_POST['credittimeDays'], $_POST['textVisibleInMyProfile'], $_POST['overrideAdminFeeDefault'], $_POST['customerType'], $birthdate, $_POST['personnumber'],$_POST['notOverwriteByImport'],       $_POST['email'],
            $_POST['homepage'],  $_POST['useOwnInvoiceAdress'], $_POST['iaStreet1'], $_POST['iaStreet2'], $_POST['iaPostalNumber'], $_POST['iaCity'], $_POST['iaCountry'], $l_projectforaccounting_id, $_POST['shop_name'], $_POST['employeeId'], $_POST['do_not_check_for_ehf'], $_POST['contact_type'],$_POST['defaultInvoiceReference']));

			if($o_query)
			{
				$insert_id = $o_main->db->insert_id();
                $customerId = $insert_id;
				$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
                $fw_return_data = $_POST['name'];
				if(isset($v_customer_accountconfig['activate_create_folders_in_files_for_new_customer']) && 1 == $v_customer_accountconfig['activate_create_folders_in_files_for_new_customer'])
				{
					$v_folders = explode(";", $v_customer_accountconfig['specify_folders_in_files_for_new_customer']);
					foreach($v_folders as $s_folder)
					{
						$l_parent_id = 0;
						$v_items = explode("/", $s_folder);
						foreach($v_items as $s_item)
						{
							if('' == trim($s_item)) continue;

							$s_sql = "INSERT INTO customer_folders SET id=NULL, moduleID = '".$o_main->db->escape_str($moduleID)."', created = NOW(), createdBy= '".$o_main->db->escape_str($variables->loggID)."', name = '".$o_main->db->escape_str($s_item)."', parent_id = '".$o_main->db->escape_str($l_parent_id)."', customer_id = '".$o_main->db->escape_str($customerId)."'";
							$o_main->db->query($s_sql);
							$l_parent_id = $o_main->db->insert_id();
						}
					}
				}

			} else {
				die('sql_error');
			}
        }
        if($_POST['customerType'] == 1){
            $s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND privatePersonCustomer = 1";
            $o_query = $o_main->db->query($s_sql, array($customerId));
            $contactpersonPrivate = $o_query ? $o_query->row_array() : array();
            if($contactpersonPrivate) {
                $o_query = $o_main->db->query("UPDATE contactperson SET updated = NOW(), updatedBy = ?, name = ?,
                middlename= ?,
                lastname= ? WHERE id = ?", array($variables->loggID, $_POST['name'],$_POST['middlename'],$_POST['lastname'], $contactpersonPrivate['id']));
                if($o_query) {
                } else {
                    die('sql_error');
                }
            } else {
                $o_query = $o_main->db->query("INSERT INTO contactperson SET created = NOW(), createdBy = ?, name = ?,
                middlename= ?,
                lastname= ?, customerId = ?, privatePersonCustomer = 1", array($variables->loggID, $_POST['name'],$_POST['middlename'],$_POST['lastname'], $customerId));
                if($o_query) {
                } else {
                    die('sql_error');
                }
            }
        }
	}
}

if(isset($_POST['brreg_orgnr']))
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
	$v_post = array(
		'organisation_no' => $_POST['brreg_orgnr'],
		'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
		'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
	);

	curl_setopt($ch, CURLOPT_POSTFIELDS, $v_post);
	$s_response = curl_exec($ch);
	
	$v_items = array();
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status']) && 1 == $v_response['status'] && 1 == count($v_response['items']))
	{
		$v_row = $v_response['items'][0];
		$customerData = array(
			'publicRegisterId' => $v_row['orgnr'],
			'name' => $v_row['navn'],
			'paStreet' => ($v_row['postadresse'] != '' ? $v_row['postadresse'] : $v_row['forretningsadr']),
			'paPostalNumber' => ($v_row['postadresse'] != '' ? $v_row['ppostnr'] : $v_row['forradrpostnr']),
			'paCity' => ($v_row['postadresse'] != '' ? $v_row['ppoststed'] : $v_row['forradrpoststed']),
			'paCountry' => ($v_row['postadresse'] != '' ? $v_row['ppostland'] : $v_row['forradrland']),
			'vaStreet' => $v_row['forretningsadr'],
			'vaPostalNumber' => $v_row['forradrpostnr'],
			'vaCity' => $v_row['forradrpoststed'],
			'vaCountry' => $v_row['forradrland']
		);
	}
}

$defaultCreditDays = 14;
if($v_customer_accountconfig['activateDefaultCreditDays'] && $v_customer_accountconfig['defaultCreditDays'] > 0) {
    $defaultCreditDays = intval($v_customer_accountconfig['defaultCreditDays']);
}

$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['owner']));

if($o_query && $o_query->num_rows()>0) {
    $ownerCompany = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}
if(!isset($customerData)) {
    $customerData['customerType'] = intval($customer_basisconfig['defaultWhenAddingNewCustomer']);
}

$s_sql = "select * from contactperson WHERE id = '".$o_main->db->escape_str($customerData['responsible_person_id'])."'";
$o_query = $o_main->db->query($s_sql);
$repeatingOrderWorklineWorker= ($o_query ? $o_query->row_array() : array());

if($_POST['newCustomer'] && $customer_basisconfig['activateDropdownToChooseCompanyOrPrivatePerson']){
    ?>
    <div class="popupform">
        <div id="popup-validate-message" style="display:none;"></div>
        <div class="inner">
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CustomerType_Output; ?></div>
                <div class="lineInput">
                    <select name="customerType" class="customerType2">
                        <option value="0" <?php if(intval($customerData['customerType']) == 0) { echo 'selected'; }?>><?php echo $formText_Company_output;?></option>
                        <option value="1" <?php if(intval($customerData['customerType']) == 1) { echo 'selected'; }?>><?php echo $formText_PrivatePerson_output;?></option>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line description">
                <?php echo $formText_CompaniesThatRegisteredInPublicRegisterCanBeSearched_output;?></br></br>
                <?php echo $formText_SearchAndFetchFromPublicRegisterHere_output;?>:</br>
            </div>
            <div class="line relative">
                <input type="text" class="popupforminput botspace search_bregg" name="search_bregg" value="<?php echo $customerData['creditor_customer_id']; ?>" autocomplete="off" required>
                <input type="button" value="<?php echo $formText_Search_output;?>" class="search_button"/>
            </div>
            <div class="line">
                <div class="addCustomerManually"><?php echo $formText_AddCustomerManually_output;?></div>
            </div>
            <div class="search_result"></div>
            <style>

            .relative {
                position: relative;
            }
            .search_bregg {
            }
            .search_button {
                position: absolute;
                top: 0;
                right: 0;
                text-align: center;
                font-size: 15px;
                background: #0095E4;
                color: #FFF;
                padding: 3px 15px;
                border-radius: 3px;
                border: none;
                margin: 0 0 0 0px;
            }
            </style>
            <script type="text/javascript">
            $(".addCustomerManually").off("click").on("click", function(e){
                e.preventDefault();
                var data = {
                    customerId: 0
                };
                ajaxCall('editCustomerDetail', data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            $(".search_button").off("click").on("click", function(e){
                var search_value = $(".search_bregg").val();
                if(search_value != "") {
                    var data = {
                        search_brreg: search_value,
                        page: 0,
                        search_from_popup: 1
                    }
                    ajaxCall('list_brreg', data, function(json) {
                        $(".search_result").html(json.html);
                    	$(".brreg-page-link").on('click', function(e) {
                    	    e.preventDefault();
                    		page = $(this).data("page");
                    		$(this).data("page", (page+1));
                    	    var data = {
                    	        search_brreg: search_value,
                    	        page: page,
                                search_from_popup: 1
                    	    };
                    	    ajaxCall('list_brreg', data, function(json) {
                    	        $('.search_result .gtable.brreg').append(json.html);
                                $(window).resize();
                    	        if(json.html.replace(" ", "") == ""){
                    	            $(".search_result .brreg-page-link").hide();
                    	        }
                	            $(".search_result .brreg-showing").html($(".search_result .gtable_row_bregg").length);
                    	    });
                        });
                        $(".gtable_row_bregg").on("click", function(){
                            out_popup.close();
                        })
                        $(window).resize();
                    });
                }
            })
            $(".customerType2").change(function(e){
                e.preventDefault();
                var data = {
                    customerId: 0,
                    customerTypeSelected: 1
                };
                ajaxCall('editCustomerDetail', data, function(json) {
                    $('#popupeditboxcontent').html('');
                    $('#popupeditboxcontent').html(json.html);
                    out_popup = $('#popupeditbox').bPopup(out_popup_options);
                    $("#popupeditbox:not(.opened)").remove();
                });
            })
            </script>
            <div class="popupformbtn">
                <button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="popupform">
    	<div id="popup-validate-message" style="display:none;"></div>
    	<form class="output-form output-worker-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCustomerDetail";?>" method="post">
    		<input type="hidden" name="fwajax" value="1">
    		<input type="hidden" name="fw_nocss" value="1">
    		<input type="hidden" name="output_form_submit" value="1">
    		<input type="hidden" name="customerId" value="<?php echo $customerId;?>">
            <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerId; ?>">
    		<div class="inner">
                <?php
                if($customer_basisconfig['activate_contact_type']) {
                    ?>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_ContactType_Output; ?></div>
                        <div class="lineInput">
                            <input type="radio" <?php if($customerData['contact_type'] == 0) { echo 'checked'; }?> class="popupforminput botspace radioInput" name="contact_type" value="0" autocomplete="off" id="customer_contact" required> <label for="customer_contact"><?php echo $formText_Customer_output;?></label>
                            <input type="radio" <?php if($customerData['contact_type'] == 1) { echo 'checked'; }?> class="popupforminput botspace radioInput" name="contact_type" value="1" autocomplete="off" id="supplier_contact" required> <label for="supplier_contact"><?php echo $formText_Supplier_output;?></label>
                            <input type="radio" <?php if($customerData['contact_type'] == 2) { echo 'checked'; }?> class="popupforminput botspace radioInput" name="contact_type" value="1" autocomplete="off" id="both_contact" required> <label for="both_contact"><?php echo $formText_Both_output;?></label>

                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                }
                if($_POST['creditorId'] > 0){
                    ?>
                    <input type="hidden" name="creditorId" value="<?php print $_POST['creditorId'];?>" required>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_CreditorCustomerId_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="creditor_customer_id" value="<?php echo $customerData['creditor_customer_id']; ?>" autocomplete="off" required>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                } else {
                    if($v_customer_accountconfig['activateFieldCreditorId']) {
                        $s_sql = "SELECT customer.*, creditor.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
                        $o_query = $o_main->db->query($s_sql, array($customerData['creditor_id']));
                        $creditor = ($o_query ? $o_query->row_array() : array());
                        ?>
                        <div class="line creditorWrapper">
            				<div class="lineTitle"><?php echo $formText_Creditor_Output; ?></div>
            				<div class="lineInput">
            					<?php if($creditor) { ?>
            					<a href="#" class="selectCreditor"><?php echo $creditor['name']?></a>
            					<?php } else { ?>
            					<a href="#" class="selectCreditor"><?php echo $formText_SelectCreditor_Output;?></a>
            					<?php } ?>
            					<input type="hidden" name="creditor_id" id="creditorId" value="<?php print $creditor['id'];?>" required>
            				</div>
            				<div class="clear"></div>
            			</div>
                        <div class="line">
                            <div class="lineTitle"><?php echo $formText_CreditorCustomerId_Output; ?></div>
                            <div class="lineInput">
                                <input type="text" class="popupforminput botspace" name="creditor_customer_id" value="<?php echo $customerData['creditor_customer_id']; ?>" autocomplete="off" required>
                            </div>
                            <div class="clear"></div>
                        </div>
                    <?php }
                }
                if($customer_basisconfig['activateDropdownToChooseCompanyOrPrivatePerson'] > 0) {
                    $cantChange = false;
                    if($customerId) {
                        $cantChange = true;
                    }
                    if($customer_basisconfig['activateChangeCustomerTypeAfterCreated']) {
                        $cantChange = false;
                    }
                    ?>
                    <?php if($customer_basisconfig['activateDropdownToChooseCompanyOrPrivatePerson'] == 1 && !$cantChange) { ?>
                        <div class="line">
                            <div class="lineTitle"><?php echo $formText_CustomerType_Output; ?></div>
                            <div class="lineInput">
                                <select name="customerType" class="customerType">
                                    <option value="0" <?php if(intval($customerData['customerType']) == 0) { echo 'selected'; }?>><?php echo $formText_Company_output;?></option>
                                    <option value="1" <?php if($_POST['customerTypeSelected'] == 1) { echo 'selected'; } else { if(intval($customerData['customerType']) == 1) { echo 'selected'; } }?>><?php echo $formText_PrivatePerson_output;?></option>
                                </select>
                            </div>
                            <div class="clear"></div>
                        </div>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateDropdownToChooseCompanyOrPrivatePerson'] == 2 || $cantChange) { ?>
                        <div class="line">
                            <div class="lineTitle"><?php echo $formText_CustomerType_Output; ?></div>
                            <div class="lineInput">
                                <?php
                                if(intval($customerData['customerType']) == 0) {
                                    echo $formText_Company_output;
                                }
                                if(intval($customerData['customerType']) == 1) {
                                    echo $formText_PrivatePerson_output;
                                }
                                ?>
                                <input type="hidden" class="popupforminput botspace customerType" name="customerType" value="<?php echo intval($customerData['customerType']); ?>">
                            </div>
                            <div class="clear"></div>
                        </div>
                    <?php } ?>
                <?php } ?>
                <?php
                if($v_customer_accountconfig['activate_customer_responsibleperson']) {
                    ?>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_CustomerResponsiblePerson_Output; ?></div>
                        <div class="lineInput">
                            <?php if($repeatingOrderWorklineWorker) { ?>
                            <a href="#" class="selectWorker"><?php echo $repeatingOrderWorklineWorker['name']." ".$repeatingOrderWorklineWorker['middlename']." ".$repeatingOrderWorklineWorker['lastname']?></a>
                            <?php } else { ?>
                            <a href="#" class="selectWorker"><?php echo $formText_SelectWorker_Output;?></a>
                            <?php } ?>
                            <input type="hidden" name="employeeId" id="employeeId" value="<?php print $repeatingOrderWorklineWorker['id'];?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php
                }
                ?>
                <div class="line companyField">
                    <div class="lineTitle"><?php echo $formText_PublicRegisterNumber_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace publicRegisterIdInput" name="publicRegisterId" value="<?php echo $customerData['publicRegisterId']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <?php if (!$customer_basisconfig['hideBirthdate']){?>
                <div class="line privatePersonField">
                    <div class="lineTitle"><?php echo $formText_Birthdate_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace datefield" name="birthdate" value="<?php if($customerData['birthdate'] != "0000-00-00" && $customerData['birthdate'] != null){ echo date("d.m.Y", strtotime($customerData['birthdate'])); } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <?php } ?>
                <?php if (!$customer_basisconfig['hidePersonNumber']){?>
                <div class="line privatePersonField">
                    <div class="lineTitle"><?php echo $formText_Personnumber_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="personnumber" value="<?php echo $customerData['personnumber']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <?php } ?>
        		<div class="line">
            		<div class="lineTitle">
                        <div class="privatePersonField"><?php echo $formText_FirstName_Output; ?></div>
                        <div class="companyField"><?php echo $formText_Name_Output; ?></div>
                    </div>
            		<div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="name" value="<?php echo $customerData['name']; ?>" required autocomplete="off">
                    </div>
            		<div class="clear"></div>
        		</div>
				<?php if($customer_basisconfig['activate_shop_name']){?>
				<div class="line">
            		<div class="lineTitle"><?php echo $formText_ShopName_Output; ?></div>
            		<div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="shop_name" value="<?php echo $customerData['shop_name']; ?>" autocomplete="off">
                    </div>
            		<div class="clear"></div>
        		</div>
				<?php } ?>
                <div class="line privatePersonField">
                    <div class="lineTitle"><?php echo $formText_MiddleName_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="middlename" value="<?php echo $customerData['middlename']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line privatePersonField">
                    <div class="lineTitle"><?php echo $formText_LastName_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="lastname" value="<?php echo $customerData['lastname']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line companyField">
                    <div class="lineTitle"><?php echo $formText_Phone_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="phone" value="<?php echo $customerData['phone']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <?php if($v_customer_accountconfig['activateMobileField']) { ?>
                    <div class="line companyField">
                        <div class="lineTitle"><?php echo $formText_Mobile_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="mobile" value="<?php echo $customerData['mobile']; ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_InvoiceBy_Output; ?></div>
                    <div class="lineInput inlineInput invoiceByWrapper">
                        <span class="ehf_checking">
                            <?php echo $formText_CheckingEhf_output;?> ...
                        </span>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line invoiceEmail">
                    <div class="lineTitle"><?php echo $formText_InvoiceAndReminderEmail_output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace invoiceEmailInput" name="invoiceEmail" value="<?php echo $customerData['invoiceEmail']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line ">
                    <div class="lineTitle"><?php echo $formText_DefaultInvoiceReference_output ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="defaultInvoiceReference" value="<?php echo $customerData['defaultInvoiceReference']; ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_CreditTimeDays_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="credittimeDays" value="<?php if($customerData['credittimeDays'] != ""){ echo $customerData['credittimeDays']; } else { echo $defaultCreditDays;}?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>

                <?php if($customer_basisconfig['display_field_text_on_mypage']) { ?>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_TextVisibleInMyProfile_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="textVisibleInMyProfile" value="<?php echo $customerData['textVisibleInMyProfile']; ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <?php
                $showAdminFee = false;
                if($ownercompany_accountconfig['addAdminFeeAutomatically'] > 0 ){
                    $showAdminFee = true;
                }
                if($showAdminFee){
                ?>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_AddAdminFee_Output; ?></div>
                        <div class="lineInput">
                            <select name="overrideAdminFeeDefault">
                                <option value="0" <?php if($customerData['overrideAdminFeeDefault'] == 0) echo 'selected';?>><?php echo $formText_Default_output;?>
                                <option value="1" <?php if($customerData['overrideAdminFeeDefault'] == 1) echo 'selected';?>><?php echo $formText_NeverCharge_output;?>
                                <option value="2" <?php if($customerData['overrideAdminFeeDefault'] == 2) echo 'selected';?>><?php echo $formText_ChargeAlways_output;?>
                                <option value="3" <?php if($customerData['overrideAdminFeeDefault'] == 3) echo 'selected';?>><?php echo $formText_ChargeIfPaper_output;?>
                            </select>
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>

                <div class="line">
                    <div class="lineTitle"><?php echo $formText_NotOverwriteByImport_Output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace" name="notOverwriteByImport" value="1" <?php if($customerData['notOverwriteByImport']) echo 'checked';?>>
                    </div>
                    <div class="clear"></div>
                </div>
                <?php
                 if($customer_basisconfig['activateCustomerEmailField']) { ?>
                     <div class="line">
                         <div class="lineTitle"><?php echo $formText_CustomerEmail_Output; ?></div>
                         <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="email" value="<?php echo $customerData['email']; ?>" autocomplete="off">
                        </div>
                         <div class="clear"></div>
                     </div>
                <?php } ?>
                <?php
                 if($customer_basisconfig['activateHomepageField']) { ?>
                     <div class="line">
                         <div class="lineTitle"><?php echo $formText_Homepage_Output; ?></div>
                         <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="homepage" value="<?php echo $customerData['homepage']; ?>" autocomplete="off">
                        </div>
                         <div class="clear"></div>
                     </div>
                <?php } ?>
				<?php if($v_customer_accountconfig['display_customer_accounting_project_number'] && 1 < $v_customer_accountconfig['display_customer_accounting_project_number']) { ?>
                     <div class="line">
                         <div class="lineTitle"><?php echo $formText_AccountingProject_Output; ?></div>
                         <div class="lineInput">
						 	<?php
							if(1 == intval($v_customer_accountconfig['activate_add_accounting_project_number_when_new_customer']) && !$customerData['id'])
							{
								echo $formText_ProjectCodeWillBeGeneratedAfterSaving_Output;
							} else {
								$s_sql = "SELECT * FROM projectforaccounting  WHERE projectforaccounting.projectnumber = ?";
								$o_query = $o_main->db->query($s_sql, array($customerData['accounting_project_number']));
								$accountingProject = ($o_query ? $o_query->row_array() : array());

								if($accountingProject) { ?>
								<a href="#" class="selectProject"><?php echo $accountingProject['name']?> (<?php echo $accountingProject['projectnumber']?>)</a>
								<?php } else { ?>
								<a href="#" class="selectProject"><?php echo $formText_SelectAccountingProject_Output;?></a>
								<?php } ?>
								<input type="hidden" name="accounting_project_number" id="projectCode" value="<?php print $accountingProject['projectnumber'];?>"<?php echo ((0 == intval($v_customer_accountconfig['activate_add_accounting_project_number_when_new_customer']) || $customerData['id'] > 0) ? ' required' : '');?>>
								<?php
								/*?>
								<select name="accounting_project_number"<?php echo ((0 == intval($v_customer_accountconfig['activate_add_accounting_project_number_when_new_customer']) || $customerData['id'] > 0) ? ' required' : '');?>>
								<option value=""><?php echo $formText_Choose_Output;?></option>
								<?php
								$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE ownercompany_id = '".$o_main->db->escape_str($v_customer_accountconfig['accounting_project_number_ownercompany_id'])."' ORDER BY name");
								if($o_query && $o_query->num_rows()>0)
								foreach($o_query->result_array() as $v_row)
								{
									?><option value="<?php echo $v_row['projectnumber'];?>"<?php echo ($v_row['projectnumber'] == $customerData['accounting_project_number'] ? ' selected' : '');?>><?php echo $v_row['projectnumber'].' '.$v_row['name'].'';?></option><?php
								}
								?>
								</select>
								<?php*/
							}
							?>
                        </div>
                         <div class="clear"></div>
                     </div>
                <?php } ?>

                <div class="line">
                    <div class="lineTitle"><?php echo $formText_UseOwnInvoiceAddress_output; ?></div>
                    <div class="lineInput">
                        <input type="checkbox" class="checkbox popupforminput botspace useOwnInvoice" name="useOwnInvoiceAdress" value="1" <?php if($customerData['useOwnInvoiceAdress']) echo 'checked';?>>
                    </div>
                    <div class="clear"></div>
                </div>
				<div class="line">
                    <div class="lineTitle"><?php echo $formText_CrmCustomerType_output; ?></div>
                    <div class="lineInput">
						<select name="customer_type_collect" required>
							<option value="0" <?php if(intval($customerData['customer_type_collect']) == 0) { echo 'selected'; }?>><?php echo $formText_Company_output;?></option>
							<option value="1" <?php if(intval($customerData['customer_type_collect']) == 1) { echo 'selected'; }?>><?php echo $formText_PrivatePerson_output;?></option>
						</select>
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="invoiceAddress">
                    <div class="line">
                        <div class="lineTitle lineTitleWithSeperator"><?php echo $formText_InvoiceAddress_output; ?></div>
                        <div class="clear"></div>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="iaStreet1" value="<?php if(isset($customerData['iaStreet1'])){ echo $customerData['iaStreet1']; } ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_Street2_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="iaStreet2" value="<?php if(isset($customerData['iaStreet1'])){ echo $customerData['iaStreet2']; } ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_PostalNumber_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="iaPostalNumber" value="<?php if(isset($customerData['iaPostalNumber'])){ echo $customerData['iaPostalNumber']; } ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="iaCity" value="<?php if(isset($customerData['iaCity'])){ echo $customerData['iaCity']; } ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="line">
                        <div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
                        <div class="lineInput">
                            <input type="text" class="popupforminput botspace" name="iaCountry" value="<?php if(isset($customerData['iaCountry'])){ echo $customerData['iaCountry']; } ?>" autocomplete="off">
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>

                <div class="line">
                    <div class="lineTitle lineTitleWithSeperator"><?php echo $formText_PostalAddress_output; ?></div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="paStreet" value="<?php if(isset($customerData['paStreet'])){ echo $customerData['paStreet']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Street2_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="paStreet2" value="<?php if(isset($customerData['paStreet2'])){ echo $customerData['paStreet2']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_PostalNumber_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="paPostalNumber" value="<?php if(isset($customerData['paPostalNumber'])){ echo $customerData['paPostalNumber']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="paCity" value="<?php if(isset($customerData['paCity'])){ echo $customerData['paCity']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="paCountry" value="<?php if(isset($customerData['paCountry'])){ echo $customerData['paCountry']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="line">
                    <div class="lineTitle lineTitleWithSeperator"><?php echo $formText_VisitingAddress_output; ?></div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Street_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="vaStreet" value="<?php if(isset($customerData['vaStreet'])){ echo $customerData['vaStreet']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Street2_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="vaStreet2" value="<?php if(isset($customerData['vaStreet2'])){ echo $customerData['vaStreet2']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_PostalNumber_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="vaPostalNumber" value="<?php if(isset($customerData['vaPostalNumber'])){ echo $customerData['vaPostalNumber']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="vaCity" value="<?php if(isset($customerData['vaCity'])){ echo $customerData['vaCity']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Country_Output; ?></div>
                    <div class="lineInput">
                        <input type="text" class="popupforminput botspace" name="vaCountry" value="<?php if(isset($customerData['vaCountry'])){ echo $customerData['vaCountry']; } ?>" autocomplete="off">
                    </div>
                    <div class="clear"></div>
                </div>
    		</div>

    		<div class="popupformbtn">
    			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
    			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
    		</div>
    	</form>
    </div>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">

    $(document).ready(function() {
        // $('.output-form').on('submit', function(e) {
        //     e.preventDefault();
        //     var data = {};
        //     $(this).serializeArray().forEach(function(item, index) {
        //         data[item.name] = item.value;
        //     });
        //     ajaxCall('editCustomerDetail', data, function (json) {
        //         if (json.redirect_url) document.location.href = json.redirect_url;
        //         else out_popup.close();
        //         // console.log(json);
        //     });
        // });
        $(".useOwnInvoice").change(function(){
            if($(this).is(":checked")){
                $(".invoiceAddress").show();
            } else {
                $(".invoiceAddress").hide();
            }
        }).change();
        $("form.output-form").validate({
            ignore: [],
            submitHandler: function(form) {
                fw_loading_start();
                $.ajax({
                    url: $(form).attr("action"),
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: $(form).serialize(),
                    success: function (json) {
    					if(json.error !== undefined)
    					{
    						var _msg = '';
    						$.each(json.error, function(index, value){
    							var _type = Array("error");
    							if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
    							_msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
    						});
    						$("#popup-validate-message").html(_msg, true);
    						$("#popup-validate-message").show();
    					} else {
    						if(json.redirect_url !== undefined)
    						{
                                <?php if($_POST['creditorId'] > 0) {
                                    ?>
                        			var data = {
                        				creditor_id: '<?php echo $_POST['creditorId'];?>',
                                        search: json.data,
                                        debitor: 1,
                        			};
                        			ajaxCall({module_file:'get_customers', module_name: 'CollectingCases', module_folder: 'output'}, data, function(json) {
                                        $('#popupeditboxcontent2').html('');
                                        $('#popupeditboxcontent2').html(json.html);
                                        out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                                        $("#popupeditbox2:not(.opened)").remove();
                        			});
                                    <?php
                                } else { ?>
        							out_popup.addClass("close-reload").data("redirect", json.redirect_url);
        							out_popup.close();
                                <?php } ?>
    						}
    					}
    					fw_loading_end();
    				}
                }).fail(function() {
                    $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    fw_loading_end();
                });
            },
            invalidHandler: function(event, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var message = errors == 1
                    ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                    : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                    $("#popup-validate-message").html(message);
                    $("#popup-validate-message").show();
                    $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                } else {
                    $("#popup-validate-message").hide();
                }
                setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
            },
            errorPlacement: function(error, element) {
                if(element.attr("name") == "creditor_id") {
                    error.insertAfter(".popupform .selectCreditor");
                }
                if(element.attr("name") == "customer_id") {
                    error.insertAfter(".popupform .selectCustomer");
                }
				if(element.attr("name") == "projectCode") {
					error.insertAfter(".selectProject");
				}
            },
            messages: {
                creditor_id: "<?php echo $formText_SelectTheCreditor_output;?>",
                customer_id: "<?php echo $formText_SelectTheCustomer_output;?>",
            	projectCode: "<?php echo $formText_SelectProjectCode_output;?>",
            }
        });
        $('.datefield').datepicker({
            dateFormat: 'dd.mm.yy',
        })
        $(".customerType").change(function(){
            var type = $(this).val();
            if(type == 0) {
                $(".privatePersonField").hide();
                $(".companyField").show();
            } else {
                $(".privatePersonField").show();
                $(".companyField").hide();
            }
            $(window).resize();
        })
        $(".customerType").change();
        $(".selectWorker").unbind("click").bind("click", function(e){
        	e.preventDefault();
        	var _data = { fwajax: 1, fw_nocss: 1};
        	$.ajax({
        		cache: false,
        		type: 'POST',
        		dataType: 'json',
        		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
        		data: _data,
        		success: function(obj){
        			$('#popupeditboxcontent2').html('');
        			$('#popupeditboxcontent2').html(obj.html);
        			out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
        			$("#popupeditbox2:not(.opened)").remove();
        		}
        	});
        })
        $(".selectCreditor").unbind("click").bind("click", function(){
            fw_loading_start();
            var _data = { fwajax: 1, fw_nocss: 1, creditor: 1};
            $.ajax({
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_creditors";?>',
                data: _data,
                success: function(obj){
                    fw_loading_end();
                    $('#popupeditboxcontent2').html('');
                    $('#popupeditboxcontent2').html(obj.html);
                    out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                    $("#popupeditbox2:not(.opened)").remove();
                }
            });
        })
		$(".selectProject").unbind("click").bind("click", function(e){
			e.preventDefault();
			fw_loading_start();
			var _data = { fwajax: 1, fw_nocss: 1, ownercompany_id: '<?php echo $v_customer_accountconfig['accounting_project_number_ownercompany_id'];?>' };
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_accounting_projects";?>',
				data: _data,
				success: function(obj){
					$('#popupeditboxcontent2').html('');
					$('#popupeditboxcontent2').html(obj.html);
					out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
					$("#popupeditbox2:not(.opened)").remove();
					fw_loading_end();
				}
			});
		})
        $(".publicRegisterIdInput").on("keyup", function(){
            checkByPublicRegister();
        })
        checkByPublicRegister();
        function checkByPublicRegister(){
            <?php if(!$customerData['do_not_check_for_ehf']) { ?>
                var collectingorderId = $(this).data('collectingorder-id');
                $('.invoiceByWrapper').html('<?php echo addslashes($formText_CheckingEhf_output);?>');
        		ajaxCall({module_file:'check_ehf&abortable=1'}, { customer_id: '<?php echo $customerData['id'];?>', publicRegisterId: $(".publicRegisterIdInput").val(),  invoiceEmail: $(".invoiceEmailInput").val() }, function(json) {
        			if(json.data >= 1)
        			{
                        fillInInvoiceBy(json.data);
        			} else {
                        fillInInvoiceBy();
        			}
        		}, false);
            <?php } else { ?>
                fillInInvoiceBy();
            <?php } ?>
        }
        function fillInInvoiceBy(checkedValue){
            var checkedPaper = "<?php if($customerData['invoiceBy'] == 0) echo 'checked';?>";
            var checkedEmail = "<?php if($customerData['invoiceBy'] == 1) echo 'checked';?>";
            var checkedEf = "<?php if($customerData['invoiceBy'] == 2) echo 'checked';?>";
            var ehfResult = '';
            var ehfDisabled = ' disabled readonly';
            if(checkedValue == undefined){
                ehfResult = '<span style="color: red;"><?php echo addslashes($formText_CanNotReceiveEhf_output);?></span>';
            } else if(checkedValue == 1){
                checkedPaper = "checked";
                checkedEmail = "";
                checkedEf = "";
                ehfResult = '<span style="color: red;"><?php echo addslashes($formText_CanNotReceiveEhf_output);?></span>';
            } else if(checkedValue == 2){
                checkedPaper = "";
                checkedEmail = "checked";
                checkedEf = "";
                ehfResult = '<span style="color: red;"><?php echo addslashes($formText_CanNotReceiveEhf_output);?></span>';
            } else if(checkedValue == 3){
                checkedPaper = "";
                checkedEmail = "";
                checkedEf = "checked";
                ehfDisabled = '';
                ehfResult = '<span style="color: green;"><?php echo addslashes($formText_CanReceiveEhf_output);?></span>';
            }
            var label = '<?php echo addslashes($formText_DoNotCheckForEhf_output);?>';

            $('.invoiceByWrapper').html('<input id="invoiceByPaper" type="radio" class="popupforminput botspace" name="invoiceBy" value="0" '+checkedPaper+'>'+
            '<label for="invoiceByPaper"><?php echo $formText_Paper_output;?></label>&nbsp;&nbsp;&nbsp;&nbsp;'+
            '<input id="invoiceByEmail" type="radio" class="popupforminput botspace" name="invoiceBy" value="1" '+checkedEmail+'>'+
            '<label for="invoiceByEmail"><?php echo $formText_Email_output;?></label>&nbsp;&nbsp;&nbsp;&nbsp;'+
            '<span class="companyField">'+
                '<input id="invoiceByEhf" type="radio" class="popupforminput botspace" name="invoiceBy" value="2" '+checkedEf+' '+ehfDisabled+'>'+
                '<label for="invoiceByEhf"><?php echo $formText_Ehf_output;?></label>&nbsp;&nbsp;&nbsp;&nbsp;'+
            '</span>'+
            '<span class="ehf_result">'+ehfResult+'</span>'+
            '<span class="doNotCheckForEhfWrapper">'+
                '<input id="do_not_check_for_ehf" type="checkbox" class="popupforminput botspace" name="do_not_check_for_ehf" value="1" <?php if($customerData['do_not_check_for_ehf']) echo 'checked';?>>'+
                '<label for="do_not_check_for_ehf">'+label+'</label>'+
            '</span>');


            // $("#invoiceByPaper").unbind("click").bind("click", function(){
            //     $(".invoiceEmail").hide();
            //     $(".invoiceEmail input").prop("required", false);
            // })
            // $("#invoiceByEmail").unbind("click").bind("click", function(){
            //     $(".invoiceEmail").show();
            //     $(".invoiceEmail input").prop("required", true);
            // })
            // $("#invoiceByEhf").unbind("click").bind("click", function(){
            //     $(".invoiceEmail").hide();
            //     $(".invoiceEmail input").prop("required", false);
            // })
            $("input[name='invoiceBy']:checked").click();
        }
    });

    </script>
<?php } ?>
<style>
.popupform input.popupforminput.checkbox {
    width: auto;
}
.popupform input.popupforminput.radioInput {
    width: auto;
    margin-bottom: 5px;
}
.popupform .inlineInput input.popupforminput {
    display: inline-block;
    width: auto;
    vertical-align: middle;
    margin-right: 20px;
    margin-bottom: 0px;
    margin-top: 5px;
}
.popupform .inlineInput {
    margin-bottom: 10px;
}
.popupform .inlineInput label {
    margin-bottom: 0px;
}
.popupform .inlineInput label {
    display: inline-block !important;
    vertical-align: middle;
}
.popupform .invoiceByWrapper input {
    margin-left: 10px;
}
.popupeditbox .lineInput.invoiceByWrapper input[type="radio"] + label {
    margin-right: 0px;
}
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
    width:100%;
    margin:0 auto;
    border:1px solid #e8e8e8;
    position:relative;
}
/* .invoiceEmail {
    display: none;
} */
.selectDivModified {
    display:block;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
.privatePersonField {
	display: none;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
.addCustomerManually {
    cursor: pointer;
    color: #46b2e2;
}
.line.description {
    font-size: 15px;
}
.ehf_result {
    margin-right: 15px;
    vertical-align: middle;
}
.doNotCheckForEhfWrapper {
    display: block;
}
.popupeditbox .popupform .inlineInput input.popupforminput {
    margin-right: 5px;
    margin-left: 0px;
}
.doNotCheckForEhfWrapper label {
    font-weight: normal;
}
</style>
