<?php
$people_contactperson_type = 2;
$sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
$o_query = $o_main->db->query($sql);
$accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
}
if(intval($variables->accountinfo['contactperson_type_to_use_in_people']) > 0)
{
	$people_contactperson_type = $variables->accountinfo['contactperson_type_to_use_in_people'];
}
//ini_set("display_errors", 1);
if(isset($_SESSION['list_filter'])){ $list_filter = $_SESSION['list_filter']; } else { $list_filter = 'all'; }
//$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
if(isset($_SESSION['city_filter'])){ $city_filter = $_SESSION['city_filter']; } else { $city_filter = ''; }
//$city_filter = $_SESSION['city_filter'] ? ($_SESSION['city_filter']) : '';
if(isset($_SESSION['search_filter'])){ $search_filter = $_SESSION['search_filter']; } else { $search_filter = ''; }
//$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
if(isset($_SESSION['selfdefinedfield_filter'])){ $selfdefinedfield_filter = $_SESSION['selfdefinedfield_filter']; } else { $selfdefinedfield_filter = ''; }
//$selfdefinedfield_filter = $_SESSION['selfdefinedfield_filter'] ? $_SESSION['selfdefinedfield_filter'] : '';
if(isset($_SESSION['search_by'])){ $search_by = $_SESSION['search_by']; } else { $search_by = 1; }
//$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;
if(isset($_SESSION['activecontract_filter'])){ $activecontract_filter = $_SESSION['activecontract_filter']; } else { $activecontract_filter = ''; }
//$activecontract_filter = $_SESSION['activecontract_filter'] ? ($_SESSION['activecontract_filter']) : '';

if(isset($_GET['subunit_filter'])){ $subunit_filter = $_GET['subunit_filter']; } else { $subunit_filter = 0; }

// List btn
require_once __DIR__ . '/list_btn.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_membersystem_config = $o_query->row_array();
}
$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);

// $_SESSION['search_by']
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$cid = ($_GET['cid']);

$s_sql = "SELECT customer.*, creditor.companyname as creditorName, creditor.creditor_reminder_default_profile_id, creditor.creditor_reminder_default_profile_for_company_id FROM customer
JOIN creditor ON creditor.id = customer.creditor_id WHERE customer.id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
if($o_query && $o_query->num_rows()>0){
    $customerData = $o_query->row_array();
}
$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['creditor_reminder_default_profile_id']));
$default_creditor_profile_person = ($o_query ? $o_query->row_array() : array());

$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, IF(crcp.name IS NULL or crcp.name = '', CONCAT_WS(' ', ccp.fee_level_name, pst.name), crcp.name) as name FROM creditor_reminder_custom_profiles crcp
LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
WHERE crcp.id = ?";
$o_query = $o_main->db->query($s_sql, array($customerData['creditor_reminder_default_profile_for_company_id']));
$default_creditor_profile_company = ($o_query ? $o_query->row_array() : array());

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customerData['id'];

require_once __DIR__ . '/filearchive_functions.php';

function getFullFolderPathForFile($id, $o_main) {
    // File info
    $s_sql = "SELECT * FROM sys_filearchive_file WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($id));
    if($o_query && $o_query->num_rows()>0){
        $fileData = $o_query->row_array();
    }
    // Path
    return getFullFolderPathForFolder($fileData['folder_id'], $o_main);
}

/**
 * Get full folder path for folder function
 */
function getFullFolderPathForFolder($id, $o_main) {
    // Full path
    $fullPath = '';
    // Folder data
    $s_sql = "SELECT * FROM sys_filearchive_folder WHERE id = ?";
    $o_query = $o_main->db->query($s_sql, array($id));
    if($o_query && $o_query->num_rows()>0){
        $folderData = $o_query->row_array();
    }
    // Add folder name to path
    $fullPath = $folderData['name'];
    // If folder has parents
    if ($folderData['parent_id']) $fullPath = getFullFolderPathForFolder($folderData['parent_id'], $o_main) . ' / ' . $fullPath;
    // Return path
    return $fullPath;
}
require_once("fnc_getMaxDecimalAmount.php");
$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}

$showCustomerInfo = false;
$showSelfdefinedFields = false;
$showComments = false;
$showMails = false;
$showOrders = false;
$showOffers = false;
$showRepeatingOrders = false;
$showInvoiceAndInvoicedOrders = false;
$showFile = false;
$showContactPersons = false;
$b_show_getynet_connection = false;


if(isset($menuaccess[$module][4]['data'][0]['content_access'])){ $content_access = $menuaccess[$module][4]['data'][0]['content_access']; } else { $content_access = null; }
if($content_access == null){
    $showCustomerInfo = true;
    $showSelfdefinedFields = true;
    $showComments = true;
    $showMails = true;
    $showOrders = true;
    $showOffers = true;
    $showRepeatingOrders = true;
    $showInvoiceAndInvoicedOrders = true;
    $showFile = true;
    $showContactPersons = true;
} else {
    if($content_access[1] == 1){
        $showCustomerInfo = true;
    }
    if($content_access[2] == 1){
        $showSelfdefinedFields = true;
    }
    if($content_access[3] == 1){
        $showComments = true;
    }
    if($content_access[4] == 1){
        $showMails = true;
    }
    if($content_access[5] == 1){
        $showOrders = true;
    }
    if($content_access[6] == 1){
        $showRepeatingOrders = true;
    }
    if($content_access[7] == 1){
        $showInvoiceAndInvoicedOrders = true;
    }
    if($content_access[8] == 1){
        $showFile = true;
    }
    if($content_access[9] == 1){
        $showContactPersons = true;
    }
}
$b_show_getynet_connection = ($v_customer_accountconfig['enable_getynet_connection'] == 1);
$b_enable_search = ($v_customer_accountconfig['getynet_customer_search'] == 1);
$b_enable_all_accounts = ($v_customer_accountconfig['getynet_show_all_accounts'] == 1);
$b_enable_grant_all = ($v_customer_accountconfig['getynet_grant_access_for_multi_partner_company'] == 1);
$b_enable_grant_admin = ($v_customer_accountconfig['getynet_grant_admin_access'] == 1);
$b_enable_grant_system_admin = ($v_customer_accountconfig['getynet_grant_system_admin_access'] == 1);
$b_enable_grant_designer = ($v_customer_accountconfig['getynet_grant_designer_access'] == 1);
$b_enable_grant_developer = ($v_customer_accountconfig['getynet_grant_developer_access'] == 1);

$externalSql = "SELECT cei.id id,
    cei.external_id external_id,
    cei.customer_id customer_id,
    oc.name name,
    oc.customerid_autoormanually customerid_autoormanually,
    oc.external_ownercompany_code external_ownercompany_code,
    oc.id ownercompany_id
    FROM customer_externalsystem_id cei
    LEFT JOIN ownercompany oc ON oc.id = cei.ownercompany_id
    WHERE cei.customer_id = ?";

$ownerCompanyIds = array();
$showAddButton = false;
$externalRowArray = array();

$o_query = $o_main->db->query($externalSql, array($cid));
if($o_query && $o_query->num_rows()>0){
    $externalRowArray = $o_query->result_array();
}
foreach($externalRowArray as $external) {
    array_push($ownerCompanyIds, $external['ownercompany_id']);
}
if(count($ownerCompanyIds) > 0) {

    $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE (customerid_autoormanually = 2 OR customerid_autoormanually = 3) AND id NOT IN (".implode(",", $ownerCompanyIds).")");
    if($o_query && $o_query->num_rows()>0){
        $showAddButton = true;
    }
} else {
    $o_query = $o_main->db->query("SELECT * FROM ownercompany WHERE (customerid_autoormanually = 2 OR customerid_autoormanually = 3)");
    if($o_query && $o_query->num_rows()>0){
        $showAddButton = true;
    }
}

$v_country = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_country[$v_item['countryID']] = $v_item['name'];
	}
}

$v_cpi_indexes = array();
if($customer_basisconfig['activateSubscriptionPriceAdjustment'])
{
	$s_response = APIConnectorAccount('cpi_index_get_list', $variables->accountinfo['accountname'], $variables->accountinfo['password'], array());
	$v_response = json_decode($s_response, TRUE);
	if(isset($v_response['status'], $v_response['items']) && 1 == $v_response['status'])
	foreach($v_response['items'] as $v_item)
	{
		$v_cpi_indexes[$v_item['index_date']] = $v_item;
	}
}

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&city_filter=".$city_filter."&activecontract_filter=".$activecontract_filter."&search_filter=".$search_filter."&selfdefinedfield_filter=".base64_encode(json_encode($selfdefinedfield_filter));

$o_query = $o_main->db->query("SELECT * FROM integration_signant_basisconfig ORDER BY id DESC");
$v_signant_basisconfig = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM integration_signant_accountconfig ORDER BY id DESC");
if($o_query && $o_query->num_rows() > 0)
{
	$v_signant_accountconfig = $o_query->row_array();
	if(2 != $v_signant_accountconfig['set_open'])
	{
		$v_signant_basisconfig['set_open'] = $v_signant_accountconfig['set_open'];
	}
}

$b_activate_signant = (1 == $v_signant_basisconfig['set_open']);
$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/output_functions.php';
if(is_file($s_signant_file)) include($s_signant_file);
$v_sign_status = array(
	0 => $formText_NotSigned_Output,
	1 => $formText_PartlySigned_Output,
	2 => $formText_Signed_Output,
	3 => $formText_Canceled_Output,
	4 => $formText_Failure_Output,
	5 => $formText_Rejected_Output,
);

require_once __DIR__ . '/functions.php';
$perPage = $_SESSION['listpagePerPage'];
$page = $_SESSION['listpagePage'];
if($perPage > 0 && $page > 0){
    $listPage = 1;
    $listPagePer = $page*$perPage;
    $customerList = get_customer_list($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by, $listPage, $listPagePer, $cid);
    $prevCustomer = $customerList[0];
    $nextCustomer = $customerList[2];
    $nextId = $nextCustomer['customerId'];
    $prevId = $prevCustomer['customerId'];
}

$s_prev_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$prevId;
$s_next_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$nextId;

require_once("fnc_rewritebasisconfig.php");

rewriteCustomerBasisconfig();

$s_sql = "SELECT * FROM customer_view_history WHERE customer_view_history.username = ?";
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
$customerHistoryOrdered = array_reverse($customerHistory);
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">

		<div class="p_content">
			<div class="p_pageContent">
                <div class="p_pagePreDetail">
                    <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list"><?php echo $formText_BackToList_outpup;?></a>
					<?php if(count($customerHistoryOrdered) > 0) { ?>
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
					<?php } ?>
                    <?php /*?><div class="employeeSearch">
                        <span class="glyphicon glyphicon-search"></span>
                        <input type="text" placeholder="<?php echo $formText_Customer_output;?>" class="employeeSearchInput" autocomplete="off"/>
                        <div class="employeeSearchSuggestions allowScroll"></div>
                    </div><?php */?>
                    <?php if(intval($nextId) > 0){ ?>
                        <a href="<?php echo $s_next_link;?>" class="output-click-helper optimize next-link"><?php echo $formText_Next_outpup;?></a>
                    <?php } ?>
                    <?php if(intval($prevId) > 0){ ?>
                        <a href="<?php echo $s_prev_link;?>" class="output-click-helper optimize prev-link"><?php echo $formText_Prev_outpup;?></a>
                    <?php } ?>
                    <div class="clear"></div>
                </div>
				<div class="p_pageDetails">
					<div class="customer_bregg_info"></div>
					<div class="p_pageDetailsTitle dropdown_content_show show_customerDetails" data-blockid="1">
                        <?php echo $formText_CustomerDetails_Output;?>
                        <?php if($customer_basisconfig['activateDisplayCustomerType']) { ?>
                        	(<?php if($customerData['customerType'] == 0){ echo $formText_Company_output;} else { echo $formText_Person_output; }?>)
                        <?php }?>
                        <span class="customerName"><?php echo $customerData['name']." ".$customerData['middlename']." ".$customerData['lastname'];?></span>
						<?php
						if($v_customer_accountconfig['activate_subunits']) {
							$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? AND content_status < 1 ORDER BY customer_subunit.id ASC";
							$o_query = $o_main->db->query($s_sql, array($customerData['id']));
							$subunits = $o_query ? $o_query->result_array() : array();
							echo "&nbsp;&nbsp;&nbsp;&nbsp;".$formText_SubunitFilter_output." ";
							?>
							<select class="subunitFilter" autocomplete="off">
								<option value=""><?php echo $formText_All_output;?></option>
								<?php
								foreach($subunits as $subunit) {
									?>
									<option value="<?php echo $subunit['id'];?>" <?php if($subunit_filter == $subunit['id']) echo 'selected';?>><?php echo $subunit['name'];?></option>
								<?php } ?>
							</select>
							<?php
						}
						?>

                        <div class="showArrow"><span class="glyphicon <?php if(!$customer_basisconfig['collapseCustomerDetails']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                        <div class="externalInfo">
                            <div class="externalInfoRowWrapper">
                                <table>
                                    <?php foreach($externalRowArray as $external) { ?>
                                    <?php
                                    if ($external['ownercompany_id'] == 0) {
                                        $external['name'] = $formText_Global_output;
                                    }
                                    ?>
                                    <tr class="externalInfoRow">
                                        <td class="customerColumn"><?php echo $formText_CustomerId_output?> (<?php echo $external['name']?>): </td>
                                        <td class="externalIdColumn"><?php echo $external['external_id']?></td>
                                        <?php if($v_customer_accountconfig['activeCustomerBalanceFromAccountingApi']): ?>
                                            <?php if ($ownercompany_accountconfig['global_integration'] === 'IntegrationXledger'): ?>
                                                <?php
                                                // Keycards
                                                $integration = 'IntegrationXledger';
                                                $integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
                                                if (file_exists($integration_file)) {
                                                    require_once $integration_file;
                                                    if (class_exists($integration)) {
                                                        if ($xledger_api) unset($xledger_api);
                                                        $xledger_api = new $integration(array(
                                                            'o_main' => $o_main
                                                        ));
                                                    }
                                                }

                                                $customer_balance_data = $xledger_api->get_customer_balance(array(
                                                    'subledgerCode' => $external['external_id']
                                                ));
                                                ?>
                                                <td>
                                                    (<a href="#" class="output-show-customer-transactions" data-customer-code="<?php echo $external['external_id']; ?>" data-external-ownercompany-code="<?php echo $external['external_ownercompany_code']; ?>">
                                                        <b><?php echo $formText_Balance_output; ?></b>:
                                                        <?php echo $customer_balance_data['amount']; ?>
                                                    </a>)
                                                </td>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <td class="externalActionColumn">
                                            <?php if($external['customerid_autoormanually'] == 2 || $external['customerid_autoormanually'] == 3 || $variables->developeraccess > 10) {?>
                                                <?php if($moduleAccesslevel > 10) { ?>
                                                <button class="editEntryBtn small output-edit-external-customer-id" data-cid="<?php echo $external['id'];?>" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
                                                <?php } ?>
                                                <?php if($moduleAccesslevel > 110) { ?>
                                                <button class="editEntryBtn small output-delete-external-customer-id" data-cid="<?php echo $external['id'];?>"><span class="glyphicon glyphicon-trash"></span></button>
                                                <?php } ?>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php }?>
                                </table>
                                <?php if($showAddButton){?>
                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-edit-external-customer-id addEntryBtn" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_AddCustomerNumber_output;?></button><?php } ?>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="clear"></div>

						<?php if($variables->developeraccess >= 5) { ?>
							<div class="previousSysIdWrapper">
								<?php echo $formText_PreviousSysId_output.": ".$customerData['previous_sys_id'];?>
								<?php if($moduleAccesslevel > 10) { ?>
								<button class="editEntryBtn small output-edit-previous-sys-id" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
								<?php } ?>
							</div>
						<?php } ?>
                        <div class="clear"></div>

                    </div>
                    <?php
					if($showCustomerInfo) { ?>
    					<div class="p_contentBlock p_contentBlockContent no-vertical-padding dropdown_content" <?php if(!$customer_basisconfig['collapseCustomerDetails']){ ?> style="display: block;" <?php } ?>>

                            <?php if($customerData['content_status'] == 2) { ?>
                                <?php if($moduleAccesslevel > 10) { ?>
                                    <button style="float: right; margin-top: -13px;" class="output-activate-customer output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_ActivateCustomer_output;?></button>
                                <?php } ?>
                            <?php } else { ?>
                                <?php if($moduleAccesslevel > 10) { ?>
                                    <?php if($v_customer_accountconfig['activate_sync_customer_hook']){?>
                                        <button style="float: right; margin-top: -13px;" class="syncCustomerHook output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_SyncCustomerHook_output;?></button>
                                    <?php } ?>
                                    <button style="float: right; margin-top: -13px;" class="output-delete-customer output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_DeleteCustomer_output;?></button>
                                    <?php
									if($customer_basisconfig['activate_merge_customer']) {
										?>
	                                    <button style="float: right; margin-top: -13px;" class="output-merge-customer output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_MergeIntoOtherCustomer_output;?></button>
										<?php
									}
                                    ?>
                                <?php } ?>
                            <?php } ?>
                            <div class="customerDetails">
                                <table class="mainTable" width="33%"  border="0" cellpadding="0" cellspacing="0">
                                    <?php
                                    if($v_customer_accountconfig['activateFieldCreditorId']) {
                                        $s_sql = "SELECT customer.*, creditor.* FROM customer LEFT JOIN creditor ON creditor.customer_id = customer.id  WHERE creditor.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['creditor_id']));
                                        $creditor = ($o_query ? $o_query->row_array() : array());
                                        ?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_Creditor_output;?></td>
                                            <td class="txt-value"><?php echo $creditor['name'];?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_CreditorCustomerId_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['creditor_customer_id'];?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <?php if(intval($customerData['customerType']) == 0) {?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_PublicRegisterNumber_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['publicRegisterId'];?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php if(intval($customerData['customerType']) == 1) {?>
                                    	<?php if (!$customer_basisconfig['hideBirthdate']){?>
	                                        <tr>
	                                            <td class="txt-label"><?php echo $formText_Birthdate_Output;?></td>
	                                            <td class="txt-value"><?php if($customerData['birthdate'] != "0000-00-00" && $customerData['birthdate'] != null){ echo date("d.m.Y", strtotime($customerData['birthdate'])); } ?></td>
	                                        </tr>
	                                    <?php } ?>
                                    <?php } ?>
                                    <?php if(intval($customerData['customerType']) == 1) {?>
                                    	<?php if (!$customer_basisconfig['hidePersonNumber']){?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_Personnumber_Output;?></td>
                                            <td class="txt-value"><?php echo $customerData['personnumber'];?></td>
                                        </tr>
	                                    <?php } ?>
                                    <?php } ?>
                                    <tr>
                                        <td class="txt-label">
                                            <?php
                                                 echo $formText_Name_output;
                                            ?>
                                        </td>
                                        <td class="txt-value"><?php echo $customerData['name'] ." ".$customerData['middlename']." ".$customerData['lastname'];?></td>
                                    </tr>
									<?php if($customer_basisconfig['activate_shop_name']) { ?>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_ShopName_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['shop_name'];?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php if(intval($customerData['customerType']) == 0) {?>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_Phone_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['phone'];?></td>
                                    </tr>
                                    <?php } ?>
									<?php if($v_customer_accountconfig['activateMobileField']) { ?>
										<tr>
	                                        <td class="txt-label"><?php echo $formText_Mobile_output;?></td>
	                                        <td class="txt-value"><?php echo $customerData['mobile'];?></td>
	                                    </tr>
									<?php } ?>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_InvoiceBy_output;?></td>
                                        <td class="txt-value"><?php
                                        if( $customerData['invoiceBy'] == 0) {
                                            echo $formText_Paper_output;
                                        } else if($customerData['invoiceBy'] == 1){
                                            echo $formText_Email_output;
                                        } else if($customerData['invoiceBy'] == 2){
                                            echo $formText_Ehf_output;;
                                        }
                                        ?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_InvoiceAndReminderEmail_output;?></td>
                                        <td class="txt-value"><?php
                                        echo $customerData['invoiceEmail'];
                                        ?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_DefaultInvoiceReference_output;?></td>
                                        <td class="txt-value"><?php
                                        echo $customerData['defaultInvoiceReference'];
                                        ?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_CreditTimeDays_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['credittimeDays'];?></td>
                                    </tr>
                                    <?php if($customer_basisconfig['display_field_text_on_mypage']) { ?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_TextVisibleInMyProfile_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['textVisibleInMyProfile'];?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                        $showAdminFee = false;
                                        if(intval($ownercompany_accountconfig['addAdminFeeAutomatically']) > 0) {
                                            $showAdminFee = true;
                                        }
                                        if($showAdminFee) { ?>
                                            <tr>
                                                <td class="txt-label"><?php echo $formText_AddAdminFee_Output;?></td>
                                                <td class="txt-value"><?php
                                                    switch(intval($customerData['overrideAdminFeeDefault'])){
                                                        case 0:
                                                            echo $formText_Default_output;
                                                            echo ' (';
                                                            if(intval($ownercompany_accountconfig['addAdminFeeAutomatically']) == 1){
                                                                echo $formText_Always_output;
                                                            } else if(intval($ownercompany_accountconfig['addAdminFeeAutomatically']) == 2){
                                                                echo $formText_AlwaysIfPrint_output;
                                                            } else if(intval($ownercompany_accountconfig['addAdminFeeAutomatically']) == 3){
                                                                echo $formText_OnlyIfChosen_output;
                                                            }
                                                            echo ')';
                                                        break;
                                                        case 1:
                                                            echo $formText_NeverCharge_output;
                                                        break;
                                                        case 2:
                                                            echo $formText_ChargeAlways_output;
                                                        break;
                                                        case 3:
                                                            echo $formText_ChargeIfPaper_output;
                                                        break;
                                                    }
                                                 ?></td>
                                            </tr>
                                    <?php } ?>
                                    <tr>
                                        <td class="txt-label"><?php echo $formText_NotOverwriteByImport_output;?></td>
                                        <td class="txt-value"><input type="checkbox" disabled readonly <?php if($customerData['notOverwriteByImport']) echo 'checked';?> /><label></label></td>
                                    </tr>
                                    <?php
                                     if($customer_basisconfig['activateCustomerEmailField']) { ?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_CustomerEmail_Output;?></td>
                                            <td class="txt-value"><?php echo $customerData['email'];?></td>
                                        </tr>
                                    <?php } ?>
                                    <?php
                                     if($customer_basisconfig['activateHomepageField']) { ?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_Homepage_Output;?></td>
                                            <td class="txt-value"><?php echo $customerData['homepage'];?></td>
                                        </tr>
                                    <?php } ?>

                                    <?php
                                     if($v_customer_accountconfig['activateSelfregistered'] && $customerData['selfregistered'] > 0) { ?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_SelfregisteredHandled_output;?></td>
                                            <td class="txt-value"><input type="checkbox" class="handle_selfregistered" id="handle_selfregistered" autocomplete="off" <?php if($customerData['selfregistered'] == 2) echo 'checked';?> /><label for="handle_selfregistered"></label></td>
                                        </tr>
                                    <?php } ?>
									<?php if($v_customer_accountconfig['display_customer_accounting_project_number'] && 0 < $v_customer_accountconfig['display_customer_accounting_project_number']) {
										$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE ownercompany_id = '".$o_main->db->escape_str($v_customer_accountconfig['accounting_project_number_ownercompany_id'])."' AND projectnumber = '".$o_main->db->escape_str($customerData['accounting_project_number'])."'");
										$v_projectforaccounting = ($o_query ? $o_query->row_array() : array());
										?>
                                        <tr>
                                            <td class="txt-label"><?php echo $formText_AccountingProject_Output;?></td>
                                            <td class="txt-value"><?php echo ($v_projectforaccounting['id'] ? $v_projectforaccounting['name'].' ('.$v_projectforaccounting['projectnumber'].')' : '');?></td>
                                        </tr>
                                    <?php } ?>
									<?php
									if($v_customer_accountconfig['activate_customer_responsibleperson']) {
										$s_sql = "select * from contactperson WHERE id = '".$o_main->db->escape_str($customerData['responsible_person_id'])."'";
										$o_query = $o_main->db->query($s_sql);
										$repeatingOrderWorklineWorker= ($o_query ? $o_query->row_array() : array());
										?>
										<tr>
                                            <td class="txt-label"><?php echo $formText_CustomerResponsiblePerson_Output;?></td>
                                            <td class="txt-value"><?php echo $repeatingOrderWorklineWorker['name']." ".$repeatingOrderWorklineWorker['middlename']." ".$repeatingOrderWorklineWorker['lastname'];?></td>
                                        </tr>
										<?php
									}
									?>
                                </table>
                                <table class="mainTable" width="33%"  border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="txt-label border-left" colspan="2"><?php echo $formText_PostalAddress_output; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Street_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['paStreet'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Street2_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['paStreet2'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_PostalNumber_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['paPostalNumber'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_City_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['paCity'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Country_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['paCountry'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_UseOwnInvoiceAddress_output;?></td>
                                        <td class="txt-value"><input type="checkbox" disabled readonly <?php if($customerData['useOwnInvoiceAdress']) echo 'checked';?> /><label></label></td>
                                    </tr>
                                    <?php if($customerData['useOwnInvoiceAdress']) {?>
                                        <tr>
                                            <td class="txt-label border-left" colspan="2"><?php echo $formText_InvoiceAddress_output; ?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label border-left"><?php echo $formText_Street_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['iaStreet1'];?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label border-left"><?php echo $formText_Street2_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['iaStreet2'];?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label border-left"><?php echo $formText_PostalNumber_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['iaPostalNumber'];?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label border-left"><?php echo $formText_City_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['iaCity'];?></td>
                                        </tr>
                                        <tr>
                                            <td class="txt-label border-left"><?php echo $formText_Country_output;?></td>
                                            <td class="txt-value"><?php echo $customerData['iaCountry'];?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                                <table class="mainTable" width="33%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="txt-label border-left" colspan="2"><?php echo $formText_VisitingAddress_output; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Street_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['vaStreet'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Street2_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['vaStreet2'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_PostalNumber_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['vaPostalNumber'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_City_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['vaCity'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="txt-label border-left"><?php echo $formText_Country_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['vaCountry'];?></td>
                                    </tr>
                                </table>
                                <table class="mainTable fullTable" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="txt-label"></td>
                                        <td class="txt-value"></td>
                                        <td class="btn-edit" colspan="2">
                                            <?php if($moduleAccesslevel > 10) { ?><button class="output-edit-customer-detail output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>

                                        </td>
                                    </tr>
                                </table>
								<br/>
								<br/>
                                <div class="clear"></div>
	                            </div>
	    					</div>
							<div class="p_contentBlock">
								<table class="mainTable fullTable" style="margin-top: 10px;" width="100%" border="0" cellpadding="0" cellspacing="0">
									<tr>
                                        <td class="txt-label"><?php echo $formText_CrmCustomerType_output;?></td>
                                        <td class="txt-value"><?php if($customerData['customer_type_collect'] == 0) {
											echo $formText_Company_output;
										} else if($customerData['customer_type_collect'] == 1) {
											echo $formText_PrivatePerson_output;
										} ?> </td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<?php


									$s_sql = "SELECT * FROM creditor WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($customerData['creditor_id']));
									$creditor = $o_query ? $o_query->row_array() : array();

									$creditor_profile_for_person = $creditor['creditor_reminder_default_profile_id'];
									$creditor_profile_for_company = $creditor['creditor_reminder_default_profile_for_company_id'];

									$customer_reminder_profile = $customerData['creditor_reminder_profile_id'];
									$customer_move_to_collecting = $customerData['choose_move_to_collecting_process'];
									$customer_progress_of_reminder_process = $customerData['choose_progress_of_reminderprocess'];

									$customer_reminder_profile = $customerData['creditor_reminder_profile_id'];
									$customer_move_to_collecting = $customerData['choose_move_to_collecting_process'];
									$customer_progress_of_reminder_process = $customerData['choose_progress_of_reminderprocess'];

									if($customer_reminder_profile == 0){
										$customer_type_collect_debitor = $customerData['customer_type_collect'];
										if($customerData['customer_type_collect_addition'] > 0){
											$customer_type_collect_debitor = $customerData['customer_type_collect_addition'] - 1;
										}
										if($customer_type_collect_debitor== 1){
											$default_reminder_profile = $creditor_profile_for_person;
										} else {
											$default_reminder_profile = $creditor_profile_for_company;
										}
									} else {
										$default_reminder_profile = $customer_reminder_profile;
									}

									$s_sql = "SELECT crcp.*, ccp.fee_level_name, pst.name as stepTypeName, CONCAT_WS(' ', ccp.fee_level_name, pst.name) as name
									FROM creditor_reminder_custom_profiles crcp
									LEFT JOIN collecting_cases_process ccp ON ccp.id = crcp.reminder_process_id
									LEFT JOIN process_step_types pst ON pst.id = ccp.process_step_type_id
									WHERE crcp.creditor_id = ? AND crcp.content_status < 2";
									$o_query = $o_main->db->query($s_sql, array($creditor['id']));
									$creditor_profiles = ($o_query ? $o_query->result_array() : array());

									?>
									<tr>
                                        <td class="txt-label"><?php echo $formText_ChooseReminderProfile_output;?></td>
                                        <td class="txt-value"><?php
										if($customerData['creditor_reminder_profile_id'] == 0) {
											echo $formText_Default_output;
											foreach($creditor_profiles as $creditor_profile) {
												if($creditor_profile['id'] == $default_reminder_profile) {
													echo " (".$creditor_profile['name'].")";
												}
											}
										} else {
											foreach($creditor_profiles as $creditor_profile) {
												if($creditor_profile['id'] == $customerData['creditor_reminder_profile_id']) {
													echo $creditor_profile['name'];
												}
											}
										}
										?>
									</tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_ChooseProgressOfReminderProcess_output;?></td>
                                        <td class="txt-value"><?php


										if($customerData['choose_progress_of_reminderprocess'] == 0) {
											$default_progress_of_reminderprocess = $creditor_progress_of_reminder_process;
											echo $formText_Default_output." ";
											switch($default_progress_of_reminderprocess) {
												case 0:
													echo "(".$formText_Manual_output.")";
												break;
												case 1:
													echo "(".$formText_Automatic_output.")";
												break;
												case 2:
													echo "(".$formText_DoNotSent_output.")";
												break;
											}
										} else if($customerData['choose_progress_of_reminderprocess'] == 1) {
											echo $formText_Manual_output;
										} else if($customerData['choose_progress_of_reminderprocess'] == 2) {
											echo $formText_Automatic_output;
										} else if($customerData['choose_progress_of_reminderprocess'] == 3) {
											echo $formText_DoNotSent_output;
										}
										?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_ChooseMoveToCollectingProcess_output;?></td>
                                        <td class="txt-value"><?php
										if($customerData['choose_move_to_collecting_process'] == 0) {
											$default_move_to_collecting = $creditor_move_to_collecting;
											echo $formText_Default_output." ";
											switch($default_move_to_collecting) {
												case 0:
													echo "(".$formText_Manual_output.")";
												break;
												case 1:
													echo "(".$formText_Automatic_output.")";
												break;
												case 2:
													echo "(".$formText_DoNotSent_output.")";
												break;
											}
										} else if($customerData['choose_move_to_collecting_process'] == 1) {
											echo $formText_Manual_output;
										} else if($customerData['choose_move_to_collecting_process'] == 2) {
											echo $formText_Automatic_output;
										} else if($customerData['choose_move_to_collecting_process'] == 3) {
											echo $formText_DoNotSent_output;
										}
										?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>



									<tr>
                                        <td class="txt-label"><?php echo $formText_CreditorName_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['creditorName'];?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_CustomerId_output;?></td>
                                        <td class="txt-value"><?php echo $customerData['creditor_customer_id'];?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_CreditorDefaultProcessForPerson_output;?></td>
                                        <td class="txt-value"><?php echo $default_creditor_profile_person['name'];?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_CreditorDefaultProcessForCompany_output;?></td>
                                        <td class="txt-value"><?php echo $default_creditor_profile_company['name'];?></td>
                                        <td class="btn-edit" colspan="2">
                                        </td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_InvoiceLanguage_output;?></td>
                                        <td class="txt-value"><?php if($customerData['integration_invoice_language']){ echo $formText_English_output; } else { echo $formText_Norwegian_output; }?></td>
                                        <td class="btn-edit" colspan="2"></td>
                                    </tr>
									<tr>
                                        <td class="txt-label"><?php echo $formText_SendAllCollectingCompanyLettersByEmail_Output;?></td>
                                        <td class="txt-value"><?php if($customerData['send_all_collecting_company_letters_by_email']){ echo $formText_Yes_output; } else { echo $formText_No_output; }?></td>
                                        <td class="btn-edit" colspan="2"></td>
                                    </tr>
                                </table>
								<table class="mainTable fullTable" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="txt-label"></td>
                                        <td class="txt-value"></td>
                                        <td class="btn-edit" colspan="2">
                                            <?php if($moduleAccesslevel > 10) { ?><button class="output-edit-customer-creditor-detail output-btn editBtnIcon" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>

                                        </td>
                                    </tr>
                                </table>
                            </div>
                    <?php } ?>


                    <?php

                    if(!$accessElementRestrict_ContactPersons){
                        if($showContactPersons) {
                            $v_subrows = array();
                            $s_sql = "select * from contactperson where customerId = ? AND content_status = 0 order by sortnr";;
                            $o_query = $o_main->db->query($s_sql, array($_GET['cid']));
                            if($o_query && $o_query->num_rows()>0){
                                $v_subrows = $o_query->result_array();
                            }
                            $defaultCount = 10;
                            ?>
        					<div class="p_contentBlock">
        						<div class="p_contentBlockTitle dropdown_content_show show_contactpersons" data-blockid="3"><?php echo $formText_Contactpersons_Output;?>
                                     <span class="badge">
                                        <?php echo count($v_subrows); ?>
                                    </span>
                                    <?php if($moduleAccesslevel > 10) { ?>
                                        <button id="output-add-contactpersons" class="addEntryBtn"><?php echo $formText_Add_output;?></button>
                                        <?php include("ajax.import_data_contactperson.php");?>
                                        <?php if($customer_basisconfig['activateContactPersonAccess']) { ?>
                                            <button class="giveAccessInBatch fw_text_link_color addEntryBtn"><?php echo $formText_GiveAccessInBatch_output;?></button>
                                        <?php } ?>
										<?php
										if($v_customer_accountconfig['activate_selfdefined_company'])
										{
											$s_response = APIconnectAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
											$v_response = json_decode($s_response, TRUE);
											if(isset($v_response['status']) && 1 == $v_response['status'] && sizeof($v_response['items']) > 1)
											{
												?>
												<span class="default-selfdefined-company">
												<label><?php echo $formText_SelfdefinedCompany_output;?></label>
												<select class="default_selfdefined_company_id" data-customerid="<?php echo $customerData['id']?>">
													<option value=""><?php echo $formText_None_output;?></option>
													<?php
													foreach($v_response['items'] as $v_item)
													{
														?><option value="<?php echo $v_item['id'];?>"<?php echo ($customerData['selfdefined_company_id'] == $v_item['id'] ? ' selected':'');?>><?php echo $v_item['name'];?></option><?php
													}
													?>
												</select>
												</span>
												<?php
											}
										}
										?>
                                        <?php
										if($v_customer_accountconfig['activate_intranet_membership'])
										{
                                            ?>
                                            <div class="customerMembershipConnectionList">
                                                <div class="listWrapper">
                                                    <?php
                                                    $s_sql = "select intranet_membership_customer_connection.* from intranet_membership_customer_connection
                                                    where customer_id = ?";
                                                    $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                                    $customerMembershipConnections = $o_query ? $o_query->result_array() : array();

                                                    $s_sql = "select * from intranet_membership WHERE content_status < 2 ORDER BY name ASC";
                                                    $o_query = $o_main->db->query($s_sql, array());
                                                    $intranet_memberships = $o_query ? $o_query->result_array() : array();

                                                    foreach($customerMembershipConnections as $customerMembershipConnection) {
                                                        ?>
                                                        <div class="membershipConnectionRow">
                                                            <?php
                                                            foreach($intranet_memberships as $intranet_membership) {
                                                                if($customerMembershipConnection['membership_id'] == $intranet_membership['id']) {
                                                                    echo $intranet_membership['name'];
                                                                }
                                                            }
                                                            ?>
                                                            <span class="glyphicon glyphicon-trash removeMembershipConnectionSelect" data-connection-id="<?php echo $customerMembershipConnection['id']?>"></span>
                                                        </div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="addMembershipConnection"><?php echo $formText_AddMembershipConnection_output;?></div>
                                                <div class="emptyMembershipConnection" style="display:none;">
                                                    <select class="membershipConnectionSelect" autocomplete="off">
                                                        <option value=""><?php echo $formText_Select_output;?></option>
                                                        <?php
                                                        foreach($intranet_memberships as $intranet_membership) {
                                                            ?>
                                                            <option value="<?php echo $intranet_membership['id']?>"><?php echo $intranet_membership['name'];?></option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                    <span class="glyphicon glyphicon-trash removeMembershipConnectionSelect"></span>
                                                </div>
                                            </div>
                                            <?php
										}
										?>
                                    <?php } ?>

                                <div class="showArrow"><span class="glyphicon  <?php if(!$customer_basisconfig['collapseContactpersons'] || $_GET['contactpersonSearch'] != ""){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div></div>
                                <div class="p_contentBlockContent dropdown_content" <?php if(!$customer_basisconfig['collapseContactpersons'] || $_GET['contactpersonSearch'] != ""){ ?> style="display: block;" <?php } ?>>

        						<div id="output-contactpersons">
                                    <?php if(count($v_subrows) > $defaultCount || (isset($_POST['search']) && $_POST['search'] != "") || (isset($_GET['contactpersonSearch']) && $_GET['contactpersonSearch'] != "")) {?>
                                        <div class="contactPersonSearch">
                                            <span class="glyphicon glyphicon-search"></span>
                                            <input type="text" placeholder="<?php echo $formText_ContactPersonLabel_output;?>" class="contactPersonSearchInput" value="<?php if(isset($_POST['search'])) { echo $_POST['search'];} else { echo $_GET['contactpersonSearch'];}?>" autocomplete="off"/>
                                            <span class="glyphicon glyphicon-triangle-right"></span>
                                        </div>
                                    <?php } ?>
                                    <div class="contactpersonTableWrapper">
                                      <?php include('ajax.contactpersons_list.php');?>
                                    </div>
            					</div>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
					<?php
					if($v_customer_accountconfig['activate_subunits']) {
						$sql_subunit_sql = "";
						if($subunit_filter > 0) {
							$sql_subunit_sql = " AND customer_subunit.id = '".$o_main->db->escape_str($subunit_filter)."'";
						}
						$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? ".$sql_subunit_sql."  AND content_status < 1 ORDER BY customer_subunit.id ASC";
						$o_query = $o_main->db->query($s_sql, array($customerData['id']));
						$subunits = $o_query ? $o_query->result_array() : array();

						$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? ".$sql_subunit_sql."  AND content_status = 1 ORDER BY customer_subunit.id ASC";
						$o_query = $o_main->db->query($s_sql, array($customerData['id']));
						$inactiveSubunits = $o_query ? $o_query->result_array() : array();
						?>
						<div class="p_contentBlock">
							<div class="p_contentBlockTitle dropdown_content_show show_subunits" data-blockid="2">
								<?php echo $formText_SubUnits_Output;?>
								<span class="badge">
									<?php echo count($subunits); ?>
								</span>
								<?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn output-edit-subunit"><?php echo $formText_Add_output;?></button><?php } ?>

								<div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
							</div>
							<div class="p_contentBlockContent dropdown_content">
								<table class="table">
									<tr>
										<th width="50px"><?php echo $formText_Id_output;?></th>
										<th width="150px"><?php echo $formText_Name_output;?></th>
										<th ><?php echo $formText_Info_output;?></th>
										<th width="100px"></th>
									</tr>
									<?php
									foreach($subunits as $subunit) {
										?>
										<tr>
											<td><?php echo $subunit['id'];?></td>
											<td><?php echo $subunit['name'];?></td>
											<td><?php
											$s_delivery_address = trim(preg_replace('/\s+/', ' ', $subunit['delivery_address_line_1'].' '.$subunit['delivery_address_line_2'].' '.$subunit['delivery_address_city'].' '.$subunit['delivery_address_postal_code'].' '.$v_country[$subunit['delivery_address_country']]));
											echo $s_delivery_address;
											if(!empty($subunit['delivery_date']) && $subunit['delivery_date'] != '0000-00-00') echo "<br/>".date("d.m.Y", strtotime($subunit['delivery_date']));
											if($subunit['reference'] != "")  echo "<br/>".$formText_DefaultInvoiceReference_output.": ".$subunit['reference'];
											?></td>
											<td>
												<?php if($moduleAccesslevel > 10) { ?>
                                                <button class="editEntryBtn small output-edit-subunit" data-cid="<?php echo $subunit['id'];?>" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
                                                <?php } ?>
                                                <?php if($moduleAccesslevel > 110) { ?>
                                                <button class="editEntryBtn small output-delete-subunit" data-cid="<?php echo $subunit['id'];?>"><span class="glyphicon glyphicon-trash"></span></button>
                                                <?php } ?>
											</td>
										</tr>
										<?php
									}
									?>
								</table>
								<?php if(count($inactiveSubunits) > 0) {
									?>
									<div class="inactiveSubunitInfo"><?php echo count($inactiveSubunits)." ".$formText_SubunitsAreInactive_output;?></div>
									<div class="inactiveSubunitWrapper">
										<table class="table">
											<tr>
												<th width="50px"><?php echo $formText_Id_output;?></th>
												<th width="150px"><?php echo $formText_Name_output;?></th>
												<th ><?php echo $formText_Info_output;?></th>
												<th width="100px"></th>
											</tr>
											<?php
											foreach($inactiveSubunits as $subunit) {
												?>
												<tr>
													<td><?php echo $subunit['id'];?></td>
													<td><?php echo $subunit['name'];?></td>
													<td><?php
													$s_delivery_address = trim(preg_replace('/\s+/', ' ', $subunit['delivery_address_line_1'].' '.$subunit['delivery_address_line_2'].' '.$subunit['delivery_address_city'].' '.$subunit['delivery_address_postal_code'].' '.$v_country[$subunit['delivery_address_country']]));
													echo $s_delivery_address;
													if(!empty($subunit['delivery_date']) && $subunit['delivery_date'] != '0000-00-00') echo "<br/>".date("d.m.Y", strtotime($subunit['delivery_date']));
													if($subunit['reference'] != "") echo "<br/>".$formText_DefaultInvoiceReference_output.": ".$subunit['reference'];
													?></td>
													<td>
														<?php if($moduleAccesslevel > 10) { ?>
		                                                <button class="editEntryBtn small output-edit-subunit" data-cid="<?php echo $subunit['id'];?>" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
		                                                <?php } ?>
		                                                <?php if($moduleAccesslevel > 110) { ?>
		                                                <button class="editEntryBtn small output-delete-subunit" data-cid="<?php echo $subunit['id'];?>"><span class="glyphicon glyphicon-trash"></span></button>
		                                                <?php } ?>
													</td>
												</tr>
												<?php
											}
											?>
										</table>
									</div>
									<script type="text/javascript">
										$(".inactiveSubunitInfo").off("click").on("click", function(){
											$(".inactiveSubunitWrapper").slideToggle();
										})
									</script>
									<?php
								}?>
							</div>
						</div>
						<?php
					}

					$s_sql = "SELECT cc.*, cr.companyname as creditorName FROM collecting_cases AS cc
					LEFT JOIN creditor AS cr ON cr.id = cc.creditor_id
					WHERE cc.debitor_id = '".$o_main->db->escape_str($customerData['id'])."' ORDER BY cc.created DESC";
					$o_query = $o_main->db->query($s_sql);
					$v_collecting_cases = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					?>
					<div class="p_contentBlock">
						<div class="p_contentBlockTitle dropdown_content_show show_collecting_cases">
							<?php echo $formText_CollectingCases_Output;?>
							<span class="badge">
								<?php echo count($v_collecting_cases); ?>
							</span>
							<div class="showArrow"><span class="glyphicon"></span></div>
						</div>
						<div class="p_contentBlockContent dropdown_content" >
							<table class="table table-bordered table-striped">
								<tr>
									<th><?php echo $formText_CaseNumber_Output;?></th>
									<th><?php echo $formText_CreditorName_Output;?></th>
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
									?>
									<tr>
										<td><a href="<?php echo $s_edit_link;?>" class="optimize"><?php echo $v_row['id']; ?></a></td>
										<td><?php echo $v_row['creditorName'];?></td>
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
					$s_sql = "SELECT ccc.*, cr.companyname as creditorName FROM collecting_company_cases AS ccc
					LEFT JOIN creditor AS cr ON cr.id = ccc.creditor_id
					WHERE ccc.debitor_id = '".$o_main->db->escape_str($customerData['id'])."' ORDER BY ccc.created DESC";
					$o_query = $o_main->db->query($s_sql);
					$v_collecting_company_cases = ($o_query && $o_query->num_rows()>0) ? $o_query->result_array() : array();
					?>
					<div class="p_contentBlock">
						<div class="p_contentBlockTitle dropdown_content_show show_collecting_company_cases">
							<?php echo $formText_CollectingCompanyCases_Output;?>
							<span class="badge">
								<?php echo count($v_collecting_company_cases); ?>
							</span>
							<div class="showArrow"><span class="glyphicon"></span></div>
						</div>
						<div class="p_contentBlockContent dropdown_content" >
							<table class="table table-bordered table-striped">
								<tr>
									<th><?php echo $formText_CaseNumber_Output;?></th>
									<th><?php echo $formText_CreditorName_Output;?></th>
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
										<td><?php echo $v_row['creditorName'];?></td>
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
					</div>
					<?php

                    if(!$accessElementRestrict_SelfdefinedFields){
                        if($showSelfdefinedFields) {
                            $predefinedFields = array();
                            $s_sql = "SELECT * FROM customer_selfdefined_fields ORDER BY customer_selfdefined_fields.sortnr";
                            $o_query = $o_main->db->query($s_sql);
                            if($o_query && $o_query->num_rows()>0){
                                $predefinedFields = $o_query->result_array();
                            }
                            ?>
                            <div class="p_contentBlock">
                                <div class="p_contentBlockTitle dropdown_content_show show_selfdefinedFields" data-blockid="22">
                                    <?php echo $formText_SelfDefinedFields_Output;?>
                                    <span class="badge">
                                        <?php echo count($predefinedFields); ?>
                                    </span>
                                    <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandSelfdefinedFields']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                                </div>
                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandSelfdefinedFields']){ ?> style="display: block;" <?php } ?>>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" class="selfdefinedTable ">
                                        <?php
                                        foreach($predefinedFields as $predefinedField) {
                                            $predefinedFieldValue = null;
                                            $s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($customerData['id'], $predefinedField['id']));
                                            if($o_query && $o_query->num_rows()>0){
                                                $predefinedFieldValue = $o_query->row_array();
                                            }
                                            $selfdefinedList = null;
                                            $s_sql = "SELECT * FROM customer_selfdefined_lists WHERE id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($predefinedField['list_id']));
                                            if($o_query && $o_query->num_rows()>0){
                                                $selfdefinedList = $o_query->row_array();
                                            }

                                            $resources = array();

                                            $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC";
                                            $o_query = $o_main->db->query($s_sql, array($selfdefinedList['id']));
                                            if($o_query && $o_query->num_rows()>0){
                                                $resources = $o_query->result_array();
                                            }
                                            ?>
                                            <tr>
                                                <?php if($predefinedField['type'] == 1) { ?>
                                                    <td class="txt-label bold" colspan="2" style="width:20%; padding: 6px 10px;"><?php echo $predefinedField['name']?></td>
                                                    <td class="txt-value" style=" padding: 6px 10px;">
                                                        <input type="hidden" class="selfdefinedCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $predefinedField['id']?>" data-selfdefinedfieldid="<?php echo $predefinedField['id'];?>" data-customerid="<?php echo $customerData['id']?>"
                                                        <?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
                                                        />
                                                        <?php if($predefinedField['open_in_popup'] != 1) { ?>
                                                            <?php if($moduleAccesslevel > 10) { ?>
                                                                <select class="selfdefinedFieldValue selfdefinedDropdown">
                                                                    <option value=""><?php echo $formText_Select_output; ?></option>
                                                                    <?php foreach($resources as $resource) { ?>
                                                                        <option value="<?php echo $resource['id']; ?>" <?php echo $resource['id'] == $predefinedFieldValue['value'] ? 'selected="selected"' : ''; ?>><?php echo $resource['name']; ?></option>
                                                                    <?php
                                                                    }
                                                                ?>
                                                                </select>
                                                            <?php } else {
                                                                $singleResource = null;
                                                                $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? AND id = ? ORDER BY name ASC";
                                                                $o_query = $o_main->db->query($s_sql, array($selfdefinedList['id'], $predefinedFieldValue['value']));
                                                                if($o_query && $o_query->num_rows()>0){
                                                                    $singleResource = $o_query->row_array();
                                                                }
                                                                echo $singleResource['name'];
                                                                ?>

                                                            <?php } ?>
                                                        <?php } else {
                                                            $selfdefinedListLine = null;
                                                            $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE customer_selfdefined_list_lines.id = ?";
                                                            $o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['value']));
                                                            if($o_query && $o_query->num_rows()>0){
                                                                $selfdefinedListLine = $o_query->row_array();
                                                            }
                                                            ?>
                                                            <span style="vertical-align: middle;"><?php if($selfdefinedListLine) { echo $selfdefinedListLine['name']; } else { echo $formText_NoValue_output; };?></span>
                                                            <?php
                                                            if($moduleAccesslevel > 10) {
                                                                ?>
                                                                <div class="editListDropdown editEntryBtn"><?php echo $formText_Choose_Output;?></div>
                                                                <div class="resetListDropdown editEntryBtn"><?php echo $formText_Reset_Output;?></div>
                                                                <?php
                                                            }
                                                        } ?>
                                                    </td>
                                                <?php } else if($predefinedField['type'] == 2) { ?>
                                                    <td class="txt-value" style=" padding: 6px 10px;" colspan="3">
                                                        <input type="hidden" class="selfdefinedCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $predefinedField['id']?>" data-selfdefinedfieldid="<?php echo $predefinedField['id'];?>" data-customerid="<?php echo $customerData['id']?>"
                                                        <?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
                                                        />
                                                        <div class="bold">
                                                        <?php echo $predefinedField['name']?>
                                                        </div>
                                                        <div class="selfchkWrapper">
                                                            <?php
                                                            if($moduleAccesslevel > 10) {
                                                                if($predefinedField['open_in_popup'] != 1) {
                                                                    foreach($resources as $resource) {
                                                                        $selected = false;
                                                                        $s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
                                                                        $o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id'], $resource['id']));
                                                                        if($o_query && $o_query->num_rows()>0){
                                                                            $selected = true;
                                                                        }
                                                                        ?>
                                                                        <div>
                                                                            <input type="checkbox" <?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?> class="selfdefinedValueLineChk" value="<?php echo $resource['id'];?>" id="selfdefinedChx<?php echo $predefinedField['id']?>_<?php echo $resource['id']?>" <?php if($selected) { echo "checked";}?> /><label for="selfdefinedChx<?php echo $predefinedField['id']?>_<?php echo $resource['id']?>"></label><label class="labelText" for="selfdefinedChx<?php echo $predefinedField['id']?>_<?php echo $resource['id']?>"><?php echo $resource['name'];?></label>
                                                                        </div>
                                                                    <?php
                                                                    }
                                                                } else {
                                                                    $selfdefinedListLines = array();
                                                                    $s_sql = "SELECT * FROM customer_selfdefined_list_lines JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_list_line_id = customer_selfdefined_list_lines.id
                                                                        WHERE customer_selfdefined_values_connection.selfdefined_value_id = ?";
                                                                    $o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id']));
                                                                    if($o_query && $o_query->num_rows()>0){
                                                                        $selfdefinedListLines = $o_query->result_array();
                                                                    }
                                                                    foreach($selfdefinedListLines as $selfdefinedListLine){
                                                                        ?>
                                                                        <div><?php echo $selfdefinedListLine['name'];?></div>
                                                                        <?php
                                                                    }
                                                                    if($moduleAccesslevel > 10) {
                                                                        ?>
                                                                        <div class="editListCheckboxes editEntryBtn"><?php echo $formText_Choose_Output;?></div>
                                                                        <?php
                                                                    }
                                                                }
                                                            }?>
                                                        </div>
                                                    </td>
                                                    <?php
                                                } else if($predefinedField['type'] == 0) { ?>
                                                    <td class="txt-value" style="width:5%; padding: 6px 10px;">
                                                        <input type="checkbox" class="selfdefinedCheckbox" value="1" <?php if($predefinedFieldValue['active']) echo 'checked="checked"';?> id="selfdefinedCheckbox<?php echo $predefinedField['id']?>" data-selfdefinedfieldid="<?php echo $predefinedField['id'];?>" data-customerid="<?php echo $customerData['id']?>"
                                                        <?php if($moduleAccesslevel <= 10) { ?> disabled<?php } ?>
                                                        /><label for="selfdefinedCheckbox<?php echo $predefinedField['id']?>" style="vertical-align:middle;"></label>
                                                    </td>
                                                    <td class="txt-label bold" style="width:20%; padding: 6px 10px;"><?php echo $predefinedField['name']?></td>
                                                    <td class="txt-value" style=" padding: 6px 10px;">
                                                        <?php
                                                        if($predefinedFieldValue['active']) {
                                                            if(!$predefinedField['hide_textfield']) { ?>
                                                                <div class="preeditBlock <?php if(!$predefinedFieldValue['active']) echo 'inactive';?>">
                                                                <?php
                                                                    if($predefinedFieldValue['value'] != "") { ?>
                                                                        <span style="vertical-align:middle;"><?php echo $predefinedFieldValue['value'];?></span>
                                                                    <?php } else { ?>
                                                                        <span style="vertical-align:middle; color: grey" class="noValueSpan"><?php echo $formText_ValueNotAdded_output;;?></span>
                                                                    <?php } ?>
                                                                    <div class="editBtn editBtnIcon selfdefinedEditBtn"><span class="glyphicon glyphicon-pencil"></span></div>
                                                                </div>
                                                                <div class="selfdefinedFieldValueWrapper">
                                                                    <?php if($moduleAccesslevel > 10) { ?>
                                                                        <input type="text" value="<?php echo $predefinedFieldValue['value'];?>" class="selfdefinedFieldValue" autocomplete="off"/>
                                                                        <div class="saveBtn output-btn small"><?php echo $formText_Save_Output;?></div>
                                                                        <div class="cancelBtn output-btn small"><?php echo $formText_Cancel_Output;?></div>
                                                                    <?php } ?>
                                                                </div>
                                                            <?php } else { ?>
                                                                <?php

                                								$s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
                                								LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
                                								WHERE customer_selfdefined_field_id = ?";
                                								$o_query = $o_main->db->query($s_sql, array($predefinedField['id']));
                                								$selfdefinedLists = $o_query ? $o_query->result_array() : array();

                                                                foreach($selfdefinedLists as $connection){

                                                                    $s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_id = ? AND selfdefined_fields_id = ? AND list_id = ?";
                                                                    $o_query = $o_main->db->query($s_sql, array($customerData['id'], $predefinedField['id'], $connection['id']));
                                                                    if($o_query && $o_query->num_rows()>0){
                                                                        $predefinedFieldValue = $o_query->row_array();
                                                                    }

                                                                    $resources = array();

                                                                    $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC";
                                                                    $o_query = $o_main->db->query($s_sql, array($connection['id']));
                                                                    if($o_query && $o_query->num_rows()>0){
                                                                        $resources = $o_query->result_array();
                                                                    }
                                                                ?>
                                                                    <select class="selfdefinedFieldValue selfdefinedDropdown2">
                                                                        <option value=""><?php echo $formText_Select_output; ?></option>
                                                                        <?php foreach($resources as $resource) {
                                                                            $selected = false;
                                                                            $s_sql = "SELECT * FROM customer_selfdefined_values_connection  WHERE selfdefined_value_id = ? AND selfdefined_list_line_id = ?";
                                                                            $o_query = $o_main->db->query($s_sql, array($predefinedFieldValue['id'], $resource['id']));
                                                                            if($o_query && $o_query->num_rows()>0){
                                                                                $selected = true;
                                                                            }
                                                                        ?>
                                                                            <option value="<?php echo $resource['id']; ?>" <?php echo $selected ? 'selected="selected"' : ''; ?>><?php echo $resource['name']; ?></option>
                                                                        <?php
                                                                        }
                                                                    ?>
                                                                    </select>
                                                                <?php } ?>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    </td>
                                                <?php } ?>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <?php
                    function output_folder_html($folder_id, $customer_id, $subunit_id = NULL) {
                        global $o_main;
                        global $moduleAccesslevel;
                        global $formText_AddFile_output;
                        global $formText_AddFolder_output;
                        global $formText_EditFolder_output;
                        global $extradomaindirroot;

                        $s_property = '';
						$s_sql_where = '';
						$s_sql_files_where = '';
						if(NULL == $subunit_id)
						{
							$s_sql_where .= " AND (subunit_id IS NULL OR subunit_id = 0)";
							$s_sql_files_where .= " AND (subunit_id IS NULL OR subunit_id = 0)";
						} else {
							$s_property .= ' data-subunit-id="'.$subunit_id.'"';
							$s_sql_where .= " AND subunit_id = '".$o_main->db->escape_str($subunit_id)."'";
							$s_sql_files_where .= " AND subunit_id = '".$o_main->db->escape_str($subunit_id)."'";
						}
						if($folder_id == 0){
                            $s_sql_where .= " AND (parent_id IS NULL OR parent_id = 0)";
                        } else {
                            $s_sql_where .= " AND parent_id = '".$o_main->db->escape_str($folder_id)."'";
                        }
						$s_sql = "SELECT * FROM customer_folders WHERE content_status < 2 AND customer_id = '".$o_main->db->escape_str($customer_id)."'".$s_sql_where." ORDER BY name ASC";
						$o_query = $o_main->db->query($s_sql);
						$folders = ($o_query ? $o_query->result_array() : array());
                        ?>
                        <ul class="folder-list folder-list<?php echo $folder_id;?>">
                            <?php
                            if(count($folders) > 0){
                                foreach($folders as $folder) {
                                    ?>
                                    <li class="customer-folder customer-folder<?php echo $folder['id']?>" data-folder-id="<?php echo $folder['id']?>">
                                        <span class="name_wrapper">
                                            <span class="fa-folder fas fw_icon_color"></span> <?php echo $folder['name']; ?>
                                        </span>
                                        <?php if($moduleAccesslevel > 10) { ?>
                                            <button class="addEntryBtn addCustomerFilesBtn" data-customer-id="<?php echo $customer_id; ?>" data-folder-id="<?php echo $folder['id'];?>"<?php echo $s_property;?>><?php echo $formText_AddFile_output;?></button>
                                            <button class="addEntryBtn addCustomerFoldersBtn" data-customer-id="<?php echo $customer_id; ?>" data-parent-id="<?php echo $folder['id'];?>"<?php echo $s_property;?>><?php echo $formText_AddFolder_output;?></button>
                                            <button class="addEntryBtn addCustomerFoldersBtn" data-customer-id="<?php echo $customer_id; ?>" data-folder-id="<?php echo $folder['id'];?>"<?php echo $s_property;?>><?php echo $formText_EditFolder_output; ?></button>
                                        <?php } ?>
                                        <?php if($moduleAccesslevel > 110) { ?>
                                            <a href="#" class="deleteFolderCustomer" data-customerfolder-id="<?php echo $folder['id'];?>">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </a>
                                        <?php } ?>
                                        <?php
                                        output_folder_html($folder['id'], $customer_id, $subunit_id);
                                        ?>
                                    </li>
                                    <?php
                                }
                            }
							?>
							<li>
								<div class="output-filelist">
									<ul>
										<?php
										if($folder_id == 0){
											$s_sql = "SELECT * FROM customer_files WHERE content_status < 2 AND customer_id = ? AND (folder_id IS NULL OR folder_id = '')".$s_sql_files_where." ORDER BY created DESC";
											$o_query = $o_main->db->query($s_sql, array($customer_id));
											$files = ($o_query ? $o_query->result_array() : array());
										} else {
											$s_sql = "SELECT * FROM customer_files WHERE content_status < 2 AND customer_id = ? AND folder_id = ?".$s_sql_files_where." ORDER BY created DESC";
											$o_query = $o_main->db->query($s_sql, array($customer_id, $folder_id));
											$files = ($o_query ? $o_query->result_array() : array());
										}
										foreach($files as $fileItem) {
											$file = json_decode($fileItem['file'], true);
											$fileName = $file[0][0];
											$fileUrl = $extradomaindirroot.'/../'.$file[0][1][0];
											if(strpos($file[0][1][0],'uploads/protected/')!==false)
											{
												$fileUrl = $extradomaindirroot.'/../'.$file[0][1][0].'?caID='.$_GET['caID'].'&table=customer_files&field=file&ID='.$fileItem['id'];
											}
											?>
												<li>
													<a href="<?php echo $fileUrl; ?>" download target="_blank">
														<span class="file_infront">&nbsp;</span> <?php echo $fileName; ?>
													</a>
													<?php if($moduleAccesslevel > 110) { ?>
														<a href="#" class="deleteFileCustomer" data-customerfile-id="<?php echo $fileItem['id'];?>">
															<span class="glyphicon glyphicon-trash"></span>
														</a>
													<?php } ?>
												</li>
											<?php
										}
										?>
									</ul>
								</div>
							</li>
                        </ul>
                        <?php
                    }

                    if(!$accessElementRestrict_Files){
                        if($showFile) {

                            $s_sql = "SELECT * FROM customer_files WHERE content_status < 2 AND customer_id = ? AND (folder_id is null OR folder_id = 0) ORDER BY created DESC";
                            $o_query = $o_main->db->query($s_sql, array($cid));
                            $files_first = ($o_query ? $o_query->result_array() : array());

                            $s_sql = "SELECT * FROM customer_files WHERE content_status < 2 AND customer_id = ?  ORDER BY created DESC";
                            $o_query = $o_main->db->query($s_sql, array($cid));
                            $files = ($o_query ? $o_query->result_array() : array());
                            ?>
        					<div class="p_contentBlock">
                                <div class="p_contentBlockTitle dropdown_content_show" data-blockid="14"><?php echo $formText_Files_Output;?>
                                     <span class="badge">
                                        <?php echo count($files); ?>
                                    </span>
                                    <div class="showArrow"><span class="glyphicon  <?php if($customer_basisconfig['expandFiles']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>

                                </div>
                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandFiles']){ ?> style="display: block;" <?php } ?>>
                                    <?php
                                    /* if (check_if_customer_folder_created($o_main, $cid)): ?>
                                        <?php echo $formText_FolderForCustomerIsCreated_output; ?>

                                    <?php else: ?>
                                        <?php echo $formText_NoFolderCreated_output; ?>.
                                        <a href="#" class="createFilearchiveFolder"><?php echo $formText_CreateNow_output; ?></a>
                                    <?php endif;*/ ?>
                                    <?php if($moduleAccesslevel > 10) { ?>
                                        <button class="addEntryBtn addCustomerFilesBtn" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_AddFile_output;?></button>
                                        <button class="addEntryBtn addCustomerFoldersBtn" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_AddFolder_output;?></button>
                                    <?php } ?>
                                    <div class="output-folderlist">
                                        <?php
                                        output_folder_html(0, $cid);
                                        ?>
                                    </div>
            						<div id="output-filelist" class="output-filelist">
            							<?php
                                        // Folder files
                                        $current_content_id = $cid;
                                        $content_table = 'customer';
                                        $s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
                                        if($o_query && $o_query->num_rows()>0){
                                            $folder_data = $o_query->row_array();
                                        } else { $folder_data = array(); }
                                        if(isset($folder_data['id'])){ $folder_id = $folder_data['id']; } else {  $folder_id = ''; }
                                        $files = array();
                                        $s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
                                        $o_query = $o_main->db->query($s_sql, array($folder_id));
                                        $files = $o_query ? $o_query->result_array(): array();
                                        if($o_query && $o_query->num_rows()>0){
                                            $files = $o_query->result_array();
                							?>
                							<ul>
                								<?php
                                                foreach($files as $file) {
                                                    $s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC LIMIT 1";
                                                    $o_query = $o_main->db->query($s_sql, array($file['id']));
                                                    if($o_query && $o_query->num_rows()>0){
                                                        $file_version_data = $o_query->row_array();
                                                    }
                									$fileInfo = json_decode($file_version_data['file'], true);
                									$fileParts = explode('/',$fileInfo[0][1][0]);
                									$fileName = array_pop($fileParts);
                									$fileParts[] = rawurlencode($fileName);
                									$filePath = implode('/',$fileParts);
                									$fileUrl = $fileInfo[0][1][0];
                									$fileName = $fileInfo[0][0];
                									$fileUrl = "";
                									if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                									{
                										$fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=sys_filearchive_file_version&field=file&ID='.$file_version_data['id'];
                									}
                								?>
                									<li>
                										<a href="<?php echo $fileUrl; ?>">
                											<span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                										</a>
                										<span class="fileFolderPath">
                											<span class="glyphicon glyphicon-folder-open"></span> <?php echo getFullFolderPathForFile($file['id'], $o_main); ?>
                										</span>
                                                        <?php if($moduleAccesslevel > 110) { ?>
                										<a href="#" class="deleteFile" data-deletefileid="<?php echo $file['id']; ?>">
                											<span class="glyphicon glyphicon-trash"></span>
                										</a>
                                                        <?php } ?>
                									</li>
                								<?php } ?>
                							</ul>
                                        <?php } ?>
            						</div>
									<?php
									if($v_customer_accountconfig['activate_subunits'])
									{
										$sql_subunit_sql = "";
										if($subunit_filter > 0) {
											$sql_subunit_sql = " AND id = '".$o_main->db->escape_str($subunit_filter)."'";
										}
										$s_sql = "SELECT * FROM customer_subunit WHERE customer_id = '".$o_main->db->escape_str($customerData['id'])."'".$sql_subunit_sql." ORDER BY id ASC";
										$o_query = $o_main->db->query($s_sql);
										$subunits = $o_query ? $o_query->result_array() : array();

										foreach($subunits as $subunit)
										{
											?>
											<div class="output-files-subunit">
												<div>
													<b><?php echo $subunit['name'];?></b>
													<?php if($moduleAccesslevel > 10) { ?>
														<button class="addEntryBtn addCustomerFilesBtn" data-customer-id="<?php echo $cid;?>" data-subunit-id="<?php echo $subunit['id'];?>"><?php echo $formText_AddFile_output;?></button>
														<button class="addEntryBtn addCustomerFoldersBtn" data-customer-id="<?php echo $cid;?>" data-subunit-id="<?php echo $subunit['id'];?>"><?php echo $formText_AddFolder_output;?></button>
													<?php } ?>
												</div>
												<div class="output-folderlist">
													<?php
													output_folder_html(0, $cid, $subunit['id']);
													?>
												</div>
											</div>
											<?php
										}
									}
									?>
                                </div>
        					</div>
                    <?php } ?>
                <?php } ?>

                    <?php
                    if(!$accessElementRestrict_CleaningWorkers){
                        if($customer_basisconfig['displayCleaningWorkersSection']) {
                            $regularWorkers = array();

                            $s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = $cid";
                            $o_query = $o_main->db->query($s_sql);
                            $repeatingOrders = ($o_query->num_rows() > 0 ? $o_query->result_array() : array());

                            foreach($repeatingOrders as $repeatingOrder) {
                                $repeatingorderlines = array();

                                $s_sql = "SELECT * FROM repeatingorderwork WHERE repeatingorderwork.repeatingOrderId = ?";
                                $o_query = $o_main->db->query($s_sql, array($repeatingOrder['id']));
                                $findRepeatingOrderWorks = ($o_query->num_rows() > 0 ? $o_query->result_array() : array());

                                foreach($findRepeatingOrderWorks as $findRepeatingOrderWork) {
                                    $s_sql = "SELECT * FROM repeatingorderworkline WHERE repeatingorderworkline.repeatingOrderWorkId = ?";
                                    $o_query = $o_main->db->query($s_sql, array($findRepeatingOrderWork['id']));
                                    $findRepeatingOrderWorklines = ($o_query->num_rows() > 0 ? $o_query->result_array() : array());

                                    foreach($findRepeatingOrderWorklines as $findRepeatingOrderWorkline){
                                        $s_sql = "SELECT * FROM reporderworklineworker WHERE reporderworklineworker.repeatingOrderWorkLineId = ?";
                                        $o_query = $o_main->db->query($s_sql, array($findRepeatingOrderWorkline['id']));
                                        $findRepeatingOrderWorklineWorkers = ($o_query->num_rows() > 0 ? $o_query->result_array() : array());
                                        foreach($findRepeatingOrderWorklineWorkers as $findRepeatingOrderWorklineWorker) {
                                            if(!in_array($findRepeatingOrderWorklineWorker['employeeId'], $regularWorkers)){
                                                array_push($regularWorkers, $findRepeatingOrderWorklineWorker['employeeId']);
                                            }
                                        }
                                    }
                                }
                            }
                        ?>

                        <div class="p_contentBlock">
                            <div class="p_contentBlockTitle dropdown_content_show show_cleaningWorkers" data-blockid="4"><?php echo $formText_RegularWorkers_Output;?>
                                <span class="badge">
                                    <?php echo count($regularWorkers); ?>
                                </span>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandCleaningWorkers']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div></div>
                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandCleaningWorkers']){ ?> style="display: block;" <?php } ?>>
                            <div class="p_contentBlockInner">
                                <?php
                                ?>
                                <table class="table table-bordered table-striped">
                                    <tr>
                                        <?php /*<th width="<?php echo $tdWidth;?>"></th>*/?>
                                        <th><?php echo $formText_Name_output; ?></th>
                                        <th><?php echo $formText_Title_output; ?></th>
                                        <th><?php echo $formText_Mobile_output; ?></th>
                                        <th><?php echo $formText_ContactEmail_output; ?></th>
                                        <th><?php echo $formText_Orders_output; ?></th>
                                    </tr>
                                    <?php
                                    foreach($regularWorkers as $regularWorker) {
                                        $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($regularWorker));
                                        $employee = ($o_query->num_rows() > 0 ? $o_query->row_array() : array());

                                        $projects = array();

                                        $s_sql = "SELECT subscriptionmulti.* FROM reporderworklineworker
                                            LEFT OUTER JOIN repeatingorderworkline ON repeatingorderworkline.id = reporderworklineworker.repeatingOrderWorkLineId
                                            LEFT OUTER JOIN repeatingorderwork ON repeatingorderwork.id = repeatingorderworkline.repeatingOrderWorkId
                                            LEFT OUTER JOIN subscriptionmulti ON subscriptionmulti.id = repeatingorderwork.repeatingOrderId WHERE reporderworklineworker.employeeId = ?
                                            GROUP BY subscriptionmulti.id";
                                        $o_query = $o_main->db->query($s_sql, array($regularWorker));
                                        $projects = ($o_query->num_rows() > 0 ? $o_query->result_array() : array());
                                        ?>
                                        <tr>
                                            <td><?php echo $employee['name'];?></td><td><?php echo $employee['title'];?></td><td><?php echo $employee['mobile'];?></td><td><?php echo $employee['email'];?></td>
                                            <td><?php
                                            $countProject = 1;
                                            $totalCountProject = count($projects);
                                            foreach($projects as $project) { echo $project['subscriptionName']; if($countProject != $totalCountProject) echo ", "; $countProject++; }
                                            ?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                            </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } ?>
                    <?php
                    if(!$accessElementRestrict_GetynetConnection) {
                        if($b_show_getynet_connection) { ?>
                            <div class="p_contentBlock">
                                <div class="p_contentBlockTitle"><?php
    							echo $formText_GetynetConnection_Output.' ';
    							if(intval($customerData['getynet_customer_id']) == 0)
    							{
    								if($moduleAccesslevel > 10)
    								{
    									?><button id="output-add-getynet-connection" class="addEntryBtn" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_Connect_output;?></button><?php
    								}
    							} else {
    								echo '(ID: '.$customerData['getynet_customer_id'].') ';
    								if($moduleAccesslevel > 10)
    								{
    									?><button id="output-add-getynet-account" class="addEntryBtn" data-customer-id="<?php echo $cid;?>"><?php echo $formText_CreateAccount_output;?></button><?php
    									if($b_enable_grant_admin || $b_enable_grant_system_admin || $b_enable_grant_developer)
    									{
    										?><button class="output-btn small output-grant-getynet-access" data-customer-id="<?php echo $cid;?>"><?php echo $formText_GrantAccess_Output;?></button><?php
    									}
    								}
    								?><button id="output-show-getynet-accounts" class="output-btn small" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_ShowAccounts_Output;?></button><?php
    							}
    							?>
                                </div>
    						</div>
                        <?php } ?>
                    <?php } ?>
                    <?php

                    // $mboxCheck = imap_check($mbox);
                    // $totalMessages = $mboxCheck->Nmsgs;
                    // $showMessages = 10;
                    // var_dump($totalMessages);
                    // $result = array_reverse(imap_fetch_overview($mbox,($totalMessages-$showMessages+1).":".$totalMessages));

                    // iterate trough those messages
                    // foreach ($result as $mail) {

                    //     // print_r($mail);

                    //     // // if you want the mail body as well, do it like that. Note: the '1.1' is the section, if a email is a multi-part message in MIME format, you'll get plain text with 1.1
                    //     // $mailBody = imap_fetchbody($mbox, $mail->msgno, '1.1');

                    //     // // but if the email is not a multi-part message, you get the plain text in '1'
                    //     // if(trim($mailBody)=="") {
                    //     //     $mailBody = imap_fetchbody($mbox, $mail->msgno, '1');
                    //     // }

                    //     // // just an example output to view it - this fit for me very nice
                    //     // echo nl2br(htmlentities(quoted_printable_decode($mailBody)));
                    // }$sql = "SELECT * FROM emailintegration ORDER BY emailName ASC";

                    ?>
                    <?php
                    if(!$accessElementRestrict_Emails) {
                        if($customer_basisconfig['activateEmails']) { ?>
                            <?php if($showMails) { ?>
                                <div class="p_contentBlock">
                                    <div class="p_contentBlockTitle show_emails show_dropdown" data-customer-id="<?php echo $cid; ?>">

                                        <?php echo $formText_Emails_Output;?>

                                        <?php if($moduleAccesslevel > 10) { ?>
                                            <button class="addEntryBtn newEmail" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_NewEmail_output;?></button>
                                        <?php } ?>
                                        <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                                    </div>
                                    <div class="p_contentBlockContent emails_content dropdown_content">
                                        <div class="contentLoading" id="emailContentLoading"><img src="../elementsGlobal/ajax.svg"/></div>
                                        <div class="emailContents">

                                        </div>
                                    </div>

                                </div>
                            <?php } ?>
                        <?php }?>
                    <?php }?>

                    <?php
                    if(!$accessElementRestrict_Transactions) {
                        if($customer_basisconfig['activateTransactions']) { ?>
                            <div class="p_contentBlock">
                                <?php
                                $ownercompanies_list = array();
                                // List of ownercompanies
                                $s_sql = "SELECT * FROM ownercompany";
                                $o_query = $o_main->db->query($s_sql);
                                if($o_query && $o_query->num_rows()>0){
                                    $ownercompanies_list = $o_query->result_array();
                                }
                                $ownercompanies_list_new = array();
                                foreach($ownercompanies_list as $row){
                                    // Name that will appear in dropdown
                                    $nameToDisplay = $row['name'];

                                    // Integration name
                                    $integration_name = $row['use_integration'] ? $row['use_integration'] : $formText_NoIntegration_output;
                                    $nameToDisplay .= ' - ' . $integration_name;

                                    $s_sql = "SELECT * FROM customer_externalsystem_id WHERE customer_id = ? AND ownercompany_id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($cid, $row['id']));
                                    if($o_query && $o_query->num_rows()>0){
                                    } else {
                                        $nameToDisplay .= ' - ' . $formText_NoCustomerId_output;
                                    }

                                    // Add nameToDisplay
                                    $row['nameToDisplay'] = $nameToDisplay;
                                    array_push($ownercompanies_list_new, $row);
                                }
                                ?>

                                <div class="p_contentBlockTitle">
                                    <?php echo $formText_Transactions_Output;?>
                                    <select id="output-change-transaction-ownercompany" data-customer-id="<?php echo $cid; ?>">
                                        <option value="0"><?php echo $formText_ChooseOwnerCompany_output; ?></option>
                                        <?php foreach ($ownercompanies_list_new as $ownercompany): ?>
                                            <option value="<?php echo $ownercompany['id']; ?>"><?php echo $ownercompany['nameToDisplay']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                                </div>

                                <div class="p_contentBlockContent dropdown_content transactions_content"></div>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <?php
                    if(!$accessElementRestrict_PriceMatrix) {
                        if($article_accountconfig['activateArticlePriceMatrix'] || $article_accountconfig['activateArticleDiscountMatrix']) {

                        ?>
                            <div class="p_contentBlock">
                                <div class="p_contentBlockTitle dropdown_content_show show_articleMatrix" data-blockid="6"><?php echo $formText_PriceAndDiscountMatrix_Output;?>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandArticleMatrix']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div></div>
                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandArticleMatrix']){ ?> style="display: block;" <?php } ?>>
                                    <table class="mainTable matrixTable" border="0" cellpadding="0" cellspacing="0">
                                        <?php if($article_accountconfig['activateArticlePriceMatrix']) {
                                            $s_sql = "SELECT * FROM articlepricematrix WHERE id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($customerData['articlePriceMatrixId']));
                                            $articlePriceMatrix = $o_query ? $o_query->row_array() : array();
                                            ?>
                                            <tr>
                                                <td class="txt-label"><?php echo $formText_ArticlePriceMatrix_output;?></td>
                                                <td class="txt-value"><?php echo $articlePriceMatrix['name'];?></td>
                                            </tr>
                                        <?php } ?>
                                        <?php if($article_accountconfig['activateArticleDiscountMatrix']) {
                                            $s_sql = "SELECT * FROM articlediscountmatrix WHERE id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($customerData['articleDiscountMatrixId']));
                                            $articleDiscountMatrix = $o_query ? $o_query->row_array() : array();
                                             ?>
                                            <tr>
                                                <td class="txt-label"><?php echo $formText_ArticleDiscountMatrix_output;?></td>
                                                <td class="txt-value"><?php echo $articleDiscountMatrix['name'];?></td>
                                            </tr>
                                        <?php } ?>
                                        <tr>
                                            <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-customer-articlematrix editBtnIcon" data-customer-id="<?php echo $cid; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?php

                    if($customer_basisconfig['activateMessagesSection']) {
                        $sqlNoLimit = "SELECT p.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) AS customerName
                        FROM message_center_message p
                        LEFT OUTER JOIN customer c ON c.id = p.customer_id
                        WHERE c.id = '".$o_main->db->escape_str($cid)."' AND (p.message_type = 1 OR p.message_type = 2) GROUP BY p.id ORDER BY p.created DESC";
                        $o_query = $o_main->db->query($sqlNoLimit);
                        $message_center_messages = $o_query ? $o_query->result_array() : array();
                        $incomingMessages_count = 0;
                        $outgoingMessages_count = 0;
                        foreach($message_center_messages as $message_center_message){
                            if($message_center_message['message_type'] == 2){
                                $outgoingMessages_count++;
                            } else if($message_center_message['message_type'] == 1){
                                $incomingMessages_count++;
                            }
                        }
                        ?>
                        <div class="p_contentBlock">
                            <div class="p_contentBlockTitle dropdown_content_show show_messageCenter" data-blockid="6">
                                <?php echo $formText_MessagesWithCustomer_Output;?>
                                <span class="badge"><?php echo $incomingMessages_count;?></span> / <span class="badge"><?php echo $outgoingMessages_count;?></span>
                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandMessageCenter']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div></div>
                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandMessageCenter']){ ?> style="display: block;" <?php } ?>>
                                <table class="mainTable matrixTable" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <th><?php echo $formText_Date_output;?></th>
                                        <th><?php echo $formText_MessageType_output;?></th>
                                        <th><?php echo $formText_Sender_output;?></th>
                                        <th><?php echo $formText_Receiver_output;?></th>
                                        <th><?php echo $formText_Message_output;?></th>
                                        <th><?php echo $formText_Handled_output;?></th>
                                        <th><?php echo $formText_Read_output;?></th>
                                        <th></th>
                                    </tr>
                                    <?php
                                    $messageTypes = array($formText_None_output, $formText_FromCustomerPortal_output, $formText_ToCustomerPortal_output, $formText_FromEmployeeInApp_output);
                            		foreach($message_center_messages as $message_center_message) {
                            			?>
                            			<tr>
                            				<td><?php echo date("d.m.Y", strtotime($message_center_message['created']));?></td>
                            	            <td><?php echo $messageTypes[intval($message_center_message['message_type'])];?></td>
                            				<td><?php echo $message_center_message['sender_name']."</br>".$message_center_message['sender_username'];?></td>
                            				<td><?php echo $message_center_message['receiver_name']."</br>".$message_center_message['receiver_username'];?></td>
                            				<td><?php echo $message_center_message['message'];?></td>
                            				<td><?php if($message_center_message['handled_date'] != "" && $message_center_message['handled_date'] != "0000-00-00") echo date("d.m.Y", strtotime($message_center_message['handled_date']));?></td>
                                            <td><?php if($message_center_message['read_date'] != "" && $message_center_message['read_date'] != "0000-00-00") echo date("d.m.Y", strtotime($message_center_message['read_date']));?></td>
                                            <td>
                                                <!-- <?php if($moduleAccesslevel > 10 && intval($message_center_message['message_type']) == 2) { ?>
                                                    <button class="output-btn small output-edit-customer-message editBtnIcon" data-message-id="<?php echo $message_center_message['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button>
                                                <?php } ?> -->
                                                <span class="output-btn small editBtnIcon readFullMessage" data-message-id="<?php echo $message_center_message['id']?>"><?php echo $formText_ReadFullMessage_output;?></span>
                                            </td>

                                        </tr>
                            			<?php
                            		}
                            		?>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    if($customer_basisconfig['activate_case_section']) {
                        $sql_where = "";
                        if($variables->useradmin){

                        } else {
                            $sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
                            $o_query = $o_main->db->query($sql, array($variables->loggID, $people_contactperson_type));
                            $currentLoggedUser = $o_query ? $o_query->row_array() : array();
                            $sql_where .= " AND ((p.case_access = 2 AND p.responsible_person_id = ".$o_main->db->escape($currentLoggedUser['id']).") OR p.case_access <> 2)";
                        }

                        $sqlNoLimit = "SELECT p.*, CONCAT_WS(' ',c.name, c.middlename, c.lastname) AS customerName, CONCAT_WS(' ',e.name, e.middlename, e.lastname) as projectLeaderName,
                                ptm1.created as lastMessageDate
                        FROM case_crm p
                        LEFT OUTER JOIN customer c ON c.id = p.customer_id
                        LEFT JOIN contactperson e ON e.id = p.responsible_person_id
                        LEFT OUTER JOIN case_crm_message ptm1 ON ptm1.case_id = p.id
                        WHERE c.id = '".$o_main->db->escape_str($cid)."'".$sql_where." GROUP BY p.id ORDER BY p.created DESC";

                        $o_query = $o_main->db->query($sqlNoLimit);
                        $message_center_messages = $o_query ? $o_query->result_array() : array();
                        $incomingMessages_count = 0;
                        $outgoingMessages_count = 0;
                        foreach($message_center_messages as $message_center_message) {
                            if($message_center_message['case_type'] == 0){
                                $outgoingMessages_count++;
                            } else if($message_center_message['case_type'] == 1){
                                $incomingMessages_count++;
                            }
                        }
                        ?>
                        <div class="p_contentBlock">
                            <div class="p_contentBlockTitle dropdown_content_show show_messageCenter" data-blockid="6">
                                <?php echo $formText_Cases_Output; ?>
                                <span class="badge"><?php echo $incomingMessages_count;?></span> / <span class="badge"><?php echo $outgoingMessages_count;?></span>
                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandCases']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div></div>
                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandCases']){ ?> style="display: block;" <?php } ?>>
                                <table class="mainTable matrixTable" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <th><?php echo $formText_Created_output;?></th>
                                        <th><?php echo $formText_LastActivity_output;?></th>
                                        <th><?php echo $formText_Subject_output;?></th>
                                        <th><?php echo $formText_ResponsiblePerson_output;?></th>
                                        <th><?php echo $formText_CaseType_output;?></th>
                                        <th><?php echo $formText_Status_output;?></th>
                                        <th></th>
                                    </tr>
                                    <?php
                                    $messageTypes = array($formText_None_output, $formText_FromCustomerPortal_output, $formText_ToCustomerPortal_output, $formText_FromEmployeeInApp_output);
                                    $caseStatus = array($formText_Unhandled_output, $formText_Finished_output, $formText_UnderWork_output);
                                    $caseType = array($formText_MessageFromCustomer_output, $formText_MessageFromEmployee_output);

                            		foreach($message_center_messages as $message_center_message) {
                                        $caseLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CaseCrm&folderfile=output&folder=output&inc_obj=details&cid=".$message_center_message['id'];

                            			?>
                            			<tr>
                            				<td><?php echo date("d.m.Y", strtotime($message_center_message['created']));?></td>
                            				<td><?php echo date("d.m.Y", strtotime($message_center_message['lastMessageDate']));?></td>
                            				<td><?php echo $message_center_message['subject'];?></td>
                            				<td><?php echo $message_center_message['projectLeaderName'];?></td>
                            	            <td><?php echo $caseType[intval($message_center_message['case_type'])];?></td>
                            	            <td><?php echo $caseStatus[intval($message_center_message['status'])];?></td>
                            	            <td><a href="<?php echo $caseLink;?>"><?php echo $formText_ViewCaseDetails_output?></a></td>

                                        </tr>
                            			<?php
                            		}
                            		?>
                                </table>
                            </div>
                        </div>
                    <?php } ?>
                    <?php
                    function writeCollectingOrders($collectingOrders) {
                        global $o_main;
                        global $formText_OrderDate_output;
                        global $formText_OrderId_output;
                        global $formText_YourContact_output;
                        global $moduleAccesslevel;
                        global $formText_ArticleNr_output;
                        global $formText_ProductName_output;
                        global $formText_PricePerPiece_output;
                        global $formText_Quantity_output;
                        global $formText_Discount_output;
                        global $formText_PriceTotal_output;
                        global $formText_ApprovedForBatchInvoicing_output;
                        global $formText_SeperatedInvoice_output;
                        global $formText_Total_output;
                        global $formText_CreateInvoice_output;
                        global $formText_AttachFilesForInvoice_output;
                        global $formText_Reference_Output;
                        global $formText_DeliveryDate_Output;
                        global $formText_DeliveryAddress_Output;
                        global $formText_AddFile_output;
                        global $extradomaindirroot;
                        global $formText_CreateOrderConfirmation_output;
                        global $formText_SendEmail_output;
                        global $formText_LastTimeSent_output;
                        global $formText_LastTimeOpened;
                        global $formText_ShowPreviousOfferPdfs_output;
						global $v_country;
						global $variables;
						global $formText_ConvertToInvoiceAlreadyInvoiced_output;

                        foreach($collectingOrders as $collectingOrder){
                            $totalOrderPrice = 0;
                            $s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ? AND orders.content_status = 0 ORDER BY orders.id ASC";
                            $o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
                            $orders = ($o_query ? $o_query->result_array() : array());

                            $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                            $o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
                            $collectingOrderContactPerson = $o_query ? $o_query->row_array() : array();
                            ?>
                            <div class="collectingOrder">
                                <table class="table table-bordered">
                                    <tr>
                                        <th colspan="6" >
                                            <?php if($collectingOrder['date'] != "" && $collectingOrder['date'] != "0000-00-00" ) { ?>
                                            <div style="float: left; margin-right: 10px;">
                                                    <?php echo $formText_OrderDate_output;?>: <span><?php echo date("d.m.Y", strtotime($collectingOrder['date']));?></span>
                                            </div>
                                            <?php } ?>
                                            <div style="float: left; margin-right: 10px;">
                                                <?php echo $formText_OrderId_output;?>: <span><?php echo $collectingOrder['id'];?></span>

                                            </div>
                                            <?php if($collectingOrderContactPerson != "") { ?>
                                            <div style="float: left">
                                                <?php echo $formText_YourContact_output;?>: <span><?php echo $collectingOrderContactPerson['name']." ".$collectingOrderContactPerson['middlename']." ".$collectingOrderContactPerson['lastname'];?></span>
                                            </div>
                                            <?php } ?>
                                            <div style="float: right;">
                                            <?php if(!$collectingOrder['approvedForInvoicing']) { ?>
                                                <?php if($moduleAccesslevel > 10) { ?>
                                                <button class="output-btn small output-edit-collectingorder editBtnIcon" data-project-id="<?php echo $collectingOrder['id']?>">
                                                    <span class="glyphicon glyphicon-pencil"></span>
                                                </button>
                                                <?php } ?>
                                                <?php if($moduleAccesslevel > 110) { ?>
                                                <button class="output-btn small output-delete-collectingorder editBtnIcon"  data-project-id="<?php echo $collectingOrder['id']?>">
                                                    <span class="glyphicon glyphicon-trash"></span>
                                                </button>
                                                <?php } ?>
                                            <?php } ?>
                                            </div>
                                            <div class="clear"></div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="6">
                                            <table style="width: 100%; table-layout: fixed;">
                                                <tr>
                                                    <td class="tableInfoTd" width="180px"><?php echo $formText_Reference_Output;?></td>
                                                    <td class="tableInfoTd">
                                                        <?php if(!empty($collectingOrder['reference'])) { ?>
                                                        <span class="tableInfoLabel"><?php echo $collectingOrder['reference'];?></span>
                                                        <?php } ?>
                                                        <div class="editEntryBtn editOrderReference glyphicon glyphicon-pencil" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"></div>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tableInfoTd"><?php echo $formText_DeliveryDate_Output;?></td>
                                                    <td class="tableInfoTd">
                                                        <?php if(!empty($collectingOrder['delivery_date']) && $collectingOrder['delivery_date'] != '0000-00-00') { ?>
                                                        <span class="tableInfoLabel"><?php echo date('d.m.Y', strtotime($collectingOrder['delivery_date']));?></span>
                                                        <?php } ?>
                                                        <span class="editEntryBtn editDeliveryDate glyphicon glyphicon-pencil" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tableInfoTd"><?php echo $formText_DeliveryAddress_Output;?></td>
                                                    <td class="tableInfoTd">
                                                        <?php
                                                        $s_delivery_address = trim(preg_replace('/\s+/', ' ', $collectingOrder['delivery_address_line_1'].' '.$collectingOrder['delivery_address_line_2'].' '.$collectingOrder['delivery_address_city'].' '.$collectingOrder['delivery_address_postal_code'].' '.$v_country[$collectingOrder['delivery_address_country']]));
                                                        if(!empty($s_delivery_address)) { ?>
                                                        <span class="tableInfoLabel"><?php echo $s_delivery_address;?></span>
                                                        <?php } ?>
                                                        <span class="editEntryBtn editDeliveryInfo glyphicon glyphicon-pencil" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="tableInfoTd"><?php echo $formText_AttachFilesForInvoice_output;?></td>
                                                    <td class="tableInfoTd">
                                                        <div class="filesAttachedToInvoice">
                                                            <div class="attachedFiles">
                                                                <table style="width: 100%; table-layout: fixed;">
                                                                <?php
                                                                $attachedFiles = json_decode($collectingOrder['files_attached_to_invoice'], true);

                                                                foreach($attachedFiles as $file){
                                                                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=customer_collectingorder&field=files_attached_to_invoice&ID='.$collectingOrder['id'];

                                                                    ?>
                                                                        <tr>
                                                                            <td style="padding: 0;" width="90%"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></td>
                                                                            <td style="padding: 0 10px;" width="10%" style="text-align: right;">
                                                                                <?php if($moduleAccesslevel > 110) { ?>
                                                                                <button class="output-btn small output-delete-attachedfile editBtnIcon" data-collectingorder-id="<?php echo $collectingOrder['id']; ?>" data-uid="<?php echo $file[4];?>">
                                                                                    <span class="glyphicon glyphicon-trash"></span>
                                                                                <?php } ?>

                                                                            </td>
                                                                        </tr>
                                                                    <?php
                                                                }
                                                                ?>
                                                                </table>
                                                            </div>
                                                            <div class="attachFiles" data-collectingorder-id="<?php echo $collectingOrder['id'];?>">+ <?php echo $formText_AddFile_output;?></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>



                                        </th>
                                    </tr>
                                    <tr>
                                        <th><b><?php echo $formText_ArticleNr_output;?></b></th>
                                        <th><b><?php echo $formText_ProductName_output;?></b></th>
                                        <th><b><?php echo $formText_Quantity_output;?></b></th>
                                        <th class="rightAligned"><b><?php echo $formText_PricePerPiece_output;?></b></th>
                                        <th><b><?php echo $formText_Discount_output;?></b></th>
                                        <th class="rightAligned"><b><?php echo $formText_PriceTotal_output;?></b></th>
                                    </tr>
                                    <?php

                                    foreach($orders as $order){
                                        $totalOrderPrice += $order['priceTotal'];

                                        $decimalNumber = getMaxDecimalAmount($order['amount']);
                                    ?>
                                    <tr>
                                        <td class="whiteBackground"><?php echo $order['articleNumber'];?></td>
                                        <td class="whiteBackground"><?php echo $order['articleName'];?></td>
                                        <td class="whiteBackground"><?php echo number_format($order['amount'], $decimalNumber, ",", " ");?></td>
                                        <td class="rightAligned whiteBackground"><?php echo number_format($order['pricePerPiece'], 2, ",", " ");?></td>
                                        <td class="whiteBackground"><?php echo number_format($order['discountPercent'], 2, ",", " ");?></td>
                                        <td class="rightAligned whiteBackground"><?php echo number_format($order['priceTotal'], 2, ",", " ");?></td>
                                    </tr>
                                    <?php } ?>
                                </table>
                                <div class="approvedForBatchInvoicingWrapper">
                                    <input type="checkbox" name="approvedForBatchInvoicing" data-projectid="<?php echo $collectingOrder['id'];?>" id="approvedForBatchInvoicing<?php echo $collectingOrder['id'];?>" class="approvedForBatchInvoicing" <?php if($collectingOrder['approvedForBatchinvoicing']) echo 'checked';?>/>
                                    <label for="approvedForBatchInvoicing<?php echo $collectingOrder['id'];?>"></label>
                                    <label class="labelText" for="approvedForBatchInvoicing<?php echo $collectingOrder['id'];?>"><?php echo $formText_ApprovedForBatchInvoicing_output;?></label>
                                </div>
                                <div class="seperatedInvoiceWrapper">
                                    <input type="checkbox" name="seperatedInvoice" data-projectid="<?php echo $collectingOrder['id'];?>" id="seperatedInvoice<?php echo $collectingOrder['id'];?>" class="seperatedInvoice" <?php if($collectingOrder['seperatedInvoice']) echo 'checked';?>/>
                                    <label for="seperatedInvoice<?php echo $collectingOrder['id'];?>"></label>
                                    <label class="labelText" for="seperatedInvoice<?php echo $collectingOrder['id'];?>"><?php echo $formText_SeperatedInvoice_output;?></label>
                                </div>

                                <div class="createOrderConfirmation" data-projectid="<?php echo $collectingOrder['id'];?>">
                                    <?php echo $formText_CreateOrderConfirmation_output;?>
                                </div>
                                <div class="totalRow"><span><?php echo $formText_Total_output;?>:</span> <?php echo number_format($totalOrderPrice, 2, ",", " ");?></div>
                                <div class="editEntryBtn  createInvoice" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"><?php echo $formText_CreateInvoice_output;?></div>
								<?php
								if($variables->developeraccess > 5) {?>
	                                <div class="editEntryBtn createInvoiceDummy" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"><?php echo $formText_ConvertToInvoiceAlreadyInvoiced_output;?></div>
								<?php } ?>
                                <div class="clear"></div>

                                <div class="offer_pdfs">

                                    <?php
                                    $s_sql = "SELECT * FROM customer_collectingorder_confirmations WHERE customer_collectingorder_confirmations.customer_collectingorder_id = ? AND file is not null AND file <> ''
                                    ORDER BY customer_collectingorder_confirmations.id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
                                    $offer_pdfs = ($o_query ? $o_query->result_array() : array());
                                    $offerCounter = 1;
                                    foreach($offer_pdfs as $offer_pdf) {
                                        $offerClass="";
                                        if($offerCounter > 1){
                                            $offerClass = "oldOffers";
                                        }
                                        if($offerCounter == 2){
                                            ?>
                                            <div class="showOldOffers"><?php echo $formText_ShowPreviousOfferPdfs_output;?> (<?php echo count($offer_pdfs) - 1;?>)</div>
                                            <?php
                                        }
                                        ?>
                                            <div class="<?php echo $offerClass;?>">
                                                <a target="_blank" href="../<?php echo $offer_pdf['file']; ?>?caID=<?php echo $_GET['caID']?>&table=customer_collectingorder_confirmations&field=file&ID=<?php echo  $offer_pdf['id']; ?>&time=<?php echo time();?>"><?php echo basename($offer_pdf['file'])." - ".date("d.m.Y", strtotime($offer_pdf['created']));?></a>

                                                <?php if($moduleAccesslevel > 110) { ?>
                                                    <button class="output-btn small output-delete-orderconfirmation editBtnIcon"  data-offerpdf-id="<?php echo $offer_pdf['id']?>">
                                                        <span class="glyphicon glyphicon-trash"></span>
                                                    </button>
                                                <?php } ?>

                                                <button class="output-btn small output-send-orderconfirmationpdf editBtnIcon"  data-offerpdf-id="<?php echo $offer_pdf['id']?>">
                                                    <?php echo $formText_SendEmail_output;?>
                                                </button>
                                                <?php

                                                $s_sql = "select * from sys_emailsend WHERE content_table = 'customer_collectingorder_confirmations' AND content_id = ? ORDER BY send_on DESC LIMIT 1";
                                                $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                $lastSents = $o_query ? $o_query->result_array() : array();
                                                if(count($lastSents) > 0) {
                                                    foreach($lastSents as $lastSent) {
                                                        $s_sql = "select * from sys_emailsendto WHERE emailsend_id = ?";
                                                        $o_query = $o_main->db->query($s_sql, array($lastSent['id']));
                                                        $lastSentTos = $o_query ? $o_query->result_array() : array();

                                                        if(count($lastSentTos)>0){
                                                            foreach($lastSentTos as $lastSentTo) {
                                                                echo "<br/>".$formText_LastTimeSent_output.": ".date("d.m.Y H:i:s", strtotime($lastSentTo['perform_time']))." - ".$lastSentTo['receiver_email'];
                                                            }
                                                        }
                                                    }
                                                }
                                                $s_sql = "select * from file_links WHERE content_table = 'customer_collectingorder_confirmations' AND content_id = ?";
                                                $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                $file_link = $o_query ? $o_query->row_array() : array();

                                                $s_sql = "select * from file_links WHERE content_table = 'customer_collectingorder_confirmations' AND content_id = ?";
                                                $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                $file_link = $o_query ? $o_query->row_array() : array();

                                                $s_sql = "select * from file_links_log where key_used = ? AND successful = 1 ORDER BY created DESC";
                                                $o_query = $o_main->db->query($s_sql, array($file_link['link_key']));
                                                $last_successfull_open = $o_query ? $o_query->row_array() : array();
                                                if($last_successfull_open) {
                                                    echo "<br/>".$formText_LastTimeOpened.": ".date("d.m.Y H:i:s", strtotime($last_successfull_open['created']));
                                                }
                                                ?>
                                            </div>
                                        <?php
                                        $offerCounter++;
                                    }
                                    ?>
                                </div>
                                <div class="clear"></div>
                                <?php /*
                                <div class="filesAttachedToInvoice">
                                    <div class="attachFiles" data-collectingorder-id="<?php echo $collectingOrder['id'];?>"><?php echo $formText_AttachFilesForInvoice_output;?></div>
                                    <div class="attachedFiles">
                                        <table class="table table-borderless">
                                        <?php
                                        $attachedFiles = json_decode($collectingOrder['files_attached_to_invoice']);

                                        foreach($attachedFiles as $file){
                                            $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=customer_collectingorder&field=files_attached_to_invoice&ID='.$collectingOrder['id'];

                                            ?>
                                                <tr>
                                                    <td style="padding: 0;" width="90%"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></td>
                                                    <td style="padding: 0 10px;" width="10%" style="text-align: right;">
                                                        <?php if($moduleAccesslevel > 110 ) { ?>
                                                        <button class="output-btn small output-delete-attachedfile editBtnIcon" data-collectingorder-id="<?php echo $collectingOrder['id']; ?>" data-uid="<?php echo $file[4];?>">
                                                            <span class="glyphicon glyphicon-trash"></span>
                                                        <?php } ?>

                                                    </td>
                                                </tr>
                                            <?php
                                        }
                                        ?>
                                        </table>
                                    </div>
                                </div>*/?>
                                <div class="orderButtons">
                                </div>
                            </div>
                            <?php
                        }
                    }

                    function outputProject($projectInvoicing, $projectStatus){
                        global $o_main;
                        global $formText_ProjectLeader_output;
                        global $formText_ProjectName_output;
                        global $formText_UninvoicedOrders_output;
                        global $formText_InvoicedOrders_output;
                        global $customerData;
                        if($projectInvoicing){
                            $s_sql = "SELECT * FROM project_categories  WHERE project_categories.type = 3";
                            $o_query = $o_main->db->query($s_sql);
                            $projectCategories = ($o_query ? $o_query->result_array() : array());
                        } else {
                            $s_sql = "SELECT * FROM project_categories  WHERE project_categories.type <> 3";
                            $o_query = $o_main->db->query($s_sql);
                            $projectCategories = ($o_query ? $o_query->result_array() : array());
                        }
                        if($projectInvoicing){
                            $sql_where = " AND project.projectType = 3";
                        } else {
                            $sql_where = " AND project.projectType <> 3";
                        }
                        if($projectStatus == "active") {
                            $sql_where .= " AND (project.status = 0 OR project.status is null)";
                        } else if($projectStatus == "canceled"){
                            $sql_where .= " AND project.status = 6 ";
                        } else if($projectStatus == "finished"){
                            $sql_where .= " AND project.status = 1 AND (project.invoiceResponsibleStatus = 0 or project.invoiceResponsibleStatus is null)";
                        } else if($projectStatus == "finishedInvoiced"){
                            $sql_where .= " AND project.status = 1 AND project.invoiceResponsibleStatus = 1 ";
                        }
                        foreach($projectCategories as $projectCategory){
                            $s_sql = "SELECT project.* FROM project WHERE
                            project.customerId = ? ".$sql_where." AND project.project_category = ?
							GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id'], $projectCategory['id']));
                            $projects = ($o_query ? $o_query->result_array() : array());

                            if(count($projects) > 0){
                                ?>
                                <!-- <table class="table table-bordered">
                                    <tr>
                                        <th><?php echo $formText_ProjectLeader_output;?></th>
                                        <th><?php echo $formText_ProjectName_output;?></th>
                                        <th><?php echo $formText_UninvoicedOrders_output;?></th>
                                        <th><?php echo $formText_InvoicedOrders_output;?></th>
                                    </tr> -->
                                    <div class="projectCategoryTitle"><?php echo $projectCategory['name'];?> (<?php echo count($projects);?>)</div>
                                    <?php
                                    foreach($projects as $project){

                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['projectLeader']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT * FROM orders  LEFT JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
                                        WHERE customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ? AND customer_collectingorder.invoiceNumber > 0";
                                        $o_query = $o_main->db->query($s_sql, array($project['id']));
                                        $invoicedOrdersCount = ($o_query ? $o_query->num_rows() : 0);

                                        $s_sql = "SELECT * FROM orders LEFT JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
                                        WHERE customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)";
                                        $o_query = $o_main->db->query($s_sql, array($project['id']));
                                        $uninvoicedOrdersCount = ($o_query ? $o_query->num_rows() : 0);

                                        $s_sql = "SELECT SUM(orders.priceTotal) as total FROM orders LEFT JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
                                        WHERE customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ? AND customer_collectingorder.invoiceNumber > 0";
                                        $o_query = $o_main->db->query($s_sql, array($project['id']));
                                        $invoicedOrdersTotalSum = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT SUM(orders.priceTotal) as total FROM orders  LEFT JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
                                        WHERE customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)";
                                        $o_query = $o_main->db->query($s_sql, array($project['id']));
                                        $uninvoicedOrdersTotalSum = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=ProjectNew&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];

                                        ?>

                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <span class="projectTitleColumn">
                                                    <a href="<?php echo $project_link;?>" target="_blank" class="optimize"><?php echo $project['name']; ?></a>
                                                </span>
                                                <span class="projectTitleColumn leaderColumn">
                                                    <?php echo $projectLeader['name']; ?>
                                                </span>
                                                <?php if(!$withoutInvoice) { ?>
                                                    <span class="projectTitleColumn infoColumn">
                                                        <label><?php echo $formText_UninvoicedOrders_output;?></label>: <?php echo number_format($uninvoicedOrdersTotalSum['total'], 2, ",", "");?> <?php if($uninvoicedOrdersCount > 0) echo "(".$uninvoicedOrdersCount.")";?><br/>

                                                        <label><?php echo $formText_InvoicedOrders_output;?></label>: <?php echo number_format($invoicedOrdersTotalSum['total'], 2, ",", "");?> <?php if($invoicedOrdersCount > 0) echo "(".$invoicedOrdersCount.")";?>
                                                    </span>
                                                <?php } ?>
                                                <?php if(count($projectsForCollectingOrders) > 0) { ?>
                                                    <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                                                <?php } ?>
                                            </div>
                                            <div class="projectOrders">
                                                <?php
                                                foreach($projectsForCollectingOrders as $projectsForCollectingOrder) {
                                                    $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
                                                    LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                                                    WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ? GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
                                                    $o_query = $o_main->db->query($s_sql, array($customerData['id'], $projectsForCollectingOrder['id']));
                                                    $projectCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                                    writeCollectingOrders($projectCollectingOrders);
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                <!-- </table> -->
                                <?php
                                ?>
                            <?php }
                        }
                    }
					if(!$accessElementRestrict_SendEmails) {?>
                        <?php if($customer_basisconfig['activateSendEmails']) { ?>
                            <div class="p_contentBlock">
                                <div class="p_contentBlockTitle dropdown_content_show show_sendemails"><?php echo $formText_SentEmails_Output;?> <?php if($moduleAccesslevel > 10) { ?><button id="output-send-email" class="addEntryBtn"><?php echo $formText_Add_output;?></button><?php } ?>
                                <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div></div>
                                <div class="p_contentBlockContent dropdown_content">
                                <?php
                                $customerModuleID = $moduleID;

                                $s_sql = "SELECT * FROM emailtemplate_basic WHERE contentModuleId = ? AND contentId = ?";
                                $o_query = $o_main->db->query($s_sql, array($customerModuleID, $customerData['id']));
                                if($o_query && $o_query->num_rows() > 0){
                                    $v_comments = $o_query->result_array();
                                }
                                foreach($v_comments as $v_comment)
                                {
                                    $emailSent = false;
                                    $s_sql = "SELECT * FROM sys_emailsendto LEFT OUTER JOIN sys_emailsend ON sys_emailsend.id = sys_emailsendto.emailsend_id WHERE sys_emailsendto.status = 1 AND sys_emailsend.content_table='emailtemplate_basic' AND sys_emailsend.content_id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($v_comment['id']));
                                    if($o_query && $o_query->num_rows() > 0){
                                        $sentEmail = $o_query->row_array();
                                    }
                                    if($sentEmail){
                                        $emailSent = true;
                                    }
                                    ?><div class="output-comment">
                                        <div>
                                            <span class="createdBy">
                                                <?php echo $formText_From_output;?>: <?php echo $v_comment['createdBy'];  ?><br/>
                                            </span>
                                            <span class="createdBy">
                                                <?php echo $formText_To_output;?>: <?php echo $v_comment['receiverEmail'];  ?><br/>
                                            </span>
                                            <?php if($sentEmail) { ?>
                                                <span class="sentTime">
                                                    <?php echo $formText_SentDate_output;?>: <?php echo date("d.m.Y H:i", strtotime($sentEmail['send_on']));  ?><br/>
                                                </span>
                                            <?php } else { ?>
                                                <span class="draft"><?php echo $formText_Draft_output;?></span>
                                            <?php }?>
                                            <?php if(!$emailSent) { ?>
                                                <?php if($moduleAccesslevel > 10) { ?>
                                                <button class="output-btn small output-send-email editBtnIcon" data-cid="<?php echo $v_comment['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button>
                                                <?php } ?>
                                                <?php if($moduleAccesslevel > 110) { ?>
                                                <button class="output-btn small output-delete-item editBtnIcon" data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendEmail&cid=".$v_comment['id'];?>" data-delete-msg="<?php echo $formText_DeleteEmail_Output;?>?"><span class="glyphicon glyphicon-trash"></span></button>
                                                <?php } ?>
                                                <?php if($moduleAccesslevel > 10) { ?>
                                                <button class="output-btn small output-confirm-send-email editBtnBlank" data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendEmail&cid=".$v_comment['id'];?>" data-send-msg="<?php echo $formText_SendEmail_Output;?>?"><?php echo $formText_Send_Output;?></button>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                        <div class="commentText">
                                            <b><?php echo $formText_Subject_output;?>:</b> <?php echo $v_comment['subject'];  ?><br/>
                                            <b><?php echo $formText_Message_output;?>:</b><br/>
                                            <?php echo nl2br($v_comment['topText']);?>
                                    </div>
                                    </div><?php
                                }
                                ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>


					<div class="p_contentBlock">
						<?php
						$s_sql = "SELECT * FROM customerhistoryextsystem WHERE customer_id = ? ORDER BY created ASC";
						$o_query = $o_main->db->query($s_sql, array($cid));
						$historical_count = ($o_query ? $o_query->num_rows():0);

						$s_sql = "SELECT customerhistoryextsystemcategory.* FROM customerhistoryextsystem LEFT JOIN customerhistoryextsystemcategory ON customerhistoryextsystemcategory.id = customerhistoryextsystem.history_category_id  WHERE customer_id = ? GROUP BY history_category_id ORDER BY created ASC";
						$o_query = $o_main->db->query($s_sql, array($cid));
						$history_categories = ($o_query ? $o_query->result_array():array());

						?>
                        <div class="p_contentBlockTitle dropdown_content_show " data-customer-id="<?php echo $cid; ?>" data-blockid="18">
                            <?php echo $formText_HistoricalExternalData_Output;?>
                            <span class="badge">
                                <?php echo $historical_count; ?>
                            </span>

                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandTasks']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                        </div>
                        <div class="p_contentBlockContent tasks_content dropdown_content " <?php if($customer_basisconfig['expandTasks']){ ?> style="display: block;" <?php } ?>>

							<?php
							$maxFieldNumber = 10;
							 foreach($history_categories as $history_category) {
							 	$historyShown = 0;
								$moreFields = 0;
								?>
								<div class="history_cat_title"><b><?php echo $formText_Category_output." ".$history_category['name'];?></b></div>
								<table class="table">
									<tr>
										<?php
										for($x=1; $x<= $maxFieldNumber; $x++){
											if($history_category['field_'.$x.'_label'] != ""){
												if($historyShown < 5) {
												   	?>
												   	<th><?php echo $history_category['field_'.$x.'_label'];?></th>
													<?php
													$historyShown++;
												} else {
													$moreFields++;
												}
											}
									   	}
										?>
										<?php
											if($moreFields > 0){
												if($moreFields > 1){ ?>
												<th><?php echo "+".$moreFields." ".$formText_MoreFields_output;?></th>
											<?php } else { ?>
												<th><?php echo $history_category['field_'.$historyShown.'_label'];?></th>
											<?php } ?>
										<?php } ?>
									</tr>
								<?php
								$s_sql = "SELECT * FROM customerhistoryextsystem WHERE customer_id = ? AND history_category_id = ? ORDER BY created ASC";
								$o_query = $o_main->db->query($s_sql, array($cid, $history_category['id']));
								$customerhistoryextsystems = ($o_query ? $o_query->result_array():array());
								foreach($customerhistoryextsystems as $customerhistoryextsystem) {
									$historyShown = 0;
									?>
									<tr class="history_view_more" data-id="<?php echo $customerhistoryextsystem['id']?>">
										<?php
										for($x=1; $x<= $maxFieldNumber; $x++){
											if($history_category['field_'.$x.'_label'] != ""){
												if($historyShown < 5) {
												   	?>
												   	<td><?php echo $customerhistoryextsystem['field_'.$x];?>&nbsp;</td>
													<?php
													$historyShown++;
												}
											}
									   	}
										?>
										<?php
											if($moreFields > 0){
												if($moreFields > 1){ ?>
												<td>&nbsp;</td>
											<?php } else { ?>
												<td><?php echo $customerhistoryextsystem['field_'.$historyShown.'_label'];?></td>
											<?php } ?>
										<?php } ?>
									</tr>
									<?php
								}
								?>
								</table>
							<?php } ?>
                        </div>
                    </div>
					<?php if($v_customer_accountconfig['activate_member_profile_link']) { ?>
						<div class="p_contentBlock">
	                        <div class="p_contentBlockTitle dropdown_content_show " data-customer-id="<?php echo $cid; ?>" data-blockid="18">
	                            <?php echo $formText_SendMemberProfileLink_Output;?>

	                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandTasks']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
	                        </div>
	                        <div class="p_contentBlockContent tasks_content dropdown_content ">
								<?php
								$getComp = $o_main->db->query("SELECT customer.* FROM customer
		                        LEFT JOIN
		                        	(SELECT subscriptionmulti.startDate, subscriptionmulti.id, subscriptionmulti.customerId, MIN(subscriptionmulti.stoppedDate) AS stoppedDate, subscriptionmulti.extraCheckbox FROM subscriptionmulti
		                        		WHERE subscriptionmulti.customerId <> 0 GROUP by subscriptionmulti.customerId) subscriptionmulti
		                        	ON subscriptionmulti.customerId = customer.id
		                        WHERE customer.content_status <> '2'
		                        AND (subscriptionmulti.extraCheckbox = 0 OR subscriptionmulti.extraCheckbox is null)
		                        AND ((subscriptionmulti.startDate is not null AND subscriptionmulti.startDate <> '0000-00-00')
		                        AND (subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00' OR subscriptionmulti.stoppedDate > NOW()))
								AND customer.id = ?
		                        ORDER BY name", array($cid));
		                        $member = $getComp ? $getComp->row_array() : array();
								// var_dump($member);
								if($member){
									$getComp = $o_main->db->query("SELECT * FROM contactperson WHERE customerId = ?", array($member['id']));
		                            $contacts = $getComp ? $getComp->result_array() : array();
		                            $main_contact = array();
		                            $contactNumber = 0;
		                            foreach($contacts as $contact) {
		                                if($contact['mainContact']) {
		                                    $main_contact = $contact;
		                                } else {
		                                    $contactNumber++;
		                                }
		                            }

		                            $linkLastOpen = "";
		                            $getComp = $o_main->db->query("SELECT * FROM customer_member_link_tracking WHERE code = ? ORDER BY created DESC", array($member['member_profile_link_code']));
		                            $tracking = $getComp ? $getComp->row_array() : array();
		                            if($tracking){
		                                $linkLastOpen = $tracking['created'];
		                            }
		                            $getComp = $o_main->db->query("SELECT sys_emailsend.*, sys_emailsendto.* FROM sys_emailsend
		                                LEFT OUTER JOIN sys_emailsendto ON sys_emailsendto.emailsend_id = sys_emailsend.id
		                                WHERE sys_emailsend.content_table = 'customer_member_link' AND sys_emailsend.content_id = ?
		                                ORDER BY sys_emailsendto.perform_time DESC", array($member['id']));
		                            $emails = $getComp ? $getComp->result_array() : array();
		                            $linkLastSent = "";
		                            foreach($emails as $email) {
		                                if($linkLastSent == ""){
		                                    $linkLastSent = $email['perform_time'];
		                                }
		                            }
									?>
									<table class="table table-fixed">
				                        <tr>
				                            <th><?php echo $formText_Member_output;?></th>
				                            <th width="200"><?php echo $formText_MainContact_output;?></th>
				                            <th width="150"><?php echo $formText_LinkLastOpen_output;?></th>
				                            <th width="150"><?php echo $formText_LinkLastSent_output;?></th>
				                            <th width="80"></th>
				                        </tr>
										<tr>
			                                <td><?php echo $member['name']." ".$member['middlename']." ".$member['lastname'];?></td>
			                                <td>
			                                    <div class="editMainContact" data-customer-id="<?php echo $member['id'];?>">
			                                        <?php
			                                        if($main_contact){
			                                            echo $main_contact['name']." ".$main_contact['middle_name']." ".$main_contact['last_name'];
			                                        } else {
			                                            echo $formText_NoMainContact_output;
			                                        }

			                                        ?> (+<?php echo $contactNumber;?>)
			                                    </div>
			                                </td>
			                                <td><?php if($linkLastOpen != "") echo date("d.m.Y", strtotime($linkLastOpen));?></td>
			                                <td><?php if($linkLastSent != ""){
			                                    echo date("d.m.Y", strtotime($linkLastSent));
			                                    ?>
			                                    <span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">
			                        				<div class="container-fluid">
			                        				<div class="row">
			                        					<div class="col-xs-5"><strong><?php echo $formText_Date_Output;?></strong></div>
			                        					<div class="col-xs-5"><strong><?php echo $formText_SentTo_Output;?></strong></div>
			                        				</div>
			                        				<?php
			                    					foreach($emails as $v_log)
			                    					{
			                    						?>
			                    						<div class="row">
			                    							<div class="col-xs-5"><?php echo date("d.m.Y H:i", strtotime($v_log['perform_time']));?></div>
			                    							<div class="col-xs-5"><?php echo $v_log['receiver_email'];?></div>
			                    						</div>
			                    						<?php
			                    					}
			                        				?>
			                        			</div>
			                        			</div></span>
			                                    <?php
			                                }?></td>
			                                <td><span class="send_link" data-customer-id="<?php echo $member['id'];?>"><?php echo $formText_SendLink_output;?></span></td>
			                            </tr>
									</table>
								<?php } else {
									echo $formText_NotMember_output;
								}?>
							</div>
						</div>
					<?php } ?>
					<?php
					$customerId = $_POST['customerId'] ? $_POST['customerId'] : '';
					$s_sql = "SELECT * FROM getynet_event_client_accountconfig";
					$o_query = $o_main->db->query($s_sql);
					$getynet_event_client_accountconfig = ($o_query ? $o_query->row_array():array());

					$s_sql = "SELECT * FROM customer_activity_types WHERE content_status < 2 ORDER BY name ASC";
					$o_query = $o_main->db->query($s_sql);
					$customer_activity_types = ($o_query ? $o_query->result_array():array());
					$showProspects = false;
					$showParticipants = false;
					$showTasks = false;

					$session = $variables->fw_session;
					$menuaccess_fw = json_decode($session['cache_menu'], true);
					if($menuaccess_fw['ProspectOverview4'][3] == 'C'){
						$showProspects = true;
					}
					if($menuaccess_fw['GetynetEventClient'][3] == 'C'){
						$showParticipants = true;
					}
					if($menuaccess_fw['CaseCrm'][3] == 'C'){
						$showTasks = true;
					}

					$s_sql = "SELECT id FROM customer_comments WHERE customer_id = ?";
    				$o_query_count = $o_main->db->query($s_sql, array($cid));
					$l_count_comments = $o_query_count ? $o_query_count->num_rows() : 0;
					?>
					<div class="p_contentBlock">
						<div class="p_contentBlockTitle dropdown_content_show " data-customer-id="<?php echo $cid; ?>" data-blockid="19">
							<?php echo $formText_Activities_Output;?>
							<div class="activityType <?php if($_GET['activity'] == "comments") echo 'active';?>" data-type-id="comments"><?php echo $formText_Comments_output.' ('.$l_count_comments.')';?></div>
							<?php if($showTasks) { ?>
								<?php
								$v_count = array('active'=>0, 'completed'=>0);
								$s_sql = "SELECT status, COUNT(id) AS cnt FROM task_crm WHERE customerId = ? GROUP BY status";
								$o_query_count = $o_main->db->query($s_sql, array($cid));
								if($o_query_count && $o_query_count->num_rows()>0)
								foreach($o_query_count->result_array() as $v_row)
								{
									if(0 == intval($v_row['status'])) $v_count['active'] += $v_row['cnt'];
									if(1 == intval($v_row['status'])) $v_count['completed'] += $v_row['cnt'];
								}
								?>
								<div class="activityType <?php if($_GET['activity'] == "tasks") echo 'active';?>" data-type-id="tasks"><?php echo $formText_Tasks_output.' ('.$v_count['active'].'/'.$v_count['completed'].')';?></div>
							<?php } ?>
							<?php if($showProspects) { ?>
								<?php
								$v_count = array('active'=>0, 'closed'=>0);
								$s_sql = "SELECT closed, COUNT(id) AS cnt FROM prospect WHERE customerId = ? GROUP BY closed";
								$o_query_count = $o_main->db->query($s_sql, array($cid));
								if($o_query_count && $o_query_count->num_rows()>0)
								foreach($o_query_count->result_array() as $v_row)
								{
									if(0 == intval($v_row['status'])) $v_count['active'] += $v_row['cnt'];
									if(1 == intval($v_row['status'])) $v_count['closed'] += $v_row['cnt'];
								}
								?>
								<div class="activityType <?php if($_GET['activity'] == "pipelineProspects") echo 'active';?>" data-type-id="pipelineProspects"><?php echo $formText_PipelineProspect_output.' ('.$v_count['active'].'/'.$v_count['closed'].')';?></div>
							<?php } ?>
							<?php
							if($showParticipants) { ?>
								<div class="activityType <?php if($_GET['activity'] == "eventParticipants") echo 'active';?>" data-type-id="eventParticipants"><?php echo $formText_EventParticipants_output;?> <span class="output-event-participant-count"><img height="15px" src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span></div>
							<?php } ?>
							<?php if($moduleAccesslevel > 10) { ?>
								<div class="edit_activity_categories"><?php echo $formText_EditType_output;?></div>
							<?php } ?>
							<?php
							foreach($customer_activity_types as $customer_activity_type) {
								$s_sql = "SELECT id FROM customer_activity WHERE customer_id = ? AND activity_type_id = ?";
								$o_query_count = $o_main->db->query($s_sql, array($cid, $customer_activity_type['id']));
								$l_count_activity = $o_query_count ? $o_query_count->num_rows() : 0;
								?>
								<div class="activityType <?php if($_GET['activity'] == $customer_activity_type['id']) echo 'active';?>" data-type-id="<?php echo $customer_activity_type['id'];?>"><?php echo $customer_activity_type['name'].' ('.$l_count_activity.')';?></div>
								<?php
							}
							?>
							<div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandTasks']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
						</div>

						<div class="p_contentBlockContent customer_activity_content dropdown_content ">

						</div>
					</div>


					<?php



                    if(!$accessElementRestrict_Offer) {
                        if($showOffers) {
                            $offers_count = 0;

                            $s_sql = "SELECT offer.* FROM offer
                            LEFT OUTER JOIN customer ON customer.id = offer.customerId
                            WHERE customer.id is not null AND customer.id = ? AND offer.content_status = 0 GROUP BY offer.id ORDER BY offer.id DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $offers = ($o_query ? $o_query->result_array() : array());
                            $offers_count = count($offers);

                            $s_sql = "SELECT offer_attached_files.* FROM offer_attached_files ORDER BY id";
                            $o_query = $o_main->db->query($s_sql);
                            $offerFiles = ($o_query ? $o_query->row_array() : array());
                            $attachedFiles = json_decode($offerFiles['file'], true);
                            ?>
                            <div class="p_contentBlock">
                                <div class="p_contentBlockTitle show_offers dropdown_content_show" data-customer-id="<?php echo $cid; ?>"  data-blockid="91">
                                    <?php echo $formText_Offers_Output;?>
                                    <span class="badge">
                                        <?php echo $offers_count; ?>
                                    </span>
                                    <?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-offer" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_NewOffer_output;?></button><?php } ?>

                                    <div class="edit_files_attached_to_offers"><?php echo $formText_AddFilesAttachedToAllOffers_output;?>(<?php echo count($attachedFiles);?>)</div>
                                    <span class="editProspectDefault fas fa-cog"></span>
                                    <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandOffers']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                                    <div class="clear"></div>
                                </div>
                                <div class="p_contentBlockContent offers_content dropdown_content" <?php if($customer_basisconfig['expandOffers']){ ?> style="display: block;" <?php } ?> >

                                    <?php

                                    foreach($offers as $offer){
                                        $totalOrderPrice = 0;
                                        $s_sql = "SELECT * FROM offerline WHERE offerline.offer_id = ? AND offerline.content_status = 0 ORDER BY offerline.id ASC";
                                        $o_query = $o_main->db->query($s_sql, array($offer['id']));
                                        $orders = ($o_query ? $o_query->result_array() : array());

                                        $s_sql = "SELECT * FROM offers_project_connection WHERE offers_project_connection.offer_id = ? ORDER BY offers_project_connection.created ASC";
                            			$o_query = $o_main->db->query($s_sql, array($offer['id']));
                            			$connectedProjects = ($o_query ? $o_query->result_array() : array());
                                        ?>
                                        <div class="collectingOrder">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th colspan="6" >
                                                        <?php if($offer['date'] != "" && $offer['date'] != "0000-00-00" ) { ?>
                                                        <div style="float: left; margin-right: 10px;">
                                                                <?php echo $formText_OfferDate_output;?>: <span><?php echo date("d.m.Y", strtotime($offer['date']));?></span>
                                                        </div>
                                                        <?php } ?>
                                                        <div style="float: left; margin-right: 10px;">
                                                            <?php echo $formText_OfferId_output;?>: <span><?php echo $offer['id'];?></span>
                                                        </div>
                                                        <div style="float: left; margin-right: 10px;">
                                                            <?php echo $formText_OfferHeadline_Output;?>: <span><?php echo $offer['offer_headline'];?></span>
                                                        </div>
                                                        <div style="float: right;">
                                                        <?php if($moduleAccesslevel > 10) { ?>
                                                        <button class="output-btn small output-edit-offer editBtnIcon" data-project-id="<?php echo $offer['id']?>">
                                                            <span class="glyphicon glyphicon-pencil"></span>
                                                        </button>
                                                        <?php } ?>
                                                        <?php if($moduleAccesslevel > 110) { ?>
                                                        <button class="output-btn small output-delete-offer editBtnIcon"  data-project-id="<?php echo $offer['id']?>">
                                                            <span class="glyphicon glyphicon-trash"></span>
                                                        </button>
                                                        <?php } ?>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </th>
                                                </tr>


                                                <tr>
                                                    <td colspan="6">
                                                        <div style="float: left">
                                                            <?php if(count($connectedProjects) > 0) {
                                                                $connectedProjectIds = array();
                                                                foreach($connectedProjects as $connectedProject) {
                                                                    $connectedProjectIds[] = $connectedProject['project_id'];
                                                                }
                                                                echo $formText_CreatedProjects_output.": ";
                                                                echo implode(",", $connectedProjectIds);
                                                                ?>
                                                            <?php } ?>
                                                        </div>

                                                        <div style="float: right; width: 45%;">
                                                            <?php echo $formText_AttachFilesForEmail_output;?>
                                                            <div class="filesAttachedToEmail">
                                                                <div class="attachedFiles">
                                                                    <table style="width: 100%; table-layout: fixed;">
                                                                    <?php
                                                                    $attachedFiles = json_decode($offer['files_attached_to_email'], true);

                                                                    foreach($attachedFiles as $file){
                                                                        $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=offer&field=files_attached_to_email&ID='.$offer['id'];

                                                                        ?>
                                                                            <tr>
                                                                                <td style="padding: 0;" width="90%"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></td>
                                                                                <td style="padding: 0 10px;" width="10%" style="text-align: right;">
                                                                                    <?php if($moduleAccesslevel > 110) { ?>
                                                                                    <button class="output-btn small output-delete-attachedfile-offer editBtnIcon" data-offer-id="<?php echo $offer['id']; ?>" data-uid="<?php echo $file[4];?>">
                                                                                        <span class="glyphicon glyphicon-trash"></span>
                                                                                    <?php } ?>

                                                                                </td>
                                                                            </tr>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                    </table>
                                                                </div>
                                                                <div class="attachFilesToOffer" data-offer-id="<?php echo $offer['id'];?>">+ <?php echo $formText_AddFile_output;?></div>
                                                            </div>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><b><?php echo $formText_ArticleNr_output;?></b></th>
                                                    <th><b><?php echo $formText_ProductName_output;?></b></th>
                                                    <th><b><?php echo $formText_Quantity_output;?></b></th>
                                                    <th class="rightAligned"><b><?php echo $formText_PricePerPiece_output;?></b></th>
                                                    <th><b><?php echo $formText_Discount_output;?></b></th>
                                                    <th class="rightAligned"><b><?php echo $formText_PriceTotal_output;?></b></th>
                                                </tr>
                                                <?php foreach($orders as $order){
                                                    $totalOrderPrice += $order['priceTotal'];
                                                ?>
                                                <tr>
                                                    <td><?php echo $order['articleNumber'];?></td>
                                                    <td><?php echo $order['articleName'];?></td>
                                                    <td><?php echo number_format($order['amount'], 2, ",", " ");?></td>
                                                    <td class="rightAligned"><?php echo number_format($order['pricePerPiece'], 2, ",", " ");?></td>
                                                    <td><?php echo number_format($order['discountPercent'], 2, ",", " ");?></td>
                                                    <td class="rightAligned"><?php echo number_format($order['priceTotal'], 2, ",", " ");?></td>
                                                </tr>
                                                <?php } ?>
                                            </table>
                                            <div class="totalRow"><span><?php echo $formText_Total_output;?>:</span> <?php echo number_format($totalOrderPrice, 2, ",", " ");?></div>
                                            <?php if($customer_basisconfig['createOrderFromOffer'] == 1) { ?>
                                                <div class="editEntryBtn createProjectFromOffer" data-offer-id="<?php echo $offer['id'];?>"><?php echo $formText_CreateProject2_output;?></div>
                                            <?php } ?>
                                            <div class="offer_pdfs">
                                                <?php
                                                if($offer['prospectId'] > 0){
                                                    echo $formText_ProspectCreated_output."</br>";
                                                }
                                                $s_sql = "SELECT * FROM offer_pdf WHERE offer_pdf.offer_id = ? AND file is not null AND file <> '' ORDER BY offer_pdf.id DESC";
                                                $o_query = $o_main->db->query($s_sql, array($offer['id']));
                                                $offer_pdfs = ($o_query ? $o_query->result_array() : array());
                                                $offerCounter = 1;
                                                foreach($offer_pdfs as $offer_pdf) {
                                                    $offerClass="";
                                                    if($offerCounter > 1){
                                                        $offerClass = "oldOffers";
                                                    }
                                                    if($offerCounter == 2){
                                                        ?>
                                                        <div class="showOldOffers"><?php echo $formText_ShowPreviousOfferPdfs_output;?> (<?php echo count($offer_pdfs) - 1;?>)</div>
                                                        <?php
                                                    }
                                                    ?>
                                                        <div class="<?php echo $offerClass;?>">
                                                            <a target="_blank" href="../<?php echo $offer_pdf['file']; ?>?caID=<?php echo $_GET['caID']?>&table=offer_pdf&field=file&ID=<?php echo  $offer_pdf['id']; ?>&time=<?php echo time();?>"><?php echo basename($offer_pdf['file'])." - ".date("d.m.Y", strtotime($offer_pdf['created']));?></a>

                                                            <?php if($moduleAccesslevel > 110) { ?>
                                                                <button class="output-btn small output-delete-offerpdf editBtnIcon"  data-offerpdf-id="<?php echo $offer_pdf['id']?>">
                                                                    <span class="glyphicon glyphicon-trash"></span>
                                                                </button>
                                                            <?php } ?>

                                                            <button class="output-btn small output-send-offerpdf editBtnIcon"  data-offerpdf-id="<?php echo $offer_pdf['id']?>">
                                                                <?php echo $formText_SendEmail_output;?>
                                                            </button>
                                                            <?php

                                                            $s_sql = "select * from sys_emailsend WHERE content_table = 'offer_pdf' AND content_id = ? ORDER BY send_on DESC LIMIT 1";
                                                            $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                            $lastSents = $o_query ? $o_query->result_array() : array();
                                                            if(count($lastSents) > 0) {
                                                                foreach($lastSents as $lastSent) {
                                                                    $s_sql = "select * from sys_emailsendto WHERE emailsend_id = ?";
                                                                    $o_query = $o_main->db->query($s_sql, array($lastSent['id']));
                                                                    $lastSentTos = $o_query ? $o_query->result_array() : array();

                                                                    if(count($lastSentTos)>0){
                                                                        foreach($lastSentTos as $lastSentTo) {
                                                                            echo "<br/>".$formText_LastTimeSent_output.": ".date("d.m.Y H:i:s", strtotime($lastSentTo['perform_time']))." - ".$lastSentTo['receiver_email'];
                                                                        }
                                                                    }
                                                                }
                                                            }

                                                            $s_sql = "select * from file_links WHERE content_table = 'offer_pdf' AND content_id = ?";
                                                            $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                            $file_link = $o_query ? $o_query->row_array() : array();

                                                            $s_sql = "select * from file_links WHERE content_table = 'offer_pdf' AND content_id = ?";
                                                            $o_query = $o_main->db->query($s_sql, array($offer_pdf['id']));
                                                            $file_link = $o_query ? $o_query->row_array() : array();

                                                            $s_sql = "select * from file_links_log where key_used = ? AND successful = 1 ORDER BY created DESC";
                                                            $o_query = $o_main->db->query($s_sql, array($file_link['link_key']));
                                                            $last_successfull_open = $o_query ? $o_query->row_array() : array();
                                                            if($last_successfull_open) {
                                                                echo "<br/>".$formText_LastTimeOpened.": ".date("d.m.Y H:i:s", strtotime($last_successfull_open['created']));
                                                            }
                                                            ?>
                                                        </div>
                                                    <?php
                                                    $offerCounter++;
                                                }
                                                ?>
                                            </div>
                                            <div class="clear"></div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
        					</div>
                            <?php
                        }
                    }
					if(!$accessElementRestrict_Orders) {
                        if($showOrders) {
                            $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
                            LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                            WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)
                            AND customer_collectingorder.content_status = 0 AND (customer_collectingorder.projectId = 0 OR customer_collectingorder.projectId is null)
                             AND (customer_collectingorder.project2Id = 0 OR customer_collectingorder.project2Id is null)
                             GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $collectingOrders = ($o_query ? $o_query->result_array() : array());

                            $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
                            LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                            WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)
                            AND customer_collectingorder.content_status = 0 AND (customer_collectingorder.projectId = 0 OR customer_collectingorder.projectId is null)
                            AND customer_collectingorder.project2Id > 0
                            GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $projectUninvoicedOrderCount = ($o_query ? $o_query->num_rows() : 0);


                            $s_sql = "SELECT order_confirmation_attached_files.* FROM order_confirmation_attached_files ORDER BY id";
                            $o_query = $o_main->db->query($s_sql);
                            $offerFiles = ($o_query ? $o_query->row_array() : array());
                            $attachedFiles = json_decode($offerFiles['file'], true);
                            ?>
                            <div class="p_contentBlock highligtedBlock2">
                                <div class="p_contentBlockTitle dropdown_content_show show_orders"  data-blockid="9"><?php echo $formText_Order_Output;?> <span class="badge"><?php echo count($collectingOrders);?></span>
                                    <?php if(!isset($projectData['approvedForInvoicing'])) { ?><?php if($moduleAccesslevel > 10) { ?><button class="addEntryBtn small output-edit-collectingorder" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_NewOrder_output;?></button><?php } ?><?php } ?>

                                    <?php  if($projectUninvoicedOrderCount > 0){ echo '<span class="small" style="pointer-events: none;">'.$formText_UninvoicedOrdersInProjects_output." (".$projectUninvoicedOrderCount.")</span>"; }?>

                                    <div class="edit_files_attached_to_confirmation"><?php echo $formText_AddFilesAttachedToAllOrderConfirmation_output;?> <span class="">(<?php echo count($attachedFiles);?>)</span></div>
                                    <div class="showArrow"><span class="glyphicon <?php if(!$customer_basisconfig['collapseOrders']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>
                                <div class="p_contentBlockContent dropdown_content" <?php if(!$customer_basisconfig['collapseOrders']){ ?> style="display: block;" <?php } ?>>
                                    <?php if($projectUninvoicedOrderCount > 0){ ?>
                                        <div class="uninvoicedProjectBlock">
                                            <div class="subBlockTitle">
                                                <?php echo $formText_UninvoicedOrdersInProjects_output." (".$projectUninvoicedOrderCount.")";?>
                                            </div>
                                            <div class="subBlockContent">
                                                <?php
                                                $s_sql = "SELECT p.*, SUM(ol.priceTotal) as totalPrice, COUNT(DISTINCT(customer_collectingorder.id)) as orderCount FROM customer_collectingorder
                                                LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                                                LEFT OUTER JOIN project2 p ON p.id = customer_collectingorder.project2Id
                                                LEFT OUTER JOIN orders ol ON ol.collectingorderId = customer_collectingorder.id
                                                WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)
                                                AND customer_collectingorder.content_status = 0 AND (customer_collectingorder.projectId = 0 OR customer_collectingorder.projectId is null)
                                                AND customer_collectingorder.project2Id > 0
                                                GROUP BY p.id ORDER BY p.name DESC";
                                                $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                                $uninvoicedProjects = ($o_query ? $o_query->result_array() : array());
                                                foreach($uninvoicedProjects as $uninvoicedProject){
                                                    $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$uninvoicedProject['id'];

                                                    ?>
                                                    <div class="projectWrapper">
                                                        <div class="projectTitle">
                                                            <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                                <span class="projectTitleColumn">
                                                                    <?php echo $uninvoicedProject['name']." (".$uninvoicedProject['orderCount'].")"; ?></span><span class="projectTitleColumn leaderColumn">
                                                                        <?php echo number_format($uninvoicedProject['totalPrice'], 2, ",",""); ?></span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php
                                    }

                                    writeCollectingOrders($collectingOrders);
                                    ?>

                                    <?php

                                    $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
                                    LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                                    WHERE customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status > 0 GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                    $deletedCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                    if(count($deletedCollectingOrders) > 0) { ?>
                                        <div class="showDeletedCollectingOrders"><?php echo $formText_ShowDeletedOrders_output;?> (<?php echo count($deletedCollectingOrders)?>)</div>
                                        <div class="deletedCollectingOrders">
                                            <?php
                                            foreach($deletedCollectingOrders as $collectingOrder){
                                                $totalOrderPrice = 0;
                                                $s_sql = "SELECT * FROM orders WHERE orders.collectingorderId = ? AND orders.content_status = 0 ORDER BY orders.id ASC";
                                                $o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
                                                $orders = ($o_query ? $o_query->result_array() : array());

                                                $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                                                $o_query = $o_main->db->query($s_sql, array($collectingOrder['contactpersonId']));
                                                $collectingOrderContactPerson = $o_query ? $o_query->row_array() : array();
                                                ?>
                                                <div class="collectingOrder">
                                                    <table class="table table-bordered">
                                                        <tr>
                                                            <th colspan="6" >
                                                                <?php if($collectingOrder['date'] != "" && $collectingOrder['date'] != "0000-00-00" ) { ?>
                                                                <div style="float: left; margin-right: 10px;">
                                                                        <?php echo $formText_OrderDate_output;?>: <span><?php echo date("d.m.Y", strtotime($collectingOrder['date']));?></span>
                                                                </div>
                                                                <?php } ?>
                                                                <div style="float: left; margin-right: 10px;">
                                                                    <?php echo $formText_OrderId_output;?>: <span><?php echo $collectingOrder['id'];?></span>

                                                                </div>
                                                                <?php if($collectingOrderContactPerson != "") { ?>
                                                                <div style="float: left">
                                                                    <?php echo $formText_ContactPerson_output;?>: <span><?php echo $collectingOrderContactPerson['name'];?></span>
                                                                </div>
                                                                <?php } ?>
                                                                <div style="float: right;">
                                                                <?php if(!$collectingOrder['approvedForInvoicing']) { ?>
                                                                    <?php if($moduleAccesslevel > 110) { ?>
                                                                    <button class="output-btn small output-delete-collectingorder-real editBtnIcon"  data-project-id="<?php echo $collectingOrder['id']?>">
                                                                        <span class="glyphicon glyphicon-trash"></span>
                                                                    </button>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                                </div>
                                                                <div class="clear"></div>
                                                            </th>
                                                        </tr>
                                                        <tr>
                                                            <th><b><?php echo $formText_ArticleNr_output;?></b></th>
                                                            <th><b><?php echo $formText_ProductName_output;?></b></th>
                                                            <th class="rightAligned"><b><?php echo $formText_PricePerPiece_output;?></b></th>
                                                            <th><b><?php echo $formText_Quantity_output;?></b></th>
                                                            <th><b><?php echo $formText_Discount_output;?></b></th>
                                                            <th class="rightAligned"><b><?php echo $formText_PriceTotal_output;?></b></th>
                                                        </tr>
                                                        <?php foreach($orders as $order){
                                                            $totalOrderPrice += $order['priceTotal'];
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $order['articleNumber'];?></td>
                                                            <td><?php echo $order['articleName'];?></td>
                                                            <td class="rightAligned"><?php echo number_format($order['pricePerPiece'], 2, ",", "");?></td>
                                                            <td><?php echo number_format($order['amount'], 2, ",", "");?></td>
                                                            <td><?php echo number_format($order['discountPercent'], 2, ",", "");?></td>
                                                            <td class="rightAligned"><?php echo number_format($order['priceTotal'], 2, ",", "");?></td>
                                                        </tr>
                                                        <?php } ?>
                                                    </table>
                                                    <div class="totalRow"><span><?php echo $formText_Total_output;?>:</span> <?php echo number_format($totalOrderPrice, 2, ",", "");?></div>
                                                    <div class="clear"></div>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                       	<?php } ?>
                    <?php } ?>

                    <?php


                    if($showInvoiceAndInvoicedOrders) {
                        if(!$accessElementRestrict_Invoices){
                            ?>
        					<div class="p_contentBlock highligtedBlock2">
                                <?php

                                $s_sql = "SELECT * FROM invoice WHERE customerId = ? AND content_status < 2 GROUP BY ownercompany_id ORDER BY id DESC";
                                $o_query = $o_main->db->query($s_sql, array($cid));
                                $invoice_ownercompanies = $o_query ? $o_query->result_array(): array();

                                $s_sql = "SELECT * FROM invoice WHERE customerId = ? AND content_status < 2 ORDER BY id DESC";
                                $o_query = $o_main->db->query($s_sql, array($cid));
                                $rows = array();
                                if($o_query){
                                    $invoice_count = $o_query->num_rows();
                                }

                                $totalExclTax = array();
                                $s_sql = "SELECT SUM(invoice.totalExTax) as total, ownercompany_id FROM invoice WHERE customerId = ? AND content_status < 2 GROUP BY ownercompany_id ORDER BY id DESC";
                                $o_query = $o_main->db->query($s_sql, array($cid));
                                $rows = array();
                                if($o_query){
                                    $totalInvoice = $o_query->result_array();
                                    foreach($totalInvoice as $totalInvoiceSingle){
                                        $totalExclTax[$totalInvoiceSingle['ownercompany_id']] = $totalInvoiceSingle['total'];
                                    }
                                }
                                $totalExclTaxPrevYear = array();

                                $year = date("Y-m-d");
                                $lastYear = date("Y-m-d", strtotime('first day of January '.date('Y')));
                                $s_sql = "SELECT SUM(invoice.totalExTax) as total, ownercompany_id FROM invoice WHERE customerId = ? AND content_status < 2 AND invoiceDate >= '".$lastYear."' GROUP BY ownercompany_id ORDER BY id DESC";
                                $o_query = $o_main->db->query($s_sql, array($cid));
                                $rows = array();
                                if($o_query){
                                    $totalInvoice = $o_query->result_array();
                                    foreach($totalInvoice as $totalInvoiceSingle){
                                        $totalExclTaxPrevYear[$totalInvoiceSingle['ownercompany_id']] = $totalInvoiceSingle['total'];
                                    }
                                }

                                ?>

        						<div class="p_contentBlockTitle show_invoices show_dropdown" data-customer-id="<?php echo $cid; ?>"  data-blockid="10">
                                    <?php echo $formText_Invoices_Output;?>
                                    <span class="badge">
                                        <?php echo $invoice_count; ?>
                                    </span>
                                    <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandInvoices']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                                    <span class="statistics">
                                        <?php
                                        $ownercompanyAppendix = true;
                                        if(count($invoice_ownercompanies) > 1){
                                            $ownercompanyAppendix = true;
                                        }
                                        foreach($invoice_ownercompanies as $invoice_ownercompany){
                                            if($ownercompanyAppendix) {
                                                $s_sql = "SELECT * FROM ownercompany WHERE id = ?";
                                                $o_query = $o_main->db->query($s_sql, array($invoice_ownercompany['ownercompany_id']));
                                                $ownercompany_info = $o_query ? $o_query->row_array(): array();
                                            }
                                            ?>
                                            <div>
                                                <?php
                                                if($ownercompany_info) echo $ownercompany_info['name']." - ";
                                                echo $formText_TotalExclTax_output.": ".number_format($totalExclTax[$invoice_ownercompany['ownercompany_id']], 0, ',', ' ')." / ". $formText_TotalExclTaxCurrentYear_output.": ".number_format($totalExclTaxPrevYear[$invoice_ownercompany['ownercompany_id']], 0, ',', ' ');
                                                ?>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </span>
                                    <div class="clear"></div>
                                </div>
                                <div class="p_contentBlockContent invoices_content dropdown_content" <?php if($customer_basisconfig['expandInvoices']){ ?> style="display: block;" <?php } ?> >

                                </div>
        					</div>
                        <?php } ?>
                        <?php
                        if(!$accessElementRestrict_InvoicedOrders) {
                            /*?>
                            <div class="p_contentBlock">
                                <?php
                                $s_sql = "SELECT customer_collectingorder.* FROM customer_collectingorder
                                LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
                                WHERE customer.id is not null AND customer.id = ? AND customer_collectingorder.invoiceNumber > 0 GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";

                                $o_query = $o_main->db->query($s_sql, array($cid));
                                $rows = array();
                                if($o_query){
                                    $invoiced_orders_count = $o_query->num_rows();
                                }
                                ?>

                                <div class="p_contentBlockTitle show_ordered_invoices show_dropdown" data-customer-id="<?php echo $cid; ?>" data-blockid="11">
                                    <?php echo $formText_InvoicedOrders_Output;?>
                                    <span class="badge">
                                        <?php echo $invoiced_orders_count; ?>
                                    </span>
                                    <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandInvoicedOrders']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                                </div>
                                <div class="p_contentBlockContent ordered_invoices_content dropdown_content " <?php if($customer_basisconfig['expandInvoicedOrders']){ ?> style="display: block;" <?php } ?>>

                                </div>
                            </div>
                        <?php */} ?>
                    <?php } ?>

                    <?php
					if(!$accessElementRestrict_ProjectsWithInvoicing) {
                            if($customer_basisconfig['activateProjectsWithInvocing']) {

                                $s_sql = "SELECT project.* FROM project
                                LEFT OUTER JOIN customer ON customer.id = project.customerId
                                WHERE customer.id is not null AND customer.id = ? AND project.projectType = 3 AND (project.status = 0 OR project.status is null) GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                                $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                $activeProjectsWithInvoicingCount = ($o_query ? $o_query->num_rows() : 0);
                                $s_sql = "SELECT project.* FROM project
                                LEFT OUTER JOIN customer ON customer.id = project.customerId
                                WHERE customer.id is not null AND customer.id = ? AND project.projectType = 3 AND project.status = 6 GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                                $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                $canceledProjectsWithInvoicingCount = ($o_query ? $o_query->num_rows() : 0);

                                $s_sql = "SELECT project.* FROM project
                                LEFT OUTER JOIN customer ON customer.id = project.customerId
                                WHERE customer.id is not null AND customer.id = ? AND project.projectType = 3 AND project.status = 1 AND (project.invoiceResponsibleStatus = 0 or project.invoiceResponsibleStatus is null) GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                                $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                $finishedProjectsWithInvoicingCount = ($o_query ? $o_query->num_rows() : 0);

                                $s_sql = "SELECT project.* FROM project
                                LEFT OUTER JOIN customer ON customer.id = project.customerId
                                WHERE customer.id is not null AND customer.id = ? AND project.projectType = 3 AND project.status = 1 AND project.invoiceResponsibleStatus = 1 GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                                $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                                $finishedInvoicedProjectsWithInvoicingCount = ($o_query ? $o_query->num_rows() : 0);
                                ?>
                        	<div class="p_contentBlock highligtedBlock">
                                <div class="p_contentBlockTitle dropdown_content_show" data-blockid="7"><?php echo $formText_ProjectsWithInvoicingActive_Output;?> <span class="badge"><?php echo $activeProjectsWithInvoicingCount;?></span>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithInvoicing']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithInvoicing']){ ?> style="display: block;" <?php } ?>>
                                    <?php echo outputProject(true, "active"); ?>
                            	</div>
                            </div>
                            <div class="p_contentBlock highligtedBlock">
                                <div class="p_contentBlockTitle dropdown_content_show" data-blockid="71"><?php echo $formText_ProjectsWithInvoicingCanceled_Output;?> <span class="badge"><?php echo $canceledProjectsWithInvoicingCount;?></span>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithInvoicingCanceled']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithInvoicingCanceled']){ ?> style="display: block;" <?php } ?>>
                                    <?php echo outputProject(true, "canceled"); ?>
                            	</div>
                            </div>
                            <div class="p_contentBlock highligtedBlock">
                                <div class="p_contentBlockTitle dropdown_content_show" data-blockid="72"><?php echo $formText_ProjectsWithInvoicingFinished_Output;?> <span class="badge"><?php echo $finishedProjectsWithInvoicingCount;?></span>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithInvoicingFinished']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithInvoicingFinished']){ ?> style="display: block;" <?php } ?>>
                                    <?php echo outputProject(true, "finished"); ?>
                            	</div>
                            </div>
                            <div class="p_contentBlock highligtedBlock">
                                <div class="p_contentBlockTitle dropdown_content_show" data-blockid="73"><?php echo $formText_ProjectsWithInvoicingFnishedInvoiced_Output;?> <span class="badge"><?php echo $finishedInvoicedProjectsWithInvoicingCount;?></span>
                                <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithInvoicingFinishedInvoiced']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                                <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithInvoicingFinishedInvoiced']){ ?> style="display: block;" <?php } ?>>
                                    <?php echo outputProject(true, "finishedInvoiced"); ?>
                            	</div>
                            </div>

                    	<?php } ?>
                    <?php } ?>

                    <?php

                    if(!$accessElementRestrict_ProjectsWithoutInvoicing) {
                        if($customer_basisconfig['activateProjectsWithoutInvocing']) {
                     		$s_sql = "SELECT project.* FROM project
                            LEFT OUTER JOIN customer ON customer.id = project.customerId
                            WHERE customer.id is not null AND customer.id = ? AND project.projectType <> 3 AND (project.status = 0 OR project.status is null) GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $activeProjectsWithoutInvoicingCount = ($o_query ? $o_query->num_rows() : 0);

                     		$s_sql = "SELECT project.* FROM project
                            LEFT OUTER JOIN customer ON customer.id = project.customerId
                            WHERE customer.id is not null AND customer.id = ? AND project.projectType <> 3 AND project.status = 6 GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $canceledProjectsWithoutInvoicingCount = ($o_query ? $o_query->num_rows() : 0);

                     		$s_sql = "SELECT project.* FROM project
                            LEFT OUTER JOIN customer ON customer.id = project.customerId
                            WHERE customer.id is not null AND customer.id = ? AND project.projectType <> 3 AND project.status = 1 GROUP BY project.id ORDER BY project.status ASC, project.finishedDate DESC";
                            $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                            $finishedProjectsWithoutInvoicingCount = ($o_query ? $o_query->num_rows() : 0);
                             ?>

                         <div class="p_contentBlock highligtedBlock">
                             <div class="p_contentBlockTitle dropdown_content_show" data-blockid="8"><?php echo $formText_ProjectsWithoutInvoicingActive_Output;?> <span class="badge"><?php echo $activeProjectsWithoutInvoicingCount;?></span>
                             <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithoutInvoicing']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                             <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithoutInvoicing']){ ?> style="display: block;" <?php } ?>>
                                 <?php echo outputProject(false, "active"); ?>
                         	</div>
                         </div>
                         <div class="p_contentBlock highligtedBlock">
                             <div class="p_contentBlockTitle dropdown_content_show" data-blockid="81"><?php echo $formText_ProjectsWithoutInvoicingCanceled_Output;?> <span class="badge"><?php echo $canceledProjectsWithoutInvoicingCount;?></span>
                             <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithoutInvoicingCanceled']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                             <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithoutInvoicingCanceled']){ ?> style="display: block;" <?php } ?>>
                                 <?php echo outputProject(false, "canceled"); ?>
                         	</div>
                         </div>
                         <div class="p_contentBlock highligtedBlock">
                             <div class="p_contentBlockTitle dropdown_content_show" data-blockid="82"><?php echo $formText_ProjectsWithoutInvoicingFinished_Output;?> <span class="badge"><?php echo $finishedProjectsWithoutInvoicingCount;?></span>
                             <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProjectWithoutInvoicingFinished']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                             <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProjectWithoutInvoicingFinished']){ ?> style="display: block;" <?php } ?>>
                                 <?php echo outputProject(false, "finished"); ?>
                         	</div>
                         </div>

                    	<?php } ?>
                    <?php } ?>
                    <?php
                    if($customer_basisconfig['activateProjects2']) {
						$sql_subunit_sql = "";
						if($subunit_filter > 0) {
							$sql_subunit_sql = " AND project2.customer_subunit_id = '".$o_main->db->escape_str($subunit_filter)."'";
						}

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 0 OR project2.type is null) AND (project2.projectLeaderStatus = 0 OR project2.projectLeaderStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $oneTimeProjects = $o_query ? $o_query->result_array() : array();
                        $oneTimeProjectsCount = count($oneTimeProjects);


                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 0 OR project2.type is null) AND (project2.projectLeaderStatus = 2)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $oneTimeProjectsCanceled = $o_query ? $o_query->result_array() : array();
                        $oneTimeProjectsCanceledCount = count($oneTimeProjectsCanceled);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 0 OR project2.type is null) AND (project2.projectLeaderStatus = 1)  AND (project2.invoiceResponsibleStatus = 0 or project2.invoiceResponsibleStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $oneTimeProjectsFinished = $o_query ? $o_query->result_array() : array();
                        $oneTimeProjectsFinishedCount = count($oneTimeProjectsFinished);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 0 OR project2.type is null) AND project2.projectLeaderStatus = 1 AND (project2.invoiceResponsibleStatus = 1)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $oneTimeProjectsFinishedAndInvoiced = $o_query ? $o_query->result_array() : array();
                        $oneTimeProjectsFinishedAndInvoicedCount = count($oneTimeProjectsFinishedAndInvoiced);

                        ?>
                        <div class="p_contentBlock highligtedBlock">
                            <div class="p_contentBlockTitle dropdown_content_show" data-blockid="711"><?php echo $formText_OneTimeProjects_Output;?> <span class="badge"><?php echo $oneTimeProjectsCount;?></span>
                             <?php if($moduleAccesslevel > 10) { ?><button data-type="one_time" class="addEntryBtn output-add-project2_onetime"><?php echo $formText_Add_output;?></button><?php } ?>
                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProject2']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProject2']){ ?> style="display: block;" <?php } ?>>
                                <?php

                                foreach($oneTimeProjects as $project) {
                                    $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                    $projectLeader = ($o_query ? $o_query->row_array() : array());

                                    $s_sql = "SELECT project.* FROM customer_collectingorder
                                    LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                    WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                    AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                    GROUP BY project.id  ORDER BY project.id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                    $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                    $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                    ?>
                                    <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                        <div class="projectTitle">
                                            <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                <span class="projectTitleColumn">
                                                    <?php echo $project['name']; ?>
                                                </span>
                                                <span class="projectTitleColumn leaderColumn">
                                                    <?php echo $projectLeader['name']; ?>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="show_finished project_show_click"><?php echo $formText_ShowFinished_output;?>(<?php echo $oneTimeProjectsFinishedCount?>)</div>
                                <div class="finished_one_time project_show_item">
                                    <?php
                                    foreach($oneTimeProjectsFinished as $project) {

                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_canceled project_show_click"><?php echo $formText_ShowCanceled_output;?>(<?php echo $oneTimeProjectsCanceledCount?>)</div>
                                <div class="canceled_one_time project_show_item">
                                    <?php
                                    foreach($oneTimeProjectsCanceled as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_finished_and_invoiced project_show_click"><?php echo $formText_ShowFinishedAndInvoiced_output;?>(<?php echo $oneTimeProjectsFinishedAndInvoicedCount?>)</div>
                                <div class="finished_and_invoiced_one_time project_show_item">
                                    <?php
                                    foreach($oneTimeProjectsFinishedAndInvoiced as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 1) AND (project2.projectLeaderStatus = 0 OR project2.projectLeaderStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $continuingProjects = $o_query ? $o_query->result_array() : array();
                        $continuingProjectsCount = count($continuingProjects);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 1) AND (project2.projectLeaderStatus = 2)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $continuingProjectsCanceled = $o_query ? $o_query->result_array() : array();
                        $continuingProjectsCanceledCount = count($continuingProjectsCanceled);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 1) AND (project2.projectLeaderStatus = 1)  AND (project2.invoiceResponsibleStatus = 0 or project2.invoiceResponsibleStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $continuingProjectsFinished = $o_query ? $o_query->result_array() : array();
                        $continuingProjectsFinishedCount = count($continuingProjectsFinished);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 1) AND project2.projectLeaderStatus = 1 AND (project2.invoiceResponsibleStatus = 1)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $continuingProjectsFinishedAndInvoiced = $o_query ? $o_query->result_array() : array();
                        $continuingProjectsFinishedAndInvoicedCount = count($continuingProjectsFinishedAndInvoiced);
                        ?>
                        <div class="p_contentBlock highligtedBlock">
                            <div class="p_contentBlockTitle dropdown_content_show" data-blockid="712"><?php echo $formText_ContinuingProjects_Output;?> <span class="badge"><?php echo $continuingProjectsCount;?></span>
                             <?php if($moduleAccesslevel > 10) { ?><button data-type="continuing" class="output-add-project2 addEntryBtn"><?php echo $formText_Add_output;?></button><?php } ?>
                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProject2']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProject2']){ ?> style="display: block;" <?php } ?>>
                                <?php

                                foreach($continuingProjects as $project) {
                                    $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                    $projectLeader = ($o_query ? $o_query->row_array() : array());

                                    $s_sql = "SELECT project.* FROM customer_collectingorder
                                    LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                    WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                    AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                    GROUP BY project.id  ORDER BY project.id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                    $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                    $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                    ?>
                                    <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                        <div class="projectTitle">
                                            <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                <span class="projectTitleColumn">
                                                    <?php echo $project['name']; ?>
                                                </span>
                                                <span class="projectTitleColumn leaderColumn">
                                                    <?php echo $projectLeader['name']; ?>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="show_finished project_show_click"><?php echo $formText_ShowFinished_output;?>(<?php echo $continuingProjectsFinishedCount?>)</div>
                                <div class="finished_one_time project_show_item">
                                    <?php
                                    foreach($continuingProjectsFinished as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_canceled project_show_click"><?php echo $formText_ShowCanceled_output;?>(<?php echo $continuingProjectsCanceledCount?>)</div>
                                <div class="canceled_one_time project_show_item">
                                    <?php
                                    foreach($continuingProjectsCanceled as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_finished_and_invoiced project_show_click"><?php echo $formText_ShowFinishedAndInvoiced_output;?>(<?php echo $continuingProjectsFinishedAndInvoicedCount?>)</div>
                                <div class="finished_and_invoiced_one_time project_show_item">
                                    <?php
                                    foreach($continuingProjectsFinishedAndInvoiced as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 2) AND (project2.projectLeaderStatus = 0 OR project2.projectLeaderStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $periodicProjects = $o_query ? $o_query->result_array() : array();
                        $periodicProjectsCount = count($periodicProjects);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 2) AND (project2.projectLeaderStatus = 0 OR project2.projectLeaderStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $periodicProjects = $o_query ? $o_query->result_array() : array();
                        $periodicProjectsCount = count($periodicProjects);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 2) AND (project2.projectLeaderStatus = 2)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $periodicProjectsCanceled = $o_query ? $o_query->result_array() : array();
                        $periodicProjectsCanceledCount = count($periodicProjectsCanceled);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 2) AND (project2.projectLeaderStatus = 1)  AND (project2.invoiceResponsibleStatus = 0 or project2.invoiceResponsibleStatus is null)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $periodicProjectsFinished = $o_query ? $o_query->result_array() : array();
                        $periodicProjectsFinishedCount = count($periodicProjectsFinished);

                        $s_sql = "SELECT project2.* FROM project2
                        LEFT OUTER JOIN customer ON customer.id = project2.customerId
                        WHERE customer.id is not null AND customer.id = ? AND (project2.type = 1) AND project2.projectLeaderStatus = 1 AND (project2.invoiceResponsibleStatus = 1)
                        ".$sql_subunit_sql." GROUP BY project2.id ORDER BY project2.projectLeaderStatus ASC";
                        $o_query = $o_main->db->query($s_sql, array($customerData['id']));
                        $periodicProjectsFinishedAndInvoiced = $o_query ? $o_query->result_array() : array();
                        $periodicProjectsFinishedAndInvoicedCount = count($periodicProjectsFinishedAndInvoiced);
                        ?>
                        <div class="p_contentBlock highligtedBlock">
                            <div class="p_contentBlockTitle dropdown_content_show" data-blockid="713"><?php echo $formText_PeriodicProjects_Output;?> <span class="badge"><?php echo $periodicProjectsCount;?></span>
                             <?php if($moduleAccesslevel > 10) { ?><button data-type="periodic" class="addEntryBtn output-add-project2"><?php echo $formText_Add_output;?></button><?php } ?>
                            <div class="showArrow"><span class="glyphicon <?php if($customer_basisconfig['expandProject2']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>" ></span></div></div>

                            <div class="p_contentBlockContent dropdown_content" <?php if($customer_basisconfig['expandProject2']){ ?> style="display: block;" <?php } ?>>
                                <?php

                                foreach($periodicProjects as $project) {
                                    $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                    $projectLeader = ($o_query ? $o_query->row_array() : array());

                                    $s_sql = "SELECT project.* FROM customer_collectingorder
                                    LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                    WHERE project.id is not nus = 0 AND customer_collectingorder.projectId = ?
                                    AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                    GROUP BY project.id  ORDER BY project.id DESC";
                                    $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                    $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                    $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                    ?>
                                    <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                        <div class="projectTitle">
                                            <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                <span class="projectTitleColumn">
                                                    <?php echo $project['name']; ?>
                                                </span>
                                                <span class="projectTitleColumn leaderColumn">
                                                    <?php echo $projectLeader['name']; ?>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="show_finished project_show_click"><?php echo $formText_ShowFinished_output;?>(<?php echo $periodicProjectsFinishedCount?>)</div>
                                <div class="finished_one_time project_show_item">
                                    <?php
                                    foreach($periodicProjectsFinished as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_canceled project_show_click"><?php echo $formText_ShowCanceled_output;?>(<?php echo $periodicProjectsCanceledCount?>)</div>
                                <div class="canceled_one_time project_show_item">
                                    <?php
                                    foreach($periodicProjectsCanceled as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <div class="show_finished_and_invoiced project_show_click"><?php echo $formText_ShowFinishedAndInvoiced_output;?>(<?php echo $periodicProjectsFinishedAndInvoicedCount?>)</div>
                                <div class="finished_and_invoiced_one_time project_show_item">
                                    <?php
                                    foreach($periodicProjectsFinishedAndInvoiced as $project) {
                                        $s_sql = "SELECT * FROM contactperson  WHERE contactperson.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($project['employeeId']));
                                        $projectLeader = ($o_query ? $o_query->row_array() : array());

                                        $s_sql = "SELECT project.* FROM customer_collectingorder
                                        LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
                                        WHERE project.id is not null customer_collectingorder.customerId = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId = ?
                                        AND (project.status <> 1 OR (project.status = 1 AND project.invoiceResponsibleStatus <> 1))
                                        GROUP BY project.id  ORDER BY project.id DESC";
                                        $o_query = $o_main->db->query($s_sql, array($customerData['id'], $project['id']));
                                        $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());
                                        $project_link =  $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=details&view=project_leader&cid=".$project['id'];


                                        ?>
                                        <div class="projectWrapper <?php if(count($projectsForCollectingOrders) > 0) echo 'hasOrders';?>">
                                            <div class="projectTitle">
                                                <a href="<?php echo $project_link;?>" target="_blank" class="optimize">
                                                    <span class="projectTitleColumn">
                                                        <?php echo $project['name']; ?>
                                                    </span>
                                                    <span class="projectTitleColumn leaderColumn">
                                                        <?php echo $projectLeader['name']; ?>
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
					<?php if(!$accessElementRestrict_RepeatingOrders) {?>
                        <?php if($showRepeatingOrders) {
                            if(isset($_GET['openedSubscriptions'])){ $openedSubscriptionIds = explode(",", $_GET['openedSubscriptions']); } else { $openedSubscriptionIds = array(); }

							$sql_subunit_sql = "";
							if($subunit_filter > 0) {
								$sql_subunit_sql = " AND subscriptionmulti.customer_subunit_id = '".$o_main->db->escape_str($subunit_filter)."'";
							}
                            $rows = array();
                            $s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = ? AND content_status = 0 ".$sql_subunit_sql." ORDER BY stoppedDate <> '0000-00-00', stoppedDate is not null,  stoppedDate DESC, subscriptionName ASC";
                            $o_query = $o_main->db->query($s_sql, array($cid));
                            $default_rows = $o_query ? $o_query->result_array() : array();

                            $s_sql = "SELECT * FROM contactperson WHERE email = ? AND type = ?";
                            $o_query = $o_main->db->query($s_sql, array($variables->loggID, $people_contactperson_type));
                            $employee = $o_query ? $o_query->row_array() : array();

                            $currentWorkgroupIds = array();
                            $s_sql = "SELECT * FROM workgroupleader WHERE employeeId = ?";
                            $o_query = $o_main->db->query($s_sql, array($employee['id']));
                            $workgroupleaders = $o_query ? $o_query->result_array() : array();
                            foreach($workgroupleaders as $workgroupleader){
                                $currentWorkgroupIds[] = $workgroupleader['workgroupId'];
                            }
                            foreach($default_rows as $row) {
                                if($accessElementRestrict_HideSubscriptionsNotInYourWorkgroup) {
                                    if(in_array($row['workgroupId'], $currentWorkgroupIds)){
                                        $rows[] = $row;
                                    }
                                } else {
                                    $rows[] = $row;
                                }
                            }

                            ?>
        					<div class="p_contentBlock highligtedBlock">
        						<div class="p_contentBlockTitle dropdown_content_show show_subscription">
                                    <?php echo $formText_Subscriptions_Output;?>
                                    <span class="badge">
                                        <?php echo count($rows); ?>
                                    </span>
                                    <?php if($moduleAccesslevel > 10) { ?>
                                        <a href="#" class="output-edit-subscribtion-detail  addEntryBtn small" data-customer-id="<?php echo $cid; ?>" data-blockid="12">
                                            <?php echo $formText_AddSubscription_output; ?>
                                        </a>
                                    <?php } ?>
                                    <div class="showArrow"><span class="glyphicon <?php if(!$customer_basisconfig['collapseSubscriptions']){ ?>glyphicon-triangle-bottom<?php } else { ?>glyphicon-triangle-right<?php } ?>"></span></div>
                                </div>
                                <div class="p_contentBlockContent dropdown_content" <?php if(!$customer_basisconfig['collapseSubscriptions']){ ?> style="display: block;" <?php } ?>>
        						<?php
                                $stopped_subscriptions_count = 0;
        						foreach($rows as $row){

                                    $s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($row['subscriptiontype_id']));
                                    $subscriptionType = ($o_query ? $o_query->row_array():array());

									$s_sql = "SELECT * FROM subscriptiontype_subtype WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($row['subscriptionsubtypeId']));
									$subscriptionSubType = ($o_query ? $o_query->row_array():array());

                                    $s_sql = "SELECT contactperson.* FROM contactperson_role_conn
                                    LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
                                    WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
                                    ORDER BY contactperson_role_conn.role DESC";
        							$o_query = $o_main->db->query($s_sql, array($row['id']));
        							$contactPerson = $o_query ? $o_query->row_array() : array();

                                    $s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($row['subscriptiontype_id']));
                                    if($o_query && $o_query->num_rows()>0) {
                                        $subscriptiontype = $o_query->row_array();
                                    }

                                    $offices = array();
                                    $s_sql = "SELECT c.id connectionId, c.subscriptionline_id, sl.articleName as subscriptionlineName, property_unit.* FROM property_unit
                                    LEFT JOIN subscriptionofficespaceconnection c ON c.officeSpaceId = property_unit.id
                                    LEFT OUTER JOIN subscriptionline sl ON sl.id = c.subscriptionline_id
                                    WHERE c.subscriptionId = ?";

                                    $o_query = $o_main->db->query($s_sql, array($row['id']));
                                    if($o_query && $o_query->num_rows()>0){
                                        $offices = $o_query->result_array();
                                    }
                                    $property = array();
                                    foreach($offices as $unit){
                                        $s_sql = "SELECT * FROM property WHERE id = ".$unit['property_id'];
                                        $o_query = $o_main->db->query($s_sql);
                                        $property = ($o_query ? $o_query->row_array() : array());
                                    }

                                    $subscribtionBlockClass = "";
                                    if ($row['stoppedDate'] && $row['stoppedDate'] != '0000-00-00'  && strtotime($row['stoppedDate']) <  strtotime(date("Y-m-d"))):
                                        $subscribtionBlockClass = "stopped";
                                        $stopped_subscriptions_count++;
                                        elseif ($row['stoppedDate'] && $row['stoppedDate'] != '0000-00-00'  && strtotime($row['stoppedDate']) >=  strtotime(date("Y-m-d"))):
                                        $subscribtionBlockClass = "activeStopped";
                                        else: ?>
                                    <?php endif; ?>
                                    <?php if($customer_basisconfig['activateFreeNoBilling']) {?>
                                        <?php if($row['freeNoBilling']) {
                                            $subscribtionBlockClass = "freeNoBilling";
                                        } ?>
                                    <?php } ?>
                                    <?php if($row['onhold']){
                                        $subscribtionBlockClass = "onhold";
                                    }

                                    ?>
                                    <div class="subscription-block <?php echo $subscribtionBlockClass;?>">
        								<div class="subscription-block-title <?php if(in_array($row['id'], $openedSubscriptionIds)) echo ' active';?>" data-subscription-id="<?php echo $row['id'];?>">
                                            <div class="subscription-block-title-col c1">
                                                <b><?php echo $row['subscriptionName'];?></b>
                                                <div class="subscriptiontitle_dropdown_active"><?php echo $contactPerson['name']." ".$contactPerson['middlename']." ".$contactPerson['lastname'];?></div>
                                                <div><?php echo $subscriptionType['name'];
												if($subscriptionSubType) echo "<br/>".$subscriptionSubType['name'];
												if($property) echo ' ('.$property['name'].')';?></div>
                                                <?php if($customer_basisconfig['activateFreeNoBilling']) {?>
                                                    <?php if($row['freeNoBilling']) { ?>
                                                        <div><?php echo $formText_FreeNoBilling_Output;?></div>
                                                    <?php } ?>
                                                <?php } ?>
                                                <?php
                                                if($v_customer_accountconfig['activateExtraCheckbox']) {?>
                                                    <?php if($row['extraCheckbox']) { ?>
                                                        <div><?php echo $v_customer_accountconfig['extraCheckboxName'];?></div>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>
                                            <div class="subscription-block-title-col c2">
                                                <?php
                                                if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                                                    echo $formText_StartMonth_Output;
                                                } else {
                                                    echo $formText_StartDate_output;
                                                }
                                                ?>:
                                                <b><?php echo formatDate($row['startDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
                                                <br/>
                                                <?php
												if($row['connectedCustomerId'] == 0){
													if(!$subscriptionSubType['is_free'] && $subscriptionSubType['type'] != 4){
														if(!$row['freeNoBilling']) {
															$subscriptionInvoiced = false;

															$s_sql = "SELECT i.* FROM invoice i
															JOIN customer_collectingorder co ON co.invoiceNumber = i.id
															JOIN orders o ON o.collectingorderId = co.id
															WHERE o.subscribtionId = ?
															GROUP BY i.id ORDER BY i.invoiceDate DESC LIMIT 5";
															$o_query = $o_main->db->query($s_sql, array($row['id']));
															$subscriptionInvoices = $o_query ? $o_query->result_array() : array();
															if(count($subscriptionInvoices) > 0){
																$subscriptionInvoiced = true;
															}
															?>
		                                                    <?php
															if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
																if(!$subscriptionInvoiced){
																	echo $formText_FirstInvoicePeriodFromMonth_output;
																} else {
																	echo $formText_nextInvoicePeriodFromMonth_output;
																}
									                        } else {
																if(!$subscriptionInvoiced){
																	echo $formText_FirstInvoicePeriodFromDate_output;
																} else {
																	echo $formText_nextInvoicePeriodFromDate_output;
																}
									                        }
		                                                    ?>:
		                                                    <b><?php echo formatDate($row['nextRenewalDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
		                                                    <br/>
		                                                <?php
														}
													}
												} else {
													$s_sql = "SELECT * FROM customer WHERE id = ?";
													$o_query = $o_main->db->query($s_sql, array($row['connectedCustomerId']));
													$connectedCustomer = $o_query ? $o_query->row_array() : array();

													echo $formText_SubMemberOf_output.": ".$connectedCustomer['name']." ".$connectedCustomer['middlename']." ".$connectedCustomer['lastname'];

												} ?>
                                                <?php if ($row['stoppedDate'] && $row['stoppedDate'] != '0000-00-00') : ?>
                                                    <div class="subscriptiontitle_dropdown_active">
                                                        <?php
                                                        if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                                                            echo $formText_StoppedLastMonth_output;
                                                        } else {
															if(strtotime($row['stoppedDate']) >= strtotime(date("Y-m-d"))){
																echo $formText_FutureStopped_output;
															} else {
	                                                            echo $formText_StoppedLastDate_output;
															}
                                                        }
                                                        ?>:
                                                        <b><?php echo formatDate($row['stoppedDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="subscriptiontitle_dropdown_active">
													<?php
                                                    if($subscriptionType['subscription_category'] == 1){
                                                        echo $formText_InvoicingDates_output.": ";

                                                        $s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$row['id']." ORDER BY id ASC";
                                                        $o_query = $o_main->db->query($s_sql);
                                                        $datesForInvoicing = ($o_query ? $o_query->result_array() : array());

                                                        $dateArray = array();
                                                        foreach($datesForInvoicing as $dateForInvoicing) {
                                                            $dateArray[] = date("d.m", strtotime($dateForInvoicing['date']));
                                                        }
                                                        echo implode(", ", $dateArray);
                                                    } else {
														if($row['connectedCustomerId'] == 0){
	                                                        if($subscriptionType['periodUnit'] == 0) {
	                                                            echo $formText_PeriodNumberOfMonths_Output;
	                                                         } else {
	                                                            echo $formText_PeriodNumberOfYears_Output;
	                                                        }

	                                                        ?>: <?php echo $row['periodNumberOfMonths'];
														}
                                                    }
                                                    ?>
                                                </div>
												<?php
												if(isset($v_customer_accountconfig['activate_account_connection']) && 1 == $v_customer_accountconfig['activate_account_connection'])
												{
													$s_connected_accounts = $formText_NoneConnected_Output;
													$o_find = $o_main->db->query("SELECT accountname FROM subscriptionmulti_accounts WHERE subscriptionmulti_id = '".$o_main->db->escape_str($row['id'])."' AND content_status = 0");
													if($o_find && $o_find->num_rows()>0)
													{
														$s_connected_accounts = '';
														foreach($o_find->result_array() as $v_row)
														{
															$s_connected_accounts .= (''!=$s_connected_accounts?', ':'').$v_row['accountname'];
														}
													}
													?>
													<div class="subscriptiontitle_dropdown_active">
														<?php echo $formText_ConnectedAccounts_Output.': '.$s_connected_accounts; ?>
													</div>
													<?php
												}
												?>
                                            </div>
                                            <div class="subscription-block-title-col c22">
												<div>
													<?php
													if($customer_basisconfig['activate_original_start_date']){
													?>
														<span class="inputInfo">
															<?php echo $formText_OriginalStartDate_output;?>: <?php if($row['original_start_date'] != "" && $row['original_start_date'] != "0000-00-00" && $row['original_start_date'] != null){ echo formatDate($row['original_start_date']); } else { echo formatDate($row['startDate']);}?>

														</span>
													<?php } ?>
												</div>
												<div>
													<?php
													$s_sql = "SELECT i.* FROM invoice i
													JOIN customer_collectingorder co ON co.invoiceNumber = i.id
													JOIN orders o ON o.collectingorderId = co.id
													WHERE o.subscribtionId = ?
													GROUP BY i.id ORDER BY i.invoiceDate DESC LIMIT 5";
													$o_query = $o_main->db->query($s_sql, array($v_row['id']));
													$subscriptionInvoices = $o_query ? $o_query->result_array() : array();
													if(count($subscriptionInvoices) > 0){
														$subscriptionInvoiced = true;
													}
													if(!$subscriptionInvoiced) {
														$nextRenewalInfo = $formText_InvoiceNeverSent_output;
													} else {
														$nextRenewalInfo = $formText_InvoiceHistory_output.'&nbsp;&nbsp;<span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">';
														$nextRenewalInfo.= '<div class="hoverLabel">'.$formText_InvoiceHistory_output.'</div><table class="table"><tr><th>'.$formText_date_output.'</th><th>'.$formText_InvoiceNumber_output.'</th></tr>';
														foreach($subscriptionInvoices as $subscriptionInvoice) {
															$nextRenewalInfo.= '<tr><td>'.date("d.m.Y",strtotime($subscriptionInvoice['invoiceDate'])).'</td><td>'.$subscriptionInvoice['id'].'</td></tr>';
														}
														$nextRenewalInfo .= '</table></div></span>';
													}


													?><span class="inputInfo"><?php echo $nextRenewalInfo;?></span>
												</div>
                                            </div>
                                            <div class="subscription-block-title-col editLastColumn">
												<?php /*
                                                <div class="showArrow"><span class="glyphicon <?php if(in_array($row['id'], $openedSubscriptionIds)){ ?>glyphicon-triangle-bottom<?php } else { ?> glyphicon-triangle-right <?php } ?>"></span></div>
                                                <div class="clear"></div>
												*/?>
                                            </div>
											<div class="clear"></div>
											<div class="showMoreInfo"><span><?php echo $formText_ShowMoreInfo_output;?></span> <span class="fas fa-angle-double-down"></span></div>
        								</div>
                                        <div class="subscription-block-dropdown" <?php if(in_array($row['id'], $openedSubscriptionIds)) echo 'style="display:block;"';?>>
											<div class="subscription-extra-detailrow">
												<div class="subscription-block-title-col no-top-padding">
													<div class="subscription-block-moreinfo">
														<?php
														$s_sql = "SELECT customer.*, cei.external_id FROM customer
														LEFT OUTER JOIN customer_externalsystem_id cei ON cei.customer_id = customer.id WHERE customer.id = ?";
														$o_query = $o_main->db->query($s_sql, array($row['invoice_to_other_customer_id']));
														$invoiceCustomer = $o_query ? $o_query->row_array() : array();

														if($v_customer_accountconfig['activate_subunits']) {
															$s_sql = "SELECT * FROM customer_subunit WHERE id = ? ORDER BY name";
															$o_query = $o_main->db->query($s_sql, array($row['customer_subunit_id']));
															$subunit = $o_query ? $o_query->row_array() : array();
															?>
															<div><b><?php echo $formText_Subunit_output?>:</b> <?php echo $subunit['name'];?></div>
															<?php
														}
														?>
														<?php
														$b_activate_accounting_project = ($customer_basisconfig['activeAccountingProjectOnOrder'] && !$customer_basisconfig['activateProjectConnection']) || in_array($customer_basisconfig['activateProjectConnection'], array(1,3));

														if($b_activate_accounting_project) {
															$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ? ORDER BY name";
															$o_query = $o_main->db->query($s_sql, array($row['projectId']));
															$projectforaccounting = $o_query ? $o_query->row_array() : array();
															?>
															<div><b><?php echo $formText_Project_output?>:</b> <?php echo $departmentforaccounting['projectnumber']." ".$projectforaccounting['name']?></div>
														<?php } ?>
											            <?php if($v_customer_accountconfig['activateAccountingDepartmentOnSubscription'] > 1) {
															$s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ? ORDER BY name";
															$o_query = $o_main->db->query($s_sql, array($row['departmentCode']));
															$departmentforaccounting = $o_query ? $o_query->row_array() : array();
															?>
															<div><b><?php echo $formText_Department_output?>:</b> <?php echo $departmentforaccounting['departmentnumber']." ".$departmentforaccounting['name']?></div>
														<?php } ?>
														<?php if($customer_basisconfig['activateWorklineOnOrder']){
															$s_sql = "SELECT * FROM workgroup WHERE id = ?";
										                    $o_query = $o_main->db->query($s_sql, array($row['workgroupId']));
										                    $workgroup = ($o_query ? $o_query->row_array() : array());
															?>
															<div><b><?php echo $formText_WorkGroup_Output?>:</b> <?php echo $workgroup['name'];?></div>
														<?php } ?>

														<?php $placeSubscriptionNameInInvoiceLine = intval($row['placeSubscriptionNameInInvoiceLine']);
														$defaultChoise = intval($subscriptiontype['default_subscriptionname_in_invoiceline']);
								                        $defaultChoiseText = "";
								                        if($defaultChoise == 0){
								                            $defaultChoiseText = $formText_No_output;
								                        } else if ($defaultChoise == 1) {
								                            $defaultChoiseText = $formText_Yes_output;
								                        }
														?>
														<div><b><?php echo $formText_PlaceSubscriptionNameInInvoiceLine_Output?>:</b>
															<?php if($placeSubscriptionNameInInvoiceLine == 0) {
																echo $formText_UseDefault_output." (".$defaultChoiseText.")";
															} else if($placeSubscriptionNameInInvoiceLine == 1) {
																echo $formText_No_output;
															} else if($placeSubscriptionNameInInvoiceLine == 2) {
																echo $formText_Yes_output;
															}?>
														</div>
											            <?php if($customer_basisconfig['activateRentalUnitConnection']) { ?>
															<div><b><?php echo $formText_VatFreeContract_Output?>:</b> <?php if($row['vat_free_contract']) { echo $formText_Yes_output;} else { echo $formText_No_output;}?></div>
														<?php } ?>
														<?php if($customer_basisconfig['activateFreeNoBilling']) { ?>
														  	<div><b><?php echo $formText_FreeNoBilling_Output?>:</b> <?php if($row['freeNoBilling']) { echo $formText_Yes_output;} else { echo $formText_No_output;}?></div>
													  	<?php } ?>
											            <?php if($v_customer_accountconfig['activateExtraCheckbox']) { ?>
														  	<div><b><?php echo $v_customer_accountconfig['extraCheckboxName']?>:</b> <?php if($row['extraCheckbox']) { echo $formText_Yes_output;} else { echo $formText_No_output;}?></div>
													  	<?php } ?>
											            <?php if($v_customer_accountconfig['activateSubscriptionOnHold']) { ?>
														  	<div><b><?php echo $formText_OnHold_Output?>:</b> <?php if($row['onhold']) { echo $formText_Yes_output;} else { echo $formText_No_output;}?></div>
														<?php } ?>
														<?php if($invoiceCustomer) { ?>
															<div><b><?php echo $formText_InvoiceToOtherCustomer_output?>:</b> <?php echo $invoiceCustomer['name']." ".$invoiceCustomer['middlename']." ".$invoiceCustomer['lastname']. " ".$invoiceCustomer['external_id'];?></div>
														<?php } ?>
													</div>
												</div>
                                                <div class="clear"></div>
												<div class="subscription-block-titleactions">
													<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-detail editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
													<?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-subscription editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-trash"></span></button><?php } ?>
												</div>
                                                <div class="clear"></div>
											</div>
											<?php
                                            if($customer_basisconfig['activateProjectConnection'] && 2 == $customer_basisconfig['activateProjectConnection']) { ?>
                                            <div class="subscription-connection-row">
                                                <div class="subscription-block-title-col cfull">

                                                    <b><?php if($customer_basisconfig['activateProjectConnection'] > 1) { echo $formText_Assign_output; } else { echo $formText_AssignAccountingProject_output;} ?></b>
                                                    <a href="#"  class="assignRentalUnit" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>">
                                                        <span class="glyphicon glyphicon-pencil"></span>
                                                        <?php echo $formText_AddEditConnection_output; ?>
                                                    </a>
                                                    <?php
                                                        if(count($offices) > 0 && $row['connectToProjectBy'] == 0){
                                                        ?>
                                                        <ul class="subscription-office-list">
                                                            <?php
                                                            $totalArea = 0;
                                                            foreach($offices as $unit):
                                                                $totalArea += $unit['size'];
                                                                $s_sql = "SELECT * FROM property WHERE id = ".$unit['property_id'];
                                                                $o_query = $o_main->db->query($s_sql);
                                                                $property = ($o_query ? $o_query->row_array() : array());
                                                                $s_sql = "SELECT * FROM property_part WHERE id = ".$unit['propertypart_id'];
                                                                $o_query = $o_main->db->query($s_sql);
                                                                $propertyPart = ($o_query ? $o_query->row_array() : array());
                                                             ?>
                                                                <li>
                                                                    <span class="subscription-office-list-name">
                                                                        <?php echo $property['name']; ?> -
                                                                        <?php if($propertyPart) { echo $propertyPart['name']." - "; } echo $unit['name']." ";?>
                                                                        <?php if($unit['subscriptionline_id'] > 0) { ?>
                                                    						<br/><span class="subscriptionlineName"><?php echo $unit['subscriptionlineName']?></span>
                                                                        <?php } ?>
                                                                    </span>
                                                                    <span class="subscription-office-list-size">
                                                                        <?php echo $formText_ContractArea_Output?>:
                                                                        <?php echo $unit['size']." ".$formText_Squaremeter_output; ?>

                                                                    </span>
                                                                    <span class="subscription-office-list-size">
                                                                        <?php echo $formText_MeasuredArea_Output?>:
                                                                        <?php echo $unit['measuredArea']." ".$formText_Squaremeter_output; ?>
                                                                    </span>
                                                                </li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                        <div class="">
                                                            <b><?php echo $formText_TotalArea_output;?> <?php echo $totalArea;?><?php if($totalArea > 0 ) { ?> - <?php echo $formText_VatFreeArea_output;?> <?php if($row['vatFreeArea'] != "") { echo $row['vatFreeArea']; } else { echo 0;}?>
                                                        <!-- <span class="edit_vat_free_area" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></span>--><?php } ?></b>
                                                        </div>

                                                    <?php } ?>
                                                    <?php if($row['connectToProjectBy'] == 1 && $row['projectId'] != 0) { ?>
                                                        <div class="">
                                                            <?php echo $formText_ProjectCode_output.": ".$row['projectId']?>
                                                        </div>
                                                        <br/>
                                                    <?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionRenewalDateSetting']){?>
												<?php if(!$subscriptionType['subscription_category']) { ?>
		                                            <div class="subscription-dates">

		                                                <div class="subscription-block-title-col chalf">
		                                                    <b><?php echo $formText_InvoiceAndDateSettings_output?></b>
		                                                    <?php
		                                                    if($row) {
		                                                        $s_sql = "SELECT * FROM default_repeatingorder_invoicedate_settings WHERE renewalappearance = ? AND renewalappearance_daynumber = ? AND invoicedate_suggestion = ? AND invoicedate_daynumber = ? AND duedate = ? AND duedate_daynumber = ?";

		                                                        $o_query = $o_main->db->query($s_sql, array(intval($row['renewalappearance']),intval($row['renewalappearance_daynumber']),intval($row['invoicedate_suggestion']),intval($row['invoicedate_daynumber']),intval($row['duedate']),intval($row['duedate_daynumber'])));
		                                                        $currentDefaultSetting = $o_query ? $o_query->row_array() : array();
		                                                        if($currentDefaultSetting) {
		                                                            echo $currentDefaultSetting['name'];
		                                                        } else {
		                                                            echo $formText_Customized_output;
		                                                        }
		                                                    }
		                                                    ?>
		                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-renewal-date editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
		                                                </div>
		                                                <div class="subscription-block-title-col chalf rightAligned">
		                                                    <b><?php echo $formText_SeperateInvoice_output;?>:</b>
		                                                    <?php if($row['seperateInvoiceFromSubscription']) { echo $formText_Yes_output; } else { echo $formText_No_output; } ?>
		                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-seperate editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
		                                                </div>
		                                                <div class="clear"></div>
		                                            </div>
                                            	<?php } ?>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionTurnOverBasedRent']){?>
                                                <div class="subscription-price-adjustment">
                                                    <div class="subscription-block-title-col">
                                                        <b><?php echo $formText_TurnoverBasedRent_output;?></b>
                                                        <?php if($row['property_turnover_rent'] == 0) { echo $formText_No_output;}?>
                                                        <?php if($row['property_turnover_rent'] == 1) { echo $formText_Yes_output;}?>
                                                        <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertyturnover editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                    <?php if($row['property_turnover_rent'] == 1){ ?>
                                                        <div class="subscription-block-title-col no-top-padding">
                                                            <div class="subscription-price-adjustment-row"><span><?php echo $formText_PercentageOfTurnover_Output?></span> <?php echo number_format($row['percentageOfTurnover'], 2, ",", " ");?></div>
                                                            <div class="subscription-price-adjustment-row"><span><?php echo $formText_MinimumAmount_Output?></span> <?php echo number_format($row['turnoverMinimumAmount'], 2, ",", " ");?></div>
                                                        </div>
                                                        <div class="clear"></div>
                                                        <?php

                                                        ?>
                                                        <div class="subscription-turnover-list subscription-block-title-col cfull adjustment-padding">
                                                            <div class="output-edit-turnoveryear addEntryBtn small" data-subscribtion-id="<?php echo $row['id'];?>" data-customer-id="<?php echo $cid;?>" data-turnover-id="0"><?php echo $formText_AddTurnOver_output;?></div>

                                                            <?php

                                                            $s_sql = "SELECT * FROM subscriptionmulti_turnover WHERE subscriptionmulti_id = ? ORDER BY year DESC";
                                                            $o_query = $o_main->db->query($s_sql, array(intval($row['id'])));
                                                            $turnoverList = $o_query ? $o_query->result_array() : array();
                                                            ?>
                                                            <table class="table table-bordered">
                                                                <tr>
                                                                    <th width="140px"><?php echo $formText_TurnoverYear_output;?></th>
                                                                    <th><?php echo $formText_TurnoverAmount_output;?></th>
                                                                    <th></th>
                                                                </tr>
                                                                <?php
                                                                $shownTurnOver = 0;
                                                                foreach($turnoverList as $turnover) {
                                                                    ?>
                                                                    <tr class="<?php if($shownTurnOver > 4) echo 'turnoverHidden'?>">
                                                                        <td><?php echo $turnover['year'];?></td>
                                                                        <td><?php echo number_format($turnover['amount'], 0, ",", " ");?></td>
                                                                        <td>
                                                                            <span class="glyphicon glyphicon-pencil output-edit-turnoveryear editBtnIcon output-btn"  data-subscribtion-id="<?php echo $row['id'];?>" data-customer-id="<?php echo $cid;?>" data-turnover-id="<?php echo $turnover['id']?>"></span>
                                                                            <span class="glyphicon glyphicon-trash output-delete-turnoveryear editBtnIcon output-btn"  data-subscribtion-id="<?php echo $row['id'];?>" data-customer-id="<?php echo $cid;?>" data-turnover-id="<?php echo $turnover['id']?>"></span>
                                                                        </td>
                                                                    </tr>
                                                                    <?php
                                                                    $shownTurnOver++;
                                                                }
                                                                ?>
                                                            </table>
                                                            <?php
                                                            if($shownTurnOver > 5){
                                                                ?>
                                                                <div class="showMoreTurnOver"><?php echo $formText_ShowAllTurnOver_output;?></div>
                                                                <?php
                                                            }
                                                            ?>
                                                        </div>
                                                        <div class="clear"></div>
                                                    <?php } ?>
                                                </div>
                                            <?php }?>
                                            <?php if($customer_basisconfig['activateSubscriptionPriceAdjustment']){?>
                                            <div class="subscription-price-adjustment">
                                                <div class="subscription-block-title-col">
                                                    <?php if($row['priceAdjustmentType'] == 3 ) {
                                                        ?>
                                                        <b><?php echo $formText_ManualPriceAdjustment_output?></b>
                                                        <?php
                                                    } else { ?>
                                                        <b><?php echo $formText_AutomaticPriceAdjustment_output?></b>
                                                        <?php
                                                        if($row['priceAdjustmentType'] == 1 ) {
                                                            echo " - ".$formText_PercentagePriceAdjustment_output;
                                                        } else if($row['priceAdjustmentType'] == 2 ) {
                                                            echo " - ".$formText_CPIPriceAdjustment_output;
                                                        } else if(intval($row['priceAdjustmentType']) == 0){
                                                            echo $formText_No_output;
                                                        }
                                                    }

                                                    ?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-price-adjustment editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="subscription-block-title-col cfull adjustment-padding">
                                                    <?php
                                                    if($row['priceAdjustmentType'] == 1 ) {
                                                        ?>
                                                        <div class="subscription-price-adjustment-row"><span><?php echo $formText_NextAnnualAdjustmentDate_Output?></span> <?php if($row['annualAdjustmentDate'] != "0000-00-00" && $row['annualAdjustmentDate'] != "") echo date("d.m.Y", strtotime($row['annualAdjustmentDate']));?></div>
                                                        <div class="subscription-price-adjustment-row"><span><?php echo $formText_AnnualPercentageAdjustment_Output?></span> <?php echo number_format($row['annualPercentageAdjustment'], 2, ",", "")?></div>
                                                        <?php
                                                    } else if($row['priceAdjustmentType'] == 2 ) {

                                                        $adjustmentIndex = 0;
                                                        $lastAdjustmentIndex = 0;
                                                        $cpiPercentage = 0;

														$s_key = date("Y-m-01", strtotime($row['nextCpiAdjustmentFoundationDate']));
														$indexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

														$s_key = date("Y-m-01", strtotime($row['lastCpiAdjustmentFoundationDate']));
														$lastIndexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

                                						if($indexItem){
                                							$adjustmentIndex = str_replace(",",".",$indexItem['index_number']);
                                						} else {
                                                            $adjustmentIndex = $formText_NotAvailable_output;
                                                            $cpiError = true;
                                                        }
                                						if($lastIndexItem){
                                							$lastAdjustmentIndex = str_replace(",",".",$lastIndexItem['index_number']);
                                						} else {
                                                            $lastAdjustmentIndex = $formText_NotAvailable_output;
                                                            $cpiError = true;
                                                        }
                                                        if(!$cpiError){
                                    						$cpiPercentage = number_format(($adjustmentIndex - $lastAdjustmentIndex)*100/$lastAdjustmentIndex, 2, ",", "");
                                                        } else {
                                                            $cpiPercentage = $formText_NotAvailable_output;
                                                        }
                                                        ?>
                                                        <table class="table table-bordered">
                                                            <tr>
                                                                <th></th>
                                                                <th><?php echo $formText_AdjustmentDate_output;?></th>
                                                                <th><?php echo $formText_AdjustmentFoundationDate_output;?></th>
                                                                <th><?php echo $formText_AdjustmentIndex_output;?></th>
                                                            </tr>
                                                            <tr>
                                                                <td><?php echo $formText_NextAdjustment_output;?></td>
                                                                <td><?php if($row['nextCpiAdjustmentDate'] != "0000-00-00" && $row['nextCpiAdjustmentDate'] != "") echo date("d.m.Y", strtotime($row['nextCpiAdjustmentDate'])); ?></td>
                                                                <td><?php  if($row['nextCpiAdjustmentFoundationDate'] != "0000-00-00" && $row['nextCpiAdjustmentFoundationDate'] != "") echo date("m.Y", strtotime($row['nextCpiAdjustmentFoundationDate']));?></td>
                                                                <td><?php if($adjustmentIndex != $formText_NotAvailable_output) { echo number_format($adjustmentIndex, 2, ",", ""); } else { echo $adjustmentIndex; }?></td>
                                                            </tr>
                                                            <tr>
                                                                <td><?php echo $formText_LastAdjustment_output;?></td>
                                                                <td><?php  if($row['lastCpiAdjustmentDate'] != "0000-00-00" && $row['lastCpiAdjustmentDate'] != "") echo date("d.m.Y", strtotime($row['lastCpiAdjustmentDate'])); ?></td>
                                                                <td><?php if($row['lastCpiAdjustmentFoundationDate'] != "0000-00-00" && $row['lastCpiAdjustmentFoundationDate'] != "") echo date("m.Y", strtotime($row['lastCpiAdjustmentFoundationDate']));?></td>
                                                                <td><?php if($lastAdjustmentIndex != $formText_NotAvailable_output) { echo number_format($lastAdjustmentIndex, 2, ",", ""); } else { echo $lastAdjustmentIndex; }?></td>
                                                            </tr>
                                                        </table>

                                                        <?php
                                                    } else if($row['priceAdjustmentType'] == 3 ) {
                                                        ?>
                                                        <div class="subscription-price-adjustment-row"><span><?php echo $formText_NextManualAdjustmentDate_Output?></span> <?php if($row['nextManualAdjustmentDate'] != "0000-00-00" && $row['nextManualAdjustmentDate'] != "") echo date("d.m.Y", strtotime($row['nextManualAdjustmentDate']));?></div>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionGuarantee']){ ?>
                                            <div class="subscription-guarantee">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_Guarantee_output?></b>
                                                    <?php if($row['property_guarantee'] == 0) { echo $formText_No_output;}?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-guarantee editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <?php if($row['property_guarantee'] > 0){ ?>
                                                    <div class="subscription-block-title-col no-top-padding">
                                                        <?php
                                                        $guaranteeArray = array($formText_BankGuarantee_output, $formText_CorporateGuarantee_output, $formText_Deposit_output, $formText_other_output);
                                                        if($row['property_guarantee'] > 0){
                                                            echo $guaranteeArray[$row['property_guarantee']-1];
                                                            ?>
                                                            <div class="subscription-guarantee-row">
                                                                <?php echo $formText_GuaranteeAmountAgreed_output?> <?php echo $row['property_guarantee_agreed'];?>
                                                            </div>
                                                            <div class="subscription-guarantee-row">
                                                                <?php echo $formText_GuaranteeAmountPledged_output?> <?php echo $row['property_guarantee_amountpledged'];?>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionOptions']){ ?>
                                            <div class="subscription-guarantee">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_Option_output?></b>
                                                    <?php if($row['property_option'] == 0) { echo $formText_No_output;}?>
                                                    <?php if($row['property_option'] == 1) { echo $formText_Yes_output;}?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertyoption editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <?php if($row['property_option'] == 1 && $row['property_option_text'] !=""){ ?>
                                                    <div class="subscription-block-title-col no-top-padding">
                                                        <?php
                                                        echo nl2br($row['property_option_text']);
                                                        ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                <?php } ?>
                                                <?php if($row['property_option'] == 1 && $row['property_option_lastdatetonotify'] != "" && $row['property_option_lastdatetonotify'] != "0000-00-00"){ ?>
                                                    <div class="subscription-block-title-col no-top-padding">
                                                        <?php echo $formText_OptionLastDateToNotify_output;?>  <?php
                                                        echo date("d.m.Y", strtotime($row['property_option_lastdatetonotify']));
                                                        ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>

                                            <?php if($customer_basisconfig['activateSubscriptionMarketingContribution']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_MarketingContribution_output?></b>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertymarketing editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="subscription-block-title-col no-top-padding">
                                                    <?php
                                                    echo nl2br($row['property_marketing_contribution']);
                                                    ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionPropertyTaxMustBeCharged']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_PropertyTaxMustBeCharged_output?></b>
                                                    <?php if($row['property_tax_must_be_charged'] == 0) { echo $formText_No_output;}?>
                                                    <?php if($row['property_tax_must_be_charged'] == 1) { echo $formText_Yes_output;}?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertytaxmustbecharged editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionSeparateProvision']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_SeparateProvision_output?></b>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertyseparateprovision editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="subscription-block-title-col no-top-padding">
                                                    <?php
                                                    echo nl2br($row['property_separate_provision']);
                                                    ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>

                                            <?php if($customer_basisconfig['activateSubscriptionBreakCloses']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_BreakCloses_output?></b>
                                                    <?php if($row['property_break_closes'] == 0) { echo $formText_No_output;}?>
                                                    <?php if($row['property_break_closes'] == 1) { echo $formText_Yes_output;}?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertybreakcloses editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <?php if($row['property_break_closes'] == 1 && $row['property_break_closes_text'] !=""){ ?>
                                                    <div class="subscription-block-title-col no-top-padding">
                                                        <?php
                                                        echo nl2br($row['property_break_closes_text']);
                                                        ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionResignation']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_OngoingTermination_output?></b>
                                                    <?php if($row['ongoing_termination'] == 0) { echo $formText_No_output;}?>
                                                    <?php if($row['ongoing_termination'] == 1) { echo $formText_Yes_output;}?>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertyresignation editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>

                                                </div>
                                                <div class="clear"></div>
                                                <?php if($row['ongoing_termination'] == 1 && $row['ongoing_termination'] !=""){ ?>
                                                    <div class="subscription-block-title-col no-top-padding">
                                                        <?php
                                                        if($row['ongoing_termination_months'] != "") echo $row['ongoing_termination_months']." ".$formText_Months_output;
                                                        ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            <?php if($customer_basisconfig['activateSubscriptionAdministrationSurchargeNuInPercent']){ ?>
                                            <div class="subscription-section">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_AdministrationSurchargeNuInPercent_output?></b>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-propertyadministrationsurcharge editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="subscription-block-title-col no-top-padding">
                                                    <?php
                                                    echo number_format($row['property_administration_surcharge_nu_in_percent'], 2, ",", "");
                                                    ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                            <?php } ?>

                                            <?php
                                            $sql = "SELECT vat_statement.*, vat_statement_set.name as setName FROM vat_statement
                                            JOIN vat_statement_set ON vat_statement_set.id = vat_statement.vat_statement_set_id
                                            WHERE vat_statement.status = 1 AND vat_statement.subscription_id = ? AND vat_statement.statement_file <> ''";
                                        	$o_query = $o_main->db->query($sql, array($row['id']));
                                            $submitted_vat_statements = $o_query ? $o_query->result_array() : array();
                                            if(count($submitted_vat_statements) > 0){
                                                ?>
                                                <div class="subscription-vatstatements">
                                                    <div class="titleWrapper">
                                                        <div class="subscription-block-title-col">
                                                            <b><?php echo $formText_SubmittedVatStatements_output?></b> (<?php echo count($submitted_vat_statements);?>)
                                                        </div>
                                                        <div class="subscription-block-title-col editLastColumn">
                                                            <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="subscription-vatstatements-dropdown">
                                                        <?php foreach($submitted_vat_statements as $submitted_vat_statement) {
                                                            ?>
                                                            <div class="">
                                                                <?php

                                                                $fileInfo = json_decode($submitted_vat_statement['statement_file'], true);
                                                                $fileParts = explode('/',$fileInfo[0][1][0]);
                                                                $fileName = array_pop($fileParts);
                                                                $fileParts[] = rawurlencode($fileName);
                                                                $filePath = implode('/',$fileParts);
                                                                $fileUrl = "";
                                                                $fileUrl = $fileInfo[0][1][0];
                                                                $fileName = $fileInfo[0][0];
                                                                if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                                                                {
                                                                    $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=vat_statement&field=statement_file&ID='.$submitted_vat_statement['id'];
                                                                }
                                                                ?>
                                                                <a href="<?php echo $fileUrl; ?>" target="_blank">
                                                                    <?php echo $fileName; ?>
                                                                </a>
                                                            </div>
                                                            <?php
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            <?php } ?>

                                            <div class="subscription-comment">
                                                <div class="subscription-block-title-col">
                                                    <b><?php echo $formText_Comment_output?></b>
                                                    <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-comment editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="subscription-block-title-col no-top-padding">
                                                    <?php
                                                    echo nl2br($row['comment']);
                                                    ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>

                                            <div class="subscription-files">
                                                <div class="output-filelist">

                                                    <div style="font-weight:bold; margin:5px 0;"><?php echo $formText_Files_output; ?>
                                                        <?php if($moduleAccesslevel > 10) { ?>
                                                            <a href="#" class="addSubscriptionFileNewBtn small" data-subscription-id="<?php echo $row['id']; ?>">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                                <?php echo $formText_AddFile_output; ?>
                                                            </a>
                                                        <?php } ?>
                                                        <ul>
                                                            <?php
                            								$s_sql = "SELECT * FROM subscriptionmulti_files WHERE content_status < 2 AND subscriptionmulti_id = ? ORDER BY created DESC";
                            								$o_query = $o_main->db->query($s_sql, array($row['id']));
                            								$fileItems = ($o_query ? $o_query->result_array() : array());
                            								foreach($fileItems as $fileItem) {
                            									$file = json_decode($fileItem['file'], true);
                                                                $fileName = $file[0][0];
                                                                $fileUrl = $extradomaindirroot.'/../'.$file[0][1][0];
                                                                if(strpos($file[0][1][0],'uploads/protected/')!==false)
                                                                {
                                                                    $fileUrl = $extradomaindirroot.'/../'.$file[0][1][0].'?caID='.$_GET['caID'].'&table=subscriptionmulti_files&field=file&ID='.$fileItem['id'];
                                                                }
																if(isset($fileItem['signant_id']) && 0 < $fileItem['signant_id'])
																{
																	$s_sql = "SELECT * FROM integration_signant WHERE id = '".$o_main->db->escape_str($fileItem['signant_id'])."'";
																	$o_query = $o_main->db->query($s_sql);
																	$v_signant = $o_query ? $o_query->row_array() : array();

																	$s_file_field = 'file_original';
																	$s_sql = "SELECT * FROM integration_signant_attachment WHERE signant_id = '".$o_main->db->escape_str($fileItem['signant_id'])."'";
																	$o_attachment = $o_main->db->query($s_sql);
																	$v_attachment = $o_attachment ? $o_attachment->row_array() : array();
																	if(1 == $v_signant['sign_status'] || 2 == $v_signant['sign_status'])
																	{
																		$s_file_field = 'file_signed';
																	}
																	$v_files = json_decode($v_attachment[$s_file_field], TRUE);
																	$fileUrl = $variables->account_root_url.$v_files[0][1][0].'?caID='.$_GET['caID'].'&table=integration_signant_attachment&field='.$s_file_field.'&ID='.$v_attachment['id'];
																}
                            									?>
                                                                    <li>
                                                                        <a href="<?php echo $fileUrl; ?>" download target="_blank">
                                                                            <span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                                                                        </a>
                                                                        <?php if($moduleAccesslevel > 110) { ?>
                                                                            <a href="#" class="deleteFileNew" data-subscriptionmultifileid="<?php echo $fileItem['id'];?>">
                                                                                <span class="glyphicon glyphicon-trash"></span>
                                                                            </a>
                                                                        <?php } ?>
                                                                        <span class="filesShowAction">
																			&nbsp;
                                                                            &nbsp;
                                                                            <?php
																			if(isset($fileItem['signant_id']) && 0 < $fileItem['signant_id'])
																			{
																				if(1 < $v_signant['sign_status'])
																				{
																					echo '<span class="hoverEye">'.$v_sign_status[$v_signant['sign_status']].integration_signant_get_status_details($v_signant['id']).'</span>';
																				} else {
																					?><div class="signant-status load" data-id="<?php echo $v_signant["id"];?>"><?php echo $formText_Checking_Output;?> <loading-dots>.</loading-dots></div><?php
																				}
																				if(1 == $accessElementAllow_SendFilesToSignant && 1 >= $v_signant['sign_status'])
																				{
																					?><a class="output-cancel-signant-document script" href="#" data-id="<?php echo $v_signant['id'];?>" data-cancel-msg="<?php echo $formText_CancelDocumentSigning_Output.': '.$v_signant['name'];?>?" title="<?php echo $formText_Cancel_Output;?>"><span class="glyphicon glyphicon-remove-circle"></span></a><?php
																				}
																			} else {
																				if(1 == $accessElementAllow_SendFilesToSignant)
																				{
																					?>
																					<a href="#" class="signCustomFile" data-id="<?php echo $fileItem['id']; ?>">
																						<?php echo $formText_SendForSigning_Output;?>
																					</a>
																					<?php
																				}
																			}
																			?>
																			&nbsp;
                                                                            &nbsp;
                                                                            <input class="popupforminput botspace checkbox change_show_to_customer" <?php if($fileItem['show_to_customer']) echo 'checked';?> id="showToCustomer<?php echo $fileItem['id'];?>" name="show_to_customer" type="checkbox" value="<?php echo $fileItem['id'];?>" autocomplete="off">
                                                                            <label for="showToCustomer<?php echo $fileItem['id'];?>"></label>
                                                                            <label for="showToCustomer<?php echo $fileItem['id'];?>"><?php echo $formText_ShowToCustomer_Output;?></label>

                                                                            <?php if($customer_basisconfig['activate_files_visible_for_performer']) { ?>
                                                                                &nbsp;
                                                                                &nbsp;
                                                                                <input class="popupforminput botspace checkbox change_show_to_performer" <?php if($fileItem['show_to_performer']) echo 'checked';?> id="showToPerformer<?php echo $fileItem['id'];?>" name="show_to_performer" type="checkbox" value="<?php echo $fileItem['id'];?>" autocomplete="off">
                                                                                <label for="showToPerformer<?php echo $fileItem['id'];?>"></label>
                                                                                <label for="showToPerformer<?php echo $fileItem['id'];?>"><?php echo $formText_ShowToPerformer_Output;?></label>
                                                                            <?php } ?>
                                                                        </span>
                                                                    </li>
                            									<?php
                            								}
                            								?>
                                                        </ul>
                                                    </div>

                                                    <?php
                                                    $current_content_id = $row['id'];
                                                    $content_table = 'subscriptionmulti#visible_only_in_here';

                                                    $s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
                                                    $o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
                                                    if($o_query && $o_query->num_rows()>0){
                                                        $folder_data = $o_query->row_array();
                                                    }

                                                    $folder_id = $folder_data['id'];
                                                    $files = array();
                                                    $s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
                                                    $o_query = $o_main->db->query($s_sql, array($folder_id));
                                                    $files = $o_query ? $o_query->result_array() : array();
                                                    ?>
                                                    <div style="font-weight:bold; margin:5px 0;"><?php echo $formText_FilesVisibleOnlyInHere_output; ?>
                                                        <?php /* if($moduleAccesslevel > 10) {?>
                                                            <a href="#" class="addSubscriptionFileBtn small" data-subscription-id="<?php echo $row['id']; ?>" data-type="visible_here">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                                <?php echo $formText_AddFileVisibleOnlyHere_output; ?>
                                                            </a>
                                                        <?php } */?>
                                                    </div>
                                                    <?php if (count($files)): ?>
                                                        <ul>
                                                            <?php foreach($files as $file) {
                                                                $s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC LIMIT 1";
                                                                $o_query = $o_main->db->query($s_sql, array($file['id']));
                                                                if($o_query && $o_query->num_rows()>0){
                                                                    $file_version_data = $o_query->row_array();
                                                                }
                                                                $fileInfo = json_decode($file_version_data['file'], true);
                                                                $fileParts = explode('/',$fileInfo[0][1][0]);
                                                                $fileName = array_pop($fileParts);
                                                                $fileParts[] = rawurlencode($fileName);
                                                                $filePath = implode('/',$fileParts);
                                                                $fileUrl = $fileInfo[0][1][0];
                                                                $fileName = $fileInfo[0][0];
                                                                $fileUrl = "";
                                                                if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                                                                {
                                                                    $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=sys_filearchive_file_version&field=file&ID='.$file_version_data['id'];
                                                                }
                                                            ?>
                                                            <li>
                                                                <a href="<?php echo $fileUrl; ?>" target="_blank">
                                                                    <span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                                                                </a>
                                                                <span class="fileFolderPath">
                                                                    <span class="glyphicon glyphicon-folder-open"></span> <?php echo getFullFolderPathForFile($file['id'], $o_main); ?>
                                                                </span>
                                                                <?php if($moduleAccesslevel > 110) { ?>
                                                                    <a href="#" class="deleteFile" data-deletefileid="<?php echo $file['id']; ?>">
                                                                        <span class="glyphicon glyphicon-trash"></span>
                                                                    </a>
                                                                <?php } ?>
                                                            </li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php endif; ?>

                                                    <?php
                                                    $current_content_id = $row['id'];
                                                    $content_table = 'subscriptionmulti#visible_for_customer';

                                                    $s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
                                                    $o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
                                                    if($o_query && $o_query->num_rows()>0){
                                                        $folder_data = $o_query->row_array();
                                                    }

                                                    $folder_id = $folder_data['id'];
                                                    $files = array();
                                                    $s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
                                                    $o_query = $o_main->db->query($s_sql, array($folder_id));
                                                    $files = $o_query ? $o_query->result_array() : array();
                                                    ?>
                                                    <div style="font-weight:bold; margin:5px 0;"><?php echo $formText_FilesVisibleForCustomer_output; ?>
                                                        <?php /*if($moduleAccesslevel > 10) {  ?>
                                                            <a href="#" class="addSubscriptionFileBtn small" data-subscription-id="<?php echo $row['id']; ?>" data-type="visible_customer">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                                <?php echo $formText_AddFileVisibleForCustomer_output; ?>
                                                            </a>
                                                        <?php }*/ ?>
                                                    </div>
                                                    <?php if (count($files)): ?>
                                                        <ul>
                                                            <?php foreach($files as $file) {
                                                                $s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC LIMIT 1";
                                                                $o_query = $o_main->db->query($s_sql, array($file['id']));
                                                                if($o_query && $o_query->num_rows()>0){
                                                                    $file_version_data = $o_query->row_array();
                                                                }
                                                                $fileInfo = json_decode($file_version_data['file'], true);
                                                                $fileParts = explode('/',$fileInfo[0][1][0]);
                                                                $fileName = array_pop($fileParts);
                                                                $fileParts[] = rawurlencode($fileName);
                                                                $filePath = implode('/',$fileParts);
                                                                $fileUrl = $fileInfo[0][1][0];
                                                                $fileName = $fileInfo[0][0];
                                                                $fileUrl = "";
                                                                if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                                                                {
                                                                    $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=sys_filearchive_file_version&field=file&ID='.$file_version_data['id'];
                                                                }
                                                            ?>
                                                            <li>
                                                                <a href="<?php echo $fileUrl; ?>" target="_blank">
                                                                    <span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                                                                </a>
                                                                <span class="fileFolderPath">
                                                                    <span class="glyphicon glyphicon-folder-open"></span> <?php echo getFullFolderPathForFile($file['id'], $o_main); ?>
                                                                </span>
                                                                <?php if($moduleAccesslevel > 110) { ?>
                                                                    <a href="#" class="deleteFile" data-deletefileid="<?php echo $file['id']; ?>">
                                                                        <span class="glyphicon glyphicon-trash"></span>
                                                                    </a>
                                                                <?php } ?>
                                                            </li>
                                                            <?php } ?>
                                                        </ul>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="subscription-block-children">
                                                <?php
												$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.id = ? AND customer_subunit.content_status = 0";
												$o_query = $o_main->db->query($s_sql, array($row['customer_subunit_id']));
												$selectedSubunit = ($o_query ? $o_query->row_array() : array());
												if($subscriptionType['hide_subscriptionlines']){

													?>
													<table style="width: 100%; table-layout: fixed;">
														<tr>
															<td class="tableInfoTd" width="180px"><?php echo $formText_Reference_Output;?></td>
															<td class="tableInfoTd">
																<span class="tableInfoLabel"><?php
																if($row['reference'] != ''){
																	if($row['reference'] != "empty"){
																		echo $row['reference'];
																	}
																} else {
																	if($selectedSubunit){
																		echo $formText_FromSubunit_output.": ". $selectedSubunit['reference'];
																	} else {
																		echo $formText_FromCustomerCard_output.": ".$customerData['defaultInvoiceReference'];
																	}
																}
																?></span>
																<div class="editEntryBtn editSubscriptionOrderReference glyphicon glyphicon-pencil" data-subscriptionmulti-id="<?php echo $row['id'];?>"></div>

															</td>
														</tr>
														<tr>
															<td class="tableInfoTd"><?php echo $formText_DeliveryAddress_Output;?></td>
															<td class="tableInfoTd">
																<?php
																$s_delivery_address = trim(preg_replace('/\s+/', ' ', $row['delivery_address_line_1'].' '.$row['delivery_address_line_2'].' '.$row['delivery_address_city'].' '.$row['delivery_address_postal_code'].' '.$v_country[$row['delivery_address_country']]));
																if(!empty($s_delivery_address)) { ?>
																<span class="tableInfoLabel"><?php echo $s_delivery_address;?></span>
																<?php } ?>
																<span class="editEntryBtn editSubscriptionDeliveryInfo glyphicon glyphicon-pencil" data-subscriptionmulti-id="<?php echo $row['id'];?>"></span>
															</td>
														</tr>
													</table>
													<?php
												} else {
	                                                if($subscriptionType['activate_specified_invoicing']){
	                                                    echo $formText_SpecifiedInRepeatingOrder_output;
	                                                } else if(intval($subscriptionType['subscription_category']) == 2) {
														echo $formText_SpecifiedInPriceList_output;
													}  else if(intval($subscriptionType['subscription_category']) == 3) {
														$s_sql = "SELECT * FROM customer WHERE id = ?";
														$o_query = $o_main->db->query($s_sql, array($row['connectedCustomerId']));
														$connectedCustomer = $o_query ? $o_query->row_array() : array();

														echo $formText_SubMemberOf_output.": ".$connectedCustomer['name']." ".$connectedCustomer['middlename']." ".$connectedCustomer['lastname'];
													} else {
	                                                    $s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
	                                                    $o_query = $o_main->db->query($s_sql, array($row['id']));
	                                                    ?>
	                                                    <div class="subscription-block-children-lines">
	                                                        <div class="subscription-block-children-lines-label">
	                                                            <?php echo $formText_SubscriptionLines_output;?>
	                                                            <a href="#" class="output-edit-subscription-line-detail small" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>">
	                                                                <span class="glyphicon glyphicon-pencil" style="margin-left: 10px;"></span>
	                                                                <?php echo $formText_AddEditSubscriptionline_output;?>
	                                                            </a>
	                                                        </div>
	                                                        <div class="">
	                                                            <table style="width: 100%; table-layout: fixed;">
	                                                                <tr>
	                                                                    <td class="tableInfoTd" width="180px"><?php echo $formText_Reference_Output;?></td>
	                                                                    <td class="tableInfoTd">
																			<span class="tableInfoLabel"><?php
																			if($row['reference'] != ''){
																				if($row['reference'] != "empty"){
																					echo $row['reference'];
																				}
																			} else {
																				if($selectedSubunit){
																					echo $formText_FromSubunit_output.": ". $selectedSubunit['reference'];
																				} else {
																					echo $formText_FromCustomerCard_output.": ".$customerData['defaultInvoiceReference'];
																				}
																			}
																			?></span>
	                                                                        <div class="editEntryBtn editSubscriptionOrderReference glyphicon glyphicon-pencil" data-subscriptionmulti-id="<?php echo $row['id'];?>"></div>

	                                                                    </td>
	                                                                </tr>
	                                                                <tr>
	                                                                    <td class="tableInfoTd"><?php echo $formText_DeliveryAddress_Output;?></td>
	                                                                    <td class="tableInfoTd">
	                                                                        <?php
	                                                                        $s_delivery_address = trim(preg_replace('/\s+/', ' ', $row['delivery_address_line_1'].' '.$row['delivery_address_line_2'].' '.$row['delivery_address_city'].' '.$row['delivery_address_postal_code'].' '.$v_country[$row['delivery_address_country']]));
	                                                                        if(!empty($s_delivery_address)) { ?>
	                                                                        <span class="tableInfoLabel"><?php echo $s_delivery_address;?></span>
	                                                                        <?php } ?>
	                                                                        <span class="editEntryBtn editSubscriptionDeliveryInfo glyphicon glyphicon-pencil" data-subscriptionmulti-id="<?php echo $row['id'];?>"></span>
	                                                                    </td>
	                                                                </tr>
	                                                            </table>
	                                                            <br/>
	                                                        </div>
	                                                        <div class="subscription-block-children-line">
	                                                            <div class="subscription-block-children-line-col c1"><b><?php echo $formText_Name_output;?></b></div>
	                                                            <div class="subscription-block-children-line-col c2"><b>
	                                                                <?php
	                                                                if($subscriptionType['periodUnit'] == 0){
	                                                                    echo $formText_AmountPerMonth_output."";
	                                                                } else {
	                                                                    echo $formText_AmountPerYear_output."";
	                                                                }
	                                                                ?></b></div>
	                                                            <div class="subscription-block-children-line-col c2"><b>
	                                                            <?php
	                                                                echo $formText_UsePriceFromArticle_output."";
	                                                            ?></b></div>
	                                                            <div class="subscription-block-children-line-col c3 rightAligned"><b><?php
	                                                                if($subscriptionType['periodUnit'] == 0){
	                                                                    echo $formText_PricePerMonth_output."";
	                                                                } else {
	                                                                    echo $formText_PricePerYear_output."";
	                                                                }
	                                                                ?></b></div>
	                                                            <div class="subscription-block-children-line-col c4"><b><?php echo $formText_Discount_output;?></b></div>
	                                                            <div class="subscription-block-children-line-col c5 rightAligned"><b><?php echo $formText_PricePerPeriod_output;?></b></div>

	                                                			<?php if ($row['priceAdjustmentType'] == 2){ ?>
	                                                                <div class="subscription-block-children-line-col c5"><b><?php echo $formText_CpiAdjustmentFactor_output;?></b></div>
	                                                            <?php } ?>
	                                                        </div>
	                                                        <?php
	                                                        if($o_query && $o_query->num_rows()>0){
	                                                            $subrows = $o_query->result_array();
	                                                            foreach($subrows as $subrow) {
	                                                                $pricePerPiece = $subrow['pricePerPiece'];
	                                                                $articleName = $subrow['articleName'];
	                                                                $s_sql = "SELECT * FROM article WHERE article.id = ?";
	                                                                $o_query = $o_main->db->query($s_sql, array($subrow['articleNumber']));
	                                                                $article = ($o_query ? $o_query->row_array() : array());
	                                                                if($subrow['articleOrIndividualPrice']){
	                                                                    if($article){
	                                                                        $pricePerPiece = $article['price'];
	                                                                        if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
	                                                                            $articleName = $article['name'];
	                                                                        }
	                                                                    }
	                                                                }
	                                                                ?>
	                                                                <div class="subscription-block-children-line">
	                                                                    <div class="subscription-block-children-line-col c1">
	                                                                        <?php echo $articleName; ?>
	                                                                        <?php
	                                                                        if ($article['system_article_type'] == 1):?>
	                                                                            <span class="label label-warning" style="display: inline-block;"><?php echo $formText_PrepaidCommonCost_output; ?></span>
	                                                                        <?php endif;
	                                                                        if ($article['system_article_type'] == 2):?>
	                                                                            <span class="label label-warning" style="display: inline-block;"><?php echo $formText_MarketingContribution_output; ?></span>
	                                                                        <?php endif; ?>

	                                                                    </div>
	                                                                    <div class="subscription-block-children-line-col c2">
	                                                                        <?php echo number_format($subrow['amount'], 2, ",", "");?>
	                                                                    </div>
	                                                                    <div class="subscription-block-children-line-col c2">
	                                                                        <input type="checkbox" disabled readonly <?php echo $subrow['articleOrIndividualPrice'] ? 'checked="checked"' : '' ?>/><label></label>
	                                                                    </div>
	                                                                    <div class="subscription-block-children-line-col c3 rightAligned">
	                                                                        <?php echo number_format($pricePerPiece, 2, ",", "");?>
	                                                                    </div>
	                                                                    <div class="subscription-block-children-line-col c4">
	                                                                        <?php echo number_format($subrow['discountPercent'], 2, ",", "");?>%
	                                                                    </div>
	                                                                    <div class="subscription-block-children-line-col c5 rightAligned">
	                                                                        <?php echo number_format($pricePerPiece*$subrow['amount']*(1 - ($subrow['discountPercent']/ 100)), 2, ",", "");?>
	                                                                    </div>
	                                                        			<?php if ($row['priceAdjustmentType'] == 2){ ?>
	                                                                        <div class="subscription-block-children-line-col c5 rightAligned"><?php echo number_format($subrow['cpiAdjustmentFactor'], 0, ",", ""); ?></div>
	                                                                    <?php } ?>
	                                                                    <!-- <div class="subscription-block-children-line-col c6">
	                                                                        <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscription-line-detail editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>" data-subscription-line-id="<?php echo $subrow['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
	                                                                        <?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-subscription-line-detail editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>" data-subscription-line-id="<?php echo $subrow['id']; ?>"><span class="glyphicon glyphicon-trash"></span></button><?php } ?>
	                                                                    </div> -->
	                                                                </div>
	                                                            <?php } ?>
	                                                    <?php } ?>
	                                                    </div>
	                                                <?php } ?>
													<?php
													 if(intval($subscriptionType['subscription_category']) != 3) {
													?>
		                                                <?php if($moduleAccesslevel > 10) { ?>
		                                                    <!-- <a href="#" class="output-edit-subscription-line-detail" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>">
		                                                        <span class="glyphicon glyphicon-plus"></span>
		                                                        <?php echo $formText_AddSubscriptionLine_output; ?>
		                                                    </a> -->
		                                                    <div class="renewSubscription output-btn " data-subscription-id="<?php echo $row['id']?>" data-customer-id="<?php echo $cid; ?>"><?php echo $formText_RenewSubscription_output;?></div>
		                                                    <div class="clear"></div>
		                                                <?php } ?>
													<?php } ?>
												<?php } ?>
                                            </div>

											<div class="subscription-agreement-block">
                                                <div class="subscription-block-title-col">
                                                    <div><b><?php echo $formText_AgreementEnteredDate_output?>:</b> <?php if($row['agreement_entered_date'] != "" && $row['agreement_entered_date'] != "0000-00-00") echo date("d.m.Y", strtotime($row['agreement_entered_date']));?></div>
                                                    <div><b><?php echo $formText_AgreementTerminatedDate_output?>:</b> <?php if($row['agreement_terminated_date'] != "" && $row['agreement_terminated_date'] != "0000-00-00") echo date("d.m.Y", strtotime($row['agreement_terminated_date']));?></div>
                                                </div>
												<div class="subscription-block-title-col">
													<?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-agreement editBtnIcon" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><span class="glyphicon glyphicon-pencil"></span></button><?php } ?>
												</div>
                                                <div class="clear"></div>
                                            </div>

											<div class="showLessInfo"><span><?php echo $formText_ShowLessInfo_output;?></span> <span class="fas fa-angle-double-up"></span></div>
										</div>
                                    </div>
        						<?php } ?>


                                <?php if($v_customer_accountconfig['hide_stopped_subscriptions'] && $stopped_subscriptions_count > 0){ ?>
                                    <div class="showStoppedSubscriptions">
                                        <?php echo $formText_Show_output." ".$stopped_subscriptions_count." ".$formText_StoppedSubscriptions_output; ?>
                                    </div>
                                <?php } ?>

                                <?php
                                $rows = array();
                                $s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = ? AND content_status > 0";
                                $o_query = $o_main->db->query($s_sql, array($cid));
                                if($o_query){
                                    $deleted_subscriptions_count = $o_query->num_rows();
                                    $rows = $o_query->result_array();
                                }
                                if($deleted_subscriptions_count > 0) {
                                    ?>
                                    <div class="showDeletedSubscriptions">
                                        <?php echo $formText_Show_output." ".$deleted_subscriptions_count." ".$formText_DeletedSubscriptions_output; ?>
                                    </div>
                                    <div class="deletedSubscriptions">
                                        <?php
                                        foreach($rows as $row){
                                            $s_sql = "SELECT * FROM contactperson WHERE id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($row['contactPerson']));
                                            if($o_query && $o_query->num_rows() > 0){
                                                $contactPerson = $o_query->row_array();
                                            }

                                            $s_sql = "SELECT * FROM subscriptiontype WHERE id = ?";
                                            $o_query = $o_main->db->query($s_sql, array($row['subscriptiontype_id']));
                                            $subscriptionType = $o_query ? $o_query->row_array() : array();
                                            ?>
                                            <div class="subscription-block">
                                                <div class="subscription-block-title">
                                                    <div class="subscription-block-title-col c1">
                                                        <b><?php echo $row['subscriptionName'];?></b>
                                                        <div><?php echo $subscriptionType['name'];?></div>
                                                    </div>
                                                    <div class="subscription-block-title-col c2">
                                                        <?php
                                                        if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                                                            echo $formText_StartMonth_Output;
                                                        } else {
                                                            echo $formText_StartDate_output;
                                                        }
                                                        ?>:
                                                        <b><?php echo formatDate($row['startDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
                                                        <br/>
                                                        <?php
                                                        if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                                                            echo $formText_NextRenewalMonth_output;
                                                        } else {
                                                            echo $formText_NextRenewalDate_output;
                                                        }
                                                        ?>:
                                                        <b><?php echo formatDate($row['nextRenewalDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
                                                        <br/>
                                                        <?php if ($row['stoppedDate'] && $row['stoppedDate'] != '0000-00-00' && strtotime($row['stoppedDate']) < strtotime(date("Y-m-d"))) : ?>
                                                            <?php
                                                            if($customer_basisconfig['activateUseMonthOnSubscriptionPeriods']){
                                                                echo $formText_StoppedLastMonth_output;
                                                            } else {
																if(strtotime($row['stoppedDate']) >= strtotime(date("Y-m-d"))){
																	echo $formText_FutureStopped_output;
																} else {
		                                                            echo $formText_StoppedLastDate_output;
																}
                                                            }
                                                            ?>:
                                                            <b><?php echo formatDate($row['stoppedDate'], $customer_basisconfig['activateUseMonthOnSubscriptionPeriods']);?></b>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="subscription-block-title-col c3">
                                                    </div>
                                                    <div class="subscription-block-title-col editLastColumn">
                                                        <div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
                                                        <div class="clear"></div>
                                                       <!--  <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscribtion-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?>
                                                        <?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-subscription editBtnBlank" data-customer-id="<?php echo $cid; ?>" data-subscribtion-id="<?php echo $row['id']; ?>"><?php echo $formText_Delete_Output;?></button><?php } ?> -->
                                                    </div>
                                                </div>
                                                <div class="subscription-block-dropdown">


                                                    <div class="subscription-files">
                                                        <div class="output-filelist">
                                                            <ul>
                                                                <?php
                                                                // $file_sql = "SELECT f.id id FROM sys_filearchive_tag_connection tc LEFT JOIN sys_filearchive_tag_group tg ON tg.id = tc.group_id LEFT JOIN sys_filearchive_file f ON tc.file_id = f.id WHERE tc.content_id = ".$row['id']." AND f.content_status = '0' AND tg.content_table = 'subscriptionmulti'";
                                                                // $find_files = mysql_query($file_sql);

                                                                $current_content_id = $row['id'];
                                                                $content_table = 'subscriptionmulti';
                                                                $s_sql = "SELECT * FROM sys_filearchive_folder WHERE connected_content_table = ? AND connected_content_id = ?";
                                                                $o_query = $o_main->db->query($s_sql, array($content_table, $current_content_id));
                                                                if($o_query && $o_query->num_rows()>0){
                                                                    $folder_data = $o_query->row_array();
                                                                }

                                                                $folder_id = $folder_data['id'];
                                                                $files = array();
                                                                $s_sql = "SELECT * FROM sys_filearchive_file WHERE folder_id = ? AND content_status = '0'";
                                                                $o_query = $o_main->db->query($s_sql, array($folder_id));
                                                                if($o_query && $o_query->num_rows()>0){
                                                                    $files = $o_query->result_array();
                                                                    foreach($files as $file) {
                                                                        $s_sql = "SELECT * FROM sys_filearchive_file_version WHERE file_id = ? AND status = 1 ORDER BY id DESC LIMIT 1";
                                                                        $o_query = $o_main->db->query($s_sql, array($file['id']));
                                                                        if($o_query && $o_query->num_rows()>0){
                                                                            $file_version_data = $o_query->row_array();
                                                                        }
                                                                        $fileInfo = json_decode($file_version_data['file'], true);
                                                                        $fileParts = explode('/',$fileInfo[0][1][0]);
                                                                        $fileName = array_pop($fileParts);
                                                                        $fileParts[] = rawurlencode($fileName);
                                                                        $filePath = implode('/',$fileParts);
                                                                        $fileUrl = $fileInfo[0][1][0];
                                                                        $fileName = $fileInfo[0][0];
                                                                        $fileUrl = "";
                                                                        if(strpos($fileInfo[0][1][0],'uploads/protected/')!==false)
                                                                        {
                                                                            $fileUrl = $extradomaindirroot.'/../'.$filePath.'?caID='.$_GET['caID'].'&table=sys_filearchive_file_version&field=file&ID='.$file_version_data['id'];
                                                                        }
                                                                    ?>
                                                                    <li>
                                                                        <a href="<?php echo $fileUrl; ?>" target="_blank">
                                                                            <span class="glyphicon glyphicon-paperclip"></span> <?php echo $fileName; ?>
                                                                        </a>
                                                                        <span class="fileFolderPath">
                                                                            <span class="glyphicon glyphicon-folder-open"></span> <?php echo getFullFolderPathForFile($file['id'], $o_main); ?>
                                                                        </span>
                                                                      <!--   <?php if($moduleAccesslevel > 110) { ?>
                                                                            <a href="#" class="deleteFile" data-deletefileid="<?php echo $file['id']; ?>">
                                                                                <span class="glyphicon glyphicon-trash"></span>
                                                                            </a>
                                                                        <?php } ?> -->
                                                                    </li>
                                                                    <?php } ?>
                                                                <?php } ?>
                                                            </ul>
                                                        </div>
                                                        <!-- <?php if($moduleAccesslevel > 10) { ?>
                                                            <a href="#" class="addSubscriptionFileBtn" data-subscription-id="<?php echo $row['id']; ?>">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                                <?php echo $formText_AddFile_output; ?>
                                                            </a>
                                                        <?php } ?> -->
                                                    </div>
                                                    <div class="subscription-block-children">

                                                        <?php
                                                        $s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
                                                        $o_query = $o_main->db->query($s_sql, array($row['id']));
                                                        if($o_query && $o_query->num_rows()>0){
                                                            $subrows = $o_query->result_array();
                                                        ?>
                                                            <div class="subscription-block-children-lines">
                                                                <div class="subscription-block-children-lines-label">
                                                                <?php echo $formText_SubscriptionLines_output;?>
                                                                </div>
                                                                <div class="subscription-block-children-line">
                                                                    <div class="subscription-block-children-line-col c1"><b><?php echo $formText_Name_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c2"><b><?php echo $formText_AmountPerPeriod_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c2"><b><?php echo $formText_UsePriceFromArticle_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c3 rightAligned"><b><?php echo $formText_PricePerPiece_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c4"><b><?php echo $formText_Discount_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c5 rightAligned"><b><?php echo $formText_PricePerPeriod_output;?></b></div>
                                                                    <div class="subscription-block-children-line-col c6">&nbsp;</div>
                                                                </div>
                                                                <?php
                                                                foreach($subrows as $subrow) {
                                                                    $pricePerPiece = $subrow['pricePerPiece'];
                                                                    $articleName = $subrow['articleName'];
                                                                    if($subrow['articleOrIndividualPrice']){
                                                                        $s_sql = "SELECT * FROM article WHERE article.id = ?";
                                                                        $o_query = $o_main->db->query($s_sql, array($subrow['articleNumber']));
                                                                        $article = ($o_query ? $o_query->row_array() : array());
                                                                        if($article){
                                                                            $pricePerPiece = $article['price'];
                                                                            if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
                                                                                $articleName = $article['name'];
                                                                            }
                                                                        }
                                                                    }
                                                                    ?>
                                                                    <div class="subscription-block-children-line">
                                                                        <div class="subscription-block-children-line-col c1">
                                                                            <?php echo $articleName; ?>
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c2">
                                                                            <?php echo number_format($subrow['amount'], 2, ",", "");?>
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c2">
                                                                            <input type="checkbox" disabled readonly <?php echo $subrow['articleOrIndividualPrice'] ? 'checked="checked"' : '' ?>><label></label>
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c3">
                                                                            <?php echo number_format($pricePerPiece, 2, ",", "");?>
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c4">
                                                                            <?php echo number_format($subrow['discountPercent'], 2, ",", "");?>%
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c5">
                                                                            <?php echo number_format($pricePerPiece*$subrow['amount']*(1 - ($subrow['discountPercent']/ 100)), 2, ",", "");?>
                                                                        </div>
                                                                        <div class="subscription-block-children-line-col c6">
                                                                           <!--  <?php if($moduleAccesslevel > 10) { ?><button class="output-btn small output-edit-subscription-line-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>" data-subscription-line-id="<?php echo $subrow['id']; ?>"><?php echo $formText_Edit_Output;?></button><?php } ?>
                                                                            <?php if($moduleAccesslevel > 110) { ?><button class="output-btn small output-delete-subscription-line-detail editBtnBlank" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>" data-subscription-line-id="<?php echo $subrow['id']; ?>"><?php echo $formText_Delete_Output;?></button><?php } ?> -->
                                                                        </div>
                                                                    </div>
                                                                <?php
                                                                } ?>
                                                            </div>
                                                        <?php
                                                        } ?>
                                                        <!-- <?php if($moduleAccesslevel > 10) { ?>
                                                            <a href="#" class="output-edit-subscription-line-detail" data-customer-id="<?php echo $cid; ?>" data-subscription-id="<?php echo $row['id']; ?>">
                                                                <span class="glyphicon glyphicon-plus"></span>
                                                                <?php echo $formText_AddSubscriptionLine_output; ?>
                                                            </a>
                                                        <?php } ?> -->
                                                    </div>
                                                </div>
                                            </div>
                                        <?php
                                        } ?>
                                    </div>
                                    <?php
                                }
                                ?>
                                </div>
        					</div>

							<?php
								if(isset($v_customer_accountconfig['activate_account_connection']) && 1 == $v_customer_accountconfig['activate_account_connection'])
								{
									$s_buffer = '';
									$v_param = array
									(
										'PARTNER_ID'=>$v_customer_accountconfig['getynet_partner_id'],
										'PARTNER_PWD'=>$v_customer_accountconfig['getynet_partner_pw'],
										'COMPANY_ID'=>$customerData['getynet_customer_id'],
										'SHOW_ALL_PARTNER_ACCOUNTS'=>$v_customer_accountconfig['getynet_show_all_partner_accounts'],
									);
									$s_request = APIconnectorAccount("accountlistbypartneridget", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
									$l_counter = 0;
									$v_accounts = json_decode($s_request, TRUE);
									foreach($v_accounts as $s_key => $v_account)
									{
										if(0 != $v_account['status']) continue;

										$s_sql = "SELECT * FROM subscriptionmulti_accounts WHERE accountname = '".$o_main->db->escape_str($v_account['accountname'])."' AND content_status = 0";
										$o_find = $o_main->db->query($s_sql);
										if($o_find && $o_find->num_rows()==0)
										{
											$l_counter++;
											$s_buffer .= '<div>'.$v_account['accountname'].'</div>';
										} else {
											$s_sql = "SELECT s.* FROM subscriptionmulti_accounts AS sa JOIN subscriptionmulti AS s ON s.id = sa.subscriptionmulti_id AND sa.content_status = 0 WHERE sa.accountname = '".$o_main->db->escape_str($v_account['accountname'])."' AND s.startDate <= CURDATE() AND (s.stoppedDate IS NULL OR s.stoppedDate = '0000-00-00' OR s.stoppedDate > CURDATE())";
											$o_find = $o_main->db->query($s_sql);
											if($o_find && $o_find->num_rows()==0)
											{
												$l_counter++;
												$s_buffer .= '<div>'.$v_account['accountname'].' - <b>'.$formText_StoppedSubscription_Output.'</b></div>';
											}
										}
									}
									if('' != $s_buffer)
									{
										?>
										<div class="p_contentBlock">
											<div class="p_contentBlockTitle dropdown_content_show show_subscription">
												<?php echo $formText_NotConnectedAccountsOrConnectedWithStoppedSubscription_Output;?>
												<span class="badge">
													<?php echo $l_counter; ?>
												</span>
												<div class="showArrow"><span class="glyphicon glyphicon-triangle-right"></span></div>
											</div>
											<div class="p_contentBlockContent dropdown_content"><?php echo $s_buffer;?></div>
										</div>
										<?php
									}
								}
								?>
                        <?php } ?>
                    <?php } ?>

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
            var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
                fw_load_ajax(redirectUrl, '', true);
			} else {
            	output_reload_page($(this).data("subscription-id"));
            }
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
	$(".subunitFilter").change(function(e){
		e.preventDefault();
		loadView("details", {cid:"<?php echo $cid;?>", subunit_filter: $(this).val()});
	})
	$(".output-edit-subunit").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			cid: $(this).data("cid"),
		 	customerId: '<?php echo $cid;?>'
		};
		ajaxCall('edit_subunit', data, function(obj) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_activity_categories").off("click").on("click", function(e){
		var data = { customerId: $(this).data("customer-id")};
		ajaxCall('edit_activity_categories', data, function(obj) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".editMainContact").off("click").on("click", function(e){
		var data = { customerId: $(this).data("customer-id")};
		ajaxCall('editMemberMainContact', data, function(obj) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".send_link").off("click").on("click", function(){
		var data = { customerId: $(this).data("customer-id"), send_link: 1, output_form_submit: 1};
		ajaxCall('sendMemberLink', data, function(obj) {
			if(obj.error != undefined) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.error);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			} else {
				loadView("details", {cid: '<?php echo $cid;?>'});
			}
		});
	})

	$(".history_view_more").off("click").on("click", function(){
		var data = { history_id: $(this).data("id")};
	    ajaxCall('view_historical', data, function(obj) {
	        $('#popupeditboxcontent').html('');
	        $('#popupeditboxcontent').html(obj.html);
	        out_popup = $('#popupeditbox').bPopup(out_popup_options);
	        $("#popupeditbox:not(.opened)").remove();
	    });
	})
    $(".subBlockTitle").off("click").on("click", function(){
        $(this).parents(".uninvoicedProjectBlock").find(".subBlockContent").slideToggle();
    })
    $(".readFullMessage").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            customerId: '<?php echo $cid?>',
            messageId: $(this).data('message-id')
        };
        ajaxCall('view_message_center', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
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
    $(".customer-folder .name_wrapper").off("click").on("click", function(){
        var folder_id = $(this).parents(".customer-folder").data("folder-id");
        $(".folder-list"+folder_id).toggle();

        if($(".folder-list"+folder_id).is(":visible")){
            $(this).find(".fa-folder").addClass("fa-folder-open").removeClass("fa-folder");
        } else {
            $(this).find(".fa-folder-open").addClass("fa-folder").removeClass("fa-folder-open");
        }
    })
    //setTimeout(output_load_contactpersons, 100);
    // Filter by customer name
    $('.backToList').on('click', function(e) {
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter; ?>',
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: '<?php echo $search_filter; ?>',
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    $(document).off('mouseenter mouseleave', '.output-access-changer')
    .on('mouseenter', '.output-access-changer', function(){
        $(this).find(".output-access-dropdown").show();
    }).on('mouseleave', '.output-access-changer', function(){
        $(this).find(".output-access-dropdown").hide();
    });
    $(".showDeletedOrders").unbind("click").bind("click", function(){
        $(".deletedOrders").slideToggle();
    })
    $(".showDeletedCollectingOrders").unbind("click").bind("click", function(){
        $(".deletedCollectingOrders").slideToggle();
    })
    $(".showDeletedSubscriptions").unbind("click").bind("click", function(){
        $(".deletedSubscriptions").slideToggle();
    })
    $(".p_pageDetails .addMembershipConnection").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            customerId: <?php echo $cid;?>
        };
        ajaxCall('editMembershipConnection', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$('.signCustomFile').off('click').on('click', function(e) {
		e.preventDefault();
		var data = {
			cid: $(this).data('id')
		};
		ajaxCall('send_to_signant', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
	<?php if(1 == $accessElementAllow_SendFilesToSignant) { ?>
	/**
    *** Cancel document
    **/
    $('.output-cancel-signant-document').off('click').on('click', function(event) {
        event.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var _this = this;
			bootbox.confirm({
				message: $(_this).data('cancel-msg'),
				buttons: {confirm:{label:'<?php echo $formText_Yes_Output;?>'},cancel:{label:'<?php echo $formText_No_Output;?>'}},
				callback: function(result){
					fw_click_instance = false;
					if(result)
					{
						ajaxCall('cancel_document', { id: $(_this).data('id') }, function(json) {
							if(json.error !== undefined)
							{
								$.each(json.error, function(index, value){
									var _type = Array("error");
									if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
									fw_info_message_add(_type[0], value);
									$('#popupeditbox span.button.b-close').trigger('click');
								});
								fw_info_message_show();
							} else {
								output_reload_page();
							}
						});
					}
				}
			});
		}
    });
	<?php } ?>
	setTimeout(integration_signant_status_check, 800);

	function integration_signant_status_check()
	{
		var handle = $('.signant-status.load');
		if(handle.length > 0)
		{
			var obj = $(handle).get(0);
			var data = {
				output_form_submit: 1,
				id: $(obj).data('id'),
			};
			ajaxCall('sync_document', data, function(json) {
				if(json.data !== undefined)
				{
					if(json.data.download_url)
					{
						$(obj).closest('tr').find('a.download-url').attr('href', json.data.download_url);
					}
					if(json.data.s > 1)
					{
						$(obj).closest('tr').find('a.output-cancel-document').remove();
					}
					$(obj).replaceWith(json.data.sign_status);
				}
				integration_signant_status_check();
			}, false);
		}
	}

    rebindMembershipButtons();
    function rebindMembershipButtons(){
        $(".p_pageDetails .removeMembershipConnectionSelect").off("click").on("click", function(e){
            var id = $(this).data("connection-id");
            if(parseInt(id) > 0){
                e.preventDefault();
                var self = $(this);

                bootbox.confirm('<?php echo $formText_ConfirmDeletingConnection_output; ?>', function(result) {
                    if (result) {
                        var data = {
                            customerId: <?php echo $cid;?>,
                            connectionId: id,
                            output_form_submit: 1,
                            action: 'deleteConnection'
                        };
                        ajaxCall('editMembershipConnection', data, function(json) {
                            if(json.data == "success"){
                                self.parents(".membershipConnectionRow").remove();
                            }
                        });
                    }
                });
            } else {
                $(this).parents(".membershipConnectionRow").remove();
            }
        })
        // $("3 .membershipConnectionSelect").change(function(){
        //     var self = $(this);
        //     var id = self.data("connection-id");
        //     var data = {
        //         customerId: <?php echo $cid;?>,
        //         connectionId: id,
        //         membershipId: self.val(),
        //         action: 'updateConnection'
        //     };
        //     ajaxCall('editMembershipConnection', data, function(json) {
        //         if(json.data > 0){
        //             self.data("connection-id", json.data);
        //             self.parents(".membershipConnectionRow").find(".removeMembershipConnectionSelect").data("connection-id", json.data);
        //         } else {
        //             if(self.find("option[selected]").length > 0){
        //                 self.find("option[selected]").prop('selected', true);
        //             } else {
        //                 self.val("");
        //             }
        //         }
        //     });
        // })
    }
    var loadingCustomer = false;
    var $input = $('.employeeSearchInput');
    var customer_search_value;
    $input.on('focusin', function () {
        searchCustomerSuggestions();
        $("#p_container").unbind("click").bind("click", function (ev) {
            if($(ev.target).parents(".employeeSearch").length == 0){
                $(".employeeSearchSuggestions").hide();
            }
        });
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        searchCustomerSuggestions();
    });
    //on keydown, clear the countdown
    $input.on('keydown', function () {
        searchCustomerSuggestions();
    });
    function searchCustomerSuggestions (){
        if(!loadingCustomer) {
            if(customer_search_value != $(".employeeSearchInput").val()) {
                loadingCustomer = true;
                customer_search_value = $(".employeeSearchInput").val();
                $('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();
                var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value};
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers_suggestions";?>',
                    data: _data,
                    success: function(obj){
                        loadingCustomer = false;
                        $('.employeeSearch .employeeSearchSuggestions').html('');
                        $('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
                        searchCustomerSuggestions();
                    }
                }).fail(function(){
                    loadingCustomer = false;
                })
            }
        }
    }

    var typingTimer;                //timer identifier
    var doneTypingInterval = 400;  //time in ms, 5 second for example
    var $input = $('.contactPersonSearchInput');

    $input.on('focusin', function () {
        // typingTimer = setTimeout(doneTypingSearchContact, doneTypingInterval);
    })
    //on keyup, start the countdown
    $input.on('keyup', function () {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(doneTypingSearchContact, doneTypingInterval);
    });

    //on keydown, clear the countdown
    $input.on('keydown', function () {
        clearTimeout(typingTimer);
    });

    //user is "finished typing," do something
    function doneTypingSearchContact () {
        if($(".contactPersonSearchInput").val().length > 2 || $(".contactPersonSearchInput").val().length == 0){
            var data = { fwajax: 1, fw_nocss: 1, customerId: <?php echo $cid;?>, search: $(".contactPersonSearchInput").val() };

            ajaxCall('contactpersons_list', data, function(json) {
                $("#output-contactpersons .contactpersonTableWrapper").html(json.html).slideDown();
            });
        }
    }

	<?php if($moduleAccesslevel > 10) { ?>
    $(".output-edit-customer-groups").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editCustomerGroups', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

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

    $(".syncCustomerHook").on('click', function(e){
        e.preventDefault();
        var data = {
            cid: $(this).data('customer-id')
        };
        ajaxCall('syncCustomerHook', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".output-merge-customer").on('click', function(e){
        e.preventDefault();
        var data = {
            cid: $(this).data('customer-id')
        };
        ajaxCall('merge_customer', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

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
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editCustomerDetail', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});

	$(".output-edit-customer-creditor-detail").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editCustomerCreditorDetail', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});


    $('.output-contactperson-edit-lock-access').on('mouseenter', function(e) {
        $(this).find('.output-access-dropdown').show();
    });


    $('.output-contactperson-edit-lock-access').on('mouseleave', function(e) {
        $(this).find('.output-access-dropdown').hide();
    });

    $(".output-edit-subscribtion-detail").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscribtionDetail', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-renewal-date").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionRenewalDates', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-price-adjustment").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPriceAdjustment', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertyturnover").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionTurnover', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".output-edit-turnoveryear").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id'),
            turnoverId: $(this).data('turnover-id')
        };
        ajaxCall('editSubscriptionTurnoverYear', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".output-delete-turnoveryear").off("click").on("click", function(e){
        e.preventDefault();
		var self = $(this);

		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				var data = {
                    subscribtionId: self.data('subscribtion-id'),
                    customerId: self.data('customer-id'),
                    turnoverId: self.data('turnover-id'),
					action: 'delete'
				};
				ajaxCall('editSubscriptionTurnoverYear', data, function(json) {
    	            output_reload_page();
				});
			}
		});
    })
    $(".showMoreTurnOver").off("click").on("click", function(e){
        $(this).parents(".subscription-turnover-list").find(".turnoverHidden").removeClass();
        $(this).hide();
    })
    $(".output-edit-subscribtion-guarantee").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionGuarantee', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertyoption").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyOption', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertymarketing").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyMarketing', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertytaxmustbecharged").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyTaxMustBeCharged', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertyseparateprovision").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertySeparateProvision', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertybreakcloses").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyBreakCloses', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertyresignation").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyResignation', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-propertyadministrationsurcharge").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionPropertyAdministrationSurcharge', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });


    $(".output-edit-subscribtion-comment").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionComment', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-edit-subscribtion-seperate").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionSeperateInvoice', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".output-edit-subscribtion-agreement").on("click", function(e){
		e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscribtion-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionAgreementDates', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
    $(".output-edit-subscription-line-detail").on('click', function(e){
        e.preventDefault();
        var data = {
            subscriptionLineId: $(this).data('subscription-line-id'),
            subscriptionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscriptionLineDetailNew', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

	$(".output-delete-subscription-line-detail").on('click', function(e){
		e.preventDefault();
		var self = $(this);

		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				var data = {
					subscriptionLineId: self.data('subscription-line-id'),
					action: 'deleteSubscriptionLine'
				};
				ajaxCall('editSubscriptionLineDetailNew', data, function(json) {
                    <?php if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) { ?>
                        loadView("details", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>"});
                    <?php  } else {?>
                        loadView("details", {cid:"<?php echo $cid;?>"});
                    <?php } ?>
				});
			}
		});
	});
    $(".edit_vat_free_area").on('click', function(e){
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('editSubscribtionVatFreeArea', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".renewSubscription").on('click', function(e){
        e.preventDefault();
        var data = {
            subscriptionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id')
        };
        ajaxCall('subscriptionRenewal', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".output-delete-subscription").on('click', function(e){
        e.preventDefault();
        var self = $(this);

        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                var data = {
                    subscribtionId: self.data('subscribtion-id'),
                    action: 'deleteSubscription'
                };
                ajaxCall('editSubscribtionDetail', data, function(json) {
                    <?php if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) { ?>
                        if(json.html == ""){
                            loadView("details", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>"});
                        } else {
                            $('#popupeditboxcontent').html('');
                            $('#popupeditboxcontent').html(json.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                        }
                    <?php  } else {?>
                        if(json.html == ""){
                            loadView("details", {cid:"<?php echo $cid;?>"});
                        } else {
                            $('#popupeditboxcontent').html('');
                            $('#popupeditboxcontent').html(json.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                        }
                    <?php } ?>
                });
            }
        });
    });
    $(".output-edit-collectingorder").unbind("click").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: '<?php echo $cid;?>',
            collectingorderId: $(this).data('project-id'),
        };
        ajaxCall('editCollectingOrder', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        });
    });
    $(".output-edit-offer").unbind("click").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: '<?php echo $cid;?>',
            offerId: $(this).data('project-id'),
        };
        ajaxCall('editOffer', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        });
    });
    $(".output-delete-offer").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            offerId: self.data('project-id'),
            action: 'deleteOrder'
        };
        bootbox.confirm('<?php echo $formText_ConfirmMovingToTrash_output; ?>', function(result) {
            if (result) {
                ajaxCall('editOffer', data, function(json) {
                    output_reload_page();
                });
            }
        });
    });
    $(".showOldOffers").off("click").on("click", function(){
        $(this).parents(".offer_pdfs").find(".oldOffers").toggle();
    })
    $(".output-delete-offerpdf").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            offerPdfId: self.data('offerpdf-id'),
            action: 'deleteOfferPdf'
        };
        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                ajaxCall('editOffer', data, function(json) {
                    output_reload_page();
                });
            }
        });
    });
    $(".output-send-offerpdf").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            offerpdf: $(this).data('offerpdf-id'),
        };
        ajaxCall('sendOfferEmail', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        });
    })
    $(".output-delete-orderconfirmation").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            offerPdfId: self.data('offerpdf-id'),
            action: 'deleteConfirmationPdf'
        };
        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                ajaxCall('editCollectingOrderConfirmation', data, function(json) {
                    output_reload_page();
                });
            }
        });
    });

    $(".output-send-orderconfirmationpdf").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            collectingOrderPdfId: $(this).data('offerpdf-id'),
        };
        ajaxCall('sendOrderConfirmationEmail', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        });
    })
    $(".output-delete-collectingorder").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            collectingorderId: self.data('project-id'),
            action: 'deleteOrderByStatus'
        };
        bootbox.confirm('<?php echo $formText_ConfirmMovingToTrash_output; ?>', function(result) {
            if (result) {
                ajaxCall('editCollectingOrder', data, function(json) {
                    if(json.data == "confirmation"){
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                    } else {
                        <?php if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) { ?>
                            loadView("details", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>"});
                        <?php  } else {?>
                            loadView("details", {cid:"<?php echo $cid;?>"});
                        <?php } ?>
                    }
                });
            }
        });
    });

    $(".output-delete-collectingorder-real").unbind("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            collectingorderId: self.data('project-id'),
            action: 'deleteOrder'
        };
        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                ajaxCall('editCollectingOrder', data, function(json) {
                    if(json.data == "confirmation"){
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                    } else {
                        <?php if(isset($_POST['projectId']) && isset($_POST['mainProjectId'])) { ?>
                            loadView("details", {cid:"<?php echo $_POST['mainProjectId'];?>", "subprojectId": "<?php echo $_POST['projectId'];?>"});
                        <?php } else {  ?>
                            loadView("details", {cid:"<?php echo $cid;?>"});
                        <?php } ?>
                    }
                });
            }
        });
    });

    $(".createProjectFromOffer").unbind("click").on('click', function(e){
        e.preventDefault();
        var offerId = $(this).data('offer-id');
        if(offerId > 0) {
            var data = {
                offerId: offerId
            };
            ajaxCall({module_file:'editProject', module_name: 'Project2', module_folder: 'output'}, data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        }
    })

    $(".createInvoice").unbind("click").on('click', function(e){
        e.preventDefault();
        var collectingorderId = $(this).data('collectingorder-id');
		<?php if(!$customerData['do_not_check_for_ehf']) { ?>
			var currentStatus = '<?php echo $customerData['invoiceBy'] + 1;?>'
			ajaxCall({module_file:'check_ehf&abortable=1'}, { customer_id: '<?php echo $customerData['id'];?>' }, function(json) {
				if(json.data >= 1 && currentStatus != json.data)
				{
					var _msg = '';
					if(1 == json.data) _msg = '<?php echo $formText_CustomerCanOnlyReceiveInvoiceByPaper_Output;?>';
					if(2 == json.data) _msg = '<?php echo $formText_CustomerCanOnlyReceiveInvoiceByEmail_Output;?>';
					if(3 == json.data) _msg = '<?php echo $formText_CustomerCanReceiveEhfInvoices_Output;?>';

					bootbox.confirm(_msg + '. <?php echo $formText_UpdateBeforeCreatingInvoice_Output; ?>?', function(result) {
						if (result) {
							ajaxCall({module_file:'check_ehf&abortable=1'}, { customer_id: '<?php echo $customerData['id'];?>', output_form_submit: 1 }, function(json) {
								if(1 == json.data)
								{
									output_create_invoice(collectingorderId);
								}
							});
						} else {
							output_create_invoice(collectingorderId);
						}
					});
				} else {
					output_create_invoice(collectingorderId);
				}
			}, false);
		<?php } else { ?>
			output_create_invoice(collectingorderId);
		<?php } ?>
    });
	function output_create_invoice(collectingorderId)
	{
		var data = {
            collectingorderId: collectingorderId,
            customerId: '<?php echo $cid;?>'
        };
		ajaxCall('make_invoice_collecting', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			$(window).resize();
		});
	}
    $(".editOrderReference").on('click', function(e){
        e.preventDefault();
        var data = {
            collectingorderId: $(this).data('collectingorder-id')
        };
        ajaxCall('edit_order_reference', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".createInvoiceDummy").off("click").on("click", function(){
		var data = {
            collectingOrderId: $(this).data('collectingorder-id'),
            customerId: '<?php echo $cid;?>'
        };
		ajaxCall('make_invoice_dummy', data, function(json) {
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			$(window).resize();
		});
	})
    $(".editDeliveryInfo").on('click', function(e){
        e.preventDefault();
        var data = {
            collectingorderId: $(this).data('collectingorder-id')
        };
        ajaxCall('edit_delivery', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".editDeliveryDate").on('click', function(e){
        e.preventDefault();
        var data = {
            collectingorderId: $(this).data('collectingorder-id')
        };
        ajaxCall('edit_delivery_date', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".attachFiles").off("click").on('click', function(e){
        e.preventDefault();
        var data = {
            collectingorderId: $(this).data('collectingorder-id')
        };
        ajaxCall('attachFilesToCollectingOrder', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".editSubscriptionOrderReference").on('click', function(e){
        e.preventDefault();
        var data = {
            subscriptionmultiId: $(this).data('subscriptionmulti-id')
        };
        ajaxCall('edit_subscription_order_reference', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".editSubscriptionDeliveryInfo").on('click', function(e){
        e.preventDefault();
        var data = {
            subscriptionmultiId: $(this).data('subscriptionmulti-id')
        };
        ajaxCall('edit_subscription_delivery', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".editSubscriptionDeliveryDate").on('click', function(e){
        e.preventDefault();
        var data = {
            subscriptionmultiId: $(this).data('subscriptionmulti-id')
        };
        ajaxCall('edit_subscription_delivery_date', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-delete-attachedfile").off("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            uid: $(this).data('uid'),
            collectingorderId: $(this).data("collectingorder-id"),
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteAttachedFile_output; ?>', function(result) {
            if (result) {
                ajaxCall('deleteAttachedFile', data, function(json) {
                    // self.closest('tr').remove();
                    output_reload_page();
                });
            }
        });
    });

    $(".approvedForBatchInvoicing").unbind("click").bind("click", function(){
        var isChecked = $(this).is(":checked");
        var projectId = $(this).data("projectid");
        if(projectId){
	        var checked = 0;
	        if(isChecked == true) {
	            checked = 1;
	        }
	        var data = {
	            collectingorderId: projectId,
	            checked: checked,
	            action: "updateApprovedForBatchInvoicing"
	        };
	        ajaxCall('editCollectingOrder', data, function(json) {
	            output_reload_page();
	        });
	    }
    })
    $(".seperatedInvoice").unbind("click").bind("click", function(){
        var isChecked = $(this).is(":checked");
        var projectId = $(this).data("projectid");
        if(projectId){
	        var checked = 0;
	        if(isChecked == true) {
	            checked = 1;
	        }
	        var data = {
	            collectingorderId: projectId,
	            checked: checked,
	            action: "updateSeperatedInvoice"
	        };
	        ajaxCall('editCollectingOrder', data, function(json) {
	            output_reload_page();
	        });
	    }
    })

    $(".attachFilesToOffer").off("click").on('click', function(e){
        e.preventDefault();
        var data = {
            offerId: $(this).data('offer-id')
        };
        ajaxCall('attachFilesToOffer', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-delete-attachedfile-offer").off("click").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            uid: $(this).data('uid'),
            offerId: $(this).data("offer-id"),
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteAttachedFile_output; ?>', function(result) {
            if (result) {
                ajaxCall('deleteAttachedFileOffer', data, function(json) {
                    // self.closest('tr').remove();
                    output_reload_page();
                });
            }
        });
    });
    $(".edit_files_attached_to_offers").off("click").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: '<?php echo $cid;?>'
        };
        ajaxCall('attachFilesToOfferAll', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".edit_files_attached_to_confirmation").off("click").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: '<?php echo $cid;?>'
        };
        ajaxCall('attachFilesToConfirmationAll', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });


    $(".createOrderConfirmation").unbind("click").bind("click", function(){
        var projectId = $(this).data("projectid");
        if(projectId){
            var data = {
                collectingorderId: projectId
            };
            ajaxCall('editCollectingOrderConfirmation', data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        }
    })
    $(".output-delete-order").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            orderId: self.data('order-id'),
            action: 'deleteOrder'
        };
        if(self.data("has-orderlines") === 1){
            ajaxCall('checkOrder', data, function(json) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        } else {
            bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
                if (result) {
                    ajaxCall('editOrder', data, function(json) {
                        // self.closest('tr').remove();
                        loadView("details", {cid:"<?php echo $customerData['id']?>"});
                    });
                }
            });
        }
    });
    $('.assignOfficeBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            subscriptionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id'),
        };
        ajaxCall('assignOffice', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $('.deleteOfficeConnectionBtn').on('click', function(e) {
        e.preventDefault();
        var self = $(this);
        bootbox.confirm({
            message: "<?php echo $formText_ConfirmDelete_output; ?>",
            callback: function (result) {
                if (result) {
                    var data = {
                        connectionId: self.data('connection-id')
                    };
                    ajaxCall('assignOffice', data, function() {
                        output_reload_page();
                    });
                }
            }
        });
    });
    $(".open-connectToProject").unbind("click").bind("click", function(){
        $(this).next(".connectToProject").slideToggle();
    })
    $(".connectToProject").click(function(){
        var data = {
            customerId: $(this).data('customer-id'),
            subscribtionId: $(this).data('subscription-id'),
            connectBy: $(this).data('value'),
            action: 'updateSubscriptionConnectType'
        };
        ajaxCall('editSubscriptionProject', data, function(json) {
            output_reload_page();
        });
    })
    $(".assignRentalUnit").click(function(e){
        e.preventDefault();
        var data = {
            subscriptionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id'),
        };
        ajaxCall('assignRentalUnit', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })

    $('.connectProjectBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            subscribtionId: $(this).data('subscription-id'),
            customerId: $(this).data('customer-id'),
        };
        ajaxCall('editSubscriptionProject', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $('.deleteProjectConnectionBtn').on('click', function(e) {
        e.preventDefault();
        var self = $(this);
        bootbox.confirm({
            message: "<?php echo $formText_ConfirmDelete_output; ?>",
            callback: function (result) {
                if (result) {
                    var data = {
                        subscribtionId: self.data('subscription-id'),
                        action: 'deleteProject'
                    };
                    ajaxCall('editSubscriptionProject', data, function() {
                        output_reload_page();
                    });
                }
            }
        });
    });

    $(".output-edit-external-customer-id").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id'),
            cid: $(this).data('cid')
        };
        ajaxCall('editExternalCustomerId', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".output-delete-external-customer-id").on('click', function(e){
        e.preventDefault();
        var cid = $(this).data('cid');

        bootbox.confirm("<?php echo $formText_ConfirmDeleteFile; ?>", function(result) {
          if(result) {
              var data = {
                  cid: cid,
                  output_delete: 1
              };
              ajaxCall('editExternalCustomerId', data, function(json) {
                output_reload_page();
              });
            }
        });
    });
	$(".output-edit-previous-sys-id").on('click', function(e){
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id')
        };
        ajaxCall('edit_customer_previous_sys_id', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".output-show-customer-transactions").on('click', function(e){
        e.preventDefault();
        var data = {
            customerCode: $(this).data('customer-code'),
            externalOwnercompanyCode: $(this).data('external-ownercompany-code')
        };
        ajaxCall('showCustomerTransactions', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $('#output-change-transaction-ownercompany').on('change', function(e) {
        var data = {
            'ownercompany_id': $(this).val(),
            'customer_id': $(this).data('customer-id')
        };

        ajaxCall('getTransactions', data, function(json) {
            $('.transactions_content').html(json.html);
        });
    });

	$("#output-add-getynet-connection").on('click', function(e){
		e.preventDefault();
        var data = {
            customer_id: $(this).data('customer-id')
        };
        ajaxCall('edit_getynet_company', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$("#output-add-getynet-account").on('click', function(e){
		e.preventDefault();
        if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			var data = {
				customer_id: $(this).data('customer-id')
			};
			ajaxCall('add_getynet_account', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_click_instance = false;
				fw_loading_end();
			});
		}
    });
	$(".output-grant-getynet-access").on('click', function(e){
		e.preventDefault();
        if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			var data = {
				customer_id: $(this).data('customer-id')
			};
			if($(this).data('sysadmin')) data.sysadmin = 1;
			if($(this).data('developer')) data.developer = 1;
			ajaxCall('grant_getynet_access', data, function(json) {
				$('#popupeditboxcontent').html('').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_click_instance = false;
				fw_loading_end();
			});
		}
    });
	$("#output-show-getynet-accounts").on('click', function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			var data = {
				customer_id: $(this).data('customer-id')
			};
			ajaxCall('get_getynet_accounts', data, function(json) {
				$('#popupeditboxcontent').html('').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
				fw_click_instance = false;
				fw_loading_end();
			});
		}
    });

	$('#output-add-files').on('click', function(e){
		output_edit_files();
	});


    $(".giveAccessInBatch").on('click', function(e){
        e.preventDefault();
        var data = {
            inBatch: 1,
            customerId: '<?php echo $cid;?>'
        };
        ajaxCall('send_invitation_preview', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$('.output-filelist li .deleteFile').on('click', function(e) {
		e.preventDefault();
		var self = $(this);
		bootbox.confirm("<?php echo $formText_ConfirmDeleteFile; ?>: " + self.closest('li').find('a').first().text(), function(result) {
		  if(result) {
				output_delete_file('<?php echo ($_GET['cid']); ?>', self.data('deletefileid'));
			}
		});
	});
	$('.output-delete-item').on('click',function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var $_this = $(this);
			bootbox.confirm({
				message:$_this.attr("data-delete-msg"),
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{
						$.ajax({
							cache: false,
							type: 'POST',
							dataType: 'json',
							data: {fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', output_delete: 1},
							url: $_this.data('url'),
							success: function(data){
								if(data.error !== undefined)
								{
									fw_info_message_empty();
									$.each(data.error, function(index, value){
										var _type = Array("error");
										if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
										fw_info_message_add(_type[0], value);
									});
									fw_info_message_show();
									fw_loading_end();
								} else {
									fw_load_ajax(data.redirect_url,'',true);
								}
							}
						});
					}
					fw_click_instance = false;
				}
			});
		}
	});

	$(".deleteFileNew").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: $(this).data('subscriptionmultifileid'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('deleteFile', data, function(json) {
					// self.closest('tr').remove();
                    output_reload_page();
				});
			}
		});
	});
    $(".change_show_to_customer").off("click").on("click", function(e){
        e.preventDefault();
		var self = $(this);
        var selected = 0;

        if(self.is(":checked")) {
            selected = 1;
        }
		var data = {
			cid: self.val(),
            selected: selected,
            action: "updateShowToCustomer"
		};
        ajaxCall('addSubscriptionFileNew', data, function(json) {
            // self.closest('tr').remove();
            output_reload_page();
        });
    })
    $(".change_show_to_performer").off("click").on("click", function(e){
        e.preventDefault();
		var self = $(this);
        var selected = 0;

        if(self.is(":checked")) {
            selected = 1;
        }
		var data = {
			cid: self.val(),
            selected: selected,
            action: "updateShowToPerformer"
		};
        ajaxCall('addSubscriptionFileNew', data, function(json) {
            // self.closest('tr').remove();
            output_reload_page();
        });
    })

	<?php if($customerData['publicRegisterId'] != '') { ?>
//	window.setTimeout(function(){ajaxCall({module_file:'brreg_check&abortable=1'}, { customer_id: '<?php echo $customerData['id'];?>' }, function(json) {
//		if(json.data == 1) $(".customer_bregg_info").html('<?php echo $formText_CustomerCanBeSynced_Output;?> <a href="#" onClick="output_show_brreg_sync()"><?php echo $formText_ClickHere_Output;?></a>').show();
//	}, false);}, 1000);
	// window.setTimeout(function(){
	// 	ajaxCall({module_file:'check_ehf&abortable=1'}, { customer_id: '<?php echo $customerData['id'];?>' }, function(json) {
	// 		var _msg = '';
	// 		if(1 == json.data) _msg = '<?php echo $formText_CustomerCanOnlyReceiveInvoiceByPaper_Output;?>';
	// 		if(2 == json.data) _msg = '<?php echo $formText_CustomerCanOnlyReceiveInvoiceByEmail_Output;?>';
	// 		if(3 == json.data) _msg = '<?php echo $formText_CustomerCanReceiveEhfInvoices_Output;?>';
	// 		if(json.data >= 1) fw_info_message_add('warn', _msg + ' <a href="#" onClick="output_set_ehf_invoicing()"><?php echo $formText_ClickHereToUpdate_Output;?></a>', true);
	// }, false);
	// }, 1000);
	<?php } ?>

	window.setTimeout(function(){
		ajaxCall('eventparticipant_count', { customer_id: '<?php echo $cid;?>', abortable: 1 }, function(json) {
			$('span.output-event-participant-count').html(json.html);
		}, false);
	}, 1000);

    $(".handle_selfregistered").change(function(){
		var _value = 1;
		if($(this).is(":checked")) {
        	_value = 2;
        }
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editCustomerDetail";?>',
			data: { fwajax: 1, fw_nocss: 1, action: 'handleSelfregistered', output_form_submit: 1, selfregistered: _value, customerId: '<?php echo $customerData['id'];?>' },
			success: function(data){ }
		});
	});
	$("#p_container .rental-unit").change(function(){
		var _value = 0;
		if($(this).is(":checked")) {
        	_value = 1;
        }
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_home";?>',
			data: { fwajax: 1, fw_nocss: 1, block: 2, output_form_submit: 1, rentalUnit: _value, cid: '<?php echo $customerData['id'];?>' },
			success: function(data){ }
		});
	});
	$(".output-access-grant").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id') },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
	$(".output-access-resend").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id'), resend: 1 },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
	$(".output-access-remove").on('click', function(e){
		e.preventDefault();
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			var _this = $(this);
			bootbox.confirm({
				message:$(_this).attr("data-delete-msg"),
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{
						$.ajax({
							cache: false,
							type: 'POST',
							dataType: 'json',
							url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
							data: { fwajax: 1, fw_nocss: 1, cid: $(_this).data('id') },
							success: function(obj){
								$('#popupeditboxcontent').html('');
								$('#popupeditboxcontent').html(obj.html);
								out_popup = $('#popupeditbox').addClass('close-reload').bPopup(out_popup_options);
								$("#popupeditbox:not(.opened)").remove();
							}
						});
					}
					fw_click_instance = false;
				}
			});
		}
	});

    $(".output-edit-customer-articlematrix").on('click', function(e) {
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id')
        };
        ajaxCall('edit_customer_articlematrix', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $('.createFilearchiveFolder').on('click', function(e) {
        var data = {
            cid: '<?php echo $cid; ?>'
        };

        ajaxCall('createFolder', data, function() {
            window.location.reload();
        });
    });


    $('.assignBuildingBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            customerId: $(this).data('customer-id'),
            page: 'detailPage'
        };
        ajaxCall('assignBuilding', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

	$('.addSubscriptionFileNewBtn').on('click', function(e) {
		e.preventDefault();
		var data = {
			cid: $(this).data('subscription-id')
		};
		ajaxCall('addSubscriptionFileNew', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

	$('.addSubscriptionFileBtn').on('click', function(e) {
		e.preventDefault();
		var data = {
			cid: $(this).data('subscription-id'),
			customerId: $(this).data('customer-id'),
            visible: ""+$(this).data("type")
		};
		ajaxCall('addSubscriptionFile', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});

    $('.addCustomerFoldersBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            customer_id: $(this).data('customer-id'),
            cid: $(this).data('folder-id'),
            parent_id: $(this).data('parent-id')
        };
		if($(this).data('subunit-id')) {
			data.subunit_id = $(this).data('subunit-id');
		}
        ajaxCall('addCustomerFolder', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

    $(".deleteFolderCustomer").on('click', function(e){
        e.preventDefault();
        var self = $(this);
        var data = {
            cid: $(this).data('customerfolder-id'),
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
            if (result) {
                ajaxCall('deleteCustomerFolder', data, function(json) {
                    if(json.html != ""){
                        $('#popupeditboxcontent').html('');
                        $('#popupeditboxcontent').html(json.html);
                        out_popup = $('#popupeditbox').bPopup(out_popup_options);
                        $("#popupeditbox:not(.opened)").remove();
                    } else {
                        // self.closest('tr').remove();
                        output_reload_page();
                    }

                });
            }
        });
    });

    $('.addCustomerFilesBtn').on('click', function(e) {
        e.preventDefault();
        var data = {
            cid: $(this).data('customer-id'),
            folder_id: $(this).data('folder-id'),
        };
		if($(this).data('subunit-id')) {
			data.subunit_id = $(this).data('subunit-id');
		}
        ajaxCall('addCustomerFileNew', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

	$(".deleteFileCustomer").on('click', function(e){
		e.preventDefault();
		var self = $(this);
		var data = {
			cid: $(this).data('customerfile-id'),
			output_delete: 1
		};
		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
			if (result) {
				ajaxCall('deleteCustomerFile', data, function(json) {
					// self.closest('tr').remove();
                    output_reload_page();
				});
			}
		});
	});
    $(".preeditBlock .editBtn").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var wrapper = parentRow.find(".selfdefinedFieldValueWrapper");
        var preeditBlock = parentRow.find(".preeditBlock");

        wrapper.show();
        preeditBlock.hide();
    })
    $(".selfdefinedCheckbox").unbind("click").bind("click", function(){
        var isChecked = $(this).is(":checked");
        var selfdefinedFieldId = $(this).data("selfdefinedfieldid");
        var customerId = $(this).data("customerid");
        var parentRow = $(this).parents("tr");
        var editBtn = parentRow.find(".preeditBlock .editBtn");
        var value = "";
        value = parentRow.find(".selfdefinedFieldValue").val();
        var data = {
            selfdefinedFieldId: selfdefinedFieldId,
            customerId: customerId,
            checked: isChecked,
            action: "updateActive",
            value: value
        };
        ajaxCall('update_selfdefinedvalues', data, function(json) {
			if(json.error != undefined){
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.error);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
	            $("#popupeditbox:not(.opened)").remove();
			} else {
	            output_reload_page();
			}
        });
    })
	$('.default_selfdefined_company_id').unbind("change").bind("change", function(){
        var data = {
            output_form_submit: 1,
			set_default_selfdefined_company: 1,
            customer_id: $(this).data("customerid"),
            selfdefined_company_id: $(this).val()
        };
        ajaxCall('add_selfdefined_company', data, function(json) {
            output_reload_page();
        });
    })
    $(".selfdefinedFieldValueWrapper .saveBtn").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var wrapper = parentRow.find(".selfdefinedFieldValueWrapper");
        var preeditBlock = parentRow.find(".preeditBlock");
        updateSelfdefinedFieldValues($(this), "updateText");
    })
    $(".selfdefinedFieldValueWrapper .cancelBtn").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var wrapper = parentRow.find(".selfdefinedFieldValueWrapper");
        var preeditBlock = parentRow.find(".preeditBlock");

        wrapper.hide();
        preeditBlock.show();
    })
    $(".selfdefinedDropdown").change(function(){
        updateSelfdefinedFieldValues($(this), "updateText");
    })
    $(".selfdefinedDropdown2").change(function(){
        updateSelfdefinedFieldValues($(this), "updateDropdowns");
    })
    $(".selfdefinedValueLineChk").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var checkbox = parentRow.find(".selfdefinedCheckbox");
        var isChecked = checkbox.is(":checked");
        var selfdefinedFieldId = checkbox.data("selfdefinedfieldid");
        var customerId = checkbox.data("customerid");
        var lineId = $(this).val();
        var lineChecked = $(this).is(":checked");
        var data = {
            selfdefinedFieldId: selfdefinedFieldId,
            customerId: customerId,
            checked: isChecked,
            action: "updateCheckboxes",
            lineId: lineId,
            lineChecked: lineChecked
        };
        ajaxCall('update_selfdefinedvalues', data, function(json) {
			if(json.error != undefined){
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.error);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
	            $("#popupeditbox:not(.opened)").remove();
			} else {
	            output_reload_page();
			}
        });
    })
    function updateSelfdefinedFieldValues(el, action){
        if(action == undefined){
            var action = 'updateActive';
        }
        var parentRow = el.parents("tr");
        var checkbox = parentRow.find(".selfdefinedCheckbox");
        var selfdefinedFieldId = checkbox.data("selfdefinedfieldid");
        var customerId = checkbox.data("customerid");
        var isChecked = checkbox.is(":checked");
        if(parentRow.find(".selfdefinedDropdown").length > 0){
            var value = parentRow.find(".selfdefinedDropdown").val();
        } else if(parentRow.find("input.selfdefinedFieldValue").length > 0) {
            var value = parentRow.find(".selfdefinedFieldValue").val();
        } else {
            var value = "";
            parentRow.find(".selfdefinedDropdown2").each(function(){
                value += $(this).val()+",";
            })
        }

        var data = {
            selfdefinedFieldId: selfdefinedFieldId,
            customerId: customerId,
            checked: isChecked,
            action: action,
            value: value
        };
        ajaxCall('update_selfdefinedvalues', data, function(json) {
			if(json.error != undefined){
	            $('#popupeditboxcontent').html('');
	            $('#popupeditboxcontent').html(json.error);
	            out_popup = $('#popupeditbox').bPopup(out_popup_options);
				out_popup.addClass("close-reload");
	            $("#popupeditbox:not(.opened)").remove();
			} else {
	            output_reload_page();
			}
        });
    }
    $(".editListDropdown").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var checkbox = parentRow.find(".selfdefinedCheckbox");
        var selfdefinedFieldId = checkbox.data("selfdefinedfieldid");
        var customerId = checkbox.data("customerid");
        var data = {
            selfdefinedFieldId: selfdefinedFieldId,
            customerId: customerId,
        };
        ajaxCall('get_selfdefinedlist_lines', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".resetListDropdown").unbind("click").bind("click", function(){
        updateSelfdefinedFieldValues($(this), "updateText");
    })
    $(".editListCheckboxes").unbind("click").bind("click", function(){
        var parentRow = $(this).parents("tr");
        var checkbox = parentRow.find(".selfdefinedCheckbox");
        var selfdefinedFieldId = checkbox.data("selfdefinedfieldid");
        var customerId = checkbox.data("customerid");
        var data = {
            selfdefinedFieldId: selfdefinedFieldId,
            customerId: customerId,
            checkboxes: true
        };
        ajaxCall('get_selfdefinedlist_lines', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".addOnInvoiceCheckbox").change(function(){
         var data = {
            orderId: $(this).data("order-id"),
            updateAddOnInvoice: true,
            addOnInvoice: $(this).is(":checked")
        };
        ajaxCall('editOrder', data, function(json) {
            output_reload_page();
        });
    })
    // rebindActivities();
    // function rebindActivities(){


    // }

    $("#output-send-email").on('click', function(e){
        output_send_email();
    });
    $(".output-send-email").on('click', function(e){
        output_send_email($(this).data("cid"));
    });
    $('.output-confirm-send-email').on('click',function(e){
        e.preventDefault();
        if(!fw_click_instance)
        {
            fw_click_instance = true;
            var $_this = $(this);
            bootbox.confirm({
                message:$_this.attr("data-send-msg"),
                buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
                callback: function(result){
                    if(result)
                    {

                        fw_loading_start();
                        $.ajax({
                            cache: false,
                            type: 'POST',
                            dataType: 'json',
                            data: {fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', send_message: 1},
                            url: $_this.data('url'),
                            success: function(data){
                                if(data.error !== undefined)
                                {
                                    fw_info_message_empty();
                                    $.each(data.error, function(index, value){
                                        var _type = Array("error");
                                        if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                                        fw_info_message_add(_type[0], value);
                                    });
                                    fw_info_message_show();
                                    fw_loading_end();
                                } else {
                                    fw_load_ajax(data.redirect_url,'',true);
                                }
                            }
                        });
                    }
                    fw_click_instance = false;
                }
            });
        }
    });
	<?php } ?>


    $(".show_invoices").unbind("click").bind("click", function(e){
        var titleBlock = $(this);
        e.preventDefault();
        if($(".invoices_content table").length > 0 ){
            if($(".invoices_content").is(":visible")) {
                $(".invoices_content").slideUp();
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                $(".invoices_content").slideDown();
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }
        } else {
            var data = {
                customerId: $(this).data('customer-id')
            };
            ajaxCall('invoice_list', data, function(json) {
                $(".invoices_content").html(json.html).slideDown();
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            });
        }
    })
    $(".show_ordered_invoices").unbind("click").bind("click", function(e){
        var titleBlock = $(this);
        e.preventDefault();
        if($(".ordered_invoices_content table").length > 0){
            if($(".ordered_invoices_content").is(":visible")) {
                $(".ordered_invoices_content").slideUp();
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
            } else {
                $(".ordered_invoices_content").slideDown();
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            }

        } else {
            var data = {
                customerId: $(this).data('customer-id')
            };
            ajaxCall('ordered_invoice_list', data, function(json) {
                $(".ordered_invoices_content").html(json.html).slideDown();
                titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
            });
        }
    })
    $(".show_emails").unbind("click").bind("click", function(e){
        var titleBlock = $(this);
        e.preventDefault();
        if($(e.target).hasClass("show_emails") || $(e.target).hasClass("showArrow") || $(e.target).parent().hasClass("showArrow")){
            if($(".emails_content .emailContents .email_browser").length > 0 ){
                if($(".emails_content").is(":visible")) {
                    $(".emails_content").slideUp();
                    titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                    titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
                } else {
                    $(".emails_content").slideDown();
                    titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                    titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
                }
            } else {
                $(".emails_content").slideDown();
                $.ajax({
                    cache: false,
                    type: 'POST',
                    dataType: 'json',
                    data: {fwajax: 1, fw_nocss: 1, customerId: '<?php echo $cid;?>'},
                    url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=getEmailsIMAP"; ?>',
                    success: function(data){
                        $('.emailContents').html('');
                        $('.emailContents').html(data.html);
                        $("#emailContentLoading").hide();
                    }
                });
            }
        }
    })
    $(".newEmail").unbind("click").bind("click", function(e){
        var data = {
            customerId: '<?php echo $cid;?>'
        }
        ajaxCall("newEmailIMAP", data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })

    $(".show_prospects").unbind("click").bind("click", function(e){

        var titleBlock = $(this);
        e.preventDefault();
        if(!$(e.target).hasClass("edit-prospect-btn")){
            if($(".prospects_content table").length > 0 ){
                if($(".prospects_content").is(":visible")) {
                    $(".prospects_content").slideUp();
                    titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                    titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
                } else {
                    $(".prospects_content").slideDown();
                    titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                    titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
                }
            } else {
                var data = {
                    customerId: $(this).data('customer-id')
                };
                ajaxCall('prospect_list', data, function(json) {
                    $(".prospects_content").html(json.html).slideDown();
                    titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                    titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
                    rebind_prospect_button();
                });
            }
        }
    })
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
     $(".subscription-block-title").unbind("click").bind("click", function(e){
        var parent = $(this);
        if(!$(e.target).hasClass("editBtnIcon") && $(e.target).parents(".editBtnIcon").length == 0){
            var dropdown = parent.next(".subscription-block-dropdown");
            if(parent.hasClass("autoload")) {
                dropdown.slideDown(0);
                parent.removeClass("autoload");
            } else {
                if(dropdown.is(":visible")) {
                    dropdown.slideUp();
                    parent.removeClass("active");
                    // parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
                    // parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
                } else {
                    dropdown.slideDown();
                    parent.addClass("active");
                    // parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
                    // parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
                }
            }
        }
    })
	$(".subscription-block-dropdown .showLessInfo").unbind("click").bind("click", function(e){
        var dropdown = $(this).parents(".subscription-block-dropdown");
		var parent = dropdown.prev(".subscription-block-title");
		dropdown.slideUp();
		parent.removeClass("active");
	})


     $(".subscription-vatstatements .titleWrapper").unbind("click").bind("click", function(e){
        var parent = $(this).parents(".subscription-vatstatements");
        var dropdown = parent.find(".subscription-vatstatements-dropdown");

        if(dropdown.is(":visible")) {
            dropdown.slideUp();
            parent.removeClass("active");
            parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
            parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
        } else {
            dropdown.slideDown();
            parent.addClass("active");
            parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
            parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
        }
    })
    $(".projectWrapper.hasOrders .projectTitle").off("click").on("click", function(){
       var parent = $(this).parents(".projectWrapper");
       var dropdown = parent.find(".projectOrders");

        if(dropdown.is(":visible")) {
            dropdown.slideUp();
            parent.removeClass("active");
            parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
            parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
        } else {
            dropdown.slideDown();
            parent.addClass("active");
            parent.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
            parent.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
        }

    })
    <?php if($customer_basisconfig['expandInvoices']){ ?>
        $(".show_dropdown.show_invoices").addClass("autoload");
    <?php } ?>
    <?php if($customer_basisconfig['expandInvoicedOrders']){ ?>
        $(".show_dropdown.show_ordered_invoices").addClass("autoload");
    <?php } ?>
    <?php if($customer_basisconfig['expandProspects']){ ?>
        $(".show_dropdown.show_prospects").addClass("autoload");
    <?php } ?>

	$(".activityType").off("click").on("click", function(){
		var data = {
			customerId: '<?php echo $cid;?>',
			typeId: $(this).data("type-id")
		}
		$(".activityType").removeClass("active");
		$(this).addClass("active");

        var titleBlock = $(this).parents(".p_contentBlockTitle");
		ajaxCall("customer_activity_content", data, function(json) {
			$(".customer_activity_content").html(json.html).slideDown();
			titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
			titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");

			rebind_prospect_button();
		});
	})
	$(".activityType.active").click();
    $(".show_dropdown.autoload").click();
    rebind_prospect_button();
    // $(".show_activities").unbind("click").bind("click", function(e){
    //     if(!$(e.target).hasClass("output-edit-activity")){
    //         var titleBlock = $(this);
    //         var parent = $(this).parents("td");
    //         e.preventDefault();
    //         if(parent.find(".activities_content .activityRow").length > 0 ){
    //             if(parent.find(".activities_content").is(":visible")) {
    //                 parent.find(".activities_content").slideUp();
    //                 titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-right");
    //                 titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-bottom");
    //             } else {
    //                 parent.find(".activities_content").slideDown();
    //                 titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
    //                 titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
    //             }
    //         } else {
    //             var data = {
    //                 orderId: $(this).data('order-id'),
    //                 customerId: $(this).data('customer-id')
    //             };
    //             ajaxCall('activity_list', data, function(json) {
    //                 titleBlock.find(".showArrow .glyphicon").removeClass("glyphicon-triangle-right");
    //                 titleBlock.find(".showArrow .glyphicon").addClass("glyphicon-triangle-bottom");
    //                 parent.find(".activities_content").html(json.html).slideDown();
    //                 // rebindActivities();
    //             });
    //         }
    //     }
    // })

    <?php
        $expandBlockIds = explode(",", $_GET['expandContent']);
        if(count($expandBlockIds) > 0){
            foreach($expandBlockIds as $expandBlockId){
                if(intval($expandBlockId) > 0){
                    ?>
                    if(!$('.dropdown_content_show[data-blockid="<?php echo $expandBlockId?>"]').next(".dropdown_content").is(":visible")){
                        $('.dropdown_content_show[data-blockid="<?php echo $expandBlockId?>"]').click();
                    }
                    <?php
                }
            }
        }
    ?>
    $(".project_show_click").off("click").on("click", function(){
        var el = $(this).next(".project_show_item");
        el.slideToggle();
    })
    $(".output-add-project2").off("click").on("click", function(){
        fw_loading_start();
        var type = $(this).data("type");
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=ContinuingProject&folderfile=output&folder=output&inc_obj=ajax&inc_act=editProject";?>',
            data: { fwajax: 1, fw_nocss: 1, typeFromCustomer: type, customerIdFromCustomer: '<?php echo $cid;?>' },
            success: function(obj){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
                fw_loading_end();
            }
        }).fail(function(){
            fw_loading_end();
        });
    })
	$(".output-add-project2_onetime").off("click").on("click", function(){
        fw_loading_start();
        var type = $(this).data("type");
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=Project2&folderfile=output&folder=output&inc_obj=ajax&inc_act=editProject";?>',
            data: { fwajax: 1, fw_nocss: 1, typeFromCustomer: type, customerIdFromCustomer: '<?php echo $cid;?>' },
            success: function(obj){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
                fw_loading_end();
            }
        }).fail(function(){
            fw_loading_end();
        });
    })

});
<?php if($moduleAccesslevel > 10) { ?>
    $(".editProspectDefault").off("click").on("click", function(){
        var data = {
            customerId: '<?php echo $cid;?>'
        }
        ajaxCall('edit_prospect_default', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
function rebind_prospect_button(){
    $(".showContactPoints").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var cid = $(this).data("prospect-id");
        if(cid === undefined) cid = 0;
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=contactpoints_list";?>',
            data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid},
            success: function(obj){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })
    $(".edit-prospect-btn").unbind("click").bind("click", function(e){
		e.preventDefault();
        fw_loading_start();
        var cid = $(this).data("prospect-id");
        if(cid === undefined) cid = 0;
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_prospect";?>',
            data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid},
            success: function(obj){
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
                fw_loading_end();
            }
        });
    })
    // $(".delete-prospect-btn").unbind("click").bind("click", function(e){
    //     var cid = $(this).data("prospect-id");

    //     e.preventDefault();
    //     if(!fw_click_instance)
    //     {
    //         fw_click_instance = true;
    //         var $_this = $(this);
    //         bootbox.confirm({
    //             message:$_this.attr("data-delete-msg"),
    //             buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
    //             callback: function(result){
    //                 if(result)
    //                 {
    //                     $.ajax({
    //                         cache: false,
    //                         type: 'POST',
    //                         dataType: 'json',
    //                         data: {fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid, output_delete: 1},
    //                         url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_prospect";?>',
    //                         success: function(data){
    //                             if(data.error !== undefined)
    //                             {
    //                                 fw_info_message_empty();
    //                                 $.each(data.error, function(index, value){
    //                                     var _type = Array("error");
    //                                     if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
    //                                     fw_info_message_add(_type[0], value);
    //                                 });
    //                                 fw_info_message_show();
    //                                 fw_loading_end();
    //                             } else {
    //                                 fw_load_ajax(data.redirect_url,'',true);
    //                             }
    //                         }
    //                     });
    //                 }
    //                 fw_click_instance = false;
    //             }
    //         });
    //     }
    // })
    <?php if($v_customer_accountconfig['hide_stopped_subscriptions']){ ?>
        $(".subscription-block.stopped").hide();
        $(".showStoppedSubscriptions").off("click").on("click", function(){
            $(".subscription-block.stopped").toggle();
        })
    <?php } ?>
}
function output_delete_file(cid, deletefileid)
{
    fw_loading_start();
	if(cid === undefined) cid = 0;
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=save_files";?>',
		data: { fwajax: 1, fw_nocss: 1, cid: cid, fileuploadaction: 'delete', deletefileid: deletefileid },
		success: function(obj){
            fw_loading_end();
            output_reload_page();
			// fw_load_ajax('', '',true);
		}
	});
}
function output_edit_comment(cid)
{
    fw_loading_start();
	if(cid === undefined) cid = 0;
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_comment";?>',
		data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid},
		success: function(obj){
            fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		}
	});
}
function output_edit_files(cid)
{
    fw_loading_start();
	if(cid === undefined) cid = 0;
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_files";?>',
		data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid},
		success: function(obj){
            fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			fileuploadPopupAC = $('#popupeditbox').bPopup(fileuploadPopupOptionsAC);
			$("#popupeditbox:not(.opened)").remove();
		}
	});
}
function output_access_load()
{
    var _items = $('.output-access-loader.load');
    if(_items.length > 0)
    {
        var _this = _items.get(0);
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=access_status";?>',
            data: { fwajax: 1, fw_nocss: 1, contactperson_id: $(_this).data('id'), membersystem_id: $(_this).data('membersystem-id'), email: $(_this).data('email') },
            success: function(obj){
                $(_this).removeClass("load").html(obj.html);
                setTimeout(output_access_load,1);
            }
        });
    }
}
function output_access_grant(_this, id)
{
    fw_loading_start();
    $(_this).closest(".output-access-loader").addClass("load");
	var _data = { fwajax: 1, fw_nocss: 1, cid: id };

	if($(_this).data('change') == 1)
	{
		_data.change_access = 1;
	}
    $.ajax({

        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=send_invitation_preview";?>',
        data: _data,
        success: function(obj){
            fw_loading_end();
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        }
    });
}
function output_access_remove(_this, id)
{
    if(!fw_click_instance)
    {
        fw_click_instance = true;
        bootbox.confirm({
            message:$(_this).attr("data-delete-msg"),
            buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
            callback: function(result){
                if(result)
                {
                    $(_this).closest(".output-access-loader").addClass("load");
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        dataType: 'json',
                        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
                        data: { fwajax: 1, fw_nocss: 1, cid: id },
                        success: function(obj){
                            $('#popupeditboxcontent').html('');
                            $('#popupeditboxcontent').html(obj.html);
                            out_popup = $('#popupeditbox').bPopup(out_popup_options);
                            $("#popupeditbox:not(.opened)").remove();
                            output_access_load();
                        }
                    });
                }
                fw_click_instance = false;
            }
        });
    }
}
function output_send_email(cid)
{
    if(cid === undefined) cid = 0;
    $.ajax({
        cache: false,
        type: 'POST',
        dataType: 'json',
        url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=sendEmail";?>',
        data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: cid},
        success: function(obj){
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(obj.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        }
    });
}
function output_getynet_account_status_change(_this)
{
	var data = {
		account_id: $(_this).data('account-id'),
		status: $(_this).data('status')
	};
	ajaxCall('update_getynet_account_status', data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
function output_getynet_account_edition_change(_this)
{
	var data = {
		account_id: $(_this).data('account-id'),
		edition_id: $(_this).val()
	};
	ajaxCall('update_getynet_account_edition', data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
function output_getynet_account_show_domainadmin(_this)
{
	var data = {
		account_id: $(_this).data('account-id'),
		status: $(_this).data('status')
	};
	ajaxCall('show_domainadmin', data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
function output_getynet_account_change_domain_status(_this)
{
	var data = {
		domainname: $(_this).data('domainname'),
		account_id: $(_this).data('account_id'),
		status: $(_this).data('status')
	};
	ajaxCall('update_account_domain_status', data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
<?php } ?>
<?php if($customerData['publicRegisterId'] != '') { ?>
function output_show_brreg_sync(){
	var data = {
		show_difference: 1,
		customer_id: '<?php echo $customerData['id'];?>'
	};
	ajaxCall({module_file:'brreg_check&abortable=1'}, data, function(json) {
		$('#popupeditboxcontent').html('');
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
function output_set_ehf_invoicing(){
	var data = {
		confirm_update: 1,
		customer_id: '<?php echo $customerData['id'];?>'
	};
	ajaxCall({module_file:'check_ehf&abortable=1'}, data, function(json) {
		$('#popupeditboxcontent').html(json.html);
		out_popup = $('#popupeditbox').bPopup(out_popup_options);
		$("#popupeditbox:not(.opened)").remove();
	});
}
<?php } ?>
</script>
<style>
.history_view_more {
	cursor: pointer;
}
.history_cat_title {
	margin-bottom: 5px;
	margin-left: 5px;
}
.showOldOffers {
    cursor: pointer;
    color: #46b2e2;
}
.oldOffers {
    display: none;
}
.task_row {
    padding: 4px 0px;
    border-bottom: 1px solid #cecece;
}
.task_row .cursor {
    cursor: pointer;
}
.task_row .task_column.createdColumn {
    color: #a5ada7;
    margin-right: 15px;
}
.task_row .task_column {
    float: left;
}
.task_row .task_description {
    display: none;
}
.task_description .delete-task-btn {
    float: right;
    cursor: pointer;
    color: #46b2e2;
    margin-left: 10px;
}
.task_description .edit-task-btn {
    float: right;
    cursor: pointer;
    color: #46b2e2;
}
.task_description_info {
    margin-top: 10px;
}
.task_description_info .fw_icon_color {
    color: #46b2e2;
    margin-right: 5px;
}
.task_description_info .performLabel {
    color: #7e7e7e;
}
.assignRentalUnit {
    margin-bottom: 20px;
    display: inline-block;
    margin-left: 10px;
}
.showinvoicedorderlines {
	color: #0284C9;
	cursor: pointer;
}
.orderlinesInvoiced table {
    margin-bottom: 0;
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
</style>
