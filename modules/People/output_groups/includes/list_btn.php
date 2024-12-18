<?php
include("department_and_group_get.php");
// include(__DIR__."/../../output/languagesOutput/empty.php");
// include(__DIR__."/../../output/languagesOutput/default.php");
// if($b_show_choosen_language){
//     include(__DIR__."/../../output/languagesOutput/".$s_default_output_language.".php");
// }
$_GET['folder'] = "output";
$groupFolder = true;
include(__DIR__."/../../output/includes/readOutputLanguage.php");
include(__DIR__."/../../output/includes/include_access_elements_for_other_modules.php");
include(__DIR__."/../../output/includes/list_btn.php");

?>
