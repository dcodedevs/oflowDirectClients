<?php
/*if(isset($_POST['get_item']) && 1 == $_POST['get_item'])
{
	$s_sql_join = '';
	$s_sql_where = '';
	if(isset($_POST['customer_id']))
	{
		$s_sql_join = " JOIN customer AS c ON c.accounting_project_number = pfa.projectnumber";
		$s_sql_where = " AND c.id = '".$o_main->db->escape_str($_POST['customer_id'])."'";
	}
	$s_sql = "SELECT pfa.* FROM projectforaccounting AS pfa".$s_sql_join."
	WHERE pfa.ownercompany_id = '".$o_main->db->escape_str($_POST['ownercompany_id'])."'".$s_sql_where." AND pfa.content_status < 2 LIMIT 1";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows() > 0)
	{
		$v_projectforaccounting = $o_query->row_array();
		$fw_return_data = array(
			'name' => $v_projectforaccounting['name'].' ('.$v_projectforaccounting['projectnumber'].')',
			'projectnumber' => $v_projectforaccounting['projectnumber'],
		);
	}
	return;
}*/
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

$currentOwnerCompanyCount_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE content_status < 2");
$currentOwnerCompanyCount = $currentOwnerCompanyCount_sql->num_rows();
$ownercompany_sql = "";
if($currentOwnerCompanyCount > 1){
	$ownercompany_sql = " ownercompany_id = '".$o_main->db->escape_str($_POST['ownercompany_id'])."' AND";
}

if($search != ""){
	$s_sql = "SELECT * FROM projectforaccounting
	WHERE ".$ownercompany_sql." content_status < 2 AND name LIKE '%".$search."%' ORDER BY projectnumber ASC";
} else {
	$s_sql = "SELECT * FROM projectforaccounting
	WHERE ".$ownercompany_sql." content_status < 2 ORDER BY projectnumber ASC";
}

$o_query = $o_main->db->query($s_sql);
$totalCount = ($o_query ? $o_query->num_rows() : 0);

$o_query = $o_main->db->query($s_sql.$pagerSql);
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
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['projectnumber']?>" data-employeename="<?php echo $employee['name']?> (<?php echo $employee['projectnumber'];?>)"><?php echo $employee['name']?> (<?php echo $employee['projectnumber'];?>)</a>
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
	$(".employees .tablescript").unbind("click").bind("click", function(e){
		e.preventDefault();
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
			$(".output-form #projectCode").val(employeeID);
			$(".output-form .selectProject").html(employeeName);
			$("#popupeditbox2 .b-close").click();
		}
	})
	$(".employees .pagination a").unbind("click").bind("click", function(){
		var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, page: $(this).html(), subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>', ownercompany_id: '<?php echo $_POST['ownercompany_id']?>'};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_accounting_projects";?>',
			data: _data,
			success: function(obj){
				$('.employees .resultOnlyWrapper').html('');
				$('.employees .resultOnlyWrapper').html(obj.html);
			}
		});
	})
	<?php if(!$resultOnly){ ?>
		$(".employeeSearch button").unbind("click").bind("click", function(){
			var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>', ownercompany_id: '<?php echo $_POST['ownercompany_id']?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_accounting_projects";?>',
				data: _data,
				success: function(obj){
					$('.employees .resultOnlyWrapper').html('');
					$('.employees .resultOnlyWrapper').html(obj.html);
				}
			});
		})
		var typingTimer;                //timer identifier
		var doneTypingInterval = 100;  //time in ms, 5 second for example
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
			var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>', ownercompany_id: '<?php echo $_POST['ownercompany_id']?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_accounting_projects";?>',
				data: _data,
				success: function(obj){
					$('.employees .resultOnlyWrapper').html('');
					$('.employees .resultOnlyWrapper').html(obj.html);
				}
			});
		}
	<?php } ?>
</script>
