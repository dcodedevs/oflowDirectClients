<?php
$s_lang_file = __DIR__."/../../../languages/default.php";
if(is_file($s_lang_file)) include($s_lang_file);
$s_lang_file = __DIR__."/../../../languages/".$_GET['lang'].".php";
if(is_file($s_lang_file)) include($s_lang_file);

$s_api_file = __DIR__ .'/../../../includes/APIconnector.php';
if(!function_exists("APIconnectorUser") && file_exists($s_api_file)) require_once($s_api_file);

$s_response = APIconnectorUser("contactsetget", $_COOKIE['username'], $_COOKIE['sessionID'], array('SHOW_SET'=>array(), 'SHOW_COMPANY'=>array()));
$v_contactset = json_decode($s_response, TRUE);
$l_contactset_count_total = sizeof($v_contactset['sets']);
$l_contactset_count_selected = 0;

foreach($v_contactset['sets'] as $item)
{
	if ($item['active']) $l_contactset_count_selected++;
}
?>
<a href="#" class="button" id="fwcl_chat_groups_button"><?php echo $formText_MyContactGroups_Chat2; ?> (<span class="selected"><?php echo $l_contactset_count_selected ?></span> / <span class="all"><?php echo $l_contactset_count_total; ?></span>) <span class="icon icon-arrow-right"></a>
<div class="filter_groups_checkboxes">
	<ul id="fwcl_chat_groups">
		<li><input type="checkbox" class="showall"<?php echo ($l_contactset_count_total == $l_contactset_count_selected ? ' checked':'');?>> <?php echo $formText_ShowAll_Chat2; ?></li>

		<?php foreach($v_contactset['sets'] as $item): ?>
		<li>
			<input type="checkbox"<?php echo($item['active']==1 || $_GET['preselectAll'] ? 'checked':'');?> data-setid="<?php echo $item['set_id']; ?>" data-companyid="<?php echo $item['company_id']; ?>"> <?php echo $item['name'];?>
		</li>
		<?php endforeach; ?>
	</ul>
</div>
