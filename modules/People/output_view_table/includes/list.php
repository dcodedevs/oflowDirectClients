<?php
$page = 1;
if(!isset($_GET['filter'])) $_GET['filter'] = 'contactpersons';
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php require __DIR__ . ((isset($_GET['filter']) && 'keycards' == $_GET['filter']) ? '/ajax.list_keycards.php' : '/ajax.list.php'); ?>
			</div>
		</div>
	</div>
</div>
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
    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV'){
		 	fw_load_ajax($(this).data('href'),'',true);
		}
	});
});
</script>
<style>
	#fw_getynet {
		display: none;
	}
	#fw_account.alternative {
		max-width: 100% !important;
		min-height: auto !important;
		margin-top: 0 !important;
	}
	body.desktop #fw_account.alternative .fw_col.col0 {
		display: none !important;
	}
	#fw_account.alternative .fw_module_head_wrapper {
		display: none !important;
	}
	.p_headerLine {
		display: none;
	}
	.p_container {
		max-width: 100%;
	}
	body.desktop #fw_account.alternative .fw_col.col1 {
		width: 96% !important;
		margin: 0px 2% !important;
		left: 0 !important;
	}
	.p_container .p_containerInner {
		margin-top: 0px !important;
	}
	body.desktop #fw_account.alternative .fw_col.col1 {
		padding-bottom: 10px;
	}
	.show_fields {
		cursor: pointer;
		float: left;
		color: #46b2e2;
		margin-top: 17px;
		margin-left: 15px;
	}
	.exportBtn {
		display: inline-block;
		vertical-align: middle;
		margin-left: 15px;
		cursor: pointer;
		color: #fff;
		padding: 6px 17px;
		border-radius: 3px;
		float: left;
		margin-top: 10px;
	}
	.fieldChooseWrapper {
		position: absolute;
		width:100%;
		background: #fff;
		z-index: 10;
		max-height: 80%;
		overflow: auto;
	}
</style>
