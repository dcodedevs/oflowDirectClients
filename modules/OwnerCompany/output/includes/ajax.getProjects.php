<?php
$buildingOwnerId = isset($_POST['buildingOwnerId']) ? $_POST['buildingOwnerId'] : 0;
$projectId =  isset($_POST['projectId']) ? $o_main->db->escape($_POST['projectId']) : 0;
?>
<select name="projectId">
    <option value=""><?php echo $formText_SelectProject_output; ?></option>
    <?php
	$sql = "SELECT * FROM projectforaccounting WHERE ownercompany_id = ? ORDER BY name ASC";
	$result = $o_main->db->query($sql, array($buildingOwnerId));
	if($result && $result->num_rows() > 0)
	foreach($result->result() AS $row){ ?>
	    <option value="<?php echo $row->id; ?>" <?php echo $row->id == $projectId ? 'selected="selected"' : ''; ?>><?php echo $row->name; ?></option>
	<?php } ?>
?>
</select>