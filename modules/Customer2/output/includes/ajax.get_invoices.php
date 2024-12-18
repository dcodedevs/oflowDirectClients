<?php
$employees = array();
$resultOnly = false;

if(isset($_POST['resultOnly'])){
	$resultOnly = $_POST['resultOnly'];
}
$limit = " LIMIT 10 OFFSET 0";
if(isset($_POST['showAll'])){
	$limit = "";
}
$s_sql = "SELECT * FROM invoice 
WHERE invoice.content_status < 2  AND invoice.totalInclTax > 0 AND invoice.customerId = ? ORDER BY invoice.invoiceDate DESC";

$o_query = $o_main->db->query($s_sql, $_POST['customerId']);
$employeesTotalCount = ($o_query ? $o_query->num_rows() : array());

$s_sql .= $limit;
$o_query = $o_main->db->query($s_sql, $_POST['customerId']);
$employees = ($o_query ? $o_query->result_array() : array());

$showMore = true;
if($employeesTotalCount == count($employees)){
	$showMore = false;
}
if(!$resultOnly){
?>
<div class="employees">
<?php } ?>
	<table class="table table-striped table-condensed">
		<tbody>
			<tr>
				<th><?php echo $formText_InvoiceNumber_output;?></th>
				<th><?php echo $formText_InvoiceDate_output;?></th>
				<th><?php echo $formText_TotalInclTax_output;?></th>
				<th></th>
			</tr>
		<?php foreach($employees as $employee) { ?>
		<tr>
			<td>				
				<?php echo $employee['external_invoice_nr'];?>	
			</td>
			<td>				
				<?php echo date("d.m.Y", strtotime($employee['invoiceDate']));?>
			</td>
			<td>
				<?php echo $employee['totalInclTax']?>	
			</td>
			<td>
				<a href="#" class="tablescript" data-employeeid="<?php echo $employee['external_invoice_nr']?>" data-employeename="<?php echo $formText_ConnectedToInvoiceNumber_output.": ". $employee['external_invoice_nr'];?>">
					<?php echo $formText_Select_output;?>
				</a>
			</td>
		</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
	if($l_total_pages > 1)
	{
		?><ul class="pagination pagination-sm" style="margin:0;"><?php
		for($l_x = 0; $l_x < $l_total_pages; $l_x++)
		{
			if($l_x < 1 || ($l_x > ($l_page - 7) && $l_x < ($l_page + 7)) || $l_x >= ($l_total_pages - 1))
			{
				$b_print_space = true;
				?><li<?php echo ($l_page==$l_x ? ' class="active"' : '');?>><a href="#"><?php echo ($l_x+1);?></a></li><?php
			} else if($b_print_space) {
				$b_print_space = false;
				?><li><a onClick="javascript:return false;">...</a></li><?php
			}
		}
		?></ul><?php
	}?>
		<?php if($showMore) { ?>
		<div class="showAll"><?php echo $formText_ShowAll_output;?></div>
		<?php }?>
<?php if(!$resultOnly){ ?>
</div>
<?php } ?>
<script type="text/javascript">
	$(".employees .tablescript").unbind("click").bind("click", function(){
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
			$("#creditRefNo").val(employeeID);
			$(".selectInvoice label").html(employeeName);
			$(".selectInvoice .resetInvoiceConnection").show();
			$("#popupeditbox2 .b-close").click();
			$(".selectInvoice .errorLabel").addClass("hidden");
		}
	})
	<?php if(!$resultOnly){ ?>
		$(".employeeSearch button").unbind("click").bind("click", function(){
			var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_invoices";?>',
				data: _data,
				success: function(obj){
					$('.employees').html('');
					$('.employees').html(obj.html);
				}
			});
		})		
	<?php } ?>
	$(".showAll").unbind("click").bind("click", function(){
		var _data = { fwajax: 1, fw_nocss: 1, resultOnly: true, showAll: true, customerId: <?php echo $_POST['customerId']?>};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_invoices";?>',
			data: _data,
			success: function(obj){
				$('.employees').html('');
				$('.employees').html(obj.html);
			}
		});
	})	
</script>
<style>
	.showAll {
		color: #46b2e2;
		cursor: pointer;
	}
	.resetInvoiceConnection {

	}
</style>