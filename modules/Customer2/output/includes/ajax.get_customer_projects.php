<?php
$employees = array();
$resultOnly = false;
$search = trim($o_main->db->escape_like_str($_POST['search']));
if(isset($_POST['resultOnly'])){
	$resultOnly = $_POST['resultOnly'];
}
$perPage = 20;
$page = 1;
if($_POST['page'] > 1){
	$page = intval($_POST['page']);
}
$pagerSql = " LIMIT ".$perPage." OFFSET ".(($page-1)*$perPage);
if($_POST['project2']){
	if($search != ""){
		$s_sql = "SELECT project2.*, project2_periods.id as projectPeriodId FROM project2
		LEFT OUTER JOIN project2_periods ON project2_periods.projectId = project2.id
		WHERE project2.content_status < 2 AND project2.customerId = ? AND project2.name LIKE '%".$search."%' ORDER BY project2.name ASC";
	} else {
		$s_sql = "SELECT project2.*, project2_periods.id as projectPeriodId FROM project2
		LEFT OUTER JOIN project2_periods ON project2_periods.projectId = project2.id
		WHERE project2.content_status < 2 AND project2.customerId = ? ORDER BY project2.name ASC";
	}
} else {
	if($search != ""){
		$s_sql = "SELECT * FROM project
		WHERE project.content_status < 2 AND project.customerId = ? AND project.name LIKE '%".$search."%' ORDER BY project.name ASC";
	} else {
		$s_sql = "SELECT * FROM project
		WHERE project.content_status < 2 AND project.customerId = ? ORDER BY project.name ASC";
	}
}

$o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
$totalCount = ($o_query ? $o_query->num_rows() : 0);

$o_query = $o_main->db->query($s_sql.$pagerSql, array($_POST['customerId']));
$employees = ($o_query ? $o_query->result_array() : array());
$l_total_pages = $totalCount/$perPage;
$l_page = $page - 1;
if(!$resultOnly){
?>
<div class="employees">
	<div class="employeeSearch">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td><input id="employeesearch" type="text" class="form-control input-sm" placeholder="<?php echo $formText_Search_output;?>" value="<?php echo $search;?>" autocomplete="off"></td>
			<td><button type="button" class="btn btn-default btn-sm"><?php echo $formText_Search_fieldtype;?></button></td>
		</tr>
		</table>
	</div>
<?php } ?>
	<div class="resultOnlyWrapper">
		<table class="table table-striped table-condensed">
			<tbody>
			<?php foreach($employees as $employee) { ?>
			<tr>
				<td>
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-periodid="<?php echo $employee['projectPeriodId'];?>" data-employeename="<?php echo $employee['name'];?> <?php if($employee['projectPeriodId']) echo " - ".$formText_Period_output." ".$employee['projectPeriodId'];?>"><?php echo $employee['name'];?> <?php if($employee['projectPeriodId']) echo " - ".$formText_Period_output." ".$employee['projectPeriodId'];?>
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
	</div>
<?php if(!$resultOnly){ ?>
</div>
<?php } ?>
<script type="text/javascript">
	$(".employees .tablescript").unbind("click").bind("click", function(){
		<?php if($_POST['project2']) { ?>
			var employeeID = $(this).data("employeeid");
			var employeeName = $(this).data("employeename");
			var periodId = $(this).data("periodid");
			if(employeeID > 0 && periodId > 0){
				$(".output-form #project2Id").val(employeeID);
				$(".output-form #project2PeriodId").val(periodId);
				$(".output-form .connectToProject2Link span").html(employeeName);
				$("#popupeditbox2 .b-close").click();
			}
		<?php } else { ?>
			var employeeID = $(this).data("employeeid");
			var employeeName = $(this).data("employeename");
			if(employeeID > 0){
				$(".output-form #projectId").val(employeeID);
				$(".output-form .connectToProjectLink span").html(employeeName);
				$("#popupeditbox2 .b-close").click();
			}
		<?php } ?>
	})
	$(".employees .pagination a").unbind("click").bind("click", function(){
		var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, page: $(this).html(), customerId: '<?php echo $_POST['customerId']?>', project2: '<?php echo $_POST['project2'];?>'};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customer_projects";?>',
			data: _data,
			success: function(obj){
				$('.employees .resultOnlyWrapper').html('');
				$('.employees .resultOnlyWrapper').html(obj.html);
			}
		});
	})
	<?php if(!$resultOnly){ ?>
		$(".employeeSearch button").unbind("click").bind("click", function(){
			var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, customerId: '<?php echo $_POST['customerId']?>', project2: '<?php echo $_POST['project2'];?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customer_projects";?>',
				data: _data,
				success: function(obj){
					$('.employees .resultOnlyWrapper').html('');
					$('.employees .resultOnlyWrapper').html(obj.html);
				}
			});
		})
		var typingTimer;                //timer identifier
		var doneTypingInterval = 300;  //time in ms, 5 second for example
		var $input = $('#employeesearch');

		//on keyup, start the countdown
		$input.on('keyup', function () {
			clearTimeout(typingTimer);
			typingTimer = setTimeout(doneTyping, doneTypingInterval);
		});

		//on keydown, clear the countdown
		$input.on('keydown', function () {
			clearTimeout(typingTimer);
		});

		//user is "finished typing," do something
		function doneTyping () {
			var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, customerId: '<?php echo $_POST['customerId']?>', project2: '<?php echo $_POST['project2'];?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customer_projects";?>',
				data: _data,
				success: function(obj){
					$('.employees .resultOnlyWrapper').html('');
					$('.employees .resultOnlyWrapper').html(obj.html);
					// $('#popupeditboxcontent2').html('');
					// $('#popupeditboxcontent2').html(obj.html);
					// out_popup = $('#popupeditbox2').bPopup(out_popup_options);
					// $("#popupeditbox2:not(.opened)").remove();
				}
			});
		}
	<?php } ?>
</script>
