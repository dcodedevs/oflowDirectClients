<?php
$extradir = "../fw/getynet_fw/modules/users";
$page = 1;
require_once __DIR__ . '/list_btn.php';
$includeFile = __DIR__."/../../../../fw/getynet_fw/modules/users/output/output_javascript.php";
if(is_file($includeFile)) include($includeFile);

if(isset($_GET['groupID'])){
    $hideExtended =true;
    include(__DIR__."/../../../../fw/getynet_fw/modules/users/output/outputeditgroup.php");
    ?>
    <?php
} else {
?>
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">
		<div class="p_content">
            <div class="p_tableFilter">
            <div class="p_tableFilter_left">
                <span class="fas fa-users fw_icon_title_color"></span>
                <?php echo $formText_groupsBigTitle_usersOutput;?>
                <a class="optimize fw_text_link_color" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=output&inc_obj=access_group_list&groupID=";?>">
                    + <?php echo $formText_addGroup_usersOutputLink;?>
                </a>
            </div>
        </div>
			<div class="p_pageContent">
                <table class="table table-striped table-hover table-condensed">
        			<thead>
        				<tr>
        					<th width="400"><?php echo $formText_listheaderName_usersOutputList;?></th>
        					<th width="235"><?php echo $formText_listheaderAccesslevel_usersOutputList;?></th>
        					<th width="30"><?php echo $formText_listheaderDelete_usersOutputList;?></th>
        				</tr>
        			</thead>
        			<tbody>
        			<?php
        			$response = json_decode(APIconnectorUser("groupcompanyaccessbymoduleidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'MODULE_ID'=>'0')));
        			foreach($response->data as $writeContent)
        			{
        				?><tr>
        					<td width="400">
        						<a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$_GET['module']."&modulename=".$modulename."&folder=output&folderfile=output&inc_obj=access_group_list&groupID=".$writeContent->id;?>"<?php echo ((isset($writeContent->deactivated) && $writeContent->deactivated==1)?' style="color:#BBBBBB;"':'');?>><?php echo $writeContent->groupname;?></a>
        					</td>
        					<td width="235"<?php echo ((isset($writeContent->deactivated) && $writeContent->deactivated==1)?' style="color:#BBBBBB;"':'');?>><?php
        					if($writeContent->accesslevel == 1)
        					{
        						print $formText_listvalueAll_usersOutputList;
        					}
        					elseif($writeContent->accesslevel == 2)
        					{
        						print $formText_listvalueRestricted_usersOutputList;
        					}
        					elseif($writeContent->accesslevel == 0)
        					{
        						print $formText_listvalueNoAccess_usersOutputList;
        					}
        					?></td>
        					<td width="30"><?php
        					if($writeContent->type < 1)
        					{
        						?><form name="update" action="<?php echo $extradir;?>/output/outputreg.php" method="post" id="fw_useradmin_deletegroup_<?php echo $writeContent->id; ?>">
        						<input type="hidden" name="pageID" value="<?php echo $_GET['pageID'];?>">
        						<input type="hidden" name="accountname" value="<?php echo $_GET['accountname'];?>">
        						<input type="hidden" name="companyID" value="<?php echo $_GET['companyID'];?>">
        						<input type="hidden" name="caID" value="<?php echo $_GET['caID'];?>">
        						<input type="hidden" name="groupID" value="<?php echo $writeContent->id; ?>" />
        						<input type="hidden" name="deletetestgroup" value="" id="fw_useradmin_deletegroupmark_<?php echo $writeContent->id;?>"/>
        						<input type="hidden" name="groupname" value="<?php echo $writeContent->groupname; ?>" />
        						<input type="hidden" name="extradir" value="<?php echo $extradir; ?>" />
        						<input type="hidden" name="module" value="<?php echo $module; ?>" />
        						<input type="hidden" name="updateuser" value="1" />
        						<input type="hidden" name="returnurl" value="<?php echo (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); ?>" />
        						<input type="image" onClick="fw_useradmin_deletegroupconfirm('<?php echo $writeContent->id; ?>','<?php echo $writeContent->groupname; ?>'); return false;" src="<?php echo $extradir;?>/output/elementsOutput/delete_icon.gif" width="20" alt="" border="0" />
        						</form><?php
        					}
        					?></td>
        				</tr><?php
        			}
        			?>
        			</tbody>
        		</table>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<?php $list_filter = isset($_GET['list_filter']) ? ($_GET['list_filter']) : 'all'; ?>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
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
            	loadView("list");
            }
          // window.location.reload();
        }
		$(this).removeClass('opened');
	}
};

$(document).ready(function() {
    var page = '<?php echo $page?>';


});
$(function(){
	// setTimeout(function(){
	// 	var currentCount = 0;
	// 	var data = {
	// 		department_filter: $('.filterDepartment').val(),
	// 		search_filter: $('.searchFilter').val(),
	// 		list_filter: '<?php echo $list_filter;?>',
	// 		page: 1,
	// 	};
	// 	ajaxCall('list', data, function(json) {
	// 		$('.p_pageContent').html(json.html);
	// 	}, true);
	// }, 50);
})


</script>
