<?php
function find_related_modules($headmodule, $relationarray, $choosenListInputLang, $extradiraccountname)
{
	$o_main = get_instance();
	$initheadmodule = $headmodule;
	$o_query = $o_main->db->query('SELECT * FROM moduledata ORDER BY modulemode, ordernr ASC');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$module = $modulelist[]  = $o_row->name;
			$modulemode[] = $o_row->modulemode;
			$nottable = 1;
			if(is_dir($extradiraccountname."/modules/".$o_row->name."/input/settings/relations"))
			{
				$findBase = opendir($extradiraccountname."/modules/".$o_row->name."/input/settings/relations");
				while($writeBase = readdir($findBase))
				{	
					$fieldParts = explode(".",$writeBase);
					if($fieldParts[2] != "LCK" && $fieldParts[1] == "php" && $fieldParts[0] != "")
					{
						include($extradiraccountname."/modules/".$o_row->name."/input/settings/relations/".$writeBase);
						foreach($prerelations as $modulerelation)
						{
							$relationValue = split("¤",$modulerelation);
							if($relationValue[1] == $headmodule)
							{
								$findBasetable = opendir($extradiraccountname."/modules/".$o_row->name."/input/settings/tables");
								while($writeBasetable = readdir($findBasetable))
								{	
									$fieldPartstable = explode(".",$writeBasetable);
									if($fieldPartstable[2] != "LCK" && $fieldPartstable[1] == "php" && $fieldPartstable[0] != "")
									{
										include($extradiraccountname."/modules/".$o_row->name."/input/settings/tables/".$fieldPartstable[0].".php");	
										$subtables++;
										if($tableordernr == "1" || ( $fieldPartstable[0] ==  $relationValue[2] && $endsubrelation == 0))
										{
											$relatedtablename = $fieldPartstable[0];
											include($extradiraccountname."/modules/".$o_row->name."/input/languagesInput/empty.php");
											include($extradiraccountname."/modules/".$o_row->name."/input/languagesInput/default.php");
											if(is_file($extradiraccountname."/modules/".$o_row->name."/input/languagesInput/$choosenListInputLang.php"))
											{
												include($extradiraccountname."/modules/".$o_row->name."/input/languagesInput/".$choosenListInputLang.".php");
											}
											if($preinputformName == '')
												$preinputformName = $o_row->name;
	
											$addtorelation =1;
											$moduledisplayname = $preinputformName;
											$relatedmodulename =$fieldParts[0];	
											if($fieldPartstable[0] ==  $_GET['submodule'])
											{
												$endsubrelation = 1;
											}
										}
									}
								}
								
								closedir($findBasetable);
								if($addtorelation == 1)
								{
									$relationarray[] = array($o_row->name, $relatedtablename, $relationValue[3], $relationValue[2], $relationValue[7], $moduledisplayname, $relatedmodulename, ($o_row->uniqueID>0 ? $o_row->uniqueID : $o_row->id));
								}
								if($subtables > 1)
								{  
									$relationarray = array_reverse($relationarray);
								}
								if($initheadmodule != $relationValue[1]  && $subtables >1 || $subtables == 1 )
								{	
									$relationarray = find_related_modules($module,$relationarray,$choosenListInputLang,$extradiraccountname);
								}
							}
						}
					}
				}
			}
		}
	} 
	return $relationarray;
}
?>