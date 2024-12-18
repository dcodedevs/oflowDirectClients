<?php

/**********************************/
include('languagesOutput/no.php');
/**********************************/

include_once(__DIR__."/output_functions.php");
if(!function_exists("include_local")) include(__DIR__."/../input/includes/fn_include_local.php");

$v_row = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
if($v_row && $v_row->num_rows() > 0) {
	$v_row = $v_row->row();
}
$s_default_output_language = $v_row->languageID;
$s_original_value = $choosenListInputLang;
$choosenListInputLang = $s_default_output_language;
include(__DIR__."/../input/includes/readInputLanguage.php");
$choosenListInputLang = $s_original_value;

$headmodule = "";
$submods = $v_module_main_tables = array();
if($findBase = opendir(__DIR__."/../input/settings/tables"))
{
	while($writeBase = readdir($findBase))
	{
		$fieldParts = explode(".",$writeBase);
		if($fieldParts[1] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
		{
			$submods[] = $fieldParts[0];
			$vars = include_local(__DIR__."/../input/settings/tables/".$fieldParts[0].".php", $v_language_variables);

			if($vars['tableordernr'] == "1")
			{
				$headmodule = $fieldParts[0];
				$v_module_main_tables[1] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
			else if((isset($vars['moduleMainTable']) && $vars['moduleMainTable'] == "1") && intval($vars['moduleTableAccesslevel'])<=$fw_session['developeraccess'])
			{
				$l_id = intval($vars['tableordernr']);
				if(array_key_exists($vars['tableordernr'], $v_module_main_tables)) $l_id += 20;
				$v_module_main_tables[$l_id] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
		}
	}
	if($headmodule == "")
	{
		$headmodule = $submods[0];
	}
	if(count($v_module_main_tables)==0)
	{
		$vars = include_local(__DIR__."/../input/settings/tables/".$submods[0].".php", $v_language_variables);
		$v_module_main_tables[1] = array($submods[0], $vars['preinputformName'], $vars['moduletype']);
	}
	if(is_file(__DIR__."/../input/settings/tables/".$headmodule.".php")) include(__DIR__."/../input/settings/tables/".$headmodule.".php");
	closedir($findBase);
}
$submodule = $headmodule;
$fields = array();
include(__DIR__."/../input/settings/fields/".$submodule."fields.php");
foreach($prefields as $s_field)
{
	$v_field = explode("¤",$s_field);
	$fields[$v_field[0]] = $v_field;
}

ob_start();
if(count($v_module_main_tables)>0)
{
	?><ul class="list-inline"><?php
	$v_keys = array_keys($v_module_main_tables);
	sort($v_keys);
	foreach($v_keys as $l_key)
	{
		?><li<?php print ($v_module_main_tables[$l_key][0]==$submodule?' class="active"':'');?>><a href="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$v_module_main_tables[$l_key][0].(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(!is_numeric($v_module_main_tables[$l_key][2])?"&folderfile=output&folder=".$v_module_main_tables[$l_key][2]:"");?>" class="optimize"><?php print ($v_module_main_tables[$l_key][1]!=""?$v_module_main_tables[$l_key][1]:$v_module_main_tables[$l_key][0]);?></a></li><?php
	}
	?></ul><?php
}
$fw_module_head = ob_get_clean();


$error_msg = json_decode($fw_session['error_msg'],true);
if(!isset($_GET['output_form_submit']) && sizeof($error_msg)>0)
{
	$print = "";
	foreach($error_msg as $key => $message)
	{
		list($class,$rest) = explode("_",$key);
		$print .= ' fw_info_message_add("'.$class.'", "'.$message.'"); ';
	}
	$print .= ' fw_info_message_show(); ';
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){'.$print.'});';
	} else {
		?><script type="text/javascript" language="javascript"><?php print '$(function(){'.$print.'});';?></script><?php
	}
	$fw_session['error_msg'] = array();

	$o_main->db->query("UPDATE session_framework SET error_msg = '' WHERE companyaccessID = ? AND session = ? AND username = ?", array($_GET['caID'], $variables->sessionID, $variables->loggID));
}
$o_query = $o_main->db->query("SELECT * FROM invoicereport_accountconfig");
$invoice_report_accountconfig = $o_query ? $o_query->row_array() : array();
ob_start();
include_once(__DIR__."/includes/readOutputLanguage.php");
/* ALLOWED INCLUDES */
$v_include = array(
	"ajax",
	"list"
);
$v_include_default = 'list';
if(isset($_GET['inc_obj']) && in_array($_GET['inc_obj'], $v_include)) $s_inc_obj = $_GET['inc_obj']; else $s_inc_obj = $v_include_default;

if($s_inc_obj != "ajax")
{

	$ownercompany_id = (isset($_GET['ownercompany_id'])?$_GET['ownercompany_id']:'0');
	$project_id = (isset($_GET['project_id'])?$_GET['project_id']:'0');
	$department_id = (isset($_GET['department_id'])?$_GET['department_id']:'0');
	
	$customer_id = $_GET['customer_id'] > 0 ? $_GET['customer_id'] : "";
	if($customer_id != ""){
		$o_query = $o_main->db->query("SELECT *, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName FROM customer WHERE id = '".$o_main->db->escape_str($customer_id)."'");
		$searchedCustomer = $o_query ? $o_query->row_array() : array();
	}

	/**********************************/

	?>
	<div class="p_headerLine"><?php
	if($moduleAccesslevel > 10)
	{
		?>
		<!-- <div class="goToTotalReport btnStyle">
			<div class="plusTextBox active">
				<div class="text"><?php echo $formText_TotalReportPage_Output; ?></div>
				<div class="clear"></div>
			</div>
		</div> -->
		<div class="clear"></div>
		<script type="text/javascript">
		$(function(){
			$(".resetSelection").off('click').on('click', function(e) {
				e.preventDefault();
				$('#employeeSearchCustomerId').val('');
				$('#finished').trigger('submit');
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
			
						var _data = { fwajax: 1, fw_nocss: 1, search: customer_search_value };
			
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
			$(function(){
				$(".goToTotalReport").on('click', function(e){
				    e.preventDefault();
				    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output_totalreport&inc_obj=list"; ?>', false, true);
				});
			})
		});
		</script>
		<?php
	}
	?></div>
	<div id="output-content-container">
		<div id="p_container" class="p_container <?php if(isset($folderName)){ echo $folderName; } ?>">
			<div class="p_containerInner">
				<div class="p_content ">
					<div class="p_contentBlock">
						<form id="finished" name="finished" method="post" action="" class="confirm">
						<br>
							<div class="errorMessage"></div>
							<div>
								<span class=""><input id="show_orders" class="changeShowType" type="radio" name="show_type" value="0" <?php if(!isset($_GET['show_type']) || $_GET['show_type'] == 0) { ?>checked<?php } ?> autocomplete="off"/> <label for="show_orders"><?php echo $formText_ShowOrders_output;?></label></span>&nbsp;&nbsp;&nbsp;&nbsp;
								<span class=""><input id="show_invoices" class="changeShowType" type="radio" name="show_type" value="1" <?php if($_GET['show_type'] == 1) { ?>checked<?php } ?> autocomplete="off"/> <label for="show_invoices"><?php echo $formText_ShowInvoices_output;?></label></span>
							</div>

							<input type="text" id="datefrom" name="datefrom" value="<?=$_GET['datefrom']?>" placeholder="<?=$formText_DateFrom_Output?>" required autocomplete="off">
							<input type="text" id="dateto" name="dateto" value="<?=$_GET['dateto']?>" placeholder="<?=$formText_DateTo_Output?>" required autocomplete="off">
							<div class="employeeSearch">
								<span class="glyphicon glyphicon-search"></span>
								<input type="hidden" id="employeeSearchCustomerId" name="customer_id" value="<?php echo $searchedCustomer['id'];?>"/>
								<input type="text" placeholder="<?php echo $formText_Customer_output;?>" value="<?php echo $searchedCustomer['customerName']?>" class="employeeSearchInput" autocomplete="off"/>
								<span class="glyphicon glyphicon-triangle-right"></span>
								<div class="employeeSearchSuggestions allowScroll"></div>
				
								<?php if(0 < $searchedCustomer['id']) { ?>
									<div class="filteredCountRow">
										<div class="resetSelection fw_text_link_color"><?php echo $formText_Reset_output;?></div>
									</div>
								<?php } ?>
							</div>
							<div class="extraChoices">
								<?php
								$findOwnerCompanies = $o_main->db->query("SELECT * FROM ownercompany");
								if($findOwnerCompanies) {
									$ownerCompanyCount = $findOwnerCompanies->num_rows();
								}
								if($ownerCompanyCount > 1){
								?>
								<label class="selectOwner"><?php echo $formText_SelectOwnerCompany_output;?></label>
								<select name="ownercompany_id" required>
									<option value=""><?php echo $formText_Select_Output;?></option>
									<?php
									$findOwnerCompanies = $o_main->db->query("SELECT * FROM ownercompany");
									if($findOwnerCompanies && $findOwnerCompanies->num_rows() > 0) {
										foreach($findOwnerCompanies->result() as $ownercompany) {
											?>
											<option value="<?php echo $ownercompany->id;?>" <?php if($ownercompany->id == $ownercompany_id) { echo 'selected';}?>><?php echo $ownercompany->name;?></option>
											<?php
										}
									}
									?>
								</select>
								<?php } else {
									if($findOwnerCompanies && $findOwnerCompanies->num_rows() > 0) {
										$ownerCompany = $findOwnerCompanies->row();
									}
									?>
									<input type="hidden" value="<?php echo $ownerCompany->id; ?>" name="ownercompany_id"/>
								<?php } ?>
								<br/>
								<div class="extraChoicesFlexible"  <?php if(!isset($_GET['show_type']) || $_GET['show_type'] == 0) { echo 'style="display: none;"'; }?>>
									<?php if($invoice_report_accountconfig['show_project_filter']) {?>
										<label class="selectProject"><?php echo $formText_SelectProject_output;?></label>
										<select name="project_id" style="width: 150px;">
											<option value=""><?php echo $formText_SelectProject_output; ?></option>
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
										<br/>
									<?php } ?>
									<?php if($invoice_report_accountconfig['show_department_filter']) {?>
										<label class="selectDepartment"><?php echo $formText_SelectDepartment_output;?></label>
										<select name="department_id" autocomplete="off" style="width: 150px;">
											<option value=""><?php echo $formText_SelectDepartment_output;?></option>
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
									<?php } ?>
								</div>
							</div>
							<input type="hidden" id="form_page" name="page" value="1"/>
							<div class="clear"></div>
							<input type="submit" value="<?=$formText_Filter_Output?>">
							<?php if(isset($_GET['datefrom']) && isset($_GET['dateto'])) { ?>
								<div class="exportResults"><?php echo $formText_ExportResults_output;?></div>
							<?php } ?>
						</form>
						<hr>

						<div class="invoices">
						<?php
						$projectSql = "";
						$departmentSql = "";

						if($_GET['show_type'] == 1){
							if($project_id > 0){
								$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE projectnumber = ?", array($project_id));
								$projectData = $o_query ? $o_query->row_array() : array();
								$projectSql = " AND (customer_collectingorder.accountingProjectCode = ".$o_main->db->escape($projectData['projectnumber']).")";
							}
							if($department_id > 0){
								$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE departmentnumber = ?", array($department_id));
								$departmentData = $o_query ? $o_query->row_array() : array();
								$departmentSql = " AND (customer_collectingorder.department_for_accounting_code = ".$o_main->db->escape($departmentData['departmentnumber']).")";
							}
						}
						if('' != $customer_id && 0 < $customer_id)
						{
							$projectSql .= " AND customer_collectingorder.customerId = '".$o_main->db->escape_str($customer_id)."'";
						}

						$datefrom = (isset($_GET['datefrom'])?$_GET['datefrom']:'0000');
						$dateto = (isset($_GET['dateto'])?date('Y-m-d',strtotime($_GET['dateto']. ' + 0 days')):'9999');
						$total = 0;
						$total_amount = 0;
						$totalInclTaxNumber = 0;
						$totalInDb = 0;
						//echo "SELECT sum( totalExTax ) FROM `invoice` WHERE invoiceDate >= '$datefrom' AND invoiceDate <= '$dateto';";
						if(isset($_GET['datefrom']) && isset($_GET['dateto'])) {
							// var_dump($projectSql);
						$totalExTax = $o_main->db->query(
							"SELECT invoice.totalExTax AS totalExTax
							FROM invoice
						 	LEFT JOIN customer_collectingorder ON customer_collectingorder.invoiceNumber = invoice.id
							LEFT JOIN orders ON customer_collectingorder.id = orders.collectingorderId
							WHERE invoice.invoiceDate >= ?
							AND invoice.invoiceDate <= ?
							AND invoice.ownercompany_id = ? ".$projectSql."
							GROUP BY invoice.id",
						  array($datefrom, $dateto, $ownercompany_id));

						if($totalExTax && $totalExTax->num_rows() > 0) {
							$totalExTaxes = $totalExTax->result();
							foreach ($totalExTaxes as $totalExTax) {
								$totalInDb += ($totalExTax->totalExTax) ? (float)$totalExTax->totalExTax : 0;
							}
						}
						?>
						<?php if($_GET['show_type'] == 0) { ?>
							<p align="right"><?php echo $formText_TotalDb_Output;?>: <?php echo number_format($totalInDb, 2, ",", " "); ?> </p>
						<?php } ?>
						<?php
						//print "SELECT sum( totalInclTax ) FROM `invoice` WHERE invoiceDate >= '$datefrom' AND invoiceDate <= '$dateto' ;"
						} ?>
						<?php

						$datefrom = (isset($_GET['datefrom'])?$_GET['datefrom']:'0000');
						$dateto = (isset($_GET['dateto'])?date('Y-m-d',strtotime($_GET['dateto']. ' + 0 days')):'9999');


						if($_GET['show_type'] == 0){
						?>
							<table width="100%">
							<tr valign="top">
								<td width="30"><?=$formText_ArticleNumber_Output?> </td>
								<td><?=$formText_ArticleName_Output?> </td>
								<td style="text-align: right" width="130"><?=$formText_Amount_Output?> </td>
								<td style="text-align: right" width="130"><?=$formText_WithoutTax_Output?> </td>
								<td style="text-align: right" width="130"><?=$formText_TotalPrice_Output?> </td>
								<td></td>
							</tr>
							<?php


							if(isset($_GET['datefrom']) && isset($_GET['dateto'])) {
							// $LISTSQL = "SELECT invoice.id, articleNumber, article.name, (totalExTax) as total
							// FROM orders
							// LEFT JOIN customer_collectingorder ON customer_collectingorder.id = orders.collectingorderId
							// LEFT JOIN invoice ON customer_collectingorder.invoiceNumber = invoice.id
							// LEFT JOIN article ON orders.articleNumber = article.id
							// WHERE invoiceDate >= ? AND invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
							// 	".$projectSql."
							// GROUP BY invoice.id
							// ORDER BY articleNumber;";

							//echo $LISTSQL.'<br>';
							$LISTSQL = "SELECT invoice.id, articleNumber, article.name, orders.priceTotal, orders.gross, sum(abs(amount)) as total_amount, sum(priceTotal ) as total,  count( articleNumber ) as c
							FROM invoice
							LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
							LEFT JOIN orders ON customer_collectingorder.id = orders.collectingorderId
							LEFT JOIN article ON orders.articleNumber = article.id
							WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
								".$projectSql.$departmentSql."
							GROUP BY orders.id
							ORDER BY c DESC, total DESC;";
							//echo $LISTSQL.'<br>';
							$findInvoices = $o_main->db->query($LISTSQL, array($datefrom, $dateto, $ownercompany_id));

							// var_dump($LISTSQL, $findInvoices->num_rows());
							if($findInvoices && $findInvoices->num_rows() > 0) {
								foreach ($findInvoices->result() as $invoice) {
									$invoices[$invoice->articleNumber]['name'] = $invoice->name;
									$invoices[$invoice->articleNumber]['total'] += $invoice->priceTotal;
									$invoices[$invoice->articleNumber]['total_amount'] += $invoice->total_amount;
									$invoices[$invoice->articleNumber]['c'] += $invoice->c;
									$invoices[$invoice->articleNumber]['totalExTax'] += $invoice->priceTotal;
									$invoices[$invoice->articleNumber]['totalInclTax'] += $invoice->gross;
								}
							}

							foreach ($invoices as $articleNumber=>$invoice) {
							$total += $invoice['total'];
							$total_amount += $invoice['total_amount'];
							$totalInclTaxNumber+= $invoice['totalInclTax'];
							?>
							<tr class="invoice" valign="top">
								<td><?=$articleNumber?></td>
								<td><?=$invoice['name']?> </td>
								<td style="text-align: right"><?=$invoice['total_amount']?> </td>
								<td style="text-align: right"><?=number_format($invoice['totalExTax'], 2, ",", " ")?> </td>
								<td style="text-align: right"><?=number_format($invoice['totalInclTax'], 2, ",", " ")?> </td>
								<td><span class="output-expander-link">(<?=($invoice['c'])?>) <span class="glyphicon glyphicon-chevron-right"></span></span></td>
							</tr>
							<tr class="invoiceArticles">
								<td colspan="6">
									<table class="table">
										<?php
										if($_GET['show_type'] == 0){
											?>
											<tr>
												<th><?php echo $formText_InvoiceDate_output;?></th>
												<th><?php echo $formText_Customer_output;?></th>
												<th><?php echo $formText_Article_output;?></th>
												<th><?php echo $formText_Amount_output;?></th>
												<th><?php echo $formText_PricePerPiece_output;?></th>
												<th><?php echo $formText_DiscountPercent_output;?></th>
												<th><?php echo $formText_priceTotal_output;?></th>
											</tr>
											<?php
											$invoicesSql =  "SELECT orders.*, invoice.invoiceDate, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName
												FROM invoice
												LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
												LEFT JOIN orders ON customer_collectingorder.id = orders.collectingorderId
												LEFT JOIN article ON orders.articleNumber = article.id
												LEFT JOIN customer ON customer_collectingorder.customerId = customer.id
												WHERE ".(('' != $customer_id && 0 < $customer_id) ? " customer_collectingorder.customerId = '".$o_main->db->escape_str($customer_id)."' AND":"")."
												invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
												AND orders.articleNumber = ?
												GROUP BY orders.id
												ORDER BY invoice.id ASC; ";
											$findInvoices = $o_main->db->query($invoicesSql, array($datefrom, $dateto, $ownercompany_id, $articleNumber));
											if($findInvoices && $findInvoices->num_rows() > 0) {
												$sum = 0;
												foreach ($findInvoices->result() as $invoiceSingle) {
													?>
													<tr>
														<td><?php echo date("d.m.Y", strtotime($invoiceSingle->invoiceDate));?></td>
														<td><?php echo $invoiceSingle->customerName;?></td>
														<td><?php echo $invoiceSingle->articleName;?></td>
														<td><?php echo $invoiceSingle->amount;?></td>
														<td><?php echo $invoiceSingle->pricePerPiece;?></td>
														<td><?php echo $invoiceSingle->discountPercent;?></td>
														<td><?php echo number_format($invoiceSingle->priceTotal, 2, ",", " ");?></td>
													</tr>
													<?php
													$sum+=$invoiceSingle->priceTotal;
												}
											}
										} else {
											?>
											<tr>
												<th><?php echo $formText_InvoiceNumber_output;?></th>
												<th><?php echo $formText_InvoiceDate_output;?></th>
												<th><?php echo $formText_Customer_output;?></th>
												<th><?php echo $formText_TotalWithoutTax_output;?></th>
											</tr>
											<?php
											$invoicesSql =  "SELECT invoice.*, invoice.invoiceDate, CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as customerName
												FROM invoice
												LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
												LEFT JOIN orders ON customer_collectingorder.id = orders.collectingorderId
												LEFT JOIN article ON orders.articleNumber = article.id
												LEFT JOIN customer ON customer_collectingorder.customerId = customer.id
												WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
												AND orders.articleNumber = ?
												GROUP BY invoice.id
												ORDER BY invoice.id ASC; ";
											$findInvoices = $o_main->db->query($invoicesSql, array($datefrom, $dateto, $ownercompany_id, $articleNumber));
											if($findInvoices && $findInvoices->num_rows() > 0) {
												foreach ($findInvoices->result() as $invoiceSingle) {
													?>
													<tr>
														<td><?php echo $invoiceSingle->external_invoice_nr;?></td>
														<td><?php echo date("d.m.Y", strtotime($invoiceSingle->invoiceDate));?></td>
														<td><?php echo $invoiceSingle->customerName;?></td>
														<td><?php echo number_format($invoiceSingle->totalExTax, 2, ",", " ");?></td>
													</tr>
													<?php
												}
											}
										}
										?>
									</table>
								</td>
							</tr>
							<?php }
							?>
							<tr valign="top">
								<td align="center"><?=$formText_Total_Output?></td>
								<td></td>
								<td style="text-align: right"><?=$total_amount?></td>
								<td style="text-align: right"><?=number_format($total, 2, ",", " ")?></td>
								<td style="text-align: right"><?=number_format($totalInclTaxNumber, 2, ",", " ")?></td>
								<td></td>
							</tr>
							<?php
							} else { ?>
							<tr class="invoice"  valign="top">
								<td colspan="6" align="center"><b><?=$formText_InputDates_Output?></b> </td>
							</tr>
							<?php } ?>
							</table>
						<?php } else {
							$page = isset($_GET['page']) ? $_GET['page'] : 1;
							$perPage = 500;
							$offset = ($page-1)*$perPage;
							$LISTSQL = "SELECT invoice.*, CONCAT_WS(' ', c.name, c.middlename, c.lastname) AS customerName
							FROM invoice LEFT JOIN customer_collectingorder ON  customer_collectingorder.invoiceNumber = invoice.id
							LEFT JOIN customer c ON c.id = customer_collectingorder.customerId
							WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ? AND customer_collectingorder.ownercompanyId = ?
								".$projectSql.$departmentSql."
							GROUP BY invoice.id
							ORDER BY invoice.external_invoice_nr ASC";
							//echo $LISTSQL.'<br>';
							$findInvoices = $o_main->db->query($LISTSQL, array($datefrom, $dateto, $ownercompany_id));
							$invoicesAll = $findInvoices ? $findInvoices->result_array() : array();
							$invoice_count = $findInvoices ? $findInvoices->num_rows() : 0;
							$total = 0;
							foreach ($invoicesAll as $invoice) {
								$total += $invoice['totalExTax'];
								$totalInclTax += $invoice['totalInclTax'];
							}

							$findInvoices = $o_main->db->query($LISTSQL." LIMIT ".$perPage." OFFSET ".$offset, array($datefrom, $dateto, $ownercompany_id));
							$invoices = $findInvoices ? $findInvoices->result_array() : array();
							$totalPages = round($invoice_count/$perPage);
							?>
							<table width="100%">
								<tr>
									<td colspan="3"><?php echo $formText_InvoicesCount_output." ".$invoice_count?></td>
									<td style="text-align: right"><?php echo $formText_total_output." ".number_format($total, 2, ",", " ")?></td>
									<td style="text-align: right"><?php echo $formText_total_output." ".number_format($totalInclTax, 2, ",", " ")?></td>
								</tr>
								<tr valign="top">
									<td width="30"><?=$formText_InvoiceNumber_Output?> </td>
									<td><?=$formText_InvoiceDate_Output?> </td>
									<td><?=$formText_CustomerName_Output?> </td>
									<td style="text-align: right" width="130"><?=$formText_WithoutTax_Output?> </td>
									<td style="text-align: right" width="130"><?=$formText_Amount_Output?> </td>
								</tr>
								<?php
									foreach ($invoices as $invoice) {
									?>
									<tr class="invoice noclick" valign="top">
										<td><?=$invoice['external_invoice_nr']?></td>
										<td><?=date("d.m.Y", strtotime($invoice['invoiceDate']))?> </td>
										<td><?=$invoice['customerName']?> </td>
										<td style="text-align: right"><?=number_format($invoice['totalExTax'], 2, ",", " ")?> </td>
										<td style="text-align: right"><?=number_format($invoice['totalInclTax'], 2, ",", " ")?> </td>
									</tr>
								<?php } ?>
							</table>
								<?php
							if($totalPages > 1) {
								?>
								<div class="getynet_pagination">
									<ul>
										<?php for($pageItem=1; $pageItem <= $totalPages; $pageItem++) { ?>
											<li data-page="<?php echo $pageItem?>" class="<?php if($pageItem == $_GET['page']) echo 'active'?>"><?php echo $pageItem;?></li>
										<?php } ?>
									</ul>
								</div>
								<style>
									.getynet_pagination ul {
										overflow: hidden;
										list-style: none;
										margin: 10px 0px;
										padding: 0;
									}
									.getynet_pagination ul li {
										float: left;
										padding: 3px 5px;
										margin-right: 3px;
										cursor: pointer;
										color: #46b2e2;
									}
									.getynet_pagination ul li:hover,
									.getynet_pagination ul li.active {
										color: #23527c
									}
								</style>
								<script type="text/javascript">
									$(function(){
										$(".getynet_pagination li").off("click").on("click", function(){
											var page = $(this).data("page");
											$("#form_page").val(page);
											$("#finished").submit();
										})
									})
								</script>
							<?php
								}
							?>
						<?php } ?>

						<?php if($_GET['show_type'] == 0) { ?>
							<?php
							if(round($totalInDb, 2) != round($total, 2)) {
								?>
							<div class="warningLabel"><?php echo $formText_ThereIsDeviationBetweenNumbers_output;?></div>
							<?php } ?>

						<?php } ?>
						</div>



						<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>

						<script type="text/javascript">

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
						$(function() {
							$(".exportResults").off("click").on("click", function(e){
						        e.preventDefault();
						        var data = {
									fwajax: 1,
						            fw_nocss: 1,
						            datefrom: '<?php echo $_GET['datefrom']?>',
						            dateto: '<?php echo $_GET['dateto']?>',
									show_type: '<?php echo $_GET['show_type']?>',
									ownercompany_id: '<?php echo $_GET['ownercompany_id']?>',
									project_id: '<?php echo $_GET['project_id']?>',
									department_id: '<?php echo $_GET['department_id']?>',
									customer_id: '<?php echo $_GET['customer_id']?>'

						        };
								submit_post_via_hidden_form('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=export_filtered"; ?>', data);

							})

							$('#datefrom, #dateto').datepicker({
								dateFormat: "yy-mm-dd",
								changeMonth: true,
								changeYear: true,
								yearRange: '-100:+20'
							});
							$(".changeShowType").change(function(){
								if($(this).val() == 0) {
									$(".extraChoicesFlexible").hide();
								} else if($(this).val() == 1) {
									$(".extraChoicesFlexible").show();
								}
							});
						});
						$("#finished").validate({
					        submitHandler: function(form) {
								fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&"; ?>'+$(form).serialize(), false, true);
					        },
					        invalidHandler: function(event, validator) {
					            var errors = validator.numberOfInvalids();
					            if (errors) {
					                var message = errors == 1
					                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
					                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

					                $(".errorMessage").html(message);
					                $(".errorMessage").show();
					            } else {
					                $(".errorMessage").hide();
					            }
					        }
					    });
					    $(".invoice").unbind("click").bind("click", function(){
					    	var $details = $(this).next(".invoiceArticles");
							var $expander = $(this).find('.output-expander-link > span');
							$details.toggleClass('expanded').slideToggle()
							$expander.toggleClass('glyphicon-chevron-down');
							$expander.toggleClass('glyphicon-chevron-right');
					    })
						</script>
						<style>
						.exportResults {
							display: inline-block;
							cursor: pointer;
							color: #57b4e4;
						}
						.inline {
							margin-left: 10px;
							display: inline-block;
							vertical-align: middle;
							margin-right: 10px;
						}
						.inline select {
							margin-bottom: 5px;
						}
						.inline label {
							display: inline-block !important;
							width: 150px;
						}
						select.error {
							border: 1px solid red;
						}
						input.error {
							border: 1px solid red;
						}
						label.error {
							display: none !important;
						}

						.selectOwner {
							margin-left: 0px;
						}
						.p_contentBlock {
							position: relative;
						}
						.errorMessage {
							color: red;
							margin-bottom: 10px;
						}
						.invoices {
							position: relative;
						}
						.warningLabel {
							position: absolute;
							top: 0px;
							color: red;
						}
						.invoices tr.invoice {
							cursor: pointer;
						}
						.invoices tr.invoice.noclick {
							cursor: default;
						}
						.invoices tr.invoice:nth-child(even) {
						    background-color: #EEE;
						}
						.invoiceArticles {
							display: none;
						}
						.invoiceArticles tr th,
						.invoiceArticles tr td {
							border-bottom: 1px solid #cecece;
						}
						.extraChoices {
							float: right;
							display: inline-block;
							text-align: right;
						}
						.employeeSearch {
							float: right;
							position: relative;
							margin-bottom: 0;
						}
						.employeeSearch .employeeSearchSuggestions {
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
						.employeeSearch .employeeSearchSuggestions table {
							margin-bottom: 0;
						}
						.employeeSearch .employeeSearchSuggestions td {
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
						.employeeSearchInput {
							width: 250px;
							border: 1px solid #dedede;
							padding: 3px 15px 3px 25px;
						}
						.employeeSearchInputBefore {
							width: 150px;
							border: 1px solid #dedede;
							padding: 3px 10px 3px 10px;
						}
						.employeeSearchBtn {
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
						</style>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
} else {
	$s_inc_act = "";
	if(is_string($_GET['inc_act'])) $s_inc_act = $_GET['inc_act'];
	if(is_file(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php")) include(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php");
}

if(!isset($_GET['output_form_submit'])) print ob_get_clean();
?>
