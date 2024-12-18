<?php
if(!function_exists("APIconnectorUser"))
	if(is_file(__DIR__."/../includes/APIconnector.php")) include_once(__DIR__."/../includes/APIconnector.php");
$includeFile = __DIR__."/../languages/empty.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../languages/".$_GET['languageID'].".php";
if(is_file($includeFile)) include($includeFile);

if(isset($_COOKIE['username'],$_COOKIE['sessionID']))
{
	$dataCompanyList = json_decode(APIconnectorUser("companyaccessgetlist", $_COOKIE['username'], $_COOKIE['sessionID']),true);
	$userLoginHistory = json_decode(APIconnectorUser("userloginhistoryget", $_COOKIE['username'], $_COOKIE['sessionID']),true);
	?>
	<span class="glyphicon glyphicon-remove" id="accountlist_close" onclick="fw_toggle_account_list();"></span>
	<div class="wraper" id="accountlist">
		<div class="search"><input type="text" class="fuzzysearch"></div>
		<ul class="list">
			<?php
			$counter = 0;
			foreach($dataCompanyList['data'] as $item)
			{
				if($item['getynetserver'] == '' || $item['getynetserver'] == '0')
					$subdomain = 'www';
				else
					$subdomain = $item['getynetserver'];

				$url = $subdomain;
				$customeraccess =0;
				$target = '_self';
				if($item['membersystemID'] >0)
				{
					$customeraccess =1;
				}
				if($item['crmuserurl'] != '')
				{
					$url = "http://".$item['crmuserurl']."/modules/GetynetIDLogin/output/login.php?username=".$_COOKIE['username']."&sessionID=".$_COOKIE['sessionID']."&companyID=".$item['companyID']."&cID=".$item['id']."&returl=".urlencode("http://".$item['crmuserurl']);
					if($item['connect_thru_serverframework'] == 1)
					{
						$url = "http://".$subdomain."/serverframework/modules/Membersystem/forwarduser.php?companyID=".$item['companyID']."&accountname=".$item['accountname']."&cID=".$item['id']."&forwardurl=". urlencode($url);
					}
				} else {
					$url = "http://".$subdomain.($item['customFramework']!="" ? "/accounts/".$item['accountname']."/".$item['customFramework'] : "")."/index.php?companyID=".$item['companyID']."&accountname=".$item['accountname']."&cID=".$item['id'];
					if($item['connect_thru_serverframework'] == 1)
					{
						$url = "http://".$subdomain."/serverframework/modules/Membersystem/forwarduser.php?companyID=".$item['companyID']."&accountname=".$item['accountname']."&cID=".$item['id']."&forwardurl=". urlencode($url);
					}
				}
				if($item['membersystemID'] > 0)
				{
					$crmcustomeraccount[] = $item['accountname'];
				}
				
				$showaccountname = 0;
				if($dataCompanyList['data'][$counter+1]['companyID'] == $item['companyID'] || $dataCompanyList['data'][$counter-1]['companyID'] == $item['companyID'])
					$showaccountname = 1;
				?>
				<li class="item"><a href="<?php echo $url;?>" target="<?php echo $target;?>" class="name"><?php echo $item['companyname']; if($showaccountname == 1){?> - <?php print $item['friendlyaccountname'];  if($customeraccess == 1){ if($item['membersystemidname'] != ''){ print " - ".implode('<br/>',explode('¤',$item['membersystemidname'])); }else{print " - ".$formText_Access_AccountList;}} }?></a></li>
				<?php
				$counter++;
			}
			?>
		</ul>
	</div>
	<?php
}