<?php
/* DO NOT MOVE USED IN OTHER MODULES */
if(!function_exists("get_files_fw")) include_once(__DIR__.'/fnctn_get_files_fw.php');
if(!function_exists("get_dirs_fw")) include_once(__DIR__.'/fnctn_get_dirs_fw.php');
if(!function_exists("devide_by_uppercase_fw")) include_once(__DIR__.'/fnctn_devide_by_upercase_fw.php');
if(!function_exists("get_access_element_variables_fw")) include_once(__DIR__.'/fnctn_get_access_element_variables_fw.php');

if(!function_exists("getAccessElements_fw")) {
    function getAccessElements_fw($moduleName, $type = "allow") {
        $module_dir = __DIR__."/../../../../../modules/".$moduleName;
        $addonFolders = array();
        $scan = scandir($module_dir);
        foreach($scan as $file)
        {
            if(strtolower(substr($file,0,6)) == 'addon_')
                $addonFolders[] = $file;
        }
        //gets php files for searching the language variables
        //directory  where to look for php scripts
        $dirs=array(realpath($module_dir));
        //define extension of the files
        $extensions=array('php');
        //directory exceptions
        $except_dirs=array(realpath($module_dir."/input"), realpath($module_dir."/properties"));
        foreach($addonFolders as $addonFolder) $except_dirs[] = realpath($module_dir."/".$addonFolder);
        //should check subdirs
        $check_subdirs=1;
        //gets files

        $output_folders=get_dirs_fw($dirs, $except_dirs, 0);
        $accessTypeString = "accessElementAllow";
        if($type == "restrict"){
            $accessTypeString = "accessElementRestrict";
        }
        $tmp_return=array();
        foreach($output_folders as $output_dir)
        {
            $directory_id = basename($output_dir);
            $except_dirs = array(realpath($output_dir."/languagesOutput"));

            $output_files = get_files_fw(array($output_dir), $extensions,$except_dirs,$check_subdirs);
            if(count($output_files) > 0) {
                $variable_ids=array();
                foreach($output_files as $file)
                {
                    //print ('<br />Checking the file:'.$file.'<br />');
                    $formTextVariables=get_access_element_variables_fw($file, $accessTypeString);
                    if(count($formTextVariables))
                    {
                        foreach($formTextVariables as $formTextVar)
                        {
                            $formTextID=str_replace("$", "", $formTextVar);
                            if(!in_array($formTextVar,$variable_ids))
                            {
                                $tmp_return[$formTextID]['files']=array();
                                array_push($variable_ids,$formTextVar);

                                $formTextName=explode("_", $formTextVar);
                                //print_r($formTextName);
                                $tmp_return[$formTextID]['context'] = (isset($formTextName[2]) ? devide_by_uppercase_fw($formTextName[2]) : '');
                                $tmp_return[$formTextID]['name']=$formTextName[1];
                                $tmp_return[$formTextID]['defaultValue']=devide_by_uppercase_fw($formTextName[1]);
                                array_push($tmp_return[$formTextID]['files'], $file);
                            } else {
                                array_push($tmp_return[$formTextID]['files'], $file);
                            }
                        }
                    }
                }

            }
        }

        return $tmp_return;
    }
}
?>
