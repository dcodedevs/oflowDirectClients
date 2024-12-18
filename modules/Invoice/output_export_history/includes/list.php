<?php
require_once __DIR__ . '/list_btn.php';

function getNextExportInvoiceFromTo($o_main, $ownerCompanyId) {
    // Min, max from invoice table
    $sql = "SELECT MAX(external_invoice_nr) max_number,
    MIN(external_invoice_nr) min_number,
    MAX(id) max_id,
    MIN(id) min_id,
    COUNT(*) count
    FROM invoice";
    if ($ownerCompanyId) {
        $sql .= " WHERE ownercompany_id = ?";
    }
    $o_query = $o_main->db->query($sql, array($ownerCompanyId));
    $invoice_minmax = $o_query ? $o_query->row_array() : array();

    // Last export
    $sql = "SELECT MAX(invoiceNrTo) last_export_number,
    MAX(invoiceIdTo) last_export_id,
    COUNT(*) count
    FROM invoice_export_history";
    if ($ownerCompanyId) {
        $sql .= " WHERE ownercompanyId = ?";
    }
    $o_query = $o_main->db->query($sql, array($ownerCompanyId));
    $export_history_minmax = $o_query ? $o_query->row_array() : array();

    // From id and number
    if ($export_history_minmax['count']) {
        $fromId = $export_history_minmax['last_export_id'] + 1;
        $fromNumber = $export_history_minmax['last_export_number'] + 1;
    } else {
        $fromId = $invoice_minmax['min_id'];
        $fromNumber = $invoice_minmax['min_number'];
    }

    // To id and number
    $toId = $invoice_minmax['max_id'];
    $toNumber = $invoice_minmax['max_number'];

    // Has unexported invoices?
    $hasUnexportedInvoices = $fromId <= $toId && $invoice_minmax['count'] > 0 ? 1 : 0;

    // Return
    $return = array (
        'fromId' => $fromId,
        'fromNumber' => $fromNumber,
        'toId' => $toId,
        'toNumber' => $toNumber,
        'hasUnexportedInvoices' => $hasUnexportedInvoices
    );

    return $return;
}

if (!$invoice_accountconfig['activate_global_export']) {
    $ownercompanies = array();
    $o_query = $o_main->db->get('ownercompany');
    if ($o_query && $o_query->num_rows()) {
        foreach ($o_query->result_array() as $row) {
            array_push($ownercompanies, $row);
        }
    }
}
?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php if ($invoice_accountconfig['activate_global_export']): ?>
                    <?php $currentOwnerCompanyId = 0; ?>
                    <div class="ownercompany_export_history_block">
                        <?php require __DIR__ . '/list_company_block.php'; ?>
                    </div>
                <?php else: ?>
                    <?php foreach ($ownercompanies as $ownercompany): ?>
                        <?php $currentOwnerCompanyId = $ownercompany['id']; ?>
                        <div class="ownercompany_export_history_block">
                            <h3 class="ownercompany_export_history_block_title"><?php echo $ownercompany['name']; ?></h3>
                            <?php require __DIR__ . '/list_company_block.php'; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
var out_popup;
var out_popup_options={
    follow: [true, false],
    modalClose: false,
    escClose: false,
    closeClass:'b-close',
    onOpen: function(){
        $(this).addClass('opened');
        //$(this).find('.b-close').on('click', function(){out_popup.close();});
    },
    onClose: function(){
        $(this).removeClass('opened');
        if($(this).is('.close-reload')) {
            var redirectUrl = $(this).data("redirect");
            if(redirectUrl != "" && redirectUrl != undefined){
                document.location.href = redirectUrl;
            } else {
                loadView("list");
            }
          // window.location.reload();
        }
    }
};

$(document).ready(function() {

    $('.sendExport').on('click', function(e) {
        e.preventDefault();
        e.preventDefault();
        var data = {
            exportId: $(this).data('export-id'),
            idFrom: $(this).data('id-from'),
            idTo: $(this).data('id-to')
        };

        ajaxCall('sendExport', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });

    });

    $(".exportInvoices").on('click', function(event) {
        event.preventDefault();
        /* Act on the event */
        var export_script = $(this).data('ownercompany-export-script');

        var data = {
            ownercompany_id: $(this).data('ownercompany-id'),
            activate_global_export: '<?php echo $invoice_accountconfig['activate_global_export']; ?>',
            from_id: $(this).data('from-id'),
            from_number: $(this).data('from-number'),
            to_id: $(this).data('to-id'),
            to_number: $(this).data('to-number'),
            export_script: $(this).data('ownercompany-export-script'),
            redirect_to: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&folderfile=".$_GET['folderfile']."&folder=".$_GET['folder']."&submodule=".$_GET['submodule'].""; ?>'
        };

        if (typeof(showLoader) !== 'boolean') var showLoader = true;

        // Default data
        var __data = {
            fwajax: 1,
            fw_nocss: 1
        }

        // Concat default and user data
        var ajaxData = $.extend({}, __data, data);

        // Show loader
        if (showLoader) $('#fw_loading').show();

        // Run AJAX
        $.ajax({
            cache: false,
            type: 'POST',
            dataType: 'json',
            url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=OwnerCompany&folderfile=output&folder=output&inc_obj=ajax&inc_act=export&exportBtn="; ?>' + export_script,
            data: ajaxData,
            success: function(json){
                if (showLoader) $('#fw_loading').hide();

                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(json.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            }
        });
    })
});

</script>

<style>
.noResultsPanel {
    background:#FFF;
    padding:10px 15px;
    border:1px solid #efecec;
}

.ownercompany_export_history_block {
    margin-bottom:60px;
}

.ownercompany_export_history_block_title {
    margin-bottom:10px;
    padding-bottom:10px;
    border-bottom:1px solid #EEE;
    font-weight:bold;
    margin: 0px 10px 10px 10px;
}
.ownercompany_export_history_block h4 {
    padding: 0px 10px;
}
</style>
