<?php 

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

require("fnc_get_settlement_sending_info.php");
$l_settlement_id = $_GET['settlementId'];
$l_creditor_id = $_GET['creditorId'];

$sql = "SELECT * FROM cs_settlement WHERE id = '".$o_main->db->escape_str($l_settlement_id)."'";
$o_query = $o_main->db->query($sql);
$settlement = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM cs_settlement_line WHERE cs_settlement_id = '".$o_main->db->escape_str($l_settlement_id)."' AND creditor_id = '".$o_main->db->escape_str($l_creditor_id)."'";
$o_query = $o_main->db->query($sql);
$settlement_line = $o_query ? $o_query->row_array() : array();

$sql = "SELECT * FROM creditor WHERE id = '".$o_main->db->escape_str($l_creditor_id)."'";
$o_query = $o_main->db->query($sql);
$creditor = $o_query ? $o_query->row_array() : array();
$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$l_settlement_id;

?><div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
				<div class="p_pageDetails">
					<div class="p_pageDetailsTitle">
						<div class="" style="float: left">
							<?php echo $formText_Settlement_output;?>
							<div class="caseId"><span class="caseIdText"><?php echo $settlement['id'];?></span></div>
						</div>
						<div class="clear"></div>
                    </div>
                    <div class="p_contentBlock">
					    <div class="caseDetails">
					        <table class="mainTable" width="100%" border="0" cellpadding="0" cellspacing="0">
					        	<tr>
					                <td class="txt-label"><?php echo $formText_Date_output;?></td>
					                <td class="txt-value">
					                	<?php echo date("d.m.Y", strtotime($settlement['date']));?>
					                </td>
					            </tr>
					        </table>
                        </div>
                    </div>
                    <div class="p_contentBlockWrapper">
                        <div class="p_pageDetailsSubTitle white dropdown_content_show ">
                            <?php echo $formText_InformationToSend_Output;?>
                        </div>
                        <div class="p_contentBlock dropdown_content noTopPadding">
                                <?php
                                $result = get_settlement_sending_info($o_main,$settlement['id'], $creditor['id']);
                                if($result['error'] == ""){
                                ?>                                
                                <table class="table">
                                    <tr>
                                        <th><?php echo $formText_Name_output;?></th>
                                        <th><?php echo $formText_BookaccountNr_output;?></th>
                                        <th><?php echo $formText_Amount_output;?></th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $formText_Bank_output;?></td>
                                        <td><?php echo 1920;?></td>
                                        <td><?php echo $result['total_bank_amount'];?></td>
                                    </tr>
                                    <tr>
                                        <td><?php echo $formText_Vat_output;?></td>
                                        <td><?php echo 2910;?></td>
                                        <td><?php echo $result['total_vat_amount'];?></td>
                                    </tr>
                                    <?php foreach($result['invoices'] as $invoice) { ?>                                        
                                        <tr>
                                            <td><?php echo $invoice['invoice_nr'];?></td>
                                            <td><?php echo 1500;?></td>
                                            <td><?php echo $invoice['amount'];?></td>
                                        </tr>
                                    <?php } ?>
                                </table>
                                <?php if($settlement_line['sent_to24']) {
                                    ?>
                                    <div class=""><?php echo $formText_SettlementSent_output;?></div>
                                    <div class="send_settlement"><?php echo $formText_SendSettlementAgain_output;?></div>
                                    <?php
                                } else {?>
                                    <div class="send_settlement"><?php echo $formText_SendSettlement_output;?></div>
                                    <div class="clear"></div>
                                <?php } ?>
                                <?php } else {
                                    echo $formText_ErrorWithSum_output."<br/><br/>";        
                                    
                                    echo $formText_TotalBankAmount_output." ". $result['total_bank_amount']."<br>";
                                    echo $formText_TotalVatAmount_output." ".$result['total_vat_amount']."<br>";
                                    echo $formText_TotalInvoices_output." ".$result['total_invoices_amount']."<br>";
                                } ?>
                                <div id="validate-message"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .send_settlement {
        color: #fff;
        border-radius: 5px;
        cursor: pointer;
        background: #0095E4;
        border: none;
        padding: 10px 14px;
        margin: 0 10px 0 0;
        min-width: 120px;
        text-align: center;
        cursor: pointer;
        float: right;
        margin-top: 10px;
    }
    #validate-message {
        color: red;
        margin-top: 15px;
    }
</style>
<script type="text/javascript">
    $(function(){
        $(".send_settlement").off("click").on("click", function(){
            var data = {creditorId:  '<?php echo $l_creditor_id?>', settlementId: "<?php echo $l_settlement_id?>"};
            ajaxCall("send_settlement_to_24", data, function(json) {
                if(json.error !== undefined) {
                    var _msg = '';
                    $.each(json.error, function(index, value){
                        var _type = Array("error");
                        if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
                        _msg = _msg + '<div class="msg-' + _type[0] + '">' + value + '</div>';
                    });
                    $("#validate-message").html(_msg, true);
                    $("#validate-message").show();
                } else {
                    loadView("send_settlement_to_24", data);
                }
            })
            
        })
    })
</script>