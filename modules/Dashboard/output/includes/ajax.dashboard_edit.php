<?php

// Store the cipher method
$ciphering = "AES-128-CTR";

// Use OpenSSl Encryption method
$iv_length = openssl_cipher_iv_length($ciphering);
$options = 0;
// Non-NULL Initialization Vector for encryption
$encryption_iv = $decryption_iv = '1234567891011121';
// Store the encryption key
$encryption_key = $decryption_key = "spVwEow4QO";

if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {

		$page1_full = explode(",", $_POST['page1_full']);
		$page2_full = explode(",", $_POST['page2_full']);
        $page1_sorted = explode(",", $_POST['page1_sorted']);
        $page2_sorted = explode(",", $_POST['page2_sorted']);
        $page1_right_sorted = explode(",", $_POST['page1_right_sorted']);
        $page2_right_sorted = explode(",", $_POST['page2_right_sorted']);
        $hidden_sorted = explode(",", $_POST['hidden_sorted']);
        $o_query = $o_main->db->query("DELETE FROM dashboard_usersettings WHERE username = ?", array($variables->loggID));

        if(count($page1_sorted) > 0){
            $sortnr = 1;
            foreach($page1_sorted as $page1_sorted_single){
                if($page1_sorted_single != "") {
                    $path = openssl_decrypt ($page1_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 0, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }
		if(count($page1_right_sorted) > 0){
            $sortnr = 1;
            foreach($page1_right_sorted as $page1_sorted_single){
                if($page1_sorted_single != "") {
                    $path = openssl_decrypt ($page1_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 2, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }

        if(count($page2_sorted) > 0){
            $sortnr = 1;
            foreach($page2_sorted as $page2_sorted_single ){
                if($page2_sorted_single != "") {
                    $path = openssl_decrypt ($page2_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 1, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }
		if(count($page1_right_sorted) > 0){
            $sortnr = 1;
            foreach($page2_right_sorted as $page2_sorted_single){
                if($page2_sorted_single != "") {
                    $path = openssl_decrypt ($page2_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 3, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }
		if(count($page1_full) > 0){
            $sortnr = 1;
            foreach($page1_full as $page2_sorted_single){
                if($page2_sorted_single != "") {
                    $path = openssl_decrypt ($page2_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 4, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }
		if(count($page2_full) > 0){
            $sortnr = 1;
            foreach($page2_full as $page2_sorted_single){
                if($page2_sorted_single != "") {
                    $path = openssl_decrypt ($page2_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = 5, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }
        if(count($hidden_sorted) > 0){
            $sortnr = 1;
            foreach($hidden_sorted as $hidden_sorted_single ){
                if($hidden_sorted_single != "") {
                    $path = openssl_decrypt ($hidden_sorted_single, $ciphering,
                            $decryption_key, $options, $decryption_iv);
                    $o_query = $o_main->db->query("INSERT INTO dashboard_usersettings SET created = NOW(), username = ?, display_on = -1, dashboard_path = ?, sortnr = ?", array($variables->loggID, $path, $sortnr));
                    $sortnr++;
                }
            }
        }

        $fw_redirect_url = $_POST['redirect_url'];
    }
}

?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=dashboard_edit";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list"; ?>">
		<div class="inner">
            <div class="line_left">
                <div class="element_title"><?php echo $formText_Elements_output; ?></div>
                <div class="connectedSortable" id="sortable_element">
                    <?php

                    $o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? ORDER BY sortnr", array($variables->loggID));
    				$elementsAdded = $o_query ? $o_query->result_array() : array();

                    $paths_added =array();
                    foreach($elementsAdded as $elementAdded) {
                        array_push($paths_added, $elementAdded['dashboard_path']);
                    }

                    $o_query = $o_main->db->query("SELECT * FROM moduledata");
    				$modulesToShow = $o_query ? $o_query->result_array() : array();
    				foreach($modulesToShow as $moduleSingle){
    					if(file_exists(__DIR__."/../../../".$moduleSingle['name']."/output_dashboard/output.php")){
                            $path = $moduleSingle['name']."/output_dashboard";
                            if(!in_array($path, $paths_added)) {
                                // Use openssl_encrypt() function to encrypt the data
                                $encrypted_path = openssl_encrypt($path, $ciphering,
                                            $encryption_key, $options, $encryption_iv);
                                echo "<div id='".$encrypted_path."'>".$path."</div>";
                            }
    					} else {
    						if(is_dir(__DIR__."/../../../".$moduleSingle['name']."/output_dashboard/")){
    							$directories = glob(__DIR__."/../../../".$moduleSingle['name']."/output_dashboard/*", GLOB_ONLYDIR);
    							if(count($directories) > 0){
    								foreach($directories as $directory){
    									if(file_exists($directory."/output.php")){
                                            $path = $moduleSingle['name']."/output_dashboard/".basename($directory);
                                            if(!in_array($path, $paths_added)) {
                                                // Use openssl_encrypt() function to encrypt the data
												$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

												$dashboardName = "";
												$dashboardFullWidth = false;
					                            $explodedPath = explode("/", $path);
												include(__DIR__."/../../../".$path."/settings.php");
												if($dashboardName == ""){
													$dashboardName = $explodedPath[0]." - ".basename($path);
												}
												if($dashboardFullWidth) {
													$dashboardName.= " (".$formText_FullWidth_output.")";
												}
												?>
												<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
												<?php
                                            }
    									}
    								}
    							}
    						}
    					}
    				}
                    ?>
                </div>


                <div class="element_title"><?php echo $formText_Hidden_output; ?></div>
                <div class="hiddden_2_wrapper connectedSortable" id="sortable_element4">
                    <?php
                    foreach($elementsAdded as $elementAdded) {
                        if($elementAdded['display_on'] == -1){
                            $path = $elementAdded['dashboard_path'];
							$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

							$dashboardName = "";
							$dashboardFullWidth = false;
                            $explodedPath = explode("/", $path);
							include(__DIR__."/../../../".$path."/settings.php");
							if($dashboardName == ""){
								$dashboardName = $explodedPath[0]." - ".basename($path);
							}
							if($dashboardFullWidth) {
								$dashboardName.= " (".$formText_FullWidth_output.")";
							}
							?>
							<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
							<?php
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="line_right">
				<div class="element_title"><?php echo $formText_Page1Full_output; ?></div>
				<div class="page_1_wrapper connectedSortable" id="sortable_element5">
					<?php
					foreach($elementsAdded as $elementAdded) {
						if($elementAdded['display_on'] == 4){
							$path = $elementAdded['dashboard_path'];
							$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

							$dashboardName = "";
							$dashboardFullWidth = false;
                            $explodedPath = explode("/", $path);
							include(__DIR__."/../../../".$path."/settings.php");
							if($dashboardName == ""){
								$dashboardName = $explodedPath[0]." - ".basename($path);
							}
							if($dashboardFullWidth) {
								$dashboardName.= " (".$formText_FullWidth_output.")";
							}
							?>
							<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
							<?php
						}
					}
					?>
				</div>
				<div class="leftColumn">
	                <div class="element_title"><?php echo $formText_Page1Left_output; ?></div>
	                <div class="page_1_wrapper connectedSortable" id="sortable_element2">
	                    <?php
	                    foreach($elementsAdded as $elementAdded) {
	                        if($elementAdded['display_on'] == 0){
	                            $path = $elementAdded['dashboard_path'];
								$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

								$dashboardName = "";
								$dashboardFullWidth = false;
	                            $explodedPath = explode("/", $path);
								include(__DIR__."/../../../".$path."/settings.php");
								if($dashboardName == ""){
									$dashboardName = $explodedPath[0]." - ".basename($path);
								}
								if($dashboardFullWidth) {
									$dashboardName.= " (".$formText_FullWidth_output.")";
								}
								?>
								<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
								<?php
	                        }
	                    }
	                    ?>
	                </div>
				</div>
				<div class="rightColumn">
					<div class="element_title"><?php echo $formText_Page1Right_output; ?></div>
					<div class="page_1_wrapper2 connectedSortable" id="sortable_element2_1">
						<?php
						foreach($elementsAdded as $elementAdded) {
							if($elementAdded['display_on'] == 2){
								$path = $elementAdded['dashboard_path'];
								$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

								$dashboardName = "";
								$dashboardFullWidth = false;
	                            $explodedPath = explode("/", $path);
								include(__DIR__."/../../../".$path."/settings.php");
								if($dashboardName == ""){
									$dashboardName = $explodedPath[0]." - ".basename($path);
								}
								if($dashboardFullWidth) {
									$dashboardName.= " (".$formText_FullWidth_output.")";
								}
								?>
								<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<div class="clear"></div>

				<div class="element_title"><?php echo $formText_Page2Full_output; ?></div>
				<div class="page_1_wrapper connectedSortable" id="sortable_element6">
					<?php
					foreach($elementsAdded as $elementAdded) {
						if($elementAdded['display_on'] == 5){
							$path = $elementAdded['dashboard_path'];
							$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

							$dashboardName = "";
							$dashboardFullWidth = false;
                            $explodedPath = explode("/", $path);
							include(__DIR__."/../../../".$path."/settings.php");
							if($dashboardName == ""){
								$dashboardName = $explodedPath[0]." - ".basename($path);
							}
							if($dashboardFullWidth) {
								$dashboardName.= " (".$formText_FullWidth_output.")";
							}
							?>
							<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
							<?php
						}
					}
					?>
				</div>
				<div class="leftColumn">
	                <div class="element_title"><?php echo $formText_Page2Left_output; ?></div>
	                <div class="page_2_wrapper connectedSortable" id="sortable_element3">
	                    <?php
	                    foreach($elementsAdded as $elementAdded) {
	                        if($elementAdded['display_on'] == 1){
	                            $path = $elementAdded['dashboard_path'];
								$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

								$dashboardName = "";
								$dashboardFullWidth = false;
	                            $explodedPath = explode("/", $path);
								include(__DIR__."/../../../".$path."/settings.php");
								if($dashboardName == ""){
									$dashboardName = $explodedPath[0]." - ".basename($path);
								}
								if($dashboardFullWidth) {
									$dashboardName.= " (".$formText_FullWidth_output.")";
								}
								?>
								<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
								<?php
	                        }
	                    }
	                    ?>
	                </div>
				</div>
				<div class="rightColumn">
					<div class="element_title"><?php echo $formText_Page2Right_output; ?></div>
	                <div class="page_2_wrapper2 connectedSortable" id="sortable_element3_1">
	                    <?php
	                    foreach($elementsAdded as $elementAdded) {
	                        if($elementAdded['display_on'] == 3){
	                            $path = $elementAdded['dashboard_path'];
								$encrypted_path = openssl_encrypt($path, $ciphering, $encryption_key, $options, $encryption_iv);

								$dashboardName = "";
								$dashboardFullWidth = false;
	                            $explodedPath = explode("/", $path);
								include(__DIR__."/../../../".$path."/settings.php");
								if($dashboardName == ""){
									$dashboardName = $explodedPath[0]." - ".basename($path);
								}
								if($dashboardFullWidth) {
									$dashboardName.= " (".$formText_FullWidth_output.")";
								}
								?>
								<div id="<?php echo $encrypted_path;?>" class="<?php if($dashboardFullWidth) echo 'dashboardFullWidth'?>"><?php echo $dashboardName;?></div>
								<?php
	                        }
	                    }
	                    ?>
	                </div>
				</div>
				<div class="clear"></div>
            </div>
            <div class="clear"></div>
		</div>

		<div class="popupformbtn">
			<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
			<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
		</div>
	</form>
</div>
<script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
<script type="text/javascript">

$(document).ready(function() {
	$( "#sortable_element,#sortable_element4" ).sortable({
        connectWith: ".connectedSortable",
		receive: function(event, ui) {
			$("#popup-validate-message").html("").hide();
		}
    }).disableSelection();
    $( "#sortable_element2,#sortable_element2_1,#sortable_element3,#sortable_element3_1" ).sortable({
        connectWith: ".connectedSortable",
		receive: function(event, ui) {
			$("#popup-validate-message").html("").hide();
            if($(ui.item).hasClass("dashboardFullWidth") ) {
				$("#popup-validate-message").html("<b>"+$(ui.item).html()+"</b> <?php echo $formText_CanNotAddToRegularColumnPleaseAddToFullWidth_output;?>").show();
      			$(".connectedSortable").sortable("cancel").sortable("refresh");
			}
		}
    }).disableSelection();
	$( "#sortable_element5,#sortable_element6" ).sortable({
        connectWith: ".connectedSortable",
		receive: function(event, ui) {
			$("#popup-validate-message").html("").hide();
            if(!$(ui.item).hasClass("dashboardFullWidth") ) {
				$("#popup-validate-message").html("<b>"+$(ui.item).html()+"</b> <?php echo $formText_CanNotAddToFullWidthPleaseAddToRegularColumn_output;?>").show();
      			$(".connectedSortable").sortable("cancel").sortable("refresh");
			}
		}
    }).disableSelection();
    $("form.output-form").validate({
        submitHandler: function(form) {

            fw_loading_start();
            var page1_sorted = $( "#sortable_element2" ).sortable( "toArray" );
            var page1_right_sorted = $( "#sortable_element2_1" ).sortable( "toArray" );
            var page2_sorted = $( "#sortable_element3" ).sortable( "toArray" );
            var page2_right_sorted = $( "#sortable_element3_1" ).sortable( "toArray" );
            var hidden_sorted = $( "#sortable_element4" ).sortable( "toArray" );
            var page1_full = $( "#sortable_element5" ).sortable( "toArray" );
            var page2_full = $( "#sortable_element6" ).sortable( "toArray" );
            var data = [];

            data[0] = {
                name: "page1_sorted",
                value:page1_sorted,
            }
            data[1] = {
                name: "page2_sorted",
                value:page2_sorted,
            }
            data[2] = {
                name: "hidden_sorted",
                value:hidden_sorted,
            }
			data[3] = {
                name: "page1_right_sorted",
                value: page1_right_sorted,
            }
			data[4] = {
                name: "page2_right_sorted",
                value: page2_right_sorted,
            }
			data[5] = {
                name: "page1_full",
                value: page1_full,
            }
			data[6] = {
                name: "page2_full",
                value: page2_full,
            }
            var data_serialized = $(form).serializeArray();

            var dataToPass = $.merge(data, data_serialized);

            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: dataToPass,
                success: function (data) {
                    fw_loading_end();
                    if(data.error !== undefined){
                        $("#popup-validate-message").html(data.error);
                        $("#popup-validate-message").show();
                    } else {
                        if(data.redirect_url !== undefined)
                        {
                            out_popup.addClass("close-reload");
                            out_popup.close();
                        }
                    }
                }
            }).fail(function() {
                $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
                $("#popup-validate-message").show();
                $('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                fw_loading_end();
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

    $('.datefield').datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });
});

</script>
<style>
.line_left {
    width: 30%;
    float: left;
    min-height: 100px;
}
.line_right {
    width: 68%;
    float: right;
}
.line_right .leftColumn {
	float: left;
	width: 49%;
}
.line_right .rightColumn {
	float: right;
	width: 49%;
}
#sortable_element {
    border: 1px solid #cecece;
    padding: 5px 10px;
	min-height: 200px;
}
.element_title {
    font-weight: bold;
    margin-bottom: 5px;
}
.page_1_wrapper,
.page_2_wrapper,
.page_1_wrapper2,
.page_2_wrapper2,
.hiddden_2_wrapper {
    min-height: 150px;
    border: 1px solid #cecece;
    padding: 5px 10px;
}

.connectedSortable div {
    padding: 3px 0px;
	cursor: move;
	word-break: break-all;
}
.selectDivModified {
    display:block;
}
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
.popupform input.popupforminput.checkbox {
    width: auto;
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
</style>
