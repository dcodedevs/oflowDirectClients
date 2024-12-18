
<div class="top_filter_wrapper">
    <div class="top_filter_column">
        <div class="addNewVatBtn btnStyle">
    		<div class="plusTextBox active">
    			<div class="plusBox addres"><div class="lineH"></div><div class="lineV"></div></div>
    			<div class="text"><?php
                echo $formText_AddNewBookaccount_Output;
                ?></div>
    		</div>
    		<div class="clear"></div>
    	</div>
    </div>
    <div class="top_filter_column">

    </div>
    <!-- <div class="top_filter_column editNotificationPeopleColumn">
        <span class="editNotificationPeople fas fa-cog"></span>
    </div> -->
    <div class="clear"></div>
</div>

<script type="text/javascript">
    $(".addNewVatBtn").on('click', function(e){
        e.preventDefault();
        var data = {
            caseId: 0
        };
        ajaxCall('editBookaccount', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
</script>
<style>
.addNewVatBtn {
    cursor: pointer;
    margin-left: 25px;
    display: inline-block;
    vertical-align: top;
}
.main-filter-wrapper ul {
    float: left;
}
.main-filter-wrapper .completed_task {
    float: right;
    padding: 10px 20px;
    font-size: 15px;
    cursor: pointer;
    border-bottom: 3px solid #FFF;
}
.main-filter-wrapper .completed_task.active {
    border-bottom: 3px solid #0095E4;
}
.unhandledCasesNotResponsible {
    padding: 5px 20px;
    float: left;
}
.top_filter_wrapper .editNotificationPeopleColumn {
    float: right !important;
    cursor: pointer;
    color: #46b2e2;
}
.selectFilterWrapper {
    margin-bottom: 10px;
}
.topFilterlink img {
    width: 20px;
}
.filteredWrapper {
    margin-top: 10px;
}
.filterLine {
    display: inline-block;
    vertical-align: middle;
    margin-right: 15px;
}
.p_tableFilter_left {
    max-width: 60%;
    float: left;
}
.p_tableFilter_right {
    float: right;
}
.filteredRow {
    margin-top: 5px;
    margin-right: 5px;
    float: left;
    border: 1px solid #23527c;
    padding: 2px 5px;
    border-radius: 3px;
}
.filteredRow .filteredLabel{
    float: left;
}
.filteredRow .filteredValue{
    float: left;
    margin-left: 3px;
}
.filteredRow .filterRemove {
    float: right;
    font-size: 10px;
    line-height: 14px;
    margin-left: 10px;
    padding: 0px 3px 1px;
    cursor: pointer;
    color: #23527c;
}
.top_filter_wrapper {
    background: #fff;
    padding: 10px 15px;
    margin-bottom: 10px;
}
.top_filter_wrapper .top_filter_column {
    float: left;
    margin-right: 10px;
}
.resetSearch {
    cursor: pointer;
    color: #46b2e2;
}
.output-filter .caseTypeFilterWrapper {
    float: right;
    margin-top: 10px;
    margin-right: 5px;
}
.output-filter ul {
    float: left;
}
</style>
