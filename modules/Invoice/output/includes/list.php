<?php
if(isset($_GET['ehf']))
{
	if(1==$_COOKIE['ehf_fail_check'])
	{echo 'unset';
		unset($_COOKIE['ehf_fail_check'], $_GET['fail_check']);
		setcookie('ehf_fail_check', NULL, -1, '/', '.getynet.com', TRUE, TRUE);
	} else {echo 'set';
		unset($_GET['fail_check']);
		$_COOKIE['ehf_fail_check'] = 1;
		setcookie('ehf_fail_check', 1, time()+60*60*24*365, '/', '.getynet.com', TRUE, TRUE);
	}
}
require_once __DIR__ .'/functions.php';
require_once __DIR__ . '/list_btn.php';

$ownercompanies = get_ownercompanies($o_main);
$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list";
?>
<script type="text/javascript">
function output_reload_page()
{
    fw_load_ajax('<?php echo $s_page_reload_url;?>', '', false);
}
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
        $(this).removeClass('opened');
        if($(this).is('.close-reload')) output_reload_page();
    }
};
function bindSend(){
    $(".sendInvoice").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('sendInvoice', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".send-ehf").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('send_ehf', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
	$(".recreate-ehf").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('recreate_ehf', data, function(json) {
            output_reload_page();
        });
    });
	$(".recreate-pdf").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('recreate_pdf', data, function(json) {
            output_reload_page();
        });
    });
	$(".edit-project-code").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('edit_project_code', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

	$(".edit-department-code").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoiceId: $(this).data('invoice-id')
        };
        ajaxCall('edit_department_code', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });

	$(".show-sending-log").unbind("click").bind("click", function(e){
        e.preventDefault();
        var data = {
            invoice_id: $(this).data('invoice-id')
        };
        ajaxCall('get_sending_log', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
}
</script>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <?php foreach ($ownercompanies['list'] as $company): ?>
                    <?php $invoices = get_invoices($o_main, array(
                        'company_filter' => $company['id'],
                        'page' => 1,
                        'per_page' => 10
                    ));
                    ?>

                    <div class="ownercompany_invoices" data-ownercompany-id="<?php echo $company['id']; ?>" data-page="<?php echo $invoices['pagination']['page']; ?>" data-total-pages="<?php echo $invoices['pagination']['total_pages']; ?>" data-per-page="<?php echo $invoices['pagination']['per_page']; ?>">
                        <div class="ownercompany_invoices_header">
                            <div class="ownercompany_invoices_header_title">
                                <h4><?php echo $company['name']; ?></h4>
                            </div>
                            <div class="ownercompany_invoices_header_search">
                                <form class="searchFilterForm">
                                    <input type="hidden" name="company_filter" value="<?php echo $company['id']; ?>">
                                    <input type="text" class="searchFilter" name="search_filter" value="">
                                    <button id="p_tableFilterSearchBtn"><?php echo $formText_Search_output; ?></button>
                                </form>
                            </div>
                        </div>

                        <div class="ownercompany_invoices_content">
                            <?php  showListHtml($invoices, 'full', $invoice_accountconfig['activate_global_export']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<?php
if(isset($_GET['list_filter'])){ $list_filter = $_GET['list_filter']; } else { $list_filter = 'active'; }

?>
<script type="text/javascript">
$(document).ready(function() {
    bindSend();
    $(".ownercompany_invoices").on("click", '.invoiceShowNext', function(e){
        e.preventDefault();

        var parentBlock = $(this).closest('.ownercompany_invoices');
        var page = parentBlock.data('page');
        var totalPages = parentBlock.data('total-pages');
        var perPage = parentBlock.data('per-page');
        var companyId = parentBlock.data('ownercompany-id');

        var search_filter = parentBlock.find('[name="search_filter"]').val();

        page++;
        parentBlock.data('page', page);

        if(page <= totalPages){
            var data = {
                page: page,
                company_filter: companyId,
                search_filter: search_filter
            };

            var __data = {
                fwajax: 1,
                fw_nocss: 1
            }

            $('#fw_loading').show();
            // Concat default and user data
            var ajaxData = $.extend({}, __data, data);
            $.ajax({
                cache: false,
                type: 'POST',
                dataType: 'json',
                url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=list"; ?>',
                data: ajaxData,
                success: function(json){
                    $('#fw_loading').hide();
                    parentBlock.find("#gtable_search").append(json.html);
                    var visibleCount = parentBlock.find("#gtable_search .gtable_row").length - 1;
                    if (visibleCount > json.invoices.invoice_count) {
                        visibleCount = json.invoices.invoice_count;
                    }
                    parentBlock.find(".invoicePaginationRow .current").html(visibleCount);
                    if(page == totalPages) {
                        parentBlock.find(".invoicePaginationRow .invoiceShowNext").hide();
                    }
                    bindSend();
                }
            });
        }
    })

    // Filter by customer name
    $('.searchFilterForm').on('submit', function(e) {
        e.preventDefault();

        var parentBlock = $(this).closest('.ownercompany_invoices');

        var formData = {};
        var serialized = $(this).serializeArray();
        serialized.forEach(function(value) {
            formData[value.name] = value.value;
        });

        formData.htmlReturn = 'full';

        ajaxCall('list', formData, function(response) {
            parentBlock.find(".ownercompany_invoices_content").html(response.html);

            parentBlock.data('total-pages', response.invoices.pagination.total_pages);
            parentBlock.data('page', response.invoices.pagination.page);
            parentBlock.data('per_page', response.invoices.pagination.per_page);
        });
    });
});


</script>
<style>
.invoicePaginationRow {
    padding: 15px 10px;
}

.ownercompany_invoices {
    margin-bottom:30px;
}
.ownercompany_invoices_header::after {
    clear:both;
    display:block;
    content:" ";
}

.ownercompany_invoices_header_title {
    float:left;
    width:70%;
}

.ownercompany_invoices_header_search {
    float:left;
    width:30%;
    text-align:right;
}
.hoverEye {
	position: relative;
	color: #0284C9;
	float: right;
	margin-top: 2px;
}
.hoverEye.failed {
	color:#a94442;
}
.hoverEye .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:450px;
	display: none;
	color: #000;
	position: absolute;
	right: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEye:hover .hoverInfo {
	display: block;
}
.hoverEyeCreated {
	position: relative;
	color: #cecece;
	float: left;
	margin-top: 2px;
}
.hoverEyeCreated .hoverInfo {
	font-family: 'PT Sans', sans-serif;
	width:250px;
	display: none;
	color: #000;
	position: absolute;
	left: 0%;
	top: 100%;
	padding: 5px 10px;
	background: #fff;
	border: 1px solid #ccc;
	z-index: 1;
}
.hoverEyeCreated:hover .hoverInfo {
	display: block;
}
</style>
