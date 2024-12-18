<?php
// $v_email_server_config = mysql_fetch_assoc(mysql_query("select * from sys_emailserverconfig order by default_server desc"));
// if($v_email_server_config['technical_email']=="")
// {
// 	?><h2><?php echo $formText_TechicalEmailNotSpecified_Output;?></h2><?php
// 	return;
// }
if(isset($_POST["order_number"]))
{
	$v_row = mysql_fetch_assoc(mysql_query("SELECT * FROM moduledata WHERE name = 'Orders'"));
	$l_orders_module_id = $v_row["uniqueID"];

	$v_sort_nr = mysql_fetch_assoc(mysql_query("select max(sortnr) sortnr from batch_invoicing"));
	$l_sort_nr = $v_sort_nr["sortnr"] + 1;
	$s_sql = "INSERT INTO batch_invoicing SET
	id=NULL,
	moduleID = '".$moduleID."',
	created = now(),
	createdBy='".$variables->loggID."',
	sortnr='".$l_sort_nr."'";
	mysql_query($s_sql);

	$v_proc_variables["order_number"] = $_POST["order_number"];
	$v_proc_variables["module_id"] = $l_orders_module_id;
	$v_proc_variables["batch_invoice_id"] = mysql_insert_id();
	$v_proc_variables["lines_total"] = 0;
	$v_proc_variables["lines_sent"] = 0;

	$v_proc_variables['createProcLines'] = true;
	$v_proc_variables['currentProcLine'] = 0;

	include(__DIR__."/../procedure_create_invoices/run.php");

	?>
	<div><?php echo $formText_TotalInvoicesWasCreated_Output.": ".$v_proc_variables["lines_total"];?></div>

	<?php
	if($v_proc_variables["lines_sent"] > 0)
	{
		?><div><?php echo $formText_TotalInvoicesWasSent_Output.": ".$v_proc_variables["lines_sent"];?></div><?php
	}
	if($v_proc_variables["lines_total"] > $v_proc_variables["lines_sent"])
	{
		echo $formText_PrintableFileWithInvoicesWasGeneraged_Output; ?>.<a target="_blank" href="<?php echo $extradir."/output/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_proc_variables["batch_invoice_id"];?>"><?php echo $formText_DownloadInvoicesForPrint_Output;?></a><?php
	}
}
?>
