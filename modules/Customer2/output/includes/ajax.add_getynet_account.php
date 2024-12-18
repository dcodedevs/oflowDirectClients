<?php
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../input/includes/APIconnect.php");
$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $v_accountinfo = $o_query->row_array();
}
$s_sql = "select * from customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
    $v_customer_accountconfig = $o_query->row_array();
}
$v_param = array
(
	"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
	"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw']
);
if($moduleAccesslevel > 10)
{
	if(isset($_POST['output_form_submit']))
	{
		$l_customer_id = intval($_POST['customer_id']);
		$s_sql = "SELECT * FROM customer WHERE id = ?";
		$o_query = $o_main->db->query($s_sql, array($l_customer_id));
		if($o_query && $o_query->num_rows()>0) {
		    $v_customer = $o_query->row_array();
			if(preg_match("/^[a-zA-Z0-9]{3,25}$/",$_POST['account_name']))
			{
				$v_data = $v_param;
				$v_data['NEW_ACC_NAME'] = $_POST['account_name'];
				$v_data['SERVER_ID'] = $_POST['server_id'];
				$s_result = APIconnectAccount("accountavailabilitycheck", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_data);
				if($s_result!="OK")
				{
					$fw_error_msg = $formText_AccountNameAlreadyInUse_output;
					return;
				}
				$v_data = $v_param;
				$v_data['NEW_ACC_NAME'] = $_POST['account_name'];
				$v_data['SERVER_ID'] = $_POST['server_id'];
				$v_data['COMPANY_ID'] = $v_customer['getynet_customer_id'];
				if($_POST['library_account'] != '')
				{
					$v_data['APP_ACC'] = $_POST['library_account'];
					$s_result = APIconnectAccount("accountcreatelibrary", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_data);
				}
				else if($_POST['server_account'] != '')
				{
					$v_data['APP_ACC'] = $_POST['server_account'];
					$s_result = APIconnectAccount("accountcreate", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_data);
				} else {
					$fw_error_msg = $formText_SourceAccountNotSelected_output;
					return;
				}
				$v_data = json_decode($s_result, true);
				if(sizeof($v_data['data'])==0)
				{
					$fw_error_msg = $formText_AccountIsNotCreated_Output.". (API: ".$v_data['error'].")";
				} else {
					$fw_return_data = array('status'=>1);
				}
			} else {
				$fw_error_msg = $formText_AccountNameLenghtShouldBeAtLeast3SymbolsAndNotMoreThan25ContainLatinSymbolsAndNumbers_Output;
			}
		}
		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customer_id'];
		return;
	}
}
unset($v_data);
if(isset($_POST['customer_id']) && $_POST['customer_id'] > 0)
{
	$s_sql = "SELECT * FROM customer WHERE id = ?";
	$o_query = $o_main->db->query($s_sql, array($_POST['customer_id']));
	if($o_query && $o_query->num_rows()>0) {
	    $v_data = $o_query->row_array();
	}
}
if($v_data['getynet_customer_id'] == 0)
{
	echo $formText_CustomerNotFound_Output;
	return;
}
$data = json_decode(APIconnectAccount("serverbypartneridgetlist", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param), true);
$v_servers = $data['data'];
$b_show_servers = TRUE;
$s_buffer_servers = '';
foreach($v_servers as $l_key => $v_server)
{
	if(isset($v_customer_accountconfig['getynet_new_account_server']) && $v_customer_accountconfig['getynet_new_account_server'] == $v_server['serverID'])
	{
		$b_show_servers = FALSE;
	}
	$s_buffer_servers .= '<option value="'.$v_server['serverID'].'">'.$v_server['name'].'</option>';
}
?>
<div class="popupform">
<form class="output-form" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=".$s_inc_act ;?>" method="post">
	<input type="hidden" name="fwajax" value="1">
	<input type="hidden" name="fw_nocss" value="1">
	<input type="hidden" name="output_form_submit" value="1">
	<input type="hidden" name="customer_id" value="<?php print $_POST['customer_id'];?>">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<div class="line">
		<div class="lineTitle"><?php echo $formText_AccountName_Output; ?></div>
		<div class="lineInput"><input class="popupforminput botspace" name="account_name" type="text" value="" required autocomplete="off"></div>
		<div class="clear"></div>
		</div>
		<?php if($b_show_servers) { ?>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_Server_Output;?></div>
		<div class="lineInput">
			<select id="output-choose-server" name="server_id" class="popupforminput botspace" required>
				<option value=""><?php echo $formText_ChooseServer_Output;?></option>
				<?php echo $s_buffer_servers;?>
			</select>
		</div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_SourceAccountFromLibrary_Output;?></div>
		<div class="lineInput"><select id="output-library-account" name="library_account" class="popupforminput botspace"></select></div>
		<div class="clear"></div>
		</div>
		<div class="line">
		<div class="lineTitle"><?php echo $formText_OrSourceAccountFromServer_Output;?></div>
		<div class="lineInput"><select id="output-server-account" name="server_account" class="popupforminput botspace"></select></div>
		<div class="clear"></div>
		</div>
		<?php } else { ?>
		<input type="hidden" name="server_id" id="output-choose-server" value="<?php echo $v_customer_accountconfig['getynet_new_account_server'];?>" />
		<input type="hidden" name="library_account" id="output-library-account" value="" />
		<input type="hidden" name="server_account" id="output-server-account" value="<?php echo $v_customer_accountconfig['getynet_duplicate_account'];?>" />
		<div class="line">
		<div class="lineTitle"><?php echo $formText_SourceAccountFromServer_Output;?></div>
		<div class="lineInput"><?php echo $v_customer_accountconfig['getynet_duplicate_account'];?></div>
		<div class="clear"></div>
		</div>
		<?php } ?>
		<div class="clear"></div>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
	</div>
</form>
</div>
<script type="text/javascript">
$(function(){
	$("#output-choose-server").change(function(){
		fw_loading_start();
		$.ajax({
			url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_server_accounts";?>&server="+$(this).find("option:selected").val()+"&_"+Math.random(),
			cache: false,
			type: "POST",
			dataType: "json",
			data: {fwajax: 1, fw_nocss: 1},
			success: function(obj){
				$("#output-server-account").html(obj.data);
				fw_loading_end();
			}
		}).fail(function(){
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredProcessingRequest_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height()+$('#popupeditbox').outerHeight()-$('#popupeditbox').height());
			fw_loading_end();
		});
		$.ajax({
			url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=get_library_accounts";?>&server="+$(this).find("option:selected").val()+"&_"+Math.random(),
			cache: false,
			type: "POST",
			dataType: "json",
			data: {fwajax: 1, fw_nocss: 1},
			success: function(obj){
				$("#output-library-account").html(obj.data);
				fw_loading_end();
			}
		}).fail(function(){
			$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredProcessingRequest_Output;?>", true);
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height()+$('#popupeditbox').outerHeight()-$('#popupeditbox').height());
			fw_loading_end();
		});
	});
	$("form.output-form").submit(function(e){
		e.preventDefault();
		var account_name = $("#output-form input[name=account_name]").val();
		var server_id = $("#output-choose-server").val();
		var library_account = $("#output-library-account").val();
		var server_account = $("#output-server-account").val();
		$("#popup-validate-message").html('');
		if(account_name=='')
		{
			$("#popup-validate-message").html("<?php echo $formText_SpecifyAccountName_Output;?>", true);
		}
		if(server_id=='')
		{
			$("#popup-validate-message").html("<?php echo $formText_ChooseServer_Output;?>", true);
		}
		if(library_account=='' && server_account=='')
		{
			$("#popup-validate-message").html("<?php echo $formText_ChooseSourceAccount_Output;?>", true);
		}
		if($("#popup-validate-message").html().length > 0)
		{
			$("#popup-validate-message").show();
			$('#popupeditbox').css('height', $('#popupeditboxcontent').height()+$('#popupeditbox').outerHeight()-$('#popupeditbox').height());
		} else {
			if(!fw_click_instance)
			{
				fw_click_instance = true;
				fw_loading_start();
				$.ajax({
					url: $(this).attr('action'),
					cache: false,
					type: "POST",
					dataType: "json",
					data: $('form.output-form').serialize(),
					success: function(obj){
						fw_loading_end();
						fw_click_instance = false;
						if(obj.data && obj.data.status == 1)
						{
							out_popup.addClass("close-reload");
							out_popup.close();
						} else {
							$("#popup-validate-message").html(obj.error, true);
							$("#popup-validate-message").show();
							$('#popupeditbox').css('height', $('#popupeditboxcontent').height()+$('#popupeditbox').outerHeight()-$('#popupeditbox').height());
						}
					}
				}).fail(function(){
					$("#popup-validate-message").html("<?php echo $formText_ErrorOccuredProcessingRequest_Output;?>", true);
					$("#popup-validate-message").show();
					$('#popupeditbox').css('height', $('#popupeditboxcontent').height()+$('#popupeditbox').outerHeight()-$('#popupeditbox').height());
					fw_loading_end();
					fw_click_instance = false;
				});
			}
		}
		return false;
	});
});
</script>
<style>
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput {
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
</style>