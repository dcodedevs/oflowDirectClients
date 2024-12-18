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
$perPage = 50;

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
} else {
	echo $formText_KeycardsSystemNotActivated_Output;
	return;
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

$keycards = $api_amadeus->get_keycards();
$showMore = false;
$showing = $page * $perPage;
$currentCount = count($keycards);

if($showing < $currentCount) $showMore = true;
$totalPages = ceil($currentCount/$perPage);

$v_order = array(
	'number',
	'First_Name',
	'AG_Name',
);
$l_order = 0;
if(isset($_GET['order_by'])) {
	$l_order = intval($_GET['order_by']);
}
$l_order_desc = 0;
if(isset($_GET['order_desc'])) {
	$l_order_desc = intval($_GET['order_desc']);
}
$v_sort = array();
foreach($keycards as $l_key => $v_keycard)
{
	$v_keycard['First_Name'] = $v_keycard['First_Name'].' '.$v_keycard['Last_Name'];
	$v_sort[$l_key] = $v_keycard[$v_order[$l_order]];
}
asort($v_sort, SORT_STRING);
if(1 == $l_order_desc) $v_sort = array_reverse($v_sort, TRUE);
ob_start();
?>
<div class="resultTableWrapper">
<div class="gtable" id="gtable_search">
	<div class="gtable_row">
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&filter=keycards&order_by=0".((0==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_AmadeusKeycard_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&filter=keycards&order_by=1".((1==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_AmadeusEmployee_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head">
			<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&filter=keycards&order_by=2".((2==$l_order&&0==$l_order_desc)?"&order_desc=1":"")."&page=".$page.'&_='.time();?>"><?php echo $formText_AmadeusAccessGroups_Output;?></a>
		</div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_AmadeusConnection_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Customer_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Contactperson_Output;?></div>
		<div class="gtable_cell gtable_cell_head"><?php echo $formText_Email_Output;?></div>
	</div>
    <?php
	$l_not_connected = 0;
	$l_counter = 0;
	$l_from = (($page-1) * $perPage);
	$l_to = (($page-1) * $perPage) + $perPage;
	foreach($v_sort as $l_key => $s_value)
	{
		//if($l_counter < $l_from || $l_counter > $l_to) continue;
		$v_keycard = $keycards[$l_key];
		$v_contactperson = array();
		$b_conn_amadeus = $b_connected = FALSE;
		$o_query = $o_main->db->query("SELECT c.name AS customer_name, cp.* FROM contactperson cp LEFT OUTER JOIN customer c ON c.id = cp.customerId WHERE cp.access_card_number_on_card = '".$o_main->db->escape_str($v_keycard['number'])."' AND cp.external_locksystem2_person_id = '".$o_main->db->escape_str($v_keycard['person_id'])."'");
		if(!empty(trim($v_keycard['person_id'])) && $o_query && $o_query->num_rows()>0)
		{
			if(isset($_GET['notconnected']) && 1 == $_GET['notconnected']) continue;
			$b_connected = $b_conn_amadeus = TRUE;
			$v_contactperson = $o_query->row_array();
		} else {
			$l_not_connected++;
		}
		if('' != $s_search)
		{
			$s_hex_converted = '';
			$s_hex = strtoupper(str_pad($v_keycard['number'], 8, '0', STR_PAD_LEFT));
			for($i=-1; $i>=-4; $i--)
			{
				$s_tmp = substr($s_hex, 2*$i, 2);
				$s_hex_converted .= $s_tmp;
			}
			$v_keycard['number_dec'] = hexdec($s_hex_converted);
			if(stripos($v_keycard['number'], $s_search) === FALSE && stripos($v_keycard['number_dec'], $s_search) === FALSE && stripos(preg_replace('/\s+/', ' ', $v_keycard['First_Name'].' '.$v_keycard['Last_Name']), $s_search) === FALSE && stripos($v_keycard['AG_Name'], $s_search) === FALSE && stripos($v_keycard['person_id'], $s_search) === FALSE) continue;
		}
        ?>
        <div class="gtable_row">
	        <div class="gtable_cell"><?php echo $v_keycard['number']; ?></div>
	        <div class="gtable_cell"><?php echo $v_keycard['First_Name'].' '.$v_keycard['Last_Name'].' - '.$v_keycard['person_id']; ?></div>
	        <div class="gtable_cell"><?php echo $v_keycard['AG_Name']; ?></div>
			<div class="gtable_cell"><?php
				if(!$b_conn_amadeus)
				{
					?><a href="#" class="output-connect-keycard" data-keycard="<?php echo $v_keycard['number'];?>" data-cardholder-id="<?php echo $v_keycard['ID'];?>">
						<span class="glyphicon glyphicon-credit-card"></span>
					</a><?php
				} else {
					?><span class="glyphicon glyphicon-ok"></span><?php
				}
			?></div>
	        <div class="gtable_cell"><?php echo ($b_connected ? $v_contactperson['customer_name'] : '-'); ?></div>
	        <div class="gtable_cell"><?php echo ($b_connected ? preg_replace('/\s+/', ' ', $v_contactperson['name'].' '.$v_contactperson['middlename'].' '.$v_contactperson['lastname']) : '-'); ?></div>
	        <div class="gtable_cell"><?php echo ($b_connected ? $v_contactperson['email'] : '-'); ?></div>
		</div>
		<?php
		$l_counter++;
	}
	?>
</div>
<?php
/*if($totalPages > 1)
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
		?><a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&filter=".$_GET['filter']."&order_by=".$l_order.(1==$l_order_desc?"&order_desc=1":"")."&page=".$page;?>" class="page-link"><?php echo $page;?></a><?php
	}
}*/
?>
</div>
<script type="text/javascript">
$(document).ready(function(){
	$(".output-connect-keycard").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			keycard: $(this).data('keycard'),
			cardholder_id: $(this).data('cardholder-id'),
		};
		ajaxCall('connect_contactperson_keycard', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		}, true);
	});
});
</script>
<?php
$s_buffer = ob_get_clean();
?>
<div style="margin-bottom:10px;">
	<form method="get">
		<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
		<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
		<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>">
		<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
		<input type="hidden" name="filter" value="<?php echo $_GET['filter'];?>">
		<input type="hidden" name="order_by" value="<?php echo $l_order;?>">
		<?php if(1==$l_order_desc) { ?>
		<input type="hidden" name="order_desc" value="1">
		<?php } ?>
		<div class="row">
			<div class="col-xs-8">
				<a href="<?php echo "?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&caID=".$_GET['caID']."&companyID=".$_GET['companyID']."&filter=".$_GET['filter']."&notconnected=1&order_by=".$l_order.(1==$l_order_desc?"&order_desc=1":"")."&page=".$page;?>"><?php echo $l_not_connected.' '.$formText_NotConnectedCards_Output;?></a>
			</div>
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
<?php
echo $s_buffer;