<?php
if(!$o_main->db->table_exists('sys_checksum'))
{
	$o_main->db->simple_query("CREATE TABLE `sys_checksum` (
		`name` CHAR(50) NOT NULL,
		`content_checksum` TEXT NOT NULL,
		`content_rows` INT(10) NOT NULL,
		`created` DATETIME NOT NULL,
		`alert_handled` TINYINT(1) NOT NULL,
		UNIQUE INDEX `name_idx` (`name`) USING BTREE
	)");
}
$o_query = $o_main->db->query("SHOW TRIGGERS WHERE `Trigger` = 'people_after_insert'");
if($o_query && $o_query->num_rows()==0)
{
	/*$o_main->db->simple_query("CREATE TRIGGER `people_after_insert` AFTER INSERT ON `people` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT MIN(LEAST(
		  LENGTH(@crc := SHA1(CONCAT(
			 @crc,
			 SHA1(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))))),
		  @cnt := @cnt + 1
	   )) INTO @dump
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");*/
	
	$o_main->db->simple_query("CREATE TRIGGER `people_after_insert` AFTER INSERT ON `people` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
	
	$o_main->db->simple_query("CREATE TRIGGER `people_after_update` AFTER UPDATE ON `people` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
	
	$o_main->db->simple_query("CREATE TRIGGER `people_after_delete` AFTER DELETE ON `people` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
	
	$o_main->db->simple_query("CREATE TRIGGER `people_crm_contactperson_connection_after_insert` AFTER INSERT ON `people_crm_contactperson_connection` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
	
	$o_main->db->simple_query("CREATE TRIGGER `people_crm_contactperson_connection_after_update` AFTER UPDATE ON `people_crm_contactperson_connection` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
	
	$o_main->db->simple_query("CREATE TRIGGER `people_crm_contactperson_connection_after_delete` AFTER DELETE ON `people_crm_contactperson_connection` FOR EACH ROW BEGIN
	SET @crc := '', @cnt := 0, @dump := '';
	SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;
	
	INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0;
	END");
}
$o_query = $o_main->db->query("SELECT * FROM sys_checksum WHERE name = 'people_crm_ins_sync'");
if($o_query && $o_query->num_rows()==0)
{
	$o_main->db->trans_start();
	$o_main->db->query("SET @crc := '', @cnt := 0, @dump := ''");
	$o_main->db->query("SELECT SUM(CRC32(CONCAT_WS(IFNULL(p.email, ''), IFNULL(p.name, ''), IFNULL(p.middle_name, ''), IFNULL(p.last_name, ''), IFNULL(p.phone_prefix, ''), IFNULL(p.phone, ''), IFNULL(c.crm_contactperson_id, 0), IFNULL(c.crm_customer_id, 0), IFNULL(c.admin, 0), IFNULL(c.notVisibleInMemberOverview, 0), p.content_status))), COUNT(p.id) INTO @crc, @cnt
	FROM people AS p LEFT OUTER JOIN people_crm_contactperson_connection AS c ON c.people_id = p.id WHERE p.content_status < 2 ORDER BY c.crm_customer_id, p.email;");
	
	$o_main->db->query("INSERT INTO sys_checksum SET name = 'people_crm_ins_sync', content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0 ON DUPLICATE KEY UPDATE content_checksum = @crc, content_rows = @cnt, created = NOW(), alert_handled = 0");
	$o_main->db->trans_complete();
}