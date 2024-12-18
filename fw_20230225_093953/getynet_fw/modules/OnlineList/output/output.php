<?php
if(isset($variables->fw_session))
{
	$UserContactSet = json_decode($variables->fw_session->cache_contactset, true);
	
	$UserContactSetCount = sizeof($UserContactSet['sets']);
	$UserContactSetSelectedCount = 0;
	
	foreach($UserContactSet['sets'] as $item) {
		if ($item['active']) $UserContactSetSelectedCount++;
	}
	?>
	
	<div class="fw_contact_list_set_status">
		<div class="collapsed-icon">
			<span class="icon icon-user"></span>
		</div>
		<form class="">
			<input class="input" type="text" placeholder="Enter status">
			<input class="button" type="submit" value="Set">
		</form>
	</div>
	
	<div class="fw_contact_list_filter filter_groups">
		<div class="collapsed-icon">
			<span class="icon icon-filter"></span>
		</div>
		<a href="#" class="button" id="fwcl_groups_button">My contact groups (<span class="selected"><?php echo $UserContactSetSelectedCount ?></span> / <span class="all"><?php echo $UserContactSetCount; ?></span>) <span class="icon icon-arrow-right"></a>
	
		<div class="filter_groups_checkboxes">
			<ul id="fwcl_groups">
				<li><input type="checkbox" class="showall" <?php if ($UserContactSetCount == $UserContactSetSelectedCount ) echo 'checked'; ?>> Show all</li>
				<?php foreach($UserContactSet['sets'] as $item): ?>
				<li>
					<input type="checkbox" <?php if ($item['active']==1) echo 'checked'; ?> data-setid="<?php echo $item['set_id']; ?>" data-companyid="<?php echo $item['company_id']; ?>"> <?php echo $item['name'];?>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
	
	<div class="fw_contact_list_filter filter_search">
		<div class="collapsed-icon">
			<span class="icon icon-search"></span>
		</div>
		<a href="#" class="button" id="fwcl_search_button">Search <span class="icon icon-arrow-right"></span></a>
		<div class="fw_filter_search_field">
			<input type="text" class="input fuzzy-search" id="fwcl_search">
		</div>
	</div>
	
	<div id="fwcl_list_container"></div>
	<?php
	require(__DIR__ . '/output_javascript.php');
}