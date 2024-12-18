<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_POST['action']) and $_POST['action'] == 'delete')
{
	$o_query = $o_main->db->query('select id from sys_tagrelation where tagID = ?', array($_POST['selectedtagid']));
	$o_query2 = $o_main->db->query('select id from sys_tag where parentID = ?', array($_POST['selectedtagid']));
	if((!$o_query || ($o_query && $o_query->num_rows() == 0)) && (!$o_query2 || ($o_query2 && $o_query2->num_rows() == 0)))
	{
		$o_main->db->query("delete from sys_tagcontent where sys_tagID = ?", array($_POST['selectedtagid']));
		$o_main->db->query("delete from sys_tag where id = ?", array($_POST['selectedtagid']));
	}
} else {
	$o_query = $o_main->db->query('select id from sys_tag where parentID = ?', array($_GET['selectedtagid']));
	$o_query2 = $o_main->db->query('select tagID from sys_tagrelation where tagID = ?', array($_GET['selectedtagid']));
	if($o_query && $o_query->num_rows()>0)
	{
		?><h3><?php echo $_GET['label_errorparent'];?></h3>
		<div class="<?php echo $_GET['className'];?>_btn"><a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="open_tag_popup_<?php echo $_GET['className'];?>();"><?php echo $_GET['label_OkButton'];?></a></div><?php
	} else if($o_query2 && $o_query2->num_rows()>0)
	{
		?><h3><?php echo $_GET['label_errorusing'];?></h3>
		<div class="<?php echo $_GET['className'];?>_btn"><a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="open_tag_popup_<?php echo $_GET['className'];?>();"><?php echo $_GET['label_OkButton'];?></a></div><?php
	} else {
		?><h3><?php echo $_GET['label_confirm'];?></h3>
		<div class="<?php echo $_GET['className'];?>_btn"><a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="do_action_<?php echo $_GET['className'];?>(this,'delete');"><?php echo $_GET['label_YesButton'];?><input type="hidden" class="tagid" value="0"><input type="hidden" class="selectedtagid" value="<?php echo $_GET['selectedtagid'];?>"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="<?php echo $_GET['className'];?>_btn bold script" href="javascript:;" onClick="open_tag_popup_<?php echo $_GET['className'];?>();"><?php echo $_GET['label_NoButton'];?></a></div><?php
	}
}
