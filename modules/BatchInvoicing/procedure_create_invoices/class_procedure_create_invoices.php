<?php
class procedure_create_invoices
{
	function multi_dim_array_get_value($arr, $path)
	{
		// todo: add checks on $path
		$dest = $arr;
		$finalKey = array_pop($path);
		foreach ($path as $key)
		{
			$dest = $dest[$key];
		}
		return $dest[$finalKey];
	}

	function multi_dim_array_set_value(&$arr, $path, $idx, $value)
	{
		// we need references as we will modify the first parameter
		$dest = &$arr;
		if(count($path)>0)
		{
			$finalKey = array_pop($path);
			foreach($path as $key)
			{
				$dest = &$dest[$key];
			}
			$dest[$finalKey][$idx] = $value;
		} else {
			$dest[$idx] = $value;
		}
	}

	function run_procedure($execute, $moduleID, &$procrunresulttext, &$v_proc_variables, $procrunID = 0, $procrunlineID = 0, $o_main)
	{
		$stack = array();
		foreach($execute as $ex)
		{
			if($ex[0] == "RUN")
			{
				if($ex[1] == "INIT")
				{
					//close previous INIT
					if(intval($procrunID) > 0){
						$o_main->db->query("update sys_procrun set stoptime = now(), status = 1 where id = '".$procrunID."'");
					}
					$stack[] = "INIT";
					include(__DIR__."/scripts/INIT/execute.php");
					$o_main->db->query("insert into sys_procrun set moduleID = '".$moduleID."', starttime = now(), status = 2");
					$procrunID = $o_main->db->insert_id();
				}
				else if($ex[1] == "CREATELINES")
				{
					include(__DIR__."/scripts/CREATELINES/execute.php");
				} else {
					$o_main->db->query("insert into sys_procrunscript set procrunlineID = '".$procrunlineID."', scriptname = '".$ex[1]."', starttime = now(), status = 2");
					$procrunscriptID = $o_main->db->insert_id();
					//$procrunresult = rand(1,2);
					//print "<br>script ".$ex[1]." - ".$procrunresult."<br>";
					include(__DIR__."/scripts/".$ex[1]."/execute.php");
					$o_main->db->query("update sys_procrunscript set stoptime = now(), status = 1, result = '".$procrunresult."', resulttext = '".$procrunresulttext."' where id = '".$procrunscriptID."'");
				}
			}
			else if($ex[0] == "EACHLINE")
			{
				$o_query = $o_main->db->query("select * from sys_procrunline where procrunID = '".$procrunID."'");
				if($o_query && $o_query->num_rows()>0)
				{
					$procrunlines = $o_query->result_array();
					foreach($procrunlines as $procrunline)
					{
						$procrunlineID = $procrunline['id'];
						$o_main->db->query("update sys_procrunline set starttime = now(), status = 2 where id = '".$procrunlineID."'");
						$this->run_procedure($ex['child'], $moduleID, $procrunresulttext, $v_proc_variables, $procrunID, $procrunlineID, $o_main);
						$o_main->db->query("update sys_procrunline set stoptime = now(), status = 1, statustext = '".$procrunresulttext."' where id = '".$procrunlineID."'");
					}
				}
			}
			else if($ex[0] == "IF")
			{
				if($ex[1] == $procrunresult)
				{
					$this->run_procedure($ex['child'], $moduleID, $procrunresulttext, $v_proc_variables, $procrunID, $procrunlineID, $o_main);
				}
			}
		}
		
		
		while(count($stack)>0)
		{
			$cmd = array_pop($stack);
			if($cmd == "INIT")
			{
				$o_main->db->query("update sys_procrun set stoptime = now(), status = 1 where id = '".$procrunID."'");
			}
		}
	}
}
?>
