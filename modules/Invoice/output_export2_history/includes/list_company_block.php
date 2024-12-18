<?php

if ($invoice_accountconfig['activate_global_export']) {
    $sql = "SELECT h.*, oc.companyname ownerCompanyName,
    oc.export2ScriptFolder export2ScriptFolder
    FROM invoice_export2_history h
    LEFT JOIN ownercompany oc ON oc.id = h.ownerCompanyId
    ORDER by h.created DESC";
} else {
    $sql = "SELECT h.*, oc.companyname ownerCompanyName,
    oc.export2ScriptFolder export2ScriptFolder
    FROM invoice_export2_history h
    LEFT JOIN ownercompany oc ON oc.id = h.ownerCompanyId
    WHERE h.ownerCompanyId = $currentOwnerCompanyId
    ORDER by h.created DESC";
}

$export_list = array();

$o_query = $o_main->db->query($sql);
if ($o_query && $o_query->num_rows()) {
    foreach ($o_query->result_array() as $row) {
        array_push($export_list, $row);
    }
}

$nextExport = getNextExportInvoiceFromTo($o_main, $currentOwnerCompanyId);
$fromId = $nextExport['fromId'];
$fromNumber = $nextExport['fromNumber'];
$toId = $nextExport['toId'];
$toNumber = $nextExport['toNumber'];
$hasUnexportedInvoices = $nextExport['hasUnexportedInvoices'];

// Export script
if ($invoice_accountconfig['activate_global_export']) {
    $exportScript = $ownercompany_accountconfig['global_export_script'];
} else {
    $o_query = $o_main->db->get_where('ownercompany', array('id' => $currentOwnerCompanyId));
    $exportScriptOC = $o_query ? $o_query->row_array() : array();
    $exportScript = basename($exportScriptOC['export2ScriptFolder']);
}
?>

<h4><?php echo $formText_SuggestedExport_output; ?></h4>

<?php if ($hasUnexportedInvoices): ?>
    <div class="gtable">
        <div class="gtable_row">
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $formText_Date_output;?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $invoice_accountconfig['activate_global_export'] ? $formText_InvoiceIdFrom_output : $formText_InvoiceNumberFrom_output; ?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $invoice_accountconfig['activate_global_export'] ? $formText_InvoiceIdTo_output : $formText_InvoiceNumberTo_output;?></div>
            <div class="gtable_cell gtable_cell_head" style="width:40%;"><?php echo $formText_Export_output;?></div>
        </div>
        <div class="gtable_row">
            <div class="gtable_cell"><?php echo date('d.m.Y'); ?></div>
            <div class="gtable_cell"><?php echo $invoice_accountconfig['activate_global_export'] ? $fromId : $fromNumber; ?></div>
            <div class="gtable_cell"><?php echo $invoice_accountconfig['activate_global_export'] ? $toId : $toNumber; ?></div>
            <div class="gtable_cell">
                <a href="#" class="exportInvoices" data-from-id="<?php echo $fromId; ?>" data-from-number="<?php echo $fromNumber; ?>" data-to-id="<?php echo $toId; ?>" data-to-number="<?php echo $toNumber; ?>" data-ownercompany-id="<?php echo $currentOwnerCompanyId; ?>" data-ownercompany-export-script="<?php echo $exportScript; ?>">
                    <?php echo $formText_Export_output; ?>
                </a>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="noResultsPanel"><?php echo $formText_NoInvoicesToExport_output; ?></div>
<?php endif; ?>

<h4><?php echo $formText_History_output; ?></h4>

<?php if (count($export_list)): ?>
    <div class="gtable" id="gtable_search">
        <div class="gtable_row">
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $formText_CreatedDate_output;?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $invoice_accountconfig['activate_global_export'] ? $formText_InvoiceIdFrom_output : $formText_InvoiceNumberFrom_output; ?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $invoice_accountconfig['activate_global_export'] ? $formText_InvoiceIdTo_output : $formText_InvoiceNumberTo_output;?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $formText_DownloadFile_output;?></div>
            <div class="gtable_cell gtable_cell_head" style="width:20%;"><?php echo $formText_Send_output;?></div>
        </div>

        <?php foreach($export_list as $row): ?>
            <?php
            $file = json_decode($row['file']);
            $fileUrl = $extradomaindirroot.'/../'.$file[0][1][0].'?caID='.$_GET['caID'].'&table=invoice_export2_history&field=file&ID='.$file[0][4];
            ?>
            <div class="gtable_row" data-href="<?php echo $s_edit_link;?>">
                <div class="gtable_cell"><?php echo date('d.m.Y', strtotime($row['created'])); ?></div>
                <div class="gtable_cell"><?php echo $invoice_accountconfig['activate_global_export'] ? $row['invoiceIdFrom'] : $row['invoiceNrFrom']; ?></div>
                <div class="gtable_cell"><?php echo $invoice_accountconfig['activate_global_export'] ? $row['invoiceIdTo'] : $row['invoiceNrTo']; ?></div>
                <div class="gtable_cell">
                    <a href="<?php echo $fileUrl; ?>"><?php echo $formText_DownloadFile_output; ?></a>
                </div>
                <div class="gtable_cell c6">
                    <?php if(!$row['sentTime']): ?>
                        <a href="#" class="sendExport" data-export-id="<?php echo $row['id']; ?>"><?php echo $formText_Send_output; ?></a>
                    <?php else: ?>
                        <?php echo date('d.m.Y', strtotime($row['sentTime'])); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="noResultsPanel"><?php echo $formText_NoPreviousExportResults_output; ?></div>
<?php endif; ?>
