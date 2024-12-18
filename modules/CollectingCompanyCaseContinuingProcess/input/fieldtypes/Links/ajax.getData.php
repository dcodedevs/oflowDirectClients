<?php
define('BASEPATH', realpath(__DIR__.'/../../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');
if(isset($_GET['val']))
{
	$s_default_output_language = '';
	$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultOutputlanguage DESC, outputlanguage DESC, sortnr ASC');
	if($o_query && $o_row = $o_query->row()) $s_default_output_language = $o_row->languageID;
	
	$str = '';
	if($_GET['val'] == "list")
	{
		$sql = "SELECT pageIDlist.menulevelID, menulevelcontent.levelname FROM pageIDlist
		JOIN menulevel ON menulevel.id = pageIDlist.menulevelID
		JOIN menulevelcontent ON menulevelcontent.menulevelID = menulevel.id AND pageIDlist.languageID = menulevelcontent.languageID
		WHERE menulevelcontent.languageID = '".$o_main->db->escape_str($s_default_output_language)."'
		GROUP BY pageIDlist.listurl
		ORDER BY pageIDlist.id";
		
		$o_query = $o_main->db->query($sql);
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
		$s_name_field = 'name';
		$sql = "SELECT name FROM moduledata WHERE id = '".$o_main->db->escape_str($_GET['val'])."'";
		$o_query = $o_main->db->query($sql);
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
					$s_name_field = ($prefieldInList != '' ? $prefieldInList : 'name');
				}
			}
		
			$sql = "SELECT c.*, cc.*, p.id AS pid, p.menulevelID FROM pageID AS p LEFT OUTER JOIN ".$o_main->db_escape_name($tableName)." AS c ON p.contentID = c.id LEFT OUTER JOIN ".$o_main->db_escape_name($tableName)."content AS cc ON cc.".$o_main->db_escape_name($tableName)."ID = c.id AND cc.languageID = '".$o_main->db->escape_str($s_default_output_language)."' WHERE p.contentTable = '".$o_main->db->escape_str($tableName)."'";
			$o_query = $o_main->db->query($sql);
            //echo "last query = ".$o_main->db->last_query();
			if(!$o_query)
			{
				$sql = "SELECT c.*, p.id AS pid, p.menulevelID FROM pageID AS p LEFT OUTER JOIN '.$o_main->db_escape_name($tableName).' AS c ON p.contentID = c.id WHERE p.contentTable = '".$o_main->db->escape_str($tableName)."'";
				$o_query = $o_main->db->query($sql);
			}
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result() as $o_row)
				{
					$pageID = $o_row->pid;
					$str .= $pageID.':'.$o_row->$s_name_field.'#';
				}
			}
			echo substr($str,0,-1);
		}
	}
}