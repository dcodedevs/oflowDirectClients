<?php
$filters = $v_data['params']['filters'];
// Process filter data
$list_filter = $filters['list_filter'] ? $filters['list_filter'] : 'active';
$customer_filter = $filters['customer_filter'] ? $filters['customer_filter'] : 0;
$search_filter = $filters['search_filter'] ? $filters['search_filter'] : '';
$page = $filters['page'] ? $filters['page'] : 1;
$per_page = $filters['perPage'] ? $filters['perPage'] : 100;

// Offset
$offset = ($page-1)*$per_page;
if($page > 1){
    $offset -= ($per_page-10);
}

// Return data array
$return_data = array(
    'list' => array()
);

$searchSql = "";
if ($search_filter) {
    $searchSql = " AND (p.name LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR p.middle_name LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    OR p.last_name LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR p.job_title LIKE  '%".$o_main->db->escape_like_str($search_filter)."%' OR c.name LIKE  '%".$o_main->db->escape_like_str($search_filter)."%'
    )";
}
$sqlNoLimit = "SELECT p.id, CONCAT_WS(' ',p.name, p.middlename, p.lastname) AS name, p.title, p.email
FROM contactperson p
JOIN workplanlineworker wlw ON wlw.employeeId = p.id
JOIN project2 wl ON wl.id = wlw.projectId
JOIN customer c ON c.id = wl.customerId
WHERE wlw.date >= DATE_SUB(NOW(),INTERVAL 1 YEAR) AND wl.type = '1' AND c.id = '".$o_main->db->escape_str($customer_filter)."' ".$searchSql ." GROUP BY p.id ORDER BY p.name ASC";

$o_query =  $o_main->db->query($sqlNoLimit);
$l_total_count = 0;
if($o_query && $o_query->num_rows()>0)
$l_total_count = $o_query->num_rows();

$sql = $sqlNoLimit." LIMIT ".$per_page." OFFSET ".$offset;
$v_return['list'] = array();
$o_result = $o_main->db->query($sql);
if($o_result && $o_result->num_rows()>0)
foreach($o_result->result_array() AS $row)
{
	$row['files'] = array();
	$s_sql = "SELECT * FROM people_files WHERE peopleId = '".$o_main->db->escape_str($row['id'])."' AND content_status < 2 ORDER BY people_files.filename ASC";
	$o_find = $o_main->db->query($s_sql);
	if($o_find && $o_find->num_rows()>0)
	foreach($o_find->result_array() AS $v_file)
	{
		if($v_file['visible_for_customers']) {
			$row['files'][] = array(
				'id' => $v_file['id'],
				'name' => $v_file['filename'],
				'file' => $v_file['file']
			);
		}
	}
	array_push($v_return['list'], $row);
}
$v_return['status'] = 1;
$v_return['total_item_count'] = $l_total_count;
