<?php

$buildingOwnerdepartmentCode =  isset($_POST['buildingOwnerdepartmentCode']) ? intval($_POST['buildingOwnerdepartmentCode']) : -1;
$departmentCode =  $_POST['departmentCode'] ? ($_POST['departmentCode']) : '';

$departmentMandatory = false;

if(isset($_POST['projectMandatory'])){
    $departmentMandatory = true;
}
?>
<select name="departmentCode" <?php if($departmentMandatory) { echo 'required';}?> autocomplete="off">

	<option value=""><?php echo $formText_SelectDepartment_output; ?></option>
	<?php
    function getDepartments($o_main, $buildingOwnerdepartmentCode, $parentNumber = 0) {
        $departments = array();

        if ($parentNumber) {
            $o_main->db->order_by('departmentnumber', 'ASC');
            $o_query = $o_main->db->get_where('departmentforaccounting', array('parentNumber' => $parentNumber));
        } else if($buildingOwnerdepartmentCode >= 0){
            $o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 AND departmentforaccounting.departmentnumber = '".$buildingOwnerdepartmentCode."' ORDER BY departmentnumber");
        } else {
            $o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE parentNumber IS NULL OR parentNumber = 0 ORDER BY departmentnumber");
        }


        if ($o_query && $o_query->num_rows()) {
            foreach ($o_query->result_array() as $row) {
                array_push($departments, array(
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'number' => $row['departmentnumber'],
                    'parentNumber' => $row['parentNumber'] ? $row['parentNumber'] : 0,
                    'children' => getDepartments($o_main, $buildingOwnerdepartmentCode, $row['departmentnumber'])
                ));
            }
        }

        return $departments;
    }

    function getDepartmentsOptionsListHtml($departments, $level, $accountingdepartment_code) {
        ob_start(); ?>

        <?php foreach ($departments as $department): ?>
            <option value="<?php echo $department['number']; ?>" <?php echo $department['number'] == $accountingdepartment_code ? 'selected="selected"' : ''; ?>>
                <?php
                $identer = '';
                for($i = 0; $i < $level; $i++) { $identer .= '-'; }
                echo $identer;
                ?>
                <?php echo $department['number']; ?> <?php echo $department['name']; ?>
            </option>

            <?php if (count($department['children'])): ?>
                <?php echo getDepartmentsOptionsListHtml($department['children'], $level+1, $accountingdepartment_code); ?>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php return ob_get_clean();
    }

    $departments = getDepartments($o_main, $buildingOwnerdepartmentCode);
    echo getDepartmentsOptionsListHtml($departments, 0, $departmentCode);

	?>
</select>
