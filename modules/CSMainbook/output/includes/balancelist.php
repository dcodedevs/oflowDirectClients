<?php
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";


$view_by = isset($_GET['view_by']) ? $_GET['view_by'] : 0;
$sql_where= "";
if($_GET['date_range_view']){
	$date_till = isset($_GET['date_till']) && $_GET['date_till'] != "" ? $_GET['date_till'] : date("t.m.Y", strtotime("-1 month"));
	$date_from = isset($_GET['date_from']) && $_GET['date_from'] != "" ? $_GET['date_from'] : date("01.m.Y", strtotime("-1 month"));
	// $sql_where = " AND cmv.date >= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."' AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_till)))."'";
	$sql_where = " AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_till)))."'";
} else {
	$date_till = isset($_GET['date_till']) && $_GET['date_till'] != "" ? $_GET['date_till'] : date("d.m.Y");
	$sql_where = " AND cmv.date <= '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_till)))."'";
}
if($view_by == 0){
	$s_sql = "SELECT cs_bookaccount.*, SUM(cmt.amount) as totalAmount FROM cs_bookaccount
	JOIN cs_mainbook_transaction cmt ON cmt.bookaccount_id = cs_bookaccount.id
	JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
	WHERE cs_bookaccount.content_status < 2 ".$sql_where."
	AND ABS(cmt.amount) > 0
	GROUP BY cs_bookaccount.id ORDER BY cs_bookaccount.number ASC";
	$o_query = $o_main->db->query($s_sql);
	$bookaccounts = $o_query ? $o_query->result_array() : array();
	if($_GET['date_range_view']) {
		$s_sql = "SELECT cs_bookaccount.*, SUM(cmt.amount) as totalAmount FROM cs_bookaccount
		JOIN cs_mainbook_transaction cmt ON cmt.bookaccount_id = cs_bookaccount.id
		JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		WHERE cs_bookaccount.content_status < 2 AND cmv.date < '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."'
		AND ABS(cmt.amount) > 0
		GROUP BY cs_bookaccount.id ORDER BY cs_bookaccount.number ASC";
		$o_query = $o_main->db->query($s_sql);
		$bookaccount_incoming = $o_query ? $o_query->result_array() : array();
		$bookaccount_incoming_array = array();
		foreach($bookaccount_incoming as $bookaccount) {
			$bookaccount_incoming_array[$bookaccount['id']] = $bookaccount;
		}
	}
} else if($view_by == 1) {
	$s_sql = "SELECT creditor.companyname as name, SUM(cmt.amount) as totalAmount, creditor.id FROM creditor
	JOIN cs_mainbook_transaction cmt ON cmt.creditor_id = creditor.id
	JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
	WHERE creditor.content_status < 2 ".$sql_where."
	AND ABS(cmt.amount) > 0
	GROUP BY creditor.id ORDER BY creditor.companyname";
	$o_query = $o_main->db->query($s_sql);
	$bookaccounts = $o_query ? $o_query->result_array() : array();

	if($_GET['date_range_view']) {
		$s_sql = "SELECT creditor.companyname as name, SUM(cmt.amount) as totalAmount, creditor.id FROM creditor
		JOIN cs_mainbook_transaction cmt ON cmt.creditor_id = creditor.id
		JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		WHERE creditor.content_status < 2  AND cmv.date < '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."'
		AND ABS(cmt.amount) > 0
		GROUP BY creditor.id ORDER BY creditor.companyname ASC";
		$o_query = $o_main->db->query($s_sql);
		$bookaccount_incoming = $o_query ? $o_query->result_array() : array();
		$bookaccount_incoming_array = array();
		foreach($bookaccount_incoming as $bookaccount) {
			$bookaccount_incoming_array[$bookaccount['id']] = $bookaccount;
		}
	}
} else if($view_by == 2) {
	$s_sql = "SELECT CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as name, SUM(cmt.amount) as totalAmount FROM customer
	JOIN cs_mainbook_transaction cmt ON cmt.debitor_id = customer.id
	JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
	WHERE customer.content_status < 2 ".$sql_where."
	AND ABS(cmt.amount) > 0
	GROUP BY customer.id ORDER BY customer.name";
	$o_query = $o_main->db->query($s_sql);
	$bookaccounts = $o_query ? $o_query->result_array() : array();
	if($_GET['date_range_view']) {
		$s_sql = "SELECT CONCAT_WS(' ', customer.name, customer.middlename, customer.lastname) as name, SUM(cmt.amount) as totalAmount, customer.id FROM customer
		JOIN cs_mainbook_transaction cmt ON cmt.debitor_id = customer.id
		JOIN cs_mainbook_voucher cmv ON cmv.id = cmt.cs_mainbook_voucher_id
		WHERE customer.content_status < 2 AND cmv.date < '".$o_main->db->escape_str(date("Y-m-d", strtotime($date_from)))."'
		AND ABS(cmt.amount) > 0
		GROUP BY customer.id ORDER BY customer.name ASC";
		$o_query = $o_main->db->query($s_sql);
		$bookaccount_incoming = $o_query ? $o_query->result_array() : array();
		$bookaccount_incoming_array = array();
		foreach($bookaccount_incoming as $bookaccount) {
			$bookaccount_incoming_array[$bookaccount['id']] = $bookaccount;
		}
	}
}

?>
<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<div class="">
					<?php echo $formText_ViewBalance_Output;?>
					<select name="" class="viewTransactionsChanger" autocomplete="off">
						<option value="0"><?php echo $formText_Bookaccount_output;?></option>
						<option value="1" <?php if($view_by == 1) echo 'selected';?>><?php echo $formText_Creditor_output;?></option>
						<option value="2" <?php if($view_by == 2) echo 'selected';?>><?php echo $formText_Debitor_output;?></option>
					</select>
					<?php if($_GET['date_range_view']) { ?>
						<input type="text" class="datepicker dateFrom" autocomplete="off" value="<?php if($date_from != "") { echo date("d.m.Y", strtotime($date_from));}?>"/>
						- <input type="text" class="datepicker dateTill" autocomplete="off" value="<?php if($date_till != "") { echo date("d.m.Y", strtotime($date_till));}?>"/>

						<span class="normal_view"><?php echo $formText_NormalView_output;?></span>
					<?php } else { ?>
						<?php echo $formText_DateTill_output;?>
						<input type="text" class="datepicker dateTill" autocomplete="off" value="<?php if($date_till != "") { echo date("d.m.Y", strtotime($date_till));}?>"/>

						<span class="date_range_view"><?php echo $formText_DateRangeView_output;?></span>
					<?php } ?>

				</div>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle"><?php echo $formText_BookaccountBalance_Output;?></div>
					<div class="p_contentBlock no-vertical-padding">
						<table class="table" width="100%" border="0" cellpadding="0" cellspacing="0">
							<tr>
								<th></th>
								<th>
									<?php
									if($view_by == 0){
										echo $formText_Bookaccount_output;
									} else if($view_by == 1){
										echo $formText_Creditor_output;
									} else if($view_by == 2){
										echo $formText_Debitor_output;
									}
									?>
								</th>
								<?php if($_GET['date_range_view']) { ?>
									<th class="rightAligned"><?php echo $formText_IngoingBalance_output;?></th>
									<th class="rightAligned"><?php echo $formText_TransactionsInPeriod_output;?></th>
									<th class="rightAligned"><?php echo $formText_OutgoingBalance_output;?></th>
								<?php } else { ?>
									<th class="rightAligned"><?php echo $formText_Amount_output;?></th>
								<?php } ?>
							</tr>
							<?php foreach($bookaccounts as $bookaccount) { ?>
								<tr>
									<td><?php echo $bookaccount['number'];?></td>
									<td><?php echo $bookaccount['name'];?></td>

									<?php if($_GET['date_range_view']) { ?>
										<td class="rightAligned"><?php echo $bookaccount_incoming_array[$bookaccount['id']]['totalAmount'];?></td>
										<td class="rightAligned"><?php echo $bookaccount['totalAmount']-$bookaccount_incoming_array[$bookaccount['id']]['totalAmount'];?></td>
										<td class="rightAligned"><?php echo $bookaccount['totalAmount'];?></td>
									<?php } else { ?>
										<td class="rightAligned"><?php echo $bookaccount['totalAmount'];?></td>
									<?php } ?>
								</tr>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
.date_range_view {
	cursor: pointer;
	color: #23527c;
	margin-left: 20px;
}
.normal_view {
	cursor: pointer;
	color: #23527c;
	margin-left: 20px;
}
</style>
<script type="text/javascript">
var out_popup;
var out_popup_options = {
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
		$(this).removeClass('fullWidth');
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
				var data = {
					date_till: $(".dateTill").val(),
					view_by: $(".viewTransactionsChanger").val()
				}
				loadView("balancelist", data);
				$('#popupeditboxcontent').html('');
			}
		}
	}
};

$(function(){

	$(".datepicker").datepicker({
		firstDay: 1,
		dateFormat: "dd.mm.yy",
		onSelect: function(){
			var data = {
				date_from: $(".dateFrom").val(),
				date_till: $(".dateTill").val(),
				date_range_view: '<?php echo $_GET['date_range_view']?>',
				view_by: $(".viewTransactionsChanger").val(),
			}
			loadView("balancelist", data);
		}
	})
	$(".viewTransactionsChanger").change(function(){
		var data = {
			date_from: $(".dateFrom").val(),
			date_till: $(".dateTill").val(),
			date_range_view: '<?php echo $_GET['date_range_view']?>',
			view_by: $(".viewTransactionsChanger").val(),
		}
		loadView("balancelist", data);
	})
	$(".date_range_view").off("click").on("click", function(){
		var data = {
			date_range_view: 1,
			view_by: $(".viewTransactionsChanger").val(),
		}
		loadView("balancelist", data);
	})
	$(".normal_view").off("click").on("click", function(){
		var data = {
			view_by: $(".viewTransactionsChanger").val(),
		}
		loadView("balancelist", data);
	})
})
</script>
