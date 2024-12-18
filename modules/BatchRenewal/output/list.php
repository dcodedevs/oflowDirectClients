<?php
include("fnc_get_sl_rows.php");
include("fnc_create_adjustment_letter.php");
$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$ownercompanyAccountconfig = $o_query->row_array();
	$activateMultiOwnerCompanies = intval($ownercompanyAccountconfig['max_number_ownercompanies']);
}

$search = $_GET['search'] > 0 ? $_GET['search'] : "";

if($search != ""){
	$o_query = $o_main->db->query("SELECT *, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName FROM customer WHERE id = ?", array($search));
	$searchedCustomer = $o_query ? $o_query->row_array() : array();
}
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM batch_renewal_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $batch_renewal_basisconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM batch_renewal_accountconfig";
$o_query = $o_main->db->query($s_sql);
$batch_renewal_accountconfig = $o_query ? $o_query->row_array() : array();

$project_id = isset($_GET['project_filter']) ? $_GET['project_filter'] : 0;
$department_id = isset($_GET['department_filter']) ? $_GET['department_filter'] : '';
$subtypeFilter = isset($_GET['subtypeFilter']) ? $_GET['subtypeFilter'] : '';

$s_sql = "SELECT * FROM subscriptiontype_subtype
WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($subtypeFilter));
$filtered_subtype = ($o_query ? $o_query->row_array() : array());

$ownercompany_filter = $_GET['ownercompany'] ? explode(",", $_GET['ownercompany']) : array();
$customerselfdefinedlist_filter = $_GET['customerselfdefinedlist_filter'] ? $_GET['customerselfdefinedlist_filter'] : "";
$ownercompany_filter_sql = "";
$selfdefined_join = "";
$selfdefined_sql = "";
$real_ownercompany_filter = array();
if(count($ownercompany_filter) > 0){
	foreach($ownercompany_filter as $singleItem){
		if($singleItem > 0){
			array_push($real_ownercompany_filter, $singleItem);
		}
	}
	if(count($real_ownercompany_filter) > 0){
		$ownercompany_filter_sql = " AND subscriptionmulti.ownercompany_id IN (".implode(',', $real_ownercompany_filter).")";
	}
}
if($customerselfdefinedlist_filter != ""){
	$selfdefined_join = "
	LEFT OUTER JOIN customer_selfdefined_values ON  customer_selfdefined_values.customer_id = customer.id AND customer_selfdefined_values.selfdefined_fields_id = ".$o_main->db->escape($batch_renewal_accountconfig['customerSelfdefinedField'])."
	LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values_connection.selfdefined_value_id = customer_selfdefined_values.id
	";
	$selfdefined_sql = " AND (customer_selfdefined_values_connection.selfdefined_list_line_id = ".$o_main->db->escape($customerselfdefinedlist_filter)." OR customer_selfdefined_values.value= ".$o_main->db->escape($customerselfdefinedlist_filter).")";
}

if($project_id > 0){
	$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_id));
	$projectData = $o_query ? $o_query->row_array() : array();

	$ownercompany_filter_sql .= " AND (subscriptionmulti.projectId = ".$o_main->db->escape($projectData['projectnumber']).")";
}
if($department_id != ''){
	$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE departmentnumber = ?", array($department_id));
	$departmentData = $o_query ? $o_query->row_array() : array();
	$ownercompany_filter_sql .= " AND (subscriptionmulti.departmentCode = ".$o_main->db->escape($departmentData['departmentnumber']).")";
}

$totalOwnerCompanies = 0;
$ownercompanies = array();
$s_sql = "SELECT * FROM ownercompany WHERE content_status < 2";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$totalOwnerCompanies = $o_query->num_rows();
	$ownercompanies = $o_query->result_array();
}
if ($activateMultiOwnerCompanies == 1 || $activateMultiOwnerCompanies == 0) {
	$s_sql = "SELECT * FROM ownercompany";
	$o_query = $o_main->db->query($s_sql);
	if($o_query && $o_query->num_rows()>0){
		$defaultOwnerCompany = $o_query->row_array();
    	$defaultOwnerCompanyId = $defaultOwnerCompany['id'];
	}
}
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

if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 1){
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForProjectNr'] == 2) {
	$batch_renewal_basisconfig['activateCheckForProjectNr'] = 0;
}

if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 1){
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 1;
} else if($batch_renewal_accountconfig['activateCheckForDepartmentCode'] == 2) {
	$batch_renewal_basisconfig['activateCheckForDepartmentCode'] = 0;
}

$l_raise_percent = 5;
//include(__DIR__."/list_btn.php");
$v_address_format = array('paStreet', 'paCity', 'paCountry', 'paPostalNumber');
$s_sql = "SELECT * FROM settings";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
	$v_settings = $o_query->row_array();
}
$dateMarker = date("Y-m-d", time());
if(isset($_GET['date']) && $_GET['date'] != ""){
	$dateMarker = date("Y-m-d", strtotime($_GET['date']));
}
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}
$v_countries = array();
$v_response = json_decode(APIconnectorOpen("countrylistget"), TRUE);
if(isset($v_response['status']) && $v_response['status'] == 1)
{
	foreach($v_response['data'] as $v_item)
	{
		$v_countries[$v_item['countryID']] = $v_item['name'];
	}
}

if($_GET['otherCustomerFilter'] == 1){
	$selfdefined_sql .= " AND subscriptionmulti.invoice_to_other_customer_id > 0";
} else if ($_GET['otherCustomerFilter'] == 2) {
	$selfdefined_sql .= " AND (subscriptionmulti.invoice_to_other_customer_id is null OR subscriptionmulti.invoice_to_other_customer_id = 0)";
}
if($filtered_subtype){
	$selfdefined_sql .= " AND subscriptiontype_subtype.id = ".$o_main->db->escape($filtered_subtype['id']);
}
if($search != ""){
	$ownercompany_filter_sql .= " AND customer.id = '".$search."'";
}
if($customer_basisconfig['activateSubscriptionRenewalDateSetting']){
	$s_sql_subscription = "select subscriptionmulti.*, subscriptiontype.activate_own_tab_in_batchrenewal, subscriptiontype.name as subscriptionTypeName, subscriptiontype.periodUnit, subscriptiontype.activate_specified_invoicing,
	subscriptiontype.default_subscriptionname_in_invoiceline, CONCAT(YEAR(nextRenewalDate), '-', MONTH(nextRenewalDate), '-', subscriptionmulti.renewalappearance_daynumber) as newString, subscriptiontype.subscription_category, subscriptiontype.script_for_generating_order, subscriptiontype.useMainContactAsContactperson
	from subscriptionmulti
		left outer join subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
		left outer join subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
		left outer join customer ON customer.id = subscriptionmulti.customerId
		".$selfdefined_join."
		WHERE customer.content_status < 2 AND (subscriptiontype_subtype.is_free = 0 OR subscriptiontype_subtype.is_free is null) AND (subscriptiontype_subtype.type <> 4 OR subscriptiontype_subtype.type is null)
		AND (
			(str_to_date(nextRenewalDate,'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') AND (subscriptionmulti.renewalappearance = 0 OR subscriptionmulti.renewalappearance is null))
			OR
			(subscriptionmulti.renewalappearance = 1 AND date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL subscriptionmulti.renewalappearance_daynumber DAY)  <= str_to_date('".$dateMarker."','%Y-%m-%d'))
			OR
			(subscriptionmulti.renewalappearance = 2 AND date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL subscriptionmulti.renewalappearance_daynumber DAY) <= str_to_date('".$dateMarker."','%Y-%m-%d'))
			OR
			(subscriptionmulti.renewalappearance = 3 AND str_to_date(CONCAT(YEAR(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', MONTH(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), subscriptionmulti.renewalappearance_daynumber)),'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') )
			OR
			(subscriptionmulti.renewalappearance = 5 AND str_to_date(CONCAT(YEAR(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', MONTH(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH)), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(date_sub(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), DAY(LAST_DAY(date_add(str_to_date(nextRenewalDate,'%Y-%m-%d'), INTERVAL 1 MONTH))), subscriptionmulti.renewalappearance_daynumber)),'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d') )
			OR
			(subscriptionmulti.renewalappearance = 4 AND str_to_date(CONCAT(YEAR(nextRenewalDate), '-', MONTH(nextRenewalDate), '-', IF(subscriptionmulti.renewalappearance_daynumber > DAY(LAST_DAY(nextRenewalDate)), DAY(LAST_DAY(nextRenewalDate)), subscriptionmulti.renewalappearance_daynumber)), '%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d'))
		)
		AND ((stoppedDate = '0000-00-00' OR stoppedDate is null) OR (nextRenewalDate <> '0000-00-00' AND stoppedDate > nextRenewalDate))
		AND (freeNoBilling < 1 OR freeNoBilling IS NULL)
		AND (connectedCustomerId = 0 OR connectedCustomerId IS NULL)
		AND subscriptiontype.autorenewal = 1
		AND subscriptionmulti.content_status = 0 AND (subscriptionmulti.onhold is null OR subscriptionmulti.onhold = 0 OR subscriptionmulti.onhold = '')
		".$ownercompany_filter_sql."
		".$selfdefined_sql."
		order by customer.name ASC";
		// var_dump($s_sql_subscription);
} else {
	$s_sql_subscription = "select subscriptionmulti.*, subscriptiontype.activate_own_tab_in_batchrenewal, subscriptiontype.name as subscriptionTypeName, subscriptiontype.periodUnit, subscriptiontype.activate_specified_invoicing, subscriptiontype.default_subscriptionname_in_invoiceline, subscriptiontype.subscription_category, subscriptiontype.script_for_generating_order, subscriptiontype.useMainContactAsContactperson
	from subscriptionmulti
		left outer join subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
		left outer join subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
		left outer join customer ON customer.id = subscriptionmulti.customerId
		".$selfdefined_join."
		where customer.content_status < 2 AND (subscriptiontype_subtype.is_free = 0 OR subscriptiontype_subtype.is_free is null) AND (subscriptiontype_subtype.type <> 4 OR subscriptiontype_subtype.type is null)
		AND str_to_date(nextRenewalDate,'%Y-%m-%d') <= str_to_date('".$dateMarker."','%Y-%m-%d')
		AND ((stoppedDate = '0000-00-00' OR stoppedDate is null)  OR (nextRenewalDate <> '0000-00-00' AND stoppedDate > nextRenewalDate))
		AND (freeNoBilling < 1 OR freeNoBilling IS NULL)
		AND (connectedCustomerId = 0 OR connectedCustomerId IS NULL) AND subscriptiontype.autorenewal = 1  AND subscriptionmulti.content_status = 0 AND (subscriptionmulti.onhold is null OR subscriptionmulti.onhold = 0 OR subscriptionmulti.onhold = '')
		".$ownercompany_filter_sql."
		".$selfdefined_sql."
		order by customer.name ASC";
}
// echo $s_sql;
$o_subscribtions_raw = array();
$o_query_subscription = $o_main->db->query($s_sql_subscription);
if($o_query_subscription && $o_query_subscription->num_rows()>0){
	$o_subscribtions_raw = $o_query_subscription->result_array();
}

$showRenewal = false;
$subscriptionsNeededForAdjustment = array();
$subscriptionsNeededForAdjustmentIds = array();

$o_subscribtions = array();
$indexNew = 0;
$indexMinus = "-".count($o_subscribtions_raw);
foreach($o_subscribtions_raw as $o_subscribtion_raw) {
	if($o_subscribtion_raw['nextRenewalDate'] == '0000-00-00')
		$nextrenewaldatevalue = $o_subscribtion_raw['startDate'];
	else
		$nextrenewaldatevalue = $o_subscribtion_raw['nextRenewalDate'];

	$realStoppedDate = $o_subscribtion_raw['stoppedDate'];
	$stoppedDate = strtotime("+". $o_subscribtion_raw['periodNumberOfMonths']." months", strtotime($nextrenewaldatevalue));

	$index = $indexNew;
	if($realStoppedDate != "0000-00-00" && $realStoppedDate != null) {
		if(strtotime($realStoppedDate) < $stoppedDate) {
			$index = $indexMinus;
		}
	}
	$o_subscribtions[$index] = $o_subscribtion_raw;
	$indexNew++;
	$indexMinus++;

}
ksort($o_subscribtions);


$specifiedInvoicingCount = 0;
$normalInvoicingCount = 0;
$collectWorkInvoicingCount = 0;
$pricelistInvoicingCount = 0;
$tabList = array();
$maxNextRenewalDates = array();
foreach($o_subscribtions as $v_row){

	if($v_row['nextRenewalDate'] == '0000-00-00')
		$nextrenewaldatevalue = $v_row['startDate'];
	else
		$nextrenewaldatevalue = $v_row['nextRenewalDate'];

	if($v_row['activate_own_tab_in_batchrenewal']) {
		$tabList[$v_row['subscriptiontype_id']]['id'] = $v_row['subscriptiontype_id'];
		$tabList[$v_row['subscriptiontype_id']]['name'] = $v_row['subscriptionTypeName'];
		$tabList[$v_row['subscriptiontype_id']]['count']++;


		// if(strtotime($nextrenewaldatevalue) > strtotime($maxNextRenewalDate)){
		// 	$maxNextRenewalDate = $nextrenewaldatevalue;
		// }
		$maxNextRenewalDate = $dateMarker;
	} else {
		if($v_row['activate_specified_invoicing']) {
			$specifiedInvoicingCount++;
			$tabIndex = 'subscriptiontype_'.$v_row['subscriptiontype_id'];
		} else {
			if($v_row['subscription_category'] == 1) {
				$collectWorkInvoicingCount++;
				$tabIndex = "collect_invoicing";
			} else if($v_row['subscription_category'] == 2) {
				$pricelistInvoicingCount++;
				$tabIndex = "pricelist_invoicing";
			} else {
				$normalInvoicingCount++;
				$tabIndex = "normal_invoicing";
			}
		}
	}

	// if(strtotime($nextrenewaldatevalue) > strtotime($maxNextRenewalDates[$tabIndex])){
		$maxNextRenewalDates[$tabIndex] = $dateMarker;
	// }
}
?>
<div id="out-customer-list">
	<?php if($activateMultiOwnerCompanies && $totalOwnerCompanies > 1) { ?>
		<div class="out-select-all">
			<input type="checkbox" autocomplete="off" id="selectDeselectAll" checked> <label for="selectDeselectAll"><?php echo $formText_SelectAll_output; ?></label>

			<div class="select-date">
				<?php echo $formText_DisplayRenewalsUntilDate_output;?>
				<input type="text" class="datepicker" value="<?php echo date("d.m.Y", strtotime($dateMarker));?>" />
			</div>
			<div class="ownercompaniesSelectWrapper">
				<label><?php echo $formText_FilterByOwnerCompany_output;  if(count($real_ownercompany_filter) > 0) echo " (".count($real_ownercompany_filter).")";?></label>
				<div class="filterWrapper <?php if(count($real_ownercompany_filter) > 0) echo 'active';?>">
					<div class="filterRow">
						<input type="checkbox" autocomplete="off" class="ownercompaniesSelect" value="" <?php if(count($real_ownercompany_filter) == 0) echo 'checked';?> /><span><?php echo $formText_All_output;?></span>
					</div>
					<?php foreach($ownercompanies as $ownercompany) { ?>
						<div class="filterRow">
							<input type="checkbox" autocomplete="off" class="ownercompaniesSelect" value="<?php echo $ownercompany['id']?>" <?php if(in_array($ownercompany['id'], $real_ownercompany_filter)) echo 'checked';?>/><span><?php echo $ownercompany['name']?></span>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="clear"></div>
		</div>
	<?php } else { ?>
		<div class="out-select-all">
			<input type="checkbox"  autocomplete="off" id="selectDeselectAll"> <label for="selectDeselectAll"><?php echo $formText_SelectAll_output; ?></label>
		</div>
		<div class="select-date">
			<?php echo $formText_DisplayRenewalsUntilDate_output;?>
			<input type="text" class="datepicker" value="<?php echo date("d.m.Y", strtotime($dateMarker));?>" />
		</div>

	<?php } ?>
	<div class="clear"></div>
	<div style=" float: right; margin-top: 5px;">
		<?php if($batch_renewal_accountconfig['activateCustomerSelfdefnedListFilter']) {?>
			<div class="selfdefinedFilterWrapper" style="text-align: right;">
				<?php echo $formText_CustomerSelfdefinedListFilter_output; ?>
				<span class="selectDiv selected">
					<span class="selectDivWrapper">
						<select name="defaultSelect" class="customerSelfdefinedListFilter" autocomplete="off">
							<option value=""><?php echo $formText_All_output;?></option>
							<?php
							$s_sql = "SELECT * FROM customer_selfdefined_fields WHERE id = ?";
							$o_query = $o_main->db->query($s_sql, array($batch_renewal_accountconfig['customerSelfdefinedField']));
							$customer_selfdefined_field = ($o_query ? $o_query->row_array():array());

							$sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name";
							$o_query = $o_main->db->query($sql, array($customer_selfdefined_field['list_id']));
							if($o_query && $o_query->num_rows()>0)
							foreach($o_query->result_array() as $row)
							{
								?><option value="<?php echo $row['id']; ?>" <?php if($customerselfdefinedlist_filter == $row['id']) { echo 'selected';}?>><?php echo $row['name']; ?></option><?php
							}
							?>
						</select>
					</span>
					<span class="arrowDown"></span>
				</span>
			</div>
			<script type="text/javascript">
				$('.customerSelfdefinedListFilter').on('change', function(e) {
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&department_filter=".$_GET['department_filter']."&project_filter=".$_GET['project_filter']."&subtypeFilter=".$_GET['subtypeFilter'];?>&customerselfdefinedlist_filter="+$(this).val(), '', true);
				});

			</script>
		<?php } ?>
		<?php if($batch_renewal_accountconfig['activate_filter_by_department']) {?>
			<div class="departmentFilterWrapper" style="text-align: right;">
				<?php echo $formText_DepartmentFilter_output; ?>
				<span class="selectDiv selected">
					<span class="selectDivWrapper">
						<select name="defaultSelect" class="departmentFilter" autocomplete="off">
							<option value=""><?php echo $formText_All_output;?></option>
							<?php
								$s_sql = "SELECT * FROM departmentforaccounting
								WHERE departmentforaccounting.content_status < 2 ORDER BY departmentforaccounting.departmentnumber ASC";

								$o_query = $o_main->db->query($s_sql);
								$departments = ($o_query ? $o_query->result_array() : array());
								foreach($departments as $department) {
									?>
									<option value="<?php echo $department['departmentnumber'];?>" <?php if($department_id == $department['departmentnumber']) echo 'selected';?>><?php echo $department['name']?></option>
									<?php
								}
							?>
						</select>
					</span>
					<span class="arrowDown"></span>
				</span>
			</div>
			<script type="text/javascript">
				$('.departmentFilter').on('change', function(e) {
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&project_filter=".$_GET['project_filter']."&subtypeFilter=".$_GET['subtypeFilter'];?>&department_filter="+$(this).val(), '', true);
				});
			</script>
		<?php } ?>
		<?php if($batch_renewal_accountconfig['activate_filter_by_project']) { ?>
			<div class="projectFilterWrapper" style="text-align: right;">
				<?php echo $formText_ProjectFilter_output; ?>
				<span class="selectDiv selected">
					<span class="selectDivWrapper">
						<select name="defaultSelect" class="projectFilter" autocomplete="off">
							<option value=""><?php echo $formText_All_output;?></option>
							<?php
							function getProjects($o_main, $parentNumber = 0) {
								$projects = array();

								if ($parentNumber) {
									$o_main->db->order_by('projectnumber', 'ASC');
									$o_query = $o_main->db->get_where('projectforaccounting', array('parentNumber' => $parentNumber));
								} else {
									$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY projectnumber");
								}

								if ($o_query && $o_query->num_rows()) {
									foreach ($o_query->result_array() as $row) {
										array_push($projects, array(
											'id' => $row['id'],
											'name' => $row['name'],
											'number' => $row['projectnumber'],
											'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
											'children' => getProjects($o_main, $row['projectnumber'])
										));
									}
								}

								return $projects;
							}

							function getProjectsOptionsListHtml($projects, $level, $accountingproject_code) {
								ob_start(); ?>

								<?php foreach ($projects as $project): ?>
									<option value="<?php echo $project['number']; ?>" <?php echo $project['number'] == $accountingproject_code ? 'selected="selected"' : ''; ?>>
										<?php
										$identer = '';
										for($i = 0; $i < $level; $i++) { $identer .= '-'; }
										echo $identer;
										?>
										<?php echo $project['number']; ?> <?php echo $project['name']; ?>
									</option>

									<?php if (count($project['children'])): ?>
										<?php echo getProjectsOptionsListHtml($project['children'], $level+1, $accountingproject_code); ?>
									<?php endif; ?>
								<?php endforeach; ?>

								<?php return ob_get_clean();
							}

							$projects = getProjects($o_main);
							echo getProjectsOptionsListHtml($projects, 0, $project_id);
							?>
						</select>
					</span>
					<span class="arrowDown"></span>
				</span>
			</div>
			<script type="text/javascript">
				$('.projectFilter').on('change', function(e) {
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&department_filter=".$_GET['department_filter']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&subtypeFilter=".$_GET['subtypeFilter'];?>&project_filter="+$(this).val(), '', true);
				});
			</script>
		<?php } ?>

		<?php if($batch_renewal_accountconfig['activateFilterForSubtype']) {?>
			<?php
			foreach($tabList as $tabItem) {
				$s_sql = "SELECT * FROM subscriptiontype_subtype
				WHERE subscriptiontype_id = ?";
				$o_query = $o_main->db->query($s_sql, array($tabItem['id']));
				$subtypes = ($o_query ? $o_query->result_array() : array());

				?>
				<div class="subscriptionSubtypeWrapper subscriptionSubtypeWrapper<?php echo $tabItem['id'];?>" style="text-align: right;">
					<?php echo $formText_FilterBySubtypes_output; ?>
					<span class="selectDiv selected">
						<span class="selectDivWrapper">
							<select name="subtypeFilter" class="subtypeFilter" autocomplete="off">
								<option value=""><?php echo $formText_All_output;?></option>
								<?php foreach($subtypes as $subtype) { ?>
									<option value="<?php echo $subtype['id']?>" <?php if($filtered_subtype['id'] == $subtype['id']) echo 'selected';?>><?php echo $subtype['name'];?></option>
								<?php } ?>
							</select>
						</span>
						<span class="arrowDown"></span>
					</span>
				</div>
				<?php
			}
			?>

			<script type="text/javascript">
				$('.subtypeFilter').on('change', function(e) {
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&project_filter=".$_GET['project_filter']."&department_filter=".$_GET['department_filter']."&otherCustomerFilter=".$_GET['otherCustomerFilter'];?>&subtypeFilter="+$(this).val(), '', true);
				});
			</script>
		<?php } ?>
		<?php if($batch_renewal_accountconfig['activateFilterSendToOtherCustomer']) {?>
			<div class="departmentFilterWrapper" style="text-align: right;">
				<?php echo $formText_FilterBySendToOtherCustomer_output; ?>
				<span class="selectDiv selected">
					<span class="selectDivWrapper">
						<select name="otherCustomerFilter" class="otherCustomerFilter" autocomplete="off">
							<option value=""><?php echo $formText_All_output;?></option>
							<option value="1" <?php if($_GET['otherCustomerFilter'] == 1) echo 'selected';?>><?php echo $formText_SentToOtherCustomer_output;?></option>
							<option value="2" <?php if($_GET['otherCustomerFilter'] == 2) echo 'selected';?>><?php echo $formText_NotSentToOtherCustomer_output;?></option>

						</select>
					</span>
					<span class="arrowDown"></span>
				</span>
			</div>
			<script type="text/javascript">
				$('.otherCustomerFilter').on('change', function(e) {
					fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&project_filter=".$_GET['project_filter']."&department_filter=".$_GET['department_filter']."&subtypeFilter=".$_GET['subtypeFilter'];?>&otherCustomerFilter="+$(this).val(), '', true);
				});
			</script>
		<?php } ?>

		<div class="p_contentBlock">
			<div class="employeeSearch">
				<span class="glyphicon glyphicon-search"></span>
				<input type="text" placeholder="<?php echo $formText_Customer_output;?>" value="<?php echo $searchedCustomer['customerName']?>" class="employeeSearchInput" autocomplete="off"/>
				<span class="glyphicon glyphicon-triangle-right"></span>
				<div class="employeeSearchSuggestions allowScroll"></div>

				<?php if($search != "") { ?>
			        <div class="filteredCountRow">
			            <span class="selectionCount"><?php echo count($o_subscribtions);?></span> <?php echo $formText_InSelection_output;?>
			            <div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
			        </div>
				<?php } ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	<div class="out-dynamic">

	<?php

	foreach($o_subscribtions as $subscription) {
		if($subscription['priceAdjustmentType'] == 1) {
			$dateToCheck = $subscription['annualAdjustmentDate'];
			if(!validateDate($subscription['annualAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if(strtotime($dateToCheck) <= strtotime($subscription['nextRenewalDate'])){
				array_push($subscriptionsNeededForAdjustment, $subscription);
				array_push($subscriptionsNeededForAdjustmentIds, $subscription['id']);
			}
		} else if($subscription['priceAdjustmentType'] == 2) {
			$dateToCheck = $subscription['nextCpiAdjustmentDate'];
			if(!validateDate($subscription['nextCpiAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if(strtotime($dateToCheck) <= strtotime($subscription['nextRenewalDate'])){
				array_push($subscriptionsNeededForAdjustment, $subscription);
				array_push($subscriptionsNeededForAdjustmentIds, $subscription['id']);
			}
		} else if($subscription['priceAdjustmentType'] == 3) {
			$dateToCheck = $subscription['nextManualAdjustmentDate'];
			if(!validateDate($subscription['nextManualAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if(strtotime($dateToCheck) <= strtotime($subscription['nextRenewalDate'])){
				array_push($subscriptionsNeededForAdjustment, $subscription);
				array_push($subscriptionsNeededForAdjustmentIds, $subscription['id']);
			}
		}
	}
	if(count($subscriptionsNeededForAdjustment) > 0){
		$v_cpi_indexes = array();
		$s_response = APIConnectorAccount('cpi_index_get_list', $variables->accountinfo['accountname'], $variables->accountinfo['password'], array());
		$v_response = json_decode($s_response, TRUE);
		if(isset($v_response['status'], $v_response['items']) && 1 == $v_response['status'])
		foreach($v_response['items'] as $v_item)
		{
			$v_cpi_indexes[$v_item['index_date']] = $v_item;
		}
	}
	//update the prices according to adjustment
	if($_POST['performAdjustment'])
	{

		foreach($subscriptionsNeededForAdjustment as $v_row) {
			if(!in_array($v_row["id"], $_POST["adjustmentSelection"])) continue;
    		$l_id = array_search($v_row["id"], $_POST["subscribtion_id"]);
    		if($l_id === false) continue;
			$updated = false;
			$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			if($o_query && $o_query->num_rows()>0){
				$sl_rows = $o_query->result_array();
				foreach($sl_rows as $sl_row) {
					//calculating new price
					if($v_row['priceAdjustmentType'] == 1) {
						$newPricePerPiece = $sl_row['pricePerPiece'] * ((100+$v_row['annualPercentageAdjustment'])/100);

						if($o_main->db->query("UPDATE subscriptionline SET pricePerPiece = ? WHERE id = ?", array($newPricePerPiece, $sl_row['id']))){
							$updated = true;
						}
					} else if($v_row['priceAdjustmentType'] == 2) {
						$cpiPercentage = 0;
						$cpiError = false;

						$s_key = date("Y-m-01", strtotime($v_row['nextCpiAdjustmentFoundationDate']));
						$indexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

						$s_key = date("Y-m-01", strtotime($v_row['lastCpiAdjustmentFoundationDate']));
						$lastIndexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

						if($indexItem && $indexItem['index_number'] > 0){
							$adjustmentIndex = str_replace(",",".",$indexItem['index_number']);
						} else {
							$cpiError = true;
						}

						if($lastIndexItem && $lastIndexItem['index_number'] > 0){
							$lastAdjustmentIndex = str_replace(",",".",$lastIndexItem['index_number']);
						} else {
							$cpiError = true;
						}
						if(!$cpiError){
							$cpiPercentage = ($adjustmentIndex - $lastAdjustmentIndex)*100/$lastAdjustmentIndex;
							$cpiPercentage = number_format($cpiPercentage, 2, ".", "");
							$adjustmentIndexDiff = ($adjustmentIndex - $lastAdjustmentIndex) * (intval($sl_row['cpiAdjustmentFactor'])/100);
							$newPricePerPiece = round($sl_row['pricePerPiece']/$lastAdjustmentIndex*($lastAdjustmentIndex+$adjustmentIndexDiff), 2);

							if($o_main->db->query("UPDATE subscriptionline SET pricePerPiece = ? WHERE id = ?", array($newPricePerPiece, $sl_row['id']))){
								$updated = true;
							}
						}
					}
				}
			}
			if($updated) {
				if($v_row['priceAdjustmentType'] == 1) {
					$dateToCheck = $v_row['annualAdjustmentDate'];
					if(!validateDate($v_row['annualAdjustmentDate'])){
						$dateToCheck = date("Y-m-d");
					}
					$o_main->db->query("UPDATE subscriptionmulti SET annualAdjustmentDate = ? WHERE id = ?", array(date("Y-m-d", strtotime("+1 year", strtotime($dateToCheck))), $v_row['id']));

				} else if($v_row['priceAdjustmentType'] == 2) {
					$cpiAdjustmentDate = $v_row['nextCpiAdjustmentDate'];
					if(!validateDate($v_row['nextCpiAdjustmentDate'])){
						$cpiAdjustmentDate = date("Y-m-d");
					}
					$cpiAdjustmentFoundationDate = $v_row['nextCpiAdjustmentFoundationDate'];
					if(!validateDate($v_row['nextCpiAdjustmentFoundationDate'])){
						$cpiAdjustmentFoundationDate = date("Y-m-d");
					}
					if($batch_renewal_accountconfig['activatePriceAdjustmentLetter']){
						create_adjustment_letter($v_row, $sl_rows, $v_cpi_indexes);
					}

					$o_main->db->query("UPDATE subscriptionmulti SET lastCpiAdjustmentDate = ?, lastCpiAdjustmentFoundationDate = ?, nextCpiAdjustmentDate = ?,nextCpiAdjustmentFoundationDate = ?
						WHERE id = ?", array($cpiAdjustmentDate, $cpiAdjustmentFoundationDate, date("Y-m-d", strtotime("+1 year", strtotime($cpiAdjustmentDate))), date("Y-m-d", strtotime("+1 year", strtotime($cpiAdjustmentFoundationDate))), $v_row['id']));
				}
			}
		}
	}

	if(count($subscriptionsNeededForAdjustment) == 0){
		$showRenewal = true;
	}
	if($_GET['skipAdjustment']) {
		$showRenewal = true;
	}
	if(!$showRenewal){
		?>
		<div class="adjustmentError"><span class="glyphicon glyphicon-alert"></span><?php echo $formText_TheFollowingContractsMustBeAdjustedBeforeRenewal_output;?></div>
		<?php
		foreach($subscriptionsNeededForAdjustment as $v_row) {
			$noError = true;
			$totalTotal = 0;
			$sl_rows = array();
			$s_sql = "SELECT * FROM subscriptionline WHERE subscribtionId = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['id']));
			if($o_query && $o_query->num_rows()>0){
				$sl_rows = $o_query->result_array();
			}

			if($v_row["invoice_to_other_customer_id"] > 0){
				$v_row["customerId"] = $v_row["invoice_to_other_customer_id"];
			}
			$s_sql = "select * from customer where id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row["customerId"]));
			if($o_query && $o_query->num_rows()>0){
				$v_customer = $o_query->row_array();
			}

			$s_address = "";
			foreach($v_address_format as $s_key)
			{
				if($v_customer[$s_key] != "")
				{
					if($s_address != "") $s_address .= ", ";
					$s_address .= $v_customer[$s_key];
				}
			}
			$l_price = round($v_row['pricePerPiece'] * (1 + ($l_raise_percent / 100)), 2);
			$l_total = $l_price * $v_row['amount'];
			$l_total = round($l_total - ($l_total * ($v_row['discountPercent'] / 100)), 2);

			if($v_row['nextRenewalDate'] == '0000-00-00')
				$nextrenewaldatevalue = $v_row['startDate'];
			else
				$nextrenewaldatevalue = $v_row['nextRenewalDate'];
			$lastdate = $nextdate = $nextrenewaldatevalue;
			$nextdate2 = strtotime($nextdate);
			//
			$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));
			if(intval($v_row['periodUnit']) == 0){
				$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
				$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
			} else {
				$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths']));
				$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths'])-24*60*60);
			}
			$lastdate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('y',$nextdate2)));

			$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
			$o_query = $o_main->db->query($s_sql, array($v_row['ownercompany_id']));
			$ownerCompanyData = $o_query ? $o_query->row_array() : array();
			if($v_row['useMainContactAsContactperson']){
				$s_sql = "SELECT contactperson.* FROM contactperson
				WHERE contactperson.customerId = ? AND contactperson.mainContact = 1";
				$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
				$contactPersonData = $o_query ? $o_query->row_array() : array();
			} else {
				$s_sql = "SELECT contactperson.* FROM contactperson_role_conn
				LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
				WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
				ORDER BY contactperson_role_conn.role DESC";
				$o_query = $o_main->db->query($s_sql, array($v_row['id']));
				$contactPersonData = $o_query ? $o_query->row_array() : array();
			}

			$dateToCheck = $v_row['annualAdjustmentDate'];
			if(!validateDate($v_row['annualAdjustmentDate'])){
				$dateToCheck = date("Y-m-d");
			}
			if($v_row['priceAdjustmentType'] == 2){
				$cpiPercentage = 0;
				$adjustmentError = false;
				$s_key = date("Y-m-01", strtotime($v_row['nextCpiAdjustmentFoundationDate']));
				$indexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

				$s_key = date("Y-m-01", strtotime($v_row['lastCpiAdjustmentFoundationDate']));
				$lastIndexItem = (isset($v_cpi_indexes[$s_key]) ? $v_cpi_indexes[$s_key] : array());

				if($indexItem && $indexItem['index_number'] > 0){
					$adjustmentIndex = str_replace(",",".",$indexItem['index_number']);
				} else {
					$adjustmentError = true;
				}

				if($lastIndexItem && $lastIndexItem['index_number'] > 0){
					$lastAdjustmentIndex = str_replace(",",".",$lastIndexItem['index_number']);
				} else {
					$adjustmentError = true;
				}
				if(!$adjustmentError){
					$cpiPercentage = ($adjustmentIndex - $lastAdjustmentIndex)*100/$lastAdjustmentIndex;
					$cpiPercentage = number_format($cpiPercentage, 2, ".", "");
				} else {
					$noError = false;
				}
			}
			$cpiFactorError = false;
			foreach($sl_rows as $sl_row){
				if($v_row['priceAdjustmentType'] == 2 ) {
					if($sl_row['cpiAdjustmentFactor'] == null) {
						$cpiFactorError = true;
					}
				}
			}
			?>
			<div class="item-customer price_adjustment">
				<div class="item-title">
					<?php if(!$ownerCompanyData) { ?>
					<div class="titleError"><?php echo $formText_NoOwnerCompany_Output;?></div>
					<?php } ?>
					<input type="hidden" value="<?php echo $v_row['id'];?>" name="subscribtion_id[]" />
					<input type="hidden" class="price" value="<?php echo $l_price;?>" name="price[]" />
					<div>
					<?php if($noError && $v_row['priceAdjustmentType'] != 3 && !$cpiFactorError) { ?>
						<input type="checkbox" autocomplete="off" value="<?php echo $v_row['id'];?>" name="adjustmentSelection[]" <?php echo ($v_row["annualAdjustmentDate"] != "0000-00-00" ? "checked":"");?>  />
					<?php } ?>
					<?php echo $v_customer['name'];?>

	                <?php if ($activateMultiOwnerCompanies > 1 && $totalOwnerCompanies > 1): ?>
	                    <div>
	                        <small>
	                            (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $ownerCompanyData['name']; ?>)
	                        </small>
	                    </div>
	                <?php endif; ?>

					</div>
					<br clear="all">
				</div>
				<div class="item-price-adjustment">
					<?php if($v_row['priceAdjustmentType'] == 1 ) { ?>
						<span><b><?php echo $formText_PriceAdjustment_output?>:
					<?php
						echo "%</b>";
						echo "</span><span>".$formText_AnnualAdjustment_output.": ".$v_row['annualPercentageAdjustment'] ."%";
						echo "</span><span>".$formText_AnnualAdjustmentDate_output.": ".date("d.m.Y", strtotime($v_row['annualAdjustmentDate']));
					} else if($v_row['priceAdjustmentType'] == 2 ) {
					?>
						<b><?php echo $formText_PriceAdjustment_output.": ".$formText_CPI_output;?></b>
						<table class="table table-condensed">
							<thead>
								<tr>
									<td colspan="3" class="priceLabel dividerLine"><?php echo $formText_LastAdjustment_output;?></td>
									<td colspan="3" class="priceLabel"><?php echo $formText_ThisAdjustment_output;?></td>
								</tr>
								<tr>
									<th class="text-center"><?php echo $formText_AdjustmentDate_Output;?></th>
									<th class="text-center"><?php echo $formText_AdjustmentFoundationDate_Output;?></th>
									<th class="dividerLine text-center"><?php echo $formText_AdjustmentIndex_Output;?></th>
									<th class="text-center"><?php echo $formText_AdjustmentDate_Output;?></th>
									<th class="text-center"><?php echo $formText_AdjustmentFoundationDate_Output;?></th>
									<th class="text-right"><?php echo $formText_AdjustmentIndex_Output;?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-center"><?php echo date("d.m.Y", strtotime($v_row['lastCpiAdjustmentDate']));?></td>
									<td class="text-center"><?php echo date("d.m.Y", strtotime($v_row['lastCpiAdjustmentFoundationDate']));?></td>
									<td class="dividerLine text-center"><?php echo number_format($lastAdjustmentIndex, 2, ",", " ");?></td>
									<td class="text-center"><?php echo date("d.m.Y", strtotime($v_row['nextCpiAdjustmentDate']));?></td>
									<td class="text-center"><?php echo date("d.m.Y", strtotime($v_row['nextCpiAdjustmentFoundationDate']));?></td>
									<td class="text-right"><?php echo  number_format($adjustmentIndex, 2, ",", " ");?></td>
								</tr>
								<tr>
									<td ><div class="priceLabel"><?php if($adjustmentError) echo $formText_CpiAdjustmentMissingIndexes_output;?></div><?php //echo $v_row['amount']; ?></td>
									<td class=""><?php //echo $v_row['discountPercent']; ?></td>
									<td></td>
									<td></td>
									<td></td>
									<td class="item-total text-right last"><?php echo $formText_AdjustmentPercent_output.": ".number_format($cpiPercentage, 2, ",", "")."%"; ?></td>
								</tr>
							</tbody>
						</table>
					<?php
				} else if($v_row['priceAdjustmentType'] == 3) {?>
					<span><b><?php echo $formText_PriceAdjustment_output?>:
					<?php
					echo "</b>";
					echo "</span><span>".$formText_ManualPriceAdjustment_output;
					echo "</span><span>".$formText_NextManualAdjustmentDate_Output_output.": ".date("d.m.Y", strtotime($v_row['nextManualAdjustmentDate']));
				}
					?>
				</div>
				<?php if($v_row['priceAdjustmentType'] != 3) {
					?>
					<div class="item-order">
						<table class="table table-condensed">
						<thead>
							<tr>
								<td></td>
								<td colspan="4" class="priceLabel dividerLine"><?php echo $formText_ExistingPrice_output;?></td>
								<?php if($v_row['priceAdjustmentType'] == 2 ) { ?>
									<td colspan="2" class="priceLabel dividerLine">&nbsp;</td>
								<?php } ?>
								<td colspan="3" class="priceLabel"><?php echo $formText_NewPriceFrom_output . " ". date("d.m.Y", strtotime($v_row['nextCpiAdjustmentDate']));?></td>
							</tr>
							<tr>
								<th><?php echo $formText_OrderlineText_Output;?></th>
								<th class="text-center"><?php echo $formText_Amount_Output;?></th>
								<th class="text-center"><?php echo $formText_PricePerPiece_Output;?></th>
								<th class="text-center"><?php echo $formText_Discount_Output;?></th>
								<th class="dividerLine text-center"><?php echo $formText_TotalPrice_Output;?></th>
								<?php if($v_row['priceAdjustmentType'] == 2 ) { ?>
									<th class="text-center"><?php echo $formText_CpiAdjustmentPercentage_Output;?></th>
									<th class="dividerLine text-center"><?php echo $formText_CpiAdjustmentFactor_output;?></th>
								<?php } ?>
								<th class="text-center"><?php echo $formText_PricePerPiece_Output;?></th>
								<th class="text-center"><?php echo $formText_Discount_Output;?></th>
								<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
							</tr>
						</thead>
						<tbody>

							<?php

							foreach($sl_rows as $sl_row){
								if($v_row['subscription_category'] == 1){
									$totalAmount = 1 * $sl_row['amount'];
								} else if($v_row['override_periods'] > 0) {
									$totalAmount = $v_row['override_periods'] * $sl_row['amount'];
								} else {
									$totalAmount = $v_row['periodNumberOfMonths'] * $sl_row['amount'];
								}

								$totalRowPrice = $totalAmount * $sl_row['pricePerPiece'] * ((100-$sl_row['discountPercent'])/100);
								//calculating new price
								if($v_row['priceAdjustmentType'] == 1) {
									$newPricePerPiece = round($sl_row['pricePerPiece'] * ((100+$v_row['annualPercentageAdjustment'])/100), 2);

									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
								} else if($v_row['priceAdjustmentType'] == 2) {
									$adjustmentIndexDiff = ($adjustmentIndex - $lastAdjustmentIndex) * (intval($sl_row['cpiAdjustmentFactor'])/100);
									$newPricePerPiece = round($sl_row['pricePerPiece']/$lastAdjustmentIndex*($lastAdjustmentIndex+$adjustmentIndexDiff), 2);

									$totalNewRowPrice = $totalAmount * $newPricePerPiece * ((100-$sl_row['discountPercent'])/100);
								}

								$totalTotal += $totalNewRowPrice;

								?>
								<tr>
									<td width="30%" style="padding-right: 15px;"><?php echo $v_row['subscriptionName'] . " - ". $sl_row['articleName']. " (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")"; ?></td>
									<td class="text-center"><?php echo $totalAmount; ?></td>
									<td class="text-center"><?php echo  number_format($sl_row['pricePerPiece'], 2, ",", " "); ?></td>
									<td class="text-center"><?php echo  number_format($sl_row['discountPercent'], 2, ",", " "); ?>%</td>
									<td class="item-total text-center dividerLine"><?php echo  number_format($totalRowPrice, 2, ",", " "); ?></td>
									<?php if($v_row['priceAdjustmentType'] == 2 ) { ?>
										<td class="text-center"><?php echo number_format($cpiPercentage, 2, ",", "");?></td>
										<td class="dividerLine text-center"><?php echo $sl_row['cpiAdjustmentFactor'];?></td>
									<?php } ?>
									<td class="text-center"><?php echo number_format($newPricePerPiece, 2, ",", " "); ?></td>
									<td class="text-center"><?php echo number_format($sl_row['discountPercent'], 2, ",", " "); ?>%</td>
									<td class="text-right"><?php echo  number_format($totalNewRowPrice, 2, ",", " "); ?></td>
								</tr>
								<?php } ?>
							<tr>
								<td>
								<?php if($cpiFactorError) { ?>
									<div class="priceLabel" style="text-align: left;"> <?php echo $formText_SomeCpiAdjustmentFactorAreMissing_output;?></div>
									<?php
								}?></td>
								<td width="8%" class="item-price"><?php //echo $l_price; ?></td>
								<td width="8%"><?php //echo $v_row['amount']; ?></td>
								<td width="8%" class=""><?php //echo $v_row['discountPercent']; ?></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td width="8%" class="item-total text-right last"><?php echo number_format($totalTotal, 2, ",", " "); ?></td>
							</tr>
						</tbody>
						</table>
					</div>
				<?php } else { ?>
					<div class="item-order">
						<div class="priceLabel" style="padding: 20px 10px;"><?php echo $formText_ManualPriceAdjustmentIsNeeded_output;?></div>
					</div>
				<?php } ?>
			</div>


			<?php
		}
		?>
		<div class="out-buttons">
			<div class="performAdjustment">
				<?php echo $formText_CompletePriceAdjustment_output;?> (<span class="adjustmentCount">0</span>)
			</div>
			<div class="skipAdjustment"><a href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany'];?>&date=<?php echo $_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']?>&skipAdjustment=1"><?php echo $formText_SkipAndMoveOn_output;?></a></div>
			<div class="clear"></div>
		</div>
		<?php
	} else {
		if(count($subscriptionsNeededForAdjustmentIds) > 0){
			?>
			<div class="adjustmentError">
				<a href="<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany'];?>&date=<?php echo $_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']?>&skipAdjustment=0">
					<span class="glyphicon glyphicon-alert"></span><?php echo $formText_SeeContractsThatMustBeAdjustedBeforeBilling_output;?> (<?php echo count($subscriptionsNeededForAdjustment);?>)
				</a>
			</div>
			<?php
		} else {
		}

		$s_sql = "SELECT * FROM subscriptionmulti WHERE onhold = 1 AND content_status < 2 ".$ownercompany_filter_sql;
		$o_query = $o_main->db->query($s_sql);
		$onholdOrdersCount = $o_query ? $o_query->num_rows() : 0;
		if($onholdOrdersCount > 0){
			?>
			<div class="onholdOrders"><?php echo $formText_OnHoldOrders_output;?> (<?php echo $onholdOrdersCount;?>)</div>
			<div class="clear"></div>
			<?php
		}

		$activateTabs = false;
		if($specifiedInvoicingCount > 0){
			$activateTabs = true;
		} else {
			if($collectWorkInvoicingCount > 0) {
				$activateTabs = true;
			}
			if($pricelistInvoicingCount > 0) {
				$activateTabs = true;
			}
		}
		if(count($tabList)){
			$activateTabs = true;
		}
		if($activateTabs) {
			?>
			<div class="tabNormal tabButtons active"><?php echo $formText_NormalRepeatingInvoicing_output;?> (<?php echo $normalInvoicingCount;?>)</div>
			<?php if($collectWorkInvoicingCount > 0) { ?>
				<div class="tabCollect tabButtons"><?php echo $formText_CollectWorkProjectInvoicing_output;?> (<?php echo $collectWorkInvoicingCount;?>)</div>
			<?php } ?>
			<?php if($specifiedInvoicingCount > 0) { ?>
				<div class="tabSpecified tabButtons"><?php echo $formText_SpecifiedInvoicing_output?> (<?php echo $specifiedInvoicingCount;?>)</div>
			<?php } ?>
			<?php if($pricelistInvoicingCount > 0) { ?>
				<div class="tabPriceList tabButtons"><?php echo $formText_PriceListInvoicing_output?> (<?php echo $pricelistInvoicingCount;?>)</div>
			<?php } ?>
			<?php
			foreach($tabList as $tabItem) {
				?>
				<div class="tabButtons" data-subscriptiontype-id="<?php echo $tabItem['id'];?>"><?php echo $tabItem['name']?> (<?php echo $tabItem['count'];?>)</div>
				<?php
			}
			?>
			<div class="clear"></div>
			<?php
		}



		?>

		<?php
		$warningCount = array();
		$errorCount = array();
		$warningCountMultiple = array();
		$changedTitle = 0;
		foreach($o_subscribtions as $v_row){
			if(!in_array($v_row['id'], $subscriptionsNeededForAdjustmentIds)){
				$s_order_reference = $s_order_delivery_date = $s_order_delivery_address = '';

				$s_order_delivery_date = ((!empty($v_row['delivery_date']) && $v_row['delivery_date'] != '0000-00-00') ? date('d.m.Y', strtotime($v_row['delivery_date'])) : '');

				$s_sql = "SELECT * FROM customer_subunit WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['customer_subunit_id']));
				$subunit = $o_query ? $o_query->row_array() : array();
				if($v_row['address_source'] == 0 && $subunit){
					$s_order_delivery_address = trim(preg_replace('/\s+/', ' ', $subunit['delivery_address_line_1'].' '.$subunit['delivery_address_city'].' '.$subunit['delivery_address_postal_code'].' '.$v_countries[$subunit['delivery_address_country']]));
				} else {
					$s_order_delivery_address = trim(preg_replace('/\s+/', ' ', $v_row['delivery_address_line_1'].' '.$v_row['delivery_address_line_2'].' '.$v_row['delivery_address_city'].' '.$v_row['delivery_address_postal_code'].' '.$v_countries[$v_row['delivery_address_country']]));
				}
				$s_sql = "SELECT * FROM customer WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
				$customer = $o_query ? $o_query->row_array() : array();
				if($v_row['reference'] == "") {
					if($subunit) {
						$s_order_reference = $subunit['reference'];
					} else {
						$s_order_reference = $customer['defaultInvoiceReference'];
					}
				} else {
					if($v_row['reference'] != 'empty') {
						$s_order_reference = $v_row['reference'];
					}
				}
				if($v_customer_accountconfig['activate_subunits']) {
					$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? AND content_status < 1 ORDER BY customer_subunit.id ASC";
					$o_query = $o_main->db->query($s_sql, array($customer['id']));
					$subunits = $o_query ? $o_query->result_array() : array();
					if(count($subunits) > 0){
						if($subunit['putSubunitAddressInDeliveryAddress']) {
							$s_order_delivery_address = trim(preg_replace('/\s+/', ' ', $subunit['name']." ".$subunit['delivery_address_line_1'].' '.$subunit['delivery_address_city'].' '.$subunit['delivery_address_postal_code'].' '.$v_countries[$subunit['delivery_address_country']]));
						}
					}
				}

				if($v_row["invoice_to_other_customer_id"] > 0){
					$v_row["customerId"] = $v_row["invoice_to_other_customer_id"];
				}

				if(intval($v_row['placeSubscriptionNameInInvoiceLine']) == 0){
					$v_row['placeSubscriptionNameInInvoiceLine'] = $v_row['default_subscriptionname_in_invoiceline'];
				} else {
					$v_row['placeSubscriptionNameInInvoiceLine']--;
				}

				$projectCode = "";
				$projectCodeItem = false;

				if($v_row['projectId'] != ""){
					$projectCode = $v_row['projectId'];
				}

				if($batch_renewal_basisconfig['activateGetProjectcodeFromSubscription']){
					$connectTablename = $batch_renewal_basisconfig['tablenameOnConnecttable'];
					$fieldInConnecttableForSubscriptionId = $batch_renewal_basisconfig['fieldInConnecttableForSubscriptionId'];
					$fieldInConnecttableForConnectedRecordId = $batch_renewal_basisconfig['fieldInConnecttableForConnectedRecordId'];
					$connectedRecordTableName = $batch_renewal_basisconfig['connectedRecordTableName'];
					$connectedRecordConnectionFieldname = $batch_renewal_basisconfig['connectedRecordConnectionFieldname'];
					$tablenameToGetProjectcodeFrom = $batch_renewal_basisconfig['tablenameToGetProjectcodeFrom'];
					$fieldnameToGetProjectcodeFrom = $batch_renewal_basisconfig['fieldnameToGetProjectcodeFrom'];

					$s_sql = "SELECT {$connectedRecordTableName}.* FROM {$connectTablename} LEFT OUTER JOIN {$connectedRecordTableName} ON {$connectedRecordTableName}.id = {$connectTablename}.{$fieldInConnecttableForConnectedRecordId}
					WHERE {$connectTablename}.{$fieldInConnecttableForSubscriptionId} = ?";
					$o_query = $o_main->db->query($s_sql, array($v_row['id']));
					if($o_query && $o_query->num_rows()>0){
						$projectCodeFromId = "";
						$connectedRecords = $o_query->result_array();

						foreach($connectedRecords as $connectedRecord){
							if($connectedRecord[$connectedRecordConnectionFieldname] != "") {
								$projectCodeFromId = $connectedRecord[$connectedRecordConnectionFieldname];
								break;
							}
						}
						$s_sql = "SELECT * FROM {$tablenameToGetProjectcodeFrom} WHERE id = ?";
						$o_query = $o_main->db->query($s_sql, array($projectCodeFromId));
						$projectCodeItem = $o_query ? $o_query->row_array() : array();
					}
				}
				if($projectCodeItem){
					$projectCode = $projectCodeItem[$fieldnameToGetProjectcodeFrom];
				}
				$departmentCode = $v_row['departmentCode'];


				$s_sql = "select * from customer where id = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row["customerId"]));
				if($o_query && $o_query->num_rows()>0){
					$v_customer = $o_query->row_array();
				}

				$s_address = "";
				foreach($v_address_format as $s_key)
				{
					if($v_customer[$s_key] != "")
					{
						if($s_address != "") $s_address .= ", ";
						$s_address .= $v_customer[$s_key];
					}
				}
				$l_price = round($v_row['pricePerPiece'] * (1 + ($l_raise_percent / 100)), 2);
				$l_total = $l_price * $v_row['amount'];
				$l_total = round($l_total - ($l_total * ($v_row['discountPercent'] / 100)), 2);

				if($v_row['nextRenewalDate'] == '0000-00-00')
					$nextrenewaldatevalue = $v_row['startDate'];
				else
					$nextrenewaldatevalue = $v_row['nextRenewalDate'];
				$lastdate = $nextdate = $nextrenewaldatevalue;
				$nextdate2 = strtotime($nextdate);
				//
				$nextrenewaldatevalue = date('d.m.Y', strtotime($nextrenewaldatevalue));
				if(intval($v_row['periodUnit']) == 0){
					$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2)));
					$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2)+$v_row['periodNumberOfMonths'], date('j',$nextdate2),  date('y',$nextdate2))-24*60*60);
				} else {
					$nextrenewaldate = date('Y-m-d',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths']));
					$nextrenewaldate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('Y',$nextdate2)+$v_row['periodNumberOfMonths'])-24*60*60);
				}
				$lastdate2 = date('d.m.Y',mktime(0, 0, 0, date('m',$nextdate2), date('j',$nextdate2),  date('y',$nextdate2)));

				$s_sql = "SELECT * FROM ownercompany WHERE id = ?";
				$o_query = $o_main->db->query($s_sql, array($v_row['ownercompany_id']));
				$ownerCompanyData = $o_query ? $o_query->row_array() : array();


				$s_sql = "SELECT * FROM subscriptionmulti_date_for_invoicing WHERE subscriptionmulti_id = ".$v_row['id']." ORDER BY id ASC";
				$o_query = $o_main->db->query($s_sql);
				$datesForInvoicing = ($o_query ? $o_query->result_array() : array());
				$dateArray = array();
				foreach($datesForInvoicing as $dateForInvoicing) {
					$dateArray[] = date("d.m", strtotime($dateForInvoicing['date']));
				}
				$totalCollectNumber = count($dateArray);
				foreach($dateArray as $key => $dateToCompare) {
					if(date("d.m", strtotime($nextrenewaldatevalue)) == $dateToCompare){
						$collectNumber = $key+1;
					}
				}
				if($v_row['subscription_category'] == 1){
					$addYear = 0;
					$collectIndex = $collectNumber;
					if($collectIndex > count($dateArray)){
						$collectIndex = 0;
					}
					$nextrenewaldate2 = date('d.m.Y', strtotime($dateArray[$collectIndex].".".(date("Y", strtotime($nextrenewaldatevalue)))));
					if(strtotime($nextrenewaldate2) < strtotime($nextrenewaldate)) {
						$nextrenewaldate2 = date('d.m.Y', strtotime("+1 year", strtotime($nextrenewaldate2)));
					}
				}
				if($v_row['useMainContactAsContactperson']){
					$s_sql = "SELECT contactperson.* FROM contactperson
					WHERE contactperson.customerId = ? AND contactperson.mainContact = 1";
					$o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
					$contactPersonData = $o_query ? $o_query->row_array() : array();
				} else {
					$s_sql = "SELECT contactperson.* FROM contactperson_role_conn
					LEFT OUTER JOIN contactperson ON contactperson.id = contactperson_role_conn.contactperson_id
					WHERE contactperson_role_conn.subscriptionmulti_id = ? AND (contactperson_role_conn.role = 0 OR contactperson_role_conn.role is null OR contactperson_role_conn.role = 1)
					ORDER BY contactperson_role_conn.role DESC";
					$o_query = $o_main->db->query($s_sql, array($v_row['id']));
					$contactPersonData = $o_query ? $o_query->row_array() : array();
				}

				$errorTxt = "";
				$noError = true;
				$warningMessage = "";
				$warningMultipleMessage = "";

				$free_sl_rows = array();
				$totalTotal = 0;
				$sl_rows = get_sl_rows($v_row, $nextrenewaldatevalue, $nextrenewaldate2, $batch_renewal_accountconfig);

				if($v_row['activate_specified_invoicing']){

					$s_sql = "SELECT ww.date, ww.estimatedTimeuse, p.name, p.middlename, p.lastname FROM workplanlineworker ww
					LEFT OUTER JOIN contactperson p ON p.id = ww.employeeId
					WHERE ww.repeatingOrderId = ? AND (ww.specified_invoicing_id = 0 OR ww.specified_invoicing_id is null) AND ww.date >= ? AND ww.date <= ? AND (ww.absenceDueToIllness is null OR ww.absenceDueToIllness = 0) ORDER BY ww.date ASC";
					$o_query = $o_main->db->query($s_sql, array($v_row['id'], date("Y-m-d", strtotime($nextrenewaldatevalue)), date("Y-m-d", strtotime($nextrenewaldate2))));
					$workplanlines = $o_query ? $o_query->result_array() : array();
					foreach($workplanlines as $temp_line){
						array_push($free_sl_rows, $temp_line);
					}


				}

				if($v_row['subscription_category'] == 2){
					require(__DIR__."/../../CrmPriceOverview/output/renewal_include.php");
				}
				if($v_row['script_for_generating_order'] != ""){
					if(is_file(__DIR__ . "/../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php")) {
						require(__DIR__ . "/../../SubscriptionReportAdvanced/output/includes/scripts/".$v_row['script_for_generating_order']."/script.php");
					}
				}
				//get subsription extras
				$sql = "SELECT * FROM subscriptionmulti_invoicing_extra WHERE content_status < 2 AND subscriptionmulti_id = '".$o_main->db->escape_str($v_row['id'])."' AND (collectingorderId = 0 OR collectingorderId is null) AND month <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($nextrenewaldate2)))."'";
			 	$o_query = $o_main->db->query($sql);
				$invoicing_extras = $o_query ? $o_query->result_array() : array();
				foreach($invoicing_extras as $invoicing_extra) {
					$invoicing_extra['extra'] = 1;
					$sl_rows[] = $invoicing_extra;
				}
				foreach($sl_rows as $line){

		            $s_sql = "SELECT * FROM article WHERE id = ?";
		            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
		            $article = $o_query ? $o_query->row_array() : array();

		            $s_sql = "SELECT * FROM customer WHERE id = ?";
		            $o_query = $o_main->db->query($s_sql, array($v_row['customerId']));
		            $customer = $o_query ? $o_query->row_array() : array();

					$pricePerPiece = $line['pricePerPiece'];
					if($line['articleOrIndividualPrice']){
						$pricePerPiece = $article['price'];
						if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
							$line['articleName'] = $article['name'];
						}
					}
					if($v_row['subscription_category'] == 1){
						$totalAmount = 1 * $line['amount'];
					} else if($v_row['override_periods'] > 0) {
						$totalAmount = $v_row['override_periods'] * $line['amount'];
					} else {
						$totalAmount = $v_row['periodNumberOfMonths'] * $line['amount'];
					}

					if($line['fromPriceList']){
						if(intval($v_row['periodUnit']) == 0){
							$pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
							$totalAmount = 1;
						}
					}
					$renewalMonth = date("n", strtotime($nextrenewaldatevalue));
					$adjustmentPercent = 100;
					switch($renewalMonth){
						case 1:
							$adjustmentPercent = 100-$v_row['january'];
						break;
						case 2:
							$adjustmentPercent = 100-$v_row['february'];
						break;
						case 3:
							$adjustmentPercent = 100-$v_row['march'];
						break;
						case 4:
							$adjustmentPercent = 100-$v_row['april'];
						break;
						case 5:
							$adjustmentPercent = 100-$v_row['may'];
						break;
						case 6:
							$adjustmentPercent = 100-$v_row['june'];
						break;
						case 7:
							$adjustmentPercent = 100-$v_row['july'];
						break;
						case 8:
							$adjustmentPercent = 100-$v_row['august'];
						break;
						case 9:
							$adjustmentPercent = 100-$v_row['september'];
						break;
						case 10:
							$adjustmentPercent = 100-$v_row['october'];
						break;
						case 11:
							$adjustmentPercent = 100-$v_row['november'];
						break;
						case 12:
							$adjustmentPercent = 100-$v_row['december'];
						break;
					}
					if($adjustmentPercent != 100) {
						$pricePerPiece = $pricePerPiece*($adjustmentPercent)/100;
						$warningMessage .= "<li>".$formText_SubscriptionRenewalHasAdjustedPrice_output . " ".number_format($adjustmentPercent, 2, ",", "")." %</li>";
					}

					$totalRowPrice = $totalAmount * $pricePerPiece * ((100-$line['discountPercent'])/100);



					$vatCode = $article['VatCodeWithVat'];
					$bookaccountNr = $article['SalesAccountWithVat'];

					if($vatCode == ""){
						$vatCode = $article_accountconfig['defaultVatCodeForArticle'];
					}
					if($bookaccountNr == ""){
						$bookaccountNr = $article_accountconfig['defaultSalesAccountWithVat'];
					}
		            $vatPercent = '';

		            $vatCodeError = false;
		            $bookAccountError = false;
		            $articleError = false;
		            $projectError = false;
					$departmentError = false;

		            $s_sql = "SELECT * FROM vatcode WHERE vatCode = ?";
		            $o_query = $o_main->db->query($s_sql, array($vatCode));
		            $vatcodeItem = $o_query ? $o_query->row_array() : array();
		            if(!$vatcodeItem){
		                $noError = false;
		                $vatCodeError = true;
		            }

		            $s_sql = "SELECT * FROM bookaccount WHERE accountNr = ?";
		            $o_query = $o_main->db->query($s_sql, array($bookaccountNr));
		            $bookaccountItem = $o_query ? $o_query->row_array() : array();
		            if(!$bookaccountItem){
		                $noError = false;
		                $bookAccountError = true;
		            }

		            $s_sql = "SELECT * FROM article WHERE id = ?";
		            $o_query = $o_main->db->query($s_sql, array($line['articleNumber']));
		            $bookaccountItem = $o_query ? $o_query->row_array() : array();
		            if(!$bookaccountItem){
		                $noError = false;
		                $articleError = true;
		            }

		            if($batch_renewal_basisconfig['activateCheckForProjectNr']) {
		                $s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = ?";
		                $o_query = $o_main->db->query($s_sql, array($projectCode));
		                $bookaccountItem = $o_query ? $o_query->row_array() : array();
		                if(!$bookaccountItem){
		                    $noError = false;
		                    $projectError = true;
		                }
		            }
					if($batch_renewal_basisconfig['activateCheckForDepartmentCode']) {
		                $s_sql = "SELECT * FROM departmentforaccounting WHERE departmentnumber = ?";
		                $o_query = $o_main->db->query($s_sql, array($departmentCode));
		                $departmentItem = $o_query ? $o_query->row_array() : array();
		                if(!$departmentItem){
		                    $noError = false;
		                    $departmentError = true;
		                }
		            }
		            if($vatCodeError || $bookAccountError || $articleError || $projectError || $departmentError){
		                if($vatCodeError){
		                    $errorTxt .= "<li>".$formText_VatCodeDoesntExist_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                }
		                if($bookAccountError){
		                    $errorTxt .= "<li>".$formText_BookAccountDoesntExist_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                }
		                if($articleError){
		                    $errorTxt .= "<li>".$formText_InvalidArticleNumber_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                }
		                if($projectError){
		                    $errorTxt .= "<li>".$formText_InvalidProjectFAccNumber_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                }
		                if($departmentError){
		                    $errorTxt .= "<li>".$formText_InvalidDepartmentFAccNumber_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." (".$nextrenewaldatevalue." - ".$nextrenewaldate2.") "."</li>";
		                }
					}

					if($pricePerPiece <= 0 || $totalRowPrice <= 0) {
						$warningMessage .= "<li>".$formText_SubscriptionLinePriceIsZero_output . " ".$formText_For_output." ".$v_row['subscriptionName']."  - ".$line['articleName']." "."</li>";
					}
				}
				if(count($sl_rows) == 0 && intval($v_row['activate_specified_invoicing']) == 0){
					$noError = false;
					$errorTxt .= "<li>".$formText_CannotRenewSubscriptionWithoutSubscriptionlines_output . "</li>";
				}
				if(intval($v_row['activate_specified_invoicing']) == 1 && count($sl_rows) == 0) {
					$warningMessage = "<li>".$formText_ForRenewalDate_output . " ". $nextrenewaldatevalue." ". $formText_ThereAreNoSpecifiedWorkToInvoice_output." ".$formText_TheRepeatingOrderWillBeUpdatedWithNextRenewalDate." ".date("d.m.Y", strtotime($nextrenewaldate))."</li>";
				}



				$modifier = 1;
				$modifierActivated = false;
				$decimalNumber = 2;
				$realStoppedDate = $v_row['stoppedDate'];
				$stoppedDate = strtotime("+". $v_row['periodNumberOfMonths']." months", strtotime($nextrenewaldatevalue));

				if($realStoppedDate != "0000-00-00" && $realStoppedDate != null) {
					if(strtotime($realStoppedDate) < $stoppedDate-24*60*60) {
						$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
						$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)));
						$laterTimestamp = $later->getTimestamp();
						$lastDayOfStoppedDate = date("t.m.Y", $laterTimestamp);
						$dayOfStoppedDate = date("d.m.Y", $laterTimestamp);

						$wholeMonths = 0;
						do {
							$earlier->add(new DateInterval("P1M"));
							$timestamp = $earlier->getTimestamp();
							$wholeMonths++;
						} while($timestamp < $laterTimestamp);

						$modifier = $wholeMonths;
						if($lastDayOfStoppedDate != $dayOfStoppedDate) {
							$stoppedDateMonthTotalDays = date("t", strtotime($realStoppedDate));
							$stoppedDateMonthFirstDay = date("01.m.Y", strtotime($realStoppedDate));

							if(strtotime($nextrenewaldatevalue) > strtotime($stoppedDateMonthFirstDay)){
								$earlier = new DateTime(date("Y-m-d", strtotime($nextrenewaldatevalue)));
							} else {
								$earlier = new DateTime(date("Y-m-d", strtotime($stoppedDateMonthFirstDay)));
							}
							$later = new DateTime(date("Y-m-d", strtotime($realStoppedDate)+24*60*60));
							$diff = $later->diff($earlier);
							$stoppedDateMonthDays = $diff->format("%a");

							$modifier = $modifier + number_format($stoppedDateMonthDays/$stoppedDateMonthTotalDays, 4, ".", "");
						}
						$modifierActivated = true;

						$nextrenewaldate = date('Y-m-d', strtotime($realStoppedDate));
						$nextrenewaldate2 = date('d.m.Y', strtotime($realStoppedDate));
						$decimalNumber = 4;
					}
				}
				$invoicing_type = "normal_invoicing";

				if($v_row['activate_own_tab_in_batchrenewal']){
					$invoicing_type = 'subscriptiontype_'.$v_row['subscriptiontype_id'];
				} else {
					if($v_row['activate_specified_invoicing']){
						$invoicing_type =  ' specified_invoicing ';
					} else{
						if($v_row['subscription_category'] == 1){
							$invoicing_type = ' collect_invoicing';
						}elseif($v_row['subscription_category'] == 2){
							$invoicing_type = ' pricelist_invoicing';
						} else {
							$invoicing_type = 'normal_invoicing';
						}
					}
				}
				if($v_row["nextRenewalDate"] == "0000-00-00")
				{
					$noError = false;
					$errorTxt .= "<li>".$formText_NextRenewalDateIsInvalid_Output . ": ".$v_row["nextRenewalDate"]." "."</li>";
				}
				if($v_row["startDate"] == "0000-00-00")
				{
					$errorTxt .= "<li>".$formText_startDateIsInvalid_Output . ": ".$v_row["startDate"]." "."</li>";
					$noError = false;
				}
				if(strtotime($nextrenewaldate2) < strtotime($maxNextRenewalDates[$invoicing_type])) {
					$warningMultipleMessage = "error";
					$warningCountMultiple[$invoicing_type]++;
				}

				?><div class="item-customer <?php echo $invoicing_type;?>
					<?php echo (!$noError ? " error":"");?>  <?php echo ($warningMessage != "" ? " warning":"");?> <?php echo ($warningMultipleMessage != "" ? " warningMultiple":"");?> ">
					<?php if(!$noError) {
						$errorCount[$invoicing_type]++;
						?>
						<div class="alert alert-danger">
							<ul class="errorList">
								<?php echo $errorTxt;?>
							</ul>
						</div>
					<?php } ?>
					<?php if($warningMessage != "") {
						$warningCount[$invoicing_type]++;
						?>
						<div class="alert alert-warning">
							<ul class="errorList">
								<?php echo $warningMessage;?>
							</ul>
						</div>
					<?php } ?>
					<div class="item-title">
						<?php
						if($v_row["invoice_to_other_customer_id"] > 0){
							echo "<span class='invoicing_to_other'>".$formText_InvoicingToDifferentCustomer_output."</span><br/>";
						}
						?>
						<?php if(!$ownerCompanyData) { ?>
						<div class="titleError"><?php echo $formText_NoOwnerCompany_Output;?></div>
						<?php } ?>
						<input type="hidden" value="<?php echo $v_row['id'];?>" name="subscribtion_id[]" />
						<input type="hidden" class="price" value="<?php echo $l_price;?>" name="price[]" />
						<div>
						<?php if($ownerCompanyData && $noError) { ?>
							<input type="checkbox" autocomplete="off" value="<?php echo $v_row['id'];?>" name="selection[]" <?php echo ($v_row["nextRenewalDate"] != "0000-00-00" && $warningMessage == "" && $invoicing_type =='normal_invoicing' ? "checked":"");?>  />
						<?php } ?>
						<?php echo $v_customer['name'];?>

		                <?php if ($activateMultiOwnerCompanies > 1 && $totalOwnerCompanies > 1): ?>
		                    <div>
		                        <small>
		                            (<?php echo $formText_OwnerCompany_output; ?>: <?php echo $ownerCompanyData['name']; ?>)
		                        </small>
		                    </div>
		                <?php endif; ?>

						</div>
						<div class="out-ref"><?php echo $formText_YourContact_output;?>: <?php echo $contactPersonData['name']." ".$contactPersonData['middlename']." ".$contactPersonData['lastname']; ?> </div>
						<?php if($projectCode != "") { ?>
						<div class="out-projectcode"><?php echo $formText_ProjectCode_output?>: <?php echo $projectCode; ?> </div>
						<?php } ?>
						<?php if($departmentCode != "") { ?>
						<div class="out-departmentcode"><?php echo $formText_DepartmentCode_output?>: <?php echo $departmentCode; ?> </div>
						<?php } ?>
						<div class="out-address"><?php echo $s_address;?></div>
						<br clear="all">
					</div>
					<?php
					if($modifierActivated){
						?>
						<div class="not_full_order"><?php echo $formText_NotFullOrder_output;?></div>
					<?php } ?>
					<div class="item-order">
						<?php if(!empty($s_order_reference)) { ?>
						<div><b><?php echo $formText_Reference;?></b>: <?php echo $s_order_reference;?></div>
						<?php } ?>
						<?php if(!empty($s_order_delivery_date)) { ?>
						<div><b><?php echo $formText_DeliveryDate;?></b>: <?php echo $s_order_delivery_date;?></div>
						<?php } ?>
						<?php if(!empty($s_order_delivery_address)) { ?>
						<div><b><?php echo $formText_DeliveryAddress;?></b>: <?php echo $s_order_delivery_address;?></div>
						<?php } ?>
						<table class="table table-condensed">
						<thead>
							<tr>
								<th><?php echo $formText_OrderlineText_Output;?></th>
								<th><?php echo $formText_Amount_Output;?></th>
								<th><span class="articleInfo">&nbsp;</span><?php echo $formText_PricePerPiece_Output;?></th>
								<th><?php echo $formText_Discount_Output;?></th>
								<th class="text-right"><?php echo $formText_TotalPrice_Output;?></th>
							</tr>
						</thead>
						<tbody>

							<?php

							foreach($sl_rows as $sl_row){
								if(!$modifierActivated){
									if($v_row['subscription_category'] == 1){
										$totalAmount = 1 * $sl_row['amount'];
									} else if($v_row['override_periods'] > 0) {
										$totalAmount = $v_row['override_periods'] * $sl_row['amount'];
									} else {
										$totalAmount = $v_row['periodNumberOfMonths'] * $sl_row['amount'];
									}
								} else {
									$totalAmount = $sl_row['amount']*$modifier;
								}

								$pricePerPiece = $sl_row['pricePerPiece'];
								if($sl_row['articleOrIndividualPrice']) {
						            $s_sql = "SELECT * FROM article WHERE id = ?";
						            $o_query = $o_main->db->query($s_sql, array($sl_row['articleNumber']));
						            $article = $o_query ? $o_query->row_array() : array();
									$pricePerPiece = $article['price'];
									if($v_customer_accountconfig['use_articlename_when_use_articleprice']) {
										$sl_row['articleName'] = $article['name'];
									}
								}
								if($sl_row['fromPriceList']){
									if(intval($v_row['periodUnit']) == 0){
										$pricePerPiece = round($pricePerPiece / 12 * $totalAmount, 2);
										$totalAmount = 1;
									}
								}

								$renewalMonth = date("n", strtotime($nextrenewaldatevalue));
								$adjustmentPercent = 100;
								switch($renewalMonth){
									case 1:
										$adjustmentPercent = 100-$v_row['january'];
									break;
									case 2:
										$adjustmentPercent = 100-$v_row['february'];
									break;
									case 3:
										$adjustmentPercent = 100-$v_row['march'];
									break;
									case 4:
										$adjustmentPercent = 100-$v_row['april'];
									break;
									case 5:
										$adjustmentPercent = 100-$v_row['may'];
									break;
									case 6:
										$adjustmentPercent = 100-$v_row['june'];
									break;
									case 7:
										$adjustmentPercent = 100-$v_row['july'];
									break;
									case 8:
										$adjustmentPercent = 100-$v_row['august'];
									break;
									case 9:
										$adjustmentPercent = 100-$v_row['september'];
									break;
									case 10:
										$adjustmentPercent = 100-$v_row['october'];
									break;
									case 11:
										$adjustmentPercent = 100-$v_row['november'];
									break;
									case 12:
										$adjustmentPercent = 100-$v_row['december'];
									break;
								}
								if($adjustmentPercent != 100) {
									$pricePerPiece = $pricePerPiece*($adjustmentPercent)/100;
								}
								$totalRowPrice = $totalAmount * $pricePerPiece * ((100-$sl_row['discountPercent'])/100);
								$totalTotal += $totalRowPrice;
								if($sl_row['specified_invoicing']) {
									if($sl_row['combined_specified']) {
										$date_string = " (".$sl_row['start_date']." - ".$sl_row['end_date'].")";
									} else {
										$date_string = " ".$sl_row['workDate'];
									}
								} else {
									if($v_row['subscription_category'] == 1){
										$collectString = "";
										if($totalCollectNumber > 1){
											$collectString = "(".$formText_PartInvoice_output." ".$collectNumber. " ".$formText_Of_output ." ".$totalCollectNumber.")";
										}
										$date_string = " ".$collectString;
									} else {
										if($sl_row['periodModifier'] > 0){
											if(intval($v_row['periodUnit']) == 0){
												$date_string = " (".$nextrenewaldatevalue." - ".date("t.m.Y", strtotime("+".$sl_row['periodModifier']." months", strtotime($nextrenewaldatevalue))).")";
											} else {
												$date_string = " (".$nextrenewaldatevalue." - ".date("t.m.Y", strtotime("+".((12*$sl_row['periodModifier'])-1)." months", strtotime($nextrenewaldatevalue))).")";
											}
										} else {
											$date_string = " (".$nextrenewaldatevalue." - ".$nextrenewaldate2.")";
										}
									}
								}
								$subscriptionNameString = "";
								if($v_row['placeSubscriptionNameInInvoiceLine']) {
									$subscriptionNameString = $v_row['subscriptionName'] . " - ";
								}
								if($sl_row['extra']){
									$date_string = " (".date("m.Y", strtotime($sl_row['month'])).")";
								}
								?>

								<tr>
									<td>
										<?php if($sl_row['extra']){ echo "<div style='font-size: 9px;'>".$formText_ExtraLine_output."</div>"; }?>
										<?php echo $subscriptionNameString.$sl_row['articleName'].$date_string ; ?>
									</td>
									<td><?php echo number_format($totalAmount, $decimalNumber, ",", " "); ?></td>
									<td><span class="articleInfo"><?php if($sl_row['articleOrIndividualPrice']) { ?><i class="fas fa-info-circle" title="<?php echo $formText_UsingArticlePrice_output;?>"></i><?php } ?></span><?php echo number_format($pricePerPiece, 2, ",", " "); ?></td>
									<td><?php echo number_format($sl_row['discountPercent'], 2, ",", " "); ?>%</td>
									<td class="item-total text-right"><?php echo number_format($totalRowPrice, 2, ",", " "); ?></td>
								</tr>
								<?php } ?>
							<tr>
								<td width="60%"></td>
								<td width="8%" class="item-price"><?php //echo $l_price; ?></td>
								<td width="12%"><?php //echo $v_row['amount']; ?></td>
								<td width="8%"><?php //echo $v_row['discountPercent']; ?></td>
								<td width="8%" class="item-total text-right last"><?php echo number_format($totalTotal, 2, ",", " "); ?></td>
							</tr>
							<?php
							if(count($free_sl_rows) > 0) {
								?>
								<tr>
									<td><b><?php echo $formText_WorkplanlinesMarkedAsFreeNoInvoicing_output;?></b></td>
									<td></td>
									<td></td>
									<td></td>
									<td class="item-total text-right"></td>
								</tr>
								<?php
								foreach($free_sl_rows as $sl_row) {
									$date_string = " ".date("d.m.Y", strtotime($sl_row['date']));
									$subscriptionNameString = $sl_row['name'] . " ".$sl_row['middlename']." ".$sl_row['lastname']." - ";
									?>
									<tr>
										<td><?php echo $subscriptionNameString.$date_string ; ?></td>
										<td><?php echo number_format($sl_row['estimatedTimeuse'], 2, ",", " "); ?></td>
										<td></td>
										<td></td>
										<td class="item-total text-right"></td>
									</tr>
								<?php }
							}?>
						</tbody>
						</table>
					</div>
				</div><?php
			}
		}
		?>
		<input type="hidden" name="date" value="<?php echo $dateMarker?>"/>
		<div class="out-buttons">
			<button id="out-perform-renewal" class="btn"><?php echo $formText_PerformRenewal_Output;?> (<span class="renewalCount"></span>)</button>
	        <span class="totalCost"><?php echo $formText_TotalCost_output;?>: <span class="totalCostNumber"></span></span>
		</div>
		</div>
	<?php } ?>
</div>
<style>
.item-customer {
	display: none;
}
.item-customer.normal_invoicing {
	display: block;
}
.item-customer.price_adjustment {
	display: block;
}
.item-customer .not_full_order {
	color: red;
	padding: 0px 20px;
}
.out-dynamic .invoicingHeadline {
	font-size: 16px;
}
.item-customer .articleInfo {
	width: 12px;
	display: inline-block;
	margin-right: 5px;
}
.item-customer .articleInfo i {
	color: #8b8b8b;
	cursor: help;
}
.ownercompaniesSelectWrapper {
	position: relative;
}
.ownercompaniesSelectWrapper label {
	cursor: pointer;
	color: #46b2e2;
}
.ownercompaniesSelectWrapper .filterWrapper.active {
	display: block;
}
.ownercompaniesSelectWrapper .filterWrapper {
	margin-top: 10px;
	background: #fff;
	padding: 10px 15px;
	display: none;
	position: absolute;
	top: 100%;
	right: 0;
	width: 200px;
	border: 1px solid #cecece;
}
.ownercompaniesSelectWrapper .filterWrapper input {
	display: inline-block;
	vertical-align: top;
	margin: 0;
	margin-top: 4px;
	margin-right: 5px;
}
.ownercompaniesSelectWrapper .filterWrapper span {
	display: inline-block;
	vertical-align: top;
	width: calc(100% - 20px);
}
.projectFilterWrapper,
.departmentFilterWrapper,
.selfdefinedFilterWrapper {
	text-align: right;
	margin: 5px 0px;
}
.item-customer.specified_invoicing {
	display: none;
}
.item-customer.pricelist_invoicing {
	display: none;
}
.item-customer.collect_invoicing {
	display: none;
}
.tabButtons {
	float: left;
	margin-left: 20px;
	font-size: 16px;
	cursor: pointer;
}
.tabButtons.active {
	text-decoration: underline;
}

.tabNormal {
	float: left;
	font-size: 16px;
	cursor: pointer;
	margin-left: 0;
}
.onholdOrders {
	float: right;
	color: #46b2e2;
	cursor: pointer;
}
.invoicing_to_other {
	color: #c11;
	margin-bottom: 10px;
}
.contactWarning {
	display: none;
	cursor: pointer;
}
.contactError {
	display: none;
	cursor: pointer;
}
.contactWarningMultiple {
	display: none;
	cursor: pointer;
}
.contactWarning.normal_invoicing {
	display: block;
}
.contactError.normal_invoicing {
	display: block;
}
.contactWarningMultiple.normal_invoicing {
	display: block;
}
.p_contentBlock .employeeSearch {
    float: right;
    position: relative;
    margin-bottom: 0;
}
.p_contentBlock .employeeSearch .employeeSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.p_contentBlock .employeeSearch .employeeSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_contentBlock .employeeSearch .employeeSearchSuggestions td {
    padding: 5px 10px;
}

.p_contentBlock .employeeSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.p_contentBlock .employeeSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.p_contentBlock .employeeSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_contentBlock .employeeSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_contentBlock .employeeSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.filteredCountRow .resetSelection {
	float: right;
	cursor: pointer;
}
.subscriptionSubtypeWrapper {
	display: none;
}
</style>
<script type="text/javascript">
$(".resetSelection").on('click', function(e) {
	e.preventDefault();
	fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&project_filter=".$_GET['project_filter']."&department_filter=".$_GET['department_filter']."&otherCustomerFilter=".$_GET['otherCustomerFilter'];?>", '', true)
});

var loadingCustomer = false;
var $input = $('.employeeSearchInput');
var customer_search_value;
$input.on('focusin', function () {
	searchCustomerSuggestions();
	$("#p_container").unbind("click").bind("click", function (ev) {
		if($(ev.target).parents(".employeeSearch").length == 0){
			$(".employeeSearchSuggestions").hide();
		}
	});
})
//on keyup, start the countdown
$input.on('keyup', function () {
	searchCustomerSuggestions();
});
//on keydown, clear the countdown
$input.on('keydown', function () {
	searchCustomerSuggestions();
});
function searchCustomerSuggestions (){
	if(!loadingCustomer) {
		if(customer_search_value != $(".employeeSearchInput").val()) {
			loadingCustomer = true;
			customer_search_value = $(".employeeSearchInput").val();

			$('.employeeSearch .employeeSearchSuggestions').html('<div class="article-loading lds-ring"><div></div><div></div><div></div><div></div></div>').show();

			var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value, ownercompany: '<?php echo $_GET['ownercompany'];?>', date: '<?php echo $_GET['date'];?>',
			department_filter: '<?php echo $_GET['department_filter'];?>', customerselfdefinedlist_filter: '<?php echo $_GET['customerselfdefinedlist_filter'];?>',
			project_filter: '<?php echo $_GET['project_filter'];?>', otherCustomerFilter: '<?php echo $_GET['otherCustomerFilter']?>'};

			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_customers_suggestions";?>',
				data: _data,
				success: function(obj){
					loadingCustomer = false;
					$('.employeeSearch .employeeSearchSuggestions').html('');
					$('.employeeSearch .employeeSearchSuggestions').html(obj.html).show();
					searchCustomerSuggestions();
				}
			}).fail(function(){
				loadingCustomer = false;
			})
		}
	}
}

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
		$(this).removeClass('opened');
        $(this).find("#popupeditboxcontent").html("");
	}
};
$(function() {
	$(document).click(function (e) {
		e.stopPropagation();
		var container = $(".ownercompaniesSelectWrapper");

		//check if the clicked area is dropDown or not
		if (container.has(e.target).length === 0) {
			$('.ownercompaniesSelectWrapper .filterWrapper').slideUp();
		}
	})
	$(".ownercompaniesSelectWrapper label").unbind("click").bind("click", function(){
		$(".ownercompaniesSelectWrapper .filterWrapper").slideToggle();
	})
	$(".onholdOrders").off("click").on("click", function(){
		if(!fw_click_instance)
		{
			fw_loading_start();
			fw_click_instance = true;
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=onholdOrders";?>',
				data: "fwajax=1&fw_nocss=1&ownercompany=<?php echo $_GET['ownercompany']?>&customerselfdefinedlist_filter=<?php echo $_GET['customerselfdefinedlist_filter']?>&department_filter=<?php echo $_GET['department_filter']?>&project_filter=<?php echo $_GET['project_filter']?>",
				success: function(obj){
					fw_loading_end();
					fw_click_instance = false;
		            $('#popupeditboxcontent').html('');
		            $('#popupeditboxcontent').html(obj.html);
		            out_popup = $('#popupeditbox').bPopup(out_popup_options);
		            $("#popupeditbox:not(.opened)").remove();
				}
			}).fail(function() {
				fw_loading_end();
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
			});
		}
	})
	$(".ownercompaniesSelect").on("change", function(){
		if($(this).val() == 0){
			window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&date=".$dateMarker."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter'];?>';
		} else {
			if($(this).is(":checked") === true){
				window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&date=".$dateMarker."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&ownercompany=".implode(",", $real_ownercompany_filter);?>,'+$(this).val();
			} else {
				var companyString = '<?php echo implode(",", $real_ownercompany_filter);?>';
				var companyArray = companyString.split(",");
				var index = companyArray.indexOf($(this).val());
				if (index > -1) {
				  companyArray.splice(index, 1);
				}
				window.location = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&date=".$dateMarker."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']."&ownercompany="?>'+companyArray.join(",");
			}
		}
	});
	$(".datepicker").datepicker({
		dateFormat: 'dd.mm.yy',
		onSelect: function(d, i) {
            if(d !== i.lastVal){
        		fw_load_ajax("<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter'];?>&date="+d, '', true);
            }
        }
	});
	<?php if(!$showRenewal){?>
		$("#selectDeselectAll").prop("checked", false);
	<?php } ?>
	$('#selectDeselectAll').on('change', function(event) {
		fw_loading_start();
		var totalToProcess = $('[name="selection[]"]').length;
		var processed = 0;
		var _this = $(this);
		setTimeout(function() {
			<?php
			if(!$showRenewal){
				?>
				if (_this.prop('checked')) {
					$('.item-customer:not(.warning) [name="adjustmentSelection[]"]').each(function (index,item) {
						if(!$(this).prop('checked')) $(this).trigger('click');
						processed++;
					});
				}
				else {
					$('.item-customer:not(.warning) [name="adjustmentSelection[]"]').each(function (index,item) {
						if($(this).prop('checked')) $(this).trigger('click');
						processed++;
					});
				}
			<?php } else { ?>
				if (_this.prop('checked')) {
					$('.item-customer:not(.warning) [name="selection[]"]').each(function (index,item) {
						if(!$(this).prop('checked')) $(this).trigger('click');
						processed++;
					});
				}
				else {
					$('.item-customer:not(.warning) [name="selection[]"]').each(function (index,item) {
						if($(this).prop('checked')) $(this).trigger('click');
						processed++;
					});
				}
			<?php } ?>
			fw_loading_end();
		}, 100);
	});
	$('[name="adjustmentSelection[]"]').change(function(){
		$(".adjustmentCount").html($('[name="adjustmentSelection[]"]:checked').length);
	})
	$('[name="adjustmentSelection[]"]').change();

	$("#out-default-raise").on("change", function(){
		$("#out-customer-list .item-customer input[type=text]").val($(this).val()).trigger("change");
	});
	$("#out-customer-list .item-customer input[type=text]").on("change", function(){
		var _parent = $(this).closest(".item-order");
		var _raise = parseInt($(this).val()),
		_price = parseInt($(this).data("price")),
		_amount = parseInt($(this).data("amount")),
		_discount = parseInt($(this).data("discount"));
		var _new_price = Math.round( _price * ( 1 + ( _raise / 100 )), 2);
		var _total = _new_price * _amount;
		var _total = Math.round(_total - (_total * (_discount / 100)), 2);

		$(this).closest(".item-customer").find("input.price").val(_new_price);
		$(this).closest(".item-order").find(".item-price").text(_new_price);
		$(this).closest(".item-order").find(".item-total").text(_total);
	});
	$("#out-perform-renewal").on("click", function(){
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=renewal";?>',
				data: "fwajax=1&fw_nocss=1&ownercompany=<?php echo $_GET['ownercompany']?>&customerselfdefinedlist_filter=<?php echo $_GET['customerselfdefinedlist_filter']?>&department_filter=<?php echo $_GET['department_filter']?>&project_filter=<?php echo $_GET['project_filter']?>&" + $("#out-customer-list input").serialize(),
				success: function(obj){
					fw_click_instance = false;
					$('#out-customer-list .out-dynamic').html(obj.html);
				}
			}).fail(function() {
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
			});
		}
	});
	$(".performAdjustment").on("click", function(){
		if(!fw_click_instance)
		{
			fw_click_instance = true;
			fw_loading_start();
			$.ajax({
				cache: false,
				type: 'POST',
				dataType: 'json',
				url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&ownercompany=".$_GET['ownercompany']."&date=".$_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter'];?>',
				data: "fwajax=1&fw_nocss=1&performAdjustment=1&" + $("#out-customer-list input").serialize(),
				success: function(obj){
					fw_loading_end();
					fw_click_instance = false;
					window.location.href = "<?=$_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folder=output&folderfile=output&ownercompany=".$_GET['ownercompany'];?>&date=<?php echo $_GET['date']."&customerselfdefinedlist_filter=".$_GET['customerselfdefinedlist_filter']?>&skipAdjustment=1";
				}
			}).fail(function() {
				fw_loading_end();
				fw_info_message_add("error", "<?php echo $formText_ErrorOccuredPleaseContactSupport_framework;?>", true, true);
				fw_click_instance = false;
			});
		}
	});
	$('[name="selection[]"]').change(function(){
		calculateTotal();
	})
    function calculateTotal(){
		$(".renewalCount").html($('[name="selection[]"]:checked').length);
        var totalCost = 0;
        $(".item-customer").each(function(){
        	if($(this).find(".item-total.last") && $(this).find('[name="selection[]"]').is(":checked")){
        		var value = parseFloat($(this).find(".item-total.last").html().replace(",", ".").replace(" ",""));
	            if(value > 0) {
	                totalCost += value;
	            }
        	}
        })
        $(".totalCostNumber").html(totalCost.toFixed(2));
    }
    calculateTotal();
	$(".tabButtons").off("click").on("click", function(){
		var subscriptionTypeId = $(this).data("subscriptiontype-id");
		if(subscriptionTypeId > 0){
			$(".item-customer").hide();
			$(".item-customer .item-title input:checkbox").prop("checked", false);

			$(".item-customer.subscriptiontype_"+subscriptionTypeId).show();
			$(".item-customer:not(.warning).subscriptiontype_"+subscriptionTypeId+" .item-title input:checkbox").prop("checked", true);

			calculateTotal();
			$(".contactError").hide();
			$(".contactWarning").hide();
			$(".contactWarningMultiple").hide();
			$(".subscriptionSubtypeWrapper").hide();

			$(".contactError.subscriptiontype_"+subscriptionTypeId).show();
			$(".contactWarning.subscriptiontype_"+subscriptionTypeId).show();
			$(".contactWarningMultiple.subscriptiontype_"+subscriptionTypeId).show();
			$(".subscriptionSubtypeWrapper"+subscriptionTypeId).show();

			$(".tabButtons").removeClass("active");
			$(this).addClass("active");
		}

	})
	$(".tabNormal").off("click").on("click", function(){
		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);

		$(".item-customer.normal_invoicing").show();
		$(".item-customer:not(.warning).normal_invoicing .item-title input:checkbox").prop("checked", true);

		calculateTotal();

		$(".contactError").hide();
		$(".contactWarning").hide();
		$(".contactWarningMultiple").hide();
		$(".subscriptionSubtypeWrapper").hide();

		$(".contactError.normal_invoicing").show();
		$(".contactWarning.normal_invoicing").show();
		$(".contactWarningMultiple.normal_invoicing").show();

		$(".tabButtons").removeClass("active");
		$(this).addClass("active");

	})
	$(".tabCollect").off("click").on("click", function(){
		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);

		$(".item-customer.collect_invoicing").show();
		$(".item-customer:not(.warning).collect_invoicing .item-title input:checkbox").prop("checked", true);

		calculateTotal();

		$(".contactError").hide();
		$(".contactWarning").hide();
		$(".contactWarningMultiple").hide();
		$(".subscriptionSubtypeWrapper").hide();

		$(".contactError.collect_invoicing").show();
		$(".contactWarning.collect_invoicing").show();
		$(".contactWarningMultiple.collect_invoicing").show();

		$(".tabButtons").removeClass("active");
		$(this).addClass("active");
	})
	$(".tabSpecified").off("click").on("click", function(){

		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);

		$(".item-customer.specified_invoicing").show();
		$(".item-customer:not(.warning).specified_invoicing .item-title input:checkbox").prop("checked", true);

		calculateTotal();

		$(".contactError").hide();
		$(".contactWarning").hide();
		$(".contactWarningMultiple").hide();
		$(".subscriptionSubtypeWrapper").hide();

		$(".contactError.specified_invoicing").show();
		$(".contactWarning.specified_invoicing").show();
		$(".contactWarningMultiple.specified_invoicing").show();

		$(".tabButtons").removeClass("active");
		$(this).addClass("active");
	})

	$(".tabPriceList").off("click").on("click", function(){

		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);

		$(".item-customer.pricelist_invoicing").show();
		$(".item-customer:not(.warning).pricelist_invoicing .item-title input:checkbox").prop("checked", true);

		calculateTotal();
		$(".contactError").hide();
		$(".contactWarning").hide();
		$(".contactWarningMultiple").hide();
		$(".subscriptionSubtypeWrapper").hide();

		$(".contactError.pricelist_invoicing").show();
		$(".contactWarning.pricelist_invoicing").show();
		$(".contactWarningMultiple.pricelist_invoicing").show();

		$(".tabButtons").removeClass("active");
		$(this).addClass("active");
	})

	<?php if(count($errorCount) > 0) {
		foreach($errorCount as $className=>$number){
		?>
		$(".out-dynamic").prepend('<div class="contactError <?php echo trim($className);?>" data-class-name="<?php echo trim($className);?>"> <span class="glyphicon glyphicon-alert"></span><?php echo $formText_ContractsWithErrors_output;?> (<?php echo $number;?>)</div>');
		<?php } ?>
	<?php } ?>
	<?php if(count($warningCount) > 0) {
		foreach($warningCount as $className=>$number){
		?>
		$(".out-dynamic").prepend('<div class="contactWarning <?php echo trim($className);?>" data-class-name="<?php echo trim($className);?>"><span class="glyphicon glyphicon-alert"></span><?php echo $formText_ContractsWithWarnings_output;?> (<?php echo $number;?>)</div>');
	<?php
		}
	} ?>
	<?php if(count($warningCountMultiple) > 0) {
		foreach($warningCountMultiple as $className=>$number){
		?>
		$(".out-dynamic").prepend('<div class="contactWarningMultiple <?php echo trim($className);?>" data-class-name="<?php echo trim($className);?>"><span class="glyphicon glyphicon-alert"></span><?php echo $formText_ContractsWithMultipleRenewalToBeUpToDate_output;?> (<?php echo $number;?>)</div>');
	<?php
		}
	} ?>

	$(".contactWarning").off("click").on("click", function(e){
		e.preventDefault();
		var typeClass = $(this).data("class-name");
		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);
		$(".item-customer.warning."+typeClass).show();
		calculateTotal();
	})
	$(".contactError").off("click").on("click", function(e){
		e.preventDefault();
		var typeClass = $(this).data("class-name");
		$(".item-customer").hide();
		$(".item-customer .item-title input:checkbox").prop("checked", false);
		$(".item-customer.error."+typeClass).show();
		calculateTotal();
	})
	$(".contactWarningMultiple").off("click").on("click", function(e){
		e.preventDefault();
		var typeClass = $(this).data("class-name");
		$(".item-customer").hide();
		$(".item-customer.warningMultiple."+typeClass).show();
		$(".item-customer:not(:visible)").prop("checked", false);
		calculateTotal();
	})
	<?php if($filtered_subtype) { ?>
		$('.tabButtons[data-subscriptiontype-id="<?php echo $filtered_subtype['subscriptiontype_id']?>"]').click();
	<?php } ?>
});
</script>
