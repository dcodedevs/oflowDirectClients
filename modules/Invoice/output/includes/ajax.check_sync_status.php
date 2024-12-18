<?php
$sql = "SELECT * FROM invoice_accountconfig";
$o_query = $o_main->db->query($sql);
$invoice_accountconfig = $o_query && $o_query->num_rows() ? $o_query->row_array() : array();
?>

<?php if (!$invoice_accountconfig['path_check_sync_status']):  ?>
    <div><?php echo $formText_PathCheckSyncStatusConfigMissing_output; ?></div>
<?php else: ?>
    <div class="popupform">
        <div class="popupformTitle"><?php echo $formText_CheckInvoiceSyncStatus_output;?></div>

        <div><?php echo $formText_CheckPreviouslySyncedInvoiceStatusesThatAreNotCheckedYet_output; ?></div>

        <?php
        $sql = "SELECT * FROM invoice WHERE sync_status = 1";
        $o_query = $o_main->db->query($sql);
        $invoices_with_sync_errors_count = $o_query && $o_query->num_rows() ? $o_query->num_rows() : 0;

        $o_query = $o_main->db->query($sql);
        // Get invoice list
        $sql = "SELECT * FROM invoice WHERE (sync_status = 2 OR sync_status = 3) AND sync_status_checked_datetime IS NULL";
        $o_query = $o_main->db->query($sql);
        $invoice_list = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
        $invoice_count = count($invoice_list);
        
        // Cleaning up invoice list for JS script so we do not expose all database data to client
        $invoices_list_json = array();
        foreach($invoice_list as $invoice) {
            array_push($invoices_list_json, array(
                'id' => intval($invoice['id'])
            ));
        }
        ?>
        
        <?php if ($invoice_count): ?>
            <div id="out-process-progress" style="display:block;">
                <div class="progress-title"><span class="processed">0</span> / <span class="total"><?php echo $invoice_count; ?></span></div>
                <div class="progress-bar2">
                    <div class="progress-bar2-fill"></div>
                </div>
            </div>


            <div id="out-checked-invoices">
                <ul id="out-checked-invoices-filter">
                    <li><a href="#" class="filter-button active" data-status="all"><?php echo $formText_All_output; ?> <span class="count-label">0</span></a></li>
                    <li><a href="#" class="filter-button" data-status="synced"><?php echo $formText_Synced_output; ?> <span class="count-label">0</span></a></li>
                    <li><a href="#" class="filter-button" data-status="error"><?php echo $formText_Error_output; ?> <span class="count-label">0</span></a></li>
                </ul>

                <div id="out-checked-invoice-list"></div>
            </div>

            <div class="popupformbtn">
			    <button class="checkInvoices"><?php echo $formText_Check_Output; ?></button>
            </div>
        <?php else: ?>
            <?php echo $formText_NoUncheckedInvoicesFound_output; ?>
        <?php endif; ?>

        <?php if ($invoices_with_sync_errors_count): ?>
            <div id="out-previous-sync-errors">
                <a href="#" class="out-previous-sync-errors-button"><?php echo $formText_ShowInvoicesWithSyncErrorsFromPreviousChecks_output; ?> (<?php echo $invoices_with_sync_errors_count; ?>)</a>
            </div>
        <?php endif; ?>

    </div>
<?php endif; ?>


<script type="text/javascript">
    $(document).ready(function(e) {
        var invoiceStatusCounts = {
            synced: 0,
            error: 0,
        };

        function checkInvoice(index, invoiceList) {
            var count = invoiceList.length;

            if (index < count) {
                var invoiceData = invoiceList[index];
                var requestData = {
                    id: invoiceData.id
                };

                ajaxCall('check_sync_status_run_hook', requestData, function(json) {
                    var message = json.data && json.data.message ? json.data.message : '(missing sync status message)';
                    var status = json.data && json.data.status ? json.data.status : 1;

                    addCheckedInvoice(message, status);
                    updateProgress(index + 1, count);
                    updateFilterStatusCounts(status);
                    checkInvoice(index + 1, invoiceList);
                }, false);
            }
        }

        function updateProgress(index, total, lineText) {
            var width = index / total * 100;
            $('.progress-bar2-fill').css({ width: width + '%'});
            $('.progress-title .processed').text(index);
        }

        function addCheckedInvoice(text, status) {
            var statusClass = 'default';
            if (status == 1) statusClass = 'error';
            if (status == 2) statusClass = 'synced';

            $('#out-checked-invoices').show();
            $('#out-checked-invoice-list').append('<div class="checked-invoice ' + statusClass + '">' + text + '</div>');
        }
        
        function updateFilterStatusCounts(status) {
            if (status == 1) invoiceStatusCounts.error += 1;
            if (status == 2) invoiceStatusCounts.synced += 1;

            var total = invoiceStatusCounts.synced + invoiceStatusCounts.error;

            $('.filter-button[data-status="all"] .count-label').text(total);
            $('.filter-button[data-status="synced"] .count-label').text(invoiceStatusCounts.synced);
            $('.filter-button[data-status="error"] .count-label').text(invoiceStatusCounts.error);
        }

        function updateActiveFilter(status) {
            // Reset
            $('#out-checked-invoice-list').removeClass('show-only-synced').removeClass('show-only-error');
            $('#out-checked-invoices-filter .filter-button').removeClass('active');
            
            // Apply active state
            $('.filter-button[data-status="' + status + '"]').addClass('active');

                $('#out-checked-invoice-list').addClass('show-only-' + status);
            if (status != 'all') {
            }
        }

        $('.checkInvoices').on('click', function() {
            var invoiceList = JSON.parse('<?php echo json_encode($invoices_list_json); ?>');
            if (!invoiceList || !invoiceList.length) return;

            $('.popupformbtn').hide();

            checkInvoice(0, invoiceList);
        });

        $('.filter-button').on('click', function(e) {
            e.preventDefault();
            var status = $(this).data('status');
            updateActiveFilter(status);
        });


        $(".out-previous-sync-errors-button").on('click', function(e){
            e.preventDefault();
            var data = { };
            ajaxCall('check_sync_status_previous_list', data, function(obj) {
                $('#popupeditboxcontent').html('');
                $('#popupeditboxcontent').html(obj.html);
                out_popup = $('#popupeditbox').bPopup(out_popup_options);
                $("#popupeditbox:not(.opened)").remove();
            });
        });
    });
</script>


<style>
#out-checked-invoices {
    /* display:none; */
    padding: 15px;
    /* height: 300px; */
}

#out-checked-invoices-filter {
    margin-bottom: 15px;
}
#out-checked-invoices-filter li {
    display: inline-block;
}

#out-checked-invoices-filter .filter-button {
    display: block;
    padding: 4px 10px;
    color: #333;
    border-radius: 50px;
    border: 1px solid transparent;
    text-decoration: none;
}

#out-checked-invoices-filter .filter-button:hover {
    text-decoration: none;
    border: 1px solid #EEE;
    background: #F8F8F8;
}

#out-checked-invoices-filter .filter-button.active {
    border: 1px solid #EEE;
    background: #F8F8F8;
    font-weight: bold;
}

#out-checked-invoices-filter .filter-button:visited,
#out-checked-invoices-filter .filter-button:active {
    text-decoration: none;
}

#out-checked-invoice-list {
    height: 200px;
    overflow: hidden;
    overflow-y: scroll;
    background: #F8F8F8;
    border: 1px solid #EEE;
    border-radius: 3px;
    padding: 15px;
}

#out-checked-invoice-list.show-only-synced .checked-invoice.error {
    display: none;
}

#out-checked-invoice-list.show-only-error .checked-invoice.synced {
    display: none;
}

#out-checked-invoice-list .checked-invoice {
    padding-top: 5px;
    padding-bottom: 5px;
    border-bottom:1px solid #EEE;
}

#out-checked-invoice-list .checked-invoice.synced {
    color: #27ae60;
}

#out-checked-invoice-list .checked-invoice.error {
    color: red;
}

.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input, .popupformbtn button {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
/* .error {
	border: 1px solid #c11;
} */
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}
.popupform .line .lineInput {
	width:70%;
	float:left;
}

#out-process-progress {
	padding:15px;
	display:none;
	font-size:15px;
}

#out-process-progress .progress-title {
	font-size:18px;
}

#out-process-progress .progress-bar2 {
	border-radius:4px;
	overflow: hidden;
	width:100%;
	min-height:25px;
	background:#F8F8F8;
	border:1px solid #EEE;
	margin-bottom:5px;
}
#out-process-progress .progress-bar2-fill {
	width:0;
	min-height:25px;
	background:#27ae60;
}

#out-previous-sync-errors {
    margin-top: 12px;
    padding-top: 12px;
    border-top:1px solid #EEE;
    text-align: center;
    color: red;
}

#out-previous-sync-errors a {
    color: red;
}
</style>

