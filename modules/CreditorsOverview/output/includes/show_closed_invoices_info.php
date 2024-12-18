<?php

$cid = isset($_GET['cid']) ? $_GET['cid'] : 0;

$sql = "SELECT * FROM creditor WHERE id = ?";
$o_query = $o_main->db->query($sql, array($cid));
$creditor = $o_query ? $o_query->row_array() : array();
if($creditor){

	$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=creditor_list&cid=".$cid;
	?>
	<div id="p_container" class="p_container <?php echo $folderName; ?>">
		<div class="p_containerInner">
			<div class="p_content">
				<div class="p_pageContent">
					<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px; float: left;"><?php echo $formText_BackToCreditor_outpup;?></a>
					<div class="clear"></div>
				</div>
			</div>
			<?php

				if(!class_exists("Integration24SevenOffice")){
					require_once __DIR__ . '/../../../'.$creditor['integration_module'].'/internal_api/load.php';
				}
				$v_config = array(
					'ownercompany_id' => 1,
					'identityId' => $creditor['entity_id'],
					'creditorId' => $creditor['id'],
					'o_main' => $o_main
				);
				$s_sql = "SELECT * FROM integration24sevenoffice_session WHERE username = '".$o_main->db->escape_str($username)."' AND creditor_id = '".$o_main->db->escape_str($creditor['id'])."'";
				$o_query = $o_main->db->query($s_sql);
				if($o_query && 0 < $o_query->num_rows())
				{
					$v_int_session = $o_query->row_array();
					$v_config['session_id'] = $v_int_session['session_id'];
				}
				try {
					$api = new Integration24SevenOffice($v_config);
					if($api->error == "") {
						$per_page = 50;
						$page = isset($_GET['page']) ? $_GET['page']:1;
						$offset = ($page - 1) * $per_page;
						$sql_limit = " LIMIT ".$per_page." OFFSET ".$offset;

						$sql = "SELECT ct.* FROM creditor_transactions  ct
						WHERE ct.creditor_id = '".$o_main->db->escape_str($creditor['id'])."' AND ct.system_type = 'InvoiceCustomer' AND (ct.collectingcase_id > 0 OR ct.collecting_company_case_id > 0)";
						$o_query = $o_main->db->query($sql);
						$closed_invoices_count = $o_query ? $o_query->num_rows() : 0;
						$pages = round($closed_invoices_count/$per_page);

						$o_query = $o_main->db->query($sql.$sql_limit);
						$closed_invoices = $o_query ? $o_query->result_array() : array();
						foreach($closed_invoices as $closed_invoice) {
							$transaction_nrs = array();
							if($closed_invoice['transaction_nr'] > 0){
								$transaction_nrs[]=$closed_invoice['transaction_nr'];
							}
							$transactionData = array();
							$transactionData['DateSearchParameters'] = 'EntryDate';
							$transactionData['date_start'] = date("Y-m-d", strtotime($closed_invoice['date']));
							$transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
							$transactionData['InvoiceNo'] = $closed_invoice['invoice_nr'];
			                $invoicesTransactions = $api->get_transactions($transactionData);

							?>
							<div class="invoice_block">
								<div class="invoice_case_block">
									<b><?php echo $formText_CaseInfo_output?>:</b><br/>
									<?php echo $formText_CaseId_output." ". $closed_invoice['collectingcase_id'];?><br/>
									<?php echo $formText_InvoiceNr_output." ". $closed_invoice['invoice_nr'];?><br/>
									<!-- <?php echo $formText_StoppedDate_output." ". date("d.m.Y", strtotime($closed_invoice['stopped_date']));?><br/> -->
								</div>

								<div class="connected_transactions">
									<table class="table">
										<tr>
											<th class="gtable_cell"><?php echo $formText_Type_output;?></th>
											<th class="gtable_cell"><?php echo $formText_Date_output;?><br/><?php echo $formText_DueDate_output;?></th>
											<th class="gtable_cell"><?php echo $formText_InvoiceNr_output;?></th>
											<th class="gtable_cell"><?php echo $formText_KidNumber_output;?></th>
											<th class="gtable_cell"><?php echo $formText_LinkId_output;?></th>
											<th class="gtable_cell"><?php echo $formText_DateChanged_output;?></th>
											<th class="gtable_cell"><?php echo $formText_Amount_output;?></th>
											<th class="gtable_cell"><?php echo $formText_Bookaccount_output;?></th>
											<th class="gtable_cell"><?php echo $formText_Status_output;?></th>
										</tr>
										<?php
										foreach($invoicesTransactions as $invoicesTransaction) {
											?>
											<tr>
												<td class="gtable_cell"><?php echo $invoicesTransaction['systemType'];?></td>
												<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['date']));?><br/><?php if($invoicesTransaction['dueDate']!="" && $invoicesTransaction['dueDate'] != "0000-00-00") echo date("d.m.Y", strtotime($invoicesTransaction['dueDate']));?></td>
												<td class="gtable_cell"><?php echo $invoicesTransaction['invoiceNr'];?></td>
												<td class="gtable_cell"><?php echo $invoicesTransaction['kidNumber'];?></td>
												<td class="gtable_cell"><?php echo $invoicesTransaction['linkId'];?></td>
												<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['dateChanged']));?></td>
												<td class="gtable_cell"><?php echo $invoicesTransaction['amount'];?></td>
												<td class="gtable_cell"><?php echo $invoicesTransaction['accountNr'];?></td>
												<td class="gtable_cell"><?php
												if($invoicesTransaction['open']) {
													echo $formText_Open_output;
												} else {
													echo $formText_Closed_output;
												}
												?></td>
											</tr>
											<?php
											if($invoicesTransaction['transactionNr'] > 0){
												if(!in_array($invoicesTransaction['transactionNr'], $transaction_nrs)) {
													$transaction_nrs[] = $invoicesTransaction['transactionNr'];
												}
											}
										}
										?>
										<tr class=""><td style="border-bottom: 1px solid #cecece;" colspan="9"><b><?php echo $formText_TransactionsFromTransactionNr_output;?></b></td></tr>
										<?php
										foreach($transaction_nrs as $transaction_nr){
											$transactionData = array();
											var_dump($closed_invoice['date']);
											$transactionData['DateSearchParameters'] = 'EntryDate';
											$transactionData['date_start'] = date("Y-m-d", strtotime($closed_invoice['date']));
											$transactionData['date_end'] =date('Y-m-t', time() + 60*60*24);
											$transactionData['TransactionNoStart'] = $transaction_nr;
											$transactionData['TransactionNoEnd'] = $transaction_nr;
											$sub_transactions = $api->get_transactions($transactionData);
											foreach($sub_transactions as $invoicesTransaction) {
												?>
												<tr>
													<td class="gtable_cell"><?php echo $invoicesTransaction['systemType'];?></td>
													<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['date']));?><br/><?php if($invoicesTransaction['dueDate']!="" && $invoicesTransaction['dueDate'] != "0000-00-00") echo date("d.m.Y", strtotime($invoicesTransaction['dueDate']));?></td>
													<td class="gtable_cell"><?php echo $invoicesTransaction['invoiceNr'];?></td>
													<td class="gtable_cell"><?php echo $invoicesTransaction['kidNumber'];?></td>
													<td class="gtable_cell"><?php echo $invoicesTransaction['linkId'];?></td>
													<td class="gtable_cell"><?php echo date("d.m.Y", strtotime($invoicesTransaction['dateChanged']));?></td>
													<td class="gtable_cell"><?php echo $invoicesTransaction['amount'];?></td>
													<td class="gtable_cell"><?php echo $invoicesTransaction['accountNr'];?></td>
													<td class="gtable_cell"><?php
													if($invoicesTransaction['open']) {
														echo $formText_Open_output;
													} else {
														echo $formText_Closed_output;
													}
													?></td>
												</tr>
												<?php
											}
										}
										?>
									</table>
								</div>
							</div>
							<?php
						}
						for($x = 1; $x<=$pages; $x++) {
							?>
							<a href="#" class="page_link <?php if($x == $_GET['page']) echo 'active';?>" data-page="<?php echo $x;?>"><?php echo $x;?></a>
							<?php
						}
					}
				} catch(Exception $e) {
					echo $formText_FailedToConnect_output."<br/>";
					$connection_error = true;
					$failedMsg = "Critical error with exception. ".$e->getMessage();
				}
			?>
			<script type="text/javascript">
			$(function(){
				$(".page_link").off("click").on("click", function(){
					var data = {
						cid: "<?php echo $_GET['cid'];?>",
						page: $(this).data("page")
					}
					loadView("show_closed_invoices_info", data);
				})
			})
			</script>
		</div>
	</div>
	<style>
	.invoice_block {
		margin-bottom: 10px;
		background: #fff;
	}
	.page_link {
		cursor: pointer;
		margin-right: 5px;
	}
	.page_link.active {
		text-decoration: underline;
		font-weight: bold;
	}
	.invoice_case_block {
		padding: 5px;
	}
	</style>
	<?php
}
?>
