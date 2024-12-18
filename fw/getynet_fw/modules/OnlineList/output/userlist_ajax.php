<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

$apiLib = __DIR__ .'/../../../includes/APIconnector.php';
if(!function_exists("APIconnectorUser") && file_exists($apiLib)) require_once($apiLib);
$showCompany = $_GET['company'];
$showSet = $_GET['set'];

if($_GET['refresh'])
{
	$UserContactSet = APIconnectorUser("contactsetget", $_COOKIE['username'], $_COOKIE['sessionID'], array('SHOW_SET' => $showSet, 'SHOW_COMPANY' => $showCompany));

	if(!sizeof($showCompany) && !sizeof($showSet)) {
		$UserList = json_encode(array());
	}
	else {
		$UserList = APIconnectorUser("usergetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>(isset($_GET['caID'])?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID'])));
	}

	$s_sql = 'UPDATE session_framework SET cache_userlist = ?, cache_contactset = ? WHERE companyaccessID = ? AND session = ? AND username = ?';
	$o_query = $o_main->db->query($s_sql, array($UserList, $UserContactSet, (isset($_GET['caID'])?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']), $_COOKIE['sessionID'], $_COOKIE['username']));
} else {
	$UserList = '';
	$o_query = $o_main->db->query('SELECT * FROM session_framework WHERE companyaccessID = ? AND session = ? AND username = ?', array((isset($_GET['caID'])?$_GET['caID']:$_COOKIE[$_GET['accountname'].'_caID']), $_COOKIE['sessionID'], $_COOKIE['username']));
	if($o_query && $o_row = $o_query->row()) $UserList = $o_row->cache_userlist;
}

$UserList = json_decode($UserList,true);

?>
<ul class="list" id="fwcl_list">
	<?php foreach($UserList as $item): ?>
	<?php $profileimage = json_decode($item['image'],true); ?>
	<li>
		<a href="#" data-user-id="<?php echo $item['userid']; ?>" rel="<?php echo $variables->languageDir."modules/OnlineList/output/userdetails_ajax.php?userID=".$item['userid']."&amp;profileimage=".urlencode($item['image'])."&amp;dlang=".$variables->defaultLanguageID."&amp;lang=".$variables->languageID;?>">
			<span class="image">
				<span class="crop">
					<?php if(is_array($profileimage)) { ?>
						<img src="https://pics.getynet.com/profileimages/<?php print $profileimage[2]; ?>" alt="" border="0" />
					<?php }
					else { ?>
						<span class="shortname">
							<?php
							$nameParts = explode(" ", $item['name']);
							$shortName = strtoupper(mb_substr($nameParts[0],0,1) . mb_substr($nameParts[1],0,1));
							echo $shortName;
							?>
						</span>
					<?php } ?>
				</span>
			</span>

			<span class="info">
				<span class="name">
					<?php echo $item['name']; ?>
				</span>
				<span class="status_text">
					<?php if($item['showMessage'] == 1 and $item['statusMessage']) { ?><?php echo "- ".$item['statusMessage']; ?><?php } else { echo '&nbsp;'; }?>
				</span>
				<span class="status status-<?php echo $item['status']; ?>">

				</span>
			</span>
		</a>
	</li>
	<?php endforeach; ?>
</ul>