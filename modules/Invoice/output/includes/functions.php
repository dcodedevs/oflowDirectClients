<?php
function get_ownercompanies($o_main) {
    $o_query = $o_main->db->get('ownercompany');

    $return_data = array('list' => array());

    if ($o_query && $o_query->num_rows()) {
        foreach ($o_query->result_array() as $row) {
            array_push($return_data['list'], $row);
        }
    }

    return $return_data;
}

function get_invoices($o_main, $filters) {
    // Process filter data
    if(isset($filters['list_filter'])){ $list_filter = $filters['list_filter']; } else{ $list_filter = 'active'; }
    //$list_filter = $filters['list_filter'] ? $filters['list_filter'] : 'active';
    if(isset($filters['company_filter'])){ $company_filter = $filters['company_filter']; } else{ $company_filter = 0; }
    //$company_filter = $filters['company_filter'] ? $filters['company_filter'] : 0;
    if(isset($filters['search_filter'])){ $search_filter = $filters['search_filter']; } else{ $search_filter = ''; }
    //$search_filter = $filters['search_filter'] ? $filters['search_filter'] : '';
    if(isset($filters['page'])){ $page = $filters['page']; } else{ $page = 1; }
    //$page = $filters['page'] ? $filters['page'] : 1;
    if(isset($filters['per_page'])){ $per_page = $filters['per_page']; } else{ $per_page = 100; }
    //$per_page = $filters['per_page'] ? $filters['per_page'] : 100;
	if(1==$_COOKIE['ehf_fail_check']) {
		$per_page = 10000;
	}
    // Offset
    $offset = ($page-1)*$per_page;
    if($page > 1){
        $offset -= ($per_page-10);
    }

    // If only 1 ownercompany
    $sql = "SELECT * FROM ownercompany";
    $result = $o_main->db->query($sql);
    if($result && $result->num_rows()>0)
    if($result->num_rows() == 1){
        $ownerCompany = $result->result();
        $company_filter = $ownerCompany[0]->id;
    }

    // Return data array
    $return_data = array(
        'list' => array()
    );

    $searchSql = "";
    if ($search_filter) {
        $searchSql = " AND (i.external_invoice_nr LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR i.id LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
        OR co.reference LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_city LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
        OR co.delivery_address_line_1 LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_line_2 LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
        OR co.delivery_address_postal_code LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR co.delivery_address_country LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
        OR cp.name LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cp.middlename LIKE '%".$o_main->db->escape_like_str($search_filter)."%' OR cp.lastname LIKE '%".$o_main->db->escape_like_str($search_filter)."%'
        )";
    }
    if(1==$_COOKIE['ehf_fail_check'])
	{
		$searchSql .= " AND i.created >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
	}
	$sqlNoLimit = "SELECT i.*, oc.name companyName, CONCAT_WS(' ',c.name, c.middlename, c.lastname) AS customerName, co.id AS collectingorder_id, co.accountingProjectCode, co.department_for_accounting_code
    FROM invoice i
    LEFT JOIN ownercompany oc ON oc.id = i.ownercompany_id
    LEFT JOIN customer c ON c.id = i.customerId
    LEFT OUTER JOIN customer_collectingorder co ON co.invoiceNumber = i.id
    LEFT OUTER JOIN contactperson cp ON cp.id = co.contactpersonId
    WHERE oc.id = $company_filter AND i.content_status < 2 ".$searchSql ." GROUP BY i.id ORDER BY i.external_invoice_nr = 0 DESC, i.external_invoice_nr DESC";

    $o_query =  $o_main->db->query($sqlNoLimit, array($company_filter));
    if($o_query && $o_query->num_rows()>0)

    $invoice_count = $o_query->num_rows();
    $totalPages = ceil($invoice_count/$per_page);

    $sql = $sqlNoLimit." LIMIT ".$per_page." OFFSET ".$offset;

    $result = $o_main->db->query($sql);
    if($result && $result->num_rows()>0)

    foreach($result->result() AS $row) {
		if(1==$_COOKIE['ehf_fail_check']) {
			if(0 == intval($row->for_sending) || 1 == $row->do_not_send_invoice) continue;
			$b_good = TRUE;
			$s_sql = "SELECT * FROM invoice_send_log WHERE invoice_id = '".$o_main->db->escape_str($row->id)."' ORDER BY id DESC";
			$o_log = $o_main->db->query($s_sql);
			if($o_log && $o_log->num_rows()>0)
			{
				$b_good = FALSE;
				foreach($o_log->result_array() as $v_log)
				{
					if(1 == $v_log['send_status']) $b_good = TRUE;
				}
				if($b_good) continue;
			}
		}
        $s_sql = "SELECT customer_collectingorder.*, CONCAT(cp.name, ' ', cp.middlename, ' ', cp.lastname) as contactPersonName FROM customer_collectingorder
        LEFT OUTER JOIN contactperson cp ON cp.id = customer_collectingorder.contactpersonId
        WHERE customer_collectingorder.invoiceNumber = ?  GROUP BY customer_collectingorder.id ORDER BY customer_collectingorder.id DESC";
        $o_query = $o_main->db->query($s_sql, array($row->id));
        $collecting_orders = ($o_query ? $o_query->result_array() : array());
        $row->collecting_orders = $collecting_orders;

        if ($list_filter == 'active') {
            if (!$row->content_status) {
                array_push($return_data['list'], $row);
            }
        }

        if ($list_filter == 'inactive') {
            if ($row->content_status == 2) {
                array_push($return_data['list'], $row);
            }
        }
    }

    $return_data['pagination'] = array(
        'total_pages' => $totalPages,
        'per_page' => $per_page,
        'page' => $page
    );

    $return_data['invoice_count'] = $invoice_count;

    return $return_data;
}

function showListHtml($invoices, $type, $activate_global_export = 0) {
    global $o_main;
	global $variables;
	global $formText_InvoiceNumber_output;
    global $formText_InvoiceDate_output;
    global $formText_CustomerName_output;
    global $formText_TotalWithTax_output;
    global $formText_Pdf_output;
    global $formText_SendInvoice_output;
    global $formText_OpenPdf_output;
    global $formText_Showing_output;
    global $formText_ShowMore_output;
    global $formText_Of_output;
    global $formText_NoResults_output;
    global $formText_InvoiceId_output;
    global $formText_Ehf_output;
    global $formText_Info_output;
    global $formText_EhfIsSent_Output;
    global $formText_SendEhfInvoice_output;
    global $module;
    global $formText_AttachedFiles_output;
    global $extradomaindirroot;
    global $v_country;
    global $formText_RecreatePdf_output;
    global $formText_AcountingProject_output;
    global $formText_AccountingDepartment_output;
	global $formText_SendingInfo_Output;
	global $formText_Paper_Output;
	global $formText_Email_Output;
	global $formText_Ehf_Output;
	global $formText_Success_Output;
	global $formText_Fail_Output;
	global $formText_InvoicingTime_Output;
	global $formText_InvoicedBy_Output;
	global $formText_Status_Output;
	global $formText_NoRecords_Output;
	global $formText_CreateEhf_output;
	global $formText_RecreateEhf_output;
	global $formText_GettingProcessed_output;
    global $formText_CreatedBy_output;
    global $formText_UpdatedBy_output;
    global $formText_Sending_output;
    ob_start();

	$v_log_types = array(
		1 => $formText_Paper_Output,
		2 => $formText_Email_Output,
		3 => $formText_Ehf_Output,
	);
	$v_log_status = array(
		1 => $formText_Success_Output,
		2 => $formText_Fail_Output,
	);

	$o_query = $o_main->db->query("SELECT * FROM invoice_accountconfig");
	$v_invoice_accountconfig = $o_query ? $o_query->row_array() : array();

    foreach($invoices['list'] as $v_row)
	{
		if(isset($v_row->files_attached) > 0){ $attachedFiles = json_decode($v_row->files_attached, true); } else { $attachedFiles = array(); }
        $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row->id;

		$s_sql = "SELECT * FROM invoice_send_log WHERE invoice_id = '".$o_main->db->escape_str($v_row->id)."' ORDER BY id DESC";
		$o_log = $o_main->db->query($s_sql);
		$b_is_sending_log = ($o_log && $o_log->num_rows() > 0);

		$s_sql = "SELECT * FROM customer WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($v_row->customerId));
		$v_customer = $o_query ? $o_query->row_array() : array();
		?>
        <div class="gtable_row" data-href="<?php echo $s_edit_link;?>">
            <div class="gtable_cell c1">
                <?php
                if($v_row->not_processed == 1 && $v_row->invoiceFile == "") {
                    echo $formText_GettingProcessed_output;
                } else {
                    echo $v_row->external_invoice_nr;
                }
                ?>
                <?php if(($v_row->created != "0000-00-00 00:00:00" && $v_row->created != null) || ($v_row->updated != "0000-00-00 00:00:00" && $v_row->updated != null)){?>
                    <br/>
                    <span class="glyphicon glyphicon-info-sign hoverEyeCreated">
                        <div class="hoverInfo">
                            <?php
                            $createdShown = false;
                            if($v_row->created != "0000-00-00 00:00:00" && $v_row->created != null){
                                echo $formText_CreatedBy_output?>: <?php echo $v_row->createdBy. " ".date("d.m.Y H:i:s", strtotime($v_row->created));
                                $createdShown = true;
                            }
                            ?>
                            <?php
                            if($v_row->updated != "0000-00-00 00:00:00" && $v_row->updated != null){
                                if($createdShown) {
                                    echo " | ";
                                }
                                echo $formText_UpdatedBy_output?>: <?php echo $v_row->updatedBy. " ".date("d.m.Y H:i:s", strtotime($v_row->updated));
                            }
                            ?>
                        </div>
                    </span>
                <?php } ?>
            </div>
            <?php if ($activate_global_export): ?>
                <div class="gtable_cell c1"><?php echo $v_row->id; ?></div>
            <?php endif; ?>
            <div class="gtable_cell c2"><?php echo $v_row->invoiceDate; ?></div>
            <div class="gtable_cell c3">
                <?php echo $v_row->customerName; ?>
                <?php
                if(count($attachedFiles) > 0) {
                    echo "<br/>".$formText_AttachedFiles_output;
                    ?>
                    <br/>
                    <?php
                    foreach($attachedFiles as $file){
                        $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=invoice&field=files_attached&ID='.$v_row->id;
                        ?>
                        <div style="padding: 0;"><a href="<?php echo $fileUrl?>" download><?php echo $file[0];?></a></div>
                        <?php
                    }
                }
                ?>
            </div>
            <div class="gtable_cell c8">
                <?php

                foreach($v_row->collecting_orders as $collecting_order) { ?>
                    <?php if(!empty($collecting_order['contactPersonName'])) { ?>
                    <div><?php echo $collecting_order['contactPersonName'];?></div>
                    <?php } ?>
                    <?php if(!empty($collecting_order['reference'])) { ?>
                    <div><?php echo $collecting_order['reference'];?></div>
                    <?php } ?>
                    <?php if(!empty($collecting_order['delivery_date']) && $collecting_order['delivery_date'] != '0000-00-00') { ?>
                    <div><?php echo date('d.m.Y', strtotime($collecting_order['delivery_date']));?></div>
                    <?php } ?>
                    <?php
                    $s_delivery_address = trim(preg_replace('/\s+/', ' ', $collecting_order['delivery_address_line_1'].' '.$collecting_order['delivery_address_line_2'].' '.$collecting_order['delivery_address_city'].' '.$collecting_order['delivery_address_postal_code'].' '.$v_country[$collecting_order['delivery_address_country']]));
                    if(!empty($s_delivery_address)) { ?>
                    <div><?php echo $s_delivery_address;?></div>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="gtable_cell c4"><?php echo number_format($v_row->totalInclTax, 2, ",", " "); ?></div>
            <div class="gtable_cell c5">
                <?php
                if($v_row->not_processed == 1 && $v_row->invoiceFile == ""){
                    echo $formText_GettingProcessed_output;
                } else {
                    ?>
                    <a href="#" class="sendInvoice" data-invoice-id="<?php echo $v_row->id;?>"><?php echo $formText_SendInvoice_output;?></a>
                    <?php
                }
                ?>
            </div>
            <div class="gtable_cell c6">
                <?php if($v_row->invoiceFile != "" && $v_row->invoiceFile != null) { ?>
                    <a target="_blank" href="../<?php echo $v_row->invoiceFile; ?>?caID=<?php echo $_GET['caID']?>&table=invoice&field=invoiceFile&ID=<?php echo $v_row->id; ?>&time=<?php echo time();?>"><?php echo $formText_OpenPdf_output;?></a>
                <?php } else { ?>
                    <?php if(intval($v_row->not_processed) == 0){ ?>
                        <a href="#" class="recreate-pdf" data-invoice-id="<?php echo $v_row->id;?>"><?php echo $formText_RecreatePdf_output;?></a>
                    <?php } ?>
                <?php } ?>
            </div>
			<div class="gtable_cell c7">
                <?php
				if(intval($v_row->not_processed) == 0)
				{
                    $invoicedByEhf= false;
                    if($b_is_sending_log) {
                        if($o_log && $o_log->num_rows()>0)
        				{
        					foreach($o_log->result_array() as $v_log)
        					{
                                if($v_log['send_type'] == 3) {
                                    $invoicedByEhf = true;
                                }
                            }
                        }
                    }
        			if(($v_row->for_print == 0 && 20 <= $variables->developeraccess && $invoicedByEhf) || '' != $v_row->ehf_invoice_file)
        			{
        				if(20 <= $variables->developeraccess && '' == $v_row->ehf_invoice_file)
        				{
        					?><a href="#" class="recreate-ehf" data-invoice-id="<?php echo $v_row->id;?>"><?php echo $formText_CreateEhf_output;?></a><?php
        				} else if(strpos($v_row->ehf_reference, '[REFERENCE]') === FALSE)
        				{
        					if(20 <= $variables->developeraccess)
        					{
        						?><div>
        							<a href="#" class="recreate-ehf" data-invoice-id="<?php echo $v_row->id;?>"><?php echo $formText_RecreateEhf_output;?></a>
        						</div><?php
        					}
        					?>
        					<div>
        						<a href="#" class="send-ehf" data-invoice-id="<?php echo $v_row->id;?>"><?php echo $formText_SendEhfInvoice_output;?></a>
        					</div><?php
        				} else {
        					echo $formText_EhfIsSent_Output;
        				}
        			}
                }
        		?>
            </div>
			<?php if(0 < intval($v_invoice_accountconfig['project_code_in_list'])) { ?>
            <div class="gtable_cell c6"><?php echo $v_row->accountingProjectCode;?><?php if('' != $v_row->collectingorder_id && 1 < $v_invoice_accountconfig['project_code_in_list']) { ?> <a href="#" class="script edit-project-code" data-invoice-id="<?php echo $v_row->id;?>"><span class="glyphicon glyphicon-pencil"></span></a><?php } ?></div>
			<?php } ?>
			<?php if(0 < intval($v_invoice_accountconfig['department_code_in_list'])) { ?>
            <div class="gtable_cell c7"><?php echo $v_row->department_for_accounting_code;?><?php if('' != $v_row->collectingorder_id && 1 < $v_invoice_accountconfig['department_code_in_list']) { ?> <a href="#" class="script edit-department-code" data-invoice-id="<?php echo $v_row->id;?>"><span class="glyphicon glyphicon-pencil"></span></a><?php } ?></div>
			<?php } ?>
			<div class="gtable_cell c7">

                <?php if($b_is_sending_log) { ?> <?php /*?><a href="#" class="script show-sending-log" data-invoice-id="<?php echo $v_row->id;?>"><span class="glyphicon glyphicon-info-sign"><?php */?>
                    <?php
    				ob_start();
					$b_invoice_fail = TRUE;
					if($o_log && $o_log->num_rows()>0)
    				{
    					foreach($o_log->result_array() as $v_log)
    					{
                            if($v_log['send_emails'] == ""){
                                $v_log['send_emails'] = $v_row->sentByEmail;
                            }
							if(1 == $v_log['send_status'])
							{
								$b_invoice_fail = FALSE;
							}
    						?>
    						<div class="row">
    							<div class="col-xs-3"><?php echo date("d.m.Y H:i", strtotime($v_log['created']));?></div>
    							<div class="col-xs-6"><?php echo $v_log_types[$v_log['send_type']].(2==$v_log['send_type']?': '.$v_log['send_emails']:'');?></div>
    							<div class="col-xs-3"><?php echo $v_log_status[$v_log['send_status']];?></div>
    						</div>
    						<?php
    					}
    				} else {
    					?><div class="row">
    						<div class="col-xs-12"><?php echo $formText_NoRecords_Output;?></div>
    					</div><?php
    				}
					$s_buffer = ob_get_clean();
    				?>
					<span class="glyphicon glyphicon-info-sign hoverEye<?php echo ($b_invoice_fail?' failed':'');?>">
						<div class="hoverInfo">
							<div class="container-fluid">
								<div class="row">
									<div class="col-xs-3"><strong><?php echo $formText_InvoicingTime_Output;?></strong></div>
									<div class="col-xs-6"><strong><?php echo $formText_InvoicedBy_Output;?></strong></div>
									<div class="col-xs-3"><strong><?php echo $formText_Status_Output;?></strong></div>
								</div>
								<?php echo $s_buffer;?>
    						</div>
						</div>
					</span>
			<?php /*?></span></a><?php */?><?php } else {
                if($v_row->for_sending && 1 != $v_row->do_not_send_invoice) {
                    ?>
                    <span class="glyphicon glyphicon-info-sign hoverEye"><div class="hoverInfo">
                        <?php
                        echo $formText_Sending_output;
                        ?>
        			</div></span>
                    <?php
                }
            } ?>
        </div>
        </div>
    <?php }

    $rowsHtml = ob_get_clean();

    ob_start();
    ?>
    <div class="gtable" id="gtable_search">
        <div class="gtable_row">
            <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_InvoiceNumber_output;?></div>
            <?php if ($activate_global_export): ?>
                <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_InvoiceId_output;?></div>
            <?php endif; ?>
            <div class="gtable_cell gtable_cell_head c2"><?php echo $formText_InvoiceDate_output;?></div>
            <div class="gtable_cell gtable_cell_head c3"><?php echo $formText_CustomerName_output;?></div>
            <div class="gtable_cell gtable_cell_head c8"><?php echo $formText_Info_output;?></div>
            <div class="gtable_cell gtable_cell_head c4"><?php echo $formText_TotalWithTax_output;?></div>
            <div class="gtable_cell gtable_cell_head c5">&nbsp;</div>
            <div class="gtable_cell gtable_cell_head c6"><?php echo $formText_Pdf_output;?></div>
            <div class="gtable_cell gtable_cell_head c7"><?php echo $formText_Ehf_output;?></div>
			<?php if(0 < intval($v_invoice_accountconfig['project_code_in_list'])) { ?>
            <div class="gtable_cell gtable_cell_head c6"><?php echo $formText_AcountingProject_output;?></div>
			<?php } ?>
			<?php if(0 < intval($v_invoice_accountconfig['department_code_in_list'])) { ?>
            <div class="gtable_cell gtable_cell_head c7"><?php echo $formText_AccountingDepartment_output;?></div>
			<?php } ?>
            <div class="gtable_cell gtable_cell_head c7"><?php echo $formText_SendingInfo_Output;?></div>
        </div>
        <?php echo $rowsHtml; ?>
    </div>

    <div class="ownercompany_invoices_pagination">
        <?php
        if($invoices['pagination']['total_pages'] > $invoices['pagination']['page']) {?>
        <div class="invoicePaginationRow">
            <?php echo $formText_Showing_output." <span class='current'>".$invoices['pagination']['per_page']*$invoices['pagination']['page']."</span> ".$formText_Of_output." ".$invoices['invoice_count'];?>
            <a href="#" class="invoiceShowNext"><?php echo $formText_ShowMore_output?></a>
        </div>
        <?php } ?>
    </div>

    <?php

    $fullHtml = ob_get_clean();

    if (!count($invoices['list'])) {
        echo '<div class="gtable_message">'.$formText_NoResults_output.'</div>';
    } else {
        if ($type == 'rows') echo $rowsHtml;
        else echo $fullHtml;
    }
    ?>
    <script type="text/javascript">
        $(function(){
            bindSend();
        })
    </script>
    <?php
}
