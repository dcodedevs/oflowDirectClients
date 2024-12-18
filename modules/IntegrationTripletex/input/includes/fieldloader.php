<?php
require_once(__DIR__."/class_DatabaseTable.php");
$parentmodule = "";
$childmodule = array();
$childmodulename = array();
$prefields = $preblocks = array();
$databases = $fields = $fieldsStructure = $fields_replace = array();

if(is_file(__DIR__."/../settings/tables/".$submodule.".php"))
{
	include(__DIR__."/../settings/tables/".$submodule.".php");
	if(is_file(__DIR__."/../settings/blocks/".$submodule.".php")) include(__DIR__."/../settings/blocks/".$submodule.".php");
	$inputformName = $preinputformName;  
	$inputformDescription = $preinputformDescription; 	
	$listButtonDelete = $prelistButtonDelete;
	$listButtonEdit = $prelistButtonEdit;
	$inputButtonCreate = $preinputButtonCreate;
	$inputButtonDelete = $preinputButtonDelete;
	$inputButtonSave = $preinputButtonSave;
	$inputButtonSaveAndStay = $preinputButtonSaveAndStay;
	$orderByField = $preorderByField;
	$orderByDesc = "ASC";
	if($preorderByDesc == 1) $orderByDesc = "DESC";
	$fieldInList = $prefieldInList;
	$perPage = $preperPage;
	$e_search_method = isset($presearchMethod) ? $presearchMethod : 0;
	$showSearchField = $preshowSearchField;	
	$showFilterByMenulevelField = $preshowFilterByMenulevelField;	
	$filterModuleID = $prefilterMenuModule;
	$childmodule = $prechildmodule;
	$parentmodule = $preparentmodule;
	$submoduleName = $inputformName;
	foreach($mysqlTableName as $child)
	{
		$subValue = array();
		$subChild = explode(":",$child);
		foreach($subChild as $outname)
		{
			$subValue[] = $outname;			  
		}
		$datbas = new DatabaseTable();
		$datbas->start($subValue[0], $subValue[1], $subValue[2], $subValue[3]);
		if($settingsChoice_maxLevel_inputMenuLevels < $subValue[3])
		{
			$settingsChoice_maxLevel_inputMenuLevels = $subValue[3];
		}
		$databases[$subValue[0]] = $datbas;
	}	
	$fieldCounter = 0;
	include(__DIR__."/../settings/fields/".$submodule."fields.php");
	foreach($prefields as $child)
	{
		$addToPre = explode("¤",$child);
		$tempre = $addToPre[6];
		$addToPre[6] = array();
		$addToPre[6]['all'] = $tempre;
		$addToPre[7] = $databases[$addToPre[3]]->multilanguage;
		$addToPre['index'] = $fieldCounter;
		$fields[] = $addToPre;
		$databases[$addToPre[3]]->fieldNums[] = $fieldCounter;
		$fieldCounter++;
	}
	if(isset($_GET['frommodule']) && $_GET['frommodule'] != "")
	{
		$parentmodule = $_GET['frommodule'];
	}
}
?>