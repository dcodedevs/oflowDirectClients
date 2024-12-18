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
$creditor_id = $_POST['creditor_id'];

$pagerSql = " LIMIT ".$perPage." OFFSET ".(($page-1)*$perPage);
if($search != ""){
	$s_sql = "SELECT * FROM customer
	WHERE customer.content_status < 2 AND customer.name LIKE '%".$search."%' AND customer.creditor_id = ? ORDER BY customer.name ASC";
} else {
	$s_sql = "SELECT * FROM customer
	WHERE customer.content_status < 2 AND customer.creditor_id = ? ORDER BY customer.name ASC";
}

$o_query = $o_main->db->query($s_sql, array($creditor_id));
$totalCount = ($o_query ? $o_query->num_rows() : 0);

$o_query = $o_main->db->query($s_sql.$pagerSql, array($creditor_id));
$employees = ($o_query ? $o_query->result_array() : array());
$l_total_pages = $totalCount/$perPage;
$l_page = $page - 1;
if(!$resultOnly){
?>
<div class="employees">
	<?php if($creditor_id > 0) {?>
		<div class="create_new_customer"><?php echo $formText_CreateNewCustomer_output;?></div>
	<?php } ?>
	<div class="employeeSearch">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td><input id="employeesearch" type="text" class="form-control input-sm" placeholder="<?php echo $formText_Search_output;?>" value="<?php echo $search;?>" autocomplete='off'></td>
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
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?>"><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?></a>
				</td
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
<style>
.create_new_customer {
	cursor: pointer;
	color: #46b2e2;
	margin-bottom: 10px;
}
</style>
<script type="text/javascript">
	<?php if($creditor_id > 0) {?>
		$(".create_new_customer").off("click").on("click", function(e){
			e.preventDefault();
			var data = {
				creditorId: '<?php echo $creditor_id;?>'
			};
			ajaxCall({module_file:'editCustomerDetail', module_name: 'Customer2', module_folder: 'output'}, data, function(json) {
                $('#popupeditboxcontent2').html('');
                $('#popupeditboxcontent2').html(json.html);
                out_popup2 = $('#popupeditbox2').bPopup(out_popup_options);
                $("#popupeditbox2:not(.opened)").remove();
			});
		})
	<?php } ?>
	$(".employees .tablescript").unbind("click").bind("click", function(){
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
			<?php if($_POST['owner']) { ?>
				$(".output-form-case #order_owner_id").val(employeeID);
				$(".output-form-case .selectOwner").html(employeeName);
			<?php } else if($_POST['creditor']) {  ?>
				$(".output-form-case #creditorId").val(employeeID);
				$(".output-form-case .selectCreditor").html(employeeName);
			<?php	} else if($_POST['debitor']) {  ?>
				$(".output-form-case #debitorId").val(employeeID);
				$(".output-form-case .selectDebitor").html(employeeName);
			<?php	} else { ?>
				$(".output-form-case #customerId").val(employeeID);
				$(".output-form-case .selectCustomer").html(employeeName);
			<?php } ?>
			$("#popupeditbox2 .b-close").click();
		}
	})
	$(".employees .pagination a").unbind("click").bind("click", function(){
		var _data = { fwajax: 1, fw_nocss: 1, owner:'<?php echo $_POST['owner']?>', debitor: '<?php echo $_POST['debitor'];?>', creditor: '<?php echo $_POST['creditor'];?>', creditor_id: '<?php echo $creditor_id;?>', search: $("#employeesearch").val(), resultOnly: true, page: $(this).html(), subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
			data: _data,
			success: function(obj){
				$('.employees .resultOnlyWrapper').html('');
				$('.employees .resultOnlyWrapper').html(obj.html);
			}
		});
	})
	<?php if(!$resultOnly){ ?>
		$(".employeeSearch button").unbind("click").bind("click", function(){
			var _data = { fwajax: 1, fw_nocss: 1, owner:'<?php echo $_POST['owner']?>', debitor: '<?php echo $_POST['debitor'];?>', creditor: '<?php echo $_POST['creditor'];?>', creditor_id: '<?php echo $creditor_id;?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
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
			var _data = { fwajax: 1, fw_nocss: 1, owner:'<?php echo $_POST['owner']?>', debitor: '<?php echo $_POST['debitor'];?>', creditor: '<?php echo $_POST['creditor'];?>', creditor_id: '<?php echo $creditor_id;?>', creditor_id: '<?php echo $creditor_id;?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers";?>',
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
