<?php
if(!function_exists('sys_get_field_setting_text')){
function sys_get_field_setting_text($fieldid, $fieldsStructure, $submodule)
{
	$s_return = '<span style="width:200px; display:inline-block;">'.$fieldid.'</span>';
	$s_return.= ' [ <span style="color:#333;">'.$fieldsStructure[$fieldid][4].'</span> ]';
	$s_return.= ' [ <span style="color:#333;">'.($submodule==$fieldsStructure[$fieldid][3]?'single-lang':'multi-lang').'</span> ]';
	$s_return.= ($fieldsStructure[$fieldid][9]==1?' [ <span style="color:#333;">hidden</span> ]':'');
	$s_return.= ($fieldsStructure[$fieldid][10]==1?'[ <span style="color:#333;">readonly</span> ]':'');
	
	return $s_return;
}
}