<?php
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

<?php $list_filter = $_GET['list_filter'] ? $_GET['list_filter'] : 0; ?>
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
			// var redirectUrl = $(this).data("redirect");
			// var data = {
			// 	mainlist_filter: "<?php echo $mainlist_filter;?>",
			// 	list_filter: "<?php echo $list_filter;?>",
			// 	sublist_filter: "<?php echo $sublist_filter;?>"
			// }
			// loadView("list", data);
          // window.location.reload();
		  reloadPage();
        }
		$(this).removeClass('opened');
	}
};

function reloadPage(){
	var dateFrom = $(".dateFrom").val();
	var dateTo = $(".dateTo").val();
	var creditorId = $("#creditorIdFilter").val();
	var objection_status = $(".objection_status").val();

	var link = '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&mainlist_filter=".$mainlist_filter."&list_filter=".$list_filter?>';
	if(objection_status != undefined){
		link += '&objection_status='+objection_status;
	}
	if(dateFrom != undefined){
		link += '&dateFrom='+dateFrom;
	}
	if(dateTo != undefined){
		link += '&dateTo='+dateTo;
	}
	if(creditorId != undefined){
		link += '&creditor_id_filter='+creditorId;
	}
	fw_load_ajax(link,'',true);  
}

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