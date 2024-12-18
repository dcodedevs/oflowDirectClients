<?php

$s_sql = "select * from customer_basisconfig";
$o_query = $o_main->db->query($s_sql);
$customer_basisconfig = $o_query ? $o_query->row_array() : array();

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$city_filter = $_SESSION['city_filter'] ? ($_SESSION['city_filter']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$selfdefinedfield_filter = $_SESSION['selfdefinedfield_filter'] ? $_SESSION['selfdefinedfield_filter'] : '';
$activecontract_filter = $_SESSION['activecontract_filter'] ? ($_SESSION['activecontract_filter']) : '';


?>
<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=filter";?>" method="post">
		<div class="inner">
            <div class="line articleLine">
                <div class="lineTitle"><?php echo $formText_City_Output; ?></div>
                <div class="lineInput">
                    <select name="city" class="city_filter">
                    	<option value=""><?php echo $formText_All_output;?></option>
                    	<?php
                    	$cities = array();
                    	$s_sql = "SELECT paCity FROM customer WHERE paCity <> '' GROUP BY paCity ORDER BY paCity ASC";
						$o_query = $o_main->db->query($s_sql);
						if($o_query && $o_query->num_rows()>0) {
							$cities = $o_query->result_array();
						}
                    	foreach($cities as $city){
                    	?>
	                        <option value="<?php echo $city['paCity']?>" <?php echo $city['paCity'] == $city_filter ? 'selected="selected"' : ''; ?>>
	                            <?php echo $city['paCity']; ?>
	                        </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <?php
            $selfdefinedFields = array();
        	$s_sql = "SELECT * FROM customer_selfdefined_fields ORDER BY name";
			$o_query = $o_main->db->query($s_sql);
			if($o_query && $o_query->num_rows()>0) {
				$selfdefinedFields = $o_query->result_array();
			}
            foreach($selfdefinedFields as $selfdefinedField) {
            	?>
	            <div class="line">
                	<div class="lineTitle"><?php echo $selfdefinedField['name']; ?></div>
                	<?php if($selfdefinedField['type'] == 0 ) {
						if($selfdefinedField['hide_textfield']){
							$s_sql = "SELECT customer_selfdefined_lists.* FROM customer_selfdefined_lists_connection
							LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_lists.id = customer_selfdefined_lists_connection.customer_selfdefined_list_id
							WHERE customer_selfdefined_field_id = ?";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedField['id']));
							$selfdefinedLists = $o_query ? $o_query->result_array() : array();
						} else {
							$selfdefinedLists = array();
						}
						?>
	                	<div class="lineInput">
							<?php
							if(count($selfdefinedLists) > 0){
								foreach($selfdefinedLists as $connection){
	                                $resources = array();
	                                $s_sql = "SELECT * FROM customer_selfdefined_list_lines WHERE list_id = ? ORDER BY name ASC";
	                                $o_query = $o_main->db->query($s_sql, array($connection['id']));
	                                if($o_query && $o_query->num_rows()>0){
	                                    $resources = $o_query->result_array();
	                                }

									?>
									<select name="selfdefinedfield<?php echo $selfdefinedField['id']?>" class="selfdefinedfield_filter" data-selfdefinedfield-id="<?php echo $selfdefinedField['id']?>">
										<option value=""><?php echo $formText_Select_output; ?></option>
										<?php foreach($resources as $resource) {
				                    		$checked = false;
				                    		if(isset($selfdefinedfield_filter[$selfdefinedField['id']])) {
				                    			$selfdefinedfieldBefore = $selfdefinedfield_filter[$selfdefinedField['id']];
				                    			$selfdefinedfieldBeforeArray = explode(",", $selfdefinedfieldBefore);
				                    			if(in_array($resource['id'], $selfdefinedfieldBeforeArray)) {
				                    				$checked = true;
				                    			}
				                    		}
										?>
											<option value="<?php echo $resource['id']; ?>" <?php if($checked) echo 'selected';?>><?php echo $resource['name']; ?></option>
										<?php
										}
										?>
									</select>
									<?php
								}
							} else {
								$checked = false;
								if(isset($selfdefinedfield_filter[$selfdefinedField['id']])) {
									$checked = true;
								}
								?>
								<div>
									<input type="checkbox" class="selfdefinedfield_filter" data-selfdefinedfield-id="<?php echo $selfdefinedField['id']?>"  name="selfdefinedfield<?php echo $selfdefinedField['id']?>" value="1" id="selfdefinedfield<?php echo $selfdefinedField['id']?>" <?php if($checked) echo 'checked'; ?>>
									<label for="selfdefinedfield<?php echo $selfdefinedField['id']?>"><?php echo $formText_checked_output;?></label>
								</div>
								<?php
							}
							?>

						</div>
                	<?php } else if($selfdefinedField['type'] == 1) { ?>
                		<select name="selfdefinedfield<?php echo $selfdefinedField['id']?>" class="selfdefinedfield_filter" data-selfdefinedfield-id="<?php echo $selfdefinedField['id']?>">
	                    	<option value=""><?php echo $formText_All_output;?></option>
	                    	<?php

	                    	$selfdefinedFieldValues = array();
				        	$s_sql = "SELECT * FROM customer_selfdefined_values WHERE selfdefined_fields_id = ? AND value <> '' GROUP BY value";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedField['id']));
							if($o_query && $o_query->num_rows()>0) {
								$selfdefinedFieldValues = $o_query->result_array();
							}
							foreach($selfdefinedFieldValues as $selfdefinedFieldValue){
	                    		$s_sql = "SELECT customer_selfdefined_list_lines.* FROM customer_selfdefined_list_lines LEFT OUTER JOIN customer_selfdefined_lists ON customer_selfdefined_list_lines.list_id = customer_selfdefined_lists.id WHERE customer_selfdefined_list_lines.id = ?";
								$o_query = $o_main->db->query($s_sql, array($selfdefinedFieldValue['value']));
								if($o_query && $o_query->num_rows()>0) {
									$listItem = $o_query->row_array();
								}
	                    		$checked = false;
	                    		if(isset($selfdefinedfield_filter[$selfdefinedField['id']])) {
	                    			$selfdefinedfieldBefore = $selfdefinedfield_filter[$selfdefinedField['id']];
	                    			$selfdefinedfieldBeforeArray = explode(",", $selfdefinedfieldBefore);
	                    			if(in_array($listItem['id'], $selfdefinedfieldBeforeArray)) {
	                    				$checked = true;
	                    			}
	                    		}
	                    	?>
		                        <option value="<?php echo $listItem['id']?>" <?php if($checked) echo 'selected';?>>
		                            <?php echo $listItem['name']?>
		                        </option>
	                        <?php } ?>
	                    </select>
                	<?php } else if ($selfdefinedField['type'] == 2) { ?>
                		<div class="lineInput">
	                    	<?php
                    		$selfdefinedFieldValues = array();
				        	$s_sql = "SELECT customer_selfdefined_values_connection.*, customer_selfdefined_list_lines.name FROM customer_selfdefined_values
	                    		LEFT OUTER JOIN customer_selfdefined_values_connection ON customer_selfdefined_values.id = customer_selfdefined_values_connection.selfdefined_value_id
	                    		LEFT OUTER JOIN customer_selfdefined_list_lines ON customer_selfdefined_list_lines.id = customer_selfdefined_values_connection.selfdefined_list_line_id
	                    		WHERE customer_selfdefined_values.selfdefined_fields_id = ?
	                    		AND customer_selfdefined_list_lines.id is not null
	                    		GROUP BY customer_selfdefined_values_connection.selfdefined_list_line_id";
							$o_query = $o_main->db->query($s_sql, array($selfdefinedField['id']));
							if($o_query && $o_query->num_rows()>0) {
								$selfdefinedFieldValues = $o_query->result_array();
							}
							foreach($selfdefinedFieldValues as $selfdefinedFieldValue){
	                    		$checked = false;
	                    		if(isset($selfdefinedfield_filter[$selfdefinedField['id']])) {
	                    			$selfdefinedfieldBefore = $selfdefinedfield_filter[$selfdefinedField['id']];
	                    			$selfdefinedfieldBeforeArray = explode(",", $selfdefinedfieldBefore);
	                    			if(in_array($selfdefinedFieldValue['selfdefined_list_line_id'], $selfdefinedfieldBeforeArray)) {
	                    				$checked = true;
	                    			}
	                    		}
	                    		?>
	                    		<div>
		                    		<input type="checkbox" class="selfdefinedfield_filter" data-selfdefinedfield-id="<?php echo $selfdefinedField['id']?>"  name="selfdefinedfield<?php echo $selfdefinedField['id']?>" value="<?php echo $selfdefinedFieldValue['selfdefined_list_line_id']?>" id="selfdefinedfield<?php echo $selfdefinedField['id']?><?php echo $selfdefinedFieldValue['selfdefined_value_id']?>" <?php if($checked) echo 'checked';?>>
		                    		<label for="selfdefinedfield<?php echo $selfdefinedField['id']?><?php echo $selfdefinedFieldValue['selfdefined_value_id']?>"><?php echo $selfdefinedFieldValue['name'];?></label>
	                    		</div>
	                    		<?php
	                    	}
	                    	?>
	                	</div>
                	<?php } ?>
                	<div class="clear"></div>
	            </div>
            	<?php
            }
            ?>
        </div>


		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Filter_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
    $("form.output-form").validate({
        submitHandler: function(form) {

            out_popup.close();
            var selfdefinedfield_filter = {};
            $(".selfdefinedfield_filter").each(function(){
            	var id = $(this).data("selfdefinedfield-id");
            	if(($(this).is(":checkbox") && $(this).is(":checked")) || !$(this).is(":checkbox")){
            		if(selfdefinedfield_filter[id] == undefined) {
            			selfdefinedfield_filter[id] = "";
            		}
        			selfdefinedfield_filter[id] += $(this).val()+",";

            	}
            })
        	var data = {
	            city_filter: $(".city_filter").val(),
	            list_filter: '<?php echo $list_filter;?>',
            	search_filter: '<?php echo $search_filter;?>',
	            selfdefinedfield_filter: selfdefinedfield_filter,
	            activecontract_filter: $(".activecontract_filter").val()
	        };
	        ajaxCall('list', data, function(json) {
	            $('.p_pageContent').html(json.html);
	        });
        },
        invalidHandler: function(event, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var message = errors == 1
                ? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
                : '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';

                $("#popup-validate-message").html(message);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
            } else {
                $("#popup-validate-message").hide();
            }
            setTimeout(function(){ $('#popupeditbox').height(''); }, 200);
        }
    });
    // $('.output-form').on('submit', function(e) {
    //     e.preventDefault();
    //     var data = {};
    //     $(this).serializeArray().forEach(function(item, index) {
    //         data[item.name] = item.value;
    //     });
    //     ajaxCall('editOrder', data, function (json) {
    //         if (json.redirect_url) document.location.href = json.redirect_url;
    //         else out_popup.close();
    //     });
    // });

    function h(e) {
        $(e).css({'height':'auto','overflow-y':'hidden'}).height(e.scrollHeight);
    }
    $('.autoheight').each(function () {
        h(this);
    }).on('input', function () {
        h(this);
    });
});

</script>
<style>
.popupform, .popupeditform {
	width:100%;
	margin:0 auto;
	border:1px solid #e8e8e8;
	position:relative;
}
label.error { display: none !important; }
.popupform .popupforminput.error { border-color:#c11 !important;}
#popup-validate-message, .error-msg { font-weight:bold; color:#c11; padding-bottom:10px; }
/* css for timepicker */
.ui-timepicker-div .ui-widget-header { margin-bottom: 8px; }
.ui-timepicker-div dl { text-align: left; }
.ui-timepicker-div dl dt { height: 25px; margin-bottom: -25px; }
.ui-timepicker-div dl dd { margin: 0 10px 10px 65px; }
.ui-timepicker-div td { font-size: 90%; }
.ui-tpicker-grid-label { background: none; border: none; margin: 0; padding: 0; }
.clear {
	clear:both;
}
.inner {
	padding:10px;
}
.pplineV {
	position:absolute;
	top:0;bottom:0;left:70%;
	border-left:1px solid #e8e8e8;
}
.popupform input.popupforminput, .popupform textarea.popupforminput, .popupform select.popupforminput, .col-md-8z input {
	width:100%;
	border-radius: 4px;
	padding:5px 10px;
	font-size:12px;
	line-height:17px;
	color:#3c3c3f;
	background-color:transparent;
	-webkit-box-sizing: border-box;
	   -moz-box-sizing: border-box;
		 -o-box-sizing: border-box;
			box-sizing: border-box;
	font-weight:400;
	border: 1px solid #cccccc;
}
.popupformname {
	font-size:12px;
	font-weight:bold;
	padding:5px 0px;
}
.popupforminput.botspace {
	margin-bottom:10px;
}
textarea {
	min-height:50px;
	max-width:100%;
	min-width:100%;
	width:100%;
}
.popupformname {
	font-weight: 700;
	font-size: 13px;
}
.popupformbtn {
	text-align:right;
	margin:10px;
}
.popupformbtn input {
	border-radius:4px;
	border:1px solid #0393ff;
	background-color:#0393ff;
	font-size:13px;
	line-height:0px;
	padding: 20px 35px;
	font-weight:700;
	color:#FFF;
	margin-left:10px;
}
.error {
	border: 1px solid #c11;
}
.popupform .lineTitle {
	font-weight:700;
}
.popupform .line .lineTitle {
	width:30%;
	float:left;
	font-weight:700;
	padding:5px 0;
}

.popupform .line .lineTitleWithSeperator {
    width:100%;
    margin: 20px 0;
    padding:0 0 10px;
    border-bottom:1px solid #EEE;
}

.popupform .line .lineInput {
	width:70%;
	float:left;
}
.popupform .line .lineInput select {
    max-width: 100%;
}
.priceTotalLine .popupforminput {
    border: none !important;
}
.popupform input.popupforminput.checkbox {
    width: auto;
}
</style>
