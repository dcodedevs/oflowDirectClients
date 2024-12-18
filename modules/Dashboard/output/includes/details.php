<?php
$detailpage_type = $_GET['type'];

if($detailpage_type > 0){
    if($detailpage_type == 1){
        include("details_latest_updates.php");
    } else if($detailpage_type == 2){
        include("details_upcoming_updates.php");
    }
}
?>
