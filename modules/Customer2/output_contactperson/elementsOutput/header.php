<?php
$formText_TestLanguageVariable_Output="";
$protectFilter = "";
$s_table = $variables->contentTable;
//if($variables->loggID=="") $s_protect = ' AND '.$s_table.'.protected <> 1';
if($o_main->db->table_exists($s_table.'content'))
{
	$s_sql = 'SELECT c.id cid, c.*, cc.* FROM '.$s_table.' c JOIN '.$s_table.'content cc ON cc.'.$s_table.'ID = c.id AND cc.languageID = '.$o_main->db->escape($variables->languageID).' WHERE c.id = '.$o_main->db->escape($variables->contentID).$s_protect;
} else {
	$s_sql = 'SELECT c.id cid, c.* FROM '.$s_table.' c WHERE c.id = '.$o_main->db->escape($variables->contentID).$s_protect;
}
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $o_content = $o_query->row();

include_once(__DIR__."/includes/readOutputLanguage.php");
?>
<div id="output-content-container">
<div id="p_container" class="p_container <?php echo $folderName; ?>">
	<div class="p_containerInner">		
			<div class="output-top-image">		
					<img src="https://s16server/accounts/svenssonNoklebyInsNo/modules/QualitySystem/output/elementsOutput/sys.jpg" alt=""/>
				<div class="output-top-image-text">Quality System</div>
			</div>
	</div>
</div>
</div>
<div class="p_headerLine">    	
		<div class="btnStyle QualityManual fw_tab_color <?php if($_GET['folder'] == "quality") echo 'active';?>">
			<div class="plusTextBox">
			<div class="text"><?php echo $formText_QualityManual_Input; ?></div>
		</div>
		<div class="clear"></div>
	</div>
		<div class="btnStyle Suggestionbox fw_tab_color <?php if($_GET['folder'] == "suggestion") echo 'active';?>">
			<div class="plusTextBox">
			<div class="text"><?php echo $formText_Suggestionbox_Input; ?></div>
		</div>
		<div class="clear"></div>
	</div>
		<div class="btnStyle ISOlcdeerrtification fw_tab_color <?php if($_GET['folder'] == "cdeerrtification") echo 'active';?>">
			<div class="plusTextBox">
			<div class="text"><?php echo $formText_ISOlcdeerrtification_Input; ?></div>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="popupeditbox" class="popupeditbox">
		<span class="button b-close fw_popup_x_color"><span>X</span></span>
		<div id="popupeditboxcontent"></div>
	</div>
	<script type="text/javascript">
	function output_reload_page()
	{
		fw_load_ajax('<?php echo $s_page_reload_url;?>', '', false);
	}
	</script>

        
	</div>
</div>
</div>



	<!--<div id="popupeditbox" class="popupeditbox opened addfileform" style="display:none; margin-top:20px;">
		<!--span class="button b-close"><span>X</span></span
		<div id="popupeditboxcontent">
			<div class="fwaFileupload">
				<h2>Add file</h2>
				<div class="fwaFileupload_Files">
				<div class="fwaFileupload_FilesBrowseDrop">
					<div class="fwaFileupload_FilesBrowseDrop_Title">
						Drag and drop files here			
						</div>
					<div class="fwaFileupload_FilesBrowseDrop_Icon">
						<span class="glyphicon glyphicon-inbox"></span>
					</div>
					<div class="fwaFileupload_FilesBrowseDrop_Browse">
						<div class="fwaFileupload_FilesBrowseDrop_Browse_Or">Or</div>
						<a href="">Browse files on your computer</a>
					</div>
				</div>
				<div class="fwaFileupload_FilesList">
					<ul class="fwaFileupload_FilesList_Files"></ul>
				</div>
				<input id="" type="file" name="" multiple="">
			</div>
			<div class="footerbtn">
				<a href="#" class="canbtn">Cancel</a> <a href="#" class="savebtn">Save</a>
			</div>
		</div>
		</div>
	</div>
	
		<div id="popupeditbox" class="popupeditbox opened editorform" style="display:none; margin-top:20px;">
		<!--span class="button b-close"><span>X</span></span
		<div id="popupeditboxcontent">
			<div class="fwaFileupload">
				<h2>Edit text</h2>
				<div class="editorpalce"></div>
			<div class="footerbtn">
				<a href="#" class="canbtn">Cancel</a> <a href="#" class="savebtn">Save</a>
			</div>
		</div>
		</div>
	</div>-->
	
	
	
<script type="text/javascript">
$(".QualityManual").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=quality"; ?>', false, true);
});
$(".Suggestionbox").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=suggest"; ?>', false, true);
});
$(".ISOlcdeerrtification").on('click', function(e){
	e.preventDefault();
    fw_load_ajax('<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=cdeerrtification"; ?>', false, true);
});
</script>
<style>
	.p_headerLine .btnStyle.addEditCustomerGroup {
		margin-left: 40px;
	}
	.p_headerLine .btnStyle.addEditSelfDefinedFields {
		margin-left: 40px;
	}
</style>
<?php require_once __DIR__ . '/output_javascript.php'; ?>
	

