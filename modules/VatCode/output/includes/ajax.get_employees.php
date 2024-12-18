<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if($_POST['action'] == "updateNotification"){
	$notificationPeople = $_POST['peopleId'];
	$s_sql = "SELECT n.*, CONCAT_WS(' ', p.name, p.middlename, p.lastname) as fullName FROM case_crm_newcase_notification_from_customerportal n
	LEFT OUTER JOIN contactperson p ON p.id = n.peopleId
	WHERE n.content_status < 2 AND n.id = ? ORDER BY n.sortnr ASC";
	$o_query = $o_main->db->query($s_sql, array($_POST['notificationId']));
	$notification = ($o_query ? $o_query->row_array() : array());
	if($notification){
		$s_sql = "UPDATE case_crm_newcase_notification_from_customerportal SET
		updated = now(),
		updatedBy= ?,
		peopleId = ?
		WHERE id = ?";
		$o_main->db->query($s_sql, array($variables->loggID, $notificationPeople, $notification['id']));
	} else {
		$s_sql = "INSERT INTO case_crm_newcase_notification_from_customerportal SET
		id=NULL,
		moduleID = ?,
		created = now(),
		createdBy = ?,
		peopleId = ?";
		$o_main->db->query($s_sql, array($moduleID, $variables->loggID, $notificationPeople));
		$crm_message_id = $o_main->db->insert_id();
	}
	return;
}
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
if($search != ""){
	$s_sql = "SELECT * FROM contactperson
	WHERE contactperson.content_status < 2 AND contactperson.type = ? AND (contactperson.name LIKE '%".$search."%' OR contactperson.middlename LIKE '%".$search."%' OR contactperson.lastname LIKE '%".$search."%')  ORDER BY contactperson.name ASC";
} else {
	$s_sql = "SELECT * FROM contactperson
	WHERE contactperson.content_status < 2  AND contactperson.type = ?   ORDER BY contactperson.name ASC";
}

$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
$totalCount = ($o_query ? $o_query->num_rows() : 0);

$o_query = $o_main->db->query($s_sql.$pagerSql, array($people_contactperson_type));
$employees = ($o_query ? $o_query->result_array() : array());
$l_total_pages = $totalCount/$perPage;
$l_page = $page - 1;
if(!$resultOnly){
?>
<div class="employees">
	<div class="employeeSearch">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td><input id="employeesearch" type="text" autocomplete="off" class="form-control input-sm" placeholder="<?php echo $formText_Search_output;?>" value="<?php echo $search;?>"></td>
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
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?>">
						<?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname']; if($employee['title'] != "") echo " - ". $employee['title'];?>
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
	$(".employees .tablescript").unbind("click").bind("click", function(e){
		e.preventDefault();
		var employeeID = $(this).data("employeeid");
		var employeeName = $(this).data("employeename");
		if(employeeID > 0){
			<?php if(intval($_POST['owner']) > 0 ) { ?>
				$(".output-form #projectOwner").val(employeeID);
				$(".output-form .selectOwner").html(employeeName);
			<?php } else if(intval($_POST['invoiceresponsible']) > 0 ) {?>
				$(".output-form #invoiceResponsible").val(employeeID);
				$(".output-form .selectInvoiceResponsible").html(employeeName);
			<?php } else if(intval($_POST['ticketresponsible']) > 0 ) {?>
				$(".output-form #ticketResponsibleId").val(employeeID);
				$(".output-form .selectTicketResponsible").html(employeeName);
			<?php } else if(isset($_POST['notificationId'])){
				?>
					var _data = { fwajax: 1, fw_nocss: 1, action: 'updateNotification', peopleId: employeeID, notificationId: '<?php echo $_POST['notificationId'];?>'};
					$.ajax({
						cache: false,
						type: 'POST',
						dataType: 'json',
						url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
						data: _data,
						success: function(obj){
							reloadNotification();
						}
					});
				<?php
				} else { ?>
				$(".output-form #projectLeader").val(employeeID);
				$(".output-form .selectEmployee").html(employeeName);
			<?php } ?>
			$("#popupeditbox2 .b-close").click();
		}
	})
	$(".employees .pagination a").unbind("click").bind("click", function(e){
		e.preventDefault();
		var _data = { fwajax: 1, fw_nocss: 1, owner: '<?php echo $_POST['owner'];?>', invoiceresponsible: '<?php echo $_POST['invoiceresponsible'];?>', search: $("#employeesearch").val(), resultOnly: true, page: $(this).html(), subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
		<?php if(isset($_POST['notificationId'])){ ?>
			_data.notificationId = '<?php echo $_POST['notificationId']?>';
		<?php } ?>
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
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
			var _data = { fwajax: 1, fw_nocss: 1, owner: '<?php echo $_POST['owner'];?>', invoiceresponsible: '<?php echo $_POST['invoiceresponsible'];?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
			<?php if(isset($_POST['notificationId'])){ ?>
				_data.notificationId = '<?php echo $_POST['notificationId']?>';
			<?php } ?>
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
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
			var _data = { fwajax: 1, fw_nocss: 1, owner: '<?php echo $_POST['owner'];?>', invoiceresponsible: '<?php echo $_POST['invoiceresponsible'];?>', search: $("#employeesearch").val(), resultOnly: true, workplanlineworkerId: '<?php echo $_POST['workplanlineworkerId']?>', subtitueWorker: '<?php echo $_POST['subtitueWorker']?>', subtitueAll: '<?php echo $_POST['subtitueAll']?>'};
			<?php if(isset($_POST['notificationId'])){ ?>
				_data.notificationId = '<?php echo $_POST['notificationId']?>';
			<?php } ?>
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_employees";?>',
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
