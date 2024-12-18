<?php
$selfdefinedFieldId = $_POST['selfdefinedFieldId'] ? ($_POST['selfdefinedFieldId']) : 0;
$customer_id = ($_POST['customerId']);

if($selfdefinedFieldId > 0 && $customer_id > 0){
	$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($selfdefinedFieldId));
	if($o_query && $o_query->num_rows()>0) {
		$selfdefinedField = $o_query->row_array();
	}

	$s_sql = "SELECT * FROM customer_selfdefined_lists WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($selfdefinedField['list_id']));
	if($o_query && $o_query->num_rows()>0) {
		$selfdefinedList = $o_query->row_array();
	}

	$employees = array();

	$resultOnly = false;
	$search = trim($_POST['search']);
	if(isset($_POST['resultOnly'])){
		$resultOnly = $_POST['resultOnly'];
	}
	if($search != ""){
		$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE customer_selfdefined_list_lines.list_id = ".$o_main->db->escape($selfdefinedList['id'])." AND customer_selfdefined_list_lines.name LIKE '%".$o_main->db->escape_like_str($search)."%' ORDER BY customer_selfdefined_list_lines.name ASC";
	} else {
		$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE customer_selfdefined_list_lines.list_id = ".$o_main->db->escape($selfdefinedList['id'])." ORDER BY customer_selfdefined_list_lines.name ASC";
	}
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0) {
		$employees = $o_query->result_array();
	}

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
		<table class="table table-striped table-condensed">
			<tbody>
			<?php foreach($employees as $employee) { ?>
			<tr>
				<td>
					<a href="#" class="tablescript" data-employeeid="<?php echo $employee['id']?>" data-employeename="<?php echo $employee['name'];?>">
						<?php if($_POST['checkboxes']) {
							$selected = false;

							$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
							$o_query = $o_main->db->query($s_sql, array($customer_id, $selfdefinedFieldId));
							if($o_query && $o_query->num_rows()>0) {
								$predefinedFieldValue = $o_query->row_array();
							}

							$s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
							$o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id'], $employee['id']));
							if($o_query && $o_query->num_rows()>0) {
                                $selected = true;
							}
						?>
							<input type="checkbox" value="<?php echo $employee['id']?>" id="popupchk<?php echo $employee['id']?>" <?php if($selected) { echo 'checked';}?>/>
						<?php }?>
						<label for="popupchk<?php echo $employee['id']?>"> <?php echo $employee['name']; if($employee['title'] != "") echo " - ". $employee['title'];?></label>
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
	<?php if(!$resultOnly){ ?>
	</div>
	<?php } ?>
	<script type="text/javascript">
		<?php if($_POST['checkboxes']) { ?>
			$(".employees .tablescript").unbind("click").bind("click", function(){
				var employeeID = $(this).data("employeeid");
				var employeeName = $(this).data("employeename");
				if(employeeID > 0){
					var lineChecked = $(this).find("input").is(":checked");
					var lineId =employeeID;
					var data = {
			            selfdefinedFieldId: '<?php echo $selfdefinedFieldId;?>',
			            customerId: '<?php echo $customer_id;?>',
			            action: "updateCheckboxes",
			            lineId: lineId,
			            lineChecked: lineChecked
			        };
			        ajaxCall('update_selfdefinedvalues', data, function(json) {
						out_popup.addClass("close-reload");
			        });
				}
			})
		<?php } else { ?>
			$(".employees .tablescript").unbind("click").bind("click", function(){
				var employeeID = $(this).data("employeeid");
				var employeeName = $(this).data("employeename");
				if(employeeID > 0){
					 var data = {
			            selfdefinedFieldId: '<?php echo $selfdefinedFieldId;?>',
			            customerId: '<?php echo $customer_id;?>',
			            action: "updateText",
			            value: employeeID
			        };
			        ajaxCall('update_selfdefinedvalues', data, function(json) {
						$("#popupeditbox .b-close").click();
		            	output_reload_page();
			        });
				}
			})
		<?php } ?>
		<?php if(!$resultOnly){ ?>
			$(".employeeSearch button").unbind("click").bind("click", function(){
				var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, selfdefinedFieldId: '<?php echo $_POST['selfdefinedFieldId']?>', customerId: '<?php echo $_POST['customerId'];?>', checkboxes: '<?php echo $_POST['checkboxes'];?>'};
				$.ajax({
					cache: false,
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_selfdefinedlist_lines";?>',
					data: _data,
					success: function(obj){
						$('.employees .table').html('');
						$('.employees .table').html(obj.html);
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
				var _data = { fwajax: 1, fw_nocss: 1, search: $("#employeesearch").val(), resultOnly: true, selfdefinedFieldId: '<?php echo $_POST['selfdefinedFieldId']?>', customerId: '<?php echo $_POST['customerId'];?>', checkboxes: '<?php echo $_POST['checkboxes'];?>'};
				$.ajax({
					cache: false,
					type: 'POST',
					dataType: 'json',
					url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_selfdefinedlist_lines";?>',
					data: _data,
					success: function(obj){
						$('.employees .table').html('');
						$('.employees .table').html(obj.html);
						// $('#popupeditboxcontent2').html('');
						// $('#popupeditboxcontent2').html(obj.html);
						// out_popup = $('#popupeditbox2').bPopup(out_popup_options);
						// $("#popupeditbox2:not(.opened)").remove();
					}
				});
			}
		<?php } ?>
	</script>
<?php } ?>
