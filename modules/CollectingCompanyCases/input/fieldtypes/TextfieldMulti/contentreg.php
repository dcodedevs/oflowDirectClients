<?php
//$fieldPos is same as $this->fieldNums[$f]
$v_items = explode("[:]", $fields[$fieldPos][11]);
$s_output_type = (isset($v_items[1]) ? strtolower($v_items[1]) : '');
$v_items = explode("::", $v_items[0]);
$structure = array();
foreach($v_items as $s_item)
{
	$structure[] = explode(":",$s_item);
}
// load items
$data = array();
$o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array(json_encode($_POST),session_id()));
$counter = $_POST[$fieldName."_counter"];
$o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("Counter = ".$counter,session_id()));
foreach($structure as $obj)
{
	$key = $fieldName."_".$obj[1];
    //
    $o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("length = ".$obj[2],session_id()));
    if($obj[2] == 0)//Checkbox
    {$o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("Checkboox".$key,session_id()));
        for($z=0;$z<$counter;$z++)
        {
            $o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("a = ".$key."_".$a,session_id()));
            if(isset($_POST[$key."_".$z]))
                $data[$z][] = "1";
            else
                $data[$z][] = "0";
            
        }
    }
    else
    {
        if(array_key_exists($key,$_POST))
        {
            $i=0;
            foreach($_POST[$key] as $item)
            {
                $o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("i = ".$i,session_id()));
                $data[$i][] = htmlentities($item);
                $i++;
            }
        }
    }
}
$o_main->db->query("insert into callbacklog set log=?, sessionID=?,datetime = now()", array("data = ".json_encode($data),session_id()));
$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($data);