<?php
$selectedId = $_POST['selected'] ? ($_POST['selected']) : 0;

$resources = array();

$s_sql = "SELECT * FROM customer_selfdefined_lists WHERE content_status < 2 ORDER BY name ASC";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) {
	$resources = $o_query->result_array();
}

?>
<option value=""><?php echo $formText_Select_output; ?></option>
<?php foreach($resources as $resource) { ?>
    <option value="<?php echo $resource['id']; ?>" <?php echo $resource['id'] == $selectedId ? 'selected="selected"' : ''; ?>><?php echo $resource['name']; ?></option>
<?php } ?>