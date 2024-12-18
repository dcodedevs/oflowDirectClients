<?php
if($_POST['customerId'] > 0){
    if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");
    $s_sql = "select * from customer_stdmembersystem_basisconfig";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0) {
        $v_membersystem_config = $o_query->row_array();
    }
    $s_sql = "select * from customer_basisconfig";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0) {
        $customer_basisconfig = $o_query->row_array();
    }
    $s_sql = "SELECT * FROM customer_accountconfig ORDER BY id DESC";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0){
        $v_customer_accountconfig = $o_query->row_array();
    }
    $s_sql = "select * from accountinfo";
    $o_query = $o_main->db->query($s_sql);
    if($o_query && $o_query->num_rows()>0) {
        $v_accountinfo = $o_query->row_array();
    }
    $s_sql = "select * from project2_accountconfig";
    $o_query = $o_main->db->query($s_sql);
    $v_project2_accountconfig= ($o_query ? $o_query->row_array() : array());

    $s_sql = "select * from project2_basisconfig";
    $o_query = $o_main->db->query($s_sql);
    $v_project2_basisconfig = ($o_query ? $o_query->row_array() : array());

    foreach($v_project2_accountconfig as $key=>$value){
        if($value > 0){
            $v_project2_basisconfig[$key] = ($value - 1);
        }
    }

    if($moduleAccesslevel > 10)
    {
    	if(isset($_POST['output_form_submit']))
    	{

            $s_sql = "select * from contactperson where mainContact = 1 AND customerId = ?";
            $o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
            $mainContacts = $o_query ? $o_query->result_array() : array();
            $birthdate = "0000-00-00";
            if($_POST['birthdate'] != ""){
                $birthdate = date("Y-m-d", strtotime($_POST['birthdate']));
            }
			if('' == trim($_POST['mobile_prefix'])) $_POST['mobile_prefix'] = '+47';
            $oldContact = array();
    		if(isset($_POST['cid']) && $_POST['cid'] > 0)
    		{

            	$s_sql = "select * from contactperson where id = ?";
                $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
                $oldContact = $o_query ? $o_query->row_array() : array();

    			$s_sql = "UPDATE contactperson SET
    			updated = now(),
    			updatedBy= ?,
    			name= ?,
    			middlename= ?,
    			lastname= ?,
                title= ?,
    			mobile= ?,
    			mobile_prefix= ?,
    			email= ?,
    			admin= ?,
    			inactive= ?,
    			not_receive_messages= ?,
                wantToReceiveInfo = ?,
                mainContact = ?,
                displayInMemberpage = ?,
                notSyncronizeToIntranet = ?,
                notVisibleInMemberOverview = ?,
                extracheckbox1 = ?,
                extracheckbox2 = ?,
                type = 1,
                birthdate = ?
    			WHERE id = ?";

                //$return['sql'] = $s_sql;

    			$queryCheck = $o_main->db->query($s_sql, array($variables->loggID, $_POST['name'], $_POST['middlename'], $_POST['lastname'], $_POST['title'], $_POST['mobile'], $_POST['mobile_prefix'], $_POST['email'], $_POST['admin'], $_POST['inactive'], $_POST['not_receive_messages'],$_POST['wantToReceiveInfo'],$_POST['mainContact'],$_POST['displayInMemberpage'], $_POST['notSyncronizeToIntranet'],$_POST['notVisibleInMemberOverview'],$_POST['extracheckbox1'],$_POST['extracheckbox2'],$birthdate,$_POST['cid']));
                $newContactId = $oldContact['id'];

				$b_no_changes = ($oldContact['name'] == $_POST['name'] && $oldContact['name'] == $_POST['middlename'] && $oldContact['name'] == $_POST['lastname']);
				if(!$b_no_changes)
				{
					$contactpersonId = $oldContact['id'];
					$s_include_file = __DIR__.'/../../../ContactpersonAccess/output/includes/perform_contactperson_sync.php';
					if(is_file($s_include_file)) include($s_include_file);
				}
        	} else {

            	$s_sql = "select * from contactperson where email = ? AND customerId = ?";
                $o_query = $o_main->db->query($s_sql, array(trim($_POST['email']), $_POST['customerId']));
                $oldContact = $o_query ? $o_query->row_array() : array();
                if(trim($_POST['email']) == ""){
                    $oldContact = false;
                }
                if(!$oldContact){
        			$s_sql = "INSERT INTO contactperson SET
        			id=NULL,
        			moduleID = ?,
        			created = now(),
        			createdBy=?,
        			name= ?,
        			middlename= ?,
        			lastname= ?,
                    title= ?,
        			mobile= ?,
        			mobile_prefix= ?,
        			email= ?,
        			admin= ?,
        			inactive= ?,
        			not_receive_messages= ?,
                    wantToReceiveInfo = ?,
        			customerId=?,
                    mainContact = ?,
                    displayInMemberpage = ?,
                    notSyncronizeToIntranet = ?,
                    notVisibleInMemberOverview = ?,
                    extracheckbox1 = ?,
                    extracheckbox2 = ?,
                    type = 1,
                    birthdate = ?";
        			$queryCheck = $o_main->db->query($s_sql, array($moduleID, $variables->loggID, $_POST['name'], $_POST['middlename'], $_POST['lastname'], $_POST['title'], $_POST['mobile'], $_POST['mobile_prefix'], $_POST['email'], $_POST['admin'], $_POST['inactive'], $_POST['not_receive_messages'],$_POST['wantToReceiveInfo'], $_POST['customerId'], $_POST['mainContact'],$_POST['displayInMemberpage'],$_POST['notSyncronizeToIntranet'],$_POST['notVisibleInMemberOverview'],$_POST['extracheckbox1'],$_POST['extracheckbox2'],$birthdate));

                    $newContactId = $o_main->db->insert_id();
                } else {
                    $fw_error_msg[] = $formText_EmailAlreadyUsed_output;
                }
            }

            if (!$queryCheck)
            {
                if(count($oldContact) == 0){
                    $s_sql = "DELETE FROM contactperson where id = ?";
                    $o_query = $o_main->db->query($s_sql, array($newContactId));
                } else {
                    $o_main->db->set($oldContact);
                    $o_main->db->where('id', $oldContact['id']);
                    $o_query = $o_main->db->update("contactperson");
                }
                $fw_error_msg[] = $formText_ErrorUpdatingContactPerson_output;
            }
        	$s_sql = "select * from contactperson where id = ?";
            $o_query = $o_main->db->query($s_sql, array($newContactId));
            $newContact = $o_query ? $o_query->row_array() : array();
            if($newContact['mainContact']){
                foreach($mainContacts as $mainContact){
                    if($mainContact['id'] != $newContact['id']){
                    	$s_sql = "UPDATE contactperson SET mainContact = 0 where id = ?";
                        $o_query = $o_main->db->query($s_sql, array($mainContact['id']));
                        if($o_query){
                            echo $formText_ThereCanBeOnly1MainContact_output." ".$formText_MainContactWasRemovedFrom_output." ".$mainContact['name']." ".$mainContact['middlename']." ".$mainContact['lastname']." - ".$mainContact['email']."</br>";
                        }
                    }
                }
            }
            if($newContactId > 0){
                $addedConnectionIds = array();
                foreach($_POST['subunits'] as $subunitId) {
                    $s_sql = "select * from contactperson_subunit_connection WHERE contactperson_id = '".$o_main->db->escape_str($newContactId)."' AND subunit_id = '".$o_main->db->escape_str($subunitId)."'";
                    $o_query = $o_main->db->query($s_sql);
                    $connection = $o_query ? $o_query->row_array() : array();
                    if(!$connection) {
                        $s_sql = "INSERT INTO contactperson_subunit_connection SET created = NOW(), createdBy = '".$o_main->db->escape_str($variables->loggID)."', subunit_id = '".$o_main->db->escape_str($subunitId)."', contactperson_id = '".$o_main->db->escape_str($newContactId)."'";

                        $o_query = $o_main->db->query($s_sql);
                        $connectionId = $o_main->db->insert_id();
                    } else {
                        $connectionId = $connection['id'];
                    }
                    $addedConnectionIds[] = $connectionId;

                }
                if(count($addedConnectionIds) > 0) {
                    $s_sql = "DELETE FROM contactperson_subunit_connection WHERE id NOT IN (".implode(',', $addedConnectionIds).") AND contactperson_id = '".$o_main->db->escape_str($newContactId)."'";
                    $o_query = $o_main->db->query($s_sql);
                } else{
                    $s_sql = "DELETE FROM contactperson_subunit_connection WHERE contactperson_id = '".$o_main->db->escape_str($newContactId)."'";
                    $o_query = $o_main->db->query($s_sql);
                }
            }

            $fw_return_data = $newContactId."__".trim($newContact['name']." ".$newContact['middlename']." ".$newContact['lastname']);
    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
    		return;
    	} else if(isset($_POST['output_delete']))
    	{
    		if(isset($_GET['cid']) && $_GET['cid'] > 0)
    		{
    		 	unset($o_membersystem);

    			$s_sql = "select * from contactperson where id = ?";
    		    $o_query = $o_main->db->query($s_sql, array($_GET['cid']));
    		    if($o_query && $o_query->num_rows()>0) {
    		        $v_data = $o_query->row_array();
    		    }
                if($v_data['email']!="")
                {
                    $o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $accountinfo['accountname'], $accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$v_data["email"], "MEMBERSYSTEMID"=>$v_data[$v_membersystem_config['content_id_field']], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
                }
                if(!is_object($o_membersystem->data)) {
                    $sql = "SELECT * FROM subscriptionmulti
                    LEFT OUTER JOIN contactperson_role_conn ON contactperson_role_conn.subscriptionmulti_id = subscriptionmulti.id
                    WHERE subscriptionmulti.content_status < 2 AND subscriptionmulti.startDate <= NOW()
                    AND ((subscriptionmulti.stoppedDate is null OR subscriptionmulti.stoppedDate = '0000-00-00') OR (subscriptionmulti.stoppedDate > NOW()))
                    AND contactperson_role_conn.contactperson_id = '".$o_main->db->escape_str($_GET['cid'])."'";
                    $o_query = $o_main->db->query($sql);
                    $activeSubscriptions = $o_query ? $o_query->result_array(): array();
                    if(count($activeSubscriptions) == 0){
                        $synced = true;
                        $contactpersonId = $_GET['cid'];
                        $contactpersonItems = array(array("id"=>$contactpersonId, "deleteEntry"=>1));
    					$b_delete_in_external_systems = TRUE;
    					$s_include_file = __DIR__.'/../../../ContactpersonAccess/output/includes/perform_contactperson_sync.php';
    					if(is_file($s_include_file)) include($s_include_file);

                        if($synced){
            				$s_sql = "DELETE FROM contactperson WHERE id = ?";
            				$o_main->db->query($s_sql, array($_GET['cid']));
                        } else {
    						$fw_error_msg[] = $formText_ErrorOccurredDeletingUser_output;
                            $fw_return_data = 1;
    						return;
    					}
                    } else {
        				$fw_error_msg[] = $formText_CantDeleteUserWithActiveSubscription_output;
                        $fw_return_data = 1;
        				return;
                    }

    			} else {
    				$fw_error_msg[] = $formText_CantDeleteUserWithAccess_output;
                    $fw_return_data = 0;
    				return;
    			}
    		}

    		$fw_redirect_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$_POST['customerId'];
    		return;
    	}
    }

    $b_is_activated = false;
    if(isset($_POST['cid']) && $_POST['cid'] > 0)
    {
    	$s_sql = "select * from contactperson where id = ?";
        $o_query = $o_main->db->query($s_sql, array($_POST['cid']));
        if($o_query && $o_query->num_rows()>0) {
            $v_data = $o_query->row_array();
        }
    	$s_sql = "select * from customer where id = ?";
        $o_query = $o_main->db->query($s_sql, array($v_data['customerId']));
        $customerData = $o_query ? $o_query->row_array() : array();

    	$o_membersystem = json_decode(APIconnectAccount("membersystemcompanyaccessusernameget", $accountinfo['accountname'], $accountinfo['password'], array("COMPANY_ID"=>$companyID, "USER"=>$v_data["email"], "MEMBERSYSTEMID"=>$v_data[$v_membersystem_config['content_id_field']], "ACCESSLEVEL"=>$v_membersystem_config['access_level'], "MODULE"=>$module)));
    	$v_registered_access[$v_subrow["id"]] = array("id"=>$o_membersystem->data->id);
    	$b_is_activated = (is_object($o_membersystem->data) ? 1 : 0);
    }
    require_once("fnc_rewritebasisconfig.php");
    rewriteCustomerBasisconfig();
    ?>
    <div class="popupform">
    <?php if($_POST['from_popup']) { ?>
        <div id="popup-validate-message2" style="display:none;"></div>
    <?php } else { ?>
        <div id="popup-validate-message" style="display:none;"></div>
    <?php } ?>
    <form class="output-form-contactperson" action="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=edit_contactperson";?>" method="post">
    	<input type="hidden" name="fwajax" value="1">
    	<input type="hidden" name="fw_nocss" value="1">
    	<input type="hidden" name="output_form_submit" value="1">
    	<input type="hidden" name="cid" value="<?php print $_POST['cid'];?>">
    	<input type="hidden" name="customerId" value="<?php print $_POST['customerId'];?>">
    	<input type="hidden" name="from_popup" value="<?php print $_POST['from_popup'];?>">

    	<div class="inner">

    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_Name_Output; ?></div>
        		<div class="lineInput">
        			<?php if($v_data['privatePersonCustomer'] && intval($customerData['customerType']) == 1) {?>
        				<input class="popupforminput botspace" name="name" type="hidden" value="<?php echo $v_data['name'];?>" required autocomplete="off">
                        <?php echo $v_data['name'];?>
        			<?php } else { ?>
        				<input class="popupforminput botspace" name="name" type="text" value="<?php echo $v_data['name'];?>" required autocomplete="off">
        			<?php } ?>
        		</div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_MiddleName_Output; ?></div>
        		<div class="lineInput">
        			<?php if($v_data['privatePersonCustomer'] && intval($customerData['customerType']) == 1) {?>
        				<input class="popupforminput botspace" name="middlename" type="hidden" value="<?php echo $v_data['middlename'];?>" autocomplete="off">
                        <?php echo $v_data['middlename'];?>
        			<?php } else { ?>
        				<input class="popupforminput botspace" name="middlename" type="text" value="<?php echo $v_data['middlename'];?>" autocomplete="off">
        			<?php } ?>
        		</div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
        		<div class="lineTitle"><?php echo $formText_LastName_Output; ?></div>
        		<div class="lineInput">
        			<?php if($v_data['privatePersonCustomer'] && intval($customerData['customerType']) == 1) {?>
        				<input class="popupforminput botspace" name="lastname" type="hidden" value="<?php echo $v_data['lastname'];?>" autocomplete="off">
                        <?php echo $v_data['lastname'];?>
        			<?php } else { ?>
        				<input class="popupforminput botspace" name="lastname" type="text" value="<?php echo $v_data['lastname'];?>" autocomplete="off">
        			<?php } ?>
        		</div>
        		<div class="clear"></div>
    		</div>
    		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Title_Output; ?></div>
    		<div class="lineInput"><input class="popupforminput botspace" name="title" type="text" value="<?php echo $v_data['title'];?>" autocomplete="off"></div>
    		<div class="clear"></div>
    		</div>

    		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Mobile_Output; ?></div>
    		<div class="lineInput output-ajax-dropdown">
				<input class="popupforminput botspace ajax-search" name="mobile_prefix" type="text" value="<?php echo $v_data['mobile_prefix'];?>" placeholder="+47" autocomplete="new-mobile-prefix" data-script="get_mobile_prefix" style="width:30% !important;">
				<ul class="output-dropdown-list" style="margin-top:-10px;"></ul>
				<input class="popupforminput botspace" name="mobile" type="text" value="<?php echo $v_data['mobile'];?>" autocomplete="off" style="width:69% !important;"></div>
    		<div class="clear"></div>
    		</div>

    		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Email_Output; ?></div>
    		<div class="lineInput"><input id="uid-email-<?php echo $v_data['id'];?>" class="popupforminput botspace" name="email" type="text" value="<?php echo $v_data['email'];?>" <?php echo ($b_is_activated ? ' readonly style="width:250px !important; background-color:#bebebe;"':'');?> autocomplete="off"><?php
    		if($b_is_activated)
    		{
    			?> <button class="btn btn-xs btn-default output-access-remove-return" data-id="<?php echo $v_data['id'];?>" data-make-writable="#uid-email-<?php echo $v_data['id'];?>"><?php echo $formText_RemoveAccess_Output;?></button><div id="uid-email-<?php echo $v_data['id'];?>-msg" class="error-msg"></div><?php
    		}
    		?></div>
    		<div class="clear"></div>
    		</div>

            <?php if($v_customer_accountconfig['activate_contactperson_birthdate']) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Birthdate_Output; ?></div>
                    <div class="lineInput"><input class="popupforminput botspace datepickerBirth" name="birthdate" type="text" value="<?php if($v_data['birthdate'] != "0000-00-00" && $v_data['birthdate'] != "") { echo date("d.m.Y", strtotime($v_data['birthdate']));}?>" autocomplete="off"></div>
                    <div class="clear"></div>
                </div>
             <?php } ?>

        	<?php if($customer_basisconfig['activateContactPersonAdmin']) { ?>
    			<div class="line">
    			<div class="lineTitle"><?php echo $formText_Admin_Output; ?></div>
    			<div class="lineInput"><input class="" name="admin" type="checkbox" value="1" <?php if($v_data['admin']) { echo 'checked';}?>></div>
    			<div class="clear"></div>
    			</div>
    		<?php } ?>
    		<div class="line">
    		<div class="lineTitle"><?php echo $formText_Inactive_Output; ?></div>
    		<div class="lineInput"><input class="inactivecheckbox" name="inactive" type="checkbox" value="1" <?php if($v_data['inactive']) { echo 'checked';}?>></div>
    		<div class="clear"></div>
    		</div>

            <?php if($customer_basisconfig['activateContactPersonMessages']) { ?>
        		<div class="line">
        		<div class="lineTitle"><?php echo $formText_NotReceiveMessages_Output; ?></div>
        		<div class="lineInput"><input class="notreceive" name="not_receive_messages" type="checkbox" value="1" <?php if($v_data['not_receive_messages']) { echo 'checked';}?>></div>
        		<div class="clear"></div>
        		</div>
            <?php } ?>
            <?php if($customer_basisconfig['activateContactPersonReceiveInfo']) { ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_WantToReceiveInfo_Output; ?></div>
                    <div class="lineInput"><input class="wantToReceiveInfo" name="wantToReceiveInfo" type="checkbox" value="1" <?php if($v_data['wantToReceiveInfo']) { echo 'checked';}?>></div>
                    <div class="clear"></div>
                </div>
             <?php } ?>

             <?php if($customer_basisconfig['activateContactPersonMainContact']) { ?>
                 <div class="line">
                     <div class="lineTitle"><?php echo $formText_MainContact_Output; ?></div>
                     <div class="lineInput"><input class="mainContact" name="mainContact" type="checkbox" value="1" <?php if($v_data['mainContact']) { echo 'checked';}?>></div>
                     <div class="clear"></div>
                 </div>
              <?php } ?>

              <?php if($customer_basisconfig['activateContactPersonDisplayInMemberpage']) { ?>
                  <div class="line">
                      <div class="lineTitle"><?php echo $formText_DisplayInMemberpage_Output; ?></div>
                      <div class="lineInput"><input class="displayInMemberpage" name="displayInMemberpage" type="checkbox" value="1" <?php if($v_data['displayInMemberpage']) { echo 'checked';}?>></div>
                      <div class="clear"></div>
                  </div>
               <?php } ?>
               <?php if($customer_basisconfig['activateHideContactPersonInIntranet']) { ?>
                  <div class="line">
                      <div class="lineTitle"><?php echo $formText_NotVisibleInMemberOverview_Output; ?></div>
                      <div class="lineInput"><input class="notVisibleInMemberOverview" name="notVisibleInMemberOverview" type="checkbox" value="1" <?php if($v_data['notVisibleInMemberOverview']) { echo 'checked';}?>></div>
                      <div class="clear"></div>
                  </div>
               <?php } ?>
              <?php if($v_customer_accountconfig['activateContactPersonExtracheckbox1']) { ?>
                  <div class="line">
                      <div class="lineTitle"><?php echo $formText_ExtraCheckbox1_Output; ?></div>
                      <div class="lineInput"><input class="extracheckbox1" name="extracheckbox1" type="checkbox" value="1" <?php if($v_data['extracheckbox1']) { echo 'checked';}?>></div>
                      <div class="clear"></div>
                  </div>
               <?php } ?>
               <?php if($v_customer_accountconfig['activateContactPersonExtracheckbox2']) { ?>
                   <div class="line">
                       <div class="lineTitle"><?php echo $formText_ExtraCheckbox2_Output; ?></div>
                       <div class="lineInput"><input class="extracheckbox2" name="extracheckbox2" type="checkbox" value="1" <?php if($v_data['extracheckbox2']) { echo 'checked';}?>></div>
                       <div class="clear"></div>
                   </div>
                <?php } ?>
            <?php
            if($v_customer_accountconfig['activate_subunits']) {
				$s_sql = "SELECT * FROM customer_subunit WHERE customer_subunit.customer_id = ? ORDER BY customer_subunit.id ASC";
				$o_query = $o_main->db->query($s_sql, array($_POST['customerId']));
				$subunits = $o_query ? $o_query->result_array() : array();

                ?>
                <div class="line">
                    <div class="lineTitle"><?php echo $formText_Subunits_Output; ?></div>
                    <div class="lineInput">
                        <?php
                        foreach($subunits as $subunit) {
                            $s_sql = "select customer_subunit.* from contactperson_subunit_connection
                            LEFT OUTER JOIN customer_subunit ON customer_subunit.id = contactperson_subunit_connection.subunit_id
                            WHERE contactperson_subunit_connection.contactperson_id = '".$o_main->db->escape_str($v_data['id'])."' AND contactperson_subunit_connection.subunit_id = '".$o_main->db->escape_str($subunit['id'])."'";
                            $o_query = $o_main->db->query($s_sql);
                            $connection = $o_query ? $o_query->row_array() : array();
                            ?>
                            <div>
                                <input id="subunit<?php echo $subunit['id'];?>" type="checkbox" class="subunitCheckbox popupforminput checkbox botspace" name="subunits[]" value="<?php echo $subunit['id'];?>"<?php if($connection){ echo ' checked';}?>/> <label for="subunit<?php echo $subunit['id'];?>"><?php echo $subunit['name'];?></label>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="clear"></div>
                </div>
                <?php
            }
            ?>
    	</div>
    	<div class="popupformbtn">
    		<button type="button" class="output-btn b-large b-close"><?php echo $formText_Cancel_Output;?></button>
    		<input type="submit" name="sbmbtn" value="<?php echo $formText_Save_Output; ?>">
    	</div>
    </form>
    </div>
    <style>
    input[type="checkbox"][readonly] {
      pointer-events: none;
    }
    .subunitCheckbox {
        display: inline-block;
        vertical-align: middle;
    }
    </style>
    <script type="text/javascript" src="../modules/<?php echo $module?>/output/elementsOutput/jquery.validate/jquery.validate.min.js"></script>
    <script type="text/javascript">
    $(function() {
        $(".datepicker").datepicker({
            firstDay: 1,
            beforeShow: function(dateText, inst) {
                $(inst.dpDiv).removeClass('monthcalendar');
            },
            dateFormat: "dd.mm.yy"
        })
        $(".datepickerBirth").datepicker({
            firstDay: 1,
            changeMonth: true,
            changeYear: true,
            yearRange: "-100:+0",
            beforeShow: function(dateText, inst) {
                $(inst.dpDiv).removeClass('monthcalendar');
            },
            dateFormat: "dd.mm.yy"
        })
    	$(".inactivecheckbox").on('change', function(){
    		if($(this).is(":checked")) {
    			$(".notreceive").attr("readonly", true).prop("checked", true);
    		} else {
    			$(".notreceive").removeAttr("readonly");
    		}
    	})
    	$(".inactivecheckbox").change();
    	$("#output-contactperson-address-changer").on("change", function(){
    		if($(this).val() == 1)
    		{
    			$("#output-contactperson-address").show();
    		} else {
    			$("#output-contactperson-address").hide();//.find("input").val('');
    		}
    	});
    	$(".output-access-remove-return").on('click', function(e){

        	fw_loading_start();
    		e.preventDefault();
    		var _this = this;
    		$($(_this).data("make-writable")+'-msg').text("");
    		$.ajax({
    			cache: false,
    			type: 'POST',
    			dataType: 'json',
    			url: '<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=remove_access";?>',
    			data: { fwajax: 1, fw_nocss: 1, cid: $(this).data('id'), return_data: 1 },
    			success: function(obj){
    				fw_loading_end();
    				if(obj.data && obj.data.result == 1)
    				{
    					$(_this).remove();
    					$($(_this).data("make-writable")).prop("readonly", false).css('background-color','#ffffff');
    				} else {
    					$($(_this).data("make-writable")+'-msg').text("<?php echo $formText_ErrorOccured_Output;?>");
    				}
    			}
    		});
    	});

    	$("form.output-form-contactperson").validate({
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
                        if(data.error !== undefined)
    					{
    						$.each(data.error, function(index, value){
                                <?php if($_POST['from_popup']) { ?>
    	                         $("#popup-validate-message2").append("<div>"+value+"</div>").show();
                                <?php } else { ?>
    	                         $("#popup-validate-message").append("<div>"+value+"</div>").show();
                                <?php } ?>
    						});
    						fw_click_instance = fw_changes_made = false;
    					} else {
    	                    if(data.redirect_url !== undefined)
    	                    {
                                <?php if($_POST['from_popup']) { ?>
                                    var returnData = data.data;
                                    var infoArray = returnData.split("__", 2);
                                    if(infoArray.length == 2){
                                        $(".contactPersonSelect").val("");
                                        $(".contactPersonSelect option").removeClass("selected");
                                        $('<option value="'+infoArray[0]+'" class="selected" data-name="'+infoArray[1]+'">'+infoArray[1]+'</option>').insertBefore(".contactPersonSelect .createNewOption");
                                        $(".contactPersonSelect option.selected").prop("selected", true);
                                        $(".contactPersonSelect").change();

                                        <?php
                                            if($_POST['fromSubscription']){
                                               $roles = explode(",", str_replace(" ", "",$customer_basisconfig['rolesAvailableForContactperson']));
                                            } else {
                                                $roles = explode(",", str_replace(" ", "",$v_project2_basisconfig['rolesAvailableForContactperson']));
                                            }
                                           if(count($roles) > 0) { ?>
                                            $(".multicp_wrapper").append('<div class="line">'+
                                                '<div class="lineTitle">'+infoArray[1]+'</div>'+
                                                '<div class="lineInput">'+
                                                    '<table width="100%">'+
                                                        '<tr>'+
                                                        <?php
    				                                      $roleWidth = floor(100/count($roles));
                                                          foreach($roles as $role) {
                                                            $s_sql = "SELECT * FROM contactperson_role_conn WHERE project2_id = ? AND contactperson_id = ? AND role = ?";
                                                            $o_query = $o_main->db->query($s_sql, array($projectId, $resource['id'], $role));
                                                            $contactperson_conn = $o_query ? $o_query->row_array() : array();
                                                            ?>
                                                            '<td width="<?php echo $roleWidth?>%"><input class="contactPersonRole contactPersonRole<?php echo $role; ?>" type="checkbox" name="role<?php echo $role?>[]" value="'+infoArray[0]+'"/></td>'+
                                                        <?php } ?>
                                                        '</tr>'+
                                                    '</table>'+
                                                '</div>'+
                                                '<div class="clear"></div>'+
                                            '</div>');
        									$(".contactPersonRole1").off("click").on("click", function(){
        										if($(this).is(":checked")){
        											$(".contactPersonRole1").prop("checked", false);
        											$(this).prop("checked", true);
        										} else {
        											$(".contactPersonRole1").prop("checked", false);
        											$(this).prop("checked", false);
        										}
        									})
        									$(".contactPersonRole3").off("click").on("click", function(){
        										if($(this).is(":checked")){
        											$(".contactPersonRole3").prop("checked", false);
        											$(this).prop("checked", true);
        										} else {
        											$(".contactPersonRole3").prop("checked", false);
        											$(this).prop("checked", false);
        										}
        									})
                                        <?php } ?>
            	                        out_popup2.close();
                                    } else {

                                    }
                                <?php } else { ?>
        	                        out_popup.addClass("close-reload");
                                    if(data.html != ""){
                                        $('#popupeditboxcontent').html('');
                                        $('#popupeditboxcontent').html(data.html);
                                        out_popup2 = $('#popupeditbox').bPopup(out_popup_options);
                                        $("#popupeditbox:not(.opened)").remove();
                                        $(window).resize();
                                    } else {
            	                        out_popup.close();
                                    }
                                <?php } ?>
    	                    }
    					}
    				}
    			}).fail(function() {
                    <?php if($_POST['from_popup']) { ?>
                        $("#popup-validate-message2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
        				$("#popup-validate-message2").show();
        				$('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
                    <?php } else { ?>
                        $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
        				$("#popup-validate-message").show();
        				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    <?php } ?>
    				fw_loading_end();
    			});
    		},
    		invalidHandler: function(event, validator) {
    			var errors = validator.numberOfInvalids();
    			if (errors) {
    				var message = errors == 1
    				? '<?php echo $formText_YouMissed_validate; ?> 1 <?php echo $formText_field_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>'
    				: '<?php echo $formText_YouMissed_validate; ?> ' + errors + ' <?php echo $formText_fields_validate; ?>. <?php echo $formText_TheyHaveBeenHighlighted_validate; ?>';


                    <?php if($_POST['from_popup']) { ?>
                        $("#popup-validate-message2").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
        				$("#popup-validate-message2").show();
        				$('#popupeditbox2').css('height', $('#popupeditboxcontent2').height());
                    <?php } else { ?>
                        $("#popup-validate-message").html("<?php echo $formText_ErrorOccuredSavingContent_Output;?>", true);
        				$("#popup-validate-message").show();
        				$('#popupeditbox').css('height', $('#popupeditboxcontent').height());
                    <?php } ?>
    			} else {
                    <?php if($_POST['from_popup']) { ?>
    	                $("#popup-validate-message2").hide();
                    <?php } else { ?>
    	                $("#popup-validate-message").hide();
                    <?php } ?>
    			}
    			setTimeout(function(){
                    <?php if($_POST['from_popup']) { ?>
    	                $('#popupeditbox2').height('');
                    <?php } else { ?>
    	                $('#popupeditbox').height('');
                    <?php } ?>
                }, 200);
    		}
    	});
    });
	var output_ajax_dropdown;
	var output_ajax_dropdown_list;
	var output_ajax_dropdown_timer;
	$('.output-ajax-dropdown input.ajax-search').on('keyup',function(e){
		if($(this).val() != '')
		{
			window.clearTimeout(output_ajax_dropdown_timer);
			output_ajax_dropdown = this;
			output_ajax_dropdown_timer = window.setTimeout(output_init_ajax_dropdown, 200);
		}
	});
	$('.output-ajax-dropdown input.ajax-search').on('click',function(e){
		if($(this).is('.open')) return;
		if($(this).val() != '')
		{
			window.clearTimeout(output_ajax_dropdown_timer);
			output_ajax_dropdown = this;
			output_ajax_dropdown_timer = window.setTimeout(output_init_ajax_dropdown, 200);
		}
	});
	function output_init_ajax_dropdown()
	{
		$(output_ajax_dropdown).addClass('open');
		var post = {
			text: $(output_ajax_dropdown).val()
		};
		output_ajax_dropdown_list = $(output_ajax_dropdown).closest('.output-ajax-dropdown').find('.output-dropdown-list');
		$(output_ajax_dropdown_list).html('<li class="spin"><span class="fa fa-spinner fa-spin"></span></li>').show();
		output_build_ajax_dropdown(post);
	}
	function output_build_ajax_dropdown(post)
	{
		var _data = { fwajax: 1, fw_nocss: 1, owner: 1, text: post.text };
		$.ajax({
			method: "POST",
			dataType: 'json',
			url: "<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=ajax&inc_act=";?>" + $(output_ajax_dropdown).data('script'),
			data: _data
		})
		.done(function(json){
			if(json.data != undefined && json.data.status != undefined && json.data.status == 1 && json.data.results != undefined)
			{
				$(output_ajax_dropdown_list).html('<div class="list-title"><?php echo $formText_ChooseFromList_Output;?><i class="fa fa-times" onClick="javascript:$(output_ajax_dropdown).removeClass(\'open\');$(this).parent().parent().hide()"></i></div>').show();
				$(json.data.results).each(function(idx, obj){
					var $liNode = $('<li>').text(obj.name).data('code', obj.code);
					$(output_ajax_dropdown_list).append($liNode);
				});
			} else {
				$(output_ajax_dropdown_list).html('<li class="nothing"><?php echo $formText_NothingHasBeenFound_Output;?></li>');
			}
			output_on_ajax_dropdown_result();
		});
	}
	function output_on_ajax_dropdown_result()
	{
		$(output_ajax_dropdown_list).find("li").on('click', function(){
			var _this = this;
			if(!$(_this).is('.nothing'))
			{
				$(output_ajax_dropdown).val($(_this).data('code'));
			}
			$(output_ajax_dropdown).removeClass('open');
			$(_this).parent().hide();
		});
	}
    </script>
<?php } else {
    echo $formText_CustomerMissing_output;
}?>
