<?php
$default_list = "payments";
// if(count($customer_listtabs_basisconfig) > 0) {
// 	$default_list = $customer_listtabs_basisconfig[0]['id'];
// }
$s_sql = "SELECT * FROM employee WHERE email = ?";
$o_query = $o_main->db->query($s_sql, array($variables->loggID));
if($o_query && $o_query->num_rows()>0){
    $currentEmployee = $o_query->row_array();
}

$filtersList = array("search_filter","search_by", "responsibleperson_filter");

if ($_POST['list_filter']) $_GET['list_filter'] = $_POST['list_filter'];
foreach($filtersList as $filterName){
	if ($_POST[$filterName]) $_GET[$filterName] = $_POST[$filterName];
}

$list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : $default_list;
foreach($filtersList as $filterName){
	${$filterName} = $_GET[$filterName] ? ($_GET[$filterName]) : '';
}
if($responsibleperson_filter == ''){
    $responsibleperson_filter = $currentEmployee['id'];
}

$_SESSION['list_filter'] = $list_filter;
foreach($filtersList as $filterName){
	$_SESSION[$filterName] = ${$filterName};
}

$filters = array();
foreach($filtersList as $filterName){
	if($filterName == "search_filter"){
		$filters[$filterName] = array($search_by, ${$filterName});
	} else if($filterName != "search_by") {
		$filters[$filterName] = ${$filterName};
	}
}
require_once __DIR__ . '/functions.php';
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "SELECT * FROM moduledata WHERE name = 'Orders'";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $orders_module_id_find = $o_query->row_array();
    $orders_module_id = $orders_module_id_find["uniqueID"];
}

$itemCount = get_customer_list_count($o_main, $list_filter, $filters);

if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;
$showing = $page * $perPage;
$showMore = false;
$currentCount = $itemCount;

if($showing < $currentCount){
	$showMore = true;
}
$totalPages = ceil($currentCount/$perPage);

$customerList = get_customer_list($o_main, $list_filter, $filters, $page, $perPage);

if($list_filter == "settlements") {
    $sql = "SELECT * FROM collectingcompany_settlement cs WHERE cs.content_status < 2 ORDER BY cs.date DESC, cs.id DESC";
    $o_query = $o_main->db->query($sql);
    $last_settlement = $o_query ? $o_query->row_array() : array();
}
$_SESSION['listpagePerPage'] = $perPage;
$_SESSION['listpagePage'] = $page;

$s_sql = "SELECT * FROM accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
?>
<?php if (!$rowOnly) { ?>
	<?php if(!$_POST['updateOnlyList']){?>
		<?php include(__DIR__."/list_filter.php"); ?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){?>
	<div class="resultTableWrapper">
	<?php } ?>
	<div class="gtable" id="gtable_search">
        <?php if($list_filter == "payments") { ?>
	    <div class="gtable_row">
	        <div class="gtable_cell gtable_cell_head c1"><input type="checkbox" class="selectAll"/></div>
	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_CollectingCase_output;?></div>
	        <div class="gtable_cell gtable_cell_head rightAlign"><?php echo $formText_Amount_output;?></div>
	    </div>
        <?php } else if($list_filter == "settlements") {?>
            <div class="gtable_row">
    	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_CollectingCompanyAmount_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_CreditorAmount_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_DebitorAmount_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_PayoutFile_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"></div>
    	    </div>
        <?php } ?>
<?php } ?>
    <?php
    $summaryAmount = 0;
    foreach($customerList as $v_row){
        if($list_filter == "payments") {

            $s_sql = "SELECT collecting_company_cases.*, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as debitorName FROM collecting_company_cases
            LEFT OUTER JOIN customer ON customer.id = collecting_company_cases.debitor_id
            WHERE collecting_company_cases.content_status < 2 AND collecting_company_cases.id = ? ORDER BY collecting_company_cases.id ASC";
            $o_query = $o_main->db->query($s_sql, array($v_row['collecting_case_id']));
            $collectingCase = $o_query ? $o_query->row_array() : array();
            ?>
            <div class="gtable_row" data-href="<?php echo $s_edit_link;?>">
            <?php
          	// Show default columns
            $summaryAmount+=$v_row['amount'];
          	 ?>
                <div class="gtable_cell">
                    <input type="checkbox" class="paymentsToSettle" value="<?php echo $v_row['id'];?>" data-amount="<?php echo $v_row['amount'];?>"/>
                </div>
    	        <div class="gtable_cell c1"><?php echo date("d.m.Y", strtotime($v_row['date']));?></div>
                <div class="gtable_cell c1"><?php echo $collectingCase['id']. " ".$collectingCase['debitorName'];?></div>
    	        <div class="gtable_cell rightAlign nobreak"><?php echo number_format($v_row['amount'], 2, ",", " ");?></div>

            </div>
        <?php } else if($list_filter == "settlements") {
            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];

            ?>
            <div class="gtable_row output-click-helper"  data-href="<?php echo $s_edit_link;?>">
    	        <div class="gtable_cell"><?php echo date("d.m.Y", strtotime($v_row['date']));?></div>
    	        <div class="gtable_cell"><?php echo number_format($v_row['collectingcompany_total_amount'], 2, ",", " ");?></div>
    	        <div class="gtable_cell"><?php echo number_format($v_row['creditor_total_amount'], 2, ",", " ");?></div>
    	        <div class="gtable_cell"><?php echo number_format($v_row['debitor_total_amount'], 2, ",", " ");?></div>
    	        <div class="gtable_cell"><a href="../modules/CollectingCaseSettlement/output/includes/download_payout_csv.php?settlementId=<?php echo $v_row['id'];?>" download><?php echo $formText_DownloadCsv_output;?></a></div>
    	        <div class="gtable_cell">
					<?php if($v_row['id'] == $last_settlement['id']) { ?>
						<span class="glyphicon glyphicon-trash delete_settlement" data-settlement-id="<?php echo $v_row['id'];?>"></span>
					<?php } ?>
				</div>
    	    </div>
        <?php } ?>
    <?php } ?>
    <?php
    if($list_filter == "payments") {
        ?>
        <div class="gtable_row">
        <?php
        // Show default columns
         ?>
            <div class="gtable_cell">
                <?php echo $formText_Summary_output;?>
            </div>
            <div class="gtable_cell c1"></div>
            <div class="gtable_cell c1"></div>
            <div class="gtable_cell rightAlign nobreak">
                <div class="payment_summary"><?php echo number_format($summaryAmount, 2, ",", " ");?></div>
            </div>

        </div>
        <?php
    }
    ?>
	<?php if (!$rowOnly) { ?>
		</div>
        <?php if($list_filter == "payments") { ?>
            <div class="createSettlement fw_button_color"><?php echo $formText_CreateSettlement_output;?></div>
        <?php } ?>
		<?php if($totalPages > 1) {
			$currentPage = $page;
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
			for($x = 1; $x <= 3;$x++){
				$prevPage = $page - $x;
				$nextPage = $page + $x;
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
			}echo '<!--section-->';
			asort($pages);
			?>
			<?php foreach($pages as $page) {?>
				<a href="#" data-page="<?php echo $page?>" class="page-link"><?php echo $page;?></a>
			<?php } ?>
			<?php /*
		    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
	<?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>
	</div>
	<?php } ?>
    <style>
    .type_wrapper {
        margin-bottom: 10px;
    }
    .createSettlement {
        float: right;
        margin-top: 15px;
        padding: 10px 15px;
        cursor: pointer;
        border-radius: 5px;
        color: #fff;

    }
    </style>
<script type="text/javascript">
	var out_popup;
	var out_popup_options={
    	follow: [true, true],
    	followSpeed: 0,
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
		}
	};
	$(function() {
        $(".delete_settlement").off("click").on("click", function(){
            var self = $(this);
    		var data = {
    			settlement_id: self.data('settlement-id'),
    			output_delete: 1
    		};
    		bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function(result) {
    			if (result) {
    				ajaxCall('delete_settlement', data, function(json) {
    					loadView("list", {list_filter:"<?php echo $list_filter;?>"});
    				});
    			}
    		});
        })
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
        $(".selectAll").click(function(){
            var checked = $(this).is(":checked");
            if(checked){
                $(".paymentsToSettle").prop("checked", true).change();
            } else {
                $(".paymentsToSettle").prop("checked", false).change();
            }
        })
    	// $(".paymentsToSettle").change(function(){
        //     var summary = 0;
        //     $(".paymentsToSettle").each(function(){
        //         if($(this).is(":checked")){
        //             summary += parseInt($(this).data("amount").toString().replace(",","."));
        //         }
        //     })
        //     $(".payment_summary").html(summary.toFixed(2).replace(".",","));
    	// })

        $(".createSettlement").off("click").on("click", function(){
            var selectedCount = $(".paymentsToSettle:checked").length;
            if(selectedCount > 0){
                var paymentsToSettle = new Array();
                $(".paymentsToSettle:checked").each(function(){
                    paymentsToSettle.push($(this).val());
                })
                var data = {paymentsToSettle: paymentsToSettle};
                ajaxCall('create_settlement', data, function(obj) {
					loadView("list", {list_filter:"<?php echo $list_filter;?>"});
                });
            } else {
                alert("<?php echo $formText_SelectPaymentsToSettle_output;?>");
            }
        })
	});
    $(".page-link").on('click', function(e) {
	    page = $(this).data("page");
	    e.preventDefault();
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
    $('.showMoreCustomersBtn').on('click', function(e) {
        page = parseInt(page)+1;
        e.preventDefault();
        var data = {
            building_filter: $(".buildingFilter").val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            search_by: $(".searchBy").val(),
            page: page,
            rowOnly: 1
        };
        ajaxCall('list', data, function(json) {
            $('.p_pageContent .gtable').append(json.html);
            $(".showMoreCustomers .showing").html($(".gtable .gtable_row.output-click-helper").length);
            if(json.html.replace(" ", "") == ""){
                $(".showMoreCustomersBtn").hide();
            }
        });
    });
</script>
<?php } ?>
