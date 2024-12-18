<?php
// $organization_numbers = array("979378726", "889653132");
// $organization_numbers = 897737442;
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_HEADER, 0);
// curl_setopt($ch, CURLOPT_VERBOSE, 0);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
// curl_setopt($ch, CURLOPT_POST, TRUE);
// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
// curl_setopt($ch, CURLOPT_URL, 'http://ap_api.getynet.com/brreg.php');
// $v_post = array(
// 	'organisation_no' => $organization_numbers,
// 	'token' => 'RySBvCv3i9u6tP4mEd177X2gspGP6Rw0E512874043zDfUyHDsNF70gZvJ2R9s6idmGxk29amaRRR1R4Hbvqy93LJzPCz9oy',
// 	'password' => '_9^JAj|7_atz~-Y.BndXBguO9_jr0^z~~09m-*:4SXlj|!KZ6Xd.hnJe3WM75H9Vy=;ZIcrab-84WLKN+4Kdz~+xM5U%ePIY'
// );
// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($v_post));
// $s_response = curl_exec($ch);

// $v_items = array();
// $v_response = json_decode($s_response, TRUE);
// var_dump($v_response);
// if(isset($v_response['status']) && $v_response['status'] == 1 && $v_response['items'])
// {
// 	foreach($v_response['items'] as $v_item) {
// 		$s_person_sql = "";
// 		if(mb_strtolower($v_item['organisasjonsform']) == mb_strtolower("ENK")){
// 			// $s_person_sql = ", customer_type_for_collecting_cases = 2";
// 		}
// 		// $sql = "UPDATE customer SET updatedBy = 'import', updated=NOW(), organization_type = ?".$s_person_sql." WHERE publicRegisterId = ?";
// 		// $o_query = $o_main->db->query($sql, array($v_response['organisasjonsform'], $v_response['orgnr']));
		
// 	}
// }
/*
if($variables->loggID=="byamba@dcode.no"){
	$options = array ('trace' => true , 'encoding'=>' UTF-8');
	$authentication = new SoapClient ( "https://api.24sevenoffice.com/authenticate/v001/authenticate.asmx?wsdl", $options );
	$clientId = "376265048980711";
	$login = true;
	if($clientId > 0){
		$params ["token"]["Id"] = "ae934fe3-f478-4382-84d3-f676be6abc79";
        $params ["token"]["ApplicationId"] = "77cf551c-3a8b-499c-9526-27facb86a9c5";
		$passport = $authentication->AuthenticateByToken($params);
		?>
		<pre>
		<?php
		var_dump($passport);
		?></pre><?php

	}
}*/
$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php require __DIR__ . '/ajax.list.php'; ?>
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
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){console.log(e.target);
		if($(e.target).hasClass("reset_transactions") || $(e.target).hasClass("resync_open_transactions")){
			e.preventDefault();
		} else {
			if(e.target.nodeName == 'DIV'){
			 	fw_load_ajax($(this).data('href'),'',true);
			}
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
