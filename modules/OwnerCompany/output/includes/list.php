<?php
//ini_set("display_errors", 1);
require_once __DIR__ .'/functions.php';
require_once __DIR__ . '/list_btn.php';
$list_filter = isset($_GET['list_filter']) ? $o_main->db->escape_like_str($_GET['list_filter']) : 'active';
$search_filter = isset($_GET['search_filter']) ? $o_main->db->escape_like_str($_GET['search_filter']) : '';
$customerList = get_ownercompany_list($o_main, $list_filter, $search_filter);
?>

<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<?php include(__DIR__."/list_filter.php"); ?>
			<div class="p_pageContent">
                <?php if(!count($customerList)): ?>
                    <div class="gtable_message"><?php echo $formText_NoResults_output; ?></div>

                <?php else: ?>

                    <div class="gtable" id="gtable_search">
                        <div class="gtable_row">
                            <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_OwnerCompanyName_output;?></div>
                        </div>
                        <?php
                        foreach($customerList as $v_row)
                        {
                            $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$v_row->id;
                            ?>
                            <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
                                <div class="gtable_cell c1"><?php echo $v_row->name;?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php endif; ?>

			</div>
		</div>
	</div>
</div>

<?php
$list_filter = isset($_GET['list_filter']) ? $o_main->db->escape($_GET['list_filter']) : 'active';

?>
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
        $(this).removeClass('opened');
        if($(this).is('.close-reload')) {
            var redirectUrl = $(this).data("redirect");
            if(redirectUrl != "" && redirectUrl != undefined){
                document.location.href = redirectUrl;
            } else {
                loadView("list");
            }
          // window.location.reload();
        }
    }
};

$(document).ready(function() {

    // On customer row click
	$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
		if(e.target.nodeName == 'DIV') fw_load_ajax($(this).data('href'),'',true);
	});

    // Filter by building
    $('.buildingFilter').on('change', function(e) {
        var data = {
            building_filter: $(this).val(),
            category_filter: $('.categoryFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: '',
        };
        loadView('list', data);
    });
    $('.categoryFilter').on('change', function(e) {
        var data = {
            building_filter: $('.buildingFilter').val(),
            category_filter: $(this).val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: '',
        };
        loadView('list', data);
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            building_filter: $('.buildingFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val()
        };
        loadView('list', data);
    });

});


</script>
