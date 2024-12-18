<?php
ob_start();
define('BASEPATH', realpath(__DIR__.'/../../../../').DIRECTORY_SEPARATOR);
require_once(BASEPATH.'elementsGlobal/cMain.php');

$v_return = array();
if(isset($_GET['cat']))
{
	$v_ids = $v_module_list = array();
	$o_query = $o_main->db->query('SELECT contentTable FROM pageID GROUP BY contentTable');
	if($o_query && $o_query->num_rows()>0)
	{
		foreach($o_query->result() as $o_row)
		{
			$o_query2 = $o_main->db->query('SELECT moduleID FROM '.$o_row->contentTable.' GROUP BY moduleID');
			if($o_query2 && $o_query2->num_rows()>0)
			{
				foreach($o_query2->result() as $o_content)
				{
					$v_module_list[$o_content->moduleID] = $o_row->contentTable;
					$v_ids[] = $o_content->moduleID;
				}
			}
		}
	}
	
	if($_GET['cat'] == 1)
	{
		$o_query = $o_main->db->query('SELECT id, name FROM moduledata WHERE id IN ?', array($v_ids));
		if($o_query && $o_query->num_rows()>0)
		{
			foreach($o_query->result() as $o_row)
			{
				$v_return[$o_row->id] = $o_row->name;
			}
		}
	} else if($_GET['cat'] == 2 && isset($_GET['val']))
	{
		$s_default_input_language = '';
		$o_query = $o_main->db->query('SELECT languageID FROM language ORDER BY defaultInputlanguage DESC, inputlanguage DESC, sortnr ASC');
		if($o_query && $o_row = $o_query->row()) $s_default_input_language = $o_row->languageID;

		$v_return[0] = 'Please Choose';
		if(isset($v_module_list[$_GET['val']]))
		{
			$s_table = $v_module_list[$_GET['val']];
			$s_sql = 'SELECT cc.name, p.id AS pid, p.menulevelID FROM pageID AS p LEFT OUTER JOIN '.$s_table.' AS c ON p.contentID = c.id LEFT OUTER JOIN '.$s_table.'content AS cc ON cc.'.$s_table.'ID = c.id AND cc.languageID = ? WHERE p.contentTable = ? AND p.deleted <> ?';
			$o_query = $o_main->db->query($s_sql, array($s_default_input_language, $s_table, 1));
			if(!$o_query)
			{
				$s_sql = 'SELECT c.name, p.id AS pid, p.menulevelID FROM pageID AS p LEFT OUTER JOIN '.$s_table.' AS c ON p.contentID = c.id WHERE p.contentTable = ? AND p.deleted <> ?';
				$o_query = $o_main->db->query($s_sql, array($s_table, 1));
				if(!$o_query)
				{
					$v_return[0] = '1# Module missing "name" field in multi table || other error';
				}
			} else {
				$v_return[0] = '2# Module missing "name" field in multi table || other error';
			}
			if($o_query && $o_query->num_rows()>0)
			{
				foreach($o_query->result() as $o_row)
				{
					$v_return[$o_row->pid] = $o_row->name;
				}
			}
		}
	}
}
ob_end_clean();
echo json_encode($v_return);
?>