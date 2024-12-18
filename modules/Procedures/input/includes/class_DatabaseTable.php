<?php
class DatabaseTable {
	var $name = "";
	var $multilanguage = "";
	var $connection = "";
	var $sqlQuery = "";
	var $fieldNums = array();
	var $ID = 0;
	var $settingsChoice_maxLevel_inputMenuLevels = 0;
	
	function start($tname, $tmultilanguage, $tconnect, $tlevel)
	{
		$this->name = $tname;
		$this->multilanguage = $tmultilanguage;
		$this->connection = $tconnect;
		$this->maxLevel = 3;
		$this->langfields = array();
		$this->langendigs = array();
	}
	
	function load($fields, $languageID, &$error_msg)
	{
		$o_main = get_instance();
		if($this->multilanguage == 0)
		{
			if(!in_array("all",$this->langfields))
			{
				$this->langfields[] = "all";
				$this->langendigs[] = "";
			}
		} else {
			if($languageID == "all")
			{
				$o_query = $o_main->db->query('SELECT languageID FROM language WHERE outputlanguage = 1 ORDER BY defaultOutputlanguage DESC, sortnr ASC');
				if($o_query && $o_query->num_rows()>0)
				{
					foreach($o_query->result() as $o_row)
					{
						if(!in_array($o_row->languageID,$this->langfields))
						{
							$this->langfields[] = $o_row->languageID;
							$this->langendigs[] = $o_row->languageID;
						}
					}
				}			     
			} else {
				if(!in_array($languageID,$this->langfields))
				{
					$this->langfields[] = $languageID;
					$this->langendigs[] = $languageID;
				}
			}
		}
		
		for($f = 0; $f < sizeof($this->fieldNums); $f++)
		{
			$fieldPos = $this->fieldNums[$f];
			$fields[$fieldPos][6] = array();		  
			for($a = 0; $a < sizeof($this->langfields); $a++)
			{
				$fieldName = $fields[$fieldPos][1].$this->langendigs[$a];
				if(is_file(__DIR__."/../fieldtypes/".$fields[$fieldPos][4]."/contentreg.php"))
				{
					include(__DIR__."/../fieldtypes/".$fields[$fieldPos][4]."/contentreg.php");
				}
			}
		}
		return $fields;
	}
}
?>