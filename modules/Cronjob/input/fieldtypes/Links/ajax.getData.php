<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_GET['val']))
{
	$str = '';
	if($_GET['val'] == "list")
	{
		$s_default_output_language = '';
		$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
		if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;
		
		$sql = 'SELECT pageIDlist.menulevelID, menulevelcontent.levelname FROM pageIDlist
		 JOIN menulevel ON menulevel.id = pageIDlist.menulevelID
		 JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id AND menulevelcontent.languageID = ?
		 GROUP BY pageIDlist.listurl
		 ORDER BY pageIDlist.id';

		$o_query = $o_main->db->query($sql, array($s_default_output_language));
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result() as $o_row)
			{
				$pageID = $o_row->menulevelID;
				$str .= $pageID.':'.$o_row->levelname.'#';
			}
			echo substr($str,0,-1);
		}
	} else {
		$sql = 'SELECT name FROM moduledata WHERE id = ?';
		$o_query = $o_main->db->query($sql, array($_GET['val']));
		if($o_query && $o_query->num_rows()>0)
		{
			$o_row = $o_query->row();
			$tableName = $o_row->name;
		
			$dir = __DIR__.'/../../../../'.$tableName.'/input/settings/tables/';
			$filelist = glob($dir."*.php");
			foreach($filelist as $key => $value)
			{
				include($value);
				if($tableordernr == 1)
				{
					$value = explode('/', $value);
					$tableName = substr($value[count($value)-1], 0, -4);
				}
			}
		
			$sql = 'SELECT c.name, p.id AS pid, p.menulevelID FROM pageID AS p LEFT OUTER JOIN '.$o_main->db_escape_name($tableName).' AS c ON p.contentID = c.id WHERE p.contentTable =?';
			$o_query = $o_main->db->query($sql, array($tableName));
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result() as $o_row)
				{
					$pageID = $o_row->pid;
					$str .= $pageID.':'.$o_row->name.'#';
				}
			}
			echo substr($str,0,-1);
		}
	}
}
?>