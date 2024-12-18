<?php

$buildingOwnerProjectCode =  isset($_POST['buildingOwnerProjectCode']) ? intval($_POST['buildingOwnerProjectCode']) : -1;
$projectCode =  $_POST['projectCode'] ? ($_POST['projectCode']) : 0;
$ownercompany_id =  $_POST['ownercompany_id'] ? ($_POST['ownercompany_id']) : 0;
$fromCustomerContent = $_POST['getProjectFromCustomerContent'] ? ($_POST['getProjectFromCustomerContent']) : 0;
if(isset($_POST['customer_id']) && 0 < $_POST['customer_id'])
{
	$s_sql = "SELECT * FROM customer WHERE id = '".$o_main->db->escape_str($_POST['customer_id'])."'";
	$o_query = $o_main->db->query($s_sql);
	$v_customer = ($o_query ? $o_query->row_array() : array());

	if($v_customer['accounting_project_number'] != '')
	{
		$projectCode = $v_customer['accounting_project_number'];
	}
}

$s_sql = "SELECT * FROM orders_basisconfig";
$o_query = $o_main->db->query($s_sql);
if($o_query && $o_query->num_rows()>0) $orderBasisConfig = $o_query->row_array();

$projectMandatory = false;
if($orderBasisConfig && $orderBasisConfig['activeProjectMandatory']) {
	$projectMandatory = true;
}
if(isset($_POST['projectMandatory'])){
    $projectMandatory = true;
}
?>
<select name="projectCode" <?php if($projectMandatory) { echo 'required';}?>>

	<option value=""><?php echo $formText_SelectProject_output; ?></option>
	<?php
    function getProjects($o_main, $ownercompany_id, $buildingOwnerProjectCode, $parentNumber = 0, $fromCustomerContent = 0) {
        $projects = array();

		$currentOwnerCompanyCount_sql = $o_main->db->query("SELECT * FROM ownercompany WHERE content_status < 2");
		$currentOwnerCompanyCount = $currentOwnerCompanyCount_sql->num_rows();
		$ownercompany_sql = "";
		if($currentOwnerCompanyCount > 1){
			$ownercompany_sql = " AND ownercompany_id = '".$o_main->db->escape_str($ownercompany_id)."'";
		}
		if($fromCustomerContent > 0) {
			$projectCodesAdded = array();
			$s_sql = "SELECT * FROM subscriptionmulti WHERE customerId = '".$o_main->db->escape_str($fromCustomerContent)."' AND content_status < 1 AND startDate <= CURDATE() AND ((stoppedDate = '0000-00-00' OR stoppedDate is null) OR (stoppedDate > CURDATE()))";
			$o_query = $o_main->db->query($s_sql);
			$repeatingorders = $o_query ? $o_query->result_array() : array();
			foreach($repeatingorders as $repeatingorder){
				if(!in_array($repeatingorder['projectId'], $projectCodesAdded)){
					$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($repeatingorder['projectId'])."'";
					$o_query = $o_main->db->query($s_sql);
					$row = $o_query ? $o_query->row_array() : array();
					if($row){
						array_push($projects, array(
							'id' => $row['id'],
							'name' => $row['name'],
							'number' => $row['projectnumber'],
							'parentNumber' => 0,
							'children' => array()
						));
						$projectCodesAdded[] = $repeatingorder['projectId'];
					}
				}
			}
			$s_sql = "SELECT * FROM project2 WHERE customerId = '".$o_main->db->escape_str($fromCustomerContent)."' AND content_status < 1";
			$o_query = $o_main->db->query($s_sql);
			$repeatingorders = $o_query ? $o_query->result_array() : array();
			foreach($repeatingorders as $repeatingorder){
				if(!in_array($repeatingorder['projectCode'], $projectCodesAdded)){
					$s_sql = "SELECT * FROM projectforaccounting WHERE projectnumber = '".$o_main->db->escape_str($repeatingorder['projectCode'])."'";
					$o_query = $o_main->db->query($s_sql);
					$row = $o_query ? $o_query->row_array() : array();
					if($row){
						array_push($projects, array(
							'id' => $row['id'],
							'name' => $row['name'],
							'number' => $row['projectnumber'],
							'parentNumber' => 0,
							'children' => array()
						));
						$projectCodesAdded[] = $repeatingorder['projectCode'];
					}
				}
			}
		} else {
	        if ($parentNumber > 0) {
	            $o_main->db->order_by('projectnumber', 'ASC');
	            $o_query = $o_main->db->get_where('projectforaccounting', array('parentNumber' => $parentNumber));
	        } else if($buildingOwnerProjectCode >= 0){
	            $o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE (parentNumber IS NULL OR parentNumber = 0) AND projectnumber = '".$buildingOwnerProjectCode."' ORDER BY projectnumber");
	        } else {
				$o_query = $o_main->db->query("SELECT * FROM projectforaccounting WHERE (parentNumber IS NULL OR parentNumber = 0) ".$ownercompany_sql." ORDER BY projectnumber");
			}
	        if ($o_query && $o_query->num_rows()) {
	            foreach ($o_query->result_array() as $row) {
	                array_push($projects, array(
	                    'id' => $row['id'],
	                    'name' => $row['name'],
	                    'number' => $row['projectnumber'],
	                    'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
	                    'children' => getProjects($o_main, $ownercompany_id, $buildingOwnerProjectCode, $row['projectnumber'], $fromCustomerContent)
	                ));
	            }
	        }
		}

        return $projects;
    }

    function getProjectsOptionsListHtml($projects, $level, $accountingproject_code) {
        ob_start(); ?>

        <?php foreach ($projects as $project): ?>
            <option value="<?php echo $project['number']; ?>" <?php echo $project['number'] == $accountingproject_code ? 'selected="selected"' : ''; ?>>
                <?php
                $identer = '';
                for($i = 0; $i < $level; $i++) { $identer .= '-'; }
                echo $identer;
                ?>
                <?php echo $project['number']; ?> <?php echo $project['name']; ?>
            </option>

            <?php if (count($project['children'])): ?>
                <?php echo getProjectsOptionsListHtml($project['children'], $level+1, $accountingproject_code); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php return ob_get_clean();
    }

    $projects = getProjects($o_main, $ownercompany_id, $buildingOwnerProjectCode, 0, $fromCustomerContent);
    echo getProjectsOptionsListHtml($projects, 0, $projectCode);

	?>
</select>
