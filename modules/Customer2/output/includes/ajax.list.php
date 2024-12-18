<?php
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
require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

if($_GET['action'] == 'unmark'){
    foreach($_GET['customerIds'] as $customerId) {
        $s_sql = "UPDATE customer SET marked_for_manual_check = 0 WHERE id = '".$o_main->db->escape_str($customerId)."'";
        $o_query = $o_main->db->query($s_sql);
    }
}

$s_sql = "SELECT * FROM customer_listtabs_basisconfig ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->result_array() : array());
$default_list = "all";
$default_sublist = "selfregistered_unhandled";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }

if(isset($_POST['list_filter'])) $_GET['list_filter'] = $_POST['list_filter'];
if($_POST['list_filter'] == "selfregistered")
{
	unset($_POST['city_filter'], $_POST['search_filter'], $_POST['search_by'], $_POST['selfdefinedfield_filter'], $_POST['activecontract_filter']);
	unset($_GET['city_filter'], $_GET['search_filter'], $_GET['search_by'], $_GET['selfdefinedfield_filter'], $_GET['activecontract_filter']);
	unset($_SESSION['selfdefinedfield_filter']);

	if(!isset($_POST['sort_field']))
	{
		$_POST['sort_field'] = 'name';
		$_POST['sort_desc'] = 1;
	}
}
if(isset($_POST['sublist_filter'])) $_GET['sublist_filter'] = $_POST['sublist_filter'];
if(isset($_POST['city_filter'])) $_GET['city_filter'] = $_POST['city_filter'];
if(isset($_POST['search_filter'])) $_GET['search_filter'] = $_POST['search_filter'];
if(isset($_POST['search_by'])) $_GET['search_by'] = $_POST['search_by'];
if(isset($_POST['selfdefinedfield_filter'])) $_GET['selfdefinedfield_filter'] = $_POST['selfdefinedfield_filter'];
if(isset($_POST['activecontract_filter'])) $_GET['activecontract_filter'] = $_POST['activecontract_filter'];

if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = $default_list; }
if($_GET['sublist_filter'] != ''){ $sublist_filter = $_GET['sublist_filter']; } else { $sublist_filter = $default_sublist; }
//$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
if(isset($_GET['city_filter'])){ $city_filter = $_GET['city_filter']; } else { $city_filter = ''; }
//$city_filter = $_GET['city_filter'] ? ($_GET['city_filter']) : '';
if(isset($_GET['search_filter'])){ $search_filter = $_GET['search_filter']; } else { $search_filter = ''; }
//$search_filter = $_GET['search_filter'] ? ($_GET['search_filter']) : '';
if(isset($_GET['search_by'])){ $search_by = $_GET['search_by']; } else { $search_by = ''; }
//$search_by = $_GET['search_by'] ? ($_GET['search_by']) : 1;
if(isset($_GET['activecontract_filter'])){ $activecontract_filter = $_GET['activecontract_filter']; } else { $activecontract_filter = ''; }
//$activecontract_filter = $_GET['activecontract_filter'] ? ($_GET['activecontract_filter']) : '';

if(isset($_GET['selfdefinedfield_filter'])){ $selfdefinedfield_filter = $_GET['selfdefinedfield_filter']; } else { $selfdefinedfield_filter = $_SESSION['selfdefinedfield_filter']; }
//$selfdefinedfield_filter = $_GET['selfdefinedfield_filter'] ? $_GET['selfdefinedfield_filter'] : '';
if(!is_array($selfdefinedfield_filter)) {
	$selfdefinedfield_filter = json_decode(base64_decode($selfdefinedfield_filter), true);
}

$_SESSION['list_filter'] = $list_filter;
$_SESSION['sublist_filter'] = $sublist_filter;
$_SESSION['city_filter'] = $city_filter;
$_SESSION['search_filter'] = $search_filter;
$_SESSION['search_by'] = $search_by;
$_SESSION['selfdefinedfield_filter'] = $selfdefinedfield_filter;
$_SESSION['activecontract_filter'] = $activecontract_filter;


$list_filter = str_replace("group_by_", "", $list_filter);

$b_init_selection = FALSE;
if(isset($_GET['selection_mode']))
{
	if($_GET['selection_mode'] == 1)
	{
		$b_init_selection = TRUE;
		$_SESSION['selection_mode'] = 1;
		$_SESSION['selected_customer'] = array();
	} else unset($_SESSION['selection_mode']);
}
$b_selection_mode = isset($_SESSION['selection_mode']);

if($list_filter == "selfregistered")
{
	if(!isset($_POST['sort_field']))
	{
		$_POST['sort_field'] = 'name';
		$_POST['sort_desc'] = 0;
	}

	$s_sort_desc = (1 == $_POST['sort_desc'] ? ' DESC' : '');

	if('name' == $_POST['sort_field'])
	{
		$s_custom_order_by = ' ORDER BY c.name'.$s_sort_desc.', c.middlename'.$s_sort_desc.', c.lastname'.$s_sort_desc;
	} else if('created' == $_POST['sort_field'])
	{
		$s_custom_order_by = ' ORDER BY c.created'.$s_sort_desc;
	}
}

if($_SERVER['REMOTE_ADDR']=='87.110.235.137') {
	require_once __DIR__ . '/functions.php';
} else {
	require_once __DIR__ . '/functions.php';
}
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM customer_listtabs_basisconfig WHERE id = '".$list_filter."' ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->row_array() : array());
$customer_currentList_basisconfig = array();
if(count($customer_currentList_basisconfig) == 0){
	$s_sql = "SELECT * FROM customer_list_basisconfig WHERE id = '".$customer_listtabs_basisconfig['choose_list']."' ORDER BY sortnr";
	$o_query = $o_main->db->query($s_sql);
	$customer_currentList_basisconfig = $o_query ? $o_query->row_array() : array();
}
$s_sql = "SELECT * FROM customer_list_basisconfig WHERE default_list = 1 ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$o_nums2 = $o_query->num_rows();
$customer_defaultList_basisconfig = ($o_query ? $o_query->row_array() : array());

if($list_filter == "all"){
	// $itemCount = get_customer_list_count($o_main, 'all', $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by);
} else if($list_filter == "with_orders"){
	// $itemCount = get_customer_list_count($o_main, 'with_orders', $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
} else {
	// $itemCount = get_customer_list_count($o_main, $list_filter."sublistSeperator".$sublist_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
    if($list_filter == "selfregistered"){
        // $selfregistered_unhandled_count = get_customer_list_count($o_main, $list_filter."sublistSeperatorselfregistered_unhandled", $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
        // $selfregistered_handled_count = get_customer_list_count($o_main, $list_filter."sublistSeperatorselfregistered_handled", $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
    }
}

if(isset($_POST['page'])) {
	$page = $_POST['page'];
} else { $page = 0; }
if(intval($page) == 0){
	$page = 1;
}
if(isset($_POST['rowOnly'])){ $rowOnly = $_POST['rowOnly']; } else { $rowOnly = null; }
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;
// if($list_filter == 'with_subscriptions'){
// 	$with_subscriptions_count = get_customer_list_count('with_subscriptions',$building_filter, $search_filter, $customergroup_filter);
// 	$currentCount = $with_subscriptions_count;
// }
// if($list_filter == 'with_expired_subscriptions'){
// 	$with_expired_subscriptions_count = get_customer_list_count('with_expired_subscriptions',$building_filter, $search_filter, $customergroup_filter);
// 	$currentCount = $with_expired_subscriptions_count;
// }

$showDefaultList = false;
if($list_filter == "all" && $o_nums2 > 0) {
	$showDefaultList = true;
}
if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

if($search_filter != "") {
	if(isset($_POST['contactPage'])){ $contactPage = $_POST['contactPage']; } else { $contactPage = 1; }
	//$contactPage = $_POST['contactPage'] ? $_POST['contactPage'] : 1;
	$customerPage = intval($_POST['customerPage']);
	if($customerPage <= 0){
		$customerPage = 1;
	}
	//$customerPage = $_POST['customerPage'] ? $_POST['customerPage'] : 1;
	if(intval($customer_basisconfig['deactivateCompanySearch']) == 0){
		$customerList = get_customer_list($o_main, $list_filter."sublistSeperator".$sublist_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1, $customerPage, $perPage, NULL, $s_custom_order_by);
	}
	if(intval($customer_basisconfig['deactivateContactPersonSearch']) == 0){
		$customerContactList = get_customer_list($o_main, $list_filter."sublistSeperator".$sublist_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 2, $contactPage, $perPage);
	}

	$customer_deleted_list = get_customer_list($o_main, "deleted", $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1, $contactPage, $perPage);

} else {
	$customerList = get_customer_list($o_main, $list_filter."sublistSeperator".$sublist_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1, $page, $perPage, NULL, $s_custom_order_by);
}


$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$filteredCount = $currentCount;
if($city_filter != "" || $search_filter != "" || $activecontract_filter != "" || $selfdefinedfield_filter != ""){
	if(intval($customer_basisconfig['deactivateCompanySearch']) == 0){
	    if(''!=$search_filter) $filteredCount = get_customer_list_count2($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 1);
	}
	if(intval($customer_basisconfig['deactivateContactPersonSearch']) == 0){
	    if(''!=$search_filter) $filtered2Count = get_customer_list_count2($o_main, $list_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, 2);
	}
	$totalPagesFiltered = ceil($filteredCount/$perPage);
} else {
	$totalPagesFiltered = $totalPages;
}
if($list_filter == "all")
{
	$s_sql = "SELECT * FROM customer_listfields_basisconfig WHERE customer_list_basisconfig_id = '".$customer_defaultList_basisconfig['id']."' ORDER BY sortnr";
} else {
	$s_sql = "SELECT * FROM customer_listfields_basisconfig WHERE customer_list_basisconfig_id = '".$customer_currentList_basisconfig['id']."' ORDER BY sortnr";
}
$o_query = $o_main->db->query($s_sql);
$customer_list_fields = ($o_query ? $o_query->result_array() : array());

if(!$rowOnly)
{
	if(!isset($_POST['updateOnlyList']) && !isset($_POST['next_page']))
	{
		include(__DIR__."/list_filter.php");
	}
	if(!isset($_POST['updateOnlyList']) && !isset($_POST['next_page']))
	{
		echo '<div class="resultTableWrapper">';
	}
	if(!$customer_basisconfig['deactivateCompanySearch'])
	{
	    if(!isset($_POST['next_page']))
		{
            if($list_filter=="not_connected_sub"){
                ?>
                <div class="gtable gtable_fixed" id="gtable_search">
                    <div class="gtable_row">
    				    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_SubscriptionName_Output;?></strong></div>
    				    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_PreviousCustomerId_Output;?></strong></div>
    				    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_PreviousCustomerName_Output;?></strong></div>
    				    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_Connect_Output;?></strong></div>
                    </div>
                <?php
            } else if($list_filter!="not_connected") {
    			if($search_filter != "")
    			{
    				?><div class="customer-list-table-title"><?php echo $formText_SearchInCustomer_output;?> <span><?php echo $filteredCount." ".$formText_Hits_Output; ?></span></div><?php
    			}
    			echo '<div class="gtable" id="gtable_search">';
    			echo '<div class="gtable_row">';
    			if($list_filter == 'selfregistered' && 'selfregistered_handled' != $sublist_filter)
    			{
    				?>
    				<div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_SelfregisteredCustomers_Output;?></strong></div>
    				<div class="gtable_cell gtable_cell_head" style="border-left-color:transparent;"></div>
    				<div class="gtable_cell gtable_cell_head" style="border-left-color:transparent;">&nbsp;</div>
    				<div class="gtable_cell gtable_cell_head" style="border-left-width:5px;"><strong><?php echo $formText_OrdinaryCustomers_Output;?></strong></div>
    				<div class="gtable_cell gtable_cell_head" style="border-left-color:transparent;"></div>
    				<?php
    				echo '</div><div class="gtable_row">';
    			}
    			if($b_selection_mode && $totalPagesFiltered == 1)
    			{
    				?><div class="gtable_cell gtable_cell_head c0"><input type="checkbox" class="selection-switch main" value="" checked><label class="selection-switch-btn main"></label></div><?php
    			}
    			if(count($customer_currentList_basisconfig) > 0 || $showDefaultList)
    			{
    				foreach($customer_list_fields as $customer_list_field)
    				{
    					?><div class="gtable_cell gtable_cell_head"><?php echo $customer_list_field['column_label'];?></div><?php
                        if($customer_list_field['fieldtype'] == 1 && ($customer_list_field['customertable_fieldname'] == 'name' || $customer_list_field['customertable_fieldname'] == 'customerName')) {
                            if($v_customer_accountconfig['activate_subunits']) { ?>
                                <div class="gtable_cell gtable_cell_head"><?php echo $formText_Subunits_output;?></div>
                            <?php }
                        }
    				}
					?>

					<?php
    			} else {
    				?><div class="gtable_cell gtable_cell_head c1<?php echo ($list_filter == "selfregistered" ? ' table-sortable'.('name' == $_POST['sort_field'] ? ' sort_active'.('1' == $_POST['sort_desc'] ? '' : ' sort_asc') : '') : '');?>" data-sortable="name"><?php echo $formText_Name_output;?><?php if($list_filter == "selfregistered"){?> <span class="glyphicon glyphicon-arrow-up"></span><span class="glyphicon glyphicon-arrow-down"></span><?php } ?></div>
    				<?php if($search_by == 3 || $search_by == 4 || $search_by == 5) { ?>
    					<div class="gtable_cell gtable_cell_head"><?php echo $formText_ContactPersons_output;?></div>
    				<?php } else { ?>
        				<?php if($list_filter != 'with_orders' && $list_filter != 'selfregistered') {  ?>
        					<div class="gtable_cell gtable_cell_head"><?php echo $formText_Street_output;?></div>
        					<div class="gtable_cell gtable_cell_head"><?php echo $formText_City_output;?></div>
        				<?php } ?>
						<div class="gtable_cell gtable_cell_head"><?php echo $formText_CreditorName_output;?></div>
						<div class="gtable_cell gtable_cell_head"><?php echo $formText_CustomerId_output;?></div>

    				<?php } ?>
                    <?php if($list_filter == 'selfregistered') { ?>
                        <div class="gtable_cell gtable_cell_head<?php echo ($list_filter == "selfregistered" ? ' table-sortable'.('created' == $_POST['sort_field'] ? ' sort_active'.('1' == $_POST['sort_desc'] ? '' : ' sort_asc') : '') : '');?>" data-sortable="created"><?php echo $formText_Created_output;?><?php if($list_filter == "selfregistered"){?> <span class="glyphicon glyphicon-arrow-up"></span><span class="glyphicon glyphicon-arrow-down"></span><?php } ?></div>
                    <?php } ?>
    				<?php if($list_filter == 'with_orders') { ?>
    				<div class="gtable_cell gtable_cell_head c3">
    					<span style="display: inline-block; width: 30%; vertical-align: middle">
    						<?php echo $formText_ProjectName_output;?>
    					</span>
    					<span style="display: inline-block; width: 22%; vertical-align: middle">
    						<?php echo $formText_OrderNr_output;?>
    					</span>
    					<span style="display: inline-block; width: 30%; vertical-align: middle">
    						<?php echo $formText_Orderlines_output;?>
    					</span>
    				</div>
    				<?php }
    			}
    			?>
                <?php if($list_filter == "selfregistered" && 'selfregistered_handled' != $sublist_filter) { ?>
    				<div class="gtable_cell gtable_cell_head">&nbsp;</div>
                    <div class="gtable_cell gtable_cell_head c1" style="border-left-width:5px;"><?php echo $formText_DuplicatesFound_output;?></div>
                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_Created_output;?></div>
                <?php } ?>
                <?php
                if($list_filter=="marked_for_manual_check"){
                    ?>
                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_UnmarkAll_output;?> <input type="checkbox" id="unmark_all" value="1" autocomplete="off"/><label for="unmark_all"></label></div>
                    <?php
                }
                ?>

                </div><?php
            }
		}
	}
    if($list_filter=="not_connected") {

        $s_sql = "SELECT * FROM contactperson WHERE (customerId is null OR customerId = 0) AND type = 1 AND content_status < 2 GROUP BY previous_customer_name ORDER BY previous_customer_name ASC";
        $o_query = $o_main->db->query($s_sql);
        $notConnectedContactPersonGroups = $o_query ? $o_query->result_array() : array();

        foreach($notConnectedContactPersonGroups as $notConnectedContactPersonGroup) {
            $previousCustomerId = '';
            $previousCustomerName = '';
            if($notConnectedContactPersonGroup['previous_customer_id'] != ""){
                $previousCustomerId = $notConnectedContactPersonGroup['previous_customer_id'];
            }
            if($notConnectedContactPersonGroup['previous_customer_name'] != ""){
                $previousCustomerName = $notConnectedContactPersonGroup['previous_customer_name'];
            }
            $s_sql = "SELECT * FROM contactperson WHERE (customerId is null OR customerId = 0) AND type = 1 AND content_status < 2
            AND IFNULL(previous_customer_name, '') = '".$o_main->db->escape_str($previousCustomerName)."'
            AND IFNULL(previous_customer_id, '') = '".$o_main->db->escape_str($previousCustomerId)."'
            ORDER BY name ASC";
            $o_query = $o_main->db->query($s_sql);
            $notConnectedContactPersons = $o_query ? $o_query->result_array() : array();
            ?>
            <div class="gtable gtable_fixed" id="gtable_search">
                <div class="gtable_row">
    			    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_Name_Output;?></strong></div>
    			    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_Email_Output;?></strong></div>
    			    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_Title_Output;?></strong></div>
    			    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_PreviousCustomerId_Output;?></strong></div>
    			    <div class="gtable_cell gtable_cell_head"><strong><?php echo $formText_PreviousCustomerName_Output;?></strong></div>
    			    <div class="gtable_cell gtable_cell_head"></div>
                </div>
                <?php
                foreach($notConnectedContactPersons as $notConnectedContactPerson) {
                    ?>
                    <div class="gtable_row">
                        <div class="gtable_cell"><?php echo $notConnectedContactPerson['name']." ".$notConnectedContactPerson['middlename']." ".$notConnectedContactPerson['lastname'];?></div>
                        <div class="gtable_cell"><?php echo $notConnectedContactPerson['email'];?></div>
                        <div class="gtable_cell"><?php echo $notConnectedContactPerson['title'];?></div>
                        <div class="gtable_cell"><?php echo $notConnectedContactPerson['previous_customer_id'];?></div>
                        <div class="gtable_cell"><?php echo $notConnectedContactPerson['previous_customer_name'];?></div>
                        <div class="gtable_cell">
                            <input type="checkbox" value="<?php echo $notConnectedContactPerson['id'];?>" class="cp_connect_customer_checkbox" id="cp_connect_customer_checkbox<?php echo $notConnectedContactPerson['id'];?>"/>
                            <span class="cp_connect_customer"><?php echo $formText_Connect_output;?></span>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    } else if($list_filter=="not_connected_sub") {
        $s_sql = "SELECT subscriptionmulti.* FROM subscriptionmulti LEFT OUTER JOIN customer ON customer.id = subscriptionmulti.customerId WHERE customer.id is null AND subscriptionmulti.content_status < 2 AND (subscriptionmulti.collecting_task is null OR subscriptionmulti.collecting_task = 0)";
        $o_query = $o_main->db->query($s_sql);
        $notConnectedContactPersons = $o_query ? $o_query->result_array() : array();
        foreach($notConnectedContactPersons as $notConnectedContactPerson) {
            ?>
            <div class="gtable_row">
                <div class="gtable_cell"><?php echo $notConnectedContactPerson['subscriptionName'];?></div>
                <div class="gtable_cell"><?php echo $notConnectedContactPerson['previous_customer_id'];?></div>
                <div class="gtable_cell"><?php echo $notConnectedContactPerson['previous_customer_name'];?></div>
                <div class="gtable_cell">
                    <input type="checkbox" value="<?php echo $notConnectedContactPerson['id'];?>" class="cp_connect_subscription_checkbox" id="cp_connect_subscription_checkbox<?php echo $notConnectedContactPerson['id'];?>"/>
                    <span class="cp_connect_subscription"><?php echo $formText_Connect_output;?></span>
                </div>
            </div>
            <?php
        }
        if(!isset($_POST['next_page'])) echo '</div>';
    } else {
    	foreach($customerList as $v_row)
    	{
			$s_sql = "SELECT * FROM creditor WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['creditor_id']));
			$v_creditor = $o_query ? $o_query->row_array() : array();

            $subunits = array();
            if($v_customer_accountconfig['activate_subunits']) {
                $s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? ORDER BY customer_subunit.id ASC";
                $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
                $subunits = $o_query ? $o_query->result_array() : array();
            }
            if(count($subunits) == 0){
                $subunits = array(array("id"=>0));
            }
            foreach($subunits as $subunit){
        		$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['customerId']."&subunit_filter=".$subunit['id'];
        		?>
        		<div class="gtable_row <?php echo (($list_filter == "selfregistered" && 'selfregistered_unhandled' == $sublist_filter) ? '' : 'output-click-helper');?>" data-href="<?php echo $s_edit_link;?>">
        			<?php
        			if($b_selection_mode && $totalPagesFiltered == 1)
        			{
        				?><div class="gtable_cell c0"><input type="checkbox" class="selection-switch" value="<?php echo $v_row['customerId'];?>" checked><label class="selection-switch-btn"></label></div><?php
        			}
        			//show customazable columns

        	        if(count($customer_currentList_basisconfig) > 0 || $showDefaultList)
        			{
        	        	foreach($customer_list_fields as $customer_list_field)
        				{
        	        		$customer_list_fieldtype = $customer_list_field['fieldtype'];
        	        		switch($customer_list_fieldtype)
        					{
        	        			case 1:
        						if($customer_list_field['customertable_fieldname'] == "name") {
        							$customer_list_field['customertable_fieldname'] = "customerName";
        						}
        	        			?>
        		        		<div class="gtable_cell">
                                    <?php echo $v_row[$customer_list_field['customertable_fieldname']].(($customer_list_field['customertable_fieldname'] == "customerName" && $customer_basisconfig['activate_shop_name'] && '' != trim($v_row['shop_name'])) ? ' ('.trim($v_row['shop_name']).')' : '');?>
                                </div>
                				<?php // Show default columns
                                if($customer_list_field['customertable_fieldname'] == 'customerName') {
                                    if($v_customer_accountconfig['activate_subunits']) { ?>
                                        <div class="gtable_cell"><?php
                                            echo $subunit['name'];
                                        ?></div>
                                    <?php }
                                } ?>
        	        			<?php
        	        			break;
        	        			case 2:
        	        			?>
        		        		<div class="gtable_cell">
        		        			<?php
        							$v_subrows = array();
        							$s_sql = "SELECT * FROM contactperson WHERE customerId = ? AND content_status = 0 ORDER BY sortnr";
        							$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        							if($o_query && $o_query->num_rows()>0){
        							    $v_subrows = $o_query->result_array();
        							}
        							foreach($v_subrows as $v_subrow)
        							{

        								?><div class="contactcell_row">
        									<?php if($customer_list_field['contactperson_display_accesstatus']) { ?>
        										<div class="contactcell_cell cc1">
        										<?php

        										if($v_subrow['email']!="") { ?>
        											<div class="output-access-loader" data-id="<?php echo $v_subrow["id"];?>" data-email="<?php echo $v_subrow['email'];?>" data-membersystem-id="<?php echo $l_membersystem_id;?>">
        												<div class="output-access-changer"><?php
        												if(isset($v_membersystem[$l_membersystem_id][$v_subrow['email']]))
        												{
        													$v_access = $v_membersystem[$l_membersystem_id][$v_subrow['email']];
        													$s_icon = "green";
        													if($v_access[2] == 0) $s_icon = "green_grey";
        													?><img src="<?php echo $extradir."/output/elementsOutput/access_key_".$s_icon;?>.png" /><?php
        													?><div class="output-access-dropdown"><a class="script" href="#" onClick="javascript:output_access_remove(this,'<?php echo $v_subrow['id'];?>');" data-delete-msg="<?php echo $formText_RemoveAccess_Output.": ".$v_subrow["email"];?>?"><?php echo $formText_RemoveAccess_Output;?></a><div><?php if($v_access[0] == 1 && $v_access[2] == 1) echo $formText_LastActivity_Output.": ".date("d.m.Y H:i", strtotime($v_access[1])); if($v_access[2] == 0) echo $formText_NeverLoggedIn_Output;?></div></div><?php
        												} else {
        													?><img src="<?php echo $extradir;?>/output/elementsOutput/access_key_grey.png" /><?php
        													?><div class="output-access-dropdown"><a class="script" href="#" onClick="javascript:output_access_grant(this,'<?php echo $v_subrow['id'];?>');"><?php echo $formText_GiveAccess_Output;?></a></div><?php
        												}
        												?>
        												</div>
        											</div>
        										<?php } ?>
        										</div>
        									<?php } ?>
        									<div class="contactcell_cell cc2"><?php echo $v_subrow['name']." ".$v_subrow['middlename']." ".$v_subrow['lastname']; ?></div>
        									<div class="contactcell_cell cc3"><?php echo $v_subrow['email']; ?></div>
        								</div><?php
        							}
        							?>
        						</div>
        	        			<?php
        	        			break;
        	        			case 3:
        	        			?>
        		        		<div class="gtable_cell">

        		        			<?php
        		        			$subscriptions = get_subscriptions_with_offices($o_main, $v_row['customerId']);
        		        			foreach($subscriptions['all'] as $subscription): ?>
        			                    <div class="subscription-cell-line">
        			                        <?php echo $subscription['subscriptionName']; ?>
        			                        <?php if ($subscription['stoppedDate'] && $subscription['stoppedDate'] != '0000-00-00'  && strtotime($subscription['stoppedDate']) <= time()): ?>
        			                            <span class="label label-danger"><?php echo $formText_Stopped_output; ?></span>
        			                        <?php elseif ($subscription['stoppedDate'] && $subscription['stoppedDate'] != '0000-00-00'  && strtotime($subscription['stoppedDate']) > time()): ?>
        			                            <span class="label label-warning"><?php echo $formText_ActiveWithStopDate_output; ?></span>
        			                        <?php else: ?>
        			                            <span class="label label-success"><?php echo $formText_Active_output; ?></span>
        			                        <?php endif; ?>

        			                        <?php if ($subscription['buildingName']): ?>
        			                            <div class="subscription-cell-building">
        			                                <span class="glyphicon glyphicon-home"></span>
        			                                <?php echo $subscription['buildingName']; ?> -
        			                                <?php echo $subscription['officeNumber']; ?>
        			                            </div>
        			                        <?php endif; ?>
        			                    </div>
        			                <?php endforeach; ?>
        		        		</div>
        	        			<?php
        	        			break;
        	        			case 4:
        		                    $s_sql = "SELECT project.* FROM customer_collectingorder
        		                    LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
        		                    LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
        		                    WHERE project.id is not null AND customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null) AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId > 0 AND project.status <> 1 GROUP BY project.id  ORDER BY project.id DESC";
        		                    $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        		                    $projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());

        					    	array_push($projectsForCollectingOrders, array("id"=>null));
        			            	?>
        							<div class="gtable_cell c3">
        								<table class="table table-bordered" style="margin-bottom: 0;">
        									<?php
        									foreach($projectsForCollectingOrders as $projectsForCollectingOrder){
        										$collectingOrders = array();
        										if($projectsForCollectingOrder['id'] == null){
        										 	$s_sql = "SELECT co.*
        										    FROM customer_collectingorder co
        										    LEFT OUTER JOIN orders o ON o.collectingorderId = co.id
        										    WHERE co.customerId = ? AND (co.projectId is ? OR co.projectId = ?) AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0 GROUP BY co.id ORDER BY co.id DESC";
        											$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $projectsForCollectingOrder['id'], intval($projectsForCollectingOrder['id'])));
        										} else {
        										 	$s_sql = "SELECT co.*
        										    FROM customer_collectingorder co
        										    LEFT OUTER JOIN orders o ON o.collectingorderId = co.id
        										    WHERE co.customerId = ? AND co.projectId = ? AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0 GROUP BY co.id ORDER BY co.id DESC";
        											$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $projectsForCollectingOrder['id']));
        										}
        										if($o_query && $o_query->num_rows()>0){
        											$collectingOrders = $o_query->result_array();
        										}

        										if(count($collectingOrders) > 0){
        											?>
        											<tr>
        												<td width="30%"><?php echo $projectsForCollectingOrder['name'];?>&nbsp;</td>
        												<td width="70%" style="padding: 0px; border-top: 0;">
        													<table class="table collectingOrderLinesTable" style="margin-bottom: 0;">
        														<?php
        														foreach($collectingOrders as $collectingOrder){
        										        			$s_sql = "SELECT o.*
        														    FROM orders o
        														    WHERE o.collectingorderId = ? AND o.content_status = 0 ORDER BY o.id ASC";
        															$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
        															$orders = $o_query ? $o_query->result_array() : array();
        										        			?>
        															<tr>
        																<td width="30%"><?php echo $collectingOrder['id'];?></td>
        																<td width="70%">
        																	<?php
        													        		foreach($orders as $order) { ?>
        																		<div>
        																			<?php echo $order['articleName']; ?>
        																		</div>
        																	<?php } ?>
        																</td>
        															</tr>
        														<?php } ?>
        													</table>
        												</td>
        											</tr>
        										<?php } ?>
        									<?php } ?>
        								</table>
        							</div>
        	        			<?php
        	        			break;
        	        			case 5:
        	        			?>
        		        		<div class="gtable_cell">
        							<?php
        							$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = '".$customer_list_field['selfdefinedfield']."'";
        							$o_query = $o_main->db->query($s_sql);
        							$selfDefinedFieldsToShow = ($o_query ? $o_query->result_array() : array());
        							foreach($selfDefinedFieldsToShow as $selfdefinedField) {
        								$sfValue = "";
        						        if($selfdefinedField['type'] == 1){
        									$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_selfdefined_values.customer_id = ? AND customer_selfdefined_values.selfdefined_fields_id = ?";
        									$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $selfdefinedField['id']));
        									$selfDefinedFieldValue = ($o_query ? $o_query->row_array() : array());
        									if($selfDefinedFieldValue){
        										$s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE customer_selfdefined_list_lines.list_id = ? AND customer_selfdefined_list_lines.id = ?";
        										$o_query = $o_main->db->query($s_sql, array($selfdefinedField['list_id'], $selfDefinedFieldValue['value']));
        										$sfValueItem = ($o_query ? $o_query->row_array() : array());
        										$sfValue = $sfValueItem['name'];
        									}
        						        } else if ($selfdefinedField['type'] == 0) {
        						        	$s_sql = "SELECT * FROM customer_selfdefined_values WHERE customer_selfdefined_values.customer_id = ? AND customer_selfdefined_values.selfdefined_fields_id = ?";
        									$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $selfdefinedField['id']));
        									$selfDefinedFieldValue = ($o_query ? $o_query->row_array() : array());
        									if($selfDefinedFieldValue){
        										$sfValue = $selfDefinedFieldValue['value'];
        									}
        						        }
        								?>
        								<?php echo $sfValue;?>
        								<?php
        							}
        							?>
        		        		</div>
        	        			<?php
        	        			break;
        						case 6:
        						?>
        		        		<div class="gtable_cell">
        							<?php
        							$s_sql = "SELECT customer_externalsystem_id.*, oc.name as ownercompanyName FROM customer_externalsystem_id
                                    LEFT OUTER JOIN ownercompany oc ON oc.id = customer_externalsystem_id.ownercompany_id
                                    WHERE customer_id = '".$v_row['customerId']."'";
        							$o_query = $o_main->db->query($s_sql);
        							$externalIdEntries = ($o_query ? $o_query->result_array() : array());
                                    foreach($externalIdEntries as $externalIdEntry) {
            							echo $externalIdEntry[$customer_list_field['customertable_fieldname']]."<span class='ownercompany_info_wrapper hoverInit'><i class='fas fa-info-circle'></i><span class='hoverSpan'>".$externalIdEntry['ownercompanyName']."</span></span><br/>";
                                    }
        							?>
        		        		</div>
        	        			<?php
        						break;
        	        		}
        				}
        	      	} else { ?>
        	            <div class="gtable_cell c1 <?php if($list_filter == "selfregistered") { ?> c3 selfregisteredCell<?php } ?>">
                            <?php if($list_filter == "selfregistered" && 'selfregistered_unhandled' == $sublist_filter) { ?>
        					<a href="<?php echo $s_edit_link;?>" class="optimize">
        					<?php } ?>
        					<?php echo $v_row['customerName'].(($customer_basisconfig['activate_shop_name'] && '' != trim($v_row['shop_name'])) ? ' ('.$v_row['shop_name'].')' : '');?>
                            <?php if($list_filter == "selfregistered" && $v_row['customerExternalNr'] != "") { ?>
                                (<?php echo $formText_CustomerNr_output.": ".$v_row['customerExternalNr'];?>)
                            <?php } ?>
        					<?php if($list_filter == "selfregistered" && 'selfregistered_unhandled' == $sublist_filter) { ?>
        					</a>
        					<?php } ?>
                        </div>
        		        <?php if($search_by == 3 || $search_by == 4 || $search_by == 5) { ?>
        		            <div class="gtable_cell">
        		            	<?php
        							$contactpersons = array();
        	                        $s_sql = "select * from contactperson where customerId = ? AND content_status = 0 order by sortnr";;
        	                        $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        	                        if($o_query && $o_query->num_rows()>0){
        	                            $contactpersons = $o_query->result_array();
        	                        }
        		            	?>

        						<table class="table table-bordered">
        							<tr><th><?php echo $formText_contactPersonName_output;?></th><th><?php echo $formText_contactPersonMobile_output;?></th><th><?php echo $formText_contactPersonEmail_output;?></th></tr>
        						<?php
        						foreach($contactpersons as $contactperson) { ?>
        							<tr>
        								<td><?php echo $contactperson['name']." ".$v_subrow['middlename']." ".$v_subrow['lastname']?></td>
        								<td><?php echo $contactperson['mobile']?></td>
        								<td><?php echo $contactperson['email']; ?></td>
        							</tr>
        						<?php } ?>
        						</table>
        		            </div>
        	        	<?php } else { ?>
        	        		<?php if($list_filter != 'with_orders' && $list_filter != "selfregistered") {  ?>
        		            	<div class="gtable_cell"><?php echo $v_row['paStreet'];?></div>
        		            	<div class="gtable_cell"><?php echo $v_row['paCity'];?></div>
        		        	<?php } ?>
							<div class="gtable_cell"><?php 
								echo $v_creditor['companyname'];							
							?></div>
							<div class="gtable_cell"><?php echo $v_row['creditor_customer_id'];?></div>
        	            <?php } ?>
                        <?php if($list_filter == 'selfregistered') { ?>
                            <div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['created']));?></div>
                        <?php } ?>
        	            <?php
        				if($list_filter == 'with_orders')
        				{
        					$s_sql = "SELECT project.* FROM customer_collectingorder
        					LEFT OUTER JOIN customer ON customer.id = customer_collectingorder.customerId
        					LEFT OUTER JOIN project ON project.id = customer_collectingorder.projectId
        					WHERE project.id is not null AND customer.id is not null AND customer.id = ? AND (customer_collectingorder.invoiceNumber = 0 OR customer_collectingorder.invoiceNumber is null)
        					AND customer_collectingorder.content_status = 0 AND customer_collectingorder.projectId > 0 AND (project.status = 0 OR project.status is null) GROUP BY project.id  ORDER BY project.id DESC";
        					$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
        					$projectsForCollectingOrders = ($o_query ? $o_query->result_array() : array());

        					array_push($projectsForCollectingOrders, array("id"=>null));
        	            	?>
        					<div class="gtable_cell c3">
        						<table class="table table-bordered" style="margin-bottom: 0;">

        							<?php
        							foreach($projectsForCollectingOrders as $projectsForCollectingOrder){
        								$collectingOrders = array();
        								if($projectsForCollectingOrder['id'] == null){
        								 	$s_sql = "SELECT co.*
        								    FROM customer_collectingorder co
        								    LEFT OUTER JOIN orders o ON o.collectingorderId = co.id
        								    WHERE co.customerId = ? AND (co.projectId is ? OR co.projectId = ?) AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0 GROUP BY co.id ORDER BY co.id DESC";
        									$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $projectsForCollectingOrder['id'], intval($projectsForCollectingOrder['id'])));
        								} else {
        								 	$s_sql = "SELECT co.*
        								    FROM customer_collectingorder co
        								    LEFT OUTER JOIN orders o ON o.collectingorderId = co.id
        								    WHERE co.customerId = ? AND co.projectId = ? AND (co.invoiceNumber = 0 OR co.invoiceNumber is null) AND co.content_status = 0 GROUP BY co.id ORDER BY co.id DESC";
        									$o_query = $o_main->db->query($s_sql, array($v_row['customerId'], $projectsForCollectingOrder['id']));
        								}
        								if($o_query && $o_query->num_rows()>0){
        									$collectingOrders = $o_query->result_array();
        								}

        								if(count($collectingOrders) > 0){
        									?>
        									<tr>
        										<td width="30%"><?php echo $projectsForCollectingOrder['name'];?>&nbsp;</td>
        										<td width="70%" style="padding: 0px; border-top: 0;">
        											<table class="table collectingOrderLinesTable" style="margin-bottom: 0;">
        												<?php
        												foreach($collectingOrders as $collectingOrder){
        								        			$s_sql = "SELECT o.*
        												    FROM orders o
        												    WHERE o.collectingorderId = ? AND o.content_status = 0 ORDER BY o.id ASC";
        													$o_query = $o_main->db->query($s_sql, array($collectingOrder['id']));
        													$orders = $o_query ? $o_query->result_array() : array();
        								        			?>
        													<tr>
        														<td width="30%"><?php echo $collectingOrder['id'];?></td>
        														<td width="70%">
        															<?php
        											        		foreach($orders as $order) { ?>
        																<div>
        																	<?php echo $order['articleName']; ?>
        																</div>
        															<?php } ?>
        														</td>
        													</tr>
        												<?php } ?>
        											</table>
        										</td>
        									</tr>
        								<?php } ?>
        							<?php } ?>
        						</table>
        					</div>
        					<?php
        				}
                        if($list_filter == "selfregistered" && 'selfregistered_handled' != $sublist_filter) {
        					$b_show_merge_button = FALSE;
        					$s_found_customer = '';
        					$s_sql = "SELECT GROUP_CONCAT(id) ids, CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) mergedName, COUNT(*) c
                             FROM customer WHERE content_status < 2 AND (selfregistered IS NULL OR selfregistered = 0 OR selfregistered = 2)
                             GROUP BY CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) HAVING c >= 1 AND mergedName = ?";
                             $o_query = $o_main->db->query($s_sql, array(trim($v_row['name']).trim($v_row['middlename']).trim($v_row['lastname'])));
                             $duplicatesByName = $o_query ? $o_query->result_array() : array();
                             $duplicatesByRegisterId = array();
                             if($v_row['publicRegisterId'] != "" && $v_row['publicRegisterId'] != 0){
                                 $s_sql = "SELECT GROUP_CONCAT(id) ids, publicRegisterId, COUNT(*) c
                                 FROM customer WHERE content_status < 2  AND (selfregistered IS NULL OR selfregistered = 0 OR selfregistered = 2)
                                 GROUP BY TRIM(publicRegisterId) HAVING c >= 1 AND publicRegisterId = ?";
                                 $o_query = $o_main->db->query($s_sql, array($v_row['publicRegisterId']));
                                 $duplicatesByRegisterId = $o_query ? $o_query->result_array() : array();
                             }
                             $duplicatesCreated = array();
                             if((count($duplicatesByName)+count($duplicatesByRegisterId)) > 0){
                                 $duplicateLinks = array();
                                 foreach($duplicatesByName as $duplicates){
                                     $customerIds = explode(",",$duplicates['ids']);
                                     foreach($customerIds as $customerId){
                                         if($customerId != $v_row['customerId']){
                                             $o_query = $o_main->db->query("SELECT c.*, cexternl.external_id as customerExternalNr FROM customer c
                                                 LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = c.id
                                                 WHERE c.id = ?", array($customerId));
                                             $customer = $o_query ? $o_query->row_array() : array();
                                             $external_string = "";
                                             if($customer['customerExternalNr'] != ""){
                                                $external_string =' ('.$formText_CustomerNr_output.": ".$customer['customerExternalNr'].')';
                                             }
                                             $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customer['id'];
                                             $duplicateLinks[] = '<a href="'.$s_edit_link.'" class="optimize">'.$customer['name']." ".$customer['middlename']." ".$customer['lastname'] .' '.$external_string.'</a>';
                                             $duplicatesCreated[] = $customer['created'];
                                         }
                                     }
                                 }
                                 foreach($duplicatesByRegisterId as $duplicates){
                                     $customerIds = explode(",",$duplicates['ids']);
                                     foreach($customerIds as $customerId){
                                         if($customerId != $v_row['customerId']){
                                             $o_query = $o_main->db->query("SELECT c.*, cexternl.external_id as customerExternalNr FROM customer c
                                                 LEFT OUTER JOIN customer_externalsystem_id cexternl ON cexternl.customer_id = c.id
                                                 WHERE id = ?", array($customerId));
                                             $customer = $o_query ? $o_query->row_array() : array();
                                             $external_string = "";
                                             if($customer['customerExternalNr'] != ""){
                                                $external_string =' ('.$formText_CustomerNr_output.": ".$customer['customerExternalNr'].')';
                                             }
                                             $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customer['id'];
                                             $duplicateLinks[] = '<a href="'.$s_edit_link.'" class="optimize">'.$customer['name']." ".$customer['middlename']." ".$customer['lastname'] .' '.$external_string.'</a>';
                                             $duplicatesCreated[] = $customer['created'];
                                         }
                                     }
                                 }
                                 $b_show_merge_button = TRUE;
        						 $s_found_customer = implode("<br/>",$duplicateLinks);
                             } else {
                                 $s_found_customer = $formText_NoneFound_output;
                             }
        					?>
                            <div class="gtable_cell">
        						<?php
        						if($b_show_merge_button)
        						{
        							?><button class="output-merge-customer output-btn editBtnIcon" data-customer-id="<?php echo $v_row['customerId'];?>"><span class="glyphicon glyphicon-arrow-right"></span></button><?php
        						}
        						?>
        					</div>
        					<div class="gtable_cell c3" style="border-left-width:5px;"><?php echo $s_found_customer;?></div>
                            <div class="gtable_cell">
                                <?php
                                foreach($duplicatesCreated as $duplicateCreated){
                                    echo date("d.m.Y", strtotime($duplicateCreated))."<br/>";
                                }
                                ?>
                            </div>
                        <?php }
        			}
        			?>
                    <?php
                    if($list_filter=="marked_for_manual_check"){
                		$sql_where = " name LIKE '".$o_main->db->escape_str(mb_substr($v_row['name'], 0, 3))."%'";

                    	$s_sql = "SELECT * FROM customer WHERE customer.content_status < 2 AND (".$sql_where.") AND id <> '".$o_main->db->escape_str($v_row['customerId'])."'";
                        $o_query = $o_main->db->query($s_sql);
                        $suggestedCustomers = $o_query ? $o_query->result_array() : array();
                        ?>
                        <div class="gtable_cell ">
                            <input type="checkbox" class="unmark" id="unmark_<?php echo $v_row['customerId']?>" value="<?php echo $v_row['customerId'];?>" autocomplete="off"/><label for="unmark_<?php echo $v_row['customerId']?>"></label>
                            <span class="show_suggested" data-customer-id="<?php echo $v_row['customerId'];?>"><?php echo count($suggestedCustomers) . " ". $formText_Suggested_output;?></span>
                        </div>
                        <?php
                    }
                    ?>
        		</div>
        	    <?php
            }
    	}
        if(!isset($_POST['next_page'])) echo '</div>';


        if($list_filter=="marked_for_manual_check"){
            ?>
            <div class="launch_unmark"><?php echo $formText_UnmarkSelected_output;?></div>
            <?php
        }
    	if($search_filter != ""){
            if(count($customerList) < $filteredCount)
        	{
        		if(!isset($_POST['next_page']))
        		{
        			?><div class="customer-paging"><?php echo $formText_Showing_output ." ". count($customerList)." ".$formText_Of_output." ".$filteredCount;?> <a href="#" class="showNextCustomer" data-page="<?php echo $customerPage;?>"><?php echo $formText_ShowNext_output;?> 50</a></div><?php
        		}
        	}
    		if(count($customer_deleted_list) > 0){
    			echo '<div class="show_deleted_customers">'.count($customer_deleted_list)." ".$formText_CustomersInDeleted_output.'</div>';
    			?>
    			<div class="deletedCustomersWrapper">
    				<div class="gtable">
    					<div class="gtable_row">
    						<div class="gtable_cell gtable_cell_head "><?php echo $formText_Name_output;?></div>
    					</div>
    					<?php
    					foreach($customer_deleted_list as $customer_deleted) {
    						?>
    						<div class="gtable_row">
    							<div class="gtable_cell reactivate_customer" data-customer-id="<?php echo $customer_deleted['customerId'];?>"><?php echo $customer_deleted['customerName'];?></div>
    						</div>
    						<?php
    					}
    					?>
    				</div>
    			</div>
    			<?php
    		}
    	}
    }
}

if(!$customer_basisconfig['deactivateContactPersonSearch'])
{
	if($search_filter != "")
	{
		?>
	    	<div class="customer-list-table-title"><?php echo $formText_SearchInContactPerson_output;?> <span><?php echo $filtered2Count." ".$formText_Hits_Output; ?></span></div>
			<div class="gtable" id="gtable_search_contact">
				<div class="gtable_row">
			        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CompanyName_output;?></div>
		        	<div class="gtable_cell gtable_cell_head"><?php echo $formText_contactName_output;?></div>
		        	<div class="gtable_cell gtable_cell_head"><?php echo $formText_Mobile_output;?></div>
		        	<div class="gtable_cell gtable_cell_head"><?php echo $formText_Email_output;?></div>
			    </div>
			    <?php
			    foreach($customerContactList as $v_row){
			        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['customerId']."&contactpersonSearch=".$v_row['contactpersonName'];
			        ?>
			        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
				        <div class="gtable_cell  c1"><?php echo $v_row['customerName'];?></div>
			        	<div class="gtable_cell"><?php echo $v_row['contactpersonName'];?></div>
			        	<div class="gtable_cell"><?php echo $v_row['contactpersonMobile'];?></div>
			        	<div class="gtable_cell"><?php echo $v_row['contactpersonEmail'];?></div>
			        </div>
			    <?php } ?>
			</div>
			<?php
			if(count($customerContactList) < $filtered2Count) {
				?><div class="customer-paging"><?php echo $formText_Showing_output ." ". count($customerContactList)." ".$formText_Of_output." ".$filtered2Count;?> <a href="#" class="showNextContact" data-page="<?php echo $contactPage;?>"><?php echo $formText_ShowNext_output;?> 50</a></div><?php
			}
		}
	}
	if(!$customer_basisconfig['deactivateBrregSearch']){
		//include(__DIR__.'/ajax.list_brreg.php');
	}
if(!$rowOnly)
{
    if($list_filter != "not_connected" && ($list_filter != "not_connected_sub")) {
    	if(!isset($_POST['next_page']))
    	{
    		if($search_filter == '' && $totalPagesFiltered > 1)
    		{
    			 ?><div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$filteredCount;?>
    				 <a href="#" class="showMoreCustomersBtn" data-page="<?php echo $page;?>"><?php echo $formText_ShowNext_output.' '.$perPage;?></a>
    			  </div><?php
    		}
    	}
    	if(!isset($_POST['updateOnlyList']) && !isset($_POST['next_page']))
    	{
    		?></div><?php
    	}
    }
	if(!isset($_POST['next_page']))
	{
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
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
                var data = {
                    city_filter: '<?php echo $city_filter;?>',
                    list_filter: '<?php echo $list_filter;?>',
                    sublist_filter: '',
                    search_filter: $('.searchFilter').val(),
                    search_by: $(".searchBy").val(),
                    selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                    activecontract_filter: '<?php echo $activecontract_filter;?>'
                };
				loadView("list", data);
			}
		}
		$(this).removeClass('opened');
	}
};
$(function() {
    $(".subunitCheck").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            cid: $(this).data("customer-id"),
            subunit_filter: $(this).data("subunit-id")
        }
        loadView("details", data);

    })
    $("#unmark_all").off("click").on("click", function(){
        if($(this).is(":checked")){
            $(".unmark").prop("checked", true);
        } else {
            $(".unmark").prop("checked", false);
        }
    })
    $(".cp_connect_customer").off("click").on("click", function(e){
        e.preventDefault();
        var contactpersonIds = [];
        // $(".cp_connect_customer_checkbox").each(function(index, element){
        //     if($(this).is(":checked")){
        //         contactpersonIds.push($(this).val());
        //     }
        // })
        contactpersonIds.push($(this).parent().find(".cp_connect_customer_checkbox").val());
        var data = {
			contactpersonId: contactpersonIds
		};
		ajaxCall('cp_connect_customer', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    })
    $(".cp_connect_customer_all").off("click").on("click", function(e){
        e.preventDefault();
        var contactpersonIds = [];

        $(this).parents(".gtable").find(".cp_connect_customer_checkbox").each(function(index, element){
            contactpersonIds.push($(this).val());
        })
        var data = {
			contactpersonId: contactpersonIds,
            customerName: $(this).data("customername")
		};
		ajaxCall('cp_connect_customer', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    })
    $(".cp_connect_subscription").off("click").on("click", function(e){
        e.preventDefault();
        var contactpersonIds = [];
        contactpersonIds.push($(this).parent().find(".cp_connect_subscription_checkbox").val());
        var data = {
			subscriptionId: contactpersonIds
		};
		ajaxCall('subscription_connect_customer', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    })
    $(".launch_unmark").off("click").on("click", function(e){
        e.preventDefault();
        var customerIds = [];
        $(".unmark").each(function(index, element){
            if($(this).is(":checked")){
                customerIds.push($(this).val());
            }
        })
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: 'marked_for_manual_check',
            sublist_filter: '',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>',
            action:'unmark',
            customerIds: customerIds
        };
        loadView('list', data);
    })
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){

        if(e.target.nodeName == 'DIV' || e.target.nodeName == 'TD'){
			<?php if($b_selection_mode && $totalPagesFiltered == 1) { ?>
			$(this).closest('.gtable_row').find('.selection-switch-btn').trigger('click');
			<?php } else { ?>
			fwAbortXhrPool();
			fw_load_ajax($(this).data('href'),'',true);
    		if($("body.alternative").length == 0) {
				if($(this).parents(".tinyScrollbar.col1")){
					var $scrollbar6 = $('.tinyScrollbar.col1');
					$scrollbar6.tinyscrollbar();

					var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
					scrollbar6.update(0);
				}
			}
			<?php } ?>
		}
	});
	$(document).off('click', '.selection-switch-btn').on('click', '.selection-switch-btn', function(e){
		e.preventDefault();
		if($(this).is('.main'))
		{
			if($(this).parent().find('input').is(':checked')) $('.selection-switch').removeProp('checked');
			else $('.selection-switch').prop('checked', true);
		} else {
			var $input = $(this).parent().find('input');
			if($input.is(':checked'))
			{
				$input.removeProp('checked');
				$('.selection-switch.main').removeProp('checked');
			} else {
				$input.prop('checked', true);
				if($('.selection-switch:not(.main):not(:checked)').length == 0) $('.selection-switch.main').prop('checked', true);
			}
		}
	});
	// $(document).off('mouseenter mouseleave', '.output-access-changer')
	// .on('mouseenter', '.output-access-changer', function(){
	// 	$(this).find(".output-access-dropdown").show();
	// }).on('mouseleave', '.output-access-changer', function(){
	// 	$(this).find(".output-access-dropdown").hide();
	// });
	$(".editInvitationButton").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_invitation";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id') },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
	$(".show_deleted_customers").off("click").on("click", function(){
		$(".deletedCustomersWrapper").toggle();
	})
	$(".reactivate_customer").off("click").on("click", function(){
		ajaxCall('reactivate_customer', { fwajax: 1, fw_nocss: 1, customer_id: $(this).data("customer-id") }, function(json){
			$('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$('.table-sortable').off('click').on('click', function(e){
		e.preventDefault();
		var sort_field = $(this).data('sortable');
		var sort_desc = $(this).is('.sort_asc') ? 1 : 0;
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        sort_field: sort_field,
	        sort_desc: sort_desc,
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	    });
	});
	$(".page-link").on('click', function(e) {
	    e.preventDefault();
		page = $(this).data("page");
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        page: page
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".showMoreCustomersBtn").hide();
	        }

	    });
    });
	$(".brreg-page-link").on('click', function(e) {
	    e.preventDefault();
		page = $(this).data("page");
		$(this).data("page", (page+1));
	    var data = {
	        search_brreg: $('.searchFilter').val(),
	        page: page
	    };
	    ajaxCall('list_brreg', data, function(json) {
	        $('.p_pageContent .gtable.brreg').append(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".brreg-page-link").hide();
	        }
	    });
    });
	$('.output-merge-customer').off('click').on('click', function(e) {
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
	$(".customer-paging .showNextCustomer").on('click', function(e) {
	    e.preventDefault();
		customerPage = $(this).data("page") + 1;
		$(this).data("page", (customerPage));
		contactPage = $(".customer-paging .showNextContact").data("page");
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	       	customerPage: customerPage,
	       	contactPage: contactPage
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".customer-paging .showNextCustomer").hide();
	        }
	    });
    });
	$(".customer-paging .showNextContact").on('click', function(e) {
	    e.preventDefault();
		contactPage = $(this).data("page")+1;
		$(this).data("page", (contactPage));
		customerPage = $(".customer-paging .showNextCustomer").data("page");
	    var data = {
	        building_filter:$(".buildingFilter").val(),
	        customergroup_filter: $(".customerGroupFilter").val(),
	        list_filter: '<?php echo $list_filter; ?>',
	        search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
	        contactPage: contactPage,
	       	customerPage: customerPage
	    };
	    ajaxCall('list', data, function(json) {
	        $('.p_pageContent').html(json.html);
	        if(json.html.replace(" ", "") == ""){
	            $(".customer-paging .showNextContact").hide();
	        }
	    });
    });
    $('.showMoreCustomersBtn').on('click', function(e) {
        e.preventDefault();
		var _this = this;
		page = parseInt($(this).data("page"))+1;
		$(this).data("page", page);
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            next_page: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html(/*$(_this).closest('.resultTableWrapper').find*/$(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
	$('.export-selected').on('click', function(e) {
        e.preventDefault();
		var selected = [];
		$('.selection-switch:not(.main):checked').each(function(index, obj){
			selected.push(obj.value);
		});
		if(selected.length > 0)
		{
			var data = {
				fwajax: 1,
				fw_nocss: 1,
				selected: selected
			};
			submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=export_selected"; ?>', data);
		} else {
			alert("<?php echo $formText_NoneSelected_Output;?>");
		}
    });
	$('.export-filtered').on('click', function(e) {
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val()
        };
		ajaxCall('export_configuration', data, function(json){
			$('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    });
    $('.export-filtered-subscription').on('click', function(e) {
        e.preventDefault();
        var data = {
			fwajax: 1,
            fw_nocss: 1,
            list_filter: '<?php echo $list_filter;?>'
        };
		submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."/../../modules/Customer2/output/includes/ajax.export_subscriptions.php"; ?>', data);
    });
	$('.export-script').on('click', function(e) {
        e.preventDefault();
        var data = {
			fwajax: 1,
            fw_nocss: 1,
        };
		submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."/../../modules/Customer2/output_export_scripts/UniEconomy/export.php"; ?>', data);
    });

	$(".create-prospects").on('click', function(e){
		var selected = [];
		$('.selection-switch:not(.main):checked').each(function(index, obj){
			selected.push(obj.value);
		});
		if(selected.length > 0)
		{
			ajaxCall('create_prospects', { fwajax: 1, fw_nocss: 1, selected: selected }, function(json){
				$('#popupeditboxcontent').html('').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		} else {
			alert("<?php echo $formText_NoneSelected_Output;?>");
		}
	});
    $(".show_suggested").off("click").on("click", function(e){
        e.preventDefault();
		var data = {
			cid: $(this).data('customer-id')
		};
		ajaxCall('show_suggested', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
    })
});
function submit_post_via_hidden_form(url, params) {
    var f = $("<form method='POST' target='_blank' style='display:none;'></form>").attr({
        action: url
    }).appendTo(document.body);
    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            $('<input type="hidden" />').attr({
                name: i,
                value: params[i]
            }).appendTo(f);
        }
    }
    f.submit();
    f.remove();
}
</script>
<?php
	}
}
?>

<div class="clear"></div>
