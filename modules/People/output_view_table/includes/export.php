<?php

if(isset($_GET['viewer_id'])){

    define('BASEPATH', dirname(__FILE__)."/../../../../");

    require_once(BASEPATH.'elementsGlobal/cMain.php');
    include("readOutputLanguage.php");
    require_once dirname(__FILE__) . '/../elementsOutput/PHPExcel/PHPExcel.php';

    $viewer_id = $_GET['viewer_id'] ? ($_GET['viewer_id']) : "";

    $sql = "SELECT * FROM table_viewer  WHERE id = ?";
    $o_query = $o_main->db->query($sql, array($viewer_id));
    $currentTable = $o_query ? $o_query->row_array() : array();


    define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getActiveSheet()->getDefaultColumnDimension()->setWidth(15);
    $objPHPExcel->setActiveSheetIndex(0);
    $row=1;

    if($currentTable){
    	$sql = "SHOW COLUMNS FROM ".$currentTable['table_name'];
    	$o_query = $o_main->db->query($sql, array($moduleID));
    	$columns = $o_query ? $o_query->result_array() : array();

        $fields = $_GET['fields'];

    	$sql = "SELECT * FROM table_viewer_sub WHERE table_viewer_id = ?";
    	$o_query = $o_main->db->query($sql, array($currentTable['id']));
    	$subTables = $o_query ? $o_query->result_array() : array();
    	$subtable_select = "";
    	$subtable_join = "";
    	if(count($subTables) > 0){
    		foreach($subTables as $subTable) {
                if(in_array($subTable['id'], $fields)){
        			$subtable_select .= ", subtable".$subTable['id'].".".$subTable['table_field']." as subtable".$subTable['id']."_".$subTable['table_field'];
        			$subtable_join .= " LEFT OUTER JOIN ".$subTable['table_name']." subtable".$subTable['id']." ON subtable".$subTable['id'].".".$subTable['subtable_field']." = t.".$subTable['parent_field'];
                }
            }
    	}
    	$sql = "SELECT t.*".$subtable_select." FROM ".$currentTable['table_name']." t
    	".$subtable_join."
    	WHERE t.content_status < 2";
    	$o_query = $o_main->db->query($sql);
    	$tableItems = $o_query ? $o_query->result_array() : array();

        $row = 1; // 1-based index
        $col = 0;
        foreach($columns as $column) {
            if(in_array($column['Field'], $fields)){
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $column['Field']);
                $col++;
            }
        }
        foreach($subTables as $subTable){
            if(in_array($subTable['id'], $fields)){
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $subTable['table_name']." ".$subTable['table_field']);
                $col++;
            }
        }
        $row++;
        foreach($tableItems as $tableItem) {
            $col = 0;
            foreach($columns as $column) {
                if(in_array($column['Field'], $fields)){
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $tableItem[$column['Field']]);
                    $col++;
                }
            }
            foreach($subTables as $subTable){
                if(in_array($subTable['id'], $fields)){
                    $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $tableItem["subtable".$subTable['id']."_".$subTable['table_field']]);
                    $col++;
                }
            }
            $row++;
        }

    }

    $objPHPExcel->setActiveSheetIndex(0);

    header("Content-Type:   application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=property_report.xls");  //File name extension was wrong
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}
 ?>
