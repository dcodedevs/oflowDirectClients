<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
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

<?php if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'all'; } ?>
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
                var data = {
                    city_filter: '<?php echo $city_filter;?>',
                    list_filter: '<?php echo $list_filter;?>',
                    sublist_filter: '',
                    search_filter: $('.searchFilter').val(),
                    search_by: $(".searchBy").val(),
                    selfdefinedfield_filter: '<?php echo base64_encode(json_encode($selfdefinedfield_filter));?>',
                    activecontract_filter: '<?php echo $activecontract_filter;?>'
                };
				loadView("list", data);
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
</script>