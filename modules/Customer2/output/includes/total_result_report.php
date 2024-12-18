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

$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get('customer_accountconfig');
$v_customer_accountconfig= $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->get('salaryreporting');
$salaryreporting = $o_query ? $o_query->row_array() : array();
$completedRepeatingOrderDate = date("t.m.Y", strtotime("-1 month", strtotime($salaryreporting['active_salary_period'])));

if(isset($_GET['filter_date_from'])){ $filter_date_from = $_GET['filter_date_from']; } else { $filter_date_from = date("01.m.Y", strtotime("-1 month", time())); }
if(isset($_GET['filter_date_to'])){ $filter_date_to = $_GET['filter_date_to']; } else { $filter_date_to = date("t.m.Y", strtotime("-1 month", time())); }
$viewType = 0;
if(isset($_GET['viewType'])) { $viewType = $_GET['viewType']; }
if(isset($_GET['order_direction'])){ $order_direction = $_GET['order_direction']; } else { $order_direction = 1;}
if(isset($_GET['order_field'])){ $order_field = $_GET['order_field']; } else { $order_field = 'customername';}

if($_GET['action'] == "fixCompletedDates"){

	$s_sql = "SELECT project2.*, project2_periods.id as projectPeriodId FROM project2 JOIN project2_periods ON project2_periods.projectId = project2.id WHERE project2.projectLeaderStatus = 1 AND (project2.type = 0 OR project2.type is null) AND (project2_periods.completed_date is null OR project2_periods.completed_date = '0000-00-00')";
	$o_query = $o_main->db->query($s_sql);
	$onetimeCompleted =  $o_query ? $o_query->result_array() : array();
	$projectUpdated = 0;
	foreach($onetimeCompleted as $result){
		$s_sql = "SELECT customer_collectingorder.date FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
		LEFT OUTER JOIN article ON article.id = orders.articleNumber
		WHERE customer_collectingorder.project2PeriodId = ? AND customer_collectingorder.invoiceNumber > 0 ORDER BY customer_collectingorder.date DESC";
		$o_query = $o_main->db->query($s_sql, array($result['projectPeriodId']));
		$lastApprovedMonthOrder = ($o_query ? $o_query->row_array() : array());
		if($lastApprovedMonthOrder){
			$s_sql = "UPDATE project2_periods SET completed_date = '".date("Y-m-d", strtotime($lastApprovedMonthOrder['date']))."' WHERE id = '".$o_main->db->escape_str($result['projectPeriodId'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				$projectUpdated++;
			}
		}
	}
	echo $projectUpdated . " ".$formText_OneTimeProjectsUpdated_output."<br/>";

	$s_sql = "SELECT project2_periods.id as projectPeriodId FROM project2_periods JOIN project2 ON project2_periods.projectId = project2.id
	WHERE project2.type = 1 AND (project2_periods.completed_date is null OR project2_periods.completed_date = '0000-00-00') AND project2_periods.status = 1";
	$o_query = $o_main->db->query($s_sql);
	$continuingCompleted =  $o_query ? $o_query->result_array() : array();
	$projectUpdated = 0;
	foreach($continuingCompleted as $result){
		$s_sql = "SELECT customer_collectingorder.date FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
		LEFT OUTER JOIN article ON article.id = orders.articleNumber
		WHERE customer_collectingorder.project2PeriodId = ? AND customer_collectingorder.invoiceNumber > 0 ORDER BY customer_collectingorder.date DESC";
		$o_query = $o_main->db->query($s_sql, array($result['projectPeriodId']));
		$lastApprovedMonthOrder = ($o_query ? $o_query->row_array() : array());
		if($lastApprovedMonthOrder){
			$s_sql = "UPDATE project2_periods SET completed_date = '".date("Y-m-d", strtotime($lastApprovedMonthOrder['date']))."' WHERE id = '".$o_main->db->escape_str($result['projectPeriodId'])."'";
			$o_query = $o_main->db->query($s_sql);
			if($o_query){
				$projectUpdated++;
			}
		}
	}
	echo $projectUpdated . " ".$formText_ContinuingProjectPeriodsUpdated_output."<br/>";
	return;
}

$s_sql = "SELECT project2.*, project2_periods.id as projectPeriodId FROM project2 JOIN project2_periods ON project2_periods.projectId = project2.id WHERE project2.projectLeaderStatus = 1 AND (project2.type = 0 OR project2.type is null) AND (project2_periods.completed_date is null OR project2_periods.completed_date = '0000-00-00')";
$o_query = $o_main->db->query($s_sql);
$onetimeCompleted =  $o_query ? $o_query->result_array() : array();
$onetimeCompletedCount = 0;
foreach($onetimeCompleted as $result){
	$s_sql = "SELECT customer_collectingorder.date FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
	LEFT OUTER JOIN article ON article.id = orders.articleNumber
	WHERE customer_collectingorder.project2PeriodId = ? AND customer_collectingorder.invoiceNumber > 0 ORDER BY customer_collectingorder.date DESC";
	$o_query = $o_main->db->query($s_sql, array($result['projectPeriodId']));
	$lastApprovedMonthOrder = ($o_query ? $o_query->row_array() : array());
	if($lastApprovedMonthOrder){
		$onetimeCompletedCount++;
	}
}

$s_sql = "SELECT project2_periods.id as projectPeriodId FROM project2_periods JOIN project2 ON project2_periods.projectId = project2.id
WHERE project2.type = 1 AND (project2_periods.completed_date is null OR project2_periods.completed_date = '0000-00-00') AND project2_periods.status = 1";
$o_query = $o_main->db->query($s_sql);
$continuingCompleted =  $o_query ? $o_query->result_array() : array();
$continuingCompletedCount = 0;
foreach($continuingCompleted as $result){
	$s_sql = "SELECT customer_collectingorder.date FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
	LEFT OUTER JOIN article ON article.id = orders.articleNumber
	WHERE customer_collectingorder.project2PeriodId = ? AND customer_collectingorder.invoiceNumber > 0 ORDER BY customer_collectingorder.date DESC";
	$o_query = $o_main->db->query($s_sql, array($result['projectPeriodId']));
	$lastApprovedMonthOrder = ($o_query ? $o_query->row_array() : array());
	if($lastApprovedMonthOrder){
		$continuingCompletedCount++;
	}
}
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{	?>
	<div class="backToCustomer btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_Back_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<?php if($v_customer_accountconfig['activate_cost_syncing_information']){ ?>
		<div class="cost_syncing_information">
			<?php

				$s_sql = "SELECT customer_collectingorder.date FROM customer_collectingorder LEFT OUTER JOIN orders ON orders.collectingorderId = customer_collectingorder.id
				LEFT OUTER JOIN article ON article.id = orders.articleNumber
				WHERE customer_collectingorder.project2PeriodId = 'modules/BatchInvoicing/autotask_process_invoices/run.php' AND customer_collectingorder.invoiceNumber > 0 ORDER BY customer_collectingorder.date DESC";
				$o_query = $o_main->db->query($s_sql);
				// $lastApprovedMonthOrder = ($o_query ? $o_query->row_array() : array());
			?>
			1

		</div>
	<?php }?>
	<div class="clear"></div>
	<?php
}
?>
</div>
<script type="text/javascript">
    $(".backToCustomer").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
    });
</script>
<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <span class="filter_low">
            <span class="filter_date_from"><?php if($filter_date_from != "") echo date("d.m.Y", strtotime($filter_date_from))?></span> -
            <span class="filter_date_to"><?php if($filter_date_to != "") echo date("d.m.Y", strtotime($filter_date_to))?></span>
			<div class="filter_span">
				<?php echo $formText_ProjectType_output;?>
				<select class="projectTypeSelector" autocomplete="off" disabled>
					<option value=""><?php echo $formText_All_output;?></option>
					<option value="1" <?php if($_GET['project_type'] == 1) echo 'selected';?>><?php echo $formText_RepeatingOrder_output;?></option>
					<option value="2" <?php if($_GET['project_type'] == 2) echo 'selected';?>><?php echo $formText_OneTimeProject_output;?></option>
					<option value="3" <?php if($_GET['project_type'] == 3) echo 'selected';?>><?php echo $formText_ContinuingProject_output;?></option>
				</select>
			</div>
			<div class="filter_span">
				<?php echo $formText_ProjectLeader_output;?>
				<?php
					$s_sql = "SELECT * FROM contactperson  WHERE content_status < 2 AND type = ? ORDER BY name ASC";
					$o_query = $o_main->db->query($s_sql, array($people_contactperson_type));
					$employees = ($o_query ? $o_query->result_array() : array());
				?>
				<select class="projectLeaderSelector" autocomplete="off" disabled>
					<option value=""><?php echo $formText_All_output;?></option>
					<?php foreach($employees as $employee) { ?>
						<option value="<?php echo $employee['id']?>" <?php if($employee['id'] == $_GET['project_leader']) echo 'selected';?>><?php echo $employee['name']." ".$employee['middlename']." ".$employee['lastname'];?></option>
					<?php } ?>
				</select>
			</div>
            <span class="edit_date"><?php echo $formText_EditFilters_output;?></span>

        </span>

		<?php
		if($onetimeCompletedCount > 0 || $continuingCompletedCount > 0) {
			echo $formText_CompletedDateIsMissingFromProjects_output." (".$onetimeCompletedCount." ".$formText_OnetimeProjects_output.", ".$continuingCompletedCount." ".$formText_ContinuingProjects_output.")";
			echo "<span class='fixCompletedDates'>".$formText_FixCompletedDates_output."</span>";
		}
		?>
	</div>
	<div class="p_tableFilter_right">
		<span class="exportReport"><?php echo $formText_ExportReport_output;?></span>
	<?php
	/*
	if($viewType == 1){ ?>
		<div class="groupByItem" data-view="0"><?php echo $formText_BackToGroupByCustomer_output;?></div>
	<?php } else { ?>
		<div class="groupByItem" data-view="1"><?php echo $formText_GroupByProjectCode_output;?></div>
	<?php }*/ ?>
	</div>
	<div class="clear"></div>
	<div class="filter_message">
	</div>
</div>
<table class="gtable" id="gtable_search" style="table-layout: fixed;">
    <tr class="gtable_row">
		<?php if($viewType == 1){ ?>
	        <th class="gtable_cell gtable_cell_head orderBy" data-orderfield="projectcode" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
	            <?php echo $formText_ProjectCode_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "projectcode" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "projectcode" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
	        </th>
			<?php
		} else { ?>
	        <th class="gtable_cell gtable_cell_head orderBy" data-orderfield="customername" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
	            <?php echo $formText_CustomerName_output;?>
				<div class="ordering">
					<div class="fas fa-caret-up" <?php if($order_field == "customername" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
					<div class="fas fa-caret-down" <?php if($order_field == "customername" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
				</div>
	        </th>
		<?php } ?>
		<th class="gtable_cell gtable_cell_head" >
			<?php echo $formText_RepP_output;?>
		</th>
		<th class="gtable_cell gtable_cell_head" >
			<?php echo $formText_OneTP_output;?>
		</th>
		<th class="gtable_cell gtable_cell_head" >
			<?php echo $formText_ContP_output;?>
		</th>
		<th class="gtable_cell gtable_cell_head orderBy" data-orderfield="revenue" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
			<?php echo $formText_Revenue_output;?>
			<div class="ordering">
				<div class="fas fa-caret-up" <?php if($order_field == "revenue" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
				<div class="fas fa-caret-down" <?php if($order_field == "revenue" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
			</div>
		</th>
		<th class="gtable_cell gtable_cell_head orderBy" data-orderfield="result" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>">
			<?php echo $formText_Result_output;?>
			<div class="ordering">
				<div class="fas fa-caret-up" <?php if($order_field == "result" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
				<div class="fas fa-caret-down" <?php if($order_field == "result" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
			</div>
		</th>
		<th class="gtable_cell gtable_cell_head orderBy" data-orderfield="margin" data-orderdirection="<?php if($order_direction == 0) { echo 1; } else { echo 0; } ?>" >
			<?php echo $formText_Margin_output;?>
			<div class="ordering">
				<div class="fas fa-caret-up" <?php if($order_field == "margin" && $order_direction == 0) { echo 'style="display: none;"';}?>></div>
				<div class="fas fa-caret-down" <?php if($order_field == "margin" && $order_direction == 1) { echo 'style="display: none;"';}?>></div>
			</div>
		</th>
	</tr>
	<?php
	include_once("total_result_report_functions.php");
	$customers_ordered = get_processed_customers($variables);
	$total_revenue_sum = 0;
	$total_result_summary = 0;
	foreach($customers_ordered as $customer){
		$projects = $customer['projects'];
		$oneTimeProjects = $customer['oneTimeProjects'];
		$continuingProjects = $customer['continuingProjects'];
		$continuingPeriods = $customer['continuingPeriods'];
		$repeatingOrdersGlobal = $customer['repeatingOrdersGlobal'];
		$repeatingOrdersUnique = $customer['repeatingOrdersUnique'];
		$repeatingOrdersUnified = $customer['repeatingOrdersUnified'];
		$total_resultAmount = $customer['total_resultAmount'];
		$total_resultPercent = $customer['total_resultPercent'];
		$total_itemCost = $customer['total_itemCost'];
		$total_salaryCost = $customer['total_salaryCost'];
		$total_invoicedItemSales = $customer['total_invoicedItemSales'];
		$total_invoicedServices = $customer['total_invoicedServices'];
		$total_revenue_sum+=$total_invoicedItemSales;
		$total_revenue_sum+=$total_invoicedServices;
		$total_result_summary+=$total_resultAmount;

		$suborder_comments = $customer['suborder_comments'];

		?>
		<tr class="gtable_row showDetailedInfo">
			<?php if($viewType == 1) { ?>
				<td class="gtable_cell">
				   <?php echo $customer['projectCode'];?>
			   </td>
		   <?php } else { ?>
		        <td class="gtable_cell">
		            <?php echo $customer['customerName']." ".$customer['subunitName'];?>
		        </td>
			<?php } ?>
			<td class="gtable_cell " >
				<?php echo count($repeatingOrdersUnique)." (".count($repeatingOrdersGlobal).")";?>
			</td>
			<td class="gtable_cell " >
				<?php echo count($oneTimeProjects);?>
			</td>
			<td class="gtable_cell " >
				<?php echo count($continuingProjects)." (".count($continuingPeriods).")";?>
			</td>
			<td class="gtable_cell " >
				<?php echo number_format($total_invoicedItemSales+$total_invoicedServices, 2, ",", "")?>
			</td>
			<td class="gtable_cell " >
				<?php echo number_format($total_resultAmount, 2, ",", "")?>
			</td>
			<td class="gtable_cell " >
				<?php echo number_format($total_resultPercent, 0, ",", "");?>%

				<?php
				if(count($suborder_comments) > 0) {
					?>
					<div class="commentsWrapper">
						<span class="fas fa-comment-alt"></span> (<?php echo count($suborder_comments);?>)
					</div>
					<?php
				}
				?>
			</td>
		</tr>
		<tr class="customerInfo">
			<td class="gtable_cell" colspan="7">
				<table class="table">
					<tr class="gtable_row">
				        <th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Date_output;?>
				        </th>
				        <th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Name_output;?>
				        </th>
				        <th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Type_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Invoiced_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_InvoicedItemSales_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_SalaryCost_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_ItemCost_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Result_output;?>
				        </th>
						<th class="gtable_cell gtable_cell_head">
				            <?php echo $formText_Margin_output;?>
				        </th>
					</tr>
					<?php
					foreach($repeatingOrdersUnified as $repeatingOrder) {
						$invoicedServices = $repeatingOrder['invoicedServices'];
						$invoicedItemSales = $repeatingOrder['invoicedItemSales'];
						$salaryCost = $repeatingOrder['salaryCost'];
						$itemCost = $repeatingOrder['itemCost'];
						$resultPercent = $repeatingOrder['resultPercent'];
						$resultAmount = $repeatingOrder['resultAmount'];

						$commentsOnOrders = $repeatingOrder['comments'];
						?>
						<tr class="gtable_row">
					        <td class="gtable_cell">
					            <?php echo date("d.m.Y", strtotime($repeatingOrder['completed_date']));?>
					        </td>
					        <td class="gtable_cell">
					            <?php echo $repeatingOrder['subscriptionName'];?>
					        </td>
					        <td class="gtable_cell">
					            <?php if($repeatingOrder['repeatingOrderId'] > 0) {
									echo $formText_Repeatingorder_output;
								} else if($repeatingOrder['projectPeriodId'] > 0) {
									echo $formText_Project_output;
								}?>
					        </td>
							<td class="gtable_cell ">
								<?php
								echo number_format($invoicedServices, 2, ",", "");
								?>
					        </td>
							<td class="gtable_cell ">
								<?php
								echo number_format($invoicedItemSales, 2, ",", "");
								?>
					        </td>
							<td class="gtable_cell ">
								<?php
								 echo number_format($salaryCost, 2, ",", "");

								?>
					        </td>
							<td class="gtable_cell ">
								<?php
								echo number_format($itemCost, 2, ",", "");
								?>
					        </td>
							<td class="gtable_cell ">
					            <?php echo number_format($resultAmount, 2, ",", "");?>
					        </td>
							<td class="gtable_cell ">
				                <?php echo number_format($resultPercent, 0, ",", "");?>%
								<div class="commentsWrapper <?php if(count($commentsOnOrders) == 0) echo ' commentsNotActive';?> view_comments" data-subscriptionmulti_id="<?php echo $repeatingOrder['repeatingOrderId'];?>" data-project2_period_id="<?php echo $repeatingOrder['projectPeriodId'];?>">
									<span class="fas fa-comment-alt"></span> (<?php echo count($commentsOnOrders);?>)
									<span class="hoverOver">
										<?php foreach($commentsOnOrders as $commentsOnOrder) {
											echo $commentsOnOrder['comment']."<br/>";
										} ?>
									</span>
								</div>
					        </td>
						</tr>
						<?php
					}
					?>
				</table>
			</td>
		</tr>
	<?php } ?>

	<tr>
		<td class="gtable_cell">
			<b><?php echo $formText_Total_output;?></b>
		</td>
		<td class="gtable_cell " >
		</td>
		<td class="gtable_cell " >
		</td>
		<td class="gtable_cell " >
		</td>
		<td class="gtable_cell " >
			<?php echo number_format($total_revenue_sum, 2, ",", "")?>
		</td>
		<td class="gtable_cell " >
			<?php echo number_format($total_result_summary, 2, ",", "")?>
		</td>
		<td class="gtable_cell " >
		</td>
	</tr>
</table>
<style>
select:disabled {
  background: #ffffff;
}
.fixCompletedDates {
	cursor: pointer;
	color: #46b2e2;
	margin-left: 20px;
}
.customerInfo {
	display: none;
}
.filter_date_from  {
	border: 1px solid #e5e5e5;
	padding: 6px 5px;
	border-radius: 3px;
	background: #fff;
}
.filter_date_to  {
	border: 1px solid #e5e5e5;
	padding: 6px 5px;
	border-radius: 3px;
	background: #fff;
}
.edit_date {
	cursor: pointer;
	color: #46b2e2;
	margin-left: 10px;
}
.showDetailedInfo {
	cursor: pointer;
}
.groupByItem {
	cursor: pointer;
	color: #46b2e2;
	margin-left: 20px;
}

.orderBy {
	cursor: pointer;
}
.ordering {
	display: inline-block;
	vertical-align: middle;
}
.ordering div {
	display: block;
	line-height: 8px;
	color: #46b2e2;
}
.filter_span {
	margin-left: 15px;
	display: inline-block;
	vertical-align: middle;
}
.filter_span select {
	padding: 5px 5px;
	font-size: 13px;
}
.commentsWrapper {
	float: right;
    color: #46b2e2;
	position: relative;
	padding-left: 10px;
}
.commentsWrapper.commentsNotActive {
	color: gray;
}
.commentsWrapper .hoverOver {
	color: #3b3c40;
	display: none;
	padding: 5px 10px;
	position: absolute;
	right: 100%;
	top: -5px;
	background-color: #fff;
	border: 1px solid #ddd;
}
.commentsWrapper:hover .hoverOver {
	display: block;
}
.view_comments {
	cursor: pointer;
}
.exportReport {
    color: #46b2e2;
	cursor: pointer;
	line-height: 27px;
}
.cost_syncing_information {
	float: right;
}
</style>
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
		},
		onClose: function(){
			$(this).removeClass('opened');
			if($(this).is('.close-reload')) {
				var redirectUrl = $(this).data("redirect");
				if(redirectUrl !== undefined && redirectUrl != ""){
					// document.location.href = redirectUrl;
					 reloadPage();
				} else {
					 reloadPage();
				}
			}

		}
	};
	function reloadPage(){
		var data = {
			filter_date_from: $(".filter_date_from").html(),
			filter_date_to: $(".filter_date_to").html(),
			viewType: '<?php echo $viewType;?>',
			project_type: $(".projectTypeSelector").val(),
			project_leader: $(".projectLeaderSelector").val()
		};
		loadView("total_result_report", data);
	}
	$(function(){
		$(".fixCompletedDates").off("click").on("click", function(){
			var data = {
				filter_date_from: $(".filter_date_from").html(),
				filter_date_to: $(".filter_date_to").html(),
				viewType: '<?php echo $_GET['viewType']?>',
				action: 'fixCompletedDates',
				project_type: $(".projectTypeSelector").val(),
				project_leader: $(".projectLeaderSelector").val()
			};
			loadView("total_result_report", data);
		})
		$(".showDetailedInfo").off("click").on("click", function(){
			$(this).next(".customerInfo").slideToggle();
		})
		$(".edit_date").off("click").on("click", function(){
			var data = {
				filter_date_from: $(".filter_date_from").html(),
				filter_date_to: $(".filter_date_to").html(),
				project_type: $(".projectTypeSelector").val(),
				project_leader: $(".projectLeaderSelector").val()
			};
			ajaxCall('editDate', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})
		$(".groupByItem").off("click").on("click", function(){

			var data = {
				filter_date_from: $(".filter_date_from").html(),
				filter_date_to: $(".filter_date_to").html(),
				viewType: $(this).data("view"),
				project_type: $(".projectTypeSelector").val(),
				project_leader: $(".projectLeaderSelector").val()
			};
			loadView("total_result_report", data);
		})
		$(".orderBy").off("click").on("click", function(){
	        var order_field = $(this).data("orderfield");
	        var order_direction = $(this).data("orderdirection");

	        var data = {
				filter_date_from: $(".filter_date_from").html(),
 			   	filter_date_to: $(".filter_date_to").html(),
 			   	viewType: '<?php echo $viewType;?>',
	            order_field: order_field,
	            order_direction: order_direction,
				project_type: $(".projectTypeSelector").val(),
				project_leader: $(".projectLeaderSelector").val()
	        }
	        loadView("total_result_report", data);
	    })
		$(".view_comments").off("click").on("click", function(){
			var data = {
				subscriptionmulti_id: $(this).data("subscriptionmulti_id"),
				project2_period_id: $(this).data("project2_period_id")
			};
			ajaxCall('view_comments', data, function(json) {
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(json.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			});
		})

		$('.exportReport').on('click', function(e) {
	        e.preventDefault();
			var data = {
				fwajax: 1,
				fw_nocss: 1
			};
			submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=exportTotalResultReport&filter_date_from=".$filter_date_from."&filter_date_to=".$filter_date_to."&viewType=".$viewType."&project_type=".$_GET['project_type']."&project_leader=".$_GET['project_leader'].""; ?>', data);
	    });

	})
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
