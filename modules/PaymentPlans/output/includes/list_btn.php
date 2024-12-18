<?php
    require_once __DIR__ . '/functions.php';
    $main_filter = isset($_GET['main_filter']) ? $_GET['main_filter'] : "list";
    $people_contactperson_type = 2;
    $sql = "SELECT * FROM accountinfo_basisconfig ORDER BY id";
    $o_query = $o_main->db->query($sql);
    $accountinfo_basisconfig = $o_query ? $o_query->row_array() : array();
    if(intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']) > 0){
    	$people_contactperson_type = intval($accountinfo_basisconfig['contactperson_type_to_use_in_people']);
    }

?>
<div class="top_filter_wrapper">
    <div class="top_filter_column">
    	<div class="processPayments btnStyle">
    		<div class="plusTextBox active">
    			<div class="text"><?php
                echo $formText_ProcessPayment_Output;
                ?></div>
    		</div>
    		<div class="clear"></div>
    	</div>
    </div>

    <div class="top_filter_column">
    </div>
    <div class="clear"></div>
</div>
<script type="text/javascript">
    $(".addNewCaseBtn").on('click', function(e){
        e.preventDefault();
        var data = {
            caseId: 0
        };
        ajaxCall('editCase', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".addNewCustomerBtn").on('click', function(e){
        e.preventDefault();
        var data = {
            caseId: 0
        };
        ajaxCall('editTask', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".processPayments").on("click", function(e){
        e.preventDefault();
        var data = {
        };
        ajaxCall('processPlans', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        })
    })
</script>
<style>

.processPayments {
    cursor: pointer;
    margin-left: 15px;
    display: inline-block;
    vertical-align: top;
}
</style>
