<?php 

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter."&mainlist_filter=".$mainlist_filter."&sublist_filter=".$sublist_filter."&show_not_zero_filter=".$show_not_zero_filter."&page=".$page;

$report_year = $_GET['report_year'] ? $_GET['report_year'] : date("Y");
$report_period = $_GET['report_period'] ? $_GET['report_period'] : '';

$v_period = array(
	$formText_FirstHalfYear_Output,
	$formText_AllYear_Output,
);

$s_sql = "SELECT MIN(t_main.first_date) AS start_date FROM
(
SELECT MIN(c1.warning_case_created_date) AS first_date FROM collecting_company_cases AS c1 WHERE IFNULL(c1.warning_case_created_date, '0000-00-00') <> '0000-00-00' AND c1.warning_case_created_date <= CURDATE()
UNION
SELECT MIN(c2.collecting_case_created_date) AS first_date FROM collecting_company_cases AS c2 WHERE IFNULL(c2.collecting_case_created_date, '0000-00-00') <> '0000-00-00' AND c2.collecting_case_created_date <= CURDATE()
) AS t_main";
$o_query = $o_main->db->query($s_sql);
$v_start_date = $o_query ? $o_query->row_array() : array();
$l_start_year = $l_stop_year = date("Y");
if('' != $v_start_date['start_date'] && '0000-00-00' != $v_start_date['start_date'])
{
	$l_start_year = date("Y", strtotime($v_start_date['start_date']));
}

include(__DIR__."/fnc_get_collecting_case_report_data.php");
$v_count = get_collecting_case_report_data($o_main, $report_year, $report_period);

include("collecting_report_entries.php");

$s_sql = "SELECT * FROM collecting_cases_claim_line_type_basisconfig ORDER BY sortnr DESC";
$o_query = $o_main->db->query($s_sql);
$nonprocessed_types = $o_query ? $o_query->result_array() : array();
$types = array();
foreach($nonprocessed_types as $type){
    $types[$type['id']] = $type;
}
?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<?php
				if($differenceInAddress){
					?>
					<div class="customer_difference_info"><?php echo $formText_ThereIsDifferenceBetweenCrmAndCollectingAddress_output;?></div>
					<?php
				}
				?>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle"><?php echo $formText_CollectingStatisticReport_Output;?></div>
                    <div class="p_contentBlock">
                        <p><?php echo $formText_Year_Output;?>: <select name="report_year" class="report_year_changer">
					        <?php
                            for($l_stop_year; $l_stop_year >= $l_start_year; $l_stop_year--)
                            {
                                ?><option value="<?php echo $l_stop_year;?>" <?php if($l_stop_year == $report_year) echo 'selected';?>><?php echo $l_stop_year;?></option><?php
                            }
                            ?>
                            </select>
                        </p>
                        <p><?php echo $formText_Period_Output;?>: 
                            <select name="report_period" class="report_period_changer">
                            <?php
                            foreach($v_period as $l_key => $s_item)
                            {
                                ?><option value="<?php echo $l_key;?>" <?php if($l_key == $report_period) echo 'selected';?>><?php echo $s_item;?></option><?php
                            }
                            ?>
                            </select>
                        </p>
                    </div>

                    <div class="p_contentBlock">
                        <table class="table">
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <?php 
                                foreach($v_info_array as $l_info_index=> $v_info) {
                                    ?>
                                    <tr>
                                        <td><?php echo $v_info['name'];?></td>
                                        <td><?php echo number_format($v_count[$l_info_index], $v_info['format'], "," ," ");
                                        if($l_info_index == "value_31") {
                                            $total_type_amounts = $v_count['value_31_array'];
                                            foreach($total_type_amounts as $type_key=>$type_amount){
                                                ?>
                                                <div><?php echo $types[$type_key]['type_name'].": ".$type_amount;?></div>
                                                <?php                                            }
                                        }
                                        ?></td>
                                        <td><a href="#" class="view_more_info" data-info='<?php echo $l_info_index;?>'><?php echo $formText_ViewMoreInfo_output;?></a></td>
                                    </tr>
                                    <?php
                                }
                            ?>
                            
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(function(){
        $(".report_year_changer").off("change").on("change", function(){
            var data = {
                report_year: $(".report_year_changer").val(),
                report_period: $(".report_period_changer").val()
            }
            loadView("view_collecting_report", data);
        })
        $(".report_period_changer").off("change").on("change", function(){
            var data = {
                report_year: $(".report_year_changer").val(),
                report_period: $(".report_period_changer").val()
            }
            loadView("view_collecting_report", data);
            
        })
        $(".view_more_info").off("click").on("click", function(e){
            e.preventDefault();
            var data = {
                report_year: $(".report_year_changer").val(),
                report_period: $(".report_period_changer").val(),
                info: $(this).data("info")
            }
            loadView("view_collecting_report_details", data);
            
        })
    })
</script>