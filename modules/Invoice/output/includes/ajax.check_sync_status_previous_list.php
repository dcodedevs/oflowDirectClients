<div class="popupform">
    <div class="popupformTitle"><?php echo $formText_InvoicesWithSyncErrors_output;?></div>

    <?php
    $sql = "SELECT * FROM invoice WHERE sync_status = 1";
    $o_query = $o_main->db->query($sql);
    $result = $o_query && $o_query->num_rows() ? $o_query->result_array() : array();
    ?>

    <div id="out-checked-invoices">
        <div id="out-checked-invoice-list">
            <?php foreach($result as $invoice): ?>
                <div class="checked-invoice error">
                    <?php echo $invoice['external_invoice_nr']; ?> - <?php echo $invoice['sync_status_check_message']; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>




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

