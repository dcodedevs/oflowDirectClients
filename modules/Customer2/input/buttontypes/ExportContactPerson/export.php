<?php

if(isset($_POST['submitExportData']) &&  $_POST['propertyId'] > 0 )
{

   if($_POST['propertyId'] > 0 ){

        define('BASEPATH', dirname(__FILE__)."/../../../../../");

        require_once(BASEPATH.'elementsGlobal/cMain.php');
        require_once dirname(__FILE__) . '/PHPExcel/PHPExcel.php';

        $s_sql = "SELECT contactperson.*, customer.name as customerName FROM contactperson
        LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.customerId = contactperson.customerId
        LEFT OUTER JOIN subscriptionofficespaceconnection ON subscriptionofficespaceconnection.subscriptionId = subscriptionmulti.id
        LEFT OUTER JOIN property_unit ON property_unit.id = subscriptionofficespaceconnection.officeSpaceId
        LEFT OUTER JOIN customer ON customer.id = contactperson.customerId
        WHERE property_unit.property_id = ? ";
        $o_query = $o_main->db->query($s_sql, array($_POST['propertyId']));

        $contactPersons = $o_query ? $o_query->result_array() : array();

        define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
        $objPHPExcel->setActiveSheetIndex(0);
        $row=1;



        // $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, "active");
        // $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, "department");
        // $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, "domain");
        // $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, "email");
        // $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, "externalid");
        // $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "first name");
        // $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, "last name");
        // $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, "login");
        // $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, "mobile");
        // $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, "parent companyid");
        // $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, "phone");


        foreach($contactPersons as $contactPerson)
        {
            $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, 1);
            $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $contactPerson['id']);
            $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $contactPerson['email']);
            $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $contactPerson['id']);
            $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $contactPerson['name']);
            $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $contactPerson['lastname']);
            $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $contactPerson['id']);
            $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $contactPerson['mobile']);
            $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $contactPerson['customerId']);
            $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, "");
            $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $contactPerson['customerName']);
            $row++;

        }
        $objPHPExcel->setActiveSheetIndex(0);


        header("Content-type: text/csv");
        header('Content-Disposition: attachment;filename="contact_persons.csv"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        $objWriter->save('php://output');
        exit;
    } else {
        echo $formText_SelectBuilding_buttontype;
    }
 }
 else
 {
?>
<div>
	<form name="exportData" method="post" enctype="multipart/form-data"  action="../modules/Customer2/input/buttontypes/ExportContactPerson/export.php" >

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
				<label><?php echo $formText_ExportContactPerson_buttontype;?></label>
			</div>

		</div>
		<div class="formRow">
			<div>
				<label><?php echo $formText_Building_buttontype;?></label>
                <select name="propertyId" required>
                    <option value=""><?php echo $formText_SelectBuilding_buttontype;?></option>
                    <?php
                    $s_sql = "SELECT * FROM property ORDER BY name ASC";
                    $o_query = $o_main->db->query($s_sql);
                    $properties = $o_query ? $o_query->result_array() : array();

                    foreach($properties as $property) {
                        ?>
                        <option value="<?php echo $property['id']; ?>">
                            <?php echo $property['name']; ?>
                        </option>
                    <?php
                    }
                    ?>
                </select>
			</div>
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="submitExportData" value="Export csv">
		</div>
	</form>
</div>
<?php
 } ?>
