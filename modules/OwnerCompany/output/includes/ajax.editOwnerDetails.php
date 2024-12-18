<?php
//error_reporting(E_ALL | E_STRICT); ini_set("display_errors", 1);
$ownercompanyId = isset($_POST['ownercompanyId']) ? $_POST['ownercompanyId'] : 0;

$o_query = $o_main->db->get('ownercompany_accountconfig');
$ownercompany_accountconfig = $o_query ? $o_query->row_array() : array();
if($moduleAccesslevel > 10) {
	if(isset($_POST['output_form_submit'])) {
        if ($ownercompanyId) {

		    $sql = "SELECT * FROM ownercompany WHERE id = $ownercompanyId";
		    $result = $o_main->db->query($sql);
		    if($result && $result->num_rows() > 0) $officeSpaceData = $result->row();
			$division = $officeSpaceData->division;
			if($ownercompany_accountconfig['activate_division'] && $variables->developeraccess>= 20) {
				$division = $_POST['division'];
			}

            $s_sql = "UPDATE ownercompany SET
            updated = now(),
            updatedBy = ?,
            name = ?,
            companyname = ?,
            companypostalbox = ?,
            companyzipcode = ?,
            companypostalplace = ?,
            companyCountry = ?,
            companyphone = ?,
            companyEmail = ?,
            companyorgnr = ?,
			extra_text_after_company_org_number = ?,
            accountingproject_code =?,
            clientNumberFactoring=?,
            accountingdepartment_code = ?,
            division = ?
            WHERE id = ?";
            $o_main->db->query($s_sql, array($variables->loggID, $_POST['companyname'], $_POST['companyname'], $_POST['companypostalbox'], $_POST['companyzipcode'], $_POST['companypostalplace'], $_POST['companyCountry'], $_POST['companyphone'], $_POST['companyEmail'], $_POST['companyorgnr'],$_POST['extra_text_after_company_org_number'], $_POST['accountingproject_code'], $_POST['clientNumberFactoring'], $_POST['accountingdepartment_code'], $division, $ownercompanyId));
            $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$ownercompanyId;
        }
        else {
            $ownerCompanyAccountConfig_sql = $o_main->db->query("SELECT * FROM ownercompany_accountconfig");
            if($ownerCompanyAccountConfig_sql && $ownerCompanyAccountConfig_sql->num_rows() > 0) $ownerCompanyAccountConfig = $ownerCompanyAccountConfig_sql->row();
            $maximumOwnerCompanies = intval($ownerCompanyAccountConfig->max_number_ownercompanies);
            if($maximumOwnerCompanies == 0) {
                $maximumOwnerCompanies = 1;
            }
			$division = $_POST['division'];
            $currentOwnerCompanyCount_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE content_status < 2");
            $currentOwnerCompanyCount = $currentOwnerCompanyCount_sql->num_rows();
            if($currentOwnerCompanyCount < $maximumOwnerCompanies) {
                $s_sql = "INSERT INTO ownercompany SET
                created = now(),
                createdBy = ?,
                name = ?,
                companyname = ?,
                companypostalbox = ?,
                companyzipcode = ?,
                companypostalplace = ?,
                companyCountry = ?,
                companyphone = ?,
                companyEmail = ?,
                companyorgnr = ?,
				extra_text_after_company_org_number = ?,
                accountingproject_code = ?,
                clientNumberFactoring = ?,
                accountingdepartment_code = ?,
	            division = ?";
                $o_main->db->query($s_sql, array($variables->loggID, $_POST['companyname'], $_POST['companyname'], $_POST['companypostalbox'], $_POST['companyzipcode'], $_POST['companypostalplace'], $_POST['companyCountry'], $_POST['companyphone'], $_POST['companyEmail'], $_POST['companyorgnr'],$_POST['extra_text_after_company_org_number'], $_POST['accountingproject_code'], $_POST['clientNumberFactoring'], $_POST['accountingdepartment_code'], $division));
                $insert_id = $o_main->db->insert_id();
                $fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$insert_id;
            } else {
                $fw_error_msg = $formText_MaximumNumberOfOwnerCompaniesAreReached_Output;
            }
        }
	}
}

if($ownercompanyId) {
    $sql = "SELECT * FROM ownercompany WHERE id = $ownercompanyId";
    $result = $o_main->db->query($sql);
    if($result && $result->num_rows() > 0) $officeSpaceData = $result->row();
}
?>

<div class="popupform">
	<div id="popup-validate-message" style="display:none;"></div>
	<form class="output-form main" action="<?php print $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=editOwnerDetails";?>" method="post">
		<input type="hidden" name="fwajax" value="1">
		<input type="hidden" name="fw_nocss" value="1">
		<input type="hidden" name="output_form_submit" value="1">
		<input type="hidden" name="ownercompanyId" value="<?php echo $ownercompanyId;?>">
		<div class="inner">

            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyName_Output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyname" value="<?php echo $officeSpaceData->companyname; ?>" required>
                </div>
                <div class="clear"></div>
            </div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_CompanyPostal_output; ?></div>
        		<div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companypostalbox" value="<?php echo $officeSpaceData->companypostalbox; ?>">
                </div>
        		<div class="clear"></div>
    		</div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyZipCode_output; ?></div>
                <div class="lineInput">
                    <input type="text" class="popupforminput botspace" name="companyzipcode" value="<?php echo $officeSpaceData->companyzipcode; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyPostalPlace_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="companypostalplace" value="<?php echo $officeSpaceData->companypostalplace; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyCountry_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="companyCountry" value="<?php echo $officeSpaceData->companyCountry; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyPhone_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="companyphone" value="<?php echo $officeSpaceData->companyphone; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyEmail_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="companyEmail" value="<?php echo $officeSpaceData->companyEmail; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_CompanyOrgNr_output; ?></div>
                <div class="lineInput">
					<input type="text" class="popupforminput botspace" name="companyorgnr" value="<?php echo $officeSpaceData->companyorgnr; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ExtraTextAfterCompanyOrgNr_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="extra_text_after_company_org_number" value="<?php echo $officeSpaceData->extra_text_after_company_org_number; ?>">
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Project_Output; ?></div>
                <div class="lineInput projectWrapper">
                    <select name="accountingproject_code">
                        <option value=""><?php echo $formText_SelectProject_output; ?></option>
                        <?php
                        function getProjects($o_main, $parentNumber = 0) {
                            $projects = array();

                            if ($parentNumber != 0) {
                                $o_main->db->order_by('projectnumber', 'ASC');
                                $o_query = $o_main->db->get_where('projectforaccounting', array('parentNumber' => $parentNumber));
                            } else {
                                $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY projectnumber");
                            }

                            if ($o_query && $o_query->num_rows()) {
                                foreach ($o_query->result_array() as $row) {
									$childrenProjects = array();
									if(trim($row['projectnumber']) != ""){
										if(trim($row['projectnumber']) > 0){
											$childrenProjects = getProjects($o_main, $row['projectnumber']);
										}
									}
                                    array_push($projects, array(
                                        'id' => $row['id'],
                                        'name' => $row['name'],
                                        'number' => $row['projectnumber'],
                                        'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
                                        'children' => $childrenProjects
                                    ));
                                }
                            }

                            return $projects;
                        }

                        function getProjectsOptionsListHtml($projects, $level, $accountingproject_code) {
                            ob_start(); ?>

                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['number']; ?>" <?php echo $project['number'] == $accountingproject_code ? 'selected="selected"' : ''; ?>>
                                    <?php
                                    $identer = '';
                                    for($i = 0; $i < $level; $i++) { $identer .= '-'; }
                                    echo $identer;
                                    ?>
                                    <?php echo $project['number']; ?> <?php echo $project['name']; ?>
                                </option>

                                <?php if (count($project['children'])): ?>
                                    <?php echo getProjectsOptionsListHtml($project['children'], $level+1, $accountingproject_code); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php return ob_get_clean();
                        }

                        $projects = getProjects($o_main);
                        echo getProjectsOptionsListHtml($projects, 0, $officeSpaceData->accountingproject_code);
                    ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_Department_Output; ?></div>
                <div class="lineInput departmentWrapper">
                    <select name="accountingdepartment_code">
                        <option value=""><?php echo $formText_SelectDepartment_output; ?></option>
                        <?php
                        function getDepartments($o_main, $parentNumber = 0) {
                            $projects = array();

                            if ($parentNumber) {
                                $o_main->db->order_by('departmentnumber', 'ASC');
                                $o_query = $o_main->db->get_where('departmentforaccounting', array('parentNumber' => $parentNumber));
                            } else {
                                $o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY departmentnumber");
                            }

                            if ($o_query && $o_query->num_rows()) {
                                foreach ($o_query->result_array() as $row) {
                                    array_push($projects, array(
                                        'id' => $row['id'],
                                        'name' => $row['name'],
                                        'number' => $row['departmentnumber'],
                                        'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
                                        'children' => getDepartments($o_main, $row['departmentnumber'])
                                    ));
                                }
                            }

                            return $projects;
                        }

                        function getDepartmentsOptionsListHtml($projects, $level, $accountingproject_code) {
                            ob_start(); ?>

                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['number']; ?>" <?php echo $project['number'] == $accountingproject_code ? 'selected="selected"' : ''; ?>>
                                    <?php
                                    $identer = '';
                                    for($i = 0; $i < $level; $i++) { $identer .= '-'; }
                                    echo $identer;
                                    ?>
                                    <?php echo $project['number']; ?> <?php echo $project['name']; ?>
                                </option>

                                <?php if (count($project['children'])): ?>
                                    <?php echo getDepartmentsOptionsListHtml($project['children'], $level+1, $accountingproject_code); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php return ob_get_clean();
                        }

                        $projects = getDepartments($o_main);
                        echo getDepartmentsOptionsListHtml($projects, 0, $officeSpaceData->accountingproject_code);
                    ?>
                    </select>
                </div>
                <div class="clear"></div>
            </div>
            <div class="line">
                <div class="lineTitle"><?php echo $formText_ClientNumberFactoring_output; ?></div>
                <div class="lineInput">
                <input type="text" class="popupforminput botspace" name="clientNumberFactoring" value="<?php echo $officeSpaceData->clientNumberFactoring; ?>">
                </div>
                <div class="clear"></div>
            </div>

			<?php
			if($ownercompany_accountconfig['activate_division'] && $variables->developeraccess>= 20) { ?>
	            <div class="line">
	                <div class="lineTitle"><?php echo $formText_Division_output; ?></div>
	                <div class="lineInput">
	                <input type="text" class="popupforminput botspace" name="division" value="<?php echo $officeSpaceData->division; ?>">
	                </div>
	                <div class="clear"></div>
	            </div>
			<?php } ?>
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
     $("form.output-form").validate({
        submitHandler: function(form) {
            fw_loading_start();
            $.ajax({
                url: $(form).attr("action"),
                cache: false,
                type: "POST",
                dataType: "json",
                data: $(form).serialize(),
                success: function (data) {
                    fw_loading_end();
                    if(data.redirect_url !== undefined)
                    {
                        out_popup.addClass("close-reload").data("redirect", data.redirect_url);
                        out_popup.close();
                    } else {
                        $("#popup-validate-message").html(data.error, true);
                        $("#popup-validate-message").show();
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
});

</script>
<style>

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
