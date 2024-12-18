<?php
// Create & check folders
// $f_check_sql = "SELECT * FROM customer ORDER BY name";
// require_once __DIR__ . '/filearchive_functions.php';
// check_filearchive_folder('Kunder', $f_check_sql, 'customer', 'name');
// create_subscription_folders();
$company_product_set_id = isset($_GET['set_id']) ? $_GET['set_id'] : -1;
$page = 1;
require_once __DIR__ . '/list_btn.php';

$sql = "SELECT * FROM accountinfo";
$result = $o_main->db->query($sql);
$v_accountinfo = $result ? $result->row_array(): array();

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
	follow: [true, false],
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
				var data = {
					set_id: '<?php echo $company_product_set_id;?>'
				}
            	loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {
	<?php
	if($updatedCount > 0){
		?>
		fw_info_message_empty();
		fw_info_message_add("dialog", "<?php echo $updatedCount." " .$formText_ProjectsCalendarWereUpdated_output;?>");
		fw_info_message_show();
		<?php
	}
	if(count($automaticUpdateError) > 0){
		$updateIds = "";
		foreach($automaticUpdateError as $orderId) { $updateIds .= $orderId.", ";}
		$updateIds = substr($updateIds, 0, -2);
		?>
		fw_info_message_add("error", "<?php echo $formText_ProjectWithName_output.' ('.$updateIds.') '.$formText_WereNotUpdatedDueToErrorsInTheirSettings_output;?>");
		fw_info_message_show();
		<?php
	}
	?>

    var page = '<?php echo $page?>';
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
			if($("body.alternative").length == 0) {
			 	if($(this).parents(".tinyScrollbar.col1")){
				 	var $scrollbar6 = $('.tinyScrollbar.col1');
				    $scrollbar6.tinyscrollbar();

				    var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
			        scrollbar6.update(0);
			    }
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
<style>
	.article_set_wrapper {
		text-align: right;
		margin-bottom: 10px;
	}
</style>
