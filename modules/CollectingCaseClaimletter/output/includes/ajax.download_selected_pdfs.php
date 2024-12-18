<?php
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('memory_limit','1024M');

if(count($_POST['casesToGenerate']) > 0)
{
	$ids = $_POST["casesToGenerate"];
	if(count($ids) > 0) {
		$v_files = array();

		$s_sql = "SELECT * FROM collecting_cases_claim_letter WHERE id IN (".implode(",", $ids).")";
		$o_query = $o_main->db->query($s_sql);
		$v_rows = $o_query ? $o_query->result_array() : array();
		$newFolder = __DIR__."/../../../../uploads/".uniqid();
		$zip_file_name_with_location = $newFolder.".zip";
		touch($zip_file_name_with_location);
		$zip = new ZipArchive;
		$opening_zip = $zip->open($zip_file_name_with_location);
		foreach($v_rows as $v_row)
		{
			if(is_file(__DIR__."/../../../../".$v_row["pdf"]))
			{
				$zip->addFile(__DIR__."/../../../../".$v_row["pdf"],basename($v_row["pdf"]));
			}
		}
		$zip->close();
		$demo_name="letters.zip";
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="'.$demo_name.'"');
		readfile($zip_file_name_with_location); // auto download
		//if you wnat to delete this zip file after download
		unlink($zip_file_name_with_location);
		exit;
	}
}
