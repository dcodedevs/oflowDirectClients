<?php
$employees = array();
$wage_group_id = $_POST['wage_group_id'];
if($wage_group_id > 0){
	$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate_group WHERE id = ? ORDER BY name", array($wage_group_id));
	$rateGroup = $wageData_sql ? $wageData_sql->row_array() : array();
} else if(intval($wage_group_id) == 0) {
	$wageData_sql = $o_main->db->query("SELECT * FROM standardwagerate_group WHERE default_group = 1 ORDER BY name");
	$rateGroup = $wageData_sql ? $wageData_sql->row_array() : array();
}
$s_sql = "SELECT * FROM standardwagerate
WHERE standardwagerate.content_status < 2 AND standardwagerate.standartwagerate_group_id = ? ORDER BY standardwagerate.sortnr";
$o_result = $o_main->db->query($s_sql, array($rateGroup['id']));
if($o_result && $o_result->num_rows() > 0)
foreach($o_result->result() AS $employee) {
	array_push($employees, $employee);
}
?>
<div class="employees">
	<div class="eployeeSearch"></div>
	<table class="table table-striped table-condensed">
		<tbody>
		<?php foreach($employees as $employee) { ?>
		<tr>
			<td>
				<a href="#" class="tablescript" data-employeeid="<?php echo $employee->id; ?>" data-employeename="<?php echo $employee->name;?>"><?php echo $employee->name; if($employee->title != "") echo " - ". $employee->title;?></a>
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
</div>
<script type="text/javascript">
	$(".employees .tablescript").unbind("click").bind("click", function(e){
		e.preventDefault();
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
			$(".output-worker-form #wageId").val(employeeID);
			$(".output-worker-form .selectWage").html(employeeName);
			$("#popupeditbox2 .b-close").click();
		}
	})
</script>
