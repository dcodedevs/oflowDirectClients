<?php
if($variables->developeraccess >= 20)
{
	?><div class="module-manager">
		<div><?php echo $formText_Test_fieldtype;?></div>
		<form class="mm-table-history" name="mm-table-history" method="post" action="<?php echo $extradir."/addOn_include/addlibrary.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&";?>table_history=1">
			<input type="hidden" name="table_history" value="1" />
			<input type="hidden" class="mm_recreate_all_triggers" name="recreate_all_triggers" value="0" />
			<table class="table table-striped table-hover table-condensed max-col">
			<thead>
				<tr>
					<th><?php echo $formText_TableName_input;?></th>
					<th align="right">
						<div style="text-align:right;">
							<?php echo $formText_LogHistory_input;?>
							<div class="mm-history-toggle-all"><?php echo $formText_SelectAllNone_Modulemanager;?></div>
						</div>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php
				$o_table = $o_main->db->query("SHOW FULL TABLES");
				if($o_table && $o_table->num_rows()>0)
				foreach($o_table->result_array() as $v_table)
				{
					$s_type = array_pop($v_table);
					$s_table = array_pop($v_table);
					
					if('BASE TABLE' != $s_type) continue;
					
					$b_active = FALSE;
					$b_enabled = FALSE;
					$o_column = $o_main->db->query("SHOW COLUMNS FROM ".$s_table);
					if($o_column && $o_column->num_rows()>0)
					foreach($o_column->result_array() as $v_column)
					{
						/*[Field] =&gt; moduleID
						[Type] =&gt; int
						[Null] =&gt; YES
						[Key] =&gt; 
						[Default] =&gt; 
						[Extra] =&gt; */
						if('PRI' == $v_column['Key'])
						{
							$b_enabled = TRUE;
						}
					}
					$o_check = $o_main->db->query("SHOW TRIGGERS WHERE `TABLE` = '".$s_table."' AND EVENT = 'UPDATE' AND TIMING = 'AFTER' AND STATEMENT LIKE '%sys_content_history%'");
					$b_active = ($o_check && $o_check->num_rows()>0);
					?>
					<tr class="mm-click-helper<?php echo ($b_enabled?'':' bg-danger');?>">
						<td><?php echo $s_table;?></td>
						<td align="right">
							<?php if($b_enabled) { ?>
							<input type="checkbox" class="changer"<?php echo ($b_active?' checked':'');?> onChange="$(this).parent().find('input.real').val($(this).is(':checked')?1:0);">
							<input type="hidden" name="history_table[]" value="<?php echo $s_table;?>">
							<input type="hidden" class="real" name="history_table_sel[]" value="<?php echo ($b_active?'1':'0');?>">
							<?php } ?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			</table>
			<div class="row">
				<div class="col-xs-10">
				<input name="submbtn" class="btn btn-success" value="<?php echo $formText_save_input;?>" type="submit">
				<button type="button" class="btn btn-default mm-recreate-triggers"><?php echo $formText_RecreateAllTriggers_Modulemanager;?></button>
				</div>
				<div class="col-xs-2">
				<a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&folder=input&folderfile=input";?>" class="btn btn-default optimize"><?php echo $formText_Cancel_input;?></a>
				</div>
			</div>
		</form>
	</div>
	<script type="text/javascript">
	$(function(){
		$('.mm-history-toggle-all').off('click').on('click', function(e){
			if($('form.mm-table-history input.changer:checked').length > 0){
				$('form.mm-table-history input.changer:checked').removeProp('checked');
				$('form.mm-table-history input.real').val('0');
			} else {
				$('form.mm-table-history input.changer').prop('checked', true);
				$('form.mm-table-history input.real').val('1');
			}
		});
		$('tr.mm-click-helper td').off('click').on('click', function(e){
			if(e.target.nodeName == 'TD'){
				$(this).parent().find('input.changer').trigger('click');
			}
		});
		$('.mm-recreate-triggers').off('click').on('click', function(e){
			$('input.mm_recreate_all_triggers').val(1);
			$('form.mm-table-history').submit();
		});
	});
	</script>
	<style type="text/css">
	tr.mm-click-helper td { cursor:pointer; }
	.mm-history-toggle-all { cursor:pointer; color:#46b2e2; }
	</style>
	<?php
} else {
	?><div id="hovedfeltStrek"><table style="width:100%"><tr><td class="notAccessField"><?php echo $formText_YouHaveNoAccessToThisModule_input;?></td></tr></table></div><?php
}
