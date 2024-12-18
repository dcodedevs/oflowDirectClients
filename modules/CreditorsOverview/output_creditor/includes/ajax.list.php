<?php
if(isset($_POST['list_filter'])) $_GET['list_filter'] = $_POST['list_filter'];
if(isset($_POST['search_filter'])) $_GET['search_filter'] = $_POST['search_filter'];

if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = $default_list; }
if(isset($_GET['search_filter'])){ $search_filter = $_GET['search_filter']; } else { $search_filter = ''; }

$_SESSION['list_filter'] = $list_filter;
$_SESSION['search_filter'] = $search_filter;

require_once __DIR__ . '/functions.php';

/*if($list_filter == "all"){
	$itemCount = get_customer_list_count($o_main, 'all', $city_filter, $search_filter,  $activecontract_filter, $selfdefinedfield_filter, $search_by);
} else if($list_filter == "with_orders"){
	$itemCount = get_customer_list_count($o_main, 'with_orders', $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
} else {
	$itemCount = get_customer_list_count($o_main, $list_filter."sublistSeperator".$sublist_filter, $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
    if($list_filter == "selfregistered"){
        $selfregistered_unhandled_count = get_customer_list_count($o_main, $list_filter."sublistSeperatorselfregistered_unhandled", $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
        $selfregistered_handled_count = get_customer_list_count($o_main, $list_filter."sublistSeperatorselfregistered_handled", $city_filter, $search_filter, $activecontract_filter, $selfdefinedfield_filter, $search_by);
    }
}*/
$itemCount = get_customer_list_count($o_main, 'all', $search_filter);

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

$showDefaultList = false;
if($list_filter == "all" && $o_nums2 > 0) {
	$showDefaultList = true;
}
if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

if($search_filter != "") {
	$customerPage = intval($_POST['customerPage']);
	if($customerPage <= 0){
		$customerPage = 1;
	}
	$customerList = get_customer_list($o_main, $list_filter, $search_filter, $customerPage, $perPage);
} else {
	$customerList = get_customer_list($o_main, $list_filter, $search_filter, $page, $perPage);
}

$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$filteredCount = $currentCount;
if($search_filter != ""){
	$filteredCount = get_customer_list_count2($o_main, $list_filter, $search_filter, 1);
	$totalPagesFiltered = ceil($filteredCount/$perPage);
} else {
	$totalPagesFiltered = $totalPages;
}

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
	if(!isset($_POST['next_page']))
	{
		if($search_filter != "")
		{
			?><div class="customer-list-table-title"><?php echo $formText_SearchInCreditor_output;?> <span><?php echo $filteredCount." ".$formText_Hits_Output; ?></span></div><?php
		}
		echo '<div class="gtable" id="gtable_search">';
		echo '<div class="gtable_row">';
		?>
		<div class="gtable_cell gtable_cell_head c10"><?php echo $formText_CreditorId_output;?></div>
		<div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Name_output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Street_output;?></div>
		<div class="gtable_cell gtable_cell_head c15"><?php echo $formText_City_output;?></div>
		<div class="gtable_cell gtable_cell_head c10"><?php echo $formText_CustomerId_output;?></div>
		<?php
		echo '</div>';
	}
	$subunits = array(array("id"=>0));
	foreach($customerList as $v_row)
	{
		foreach($subunits as $subunit){
			$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_creditor&inc_obj=details&cid=".$v_row['id'];
			?>
			<div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
				<div class="gtable_cell c10"><?php echo $v_row['id'];?></div>
				<div class="gtable_cell c1"><?php echo $v_row['companyname'];?></div>
				<div class="gtable_cell"><?php echo $v_row['companypostalbox'];?></div>
				<div class="gtable_cell c15"><?php echo $v_row['companypostalplace'];?></div>
				<div class="gtable_cell c10"><?php echo $v_row['customer_id'];?></div>
			</div>
			<?php
		}
	}
	if(!isset($_POST['next_page'])) echo '</div>';


	if($search_filter != ""){
		if(count($customerList) < $filteredCount)
		{
			if(!isset($_POST['next_page']))
			{
				?><div class="customer-paging"><?php echo $formText_Showing_output ." ". count($customerList)." ".$formText_Of_output." ".$filteredCount;?> <a href="#" class="showNextCustomer" data-page="<?php echo $customerPage;?>"><?php echo $formText_ShowNext_output;?> 50</a></div><?php
			}
		}
	}
}

/*if(!$customer_basisconfig['deactivateContactPersonSearch'])
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
}*/
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
		}
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
