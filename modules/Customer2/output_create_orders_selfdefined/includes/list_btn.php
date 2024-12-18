<?php
$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
?>
<div class="p_headerLine"><?php
if($moduleAccesslevel > 10)
{	?>
	<div class="backToCustomer btnStyle">
		<div class="plusTextBox active">
			<div class="text"><?php echo $formText_Back_Output; ?></div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	<?php
}
?>
</div>


<script type="text/javascript">
    $(".backToCustomer").on('click', function(e){
        e.preventDefault();
        fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>', false, true);
    });
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSelfDefinedFields {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSubscriptionType {
		margin-left: 40px;
	}
</style>
