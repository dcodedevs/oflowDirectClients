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

//require_once("fnc_rewritebasisconfig.php");
//rewriteCustomerBasisconfig();

$default_list = "all";
if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = $default_list; }
$list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : $default_list;

if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "total"; }

?>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <div class="filterLine">
        </div>
        <div class="filteredWrapper">
            <?php
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

		/*if($b_selection_mode && $totalPagesFiltered == 1)
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
        }*/

        ?>

    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" value="<?php echo $search_filter;?>" autocomplete="off">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
        <?php

        /*$s_sql = "SELECT * FROM customer_view_history WHERE customer_view_history.username = ?";
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
        <?php }*/ ?>
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
    $(".resetSelection").on('click', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter;?>',
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
    $(".filterRemove").unbind("click").bind("click", function(e){
        var removeFilter = $(this).data("removefilter");
        e.preventDefault();

        var data = {};
        if(removeFilter != "list"){
            data.list_filter= '<?php echo $list_filter;?>';
        }
        if(removeFilter != "search"){
            data.search_filter= '<?php echo $search_filter;?>';
        }
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    })
    $(".searchFilter").keyup(function(){
        delay(function(){
            var data = {
                list_filter: '<?php echo $list_filter; ?>',
                search_filter: $('.searchFilter').val(),
                //search_by: $(".searchBy").val(),
                updateOnlyList: true
            };
            ajaxCall('list', data, function(json) {
                if($(".resultTableWrapper").length > 0){
                    $('.resultTableWrapper').html(json.html);
                } else {
                    $('.p_pageContent').html(json.html);
                }
            });
        }, 500 );
    });
    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val()
            //search_by: $(".searchBy").val(),
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent').html(json.html);
        });
    });
</script>
