<?php
	
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 120);
	function checkValue($value) {
		$value = trim($value);
		if (get_magic_quotes_gpc()) { $value = stripslashes($value); }
		$value = strtr($value,array_flip(get_html_translation_table(HTML_ENTITIES)));
		$value = htmlspecialchars($value);
		$value = mysql_real_escape_string($value);
		return $value;
	}
	if(isset($_POST['submitImportData'])) {		
		//customer
		$getCustomers = "SELECT * FROM customer_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM customer WHERE id = ".$oldCustomer['id'])) == 0){
				mysql_query("INSERT INTO customer SET id = '".$oldCustomer['id']."',
					moduleID = '41',
					createdBy = '".$oldCustomer['createdBy']."',
					created = '".$oldCustomer['created']."',
					updatedBy = '".$oldCustomer['updatedBy']."',
					updated = '".$oldCustomer['updated']."',
					sortnr = '".$oldCustomer['sortnr']."',
					content_status = '".$oldCustomer['content_status']."',
					publicRegisterId = '".$oldCustomer['publicRegisterId']."',
					name = '".$oldCustomer['name']."',
					paStreet = '".$oldCustomer['paStreet']."',
					paStreet2 = '".$oldCustomer['paStreet2']."',
					paPostalNumber = '".$oldCustomer['paPostalNumber']."',
					paCity = '".$oldCustomer['paCity']."',
					paCountry = '".$oldCustomer['paCountry']."',
					vaStreet = '".$oldCustomer['vaStreet']."',
					vaStreet2 = '".$oldCustomer['vaStreet2']."',
					vaPostalNumber = '".$oldCustomer['vaPostalNumber']."',
					vaCity = '".$oldCustomer['vaCity']."',
					vaCountry = '".$oldCustomer['vaCountry']."',
					phone = '".$oldCustomer['phone']."',
					mobile = '".$oldCustomer['mobile']."',
					fax = '".$oldCustomer['fax']."',
					email = '".$oldCustomer['email']."',
					homepage = '".$oldCustomer['homepage']."',
					comments = '".$oldCustomer['comments']."',
					companyType = '".$oldCustomer['companyType']."',
					industries = '".$oldCustomer['industries']."',
					financialYear = '".$oldCustomer['financialYear']."',
					revenue = '".$oldCustomer['revenue']."',
					municipalityName = '".$oldCustomer['municipalityName']."',
					consideredIrrelevant = '".$oldCustomer['consideredIrrelevant']."',
					publicRegisterContactperson = '".$oldCustomer['publicRegisterContactperson']."',
					publicRegisterContactpFunction = '".$oldCustomer['publicRegisterContactpFunction']."',
					revenueManuallyAdded = '".$oldCustomer['revenueManuallyAdded']."',
					revenueManuallyAddedYear = '".$oldCustomer['revenueManuallyAddedYear']."',
					numberOfEmplyees = '".$oldCustomer['numberOfEmplyees']."',
					notOverwriteByImport = '".$oldCustomer['notOverwriteByImport']."',
					invoiceBy = '".$oldCustomer['invoiceBy']."',
					invoiceEmail = '".$oldCustomer['invoiceEmail']."',
					taxFreeSale = '".$oldCustomer['taxFreeSale']."',
					iaStreet1 = '".$oldCustomer['iaStreet1']."',
					iaStreet2 = '".$oldCustomer['iaStreet2']."',
					iaPostalNumber = '".$oldCustomer['iaPostalNumber']."',
					iaCity = '".$oldCustomer['iaCity']."',
					iaCountry = '".$oldCustomer['iaCountry']."',
					useOwnInvoiceAdress = '".$oldCustomer['useOwnInvoiceAdress']."',
					credittimeDays = '".$oldCustomer['credittimeDays']."',
					textVisibleInMyProfile = '".$oldCustomer['textVisibleInMyProfile']."',
					numberOfUnits = '".$oldCustomer['numberOfUnits']."',
					associationId = '".$oldCustomer['associationId']."',
					housingcooperativeType = '".$oldCustomer['housingcooperativeType']."'");
			}
		}
		$getContactPersons = "SELECT * FROM contactperson_import ORDER BY id ASC";
		$findContactPersons = mysql_query($getContactPersons);
		while($contactPerson = mysql_fetch_array($findContactPersons)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM contactperson WHERE id = ".$contactPerson['id'])) == 0){
				mysql_query("INSERT INTO contactperson SET id = '".$contactPerson['id']."',
					moduleID = '41',
					createdBy = '".$contactPerson['createdBy']."',
					created = '".$contactPerson['created']."',
					updatedBy = '".$contactPerson['updatedBy']."',
					updated = '".$contactPerson['updated']."',
					sortnr = '".$contactPerson['sortnr']."',
					customerId = '".$contactPerson['customerId']."',
					name = '".$contactPerson['name']."',
					title = '".$contactPerson['title']."',
					directPhone = '".$contactPerson['directPhone']."',
					mobile = '".$contactPerson['mobile']."',
					displayInMemberpage = '".$contactPerson['displayInMemberpage']."',
					mainContact = '".$contactPerson['mainContact']."',
					wantToReceiveInfo = '".$contactPerson['wantToReceiveInfo']."',
					email = '".$contactPerson['email']."',
					inactive = '".$contactPerson['notActive']."',
					notes = '".$contactPerson['notes']."',
					content_status = '".$contactPerson['content_status']."'");
			}
		}
	}
	if(isset($_POST['submitImportData2'])) {		
		//customer
		$getCustomers = "SELECT * FROM customer_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM customer WHERE id = '".checkValue($oldCustomer['id'])."'")) == 0){
				mysql_query("INSERT INTO customer SET id = '".checkValue($oldCustomer['id'])."',
					moduleID = '41',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					publicRegisterId = '".checkValue($oldCustomer['publicRegisterId'])."',
					name = '".checkValue($oldCustomer['name'])."',
					paStreet = '".checkValue($oldCustomer['paStreet'])."',
					paStreet2 = '".checkValue($oldCustomer['paStreet2'])."',
					paPostalNumber = '".checkValue($oldCustomer['paPostalNumber'])."',
					paCity = '".checkValue($oldCustomer['paCity'])."',
					paCountry = '".checkValue($oldCustomer['paCountry'])."',
					vaStreet = '".checkValue($oldCustomer['vaStreet'])."',
					vaStreet2 = '".checkValue($oldCustomer['vaStreet2'])."',
					vaPostalNumber = '".checkValue($oldCustomer['vaPostalNumber'])."',
					vaCity = '".checkValue($oldCustomer['vaCity'])."',
					vaCountry = '".checkValue($oldCustomer['vaCountry'])."',
					phone = '".checkValue($oldCustomer['phone'])."',
					mobile = '".checkValue($oldCustomer['mobile'])."',
					fax = '".checkValue($oldCustomer['fax'])."',
					email = '".checkValue($oldCustomer['email'])."',
					homepage = '".checkValue($oldCustomer['homepage'])."',
					comments = '".checkValue($oldCustomer['comments'])."',
					companyType = '".checkValue($oldCustomer['companyType'])."',
					industries = '".checkValue($oldCustomer['industries'])."',
					industryCode = '".checkValue($oldCustomer['industryCode'])."',
					industryText = '".checkValue($oldCustomer['industryText'])."',
					creditApproved = '".checkValue($oldCustomer['creditApproved'])."',
					creditLimit = '".checkValue($oldCustomer['creditLimit'])."',
					financialYear = '".checkValue($oldCustomer['financialYear'])."',
					revenue = '".checkValue($oldCustomer['revenue'])."',
					municipalityName = '".checkValue($oldCustomer['municipalityName'])."',
					consideredIrrelevant = '".checkValue($oldCustomer['consideredIrrelevant'])."',
					publicRegisterContactperson = '".checkValue($oldCustomer['publicRegisterContactperson'])."',
					publicRegisterContactpFunction = '".checkValue($oldCustomer['publicRegisterContactpFunction'])."',
					revenueManuallyAdded = '".checkValue($oldCustomer['revenueManuallyAdded'])."',
					revenueManuallyAddedYear = '".checkValue($oldCustomer['revenueManuallyAddedYear'])."',
					numberOfEmplyees = '".checkValue($oldCustomer['numberOfEmplyees'])."',
					notOverwriteByImport = '".checkValue($oldCustomer['notOverwriteByImport'])."',
					invoiceBy = '".checkValue($oldCustomer['invoiceBy'])."',
					invoiceEmail = '".checkValue($oldCustomer['invoiceEmail'])."',
					taxFreeSale = '".checkValue($oldCustomer['taxFreeSale'])."',
					credittimeDays = '".checkValue($oldCustomer['credittimeDays'])."'");
			}
		}
		$getContactPersons = "SELECT * FROM contactperson_import ORDER BY id ASC";
		$findContactPersons = mysql_query($getContactPersons);
		while($contactPerson = mysql_fetch_array($findContactPersons)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM contactperson WHERE id = ".$contactPerson['id'])) == 0){
				mysql_query("INSERT INTO contactperson SET id = '".$contactPerson['id']."',
					moduleID = '41',
					createdBy = '".checkValue($contactPerson['createdBy'])."',
					created = '".checkValue($contactPerson['created'])."',
					updatedBy = '".checkValue($contactPerson['updatedBy'])."',
					updated = '".checkValue($contactPerson['updated'])."',
					sortnr = '".checkValue($contactPerson['sortnr'])."',
					customerId = '".checkValue($contactPerson['customerId'])."',
					name = '".checkValue($contactPerson['name'])."',
					title = '".checkValue($contactPerson['title'])."',
					directPhone = '".checkValue($contactPerson['directPhone'])."',
					mobile = '".checkValue($contactPerson['mobile'])."',
					email = '".checkValue($contactPerson['email'])."',
					content_status = '".checkValue($contactPerson['content_status'])."'");
			}
		}
	}
	if(isset($_POST['changeAssociation'])){
		$getContactPersons = "SELECT * FROM association ORDER BY id ASC";
		$findContactPersons = mysql_query($getContactPersons);
		while($contactPerson = mysql_fetch_array($findContactPersons)) {
			mysql_query("INSERT INTO customer_selfdefined_list_lines SET name = '".$contactPerson['name']."', list_id = 4");
		}
	}
	if(isset($_POST['importOrders'])) {
		//customer
		$getCustomers = "SELECT * FROM orders_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM orders WHERE id = '".checkValue($oldCustomer['id'])."'")) == 0){
				if($oldCustomer['moduleID'] == 45) {
					$moduleID = '66';
				} else if($oldCustomer['moduleID'] == 33) {
					$moduleID = '79';
				}else if($oldCustomer['moduleID'] == 29) {
					$moduleID = '78';
				} else if($oldCustomer['moduleID'] == 28){
					$moduleID = '65';
				} else {
					$moduleID = $oldCustomer['moduleID'];
				}
				mysql_query("INSERT INTO orders SET id = '".checkValue($oldCustomer['id'])."',
					moduleID = '".$moduleID."',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					Status = '".checkValue($oldCustomer['Status'])."',
					projectLeader = '".checkValue($oldCustomer['projectLeader'])."',
					customerID = '".checkValue($oldCustomer['customerID'])."',
					contactPerson = '".checkValue($oldCustomer['contactPerson'])."',
					articleNumber = '".checkValue($oldCustomer['articleNumber'])."',
					articleName = '".checkValue($oldCustomer['articleName'])."',
					describtion = '".checkValue($oldCustomer['describtion'])."',
					amount = '".checkValue($oldCustomer['amount'])."',
					pricePerPiece = '".checkValue($oldCustomer['pricePerPiece'])."',
					discountPercent = '".checkValue($oldCustomer['discountPercent'])."',
					priceTotal = '".checkValue($oldCustomer['priceTotal'])."',
					delieveryDate = '".checkValue($oldCustomer['delieveryDate'])."',
					expectedTimeuseMinutes = '".checkValue($oldCustomer['expectedTimeuseMinutes'])."',
					addOnInvoice = '".checkValue($oldCustomer['addOnInvoice'])."',
					monthsInvoicedFromStart = '".checkValue($oldCustomer['monthsInvoicedFromStart'])."',
					dateFrom = '".checkValue($oldCustomer['dateFrom'])."',
					dateTo = '".checkValue($oldCustomer['dateTo'])."',
					vatCode = '".checkValue($oldCustomer['vatCode'])."',
					vatPercent = '".checkValue($oldCustomer['vatPercent'])."',
					bookaccountNr = '".checkValue($oldCustomer['bookaccountNr'])."',
					gross = '".checkValue($oldCustomer['gross'])."',
					projectFAccNumber = '".checkValue($oldCustomer['projectFAccNumber'])."',
					invoiceNumber = '".checkValue($oldCustomer['invoiceNumber'])."',
					subscribtionId = '".checkValue($oldCustomer['subscribtionId'])."',
					currencyId = '".checkValue($oldCustomer['currencyId'])."',
					ownercompany_id = '1',
					projectId = '".checkValue($oldCustomer['projectId'])."',
					singleactivity = '".checkValue($oldCustomer['singleactivity'])."',
					productelement_id = '".checkValue($oldCustomer['productelement_id'])."',
					planningStatus = '".checkValue($oldCustomer['planningStatus'])."',
					orderdescdoccustomer = '".checkValue($oldCustomer['orderdescdoccustomer'])."',
					orderdescdocdeveloper = '".checkValue($oldCustomer['orderdescdocdeveloper'])."',
					projectchecklist = '".checkValue($oldCustomer['projectchecklist'])."',
					doneTime = '".checkValue($oldCustomer['doneTime'])."'");
			}
		}
	}
	if(isset($_POST['fixOrders'])) {
		//customer
		$getCustomers = "SELECT * FROM orders ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {	
			if($oldCustomer['projectLeader']!=""){		
				$responiblePerson = mysql_fetch_array(mysql_query("SELECT * FROM employee WHERE email = '".$oldCustomer['projectLeader']."'"));
				if($responiblePerson){
					$responiblePersonId = $responiblePerson['id'];
				} else {
					mysql_query("INSERT INTO employee SET moduleID = 73, created = NOW(), createdBy='import', name ='".$oldCustomer['projectLeader']."', email ='".$oldCustomer['projectLeader']."'");
					$responiblePersonId = mysql_insert_id();
				}
				mysql_query("UPDATE orders SET projectLeader = '".$responiblePersonId."' WHERE id = '".checkValue($oldCustomer['id'])."'");
			}

		}
	}
	if(isset($_POST['importActivity'])) {
		//customer
		$getCustomers = "SELECT * FROM activity_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM activity WHERE id = '".checkValue($oldCustomer['id'])."'")) == 0){
				$moduleID = '77';
				$responiblePerson = mysql_fetch_array(mysql_query("SELECT * FROM employee WHERE email = '".$oldCustomer['responiblePerson']."'"));
				if($responiblePerson){
					$responiblePersonId = $responiblePerson['id'];
				} else {
					mysql_query("INSERT INTO employee SET moduleID = 73, created = NOW(), createdBy='import', name ='".$oldCustomer['responiblePerson']."', email ='".$oldCustomer['responiblePerson']."'");
					$responiblePersonId = mysql_insert_id();
				}
				mysql_query("INSERT INTO activity SET id = '".checkValue($oldCustomer['id'])."',
					moduleID = '".$moduleID."',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					orderId = '".checkValue($oldCustomer['orderId'])."',
					status = '".checkValue($oldCustomer['status'])."',
					responiblePerson = '".checkValue($responiblePersonId)."',
					activityName = '".checkValue($oldCustomer['activityName'])."',
					describtion = '".checkValue($oldCustomer['describtion'])."',
					completedWithinDate = '".checkValue($oldCustomer['completedWithinDate'])."',
					expectedTimeuseMinutes = '".checkValue($oldCustomer['expectedTimeuseMinutes'])."',
					triggerEmailAdress = '".checkValue($oldCustomer['triggerEmailAdress'])."',
					triggerNextWhenFinished = '".checkValue($oldCustomer['triggerNextWhenFinished'])."',
					active = '".checkValue($oldCustomer['active'])."',
					follower = '".checkValue($oldCustomer['follower'])."',
					planningStatus = '".checkValue($oldCustomer['planningStatus'])."',
					doneTime = '".checkValue($oldCustomer['doneTime'])."',
					tempactivity = '".checkValue($oldCustomer['tempactivity'])."'");
			}
		}
		//customer
		$getCustomers = "SELECT * FROM partactivity_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM partactivity WHERE id = '".checkValue($oldCustomer['id'])."'")) == 0){
				$moduleID = '77';
				mysql_query("INSERT INTO partactivity SET id = '".checkValue($oldCustomer['id'])."',
					moduleID = '".$moduleID."',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					activityId = '".checkValue($oldCustomer['activityId'])."',
					name = '".checkValue($oldCustomer['name'])."',
					describtion = '".checkValue($oldCustomer['describtion'])."',
					estimatedtimeuse = '".checkValue($oldCustomer['estimatedtimeuse'])."',
					finished = '".checkValue($oldCustomer['finished'])."',
					planningStatus = '".checkValue($oldCustomer['planningStatus'])."',
					doneTime = '".checkValue($oldCustomer['doneTime'])."'");
			}
		}
	}
	if(isset($_POST['importSubscriptions'])) {
		//customer
		$getCustomers = "SELECT * FROM subscribtion_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM subscriptionmulti WHERE id = '".checkValue($oldCustomer['id'])."'")) == 0){

				mysql_query("INSERT INTO subscriptionmulti SET id = '".checkValue($oldCustomer['id'])."',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					subscriptionName = '".checkValue($oldCustomer['subscribtionName'])."',
					periodNumberOfMonths = '".checkValue($oldCustomer['periodNumberOfMonths'])."',
					startDate = '".checkValue($oldCustomer['startDate'])."',
					nextRenewalDate = '".checkValue($oldCustomer['nextRenewalDate'])."',
					lastRenewalDate = '".checkValue($oldCustomer['lastRenewalDate'])."',
					stoppedDate = '".checkValue($oldCustomer['stoppedDate'])."',
					comments = '".checkValue($oldCustomer['comments'])."',
					stopHandleCleanupDate = '".checkValue($oldCustomer['stopHandleCleanupDate'])."',
					freeNoBilling = '".checkValue($oldCustomer['freeNoBilling'])."',
					customerId = '".checkValue($oldCustomer['customerId'])."',
					rentalAgreementFile = '".checkValue($oldCustomer['rentalAgreementFile'])."',
					contactPerson = '".checkValue($oldCustomer['contactPerson'])."',
					contractFile = '".checkValue($oldCustomer['contractFile'])."',
					v2createdDate = '".checkValue($oldCustomer['v2createdDate'])."',
					ownercompany_id = '1',
					subscriptiontype_id = '".checkValue($oldCustomer['subscriptiontype_id'])."'
					");

				mysql_query("INSERT INTO subscriptionline SET 
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					subscribtionId = '".checkValue($oldCustomer['id'])."',
					articleNumber = '".checkValue($oldCustomer['articleNumber'])."',
					articleName = '".checkValue($oldCustomer['articleName'])."',
					discountPercent = '".checkValue($oldCustomer['discountPercent'])."',
					withoutArticle = '".checkValue($oldCustomer['withoutArticle'])."',
					articleOrIndividualPrice = '".checkValue($oldCustomer['articleOrIndividualPrice'])."',
					amount = '".checkValue($oldCustomer['amount'])."',
					pricePerPiece = '".checkValue($oldCustomer['pricePerPiece'])."'");
			}
		}
	}
	if(isset($_POST['importDomainSubscription'])) {
		//customer
		$getCustomers = "SELECT * FROM domainsubscription_import ORDER BY id ASC";
		$findCustomers = mysql_query($getCustomers);
		while($oldCustomer = mysql_fetch_array($findCustomers)) {
			if(mysql_num_rows(mysql_query("SELECT * FROM subscriptionmulti WHERE id = '1".checkValue($oldCustomer['id'])."'")) == 0){
				mysql_query("INSERT INTO subscriptionmulti SET id = '1".checkValue($oldCustomer['id'])."',
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					subscriptionName = '".checkValue($oldCustomer['subscribtionName'])."',
					periodNumberOfMonths = '".checkValue($oldCustomer['periodNumberOfMonths'])."',
					startDate = '".checkValue($oldCustomer['startDate'])."',
					nextRenewalDate = '".checkValue($oldCustomer['nextRenewalDate'])."',
					lastRenewalDate = '".checkValue($oldCustomer['lastRenewalDate'])."',
					stoppedDate = '".checkValue($oldCustomer['stoppedDate'])."',
					comments = '".checkValue($oldCustomer['comments'])."',
					stopHandleCleanupDate = '".checkValue($oldCustomer['stopHandleCleanupDate'])."',
					freeNoBilling = '".checkValue($oldCustomer['freeNoBilling'])."',
					customerId = '".checkValue($oldCustomer['customerId'])."',
					rentalAgreementFile = '".checkValue($oldCustomer['rentalAgreementFile'])."',
					contactPerson = '".checkValue($oldCustomer['contactPerson'])."',
					contractFile = '".checkValue($oldCustomer['contractFile'])."',
					v2createdDate = '".checkValue($oldCustomer['v2createdDate'])."',
					ownercompany_id = '1',
					subscriptiontype_id = '3'
					");

				mysql_query("INSERT INTO subscriptionline SET 
					createdBy = '".checkValue($oldCustomer['createdBy'])."',
					created = '".checkValue($oldCustomer['created'])."',
					updatedBy = '".checkValue($oldCustomer['updatedBy'])."',
					updated = '".checkValue($oldCustomer['updated'])."',
					sortnr = '".checkValue($oldCustomer['sortnr'])."',
					content_status = '".checkValue($oldCustomer['content_status'])."',
					subscribtionId = '1".checkValue($oldCustomer['id'])."',
					articleNumber = '".checkValue($oldCustomer['articleNumber'])."',
					articleName = '".checkValue($oldCustomer['articleName'])."',
					discountPercent = '".checkValue($oldCustomer['discountPercent'])."',
					withoutArticle = '".checkValue($oldCustomer['withoutArticle'])."',
					articleOrIndividualPrice = '0',
					amount = '".checkValue($oldCustomer['amount'])."',
					pricePerPiece = '".checkValue($oldCustomer['pricePerPiece'])."'");
			}
		}
	}
	function uploadingImportFile() {
		$randomnumber = time();
		$allowedExts = array("xlsx", "csv");
		$extension = strtolower(end(explode(".", $_FILES["fileImport"]["name"])));
		mkdir(dirname(__FILE__)."/../../../../../uploads/importData");
		$importPath = realpath(dirname(__FILE__)."/../../../../../uploads/importData");
		$myfiles = array();
		if ($handle = opendir($importPath)) {
			while (($file = readdir($handle)) !== false){
				if (!in_array($file, array('.', '..')) && !is_dir($importPath."/".$file)){
					if(is_file($importPath."/".$file)) {
						$created = filemtime($importPath ."/". $file);
	           			$myfiles[$created] = $file;
	           		}
				}
			}
		 	krsort($myfiles);
		}
		if(count($myfiles) >= 10){
			unlink($importPath ."/".end($myfiles));
		}
		if (in_array($extension, $allowedExts))	{
			if ($_FILES["fileImport"]["error"] > 0) {
		 		echo "Return Code: " . $_FILES["fileImport"]["error"] . "<br>";
		 	} else {
		 		if (file_exists($importPath."/".$randomnumber."". $_FILES["fileImport"]["name"])){
			   		echo $_FILES["fileImport"]["name"] . " already exists. ";
			   	}else {
					move_uploaded_file($_FILES["fileImport"]["tmp_name"],
					$importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"]);
					umask(0);
					chmod($importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"],0777);
					//echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
			   	}
		 	}
		 	return($importPath."/".$randomnumber. "". $_FILES["fileImport"]["name"]);
		}else {
			// echo "Invalid file";
			return("ERROR: INVALID FILE");
		}
	}
	if(isset($_POST['submitImportData'])) {		
		if($_FILES["fileImport"]["error"] == 0){
			$file = uploadingImportFile();
			require_once dirname(__FILE__) . '/PHPExcel/PHPExcel/IOFactory.php';
			$inputFileType = PHPExcel_IOFactory::identify($file);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType)->setDelimiter(";");
            $objReader->setInputEncoding('ISO-8859-1');
            $objReader->setReadDataOnly(FALSE);
			$objPHPExcel = $objReader->load($file);
			$objWorksheet = $objPHPExcel->getActiveSheet();
			$arraydata = array();
			foreach ($objWorksheet->getRowIterator() as $row) {
			    $cellIterator = $row->getCellIterator();
			    $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
			    if($row->getRowIndex() != 1){
				    foreach ($cellIterator as $cell) {
				        if (!is_null($cell)) {
				        	$arraydata[$row->getRowIndex()][PHPExcel_Cell::columnIndexFromString($cell->getColumn())] = $cell->getValue();
				        }
				    }
				}
			}
			foreach($arraydata as $arrayrow){
				$customerNumber = trim($arrayrow[1]);
				$customerName = $arrayrow[3];
				$organNr = $arrayrow[4];
				$phone = $arrayrow[5];
				$mobile = $arrayrow[6];
				$fax = $arrayrow[7];
				$invoiceEmail = $arrayrow[8];
				$address = $arrayrow[9];
				$address2 = $arrayrow[10];
				$postnr = $arrayrow[11];
				$city = $arrayrow[12];
				$country = $arrayrow[13];

				$vis_address = $arrayrow[14];
				$vis_address2 = $arrayrow[15];
				$vis_postnr = $arrayrow[16];
				$vis_city = $arrayrow[17];
				$vis_country = $arrayrow[18];

				$moduleID = 41;
				if($customerNumber != ""){
					if($invoiceEmail != ""){
						$invoiceBy = 1;
					} else {
						$invoiceBy = 0;
					}
					$sqlCheckCustomer = "SELECT * FROM customer_externalsystem_id WHERE external_id = ?";					
					$o_query = $o_main->db->query($sqlCheckCustomer, array($customerNumber));
					$result = $o_query ? $o_query->row_array() : array();
					if(count($result) == 0){
						$insertCustomer = "INSERT INTO customer SET moduleID = ?, created = NOW(), createdBy=?, name = ?, publicRegisterId =?, phone =?, mobile = ?, fax = ?, invoiceEmail = ?, invoiceBy = ?, paStreet = ?, paStreet2 = ?, paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?, vaStreet2 =?, vaPostalNumber = ?, vaCity = ?, vaCountry = ?";					
						$o_query = $o_main->db->query($insertCustomer, array($moduleID, $variables->loggID, $customerName, $organNr, $phone, $mobile, $fax, $invoiceEmail, $invoiceBy, $address, $address2, $postnr, $city, $country, $vis_address, $vis_address2, $vis_postnr, $vis_city, $vis_country));
						if($o_query ){
							$customerId = $o_main->db->insert_id();
							$o_query = $o_main->db->query("INSERT INTO customer_externalsystem_id SET customer_id = ?, external_id = ?, ownercompany_id = 1", array($customerId, $customerNumber));
						} 
					} else {
						$customerId = $result['customer_id'];
						$updateCustomer = "UPDATE customer SET updated = NOW(), updatedBy=?, name = ?, publicRegisterId =?, phone =?, mobile = ?, fax = ?, invoiceEmail = ?, invoiceBy = ?, paStreet = ?, paStreet2 = ?, paPostalNumber = ?, paCity = ?, paCountry = ?, vaStreet = ?, vaStreet2 = ?, vaPostalNumber = ?, vaCity = ?, vaCountry = ? WHERE id = ?";					
						$o_query = $o_main->db->query($updateCustomer, array($variables->loggID, $customerName, $organNr, $phone, $mobile, $fax, $invoiceEmail, $invoiceBy, $address, $address2, $postnr, $city, $country, $vis_address, $vis_address2, $vis_postnr, $vis_city, $vis_country, $customerId));

					}
				}
			}
		}
	}
?>
<div>
	<form name="importData" method="post" enctype="multipart/form-data"  action="" >
		
		<!--<div class="formRow submitRow">
			<input type="submit" name="submitImportData2" value="Import customers and contactPersons">
			<input type="submit" name="importOrders" value="Import orders">
			<input type="submit" name="importActivity" value="Import activity">
			<input type="submit" name="importSubscriptions" value="Import Subscription">
			<input type="submit" name="importDomainSubscription" value="Import Domain Subscription">
			
			<input type="submit" name="fixOrders" value="fix orders">

			<input type="submit" name="changeAssociation" value="Change Associations">
		</div>-->
		<div class="formRow">
			<div>
				<label>Excel file</label>
			</div>
			<input type="file" name="fileImport"/>
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="submitImportData" value="Import">
		</div>
	</form>
</div>