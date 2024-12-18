<?php
/*
** Version 8.02
** Created: 2015-07-01
** Updated: 2017-06-29
*/
 
function ftp_get_connected()
{  
	if(is_file(__DIR__."/../../../../ftpConnect.php"))
	{ 
		include(__DIR__."/../../../../ftpConnect.php");
		$backdir = "/../../../";
	}
	else if(is_file("../ftpConnect.php"))
	{
		include("../ftpConnect.php");
		$backdir = "/../";
	} else if(is_file("../../../ftpConnect.php")) {
		include("../../../ftpConnect.php");
		$backdir = "/../../../";
	}
	 else {
		include("../../../../ftpConnect.php");
		$backdir = "/../../../../";
	}
	return array($ftplogin, $ftpconn, $backdir);
}
function ftp_chmod_file($chmodfile,$filemod)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	
	if ($ftplogin == 1)
	{
		$fileinfo = pathinfo($chmodfile);
		ftp_mkdir_recusive($ftpconn,$fileinfo['dirname']);
		 
		$filemode = $filemod;//"777";
		$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
		ftp_site($ftpconn, sprintf('CHMOD %o %s', "0".$filemode, $fileinfo['basename']));
	}
	ftp_close($ftpconn);
}
function ftp_file_put_content($file,$content)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	
	if(stristr($file,"/var/www"))
		$file = str_replace("/var/www","",$file);
	 
	if ($ftplogin == 1)
	{  
		//$file = str_replace("../","",$file);
		if(substr($file,0,1) !=  '/')
			$file = "".$file;
		//echo "file = $file";
		$scriptroot = substr(__DIR__,0,strrpos(__DIR__,"/"));
		$fileinfo = pathinfo($file);
		 
		//	$mydir =  ftp_chdir($ftpconn,$fileinfo['dirname']);
		$path = $scriptroot."".$backdir."uploads/".$fileinfo['basename'];
					 
		umask(0);
		//echo "dirname = ".$fileinfo['dirname']."<br>";
		ftp_mkdir_recusive($ftpconn,$fileinfo['dirname']);
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

function ftp_rename_file($oldfile,$newfile)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	$newfile = str_replace("/var/www","",$newfile);
	$oldfile = str_replace("/var/www","",$oldfile);
	
	if ($ftplogin == 1)
	{
		$getfile = ftp_rename($ftpconn,$oldfile,$newfile);
	}
	ftp_close($ftpconn);
}

function ftp_copy($copydir,$newdir)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	$newdir = str_replace("/var/www","",$newdir);
	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );	
	 
	if ($ftplogin == 1) 
	{
		//echo "dirname = ".dirname($newdir) . "<br>";
		ftp_mkdir_recusive($ftpconn, dirname($newdir));
		//echo "Current directory: " . ftp_pwd($ftpconn) . "<br>";
		//echo "newdir = $newdir, copydir = $copydir<br>";
		if(ftp_put($ftpconn, $newdir  , $copydir, FTP_BINARY))
			ftp_chmod($ftpconn,$filemode,$newdir);
			 
	}
	ftp_close($ftpconn);
}
 
function ftp_copy_singlefiles($copydir,$newdir)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	$newdir = str_replace("/var/www","",$newdir);
	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
	//$filemode = (int) $filemode;
	
	if($ftplogin == 1)
	{
		ftp_chdir($ftpconn,"~");
		ftp_mkdir_recusive($ftpconn,$newdir);
		foreach(glob($copydir."/*.*") as $filename)
   		{
			$ftpputrestult = ftp_put($ftpconn, basename($filename) , $filename, FTP_BINARY);		
			if($ftpputrestult == 1)
				ftp_chmod($ftpconn,$filemode,basename($filename));
		}
	}
	ftp_close($ftpconn);
}

function ftp_copy_directory($localdir,$newdir,$recursive)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	 
	if ($ftplogin == 1){
	 //echo "nwedir = $newdir, localdir = $localdir<br />";
		ftp_copyrr($ftpconn, $newdir,$localdir,$recursive, $newdir);
	}
	ftp_close($ftpconn);
}

function ftp_delete_file($deletefile)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	if ($ftplogin == 1)
	{
		$ftpdelresult = ftp_delete($ftpconn,$deletefile);
	}	
	ftp_close($ftpconn);
}

function ftp_delete_directory($newdir,$delete_also_folders)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	
	$newdir = str_replace("/var/www","",$newdir);
	if ($ftplogin == 1)
	{
		ftp_delete_dir($ftpconn,$newdir,$delete_also_folders);
	}
	ftp_close($ftpconn);
}
function ftp_delete_dir($ftpconn, $path, $delete_also_folders)
{
	//go to ftp root
	@ftp_chdir($ftpconn, '~');
	if($children = @ftp_nlist($ftpconn, $path))
	{
		foreach($children as $p)
		{
			ftp_rdel($ftpconn, rtrim($path, '/').'/'.$p, $delete_also_folders);
		}
	}
	if($delete_also_folders == 1)
	{
		//print "delete: $path \n";
		@ftp_rmdir ($ftpconn, $path);
	}
}
function ftp_rdel($ftpconn, $path, $delete_also_folders)
{
	//print "delete: $path \n";
	if(@ftp_delete($ftpconn, $path) === false && $delete_also_folders == 1)
	{
		if ($children = @ftp_nlist($ftpconn, $path))
		{
			foreach($children as $p)
				ftp_rdel($ftpconn, rtrim($path, '/').'/'.$p, $delete_also_folders);
		}
		@ftp_rmdir($ftpconn, $path);
	}
}
function ftp_copyrr($ftpconn, $dest, $source, $recursive,$defaultdest)
{
 
	$dirmode = "775";
	$dirmode = octdec ( str_pad ( $dirmode, 4, '0', STR_PAD_LEFT ) );
	$dirmode = (int) $dirmode;
  	$filemode = "775";
	$filemode = octdec ( str_pad ( $filemode, 4, '0', STR_PAD_LEFT ) );
	$filemode = (int) $filemode;
	

    // Simple copy for a file
    if (is_file($source)) {
		$destfile = substr($dest,strrpos($dest,"/")+1);
		$destfolder = substr($dest,0,strrpos($dest,"/"));
		ftp_chdir($ftpconn,"~");
		ftp_mkdir_recusive($ftpconn,$destfolder); 
		$ftpputrestult = ftp_put($ftpconn, $destfile , $source, FTP_BINARY);
 		if($ftpputrestult == 1)
		{
			if(ftp_chmod($ftpconn,$filemode,$destfile))
			{	//echo "mode is changed ".$filemode.$destfile;exit;
			}
			else
			{	//echo "Mode is not changed ".$filemode.$destfile;exit;
			}
		}
    }
 
    // Make destination directory
    if(is_dir($source) && !is_dir($dest)) {
 

		ftp_chdir($ftpconn,"~");
	  	ftp_mkdir_recusive($ftpconn,$dest);
		if(ftp_chmod($ftpconn,$dirmode,$dest))
		{	//echo "mode is changed ".$dirmode.$dest;
		}
		else
		{	//echo "Mode is not changed ".$dirmode.$dest;
		}
    }
	//
    // Loop through the folder

	if(is_dir($source))
	{
		$dir = dir($source);
		//echo "dir =$dir<br />";
		while (false !== $entry = $dir->read()) {
		//echo "wer her<br />";
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			// Deep copy directories'
		//echo "<br />source = $dest<br />";
			if ($dest !== "$source/$entry" && $recursive == 1) {
			//	echo "entry = $entry<br />";
				ftp_copyrr($ftpconn, "$dest/$entry","$source/$entry",$recursive,$defaultdest);
			}
			else if($dest !== "$source/$entry" && !is_dir("$source/$entry"))
				ftp_copyrr($ftpconn, "$dest/$entry","$source/$entry",$recursive,$defaultdest);
			else{} 
		} 
		$dir->close();
	}
 
    // Clean up
   
    return true;
}

function ftp_get_filelist($s_path, $changeDirToRoot = false)
{
	list($ftplogin, $ftpconn, $backdir) = ftp_get_connected();
	
	$dirArray = array();
	if($changeDirToRoot) ftp_chdir($ftpconn,"~");
	ftp_chdir($ftpconn, $s_path);
	$filelist = ftp_rawlist($ftpconn, "-al");
	foreach($filelist as $file)
	{
		$chunks = preg_split("/\s+/", $file);
		if($chunks[8] != "." && $chunks[8] != "..")
		{
			list($item['rights'], $item['number'], $item['user'], $item['group'], $item['size'], $item['month'], $item['day'], $item['time']) = $chunks; 
			$item['type'] = $chunks[0]{0} === 'd' ? 'directory' : 'file'; 
			array_splice($chunks, 0, 8); 
			
			$items[implode(" ", $chunks)] = $item;
			if($item['type'] == 'directory')
			{
				$dirname = implode(" ", $chunks);
				$dirArray[] = array("dir" => $s_path."/".$dirname, "name" => $dirname);
			} else {
				$dirname = implode(" ", $chunks);
				$dirArray[] = array("file" => $s_path."/".$dirname, "name" => $dirname);
			}
		}
	}
	return $dirArray;
}

function ftp_mkdir_recusive($ftpconn,$path)
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
		{	//echo "fullpath = $fullpath<br />";
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
?>