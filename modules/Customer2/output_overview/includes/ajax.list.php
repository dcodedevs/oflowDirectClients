<?php
if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;

$l_order = 1;
if(isset($_GET['order_by'])) {
	$l_order = intval($_GET['order_by']);
}
$l_order_desc = 0;
if(isset($_GET['order_desc'])) {
	$l_order_desc = intval($_GET['order_desc']);
}
$v_order_desc = array(
	'ASC',
	'DESC',
);
$v_order = array(
	'c.id',
	'c.name',
	'cp.name',
	'cp.email',
);

$s_search = '';
if(isset($_GET['search'])) {
	$s_search = $_GET['search'];
}
if(isset($_POST['search'])) {
	$s_search = $_POST['search'];
}

$module = 'Customer2';

$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_membersystem_config = $o_query->row_array();
}
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}

$person_access_categories = $person_access_categories_amadeus = array();

if($v_customer_accountconfig['activateKeycardsSystem']) {
    // Keycards
    $integration = 'IntegrationArx';
    $integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
    if (file_exists($integration_file)) {
        require_once $integration_file;
        if (class_exists($integration)) {
            if ($api) unset($api);
            $api = new $integration(array(
                'o_main' => $o_main
            ));
        }
    }
    $integration = 'IntegrationAmadeus';
	$integration_file = __DIR__ . '/../../../'. $integration .'/internal_api/load.php';
	if (file_exists($integration_file)) {
		require_once $integration_file;
		if (class_exists($integration)) {
			if ($api_amadeus) unset($api_amadeus);
			$api_amadeus = new $integration(array(
				'o_main' => $o_main
			));
		}
	}
    $person_access_categories =  $api->get_assigned_categories_grouped_by_person();
	$person_access_categories_amadeus =  $api_amadeus->get_assigned_categories_grouped_by_person();
	foreach($person_access_categories_amadeus as $s_key => $v_item)
	{
		$v_tmp = array();
		foreach($v_item as $s_item) if($s_item != '-VIDE') $v_tmp[] = $s_item;
		unset($person_access_categories_amadeus[$s_key]);
		if(count($v_tmp)>0) $person_access_categories_amadeus[$s_key] = $v_tmp;
	}
	function isKeyCardUserOnArx($keycardNumber, $person_id) {
		global $o_main;
		global $api;
		$b_return = FALSE;
		
		if(!empty(trim($keycardNumber)))
		{
			$keycardNumber = trim($keycardNumber);
			$keycards = $api->get_keycards();
			foreach ($keycards as $card) {
				if ($card->number == $keycardNumber && $card->person_id == $person_id) {
					$b_return = TRUE;
				}
			}
		}
		
		return $b_return;
	}
	
	function isKeyCardUserOnAmadeus($keycardNumber, $person_id) {
		global $o_main;
		global $api_amadeus;
		$b_return = FALSE;
		
		if(!empty(trim($keycardNumber)))
		{
			$keycardNumber = trim($keycardNumber);
			$keycards = $api_amadeus->get_keycard($keycardNumber);
			foreach ($keycards as $card) {
				if ($card['person_id'] == $person_id) {
					$b_return = TRUE;
				}
			}
		}
		
		return $b_return;
	}
}

$v_selfdefined_companies = array();
$b_activate_selfdefined_company = $b_check_selfdefined_company = FALSE;
$s_response = APIconnectorAccount('companyname_selfdefined_getlist', $v_accountinfo['accountname'], $v_accountinfo['password']);
$v_response = json_decode($s_response, TRUE);
if(isset($v_response['status']) && 1 == $v_response['status'])
{
	$b_activate_selfdefined_company = TRUE;
	$v_selfdefined_companies = $v_response['items'];
}
$s_sql_where = '';
if('' != $s_search)
{
	$s_search_esc = $o_main->db->escape_str(str_replace(" ", "%", preg_replace('/\s+/', ' ', $s_search)));
	$s_sql_where = " AND (c.name LIKE '%".$s_search_esc."%' ESCAPE '!' OR CONCAT(cp.name, ' ', cp.middlename, ' ', cp.lastname) LIKE '%".$s_search_esc."%' ESCAPE '!' OR cp.email LIKE '%".$s_search_esc."%' ESCAPE '!' OR cp.access_card_number LIKE '%".$s_search_esc."%' ESCAPE '!' OR cp.access_card_number_on_card LIKE '%".$s_search_esc."%' ESCAPE '!')";
}
$s_order = $v_order[$l_order]." ".$v_order_desc[$l_order_desc];
$o_query = $o_main->db->query("SELECT c.name AS customer_name, cp.* FROM contactperson cp LEFT OUTER JOIN customer c ON c.id = cp.customerId WHERE cp.content_status = 0".$s_sql_where." ORDER BY ".$s_order);
$showMore = false;
$showing = $page * $perPage;
$currentCount = $o_query ? $o_query->num_rows() : 0;

if($showing < $currentCount) $showMore = true;
$totalPages = ceil($currentCount/$perPage);

?>
<div style="margin-bottom:10px;">
	<form method="get">
		<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
		<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
		<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>">
		<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
		<input type="hidden" name="order_by" value="<?php echo $l_order;?>">
		<?php if(1==$l_order_desc) { ?>
		<input type="hidden" name="order_desc" value="1">
		<?php } ?>
		<div class="row">
			<div class="col-xs-8"></div>
			<div class="col-xs-3">
				<input type="text" class="form-control" name="search" value="<?php echo $s_search;?>" placeholder="<?php echo $formText_Search_Output;?>" autocomplete="off">
			</div>
			<input type="hidden" name="_" value="<?php echo time();?>">
			<div class="col-xs-1">
				<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search"></span></button>
			</div>
		</div>
	</form>
</div>

<div class="resultTableWrapper">
<div class="gtable" id="gtable_search">
	<div class="gtable_row">
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&order_by=0".((0==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_Id_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&order_by=1".((1==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_Customer_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&order_by=2".((2==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_Contactperson_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&order_by=3".((3==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_Email_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_GetynetAccess_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_SelfdefinedCompany_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Membership_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_MembershipSubscription_Output;?></div>
		<?php if($v_customer_accountconfig['activate_door_access_code']) { ?>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_DoorCodeAccess_Output;?></div>
		<?php } ?>
		<?php if($v_customer_accountconfig['activateKeycardsSystem']) { ?>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_KeycardInArxSystem_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_KeycardInAmadeusSystem_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Network_Output;?></div>
		<?php } ?>
		<?php if($v_customer_accountconfig['activateGateSystem']) { ?>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_GateSystem_Output;?></div>
		<?php } ?>
	</div>
    <?php
	$o_query = $o_main->db->query("SELECT c.name AS customer_name, cp.* FROM contactperson cp LEFT OUTER JOIN customer c ON c.id = cp.customerId WHERE cp.content_status = 0".$s_sql_where." ORDER BY ".$s_order." LIMIT ".$perPage." OFFSET ".(($page-1) * $perPage));
    if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $v_contactperson)
	{
		unset($o_membersystem);
		if($v_contactperson['email']!="")
		{
			$o_membersystem = json_decode(APIconnectorAccount("membersystemcompanyaccessusernameget", $v_accountinfo['accountname'], $v_accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$v_contactperson["email"], "MEMBERSYSTEMID"=>$v_contactperson['customerId'], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
		}
        ?>
        <div class="gtable_row">
	        <div class="gtable_cell"><?php echo $v_contactperson['id']; ?></div>
	        <div class="gtable_cell"><?php echo $v_contactperson['customer_name']; ?></div>
	        <div class="gtable_cell"><?php echo preg_replace('/\s+/', ' ', $v_contactperson['name'].' '.$v_contactperson['middlename'].' '.$v_contactperson['lastname']); ?></div>
	        <div class="gtable_cell"><?php echo $v_contactperson['email']; ?></div>
	        <div class="gtable_cell">
				<?php
				if('' != $v_contactperson['email'] && isset($o_membersystem, $o_membersystem->data))
				{
					echo $formText_HaveMembersystemAccess_Output;
				}
				?>
			</div>
	        <div class="gtable_cell">
				<?php
				if('' != $v_contactperson['email'] && isset($o_membersystem, $o_membersystem->data))
				{
					if('' != $o_membersystem->data->companyname_selfdefined_id)
					{
						echo $v_selfdefined_companies[$o_membersystem->data->companyname_selfdefined_id]['name'];
					}
				}
				?>
			</div>
	        <div class="gtable_cell">
				<?php
				if('' != $v_contactperson['email'] && isset($o_membersystem, $o_membersystem->data))
				{
					$v_memberships = array(
						'' => '',
						0 => $formText_CustomerDefinedMembership_output,
						1 => $formText_SpecifiedMembership_output,
					);
					echo $v_memberships[$v_contactperson['intranet_membership_type']];
					if(1 == $v_contactperson['intranet_membership_type'])
					{
						$s_sql = "SELECT im.* FROM intranet_membership_contactperson_connection imcc JOIN intranet_membership im ON im.id = imcc.membership_id WHERE imcc.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
						$o_find = $o_main->db->query($s_sql);
						if($o_find && $o_find->num_rows()>0)
						{
							echo ' (';
							$l_counter = 0;
							foreach($o_find->result_array() as $v_connection)
							{
								echo ($l_counter>0?', ':'').$v_connection['name'];
								$l_counter++;
							}
							echo ')';
						}
					}
				}
				?>
			</div>
	        <div class="gtable_cell">
				<?php
				if('' != $v_contactperson['email'] && isset($o_membersystem, $o_membersystem->data))
				{
					$v_memberships = array(
						'' => '',
						0 => $formText_AnyCustomerSubscription_Output,
						1 => $formText_SpecifiedSubscriptions_Output,
						2 => $formText_NoSubscriptionNeeded_Output,
					);
					echo $v_memberships[$v_contactperson['intranet_membership_subscription_type']];
					if(1 == $v_contactperson['intranet_membership_subscription_type'])
					{
						$s_sql = "SELECT s.* FROM contactperson_subscription_connection csc JOIN subscriptionmulti s ON s.id = csc.subscriptionmulti_id WHERE csc.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."'";
						$o_find = $o_main->db->query($s_sql);
						if($o_find && $o_find->num_rows()>0)
						{
							echo ' (';
							$l_counter = 0;
							foreach($o_find->result_array() as $v_connection)
							{
								echo ($l_counter>0?', ':'').$v_connection['subscriptionName'];
								$l_counter++;
							}
							echo ')';
						}
					}
				}
				?>
			</div>
			<?php if($v_customer_accountconfig['activate_door_access_code']) { ?>
			<div class="gtable_cell">
				<?php
				$v_door_code_access = array(
					'' => '',
					0 => '',
					1 => $formText_AnyCustomerSubscription_Output,
					2 => $formText_SpecifiedSubscriptions_Output,
					3 => $formText_NoSubscriptionNeeded_Output,
				);
				echo $v_door_code_access[$v_contactperson['door_access_code_type']];
				if(2 == $v_contactperson['door_access_code_type'])
				{
					$s_sql = "SELECT s.* FROM contactperson_doorcode_connection cdc JOIN subscriptionmulti s ON cdc.subscriptionmulti_id = s.id AND s.content_status < 2 WHERE cdc.contactperson_id = '".$o_main->db->escape_str($v_contactperson['id'])."' AND s.customerId = '".$o_main->db->escape_str($v_contactperson['customerId'])."'";
					$o_find = $o_main->db->query($s_sql);
					if($o_find && $o_find->num_rows()>0)
					{
						echo ' (';
						$l_counter = 0;
						foreach($o_find->result_array() as $v_subscription)
						{
							echo ($l_counter>0?', ':'').$v_subscription['subscriptionName'];
							$l_counter++;
						}
						echo ')';
					}
				}
				?>
			</div>
			<?php } ?>
			<?php if($v_customer_accountconfig['activateKeycardsSystem']) { ?>
			<?php
			// Create person if ID does not exist on ARX, update if exists already
			$external_locksystem_person_id = ($v_contactperson['external_locksystem_person_id'] != '' ? $v_contactperson['external_locksystem_person_id'] : 'DCODE_ID_' . $v_contactperson['id']);
			$external_locksystem2_person_id = ($v_contactperson['external_locksystem2_person_id'] != '' ? $v_contactperson['external_locksystem2_person_id'] : 'D' . $v_contactperson['id']);
			

			$has_access_to_categories = array();
			if(isset($person_access_categories[$external_locksystem_person_id]) && count($person_access_categories[$external_locksystem_person_id]) > 0)
			{
				$has_access_to_categories = $person_access_categories[$external_locksystem_person_id];
			} else {
				$has_access_to_categories = $person_access_categories_amadeus[$external_locksystem2_person_id];
			}
			?>
			<div class="gtable_cell">
				<?php
				$b_exists_in_security_system = !empty($v_contactperson['access_card_number']) && isKeyCardUserOnArx($v_contactperson['access_card_number'], $external_locksystem_person_id);
				echo $b_exists_in_security_system ? $formText_Connected_Output.' ('.$v_contactperson['access_card_number'].')' : '';
				if(isset($person_access_categories[$external_locksystem_person_id]))
				{
					$l_counter = 0;
					foreach($person_access_categories[$external_locksystem_person_id] as $s_item)
					{
						echo ($l_counter>0?',<br>':' ').$s_item;
						$l_counter++;
					}
				}
				?>
			</div>
			<div class="gtable_cell">
				<?php
				$b_exists_in_security_system = !empty($v_contactperson['access_card_number_on_card']) && isKeyCardUserOnAmadeus($v_contactperson['access_card_number_on_card'], $external_locksystem2_person_id);
				echo $b_exists_in_security_system ? $formText_Connected_Output.' ('.$v_contactperson['access_card_number_on_card'].')' : '';
				if(count($person_access_categories[$external_locksystem_person_id]) > 0)
				{
					$l_counter = 0;
					foreach($person_access_categories_amadeus[$external_locksystem2_person_id] as $s_item)
					{
						echo ($l_counter>0?',<br>':' ').$s_item;
						$l_counter++;
					}
				}
				?>
			</div>
			<div class="gtable_cell">
				<?php
				echo $v_contactperson['network_status'] ? $v_contactperson['network_group_access'] : '';
				?>
			</div>
			<?php } ?>
			<?php if($v_customer_accountconfig['activateGateSystem']) { ?>
			<div class="gtable_cell">
				<?php
				$s_sql = "SELECT * FROM contactperson_gate_access WHERE contactpersonId = '".$o_main->db->escape_str($v_contactperson['id'])."' AND customerId = '".$o_main->db->escape_str($v_contactperson['customerId'])."' AND (deleted IS NULL OR deleted = '0000-00-00 00:00:00') ORDER BY sortnr";
				$o_find = $o_main->db->query($s_sql);
				if($o_find && $o_find->num_rows()>0)
				{
					echo $formText_HaveAccess_Output;
				}
				?>
			</div>
			<?php } ?>
		</div>
		<?php
	}
	?>
</div>
<?php
if($totalPages > 1)
{
	$currentPage = $page;
	$pages = array();
	array_push($pages, 1);
	if(!in_array($currentPage, $pages))
	{
		array_push($pages, $currentPage);
	}
	if(!in_array($totalPages, $pages))
	{
		array_push($pages, $totalPages);
	}
	for ($y = 10; $y <= $totalPages; $y+=10)
	{
		if(!in_array($y, $pages))
		{
			array_push($pages, $y);
		}
	}
	for($x = 1; $x <= 3;$x++)
	{
		$prevPage = $page - $x;
		$nextPage = $page + $x;
		if($prevPage > 0)
		{
			if(!in_array($prevPage, $pages))
			{
				array_push($pages, $prevPage);
			}
		}
		if($nextPage <= $totalPages)
		{
			if(!in_array($nextPage, $pages))
			{
				array_push($pages, $nextPage);
			}
		}
	}
	asort($pages);
	
	foreach($pages as $page)
	{
		?><a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&order_by=".$l_order.(1==$l_order_desc?"&order_desc=1":"")."&page=".$page;?>" class="page-link"><?php echo $page;?></a><?php
	}
}
?>
</div>