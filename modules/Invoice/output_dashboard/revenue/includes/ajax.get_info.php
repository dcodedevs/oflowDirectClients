<?php
if(!function_exists("include_local")) include(__DIR__."/../../../input/includes/fn_include_local.php");

include_once(__DIR__."/readOutputLanguage.php");

$currentYear = date("Y");
$monthArray = array(
    1=>$formText_January_output,
    2=>$formText_February_output,
    3=>$formText_March_output,
    4=>$formText_April_output,
    5=>$formText_May_output,
    6=>$formText_June_output,
    7=>$formText_July_output,
    8=>$formText_August_output,
    9=>$formText_September_output,
    10=>$formText_October_output,
    11=>$formText_November_output,
    12=>$formText_December_output
);
$currentMonth = date("n");
if(isset($_POST['year'])) {
    $currentYear = $_POST['year'];
    if($currentYear < date("Y")){
        $currentMonth = 12;
    }
}

$departmentActive = false;
$o_query = $o_main->db->query("SELECT * FROM departmentforaccounting WHERE content_status < 2");
$departmentCount = $o_query ? $o_query->num_rows() : 0;
if($departmentCount > 0){
    $departmentActive = true;
}
?>
<?php
$totalRevenue = 0;
for($x = 1; $x <= $currentMonth; $x++){
    $currentMonthRevenue = 0;
    $monthTime = strtotime("01.".$x.".".$currentYear);
    $monthStart = date("Y-m-01", $monthTime);
    $monthEnd = date("Y-m-t", $monthTime);
    $o_query = $o_main->db->query("SELECT SUM(invoice.totalExTax) as totalMonthRevenue FROM invoice WHERE invoiceDate >= ? AND invoiceDate <= ?", array($monthStart,$monthEnd));
    $invoice_info = $o_query ? $o_query->row_array() : array();
    $currentMonthRevenue = $invoice_info['totalMonthRevenue'];
    ?>
    <div class="invoice_month_row">
        <span class="textLabel"><?php echo $monthArray[$x]?></span><span class="number"><?php echo number_format($currentMonthRevenue, 0, ",", " ");?></span>
        <div class="clear"></div>
        <?php
        if($departmentActive){
            $o_query = $o_main->db->query("SELECT SUM(o.priceTotal) as totalMonthRevenue, dep.name as departmentName
            FROM invoice
            LEFT OUTER JOIN customer_collectingorder co ON co.invoiceNumber = invoice.id
            LEFT OUTER JOIN departmentforaccounting dep ON dep.departmentnumber = co.department_for_accounting_code
            LEFT OUTER JOIN orders o ON o.collectingorderId = co.id
            WHERE invoice.invoiceDate >= ? AND invoice.invoiceDate <= ?
            GROUP BY co.department_for_accounting_code", array($monthStart,$monthEnd));
            $invoice_departments = $o_query ? $o_query->result_array() : array();

                // var_dump($monthStart, $monthEnd);
            foreach($invoice_departments as $invoice_department) {
                $departmentName = $invoice_department['departmentName'];
                if($departmentName == null) {
                    $departmentName = $formText_NoDepartment_output;
                }
                ?>
                <div class="invoice_month_row_department">
                    <span class="textLabel"><?php echo $departmentName;?></span><span class="number"><?php echo number_format($invoice_department['totalMonthRevenue'], 0, ",", " ");?></span>
                    <div class="clear"></div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
    <?php
    $totalRevenue+= $currentMonthRevenue;
}
?>
<div class="invoice_month_row">
    <span class="textLabel"><?php echo $formText_Total_output;?></span><span class="number"><?php echo number_format($totalRevenue, 0, ",", " ");?></span>

</div>
