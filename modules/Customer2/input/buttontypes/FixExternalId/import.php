<?php

// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 120);

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
			require_once dirname(__FILE__) . '/../Import/PHPExcel/PHPExcel/IOFactory.php';
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
				$customerName = trim($arrayrow[2]);
				$organNr = $arrayrow[5];
				// $phone = $arrayrow[5];
				// $mobile = $arrayrow[6];
				// $fax = $arrayrow[7];
				// $invoiceEmail = $arrayrow[8];
				// $address = $arrayrow[9];
				// $address2 = $arrayrow[10];
				// $postnr = $arrayrow[11];
				// $city = $arrayrow[12];
				// $country = $arrayrow[13];
				//
				// $vis_address = $arrayrow[14];
				// $vis_address2 = $arrayrow[15];
				// $vis_postnr = $arrayrow[16];
				// $vis_city = $arrayrow[17];
				// $vis_country = $arrayrow[18];

				$moduleID = 41;
				if($customerNumber != ""){
					$sqlCheckCustomer = "SELECT * FROM customer_externalsystem_id WHERE external_id = ?";
					$o_query = $o_main->db->query($sqlCheckCustomer, array($customerNumber));
					$result = $o_query ? $o_query->row_array() : array();
					if(count($result) == 0){
						$customerEntry = array();
						if(trim($organNr) != ""){
							$sqlCheckCustomer = "SELECT * FROM customer WHERE publicRegisterId = ? AND name = ?";
							$o_query = $o_main->db->query($sqlCheckCustomer, array($organNr, $customerName));
							$customerEntry = $o_query ? $o_query->row_array() : array();
							if(!$customerEntry){
								$sqlCheckCustomer = "SELECT * FROM customer WHERE publicRegisterId = ?";
								$o_query = $o_main->db->query($sqlCheckCustomer, array($organNr));
								$customerEntry = $o_query ? $o_query->row_array() : array();
							}
						}
						if(!$customerEntry){
							$sqlCheckCustomer = "SELECT * FROM customer WHERE name = ?";
							$o_query = $o_main->db->query($sqlCheckCustomer, array($customerName));
							$customerEntry = $o_query ? $o_query->row_array() : array();
						}
						if($customerEntry) {
							$customerId = $customerEntry['id'];
							$o_query = $o_main->db->query("INSERT INTO customer_externalsystem_id SET customer_id = ?, external_id = ?, external_sys_id = ?, ownercompany_id = 1", array($customerEntry['id'], $customerNumber, $customerNumber));
						}
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
			<input type="submit" name="submitImportData" value="Fix ExternalId">
		</div>
	</form>
</div>
