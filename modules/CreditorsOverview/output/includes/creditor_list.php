<?php
$page = 1;
// require_once __DIR__ . '/creditor_list_btn.php';
// $hook_file = __DIR__ . '/../../../Integration24SevenOffice/hooks/get_currency_rates.php';
// if (file_exists($hook_file)) {
// include $hook_file;
// if (is_callable($run_hook)) {
// 		$hook_result = $run_hook(array("creditor_id"=>$_GET['cid']));
// 		if(count($hook_result['currencyRates']) > 0){
// 			$currencyRates = $hook_result['currencyRates'];
// 			var_dump($currencyRates);
// 			foreach($currencyRates as $currencyRate) {
// 				if($currencyRate['symbol'] == $currencyName) {
// 					$currency_rate = $currencyRate['rate'];
// 					$error_with_currency = false;
// 					break;
// 				}
// 			}
// 		}
// 	}
// }

if($variables->loggID == "byamba@dcode.no") {	
	
// 	$curl = curl_init();
// 	curl_setopt_array($curl, array(
// 		CURLOPT_URL => "https://c005013.test.hosting.aptic.cloud/C005013-15530-T01/APIGW/login/oauth/access_token",
// 		CURLOPT_RETURNTRANSFER => true,
// 		CURLOPT_TIMEOUT=> 30,
// 		CURLOPT_CUSTOMREQUEST => "POST",
// 		CURLOPT_USERPWD => "david@dcode.no:Skarsnuten194!",
// 		CURLOPT_POSTFIELDS => "grant_type=client_credentials&client_id=david@dcode.no&client_secret=Skarsnuten194!",
// 		CURLOPT_HTTPHEADER => array(
// 		  "content-type: application/x-www-form-urlencoded",
// 		),
// 	));
// 	$response = curl_exec($curl);
// 	var_dump($response);
// 	$response_decoded = json_decode(trim($response), true);


	// $headers = array(
	// 	'Content-Type: application/json',
	// );
	// $curl = curl_init();
	// $get_params = $params ? '?' . http_build_query($params) : '';
	// curl_setopt($curl,CURLOPT_URL, "https://help.aptic.net/api/v1/CurrentUser" . $get_params);
	// curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);
	// curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	// $response = curl_exec($curl);
	// var_dump($response);
	// $response_decoded = json_decode(trim($response), true);

	
}
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php
				require __DIR__ . '/ajax.creditor_list.php';
				?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
		//$(this).find('.b-close').on('click', function(){out_popup.close();});
	},
	onClose: function(){
		if($(this).is('.close-reload')) {
			var redirectUrl = $(this).data("redirect");
			if(redirectUrl !== undefined && redirectUrl != ""){
				document.location.href = redirectUrl;
			} else {
            	loadView("list");
            }
          // window.location.reload();
	  } else if($(this).is('.close-reload-creditor')){
			var data = {
				building_filter:$(".buildingFilter").val(),
				customergroup_filter: $(".customerGroupFilter").val(),
				mainlist_filter: '<?php echo $mainlist_filter; ?>',
				list_filter: '<?php echo $list_filter; ?>',
				cid: '<?php echo $cid;?>',
				search_filter: $('.searchFilter').val(),
				search_by: $(".searchBy").val(),
				order_field: '<?php echo $order_field;?>',
				order_direction: '<?php echo $order_direction;?>',
				transaction_status: $(".transactionsStatus").val(),
				customer_filter: $(".select_transaction_customer").data("customer-id")
			}
			loadView("creditor_list", data);
		}
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});

    // Add new (old not fixed)
	$(".addNewButton").on('click', function(e){
		$.ajax({
			cache: false,
			type: 'POST',
			dataType: 'json',
			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_home";?>',
			data: { fwajax: 1, fw_nocss: 1, cid: 0 },
			success: function(obj){
				$('#popupeditboxcontent').html('');
				$('#popupeditboxcontent').html(obj.html);
				out_popup = $('#popupeditbox').bPopup(out_popup_options);
				$("#popupeditbox:not(.opened)").remove();
			}
		});
	});
});
</script>
