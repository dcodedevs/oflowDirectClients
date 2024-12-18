<?php
$page = 1;
require_once __DIR__ . '/list_btn.php';
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                    <?php

                    $s_sql = "SELECT GROUP_CONCAT(id) ids, CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) name, COUNT(*) c
                    FROM customer WHERE content_status < 2
                    GROUP BY CONCAT(COALESCE(TRIM(name),''), '', COALESCE(TRIM(middlename), ''), '', COALESCE(TRIM(lastname), '')) HAVING c > 1";
                    $o_query = $o_main->db->query($s_sql);
                    $duplicatesByName = $o_query ? $o_query->result_array() : array();

                    $s_sql = "SELECT GROUP_CONCAT(id) ids, publicRegisterId, COUNT(*) c
                    FROM customer WHERE content_status < 2
                    GROUP BY TRIM(publicRegisterId) HAVING c > 1";
                    $o_query = $o_main->db->query($s_sql);
                    $duplicatesByRegisterId = $o_query ? $o_query->result_array() : array();


                    foreach($duplicatesByName as $duplicates) {
                        $customerIds = explode(",",$duplicates['ids']);
                        ?>
                        <div class="duplicate_row">
                            <div class="duplicate_title"><?php echo $formText_DuplicatesByName_output.": ".$duplicates['name'] ." (".count($customerIds).")";?></div>
                            <div class="gtable">
                                <div class="gtable_row">
                                    <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Name_output;?></div>
                                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_Street_output;?></div>
                                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_City_output;?></div>
                                </div>
                                <?php
                                foreach($customerIds as $customerId){
                                    $o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($customerId));
                                    $customer = $o_query ? $o_query->row_array() : array();
                                    $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customer['id'];
                                    ?>
                                        <div class="gtable_row output-click-helper"  data-href="<?php echo $s_edit_link;?>">
                                            <div class="gtable_cell c1"><?php echo $customer['name'];?></div>
                        					<div class="gtable_cell"><?php echo $customer['paStreet'];?></div>
                        					<div class="gtable_cell"><?php echo $customer['paCity'];?></div>
                                        </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }

                    foreach($duplicatesByRegisterId as $duplicates) {
                        $customerIds = explode(",",$duplicates['ids']);
                        ?>
                        <div class="duplicate_row">
                            <div class="duplicate_title"><?php echo $formText_DuplicatesByRegisterId_output.": ".$duplicates['publicRegisterId'] ." (".count($customerIds).")";?></div>
                            <div class="gtable">
                                <div class="gtable_row">
                                    <div class="gtable_cell gtable_cell_head c1"><?php echo $formText_Name_output;?></div>
                                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_Street_output;?></div>
                                    <div class="gtable_cell gtable_cell_head"><?php echo $formText_City_output;?></div>
                                </div>
                                <?php
                                foreach($customerIds as $customerId){
                                    $o_query = $o_main->db->query("SELECT * FROM customer WHERE id = ?", array($customerId));
                                    $customer = $o_query ? $o_query->row_array() : array();
                                    $s_edit_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$customer['id'];
                                    ?>

                                        <div class="gtable_row output-click-helper" data-href="<?php echo $s_edit_link;?>">
                                            <div class="gtable_cell c1"><?php echo $customer['name'];?></div>
                        					<div class="gtable_cell"><?php echo $customer['paStreet'];?></div>
                        					<div class="gtable_cell"><?php echo $customer['paCity'];?></div>
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
.duplicate_row {
    padding: 0px 0px 10px;
}
.duplicate_row .duplicate_title {
    margin-bottom: 5px;
    font-weight: bold;
    font-size: 14px;

}
</style>
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
            	loadView("duplicate_search");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};
$(document).off('click', '.output-click-helper').on('click', '.output-click-helper', function(e){
	if(e.target.nodeName == 'DIV'){
		<?php if($b_selection_mode && $totalPagesFiltered == 1) { ?>
		$(this).closest('.gtable_row').find('.selection-switch-btn').trigger('click');
		<?php } else { ?>
		fw_load_ajax($(this).data('href'),'',true);
		if($("body.alternative").length == 0) {
			if($(this).parents(".tinyScrollbar.col1")){
				var $scrollbar6 = $('.tinyScrollbar.col1');
				$scrollbar6.tinyscrollbar();

				var scrollbar6 = $scrollbar6.data("plugin_tinyscrollbar");
				scrollbar6.update(0);
			}
		}
		<?php } ?>
	}
});
</script>
