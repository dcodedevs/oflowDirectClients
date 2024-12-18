<?php
if(!function_exists("truncate_chars")) {
    function truncate_chars($text, $limit, $ellipsis = '...') {
        if( strlen($text) > $limit ) {
            $endpos = mb_strpos(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $text), ' ', $limit);
            if($endpos !== FALSE)
                $text = trim(mb_substr($text, 0, $endpos)) . $ellipsis;
        }
        return $text;
    }
}
$sql = "SELECT d.* FROM dashboard_clean_basisconfig d
WHERE d.content_status < 2
ORDER BY d.id DESC";
$o_query = $o_main->db->query($sql);
$dashboard_settings = $o_query ? $o_query->row_array() : array();
$params = array(
    'api_url' => $dashboard_settings['crm_account_url'],
    'access_token'=> $dashboard_settings['crm_account_token'],
    'module' => 'UpcomingUpdates',
    'action' => 'get_upcoming_updates',
    'params' => array(
    )
);
$response = fw_api_call($params, false);
$latestUpdates = array();
if($response['status']) {
    $latestUpdates = $response['list'];
}
?>

<div class="latest_update_wrapper">
    <?php foreach($latestUpdates as $latestUpdate) {
        ?>
        <div class="latest_update_row">
            <div class="latest_update_title"><?php echo $latestUpdate['title'];?></div>
            <div class="latest_update_date"><?php echo date("d.m.Y", strtotime($latestUpdate['created']));?></div>
            <?php
            $images = json_decode($latestUpdate['image'], true);
            foreach($images as $file){
                $externalApiAccount = $dashboard_settings['crm_account_url'];
                $fileAddition = "";
                $fileParts = explode('/',$file[1][0]);
                $fileName = array_pop($fileParts);
                $fileParts[] = rawurlencode($fileName);
                $filePath = implode('/',$fileParts);
                if($externalApiAccount != ""){
                    $hash = md5($externalApiAccount . '-' . $latestUpdate['id']);
                    $fileNameApi = "";
                    foreach($fileParts as $filePart) {
                        if($filePart != "uploads" && $filePart != "protected"){
                            $fileNameApi .= $filePart."/";
                        }
                    }
                    $fileNameApi = trim($fileNameApi, "/");
                    $fileAddition = "&externalApiAccount=".$externalApiAccount."&externalApiHash=".$hash."&file=".$fileNameApi;
                }
                if(strpos($file[1][0],'uploads/protected/')!==false)
                {
                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=upcomingupdates&field=file&ID='.$latestUpdate['id']."&".$fileAddition;
                } else {
                    $fileUrl = $externalApiAccount.'/../'.$file[1][0];
                }
                ?>
                <div class="latest_update_picture"><a href="<?php echo $fileUrl;?>" class="fancybox" rel="gallery"><img src="<?php echo $fileUrl?>" alt=""/></a></div>
                <?php
            }
            ?>
            <div class="latest_update_text">
                <?php
                    $textLimit = 300;
                    $textToTruncate = $textToShow = strip_tags($latestUpdate['text']);
                    if(strlen($textToTruncate) > $textLimit) {
                        $textToShow = "<span class='truncated_text'>".truncate_chars($textToTruncate, $textLimit)."</span>";
                    }
                    $latestUpdate['text'] = nl2br($textToShow);

                    echo $latestUpdate['text'];
                ?>
                <div class='post_readmore fw_text_link_color'><a class="optimize" href="<?php echo $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$s_current_module."&folderfile=output&folder=output&inc_obj=details&type=2&cid=".$latestUpdate['id'];?>"><?php echo $formText_ReadMore_output;?></a></div>
            </div>
        </div>
        <?php
    }?>
</div>
<script type="text/javascript">
    $(function(){
        $(".fancybox").fancybox();
    })
</script>
