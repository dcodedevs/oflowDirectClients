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
$counter = $_POST[$fieldName."_counter"];
foreach($structure as $obj)
{
	$key = $fieldName."_".$obj[1];
    if($obj[2] == 0)//Checkbox
    {
        for($z=0;$z<$counter;$z++)
        {
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
                $data[$i][] = htmlentities($item);
                $i++;
            }
        }
    }
}
$fields[$fieldPos][6][$this->langfields[$a]] = json_encode($data);