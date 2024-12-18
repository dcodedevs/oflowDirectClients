<?php
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'active'; }

require_once __DIR__ . '/functions.php';

$all_count = get_support_list_count('active', $company_product_set_id);

$s_sql = "SELECT * FROM ownercompany_accountconfig";
$o_query = $o_main->db->query($s_sql);
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM company_product_set ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
$company_product_sets = $o_query ? $o_query->result_array() : array();

$default_label = $formText_Default_output;
if($ownercompany_accountconfig['default_set_name'] != "") {
    $default_label = $ownercompany_accountconfig['default_set_name'];
}

?>
<?php
    if($ownercompany_accountconfig['activate_company_product_sets'] && count($company_product_sets) > 0){
?>
<div class="article_set_wrapper">
    <label><?php echo $formText_CompanyProductSet_output;?>: </label>
    <select class="changeSet" autocomplete="off">
        <option value="-1"><?php echo $formText_ChooseSet_output;?></option>
        <option value="0" <?php if($company_product_set_id == 0) echo 'selected';?>><?php echo $default_label;?></option>
        <?php foreach($company_product_sets as $company_product_set) { ?>
            <option value="<?php echo $company_product_set['id'];?>" <?php if($company_product_set['id'] == $company_product_set_id) echo 'selected';?>><?php echo $company_product_set['name'];?></option>
        <?php } ?>
    </select>
</div>
<?php } ?>
<div class="output-filter">
    <ul>
        <li class="item<?php echo ($list_filter == 'active' ? ' active':'');?>">
            <a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=active"; ?>">
                <span class="link_wrapper">
                    <span class="count"><?php echo $all_count; ?></span>
                    <?php echo $formText_ActiveArticles_output;?>
                </span>
            </a>
        </li>
        <?php
        if($article_accountconfig['activate_supplier_products']) {
            $s_sql = "SELECT * FROM article_supplier WHERE content_status < 2";
            $o_query = $o_main->db->query($s_sql);
            $supplier_count = $o_query ? $o_query->num_rows() : 0;
            ?>
            <li class="item<?php echo ($list_filter == 'supplier' ? ' active':'');?>">
                <a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&list_filter=supplier"; ?>">
                    <span class="link_wrapper">
                        <span class="count"><?php echo $supplier_count; ?></span>
                        <?php echo $formText_Suppliers_output;?>
                    </span>
                </a>
            </li>
            <?php
        }
        ?>

    </ul>
</div>

<?php
if(isset($_GET['list_filter'])) { $filter = $_GET['list_filter']; } else { $filter = "active"; }
?>

<div class="p_tableFilter">
    <div class="p_tableFilter_left">
        <?php if($list_filter != 'supplier') { ?>
            <?php if($article_accountconfig['activateArticlePriceMatrix']) {
                $s_sql = "SELECT * FROM articlepricematrix ORDER BY name ASC";
                $o_query = $o_main->db->query($s_sql);
                $articlePriceMatrixes = $o_query ? $o_query->result_array() : array();
            ?>
            <div class="inline">
                <label><?php echo $formText_SelectArticlePriceMatrix_output;?></label>
                <select class="articlePriceMatrix" autocomplete="off">
                    <option value=""><?php echo $formText_None_output;?></option>
                    <?php foreach($articlePriceMatrixes as $articlePriceMatrix) { ?>
                    <option value="<?php echo $articlePriceMatrix['id']?>" <?php if(isset($_GET['priceMatrix'])) if($articlePriceMatrix['id'] == $_GET['priceMatrix']) echo 'selected';?>><?php echo $articlePriceMatrix['name'];?></option>
                    <?php }?>
                </select>
            </div>
            <?php } ?>

             <?php if($article_accountconfig['activateArticleDiscountMatrix']) {
                $s_sql = "SELECT * FROM articlediscountmatrix ORDER BY name ASC";
                $o_query = $o_main->db->query($s_sql);
                $articleDiscountMatrixes = $o_query ? $o_query->result_array() : array();
            ?>
            <div class="inline">
                <label><?php echo $formText_SelectArticleDiscountMatrix_output;?></label>
                <select class="articleDiscountMatrix" autocomplete="off">
                    <option value=""><?php echo $formText_None_output;?></option>
                    <?php foreach($articleDiscountMatrixes as $articleDiscountMatrix) { ?>
                    <option value="<?php echo $articleDiscountMatrix['id']?>" <?php if(isset($_GET['discountMatrix'])) if($articleDiscountMatrix['id'] == $_GET['discountMatrix']) echo 'selected';?>><?php echo $articleDiscountMatrix['name'];?></option>
                    <?php }?>
                </select>
            </div>
            <?php } ?>
        <?php } else { ?>
            <a href="#" class="add_supplier"><?php echo $formText_AddSupplier_output;?></a>
        <?php } ?>
    </div>
    <div class="p_tableFilter_right">
        <form class="searchFilterForm" id="searchFilterForm">
            <input type="text" class="searchFilter" autocomplete="off" value="<?php if(isset($_GET['search_filter'])) echo $_GET['search_filter'];?>">
            <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
        </form>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $(".changeSet").off("change").on("change", function(e){
        e.preventDefault();
        var data = {
            building_filter: $('.buildingFilter').val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            priceMatrix: $('.articlePriceMatrix').val(),
            discountMatrix: $('.articleDiscountMatrix').val(),
            set_id: $(".changeSet").val()
        };
        loadView('list', data);
    })

	$(".add_supplier").unbind("click").on('click', function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('edit_supplier', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
	});
     // Filter by building
    $('.customerGroupFilter').on('change', function(e) {
        var data = {
            building_filter: $('.buildingFilter').val(),
            customergroup_filter: $(this).val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            priceMatrix: $('.articlePriceMatrix').val(),
            discountMatrix: $('.articleDiscountMatrix').val(),
            set_id: $(".changeSet").val()
        };
        loadView('list', data);
    });

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();
        var data = {
            building_filter: $('.buildingFilter').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            customergroup_filter: $(".customerGroupFilter").val(),
            priceMatrix: $('.articlePriceMatrix').val(),
            discountMatrix: $('.articleDiscountMatrix').val(),
            set_id: $(".changeSet").val()
        };
        loadView('list', data);
    });
    $(".articlePriceMatrix").on('change', function(e) {
        var data = {
            priceMatrix: $('.articlePriceMatrix').val(),
            discountMatrix: $('.articleDiscountMatrix').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            set_id: $(".changeSet").val()
        };
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
        loadView("list", data);
    });
    $(".articleDiscountMatrix").on('change', function(e) {
        var data = {
            priceMatrix: $('.articlePriceMatrix').val(),
            discountMatrix: $('.articleDiscountMatrix').val(),
            list_filter: '<?php echo $list_filter; ?>',
            search_filter: $('.searchFilter').val(),
            set_id: $(".changeSet").val()
        };
        // ajaxCall('list', data, function(json) {
        //     $('.p_pageContent').html(json.html);
        // });
        loadView("list", data);
    });
})
</script>
<style>
    .article_set_wrapper {
        text-align: right;
    }
</style>
