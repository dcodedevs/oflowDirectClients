<?php
function include_local($s_file, $v_parameters = array())
{
	if(is_file($s_file))
	{
		foreach($v_parameters as $s_key => $s_parameter) ${$s_key} = $s_parameter;
		include($s_file);
		unset($s_file, $v_parameters, $s_key, $s_parameter);
		$v_vars = get_defined_vars();
	} else {
		$v_vars = array();
	}
	return $v_vars;
}