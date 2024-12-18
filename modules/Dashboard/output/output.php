<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
/* ALLOWED INCLUDES */
$v_include = array(
	"ajax",
	"list",
    "details"
);

// $accountConfigData = mysql_fetch_assoc(mysql_query("SELECT * FROM picturegallery_accountconfig"));
$v_include_default = 'list';

if(!function_exists("include_local")) include(__DIR__."/../input/includes/fn_include_local.php");

$o_query = $o_main->db->query("SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC");
$v_row = $o_query ? $o_query->row_array() : array();
$s_default_output_language = $v_row['languageID'];
$s_original_value = $choosenListInputLang;
$choosenListInputLang = $s_default_output_language;
include(__DIR__."/../input/includes/readInputLanguage.php");
$choosenListInputLang = $s_original_value;

$headmodule = "";
$submods = $v_module_main_tables = array();
if($findBase = opendir(__DIR__."/../input/settings/tables"))
{
	while($writeBase = readdir($findBase))
	{
		$fieldParts = explode(".",$writeBase);
		if($fieldParts[1] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
		{
			$submods[] = $fieldParts[0];
			$vars = include_local(__DIR__."/../input/settings/tables/".$fieldParts[0].".php", $v_language_variables);

			if($vars['tableordernr'] == "1")
			{
				$headmodule = $fieldParts[0];
				$v_module_main_tables[1] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
			else if($vars['moduleMainTable'] == "1" && intval($vars['moduleTableAccesslevel'])<=$fw_session['developeraccess'])
			{
				$l_id = intval($vars['tableordernr']);
				if(array_key_exists($vars['tableordernr'], $v_module_main_tables)) $l_id += 20;
				$v_module_main_tables[$l_id] = array($fieldParts[0], $vars['preinputformName'], $vars['moduletype']);
			}
		}
	}
	if($headmodule == "")
	{
		$headmodule = $submods[0];
	}
	if(count($v_module_main_tables)==0)
	{
		$vars = include_local(__DIR__."/../input/settings/tables/".$submods[0].".php", $v_language_variables);
		$v_module_main_tables[1] = array($submods[0], $vars['preinputformName'], $vars['moduletype']);
	}
	if(is_file(__DIR__."/../input/settings/tables/".$headmodule.".php")) include(__DIR__."/../input/settings/tables/".$headmodule.".php");
	closedir($findBase);
}
$submodule = $headmodule;

include(__DIR__."/includes/readOutputLanguage.php");

if(isset($_GET['inc_obj']) && in_array($_GET['inc_obj'], $v_include)) $s_inc_obj = $_GET['inc_obj']; else $s_inc_obj = $v_include_default;

$o_query = $o_main->db->query("SELECT * FROM frontpage_accountconfig".(isset($variables->fw_session['frontpage_config']) && $variables->fw_session['frontpage_config'] != '' ? " WHERE id = '".$o_main->db->escape_str($variables->fw_session['frontpage_config'])."'" : ""));
$frontpage_accountinfo = $o_query ? $o_query->row_array() : array();
$topImage = json_decode($frontpage_accountinfo['topImage']);
if(count($topImage) < 0){
	ob_start();
	if(count($v_module_main_tables)>0)
	{
		?><ul class="list-inline"><?php
		$v_keys = array_keys($v_module_main_tables);
		sort($v_keys);
		foreach($v_keys as $l_key)
		{
			?><li<?php echo ($v_module_main_tables[$l_key][0]==$submodule?' class="active"':'');?>><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$v_module_main_tables[$l_key][0].(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(!is_numeric($v_module_main_tables[$l_key][2])?"&folderfile=output&folder=".$v_module_main_tables[$l_key][2]:"");?>" class="optimize"><?php echo ($v_module_main_tables[$l_key][1]!=""?$v_module_main_tables[$l_key][1]:$v_module_main_tables[$l_key][0]);?></a></li><?php
		}
		?></ul><?php
	}
	$ob_module_head = ob_get_clean();
	$fw_module_head = $ob_module_head;
}
// $systemAdmin = false;
//
// $response = json_decode(APIconnectorUser("companyaccessbycompanyidget", $variables->loggID, $variables->sessionID, array('COMPANY_ID'=>$companyID, 'GET_GROUPS'=>1)));
// $v_membersystem = array();
// foreach($response->data as $writeContent)
// {
//     array_push($v_membersystem, $writeContent);
// }
//
// //current logged in user groups
// foreach($v_membersystem as $member){
//     if($variables->loggID == $member->username) {
//         foreach($member->groups as $groupSingle){
//             if($groupSingle->name == "System admin") {
//                 $systemAdmin = true;
//             }
//         }
//     }
// }

//set access elements
$v_current_module=explode('/modules/', __DIR__);
$s_current_module=$v_current_module[1];

$v_current_module=explode('/', $s_current_module);
$s_current_module=$v_current_module[0];
$s_current_module_folder=$v_current_module[1];

include_once(__DIR__."/includes/readAccessElements.php");
if($s_inc_obj != "ajax")
{
?>
<div id="output-container">
	<?php
	if($s_inc_obj == "list") {
		?>
		<?php if(count($topImage) >= 0 && $s_group_id == 0){ ?>
			<div class="output-top-image fw_module_head_color">
				<?php if(count($topImage) > 0) {?>
					<img src="<?php echo $extradomaindirroot.$topImage[0][1][0]; ?>" alt=""/>
				<?php } ?>
				<?php if($frontpage_accountinfo['activateTopImageTitle']) {?>
					<div class="output-top-image-text"><?php echo $formText_TopImageTitle_output;?></div>
				<?php } ?>

		        <?php if($accessElementAllow_EditTopImage) {?>
		            <div class="editPageCoverBtn<?php if(count($topImage)==0) echo ' visible';?>"><?php echo $formText_changeBannerImage_output;?></div>
		        <?php } ?>
			</div>
		<?php } ?>
		<div class="output-left-right-wrapper">
			<div class="output-left fw_col_left">
				<?php
				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? ORDER BY sortnr", array($variables->loggID));
				$elementsAdded = $o_query ? $o_query->result_array() : array();
				// foreach($elementsAdded as $elementAdded) {
				// 	array_push($paths_added, $elementAdded['dashboard_path']);
				// }

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 0 ORDER BY sortnr", array($variables->loggID));
				$page1Elements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 2 ORDER BY sortnr", array($variables->loggID));
				$page1RightElements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 1 ORDER BY sortnr", array($variables->loggID));
				$page2Elements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 3 ORDER BY sortnr", array($variables->loggID));
				$page2RightElements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 4 ORDER BY sortnr", array($variables->loggID));
				$page1FullElements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = 5 ORDER BY sortnr", array($variables->loggID));
				$page2FullElements = $o_query ? $o_query->result_array() : array();

				$o_query = $o_main->db->query("SELECT * FROM dashboard_usersettings WHERE username = ? AND display_on = -1 ORDER BY sortnr", array($variables->loggID));
				$pageHiddenElements = $o_query ? $o_query->result_array() : array();

				if(count($elementsAdded) == 0){
					$elementCount = 0;
					$o_query = $o_main->db->query("SELECT * FROM moduledata");
					$modulesToShow = $o_query ? $o_query->result_array() : array();
					foreach($modulesToShow as $moduleSingle){
						if(file_exists(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/output.php")){
							$elementCount++;
						} else {
							if(is_dir(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/")){
								$directories = glob(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/*", GLOB_ONLYDIR);
								if(count($directories) > 0){
									foreach($directories as $directory){
										if(file_exists($directory."/output.php")){
											$elementCount++;
										}
									}
								}
							}
						}
					}
					$halfWay = round($elementCount/2);
					$currentElementCount = 0;
					foreach($modulesToShow as $moduleSingle){
						if(file_exists(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/output.php")){
							$path = $moduleSingle['name']."/output_dashboard";
							if(!in_array($path, $paths_added)) {
								if($currentElementCount >= $halfWay){
									$page1RightElements[] = array('dashboard_path'=>$path);
								} else {
									$page1Elements[] = array('dashboard_path'=>$path);
								}
								$currentElementCount++;
							}
						} else {
							if(is_dir(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/")){
								$directories = glob(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/*", GLOB_ONLYDIR);
								if(count($directories) > 0){
									foreach($directories as $directory){
										if(file_exists($directory."/output.php")){
											$path = $moduleSingle['name']."/output_dashboard/".basename($directory);
											if(!in_array($path, $paths_added)) {

												$dashboardFullWidth = false;
												include(__DIR__."/../../".$path."/settings.php");
												if($dashboardFullWidth) {
													$page1FullElements[] = array('dashboard_path'=>$path);
												} else {
													if($currentElementCount >= $halfWay){
														$page1RightElements[] = array('dashboard_path'=>$path);
													} else {
														$page1Elements[] = array('dashboard_path'=>$path);
													}
												}
												$currentElementCount++;
											}
										}
									}
								}
							}
						}
					}
				}

				$paths_added = array();
				foreach($page1Elements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				foreach($page1RightElements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				foreach($page2Elements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				foreach($page2RightElements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				foreach($page1FullElements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				foreach($page2FullElements as $elementAdded) {
					array_push($paths_added, $elementAdded['dashboard_path']);
				}
				$paths_hidden = array();
				foreach($pageHiddenElements as $elementHidden) {
					array_push($paths_hidden, $elementHidden['dashboard_path']);
				}
				?>
				<div class="dashboard_menu">
					<?php if(count($page1Elements) > 0 && (count($page2Elements) > 0 || count($page2RightElements) > 0)) { ?>
						<div class="dashboard_menu_item active" data-page="1"><?php echo $formText_Page1_output;?></div>
					<?php } ?>
					<?php if(count($page2Elements) > 0 || count($page2RightElements) > 0) { ?>
						<div class="dashboard_menu_item" data-page="2"><?php echo $formText_Page2_output;?></div>
					<?php } ?>
					<div class="dashboard_edit"><?php echo $formText_EditDashboard_output;?></div>
					<div class="clear"></div>
				</div>

				<div class="page_wrapper page_wrapper_1">
					<?php if(count($page1FullElements) > 0) { ?>
						<div class="full_width_column">
							<?php
							foreach($page1FullElements as $page1Element) {
								$path = $page1Element['dashboard_path'];
								$directory = __DIR__."/../../".$path;
								if(file_exists($directory."/output.php")){
									include($directory."/output.php");
								}
							}
							?>
						</div>
					<?php } ?>
					<div class="page_left_column">
						<?php
						foreach($page1Elements as $page1Element) {
							$path = $page1Element['dashboard_path'];
							$directory = __DIR__."/../../".$path;
							if(file_exists($directory."/output.php")){
								include($directory."/output.php");
							}
						}
						?>

						<?php

						$o_query = $o_main->db->query("SELECT * FROM moduledata");
						$modulesToShow = $o_query ? $o_query->result_array() : array();
						foreach($modulesToShow as $moduleSingle){
							if(file_exists(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/output.php")){
								$path = $moduleSingle['name']."/output_dashboard";
		                        if(!in_array($path, $paths_added) && !in_array($path, $paths_hidden)) {
									include(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/output.php");
								}
							} else {
								if(is_dir(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/")){
									$directories = glob(__DIR__."/../../".$moduleSingle['name']."/output_dashboard/*", GLOB_ONLYDIR);
									if(count($directories) > 0){
										foreach($directories as $directory){
											if(file_exists($directory."/output.php")){
		                                        $path = $moduleSingle['name']."/output_dashboard/".basename($directory);
		                                        if(!in_array($path, $paths_added) && !in_array($path, $paths_hidden)) {
													include($directory."/output.php");
												}
											}
										}
									}
								}
							}
						}
						?>
					</div>
					<div class="page_right_column">
						<?php
						foreach($page1RightElements as $page1Element) {
							$path = $page1Element['dashboard_path'];
							$directory = __DIR__."/../../".$path;
							if(file_exists($directory."/output.php")){
								include($directory."/output.php");
							}
						}
						?>
					</div>
				</div>
				<?php if(count($page2Elements) > 0 || count($page2RightElements) > 0 || count($page2FullElements) > 0) { ?>

					<div class="page_wrapper page_wrapper_2">
						<?php if(count($page2FullElements) > 0) { ?>
							<div class="full_width_column">
								<?php
								foreach($page2FullElements as $page1Element) {
									$path = $page1Element['dashboard_path'];
									$directory = __DIR__."/../../".$path;
									if(file_exists($directory."/output.php")){
										include($directory."/output.php");
									}
								}
								?>
							</div>
						<?php } ?>
						<div class="page_left_column">
							<?php
							foreach($page2Elements as $page2Element) {
								$path = $page2Element['dashboard_path'];
								$directory = __DIR__."/../../".$path;
								if(file_exists($directory."/output.php")){
									include($directory."/output.php");
								}
							}
							?>
						</div>
						<div class="page_right_column">
							<?php
							foreach($page2RightElements as $page1Element) {
								$path = $page1Element['dashboard_path'];
								$directory = __DIR__."/../../".$path;
								if(file_exists($directory."/output.php")){
									include($directory."/output.php");
								}
							}
							?>
						</div>
					</div>
				<?php } ?>
		    </div>
				<?php /*<div class="output-right fw_col_right">

				<div class="output-category-block">
					<div class="output-title fw_module_head_color">
						<?php echo $formText_LatestUpdates_output;?>
					</div>
					<div class="rs_box extra">
						<div class="output-content" id="latestUpdatesBlock">
							<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
						</div>
					</div>
				</div>
				<div class="output-category-block">
					<div class="output-title fw_module_head_color">
						<?php echo $formText_UpcomingUpdates_output;?>
					</div>
					<div class="rs_box extra">
						<div class="output-content" id="upcomingUpdatesBlock">
							<div class="loading"><div class="lds-ring"><div></div><div></div><div></div><div></div></div></div>
						</div>
					</div>
				</div>
			</div>*/?>
			<div class="clear"></div>
		</div>
		<?php
	} else {
		if(is_file(__DIR__."/includes/".$s_inc_obj.".php")) include(__DIR__."/includes/".$s_inc_obj.".php");
	}
	?>

</div>

<div id="popupeditbox" class="popupeditbox">
	<span class="button b-close fw_popup_x_color"><span>X</span></span>
	<div id="popupeditboxcontent"></div>
</div>
<?php require_once __DIR__ . '/output_javascript.php'; ?>
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
		var imgLoaded = 0;
		var popupImages = $(".popupeditbox .fwaFileupload_FilesList_Files img");
		var totalImages = popupImages.length;
		$(window).resize();
		popupImages.one("load", function() {
			imgLoaded++;
			if(imgLoaded == totalImages) {
				$(window).resize();
			}
		}).each(function() {
		  if(this.complete) {
			  $(this).load(); // For jQuery < 3.0
			  // $(this).trigger('load'); // For jQuery >= 3.0
		  }
		});
	},
	onClose: function(){
		if($(this).hasClass("close-reload")){
			<?php if(intval($_GET['postid']) > 0) {?>
				loadView("list", {postid: '<?php echo intval($_GET['postid']);?>'});
			<?php } else { ?>
				loadView("list");
			<?php } ?>
		}
		$(this).removeClass('opened');
	}
};
$(".dashboard_menu_item").off("click").on("click", function(e){
	e.preventDefault();
	var page = $(this).data("page");
	$(".page_wrapper").hide();
	$(".page_wrapper_"+page).show();
	$(".dashboard_menu_item").removeClass("active");
	$(this).addClass("active");
})
$(".dashboard_edit").off("click").on("click", function(e){
	e.preventDefault();
	var data = {
    };
    ajaxCall('dashboard_edit', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
})
// window.outputModuleViewportSettings = {
// 	minWidthDesktop: 550
// }
$(".test_class").on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('test', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});

/*
//load latesupdates
ajaxCall('getLatestUpdates', {}, function(json) {
	setTimeout(function() {
		$('#latestUpdatesBlock').html(json.html);
	}, 500);
});
ajaxCall('getUpcomingUpdates', {}, function(json) {
	setTimeout(function() {
		$('#upcomingUpdatesBlock').html(json.html);
	}, 500);
});*/

$(".editPageCoverBtn").on('click', function(e){
    e.preventDefault();
    var data = {
        cid: '<?php echo $cid;?>'
    };
    ajaxCall('editPageCover', data, function(json) {
        $('#popupeditboxcontent').html('');
        $('#popupeditboxcontent').html(json.html);
        out_popup = $('#popupeditbox').bPopup(out_popup_options);
        $("#popupeditbox:not(.opened)").remove();
    });
});
</script>
<?php
} else {
	$s_inc_act = "";
	if(is_string($_GET['inc_act'])) $s_inc_act = $_GET['inc_act'];
	if(is_file(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php")) include(__DIR__."/includes/".$s_inc_obj.".".$s_inc_act.".php");
}
?>
