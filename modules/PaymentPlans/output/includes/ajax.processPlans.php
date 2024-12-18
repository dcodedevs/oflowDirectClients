<?php
require(__DIR__."/../../autotask_process_payments/fnc_process_plan.php");

list($completedPlans, $interruptedPlans, $errors) = process_plan($formText_ErrorUpdatingDatabaseForPlan_output, $formText_ErrorUpdatingDatabaseForPlanline_output);

echo '<b>'.$formText_CompletedPlans_output.':</b>';
foreach($completedPlans as $completedPlan){
    echo $completedPlan['id']."<br/>";;
}
echo "<br/>";
echo '<b>'.$formText_InterruptedPlans_output.':</b>';
foreach($interruptedPlans as $interruptedPlan){
    echo $interruptedPlan['id']."<br/>";;
}
echo "<br/>";
echo '<b>'.$formText_Errors_output.':</b>';
foreach($errors as $errors){
    echo $error."<br/>";
}
?>
