<?php
 
$v_page = array();
 
if($_POST['accountinfodomain'] != '')
{
   //include(__DIR__."/../input/includes/ftp_commands.php");
   $robotscontent = file_get_contents(__DIR__."/../../../robots.txt");
    $liste = explode("\n",$robotscontent);
    for($a=0;$a<count($liste);$a++)
    {
        if(stristr($liste[$a],"sitemap"))
           $liste[$a] = '';
        
    }
    
    $robotscontent = implode("\n",$liste)."\n"."Sitemap: http://".$_POST['accountinfodomain']."/sitemap.php";
    $o_query = $o_main->db->query('insert into callbacklog set log = ?, sessionID = ?', array($robotscontent,'test'));
    ftp_file_put_content("robots.txt",$robotscontent);
}
?>  