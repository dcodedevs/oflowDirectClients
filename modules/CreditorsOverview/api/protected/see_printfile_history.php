<?php
$customer_id = $v_data['params']['customer_id'];
$creditor_filter = $v_data['params']['creditor_filter'];
$accountname = $v_data['params']['accountname'];;
$extradomaindirroot = $v_data['params']['extradomaindirroot'];
$languageID = $v_data['params']['languageID'];
$page = isset($v_data['params']['page']) ? $v_data['params']['page'] : 1;

$perPage = 100;
$showing = $page * $perPage;

$offset = ($page-1)*$perPage;
if($offset < 0){
    $offset = 0;
}
$pager = " LIMIT ".$perPage ." OFFSET ".$offset;
if($creditor_filter > 0){
	$s_sql = "SELECT cccl.*, ccha.created, ccha.action_type, ccha.performed_date, ccb.code, CONCAT_WS(' ', c2.name, c2.middlename, c2.lastname) as customerName, ccha.performed_action FROM collecting_cases_claim_letter cccl
	LEFT OUTER JOIN collecting_cases_batch ccb ON ccb.id = cccl.batch_id
	LEFT OUTER JOIN collecting_cases_handling_action ccha ON ccha.id = cccl.action_id
	LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
	LEFT OUTER JOIN creditor cr ON cr.id = cc.creditor_id
	LEFT OUTER JOIN customer c2 ON c2.id = cc.debitor_id
	WHERE cccl.content_status < 2 AND cr.id = ?  ORDER BY cccl.created DESC";

	$o_query = $o_main->db->query($s_sql, array($creditor_filter));
	$currentCount = ($o_query ? $o_query->num_rows() : 0);

	$o_query = $o_main->db->query($s_sql.$pager, array($creditor_filter));
	$v_claim_letters = ($o_query ? $o_query->result_array() : array());
} else {
	$s_sql = "SELECT cccl.*, ccha.created, ccha.action_type, ccha.performed_date, ccb.code, CONCAT_WS(' ', c2.name, c2.middlename, c2.lastname) as customerName, ccha.performed_action FROM collecting_cases_claim_letter cccl
	LEFT OUTER JOIN collecting_cases_batch ccb ON ccb.id = cccl.batch_id
	LEFT OUTER JOIN collecting_cases_handling_action ccha ON ccha.id = cccl.action_id
	LEFT OUTER JOIN collecting_cases cc ON cc.id = cccl.case_id
	LEFT OUTER JOIN creditor cr ON cr.id = cc.creditor_id
	LEFT OUTER JOIN customer c ON c.id = cr.customer_id
	LEFT OUTER JOIN customer c2 ON c2.id = cc.debitor_id
	WHERE cccl.content_status < 2 AND c.id = ?  ORDER BY cccl.created DESC";

	$o_query = $o_main->db->query($s_sql, array($customer_id));
	$currentCount = ($o_query ? $o_query->num_rows() : 0);

	$o_query = $o_main->db->query($s_sql.$pager, array($customer_id));
	$v_claim_letters = ($o_query ? $o_query->result_array() : array());
}

$totalPages = ceil($currentCount/$perPage);


ob_start();

include(__DIR__."/../languagesOutput/default.php");
if(is_file(__DIR__."/../languagesOutput/".$languageID.".php")){
    include(__DIR__."/../languagesOutput/".$languageID.".php");
}
include(__DIR__."/../../output/languagesOutput/default.php");
if(is_file(__DIR__."/../../output/languagesOutput/".$languageID.".php")){
    include(__DIR__."/../../output/languagesOutput/".$languageID.".php");
}
?>
<table class="table">
    <tr>
        <th><?php echo $formText_Created_output;?></th>
        <th><?php echo $formText_CustomerName_output;?></th>
        <th><?php echo $formText_Action_output;?></th>
        <th><?php echo $formText_Link_output;?></th>
    </tr>
<?php
foreach($v_claim_letters as $v_claim_letter) {
    ?>
    <tr>
        <td><?php echo date("d.m.Y", strtotime($v_claim_letter['created']));?></td>
        <td><?php echo $v_claim_letter['customerName'];?></td>
        <td><?php if(intval($v_claim_letter['performed_action']) == 0) {
            echo $formText_SentLetter_output;
        } else if($v_claim_letter['performed_action'] == 1){
            echo $formText_SentEmail_output;
        }?></td>
        <td><a target="_blank" href="<?php echo $extradomaindirroot."/".$v_claim_letter['pdf']?>"><?php echo $formText_DownloadPdf_output;?></a></td>
    </tr>
    <?php
}
?>
</table>
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
    <?php foreach($pages as $singlePage) {?>
        <a href="#" data-page="<?php echo $singlePage?>" class="page-link<?php if($singlePage == $page) echo ' active';?>"><?php echo $singlePage;?></a>
    <?php } ?>
    <?php /*
    <div class="showMoreCustomers"><?php echo $formText_Showing_Output;?> <span class="showing"><?php echo $showing;?></span> <?php echo $formText_Of_output." ".$currentCount;?> <a href="#" class="showMoreCustomersBtn"><?php echo $formText_ShowMore_output;?></a> </div>*/?>
<?php } ?>
<style>
.page-link {
    padding: 0px 3px;
}
.page-link.active {
    text-decoration: underline;
}
</style>
<script type="text/javascript">
    $(".page-link").off("click").on("click", function(){
        var data = {
            customer_filter:'<?php echo $customer_id?>',
            page: $(this).data("page")
        };
        ajaxCall('seePrintfileHistory', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
            $(window).resize();
        });
    })
</script>
<?php
$result_output = ob_get_contents();
$result_output = trim(preg_replace('/\s\s+/', '', $result_output));
ob_end_clean();
$v_return['html'] = $result_output;
$v_return['status'] = 1;
?>
