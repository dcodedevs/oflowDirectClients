<?php
//include(__DIR__."/list_btn.php");
$v_address_format = array('paStreet', 'paCity', 'paCountry', 'paPostalNumber');

$s_sql = "SELECT * FROM invoice_accountconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_settings = $o_query->row_array();
}
?>
<div id="out-batch-list">
	<table class="table">
	<thead>
		<tr>
			<th><?php echo $formText_Created_Output;?></th>
			<th><?php echo $formText_CreatedBy_Output;?></th>
			<th><?php echo $formText_DownloadPrintable_Output;?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$s_sql = "SELECT bi.* FROM batch_invoicing bi JOIN invoice i ON  i.batch_id = bi.id WHERE (i.sentByEmail IS NULL OR i.sentByEmail = '') AND (ehf_reference IS NULL OR ehf_reference = '' OR ehf_reference NOT LIKE '%[REFERENCE]%' ESCAPE '!') GROUP BY bi.id ORDER BY bi.created DESC";
	//$s_sql = "SELECT * FROM batch_invoicing ORDER BY created DESC";
	$v_rows = array();
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$v_rows = $o_query->result_array();
	}
	foreach($v_rows as $v_row)
	{
		?><tr>
			<td><?php echo $v_row["created"];?></td>
			<td><?php echo $v_row["createdBy"];?></td>
			<td><a target="_blank" href="<?php echo $extradir."/output/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_row["id"];?>"><?php echo $formText_Download_Output;?></a></td>
		</tr><?php
	}
	?>
	</tbody>
	</table>
</div>
