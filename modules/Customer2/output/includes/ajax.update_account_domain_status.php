<?php
if(!function_exists("APIconnectAccount")) include(__DIR__."/../../input/includes/APIconnect.php");

$o_query = $o_main->db->get('accountinfo');
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$o_query = $o_main->db->query("SELECT * FROM customer_accountconfig ORDER BY id DESC");
$v_customer_accountconfig = $o_query ? $o_query->row_array() : array();

$v_param = array
(
	"PARTNER_ID"=>$v_customer_accountconfig['getynet_partner_id'],
	"PARTNER_PWD"=>$v_customer_accountconfig['getynet_partner_pw'],
	"ACCOUNT_ID"=>$_POST['account_id'],
	"DOMAINNAME"=>$_POST['domainname'],
	"STATUS"=>$_POST['status']
);

$s_request = APIconnectorAccount("account_update_domainstatus", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
$v_response = json_decode($s_request, TRUE);
?>
<div class="popupform">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<?php
		if($v_response !== NULL && $v_response['status'] == 1)
		{
			if($_POST['status'] == 2)
			{
				echo $formText_AccountArchivedSuccessfully_Output;
			} else {
				echo $formText_AccountDomainIsUpdated_Output;
			}
		} else {
			echo $formText_ErrorOccuredProcessingRequest_Output;
		}
		?>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
	</div>
</div>