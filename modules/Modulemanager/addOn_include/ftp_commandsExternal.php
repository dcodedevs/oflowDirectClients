<?php
/*
** Version 8.10
** Created: 2015-07-01
** Updated: 2019-10-24
*/

function ftp_ext_get_connected()
{
	$s_host = 'localhost';
	$ftpconn = ftp_connect($s_host);
	$ftplogin = ftp_login($ftpconn,"devlibraryFTP","123Dc0de!");
	ftp_pasv($ftpconn, true);
	$backdir = "/../../../";
	
	$_SESSION['mm_library_host'] = $s_host;
	 
	return array($ftplogin, $ftpconn, $backdir);
}
function ftp_ext_chmod_file($chmodfile,$filemod)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	
	if ($ftplogin == 1)
	{
		$fileinfo = pathinfo($chmodfile);
		ftp_ext_mkdir_recusive($ftpconn,$fileinfo['dirname']);
		 
		$filemode = $filemod;//"777";
		$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
		ftp_site($ftpconn, sprintf('CHMOD %o %s', "0".$filemode, $fileinfo['basename']));
	}
	ftp_close($ftpconn);
}
function ftp_ext_chmod_directory($dest, $dirmode = "775")
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	
	if($ftplogin == 1)
	{
		ftp_chdir($ftpconn,"~");
		$dirmode = octdec ( str_pad ( $dirmode, 4, '0', STR_PAD_LEFT ) );
		$dirmode = (int) $dirmode;
		if(ftp_chmod($ftpconn,$dirmode,$dest))
		{	//echo "mode is changed ".$dirmode." - ".$dest."<br/>";
		}
		else
		{	//echo "Mode is not changed ".$dirmode." - ".$dest."<br/>";
		}
	}
	ftp_close($ftpconn);
}
		
function ftp_ext_file_put_content($file,$content)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	
	if(stristr($file,"/var/www"))
		$file = str_replace("/var/www","",$file);
	 
	if($ftplogin == 1)
	{
		ftp_chdir($ftpconn,"~");
		//$file = str_replace("../","",$file);
		if(substr($file,0,1) !=  '/')
			$file = "".$file;
		//echo "file = $file";
		$scriptroot = substr(dirname(__FILE__),0,strrpos(dirname(__FILE__),"/"));
		$fileinfo = pathinfo($file);
		 
		//	$mydir =  ftp_chdir($ftpconn,$fileinfo['dirname']);
		$path = $scriptroot."".$backdir."uploads/".$fileinfo['basename'];
					 
		umask(0);
		//echo "dirname = ".$fileinfo['dirname']."<br>";
		ftp_ext_mkdir_recusive($ftpconn,$fileinfo['dirname']);
 	 	//echo "path ftppath = ".ftp_pwd($ftpconn)."<br />";
		// echo "path ftppath = ".$path."<br />";
		$numbytes = file_put_contents($path,$content);
	//	echo "numbytes = $numbytes";
		chmod($path,0777); //
		$fp = fopen($path, 'r');

		$ftpputrestult = ftp_fput($ftpconn,$fileinfo['basename'],$fp, FTP_ASCII );
		$fp = fclose($fp);
		//echo "ftpputrestult121 = ".$ftpputrestult." <br />";
		 //echo "$file = ".file_get_contents("..".$file);
		//echo "basename =".$fileinfo['basename'];exit;
		$filemode = "777";
		$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
		if($ftpputrestult == 1)
			ftp_site($ftpconn, sprintf('CHMOD %o %s', "0".$filemode, $file));
		//	ftp_chmod($ftpconn,$filemode,$file);
		unlink($path);
	}
	ftp_close($ftpconn);
}

function ftp_ext_rename_file($oldfile,$newfile)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	$newfile = str_replace("/var/www","",$newfile);
	$oldfile = str_replace("/var/www","",$oldfile);
	
	if ($ftplogin == 1)
	{
		ftp_chdir($ftpconn,"~");
		$getfile = ftp_rename($ftpconn,$oldfile,$newfile);
	}
	ftp_close($ftpconn);
}

function ftp_ext_copy($copydir,$newdir)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	$newdir = str_replace("/var/www","",$newdir);
	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );	
	 
	if ($ftplogin == 1) 
	{
		//echo "dirname = ".dirname($newdir);
		ftp_ext_mkdir_recusive($ftpconn,dirname($newdir));
		//echo "Current directory: " . ftp_pwd($ftpconn) . "\n";
		if(ftp_put($ftpconn, $newdir  , $copydir, FTP_BINARY))
			ftp_chmod($ftpconn,$filemode,$newdir);
			 
	}
	ftp_close($ftpconn);
}

function ftp_ext_get($s_local_file, $s_ftp_file)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );	
	 
	if($ftplogin == 1)
	{
		ftp_change_rootdir($ftpconn, dirname($s_ftp_file));
		$s_file = trim(str_replace(dirname($s_ftp_file), '', $s_ftp_file), '/');
		//echo "Current directory: " . ftp_pwd($ftpconn) . "\n";
		if(ftp_get($ftpconn, $s_local_file, $s_file, FTP_BINARY))
		{
			chmod($s_local_file,$filemode);
			return true;
		}
	}
	ftp_close($ftpconn);
}
 
function ftp_ext_copy_singlefiles($copydir,$newdir)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	//$newdir = str_replace("/var/www","",$newdir);
	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
	//$filemode = (int) $filemode;
	$errorfiles = array();
	if($ftplogin == 1)
	{
		ftp_chdir($ftpconn,"~");
		//
		$filelist = ftp_ext_get_filelist($ftpconn,$copydir);
		ftp_change_rootdir($ftpconn,$copydir);
		//echo "copydir = $copydir";
		//print_r($filelist);
		foreach($filelist as $file)
		{//print_r($file);
			if(array_key_exists("file",$file))
			{
				$ftpgetresult = ftp_get($ftpconn, $newdir."/".$file['name'], $file['name'], FTP_BINARY);
				if($ftpgetresult == 1)
				{
					if(chmod($newdir,$filemode))
					{	//echo "mode is changed ".$filemode.$destfile;exit;
					}
					else
					{	//echo "Mode is not changed ".$filemode.$destfile;exit;
					}
				}
				else
				{
					$fileerror[] =  $file['name'];	
				}
			}
		}
		 
	}
	if(count($errorfiles) > 0)
   		return $errorfiles;
	else
    	return true;
	ftp_close($ftpconn);
}

function ftp_ext_copy_directory($localdir, $newdir, $recursive, $copymode = "get", $v_excludes = array())
{
	//echo "localdir = ".$localdir."<br><br>newdir  = ".$newdir."<br>";
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	//echo "mode = $copymode";
	if($ftplogin == 1)
	{
		if($copymode == 'put')
		{
			$copyreturn = ftp_ext_copyrr($ftpconn, $newdir, $localdir, $recursive, $newdir, $v_excludes);
		} else {
			$errorfiles = array();
			$copyreturn = ftp_ext_copyrr_get($ftpconn, $newdir, $localdir, $recursive, $newdir);
		}
	}
	ftp_close($ftpconn);
	return $copyreturn;
}

function ftp_ext_delete_file($deletefile)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	if ($ftplogin == 1)
	{
		$ftpdelresult = ftp_delete($ftpconn,$deletefile);
	}	
	ftp_close($ftpconn);
}

function ftp_ext_delete_directory($newdir,$delete_also_folders)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	
	$newdir = str_replace("/var/www","",$newdir);
	if ($ftplogin == 1)
	{
		ftp_ext_delete_dir($ftpconn,$newdir,$delete_also_folders);
	}
	ftp_close($ftpconn);
}
function ftp_ext_delete_dir($ftpconn, $path, $delete_also_folders)
{
	//go to ftp root
	@ftp_chdir($ftpconn, '~');
	if($children = @ftp_nlist($ftpconn, $path))
	{
		foreach($children as $p)
		{
			ftp_ext_rdel($ftpconn, rtrim($path, '/').'/'.$p, $delete_also_folders);
		}
	}
	if($delete_also_folders == 1)
	{
		//print "delete: $path \n";
		@ftp_rmdir ($ftpconn, $path);
	}
}
function ftp_ext_rdel($ftpconn, $path, $delete_also_folders)
{
	//print "delete: $path \n";
	if(@ftp_delete($ftpconn, $path) === false && $delete_also_folders == 1)
	{
		if ($children = @ftp_nlist($ftpconn, $path))
		{
			foreach($children as $p)
				ftp_ext_rdel($ftpconn, rtrim($path, '/').'/'.$p, $delete_also_folders);
		}
		@ftp_rmdir($ftpconn, $path);
	}
}
function ftp_ext_copyrr($ftpconn, $dest, $source, $recursive, $defaultdest, $v_excludes = array())
{
 
	$dirmode = "775";
	$dirmode = octdec ( str_pad ( $dirmode, 4, '0', STR_PAD_LEFT ) );
	$dirmode = (int) $dirmode;
  	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
	$filemode = (int) $filemode;
	

    // Simple copy for a file
    if (is_file($source) && !in_array($source, $v_excludes)) {
		$destfile = substr($dest,strrpos($dest,"/")+1);
		$destfolder = substr($dest,0,strrpos($dest,"/"));
		ftp_chdir($ftpconn,"~");
		ftp_ext_mkdir_recusive($ftpconn,$destfolder); 
		$ftpputrestult = ftp_put($ftpconn, $destfile , $source, FTP_BINARY);
 		if($ftpputrestult == 1)
		{
			if(ftp_chmod($ftpconn,$filemode,$destfile))
			{	//echo "mode is changed ".$filemode." - ".$destfile."<br/>";
			}
			else
			{	//echo "Mode is not changed ".$filemode." - ".$destfile."<br/>";
			}
		}
    }
 
    // Make destination directory
    if(is_dir($source) && !is_dir($dest) && !in_array($source, $v_excludes)) {
 

		ftp_chdir($ftpconn,"~");
	  	ftp_ext_mkdir_recusive($ftpconn,$dest);
		if(ftp_chmod($ftpconn,$dirmode,$dest))
		{	//echo "mode is changed ".$dirmode." - ".$dest."<br/>";
		}
		else
		{	//echo "Mode is not changed ".$dirmode." - ".$dest."<br/>";
		}
    }
	
    // Loop through the folder
	if(is_dir($source) && !in_array($source, $v_excludes))
	{
		$v_scan = array();
		if(substr(sprintf('%o', fileperms($source)), -1) <= 1)
		{
			$v_tmp = ftp_get_filelist(str_replace(realpath(__DIR__."/../../../"), "", $source));
			foreach($v_tmp as $v_item) $v_scan[] = $v_item["name"];
		} else {
			$v_scan = scandir($source);
		}
		foreach($v_scan as $s_item)
		{
			// Skip pointers
			if ($s_item == '.' || $s_item == '..')
			{
				continue;
			}
			// Deep copy directories'
			if ($dest !== $source."/".$s_item && $recursive == 1)
			{
				ftp_ext_copyrr($ftpconn, $dest."/".$s_item, $source."/".$s_item, $recursive, $defaultdest, $v_excludes);
			}
			else if($dest !== $source."/".$s_item && !is_dir($source."/".$s_item))
			{
				ftp_ext_copyrr($ftpconn, $dest."/".$s_item, $source."/".$s_item, $recursive, $defaultdest, $v_excludes);
			} else {} 
		}
	}
 
    // Clean up
   
    return true;
}
function ftp_ext_isfile($ftpconn,$source)
{
	$contents_on_server = ftp_nlist($ftpconn, $source); //Returns an array of filenames from the specified directory on success or FALSE on error. 
	//Test if file is in the ftp_nlist array
	if (in_array($check_file_exist, $contents_on_server)) 
	{
		//echo "<br>";
		//echo "I found ".$check_file_exist." in directory : ".$path;
	}	
}
function ftp_ext_copyrr_get($ftpconn, $dest, $source, $recursive, $defaultdest, $errorfiles=array())
{
	$dirmode = "775";
	$dirmode = octdec ( str_pad ( $dirmode, 4, '0', STR_PAD_LEFT ) );
	$dirmode = (int) $dirmode;
  	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
	$filemode = (int) $filemode;
	$filelist = ftp_ext_get_filelist($ftpconn,$source,true);
	//echo "source = ".$source;
	//print_r($filelist);
	foreach($filelist as $fileordir)
	{ //echo "<br><br>rootdir6 = ".ftp_pwd($ftpconn);
		ftp_change_rootdir($ftpconn,$source);
		$destfile = $dest."/".$fileordir['name'];
		$sourcefile = $source."/".$fileordir['name'];
		if(isset($fileordir['dir']))
		{//echo "er dire";
			//print_r($fileordir);exit;
			//echo "destfile = ".$destfile."<br>";
			mkdir($destfile,octdec(2777));
			$tmp = ftp_ext_copyrr_get($ftpconn, $destfile, $sourcefile, 1, 1, $errorfiles);
			if($tmp != 1)
			{
				array_merge($errorfiles,$tmp);
			}
		} else {
			//echo "<br><br>rootdir2 = ".ftp_pwd($ftpconn);
			//echo "getting file = ".$source."/".$fileordir['name']."<br>";
			//echo "getting file = ".$destfile."<br>";

			$ftpgetresult = ftp_get($ftpconn, $destfile, $fileordir['name'], FTP_BINARY);
			if($ftpgetresult == 1)
			{
				if(chmod($destfile,$filemode))
				{//	 echo "mode is changed ".$filemode.$destfile;exit;
				} else {	//echo "Mode is not changed ".$filemode.$destfile;exit;
				}
			} else {
					$errorfiles[] = $destfile;
			}
			//print_r($errorfiles);
			//print_r($fileordir);exit;
		}
	}
	//print_r($errorfiles);exit;
    // Clean up
    if(count($errorfiles) > 0)
   		return $errorfiles;
	else
    	return true;
}

function ftp_ext_get_filelist($ftpconn='', $listDir, $changeDirToRoot = false)
{
	if($ftpconn == '')
	{
		list($ftplogin, $ftpconn, $backdir) = ftp_ext_get_connected();
	}
	
	$dirArray = array();
	if($changeDirToRoot) ftp_chdir($ftpconn,"~");
	ftp_chdir($ftpconn, $listDir);
	
	$filelist = ftp_rawlist($ftpconn, "-al");//$listDir);
	
	foreach($filelist as $file)
	{
		$chunks = preg_split("/\s+/", $file);
		if($chunks[8] != "." && $chunks[8] != "..")
		{
			list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
			$item['type'] = $chunks[0][0] === 'd' ? 'directory' : 'file'; 
			array_splice($chunks, 0, 8); 
			
			$items[implode(" ", $chunks)] = $item;
			if($item['type'] == 'directory')
			{
				$dirname = implode(" ", $chunks);
				$dirArray[] = array(
				"dir" => $listDir."/".$dirname,
				"name" => $dirname);
			} else {
				$dirname = implode(" ", $chunks);
				$dirArray[] = array(
				"file" => $listDir."/".$dirname,
				"name" => $dirname);
			}
		}
	}
	return $dirArray;
}

function ftp_ext_mkdir_recusive($ftpconn,$path)
{
	$parts = explode("/",$path);
	$return = true;
	$fullpath = "";
	foreach($parts as $part)
	{
		if(empty($part))
		{
			$fullpath .= "/";
			continue;
		}
		$fullpath .= $part."/";
		if(ftp_chdir($ftpconn, $part))
		{
			//ftp_chdir($ftpconn, $fullpath);
		} else {
			if(@ftp_mkdir($ftpconn, $part))
			{
				// echo "makedir $part<br />";
				ftp_chdir($ftpconn, $part);
			} else {
				// echo "cant makedir $part<br />";
				$return = false;
			}
		}
	}
	return $return;
}
function ftp_change_rootdir($ftpconn,$dir)
{
	$v_folders = explode("/",$dir);
	ftp_chdir($ftpconn,"~");
	
	$curdir = '';
	foreach($v_folders as $l_key => $s_folder)
	{
		if($l_key == 0 && $s_folder == '') continue;
		$curdir .= '/'. $s_folder;
		ftp_chdir($ftpconn,$curdir);
	}
}
?>