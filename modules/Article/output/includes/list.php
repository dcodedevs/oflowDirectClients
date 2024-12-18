<?php

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$company_product_set_id = isset($_GET['set_id']) ? $_GET['set_id'] : -1;
if(count($company_product_sets) == 0){
	$company_product_set_id = 0;
}
// ini_set("display_errors", 1);
//include("file_with_errors.php");
$page = 1;
require_once __DIR__ . '/list_btn.php';
?>

<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<?php
			//check if articles with wrong set was added
			$s_sql = "SELECT * FROM article WHERE company_product_set_id = -1";
			$o_query = $o_main->db->query($s_sql);
			$articlesWithErrors = $o_query ? $o_query->result_array() : array();

			if(count($articlesWithErrors) > 0) {
				echo '<div class="fix_articles">'.$formText_ThereAreArticlesThatAreSavedWronglyClickHereToFixTheData_output."</div>";
			}
			include(__DIR__."/list_filter.php"); ?>
			<div class="p_pageContent">
                <?php require __DIR__ . '/ajax.list.php'; ?>
			</div>
		</div>
	</div>
</div>
<style>
	.fix_articles {
		cursor: pointer;
		color: #46b2e2;
	}
</style>

<?php if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'all'; } ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	fadeSpeed: 0,
	followSpeed: 200,
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
				fw_load_ajax(redirectUrl, '', true);
			} else {
				var data = {
		            building_filter: $('.buildingFilter').val(),
		            list_filter: '<?php echo $list_filter; ?>',
		            search_filter: $('.searchFilter').val(),
		            priceMatrix: $('.articlePriceMatrix').val(),
		            discountMatrix: $('.articleDiscountMatrix').val(),
					set_id: '<?php echo $company_product_set_id;?>'
		        };
            	loadView("list", data);
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
		if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
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
	$(".fix_articles").off("click").on("click", function(e){
	   e.preventDefault();
	   var data = {
		  	fix_articles: 1
	   };
	   ajaxCall('editArticle', data, function(json) {
		   loadView("list", []);
	   });
	})

});


</script>
