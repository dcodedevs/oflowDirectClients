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

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($_POST['customer_id']));
if($o_query && $o_query->num_rows()>0) {
	$v_customer = $o_query->row_array();
}

$v_param = array
(
	'PARTNER_ID'=>$v_customer_accountconfig['getynet_partner_id'],
	'PARTNER_PWD'=>$v_customer_accountconfig['getynet_partner_pw'],
	'ACCOUNT_ID'=>$_POST['account_id']
);
 

$s_request = APIconnectorAccount("accountinfoget", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
$v_accountinfoAPI = json_decode($s_request, TRUE);
//print_r($v_accountinfoAPI);
?>
<div class="popupform">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<table class="table table-bordered table-striped table-condensed">
			<tr>
				<th><?php echo $formText_AccountName_output; ?></th>
				<th><?php echo $formText_Domainname_output; ?></th>
				<th><?php echo $formText_Status_output; ?></th>
				<th><?php echo $formText_Action_output; ?></th>
				<th class="smallColumn account_actions">&nbsp;</th>
			</tr>
			<?php
			$l_i = 1;
			 
				 
			$v_param = array
			(
				'PARTNER_ID'=>$v_customer_accountconfig['getynet_partner_id'],
				'PARTNER_PWD'=>$v_customer_accountconfig['getynet_partner_pw'],
				'SEARCH_ACCOUNT_NAME'=>$v_accountinfoAPI[0]['accountname']
			);

			$domainlist = json_decode(APIconnectorAccount("account_get_domainlist", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param),true);
			///print_r($domainlist);
			 
			$domainlistarray = json_decode($domainlist['data'],true);

			foreach ($domainlistarray as $status => $domains)
			{

				//echo "<br>statuskey = ".$status. " value". $domains;
				if(is_array($domains))
				{
					for($x=0;$x<count($domains);$x++)
					{
						?>
						<tr>
							<td><?php echo $v_accountinfoAPI[0]['accountname'];?></td>
							<td><?php echo $domains[$x] ; ?></td>
							<td><?php echo $v_accountinfoAPI[0]['getynetserver'];?></td>
							<td><?php echo $status; ?></td>
							<td> 
								<?php if($status == 'closed')
								{
									?>
										<button class="output-btn small" onClick="output_getynet_account_change_domain_status(this);"  data-status=0 data-domainname="<?php echo $domains[$x];?>" data-account_id="<?php echo $v_accountinfoAPI[0]['accountID'];?>"><?php echo $formText_OpenDomain_Output;?></button>
									<?php
								}
								else
								{
									?>
										<button class="output-btn small" onClick="output_getynet_account_change_domain_status(this);" data-status=1 data-domainname="<?php echo $domains[$x];?>" data-account_id="<?php echo $v_accountinfoAPI[0]['accountID'];?>"><?php echo $formText_CloseDomain_Output;?></button>
									<?php
								}
	
							?>
							
							
							</td>

						</tr>
						<?php
					}
				}
			} 
		?>
		</table>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
	</div>
</div>