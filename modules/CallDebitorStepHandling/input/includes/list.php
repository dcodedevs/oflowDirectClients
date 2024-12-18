<?php
require_once(__DIR__.'/class_ListInfo.php');

if($s_input_jumpfirst_link != '')
{
	if(isset($ob_javascript))
	{
		$ob_javascript .= ' $(function(){fw_load_ajax("'.$s_input_jumpfirst_link.'");});';
	} else {
		?><script type="text/javascript" language="javascript"><?php echo '$(function(){fw_load_ajax("'.$s_input_jumpfirst_link.'");});';?></script><?php
	}
	return;
}

$kols = array('#f6f7f8','#FFFFFF');
$soker = new ListInfo();
$soker->Start($submodule);
$start = 0;
$startList = 0;
$searchword = '';
$searchaddon = '';
$orgsearch = '';
$sortefield = '';
$orderByField = '';
$l_list_items_per_page = 100;
$orderByDesc = 'ASC';
$sqlList = '';

$module_multi_table = false;
if($o_main->db->table_exists($soker->mainTable.'content')) $module_multi_table = true;
if(!$o_main->db->table_exists($soker->mainTable)) return;

$linkers = array();
foreach($listFieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[0][] = ${$var};
foreach($listSet2FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[1][] = ${$var};
foreach($listSet3FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[2][] = ${$var};
foreach($listSet4FieldVariables as $var) if(isset(${$var}) && !empty(${$var})) $linkers[3][] = ${$var};
$data_columns = is_countable($linkers[0]) ? count($linkers[0]) : 0;

if(isset($_GET['sortfield']) && $_GET['sortfield'] != '')
{
	$orderByField = $o_main->db_escape_name($_GET['sortfield']);
}
if(isset($preorderByDesc))
{
	if($preorderByDesc == 1) $orderByDesc = 'DESC';
	else $orderByDesc = 'ASC';
}
if(isset($_GET['descUse']) && $_GET['descUse'] != "")
{
	if(strtoupper($_GET['descUse']) == 'DESC') $orderByDesc = 'DESC';
	else $orderByDesc = 'ASC';
}
$otherdesc = "DESC";
if($orderByDesc == "DESC") $otherdesc = "ASC";
if($orderByField == "") $orderByField = $preorderByField;
$s_sql_order = '';
if($orderByField != "") $s_sql_order = ' ORDER BY '.$o_main->db_escape_name($orderByField).' '.$orderByDesc;
if(isset($_GET['start']) && is_numeric($_GET['start']))
{
	$start = intval($_GET['start']);
	$startList = $start * $l_list_items_per_page;
	$_SESSION['caID_'.$_GET['caID']]['module_page'] = $start;
} else {
	unset($_SESSION['caID_'.$_GET['caID']]['module_page']);
}

if(isset($_GET['relationfield']))
{
	$soker->conditions[] = array($_GET['relationID'],'=',$submodule,$_GET['relationfield']);
}
if(isset($linkToModuleID) && $linkToModuleID == 1) $soker->conditions[] = array($moduleID,'=',$submodule,'moduleID');
if(is_file($extradir.'/addOn_InputListFilter/addOn_InputListFilter.php'))
{
	include($extradir.'/addOn_InputListFilter/addOn_InputListFilter.php');
}
if($settingsChoice_maxLevel_inputMenuLevels > 0)
{
	$soker->conditions[] = array(0,'=',$submodule,'level');
}
if(isset($_GET['content_status']) && $_GET['content_status']!='')
{
	$soker->conditions[] = array(intval($_GET['content_status']),'=',$submodule,'content_status');
} else {
	$soker->conditions[] = array(2,'<',$submodule,'content_status');
}
if($o_main->multi_acc)
{
	if('_basisconfig' == substr($submodule, -12))
	{
		$soker->conditions[] = array($o_main->app_id,'=',$submodule,'app_id');
	} else {
		$soker->conditions[] = array($o_main->account_id,'=',$submodule,'account_id');
	}
}

$o_soker_main = clone $soker;

if(isset($_SESSION[$_GET['caID'].$_GET['accountname'].$submodule.'_search'])) $_GET['search'] = $_SESSION[$_GET['caID'].$_GET['accountname'].$submodule.'_search'];
if(isset($_GET['search']) && $_GET['search'] != '')
{
	$v_exception_columns = array('createdBy', 'updatedBy', 'sortnr', 'origId', 'content_status');
	$searchword = $_GET['search'];
	
	if($e_search_method == 0 && $searchFieldName != "")
	{
		$soker->searchConditions[] = array($searchword, "LIKE", $submodule, $searchFieldName);
	} else if($e_search_method == 1)
	{
		foreach($linkers[0] as $s_field)
		{
			if(isset($fieldsStructure[$s_field])) $soker->searchConditions[] = array($searchword, "LIKE", $soker->mainTable, $fieldsStructure[$s_field][0]);
		}
	} else {
		foreach($fields as $felt)
		{
			if(!in_array($felt[0], $v_exception_columns))
			{
				$soker->searchConditions[] = array($searchword, "LIKE", $soker->mainTable, $felt[0]);
			}
		}
	}
	$_SESSION[$_GET['caID'].$_GET['accountname'].$submodule.'_search'] = $searchword;
}

if(sizeof($soker->conditions) > 0 || sizeof($soker->searchConditions) > 0)
{
	if($sqlList == '') $sqlList = ' WHERE';
	if(sizeof($soker->searchConditions) > 0)
	{
		$sqlList .= ' (';
		foreach($soker->searchConditions as $key => $condition)
		{
			if(!in_array(strtolower($condition[1]), array('=', '<', '>', '<=', '>=', '<>', '!=', 'is', 'in', 'like'))) continue;
			$condition[0] = $o_main->db->escape_like_str($condition[0]);
			$condition[2] = $o_main->db_escape_name($condition[2]);
			$condition[3] = $o_main->db_escape_name($condition[3]);
			if($key > 0) $sqlList .= ' OR';
			if($module_multi_table && $o_main->db->field_exists($condition[3], $condition[2]."content"))
			{
				$sqlList .= " ".$condition[2]."content.".$condition[3]." ".$condition[1];
			} else {
				$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1];
			}
			if($searchType == 0)
			{
				$sqlList.=" '".$condition[0]."%' ESCAPE '!'";
			} else if($searchType == 1) {
				$sqlList.=" '%".$condition[0]."%' ESCAPE '!'";
			}
		}
		$sqlList .= ')';
	}
	
	if(sizeof($soker->conditions) > 0)
	{
		if(sizeof($soker->searchConditions) > 0) $sqlList .= ' AND';
		foreach($soker->conditions as $key => $condition)
		{
			if(!in_array(strtolower($condition[1]), array('=', '<', '>', '<=', '>=', '<>', '!=', 'is', 'in', 'like'))) continue;
			$condition[0] = $o_main->db->escape($condition[0]);
			$condition[2] = $o_main->db_escape_name($condition[2]);
			$condition[3] = $o_main->db_escape_name($condition[3]);
			if($key > 0) $sqlList .= ' AND';
			if($module_multi_table && $o_main->db->field_exists($condition[3], $condition[2]."content"))
			{
				$sqlList .= " ".$condition[2]."content.".$condition[3]." ".$condition[1]." ".$condition[0]."";
			} else {
				$sqlList .= " ".$condition[2].".".$condition[3]." ".$condition[1]." ".$condition[0]."";
			}
		}				 
	}
}

if($module_multi_table)
{
	$sqlSelect = "";
	$sqlExcludeColumns = array('id','moduleID','created','createdBy','updated','updatedBy','sortnr','origId');
	
	$v_fields = $o_main->db->list_fields($soker->mainTable.'content');
	foreach($v_fields as $s_field)
	{
		if(!in_array($s_field, $sqlExcludeColumns)) $sqlSelect .= ', '.$soker->mainTable.'content.'.$s_field;
	}
	
	$listOut = 'SELECT '.$soker->mainTable.'.*, '.$soker->mainTable.'.id as sideID '.$sqlSelect.' FROM '.$soker->mainTable.' LEFT OUTER JOIN '.$soker->mainTable.'content ON '.$soker->mainTable.'content.'.$soker->mainTable.'ID = '.$soker->mainTable.'.id AND '.$soker->mainTable.'content.languageID = '.$o_main->db->escape($s_default_output_language).' '.$sqlList.$s_sql_order.' LIMIT '.$startList.', '.$l_list_items_per_page;
	
	$countOut = 'SELECT COUNT('.$soker->mainTable.'.id) cnt FROM '.$soker->mainTable.' LEFT OUTER JOIN '.$soker->mainTable.'content ON '.$soker->mainTable.'content.'.$soker->mainTable.'ID = '.$soker->mainTable.'.id AND '.$soker->mainTable.'content.languageID = '.$o_main->db->escape($s_default_output_language).' '.$sqlList;
} else {
	$listOut = 'SELECT '.$soker->mainTable.'.*, '.$soker->mainTable.'.id as sideID FROM '.$soker->mainTable.$sqlList.$s_sql_order.' LIMIT '.$startList.', '.$l_list_items_per_page;
	
	$countOut = 'SELECT COUNT('.$soker->mainTable.'.id) cnt FROM '.$soker->mainTable.$sqlList;
}
//echo "listOut = $listOut<br>";
//echo "countOut = $countOut<br>";
$number = 0;
$o_query = $o_main->db->query($countOut);
if($o_query && $o_row = $o_query->row()) $number = $o_row->cnt;			
$pageNum = ceil($number / $l_list_items_per_page);

$o_get_content = $o_main->db->query($listOut);

$linkStandard = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&submodule=".$submodule."&start=".$start; 
if(isset($_GET['relationID'])) $linkStandard .= "&relationID=".$_GET['relationID'];
if(isset($_GET['relationfield'])) $linkStandard .= "&relationfield=".$_GET['relationfield'];
if(isset($_GET["content_status"])) $linkStandard .= "&content_status=".$_GET['content_status'];
?>
<style>
	.list_content .filter {
		padding: 10px 15px;
	}
	.list_content .filterLabel {
		color: #888;
		font-size: 12px;
		margin-bottom: 5px;
	}
	.list_content .selectDiv {
		border: 1px solid #e7e3e3;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		background: #fff;
		color: #5D5D5D;
		position: relative;
	}
	.list_content .selectDivWrapper {
		font-size: 13px;
		cursor: pointer;
	}
	.list_content .selectDiv .arrowDown {
		position: absolute;
		right: 10px;
		top: 17px;
		width: 0; 
		height: 0; 
		border-left: 5px solid transparent;
		border-right: 5px solid transparent;	
		border-top: 5px solid #049bec;	
		pointer-events: none;
	}
	.list_content .selectDiv select {
		padding: 9px 25px 9px 10px;
		height: auto;
		font-size: 13px;
		margin: 0;
	    width: 100%;
	    border: none;
	    box-shadow: none;
	    background-color: transparent;
	    background-image: none;
	    -webkit-appearance: none;
	       -moz-appearance: none;
	            appearance: none;
	}
	.list_content .selectDiv select::-ms-expand {
	    display: none;
	}
	.list_content .selectDiv select:focus {
	    outline: none;
	}
	.list_content .menu_filter:not(:first-child) {
		margin-top: 10px;
	}

	.list_content .searchWrapper {
		padding: 10px 15px;
	}
	.list_content .searchTitle {
		color: #888;
		font-size: 12px;
		margin-bottom: 5px;
	}
	.list_content .searchField {
		display: block;
		width: 100%;
		font-size: 13px;
		border: 1px solid #e7e3e3;
		padding: 5px 10px;
		color: #5D5D5D;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		border-radius: 3px;
		height: 40px;
	}
	.module_list .input_list_item .input_list_data .column{
		float: left;
		word-wrap: break-word;
	}
	.module_list .inputListSet {
		display: none;
	}
	.module_list.set1 .inputListSet1 {
		display: block;
	}	
	.module_list.set2 .inputListSet2 {
		display: block;
	}
	.module_list.set3 .inputListSet3 {
		display: block;
	}
	.module_list.set4 .inputListSet4 {
		display: block;
	}
<?php

	if(isset($fieldInListWidth) && $fieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column0 {
			width: <?php echo $fieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	if(isset($presecondFieldInListWidth) && $presecondFieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column1 {
			width: <?php echo $presecondFieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	if(isset($prethirdFieldInListWidth) && $prethirdFieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column2 {
			width: <?php echo $prethirdFieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	if(isset($preforthFieldInListWidth) && $preforthFieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column3 {
			width: <?php echo $preforthFieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	if(isset($prefifthFieldInListWidth) && $prefifthFieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column4 {
			width: <?php echo $prefifthFieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	if(isset($presixthFieldInListWidth) && $presixthFieldInListWidth != ""){
		?>
		.module_list .input_list_item .input_list_data .column5 {
			width: <?php echo $presixthFieldInListWidth?>%;
			margin-right: 0;
		}
		<?php
	}
	for($x = 2; $x<= 4; $x++){
		$variableFields = "preSet".$x."numberOfFields";
		$variableOne = "preSet".$x."fieldInListWidth";
		$variableTwo = "preSet".$x."secondFieldInListWidth";
		$variableThree = "preSet".$x."thirdFieldInListWidth";
		$variableFour = "preSet".$x."forthFieldInListWidth";

		$variableFive = "preSet".$x."fifthFieldInListWidth";
		$variableSix = "preSet".$x."sixthFieldInListWidth";
		$variableUseAfter = "preSet".$x."UseAfterWidth";
		if(isset($$variableFields) && $$variableFields > 0){
			if(isset($$variableUseAfter) && intval($$variableUseAfter) > 0){				
				if(isset($$variableOne) && $$variableOne != ""){
				?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column0 {
						width: <?php echo $$variableOne?>%;
						margin-right: 0;
					}
					<?php
				}
				if(isset($$variableTwo) && $$variableTwo != ""){
					?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column1 {
						width: <?php echo $$variableTwo?>%;
						margin-right: 0;
					}
					<?php
				}
				if(isset($$variableThree) && $$variableThree != ""){
					?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column2 {
						width: <?php echo $$variableThree?>%;
						margin-right: 0;
					}
					<?php
				}
				if(isset($$variableFour) && $$variableFour != ""){
					?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column3 {
						width: <?php echo $$variableFour?>%;
						margin-right: 0;
					}
					<?php
				}
				if(isset($$variableFive) && $$variableFive != ""){
					?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column4 {
						width: <?php echo $$variableFive?>%;
						margin-right: 0;
					}
					<?php
				}
				if(isset($$variableSix) && $$variableSix != ""){
					?>
					.module_list.set<?php echo $x;?> .input_list_item .input_list_data .column5 {
						width: <?php echo $$variableSix?>%;
						margin-right: 0;
					}
					<?php
				}
			}
		}
	}
?>
</style>
<script type="text/javascript">
	function resizeColumns(){
		$(".module_list").removeClass("set1 set2 set3 set4 set5")
		<?php
		for($x = 2; $x<= 4; $x++){
			$variableFields = "preSet".$x."numberOfFields";
			$variableOne = "preSet".$x."fieldInListWidth";
			$variableTwo = "preSet".$x."secondFieldInListWidth";
			$variableThree = "preSet".$x."thirdFieldInListWidth";
			$variableFour = "preSet".$x."forthFieldInListWidth";
			$variableFive = "preSet".$x."fifthFieldInListWidth";
			$variableSix = "preSet".$x."sixthFieldInListWidth";
			$variableUseAfter = "preSet".$x."UseAfterWidth";
			if(isset($$variableFields) && $$variableFields > 0){
				if(isset($$variableUseAfter) && intval($$variableUseAfter) > 0){				
			?>
				if($(".module_list").width() <= <?php echo intval($$variableUseAfter)?>){
					$(".module_list").removeClass("set1 set2 set3 set4 set5")
					$(".module_list").addClass("set<?php echo $x?>");
				}
			<?php
				}
			}
		}
		?>
		//set default list set
		if(!$(".module_list").hasClass("set2") && !$(".module_list").hasClass("set3") && !$(".module_list").hasClass("set4")){
			$(".module_list").addClass("set1");
		}
	}
	resizeColumns();
	$(document).ready(function(){
		resizeColumns();
	})
	$(window).on('resize', resizeColumns);
</script>
<div class="list_content">
	<?php
	if($showSearchField == 1)
	{
		?>
		<div class="searchWrapper">
			<div class="searchTitle"><?=$formText_Search_input;?></div>
			<form style="padding:0; margin:0;" action="<?=$_SERVER['PHP_SELF'];?>" method="get" onSubmit="javascript:$('.list_content .searchField').trigger('keyup'); return false;">
				<input type="hidden" name="pageID" value="<?=$_GET['pageID'];?>" />
				<input type="hidden" name="accountname" value="<?=$_GET['accountname'];?>" />
				<input type="hidden" name="companyID" value="<?=$_GET['companyID'];?>" />
				<input type="hidden" name="caID" value="<?=$_GET['caID'];?>" />
				<input type="hidden" name="module" value="<?=$module;?>" />
				<input type="hidden" name="submodule" value="<?=$submodule;?>" />
				<input type="text" name="search" value="<?=$searchword;?>" autocomplete='off' class="searchField" />
				<?php if(isset($_GET['relationID'])){ ?><input type="hidden" name="relationID" value="<?=$_GET['relationID'];?>" /><?php } ?>
				<?php if(isset($_GET['relationfield'])){ ?><input type="hidden" name="relationfield" value="<?=$_GET['relationfield'];?>" /><?php } ?>
				<?php if(isset($_GET['content_status'])){ ?><input type="hidden" name="content_status" value="<?=$_GET['content_status'];?>" /><?php } ?>
			</form>
		</div>
		<script type="text/javascript">
			$(document).ready(function(){				
				var delay = (function(){
					var timer = 0;
					return function(callback, ms){
						clearTimeout (timer);
						timer = setTimeout(callback, ms);
					};
				})();
				$(".list_content .searchField").keyup(function(){
					var searchText = $(this).val();
					var filter1 = $(".list_content .menu_filter.1 .filterBy option:selected").val();
					var filter2 = $(".list_content .menu_filter.2 .filterBy option:selected").val();
					var filter3 = $(".list_content .menu_filter.3 .filterBy option:selected").val();
					delay(function(){   
						$(".list_content .listItems").html('<div style="text-align: center;"><img border="0" src="<?php echo $variables->languageDir; ?>account_fw/menu/elementsOutput/ajax-loader.gif" /></div>');
						$.ajax({
							type: "POST",
							cache: false,
							url: "<?php echo $extradir;?>/input/includes/list_ajax.php?<?php echo $_SERVER['QUERY_STRING']?>",
							data: {soker: '<?php echo json_encode($o_soker_main); ?>',filter1: filter1, filter2:filter2, filter3: filter3, search: searchText, searchFieldName: "<?php echo (isset($searchFieldName)?$searchFieldName:'')?>", search_method: "<?php echo $e_search_method?>", searchType: "<?php echo (isset($searchType)?$searchType:'');?>", 
							orderByField: "<?php echo $orderByField?>",	orderByDesc: "<?php echo $orderByDesc?>", perPage: "<?php echo $l_list_items_per_page?>", choosenListInputLang: "<?php echo $choosenListInputLang;?>", 
								listFieldVariables: '<?php echo json_encode($listFieldVariables);?>',listSet2FieldVariables: '<?php echo json_encode($listSet2FieldVariables);?>',
								listSet3FieldVariables: '<?php echo json_encode($listSet3FieldVariables);?>',listSet4FieldVariables: '<?php echo json_encode($listSet4FieldVariables);?>',
								module: "<?php echo $module; ?>", submodule: "<?php echo $submodule?>",
								selfurl: "<?php echo $_SERVER['PHP_SELF']?>",
								extradomaindirroot: "<?php echo $extradomaindirroot;?>"
							},
							
							success: function(data){
								var object = jQuery.parseJSON(data);
								$(".list_content .listItems").html(object.html);
								unbindScroll();
								bindScroll();
								$(window).trigger('resize');
							}
						})					
   					}, 500 );
				})
			})
		</script>
		<?php
	}
	?>
	<?php
	if($showFilterByMenulevelField == 1 && $filterModuleID != null)
	{
		$getMenulevels = "SELECT * FROM menulevel LEFT JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id
		WHERE menulevelcontent.languageID = '".$o_main->db->escape_str($s_default_output_language)."'".($o_main->multi_acc?" AND menulevel.account_id = '".$o_main->db->escape_str($o_main->account_id)."'":"")." 
		AND menulevel.content_status < 2 AND menulevel.level = 0 AND menulevel.moduleID = '".$o_main->db->escape_str($filterModuleID)."' ORDER BY menulevel.sortnr";
		$o_query = $o_main->db->query($getMenulevels);
		if($o_query && $menulevels = $o_query->result_array())
		{
			if(count($menulevels) > 0)
			{
			?>
				<div class="filter">
					<div class="filterLabel"><?=$formText_FilterBy_inputFilter?></div>
					<div class="menu_filter 1" data-level="1">
						<div class="selectDiv">
							<div class="selectDivWrapper">
								<select name="filter" class="filterBy">
									<option value="0"><?=$formText_All_inputFilter?></option>
									<?php foreach($menulevels as $menulevel) { ?>						
									<option value="<?=$menulevel['id']?>" <?php if($menulevel['id'] == $_SESSION['filter1']) echo 'selected';?>><?php echo htmlentities($menulevel['levelname']);?></option>
									<?php } ?>
									<option value="-1"<?php echo ($_SESSION['filter1'] == -1 ? ' selected':'');?>><?php echo $formText_NotConnectedContent_inputFilter?></option>
								</select>
								<div class="arrowDown"></div>
							</div>
						</div>
					</div>
					<div class="menu_filter 2" data-level="2"></div>
					<div class="menu_filter 3" data-level="3"></div>
                    <div class="menu_filter 4" data-level="4"></div>
				</div>
				<script type="text/javascript">
					function bindFilter()
					{
						$(".list_content .menu_filter .filterBy").off('change').on('change', function(){
							var cur_level = $(this).closest('.menu_filter').data('level');
							menu_filter_update(1, cur_level, cur_level);
						})
					}
					function menu_filter_update_list(filter1, filter2, filter3, filter4)
					{
						var saveFilter = true;
						if(filter1 == 0 || filter2 == 0 || filter3 == 0 || filter4 == 0)
						{
							saveFilter = false;
						}
						$(".list_content .listItems").html('<div style="text-align: center;"><img border="0" src="<?=$variables->languageDir; ?>account_fw/menu/elementsOutput/ajax-loader.gif" /></div>');
						
						$.ajax({
							type: "POST",
							cache: false,
							url: "<?=$extradir;?>/input/includes/list_ajax.php?<?=$_SERVER['QUERY_STRING']?>",
							data: {
								soker: '<?=json_encode($o_soker_main); ?>', filter1: filter1, filter2: filter2, filter3: filter3, filter4: filter4, search_method: "<?php echo $e_search_method?>",
								searchFieldName: "<?=(isset($searchFieldName)?$searchFieldName:'');?>", searchType: "<?=(isset($searchType)?$searchType:'');?>", orderByField: "<?=$orderByField?>",
								orderByDesc: "<?=$orderByDesc?>", perPage: "<?=$l_list_items_per_page?>", choosenListInputLang: "<?=$choosenListInputLang;?>", 
								listFieldVariables: '<?=json_encode($listFieldVariables);?>', listSet2FieldVariables: '<?=json_encode($listSet2FieldVariables);?>',
								listSet3FieldVariables: '<?=json_encode($listSet3FieldVariables);?>', listSet4FieldVariables: '<?=json_encode($listSet4FieldVariables);?>',
								module: "<?=$module?>", submodule: "<?=$submodule?>", extradir: "<?=$extradir?>",
								selfurl: "<?=$_SERVER['PHP_SELF']?>", saveFilter: saveFilter
							},
							success: function(data){
								var object = jQuery.parseJSON(data);
								$(".list_content .listItems").html(object.html);
								$(".list_content .searchField").val("");
								unbindScroll();
								bindScroll();
								$(window).trigger('resize');
							}
						});
					}
					function menu_filter_update(counter, from_level, to_level)
					{
						if($(".list_content .menu_filter."+counter).length == 0)
						{
							var filter1 = $(".list_content .menu_filter.1 .filterBy option:selected").val();
							var filter2 = $(".list_content .menu_filter.2 .filterBy option:selected").val();
							var filter3 = $(".list_content .menu_filter.3 .filterBy option:selected").val();
                            var filter4 = $(".list_content .menu_filter.4 .filterBy option:selected").val();
							menu_filter_update_list(filter1, filter2, filter3, filter4);
							bindFilter();
							return;
						}
						
						if(counter >= from_level && counter <= to_level)
						{
							var next = counter + 1;
							var value = $(".list_content .menu_filter."+counter+" .filterBy option:selected").val();
							if($(".list_content .menu_filter."+next).length == 0)
							{
								menu_filter_update(counter+1, from_level, to_level);
								return;
							}
							$.ajax({
								type: "POST",
								cache: false,
								url: "<?php echo $extradir."/input/includes/ajax_get_menulevel_filter.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID'];?>",
								data: {choosenMenulevel:value, choosenListInputLang: '<?=$choosenListInputLang;?>'},
								success: function(data){
									var object = jQuery.parseJSON(data);
									$(".list_content .menu_filter."+next).html(object.html);
									menu_filter_update(counter+1, from_level, to_level);
								}
							});
						} else if(counter > (to_level+1)) {
							$(".list_content .menu_filter."+counter).html('');
							menu_filter_update(counter+1, from_level, to_level);
						} else {
							menu_filter_update(counter+1, from_level, to_level);
						}
					}
					bindFilter();
					<?php
					$l_menu_filter_level = 0;
					if(intval($_SESSION['filter1']) > 0) $l_menu_filter_level++;
					if(intval($_SESSION['filter2']) > 0) $l_menu_filter_level++;
					if(intval($_SESSION['filter3']) > 0) $l_menu_filter_level++;
                    if(intval($_SESSION['filter4']) > 0) $l_menu_filter_level++;
					if($l_menu_filter_level > 0)
					{
						?>
						menu_filter_update_list(<?php echo intval($_SESSION['filter1']).', '.intval($_SESSION['filter2']).', '.intval($_SESSION['filter3']).', '.intval($_SESSION['filter4']);?>);
						menu_filter_update(1, 1, <?php echo $l_menu_filter_level;?>, true);
						<?php
					}
					?>
				</script>
			<?php } 
		}
	}?>
	<div class="input_list_item head">
		<div class="input_list_data"><?php
		$counter = 0;
		$linkersField = array();
		foreach($linkers as $key => $outlink)
		{
			foreach($outlink as $key => $outlink)
			{
				$linkUse = $linkStandard;
				if($sortefield == $outlink)
				{
					$linkUse .= "&amp;descUse=".$otherdesc; 
				}
				
				foreach($fields as $outfield)
				{
					if($outfield[0] == $outlink)
					{
						$linkersField[$key] = $outfield;
						
						if($outfield[4] == "Dropdown" or $outfield[4] == "GroupDropdownField")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$tmp = explode("::",$outfield[11]);
							foreach($tmp as $tmpv)
							{
								list($lKey,$lValue) = explode(":",$tmpv);
								${$varName}[$lKey] = $lValue;
							}
						}
						elseif($outfield[4] == "RadioButtonAdv")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$tmp = explode("::",$outfield[11]);
							foreach($tmp as $tmpv)
							{
								list($lKey,$lValue) = explode(":",$tmpv);
								${$varName}[$lKey] = $lValue;
							}
						}
						elseif($outfield[4] == "DropdownTable")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "dropdown";
							$expl = explode(":",$outfield[11]);
							$dataTable = $expl[0];
							$dataID = $expl[1];
							$dataNames = explode(",",$expl[2]);
							$dataFilter = $expl[3];
							
							//TODO: ALI - security_check - filter
							$s_sql = "SELECT * FROM ".$o_main->db_escape_name($dataTable);
							if($o_main->multi_acc)
							{
								if('_basisconfig' == substr($dataTable, -12))
								{
									$dataFilter .= (""!=$dataFilter?" AND ":"")."app_id = '".$o_main->db->escape_str($o_main->app_id)."'";
								} else {
									$dataFilter .= (""!=$dataFilter?" AND ":"")."account_id = '".$o_main->db->escape_str($o_main->account_id)."'";
								}
							}
							if($dataFilter!="") $s_sql .= ' WHERE '.$dataFilter;
							$o_query = $o_main->db->query($s_sql);
							if($o_query && $o_query->num_rows()>0)
							{
								foreach($o_query->result() as $o_row)
								{
									$outName = "";
									foreach($dataNames as $dataName) $outName .= $o_row->$dataName." ";
									${$varName}[$o_row->$dataID] = $outName;
								}
							}
						}
						else if($outfield[4] == "Number" and $outfield[11] != "")
						{
							list($relTable, $relID, $relName) = explode(":",$outfield[11]);
							$o_query = $o_main->db->query('SELECT '.$o_main->db_escape_name($relID).', '.$o_main->db_escape_name($relName).' FROM '.$o_main->db_escape_name($relTable).($o_main->multi_acc?('_basisconfig' == substr($relTable, -12)?" WHERE app_id = '".$o_main->db->escape_str($o_main->app_id)."'":" WHERE account_id = '".$o_main->db->escape_str($o_main->account_id)."'"):""));
							if($o_query && $o_query->num_rows()>0)
							{
								$varName = $outfield[0]."ListArray";
								${$varName} = array();
								${$varName}["type"] = "relation";
								
								foreach($o_query->result() as $o_row)
								{
									${$varName}[$o_row->$relID] = $o_row->$relName;
								}
							}
						}
						else if($outfield[4] == "Checkbox")
						{
							$varName = $outfield[0]."ListArray";
							${$varName} = array();
							${$varName}["type"] = "checkbox";
							${$varName}[''] = ${$varName}[0] = "<div class=\"adm-ui-checkbox-empty\"></div>";
							${$varName}[1] = "<div class=\"adm-ui-checkbox-full\"></div>";
						}
						else if(in_array($outfield[4],array("Image","File","FileOrImage")))
						{
							$varName = $outfield[0]."Json";
							${$varName} = array();
							${$varName}["type"] = "json";
							${$varName}['levels'] = array(0);
						}
						else if($outfield[4] == "Comment")
						{
							$varName = $outfield[0]."Json";
							${$varName} = array();
							${$varName}["type"] = "json";
							${$varName}['levels'] = array(1);
						}
						else if($outfield[4] == "InvoiceNumber")
						{
							$varName = $outfield[0]."invoiceSearch";
							${$varName} = "SEARCH";
						}
						else if($outfield[4] == "ShowInvoice")
						{

							$varName = $outfield[0]."invoiceSearch";
							${$varName} = "OWNSEARCH";							 
						}
					}
				}
				
				if($outlink != "")
				{
					$linkUse .= "&amp;sortfield=".$outlink; 
				}
				?><div class="column t<?php echo $data_columns;?><?php echo ($counter>0?" next":"");?>"><a href="<?php echo $linkUse;?>" _class="optimize"><?php echo htmlentities(isset($linkersField[$key][2]) ? $linkersField[$key][2] : '[no_label]');?></a></div><?php
				$counter++;
			}
		}
		?></div>
		<div class="clear_both"></div>
	</div>
	<div class="listItems">
		<?php		
		$l_rand_num = rand(1,999999);
		$prebuttonconfig = '';
		$s_button_config = ACCOUNT_PATH.'/modules/'.$module.'/input/settings/buttonconfig/'.$submodule.'inputform.php';
		if(is_file($s_button_config))
		{
			include($s_button_config);
			$buttons = explode("¤",$prebuttonconfig);
			foreach($buttons as $button)
			{
				$items = explode(":",$button);
				if(count($items) > 2 )
				{
					$content_buttons[] = $items;
				}
			}
		}
		$exDir = explode("modules",$extradir);
		if($o_get_content && $o_get_content->num_rows()>0)
		{
			foreach($o_get_content->result_array() as $writeContent)
			{
				$showpageID = array();
				$content_id = $writeContent['sideID'];
				if($o_main->db->table_exists("pageIDcontent"))
				{
					$s_sql = 'SELECT p.id, pc.urlrewrite FROM pageID p LEFT OUTER JOIN pageIDcontent pc ON pc.pageIDID = p.id AND pc.languageID = ? WHERE p.contentID = ? AND p.contentTable = ?';
					$o_query = $o_main->db->query($s_sql, array($s_default_output_language, $content_id, $submodule));
					if($o_query) $showpageID = $o_query->row_array();
				}
				$editLink = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=edit&submodule=".$submodule.(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield']) ? "&relationfield=".$_GET['relationfield']:"").(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "");
				$edit_link_attr = "";
				
				?><div class="input_list_item activate<?php echo ((isset($_GET['ID']) && $content_id==$_GET['ID']) ? ' active' : '')." ".$v_content_status_class[$writeContent['content_status']];?>" data-group="input_list_item">
					<div class="input_list_data"><?php
					ob_start();
					$setCounter = 1;
					$item_name = "";
					foreach($linkers as $key => $finList)
					{
						?>
						<div class="inputListSet inputListSet<?php echo $setCounter;?>">
						<?php
						$counter = 0;
						foreach($finList as $key => $finList)
						{
							$varName = $finList."ListArray";
							?><div class="column column<?php echo $counter;?> <?php echo ($counter>0?" next":"");?>"><?php
							if(is_file(__DIR__."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php"))
							{
								include(__DIR__."/../fieldtypes/".$linkersField[$key][4]."/customlistdisplay.php");
							} else {
								$invoiceName = $finList."invoiceSearch";
								$varJson = $finList."Json";
								if(isset(${$invoiceName}))
								{
									$invoiceID = $content_id;
									if(${$invoiceName} == "SEARCH")
									{
										$invoiceID = $writeContent[$finList];
									}
									$o_query = $o_main->db->query("SELECT invoiceFile FROM invoice WHERE id = ?".($o_main->multi_acc?" AND account_id = '".$o_main->db->escape_str($o_main->account_id)."'":""), array($invoiceID));
									if($o_query && $o_row = $o_query->row())
									{
										if($o_row->invoiceFile != "")
										{
											$v_path_split = explode("uploads/",$o_row->invoiceFile);
											$filepathTo = $extradomaindirroot."uploads/".$v_path_split[1];
											echo '<a href="'.$filepathTo.(strpos($filepathTo,'uploads/protected/')!==false ? '?caID='.$_GET['caID'].'&table=invoice&field=invoiceFile&ID='.$invoiceID : '').'" target="_blank">'.$formText_openPdf_input.'</a>';
										}
									}
								}
								else if(isset(${$varJson}))
								{
									$value = "";
									$data = json_decode($writeContent[$finList]);
									foreach($data as $item0)
									{
										if(sizeof(${$varJson}['levels'])>1)
										{
											foreach($item0[${$varJson}['levels'][0]] as $item1)
											{
												if($value!="") $value.="; ";
												$value .= html_entity_decode($item1[${$varJson}['levels'][1]]);
											}
										} else {
											if($value!="") $value.="; ";
											$value .= html_entity_decode($item0[${$varJson}['levels'][0]]);
										}
									}
									
									echo '<a href="'.$editLink.'" class="optimize">'.htmlentities($value).'</a>';
									if($item_name == "") $item_name = $value;
								} else {
									if($access>=10 and isset(${$varName}) && ${$varName}["type"] == "checkbox")
									{
										echo "<a href=\"".$extradir."/input/update.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&updateID=".$content_id."&updatemodule=".$submodule."&updatefield=".$finList."&updatevalue=".($writeContent[$finList]==1 ? "0" : "1")."&start=".$start."&moduleID=".$moduleID."&parentdir=".$parentdir."&extradir=".$extradir.(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "")."\">";
									} else {
										echo "<a href=\"".$editLink."\" class=\"optimize\">";
									}
									echo htmlentities(isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
									echo "</a>"; 
									if($item_name == "") $item_name = (isset(${$varName}) ? ${$varName}[$writeContent[$finList]] : $writeContent[$finList]);
								}
							}
							?></div><?php
							$counter++;
						}
						?>
						</div>
						<?php
						$setCounter++;
					}
					if($item_name == "")
					{
						ob_clean();
						$setCounter = 1;
						$item_name = "noname";
						foreach($linkers as $key => $finList)
						{
							?>
							<div class="inputListSet inputListSet<?php echo $setCounter;?>">
							<?php
							$counter = 0;
							foreach($finList as $key => $finList)
							{
								?><div class="column column<?php echo $counter;?> <?php echo ($counter>0?" next":"");?>"><?php
								if($counter==0) echo '<a href="'.$editLink.'" class="optimize">'.htmlentities($item_name).'</a>';
								?></div><?php
								$counter++;
							}
							?>
							</div>
							<?php
							$setCounter++;
						}
					}
					echo ob_get_clean();
					?>
					</div>
					<?php if(isset($_GET['subcontent'])) { ?>
					<div class="list_buttons popup_button_box dropdown">
						<a id="<?php echo "l_btn_".$module."_".$submodule."_".$content_id;?>" class="script" data-target="#" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="glyphicon glyphicon-menu-hamburger glyphicon-white"></span>
						</a>
						<ul class="dropdown-menu" role="menu" aria-labelledby="<?php echo "l_btn_".$module."_".$submodule."_".$content_id;?>"><?php
						foreach($content_buttons as $buttonsArray)
						{
							$buttonSubmodule = $buttonsArray[6];
							$buttonModule = $buttonsArray[0];
							$buttonInclude = $buttonsArray[2];
							$buttonRelationModule = $buttonsArray[3];
							$buttonMode = $buttonsArray[4];
							if($buttonMode == 0)
							{
								?><li><?php
								include(ACCOUNT_PATH."/modules/".$module."/input/buttontypes/$buttonInclude/button.php");
								?></li><?php
							}
						}
						if($orderManualOrByField == '0' and $access >= 10)
						{
							?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=orderContent&submodule=".$submodule.(array_key_exists('menulevel',$writeContent) ? "&menulevel=".$writeContent['menulevel'] : "").(isset($_GET['relationID']) ? "&relationID=".$_GET['relationID']."&relationfield=".$_GET['relationfield']."&list=1" : "").(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "")."&_=".$l_rand_num;?>" class="optimize" role="menuitem"><?php echo $formText_order_list;?></a></li><?php
						} 
						
						if($listButtonContentSettings == 1 and $access >= 10)
						{
							?><li><a href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&ID=".$content_id."&includefile=editContentSettings&submodule=".$submodule.(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "");?>" class="optimize" role="menuitem"><?php echo $formText_contentsettings_list;?></a></li><?php
						}
						
						if($showShowpagebutton == 1)
						{
							?><li><a href="<?php echo (isset($languagedir) ? $languagedir : "../").((isset($showpageID['urlrewrite']) and $showpageID['urlrewrite'] != "") ? $showpageID['urlrewrite'] : "index.php?pageID=".$showpageID['id']);?>" target="_blank" role="menuitem"><?php echo $formText_showPage_list;?></a></li><?php
						}
						
						if($listButtonDelete == 1 and $access >= 100)
						{
							?><li><a class="delete-confirm-btn" data-name="<?php echo $item_name;?>" href="<?php echo $extradir."/input/delete.php?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&deleteID=".$content_id."&deletemodule=".$submodule."&submodule=".$submodule."&choosenListInputLang=".$choosenListInputLang.(isset($_GET['relationID'])?"&relationID=".$_GET['relationID']."&parentID=".$_GET['relationID']:"").(isset($_GET['relationfield'])?"&relationfield=".$_GET['relationfield']:"").(isset($_GET["content_status"]) ? "&content_status=".$_GET['content_status'] : "")."&_=".$l_rand_num;?>" role="menuitem"><?php echo $formText_delete_list;?></a></li><?php
						}
						?>
						</ul>
					</div>
					<?php } ?>
					<div class="clear_both"></div>
				</div><?php
				$counter++;
			}
		}
		?>
	</div>
</div>
 
<script type="text/javascript">
<?php if(isset($ob_javascript)) { ob_start(); } ?>
function bindScroll(){
	var start = 1;
	var $scrollbar  = $('.col1'), 
	$overview   = $scrollbar.find(".overview"),
	loadingData = false;

	var scrollbarData = $scrollbar.data("plugin_tinyscrollbar")

	$scrollbar.off('move').on('move', function() {
			// The threshold will enable us to start loading the text before we reach the end.
		//
		var threshold       = 0.9,   
			positionCurrent = scrollbarData.contentPosition + scrollbarData.viewportSize,
			positionEnd     = scrollbarData.contentSize * threshold;

		// Check if have reached the "end" and that we arent allready in the process of loading new data.
		//
		if(!loadingData && positionCurrent >= positionEnd) {
			loadingData = true;

			var filter1 = $(".list_content .menu_filter.1 .filterBy option:selected").val();
			var filter2 = $(".list_content .menu_filter.2 .filterBy option:selected").val();
			var filter3 = $(".list_content .menu_filter.3 .filterBy option:selected").val();
            var filter4 = $(".list_content .menu_filter.4 .filterBy option:selected").val();
			var searchText = $(".searchField").val();
			if(searchText == undefined){
				searchText = "";
			}
			$.ajax({
				type: "POST",
				cache: false,				
				url: "/accounts/<?php echo $_GET['accountname']?>/modules/<?php echo $_GET['module']?>/input/includes/list_ajax.php?<?php echo $_SERVER['QUERY_STRING']?>&start="+start,
				data: {soker: '<?php echo json_encode($o_soker_main); ?>', filter1:filter1, filter2:filter2,filter3:filter3,filter4:filter4, search: searchText, searchFieldName: "<?php echo (isset($searchFieldName)?$searchFieldName:'');?>", search_method: "<?php echo $e_search_method?>", searchType: "<?php echo (isset($searchType)?$searchType:'');?>", 
				orderByField: "<?php echo $orderByField?>",	orderByDesc: "<?php echo $orderByDesc?>", perPage: "<?php echo $l_list_items_per_page?>", choosenListInputLang: "<?php echo $choosenListInputLang?>", 
					listFieldVariables: '<?php echo json_encode($listFieldVariables); ?>',listSet2FieldVariables: '<?php echo json_encode($listSet2FieldVariables); ?>',
					listSet3FieldVariables: '<?php echo json_encode($listSet3FieldVariables); ?>',listSet4FieldVariables: '<?php echo json_encode($listSet4FieldVariables); ?>',
					module: "<?php echo $module?>", submodule: "<?php echo $submodule?>",
					selfurl: "<?php echo $_SERVER['PHP_SELF']?>", accountPath: "<?php echo ACCOUNT_PATH?>",
					sessionID: "<?php echo $variables->sessionID?>", username: "<?php echo $variables->loggID?>",
					extradomaindirroot: "<?php echo $extradomaindirroot;?>"},
				
				success: function(data){
					start++;
					loadingData = false;
					var object = jQuery.parseJSON(data);
					if(object.html != "" && object.html.search('div class="input_list_item">No data</div') === -1){
						$(".listItems").append(object.html);
						$(window).trigger('resize');
					}else{
						unbindScroll();
					}
					
				}
			})
		}
	});
}
function unbindScroll(){
	var $scrollbar  = $('.col1');
	$scrollbar.off('move');
}
$(document).ready(function(){
	bindScroll();
})
$(window).on('load', function(){
	bindScroll();
})
<?php if(isset($ob_javascript)) { $ob_javascript .= " ".ob_get_clean(); } ?>
</script>