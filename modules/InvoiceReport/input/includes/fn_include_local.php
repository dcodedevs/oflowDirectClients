<?php
function include_local($s_file, $v_parameters = array())
{
	foreach($v_parameters as $s_key => $s_parameter) ${$s_key} = $s_parameter;
	include($s_file);
	unset($s_file, $v_parameters, $s_key, $s_parameter);
	return get_defined_vars();
}
?>