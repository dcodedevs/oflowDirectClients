<?php
$current_page = $_GET['current_page'] ?? 1;
require_once __DIR__ . '/list_btn.php';

$legal_handling_steps = array();
$sql = "SELECT * FROM collecting_cases_collecting_process_steps ORDER BY sortnr DESC";
$o_query = $o_main->db->query($sql);
$all_steps = $o_query ? $o_query->result_array() : array();
$continuing_steps = array();
foreach($all_steps as $all_step){
	if(!$continuing_steps[$all_step['collecting_cases_collecting_process_id']]){
		$continuing_steps[$all_step['collecting_cases_collecting_process_id']] = $all_step;
	}
}
$collecting_cases_on_legal_handling_step = array();
foreach($continuing_steps as $continuing_step) {	
	$legal_handling_steps[] = $continuing_step['id'];
}

$per_page = 200;
$offset = ($current_page-1)*$per_page;
$sql_limit = " LIMIT ".$per_page." OFFSET ".$offset;
if(count($legal_handling_steps) > 0) {
	$sql = "SELECT collecting_company_cases.*, creditor.companyname as creditorName FROM collecting_company_cases 
	JOIN creditor ON creditor.id = collecting_company_cases.creditor_id
	WHERE IFNULL(collecting_company_cases.case_closed_date, '0000-00-00') = '0000-00-00' 
	AND collecting_company_cases.due_date < NOW()
	AND collecting_company_cases.collecting_cases_process_step_id IN ('".implode(",", $legal_handling_steps)."')";
	$o_query = $o_main->db->query($sql);
	$legal_count = $o_query ? $o_query->num_rows() : 0;
	
	$totalPages = ceil($legal_count/$per_page);

	$o_query = $o_main->db->query($sql.$sql_limit);	
	$collecting_cases_on_legal_handling_step = $o_query ? $o_query->result_array() : array();
}

?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<div class="p_pageContent_title">
					<?php echo $formText_FinishedLastStepCollectingCompanyCases_output." (".$legal_count.")";?>
				</div>
				<div class="gtable">
					<div class="gtable_row">
						<div class="gtable_cell">	
							<?php echo $formText_CaseId_output;?>
						</div>
						<div class="gtable_cell">	
							<?php echo $formText_CreditorName_output;?>
						</div>
						<div class="gtable_cell">	
							<?php echo $formText_DueDate_output; ?>
						</div>						
					</div>	
					<?php 
					foreach($collecting_cases_on_legal_handling_step as $collecting_cases_on_legal_handling_step) {
						$s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collecting_cases_on_legal_handling_step['id'];

						?>
						<div class="gtable_row">
							<a class="gtable_cell" target="_blank" href="<?php echo $s_edit_link;?>">	
								<?php echo $collecting_cases_on_legal_handling_step['id'];?>
							</a>
							<a class="gtable_cell" target="_blank" href="<?php echo $s_edit_link;?>">	
								<?php echo $collecting_cases_on_legal_handling_step['creditorName'];?>
							</a>
							<a class="gtable_cell" target="_blank" href="<?php echo $s_edit_link;?>">	
								<?php echo date("d.m.Y", strtotime($collecting_cases_on_legal_handling_step['due_date'])); ?>
							</a>							
						</div>	
										
						<?php 
					}				
					?>
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
					}
					asort($pages);
					?>
					<?php foreach($pages as $page) {?>
						<a href="#" data-page="<?php echo $page?>" class="page-link<?php if($current_page == $page) echo ' active';?>"><?php echo $page;?></a>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<style>
	.p_pageContent_title {
		font-size: 16px;
		font-weight: bold;
		margin-top: 20px;
	}
	.page-link.active {
		text-decoration: underline;
	}
</style>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
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
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("list");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});

    // Add new (old not fixed)
	$(".addNewButton").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_home";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: 0 },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
	
	$(".page-link").on('click', function(e) {
		page = $(this).data("page");
		e.preventDefault();
		var data = {
			current_page: page
		}
		loadView("list", data);
	});
});
</script>
<style>

</style>
