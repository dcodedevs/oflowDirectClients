<?php
$s_sql = "SELECT * FROM customer_listtabs_basisconfig ORDER BY sortnr";
$o_query = $o_main->db->query($s_sql);
$customer_listtabs_basisconfig = ($o_query ? $o_query->result_array() : array());

$s_sql = "select * from customer_basisconfig";
$o_query = $o_main->db->query($s_sql);
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "select * from customer_accountconfig";
$o_query = $o_main->db->query($s_sql);
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

require_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

$default_list = "all";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = $default_list; }
if($_GET['sublist_filter'] != ''){ $sublist_filter = $_GET['sublist_filter']; } else { $sublist_filter = $default_sublist; }
//$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : $default_list;

$all_count = $with_orders_count = '<img src="'.$extradir.'/output/elementsOutput/ajax-loader.gif"/>';
if($list_filter == "all"){
    $all_count = $itemCount;
}
if(count($customer_listtabs_basisconfig) == 0) {
    if($list_filter == "with_orders"){
        $with_orders_count = $itemCount;
    }
}
?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'all' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="all" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=all"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $all_count; ?></span>
                    <?php echo $formText_AllCustomers_output;?>
                </span>
            </a>
        </li>
    <?php
    if(count($customer_listtabs_basisconfig) > 0) {
    	foreach($customer_listtabs_basisconfig as $customer_listtab) {
            $itemCount2 = '<img src="'.$extradir.'/output/elementsOutput/ajax-loader.gif"/>';
            if($list_filter == $customer_listtab['id']){
        		$itemCount2 = $itemCount;
            }
    		?>
    		<li class="item<?php echo ($list_filter == $customer_listtab['id'] ? ' active':'');?>">
	            <a class="topFilterlink" data-listfilter="<?php echo $customer_listtab['id']?>" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=".$customer_listtab['id'].""; ?>">
	                <span class="link_wrapper">
	                    <span class="count"><?php echo $itemCount2;?></span>
	                    <?php echo $customer_listtab['tabname'];?>
	                </span>
	            </a>
	        </li>
    		<?php
    	}
    	?>

    <?php } else { ?>
        <li class="item<?php echo ($list_filter == 'with_orders' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="with_orders"  href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=with_orders"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $with_orders_count; ?></span>
                    <?php echo $formText_CustomersWithOrders_output;?>
                </span>
            </a>
        </li>
    <?php } ?>
    <?php
    if($customer_basisconfig['activateContactPersonTab']) { ?>
        <li class="item<?php echo ($list_filter == 'contactperson_tab' ? ' active':'');?>">
            <a class="goToContactPersonTab" data-listfilter="contactperson_tab" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_contactperson&list_filter=contactperson_tab"; ?>">
                <span class="link_wrapper">
                    <span class="count"><img src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span>
                    <?php echo $formText_ContactPersons_output;?>
                </span>
            </a>
        </li>
    <?php } ?>
    <?php
    if($customer_basisconfig['activateGroupTab']) { ?>
        <li class="item<?php echo ($list_filter == 'group_tab' ? ' active':'');?>">
            <a class="goToGroupTab" data-listfilter="group_tab" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=group_tab"; ?>">
                <span class="link_wrapper">
                    <span class="count"><img src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span>
                    <?php echo $formText_Group_output;?>
                </span>
            </a>
        </li>
    <?php } ?>
        <li class="item<?php echo ($list_filter == 'deleted' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="deleted" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=deleted"; ?>">
                <span class="link_wrapper">
                    <span class="count"><img src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span>
                    <?php echo $formText_Deleted_output;?>
                </span>
            </a>
        </li>
    <?php if($v_customer_accountconfig['activateSelfregistered']) { ?>
        <li class="item<?php echo ($list_filter == 'selfregistered' ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="selfregistered" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=selfregistered"; ?>">
                <span class="link_wrapper">
                    <span class="count"><img src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span>
                    <?php echo $formText_Selfregistered_output;?>
                </span>
            </a>
        </li>
    <?php } ?>
    <?php
    $s_sql = "select * from subscriptiontype ORDER BY name ASC";
    $o_query = $o_main->db->query($s_sql);
    $subscriptiontypes = $o_query ? $o_query->result_array() : array();
    foreach($subscriptiontypes as $subscriptiontype) {
        ?>
        <li class="item<?php echo ($list_filter == 'group_by_subscriptiontype_'.$subscriptiontype['id'] ? ' active':'');?>">
            <a class="topFilterlink" data-listfilter="group_by_subscriptiontype_<?php echo $subscriptiontype['id']; ?>" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=subscriptiontype_".$subscriptiontype['id']; ?>">
                <span class="link_wrapper">
                    <span class="count"><img src="<?php echo $extradir;?>/output/elementsOutput/ajax-loader.gif"/></span>
                    <?php echo $subscriptiontype['name'];?>
                </span>
            </a>
        </li>
        <?php
    }
    ?>
    </ul>
</div>
<?php

$s_sql = "SELECT * FROM customer WHERE marked_for_manual_check = 1 AND content_status < 2 ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$markedCount = $o_query ? $o_query->num_rows() : 0;

$s_sql = "SELECT * FROM contactperson WHERE (customerId is null OR customerId = 0) AND type = 1 AND content_status < 2 ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$notConnectedContactPersonCount = $o_query ? $o_query->num_rows() : 0;

$s_sql = "SELECT * FROM subscriptionmulti LEFT OUTER JOIN customer ON customer.id = subscriptionmulti.customerId WHERE customer.id is null AND subscriptionmulti.content_status < 2 AND (subscriptionmulti.collecting_task is null OR subscriptionmulti.collecting_task = 0)";
$o_query = $o_main->db->query($s_sql);
$notConnectedSubscriptionCount = $o_query ? $o_query->num_rows() : 0;
if($markedCount > 0) { ?>
    <div class="view_marked_for_manual"><?php echo $formText_MarkedForManualCheck_output." (".$markedCount.")";?></div>
    <br/>
<?php } ?>
<?php if($notConnectedContactPersonCount > 0) { ?>
    <div class="view_not_connected <?php if($list_filter == "not_connected") echo 'active';?>"><?php echo $formText_NotConnectedContactpersons_output." (".$notConnectedContactPersonCount.")";?></div>
    <br/>
<?php } ?>
<?php if($notConnectedSubscriptionCount > 0) { ?>
    <div class="view_not_connected_sub <?php if($list_filter == "not_connected_sub") echo 'active';?>"><?php echo $formText_NotConnectedSubscriptions_output." (".$notConnectedSubscriptionCount.")";?></div>
    <br/>
<?php } ?>
<br/>
<div class="clear"></div>
<?php
if($list_filter == "selfregistered"){
    ?>
    <div class="output-filter">
        <ul>
            <li class="item<?php echo ($sublist_filter == 'selfregistered_unhandled' ? ' active':'');?>">
                <a class="topSubFilterlink" data-sublistfilter="selfregistered_unhandled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=selfregistered&sublist_filter=selfregistered_unhandled"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $selfregistered_unhandled_count; ?></span>
                        <?php echo $formText_Unhandled_output;?>
                    </span>
                </a>
            </li>
            <li class="item<?php echo ($sublist_filter == 'selfregistered_handled' ? ' active':'');?>">
                <a class="topSubFilterlink" data-sublistfilter="selfregistered_handled" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=selfregistered&sublist_filter=selfregistered_handled"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $selfregistered_handled_count; ?></span>
                        <?php echo $formText_Handled_output;?>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    <?php
}
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "total"; }

if(!isset($differentFilter)){
?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
    <?php
        if($customer_basisconfig['enableActiveContractFilter']){
            $s_sql = "SELECT id, ".$customer_basisconfig['contractfilterField']." FROM ".$customer_basisconfig['contractfilterTable']." WHERE id = ?";
            $o_query = $o_main->db->query($s_sql, array($activecontract_filter));
            $unit = $o_query ? $o_query->row_array():array();
            ?>
            <div class="filterLine">
                <div class="lineInput">
                    <select name="activecontract_filter" class="activecontract_filter">
                        <option value=""><?php echo $formText_All_output;?></option>
                        <?php
                        $cities = array();
                        $s_sql = "SELECT id, ".$customer_basisconfig['contractfilterField']." FROM ".$customer_basisconfig['contractfilterTable']." GROUP BY id ORDER BY ".$customer_basisconfig['contractfilterField']." ASC";
                        $o_query = $o_main->db->query($s_sql);
                        if($o_query && $o_query->num_rows()>0) {
                            $cities = $o_query->result_array();
                        }
                        foreach($cities as $city){
                        ?>
                            <option value="<?php echo $city['id']?>" <?php echo $city['id'] == $activecontract_filter ? 'selected="selected"' : ''; ?>>
                                <?php echo $city[$customer_basisconfig['contractfilterField']]; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <?php
        }
        ?>
        <div class="filterLine">
        	<a href="#" class="openFilterPopup">
                <?php echo $formText_Filter_output;?>
            </a>
        </div>
        <div class="filteredWrapper">
            <?php
            if($city_filter != ""){
                ?>
                <div class="filteredRow">
                    <div class="filteredLabel"><?php echo $formText_City_output;?>:</div>
                    <div class="filteredValue"><?php echo $city_filter;?></div>
                    <div class="filterRemove" data-removefilter="city">x</div>
                    <div class="clear"></div>
                </div>
                <?php
            }
            if($selfdefinedfield_filter != "") {
                foreach ($selfdefinedfield_filter as $selfdefinedfieldId => $selfdefinedfieldValue) {
                    $selfdefinedfieldValueArray = explode(",", $selfdefinedfieldValue);
                    $selfdefinedfieldValueCount = 0;
                    foreach ($selfdefinedfieldValueArray as $selfdefinedfieldSingle) {
                        if($selfdefinedfieldSingle != ""){
                            $selfdefinedfieldValueCount++;
                        }
                    }
                    if($selfdefinedfieldValueCount > 0){
                        $s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
                        $o_query = $o_main->db->query($s_sql, array($selfdefinedfieldId));
                        if($o_query && $o_query->num_rows()>0){
                            $selfdefinedfield = $o_query->row_array();
                        }
                        foreach ($selfdefinedfieldValueArray as $selfdefinedfieldSingle) {
                            if($selfdefinedfieldSingle != ""){
                                $selfdefinedfieldVisibleText = "";
                                if($selfdefinedfield['type'] == 0){
                                    if($selfdefinedfield['hide_textfield']){
            							$s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
            							LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
            							WHERE customer_selfdefined_field_id = ?";
            							$o_query = $o_main->db->query($s_sql, array($selfdefinedfield['id']));
            							$selfdefinedLists = $o_query ? $o_query->result_array() : array();
                                    } else {
                                        $selfdefinedLists = array();
                                    }
                                    if(count($selfdefinedLists) > 0){
                                        $s_sql = "SELECT customer_selfdefined_list_lines.* FROM customer_selfdefined_list_lines  WHERE customer_selfdefined_list_lines.id = ?";
                                        $o_query = $o_main->db->query($s_sql, array($selfdefinedfieldSingle));
                                        if($o_query && $o_query->num_rows()>0){
                                            $listItem = $o_query->row_array();
                                            $selfdefinedfieldVisibleText = $listItem['name'];
                                        }
                                    } else {
                                        $selfdefinedfieldVisibleText = $formText_Checked_output;
                                    }
                                } else if($selfdefinedfield['type'] == 1){
                                    $s_sql = "SELECT customer_selfdefined_list_lines.* FROM customer_selfdefined_list_lines  WHERE customer_selfdefined_list_lines.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($selfdefinedfieldSingle));
                                    if($o_query && $o_query->num_rows()>0){
                                        $listItem = $o_query->row_array();
                                        $selfdefinedfieldVisibleText = $listItem['name'];
                                    }
                                } else if($selfdefinedfield['type'] == 2){
                                    $s_sql = "SELECT customer_selfdefined_list_lines.* FROM customer_selfdefined_list_lines  WHERE customer_selfdefined_list_lines.id = ?";
                                    $o_query = $o_main->db->query($s_sql, array($selfdefinedfieldSingle));
                                    if($o_query && $o_query->num_rows()>0){
                                        $listItem = $o_query->row_array();
                                        $selfdefinedfieldVisibleText = $listItem['name'];
                                    }
                                }
                            ?>
                                <div class="filteredRow">
                                    <div class="filteredLabel"><?php echo $selfdefinedfield['name'];?>:</div>
                                    <div class="filteredValue"><?php echo $selfdefinedfieldVisibleText;?></div>
                                    <div class="filterRemove" data-removefilter="selfdefinedfield" data-selfdefinedfieldid="<?php echo $selfdefinedfield['id']?>" data-selfdefinedfieldvalue="<?php echo $selfdefinedfieldSingle?>">x</div>
                                    <div class="clear"></div>
                                </div>
                                <?php
                            }
                        }
                    }
                }
            }
            if($search_filter != ""){
                ?>
                <div class="filteredRow">
                    <div class="filteredLabel"><?php echo $formText_Search_output;?>:</div>
                    <div class="filteredValue"><?php echo $search_filter;?></div>
                    <div class="filterRemove" data-removefilter="search">x</div>
                    <div class="clear"></div>
                </div>
                <?php
            }
            ?>
            <div class="clear"></div>
        </div>
        <?php /*
		<div class="filterLine">
			<a href="#" class="switchSelectionMode">
                <?php echo (!$b_selection_mode ? $formText_SwitchToExportMode_Output : $formText_SwitchToNormalListMode_Output);?>
            </a>
        </div>*/

		if($b_selection_mode && $totalPagesFiltered == 1)
		{
			?><div><a href="#" class="create-prospects"><?php echo $formText_CreateProspectsForSelectedCustomers_Output;?></a></div><?php
			?><div><a href="#" class="export-selected"><?php echo $formText_ExportSelectedCustomers_Output;?></a></div><?php
		}
		?>
		<div><a href="#" class="export-filtered"><?php echo $formText_ExportFilteredCustomers_Output;?></a></div>
		<?php
		if($v_customer_accountconfig['activate_customer_export_by_script'] && $v_customer_accountconfig['customer_export_by_script_path'] != "") {
			?>
			<div><a href="#" class="export-script"><?php echo $formText_ExportFor_Output." ".basename(dirname($v_customer_accountconfig['customer_export_by_script_path']));?></a></div>
		<?php } ?>

        <?php if(strpos($list_filter, "group_by_subscriptiontype_") !== false) { ?>
    		<div><a href="#" class="export-filtered-subscription"><?php echo $formText_ExportSubscriptions_Output;?></a></div>
    		<?php
        }

        ?>

    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>" autocomplete="off">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
        <?php

        $s_sql = "SELECT * FROM customer_view_history WHERE customer_view_history.username = ?";
        $o_query = $o_main->db->query($s_sql, array($variables->loggID));
        $customer_view_history = $o_query ? $o_query->row_array() : array();
        $customerHistory = json_decode($customer_view_history['history_log'], true);
        $customerHistoryOrdered = array_reverse($customerHistory);
        if(count($customerHistoryOrdered) > 0) { ?>
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
    </div>
    <div class="clear"></div>
    <?php if($list_filter != "not_connected" && $list_filter != "not_connected_sub") { ?>
        <div class="filteredCountRow">
            <span class="selectionCount"><?php echo $filteredCount;?></span> <?php echo $formText_InSelection_output;?>
            <?php
            if($city_filter != "" || $search_filter != "" || $activecontract_filter != "" || $selfdefinedfield_filter != ""){
            ?>
                <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<style>
    .filteredCountRow {
    	padding: 5px 15px;
    }
    .filteredCountRow .resetSelection {
    	float: right;
    	width: 290px;
    	cursor: pointer;
    }
    .filteredWrapper {
        margin-top: 10px;
    }
    .filterLine {
        display: inline-block;
        vertical-align: middle;
        margin-right: 15px;
    }
    .p_tableFilter_left {
        max-width: 60%;
        float: left;
    }
    .p_tableFilter_right {
        float: right;
    }
    .filteredRow {
        margin-top: 5px;
        margin-right: 5px;
        float: left;
        border: 1px solid #23527c;
        padding: 2px 5px;
        border-radius: 3px;
    }
    .filteredRow .filteredLabel{
        float: left;
    }
    .filteredRow .filteredValue{
        float: left;
        margin-left: 3px;
    }
    .filteredRow .filterRemove {
        float: right;
        font-size: 10px;
        line-height: 14px;
        margin-left: 10px;
        padding: 0px 3px 1px;
        cursor: pointer;
        color: #23527c;
    }
</style>
<?php } ?>
<style>
.cp_connect_customer {
	color: #46b2e2;
	cursor: pointer;
}
.cp_connect_customer_all {
	color: #46b2e2;
	cursor: pointer;
}
.cp_connect_subscription {
	color: #46b2e2;
	cursor: pointer;
}
.view_marked_for_manual {
	float: right;
	color: red;
	cursor: pointer;
	margin-right: 10px;
}
.view_not_connected {
	float: right;
	color: red;
	cursor: pointer;
	margin-right: 10px;
}
.view_not_connected_sub {
	float: right;
	color: red;
	cursor: pointer;
	margin-right: 10px;
}

.view_not_connected.active {
	text-decoration: underline;
}
.view_not_connected_sub.active {
	text-decoration: underline;
}
.show_suggested {
	cursor: pointer;
	color: #46b2e2;
	display: inline-block;
	vertical-align: middle;
}
.launch_unmark {
	float: right;
	color: #fff;
	background: #0497e5;
	border: 1px solid #0497e5;
	cursor: pointer;
	padding: 10px 20px;
	margin-top: 10px;
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
<script type="text/javascript">
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
    // Filter by building
    // $('.customerGroupFilter').on('change', function(e) {
    //     var data = {
    //         building_filter: $('.buildingFilter').val(),
    //         customergroup_filter: $(this).val(),
    //         list_filter: '<?php echo $list_filter; ?>',
    //         search_filter: $('.searchFilter').val()
    //     };
    //     ajaxCall('list', data, function(json) {
    //         $('.p_pageContent').html(json.html);
    //     });
    // });
    $(".openFilterPopup").unbind("click").bind("click", function(e){
    	e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter; ?>'
        };
        ajaxCall('filter', data, function(json) {
           	$('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    })
    $(".view_marked_for_manual").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: 'marked_for_manual_check',
            sublist_filter: '',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>'
        };
        loadView('list', data);
    })
    $(".view_not_connected").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: 'not_connected',
            sublist_filter: '',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>'
        };
        loadView('list', data);
    })
    $(".view_not_connected_sub").off("click").on("click", function(e){
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: 'not_connected_sub',
            sublist_filter: '',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>'
        };
        loadView('list', data);
    })
	$(".switchSelectionMode").unbind("click").bind("click", function(e){
    	e.preventDefault();
        loadView('list', {selection_mode: <?php echo (!$b_selection_mode ? 1 : 2);?>});
    })
    <?php if(isset($differentFilter)) { ?>
        $(".topFilterlink").unbind("click").bind("click", function(e){
            e.preventDefault();
            var href = $(this).attr("href");
            fw_load_ajax(href, false, true);
        })

    <?php } else {?>
        $(".topFilterlink").unbind("click").bind("click", function(e){
            e.preventDefault();
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: $(this).data("listfilter"),
                sublist_filter: $(this).data("sublistfilter"),
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            loadView('list', data);
        })
        $(".topSubFilterlink").unbind("click").bind("click", function(e){
            e.preventDefault();
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter;?>',
                sublist_filter: $(this).data("sublistfilter"),
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            loadView('list', data);
        })
    <?php } ?>

    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
            selfdefinedfield_filter: '',
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    $(".goToContactPersonTab").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_contactperson&inc_obj=list&list_filter=contactperson_tab"; ?>', false, true);
    });
    $(".goToGroupTab").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_groups&inc_obj=list&list_filter=group_tab"; ?>', false, true);
    });
    $(".activecontract_filter").change(function(){
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: '<?php echo $list_filter;?>',
            search_filter: '<?php echo $search_filter;?>',
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: $(".activecontract_filter").val()
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    })
    $(".filterRemove").unbind("click").bind("click", function(e){
        var removeFilter = $(this).data("removefilter");
        e.preventDefault();

        var data = {};
        if(removeFilter != "city"){
            data.city_filter= '<?php echo $city_filter;?>';
        }
        if(removeFilter != "list"){
            data.list_filter= '<?php echo $list_filter;?>';
        }
        if(removeFilter != "search"){
            data.search_filter= '<?php echo $search_filter;?>';
        }
        if(removeFilter != "activecontract"){
            data.activecontract_filter= '<?php echo $activecontract_filter;?>';
        }
        if(removeFilter == "selfdefinedfield"){
            var removeSelfdefinedFieldId = $(this).data("selfdefinedfieldid");
            var removeSelfdefinedFieldValue = $(this).data("selfdefinedfieldvalue");
            var selfdefinedfield_filter_old =  <?php echo json_encode($selfdefinedfield_filter);?>;
            var selfdefinedfield_filter_new = {};
            $.each(selfdefinedfield_filter_old, function(index, value){
                if(index != removeSelfdefinedFieldId) {
                    selfdefinedfield_filter_new[index] = value;
                } else {
                    var myarr = value.split(",");
                    var newArray = new Array();
                    $.each(myarr, function(index2, value2){
                        if(value2 != removeSelfdefinedFieldValue) {
                            newArray.push(value2);
                        }
                    });
                    var newString = newArray.join(",");
                    selfdefinedfield_filter_new[index] = newString;
                }
            })

            data.selfdefinedfield_filter = btoa(JSON.stringify(selfdefinedfield_filter_new));
        } else {
            data.selfdefinedfield_filter = '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>';
        }

        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    })
    $(".searchFilter").keyup(function(e){
        if (e.key === "Enter")
		{
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                updateOnlyList: true,
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            ajaxCall('list', data, function(json) {
                if($(".resultTableWrapper").length > 0){
                    $('.resultTableWrapper').html(json.html);
                } else {
                    $('.p_pageContent').html(json.html);
                }
            });
        }
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            city_filter: '<?php echo $city_filter;?>',
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
            activecontract_filter: '<?php echo $activecontract_filter;?>'
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    $(function(){
        function loadTabNumber(tab_id){
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                sublist_filter: '<?php echo $sublist_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                tab_id: tab_id,
                activecontract_filter: '<?php echo $activecontract_filter;?>',
                abortable: 1
            };
            ajaxCall({module_file:'getTabNumbers', module_name: 'Customer2&abortable=1', module_folder: 'output'}, data, function(json) {
                $('.topFilterlink[data-listfilter="'+tab_id+'"] .count').html(json.html);
            }, false);
        }
        <?php if($list_filter != "all") { ?>
            loadTabNumber("all");
        <?php } ?>
        <?php if(count($customer_listtabs_basisconfig) > 0) {
            foreach($customer_listtabs_basisconfig as $customer_listtab) {

                if($list_filter != $customer_listtab["id"]) {
                ?>
                    loadTabNumber(<?php echo $customer_listtab["id"]?>);
                <?php
                }
            }
        } else {
            if($list_filter != "with_orders") {
                ?>
                loadTabNumber("with_orders");
                <?php
            }
        }?>

        <?php if($customer_basisconfig['activateContactPersonTab']) {?>
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                tab_id: 'contactperson_tab',
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            ajaxCall({module_file:'getTabNumbers', module_name: 'Customer2&abortable=1', module_folder: 'output_contactperson'}, data, function(json) {
                $('.goToContactPersonTab[data-listfilter="contactperson_tab"] .count').html(json.html);
            }, false);
        <?php } ?>
        <?php if($customer_basisconfig['activateGroupTab']) { ?>
            var data = {
                city_filter: '<?php echo $city_filter;?>',
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                search_by: $(".searchBy").val(),
                selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                tab_id: 'group_tab',
                activecontract_filter: '<?php echo $activecontract_filter;?>'
            };
            ajaxCall({module_file:'getTabNumbers', module_name: 'Customer2&abortable=1', module_folder: 'output_groups'}, data, function(json) {
                $('.goToGroupTab[data-listfilter="group_tab"] .count').html(json.html);
            }, false);
        <?php } ?>

        loadTabNumber("deleted");

        <?php if($v_customer_accountconfig['activateSelfregistered']) { ?>
            loadTabNumber("selfregistered");
        <?php } ?>
        <?php
        foreach($subscriptiontypes as $subscriptiontype) {
            ?>
            loadTabNumber("group_by_subscriptiontype_"+<?php echo $subscriptiontype['id'];?>);
            <?php
        }
        ?>
    })
</script>
