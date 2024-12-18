<?php
include("fn_tags_print_tree.php");
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

tags_print_tree(0,0,$_GET['className'],$_GET['addButton'],$_GET['renameButton'],$_GET['moveButton'],$_GET['mergeButton'],$_GET['deleteButton'],$_GET['s_default_output_language']);
