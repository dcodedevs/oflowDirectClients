<?php
$default_list = "not_printed";
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

$not_printed_count = get_customer_list_count($o_main, "not_printed", $filters);
$printed_today_count = get_customer_list_count($o_main, "printed_today", $filters);
$printed_earlier_count = get_customer_list_count($o_main, "printed_earlier", $filters);

if($list_filter == "not_printed") {
    $itemCount = $not_printed_count;
} else if($list_filter == "printed_today") {
    $itemCount = $printed_today_count;
} else if($list_filter == "printed_earlier") {
    $itemCount = $printed_earlier_count;
}
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
	    <div class="gtable_row">
            <?php
            if($list_filter == "not_printed") {
                ?>
    	        <div class="gtable_cell gtable_cell_head"><input type="checkbox" class="selectAll" autocomplete="off"/></div>
    	        <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?></div>
    	        <div class="gtable_cell gtable_cell_head"><?php echo $formText_Pdf_output;?></div>
            <?php } else if($list_filter == "printed_today") { ?>
                <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
                <div class="gtable_cell gtable_cell_head"><?php echo $formText_PdfCounts_output;?></div>
                <div class="gtable_cell gtable_cell_head"></div>
            <?php } else if($list_filter == "printed_earlier") { ?>
                <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
                <div class="gtable_cell gtable_cell_head"><?php echo $formText_PdfCounts_output;?></div>
                <div class="gtable_cell gtable_cell_head"></div>
            <?php } ?>
	    </div>
<?php } ?>
    <?php
    if($list_filter == "not_printed") {
        foreach($customerList as $v_row){
            $sql = "SELECT p.*, c2.name as debitorName, c.name as creditorName FROM collecting_cases p
            LEFT JOIN customer c2 ON c2.id = p.debitor_id
            LEFT JOIN creditor cred ON cred.id = p.creditor_id
            LEFT JOIN customer c ON c.id = cred.customer_id
            WHERE p.id = ? ORDER BY p.sortnr ASC";
            $o_query = $o_main->db->query($sql, array($v_row['id']));
            $case = $o_query ? $o_query->row_array() : array();
            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
            ?>
            <div class="gtable_row " data-href="<?php echo $s_edit_link;?>">
            <?php
          	// Show default columns
          	 ?>
                <div class="gtable_cell"><input type="checkbox" class="checkboxesGenerate" name="casesToGenerate" autocomplete="off" value="<?php echo $case['id'];?>" /></div>
    	        <div class="gtable_cell c1"><?php echo date("d.m.Y", strtotime($v_row['created']));?></div>
    	        <div class="gtable_cell"><?php echo $case['creditorName'];?></div>
    	        <div class="gtable_cell"><?php echo $case['debitorName'];?></div>
    	        <div class="gtable_cell">
                    <a href="<?php echo $extradomaindirroot.'/modules/CollectingCases/output/includes/generatePdf.php?caseId='.$case['id'].'&action_id='.$v_row['action_id'];?>" target="_blank"><?php echo $formText_PreviewPdf_output;?></a>
                </div>
            </div>
        <?php } ?>
        <?php
    } else if($list_filter == "printed_today") {
        foreach($customerList as $v_row){
            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
            ?>
            <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
            <?php
          	// Show default columns
          	 ?>
    	        <div class="gtable_cell c1"><?php echo date("d.m.Y", strtotime($v_row['created']));?></div>
    	        <div class="gtable_cell"><?php echo $v_row['pdf_count'];?></div>
    	        <div class="gtable_cell"><a target="_blank" href="<?php echo $extradir."/output/includes/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_row['id'] ;?>"><?php echo $formText_DownloadAllPdf_output;?></a></div>
            </div>
        <?php }
    } else if($list_filter == "printed_earlier") {
        foreach($customerList as $v_row){
            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row['id'];
            ?>
            <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
            <?php
            // Show default columns
             ?>
                <div class="gtable_cell c1"><?php echo date("d.m.Y", strtotime($v_row['created']));?></div>
                <div class="gtable_cell"><?php echo $v_row['pdf_count'];?></div>
                <div class="gtable_cell"><a target="_blank" href="<?php echo $extradir."/output/includes/ajax.download.php?accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&ID=".$v_row['id'] ;?>"><?php echo $formText_DownloadAllPdf_output;?></a></div>
            </div>
        <?php }
    }
    ?>
	<?php if (!$rowOnly) { ?>
		</div>
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

    <?php if($list_filter == "not_printed") { ?>
        <div class="launchPdfGenerate"><?php echo $formText_LaunchPdfGenerateScript_output; ?></div>
        <script type="text/javascript">
            $(".selectAll").on("click", function(){
                if($(this).is(":checked")){
                    $(".checkboxesGenerate").prop("checked", true);
                } else {
                    $(".checkboxesGenerate").prop("checked", false);
                }
            });
            $(".launchPdfGenerate").on("click", function(e){
                e.preventDefault();
                bootbox.confirm('<?php echo $formText_ProcessActions_output; ?>', function(result) {
                    if (result) {
                        var casesToGenerate = [];
                        var data = {
                            casesToGenerate: casesToGenerate
                        }
                        $(".checkboxesGenerate").each(function(index, el){
                            if($(el).is(":checked")){
                                casesToGenerate.push($(el).val());
                            }
                        })
                        ajaxCall('generate_pdf', data, function(json) {
                            // var win = window.open('<?php echo $extradomaindirroot.'/modules/CollectingCases/output/ajax.download.php?ID=';?>' + json.data.batch_id, '_blank');
                            // win.focus();
                            var data = {
                                list_filter: '<?php echo $list_filter;?>',
                                customer_filter:$(".customerId").val(),
                                search_filter: $('.searchFilter').val()
                            };
                            loadView('list', data);
                        });
                    }
                });
            })
        </script>
    <?php } ?>
    <?php if($list_filter == "printed_today") { ?>
        <div class="gtable" id="gtable_search2">
            <div class="gtable_row">
                <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Date_output;?></div>
                <div class="gtable_cell gtable_cell_head"><?php echo $formText_Creditor_output;?></div>
                <div class="gtable_cell gtable_cell_head"><?php echo $formText_Debitor_output;?></div>
                <div class="gtable_cell gtable_cell_head"><?php echo $formText_Pdf_output;?></div>
            </div>

            <?php foreach($customerList as $v_row) {
                $sql = "SELECT * FROM collecting_cases_claim_letter WHERE batch_id = ? ORDER BY sortnr ASC";
                $o_query = $o_main->db->query($sql, array($v_row['id']));
                $letters = $o_query ? $o_query->result_array() : array();
                foreach($letters as $letter){
                    $sql = "SELECT * FROM collecting_cases_handling_action WHERE id = ? ORDER BY sortnr ASC";
                    $o_query = $o_main->db->query($sql, array($letter['action_id']));
                    $collecting_cases_handling_action = $o_query ? $o_query->row_array() : array();

                    $sql = "SELECT p.*, c2.name as debitorName, c.name as creditorName FROM collecting_cases p
                    LEFT JOIN customer c2 ON c2.id = p.debitor_id
                    LEFT JOIN creditor cred ON cred.id = p.creditor_id
                    LEFT JOIN customer c ON c.id = cred.customer_id
                    WHERE p.id = ? ORDER BY p.sortnr ASC";
                    $o_query = $o_main->db->query($sql, array($letter['case_id']));
                    $case = $o_query ? $o_query->row_array() : array();
                    ?>
                    <div class="gtable_row">
                        <div class="gtable_cell c1"><?php echo date("d.m.Y", strtotime($collecting_cases_handling_action['performed_date']));?></div>
                       <div class="gtable_cell"><?php echo $case['creditorName'];?></div>
                       <div class="gtable_cell"><?php echo $case['debitorName'];?></div>
                       <div class="gtable_cell">
                           <a href="<?php echo $extradomaindirroot.$letter['pdf'];?>" download><?php echo $letter['pdf'];?></a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    <?php } ?>
	<?php if(!$_POST['updateOnlyList']){ ?>
	</div>
	<?php } ?>
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
		$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
			if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
		});
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
