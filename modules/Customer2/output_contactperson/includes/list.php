<?php

$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<?php include(__DIR__."/list_filter.php"); ?>
			<div class="p_pageContent">
                <?php
				// require __DIR__ . '/ajax.list.php';
				?>
			</div>
		</div>
	</div>
</div>

<?php $list_filter = $_GET['list_filter'] ? ($_GET['list_filter']) : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed: 300,
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
	setTimeout(function(){
		var currentCount = 0;
		var data = {
			department_filter: $('.filterDepartment').val(),
			search_filter: $('.searchFilter').val(),
			list_filter: '<?php echo $list_filter;?>',
			page: 1,
		};
		ajaxCall('list', data, function(json) {
			$('.p_pageContent').html(json.html);
		}, true);
	}, 50);
})


</script>
