<?php
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = array();
if($o_query && $o_query->num_rows()>0) {
    $v_customer_accountconfig = $o_query->row_array();
}

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

$employees = array();
$resultOnly = false;
$search = trim($o_main->db->escape_like_str($_POST['search']));
$s_sql = "SELECT customer.* FROM customer
LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = customer.id
WHERE customer.content_status < 2  AND  (
	customer.name LIKE '%".$search."%' OR customer.middlename LIKE '%".$search."%' OR customer.lastname LIKE '%".$search."%' OR
	cexternl.external_sys_id LIKE '%".$o_main->db->escape_like_str($search)."%' OR cexternl.external_id LIKE '%".$o_main->db->escape_like_str($search)."%'
	".($customer_basisconfig['activate_shop_name'] ? " OR customer.shop_name LIKE '%".$search."%'" : '')."
)
GROUP BY customer.id
ORDER BY customer.name ASC";

$o_query = $o_main->db->query($s_sql);
$employees = ($o_query ? $o_query->result_array() : array());
?>
<table class="table table-striped table-condensed">
	<tbody>
	<?php foreach($employees as $employee) { ?>
	<tr>
		<td>
			<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?>"><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'].(($customer_basisconfig['activate_shop_name'] && '' != trim($employee['shop_name'])) ? ' ('.trim($employee['shop_name']).')' : '');?></a>
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
<script type="text/javascript">
	$(".employeeSearchSuggestions .tablescript").unbind("click").bind("click", function(){
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
            <?php
            if($_POST['from_project_code_overview']) {
                ?>
                fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=project_code_overview&cid="+employeeID, '', true);
                <?php
            } else {
            ?>
                fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=details&cid="+employeeID, '', true);
            <?php } ?>
        }
	})
</script>
