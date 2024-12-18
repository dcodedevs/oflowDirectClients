<?php
$buildingOwnerId = isset($_POST['buildingOwnerId']) ? $_POST['buildingOwnerId'] : 0;
$projectId =  isset($_POST['projectId']) ? $_POST['projectId'] : 0;
$orderBasisConfig = array();
$o_query = $o_main->db->query("SELECT * FROM orders_basisconfig");
if($o_query && $o_query->num_rows()>0) $orderBasisConfig = $o_query->row_array();
$projectMandatory = false;
if($orderBasisConfig && $orderBasisConfig['activeProjectMandatory']) {
	$projectMandatory = true;
}
?>
<select name="projectId" <?php if($projectMandatory) { echo 'required';}?>>
    <option value=""><?php echo $formText_SelectProject_output; ?></option>
    <?php
	$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE ownercompany_id = ? ORDER BY name ASC", array($buildingOwnerId));
	if($o_query && $o_query->num_rows()>0)
	foreach($o_query->result_array() as $row)
	{
		?><option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $projectId ? 'selected="selected"' : ''; ?>><?php echo $row['name']; ?></option><?php
	}
	?>
?>
</select>