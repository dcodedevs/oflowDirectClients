<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<?php
$sql = "SELECT * FROM people_sync_other_accounts ORDER BY sortnr";
$o_query = $o_main->db->query($sql);
$people_sync_other_accounts = $o_query ? $o_query->result_array() : array();
$syncedModule = false;
foreach($people_sync_other_accounts as $people_sync_other_account) {
	if($people_sync_other_account['accountname'] != "" && $people_sync_other_account['access_token'] != "") {

	}
}
if(count($people_sync_other_accounts) > 0){
	$syncedModule = true;
}
if($v_employee_accountconfig['duplicate_module']) {
	$externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
	$syncedModule = true;
}
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<?php if($syncedModule){?>
			<div class="p_tableInfoWrapper">
				<?php
				echo $formText_ThisModuleIsSyncedWithPeopleModuleIn_output;
				if($v_employee_accountconfig['duplicate_module']) {
					$externalApiAccount = $v_employee_accountconfig['masteraccount_url'];
					$externalApiAccountArray = explode("accounts/", $externalApiAccount);
					echo " <b>".str_replace("/", "", $externalApiAccountArray[1])."</b>.";
					echo " ".$formText_EditableOnlyIn_output." ".str_replace("/", "", $externalApiAccountArray[1]);
				} else {
					foreach($people_sync_other_accounts as $people_sync_other_account) {
						echo " <b>".$people_sync_other_account['accountname']."</b> ";
					}
					echo ". ".$formText_EditableOnlyIn_output." ".$formText_thisAccount_output;
				}
				?>
			</div>
		<?php } ?>
		<div class="p_content">
			<?php include(__DIR__."/list_filter.php"); ?>
			<div class="p_pageContent">
                <?php
				require __DIR__ . '/ajax.list.php';
				?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : 'all'; ?>
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


});
$(function(){
	// setTimeout(function(){
	// 	var currentCount = 0;
	// 	var data = {
	// 		department_filter: $('.filterDepartment').val(),
	// 		search_filter: $('.searchFilter').val(),
	// 		list_filter: '<?php echo $list_filter;?>',
	// 		page: 1,
	// 	};
	// 	ajaxCall('list', data, function(json) {
	// 		$('.p_pageContent').html(json.html);
	// 	}, true);
	// }, 50);
})


</script>
