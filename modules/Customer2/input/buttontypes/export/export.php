<?php
 
 if(isset($_POST['submitExportData']) )
 {
define('BASEPATH', dirname(__FILE__)."/../../../../../");
 
require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once dirname(__FILE__) . '/PHPExcel/PHPExcel.php';
$query = "SELECT customer.id as custId, customer.name as custName, contactperson.name as contactName, customer.email as custEmail, contactperson.email as contactEmail, customer.*, contactperson.* FROM `customer`, contactperson where customer.id = contactperson.customerId and ( contactperson.inactive = 0 OR contactperson.inactive IS NULL)";
 $o_query = $o_main->db->query($query);
 
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();		
$objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
$objPHPExcel->setActiveSheetIndex(0);
$row=1;

  

  $objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, "KundeID");
  $objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, "Kundenavn");
  $objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, "Org. nr.");
  $objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, "Adresse");
  $objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, "Postnr");
  $objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, "Sted");
  $objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, "Telefon");
  $objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, "Epost");
  $objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, "Kontaktperson navn");
  $objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, "Kontaktperson tittel");
  $objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, "Kontaktperson epost");
  $objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, "Kontaktperson telefon"); 

 
foreach($o_query->result_array() as $customerlist)
{
	$queryTest = $o_main->db->query("SELECT * FROM subscriptionmulti where subscriptionmulti.customerId = '".$customerlist['custId']."' and subscriptionmulti.stoppedDate = '0000-00-00' and subscriptionmulti.startDate != '0000-00-00'");
	
	if($queryTest && $queryTest->num_rows()>0  ) 
	{ 
		  
		$row = $objPHPExcel->getActiveSheet()->getHighestRow()+1;
		$objPHPExcel->getActiveSheet()->SetCellValue('A'.$row, $customerlist['custId']);
		$objPHPExcel->getActiveSheet()->SetCellValue('B'.$row, $customerlist['custName']);
		$objPHPExcel->getActiveSheet()->SetCellValue('C'.$row, $customerlist['publicRegisterId']); 
		$objPHPExcel->getActiveSheet()->SetCellValue('D'.$row, $customerlist["paStreet"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('E'.$row, $customerlist["paPostalNumber"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('F'.$row, $customerlist["paCity"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('G'.$row, $customerlist["phone"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('H'.$row, $customerlist["custEmail"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('I'.$row, $customerlist["contactName"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('J'.$row, $customerlist["title"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('K'.$row, $customerlist["contactEmail"]);
		$objPHPExcel->getActiveSheet()->SetCellValue('L'.$row, $customerlist["mobile"]);
	}
	 
 
}
$objPHPExcel->setActiveSheetIndex(0);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="kundeeksport_m_kontaktpersondata.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit; 
 }
 else
 {
?>
<div>
	<form name="exportData" method="post" enctype="multipart/form-data"  action="https://s16.getynet.com/accounts/ifbCrm3No/modules/Customer2/input/buttontypes/export/export.php" target="_blank" >
		
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
				<label>Eksporter kunder med aktivt abonnement med kontaktpersondata</label>
			</div>
			 
		</div>
		<div class="formRow submitRow">
			<input type="submit" name="submitExportData" value="Eksporter til Excel">
		</div>
	</form>
</div>
<?php 
 } ?>