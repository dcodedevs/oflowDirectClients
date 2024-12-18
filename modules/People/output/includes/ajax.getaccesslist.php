<form class="specifiedForm">
    <?php
        $_GET['accessID'] = $_POST['accessID'];

        $includeFile = __DIR__."/../../../../fw/getynet_fw/modules/users/output/output_javascript.php";
        if(is_file($includeFile)) include($includeFile);

        include(__DIR__."/../../../../fw/getynet_fw/modules/users/output/accountaccesslist.php");
    ?>
</form>
