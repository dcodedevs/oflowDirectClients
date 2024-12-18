<?php
ob_start();
session_start();

define('ACCOUNT_PATH', realpath(__DIR__.'/../../../'));
define('BASEPATH', ACCOUNT_PATH.DIRECTORY_SEPARATOR);

require_once(BASEPATH.'elementsGlobal/cMain.php');
require_once(__DIR__.'/../../account_fw/includes/fn_fw_api_call.php');

$v_return = array(
	'status' => 0,
);


$includeFile = __DIR__."/../languages/default.php";
if(is_file($includeFile)) include($includeFile);
$includeFile = __DIR__."/../languages/".$_POST['language_id'].".php";
if(is_file($includeFile)) include($includeFile);

$v_description = array(
	$formText_GeneralInfoAboutArticles_Helppage,
	$formText_GeneralInfoAboutModules_Helppage,
);
$v_subtitle = array(
	$formText_AllArticles_Helppage,
	$formText_AllModules_Helppage,
);
$v_help_article_tabs = array(
	$formText_Articles_Helppage,
	$formText_Modules_Helppage,
	$formText_Contact_Helppage,
	$formText_Search_Helppage,
);

$o_query = $o_main->db->get('accountinfo');
$v_accountinfo = $accountinfo = $o_query ? $o_query->row_array() : array();

$fw_helppage_content = '';
$l_help_article_tab = $_POST['tab_id'];
if(isset($_POST['module']) && '' != $_POST['module']) $l_help_module = $_POST['module'];
if(isset($_POST['article_id']) && 0 < $_POST['article_id']) $l_help_article_id = $_POST['article_id'];
$_SESSION['help_page_module'] = $l_help_module;
$_SESSION['help_page_article_id'] = $l_help_article_id;
$_SESSION['help_page_article_tab'] = $l_help_article_tab;

if(1 >= $l_help_article_tab)
{
	if($l_help_article_id == 0)
	{
		$v_params = array(
			'api_url' => 'https://help.getynet.com/api/',
			'module' => 'HelpArticle',
			'action' => 'get_help_articles',
			'params' => array(
				'app_id' => $v_accountinfo['getynet_app_id'],
				'language_id' => $v_accountinfo['customerlanguageID'],
				'article_id' => $l_help_article_id,
				'article_type' => $l_help_article_tab == 0 ? 1 : 2,
			)
		);
		$v_response = fw_api_call($v_params, FALSE);
		$v_help_articles = array();
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			$fw_helppage_content .= '<div class="fw-hp-description">'.$v_description[$l_help_article_tab].'</div>';
			$fw_helppage_content .= '<div class="fw-hp-subtitle">'.$v_subtitle[$l_help_article_tab].'</div>';
			foreach($v_response['items'] as $v_help_article)
			{
				$fw_helppage_content .= '<div class="fw-hp-article-btn fw-hp-related" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_help_article['id'].'" data-module="'.$v_help_article['module_folder'].'">'.$v_help_article['name'].'</div>';
			}
			$v_return['status'] = 1;
			$v_return['html'] = $fw_helppage_content;
		}
	} else {
		$s_version = '';
		if(isset($_POST['module']) && '' != $_POST['module'])
		{
			$s_path = BASEPATH.'modules/'.$_POST['module'];
			if($o_dir = opendir($s_path))
			{
				while(($s_file = readdir($o_dir)) !== FALSE)
				{
					if(strpos($s_file,".ver") > 0 && !stristr($s_file,"LCK"))
					{
						$s_version = str_replace("_",".",substr($s_file,0,strpos($s_file,".ver")));
					}
				}
			}
		}
		
		$v_params = array(
			'api_url' => 'https://help.getynet.com/api/',
			'module' => 'HelpArticle',
			'action' => 'get_help_content',
			'params' => array(
				'article_id' => $l_help_article_id,
				'version_from' => $s_version,
			)
		);
		$v_response = fw_api_call($v_params, FALSE);
		if(isset($v_response['status']) && 1 == $v_response['status'])
		{
			$fw_helppage_content .= '<div class="fw-hp-breadcrumb">';
			$fw_helppage_content .= '<span class="fw-hp-tab fw-hp-article-btn" data-tab-id="'.$l_help_article_tab.'">'.$v_help_article_tabs[$l_help_article_tab].'</span>';
			foreach($v_response['parents'] as $v_parent)
			{
				$fw_helppage_content .= ' > <span class="fw-hp-article-btn fw-hp-related'.($v_parent['level'] == 0 ? '-active' : '').'" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_parent['id'].'" data-module="'.$v_parent['module_folder'].'">'.$v_parent['name'].'</span>';
			}
			$fw_helppage_content .= '</div>';
			
			$fw_helppage_content .= '<div class="fw-hp-title">'.$v_response['title'].'</div>';
			$fw_helppage_content .= '<div class="fw-hp-description">'.$v_response['text'].'</div>';
			if(sizeof($v_response['items'])>0)
			{
				$fw_helppage_content .= '<div class="fw-hp-subtitle">'.$formText_RelatedArticles_Helppage.'</div>';
				foreach($v_response['items'] as $v_help_article)
				{
					$fw_helppage_content .= '<div class="fw-hp-article-btn fw-hp-related" data-tab-id="'.$l_help_article_tab.'" data-article-id="'.$v_help_article['id'].'" data-module="'.$v_help_article['module_folder'].'">'.$v_help_article['name'].'</div>';
				}
			}
		} else {
			$fw_helppage_content .= '<center>'.$formText_ContentNotFound_Helppage.'</center>';
		}
		$v_return['status'] = 1;
		$v_return['html'] = $fw_helppage_content;
	}
}

if(0 == $v_return['status'])
{
	$v_return['error'] = $formText_ErrorOccurredHandlingRequest_Helppage;
}

echo json_encode($v_return);
