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
	'COMPANY_ID'=>$v_customer['getynet_customer_id'],
	'SHOW_ALL_PARTNER_ACCOUNTS'=>$v_customer_accountconfig['getynet_show_all_partner_accounts'],
	'GET_APP_EDITION'=>1
);

$s_request = APIconnectorAccount("accountlistbypartneridget", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param);
$v_accounts = json_decode($s_request, TRUE);
?>
<div class="popupform">
	<div id="popup-validate-message"></div>
	<div class="inner">
		<table class="table table-bordered table-striped table-condensed">
			<tr>
				<th class="smallColumn">#</th>
				<th><?php echo $formText_AccountName_output; ?></th>
				<th><?php echo $formText_Server_output; ?></th>
				<th><?php echo $formText_Domains_output; ?></th>
				<th><?php echo $formText_Edition_output; ?></th>
				<th class="account_actions">&nbsp;</th>
			</tr>
			<?php
			$l_i = 1;
			if(intval($v_customer['getynet_customer_id'])>0 && !isset($v_accounts['error']) && sizeof($v_accounts)>0)
			{
				foreach($v_accounts as $s_key => $v_account)
				{
					$v_param = array
					(
						'PARTNER_ID'=>$v_customer_accountconfig['getynet_partner_id'],
						'PARTNER_PWD'=>$v_customer_accountconfig['getynet_partner_pw'],
						'SEARCH_ACCOUNT_NAME'=>$v_account['accountname']
					);

					$domainlist = json_decode(APIconnectorAccount("account_get_domainlist", $v_accountinfo['accountname'], $v_accountinfo['password'], $v_param),true);
					$domaindetaillist = json_decode($domainlist['data']['active'][0],true);
					//print_r($domainlist);
					?>
					<tr>
						<td><?php echo $l_i;?> </td>
						<td><?php echo $v_account['accountname'];?></td>
						<td><?php echo $v_account['getynetserver'];?></td>
						<td><?php 
						if(is_array(json_decode($domainlist['data'],true)['active']) )
						{
							$activedomains = json_decode($domainlist['data'],true)['active'];
							foreach($activedomains as $s_key => $domaininfo)
							{		
								 echo "Active: ".$domaininfo['domain']." <button class=\"output-btn small dnstooltip\"   data-status=\"2\" title=\"".$domaininfo['DNS']."\" \">DNS</button>";
								echo "<br>";
							} 
						}
						if(is_array(json_decode($domainlist['data'],true)['closed']) )
						{		
							$closeddomains = json_decode($domainlist['data'],true)['closed'];
							foreach($closeddomains as $s_key => $v_account)
							{		
								 echo "Closed: ".$s_key." og ".$v_account." <br>"; 

							} 

						}
					
					 ?></td>
						<td>
							<?php if(count($v_account['app_edition'])>0) { ?>
							<select class="output-input" onChange="output_getynet_account_edition_change(this);" data-account-id="<?php echo $v_account['accountID'];?>">
							<?php foreach($v_account['app_edition'] as $v_edition)
							{
								?><option value="<?php echo $v_edition['edition_id'];?>"<?php echo ($v_edition['edition_id'] == $v_account['edition'] ? ' selected':'');?>><?php echo $v_edition['name'];?></option><?php
							}
							?>
							</select>
							<?php } ?>
						</td>
						<td><?php 
						if($v_account['status'] == 0)
						{
							?>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="1" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_CloseAccount_Output;?></button>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="2" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ArchiveAccount_Output;?></button>
							
							<?php
						} else if($v_account['status'] == 1) {
							?>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="0" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ActivateAccount_Output;?></button>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="2" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ArchiveAccount_Output;?></button>
							 
							<?php
						}  else if($v_account['status'] == 3) {
							?>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="0" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ActivateAccount_Output;?></button>
							<button class="output-btn small" onClick="output_getynet_account_status_change(this);" data-status="2" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ArchiveAccount_Output;?></button>
							 
							<?php
						}else {
							echo $formText_ArchivedAccount_Output;
						}
						?>
						<button class="output-btn small" onClick="output_getynet_account_show_domainadmin(this);" data-status="" data-account-id="<?php echo $v_account['accountID'];?>"><?php echo $formText_ManageDomains_Output;?></button>
						</td>
					</tr>
					<?php
					$l_i++;
				}
			}
			if($l_i == 1)
			{
				?><tr><td colspan="4"><center><?php echo $formText_NothingFound_Output;?></center></td></tr><?php
			}
		?>
		</table>
	</div>
	<div class="popupformbtn">
		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Close_Output;?></button>
	</div>
</div>
