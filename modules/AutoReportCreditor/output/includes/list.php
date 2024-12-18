<?php
// Create & check folders
// $f_check_sql = "SELECT * FROM customer ORDER BY name";
// require_once __DIR__ . '/filearchive_functions.php';
// check_filearchive_folder('Kunder', $f_check_sql, 'customer', 'name');
// create_subscription_folders();
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
				<table class="table">
					<tr>
						<th><?php echo $formText_Id_output;?></th>
						<th><?php echo $formText_Name_output;?></th>
						<th><?php echo $formText_CreditorId_output;?></th>
						<th><?php echo $formText_ScriptUrl_output;?></th>
						<th></th>
					</tr>
					<?php
					$s_sql = "SELECT * FROM autoreportcreditor ORDER BY name";
				    $o_query = $o_main->db->query($s_sql);
					$autoreportcreditors = $o_query ? $o_query->result_array() : array();

					foreach($autoreportcreditors as $autoreportcreditor) {
						$detailPageLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=reports&cid=".$autoreportcreditor['id'];
						?>
						<tr>
							<td><?php echo $autoreportcreditor['id'];?></td>
							<td><?php echo $autoreportcreditor['name'];?></td>
							<td><?php echo $autoreportcreditor['creditorId'];?></td>
							<td><?php echo $autoreportcreditor['scriptUrl'];?></td>
							<td>
								<a class="show_details optimize" href="<?php echo $detailPageLink;?>"><?php echo $formText_Reports_output;?></a>
								<span class="glyphicon glyphicon-pencil edit_autoreport" data-id="<?php echo $autoreportcreditor['id'];?>"></span>
								<span class="glyphicon glyphicon-trash delete_autoreport" data-id="<?php echo $autoreportcreditor['id'];?>"></span>
							</td>
						</tr>
						<?php
					}
					?>
				</table>
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
            	loadView("list");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};


$(document).ready(function() {
	$(".edit_autoreport").off("click").on("click", function(){
		var data = { cid: $(this).data("id")};
		ajaxCall('add_autoreport', data, function(obj) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
	$(".run_script").off("click").on("click", function(){
		var data = { cid: $(this).data("id")};
		ajaxCall('run_script', data, function(obj) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})
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
.run_script {
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
.show_details {
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
</style>
