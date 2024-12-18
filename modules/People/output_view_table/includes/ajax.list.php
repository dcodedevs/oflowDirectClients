
<script type="text/javascript">
$(function(){
	$(".addTable").off("click").on("click", function(e){
		e.preventDefault();
		var data = { };
	    ajaxCall('add_table_view', data, function(obj) {
	        $('#popupeditboxcontent').html('');
	        $('#popupeditboxcontent').html(obj.html);
	        out_popup = $('#popupeditbox').bPopup(out_popup_options);
	        $("#popupeditbox:not(.opened)").remove();
	    });
	})
})
</script>
<?php
if(isset($_GET['page'])) {
	$page = $_GET['page'];
}
if(isset($_POST['page'])) {
	$page = $_POST['page'];
}
if(intval($page) == 0){
	$page = 1;
}
$rowOnly = $_POST['rowOnly'];
$perPage = 100;


$sql = "SELECT * FROM table_viewer WHERE moduleID = ?";
$o_query = $o_main->db->query($sql, array($moduleID));
$table_viewers = $o_query ? $o_query->result_array() : array();
if(count($table_viewers) > 0){
	$currentTable = $table_viewers[0];
}
if(isset($_GET['viewer_id'])){
	$currentTable = array();
}
foreach($table_viewers as $table_viewer){
	$activeClass = "";
	if($table_viewer['id'] == $_GET['viewer_id']){
		$currentTable = $table_viewer;
	}
	if($currentTable['id'] == $table_viewer['id']){
		$activeClass= " active";
	}
	?>
	<div class="table_title <?php echo $activeClass?>" data-id="<?php echo $table_viewer['id']?>"><?php echo $table_viewer['table_name']?></div>

	<?php
}
?>
<div class="addTable btnStyle" style="float: left; color: #46b2e2; cursor: pointer; padding: 15px 10px;">
	<div class="plusTextBox active">
		<div class="text"><?php echo $formText_AddTable_Output; ?></div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<div class="clear"></div>
<style>
.table_title {
	float: left;
	cursor: pointer;
	padding: 5px 10px;
	border: 1px solid #cecece;
	margin-top: 10px;
	margin-left: 10px;
}
.table_title.active {
	background: #cecece;
}
.add_subtables {
	color: #46b2e2;
	cursor: pointer;
	margin-left: 15px;
	margin-top: 10px;
}
.edit_subtable {
	color: #46b2e2;
	cursor: pointer;
}
.delete_subtable {
	color: #46b2e2;
	cursor: pointer;
	margin-left: 5px;
}
.fieldChooseWrapper {
	display: none;
}
</style>
<?php
if($currentTable){
	$sql = "SHOW COLUMNS FROM ".$currentTable['table_name'];
	$o_query = $o_main->db->query($sql, array($moduleID));
	$columns = $o_query ? $o_query->result_array() : array();


	$sql = "SELECT * FROM table_viewer_sub WHERE table_viewer_id = ?";
	$o_query = $o_main->db->query($sql, array($currentTable['id']));
	$subTables = $o_query ? $o_query->result_array() : array();
	$subtable_select = "";
	$subtable_join = "";
	if(count($subTables) > 0){
		foreach($subTables as $subTable) {
			$subtable_select .= ", subtable".$subTable['id'].".".$subTable['table_field']." as subtable".$subTable['id']."_".$subTable['table_field'];
			$subtable_join .= " LEFT OUTER JOIN ".$subTable['table_name']." subtable".$subTable['id']." ON subtable".$subTable['id'].".".$subTable['subtable_field']." = t.".$subTable['parent_field'];
		}
	}
	$sql = "SELECT t.*".$subtable_select." FROM ".$currentTable['table_name']." t
	".$subtable_join."
	WHERE t.content_status < 2";
	$o_query = $o_main->db->query($sql);
	$tableItems = $o_query ? $o_query->result_array() : array();
	?>

	<div class="show_fields"><?php echo $formText_Fields_output;?> (<span class="current_fields"><?php echo count($columns);?></span>/<span class="total_fields"><?php echo count($columns);?></span>)</div>
	<div class="exportBtn fw_button_color "><?php echo $formText_Export_output;?></div>
	<div class="clear"></div>

	<div class="fieldChooseWrapper">
		<div class="subtables">
			<div class="add_subtables"><?php echo $formText_AddSubTable_output;?></div>
			<table class="table">
				<tr>
					<th><?php echo $formText_ParentColumn_Output;?></th>
					<th><?php echo $formText_SubTableName_Output;?></th>
					<th><?php echo $formText_SubTableFieldForParentConnection_Output;?></th>
					<th><?php echo $formText_SubTableFieldNameToDisplayInMainTable_Output;?></th>
					<th></th>
				</tr>
				<?php
				foreach($subTables as $subTable) {
					?>
					<tr>
						<td><?php echo $subTable['parent_field']?></td>
						<td><?php echo $subTable['table_name']?></td>
						<td><?php echo $subTable['subtable_field']?></td>
						<td><?php echo $subTable['table_field']?></td>
						<td>
							<span class="glyphicon glyphicon-pencil edit_subtable" data-id="<?php echo $subTable['id']?>"></span>
							<span class="glyphicon glyphicon-trash delete_subtable" data-id="<?php echo $subTable['id']?>"></span>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>

	    <?php echo $formText_ChooseFields_output;?> <span class="selectAll"><?php echo $formText_SelectAll_output;?></span>
	    <div class="fieldChoose">
			<form class="fieldchooseForm">
				<?php
				foreach($columns as $column) {
					?>
					<div class="fieldChooseRow">
						<input type="checkbox" checked name="fields[]" autocomplete="off" value="<?php echo $column['Field'];?>" id="field<?php echo $column['Field']?>" class="fieldchooseInput"/><label for="field<?php echo $column['Field']?>"><?php echo $column['Field'];?></label>
					</div>
					<?php
				}
				foreach($subTables as $subTable){
					?>
					<div class="fieldChooseRow">
						<input type="checkbox" checked name="fields[]" autocomplete="off" value="<?php echo $subTable['id'];?>" id="field<?php echo $subTable['id']?>" class="fieldchooseInput"/><label for="field<?php echo $subTable['id']?>"><?php echo $subTable['table_name']." ".$subTable['table_field'];?></label>
					</div>
					<?php
				}
				/* foreach($fieldsArray as $fieldItem) {
					?>
					<div class="fieldChooseRow">
						<input type="checkbox" checked name="fields[]" value="<?php echo $fieldItem['id'];?>" id="field<?php echo $fieldItem['id']?>" class="fieldchooseInput"/><label for="field<?php echo $fieldItem['id']?>"><?php echo $fieldItem['name'];?></label>
					</div>
					<?php
					if(count($fieldItem['children']) > 0){
						foreach($fieldItem['children'] as $childrenItem){
							?>
							<div class="fieldChooseRow fieldChooseRowSub fieldChooseRow<?php echo $fieldItem['id'];?>" style="display:block;">
								<input type="checkbox" data-parentvalue="<?php echo $fieldItem['id']?>" checked name="fields[]" value="<?php echo $childrenItem['id'];?>" id="field<?php echo $childrenItem['id']?>" class="fieldchooseInput" autocomplete="off"/><label for="field<?php echo $childrenItem['id']?>"><?php echo $childrenItem['name'];?></label>
							</div>
							<?php
						}
					}
				}*/
				?>
			</form>
	    </div>
		<div class="clear"></div>
	</div>

	<div class="resultTableWrapper">
		<div class="tableWrapper">
			<table class="gtable table table_info table-fixed">
				<thead>
					<tr class="gtable_row table_head">
						<?php
						foreach($columns as $column) {
							?>
							<td class="gtable_cell gtable_cell_head columnForTable column<?php echo $column['Field']?> <?php if(count($column['children']) > 0) { echo 'merged_head'; }?>" <?php if(count($column['children']) > 0) { ?>colspan="<?php echo count($column['children']);?>" <?php } else { ?> rowspan="2"<?php } ?>>
								<?php
									echo ($column['Field']);
								?>
							</td>
							<?php
						}
						foreach($subTables as $subTable){
							?>
							<td class="gtable_cell gtable_cell_head columnForTable column<?php echo $subTable['id']?>">
								<?php
									echo ($subTable['table_name']." ".$subTable['table_field']);
								?>
							</td>
							<?php
						}
						?>
					</tr>
				</thead>
			</table>
			<table class="gtable table table_info">
				<thead>
					<tr class="gtable_row table_head">
						<?php
						foreach($columns as $column) {
							?>
							<td class="gtable_cell gtable_cell_head columnForTable column<?php echo $column['Field']?> <?php if(count($column['children']) > 0) { echo 'merged_head'; }?>" <?php if(count($column['children']) > 0) { ?>colspan="<?php echo count($column['children']);?>" <?php } else { ?> rowspan="2"<?php } ?>>
								<?php
									echo ($column['Field']);
								?>
							</td>
							<?php
						}
						foreach($subTables as $subTable){
							?>
							<td class="gtable_cell gtable_cell_head columnForTable column<?php echo $subTable['id']?>">
								<?php
									echo ($subTable['table_name']." ".$subTable['table_field']);
								?>
							</td>
							<?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach($tableItems as $tableItem) {
						?>
						<tr>
							<?php
							foreach($columns as $column) {
							?>
								<td class="gtable_cell columnForTable column<?php echo $column['Field']?>">
									<?php
										echo ($tableItem[$column['Field']]);
									?>
								</td>
							<?php }
							foreach($subTables as $subTable){
								?>
								<td class="gtable_cell columnForTable column<?php echo $subTable['id']?>">
									<?php
										echo ($tableItem["subtable".$subTable['id']."_".$subTable['table_field']]);
									?>
								</td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<script type="text/javascript">
		$(function(){
			// $('.table_info').dragtable();
			$(".exportBtn").on('click', function(e){
		        e.preventDefault();
				window.open('<?php echo $_SERVER['PHP_SELF']."/../../modules/".$module."/output_view_table/includes/export.php?viewer_id=".$currentTable['id'];?>'+"&"+$(".fieldchooseForm").serialize(), '_blank');
		    });
			$(".add_subtables").off("click").on("click", function(e){
				e.preventDefault();
		        var data = {
					table_id: '<?php echo $currentTable['id'];?>'
		        };
		        ajaxCall('add_subtable', data, function(json) {
		            $('#popupeditboxcontent').html('');
		            $('#popupeditboxcontent').html(json.html);
		            out_popup = $('#popupeditbox').bPopup(out_popup_options);
		            $("#popupeditbox:not(.opened)").remove();
		        });
			})
			$(".edit_subtable").off("click").on("click", function(e){
				e.preventDefault();
		        var data = {
					table_id: '<?php echo $currentTable['id'];?>',
					subtable_id: $(this).data("id")
		        };
		        ajaxCall('add_subtable', data, function(json) {
		            $('#popupeditboxcontent').html('');
		            $('#popupeditboxcontent').html(json.html);
		            out_popup = $('#popupeditbox').bPopup(out_popup_options);
		            $("#popupeditbox:not(.opened)").remove();
		        });
			})
			$(".delete_subtable").off("click").on("click", function(e){
				e.preventDefault();
		        var data = {
					subtable_id: $(this).data("id"),
					action: "deleteTable"
		        };
				bootbox.confirm('<?php echo $formText_ConfirmDelete_output; ?>', function (result) {
                    if (result) {
			        	ajaxCall('add_subtable', data, function(json) {
            				loadView("list", {viewer_id:"<?php echo $currentTable['id'];?>"});
                        });
                    }
                });
			})
			$(".table_title").off("click").on("click", function(){
				var data = {
					viewer_id: $(this).data("id")
				}
				loadView("list", data);
			})
			$(".selectAll").off("click").on("click", function(){
				if($('.fieldchooseForm input:checked').length == $('.fieldchooseForm input').length){
					$('.fieldchooseForm input').prop("checked", false);
					$(".columnForTable").hide();
				} else {
					$('.fieldchooseForm input').prop("checked", true);
					$(".columnForTable").show();
				}
				$(".current_fields").html($('.fieldchooseForm input:checked').length);
			})

			$(".tableWrapper").scroll(function(){
				$(".table-fixed").css({"top":  $(this).scrollTop()+"px"});
			})
			$(".fieldchooseInput").off("change").on("change", function(){
				var value = $(this).val();
				if($(this).is(":checked")){
					$(".column"+value).show();
					$(".fieldChooseRow"+value).show();
					$(".fieldChooseRow"+value+" input").prop("checked", true);

				} else {
					$(".column"+value).hide();
					$(".fieldChooseRow"+value).hide();
					$(".fieldChooseRow"+value+" input").prop("checked", false);
				}
				if($(this).parents(".fieldChooseRow").hasClass("fieldChooseRowSub")){
					var parentValue = $(this).data("parentvalue");
					if($(".fieldChooseRow"+parentValue+" input:checked").length == 0) {
						$("#field"+parentValue+"").click();
					} else {
						$(".gtable_cell_head.merged_head.column"+parentValue).prop("colspan", $(".fieldChooseRow"+parentValue+" input:checked").length);
					}
				}
				$(".current_fields").html($('.fieldchooseForm input:checked').length);
				updateTableHead();
			})
			updateTableHead();
			function updateTableHead() {
				// $(".table-fixed .gtable_cell_head").removeClass("lastel");
				// $(".table-fixed .gtable_cell_head:visible:last").addClass("lastel");
				// $(".table-fixed .gtable_cell_head:visible:first").addClass("first");
				<?php
				foreach($fieldsArray as $fieldItem) {
					?>
					<?php
					foreach($fieldItem['children'] as $childrenItem){
						?>
						$(".table-fixed .gtable_cell_head.children_head.column<?php echo $fieldItem['id'];?>").removeClass("last");
						$(".table-fixed .gtable_cell_head.children_head.column<?php echo $fieldItem['id'];?>:visible:last").addClass("last");
						<?php
					}
				}
				?>
			}
			// $(".loadDefaultFields").click();

		    <?php if(count($default_fields) > 0) { ?>
		        $(".fieldchooseForm input").prop("checked", false).change();
		        <?php foreach($default_fields as $default_field) { ?>
		            $('.fieldchooseForm input[value="<?php echo $default_field->value;?>"]').trigger("click");
		        <?php } ?>
		    <?php } ?>
		})
		function viewport() {
			var e = window, a = 'inner';
			if (!('innerWidth' in window )) {
				a = 'client';
				e = document.documentElement || document.body;
			}
			return { width : e[ a+'Width' ] , height : e[ a+'Height' ] };
		}

		function resizeList(){
			var ww = viewport().width;
			var wh = viewport().height;
			$(".tableWrapper").height("auto");
			var accountHeight = $("#fw_account").height();
			var height = accountHeight - 60 - $(".fw_module_head_wrapper").height() - $(".p_headerLine").height() - $(".p_tableFilter_bottom").height() - 20;

			$(".tableWrapper").height(wh - $(".resultTableWrapper").offset().top-20);
			<?php /* if($_GET['fullsize'] == 1) { ?>
				$(".tableWrapper").height(wh - $(".resultTableWrapper").offset().top-20);
			<?php } else { ?>
				$(".tableWrapper").height(height- $(".resultTableWrapper").offset().top-20);
			<?php }*/ ?>
		}
		$(window).resize(resizeList);
		resizeList();
		$(".show_fields").off("click").on("click", function(){
			$(".fieldChooseWrapper").toggle();
		})
	</script>
	<?php
}
?>
