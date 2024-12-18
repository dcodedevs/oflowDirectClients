<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
$page = 1;
require_once __DIR__ . '/list_btn.php';
$list_filter = isset($_GET['list_filter']) ? $_GET['list_filter'] : 'not_processed';

$creditor_id = $_GET['creditor_id'] ?? 0;
$collecting_company_case_id = $_GET['collecting_company_case_id'] ?? 0;
$s_sql = "SELECT creditor.id, creditor.companyname, cccc.collecting_company_case_id FROM creditor
JOIN creditor_collecting_company_chat cccc ON cccc.creditor_id = creditor.id
WHERE creditor.id = ? AND cccc.collecting_company_case_id = ?";
$o_query = $o_main->db->query($s_sql, array($creditor_id, $collecting_company_case_id));
$selected_creditor = ($o_query ? $o_query->row_array() : array());

$fwaFileuploadConfigs = array(
	array (
	  'module_folder' => 'CreditorChat', // module id in which this block is used
	  'id' => 'articleimageeuploadpopup',
	  'upload_type'=>'file',
	  'content_table' => 'creditor_collecting_company_chat',
	  'content_field' => 'files',
	  'content_id' => $cid,
	  'content_module_id' => $moduleID, // id of module
	  'dropZone' => 'block',
	  'callbackAll' => 'callBackOnUploadAll',
	  'callbackStart' => 'callbackOnStart',
	  'callbackDelete' => 'callbackOnDelete'
	),
);
$not_processed_count = 0;
$processed_count = 0;

$s_sql = "SELECT creditor.id, creditor.companyname FROM creditor
WHERE IFNULL(creditor.creditor_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND IFNULL(creditor_marked_ceases_to_exist_handled, 0) = 0";
$o_query = $o_main->db->query($s_sql);
$not_processed_count = ($o_query ? $o_query->num_rows() : 0);

$s_sql = "SELECT creditor.id, creditor.companyname FROM creditor
WHERE IFNULL(creditor.creditor_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND creditor_marked_ceases_to_exist_handled = 1";
$o_query = $o_main->db->query($s_sql);
$processed_count = ($o_query ? $o_query->num_rows() : 0);


// $s_sql = "SELECT cc.id, cc.stopped_date, ccc.created FROM collecting_cases cc
// JOIN creditor_transactions ct ON ct.collectingcase_id = cc.id
// JOIN collecting_company_cases ccc ON ccc.id = ct.collecting_company_case_id
// WHERE cc.stopped_date = '0000-00-00 00:00:00' AND ct.collecting_company_case_id > 0";
// $o_query = $o_main->db->query($s_sql);
// $casesToUpdate = ($o_query ? $o_query->result_array() : array());
// foreach($casesToUpdate as $caseToUpdate){
// 	$s_sql = "UPDATE collecting_cases SET stopped_date = ?, updatedBy='stopped fix' WHERE id = ?";
// 	$o_query = $o_main->db->query($s_sql, array($caseToUpdate["created"], $caseToUpdate['id']));
// }



// if($variables->loggID=="byamba@dcode.no") {

// 	$org_nrs_primary = array();
// 	$o_query = $o_main->db->query("SELECT publicRegisterId, customer_marked_ceases_to_exist_date,updatedBy FROM customer WHERE customer_marked_ceases_to_exist_reason='Not found in brreg' AND customer_ceases_to_exist_handled = 1 and publicRegisterId > 0  AND IFNULL(active_company_case_count, 0) = 0  AND customer_marked_ceases_to_exist_date <> '0000-00-00' AND updatedBy <> 'cease check2' LIMIT 100");
// 	$v_customers = $o_query ? $o_query->result_array() : array();
// 	$org_nrs_primary = array();
// 	foreach($v_customers as $v_customer){
// 		if(intval(trim($v_customer['publicRegisterId'])) > 0){
// 			$org_nrs_primary[] = trim($v_customer['publicRegisterId']);
// 		}
// 	}
// 	var_dump(count($org_nrs_primary));
// 	$org_nrs_primary = array_slice($org_nrs_primary, 0, 1000);
// 	// $markedAsBankrupt = 0;
// 	$org_nrs_chunk = array_chunk($org_nrs_primary, 100);
// 	// $customers_checked = 0;
// 	foreach($org_nrs_chunk as $org_nrs) {
// 		$ch = curl_init();
// 		curl_setopt($ch, CURLOPT_HEADER, 0);
// 		curl_setopt($ch, CURLOPT_VERBOSE, 0);
// 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// 		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
// 		curl_setopt($ch, CURLOPT_POST, TRUE);
// 		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
// 		curl_setopt($ch, CURLOPT_URL, 'https://brreg.getynet.com/brreg.php');
// 		$v_post = array(
// 			'organisation_no' => $org_nrs,
// 			'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
// 			'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
// 		);
// 		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
// 		$s_response = curl_exec($ch);

// 		$v_items = array();
// 		$v_response = json_decode($s_response, TRUE);
// 		$org_nr_update_array = array();
// 		$org_nr_found_array = array();
// 		if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
// 		{
// 			$v_items = $v_response['items'];
// 			foreach($v_items as $v_item) {
// 				$org_nr_found_array[] = $v_item['orgnr'];
// 				if($v_item['konkurs'] == "J") {
// 					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_reason='Konkurs'";
// 					$markedAsBankrupt++;
// 				} else if($v_item['tvangsavvikling'] == "J") {
// 					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_reason='Tvangsavvikling'";
// 					$markedAsBankrupt++;
// 				} else if($v_item['avvikling'] == "J") {
// 					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_reason='Avvikling'";
// 					$markedAsBankrupt++;
// 				} else {
// 					$org_nr_update_array[$v_item['orgnr']] = ",customer_marked_ceases_to_exist_date = '0000-00-00', customer_marked_ceases_to_exist_reason=''";
// 				}
// 			}			
// 		}
// 		foreach($org_nrs as $org_nr) {
// 			if(intval(trim($org_nr)) > 0) {
// 				$o_query = $o_main->db->query("SELECT customer.id, customer.creditor_id, customer.creditor_customer_id
// 				FROM customer 
// 				WHERE publicRegisterId = '".$o_main->db->escape_like_str($org_nr)."'");
// 				$v_customers = $o_query ? $o_query->result_array() : array();
// 				foreach($v_customers as $v_customer) {
// 					$sql_update = $org_nr_update_array[$org_nr];	
										
// 					$o_query = $o_main->db->query("UPDATE customer SET updated = NOW(), updatedBy='cease check2'".$sql_update." WHERE id = '".$o_main->db->escape_str($v_customer['id'])."'");
// 					if($o_query){
// 						$customers_checked++;
// 					}
// 				}
// 			}
// 		}
// 		var_dump($org_nr_found_array, $org_nr_update_array);
// 	}
// }
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="output-filter">
					<ul>
						<li class="item<?php echo ($list_filter == 'not_processed' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="not_processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=not_processed"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $not_processed_count; ?></span>
									<?php echo $formText_NotProcessed_output;?>
								</span>
							</a>
						</li>
						<li class="item<?php echo ($list_filter == 'processed' ? ' active':'');?>">
							<a class="topFilterlink" data-listfilter="processed" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=processed"; ?>">
								<span class="link_wrapper">
									<span class="count"><?php echo $processed_count; ?></span>
									<?php echo $formText_Processed_output;?>
								</span>
							</a>
						</li>
					</ul>
				</div>
				<div class="creditor_list">
					<table class="table">
						<tr>
							<td><?php echo $formText_CreditorId_output;?></td>
							<td><?php echo $formText_CreditorName_output;?></td>
							<td width="80px"><?php echo $formText_CreditorMarkedCeasesToExistDate_output;?></td>
							<td><?php echo $formText_Reason_output;?></td>
							<td width="250xp"><?php echo $formText_Note_output;?></td>
							<td width="50px"><?php echo $formText_ActiveCollectingCompanyCases_output;?></td>
							<td></td>
						</tr>
						<?php 
						if($list_filter == "processed"){
							$s_sql = "SELECT creditor.id, creditor.companyname, creditor.creditor_marked_ceases_to_exist_date,
							creditor.creditor_marked_ceases_to_exist_reason,creditor.creditor_marked_ceases_to_exist_note FROM creditor
							WHERE IFNULL(creditor.creditor_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND IFNULL(creditor_marked_ceases_to_exist_handled, 0) = 1
							ORDER BY creditor_marked_ceases_to_exist_handled_date DESC";
							$o_query = $o_main->db->query($s_sql);
							$creditors = ($o_query ? $o_query->result_array() : array());
						} else {
							$s_sql = "SELECT creditor.id, creditor.companyname, creditor.creditor_marked_ceases_to_exist_date,
							creditor.creditor_marked_ceases_to_exist_reason,creditor.creditor_marked_ceases_to_exist_note
							FROM creditor
							WHERE IFNULL(creditor.creditor_marked_ceases_to_exist_date, '0000-00-00') != '0000-00-00' AND IFNULL(creditor_marked_ceases_to_exist_handled, 0) = 0";
							$o_query = $o_main->db->query($s_sql);
							$creditors = ($o_query ? $o_query->result_array() : array());
						}
						$active_collecting_company_array = array();
						$v_creditor_ids = array();
						foreach($creditors as $creditor) {
							$v_creditor_ids[] = $creditor['id'];
						}						
						if($v_creditor_ids > 0){
							$s_sql = "SELECT collecting_company_cases.id,collecting_company_cases.creditor_id
							FROM collecting_company_cases
							WHERE collecting_company_cases.creditor_id IN (".implode(",", $v_creditor_ids).") AND IFNULL(case_closed_date, '0000-00-00') = '0000-00-00'";
							$o_query = $o_main->db->query($s_sql);
							$v_active_collecting_company_cases = ($o_query ? $o_query->result_array() : array());
							foreach($v_active_collecting_company_cases as $v_active_collecting_company_case) {
								$active_collecting_company_array[$v_active_collecting_company_case['creditor_id']]++;
							}
						}

						foreach($creditors as $creditor) {
							$active_collecting_company_case_count = intval($active_collecting_company_array[$creditor['id']]);
							?>
							<tr>
								<td><?php echo $creditor['id'];?></td>
								<td><?php echo $creditor['companyname'];?></td>
								<td><?php echo date("d.m.Y", strtotime($creditor['creditor_marked_ceases_to_exist_date']));?></td>
								<td><?php echo $creditor['creditor_marked_ceases_to_exist_reason'];?></td>
								<td>
									<?php if($list_filter == "processed") { 
										echo $creditor['creditor_marked_ceases_to_exist_handled_by']."<br/>";
										if($creditor['creditor_marked_ceases_to_exist_handled_date'] != "0000-00-00" && $creditor['creditor_marked_ceases_to_exist_handled_date'] != ""){
											echo date("d.m.Y H:i:s", strtotime($creditor['creditor_marked_ceases_to_exist_handled_date']))."<br/>";
										}
									} ?>
									<?php echo nl2br($creditor['creditor_marked_ceases_to_exist_note']);?>
								</td>
								<td><?php echo $active_collecting_company_case_count;?></td>
								<td>
									<a class="marked_as_handled" href="#" data-id="<?php echo $creditor['id'];?>"><span class="glyphicon glyphicon-pencil"></span></a>
								</td>
							</tr>
							<?php
						}
						?>
					</table>
						
					<?php if($totalPages > 1) {
						$currentPage = $current_page;
						$pages = array();
						array_push($pages, 1);
						if(!in_array($currentPage, $pages)){
							array_push($pages, $currentPage);
						}
						if(!in_array($totalPages, $pages)){
							array_push($pages, $totalPages);
						}
						for ($y = 10; $y <= $totalPages; $y+=10){
							if(!in_array($y, $pages)){
								array_push($pages, $y);
							}
						}
						for($x = 1; $x <= 5;$x++){
							$prevPage = $current_page - $x;
							$nextPage = $current_page + $x;
							if($prevPage > 0){
								if(!in_array($prevPage, $pages)){
									array_push($pages, $prevPage);
								}
							}
							if($nextPage <= $totalPages){
								if(!in_array($nextPage, $pages)){
									array_push($pages, $nextPage);
								}
							}
						}
						asort($pages);
						?>
						<?php foreach($pages as $page) {?>
							<a href="#" data-page="<?php echo $page?>" class="page-link<?php if($current_page == $page) echo ' active';?>"><?php echo $page;?></a>
						<?php } ?>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<style>
	.creditor_selected_block {
		padding: 5px;
	}
	.creditor_selecting_wrapper {
		width: 25%;
		float: left;
	}
	.creditor_selecting_block {
		height: 600px;
		overflow-y: scroll;
	}
	.creditor_message_wrapper {
		width: 75%;
		float: right;
	}
	.creditor_chat_wrapper {
		margin-left: 15px;
		margin-right: 15px;
	}
	.creditor_chat_wrapper .creditor_chat {
		width: 100%;
	}
	.creditor_selection {
		padding: 5px;
		background: #fff;
		border: 1px solid #cecece;
		cursor: pointer;
	}
	.creditor_selection.active {
		background: #46b2e2;
		color: #fff;
	}
	.creditor_message_wrapper .send_message {
		display: inline-block;
		border: none;
		border-radius: 4px;
		padding: 5px 10px;
		color: #FFF;
		background: #124171;
		outline: none;
		margin-top: 10px;
		cursor:pointer;
	}
	.creditor_chat_messages {
		margin-top: 10px;
		padding: 5px 15px;
	}
	.creditor_chat_messages .chat_message {
		display: block;
	    margin-bottom: 10px;	
		margin-top: 5px;
		float: left;
		width: 65%;
		text-align: left;		
	}
	.creditor_chat_messages .chat_message_info {
		border: 1px solid #ddd;
	    border-radius: 5px;
		word-break: break-all;
	    padding: 5px 7px;
		background: #6edaed;
	}
	.creditor_chat_messages .chat_message.from_oflow {
	    float: right;
		text-align: right;
	}
	.creditor_chat_messages .chat_message.from_oflow .chat_message_info{
	    background: #f0f0f0;
	}
	.creditor_chat_messages .message_info {
		color: #bbbbbb;
	}
	.show_case {
		margin-left: 10px;
	}
	.screenshot-view {
	    display: inline-block;
	    vertical-align: top;
	    width: 50px;
	    margin-right: 10px;
	    border: 1px solid #cecece;
	    cursor: pointer;
	}
	.screenshot-view img {
	    width: 100%;
	}
	.unread_indicator {
		background-color: red;
		width: 6px;
		height: 6px;
		display: inline-block;
		margin-right: 5px;
		border-radius: 10px;
		vertical-align: middle;;
	}
</style>

<?php if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'all'; } ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
                var data = {
					list_filter: '<?php echo $list_filter;?>'
                };
				loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};

function callBackOnUploadAll(data) {
	// updatePreview();
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_Send; ?>').prop('disabled',false);

};
function callbackOnStart(data) {
    $('.creditor_message_wrapper .send_message').val('<?php echo $formText_UploadInProgress_output;?>...').prop('disabled',true);
};
function callbackOnDelete(data){
	// updatePreview();
}
$(function(){
	$(".creditor_selection").off("click").on("click", function(){
		var data = {
			creditor_id: $(this).data("creditor-id"),
			collecting_company_case_id: $(this).data('collecting_company_case_id-id')
		}
		loadView("list", data);
	})
	$(".fancybox").fancybox();
	$(".send_message").off("click").on("click", function(){
        var data = {
			creditor_id: '<?php echo $selected_creditor['id']?>',
			collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id']?>',
			message: $(".creditor_chat").val()
        };
		var formdata = $(".messageForm").serializeArray();
		var data = {};
		$(formdata ).each(function(index, obj){
			if(data[obj.name] != undefined) {
				if(Array.isArray(data[obj.name])){
					data[obj.name].push(obj.value);
				} else {
					data[obj.name] = [data[obj.name], obj.value];
				}
			} else {
				data[obj.name] = obj.value;
			}
		});

		$.ajax({
			url: $(".messageForm").attr("action"),
			cache: false,
			type: "POST",
			dataType: "json",
			data: data,
			success: function (data) {
				fw_loading_end();					
				var data = {
					creditor_id: '<?php echo $selected_creditor['id'];?>',
					collecting_company_case_id: '<?php echo $selected_creditor['collecting_company_case_id'];?>',
				};
				loadView("list", data);
			}
		}).fail(function() {
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
			fw_loading_end();
		});

	})
	$(".marked_as_handled").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data("id")
		};
		ajaxCall({module_file: 'mark_as_handled', module_folder: 'output'}, data, function(json) {
			
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();	
		});
	})
})
</script>