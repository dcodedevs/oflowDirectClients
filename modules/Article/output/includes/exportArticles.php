<?php
session_start();
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
ini_set('max_execution_time', 600);
// Constants (taken from fw/index.php)
define('FRAMEWORK_DEBUG', FALSE);
define('ACCOUNT_PATH', realpath(__DIR__.'/../../../')); // this is modified to fit this files location
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);
$v_tmp = explode("/",ACCOUNT_PATH);
$accountname = array_pop($v_tmp);

// Load database
require_once __DIR__ . '/../../../elementsGlobal/cMain.php';

include_once(dirname(__FILE__).'/languagesOutput/no.php');

$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();



	// ---------------------------------------------------------
	ob_start();

	//Close and output
    if($article_accountconfig['activateArticleCode']) {
        echo $formText_ArticleCode_output.";";
    }
    echo $formText_Name_output.";";
    if($article_accountconfig['activate_article_group']) {
        echo $formText_Group_Output.";";
    }
    if($article_accountconfig['activate_comment_field']) {
        echo $formText_Comment_Output.";";
    }
    echo $formText_CostPrice_output.";";
    echo $formText_Price_output.";";
    echo $formText_SalesAccountWithVat_output.";";
    echo $formText_VatCodeWithVat_Output."\n";

    require_once __DIR__ . '/includes/functions.php';
    $customerList = get_support_list($list_filter, $search_filter);

    foreach($customerList as $v_row)
    {
        if($article_accountconfig['activateArticleCode']) {
            echo $v_row['articleCode'].";";
        }
        echo $v_row['name'].";";
        if($article_accountconfig['activate_article_group']) {
            echo $v_row['groupName'].";";
        }
        if($article_accountconfig['activate_comment_field']) {
            echo $v_row['comment'].";";
        }
        echo $v_row['costPrice'].";";
        echo $v_row['price'].";";
        echo $v_row['SalesAccountWithVat'].";";
        echo $v_row['VatCodeWithVat']."\n";

	}


	$csv = ob_get_clean();
	$filename = 'articles';

	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $csv;

// header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
