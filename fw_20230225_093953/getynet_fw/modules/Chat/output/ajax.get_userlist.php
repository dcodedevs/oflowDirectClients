<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$_POST['lang'].".php";
if(is_file($s_lang_file)) include($s_lang_file);

$s_api_file = __DIR__ .'/../../../includes/APIconnector.php';
if(!function_exists("APIconnectorUser") && file_exists($s_api_file)) require_once($s_api_file);

$showCompany = $_POST['company'];
$showSet = $_POST['set'];

$UserContactSet = APIconnectorUser("contactsetget", $_COOKIE['username'], $_COOKIE['sessionID'], array('SHOW_SET' => $showSet, 'SHOW_COMPANY' => $showCompany));
if(!sizeof($showCompany) && !sizeof($showSet))
{
	$UserList = json_encode(array());
} else {
	$UserList = APIconnectorUser("usergetlist", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANYACCESS_ID'=>$_POST['caID']));

}

$UserList = json_decode($UserList,true);
$preseletedIds = json_decode($_POST['preseletedIds']);

//pagination
$searchValue = $_POST['search'];
$page = intval($_POST['page']);
$per_page = intval($_POST['per_page']);
if($page > 0 && $per_page > 0){
	$startNumber = ($page - 1) * $per_page;
	$endNumber = $startNumber + $per_page;
}
$totalCount = count($UserList);
if($searchValue != ""){
	$searchedArray = array();
	foreach($UserList as $item) {
		$nameToDisplay = $item['name']." ".$item['middle_name']." ".$item['last_name'];
		$nameToDisplay = trim(preg_replace('!\s+!', ' ', $nameToDisplay));
		if(strpos(mb_strtolower($nameToDisplay), mb_strtolower($searchValue)) !== false) {
			array_push($searchedArray, $item);
		}
	}
	$UserList = $searchedArray;
	$searchedCount = count($UserList);
}
if($_POST['show_department']){
	$response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $_COOKIE['username'], $_COOKIE['sessionID'], array('COMPANY_ID'=>$_POST['companyID'])), true);
	$v_membersystem = array();
	$v_registered_usernames = array();
	foreach($response['data'] as $writeContent)
	{
		$v_membersystem[$writeContent['registeredID']] = $writeContent;
		if($writeContent['registeredID'] > 0) $v_registered_usernames[] = $writeContent['username'];
	}
	$registered_group_list = array();
	if(count($v_registered_usernames)>0)
	{
		$v_response = json_decode(APIconnectorUser("group_get_list_by_filter", $_COOKIE['username'], $_COOKIE['sessionID'], array('company_id'=>$_POST['companyID'], 'usernames'=>$v_registered_usernames, 'not_hidden'=>1)),true);
		if(isset($v_response['status']) && $v_response['status'] == 1)
		{
			$registered_group_list = $v_response['items'];
		}
	}
}
?>
<ul class="list" id="fwcl_list" data-total-count="<?php echo $totalCount;?>" data-search-count="<?php echo $searchedCount;?>">
	<?php
	$itemIterator = 0;
	foreach($UserList as $item):
		if(($itemIterator >= $startNumber && $itemIterator < $endNumber) || ($page == 0 || $per_page == 0)) {

			$shownItems = $itemIterator;
			$preselected = false;
			if(in_array($item['userid'], $preseletedIds)){
				$preselected = true;
			}
			?>
			<?php $profileimage = json_decode($item['image'],true); ?>
			<li data-fullname="<?php echo mb_strtolower($item['name']." ".$item['middle_name']." ".$item['last_name']); ?>">
				<a href="#" class="<?php if($preselected) echo 'selected';?>" data-user-id="<?php echo $item['userid']; ?>" rel="<?php echo $variables->languageDir."modules/OnlineList/output/userdetails_ajax.php?userID=".$item['userid']."&amp;profileimage=".urlencode($item['image'])."&amp;dlang=".$variables->defaultLanguageID."&amp;lang=".$variables->languageID;?>">
					<span class="image">
						<span class="user_image">
							<?php if(is_array($profileimage)){?><img src="https://pics.getynet.com/profileimages/<?php print $profileimage[0]; ?>" alt="" border="0" /><?php }
							else {
								echo '&nbsp;';
							}
							?>
						</span>
					</span>

					<span class="name"><?php echo $item['name']." ".$item['middle_name']." ".$item['last_name']; ?><span class="glyphicon glyphicon-ok selected-icon fw_icon_color" style="display: none;"></span></span>
					<span class="status_text"><?php echo $item['formatted_address'];?></span>
					<?php /*?><span class="status_text">
						<?php if($item['showMessage'] == 1 and $item['statusMessage']) { ?><?php echo "- ".$item['statusMessage']; ?><?php } else { echo '&nbsp;'; }?>
					</span><?php */?>
					<?php if(!$_POST['hideStatus']) { ?>
						<span class="status status-<?php echo $item['status']; ?>"></span>
					<?php } ?>
					<?php if($_POST['show_department']){
						$groups = array();
						$departments = array();
						if(isset($registered_group_list[$v_membersystem[$item['userid']]['username']]))
						{
							$allGroupsForNotRegistered = $registered_group_list[$v_membersystem[$item['userid']]['username']];
							foreach($allGroupsForNotRegistered as $groupSingleItem){
								if($groupSingleItem['department']){
									array_push($departments, $groupSingleItem);
								} else {
									array_push($groups, $groupSingleItem);
								}
							}
						}
						// if(isset($v_membersystem[$item['userid']])) {
						// 	$single_item = $v_membersystem[$item['userid']];
						// 	foreach($single_item['groups'] as $groupSingle){
						// 		if(intval($groupSingle['department']) == 0) {
						// 			array_push($groups, $groupSingle);
						// 		} else {
						// 			array_push($departments, $groupSingle);
						// 		}
						// 	}
						// }
						if(count($departments) > 0) {
							?>
							<span class="departmentInfo fas fa-info-circle"></span>
							<div class="departments">
								<?php
								foreach($departments as $department){
									?>
									<div class="department"><?php echo $department['name'];?></div>
									<?php
								}
								?>
							</div>
						<?php } ?>
					<?php } ?>
				</a>
			</li>
			<?php
		}
		$itemIterator++;
	endforeach; ?>
</ul>
<?php
if(($searchValue == "" && ($shownItems + 1) < $totalCount) || ($searchValue != "" && ($shownItems + 1) < $searchedCount)) {?>
<div class="showMoreInChatWrapper"><?php echo $formText_Showing_chat2;?> <?php echo $shownItems+1?> <span class="showMoreInChat fw_text_link_color"  style="margin-left: 15px;"><?php echo $formText_LoadMore_chat2;?></span></div>
<?php } ?>
