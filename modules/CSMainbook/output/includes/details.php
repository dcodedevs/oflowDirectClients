<?php
$cid = $_GET['cid'];
$s_sql = "SELECT * FROM cs_mainbook_voucher WHERE id = '".$o_main->db->escape_str($cid)."'";
$o_query = $o_main->db->query($s_sql);
$voucher = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM collecting_company_cases WHERE id = '".$o_main->db->escape_str($voucher['case_id'])."'";
$o_query = $o_main->db->query($s_sql);
$collectingCase = ($o_query ? $o_query->row_array() : array());
$checksum_url = "";
if(isset($_SESSION['checksum'])){
	$checksum_url = "&checksum=1";
}
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list".$checksum_url;

?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
				<a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>

				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle"><?php echo $formText_VoucherDetails_Output; echo " ".$voucher['id'];?></div>

					<?php if($voucher) { ?>
						<div class="p_contentBlock no-vertical-padding">
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td class="txt-label"><?php echo $formText_Text_output;?></td>
									<td class="txt-value">
										<?php echo $voucher['text'];?>
									</td>
								</tr>
								<tr>
									<td class="txt-label"><?php echo $formText_Date_output;?></td>
									<td class="txt-value">
										<?php if($voucher['date'] != "" && $voucher['date'] != "0000-00-00") echo date("d.m.Y", strtotime($voucher['date']));?>
									</td>
								</tr>
								<?php if($collectingCase) { ?>
									<tr>
										<td class="txt-label"><?php echo $formText_Case_output;?></td>
										<td class="txt-value">
											<?php echo $collectingCase['id'];?>
											<a href="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=CollectingCompanyCases&folderfile=output&folder=output&inc_obj=details&cid=".$collectingCase['id'];?>" target="_blank"><?php echo $formText_openCase_output;?></a>
										</td>
									</tr>
								<?php } ?>



                                <tr>
                                    <td class="txt-label"></td>
                                    <td class="btn-edit" colspan="2"><?php if($moduleAccesslevel > 10) { ?><span class="glyphicon glyphicon-pencil edit_voucher" data-voucher-id="<?php echo $cid; ?>"></span>
										
									<?php } ?></td>
                                </tr>
							</table>
						</div>
						<div class="p_pageDetailsTitle"><?php echo $formText_Transactions_Output;?></div>
						<div class="p_contentBlock no-vertical-padding">
							<table class="table" width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<th><?php echo $formText_Bookaccount_output;?></th>
			                        <th><?php echo $formText_Amount_output;?></th>
								</tr>
								<?php

						        $s_sql = "SELECT * FROM cs_mainbook_transaction WHERE cs_mainbook_voucher_id = ?";
						        $o_query = $o_main->db->query($s_sql, array($voucher['id']));
						        $paymentCoverlines = $o_query ? $o_query->result_array() : array();

								foreach( $paymentCoverlines as $paymentCoverline) {
			                        $total_amount = $paymentCoverline['amount'];

									$s_sql = "SELECT * FROM cs_bookaccount WHERE id = ?";
									$o_query = $o_main->db->query($s_sql, array($paymentCoverline['bookaccount_id']));
									$cs_bookaccount = $o_query ? $o_query->row_array() : array();
			                         ?>
			                         <tr>
			                             <td><?php echo $cs_bookaccount['name']; ?></td>
			                             <td><?php echo number_format($total_amount, 2, ",", " "); ?></td>
			                         </tr>
			                    <?php } ?>
							</table>
						</div>
					<?php } else {
						echo $formText_MissingVoucher_output;
					}?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>
.txt-label {
    width: 30%;
}
.btn-edit {
	text-align: right;
}
</style>
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
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
				var data = {
					cid: '<?php echo $cid;?>'
				}
            	loadView("details", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
$(function(){
	$(".edit_voucher").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			id: $(this).data("voucher-id")
		};
		ajaxCall('editVoucher', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".edit_voucher2").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			id: $(this).data("voucher-id")
		};
		ajaxCall('editVoucherWithPayment', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
})
</script>
