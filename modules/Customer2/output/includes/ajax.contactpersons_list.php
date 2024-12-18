<?php
$cid = $_GET['cid'];
if(isset($_POST['customerId'])){
    $cid = $_POST['customerId'];
}
$subunit_filter = $_GET['subunit_filter'];
if(isset($_POST['subunit_filter'])){
    $subunit_filter = $_POST['subunit_filter'];
}

if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$s_sql = "select * from accountinfo";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_accountinfo = $o_query->row_array();
}
$s_sql = "select * from customer_stdmembersystem_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_membersystem_config = $o_query->row_array();
}
$s_sql = "SELECT * FROM article_accountconfig";
$o_query = $o_main->db->query($s_sql);
$article_accountconfig = $o_query ? $o_query->row_array() : array();

$s_sql = "SELECT * FROM customer_basisconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $customer_basisconfig = $o_query->row_array();
}

$s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $v_customer_accountconfig = $o_query->row_array();
}
$s_sql = "SELECT * FROM ownercompany_accountconfig ORDER BY id DESC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0){
    $ownercompany_accountconfig = $o_query->row_array();
}

if(!function_exists("rewriteCustomerBasisconfig")) include_once("fnc_rewritebasisconfig.php");
rewriteCustomerBasisconfig();

$s_sql = "SELECT * FROM customer WHERE id = ?";
$o_query = $o_main->db->query($s_sql, array($cid));
if($o_query && $o_query->num_rows()>0){
    $customerData = $o_query->row_array();
}

$showAll = false;
if(isset($_POST['showAll']) && $_POST['showAll']){
    $showAll = true;
}
$defaultCount = 30;
$perPageDefault = 50;
$showUntil = $defaultCount;
if(isset($_POST['showUntil']) && intval($_POST['showUntil'])>0){
    $showUntil = intval($_POST['showUntil']);
}

if($cid > 0)
{
    $sqlNoLitmit = "select * from contactperson where customerId = ? AND content_status = 0 order by name";
    $invoice_count = 0;
    $o_query = $o_main->db->query($sqlNoLitmit, array($cid));
    if($o_query && $o_query->num_rows()>0) {
        $invoice_count = $o_query->num_rows();
    }
    $searchSql = "";
    $search_count = 0;
    if(isset($_GET['contactpersonSearch']) && trim($_GET['contactpersonSearch']) != ""){
        $_POST['search'] = $_GET['contactpersonSearch'];
    }
    if(isset($_POST['search']) && trim($_POST['search']) != ""){
        $searchTextContact = $_POST['search'];
        $searchArray = explode(" ", $searchTextContact);

        $searchSql = " AND (CONCAT(name, ' ', middlename, ' ', lastname)  LIKE '%".$searchTextContact."%' OR  email LIKE '%".$searchTextContact."%' OR title LIKE '%".$searchTextContact."%' OR mobile LIKE '%".$searchTextContact."%')";
        $sqlNoLitmit = "select * from contactperson where customerId = ? AND content_status = 0 ".$searchSql." order by name";

        $o_query = $o_main->db->query($sqlNoLitmit, array($cid));
        if($o_query && $o_query->num_rows()>0) {
            $search_count = $o_query->num_rows();
        }
    }



    if($showAll){
        $o_query = $o_main->db->query($sqlNoLitmit, array($cid));
    } else  {
        $sql = "select * from contactperson where customerId = ? AND content_status = 0 ";
        $sql .= $searchSql;
        $sql .= " order by name LIMIT ".$showUntil." OFFSET 0";
        $o_query = $o_main->db->query($sql, array($cid));
    }
    if($o_query) {
        $showingNow = $o_query->num_rows();
        $v_subrows = $o_query->result_array();
    }

	$l_counter = 0;
	$v_options1 = array(0 => "Ukjent", 1 => "Eier bor i leiligheten", 2 => "Eier bor ikke i leiligheten", 3 => "Leietager", 4 => "Medlem av husstand");
	$v_options2 = array(0 => "Samme postadresse som boenheten", 1 => "Annen postadresse enn boenheten");

    $contactPersonEmails = array();
    $tdWidth = "17px";
    ?>
    <?php if(isset($_POST['search']) && trim($_POST['search']) != "") { ?>
        <div class="searchResult">
            <?php echo $formText_Searched_output." ". $search_count." / ".$invoice_count ?> <span class="resetSearch"><?php echo $formText_ResetSearch_output;?></span>
        </div>
    <?php } ?>
	<div class="">
		<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=ContactpersonAccess&folderfile=output&folder=output&inc_obj=details&cid=".$customerData['id'];?>" class="optimize pull-right"><?php echo $formText_ManageExternalAccess_Output;?></a>
	</div>
    <table class="table table-bordered table-striped">
        <tr>
            <?php if($customer_basisconfig['activateContactPersonAccess']) { ?>
                <th width="5%">&nbsp;</th>
            <?php }?>
			<?php if($v_customer_accountconfig['activate_selfdefined_company']) { ?>
			<th width="10%"><?php echo $formText_SelfdefinedCompany_Output;?></th>
            <?php }?>
            <th>
                <?php echo $formText_Name_output; ?>
                <?php if($v_customer_accountconfig['activate_contactperson_birthdate']) {
                    echo "<br/>".$formText_Birthdate_Output;
                } ?>
                <?php if(0 == $v_customer_accountconfig['contactperson_title_setting']) { ?>
                    <br/><?php echo $formText_Title_output; ?>
                <?php } ?>
            </th>
            <?php if(1 == $v_customer_accountconfig['activatePersonalMembership']) { ?>
            <th><?php echo $formText_PersonalMembership_output; ?></th>
            <?php } ?>
			<th>
                <?php if(0 == $v_customer_accountconfig['contactperson_mobile_setting']) { ?>
                    <?php echo $formText_Mobile_output; ?><br/>
    			<?php } ?>
                <?php echo $formText_ContactEmail_output; ?>
                <?php if(2 == intval($v_customer_accountconfig['contactperson_mobile_setting'])) { ?>
                    <br/><?php echo $formText_Mobile_output; ?>
                <?php } ?>
            </th>
            <?php if($customer_basisconfig['activateGroupsInContactperson']) { ?>
            <th class=""><?php echo $formText_Groups_output; ?></th>
            <?php } ?>
            <?php if($customer_basisconfig['activateContactPersonAdmin']) { ?>
            <th class="smallColumn"><?php echo $formText_Admin_output; ?></th>
            <?php } ?>
            <th class="smallColumn"><?php echo $formText_Inactive_output; ?></th>
            <?php if($customer_basisconfig['activateContactPersonMessages']) { ?>
            <th class="smallColumn"><?php echo $formText_Messages_output; ?></th>
            <?php } ?>
            <?php if($customer_basisconfig['activateContactPersonReceiveInfo']) { ?>
                 <th class="smallColumn"><?php echo $formText_WantToReceiveInfo_Output; ?></th>
             <?php } ?>
             <?php if($customer_basisconfig['activateContactPersonMainContact']) { ?>
                  <th class="smallColumn"><?php echo $formText_MainContact_Output; ?></th>
              <?php } ?>
          <?php if($customer_basisconfig['activateContactPersonDisplayInMemberpage']) { ?>
               <th class="smallColumn"><?php echo $formText_DisplayInMemberpage_Output; ?></th>
           <?php } ?>
           <?php if($customer_basisconfig['activateHideContactPersonInIntranet']) { ?>
               <th class="smallColumn"><?php echo $formText_NotVisibleInMemberOverview_Output; ?></th>
           <?php } ?>
           <?php
           if($v_customer_accountconfig['activate_subunits']) {
               ?>
               <th><?php echo $formText_Subunits_Output; ?></th>
               <?php
           }
           ?>
            <th class="smallColumn actionWidth">&nbsp;</th>
        </tr>
        <?php
        foreach($v_subrows as $v_subrow)
		{
            $showContactperson = true;
            if($subunit_filter > 0){
                $showContactperson = false;
                $s_sql = "select customer_subunit.* from contactperson_subunit_connection
                LEFT OUTER JOIN customer_subunit ON customer_subunit.id = contactperson_subunit_connection.subunit_id
                WHERE contactperson_subunit_connection.contactperson_id = '".$o_main->db->escape_str($v_subrow['id'])."' AND contactperson_subunit_connection.subunit_id = '".$o_main->db->escape_str($subunit_filter)."'";
                $o_query = $o_main->db->query($s_sql);
                $connection = $o_query ? $o_query->row_array() : array();
                if($connection) {
                    $showContactperson = true;
                }
            }
            if($showContactperson){
    			?>
                 <tr>

                    <?php if($customer_basisconfig['activateContactPersonAccess']) { ?>
                    <td width="<?php echo $tdWidth;?>">
                        <div class="employeeImage load" data-id="<?php echo $v_subrow["id"];?>"><loading-dots>.</loading-dots></div>
    					<div class="output-access-loader load" data-id="<?php echo $v_subrow["id"];?>" data-email="<?php echo $v_subrow['email'];?>" data-membersystem-id="<?php echo $l_membersystem_id;?>"></div>
                    </td>
                    <?php } ?>
    				<?php if($v_customer_accountconfig['activate_selfdefined_company']) { ?>
    				<td><div class="company_name load" data-id="<?php echo $v_subrow["id"];?>"><loading-dots>.</loading-dots></div></td>
    				<?php } ?>
                    <td>
                        <?php echo $v_subrow['name'] ." ".$v_subrow['middlename']." ".$v_subrow['lastname']; ?>

        				<?php if(2 == intval($v_customer_accountconfig['contactperson_title_setting'])) { ?>
                            <div class="contactPersonTitleLabel"><?php echo $v_subrow['title']; ?></div>
                        <?php } ?>
                        <?php if($v_customer_accountconfig['activate_contactperson_birthdate']) {
                            if($v_subrow['birthdate'] != "0000-00-00" && $v_subrow['birthdate'] != ""){
                                echo "<br/>".date("d.m.Y", strtotime($v_subrow['birthdate']));
                            }
                        } ?>
        				<?php if(0 == intval($v_customer_accountconfig['contactperson_title_setting'])) { ?>
                            <br/><?php echo $v_subrow['title']; ?>
        				<?php } ?>
                    </td>
                    <?php if(1 == $v_customer_accountconfig['activatePersonalMembership']) {
                        $sql = "SELECT subscriptiontype.name as subscriptionTypeName, subscriptiontype_subtype.name as subscriptionSubtypeName, subscriptionmulti.stoppedDate FROM subscriptionmulti
                        LEFT OUTER JOIN subscriptiontype ON subscriptiontype.id = subscriptionmulti.subscriptiontype_id
                        LEFT OUTER JOIN subscriptiontype_subtype ON subscriptiontype_subtype.id = subscriptionmulti.subscriptionsubtypeId
                        LEFT OUTER JOIN contactperson_role_conn ON contactperson_role_conn.subscriptionmulti_id = subscriptionmulti.id
                        WHERE subscriptiontype.activatePersonalSubscriptionConnection = 1 AND contactperson_role_conn.contactperson_id = '".$o_main->db->escape_str($v_subrow['id'])."'";
                        $o_query = $o_main->db->query($sql);
                        $personalMemberships = $o_query ? $o_query->result_array(): array();
                        ?>
        			<td><?php
                    foreach($personalMemberships as $personalMembership) {
                        $subscribtionBlockClass = "";
                        if ($personalMembership['stoppedDate'] && $personalMembership['stoppedDate'] != '0000-00-00'  && strtotime($personalMembership['stoppedDate']) <  strtotime(date("Y-m-d"))):
                            $subscribtionBlockClass = "stopped";
                        elseif ($personalMembership['stoppedDate'] && $personalMembership['stoppedDate'] != '0000-00-00'  && strtotime($personalMembership['stoppedDate']) >=  strtotime(date("Y-m-d"))):
                            $subscribtionBlockClass = "activeStopped"; ?>
                        <?php endif;
                        ?>
                        <div class="personal_membership_wrapper <?php echo $subscribtionBlockClass;?>">
                            <?php
                            echo $personalMembership['subscriptionTypeName']."</br>".$personalMembership['subscriptionSubtypeName'];
                            if($personalMembership['stoppedDate'] != "0000-00-00" && $personalMembership['stoppedDate'] != ""){
                                if(strtotime($personalMembership['stoppedDate']) >= strtotime(date("Y-m-d"))){
                                    ?>
                                    <div class=""><?php echo $formText_FutureStopped_output.": ".date("d.m.Y", strtotime($personalMembership['stoppedDate']))?></div>
                                <?php } else {
                                    ?>
                                    <div class=""><?php echo $formText_Stopped_output.": ".date("d.m.Y", strtotime($personalMembership['stoppedDate']))?></div>
                                    <?php
                                }
                            }   ?>
                        </div>
                        <?php
                    }
                    ?></td>
                    <?php } ?>
                    <td>
                        <?php if(0 == intval($v_customer_accountconfig['contactperson_mobile_setting'])) { ?>
                            <?php echo $v_subrow['mobile_prefix'].$v_subrow['mobile']; ?><br/>
                        <?php } ?>
                        <?php echo $v_subrow['email']; ?>
                        <?php if(2 == intval($v_customer_accountconfig['contactperson_mobile_setting'])) { ?>
                            <div class="contactPersonMobileLabel"><?php echo $v_subrow['mobile']; ?></div>
                        <?php } ?>
                    </td>
                    <?php if($customer_basisconfig['activateGroupsInContactperson']) { ?>
                        <td class="">
                            <?php
                            $sql = "SELECT g.* FROM contactperson_group_user p
                            JOIN contactperson_group g ON g.id = p.contactperson_group_id
                            WHERE p.type = 1 AND g.group_type = 1 AND (p.status = 0 OR p.status is null) AND (p.hidden = 0 OR p.hidden is null) AND p.contactperson_id = ?";
                        	$o_query = $o_main->db->query($sql, array($v_subrow['id']));
                        	$groups = $o_query ? $o_query->result_array(): array();
                            ?>
                            <div class="editContactpersonGroupsLeft">
                                <?php
                                foreach($groups as $group_single){
                                    ?>
                                    <div class="cp_group_wrapper"><?php echo $group_single['name'];?></div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            echo '<span class="editContactpersonGroups glyphicon glyphicon-pencil" data-contactperson-id="'.$v_subrow['id'].'"></span>';
                            ?>
                            <div class="clear"></div>
                        </td>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateContactPersonAdmin']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['admin']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <td class="smallColumn">
                        <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['inactive']){ echo ' checked';}?> disabled readonly/>
                    </td>
                    <?php if($customer_basisconfig['activateContactPersonMessages']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['not_receive_messages']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateContactPersonReceiveInfo']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['wantToReceiveInfo']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateContactPersonMainContact']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['mainContact']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateContactPersonDisplayInMemberpage']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['displayInMemberpage']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <?php if($customer_basisconfig['activateHideContactPersonInIntranet']) { ?>
                        <td class="smallColumn">
                            <input type="checkbox" class="defaultCheckbox popupforminput checkbox botspace" name="" value="1"<?php if($v_subrow['notVisibleInMemberOverview']){ echo ' checked';}?> disabled readonly/>
                        </td>
                    <?php } ?>
                    <?php
                    if($v_customer_accountconfig['activate_subunits']) {
    					// $s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? ORDER BY customer_subunit.id ASC";
    					// $o_query = $o_main->db->query($s_sql, array($customerData['id']));
    					// $subunits = $o_query ? $o_query->result_array() : array();
                        $s_sql = "select customer_subunit.* from contactperson_subunit_connection
                        LEFT OUTER JOIN customer_subunit ON customer_subunit.id = contactperson_subunit_connection.subunit_id
                        WHERE contactperson_subunit_connection.contactperson_id = '".$o_main->db->escape_str($v_subrow['id'])."'";
                        $o_query = $o_main->db->query($s_sql);
                        $connections = $o_query ? $o_query->result_array() : array();
                        ?>
                        <td>
                            <?php
                            foreach($connections as $connection) {
                                echo $connection['name']."</br>";
                                ?>

                                <?php
                            }
                            ?>
                        </td>
                        <?php
                    }
                    ?>
                    <td class="smallColumn actionWidth">
						<?php if(($v_subrow['created'] != "0000-00-00 00:00:00" && $v_subrow['created'] != null) || ($v_subrow['updated'] != "0000-00-00 00:00:00" && $v_subrow['updated'] != null)){?>
						<span class="glyphicon glyphicon-info-sign hoverEyeCreated show-right-over">
							<div class="hoverInfo">
								<?php
								$createdShown = false;
								if($v_subrow['created'] != "0000-00-00 00:00:00" && $v_subrow['created'] != null){
									echo '<div>'.$formText_CreatedBy_output?>: <?php echo $v_subrow['createdBy']. " ".date("d.m.Y H:i:s", strtotime($v_subrow['created'])).'</div>';
									$createdShown = true;
								}
								?>
								<?php
								if($v_subrow['updated'] != "0000-00-00 00:00:00" && $v_subrow['updated'] != null){
									echo '<div>'.$formText_UpdatedBy_output?>: <?php echo $v_subrow['updatedBy']. " ".date("d.m.Y H:i:s", strtotime($v_subrow['updated'])).'</div>';
								}
								?>
								<div class="output_show_content_history" data-id="<?php echo $v_subrow['id'];?>" data-table="contactperson"><?php echo $formText_ShowHistory_Output;?></div>
							</div>
						</span>
					<?php } ?>
						<?php if($moduleAccesslevel > 10) { ?><button class="editEntryBtn output-edit-contactperson" data-cid="<?php echo $v_subrow['id'];?>"><span class="glyphicon glyphicon-pencil"></span></button> <button class="editEntryBtn output-delete-contactperson" data-cid="<?php echo $v_subrow['id'];?>" data-url="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson&cid=".$v_subrow['id'];?>" data-delete-msg="<?php echo $formText_DeleteContactperson_Output.": ".$v_subrow['name'];?>?"><span class="glyphicon glyphicon-trash"></span></button>
						<?php } ?>
                    </td>
                </tr><?php
    			$l_counter++;
                array_push($contactPersonEmails, $v_subrow['email']);
            }
		}
        ?>
    </table>
    <?php
    if(isset($_POST['search']) && trim($_POST['search']) != "") {
        $invoice_count = $search_count;
    }
    if($invoice_count > $showingNow) {?>
    <div class="dropdownShowRow">
        <?php echo $formText_Showing_output." ".$showingNow." ".$formText_Of_output." ".$invoice_count;?>
        <?php if($invoice_count-$showingNow >= $perPageDefault){ ?>
            <a href="#" class="invoiceShowNext"><?php echo $formText_Show_output." ".$perPageDefault." ".$formText_More_output;?></a>
        <?php } ?>
        <a href="#" class="invoiceShowAll"><?php echo $formText_ShowAll_output;?></a>
    </div>
    <?php } ?>
    <?php
}
?>
<script type="text/javascript">
function contactperson_post_load()
{
	var ids = [];
	$('.output-access-loader').each(function(){
		if($(this).is('.load'))
		{
			ids.push($(this).data('id'));
		}
	});
	ajaxCall({module_file:'contactperson_post_load&abortable=1'}, { output_post_form: 1, ids: ids, customer_id: "<?php echo $customerData['id'];?>" }, function(json) {
        if(json.error !== undefined)
		{
			$.each(json.error, function(index, value){
				var _type = Array("error");
				if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
				fw_info_message_add(_type[0], value);
			});
			fw_info_message_show();
		}
		if(json.data != undefined){
    		$.each(json.data, function(id, obj){
    			if($('.employeeImage[data-id="' + id + '"]').is('.load'))
    			{
    				$('.employeeImage[data-id="' + id + '"]').html(obj.image).removeClass('load');
    			}
    			if($('.output-access-loader[data-id="' + id + '"]').is('.load'))
    			{
    				$('.output-access-loader[data-id="' + id + '"]').html(obj.access).removeClass('load');
    			}
    			if($('.company_name[data-id="' + id + '"]').is('.load'))
    			{
    				$('.company_name[data-id="' + id + '"]').html(obj.company).removeClass('load');
    			}
    		});
        }
		contactperson_bind();
	}, false);
}
function output_edit_contactperson(cid)
{
	fw_loading_start();
	if(cid === undefined) cid = 0;
	$.ajax({
		cache: false,
		type: 'POST',
		dataType: 'json',
		url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson";?>',
		data: { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $cid;?>', cid: cid},
		success: function(obj){
			fw_loading_end();
			$('#popupeditboxcontent').html('');
			$('#popupeditboxcontent').html(obj.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		}
	});
}
function contactperson_bind()
{
	$(".searchResult .resetSearch").unbind("click").bind("click", function(e){
		e.preventDefault();
		$(".contactPersonSearchInput").val("").keyup();
	})
	$(".invoiceShowAll").unbind("click").bind("click", function(e){
		e.preventDefault();
		var data = {
			customerId: <?php echo $cid;?>,
			search: '<?php if(isset($_POST['search'])){ echo $_POST['search']; } ?>',
			showAll: true
		};
		ajaxCall('contactpersons_list', data, function(json) {
			$("#output-contactpersons .contactpersonTableWrapper").html(json.html).slideDown();
		});
	})
	$(".invoiceShowNext").unbind("click").bind("click", function(e){
		e.preventDefault();
		var data = {
			customerId: <?php echo $cid;?>,
			search: '<?php if(isset($_POST['search'])){ echo $_POST['search']; } ?>',
			showUntil: <?php echo $showingNow+$perPageDefault;?>
		};
		ajaxCall('contactpersons_list', data, function(json) {
			$("#output-contactpersons .contactpersonTableWrapper").html(json.html).slideDown();
		});
	})


	$(".output-edit-contactperson").off('click').on('click', function(e){
		e.preventDefault();
		output_edit_contactperson($(this).data('cid'));
	});
	$('#output-add-contactpersons').off('click').on('click', function(e){
		e.preventDefault();
		output_edit_contactperson();
	});


	$(".output-contactperson-edit-door-access").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_door_access_code_type', data, function(json) {
			$('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
	});

	$(".output-contactperson-edit-keycard").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_keycard', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
	});

	$(".output-contactperson-edit-lock-access").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_lock_access', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
	});

	$(".output-contactperson-edit-wifi").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_wifi', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
	});

	$(".output-contactperson-edit-gate").off('click').on('click', function(e){
		e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_gate', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
	});

	$(".output-delete-contactperson").off('click').on('click', function(e){
		e.preventDefault();
		var contactperson = $(this).data("cid");
		if(contactperson > 0){
			var $_this = $(this);
			bootbox.confirm({
				message:$_this.attr("data-delete-msg"),
				buttons:{confirm:{label:"<?php echo $formText_Yes_Output;?>"},cancel:{label:"<?php echo $formText_No_Output;?>"}},
				callback: function(result){
					if(result)
					{

						fw_loading_start();
						var _data = { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', output_delete: true};
						if($(this).data('block')) _data['block'] = $(this).data('block');
						$.ajax({
							cache: false,
							type: 'POST',
							dataType: 'json',
							url: $_this.data('url'),
							data: _data,
							success: function(data){
								fw_loading_end();
								if(data.error !== undefined)
								{
									var _data = { fwajax: 1, fw_nocss: 1, customerId: '<?php echo $customerData['id'];?>', cid: contactperson, hide_access: data.data};
									$.each(data.error, function(index, value){
										var _type = Array("error");
										if(index.length > 0 && index.indexOf("_") > 0) _type = index.split("_");
										_data['message'] = value;
									});
									fw_loading_start();
									$.ajax({
										cache: false,
										type: 'POST',
										dataType: 'json',
										url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=delete_all";?>',
										data: _data,
										success: function(obj){
											fw_loading_end();
											$('#popupeditboxcontent').html('');
											$('#popupeditboxcontent').html(obj.html);
											out_popup = $('#popupeditbox').bPopup(out_popup_options);
											$("#popupeditbox:not(.opened)").remove();
										}
									});
									// fw_info_message_empty();

									// fw_info_message_show();
									fw_loading_end();
								} else {
									fw_load_ajax(data.redirect_url,'',true);
								}
							}
						});
					}
				}
			});
		}
	});
    $(".editContactpersonGroups").off("click").on("click", function(e){
        e.preventDefault();
		var data = {
			contactpersonId: $(this).data('contactperson-id'),
			customerId: '<?php echo $cid; ?>'
		};
		ajaxCall('edit_contactperson_group', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
			fw_click_instance = false;
			fw_loading_end();
		});
    });
	$('.output_show_content_history').off('click').on('click', function(e){
		var data = {
			id: $(this).data('id'),
			table: $(this).data('table'),
		};
		ajaxCall('show_content_history', data, function(json) {
			 $('#popupeditboxcontent').html('').html(json.html);
			out_popup = $('#popupeditbox').bPopup(out_popup_options);
			$("#popupeditbox:not(.opened)").remove();
		});
	});
}
$(function(){
	contactperson_post_load();
	contactperson_bind();
});
</script>
<style>
.editContactpersonGroups {
    cursor: pointer;
    color: #46b2e2;
    float: right;
    vertical-align: top;
}
.editContactpersonGroupsLeft {
    float: left;
    width: calc(100% - 16px);
    vertical-align: top;
}
.contactpersonTableWrapper table {
    table-layout: fixed;
    word-wrap: break-word;
}
.contactPersonTitleLabel {
    color: #a1a1a1;
}
.searchResult {
    margin-left: 20px;
    float: left;
}
.searchResult .resetSearch {
    margin: 0;
    margin-left: 15px;
    color: #0284C9;
    cursor: pointer;
    vertical-align: middle;
}
.p_pageContent .contactPersonSearch {
    position: relative;
    float: left;
    margin-bottom: 10px;
}
.p_pageContent .contactPersonSearch .contactPersonSearchSuggestions {
    display: none;
    background: #fff;
    position: absolute;
    width: 100%;
    max-height: 200px;
    overflow: auto;
    z-index: 2;
    border: 1px solid #dedede;
    border-top: 0;
}
.p_pageContent .contactPersonSearch .contactPersonSearchSuggestions table {
    margin-bottom: 0;
}
#p_container .p_pageContent .contactPersonSearch .contactPersonSearchSuggestions td {
    padding: 5px 10px;
}

.p_pageContent .contactPersonSearch .glyphicon-triangle-right {
    position: absolute;
    top: 7px;
    right: 4px;
    color: #048fcf;
}
.p_pageContent .contactPersonSearch .glyphicon-search {
    position: absolute;
    top: 7px;
    left: 6px;
    color: #048fcf;
}
.p_pageContent .contactPersonSearchInput {
    width: 250px;
    border: 1px solid #dedede;
    padding: 3px 15px 3px 25px;
}
.p_pageContent .contactPersonSearchInputBefore {
    width: 150px;
    border: 1px solid #dedede;
    padding: 3px 10px 3px 10px;
}
.p_pageContent .contactPersonSearchBtn {
    background: #0093e7;
    border-radius: 5px;
    margin-left: 3px;
    color: #fff;
    padding: 5px 15px;
    cursor: pointer;
    border: 0;
}
.output_show_content_history {
	cursor:pointer;
	color: #048fcf;
	text-align:right;
}
.personal_membership_wrapper {
    padding: 2px 3px;
    border-radius: 5px;
    border: 2px solid #66C733;
    margin-bottom: 5px;
    font-size: 12px;
}
.personal_membership_wrapper.stopped {
    border: 2px solid #D91D1D;
}
.personal_membership_wrapper.activeStopped {
    border: 2px solid #FF9300;
}
.cp_group_wrapper {
    padding: 2px 3px;
    border-radius: 5px;
    border: 2px solid #cecece;
    margin-bottom: 5px;
    font-size: 12px;
}
</style>
