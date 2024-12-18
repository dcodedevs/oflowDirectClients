<?php
if(isset($_SESSION['list_filter'])){ $list_filter = $_SESSION['list_filter']; } else { $list_filter = 'all'; }
if(isset($_SESSION['search_filter'])){ $search_filter = $_SESSION['search_filter']; } else { $search_filter = ''; }
//if(isset($_SESSION['search_by'])){ $search_by = $_SESSION['search_by']; } else { $search_by = 1; }
$sublist_filter = isset($_GET['sublist_filter']) ? $_GET['sublist_filter'] : 'contactperson';

// List btn
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$cid = (int)$_GET['cid'];

$s_sql = "SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($cid)."'";
$o_query = $o_main->db->query($s_sql);
$v_creditor = ($o_query && $o_query->num_rows()>0) ? $o_query->row_array() : array();

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$v_creditor['id'];
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

//require_once("fnc_getMaxDecimalAmount.php");

$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.id = ?";
$o_query = $o_main->db->query($s_sql, array($v_creditor['creditor_reminder_default_profile_id']));
$default_creditor_profile_person = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.id = ?";
$o_query = $o_main->db->query($s_sql, array($v_creditor['creditor_reminder_default_profile_for_company_id']));
$default_creditor_profile_company = ($o_query ? $o_query->row_array() : array());

$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}

require_once __DIR__ . '/functions.php';
$perPage = $_SESSION['listpagePerPage'];
$page = $_SESSION['listpagePage'];
if($perPage > 0 && $page > 0){
    $listPage = 1;
    $listPagePer = $page*$perPage;
    $customerList = get_customer_list($o_main, $list_filter, $search_filter, $listPage, $listPagePer, $cid);
    $prevCustomer = $customerList[0];
    $nextCustomer = $customerList[2];
    $nextId = $nextCustomer['id'];
    $prevId = $prevCustomer['id'];
}

$s_prev_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$prevId;
$s_next_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&inc_obj=details&cid=".$nextId;

/*$s_sql = "SELECT * FROM customer_view_history WHERE customer_view_history.username = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
$customer_view_history = $o_query ? $o_query->row_array() : array();
if($customer_view_history) {
	$customerHistory = json_decode($customer_view_history['history_log'], true);
	$newHistoryList = array();
	$newCount = 0;
	$customerHistoryOrdered = array_reverse($customerHistory);
	foreach($customerHistoryOrdered as $customerHistoryItem) {
		if($customerHistoryItem['id'] != $cid){
			if($newCount < 19){
				$newCount++;
				$newHistoryList[] = $customerHistoryItem;
			}
		}
	}
	$newHistoryList = array_reverse($newHistoryList);
	$newHistoryList[] = array("id"=>$cid, "time"=>date("d.m.Y H:i:s", time()));

	$s_sql = "UPDATE customer_view_history SET updated = NOW(), history_log = ? WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array(json_encode($newHistoryList), $customer_view_history['id']));
} else {
	$customerHistory = array();
	$customerHistory[] = array("id"=>$cid, "time"=>date("Y-m-d H:i:s", time()));
	$s_sql = "INSERT INTO customer_view_history SET username = ?, created = NOW(), history_log = ?";
	$o_query = $o_main->db->query($s_sql, array($variables->loggID, json_encode($customerHistory)));
}


$s_sql = "SELECT * FROM customer_view_history WHERE customer_view_history.username = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
$customer_view_history = $o_query ? $o_query->row_array() : array();
$customerHistory = json_decode($customer_view_history['history_log'], true);
$customerHistoryOrdered = array_reverse($customerHistory);*/
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">

		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_pagePreDetail">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>
					<?php /*if(count($customerHistoryOrdered) > 0) { ?>
						<div class="fas fa-history historyList hoverEye"><div class="hoverInfo">
			                <div class="historyListTitle"><?php echo $formText_LastViewedCustomerCards_output;?></div>
			                <table clasas="gtable" style="width: 100%;">
			                    <?php foreach($customerHistoryOrdered as $customerHistoryItem) {
			                        $s_sql = "SELECT * FROM customer WHERE customer.id = ?";
			                        $o_query = $o_main->db->query($s_sql, array($customerHistoryItem['id']));
			                        $historyCustomer = $o_query ? $o_query->row_array() : array();
			                        ?>
			                        <tr class="gtable_row output-click-helper" data-href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output";?>&inc_obj=details&cid=<?php echo $customerHistoryItem['id'];?>">
			                            <td class="gtable_cell"><?php echo $historyCustomer['name']." ".$historyCustomer['lastname'];?></td>
			                            <td class="gtable_cell timeColor"><?php echo $customerHistoryItem['time'];?></td>
			                        </tr>
			                    <?php } ?>
			                </table>
			            </div></div>
					<?php }*/ ?>
                    <?php if(intval($nextId) > 0){ ?>
                        <a href="<?php echo $s_next_link;?>" class="output-click-helper optimize next-link"><?php echo $formText_Next_outpup;?></a>
                    <?php } ?>
                    <?php if(intval($prevId) > 0){ ?>
                        <a href="<?php echo $s_prev_link;?>" class="output-click-helper optimize prev-link"><?php echo $formText_Prev_outpup;?></a>
                    <?php } ?>
                    <div class="clear"></div>
                </div>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle dropdown_content_show show_customerDetails" data-blockid="1">
                        <?php echo $formText_CreditorDetails_Output;?>
                        <span class="customerName"><?php echo $v_creditor['companyname'];?></span>

                        <div class="showArrow"><span class="glyphicon <?php if(!$customer_basisconfig['collapseCustomerDetails']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
						<div class="clear"></div>
                    </div>
					<div class="p_contentBlock p_contentBlockContent no-vertical-padding dropdown_content" <?php if(!$customer_basisconfig['collapseCustomerDetails']){ ?> style="display: block;" <?php } ?>>

						<?php if(1==0 && $v_creditor['content_status'] == 2) { ?>
							<?php if($moduleAccesslevel > 10) { ?>
								<button style="float: right; margin-top: -13px;" class="output-activate-customer output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_ActivateCreditor_output;?></button>
							<?php } ?>
						<?php } else { ?>
							<?php if(1==0 && $moduleAccesslevel > 10) { ?>
								<button style="float: right; margin-top: -13px;" class="output-delete-customer output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_DeleteCreditor_output;?></button>
							<?php } ?>
						<?php } ?>
						<div class="customerDetails">
							<table class="mainTable" width="50%"  border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="txt-label"><?php echo $formText_Name_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companyname'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_PublicRegisterNumber_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companyorgnr'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_Phone_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companyphone'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_Email_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companyEmail'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_CusotmerId_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['customer_id'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_SenderName_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['sender_name'];?></td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_SenderEmail_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['sender_email'];?></td>
								</tr>
							</table>
							<table class="mainTable" width="50%"  border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="txt-label border-left" colspan="2"><?php echo $formText_CompanyAddress_output; ?></td>
								</tr>
								<tr>
									<td class="txt-label border-left"><?php echo $formText_Street_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companypostalbox'];?></td>
								</tr>
								<tr>
									<td class="txt-label border-left"><?php echo $formText_PostalNumber_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companyzipcode'];?></td>
								</tr>
								<tr>
									<td class="txt-label border-left"><?php echo $formText_City_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['companypostalplace'];?></td>
								</tr>
								<?php /*?><tr>
									<td class="txt-label border-left"><?php echo $formText_Country_output;?></td>
									<td class="txt-value"><?php echo $v_creditor['paCountry'];?></td>
								</tr><?php */?>
							</table>
							<br/>
							<br/>
							<div class="clear"></div>
							</div>
						</div>
						<div class="p_contentBlock">
							<table class="mainTable fullTable" style="margin-top: 10px;" width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="txt-label"><?php echo $formText_DefaultProcessForPerson_output;?></td>
									<td class="txt-value"><?php echo $default_creditor_profile_person['name'];?></td>
									<td class="btn-edit" colspan="2">
									</td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_DefaultProcessForCompany_output;?></td>
									<td class="txt-value"><?php echo $default_creditor_profile_company['name'];?></td>
									<td class="btn-edit" colspan="2">
									</td>
								</tr>
							</table>
							<table class="mainTable fullTable" width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="txt-label"></td>
									<td class="txt-value"></td>
									<td class="btn-edit" colspan="2">
										<?php if($moduleAccesslevel > 10) { ?><button class="output-edit-customer-detail output-btn editBtnIcon" data-creditor-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>

									</td>
								</tr>
							</table>

						</div>
					</div>

					<?php
					$s_sql_select = "COUNT(id) AS cnt";
					$s_sql_group_by = "";
					if($sublist_filter == 'contactperson')
					{
						$s_sql_select = "*";
						$s_sql_group_by = "";
					}
					$o_query = $o_main->db->query("SELECT ".$s_sql_select." FROM creditor_contact_person WHERE creditor_id = '".$o_main->db->escape_str($cid)."'".$s_sql_group_by);
					$v_contactpersons = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					$l_contactperson_count = (int)($sublist_filter == 'contactperson' ? count($v_contactpersons) : $v_contactpersons[0]['cnt']);

					$s_sql_select = "COUNT(cc.id) AS cnt";
					$s_sql_group_by = "";
					if($sublist_filter == 'cases')
					{
						$s_sql_select = "cc.*, c.name";
						$s_sql_group_by = " ORDER BY cc.created DESC";
					}
					$s_sql = "SELECT ".$s_sql_select."
					FROM collecting_cases AS cc
					LEFT JOIN customer AS c ON c.id = cc.debitor_id
					WHERE cc.creditor_id = '".$o_main->db->escape_str($cid)."'".$s_sql_group_by;
					$o_query = $o_main->db->query($s_sql);
					$v_collecting_cases = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					$l_cases_count = (int)($sublist_filter == 'cases' ? count($v_collecting_cases) : $v_collecting_cases[0]['cnt']);

					$s_sql_select = "COUNT(ccc.id) AS cnt";
					$s_sql_group_by = "";
					if($sublist_filter == 'company_cases')
					{
						$s_sql_select = "ccc.*, c.name";
						$s_sql_group_by = " ORDER BY ccc.created DESC";
					}
					$s_sql = "SELECT ".$s_sql_select."
					FROM collecting_company_cases AS ccc
					LEFT JOIN customer AS c ON c.id = ccc.debitor_id
					WHERE ccc.creditor_id = '".$o_main->db->escape_str($cid)."'".$s_sql_group_by;
					$o_query = $o_main->db->query($s_sql);
					$v_collecting_company_cases = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					$l_company_cases_count = (int)($sublist_filter == 'company_cases' ? count($v_collecting_company_cases) : $v_collecting_company_cases[0]['cnt']);


					$s_sql = "SELECT crcp.*, ccp.available_for, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name
					FROM creditor_reminder_custom_profiles crcp
					LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
					LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
					WHERE crcp.creditor_id = ? AND crcp.content_status < 2 ORDER BY ccp.sortnr ASC";
					$o_query = $o_main->db->query($s_sql, array($cid));
					$v_processes = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					$l_processes_count = (int)count($v_processes);
					$v_person_processes = array();
					$v_company_processes = array();
					foreach($v_processes as $v_process) {
						if($v_process['available_for'] == 1){
							$v_person_processes[] = $v_process;
						} else if($v_process['available_for'] == 2){
							$v_company_processes[] = $v_process;
						}
					}

					$l_letter_types_count = 0;
					$s_sql = "SELECT * FROM creditor_collecting_company_letter_type_text AS cccltt
					WHERE cccltt.creditor_id = '".$o_main->db->escape_str($cid)."'";
					$o_query = $o_main->db->query($s_sql);
					$l_letter_types_count = ($o_query && $o_query->num_rows()>0) ? $o_query->num_rows() : 0;
					?>
					<div class="output-filter">
						<ul>
							<li class="item<?php echo ($sublist_filter == 'contactperson' ? ' active':'');?>">
								<a class="topFilterlink optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&sublist_filter=contactperson&inc_obj=details&cid=".$cid; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $l_contactperson_count; ?></span>
										<?php echo $formText_Contactpersons_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($sublist_filter == 'cases' ? ' active':'');?>">
								<a class="topFilterlink optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&sublist_filter=cases&inc_obj=details&cid=".$cid; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $l_cases_count; ?></span>
										<?php echo $formText_Cases_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($sublist_filter == 'company_cases' ? ' active':'');?>">
								<a class="topFilterlink optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&sublist_filter=company_cases&inc_obj=details&cid=".$cid; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $l_company_cases_count; ?></span>
										<?php echo $formText_CompanyCases_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($sublist_filter == 'processes' ? ' active':'');?>">
								<a class="topFilterlink optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&sublist_filter=processes&inc_obj=details&cid=".$cid; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $l_processes_count; ?></span>
										<?php echo $formText_Processes_output;?>
									</span>
								</a>
							</li>
							<li class="item<?php echo ($sublist_filter == 'letter_types' ? ' active':'');?>">
								<a class="topFilterlink optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&sublist_filter=letter_types&inc_obj=details&cid=".$cid; ?>">
									<span class="link_wrapper">
										<span class="count"><?php echo $l_letter_types_count; ?></span>
										<?php echo $formText_collectincompanyLettersAdditionalTexts_output;?>
									</span>
								</a>
							</li>

						</ul>
					</div>
                    <?php

                    if($sublist_filter == 'contactperson')
					{
						?>
						<div class="p_contentBlock">
							<div class="p_contentBlockTitle">
								<?php if($moduleAccesslevel > 10) { ?>
									<button id="output-add-contactpersons" class="addEntryBtn"><?php echo $formText_Add_output;?></button>
								<?php } ?>
							</div>
							<div class="p_contentBlockContent">
								<div id="output-contactpersons">
									<div class="contactpersonTableWrapper">
										<table class="table table-bordered table-striped">
											<tr>
												<th><?php echo $formText_Name_output;?></th>
												<th><?php echo $formText_MessagesRegardingCases_output;?></th>
												<th><?php echo $formText_ContactpersonForAgreement_output;?></th>
												<th><?php echo $formText_ReceiveSettlementReports_output;?></th>
												<th class="smallColumn actionWidth">&nbsp;</th>
											</tr>
											<?php
											foreach($v_contactpersons as $v_contactperson)
											{
												?>
												<tr>
													<td>
													<?php
														echo $v_contactperson['name'];
														if(!empty($v_contactperson['email'])) echo '<br/>'.$v_contactperson['email'];
														if(!empty($v_contactperson['phone'])) echo '<br/>'.$v_contactperson['phone'];
														if(!empty($v_contactperson['position'])) echo '<br/>'.$v_contactperson['position'];
													?>
													</td>
													<td><input type="checkbox" disabled readonly <?php if($v_contactperson['messages_regarding_cases']) echo 'checked';?>></td>
													<td><input type="checkbox" disabled readonly <?php if($v_contactperson['contactperson_for_agreement']) echo 'checked';?>></td>
													<td><input type="checkbox" disabled readonly <?php if($v_contactperson['receive_settlement_reports']) echo 'checked';?>></td>
													<td class="smallColumn actionWidth">
														<?php if($moduleAccesslevel > 10) { ?>
														<button class="editEntryBtn output-edit-contactperson" data-cid="<?php echo $v_contactperson['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button>
														<button class="editEntryBtn output-delete-contactperson" data-cid="<?php echo $v_contactperson['id'];?>" data-delete-msg="<?php echo $formText_DeleteContactperson_Output.": ".$v_contactperson['name'];?>?"><span class="glyphicon glyphicon-trash"></span></button>
														<?php } ?>
													</td>
												</tr>
												<?php
											}
											?>
										</table>
									</div>
								</div>
							</div>
						</div>
						<?php
					}

					if($sublist_filter == 'cases')
					{
						?>
						<div class="p_contentBlock">
							<div class="p_contentBlockContent" >
								<table class="table table-bordered table-striped">
									<tr>
										<th><?php echo $formText_CaseNumber_Output;?></th>
										<th><?php echo $formText_CustomerName_Output;?></th>
										<th><?php echo $formText_Mainclaim_Output;?></th>
										<th><?php echo $formText_Currency_Output;?></th>
										<th><?php echo $formText_Status_Output;?></th>
										<th><?php echo $formText_DueDate_Output;?></th>
									</tr>
									<?php
									foreach($v_collecting_cases as $v_row)
									{
										$s_sql = "SELECT * FROM creditor_transactions WHERE content_status < 2 AND collectingcase_id = ? ORDER BY created DESC";
										$o_query = $o_main->db->query($s_sql, array($v_row['id']));
										$invoice = ($o_query ? $o_query->row_array() : array());

										if(intval($v_row['status']) == 0){
											$v_row['status'] = 1;
										}
										$s_sql = "SELECT * FROM collecting_cases_main_status_basisconfig WHERE id = ? ORDER BY id ASC";
										$o_query = $o_main->db->query($s_sql, array(intval($v_row['status'])));
										$collecting_case_status = ($o_query ? $o_query->row_array() : array());
										$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];


										$currencyName = "";
										if($invoice['currency'] == 'LOCAL') {
											$currencyName = " ".$v_creditor['default_currency'];
										} else {
											$currencyName = " ".$invoice['currency'];
										}
										?>
										<tr>
											<td><a href="<?php echo $s_edit_link;?>" class="optimize"><?php echo $v_row['id']; ?></a></td>
											<td><?php echo $v_row['name'];?></td>
											<td><?php echo number_format($v_row['original_main_claim'], 2, ",", "");?></td>
											<td><?php echo $currencyName;?></td>
											<td><?php
											if($invoice['open'] == 0) {
												echo $formText_Closed_output;
											} else {
												echo $collecting_case_status['name'];
											}
											?></td>
											<td><?php if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['due_date'])); }?></td>
										</tr>
										<?php
									}
									?>
								</table>
							</div>
						</div>
						<?php
					}
					if($sublist_filter == 'company_cases')
					{
					?>
					<div class="p_contentBlock">
						<div class="p_contentBlockContent">
							<table class="table table-bordered table-striped">
								<tr>
									<th><?php echo $formText_CaseNumber_Output;?></th>
									<th><?php echo $formText_CustomerName_Output;?></th>
									<th><?php echo $formText_Status_Output;?></th>
									<th><?php echo $formText_DueDate_Output;?></th>
								</tr>
								<?php
								foreach($v_collecting_company_cases as $v_row)
								{
									$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
									?>
									<tr>
										<td><a href="<?php echo $s_edit_link;?>" class="optimize"><?php echo $v_row['id']; ?></a></td>
										<td><?php echo $v_row['name'];?></td>
										<td><?php
											if($v_row['collecting_case_surveillance_date'] != '0000-00-00' && $v_row['collecting_case_surveillance_date'] != ''){
												if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
													echo $formText_Surveillance_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_surveillance_date'])).")";
												} else {
													echo $formText_ClosedInSurveillance_output;
												}
											} else if($v_row['collecting_case_manual_process_date'] != '0000-00-00' && $v_row['collecting_case_manual_process_date'] != ''){
												if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
													echo $formText_ManualProcess_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_manual_process_date'])).")";
												} else {
													echo $formText_ClosedInManualProcess_output;
												}
											} else if($v_row['collecting_case_created_date'] != '0000-00-00' && $v_row['collecting_case_created_date'] != ''){
												if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
													echo $formText_CollectingLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['collecting_case_created_date'])).")";
												} else {
													echo $formText_ClosedInCollectingLevel_output;
												}
											} else if($v_row['warning_case_created_date'] != '0000-00-00' && $v_row['warning_case_created_date'] != '') {
												if(($v_row['case_closed_date'] == "0000-00-00" OR $v_row['case_closed_date'] == "")){
													echo $formText_WarningLevel_output." (".$formText_Started_output." ".date("d.m.Y", strtotime($v_row['warning_case_created_date'])).")";
												} else {
													echo $formText_ClosedInWarningLevel_output;
												}
											}
										?></td>
										<td><?php if($v_row['due_date'] != "0000-00-00" && $v_row['due_date'] != ""){ echo date("d.m.Y", strtotime($v_row['due_date'])); }?></td>
									</tr>
									<?php
								}
								?>
							</table>
						</div>
					</div><?php
					}
					if($sublist_filter == 'processes')
					{

						function show_processes($v_process){
							global $o_main;
							global $formText_Customized_output;
							global $formText_Default_output;
							global $formText_PdfText_Output;
							global $formText_None_output;
							global $formText_AddFee_Output;
							global $formText_Yes_output;
							global $formText_No_output;
							global $formText_ReminderAmount_Output;
							global $formText_MainClaimFrom_Output;
							global $formText_Amount_Output;
							global $formText_AddInterest_Output;
							global $formText_DaysAfterDueDate_Output;
							global $formText_AddNumberOfDaysToDueDate_Output;
							global $formText_SendingAction_Output;
							global $formText_SendLetter_output;
							global $formText_SendEmailIfEmailExistsOrElseLetter_output;
							global $formText_SendSmsIfMobileExistsOrEmailOrElseLetter_output;
							global $formText_CustomSmsText_output;
							global $formText_ShowCollectingCompanyLogo_Output;
							global $formText_CreditorSpecified_output;
							global $formText_OflowSpecified_output;
							global $formText_CasesConnectedToProcess_output;
							global $formText_DaysAfterDueDate_output;
							global $formText_ProccessToMoveTo_output;

							$s_sql = "SELECT * FROM collecting_cases_pdftext WHERE content_status < 2 ORDER BY sortnr ASC";
							$o_query = $o_main->db->query($s_sql);
							$pdfTexts = $o_query ? $o_query->result_array() : array();

							$s_sql = "SELECT * FROM collecting_cases_emailtext WHERE content_status < 2 ORDER BY sortnr ASC";
							$o_query = $o_main->db->query($s_sql);
							$emailTexts = $o_query ? $o_query->result_array() : array();
							
							$s_sql = "SELECT * FROM collecting_cases_process_steps WHERE collecting_cases_process_id = ? ORDER BY sortnr ASC";
							$o_query = $o_main->db->query($s_sql, array($v_process['reminder_process_id']));
							$old_steps = ($o_query ? $o_query->result_array() : array());
							$steps = array();
							foreach($old_steps as $old_step) {
								$s_sql = "SELECT * FROM collecting_cases_process_step_fees WHERE collecting_cases_process_step_id = ? ORDER BY mainclaim_from_amount ASC";
								$o_query = $o_main->db->query($s_sql, array($old_step['id']));
								$fees = ($o_query ? $o_query->result_array() : array());
								$old_step['fees'] = $fees;
								$steps[] = $old_step;
							}

							$profile_values = array();

							$s_sql = "SELECT * FROM creditor_reminder_custom_profile_values WHERE creditor_reminder_custom_profile_id = ?";
							$o_query = $o_main->db->query($s_sql, array($v_process['id']));
							$unprocessed_profile_values = $o_query ? $o_query->result_array() : array();

							foreach($unprocessed_profile_values as $unprocessed_profile_value) {
								$profile_values[$unprocessed_profile_value['collecting_cases_process_step_id']] = $unprocessed_profile_value;
							}
							$s_sql = "SELECT * FROM collecting_cases WHERE reminder_profile_id = ?";
							$o_query = $o_main->db->query($s_sql, array($v_process['id']));
							$cases_connected_count = ($o_query ? $o_query->num_rows() : 0);
							
							$s_sql = "SELECT * FROM collecting_cases_process WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($v_process['reminder_process_id']));
							$default_process = ($o_query ? $o_query->row_array() : 0);

							if($v_process['collecting_process_move_to'] > 0){
								$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($v_process['collecting_process_move_to']));
								$collecting_process = ($o_query ? $o_query->row_array() : 0);
							} else {								
								$s_sql = "SELECT * FROM collecting_cases_collecting_process WHERE id = ?";
								$o_query = $o_main->db->query($s_sql, array($default_process['collecting_process_move_to']));
								$collecting_process = ($o_query ? $o_query->row_array() : 0);
							}

							?>
							<div class="profile_block">
								<?php echo $v_process['name'];?>
								(<?php if($v_process['type'] == 0) { echo $formText_CreditorSpecified_output;} else if ($v_process['type'] == 1) { echo $formText_OflowSpecified_output;}?>)
								(<?php echo $cases_connected_count;?> <?php echo $formText_CasesConnectedToProcess_output;?>)
								<span class="glyphicon glyphicon-pencil edit_profile" data-profile-id="<?php echo $v_process['id']?>"></span>
								<span class="glyphicon glyphicon-trash deleteProfile" data-profile-id="<?php echo $v_process['id']?>"></span>
								<div>
									<div><b><?php echo $formText_DaysAfterDueDate_output;?></b>: <?php if($v_process['days_after_due_date_move_to_collecting'] > 0){ echo $v_process['days_after_due_date_move_to_collecting']; } else {echo $default_process['days_after_due_date_move_to_collecting'];}?></div>
									<div><b><?php echo $formText_ProccessToMoveTo_output;?></b>: <?php echo $collecting_process['name'];?></div>
								</div>
								<table class="profile_table">
									<tr>
										<th class="firstTd"></th>
										<?php
										foreach($steps as $step) {
											echo "<th>".$step['name']."</th><th class='spanTd'></th>";
										}
										?>
									</tr>
									<tr>
										<td class="firstTd"><b><?php echo $formText_PdfText_Output; ?></b></td>

										<?php
										foreach($steps as $step) {
											$stepPdfText = array();
											foreach($pdfTexts as $letter) {
												if($letter['id'] == $step['collecting_cases_pdftext_id']) {
													$stepPdfText = $letter;
												}
											}
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['pdftext_title'] != "" || $profile_value['pdftext_text'] != "") {
													echo $profile_value['pdftext_title'];
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													if($stepPdfText){
														echo $stepPdfText['name'];
													} else {
														echo $formText_None_output;
													}
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}?>

											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileText" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>

									<tr>
										<td class="firstTd"><b><?php echo $formText_AddFee_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['doNotAddFee'] > 0){
													if($profile_value['doNotAddFee'] != 2){
														echo $formText_Yes_output;
													} else {
														echo $formText_No_output;
													}
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													if(!$step['doNotAddFee']){
														echo $formText_Yes_output;
													} else {
														echo $formText_No_output;
													}
													$default_span =  " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>
											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileFee" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>

									<tr>
										<td class="firstTd"><b><?php echo $formText_ReminderAmount_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$fees = $step['fees'];

											$profile_value = $profile_values[$step['id']];
											$addingFee = false;
											if($profile_value['doNotAddFee'] > 0){
												if($profile_value['doNotAddFee'] != 2){
													$addingFee = true;
												} else {
												}
											} else {
												if(!$step['doNotAddFee']){
													$addingFee = true;
												} else {
												}
											}
											?>

											<td colspan="2">
												<?php
												if($addingFee){
													if($profile_value['reminder_amount_type'] == 0) {
														if($profile_value['reminder_amount'] > 0){
															echo number_format($profile_value['reminder_amount'], 2, ","," ");
															$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
														} else {
															echo number_format($step['reminder_amount'], 2, ","," ");
															$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
														}
														?>
														<div class="rightAligned" style="float: right;">
															<?php
															echo $default_span;
															?>
															<span class="glyphicon glyphicon-pencil editProfileAmount2" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
														</div>
														<?php
													} else {
														if(count($profile_value['fees']) > 0){
															?>
															<div class="rightAligned">
																<?php
																echo "<span class='customized_span'>".$formText_Customized_output." </span>";
																?>
																<span class="glyphicon glyphicon-pencil editProfileAmount2" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
															</div>
															<?php
															foreach($profile_value['fees'] as $fee) {
																?>
																	<div class="fee_preview_block">
																		<div class="line">
																			<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
																			<div class="lineInput">
																				<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " ");?>
																			</div>
																			<div class="clear"></div>
																		</div>
																		<div class="line">
																			<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
																			<div class="lineInput">
																				<?php echo number_format($fee['amount'], 2, ",", " ");?>
																			</div>
																			<div class="clear"></div>
																		</div>
																	</div>
																<?php
															}
														} else {
															?>
															<div class="rightAligned">
																<?php
																echo "<span class='default_span'>".$formText_Default_output." </span>";
																?>
																<span class="glyphicon glyphicon-pencil editProfileAmount2" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
															</div>
															<?php
															foreach($fees as $fee) {
																?>
																	<div class="fee_preview_block">
																		<div class="line">
																			<div class="lineTitle"><?php echo $formText_MainClaimFrom_Output; ?></div>
																			<div class="lineInput">
																				<?php echo number_format($fee['mainclaim_from_amount'], 2, ",", " ");?>
																			</div>
																			<div class="clear"></div>
																		</div>
																		<div class="line">
																			<div class="lineTitle"><?php echo $formText_Amount_Output; ?></div>
																			<div class="lineInput">
																				<?php echo number_format($fee['amount'], 2, ",", " ");?>
																			</div>
																			<div class="clear"></div>
																		</div>
																	</div>
																<?php
															}
														}
													}
												}
												?>
											</td>
											<?php /*
											<td>
												<?php
												if($profile_value['reminder_amount'] > 0){
													echo number_format($profile_value['reminder_amount'], 2, ","," ");
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													echo number_format($step['reminder_amount'], 2, ","," ");
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>
											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileAmount" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php */
										}
										?>
									</tr>
									<tr>
										<td class="firstTd"><b><?php echo $formText_AddInterest_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['doNotAddInterest'] > 0){
													if($profile_value['doNotAddInterest'] != 2){
														echo $formText_Yes_output;
													} else {
														echo $formText_No_output;
													}
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													if(!$step['doNotAddInterest']){
														echo $formText_Yes_output;
													} else {
														echo $formText_No_output;
													}
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>
											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileInterest" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>
									<tr>
										<td class="firstTd"><b><?php echo $formText_DaysAfterDueDate_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['days_after_due_date'] != ""){
													echo $profile_value['days_after_due_date'];
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													echo $step['days_after_due_date'];
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>
											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileDaysAfterDueDate" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>
									<tr>
										<td class="firstTd"><b><?php echo $formText_AddNumberOfDaysToDueDate_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['add_number_of_days_to_due_date'] != ""){
													echo $profile_value['add_number_of_days_to_due_date'];
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													echo $step['add_number_of_days_to_due_date'];
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>
											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileDaysToDueDate" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>
									<tr>
										<td class="firstTd"><b><?php echo $formText_SendingAction_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												$sending_action = $step['sending_action'];
												$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												if($profile_value['sending_action'] > 0){
													$sending_action = $profile_value['sending_action'];
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												}
												switch($sending_action){
													case 1:
														echo $formText_SendLetter_output;
													break;
													case 2:
														echo $formText_SendEmailIfEmailExistsOrElseLetter_output;
													break;
													case 4:
														echo $formText_SendSmsIfMobileExistsOrEmailOrElseLetter_output;
														echo '<div class="sms_extra_text">
															<b>'.$formText_CustomSmsText_output.':</b>
															<div>'.nl2br($profile_value['extra_text_in_sms']).'

															<span class="glyphicon glyphicon-pencil editProfileExtraSmsText" data-profile-id="'.$v_process['id'].'" data-step-id="'.$step['id'].'"></span>

															</div>
														</div>';
													break;
												}
												?>

											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileSending" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>

									<tr>
										<td class="firstTd"><b><?php echo $formText_ShowCollectingCompanyLogo_Output; ?></b></td>
										<?php
										foreach($steps as $step) {
											$profile_value = $profile_values[$step['id']];
											?>
											<td>
												<?php
												if($profile_value['show_collecting_company_logo'] > 0){
													if($profile_value['show_collecting_company_logo'] != 2){
														echo $formText_No_output;
													} else {
														echo $formText_Yes_output;
													}
													$default_span = " <span class='customized_span'>".$formText_Customized_output."</span>";
												} else {
													if($step['show_collecting_company_logo']){
														echo $formText_Yes_output;
													} else {
														echo $formText_No_output;
													}
													$default_span = " <span class='default_span'>".$formText_Default_output." </span>";
												}
												?>

											</td>
											<td class="spanTd"><?php
											echo $default_span;
											?><span class="glyphicon glyphicon-pencil editProfileShowLogo" data-profile-id="<?php echo $v_process['id']?>" data-step-id="<?php echo $step['id'];?>"></span>
											</td>
											<?php
										}
										?>
									</tr>

								</table>
							</div>
							<?php
						}
					?>
					<div class="p_contentBlock">
						<div class="p_contentBlockContent">
							<div class="person_processes_list">
								<span class="process_title"><?php echo $formText_PersonProcesses_output?></span> (<?php echo count($v_person_processes);?>)
								<span class="edit_profile" data-available-for="1"><?php echo $formText_AddProfileForPerson_output;?></span>
								<?php foreach($v_person_processes as $v_process){
									show_processes($v_process);
								}?>
							</div>
							<div class="company_processes_list">
								<span class="process_title"><?php echo $formText_CompanyProcesses_output?></span> (<?php echo count($v_company_processes);?>)
								<span class="edit_profile" data-available-for="2"><?php echo $formText_AddProfileForCompany_output;?></span>
								<?php foreach($v_company_processes as $v_process){
									show_processes($v_process);
								}?>
							</div>
						</div>
					</div>
					<?php
					} else if($sublist_filter == "letter_types") {						
						$s_sql = "SELECT cccltt.*, cclt.name  FROM creditor_collecting_company_letter_type_text AS cccltt
						LEFT OUTER JOIN collecting_company_letter_types cclt ON cclt.id = cccltt.collecting_company_letter_type_id
						WHERE cccltt.creditor_id = '".$o_main->db->escape_str($cid)."'";
						$o_query = $o_main->db->query($s_sql);
						$v_letter_types = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
						
						?>
						<div class="p_contentBlock">
							<div class="p_contentBlockTitle">
								<?php if($moduleAccesslevel > 10) { ?>
									<button id="output-add-type-text" class="addEntryBtn"><?php echo $formText_Add_output;?></button>
								<?php } ?>
							</div>
							<div class="p_contentBlockContent">	
								<div id="output-contactpersons">
									<div class="contactpersonTableWrapper">
										<table class="table table-bordered table-striped">
											<tr>
												<th><?php echo $formText_Name_output;?></th>
												<th><?php echo $formText_Text_output;?></th>
												<th class="smallColumn actionWidth">&nbsp;</th>
											</tr>
											<?php
											foreach($v_letter_types as $v_letter_type)
											{
												?>
												<tr>
													<td>
														<?php echo $v_letter_type['name'];?>
													</td>
													<td><?php echo nl2br($v_letter_type['text']);?></td>
													<td class="smallColumn actionWidth">
														<?php if($moduleAccesslevel > 10) { ?>
														<button class="editEntryBtn output-edit-type-text" data-cid="<?php echo $v_letter_type['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button>
														<button class="editEntryBtn output-delete-type-text" data-cid="<?php echo $v_letter_type['id'];?>" data-delete-msg="<?php echo $formText_DeleteContactperson_Output.": ".$v_contactperson['name'];?>?"><span class="glyphicon glyphicon-trash"></span></button>
														<?php } ?>
													</td>
												</tr>
												<?php
											}
											?>
										</table>
									</div>
								</div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
function formatDate($date, $monthType = false) {
    global $formText_NotSet_output;
    if ($date == '0000-00-00' || !$date || empty($date)) return $formText_NotSet_output;
    if($monthType){
        return date('m.Y', strtotime($date));
    } else {
        return date('d.m.Y', strtotime($date));
    }
}
?>
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
		$(this).removeClass('opened');
		$(this).removeClass('fixedWidth');
        $(this).find("#popupeditboxcontent").html("");
		if($(this).is('.close-reload')) {
			loadView("details", {cid:"<?php echo $cid;?>", sublist_filter:"<?php echo $sublist_filter;?>"});
        }
	}
};
var fileuploadPopupAC, fileuploadPopupOptionsAC={
	follow: [true, false],
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
	}
};
function output_load_contactpersons()
{
	var data = {
		customerId: <?php echo $cid;?>,
		<?php if(isset($_GET['contactpersonSearch']) && trim($_GET['contactpersonSearch']) != '') { ?>
		search: '<?php echo $_GET['contactpersonSearch']; ?>',
		<?php } ?>
		subunit_filter: $_GET['subunit_filter']
	};
	ajaxCall('contactpersons_list', data, function(json) {
		$("#output-contactpersons .contactpersonTableWrapper").html(json.html).slideDown();
	}, false);
}
$(function(){
    $("#p_container").off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
        if(e.target.nodeName == 'DIV' || e.target.nodeName == 'TD'){
            fw_load_ajax($(this).data('href'),'',true);
    		if($("body.alternative").length == 0) {
                var $scrollbar6 = $('.tinyScrollbar.col1');
                $scrollbar6.tinyscrollbar();

                var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
                scrollbar6.update(0);
            }
        }
    });
    $('.backToList').on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: '<?php echo $search_filter; ?>',
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });


	<?php if($moduleAccesslevel > 10) { ?>
    $(".output-delete-customer").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDeleteCustomer_output; ?>', function(result) {
            if (result) {
                var data = {
                    customerId: self.data('customer-id'),
                    action: 'deleteCustomer'
                };
                ajaxCall('editCustomerDetail', data, function(json) {
                    if(json.data == "warning"){
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                        $(window).trigger("resize");
                    } else {
                        loadView("details", {cid:"<?php echo $cid;?>"});
                    }
                });
            }
        });
    })

    $(".output-activate-customer").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmActivate_output; ?>', function(result) {
            if (result) {
                var data = {
                    customerId: self.data('customer-id'),
                    action: 'activateCustomer'
                };
                ajaxCall('editCustomerDetail', data, function(json) {
                    loadView("details", {cid:"<?php echo $cid;?>"});
                });
            }
        });
    })

	$(".output-edit-customer-detail").on('click', function(e){
		e.preventDefault();
		var data = {
			creditor_id: $(this).data('creditor-id')
		};
		ajaxCall({module_file: 'edit_creditor', module_folder: 'output'}, data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	$(".output-edit-contactperson, #output-add-contactpersons").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			contactperson_id: $(this).data("cid")
		};
		ajaxCall({module_name:"CreditorsOverview", module_file:'edit_creditor_contactperson', module_folder: "output"}, data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".output-delete-contactperson").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			contactperson_id: $(this).data("cid"),
			action: "delete"
		}
		bootbox.confirm($(this).data('delete-msg'), function(result) {
			if (result) {
				ajaxCall({module_name:"CreditorsOverview", module_file:'edit_creditor_contactperson', module_folder: "output"}, data, function(json) {
				    loadView("details", {cid:"<?php echo $cid;?>"});
				});
			}
		});
	})
	
	$(".output-edit-type-text, #output-add-type-text").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			cid: $(this).data("cid")
		};
		ajaxCall("edit_type_text", data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".output-delete-type-text").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			cid: $(this).data("cid"),
			action: "delete"
		}
		bootbox.confirm($(this).data('delete-msg'), function(result) {
			if (result) {
				ajaxCall("edit_type_text", data, function(json) {
				    loadView("details", {cid:"<?php echo $cid;?>", sublist_filter: "<?php echo $sublist_filter;?>"});
				});
			}
		});
	})
	

	<?php } ?>

    $(".dropdown_content_show").unbind("click").bind("click", function(e){
        var parent = $(this);
        if($(e.target).hasClass("dropdown_content_show") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
            var dropdown = parent.next(".p_contentBlockContent.dropdown_content");
            if(dropdown.is(":visible")) {
                dropdown.slideUp();
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                if(parent.hasClass("autoload")) {
                    dropdown.slideDown(0);
                    parent.removeClass("autoload");
                } else {
                    dropdown.slideDown();
                }
                parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }
        }
    })

	$(".edit_profile").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data('profile-id'),
			available_for: $(this).data("available-for")
		};
		ajaxCall({module_file: 'edit_profile'}, data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".editProfileText").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeText"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editProfileAmount").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeAmount"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".editProfileAmount2").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeAmount2"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".editProfileFee").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeFee"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".editProfileSending").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeSending"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editProfileExtraSmsText").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeSmsText"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editProfileInterest").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeInterest"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editProfileDaysAfterDueDate").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeAfterDays"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editProfileDaysToDueDate").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeBeforeDays"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".editProfileShowLogo").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			creditor_id: '<?php echo $cid;?>',
			profile_id: $(this).data("profile-id"),
			step_id: $(this).data("step-id"),
			action: "changeShowLogo"
		};
		ajaxCall('edit_profile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".deleteProfile").off("click").on("click", function(){
		var self = $(this);
		bootbox.confirm('<?php echo $formText_ConfirmDeleteProfile_output; ?>', function(result) {
            if (result) {
                var data = {
                    profile_id: self.data('profile-id'),
					creditor_id: '<?php echo $cid;?>',
                    action: 'deleteProfile'
                };
                ajaxCall('edit_profile', data, function(json) {
                    if(json.data == "warning"){
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                        $(window).trigger("resize");
                    } else {
                        loadView("details", {cid:"<?php echo $cid;?>", sublist_filter: "<?php echo $sublist_filter;?>"});
                    }
                });
            }
        });
	})
});
</script>
<style>
.process_title {
	font-size: 20px;
	font-weight: bold;
	margin-bottom: 10px;
}
.profile_block {
	margin-top: 15px;
}
.company_processes_list {
	margin-top: 20px;
	padding-top: 10px;
	border-top: 1px solid #cecece;
}
.rightAligned {
    text-align: right !important;
}
.open-connectToProject {
    color: #46b2e2;
    margin-left: 15px;
}
.connectToProject {
    position: absolute;
    background: #fff;
    padding: 5px 10px;
    border: 1px solid #cecece;
    cursor: pointer;
    right: 80px;
    display: none;
}
.edit_vat_free_area {
    color: #46b2e2;
    cursor: pointer;
    margin-left: 10px;
}
input[type='checkbox']:disabled {
    cursor: default;
}
input[type="checkbox"]:disabled + label {
    cursor: default !important;
}
.p_pageDetailsTitle .customerName {
    margin-left: 30px;
    font-weight: 300;
}
.p_pageDetails {
    border: 1px solid #cecece;
    border-bottom: 0;
}
.p_pagePreDetail {
    margin-bottom: 10px;
}
.p_pagePreDetail .prev-link {
    float: right;
    padding: 3px 10px;
}
.p_pagePreDetail .next-link {
    float: right;
    padding: 3px 10px;
}
.p_pageContent .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.p_pageContent .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.p_pageContent .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_pageContent .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.p_pageContent .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.p_pageContent .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.p_pageContent .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_pageContent .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_pageContent .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.show_invoices .statistics {
    float: right;
    font-weight: normal;
    font-size: 13px;
}

.addSubscriptionFileBtn {
    font-size: 12px;
    font-weight: normal !important;
    margin-left: 10px;
}
.addSubscriptionFileBtn  span {
    font-size: 7px;
    top: -1px;
}
.addSubscriptionFileNewBtn {
    font-size: 12px;
    font-weight: normal !important;
    margin-left: 10px;
}
.addSubscriptionFileNewBtn  span {
    font-size: 7px;
    top: -1px;
}
.subscription-seperate {
    padding: 10px 10px;
}
.renewSubscription {
    float: right;
    border-radius: 4px;
    font-size: 13px;
    line-height: 0px;
    padding: 15px 20px;
    font-weight: 700;
    background: #fff;
    cursor: pointer;
}
.back-to-list {
    float: left;
    display: block;
    margin-bottom: 10px;
}
.add-prospect-btn,
.edit-prospect-btn,
.delete-prospect-btn {
    cursor: pointer;
    color: #0284C9;
}
.showContactPoints {
    cursor: pointer;
}
.showDeletedOrders,
.showDeletedSubscriptions,
.showDeletedCollectingOrders,
.showStoppedSubscriptions {
    cursor: pointer;
    color: #0284C9;
}
.deletedOrders,
.deletedSubscriptions,
.deletedCollectingOrders {
    margin-top: 20px;
    display: none;
}
.externalInfoRowWrapper {
    display: inline-block;
    vertical-align: top;
}
.externalInfo {
    float: right;
    font-weight: normal !important;
}
.externalInfo .customerColumn {
    vertical-align: middle;
    text-align: left;
}
.externalInfo .externalIdColumn {
    text-align: right;
    padding: 0px 10px;
}
.externalInfo .externalActionColumn {

}
.previousSysIdWrapper {
	float: right;
	font-weight: normal;
}
.addEntryBtn.output-edit-external-customer-id {

}
.smallColumn.actionWidth {
    width: 80px;
}
.smallColumn.account_actions {
    width:150px;
}
.defaultCheckbox {
    position: relative !important;
    left: 0 !important;
}
.activitiesTitle {
    font-size: 14px;
}
.bold {
    font-weight: bold !important;
}
.editListDropdown {
    margin-left: 20px;
}
.resetListDropdown {
    margin-left: 15px;
}
.selfdefinedTable {
    border: 1px solid #ddd;
}
.selfdefinedTable td {
    padding: 0px 10px;
}
.selfdefinedTable tr:nth-child(odd) {
    /*background: #f9f9f9;*/
}
.selfdefinedTable td {
    border-bottom: 1px solid #ddd;
}
.selfchkWrapper {
    margin-top: 5px;
}
.editEntryBtn {
    color: #46b2e2;
    cursor: pointer;
    display: inline-block;
    vertical-align: middle;
    background: none;
    border: 0;
}
.editEntryBtn.output-delete-external-customer-id {
    padding-right: 0;
}
.labelText {
    font-weight: normal;
    margin-left: 5px;
    vertical-align: middle;
    cursor: pointer;
}
.selfdefinedFieldValueWrapper {
    display: none;
}
.preeditBlock.inactive {
    color: grey;
}
.preeditBlock.inactive .editBtn {
    display: none;
}
.preeditBlock.inactive .noValueSpan {
    display: none;
}
.smallColumn {
    width: 5%;
}
.smallColumn .output-btn {
    margin-left: 0;
}
.contactPerson .glyphicon {
    margin-right: 5px;
}
.default-selfdefined-company {
	font-size:12px;
	font-weight:normal;
	margin:0 10px;
}
.default-selfdefined-company label {
	font-weight:normal;
	margin-right:5px;
}
#output-contactpersons .employeeImage {
    width: 30px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 5px;
}
#output-contactpersons .employeeImage img {
    width: 100%;
}
#output-contactpersons .output-access-loader .output-access-changer {
    margin-right: -10px;
}
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
input.error { border-color:#c11; }
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
#bookmeetingroom input.popupforminput, #bookmeetingroom textarea.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:5px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:none;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}
#output-contactpersons {}
#output-contactpersons hr {
	border-style:dashed;
	border-color: #cecece;
}
#p_container #output-contactpersons .main-info td {
	padding:0 0 5px 0;
}
#p_container #output-contactpersons .main-info .padding-top td {
	padding-top:5px;
}
#output-contactpersons .main-info {
	padding:0 10px;
	border:1px solid #aadfff;
	border-bottom:none;
	background-color:#f6fbff;
}
#output-contactpersons .output-access {
	padding:5px 10px;
	border:1px solid #cecece;
	background-color:#ffffff;
    background: -moz-linear-gradient(53deg, rgba(255,225,213,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* ff3.6+ */
    background: -webkit-gradient(linear, left bottom, right top, color-stop(0%, rgba(255,225,213,1)), color-stop(49%, rgba(255,255,255,0.51)), color-stop(100%, rgba(255,255,255,0))); /* safari4+,chrome */
    background: -webkit-linear-gradient(53deg, rgba(255,225,213,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* safari5.1+,chrome10+ */
    background: -o-linear-gradient(53deg, rgba(255,225,213,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* opera 11.10+ */
    background: -ms-linear-gradient(53deg, rgba(255,225,213,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* ie10+ */
    background: linear-gradient(37deg, rgba(255,225,213,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* w3c */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ffe1d5',GradientType=0 ); /* ie6-9 */
}
#output-contactpersons .output-access.granted {
    background: -moz-linear-gradient(53deg, rgba(214,255,230,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* ff3.6+ */
    background: -webkit-gradient(linear, left bottom, right top, color-stop(0%, rgba(214,255,230,1)), color-stop(49%, rgba(255,255,255,0.51)), color-stop(100%, rgba(255,255,255,0))); /* safari4+,chrome */
    background: -webkit-linear-gradient(53deg, rgba(214,255,230,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* safari5.1+,chrome10+ */
    background: -o-linear-gradient(53deg, rgba(214,255,230,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* opera 11.10+ */
    background: -ms-linear-gradient(53deg, rgba(214,255,230,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* ie10+ */
    background: linear-gradient(37deg, rgba(214,255,230,1) 0%, rgba(255,255,255,0.51) 49%, rgba(255,255,255,0) 100%); /* w3c */
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#d6ffe6',GradientType=0 ); /* ie6-9 */
}

.output-contactperson-edit-keycard {
    color: #999;
}

.output-contactperson-keycard-is-set .glyphicon {
    color: #0284CA;
}

.output-contactperson-edit-wifi {
    color: #999;
}

.output-contactperson-wifi-is-set .glyphicon {
    color: #0284CA;
}

.output-contactperson-edit-lock-access {
    display:inline-block;
    position:relative;
    color: #999;
}

.output-contactperson-has-access-categories {
    color: #0284CA;
}

.output-contactperson-has-wifi-access {
    color: #0284CA;
}

.output-contactperson-edit-lock-access .output-access-dropdown {
    margin-left:15px;
    min-width:125px;
}


.output-comment {
	margin:5px 0;
	border:1px solid #e8e8e8;
	border-radius:5px;
	padding:10px;
}
.txt-bold {
	font-weight:bold !important;
}


.output-filelist li {
	display:block;
	padding:4px 4px;
	position:relative;
}
.output-filelist li:hover {
	background: #F8F8F8;
	border-radius:2px;
}

.output-filelist li .fileFolderPath {
	display:inline-block;
	margin-left:5px;
	color:#AAA;
	font-size:0.95em;
}

.output-filelist li .fileFolderPath .glyphicon {
	font-size:0.8em;
}

.output-filelist li .deleteFile {
	display:none;
	position:absolute;
	right:6px;
	top:4px;
}
.output-filelist li:hover .deleteFile {
	display:inline-block;
}
.output-filelist .file_infront {
    width: 13px;
    display: inline-block;
}
.deleteFileNew {
    float: right;
}
.deleteFileCustomer {
    float: right;
}
.filesShowAction {
    float: right;
    margin-right: 10px;
}
.output-filelist li a {
	display:inline-block;
	padding:0;
    font-weight: normal;
	/*color:#666;*/
}
.output-filelist li a:hover {
	text-decoration: none;
	/*color:#3B3C4E;*/
}
.output-filelist li a .glyphicon{
	font-size:0.8em;
}

.customerDetails {
    padding:10px 0 0 0;
}

.customerDetails .txt-label {
    width:30%;
}
.customerDetails .mainTable {
    float: left;
}
.customerDetails .fullTable {
    float: none;
}
.customerDetails .txt-label {
    width:30%;
}
.customerDetailsTableTitle {
    margin:15px 0 5px 0;
    padding:5px 0;
    font-weight:bold;
    border-bottom:1px solid #EEE;
}
.matrixTable {
    width: 100%;
}
.matrixTable .txt-label {
    width: 25%;
}
/**
 * Subscribtion styling
 */

 .output-edit-turnoveryear {
     cursor: pointer;
     margin-bottom: 15px;
 }
 .turnoverHidden {
     display: none;
 }
 .showMoreTurnOver {
     cursor: pointer;
     margin-bottom: 15px;
     color: #46b2e2;
 }
.subscription-block {
    border:2px solid #ddd;
    border-left: 4px solid #66C733;
    margin-bottom:15px;
}
.subscription-block.stopped {
    border-left: 4px solid #D91D1D;
}
.subscription-block.onhold {
    border-left: 4px solid #46b2e2;
}
.subscription-block.activeStopped {
    border-left: 4px solid #FF9300;
}
.subscription-block.freeNoBilling {
    border-left: 4px solid #000;
}
.subscription-block-dropdown {
    /* border-top:1px solid #ddd; */
	background: #fff;
}
.subscriptiontitle_dropdown_active {
    display: block;
}
.subscription-block-title {
	background: #E6E6E6;
}
.subscription-block-title .showMoreInfo {
	cursor: pointer;
	color: #46b2e2;
	text-align: center;
	padding: 5px 0px;
}
.subscription-extra-detailrow {
	background: #E6E6E6;
}
.subscription-block-title.active .showMoreInfo {
	display: none;
}
.subscription-block-dropdown .showLessInfo {
	cursor: pointer;
	color: #46b2e2;
	text-align: center;
	padding: 5px 0px;
}
.subscription-block-title.active .subscriptiontitle_dropdown_active {
    display: block;
}
.subscription-block-title::after {
    clear:both;
    display: block;
    content:" ";
}

.subscription-block-title-col {
    position: relative;
    float:left;
    padding:10px;
}
.subscription-block-title-col.c1 { width: 40%; }
.subscription-block-title-col.c2 { width: 35%; }
.subscription-block-title-col.c22 { width: 25%; font-size: 11px;}
.inputInfo .hoverEye {
	float: none;
	display: inline-block;
	margin-left: 15px;
}
.subscription-block-title-col.c3 { width: 5%; }
.subscription-block-title-col.c4 { width: 10%; text-align:right; }

.subscription-block-title-col.chalf { width: 50%; }
.subscription-block-title-col.cfull { width: 100%; padding-left:0; padding-right: 0;}

.suubscription-block-title-col.editLastColumn {
    float: right;
}
.subscription-block-title-col.no-top-padding {
    padding-top: 0;
}
.subscription-block-title-col.adjustment-padding {
    padding: 0px 10px;
}
.subscription-connection-row {
    padding: 7px 10px;
    border-top: 1px solid #cecece;
}
.subscription-block-info-line {
    /*border-bottom:1px solid #cecece;*/
}

.subscription-block-info {
    padding:10px;
    background:#f9f9f9;
    display:none;
}

.subscription-block-children {
    padding:10px;
    background:#f9f9f9;
}


.subscription-block-children-lines {
    margin-bottom:10px;
    display: table;
    width: 100%;
    border-collapse:collapse
}
.subscription-block-children-lines-label {
    padding: 5px 0px 10px 0px;
    font-weight: bold;
}
.output-edit-subscription-line-detail {
    font-weight: normal;
    font-size: 12px;
}

.subscription-block-children-line {
    border:1px solid #ddd;
    background:#FFF;
    border-top:none;
    display: table-row;
}

.subscription-block-children-line::after {
    clear:both;
    display: block;
    content:" ";
}

.subscription-block-children-line:first-child {
    border-top:1px solid #ddd;
}

.subscription-block-children-line-col {
    display: table-cell;
    padding:10px;
    border: 1px solid #ddd;
}
.subscription-block-children-line-col.c1 { width: 45%;  }
.subscription-block-children-line-col.c2 { width: 15%; }
.subscription-block-children-line-col.c3 { width: 15%; }
.subscription-block-children-line-col.c4 { width: 10%; }
.subscription-block-children-line-col.c5 { width: 15%; }
.subscription-block-children-line-col.c6 { width: 10%; text-align:right; }

.subscription-office-list {
    margin:0;
    padding:0;
}

.subscription-office-list li {
    padding:0 0 7px 0;
}

.subscription-office-list-home-icon {
    font-size:0.85em;
    margin-right:3px;
    color:#46b2e2;
}

.subscription-office-list li .subscription-office-list-name {
    display: inline-block;
    width: 40%;
    margin-right: 10px;
    vertical-align: top;
}

.subscription-office-list li .subscription-office-list-name .subscriptionlineName {
    color: #bbb;
}
.subscription-office-list li .subscription-office-list-size {
    display: inline-block;
    width: 25%;
    vertical-align: top;
}
.subscription-office-list-delete-icon {
    font-size:0.85em;
    margin-right:3px;
}

.assignOfficeBtn .glyphicon {
    font-size:0.85em;
    margin-right:3px;
}
.subscription-files {
    padding:10px;
    border-top:1px solid #ddd;
    border-bottom:1px solid #ddd;
}
.subscription-dates {
    border-top:1px solid #ddd;
}
.subscription-price-adjustment {
    border-top:1px solid #ddd;
}
.subscription-price-adjustment-row span {
    display: inline-block;
    width: 250px;
    vertical-align: middle;
}
.subscription-section {
    border-top:1px solid #ddd;
}
.subscription-guarantee {
    border-top:1px solid #ddd;
}
.subscription-comment {
    border-top:1px solid #ddd;
}
.subscription-vatstatements {
    border-top: 1px solid #ddd;
}
.subscription-vatstatements .titleWrapper {
    cursor: pointer;
}
.output-access-dropdown {
    z-index:99999;
}
.ordered_invoices_content th {
    background: #fafafa;
}
.p_contentBlockContent  .projectWrapper {
    padding: 10px 10px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
}

.p_contentBlockContent  .projectWrapper .projectTitle {
    position: relative;
    cursor: default;
}
.p_contentBlockContent  .projectWrapper .projectTitle .projectTitleColumn {
    display: inline-block;
    width: 45%;
    vertical-align: top;
}
.p_contentBlockContent  .projectWrapper .projectTitle a {
    color: inherit;
}
.p_contentBlockContent  .projectWrapper .projectTitle .projectTitleColumn a {
    color: inherit;
}
.p_contentBlockContent  .projectWrapper .projectTitle .projectTitleColumn.leaderColumn {
    width: 30%;
}
.p_contentBlockContent  .projectWrapper .projectTitle .projectTitleColumn.infoColumn {
    width: 20%
}
.p_contentBlockContent .projectCategoryTitle {
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px;
}
.p_contentBlockContent  .projectWrapper.hasOrders .projectTitle {
    cursor: pointer;
}
.p_contentBlockContent  .projectWrapper .projectTitle .showArrow {
    float: right;
    cursor: pointer;
    color: #2996E7;
    margin-left: 10px;
    position: absolute;
    right: 0px;
    top: 2px;
}
.p_contentBlockContent  .projectWrapper .projectOrders {
    margin-top: 15px;
    display: none;
}
.collectingOrder {
    margin-bottom: 15px;
}
.collectingOrder .table {
    margin-bottom: 5px;
}
.collectingOrder th {
    background: #fafafa;
}
.collectingOrder th span {
    font-weight: normal;
}
.collectingOrder td.whiteBackground {
	background: #fff;
}
.approvedForBatchInvoicingWrapper {
    float: left;
}
.seperatedInvoiceWrapper {
    float: left;
    margin-left: 20px;
}
.createOrderConfirmation {
    float: left;
    margin-left: 20px;
    color: #46b2e2;
    cursor: pointer;
}
.createProjectFromOffer {
    float: right;
    margin-right: 15px;
}
.totalRow {
    float: right;
    text-align: right;
    margin-bottom: 10px;
}
.totalRow span {
    font-weight: bold;
}
.output-btn-filled {
    padding: 10px 15px;
    background: #2893e2;
    color: #fff;
    font-weight: bold;
    display: inline-block;
    cursor: pointer;
    border-radius: 5px;
}
.orderConfirmations {
    float: left;
    width: 58%;
}
.orderButtons {
    float: right;
    width: 40%;
    text-align: right;
}
.createInvoice {
    float: right;
    margin-right: 40px;
}
.createInvoiceDummy {
	float: right;
	margin-right: 10px;
}
.selfdefinedEditBtn {
    display: inline-block;
    vertical-align: middle;
    color: #46b2e2;
    cursor: pointer;
    margin-left: 10px;
}
.filesAttachedToInvoice {
    max-width: 50%;
}
.filesAttachedToInvoice .attachFiles {
    color: #46b2e2;
    cursor: pointer;
}
.filesAttachedToEmail {
    max-width: 90%;
}
.filesAttachedToEmail .attachFilesToOffer {
    color: #46b2e2;
    cursor: pointer;
}
.tableInfoLabel {
    vertical-align: middle;
    margin-right: 10px;
}
.tableInfoTd {
    vertical-align: top;
    padding: 2px 0px;
}
.output-filelist label {
    cursor: pointer;
}
.article-loading.lds-ring {
  display: inline-block;
  position: relative;
  width: 24px;
  height: 24px;
  margin: 10px 20px;
}
.article-loading.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 22px;
  height: 22px;
  margin: 3px;
  border: 3px solid #46b2e2;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #46b2e2 transparent transparent transparent;
}
.article-loading.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.article-loading.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.article-loading.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
.customerMembershipConnectionList {
    font-size: 12px;
    margin-top: 10px;
    font-weight: normal;
}
.customerMembershipConnectionList .addMembershipConnection {
    color: #46b2e2;
    cursor: pointer;
}
.customerMembershipConnectionList .membershipConnectionRow {
    padding: 3px 0px;
}
.customerMembershipConnectionList .membershipConnectionRow .removeMembershipConnectionSelect {
    cursor: pointer;
    margin-left: 20px;
}
.output-folderlist .folder-list {
    margin-left: 15px;
    display: none;
}
.output-folderlist .folder-list li {
    padding: 5px 0px;
}
.output-folderlist .folder-list0 {
    display: block;
}
.output-folderlist .folder-list li .name_wrapper {
    cursor: pointer;
}
.output-folderlist .folder-list li .name_wrapper .fas {
    color: #46b2e2
}
.project_show_click {
    cursor: pointer;
    color: #46b2e2;
}
.project_show_item {
    display: none;
}
.uninvoicedProjectBlock {
    margin-bottom: 40px;
}
.uninvoicedProjectBlock .subBlockTitle {
    font-weight: bold;
    margin-bottom: 10px;
    cursor: pointer;
}
.uninvoicedProjectBlock .subBlockContent {
    display: block;
}
.edit_files_attached_to_offers {
    margin-bottom: 10px;
    color: #46b2e2;
    cursor: pointer;
    float: right;
    font-size: 12px;
    font-weight: normal;
    margin-right: 25px;
}
.edit_files_attached_to_confirmation {
    margin-bottom: 10px;
    color: #46b2e2;
    cursor: pointer;
    float: right;
    font-size: 12px;
    font-weight: normal;
    margin-right: 25px;
}

.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 2px;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
.hoverLabel {
	margin-bottom: 10px;
	font-weight: bold;
}
.hoverEyeCreated {
	position: relative;
	color: #cecece;
	float: left;
	margin-top: 2px;
}
.hoverEyeCreated .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:250px;
	display: none;
	color: #000;
	position: absolute;
	left: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEyeCreated.show-right-over .hoverInfo {
	width:350px;
	left:auto;
	right:0;
	top:0;
	padding:20px 20px;
}
.hoverEyeCreated.show-right-over .hoverInfo div {
	padding:5px 0;
}
.hoverEyeCreated:hover .hoverInfo {
	display: block;
}
.editProspectDefault {
    float: right;
    margin-right: 15px;
    cursor: pointer;
    color: #0284C9;
}

.editMainContact {
	cursor: pointer;
	color: #46b2e2;
}
.send_link {
	cursor: pointer;
	color: #46b2e2;
}
.activityType {
	font-weight: normal;
	font-size: 12px;
	padding: 5px 10px;
	border-radius: 5px;
	background: #cecece;
	display: inline-block;
	vertical-align: middle;
	margin-left: 10px;
	cursor: pointer;
	color: #fff;
}
.activityType.active {
	background: #46b2e2;
}
.customer_bregg_info {
	padding: 5px 10px;
	background: #fff3cd;
	display: none;
}
.edit_activity_categories {
	float: right;
	cursor: pointer;
	color: #0284C9;
	font-size: 12px;
	margin-right: 25px;
	font-weight: normal;
}
.subscription-block-titleactions {
	padding: 10px;
	float: right;
	margin-top: -40px;
}
.p_contentBlock.highligtedBlock {
	background: #ECFFE6;
}
.p_contentBlock.highligtedBlock2 {
	background: #fffff1;
}
.inactiveSubunitWrapper {
	display: none;
}
.inactiveSubunitInfo {
	color: #46b2e2;
	cursor: pointer;
	margin-bottom: 10px;
}
.historyList {
	margin-top: 7px; margin-left: 5px;margin-right: 5px;
    text-align: left;
    padding-left: 30px;
}
.historyList .hoverInfo {
	padding: 0px 0px;
}
.historyListItem {
	font-weight: normal;
	padding: 3px 0px;
    text-align: left;
}
.historyList .historyListTitle {
    font-weight: bold;
    text-align: left;
	padding: 10px 10px 10px 10px;
}
.historyList .gtable_cell {
	border: 0;
	border-top: 1px solid #efecec;
}
.historyList .timeColor {
	color: #999999 !important;
}
.edit_profile {
	cursor: pointer;
	color: #46b2e2;
	margin-left: 10px;
}
.profile_table {
	table-layout: fixed;
	width: auto;
	border: 1px solid #cecece;
	margin-top: 10px;
	border-left: 0;
	margin-bottom: 10px;
}
.profile_table td,
.profile_table th {
	padding: 5px 15px 5px 25px;
	border-left: 1px solid #cecece;
}
.profile_table .firstTd {
	width: 200px;
	border-left: 0;
	padding-left: 0;
	vertical-align: top;
}
.profile_table .default_span {
	color: #cecece;
	margin-left: 15px;
}
.profile_table .spanTd {
	border-left: 0;
	vertical-align: top;
}
.profile_table .glyphicon-pencil {
	margin-left: 10px;
	color: #cccccc;
	cursor: pointer;
}
.profile_table .customized_span {
	color: orange;
	font-weight: bold;
	margin-left: 15px;
}
.deleteProfile {
	cursor: pointer;
	color: #46b2e2;
}
</style>
