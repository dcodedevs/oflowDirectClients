<?php
// List btn
if(!function_exists("APIconnectAccount")) include_once(__DIR__."/../../input/includes/APIconnect.php");

$sql = "select * from accountinfo";
$o_query = $o_main->db->query($sql);
$v_accountinfo = $o_query ? $o_query->row_array() : array();

$cid = $o_main->db->escape_str($_GET['cid']);

$sql = "SELECT * FROM collecting_cases WHERE id = $cid";
$o_query = $o_main->db->query($sql);
$caseData = $o_query ? $o_query->row_array() : array();


function formatHour($hour){
	return str_replace(".", ",", floatval(number_format($hour, 2, ".", "")));
}

$list_filter = $_SESSION['list_filter'] ? ($_SESSION['list_filter']) : 'all';
$responsibleperson_filter = $_SESSION['responsibleperson_filter'] ? ($_SESSION['responsibleperson_filter']) : '';
$list_filter_main = $_SESSION['list_filter_main'] ? ($_SESSION['list_filter_main']) : '';
$search_filter = $_SESSION['search_filter'] ? ($_SESSION['search_filter']) : '';
$casetype_filter = $_SESSION['casetype_filter'] ? $_SESSION['casetype_filter'] : '';
$search_by = $_SESSION['search_by'] ? ($_SESSION['search_by']) : 1;

$s_list_link = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=list&list_filter=".$list_filter."&search_filter=".$search_filter;

$s_page_reload_url = $_SERVER['PHP_SELF']."?pageID=".$_GET['pageID']."&accountname=".$_GET['accountname']."&companyID=".$_GET['companyID']."&caID=".$_GET['caID']."&module=".$module."&folderfile=output&folder=output&inc_obj=details&cid=".$caseData['id']."&view=".$list_filter_main;


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
        'id'=>$cid
    )
);
$response = fw_api_call($params, false);
$article = array();
if($response['status']) {
    $article = $response['item'];
    $image = json_decode($article['image'], true);
}
?>

<div id="p_container" class="p_container">
	<div class="p_containerInner">
		<div class="p_content">
			<div class="p_pageContent">
                <a href="<?php echo $s_list_link?>" class="output-click-helper optimize back-to-list" style="display: block; margin-bottom:10px;"><?php echo $formText_BackToList_outpup;?></a>
                <?php
                if($article){ ?>
                    <div class="article_single_page">
                        <div class="article_left">
                            <div class="article_info">
                                <div class="article_title"><?php echo $article['title'];?></div>
                                <div class="article_text">
    								<?php if(count($image) > 0) { ?>
    						            <div class="article_image">
    										<?php
                                            foreach($image as $file){
                                                $externalApiAccount = $dashboard_settings['crm_account_url'];
                                                $fileAddition = "";
                                                $fileParts = explode('/',$file[1][0]);
                                                $fileName = array_pop($fileParts);
                                                $fileParts[] = rawurlencode($fileName);
                                                $filePath = implode('/',$fileParts);
                                                if($externalApiAccount != ""){
                                                    $hash = md5($externalApiAccount . '-' . $article['id']);
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
                                                    $fileUrl = $extradomaindirroot.'/../'.$file[1][0].'?caID='.$_GET['caID'].'&table=upcomingupdates&field=image&ID='.$article['id']."&".$fileAddition;
                                                } else {
                                                    $fileUrl = $externalApiAccount.'/../'.$file[1][0];
                                                }
    											?>
    							                <a href="<?php echo $fileUrl?>" class="fancybox" rel="gallery"><img src="<?php echo ($fileUrl)?>" class=""/></a>
    										<?php } ?>
    						            </div>
    						        <?php } ?>
    								<?php echo $article['text'];?>
    								<div class="clear"></div>
    							</div>
                            </div>

                        </div>
                        <div class="article_right">
                            <!-- <div class="article_right_info">
                                <div class="article_creator">
                                    <div class="article_creator_image">
    									<?php if($imgToDisplay != "") { ?>
    										<img src="<?php echo $imgToDisplay; ?>" alt="<?php echo $nameToDisplay;?>" title="<?php echo $nameToDisplay;?>"/>
    									<?php } ?>
                                    </div>
                                    <div class="article_creator_info">
                                        <?php echo $nameToDisplay;?>
                                        <div><?php echo $formText_Published_output.": ". date("d.m.Y", strtotime($article['created']));?></div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                            </div> -->
                        </div>
                        <div class="clear"></div>
                    </div>
                <?php } ?>
			</div>
		</div>
	</div>
</div>
<style>
    .article_title {
        font-size: 18px;
        margin-bottom: 15px;
    }
    .article_single_page {
        background: #fff;
    }
    .article_single_page .article_left {
        float: left;
        width: calc(100% - 250px);
    }
    .article_single_page .article_right {
        float: right;
        width: 250px;
    }
    .article_left .article_info {
        padding: 10px 20px;
        border-right: 2px solid #f6f6f6;
    }
    .article_left .article_info .editRow {
        text-align: right;
        margin-top: 10px;
    }
    .article_left .article_info .article_edit {
        cursor: pointer;
    }
    .article_left .article_info .article_delete {
        cursor: pointer;
        margin-left: 10px;
    }
    .article_single_page .article_right .article_right_info {
        padding: 10px 20px;
        color: #7f7f7f;
    }
    .article_right_info .article_creator {
        margin-top: 10px;
        margin-bottom: 15px;
    }
    .article_right_info .article_creator .article_creator_image {
        width: 40px;
        height: 40px;
        float: left;
		border-radius: 50%;
		display: inline-block;
		vertical-align: middle;
		overflow: hidden;
		position: relative;
    }
	.article_right_info .article_creator .article_creator_image img {
		width: 100%;
		height: auto;
		vertical-align: top;
	}
    .article_right_info .article_creator .article_creator_info  {
        float: right;
        width: calc(100% - 50px);
    }
    .article_left .article_tags {
        margin-bottom: 15px;
        margin-top: 15px;
        color: #7f7f7f;
    }
    .article_right_info .article_actions {
        margin-bottom: 20px;
    }
    .article_right_info .article_actionbtn {
        margin-bottom: 7px;
        color: #fff;
        padding: 5px 10px;
        text-align: center;
        border-radius: 4px;
        overflow: hidden;
        background: #53b2e4;
        cursor: pointer;
        font-weight: bold;
    }
	.article_single_page .article_image {
		float: right;
		width: 200px;
		overflow: hidden;
	}
	.article_single_page .article_image img {
		width: calc(100% - 20px);
		height: auto;
		margin-bottom: 10px;
		margin-left: 20px;
	}
	body.mobile .article_single_page .article_left {
		float: none;
		width:100%;
	}
	body.mobile .article_single_page .article_right {
		float: none;
		width: 100%;
	}
	body.mobile .article_single_page .article_image {
		float: none;
		width: 100%;
	}
	body.mobile .article_single_page .article_image img {
		width: 100%;
		margin-left: 0;
	}
	.tag_selector {
		cursor: pointer;
	}
</style>
<script type="text/javascript">
var out_popup;
var out_popup_options={
	follow: [true, true],
	followSpeed:0,
	fadeSpeed: 0,
	modalClose: false,
	escClose: false,
	closeClass:'b-close',
	onOpen: function(){
		$(this).addClass('opened');
	},
	onClose: function(){
		$(this).removeClass('opened');
		if($(this).is('.close-reload')) {
			loadView("details", {type:1, cid:"<?php echo $cid;?>"});
		}
	}
};
$(function(){
    $(".fancybox").fancybox();
    $(".article_edit").on('click', function(e){
        e.preventDefault();
        var data = {
            cid: '<?php echo $article['id']?>'
        };
        ajaxCall('edit_article', data, function(json) {
            $('#popupeditboxcontent').html('');
            $('#popupeditboxcontent').html(json.html);
            out_popup = $('#popupeditbox').bPopup(out_popup_options);
            $("#popupeditbox:not(.opened)").remove();
        });
    });
    $(".article_delete").on('click', function(e){
        e.preventDefault();
        var data = {
            cid: '<?php echo $article['id']?>',
            output_delete: 1
        };
        bootbox.confirm('<?php echo $formText_ConfirmDeleteArticle_output; ?>', function(result) {
            if (result) {
                ajaxCall('edit_article', data, function(json) {
                    loadView("list");
                });
            }
        })
    });
})
</script>
