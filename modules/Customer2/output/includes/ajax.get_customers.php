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
$search_sql = "";
if($_POST['invoice_customer']){
	$search_sql = " OR cei.external_id = '".$o_main->db->escape_str($search)."'";
}
if($_POST['mergeFromCustomerId'] > 0){
	$filter_sql = " AND customer.id <> '".$o_main->db->escape_str($_POST['mergeFromCustomerId'])."'";
}
$group_by = " GROUP BY customer.id";
if($search != ""){
	$s_sql = "SELECT customer.*, cei.external_id FROM customer
	LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id
	WHERE customer.content_status < 2 AND  (customer.name LIKE '%".$search."%' OR customer.middlename LIKE '%".$search."%' OR customer.lastname LIKE '%".$search."%'".$search_sql.") ".$filter_sql." ".$group_by." ORDER BY customer.name ASC";
} else {
	$s_sql = "SELECT customer.*, cei.external_id FROM customer
	LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id
	WHERE customer.content_status < 2 ".$filter_sql." ".$group_by." ORDER BY customer.name ASC";
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
			<?php foreach($employees as $employee) {
				$s_sql = "SELECT s.*, st.name as subscriptionTypeName FROM subscriptionmulti s
				JOIN subscriptiontype st ON st.id = s.subscriptiontype_id
				WHERE s.customerId = '".$o_main->db->escape_str($employee['id'])."' AND s.content_status < 2 AND  s.startDate < CURDATE() AND (s.stoppedDate is null OR s.stoppedDate = '0000-00-00' OR s.stoppedDate > CURDATE())";
				$o_query = $o_main->db->query($s_sql);
				$activeSubscriptions = ($o_query ? $o_query->result_array() : array());

				$s_sql = "SELECT cei.* FROM customer_externalsystem_id cei
				WHERE cei.customer_id = '".$o_main->db->escape_str($employee['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				$external_items = ($o_query ? $o_query->result_array() : array());

				$external_ids = array();
				foreach($external_items as $external_item) {
					$external_ids[] = $external_item['external_id'];
				}
				?>

			<tr>
				<td>
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];
					?>">
					 <?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?>

						<div class="contactPersonInit" style="display: none;">
							<select name="contactPerson">
                                <option value=""><?php echo $formText_Select_output;?></option>
								<?php
						        $s_sql = "select * from contactperson where customerId = ? AND content_status = 0 order by sortnr";;
						        $o_query = $o_main->db->query($s_sql, array($employee['id']));
						        $contactPersons = $o_query ? $o_query->result_array() : array();
	                            foreach($contactPersons as $contactPerson) {
	                                ?>
	                                <option value="<?php echo $contactPerson['id'];?>"><?php echo $contactPerson['name'];?></option>
	                                <?php
	                            }
	                            ?>
							</select>
						</div><?php
						if(count($activeSubscriptions) > 0) {
							echo ' - ';
							$subscriptionTypeNames = array();
							foreach($activeSubscriptions as $activeSubscription) {
								$subscriptionTypeNames[] = $activeSubscription['subscriptionTypeName'];
							}
							echo implode(", ", $subscriptionTypeNames);
						}
						?>
					</a>
				</td>
				<?php if($_POST['invoice_customer'] || $_POST['mergeFromCustomerId']){ ?>
					<td width="40%"><?php echo implode(", ", $external_ids);?></td>
				<?php } ?>
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
			<?php  if($_POST['invoice_customer']) { ?>
				$(".output-form #invoiceCustomerId").val(employeeID).trigger("change");
				$(".output-form .selectInvoiceCustomer").html(employeeName);
				$(".output-form .reset_invoice_customer").show();
				$("#popupeditbox2 .b-close").click();
			<?php } else { ?>
				var changed = 0;
				if($(".output-form #customerId").val() != employeeID) {
					changed = 1;
				}
				$(".output-form #customerId").val(employeeID).trigger("change");
				$(".output-form #connectedCustomerId").val(employeeID).trigger("change");
				$(".output-form .selectCustomer").html(employeeName);
				$(".output-form .contactPersonSelectWrapper").html($(this).find(".contactPersonInit").html());
				if(changed){
					$(".output-form .connectToProjectLink span").html("");
					$(".output-form #projectId").val("");
				}
				$(".output-form .customerChanged").val(changed);
				$("#popupeditbox2 .b-close").click();
			<?php } ?>

		}
	})
	$(".employees .pagination a").unbind("click").bind("click", function(e){

			e.preventDefault();
		var _data = { fwajax: 1, fw_nocss: 1, customer_group:'<?php echo $_POST['customer_group'];?>', show_subscriptiontypes: '<?php echo $_POST['show_subscriptiontypes'];?>', mergeFromCustomerId:'<?php echo $_POST['mergeFromCustomerId'];?>', invoice_customer: '<?php echo $_POST['invoice_customer'];?>', search: $("#employeesearch").val(), resultOnly: true, page: $(this).html(), subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
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
		$(".employeeSearch button").unbind("click").bind("click", function(e){

				e.preventDefault();
			var _data = { fwajax: 1, fw_nocss: 1, customer_group:'<?php echo $_POST['customer_group'];?>', show_subscriptiontypes: '<?php echo $_POST['show_subscriptiontypes'];?>', mergeFromCustomerId:'<?php echo $_POST['mergeFromCustomerId'];?>', invoice_customer: '<?php echo $_POST['invoice_customer'];?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
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
			var _data = { fwajax: 1, fw_nocss: 1, customer_group:'<?php echo $_POST['customer_group'];?>', show_subscriptiontypes: '<?php echo $_POST['show_subscriptiontypes'];?>', mergeFromCustomerId:'<?php echo $_POST['mergeFromCustomerId'];?>', invoice_customer: '<?php echo $_POST['invoice_customer'];?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
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
