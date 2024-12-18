<?php
$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/output_functions.php';
if(is_file($s_signant_file)) include($s_signant_file);
$s_signant_file = BASEPATH.'modules/IntegrationSignant/output/includes/ajax.cancel_document.php';
if(is_file($s_signant_file)) include($s_signant_file);