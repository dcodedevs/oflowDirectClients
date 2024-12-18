<?php
if($variables->developeraccess >= 20)
{
	?><div class="module-manager">
		<div><?php echo $formText_DragAndDropItemsToChangeOrder_fieldtype;?></div>
		<form  name="sortableListForm" method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&";?>editOrder=1">
			<input type="hidden" name="editOrder" value="1" />
			<ul id="sortable">
				<?php
				$o_query = $o_main->db->query('select * from moduledata order by modulemode, ordernr');
				if($o_query && $o_query->num_rows()>0)
				foreach($o_query->result_array() as $row)
				{
					?><li class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span><input type="hidden" name="uniqueID[]" value="<?php echo $row['uniqueID']; ?>" /><?php echo $row['name'];?></li><?php
				}
				?>
			</ul>
			<div><input name="submbtn" class="btn btn-success" value="<?php echo $formText_save_input;?>" type="submit"></div>
		</form>
	</div>
	<script type="text/javascript">
	$(function() {
		$("#sortable").sortable().disableSelection();
	});
	</script>
	<?php
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?php echo $formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
}
?>