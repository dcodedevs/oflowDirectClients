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
				<?php
				$s_sql = "SELECT * FROM debtcollectionlatefee_main ORDER BY id ASC";
			    $o_query = $o_main->db->query($s_sql);
		        $late_fees = $o_query ? $o_query->result_array() : array();
				foreach($late_fees as $late_fee) {
					?>
					<div class="late_fee">
						<div class="late_fee_title">
							<?php
							echo $late_fee['internal_name']." - ".$late_fee['article_name'];
							?>
							<span class="glyphicon glyphicon-trash delete_latefee" data-lateid="<?php echo $late_fee['id'];?>"></span>
							<span class="glyphicon glyphicon-pencil edit_latefee" data-lateid="<?php echo $late_fee['id']?>"></span>
							<div class="clear"></div>
						</div>
						<div class="type_label"><?php echo $formText_ForCompany_output;?><span class="add_level" data-lateid="<?php echo $late_fee['id']?>" data-type="0">+ <?php echo $formText_Add_output;?></span></div>
						<div class="type_content">
							<?php
							$s_sql = "SELECT * FROM debtcollectionlatefee_amount WHERE debtcollectionlatefee_amount.debtcollectionlatefee_main_id = ?
							AND IFNULL(debtcollectionlatefee_amount.type, 0) = 0
							ORDER BY mainclaim_amount ASC";
						    $o_query = $o_main->db->query($s_sql, array($late_fee['id']));
					        $late_fee_levels = $o_query ? $o_query->result_array() : array();
							foreach($late_fee_levels as $late_fee_level){
								?>
								<div class="late_fee_amount">
									<?php echo $formText_MainClaim_output." ".number_format($late_fee_level['mainclaim_amount'], 2, ",", "")." - ".$formText_Amount_output." ".number_format($late_fee_level['amount'], 2, ",", "");?>
									<span class="glyphicon glyphicon-trash delete_level" data-levelid="<?php echo $late_fee_level['id'];?>"></span>
									<span class="glyphicon glyphicon-pencil add_level" data-lateid="<?php echo $late_fee['id']?>" data-type="0" data-levelid="<?php echo $late_fee_level['id'];?>"></span>
									<div class="clear"></div>
								</div>
								<?php
							}
							?>
						</div>
						<div class="type_label"><?php echo $formText_ForPerson_output;?><span class="add_level" data-lateid="<?php echo $late_fee['id']?>" data-type="1">+ <?php echo $formText_Add_output;?></span></div>
						<div class="type_content">
							<?php
							$s_sql = "SELECT * FROM debtcollectionlatefee_amount WHERE debtcollectionlatefee_amount.debtcollectionlatefee_main_id = ?
							AND IFNULL(debtcollectionlatefee_amount.type, 0) = 1
							ORDER BY mainclaim_amount ASC";
						    $o_query = $o_main->db->query($s_sql, array($late_fee['id']));
					        $late_fee_levels = $o_query ? $o_query->result_array() : array();
							foreach($late_fee_levels as $late_fee_level){
								?>
								<div class="late_fee_amount">
									<?php echo $formText_MainClaim_output." ".number_format($late_fee_level['mainclaim_amount'], 2, ",", "")." - ".$formText_Amount_output." ".number_format($late_fee_level['amount'], 2, ",", "");?>
									<span class="glyphicon glyphicon-trash delete_level" data-levelid="<?php echo $late_fee_level['id'];?>"></span>
									<span class="glyphicon glyphicon-pencil add_level" data-lateid="<?php echo $late_fee['id']?>" data-type="0" data-levelid="<?php echo $late_fee_level['id'];?>"></span>
									<div class="clear"></div>
								</div>
								<?php
							}
							?>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>
<style>
.late_fee {
	padding: 10px 10px;
	background: #fff;
	margin-bottom: 10px;
}
.late_fee .late_fee_title {
	font-weight: bold;
	font-size: 18px;
	margin-bottom: 5px;
}
.late_fee .type_label {
	font-weight: bold;
	margin-bottom: 5px;
}
.type_label .add_level {
	margin-left: 10px;
	font-weight: normal;
	cursor: pointer;
	color: #46b2e2;
	font-size: 11px;
}
.delete_level {
	float: right;
	cursor: pointer;
	color: #46b2e2;
}
.type_content {
	margin-bottom: 20px;
}
.late_fee_amount {
	padding: 5px;
	margin-bottom: 5px;
	border-bottom: 1px solid #cecece;
}
.late_fee_amount .add_level {
	float: right;
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
}
.delete_latefee {
	float: right;
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
	font-size: 12px;
	margin-top: 3px;
}
.edit_latefee {
	float: right;
	cursor: pointer;
	color: #46b2e2;
	margin-right: 10px;
	font-size: 12px;
	margin-top: 3px;
}
</style>
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
	$(".add_level").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			late_fee_id: $(this).data("lateid"),
			level_id: $(this).data("levelid"),
			type: $(this).data("type")
        };
        ajaxCall('edit_level', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	})
	$(".delete_level").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			level_id: $(this).data("levelid"),
			output_delete: 1
		};
		bootbox.confirm({
			message:'<?php echo $formText_DeleteLevel_output;?>',
			buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
			callback: function(result){
				if(result)
				{
					ajaxCall('edit_level', data, function(json) {
						loadView("list");
					});
				}
			}
		})
	})
	$(".edit_latefee").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			cid: $(this).data("lateid"),
		};
		ajaxCall('editLateFee', data, function(json) {
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	})

	$(".delete_latefee").off("click").on("click", function(e){
		e.preventDefault();
		var data = {
			cid: $(this).data("lateid"),
			output_delete: 1
		};
		bootbox.confirm({
			message:'<?php echo $formText_DeleteLateFee_output;?>',
			buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
			callback: function(result){
				if(result)
				{
					ajaxCall('editLateFee', data, function(json) {
						loadView("list");
					});
				}
			}
		})
	})
});
</script>
