<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>
<?php								
include("dbConnect.php");
$result = mysql_fetch_array(mysql_query("select * from accountinfo"));
if(!stristr($result['domain'],"http"))
	$domain = 'http://'.$result['domain'];
else
	$domain = $result['domain'];
 
																																																																		
	header( "HTTP/1.1 301 Moved Permanently" );
	header("Location: ".$domain);
	exit;
?>
</body>
</html>

