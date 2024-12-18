<?Php
if(!function_exists("get_dirs")) {
    function get_dirs($dirs,$except_dirs,$check_subdirs)
    {
        //function for getting array of files from some given dirrectory
        //widely used this function in cssListingEditingModule

        $tmp_return = array();
        $sub_dirs = array();

        // goes trough list of directories
        foreach ($dirs as $dir)
        {
            //print ('<br />current dir:'.$dir.'<br />');
           // tires to open dir
        	 if ($handle = opendir($dir))
           {
              // sucessfull reads trough the files in the dir
              while (false !== ($new_dir = readdir($handle)))
              {
                  // print ('<br />'.$new_dir.'<br />');
                  if (!is_file($dir."/".$new_dir))
                  {
                    if (!in_array($new_dir,$except_dirs) and !in_array($dir.'/'.$new_dir,$except_dirs) and ($new_dir!="." and $new_dir!=".."))
                    {
                        array_push($tmp_return,$dir.'/'.$new_dir);
                        array_push($sub_dirs,$dir.'/'.$new_dir);
                    }
                  }
              }
              closedir($handle);
           }
        }
        if(count($sub_dirs)>0)
        {
            if ($check_subdirs)
            {
              // if check_subdirs flag is set to 1
              // recursivery goes into subdirs
              $tmp_return=array_merge($tmp_return, get_files($sub_dirs,$except_dirs, 1 ));
            }
        }
        return $tmp_return;
    }
}
?>
