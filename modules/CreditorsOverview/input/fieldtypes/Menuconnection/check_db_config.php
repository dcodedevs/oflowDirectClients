<?php
//check if pageIDcontent is set
$v_tables = array(
	'pageIDcontent' => array(
		'fields' => array(
			'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
			'pageIDID' => 'INT(11) NOT NULL',
			'languageID' => 'CHAR(50) NOT NULL',
			'urlrewrite' => 'VARCHAR(255) NOT NULL',
			'lang_url_part' => 'CHAR(10) NOT NULL',
			'menu_url_part' => 'CHAR(100) NOT NULL',
			'content_url_part' => 'CHAR(100) NOT NULL',
			'menu_url_splitter' => 'CHAR(10) NOT NULL',
			'full_url_edit' => 'TINYINT NOT NULL DEFAULT 0',
		),
		'indexes' => array(
			array('PRIMARY', '', '`id`'),
			array('INDEX', 'Relation', array('`pageIDID`', '`languageID`')),
			array('INDEX', 'Search2', array('`urlrewrite`')),
		),
	),
	'sys_htaccess' => array(
		'fields' => array(
			'pageID' => 'INT(11) NOT NULL',
			'languageID' => 'CHAR(50) NOT NULL',
			'urlfrom' => 'VARCHAR(1000) NOT NULL',
			'urlto' => 'VARCHAR(1000) NOT NULL',
			'redirect' => 'TINYINT NOT NULL DEFAULT 0',
			'content_url_part' => 'CHAR(100) NOT NULL',
			'menu_url_splitter' => 'CHAR(10) NOT NULL',
		),
		'indexes' => array(
			array('INDEX', 'Relation', array('`pageID`', '`languageID`')),
			array('INDEX', 'RelationUrl', array('`urlfrom`(100)')),
		),
	),
	'pageIDlist' => array(
		'fields' => array(
			'id' => 'INT(11) NOT NULL AUTO_INCREMENT',
			'menulevelID' => 'INT(11) NOT NULL',
			'languageID' => 'CHAR(50) NOT NULL',
			'listurl' => 'VARCHAR(255) NOT NULL',
			'menu_url_splitter' => 'CHAR(10) NOT NULL',
		),
		'indexes' => array(
			array('PRIMARY', '', '`id`'),
			array('INDEX', 'Relation', array('`menulevelID`', '`languageID`')),
			array('INDEX', 'Search2', array('`listurl`')),
		),
	),
);

foreach($v_tables as $s_table => $v_table)
{
	if(!$o_main->db->table_exists($s_table))
	{
		$s_column = '';
		foreach($v_table['fields'] as $s_field => $s_option) $s_column .= (''!=$s_column?', ':'').'`'.$s_field.'` '.str_replace('AUTO_INCREMENT', '', $s_option);
		echo "CREATE TABLE `".$s_table."` (".$s_column.")";
		$o_main->db->query("CREATE TABLE `".$s_table."` (".$s_column.")");
	}
	foreach($v_table['fields'] as $s_field => $s_option)
	{
		if(!$o_main->db->field_exists($s_field, $s_table))
		{
			$o_main->db->query("ALTER TABLE `".$s_table."` ADD COLUMN `".$s_field."` ".str_replace('AUTO_INCREMENT', '', $s_option));
		}
	}
	$o_query = $o_main->db->query("SHOW INDEX FROM `".$s_table."`");
	$v_db_indexes = $o_query ? $o_query->result_array() : array();
	
	foreach($v_table['indexes'] as $v_index)
	{
		$b_found = FALSE;
		foreach($v_db_indexes as $v_db_index)
		{
			if('PRIMARY' == $v_index[0])
			{
				if('PRIMARY' == $v_db_index['Key_name']) $b_found = TRUE;
			} else if($v_index[1] == $v_db_index['Key_name']) {
				$b_found = TRUE;
			}
		}
		if(!$b_found)
		{
			if('PRIMARY' == $v_index[0])
			{
				$o_main->db->query("ALTER TABLE `".$s_table."` CHANGE COLUMN ".$v_index[2]." ".$v_index[2]." ".$v_table['fields'][trim($v_index[2], '`')]." FIRST, ADD PRIMARY KEY (".$v_index[2].")");
			} else {
				$o_main->db->query("ALTER TABLE `".$s_table."` ADD ".('PRIMARY' == $v_index[0] ? "PRIMARY KEY" : "INDEX `".$v_index[1]."`")." (".implode(", ", $v_index[2]).")");
			}
		}
	}
}